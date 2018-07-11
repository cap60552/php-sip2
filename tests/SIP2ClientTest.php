<?php

namespace lordelph\SIP2;

use Prophecy\Argument;

class SIP2ClientTest extends \PHPUnit\Framework\TestCase
{
    public function testBasicPatronInfo()
    {
        //our mock socket will return these responses in sequence after each write() to the socket
        //here we simulate a basic patron information request
        $responses = [
            "64              00020180711    185645000000000010000000000009AOExample City Library|" .
            "AA1381380|AEMr Joe Tester|BZ9999|CA9999|CB9999|BLY|CQY|BV0.00|" .
            "BEjoe.tester@example.com|AY0AZCF82\x0D"
        ];

        $client = new SIP2Client;
        $client->setSocketFactory($this->createMockSIP2Server($responses));

        $client->hostname = 'server.example.com';
        $client->port = 6002;
        $client->patron = '101010101';
        $client->patronpwd = '010101';

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

    /**
     * This helper creates a socket factory we can pass to the client. The factory returns a mock
     * socket which will return a sequence of responses after each write() to the socket. This allows
     * us to easily simulate SIP2 server responses
     *
     * @param array $responses
     * @return \Socket\Raw\Factory
     */
    private function createMockSIP2Server(array $responses)
    {
        $socket = $this->prophesize(\Socket\Raw\Socket::class);

        //our mock socket will send each given response in sequence after each write() call
        $socket->responses = $responses;
        $socket->responseIdx = -1;
        $socket->responseOffset = 0;
        $socket->responseLength = 0;

        $socket->write(Argument::type('string'))->will(function ($args) use ($socket) {
            //printf("write(%s)\n", $args[0]);

            //next call to recv will start returning out next canned response
            $socket->responseIdx++;
            if ($socket->responseIdx >= count($socket->responses)) {
                throw new \LogicException(
                    'Mock client has no response for write #' . ($socket->responseIdx + 1) . ':' . $args[0]
                );
            }
            $socket->responseOffset = 0;
            $socket->responseLength = strlen($socket->responses[$socket->responseIdx]);
            return true;
        });

        $socket->recv(1, 0)->will(function ($args) use ($socket) {
            if ($socket->responseOffset >= $socket->responseLength) {
                throw new \LogicException('Client is reading past prophesized response');
            }
            //return next char of response
            return $socket->responses[$socket->responseIdx][$socket->responseOffset++];
        });


        //our factory just returns our mock
        $factory = $this->prophesize(\Socket\Raw\Factory::class);
        $factory->createClient(Argument::type('string'))->willReturn($socket->reveal());

        return $factory->reveal();
    }

    /**
     * Checks fixed data in patron info is present and valid
     * @param string $expected expected value
     * @param array $info info array returned from parsePatronInfoResponse
     * @param string $name name of element
     */
    private function assertFixedMetadata($expected, array $info, $name)
    {
        $this->assertArrayHasKey($name, $info['fixed']);
        $this->assertEquals($expected, $info['fixed'][$name]);
    }

    /**
     * Checks variable data in patron info is present and valid
     * @param string|array $expected expected value - for multi-valued responses you can pass an array here
     * @param array $info info array returned from parsePatronInfoResponse
     * @param string $name name of element
     */
    private function assertVariableMetadata($expected, array $info, $name)
    {
        $this->assertArrayHasKey($name, $info['variable']);
        if (is_string($expected)) {
            $expected = [$expected];
        }

        $valueCount = count($expected);
        $this->assertCount($valueCount, $info['variable'][$name]);
        for ($i = 0; $i < $valueCount; $i++) {
            $this->assertEquals($expected[$i], $info['variable'][$name][$i]);
        }
    }
}
