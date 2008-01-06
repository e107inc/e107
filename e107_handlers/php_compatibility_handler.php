<?php

if (!defined('e107_INIT')) { exit; }

// e107 requires PHP > 4.3.0, all functions that are used in e107, introduced in newer
// versions than that should be recreated in here for compatabilty reasons..

// file_put_contents - introduced in PHP5
if (!function_exists('file_put_contents')) 
{
	/**
	* @return int
	* @param string $filename
	* @param mixed $data
	* @desc Write a string to a file
	*/
	define('FILE_APPEND', 1);
	function file_put_contents($filename, $data, $flag = false) 
	{
      $mode = ($flag == FILE_APPEND || strtoupper($flag) == 'FILE_APPEND') ? 'a' : 'w';
	  if (($h = @fopen($filename, $mode)) === false) 
	  {
		return false;
	  }
      if (is_array($data)) $data = implode($data);
	  if (($bytes = @fwrite($h, $data)) === false) 
	  {
		return false;
	  }
	  fclose($h);
	  return $bytes;
	}
}



if (!function_exists('stripos')) 
{
 	function stripos($str,$needle,$offset=0)
	{
		return strpos(strtolower($str), strtolower($needle), $offset);
	}
}


?>