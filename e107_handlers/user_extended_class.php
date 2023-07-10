<?php
/*
* e107 website system
*
* Copyright (C) 2008-2012 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Extended user field handler
*
*/

if (!defined('e107_INIT')) { exit; }

/**
 *	Extended user field handler
 *
 *	@todo: - change some routines to access the cached variables rather than DB
 *	@todo: Remove setting up of _FIELD_TYPES array (may be necessary, since UEF data structure not fixed)
 *	@todo: Consider changing field type constants to class constants
 *	@todo - cache field structure (already done in a different way in e107::user() in class2.php line 1387 or so)
 *	@todo - class variables - confirm whether public/protected assignments are correct
 *	@todo - consider whether to split system and non-system fields

Code uses two tables:
	user_extended_struct - individual field definitions, one record per field
	user_extended - actual field data, one record per user

@todo: Should user_extended_validate_entry() check DB for DB-type fields?

*/

e107::coreLan('user_extended');


/**
 *
 */
class e107_user_extended
{
	public $user_extended_types;	// Text description corresponding to each field type
	private $extended_xml 		= FALSE;
	public $typeArray;				// Cross-reference between names of field types, and numeric ID (must be public)
	private $reserved_names;		// List of field names used in main user DB - not allowed in extended DB
	public $fieldDefinitions    = array();	// Array initialised from DB by constructor - currently all fields
	public $catDefinitions;			// Categories
	private $nameIndex          = array();	// Array for field name lookup - initialised by constructor
	public $systemCount         = 0;		// Count of system fields - always zero ATM
	public $userCount           = 0;			// Count of non-system fields
	private $fieldAttributes   	= array(); // Field Permissionss with field name as key.
	private $catAttributes      = array();
	private $lastError;

	public function __construct()
	{
		$constants = array(
			'EUF_CATEGORY'     => 0,
			'EUF_TEXT'         => 1,
			'EUF_RADIO'        => 2,
			'EUF_DROPDOWN'     => 3,
			'EUF_DB_FIELD'     => 4,
			'EUF_TEXTAREA'     => 5,
			'EUF_INTEGER'      => 6,
			'EUF_DATE'         => 7,
			'EUF_LANGUAGE'     => 8,
			'EUF_PREDEFINED'   => 9,    // should be EUF_LIST IMO
			'EUF_CHECKBOX'     => 10,
			'EUF_PREFIELD'     => 11,   // should be EUF_PREDEFINED               => useful when creating fields from e.g. plugin XML
			'EUF_ADDON'        => 12,   // defined within e_user.php addon @todo
			'EUF_COUNTRY'      => 13,   // $frm->country()
			'EUF_RICHTEXTAREA' => 14,   // $frm->bbarea()
		);
		
		foreach($constants as $def => $val)
		{
			if(!defined($def))
			{
				define($def, $val);
			}
		}
		

		$this->typeArray = array(
			'text'          => EUF_TEXT,
			'radio'         => EUF_RADIO,
			'dropdown'      => EUF_DROPDOWN,
			'db field'      => EUF_DB_FIELD,
			'textarea'      => EUF_TEXTAREA,
			'integer'       => EUF_INTEGER,
			'date'          => EUF_DATE,
			'language'      => EUF_LANGUAGE,
			'list'          => EUF_PREDEFINED,
			'checkbox'	    => EUF_CHECKBOX,
			'predefined'    => EUF_PREFIELD, // DON'T USE IT IN PREDEFINED FIELD XML!!! Used in plugin installation routine.
			'addon'         => EUF_ADDON,
			'country'       => EUF_COUNTRY,
			'richtextarea' 	=> EUF_RICHTEXTAREA, 
		);

		$this->user_extended_types = array(
			1 	=> UE_LAN_1,
			2 	=> UE_LAN_2,
			3 	=> UE_LAN_3,
			4 	=> UE_LAN_4,
			5 	=> UE_LAN_5,
			14 	=> UE_LAN_14,
			6 	=> UE_LAN_6,
			7 	=> LAN_DATE,
			8 	=> UE_LAN_8,
			9 	=> UE_LAN_9,
			10 	=> UE_LAN_10,
			13 	=> UE_LAN_13,
		//	12=> UE_LAN_10
			
		);

		//load array with field names from main user table, so we can disallow these
		// user_new, user_timezone deleted for 0.8
		$this->reserved_names = array (
		'id', 'name', 'loginname', 'customtitle', 'password',
		'sess', 'email', 'signature', 'image', 'hideemail',
		'join', 'lastvisit', 'currentvisit', 'chats',
		'comments', 'forums', 'ip', 'ban', 'prefs', 'viewed',
		'visits', 'admin', 'login', 'class', 'baseclasslist', 'perms', 'pwchange',
		'xup'
		);

		$this->init();

	}

	/**
	 * @return null
	 */
	public function init()
	{
		$sql = e107::getDb('ue');

		// Read in all the field and category fields
		// At present we load all fields into common array - may want to split system and non-system
		$this->catDefinitions = array();		// Categories array
		$this->nameIndex = array();				// Index of names => field IDs
		$this->systemCount = 0;
		$this->userCount = 0;

		if($sql->select('user_extended_struct', '*', "user_extended_struct_text != '_system_' ORDER BY user_extended_struct_order ASC"))
		{
			while($row = $sql->fetch())
			{


				if ($row['user_extended_struct_type'] == 0)
				{	// Its a category
					$id = $row['user_extended_struct_name'];
					$this->catDefinitions[$row['user_extended_struct_id']] = $row;
					$this->catAttributes[$id] = array(
						'read'          => (int) $row['user_extended_struct_read'],
						'write'         => (int) $row['user_extended_struct_write'],
						'applicable'    => (int) $row['user_extended_struct_applicable'],
					);
				}
				else
				{	// Its a field definition
					$id = 'user_' . $row['user_extended_struct_name'];

					$row['user_extended_struct_parent'] = (int) $row['user_extended_struct_parent'];

					$this->fieldDefinitions[$row['user_extended_struct_id']] = $row;


					$this->fieldAttributes[$id] = array(
							'read'          => (int) $row['user_extended_struct_read'],
							'write'         => (int) $row['user_extended_struct_write'],
							'type'          => $row['user_extended_struct_type'],
							'values'        => $row['user_extended_struct_values'],
							'parms'         => $row['user_extended_struct_parms'],
							'applicable'    => (int) $row['user_extended_struct_applicable'],
							'required'      => (int) $row['user_extended_struct_required'],
					);

					$this->nameIndex['user_' . $row['user_extended_struct_name']] = $row['user_extended_struct_id'];            // Create name to ID index

					if($row['user_extended_struct_text'] == '_system_')
					{
						$this->systemCount++;
					}
					else
					{
						$this->userCount++;
					}
				}
			}
		}

		return null;
	}





