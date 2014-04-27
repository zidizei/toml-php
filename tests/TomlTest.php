<?php
namespace Toml;

class TomlTest extends \PHPUnit_Framework_TestCase
{
    public function testParseFile1()
    {
        $p = Toml::parseFile('tests/examples/example.toml');
        $this->assertEquals('TOML Example', $p['title']);
    }

    public function testParseFile2()
    {
        $p = Toml::parseFile('tests/examples/example.toml');
        $this->assertEquals('192.168.1.1', $p['database']['server']);
    }

    public function testParseFile3()
    {
        $p = Toml::parseFile('tests/examples/hard_example.toml');
        $this->assertEquals("You'll hate me after this - #", $p['the']['test_string']);
    }

    /**
    * @expectedException Exception
    */
    public function testAvoidDuplicates()
    {
        $p = Toml::parse("test = 12\ntest = 24");
    }

    /**
    * @expectedException UnexpectedValueException
    */
    public function testAvoidMixedTypeArrays()
    {
        $p = Toml::parse('test = [1, "gamme"]');
    }

    /**
    * @expectedException UnexpectedValueException
    */
    public function testUnkownDatatype()
    {
        $p = Toml::parse("test = unknown");
    }

    /**
    * @expectedException UnexpectedValueException
    */
    public function testInvalidSyntax()
    {
        $p = Toml::parse("test unknown");
    }

    /**
    * @expectedException UnexpectedValueException
    */
    public function testInvalidSyntax2()
    {
        $p = Toml::parse("[test] invalid");
    }

    /**
    * @expectedException InvalidArgumentException
    */
    public function testInvalidFile()
    {
        $p = Toml::parseFile('notfound.toml');
    }

    /**
    * @expectedException UnexpectedValueException
    */
    public function testNullNotAllowed()
    {
        $p = Toml::parse("test =");
    }
}
