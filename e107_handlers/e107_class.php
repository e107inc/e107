<?php

class e107{
	var $server_path;
	var $http_path;
	var $https_path;
	var $class2_path;
	var $e107_dirs = array();

	function e107($e107_paths, $class2_file){
		error_reporting(E_ERROR | E_WARNING | E_PARSE);

		if(defined("COMPRESS_OUTPUT") && COMPRESS_OUTPUT === true) {
			ob_start ("ob_gzhandler");
		} else {
			ob_start();
		}

		$this->e107_dirs = $e107_paths;
		$this->set_e107_dirs($class2_file);
	}

	function set_e107_dirs($class2_file){
		$this->fix_missing_doc_root();
		$_SERVER['DOCUMENT_ROOT'] = $this->fix_windows_paths($_SERVER['DOCUMENT_ROOT']);

		$class2_file = dirname($class2_file).'/';
		$class2_file = $this->fix_windows_paths($class2_file);

		$this->ServerPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $class2_file);
		$this->http_path = 'http://'.$_SERVER['HTTP_HOST'].$this->ServerPath;
		$this->https_path = 'https://'.$_SERVER['HTTP_HOST'].$this->ServerPath;
		$this->class2_path = $class2_file;
		define("e_HTTP", $this->ServerPath);
	}

	function fix_windows_paths($path) {
		$fixed_path = str_replace(array('\\\\', '\\'), array('/', '/'), $path);
		return $fixed_path;
	}

	function fix_missing_doc_root() {
		if($_SERVER['DOCUMENT_ROOT'] == '') {
			$_SERVER['PATH_INFO'] = $this->fix_windows_paths($_SERVER['PATH_INFO']);
			$_SERVER['PATH_TRANSLATED'] = $this->fix_windows_paths($_SERVER['PATH_TRANSLATED']);
			$_SERVER['DOCUMENT_ROOT'] = str_replace($_SERVER['PATH_INFO'], '', $_SERVER['PATH_TRANSLATED']);
		}
	}
}

?>