# Changelog

All notable changes to `php-sip2` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

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