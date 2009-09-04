<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Base Model
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/model_class.php,v $
 * $Revision: 1.6 $
 * $Date: 2009-09-04 15:27:28 $
 * $Author: secretr $
*/

if (!defined('e107_INIT')) { exit; }

/**
 * Base e107 Model class
 *
 * @package e107
 * @category e107_handlers
 * @version 1.0
 * @author SecretR
 * @copyright Copyright (c) 2009, e107 Inc.
 */
class e_model
{
    /**
     * Object data
     *
     * @var array
     */
    protected $_data = array();
    
    /**
    * Posted data
    * Back-end related data
    *
    * @var array
    */
    protected $_posted_data = array();
    
    /**
     * Runtime cache of parsed from {@link _getData()} keys
     *
     * @var array
     */
    protected $_parsed_keys = array();
    
    /**
     * DB structure array
     * Awaits implementation logic, 
     * should be consistent with db::_getTypes() and db::_getFieldValue()
     *
     * @var array
     */
    protected $_FIELD_TYPES = array();
    
    /**
     * Avoid DB calls if data is not changed
     *
     * @see mergePostedData()
     * @var boolean
     */
    protected $data_has_changed = false;
    
    /**
     * Validation structure in format 
     * 'field_name' => rule (to be used with core validator handler)
     * Awaits implementation logic, should be consistent with expected frmo the validator 
     * structure.
     *
     * @var array
     */
    protected $_validation_rules = array();
    
    /**
     * Validator object
     * 
     * @var validatorClass 
     */
    protected $_validator = null;
    
