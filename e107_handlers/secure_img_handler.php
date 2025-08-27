<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2025 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if(!defined('e107_INIT'))
{
	exit;
}


class secure_image
{

	// Preference keys for CAPTCHA settings
	const PREF_CAPTCHA_TTL = 'captcha_ttl';
	const PREF_CAPTCHA_VERIFY_IP = 'captcha_verify_ip';

	/**
	 * @var string Since v2.4.0, a self-contained encrypted token that only the e107 server can decrypt
	 * @deprecated v2.4.0 Property made private. Use magic getter/setter for backward compatibility.
	 *             This property holds the JWT token that contains the encrypted CAPTCHA solution.
	 *             Legacy code expecting this property will still work via __get() magic method.
	 *             Use {@see secure_image::getToken()} instead.
	 */
	private $random_number = null;

	protected $HANDLERS_DIRECTORY;
	protected $IMAGES_DIRECTORY;
	protected $FONTS_DIRECTORY;
	protected $BASE_DIR;
	public $FONT_COLOR = "90,90,90";

	/** @var null|e_jwt */
	private $jwtHandler = null;
	/** @var null|string The JWT token containing the CAPTCHA solution */
	private $jwtToken = null;

	function __construct()
	{
		$this->BASE_DIR           = e_BASE;
		$CORE_DIRECTORY           = e107::getFolder('CORE');
		$this->HANDLERS_DIRECTORY = e107::getFolder('HANDLERS');
		$this->FONTS_DIRECTORY    = !empty($CORE_DIRECTORY) ? $CORE_DIRECTORY . "fonts/" : "e107_core/fonts/";
		$this->IMAGES_DIRECTORY   = e107::getFolder('IMAGES');
	}

	/**
	 * Magic getter to provide backward compatibility for $random_number property
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if($name === 'random_number')
		{
			trigger_error('The random_number property is deprecated. Use ' . __CLASS__ . '::getToken() instead.', E_USER_DEPRECATED);
			return $this->getToken();
		}

		// Trigger normal PHP error for undefined properties
		trigger_error('Undefined property: ' . __CLASS__ . '::$' . $name, E_USER_NOTICE);

		return null;
	}

	/**
	 * Magic setter to provide backward compatibility for $random_number property
	 * @param string $name
	 * @param mixed  $value
	 */
	public function __set($name, $value)
	{
		if($name === 'random_number')
		{
			trigger_error('Setting random_number leads to undefined behavior. Do not use this property.', E_USER_DEPRECATED);
			$this->jwtToken = $value;

			return;
		}

		// For other properties, set them dynamically (PHP <8.2 default behavior)
		$this->$name = $value;
	}

	/**
	 * Magic isset to provide backward compatibility for $random_number property
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		if($name === 'random_number')
		{
			return isset($this->jwtToken);
		}

		return isset($this->$name);
	}

	/**
	 * @return string
	 * @deprecated v2.3.1 Use {@see createCode()} instead.
	 *             Legacy spelling of {@see createCode()};
	 */
	public function create_code()
	{
		return $this->createCode();
	}


	/**
	 * Generates a public code and a secret code. Returns the public code.
	 * @return string
	 */
	public function createCode()
	{
		if($user_func = e107::getOverride()->check($this, 'create_code'))
		{
			return call_user_func($user_func);
		}

		// Generate a random secret for the CAPTCHA solution
		$secret = e107::getUserSession()->generateRandomString('*****');

		// Create JWT token containing the CAPTCHA solution
		$payload = [
			'solution' => $secret,
			'ip'       => e107::getIPHandler()->getIP(false)
		];

		// CAPTCHA tokens expire after 10 minutes by default
		$ttl            = $this->getCaptchaPref(self::PREF_CAPTCHA_TTL, 600);
		$this->jwtToken = $this->getJWTHandler()->encode($payload, $ttl);

		// For backward compatibility, $random_number now contains the JWT token
		// Legacy code expecting this property will get the JWT token instead
		return $this->random_number = $this->jwtToken;
	}


	/**
	 * Get the JWT handler, initializing it if necessary
	 * @return e_jwt
	 */
	private function getJWTHandler()
	{
		if($this->jwtHandler === null)
		{
			$this->jwtHandler = e107::getJWT();
		}

		return $this->jwtHandler;
	}


	/**
	 * Get a CAPTCHA-related preference
	 * @param string $key     Preference key (use class constants)
	 * @param mixed  $default Default value if not set
	 * @return mixed
	 */
	private function getCaptchaPref($key, $default = null)
	{
		return e107::getPref($key, $default);
	}

