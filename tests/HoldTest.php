<?php

namespace lordelph\SIP2;

class HoldTest extends AbstractSIP2ClientTest
{
    public function testHold()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a hold response
        $responses = [
            $this->makeResponse("161Y20180711    185645BW20180711    185645" .
                "BR1|BSLibrary|AO123|AApatron|ABitem|AJtitle|AFthankyou|AGprint|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgHold('+', strtotime('2018-07-11 11:22:33'), 1, 'Item', 'Title', 'N', 'Loc');
        $response = $client->getMessage($msg);

        $info = $client->parseHoldResponse($response);

        $this->assertFixedMetadata('1', $info, 'Ok');
        $this->assertFixedMetadata('Y', $info, 'available');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');
        $this->assertFixedMetadata('20180711    185645', $info, 'ExpirationDate');

        $this->assertVariableMetadata('1', $info, 'BR');
        $this->assertVariableMetadata('Library', $info, 'BS');
        $this->assertVariableMetadata('123', $info, 'AO');
        $this->assertVariableMetadata('patron', $info, 'AA');
        $this->assertVariableMetadata('item', $info, 'AB');
        $this->assertVariableMetadata('title', $info, 'AJ');
        $this->assertVariableMetadata('thankyou', $info, 'AF');
        $this->assertVariableMetadata('print', $info, 'AG');
    }
}
