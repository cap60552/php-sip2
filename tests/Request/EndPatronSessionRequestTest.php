<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\EndPatronSessionRequest;

class EndPatronSessionRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new EndPatronSessionRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setInstitutionId('Banjo');
        $req->setPatronIdentifier('paul');

        $msg = $req->getMessageString();

        $this->assertEquals("3520180723    094611AOBanjo|AApaul|AY0AZF541\r\n", $msg);
    }
}
