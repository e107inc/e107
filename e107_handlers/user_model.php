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
	 * Get User value
	 *
	 * @param string$field
	 * @param string $default
	 * @return mixed
	 */
	public function getValue($field, $default)
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
	 * Set current object as a target
	 * @return e_user_model
	 */
	public function setAsTarget()
	{
		e107::setRegistry('targets/core/user', $this);
		return $this;
	}

	/**
	 * Clear registered target
	 * @return e_user_model
	 */
	public function clearTarget()
	{
		e107::setRegistry('targets/core/user', null);
		return $this;
	}

	/**
	 * @see e_model#load($id, $force)
	 */
	public function load($user_id = 0, $force = false)
	{
		parent::load($user_id, $force);
	}
}


// TODO Current user model is under construction
class e_current_user extends e_user_model
{

}

// TODO - add some more useful methods, sc_* methods
class e_user extends e_user_model
{

}