<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Tagwords Event Handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/tagwords/e_event.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

if (!defined('e107_INIT')) { exit; }

if(!defined("TAG_TEXTAREA_COLS")){ define("TAG_TEXTAREA_COLS", "70"); }
if(!defined("TAG_TEXTAREA_ROWS")){ define("TAG_TEXTAREA_ROWS", "4"); }

class e_event_tagwords
{
	/*
	* all event methods have a single parameter
	* @param array $data array containing
	*	@param string $method form,insert,update,delete
	*	@param string $table the table name of the calling plugin
	*	@param int $id item id of the record
	*	@param string $plugin identifier for the calling plugin
	*	@param string $function identifier for the calling function
	*/

	/*
	* constructor
	*/
	function e_event_tagwords()
	{
		global $tag;
		require_once(e_PLUGIN."tagwords/tagwords_class.php");
		$this->tag = new tagwords();
		$tag = $this->tag;
	}

	/*
	* add form field
	* @param array $data
	*/
	function event_form($data)
	{
		return $this->tag->tagwords_form($data['table'], intval($data['id']));
	}

	/*
	* handle db create
	* @param array $data
	*/
	function event_create($data)
	{
		if(isset($_POST['tagwords']))
		{
			return $this->tag->dbTagWords($data['table'], intval($data['id']), $_POST['tagwords']);
		}
	}

	/*
	* handle db update
	* @param array $data
	*/
	function event_update($data)
	{
		if(isset($_POST['tagwords']))
		{
			return $this->tag->dbTagWords($data['table'], intval($data['id']), $_POST['tagwords']);
		}
	}

	/*
	* handle db delete
	* @param array $data
	*/
	function event_delete($data)
	{
		return $this->tag->dbDelete($data['table'], intval($data['id']));
	}

} //end class

?>