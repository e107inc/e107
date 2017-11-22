<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * User Model
 *
 * $URL$
 * $Id$
 */

/**
 * @package e107
 * @subpackage	e107_handlers
 * @version $Id$
 * @author SecretR
 *
 * Front-end User Models
 */

if (!defined('e107_INIT'))
{
	exit;
}

class e_user_model extends e_admin_model
{
	/**
	 * Describes all model data, used as _FIELD_TYPE array as well
	 * @var array
	 */
	protected $_data_fields = array(
		'user_id'			 => 'integer',
		'user_name'			 => 'string',
		'user_loginname'	 => 'string',
		'user_customtitle'	 => 'string',
		'user_password'		 => 'string',
		'user_sess'			 => 'string',
		'user_email'		 => 'string',
		'user_signature'	 => 'string',
		'user_image'		 => 'string',
		'user_hideemail'	 => 'integer',
		'user_join'			 => 'integer',
		'user_lastvisit'	 => 'integer',
		'user_currentvisit'	 => 'integer',
		'user_lastpost'		 => 'integer',
		'user_chats'		 => 'integer',
		'user_comments'		 => 'integer',
		'user_ip'			 => 'string',
		'user_ban'			 => 'integer',
		'user_prefs'		 => 'string',
		'user_visits'		 => 'integer',
		'user_admin'		 => 'integer',
		'user_login'		 => 'string',
		'user_class'		 => 'string',
		'user_perms'		 => 'string',
		'user_realm'		 => 'string',
		'user_pwchange'		 => 'integer',
		'user_xup'			 => 'string',
	);

	/**
	 * Validate required fields
	 * @var array
	 */
	protected $_validation_rules = array(
		'user_name' => array('string', '1', 'LAN_USER_01', 'LAN_USER_HELP_01'), // TODO - regex
		'user_loginname' => array('string', '1', 'LAN_USER_02', 'LAN_USER_HELP_02'), // TODO - regex
		'user_password' => array('compare', '5', 'LAN_PASSWORD', 'LAN_USER_HELP_05'), // TODO - pref - modify it somewhere below - prepare_rules()?
		'user_email' => array('email', '', 'LAN_EMAIL', 'LAN_USER_HELP_08'),
	);

	/**
	 * Validate optional fields - work in progress, not working yet
	 * @var array
	 */
	protected $_optional_rules = array(
		'user_customtitle' => array('string', '1', 'LAN_USER_01'), // TODO - regex
	);

	/**
	 * @see e_model
	 * @var string
	 */
	protected $_db_table = 'user';

	/**
	 * @see e_model
	 * @var string
	 */
	protected $_field_id = 'user_id';

	/**
	 * @see e_model
	 * @var string
	 */
	protected $_message_stack = 'user';

	/**
	 * User class as set in user Adminsitration
	 *
	 * @var integer
	 */
	protected $_memberlist_access = null;

	/**
	 * Extended data
	 *
	 * @var e_user_extended_model
	 */
	protected $_extended_model = null;

	/**
	 * Extended structure
	 *
	 * @var e_user_extended_structure
	 */
	protected $_extended_structure = null;

	/**
	 * User preferences model
	 * @var e_user_pref
	 */
	protected $_user_config = null;

	/**
	 * User model of current editor
	 * @var e_user_model
	 */
	protected $_editor = null;
	
	protected $_class_list;

	/**
	 * Constructor
	 * @param array $data
	 * @return void
	 */
	public function __construct($data = array())
	{
		$this->_memberlist_access = e107::getPref('memberlist_access');
		parent::__construct($data);
	}

	/**
	 * Always return integer
	 *
	 * @see e107_handlers/e_model#getId()
	 */
	public function getId()
	{
		return (integer) parent::getId();
	}

	/**
	 * Try display name, fall back to login name when empty (shouldn't happen)
	 */
	final public function getName($anon = false)
	{
		if($this->isUser())
		{
			return ($this->get('user_name') ? $this->get('user_name') : $this->get('user_loginname'));
		}
		return $anon;
	}
	
	/**
	 * Display name getter. Use it as DB field name will be changed soon.
	 */
	final public function getDisplayName()
	{
		return $this->get('user_name');
	}
	
	/**
	 * Login name getter. Use it as DB field name will be changed soon.
	 */
	final public function getLoginName()
	{
		return $this->get('user_loginname');
	}

	/**
	 * Real name getter. Use it as DB field name will be changed soon.
	 * @param bool $strict if false, fall back to Display name when empty
	 * @return mixed
	 */
	final public function getRealName($strict = false)
	{
		if($strict) return $this->get('user_login');
		return ($this->get('user_login') ? $this->get('user_login') : $this->get('user_name'));
	}

	final public function getAdminId()
	{
		return ($this->isAdmin() ? $this->getId() : false);
	}

	final public function getAdminName()
	{
		return ($this->isAdmin() ? $this->get('user_name') : false);
	}

	final public function getAdminEmail()
	{
		return ($this->isAdmin() ? $this->get('user_email') : false);
	}

	final public function getAdminPwchange()
	{
		return ($this->isAdmin() ? $this->get('user_pwchange') : false);
	}

	final public function getAdminPerms()
	{
		return ($this->isAdmin() ? $this->get('user_perms') : false);
	}

	final public function getTimezone()
	{
		// If timezone is not set, we return an empty string in order to use the
		// default timezone is set for e107.
		return ($this->get('user_timezone') ? $this->get('user_timezone') : '');
	}

	/**
	 * DEPRECATED - will be removed or changed soon (see e_session)
	 * @return string
	 */
	public function getToken()
	{
		if(null === $this->get('user_token'))
		{
			//$this->set('user_token', md5($this->get('user_password').$this->get('user_lastvisit').$this->get('user_pwchange').$this->get('user_class')));
			$this->set('user_token', e107::getSession()->getFormToken(false));
		}
		return $this->get('user_token');
	}
	
	public static function randomKey()
	{
		return md5(uniqid(rand(), 1));
	}

	public function isCurrent()
	{
		return false;
	}

	final public function isAdmin()
	{
		return ($this->get('user_admin') ? true : false);
	}

	final public function isNewUser()
	{
		$new_user_period = e107::getPref('user_new_period', 0);

		if(empty($new_user_period))	{ return false; }

		return (($this->get('user_join') > strtotime($new_user_period." days ago")) ? true : false);
	}

	final public function isBot()
	{
		$userAgent = $_SERVER['HTTP_USER_AGENT'];

		if(empty($userAgent))
		{
			return false;
		}

		$botlist = array( "googlebot", "Bingbot", 'slurp', 'baidu', 'ichiro','nutch','yacy', "Teoma",
		"alexa", "froogle", "Gigabot", "inktomi",
		"looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory",
		"Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot",
		"crawler", "www.galaxy.com", "Scooter", "msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz",
		"Baiduspider", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot",
		"Mediapartners-Google", "Sogou web spider", "WebAlta Crawler","TweetmemeBot",
		"Butterfly","Twitturls","Me.dium","Twiceler");

		foreach($botlist as $bot)
		{
			if(stripos($userAgent, $bot) !== false){ return true; }
		}

		return false;
	}

	final public function isMainAdmin()
	{
		return $this->checkAdminPerms('0');
	}

	final public function isUser()
	{
		return ($this->getId() ? true : false);
	}

	final public function isGuest()
	{
		return ($this->getId() ? false : true);
	}

	final public function hasBan()
	{
		return ((integer)$this->get('user_ban') === 1 ? true : false);
	}

	final public function hasRestriction()
	{
		return ((integer)$this->get('user_ban') === 0 ? false : true);
	}

	public function hasEditor()
	{
		return (null !== $this->_editor);
	}

	final protected function _setClassList()
	{
		$this->_class_list = array();
		if ($this->isUser())
		{
			if ($this->get('user_class'))
			{
				// list of all 'inherited' user classes, convert elements to integer
				$this->_class_list = array_map('intval', e107::getUserClass()->get_all_user_classes($this->get('user_class'), true));
			}

			$this->_class_list[] = e_UC_MEMBER;

			if($this->isNewUser())
			{
				$this->_class_list[] = e_UC_NEWUSER;
			}

			if ($this->isAdmin())
			{
				$this->_class_list[] = e_UC_ADMIN;
			}

			if ($this->isMainAdmin())
			{
				$this->_class_list[] = e_UC_MAINADMIN;
			}
		}
		else
		{
			$this->_class_list[] = e_UC_GUEST;

			if($this->isBot())
			{
				$this->_class_list[] = e_UC_BOTS;
			}

		}

		$this->_class_list[] = e_UC_READONLY;
		$this->_class_list[] = e_UC_PUBLIC;

		// unique, rebuild indexes
		$this->_class_list = array_merge(array_unique($this->_class_list));
		return $this;
	}

	final public function getClassList($toString = false)
	{
		if (null === $this->_class_list)
		{
			$this->_setClassList();
		}
		return ($toString ? implode(',', $this->_class_list) : $this->_class_list);
	}

	final public function getClassRegex()
	{
		return '(^|,)('.str_replace(',', '|', $this->getClassList(true)).')(,|$)';
	}

	final public function checkClass($class, $allowMain = true)
	{
		// FIXME - replace check_class() here
		return (($allowMain && $this->isMainAdmin()) || check_class($class, $this->getClassList(), 0));
	}

	final public function checkAdminPerms($perm_str)
	{
		// FIXME - method to replace getperms()
		return ($this->isAdmin() && getperms($perm_str, $this->getAdminPerms()));
	}

