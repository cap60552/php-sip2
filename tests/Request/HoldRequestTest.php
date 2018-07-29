<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\HoldRequest;

class HoldRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new HoldRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setHoldMode(HoldRequest::MODE_ADD);
        $req->setItemIdentifier('1234');
        $req->setInstitutionId('Banjo');
        $req->setPatronIdentifier('paul');
        $req->setPatronPassword('foo');

        $msg = $req->getMessageString();

        $this->assertEquals("15+20180723    094611AOBanjo|AApaul|ADfoo|AB1234|BON|AY0AZEFAF\r\n", $msg);
    }
}
