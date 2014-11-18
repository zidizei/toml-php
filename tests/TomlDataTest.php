<?php
namespace Toml;

class TomlDataTest extends \PHPUnit_Framework_TestCase
{
    public function testParseInt()
    {
        $p = Toml::parse('test = 42');
        $this->assertInternalType('integer', $p['test']);
        $this->assertEquals(array('test' => 42), $p);
    }

    public function testPositiveInt()
    {
        $p = Toml::parse('test = +42');
        $this->assertInternalType('integer', $p['test']);
        $this->assertEquals(array('test' => 42), $p);
    }

    public function testNegativeInt()
    {
        $p = Toml::parse('test = -12');
        $this->assertInternalType('integer', $p['test']);
        $this->assertEquals(array('test' => -12), $p);
    }

    public function testParseFloat()
    {
        $p = Toml::parse('test = 4.2');
        $this->assertInternalType('float', $p['test']);
    }

    public function testParsePositiveFloat()
    {
        $p = Toml::parse('test = +4.2');
        $this->assertInternalType('float', $p['test']);
        $this->assertEquals(array('test' => 4.2), $p);
    }

    public function testParseNegativeFloat()
    {
        $p = Toml::parse('test = -4.2');
        $this->assertInternalType('float', $p['test']);
        $this->assertEquals(array('test' => -4.2), $p);
    }

    public function testParsePositiveExpFloat()
    {
        $p = Toml::parse('test = +6e+22');
        $this->assertInternalType('float', $p['test']);
        $this->assertEquals(array('test' => 6e+22), $p);
    }

    public function testParsePositiveExpFloat2()
    {
        $p = Toml::parse('test = 6e22');
        $this->assertInternalType('float', $p['test']);
        $this->assertEquals(array('test' => 6e+22), $p);
    }

    public function testParseNegativeExpFloat()
    {
        $p = Toml::parse('test = 6e-3');
        $this->assertInternalType('float', $p['test']);
        $this->assertEquals(array('test' => 6e-3), $p);
    }

    public function testParseNegativeExpFloat2()
    {
        $p = Toml::parse('test = -6e-3');
        $this->assertInternalType('float', $p['test']);
        $this->assertEquals(array('test' => -6e-3), $p);
    }

    public function testParseExpFloat()
    {
        $p = Toml::parse('test = 6E+3');
        $this->assertInternalType('float', $p['test']);
        $this->assertEquals(array('test' => 6e+3), $p);
    }

    public function testParseExpFloat2()
    {
        $p = Toml::parse('test = 6.42e+3');
        $this->assertInternalType('float', $p['test']);
        $this->assertEquals(array('test' => 6.42e+3), $p);
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

    public function testParseDate2()
    {
        $p = Toml::parse('test = 1979-05-27T00:32:00-07:00');
        $this->assertEquals(array('test' => new \Datetime('1979-05-27T00:32:00-07:00')), $p);
    }

    public function testParseDate3()
    {
        $p = Toml::parse('test = 1979-05-27T00:32:00.999999-07:00');
        $this->assertEquals(array('test' => new \Datetime('1979-05-27T00:32:00.999999-07:00')), $p);
    }

    public function testZeroInt()
    {
        $p = Toml::parse('test = 0');
        $this->assertEquals(0, $p['test']);
    }
}
