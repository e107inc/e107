<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/date_handler.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-03-09 10:53:58 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_date.php");

class convert
{

	function convert_date($datestamp, $mode = "long") {
		/*
		# Date convert
		# - parameter #1:  string $datestamp, unix stamp
		# - parameter #2:  string $mode, date format, default long
		# - return         parsed text
		# - scope          public
		*/
		global $pref;

		$datestamp += TIMEOFFSET;

		if ($mode == "long") {
			return strftime($pref['longdate'], $datestamp);
		} else if ($mode == "short") {
			return strftime($pref['shortdate'], $datestamp);
		} else {
			return strftime($pref['forumdate'], $datestamp);
		}
	}

	function computeLapse($older_date, $newer_date = false) 
	{ 
		$chunks = array(
			array(60 * 60 * 24 * 365 , LANDT_01),
			array(60 * 60 * 24 * 30 , LANDT_02),
			array(60 * 60 * 24 * 7, LANDT_03),
			array(60 * 60 * 24 , LANDT_04),
			array(60 * 60 , LANDT_05),
			array(60 , LANDT_06),
		);
		$newer_date = ($newer_date == false) ? (time()) : $newer_date;
		$since = $newer_date - $older_date;
		for ($i = 0, $j = count($chunks); $i < $j; $i++)
		{
			$seconds = $chunks[$i][0];
			$name = $chunks[$i][1];
			if (($count = floor($since / $seconds)) != 0)
			{
				break;
			}
		}

		$output = ($count == 1) ? '1 '.$name : "$count {$name}".LANDT_PLURAL;

		if ($i + 1 < $j)
		{
			$seconds2 = $chunks[$i + 1][0];
			$name2 = $chunks[$i + 1][1];
	  
			if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0)
			{
				$output .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 {$name2}".LANDT_PLURAL;
			}
		}
	return $output;
	}
}