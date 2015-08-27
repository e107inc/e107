<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

class secure_image
{
	public $random_number;
	protected $HANDLERS_DIRECTORY;
	protected $IMAGES_DIRECTORY;
	protected $FONTS_DIRECTORY;
	protected $MYSQL_INFO;
	protected $THIS_DIR;
	protected $BASE_DIR;
	public $FONT_COLOR = "90,90,90";

	function secure_image()
	{
		
/*
		if ($user_func = e107::getOverride()->check($this,'secure_image'))
		{
	 		return call_user_func($user_func);
		}
 * */

	
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
		// FIX - new prefered configuration format - $E107_CONFIG
		if(isset($E107_CONFIG))
		{
			extract($E107_CONFIG);
		}

		$this->THIS_DIR 			= $imgp;
		$this->BASE_DIR 			= realpath($imgp.'..'.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		$this->HANDLERS_DIRECTORY 	= $HANDLERS_DIRECTORY;
		$this->FONTS_DIRECTORY 		= isset($CORE_DIRECTORY) ? $CORE_DIRECTORY."fonts/" : "e107_core/fonts/";
	//	$this->IMAGES_DIRECTORY 	= str_replace('/', DIRECTORY_SEPARATOR, $IMAGES_DIRECTORY);
        $this->IMAGES_DIRECTORY     =  $IMAGES_DIRECTORY;
		$this->MYSQL_INFO = array('db' => $mySQLdefaultdb, 'server' => $mySQLserver, 'user' => $mySQLuser, 'password' => $mySQLpassword, 'prefix' => $mySQLprefix);
	}

	function create_code()
	{
		if ($user_func = e107::getOverride()->check($this,'create_code'))
		{
	 		return call_user_func($user_func);
		}

		$pref = e107::getPref();
		$sql = e107::getDb();

		mt_srand ((double)microtime() * 1000000);
		$maxran = 1000000;
		$rand_num = mt_rand(0, $maxran);
		$datekey = date("r");
		$rcode = hexdec(md5($_SERVER['HTTP_USER_AGENT'] . serialize($pref). $rand_num . $datekey));
		$code = substr($rcode, 2, 6);
		$recnum = $this->random_number;
		$del_time = time()+1200;
		$sql->db_Insert("tmp", "'{$recnum}',{$del_time},'{$code}'");
		return $recnum;
	}

	/* Return TRUE if code is valid, otherwise return FALSE 
	 * 
	 */
	function verify_code($rec_num, $checkstr)
	{
		if ($user_func = e107::getOverride()->check($this,'verify_code'))
		{
	 		return call_user_func($user_func,$rec_num,$checkstr);
		}
		
		$sql = e107::getDb();
		$tp = e107::getParser();

		if ($sql->db_Select("tmp", "tmp_info", "tmp_ip = '".$tp -> toDB($rec_num)."'")) {
			$row = $sql->db_Fetch();
			$sql->db_Delete("tmp", "tmp_ip = '".$tp -> toDB($rec_num)."'");
			//list($code, $path) = explode(",", $row['tmp_info']);
			$code = intval($row['tmp_info']);
			return ($checkstr == $code);
		}
		return FALSE;
	}
	
	
	
	// Return an Error message (true) if check fails, otherwise return false. 
	function invalidCode($rec_num,$checkstr)
	{
		if ($user_func = e107::getOverride()->check($this,'invalidCode'))
		{
	 		return call_user_func($user_func,$rec_num,$checkstr);
		}
			
		if($this->verify_code($rec_num,$checkstr))
		{
			return false;	
		}
		else
		{
			return LAN_INVALID_CODE;	
		}
		
		return true;	
		
	}
	
	
	//XXX Discuss - Add more posibilities for themers? e_CAPTCHA_BGIMAGE, e_CAPTCH_WIDTH, e_CAPTCHA_HEIGHT?
	function r_image()
	{
		if ($user_func = e107::getOverride()->check($this,'r_image'))
		{
	 		return call_user_func($user_func);
		}
		
		if(defined('e_CAPTCHA_FONTCOLOR'))
		{
			$color = str_replace("#","", e_CAPTCHA_FONTCOLOR);	
		}
		else
		{
			$color = 'cccccc';		
		}
		
		$code = $this->create_code();
		return "<img src='".e_HTTP.$this->IMAGES_DIRECTORY."secimg.php?id={$code}&amp;clr={$color}' class='icon secure-image' alt='Missing Code' style='max-width:100%' />";
	}
	
	
	
	
	function renderImage() // Alias of r_image
	{
		return $this->r_image();			
	}


	function hex2rgb($hex) 
	{
		$hex = str_replace("#", "", $hex);

		if(strlen($hex) == 3) 
		{
			$r = hexdec(substr($hex,0,1).substr($hex,0,1));
			$g = hexdec(substr($hex,1,1).substr($hex,1,1));
			$b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} 
		else 
		{
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
		}
	   
		$rgb = array($r, $g, $b);

		return implode(",", $rgb); 
	}
	
	
	

	function renderInput()
	{
		if ($user_func = e107::getOverride()->check($this,'renderInput'))
		{
	 		return call_user_func($user_func);
		}
			
		$frm = e107::getForm();	
		return $frm->hidden("rand_num", $this->random_number).$frm->text("code_verify", "", 20, array("size"=>20,"title"=> LAN_ENTER_CODE,'required'=>1, 'placeholder'=>LAN_ENTER_CODE));
	}
	
	function renderLabel()
	{
		if ($user_func = e107::getOverride()->check($this,'renderLabel'))
		{
	 		return call_user_func($user_func);
		}
			
		return LAN_ENTER_CODE;	
	}
	

	/**
	 * Render the generated Image. Called without class2 environment (standalone).
	 */
	function render($qcode, $color='')
	{
		if($color)
		{
			$this->FONT_COLOR = $this->hex2rgb($color);
		}
		
	//	echo "COLOR: ".$this->FONT_COLOR;
		
		require_once($this->BASE_DIR.$this->HANDLERS_DIRECTORY."override_class.php");
		$over = new override;
		
		if ($user_func = $over->check($this,'render'))
		{
			
	 		return call_user_func($user_func,$qcode);
		}
		
		if(!is_numeric($qcode)){ exit; }
		$recnum = preg_replace('#\D#',"",$qcode);

		$imgtypes = array('png'=>"png",'gif'=>"gif",'jpg'=>"jpeg",);

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

		$path 		= $this->BASE_DIR.$this->IMAGES_DIRECTORY;
		$fontpath 	= $this->BASE_DIR.$this->IMAGES_DIRECTORY;
		$secureimg 	= array();

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
		
			// var_dump($secureimg);
			
			if(isset($secureimg['font']) && !is_readable($path.$secureimg['font']))
			{
				echo "Font missing"; // for debug only. translation not necessary.
				exit;
			}
	
			if(!is_readable($path.$secureimg['image'].$ext))
			{
				echo "Missing Background-Image: ".$secureimg['image'].$ext; // for debug only. translation not necessary.
				exit;
			}
			
		}
		else
		{
			$fontpath 				= $this->BASE_DIR.$this->FONTS_DIRECTORY;
			$secureimg['image'] 	= "generic/code_bg";
			$secureimg['angle']		= "0";
			$secureimg['color'] 	= $this->FONT_COLOR; // red,green,blue
			$secureimg['x']			= "1";
			$secureimg['y']			= "21";
			
			$num = rand(1,3);
			
			switch ($num) 
			{
				case 1:
					$secureimg['font'] 	= "chaostimes.ttf";
					$secureimg['size']	= "19";	
				break;
				
				case 2:
					$secureimg['font'] 	= "crazy_style.ttf";
					$secureimg['size']	= "18";
				break;
				
				case 3:
					$secureimg['font'] 	= "puchakhonmagnifier3.ttf"; 
					$secureimg['size']	= "19";	
				break;
			}
				
			
		}

		if(isset($secureimg['font']) && !is_readable($fontpath.$secureimg['font']))
		{
			echo "Font missing"; // for debug only. translation not necessary.
			exit;
		}
		
		
		if(isset($secureimg['image']) && !is_readable($path.$secureimg['image'].$ext))
		{
			echo "Missing Background-Image: ".$secureimg['image'].$ext; // for debug only. translation not necessary.
			exit;
		}

		$bg_file = $secureimg['image'];
		
		switch($type)
		{
			case "png": // preferred 
				$image = imagecreatefrompng($path.$bg_file.".png");
				imagealphablending($image, true);
			break;
			
			case "gif":
				$image = imagecreatefromgif($path.$bg_file.".gif");
				imagealphablending($image, true);
			break;
			
			case "jpeg":
				$image = imagecreatefromjpeg($path.$bg_file.".jpg");
			break;
		}


        // removing the black from the placeholder
      	 $image  = $this->imageCreateTransparent(100,35); //imagecreatetruecolor(100, 35);
	
		

		if(isset($secureimg['color']))
		{
			$tmp = explode(",",$secureimg['color']);
			$text_color = imagecolorallocate($image,$tmp[0],$tmp[1],$tmp[2]);
						
		}
		else
		{
			$text_color = imagecolorallocate($image, 90, 90, 90);
		}
		
		header("Content-type: image/{$type}");

		if(isset($secureimg['font']) && is_readable($fontpath.$secureimg['font']))
		{
			imagettftext($image, $secureimg['size'],$secureimg['angle'], $secureimg['x'], $secureimg['y'], $text_color,$fontpath.$secureimg['font'], $code);
		}
		else
		{
			imagestring ($image, 5, 12, 2, $code, $text_color);
		}

		imagesavealpha($image, true);

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


	function imageCreateTransparent($x, $y) 
	{
    	$imageOut = imagecreatetruecolor($x, $y);
    	$backgroundColor = imagecolorallocatealpha($imageOut, 0, 0, 0, 127);
    	imagefill($imageOut, 0, 0, $backgroundColor);
    	return $imageOut;
	}
	




}
?>