<?php
namespace ngyuki\SimpleWorker;

abstract class SimpleWorkerAbstract
{
    protected $_pidfile;
    protected $_logger;
    
    /**
     * コンストラクタ
     *
     * @param string $pidfile  PIDファイル、省略時は /tmp/SimpleWorker-{$hash}.pid
     */
    public function __construct($pidfile = null)
    {
        if ($pidfile !== null)
        {
            $this->_pidfile = $pidfile;
        }
        else
        {
            $hash = md5(__DIR__);
            $this->_pidfile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "SimpleWorker-$hash.pid";
        }
        
        $this->_logger = function () {};
    }
    
    /**
     * ロガーを設定
     *
     * @param callable $logger
     */
    public function setLogger(callable $logger)
    {
        $this->_logger = $logger;
        return $this;
    }
    
    /**
     * PIDファイル名を取得
     *
     * @return string
     */
    public function getPidFile()
    {
        return $this->_pidfile;
    }
    
    /**
     * 動作ログ
     *
     * @param string $log
     */
    protected function _log($log)
    {
        call_user_func($this->_logger, $log);
    }
}
