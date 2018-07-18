<?php

namespace lordelph\SIP2;

class EndPatronSessionTest extends AbstractSIP2ClientTest
{
    public function testEndSession()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a patron status response
        $responses = [
            $this->makeResponse("36".
                "Y".
                "20180711    185645".
                "AO1234|".
                "AApatron|".
                "AEJoe|".
                "AFmessage|".
                "AGprint|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgEndPatronSession();
        $response = $client->getMessage($msg);

        $info = $client->parseEndSessionResponse($response);

        $this->assertFixedMetadata('Y', $info, 'EndSession');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');

        $this->assertVariableMetadata('1234', $info, 'AO');
        $this->assertVariableMetadata('patron', $info, 'AA');
        $this->assertVariableMetadata('Joe', $info, 'AE');
        $this->assertVariableMetadata('message', $info, 'AF');
        $this->assertVariableMetadata('print', $info, 'AG');
    }
}
