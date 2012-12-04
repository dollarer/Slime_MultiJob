<?php
namespace Slime\MultiJob;

final class Job
{
    private $file;
    private $callback;
    private $param_arr;

    private function __construct($file, $callback, $param_arr)
    {
        $this->file = $file;
        $this->callback = $callback;
        $this->param_arr = $param_arr;
    }

    public static function factory($file, $callback, $param_arr)
    {
        return new self($file, $callback, $param_arr);
    }

    public static function factoryFromString($string)
    {
        $arr = @json_decode($string);
        if (!$arr || !isset($arr['file']) || !isset($arr['callback']) || !isset($arr['param_arr'])) {
            throw new Exception_JobCreate($string);
        }
        return self::factory($arr['file'], $arr['callback'], $arr['param_arr']);
    }

    public function __toString()
    {
        return json_encode(array(
            'file' => $this->file,
            'callback' => $this->callback,
            'param_arr' => $this->param_arr
        ));
    }

    public function run()
    {
        require_once $this->file;
        return call_user_func_array($this->callback, $this->param_arr);
    }
}

class Exception_JobCreate extends \Exception{}