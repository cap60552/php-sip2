<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\FeePaidRequest;

class FeePaidRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new FeePaidRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setFeeType('01');
        $req->setPaymentType('02');
        $req->setPaymentAmount('2.34');

        $req->setInstitutionId('Banjo');
        $req->setPatronIdentifier('paul');
        $req->setPatronPassword('foo');

        $msg = $req->getMessageString();

        $this->assertEquals("3720180723    0946110102USDBV2.34|AOBanjo|AApaul|AC|ADfoo|AY0AZEE70\r\n", $msg);
    }
}
