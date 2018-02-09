<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * List Admin Class
 *
*/
if (!defined('e107_INIT')) { exit; }

/**
 *	Core class for list_new plugin admin
 *
 *	@package e107_plugins
 *	@subpackage list_new
 */
class list_admin
{
	var $row;

	/**
	 * constructor
	 * 
	 * @param object $parent the parent object
	 * @return void
	 * 
	 */
	function list_admin($parent)
	{
		$this->e107 = e107::getInstance();
		$this->parent = $parent;
	}

	/**
	 * database update settings
	 * 
	 * @return string $message
	 * 
	 */
	function db_update_menu()
	{
	//	$sql = e107::getDb();
	//	$tp = e107::getParser();
		// Get the preferences so we've got a reference for changes
	//	$list_pref = $this->parent->getListPrefs();
	//	$temp = array();
	//	while(list($key, $value) = each($_POST))
	//	{
	//		if($value != LIST_ADMIN_2){ $temp[$tp->toDB($key)] = $tp->toDB($value); }
	//	}
		
		e107::getPlugConfig('list_new')->reset()->setPref($_POST)->save(true);

	//	retrieve with e107::pref('list_new');
		
		return;
		
		/*
		if ($this->e107->admin_log->logArrayDiffs($temp, $list_pref, 'LISTNEW_01'))
		{
			$tmp = $this->e107->arrayStorage->WriteArray($list_pref);
			$sql->update("core", "e107_value='{$tmp}' WHERE e107_name='list' ");
			$message = LIST_ADMIN_3;
		}
		else
		{
			$message = LIST_ADMIN_17;
		}
		return $message;
		 */
	}

	/**
	 * display the admin configuration page
	 * 
	 * @return string
	 * 
	 */
	function display()
	{
		global $rs;

		$text = $this->parseTemplate('ADMIN_START');

		$text .= $this->parse_menu_options("recent_menu");
		$text .= $this->parse_menu_options("new_menu");
		$text .= $this->parse_page_options("recent_page");
		$text .= $this->parse_page_options("new_page");

		$text .= $this->parseTemplate('ADMIN_END');

		return $text;
	}

