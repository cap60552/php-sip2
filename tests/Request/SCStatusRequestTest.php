<?php

namespace cap60552\SIP2;

use cap60552\SIP2\Request\SCStatusRequest;

class SCStatusRequestTest extends AbstractSIP2ClientTest
{
    public function testBasic()
    {
        $req = new SCStatusRequest();
        $msg = $req->getMessageString();
        $this->assertEquals("990 802.00AY0AZFCB1\r\n", $msg);
    }
}
