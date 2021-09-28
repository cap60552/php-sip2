<?php

namespace lordelph\SIP2\Response;

use lordelph\SIP2\Exception\LogicException;
use lordelph\SIP2\Exception\RuntimeException;
use lordelph\SIP2\SIP2Client;
use lordelph\SIP2\SIP2Message;

/**
 * Class SIP2Response provides a base class for responses and a factory method for constructing them
 *
 * Derived classes declare the variable data they expect to receive, and provide a parser for the 'fixed'
 * fields
 *
 * @licence    https://opensource.org/licenses/MIT
 * @copyright  John Wohlers <john@wohlershome.net>
 * @copyright  Paul Dixon <paul@elphin.com>
 */
abstract class SIP2Response extends SIP2Message
{
    const AA_PATRON_IDENTIFIER = 'AA';
    const AB_ITEM_IDENTIFIER = 'AB';
    const AE_PERSONAL_NAME = 'AE';
    const AF_SCREEN_MESSAGE = 'AF';
    const AG_PRINT_LINE = 'AG';
    const AH_DUE_DATE = 'AH';
    const AJ_TITLE_IDENTIFIER = 'AJ';
    const AM_LIBRARY_NAME='AM';
    const AN_TERMINAL_LOCATION='AN';
    const AO_INSTITUTION_ID = 'AO';
    const AP_CURRENT_LOCATION = 'AP';
    const AQ_PERMANENT_LOCATION='AQ';
    const AS_HOLD_ITEMS = 'AS';
    const AT_OVERDUE_ITEMS = 'AT';
    const AU_CHARGED_ITEMS = 'AU';
    const AV_FINE_ITEMS = 'AV';
    const AY_SEQUENCE_NUMBER = 'AY';
    const BD_HOME_ADDRESS = 'BD';
    const BE_EMAIL_ADDRESS = 'BE';
    const BF_HOME_PHONE_NUMBER = 'BF';
    const BG_OWNER = 'BG';
    const BH_CURRENCY_TYPE = 'BH';
    const BK_TRANSACTION_ID= 'BK';
    const BL_VALID_PATRON = 'BL';
    const BM_RENEWED_ITEMS = 'BM';
    const BN_UNRENEWED_ITEMS = 'BN';
    const BR_QUEUE_POSITION = 'BR';
    const BS_PICKUP_LOCATION = 'BS';
    const BT_FEE_TYPE = 'BT';
    const BU_RECALL_ITEMS = 'BU';
    const BV_FEE_AMOUNT = 'BV';
    const BW_EXPIRATION_DATE = 'BW';
    const BX_SUPPORTED_MESSAGES='BX';
    const BZ_HOLD_ITEMS_LIMIT = 'BZ';
    const CA_OVERDUE_ITEMS_LIMIT = 'CA';
    const CB_CHARGED_ITEMS_LIMIT = 'CB';
    const CC_FEE_LIMIT = 'CC';
    const CD_UNAVAILABLE_HOLD_ITEMS = 'CD';
    const CF_HOLD_QUEUE_LENGTH = 'CF';
    const CH_ITEM_PROPERTIES= 'CH';
    const CI_SECURITY_INHIBIT = 'CI';
    const CJ_RECALL_DATE = 'CJ';
    const CK_MEDIA_TYPE= 'CK';
    const CL_SORT_BIN='CL';
    const CM_HOLD_PICKUP_DATE = 'CM';
    const CQ_VALID_PATRON_PASSWORD = 'CQ';

    /** @var array maps SIP2 numeric response codes onto response classes */
    private static $mapResponseToClass = [
        '10' => CheckInResponse::class,
        '12' => CheckOutResponse::class,
        '16' => HoldResponse::class,
        '18' => ItemInformationResponse::class,
        '20' => ItemStatusUpdateResponse::class,
        '24' => PatronStatusResponse::class,
        '26' => PatronEnableResponse::class,
        '30' => RenewResponse::class,
        '36' => EndSessionResponse::class,
        '38' => FeePaidResponse::class,
        '64' => PatronInformationResponse::class,
        '66' => RenewAllResponse::class,
        '94' => LoginResponse::class,
        '98' => ACSStatusResponse::class,
    ];

