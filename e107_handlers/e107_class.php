<?php

class e107{
	var $ServerPath;
	var $HTTPPath;
	var $FilePath;
	var $DirectoryLocations = array();

	function e107($PathsArray, $File, $Compress = true){
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		if($Compress == true){
			ob_start ("ob_gzhandler");
		} else {
			ob_start();
		}
		$this->DirectoryLocations = $PathsArray;
		$this->SetDirs($File);
	}

	function SetDirs($___FILE__){
		$_SERVER['DOCUMENT_ROOT'] = str_replace(array('\\\\', '\\'), array('/', '/'), $_SERVER['DOCUMENT_ROOT']);
		$___FILE__ = dirname($___FILE__).'/';
		$___FILE__ = str_replace(array('\\\\', '\\'), array('/', '/'), $___FILE__);
		
		$this->ServerPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $___FILE__);
		$this->HTTPPath = 'http://'.$_SERVER['HTTP_HOST'].$this->ServerPath;
		$this->FilePath = $___FILE__;
		define("e_HTTP", $this->ServerPath);
	}
}

?>