	/**
	 * Check read/write access on extended user-fields
	 * @param string $field eg. user_something
	 * @param string $type read|write|applicable
	 * @return boolean true if
	 */
	public function hasPermission($field, $type='read', $classList=null)
	{
		if($classList === null)
		{
			$classList = USERCLASS_LIST;
		}

		if(!isset($this->fieldAttributes[$field][$type]))
		{
			trigger_error('$this->fieldAttributes['.$field.']['.$type.'] was not set', E_USER_NOTICE);
		}

		$class = $this->fieldAttributes[$field][$type];
		return check_class($class, $classList);
	}



	/**
	 *	Check for reserved field names.
	 *	(Names which clash with the 'normal' user table aren't allowed)
	 *	@param array $name - name of field bweing checked (no 'user_' prefix)
	 *	@return boolean TRUE if disallowed name
	 */
	public function user_extended_reserved($name)
	{
		return (in_array($name, $this->reserved_names));
	}



	// Adds the _FIELD_TYPES array to the data, ready for saving in the DB.

	/**
	 * @param $target
	 * @return void
	 */
	function addFieldTypes(&$target)
	{
		$target['_FIELD_TYPES'] = array();		// We should always want to recreate the array, even if it exists
		foreach ($target['data'] as $k => $v)
		{

		//	if (isset($this->nameIndex[$k]))
		//	{
				if($type = $this->getDbFieldType($k))
				{
					$target['_FIELD_TYPES'][$k] = $type;
				}
		/*		switch ($this->fieldDefinitions[$this->nameIndex[$k]]['user_extended_struct_type'])
				{
					case EUF_TEXT :
					case EUF_DB_FIELD :
					case EUF_TEXTAREA :
					case EUF_RICHTEXTAREA :
					case EUF_DROPDOWN :
					case EUF_DATE :
					case EUF_LANGUAGE :
					case EUF_PREDEFINED :

					case EUF_RADIO :
						$target['_FIELD_TYPES'][$k] = 'todb';
						break;

					case EUF_CHECKBOX :
						$target['_FIELD_TYPES'][$k] = 'array';
						break;


					case EUF_INTEGER :
						$target['_FIELD_TYPES'][$k] = 'int';
						break;
				}*/
		//	}
		}
	}

	/**
	 * Given the field name, returns the database FIELD_TYPE
	 * @param $fieldname
	 * @return string|null
	 */
	public function getDBFieldType($fieldname)
	{
		if(strpos($fieldname, 'user_') !== 0)
		{
		//	$fieldname = 'user_'. $fieldname;
			var_dump($fieldname);
		}

		if (!isset($this->nameIndex[$fieldname]))
		{
			return null;
		}

		$ret = null;

		$index = $this->nameIndex[$fieldname];

		$type = $this->fieldDefinitions[$index]['user_extended_struct_type'];

		$ret = null;

		switch($type)
		{
			case EUF_TEXT :
			case EUF_DB_FIELD :
			case EUF_TEXTAREA :
			case EUF_RICHTEXTAREA :
			case EUF_DROPDOWN :
			case EUF_DATE :
			case EUF_LANGUAGE :
			case EUF_PREDEFINED :
			case EUF_RADIO :
				$ret = 'todb';
				break;

			case EUF_CHECKBOX :
				$ret = 'array';
				break;

			case EUF_INTEGER :
				$ret = 'int';
				break;

			// admin-ui format 'data' ie. 'str', 'int', 'array';
			case EUF_ADDON :
				$ret = 'JSON';

				if(!empty($this->fieldDefinitions[$index]['user_extended_struct_values']))
				{
					if($tmp = e107::unserialize($this->fieldDefinitions[$index]['user_extended_struct_values']))
					{
						if(isset($tmp['data']))
						{
							$ret = $tmp['data'];
						}

					}

				}

				break;
			}


		return $ret;

	}

	/**
	 * For all UEFs not in the target array, adds the default value
	 * Also updates the _FIELD_TYPES array, so call this last thing before writing to the DB
	 *
	 *	@param $target - pointer to data array
	 */
	public function addDefaultFields(&$target)
	{
		//$target['_FIELD_TYPES'] = array();		// We should always want to recreate the array, even if it exists
		foreach ($this->fieldDefinitions as $k => $defs)
		{
			$f = 'user_'.$defs['user_extended_struct_name'];
			if (!isset($target['data'][$f]) && $this->fieldDefinitions[$k]['user_extended_struct_default'])
			{
				switch ($this->fieldDefinitions[$k]['user_extended_struct_type'])
				{
					case EUF_TEXT :
					case EUF_DB_FIELD :
					case EUF_TEXTAREA :
					case EUF_RICHTEXTAREA :
					case EUF_DROPDOWN :
					case EUF_DATE :
					case EUF_LANGUAGE :
					case EUF_PREDEFINED :

						$target['data'][$f] = $this->fieldDefinitions[$k]['user_extended_struct_default'];
						$target['_FIELD_TYPES'][$f] = 'todb';
						break;
					case EUF_RADIO :
					case EUF_INTEGER :
						$target['data'][$f] = $this->fieldDefinitions[$k]['user_extended_struct_default'];
						$target['_FIELD_TYPES'][$f] = 'int';
						break;
					case EUF_CHECKBOX :
                    	$target['data'][$f] = $this->fieldDefinitions[$k]['user_extended_struct_default'];
						$target['_FIELD_TYPES'][$f] = 'array';
						break;
				}
			}
		}
	}



	// Validate a single extended user field
	// $val is whatever the user entered.
	// $params is the field definition
	// Return FALSE if acceptable, TRUE if fail , error message on regex fail if the message is defined
	/**
	 * @param $val
	 * @param $params
	 * @return array|bool|mixed|string|string[]
	 */
	function user_extended_validate_entry($val, $params)
	{
		$tp = e107::getParser();

		$parms = explode('^,^', $params['user_extended_struct_parms']);
		$requiredField = $params['user_extended_struct_required'] == 1;
		$regex = $tp->toText($parms[1]);
		$regexfail = $tp->toText($parms[2]);
		if(defined($regexfail))
		{
			$regexfail = constant($regexfail);
		}
		if($val == '' && $requiredField)
		{
			return true;
		}

		$type = $params['user_extended_struct_type'];

		switch($type)
		{
			case EUF_DATE :
				if($requiredField && ($val == '0000-00-00'))
				{
					return true;
				}
				break;
		}
		if($regex != "" && $val != "")
		{
			if(!preg_match($regex, $val))
			{
				return $regexfail ? $regexfail : true;
			}
		}

		return false;            // Pass by default here
	}



