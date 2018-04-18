<?php

/*
|---------------------------------------------------------------
| File management utility methods.
|---------------------------------------------------------------
| @package ZOL
|
*/

class ZOL_File
{
	protected static $_file;

	public static function exists($file)
	{
		$file = trim($file);
		if (! $file) {
			return false;
		}

		$abs = ($file[0] == '/' || $file[0] == '\\' || $file[1] == ':');
		if ($abs && file_exists($file)) {
			return $file;
		} elseif (strpos($file, 'http://') === 0) {//远程文件
			return self::remoteExists($file);
		}

		return false;
	}

	/**
	* 检测远程文件是否存在
	*
	* @param string $fileUrl
	* @return boolean
	*/
	private static function remoteExists($file)
	{
		//检测输入
		$file = trim($file);

		if (empty($file)) {
			return false;
		}

		$urlArr = parse_url($file);
		if (!is_array($urlArr) || empty($urlArr)){
			return false;
		}

		//获取请求数据
		$host = $urlArr['host'];
		$path = $urlArr['path'] ."?". $urlArr['query'];
		$port = isset($urlArr['port']) ? $urlArr['port'] : "80";

		//连接服务器
		$fp = fsockopen($host, $port, $errNo, $errStr, 30);
		if (!$fp){
			return false;
		}

		//构造请求协议
		$requestStr = "GET ".$path." HTTP/1.1\r\n";
		$requestStr .= "Host: ".$host."\r\n";
		$requestStr .= "Connection: Close\r\n\r\n";

		//发送请求
		fwrite($fp, $requestStr);
		$firstHeader = fgets($fp, 1024);
		fclose($fp);

		//判断文件是否存在
		if (!trim($firstHeader)) {
			return false;
		}
		if (strpos($firstHeader,'200') === false) {
			return false;
		}
		return true;
	}

	public static function load($file)
	{
		self::$_file = self::exists($file);

		if (!self::$_file)
		{
			throw new ZOL_Exception('File does not exist or is not readable: ' . $file);
		}

		if (!is_readable(self::$_file))
		{
			throw new ZOL_Exception('File does not readable: ' . $file);
		}

		unset($file);

		return include self::$_file;
	}

	public static function get($file, $intoAnArray = false)
	{
		self::$_file = self::exists($file);
		if (!self::$_file) {
			//throw new ZOL_Exception('File does not exist or is not readable: '.$file);
			//trigger_error('File does not exist or is not readable: ' . $file);

			return false;
		}

		unset($file);
		if (false == $intoAnArray)
		{
			return file_get_contents(self::$_file);
		}
		else
		{
			return file(self::$_file);
		}
	}

	public static function write($content, $path, $flags = 0)
	{
		$path = trim($path);
		if (empty($path))
		{
			trigger_error('$path must to be set!');

			return false;
		}

		$dir = dirname($path);
		if (!self::exists($dir))
		{
			if (false == self::mkdir($dir))
			{
				trigger_error('filesystem is not writable: ' . $dir);

				return false;
			}
		}
		$path = str_replace("//","/",$path);
        if(file_exists($path)) chmod($path,0777);
		return file_put_contents($path, $content, ((empty($flags)) ? (LOCK_EX) : $flags));
	}

	function copyDir($source, $dest, $overwrite = false)
	{
		if (!is_dir($dest)) {
			if (!is_writable(dirname($dest))) {
				throw new ZOL_Exception('filesystem not writable:' . dirname($dest));
			}
			mkdir($dest);
		}
		if ($handle = opendir($source)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..') {
					$path = $source . '/' . $file;
					if (self::exists($path)) {
						if (!self::exists($dest . '/' . $file) || $overwrite) {
							if (!@copy($path, $dest . '/' . $file)){
								throw new ZOL_Exception('filesystem not writable:' . $dest . '/' . $file);
							}
						}
					} elseif (is_dir($path)) {
						if (!is_dir($dest . '/' . $file)) {
							if (!is_writable(dirname($dest . '/' . $file))) {
								throw new ZOL_Exception('filesystem not writable:' . dirname($dest . '/' . $file));
							}
							mkdir($dest . '/' . $file); // make subdirectory before subdirectory is copied
						}
						self::copyDir($path, $dest . '/' . $file, $overwrite); //recurse
					}
				}
			}
			closedir($handle);
		}
		return true;
	}

	public static function rm($path, $recursive = false)
	{
		//$path = rtrim($path, '/').'/';

		if (!self::exists($path))
		{
			trigger_error('File does not exist or is not readable:' . $path);

			return false;
		}
		if (is_file($path))
		{
			return unlink($path);
		}
		elseif (is_dir($path))
		{
			$handle = opendir($path);
			while(false !== ($file = readdir($handle)))
			{
				if($file != '.' and $file != '..' )
				{
					$fullpath = $path.$file;
					if(is_dir($fullpath) && $recursive)
					{
						self::rm($fullpath, $recursive);
					}
					else
					{
						unlink($fullpath);
					}
				}
			}

			closedir($handle);
			rmdir($path);

			return true;
		}
		return false;
	}
	public static function mkdir($path, $chmod = 0777, $recursive = true)
	{
		mkdir($path, $chmod, $recursive);

		return true;
	}

	/*
	|---------------------------------------------------------------
	| 列出文件列表
	|---------------------------------------------------------------
	| @param string $__dir     路径      默认为当前路径
	| @param string $__pattern 文件类型  默认为所有类型
	| @return array An array of list of files on success
	*/
	public static function ls($__dir = './', $__pattern='*.*')
	{
		settype($__dir, 'string');
		settype($__pattern, 'string');

		$__ls = array();
		$__regexp = preg_quote($__pattern, '/');
		$__regexp = preg_replace('/[\\x5C][\x2A]/', '.*', $__regexp);
		$__regexp = preg_replace('/[\\x5C][\x3F]/', '.', $__regexp);

		if(is_dir($__dir))
		{
			if(($__dir_h = @opendir($__dir)) !== FALSE)
			{
				while(($__file = readdir($__dir_h)) !== FALSE)
				{
					if ('.' != $__file && '..' != $__file)
					{
						if(preg_match('/^' . $__regexp . '$/', $__file))
						{
							array_push($__ls, $__file);
						}
					}
				}
				closedir($__dir_h);
				sort($__ls, SORT_STRING);
			}
		}
		return $__ls;
	}
}
