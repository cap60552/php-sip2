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

    /** @var string request is built up here */
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
     * This message is used by the client to request patron information from the SIP2 server. The service must
     * respond to this command with a Patron Status Response message.
     * @return string
     *
     * @see SIP2Client::parsePatronStatusResponse()
     */
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

    /**
     * This message is used by the SC to request to check out an item, and also to cancel a Checkin request that did
     * not successfully complete. The ACS must respond to this command with a Checkout Response message.
     *
     * @param string $item item identifier
     * @param string $nbDateDue unix timestamp of due date (can be blank to let service decide)
     * @param string $scRenewal renewal policy, either Y or N
     * @param string $itmProp item properties
     * @param string $fee fee acknowledge, either Y or N
     * @param string $noBlock no block, either Y or N
     * @param string $cancel Y or N - used to cancel an incomplete checkin
     *
     * @return string
     *
     * @see SIP2Client::parseCheckoutResponse()
     */
    public function msgCheckout(
        $item,
        $nbDateDue = '',
        $scRenewal = 'N',
        $itmProp = '',
        $fee = 'N',
        $noBlock = 'N',
        $cancel = 'N'
    ) {
    
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

    /**
     * This message is used by the SC to request to check in an item, and also to cancel a Checkout request that did not
     * successfully complete. The ACS must respond to this command with a Checkin Response message.
     * @param string $item item identifier
     * @param string $itmReturnDate unix timestamp of return date
     * @param string $itmLocation item location
     * @param string $itmProp item properties
     * @param string $noBlock no block, either Y or N
     * @param string $cancel Y or N - used to cancel an incomplete checkout
     * @return string
     *
     * @see SIP2Client::parseCheckinResponse()
     */
    public function msgCheckin($item, $itmReturnDate, $itmLocation = '', $itmProp = '', $noBlock = 'N', $cancel = '')
    {
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

    /**
     * This message requests that the patron card be blocked by the ACS. This is, for example, sent when the patron is
     * detected tampering with the SC or when a patron forgets to take their card. The ACS should invalidate the
     * patron’s card and respond with a Patron Status Response message. The ACS could also notify the library staff
     * that the card has been blocked.
     *
     * @param string $message blocked card message
     * @param string $retained Y/N indicating whether card was retained
     * @return string
     *
     * @see SIP2Client::parsePatronStatusResponse()
     */
    public function msgBlockPatron($message, $retained = 'N')
    {
        $this->newMessage('01');
        $this->addFixedOption($retained, 1); /* Y if card has been retained */
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AL', $message);
        $this->addVarOption('AA', $this->patronId);
        $this->addVarOption('AC', $this->terminalPassword);

        return $this->returnMessage();
    }

    /**
     * The SC status message sends SC status to the ACS. It requires an ACS Status Response message reply from the ACS.
     * This message will be the first message sent by the SC to the ACS once a connection has been established
     * (exception: the Login Message may be sent first to login to an ACS server program). The ACS will respond with a
     * message that establishes some of the rules to be followed by the SC and establishes some parameters needed for
     * further communication.
     *
     * @param int $status 0=OK, 1=out of paper, 2=shutting down
     * @param int $width print width
     * @param int $version version number X.YY
     * @return bool|string
     *
     * @see SIP2Client::parseACSStatusResponse()
     */
    public function msgSCStatus($status = 0, $width = 80, $version = 2)
    {
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

    /**
     * This message requests the ACS to re-transmit its last message. It is sent by the SC to the ACS when the checksum
     * in a received message does not match the value calculated by the SC. The ACS should respond by re-transmitting
     * its last message, This message should never include a “sequence number” field, even when error detection is
     * enabled, but would include a “checksum” field since checksums are in use.
     *
     * @return string
     */
    public function msgRequestACSResend()
    {
        $this->newMessage('97');
        return $this->returnMessage(false);
    }

    /**
     * This message can be used to login to an ACS server program. The ACS should respond with the Login Response
     * message. Whether to use this message or to use some other mechanism to login to the ACS is configurable on the
     * SC. When this message is used, it will be the first message sent to the ACS.
     *
     * @param string $sipLogin username
     * @param string $sipPassword password
     * @return string
     *
     * @see SIP2Client::parseLoginResponse()
     */
    public function msgLogin($sipLogin, $sipPassword)
    {
        $this->newMessage('93');
        $this->addFixedOption($this->uidAlgorithm, 1);
        $this->addFixedOption($this->passwordAlgorithm, 1);
        $this->addVarOption('CN', $sipLogin);
        $this->addVarOption('CO', $sipPassword);
        $this->addVarOption('CP', $this->location, true);
        return $this->returnMessage();
    }

    /**
     * This message is a superset of the Patron Status Request message. It should be used to request patron information.
     * The ACS should respond with the Patron Information Response message.
     *
     * @param string $type one of none,hold,overdue,charged,fine,recall or unavail
     * @param string $start item
     * @param string $end item
     * @return string
     *
     * @see SIP2Client::parsePatronInfoResponse()
     */
    public function msgPatronInformation($type, $start = '1', $end = '5')
    {
        /*
        * According to the specification:
        * Only one category of items should be  requested at a time, i.e. it would take 6 of these messages, 
        * each with a different position set to Y, to get all the detailed information about a patron's items.
        */
        $summary = [];
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

    /**
     * This message will be sent when a patron has completed all of their transactions. The ACS may, upon receipt of
     * this command, close any open files or deallocate data structures pertaining to that patron. The ACS should
     * respond with an End Session Response message.
     * @return string
     *
     * @see SIP2Client::parseEndSessionResponse()
     */
    public function msgEndPatronSession()
    {
        $this->newMessage('35');
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AA', $this->patron);
        $this->addVarOption('AC', $this->terminalPassword, true);
        $this->addVarOption('AD', $this->patronpwd, true);
        return $this->returnMessage();
    }

    /**
     * This message can be used to notify the ACS that a fee has been collected from the patron. The ACS should record
     * this information in their database and respond with a Fee Paid Response message.
     *
     * @param string $feeType fee type
     *    01 other/unknown
     *    02 administrative
     *    03 damage
     *    04 overdue
     *    05 processing
     *    06 rental
     *    07 replacement
     *    08 computer access charge
     *    09 hold fee
     * @param string $pmtType payment type
     *    00 cash
     *    01 visa
     *    02 credit card
     * @param string $pmtAmount payment amount
     * @param string $curType currency 3-letter code following ISO Standard 4217:1995
     * @param string $feeId Identifies a specific fee, possibly in combination with fee type.
     * @param string $transId transaction identifier
     *
     * @return bool|string
     *
     * @see SIP2Client::parseFeePaidResponse()
     */
    public function msgFeePaid($feeType, $pmtType, $pmtAmount, $curType = 'USD', $feeId = '', $transId = '')
    {
        if (!is_numeric($feeType) || $feeType > 99 || $feeType < 1) {
            $this->logger->error("SIP2: (msgFeePaid) Invalid fee type: {$feeType}");
            return false;
        }

        if (!is_numeric($pmtType) || $pmtType > 99 || $pmtType < 0) {
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

    /**
     * This message may be used to request item information. The ACS should respond with the Item Information Response
     * message.
     *
     * @param string $item item identifier
     * @return string
     *
     * @see SIP2Client::parseItemInfoResponse()
     */
    public function msgItemInformation($item)
    {
        $this->newMessage('17');
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AB', $item);
        $this->addVarOption('AC', $this->terminalPassword, true);
        return $this->returnMessage();
    }

    /**
     * This message can be used to send item information to the ACS, without having to do a Checkout or Checkin
     * operation. The item properties could be stored on the ACS’s database. The ACS should respond with an Item
     * Status Update Response message.
     *
     * @param string $item item identifier
     * @param string $itmProp item properties
     * @return string
     *
     * @see SIP2Client::parseItemStatusResponse()
     */
    public function msgItemStatus($item, $itmProp = '')
    {
        $this->newMessage('19');
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AB', $item);
        $this->addVarOption('AC', $this->terminalPassword, true);
        $this->addVarOption('CH', $itmProp);
        return $this->returnMessage();
    }

    /**
     * This message can be used by the SC to re-enable canceled patrons. It should only be used for system testing and
     * validation. The ACS should respond with a Patron Enable Response message.
     *
     * @return string
     *
     * @see SIP2Client::parsePatronEnableResponse()
     */
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

    /**
     * This message is used to create, modify, or delete a hold. The ACS should respond with a Hold Response message.
     * Either or both of the “item identifier” and “title identifier” fields must be present for the message to
     * be useful.
     *
     * @param string $mode one of '+', '-' or '*' to denote add, delete or  change
     * @param string $expDate unix timestamp expiration date
     * @param string $holdtype optional single digit, one of following values:
     *  1   other
     *  2   any copy of title
     *  3   specific copy
     *  4   any copy at a single branch or location
     * @param string $item item identifier
     * @param string $title item title
     * @param string $feeAcknowledged Y/N to indicate if fee has been acknowledged
     * @param string $pickupLocation pickup location
     * @return bool|string
     *
     * @see SIP2Client::parseHoldResponse()
     */
    public function msgHold(
        $mode,
        $expDate = '',
        $holdtype = '',
        $item = '',
        $title = '',
        $feeAcknowledged = 'N',
        $pickupLocation = ''
    ) {
    
        if (strpos('-+*', $mode) === false) {
            $this->logger->error("SIP2: Invalid hold mode: {$mode}");
            return false;
        }

        if ($holdtype != '' && ($holdtype < 1 || $holdtype > 9)) {
            $this->logger->error("SIP2: Invalid hold type code: {$holdtype}");
            return false;
        }

        $this->newMessage('15');
        $this->addFixedOption($mode, 1);
        $this->addFixedOption($this->datestamp(), 18);
        if ($expDate != '') {
            $this->addVarOption('BW', $this->datestamp($expDate), true);
        }
        $this->addVarOption('BS', $pickupLocation, true);
        $this->addVarOption('BY', $holdtype, true);
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AA', $this->patron);
        $this->addVarOption('AD', $this->patronpwd, true);
        $this->addVarOption('AB', $item, true);
        $this->addVarOption('AJ', $title, true);
        $this->addVarOption('AC', $this->terminalPassword, true);
        $this->addVarOption('BO', $feeAcknowledged, true);

        return $this->returnMessage();
    }

    /**
     * This message is used to renew an item. The ACS should respond with a Renew Response message. Either or both of
     * the “item identifier” and “title identifier” fields must be present for the message to be useful.
     *
     * @param string $item item identifier
     * @param string $title item title
     * @param string $nbDateDue unix timestamp of no block due date
     * @param string $itemProperties item properties
     * @param string $feeAcknowledged Y/N if fee acknowledged
     * @param string $noBlock Y/N if no blocking permitted - see specification
     * @param string $thirdParty Y/N if third party renewals allowed
     * @return string
     *
     * @see SIP2Client::parseRenewResponse()
     */
    public function msgRenew(
        $item = '',
        $title = '',
        $nbDateDue = '',
        $itemProperties = '',
        $feeAcknowledged = 'N',
        $noBlock = 'N',
        $thirdParty = 'N'
    ) {
    
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
        $this->addVarOption('CH', $itemProperties, true);
        $this->addVarOption('BO', $feeAcknowledged, true); /* Y or N */

        return $this->returnMessage();
    }

    /**
     * This message is used to renew all items that the patron has checked out. The ACS should respond with a Renew All
     * Response message.
     *
     * @param string $feeAcknowledged
     * @return string
     *
     * @see SIP2Client::parseRenewAllResponse()
     */
    public function msgRenewAll($feeAcknowledged = 'N')
    {
        $this->newMessage('65');
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AA', $this->patron);
        $this->addVarOption('AD', $this->patronpwd, true);
        $this->addVarOption('AC', $this->terminalPassword, true);
        $this->addVarOption('BO', $feeAcknowledged, true); /* Y or N */

        return $this->returnMessage();
    }

    /**
     * Parse response from a Patron Status request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgPatronStatusRequest()
     */
    public function parsePatronStatusResponse($response)
    {
        $result = [];
        $result['fixed'] =
            array(
                'PatronStatus' => substr($response, 2, 14),
                'Language' => substr($response, 16, 3),
                'TransactionDate' => substr($response, 19, 18),
            );

        $result['variable'] = $this->parseVariableData($response, 37);
        return $result;
    }

    /**
     * Parse response from a Checkout request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgCheckout()
     */
    public function parseCheckoutResponse($response)
    {
        $result = [];
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

    /**
     * Parse response from a Checkin request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgCheckin()
     */
    public function parseCheckinResponse($response)
    {
        $result = [];
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

    /**
     * Parse response from a SC Status request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgSCStatus()
     */
    public function parseACSStatusResponse($response)
    {
        $result = [];
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

    /**
     * Parse response from a Login request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgSCStatus()
     */
    public function parseLoginResponse($response)
    {
        $result = [];
        $result['fixed'] =
            [
                'Ok' => substr($response, 2, 1),
            ];
        $result['variable'] = array();
        return $result;
    }

    /**
     * Parse response from a Patron Information request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgPatronInformation()
     */
    public function parsePatronInfoResponse($response)
    {
        $result = [];
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

    /**
     * Parse response from a End Session request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgPatronInformation()
     */
    public function parseEndSessionResponse($response)
    {
        /*   Response example:  36Y20080228 145537AOWOHLERS|AAX00000000|AY9AZF474   */
        $result = [];
        $result['fixed'] =
            [
                'EndSession' => substr($response, 2, 1),
                'TransactionDate' => substr($response, 3, 18),
            ];


        $result['variable'] = $this->parseVariableData($response, 21);

        return $result;
    }

    /**
     * Parse response from a Fee Paid request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgFeePaid()
     */
    public function parseFeePaidResponse($response)
    {
        $result = [];
        $result['fixed'] =
            [
                'PaymentAccepted' => substr($response, 2, 1),
                'TransactionDate' => substr($response, 3, 18),
            ];

        $result['variable'] = $this->parseVariableData($response, 21);
        return $result;
    }

    /**
     * Parse response from a Item Information request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgItemInformation()
     */
    public function parseItemInfoResponse($response)
    {
        $result = [];
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

    /**
     * Parse response from a Item Status request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgItemStatus()
     */
    public function parseItemStatusResponse($response)
    {
        $result = [];
        $result['fixed'] =
            [
                'PropertiesOk' => substr($response, 2, 1),
                'TransactionDate' => substr($response, 3, 18),
            ];

        $result['variable'] = $this->parseVariableData($response, 21);
        return $result;
    }

    /**
     * Parse response from a Patron Enable request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgPatronEnable()
     */
    public function parsePatronEnableResponse($response)
    {
        $result = [];
        $result['fixed'] =
            [
                'PatronStatus' => substr($response, 2, 14),
                'Language' => substr($response, 16, 3),
                'TransactionDate' => substr($response, 19, 18),
            ];

        $result['variable'] = $this->parseVariableData($response, 37);
        return $result;
    }

    /**
     * Parse response from a Hold request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgHold()
     */
    public function parseHoldResponse($response)
    {
        $result = [];
        $result['fixed'] =
            [
                'Ok' => substr($response, 2, 1),
                'available' => substr($response, 3, 1),
                'TransactionDate' => substr($response, 4, 18)
            ];

        //expiration date is optional an indicated by BW
        $variableOffset = 22;
        if (substr($response, 22, 2) === 'BW') {
            $result['fixed']['ExpirationDate'] = substr($response, 24, 18);
            $variableOffset = 42;
        }

        $result['variable'] = $this->parseVariableData($response, $variableOffset);

        return $result;
    }


    /**
     * Parse response from a Renew request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgRenew()
     */
    public function parseRenewResponse($response)
    {
        /* Response Example:
           300NUU20080228    222232AOWOHLERS|AAX00000241|ABM02400028262|
           AJFolksongs of Britain and Ireland|AH5/23/2008,23:59|CH|
           AFOverride required to exceed renewal limit.|AY1AZCDA5
        */
        $result = [];
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

    /**
     * Parse response from a Renew All request
     * @param string $response ACS response
     * @return array with 'fixed' and 'variable' keys
     *
     * @see SIP2Client::msgRenewAll()
     */
    public function parseRenewAllResponse($response)
    {
        $result = [];
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

    /**
     * Send a request to ACS and obtain raw response
     *
     * @param string $message generated by one of the msg*() methods
     * @return bool|string false in event of failure, otherwise a string containing ACS response
     */
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

    /**
     * Connect to ACS via SIP2
     * @return bool returns true if connection is established
     */
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
            $this->logger->error("SIP2Client: Failed to connect: " . $e->getMessage());
            return false;
        }

        $this->logger->debug("SIP2: --- SOCKET READY ---");
        return true;
    }

    /**
     * Disconnect from ACS
     */
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
