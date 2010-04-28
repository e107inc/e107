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
  * @subpackage user
  * @version $Id$
  *
  * User Model
  */

if (!defined('e107_INIT')) { exit; }

class e_user_model extends e_model
{
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
	 * @var e_user_extended_strcuture
	 */
	protected $_extended_strcuture = null;

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
		$this->set($field, $value, false);
		return $this;
	}
	
	/**
	 * Get User extended value
	 *
	 * @param string$field
	 * @param string $default
	 * @return mixed
	 */
	public function getExtendedValue($field, $default = '')
	{
		return $this->getExtendedModel()->getValue($field, $default);
	}

	/**
	 * Set User extended value
	 * 
	 * @param string $field
	 * @param mixed $value
	 * @return e_user_model
	 */
	public function setExtendedValue($field, $value)
	{
		$this->getExtendedModel()->setValue($field, $value);
		return $this;
	}
	
	/**
	 * Get user extended model
	 * 
	 * @return e_user_extended_model
	 */
	public function getExtended()
	{
		if(null === $this->_extended_model)
		{
			$this->_extended_model = new e_user_extended_model();
			$this->_extended_model->load($this->getId());
		}
		return $this->_extended_model;
	}
	
	/**
	 * Set user extended model
	 * 
	 * @param e_user_extended_model $extended_model
	 * @return e_user_model
	 */
	public function setExtended($extended_model)
	{
		$this->_extended_model = $extended_model;
		return $this;
	}
	
	/**
	 * Get extended structure tree
	 * 
	 * @return e_user_extended_strcuture_tree
	 */
	public function getExtendedStructure()
	{
		return e107::getExtendedStructure();
	}

	/**
	 * Set current object as a target
	 * 
	 * @return e_user_model
	 */
	public function setAsTarget()
	{
		e107::setRegistry('targets/core/user/'.$this->getId() , $this);
		return $this;
	}

	/**
	 * Clear registered target
	 * 
	 * @return e_user_model
	 */
	public function clearTarget()
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
		if($this->getId())
		{
			// no errors - register
			$this->setAsTarget();
		}
	}
	
	public function destroy()
	{
		$this->clearTarget()
			->removeData();
		if(null !== $this->_extended_model)
		{
			$this->_extended_model->destroy();
		}
	}
}


// TODO Current user model is under construction
class e_current_user extends e_user_model
{
	final public function isCurrent()
	{
		return true;
	}
}

// TODO - add some more useful methods, sc_* methods support
class e_user extends e_user_model
{
	final public function isCurrent()
	{
		// FIXME - check against current system user
		return ($this->getId() && $this->getId() === USERID);
	}
}

class e_user_extended_model extends e_model
{
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
	 * Get User extended field value
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
	 * Set User extended field value
	 * 
	 * @param string $field
	 * @param mixed $value
	 * @return e_user_model
	 */
	public function setValue($field, $value)
	{
		$field = 'user_'.$field;
		$this->set($field, $value, false);
		return $this;
	}
	
	/**
	 * Get extended structure tree
	 * 
	 * @return e_user_extended_strcuture_tree
	 */
	public function getExtendedStructure()
	{
		return e107::getExtendedStructure();
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
	protected $_message_stack = 'user';
	
	/**
	 * Get User extended structure field value
	 *
	 * @param string$field
	 * @param string $default
	 * @return mixed
	 */
	public function getValue($field, $default = '')
	{
		$field = 'user_extended_struct'.$field;
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
		$field = 'user_extended_struct'.$field;
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

class e_user_extended_strcuture_tree extends e_tree_model
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
	protected $_cache_string = 'user';
	
	/**
	 * Force system cache
	 * @var boolen
	 */
	protected $_cache_force = true;
	
	/**
	 * Load tree data
	 * 
	 * @see e107_handlers/e_tree_model#load($force)
	 */
	public function load($force = false)
	{
		$this->setParam('nocount', true)
			->setParam('model_class', 'e_user_extended_structure_model');
		parent::load($force);
			
		
		return $this;
	}
}