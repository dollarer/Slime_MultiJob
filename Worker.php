<?php
abstract class Worker
{
    final public function run($file, $callback, $param_arr)
    {
        require_once $file;
        return call_user_func_array($callback, $param_arr);
    }

    abstract public function pre();
}