    /** @var array maps SIP2 variable code names to a definition */
    private static $mapCodeToVarDef = [
        self::AA_PATRON_IDENTIFIER => ['name' => 'PatronIdentifier', 'default'=>''],
        self::AB_ITEM_IDENTIFIER => ['name' => 'ItemIdentifier', 'default'=>''],
        self::AE_PERSONAL_NAME => ['name' => 'PersonalName', 'default'=>''],
        self::AF_SCREEN_MESSAGE => ['name' => 'ScreenMessage', 'type' => 'array', 'default'=>[]],
        self::AG_PRINT_LINE => ['name' => 'PrintLine', 'type' => 'array', 'default'=>[]],
        self::AH_DUE_DATE => ['name' => 'DueDate', 'default'=>''],
        self::AJ_TITLE_IDENTIFIER => ['name' => 'TitleIdentifier', 'default'=>''],
        self::AM_LIBRARY_NAME => ['name' => 'LibraryName', 'default'=>''],
        self::AN_TERMINAL_LOCATION => ['name' => 'TerminalLocation', 'default'=>''],
        self::AO_INSTITUTION_ID => ['name' => 'InstitutionId', 'default'=>''],
        self::AP_CURRENT_LOCATION => ['name' => 'CurrentLocation', 'default'=>''],
        self::AQ_PERMANENT_LOCATION => ['name' => 'PermanentLocation', 'default'=>''],
        self::AS_HOLD_ITEMS => ['name' => 'HoldItems', 'type' => 'array', 'default'=>[]],
        self::AT_OVERDUE_ITEMS => ['name' => 'OverdueItems', 'type' => 'array', 'default'=>[]],
        self::AU_CHARGED_ITEMS => ['name' => 'ChargedItems', 'type' => 'array', 'default'=>[]],
        self::AV_FINE_ITEMS => ['name' => 'FineItems', 'type' => 'array', 'default'=>[]],
        self::AY_SEQUENCE_NUMBER => ['name' => 'SequenceNumber', 'default'=>''],
        self::BD_HOME_ADDRESS => ['name' => 'HomeAddress', 'default'=>''],
        self::BE_EMAIL_ADDRESS => ['name' => 'EmailAddress', 'default'=>''],
        self::BF_HOME_PHONE_NUMBER => ['name' => 'HomePhoneNumber', 'default'=>''],
        self::BG_OWNER => ['name' => 'Owner', 'default'=>''],
        self::BH_CURRENCY_TYPE => ['name' => 'CurrencyType', 'default'=>''],
        self::BK_TRANSACTION_ID => ['name' => 'TransactionId', 'default'=>''],
        self::BL_VALID_PATRON => ['name' => 'ValidPatron', 'default'=>''],
        self::BM_RENEWED_ITEMS => ['name' => 'RenewedItems', 'type' => 'array', 'default'=>[]],
        self::BN_UNRENEWED_ITEMS => ['name' => 'UnrenewedItems', 'type' => 'array', 'default'=>[]],
        self::BR_QUEUE_POSITION => ['name' => 'QueuePosition', 'default'=>''],
        self::BS_PICKUP_LOCATION => ['name' => 'PickupLocation', 'default'=>''],
        self::BT_FEE_TYPE => ['name' => 'FeeType', 'default'=>''],
        self::BU_RECALL_ITEMS => ['name' => 'RecallItems', 'type' => 'array', 'default'=>[]],
        self::BV_FEE_AMOUNT => ['name' => 'FeeAmount', 'default'=>''],
        self::BW_EXPIRATION_DATE => ['name' => 'ExpirationDate', 'default'=>''],
        self::BX_SUPPORTED_MESSAGES => ['name' => 'SupportedMessages', 'default'=>''],
        self::BZ_HOLD_ITEMS_LIMIT => ['name' => 'HoldItemsLimit', 'default'=>''],
        self::CA_OVERDUE_ITEMS_LIMIT => ['name' => 'OverdueItemsLimit', 'default'=>''],
        self::CB_CHARGED_ITEMS_LIMIT => ['name' => 'ChargedItemsLimit', 'default'=>''],
        self::CC_FEE_LIMIT => ['name' => 'FeeLimit', 'default'=>''],
        self::CD_UNAVAILABLE_HOLD_ITEMS => ['name' => 'UnavailableHoldItems', 'type' => 'array', 'default'=>[]],
        self::CF_HOLD_QUEUE_LENGTH => ['name' => 'HoldQueueLength', 'default'=>''],
        self::CH_ITEM_PROPERTIES => ['name' => 'ItemProperties', 'default'=>''],
        self::CI_SECURITY_INHIBIT => ['name' => 'SecurityInhibit', 'default'=>''],
        self::CJ_RECALL_DATE => ['name' => 'RecallDate', 'default'=>''],
        self::CK_MEDIA_TYPE => ['name' => 'MediaType', 'default'=>''],
        self::CL_SORT_BIN => ['name' => 'SortBin', 'default'=>''],
        self::CM_HOLD_PICKUP_DATE => ['name' => 'HoldPickupDate', 'default'=>''],
        self::CQ_VALID_PATRON_PASSWORD => ['name' => 'ValidPatronPassword', 'default'=>'']
    ];

