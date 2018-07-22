<?php

namespace lordelph\SIP2;

abstract class AbstractSIP2Request
{
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

    protected function returnMessage($withSeq = true, $withCrc = true)
    {
        /* Finalizes the message and returns it.  Message will remain in msgBuild until newMessage is called */
        if ($withSeq) {
            $this->msgBuild .= 'AY' . self::getSeqNumber();
        }
        if ($withCrc) {
            $this->msgBuild .= 'AZ';
            $this->msgBuild .= $this->crc($this->msgBuild);
        }
        $this->msgBuild .= $this->msgTerminator;

        return $this->msgBuild;
    }

    /* Core local utility functions */
    protected function datestamp($timestamp = '')
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

    protected function crc($buf)
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