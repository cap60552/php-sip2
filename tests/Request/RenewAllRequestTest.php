<?php

namespace cap60552\SIP2;

use cap60552\SIP2\Request\RenewAllRequest;

class RenewAllRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new RenewAllRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setInstitutionId('Banjo');
        $req->setPatronIdentifier('paul');
        $req->setPatronPassword('foo');

        $msg = $req->getMessageString();

        $this->assertEquals("65AOBanjo|AApaul|ADfoo|BON|AY0AZF4EA\r\n", $msg);
    }
}