	/**
	 * Validate all user-modifable extended user fields which are presented.
	 *	Primarily intended to validate data entered by a user or admin
	 *
	 * @param array $inArray is the input data (usually from $_POST or $_POST['ue'], although doesn't have to be) - may have 'surplus' values
	 * @param array $hideArray is a set of possible 'hide' flags
	 * @param boolean $isSignup TRUE causes required fields to be specifically checked, else only data passed is checked
	 *
	 *	@return array with three potential subkeys:
	 *		'data' - valid data values (key is field name)
	 *			['data']['user_hidden_fields'] is the hidden fields
	 *		'errors' - data values in error
	 *		'errortext' - error message corresponding to erroneous value
	 *
	 *	@todo - does $hidden_fields need to be merged with values for fields not processed? (Probably not - should only relate to fields current user can see)
	 *	@todo - make sure admin can edit fields of other users
	 */
	public function userExtendedValidateAll($inArray, $hideArray, $isSignup=FALSE)
	{
		$tp = e107::getParser();
		
		$eufVals = array();		// 'Answer' array
		$hideFlags = array();
		
		foreach ($this->fieldDefinitions as $k => $defs)
		{
			$category = $defs['user_extended_struct_parent'];
			if (($category == 0) || ($isSignup && (int) $this->catDefinitions[$category]['user_extended_struct_applicable'] === (int) e_UC_MEMBER && (int) $this->catDefinitions[$category]['user_extended_struct_write'] === (int) e_UC_MEMBER) || (check_class($this->catDefinitions[$category]['user_extended_struct_applicable']) && check_class($this->catDefinitions[$category]['user_extended_struct_write'])))
			{	// Category applicable to user
				
				if (($isSignup && (int) $defs['user_extended_struct_applicable'] === (int) e_UC_MEMBER && (int) $defs['user_extended_struct_write'] === (int) e_UC_MEMBER) || (check_class($defs['user_extended_struct_applicable']) && check_class($defs['user_extended_struct_write'])))
				{	// User can also update field
					$f = 'user_'.$defs['user_extended_struct_name'];
					if (isset($inArray[$f]) || ($isSignup && ($defs['user_extended_struct_required'] == 1)))
					{	// Only allow valid keys
						$val = varset($inArray[$f], FALSE);
						$err = $this->user_extended_validate_entry($val, $defs); 
						if ($err === true)
						{  // General error - usually empty field; could be unacceptable value, or regex fail and no error message defined
							$eufVals['errortext'][$f] = str_replace('[x]',$tp->toHTML(defset($defs['user_extended_struct_text'], $defs['user_extended_struct_text']),FALSE,'defs'),LAN_USER_75);
							$eufVals['errors'][$f] = ERR_GENERIC;
						}
						elseif ($err)
						{	// Specific error message returned - usually regex fail
							$eufVals['errortext'][$f] = $err;
							$eufVals['errors'][$f] = ERR_GENERIC;
						}
						elseif (!$err)
						{
							$eufVals['data'][$f] = $tp->toDB($val);
						}
						if (isset($hideArray[$f]))
						{
							$hideFlags[] = $f;
						}
					}
				}
			}
		}
		$hidden_fields = implode('^', $hideFlags);
		if ($hidden_fields != '')
		{
			$hidden_fields = '^'.$hidden_fields.'^';
		}
		$eufVals['data']['user_hidden_fields'] = $hidden_fields;

		return $eufVals;
	}


	/**
	 * Sanitize User submitted user-extended fields.
	 * @param $posted
	 * @return array
	 */
	function sanitizeAll($posted)
	{

		$arr = array();

		foreach($posted as $field => $value)
		{
			$type = $this->getFieldType($field);

			switch($type)
			{

				case EUF_INTEGER :  //integer
					$arr[$field] = (int) $value;
			    break;

				case EUF_TEXT :  //textbox
				case EUF_COUNTRY:
				case EUF_RADIO : //radio
				case EUF_CHECKBOX : //checkboxes
				case EUF_DROPDOWN : //dropdown
				case EUF_PREDEFINED : // predefined list, shown in dropdown
				case EUF_DB_FIELD : //db_field
				case EUF_DATE : //date
				case EUF_LANGUAGE : // language
				case EUF_TEXTAREA : //textarea
				case EUF_PREFIELD:
				case EUF_ADDON:

					$arr[$field] = e107::getParser()->filter($value);
			    break;

				case EUF_RICHTEXTAREA : // rich textarea (using WYSIWYG editor)
					$arr[$field] = e107::getParser()->cleanHtml($value);
				break;

				default:
					e107::getDebug()->log("User extended field: ".$field." is missing a valid field-type.");

			}


		}


		return $arr;


	}



	/**
	 * alias of user_extended_get_categories();
	 *
	 * @return array
	 */
	function getCategories()
	{
		return $this->catDefinitions;
	}

	/**
	 * Return a list of the user-extended categories.
	 * @deprecated Use getCategories() instead.
	 * @param bool $byID
	 * @return array
	 */
	function user_extended_get_categories($byID = TRUE)
	{

	   	$ret = array();
		$sql = e107::getDb('ue');
		
		if($sql->select("user_extended_struct", "*", "user_extended_struct_type = 0 ORDER BY user_extended_struct_order ASC"))
		{
			if($byID == TRUE)
			{
				while($row = $sql->fetch())
				{
					$ret[$row['user_extended_struct_id']][] = $row;
				}
			}
			else
			{
				$ret = $sql->db_getList();
			}
		}
		return $ret;
	}


	/**
	 * Returns an array of fields for the selected category.
	 * The keys are the field name, minus the 'user_'.
	 * @param string $cat
	 * @return array
	 */
	public function getFields($cat = null)
	{
		return $this->user_extended_get_fieldList($cat, 'user_extended_struct_name');
	}


	// Get the definition of all fields, or those in a specific category, grouped by category ID
	// Reads non-system fields only
	/**
	 * @param $cat
	 * @return array
	 */
	public function user_extended_get_fields($cat = "")
	{
		$list = $this->getFieldList($cat);
		$ret = array();
		foreach($list as $row)
		{
			$ret[$row['user_extended_struct_parent']][] = $row;
		}
		return $ret;
	}


	/**
	 * Get a list of fields in a particular category.
	 * @param string $cat
	 * @param string $indexField
	 * @return array
	 */
	function getFieldList($cat = null, $indexField = 'user_extended_struct_id')
	{
		return $this->user_extended_get_fieldList($cat, $indexField); 	
	}

