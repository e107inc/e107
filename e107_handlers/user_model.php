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
 * @category user
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
		'user_password' => array('string', '5', 'LAN_USER_05', 'LAN_USER_HELP_05'), // TODO - pref - modify it somewhere below
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
	 * User model of current editor
	 * @var e_user_model
	 */
	protected $_editor = null;

	/**
	 * Always return integer
	 *
	 * @see e107_handlers/e_model#getId()
	 */
	public function getId()
	{
		return (integer) parent::getId();
	}

	final public function getAdminId()
	{
		return ($this->isAdmin() ? $this->getId() : false);
	}

	final public function getAdminName()
	{
		return ($this->isAdmin() ? $this->getValue('name') : '');
	}

	final public function getAdminEmail()
	{
		return ($this->isAdmin() ? $this->getValue('email') : '');
	}

	final public function getAdminPwchange()
	{
		return ($this->isAdmin() ? $this->getValue('pwchange') : '');
	}

	final public function getAdminPerms()
	{
		return $this->getValue('perms');
	}

	public function isCurrent()
	{
		return false;
	}

	final public function isAdmin()
	{
		return ($this->getValue('admin') ? true : false);
	}

	final public function isMainAdmin()
	{
		return $this->checkAdminPerms('0');
	}

	final public function isUser()
	{
		return ($this->getId() ? true : false);
	}

	public function hasEditor()
	{
		return null !== $this->_editor;
	}

	final protected function _setClassList($uid = '')
	{
		$this->_class_list = array();
		if ($this->isUser())
		{
			if ($this->getValue('class'))
			{
				$this->_class_list = explode(',', $this->getValue('class'));
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
	 * Get User value
	 *
	 * @param string$field
	 * @param string $default
	 * @return mixed
	 */
	public function getValue($field, $default = '')
	{
		$field = 'user_'.$field;
		return $this->get($field, $default);
	}

	/**
	 * Set User value
	 * @param string $field
	 * @param mixed $value
	 * @return e_user_model
	 */
	public function setValue($field, $value)
	{
		$field = 'user_'.$field;
		$this->set($field, $value, true);
		return $this;
	}

	/**
	 * Get User extended value
	 *
	 * @param string$field
	 * @param string $default
	 * @return mixed
	 */
	public function getExtended($field)
	{
		return $this->getExtendedModel()->getValue($field);
	}

	/**
	 * Set User extended value
	 *
	 * @param string $field
	 * @param mixed $value
	 * @return e_user_model
	 */
	public function setExtended($field, $value)
	{
		$this->getExtendedModel()->setValue($field, $value);
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
	 * Get current user editor model
	 * @return e_user_model
	 */
	public function getEditor()
	{
		return $this->_editor;
	}

	/**
	 * Get current user editor model
	 * @return e_user_model
	 */
	public function setEditor($user_model)
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
		if (!is_string($field))
			return true;
		return !in_array($field, array($this->getFieldIdName(), 'user_admin', 'user_perms'));
	}

	/**
	 * Set current object as a target
	 *
	 * @return e_user_model
	 */
	protected function setAsTarget()
	{
		e107::setRegistry('targets/core/user/'.$this->getId(), $this);
		return $this;
	}

	/**
	 * Clear registered target
	 *
	 * @return e_user_model
	 */
	protected function clearTarget()
	{
		e107::setRegistry('targets/core/user'.$this->getId(), null);
		return $this;
	}

	/**
	 * @see e_model#load($id, $force)
	 */
	public function load($user_id = 0, $force = false)
	{
		parent::load($user_id, $force);
		if ($this->getId())
		{
			// no errors - register
			$this->setAsTarget()
				->setEditor(e107::getUser()); //set current user as default editor
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

		// TODO - do the save manual in this order: validate() on user model, save() on extended fields, save() on user model
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
		if (null !== $this->_extended_model)
		{
			$this->_extended_model->destroy();
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
			$this->setEditor(e107::getUser());
		}
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
 * Current system user - additional data protection is required
 * @author SecretR
 */
class e_user extends e_user_model
{
	public function __construct()
	{
		// reference to self
		$this->load()->setEditor($this);
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

	// TODO login by name/password, load, set cookie/session data
	final public function login($uname, $upass_plain, $uauto = false, $uchallange = false)
	{
		// FIXME - rewrite userlogin - clean up redirects and
		//$userlogin = new userlogin($uname, $upass_plain, $uauto, $uchallange);
		// if($userlogin->getId()) $this->load() --> use the previously set user COOKIE/SESSION data
		return $this->isUser();
	}

	/**
	 *
	 * @return unknown_type
	 */
	protected function initConstants()
	{
		//FIXME - BC - constants from init_session() should be defined here
		//init_session(); // the old way
	}

	/**
	 * TODO destroy cookie/session data, self destroy
	 * @return void
	 */
	final public function logout()
	{
		// FIXME - destoy cookie/session data first
		$this->_data = array();
		if (null !== $this->_extended_model)
		{
			$this->_extended_model->destroy();
		}
		e107::setRegistry('targets/core/current_user', null);
	}

	/**
	 * TODO load user data by cookie/session data
	 * @return e_user
	 */
	final public function load($force = false)
	{
		// init_session() should come here
		// $this->initConstants(); - called after data is loaded

		// FIXME - temporary here, for testing only!!!

		if (USER)
			$this->setData(get_user_data(USERID));
		return $this;
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
	 * @var e_user_extended_structure_tree
	 */
	protected $_structure = null;

	/**
	 * User model, the owner of extended fields model
	 * @var e_user_model
	 */
	protected $_user = null;

	/**
	 * User model
	 * @var e_user_model
	 */
	protected $_editor = null;

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
		$this->setUser($user_model)
			->setEditor(e107::getUser()) // current by default
				->load();
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
		return $this->_editor;
	}

	/**
	 * Get current user editor model
	 * @return e_user_model
	 */
	public function setEditor($user_model)
	{
		$this->_editor = $user_model;
		return $this;
	}

	/**
	 * Get User extended field value
	 * Returns NULL when field/default value not found or not enough permissions
	 * @param string$field
	 * @return mixed
	 */
	public function getValue($field)
	{
		$field = 'user_'.$field;
		if (!$this->checkRead($field))
			return null;
		return $this->get($field, $this->getDefault($field));
	}

	/**
	 * Set User extended field value, only if current editor has write permissions
	 * Note: Data is not sanitized!
	 * @param string $field
	 * @param mixed $value
	 * @return e_user_extended_model
	 */
	public function setValue($field, $value)
	{
		$field = 'user_'.$field;
		if (!$this->checkWrite($field))
			return $this;
		$this->set($field, $value, true);
		return $this;
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
		return $this->getEditor()->checkClass(varset($this->_struct_index[$field]['read']));
	}

	/**
	 * Check field write permissions
	 * @param string $field
	 * @return boolean
	 */
	public function checkWrite($field)
	{
		return $this->getEditor()->checkClass(varset($this->_struct_index[$field]['write']));
	}

	/**
	 * Check field signup permissions
	 * @param string $field
	 * @return boolean
	 */
	public function checkSignup($field)
	{
		return $this->getEditor()->checkClass(varset($this->_struct_index[$field]['signup']));
	}

	/**
	 * Check field applicable permissions
	 * @param string $field
	 * @return boolean
	 */
	public function checkApplicable($field)
	{
		return $this->getEditor()->checkClass(varset($this->_struct_index[$field]['applicable']));
	}

	/**
	 * @see e_model#load($id, $force)
	 * @return e_user_extended_model
	 */
	public function load($force = false)
	{
		if ($this->getId() && !$force)
			return $this;

		parent::load($this->getUser()->getId(), $force);
		$this->_loadAccess();
		return $this;
	}

	/**
	 * Load extended fields permissions once (performance)
	 * @return e_user_extended_model
	 */
	protected function _loadAccess()
	{
		$struct_tree = $this->getExtendedStructure();
		if (/*$this->getId() && */$struct_tree->hasTree())
		{
			// load structure dependencies
			$ignore = array($this->getFieldIdName(), 'user_hidden_fields'); // TODO - user_hidden_fields? Old?
			$fields = $struct_tree->getTree();
			foreach ($fields as $id => $field)
			{
				if (!in_array($field->getValue('name'), $ignore))
				{
					$this->_struct_index['user_'.$field->getValue('name')] = array(
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
	 * Build data types and rules on the fly and save
	 * @see e107_handlers/e_front_model#save($from_post, $force, $session_messages)
	 */
	public function save($force = false, $session = false)
	{
		$this->_buildManageRules();
		return parent::save(true, $force, $session);
	}

	public function saveDebug($retrun = false, $undo = true)
	{
		$this->_buildManageRules();
		parent::saveDebug($return, $undo);
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
	 * Force system cache (cache used even if disabled by site admin)
	 * @var boolen
	 */
	protected $_name_index = true;

	/**
	 * Constructor - auto-load
	 * @return void
	 */
	public function __construct()
	{
		$this->load();
	}

	public function getNodeByName($name)
	{
		if ($this->isNodeName($name))
		{
			return $this->getNode($this->getNodeId($name));
		}
		return null;
	}

	public function isNodeName($name)
	{
		return (isset($this->_name_index[$name]) && $this->isNode($this->_name_index[$name]));
	}

	public function getNodeId($name)
	{
		return $this->_name_index[$name];
	}

	/**
	 * Load tree data
	 *
	 * @param boolean $force
	 */
	public function load($force = false)
	{
		$this->setParam('nocount', true)
			->setParam('model_class', 'e_user_extended_structure_model');
		parent::load($force);

		return $this;
	}

	/**
	 * Build name index on load
	 * @param e_user_extended_structure_model $model
	 */
	protected function _onLoad($model)
	{
		$this->_name_index['user_'.$model->getValue('name')] = $model->getId();
		return $this;
	}
}
