<?php

namespace lordelph\SIP2;

class ItemStatusTest extends AbstractSIP2ClientTest
{
    public function testItemStatus()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate an item status response...
        $responses = [
            $this->makeResponse("20" .
                "1" .
                "20180711    185645" .
                "AB1565921879|" .
                "AJPerl 5 desktop reference|" .
                "CHprop|" .
                "AFmessage|" .
                "AGprint|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgItemStatus('item', 'prop');
        $response = $client->getMessage($msg);

        $info = $client->parseItemStatusResponse($response);

        $this->assertFixedMetadata('1', $info, 'PropertiesOk');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');

        $this->assertVariableMetadata('1565921879', $info, 'AB');
        $this->assertVariableMetadata('Perl 5 desktop reference', $info, 'AJ');
        $this->assertVariableMetadata('prop', $info, 'CH');
        $this->assertVariableMetadata('message', $info, 'AF');
        $this->assertVariableMetadata('print', $info, 'AG');
    }
}
