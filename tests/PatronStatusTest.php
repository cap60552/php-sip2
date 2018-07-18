<?php

namespace lordelph\SIP2;

class PatronStatusTest extends AbstractSIP2ClientTest
{
    public function testPatronStatus()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a patron status response
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

        $msg = $client->msgPatronStatusRequest();
        $response = $client->getMessage($msg);

        $info = $client->parsePatronStatusResponse($response);

        $this->assertFixedMetadata('xxxYYYYxxxYYYY', $info, 'PatronStatus');
        $this->assertFixedMetadata('ENG', $info, 'Language');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');

        $this->assertVariableMetadata('1234', $info, 'AO');
        $this->assertVariableMetadata('patron', $info, 'AA');
        $this->assertVariableMetadata('Joe', $info, 'AE');
        $this->assertVariableMetadata('Y', $info, 'BL');
        $this->assertVariableMetadata('Y', $info, 'CQ');
        $this->assertVariableMetadata('GBP', $info, 'BH');
        $this->assertVariableMetadata('1.23', $info, 'BV');
        $this->assertVariableMetadata('message', $info, 'AF');
        $this->assertVariableMetadata('print', $info, 'AG');
    }
}
