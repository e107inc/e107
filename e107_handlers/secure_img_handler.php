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
		
		if(!is_numeric($rec_num))
		{
			return FALSE;
		}
		
		if ($sql->db_Select("tmp", "tmp_info", "tmp_ip = '".$tp -> toDB($rec_num)."'")) 
		{
			$row = $sql->db_Fetch();
			$sql->db_Delete("tmp", "tmp_ip = '".$tp -> toDB($rec_num)."'");
			list($code, $path) = explode(",", $row[0]);
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
		return "<img src='".e_BASE.$this->HANDLERS_DIRECTORY."secure_img_render.php?{$code}' alt='' />";
	}
	
	
	
	/**
	 * Render the generated Image. 
	 */
	function render()
	{
		global $sql;
	//	while (ob_end_clean());
		
		$imgtypes = array('jpg'=>"jpeg",'png'=>"png",'gif'=>"gif");
		
		$recnum = preg_replace("#\D#","",e_QUERY);
		
		if($recnum == false){ exit; }
		
		$sql->db_Select_gen("SELECT tmp_info FROM #tmp WHERE tmp_ip = '{$recnum}' LIMIT 1");

		if(!$row = $sql->db_Fetch(MYSQL_ASSOC))
		{
			echo "Render Failed";
			exit;
		}
		
		list($code, $url) = explode(",",$row['tmp_info']);
		
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
		
	//	$path = realpath(dirname(__FILE__)."/../")."/".$this->IMAGES_DIRECTORY;
		
		$path = e_IMAGE;
	
		if(is_readable(e_IMAGE."secure_image_custom.php"))
		{
			
			require_once(e_IMAGE."secure_image_custom.php");
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
			
			if(!is_readable(e_IMAGE.$secureimg['font']))
			{
				echo "Font missing"; // for debug only. translation not necessary.
				exit;
			}
			
			if(!is_readable(e_IMAGE.$secureimg['image'].$ext))
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
		
		ob_end_clean();
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


?>