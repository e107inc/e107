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
|     $Revision: 1.21 $
|     $Date: 2005-05-23 01:45:54 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

class e107{

	var $server_path;
	var $e107_dirs;
	var $http_path;
	var $file_path;
	var $relative_base_path;

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

		// the code chunk below fixes what appears to be either a php bug, or a too common server mis-config, where __FILE__ and DOCUMENT_ROOT
		// dont tally - don't ask me to explain it, figure it out for yourself :)
		if(!strstr($e107_root_folder, $_SERVER['DOCUMENT_ROOT'])) {
			$e107_root_folder_array = explode("/", $e107_root_folder);
			$temp_path = $_SERVER['DOCUMENT_ROOT'].$_SERVER['PATH_INFO'];
			foreach ($e107_root_folder_array as $key => $val) {
				if($val != "" && !strstr($temp_path, $val)) {
					unset($e107_root_folder_array[$key]);
				}
			}
			$e107_root_folder = implode("/", $e107_root_folder_array);
		}

		// replace the document root with "" (nothing) in the e107 root path, gives us out e_HTTP path :)
		$server_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $e107_root_folder)."/";

		if ($_SERVER['SERVER_PORT'] != 80) {
			$url_port = ":{$_SERVER['SERVER_PORT']}";
		} else {
			$url_port = "";
		}

		$this->http_path = 'http://'.$_SERVER['HTTP_HOST'].$url_port.$server_path;
		$this->https_path = 'https://'.$_SERVER['HTTP_HOST'].$url_port.$server_path;

		$this->file_path = $_SERVER['DOCUMENT_ROOT'].$server_path;

		$this->server_path = $server_path;

		// For compatability
		define("e_HTTP", $server_path);

		$page_path = dirname($_SERVER['PHP_SELF']).'/';
		$relative_path = str_replace($this->server_path, '', $page_path);
		$link_prefix = '';
		$url_prefix = substr($_SERVER['PHP_SELF'], strlen($this->server_path), strrpos($_SERVER['PHP_SELF'], "/") + 1 - strlen($this->server_path));
		$tmp = explode("?", $url_prefix);
		$num_levels = substr_count($tmp[0], "/");
		for ($i = 1; $i <= $num_levels; $i++) {
			$link_prefix .= "../";
		}
		$this->relative_base_path = $link_prefix;
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
		global $pref;
		if ($pref['ssl_enabled']) {
			$secure = true;
		}
		$site_uri = ($secure ? $this->https_path : $this->http_path);
		return "{$site_uri}".($dir_type ? $this->e107_dirs[$dir_type] : "").($extended ? $extended : "");
	}

	function ban() {
		global $sql;
		$ip = $this->getip();
		$wildcard = substr($ip, 0, strrpos($ip, ".")).".*";

		$tmp = gethostbyaddr(getenv('REMOTE_ADDR'));
		preg_match("/[\w]+\.[\w]+$/si", $tmp, $match);
		$bhost = (isset($match[0])) ? $match[0] : "" ;

		if ($ip != '127.0.0.1') {
			if ($sql->db_Select("banlist", "*", "banlist_ip='{$_SERVER['REMOTE_ADDR']}' OR banlist_ip='".USEREMAIL."' OR banlist_ip='{$ip}' OR banlist_ip='{$wildcard}' OR banlist_ip='{$bhost}'")) {
				// enter a message here if you want some text displayed to banned users ...
				exit;
			}
		}
	}

	function getip() {
		if(!isset($this->_ip_cache)){
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

	function get_memory_usage(){
		global $dbg;
		if(function_exists("memory_get_usage")){
			$memusage = memory_get_usage();
			$memunit = 'b';
			if ($memusage > 1024){
				$memusage = $memusage / 1024;
				$memunit = 'kb';
			}
			if ($memusage > 1024){
				$memusage = $memusage / 1024;
				$memunit = 'mb';
			}
			if ($memusage > 1024){
				$memusage = $memusage / 1024;
				$memunit = 'gb';
			}
			return (number_format($memusage, 2).$memunit);
		} else {
			return ('Unknown');
		}
	}
}

?>