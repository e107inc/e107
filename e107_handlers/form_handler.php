<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Form Handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/form_handler.php,v $
 * $Revision: 1.7 $
 * $Date: 2008-12-15 21:16:32 $
 * $Author: secretr $
 *
*/

if (!defined('e107_INIT')) { exit; }

/**
 * Automate Form fields creation. Produced markup is following e107 CSS/XHTML standards
 * If options argument is omitted, default values will be used (which OK most of the time)
 * Options are intended to handle some very special cases.
 *
 * Overall field options format (array or GET string like this one: var1=val1&var2=val2...):
 *
 *  - id => (mixed) custom id attribute value
 *  if numeric value is passed it'll be just appended to the name e.g. {filed-name}-{value}
 *  if false is passed id will be not created
 *  if empty string is passed (or no 'id' option is found)
 *  in all other cases the value will be used as field id
 * 	default: empty string
 *
 *  - class => (string) field class(es)
 * 	Example: 'tbox select class1 class2 class3'
 * 	NOTE: this will override core classes, so you have to explicit include them!
 * 	default: empty string
 *
 *  - size => (int) size attribute value (used when needed)
 *	default: 40
 * 
 *  - title (string) title attribute
 *  default: empty string (omitted)
 *
 *  - readonly => (bool) readonly attribute
 * 	default: false
 * 
 *  - selected => (bool) selected attribute (used when needed)
 * 	default: false
 * 
 *  checked => (bool) checked attribute (used when needed)
 *  default: false
 *  - disabled => (bool) disabled attribute
 *  default: false
 *
 *  - tabindex => (int) tabindex attribute value
 *	default: inner tabindex counter
 *
 *  - other => (string) additional data
 *  Example: 'attribute1="value1" attribute2="value2"'
 *  default: empty string
 */
class e_form
{
	var $_tabindex_counter = 0;
	var $_tabindex_enabled = true;
	var $_cached_attributes = array();

	function e_form($enable_tabindex = true)
	{
		$this->_tabindex_enabled = $enable_tabindex;
	}

	function text($name, $value, $maxlength = 200, $options = array())
	{
		$options = $this->format_options('text', $name, $options);
		return "<input type='text' name='{$name}' value='{$value}' maxlength='{$maxlength}'".$this->get_attributes($options, $name, $value)." />";
	}

	function file($name, $options = array()) 
	{
		$options = $this->format_options('text', $name, $options);
		return "<input type='text' name='{$name}'".$this->get_attributes($options, $name, $value)." />";
	}
	

	function password($name, $maxlength = 50, $options = array())
	{
		$options = $this->format_options('text', $name, $options);
		return "<input type='password' name='{$name}' value='' maxlength='{$maxlength}'".$this->get_attributes($options, $name, $value)." />";
	}

	function textarea($name, $value, $rows = 15, $cols = 40, $options = array())
	{
		$options = $this->format_options('textarea', $name, $options);
		return "<textarea name='{$name}' rows='{$rows}' cols='{$cols}'".$this->get_attributes($options, $name, $value).">{$value}</textarea>";
	}
	
	function checkbox($name, $value, $checked = false, $options = array()) 
	{
		$options['checked'] = $checked; //comes as separate argument just for convenience
		$options = $this->format_options('checkbox', $name, $options);
		return "<input type='checkbox' name='{$name}' value='{$value}'".$this->get_attributes($options, $name, $value)." />";

	}
	
	function radio($name, $value, $checked = false, $options = array()) 
	{
		$options['checked'] = $checked; //comes as separate argument just for convenience
		$options = $this->format_options('radio', $name, $options);
		return "<input type='radio' name='{$name}' value='".$value."'".$this->get_attributes($options, $name, $value)." />";

	}
	
	function label($text, $name = '', $value = '') 
	{
		$for_id = $this->_format_id('', $name, $value, 'for');
		return "<label$for_id>{$text}</label>";
	}

	function select_open($name, $options = array()) 
	{
		$options = $this->format_options('select', $name, $options);
		return "<select name='{$name}'".$this->get_attributes($options, $name).">";
	}
	
