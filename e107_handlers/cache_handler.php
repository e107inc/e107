<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /e107_handlers/cache_handler.php
|
|        http://e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

class ecache {

        function e107cache_page_md5(){
                return md5(e_BASE.e_LANGUAGE.e_THEME.USERCLASS);
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
                        $cache_file = "./".e_BASE.$FILES_DIRECTORY."cache/".md5($query)."-".$this -> e107cache_page_md5().".cache.php";
                        if($fp = fopen($cache_file, 'rb')) {
                                fseek($fp,6);
                                $ret = fread($fp, filesize($cache_file));
                                fclose($fp);
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
                        $cache_file = "./".e_BASE.$FILES_DIRECTORY."cache/".md5($query)."-".$this -> e107cache_page_md5().".cache.php";
                        $fp = fopen($cache_file, 'w+');
                        fwrite ($fp, "<?php\n<!-- BEGIN CACHE FILE: $query -->\n\n".$text."\n\n<!-- END CACHE FILE: $query -->");
                        fclose($fp);
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
                        $file = ($query) ? md5($query)."-*.cache.php" : "*.cache.php";
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