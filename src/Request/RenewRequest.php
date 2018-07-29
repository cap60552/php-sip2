<?php

namespace lordelph\SIP2\Request;

/**
 * RenewRequest is used to renew an item. The ACS should respond with a Renew Response message.
 * Either or both of the setItemIdentifier and setItemTitle methods must be called for the message to
 * be useful.
 *
 * @method setThirdParty(string $yn)
 * @method setNoBlock(string $yn)
 * @method setNBDateDue(string $timestamp)
 * @method setInstitutionId(string $institutionId)
 * @method setPatronIdentifier(string $patron)
 * @method setPatronPassword(string $patronPassword)
 * @method setItemIdentifier(string $itemIdentifier)
 * @method setItemTitle(string $itemTitle)
 * @method setTerminalPassword(string $terminalPassword)
 * @method setItemProperties(string $itemProperties)
 * @method setFeeAcknowledged(string $yn)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class RenewRequest extends SIP2Request
{
    protected $var = [
        'ThirdParty' => ['type' => 'YN', 'default' => 'N'],
        'NoBlock' => ['type' => 'YN', 'default' => 'N'],
        'NBDateDue' => ['type' => 'timestamp', 'default' => ''],
        'InstitutionId' => [],
        'PatronIdentifier' => [],
        'PatronPassword' => ['default' => ''],
        'ItemIdentifier' => ['default' => ''],
        'ItemTitle' => ['default' => ''],
        'TerminalPassword' => ['default' => ''],
        'ItemProperties' => ['default' => ''],
        'FeeAcknowledged' => ['type' => 'YN', 'default' => 'N'],
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('29');
        $this->addFixedOption($this->getVariable('ThirdParty'), 1);
        $this->addFixedOption($this->getVariable('NoBlock'), 1);
        $this->addFixedOption($this->datestamp(), 18);
        $this->addFixedOption($this->getVariable('NBDateDue'), 18);

        $this->addVarOption('AO', $this->getVariable('InstitutionId'));
        $this->addVarOption('AA', $this->getVariable('PatronIdentifier'));
        $this->addVarOption('AD', $this->getVariable('PatronPassword'), true);
        $this->addVarOption('AB', $this->getVariable('ItemIdentifier'), true);
        $this->addVarOption('AJ', $this->getVariable('ItemTitle'), true);
        $this->addVarOption('AC', $this->getVariable('TerminalPassword'), true);
        $this->addVarOption('CH', $this->getVariable('ItemProperties'), true);
        $this->addVarOption('BO', $this->getVariable('FeeAcknowledged'), true);

        return $this->returnMessage($withSeq, $withCrc);
    }
}
