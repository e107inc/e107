<?php


function cleanField($string)
{
	return htmlspecialchars($string);
}

/*******************/
/* MAIN FORM CLASS */
/*******************/
class e107Form
{
	var $parms, $txt, $fields, $redraw, $validated;

	function e107Form()
	{
		$this->redraw = false;
		$this->parms = array();
		$this->parms['method'] = 'post';
		$this->parms['action'] = e_SELF.'?'.e_QUERY;
		$this->parms['class'] = 'fborder';
		$this->txt = '';
		$this->template = '';
		$this->fields = array();
	}

	function addField($fieldName, $fieldInfo)
	{
		$this->fields[$fieldName] = $fieldInfo;
	}

	function setParm($parm='', $value='')
	{
		$this->parms[$parm] = $value;
	}

	function open()
	{
		$parmStr = '';
		foreach($this->parms as $parm => $value)
		{
			$parmStr .= ($parmStr ? ' ' : '');
			$parmStr .= "{$parm}='{$value}'";
		}
		$this->txt = "<form {$parmStr}>\n";
		return $this->txt;
	}

	function close()
	{
		return '</form>';
	}

	function show()
	{
		$text = $this->open();
		foreach ($this->fields as $f)
		{
			$text .= $f->show();
		}
		$text .= $this->close();
	}

	function render($redraw = false)
	{
		echo 'Rendering..';
		$this->redraw = $redraw;
		$text = $this->open();
		$text .= preg_replace_callback("#\[-(.*?)-]#", array($this, 'replace_fields'), $this->template);
		$text .= $this->close();
		return $text;
	}

	function validateFields($redraw=false)
	{
		$this->validated = true;
		foreach($this->fields as $key => $field)
		{
			$errorText = '';
			if(!$errorText = $field->checkRequired($this))
			{
				$errorText = $field->validate();
			}
			if($errorText)
			{
				$field->config['errorText'] = $errorText; 
				$this->redraw = true;
				$this->validated = false;
			}
		}
		if(!$this->validated && $redraw)
		{
			return $this->render(true);
		}
	}

	function replace_fields($matches)
	{
		switch($matches[1])
		{
			case 'form_open':
				return $this->open();
				break;

			case 'form_close':
				return $this->close();
				break;

			default:
//				print_a($this);
				$tmp = explode('_', $matches[1], 2);
				$fname = $tmp[0];
				if(!isset($tmp[1])) { $tmp[1] = 'field'; }
				switch ($tmp[1])
				{
					case 'error':
					
						if(isset($this->fields[$fname]->config['errorText']))
						{
							$errorText  = varset($this->fields[$fname]->config['errorTextPre']);
							$errorText .= $this->fields[$fname]->config['errorText'];
							$errorText .= varset($this->fields[$fname]->config['errorTextPost']);
							return $errorText;
						}
						else
						{
							return '';
						}
						break;
				
					case 'id':
						return "id='".(isset($this->fields[$fname]->id) ? $this->fields[$fname]->id : $this->fields[$fname]->name.'_id')."'";
						break;

					case 'label_id':
						return "id='".(isset($this->fields[$fname]->label_id) ? $this->fields[$fname]->label_id : $this->fields[$fname]->name.'_label_id')."'";;
						break;
							
					case 'label':
						return $this->fields[$fname]->label;
						break;
					
					case 'field':
						return $this->fields[$fname]->render($this->redraw);
						break;
				}
		}
	}
}


/********************************************************************************************/
/* BEGIN FORM FIELDS ************************************************************************/
/********************************************************************************************/
class e107FormItem
{
	var $parms, $txt, $config, $required, $label;

	function e107FormItem($name='', $label='', $parms=null)
	{
		$this->config = array('errorTextPre' => '<br />');
		$this->label = ($label == '' ? $name : $label);
		$this->name = $name;
		$this->parms = array( 'class' => 'tbox', 'name' => $name );
		$this->required = false;
		if(!is_null($parms))
		{
			if(is_array($parms))
			{
				foreach($parms as $p => $v)
				{ 
					$this->setParm($p, $v);
				}
			}
			else
			{
				$this->parms['value'] = $value;
			}
		}
		$this->init();
	}

	function setParm($parm='', $value='')
	{
		$this->parms[$parm] = $value;
	}

	function setConfig($key='', $value='')
	{
		$this->config[$key] = $value;
	}

	function init()
	{
		return;
	}

	function createParmStr()
	{
		$parmStr = '';
		foreach($this->parms as $parm => $value)
		{
			$parmStr .= " {$parm}='{$value}'";
		}
		return $parmStr;
	}

	function render()
	{
		return $this->txt;
	}
	
	function validate()
	{
		return '';
	}

	function checkRequired(&$form)
	{
		if($this->required === true)
		{ 
			if(!isset($_POST[$this->parms['name']]) || trim($_POST[$this->parms['name']]) == '')
			{
				return (varset($this->config['required']) ? $this->config['required'] : '*Required Field');
			} 
		}
	}
}

class e107FormText extends e107FormItem
{
	function render($redraw = false)
	{
		if($redraw) { $this->redraw(); }
		$this->txt = "<input type='text'".$this->createParmStr().' />';
		return $this->txt;
	}
	
	function redraw()
	{
		$this->parms['value'] = cleanField($_POST[$this->parms['name']]);
	}
	
	function validate()
	{
		
		if(varset($this->config['regexp']))
		{
			if(!preg_match("/{$this->config['regexp']}/", varset($_POST[$this->parms['name']])))
			{
				return varset($this->config['regexpErrorText'], '*Input is not in right format');
			}
		}
		return '';
	}
}

