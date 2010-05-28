<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
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

class e_user_model extends e_front_model
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
		'user_password' => array('compare', '5', 'LAN_USER_05', 'LAN_USER_HELP_05'), // TODO - pref - modify it somewhere below - prepare_rules()?
		'user_email' => array('email', '', 'LAN_USER_08', 'LAN_USER_HELP_08'),
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

	final public function getName($anon = false)
	{
		return ($this->isUser() ? $this->get('user_name') : $anon);
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

	public function getToken()
	{
		if($this->isUser()) return '';

		if(null === $this->get('user_token'))
		{
			$this->set('user_token', md5($this->get('user_password').$this->get('user_lastvisit').$this->get('user_pwchange').$this->get('user_class')));
		}
		return $this->get('user_token');
	}

	public function isCurrent()
	{
		return false;
	}

	final public function isAdmin()
	{
		return ($this->get('user_admin') ? true : false);
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
		$qry = "SELECT u.*, ue.* FROM #user AS u LEFT JOIN #user_extended as ue ON u.user_id=ue.user_extended_id WHERE user_id={ID}";
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
	public function mergePostedData()
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
	public function save($force = false, $session = false)
	{
		if (!$this->checkEditorPerms())
		{
			return false; // TODO - message, admin log
		}

		// sync user prefs
		$this->getConfig()->apply();

		// TODO - do the save manually in this order: validate() on user model, save() on extended fields, save() on user model
		$ret = parent::save(true, $force, $session);
		if(false !== $ret && null !== $this->_extended_model) // don't load extended fields if not already used
		{
			$ret_e = $this->_extended_model->save($force, $session);
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
}

// TODO - add some more useful methods, sc_* methods support
class e_system_user extends e_user_model
{
	/**
	 * Constructor
	 *
	 * @param array $user_data trusted data, loaded from DB
	 * @return void
	 */
	public function __construct($user_data = array())
	{
		if ($user_data)
		{
			$this->_data = $user_data;
		}
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

		$userlogin = new userlogin($uname, $upass_plain, $uauto, $uchallange, $noredirect);
		$this->setSessionData(true)
			->setData($userlogin->getUserData());

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
		$this->logoutAs()
			->_destroySession();

		parent::destroy();
		if(session_id()) session_destroy();

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
			$this->setData($this->_parent_model->getData());

			// cleanup
			$this->_parent_id = false;
			$this->_parent_model = $this->_parent_extstruct = $this->_parent_extmodel = $this->_parent_config = null;
		}
		$this->_destroyAsSession();
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
			$current_ip = e107::getInstance()->getip();
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
		$_SESSION[$this->_session_key] = '';

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
		$qry = 'SELECT u.*, ue.* FROM #user AS u LEFT JOIN #user_extended as ue ON u.user_id=ue.user_extended_id WHERE user_id='.intval($user_id);
		if(e107::getDb()->db_Select_gen($qry))
		{
			return e107::getDb()->db_Fetch();
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

class e_user_extended_model extends e_front_model
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
	 * @param $user_model
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
	public function load($force = false)
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
		$this->setValidationRule($structure_model->getValue('name'), array($vtype, $parms[1], $structure_model->getValue('text'), $parms[2]), $structure_model->getValue('required'));

		// data type, required for sql query
		$this->_data_fields[$structure_model->getValue('name')] = $ftype;
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
				if (!in_array($field->getValue('name'), $ignore))
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
	public function mergePostedData()
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
	public function save($force = false, $session = false)
	{
		$this->_buildManageRules();
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
	public function load()
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
	public function load($force = false)
	{
		if($force || !$this->hasData())
		{
			$data = $this->_user->get('user_prefs', '');
			if(!empty($data))
			{
				// BC
				$data = substr($data, 0, 5) == "array" ? e107::getArrayStorage()->ReadArray($data) : unserialize($data);
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
		$this->_user->set('user_prefs', $this->toString(true));
		return $this;
	}

	/**
	 * Save and apply user preferences
	 * @param boolean $from_post
	 * @param boolean $force
	 * @return boolean success
	 */
	public function save($from_post = false, $force = false)
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
	 * Remove & apply user prefeferences, optionally - save to DB
	 * @return boolean success
	 */
	public function delete($save = false)
	{
		$this->removeData()->apply();
		if($save) return $this->save();
		return true;
	}
}
