<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class e_file
{
	function get_files($path, $fmask = '', $omit='standard', $recurse_level = 0, $current_level = 0)
	{
		$ret = array();
		if($recurse_level != 0 && $current_level > $recurse_level)
		{
			return $ret;
		}
		if(substr($path,-1) == '/')
		{
			$path = substr($path, 0, -1);
		}

		if(!$handle = opendir($path))
		{
			return $ret;
		}
		if($omit == 'standard')
		{
			$rejectArray = array('^\.$','^\.\.$','^\/$','^CVS$','^\.svn','Thumbs\.db','thumbs\.db','.*\._$','^\.htaccess$','index\.html','null\.txt');
		}
		else
		{
			if(is_array($omit))
			{
				$rejectArray = $omit;
			}
			else
			{
				$rejectArray = array($omit);
			}
		}
		while (false !== ($file = readdir($handle)))
		{
			if(is_dir($path.'/'.$file))
			{
				if($file != '.' && $file != '..' && $file != 'CVS' && ($file != '.svn') && $recurse_level > 0 && $current_level < $recurse_level)
				{
					$xx = $this->get_files($path.'/'.$file, $fmask, $omit, $recurse_level, $current_level+1);
					$ret = array_merge($ret,$xx);
				}
			}
			elseif ($fmask == '' || preg_match("#".$fmask."#", $file))
			{
				$rejected = FALSE;

				foreach($rejectArray as $rmask)
				{
					if(preg_match("#".$rmask."#", $file))
					{
						$rejected = TRUE;
						break;
					}
				}
				if($rejected == FALSE)
				{
					$finfo['path'] = $path."/";  // important: leave this slash here and update other file instead.
					$finfo['fname'] = $file;
					$ret[] = $finfo;
				}
			}
		}
		return $ret;
	}

	function get_dirs($path, $fmask = '', $omit='standard')
	{
		$ret = array();
		if(substr($path,-1) == '/')
		{
			$path = substr($path, 0, -1);
		}

		if(!$handle = opendir($path))
		{
			return $ret;
		}
		if($omit == 'standard')
		{
			$rejectArray = array('^\.$','^\.\.$','^\/$','^\.svn','^CVS$','thumbs\.db','.*\._$');
		}
		else
		{
			if(is_array($omit))
			{
				$rejectArray = $omit;
			}
			else
			{
				$rejectArray = array($omit);
			}
		}
		while (false !== ($file = readdir($handle)))
		{
			if(is_dir($path.'/'.$file) && ($fmask == '' || preg_match("#".$fmask."#", $file)))
			{
				$rejected = FALSE;
				foreach($rejectArray as $rmask)
				{
					if(preg_match("#".$rmask."#", $file))
					{
						$rejected = TRUE;
						break;
					}
				}
				if($rejected == FALSE)
				{
					$ret[] = $file;
				}
			}
		}
		return $ret;
	}

	function rmtree($dir)
	{
		if (substr($dir, strlen($dir)-1, 1) != '/')
		{
			$dir .= '/';
		}
		if ($handle = opendir($dir))
		{
			while ($obj = readdir($handle))
			{
				if ($obj != '.' && $obj != '..')
				{
					if (is_dir($dir.$obj))
					{
						if (!$this->rmtree($dir.$obj))
						{
							return false;
						}
					}
					elseif (is_file($dir.$obj))
					{
						if (!unlink($dir.$obj))
						{
							return false;
						}
					}
				}
			}

			closedir($handle);

			if (!@rmdir($dir))
			{
				return false;
			}
			return true;
		}
		return false;
	}

}
?>
