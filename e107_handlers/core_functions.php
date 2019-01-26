<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Core functions
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/core_functions.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/

//

//

/**
 * Use these to combine isset() and use of the set value. or defined and use of a constant
 * i.e. to fix  if($pref['foo']) ==> if ( varset($pref['foo']) ) will use the pref, or ''.
 * Can set 2nd param to any other default value you like (e.g. false, 0, or whatever)
 * $testvalue adds additional test of the value (not just isset())
 * Examples:
 * <code>
 * $something = pref;  Bug if pref not set         ==> $something = varset(pref);
 * $something = isset(pref) ? pref : "";              ==> $something = varset(pref);
 * $something = isset(pref) ? pref : default;         ==> $something = varset(pref,default);
 * $something = isset(pref) && pref ? pref : default; ==> use varsettrue(pref,default)
 * </code>
 * 
 * @param mixed $val
 * @param mixed $default [optional]
 * @return mixed
 */
function varset(&$val, $default='')
{
	if (isset($val)) { return $val; }
	return $default;
}

/**
 * Check if the given string is defined (constant)
 * 
 * @param string $str
 * @param mixed $default [optional]
 * @return mixed 
 */
function defset($str, $default='')
{
	if(is_array($str))
	{
		return false;
	}

	if (defined($str)) { return constant($str); }
	return $default;
}

/**
 * Variant of {@link varset()}, but only return the value if both set AND 'true'
 * @deprecated - use vartrue();
 * @param mixed $val
 * @param mixed $default [optional]
 * @return mixed
 */
function varsettrue(&$val, $default='')
{
	return vartrue($val, $default);
}

/**
 * Alias of {@link varsettrue()}
 * 
 * @param mixed $val
 * @param mixed $default [optional]
 * @return mixed
 */
function vartrue(&$val, $default='')
{

	if (isset($val) && $val) { return $val; }
	return $default;
}

/**
 * Variant of {@link defset()}, but only return the value if both defined AND 'true'
 * @deprecated  - use deftrue()
 * @param string $str
 * @param mixed $default [optional]
 * @return mixed
 */
function defsettrue($str, $default='')
{
	if (defined($str) && constant($str)) { return constant($str); }
	return $default;
}

/**
 * Alias of {@link defsettrue()}
 * 
 * @param string $str
 * @param mixed $default [optional]
 * @return mixed
 */
function deftrue($str, $default='')
{
	if (defined($str) && constant($str)) { return constant($str); }
	return $default;
}

function e107_include($fname)
{
	global $e107_debug, $_E107;
	$ret = (($e107_debug || isset($_E107['debug']) || deftrue('e_DEBUG')) ? include($fname) : @include($fname));
	return $ret;
}

function e107_include_once($fname)
{
	global $e107_debug, $_E107;
	if(is_readable($fname))
	{
		$ret = ($e107_debug || isset($_E107['debug']) || deftrue('e_DEBUG')) ? include_once($fname) : @include_once($fname);
	}
	return (isset($ret)) ? $ret : '';
}

function e107_require_once($fname)
{
	global $e107_debug, $_E107;
	
	$ret = (($e107_debug || isset($_E107['debug']) || deftrue('e_DEBUG')) ? require_once($fname) : @require_once($fname));
	
	return $ret;
}

function e107_require($fname)
{
	global $e107_debug, $_E107;
	$ret = (($e107_debug || isset($_E107['debug']) || deftrue('e_DEBUG')) ? require($fname) : @require($fname));
	return $ret;
}


function print_a($var, $return = FALSE)
{
	if( ! $return)
	{
		echo '<pre>'.htmlspecialchars(print_r($var, TRUE), ENT_QUOTES, 'utf-8').'</pre>';
		return TRUE;
	}
	else
	{
		return '<pre>'.htmlspecialchars(print_r($var, true), ENT_QUOTES, 'utf-8').'</pre>';
	}
}

function e_print($expr = null)
{
	$args = func_get_args();
	if(!$args) return;
	foreach ($args as $arg) 
	{
		print_a($arg);
	}
}

