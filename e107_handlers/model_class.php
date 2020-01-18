<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Base Model
 *
 * $Id$
 * $Author$
*/

if (!defined('e107_INIT')) { exit; }

/**
 * Base e107 Object class
 *
 * @package e107
 * @category e107_handlers
 * @version 1.0
 * @author SecretR
 * @copyright Copyright (C) 2010, e107 Inc.
 */
class e_object
{
    /**
     * Object data
     *
     * @var array
     */
    protected $_data = array();

	/**
	 * Model parameters passed mostly from external sources
	 *
	 * @var array
	 */
	protected $_params = array();


    /**
    * Name of object id field
    * Required for {@link getId()()} method
    *
    * @var string
    */
    protected $_field_id;

    /**
     * Constructor - set data on initialization
     *
     * @param array $data
     */
	function __construct($data = array())
	{
		if(is_array($data)) $this->setData($data);
	}

    /**
     * Set name of object's field id
     *
     * @see getId()
     *
     * @param   string $name
     * @return object e_object
     */
    public function setFieldIdName($name)
    {
        $this->_field_id = $name;
        return $this;
    }

    /**
     * Retrieve name of object's field id
     *
     * @see getId()
     *
     * @param   string $name
     * @return  string
     */
    public function getFieldIdName()
    {
        return $this->_field_id;
    }

    /**
     * Retrieve object primary id field value
     *
     * @return mixed
     */
    public function getId()
    {

        if ($this->getFieldIdName())
        {
            return $this->get($this->getFieldIdName(), 0); // default of NULL will break MySQL strict in most cases.
        }
        return $this->get('id', 0);
    }

    /**
     * Set object primary id field value
     *
     * @return e_object
     */
    public function setId($id)
    {
        if ($this->getFieldIdName())
        {
            return $this->set($this->getFieldIdName(), $id);
        }
        return $this;
    }

    /**
     * Retrieves data from the object ($_data) without
     * key parsing (performance wise, prefered when possible)
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
	public function get($key, $default = null)
    {
    	return (isset($this->_data[$key]) ? $this->_data[$key] : $default);
    }

    /**
     * Get object data
     * @return array
     */
	public function getData()
    {
    	return $this->_data;
    }

    /**
     * Overwrite data in the object for a single field.
     *
     * @param string $key
     * @param mixed $value
     * @return e_object
     */
	public function set($key, $value)
    {
    	$this->_data[$key] = $value;
    	return $this;
    }

    /**
     * Set object data
     * @return e_object
     */
	public function setData($data)
    {
    	$this->_data = $data;
    	return $this;
    }

    /**
     * Update object data
     * @return e_object
     */
	public function addData($data)
    {
    	foreach($data as $key => $val)
		{
			$this->set($key, $val);
		}
    	return $this;
    }

    /**
     * Remove object data key
     *
     * @param string $key
     * @return e_object
     */
	public function remove($key)
    {
    	unset($this->_data[$key]);
    	return $this;
    }

    /**
     * Reset  object data key
     *
     * @return e_object
     */
	public function removeData()
    {
    	$this->_data = array();
    	return $this;
    }

    /**
     * Check if key is set
     * @param string $key
     * @return boolean
     */
    public function is($key)
    {
    	return (isset($this->_data[$key]));
    }

    /**
     * Check if key is set and not empty
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
    	return (isset($this->_data[$key]) && !empty($this->_data[$key]));
    }

    /**
     * Check if object has data
     * @return boolean
     */
    public function hasData()
    {
    	return !empty($this->_data);
    }

	/**
	 * Set parameter array
	 * @param array $params
	 * @return e_object
	 */
	public function setParams(array $params)
	{
		$this->_params = $params;
		return $this;
	}

	/**
	 * Update parameter array
	 * @param array $params
	 * @return object e_object
	 */
	public function updateParams(array $params)
	{
		foreach ($params as $k => $v)
		{
			$this->setParam($k, $v);
		}

		return $this;
	}

	/**
	 * Get parameter array
	 *
	 * @return array parameters
	 */
	public function getParams()
	{
		return $this->_params;
	}

	/**
	 * Set parameter
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return e_tree_model
	 */
	public function setParam($key, $value)
	{
		if(null === $value)
		{
			unset($this->_params[$key]);
		}
		else $this->_params[$key] = $value;

		return $this;
	}

	/**
	 * Get parameter
	 *
	 * @param string $key
	 * @param mixed $default
	 */
	public function getParam($key, $default = null)
	{
		return (isset($this->_params[$key]) ? $this->_params[$key] : $default);
	}

	/**
	 * Convert object data to simple shortcodes (e_vars object)
	 * @return string
	 */
	public function toSc()
	{
		return new e_vars($this->_data);
	}

	/**
	 * Convert object data to array
	 * @return string
	 */
	public function toJson()
	{
		return json_encode($this->_data);
	}

	/**
	 * Convert object to array
	 * @return array object data
	 */
	public function toArray()
	{
		return $this->_data;
	}

	/**
	 * Magic method - convert object data to an array
	 *
	 * @return array
	 */
	public function __toArray()
	{
		return $this->toArray();
	}

	/**
	 * Convert object data to a string
	 *
	 * @param boolean $AddSlashes
	 * @return string
	 */
	public function toString($AddSlashes = false)
	{
		return (string) e107::getArrayStorage()->WriteArray($this->toArray(), $AddSlashes);
	}

	/**
	 * Magic method - convert object data to a string
	 * NOTE: before PHP 5.2.0 the __toString method was only
	 * called when it was directly combined with echo() or print()
	 *
	 * NOTE: PHP 5.3+ is throwing parse error if __toString has optional arguments.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString(false);
	}

	/**
	 * Magic setter
	 * Triggered on e.g. <code><?php $e_object->myKey = 'someValue'; </code>
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value)
	{
		// Unset workaround - PHP < 5.1.0
		if(null === $value) $this->remove($key);
		else $this->set($key, $value);
	}

	/**
	 * Magic getter
	 * Triggered on e.g. <code><?php print($e_object->myKey); </code>
	 * @param string $key
	 * @return mixed value or null if key not found
	 */
	public function __get($key)
	{
		if($this->is($key))
		{
			return $this->get($key);
		}

		return null;
	}

	/**
	 * Magic method to check if given data key is set.
	 * Triggered on <code><?php isset($e_object->myKey); </code>
	 * NOTE: works on PHP 5.1.0+
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		return $this->is($key);
	}

	/**
	 * Magic method to unset given data key.
	 * Triggered on <code><?php unset($e_object->myKey); </code>
	 * NOTE: works on PHP 5.1.0+
	 *
	 * @param string $key
	 */
	public function __unset($key)
	{
		$this->remove($key);
	}
}


/**
 * Data object for e_parse::simpleParse()
 * NEW - not inherits core e_object
 * Moved from e_parse_class.php
 * Could go in separate file in the future, together with e_object class
 */
class e_vars extends e_object
{
	/**
	 * Get data array
	 *
	 * @return array
	 */
	public function getVars()
	{
		return $this->getData();
	}

	/**
	 * Set array data
	 *
	 * @param array $array
	 * @return e_vars
	 */
	public function setVars(array $array)
	{
		$this->setData($array);
		return $this;
	}

	/**
	 * Add array data to the object (merge with existing)
	 *
	 * @param array $array
	 * @return e_vars
	 */
	public function addVars(array $array)
	{
		$this->addData($array);
	}

	/**
	 * Reset object data
	 *
	 * @return e_vars
	 */
	public function emptyVars()
	{
		$this->removeData();
		return $this;
	}

	/**
	 * Check if there is data available
	 *
	 * @return boolean
	 */
	public function isEmpty()
	{
		return (!$this->hasData());
	}

	/**
	 * Check if given data key is set
	 * @param string $key
	 * @return boolean
	 */
	public function isVar($key)
	{
		return $this->is($key);
	}

	/**
	 * No need of object conversion, optional cloning
	 * @param boolean $clone return current object clone
	 * @return e_vars
	 */
	public function toSc($clone = false)
	{
		if($clone) return clone $this;
		return $this;
	}
}

/**
 * Base e107 Model class
 *
 * @package e107
 * @category e107_handlers
 * @version 1.0
 * @author SecretR
 * @copyright Copyright (C) 2010, e107 Inc.
 */
class e_model extends e_object
{
    /**
     * Data structure (types) array, required for {@link e_front_model::sanitize()} method,
     * it also serves as a map (find data) for building DB queries,
     * copy/sanitize posted data to object data, etc.
     *
     * This can/should be overwritten by extending the class
     *
     * @var array
     */
    protected $_data_fields = array();



	/**
	 * Current model field types eg. text, bbarea, dropdown etc.
	 *
	 *
	 * @var string
	 */
	protected $_field_input_types = array();


	/**
	 * Current model DB table, used in all db calls
	 *
	 * This can/should be overwritten/set by extending the class
	 *
	 * @var string
	 */
	protected $_db_table;

    /**
     * Current url Profile data
	 * Example: array('route'=>'page/view/index', 'vars' => array('id' => 'page_id', 'sef' => 'page_sef'), 'name' => 'page_title', 'description' => '');
     * @var string
     */
    protected $_url = array();


    /**
     * Current Featurebox Profile data
	 * Example: array('title' => 'page_title', 'text' => '');
     * @var string
     */
    protected $_featurebox = array();

    /**
     * Runtime cache of parsed from {@link _getData()} keys
     *
     * @var array
     */
    protected $_parsed_keys = array();

    /**
     * Avoid DB calls if data is not changed
     *
     * @see _setData()
     * @var boolean
     */
    protected $data_has_changed = false;

	/**
	 * Namespace to be used for model related system messages in {@link eMessage} handler
	 *
	 * @var string
	 */
	protected $_message_stack = 'default';

	/**
	 * Cache string to be used from _get/set/clearCacheData() methods
	 *
	 * @var string
	 */
	protected $_cache_string = null;

	/**
	 * Force Cache even if system cahche is disabled
	 * Default is false
	 *
	 * @var boolean
	 */
	protected $_cache_force = false;


	/**
	 * Optional DB table - used for auto-load data from the DB
	 * @param string $table
	 * @return e_model
	 */
	public function getModelTable()
	{
		return $this->_db_table;
	}

	/**
	 * Set model DB table
	 * @param string $table
	 * @return e_model
	 */
	public function setModelTable($table)
	{
		$this->_db_table = $table;
		return $this;
	}


    /**
     * Set model Url Profile
     * @param string $table
     * @return e_model
     */
    public function setUrl($url)
    {
    	if(!is_array($url)) $url = array('route' => $url);
        $this->_url = $url;
        return $this;
    }

    /**
     * Get url profile
     * @return array
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Set model Featurebox  Profile
     * @param string $table
     * @return e_model
     */
    public function setFeaturebox($fb)
    {
    //	if(!is_array($url)) $url = array('route' => $url);
        $this->_featurebox = $fb;
        return $this;
    }

    /**
     * Get Featurebox profile
     * @return array
     */
    public function getFeaturebox()
    {
        return $this->_featurebox;
    }


    /**
     * Generic URL assembling method
	 * @param array $options [optional] see eRouter::assemble() for $options structure
	 * @param boolean $extended [optional] if true, method will return an array containing url, title and description of the url
     * @return mixed URL string or extended array data
     */
    public function url($ids, $options = array(), $extended = false)
    {
        $urldata = $this->getUrl();
		if(empty($urldata) || !vartrue($urldata['route'])) return ($extended ? array() : null);

		$eurl = e107::getUrl();

		if(empty($options)) $options = array();
		elseif(!is_array($options)) parse_str($options, $options);

		$vars = $this->toArray();
		if(!isset($options['allow']) || empty($options['allow']))
		{
			if(vartrue($urldata['vars']) && is_array($urldata['vars']))
			{
				$vars = array();
				foreach ($urldata['vars'] as $var => $field)
				{
					if($field === true) $field = $var;
					$vars[$var] = $this->get($field);
				}
			}
		}

		$method = isset($options['sc']) ? 'sc' : 'create';

		$url = e107::getUrl()->$method($urldata['route'], $vars, $options);

		if(!$extended)
		{
			return $url;
		}

		return array(
			'url' => $url,
			'name' => vartrue($urldata['name']) ? $this->get($urldata['name']) : '',
			'description' => vartrue($urldata['description']) ? $this->get($urldata['description']) : '',
		);
    }


