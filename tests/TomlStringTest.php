<?php
namespace Toml;

class TomlStringTest extends \PHPUnit_Framework_TestCase
{

    public function testParseString()
    {
        $p = Toml::parse('title = "TOML example"');
        $this->assertEquals(array('title' => 'TOML example'), $p);
    }

    public function testParseMultiLineString()
    {
        $this->setExpectedException('Exception');
        $p = Toml::parse('title = "TOML
        example"');
    }

    public function testLiteralString()
    {
        $p = Toml::parse('title = \'C:\Users\nodejs\templates\'');
        $this->assertEquals(array('title' => 'C:\Users\nodejs\templates'), $p);
    }

}
