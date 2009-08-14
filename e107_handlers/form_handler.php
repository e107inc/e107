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
 * $Revision: 1.38 $
 * $Date: 2009-08-14 23:22:37 $
 * $Author: bugrain $
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

	/**
	 * @var user_class
	 */
	var $_uc;

	function e_form($enable_tabindex = false)
	{
		$this->_tabindex_enabled = $enable_tabindex;
		$e107 = &e107::getInstance();
		$this->_uc = &$e107->user_class;
	}

	function text($name, $value, $maxlength = 200, $options = array())
	{
		$options = $this->format_options('text', $name, $options);
		//never allow id in format name-value for text fields
		return "<input type='text' name='{$name}' value='{$value}' maxlength='{$maxlength}'".$this->get_attributes($options, $name)." />";
	}

	function iconpicker($name, $default, $label, $sc_parameters = '', $ajax = true)
	{
    	// TODO - Hide the <input type='text'> element, and display the icon itself after it has been chosen.
		// eg. <img id='iconview' src='".$img."' style='border:0; ".$blank_display."' alt='' />
		// The button itself could be replaced with an icon just for this purpose.


		$e107 = &e107::getInstance();
		$id = $this->name2id($name);
		$sc_parameters .= '&id='.$id;
		$jsfunc = $ajax ? "e107Ajax.toggleUpdate('{$id}-iconpicker', '{$id}-iconpicker-cn', 'sc:iconpicker=".urlencode($sc_parameters)."', '{$id}-iconpicker-ajax', { overlayElement: '{$id}-iconpicker-button' })" : "e107Helper.toggle('{$id}-iconpicker')";
		$ret = $this->text($name, $default).$this->admin_button($name.'-iconpicker-button', $label, 'action', '', array('other' => "onclick=\"{$jsfunc}\""));
		$ret .= "
			<div id='{$id}-iconpicker' class='e-hideme'>
				<div class='expand-container' id='{$id}-iconpicker-cn'>
					".(!$ajax ? $e107->tp->parseTemplate('{ICONPICKER='.$sc_parameters.'}') : '')."
				</div>
			</div>
		";

		return $ret;
	}

   /**
    * Date field with popup calendar
    * @param name => string - the name of the field
    * @param datestamp => UNIX timestamp - default value of the field
    **/
   function datepicker($name, $datestamp=false)
   {
      global $pref;
      //TODO can some of these values be set in an admin section somewhere so they are set per site?
      //TODO allow time option ?
      $cal = new DHTML_Calendar(true);
		$cal_options['showsTime'] = false;
		$cal_options['showOthers'] = false;
		$cal_options['weekNumbers'] = false;
		//TODO use $prefs values for format?
		$cal_options['ifFormat'] = $pref['inputdate'];
		$cal_options['timeFormat'] = "24";
		$cal_attrib['class'] = "tbox";
		$cal_attrib['size'] = "12";
		$cal_attrib['name'] = $name;
		if ($datestamp)
		{
   		//TODO use $prefs values for format?
		   $cal_attrib['value'] = date("d/m/Y H:i:s", $datestamp);
		   $cal_attrib['value'] = date("d/m/Y", $datestamp);
		}
		return $cal->make_input_field($cal_options, $cal_attrib);
   }

	function file($name, $options = array())
	{
		$options = $this->format_options('file', $name, $options);
		//never allow id in format name-value for text fields
		return "<input type='file' name='{$name}'".$this->get_attributes($options, $name)." />";
	}


	function password($name, $maxlength = 50, $options = array())
	{
		$options = $this->format_options('text', $name, $options);
		//never allow id in format name-value for text fields
		return "<input type='password' name='{$name}' value='' maxlength='{$maxlength}'".$this->get_attributes($options, $name)." />";
	}

	function textarea($name, $value, $rows = 15, $cols = 40, $options = array())
	{
		$options = $this->format_options('textarea', $name, $options);
		//never allow id in format name-value for text fields
		return "<textarea name='{$name}' rows='{$rows}' cols='{$cols}'".$this->get_attributes($options, $name).">{$value}</textarea>";
	}

	function bbarea($name, $value, $help_mod = '', $help_tagid='')
	{
	   	$options = array('class' => 'tbox large e-wysiwyg');
		if(!defsettrue('e_WYSIWYG'))
		{
			require_once(e_HANDLER."ren_help.php");
			$options['other'] = "onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'";
			$bbbar = display_help($help_tagid, $help_mod);
		}

		$ret = "
		<div class='bbarea'>
			<div class='field-spacer'>
				".$this->textarea($name, $value, 15, 50, $options)."
			</div>
			{$bbbar}
		</div>
		";

		return $ret;
	}

	function checkbox($name, $value, $checked = false, $options = array())
	{
		$options['checked'] = $checked; //comes as separate argument just for convenience
		$options = $this->format_options('checkbox', $name, $options);
		return "<input type='checkbox' name='{$name}' value='{$value}'".$this->get_attributes($options, $name, $value)." />";

	}

	function checkbox_switch($name, $value, $checked = false, $label = '')
	{
		return $this->checkbox($name, $value, $checked).$this->label($label ? $label : LAN_ENABLED, $name, $value);
	}

	function checkbox_toggle($name, $selector = 'multitoggle')
	{
		$selector = 'jstarget:'.$selector;
		return $this->checkbox($name, $selector, false, array('id'=>false,'class'=>'checkbox toggle-all'));
	}

	function uc_checkbox($name, $current_value, $uc_options, $field_options = array())
	{
		if(!is_array($field_options)) parse_str($field_options, $field_options);
		return '
			<div class="check-block">
				'.$this->_uc->vetted_tree($name, array($this, '_uc_checkbox_cb'), $current_value, $uc_options, $field_options).'
			</div>
		';
	}

	function _uc_checkbox_cb($treename, $classnum, $current_value, $nest_level, $field_options)
	{
		if($classnum == e_UC_BLANK)
			return '';

		$tmp = explode(',', $current_value);

		$class = $style = '';
		if($nest_level == 0)
		{
			$class = " strong";
		}
		else
		{
			$style = " style='text-indent:" . (1.2 * $nest_level) . "em'";
		}
		$descr = varset($field_options['description']) ? ' <span class="smalltext">('.$this->_uc->uc_get_classdescription($classnum).')</span>' : '';

		return "<div class='field-spacer{$class}'{$style}>".$this->checkbox($treename.'[]', $classnum, in_array($classnum, $tmp), $field_options).$this->label($this->_uc->uc_get_classname($classnum).$descr, $treename.'[]', $classnum)."</div>\n";
	}

	function radio($name, $value, $checked = false, $options = array())
	{
		$options['checked'] = $checked; //comes as separate argument just for convenience
		$options = $this->format_options('radio', $name, $options);
		return "<input type='radio' name='{$name}' value='".$value."'".$this->get_attributes($options, $name, $value)." />";

	}

	function radio_switch($name, $checked_enabled = false, $label_enabled = '', $label_disabled = '')
	{
		return $this->radio($name, 1, $checked_enabled)."".$this->label($label_enabled ? $label_enabled : LAN_ENABLED, $name, 1)."&nbsp;&nbsp;
			".$this->radio($name, 0, !$checked_enabled)."".$this->label($label_disabled ? $label_disabled : LAN_DISABLED, $name, 0);

	}

	function radio_multi($name, $elements, $checked, $multi_line = false)
	{
		$text = array();
		if(is_string($elements)) parse_str($elements, $elements);

		foreach ($elements as $value => $label)
		{
			$text[] = $this->radio($name, $value, $checked == $value)."".$this->label($label, $name, $value);
		}
		if(!$multi_line)
			return implode("&nbsp;&nbsp;", $text);

		return "<div class='field-spacer'>".implode("</div><div class='field-spacer'>", $text)."</div>";

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

	function selectbox($name, $option_array, $selected = false, $options = array())
	{
		return $this->select_open($name, $options)."\n".$this->option_multi($option_array, $selected)."\n".$this->select_close();
	}

	function uc_select($name, $current_value, $uc_options, $select_options = array(), $opt_options = array())
	{
		return $this->select_open($name, $select_options)."\n".$this->_uc->vetted_tree($name, array($this, '_uc_select_cb'), $current_value, $uc_options, $opt_options)."\n".$this->select_close();
	}

	// Callback for vetted_tree - Creates the option list for a selection box
	function _uc_select_cb($treename, $classnum, $current_value, $nest_level)
	{
		if($classnum == e_UC_BLANK)
			return $this->option('&nbsp;', '');

		$tmp = explode(',', $current_value);
		if($nest_level == 0)
		{
			$prefix = '';
			$style = "font-weight:bold; font-style: italic;";
		}
		elseif($nest_level == 1)
		{
			$prefix = '&nbsp;&nbsp;';
			$style = "font-weight:bold";
		}
		else
		{
			$prefix = '&nbsp;&nbsp;'.str_repeat('--', $nest_level - 1).'&gt;';
			$style = '';
		}
		return $this->option($prefix.$this->_uc->uc_get_classname($classnum), $classnum, in_array($classnum, $tmp), array("style"=>"{$style}"))."\n";
	}

	function optgroup_open($label, $disabled)
	{
		return "<optgroup class='optgroup' label='{$label}'".($disabled ? " disabled='disabled'" : '').">";
	}

	function option($option_name, $value, $selected = false, $options = array())
	{
		if(false === $value) $value = '';
		$options = $this->format_options('option', '', $options);
		$options['selected'] = $selected; //comes as separate argument just for convenience
		return "<option value='{$value}'".$this->get_attributes($options).">{$option_name}</option>";
	}

	function option_multi($option_array, $selected = false, $options = array())
	{
		if(is_string($option_array)) parse_str($option_array, $option_array);

		$text = '';
		foreach ($option_array as $value => $label)
		{
			$text .= $this->option($label, $value, $selected == $value, $options)."\n";
		}

		return $text;
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
		return "<input type='submit' name='{$name}' value='{$value}'".$this->get_attributes($options, $name, $value)." />";
	}

	function submit_image($name, $value, $image, $title='', $options = array())
	{
		$options = $this->format_options('submit_image', $name, $options);
		switch ($image) {
			case 'edit':
				$image = ADMIN_EDIT_ICON_PATH;
				$options['class'] = 'action edit';
			break;

			case 'delete':
				$image = ADMIN_DELETE_ICON_PATH;
				$options['class'] = 'action delete';
			break;
		}
		$options['title'] = $title;//shorthand

		return "<input type='image' src='{$image}' name='{$name}' value='{$value}'".$this->get_attributes($options, $name, $value)." />";
	}

	function admin_button($name, $value, $action = 'submit', $label = '', $options = array())
	{

		$btype = 'submit';
		if($action == 'action') $btype = 'button';
		$options = $this->format_options('admin_button', $name, $options);
		$options['class'] = $action;//shorthand
		if(empty($label)) $label = $value;

		return "
			<button type='{$btype}' name='{$name}' value='{$value}'".$this->get_attributes($options, $name)."><span>{$label}</span></button>
		";
	}

	function getNext()
	{
		if(!$this->_tabindex_enabled) return 0;
		$this->_tabindex_counter += 1;
		return $this->_tabindex_counter;
	}

	function getCurrent()
	{
		if(!$this->_tabindex_enabled) return 0;
		return $this->_tabindex_counter;
	}

	function resetTabindex($reset = 0)
	{
		$this->_tabindex_counter = $reset;
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
					if($optval) $ret .= " tabindex='{$optval}'";
					elseif(false === $optval || !$this->_tabindex_enabled) break;
					else
					{
						$this->_tabindex_counter += 1;
						$ret .= " tabindex='".$this->_tabindex_counter."'";
					}
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
	 * @return string formatted id attribute
	 */
	function _format_id($id_value, $name, $value = '', $return_attribute = 'id')
	{
		if($id_value === false) return '';

		//format data first
		$name = $this->name2id($name);
		$value = trim(preg_replace('#[^a-z0-9\-]/i#','-', $value), '-');

		if(!$id_value && is_numeric($value)) $id_value = $value;

		if(empty($id_value) ) return " {$return_attribute}='{$name}".($value ? "-{$value}" : '')."'";// also useful when name is e.g. name='my_name[some_id]'
		elseif(is_numeric($id_value) && $name) return " {$return_attribute}='{$name}-{$id_value}'";// also useful when name is e.g. name='my_name[]'
		else return " {$return_attribute}='{$id_value}'";
	}

	function name2id($name)
	{
		return rtrim(str_replace(array('[]', '[', ']', '_'), array('-', '-', '', '-'), $name), '-');
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
			if(!isset($def_options[$key])) unset($user_options[$key]);//remove it?
		}

		$user_options['name'] = $name; //required for some of the automated tasks
		return array_merge($def_options, $user_options);
	}

	/**
	 * Get default options array based on the field type
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
				$def_options = array('id' => false, 'disabled' => false, 'other' => '');
				break;

			case 'text':
				$def_options['class'] = 'tbox input-text';
				unset($def_options['selected'], $def_options['checked']);
				break;

			case 'file':
				$def_options['class'] = 'tbox file';
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

			case 'submit_image':
				$def_options['class'] = 'action';
				unset($def_options['checked'], $def_options['selected'], $def_options['readonly']);
				break;

			case 'admin_button':
				unset($def_options['checked'],  $def_options['selected'], $def_options['readonly']);
				break;

		}

		$this->_cached_attributes[$type] = $def_options;
		return $def_options;
	}





	function columnSelector($columnsArray,$columnsDefault='',$id='column_options')
	{
        $text = "<div style='position:relative;float:right;'>
		<a href='#".$id."' class='e-show-if-js e-expandit' title='Click to select columns to display'>
		<img class='middle' src='".e_IMAGE_ABS."admin_images/select_columns_16.png' alt='select columns' /></a>

		<div id='".$id."' class='e-show-if-js e-hideme col-selection'>\n";
        unset($columnsArray['options']);

		foreach($columnsArray as $key=>$fld)
		{
			if(!varset($fld['forced']))
			{
				$checked = (in_array($key,$columnsDefault)) ?  TRUE : FALSE;
				$text .= $this->checkbox('e-columns[]', $key, $checked). $fld['title']."<br />\n";
			}
		}

        $text .= "<div id='button' style='text-align:right'>\n";  // has issues with the checkboxes.
	 	$text .= $this->admin_button('submit-e-columns','Save','Save');

   	 	$text .= "</div>\n";
		$text .= "</div></div>";
		return $text;
	}

	function colGroup($fieldarray,$columnPref='')
	{
        $text = "";
        $count = 0;
		foreach($fieldarray as $key=>$val)
		{
			if(in_array($key,$columnPref) || $key=='options' || varsettrue($val['forced']))
			{
				$text .= "\n<col style='width: ".$val['width'].";'></col>";
				$count++;
			}
		}

		return "<colgroup span='".$count."'>\n".$text."\n</colgroup>\n";
	}

	function thead($fieldarray,$columnPref='',$querypattern = '')
	{
        $text = "";

        $tmp = explode(".",e_QUERY);

		$etmp = explode(".",$querypattern);

		// Note: this function should probably be adapted to ALSO deal with $_GET. eg. ?mode=main&field=user_name&asc=desc&from=100
		// or as a pattern: ?mode=main&field=[FIELD]&asc=[ASC]&from=[FROM]

		foreach($etmp as $key=>$val)    // I'm sure there's a more efficient way to do this, but too tired to see it right now!.
		{

        	if($val == "[FIELD]")
			{
            	$field = $tmp[$key];
			}

			if($val == "[ASC]")
			{
            	$ascdesc = $tmp[$key];
			}
			if($val == "[FROM]")
			{
            	$fromval = $tmp[$key];
			}
		}

		if(!$fromval){ $fromval = 0; }

        $ascdesc = ($ascdesc == 'desc') ? 'asc' : 'desc';

		foreach($fieldarray as $key=>$val)
		{
     		if(in_array($key,$columnPref) || $key == "options" || (varsettrue($val['forced'])))
			{
				$cl = (varset($val['thclass'])) ? "class='".$val['thclass']."'" : "";
				$text .= "\n\t<th id='e-column-".$key."' {$cl}>";

                if($querypattern!="" && !varsettrue($val['nosort']) && $key != "options")
				{
					$from = ($key == $field) ? $fromval : 0;
					$srch = array("[FIELD]","[ASC]","[FROM]");
					$repl = array($key,$ascdesc,$from);
                	$val['url'] = e_SELF."?".str_replace($srch,$repl,$querypattern);
				}

				$text .= (varset($val['url'])) ? "<a href='".$val['url']."'>" : "";  // Really this column-sorting link should be auto-generated, or be autocreated via unobtrusive js.
	            $text .= $val['title'];
				$text .= ($val['url']) ? "</a>" : "";
	            $text .= ($key == "options") ? $this->columnSelector($fieldarray,$columnPref) : "";
				$text .= ($key == "checkboxes") ? $this->checkbox_toggle('e-column-toggle',$val['toggle']) : "";


	 			$text .= "</th>";
			}
		}

      return "<thead>\n<tr>\n".$text."\n</tr>\n</thead>\n\n";

	}

	// The 2 functions below are for demonstration purposes only, and may be moved/modified before release.
	function filterType($fieldarray)
	{
		define("e_AJAX_REQUEST",TRUE);
    	$text .= "<select name='search_filter[]' style='margin:2px' onchange='UpdateForm(this.options[selectedIndex].value)'>";
		foreach($fieldarray as $key=>$val)
		{
        	$text .= ($val['type']) ? "<option value='$key'>".$val['title']."</option>\n" : "";

		}
		$text .= "</select>";
		return $text;
	}

	function filterValue($type,$fields)
	{


		if($type)
		{

			switch ($fields[$type]['type']) {
				case "datestamp":
					return "[date field]";
               	break;

				case "boolean":

					return "<select name='searchquery'><option value='1'>".LAN_YES."</option>\n
				  	<option value='0'>".LAN_NO."</option>
				  	</select>";
               	break;

			   	case "user":
 			   		return "<select name='searchquery'><option value='1'>User One</option><option value='2'>User Two</option></select>";
				break;


              default :

			  return $this->text('searchquery', '', 50);

            }
		}
		else
		{
    		return $this->text('searchquery', '', 50);
		}
		// This needs to be dynamic for the various form types, and be loaded via ajax.
	}

   /**
    * Generates a batch options select component
    * This component is generally associated with a table of items where one or more rows in the table can be selected (using checkboxes).
    * The list options determine some processing that wil lbe applied to all checked rows when the form is submitted.
    * @param options => array - associative array of option elements, keyed on the option value
    * @param ucOptions => array - associative array of userclass option groups to display, keyed on the option value prefix
    * @return the HTML for the form component
    */
	function batchoptions($options, $ucOptions=null) {
      $text = "
         <div class='f-left'>
         <img src='".e_IMAGE."generic/branchbottom.gif' alt='' class='TODO' />
			<select class='tbox e-execute-batch' name='execute_batch'>
			<option value=''>With selected...</option>";

		foreach ($options as $key => $val)
		{
		   $text .= "<option value='".$key."'>".$val."</option>";
		}


		if ($ucOptions)
	   {
   		foreach ($ucOptions as $ucKey => $ucVal)
   	   {
            $text .= "<optgroup label='".$ucVal[0]."'>";
      		foreach ($ucVal[1] as $key => $val)
      		{
      			$text .= "<option value='".$ucKey."_selected_".$val['userclass_name']['userclass_id']."'>".$val['userclass_name']['userclass_name']."</option>\n";
      		}
      		$text .= "</optgroup>";
         }
      }

		$text .= "
			</select>
			<button class='update e-hide-if-js' type='submit'><span>Go</span></button>
			</div>
			<span class='clear'>&nbsp;</span>";
		return $text;
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