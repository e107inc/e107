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


class secure_image
{
	var $random_number;
	var $HANDLERS_DIRECTORY;
	var $IMAGES_DIRECTORY;
	var $THIS_DIR;
	
	function secure_image()
	{
		list($usec, $sec) = explode(" ", microtime());
		$this->random_number = str_replace(".", "", $sec.$usec);
				
		$imgp = dirname(__FILE__);
		if (substr($imgp,-1,1) != '/') $imgp .= '/';
		if(!require($imgp.'../e107_config.php'))
		{
			if(defined('e_DEBUG'))
			{
				echo "FAILED TO LOAD e107_config.php in secure_img_handler.php";	
			}			
		}	
		
		$this->THIS_DIR 			= $imgp;
		$this->HANDLERS_DIRECTORY 	= $HANDLERS_DIRECTORY;
		$this->IMAGES_DIRECTORY 	= $IMAGES_DIRECTORY;
	}


	function create_code() 
	{
		global $pref, $sql;
		
		$imgp = str_replace($this->HANDLERS_DIRECTORY, $this->IMAGES_DIRECTORY, $this->THIS_DIR);

		mt_srand ((double)microtime() * 1000000);
		$maxran = 1000000;
		$rand_num = mt_rand(0, $maxran);
		$datekey = date("r");
		$rcode = hexdec(md5($_SERVER['HTTP_USER_AGENT'] . serialize($pref). $rand_num . $datekey));
		$code = substr($rcode, 2, 6);
		$recnum = $this->random_number;
		$del_time = time()+1200;
		$sql->db_Insert("tmp", "'{$recnum}',{$del_time},'{$code},{$imgp}'"); // unsure why $imgp is included here
		return $recnum;
	}


	function verify_code($rec_num, $checkstr)
	{
		global $sql, $tp;
		if ($sql->db_Select("tmp", "tmp_info", "tmp_ip = '".$tp -> toDB($rec_num)."'")) 
		{
			$row = $sql->db_Fetch();
			$sql->db_Delete("tmp", "tmp_ip = '".$tp -> toDB($rec_num)."'");
			list($code, $path) = explode(",", $row[0]);
			return ($checkstr == $code);
		}
		return FALSE;
	}


	function r_image()
	{	
		$code = $this->create_code();
		return "<img src='".e_BASE.$this->HANDLERS_DIRECTORY."secure_img_render.php?{$code}' alt='' />";
	}

}


?>