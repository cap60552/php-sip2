<?php

namespace lordelph\SIP2;

class PatronEnableTest extends AbstractSIP2ClientTest
{
    public function testPatronEnable()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate an item status response...
        $responses = [
            $this->makeResponse("26" .
                "XXXXyyyXXXXyyy" .
                "ENG".
                "20180711    185645" .
                "AOinstitution|" .
                "AApatron|" .
                "AEJoe Tester|" .
                "BLY|".
                "CQY|".
                "AFmessage|" .
                "AGprint|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgPatronEnable();
        $response = $client->getMessage($msg);

        $info = $client->parsePatronEnableResponse($response);

        $this->assertFixedMetadata('XXXXyyyXXXXyyy', $info, 'PatronStatus');
        $this->assertFixedMetadata('ENG', $info, 'Language');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');

        $this->assertVariableMetadata('institution', $info, 'AO');
        $this->assertVariableMetadata('patron', $info, 'AA');
        $this->assertVariableMetadata('Joe Tester', $info, 'AE');
        $this->assertVariableMetadata('Y', $info, 'BL');
        $this->assertVariableMetadata('Y', $info, 'CQ');
        $this->assertVariableMetadata('message', $info, 'AF');
        $this->assertVariableMetadata('print', $info, 'AG');
    }
}