function e_dump($expr = null)
{
	$args = func_get_args();
	if(!$args) return;
	
	echo '<pre>';
	call_user_func_array('var_dump', $args);
	echo '</pre>';
}

/**
 * Strips slashes from a var if magic_quotes_gqc is enabled
 *
 * @param mixed $data
 * @return mixed
 */
function strip_if_magic($data)
{
	if (MAGIC_QUOTES_GPC == true)
	{
		return array_stripslashes($data);
	}
	else
	{
		return $data;
	}
}

/**
 * Return an array with changes between 2 arrays. 
 */
function array_diff_recursive($array1, $array2) 
{
	$ret = array();

	foreach($array1 as $key => $val) 
	{
    	if(array_key_exists($key, $array2)) 
    	{
      		if(is_array($val)) 
      		{
        		$diff = array_diff_recursive($val, $array2[$key]);

				if(count($diff)) 
	        	{
	        	 	$ret[$key] = $diff; 
				}
			} 
			else 
			{
				if($val != $array2[$key]) 
	        	{
					$ret[$key] = $val;
				}
			}
		} 
    	else 
    	{
    	  $ret[$key] = $val;
		}
	}
	
  return $ret;
} 







/**
 * Strips slashes from a string or an array
 *
 * @param mixed $value
 * @return mixed
 */
function array_stripslashes($data)
{
	return is_array($data) ? array_map('array_stripslashes', $data) : stripslashes($data);
}

function echo_gzipped_page()
{

    if(headers_sent())
	{
        $encoding = false;
    }
	elseif( strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'x-gzip') !== false )
	{
        $encoding = 'x-gzip';
    }
	elseif( strpos($_SERVER["HTTP_ACCEPT_ENCODING"],'gzip') !== false )
	{
        $encoding = 'gzip';
    }
	else
	{
        $encoding = false;
    }

    if($encoding)
	{
        $contents = ob_get_contents();
        ob_end_clean();
        header('Content-Encoding: '.$encoding);
        print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
        $size = strlen($contents);
        $contents = gzcompress($contents, 9);
        $contents = substr($contents, 0, $size);
        print($contents);
        exit();
    }
	else
	{
        ob_end_flush();
        exit();
    }
}


// Better Array-sort by key function by acecream (22-Apr-2003 11:02) http://php.net/manual/en/function.asort.php
if (!function_exists('asortbyindex')) 
{
    function asortbyindex($array, $key)
    {
       foreach ($array as $i => $k)
       {
            $sort_values[$i] = $array[$i][$key];
       }
       asort ($sort_values);
       reset ($sort_values);
       while (list ($arr_key, $arr_val) = each ($sort_values))
       {
              $sorted_arr[] = $array[$arr_key];
       }
       return $sorted_arr;
    }
}

if (!function_exists('r_emote')) 
{
	/**
	 * Still in use.
	 */
	function r_emote()
	{
		global $sysprefs, $pref;
		$tp = e107::getParser();
	
		if (!is_object($tp->e_emote))
		{
		//	require_once(e_HANDLER.'emote_filter.php');
			$tp->e_emote = new e_emoteFilter;
		}
		
		$str = '';
		foreach($tp->e_emote->emotes as $key => $value)		// filename => text code
		{
			$key = str_replace("!", ".", $key);					// Usually '.' was replaced by '!' when saving
			$key = preg_replace("#_(\w{3})$#", ".\\1", $key);	// '_' followed by exactly 3 chars is file extension
			$key = e_IMAGE_ABS."emotes/" . $pref['emotepack'] . "/" .$key;		// Add in the file path
	
			$value2 = substr($value, 0, strpos($value, " "));
			$value = ($value2 ? $value2 : $value);
			$value = ($value == '&|') ? ':((' : $value;
			$value = " ".$value." ";

		//	$str .= "\n<a class='addEmote' data-emote=\"".$value."\" href=\"javascript:addtext('$value',true)\"><img src='$key' alt='' /></a> ";
			$str .= "\n<a class='addEmote' data-emote=\"".$value."\" href=\"#\"><img src='$key' alt='' /></a> ";
		}

		$JS = "

		$('.addEmote').click(function(){

			val = $(this).attr('data-emote')
			addtext(val,true);
			return false;
		});
		";

		e107::js('footer-inline',$JS);


		return "<div class='spacer'>".$str."</div>";
	}
}




