<?php

if (!function_exists('file_get_contents')) {
	/**
	* @return string
	* @param string $filename
	* @desc Reads entire file into a string
	*/
	function file_get_contents($filename) {
		$fd = fopen("$filename", "rb");
		$content = fread($fd, filesize($filename));
		fclose($fd);
		return $content;
	}
}
if (!function_exists('file_put_contents')) {
	/**
	* @return int
	* @param string $filename
	* @param mixed $data
	* @desc Write a string to a file
	*/
	function file_put_contents($filename, $data) {
		if (($h = @fopen($filename, 'w+')) === false) {
			return false;
		}
		if (($bytes = @fwrite($h, $data)) === false) {
			return false;
		}
		fclose($h);
		return $bytes;
	}
}

?>