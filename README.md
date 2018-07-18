# php-sip2

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]


PHP client library to facilitate communication with Integrated Library System (ILS) servers via 3M's SIP2.

## ToDo
- abstract socket into separate class https://github.com/clue/php-socket-raw
- make socket factory which can be given to SIP2Client
- now can make unit tests which simulate socket connections


## Install

Via Composer

``` bash
$ composer require lordelph/php-sip2
```

## Usage

``` php
// create object
$mysip = new lordelph\SIP2\SIP2Client;

// Set host name
$mysip->hostname = 'server.example.com';
$mysip->port = 6002;

// Identify a patron
$mysip->patron = '101010101';
$mysip->patronpwd = '010101';

// connect to SIP server 
$result = $mysip->connect();

// Get Charged Items Raw response
$in = $mysip->msgPatronInformation('charged');

// parse the raw response into an array
$result = $mysip->parsePatronInfoResponse( $mysip->getMessage($in) );
```

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