	function optgroup_open($label, $disabled) 
	{
		return "<optgroup class='optgroup' label='{$label}'".($disabled ? " disabled='disabled'" : '').">";
	}
	
	function option($option_name, $value, $selected = false, $options = array()) 
	{
		if(false === $value) $value = '';
		$options['selected'] = $selected; //comes as separate argument just for convenience
		$options = $this->format_options('option', '', $options);
		return "<option value='{$value}'".$this->get_attributes($options).">{$option_name}</option>";
	}

	function optgroup_close() 
	{
		return "</optgroup>";
	}
	
	function select_close() 
	{
		return "</select>";
	}

	function hidden($name, $value, $options = array()) 
	{
		$options = $this->format_options('hidden', $name, $options);
		return "<input type='hidden' name='{$name}' value='{$value}'".$this->get_attributes($options, $name, $value)." />";
	}
	
	function submit($name, $value, $options = array()) 
	{
		$options = $this->format_options('submit', $name, $options);
		return "<input class='button' type='submit' name='{$name}' value='{$value}'".$this->get_attributes($options, $name, $value)." />";
	}
	
	function submit_image($name, $value, $image, $options = array()) 
	{
		$options = $this->format_options('submit', $name, $options);
		return "<input class='image' type='image' src='{$image}' name='{$name}' value='{$value}'".$this->get_attributes($options, $name, $value)." />";
	}
	
	function admin_button($name, $value, $action = 'submit', $label = '', $options = array())
	{
		$options['class'] = $action; //additional classes in options not allowed
		$btype = 'submit';
		if($action == 'action') $btype = 'button';
		$options = $this->format_options('admin_button', $name, $options);
		if(empty($label)) $label = $value;
		
		return "
			<button type='{$btype}' name='{$name}' value='{$value}'".$this->get_attributes($options, $name).">
				<span>{$label}</span>
			</button>
		";
	}
	
	function get_attributes($options, $name = '', $value = '')
	{
		$ret = '';
		//
		foreach ($options as $option => $optval)
		{
			switch ($option) {

				case 'id':
					$ret .= $this->_format_id($optval, $name, $value);
					break;

				case 'class':
					if(!empty($optval)) $ret .= " class='{$optval}'";
					break;

				case 'size':
					if($optval) $ret .= " size='{$optval}'";
					break;
					
				case 'title':
					if($optval) $ret .= " title='{$optval}'";
					break;

				case 'tabindex':
					if($optval === false || !$this->_tabindex_enabled) break;
					$this->_tabindex_counter++;
					$ret .= " tabindex='".($optval ? $optval : $this->_tabindex_counter)."'";
					break;

				case 'readonly':
					if($optval) $ret .= " readonly='readonly'";
					break;

				case 'selected':
					if($optval) $ret .= " selected='selected'";
					break;
					
				case 'checked':
					if($optval) $ret .= " checked='checked'";
					break;

				case 'disabled':
					if($optval) $ret .= " disabled='disabled'";
					break;

				case 'other':
					if($optval) $ret .= " $optval";
					break;
			}
		}

		return $ret;
	}

	/**
	 * Auto-build field attribute id
	 *
	 * @param string $id_value value for attribute id passed with the option array
	 * @param string $name the name attribute passed to that field
	 * @param unknown_type $value the value attribute passed to that field
	 * @return unknown
	 */
	function _format_id($id_value, $name, $value = '', $return_attribute = 'id')
	{
		if($id_value === false) return '';

		//format the name first
		$name = str_replace(array('[]', '[', ']', '_'), array('', '-', '', '-'), $name);
		
		if(empty($id_value) ) return " {$return_attribute}='{$name}".($value ? "-{$value}" : '')."'";// also useful when name is e.g. name='my_name[some_id]'
		elseif(is_numeric($id_value) && $name) return " {$return_attribute}='{$name}-{$id_value}'";// also useful when name is e.g. name='my_name[]'
		else return " {$return_attribute}='{$id_value}'";
	}


