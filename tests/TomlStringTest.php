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
        $p = Toml::parse('title = "TOML\nexample"');
        $this->assertEquals(array('title' => "TOML\nexample"), $p);
    }

    public function testLiteralString()
    {
        $p = Toml::parse('title = \'C:\Users\nodejs\templates\'');
        $this->assertEquals(array('title' => 'C:\Users\nodejs\templates'), $p);
    }

}
