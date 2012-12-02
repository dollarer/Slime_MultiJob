<?php
interface I_JobQueue
{
    /**
     * @return array(file, callback, param_arr)
     */
    public function pop();

    /**
     * @return array(file, callback, param_arr)
     */
    public function bpop();

    public function push($file, $callback, $param_arr);
}