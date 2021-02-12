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

// v2.x Standard 
class _blank_user // plugin-folder + '_user'
{		
		
	function profile($udata)  // display on user profile page.
	{

		$var = array(
			0 => array('label' => "Label", 'text' => "Some text to display", 'url'=> e_PLUGIN_ABS."_blank/blank.php")
		);
		
		return $var;
	}

	/**
	 * The same field format as admin-ui, with the addition of 'fieldType', 'read', 'write', 'appliable' and 'required' as used in extended fields table.
	 *
	 * @return array
	 */
	function settings()
	{
		$fields = array();
		$fields['field1'] = array('title' => "Field 1",  'fieldType' => 'varchar(30)',  'read'=> e_UC_ADMIN, 'write'=>e_UC_MEMBER, 'type' => 'text', 'writeParms' => array('size' => 'xxlarge'));
		$fields['field2'] = array('title' => "Field 2",  'fieldType' => 'int(2)',       'type' => 'number', 'data'=>'int');
		$fields['field3'] = array('title' => "Field 3",  'fieldType' => 'int(1)',       'type' => 'method', 'data'=>'str', 'required'=>true); // see below.

        return $fields;

	}


	/**
	 * Experimental and subject to change without notice.
	 * @return mixed
	 */
	function delete()
	{

		$config['user'] =  array(
			'user_id'           => '[primary]',
			'user_name'         => '[unique]',
			'user_loginname'    => '[unique]',
			'user_email'        => '[unique]',
			'user_ip'           => '',
			// etc.
			'WHERE'             => 'user_id = '.USERID,
			'MODE'              => 'update'
		);

		$config['user_extended'] = array(
			'WHERE'             => 'user_extended_id = '.USERID,
			'MODE'              => 'delete'
		);

		return $config;

	}


	
}

// (plugin-folder)_user_form - only required when using custom methods.
class _blank_user_form extends e_form
{
	// user_plugin_(plugin-folder)_(fieldname)
	public function user_plugin__blank_field3($curVal, $mode, $att=array())
	{
		$opts = array(1,2,3,4);
		return $this->select('user_plugin__blank_field3', $opts, $curVal);
	}


}