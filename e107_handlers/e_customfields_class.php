<?php


	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2017 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */
	class e_customfields
	{
		private $_fieldTypes = array(
			'number', 'email', 'url', 'password', 'text', 'tags', 'textarea',
			'bbarea', 'image', 'file', 'icon', 'datestamp', 'checkboxes', 'dropdown', 'radio',
			'userclass', 'user', 'boolean', 'checkbox', 'hidden', 'lanlist', 'language', 'country', 'video', 'progressbar'

		);

		private $_config = array();

		private $_data = array();

		/**
		 * @var array what the administrator who wrote each value of $_data could store, as
		 *            {@see e_customfields::authorTrust()} says, keyed by field; none for a value saved before values
		 *            were filtered
		 */
		private $_trust = array();

		/**
		 * @var array the same for each title, help text and parameters of $_config, keyed by field and part, and for
		 *            the tab label under '__tabs__'
		 */
		private $_configTrust = array();

		private $_field_limit = 30;

		private $_tab = array();

		private $_tab_default = 'additional';

		private $_tab_label = 'Additional';

		private $_writeParms = array('optArray', 'useValues', 'default', 'format', 'type', 'size', 'rows', 'placeholder', 'maxlength',
			'multiple', 'min', 'max', 'step', 'decimals');


		public function __construct()
		{
			asort($this->_fieldTypes);
			$this->_tab = array($this->_tab_default => $this->_tab_label);

		}

		/**
		 * @return string[]
		 */
		public function getFieldTypes()
		{

			return $this->_fieldTypes;
		}

		/**
		 * @return string
		 */
		public function getTabId()
		{
			return $this->_tab_default;
		}

		/**
		 * @return string the tab's label, encoded for HTML unless its author's classes decided how it was stored
		 */
		public function getTabLabel()
		{
			return $this->tabLabel($this->_tab_default);
		}


		/**
		 * Load the configuration for all custom fields
		 * @param string $data - json custom-field configuration data.
		 * @return $this
		 */
		public function loadConfig($data)
		{
			$this->_config = array();
			$this->_configTrust = array();

			if(empty($data))
			{
				return $this;
			}

			if(is_array($data))
			{
				if(isset($data['__tabs__']))
				{
					$this->_tab = $this->tabsFrom($data['__tabs__']);
					unset($data['__tabs__']);
				}

				$this->_config = $this->withoutTrust($data);

				return $this;
			}

			$tp = e107::getParser();

			if($arr = $tp->isJSON($data))
			{

				if(!empty($arr['__tabs__']))
				{
					$this->_tab = $this->tabsFrom($arr['__tabs__']);
					unset($arr['__tabs__']);
				}

				$this->_config = $this->withoutTrust($arr);
			}


			// e107::getDebug()->log($this->_config);


			return $this;
		}


		/**
		 * Load a set of custom-field data for the current configuration.
		 * @param string $data json custom-field form data.
		 * @return $this
		 */
		public function loadData($data)
		{
			$data = e107::unserialize($data);
			$this->_data = is_array($data) ? $data : array();
			$this->_trust = array();

			if(isset($this->_data['__trust__']))
			{
				$this->_trust = $this->_data['__trust__'];
				unset($this->_data['__trust__']);
			}

		//	e107::getDebug()->log($this->_data);

			return $this;
		}


		/**
		 * @return array
		 */
		public function getConfig()
		{
			return $this->_config;
		}


		/**
		 * @return array
		 */
		public function getData()
		{
			return $this->_data;
		}


		/**
		 * @param $key
		 * @param $label
		 * @return $this
		 */
		public function setTab($key, $label)
		{
			$this->_tab = array((string) $key => (string) $label);

			return $this;
		}


		/**
		 * @param $key
		 * @param $parm
		 * @return array|bool|mixed|string|null
		 */
		public function getFieldValue($key, $parm=array())
		{
			$tp = e107::getParser();

			$value = $this->_data[$key];
			$raw = (!empty($parm['mode']) && $parm['mode'] === 'raw');
			$type = (!empty($parm['type'])) ? $parm['type'] : null;

			$fieldType = $this->_config[$key]['type'];

			if($value === null)
			{
				return null;
			}

			$trust = $this->levelIn($this->_trust, $key);
			$text = is_string($value) ? $tp->attributeValue($value) : $value;
			$label = ($trust === 2) ? $this->asScriptAuthorWrote($value) : $text;
			$ret = null;

			switch($fieldType)
			{
				case 'dropdown':
				case 'checkboxes':
				case 'radio':
					$ret = ($raw) ? $text : e107::getForm()->renderValue('customfield_'.$key,$this->decoded($value),$this->definition($key));
				break;

				case 'video':

					if(!empty($value))
					{
						$ret = ($raw) ? 'https://www.youtube.com/watch?v='.str_replace('.youtube', '', $text) : $tp->toVideo($value);
					}
				break;



				case 'image':
					if(!empty($value))
					{
						$ret = ($raw) ? $this->linkTo($tp->thumbUrl($value,$parm)) : $tp->toImage($value,$parm);
					}
					break;

				case 'icon':
					$ret = ($raw) ? str_replace('.glyph', '', $text) : $tp->toIcon($label);
					break;

				case 'country':
					$ret = ($raw) ? $text : e107::getForm()->getCountry($value);
					break;

				case 'tags':
					$ret = ($raw) ? $text : $tp->toLabel($label,$type);
					break;

				case 'lanlist':
				case 'language':
					$ret = ($raw) ? $text : e107::getLanguage()->convert($value);
					break;

				case 'datestamp':
					$ret = ($raw) ? $text : $tp->toDate($value);
					break;

				case 'file':
					if(!empty($value))
					{
						$ret = ($raw) ? $tp->toFile($text, array('raw'=>1)) : $tp->toFile($text);
					}
					break;

				case 'url':
					$ret = ($raw) ? $this->linkTo($value) : $tp->toHTML($this->atLevel($value, $trust));
					break;

				case 'email':
					$ret = ($raw) ? $text : $tp->toHTML($this->atLevel($value, $trust));
					break;

				case 'user':
					$ret = ($raw) ? $text : e107::getSystemUser($value,true)->getName();
					break;

				case 'userclass':
					$ret = ($raw) ? $text : e107::getUserClass()->getName($value);
					break;

				case 'progressbar':
					if($raw)
					{
						$ret = (strpos($text, '/') === false) ? $text.'%' : $text;
					}
					else
					{
						$ret = e107::getForm()->progressBar($key,$label,$this->definition($key));
					}

					break;

				case 'textarea':
				case 'bbarea':
					$html = $this->atLevel($value, $trust);

					if(!$raw)
					{
						$ret = $tp->toHTML($html, true, 'BODY');
					}
					elseif($trust === 1 || $trust === null)
					{
						$ret = $tp->toHTML($html, false, 'WYSIWYG');
					}
					else
					{
						$ret = ($trust === 2) ? $label : $html;
					}
					break;


				case 'boolean':
					if($raw)
					{
						return $text;
					}
					else
					{
						$ret = empty($value) ? $tp->toGlyph('fa-times') : $tp->toGlyph('fa-check');
					}
					break;

				case 'checkbox':
					if($raw)
					{
						$ret = $text;
					}
					elseif(is_numeric($value))
					{
						$ret = empty($value) ? $tp->toGlyph('fa-times') : $tp->toGlyph('fa-check');
					}
					else
					{
						$ret = $label;
					}
					break;

				default:
					$ret = ($raw) ? $text : $tp->toHTML($this->atLevel($value, $trust));
			}


			return $ret;
		}


		/**
		 * @param mixed $value a value as stored
		 * @param int|null $trust the level recorded for it, null for none
		 * @return mixed the value as an author at that level stored it: one with no level, stored as it was posted, as
		 *               {@see e_parse::toDBWithoutScript()} stores it for an author who may post HTML but not script
		 */
		private function atLevel($value, $trust)
		{
			return ($trust === null) ? e107::getParser()->toDBWithoutScript($value) : $value;
		}


		/**
		 * @param mixed $value as {@see e_parse::toDB()} stored it for an author with script access
		 * @return mixed the markup that author wrote, with the quotes, dollar signs and backslashes
		 *               {@see e_parse::toDB()} encoded, and a quote stored as an entity inside an attribute value kept
		 *               as one
		 */
		private function asScriptAuthorWrote($value)
		{
			return is_string($value) ? strtr($value, array('&quot;' => '"', '&#039;' => "'", '&#036;' => '$', '&#092;' => '\\', '&amp;quot;' => '&quot;', '&amp;#039;' => '&#039;')) : $value;
		}


		/**
		 * @return int what the current user's values may hold once {@see e_parse::toDB()} has stored them: 2 scripts
		 *             and HTML (post_script), 1 HTML (post_html), 0 text
		 */
		private function authorTrust()
		{
			$pref = e107::getConfig();

			if(check_class($pref->get('post_script', e_UC_NOBODY)))
			{
				return 2;
			}

			return check_class($pref->get('post_html', e_UC_MAINADMIN)) ? 1 : 0;
		}


		/**
		 * @param mixed $value a stored value, or an array of them
		 * @return mixed the value with the entities {@see e_parse::toDB()} wrote turned back into characters
		 */
		private function decoded($value)
		{
			if(is_array($value))
			{
				return array_map(array($this, 'decoded'), $value);
			}

			return is_string($value) ? html_entity_decode($value, ENT_QUOTES, 'UTF-8') : $value;
		}


		/**
		 * The parts of a field's stored configuration a form or a page reads, encoded to print, or null for a type this
		 * class does not render.
		 *
		 * @param string $key
		 * @return array|null
		 */
		private function definition($key)
		{
			$fld = isset($this->_config[$key]) ? $this->_config[$key] : null;

			if(!is_array($fld) || !in_array(varset($fld['type']), $this->_fieldTypes, true))
			{
				return null;
			}

			$writeParms = $this->parameters(varset($fld['writeParms'], array()));
			$parmsTrust = $this->configLevel($key, 'writeParms');

			if($parmsTrust !== 2)
			{
				$writeParms = array_intersect_key($writeParms, array_flip($this->_writeParms));

				if(isset($writeParms['optArray']))
				{
					$options = is_array($writeParms['optArray']) ? array_filter($writeParms['optArray'], 'is_scalar') : array();
					$writeParms['optArray'] = ($fld['type'] === 'dropdown' && !empty($writeParms['useValues'])) ? array_combine($options, $options) : $options;
					unset($writeParms['useValues']);
				}

				if(!isset($writeParms['default']) || !is_string($writeParms['default']) || ($fld['type'] !== 'dropdown' && ($writeParms['default'] !== 'blank' || !in_array($fld['type'], array('radio', 'checkboxes'), true))))
				{
					unset($writeParms['default']);
				}

				$writeParms = $this->encoded($writeParms);
			}

			$definition = array(
				'title'      => $this->printable(varset($fld['title'], ''), $this->configLevel($key, 'title')),
				'type'       => $fld['type'],
				'writeParms' => $writeParms,
				'help'       => $this->printable(varset($fld['help'], ''), $this->configLevel($key, 'help')),
			);

			return ($parmsTrust === 2) ? $definition + $fld : $definition;
		}


		/**
		 * @param mixed $value a title, help text or tab label as the configuration stores it
		 * @param int|null $trust what its author could post, as {@see e_customfields::authorTrust()} says, or null for
		 *                        none recorded
		 * @return mixed HTML for an author who could post it when it was stored, otherwise encoded text
		 */
		private function printable($value, $trust)
		{
			if(empty($trust) || !is_string($value))
			{
				return $this->encoded($value);
			}

			return ($trust === 2) ? $this->asScriptAuthorWrote($value) : e107::getParser()->toHTML($value, false, 'TITLE,scparse_off,defs_off');
		}


		/**
		 * @param array $config a stored configuration
		 * @return array the configuration without its '__trust__' record, which sets $_configTrust
		 */
		private function withoutTrust(array $config)
		{
			if(isset($config['__trust__']))
			{
				$this->_configTrust = $config['__trust__'];
				unset($config['__trust__']);
			}

			return $config;
		}


		/**
		 * @param mixed $tabs the stored '__tabs__' setting
		 * @return array the field tab's id and label, the id always the default one
		 */
		private function tabsFrom($tabs)
		{
			$label = is_array($tabs) && isset($tabs[$this->_tab_default]) ? $tabs[$this->_tab_default] : null;

			return array($this->_tab_default => is_string($label) ? $label : $this->_tab_label);
		}


		/**
		 * @param mixed $value a string, or an array whose string values are encoded at every depth and whose keys
		 *                     are kept
		 * @return mixed
		 */
		private function encoded($value)
		{
			$tp = e107::getParser();

			if(is_array($value))
			{
				return array_map(array($this, 'encoded'), $value);
			}

			return is_string($value) ? $tp->attributeValue($value) : $value;
		}


		/**
		 * @param mixed $levels levels as {@see e_customfields::authorTrust()} gives them, keyed as the values they were
		 *                      recorded for
		 * @param string $key
		 * @return int|null the level recorded for $key, or null for none
		 */
		private function levelIn($levels, $key)
		{
			$level = (is_array($levels) && isset($levels[$key])) ? $levels[$key] : null;

			return in_array($level, array(0, 1, 2), true) ? $level : null;
		}


		/**
		 * @param string $key a field of the loaded configuration
		 * @param string $part 'title', 'help' or 'writeParms'
		 * @return int|null the level recorded for that part of the field, or null for none
		 */
		private function configLevel($key, $part)
		{
			return $this->levelIn(isset($this->_configTrust[$key]) ? $this->_configTrust[$key] : null, $part);
		}


		/**
		 * @param string $part 'title', 'help', 'writeParms' or '__tabs__'
		 * @param mixed $value that setting as stored
		 * @return mixed the setting as the chapter form shows it: a parameter list as stored, any other with its
		 *               entities decoded
		 */
		private function shownSetting($part, $value)
		{
			return ($part === 'writeParms') ? $value : $this->decoded($value);
		}


		/**
		 * @return int|null the level recorded for the tab label, or null for none
		 */
		private function tabLevel()
		{
			return $this->levelIn($this->_configTrust, '__tabs__');
		}


		/**
		 * @param string $key the tab's id
		 * @return mixed the tab's label, encoded for HTML unless its author's classes decided how it was stored
		 */
		private function tabLabel($key)
		{
			return $this->printable($this->_tab[$key], $this->tabLevel());
		}


		/**
		 * @param string $key a field of the loaded data
		 * @param mixed $value its value as stored
		 * @return mixed the value as the page form is given it: as stored when its level is recorded, otherwise through
		 *               {@see e_parse::toDB()}, with every '%' and '+' of a string written as a character reference
		 */
		private function formValue($key, $value)
		{
			$value = ($this->levelIn($this->_trust, $key) === null) ? e107::getParser()->toDB($value) : $value;

			return is_string($value) ? str_replace(array('%', '+'), array('&#37;', '&#43;'), $value) : $value;
		}


		/**
		 * @param string $key a field of the loaded data
		 * @param mixed $value its value as stored
		 * @return mixed the value as the page form shows it: a string as {@see e_parse::toForm()} gives the form it and
		 *               {@see e_form::renderElement()} decodes it, an array decoded
		 */
		private function shownValue($key, $value)
		{
			$value = $this->formValue($key, $value);

			return $this->decoded(is_string($value) ? e107::getParser()->toForm($value) : $value);
		}


		/**
		 * @param mixed $posted a value as posted
		 * @param mixed $stored the value it replaces as stored, null for none
		 * @param mixed $shown that stored value as its form element shows it
		 * @param int|null $level the level recorded for the stored value
		 * @param bool $filtered whether a changed value is stored through {@see e_parse::toDB()} rather than as posted
		 * @param string|null $key the field the value belongs to, null for a chapter setting, which a one-line
		 *                         input shows
		 * @return array the value to store and the level to record for it, null for none
		 */
		private function storedAgain($posted, $stored, $shown, $level, $filtered, $key = null)
		{
			$tp = e107::getParser();

			if($stored !== null && $tp->isUnchangedPost($posted, $shown, $this->elementOf($key)))
			{
				return array($stored, $level);
			}

			return array($filtered ? $tp->toDB($posted) : $posted, $this->isChoice($key) ? null : $this->authorTrust());
		}


		/**
		 * @param string|null $key a field of the loaded configuration, null for a chapter setting
		 * @return bool whether its value is a choice among the options its parameters list, which their author wrote
		 */
		private function isChoice($key)
		{
			return $key !== null && isset($this->_config[$key]['type']) && in_array($this->_config[$key]['type'], array('dropdown', 'radio', 'checkboxes'), true);
		}


		/**
		 * @param string|null $key a field of the loaded configuration, null for a chapter setting, which a one-line
		 *                         input shows
		 * @return array the page form's element for the field's value, as {@see e_parse::isUnchangedPost()} takes it:
		 *               an email input for a one-line input its parameters make one, and a tag list's input without its
		 *               widget when {@see e_customfields::isModelledWidget()} says the parameters set the widget
		 *               up otherwise
		 */
		private function elementOf($key)
		{
			$type = ($key !== null && isset($this->_config[$key]['type']) && is_string($this->_config[$key]['type'])) ? $this->_config[$key]['type'] : 'text';
			$parms = $this->fieldParameters($key);

			if($type === 'tags')
			{
				return $this->isModelledWidget($parms) ? array('type' => 'tags', 'maxItems' => varset($parms['maxItems'])) : array('type' => 'text');
			}

			return array('type' => ($type !== 'textarea' && $type !== 'bbarea' && isset($parms['type']) && $parms['type'] === 'email') ? 'email' : $type);
		}


		/**
		 * @param array $parms a tags field's parameters
		 * @return bool whether {@see e_parse::isUnchangedPost()} reads the tag widget they set up as the widget runs:
		 *              no settings of the widget's own, and a limit that is empty, for the default, or a positive
		 *              whole number
		 */
		private function isModelledWidget(array $parms)
		{
			$limit = isset($parms['maxItems']) ? $parms['maxItems'] : null;

			return !array_key_exists('selectize', $parms) && (empty($limit) || ((is_int($limit) || (is_string($limit) && ctype_digit($limit))) && (int) $limit > 0));
		}


		/**
		 * @param string|null $key a field of the loaded configuration, null for a chapter setting
		 * @return array its parameters as the page form is given them, {@see e_customfields::definition()} says which
		 */
		private function fieldParameters($key)
		{
			$definition = ($key === null) ? null : $this->definition($key);

			return isset($definition['writeParms']) ? $definition['writeParms'] : array();
		}


		/**
		 * @param mixed $writeParms a field's parameters as its configuration stores them
		 * @return array the parameters as {@see e_form::renderElement()} reads them
		 */
		private function parameters($writeParms)
		{
			if(is_string($writeParms))
			{
				$json = e107::getParser()->isJSON($writeParms);

				if(is_array($json))
				{
					return $json;
				}

				parse_str($writeParms, $writeParms);
			}

			return is_array($writeParms) ? $writeParms : array();
		}


		/**
		 * @param array $fields the page form's custom fields, keyed by name
		 * @return array the same, the input of each tag list and user field with an id of its own, which the widget
		 *               {@see e_form} puts on it takes as it takes the first element with its id, and a tag list in a
		 *               text input
		 */
		private function withOwnWidgetInputs(array $fields)
		{
			foreach($fields as $name => $fld)
			{
				$type = isset($fld['type']) ? $fld['type'] : null;

				if($type !== 'tags' && $type !== 'user')
				{
					continue;
				}

				$parms = $this->parameters(isset($fld['writeParms']) ? $fld['writeParms'] : null);
				$id = e107::getForm()->name2id($name).'_'.$type;

				if($type === 'tags')
				{
					unset($parms['type']);
					$parms['id'] = $id;
				}
				else
				{
					$parms['__options'] = array('id' => $id) + $this->parameters(isset($parms['__options']) ? $parms['__options'] : null);
				}

				$fields[$name]['writeParms'] = $parms;
			}

			return $fields;
		}


		/**
		 * @param mixed $key
		 * @return bool whether $key can name a field: letters, digits, '_' and '-', not starting with the '__' of the
		 *              reserved '__tabs__' and '__trust__'
		 */
		private function isFieldKey($key)
		{
			return is_string($key) && strpos($key, '__') !== 0 && preg_match('/^[\w-]+$/u', $key) === 1;
		}


		/**
		 * @param string $url the address as a page would print it, entities and all
		 * @return string the address encoded for an href or src, its quotes and angle brackets percent-encoded so a
		 *                script that reads it back into markup cannot end the attribute; nothing when it still holds an
		 *                entity, which such a script would decode again, or a scheme
		 *                {@see e_parse::toUrlAttribute()} refuses
		 */
		private function linkTo($url)
		{
			$url = html_entity_decode((string) $url, ENT_QUOTES | ENT_HTML5, 'UTF-8');

			if(preg_match('/&#|&[a-z][a-z0-9]*;/i', $url))
			{
				return '';
			}

			$url = str_replace(array('"', "'", '<', '>', '`'), array('%22', '%27', '%3C', '%3E', '%60'), $url);

			return e107::getParser()->toUrlAttribute($url);
		}


		/**
		 * @return string
		 */
		public function renderTest()
		{

				$text = '<table class="table table-bordered table-striped">
			<tr><th>Name</th><th>Title<br /><small>&#123;CPAGEFIELDTITLE: name=x&#125;</small></th><th>Normal<br /><small>&#123;CPAGEFIELD: name=x&#125;</small></th><th>Raw<br /><small>&#123;CPAGEFIELD: name=x&mode=raw&#125;</small></th></tr>';

			foreach($this->_data as $ok=>$v)
			{

				$text .= '<tr><td>' .e107::getParser()->attributeValue($ok). '</td><td>' .$this->getFieldTitle($ok). '</td><td>' .$this->getFieldValue($ok). '</td><td>' .$this->getFieldValue($ok, array('mode' =>'raw')). '</td></tr>';
			}

			$text .= '</table>';

			return $text;



		}


		/**
		 * @param $key
		 * @return string|null the field's title, encoded for HTML unless its author's classes decided how it was stored
		 */
		public function getFieldTitle($key)
		{

			if(!empty($this->_config[$key]['title']))
			{
				return $this->printable($this->_config[$key]['title'], $this->configLevel($key, 'title'));
			}

			return null;
		}


		/**
		 * @param $name
		 * @return string
		 */
		public function renderConfigForm($name)
		{

			$frm = e107::getForm();
			$curVal = $this->_config;
			$value = array();

			if(!empty($curVal))
			{
				$i = 0;
				foreach($curVal as $k=>$v)
				{
					$v['key'] = $k;
					$value[$i] = $v;
					$i++;
				}
			}

			$text = "
			<div class='form-group'>
				".$frm->text('__e_customfields_tabs__', $this->shownSetting('__tabs__', $this->_tab[$this->_tab_default]), 30, array('placeholder' => 'Tab label', 'size' =>'medium', 'required' =>1))."
			</div>
			<table class='table table-striped table-bordered'>
			<colgroup>
				<col />
				<col />
				<col />
				<col style='width:40%' />
			</colgroup>
			<tbody>
				<tr><th>".LAN_NAME. '</th><th>' .LAN_TITLE. '</th><th>' .LAN_TYPE. '</th><th>Params</th><th>' .LAN_TOOLTIP. '</th></tr>
				';

			for($i = 0; $i <= $this->_field_limit; $i++)
			{
				$writeParms = array(
					//	'class' => 'form-control',
					'useValues' => 1,
					'default'   => 'blank',
					'data-src'  => e_REQUEST_URI,

				);

				$parmsWriteParms = array(
					'size'        => 'block-level',
					'placeholder' => isset($value[$i]['type']) ? $this->getCustomFieldPlaceholder($value[$i]['type']) : '',
				);

				$fieldName = $frm->text($name . '[' . $i . '][key]', varset($value[$i]['key']), 30, array('pattern' => '^[a-z0-9-]*'));
				$fieldTitle = $frm->text($name . '[' . $i . '][title]', $this->shownSetting('title', varset($value[$i]['title'])), 80);
				$fieldType = $frm->select($name . '[' . $i . '][type]', $this->getFieldTypes(), varset($value[$i]['type']), $writeParms);
				$fieldParms = $frm->text($name . '[' . $i . '][writeParms]', $this->shownSetting('writeParms', varset($value[$i]['writeParms'])), 255, $parmsWriteParms);
				$fieldHelp = $frm->text($name . '[' . $i . '][help]', $this->shownSetting('help', varset($value[$i]['help'])), 255, array('size' => 'block-level'));
				$text .= '<tr><td>' . $fieldName . '</td><td>' . $fieldTitle . '</td><td>' . $fieldType . '</td><td>' . $fieldParms . '</td><td>' . $fieldHelp . '</td></tr>';
			}

			$text .= '</tbody></table>';


			return $text;

		}


		/**
		 * @param $type
		 * @return null|string
		 */
		private function getCustomFieldPlaceholder($type)
		{
			switch($type)
			{
				case 'radio':
				case 'dropdown':
				case 'checkboxes':
					return 'eg. { "optArray": { "blue": "Blue", "green": "Green", "red": "Red" }, "default": "blank" }';
					break;

				case 'datestamp':
					return 'eg. (Optional) { "format": "yyyy-mm-dd" }';
				break;


				default:

			}


			return null;


		}


		/**
		 *
		 * @param $fieldName
		 * @param e_admin_ui $ui
		 * @return $this
		 */
		public function setAdminUIConfig($fieldName, e_admin_ui &$ui)
		{
			$fields = array();

			$tabKey = key($this->_tab);
			$ui->addTab($tabKey, $this->tabLabel($tabKey));


			foreach(array_keys($this->_config) as $key)
			{
				$fld = $this->definition($key);

				if($fld === null || !$this->isFieldKey($key))
				{
					continue;
				}

				$fld['tab'] = $tabKey;
				$fld['data'] = false;

				if($fld['type'] === 'icon')
				{
					$fld['writeParms']['glyphs'] = 1;
				}

				if($fld['type'] === 'checkboxes')
				{
					$fld['writeParms']['useKeyValues'] = 1;
				}

				$fields[$fieldName.'__'.$key] = $fld;

			}

			$ui->setFieldAttr($this->withOwnWidgetInputs($fields));

			return $this;


		}


		/**
		 * @param $fieldname
		 * @param e_admin_ui $ui
		 * @return $this
		 */
		public function setAdminUIData($fieldname, e_admin_ui &$ui)
		{

			$ui->getModel()->set($fieldname, null);

			foreach($this->_data as $key=>$value)
			{
				$ui->getModel()->set($fieldname.'__'.$key, $this->formValue($key, $value));
			}

			return $this;


		}


		/**
		 * Process Posted form data and compiled Configuration Form data if found, keeping a title, help text,
		 * parameters or tab label posted back as the configuration form showed the configuration
		 * {@see e_customfields::loadConfig()} loaded as it is stored.
		 * @param string $fieldname
		 * @param array $postData all posted data.
		 * @return array
		 */
		public function processConfigPost($fieldname, $postData)
		{

			if(empty($postData[$fieldname]))
			{
				return $postData;
			}


			$trust = $this->authorTrust();
			$new = array();
			$levels = array();

			if(!empty($postData['__e_customfields_tabs__']) && is_string($postData['__e_customfields_tabs__']))
			{
				$posted = $postData['__e_customfields_tabs__'];
				$stored = isset($this->_tab[$this->_tab_default]) ? $this->_tab[$this->_tab_default] : null;
				list($tab, $levels['__tabs__']) = $this->storedAgain($posted, $stored, $this->shownSetting('__tabs__', $stored), $this->tabLevel(), true);
				$new['__tabs__'] = array($this->_tab_default => $tab);
			}

			unset($postData['__e_customfields_tabs__']);

			foreach((array) $postData[$fieldname] as $fields)
			{
				if(!is_array($fields) || empty($fields['key']) || !$this->isFieldKey($fields['key']) || !in_array(varset($fields['type']), $this->_fieldTypes, true))
				{
					continue;
				}

				$key = $fields['key'];
				unset($fields['key']);

				$row = array(
					'title'      => is_string(varset($fields['title'])) ? $fields['title'] : '',
					'type'       => $fields['type'],
					'writeParms' => is_string(varset($fields['writeParms'])) ? $fields['writeParms'] : '',
					'help'       => is_string(varset($fields['help'])) ? $fields['help'] : '',
				);
				$rowLevels = array();

				foreach(array('title', 'writeParms', 'help') as $part)
				{
					$stored = isset($this->_config[$key][$part]) ? $this->_config[$key][$part] : null;
					list($row[$part], $rowLevels[$part]) = $this->storedAgain($row[$part], $stored, $this->shownSetting($part, $stored),
						$this->configLevel($key, $part), $part !== 'writeParms');
				}

				$new[$key] = $row + (($trust === 2) ? $fields : array());
				$levels[$key] = array_filter($rowLevels, 'is_int');
			}

			$levels = array_filter($levels, function ($level) { return $level !== null && $level !== array(); });

			if(!empty($new) && !empty($levels))
			{
				$new['__trust__'] = $levels;
			}

			$postData[$fieldname] = empty($new) ? null : $new;

			return $postData;

		}


		/**
		 * Process all posted data and compile into a single field array, keeping a value posted back as the page form
		 * showed the data {@see e_customfields::loadData()} loaded as it is stored.
		 * @param $fieldname
		 * @param $new_data
		 * @return null
		 * @internal param array $newdata - all posted data.
		 */
		public function processDataPost($fieldname, $new_data)
		{
			if(empty($new_data))
			{
				return null;
			}

			$columnPosted = array_key_exists($fieldname, $new_data);
			unset($new_data[$fieldname]); // Reset.

			$len = strlen($fieldname);
			$levels = array();

			foreach($new_data as $k=>$v)
			{
				if(strpos($k, $fieldname) === 0)
				{
					list($tmp,$newkey) = array_pad(explode('__', $k), 2, null);

					if($this->isFieldKey($newkey))
					{
						$stored = isset($this->_data[$newkey]) ? $this->_data[$newkey] : null;
						list($new_data[$fieldname][$newkey], $levels[$newkey]) = $this->storedAgain($v, $stored, $this->shownValue($newkey, $stored),
							$this->levelIn($this->_trust, $newkey), true, $newkey);
					}

					unset($new_data[$k]);


				}

			}

			$levels = array_filter($levels, 'is_int');

			if(!empty($levels))
			{
				$new_data[$fieldname]['__trust__'] = $levels;
			}

			if($columnPosted && !isset($new_data[$fieldname]))
			{
				$new_data[$fieldname] = null;
			}

			return $new_data;

		}



	}