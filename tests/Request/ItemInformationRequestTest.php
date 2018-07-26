<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\ItemInformationRequest;

class ItemInformationRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new ItemInformationRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setItemIdentifier('1234');
        $req->setInstitutionId('Banjo');
        $req->setTerminalPassword('foo');

        $msg = $req->getMessageString();

        $this->assertEquals("1720180723    094611AOBanjo|AB1234|ACfoo|AY0AZF3E4\r\n", $msg);
    }
}
