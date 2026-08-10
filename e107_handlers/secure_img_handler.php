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


/**
 * The CAPTCHA challenge, carried entirely by the visitor.
 *
 * The answer is sealed into the token the form hands out and is never written
 * down here, because web spiders fetch CAPTCHA images by the thousand and
 * submit nothing: a design that stored a row per rendered challenge stored
 * millions of rows nobody ever read. Only a verification writes anything, and
 * all it writes is a marker saying this one challenge has now been used up.
 *
 * {@see \e107\Security\SealedToken} encrypts, where its predecessor merely
 * signed, so the answer is no longer in the page source.
 */
class secure_image
{

	// Preference keys for CAPTCHA settings
	const PREF_CAPTCHA_TTL = 'captcha_ttl';
	const PREF_CAPTCHA_VERIFY_IP = 'captcha_verify_ip';

	/**
	 * Sealed token purpose every CAPTCHA is issued under.
	 *
	 * One purpose serves every form on purpose. e107_images/secimg.php is a
	 * separate request whose only input is the token in the query string, so it
	 * cannot know which form the token came from and could not choose a
	 * per-form key. The form travels as a claim inside the sealed payload
	 * instead and is compared during verification, which gives the same
	 * "solved here, worthless there" property without teaching the image
	 * endpoint about forms.
	 */
	const TOKEN_PURPOSE = 'captcha';

	/**
	 * Seconds a challenge stays answerable when the site names no preference.
	 *
	 * This matches what the handler did before it issued tokens at all. The
	 * answer then lived in the session under secret/<code> with no expiry of
	 * its own, evicted only by a cap of six outstanding challenges, so it
	 * lasted as long as the session did: session_lifetime, which
	 * default_install.xml seeds at 86400 and session_handler.php reads with
	 * the same default.
	 *
	 * A day is only safe because a challenge is now spent by the first attempt
	 * to answer it. The old code cleared the session entry on a correct answer
	 * and did nothing on a wrong one, so a challenge could be guessed at for
	 * the whole session lifetime; this one cannot be guessed at twice.
	 *
	 * @see secure_image::sessionLifetime() which is preferred over this
	 * @see secure_image::verify_code() for the spend
	 */
	const DEFAULT_TTL = 86400;

	/**
	 * Directory, under the content cache, holding the spent markers.
	 *
	 * Its own directory rather than an {@see ecache} entry, because the marker
	 * has to be created atomically and ecache offers no primitive that answers
	 * "did this already exist" from the write itself. A separate directory also
	 * keeps the markers out of the way of an admin cache purge, which must not
	 * un-spend a challenge, and makes them sweepable as a set.
	 */
	const SPENT_DIRECTORY = 'captcha-spent/';

	/**
	 * Suffix identifying a marker, so the sweep leaves everything else alone.
	 */
	const SPENT_SUFFIX = '.spent';

	/**
	 * Name of the file whose modification time paces the sweep.
	 */
	const SWEEP_STAMP = 'last-sweep';

	/**
	 * Seconds between sweeps of the marker directory.
	 */
	const SWEEP_INTERVAL = 60;

	/**
	 * Hexadecimal characters {@see \e107\Security\SealedToken::seal()} gives a
	 * token identifier, checked before one becomes part of a filename.
	 */
	const JTI_LENGTH = 32;

	/**
	 * The core forms that issue a CAPTCHA.
	 *
	 * A challenge naming one of these is refused by every other, so an answer
	 * solved on the contact form cannot be spent on the signup form. The names
	 * are constants rather than literals because the form that renders the
	 * challenge and the code that verifies it are usually in different files,
	 * and a typo between them would be a CAPTCHA that can never be passed.
	 *
	 * The guarantee holds only where both sides name a form. A theme or plugin
	 * that renders its own CAPTCHA markup and names nothing issues a challenge
	 * every form accepts, which is what every caller did before per-form
	 * challenges existed and is kept working deliberately. Core names every
	 * form it renders and every form it verifies. See
	 * {@see secure_image::formHolds()}.
	 *
	 * FORM_LOGIN covers the public login box and e107_admin/auth.php alike.
	 * Splitting them would buy nothing: the admin login form is served to any
	 * anonymous visitor who asks for it, so a challenge scoped to it is exactly
	 * as easy to obtain as one scoped to the public box. It would also fail
	 * closed the moment the two sides disagreed, and they are chosen in
	 * different places from different conditions.
	 */
	const FORM_CONTACT = 'contact';
	const FORM_EMAILFRIEND = 'emailfriend';
	const FORM_FPW = 'fpw';
	const FORM_LOGIN = 'login';
	const FORM_SIGNUP = 'signup';

