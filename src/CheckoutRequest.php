<?php
/**
 * Created by PhpStorm.
 * User: paul
 * Date: 22/07/2018
 * Time: 19:19
 */

namespace lordelph\SIP2;


class CheckoutRequest extends AbstractSIP2Request
{
    private $itemIdentifer;
    private $nbDateDue='';
    private $scRenewal = 'N';
    private $itemProperties = '';
    private $fee = 'N';
    private $noBlock = 'N';
    private $cancel = 'N';

    private $institutionId;
    private $patron;
    private $terminalPassword;
    private $patronpwd;

    public function getMessageString()
    {
        $this->newMessage('11');
        $this->addFixedOption($this->scRenewal, 1);
        $this->addFixedOption($this->noBlock, 1);
        $this->addFixedOption($this->datestamp(), 18);
        if ($this->nbDateDue != '') {
            /* override default date due */
            $this->addFixedOption($this->datestamp($this->nbDateDue), 18);
        } else {
            /* send a blank date due to allow ACS to use default date due computed for item */
            $this->addFixedOption('', 18);
        }
        $this->addVarOption('AO', $this->institutionId);
        $this->addVarOption('AA', $this->patron);
        $this->addVarOption('AB', $this->itemIdentifer);
        $this->addVarOption('AC', $this->terminalPassword);
        $this->addVarOption('CH', $this->itemProperties, true);
        $this->addVarOption('AD', $this->patronpwd, true);
        $this->addVarOption('BO', $this->fee, true); /* Y or N */
        $this->addVarOption('BI', $this->cancel, true); /* Y or N */

        return $this->returnMessage();
    }
}