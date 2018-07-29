<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\PatronEnableRequest;

class PatronEnableRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new PatronEnableRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setInstitutionId('Banjo');
        $req->setTerminalPassword('bar');
        $req->setPatronIdentifier('paul');
        $req->setPatronPassword('foo');

        $msg = $req->getMessageString();

        $this->assertEquals("2520180723    094611AOBanjo|AApaul|ACbar|ADfoo|AY0AZF0C8\r\n", $msg);
    }
}
