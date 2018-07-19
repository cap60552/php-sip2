<?php

namespace lordelph\SIP2;

class ItemInformationTest extends AbstractSIP2ClientTest
{
    public function testItemInformation()
    {
        //Here's an example from
        //http://docs.evergreen-ils.org/2.5/_sip_communication.html#17-18_item_information
        //1810020120100623    171415
        //AB1565921879|AJPerl 5 desktop reference|
        //CK001|AQBR1|APBR1|BGBR1|
        //CTBR3|CSQA76.73.P33V76 1996|

        //EXTENSIONS: The CT field for destination location and CS call number are used by Automated Material
        //Handling systems.

        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate an information response using part of the example above
        $responses = [
            $this->makeResponse("18" .
                "01" .
                "02" .
                "03" .
                "20180711    185645" .
                "CF3|" .
                "AH20180711    185645|" .
                "CJ20180711    185645|" .
                "CM20180711    185645|" .
                "AB1565921879|" .
                "AJPerl 5 desktop reference|" .
                "BGBR1|" .
                "BHGBP|" .
                "BV1.23|" .
                "CK001|" .
                "AQBR2|" .
                "APBR3|" .
                "CHprop|" .
                "AFmessage|" .
                "AGprint|")
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->connect();

        $msg = $client->msgItemInformation('item');
        $response = $client->getMessage($msg);

        $info = $client->parseItemInfoResponse($response);

        $this->assertFixedMetadata('01', $info, 'CirculationStatus');
        $this->assertFixedMetadata('02', $info, 'SecurityMarker');
        $this->assertFixedMetadata('03', $info, 'FeeType');
        $this->assertFixedMetadata('20180711    185645', $info, 'TransactionDate');

        $this->assertVariableMetadata('3', $info, 'CF');
        $this->assertVariableMetadata('20180711    185645', $info, 'AH');
        $this->assertVariableMetadata('20180711    185645', $info, 'CJ');
        $this->assertVariableMetadata('20180711    185645', $info, 'CM');
        $this->assertVariableMetadata('1565921879', $info, 'AB');
        $this->assertVariableMetadata('Perl 5 desktop reference', $info, 'AJ');
        $this->assertVariableMetadata('BR1', $info, 'BG');
        $this->assertVariableMetadata('GBP', $info, 'BH');
        $this->assertVariableMetadata('1.23', $info, 'BV');
        $this->assertVariableMetadata('001', $info, 'CK');
        $this->assertVariableMetadata('BR2', $info, 'AQ');
        $this->assertVariableMetadata('BR3', $info, 'AP');
        $this->assertVariableMetadata('prop', $info, 'CH');
        $this->assertVariableMetadata('message', $info, 'AF');
        $this->assertVariableMetadata('print', $info, 'AG');
    }
}