    protected $allowedVariables = [];

    public static function parse($raw): SIP2Response
    {
        if (empty($raw) || !self::checkCRC($raw)) {
            throw new LogicException("Empty string or bad CRC not expected here");//@codeCoverageIgnore
        }

        $type = substr($raw, 0, 2);
        if (!isset(self::$mapResponseToClass[$type])) {
            throw new RuntimeException("Unexpected SIP2 response $type");
        }

        //good to go
        $className = self::$mapResponseToClass[$type];
        return new $className($raw);
    }

    public static function checkCRC($raw)
    {
        if (!SIP2Client::isCRCCheckEnabled()) {
            //CRC checks are disabled
            return true;
        }
        if (preg_match('/^(.*AZ)(.{4})$/', trim($raw), $match)) {
            $plaintext=$match[1];
            $checksum=$match[2];
            return self::crc($plaintext) == $checksum;
        }

        //no checksum added to message
        return true;
    }

    protected function parseVariableData($response, $start)
    {
        //init allowed variables
        foreach ($this->allowedVariables as $code) {
            if (!isset(self::$mapCodeToVarDef[$code])) {
                throw new LogicException("Unexpected $code in allowed variables"); //@codeCoverageIgnore
            }
            $name = self::$mapCodeToVarDef[$code]['name'];
            if (!$this->hasVariable($name)) {
                //add a definition for this variable
                $this->var[$name] = self::$mapCodeToVarDef[$code];
            }
        }

        $items = explode("|", substr($response, $start, -7));

        foreach ($items as $item) {
            $value = substr($item, 2);

            //we ignore anything with no value
            $clean = trim($value, "\x00..\x1F");
            if ($clean==='') {
                continue;
            }

            $field = substr($item, 0, 2);

            //expected?
            if (!in_array($field, $this->allowedVariables)) {
                //we tolerate unexpected values and treat them as array types
                //named after the code if we don't have a definition for it
                if (!isset(self::$mapCodeToVarDef[$field])) {
                    self::$mapCodeToVarDef[$field]=[
                        'name' => $field,
                        'type' => 'array'
                    ];
                    $name=$field;
                } else {
                    $name = self::$mapCodeToVarDef[$field]['name'];
                }
                $this->var[$name] = self::$mapCodeToVarDef[$field];
            }

            $name = self::$mapCodeToVarDef[$field]['name'];
            $this->addVariable($name, $clean);
        }
    }
}