	/**
	 * @param $listRoot
	 * @return extended_timezones|false
	 */
	private function getFieldTypeClass($listRoot)
	{

		$filename = e_CORE . 'sql/extended_' . $listRoot . '.php';

		if(!is_readable($filename))
		{
			return false;
		}

		require_once($filename);
		$className = 'extended_' . $listRoot;

		if(!class_exists($className))
		{
			return false;
		}

		/** @var extended_timezones $temp */
		$temp = new $className();

		if(!method_exists($className, 'getValue'))
		{
			return false;
		}


		return $temp;
	}
		

	/**
	 * Get the definition of all fields, or those in a specific category, indexed by field ID (or some other field by specifying $indexField)
	 * @param $cat
	 * @param $indexField;
	 * @param $system - include system fields.
	 * @return array
	 */
	function user_extended_get_fieldList($cat = null, $indexField = 'user_extended_struct_id', $system = false)
	{
		if(empty($indexField))
		{
			$indexField = 'user_extended_struct_id';
		}

		$ret = [];

		foreach($this->fieldDefinitions as $row)
		{
			if($cat !== null && ($row['user_extended_struct_parent'] !== (int) $cat))
			{
				continue;
			}

			if($system == false && ($row['user_extended_struct_text'] === '_system_'))
			{
				continue;
			}

			$id = $row[$indexField];
			$ret[$id] = $row;

		}

		return $ret;

		/*
		$sql = e107::getDb('ue');

		$ret = array();
		
		$more = ($cat != '') ? " AND user_extended_struct_parent = ".intval($cat)." " : "";
		$sys = ($system == false) ? " AND user_extended_struct_text != '_system_' " : "";
		
		if($sql->select("user_extended_struct", "*", "user_extended_struct_type > 0 {$sys} {$more} ORDER BY user_extended_struct_order ASC"))
		{
			while($row = $sql->fetch())
			{
				$ret[$row[$indexField]] = $row;
			}
		}

		return $ret;*/
	}


	/**
	 * Return the list of user_extended fields. 
	 * @return array
	 */
	function getFieldNames()
	{
		$ret = array();

		foreach($this->fieldDefinitions as $row)
		{
			$ret[] = 'user_'.$row['user_extended_struct_name'];

		}

		return $ret;
	}


	/**
	 * Get the field-type of a given field-name.
	 * @param $field
	 * @return bool|int
	 */
	public function getFieldType($field)
	{

		if(isset($this->fieldAttributes[$field]['type']))
		{
			return (int) $this->fieldAttributes[$field]['type'];
		}

		return false;
	}

	/**
	 * Get the field attributes of a given field-name.
	 * @param string $field
	 * @param string read|write|type|values|parms|applicable
	 * @return false|string
	 */
	public function getFieldAttribute($field, $att)
	{
		if(isset($this->fieldAttributes[$field][$att]))
		{
			return html_entity_decode($this->fieldAttributes[$field][$att]);
		}

		return false;
	}

	/**
	 * Get the category attributes of a given category-name.
	 * @param string $field
	 * @param string read|write|applicable
	 * @return bool|int
	 */
	public function getCategoryAttribute($field, $att)
	{
		if(isset($this->catAttributes[$field][$att]))
		{
			return (int) $this->catAttributes[$field][$att];
		}

		return false;
	}

		/**
	 * Get the field structure values of a given field-name.
	 * @param $field
	 * @return bool|string
	 */
	public function getFieldValues($field)
	{
		if(!empty($this->fieldAttributes[$field]['values']))
		{
			return html_entity_decode($this->fieldAttributes[$field]['values']);
		}

		return false;
	}


	/**
	 * Return a list of all field types.
	 * @return array
	 */
	public function getFieldTypes()
	{
		return $this->user_extended_types;

	}


	// Return the field creation text for a definition
	/**
	 * @param $type
	 * @param $default
	 * @return bool|string
	 */
	function user_extended_type_text($type, $default)
	{
	  $tp = e107::getParser();
	  
	  if(!is_numeric($type))
	  {
	  	return false;
	  }

	  switch ($type)
	  {
		  case EUF_COUNTRY :
		  $db_type = 'VARCHAR(2)';
		  break;

		case EUF_INTEGER :
		  $db_type = 'INT(11)';
		  break;

		case EUF_DATE :
		  $db_type = 'DATE';
		  break;

		case EUF_TEXTAREA:
		case EUF_RICHTEXTAREA :
		case EUF_CHECKBOX :
		  $db_type = 'TEXT';
		 break;

		case EUF_TEXT :
		case EUF_RADIO :
		case EUF_DROPDOWN :
		case EUF_DB_FIELD :
		case EUF_LANGUAGE :
		case EUF_PREDEFINED :


		  $db_type = 'VARCHAR(255)';
		 break;

		 case EUF_PREFIELD: // FIXME Predefined field - this should be assignable from XML typically.
		     $db_type = 'VARCHAR(255)';
		 break;
		 
		case EUF_CATEGORY:
			return '';
		 break;

		 case EUF_ADDON:
		    return 'JSON';
		 break;

		default:
			e107::getMessage()->addDebug("<strong>Unknown type '{$type}' for user extended field.</strong>"); 
			return false;
		break;

	  }
	  if($type != EUF_DB_FIELD && ($type != EUF_TEXTAREA) && ($type != EUF_RICHTEXTAREA) &&  ($type != EUF_CHECKBOX) && !empty($default))
	  {
		$default_text = " DEFAULT '".$tp -> toDB($default, true)."'";
	  }
	  else
	  {
		$default_text = '';
	  }


	  return $db_type.$default_text;
	}


	/**
	 * @param $name
	 * @return int
	 */
	function user_extended_field_exist($name)
	{
	  	$sql = e107::getDb('ue');
		$tp = e107::getParser();
		return $sql->count('user_extended_struct','(*)', "WHERE user_extended_struct_name = '".$tp -> toDB($name, true)."'");
	}

	/**
	 * @return void
	 */
	function clear_cache()
	{
		e107::getCache()->clear_sys('nomd5_extended_struct');
	}

	// For use by plugins to add extended user fields and won't be visible anywhere else

	/**
	 * @param $name
	 * @param $type
	 * @param $default
	 * @param $source
	 * @return bool
	 */
	function user_extended_add_system($name, $type, $default = '', $source = '_system_')
	{
	  return $this->user_extended_add($name, '_system_', $type, $source, '', $default, 0, 255, 255, 255, 0, 0);
	}


