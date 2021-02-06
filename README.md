# PHP OAuth2 Client Library
[![Packagist](https://img.shields.io/packagist/v/jwilsson/oauth2-client.svg)](https://packagist.org/packages/jwilsson/oauth2-client)
![build](https://github.com/jwilsson/php-oauth2-client/workflows/build/badge.svg)
[![Coverage Status](https://coveralls.io/repos/jwilsson/php-oauth2-client/badge.svg?branch=master)](https://coveralls.io/r/jwilsson/php-oauth2-client?branch=master)

## Features
* Fully supports modern OAuth2 grant types:
    * Authorization Code
    * Client Credentials
    * Proof Key for Code Exchange (PKCE)
    * Refresh Token
* Full utilization of PSR-7, PSR-17, and PSR-18.
* Compatible with PSR-4 autoloading.

## Requirements
* PHP 8.0 or later.
* A [PSR-18 HTTP client](https://packagist.org/providers/php-http/client-implementation).
* A [PSR-7 implementation](https://packagist.org/providers/psr/http-message-implementation).

## Installation
Via Composer:

```bash
composer require jwilsson/oauth2-client
```

## Usage
See the [`docs`](docs/) folder for complete usage information.
