<?php

class TomlTestComments extends PHPUnit_Framework_TestCase {

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

}
