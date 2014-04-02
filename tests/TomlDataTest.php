<?php

class TomlDataTest extends PHPUnit_Framework_TestCase {

    public function testParseInt()
    {
        $p = Toml::parse('test = 42');
        $this->assertInternalType('integer', $p['test']);
    }

    public function testParseFloat()
    {
        $p = Toml::parse('test = 4.2');
        $this->assertInternalType('float', $p['test']);
    }

    public function testParseBool()
    {
        $p = Toml::parse('test = true');
        $this->assertInternalType('bool', $p['test']);
    }

    public function testParseDate()
    {
        $p = Toml::parse('test = 1979-05-27T07:32:00Z');
        $this->assertEquals(array('test' => new \Datetime('1979-05-27T07:32:00Z')), $p);
    }

    public function testZeroInt()
    {
        $p = Toml::parse('test = 0');
        $this->assertEquals(0, $p['test']);
    }

}
