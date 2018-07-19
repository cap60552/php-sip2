<?php

namespace lordelph\SIP2;

use Prophecy\Argument;

/**
 * BindingTest checks we can bind to a specific IP for outbound connections
 * @package lordelph\SIP2
 */
class BindingTest extends AbstractSIP2ClientTest
{
    public function testBinding()
    {
        $client = new SIP2Client;
        $client->bindTo = '1.2.3.4';
        $client->setSocketFactory($this->createBindingTestMockSIP2Server());

        $ok = $client->connect();
        $this->assertTrue($ok);
    }
    
    /**
     * This provides a socket factory which will verify the bind method is  called
     * @return \Socket\Raw\Factory
     */
    protected function createBindingTestMockSIP2Server()
    {
        $socket = $this->prophesize(\Socket\Raw\Socket::class);
        $socket->connect(Argument::type('string'))->willReturn(true);

        //we verify bind gets called...
        $socket->bind(Argument::type('string'))->shouldBeCalled()->willReturn(true);

        //our factory will always fail to connect...
        $factory = $this->prophesize(\Socket\Raw\Factory::class);
        $factory->createFromString(
            Argument::type('string'),
            Argument::any()
        )->willReturn($socket->reveal());

        return $factory->reveal();
    }
}
