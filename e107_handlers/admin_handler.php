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
 * $Revision: 1.36 $
 * $Date: 2009-11-24 16:32:01 $
 * $Author: secretr $
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
        else
        {
             $case ? natsort($sort_values) : natcasesort($sort_values);
             if($order != 'asc') $sort_values = array_reverse($sort_values, true);
        }
        reset ($sort_values);

        while (list ($arr_key, $arr_val) = each ($sort_values))
        {
             $sorted_arr[] = $array[$arr_key];
        }
        return $sorted_arr;
    }
}

/**
 * Experiment
 * Most basic & performance wise solution for admin icons override
 */
class e_admin_icons
{
	/**
	 * @var string icons absolute URL path
	 */
	protected $path;
	
	/**
	 * @var string icons relative server path
	 */
	protected $relpath;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 */
	function __construct()
	{
		//XXX maybe we should use admintheme pref instead THEME here?
		if(is_readable(THEME.'icons/admin/'))
		{
			$this->path = THEME_ABS.'icons/admin/';
			$this->relpath = THEME.'icons/admin/';
		}
		else
		{
			$this->path = e_IMAGE_ABS.'/admin_images/';
			$this->relpath = e_IMAGE.'/admin_images/';
		}
	}
	
	/**
	 * Get icon absolute path (url, without domain)
	 * 
	 * @param string $name without size and extension e.g. 'edit'
	 * @param integer size pixel , default 16
	 * @param string $extension without leading dot, default 'png'
	 * @return string icon url without domain
	 */
	public function url($name, $size = 16, $extension = 'png')
	{
		return $this->path.$name.'.'.$extension;		
	}
	
	/**
	 * Get image tag of an icon
	 * 
	 * @param string $name without size and extension e.g. 'edit'
	 * @param integer $size default 16
	 * @param string $class default empty
	 * @param string $alt default empty
	 * @param string $extension default 'png'
	 * @return string img tag
	 */
	public function tag($name, $size = 16, $class='', $alt = '', $extension = 'png')
	{
		$_class = 'icon';
		if($size)
		{
			$name .= '_'.$size;
			$_class .= ' S'.$size;
		}
		if($class)
		{
			$_class .= ' '.$class;
		}
		$src = $this->url($name, $extension);
		
		return '<img src="'.$src.'" alt="'.$alt.'" class="'.$_class.'" />';
	}	
	
	/**
	 * Get icon relative server path
	 * 
	 * @param string $name without size and extension e.g. 'edit'
	 * @param integer size pixel , default 16
	 * @param string $extension without leading dot, default 'png'
	 * @return string icon relative server path
	 */
	public function path($name, $size = 16, $extension = 'png')
	{
		return $this->relpath.$name.'.'.$extension;
	}
}

/**
 * Convenient proxy to e_admin_icons::url()
 * Get icon absolute path (url, without domain)
 * Example:
 * <code>
 * echo _I('edit');
 * // If icon path is overloaded by current admin theme:
 * // '/e107_themes/current_theme/icons/admin/edit_16.png'
 * // else
 * // '/e107_images/admin_images/edit_16.png'
 * </code>
 * 
 * @param string $name without size and extension e.g. 'edit'
 * @param integer size pixel , default 16
 * @param string $extension without leading dot, default 'png'
 * @return string icon url without domain
 */
function _I($name, $size = 16, $extension = 'png')
{
	return e107::getSingleton('e_admin_icons')->url($name, $size, $extension);
}

/**
 * Convenient proxy to e_admin_icons::tag()
 * Get image tag of an icon
 * Example: <code>echo _ITAG('edit');</code>
 * @see _I()
 * @param string $name without size and extension e.g. 'edit'
 * @param integer $size default 16
 * @param string $class default empty
 * @param string $alt default empty
 * @param string $extension default 'png'
 * @return string img tag
 */
function _ITAG($name, $size = 16, $class = '', $alt = '', $extension = 'png')
{
	return e107::getSingleton('e_admin_icons')->tag($name, $size, $class, $alt, $extension);
}

/**
 * Convenient proxy to e_admin_icons::path()
 * Get icon relative server path
 * <code>
 * echo _IPATH('edit');
 * // If icon path is overloaded by current admin theme:
 * // '../e107_themes/current_theme/icons/admin/edit_16.png'
 * // else
 * // '../e107_images/admin_images/edit_16.png'
 * </code>
 * 
 * @param string $name without size and extension e.g. 'edit'
 * @param integer size pixel , default 16
 * @param string $extension without leading dot, default 'png'
 * @return string icon relative server path
 */
function _IPATH($name, $size = 16, $extension = 'png')
{
	return e107::getSingleton('e_admin_icons')->path($name, $size, $extension);
}
