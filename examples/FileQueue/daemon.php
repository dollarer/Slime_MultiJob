<?php
use \Slime\MultiJob\Daemon;

require '../../src/Slime/MultiJob/Daemon.php';
require '../../src/Slime/MultiJob/Job.php';
require '../../src/Slime/MultiJob/I_JobQueue.php';
require './JobQueue.php';

Daemon::getInstance(new JobQueue())->run();
