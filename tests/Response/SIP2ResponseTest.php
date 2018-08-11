<?php

namespace cap60552\SIP2;

use cap60552\SIP2\Exception\LogicException;
use cap60552\SIP2\Exception\RuntimeException;
use cap60552\SIP2\Response\EndSessionResponse;
use cap60552\SIP2\Response\LoginResponse;
use cap60552\SIP2\Response\SIP2Response;

class SIP2ResponseTest extends AbstractSIP2ClientTest
{
    /**
     * Test that a SIP2 service providing a response code we don't know will throw an exception
     *
     * @expectedException RuntimeException
     */
    public function testResponse()
    {
        $raw =  $this->makeResponse("771");
        SIP2Response::parse($raw);
    }

    /**
     * Test that a response with new codes in it is handled
     *
     */
    public function testUnknownVariableCodes()
    {
        $raw =  $this->makeResponse("36".
            "Y".
            "20180711    185645".
            "AJ1234|".
            "ZZtop|");

        /** @var EndSessionResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(EndSessionResponse::class, $response);
        $this->assertEquals('Y', $response->getEndSession());

        $this->assertTrue($response->hasVariable('TitleIdentifier'));
        $this->assertTrue($response->hasVariable('ZZ'));
    }

    public function testGetAll()
    {
        $raw =  $this->makeResponse("36".
            "Y".
            "20180711    185645".
            "AOlibrary|".
            "AGline1|".
            "AGline2|");

        /** @var EndSessionResponse $response */
        $response = SIP2Response::parse($raw);
        $this->assertInstanceOf(EndSessionResponse::class, $response);

        $data = $response->getAll();
        $this->assertCount(7, $data);
        $this->assertArrayHasKey('EndSession', $data);
        $this->assertEquals('Y', $data['EndSession']);

        $this->assertArrayHasKey('PrintLine', $data);
        $this->assertCount(2, $data['PrintLine']);
        $this->assertEquals('line1', $data['PrintLine'][0]);
    }

    /**
     * Test that attempting to get unexpected data on a response will throw exception
     *
     * @expectedException LogicException
     */
    public function testGetInvalidVar()
    {
        $raw =  $this->makeResponse("36".
            "Y".
            "20180711    185645".
            "AOlibrary|".
            "AGline1|".
            "AGline2|");

        /** @var EndSessionResponse $response */
        $response = SIP2Response::parse($raw);

        $response->getVariable('TitleIdentifier');
    }
}
