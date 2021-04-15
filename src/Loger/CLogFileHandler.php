<?php
//以下为日志
namespace Xubin\WxPayApiV3\Loger;



class CLogFileHandler implements ILogHandler
{
	private $handle = null;
	
	public function __construct($file = '')
	{
	    $dir = dirname($file);
	    if (!file_exists($dir)) {
	        mkdir($dir, 775, true);
	    }
		$this->handle = fopen($file,'a');
	}
	
	public function write($msg)
	{
		fwrite($this->handle, $msg, 4096);
	}
	
	public function __destruct()
	{
		fclose($this->handle);
	}
}

