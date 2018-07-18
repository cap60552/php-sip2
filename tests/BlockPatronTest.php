<?php

namespace lordelph\SIP2;

class BlockPatronTest extends AbstractSIP2ClientTest
{
    public function testBlockPatron()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a patron status response which is returned for a block patron request
        $responses = [
            $this->makeResponse("24".
                "xxxYYYYxxxYYYY".
                "ENG".
                "20180711    185645".
                "AO1234|".
                "AApatron|".
                "AEJoe|".
                "BLY|".
                "CQY|".
                "BHGBP|".
                "BV1.23|".
                "AFmessage|".
                "AGprint|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgBlockPatron('Blocked', 'Y');
        $response = $client->getMessage($msg);

        //no need to fully test this response as other tests do it
        $info = $client->parsePatronStatusResponse($response);
        $this->assertFixedMetadata('xxxYYYYxxxYYYY', $info, 'PatronStatus');
        $this->assertFixedMetadata('ENG', $info, 'Language');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');
    }
}