	final public function checkEditorPerms($class = '')
	{
		if (!$this->hasEditor())
			return false;

		$editor = $this->getEditor();

		if ('' !== $class)
			return ($editor->isAdmin() && $editor->checkClass($class));

		return $editor->isAdmin();
	}

	/**
	 * Check passed value against current user token
	 * DEPRECATED - will be removed or changed soon (see e_core_session)
	 * @param string $token md5 sum of e.g. posted token
	 * @return boolean
	 */
	final public function checkToken($token)
	{
		$utoken = $this->getToken();
		return (null !== $utoken && $token === md5($utoken));
	}

	/**
	 * Bad but required (BC) method of retrieving all user data
	 * It's here to be used from get_user_data() core function.
	 * DON'T USE THEM BOTH unless you have VERY good reason to do it.
	 *
	 * @return array
	 */
	public function getUserData()
	{
		// revised - don't call extended object, no permission checks, just return joined user data
		$ret = $this->getData();
		// $ret = array_merge($this->getExtendedModel()->getExtendedData(), $this->getData());
		if ($ret['user_perms'] == '0.') $ret['user_perms'] = '0';
		$ret['user_baseclasslist'] = $ret['user_class'];
		$ret['user_class'] = $this->getClassList(true);
		return $ret;
	}

	/**
	 * Check if given field name is present in core user table structure
	 *
	 * @param string $field
	 * @param boolean $short
	 * @return boolean
	 */
	public function isCoreField($field, $short = true)
	{
		if($short) $field = 'user_'.$field;
		return isset($this->_data_fields[$field]);
	}

	/**
	 * Check if given field name is present in extended user table structure
	 *
	 * @param string $field
	 * @param boolean $short
	 * @return boolean
	 */
	public function isExtendedField($field, $short = true)
	{
		if($short) $field = 'user_'.$field;
		if($this->isCoreField($field, false))
		{
			return false;
		}
		return $this->getExtendedModel()->isField($field, false);
	}

	/**
	 * Get User value from core user table.
	 * This method doesn't perform any read permission cheks.
	 *
	 * @param string $field
	 * @param mixed $default
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @return mixed if field is not part of core user table returns null by default
	 */
	public function getCore($field, $default = null, $short = true)
	{
		if($short) $field = 'user_'.$field;
		if($this->isCoreField($field, false)) return $this->get($field, $default);
		return $default;
	}

	/**
	 * Set User value (core user field).
	 * This method doesn't perform any write permission cheks.
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @param boolean $strict if false no Applicable check will be made
	 * @return e_user_model
	 */
	public function setCore($field, $value, $short = true, $strict = false)
	{
		if($short) $field = 'user_'.$field;
		if($this->isCoreField($field, false)) $this->set($field, $value, $strict);
		return $this;
	}

	/**
	 * Get User extended value.
	 * This method doesn't perform any read permission cheks.
	 *
	 * @param string $field
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @param boolean $raw get raw DB values (no SQL query)
	 * @return mixed
	 */
	public function getExtended($field, $short = true, $raw = true)
	{
		return $this->getExtendedModel()->getSystem($field, $short, $raw);
	}

	/**
	 * Set User extended value.
	 * This method doesn't perform any write permission cheks.
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @param boolean $strict if false no Applicable check will be made
	 * @return e_user_model
	 */
	public function setExtended($field, $value, $short = true, $strict = false)
	{
		$this->getExtendedModel()->setSystem($field, $value, $short, $strict);
		return $this;
	}

	/**
	 * Get User extended value after checking read permissions against current Editor
	 *
	 * @param string $field
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @param boolean $raw get raw DB values (no SQL query)
	 * @return mixed
	 */
	public function getExtendedFront($field, $short = true, $raw = false)
	{
		return $this->getExtendedModel()->getValue($field, $short, $raw);
	}

	/**
	 * Set User extended value after checking write permissions against current Editor.
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @return e_user_model
	 */
	public function setExtendedFront($field, $value, $short = true)
	{
		$this->getExtendedModel()->setValue($field, $value, $short);
		return $this;
	}

	/**
	 * Transparent front-end getter. It performs all required read/applicable permission checks
	 * against current editor/user. It doesn't distinguish core and extended fields.
	 * It grants BC.
	 * It's what you'd need in all front-end parsing code (e.g. shortcodes)
	 *
	 * @param string $field
	 * @param mixed $default
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @param boolean $rawExtended get raw DB values (no SQL query) - used only for extended fields
	 * @return mixed if field is not readable returns null by default
	 */
	public function getValue($field, $default = null, $short = true, $rawExtended = false)
	{
		if($short)
		{
			$mfield = $field;
			$field = 'user_'.$field;
		}
		else
		{
			$mfield = substr($field, 5);
		}

		// check for BC/override method first e.g. getSingatureValue($default, $system = false, $rawExtended);
		$method = 'get'.ucfirst($mfield).'Value';
		if(method_exists($this, $method)) return $this->$method($default, false, $rawExtended);

		if($this->isCoreField($field, false))
		{
			if(!$this->isReadable($field)) return $default;
			return $this->getCore($field, $default, false);
		}

		return $this->getExtendedFront($field, false, $rawExtended);
	}

	/**
	 * Transparent front-end setter. It performs all required write/applicable permission checks
	 * against current editor/user. It doesn't distinguish core and extended fields.
	 * It grants BC.
	 * It's what you'd need on all user front-end manipulation events (e.g. user settings page related code)
	 * NOTE: untrusted data should be provided via setPosted() method!
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @return e_user_model
	 */
	public function setValue($field, $value, $short = true)
	{
		if($short)
		{
			$mfield = $field;
			$field = 'user_'.$field;
		}
		else
		{
			$mfield = substr($field, 5);
		}

		// check for BC/override method first e.g. setSingatureValue($value, $system = false);
		$method = 'set'.ucfirst($mfield).'Value';
		if(method_exists($this, $method))
		{
			$this->$method($value, false);
			return $this;
		}

		if($this->isCoreField($field, false))
		{
			if($this->isWritable($field)) $this->setCore($field, $value, false, true);
		}
		else
		{
			$this->setExtendedFront($field, $value, false);
		}

		return $this;
	}

	/**
	 * Transparent system getter. It doesn't perform any read/applicable permission checks
	 * against current editor/user. It doesn't distinguish core and extended fields.
	 * It grants BC.
	 * It's here to serve in your application logic.
	 *
	 * @param string $field
	 * @param mixed $default
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @param boolean $rawExtended get raw DB values (no SQL query) - used only for extended fields
	 * @return mixed
	 */
	public function getSystem($field, $default = null, $short = true, $rawExtended = true)
	{
		if($short)
		{
			$mfield = $field;
			$field = 'user_'.$field;
		}
		else
		{
			$mfield = substr($field, 5);
		}

		// check for BC/override method first e.g. getSingatureValue($default, $system = true, $rawExtended);
		$method = 'get'.ucfirst($mfield).'Value';
		if(method_exists($this, $method)) return $this->$method($default, true, $rawExtended);

		if($this->isCoreField($field, false))
		{
			return $this->getCore($field, $default, false);
		}

		return $this->getExtended($field, false, $rawExtended);
	}

	/**
	 * Transparent front-end setter. It doesn't perform any write/applicable permission checks
	 * against current editor/user. It doesn't distinguish core and extended fields.
	 * It's here to serve in your application logic.
	 * NOTE: untrusted data should be provided via setPosted() method!
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @param boolean $strict if false no Applicable check will be made
	 * @return e_user_model
	 */
	public function setSystem($field, $value, $short = true, $strict = false)
	{
		if($short)
		{
			$mfield = $field;
			$field = 'user_'.$field;
		}
		else
		{
			$mfield = substr($field, 5);
		}

		// check for BC/override method first e.g. setSingatureValue($value, $system = true);
		$method = 'set'.ucfirst($mfield).'Value';
		if(method_exists($this, $method))
		{
			$this->$method($value, true);
			return $this;
		}

		if($this->isCoreField($field, false))
		{
			$this->setCore($field, $value, false, $strict);
		}
		else
		{
			$this->setExtended($field, $value, false, $strict);
		}

		return $this;
	}

	/**
	 * Just an example override method. This method is auto-magically called by getValue/System
	 * getters.
	 * $rawExtended is not used (here for example purposes only)
	 * If user_signature become extended field one day, we'd need this method
	 * for real - it'll call extended getters to retrieve the required value.
	 *
	 * @param mixed $default optional
	 * @param boolean $system optional
	 * @param boolean $rawExtended optional
	 * @return mixed value
	 */
	public function getSignatureValue($default = null, $system = false, $rawExtended = true)
	{
		if($system || $this->isReadable('user_signature')) return $this->getCore('signature', $default);
		return $default;
	}

	/**
	 * Just an example override method. This method is auto-magically called by setValue/System
	 * setters.
	 * If user_signature become extended field one day, we'd need this method
	 * for real - it'll call extended setters to set the new signature value
	 *
	 * @param string $value
	 * @param boolean $system
	 * @return e_user_model
	 */
	public function setSignatureValue($value, $system = false)
	{
		if($system || $this->isWritable('user_signature')) $this->setCore('signature', $value);
		return $this;
	}

	/**
	 * Get user preference
	 * @param string $pref_name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getPref($pref_name = null, $default = null)
	{
		if(null === $pref_name) return $this->getConfig()->getData();
		return $this->getConfig()->get($pref_name, $default);
	}

	/**
	 * Set user preference
	 * @param string $pref_name
	 * @param mixed $value
	 * @return e_user_model
	 */
	public function setPref($pref_name, $value = null)
	{
		$this->getConfig()->set($pref_name, $value);
		return $this;
	}