	/**
	 * display global options
	 * 
	 * @param string $type the area to display
	 * @return string
	 * 
	 */
	function parse_global_options($type)
	{
		global $rs;
		$fl = e107::getFile();

		$text = '';

		//show sections
		$this->row['TOPIC'] = LIST_ADMIN_SECT_1;
		$this->row['HEADING'] = LIST_ADMIN_SECT_2;
		$this->row['HELP'] = LIST_ADMIN_SECT_3;
		$this->row['CONTID'] = "list-new-{$type}-expandable-sections";
		$this->row['FIELD'] = "";
		for($i=0;$i<count($this->parent->sections);$i++)
		{
			$this->row['FIELD'] .= $rs->form_checkbox($this->parent->sections[$i]."_".$type."_display", 1, (vartrue($this->parent->list_pref[$this->parent->sections[$i]."_".$type."_display"]) ? "1" : "0"))." ".$this->parent->titles[$i]."<br />";
		}
		$text .= $this->parseTemplate('TOPIC_ROW');

		//open or closed
		$this->row['TOPIC'] = LIST_ADMIN_SECT_4;
		$this->row['HEADING'] = LIST_ADMIN_SECT_5;
		$this->row['HELP'] = LIST_ADMIN_SECT_6;
		$this->row['CONTID'] = "list-new-{$type}-expandable-display-style";
		$this->row['FIELD'] = "";
		for($i=0;$i<count($this->parent->sections);$i++)
		{
			$this->row['FIELD'] .= $rs->form_checkbox($this->parent->sections[$i]."_".$type."_open", 1, (vartrue($this->parent->list_pref[$this->parent->sections[$i]."_".$type."_open"]) ? "1" : "0"))." ".$this->parent->titles[$i]."<br />";
		}
		$text .= $this->parseTemplate('TOPIC_ROW');

		//author
		$this->row['TOPIC'] = LIST_ADMIN_SECT_7;
		$this->row['HEADING'] = LIST_ADMIN_SECT_8;
		$this->row['HELP'] = LIST_ADMIN_SECT_9;
		$this->row['CONTID'] = "list-new-{$type}-expandable-author";
		$this->row['FIELD'] = "";
		for($i=0;$i<count($this->parent->sections);$i++)
		{
			$this->row['FIELD'] .= $rs->form_checkbox($this->parent->sections[$i]."_".$type."_author", 1, (vartrue($this->parent->list_pref[$this->parent->sections[$i]."_".$type."_author"]) ? "1" : "0"))." ".$this->parent->titles[$i]."<br />";
		}
		$text .= $this->parseTemplate('TOPIC_ROW');

		//category
		$this->row['TOPIC'] = LIST_ADMIN_SECT_10;
		$this->row['HEADING'] = LIST_ADMIN_SECT_11;
		$this->row['HELP'] = LIST_ADMIN_SECT_12;
		$this->row['FIELD'] = "";
		$this->row['CONTID'] = "list-new-{$type}-expandable-category";
		for($i=0;$i<count($this->parent->sections);$i++)
		{
			$this->row['FIELD'] .= $rs->form_checkbox($this->parent->sections[$i]."_".$type."_category", 1, (vartrue($this->parent->list_pref[$this->parent->sections[$i]."_".$type."_category"]) ? "1" : "0"))." ".$this->parent->titles[$i]."<br />";
		}
		$text .= $this->parseTemplate('TOPIC_ROW');

		//date
		$this->row['TOPIC'] = LIST_ADMIN_SECT_13;
		$this->row['HEADING'] = LIST_ADMIN_SECT_14;
		$this->row['HELP'] = LIST_ADMIN_SECT_15;
		$this->row['FIELD'] = "";
		$this->row['CONTID'] = "list-new-{$type}-expandable-date";
		for($i=0;$i<count($this->parent->sections);$i++)
		{
			$this->row['FIELD'] .= $rs->form_checkbox($this->parent->sections[$i]."_".$type."_date", 1, (vartrue($this->parent->list_pref[$this->parent->sections[$i]."_".$type."_date"]) ? "1" : "0"))." ".$this->parent->titles[$i]."<br />";
		}
		$text .= $this->parseTemplate('TOPIC_ROW');

		//icon
		$this->row['TOPIC'] = LIST_ADMIN_SECT_22;
		$this->row['HEADING'] = LIST_ADMIN_SECT_23;
		$this->row['HELP'] = LIST_ADMIN_SECT_24;
		$this->row['CONTID'] = "list-new-{$type}-expandable-icon";
		$this->row['FIELD'] = $this->parseTemplate('FIELD_TABLE_START');
		$iconlist = $fl->get_files($this->parent->plugin_dir."images/");
		$frm = e107::getForm();
		
		for($i=0;$i<count($this->parent->sections);$i++)
		{
			$name = $this->parent->sections[$i]."_".$type."_icon";
			$curVal = $this->parent->list_pref[$this->parent->sections[$i]."_".$type."_icon"];
			
			$this->row['FIELD_TITLE'] = $this->parent->titles[$i];
			$this->row['FIELD_ITEM'] = $frm->iconpicker($name,$curVal, LAN_SELECT);		// TODO: Is this a reasonable label to use? Might not be used
		//	$this->row['FIELD_ITEM'] = $frm->iconpicker($this->parent->sections[$i]."_".$type."_icon",$this->parent->list_pref[$this->parent->sections[$i]."_".$type."_icon"]).
		//	$this->row['FIELD_ITEM'] = $rs->form_text($this->parent->sections[$i]."_".$type."_icon", 15, $this->parent->list_pref[$this->parent->sections[$i]."_".$type."_icon"], 100)."
			
		//		<input  type='button' style='cursor:pointer' size='30' value='".LIST_ADMIN_12."' onclick=\"e107Helper.toggle('div_".$this->parent->sections[$i]."_".$type."_icon'); return false;\" />
		//	$this->row['FIELD_ITEM'] .= div id='div_".$this->parent->sections[$i]."_".$type."_icon' style='display:none;'>";
				
				
			//	foreach($iconlist as $icon)
		//		{
			//		$this->row['FIELD_ITEM'] .= "<a href=\"javascript:insertext('".$icon['fname']."','".$this->parent->sections[$i]."_".$type."_icon','div_".$this->parent->sections[$i]."_".$type."_icon')\"><img src='".$icon['path'].$icon['fname']."' alt='' /></a> ";
			//	}
		//		$this->row['FIELD_ITEM'] .= "</div>";
			$this->row['FIELD'] .= $this->parseTemplate('FIELD_TABLE');
		}

		$this->row['FIELD'] .= $this->parseTemplate('FIELD_TABLE_END');
		$text .= $this->parseTemplate('TOPIC_ROW');

		//amount
		$maxitems_amount = "50";
		$this->row['TOPIC'] = LIST_ADMIN_SECT_16;
		$this->row['HEADING'] = LIST_ADMIN_SECT_17;
		$this->row['HELP'] = LIST_ADMIN_SECT_18;
		$this->row['CONTID'] = "list-new-{$type}-expandable-amount";
		$this->row['FIELD'] = $this->parseTemplate('FIELD_TABLE_START');
		for($i=0;$i<count($this->parent->sections);$i++)
		{
			$this->row['FIELD_TITLE'] = $this->parent->titles[$i];
			$this->row['FIELD_ITEM'] = $rs->form_select_open($this->parent->sections[$i]."_".$type."_amount");
			for($a=1; $a<=$maxitems_amount; $a++)
			{
				$this->row['FIELD_ITEM'] .= ($this->parent->list_pref[$this->parent->sections[$i]."_".$type."_amount"] == $a ? $rs->form_option($a, 1, $a) : $rs->form_option($a, 0, $a));
			}
			$this->row['FIELD_ITEM'] .= $rs->form_select_close();
			$this->row['FIELD'] .= $this->parseTemplate('FIELD_TABLE');
		}
		$this->row['FIELD'] .= $this->parseTemplate('FIELD_TABLE_END');
		$text .= $this->parseTemplate('TOPIC_ROW');

		//order
		$max = count($this->parent->sections);
		$this->row['TOPIC'] = LIST_ADMIN_SECT_19;
		$this->row['HEADING'] = LIST_ADMIN_SECT_20;
		$this->row['HELP'] = LIST_ADMIN_SECT_21;
		$this->row['CONTID'] = "list-new-{$type}-expandable-order";
		$this->row['FIELD'] = $this->parseTemplate('FIELD_TABLE_START');
		for($i=0;$i<count($this->parent->sections);$i++)
		{
			$this->row['FIELD_TITLE'] = $this->parent->titles[$i];
			$this->row['FIELD_ITEM'] = $rs->form_select_open($this->parent->sections[$i]."_".$type."_order");
			for($a=1; $a<=$max; $a++)
			{
				$this->row['FIELD_ITEM'] .= ($this->parent->list_pref[$this->parent->sections[$i]."_".$type."_order"] == $a ? $rs->form_option($a, 1, $a) : $rs->form_option($a, 0, $a));
			}
			$this->row['FIELD_ITEM'] .= $rs->form_select_close();
			$this->row['FIELD'] .= $this->parseTemplate('FIELD_TABLE');
		}
		$this->row['FIELD'] .= $this->parseTemplate('FIELD_TABLE_END');
		$text .= $this->parseTemplate('TOPIC_ROW');

		//caption
		$this->row['TOPIC'] = LIST_ADMIN_SECT_25;
		$this->row['HEADING'] = LIST_ADMIN_SECT_26;
		$this->row['HELP'] = LIST_ADMIN_SECT_27;
		$this->row['CONTID'] = "list-new-{$type}-expandable-caption";
		$this->row['FIELD'] = $this->parseTemplate('FIELD_TABLE_START');
		for($i=0;$i<count($this->parent->sections);$i++)
		{
			$this->row['FIELD_TITLE'] = $this->parent->titles[$i];
			$this->row['FIELD_ITEM'] = $rs->form_text($this->parent->sections[$i]."_".$type."_caption", 30, e107::getParser()->toHTML($this->parent->list_pref[$this->parent->sections[$i]."_".$type."_caption"],"","defs"), "50", "tbox");
			$this->row['FIELD'] .= $this->parseTemplate('FIELD_TABLE');
		}
		$this->row['FIELD'] .= $this->parseTemplate('FIELD_TABLE_END');
		$text .= $this->parseTemplate('TOPIC_ROW');

		$text .= $this->parseTemplate('TOPIC_ROW_SPACER');

		return $text;
	}

