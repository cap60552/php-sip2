<?php

namespace lordelph\SIP2;

class FeePaidTest extends AbstractSIP2ClientTest
{
    public function testFeePaid()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a fee paid response
        $responses = [
            $this->makeResponse("36".
                "Y".
                "20180711    185645".
                "AO1234|".
                "AApatron|".
                "BK5555|".
                "AFmessage|".
                "AGprint|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgFeePaid(4, 0, '1.30', 'GBP', 'xxx', 'yyy');
        $response = $client->getMessage($msg);

        $info = $client->parseFeePaidResponse($response);

        $this->assertFixedMetadata('Y', $info, 'PaymentAccepted');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');

        $this->assertVariableMetadata('1234', $info, 'AO');
        $this->assertVariableMetadata('patron', $info, 'AA');
        $this->assertVariableMetadata('5555', $info, 'BK');
        $this->assertVariableMetadata('message', $info, 'AF');
        $this->assertVariableMetadata('print', $info, 'AG');
    }

    public function testBadFeeType()
    {
        $client = new SIP2Client;
        $this->assertFalse($client->msgFeePaid(100, 0, '1.30'));
    }

    public function testBadPaymentType()
    {
        $client = new SIP2Client;
        $this->assertFalse($client->msgFeePaid(2, 100, '1.30'));
    }
}