	/**
	 * Get user preference (advanced - slower)
	 * @param string $pref_path
	 * @param mixed $default
	 * @param integer $index if number, value will be exploded by "\n" and corresponding index will be returned
	 * @return mixed
	 */
	public function findPref($pref_path = null, $default = null, $index = null)
	{
		return $this->getConfig()->getData($pref_path, $default, $index);
	}

	/**
	 * Set user preference (advanced - slower)
	 * @param string $pref_path
	 * @param mixed $value
	 * @return e_user_model
	 */
	public function setPrefData($pref_path, $value = null)
	{
		$this->getConfig()->setData($pref_path, $value = null);
		return $this;
	}
	
	/**
	 * New - External login providers support
	 * @return string Provider name
	 */
	public function getProviderName()
	{
		if($this->get('user_xup'))
		{
			return array_shift(explode('_', $this->get('user_xup')));
		}
		return null;
	}
	
	/**
	 * New - External login providers support
	 * @return boolean Check if there is external provider data
	 */
	public function hasProviderName()
	{
		return $this->has('user_xup');
	}

	/**
	 * Get user extended model
	 *
	 * @return e_user_extended_model
	 */
	public function getExtendedModel()
	{
		if (null === $this->_extended_model)
		{
			$this->_extended_model = new e_user_extended_model($this);
		}
		return $this->_extended_model;
	}

	/**
	 * Set user extended model
	 *
	 * @param e_user_extended_model $extended_model
	 * @return e_user_model
	 */
	public function setExtendedModel($extended_model)
	{
		$this->_extended_model = $extended_model;
		return $this;
	}

	/**
	 * Get user config model
	 *
	 * @return e_user_pref
	 */
	public function getConfig()
	{
		if (null === $this->_user_config)
		{
			$this->_user_config = new e_user_pref($this);
		}
		return $this->_user_config;
	}

	/**
	 * Set user config model
	 *
	 * @param e_user_pref $user_config
	 * @return e_user_model
	 */
	public function setConfig(e_user_pref $user_config)
	{
		$this->_user_config = $user_config;
		return $this;
	}

	/**
	 * Get current user editor model
	 * @return e_user_model
	 */
	public function getEditor()
	{
		return $this->_editor;
	}

	/**
	 * Set current user editor model
	 * @return e_user_model
	 */
	public function setEditor(e_user_model $user_model)
	{
		$this->_editor = $user_model;
		return $this;
	}

	/**
	 * Check if passed field is writable
	 * @param string $field
	 * @return boolean
	 */
	public function isWritable($field)
	{
		$perm = false;
		$editor = $this->getEditor();
		if($this->getId() === $editor->getId() || $editor->isMainAdmin() || $editor->checkAdminPerms('4'))
			$perm = true;
		return ($perm && !in_array($field, array($this->getFieldIdName(), 'user_admin', 'user_perms', 'user_prefs')));
	}

	/**
	 * Check if passed field is readable by the Editor
	 * @param string $field
	 * @return boolean
	 */
	public function isReadable($field)
	{
		$perm = false;
		$editor = $this->getEditor();
		if($this->getId() === $editor->getId() || $editor->isMainAdmin() || $editor->checkAdminPerms('4'))
			$perm = true;
		return ($perm || (!in_array($field, array('user_admin', 'user_perms', 'user_prefs', 'user_password') && $editor->checkClass($this->_memberlist_access))));
	}

	/**
	 * Set current object as a target
	 *
	 * @return e_user_model
	 */
	protected function setAsTarget()
	{
		e107::setRegistry('core/e107/user/'.$this->getId(), $this);
		return $this;
	}

	/**
	 * Clear registered target
	 *
	 * @return e_user_model
	 */
	protected function clearTarget()
	{
		e107::setRegistry('core/e107/user'.$this->getId(), null);
		return $this;
	}

	/**
	 * @see e_model#load($id, $force)
	 */
	public function load($user_id = 0, $force = false)
	{
		$qry = "SELECT u.*, ue.* FROM #user AS u LEFT JOIN #user_extended as ue ON u.user_id=ue.user_extended_id WHERE u.user_id={ID}";
		$this->setParam('db_query', $qry);
		parent::load($user_id, $force);
		if ($this->getId())
		{
			// no errors - register
			$this->setAsTarget()
				->setEditor(e107::getUser()); //set current user as default editor
		}
	}

	/**
	 * Additional security while applying posted
	 * data to user model
	 * @return e_user_model
	 */
	public function mergePostedData($strict = true, $sanitize = true, $validate = true)
    {
    	$posted = $this->getPostedData();
    	foreach ($posted as $key => $value)
    	{
    		if(!$this->isWritable($key))
    		{
    			$this->removePosted($key);
    			continue;
    		}
    		$this->_modifyPostedData($key, $value);
    	}
		parent::mergePostedData(true, true, true);
		return $this;
    }

	protected function _modifyPostedData($key, $value)
    {
    	// TODO - add more here
    	switch ($key)
    	{
    		case 'password1':
    			// compare validation rule
    			$this->setPosted('user_password', array($value, $this->getPosted('password2')));
    		break;
    	}
    }

	/**
	 * Send model data to DB
	 */
	public function save($noEditorCheck = false, $force = false, $session = false)
	{
		if (!$noEditorCheck && !$this->checkEditorPerms())
		{
			return false; // TODO - message, admin log
		}

		// sync user prefs
		$this->getConfig()->apply();

		// TODO - do the save manually in this order: validate() on user model, save() on extended fields, save() on user model
		$ret = parent::save(true, $force, $session);
		
		if(false !== $ret && null !== $this->_extended_model) // don't load extended fields if not already used
		{
			$ret_e = $this->_extended_model->save(true, $force, $session);
			if(false !== $ret_e)
			{
				return ($ret_e + $ret);
			}
			return false;
		}
		return $ret;
	}

	public function saveDebug($extended = true, $return = false, $undo = true)
	{
		$ret = array();
		$ret['CORE_FIELDS'] = parent::saveDebug(true, $undo);
		if($extended && null !== $this->_extended_model)
		{
			$ret['EXTENDED_FIELDS'] = $this->_extended_model->saveDebug(true, $undo);
		}

		if($return) return $ret;
		print_a($ret);
	}

	public function destroy()
	{
		$this->clearTarget()
			->removeData();

		$this->_class_list = array();
		$this->_editor = null;
		$this->_extended_structure = null;
		$this->_user_config = null;

		if (null !== $this->_extended_model)
		{
			$this->_extended_model->destroy();
			 $this->_extended_model = null;
		}
	}


	/**
	 * Add userclass to user and save.
	 * @param null $userClassId
	 * @return bool
	 */
	public function addClass($userClassId=null)
	{
		if(empty($userClassId))
		{
			return false;
		}

		$curClasses = explode(",", $this->getData('user_class'));
		$curClasses[] = $userClassId;
		$curClasses = array_unique($curClasses);

		$insert = implode(",", $curClasses);

		//FIXME - @SecretR - I'm missing something here with setCore() etc.
	//	$this->setCore('user_class',$insert );
	//	$this->saveDebug(false);

		$uid = $this->getData('user_id');

		return e107::getDb()->update('user',"user_class='".$insert."' WHERE user_id = ".$uid." LIMIT 1");

	}


	/**
	 * Remove a userclass from the user.
	 * @param null $userClassId
	 * @return bool
	 */
	public function removeClass($userClassId=null)
	{
		if(empty($userClassId))
		{
			return false;
		}

		$curClasses = explode(",", $this->getData('user_class'));

		foreach($curClasses as $k=>$v)
		{
			if($v == $userClassId)
			{
				unset($curClasses[$k]);
			}
		}

		$uid = $this->getData('user_id');

		$insert = implode(",", $curClasses);

		return e107::getDb()->update('user',"user_class='".$insert."' WHERE user_id = ".$uid." LIMIT 1");


	}


}

// TODO - add some more useful methods, sc_* methods support
class e_system_user extends e_user_model
{
	public $debug = false;
	/**
	 * Constructor
	 *
	 * @param array $user_data trusted data, loaded from DB
	 * @return void
	 */
	public function __construct($user_data = array())
	{
		parent::__construct($user_data);
		$this->setEditor(e107::getUser());
	}

	/**
	 * Returns always false
	 * Even if user data belongs to the current user, Current User interface
	 * is not available
	 *
	 * @return boolean
	 */
	final public function isCurrent()
	{
		// check against current system user
		//return ($this->getId() && $this->getId() == e107::getUser()->getId());
		return false;
	}
	
	/**
	 * Send user email
	 * @param mixed $userInfo array data or null for current logged in user or any object subclass of e_object (@see e_system_user::renderEmail() for field requirements)
	 */
	public function email($type = 'email', $options = array(), $userInfo = null)
	{
		
		if(null === $userInfo)
		{
			$userInfo = $this->getData();
		}
		elseif(is_object($userInfo) && get_class($userInfo) == 'e_object' || is_subclass_of($userInfo, 'e_object'))
		{
			$userInfo = $userInfo->getData();
		}
		
		if(empty($userInfo) || !vartrue($userInfo['user_email'])) return false;
		
		// plain password could be passed only via $options
		unset($userInfo['user_password']);
		if($options && is_array($options))
		{
			$userInfo = array_merge($options, $userInfo);
		}
		
		$eml = $this->renderEmail($type, $userInfo);
		
		
		
		if(empty($eml))
		{
			if($this->debug)
			{
				echo '$eml returned nothing on Line '.__LINE__.' of user_model.php using $type = '.$type;
				print_a($userInfo);
			}
			 return false;
		}
		else
		{
			if($this->debug)
			{
				echo '<h3>$eml array</h3>';
				print_a($eml);
				$temp = var_export($eml, true);
				print_a($temp);
			}	
		}
		
		$mailer = e107::getEmail();
		
		$mailer->template = $eml['template'];

		
		// Custom e107 Header
		if($userInfo['user_id'])
		{
			$eml['e107_header'] = $userInfo['user_id']; 
		//	$mailer->AddCustomHeader("X-e107-id: {$userInfo['user_id']}");
		}


		if(getperms('0') && E107_DEBUG_LEVEL > 0)
		{
			e107::getMessage()->addDebug("Email Debugger active. <b>Simulation Only!</b>");
			e107::getMessage()->addDebug($mailer->preview($eml));
			return true;
		}

		if(!empty($options['debug']))
		{
			return $mailer->preview($eml);
		}

		
		return $mailer->sendEmail($userInfo['user_email'], $userInfo['user_name'], $eml, false);
	}
	