	/**
	 * display menu options
	 * 
	 * @param string $type the area to display
	 * @return string
	 * 
	 */
	function parse_menu_options($type)
	{
		global $rs;
		$tp = e107::getParser();

		$this->row['ID'] = "list-new-".str_replace('_', '-', $type);
		$this->row['TITLE'] = ($type == "new_menu" ? LIST_ADMIN_OPT_5 : LIST_ADMIN_OPT_3);
		$text = $this->parseTemplate('OPTIONS_HEADER');

		$text .= $this->parse_global_options($type);

		//menu preference : caption
		$this->row['TOPIC'] = LIST_ADMIN_LAN_2;
		$this->row['HEADING'] = LIST_ADMIN_LAN_3;
		$this->row['HELP'] = LIST_ADMIN_LAN_4;
		$this->row['CONTID'] = "list-new-menu-{$type}-expandable-caption";
		$this->row['FIELD'] = $rs->form_text($type."_caption", "30", $tp->toHTML($this->parent->list_pref[$type."_caption"],"","defs"), "50", "tbox");
		$text .= $this->parseTemplate('TOPIC_ROW');

		//menu preference : icon : use
		$this->row['TOPIC'] = LIST_ADMIN_LAN_5;
		$this->row['HEADING'] = LIST_ADMIN_LAN_6;
		$this->row['HELP'] = LIST_ADMIN_LAN_7;
		$this->row['CONTID'] = "list-new-menu-{$type}-expandable-icon-use";
		$this->row['FIELD'] = "
			".$rs->form_radio($type."_icon_use", "1", ($this->parent->list_pref[$type."_icon_use"] ? "1" : "0"), "", "").LIST_ADMIN_7."
			".$rs->form_radio($type."_icon_use", "0", ($this->parent->list_pref[$type."_icon_use"] ? "0" : "1"), "", "").LIST_ADMIN_8."
		";
		$text .= $this->parseTemplate('TOPIC_ROW');

		//menu preference : icon : show default theme bullet
		$this->row['TOPIC'] = LIST_ADMIN_MENU_2;
		$this->row['HEADING'] = LIST_ADMIN_MENU_3;
		$this->row['HELP'] = LIST_ADMIN_MENU_4;
		$this->row['CONTID'] = "list-new-menu-{$type}-expandable-icon-show";
		$this->row['FIELD'] = "
			".$rs->form_radio($type."_icon_default", "1", ($this->parent->list_pref[$type."_icon_default"] ? "1" : "0"), "", "").LIST_ADMIN_7."
			".$rs->form_radio($type."_icon_default", "0", ($this->parent->list_pref[$type."_icon_default"] ? "0" : "1"), "", "").LIST_ADMIN_8."
		";
		$text .= $this->parseTemplate('TOPIC_ROW');

		//menu preference : amount chars
		$this->row['TOPIC'] = LIST_ADMIN_LAN_8;
		$this->row['HEADING'] = LIST_ADMIN_LAN_9;
		$this->row['HELP'] = LIST_ADMIN_LAN_10;
		$this->row['CONTID'] = "list-new-menu-{$type}-expandable-amount-chars";
		$this->row['FIELD'] = $rs->form_text($type."_char_heading", "3", $this->parent->list_pref[$type."_char_heading"], "3", "tbox");
		$text .= $this->parseTemplate('TOPIC_ROW');

		//menu preference : postfix
		$this->row['TOPIC'] = LIST_ADMIN_LAN_11;
		$this->row['HEADING'] = LIST_ADMIN_LAN_12;
		$this->row['HELP'] = LIST_ADMIN_LAN_13;
		$this->row['CONTID'] = "list-new-menu-{$type}-expandable-postfix";
		$this->row['FIELD'] = $rs->form_text($type."_char_postfix", "3", $this->parent->list_pref[$type."_char_postfix"], "3", "tbox");
		$text .= $this->parseTemplate('TOPIC_ROW');

		//menu preference : date
		$this->row['TOPIC'] = LIST_ADMIN_LAN_14;
		$this->row['HEADING'] = LIST_ADMIN_LAN_15;
		$this->row['HELP'] = LIST_ADMIN_LAN_16;
		$this->row['CONTID'] = "list-new-menu-{$type}-expandable-date";
		$this->row['FIELD'] = $rs->form_text($type."_datestyle", "30", $this->parent->list_pref[$type."_datestyle"], "50", "tbox");
		$text .= $this->parseTemplate('TOPIC_ROW');

		//menu preference : date today
		$this->row['TOPIC'] = LIST_ADMIN_LAN_17;
		$this->row['HEADING'] = LIST_ADMIN_LAN_18;
		$this->row['HELP'] = LIST_ADMIN_LAN_19;
		$this->row['CONTID'] = "list-new-menu-{$type}-expandable-datet";
		$this->row['FIELD'] = $rs->form_text($type."_datestyletoday", "30", $this->parent->list_pref[$type."_datestyletoday"], "50", "tbox");
		$text .= $this->parseTemplate('TOPIC_ROW');

		//menu preference : show empty
		$this->row['TOPIC'] = LIST_ADMIN_LAN_26;
		$this->row['HEADING'] = LIST_ADMIN_LAN_27;
		$this->row['HELP'] = LIST_ADMIN_LAN_28;
		$this->row['CONTID'] = "list-new-menu-{$type}-expandable-sempty";
		$this->row['FIELD'] = "
			".$rs->form_radio($type."_showempty", "1", ($this->parent->list_pref[$type."_showempty"] ? "1" : "0"), "", "").LIST_ADMIN_7."
			".$rs->form_radio($type."_showempty", "0", ($this->parent->list_pref[$type."_showempty"] ? "0" : "1"), "", "").LIST_ADMIN_8."
		";
		$text .= $this->parseTemplate('TOPIC_ROW');

		//menu preference : open section if content exists? this will override the individual setting of the section
		$this->row['TOPIC'] = LIST_ADMIN_LAN_39;
		$this->row['HEADING'] = LIST_ADMIN_LAN_40;
		$this->row['HELP'] = LIST_ADMIN_LAN_41;
		$this->row['CONTID'] = "list-new-menu-{$type}-expandable-osie";
		$this->row['FIELD'] = "
			".$rs->form_radio($type."_openifrecords", "1", ($this->parent->list_pref[$type."_openifrecords"] ? "1" : "0"), "", "").LIST_ADMIN_7."
			".$rs->form_radio($type."_openifrecords", "0", ($this->parent->list_pref[$type."_openifrecords"] ? "0" : "1"), "", "").LIST_ADMIN_8."
		";
		$text .= $this->parseTemplate('TOPIC_ROW');

		$text .= $this->parseTemplate('TOPIC_ROW_SPACER');
		$this->row['SUBMIT'] = $this->pref_submit();
		$text .= $this->parseTemplate('TOPIC_TABLE_END');
		return $text;
	}

