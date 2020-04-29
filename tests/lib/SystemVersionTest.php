<?php

class SystemVersionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    }

    /**
     * Test if SystemVersion detects if a Version is a stable one.
     */
    public function test_stable()
    {
        $version = new SystemVersion("0.3.4");
        $this->assertEquals($version->get_version_type(), "stable");
    }

    /*
     * Test if SystemVersion detects if a Version is a unstable one.
     */
    public function test_unstable()
    {
        $version = new SystemVersion("0.3.4.RC1");
        $this->assertEquals($version->get_version_type(), "unstable");
    }

    /**
     * @expectedException Exception
     */
    public function test_invalidVersion()
    {
        //$this->expectException(Exception::class);
        $version = new SystemVersion("04.23.3R3.4");
    }

    /*
     * Test the compare between two Versions
     */
    public function test_compare_newer()
    {
        $ver1 = new SystemVersion("0.2.1");
        $ver2 = new SystemVersion("0.2.2");
        $this->assertFalse($ver1->is_newer_than($ver2));
    }

    public function test_compare_older()
    {
        $ver1 = new SystemVersion("0.2.1");
        $ver2 = new SystemVersion("0.2.2");
        $this->assertFalse($ver1->is_newer_than($ver2));
    }

    public function test_compare_equals()
    {
        $ver1 = new SystemVersion("0.2.1");
        $ver2 = new SystemVersion("0.2.1");
        $this->assertFalse($ver1->is_newer_than($ver2));
    }

    /**
     * Test the compare between two RC versions.
     */
    public function test_compare_rc()
    {
        $ver1 = new SystemVersion("0.2.1.RC1");
        $ver2 = new SystemVersion("0.2.2.RC2");
        $this->assertTrue($ver2->is_newer_than($ver1));
    }

    /**
     * Test the compare between a RC version and a final one.
     */
    public function test_compare_rc_stable()
    {
        $ver1 = new SystemVersion("0.2.1.RC1");
        $ver2 = new SystemVersion("0.2.1");
        $this->assertTrue($ver2->is_newer_than($ver1));
    }
}