	/**
	 * Render user email. 
	 * Additional user fields:
	 * 'mail_subject' -> required when type is not signup
	 * 'mail_body' -> required when type is not signup
	 * 'mail_copy_to' -> optional, carbon copy, used when type is not signup
	 * 'mail_bcopy_to' -> optional, blind carbon copy, used when type is not signup
	 * 'mail_attach' -> optional, attach files, available for all types, additionally it overrides $SIGNUPEMAIL_ATTACHMENTS when type is signup
	 * 'mail_options' -> optional, available for all types, any additional valid mailer option as described in e107Email::sendEmail() phpDoc help (options above can override them)
	 * All standard user fields from the DB (user_name, user_loginname, etc.)
	 * 
	 * @param string $type signup|notify|email|quickadd
	 * @param array $userInfo
	 * @return array
	 */
	public function renderEmail($type, $userInfo)
	{	
		$pref = e107::getPref();
		$ret = array();
		$tp = e107::getParser();
		$mes = e107::getMessage();
		
	
		// mailer options
		if(isset($userInfo['mail_options']) && is_array($userInfo['mail_options']))
		{
			$ret = $userInfo['mail_options'];
		}

		// required for signup and quickadd email type
		e107::coreLan('signup');

		$EMAIL_TEMPLATE = e107::getCoreTemplate('email');
		
		if(!is_array($EMAIL_TEMPLATE)) //BC Fixes. pre v2 alpha3. 
		{
			// load from old location. (root of theme folder if it exists)

			$SIGNUPEMAIL_SUBJECT = '';
			$SIGNUPEMAIL_CC = '';
			$SIGNUPEMAIL_BCC = '';
			$SIGNUPEMAIL_ATTACHMENTS = '';
			$SIGNUPEMAIL_TEMPLATE = '';


			if (file_exists(THEME.'email_template.php'))
			{
				include(THEME.'email_template.php');
			}
			else
			{
				// include core default. 
				include(e107::coreTemplatePath('email'));
			}
			
			// BC Fixes. 
			$EMAIL_TEMPLATE['signup']['subject'] 		= $SIGNUPEMAIL_SUBJECT;
			$EMAIL_TEMPLATE['signup']['cc']				= $SIGNUPEMAIL_CC;
			$EMAIL_TEMPLATE['signup']['bcc']			= $SIGNUPEMAIL_BCC;
			$EMAIL_TEMPLATE['signup']['attachments']	= $SIGNUPEMAIL_ATTACHMENTS;		
			$EMAIL_TEMPLATE['signup']['body']			= $SIGNUPEMAIL_TEMPLATE;
			
			$EMAIL_TEMPLATE['quickadduser']['body']		= $QUICKADDUSER_TEMPLATE['email_body'];
			$EMAIL_TEMPLATE['notify']['body']			= $NOTIFY_TEMPLATE['email_body'];
			
		}
		
		$template = '';
		switch ($type) 
		{
			case 'signup':
				$template = (vartrue($SIGNUPPROVIDEREMAIL_TEMPLATE)) ? $SIGNUPPROVIDEREMAIL_TEMPLATE :  $EMAIL_TEMPLATE['signup']['body'];
				$ret['template'] = 'signup'; //  // false Don't allow additional headers (mailer) ??
			break;
			
			case 'quickadd':
				$template = $EMAIL_TEMPLATE['quickadduser']['body']; 
				$ret['template'] = 'quickadduser'; // Don't allow additional headers (mailer)
			break;
				
			case 'notify': 
				if(vartrue($userInfo['mail_body'])) $template = $userInfo['mail_body']; //$NOTIFY_HEADER.$userInfo['mail_body'].$NOTIFY_FOOTER; 
				$ret['template'] = 'notify';
			break;
				
			case 'email':
			case 'default':
				if(vartrue($userInfo['mail_body'])) $template = $userInfo['mail_body']; //$EMAIL_HEADER.$userInfo['mail_body'].$EMAIL_FOOTER; 
				$ret['template'] = 'default';
			break;
		}
		
		if(!$template)
		{
			$mes->addDebug('$template is empty in user_model.php line 1171.'); // Debug only, do not translate. 
			return array();
		}



	//
		
		// signup email only
		if($type == 'signup')
		{
			$HEAD = '';
			$FOOT = '';

			$pass_show = e107::pref('core','user_reg_secureveri', false);
			
			$ret['e107_header'] = $userInfo['user_id'];
			
			if (vartrue($EMAIL_TEMPLATE['signup']['cc'])) { $ret['email_copy_to'] = $EMAIL_TEMPLATE['signup']['cc']; }
			if (vartrue($EMAIL_TEMPLATE['signup']['bcc'])) { $ret['email_bcopy_to'] = $EMAIL_TEMPLATE['signup']['bcc']; }
			if (vartrue($userInfo['email_attach'])) { $ret['email_attach'] = $userInfo['mail_attach']; }
			elseif (vartrue($EMAIL_TEMPLATE['signup']['attachments'])) { $ret['email_attach'] = $EMAIL_TEMPLATE['signup']['attachments']; }
			
			$style = vartrue($SIGNUPEMAIL_LINKSTYLE) ? "style='{$SIGNUPEMAIL_LINKSTYLE}'" : "";


			if(empty($userInfo['activation_url']) && !empty($userInfo['user_sess']) && !empty($userInfo['user_id']))
			{
				$userInfo['activation_url'] = SITEURL."signup.php?activate.".$userInfo['user_id'].".".$userInfo['user_sess'];
			}

			
			$sc = array();
			
			$sc['LOGINNAME'] 		= intval($pref['allowEmailLogin']) === 0 ? $userInfo['user_loginname'] : $userInfo['user_email'];
			$sc['PASSWORD']			= ($pass_show && !empty($userInfo['user_password'])) ?  '*************' : $userInfo['user_password'];
			$sc['ACTIVATION_LINK']	= strpos($userInfo['activation_url'], 'http') === 0 ? '<a href="'.$userInfo['activation_url'].'">'.$userInfo['activation_url'].'</a>' : $userInfo['activation_url'];
		//	$sc['SITENAME']			= SITENAME;
			$sc['SITEURL']			= "<a href='".SITEURL."' {$style}>".SITEURL."</a>";
			$sc['USERNAME']			= $userInfo['user_name'];
			$sc['USERURL']			= vartrue($userInfo['user_website']) ? $userInfo['user_website'] : "";
			$sc['DISPLAYNAME']		= $userInfo['user_login'] ? $userInfo['user_login'] : $userInfo['user_name'];
			$sc['EMAIL']			= $userInfo['user_email'];
			$sc['ACTIVATION_URL']	= $userInfo['activation_url'];
			
			$ret['subject'] =  $EMAIL_TEMPLATE['signup']['subject']; // $subject;
			$ret['send_html'] = TRUE;
			$ret['shortcodes'] = $sc;
		
			if(!varset($EMAIL_TEMPLATE['signup']['header']))
			{
		
				$HEAD = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";
				$HEAD .= "<html xmlns='http://www.w3.org/1999/xhtml' >\n";
				$HEAD .= "<head><meta http-equiv='content-type' content='text/html; charset=utf-8' />\n";
				$HEAD .= ($SIGNUPEMAIL_USETHEME == 1) ? "<link rel=\"stylesheet\" href=\"".SITEURLBASE.THEME_ABS."style.css\" type=\"text/css\" />\n" : "";
			    $HEAD .= "<title>".LAN_SIGNUP_58."</title>\n";
				
				if($SIGNUPEMAIL_USETHEME == 2) // @deprecated in favor of {STYLESHEET}
				{ 
					$CSS = file_get_contents(THEME."style.css");
					$HEAD .= "<style>\n".$CSS."\n</style>";
				}
			
				$HEAD .= "</head>\n";
				if(vartrue($SIGNUPEMAIL_BACKGROUNDIMAGE)) // @deprecated. 
				{
					$HEAD .= "<body background=\"".$SIGNUPEMAIL_BACKGROUNDIMAGE."\" >\n";
				}
				else
				{
					$HEAD .= "<body>\n";
				}
			
			}
			else
			{
				$HEAD = ""; // $tp->parseTemplate($EMAIL_TEMPLATE['signup']['header'], true);	
			}
			
			if(!varset($EMAIL_TEMPLATE['signup']['footer']))
			{
				$FOOT = "\n</body>\n</html>\n";
			}
			else
			{
				$FOOT = ""; // $tp->parseTemplate($EMAIL_TEMPLATE['signup']['footer'], true);
			}
		
			$ret['send_html'] 		= TRUE;
			$ret['email_body'] 		= $HEAD.$template.$FOOT; // e107::getParser()->parseTemplate(str_replace($search,$replace,$HEAD.$template.$FOOT), true);
			$ret['preview'] 		= $tp->parseTemplate($ret['email_body'],true, $sc);// Non-standard field
			$ret['shortcodes'] 		= $sc;
			
			
			return $ret;
		}

		


		// all other email types		
		if(!$userInfo['mail_subject'])
		{
			$mes->addDebug('No Email subject provided to renderEmail() method.'); // Debug only, do not translate. 
			return array();
		}
		

		$templateName = $ret['template'];
		
//		$ret['email_subject'] 	=  varset($EMAIL_TEMPLATE[$templateName]['subject'], $EMAIL_TEMPLATE['default']['subject']) ; // $subject;
		$ret['subject']         = $userInfo['mail_subject'];
		$ret['e107_header'] 	= $userInfo['user_id'];
		
		if (vartrue($userInfo['email_copy_to'])) 	{ 	$ret['email_copy_to']	= $userInfo['email_copy_to']; }
		if (vartrue($userInfo['email_bcopy_to'])) 	{ 	$ret['email_bcopy_to'] 	= $userInfo['email_bcopy_to']; }
		if (vartrue($userInfo['email_attach']))		{ 	$ret['email_attach'] 	= $userInfo['email_attach']; }
		
		$sc = array();
		
		$sc['LOGINNAME']			= intval($pref['allowEmailLogin']) === 0 ? $userInfo['user_loginname'] : $userInfo['user_email'];
		$sc['DISPLAYNAME']			= $userInfo['user_login'] ? $userInfo['user_login'] : $userInfo['user_name'];
		$sc['SITEURL']				= "<a href='".SITEURL."'>".SITEURL."</a>";
		$sc['USERNAME']				= $userInfo['user_name'];
		$sc['USERURL']				= vartrue($userInfo['user_website'], '');
		$sc['PASSWORD']				= vartrue($userInfo['user_password'], '***********');
		$sc['SUBJECT']				= $userInfo['mail_subject'];


		if(isset($userInfo['activation_url']))
		{
			$sc['ACTIVATION_URL']	= $userInfo['activation_url'];
			$sc['ACTIVATION_LINK']	= strpos($userInfo['activation_url'], 'http') === 0 ? '<a href="'.$userInfo['activation_url'].'">'.$userInfo['activation_url'].'</a>' : $userInfo['activation_url'];
		}
		
		$ret['send_html'] 		= true;
		$ret['email_body'] 		= $template; // e107::getParser()->parseTemplate(str_replace($search, $replace, $template)); - performed in mail handler. 
		$ret['preview'] 		= $ret['mail_body']; // Non-standard field
		$ret['shortcodes'] 		= $sc;
		
		return $ret;
	}
}

