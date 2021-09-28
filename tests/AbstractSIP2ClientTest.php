<?php

namespace lordelph\SIP2;

use lordelph\SIP2\Request\SIP2Request;
use Prophecy\Argument;

/**
 * AbstractSIP2ClientTest provides a mock socket which can return a sequence of canned responses, and
 * helpers to assist with asserting the content of a parsed response
 *
 * @package lordelph\SIP2
 */
abstract class AbstractSIP2ClientTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        SIP2Request::resetSequence();
    }

    /**
     * Make a valid response by adding sequence number and CRC
     * @param $str
     * @return string
     */
    protected function makeResponse($str)
    {
        //add sequence number and intro for checksum
        $str .= 'AY0AZ';
        //add checksum
        $str .= $this->crc($str);
        //add terminator
        $str .= "\x0D";
        return $str;
    }

    /**
     * Calc SIP2 CRC value
     * @param $buffer
     * @return string
     */
    private function crc($buffer)
    {
        $sum = 0;
        $len = strlen($buffer);
        for ($n = 0; $n < $len; $n++) {
            $sum = $sum + ord($buffer[$n]);
        }
        $crc = ($sum & 0xFFFF) * -1;
        return substr(sprintf("%4X", $crc), -4, 4);
    }

    /**
     * This helper creates a socket factory we can pass to the client. The factory returns a mock
     * socket which will return a sequence of responses after each write() to the socket. This allows
     * us to easily simulate SIP2 server responses
     *
     * @param array $responses
     * @return \Socket\Raw\Factory
     */
    protected function createMockSIP2Server(array $responses)
    {
        $socket = $this->prophesize(\Socket\Raw\Socket::class);

        //our mock socket will send each given response in sequence after each write() call
        $socket->responses = $responses;
        $socket->responseIdx = -1;
        $socket->responseOffset = 0;
        $socket->responseLength = 0;

        $socket->write(Argument::type('string'))->will(function ($args) use ($socket) {
            //printf("write(%s)\n", $args[0]);

            //next call to recv will start returning our next canned response
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

        $socket->close()->willReturn(true);

        $socket->connect(Argument::type('string'))->willReturn(true);
        $socket->connectTimeout(Argument::type('string'), Argument::any())->willReturn(true);
        $socket->setBlocking(Argument::any())->willReturn(true);
        $socket->bind(Argument::type('string'))->willReturn(true);

        //our factory just returns our mock
        $factory = $this->prophesize(\Socket\Raw\Factory::class);
        $factory->createFromString(
            Argument::type('string'),
            Argument::any()
        )->willReturn($socket->reveal());

        return $factory->reveal();
    }

    /**
     * Checks fixed data in patron info is present and valid
     * @param string $expected expected value
     * @param array $info info array returned from parsePatronInfoResponse
     * @param string $name name of element
     */
    protected function assertFixedMetadata($expected, array $info, $name)
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
    protected function assertVariableMetadata($expected, array $info, $name)
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
