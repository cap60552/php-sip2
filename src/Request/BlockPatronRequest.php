<?php

namespace lordelph\SIP2\Request;

/**
 * BlockPatronRequest requests that the patron card be blocked by the ACS. This is, for example, sent when the patron
 * is detected tampering with the SC or when a patron forgets to take their card. The ACS should invalidate the
 * patron’s card and respond with a Patron Status Response message. The ACS could also notify the library staff
 * that the card has been blocked.
 *
 * @method setCardRetained(string $yn)
 * @method setInstitutionId(string $institutionId)
 * @method setMessage(string $message)
 * @method setPatronIdentifier(string $patron)
 * @method setTerminalPassword(string $terminalPassword)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class BlockPatronRequest extends SIP2Request
{
    protected $var = [
        'CardRetained' => ['type' => 'YN', 'default' => 'N'],
        'InstitutionId' => [],
        'Message' => ['default' => ''],
        'PatronIdentifier' => [],
        'TerminalPassword' => ['default' => ''],
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('01');
        $this->addFixedOption($this->getVariable('CardRetained'), 1);
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->getVariable('InstitutionId'));
        $this->addVarOption('AL', $this->getVariable('Message'));
        $this->addVarOption('AA', $this->getVariable('PatronIdentifier'));
        $this->addVarOption('AC', $this->getVariable('TerminalPassword'));

        return $this->returnMessage($withSeq, $withCrc);
    }
}