/**
 * Current system user
 * @author SecretR
 */
class e_user extends e_user_model
{
	private $_session_data = null;
	private $_session_key = null;
	private $_session_type = null;
	private $_session_error = false;

	private $_parent_id = false;
	private $_parent_data = array();
	private $_parent_extmodel = null;
	private $_parent_extstruct = null;
	private $_parent_config = null;
	
	/**
	 * @var Hybrid_Provider_Model
	 */
	protected $_provider;

	public function __construct()
	{
		$this->setSessionData() // retrieve data from current session
			->load() // load current user from DB
			->setEditor($this); // reference to self
	}
	
	/**
	 * Yes, it's current user - return always true
	 * NOTE: it's not user check, use isUser() instead!
	 * @return boolean
	 */
	final public function isCurrent()
	{
		return true;
	}

	/**
	 * Get parent user ID - present if main admin is browsing
	 * front-end logged in as another user account
	 *
	 * @return integer or false if not present
	 */
	final public function getParentId()
	{
		return $this->_parent_id;
	}
	
	/**
	 * Init external user login/signup provider
	 * @return e_system_user
	 */
	public function initProvider()
	{
		if(null !== $this->_provider) return $this;

		if($this->get('user_xup'))
		{
			$providerId = $this->getProviderName();
			require_once(e_HANDLER.'user_handler.php');
			$this->_provider = new e_user_provider($providerId);
			$this->_provider->init();
		}
	}
	
	/**
	 * Get external user provider
	 * @return Hybrid_Provider_Model
	 */
	public function getProvider()
	{
		if(null === $this->_provider) $this->initProvider();
		return $this->_provider;
	}
	
	
	/**
	 * Set external user provider (already initialized)
	 * @return e_user
	 */
	public function setProvider($provider)
	{
		$this->_provider = $provider;
		return $this;
	}
	
	/**
	 * Check if this user has assigned login provider
	 * @return boolean
	 */
	public function hasProvider()
	{
		return ($this->getProvider() !== null);
	}

	/**
	 * User login
	 * @param string $uname
	 * @param string $upass_plain
	 * @param boolean $uauto
	 * @param string $uchallange
	 * @param boolean $noredirect
	 * @return boolean success
	 */
	final public function login($uname, $upass_plain, $uauto = false, $uchallange = false, $noredirect = true)
	{
		if($this->isUser()) return false;

		$userlogin = new userlogin();
		$userlogin->login($uname, $upass_plain, $uauto, $uchallange, $noredirect);
		
		$userdata  = $userlogin->getUserData(); 
		
		$this->setSessionData(true)->setData($userdata);
		
		e107::getEvent()->trigger('user_login', $userdata); 	

		return $this->isUser();
	}
	
	/**
	 * User login via external user provider
	 * @param string $xup external user provider identifier
	 * @return boolean success
	 */
	final public function loginProvider($xup)
	{
		if(!e107::getPref('social_login_active', false))  return false;
		
		if($this->isUser()) return true;
		
		$userlogin = new userlogin();
		$userlogin->login($xup, '', 'provider', false, true);
		
		$userdata  = $userlogin->getUserData();

		if(defset('E107_DEBUG_LEVEL', 0) > 0)
		{
			e107::getLog()->add('XUP Debug', (__CLASS__ . ':' . __METHOD__ . '-' . __LINE__), E_LOG_INFORMATIVE, "XUP_DEBUG");
		}
		
		$this->setSessionData(true)->setData($userdata);
			
		e107::getEvent()->trigger('user_xup_login', $userdata);

		return $this->isUser();
	}

	/**
	 * Login as another user account
	 * @param integer $user_id
	 * @return boolean success
	 */
	final public function loginAs($user_id)
	{
		// TODO - set session data required for loadAs()
		if($this->getParentId()
			|| !$this->isMainAdmin()
			|| empty($user_id)
			|| $this->getSessionDataAs()
			|| $user_id == $this->getId()
		) return false;

		$key = $this->_session_key.'_as';

		if('session' == $this->_session_type)
		{
			$_SESSION[$key] = $user_id;
		}
		elseif('cookie' == $this->_session_type)
		{
			$_COOKIE[$key] = $user_id;
			cookie($key, $user_id);
		}

		// TODO - lan
		e107::getAdminLog()->log_event('Head Admin used Login As feature', 'Head Admin [#'.$this->getId().'] '.$this->getName().' logged in user account #'.$user_id);
		//$this->loadAs(); - shouldn't be called here - loginAs should be called in Admin area only, loadAs - front-end
		return true;
	}

	/**
	 *
	 * @return e_user
	 */
	protected function _initConstants()
	{
		//FIXME - BC - constants from init_session() should be defined here
		// [SecretR] Not sure we should do this here, it's too restricting - constants can be
		// defined once, we need the freedom to do it multiple times - e.g. load() executed in constructor than login(), loginAs() etc.
		// called by a controller
		// We should switch to e.g. isAdmin() instead of ADMIN constant check
		return $this;
	}

	/**
	 * Destroy cookie/session data, self destroy
	 * @return e_user
	 */
	final public function logout()
	{
		if($this->hasProvider())
		{
			$this->getProvider()->logout();
		}
		$this->logoutAs()
			->_destroySession();

		parent::destroy();
		//if(session_id()) session_destroy();
		e107::getSession()->destroy();

		e107::setRegistry('core/e107/current_user', null);
		return $this;
	}

	/**
	 * Destroy cookie/session/model data for current user, resurrect parent user
	 * @return e_user
	 */
	final public function logoutAs()
	{
		if($this->getParentId())
		{
			// load parent user data
			$this->_extended_model = $this->_parent_extmodel;
			$this->_extended_structure = $this->_parent_extstruct;
			$this->_user_config = $this->_parent_config;
			if($this->_parent_model)
				$this->setData($this->_parent_model->getData());

			// cleanup
			$this->_parent_id = false;
			$this->_parent_model = $this->_parent_extstruct = $this->_parent_extmodel = $this->_parent_config = null;
		}
		$this->_destroyAsSession();
		return $this;
	}
	