	/**
	 * @var string Since v2.4.0, a self-contained encrypted token that only this site can open
	 * @deprecated v2.4.0 Property made private. Use magic getter/setter for backward compatibility.
	 *             This property holds the sealed token that carries the CAPTCHA solution.
	 *             Legacy code expecting this property will still work via __get() magic method.
	 *             Use {@see secure_image::getToken()} instead.
	 */
	private $random_number = null;

	protected $HANDLERS_DIRECTORY;
	protected $IMAGES_DIRECTORY;
	protected $FONTS_DIRECTORY;
	protected $BASE_DIR;
	public $FONT_COLOR = "90,90,90";

	/**
	 * One issued challenge per form name, so that a page carrying two CAPTCHA
	 * forms does not hand the second one the first one's token, and so that the
	 * image and the input of the same form always agree.
	 *
	 * @var array form name => sealed token
	 */
	private $tokens = array();

	/**
	 * Whether this request has already told the operator that single use is off.
	 *
	 * @var bool
	 */
	private static $unenforceableReported = false;

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
			$this->tokens[''] = $value;

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
			return isset($this->tokens['']);
		}

		return isset($this->$name);
	}

	/**
	 * @param string|null $form one of the FORM_ constants, or null
	 * @return string
	 * @deprecated v2.3.1 Use {@see createCode()} instead.
	 *             Legacy spelling of {@see createCode()};
	 */
	public function create_code($form = null)
	{
		return $this->createCode($form);
	}


	/**
	 * Issue a fresh challenge and return the token that carries it.
	 *
	 * The answer, the address it was issued to, the form it belongs to and the
	 * client it was handed to all travel inside the token, encrypted. Nothing
	 * is written anywhere.
	 *
	 * @param string|null $form one of the FORM_ constants. A challenge issued
	 *                    for a named form is refused by every other form. Naming
	 *                    nothing issues an unscoped challenge, which is what
	 *                    every caller did before per-form challenges existed and
	 *                    is still accepted anywhere
	 * @return string the token, or an empty string when this site has no key to
	 *                seal it with, in which case no answer can be accepted
	 *                either
	 */
	public function createCode($form = null)
	{
		if($user_func = e107::getOverride()->check($this, 'create_code'))
		{
			return call_user_func($user_func, $form);
		}

		$name = $this->formName($form);

		$claims = array(
			'solution' => e107::getUserSession()->generateRandomString('*****'),
			'ip'       => e107::getIPHandler()->getIP(false),
			'form'     => $name,
		);

		// A preference of zero or less would seal a challenge that has already
		// expired, so every CAPTCHA on the site would be refused.
		$fallback = $this->sessionLifetime();
		$ttl      = (int) $this->getCaptchaPref(self::PREF_CAPTCHA_TTL, $fallback);
		$ttl      = ($ttl > 0) ? $ttl : $fallback;

		$token = e107::getSealedToken(self::TOKEN_PURPOSE)->seal($claims, $ttl);

		$this->tokens[$name] = is_string($token) ? $token : '';

		return $this->tokens[$name];
	}


	/**
	 * Normalise a caller's form name. An empty name means "unscoped".
	 *
	 * @param string|null $form
	 * @return string
	 */
	private function formName($form)
	{
		return is_string($form) ? trim($form) : '';
	}


	/**
	 * How long a challenge lasted before this handler issued tokens.
	 *
	 * The answer sat in the session and expired with it, so a site that
	 * shortens its sessions shortens its challenges too, and one that names no
	 * CAPTCHA preference gets the behaviour it had.
	 *
	 * @see secure_image::DEFAULT_TTL
	 * @return int seconds, always greater than zero
	 */
	private function sessionLifetime()
	{
		$lifetime = (int) e107::getPref('session_lifetime', self::DEFAULT_TTL);

		return ($lifetime > 0) ? $lifetime : self::DEFAULT_TTL;
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
	 * The answer the visitor is expected to type.
	 *
	 * This is an open, not a verification: it spends nothing and enforces
	 * nothing, because it reads a challenge that is about to be shown rather
	 * than one that has been answered.
	 *
	 * @param string|null $form the form whose challenge to read
	 * @return string|null
	 */
	public function getSecret($form = null)
	{
		$data = e107::getSealedToken(self::TOKEN_PURPOSE)->open($this->getToken($form));

		if(is_array($data) && isset($data['solution']))
		{
			return $data['solution'];
		}

		return null;
	}


	/**
	 * Spend the challenge and say whether the answer was right.
	 *
	 * THIS SPENDS THE CHALLENGE. Since v2.4.0 one image buys one attempt: the
	 * token is used up here whether the answer was right, wrong or for another
	 * form entirely, so that a visitor cannot sit on a single image and try the
	 * five characters until they land. A caller that answers a failed
	 * submission by re-serving the token it was just given is therefore now
	 * serving a token that can never succeed, and must issue a fresh challenge
	 * instead. Every core form does.
	 *
	 * @deprecated v2.3.1 Use {@see invalidCode()} instead. Returns true when the code doesn't match.
	 * @param string $recnum   The public code - returned by {@see create_code()}
	 * @param string $checkstr - code entered by the user.
	 * @param string|null $form the form this submission arrived at, one of the
	 *                    FORM_ constants. A challenge issued for another named
	 *                    form is refused
	 * @return bool|mixed
	 */
	public function verify_code($recnum, $checkstr, $form = null)
	{
		if($user_func = e107::getOverride()->check($this, 'verify_code'))
		{
			return call_user_func($user_func, $recnum, $checkstr, $form);
		}

		return $this->verifySealed($recnum, $checkstr, $form);
	}

	/**
	 * Open a submitted challenge, spend it, and check the answer.
	 *
	 * The order is deliberate. The challenge is spent as soon as it is known to
	 * be one this site issued, before the form, the client and the address are
	 * looked at, so that every route out of this method has cost the visitor
	 * their one attempt. Spending after those checks would leave a visitor free
	 * to keep guessing simply by submitting from an address the site was told
	 * to check.
	 *
	 * No rejection says which check failed. The caller is holding a string an
	 * anonymous visitor sent.
	 *
	 * @param string $token     the sealed challenge
	 * @param string $userInput the user's answer
	 * @param string|null $form the form this submission arrived at
	 * @return bool
	 */
	private function verifySealed($token, $userInput, $form)
	{
		$data = e107::getSealedToken(self::TOKEN_PURPOSE)->open($token);

		if(!is_array($data))
		{
			e107::getDebug()->log('CAPTCHA verification failed: the challenge was not issued by this site, or it has expired');

			return false;
		}

		if(!$this->spend($data))
		{
			e107::getDebug()->log('CAPTCHA verification failed: the challenge has already been answered once');

			return false;
		}

		if(!$this->formHolds($data, $form))
		{
			e107::getDebug()->log('CAPTCHA verification failed: the challenge belongs to another form');

			return false;
		}

		if($this->getCaptchaPref(self::PREF_CAPTCHA_VERIFY_IP, true) && isset($data['ip'])
			&& $data['ip'] !== e107::getIPHandler()->getIP(false))
		{
			e107::getDebug()->log('CAPTCHA verification failed: IP mismatch');

			return false;
		}

		$solution = isset($data['solution']) ? $data['solution'] : '';

		return hash_equals((string) $solution, (string) $userInput);
	}


	/**
	 * Use up a challenge, and say whether it had any use left.
	 *
	 * The marker is written during verification only, never while an image is
	 * being rendered, because spiders fetch CAPTCHA images constantly and
	 * submit nothing: they must cost the site no storage at all.
	 *
	 * The answer to "had this one been spent" comes from the act of creating
	 * the marker and never from a preceding read. fopen() in mode 'x' is
	 * O_CREAT|O_EXCL, so exactly one of any number of simultaneous
	 * verifications creates the file and every other one is told the file was
	 * already there. Asking first and writing second would let a solver farm
	 * answer one image from as many requests as it cared to fire at once, which
	 * is the only way an abuser ever submits.
	 *
	 * A site whose cache directory cannot be written keeps working without
	 * single use, and says so where an operator can see it. Refusing every
	 * submission instead would turn a misconfigured directory into a site
	 * nobody can contact, register with or recover a password on.
	 *
	 * @param array $data the opened claims
	 * @return bool false when this challenge had already been spent
	 */
	private function spend(array $data)
	{
		$marker = $this->spentMarker($data);

		if($marker === false)
		{
			return false;
		}

		$directory = $this->spentDirectory();

		if(!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory))
		{
			$this->reportUnenforceable('the directory ' . $directory . ' could not be created');

			return true;
		}

		$this->sweep($directory);

		$handle = @fopen($directory . $marker, 'xb');

		if($handle !== false)
		{
			fclose($handle);

			return true;
		}

		if(file_exists($directory . $marker))
		{
			return false;
		}

		$this->reportUnenforceable('the marker ' . $marker . ' could not be written to ' . $directory);

		return true;
	}


	/**
	 * Where the spent markers live.
	 *
	 * @return string with a trailing separator
	 */
	private function spentDirectory()
	{
		return e_CACHE_CONTENT . self::SPENT_DIRECTORY;
	}


	/**
	 * The marker naming one challenge, or false when the claims cannot name one.
	 *
	 * The expiry leads the name so that {@see secure_image::sweep()} can decide
	 * whether a marker is still needed by reading the name alone. It is the
	 * token's own expiry, so a marker dies at the moment the challenge it
	 * records stops being openable and no replay window is left behind.
	 *
	 * @param array $data the opened claims
	 * @return string|false
	 */
	private function spentMarker(array $data)
	{
		if(!isset($data['jti'], $data['exp']) || !is_string($data['jti'])
			|| strlen($data['jti']) !== self::JTI_LENGTH || !ctype_xdigit($data['jti']))
		{
			return false;
		}

		return sprintf('%010d', (int) $data['exp']) . '-' . $data['jti'] . self::SPENT_SUFFIX;
	}


	/**
	 * Delete the markers whose challenges have expired.
	 *
	 * Without this, every submission carrying a genuine token and a wrong
	 * answer would leave a file behind for good, and an anonymous visitor could
	 * spend a site's inodes two requests at a time. The stamp file paces the
	 * work so that a busy site sweeps once a minute rather than once a
	 * submission; two sweeps racing each other only means two processes
	 * unlinking the same dead markers.
	 *
	 * @param string $directory
	 * @return void
	 */
	private function sweep($directory)
	{
		$now  = time();
		$last = @filemtime($directory . self::SWEEP_STAMP);

		if($last !== false && $now - $last < self::SWEEP_INTERVAL)
		{
			return;
		}

		if(@touch($directory . self::SWEEP_STAMP) === false)
		{
			return;
		}

		$handle = @opendir($directory);

		if($handle === false)
		{
			return;
		}

		while(($entry = readdir($handle)) !== false)
		{
			if(substr($entry, -strlen(self::SPENT_SUFFIX)) !== self::SPENT_SUFFIX)
			{
				continue;
			}

			if((int) substr($entry, 0, 10) >= $now)
			{
				continue;
			}

			@unlink($directory . $entry);
		}

		closedir($handle);
	}


	/**
	 * Say, once per request and where an operator will find it, that this site
	 * is not enforcing single use.
	 *
	 * The debug log alone was not enough: it is discarded unless somebody has
	 * debugging switched on, so a site could run for years with its CAPTCHA
	 * quietly reduced to unlimited guesses and nobody would ever be told.
	 *
	 * @param string $reason
	 * @return void
	 */
	private function reportUnenforceable($reason)
	{
		e107::getDebug()->log('CAPTCHA single use is not being enforced: ' . $reason);

		if(self::$unenforceableReported)
		{
			return;
		}

		self::$unenforceableReported = true;

		e107::getLog()->add('SECIMG', 'CAPTCHA single use is not being enforced, so one image buys unlimited attempts: ' . $reason, E_LOG_WARNING);
	}


	/**
	 * Whether a challenge may be answered at the form it arrived at.
	 *
	 * An unnamed challenge answers anywhere and a form that names nothing
	 * accepts anything, which is what every caller did before per-form
	 * challenges existed. Core names every form it renders and every form it
	 * verifies, so core challenges are all scoped; the allowance exists for a
	 * theme or plugin that renders its own CAPTCHA markup and would otherwise
	 * have a login box nobody can get through.
	 *
	 * @param array $data the opened claims
	 * @param string|null $form the form this submission arrived at
	 * @return bool
	 */
	private function formHolds(array $data, $form)
	{
		$issued   = isset($data['form']) ? (string) $data['form'] : '';
		$expected = $this->formName($form);

		if($issued === '' || $expected === '')
		{
			return true;
		}

		return hash_equals($issued, $expected);
	}



	/**
	 * Spend the challenge and return an error message when the answer was wrong.
	 *
	 * THIS SPENDS THE CHALLENGE, see {@see secure_image::verify_code()}. A form
	 * that fails validation for any reason must render a new challenge rather
	 * than repeat the one it was posted, or every failed submission becomes a
	 * submission that can never succeed.
	 *
	 * @param string|null $rec_num the token the form carried
	 * @param string|null $checkstr the answer the visitor typed
	 * @param string|null $form the form this submission arrived at
	 * @return bool|string false when the answer was right
	 */
	function invalidCode($rec_num = null, $checkstr = null, $form = null)
	{
		if($user_func = e107::getOverride()->check($this, 'invalidCode'))
		{
			return call_user_func($user_func, $rec_num, $checkstr, $form);
		}

		if($this->verify_code($rec_num, $checkstr, $form))
		{
			return false;
		}
		else
		{
			return LAN_INVALID_CODE;
		}

	}


	/**
	 * @param string|null $form the form the image belongs to
	 * @return string
	 * @deprecated Use renderImage() instead.
	 */
	public function r_image($form = null)
	{
		if($user_func = e107::getOverride()->check($this, 'r_image'))
		{
			return call_user_func($user_func, $form);
		}

		if(defined('e_CAPTCHA_FONTCOLOR'))
		{
			$color = str_replace("#", "", e_CAPTCHA_FONTCOLOR);
		}
		else
		{
			$color = 'cccccc';
		}

		$token        = $this->getToken($form);
		$encodedToken = urlencode($token);

		return "<img src='" . e_IMAGE_ABS . "secimg.php?id={$encodedToken}&amp;clr={$color}' class='icon secure-image' alt='Missing Code' style='max-width:100%' />";
	}


	/**
	 * Return the rendered code/image.
	 * @param string|null $form the form the image belongs to, one of the FORM_ constants
	 * @return string
	 */
	public function renderImage($form = null) // Alias of r_image
	{
		return $this->r_image($form);
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
	 * @param string|null $form the form the input belongs to, one of the FORM_ constants
	 * @return string
	 */
	function renderInput($form = null)
	{
		if($user_func = e107::getOverride()->check($this, 'renderInput'))
		{
			return call_user_func($user_func, $form);
		}

		$frm = e107::getForm();

		return $frm->hidden("rand_num", $this->getToken($form)) . $frm->text("code_verify", "", 20, array("size" => 20, 'required' => 1, 'placeholder' => LAN_ENTER_CODE, 'autocomplete' => 'off'));
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
	 * The challenge this form is currently offering, issuing one if it has none.
	 *
	 * A verification never stores anything here, so a form that re-renders after
	 * a failed submission gets a new challenge rather than the spent one it was
	 * posted. Nothing in this class may be changed to remember a submitted
	 * token: doing so would turn every failed submission into a form that can
	 * never be completed.
	 *
	 * @param string|null $form the form asking, one of the FORM_ constants
	 * @return string CAPTCHA token
	 */
	public function getToken($form = null)
	{
		$name = $this->formName($form);

		if(!array_key_exists($name, $this->tokens))
		{
			return $this->createCode($form);
		}

		return $this->tokens[$name];
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


		// Drawing the challenge is not answering it: nothing is spent here, and
		// the form the token belongs to is nothing this endpoint can know.
		$data = e107::getSealedToken(self::TOKEN_PURPOSE)->open($qcode);

		if(!is_array($data) || !isset($data['solution']))
		{
			// The debug log rather than the admin log. This endpoint takes one
			// input from anyone who asks, and a challenge now expires in two
			// minutes rather than ten, so a browser revalidating an image it
			// cached is the ordinary cause of arriving here. A row per
			// occurrence made an anonymous visitor's GET into a way of filling
			// the administrator's log.
			e107::getDebug()->log('Invalid or expired CAPTCHA token at the image endpoint');
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

