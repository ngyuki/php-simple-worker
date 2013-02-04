<?php
namespace ngyuki\SimpleWorker;

class PidFileFlock
{
    private $_pidfile;
    private $_handle;
    
    /**
     * コンストラクタ
     */
    public function __construct($pidfile, $pid = null)
    {
        if ($pid === null)
        {
            $pid = getmypid();
        }
        
        $this->_handle = $this->_lock($pidfile, $pid);
        $this->_pidfile = $pidfile;
    }
    
    /**
     * デストラクタ
     */
    public function __destruct()
    {
        $this->release();
    }
    
    /**
     * PID ファイルをロック
     */
    private function _lock($pidfile, $pid)
    {
        touch($pidfile);
        
        $fp = fopen($pidfile, "r+");
        
        if ($fp == false)
        {
            throw new \RuntimeException("unable create file \"$pidfile\"");
        }
        
        try
        {
            $wouldblock = 0;
            $ret = flock($fp, LOCK_EX | LOCK_NB, $wouldblock);
            
            if ($ret == false)
            {
                if ($wouldblock)
                {
                    throw new \RuntimeException("already started");
                }
                else
                {
                    throw new \RuntimeException("flock: unknown error");
                }
            }
            
            if (ftruncate($fp, 0) === false)
            {
                throw new \RuntimeException("ftruncate: unknown error");
            }
            
            if (rewind($fp) === false)
            {
                throw new \RuntimeException("rewind: unknown error");
            }
            
            
            if (fwrite($fp, $pid) === false)
            {
                throw new \RuntimeException("fwrite: unknown error");
            }
            
            if (fflush($fp) === false)
            {
                throw new \RuntimeException("fflush: unknown error");
            }
            
            return $fp;
        }
        catch (\Exception $ex)
        {
            fclose($fp);
            throw $ex;
        }
    }
    
    /**
     * PID ファイルを開放
     */
    public function release()
    {
        if (is_resource($this->_handle))
        {
            fclose($this->_handle);
            $this->_handle = null;
        }
        
        if (file_exists($this->_pidfile))
        {
            unlink($this->_pidfile);
            $this->_pidfile = null;
        }
    }
}
