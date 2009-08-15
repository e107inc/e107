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
|     $Revision: 1.12 $
|     $Date: 2009-08-15 11:54:30 $
|     $Author: marj_nl_fr $
|
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_date.php");

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

	function computeLapse($older_date, $newer_date = FALSE, $mode = FALSE, $show_secs = TRUE, $format = 'long') 
	{	/*
		$mode = TRUE :: return array
		$mode = FALSE :: return string
		*/

		if($format == 'short')
		{
			$sec = LANDT_09;
			$secs = LANDT_09s;
			$min = LANDT_08;
			$mins = LANDT_08s;
		}
		else
		{
			$sec = LANDT_07;
			$secs = LANDT_07s;
			$min = LANDT_06;
			$mins = LANDT_06s;
		}
/*
  If we want an absolutely accurate result, main problems arise from the varying numbers of days in a month.
  If we go over a month boundary, then we need to add days to end of start month, plus days in 'end' month
  If start day > end day, we cross a month boundary. Calculate last day of start date. Otherwise we can just do a simple difference.
*/
		$newer_date = ($newer_date == FALSE ? (time()) : $newer_date);
		if($older_date>$newer_date)
		{  // Just in case the wrong way round
		  $tmp=$newer_date; 
		  $newer_date=$older_date; 
		  $older_date=$tmp; 
		}
		$new_date = getdate($newer_date);
		$old_date = getdate($older_date);
		$result   = array();
		$outputArray = array();

		$params   = array(
					  6 => array('seconds',60, $sec, $secs),
					  5 => array('minutes',60, $min, $mins),
					  4 => array('hours',24, LANDT_05, LANDT_05s),
					  3 => array('mday', -1, LANDT_04, LANDT_04s),
					  2 => array('',-3, LANDT_03, LANDT_03s),
					  1 => array('mon',12, LANDT_02, LANDT_02s),
					  0 => array('year', -2, LANDT_01,LANDT_01s)
					);

		$cy = 0;
		foreach ($params as $parkey => $parval)
		{
		  if ($parkey == 2)
		  {
		    $result['2'] = floor($result['3']/7);
			$result['3'] = fmod($result['3'],7);
		  }
		  else
		  {
		    $tmp = $new_date[$parval[0]] - $old_date[$parval[0]] - $cy;
			$scy = $cy;
		    $cy = 0;
		    if ($tmp < 0)
		    {
		      switch ($parval[1])
			  {
			    case -1 :    // Wrapround on months - special treatment
			      $tempdate = getdate(mktime(0,0,0,$old_date['mon']+1,1,$old_date['year']) - 1);  // Last day of month
				  $tmp = $tempdate['mday'] - $old_date['mday'] + $new_date['mday'] - $scy;
				  $cy = 1;
			      break;
			    case -2 :		// Year wraparound - shouldn't happen
				case -3 : 		// Week processing - this shouldn't happen either
				  echo "Code bug!<br />";
			      break;
			    default :
		          $cy = 1;
				  $tmp += $parval[1];
			}
		  }
		  $result[$parkey] = $tmp;
		  }
		}

		// Generate output array, add text
		for ($i = 0; $i < ($show_secs ? 7 : 6); $i++)
		{
		  if (($i > 4) || ($result[$i] != 0))
		  {  // Only show non-zero values, except always show minutes/seconds
		    $outputArray[] = $result[$i]." ".($result[$i] == 1 ? $params[$i][2] : $params[$i][3]);
		  }
		}
		return ($mode ? $outputArray : implode(", ", $outputArray));
	}
}
?>