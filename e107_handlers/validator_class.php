<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Handler - general purpose validation functions
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/validator_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

if (!defined('e107_INIT')) { exit; }

// List of error numbers which may be returned from validation
define('ERR_MISSING_VALUE','01');
define('ERR_UNEXPECTED_VALUE','02');
define('ERR_INVALID_CHARS', '03');
define('ERR_TOO_SHORT', '04');
define('ERR_TOO_LONG', '05');
define('ERR_DUPLICATE', '06');
define('ERR_DISALLOWED_TEXT', '07');
define('ERR_DISALLOWED_TEXT_EXACT_MATCH', '23');
define('ERR_FIELD_DISABLED', '08');
define('ERR_INVALID_WORD', '09');
define('ERR_PASSWORDS_DIFFERENT', '10');
define('ERR_BANNED_EMAIL', '11');
define('ERR_INVALID_EMAIL', '12');
define('ERR_ARRAY_EXPECTED', '13');
define('ERR_BANNED_USER', '14');
define('ERR_FIELDS_DIFFERENT', '15');
define('ERR_CODE_ERROR', '16');
define('ERR_TOO_LOW', '17');
define('ERR_TOO_HIGH', '18');
define('ERR_GENERIC', '19');				// This requires coder-defined error text
define('ERR_IMAGE_TOO_WIDE', '20');
define('ERR_IMAGE_TOO_HIGH', '21');

// Default error messages
e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_validator.php');

/**
 * Validator class - used by e_model and its child classes
 *
 * @package e107
 * @category e107_handlers
 * @version 1.0
 * @author SecretR
 * @copyright Copyright (c) 2009, e107 Inc.
 */
class e_validator
{
	/**
	 * @var integer Unknown error code
	 */
	const ERR_UNKNOWN = 0;

	/**
	 * @var integer Value not found error code
	 */
	const ERR_MISSING_VALUE = 101;

	/**
	 * @var integer Unexpected value type error code (bad rule)
	 */
	const ERR_UNEXPECTED_VALUE = 102;

	/**
	 * @var integer Invalid characters error code
	 */
	const ERR_INVALID_CHARS = 103;

	/**
	 * @var integer Invalid email error code
	 */
	const ERR_INVALID_EMAIL = 104;

	/**
	 * @var integer Field doesn't match error code
	 */
	const ERR_FIELDS_MATCH = 105;

	/**
	 * @var integer String too short error code
	 */
	const ERR_TOO_SHORT = 131;

	/**
	 * @var integer String too long error code
	 */
	const ERR_TOO_LONG = 132;

	/**
	 * @var integer Number too low error code
	 */
	const ERR_TOO_LOW = 133;

	/**
	 * @var integer Number too high error code
	 */
	const ERR_TOO_HIGH = 134;

	/**
	 * @var integer Array count too low error code
	 */
	const ERR_ARRCOUNT_LOW = 135;

	/**
	 * @var integer Array count high error code
	 */
	const ERR_ARRCOUNT_HIGH = 136;

	/**
	 * @var integer Type of integer expected error code
	 */
	const ERR_INT_EXPECTED = 151;

	/**
	 * @var integer Type of float expected error code
	 */
	const ERR_FLOAT_EXPECTED = 152;

	/**
	 * @var integer Instance type expected error code
	 */
	const ERR_INSTANCEOF_EXPECTED = 153;

	/**
	 * @var integer Array type expected error code
	 */
	const ERR_ARRAY_EXPECTED = 154;

	/**
	 * @var integer Generic (empty value) error code
	 */
	const ERR_GENERIC = 191;

	/**
	 * @var integer File not exists or not a file error code
	 */
	const ERR_NOT_FILE = 201;

	/**
	 * @var integer File not writable error code
	 */
	const ERR_WRITABLE_FILE = 202;

	/**
	 * @var integer File exceeds allowed file size error code
	 */
	const ERR_SIZEMIN_FILE = 203;

	/**
	 * @var integer File lower than minimal file size error code
	 */
	const ERR_SIZEMAX_FILE = 204;

	/**
	 * Required rules - Used by validate method
	 *
	 * Structure: array(type, condition, field title LAN[, condition help, validation error message]);
	 *
	 * @example $_required_rules['download_category_id'] = array('int', '1', 'Download Category', 'choose category')
	 *
	 * Validation array structure:
	 * - type | condition =
	 * 		- regex | regex string
	 * 		- email | no condition required
	 * 		- int/integer | number range in format 'min:max'
	 * 		- float |  number range in format 'min:max'
	 * 		- str/string | number string length range in format 'min:max'
	 * 		- required | no condition required
	 * 		- callback | string function name or array(class name|object, method) (static call)
	 * 		- instanceof | string class name
	 * 		- enum | (string) values separated by '#' e.g. 'match1#match2#match3'
	 * 		- array | array count range in format 'min:max'
	 * 		- compare | string field_name, value should be in format field_name => array('value1', 'value1')
	 * 					if value1 === value1, field_name => value1 will be added to $_valid_data array
	 * - field title LAN =
	 * 		human readable field (data key) name
	 * - [optional] condition help =
	 * 		can be used for both inline field help and validation error message
	 * - [optional] validation error message =
	 * 		if empty condition help will be used
	 *
	 * @var array
	 */
	protected $_required_rules = array();

