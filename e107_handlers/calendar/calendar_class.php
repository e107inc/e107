<?php
/*
 * e107 website system
 * 
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 * 
 * $Source: /cvs_backup/e107_0.8/e107_handlers/calendar/calendar_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 * 
*/

/**
*  File: calendar.php | (c) dynarch.com 2004
*  Distributed as part of "The Coolest DHTML Calendar"
*  under the same terms.
*  -----------------------------------------------------------------
*  This file implements a simple PHP wrapper for the calendar.  It
*  allows you to easily include all the calendar files and setup the
*  calendar by instantiating and calling a PHP object.
*/

class DHTML_Calendar
{
	public static $NEWLINE = "\n";
	public $calendar_file;
	public $calendar_lang_file;
	public $calendar_setup_file;
	public $calendar_theme_file;
	public $calendar_options;
	public $calendar_img;

	function DHTML_Calendar($stripped = true)
	{
		if ($stripped)
		{
			$this->calendar_file = e_HANDLER_ABS.'calendar/calendar_stripped.js';
			$this->calendar_setup_file = e_HANDLER_ABS.'calendar/calendar-setup_stripped.js';
		}
		else
		{
			$this->calendar_file = e_HANDLER_ABS.'calendar/calendar.js';
			$this->calendar_setup_file = e_HANDLER_ABS.'calendar/calendar-setup.js';
		}


		if(file_exists(e_HANDLER.'calendar/language/'.e_LANGUAGE.'.js'))
		{
			$this->calendar_lang_file = e_HANDLER_ABS.'calendar/language/'.e_LANGUAGE.'.js';
		}
		else
		{
			$this->calendar_lang_file = e_HANDLER_ABS.'calendar/language/English.js';
		}

		if(defined('CALENDAR_IMG'))
		{
			$this->calendar_img = CALENDAR_IMG;
		}
		else
		{
			$this->calendar_img = "<img style='vertical-align:middle;' src='".e_HANDLER_ABS."calendar/cal.gif'  alt='' />";
		}

		if(file_exists(THEME."calendar.css"))
		{
			$this->calendar_theme_file = THEME_ABS."calendar.css";
		}
		else
		{
			$this->calendar_theme_file = e_HANDLER_ABS."calendar/calendar.css";
		}

		$this->calendar_options = array('ifFormat' => '%Y/%m/%d', 'daFormat' => '%Y/%m/%d');
	}

	function set_option($name, $value) {
		$this->calendar_options[$name] = $value;
	}

	function load_files() {
		//return $this->get_load_files_code();
		// JS and CSS are now sent on the fly - see make_input_field()
		return '';
	}

	function get_load_files_code() {
		$code  = ( '<link rel="stylesheet" type="text/css" media="all" href="' . $this->calendar_theme_file . '" />' . self::$NEWLINE );
		$code .= ( '<script type="text/javascript" src="'.$this->calendar_file.'"></script>' . self::$NEWLINE );
		$code .= ( '<script type="text/javascript" src="'.$this->calendar_setup_file.'"></script>' . self::$NEWLINE );
		$code .= ( '<script type="text/javascript" src="'.$this->calendar_lang_file.'"></script>' . self::$NEWLINE );
		return $code;
	}

	function _make_calendar($other_options = array(), $script_tag = true) {
		$js_options = $this->_make_js_hash(array_merge($this->calendar_options, $other_options));
		$code  = $script_tag ? ( '<script type="text/javascript">Calendar.setup({' . $js_options . '});</script>' ) : 'Calendar.setup({' . $js_options . '});';
		return $code;
	}

	function make_input_field($cal_options = array(), $field_attributes = array())
	{
		$ret = "";
		$id = $this->_gen_id();
		$attrstr = $this->_make_html_attr(array_merge($field_attributes, array('id' => $this->_field_id($id), 'type' => 'text')));
		$ret .= '<input ' . $attrstr .'/> ';
		
		//TODO perhaps make an admin-pref option for this. Default should be without the trigger-image. 
	//	$ret .= "<a href='#' id='".$this->_trigger_id($id)."'>".$this->calendar_img."</a>";
	//	$options = array_merge($cal_options, array('inputField' => $this->_field_id($id), 'button' => $this->_trigger_id($id)));
	
		$options = array_merge($cal_options, array('inputField' => $this->_field_id($id), 'button' => null));
	
		e107::getJs()->footerInline($this->_make_calendar($options, false)); 
		//JS manager to send JS to header if possible, if not - footer
		e107::getJs()
			->tryHeaderFile($this->calendar_file)
			->tryHeaderFile($this->calendar_setup_file)
			->tryHeaderFile($this->calendar_lang_file)
			->otherCSS($this->calendar_theme_file); // send CSS to the site header
		return $ret;
	}

	/// PRIVATE SECTION

	function _field_id($id) { return 'f-calendar-field-' . $id; }
	function _trigger_id($id) { return 'f-calendar-trigger-' . $id; }
	function _gen_id() { static $id = 0; return ++$id; }

	function _make_js_hash($array) {
		$jstr = '';
		reset($array);
		while (list($key, $val) = each($array))
		{
			if (is_bool($val))
			{
				$val = $val ? 'true' : 'false';
			}
			elseif (!is_numeric($val))
			{
				$val = '"'.$val.'"';
			}
			if ($jstr)
			{
				$jstr .= ',';
			}

			$jstr .= '"' . $key . '":' . $val;
		}
		return $jstr;
	}

	function _make_html_attr($array) {
		$attrstr = '';
		reset($array);
		while (list($key, $val) = each($array))
		{
			$attrstr .= $key . '="' . $val . '" ';
		}
		return $attrstr;
	}
}

?>