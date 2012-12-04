<?php
namespace Slime\MultiJob;

interface I_JobQueue
{
    /**
     * @return string
     */
    public function pop();

    /**
     * @return string
     */
    public function bpop();

    /**
     * @param $string
     * @return bool
     */
    public function push($string);
}