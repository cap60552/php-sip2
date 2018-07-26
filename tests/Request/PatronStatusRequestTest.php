<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\PatronStatusRequest;

class PatronStatusRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new PatronStatusRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setInstitutionId('Banjo');
        $req->setPatronIdentifier('paul');
        $req->setPatronPassword('foo');

        $msg = $req->getMessageString();

        $this->assertEquals("2300120180723    094611AOBanjo|AApaul|AC|ADfoo|AY0AZF16E\r\n", $msg);
    }
}
