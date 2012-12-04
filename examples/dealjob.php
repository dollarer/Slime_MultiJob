<?php
class DealJob
{
    public static $resultFile = '/tmp/Slime_MultiJob_queue';

    public static function add($a, $b)
    {
        sleep(3);
        $rs = $a + $b;
        @file_put_contents(
            self::$resultFile,
            file_get_contents(self::$resultFile) . "$a + $b=$rs\n"
        );
        return true;
    }

    public static function multi($a, $b)
    {
        sleep(3);
        $rs = $a * $b;
        @file_put_contents(
            self::$resultFile,
            file_get_contents(self::$resultFile) . "$a * $b=$rs\n"
        );
        return true;
    }
}