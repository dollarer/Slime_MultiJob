<?php
require '../src/Slime/MultiJob/Job.php';

$file = dirname(__FILE__) . '/JobQueue.php';
$jobQueue = new JobQueue();

$method = rand(0,1) ? 'add' : 'multi';
$a = rand(1,9);
$b = rand(10,99);

$job = \Slime\MultiJob\Job::factory(
    dirname(__FILE__) . '/dealjob.php',
    array('DealJob', $method), array($a, $b)
);

for ($i=0;$i<=19;$i++) {
    $rs = $jobQueue->push((string)$job);
    echo "push job:$i\n";
}
