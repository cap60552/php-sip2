<?php

namespace lordelph\SIP2\Exception;

/**
 * Class RuntimeException is fired for conditions which arise only at runtime, e.g. external services being down
 * bad CRCs from remote services
 */
class RuntimeException extends \RuntimeException implements SIP2ClientException
{
}
