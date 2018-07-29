<?php

namespace lordelph\SIP2\Request;

/**
 * EndPatronSessionRequest will be sent when a patron has completed all of their transactions. The ACS may, upon
 * receipt of this command, close any open files or deallocate data structures pertaining to that patron. The ACS
 * should respond with an End Session Response message.
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
class EndPatronSessionRequest extends SIP2Request
{
    protected $var = [
        'InstitutionId' => [],
        'PatronIdentifier' => [],
        'TerminalPassword' => ['default' => ''],
        'PatronPassword' => ['default' => ''],
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('35');
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->getVariable('InstitutionId'));
        $this->addVarOption('AA', $this->getVariable('PatronIdentifier'));
        $this->addVarOption('AC', $this->getVariable('TerminalPassword'), true);
        $this->addVarOption('AD', $this->getVariable('PatronPassword'), true);

        return $this->returnMessage($withSeq, $withCrc);
    }
}