if (!function_exists('multiarray_sort')) 
{
	
	
	/**
	 * Sort a Multi-Dimensional array
	 * @param $array
	 * @param $key
	 * @param $order
	 * @param $natsort
	 * @param $case
	 * @return array sorted array.
	 */
    function multiarray_sort(&$array, $key, $order = 'asc', $natsort = true, $case = true)
    {
        if(!is_array($array)) return $array;

        $order = strtolower($order);
        foreach ($array as $i => $arr)
        {
           $sort_values[$i] = varset($arr[$key]);
        }

        if(!$natsort) 
        {
            ($order=='asc')? asort($sort_values) : arsort($sort_values);
        }
        elseif(isset($sort_values))
        {
             $case ? natsort($sort_values) : natcasesort($sort_values);
             if($order != 'asc') $sort_values = array_reverse($sort_values, true);
        }
        

        if(!isset($sort_values))
        {
            return $array;
        }
            
        reset ($sort_values);
/*
        while (list ($arr_key, $arr_val) = each ($sort_values))
        {
 			$key = is_numeric($arr_key) ? "" : $arr_key; // retain assoc-array keys. 
 			$sorted_arr[$key] = $array[$arr_key];
        }*/

        foreach($sort_values as $arr_key=>$arr_val)
        {
            $key = is_numeric($arr_key) ? "" : $arr_key; // retain assoc-array keys.
 			$sorted_arr[$key] = $array[$arr_key];
        }
        return $sorted_arr;
    }
}

/**
 * Array Storage Class. 
 */
class e_array {

    /**
    * Returns an array from stored array data in php serialized, e107 var_export and json-encoded data. 
    *
    * @param string $ArrayData
    * @return array|bool stored data
    */
    public function unserialize($ArrayData) 
    {
        if ($ArrayData == ""){
            return false;
        }

        if(is_array($ArrayData))
        {
            return false;
        }
        
        // Saftety mechanism for 0.7 -> 0.8 transition. 
        if(substr($ArrayData,0,2)=='a:' || substr($ArrayData,0,2)=='s:') // php serialize.
        {
            $dat = unserialize($ArrayData);
            $ArrayData = $this->WriteArray($dat,FALSE);
        }

	    if(substr($ArrayData,0,1) === '{' || substr($ArrayData,0,1) === '[') // json
	    {
	        $dat = json_decode($ArrayData, true);

	     //   e107::getDebug()->log("Json data found");

	        if(json_last_error() !=  JSON_ERROR_NONE && (e_DEBUG === true))
	        {
	            echo "<div class='alert alert-danger'><h4>e107::unserialize() Parser Error (json)</h4></div>";
		        echo "<pre>";
				debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
				echo "</pre>";
	        }

	        return $dat;
	    }

		// below is var_export() format using eval();

        $ArrayData = trim($ArrayData);

        if(strpos($ArrayData, "\$data = ") === 0) // Fix for buggy old value.
		{
			$ArrayData = substr($ArrayData,8);
		}

        if(strtolower(substr($ArrayData,0,5)) != 'array')
        {
            return false;
        }

		if(strpos($ArrayData,"0 => \'")!=false)
		{
             $ArrayData = stripslashes($ArrayData);
		}

	    $ArrayData = str_replace('=&gt;','=>',$ArrayData); //FIX for PDO encoding of strings. .


	    if(trim($ArrayData) == 'Array') // Something went wrong with storage.
        {
            $debug = debug_backtrace(false);
            e107::getMessage()->addDebug("Bad Array Storage found: ". print_a($debug,true));

            return array();
        }

        $data = "";
        $ArrayData = '$data = '.$ArrayData.';';

		if(PHP_MAJOR_VERSION > 6) // catch parser error.
	    {
	        try
	        {
			    @eval($ArrayData);
			}
			catch (ParseError $e)
			{

				if(e_DEBUG === true)
				{
					$message = $e->getMessage();
					$message .= print_a($ArrayData,true);
					echo "<div class='alert alert-danger'><h4>e107::unserialize() Parser Error</h4>". $message. "</div>";
					echo "<pre>";
					debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
					echo "</pre>";
				}

			    return array();

			}

	    }
		else
		{

			@eval($ArrayData);
	        if (!isset($data) || !is_array($data))
	        {
	            trigger_error("Bad stored array data - <br /><br />".htmlentities($ArrayData), E_USER_ERROR);
	            return false;
	        }

		}


        return $data;        
    }
    
    
    /**
    * Return a string containg exported array data.
    *
    * @param array $ArrayData array to be stored
    * @param bool|string $mode true = var_export with addedslashes, false = var_export (default), 'json' = json encoded
    * @return null|string
    */
    public function serialize($ArrayData, $mode = false)
    {       
        if (!is_array($ArrayData) || empty($ArrayData))
        {
            return null;
        }

        if($mode === 'json')
        {
            return json_encode($ArrayData, JSON_PRETTY_PRINT);
        }

        $Array = var_export($ArrayData, true);

        if ($mode == true)
        {
            $Array = addslashes($Array);
        }

        return $Array;        
    }


