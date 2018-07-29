<?php

namespace lordelph\SIP2\Exception;

/**
 * LogicException represents an integration problem - the code is being used incorrectly
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class LogicException extends \LogicException implements SIP2ClientException
{
}