	/**
	 * The secret code that should be entered by the user.
	 * @return string|null
	 */
	public function getSecret()
	{
		// Ensure code is generated before returning secret
		$token = $this->getToken();

		// Extract the secret from the JWT token
		$decoded = $this->getJWTHandler()->decode($token);
		if($decoded !== false && isset($decoded['solution']))
		{
			return $decoded['solution'];
		}

		return null;
	}


	/**
	 * Return true if code is valid, otherwise return FALSE
	 *
	 * @deprecated v2.3.1 Use {@see invalidCode()} instead. Returns true when the code doesn't match.
	 * @param string $recnum   The public code - returned by {@see create_code()}
	 * @param string $checkstr - code entered by the user.
	 * @return bool|mixed
	 */
	public function verify_code($recnum, $checkstr)
	{
		if($user_func = e107::getOverride()->check($this, 'verify_code'))
		{
			return call_user_func($user_func, $recnum, $checkstr);
		}

		return $this->verifyJWT($recnum, $checkstr);
	}

	/**
	 * Verify a JWT-based CAPTCHA
	 *
	 * @param string $token     The JWT token
	 * @param string $userInput The user's input
	 * @return bool {@see TRUE} if the token matches the input, {@see FALSE} otherwise
	 */
	private function verifyJWT($token, $userInput)
	{
		$data = $this->getJWTHandler()->decode($token);

		if($data === false)
		{
			e107::getDebug()->log('CAPTCHA verification failed: Invalid JWT token');

			return false;
		}

		// Verify IP address matches (optional security feature)
		$verifyIP = $this->getCaptchaPref(self::PREF_CAPTCHA_VERIFY_IP, false);
		if($verifyIP && isset($data['ip']))
		{
			$currentIP = e107::getIPHandler()->getIP(false);
			if($data['ip'] !== $currentIP)
			{
				e107::getDebug()->log('CAPTCHA verification failed: IP mismatch');

				return false;
			}
		}

		// Compare solution (case-sensitive)
		$solution = $data['solution'] ?? '';

		return ($solution === $userInput);
	}


	/**
	 * Returns an Error message (true) if check fails, otherwise return false.
	 * @param $rec_num
	 * @param $checkstr
	 * @return bool|string
	 */
	function invalidCode($rec_num = null, $checkstr = null)
	{
		if($user_func = e107::getOverride()->check($this, 'invalidCode'))
		{
			return call_user_func($user_func, $rec_num, $checkstr);
		}

		if($this->verify_code($rec_num, $checkstr))
		{
			return false;
		}
		else
		{
			return LAN_INVALID_CODE;
		}

	}


	/**
	 * @return string
	 * @deprecated Use renderImage() instead.
	 */
	public function r_image()
	{
		if($user_func = e107::getOverride()->check($this, 'r_image'))
		{
			return call_user_func($user_func);
		}

		if(defined('e_CAPTCHA_FONTCOLOR'))
		{
			$color = str_replace("#", "", e_CAPTCHA_FONTCOLOR);
		}
		else
		{
			$color = 'cccccc';
		}

		$token        = $this->getToken();
		$encodedToken = urlencode($token);

		return "<img src='" . e_IMAGE_ABS . "secimg.php?id={$encodedToken}&amp;clr={$color}' class='icon secure-image' alt='Missing Code' style='max-width:100%' />";
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

		if(!preg_match('/^[a-f0-9]{3}(?:[a-f0-9]{3})?$/i', $hex))
		{
			return '90,90,90'; // Return default on invalid hex
		}

		if(strlen($hex) == 3)
		{
			$r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
			$g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
			$b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
		}
		else
		{
			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));
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
		if($user_func = e107::getOverride()->check($this, 'renderInput'))
		{
			return call_user_func($user_func);
		}

		$frm = e107::getForm();