	/**
	 * Format options based on the field type,
	 * merge with default
	 *
	 * @param string $type
	 * @param string $name form name attribute value
	 * @param array|string $user_options
	 * @return array merged options
	 */
	function format_options($type, $name, $user_options)
	{
		if(is_string($user_options)) parse_str($user_options, $user_options);

		$def_options = $this->_default_options($type);

		foreach (array_keys($user_options) as $key)
		{
			if(!isset($def_options[$key])) unset($user_options[$key]);
		}

		$user_options['name'] = $name; //required for some of the automated tasks
		return array_merge($def_options, $user_options);
	}

	/**
	 * Get default options array based on the filed type
	 *
	 * @param string $type
	 * @return array default options
	 */
	function _default_options($type)
	{
		if(isset($this->_cached_attributes[$type])) return $this->_cached_attributes[$type];

		$def_options = array(
			'id' => '',
			'class' => '',
			'title' => '',
			'size' => '',
			'readonly' => false,
			'selected' => false,
			'checked' => false,
			'disabled' => false,
			'tabindex' => 0,
			'other' => ''
		);

		switch ($type) {
			case 'hidden':
				$def_options = array('disabled' => false, 'other' => '');
				break;

			case 'text':
				$def_options['class'] = 'tbox input-text';
				unset($def_options['selected'], $def_options['checked']);
				break;

			case 'textarea':
				$def_options['class'] = 'tbox textarea';
				unset($def_options['selected'], $def_options['checked'], $def_options['size']);
				break;

			case 'select':
				$def_options['class'] = 'tbox select';
				unset($def_options['checked'],  $def_options['checked']);
				break;

			case 'option':
				$def_options = array('class' => '', 'selected' => false, 'other' => '');
				break;

			case 'radio':
				$def_options['class'] = 'radio';
				unset($def_options['size'], $def_options['selected']);
				break;

			case 'checkbox':
				$def_options['class'] = 'checkbox';
				unset($def_options['size'],  $def_options['selected']);
				break;

			case 'submit':
				$def_options['class'] = 'button';
				unset($def_options['checked'], $def_options['selected'], $def_options['readonly']);
				break;

			case 'admin_button':
				unset($def_options['checked'],  $def_options['selected'], $def_options['readonly']);
				break;

		}

		$this->_cached_attributes[$type] = $def_options;
		return $def_options;
	}
}

class form {

	function form_open($form_method, $form_action, $form_name = "", $form_target = "", $form_enctype = "", $form_js = "") {
		$method = ($form_method ? "method='".$form_method."'" : "");
		$target = ($form_target ? " target='".$form_target."'" : "");
		$name = ($form_name ? " id='".$form_name."' " : " id='myform'");
		return "\n<form action='".$form_action."' ".$method.$target.$name.$form_enctype.$form_js.">";
	}

