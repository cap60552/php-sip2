<?php

namespace lordelph\SIP2;

class CheckoutTest extends AbstractSIP2ClientTest
{
    public function testFullCheckout()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a fully-loaded checkout response
        $responses = [
            $this->makeResponse("12" .
                "1" .
                "Y" .
                "N" .
                "Y" .
                "20180711    185645" .
                "AO1234|" .
                "AApatron|" .
                "ABitem|" .
                "AJtitle|" .
                "AH20180711    185645|" .
                "BT01|" .
                "CIN|" .
                "BHGBP|" .
                "BV1.23|" .
                "CKmda|" .
                "CHprop|" .
                "BKxyz|" .
                "AFmessage|" .
                "AGprint|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgCheckout(
            'mybook',
            strtotime('2018-07-11 11:22:33'),
            'N',
            'prop',
            'Y',
            'N',
            'N'
        );
        $response = $client->getMessage($msg);

        $info = $client->parseCheckoutResponse($response);

        $this->assertFixedMetadata('1', $info, 'Ok');
        $this->assertFixedMetadata('Y', $info, 'RenewalOk');
        $this->assertFixedMetadata('N', $info, 'Magnetic');
        $this->assertFixedMetadata('Y', $info, 'Desensitize');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');

        $this->assertVariableMetadata('1234', $info, 'AO');
        $this->assertVariableMetadata('patron', $info, 'AA');
        $this->assertVariableMetadata('item', $info, 'AB');
        $this->assertVariableMetadata('title', $info, 'AJ');
        $this->assertVariableMetadata('20180711    185645', $info, 'AH');
        $this->assertVariableMetadata('01', $info, 'BT');
        $this->assertVariableMetadata('N', $info, 'CI');
        $this->assertVariableMetadata('GBP', $info, 'BH');
        $this->assertVariableMetadata('1.23', $info, 'BV');
        $this->assertVariableMetadata('mda', $info, 'CK');
        $this->assertVariableMetadata('prop', $info, 'CH');
        $this->assertVariableMetadata('xyz', $info, 'BK');
        $this->assertVariableMetadata('message', $info, 'AF');
        $this->assertVariableMetadata('print', $info, 'AG');
    }

    public function testExampleOLIBCheckout()
    {
        //here we mock an example response from the OCLC OLIB SIP server
        //http://www.oclc.org/support/help/olib/900/Content/System/Supported%20SIP2%20Messages.htm#11
        //Note that the example gives the CRC as DC91 but we calculate it as DD61
        $responses = [
            $this->makeResponse("121NUY20101014    121215AOMAIN|AH20101104    120000|AAJSMITH|AB111111|" .
                "AJMarmalade and jam making for dummies|BV1.30|AF|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgCheckout('mybook', '', 'N', 'prop', 'Y', 'N', 'N');
        $response = $client->getMessage($msg);

        $info = $client->parseCheckoutResponse($response);

        $this->assertFixedMetadata('1', $info, 'Ok');
        $this->assertFixedMetadata('N', $info, 'RenewalOk');
        $this->assertFixedMetadata('U', $info, 'Magnetic');
        $this->assertFixedMetadata('Y', $info, 'Desensitize');
        $this->assertFixedMetadata('20101014    121215', $info, 'TransactionDate');

        $this->assertVariableMetadata('MAIN', $info, 'AO');
        $this->assertVariableMetadata('20101104    120000', $info, 'AH');
        $this->assertVariableMetadata('JSMITH', $info, 'AA');
        $this->assertVariableMetadata('111111', $info, 'AB');
        $this->assertVariableMetadata('Marmalade and jam making for dummies', $info, 'AJ');
        $this->assertVariableMetadata('1.30', $info, 'BV');
    }
}
