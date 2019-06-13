<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Response\ACSStatusResponse;
use lordelph\SIP2\Response\SIP2Response;

class ACSStatusResponseTest extends AbstractSIP2ClientTest
{
    public function testResponse()
    {
        $raw =  $this->makeResponse("98".
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
            "AOinstitution|");

        /** @var ACSStatusResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(ACSStatusResponse::class, $response);

        $this->assertEquals('Y', $response->getOnline());
        $this->assertEquals('N', $response->getCheckin());
        $this->assertEquals('Y', $response->getCheckout());
        $this->assertEquals('N', $response->getRenewal());
        $this->assertEquals('Y', $response->getPatronUpdate());
        $this->assertEquals('N', $response->getOffline());
        $this->assertEquals('123', $response->getTimeout());
        $this->assertEquals('456', $response->getRetries());
        $this->assertEquals('20180711    185645', $response->getTransactionDate());
        $this->assertEquals('2.00', $response->getProtocol());
        $this->assertEquals('institution', $response->getInstitutionId());
    }

    /**
     * SIP2 messages don't all have checksums - this is an edited example of an ACS Status response
     * message seen in the wild
     */
    public function testNoChecksum()
    {
        $raw="98YYYYYY60000320190614    0015442.00AONHKS|AMExample|BXYYYYYYYYNYYNNYYY|ANWeb|".
            "AFThis is a long status message which does not include a checksum.|";
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(ACSStatusResponse::class, $response);
        $this->assertEquals('Y', $response->getOnline());
        $this->assertEquals('Example', $response->getLibraryName());
    }
}
