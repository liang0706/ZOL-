<?php

class ZOL_Exception extends Exception
{
	public function __construct($errorMsg = '', $level = 0, $file = '', $line = 0)
	{
		parent::__construct($errorMsg, $level);
		if (!empty($file))
		{
			$this->file = $file;
		}
		if (!empty($line))
		{
			$this->line = $line;
		}
	}

	public function __toString()
	{
		$trace = $this->getTrace();
		krsort($trace);
		$string = "<br />\n<h2>Stack trace:</h2>\n";
		$string .= "Exception '<b>{$trace[0]['class']}</b>' with message '<b>$this->message</b>' <br /><br />\n";
		$rowNum = 1;
		if (!empty($trace))
		{
			foreach ($trace as $key => $val)
			{
				if ($key == 0)
				{
					continue;
				}
				$args = array();
				if (!empty($val['args']))
				{
					foreach ($val['args'] as $v)
					{
						$args[] = is_object($v) ? (sprintf('Object(%s)', get_class($v)))
							: (is_array($v) ? gettype($v) : "'$v'");
					}
				}
				$args = implode(', ', $args);
				$val['class'] = isset($val['class']) ? $val['class'] : '';
				$val['type'] = isset($val['type']) ? $val['type'] : '';
				$val['file'] = isset($val['file']) ? $val['file'] : '';
				$val['line'] = isset($val['line']) ? "($val[line]):<br />\n" : '';
				$string .="#$rowNum $val[file]$val[line]<b>$val[class]$val[type]$val[function]($args) </b><br /> <br />\n" ;
				++ $rowNum;
			}
		}
		$string .= $this->getDebugInfo();
		
		return $string;
	}

	protected function getDebugInfo()
	{
		$ret = "<h2>Debug Info:</h2>\n";
		$contentLines = file($this->file);
		$total = count($contentLines);
		$startLine = ($this->line < 5) ? 0 : ($this->line - 5);
		$endLine = $this->line + 5;
		$endLine = ($total >= $endLine) ? $endLine : $total;

		for ($i = $startLine; $i < $endLine; ++ $i)
		{
			if ($i == ($this->line - 1))
			{
				$ret .= '<font color="red"><b> >>' . ($i + 1) . ' ' . htmlspecialchars($contentLines[$i]) . "</b></font><br />\n";
			}
			else
			{
				$ret .= '<b>' . ($i + 1) . '</b> ' . htmlspecialchars($contentLines[$i]) . "<br />\n";
			}
		}
		return $ret;
	}

	public static function register()
	{
		set_exception_handler(array(__CLASS__, 'handler'));
	}

	public static function handler($exception)
	{
		if ($exception instanceof Exception)
		{
			$debugging = defined('IS_DEBUGGING') ? IS_DEBUGGING : false;
			$production = defined('IS_PRODUCTION') ? IS_PRODUCTION : false;

			if (true == $debugging)
			{
				if (true == $production)
				{
					ZOL_Log::write(ZOL_String::clean($exception), ZOL_Log::TYPE_EXCEPTION);
				}
				else
				{
					echo (ZOL_Request::resolveType() == ZOL_Request::CLI) 
						? ZOL_String::clean($exception)
						: $exception;
				}
			}
		}
	}
}

?>