	/**
	 * display page options
	 * 
	 * @param string $type the area to display
	 * @return string
	 * 
	 */
	function parse_page_options($type)
	{
		global $rs;
		$tp = e107::getParser();

		$display = ($type == "recent_page" ? "display:none;" : '');

		$this->row['ID'] = "list-new-".str_replace('_', '-', $type);
		$this->row['TITLE'] = ($type == "new_page" ? LIST_ADMIN_OPT_4 : LIST_ADMIN_OPT_2);
		$text = $this->parseTemplate('OPTIONS_HEADER');

		$text .= $this->parse_global_options($type);

		//page preference : caption
		$this->row['TOPIC'] = LIST_ADMIN_LAN_2;
		$this->row['HEADING'] = LIST_ADMIN_LAN_3;
		$this->row['HELP'] = LIST_ADMIN_LAN_4;
		$this->row['CONTID'] = "list-new-page-{$type}-expandable-caption";
		$this->row['FIELD'] = $rs->form_text($type."_caption", "30", $tp->toHTML($this->parent->list_pref[$type."_caption"],"","defs"), "50", "tbox");
		$text .= $this->parseTemplate('TOPIC_ROW');

		//page preference : icon : use
		$this->row['TOPIC'] = LIST_ADMIN_LAN_5;
		$this->row['HEADING'] = LIST_ADMIN_LAN_6;
		$this->row['HELP'] = LIST_ADMIN_LAN_7;
		$this->row['CONTID'] = "list-new-page-{$type}-expandable-icon-use";
		$this->row['FIELD'] = "
			".$rs->form_radio($type."_icon_use", "1", ($this->parent->list_pref[$type."_icon_use"] ? "1" : "0"), "", "").LIST_ADMIN_7."
			".$rs->form_radio($type."_icon_use", "0", ($this->parent->list_pref[$type."_icon_use"] ? "0" : "1"), "", "").LIST_ADMIN_8."
		";
		$text .= $this->parseTemplate('TOPIC_ROW');

		//page preference : icon : show default theme bullet
		$this->row['TOPIC'] = LIST_ADMIN_LAN_29;
		$this->row['HEADING'] = LIST_ADMIN_LAN_30;
		$this->row['HELP'] = LIST_ADMIN_LAN_31;
		$this->row['CONTID'] = "list-new-page-{$type}-expandable-icon-show";
		$this->row['FIELD'] = "
			".$rs->form_radio($type."_icon_default", "1", ($this->parent->list_pref[$type."_icon_default"] ? "1" : "0"), "", "").LIST_ADMIN_7."
			".$rs->form_radio($type."_icon_default", "0", ($this->parent->list_pref[$type."_icon_default"] ? "0" : "1"), "", "").LIST_ADMIN_8."
		";
		$text .= $this->parseTemplate('TOPIC_ROW');

		//page preference : amount chars
		$this->row['TOPIC'] = LIST_ADMIN_LAN_8;
		$this->row['HEADING'] = LIST_ADMIN_LAN_9;
		$this->row['HELP'] = LIST_ADMIN_LAN_10;
		$this->row['CONTID'] = "list-new-page-{$type}-expandable-amount-chars";
		$this->row['FIELD'] = $rs->form_text($type."_char_heading", "3", $this->parent->list_pref[$type."_char_heading"], "3", "tbox");
		$text .= $this->parseTemplate('TOPIC_ROW');

		//page preference : postfix
		$this->row['TOPIC'] = LIST_ADMIN_LAN_11;
		$this->row['HEADING'] = LIST_ADMIN_LAN_12;
		$this->row['HELP'] = LIST_ADMIN_LAN_13;
		$this->row['CONTID'] = "list-new-page-{$type}-expandable-postfix";
		$this->row['FIELD'] = $rs->form_text($type."_char_postfix", "3", $this->parent->list_pref[$type."_char_postfix"], "3", "tbox");
		$text .= $this->parseTemplate('TOPIC_ROW');

		//page preference : date
		$this->row['TOPIC'] = LIST_ADMIN_LAN_14;
		$this->row['HEADING'] = LIST_ADMIN_LAN_15;
		$this->row['HELP'] = LIST_ADMIN_LAN_16;
		$this->row['CONTID'] = "list-new-page-{$type}-expandable-date";
		$this->row['FIELD'] = $rs->form_text($type."_datestyle", "30", $this->parent->list_pref[$type."_datestyle"], "50", "tbox");
		$text .= $this->parseTemplate('TOPIC_ROW');

		//page preference : date today
		$this->row['TOPIC'] = LIST_ADMIN_LAN_17;
		$this->row['HEADING'] = LIST_ADMIN_LAN_18;
		$this->row['HELP'] = LIST_ADMIN_LAN_19;
		$this->row['CONTID'] = "list-new-page-{$type}-expandable-datet";
		$this->row['FIELD'] = $rs->form_text($type."_datestyletoday", "30", $this->parent->list_pref[$type."_datestyletoday"], "50", "tbox");
		$text .= $this->parseTemplate('TOPIC_ROW');

		//page preference : show empty
		$this->row['TOPIC'] = LIST_ADMIN_LAN_26;
		$this->row['HEADING'] = LIST_ADMIN_LAN_27;
		$this->row['HELP'] = LIST_ADMIN_LAN_28;
		$this->row['CONTID'] = "list-new-page-{$type}-expandable-showe";
		$this->row['FIELD'] = "
			".$rs->form_radio($type."_showempty", "1", ($this->parent->list_pref[$type."_showempty"] ? "1" : "0"), "", "").LIST_ADMIN_7."
			".$rs->form_radio($type."_showempty", "0", ($this->parent->list_pref[$type."_showempty"] ? "0" : "1"), "", "").LIST_ADMIN_8."
		";
		$text .= $this->parseTemplate('TOPIC_ROW');

		//page preference : colomn
		$this->row['TOPIC'] = LIST_ADMIN_LAN_20;
		$this->row['HEADING'] = LIST_ADMIN_LAN_21;
		$this->row['HELP'] = LIST_ADMIN_LAN_22;
		$this->row['CONTID'] = "list-new-page-{$type}-expandable-colomn";
		$this->row['FIELD'] = $rs->form_select_open($type."_colomn");
			for($a=1; $a<=count($this->parent->sections); $a++)
			{
				$this->row['FIELD'] .= ($this->parent->list_pref[$type."_colomn"] == $a ? $rs->form_option($a, 1, $a) : $rs->form_option($a, 0, $a));
			}
			$this->row['FIELD'] .= $rs->form_select_close();
		$text .= $this->parseTemplate('TOPIC_ROW');

		//page preference : welcome text
		$this->row['TOPIC'] = LIST_ADMIN_LAN_23;
		$this->row['HEADING'] = LIST_ADMIN_LAN_24;
		$this->row['HELP'] = LIST_ADMIN_LAN_25;
		$this->row['CONTID'] = "list-new-page-{$type}-expandable-wtext";
		$this->row['FIELD'] = $rs->form_textarea($type."_welcometext", "50", "5", $tp->toHTML($this->parent->list_pref[$type."_welcometext"],"","defs"), "", "tbox");
		$text .= $this->parseTemplate('TOPIC_ROW');

		if($type == "new_page")
		{
			//timelapse:show
			$this->row['TOPIC'] = LIST_ADMIN_LAN_36;
			$this->row['HEADING'] = LIST_ADMIN_LAN_37;
			$this->row['HELP'] = LIST_ADMIN_LAN_38;
			$this->row['CONTID'] = "list-new-page-{$type}-expandable-timelapse-show";
			$this->row['FIELD'] = "
				".$rs->form_radio($type."_timelapse", "1", ($this->parent->list_pref[$type."_timelapse"] ? "1" : "0"), "", "").LIST_ADMIN_7."
				".$rs->form_radio($type."_timelapse", "0", ($this->parent->list_pref[$type."_timelapse"] ? "0" : "1"), "", "").LIST_ADMIN_8."
			";
			$text .= $this->parseTemplate('TOPIC_ROW');

			//timelapse day number maximum
			$this->row['TOPIC'] = LIST_ADMIN_LAN_32;
			$this->row['HEADING'] = LIST_ADMIN_LAN_33;
			$this->row['HELP'] = LIST_ADMIN_LAN_34;
			$this->row['CONTID'] = "list-new-page-{$type}-expandable-timelapse-dnm";
			$this->row['FIELD'] = $rs->form_text($type."_timelapse_days", "3", $this->parent->list_pref[$type."_timelapse_days"], "3", "tbox")." ".LIST_ADMIN_LAN_35;
			$text .= $this->parseTemplate('TOPIC_ROW');
		}

		//page preference : open section if content exists? this will override the individual setting of the section
		$this->row['TOPIC'] = LIST_ADMIN_LAN_39;
		$this->row['HEADING'] = LIST_ADMIN_LAN_40;
		$this->row['HELP'] = LIST_ADMIN_LAN_41;
		$this->row['CONTID'] = "list-new-page-{$type}-expandable-osie";
		$this->row['FIELD'] = "
			".$rs->form_radio($type."_openifrecords", "1", ($this->parent->list_pref[$type."_openifrecords"] ? "1" : "0"), "", "").LIST_ADMIN_7."
			".$rs->form_radio($type."_openifrecords", "0", ($this->parent->list_pref[$type."_openifrecords"] ? "0" : "1"), "", "").LIST_ADMIN_8."
		";
		$text .= $this->parseTemplate('TOPIC_ROW');

		$text .= $this->parseTemplate('TOPIC_ROW_SPACER');
		$this->row['SUBMIT'] = $this->pref_submit();
		$text .= $this->parseTemplate('TOPIC_TABLE_END');

		return $text;
	}

	/**
	 * parseTemplate for admin page
	 * 
	 * @param string $template the template to parse
	 * @return string
	 * 
	 */
	function parseTemplate($template)
	{
		$text = preg_replace_callback( '/\{(.*?)\}/', function($matches) {
	        return($this->row[$matches[1]]);
	    }, $this->parent->template[$template]);

		return $text;
		//return preg_replace("/\{(.*?)\}/e", '$this->row[\'\1\']', $this->parent->template[$template]);
	}

	/**
	 * display submit button
	 * 
	 * @return string
	 * 
	 */
	function pref_submit()
	{
		global $rs;
		$frm = e107::getForm();
		
		$this->row['TOPIC'] = LIST_ADMIN_11;
		$this->row['FIELD'] = $frm->admin_button('update_menu',LIST_ADMIN_2,'update');

		return "<tr><td class='buttons-bar center' colspan='2'>".$frm->admin_button('update_menu',LIST_ADMIN_2,'update')."</td></tr>"; 
		// return $this->parseTemplate('TOPIC_ROW_NOEXPAND');
	}
}

?>
