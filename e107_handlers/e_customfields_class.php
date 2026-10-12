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

		/** @var int|null what the administrator who last saved $_data could store, as {@see e_customfields::authorTrust()} says; null for data saved before values were filtered */
		private $_trust = null;

		private $_field_limit = 30;

		private $_tab = array();

		private $_tab_default = 'additional';


		public function __construct()
		{
			asort($this->_fieldTypes);
			$this->_tab = array($this->_tab_default => 'Additional');

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
		 * @return mixed|string
		 */
		public function getTabLabel()
		{
			return $this->_tab[$this->_tab_default];
		}


		/**
		 * Load the configuration for all custom fields
		 * @param string $data - json custom-field configuration data.
		 * @return $this
		 */
		public function loadConfig($data)
		{
			if(empty($data))
			{
				return $this;
			}

			if(is_array($data))
			{
				if(isset($data['__tabs__']))
				{
					$this->_tab = $data['__tabs__'];
					unset($data['__tabs__']);
				}

				$this->_config = $data;

				return $this;
			}

			$tp = e107::getParser();

			if($arr = $tp->isJSON($data))
			{

				if(!empty($arr['__tabs__']))
				{
					$this->_tab = $arr['__tabs__'];
					unset($arr['__tabs__']);
				}

				$this->_config = $arr;
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
			$this->_data = e107::unserialize($data);
			$this->_trust = null;

			if(is_array($this->_data) && isset($this->_data['__trust__']))
			{
				$this->_trust = in_array($this->_data['__trust__'], array(0, 1, 2), true) ? $this->_data['__trust__'] : null;
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

			$text = is_string($value) ? $tp->attributeValue($value) : $value;
			$shown = ($this->_trust === null) ? $text : $value;
			$label = ($this->_trust === 2) ? $this->asScriptAuthorWrote($value) : $text;
			$ret = null;

			switch($fieldType)
			{
				case 'dropdown':
				case 'checkboxes':
				case 'radio':
					$ret = ($raw) ? $text : e107::getForm()->renderValue('customfield_'.$key,$this->decoded($value),$this->_config[$key]);
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
					$ret = ($raw) ? $this->linkTo($value) : $tp->toHTML($shown);
					break;

				case 'email':
					$ret = ($raw) ? $text : $tp->toHTML($shown);
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
						$ret = e107::getForm()->progressBar($key,$label,$this->_config[$key]);
					}

					break;

				case 'textarea':
				case 'bbarea':
					if(!$raw)
					{
						$ret = $tp->toHTML($shown, true, 'BODY');
					}
					elseif($this->_trust === 1)
					{
						$ret = $tp->toHTML($value, false, 'WYSIWYG');
					}
					else
					{
						$ret = ($this->_trust === 2) ? $label : $shown;
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
					$ret = ($raw) ? $text : $tp->toHTML($shown);
			}


			return $ret;
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
		 * @param mixed $key
		 * @return bool whether $key can name a field, as the configuration form's pattern allows and the reserved '__tabs__' and '__trust__' do not
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
		 * @return mixed|null
		 */
		public function getFieldTitle($key)
		{

			if(!empty($this->_config[$key]['title']))
			{
				return $this->_config[$key]['title'];
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
				".$frm->text('__e_customfields_tabs__', $this->_tab[$this->_tab_default], 30, array('placeholder' => 'Tab label', 'size' =>'medium', 'required' =>1))."
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
				$fieldTitle = $frm->text($name . '[' . $i . '][title]', varset($value[$i]['title']), 80);
				$fieldType = $frm->select($name . '[' . $i . '][type]', $this->getFieldTypes(), varset($value[$i]['type']), $writeParms);
				$fieldParms = $frm->text($name . '[' . $i . '][writeParms]', varset($value[$i]['writeParms']), 255, $parmsWriteParms);
				$fieldHelp = $frm->text($name . '[' . $i . '][help]', varset($value[$i]['help']), 255, array('size' => 'block-level'));
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
			$ui->addTab($tabKey, $this->_tab[$tabKey]);


			foreach($this->_config as $key=>$fld)
			{
				$fld['tab'] = $tabKey;
				$fld['data'] = false;

				if($fld['type'] === 'icon')
				{
					$fld['writeParms'] .= '&glyphs=1';
				}

				if($fld['type'] === 'checkboxes')
				{
					if($tmp = e107::getParser()->isJSON($fld['writeParms']))
					{
						$fld['writeParms'] = $tmp;
					}

					$fld['writeParms']['useKeyValues'] = 1;
				}

				$fields[$fieldName.'__'.$key] = $fld;

			}

			$ui->setFieldAttr($fields);

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
				$ui->getModel()->set($fieldname.'__'.$key, ($this->_trust === null) ? e107::getParser()->toDB($value) : $value);
			//	e107::getDebug()->log($fieldname.'__'.$key.": ".$value);
			}

			return $this;


		}


		/**
		 * Process Posted form data and compiled Configuration Form data if found.
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


			$new = array();

			if(!empty($postData['__e_customfields_tabs__']))
			{
				$new['__tabs__'] = array($this->_tab_default =>	$postData['__e_customfields_tabs__']);
				unset($postData['__e_customfields_tabs__']);
			}

			foreach($postData[$fieldname] as $fields)
			{




				if(empty($fields['key']) || empty($fields['type']))
				{
					continue;
				}


				$key = $fields['key'];
				unset($fields['key']);
				$new[$key] = $fields;


			}

			$postData[$fieldname] = empty($new) ? null : $new;

			return $postData;

		}


		/**
		 * Process all posted data and compile into a single field array.
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

			unset($new_data[$fieldname]); // Reset.

			$len = strlen($fieldname);

			foreach($new_data as $k=>$v)
			{
				if(strpos($k, $fieldname) === 0)
				{
					list($tmp,$newkey) = array_pad(explode('__', $k), 2, null);

					if($this->isFieldKey($newkey))
					{
						$new_data[$fieldname][$newkey] = e107::getParser()->toDB($v);
					}

					unset($new_data[$k]);


				}

			}

			if(!empty($new_data[$fieldname]))
			{
				$new_data[$fieldname]['__trust__'] = $this->authorTrust();
			}

			return $new_data;

		}



	}