<?php

class e107{
	var $server_path;
	var $http_path;
	var $https_path;
	var $class2_path;
	var $e107_dirs = array();
	var $e107_file_root;
	var $_ip_cache;

	function e107($e107_paths, $class2_file){
		error_reporting(E_ERROR | E_WARNING | E_PARSE);

		if(defined("COMPRESS_OUTPUT") && COMPRESS_OUTPUT === true) {
			ob_start ("ob_gzhandler");
		}

		$this->e107_dirs = $e107_paths;
		$this->set_e107_dirs($class2_file);
	}

	function set_e107_dirs($class2_file){
		$this->fix_missing_doc_root();
		$_SERVER['DOCUMENT_ROOT'] = $this->fix_windows_paths($_SERVER['DOCUMENT_ROOT']);

		$class2_file = dirname($class2_file).'/';
		$class2_file = $this->fix_windows_paths($class2_file);

		$this->server_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $class2_file);
		$this->http_path = 'http://'.$_SERVER['HTTP_HOST'].$this->server_path;
		$this->https_path = 'https://'.$_SERVER['HTTP_HOST'].$this->server_path;
		$this->class2_path = $class2_file;

		$this->e107_file_root = $_SERVER['DOCUMENT_ROOT'].$this->server_path;

		define("e_HTTP", $this->server_path);
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

	function ban() {
		global $sql;
		$ip = $this->getip();
		$wildcard = substr($ip, 0, strrpos($ip, ".")).".*";

		$tmp = gethostbyaddr(getenv('REMOTE_ADDR'));
		preg_match("/[\w]+\.[\w]+$/si", $tmp, $match);
		$bhost = $match[0];

		if ($ip != '127.0.0.1') {
			if ($sql->db_Select("banlist", "*", "banlist_ip='".$_SERVER['REMOTE_ADDR']."' OR banlist_ip='".USEREMAIL."' OR banlist_ip='$ip' OR banlist_ip='$wildcard' OR banlist_ip='$bhost'")) {
				// enter a message here if you want some text displayed to banned users ...
				exit;
			}
		}
	}

	function getip() {
		if(!$this->_ip_cache){
			if (getenv('HTTP_X_FORWARDED_FOR')) {
				$ip=$_SERVER['REMOTE_ADDR'];
				if (preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", getenv('HTTP_X_FORWARDED_FOR'), $ip3)) {
					$ip2 = array(
					'/^0\./',
					'/^127\.0\.0\.1/',
					'/^192\.168\..*/',
					'/^172\.16\..*/',
					'/^10..*/',
					'/^224..*/',
					'/^240..*/'
					);
					$ip=preg_replace($ip2, $ip, $ip3[1]);
				}
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			if ($ip == "") {
				$ip = "x.x.x.x";
			}
			$this->_ip_cache = $ip;
		}
		return $this->_ip_cache;
	}
}

?>