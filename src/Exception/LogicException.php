<?php

namespace lordelph\SIP2\Exception;

/**
 * Class LogicException represents an integration problem - the code is being used incorrectly
 */
class LogicException extends \LogicException implements SIP2ClientException
{
}
