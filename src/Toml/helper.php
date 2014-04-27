<?php
namespace Toml;

/**
 * Helper function for parsing Toml strings
 * or files.
 *
 * @param string $toml Specifies the Toml
 *                     formatted String or
 *                     the path to a Toml file
 *                     to be parsed.
 */
function parse ($toml)
{
    if (!is_string($toml)) {
        throw new \InvalidArgumentException('Please specify a Toml formatted String or a file path to a Toml document');
    }

    $path = pathinfo($toml);

    if (isset($path['extension']) && strtolower($path['extension']) == 'toml') {
        return Toml::parseFile($toml);
    }

    return Toml::parse($toml);
}
