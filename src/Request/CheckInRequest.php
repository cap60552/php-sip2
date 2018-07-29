<?php

namespace lordelph\SIP2\Request;

/**
 * CheckInRequest is used by the SC to request to check in an item, and also to cancel a Checkout request that did not
 * successfully complete. The ACS must respond to this command with a Checkin Response message.
 *
 * You must call setItemIdentifier() to provide the barcode of the item you're checking in. Other variables
 * are optional or set automatically by the SIP2Client
 *
 * @method setNoBlock(string $yn)
 * @method setItemReturnDate(string $timestamp)
 * @method setItemLocation(string $location)
 * @method setInstitutionId(string $institutionId)
 * @method setItemIdentifier(string $itemIdentifier)
 * @method setTerminalPassword(string $terminalPassword)
 * @method setItemProperties(string $itemProperties)
 * @method setCancel(string $yn)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class CheckInRequest extends SIP2Request
{
    protected $var = [
        'NoBlock' => ['type' => 'YN', 'default' => 'N'],
        'ItemReturnDate' => ['type' => 'timestamp', 'default' => ''],
        'ItemLocation' => ['default' => ''],
        'InstitutionId' => [],
        'ItemIdentifier' => [],
        'TerminalPassword' => ['default' => ''],
        'ItemProperties' => ['default' => ''],
        'Cancel' => ['type' => 'YN', 'default' => 'N'],
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('09');
        $this->addFixedOption($this->getVariable('NoBlock'), 1);
        $this->addFixedOption($this->datestamp(), 18);
        $this->addFixedOption($this->getVariable('ItemReturnDate'), 18);
        $this->addVarOption('AP', $this->getVariable('ItemLocation'));
        $this->addVarOption('AO', $this->getVariable('InstitutionId'));
        $this->addVarOption('AB', $this->getVariable('ItemIdentifier'));
        $this->addVarOption('AC', $this->getVariable('TerminalPassword'));
        $this->addVarOption('CH', $this->getVariable('ItemProperties'), true);
        $this->addVarOption('BI', $this->getVariable('Cancel'), true);

        return $this->returnMessage($withSeq, $withCrc);
    }
}
