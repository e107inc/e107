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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/cache_handler.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-09-24 09:51:51 $
|     $Author: loloirie $
+----------------------------------------------------------------------------+
*/

if(!function_exists('file_get_contents')) {
	function file_get_contents($filename) {
		$fd = fopen("$filename", "rb");
		$content = fread($fd, filesize($filename));
		fclose($fd);
		return $content;
	}
}

if (!function_exists('file_put_contents')) {
   function file_put_contents($filename, $data)
   {
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

class ecache {

        function e107cache_page_md5(){
                return md5(e_BASE.e_LANGUAGE.THEME.USERCLASS);
        }

		function cache_fname($query){
			global $FILES_DIRECTORY;
			$q = preg_replace("#\W#","_",$query);
			$fname = "./".e_BASE.$FILES_DIRECTORY."cache/".$q."-".$this -> e107cache_page_md5().".cache.php";
			return $fname;
		}

        function retrieve($query){
                global $pref,$FILES_DIRECTORY;
                if('1' == $pref['cachestatus']){  //Save to db
                        global $sql;
                        if(!is_object($sql)){$sql = new db;}
                        $qry = $query."-".$this -> e107cache_page_md5();
                        if($sql -> db_Select("cache", "cache_data", "cache_url='$qry' ")){
                                $row = $sql -> db_Fetch();
                                return "\n\n<!-- BEGIN CACHE DB: $query -->\n\n".stripslashes($row['cache_data'])."\n<!-- END CACHE DB: $query -->\n\n";
                        } else {
                                return FALSE;
                        }
                }
                if('2' == $pref['cachestatus']){  //Save to file
                        if($cache_file = $this -> cache_fname($query)){
                                $ret = file_get_contents($cache_file);
                                $ret = substr($ret, 6);
								if($ret == false){ return false; }
                                return ('' == $ret) ? '<!-- null -->' : $ret;
                        } else {
                                return FALSE;
                        }
                }
        }

        function set($query, $text){
                global $pref,$FILES_DIRECTORY;
                if('1' == $pref['cachestatus']){
                        global $sql;
                        $query = $query."-".$this -> e107cache_page_md5();
                        if(!is_object($sql)){$sql = new db;}
                        $sql -> db_Insert("cache", "'$query', '".time()."', '".mysql_escape_string($text)."' ");
                }
                if('2' == $pref['cachestatus']){
                        $cache_file = $this -> cache_fname($query);
                        file_put_contents($cache_file, "<?php\n<!-- BEGIN CACHE FILE: $query -->\n\n".$text."\n\n<!-- END CACHE FILE: $query -->");
                        @chmod($cache_file, 0777);
                }
        }

        function clear($query){
                global $pref,$FILES_DIRECTORY;
                if('1' == $pref['cachestatus'] || !$query){
                        global $sql;
                        if(!is_object($sql)){$sql = new db;}
                        $ret = $sql -> db_Delete("cache", "cache_url LIKE '%".$query."%' ");
                }
                if('2' == $pref['cachestatus'] || !$query){
                        $file = ($query) ? preg_replace("#\W#","_",$query)."*.cache.php" : "*.cache.php";
                        $dir = "./".e_BASE.$FILES_DIRECTORY."cache/";
                        $ret = $this -> delete($dir, $file);
                }
                return $ret;
        }

        function delete($dir, $pattern = "*.*") {
                $deleted = false;
                $pattern = str_replace(array("\*","\?"), array(".*","."), preg_quote($pattern));
                if (substr($dir,-1) != "/") $dir.= "/";
                if (is_dir($dir)) {
                        $d = opendir($dir);
                        while ($file = readdir($d)) {
                                if (is_file($dir.$file) && ereg("^".$pattern."$", $file)) {
                                        if (unlink($dir.$file)) {
                                                $deleted[] = $file;
                                        }
                                }
                        }
                        closedir($d);
                        return true;
                } else {
                        return false;
                }
        }
}
?>