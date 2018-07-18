<?php

namespace lordelph\SIP2;

class SCStatusTest extends AbstractSIP2ClientTest
{
    public function testSCStatus()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a patron status response which is returned for a block patron request
        $responses = [
            $this->makeResponse("98".
                "Y".
                "N".
                "Y".
                "N".
                "Y".
                "N".
                "123".
                "456".
                "20180711    185645".
                "2.00".
                "AOinstitution|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgSCStatus(0, 80, 2);
        $response = $client->getMessage($msg);

        $info = $client->parseACSStatusResponse($response);

        $this->assertFixedMetadata('Y', $info, 'Online');
        $this->assertFixedMetadata('N', $info, 'Checkin');
        $this->assertFixedMetadata('Y', $info, 'Checkout');
        $this->assertFixedMetadata('N', $info, 'Renewal');
        $this->assertFixedMetadata('Y', $info, 'PatronUpdate');
        $this->assertFixedMetadata('N', $info, 'Offline');
        $this->assertFixedMetadata('123', $info, 'Timeout');
        $this->assertFixedMetadata('456', $info, 'Retries');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');
        $this->assertFixedMetadata('2.00', $info, 'Protocol');

        $this->assertVariableMetadata('institution', $info, 'AO');
    }
}
