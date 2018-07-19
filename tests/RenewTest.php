<?php

namespace lordelph\SIP2;

class RenewTest extends AbstractSIP2ClientTest
{
    public function testRenew()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a renew response...
        $responses = [
            $this->makeResponse("30" .
                "1" .
                "Y".
                "U".
                "N".
                "20180711    185645" .
                "AOinstitution|" .
                "AApatron|" .
                "AB1565921879|" .
                "AJPerl 5 desktop reference|" .
                "AH20180711    185645|" .
                "BT01|".
                "CIY|".
                "BHGBP|".
                "BV1.23|".
                "CK001|".
                "CHprop|".
                "BK1234|".
                "AFmessage|" .
                "AGprint|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgRenew(
            '1565921879',
            'Perl 5 desktop reference',
            strtotime('2018-07-11 11:22:33'),
            'prop',
            'N',
            'N',
            'N'
        );
        $response = $client->getMessage($msg);

        $info = $client->parseRenewResponse($response);

        $this->assertFixedMetadata('1', $info, 'Ok');
        $this->assertFixedMetadata('Y', $info, 'RenewalOk');
        $this->assertFixedMetadata('U', $info, 'Magnetic');
        $this->assertFixedMetadata('N', $info, 'Desensitize');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');

        $this->assertVariableMetadata('institution', $info, 'AO');
        $this->assertVariableMetadata('patron', $info, 'AA');
        $this->assertVariableMetadata('1565921879', $info, 'AB');
        $this->assertVariableMetadata('Perl 5 desktop reference', $info, 'AJ');
        $this->assertVariableMetadata('20180711    185645', $info, 'AH');
        $this->assertVariableMetadata('01', $info, 'BT');
        $this->assertVariableMetadata('Y', $info, 'CI');
        $this->assertVariableMetadata('GBP', $info, 'BH');
        $this->assertVariableMetadata('1.23', $info, 'BV');
        $this->assertVariableMetadata('001', $info, 'CK');
        $this->assertVariableMetadata('prop', $info, 'CH');
        $this->assertVariableMetadata('1234', $info, 'BK');
        $this->assertVariableMetadata('message', $info, 'AF');
        $this->assertVariableMetadata('print', $info, 'AG');

        //for coverage, build message with empty date
        $msg = $client->msgRenew('123', 'Test Book', '');
        $this->assertNotEmpty($msg);
    }
}
