<?php

namespace lordelph\SIP2;

class PatronInfoTest extends AbstractSIP2ClientTest
{
    public function testBasicPatronInfo()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a basic patron information request
        $responses = [
            $this->makeResponse("64              00020180711    185645000000000010000000000009" .
                "AOExample City Library|AA1381380|AEMr Joe Tester|BZ9999|CA9999|CB9999|BLY|CQY|BV0.00|" .
                "BEjoe.tester@example.com|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $ok = $client->connect();
        $this->assertTrue($ok);

        $msg = $client->msgPatronInformation('none');
        $this->assertNotEmpty($msg);

        $response = $client->getMessage($msg);
        $this->assertNotEmpty($response);

        $info = $client->parsePatronInfoResponse($response);
        $this->assertArrayHasKey('fixed', $info);
        $this->assertArrayHasKey('variable', $info);
        $this->assertArrayHasKey('Raw', $info['variable']);

        //check the fixed data
        $this->assertFixedMetadata('              ', $info, 'PatronStatus');
        $this->assertFixedMetadata('000', $info, 'Language');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');
        $this->assertFixedMetadata(0, $info, 'HoldCount');
        $this->assertFixedMetadata(10, $info, 'ChargedCount');
        $this->assertFixedMetadata(0, $info, 'FineCount');
        $this->assertFixedMetadata(0, $info, 'RecallCount');
        $this->assertFixedMetadata(9, $info, 'UnavailableCount');

        //check variable data
        $this->assertVariableMetadata('Example City Library', $info, 'AO');
        $this->assertVariableMetadata('1381380', $info, 'AA');
        $this->assertVariableMetadata('Mr Joe Tester', $info, 'AE');
        $this->assertVariableMetadata('9999', $info, 'BZ');
        $this->assertVariableMetadata('9999', $info, 'CA');
        $this->assertVariableMetadata('9999', $info, 'CB');
        $this->assertVariableMetadata('Y', $info, 'BL');
        $this->assertVariableMetadata('Y', $info, 'CQ');
        $this->assertVariableMetadata('0.00', $info, 'BV');
        $this->assertVariableMetadata('joe.tester@example.com', $info, 'BE');
        $this->assertVariableMetadata('0', $info, 'AY');
        $this->assertVariableMetadata("CF82", $info, 'AZ');
    }
}
