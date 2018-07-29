<?php

namespace lordelph\SIP2\Response;

/**
 * Class ItemStatusUpdateResponse provides the response from a ItemStatusUpdateRequest
 *
 * @method getPropertiesOk()
 * @method getTransactionDate()
 * @method getItemIdentifier()
 * @method getTitleIdentifier()
 * @method getItemProperties()
 * @method getScreenMessage()
 * @method getPrintLine()
 * @method getSequenceNumber()
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class ItemStatusUpdateResponse extends SIP2Response
{
    //fixed part of response contains these
    protected $var = [
        'PropertiesOk' => [],
        'TransactionDate' => [],
    ];

    //variable part of the response allowed to contain these...
    protected $allowedVariables=[
        self::AB_ITEM_IDENTIFIER,
        self::AJ_TITLE_IDENTIFIER,
        self::CH_ITEM_PROPERTIES,
        self::AF_SCREEN_MESSAGE,
        self::AG_PRINT_LINE,
        self::AY_SEQUENCE_NUMBER
    ];

    public function __construct($raw)
    {
        $this->setVariable('PropertiesOk', substr($raw, 2, 1));
        $this->setVariable('TransactionDate', substr($raw, 3, 18));
        $this->parseVariableData($raw, 21);
    }
}
