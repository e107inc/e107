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


/**
 *
 */
class secure_image
{
	public $random_number;
	protected $HANDLERS_DIRECTORY;
	protected $IMAGES_DIRECTORY;
	protected $FONTS_DIRECTORY;
	protected $BASE_DIR;
	public $FONT_COLOR = "90,90,90";
	private $secret;

	function __construct()
	{
		
/*
		if ($user_func = e107::getOverride()->check($this,'secure_image'))
		{
	 		return call_user_func($user_func);
		}
 * */
        $this->createCode();

        $this->BASE_DIR             = e_BASE;

        $CORE_DIRECTORY             = e107::getFolder('CORE');
		$this->HANDLERS_DIRECTORY 	= e107::getFolder('HANDLERS');
		$this->FONTS_DIRECTORY 		= !empty($CORE_DIRECTORY) ? $CORE_DIRECTORY."fonts/" : "e107_core/fonts/";
	    $this->IMAGES_DIRECTORY     =  e107::getFolder('IMAGES');

	}

	/**
	 * @deprecated Use createCode() instead.
	 * Legacy version of createCode();
	 * @return int
	 */
	public function create_code()
	{
		return $this->createCode();
	}


	/**
	 * Generates a public code and a secret code. Returns the public code.
	 * @return int
	 */
	public function createCode()
	{
		if ($user_func = e107::getOverride()->check($this,'create_code'))
		{
	 		return call_user_func($user_func);
		}

		list($usec, $sec)     = explode(" ", microtime());
		$this->random_number  = str_replace(".", "", $sec.$usec);

		$this->secret = e107::getUserSession()->generateRandomString('*****');

		$secImg = e107::getSession('secureImage');

		$list = $secImg->get('secret');

		$maxCache = 6;

		if(!empty($list) && count($list) > $maxCache)
		{
			$total = count($list) - $maxCache;
			$c = 1;
			foreach($list as $key=>$v)
			{
				if($c > $total)
				{
					continue;
				}

				$secImg->clear('secret/'.$key);
				$c++;
			}
		}


		$secImg->set('secret/'.$this->random_number, $this->secret);

		return $this->random_number;
	}

	/**
	 * The secret code that should be entered by the user. Must be called after createCode();
	 * @return mixed
	 */
	public function getSecret()
	{
		return $this->secret;
	}


	/**
	 * @deprecated Use invalidCode() instead. Returns true when the code doesn't match.
	 * Return true if code is valid, otherwise return FALSE
	 * @param integer $recnum The public code - returned by create_code()
	 * @param string $checkstr - code entered by the user.
	 * @return bool|mixed
	 */
	public function verify_code($recnum, $checkstr)
	{
		if ($user_func = e107::getOverride()->check($this,'verify_code'))
		{
	 		return call_user_func($user_func,$recnum,$checkstr);
		}

		$tmp = e107::getSession('secureImage')->get('secret');
		$secret = varset($tmp[$recnum]);

		if(!empty($secret) && ($secret === $checkstr))
		{
			e107::getSession('secureImage')->clear();

			return true;
		}
		else
		{
			return false;
		}

	}
	

	/**
	 * Returns an Error message (true) if check fails, otherwise return false.
	 * @param $rec_num
	 * @param $checkstr
	 * @return bool
	 */
	function invalidCode($rec_num=null, $checkstr=null)
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

	}
	

	/**
	 * @deprecated Use renderImage() instead.
	 * @return string
	 */
	public function r_image()
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
	
		return "<img src='".e_IMAGE_ABS."secimg.php?id={$this->random_number}&amp;clr={$color}' class='icon secure-image' alt='Missing Code' style='max-width:100%' />";
	}


	/**
	 * Return the rendered code/image.
	 * @return string
	 */
	public function renderImage() // Alias of r_image
	{
		return $this->r_image();			
	}


	/**
	 * @param $hex
	 * @return string
	 */
	private function hex2rgb($hex)
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


	/**
	 * Render the input where the user will enter the code.
	 * @return string
	 */
	function renderInput()
	{
		if ($user_func = e107::getOverride()->check($this,'renderInput'))
		{
	 		return call_user_func($user_func);
		}
			
		$frm = e107::getForm();	
		return $frm->hidden("rand_num", $this->random_number).$frm->text("code_verify", "", 20, array( "size"=>20, 'required'=>1, 'placeholder'=>LAN_ENTER_CODE, 'autocomplete' => 'off'));
	}



	/**
	 * Return the label to accompany the input.
	 * @return mixed|string
	 */
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
	 * @param $qcode
	 * @param string $color
	 * @return mixed
	 */
	function render($qcode, $color='')
	{
		if($color)
		{
			$this->FONT_COLOR = $this->hex2rgb($color);
		}
		
	//	echo "COLOR: ".$this->FONT_COLOR;
		$over = e107::getOverride();

		if ($user_func = $over->check($this,'render'))
		{
			
	 		return call_user_func($user_func,$qcode);
		}

		
		if(!is_numeric($qcode)){ exit; }
		$recnum = preg_replace('#\D#',"",$qcode);

		$imgtypes = array('png'=>"png",'gif'=>"gif",'jpg'=>"jpeg",);

		/** @FIXME - needs to use mysql class. */

	//	@mysql_connect($this->MYSQL_INFO['mySQLserver'], $this->MYSQL_INFO['mySQLuser'],  $this->MYSQL_INFO['mySQLpassword']) || die('db connection failed');
	//	@mysql_select_db($this->MYSQL_INFO['mySQLdefaultdb']);

	//	$result = mysql_query("SELECT tmp_info FROM {$this->MYSQL_INFO['mySQLprefix']}tmp WHERE tmp_ip = '{$recnum}'");
	//	if(!$result || !($row = mysql_fetch_array($result, MYSQL_ASSOC)))
		{
			// echo "Render Failed";
			// echo "SELECT tmp_info FROM {$this->MYSQL_INFO['prefix']}tmp WHERE tmp_ip = '{$recnum}'";
	//		exit;
		}

	//	$code = intval($row['tmp_info']); // new value

		$tmp = e107::getSession('secureImage')->get('secret');
		if(isset($tmp[$recnum]))
		{
			$code = $tmp[$recnum];
		}
		else
		{

			echo "Render Failed";
			http_response_code(500);
			exit;
		}


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

		$path 		= e_IMAGE;
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

		$fontFile = isset($secureimg['font']) ? realpath($fontpath.$secureimg['font']) : false;

		if(!empty($fontFile) && !is_readable($fontFile))
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
		

		if(!empty($fontFile))
		{
			imagettftext($image, $secureimg['size'],$secureimg['angle'], $secureimg['x'], $secureimg['y'], $text_color, $fontFile, $code);
		}
		else
		{
			imagestring ($image, 5, 12, 2, $code, $text_color);
		}

		imagesavealpha($image, true);

		switch($type)
		{
			case "jpeg":
				imagejpeg($image, null, 60 );
				break;
			case "png":
				imagepng($image, null, 9);
				break;
			case "gif":
				imagegif($image);
				break;
		}


	}


	/**
	 * @param $x
	 * @param $y
	 * @return false|GdImage|resource
	 */
	private function imageCreateTransparent($x, $y)
	{
    	$imageOut = imagecreatetruecolor($x, $y);
    	$backgroundColor = imagecolorallocatealpha($imageOut, 0, 0, 0, 127);
    	imagefill($imageOut, 0, 0, $backgroundColor);
    	return $imageOut;
	}
	




}

