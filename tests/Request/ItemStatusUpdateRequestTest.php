<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\ItemStatusUpdateRequest;

class ItemStatusUpdateRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new ItemStatusUpdateRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setItemIdentifier('1234');
        $req->setInstitutionId('Banjo');
        $req->setTerminalPassword('foo');
        $req->setItemProperties('xyz');

        $msg = $req->getMessageString();

        $this->assertEquals("1920180723    094611AOBanjo|AB1234|ACfoo|CHxyz|AY0AZF170\r\n", $msg);
    }
}
