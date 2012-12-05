<?php
use \Slime\MultiJob\I_JobQueue;

class JobQueue implements I_JobQueue
{
    public $queueFile = '/tmp/Slime_MultiJob_queue';
    public $queueLock = '/tmp/Slime_MultiJob_queue.lock';

    public function __construct()
    {
        $arr = array($this->queueFile, $this->queueLock);
        foreach ($arr as $f) {
            if (!file_exists($f)) {
                if (!touch($f)) {
                    exit("$f can not be created");
                }
                @chmod($f, 0600);
            }
            if (!is_readable($f) || !is_writable($f)) {
                exit("$f must be readable and writable");
            }
        }
    }

    /**
     * @return string
     */
    public function pop()
    {
        $f = fopen($this->queueLock, 'w');
        $rs = flock($f, LOCK_EX|LOCK_NB);
        if (!$rs) {
            return '';
        }

        $arr = include $this->queueFile;
        if (empty($arr) || !is_array($arr)) {
            return '';
        }
        $data = array_pop($arr);
        $str = $this->parse($arr);
        file_put_contents($this->queueFile, $str);

        flock($f, LOCK_UN);
        fclose($f);
        return $data;
    }

    /**
     * @return string
     */
    public function bpop()
    {
        $f = fopen($this->queueLock, 'w');
        flock($f, LOCK_EX);

        while (true) {
            $arr = include $this->queueFile;
            if (!empty($arr) && is_array($arr)) {
                break;
            }
            usleep(100000);
        }
        $data = array_pop($arr);
        $str = $this->parse($arr);
        file_put_contents($this->queueFile, $str);

        flock($f, LOCK_UN);
        fclose($f);
        return $data;
    }

    /**
     * @param $string
     * @return bool
     */
    public function push($string)
    {
        $f = fopen($this->queueLock, 'w');
        flock($f, LOCK_EX);

        $arr = include $this->queueFile;
        if (empty($arr) || !is_array($arr)) {
            $arr = array();
        }
        array_push($arr, $string);
        $str = $this->parse($arr);
        file_put_contents($this->queueFile, $str);

        flock($f, LOCK_UN);
        fclose($f);
        return true;
    }

    private function parse($arr)
    {
        $str = var_export($arr, true);
        return <<<PHP
<?php return $str; ?>
PHP;
    }
}