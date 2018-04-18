<?php

class ZOL_Log
{
    const TYPE_ERROR = 1;
    const TYPE_EXCEPTION = 2;
    protected static $type = array(
        self::TYPE_ERROR => 'error',
        self::TYPE_EXCEPTION => 'exception',
    );
    public static function write($message, $type)
    {
        if (empty($message))
        {
            trigger_error('$message dose not empty! ');

            return false;
        }
        if (empty($type))
        {
            trigger_error('$type dose not empty! ');

            return false;
        }
        if (!isset(self::$type[$type]))
        {
            trigger_error('Unknow log type: ' . $type);

            return false;
        }
        $var = SYSTEM_VAR;
        $path = $var . '/log/' . self::$type[$type] . '/' . date('Y/m/d') . '.log';

        $mark = "\n\n===========================================================================\n";
        $mark .= 'time:' . date('Y/m/d H:i:s') . "\n";

        return ZOL_File::write($mark . $message, $path, (FILE_APPEND | LOCK_EX));
    }
}
