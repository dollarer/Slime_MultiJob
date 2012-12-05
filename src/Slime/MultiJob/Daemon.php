<?php
namespace Slime\MultiJob;

class Daemon
{
    const QUEUE_FETCH_COMMON = 0;
    const QUEUE_FETCH_BLOCK = 1;

    /**
     * @var I_JobQueue
     */
    private $jobQueue;

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

    private function __construct(
        I_JobQueue $jobQueue,
        $numOfMaxProcess,
        $fetchMode,
        $interval
    ) {
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
        $this->numOfMaxWorkers = $numOfMaxProcess;
        $this->fetchMode = $fetchMode;
        $this->interval = $interval;
        $this->numOfWorkers = 0;
    }

    private function __clone(){}

    /**
     * @param I_JobQueue $jobQueue
     * @param int $numOfMaxProcess
     * @param int $fetchMode
     * @param int $interval ms
     * @param string $lockFile
     * @return Daemon
     */
    public static function getInstance(
        I_JobQueue $jobQueue,
        $numOfMaxProcess = 10,
        $fetchMode = self::QUEUE_FETCH_COMMON,
        $interval = 100000,
        $lockFile = '/tmp/slime_multijob_deamon.lock'
    ) {
        if (!file_exists($lockFile)) {
            if (!touch($lockFile)) {
                fprintf(STDERR, "Create lock file failed![%s]\n", $lockFile);
                exit();
            }
        }
        $fHandle = fopen($lockFile, 'r');
        if (flock($fHandle, LOCK_EX | LOCK_NB)) {
            if (!self::$instance) {
                self::$instance = new self($jobQueue, $numOfMaxProcess, $fetchMode, $interval);
            }
            return self::$instance;
        } else {
            exit();
        }
    }

    public function sig_handler($sigNo)
    {
        pcntl_wait($status);
        $this->numOfWorkers--;
    }

    public function run()
    {
        declare(ticks = 1) ;
        pcntl_signal(SIGCHLD, array($this, 'sig_handler'));

        while (true) {
            //Check if beyond limit
            if ($this->numOfWorkers > $this->numOfMaxWorkers) {
                usleep($this->interval);
                continue;
            }

            //Get job
            try {
                if ($this->fetchMode === self::QUEUE_FETCH_BLOCK) {
                    $job = Job::factoryFromString($this->jobQueue->bpop());
                } else {
                    $jobString = $this->jobQueue->pop();
                    if ($jobString!=='') {
                        $job = Job::factoryFromString($jobString);
                    } else {
                        usleep($this->interval);
                        continue;
                    }
                }
            } catch (Exception_JobCreate $e) {
                fprintf(STDOUT, "Job create error[%s]\n", $e->getMessage());
                usleep($this->interval);
                continue;
            }

            //Fork a worker process and do job
            $this->numOfWorkers++;
            $pid = pcntl_fork();
            if ($pid === -1) {
                fprintf(STDERR, "Process fork error\n");
                exit();
            } elseif ($pid) {
                ;
            } else {
                if (!$job->run()) {
                    fprintf(STDOUT, "job run failed[%s]\n", $job);
                    $this->jobQueue->push((string)$job);
                }
                exit();
            }
        }
    }
}