     /**
     * Generic Featurebox assembling method
     * @return mixed URL string or extended array data
     */
    public function featurebox($options = array(), $extended = false)
    {


    }













    /**
     * Get data fields array
     * @return array
     */
    public function getDataFields()
    {
    	return $this->_data_fields;
    }


	/**
	 * @param $key
	 * @return bool
	 */
	public function getFieldInputType($key)
    {
        if(isset($this->_field_input_types[$key]))
        {
            return $this->_field_input_types[$key];
        }

        return false;
    }

    /**
     * Set Predefined data fields in format key => type
     * @return object e_model
     */
    public function setDataFields($data_fields)
    {
    	$this->_data_fields = $data_fields;
		return $this;
    }

	/**
     * Set Predefined data fields in format key => type
     * @return e_model
     */
    public function setFieldInputTypes($fields)
    {
    	$this->_field_input_types = $fields;
		return $this;
    }

    /**
     * Set Predefined data field
     * @return e_model
     */
    public function setDataField($field, $type)
    {
    	$this->_data_fields[$field] = $type;
		return $this;
    }

    /**
     * Retrieves data from the object ($_data) without
     * key parsing (performance wise, prefered when possible)
     *
     * @see _getDataSimple()
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
	public function get($key, $default = null)
    {
    	return $this->_getDataSimple((string) $key, $default);
    }

    /**
     * Retrieves data from the object ($_data)
     * If $key is empty, return all object data
     *
     * @see _getData()
     * @param string $key
     * @param mixed $default
     * @param integer $index
     * @return mixed
     */
	public function getData($key = '', $default = null, $index = null)
    {
    	return $this->_getData($key, $default, $index);
    }

    /**
     * Overwrite data in the object for a single field. Key is not parsed.
     * Public proxy of {@link _setDataSimple()}
     * Data isn't sanitized so use this method only when data comes from trustable sources (e.g. DB)
     *
     *
     * @see _setData()
     * @param string $key
     * @param mixed $value
     * @param boolean $strict update only
     * @return e_model
     */
	public function set($key, $value = null, $strict = false)
    {
    	return $this->_setDataSimple($key, $value, $strict);
    }

    /**
     * Overwrite data in the object. Public proxy of {@link _setData()}
     * Data isn't sanitized so use this method only when data comes from trustable sources (e.g. DB)
     *
     * @see _setData()
     * @param string|array $key
     * @param mixed $value
     * @param boolean $strict update only
     * @return e_model
     */
	public function setData($key, $value = null, $strict = false)
    {
    	return $this->_setData($key, $value, $strict);
    }

    /**
     * Add data to the object.
     * Retains existing data in the object.
     * Public proxy of {@link _addData()}
     *
     * If $override is false, data will be updated only (check against existing data)
     *
     * @param string|array $key
     * @param mixed $value
     * @param boolean $override override existing data
     * @return e_model
     */
    public function addData($key, $value = null, $override = true)
    {
    	return $this->_addData($key, $value, $override);
    }

	/**
     * Unset single field from the object.
     * Public proxy of {@link _unsetDataSimple()}
     *
     * @param string $key
     * @return e_model
     */
    public function remove($key)
    {
    	return $this->_unsetDataSimple($key);
    }

