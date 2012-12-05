<?php
use \Slime\MultiJob\Daemon;

require '../../src/Slime/MultiJob/Daemon.php';
require '../../src/Slime/MultiJob/Job.php';
require '../../src/Slime/MultiJob/I_JobQueue.php';
require './JobQueue.php';

$numOfMaxProcess = isset($argv[1]) && $argv[1]>0 ? (int)$argv[1] : 10;
Daemon::getInstance(new JobQueue(), $numOfMaxProcess)->run();
