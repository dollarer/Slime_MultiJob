<?php
require_once '../../src/Slime/MultiJob/Job.php';
require_once '../../src/Slime/MultiJob/I_JobQueue.php';
require_once './JobQueue.php';

class Job
{
    public static $resultFile = '/tmp/Slime_MultiJob_queue.result';

    public static function add($a, $b)
    {
        sleep(3);
        $rs = $a + $b;
        @file_put_contents(
            self::$resultFile,
            file_get_contents(self::$resultFile) . "$a + $b=$rs\n"
        );
        return true;
    }

    public static function multi($a, $b)
    {
        sleep(3);
        $rs = $a * $b;
        @file_put_contents(
            self::$resultFile,
            file_get_contents(self::$resultFile) . "$a * $b=$rs\n"
        );
        return true;
    }

    public static function pushJob($number)
    {
        $jobQueue = new JobQueue();

        for ($i=0;$i<$number;$i++) {
            $method = rand(0,1) ? 'add' : 'multi';
            $a = rand(1,9);
            $b = rand(10,99);

            $job = \Slime\MultiJob\Job::factory(
                __FILE__,
                array('Job', $method), array($a, $b)
            );

            $rs = $jobQueue->push((string)$job);
            fprintf(STDOUT, "push job:%d:%s\n", $i, $rs ? 'successful' : 'failed') ;
        }
    }
}

if (isset($argv[1]) && $argv[1]=='push') {
    Job::pushJob(isset($argv[2]) ? (int)$argv[2] : 10);
}