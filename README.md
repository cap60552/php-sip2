# SIP2 communication library for PHP

PHP class library to facilitate communication with Integrated Library System (ILS) servers via 3M's SIP2.


## Composer Installation

To install this package, run this command:
```sh
composer require cap60552/php-sip2
```

## General Installation
Copy the sip2.class.php file to a location in your php_include path.

## General Usage

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
	
## Contribution

Feel free to contribute!