	/**
	 * Check data only if exist/non-empty
	 *
	 * @var array
	 */
	protected $_optional_rules = array();

	/**
	 * Contains validation error codes in format 'field=>error_code'
	 * @var array
	 */
	protected $_validation_results = array();

	/**
	 * Stores validated data (only after successful {@link validateField()} call
	 * @var array
	 */
	protected $_valid_data = array();

	/**
	 * Stores validate check result
	 * @var boolean
	 */
	protected $_is_valid_data = true;

	/**
	 * eMessage handler namespace
	 *
	 * @var string
	 */
	protected $_message_stack = 'validator';

	/**
	 * Constructore
	 * @param string [optional] $message_stack [optional] eMessage handler namespace
	 * @param array [optional] $rules validation rules
	 * @param array [optional] $optrules optional validation rules
	 */
	public function __construct($message_stack = '', $rules = array(), $optrules = array())
	{
		$this->setMessageStack($message_stack)
			->setRules($rules)
			->setOptionalRules($optrules);
	}

	/**
	 * Set message stack
	 *
	 * @param string $mstack
	 * @return e_validator
	 */
	public function setMessageStack($mstack)
	{
		if(!$mstack) $mstack = 'validator';
		$this->_message_stack = $mstack;
		return $this;
	}

	/**
	 * @param array $rules
	 * @return e_validator
	 */
	public function setRules($rules)
	{
		$this->_required_rules = $rules;
		return $this;
	}

	/**
	 * @param array $rules
	 * @return e_validator
	 */
	public function setOptionalRules($rules)
	{
		$this->_optional_rules = $rules;
		return $this;
	}

