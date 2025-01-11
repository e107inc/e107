<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

/**
* @deprecated: Allows Storage of arrays without use of serialize functions
*
*/
class ArrayData
{


    function __construct()
    {
        // DO Not translate - debug info only. 
        trigger_error('<b>ArrayData class is deprecated.</b> Use e107::serialize() and e107::unserialize instead of WriteArray() and ReadArray().', E_USER_DEPRECATED);

   
       if(E107_DEBUG_LEVEL > 0 || e107::getPref('developer'))
       { 
           $dep = debug_backtrace(false);
		    $log = e107::getLog();

           foreach($dep as $d)
           {
             $log->addDebug("Deprecated ArrayStorage Class called by ".str_replace(e_ROOT,"",$d['file'])." on line ".$d['line']);
           }
		   
	       $log->save('DEPRECATED',E_LOG_NOTICE,'',false, LOG_TO_ROLLING);
		   
           e107::getMessage()->addDebug("Please remove references to <b>arraystorage_class.php</b> and use e107::serialize() and e107::unserialize() instead."); 
       }
    }
	/**
	* Return a string containg exported array data.
	* @deprecated use e107::serialize() instead.
	* @param array $ArrayData array to be stored
	* @param bool $AddSlashes default true, add slashes for db storage, else false
	* @return string
	*/
	function WriteArray($ArrayData, $AddSlashes = true)
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated. Use e107::serialize() instead.', E_USER_DEPRECATED);
		return e107::serialize($ArrayData, $AddSlashes);

	}

	/**
	* Returns an array from stored array data.
	* @deprecated use e107::unserialize() instead.
	* @param string $ArrayData
	* @return array stored data
	*/
	function ReadArray($ArrayData) 
	{
		trigger_error('Method ' . __METHOD__ . ' is deprecated. Use e107::unserialize() instead.', E_USER_DEPRECATED);
		return e107::unserialize($ArrayData);
	}
}

