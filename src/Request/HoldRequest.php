<?php

namespace lordelph\SIP2\Request;

/**
 * HoldRequest is used to create, modify, or delete a hold. The ACS should respond with a Hold Response message.
 * Either or both of the setItemIdentifier and setItemTitle methods must be called for the message to
 * be useful.
 *
 * setHoldMode takes one of following values
 *   HoldRequest::MODE_ADD
 *   HoldRequest::MODE_DELETE
 *   HoldRequest::MODE_CHANGE
 *
 * setHoldType takes one of following values:
 *   HoldRequest::HOLD_OTHER
 *   HoldRequest::HOLD_ANY_COPY
 *   HoldRequest::HOLD_SPECIFIC_COPY
 *   HoldRequest::HOLD_ANY_COPY_AT_LOCATION
 *
 * @method setHoldMode(string $mode)
 * @method setExpiryDate(string $timestamp)
 * @method setPickupLocation(string $location)
 * @method setHoldType(string $type)
 * @method setInstitutionId(string $institutionId)
 * @method setPatronIdentifier(string $patron)
 * @method setPatronPassword(string $patronPassword)
 * @method setItemIdentifier(string $itemIdentifier)
 * @method setItemTitle(string $itemTitle)
 * @method setTerminalPassword(string $terminalPassword)
 * @method setFeeAcknowledged(string $yn)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class HoldRequest extends SIP2Request
{
    const MODE_ADD = '+';
    const MODE_DELETE = '-';
    const MODE_CHANGE = '*';

    const HOLD_OTHER = 1;
    const HOLD_ANY_COPY = 2;
    const HOLD_SPECIFIC_COPY = 3;
    const HOLD_ANY_COPY_AT_LOCATION = 4;

    protected $var = [
        'HoldMode' => ['type' => 'regex/[\-\+\*]/'],
        'ExpiryDate' => ['type' => 'timestamp', 'default' => ''],
        'PickupLocation' => ['default' => ''],
        'HoldType' => ['type' => 'n', 'default' => ''],
        'InstitutionId' => [],
        'PatronIdentifier' => [],
        'PatronPassword' => ['default' => ''],
        'ItemIdentifier' => ['default' => ''],
        'ItemTitle' => ['default' => ''],
        'TerminalPassword' => ['default' => ''],
        'FeeAcknowledged' => ['type' => 'YN', 'default' => 'N'],
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('15');
        $this->addFixedOption($this->getVariable('HoldMode'), 1);
        $this->addFixedOption($this->datestamp(), 18);

        $this->addVarOption('BW', $this->getVariable('ExpiryDate'), true);
        $this->addVarOption('BS', $this->getVariable('PickupLocation'), true);
        $this->addVarOption('BY', $this->getVariable('HoldType'), true);
        $this->addVarOption('AO', $this->getVariable('InstitutionId'));
        $this->addVarOption('AA', $this->getVariable('PatronIdentifier'));
        $this->addVarOption('AD', $this->getVariable('PatronPassword'), true);
        $this->addVarOption('AB', $this->getVariable('ItemIdentifier'), true);
        $this->addVarOption('AJ', $this->getVariable('ItemTitle'), true);
        $this->addVarOption('AC', $this->getVariable('TerminalPassword'), true);
        $this->addVarOption('BO', $this->getVariable('FeeAcknowledged'), true);

        return $this->returnMessage($withSeq, $withCrc);
    }
}