	/**
	 * Add successfully validated data to the valid array
	 *
	 * @param string $field_name
	 * @param mixed $value
	 * @return e_validator
	 */
	protected function addValidData($field_name, $value)
	{
		$this->_valid_data[$field_name] = $value;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getValidData()
	{
		return $this->_valid_data;
	}

	/**
	 * Validate data
	 *
	 * @param array $data
	 * @param boolean $availableOnly check only against available data if true
	 * @return boolean
	 */
	function validate($data, $availableOnly = false)
	{
		$this->reset();

		$rules = array_merge(array_keys($this->_required_rules), array_keys($this->_optional_rules));
		// no rules, no check
		if(!$rules)
		{
			$this->setIsValidData(true);
			$this->_valid_data = $data;
			return true;
		}
		
		$fieldList = $rules;
		if($availableOnly) $fieldList = array_keys($data);
		
		foreach ($rules as $field_name)
		{
			if(!in_array($field_name, $fieldList)) continue;
			$value = varset($data[$field_name], null);
			$required = $this->isRequiredField($field_name);
			if(($required || $this->isOptionalField($field_name)) && !$this->validateField($field_name, $value, $required))
			{
				$this->setIsValidData(false);
				$this->addValidateMessage($this->getFieldName($field_name, $required), $this->getErrorCode($field_name), $this->getFieldMessage($field_name, $value, $required));
				continue;
			}
		}

		return $this->_is_valid_data;
	}

	/**
	 * Check if field is required
	 *
	 * @param string $name
	 * @return boolean
	 */
	function isRequiredField($name)
	{
		return isset($this->_required_rules[$name]);
	}

	/**
	 * Check if there is optional rule for this field
	 *
	 * @param string $name
	 * @return boolean
	 */
	function isOptionalField($name)
	{
		return isset($this->_optional_rules[$name]);
	}

	/**
	 * Retrieve help for the required field
	 *
	 * @param string $name
	 * @return string
	 */
	function getFieldHelp($name, $required = true, $default = '')
	{
		if($required)
		{
			$msg = (isset($this->_required_rules[$name][3]) ? $this->_required_rules[$name][3] : $default);
		}
		else
		{
			$msg = (isset($this->_optional_rules[$name][3]) ? $this->_optional_rules[$name][3] : $default);
		}

		return defset($msg, $msg);
	}

	/**
	 * Retrieve validation error message for the required field
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return string
	 */
	function getFieldMessage($name, $value = '', $required = true)
	{
		if($required)
		{
			if(!isset($this->_required_rules[$name][4]))
			{
				$msg = $this->getFieldHelp($name, true);
			}
			else $msg = $this->_required_rules[$name][4];
		}
		else
		{
			if(!isset($this->_optional_rules[$name][4]))
			{
				$msg = $this->getFieldHelp($name, false);
			}
			else $msg = $this->_optional_rules[$name][4];
		}

		return ($msg ? defset($msg, $msg) : '');
	}

	/**
	 * @param string $name
	 * @return string
	 */
	function getFieldName($name, $required = true)
	{
		if($required)
		{
			$msg = (isset($this->_required_rules[$name][2]) ? $this->_required_rules[$name][2] : $name);
		}
		else
		{
			$msg = (isset($this->_optional_rules[$name][2]) ? $this->_optional_rules[$name][2] : $name);
		}

		return defset($msg, $msg);
	}

	/**
	 * Validate single field
	 *
	 * @param string $name
	 * @param string $newval
	 * @param boolean $required
	 * @return boolean
	 */
	function validateField($name, $value, $required = true)
	{
		if($required)
		{
			$type = $this->_required_rules[$name][0];
			$cond = $this->_required_rules[$name][1];
		}
		else
		{
			if(empty($value))
			{
				switch($this->_optional_rules[$name][0])
				{
					case 'int':
					case 'integer':
						$value = 0;
					break;

					case 'float':
						$value = 0.00;
					break;

					case 'array':
						$value = array();
					break;

					default:
						$value = '';
					break;
				}
				$this->addValidData($name, $value);
				return true;
			}
			$type = $this->_optional_rules[$name][0];
			$cond = $this->_optional_rules[$name][1];
		}

		switch ($type)
		{
			case 'required':
				if(empty($value))
				{
					$this->addValidateResult($name, self::ERR_GENERIC);
					return false;
				}
				$this->addValidData($name, $value);
				return true;
			break;

			case 'email':
				if(!check_email($value))
				{
					$this->addValidateResult($name, self::ERR_INVALID_EMAIL);
					return false;
				}
				$this->addValidData($name, $value);
				return true;
			break;

			case 'regexp':
			case 'regex':
				if(!preg_match($cond, $value))
				{
					$this->addValidateResult($name, self::ERR_INVALID_CHARS);
					return false;
				}
				$this->addValidData($name, $value);
				return true;
			break;

			case 'callback':
				if(!call_user_func($cond, $value))
				{
					$this->addValidateResult($name, self::ERR_INVALID_CHARS);
					return false;
				}
				$this->addValidData($name, $value);
				return true;
			break;

			case 'instanceof':
				if(!(is_object($value) && $value instanceof $cond))
				{
					$this->addValidateResult($name, self::ERR_INSTANCEOF_EXPECTED);
					return false;
				}
				$this->addValidData($name, $value);
				return true;
			break;

			case 'int':
			case 'integer':
				if(!preg_match('/^-?[\d]+$/', $value)) // negative values support
				{
					$this->addValidateResult($name, self::ERR_INT_EXPECTED);
					return false;
				}
				// BC! Will be removed after we replace '-' with ':' separator!
				$tmp = $this->parseMinMax($cond);
				if(is_numeric($tmp[0]) && (integer) $tmp[0] > (integer) $value)
				{
					$this->addValidateResult($name, self::ERR_TOO_LOW);
					return false;
				}
				if(is_numeric(varset($tmp[1])) && (integer) $tmp[1] < (integer) $value)
				{
					$this->addValidateResult($name, self::ERR_TOO_HIGH);
					return false;
				}
				$this->addValidData($name, intval($value));
				return true;
			break;

			case 'str':
			case 'string':
			case 'text':
			case 'varchar':
				$tmp = $this->parseMinMax($cond);
				$length = e107::getParser()->ustrlen($value);
				if(is_numeric($tmp[0]) && (integer) $tmp[0] > $length)
				{
					$this->addValidateResult($name, self::ERR_TOO_SHORT);
					return false;
				}

				if('varchar' == $type && !varset($tmp[1])) $tmp[1] = 255;

				if(is_numeric(varset($tmp[1])) && (integer) $tmp[1] < $length)
				{
					$this->addValidateResult($name, self::ERR_TOO_LONG);
					return false;
				}
				$this->addValidData($name, (string) $value);
				return true;
			break;

			case 'set':
			case 'enum':
				$tmp = array_map('trim', explode(',', $cond));
				if(!$value || !in_array($value, $tmp))
				{
					$this->addValidateResult($name, self::ERR_FIELDS_MATCH);
					return false;
				}

				$this->addValidData($name, (string) $value);
				return true;
			break;

			case 'float':
				$value = e107::getParser()->toNumber($value);
				if(!is_numeric($value))
				{
					$this->addValidateResult($name, self::ERR_FLOAT_EXPECTED);
					return false;
				}
				$tmp = $this->parseMinMax($cond);
				if(is_numeric($tmp[0]) && (float) $tmp[0] > (float) $value)
				{
					$this->addValidateResult($name, self::ERR_TOO_LOW);
					return false;
				}
				if(is_numeric(varset($tmp[1])) && (float) $tmp[1] < (float) $value)
				{
					$this->addValidateResult($name, self::ERR_TOO_HIGH);
					return false;
				}
				$this->addValidData($name, $value);
				return true;
			break;

			case 'array':
				if(!is_array($value))
				{
					$this->addValidateResult($name, self::ERR_ARRAY_EXPECTED);
					return false;
				}
				$length = count($value);
				$tmp = $this->parseMinMax($cond);
				if(is_numeric($tmp[0]) && (integer) $tmp[0] > $length)
				{
					$this->addValidateResult($name, self::ERR_ARRCOUNT_LOW);
					return false;
				}
				if(is_numeric(varset($tmp[1])) && (float) $tmp[1] < $length)
				{
					$this->addValidateResult($name, self::ERR_ARRCOUNT_HIGH);
					return false;
				}
				$this->addValidData($name, $value);
				return true;
			break;

			case 'file': // TODO - type image - validate dimensions?
				parse_str($cond, $params);
				$path = e107::getParser()->replaceConstants(varset($params['base']).$value);
				if(!$value || !is_file($path))
				{
					$this->addValidateResult($name, self::ERR_NOT_FILE);
					return false;
				}
				if(vartrue($params['writable']) && !is_writable($path))
				{
					$this->addValidateResult($name, self::ERR_WRITABLE_FILE);
					return false;
				}
				if(vartrue($params['size']))
				{
					$tmp = $this->parseMinMax($cond);
					$fs = filesize($path);
					if(!$fs || (integer) $tmp[0] > $fs)
					{
						$this->addValidateResult($name, self::ERR_SIZEMIN_FILE);
						return false;
					}
					elseif(is_numeric(varset($tmp[1])) && (integer) $tmp[1] < $fs)
					{
						$this->addValidateResult($name, self::ERR_SIZEMAX_FILE);
						return false;
					}
				}
				if(is_numeric(varset($params['maxlen'])) && (integer) $params['maxlen'] < e107::getParser()->ustrlen($value))
				{
					$this->addValidateResult($name, self::ERR_TOO_LONG);
					return false;
				}
				$this->addValidData($name, $value);
				return true;
			break;

			case 'compare':
				if(!is_array($value))
				{
					$this->addValidateResult($name, self::ERR_UNEXPECTED_VALUE);
					return false;
				}

				if(!($value[0] && $value[1] && $value[0] == $value[1]))
				{
					$this->addValidateResult($name, self::ERR_FIELDS_MATCH);
					return false;
				}

				// check length
				if($cond)
				{
					$tmp = $this->parseMinMax($cond);
					$length = e107::getParser()->ustrlen($value[0]);
					if(is_numeric($tmp[0]) && (integer) $tmp[0] > $length)
					{
						$this->addValidateResult($name, self::ERR_TOO_SHORT);
						return false;
					}
					if(is_numeric(varset($tmp[1])) && (integer) $tmp[1] < $length)
					{
						$this->addValidateResult($name, self::ERR_TOO_LONG);
						return false;
					}
				}
				$this->addValidData($name, $value[0]);
				return true;
			break;

			case 'compare_strict':
				if(!is_array($value))
				{
					$this->addValidateResult($name, self::ERR_UNEXPECTED_VALUE);
					return false;
				}
				if(!($value[0] && $value[1] && $value[0] === $value[1]))
				{
					$this->addValidateResult($name, self::ERR_FIELDS_MATCH);
					return false;
				}

				// check length
				if($cond)
				{
					$tmp = $this->parseMinMax($cond);
					$length = e107::getParser()->ustrlen($value[0]);
					if(is_numeric($tmp[0]) && (integer) $tmp[0] > $length)
					{
						$this->addValidateResult($name, self::ERR_TOO_SHORT);
						return false;
					}
					if(is_numeric(varset($tmp[1])) && (integer) $tmp[1] < $length)
					{
						$this->addValidateResult($name, self::ERR_TOO_LONG);
						return false;
					}
				}
				$this->addValidData($name, $value[0]);
				return true;
			break;

			default:
				$this->addValidateResult($name, self::ERR_UNEXPECTED_VALUE);
				return false;
			break;
		}
	}

	// moved to e_parse
	// public function toNumber($value)
	// {
	// 	$larr = localeconv();
	// 	$search = array(
	// 		$larr['decimal_point'], 
	// 		$larr['mon_decimal_point'], 
	// 		$larr['thousands_sep'], 
	// 		$larr['mon_thousands_sep'], 
	// 		$larr['currency_symbol'], 
	// 		$larr['int_curr_symbol']
	// 	);
	// 	$replace = array('.', '.', '', '', '', '');
			
	// 	return str_replace($search, $replace, $value);
	// }

	protected function parseMinMax($string)
	{
		return explode(':', $this->_convertConditionBC($string), 2);
	}

	private function _convertConditionBC($condition)
	{
		// BC! Will be removed after we replace '-' with ':' separator!
		if(strpos($condition, ':') === false)
		{
			return preg_replace('/^([0-9]+)-([0-9]+)$/', '$1:$2', $condition);
		}
		return $condition;
	}

	/**
	 * Add validation error to validate result stack
	 *
	 * @param string $field_title
	 * @param string $err_code
	 * @param string $err_message
	 * @param string $custom
	 * @return e_validator
	 */
	function addValidateMessage($field_title, $err_code = 0, $err_message = '', $custom = '')
	{
		$tp = e107::getParser();
		$lanVars = array(
			'x' => $field_title,
			'y' => $err_code,
			'z' => $this->getErrorByCode($err_code)
		);
		
		if($custom)
		{
			e107::getMessage()->addStack(sprintf($err_message, $err_code, $field_title), $this->_message_stack, (true === $custom ? E_MESSAGE_ERROR : $custom));
			return $this;
		}
	
		//Additional message
		$lan = LAN_VALIDATE_FAILMSG;
		$dbgmsg = false;
		if($err_message)
		{
			$lan = (!$field_title || strpos($err_message, '[x]') !== false ? '' : '[x] - ').$err_message; // custom, e.g. // default '<strong>&quot;%1$s&quot;</strong> field error: Custom error message. '
			$dbgmsg = LAN_VALIDATE_FAILMSG;
		}
		
		//Core message
		/*
		$msg = sprintf(
			$lan, // default '<strong>&quot;%1$s&quot;</strong> validation error: [#%2$d] %3$s. '
			$field_title,
			$err_code,
			$this->getErrorByCode($err_code)
		);
		 */
		
		//NEW - removes need for using sprintf() 
		$msg = $tp->lanVars($lan,$lanVars,true); // '[x] validation error: [y] [z].'

		if($dbgmsg && defset('e107_DEBUG_LEVEL'))
		{
			
			e107::getMessage()->addDebug($tp->lanVars($dbgmsg,$lanVars));
			
			/*
			e107::getMessage()->addDebug(sprintf(
				$dbgmsg, 
				$field_title,
				$err_code,
				$this->getErrorByCode($err_code)
			));
			 */
		}

		e107::getMessage()->addStack($msg, $this->_message_stack, E_MESSAGE_ERROR);

		return $this;
	}

	/**
	 * Get validate message array
	 *
	 * @param boolean $clear
	 * @return array
	 */
	function getValidateMessages($clear = true)
	{
		return e107::getMessage()->getAll($this->_message_stack, true, $clear);
	}

	/**
	 * Render validate messages
	 *
	 * @param boolean $session merge with session messages
	 * @param boolean $clear
	 * @return string
	 */
	function renderValidateMessages($session = false, $clear = true)
	{
		return e107::getMessage()->render($this->_message_stack, $session, $clear);
	}

	/**
	 * @param boolean $session clear session messages as well, default true
	 * @return e_validator
	 */
	function clearValidateMessages($session = true)
	{
		e107::getMessage()->reset(false, $this->_message_stack, $session);
		return $this;
	}

	/**
	 * Add validate error code for a field
	 *
	 * @param string $name
	 * @param integer $code
	 * @return e_validator
	 */
	function addValidateResult($name, $code)
	{
		$this->_validation_results[$name] = $code;
		return $this;
	}

	/**
	 * Get validate result array
	 *
	 * @param boolean $clear
	 * @return array
	 */
	function getValidateResults($clear = true)
	{
		return $this->_validation_results;
	}

	/**
	 * Get validate result for a field
	 *
	 * @param string $field
	 * @param mixed $default
	 * @return integer error code
	 */
	function getErrorCode($field, $default = 0)
	{
		return (isset($this->_validation_results[$field]) ? $this->_validation_results[$field] : $default);
	}

	/**
	 * Get error string by given error code
	 *
	 * @param string $error_code
	 * @return integer error code
	 */
	function getErrorByCode($error_code)
	{
		$lan = 'LAN_VALIDATE_'.$error_code;
		return defset($lan, $lan);
	}

	/**
	 * @return e_validator
	 */
	function clearValidateResults()
	{
		$this->_validation_results = array();
		return $this;
	}

	/**
	 * @return boolean
	 */
	function isValid()
	{
		return empty($this->_is_valid_data);
	}
	
	/**
	 * Set validation status
	 *
	 * @param boolean $status
	 * @return e_validator
	 */
	public function setIsValidData($status)
	{
		$this->_is_valid_data = (boolean) $status;
		return $this;
	}

	/**
	 * Reset object validate result data
	 * @return e_validator
	 */
	function reset()
	{
		$this->setIsValidData(true);
		$this->_valid_data = array();
		$this->clearValidateResults()
			->clearValidateMessages();

		return $this;
	}
}

/*
The validator functions use an array of parameters for each variable to be validated.

	The index of the parameter array is the destination field name.

	Possible processing options:
		'srcname'		- specifies the array index of the source data, where its different to the destination index
		'dbClean'		- method for preparing the value to write to the DB (done as final step before returning). Options are:
							- 'toDB' 	- passes final value through $tp->toDB()
							- 'intval' 	- converts to an integer
							- 'image'  	- checks image for size
							- 'avatar' 	- checks an image in the avatars directory
		'stripTags'		- strips HTML tags from the value (not an error if there are some)
		'minLength'		- minimum length (in utf-8 characters) for the string
		'maxLength'		- minimum length (in utf-8 characters) for the string
		'minVal'		- lowest allowed value for numerics
		'maxVal'		- highest allowed value for numerics
		'longTrim'		- if set, and the string exceeds maxLength, its trimmed
		'enablePref'	- value is processed only if the named $pref evaluates to true; otherwise any input is discarded without error
		'dataType'		- selects special processing methods:
							1 - array of numerics (e.g. class membership)

	In general, only define an option if its to be used
*/

/*	[ Berckoff ]
 * Added "public static " to each method as the parser generates errors (and methods are called statically everywhere)
 */
class validatorClass
{
	// Passed an array of 'source' fields and an array of definitions to validate. The definition may include the name of a validation function.
	// Returns three arrays - one of validated results, one of failed fields and one of errors corresponding to the failed fields
	// Normally processes only those source fields it finds (and for which it has a definition). If $addDefaults is true, sets defaults for those that have
	//  ...one and aren't otherwise defined.
	public static function validateFields(&$sourceFields, &$definitions, $addDefaults = FALSE)
	{
		global $tp, $pref;
		$ret = array('data' => array(), 'failed' => array(), 'errors' => array());

		foreach ($definitions as $dest => $defs)
		{
			$errNum = 0;			// Start with no error

			if(!is_array($defs)) //default rule - dbClean -> toDB
			{
				$defs = array('dbClean', ($defs ? $defs : 'toDB'));
			}
			$src = varset($defs['srcName'],$dest);				// Set source field name
			if (!isset($sourceFields[$src]))
			{
				if ($addDefaults)
				{
					if (isset($defs['default']))
					{
						$ret['data'] = $defs['default'];		// Set default value if one is specified
					} //...otherwise don't add the value at all
				}
				else
				{
					if (!vartrue($defs['fieldOptional']))
					{
						$ret['errors'][$dest] = ERR_MISSING_VALUE;		// No source value
					}
				}
			}
			else
			{	// Got a field we want, and some data to validate here
				$value = $sourceFields[$src];
				if (!$errNum && isset($defs['enablePref']))
				{	// Only process this field if a specified pref enables it
					if (!vartrue($pref[$defs['enablePref']]))
					{
						continue;			// Just loop to the next field - ignore this one.
					}
				}
				if (!$errNum && isset($defs['stripTags']))
				{
					$newValue = trim(strip_tags($value));
					if ($newValue <> $value)
					{
						$errNum = ERR_INVALID_CHARS;
					}
					$value = $newValue;
				}
				if (!$errNum && isset($defs['stripChars']))
				{
					$newValue = trim(preg_replace($defs['stripChars'], "", $value));
					if ($newValue <> $value)
					{
						//echo "Invalid: {$newValue} :: {$value}<br />";
						$errNum = ERR_INVALID_CHARS;
					}
					$value = $newValue;
				}
				if (!$errNum && isset($defs['minLength']) && ($tp->ustrlen($value) < $defs['minLength']))
				{
					if ($value == '')
					{
						if (!vartrue($defs['fieldOptional']))
						{
							$errNum = ERR_MISSING_VALUE;
						}
					}
					else
					{
						$errNum = ERR_TOO_SHORT;
					}
				}
				if (!$errNum && isset($defs['maxLength']) && $tp->ustrlen($value) > $defs['maxLength'])
				{
					if (vartrue($defs['longtrim']))
					{
						$value = substr($value,0,$defs['maxLength']);
					}
					else
					{
						$errNum = ERR_TOO_LONG;
					}
				}
				if (!$errNum && isset($defs['minVal']) && ($value < $defs['minVal']))
				{
					$errNum = ERR_TOO_LOW;
				}
				if (!$errNum && isset($defs['maxVal']) && ($value < $defs['maxVal']))
				{
					$errNum = ERR_TOO_HIGH;
				}
				if (!$errNum && isset($defs['fixedBlock']))
				{
					$newValue = $tp->ustrtolower($value);
					$temp = explode(',',$defs['fixedBlock']);
					foreach ($temp as $t)
					{
						if ($newValue == $tp->ustrtolower($t))
						{
							$errNum = ERR_INVALID_WORD;
							break;
						}
					}
				}
				if (!$errNum && isset($defs['dataType']))
				{
					switch ($defs['dataType'])
					{
						case 1 :		// Assumes we've passed an array variable to be turned into a comma-separated list of integers
							if (is_array($value))
							{
								$temp = array();
								foreach ($value as $v)
								{
									$v = trim($v);
									if (is_numeric($v))
									{
										$temp[] = intval($v);
									}
								}
								$value = implode(',', array_unique($temp));
							}
							else
							{
								$errNum = ERR_ARRAY_EXPECTED;
							}
							break;
						case 2 :		// Assumes we're processing a dual password field - array name for second value is one more than for first
							$src2 = substr($src,0,-1).(substr($src,-1,1) + 1);
							if (!isset($sourceFields[$src2]) || ($sourceFields[$src2] != $value))
							{
								$errNum = ERR_PASSWORDS_DIFFERENT;
							}
							break;
						default :
							$errNum = ERR_CODE_ERROR;		// Pick up bad values
					}
				}
				if (!$errNum)
				{
					if (isset($defs['dbClean']))
					{
						switch ($defs['dbClean'])
						{
							case 'toDB' :
								$value = $tp->toDB($value);
								break;
							case 'intval' :
								$value = intval($value);
								break;
							case 'avatar' :			// Special case of an image - may be found in the avatars directory
								if (preg_match('#[0-9\._]#', $value))
								{
									if (strpos('-upload-', $value) === 0)
									{
										$img = e_AVATAR_UPLOAD.str_replace('-upload-', '', $value);		// Its a user-uploaded image
									}
									elseif (strpos($value, '//') !== false)
									{
										$img = $value;			// Its a remote image
									}
									else
									{
										$img = e_AVATAR_DEFAULT.$value;		// Its a server-stored image
									}
								}
												// Deliberately fall through into normal image processing
							case 'image' :			// File is an image name.  $img may be set if we fall through from 'avatar' option - its the 'true' path to the image
								if (!isset($img) && isset($defs['imagePath']))
								{
									$img = $defs['imagePath'].$value;
								}
								$img = varset($img,$value);
								//XXX There should be no size limits - as image sizes are handled by thumb.php 
								if ($size = getimagesize($img))
								{
									// echo "Image {$img} size: {$size[0]} x {$size[1]}<br />";
									if (isset($defs['maxWidth']) && $size[0] > $defs['maxWidth'])
									{		// Image too wide
									//	$errNum = ERR_IMAGE_TOO_WIDE;
									}
									if (isset($defs['maxHeight']) && $size[1] > $defs['maxHeight'])
									{		// Image too high
									//	$errNum = ERR_IMAGE_TOO_HIGH;
									}
								}
								else
								{
									// echo "Image {$img} not found or cannot size - original value {$value}<br />";
								}
								unset($img);
								break;
							default :
								echo "Invalid dbClean method: {$defs['dbClean']}<br />";	// Debug message
						}
					}
					$ret['data'][$dest] = $value;			// Success!!
				}
			}
			if ($errNum)
			{  // error to report
				$ret['errors'][$dest] = $errNum;
				if ($defs['dataType'] == 2)
				{
					$ret['failed'][$dest] = str_repeat('*',strlen($sourceFields[$src]));		// Save value with error - obfuscated
				}
				else
				{
					$ret['failed'][$dest] = $sourceFields[$src];		// Save value with error
				}
			}
		}
		return $ret;
	}


/*
	// Validate data against a DB table
	//  Inspects the passed array of user data (not necessarily containing all possible fields) and validates against the DB where appropriate.
	//  Just skips over fields for which we don't have a validation routine without an error
	//	The target array is as returned from validateFields(), so has 'data', 'failed' and 'errors' first-level sub-arrays
	//  All the 'vetting methods' begin 'vet', and don't overlap with validateFields(), so the same definition array may be used for both
	//	Similarly, error numbers don't overlap with validateFields()
	//	Typically checks for unacceptable duplicates, banned users etc
	//	Any errors are reflected by updating the passed array.
	//	Returns TRUE if all data validates, FALSE if any field fails to validate. Checks all fields which are present, regardless
	//  For some things we need to know the user_id of the data being validated, so may return an error if that isn't specified

	Parameters:
		'vetMethod' - see list below. To use more than one method, specify comma-separated
		'vetParam' - possible parameter for some vet methods

	Valid 'vetMethod' values (use comma separated list for multiple vetting):
		0 - Null method
		1 - Check for duplicates - field name in table must be the same as array index unless 'dbFieldName' specifies otherwise
		2 - Check against the comma-separated wordlist in the $pref named in vetParam['signup_disallow_text']
		3 - Check email address against remote server, only if option enabled

*/
	public static function dbValidateArray(&$targetData, &$definitions, $targetTable, $userID = 0)
	{
		global $pref;
		$u_sql = new db;
		$allOK = TRUE;
		$userID = intval($userID);			// Precautionary
		$errMsg = '';
		if (!$targetTable) return FALSE;
		foreach ($targetData['data'] as $f => $v)
		{
			$errMsg = '';
			if (isset($definitions[$f]))
			{
				$options = $definitions[$f];			// Validation options to use
				if (!vartrue($options['fieldOptional']) || ($v != ''))
				{
					$toDo = explode(',',$options['vetMethod']);
					foreach ($toDo as $vm)
					{
						switch ($vm)
						{
							case 0 :		// Shouldn't get this - just do nothing if we do
								break;
							case 1 :		// Check for duplicates.
								if ($v == '')
								{
									$errMsg = ERR_MISSING_VALUE;
									break;
								}
								$field = varset($options['dbFieldName'],$f);
								if ($temp = $u_sql->count($targetTable, "(*)", "WHERE `{$f}`='".filter_var($v, FILTER_SANITIZE_STRING)."' AND `user_id` != ".$userID))
								{
									$errMsg = ERR_DUPLICATE;
								}
//								echo "Duplicate check: {$f} = {$v} Result: {$temp}<br />";
								break;
							case 2 :		// Check against $pref
								if (isset($options['vetParam']) && isset($pref[$options['vetParam']]))
								{
									$tmp = explode(",", $pref[$options['vetParam']]);
									foreach($tmp as $disallow)
									{
										if ('!' == substr(trim($disallow), -1) && $v == str_replace('!', '', $disallow))
										{	// Exact match search (noticed with exclamation mark in the end of the word)
											$errMsg = ERR_DISALLOWED_TEXT_EXACT_MATCH;
										}
										elseif(stristr($v, trim($disallow)))
										{	// Wild card search
											$errMsg = ERR_DISALLOWED_TEXT;
										}
									}
									unset($tmp);
								}
								break;
							case 3 :			// Check email address against remote server

							/*	if (vartrue($pref['signup_remote_emailcheck']))
								{
									require_once(e_HANDLER."mail_validation_class.php");
									list($adminuser,$adminhost) = split ("@", SITEADMINEMAIL);
									$validator = new email_validation_class;
									$validator->localuser= $adminuser;
									$validator->localhost= $adminhost;
									$validator->timeout=3;
										//	$validator->debug=1;
										//	$validator->html_debug=1;
									if($validator->ValidateEmailBox(trim($v)) != 1)
									{
										$errMsg = ERR_INVALID_EMAIL;
									}
								}
							*/
								break;
							default :
								echo 'Invalid vetMethod: '.$options['vetMethod'].'<br />';	// Really a debug aid - should never get here
						}
						if ($errMsg) { break; }			// Just trap first error
					}
					// Add in other validation methods here
				}
			}
			if ($errMsg)
			{	// Update the error
				$targetData['errors'][$f] = $errMsg;
				$targetData['failed'][$f] = $v;
				unset($targetData['data'][$f]);			// Remove the valid entry
				$allOK = FALSE;
			}
		}
		return $allOK;
	}


