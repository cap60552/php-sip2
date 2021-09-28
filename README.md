# php-sip2

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


PHP client library to facilitate communication with Integrated Library System (ILS) servers via 3M's SIP2.

This is derived from [cap60552/php-sip2](https://github.com/cap60552/php-sip2) by John Wohlers, 
with following improvements:

* MIT license (with consent of original author who used GPL)
* Separate classes for each SIP2 request and response type
* Ability to bind to specific interface when making requests
* Full unit tests
* PSR-2 formatting conventions
* PSR-3 logging
* PSR-4 auto-loading

## Install

Via Composer

``` bash
$ composer require lordelph/php-sip2
```

## Example

Here's a typical example of use 
```php
use lordelph\SIP2\SIP2Client;
use lordelph\SIP2\Request\PatronInformationRequest;

// instantiate client, set any defaults used for all requests,
// typically you might set the PatronIdentifier and PatronPassword
// so that you don't have to set this for every request
$mysip = new SIP2Client;
$mysip->setDefault('PatronIdentifier', '101010101');
$mysip->setDefault('PatronPassword', '010101');

// connect to SIP server 
$mysip->connect("server.example.com:6002");

// to make a request, instantiate relevant request class
// and configure as appropriate
$request=new PatronInformationRequest();
$request->setType('charged');

// send the request, obtaining, in this case a
// PatronInformationResponse object
$response = $mysip->sendRequest($request);

// now we can obtain information from the result object
$status = $response->getPatronStatus();
$name = $response->getPersonalName();

```

## SIP2 requests and responses

All requests defined in SIP2 are available - note that not all SIP2
services will support every request.


| Request  | Response |
| ------------- | ------------- |
| PatronStatusRequest  | PatronStatusResponse  |
| CheckOutRequest | CheckOutResponse |
| CheckInRequest | CheckInResponse |
| BlockPatronRequest | PatronStatusResponse |
| SCStatusRequest | ASCStatusResponse |
| RequestACSResendRequest | _previous response_ |
| LoginRequest | LoginResponse |
| PatronInformationRequest | PatronInformationResponse |
| EndPatronSessionRequest | EndSessionResponse |
| FeePaidRequest | FeePaidResponse |
| ItemInformationRequest | ItemInformationResponse |
| ItemStatusUpdateRequest | ItemStatusUpdateResponse |
| PatronEnableRequest | PatronEnableResponse |
| HoldRequest | HoldResponse |
| RenewRequest | RenewResponse |
| RenewAllRequest | RenewAllResponse |


## Migration from v1.0

See [MIGRATION](MIGRATION.md) for details.

## Binding to a specific local outbound address

If connecting to a SIP2 service over the internet, such services will usually be tightly firewalled
to specific IPs. If your client software is running on a machine with multiple outbound interfaces,
you may wish to pick the specific interface so that the SIP2 server sees the correct IP.

To do this, specify the IP with `bindTo` public member variable *before* calling `connect()`:


``` php
use lordelph\SIP2\SIP2Client;


// create object
$mysip = new SIP2Client;

// Set host name
$mysip->hostname = 'server.example.com';
$mysip->port = 6002;

//ensure outbound connections go from this IP address
$mysip->bindTo = '1.2.3.4';

// connect to SIP server 
$result = $mysip->connect();
```

## Disabling CRC checks

Some SIP2 server implementations can provide CRCs which are invalid. You can
configure the client to skip CRC checks with `SIP2Client::enableCRCCheck(false);`


## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email paul@elphin.com instead of using the 
issue tracker.

## Credits

- [John Wohlers][link-author1]
- [Paul Dixon][link-author2]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

Note that prior to v2.0.0, the GPL licence was used. The original author, John Wohlers, kindly
agreed to allow the MIT license terms.

[ico-version]: https://img.shields.io/packagist/v/lordelph/php-sip2.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/lordelph/php-sip2/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/lordelph/php-sip2.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/lordelph/php-sip2.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/lordelph/php-sip2.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/lordelph/php-sip2
[link-travis]: https://travis-ci.org/lordelph/php-sip2
[link-scrutinizer]: https://scrutinizer-ci.com/g/lordelph/php-sip2/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/lordelph/php-sip2
[link-downloads]: https://packagist.org/packages/lordelph/php-sip2
[link-author1]: https://github.com/cap60552
[link-author2]: https://github.com/lordelph
[link-contributors]: ../../contributors
