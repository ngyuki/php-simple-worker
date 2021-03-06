<?php
namespace ngyuki\SimpleWorker;

class SimpleWorker extends SimpleWorkerAbstract
{
    private $_inited = false;
    private $_sigset;
    private $_pidlock;

    /**
     * デストラクタ
     */
    public function __destruct()
    {
        $this->fin();
    }

    /**
     * ワーカーの初期化
     */
    public function init()
    {
        ASSERT(' $this->_sigset === null ');
        ASSERT(' $this->_pidlock === null ');

        // ログ
        $pid = posix_getpid();
        $this->_log("init worker [$pid => $this->_pidfile]");

        // PIDファイルをロック
        $this->_pidlock = new PidFileFlock($this->_pidfile, $pid);

        // シグナルのセット
        $this->_sigset = array(SIGTERM, SIGINT, SIGHUP, SIGUSR2);
        pcntl_sigprocmask(SIG_BLOCK, $this->_sigset);

        // シグナルの無視設定
        pcntl_signal(SIGUSR2, SIG_IGN);
    }

    /**
     * ワーカーの後処理
     */
    public function fin()
    {
        if ($this->_sigset)
        {
            // シグナルマスクを解除
            pcntl_sigprocmask(SIG_UNBLOCK, $this->_sigset);

            $this->_sigset = null;
        }

        if ($this->_pidlock)
        {
            // PIDファイルのアンロック
            $this->_pidlock->release();
            $this->_pidlock = null;

            // ログ
            $pid = posix_getpid();
            $this->_log("exit worker [$pid]");
        }
    }

    /**
     * 次のトリガまで待機
     *
     * @param int $sec
     */
    public function wait($sec)
    {
        $limit = microtime(true) + $sec;

        for (;;)
        {
            $sec = (int)($limit - microtime(true) + 1);

            if ($sec < 1)
            {
                return true;
            }

            if ($signo = pcntl_sigtimedwait($this->_sigset, $info, $sec))
            {
                switch ($signo)
                {
                    case SIGTERM:
                    case SIGINT:
                    case SIGHUP:
                        return false;

                    case SIGUSR2:
                        return true;
                }
            }
        }
    }

    /**
     * 送信
     */
    public function send()
    {
        if (!is_readable($this->_pidfile))
        {
            $this->_log("not readable [$this->_pidfile]");
            return;
        }

        $pid = (int)file_get_contents($this->_pidfile);

        if ($pid <= 0)
        {
            $this->_log("invalid pid [$pid]");
            return;
        }

        if (!defined('SIGUSR2'))
        {
            define('SIGUSR2', 12);
        }

        $this->_log("sending signal to [$pid]");
        posix_kill($pid, SIGUSR2);
    }
}