	// Given a comma-separated string of required fields, and an array of data, adds an error message for each field which doesn't already have an entry.
	// Returns TRUE if no changes (which doesn't mean there are no errors - other routines may have found them). FALSE if new errors
	public static function checkMandatory($fieldList, &$target)
	{
		$fields = explode(',', $fieldList);
		$allOK = TRUE;
		foreach ($fields as $f)
		{
			if (!isset($target['data'][$f]) && !isset($target['errors'][$f]))
			{
				$allOK = FALSE;
				$targetData['errors'][$f] = ERR_MISSING_VALUE;
			}
		}
		return $allOK;
	}


	// Adds the _FIELD_TYPES array to the data, ready for saving in the DB.
	// $fieldList is the standard definition array
	public static function addFieldTypes($fieldList, &$target, $auxList=FALSE)
	{
		$target['_FIELD_TYPES'] = array();		// We should always want to recreate the array, even if it exists
		foreach ($target['data'] as $k => $v)
		{
			if (isset($fieldList[$k]) && isset($fieldList[$k]['fieldType']))
			{
				$target['_FIELD_TYPES'][$k] = $fieldList[$k]['fieldType'];
			}
			elseif (is_array($auxList) && isset($auxList[$k]))
			{
				$target['_FIELD_TYPES'][$k] = $auxList[$k];
			}
		}
	}



