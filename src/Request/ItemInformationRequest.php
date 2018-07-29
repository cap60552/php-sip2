<?php

namespace lordelph\SIP2\Request;

/**
 * ItemInformationRequest may be used to request item information. The ACS should respond with the Item Information
 * Response message.
 *
 * @method setInstitutionId(string $institutionId)
 * @method setItemIdentifier(string $itemIdentifier)
 * @method setTerminalPassword(string $terminalPassword)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class ItemInformationRequest extends SIP2Request
{
    protected $var = [
        'InstitutionId' => [],
        'ItemIdentifier' => [],
        'TerminalPassword' => ['default' => ''],
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('17');
        $this->addFixedOption($this->datestamp(), 18);
        $this->addVarOption('AO', $this->getVariable('InstitutionId'));
        $this->addVarOption('AB', $this->getVariable('ItemIdentifier'));
        $this->addVarOption('AC', $this->getVariable('TerminalPassword'));

        return $this->returnMessage($withSeq, $withCrc);
    }
}
