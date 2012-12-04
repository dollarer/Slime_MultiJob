<?php
class Daemon
{
    const QUEUE_FETCH_COMMON = 0;
    const QUEUE_FETCH_BLOCK = 1;

    /**
     * @var I_JobQueue
     */
    private $jobQueue;

    /**
     * @var I_Worker
     */
    private $worker;

    /**
     * @var int
     */
    private $numOfWorkers;

    /**
     * @var int
     */
    private $numOfMaxWorkers;

    /**
     * @var int
     */
    private $fetchMode;

    /**
     * @var int
     */
    private $interval;

    /**
     * @var $this
     */
    private static $instance;

    private function __construct(I_JobQueue $jobQueue,
                                 I_Worker $worker,
                                 $numOfMaxProcess,
                                 $fetchMode,
                                 $interval
    )
    {
        if (
            !is_int($numOfMaxProcess) ||
            $numOfMaxProcess < 1 ||
            $fetchMode !== self::QUEUE_FETCH_COMMON ||
            $fetchMode !== self::QUEUE_FETCH_BLOCK ||
            !is_int($interval) ||
            $interval <= 0
        ) {
            exit('ERROR PARAM');
        }
        $this->jobQueue = $jobQueue;
        $this->worker = $worker;
        $this->numOfMaxWorkers = $numOfMaxProcess;
        $this->fetchMode = $fetchMode;
        $this->interval = $interval;
        $this->numOfWorkers = 0;

        declare(ticks=1);
        pcntl_signal(SIGCHLD, array($this, 'sig_handler'));
    }
    private function __clone(){}

    /**
     * @param I_JobQueue $jobQueue
     * @param I_Worker $worker
     * @param int $numOfMaxProcess
     * @param int $fetchMode
     * @param int $interval ms
     * @param string $lockFile
     * @return Daemon
     */
    public static function getInstance(I_JobQueue $jobQueue,
                                       I_Worker $worker,
                                       $numOfMaxProcess = 10,
                                       $fetchMode = self::QUEUE_FETCH_COMMON,
                                       $interval = 100000,
                                       $lockFile = '/tmp/slime_multijob_deamon.lock'
    )
    {
        if (!file_exists($lockFile)) {
            if (!touch($lockFile)) {
                exit("create lock file failed![$lockFile]");
            }
        }
        $fHandle = fopen($lockFile, 'r');
        if (flock($fHandle, LOCK_EX|LOCK_NB)) {
            if (!self::$instance) {
                self::$instance = new self($jobQueue, $worker, $numOfMaxProcess, $fetchMode, $interval);
            }
            return self::$instance;
        } else {
            exit('daemon process is running');
        }
    }

    public function sig_handler($signo)
    {
        pcntl_wait($status);
        $this->numOfWorkers--;
    }

    public function run()
    {
        while (true) {
            if ($this->numOfWorkers<$this->numOfMaxWorkers) {
                if ($this->fetchMode===self::QUEUE_FETCH_COMMON) {
                    list($file, $callback, $param_arr) = $this->jobQueue->pop();
                } else {
                    list($file, $callback, $param_arr) = $this->jobQueue->bpop();
                }
                $this->numOfWorkers++;
                $pid = pcntl_fork();
                if ($pid === -1) {
                    exit('ERROR FORK');
                } elseif ($pid) {
                    ;
                } else {
                    $this->worker->pre();
                    if (!$this->worker->run($file, $callback, $param_arr)) {
                        $this->jobQueue->push($file, $callback, $param_arr);
                    }
                    exit();
                }
                if ($this->fetchMode==self::QUEUE_FETCH_COMMON) {
                    usleep($this->interval);
                }
            } else {
                usleep($this->interval);
            }
        }
    }
}
