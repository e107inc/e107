<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/override_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

/*
 * USAGE
 * In user code, to override an existing function...
 *
 * $override->override_function('original_func_name','mynew_function_name',['optional_include_file_from_root']);
 *
 * In e107 code...
 * if ($over_func_name = $override->override_check('original_func_name')) {
 *	$result=call_user_func($over_func_name, params...);
 * }
 * 
 *
 */
//XXX IMPORTANT: // do not use e107 specific functions in this file - it may break secure_img_handler. 
class override {
	
	protected $functions = array();
	protected $includes = array();
	
	
	
	/**
	 * Replace an existing function or class method
	 * @param string|array $override - function name or class::method
	 * @param string|array $function - new function name or class::method 
	 * @param $include (optional) - file to include from root dir. 
	 * @example e107::getOverride()->replace('secure_image::create_code', 'myclass::mymethod');
	 */
	public function replace($override,$function,$include='') // Alias with class functionality. 
	{
		if(is_array($override))
		{
			$arr = $override[0]."::".$override[1];		
		}
		else
		{
			$arr = $override;	
		}
		
		$this->override_function($arr, $function, $include);
	}
	/**
	 * check if an override exists
	 * @param $override : function name or class object
	 * @param $method : method name when 'class' is used for $override
	 * @return mixed
	 * @example if ($user_func = e107::getOverride()->check($this,'secure_image'))
				{
	 				return call_user_func($user_func);
				}  
	 */
	public function check($override,$method='') // alias with check for class object
	{
		
		if($method != '')
		{			
			$class = get_class($override);
			$override = $class."::".$method;	
		}
		return $this->override_check($override);	
	}
	

	 
	function override_function($override, $function, $include) 
	{
		if ($include)
		{
			$this->includes[$override] = $include;
		}
		else if (isset($this->includes[$override]))
		{
			unset($this->includes[$override]);
		}
		
		$this->functions[$override] = $function;
	}
	 
	 
	function override_check($override)
	{		
		if (isset($this->includes[$override])) 
		{			
			if (file_exists($this->includes[$override]))
			{
				include_once($this->includes[$override]);
			}	
		} 
		
		if($override && isset($this->functions[$override]))
		{
			$tmp =  (strpos($this->functions[$override],"::")) ?  explode("::",$this->functions[$override]) : $this->functions[$override];
		}
		else
		{
			$tmp = false;
		} 
		if(is_array($tmp) && class_exists($tmp[0]))
		{
			$cl = new $tmp[0];
			 if(method_exists($cl,$tmp[1]))
			 {
			 	return $this->functions[$override];
			 }	
		}	
	
		if ($override && isset($this->functions[$override]) && function_exists($this->functions[$override]))
		{
			
			return $this->functions[$override];
		}
		else
		{
			return false;
		}

	}
}
	