	/**
	 * @param $name
	 * @param $text
	 * @param $type
	 * @param $parms
	 * @param $values
	 * @param $default
	 * @param $required
	 * @param $read
	 * @param $write
	 * @param $applicable
	 * @param $order
	 * @param $parent
	 * @return bool
	 */
	function user_extended_add($name, $text='', $type='', $parms='', $values='', $default='', $required='', $read='', $write='', $applicable='', $order='', $parent='')
	{
		
		$sql = e107::getDb('ue');
		$tp = e107::getParser();
		
		$this->clear_cache();
		
		if(is_array($name))
	  	{
			extract($name);
		}

	 	if(!is_numeric($type))
	  	{
			$type = $this->typeArray[$type];
	  	}

		if($this->user_extended_field_exist($name) && $sql->field('user_extended', 'user_'.$name)!==false)
		{
			return true;
		}

		if ($this->user_extended_reserved($name))
		{
			e107::getMessage()->addDebug("Reserved Field");
			return false;
		}

		$field_info = $this->user_extended_type_text($type, $default);

		if($type === EUF_ADDON && !empty($fieldType))
		{
			$field_info = $fieldType;
		}
		
		// wrong type
		if(false === $field_info)
		{
			trigger_error('$field_info is false: '.__METHOD__, E_USER_NOTICE);
			e107::getMessage()->addDebug("\$field_info is false ".__METHOD__);
			return false;
		}
		
		if($order === '' && $field_info)
		{
		  if($sql->select('user_extended_struct','MAX(user_extended_struct_order) as maxorder','1'))
		  {
			$row = $sql->fetch();
			if(is_numeric($row['maxorder']))
			{
			  $order = $row['maxorder']+1;
			}
		  }
		}
		// field of type category
		if($field_info)
		{
			$sql->gen('ALTER TABLE #user_extended ADD user_'.$tp -> toDB($name, true).' '.$field_info);
		}

			$extStructInsert = array(
		//	'user_extended_struct_id'           => null,
			'user_extended_struct_name'         => $tp -> toDB($name, true),
			'user_extended_struct_text'         => (string) $tp -> toDB($text, true),
			'user_extended_struct_type'         => (int) $type,
			'user_extended_struct_parms'        => (string) $tp -> toDB($parms, true),
			'user_extended_struct_values'       => ($type === EUF_ADDON) ? (string) $values : (string) $tp -> toDB($values, true),
			'user_extended_struct_default'      => (string) $tp -> toDB($default, true),
			'user_extended_struct_read'         => (int) $read,
			'user_extended_struct_write'        => (int) $write,
			'user_extended_struct_required'     => (int) $required,
			'user_extended_struct_signup'       => '0',
			'user_extended_struct_applicable'   => (int) $applicable,
			'user_extended_struct_order'        => (int) $order,
			'user_extended_struct_parent'       => (int) $parent
			);


		if(!$this->user_extended_field_exist($name))
		{

			$nid = $sql->insert('user_extended_struct', $extStructInsert);
			$this->init(); // rebuild the list.

		//	$sql->insert('user_extended_struct',"null,'".$tp -> toDB($name, true)."','".$tp -> toDB($text, true)."','".intval($type)."','".$tp -> toDB($parms, true)."','".$tp -> toDB($values, true)."', '".$tp -> toDB($default, true)."', '".intval($read)."', '".intval($write)."', '".intval($required)."', '0', '".intval($applicable)."', '".intval($order)."', '".intval($parent)."'");
		}

		if($this->user_extended_field_exist($name))
		{
		    return true;
		}

		trigger_error("Extended User Field ".$name." doesn't exist", E_USER_NOTICE);

		return false;
	}


	/**
	 * @param $id
	 * @param $name
	 * @param $text
	 * @param $type
	 * @param $parms
	 * @param $values
	 * @param $default
	 * @param $required
	 * @param $read
	 * @param $write
	 * @param $applicable
	 * @param $parent
	 * @return false|int
	 */
	function user_extended_modify($id, $name, $text, $type, $parms, $values, $default, $required, $read, $write, $applicable, $parent)
	{
		$sql = e107::getDb('ue');
		$tp = e107::getParser();
		
		if ($this->user_extended_field_exist($name))
		{
			$field_info = $this->user_extended_type_text($type, $default);
			// wrong type
			if(false === $field_info) return false;
			
			// field of type category
			if($field_info)
			{
				$sql->gen("ALTER TABLE #user_extended MODIFY user_".$tp -> toDB($name, true)." ".$field_info);
			}
			
			$newfield_info = "
				user_extended_struct_text = '".$tp -> toDB($text, true)."',
				user_extended_struct_type = '".intval($type)."',
				user_extended_struct_parms = '".$tp -> toDB($parms, true)."',
				user_extended_struct_values = '".$tp -> toDB($values, true)."',
				user_extended_struct_default = '".$tp -> toDB($default, true)."',
				user_extended_struct_required = '".intval($required)."',
				user_extended_struct_read = '".intval($read)."',
				user_extended_struct_write = '".intval($write)."',
				user_extended_struct_applicable = '".intval($applicable)."',
				user_extended_struct_parent = '".intval($parent)."'
				WHERE user_extended_struct_id = '".intval($id)."'
			";
			return $sql->update("user_extended_struct", $newfield_info);
		}

		return false;
	}

	/**
	 * @param $id
	 * @param $name
	 * @return bool
	 */
	function user_extended_remove($id, $name)
	{
		$sql = e107::getDb('ue');
		$tp = e107::getParser();
		
		$this->clear_cache(); 
		if ($this->user_extended_field_exist($name))
		{
			// FIXME - no table structure changes for categories
			// but no good way to detect it right now - ignore the sql error for now, fix it asap
			$sql->gen("ALTER TABLE #user_extended DROP user_".$tp -> toDB($name, true));
			
			if(is_numeric($id))
			{
				$sql->delete("user_extended_struct", "user_extended_struct_id = '".intval($id)."' ");
			}
			else
			{
				$sql->delete("user_extended_struct", "user_extended_struct_name = '".$tp -> toDB($id, true)."' ");
			}
			return !($this->user_extended_field_exist($name));
		}

		return false;
	}

	/**
	 * @param $struct
	 * @param $curval
	 * @return string
	 */
	function user_extended_hide($struct, $curval)
	{
		$chk = ($curval) ? " checked='checked' " : "";
		$name = "hide[user_".$struct['user_extended_struct_name']."]";
		return "<input type='checkbox' {$chk} value='1' name='{$name}' />&nbsp;".UE_LAN_HIDE;
	}


	/**
	 * BC alias of renderElement
	 *
	 * @param array $struct
	 * @param mixed $curval
	 * @return array|string
	 */
	function user_extended_edit($struct, $curval)
	{
		return $this->renderElement($struct, $curval);
	}


