<?php

/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/e107_class.php,v $
|     $Revision: 1.12 $
|     $Date: 2005-05-19 08:28:48 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

class e107{
	
	var $e107_dirs;
	var $http_path;
	var $file_path;

	function e107($e107_paths, $class2_file){
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		if(defined("COMPRESS_OUTPUT") && COMPRESS_OUTPUT === true) {
			ob_start ("ob_gzhandler");
		}
		$this->e107_dirs = $e107_paths;
		$this->set_e107_dirs($class2_file);
	}

	function set_e107_dirs($class2_file){
		// go off and fix missing doc root paths. also fix windows paths.
		$this->fix_doc_root();

		$e107_root_folder = dirname($class2_file);
		$e107_root_folder = $this->fix_windows_paths($e107_root_folder);
		$e107_root_foler_array = explode("/", $e107_root_folder);
		
		// the code chunk below fixes what appears to be either a php bug, or a too common server mis-config, where __FILE__ and DOCUMENT_ROOT
		// dont tally - don't ask me to explain it, figure it out for yourself :)
		if(!strstr($e107_root_folder, $_SERVER['DOCUMENT_ROOT'])) {
			$temp_path = $_SERVER['DOCUMENT_ROOT'].$_SERVER['PATH_INFO'];
			foreach ($e107_root_foler_array as $key => $val) {
				if(!strstr($temp_path, $val) && $val != "") {
					unset($e107_root_foler_array[$key]);
				}
			}
			$e107_root_folder = implode("/", $e107_root_foler_array);
		}
		
		// replace the document root with "" (nothing) in the e107 root path, gives us out e_HTTP path :)
		$server_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $e107_root_folder)."/";
		$this->http_path = $server_path;
		$this->file_path = $_SERVER['DOCUMENT_ROOT'].$server_path;
		
		// For compatability
		define("e_HTTP", $server_path);
	}

	function fix_windows_paths($path) {
		return str_replace(array('\\\\', '\\'), array('/', '/'), $path);
	}

	function fix_doc_root() {
		if (!$_SERVER['DOCUMENT_ROOT']) {
			$_SERVER['PATH_INFO'] = $this->fix_windows_paths($_SERVER['PATH_INFO']);
			$_SERVER['PATH_TRANSLATED'] = $this->fix_windows_paths($_SERVER['PATH_TRANSLATED']);
			$_SERVER['DOCUMENT_ROOT'] = str_replace($_SERVER['PATH_INFO'], '', $_SERVER['PATH_TRANSLATED']);
		} else {
			$_SERVER['DOCUMENT_ROOT'] = $this->fix_windows_paths($_SERVER['DOCUMENT_ROOT']);
		}
	}

	function http_abs_location($dir_type = false, $extended = false, $secure = false) {
		return "{$this->http_path}{$this->e107_dirs[$dir_type]}{$extended}";
	}

	function ban() {
		global $sql;
		$ip = $this->getip();
		$wildcard = substr($ip, 0, strrpos($ip, ".")).".*";

		$tmp = gethostbyaddr(getenv('REMOTE_ADDR'));
		preg_match("/[\w]+\.[\w]+$/si", $tmp, $match);
		$bhost = $match[0];

		if ($ip != '127.0.0.1') {
			if ($sql->db_Select("banlist", "*", "banlist_ip='{$_SERVER['REMOTE_ADDR']}' OR banlist_ip='".USEREMAIL."' OR banlist_ip='{$ip}' OR banlist_ip='{$wildcard}' OR banlist_ip='{$bhost}'")) {
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
					$ip = preg_replace($ip2, $ip, $ip3[1]);
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