	public function tryProviderSession($deniedAs)
	{
		// don't allow if main admin browse front-end or there is already user session
		if((!$deniedAs && $this->getSessionDataAs()) || null !== $this->_session_data || !e107::getPref('social_login_active', false)) return $this;
		
		try
		{
			// detect all currently connected providers
			$hybrid = e107::getHybridAuth(); // init the auth class
			$connected = Hybrid_Auth::getConnectedProviders();
		}
		catch(Exception $e)
		{
			e107::getMessage()->addError('['.$e->getCode().']'.$e->getMessage(), 'default', true);
			$session = e107::getSession();
			$session->set('HAuthError', true);
			$connected = false;
		}
		// no active session found 
		if(!$connected) return $this;
		
		// query DB
		$sql = e107::getDb();
		$where = array();
		$userdata = array();

		foreach ($connected as $providerId) 
		{
			$adapter = Hybrid_Auth::getAdapter($providerId);
			
			if(!$adapter->getUserProfile()->identifier) continue;

			$profile = $adapter->getUserProfile();

			$userdata['user_name']  = $sql->escape($profile->displayName);
			$userdata['user_image'] = $profile->photoURL; // avatar
			$userdata['user_email'] = $profile->email;

			$id = $providerId.'_'.$profile->identifier;
			$where[] = "user_xup='".$sql->escape($id)."'";
		}


		$where = implode(' OR ', $where);
		if($sql->select('user', 'user_id, user_name, user_email, user_image, user_password, user_xup', $where))
		{

			$user = $sql->fetch();
			e107::getUserSession()->makeUserCookie($user);
			$this->setSessionData();

			$spref = e107::pref('social');

			// Update display name or avatar image if they have changed.
			if(( empty($user['user_email']) && !empty($userdata['user_email']) ) || !empty($spref['xup_login_update_username']) || !empty($spref['xup_login_update_avatar']) || ($userdata['user_name'] != $user['user_name']) || ($userdata['user_image'] != $user['user_image']))
			{
				$updateQry = array();

				if(!empty($spref['xup_login_update_username']))
				{
					$updateQry['user_name'] = $userdata['user_name'];
				}

				if(!empty($spref['xup_login_update_avatar']))
				{
					$updateQry['user_image'] = $userdata['user_image'];
				}

				if(empty($user['user_email']))
				{
					$updateQry['user_email'] = $userdata['user_email'];
				}

				$updateQry['WHERE'] = "user_id=".$user['user_id']." LIMIT 1";

				if($sql->update('user', $updateQry) !==false)
				{
					$updatedProfile = array_replace($user, $userdata);
					e107::getEvent()->trigger('user_xup_updated', $updatedProfile);
					e107::getLog()->add('User Profile Updated', $userdata, E_LOG_INFORMATIVE, "XUP_LOGIN", LOG_TO_ADMIN, array('user_id'=>$user['user_id'],'user_name'=>$user['user_name'], 'user_email'=>$userdata['user_email']));
				}
				else
				{
					e107::getLog()->add('User Profile Update Failed', $userdata, E_LOG_WARNING, "XUP_LOGIN", LOG_TO_ADMIN, array('user_id'=>$user['user_id'],'user_name'=>$user['user_name'], 'user_email'=>$userdata['user_email']));
				}
			}

			unset($user['user_password']);
			e107::getLog()->user_audit(USER_AUDIT_LOGIN,'', $user['user_id'], $user['user_name']);
			// e107::getLog()->add('XUP Login', $user, E_LOG_INFORMATIVE, "LOGIN", LOG_TO_ROLLING, array('user_id'=>$user['user_id'],'user_name'=>$user['user_name']));
		}
		
		return $this;
	}

	/**
	 * TODO load user data by cookie/session data
	 * @return e_user
	 */
	final public function load($force = false, $denyAs = false)
	{
		if(!$force && $this->getId()) return $this;

		if(deftrue('e_ADMIN_AREA')) $denyAs = true;

		// always run cli as main admin
		if(e107::isCli())
		{
			$this->_load(1, $force);
			$this->_initConstants();
			return $this;
		}
		
		// NEW - new external user login provider feature
		$this->tryProviderSession($denyAs);

		// We have active session
		if(null !== $this->_session_data)
		{
			list($uid, $upw) = explode('.', $this->_session_data);
			// Bad cookie - destroy session
			if(empty($uid) || !is_numeric($uid) || empty($upw))
			{
				$this->_destroyBadSession();
				$this->_initConstants();
				return $this;
			}

			$udata = $this->_load($uid, $force);
			// Bad cookie - destroy session
			if(empty($udata))
			{
				$this->_destroyBadSession();
				$this->_initConstants();
				return $this;
			}

			// we have a match
			if(md5($udata['user_password']) == $upw)
			{
				// set current user data
				$this->setData($udata);

				// NEW - try 'logged in as' feature
				if(!$denyAs) $this->loadAs();

				// update lastvisit field
				$this->updateVisit();

				// currently does nothing
				$this->_initConstants();
				
				// init any available external user provider
				if(e107::getPref('social_login_active', false)) $this->initProvider();
				
				return $this;
			}

			$this->_destroyBadSession();
			$this->_initConstants();
			return $this;
		}

		return $this;
	}

	final public function loadAs()
	{
		// FIXME - option to avoid it when browsing Admin area
		$loginAs = $this->getSessionDataAs();
		if(!$this->getParentId() && false !== $loginAs && $loginAs !== $this->getId() && $loginAs !== 1 && $this->isMainAdmin())
		{
			$uasdata = $this->_load($loginAs);
			if(!empty($uasdata))
			{
				// backup parent user data to prevent further db queries
				$this->_parent_id = $this->getId();
				$this->_parent_model = new e_user_model($this->getData());
				$this->setData($uasdata);

				// not allowed - revert back
				if($this->isMainAdmin())
				{
					$this->_parent_id = false;
					$this->setData($this->_parent_model->getData());
					$this->_parent_model = null;
					$this->_destroyAsSession();
				}
				else
				{
					$this->_parent_extmodel = $this->_extended_model;
					$this->_parent_extstruct = $this->_extended_structure;
					$this->_user_config = $this->_parent_config;
					$this->_extended_model = $this->_extended_structure = $this->_user_config = null;
				}
			}
		}
		else
		{
			$this->_parent_id = false;
			$this->_parent_model = null;
			$this->_parent_extstruct = $this->_parent_extmodel = null;
		}
		return $this;
	}

	/**
	 * Update user visit timestamp
	 * @return void
	 */
	protected function updateVisit()
	{
		// Don't update if main admin is logged in as current (non main admin) user
		if(!$this->getParentId())
		{
			$sql = e107::getDb();
			$this->set('last_ip', $this->get('user_ip'));
			$current_ip = e107::getIPHandler()->getIP(FALSE);
			$update_ip = $this->get('user_ip' != $current_ip ? ", user_ip = '".$current_ip."'" : "");
			$this->set('user_ip', $current_ip);
			if($this->get('user_currentvisit') + 3600 < time() || !$this->get('user_lastvisit'))
			{
				$this->set('user_lastvisit', (integer) $this->get('user_currentvisit'));
				$this->set('user_currentvisit', time());
				$sql->db_Update('user', "user_visits = user_visits + 1, user_lastvisit = ".$this->get('user_lastvisit').", user_currentvisit = ".$this->get('user_currentvisit')."{$update_ip} WHERE user_id='".$this->getId()."' ");
			}
			else
			{
				$this->set('user_currentvisit', time());
				$sql->db_Update('user', "user_currentvisit = ".$this->get('user_currentvisit')."{$update_ip} WHERE user_id='".$this->getId()."' ");
			}
		}
	}

	final protected function _destroySession()
	{
		cookie($this->_session_key, '', (time() - 2592000));
		unset($_SESSION[$this->_session_key]);

		return $this;
	}

	final protected function _destroyAsSession()
	{
		$key = $this->_session_key.'_as';
		cookie($key, '', (time() - 2592000));
		$_SESSION[$key] = '';
		unset($_SESSION[$key]);

		return $this;
	}

	final protected function _destroyBadSession()
	{
		$this->_session_error = true;
		return $this->_destroySession();
	}

	final public function getSessionDataAs()
	{
		$id = false;
		$key = $this->_session_key.'_as';

		if('session' == $this->_session_type && isset($_SESSION[$key]) && !empty($_SESSION[$key]))
		{
			$id = $_SESSION[$key];
		}
		elseif('cookie' == $this->_session_type && isset($_COOKIE[$key]) && !empty($_COOKIE[$key]))
		{
			$id = $_COOKIE[$key];
		}

		if(!empty($id) && is_numeric($id)) return intval($id);

		return false;
	}

	final public function setSessionData($force = false)
	{
		if($force || null === $this->_session_data)
		{
			$this->_session_data = null;
			$this->_session_key = e107::getPref('cookie_name', 'e107cookie');
			$this->_session_type = e107::getPref('user_tracking', 'cookie');
			
			if('session' == $this->_session_type && isset($_SESSION[$this->_session_key]) && !empty($_SESSION[$this->_session_key]))
			{
				$this->_session_data = &$_SESSION[$this->_session_key];
			}
			elseif('cookie' == $this->_session_type && isset($_COOKIE[$this->_session_key]) && !empty($_COOKIE[$this->_session_key]))
			{
				$this->_session_data = &$_COOKIE[$this->_session_key];
			}
		}

		return $this;
	}

	public function hasSessionError()
	{
		return $this->_session_error;
	}


	final protected function _load($user_id)
	{
		$qry = 'SELECT u.*, ue.* FROM #user AS u LEFT JOIN #user_extended as ue ON u.user_id=ue.user_extended_id WHERE u.user_id='.intval($user_id);
		if(e107::getDb()->gen($qry))
		{
			return e107::getDb()->fetch();
		}
		return array();
	}

	/**
	 * Not allowed
	 *
	 * @return e_user_model
	 */
	final protected function setAsTarget()
	{
		return $this;
	}

	/**
	 * Not allowed
	 *
	 * @return e_user_model
	 */
	final protected function clearTarget()
	{
		return $this;
	}

	public function destroy()
	{
		// not allowed - see logout()
	}
}

class e_user_extended_model extends e_admin_model
{
	/**
	 * Describes known model fields
	 * @var array
	 */
	protected $_data_fields = array(
		'user_extended_id'	 => 'integer',
		'user_hidden_fields' => 'string',
	);

	/**
	 * @see e_model
	 * @var string
	 */
	protected $_db_table = 'user_extended';

