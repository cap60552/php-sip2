<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\LoginRequest;

class LoginRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new LoginRequest();
        $req->setTimestamp(strtotime('2018-07-23 09:46:11'));

        $req->setSIPLogin('alice');
        $req->setSIPPassword('c4rr0ll');

        $msg = $req->getMessageString();

        $this->assertEquals("9300CNalice|COc4rr0ll|AY0AZF733\r\n", $msg);
    }
}
