<?php
namespace ngyuki\Tests;

use ngyuki\SimpleWorker\SimpleWorkerServer;
use ngyuki\SimpleWorker\SimpleWorkerClient;

class SimpleWorkerServerTest extends \PHPUnit_Framework_TestCase
{
    private $fn;
    private $server;

    public function setUp()
    {
        $this->fn = sys_get_temp_dir() . DIRECTORY_SEPARATOR .  'SimpleWorker-SimpleWorkerServerTest.pid';

        if (file_exists($this->fn))
        {
            unlink($this->fn);
        }
    }

    protected function tearDown()
    {
        if ($this->server)
        {
            $this->server->fin();
        }
    }

    /**
     * @test
     */
    public function test()
    {
        $this->server = new SimpleWorkerServer($this->fn);
        $this->server->init();

        $this->assertEquals($this->server->getPidFile(), $this->fn);
        $this->assertEquals(file_get_contents($this->server->getPidFile()), getmypid());
    }

    /**
     * @test
     */
    public function test_pidfile_not_specified()
    {
        $this->server = new SimpleWorkerServer();
        $this->server->init();

        $this->assertEquals(file_get_contents($this->server->getPidFile()), getmypid());
    }

    /**
     * @test
     */
    public function logger()
    {
        $logs = array();

        $this->server = new SimpleWorkerServer();
        $this->server->setLogger(function ($log) use (&$logs) { $logs[] = $log;} );
        $this->server->init();

        list ($log) = $logs;
        $this->assertContains("init server", $log);
    }

    /**
     * @test
     */
    public function wait()
    {
        $this->server = new SimpleWorkerServer();
        $this->server->init();

        // 開始時刻
        $time = microtime(true);

        $ret = $this->server->wait(1);

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
        $this->server = new SimpleWorkerServer();
        $this->server->init();

        // TERM シグナル
        posix_kill(getmypid(), SIGTERM);

        // 開始時刻
        $time = microtime(true);

        $ret = $this->server->wait(1);

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
        $this->server = new SimpleWorkerServer();
        $this->server->init();

        // TERM シグナル
        posix_kill(getmypid(), SIGUSR2);

        // 開始時刻
        $time = microtime(true);

        $ret = $this->server->wait(1);

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
        $this->server = new SimpleWorkerServer();

        $fn = $this->server->getPidFile();

        $this->server->init();

        $this->assertFileExists($fn);

        $this->server->fin();
        $this->server = null;

        $this->assertFileNotExists($fn);
    }

    /**
     * @test
     */
    public function destruct()
    {
        $this->server = new SimpleWorkerServer();

        $fn = $this->server->getPidFile();

        $this->server->init();

        $this->assertFileExists($fn);

        $this->server = null;

        gc_collect_cycles();

        $this->assertFileNotExists($fn);
    }

    /**
     * @test
     */
    public function client()
    {
        $this->server = new SimpleWorkerServer();
        $this->server->init();

        $client = new SimpleWorkerClient();
        $client->send();

        // 開始時刻
        $time = microtime(true);

        $ret = $this->server->wait(1);

        // 戻り値は true (継続)
        $this->assertTrue($ret);

        // ウエイト無し
        $this->assertLessThan(0.1, microtime(true) - $time);
    }
}
