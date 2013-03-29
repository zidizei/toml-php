<?php

class TomlTest extends PHPUnit_Framework_TestCase
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

    public function testParseFile1()
    {
        $p = Toml::parseFile('tests/example.toml');
        $this->assertEquals('TOML Example', $p['title']);
    }

    public function testParseFile2()
    {
        $p = Toml::parseFile('tests/example.toml');
        $this->assertEquals('192.168.1.1', $p['database']['server']);
    }

    public function testParseArray()
    {
        $p = Toml::parse('test = [12, 23, 45]');
        $this->assertEquals(array(12, 23, 45), $p['test']);
    }

    public function testParseMultiArray()
    {
        $p = Toml::parse('test = [[12, 84], ["lorem", "ipsum"], [2.3, 4.4]]');
        $this->assertEquals(array(array(12, 84), array("lorem", "ipsum"), array(2.3, 4.4)), $p['test']);
    }


    public function testParseMultiLineArray()
    {
        $p = Toml::parse('test = [
            [12, 84],
            ["lorem", "ipsum"],
            [2.3, 4.4]
        ]');
        $this->assertEquals(array(array(12, 84), array("lorem", "ipsum"), array(2.3, 4.4)), $p['test']);
    }

    public function testParseMultiLineArray2()
    {
        $p = Toml::parse('test = [
            [
                12,
                84],
            ["lorem",
            "ipsum"
            ],
            [2.3, 4.4]
        ]');
        $this->assertEquals(array(array(12, 84), array("lorem", "ipsum"), array(2.3, 4.4)), $p['test']);
    }

    public function testParseMultiLineArray3()
    {
        $p = Toml::parse('test = [
            "lorem", "ipsum"
        ]');
        $this->assertEquals(array("lorem", "ipsum"), $p['test']);
    }

    public function testParseMultiLineArrayTrailingComma()
    {
        $p = Toml::parse('test = [
            "lorem", "ipsum",
        ]');
        $this->assertEquals(array("lorem", "ipsum"), $p['test']);
    }

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
        $p = Toml::parse('test = false');
        $this->assertInternalType('bool', $p['test']);
    }

    public function testParseDate()
    {
        $p = Toml::parse('test = 1979-05-27T07:32:00Z');
        $this->assertEquals(array('test' => new \Datetime('1979-05-27T07:32:00Z')), $p);
    }

    public function testKeyGroup()
    {
        $p = Toml::parse("[test]\nalpha = 1\nbeta = 2");
        $this->assertEquals(array('test' => array('alpha' => 1, 'beta' => 2)), $p);
    }
    
    public function testNestedKeyGroup()
    {
        $p = Toml::parse("[test.nested]\nalpha = 1\nbeta = 2");
        $this->assertEquals(array('test' => array('nested' => array('alpha' => 1, 'beta' => 2))), $p);
    }

    public function testEmptyKeyGroup()
    {
        $p = Toml::parse("[test]");
        $this->assertEquals(array('test' => null), $p);
    }

    public function testEmptyNestedKeyGroup()
    {
        $p = Toml::parse("[test.alpha]");
        $this->assertEquals(array('test' => array('alpha' => null)), $p);
    }

    public function testComments()
    {
        $p = Toml::parse('title = "TOML Example" # test');
        $this->assertEquals(array('title' => "TOML Example"), $p);
    }

    public function testCommentInString()
    {
        $p = Toml::parse('title = "TOML # Example"');
        $this->assertEquals(array('title' => "TOML # Example"), $p);
    }

    public function testCommentInArray()
    {
        $p = Toml::parse('title = ["TOML # Example", "Test"]');
        $this->assertEquals(array('title' => array("TOML # Example", "Test")), $p);
    }

    public function testCommentAtMultilineArray()
    {
        $p = Toml::parse("test = [\n1,\n2 # test\n]");
        $this->assertEquals(array('test' => array(1, 2)), $p);
    }
    
    public function testZeroInt()
    {
        $p = Toml::parseFile('test = 0');
        $this->assertEquals(0, $p['test']);
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