	/**
	 * @param array $struct
	 * @param mixed $curval
     * @param array $opts
	 * @return array|string
	 */
	public function renderElement($struct, $curval, $opts=array())
	{
		$tp = e107::getParser();
		$frm = e107::getForm();
		$curval = trim($curval);

		if(empty($curval) && !empty($struct['user_extended_struct_default']))
		{
			$curval = $struct['user_extended_struct_default'];
		}
		
		$choices = explode(",",$struct['user_extended_struct_values']);
		
		foreach($choices as $k => $v)
		{
			$choices[$k] = str_replace("[E_COMMA]", ",", $v);
		}
		
		$parms 		= explode("^,^",$struct['user_extended_struct_parms']);
		$include 	= preg_replace("/\n/", " ", $tp->toHTML($parms[0]));
		// $regex 		= $tp->toText(varset($parms[1]));
		// $regexfail 	= $tp->toText(varset($parms[2]));
		$fname 		= "ue[user_".$struct['user_extended_struct_name']."]";
		$required	= vartrue($struct['user_extended_struct_required']) == 1 ? "required"  : "";
		$fid		= $frm->name2id($fname);
		$placeholder = (!empty($parms[4])) ? "placeholder=\"".$tp->toAttribute($parms[4])."\"" : "";

		$class = !empty($opts['class']) ? $opts['class'] : "form-control tbox";
		$placeholder = !empty($opts['placeholder']) ? "placeholder=\"".$tp->toAttribute($opts['placeholder'])."\"" : $placeholder;

		if(!empty($parms[5]))
		{
			$class .= " e-tip";
			$title = "title=\"".$tp->toAttribute($parms[5])."\"";
		}
		else
		{
			$title = '';
		}

		if(strpos($include, 'class') === FALSE)
		{
			$include .= " class='".$class."' ";
		}

		$ret = null;

		switch($struct['user_extended_struct_type'])
		{

			case EUF_ADDON:
				$attributes = e107::unserialize($struct['user_extended_struct_values']);

				$plug = $struct['user_extended_struct_parms'];
				if(!$form = e107::getAddon($plug,'e_user',$plug."_user_form")) // custom form.
				{
					$form = e107::getForm();
				}

		//		$method = str_replace('plugin_'.$plug.'_', '', $struct['user_extended_struct_name']);

				if(empty($attributes['type']))
				{
					trigger_error("'type' is missing from field definition", E_USER_NOTICE);
					return null;
				}

				$attributes['method'] = 'user_'.$struct['user_extended_struct_name'];

				return $form->renderElement($fname,$curval, $attributes);
			break;


			case EUF_COUNTRY:
				return e107::getForm()->country($fname,$curval, $opts);
			break;


			case EUF_TEXT :  //textbox
			case EUF_INTEGER :  //integer
		 		$ret = "<input id='{$fid}' type='text' name='{$fname}' {$title} value='{$curval}' {$include} {$required} {$placeholder} />";
			
		  		return $ret;
		  	break;

			case EUF_RADIO : //radio
			
				$ret = '';
			
				foreach($choices as $choice)
				{
					$choice = trim($choice);
					$choice = html_entity_decode($choice);

					if(strpos($choice,"|")!==FALSE)
					{
		            	list($val,$label) = explode("|",$choice);
					}
					elseif(strpos($choice," => ")!==FALSE) // new in v2.x
					{
		            	list($val,$label) = explode(" => ",$choice);
					}
					else
					{
		            	$val = $choice;
						$label = $choice;
					}
					
					$label = deftrue($label, $label);
					
					if(deftrue('BOOTSTRAP'))
					{
						$ret .= $frm->radio($fname,$val,($curval == $val),array('label'=>$label, 'required'=> !empty($required)));
					}
					else 
					{
						$chk = ($curval == $val)? " checked='checked' " : "";
						$ret .= "<input id='{$fid}' {$include} type='radio' name='{$fname}' value='{$val}' {$chk} {$required} /> {$label}";	
					}
					
				}
			
				return $ret;
				
		    break;

	        case EUF_CHECKBOX : //checkboxes

				if(!is_array($curval))
				{
					$curval = e107::unserialize($curval);
				}

				return e107::getForm()->checkboxes($fname.'[]',$choices, $curval, array('useLabelValues'=>1));

			break;

			case EUF_DROPDOWN : //dropdown
			  $ret = "<select {$include} id='{$fid}' name='{$fname}' {$required} {$title} >\n";
			  $ret .= "<option value=''>&nbsp;</option>\n";  // ensures that the user chose it.
			  foreach($choices as $choice)
			  {
				$choice = trim($choice);
				$choice = deftrue($choice, $choice);
				$sel = ($curval == $choice) ? " selected='selected' " : "";
				$ret .= "<option value='{$choice}' {$sel}>{$choice}</option>\n";
			  }
			  $ret .= "</select>\n";
			  return $ret;
			  break;

			case EUF_PREDEFINED : // predefined list, shown in dropdown
				$listRoot = trim($struct['user_extended_struct_values']);			// Base list name

				if(!$temp = $this->getFieldTypeClass($listRoot))
				{
					return "Missing Extended Class";
				}

				$temp->pointerReset();

				$ret = "<select id='{$fid}' {$include} name='{$fname}' {$required} >\n";
				$ret .= "<option value=''>&nbsp;</option>\n";  // ensures that the user chooses it.
				while (FALSE !== ($row = $temp->getValue(0, 'next')))
				{
					$val = key($row);
					$choice = $temp->getValue($val, 'display');
					$sel = ($curval == $val) ? " selected='selected' " : '';
					$ret .= "<option value='{$val}' {$sel}>{$choice}</option>\n";
				}
				$ret .= "</select>\n";
				return $ret;

			case EUF_DB_FIELD : //db_field

				if(empty($choices))
				{
					e107::getDebug()->log("DB Field Choices is empty");
					$error = true;
				}

				if(empty($choices[0]))
				{
					e107::getDebug()->log("DB Field Choices is missing a table");
					$error = true;
				}
				if(empty($choices[1]))
				{
					e107::getDebug()->log("DB Field Choices is missing an index field");
					$error = true;
				}
				if(empty($choices[2]))
				{
					e107::getDebug()->log("DB Field Choices is missing an value field");
					$error = true;
				}

				if(!empty($error))
				{
					return "<span class='label label-danger'>Failed to load (misconfigured. See debug for more info.)</span>";
				}


				$sql = e107::getDb('ue');

				$order = !empty($choices[3]) ? "ORDER BY " . $tp->toDB($choices[3], true) : "";

				if($sql->select($tp->toDB($choices[0], true), $tp->toDB($choices[1], true) . "," . $tp->toDB($choices[2], true), "1 $order") !== FALSE)
				{
					$choiceList = $sql->db_getList('ALL', false);
					$ret = "<select id='{$fid}' {$include} name='{$fname}' {$required}  {$title}>\n";
					$ret .= "<option value=''>&nbsp;</option>\n";  // ensures that the user chose it.

					foreach($choiceList as $cArray)
					{
						$cID = trim($cArray[$choices[1]]);
						$cText = trim($cArray[$choices[2]]);
						$sel = ($curval == $cID) ? " selected='selected' " : "";
						$ret .= "<option value='{$cID}' {$sel}>{$cText}</option>\n";
					}

					$ret .= "</select>\n";

					return $ret;
				}
				else
				{
					return "<span class='label label-danger'>Failed to load</span>";
				}

				break;

			case EUF_TEXTAREA : //textarea
					return "<textarea id='{$fid}' {$include} name='{$fname}'  {$required} {$title}>{$curval}</textarea>";
					break;

			case EUF_RICHTEXTAREA : // rich textarea (using WYSIWYG editor)
					return e107::getForm()->bbarea($fname, $curval);

			case EUF_DATE : //date

					if($curval == '0000-00-00') // Quick datepicker fix.
					{
						$curval = '';
					}

					if(THEME_LEGACY === true)
					{
					    if(empty($opts['placeholder']))
                        {
                            $opts['placeholder'] = 'yyyy-mm-dd';
                        }

						return e107::getForm()->text($fname,$curval,10,$opts);
					}

                    $opts['format'] = 'yyyy-mm-dd';
                    $opts['return'] = 'string';

                    if(!empty($required))
                    {
                        $opts['required'] = true;
                    }

					return e107::getForm()->datepicker($fname,$curval,$opts);
					break;

			case EUF_LANGUAGE : // language
					$lanlist = e107::getLanguage()->installed();
					sort($lanlist);

	                $ret = "<select {$include} id='{$fid}' name='{$fname}' {$required} >\n";
					$ret .= "<option value=''>&nbsp;</option>\n";  // ensures that the user chose it.
					foreach($lanlist as $choice)
					{
						$choice = trim($choice);
						$sel = ($curval == $choice || (!USER && $choice == e_LANGUAGE))? " selected='selected' " : "";
						$ret .= "<option value='{$choice}' {$sel}>{$choice}</option>\n";
					}
					$ret .= "</select>\n";

	            break;

		}

		return $ret;
	}


