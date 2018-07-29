<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\RenewRequest;

class RenewRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new RenewRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setItemIdentifier('1234');
        $req->setInstitutionId('Banjo');
        $req->setPatronIdentifier('paul');
        $req->setPatronPassword('foo');

        $msg = $req->getMessageString();

        $this->assertEquals(
            "29NN20180723    094611                  AOBanjo|AApaul|ADfoo|AB1234|BON|AY0AZECF9\r\n",
            $msg
        );
    }
}
