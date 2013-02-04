<?php
namespace ngyuki\SimpleWorker;

class SimpleWorkerClient extends SimpleWorkerAbstract
{
    /**
     * 送信
     */
    public function send()
    {
        if (is_readable($this->_pidfile))
        {
            $pid = (int)file_get_contents($this->_pidfile);
            
            if ($pid > 0)
            {
                posix_kill($pid, SIGUSR2);
            }
        }
    }
}
