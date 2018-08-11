<?php

namespace cap60552\SIP2\Exception;

/**
 * RuntimeException is fired for conditions which arise only at runtime, e.g. external services being down
 * bad CRCs from remote services
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class RuntimeException extends \RuntimeException implements SIP2ClientException
{
}
