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
if (!defined('ENT_NOQUOTES')) {
	define('ENT_NOQUOTES', 0);
}
if (!defined('ENT_COMPAT')) {
	define('ENT_COMPAT', 2);
}
if (!defined('ENT_QUOTES')) {
	define('ENT_QUOTES', 3);
}

if (!function_exists('html_entity_decode')) {
	function html_entity_decode($string, $quote_style = ENT_COMPAT, $charset = null) {
		if (!is_int($quote_style)) {
			echo 'html_entity_decode() expects parameter 2 to be long, '.gettype($quote_style).' given';
			return;
		}
		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		$trans_tbl = array_flip($trans_tbl);
		$trans_tbl['&#039;'] = '\'';
		if ($quote_style & ENT_NOQUOTES) {
			unset($trans_tbl['&quot;']);
		}
		return strtr($string, $trans_tbl);
	}
}
if (!function_exists('ob_get_level')) {
	function ob_get_level(){
		return 0;
	}
}


?>