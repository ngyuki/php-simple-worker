<?php
namespace ngyuki\Tests;

use ngyuki\SimpleWorker\PidFileFlock;

class PidFileFlockTest extends \PHPUnit_Framework_TestCase
{
    private $fn;
    
    public function setUp()
    {
        $this->fn = sys_get_temp_dir() . DIRECTORY_SEPARATOR .  'SimpleWorker-PidFileFlock.pid';
        
        if (file_exists($this->fn))
        {
            unlink($this->fn);
        }
    }
    
    /**
     * @test
     */
    public function test()
    {
        $this->assertFileNotExists($this->fn);
        
        $pidfile = new PidFileFlock($this->fn);
        
        $this->assertFileExists($this->fn);
        $this->assertEquals(getmypid(), file_get_contents($this->fn));
        
        $fp = fopen($this->fn, "r");
        $ret = flock($fp, LOCK_EX | LOCK_NB, $wouldblock);
        fclose($fp);
        
        $this->assertFalse($ret);
        $this->assertEquals(1, $wouldblock);
    }
    
    /**
     * @test
     * @errorHandler disabled
     * @expectedException RuntimeException
     * @expectedExceptionMessage unable create file
     */
    public function unable_fopen()
    {
        $this->iniSet("display_errors", false);
        $pidfile = new PidFileFlock(__DIR__);
    }
    
    /**
     * @test
     * @errorHandler disabled
     * @expectedException RuntimeException
     * @expectedExceptionMessage already started
     */
    public function already_started()
    {
        $pidfile1 = new PidFileFlock($this->fn);
        $pidfile2 = new PidFileFlock($this->fn);
    }
    
    /**
     * @test
     */
    public function release()
    {
        $pidfile = new PidFileFlock($this->fn);
        $this->assertFileExists($this->fn);
        
        $pidfile->release();
        $this->assertFileNotExists($this->fn);
    }
}
