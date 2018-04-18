<?php
if (!defined('IN_PRODUCTION'))
{
	die('Hacking attempt');
}

if (PHP_VERSION < '5.0.0')
{
	die ('The PHP version is ' . PHP_VERSION . '! Plz upgrade it to 5.0 or newer version!');
}

/*
|---------------------------------------------------------------
| Protect against register_globals
|------------------------------------------------------------  ---
|  This must be done before any globals are set by the code
|
*/
if (ini_get('register_globals'))
{
	if (isset ($_REQUEST ['GLOBALS']))
	{
		die ('<a href="http://www.hardened-php.net/index.76.html">$GLOBALS overwrite vulnerability</a>');
	}
	$verboten = array ('GLOBALS', '_SERVER', 'HTTP_SERVER_VARS', '_GET', 'HTTP_GET_VARS', '_POST', 'HTTP_POST_VARS', '_COOKIE', 'HTTP_COOKIE_VARS', '_FILES', 'HTTP_POST_FILES', '_ENV', 'HTTP_ENV_VARS', '_REQUEST', '_SESSION', 'HTTP_SESSION_VARS' );
	foreach ($_REQUEST as $name => $value)
	{
		if (in_array ( $name, $verboten ))
		{
			header ("HTTP/1.x 500 Internal Server Error");
			echo "register_globals security paranoia: trying to overwrite superglobals, aborting.";
			die (- 1);
		}
		unset ($GLOBALS [$name]);
	}
}

if (!function_exists('get_called_class'))
{

	function get_called_class()
	{
		$bt = debug_backtrace();
		$lines = file($bt[1]['file']);
		preg_match('/([a-zA-Z0-9\_]+)::'.$bt[1]['function'].'/',
			   $lines[$bt[1]['line']-1],
			   $matches);
		return $matches[1];
	}
}

/*
|---------------------------------------------------------------
| catching the current resource usages
|---------------------------------------------------------------
| getrusage() does not exist on the Microsoft Windows platforms
|
*/
if (function_exists ( 'getrusage' ))
{
	$sysRUstart = getrusage ();
}
else
{
	$sysRUstart = array ();
}

/*
|---------------------------------------------------------------
| For security
|---------------------------------------------------------------
*/
@ini_set ( 'allow_url_fopen', 0 );

/*
|---------------------------------------------------------------
| Test for PHP bug which breaks PHP 5.0.x on 64-bit...
| As of 1.8 this breaks lots of common operations instead
| of just some rare ones like export.
|---------------------------------------------------------------
*/
$borked = str_replace ('a', 'b', array (- 1 => - 1 ));
if (! isset ( $borked [- 1] ))
{
	echo "PHP 5.0.x is buggy on your 64-bit system; you must upgrade to PHP 5.1.x\n" . "or higher. ABORTING. (http://bugs.php.net/bug.php?id=34879 for details)\n";
	exit ();
}

/* 设置PHP的环境值 */
date_default_timezone_set ( 'PRC' );
define('SYSTEM_TIME', isset ( $_SERVER ['REQUEST_TIME'] ) ? $_SERVER ['REQUEST_TIME'] : time ());
define('SYSTEM_DATE', date ( 'Y-m-d H:i:s', SYSTEM_TIME ));
define('PAGE_REQUEST_TIME', microtime ( true ));
define('SYSTEM_PATH', dirname(__FILE__));
define('SYSTEM_CHARSET', 'utf-8');

//auto loaded
spl_autoload_register(array('ZOL', 'autoload'));

if (IS_DEBUGGING)
{
	ZOL_Exception::register();
	ZOL_Error::register();
}

class ZOL
{
	/**
	* 已加载的类
	* @var array
	*/
	private static $_loadedClass = array();

	private static $_namespace = array();

	/*
	|---------------------------------------------------------------
	|  Loads a class or interface file from the include_path.
	|---------------------------------------------------------------
	| @param string $name A ZOL (or other) class or interface name.
	| @return void
	*/
	public static function autoload($name)
	{
		if (trim($name) == '')
		{
			new ZOL_Exception('No class or interface named for loading');
		}

		//为了使用通用的ip库接口,必须这样特殊判断
		if(in_array($name,array("IpLocation"))  ){
			include '/www/zdata/intf/iplocation.php';
			return;
		}
		if( $name == "DB_Interface_Read"  ){
			include SYSTEM_PATH . '/Db/Interface/Read.php';
			return;
		}
		//<<


		if (class_exists($name, false) || interface_exists($name, false))
		{
			return;
		}

		$namespace = substr($name, 0, stripos($name, '_'));
		// 对ZOL一种处理
		if ($namespace == 'ZOL')
		{
           	$file = SYSTEM_PATH . '/' . str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';
		}
		// 对个性的命名空间做处理
		elseif (array_key_exists($namespace, self::$_namespace))
		{
			$file = self::$_namespace[$namespace] . '/' . str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';
		}
		// 其他情况全是有问题的
		else
		{
			throw new ZOL_Exception("The namespace config have problem: '{$name}'");
		}

		if (! file_exists($file))
		{
			throw new ZOL_Exception("The file dose not exist: '{$file}'");
		}
		if (! is_readable($file))
		{
			throw new ZOL_Exception("The file can not read: '{$file}'");
		}

		include $file;

		if (! class_exists($name, false) && ! interface_exists($name, false))
		{
			throw new ZOL_Exception('Class or interface does not exist in loaded file');
		}
		if (empty(self::$_loadedClass[$name])) {
			self::$_loadedClass[$name] = 0;
		}
		self::$_loadedClass[$name] ++;
	}

	/**
	 * 使用namespace方法实现每个实例的命名空间映射
	 *
	 * @param string $path
	 */
	public static function setNameSpace($path)
	{
		if (empty($path)) {
			new ZOL_Exception('No class or interface named for loading');
		}
		$namespace = substr(strrchr($path, '/'), 1);
		$namespacePath = substr($path, 0, strlen($path) - strlen($namespace) - 1);
		if (!isset(self::$_namespace[$namespace]) || self::$_namespace[$namespace] != $namespacePath) {
			self::$_namespace[$namespace] = $namespacePath;
		} else {
			throw new ZOL_Exception('Class or interface does not exist in loaded file');
		}
	}

	public static function getLoadedClass()
	{
		return self::$_loadedClass;
	}
}
