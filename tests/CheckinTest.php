<?php

namespace lordelph\SIP2;

class CheckinTest extends AbstractSIP2ClientTest
{
    public function testFullCheckin()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a fully-loaded checkin response
        $responses = [
            $this->makeResponse("12" .
                "1" .
                "Y" .
                "U" .
                "N" .
                "20180711    185645" .
                "AO1234|" .
                "ABitem|" .
                "AQloc|" .
                "AJtitle|" .
                "CLsort|" .
                "AApatron|" .
                "CKmda|" .
                "CHprop|" .
                "AFmessage|" .
                "AGprint|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgCheckin(
            'mybook',
            strtotime('2018-07-11 11:22:33'),
            'loc',
            'prop',
            'Y',
            'N'
        );
        $response = $client->getMessage($msg);

        $info = $client->parseCheckinResponse($response);

        $this->assertFixedMetadata('1', $info, 'Ok');
        $this->assertFixedMetadata('Y', $info, 'Resensitize');
        $this->assertFixedMetadata('U', $info, 'Magnetic');
        $this->assertFixedMetadata('N', $info, 'Alert');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');

        $this->assertVariableMetadata('1234', $info, 'AO');
        $this->assertVariableMetadata('item', $info, 'AB');
        $this->assertVariableMetadata('loc', $info, 'AQ');
        $this->assertVariableMetadata('title', $info, 'AJ');
        $this->assertVariableMetadata('sort', $info, 'CL');
        $this->assertVariableMetadata('patron', $info, 'AA');
        $this->assertVariableMetadata('mda', $info, 'CK');
        $this->assertVariableMetadata('prop', $info, 'CH');
        $this->assertVariableMetadata('message', $info, 'AF');
        $this->assertVariableMetadata('print', $info, 'AG');
    }

    public function testExampleOLIBCheckin()
    {
        //here we mock an example response from the OCLC OLIB SIP server
        //http://www.oclc.org/support/help/olib/900/Content/System/Supported%20SIP2%20Messages.htm#11
        //Note that the example gives the CRC as E777 but we calculate it as E6C0
        $responses = [
            $this->makeResponse("101YUN20110217    075306".
                "AOMAIN|AB111111|AQ|AJThe 7 pillars of wisdom|AAJSMITH|CK001|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgCheckin('mybook', '', '', 'prop', 'Y', 'N');
        $response = $client->getMessage($msg);

        $info = $client->parseCheckinResponse($response);

        $this->assertFixedMetadata('1', $info, 'Ok');
        $this->assertFixedMetadata('Y', $info, 'Resensitize');
        $this->assertFixedMetadata('U', $info, 'Magnetic');
        $this->assertFixedMetadata('N', $info, 'Alert');
        $this->assertFixedMetadata('20110217    075306', $info, 'TransactionDate');

        $this->assertVariableMetadata('MAIN', $info, 'AO');
        $this->assertVariableMetadata('111111', $info, 'AB');
        $this->assertVariableMetadata('The 7 pillars of wisdom', $info, 'AJ');
        $this->assertVariableMetadata('JSMITH', $info, 'AA');
        $this->assertVariableMetadata('001', $info, 'CK');
    }
}
