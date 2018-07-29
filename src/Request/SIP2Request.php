<?php

namespace lordelph\SIP2\Request;

use lordelph\SIP2\SIP2Message;

/**
 * Class SIP2Request extends SIP2Message with methods for building SIP2 message strings
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
abstract class SIP2Request extends SIP2Message
{
    // can use these constants for getVariable/setVariable/setDefault etc
    
    const CANCEL = 'Cancel';
    const END = 'End';
    const EXPIRY_DATE = 'ExpiryDate';
    const FEE_ACKNOWLEDGED = 'FeeAcknowledged';
    const FEE_IDENTIFIER = 'FeeIdentifier';
    const HOLD_TYPE = 'HoldType';
    const INSTITUTION_ID = 'InstitutionID';
    const ITEM_IDENTIFIER = 'ItemIdentifier';
    const ITEM_LOCATION = 'ItemLocation';
    const ITEM_PROPERTIES = 'ItemProperties';
    const ITEM_TITLE = 'ItemTitle';
    const LOCATION = 'Location';
    const MESSAGE = 'Message';
    const NB_DATEDUE = 'NBDateDue';
    const NO_BLOCK = 'NoBlock';
    const PASSWORD_ALGORITHM = 'PasswordAlgorithm';
    const PATRON_IDENTIFIER = 'PatronIdentifier';
    const PATRON_PASSWORD = 'PatronPassword';
    const PAYMENT_AMOUNT = 'PaymentAmount';
    const PICKUP_LOCATION = 'PickupLocation';
    const SIP_LOGIN = 'SIPLogin';
    const SIP_PASSWORD = 'SIPPassword';
    const START = 'Start';
    const STATUS = 'Status';
    const TERMINAL_PASSWORD = 'TerminalPassword';
    const THIRD_PARTY = 'ThirdParty';
    const TRANSACTION_IDENTIFIER = 'TransactionIdentifier';
    const USERID_ALGORITHM = 'UserIdAlgorithm';
    const VERSION = 'Version';
    const WIDTH = 'Width';

    /** @var string request is built up here */
    private $msgBuild = '';

    /** @var bool tracks when a variable field is used to prevent further fixed fields */
    private $noFixed = false;

    /**
     * @var string terminator for requests. This should be just \r (0x0d) according to docs, but some vendors
     * require \r\n
     */
    private $msgTerminator = "\r\n";

    /** @var string variable length field terminator */
    private $fldTerminator = '|';


    private static $seq = -1;

    /**
     * This class automatically increments a static sequence number, but for testing its useful to have this
     * start at 0. This method allows it to be reset
     */
    public static function resetSequence()
    {
        self::$seq = -1;
    }

    /**
     * Derived class must implement this to build its SIP2 request
     */
    abstract public function getMessageString($withSeq = true, $withCrc = true): string;


    /**
     * Start building a new message
     * @param string $code
     */
    protected function newMessage($code)
    {
        /* resets the msgBuild variable to the value of $code, and clears the flag for fixed messages */
        $this->noFixed = false;
        $this->msgBuild = $code;
    }

    protected function addFixedOption($value, $len)
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

    protected function addVarOption($field, $value, $optional = false)
    {
        /* adds a variable length option to the message, and also prevents adding additional fixed fields */
        if ($optional == true && $value == '') {
            /* skipped */
            //$this->logger->debug("SIP2: Skipping optional field {$field}");
        } else {
            $this->noFixed = true; /* no more fixed for this message */
            $this->msgBuild .= $field . substr($value, 0, 255) . $this->fldTerminator;
        }
        return true;
    }

    protected function returnMessage($withSeq = true, $withCrc = true): string
    {
        /* Finalizes the message and returns it.  Message will remain in msgBuild until newMessage is called */
        if ($withSeq) {
            $this->msgBuild .= 'AY' . self::getSeqNumber();
        }
        if ($withCrc) {
            $this->msgBuild .= 'AZ';
            $this->msgBuild .= self::crc($this->msgBuild);
        }
        $this->msgBuild .= $this->msgTerminator;

        return $this->msgBuild;
    }

    /* Core local utility functions */

    protected static function getSeqNumber()
    {
        /* Get a sequence number for the AY field */
        /* valid numbers range 0-9 */
        self::$seq++;
        if (self::$seq > 9) {
            self::$seq = 0;
        }
        return self::$seq;
    }
}
