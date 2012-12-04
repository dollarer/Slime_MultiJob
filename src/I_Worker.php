<?php
interface I_Worker
{
    public function run($file, $callback, $param_arr);

    public function pre();
}

abstract class Worker implements I_Worker
{
    final public function run($file, $callback, $param_arr)
    {
        require_once $file;
        return call_user_func_array($callback, $param_arr);
    }

    abstract public function pre();
}