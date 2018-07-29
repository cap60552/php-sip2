<?php

namespace lordelph\SIP2\Request;

/**
 * RequestACSResendRequest requests the ACS to re-transmit its last message. It is sent by the SC to the ACS when the
 * checksum in a received message does not match the value calculated by the SC. The ACS should respond by
 * re-transmitting its last message, This message should never include a “sequence number” field, even when error
 * detection is enabled, but would include a “checksum” field since checksums are in use.
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class RequestACSResendRequest extends SIP2Request
{
    public function getMessageString($withSeq = true, $withCrc = true): string
    {
        $this->newMessage('97');
        return $this->returnMessage($withSeq, $withCrc);
    }
}
