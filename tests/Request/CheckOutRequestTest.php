<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\CheckOutRequest;

class CheckOutRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new CheckOutRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setItemIdentifier('1234');
        $req->setInstitutionId('Banjo');
        $req->setPatronIdentifier('paul');
        $req->setPatronPassword('foo');

        $msg = $req->getMessageString();

        $this->assertEquals(
            "11NN20180723    094611                  ".
            "AOBanjo|AApaul|AB1234|AC|ADfoo|BON|BIN|AY0AZEAAD\r\n",
            $msg
        );
    }
}