	// Given two arrays, returns an array of those elements in $input which are different from the corresponding element in $refs.
	// If $addMissing == TRUE, includes any element in $input for which there isn't a corresponding element in $refs
	public static function findChanges(&$input, &$refs, $addMissing = FALSE)
	{
		$ret = array();
		foreach ($input as $k => $v)
		{
			if (array_key_exists($k, $refs))
			{
				if ($refs[$k] != $v) { $ret[$k] = $v; }
			}
			else
			{
				if ($addMissing) { $ret[$k] = $v; }
			}
		}
		return $ret;
	}


	// Given a vetted array of variables, generates a list of errors using the specified format string.
	// %n is the error number (as stored on the array)
	// %t is the corresponding error message, made by concatenating $constPrefix and the error number to form a constant (e.g. $constPrefix = 'USER_ERROR_')
	// %v calls up the entered value
	// %f is the field name
	// %x is the 'nice name' - possible if parameter list passed. Otherwise field name added
	// $EOL is inserted after all messages except the last.
	// If $EOL is an empty string, returns an array of messages.
	public static function makeErrorList($vars, $constPrefix, $format = '%n - %x %t: %v', $EOL = '<br />', $niceNames = NULL)
	{
		if (count($vars['errors']) == 0) return '';
		$eList = array();
		$checkNice = ($niceNames != NULL) && is_array($niceNames);
		foreach ($vars['errors'] as $f => $n)
		{
			$curLine = $format;
			$curLine = str_replace('%n', $n, $curLine);
			if (($n == ERR_GENERIC) && isset($vars['errortext'][$f]))
			{
				$curLine = str_replace('%t', $vars['errortext'][$f], $curLine);			// Coder-defined specific error text
			}
			else
			{
				$curLine = str_replace('%t', constant($constPrefix.$n), $curLine);		// Standard messages
			}
			if(empty($vars['failed'][$f]))
			{
				$vars['failed'][$f] = LAN_VALIDATE_191; 
		//		print_a($vars['failed']);
			}
			$curLine = str_replace('%v', filter_var($vars['failed'][$f], FILTER_SANITIZE_SPECIAL_CHARS), $curLine);
			$curLine = str_replace('%f', $f, $curLine);
			if ($checkNice & isset($niceNames[$f]['niceName']))
			{
				$curLine = str_replace('%x', $niceNames[$f]['niceName'], $curLine);
			}
			else
			{
				$curLine = str_replace('%x', $f, $curLine);		// Just use the field name
			}
			$eList[] = $curLine;
		}
		if ($EOL == '') return $eList;
		return implode($EOL, $eList);
	}
}


