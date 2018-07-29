<?php

namespace lordelph\SIP2\Request;

/**
 * FeePaidRequest can be used to notify the ACS that a fee has been collected from the patron. The ACS should record
 * this information in their database and respond with a Fee Paid Response message.
 *
 * The FeeType can be one of
 *    01 other/unknown
 *    02 administrative
 *    03 damage
 *    04 overdue
 *    05 processing
 *    06 rental
 *    07 replacement
 *    08 computer access charge
 *    09 hold fee
 * The PaymentType can be one of
 *    00 cash
 *    01 visa
 *    02 credit card
 *
 * CurrencyType is a 3-letter code following ISO Standard 4217:1995
 *
 * @method setFeeType(string $feeType)
 * @method setPaymentType(string $paymentType)
 * @method setCurrencyType(string $currencyCode)
 * @method setPaymentAmount(string $amount)
 * @method setInstitutionId(string $institutionId)
 * @method setPatronIdentifier(string $patron)
 * @method setTerminalPassword(string $terminalPassword)
 * @method setPatronPassword(string $patronPassword)
 * @method setFeeIdentifier(string $feeId)
 * @method setTransactionIdentifier(string $transactionId)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class FeePaidRequest extends SIP2Request
{
    protected $var = [
        'FeeType' => ['type' => 'nn'],
        'PaymentType' => ['type' => 'nn'],
        'CurrencyType' => ['default' => 'USD'],
        'PaymentAmount' => [],
        'InstitutionId' => [],
        'PatronIdentifier' => [],
        'TerminalPassword' => ['default' => ''],
        'PatronPassword' => ['default' => ''],
        'FeeIdentifier' => ['default' => ''],
        'TransactionIdentifier' => ['default' => ''],
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('37');
        $this->addFixedOption($this->datestamp(), 18);
        $this->addFixedOption(sprintf('%02d', (string)$this->getVariable('FeeType')), 2);
        $this->addFixedOption(sprintf('%02d', (string)$this->getVariable('PaymentType')), 2);
        $this->addFixedOption($this->getVariable('CurrencyType'), 3);

        // due to currency format localization, it is up to the programmer
        // to properly format their payment amount
        $this->addVarOption('BV', $this->getVariable('PaymentAmount'));
        $this->addVarOption('AO', $this->getVariable('InstitutionId'));
        $this->addVarOption('AA', $this->getVariable('PatronIdentifier'));
        $this->addVarOption('AC', $this->getVariable('TerminalPassword'));
        $this->addVarOption('AD', $this->getVariable('PatronPassword'), true);
        $this->addVarOption('CG', $this->getVariable('FeeIdentifier'), true);
        $this->addVarOption('BK', $this->getVariable('TransactionIdentifier'), true);

        return $this->returnMessage($withSeq, $withCrc);
    }
}
