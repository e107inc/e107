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
|     $Revision: 1.5 $
|     $Date: 2004-10-30 02:30:06 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/


class ecache
{
	function e107cache_page_md5()
	{
		return md5(e_BASE.e_LANGUAGE.THEME.USERCLASS.e_QUERY);
	}

	function cache_fname($query)
	{
		global $FILES_DIRECTORY;
		$q = preg_replace("#\W#","_",$query);
		$fname = "./".e_BASE.$FILES_DIRECTORY."cache/".$q."-".$this -> e107cache_page_md5().".cache.php";
		return $fname;
	}

	function retrieve($query)
	{
		global $pref,$FILES_DIRECTORY;
		if($pref['cachestatus'])  //Save to file
		{
			if($cache_file = $this -> cache_fname($query))
			{
				$ret = file_get_contents($cache_file);
				$ret = substr($ret, 6);
				if($ret == false){ return false; }
				return ('' == $ret) ? '<!-- null -->' : $ret;
			}
			else
			{
				return FALSE;
			}
		}
	}

	function set($query, $text)
	{
		global $pref,$FILES_DIRECTORY;
		if($pref['cachestatus'])
		{
			$cache_file = $this -> cache_fname($query);
			file_put_contents($cache_file, "<?php\n<!-- BEGIN CACHE FILE: $query -->\n\n".$text."\n\n<!-- END CACHE FILE: $query -->");
			@chmod($cache_file, 0777);
		}
	}

	function clear($query)
	{
		global $pref,$FILES_DIRECTORY;
		if($pref['cachestatus'] || !$query)
		{
			$file = ($query) ? preg_replace("#\W#","_",$query)."*.cache.php" : "*.cache.php";
			$dir = "./".e_BASE.$FILES_DIRECTORY."cache/";
			$ret = $this -> delete($dir, $file);
		}
		return $ret;
	}

	function delete($dir, $pattern = "*.*")
	{
		$deleted = false;
		$pattern = str_replace(array("\*","\?"), array(".*","."), preg_quote($pattern));
		if (substr($dir,-1) != "/"){$dir .= "/";}
		if (is_dir($dir))
		{
			$d = opendir($dir);
			while ($file = readdir($d))
			{
				if (is_file($dir.$file) && ereg("^".$pattern."$", $file))
				{
					if (unlink($dir.$file))
					{
						$deleted[] = $file;
					}
				}
			}
			closedir($d);
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>