		return $frm->hidden("rand_num", $this->getToken()) . $frm->text("code_verify", "", 20, array("size" => 20, 'required' => 1, 'placeholder' => LAN_ENTER_CODE, 'autocomplete' => 'off'));
	}


	/**
	 * Return the label to accompany the input.
	 * @return mixed|string
	 */
	function renderLabel()
	{
		if($user_func = e107::getOverride()->check($this, 'renderLabel'))
		{
			return call_user_func($user_func);
		}

		return LAN_ENTER_CODE;
	}


	/**
	 * Get the CAPTCHA token, generating it if not already generated (lazy getter)
	 *
	 * @return string CAPTCHA token
	 */
	public function getToken()
	{
		// Use jwtToken as the indicator - if it's null, code hasn't been generated
		if($this->jwtToken === null)
		{
			return $this->createCode();
		}

		return $this->jwtToken;
	}

	/**
	 * Render the generated Image. Called without class2 environment (standalone).
	 * @param string $qcode
	 * @param string $color
	 * @return mixed
	 */
	function render($qcode, $color = '')
	{
		if($color)
		{
			$this->FONT_COLOR = $this->hex2rgb($color);
		}

		//	echo "COLOR: ".$this->FONT_COLOR;
		$over = e107::getOverride();

		if($user_func = $over->check($this, 'render'))
		{

			return call_user_func($user_func, $qcode);
		}


		// Decode JWT token to get the CAPTCHA solution
		$data = $this->getJWTHandler()->decode($qcode);
		if($data === false || !isset($data['solution']))
		{
			e107::getLog()->add('SECIMG', 'Invalid or expired CAPTCHA token', E_LOG_WARNING);
			header('HTTP/1.1 400 Bad Request');
			echo "Invalid or Expired Token";
			exit;
		}

		$code = $data['solution'];

		$imgtypes = array('png' => "png", 'gif' => "gif", 'jpg' => "jpeg",);


		$type = "none";

		foreach($imgtypes as $k => $t)
		{
			if(function_exists("imagecreatefrom" . $t))
			{
				$ext  = "." . $k;
				$type = $t;
				break;
			}
		}

		$path      = e_IMAGE;
		$fontpath  = $this->BASE_DIR . $this->IMAGES_DIRECTORY;
		$secureimg = array();

		if(is_readable($path . "secure_image_custom.php"))
		{

			require_once($path . "secure_image_custom.php");
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

			if(isset($secureimg['font']) && !is_readable($path . $secureimg['font']))
			{
				echo "Font missing"; // for debug only. translation not necessary.
				exit;
			}

			if(!is_readable($path . $secureimg['image'] . $ext))
			{
				echo "Missing Background-Image: " . $secureimg['image'] . $ext; // for debug only. translation not necessary.
				exit;
			}

		}
		else
		{
			$fontpath           = $this->BASE_DIR . $this->FONTS_DIRECTORY;
			$secureimg['image'] = "generic/code_bg";
			$secureimg['angle'] = "0";
			$secureimg['color'] = $this->FONT_COLOR; // red,green,blue
			$secureimg['x']     = "1";
			$secureimg['y']     = "21";

			$num = rand(1, 3);

			switch($num)
			{
				case 1:
					$secureimg['font'] = "chaostimes.ttf";
					$secureimg['size'] = "19";
				break;

				case 2:
					$secureimg['font'] = "crazy_style.ttf";
					$secureimg['size'] = "18";
				break;

				case 3:
					$secureimg['font'] = "puchakhonmagnifier3.ttf";
					$secureimg['size'] = "19";
				break;
			}


		}

		$fontFile = isset($secureimg['font']) ? realpath($fontpath . $secureimg['font']) : false;

		if(!empty($fontFile) && !is_readable($fontFile))
		{
			echo "Font missing"; // for debug only. translation not necessary.
			exit;
		}


		if(isset($secureimg['image']) && !is_readable($path . $secureimg['image'] . $ext))
		{
			echo "Missing Background-Image: " . $secureimg['image'] . $ext; // for debug only. translation not necessary.
			exit;
		}

		$bg_file = $secureimg['image'];

		switch($type)
		{
			case "png": // preferred 
				$image = imagecreatefrompng($path . $bg_file . ".png");
				imagealphablending($image, true);
			break;

			case "gif":
				$image = imagecreatefromgif($path . $bg_file . ".gif");
				imagealphablending($image, true);
			break;

			case "jpeg":
				$image = imagecreatefromjpeg($path . $bg_file . ".jpg");
			break;
		}


		// removing the black from the placeholder
		$image = $this->imageCreateTransparent(100, 35); //imagecreatetruecolor(100, 35);


		if(isset($secureimg['color']))
		{
			$tmp        = explode(",", $secureimg['color']);
			$text_color = imagecolorallocate($image, $tmp[0], $tmp[1], $tmp[2]);

		}
		else
		{
			$text_color = imagecolorallocate($image, 90, 90, 90);
		}

		header("Content-type: image/{$type}");


		if(!empty($fontFile))
		{
			imagettftext($image, $secureimg['size'], $secureimg['angle'], $secureimg['x'], $secureimg['y'], $text_color, $fontFile, $code);
		}
		else
		{
			imagestring($image, 5, 12, 2, $code, $text_color);
		}

		imagesavealpha($image, true);

		switch($type)
		{
			case "jpeg":
				imagejpeg($image, null, 60);
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
		$imageOut        = imagecreatetruecolor($x, $y);
		$backgroundColor = imagecolorallocatealpha($imageOut, 0, 0, 0, 127);
		imagefill($imageOut, 0, 0, $backgroundColor);

		return $imageOut;
	}


}

