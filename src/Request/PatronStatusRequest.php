<?php

namespace lordelph\SIP2\Request;

/**
 * PatronStatusRequest is used by the client to request patron information from the SIP2 server. The service must
 * respond to this command with a Patron Status Response message.
 *
 * @method setLanguage(string $languageCode)
 * @method setInstitutionId(string $institutionId)
 * @method setPatronIdentifier(string $patron)
 * @method setTerminalPassword(string $terminalPassword)
 * @method setPatronPassword(string $patronPassword)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class PatronStatusRequest extends SIP2Request
{
    protected $var = [
        'Language' => ['type' => 'nnn', 'default' => '001'],
        'InstitutionId' => [],
        'PatronIdentifier' => [],
        'TerminalPassword' => ['default' => ''],
        'PatronPassword' => ['default' => ''],
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('23');
        $this->addFixedOption($this->getVariable('Language'), 3);
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->getVariable('InstitutionId'));
        $this->addVarOption('AA', $this->getVariable('PatronIdentifier'));
        $this->addVarOption('AC', $this->getVariable('TerminalPassword'));
        $this->addVarOption('AD', $this->getVariable('PatronPassword'), true);

        return $this->returnMessage($withSeq, $withCrc);
    }
}
