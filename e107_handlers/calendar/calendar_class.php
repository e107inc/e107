<?php

/**
*  File: calendar.php | (c) dynarch.com 2004
*  Distributed as part of "The Coolest DHTML Calendar"
*  under the same terms.
*  -----------------------------------------------------------------
*  This file implements a simple PHP wrapper for the calendar.  It
*  allows you to easily include all the calendar files and setup the
*  calendar by instantiating and calling a PHP object.
*/

define('NEWLINE', "\n");

class DHTML_Calendar
{
	var $calendar_file;
	var $calendar_lang_file;
	var $calendar_setup_file;
	var $calendar_theme_file;
	var $calendar_options;
	var $calendar_img;

	function DHTML_Calendar($stripped = true)
	{
		if ($stripped)
		{
			$this->calendar_file = e_HANDLER.'calendar/calendar_stripped.js';
			$this->calendar_setup_file = e_HANDLER.'calendar/calendar-setup_stripped.js';
		}
		else
		{
			$this->calendar_file = e_HANDLER.'calendar/calendar.js';
			$this->calendar_setup_file = e_HANDLER.'calendar/calendar-setup.js';
		}


		if(file_exists(e_HANDLER.'calendar/language/'.e_LANGUAGE.'.js'))
		{
			$this->calendar_lang_file = e_HANDLER.'calendar/language/'.e_LANGUAGE.'.js';
		}
		else
		{
			$this->calendar_lang_file = e_HANDLER.'calendar/language/English.js';
		}

		if(defined('CALENDAR_IMG'))
		{
			$this->calendar_img = CALENDAR_IMG;
		}
		else
		{
			$this->calendar_img = "<img style='vertical-align:middle; border:0px' src='".e_HANDLER."calendar/cal.gif'  alt='' />";
		}

		if(file_exists(THEME."calendar.css"))
		{
			$this->calendar_theme_file = THEME."calendar.css";
		}
		else
		{
			$this->calendar_theme_file = e_HANDLER."calendar/calendar.css";
		}

		$this->calendar_options = array('ifFormat' => '%Y/%m/%d', 'daFormat' => '%Y/%m/%d');
	}

	function set_option($name, $value) {
		$this->calendar_options[$name] = $value;
	}

	function load_files() {
		return $this->get_load_files_code();
	}

	function get_load_files_code() {
		$code  = ( '<link rel="stylesheet" type="text/css" media="all" href="' . $this->calendar_theme_file . '" />' . NEWLINE );
		$code .= ( '<script type="text/javascript" src="'.$this->calendar_file.'"></script>' . NEWLINE );
		$code .= ( '<script type="text/javascript" src="'.$this->calendar_setup_file.'"></script>' . NEWLINE );
		$code .= ( '<script type="text/javascript" src="'.$this->calendar_lang_file.'"></script>' . NEWLINE );
		return $code;
	}

	function _make_calendar($other_options = array()) {
		$js_options = $this->_make_js_hash(array_merge($this->calendar_options, $other_options));
		$code  = ( '<script type="text/javascript">Calendar.setup({' . $js_options . '});</script>' );
		return $code;
	}

	function make_input_field($cal_options = array(), $field_attributes = array())
	{
		$ret = "";
		$id = $this->_gen_id();
		$attrstr = $this->_make_html_attr(array_merge($field_attributes, array('id'   => $this->_field_id($id), 'type' => 'text')));
		$ret .= '<input ' . $attrstr .'/> ';
		$ret .= "<a href='#' id='{$this->_trigger_id($id)}'>{$this->calendar_img}</a>";
		$options = array_merge($cal_options, array('inputField' => $this->_field_id($id), 'button' => $this->_trigger_id($id)));
		$ret .= $this->_make_calendar($options);
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