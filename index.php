<?php
require_once "src/Toml.php";

/**
 * Parse TOML with PHP.
 *
 * Check the README for more information.
 */
$toml = Toml::parseFile("tests/example.toml");

print_r($toml);