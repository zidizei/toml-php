# TOML for PHP

Parsing [TOML](https://github.com/mojombo/toml) with PHP. Supports TOML Specs [0.2.0](https://github.com/mojombo/toml/blob/master/versions/toml-v0.2.0.md).

# Installation

## Composer
Use [Composer](http://getcomposer.org) and add this to your `composer.json`:

    "require": {
        "zidizei/toml-php", "dev-master"
    }

## Manual Download
If you want you can just download/clone the source code from GitHub as well. Just include the `src/Toml/Toml.php` file in your application and you should be good to go.

# Usage

There are two static functions available to parse your TOML. The following will parse a TOML-formatted string:

    $array = Toml::parse('title = "TOML Example"');

To parse a TOML file you can use:

    $array = Toml::parseFile('tests/example.toml');

# Tests

Tests are done using [PHPUnit](http://phpunit.de/) and [Composer](http://getcomposer.org)'s autoloader (`vendor/autoload.php` is bootstrapped by *phpunit* to include the TOML parser class to be tested):

```
composer install
phpunit
```
