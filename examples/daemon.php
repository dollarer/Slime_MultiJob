<?php
require '../src/Slime/MultiJob/Daemon.php';
require '../src/Slime/MultiJob/Job.php';
require './JobQueue.php';

\Slime\MultiJob\Daemon::getInstance(new JobQueue())->run();
