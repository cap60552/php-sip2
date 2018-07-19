<?php

namespace lordelph\SIP2;

class RenewAllTest extends AbstractSIP2ClientTest
{
    public function testRenewAll()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate an renew all response
        $responses = [
            $this->makeResponse("66" .
                "1" .
                "0002".
                "0003".
                "20180711    185645" .
                "AOinstitution|" .
                "BMbook 1|".
                "BMbook 2|".
                "BNbook 3|".
                "BNbook 4|".
                "BNbook 5|".
                "AFmessage|" .
                "AGprint|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgRenewAll('Y');
        $response = $client->getMessage($msg);

        $info = $client->parseRenewAllResponse($response);

        $this->assertFixedMetadata('1', $info, 'Ok');
        $this->assertFixedMetadata('0002', $info, 'Renewed');
        $this->assertFixedMetadata('0003', $info, 'Unrenewed');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');

        $this->assertVariableMetadata('institution', $info, 'AO');

        $this->assertVariableMetadata(['book 1', 'book 2'], $info, 'BM');
        $this->assertVariableMetadata(['book 3', 'book 4', 'book 5'], $info, 'BN');

        $this->assertVariableMetadata('message', $info, 'AF');
        $this->assertVariableMetadata('print', $info, 'AG');
    }
}
