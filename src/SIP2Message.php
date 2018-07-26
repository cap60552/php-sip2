<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Exception\LogicException;

abstract class SIP2Message
{
    /**
     * @var array provides a list of the variables this message can use. Array key is the variable name in
     * StudlyCaps, value is an array which can contain type, default values
     */
    protected $var=[];

    /** @var integer|null current timestamp, useful for testing */
    protected $timestamp = null;


    protected static function crc($buf)
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

    public function hasVariable($name)
    {
        return isset($this->var[$name]);
    }

    public function getVariable($varName)
    {
        $this->ensureVariableExists($varName);
        return $this->var[$varName]['value'] ??
            $this->var[$varName]['default'] ??
            $this->handleMissing($varName);
    }

    public function getAll()
    {
        $result=[];
        foreach ($this->var as $name => $data) {
            $result[$name] = $this->getVariable($name);
        }
        return $result;
    }

    public function setVariable($varName, $value)
    {
        $this->ensureVariableExists($varName);

        //check type...
        $type = $this->var[$varName]['type'] ?? 'string';
        switch ($type) {
            case 'timestamp':
                $value = $this->datestamp($value);
                break;
            case 'array':
                $value = is_array($value) ? $value : [$value];
                break;
        }

        return $this->var[$varName]['value'] = $value;
    }

    /**
     * If $varName is defined as an array, this will append given value. Otherwise value is set as normal
     * @param $varName
     * @param $value
     */
    public function addVariable($varName, $value)
    {
        $this->ensureVariableExists($varName);
        $type = $this->var[$varName]['type'] ?? 'string';
        if (($type === 'array') && isset($this->var[$varName]['value'])) {
            $this->var[$varName]['value'][] = $value;
        } else {
            $this->setVariable($varName, $value);
        }
    }


    /**
     * Get current timestamp, which can be override with setTimestamp for testing
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp ?? time();
    }

    /**
     * Sets current timestamp, which is useful for creating predictable tests
     * @param $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

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
            return date('Ymd    His', $this->getTimestamp());
        }
    }

    protected function ensureVariableExists($name)
    {
        if (!isset($this->var[$name])) {
            throw new LogicException(get_class($this) . ' has no ' . $name . ' member');
        }
    }

    protected function handleMissing($varName)
    {
        throw new LogicException(get_class($this) . '::set' . $varName . ' must be called');
    }

    public function __call($name, $arguments)
    {
        if (!preg_match('/^(get|set)(.+)$/', $name, $match)) {
            throw new LogicException(get_class($this) . ' has no ' . $name . ' method');
        }
        $varName = $match[2];

        //get?
        if ($match[1] === 'get') {
            return $this->getVariable($varName);
        }
        //set
        $this->setVariable($varName, $arguments[0]);
        return $this;
    }
}
