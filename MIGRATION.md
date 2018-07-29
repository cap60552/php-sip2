# Migration from v1.0

While this is derived from [cap60552/php-sip2](https://github.com/cap60552/php-sip2) 
many changes were made to make the code more easily testable and less complex. As a result,
this isn't a drop-in replacement. Here's the main differences for a typical scenaio:

## Before
```php
// create object
$mysip = new sip2;

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
$result = $mysip->parsePatronInfoResponse( $mysip->get_message($in) );

// extra data from result
$status = $result['fixed']['PatronStatus'];
$name = $result['variable']['AE'];
```

## After
```php
use lordelph\SIP2\SIP2Client;
use lordelph\SIP2\Request\PatronInformationRequest;

// instantiate client, set any defaults used for all requests
$mysip = new SIP2Client;
$mysip->setDefault('PatronIdentifier', '101010101');
$mysip->setDefault('PatronPassword', '010101');

// connect to SIP server 
$mysip->connect("server.example.com:6002");

// Get Charged Items Raw response
$request=new PatronInformationRequest();
$request->setType('charged');

$response = $mysip->sendRequest($request);

// extra data from result
$status = $response->getPatronStatus();
$name = $response->getPersonalName();

```