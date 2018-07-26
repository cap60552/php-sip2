<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\RequestACSResendRequest;

class RequestACSResendRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new RequestACSResendRequest();
        $msg = $req->getMessageString();
        $this->assertEquals("97AY0AZFE2B\r\n", $msg);
    }
}
