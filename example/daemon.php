<?php
require '../src/Daemon.php';
require '../src/Job.php';
require './JobQueue.php';

\Slime\MultiJob\Daemon::getInstance(new JobQueue())->run();
