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
	var $MYSQL_INFO;
	var $THIS_DIR;
	var $BASE_DIR;

	function secure_image()
	{
		list($usec, $sec) = explode(" ", microtime());
		$this->random_number = str_replace(".", "", $sec.$usec);

		$imgp = dirname(__FILE__);
		if (substr($imgp,-1,1) != DIRECTORY_SEPARATOR) $imgp .= DIRECTORY_SEPARATOR;
		$imgp = str_replace('/', DIRECTORY_SEPARATOR, $imgp);
		@include($imgp.'..'.DIRECTORY_SEPARATOR.'e107_config.php');
		if(!isset($mySQLserver))
		{
			if(defined('e_DEBUG'))
			{
				echo "FAILED TO LOAD e107_config.php in secure_img_handler.php";
			}
			exit;
		}

		$this->THIS_DIR 			= $imgp;
		$this->BASE_DIR 			= realpath($imgp.'..'.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		$this->HANDLERS_DIRECTORY 	= $HANDLERS_DIRECTORY;
		$this->IMAGES_DIRECTORY 	= str_replace('/', DIRECTORY_SEPARATOR, $IMAGES_DIRECTORY);
		$this->MYSQL_INFO = array('db' => $mySQLdefaultdb, 'server' => $mySQLserver, 'user' => $mySQLuser, 'password' => $mySQLpassword, 'prefix' => $mySQLprefix);
	}


	function create_code()
	{
		global $pref, $sql;

		//$imgp = addslashes($this->BASE_DIR.$this->IMAGES_DIRECTORY); // removed

		mt_srand ((double)microtime() * 1000000);
		$maxran = 1000000;
		$rand_num = mt_rand(0, $maxran);
		$datekey = date("r");
		$rcode = hexdec(md5($_SERVER['HTTP_USER_AGENT'] . serialize($pref). $rand_num . $datekey));
		$code = substr($rcode, 2, 6);
		$recnum = $this->random_number;
		$del_time = time()+1200;
		//$sql->db_Insert("tmp", "'{$recnum}',{$del_time},'{$code},{$imgp}'"); // unsure why $imgp is included here
		$sql->db_Insert("tmp", "'{$recnum}',{$del_time},'{$code}'"); // $imgp removed
		return $recnum;
	}


	function verify_code($rec_num, $checkstr)
	{
		global $sql, $tp;

		if(!is_numeric($rec_num))
		{
			return FALSE;
		}
		
		if ($sql->db_Select("tmp", "tmp_info", "tmp_ip = '".$tp -> toDB($rec_num)."'"))
		{
			$row = $sql->db_Fetch(MYSQL_ASSOC);
			$sql->db_Delete("tmp", "tmp_ip = '".$tp -> toDB($rec_num)."'");
			//list($code, $path) = explode(",", $row['tmp_ip']);
			$code = intval($row['tmp_info']);
			return ($checkstr == $code);
		}

		return FALSE;
	}


	/**
	 * Render Img Tag
	 */
	function r_image()
	{
		$code = $this->create_code();
		return "<img src='".e_HTTP.$this->HANDLERS_DIRECTORY."secure_img_render.php?{$code}' alt='' />";
	}



	/**
	 * Render the generated Image. Called without class2 environment (standalone).
	 */
	function render($qcode)
	{
		if(!is_numeric($qcode)){ exit; }
		$recnum = preg_replace('#\D#',"",$qcode);

		$imgtypes = array('jpg'=>"jpeg",'png'=>"png",'gif'=>"gif");

		@mysql_connect($this->MYSQL_INFO['server'], $this->MYSQL_INFO['user'],  $this->MYSQL_INFO['password']) || die('db connection failed');
		@mysql_select_db($this->MYSQL_INFO['db']);

		$result = mysql_query("SELECT tmp_info FROM {$this->MYSQL_INFO['prefix']}tmp WHERE tmp_ip = '{$recnum}'");
		if(!$result || !($row = mysql_fetch_array($result, MYSQL_ASSOC)))
		{
			// echo "Render Failed";
			// echo "SELECT tmp_info FROM {$this->MYSQL_INFO['prefix']}tmp WHERE tmp_ip = '{$recnum}'";
			exit;
		}

		$code = intval($row['tmp_info']); // new value

		$type = "none";

		foreach($imgtypes as $k=>$t)
		{
			if(function_exists("imagecreatefrom".$t))
			{
				$ext = ".".$k;
				$type = $t;
				break;
			}
		}

		$path = $this->BASE_DIR.$this->IMAGES_DIRECTORY;
		$secureimg = array();

		if(is_readable($path."secure_image_custom.php"))
		{

			require_once($path."secure_image_custom.php");
			/*   Example secure_image_custom.php file:

			$secureimg['image'] = "code_bg_custom";  // filename excluding the .ext
			$secureimg['size']	= "15";
			$secureimg['angle']	= "0";
			$secureimg['x']		= "6";
			$secureimg['y']		= "22";
			$secureimg['font'] 	= "imagecode.ttf";
			$secureimg['color'] = "90,90,90"; // red,green,blue

			*/
			$bg_file = $secureimg['image'];

			if(!is_readable($path.$secureimg['font']))
			{
				echo "Font missing"; // for debug only. translation not necessary.
				exit;
			}

			if(!is_readable($path.$secureimg['image'].$ext))
			{
				echo "Missing Background-Image: ".$secureimg['image'].$ext; // for debug only. translation not necessary.
				exit;
			}
			// var_dump($secureimg);
		}
		else
		{
			$bg_file = "generic/code_bg";
		}

		switch($type)
		{
			case "jpeg":
				$image = ImageCreateFromJPEG($path.$bg_file.".jpg");
				break;
			case "png":
				$image = ImageCreateFromPNG($path.$bg_file.".png");
				break;
			case "gif":
				$image = ImageCreateFromGIF($path.$bg_file.".gif");
				break;
		}



		if(isset($secureimg['color']))
		{
			$tmp = explode(",",$secureimg['color']);
			$text_color = ImageColorAllocate($image,$tmp[0],$tmp[1],$tmp[2]);
		}
		else
		{
			$text_color = ImageColorAllocate($image, 90, 90, 90);
		}

		header("Content-type: image/{$type}");

		if(isset($secureimg['font']) && is_readable($path.$secureimg['font']))
		{
			imagettftext($image, $secureimg['size'],$secureimg['angle'], $secureimg['x'], $secureimg['y'], $text_color,$path.$secureimg['font'], $code);
		}
		else
		{
			imagestring ($image, 5, 12, 2, $code, $text_color);
		}

		switch($type)
		{
			case "jpeg":
				imagejpeg($image);
				break;
			case "png":
				imagepng($image);
				break;
			case "gif":
				imagegif($image);
				break;
		}


	}

}