	/**
	 * BC Alias for getStructure()
	 * @param string $orderby
	 * @return mixed
	 */
	function user_extended_getStruct($orderby="user_extended_struct_order")
	{
		return $this->getStructure($orderby);
	}


	/**
	 * Return all extended-field structure information
	 * @param string $orderby
	 * @return array|mixed
	 */
	function getStructure($orderby="user_extended_struct_order")
	{

		$id = 'core/userextended/structure';

		if($ueStruct = e107::getRegistry($id))
		{
			return $ueStruct;
		}
		
		$tp 	= e107::getParser();
		$sql_ue = e107::getDb('ue'); // new db;		// Use our own db to avoid interference with other objects
		
		$ret = array();
		$parms = "";
		
		if($orderby != "")
		{
			$parms = "1 ORDER BY ".$tp -> toDB($orderby, true);
		}
		
		if($sql_ue->select('user_extended_struct','*',$parms))
		{
			while($row = $sql_ue->fetch())
			{
				$ret['user_'.$row['user_extended_struct_name']] = $row;
			}
		}

		e107::setRegistry($id, $ret);

		return $ret;
	}


	/**
	 * @param bool|false $no_cache
	 * @return bool|array
	 */
	function parse_extended_xml($no_cache = false)
	{
		if($no_cache == FALSE && $this->extended_xml)
		{
			return $this->extended_xml;
		}

		$xml = e107::getXml();
		$data = $xml->loadXMLfile(e_CORE."xml/user_extended.xml", true);
		$ret['version'] = $data['@attributes']['version'];
		unset($info);
		foreach($data['item'] as $item)
		{
			if(isset($item['include_text']) && is_array($item['include_text']) && !count($item['include_text']))
			{
				$item['include_text'] = '';
			}

			$info = array(
						"name" 			=> $item['@attributes']['name'],
						"text" 			=> "UE_LAN_".strtoupper($item['@attributes']['name']),
						"type" 			=> varset($item['type']),
						"values" 		=> varset($item['values']),
						"default" 		=> varset($item['default']),
						"required" 		=> varset($item['required']),
						"read" 			=> varset($item['read']),
						"write"			=> varset($item['write']),
						"applicable" 	=> varset($item['applicable']),
						"include_text"	=> varset($item['include_text']),
						"parms"			=> varset($item['include_text']),
						"regex" 		=> varset($item['regex'])
					);
			if(is_array($item['default']) && $item['default'] == '')
			{
				$info['default'] = 0;
			}
			if($item['regex'])
			{
				$info['parms'] .= $item['include_text']."^,^".$item['regex']."^,^LAN_UE_FAIL_".strtoupper($item['@attributes']['name']);
			}
			$ret[$item['@attributes']['name']] = $info;
		}
		$this->extended_xml = $ret;
		return $this->extended_xml;
	}

	/**
	 * @param string $field
	 * @return false|mixed|null
	 */
	public function getFieldLabel($field)
	{
		if(strpos($field, 'user_') !== 0)
		{
			$field = 'user_' . $field;
		}

		if(!isset($this->nameIndex[$field]) || !isset($this->fieldDefinitions[$this->nameIndex[$field]]['user_extended_struct_text']))
		{
			return null;
		}

		$text = $this->fieldDefinitions[$this->nameIndex[$field]]['user_extended_struct_text'];

		return defset($text, $text);

	}

	/**
	 * Replacement Method for user_extended_getvalue(); Returns extended field data in the original posted format.
	 * @param int $uid
	 * @param string $field_name
	 * @param mixed $ifnotset [optional]
	 * @return mixed
	 */
	function get($uid, $field_name, $ifnotset=false)
	{

		$uid = (int) $uid;

		if(strpos($field_name, 'user_') !== 0)
		{
			$field_name = 'user_' . $field_name;
		}

		$uinfo = e107::user($uid);

		if(!isset($uinfo[$field_name]))
		{
			return $ifnotset;
		}

		$type = $this->getDbFieldType($field_name);

		switch($type)
		{
			case "int":
				$ret = (int) $uinfo[$field_name];
				break;

			case "array":
				$ret = e107::unserialize($uinfo[$field_name]); //  code
				break;

			default:
				$ret = $uinfo[$field_name];
		}

		return $ret;
	}