	/**
	 * @see e_model
	 * @var string
	 */
	protected $_field_id = 'user_extended_id';

	/**
	 * @see e_model
	 * @var string
	 */
	protected $_message_stack = 'user';

	/**
	 * User class as set in user Adminsitration
	 *
	 * @var integer
	 */
	protected $_memberlist_access = null;

	/**
	 * @var e_user_extended_structure_tree
	 */
	protected $_structure = null;

	/**
	 * User model, the owner of extended fields model
	 * @var e_user_model
	 */
	protected $_user = null;

	/**
	 * Stores access classes and default value per custom field
	 * @var array
	 */
	protected $_struct_index = array();

	/**
	 * Constructor
	 * @param e_user_model $user_model
	 * @return void
	 */
	public function __construct(e_user_model $user_model)
	{
		$this->_memberlist_access = e107::getPref('memberlist_access');
		$this->setUser($user_model)
			->load();
	}

	/**
	 * Always return integer
	 */
	public function getId()
	{
		return (integer) parent::getId();
	}

	/**
	 * Get user model
	 * @return e_user_model
	 */
	public function getUser()
	{
		return $this->_user;
	}

	/**
	 * Set User model
	 * @param e_user_model $user_model
	 * @return e_user_extended_model
	 */
	public function setUser($user_model)
	{
		$this->_user = $user_model;
		return $this;
	}

	/**
	 * Get current user editor model
	 * @return e_user_model
	 */
	public function getEditor()
	{
		return $this->getUser()->getEditor();
	}

	/**
	 * Bad but required (BC) method of retrieving all user data
	 * It's here to be used from get_user_data() core function.
	 * DON'T USE IT unless you have VERY good reason to do it.
	 * TODO - revise this! Merge it to getSystemData, getApplicableData
	 *
	 * @return array
	 */
	public function getExtendedData()
	{
		$ret = array();

		$fields = $this->getExtendedStructure()->getFieldTree();
		foreach ($fields as $id => $field)
		{
			$value = $this->getValue($field->getValue('name'));
			if(null !== $value) $ret[$field->getValue('name')] = $value;
		}

		$ret['user_extended_id'] = $this->getId();
		$ret['user_hidden_fields'] = $this->get('user_hidden_fields');

		return $ret;
	}

	/**
	 * Get User extended field value. It performs all required read/applicable permission checks
	 * against current editor/user.
	 * Returns NULL when field/default value not found or not enough permissions
	 * @param string $field
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @param boolean $raw doesn't retrieve db value when true (no sql query)
	 * @return mixed
	 */
	public function getValue($field, $short = true, $raw = false)
	{
		if($short) $field = 'user_'.$field;
		if (!$this->checkRead($field))
			return null;
		if(!$raw && vartrue($this->_struct_index[$field]['db']))
		{
			return $this->getDbValue($field);
		}
		return $this->get($field, $this->getDefault($field));
	}

	/**
	 * Set User extended field value, only if current editor has write permissions and field
	 * is applicable for the current user.
	 * Note: Data is not sanitized!
	 * @param string $field
	 * @param mixed $value
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @return e_user_extended_model
	 */
	public function setValue($field, $value, $short = true)
	{
		if($short) $field = 'user_'.$field;
		if (!$this->checkWrite($field))
			return $this;

		$this->set($field, $value, true);
		return $this;
	}

	/**
	 * Retrieve value of a field of type 'db'. It does sql request only once.
	 *
	 * @param string $field field name
	 * @return mixed db value
	 */
	protected function getDbValue($field)
	{
		if(null !== $this->_struct_index[$field]['db_value'])
		{
			return $this->_struct_index[$field]['db_value'];
		}

		// retrieve db data
		$value = $this->get($field);
		list($table, $field_id, $field_name, $field_order) = explode(',', $this->_struct_index[$field]['db'], 4);
		$this->_struct_index[$field]['db_value'] = $value;
		if($value && $table && $field_id && $field_name && e107::getDb()->db_Select($table, $field_name, "{$field_id}='{$value}'"))
		{
			$res = e107::getDb()->db_Fetch();
			$this->_struct_index[$field]['db_value'] = $res[$field_name];
		}

		return $this->_struct_index[$field]['db_value'];
	}

	/**
	 * System getter. It doesn't perform any read/applicable permission checks
	 * against current editor/user.
	 * It's here to serve in your application logic.
	 *
	 * @param string $field
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @param boolean $raw don't retrieve db value
	 * @return mixed
	 */
	public function getSystem($field, $short = true, $raw = true)
	{
		if($short) $field = 'user_'.$field;

		if(!$raw && vartrue($this->_struct_index[$field]['db']))
		{
			return $this->getDbValue($field);
		}
		return $this->get($field, $this->getDefault($field));
	}

	/**
	 * System setter. It doesn't perform any write/applicable permission checks
	 * against current editor/user.
	 * It's here to serve in your application logic.
	 * NOTE: untrusted data should be provided via setPosted() method!
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param boolean $short if true, 'user_' prefix will be added to field name
	 * @param boolean $strict if false no Applicable check will be made
	 * @return e_user_model
	 */
	public function setSystem($field, $value, $short = true, $strict = true)
	{
		if($short) $field = 'user_'.$field;

		$this->set($field, $value, $strict);
		return $this;
	}

	public function getReadData()
	{
		// TODO array allowed user profile page data (read mode)
	}

	public function getWriteData()
	{
		// TODO array allowed user settings page data (edit mode)
	}

	/**
	 * Get default field value, defined by extended field structure
	 * Returns NULL if field/default value not found
	 * @param string $field
	 * @return mixed
	 */
	public function getDefault($field)
	{
		return varset($this->_struct_index[$field]['default'], null);
	}

	/**
	 * Check field read permissions against current editor
	 * @param string $field
	 * @return boolean
	 */
	public function checkRead($field)
	{
		$hidden = $this->get('user_hidden_fields');
		$editor = $this->getEditor();

		if(!empty($hidden) && $this->getId() !== $editor->getId() && strpos($hidden, '^'.$field.'^') !== false) return false;

		return ($this->checkApplicable($field) && $editor->checkClass($this->_memberlist_access) && $editor->checkClass(varset($this->_struct_index[$field]['read'])));
	}

	/**
	 * Check field write permissions against current editor
	 * @param string $field
	 * @return boolean
	 */
	public function checkWrite($field)
	{
		if(!$this->checkApplicable($field)) return false;

		$editor = $this->getEditor();
		// Main admin checked later in checkClass() method
		if($editor->checkAdminPerms('4') && varset($this->_struct_index[$field]['write']) != e_UC_NOBODY)
			return true;

		return $editor->checkClass(varset($this->_struct_index[$field]['write']));
	}

	/**
	 * Check field signup permissions
	 * @param string $field
	 * @return boolean
	 */
	public function checkSignup($field)
	{
		return $this->getUser()->checkClass(varset($this->_struct_index[$field]['signup']));
	}

	/**
	 * Check field applicable permissions against current user
	 * @param string $field
	 * @return boolean
	 */
	public function checkApplicable($field)
	{
		return $this->getUser()->checkClass(varset($this->_struct_index[$field]['apply']));
	}

	/**
	 * @see e_model#load($id, $force)
	 * @return e_user_extended_model
	 */
	public function load($id=null, $force = false)
	{
		if ($this->getId() && !$force)
			return $this;

		$this->_loadDataAndAccess();
		return $this;
	}

	/**
	 * Check if given field name is present in extended user table structure
	 *
	 * @param string $field
	 * @param boolean $short
	 * @return boolean
	 */
	public function isField($field, $short = true)
	{
		if($short) $field = 'user_'.$field;
		return (isset($this->_struct_index[$field]) || in_array($field, array($this->getFieldIdName(), 'user_hidden_fields')));
	}

	/**
	 * Load extended fields permissions once (performance)
	 * @return e_user_extended_model
	 */
	protected function _loadDataAndAccess()
	{
		$struct_tree = $this->getExtendedStructure();
		$user = $this->getUser();
		if ($user && $struct_tree->hasTree())
		{
			// load structure dependencies
			$ignore = array($this->getFieldIdName(), 'user_hidden_fields');

			// set ignored values
			foreach ($ignore as $field_name)
			{
				$this->set($field_name, $user->get($field_name));
			}

			$fields = $struct_tree->getTree();
			foreach ($fields as $id => $field)
			{
				$field_name = 'user_'.$field->getValue('name');
				$this->set($field_name, $user->get($field_name));
				if (!in_array($field->getValue('name'), $ignore))
				{
					$this->_struct_index[$field_name] = array(
						'db'		 => $field->getValue('type') == 4 ? $field->getValue('values') : '',
						'db_value'	 => null, // used later for caching DB results
						'read'		 => $field->getValue('read'),
						'write'		 => $field->getValue('write'),
						'signup'	 => $field->getValue('signup'),
						'apply'		 => $field->getValue('applicable'),
						'default'	 => $field->getValue('default'),
					);
				}
			}
		}
		return $this;
	}

	/**
	 * Build manage rules for single field
	 * @param $structure_model
	 * @return e_user_extended_model
	 */
	protected function _buildManageField(e_user_extended_structure_model $structure_model)
	{
		$ftype = $structure_model->getValue('type') == 6 ? 'integer' : 'string';

		// 0- field control (html) attributes;1 - regex; 2 - validation error msg;
		$parms = explode('^,^', $structure_model->getValue('parms'));

		// validaton rules
		$vtype = $parms[1] ? 'regex' : $ftype;
		$name = 'user_'.$structure_model->getValue('name');
		$this->setValidationRule($name, array($vtype, $parms[1], $structure_model->getValue('text'), $parms[2]), $structure_model->getValue('required'));

		// data type, required for sql query
		$this->_data_fields[$name] = $ftype;
		return $this;
	}

