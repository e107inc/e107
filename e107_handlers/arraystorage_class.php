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
* @DEPRECATED: Allows Storage of arrays without use of serialize functions
*
*/
class ArrayData {


    function __construct()
    {
        // DO Not translate - debug info only. 
        
        $log = e107::getAdminLog();
   
       if(E107_DEBUG_LEVEL > 0 || e107::getPref('developer'))
       { 
           $dep = debug_backtrace(false);
		   
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
	* @DEPRECATED use e107::serialize() instead. 
	* @param array $ArrayData array to be stored
	* @param bool $AddSlashes default true, add slashes for db storage, else false
	* @return string
	*/
	function WriteArray($ArrayData, $AddSlashes = true) {
		if (!is_array($ArrayData)) {
			return false;
		}
		$Array = var_export($ArrayData, true);
		if ($AddSlashes == true) {
			$Array = addslashes($Array);
		}
		return $Array;
	}

	/**
	* Returns an array from stored array data.
	* @DEPRECATED use e107::unserialize() instead. 
	* @param string $ArrayData
	* @return bool|array stored data
	*/
	function ReadArray($ArrayData) 
	{
		if ($ArrayData == ""){
			return false;
		}
		
		// Saftety mechanism for 0.7 -> 0.8 transition. 
		if(substr($ArrayData,0,2)=='a:' || substr($ArrayData,0,2)=='s:')
		{
			$dat = unserialize($ArrayData);
			$ArrayData = $this->WriteArray($dat,FALSE);
		}
		
		
		$data = "";
		$ArrayData = '$data = '.trim($ArrayData).';';
		@eval($ArrayData);
		if (!isset($data) || !is_array($data))
		{
			trigger_error("Bad stored array data - <br /><br />".htmlentities($ArrayData), E_USER_ERROR);
			// return false;
		}
		return $data;
	}
}

