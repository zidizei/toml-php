<?php
namespace Toml;

class TomlTest extends \PHPUnit_Framework_TestCase
{
    public function testParseFile1()
    {
        $p = parse('tests/examples/example.toml');
        $this->assertEquals('TOML Example', $p['title']);
    }

    public function testParseFile2()
    {
        $p = parse('tests/examples/example.toml');
        $this->assertEquals('192.168.1.1', $p['database']['server']);
    }

    public function testParseFile3()
    {
        $p = parse('tests/examples/hard_example.toml');
        $this->assertEquals("You'll hate me after this - #", $p['the']['test_string']);
    }

    /**
    * @expectedException Exception
    */
    public function testAvoidDuplicates()
    {
        $p = parse("test = 12\ntest = 24");
    }

    /**
    * @expectedException UnexpectedValueException
    */
    public function testAvoidMixedTypeArrays()
    {
        $p = parse('test = [1, "gamme"]');
    }

    /**
    * @expectedException UnexpectedValueException
    */
    public function testUnkownDatatype()
    {
        $p = parse("test = unknown");
    }

    /**
    * @expectedException UnexpectedValueException
    */
    public function testInvalidSyntax()
    {
        $p = parse("test unknown");
    }

    /**
    * @expectedException UnexpectedValueException
    */
    public function testInvalidSyntax2()
    {
        $p = parse("[test] invalid");
    }

    /**
    * @expectedException InvalidArgumentException
    */
    public function testInvalidFile()
    {
        $p = parse('notfound.toml');
    }

    /**
    * @expectedException InvalidArgumentException
    */
    public function testInvalidArgument()
    {
        $p = parse(0);
    }

    /**
    * @expectedException UnexpectedValueException
    */
    public function testNullNotAllowed()
    {
        $p = parse("test =");
    }
}
