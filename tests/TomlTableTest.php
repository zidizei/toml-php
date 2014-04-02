<?php

class TomlTableTest extends PHPUnit_Framework_TestCase {

    public function testTable()
    {
        $p = Toml::parse("[test]\nalpha = 1\nbeta = 2");
        $this->assertEquals(array('test' => array('alpha' => 1, 'beta' => 2)), $p);
    }

    public function testNestedTable()
    {
        $p = Toml::parse("[test.nested]\nalpha = 1\nbeta = 2");
        $this->assertEquals(array('test' => array('nested' => array('alpha' => 1, 'beta' => 2))), $p);
    }

    public function testEmptyTable()
    {
        $p = Toml::parse("[test]");
        $this->assertEquals(array('test' => null), $p);
    }

    public function testEmptyNestedTable()
    {
        $p = Toml::parse("[test.alpha]");
        $this->assertEquals(array('test' => array('alpha' => null)), $p);
    }

}
