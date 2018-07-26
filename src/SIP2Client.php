<?php

namespace lordelph\SIP2;

/**
 * SIP2Client Class
 *
 * This class provides a method of communicating with an Integrated
 * Library System using 3M's SIP2 standard.
 *
 * @package
 * @author     John Wohlers <john@wohlershome.net>
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @version    2.0.0
 */

use lordelph\SIP2\Exception\RuntimeException;
use lordelph\SIP2\Request\SIP2Request;
use lordelph\SIP2\Response\SIP2Response;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Socket\Raw\Factory;
use Socket\Raw\Socket;

/**
 * SIP2Client provides a simple client for SIP2 library services
 *
 * In the specification, and the comments below, 'SC' (or Self Check) denotes the client, and ACS (or Automated
 * Circulation System) denotes the server.
 */
class SIP2Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    //-----------------------------------------------------
    // connection configuration
    //-----------------------------------------------------

    /** @var int maximum number of resends in the event of CRC failure */
    public $maxretry = 3;

    //-----------------------------------------------------
    // patron credentials
    //-----------------------------------------------------

    /** @var string patron identifier / barcode */
    public $patron = '';

    /** @var string patron password / pin */
    public $patronpwd = '';

    //-----------------------------------------------------
    // request options
    //-----------------------------------------------------

    /** @var string language code - 001 is English */
    public $language = '001';

    /**
     * @var string terminator for requests. This should be just \r (0x0d) according to docs, but some vendors
     * require \r\n
     */
    public $msgTerminator = "\r\n";

    /** @var string variable length field terminator */
    public $fldTerminator = '|';

    /** @var int encryption algorithm for user id using during login 0=unencrypted */
    public $uidAlgorithm = 0;

    /** @var int encryption algorithm for user password using during login (no docs for this) */
    public $passwordAlgorithm = 0;

    /** @var string Default location used in some request messages */
    public $location = '';

    /** @var string Institution ID */
    public $institutionId = 'WohlersSIP';

    /** @var string Patron identifier */
    public $patronId = '';

    /** @var string Terminal password */
    public $terminalPassword = '';


    //-----------------------------------------------------
    // internal socket handling
    //-----------------------------------------------------

    /** @var Socket */
    private $socket;

    /** @var Factory injectable factory for creating socket connections */
    private $socketFactory;

    /**
     * Constructor allows you to provide a PSR-3 logger, but you can also use the setLogger method
     * later on.
     *
     * You can also specific the IP address you want to bind to, which is useful if you have multiple local
     * IPs, but you want the remote SIP2 service to see a specific IP address
     *
     * @param LoggerInterface|null $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Allows an alternative socket factory to be injected. The allows us to
     * mock socket connections for testing
     *
     * @param Factory $factory
     */
    public function setSocketFactory(Factory $factory)
    {
        $this->socketFactory = $factory;
    }

    /**
     * Get the current socket factory, creating a default one if necessary
     * @return Factory
     */
    private function getSocketFactory()
    {
        if (is_null($this->socketFactory)) {
            $this->socketFactory = new Factory(); //@codeCoverageIgnore
        }
        return $this->socketFactory;
    }


    /**
     * @param SIP2Request $request
     * @return SIP2Response
     * @throws RuntimeException if server fails to produce a valid response
     */
    public function sendRequest(SIP2Request $request) : SIP2Response
    {
        $raw = $this->getRawResponse($request);
        return SIP2Response::parse($raw);
    }

    private function getRawResponse(SIP2Request $request, $depth = 0)
    {
        $result = '';
        $terminator = '';

        $message = $request->getMessageString();

        $this->logger->debug('SIP2: Sending SIP2 request '.trim($message));
        $this->socket->write($message);

        $this->logger->debug('SIP2: Request Sent, Reading response');

        while ($terminator != "\x0D") {
            //@codeCoverageIgnoreStart
            try {
                $terminator = $this->socket->recv(1, 0);
            } catch (\Exception $e) {
                break;
            }
            //@codeCoverageIgnoreEnd

            $result = $result . $terminator;
        }

        $this->logger->info("SIP2: result={$result}");

        // test message for CRC validity
        if (SIP2Response::checkCRC($result)) {
            $this->logger->debug("SIP2: Message from ACS passed CRC check");
        } else {
            //CRC check failed, we resend the request
            if ($depth < $this->maxretry) {
                $depth++;
                $this->logger->warning("SIP2: Message failed CRC check, retry {$depth})");
                $result = $this->getRawResponse($request, $depth);
            } else {
                $errMsg="SIP2: Failed to get valid CRC after {$this->maxretry} retries.";
                $this->logger->critical($errMsg);
                throw new RuntimeException($errMsg);
            }
        }

        return $result;
    }

    /**
     * Connect to ACS via SIP2
     *
     * The $bind parameter can be useful where a machine which has multiple outbound connections and its important
     * to control which one is used (normally because the remote SIP2 service is firewalled to particular IPs
     *
     * @param string $address ip:port of remote SIP2 service
     * @param string|null $bind local ip to bind socket to
     * @throws RuntimeException if connection cannot be established
     */
    public function connect($address, $bind = null)
    {
        $this->logger->debug("SIP2Client: Attempting connection to $address");

        $this->socket = $this->getSocketFactory()->createFromString($address, $scheme);

        try {
            if (!empty($bind)) {
                $this->logger->debug("SIP2Client: binding socket to $bind");
                $this->socket->bind($bind);
            }

            $this->socket->connect($address);
        } catch (\Exception $e) {
            $this->socket->close();
            $this->socket = null;
            $this->logger->error("SIP2Client: Failed to connect: " . $e->getMessage());
            throw new RuntimeException("Connection failure", 0, $e);
        }

        $this->logger->debug("SIP2Client: connected");
    }

    /**
     * Disconnect from ACS
     */
    public function disconnect()
    {
        $this->socket->close();
        $this->socket = null;
    }
}