	/**
	 * Build manage rules for single field
	 * @param $structure_model
	 * @return e_user_extended_model
	 */
	protected function _buildManageRules()
	{
		$struct_tree = $this->getExtendedStructure();
		if ($this->getId() && $struct_tree->hasTree())
		{
			// load structure dependencies TODO protected fields check as method
			$ignore = array($this->getFieldIdName(), 'user_hidden_fields'); // TODO - user_hidden_fields? Old?
			$fields = $struct_tree->getTree();
			foreach ($fields as $id => $field)
			{
				if (!in_array('user_'.$field->getValue('name'), $ignore) && !$field->isCategory())
				{
					// build _data_type and rules
					$this->_buildManageField($field);
				}
			}
		}
		return $this;
	}

	/**
	 * Get extended structure tree
	 * @return e_user_extended_structure_tree
	 */
	public function getExtendedStructure()
	{
		if (null === $this->_structure)
			$this->_structure = e107::getUserStructure();
		return $this->_structure;
	}

	/**
	 * Additional security while applying posted
	 * data to user extended model
	 * @return e_user_extended_model
	 */
	public function mergePostedData($strict = true, $sanitize = true, $validate = true)
    {
    	$posted = $this->getPostedData();
    	foreach ($posted as $key => $value)
    	{
    		if(!$this->checkWrite($key))
    		{
    			$this->removePosted($key);
    		}
    	}
		parent::mergePostedData(true, true, true);
		return $this;
    }

	/**
	 * Build data types and rules on the fly and save
	 * @see e_front_model::save()
	 */
	public function save($from_post = true, $force = false, $session = false)
	{
		// when not loaded from db, see the construct check
		if(!$this->getId()) 
		{
			$this->setId($this->getUser()->getId());
		}
		$this->_buildManageRules();
		// insert new record
		if(!e107::getDb()->db_Count('user_extended', '(user_extended_id)', "user_extended_id=".$this->getId()))
		{
			return $this->insert(true, $session);
		}
		return parent::save(true, $force, $session);
	}

	/**
	 * Doesn't save anything actually...
	 */
	public function saveDebug($retrun = false, $undo = true)
	{
		$this->_buildManageRules();
		return parent::saveDebug($return, $undo);
	}
}

class e_user_extended_structure_model extends e_model
{
	/**
	 * @see e_model
	 * @var string
	 */
	protected $_db_table = 'user_extended_struct';

	/**
	 * @see e_model
	 * @var string
	 */
	protected $_field_id = 'user_extended_struct_id';

	/**
	 * @see e_model
	 * @var string
	 */
	protected $_message_stack = 'user_struct';

	/**
	 * Get User extended structure field value
	 *
	 * @param string$field
	 * @param string $default
	 * @return mixed
	 */
	public function getValue($field, $default = '')
	{
		$field = 'user_extended_struct_'.$field;
		return $this->get($field, $default);
	}

	/**
	 * Set User extended structure field value
	 *
	 * @param string $field
	 * @param mixed $value
	 * @return e_user_model
	 */
	public function setValue($field, $value)
	{
		$field = 'user_extended_struct_'.$field;
		$this->set($field, $value, false);
		return $this;
	}

	public function isCategory()
	{
		return ($this->getValue('type') ? false : true);
	}

	public function getCategoryId()
	{
		return $this->getValue('parent');
	}

	public function getLabel()
	{
		$label = $this->isCategory() ? $this->getValue('name') : $this->getValue('text');
		return defset($label, $label);
	}

	/**
	 * Loading of single structure row not allowed for front model
	 */
	public function load($id = null, $force = false)
	{
		return $this;
	}
}

class e_user_extended_structure_tree extends e_tree_model
{
	/**
	 * @see e_model
	 * @var string
	 */
	protected $_db_table = 'user_extended_struct';

	/**
	 * @see e_model
	 * @var string
	 */
	protected $_field_id = 'user_extended_struct_id';

	/**
	 * @see e_model
	 * @var string
	 */
	protected $_message_stack = 'user';

	/**
	 * @var string
	 */
	protected $_cache_string = 'nomd5_user_extended_struct';

	/**
	 * Force system cache (cache used even if disabled by site admin)
	 * @var boolen
	 */
	protected $_cache_force = true;

	/**
	 * Index for speed up retrieving by name routine
	 * @var array
	 */
	protected $_name_index = array();

	/**
	 * Category Index - numerical array of id's
	 * @var array
	 */
	protected $_category_index = array();

	/**
	 * Items by category list
	 * @var array
	 */
	protected $_parent_index = array();

	/**
	 * Constructor - auto-load
	 * @return void
	 */
	public function __construct()
	{
		$this->load();
	}

	/**
	 * @param string $name name field value
	 * @return e_user_extended_structure_model
	 */
	public function getNodeByName($name)
	{
		if ($this->isNodeName($name))
		{
			return $this->getNode($this->getNodeId($name));
		}
		return null;
	}

	/**
	 * Check if node exists by its name field value
	 * @param string $name
	 * @return boolean
	 */
	public function isNodeName($name)
	{
		return (isset($this->_name_index[$name]) && $this->isNode($this->_name_index[$name]));
	}

	/**
	 * Get node ID by node name field
	 * @param string $name
	 * @return integer
	 */
	public function getNodeId($name)
	{
		return (isset($this->_name_index[$name]) ? $this->_name_index[$name] : null);
	}

	/**
	 * Get collection of nodes of type category
	 * @return array
	 */
	public function getCategoryTree()
	{
		return $this->_array_intersect_key($this->getTree(), array_combine($this->_category_index, $this->_category_index));
	}

	/**
	 * Get collection of nodes of type field
	 * @return array
	 */
	public function getFieldTree()
	{
		return array_diff_key($this->getTree(), array_combine($this->_category_index, $this->_category_index));
	}

	/**
	 * Get collection of nodes assigned to a specific category
	 * @param integer $category_id
	 * @return array
	 */
	public function getTreeByCategory($category_id)
	{
		if(!isset($this->_parent_index[$category_id]) || empty($this->_parent_index[$category_id])) return array();
		return $this->_array_intersect_key($this->getTree(), array_combine($this->_parent_index[$category_id], $this->_parent_index[$category_id]));
	}

	/**
	 * Load tree data
	 *
	 * @param boolean $force
	 */
	public function load($force = false)
	{
		$this->setParam('nocount', true)
			->setParam('model_class', 'e_user_extended_structure_model')
			->setParam('db_order', 'user_extended_struct_order ASC');
		parent::load($force);

		return $this;
	}

	/**
	 * Build all indexes on load
	 * (New) This method is auto-triggered by core load() method
	 * @param e_user_extended_structure_model $model
	 */
	protected function _onLoad($model)
	{
		if($model->isCategory())
		{
			$this->_category_index[] = $model->getId();
		}
		else
		{
			$this->_name_index['user_'.$model->getValue('name')] = $model->getId();
			$this->_parent_index[$model->getCategoryId()][] = $model->getId();
		}
		return $this;
	}

	/**
	 * Compatibility - array_intersect_key() available since PHP 5.1
	 *
	 * @see http://php.net/manual/en/function.array-intersect-key.php
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	protected function _array_intersect_key($array1, $array2)
	{
		if(function_exists('array_intersect_key')) return array_intersect_key($array1, $array2);

		$ret = array();
		foreach ($array1 as $k => $v)
		{
			if(isset($array2[$k])) $ret[$k] = $v;
		}
		return $ret;
	}
}

class e_user_pref extends e_front_model
{
	/**
	 * @var e_user_model
	 */
	protected $_user;

	/**
	 * Constructor
	 * @param e_user_model $user_model
	 * @return void
	 */
	public function __construct(e_user_model $user_model)
	{
		$this->_user = $user_model;
		$this->load();
	}

	/**
	 * Load data from user preferences string
	 * @param boolean $force
	 * @return e_user_pref
	 */
	public function load($id = null, $force = false)
	{
		if($force || !$this->hasData())
		{
			$data = $this->_user->get('user_prefs', '');
			if(!empty($data))
			{
				// BC
				$data = substr($data, 0, 5) == "array" ? e107::unserialize($data) : unserialize($data);
				if(!$data) $data = array();
			}
			else $data = array();

			$this->setData($data);
		}
		return $this;
	}

	/**
	 * Apply current data to user data
	 * @return e_user_pref
	 */
	public function apply()
	{
		$data = $this->hasData() ? $this->toString(true) : '';
		$this->_user->set('user_prefs', $data);
		return $this;
	}

	/**
	 * Save and apply user preferences
	 * @param boolean $from_post
	 * @param boolean $force
	 * @return boolean success
	 */
	public function save($from_post = false, $force = false, $session_messages = false)
	{
		if($this->_user->getId())
		{
			if($from_post)
			{
				$this->mergePostedData(false, true, false);
			}
			if($force || $this->dataHasChanged())
			{
				$data = $this->toString(true);
				$this->apply();
				return (e107::getDb('user_prefs')->db_Update('user', "user_prefs='{$data}' WHERE user_id=".$this->_user->getId()) ? true : false);
			}
			return 0;
		}
		return false;
	}

	/**
	 * Remove & apply user preferences, optionally - save to DB
	 * @return boolean success
	 */
	public function delete($ids, $destroy = true, $session_messages = false) // replaced $save = false for PHP7 fix.
	{
		$this->removeData()->apply();
	//	if($save) return $this->save(); //FIXME adjust within the context of the variables in the method.
		return true;
	}
}
