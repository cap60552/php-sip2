<?php

namespace lordelph\SIP2;

class ACSResendTest extends AbstractSIP2ClientTest
{
    public function testResend()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //we don't really need a response for this test so we go with a bare minimum
        $responses = [$this->makeResponse("96")];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));
        $client->connect();

        $msg = $client->msgRequestACSResend();
        $this->assertEquals("97AZFEF5", trim($msg));
    }
}
