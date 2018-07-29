<?php

namespace lordelph\SIP2\Request;

/**
 * ItemStatusUpdateRequest can be used to send item information to the ACS, without having to do a Checkout or Checkin
 * operation. The item properties could be stored on the ACS’s database. The ACS should respond with an Item
 * Status Update Response message.
 *
 * @method setInstitutionId(string $institutionId)
 * @method setItemIdentifier(string $itemIdentifier)
 * @method setTerminalPassword(string $terminalPassword)
 * @method setItemProperties(string $itemProperties)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class ItemStatusUpdateRequest extends SIP2Request
{
    protected $var = [
        'InstitutionId' => [],
        'ItemIdentifier' => [],
        'TerminalPassword' => ['default' => ''],
        'ItemProperties' => ['default' => ''],
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('19');
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->getVariable('InstitutionId'));
        $this->addVarOption('AB', $this->getVariable('ItemIdentifier'));
        $this->addVarOption('AC', $this->getVariable('TerminalPassword'));
        $this->addVarOption('CH', $this->getVariable('ItemProperties'), true);

        return $this->returnMessage($withSeq, $withCrc);
    }
}
