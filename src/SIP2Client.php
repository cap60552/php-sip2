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
 * @link       https://github.com/cap60552/php-sip2/
 */

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Socket\Raw\Factory;
use \Socket\Raw\Socket;

/**
 * SIP2Client provides a simple client for SIP2 library services
 */
class SIP2Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    //-----------------------------------------------------
    // connection configuration
    //-----------------------------------------------------

    /** @var string hostname or IP address to connect to */
    public $hostname;

    /** @var int port number */
    public $port = 6002;

    /**
     * @var string IP (or IP:port) to bind outbound connnections to
     * Using this is only necessary on a machine which has multiple outbound connections and its important
     * to control which one is used (normally because the remote SIP2 service is firewalled to particular IPs
     */
    public $bindTo = '';

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
    // internal request building
    //-----------------------------------------------------

    /** @var int sequence counter for AY */
    private $seq = -1;

    /** @var int resend counter */
    private $retry = 0;

    /** @var string request is built up here  */
    private $msgBuild = '';

    /** @var bool tracks when a variable field is used to prevent further fixed fields */
    private $noFixed = false;

    //-----------------------------------------------------
    // internal socket handling
    //-----------------------------------------------------

    /** @var Socket */
    private $socket;

    /** @var Factory injectable factory for creating socket connections */
    private $socketFactory;

    /**
     * Constructor allows you to provide a PSR-3 logger, but you can also use the setLogger method
     * later on
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
     * Get the current socket factory, creating a default on if necessary
     * @return Factory
     */
    private function getSocketFactory()
    {
        if (is_null($this->socketFactory)) {
            $this->socketFactory = new Factory(); //@codeCoverageIgnore
        }
        return $this->socketFactory;
    }

    public function msgPatronStatusRequest()
    {
        /* Server Response: Patron Status Response message. */
        $this->newMessage('23');
        $this->addFixedOption($this->language, 3);
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AA', $this->patron);
        $this->addVarOption('AC', $this->terminalPassword);
        $this->addVarOption('AD', $this->patronpwd);
        return $this->returnMessage();
    }

    public function msgCheckout(
        $item,
        $nbDateDue = '',
        $scRenewal = 'N',
        $itmProp = '',
        $fee = 'N',
        $noBlock = 'N',
        $cancel = 'N'
    ) {
    
        /* Checkout an item  (11) - untested */
        $this->newMessage('11');
        $this->addFixedOption($scRenewal, 1);
        $this->addFixedOption($noBlock, 1);
        $this->addFixedOption($this->datestamp(), 18);
        if ($nbDateDue != '') {
            /* override default date due */
            $this->addFixedOption($this->datestamp($nbDateDue), 18);
        } else {
            /* send a blank date due to allow ACS to use default date due computed for item */
            $this->addFixedOption('', 18);
        }
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AA', $this->patron);
        $this->addVarOption('AB', $item);
        $this->addVarOption('AC', $this->terminalPassword);
        $this->addVarOption('CH', $itmProp, true);
        $this->addVarOption('AD', $this->patronpwd, true);
        $this->addVarOption('BO', $fee, true); /* Y or N */
        $this->addVarOption('BI', $cancel, true); /* Y or N */

        return $this->returnMessage();
    }

    public function msgCheckin($item, $itmReturnDate, $itmLocation = '', $itmProp = '', $noBlock = 'N', $cancel = '')
    {
        /* Check-in an item (09) - untested */
        if ($itmLocation == '') {
            /* If no location is specified, assume the default location of the SC, behaviour suggested by spec*/
            $itmLocation = $this->location;
        }

        $this->newMessage('09');
        $this->addFixedOption($noBlock, 1);
        $this->addFixedOption($this->datestamp(), 18);
        $this->addFixedOption($this->datestamp($itmReturnDate), 18);
        $this->addVarOption('AP', $itmLocation);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AB', $item);
        $this->addVarOption('AC', $this->terminalPassword);
        $this->addVarOption('CH', $itmProp, true);
        $this->addVarOption('BI', $cancel, true); /* Y or N */

        return $this->returnMessage();
    }

    public function msgBlockPatron($message, $retained = 'N')
    {
        /* Blocks a patron, and responds with a patron status response  (01) - untested */
        $this->newMessage('01');
        $this->addFixedOption($retained, 1); /* Y if card has been retained */
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AL', $message);
        $this->addVarOption('AA', $this->patronId);
        $this->addVarOption('AC', $this->terminalPassword);

        return $this->returnMessage();
    }

    public function msgSCStatus($status = 0, $width = 80, $version = 2)
    {
        /* selfcheck status message, this should be sent immediately after login  - untested */
        /* status codes, from the spec:
            * 0 SC unit is OK
            * 1 SC printer is out of paper
            * 2 SC is about to shut down
            */

        $version = min(2, $version);

        if ($status < 0 || $status > 2) {
            //@codeCoverageIgnoreStart
            $this->logger->error("SIP2: Invalid status passed to msgSCStatus");
            return false;
            //@codeCoverageIgnoreEnd
        }
        $this->newMessage('99');
        $this->addFixedOption($status, 1);
        $this->addFixedOption($width, 3);
        $this->addFixedOption(sprintf("%03.2f", $version), 4);
        return $this->returnMessage();
    }

    public function msgRequestACSResend()
    {
        /* Used to request a resend due to CRC mismatch - No sequence number is used */
        $this->newMessage('97');
        return $this->returnMessage(false);
    }

    public function msgLogin($sipLogin, $sipPassword)
    {
        /* Login (93) - untested */
        $this->newMessage('93');
        $this->addFixedOption($this->uidAlgorithm, 1);
        $this->addFixedOption($this->passwordAlgorithm, 1);
        $this->addVarOption('CN', $sipLogin);
        $this->addVarOption('CO', $sipPassword);
        $this->addVarOption('CP', $this->location, true);
        return $this->returnMessage();
    }

    public function msgPatronInformation($type, $start = '1', $end = '5')
    {

        /*
        * According to the specification:
        * Only one category of items should be  requested at a time, i.e. it would take 6 of these messages, 
        * each with a different position set to Y, to get all the detailed information about a patron's items.
        */
        $summary['none'] = '      ';
        $summary['hold'] = 'Y     ';
        $summary['overdue'] = ' Y    ';
        $summary['charged'] = '  Y   ';
        $summary['fine'] = '   Y  ';
        $summary['recall'] = '    Y ';
        $summary['unavail'] = '     Y';

        /* Request patron information */
        $this->newMessage('63');
        $this->addFixedOption($this->language, 3);
        $this->addFixedOption($this->datestamp(), 18);
        $this->addFixedOption(sprintf("%-10s", $summary[$type]), 10);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AA', $this->patron);
        $this->addVarOption('AC', $this->terminalPassword, true);
        $this->addVarOption('AD', $this->patronpwd, true);
        /* old function version used padded 5 digits, not sure why */
        $this->addVarOption('BP', $start, true);
        /* old function version used padded 5 digits, not sure why */
        $this->addVarOption('BQ', $end, true);
        return $this->returnMessage();
    }

    public function msgEndPatronSession()
    {
        /*  End Patron Session, should be sent before switching to a new patron. (35) - untested */

        $this->newMessage('35');
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AA', $this->patron);
        $this->addVarOption('AC', $this->terminalPassword, true);
        $this->addVarOption('AD', $this->patronpwd, true);
        return $this->returnMessage();
    }

    /* Fee paid function should go here */
    public function msgFeePaid($feeType, $pmtType, $pmtAmount, $curType = 'USD', $feeId = '', $transId = '')
    {
        /* Fee payment function (37) - untested */
        /* Fee Types: */
        /* 01 other/unknown */
        /* 02 administrative */
        /* 03 damage */
        /* 04 overdue */
        /* 05 processing */
        /* 06 rental*/
        /* 07 replacement */
        /* 08 computer access charge */
        /* 09 hold fee */

        /* Value Payment Type */
        /* 00   cash */
        /* 01   VISA */
        /* 02   credit card */

        if (!is_numeric($feeType) || $feeType > 99 || $feeType < 1) {
            /* not a valid fee type - exit */
            $this->logger->error("SIP2: (msgFeePaid) Invalid fee type: {$feeType}");
            return false;
        }

        if (!is_numeric($pmtType) || $pmtType > 99 || $pmtType < 0) {
            /* not a valid payment type - exit */
            $this->logger->error("SIP2: (msgFeePaid) Invalid payment type: {$pmtType}");
            return false;
        }

        $this->newMessage('37');
        $this->addFixedOption($this->datestamp(), 18);
        $this->addFixedOption(sprintf('%02d', $feeType), 2);
        $this->addFixedOption(sprintf('%02d', $pmtType), 2);
        $this->addFixedOption($curType, 3);

        // due to currency format localization, it is up to the programmer
        // to properly format their payment amount
        $this->addVarOption('BV', $pmtAmount);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AA', $this->patron);
        $this->addVarOption('AC', $this->terminalPassword, true);
        $this->addVarOption('AD', $this->patronpwd, true);
        $this->addVarOption('CG', $feeId, true);
        $this->addVarOption('BK', $transId, true);

        return $this->returnMessage();
    }

    public function msgItemInformation($item)
    {

        $this->newMessage('17');
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AB', $item);
        $this->addVarOption('AC', $this->terminalPassword, true);
        return $this->returnMessage();
    }

    public function msgItemStatus($item, $itmProp = '')
    {
        /* Item status update function (19) - untested  */

        $this->newMessage('19');
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AB', $item);
        $this->addVarOption('AC', $this->terminalPassword, true);
        $this->addVarOption('CH', $itmProp);
        return $this->returnMessage();
    }

    public function msgPatronEnable()
    {
        /* Patron Enable function (25) - untested */
        /*  This message can be used by the SC to re-enable cancelled patrons.
        It should only be used for system testing and validation. */
        $this->newMessage('25');
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AA', $this->patron);
        $this->addVarOption('AC', $this->terminalPassword, true);
        $this->addVarOption('AD', $this->patronpwd, true);
        return $this->returnMessage();
    }

    public function msgHold(
        $mode,
        $expDate = '',
        $holdtype = '',
        $item = '',
        $title = '',
        $fee = 'N',
        $pkupLocation = ''
    ) {
    
        /* mode validity check */
        /*
        * - remove hold
        * + place hold
        * * modify hold
        */
        if (strpos('-+*', $mode) === false) {
            /* not a valid mode - exit */
            $this->logger->error("SIP2: Invalid hold mode: {$mode}");
            return false;
        }

        if ($holdtype != '' && ($holdtype < 1 || $holdtype > 9)) {
            /*
            * Valid hold types range from 1 - 9
            * 1   other
            * 2   any copy of title
            * 3   specific copy
            * 4   any copy at a single branch or location
            */
            $this->logger->error("SIP2: Invalid hold type code: {$holdtype}");
            return false;
        }

        $this->newMessage('15');
        $this->addFixedOption($mode, 1);
        $this->addFixedOption($this->datestamp(), 18);
        if ($expDate != '') {
            // hold expiration date,  due to the use of the datestamp function, we have to check here for
            // empty value. when datestamp is passed an empty value it will generate a current datestamp.
            // Also, spec says this is fixed field, but it behaves like a var field and is optional...
            $this->addVarOption('BW', $this->datestamp($expDate), true);
        }
        $this->addVarOption('BS', $pkupLocation, true);
        $this->addVarOption('BY', $holdtype, true);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AA', $this->patron);
        $this->addVarOption('AD', $this->patronpwd, true);
        $this->addVarOption('AB', $item, true);
        $this->addVarOption('AJ', $title, true);
        $this->addVarOption('AC', $this->terminalPassword, true);
        $this->addVarOption('BO', $fee, true); /* Y when user has agreed to a fee notice */

        return $this->returnMessage();
    }

    public function msgRenew(
        $item = '',
        $title = '',
        $nbDateDue = '',
        $itmProp = '',
        $fee = 'N',
        $noBlock = 'N',
        $thirdParty = 'N'
    ) {
    
        /* renew a single item (29) - untested */
        $this->newMessage('29');
        $this->addFixedOption($thirdParty, 1);
        $this->addFixedOption($noBlock, 1);
        $this->addFixedOption($this->datestamp(), 18);
        if ($nbDateDue != '') {
            /* override default date due */
            $this->addFixedOption($this->datestamp($nbDateDue), 18);
        } else {
            /* send a blank date due to allow ACS to use default date due computed for item */
            $this->addFixedOption('', 18);
        }
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AA', $this->patron);
        $this->addVarOption('AD', $this->patronpwd, true);
        $this->addVarOption('AB', $item, true);
        $this->addVarOption('AJ', $title, true);
        $this->addVarOption('AC', $this->terminalPassword, true);
        $this->addVarOption('CH', $itmProp, true);
        $this->addVarOption('BO', $fee, true); /* Y or N */

        return $this->returnMessage();
    }

    public function msgRenewAll($fee = 'N')
    {
        /* renew all items for a patron (65) - untested */
        $this->newMessage('65');
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AA', $this->patron);
        $this->addVarOption('AD', $this->patronpwd, true);
        $this->addVarOption('AC', $this->terminalPassword, true);
        $this->addVarOption('BO', $fee, true); /* Y or N */

        return $this->returnMessage();
    }

    public function parsePatronStatusResponse($response)
    {
        $result['fixed'] =
            array(
                'PatronStatus' => substr($response, 2, 14),
                'Language' => substr($response, 16, 3),
                'TransactionDate' => substr($response, 19, 18),
            );

        $result['variable'] = $this->parseVariableData($response, 37);
        return $result;
    }

    public function parseCheckoutResponse($response)
    {
        $result['fixed'] =
            array(
                'Ok' => substr($response, 2, 1),
                'RenewalOk' => substr($response, 3, 1),
                'Magnetic' => substr($response, 4, 1),
                'Desensitize' => substr($response, 5, 1),
                'TransactionDate' => substr($response, 6, 18),
            );

        $result['variable'] = $this->parseVariableData($response, 24);
        return $result;
    }

    public function parseCheckinResponse($response)
    {
        $result['fixed'] =
            array(
                'Ok' => substr($response, 2, 1),
                'Resensitize' => substr($response, 3, 1),
                'Magnetic' => substr($response, 4, 1),
                'Alert' => substr($response, 5, 1),
                'TransactionDate' => substr($response, 6, 18),
            );

        $result['variable'] = $this->parseVariableData($response, 24);
        return $result;
    }

    public function parseACSStatusResponse($response)
    {
        $result['fixed'] =
            [
                'Online' => substr($response, 2, 1),
                // is Checkin by the SC allowed ?
                'Checkin' => substr($response, 3, 1),
                // is Checkout by the SC allowed ?
                'Checkout' => substr($response, 4, 1),
                // renewal allowed? */
                'Renewal' => substr($response, 5, 1),
                //is patron status updating by the SC allowed ? (status update ok)
                'PatronUpdate' => substr($response, 6, 1),
                'Offline' => substr($response, 7, 1),
                'Timeout' => substr($response, 8, 3),
                'Retries' => substr($response, 11, 3),
                'TransactionDate' => substr($response, 14, 18),
                'Protocol' => substr($response, 32, 4),
            ];

        $result['variable'] = $this->parseVariableData($response, 36);
        return $result;
    }

    public function parseLoginResponse($response)
    {
        $result['fixed'] =
            [
                'Ok' => substr($response, 2, 1),
            ];
        $result['variable'] = array();
        return $result;
    }

    public function parsePatronInfoResponse($response)
    {

        $result['fixed'] =
            [
                'PatronStatus' => substr($response, 2, 14),
                'Language' => substr($response, 16, 3),
                'TransactionDate' => substr($response, 19, 18),
                'HoldCount' => intval(substr($response, 37, 4)),
                'OverdueCount' => intval(substr($response, 41, 4)),
                'ChargedCount' => intval(substr($response, 45, 4)),
                'FineCount' => intval(substr($response, 49, 4)),
                'RecallCount' => intval(substr($response, 53, 4)),
                'UnavailableCount' => intval(substr($response, 57, 4))
            ];

        $result['variable'] = $this->parseVariableData($response, 61);
        return $result;
    }

    public function parseEndSessionResponse($response)
    {
        /*   Response example:  36Y20080228 145537AOWOHLERS|AAX00000000|AY9AZF474   */

        $result['fixed'] =
            [
                'EndSession' => substr($response, 2, 1),
                'TransactionDate' => substr($response, 3, 18),
            ];


        $result['variable'] = $this->parseVariableData($response, 21);

        return $result;
    }

    public function parseFeePaidResponse($response)
    {
        $result['fixed'] =
            [
                'PaymentAccepted' => substr($response, 2, 1),
                'TransactionDate' => substr($response, 3, 18),
            ];

        $result['variable'] = $this->parseVariableData($response, 21);
        return $result;
    }

    public function parseItemInfoResponse($response)
    {
        $result['fixed'] =
            [
                'CirculationStatus' => intval(substr($response, 2, 2)),
                'SecurityMarker' => intval(substr($response, 4, 2)),
                'FeeType' => intval(substr($response, 6, 2)),
                'TransactionDate' => substr($response, 8, 18),
            ];

        $result['variable'] = $this->parseVariableData($response, 26);

        return $result;
    }

    public function parseItemStatusResponse($response)
    {
        $result['fixed'] =
            [
                'PropertiesOk' => substr($response, 2, 1),
                'TransactionDate' => substr($response, 3, 18),
            ];

        $result['variable'] = $this->parseVariableData($response, 21);
        return $result;
    }

    public function parsePatronEnableResponse($response)
    {
        $result['fixed'] =
            [
                'PatronStatus' => substr($response, 2, 14),
                'Language' => substr($response, 16, 3),
                'TransactionDate' => substr($response, 19, 18),
            ];

        $result['variable'] = $this->parseVariableData($response, 37);
        return $result;
    }

    public function parseHoldResponse($response)
    {

        $result['fixed'] =
            [
                'Ok' => substr($response, 2, 1),
                'available' => substr($response, 3, 1),
                'TransactionDate' => substr($response, 4, 18)
            ];

        //expiration date is optional an indicated by BW
        $variableOffset=22;
        if (substr($response, 22, 2) === 'BW') {
            $result['fixed']['ExpirationDate'] = substr($response, 24, 18);
            $variableOffset=42;
        }

        $result['variable'] = $this->parseVariableData($response, $variableOffset);

        return $result;
    }


    public function parseRenewResponse($response)
    {
        /* Response Example:
           300NUU20080228    222232AOWOHLERS|AAX00000241|ABM02400028262|
           AJFolksongs of Britain and Ireland|AH5/23/2008,23:59|CH|
           AFOverride required to exceed renewal limit.|AY1AZCDA5
        */
        $result['fixed'] =
            [
                'Ok' => substr($response, 2, 1),
                'RenewalOk' => substr($response, 3, 1),
                'Magnetic' => substr($response, 4, 1),
                'Desensitize' => substr($response, 5, 1),
                'TransactionDate' => substr($response, 6, 18),
            ];


        $result['variable'] = $this->parseVariableData($response, 24);

        return $result;
    }

    public function parseRenewAllResponse($response)
    {
        $result['fixed'] =
            [
                'Ok' => substr($response, 2, 1),
                'Renewed' => substr($response, 3, 4),
                'Unrenewed' => substr($response, 7, 4),
                'TransactionDate' => substr($response, 11, 18),
            ];


        $result['variable'] = $this->parseVariableData($response, 29);

        return $result;
    }


    public function getMessage($message)
    {
        /* sends the current message, and gets the response */
        $result = '';
        $terminator = '';

        $this->logger->debug('SIP2: Sending SIP2 request...');
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

        /* test message for CRC validity */
        if ($this->checkCRC($result)) {
            /* reset the retry counter on successful send */
            $this->retry = 0;
            $this->logger->debug("SIP2: Message from ACS passed CRC check");
        } else {
            /* CRC check failed, request a resend */
            $this->retry++;
            if ($this->retry < $this->maxretry) {
                /* try again */
                $this->logger->warning("SIP2: Message failed CRC check, retrying ({$this->retry})");

                $result = $this->getMessage($message);
            } else {
                /* give up */
                $this->logger->error("SIP2: Failed to get valid CRC after {$this->maxretry} retries.");
                return false;
            }
        }
        return $result;
    }

    public function connect()
    {

        /* Socket Communications  */
        $this->logger->debug("SIP2: --- BEGIN SIP communication ---");
        $address = $this->hostname . ':' . $this->port;

        $this->socket = $this->getSocketFactory()->createFromString($address, $scheme);

        try {
            if (!empty($this->bindTo)) {
                $this->socket->bind($this->bindTo);
            }

            $this->socket->connect($address);
        } catch (\Exception $e) {
            $this->socket->close();
            $this->socket = null;
            $this->logger->error("SIP2Client: Failed to connect: ".$e->getMessage());
            return false;
        }

        $this->logger->debug("SIP2: --- SOCKET READY ---");
        return true;
    }


    public function disconnect()
    {
        $this->socket->close();
        $this->socket = null;
    }

    /* Core local utility functions */
    private function datestamp($timestamp = '')
    {
        /* generate a SIP2 compatible datestamp */
        /* From the spec:
        * YYYYMMDDZZZZHHMMSS. 
        * All dates and times are expressed according to the ANSI standard X3.30 for date and X3.43 for time. 
        * The ZZZZ field should contain blanks (code $20) to represent local time. To represent universal time, 
        *  a Z character(code $5A) should be put in the last (right hand) position of the ZZZZ field. 
        * To represent other time zones the appropriate character should be used; a Q character (code $51) 
        * should be put in the last (right hand) position of the ZZZZ field to represent Atlantic Standard Time. 
        * When possible local time is the preferred format.
        */
        if ($timestamp != '') {
            /* Generate a proper date time from the date provided */
            return date('Ymd    His', $timestamp);
        } else {
            /* Current Date/Time */
            return date('Ymd    His');
        }
    }

    private function parseVariableData($response, $start)
    {

        $result = array();
        $result['Raw'] = explode("|", substr($response, $start, -7));
        foreach ($result['Raw'] as $item) {
            $field = substr($item, 0, 2);
            $value = substr($item, 2);
            /* SD returns some odd values on occasion, Unable to locate the purpose in spec, so I strip from
            * the parsed array. Orig values will remain in ['raw'] element
            */
            $clean = trim($value, "\x00..\x1F");
            if (trim($clean) <> '') {
                $result[$field][] = $clean;
            }
        }
        $result['AZ'][] = trim(substr($response, -5));

        return ($result);
    }

    private function crc($buf)
    {
        /* Calculate CRC  */
        $sum = 0;

        $len = strlen($buf);
        for ($n = 0; $n < $len; $n++) {
            $sum = $sum + ord(substr($buf, $n, 1));
        }

        $crc = ($sum & 0xFFFF) * -1;

        /* 2008.03.15 - Fixed a bug that allowed the checksum to be larger then 4 digits */
        return substr(sprintf("%4X", $crc), -4, 4);
    }

    private function getSeqNumber()
    {
        /* Get a sequence number for the AY field */
        /* valid numbers range 0-9 */
        $this->seq++;
        if ($this->seq > 9) {
            $this->seq = 0;
        }
        return ($this->seq);
    }

    private function checkCRC($message)
    {
        /* test the received message's CRC by generating our own CRC from the message */
        $test = preg_split('/(.{4})$/', trim($message), 2, PREG_SPLIT_DELIM_CAPTURE);

        if ($this->crc($test[0]) == $test[1]) {
            return true;
        } else {
            //echo "Expected SRC was ".$this->crc($test[0])." but found ".$test[1]."\n";
            return false;
        }
    }

    private function newMessage($code)
    {
        /* resets the msgBuild variable to the value of $code, and clears the flag for fixed messages */
        $this->noFixed = false;
        $this->msgBuild = $code;
    }

    private function addFixedOption($value, $len)
    {
        /* adds a fixed length option to the msgBuild IF no variable options have been added. */
        if ($this->noFixed) {
            //@codeCoverageIgnoreStart
            throw new \LogicException('Cannot add fixed options after variable options');
            //@codeCoverageIgnoreEnd
        }

        $this->msgBuild .= sprintf("%{$len}s", substr($value, 0, $len));
        return true;
    }

    private function addVarOption($field, $value, $optional = false)
    {
        /* adds a variable length option to the message, and also prevents adding additional fixed fields */
        if ($optional == true && $value == '') {
            /* skipped */
            $this->logger->debug("SIP2: Skipping optional field {$field}");
        } else {
            $this->noFixed = true; /* no more fixed for this message */
            $this->msgBuild .= $field . substr($value, 0, 255) . $this->fldTerminator;
        }
        return true;
    }

    private function returnMessage($withSeq = true, $withCrc = true)
    {
        /* Finalizes the message and returns it.  Message will remain in msgBuild until newMessage is called */
        if ($withSeq) {
            $this->msgBuild .= 'AY' . $this->getSeqNumber();
        }
        if ($withCrc) {
            $this->msgBuild .= 'AZ';
            $this->msgBuild .= $this->crc($this->msgBuild);
        }
        $this->msgBuild .= $this->msgTerminator;

        return $this->msgBuild;
    }
}
