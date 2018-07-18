<?php

namespace lordelph\SIP2;

class CRCTest extends AbstractSIP2ClientTest
{
    public function testCRC()
    {
        $client = new SIP2Client;

        //crc is a private method, but it's important we ensure it is correct. So, our test works a little
        //magic to call a private method. These particular test values are taken from a Python SIP2
        //implementation https://github.com/tzeumer/SIP2-Client-for-Python/blob/master/sip2/sip2.py#L311
        $in = '09N20160419    12200820160419    122008APReading Room 1|AO830|AB830$28170815|AC|AY2AZ';
        $this->assertEquals('EB80', $this->invokeMethod($client, 'crc', [$in]));

        $in = '09N20160419    12171320160419    121713APReading Room 1|AO830|AB830$28170815|AC|AY2AZ';
        $this->assertEquals('EB7C', $this->invokeMethod($client, 'crc', [$in]));
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @throws \ReflectionException
     */
    private function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