	/**
     * Unset data from the object.
     * $key can be a string only. Array will be ignored.
     * '/' inside the key will be treated as array path
     * if $key is null entire object will be reset
     *
     * Public proxy of {@link _unsetData()}
     *
     * @param string|null $key
     * @return e_model
     */
    public function removeData($key = null)
    {
    	return $this->_unsetData($key);
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
    	return $this->_hasData($key);
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function hasData($key = '')
    {
    	return $this->_hasData($key);
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function isData($key)
    {
    	return $this->_isData($key);
    }

    /**
     * @param boolean $new_state new object state if set
     * @return boolean
     */
    public function isModified($new_state = null)
    {
    	if(is_bool($new_state))
    	{
    		$this->data_has_changed = $new_state;
    	}
    	return $this->data_has_changed;
    }

    /**
     * Retrieves data from the object
     *
     * If $key is empty will return all the data as an array
     * Otherwise it will return value of the attribute specified by $key
     * '/' inside the key will be treated as array path (x/y/z equals to [x][y][z]
     *
     * If $index is specified it will assume that attribute data is an array
     * and retrieve corresponding member.
     *
     * NEW: '/' supported in keys now, just use double slashes '//' as key separator
     * Examples:
     * - 'key//some/key/with/slashes//more' -> [key][some/key/with/slashes][more]
     * - '//some/key' -> [some/key] - starting with // means - don't parse!
     * - '///some/key/' -> [/some/key/]
     * - '//key//some/key/with/slashes//more' WRONG -> single key [key//some/key/with/slashes//more]
     *
     * @param string $key
     * @param mixed $default
     * @param integer $index
     * @param boolean $posted data source
     * @return mixed
     */
    protected function _getData($key = '', $default = null, $index = null, $data_src = '_data')
    {
        if ('' === $key)
        {
            return $this->$data_src;
        }

        $simple = false;
        if(strpos($key, '//') === 0)
        {
        	$key = substr($key, 2);
        	$simple = true;
        }
        /*elseif($key[0] == '/')
        {
        	// just use it!
        	$simple = true;
        }*/
        else
        {
        	$simple = strpos($key, '/') === false;
        }

        // Fix - check if _data[path/to/value] really doesn't exist
        if (!$simple)
        {
        	//$key = trim($key, '/');
        	if(isset($this->_parsed_keys[$data_src.'/'.$key]))
        	{
        		return $this->_parsed_keys[$data_src.'/'.$key];
        	}
        	// new feature (double slash) - when searched key string is key//some/key/with/slashes//more
        	// -> search for 'key' => array('some/key/with/slashes', array('more' => value));
            $keyArr = explode(strpos($key, '//') ? '//' : '/', $key);
            $data = $this->$data_src;
            foreach ($keyArr as $i => $k)
            {
                if ('' === $k)
                {
                    return $default;
                }

                if (is_array($data))
                {
                    if (!isset($data[$k]))
                    {
                        return $default;
                    }
                    $data = $data[$k];
                }
                else
                {
                    return $default;
                }
            }
            $this->_parsed_keys[$data_src.'/'.$key] = $data;
            return $data;
        }

        //get $index
        if (isset($this->{$data_src}[$key]))
        {
            if (null === $index)
            {
                return $this->{$data_src}[$key];
            }

            $value = $this->{$data_src}[$key];
            if (is_array($value))
            {
                if (isset($value[$index]))
                {
                    return $value[$index];
                }
                return $default;
            }
            elseif (is_string($value))
            {
                $arr = explode("\n", $value);
                return (isset($arr[$index]) ? $arr[$index] : $default);
            }
            return $default;
        }
        return $default;
    }

    /**
     * Get value from _data array without parsing the key
     *
     * @param string $key
     * @param mixed $default
     * @param string $posted data source
     * @return mixed
     */
    protected function _getDataSimple($key, $default = null, $data_src = '_data')
    {
        return isset($this->{$data_src}[$key]) ? $this->{$data_src}[$key] : $default;
    }

    /**
     * Overwrite data in the object.
     *
     * $key can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     * '/' inside the key will be treated as array path
     *
     * If $key is an array and $strict is false, it will overwrite all the data in the object.
     *
     * If $strict is true and $data_src is '_data', data will be updated only (no new data will be added)
     *
     * NEW: '/' supported in keys now, just use double slashes '//' as key separator
     * Examples:
     * - 'key//some/key/with/slashes//more' -> [key][some/key/with/slashes][more]
     * - '//some/key' -> [some/key] - starting with // means - don't parse!
     * - '///some/key/' -> [/some/key/]
     * - '//key//some/key/with/slashes//more' WRONG -> single key [key//some/key/with/slashes//more]
     *
     *
     * @param string|array $key
     * @param mixed $value
     * @param boolean $strict
     * @param string $data_src
     * @return e_model
     */
    protected function _setData($key, $value = null, $strict = false, $data_src = '_data')
    {
        if(is_array($key))
        {
            if($strict)
	    	{
				foreach($key as $k => $v)
		        {
		        	$this->_setData($k, $v, true, $data_src);
		        }
		        return $this;
	    	}

            $this->$data_src = $key;
            return $this;
        }

        //multidimensional array support - strict _setData for values of type array
    	if($strict && !empty($value) && is_array($value))
       	{
			foreach($value as $k => $v)
			{
				// new - $k couldn't be a path - e.g. 'key' 'value/value1'
				// will result in 'key' => 'value/value1' and NOT 'key' => array('value' => value1)
			    $this->_setData($key.'//'.$k, $v, true, $data_src);
			}
			return $this;
       	}

        $simple = false;
        if(strpos($key, '//') === 0)
        {
        	// NEW - leading '//' means set 'some/key' without parsing it
        	// Example: '//some/key'; NOTE: '//some/key//more/depth' is NOT parsed
        	// if you wish to have array('some/key' => array('more/depth' => value))
        	// right syntax is 'some/key//more/depth'
        	$key = substr($key, 2);
        	$simple = true;
        }
        /*elseif($key[0] == '/')
        {
        	$simple = true;
        }*/
        else
        {
        	$simple = strpos($key, '/') === false;
        }

        //multidimensional array support - parse key
        if(!$simple)
        {
        	//$key = trim($key, '/');
        	//if strict - update only
	        if($strict && !$this->isData($key))
	        {
	        	return $this;
	        }

        	// new feature (double slash) - when parsing key: key//some/key/with/slashes//more
        	// -> result is 'key' => array('some/key/with/slashes', array('more' => value));
	        $keyArr = explode(strpos($key, '//') ? '//' : '/', $key);
        	//$keyArr = explode('/', $key);
        	$data = &$this->{$data_src};
            for ($i = 0, $l = count($keyArr); $i < $l; $i++)
            {

	            $k = $keyArr[$i];

	            if (!isset($data[$k]) || empty($data[$k])) // PHP7.1 fix. Reset to empty array() if $data[$k] is an empty string. Reason for empty string still unknown.
	            {
	                $data[$k] = array();
	            }

	            $data = &$data[$k];
	        }

	        //data has changed - optimized
	        if('_data' === $data_src && !$this->data_has_changed)
	        {
	        	$this->data_has_changed = (!isset($this->_data[$key]) || $this->_data[$key] != $value);
	        }
	        $this->_parsed_keys[$data_src.'/'.$key] = $value;
	        $data = $value;
        }
        else
        {
			//if strict - update only
	        if($strict && !isset($this->_data[$key]))
	        {
	        	return $this;
	        }
        	if('_data' === $data_src && !$this->data_has_changed)
	        {
	        	$this->data_has_changed = (!isset($this->_data[$key]) || $this->{$data_src}[$key] != $value);
	        }
            $this->{$data_src}[$key] = $value;
        }

        return $this;
    }

    /**
     * Set data for the given source. More simple (and performance wise) version
     * of {@link _setData()}
     *
     * @param string $key
     * @param mixed $value
     * @param boolean $strict
     * @param string $data_src
     * @return e_model
     */
	protected function _setDataSimple($key, $value = null, $strict = false, $data_src = '_data')
    {
    	$key = $key.'';//smart toString
    	if(!$strict)
    	{
			//data has changed
	        if('_data' === $data_src && !$this->data_has_changed)
	        {
	        	$this->data_has_changed = (!isset($this->_data[$key]) || $this->_data[$key] != $value);
	        }
	        $this->{$data_src}[$key] = $value;
    		return $this;
    	}

    	if($this->isData($key))
    	{
			if('_data' === $data_src && !$this->data_has_changed)
	        {
	        	$this->data_has_changed = (!isset($this->_data[$key]) || $this->_data[$key] != $value);
	        }
	        $this->{$data_src}[$key] = $value;
    	}

    	return $this;
    }

    /**
     * Add data to the object.
     * Retains existing data in the object.
     *
     * If $override is false, only new (non-existent) data will be added
     *
     * @param string|array $key
     * @param mixed $value
     * @param boolean $override allow override of existing data
     * @param string $data_src data source
     * @return e_model
     */
    protected function _addData($key, $value = null, $override = true, $data_src = '_data')
    {
    	if(is_array($key))
    	{
			foreach($key as $k => $v)
			{
			    $this->_addData($k, $v, $override, $data_src);
			}
			return $this;
    	}

		if($override || !$this->_isData($key, $data_src))
       	{
       		if(is_array($value))
       		{
				if(is_array($key))
				{
					foreach($key as $k => $v)
					{
					    $this->_addData($key.'/'.$k, $v, $override, $data_src);
					}
				}
				return $this;
       		}
       		$this->_setData($key, $value, false, $data_src);
       	}
        return $this;
    }

    /**
     * Unset data from the object from the given source.
     * $key can be a string only. Array will be ignored.
     * '/' inside the key will be treated as array path
     * if $key is null entire object will be reset
     *
     * @param string|null $key
     * @param string $data_src data source
     * @return e_model
     */
    protected function _unsetData($key = null, $data_src = '_data')
    {
        if (null === $key)
        {
        	if('_data' === $data_src && !empty($this->_data))
        	{
        		$this->data_has_changed = true;
        	}
        	$this->{$data_src} = array();
            return $this;
        }

        $key = trim($key, '/');
        if(strpos($key,'/'))
        {
        	$keyArr = explode('/', $key);
        	$data = &$this->{$data_src};

        	$unskey = array_pop($keyArr);
            for ($i = 0, $l = count($keyArr); $i < $l; $i++)
            {
	            $k = $keyArr[$i];
	            if (!isset($data[$k]))
	            {
	                return $this; //not found
	            }
	            $data = &$data[$k];
	        }
	        if(is_array($data))
	        {
				if('_data' === $data_src && isset($data[$unskey]))
	        	{
	        		$this->data_has_changed = true;
	        	}
	        	unset($data[$unskey], $this->_parsed_keys[$data_src.'/'.$key]);
	        }
        }
        else
        {
       		if('_data' === $data_src && isset($this->{$data_src}[$key]))
        	{
        		$this->data_has_changed = true;
        	}
            unset($this->{$data_src}[$key]);
        }
        return $this;
    }

	/**
     * Unset single field from the object from the given source. Key is not parsed
     *
     * @param string $key
     * @param string $data_src data source
     * @return e_model
     */
    protected function _unsetDataSimple($key, $data_src = '_data')
    {
		if('_data' === $data_src && isset($this->{$data_src}[$key]))
       	{
       		$this->data_has_changed = true;
       	}
    	unset($this->{$data_src}[$key]);
    	return $this;
    }

    /**
     * If $key is empty, checks whether there's any data in the object
     * Otherwise checks if the specified key is empty/set.
     *
     * @param string $key
     * @param string $data_src data source
     * @return boolean
     */
    protected function _hasData($key = '', $data_src = '_data')
    {
        if (empty($key))
        {
            return !empty($this->$data_src);
        }
        $value = $this->_getData($key, null, null, $data_src);
        return !empty($value);
    }

    /**
     * Checks if the specified key is set
     *
     * @param string $key
     * @param string $data_src data source
     * @return boolean
     */
    protected function _isData($key, $data_src = '_data')
    {
        return (null !== $this->_getData($key, null, null, $data_src));
    }

	/**
	 * Add system message of type Information
	 *
	 * @param string $message
	 * @param boolean $session [optional]
	 * @return e_model
	 */
	public function addMessageInfo($message, $session = false)
	{
		e107::getMessage()->addStack($message, $this->_message_stack, E_MESSAGE_INFO, $session);
		return $this;
	}

	/**
	 * Add system message of type Success
	 *
	 * @param string $message
	 * @param boolean $session [optional]
	 * @return e_model
	 */
	public function addMessageSuccess($message, $session = false)
	{
		e107::getMessage()->addStack($message, $this->_message_stack, E_MESSAGE_SUCCESS, $session);
		return $this;
	}

	/**
	 * Add system message of type Warning
	 *
	 * @param string $message
	 * @param boolean $session [optional]
	 * @return e_model
	 */
	public function addMessageWarning($message, $session = false)
	{
		e107::getMessage()->addStack($message, $this->_message_stack, E_MESSAGE_WARNING, $session);
		return $this;
	}

	/**
	 * Add system message of type Error
	 *
	 * @param string $message
	 * @param boolean $session [optional]
	 * @param array $logData [optional] array('TABLE'=>'', 'ERROR'=>'') etc.
	 * @return e_model
	 */
	public function addMessageError($message, $session = false, $logData = array())
	{
		e107::getMessage()->addStack($message, $this->_message_stack, E_MESSAGE_ERROR, $session);

		if(!empty($logData))
		{
			e107::getAdminLog()->addArray($logData);
		}
		else
		{
			e107::getAdminLog()->addError($message,false);
		}

		e107::getAdminLog()->save('ADMINUI_04', E_LOG_WARNING);

		return $this;
	}

	/**
	 * Add system message of type Information
	 *
	 * @param string $message
	 * @param boolean $session [optional]
	 * @return e_model
	 */
	public function addMessageDebug($message, $session = false)
	{
		e107::getMessage()->addStack($message, $this->_message_stack, E_MESSAGE_DEBUG, $session);
		return $this;
	}

    /**
     * Render System messages (if any)
     *
     * @param boolean $session store messages to session
     * @param boolean $reset reset errors
     * @return string
     */
    public function renderMessages($session = false, $reset = true)
    {
    	return e107::getMessage()->render($this->_message_stack, $session, $reset);
    }

    /**
     * Move model System messages (if any) to the default eMessage stack
     *
     * @param boolean $session store messages to session
     * @return e_model
     */
    public function setMessages($session = false)
    {
    	e107::getMessage()->moveStack($this->_message_stack, 'default', false, $session);
		return $this;
    }

    /**
     * Reset model System messages
     *
     * @param boolean|string $type E_MESSAGE_INFO | E_MESSAGE_SUCCESS | E_MESSAGE_WARNING | E_MESSAGE_WARNING | E_MESSAGE_DEBUG | false (all)
     * @param boolean $session reset also session messages
     * @return e_model
     */
    public function resetMessages($type = false, $session = false)
    {
        e107::getMessage()->reset($type, $this->_message_stack, $session);
		return $this;
    }

	/**
	 * Set model message stack
	 * @param string $stack_name
	 * @return e_model
	 */
    public function setMessageStackName($stack_name)
    {
    	$this->_message_stack = $stack_name;
		return $this;
    }

	/**
	 * Get model message stack name
	 * @return string
	 */
    public function getMessageStackName()
    {
		return $this->_message_stack;
    }

    /**
     * User defined model validation
     * Awaiting for child class implementation
     *
     */
    public function verify()
    {
    }

    /**
     * Model validation
     * @see e_model_admin
     */
    public function validate()
    {
    }

    /**
     * Generic load data from DB
	 * @param mixed $id
     * @param boolean $force
     * @return e_model
     */
	public function load($id = null, $force = false)
	{


		if(!$force && $this->getId())
		{
			return $this;
		}

		if($force)
		{
			$this->setData(array())
				->_clearCacheData();
		}
		if($id) $id = e107::getParser()->toDB($id);
		if(!$id && !$this->getParam('db_query'))
		{
			return $this;
		}

		$cached = $this->_getCacheData();
		if($cached !== false)
		{
			$this->setData($cached);
			return $this;
		}

		$sql = e107::getDb();
		$qry = str_replace('{ID}', $id, $this->getParam('db_query'));
		if($qry)
		{
			$res = $sql->gen($qry, $this->getParam('db_debug') ? true : false);
		}
		else
		{
			if(!is_numeric($id)) $id = "'{$id}'";

			$res = $sql->select(
				$this->getModelTable(),
				$this->getParam('db_fields', '*'),
				$this->getFieldIdName().'='.$id.' '.trim($this->getParam('db_where', '')),
				'default',
				($this->getParam('db_debug') ? true : false)
			);
		}


		if($res)
		{
			$this->setData($sql->fetch());
		}

		if($sql->getLastErrorNumber())
		{
			$this->addMessageDebug('SQL error #'.$sql->getLastErrorNumber().': '.$sql->getLastErrorText());
			$this->addMessageDebug($sql->getLastQuery());
		}
		else
		{

			$this->_setCacheData();
		}

		return $this;
	}

	/**
	 * Retrieve system cache (if any)
	 * @return array|false
	 */
	protected function _getCacheData()
	{
		if(!$this->isCacheEnabled())
		{
			return false;
		}

		$cached = e107::getCache()->retrieve_sys($this->getCacheString(true), false, $this->_cache_force);
		if(false !== $cached)
		{
			return e107::unserialize($cached);
		}

		return false;
	}

	/**
	 * Set system cache if enabled for the model
	 * @return e_model
	 */
	protected function _setCacheData()
	{
		if(!$this->isCacheEnabled())
		{
			return $this;
		}
		e107::getCache()->set_sys($this->getCacheString(true), $this->toString(false), $this->_cache_force, false);
		return $this;
	}

	/**
	 * Clrear system cache if enabled for the model
	 * @return e_model
	 */
	protected function _clearCacheData()
	{
		if(!$this->isCacheEnabled(false))
		{
			return $this;
		}
		e107::getCache()->clear_sys($this->getCacheString(true), false);
		return $this;
	}

	/**
	 * Clrear system cache (public proxy) if enabled for the model
	 * @return e_model
	 */
	public function clearCache()
	{
		return $this->_clearCacheData();
	}

	/**
	 * Check if cache is enabled for the current model
	 * @param boolean $checkId check if there is model ID
	 * @return boolean
	 */
	public function isCacheEnabled($checkId = true)
	{
		return (null !== $this->getCacheString() && (!$checkId || $this->getId()));
	}

	/**
	 * Get model cache string
	 * @param boolean $replace try to add current model ID (replace destination is {ID})
	 * @return string
	 */
	public function getCacheString($replace = false)
	{
		return ($replace ? str_replace('{ID}', $this->getId(), $this->_cache_string) : $this->_cache_string);
	}

	/**
	 * Set model cache string
	 * @param string $str
	 * @return e_model
	 */
	public function setCacheString($str)
	{
		$this->_cache_string = $str;
		return $this;
	}

    /**
     * Save data to DB
     * Awaiting for child class implementation
     * @see e_model_admin
     */
    public function save()
    {
    }

    /**
     * Delete DB record
     * Awaiting for child class implementation
     * @see e_model_admin
     */
    public function delete($ids, $destroy = true, $session_messages = false)
    {
    }

    /**
     * Create new DB recorrd
     * Awaiting for child class implementation
     * @see e_model_admin
     */
    public function create()
    {
    }

    /**
     * Insert data to DB
     * Awaiting for child class implementation
     * @see e_model_admin
     */
    protected function dbInsert()
    {
    }

    /**
     * Update DB data
     * Awaiting for child class implementation
     * @see e_model_admin
     */
    protected function dbUpdate($force = false, $session_messages = false)
    {
    }

    /**
     * Replace DB record
     * Awaiting for child class implementation
     * @see e_model_admin
     */
    protected function dbReplace()
    {
    }

    /**
     * Delete DB data
     * Awaiting for child class implementation
     * @see e_model_admin
     */
    protected function dbDelete()
    {
    }

	/**
	 * Set parameter array
	 * Core implemented:
	 * - db_query: string db query to be passed to load() ($sql->gen())
	 * - db_query
	 * - db_fields
	 * - db_where
	 * - db_debug
	 * - model_class: e_tree_model class/subclasses - string class name for creating nodes inside default load() method
	 * - clearModelCache: e_tree_model class/subclasses - clear cache per node after successful DB operation
	 * - noCacheStringModify: e_tree_model class/subclasses - do not add additional md5 sum to tree cache string
	 * @param array $params
	 * @return e_model|e_tree_model
	 */
	public function setParams(array $params)
	{
		parent::setParams($params);
		return $this;
	}



	/**
	 * Render model data, all 'sc_*' methods will be recongnized
	 * as shortcodes.
	 *
	 * @param string $template
	 * @param boolean $parsesc parse external shortcodes, default is true
	 * @param e_vars $eVars simple parser data
	 * @return string parsed template
	 */
	public function toHTML($template, $parsesc = true, $eVars = null)
	{
		return e107::getParser()->parseTemplate($template, $parsesc, $this, $eVars);
	}

	/**
	 * Export a Model configuration
	 * @return string
	 */
	public function toXML()
	{
		$ret = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
		$ret .= "<e107Export type=\"model\" version=\"1.0\" timestamp=\"".time()."\" >\n";

		$ret .= "\t<data>\n";
		// TODO - handle multi dimensional arrays (already possible - field1/field2?), method toXMLValue($value, $type)
		foreach ($this->getDataFields() as $field => $type)
		{
			$ret .= "\t\t<field name=\"{$field}\" type=\"{$type}\">";
			$ret .= $type == 'str' || $type == 'string' ? "<![CDATA[".$this->getData($field)."]]>" : $this->getData($field);
			$ret .= "</field>\n";
		}
		$ret .= "\t</data>\n";

		$ret .= "</e107Export>";
		return $ret;
	}

	/**
	 * Try to convert string to a number
	 * Shoud fix locale related troubles
	 *
	 * @param string $value
	 * @return integer|float
	 */
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

	/**
	 * Convert object data to a string
	 *
	 * @param boolean $AddSlashes
	 * @param string $key optional, if set method will return corresponding value as a string
	 * @return string
	 */
	public function toString($AddSlashes = true, $key = null)
	{
		if (null !== $key)
		{
			$value = $this->getData($key);
			if(is_array($value))
			{
				return e107::getArrayStorage()->WriteArray($value, $AddSlashes);
			}
			return (string) $value;
		}
		return (string) e107::getArrayStorage()->WriteArray($this->toArray(), $AddSlashes);
	}

	public function destroy()
	{
		$this->_data = array();
		$this->_params = array();
		$this->_data_fields = array();
		$this->_parsed_keys = array();
		$this->_db_table = $this->_field_id = '';
		$this->data_has_changed = false;
	}

	/**
	 * Disable Magic setter
	 */
	public function __set($key, $value)
	{
	}

	/**
	 * Disable Magic getter
	 */
	public function __get($key)
	{
	}

	/**
	 * Disable
	 */
	public function __isset($key)
	{
	}

	/**
	 * Disable
	 */
	public function __unset($key)
	{
	}
}

/**
 * Base e107 Front Model class interface
 *
 * Some important points:
 * - model data should be always in toDB() format:
 * 		- retrieved direct from DB
 * 		- set & sanitized via setPostedData()->mergePostedData()
 * 		- manually sanitized before passed to model setter (set(), setData(), add(), addData(), etc.) methods
 * - $_data_fields property is important, it tells to sanitize() method how to sanitize posted data
 * - if $_data_fields is missing, sanitize() will call internally e107::getParser()->toDB() on the data
 * - sanitize() is triggered by default on mergePostedData() and mergeData() methods
 * - mergePostedData() and mergeData() methods will filter posted/passed data against (in this order):
 * 		- getValidator()->getValidData() if true is passed as validate parameter (currently disabled, gather feedback)
 * 		- $_data_fields if true is passed as sanitize parameter
 * - toSqlQuery() needs $_data_fields and $_field_id to work proper, $_FIELD_TYPES is optional but recommended (faster SQL queries)
 * - result array from toSqlQuery() call will be filtered against $_data_fields
 * - in almost every case $_FIELD_TYPES shouldn't contain 'escape' and 'todb' - dont't forget you are going to pass already sanitized data (see above)
 * - most probably $_FIELD_TYPES will go in the future, $_data_fields alone could do the job
 * - default db related methods (save(), dbUpdate(), etc.) need $_db_table
 *
 * @package e107
 * @category e107_handlers
 * @version $Id$
 * @author SecretR
 * @copyright Copyright (C) 2008-2010 e107 Inc.
 */
class e_front_model extends e_model
{
    /**
    * Posted data
    * Back-end related
    *
    * @var array
    */
    protected $_posted_data = array();


    /**
     * DB format array - see db::_getTypes() and db::_getFieldValue() (mysql_class.php)
     * for example
     *
     * This can/should be overwritten by extending the class
     *
     * @var array
     */
    protected $_FIELD_TYPES = array();

    /**
     * Validation structure - see {@link e_validator::$_required_rules} for
     * more information about the array format.
     * Used in {@link validate()} method.
     * TODO - check_rules (see e_validator::$_optional_rules)
     * This can/should be overwritten by extending the class.
     *
     * @var array
     */
    protected $_validation_rules = array();

    protected $_optional_rules = array();

	/**
	 * @var integer Last SQL error number
	 */
	protected $_db_errno = 0;

	/**
	 * @var string Last SQL error message
	 */
	protected $_db_errmsg = '';

	/**
	 * @var string Last SQL query
	 */
	protected $_db_qry = '';

    /**
     * Validator object
     *
     * @var e_validator
     */
    protected $_validator = null;

    /**
     * @return array
     */
    public function getValidationRules()
    {
    	return $this->_validation_rules;
    }

    /**
     * Set object validation rules if $_validation_rules array is empty
     *
     * @param array $vrules
     * @return e_front_model
     */
    public function setValidationRules(array $vrules, $force = false)
    {
    	if($force || empty($this->_validation_rules))
    	{
    		$this->_validation_rules = $vrules;
    	}
    	return $this;
    }

    /**
     * @return array
     */
    public function getOptionalRules()
    {
    	return $this->_optional_rules;
    }

    /**
     * @param array $rules
     * @return e_front_model
     */
    public function setOptionalRules(array $rules)
    {
    	$this->_optional_rules = $rules;
    	return $this;
    }

    /**
     * Set object validation rules if $_validation_rules array is empty
     *
     * @param string $field
     * @param array $rule
     * @param boolean $required
     * @return e_front_model
     */
    public function setValidationRule($field, $rule, $required = true)
    {
    	$pname = $required ? '_validation_rules' : '_optional_rules';
    	$rules = &$this->$pname;
    	$rules[$field] = $rule;

    	return $this;
    }

    /**
     * Predefined data fields types, passed to DB handler
     * @return array
     */
    public function getDbTypes()
    {
    	return ($this->_FIELD_TYPES ? $this->_FIELD_TYPES : $this->getDataFields());
    }

    /**
     * Predefined data fields types, passed to DB handler
     *
     * @param array $field_types
     * @return e_front_model
     */
    public function setDbTypes($field_types)
    {
    	$this->_FIELD_TYPES = $field_types;
		return $this;
    }

    /**
     * Auto field type definitions
     * Disabled for now, it should auto-create _data_types
     * @param boolean $force
     * @return boolean
     */
//	public function setFieldTypeDefs($force = false)
//	{
//		if($force || !$this->getFieldTypes())
//		{
//			$ret = e107::getDb()->getFieldDefs($this->getModelTable());
//			if($ret)
//			{
//				foreach ($ret as $k => $v)
//				{
//					if('todb' == $v)
//					{
//						$ret[$k] = 'string';
//					}
//				}
//				$this->setFieldTypes($ret);
//				return true;
//			}
//		}
//		return false;
//	}

    /**
     * Retrieves data from the object ($_posted_data) without
     * key parsing (performance wise, prefered when possible)
     *
     * @see _getDataSimple()
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
	public function getPosted($key, $default = null)
    {
    	return $this->_getDataSimple((string) $key, $default, '_posted_data');
    }

    /**
     * Retrieves data from the object ($_posted_data)
     * If $key is empty, return all object posted data
     * @see _getData()
     * @param string $key
     * @param mixed $default
     * @param integer $index
     * @return mixed
     */
	public function getPostedData($key = '', $default = null, $index = null)
    {
    	return $this->_getData($key, $default, $index, '_posted_data');
    }

    /**
     * Search for requested data from available sources in this order:
     * - posted data
     * - default object data
     * - passed default value
     *
     * Use this method inside forms
     *
     * @param string $key
     * @param string $default
     * @param integer $index
     * @return string
     */
    public function getIfPosted($key, $default = '', $index = null)
    {
    	$d = $this->getDataFields();
		
		if(!empty($d[$key]) && ($d[$key] == 'array'))
		{
			return e107::unserialize($this->getData((string) $key, $default, $index));	
		}   
		
    	$posted = $this->getPostedData((string) $key, null, $index);
		if(null !== $posted)
		{
			// FIXED - double post_toFom() and toDB(post_toForm()) problems
			// setPosted|setPostedData|addPostedData methods are storing RAW data now
			return e107::getParser()->post_toForm($posted);
		}
		return e107::getParser()->toForm($this->getData((string) $key, $default, $index));
    }

    /**
     * Overwrite posted data in the object for a single field. Key is not parsed.
     * Public proxy of {@link _setDataSimple()}
     * Use this method to store data from non-trustable sources (e.g. _POST) - it doesn't overwrite
     * the original object data
     *
     * @param string $key
     * @param mixed $value
     * @param boolean $strict update only
     * @return e_front_model
     */
    public function setPosted($key, $value, $strict = false)
    {
        return $this->_setDataSimple($key, $value, $strict, '_posted_data');
    }

    /**
     * Overwrite posted data in the object. Key is parsed (multidmensional array support).
     * Public proxy of {@link _setData()}
     * Use this method to store data from non-trustable sources (e.g. _POST) - it doesn't overwrite
     * the original object data
     *
     * @param string|array $key
     * @param mixed $value
     * @param boolean $strict update only
     * @return e_front_model
     */
    public function setPostedData($key, $value = null, $strict = false)
    {
        return $this->_setData($key, $value, $strict, '_posted_data');
    }

    /**
     * Add data to the object.
     * Retains existing data in the object.
     * Public proxy of {@link _addData()}
     *
     * If $override is false, data will be updated only (check against existing data)
     *
     * @param string|array $key
     * @param mixed $value
     * @param boolean $override override existing data
     * @return e_front_model
     */
    public function addPostedData($key, $value = null, $override = true)
    {
    	return $this->_addData($key, $value, $override, '_posted_data');
    }

	/**
     * Unset single posted data field from the object.
     * Public proxy of {@link _unsetDataSimple()}
     *
     * @param string $key
     * @return e_front_model
     */
    public function removePosted($key)
    {
    	return $this->_unsetDataSimple($key, '_posted_data');
    }

	/**
     * Unset posted data from the object.
     * $key can be a string only. Array will be ignored.
     * '/' inside the key will be treated as array path
     * if $key is null entire object will be reset
     *
     * Public proxy of {@link _unsetData()}
     *
     * @param string|null $key
     * @return e_front_model
     */
    public function removePostedData($key = null)
    {
    	return $this->_unsetData($key, '_posted_data');
    }

    /**
     * Check if given key exists and non-empty in the posted data array
     * @param string $key
     * @return boolean
     */
    public function hasPosted($key)
    {
    	return $this->_hasData($key, '_posted_data');
    }

	/**
	 * Check if posted data is empty
	 * @return boolean
	 */
    public function hasPostedData()
    {
    	return $this->_hasData('', '_posted_data');
    }

    /**
     * Check if given key exists in the posted data array
     *
     * @param string $key
     * @return boolean
     */
    public function isPosted($key)
    {
    	return (isset($this->_posted_data[$key]));
    }

    /**
     * Check if given key exists in the posted data array ($key us parsed)
     *
     * @param string $key
     * @return boolean
     */
    public function isPostedData($key)
    {
    	return $this->_isData($key, '_posted_data');
    }

    /**
     * Compares posted data vs object data
     *
     * @param string $field
     * @param boolean $strict compare variable type as well
     * @return boolean
     */
    public function dataHasChangedFor($field, $strict = false)
    {
        $newData = $this->getData($field);
        $postedData = $this->getPostedData($field);
        return ($strict ? $newData !== $postedData : $newData != $postedData);
    }

    /**
     * @return boolean
     */
    public function dataHasChanged()
    {
        return $this->data_has_changed;
    }

    /**
     * Merge posted data with the object data
     * Should be used on edit/update/create record (back-end)
     * Retrieved for copy Posted data will be removed (no matter if copy is successfull or not)
     *
     * If $strict is true, only existing object data will be copied (update)
     * If $validate is true, data will be copied only after successful validation
     *
     * @param boolean $strict
     * @param boolean $sanitize sanitize posted data before move it to the object data
     * @param boolean $validate perform validation check
     * @return e_front_model
     */
    public function mergePostedData($strict = true, $sanitize = true, $validate = true)
    {
    	if(!$this->hasPostedData() || ($validate && !$this->validate()))
    	{
    		return $this;
    	}


		$oldData = $this->getData();
//		$this->addMessageDebug("OLDD".print_a($oldData,true));


		$data = $this->getPostedData();

		$valid_data = $validate ? $this->getValidator()->getValidData() : array();

		if($sanitize)
		{
			// search for db_field types
			if($this->getDataFields())
			{
				$data = $this->sanitize($data);
			}
			else //no db field types, use toDB()
			{
				$data = e107::getParser()->toDB($data);
			}
		}

	//	$newData = $this->getPostedData();
		e107::getAdminLog()->addArray($data,$oldData);
	//	$this->addMessageDebug("NEWD".print_a($data,true));

		$tp = e107::getParser();
    	foreach ($data as $field => $dt)
    	{
    		// get values form validated array when possible
    		// we need it because of advanced validation methods e.g. 'compare'
    		// FIX - security issue, toDb required
    		if(isset($valid_data[$field])) $dt = $tp->toDb($valid_data[$field]);

    		$this->setData($field, $dt, $strict)
    			->removePostedData($field);
    	}




    	return $this;
    }

    /**
     * Merge passed data array with the object data
     * Should be used on edit/update/create record (back-end)
     *
     * If $strict is true, only existing object data will be copied (update)
     * If $validate is true, data will be copied only after successful validation
     *
     * @param array $src_data
     * @param boolean $sanitize
     * @param boolean $validate perform validation check
     * @return e_front_model
     */
    public function mergeData(array $src_data, $strict = true, $sanitize = true, $validate = true)
    {
    	//FIXME
    	if(!$src_data || ($validate && !$this->validate($src_data)))
    	{
    		return $this;
    	}

		/* Wrong?
		// retrieve only valid data
		if($validate)
		{
			$src_data = $this->getValidator()->getValidData();
		}*/

		if($sanitize)
		{
			// search for db_field types
			if($this->getDataFields())
			{
				$src_data = $this->sanitize($src_data);
			}
			else //no db field types, use toDB()
			{
				$src_data = e107::getParser()->toDB($src_data);
			}
		}



		foreach ($src_data as $key => $value)
		{
			$this->setData($key, $value, $strict);
		}



    	return $this;
    }

    /**
     * Validate posted data:
     * 1. validate posted data against object validation rules
     * 2. add validation errors to the object if any
     * 3. return true for valid and false for non-valid data
     *
     * @param array $data optional - data for validation, defaults to posted data
     * @return boolean
     */
    public function validate($data = null)
    {
    	if(!$this->getValidationRules())
    	{
    		return true;
    	}
		if(null === $data)
		{
			$data = $this->getPostedData();
		}

		// New param to control validate process - useful when part of the data is going to be updated
		// Use it with cautious!!!
		$availableOnly = false;
		if($this->getParam('validateAvailable'))
		{
			$availableOnly = true;
			$this->setParam('validateAvailable', null); // reset it
		}

		return $this->getValidator()->validate($data, $availableOnly);
    }

    /**
     * User defined model validation
     * Awaiting for child class implementation
     *
     */
    public function verify()
    {
    }

	/**
	 * @return e_validator
	 */
	public function getValidator()
	{
		if(null === $this->_validator)
		{
			$this->_validator = e107::getObject('e_validator');
			$this->_validator->setRules($this->getValidationRules())
				->setOptionalRules($this->getOptionalRules())
				->setMessageStack($this->_message_stack.'_validator');
			//TODO - optional check rules
		}
		return $this->_validator;
	}

	/**
	 * Add custom validation message.
	 * $field_type and $error_code will be inserted via $tp->lanVars()
	 * in the $message string
	 * Example:
	 * <code>
	 * $model->addValidationError('Custom error message [#%d] for %s', 'My Field', 1000);
	 * //produces 'Custom error message [#1000] for My Field'
	 * </code>
	 *
	 * @param string $message
	 * @param string $field_title [optional]
	 * @param integer $error_code [optional]
	 * @return object
	 */
	public function addValidationError($message, $field_title = '', $error_code = 0)
	{
		$this->getValidator()->addValidateMessage($field_title, $error_code, $message)->setIsValidData(false);
		return $this;
	}

    /**
     * Render validation errors (if any)
     *
     * @param boolean $session store messages to session
     * @param boolean $reset reset errors
     * @return string
     */
    public function renderValidationErrors($session = false, $reset = true)
    {
		return $this->getValidator()->renderValidateMessages($session, $reset);
    }

    /**
     * Render System messages (if any)
     *
     * @param boolean $validation render validation messages as well
     * @param boolean $session store messages to session
     * @param boolean $reset reset errors
     * @return string
     */
    public function renderMessages($validation = true, $session = false, $reset = true)
    {
    	if($validation)
		{
			e107::getMessage()->moveStack($this->_message_stack.'_validator', $this->_message_stack, false, $session);
		}
		return parent::renderMessages($session, $reset);
    }

    /**
     * Move model System messages (if any) to the default eMessage stack
     *
     * @param boolean $session store messages to session
     * @param boolean $validation move validation messages as well
     * @return e_front_model
     */
    public function setMessages($session = false, $validation = true)
    {
    	if($validation)
		{
			e107::getMessage()->moveStack($this->_message_stack.'_validator', 'default', false, $session);
		}
    	parent::setMessages($session);
		return $this;
    }

    /**
     * Reset model System messages
     *
     * @param boolean|string $type E_MESSAGE_INFO | E_MESSAGE_SUCCESS | E_MESSAGE_WARNING | E_MESSAGE_WARNING | E_MESSAGE_DEBUG | false (all)
     * @param boolean $session reset session messages
     * @param boolean $validation reset validation messages as well
     * @return e_front_model
     */
    public function resetMessages($type = false, $session = false, $validation = false)
    {
        if($validation)
		{
			e107::getMessage()->reset($type, $this->_message_stack.'_validator', $session);
		}
    	parent::resetMessages($type, $session);
		return $this;
    }

    /**
     * @return boolean
     */
    public function hasValidationError()
    {
    	return $this->getValidator()->isValid();
    }

    /**
     * @return boolean
     */
    public function hasSqlError()
    {
    	return !empty($this->_db_errno);
    }

    /**
     * @return integer last mysql error number
     */
    public function getSqlErrorNumber()
    {
    	return $this->_db_errno;
    }

    /**
     * @return string last mysql error message
     */
    public function getSqlError()
    {
    	return $this->_db_errmsg;
    }

    /**
     * @return string last mysql error message
     */
    public function getSqlQuery()
    {
    	return $this->_db_qry;
    }

    /**
     * @return boolean
     */
    public function hasError()
    {
    	return ($this->hasValidationError() || $this->hasSqlError());
    }

    /**
     * Generic load data from DB
     * @param boolean $force
     * @return e_front_model
     */
	public function load($id=null, $force = false)
	{
		parent::load($id, $force);

		$sql = e107::getDb();
		$this->_db_errno = $sql->getLastErrorNumber();
		$this->_db_errmsg = $sql->getLastErrorText();
		$this->_db_qry = $sql->getLastQuery();

		if($this->_db_errno)
		{
			$data = array(
				'TABLE'     => $this->getModelTable(),
				'error_no'  => $this->_db_errno,
				'error_msg' => $this->_db_errmsg,
				'qry'       => $this->_db_qry,
				'url'       => e_REQUEST_URI,
			);


			$this->addMessageError('SQL Select Error', false, $data); //TODO - Lan
			// already done by the parent
			//$this->addMessageDebug('SQL Error #'.$this->_db_errno.': '.$sql->getLastErrorText());
		}


		return $this;
	}


	/**
	 * Build query array to be used with db methods (db_Update, db_Insert, db_Replace)
	 *
	 * @param string $force [optional] force action - possible values are create|update|replace
	 * @return array db query
	 */
	public function toSqlQuery($force = '')
	{
		$qry = array();

		if($force)
		{
			$action = $force;
		}
		else
		{
			$action = $this->getId() ? 'update' : 'create';
		}

		$qry['_FIELD_TYPES'] = $this->_FIELD_TYPES; //DB field types are optional

		// support for tables with no auto-increment PK
		$id = $this->getId();
		$qry['data'][$this->getFieldIdName()] = $id;

		//XXX This check is done in _setModel() of admin-ui.  NULL below will break MySQL strict.
		// Allow admin config to specify the best data type. 
		/*
		if($action == 'create' && !$id) $qry['_FIELD_TYPES'][$this->getFieldIdName()] = 'NULL';
		elseif(is_numeric($id)) $qry['_FIELD_TYPES'][$this->getFieldIdName()] = 'integer';
		else $qry['_FIELD_TYPES'][$this->getFieldIdName()] = 'string';
		*/

		foreach ($this->_data_fields as $key => $type)
		{

			if(!isset($qry['_FIELD_TYPES'][$key]))
			{
				$qry['_FIELD_TYPES'][$key] = $type; //_FIELD_TYPES much more optional now...
			}

			if($qry['_FIELD_TYPES'][$key] == 'set') //new 'set' type, could be moved in mysql handler now
			{
				$qry['_FIELD_TYPES'][$key] = 'str';
				if(is_array($this->getData($key)))	$this->setData($key, implode(',', $this->getData($key)));
			}
			$qry['data'][$key] = $this->getData($key);

		}

		switch($action)
		{
			case 'create':
				//$qry['data'][$this->getFieldIdName()] = NULL;
			break;
			case 'replace':
				$qry['_REPLACE'] = true;
			break;

			case 'update':
				unset($qry['data'][$this->getFieldIdName()]);
				if(is_numeric($id)) $id = intval($id);
				else $id = "'".e107::getParser()->toDB($id)."'";
				$qry['WHERE'] = $this->getFieldIdName().'='.$id;
			break;
		}

		if(E107_DEBUG_LEVEL == E107_DBG_SQLQUERIES)
		{
			$this->addMessageDebug('SQL Qry: '.print_a($qry,true), null);
		}
		return $qry;
	}

	/**
	 * Sanitize value based on its db field type ($_data_fields),
	 * method will return null only if db field rule is not found.
	 * If $value is null, it'll be retrieved from object posted data
	 * If $key is an array, $value is omitted.
	 *
	 * NOTE: If $key is not found in object's _data_fields array, null is returned
	 *
	 * @param mixed $key string key name or array data to be sanitized
	 * @param mixed $value
	 * @return mixed sanitized $value or null on failure
	 */
	public function sanitize($key, $value = null)
	{
		$tp = e107::getParser();
		if(is_array($key))
		{
			$ret = array();
			foreach ($key as $k=>$v)
			{
	            if(isset($this->_data_fields[$k]))
	            {
	               $ret[$k] = $this->sanitize($k, $v);
	            }
			}
			return $ret;
		}

		if(!isset($this->_data_fields[$key]))
		{
			return null;
		}
		$type =  $this->_data_fields[$key];
		if(null === $value)
		{
			$value = $this->getPostedData($key);
		}


		switch ($type)
		{
			case 'int':
			case 'integer':
				//return intval($this->toNumber($value));
				return intval($tp->toNumber($value));
			break;

			case 'safestr':
				return $tp->filter($value);
			break;

			case 'str':
			case 'string':
			case 'array':
				$type = $this->getFieldInputType($key);
				return $tp->toDB($value, false, false, 'model', array('type'=>$type, 'field'=>$key));
			break;

			case 'json':
				if(empty($value))
				{
					return null;
				}
				return e107::serialize($value,'json');
			break;

			case 'code':
				return $tp->toDB($value, false, false, 'pReFs');
			break;

			case 'float':
				// return $this->toNumber($value);
				return $tp->toNumber($value);
			break;

			case 'bool':
			case 'boolean':
				return ($value ? true : false);
			break;

			case 'model':
				return $value->mergePostedData(false, true, true);
			break;

			case 'null':
				return ($value ? $tp->toDB($value) : null);
			break;
	  	}

		return null;
	}



	public function destroy()
	{
		parent::destroy();
		$this->_validator = null;
		$this->_validation_rules = array();
		$this->_db_errno = null;
		$this->_posted_data = array();
		$this->data_has_changed = array();
		$this->_FIELD_TYPES = array();
	}


    /**
     * Update DB data
     *
     * @param boolean $force force query even if $data_has_changed is false
     * @param boolean $session_messages to use or not session to store system messages
     */
    protected function dbUpdate($force = false, $session_messages = false)
    {
        $this->_db_errno = 0;
		$this->_db_errmsg = '';
		$this->_db_qry = '';

	//	 $this->getData();
	//	 $this->getPostedData();


		if($this->hasError()) return false;

		if(!$this->data_has_changed && $force === false)
		{
			$this->addMessageInfo(LAN_NO_CHANGE);
			return 0;
		}

		$sql = e107::getDb();
		$qry = $this->toSqlQuery('update');
		$table = $this->getModelTable();

		$res = $sql->update($table, $qry, $this->getParam('db_debug', false));
        $this->_db_qry = $sql->getLastQuery();
		if(!$res)
		{
			$this->_db_errno = $sql->getLastErrorNumber();
			$this->_db_errmsg = $sql->getLastErrorText();

			if($this->_db_errno)
			{
				$data = array(
					'TABLE'     => $table,
					'error_no' => $this->_db_errno,
					'error_msg' => $this->_db_errmsg,
					'qry'       => $this->_db_qry,
					'url'       => e_REQUEST_URI,
				);

				$this->addMessageError('SQL Update Error', $session_messages, $data); //TODO - Lan
				$this->addMessageDebug('SQL Error #'.$this->_db_errno.': '.$sql->getLastErrorText());
				return false;
			}

			if($force === false)
			{
				$this->addMessageInfo(LAN_NO_CHANGE);
			}
			else
			{
				$this->addMessageDebug(LAN_NO_CHANGE);
			}


			return 0;
		}
		$this->clearCache()->addMessageSuccess(LAN_UPDATED);

		e107::getAdminLog()->addSuccess('TABLE: '.$table, false);
		e107::getAdminLog()->addSuccess('WHERE: '.$qry['WHERE'], false);
		e107::getAdminLog()->save('ADMINUI_02');


		return $res;
    }

    /**
     * Save data to DB
     *
     * @param boolen $from_post
     * @return boolean|integer
     */
    public function save($from_post = true, $force = false, $session_messages = false)
    {
    	if(!$this->getFieldIdName())
		{
			return false;
		}

		if($from_post)
		{
			//no strict copy, validate & sanitize
			$this->mergePostedData(false, true, true);
		}

		if($this->getId())
		{
			return $this->dbUpdate($force, $session_messages);
		}

		return false;
    }

    /**
     * Update record
     * @see save()
     * @param boolen $from_post
     * @return boolean|integer
     *//*
    public function update($from_post = true, $force = false, $session_messages = false)
    {
    	if(!$this->getFieldIdName())
		{
			return false;
		}

		if($from_post)
		{
			//no strict copy, validate & sanitize
			$this->mergePostedData(false, true, true);
		}

		return $this->dbUpdate($force, $session_messages);
    }*/

    /**
     * Exactly what it says - your debug helper
     * @param boolean $retrun
     * @param boolean $undo
     * @return void
     */
	public function saveDebug($return = false, $undo = true)
	{
		$ret = array();

		$ret['validation_rules'] = $this->getValidationRules();
		$ret['optional_validation_rules'] = $this->getOptionalRules();
		$ret['model_base_ismodfied'] = $this->isModified();
		$ret['model_base_data'] = $this->getData();
		$ret['posted_data'] = $this->getPostedData();

		$this->mergePostedData(false, true, true);

		$ret['model_modified_data'] = $this->getData();
		$ret['model_modified_ismodfied'] = $this->isModified();
		$ret['validator_valid_data'] = $this->getValidator()->getValidData();

		// undo
		if($undo)
		{
			$this->setData($ret['model_base_data'])
				->isModified($ret['model_base_ismodfied'])
				->setPostedData($ret['posted_data']);
		}
		if($return) return $ret;

		print_a($ret);
	}
}

//FIXME - move e_model_admin to e_model_admin.php

/**
 * Base e107 Admin Model class
 *
 * @package e107
 * @category e107_handlers
 * @version $Id$
 * @author SecretR
 * @copyright Copyright (C) 2008-2010 e107 Inc.
 */
class e_admin_model extends e_front_model
{
    /**
     * Save data to DB
     *
     * @param boolen $from_post
     */
    public function save($from_post = true, $force = false, $session_messages = false)
    {
    	if(!$this->getFieldIdName())
		{
			return false;
		}

		if($from_post)
		{
			//no strict copy, validate & sanitize
			$this->mergePostedData(false, true, true);
		}

		if($this->getId() && $this->getPostedData('etrigger_submit') !='create') // Additional Check to allow primary ID to be manually set when auto-increment PID is not used. @see userclass2.php
		{
			return $this->dbUpdate($force, $session_messages);
		}

		return $this->dbInsert($session_messages);
    }

    /**
     * Insert record
     *
     * @param boolen $from_post
	 * @param boolean $session_messages
	 * @return integer inserted ID or false on error
     */
    public function insert($from_post = true, $session_messages = false)
    {
		if($from_post)
		{
			//no strict copy, validate & sanitize
			$this->mergePostedData(false, true, true);
		}

		return $this->dbInsert($session_messages);
    }

	public function delete($ids, $destroy = true, $session_messages = false)
	{
		$ret = $this->dbDelete();
		if($ret)
		{
			if($destroy)
			{
				$this->setMessages($session_messages)->destroy();
			}
		}
		return $ret;
	}

    /**
     * Insert data to DB
     * @param boolean $session_messages to use or not session to store system messages
     * @return integer
     */
    protected function dbInsert($session_messages = false)
    {
        $this->_db_errno = 0;
        $this->_db_errmsg = '';
		$this->_db_qry = '';
		if($this->hasError()/* || (!$this->data_has_changed && !$force)*/) // not appropriate here!
		{
			return false;
		}
		$sql = e107::getDb();
		$sqlQry = $this->toSqlQuery('create');
		$table = $this->getModelTable();

		$res = $sql->insert($table, $sqlQry, $this->getParam('db_debug', false));
        $this->_db_qry = $sql->getLastQuery();
		if(!$res)
		{
			$this->_db_errno = $sql->getLastErrorNumber();
			$this->_db_errmsg = $sql->getLastErrorText();

			$logData = ($table != 'admin_log') ? array('TABLE'=>$table, 'ERROR'=>$this->_db_errmsg, 'QRY'=>print_r($sqlQry,true)) : false;

			$this->addMessageError('SQL Insert Error', $session_messages, $logData); //TODO - Lan
			$this->addMessageDebug('SQL Error #'.$this->_db_errno.': '.$this->_db_errmsg);
			$this->addMessageDebug('SQL QRY Error '.print_a($sqlQry,true));

			return false;
		}

	    e107::getAdminLog()->addSuccess('TABLE: '.$table, false);
		e107::getAdminLog()->save('ADMINUI_01');
	//	e107::getAdminLog()->clear()->addSuccess($table,false)->addArray($sqlQry)->save('ADMINUI_01');

		// Set the reutrned ID
		$this->setId($res);
		$this->clearCache()->addMessageSuccess(LAN_CREATED);

		return $res;
    }

    /**
     * Replace data in DB
     *
     * @param boolean $force force query even if $data_has_changed is false
     * @param boolean $session_messages to use or not session to store system messages
     */
    protected function dbReplace($force = false, $session_messages = false)
    {
    	$this->_db_errno = 0;
    	$this->_db_errmsg = '';
		$this->_db_qry = '';

		if($this->hasError()) return false;
		if(!$this->data_has_changed && !$force)
		{
			return 0;
		}
		$sql = e107::getDb();
		$table = $this->getModelTable();
		$res = $sql->db_Insert($table, $this->toSqlQuery('replace'));
        $this->_db_qry = $sql->getLastQuery();
		if(!$res)
		{
			$this->_db_errno = $sql->getLastErrorNumber();
			$this->_db_errmsg = $sql->getLastErrorText();

			if($this->_db_errno)
			{
				$logData = ($table != 'admin_log') ? array('TABLE'=>$table, 'ERROR'=>$this->_db_errmsg, 'QRY'=> print_r($this->_db_qry,true)) : false;

				$this->addMessageError('SQL Replace Error', $session_messages, $logData); //TODO - Lan
				$this->addMessageDebug('SQL Error #'.$this->_db_errno.': '.$sql->getLastErrorText());
			}
		}
		else
		{
			$this->clearCache();
		}
		return $res;
    }

    /**
     * Delete DB data
     *
     * @param boolean $force force query even if $data_has_changed is false
     * @param boolean $session_messages to use or not session to store system messages
     */
    protected function dbDelete($session_messages = false)
    {
    	$this->_db_errno = 0;
		$this->_db_errmsg = '';
		$this->_db_qry = '';

		if($this->hasError())
		{
			return false;
		}

		if(!$this->getId())
		{
			$this->addMessageError('Record not found', $session_messages); //TODO - Lan
			return 0;
		}
		$sql = e107::getDb();
		$id = $this->getId();
		if(is_numeric($id)) $id = intval($id);
		else  $id = "'".e107::getParser()->toDB($id)."'";
		$table  = $this->getModelTable();
		$where = $this->getFieldIdName().'='.$id;
		$res = $sql->delete($table, $where);
        $this->_db_qry = $sql->getLastQuery();

		if(!$res)
		{
			$this->_db_errno = $sql->getLastErrorNumber();
			$this->_db_errmsg = $sql->getLastErrorText();

			if($this->_db_errno)
			{
				$logData = ($table != 'admin_log') ? array('TABLE'=>$table, 'ERROR'=>$this->_db_errmsg, 'WHERE'=>$where) : false;

				$this->addMessageError('SQL Delete Error', $session_messages, $logData); //TODO - Lan
				$this->addMessageDebug('SQL Error #'.$this->_db_errno.': '.$sql->getLastErrorText());
			}
		}
    	else
		{
			if($table != 'admin_log')
			{
				$logData = array('TABLE'=>$table, 'WHERE'=>$where);
				e107::getAdminLog()->addSuccess($table,false);
				e107::getAdminLog()->addArray($logData)->save('ADMINUI_03');
			}

			$this->clearCache();
		}
		return $res;
    }
}

/**
 * Model collection handler
 */
class e_tree_model extends e_front_model
{
	/**
	 * Current model DB table, used in all db calls
	 * This can/should be overwritten by extending the class
	 *
	 * @var string
	 */
	protected $_db_table;

	/**
	 * All records (no limit) cache
	 *
	 * @var string
	 */
	protected $_total = false;

	/**
	 * Constructor
	 *
	 */
	function __construct($tree_data = array())
	{
		if($tree_data)
		{
			$this->setTree($tree_data);
		}
	}

	public function getTotal()
	{
		return $this->_total;
	}

	public function setTotal($num)
	{
		$this->_total = $num;
		return $this;
	}

	/**
	 * Set table name
	 * @param string $table
	 * @return e_tree_model
	 */
	public function setModelTable($table)
	{
		$this->_db_table = $table;
		return $this;
	}

	/**
	 * Get table name
	 * @return string
	 */
	public function getModelTable()
	{
		return $this->_db_table;
	}

	/**
	 * Get array of models
	 * @return array
	 */
	function getTree()
	{
		return $this->get('__tree', array());
	}

	/**
	 * Set array of models
	 * @return e_tree_model
	 */
	function setTree($tree_data, $force = false)
	{
		if($force || !$this->isTree())
		{
			$this->set('__tree', $tree_data);
		}

		return $this;
	}

	/**
	 * Unset all current data
	 * @return e_tree_model
	 */
	function unsetTree()
	{
		$this->remove('__tree');
		return $this;
	}

	public function isCacheEnabled($checkId = true)
	{
		return (null !== $this->getCacheString());
	}

	public function getCacheString($replace = false)
	{
		return $this->_cache_string;
	}

	public function setCacheString($str = null)
	{
		if(isset($str))
			return parent::setCacheString($str);

		if($this->isCacheEnabled() && !$this->getParam('noCacheStringModify'))
		{
			$str = !$this->getParam('db_query')
				?
					$this->getModelTable()
					.$this->getParam('nocount')
					.$this->getParam('db_where')
					.$this->getParam('db_order')
					.$this->getParam('db_limit')
				:
					$this->getParam('db_query');

			return $this->setCacheString($this->getCacheString().'_'.md5($str));
		}

		return parent::setCacheString($str);
	}

	protected function _setCacheData()
	{
		if(!$this->isCacheEnabled())
		{
			return $this;
		}

		e107::getCache()->set_sys(
			$this->getCacheString(true),
			$this->toString(false, null, $this->getParam('nocount') ? false : true),
			$this->_cache_force,
			false
		);
		return $this;
	}

	protected function _loadFromArray($array)
	{
		if(isset($array['total']))
		{
			$this->setTotal((integer) $array['total']);
			unset($array['total']);
		}
		$class_name = $this->getParam('model_class', 'e_model');
		$tree = array();
		foreach ($array as $id => $data)
		{
			$tree[$id] = new $class_name($data);
			$this->_onLoad($tree[$id]);
		}

		$this->setTree($tree, true);
	}

	/**
	 * Additional on load logic to be set from subclasses
	 *
	 * @param e_model $node
	 * @return e_tree_model
	 */
	protected function _onLoad($node)
	{
		return $this;
	}

	/**
	 * Default load method
	 *
	 * @return e_tree_model
	 */
	public function loadBatch($force = false)
	{
		if ($force)
		{
			$this->unsetTree()
				->_clearCacheData();

			$this->_total = false;
		}

		// XXX What would break if changed to the most proper isTree()?
		elseif($this->isTree()) //!$this->isEmpty()
		{
			return $this;
		}

		$this->setCacheString();
		$cached = $this->_getCacheData();
		if($cached !== false)
		{
			$this->_loadFromArray($cached);
			return $this;
		}

		// auto-load all
		if(!$this->getParam('db_query') && $this->getModelTable())
		{
			$this->setParam('db_query', 'SELECT'.(!$this->getParam('nocount') ? ' SQL_CALC_FOUND_ROWS' : '')
				.($this->getParam('db_cols') ? ' '.$this->getParam('db_cols') : ' *').' FROM #'.$this->getModelTable()
				.($this->getParam('db_joins') ? ' '.$this->getParam('db_joins') : '')
				.($this->getParam('db_where') ? ' WHERE '.$this->getParam('db_where') : '')
				.($this->getParam('db_order') ? ' ORDER BY '.$this->getParam('db_order') : '')
				.($this->getParam('db_limit') ? ' LIMIT '.$this->getParam('db_limit') : '')
			);
		}

		$class_name = $this->getParam('model_class', 'e_model');
		if($this->getParam('db_query') && $class_name && class_exists($class_name))
		{
			$sql = e107::getDb($this->getParam('model_class', 'e_model'));
			$this->_total = $sql->total_results = false;

			if($rows = $this->getRows($sql))
			{
				foreach($rows as $tmp)
				{
					$tmp = new $class_name($tmp);
					if($this->getParam('model_message_stack'))
					{
						$tmp->setMessageStackName($this->getParam('model_message_stack'));
					}
					$this->_onLoad($tmp)->setNode($tmp->get($this->getFieldIdName()), $tmp);
				}
				unset($tmp);

				$this->countResults($sql);
			}

			if($sql->getLastErrorNumber())
			{

				$data = array(
					'TABLE'     => $this->getModelTable(),
					'error_no' => $sql->getLastErrorNumber(),
					'error_msg' => $sql->getLastErrorText(),
					'qry'       => $sql->getLastQuery(),
					'url'       => e_REQUEST_URI,
				);


				$this->addMessageError('Application Error - DB query failed.', false, $data) // TODO LAN
					->addMessageDebug('SQL Error #'.$sql->getLastErrorNumber().': '.$sql->getLastErrorText())
					->addMessageDebug($sql->getLastQuery());
			}
			else
			{
				$this->_setCacheData();
			}

		}
		return $this;
	}

	protected function getRows($sql)
	{
		// Tree (Parent-Child Relationship)
		if ($this->getParam('sort_parent') && $this->getParam('sort_field'))
		{
			return $this->getRowsTree($sql);
		}
		// Flat List
		return $this->getRowsList($sql);
	}

	protected function getRowsList($sql)
	{
		$success = $sql->gen($this->getParam('db_query'), $this->getParam('db_debug') ? true : false);
		if (!$success) return false;

		return $sql->rows();
	}

	protected function getRowsTree($sql)
	{
		// Workaround: Parse and modify db_query param for simulated pagination
		$this->prepareSimulatedPagination();
		// Workaround: Parse and modify db_query param for simulated custom ordering
		$this->prepareSimulatedCustomOrdering();

		$success = $sql->gen($this->getParam('db_query'), $this->getParam('db_debug') ? true : false);
		if (!$success) return false;

		$rows_tree = self::arrayToTree($sql->rows(),
			$this->getParam('primary_field'),
			$this->getParam('sort_parent'));
		$rows = self::flattenTree($rows_tree,
			$this->getParam('sort_field'),
			$this->getParam('sort_order'));

		// Simulated pagination
		$rows = array_splice($rows,
			(int) $this->getParam('db_limit_offset'),
			($this->getParam('db_limit_count') ? $this->getParam('db_limit_count') : count($rows))
		);

		return $rows;
	}

	/**
	 * Converts a relational array with a parent field and a sort order field to a tree
	 * @param array $rows Relational array with a parent field and a sort order field
	 * @param string $primary_field The field name of the primary key (matches children to parents)
	 * @param string $sort_parent The field name whose value is the parent ID
	 * @return array Multidimensional array with child nodes under the "_children" key
	 */
	protected static function arrayToTree($rows, $primary_field, $sort_parent)
	{
		$nodes = array();
		$root = array($primary_field => 0);
		$nodes[] = &$root;

		while(!empty($nodes))
		{
			self::moveRowsToTreeNodes($nodes, $rows, $primary_field, $sort_parent);
		}

		return array(0 => $root);
	}

	/**
	 * Put rows with parent matching the ID of the first node into the next node's children
	 * @param array &$nodes Current queue of nodes, the first of which may have children added to it
	 * @param array &rows The remaining rows that have yet to be converted into children of nodes
	 * @param string $primary_field The field name of the primary key (matches children to parents)
	 * @param string $sort_parent The field name whose value is the parent ID
	 * @returns null
	 */
	protected static function moveRowsToTreeNodes(&$nodes, &$rows, $primary_field, $sort_parent)
	{
		$node = &$nodes[0];
		array_shift($nodes);
		$nodeID = (int) $node[$primary_field];
		foreach($rows as $key => $row)
		{
			$rowParentID = (int) $row[$sort_parent];

			// Note: This optimization only works if the SQL query executed was ordered by the sort parent.
			if($rowParentID > $nodeID) break;

			$node['_children'][] = &$row;
			unset($rows[$key]);
			$nodes[] = &$row;
			unset($row);
		}
	}

	/**
	 * Flattens a tree into a depth-first array, sorting each node by a field's values
	 * @param array $tree Tree with child nodes under the "_children" key
	 * @param mixed $sort_field The field name (string) or field names (array) whose value
	 *                          is or values are the sort order in the current tree node
	 * @param int $sort_order Desired sorting direction: 1 if ascending, -1 if descending
	 * @param int $depth The depth that this level of recursion is entering
	 * @return array One-dimensional array in depth-first order with depth indicated by the "_depth" key
	 */
	protected static function flattenTree($tree, $sort_field = null, $sort_order = 1, $depth = 0)
	{
		$flat = array();

		foreach($tree as $item)
		{
			$children = isset($item['_children']) ? $item['_children'] : null;
			unset($item['_children']);
			$item['_depth'] = $depth;
			if($depth > 0)
				$flat[] = $item;
			if(is_array($children))
			{
				uasort($children, function($node1, $node2) use ($sort_field, $sort_order)
				{
					return self::multiFieldCmp($node1, $node2, $sort_field, $sort_order);
				});
				$flat = array_merge($flat, self::flattenTree($children, $sort_field, $sort_order, $depth+1));
			}
		}

		return $flat;
	}

	/**
	 * Naturally compares two associative arrays given multiple sort keys and a reverse order flag
	 * @param array $row1 Associative array to compare to $row2
	 * @param array $row2 Associative array to compare to $row1
	 * @param mixed $sort_field Key (string) or keys (array) to compare
	 *                          the values of in both $row1 and $row2
	 * @param int $sort_order -1 to reverse the sorting order or 1 to keep the order as ascending
	 * @return int -1 if $row1 is less than $row2
	 *             0 if $row1 is equal to $row2
	 *             1 if $row1 is greater than $row2
	 */
	protected static function multiFieldCmp($row1, $row2, $sort_field, $sort_order = 1)
	{
		if (!is_array($sort_field))
			$sort_field = [$sort_field];
		$field = array_shift($sort_field);

		$cmp = strnatcmp((string) $row1[$field], (string) $row2[$field]);
		if ($sort_order === -1 || $sort_order === 1) $cmp *= $sort_order;
		if ($cmp === 0 && count($sort_field) >= 1)
			return self::multiFieldCmp($row1, $row2, $sort_field, $sort_order);
		return $cmp;
	}

	/**
	 * Resiliently counts the results from the last SQL query in the given resource
	 *
	 * Sets the count in $this->_total
	 *
	 * @param resource $sql SQL resource that executed a query
	 * @return int Number of results from the latest query
	 */
	protected function countResults($sql)
	{
		$this->_total = is_integer($sql->total_results) ? $sql->total_results : false; //requires SQL_CALC_FOUND_ROWS in query - see db handler
		if(false === $this->_total && $this->getModelTable() && !$this->getParam('nocount'))
		{
			//SQL_CALC_FOUND_ROWS not found in the query, do one more query
		//	$this->_total = e107::getDb()->db_Count($this->getModelTable()); // fails with specific listQry

			// Calculates correct total when using filters and search. //XXX Optimize.
			$countQry = preg_replace('/(LIMIT ([\d,\s])*)$/', "", $this->getParam('db_query'));

			$this->_total = e107::getDb()->gen($countQry);

		}
		return $this->_total;
	}

	/**
	 * Workaround: Parse and modify query to prepare for simulation of tree pagination
	 *
	 * This is a hack to maintain compatibility of pagination of tree
	 * models without SQL LIMITs
	 *
	 * Implemented out of necessity under
	 * https://github.com/e107inc/e107/issues/3015
	 *
	 * @returns null
	 */
	protected function prepareSimulatedPagination()
	{
		$db_query = $this->getParam('db_query');
		$db_query = preg_replace_callback("/LIMIT ([\d]+)[ ]*(?:,|OFFSET){0,1}[ ]*([\d]*)/i", function($matches)
		{
			// Count only
			if (empty($matches[2]))
			{
				$this->setParam('db_limit_count', $matches[1]);
			}
			// Offset and count
			else
			{
				$this->setParam('db_limit_offset', $matches[1]);
				$this->setParam('db_limit_count', $matches[2]);
			}

			return "";
		}, $db_query);
		$this->setParam('db_query', $db_query);
	}

	/**
	 * Workaround: Parse and modify query to prepare for simulation of custom ordering
	 *
	 * XXX: Not compliant with all forms of ORDER BY clauses
	 * XXX: Does not support quoted identifiers (`identifier`)
	 * XXX: Does not support mixed sort orders (identifier1 ASC, identifier2 DESC)
	 *
	 * This is a hack to enable custom ordering of tree models when
	 * flattening the tree.
	 *
	 * Implemented out of necessity under
	 * https://github.com/e107inc/e107/issues/3029
	 *
	 * @returns null
	 */
	protected function prepareSimulatedCustomOrdering()
	{
		$db_query = $this->getParam('db_query');
		$db_query = preg_replace_callback('/ORDER BY (?:.+\.)*[\.]*([A-Za-z0-9$_,]+)[ ]*(ASC|DESC)*/i', function($matches)
			{
				if (!empty($matches[1]))
				{
					$current_sort_field = $this->getParam('sort_field');
					if (!empty($current_sort_field))
					{
						$matches[1] = $current_sort_field.",".$matches[1];
					}
					$this->setParam('sort_field', array_map('trim', explode(',', $matches[1])));
				}
				if (!empty($matches[2]))
					$this->setParam('sort_order',
						(0 === strcasecmp($matches[2], 'DESC') ? -1 : 1)
					);

				return "";
			}, $db_query)
			// Optimization goes with e_tree_model::moveRowsToTreeNodes()
			. " ORDER BY " . $this->getParam('sort_parent') . "," . $this->getParam('primary_field');
		$this->setParam('db_query', $db_query);
	}

	/**
	 * Get single model instance from the collection
	 * @param integer $node_id
	 * @return e_model
	 */
	function getNode($node_id)
	{
		return $this->getData('__tree/'.$node_id);
	}

	/**
	 * Add or remove (when $node is null) model to the collection
	 *
	 * @param integer $node_id
	 * @param e_model $node
	 * @return e_tree_model
	 */
	function setNode($node_id, $node)
	{
		if(null === $node)
		{
			$this->removeData('__tree/'.$node_id);
			return $this;
		}

		$this->setData('__tree/'.$node_id, $node);
		return $this;
	}

	/**
	 * Check if model with passed id exists in the collection
	 *
	 * @param integer $node_id
	 * @return boolean
	 */
	public function isNode($node_id)
	{
		return $this->isData('__tree/'.$node_id);
	}

	/**
	 * Check if model with passed id exists in the collection and is not empty
	 *
	 * @param integer $node_id
	 * @return boolean
	 */
	public function hasNode($node_id)
	{
		return $this->hasData('__tree/'.$node_id);
	}

	/**
	 * Check if collection is empty
	 *
	 * @return boolean
	 */
	function isEmpty()
	{
		return (!$this->has('__tree'));
	}

	/**
	 * Check if collection is loaded (not null)
	 *
	 * @return boolean
	 */
	function isTree()
	{
		return $this->is('__tree');
	}

	/**
	 * Same as isEmpty(), but with opposite boolean logic
	 *
	 * @return boolean
	 */
	function hasTree()
	{
		return $this->has('__tree');
	}

	/**
	 * Render model data, all 'sc_*' methods will be recongnized
	 * as shortcodes.
	 *
	 * @param string $template
	 * @param boolean $parsesc parse external shortcodes, default is true
	 * @param e_vars $eVars simple parser data
	 * @return string parsed template
	 */
	public function toHTML($template, $parsesc = true, $eVars = null)
	{
		$ret = '';
		$i = 1;
		foreach ($this->getTree() as $model)
		{
			if($eVars) $eVars->treeCounter = $i;
			$ret .= $model->toHTML($template, $parsesc, $eVars);
			$i++;
		}
		return $ret;
	}

	public function toXML()
	{
		return '';
		// UNDER CONSTRUCTION
	}

	/**
	 * Convert model object to array
	 * @param boolean $total include total results property
	 * @return array object data
	 */
	public function toArray($total = false)
	{
		$ret = array();
		foreach ($this->getTree() as $id => $model)
		{
			$ret[$id] = $model->toArray();
		}
		if($total) $ret['total'] = $this->getTotal();

		return $ret;
	}

	/**
	 * Convert object data to a string
	 *
	 * @param boolean $AddSlashes
	 * @param string $node_id optional, if set method will return corresponding value as a string
	 * @param boolean $total include total results property
	 * @return string
	 */
	public function toString($AddSlashes = true, $node_id = null, $total = false)
	{
		if (null !== $node_id && $this->isNode($node_id))
		{
			return $this->getNode($node_id)->toString($AddSlashes);
		}
		return (string) e107::getArrayStorage()->WriteArray($this->toArray($total), $AddSlashes);
	}

	public function update($from_post = true, $force = false, $session_messages = false)
	{
	}

	public function delete($ids, $destroy = true, $session_messages = false)
	{
	}
}

class e_front_tree_model extends e_tree_model
{
	/**
	 * @var integer Last SQL error number
	 */
	protected $_db_errno = 0;

	/**
	 * @var string Last SQL error message
	 */
	protected $_db_errmsg = '';

	/**
	 * @var string Last SQL query
	 */
	protected $_db_qry = '';

    /**
     * @return boolean
     */
    public function hasSqlError()
    {
    	return !empty($this->_db_errno);
    }

    /**
     * @return integer last mysql error number
     */
    public function getSqlErrorNumber()
    {
    	return $this->_db_errno;
    }

    /**
     * @return string last mysql error message
     */
    public function getSqlError()
    {
    	return $this->_db_errmsg;
    }

    /**
     * @return string last mysql error message
     */
    public function getSqlQuery()
    {
    	return $this->_db_qry;
    }

    /**
     * @return boolean
     */
    public function hasError()
    {
    	return $this->hasSqlError();
    }

	/**
	 * Batch update tree records/nodes
	 * @param string $field field name
	 * @param string $value
	 * @param string|array $ids numerical array or string comma separated ids
	 * @param mixed $syncvalue value to be used for model data synchronization (db value could be something like '1-field_name'), null - no sync
	 * @param boolean $sanitize [optional] default true
	 * @param boolean $session_messages [optional] default false
	 * @return integer updated count or false on error
	 */
	public function batchUpdate($field, $value, $ids, $syncvalue = null, $sanitize = true, $session_messages = false)
	{
		$tp = e107::getParser();
		$sql = e107::getDb();
		if(empty($ids))
		{
			return 0;
		}
		if(!is_array($ids))
		{
			$ids = explode(',', $ids);
		}

		if(true === $syncvalue)
		{
			$syncvalue = $value;
		}

		if($sanitize)
		{
			$ids = array_map(array($tp, 'toDB'), $ids);
			$field = $tp->toDB($field);
			$value = "'".$tp->toDB($value)."'";
		}
		$idstr = implode(', ', $ids);

		$table = $this->getModelTable();

		$res = $sql->update($table, "{$field}={$value} WHERE ".$this->getFieldIdName().' IN ('.$idstr.')', $this->getParam('db_debug', false));
		$this->_db_errno = $sql->getLastErrorNumber();
		$this->_db_errmsg = $sql->getLastErrorText();
		$this->_db_qry = $sql->getLastQuery();

		if(!$res)
		{
			if($sql->getLastErrorNumber())
			{
				$data = array(
					'TABLE'     => $table ,
					'error_no' => $this->_db_errno,
					'error_msg' => $this->_db_errmsg,
					'qry'       => $this->_db_qry,
					'url'       => e_REQUEST_URI,
				);


				$this->addMessageError(LAN_UPDATED_FAILED, $session_messages, $data);
				$this->addMessageDebug('SQL Error #'.$sql->getLastErrorNumber().': '.$sql->getLastErrorText());
			}
			else
			{
				$this->addMessageInfo(LAN_NO_CHANGE, $session_messages);
			}
		}
		else
		{
			$this->clearCache();
		}

		$modelCacheCheck = $this->getParam('clearModelCache');
		if(null === $syncvalue && !$modelCacheCheck) return $res;

		foreach ($ids as $id)
		{
			$node = $this->getNode($id);
			if(!$node) continue;

			if(null !== $syncvalue)
			{
				$node->set($field, $syncvalue)
					->setMessages($session_messages);
			}
			if($modelCacheCheck) $this->clearCache();
		}
		return $res;
	}
}

class e_admin_tree_model extends e_front_tree_model
{


	/**
	 * Batch Delete records
	 * @param mixed $ids
	 * @param boolean $destroy [optional] destroy object instance after db delete
	 * @param boolean $session_messages [optional]
	 * @return integer deleted records number or false on DB error
	 */
	public function delete($ids, $destroy = true, $session_messages = false)
	{
		if(!$ids) return 0;

		if(!is_array($ids))
		{
			$ids = explode(',', $ids);
		}

		$tp = e107::getParser();
		$ids = array_map(array($tp, 'toDB'), $ids);
		$idstr = implode(', ', $ids);

		$sql = e107::getDb();
		$table = $this->getModelTable();
		$sqlQry = $this->getFieldIdName().' IN (\''.$idstr.'\')';

		$res = $sql->delete($table, $sqlQry);

		$this->_db_errno = $sql->getLastErrorNumber();
		$this->_db_errmsg = $sql->getLastErrorText();
		$this->_db_qry = $sql->getLastQuery();

		$modelCacheCheck = $this->getParam('clearModelCache');

		if(!$res)
		{
			if($sql->getLastErrorNumber())
			{
				$data = array(
					'TABLE'     => $table,
					'error_no' => $this->_db_errno,
					'error_msg' => $this->_db_errmsg,
					'qry'       => $this->_db_qry,
					'url'       => e_REQUEST_URI,
				);


				$this->addMessageError('SQL Delete Error: ' . $sql->getLastQuery(), $session_messages, $data); //TODO - Lan
				$this->addMessageDebug('SQL Error #'.$sql->getLastErrorNumber().': '.$sql->getLastErrorText());
			}
		}
		elseif($destroy || $modelCacheCheck)
		{
			foreach ($ids as $id)
			{
				if($this->hasNode($id))
				{
					$this->getNode($id)->clearCache()->setMessages($session_messages);
					if($destroy)
					{
						call_user_func(array($this->getNode(trim($id)), 'destroy')); // first call model destroy method if any
						$this->setNode($id, null);
					}
				}
			}
		}

		if($table != 'admin_log')
		{
			$logData = array('TABLE'=>$table, 'WHERE'=>$sqlQry);
			e107::getAdminLog()->addArray($logData)->save('ADMINUI_03');
		}
		return $res;
	}

	/**
	 * Batch Copy Table Rows.
	 */
	public function copy($ids, $session_messages = false)
	{
		if(empty($ids[0]))
		{
			$this->addMessageError('No IDs provided', $session_messages); //TODO - Lan
			$this->addMessageDebug(print_a(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),true),$session_messages); //TODO - Lan
			return false;
		}


		$tp = e107::getParser();
		$ids = array_map(array($tp, 'toDB'), $ids);
		$idstr = implode(', ', $ids);

		$sql = e107::getDb();
		$res = $sql->db_CopyRow($this->getModelTable(), "*", $this->getFieldIdName().' IN ('.$idstr.')');
		if(false !== $res)
		{
			$this->addMessageSuccess('Copied #'.$idstr);
		}
		else
		{
			if($sql->getLastErrorNumber())
			{
				$this->addMessageError('SQL Copy Error', $session_messages); //TODO - Lan
				$this->addMessageDebug('SQL Error #'.$sql->getLastErrorNumber().': '.$sql->getLastErrorText());
				$this->addMessageDebug('$SQL Query'.print_a($sql->getLastQuery(),true));
			}
		}
		$this->_db_errno = $sql->getLastErrorNumber();
		$this->_db_errmsg = $sql->getLastErrorText();
		$this->_db_qry = $sql->getLastQuery();
		return $res;
	}


	/**
	 * Get urls/url data for given nodes
	 */
    public function url($ids, $options = array(), $extended = false)
    {
    	$ret = array();
    	foreach ($ids as $id)
    	{
    		if(!$this->hasNode($id)) continue;

			$model = $this->getNode($id);
			if($this->getUrl()) $model->setUrl($this->getUrl()); // copy url config data if available
			$ret[$id] = $model->url(null, $options, $extended);
		}
		return $ret;
    }


	/**
	 * Export Selected Data
	 * @param $ids
	 * @return null
	 */
	public function export($ids)
    {
        $ids = e107::getParser()->filter($ids,'int');

        if(empty($ids))
        {
            return false;
        }

        $idstr = implode(', ', $ids);

	    $table      = array($this->getModelTable());

	    $filename   = "e107Export_" .$this->getModelTable()."_". date("YmdHi").".xml";
	    $query      = $this->getFieldIdName().' IN ('.$idstr.') '; //  ORDER BY '.$this->getParam('db_order') ;

		e107::getXml()->e107Export(null,$table,null,null, array('file'=>$filename,'query'=>$query));

		return null;

    }
}
