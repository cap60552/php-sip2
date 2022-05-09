# Changelog

All notable changes to `php-sip2` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## 2.1.1 - 2022-05-09

### Changed
- Updated composer.json to allow php8 as test suite passes without modification

## 2.1.0 - 2021-09-28

### Added
- client allows CRC checks to be disabled with `SIP2Client::enableCRCCheck(false). 
  Some SIP2 server implementations have been observed to produce invalid CRCs when UTF-8 
  characters are involved. 

## 2.0.6 - 2020-11-16

### Fixed
- SIP2Response::getRawResponse now trims whitespace from the start and end of responses.
  It reads until it sees an 0x0D (CR) terminator, but if the remote side uses CR+LF, a 0x0A 
  will be in the buffer when we read the next response. By trimming the responses, we
  ensure this still produces a valid result.


## 2.0.5 - 2019-06-13

### Changed
- Temporarily disabled testing of php 7.2 from travis - one of the test cases needs a 
  `:void` return type which would cause the 7.0 and 7.1 tests to fail. 
  
## 2.0.4 - 2019-06-13

### Fixed
- SIP2Response::parse now correctly handles responses which do not include a checksum. 
  Previously, these were rejected, but it will now allow responses which don't include
  the optional checksum.


## 2.0.3 - 2018-09-19

### Fixed
- Fixed bug where receiving unexpected, but blank variable fields would cause an
  exception when calling getAll 

## 2.0.2 - 2018-08-20

### Fixed
- SIP2Client::connect binding fixed to avoid a connection attempt before binding


## 2.0.1 - 2018-08-01

### Added
- SIP2Client::connect now accepts a timeout parameter, default 15 seconds


## 2.0.0 - 2018-07-29

### Added
- MIT License adopted - prior releases were GPL
- PSR-2 formatting/naming conventions, including change of classname from sip2 to SIP2Client
- PSR-3 logger support
- Classes for each request and response type
- Support for binding to particular interface
- Full unit tests

### Deprecated
- Nothing

### Fixed
- Ensure client properly handles retries in event of CRC failure

### Removed
- original v1 classname changed
- original public methods and variables all removed - see [MIGRATION](MIGRATION.md)

### Security
- Nothing


## 1.0.0 - 2015-11-03

- First release, GPL licensed
