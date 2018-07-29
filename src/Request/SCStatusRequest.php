<?php

namespace lordelph\SIP2\Request;

/**
 * SCStatusRequest message sends SC status to the ACS. It requires an ACS Status Response message reply from the
 * ACS. This message will be the first message sent by the SC to the ACS once a connection has been established
 * (exception: the Login Message may be sent first to login to an ACS server program). The ACS will respond with a
 * message that establishes some of the rules to be followed by the SC and establishes some parameters needed for
 * further communication.
 *
 * @method setStatus(string $status)
 * @method setWidth(string $width)
 * @method setVersion(string $version)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class SCStatusRequest extends SIP2Request
{
    protected $var = [
        'Status' => ['default' => '0'],
        'Width' => ['default' => '80'],
        'Version' => ['default' => '2']
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('99');
        $this->addFixedOption($this->getVariable('Status'), 1);
        $this->addFixedOption($this->getVariable('Width'), 3);
        $this->addFixedOption(sprintf("%03.2f", (float)$this->getVariable('Version')), 4);

        return $this->returnMessage($withSeq, $withCrc);
    }
}
