<?php

namespace lordelph\SIP2\Request;

/**
 * RenewAllRequest is used to renew all items that the patron has checked out. The ACS should respond with a Renew All
 * Response message.
 *
 * @method setInstitutionId(string $institutionId)
 * @method setPatronIdentifier(string $patron)
 * @method setPatronPassword(string $patronPassword)
 * @method setTerminalPassword(string $terminalPassword)
 * @method setFeeAcknowledged(string $yn)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class RenewAllRequest extends SIP2Request
{
    protected $var = [
        'InstitutionId' => [],
        'PatronIdentifier' => [],
        'PatronPassword' => ['default' => ''],
        'TerminalPassword' => ['default' => ''],
        'FeeAcknowledged' => ['type' => 'YN', 'default' => 'N'],
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('65');
        $this->addVarOption('AO', $this->getVariable('InstitutionId'));
        $this->addVarOption('AA', $this->getVariable('PatronIdentifier'));
        $this->addVarOption('AD', $this->getVariable('PatronPassword'), true);
        $this->addVarOption('AC', $this->getVariable('TerminalPassword'), true);
        $this->addVarOption('BO', $this->getVariable('FeeAcknowledged'), true);

        return $this->returnMessage($withSeq, $withCrc);
    }
}
