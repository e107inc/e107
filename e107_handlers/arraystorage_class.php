<?php
	
/**
* Allows Storage of arrays without use of serialize functions
*
*/
class ArrayData {
	 
	/**
	* Return a string containg exported array data.
	*
	* @param array $ArrayData array to be stored
	* @param bool $AddSlashes default true, add slashes for db storage, else false
	* @return string
	*/
	function WriteArray(&$ArrayData, $AddSlashes = true) {
		if (!is_array($ArrayData)) {
			echo 'Input should be an array';
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
	*
	* @param string $ArrayData
	* @return array stored data
	*/
	function ReadArray(&$ArrayData) {
		$ArrayData = '$data = '.trim($ArrayData).';';
		eval('$data = '.$ArrayData.';');
		if (!is_array($data)) {
			echo 'Input should be stored array data';
			return false;
		}
		return $data;
	}
}
	
?>