<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * List Shortcodes
 *
*/

/**
 *	Shortcodes for list_new plugin
 *
 *	@package e107_plugins
 *	@subpackage list_new
 */

if (!defined('e107_INIT')) { exit; }

//register_shortcode('list_shortcodes', true);
//initShortcodeClass('list_shortcodes');

class list_shortcodes extends e_shortcode
{
	var $rc; // list class.
	var $e107;
	var $row;
	var $list_pref = array();
	public $plugin;

/*
	function load_globals()
	{
		global $rc;
		$e107 = e107::getInstance();
//		$tp->e_sc->scClasses['list_shortcodes']->rc = $rc;
//		$tp->e_sc->scClasses['list_shortcodes']->row = $rc->row;
//		$tp->e_sc->scClasses['list_shortcodes']->list_pref = $rc->list_pref;
	}
*/


	function sc_list_css_id()
	{
		return eHelper::title2sef('list-new-'.$this->plugin, 'dashl');
	}

	function sc_list_date()
	{
		return e107::getParser()->toHTML($this->row['date'], true, "");
	}

	function sc_list_icon()
	{
		return e107::getParser()->toHTML($this->row['icon'], true, "");
	}

	function sc_list_heading()
	{
		if(empty($this->row['heading']))
		{
			return null;
		}

		return e107::getParser()->toHTML($this->row['heading'], true, "TITLE");
	}

	function sc_list_author()
	{
		return e107::getParser()->toHTML($this->row['author'], true, "");
	}

	function sc_list_category()
	{
		if(empty($this->row['category']))
		{
			return null;
		}

		return e107::getParser()->toHTML($this->row['category'], true, "");
	}

	function sc_list_info()
	{
		if(empty($this->row['info']))
		{
			return null;
		}

		return e107::getParser()->toHTML($this->row['info'], true, "");
	}

	function sc_list_caption()
	{
		if(empty($this->rc->data) || empty($this->rc->data['caption']))
		{
			return null;
		}

		return e107::getParser()->toHTML($this->rc->data['caption'], true, "");
	}

	function sc_list_displaystyle()
	{
		//open sections if content exists ? yes if true, else use individual setting of section
		$mode = $this->rc->mode."_openifrecords";
		return (!empty($this->list_pref[$mode]) && isset($this->rc->data['records']) &&  is_array($this->rc->data['records'])) ? "" : varset($this->rc->data['display']);
	}

	function sc_list_col_cols()
	{
		if(empty($this->list_pref[$this->rc->mode."_colomn"]))
		{
			return null;
		}

		return $this->list_pref[$this->rc->mode."_colomn"];
	}

	function sc_list_col_welcometext()
	{
		if(empty($this->list_pref[$this->rc->mode."_welcometext"]))
		{
			return null;
		}

		return e107::getParser()->toHTML($this->list_pref[$this->rc->mode."_welcometext"], true, "");
	}

	function sc_list_col_cellwidth()
	{
		if(empty($this->list_pref[$this->rc->mode."_colomn"]))
		{
			return 25;
		}

		return round((100/$this->list_pref[$this->rc->mode."_colomn"]),0);
	}

	function sc_list_timelapse()
	{
		return varset($this->row['timelapse']);
	}
}