    /**
     * @DEPRECATED - Backwards Compatible. Use e107::serialize() instead; 
    * @param array $ArrayData array to be stored
    * @param bool $AddSlashes default true, add slashes for db storage, else false
    * @return string a string containg exported array data.
     */
    function WriteArray($ArrayData, $AddSlashes = true) {
        
        return  $this->serialize($ArrayData, $AddSlashes);   

    }
	
	function write($ArrayData, $AddSlashes = true) {
        
        return  $this->serialize($ArrayData, $AddSlashes);   

    }

    /**
    * @DEPRECATED: Use e107::unserialize(); instead. 
    * Returns an array from stored array data.
    * @deprecated
    * @param string $ArrayData
    * @return array stored data
    */
    function ReadArray($ArrayData) 
    {
        return $this->unserialize($ArrayData);
    }
	
	 function read($ArrayData) 
    {
        return $this->unserialize($ArrayData);
    }
	
	/**
	 * Load and unserialize stored data from a local file inside SYSTEM folder
	 * @example e107::getArrayStorage()->load('import/somefile'); // -> e_SYSTEM/import/somefile.php
	 * @example e107::getArrayStorage()->load('somefile', 'weird'); // -> e_SYSTEM/somefile.weird
	 * 
	 * @param string $systemLocationFile relative to e_SYSTEM file path (without the extension)
	 * @param string $extension [optional] file extension, default is 'php'
	 * @return array or false when file not found (or on error)
	 */
	public function load($systemLocationFile, $extension = 'php')
	{
		if($extension) $extension = '.'.$extension;
		$_f = e_SYSTEM.preg_replace('#[^\w/]#', '', trim($systemLocationFile, '/')).$extension;
		if(!file_exists($_f))
		{
			return false;
		}
		$content = file_get_contents($_f);

		return $this->read($content);
	}
	
	/**
	 * Serialize and store data to a local file inside SYSTEM folder
	 * @example e107::getArrayStorage()->store($arrayData, 'import/somefile'); // -> e_SYSTEM/import/somefile.php
	 * @example e107::getArrayStorage()->store($arrayData, 'somefile', 'weird'); // -> e_SYSTEM/somefile.weird
	 * 
	 * @param string $systemLocationFile relative to e_SYSTEM file path (without the extension)
	 * @param string $extension [optional] file extension, default is 'php'
	 * @return array or false when file not found (or on error)
	 */
	public function store($array, $systemLocationFile, $extension = 'php')
	{
		if($extension) $extension = '.'.$extension;
		$_f = e_SYSTEM.preg_replace('#[^\w/]#', '', trim($systemLocationFile, '/')).$extension;

		$content = $this->write($array, false);
		
		if(false !== $content)
		{
			return file_put_contents($_f, $content) ? true : false;
		}

		return false;
	}
}

