<?php

class TomlArrayTest extends PHPUnit_Framework_TestCase {

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

}
