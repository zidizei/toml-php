# TOML for PHP

Parsing [TOML](https://github.com/mojombo/toml) with PHP. Supports TOML Specs up to [6303a80](https://github.com/mojombo/toml/tree/6303a809242307e9591ea26e8cd1ee87fef4ce45).

# Installation

## Composer
Use [Composer](http://getcomposer.org) and add this to your `composer.json`:

    "require": {
        "zidizei/toml-php", "dev-master"
    }

## Manual Download
If you want you can just download/clone the source code from GitHub as well. Just include the `src/Toml.php` file in your application and you should be good to go.

# Usage

There are two static functions available to parse your TOML. The following will parse a TOML-formatted string:

    $array = Toml::parse('title = "TOML Example"');

To parse a TOML file you can use:

    $array = Toml::parseFile('tests/example.toml');

# Tests

Unit tests are included, so run `phpunit` to see for yourself.