class e107FormPassword extends e107FormText
{
	function render($redraw = false)
	{
		if($redraw) { $this->redraw(); }
		$this->txt = "<input type='password'".$this->createParmStr().' />';
		return $this->txt;
	}
}

class e107FormSelect extends e107FormItem
{
	var $options;

	function addOption($key='', $value='', $selected=false)
	{
		$opt = array();
		$opt['key'] = $key;
		$opt['value'] = ($value ? $value : $key);
		$opt['selected'] = ($selected ? true : false);
		$this->options[] = $opt;
	}

	function addOptions($optionsArray='', $selectedValue = '')
	{
		if(!is_array($optionsArray))
		{
			return '';
		}
		foreach($optionsArray as $option)
		{
			$opt = array();
			if(is_array($option))
			{
				foreach($option as $key => $val)
				{
					$opt['key'] = $key;
					$opt['value'] = $val;
				}
			}
			else
			{
				$opt['key'] = $option;
				$opt['value'] = $option;
			}

			$opt['selected'] = ($opt['key'] == $selectedValue ? true : false);
			$this->options[] = $opt;
		}
	}

	function open()
	{
		$this->txt = '<select '.$this->createParmStr().'/>';
		return $this->txt;
	}

	function close()
	{
		return '</select>';
	}

	function renderOptions($redraw)
	{
		$ret = '';
		foreach($this->options as $key => $option)
		{
			$parms = '';
			if(is_object($option))
			{
//				echo "key = $key<br />";
//				print_a($option);
				$parms = $option->createParmStr();
				if($redraw)
				{
					$sel = ($this->redraw($option->key) ? " selected='selected'" : '');
				}
				else
				{
					$sel = ($option->selected ? " selected='selected'" : '');
				}
				$ret .= "<option {$parms} value='{$option->key}'{$sel}>{$option->value}</option>\n";
			}
			else
			{
//				echo "name = {$this->parms['name']}<br />";
//				echo "key = $key<br />";
//				print_a($option);
				if($redraw)
				{
					$sel = ($this->redraw($option['key']) ? " selected='selected'" : '');
				}
				else
				{
					$sel = ($option['selected'] ? " selected='selected'" : '');
				}
				$ret .= "<option {$parms} value='{$option['key']}'{$sel}>{$option['value']}</option>\n";
			}
		}
		return $ret;
	}
	
	function redraw($val)
	{
		$fname = str_replace('[]', '', $this->parms['name']);
		if(isset($_POST[$fname]))
		{
			if(is_array($_POST[$fname]))
			{
				return in_array($val, $_POST[$fname]);
			}
			else
			{
				return $_POST[$fname] == $val;
			}
		}
	}

	function render($redraw = false)
	{
		$this->txt = $this->open();
		$this->txt .= $this->renderOptions($redraw);
		$this->txt .= $this->close();
		return $this->txt;
	}
}

class e107FormOption extends e107FormItem
{
	var $key, $value, $selected;

	function e107FormItem()
	{
		$this->key = '';
		$this->value = '';
		$this->selected = false;
	}

	function addOption(&$select)
	{
		if(!$this->key)
		{
			$this->key = $this->value;
		}
		$select->options[] = $this;
	}

}

class e107FormTextarea extends e107FormText
{
	var $text;

	function init()
	{
		$this->text = '';
	}

	function render($redraw = false)
	{
		if($redraw) { $this->redraw(); }
		$this->txt = '<textarea '.$this->createParmStr().">{$this->text}</textarea>";
		return $this->txt;
	}

	function redraw()
	{
		$this->text = cleanField($_POST[$this->parms['name']]);
	}

}

class e107FormCheckbox extends e107FormItem
{
	var $checked;
	function init()
	{
		$this->checked = false;
	}

	function render($redraw = false)
	{
		if($redraw) { $this->redraw(); }
		$chk = ($this->checked ? " checked='checked'" : '');
		$this->txt = "<input type='checkbox' ".$this->createParmStr()."{$chk} />";
		return $this->txt;
	}
	
	function redraw()
	{
		$this->checked = isset($_POST[$this->parms['name']]);
	}
}

class e107FormRadio extends e107FormCheckbox
{
	function render($redraw)
	{
		if($redraw) { $this->redraw(); }
		$chk = ($this->checked ? " checked='checked'" : '');
		$this->txt = "<input type='radio' ".$this->createParmStr()."{$chk} />";
		return $this->txt;
	}

	function redraw()
	{
		$this->checked = (varset($_POST[$this->parms['name']]) == $this->parms['value']);
	}
}

class e107FormFile extends e107FormText
{
	function render()
	{
		$this->txt = "<input type='file'".$this->createParmStr().' />';
		return $this->txt;
	}
}

class e107FormHidden extends e107FormText
{
	function init()
	{
		unset($this->parms['class']);
	}
	function render()
	{
		$this->txt = "<input type='hidden'".$this->createParmStr().' />';
		return $this->txt;
	}
}

class e107FormButton extends e107FormItem
{
	function init()
	{
		$this->parms['class'] = 'button';
	}

	function render()
	{
		$this->txt = "<input type='button'".$this->createParmStr().' />';
		return $this->txt;
	}
}

class e107FormSubmit extends e107FormButton
{
	function render()
	{
		$this->txt = "<input type='submit'".$this->createParmStr().' />';
		return $this->txt;
	}
}

class e107FormReset extends e107FormButton
{
	function render()
	{
		$this->txt = "<input type='reset'".$this->createParmStr().' />';
		return $this->txt;
	}
}

