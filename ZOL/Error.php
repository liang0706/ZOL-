<?php

class ZOL_Error
{
	private static $levels = array(
						E_ERROR				=>	'Error',
						E_WARNING			=>	'Warning',
						E_PARSE				=>	'Parsing Error',
						E_NOTICE			=>	'Notice',
						E_CORE_ERROR		=>	'Core Error',
						E_CORE_WARNING		=>	'Core Warning',
						E_COMPILE_ERROR		=>	'Compile Error',
						E_COMPILE_WARNING	=>	'Compile Warning',
						E_USER_ERROR		=>	'User Error',
						E_USER_WARNING		=>	'User Warning',
						E_USER_NOTICE		=>	'User Notice',
						E_STRICT			=>	'Runtime Notice'
					);
    public static function register()
    {
        set_error_handler(array(__CLASS__, 'handler'));
    }
    public static function handler($level, $errorMsg, $file, $line, $context = null)
    {
        if ('.tpl.php' == substr($file, -8))
        {
            return;
        }
        $str = new ZOL_Exception($errorMsg, $level, $file, $line);
        $debugging = IS_DEBUGGING;
        $production = IS_PRODUCTION;
        if ($debugging)
        {
            $content = "<br />\n<h2>Error Info:</h2>\n" .
                '<b>MESSAGE:</b> ' . $errorMsg . "<br />\n" .
                '<b>TYPE:</b> ' . (isset(self::$levels[$level]) ? self::$levels[$level] :  $level ). "<br />\n" .
                '<b>FILE:</b> ' . $file . "<br />\n" .
                '<b>LINE:</b> ' . $line . "<br />\n" .
                $str
                ;
            if ($production)
            {
                ZOL_Log::write(ZOL_String::clean($content), ZOL_Log::TYPE_ERROR);
            }
            else
            {
                echo (ZOL_Request::resolveType() == ZOL_Request::CLI)
                    ? ZOL_String::clean($content)
                    : $content;
            }
        }
    }
}
