<?php

namespace lordelph\SIP2\Request;

/**
 * CheckOutRequest is used by the SC to request to check out an item, and also to cancel a CheckIn request that did
 * not successfully complete. The ACS must respond to this command with a CheckOut Response message.
 *
 * You must call setItemIdentifier() to provide the barcode of the item you're checking out. Other variables
 * are optional or set automatically by the SIP2Client
 *
 * @method setSCRenewal(string $yn)
 * @method setNoBlock(string $yn)
 * @method setNBDateDue(string $timestamp)
 * @method setInstitutionId(string $institutionId)
 * @method setPatronIdentifier(string $patron)
 * @method setItemIdentifier(string $itemIdentifier)
 * @method setTerminalPassword(string $terminalPassword)
 * @method setItemProperties(string $itemProperties)
 * @method setPatronPassword(string $patronPassword)
 * @method setFeeAcknowledged(string $yn)
 * @method setCancel(string $yn)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class CheckOutRequest extends SIP2Request
{
    protected $var = [
        'SCRenewal' => ['type' => 'YUN', 'default' => 'N'],
        'NoBlock' => ['type' => 'YN', 'default' => 'N'],
        'NBDateDue' => ['type' => 'timestamp', 'default' => ''],
        'InstitutionId' => [],
        'PatronIdentifier' => [],
        'ItemIdentifier' => [],
        'TerminalPassword' => ['default' => ''],
        'ItemProperties' => ['default' => ''],
        'PatronPassword' => ['default' => ''],
        'FeeAcknowledged' => ['type' => 'YN', 'default' => 'N'],
        'Cancel' => ['type' => 'YN', 'default' => 'N'],
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('11');
        $this->addFixedOption($this->getVariable('SCRenewal'), 1);
        $this->addFixedOption($this->getVariable('NoBlock'), 1);
        $this->addFixedOption($this->datestamp(), 18);
        $this->addFixedOption($this->getVariable('NBDateDue'), 18);

        $this->addVarOption('AO', $this->getVariable('InstitutionId'));
        $this->addVarOption('AA', $this->getVariable('PatronIdentifier'));
        $this->addVarOption('AB', $this->getVariable('ItemIdentifier'));
        $this->addVarOption('AC', $this->getVariable('TerminalPassword'));
        $this->addVarOption('CH', $this->getVariable('ItemProperties'), true);
        $this->addVarOption('AD', $this->getVariable('PatronPassword'), true);
        $this->addVarOption('BO', $this->getVariable('FeeAcknowledged'), true);
        $this->addVarOption('BI', $this->getVariable('Cancel'), true);

        return $this->returnMessage($withSeq, $withCrc);
    }
}
