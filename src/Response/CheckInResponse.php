<?php

namespace lordelph\SIP2\Response;

/**
 * Class CheckInResponse provides the response from a CheckInRequest
 *
 * @method getOk()
 * @method getResensitize()
 * @method getMagnetic()
 * @method getAlert()
 * @method getTransactionDate()
 * @method getInstitutionId()
 * @method getItemIdentifier()
 * @method getPermanentLocation()
 * @method getTitleIdentifier()
 * @method getSortBin()
 * @method getPatronIdentifier()
 * @method getMediaType()
 * @method getItemProperties()
 * @method getScreenMessage()
 * @method getPrintLine()
 * @method getSequenceNumber()
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
class CheckInResponse extends SIP2Response
{
    //fixed part of response contains these
    protected $var = [
        'Ok' => [],
        'Resensitize' => [],
        'Magnetic' => [],
        'Alert' => [],
        'TransactionDate' => []
    ];

    //variable part of the response allowed to contain these...
    protected $allowedVariables=[
        self::AO_INSTITUTION_ID,
        self::AB_ITEM_IDENTIFIER,
        self::AQ_PERMANENT_LOCATION,
        self::AJ_TITLE_IDENTIFIER,
        self::CL_SORT_BIN,
        self::AA_PATRON_IDENTIFIER,
        self::CK_MEDIA_TYPE,
        self::CH_ITEM_PROPERTIES,
        self::AF_SCREEN_MESSAGE,
        self::AG_PRINT_LINE,
        self::AY_SEQUENCE_NUMBER
    ];

    public function __construct($raw)
    {
        $this->setVariable('Ok', substr($raw, 2, 1));
        $this->setVariable('Resensitize', substr($raw, 3, 1));
        $this->setVariable('Magnetic', substr($raw, 4, 1));
        $this->setVariable('Alert', substr($raw, 5, 1));
        $this->setVariable('TransactionDate', substr($raw, 6, 18));

        $this->parseVariableData($raw, 24);
    }
}