	/**
	 * Proxy method for setting the value of an extended field
	 * (inserts or updates)
	 *
	 * @param integer $uid
	 * @param string $field_name eg. location
	 * @param string $newvalue eg. USA
	 * @param string $fieldType [optional] default 'todb' |
	 * @return boolean;
	 */
	function set($uid, $field_name, $newvalue, $fieldType = 'todb')
	{
		return $this->user_extended_setvalue($uid, $field_name, $newvalue, $fieldType);
	}


	/**
	 * Set the value of an extended field
	 *
	 *  $ue = new e107_user_extended;
	 *     $result = $ue->user_extended_setvalue(1, 'location', 'Pittsburgh');
	 *
	 * @param  int  $uid
	 * @param  string $field_name
	 * @param mixed $newvalue
	 * @param string $fieldType
	 * @return bool|int
	 */
	function user_extended_setvalue($uid, $field_name, $newvalue, $fieldType = 'todb')
	{
		$sql = e107::getDb('ue');
		$tp = e107::getParser();

		$uid = (int)$uid;

		$target = array('data' => array('user_'.$field_name => $newvalue));
		$this->addFieldTypes($target);

		$fieldType = isset($target['_FIELD_TYPES']['user_'.$field_name]) ? $target['_FIELD_TYPES']['user_'.$field_name] : $fieldType;

		switch($fieldType)
		{
			case 'int':
				$newvalue = (int)$newvalue;
				break;

			case 'escape':
				$newvalue = "'".$sql->escape($newvalue)."'";
				break;

			case 'array':
				if(is_array($newvalue))
				{
					$newvalue = "'".e107::serialize($newvalue, true)."'";
				}
				else
				{
					$newvalue = "'". (string) $newvalue."'";
				}
			break;

			default:
				$newvalue = "'".$tp->toDB($newvalue)."'";
				break;
		}

		if(strpos($field_name, 'user_') !== 0)
		{
			$field_name = 'user_'.$field_name;
		}


		$qry = "
		INSERT INTO `#user_extended` (user_extended_id, {$field_name})
		VALUES ({$uid}, {$newvalue})
		ON DUPLICATE KEY UPDATE {$field_name} = {$newvalue}
		";

		if(!$result = $sql->gen($qry))
		{
		//	$this->lastError = $sql->getLastErrorText();
			echo (ADMIN) ? $this->lastError : '';
		}

		e107::setRegistry('core/e107/user/'.$uid); // reset the registry since the values changed.

		return $result;
	}


	/**
	 * Retrieve the value of an extended field
	 *
	 *  $ue = new e107_user_extended;
	 *  $value = $ue->user_extended_getvalue(2, 'location');
	 *
	 * @param int     $uid
	 * @param string    $field_name
	 * @param bool $ifnotset
	 * @return bool
	 */
	function user_extended_getvalue($uid, $field_name, $ifnotset=false)
	{

		$uid = (int) $uid;

		if(strpos($field_name, 'user_') !== 0)
		{
			$field_name = 'user_' . $field_name;
		}

		$uinfo = e107::user($uid);

		if(!isset($uinfo[$field_name]))
		{
			return $ifnotset;
		}

		return $uinfo[$field_name];
	}


	/**
	 *
	 * Given a predefined list field, returns the display text corresponding to the passed value
	 *
	 * TODO: consider whether to cache the class object@param $table
	 * @param $value
	 * @return mixed|string
	 */
	function user_extended_display_text($table, $value)
	{
		if(!$temp = $this->getFieldTypeClass($table))
		{
			"Couldn't find extended field class: ".$table;
			return null;
		}

		return $temp->getValue($value);
	}


	/**
	 * Render Extended User Field Data in a read-only fashion.
	 * @param $value
	 * @param int|string $type field type number or field name.
	 * @return array|string
	 */
	public function renderValue($value, $type=null)
	{

		if(!empty($type) && !is_numeric($type))
		{
			$fieldname = $type;
			$type = $this->getFieldType($type);
		}

		$ret = null;

		switch($type)
		{
			case EUF_RADIO:

				if(isset($fieldname))
				{
					$tmp = $this->getFieldAttribute($fieldname, 'values');
					$choices = explode(',', $tmp);

					if(empty($choices))
					{
						trigger_error('User Extended RADIO field is missing configured selection values', E_USER_NOTICE);
						return null;
					}

					foreach($choices as $choice)
					{
						$choice = trim($choice);

						if(strpos($choice,"|") !==false)
						{
			                list($val,$label) = explode("|",$choice);
						}
						elseif(strpos($choice," => ") !==false) // new in v2.x
						{
			                list($val, $label) = explode(" => ",$choice);
						}
						else
						{
			                $val = $choice;
							$label = $choice;
						}

						if($val == $value)
						{
							$ret = defset($label, $label);
							break;
						}
					}
				}
			break;


			case EUF_COUNTRY:
				if(!empty($value))
				{
					$ret = e107::getForm()->getCountry($value);
				}
				break;

			case EUF_CHECKBOX:
				if(is_string($value))
				{
					$value = e107::unserialize($value);
				}

				if(!empty($value))
				{
					sort($value);
					$ret = implode(', ', $value);
				}

				break;

			case EUF_DATE :        //check for 0000-00-00 in date field
				if($value == '0000-00-00')
				{
					$value = '';
				}
				$ret = $value;
				break;

			case EUF_RICHTEXTAREA:
				$ret = e107::getParser()->toHTML($value, true);
				break;

			case EUF_DB_FIELD :        // check for db_lookup type
				if(!isset($fieldname))
				{
					return null;
				}

				$structValues = $this->getFieldValues($fieldname);
				$tmp = explode(',', $structValues);

				if(empty($tmp[1]) || empty($tmp[2]))
				{
					return null;
				}

				$sql_ue = e107::getDb('euf_db');            // Use our own DB object to avoid conflicts
				if($sql_ue->select($tmp[0], "{$tmp[1]}, {$tmp[2]}", "{$tmp[1]} = '{$value}'"))
				{

					$row = $sql_ue->fetch();
					$ret = varset($row[$tmp[2]]);
				}

				break;

			case EUF_PREDEFINED :    // Predefined field - have to look up display string in relevant file
				if(isset($fieldname))
				{
					$structValues = $this->getFieldValues($fieldname);
					$ret = $this->user_extended_display_text($structValues, $value);
				}
				break;

			default:
				$ret = $value;
			// code to be executed if n is different from all labels;
		}

		return $ret;
	}



}


