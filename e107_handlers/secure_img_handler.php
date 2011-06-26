<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/secure_img_handler.php,v $
 * $Revision$
 * $Date$
 * $Author$
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

		$pref = e107::getPref();
		$sql = e107::getDb();
		
/*
		require_once('e107_class.php');
		$e107 = new e107(false, false);
		$e107->set_paths();

		$imgpy = str_replace($HANDLERS_DIRECTORY, "", $e107->file_path);
*/
		$imgp = str_replace($this->HANDLERS_DIRECTORY, $this->IMAGES_DIRECTORY, $this->THIS_DIR);

		mt_srand ((double)microtime() * 1000000);
		$maxran = 1000000;
		$rand_num = mt_rand(0, $maxran);
		$datekey = date("r");
		$rcode = hexdec(md5($_SERVER['HTTP_USER_AGENT'] . serialize($pref). $rand_num . $datekey));
		$code = substr($rcode, 2, 6);
		$recnum = $this->random_number;
		$del_time = time()+1200;
		$sql->db_Insert("tmp", "'{$recnum}',{$del_time},'{$code},{$imgp}'");
		return $recnum;
	}


	function verify_code($rec_num, $checkstr) 
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		
		if ($sql->db_Select("tmp", "tmp_info", "tmp_ip = '".$tp -> toDB($rec_num)."'")) {
			$row = $sql->db_Fetch();
			$sql->db_Delete("tmp", "tmp_ip = '".$tp -> toDB($rec_num)."'");
			list($code, $path) = explode(",", $row['tmp_info']);
			return ($checkstr == $code);
		}
		return FALSE;
	}

	function r_image()
	{
		$code = $this->create_code();
		return "<img src='".e_HTTP.$this->HANDLERS_DIRECTORY."secure_img_render.php?{$code}' class='icon secure-image' alt='' />";
	}
	
	
	
	/**
	 * Render the generated Image. 
	 */
	function render()
	{
		$sql = e107::getDb();

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
		
			
		$path = e_IMAGE;
		// TODO - add support for adding it in the THEME folder. 
		
		if(is_readable(e_IMAGE."secure_image_custom.php"))
		{
			
			require_once(e_IMAGE."secure_image_custom.php");

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