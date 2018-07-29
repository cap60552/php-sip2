<?php

namespace lordelph\SIP2\Response;

/**
 * Class RenewAllResponse provides the response from a RenewAllRequest
 *
 * @method getOk()
 * @method getRenewed()
 * @method getUnrenewed()
 * @method getTransactionDate()
 * @method getInstitutionId()
 * @method getRenewedItems()
 * @method getUnrenewedItems()
 * @method getScreenMessage()
 * @method getPrintLine()
 * @method getSequenceNumber()
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class RenewAllResponse extends SIP2Response
{
    //fixed part of response contains these
    protected $var = [
        'Ok' => [],
        'Renewed' => [],
        'Unrenewed' => [],
        'TransactionDate' => []
    ];

    //variable part of the response allowed to contain these...
    protected $allowedVariables=[
        self::AO_INSTITUTION_ID,
        self::BM_RENEWED_ITEMS,
        self::BN_UNRENEWED_ITEMS,
        self::AF_SCREEN_MESSAGE,
        self::AG_PRINT_LINE,
        self::AY_SEQUENCE_NUMBER
    ];

    public function __construct($raw)
    {
        $this->setVariable('Ok', substr($raw, 2, 1));
        $this->setVariable('Renewed', substr($raw, 3, 4));
        $this->setVariable('Unrenewed', substr($raw, 7, 4));
        $this->setVariable('TransactionDate', substr($raw, 11, 18));
        $this->parseVariableData($raw, 29);
    }
}