	function form_text($form_name, $form_size, $form_value, $form_maxlength = FALSE, $form_class = "tbox", $form_readonly = "", $form_tooltip = "", $form_js = "") {
		$name = ($form_name ? " id='".$form_name."' name='".$form_name."'" : "");
		$value = (isset($form_value) ? " value='".$form_value."'" : "");
		$size = ($form_size ? " size='".$form_size."'" : "");
		$maxlength = ($form_maxlength ? " maxlength='".$form_maxlength."'" : "");
		$readonly = ($form_readonly ? " readonly='readonly'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		return "\n<input class='".$form_class."' type='text' ".$name.$value.$size.$maxlength.$readonly.$tooltip.$form_js." />";
	}

	function form_password($form_name, $form_size, $form_value, $form_maxlength = FALSE, $form_class = "tbox", $form_readonly = "", $form_tooltip = "", $form_js = "") {
		$name = ($form_name ? " id='".$form_name."' name='".$form_name."'" : "");
		$value = (isset($form_value) ? " value='".$form_value."'" : "");
		$size = ($form_size ? " size='".$form_size."'" : "");
		$maxlength = ($form_maxlength ? " maxlength='".$form_maxlength."'" : "");
		$readonly = ($form_readonly ? " readonly='readonly'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		return "\n<input class='".$form_class."' type='password' ".$name.$value.$size.$maxlength.$readonly.$tooltip.$form_js." />";
	}

	function form_button($form_type, $form_name, $form_value, $form_js = "", $form_image = "", $form_tooltip = "") {
		$name = ($form_name ? " id='".$form_name."' name='".$form_name."'" : "");
		$image = ($form_image ? " src='".$form_image."' " : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."' " : "");
		return "\n<input class='button' type='".$form_type."' ".$form_js." value='".$form_value."'".$name.$image.$tooltip." />";
	}

	function form_textarea($form_name, $form_columns, $form_rows, $form_value, $form_js = "", $form_style = "", $form_wrap = "", $form_readonly = "", $form_tooltip = "") {
		$name = ($form_name ? " id='".$form_name."' name='".$form_name."'" : "");
		$readonly = ($form_readonly ? " readonly='readonly'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		$wrap = ($form_wrap ? " wrap='".$form_wrap."'" : "");
		$style = ($form_style ? " style='".$form_style."'" : "");
		return "\n<textarea class='tbox' cols='".$form_columns."' rows='".$form_rows."' ".$name.$form_js.$style.$wrap.$readonly.$tooltip.">".$form_value."</textarea>";
	}

	function form_checkbox($form_name, $form_value, $form_checked = 0, $form_tooltip = "", $form_js = "") {
		$name = ($form_name ? " id='".$form_name.$form_value."' name='".$form_name."'" : "");
		$checked = ($form_checked ? " checked='checked'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		return "\n<input type='checkbox' value='".$form_value."'".$name.$checked.$tooltip.$form_js." />";

	}

	function form_radio($form_name, $form_value, $form_checked = 0, $form_tooltip = "", $form_js = "") {
		$name = ($form_name ? " id='".$form_name.$form_value."' name='".$form_name."'" : "");
		$checked = ($form_checked ? " checked='checked'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		return "\n<input type='radio' value='".$form_value."'".$name.$checked.$tooltip.$form_js." />";

	}

	function form_file($form_name, $form_size, $form_tooltip = "", $form_js = "") {
		$name = ($form_name ? " id='".$form_name."' name='".$form_name."'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		return "<input type='file' class='tbox' size='".$form_size."'".$name.$tooltip.$form_js." />";
	}

	function form_select_open($form_name, $form_js = "") {
		return "\n<select id='".$form_name."' name='".$form_name."' class='tbox' ".$form_js." >";
	}

	function form_select_close() {
		return "\n</select>";
	}

	function form_option($form_option, $form_selected = "", $form_value = "", $form_js = "") {
		$value = ($form_value !== FALSE ? " value='".$form_value."'" : "");
		$selected = ($form_selected ? " selected='selected'" : "");
		return "\n<option".$value.$selected." ".$form_js.">".$form_option."</option>";
	}

	function form_hidden($form_name, $form_value) {
		return "\n<input type='hidden' id='".$form_name."' name='".$form_name."' value='".$form_value."' />";
	}

	function form_close() {
		return "\n</form>";
	}
}

/*
Usage
echo $rs->form_open("post", e_SELF, "_blank");
echo $rs->form_text("testname", 100, "this is the value", 100, 0, "tooltip");
echo $rs->form_button("submit", "testsubmit", "SUBMIT!", "", "Click to submit");
echo $rs->form_button("reset", "testreset", "RESET!", "", "Click to reset");
echo $rs->form_textarea("textareaname", 10, 10, "Value", "overflow:hidden");
echo $rs->form_checkbox("testcheckbox", 1, 1);
echo $rs->form_checkbox("testcheckbox2", 2);
echo $rs->form_hidden("hiddenname", "hiddenvalue");
echo $rs->form_radio("testcheckbox", 1, 1);
echo $rs->form_radio("testcheckbox", 1);
echo $rs->form_file("testfile", "20");
echo $rs->form_select_open("testselect");
echo $rs->form_option("Option 1");
echo $rs->form_option("Option 2");
echo $rs->form_option("Option 3", 1, "defaultvalue");
echo $rs->form_option("Option 4");
echo $rs->form_select_close();
echo $rs->form_close();
*/


?>