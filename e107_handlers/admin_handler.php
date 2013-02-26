<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration UI handlers, admin helper functions
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/admin_handler.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/

if (!defined('e107_INIT')) { exit; }

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

if (!function_exists('multiarray_sort')) {
    function multiarray_sort(&$array, $key, $order = 'asc', $natsort = true, $case = true)
    {
        if(!is_array($array)) return $array;

        $order = strtolower($order);
        foreach ($array as $i => $arr)
        {
           $sort_values[$i] = $arr[$key];
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
			return;				
		}
			
        reset ($sort_values);

        while (list ($arr_key, $arr_val) = each ($sort_values))
        {
             $sorted_arr[] = $array[$arr_key];
        }
        return $sorted_arr;
    }
}

