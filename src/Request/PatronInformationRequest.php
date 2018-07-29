<?php

namespace lordelph\SIP2\Request;

/**
 * PatronInformationRequest is a superset of the Patron Status Request message. It should be used to request patron
 * information. The ACS should respond with the Patron Information Response message.
 *
 * The setType() method accepts one of none, hold, overdue, charged, fine, recall and unavail
 *
 * @method setLanguage(string $languageCode)
 * @method setType(string $informationType)
 * @method setInstitutionId(string $institutionId)
 * @method setPatronIdentifier(string $patron)
 * @method setTerminalPassword(string $terminalPassword)
 * @method setPatronPassword(string $patronPassword)
 * @method setStart(string $start)
 * @method setEnd(string $end)
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class PatronInformationRequest extends SIP2Request
{
    protected $var = [
        'Language' => ['type' => 'nnn', 'default' => '001'],
        'Type' => ['default' => 'none'],
        'InstitutionId' => [],
        'PatronIdentifier' => [],
        'TerminalPassword' => ['default' => ''],
        'PatronPassword' => ['default' => ''],
        'Start' => ['default' => '1'],
        'End' => ['default' => '5'],
    ];

    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        /*
        * According to the specification:
        * Only one category of items should be  requested at a time, i.e. it would take 6 of these messages,
        * each with a different position set to Y, to get all the detailed information about a patron's items.
        */
        $summary = [];
        $summary['none'] = '      ';
        $summary['hold'] = 'Y     ';
        $summary['overdue'] = ' Y    ';
        $summary['charged'] = '  Y   ';
        $summary['fine'] = '   Y  ';
        $summary['recall'] = '    Y ';
        $summary['unavail'] = '     Y';

        $type = $this->getVariable('Type');

        $this->newMessage('63');
        $this->addFixedOption($this->getVariable('Language'), 3);
        $this->addFixedOption($this->datestamp(), 18);
        $this->addFixedOption(sprintf("%-10s", $summary[$type]), 10);
        $this->addVarOption('AO', $this->getVariable('InstitutionId'));
        $this->addVarOption('AA', $this->getVariable('PatronIdentifier'));
        $this->addVarOption('AC', $this->getVariable('TerminalPassword'));
        $this->addVarOption('AD', $this->getVariable('PatronPassword'), true);
        /* old function version used padded 5 digits, not sure why */
        $this->addVarOption('BP', $this->getVariable('Start'), true);
        /* old function version used padded 5 digits, not sure why */
        $this->addVarOption('BQ', $this->getVariable('End'), true);

        return $this->returnMessage($withSeq, $withCrc);
    }
}
