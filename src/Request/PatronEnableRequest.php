<?php

namespace lordelph\SIP2\Request;

/**
 * PatronEnableRequest can be used by the SC to re-enable canceled patrons. It should only be used for system testing
 * and validation. The ACS should respond with a Patron Enable Response message.
 *
 * @method setInstitutionId(string $institutionId)
 * @method setPatronIdentifier(string $patron)
 * @method setTerminalPassword(string $terminalPassword)
 * @method setPatronPassword(string $patronPassword)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class PatronEnableRequest extends SIP2Request
{
    protected $var = [
        'InstitutionId' => [],
        'PatronIdentifier' => [],
        'TerminalPassword' => ['default' => ''],
        'PatronPassword' => ['default' => ''],
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('25');
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->getVariable('InstitutionId'));
        $this->addVarOption('AA', $this->getVariable('PatronIdentifier'));
        $this->addVarOption('AC', $this->getVariable('TerminalPassword'), true);
        $this->addVarOption('AD', $this->getVariable('PatronPassword'), true);

        return $this->returnMessage($withSeq, $withCrc);
    }
}