    /**
     * Validation error stack 
     * See also {@link validate()}, {@link setErrors()}, {@link getErrors()}
     * 
     * @var array
     */
    protected $_validation_errors = array();

    
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
		$this->setData($data);
	}
    
    /**
     * Set name of object's field id
     *
     * @see getId()
     * 
     * @param   string $name
     * @return  e_model
     */
    public function setFieldIdName($name)
    {
        $this->_idFieldName = $name;
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
        return $this->_idFieldName;
    }
    
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
     * @return e_model
     */
    public function setValidationRules(array $vrules)
    {
    	if(empty($this->_validation_rules))
    	{
    		$this->_validation_rules = $vrules;
    	}
    	return $this;
    }
    
    /**
     * Retrieve object field id value
     *
     * @return mixed
     */
    public function getId()
    {
        if ($this->getIdFieldName()) 
        {
            return $this->getData($this->getIdFieldName(), 0, null);
        }
        return $this->getData('id', 0, null);
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
     * - empty string
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
		if(null !== $this->getPostedData((string) $key))
		{
			return e107::getParser()->post_toForm($this->getPostedData((string) $key, null, $index));
		}
		return e107::getParser()->toForm($this->getData((string) $key, $default, $index));
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
     * Overwrite posted data in the object for a single field. Key is not parsed.
     * Public proxy of {@link _setDataSimple()}
     * Use this method to store data from non-trustable sources (e.g. _POST) - it doesn't overwrite 
     * the original object data
     * 
     * @param string $key
     * @param mixed $data
     * @param boolean $strict update only
     * @return e_model
     */
    public function setPosted($key, $data = null, $strict = false)
    {
        return $this->_setDataSimple($key, $data, $strict, '_posted_data');
    }
    
    /**
     * Overwrite posted data in the object. Key is parsed (multidmensional array support).
     * Public proxy of {@link _setData()}
     * Use this method to store data from non-trustable sources (e.g. _POST) - it doesn't overwrite 
     * the original object data
     *
     * @param string|array $key
     * @param mixed $data
     * @param boolean $strict update only
     * @return e_model
     */
    public function setPostedData($key = null, $data = null, $strict = false)
    {
        return $this->setData($key, $data, $strict, '_posted_data');
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
    public function addPostedData($key, $value = null, $override = true)
    {
    	return $this->_addData($key, $value, $override, '_posted_data');
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
     * Unset single posted data field from the object.
     * Public proxy of {@link _unsetDataSimple()}
     *
     * @param string $key
     * @return e_model
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
     * @return e_model
     */
    public function removePostedData($key = null)
    {
    	return $this->_unsetData($key, '_posted_data');
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
    public function hasPosted($key)
    {
    	return $this->_hasData($key, '_posted_data');
    }
    
    public function hasPostedData()
    {
    	return $this->_hasData('', '_posted_data');
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
     * Copied posted data will be removed (no matter if copy is successfull or not)
     * 
     * If $strict is true, only existing object data will be copied (update)
     * TODO - move to admin e_model extension
     *
     * @param boolean $strict
     * @param boolean $sanitize
     * @param boolean $validate perform validation check
     * @return e_model
     */
    public function mergePostedData($strict = true, $sanitize = true, $validate = true)
    {
    	if(!$this->getPostedData() || ($validate && !$this->validate()))
    	{
    		return $this;
    	}
    	
    	$tp = e107::getParser();
    	
    	//TODO - sanitize method based on validation rules OR _FIELD_TYPES array?
       	$data = $sanitize ? $tp->toDB($this->getPostedData()) : $this->getPostedData();
       	
    	foreach ($data as $field => $dt) 
    	{
    		$this->setData($field, $dt, $strict)
    			->removePostedData($field);
    	}
    	return $this;
    }
    
    /**
     * Merge passed data array with the object data
     * If $strict is true, only existing object data will be copied (update)
     * TODO - move to admin e_model extension
     *
     * @param array $src_data
     * @param boolean $sanitize
     * @param boolean $validate perform validation check
     * @return e_model
     */
    public function mergeData(array $src_data, $strict = true, $sanitize = true, $validate = true)
    {
    	//FIXME
    	if(!$src_data || ($validate && !$this->validate($src_data)))
    	{
    		return $this;
    	}
    	
   	    //TODO - sanitize method based on validation rules OR _FIELD_TYPES array?
   		if($sanitize)
   		{
   			$src_data =  e107::getParser()->toDB($src_data);
   		}
   		
		foreach ($src_data as $key => $value)
		{
			$this->setData($key, $value, $strict);
		}
   		
    	return $this;
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
     * @param string $key
     * @param mixed $default
     * @param integer $index
     * @param boolean $posted data source
     * @return mixed
     */
    protected function _getData($key = '', $default = null, $index = null, $data_src = '_data')
    {
    	$key = trim($key, '/');
        if ('' === $key) 
        {
            return $this->$data_src;
        }

        if (strpos($key, '/'))
        {
        	if(isset($this->_parsed_keys[$data_src.'/'.$key]))
        	{
        		return $this->_parsed_keys[$data_src.'/'.$key];
        	}
            $keyArr = explode('/', $key);
            $data = $this->$data_src;
            foreach ($keyArr as $k) 
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
            if($strict && '_data_structure' !== $data_src)
	    	{
				foreach(array_keys($key) as $k) 
		        {
		        	$this->_setData($k, $key[$k], true, $data_src);
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
			    $this->_setData($key.'/'.$k, $v, true, $data_src);
			}
			return $this;
       	}
        
        //multidimensional array support - parse key
        $key = trim($key, '/');
        if(strpos($key,'/')) 
        {
        	//if strict - update only
	        if($strict && !$this->isData($key))
	        {
	        	return $this;
	        }
	        
        	$keyArr = explode('/', $key);
        	$data = &$this->$data_src;
            for ($i = 0, $l = count($keyArr); $i < $l; $i++) 
            {
	            $k = $keyArr[$i];
		        
	            if (!isset($data[$k])) 
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
        	$this->$data_src = array();
            return $this;
        } 
		
        $key = trim($key, '/');
        if(strpos($key,'/')) 
        {
        	$keyArr = explode('/', $key);
        	$data = &$this->$data_src;
        	
        	$unskey = array_pop($data);
        	
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
     * Validate posted data:
     * 1. validate posted data against object validation rules
     * 2. add validation errors to the object if any
     * 3. return true for valid and false for non-valid data
     *
     * @param array $data optional - data for validation, defaults to posted data
     * @return boolean
     */
    public function validate(array $data = array())
    {
    	$this->_validation_errors = array();
    	
    	if(!$this->getValidationRules())
    	{
    		return true;
    	}
    	
    	$result = $this->getValidator()->validateFields(($data ? $data : $this->getPostedData()), $this->getValidationRules());
    	if(!empty($result['errors']))
    	{
    		$this->_validation_errors = $result['errors'];
    		return false;
    	}
    	
    	return true;
    }
    
    /**
     * @return boolean
     */
    public function isError()
    {
    	return !empty($this->_validation_errors);
    }
    
    /**
     * Under construction
     * Add human readable errors to eMessage stack
     * 
     * @param boolean $reset reset errors
     * @param boolean $session store messages to session
     * @return e_model
     */
    public function setErrors($reset = true, $session = false)
    {
    	//$emessage = eMessage::getInstance();
    	return $this;
    }
    
    /**
     * Load data from DB
     * Awaiting for child class implementation
     *
     */
    public function load()
    {
    }
    
    /**
     * Save data to DB
     * Awaiting for child class implementation
     *
     */
    public function save()
    {
    }
    
    /**
     * Insert data to DB
     * Awaiting for child class implementation
     *
     */
    public function dbInsert()
    {
    }
    
    /**
     * Update DB data
     * Awaiting for child class implementation
     *
     */
    public function dbUpdate()
    {
    }
    
	/**
	 * @return validatorClass
	 */
	public function getValidator()
	{
		if(null === $this->_validator)
		{
			$this->_validator = e107::getObject('validatorClass', null, e_HANDLER.'validator_class.php');
		}
		return $this->_validator;
	}
	
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
		return (string) e107::getArrayStorage()->WriteArray($this->getData(), $AddSlashes);
	}
	
	/**
	 * Magic method - convert object data to a string
	 * NOTE: before PHP 5.2.0 the __toString method was only 
	 * called when it was directly combined with echo() or print()
	 * 
	 * NOTE: PHP 5.3+ is throwing parse error if __toString has optional arguments.
	 *
	 * @param boolean $AddSlashes
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString((@func_get_arg(0) === true));
	}
}