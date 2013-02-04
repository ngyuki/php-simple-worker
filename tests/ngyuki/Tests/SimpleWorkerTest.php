<?php
namespace ngyuki\Tests;

use ngyuki\SimpleWorker\SimpleWorker;

class SimpleWorkerTest extends \PHPUnit_Framework_TestCase
{
    private $fn;
    private $worker;

    public function setUp()
    {
        $this->fn = sys_get_temp_dir() . DIRECTORY_SEPARATOR .  'SimpleWorker-SimpleWorkerTest.pid';

        if (file_exists($this->fn))
        {
            unlink($this->fn);
        }
    }

    protected function tearDown()
    {
        if ($this->worker)
        {
            $this->worker->fin();
        }
    }

    /**
     * @test
     */
    public function test()
    {
        $this->worker = new SimpleWorker($this->fn);
        $this->worker->init();

        $this->assertEquals($this->worker->getPidFile(), $this->fn);
        $this->assertEquals(file_get_contents($this->worker->getPidFile()), getmypid());
    }

    /**
     * @test
     */
    public function test_pidfile_not_specified()
    {
        $this->worker = new SimpleWorker();
        $this->worker->init();

        $this->assertEquals(file_get_contents($this->worker->getPidFile()), getmypid());
    }

    /**
     * @test
     */
    public function logger()
    {
        $logs = array();

        $this->worker = new SimpleWorker();
        $this->worker->setLogger(function ($log) use (&$logs) { $logs[] = $log;} );
        $this->worker->init();

        list ($log) = $logs;
        $this->assertContains("init worker", $log);
    }

    /**
     * @test
     */
    public function wait()
    {
        $this->worker = new SimpleWorker();
        $this->worker->init();

        // 開始時刻
        $time = microtime(true);

        $ret = $this->worker->wait(1);

        // 戻り値は true (継続)
        $this->assertTrue($ret);

        // ウエイトは 1 秒強
        $this->assertLessThan(1.1, microtime(true) - $time);
        $this->assertGreaterThan(1.0, microtime(true) - $time);
    }

    /**
     * @test
     */
    public function wait_term()
    {
        $this->worker = new SimpleWorker();
        $this->worker->init();

        // TERM シグナル
        posix_kill(getmypid(), SIGTERM);

        // 開始時刻
        $time = microtime(true);

        $ret = $this->worker->wait(1);

        // 戻り値は false (終了)
        $this->assertFalse($ret);

        // ウエイト無し
        $this->assertLessThan(0.1, microtime(true) - $time);
    }

    /**
     * @test
     */
    public function wait_usr2()
    {
        $this->worker = new SimpleWorker();
        $this->worker->init();

        // TERM シグナル
        posix_kill(getmypid(), SIGUSR2);

        // 開始時刻
        $time = microtime(true);

        $ret = $this->worker->wait(1);

        // 戻り値は true (継続)
        $this->assertTrue($ret);

        // ウエイト無し
        $this->assertLessThan(0.1, microtime(true) - $time);
    }

    /**
     * @test
     */
    public function fin()
    {
        $this->worker = new SimpleWorker();

        $fn = $this->worker->getPidFile();

        $this->worker->init();

        $this->assertFileExists($fn);

        $this->worker->fin();
        $this->worker = null;

        $this->assertFileNotExists($fn);
    }

    /**
     * @test
     */
    public function destruct()
    {
        $this->worker = new SimpleWorker();

        $fn = $this->worker->getPidFile();

        $this->worker->init();

        $this->assertFileExists($fn);

        $this->worker = null;

        gc_collect_cycles();

        $this->assertFileNotExists($fn);
    }

    /**
     * @test
     */
    public function client()
    {
        $this->worker = new SimpleWorker();
        $this->worker->init();

        $client = new SimpleWorker();
        $client->send();

        // 開始時刻
        $time = microtime(true);

        $ret = $this->worker->wait(1);

        // 戻り値は true (継続)
        $this->assertTrue($ret);

        // ウエイト無し
        $this->assertLessThan(0.1, microtime(true) - $time);
    }
}
