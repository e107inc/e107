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
			$text .= $f->render();
		}
		$text .= $this->close();
	}


	function render($template, $redraw = false)
	{
		$this->redraw = $redraw;
		return preg_replace_callback("#\[-(.*?)-]#", array($this, 'replace_fields'), $template);
	}

	function validateFields()
	{
		$this->validated = true;
		foreach($this->fields as $key => $field)
		{
			if($errorText = $field->validate())
			{
				$field->config['errorText'] = $errorText; 
				$this->redraw = true;
				$this->validated = false;
			}
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

			default :
				if(substr($matches[1], -6) == '_error')
				{
					$fname = substr($matches[1], 0, -6);
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
				}
				else
				{
					return $this->fields[$matches[1]]->render($this->redraw);
				}
				break;
		}
	}
}


/*********************/
/* BEGIN FORM FIELDS */
/*********************/
class e107FormItem
{
	var $parms, $txt, $config;

	function e107FormItem($name='', $value=null)
	{
		$this->config = array('errorTextPre' => '<br />');
		$this->parms = array();
		$this->parms = array( 'class' => 'tbox', 'name' => $name );
		if(!is_null($value))
		{
			$this->parms['value'] = $value;
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
		if(varset($this->config['required']))
		{ 
			if(!isset($_POST[$this->parms['name']]) || trim($_POST[$this->parms['name']]) == '')
			{
				return $this->config['required'];
			} 
		}
		
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


class e107FormTable extends e107FormItem
{
	var $headerlist = array();
	var $rowlist = array();

	function open()
	{
		return '<table>';		// Add parameters to this
	}

	function close()
	{
		return '</table>';
	}

	function render()
	{
		$text = $this->open();
		foreach ($this->headerlist as $h)
		{
			$text .= $h->render();
		}
		foreach ($this->rowlist as $r)
		{
			$text .= $r->render();
		}
		$text .= $this->close();
	}
}


class e107FormRow extends e107FormItem
{
	var $rows = array();
	function open()
	{
		return '<tr>';		// Add parameters to this
	}

	function close()
	{
		return '</tr>';
	}

	function render()
	{
		$text = $this->open();
		foreach ($this->rows as $r)
		{
			$text .= $r->render();
		}
		$text .= $this->close();
	}
}


class e107FormCell extends e107FormItem
{
	function open()
	{
		return '<td>';		// Add parameters to this
	}

	function close()
	{
		return '</td>';
	}

	function render()
	{
		$text = $this->open();
		$text .= $this->txt;
		$text .= $this->close();
	}
}


// Header cell - same as ordinary cell apart from open() and close() text
class e107FormHeaderCell extends e107FormCell
{
	function open()
	{
		return '<th>';		// Add parameters to this
	}

	function close()
	{
		return '</th>';
	}

}
