<?php

namespace lordelph\SIP2;

use Prophecy\Argument;

/**
 * ConnectionFailureTest just tests how the connect() method responds when a TCP connection cannot be made
 * @package lordelph\SIP2
 */
class ConnectionFailureTest extends AbstractSIP2ClientTest
{
    public function testCRCFailureRetry()
    {
        $client = new SIP2Client;
        $client->setSocketFactory($this->createUnconnectableMockSIP2Server());

        $ok = $client->connect();
        $this->assertFalse($ok);
    }


    /**
     * This provides a socket factory which will always fail to connect
     * @return \Socket\Raw\Factory
     */
    protected function createUnconnectableMockSIP2Server()
    {
        //our factory will always fail to connect...
        $factory = $this->prophesize(\Socket\Raw\Factory::class);
        $factory->createClient(Argument::type('string'))->will(function () {
            throw new \Exception('Test connection failure');
        });

        return $factory->reveal();
    }
}
