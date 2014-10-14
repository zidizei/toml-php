# TOML for PHP

Parsing [TOML](https://github.com/mojombo/toml) with PHP. Supports TOML Specs [0.2.0](https://github.com/mojombo/toml/blob/master/versions/toml-v0.2.0.md).

[![Build Status](https://travis-ci.org/zidizei/toml-php.svg?branch=master)](https://travis-ci.org/zidizei/toml-php)

## Installation

Use [Composer](http://getcomposer.org) and add this to your `composer.json`:

    "require": {
        "zidizei/toml-php", "~0.3.0"
    }

If you want you can just download/clone the source code from GitHub as well. Just include the `src/Toml/Toml.php` file in your application and you should be good to go.

## Usage

The following will parse a TOML formatted String:

    $array = \Toml\parse('title = "TOML Example"');

To parse a TOML file, you can just use the same helper function:

    $array = \Toml\parse('tests/example.toml');

## Tests

Tests are done using [PHPUnit](http://phpunit.de/) and [Composer](http://getcomposer.org)'s autoloader (`vendor/autoload.php` is bootstrapped by *phpunit* to include the TOML parser class to be tested):

```
composer install
phpunit
```
