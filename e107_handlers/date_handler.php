<?php
/*
 * e107 website system
 * 
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://gnu.org).
 * 
 * $Source: /cvs_backup/e107_0.8/e107_handlers/date_handler.php,v $
 * $Revision$
 * $Date$
 * $Author$
 * 
*/
if (!defined('e107_INIT')) { exit; }

include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_date.php");

class convert
{
	/**
	 * Convert datestamp to human readable date.
	 * System time offset is considered.
	 * 
	 * @param integer $datestamp unix stamp
	 * @param string $mask [optional] long|short|forum or any strftime() valid string
	 * 
	 * @return string parsed date
	 */
	function convert_date($datestamp, $mask = '') {
		if(empty($mask))
		{
			$mask = 'long';
		}
		
		switch($mask)
		{
			case 'long':
				$mask = e107::getPref('longdate');
			break;
			
			case 'short':
				$mask = e107::getPref('shortdate');
			break;

			case 'input': //New - use inputdate as mask; FIXME - add inputdate to Date site prefs 
				$mask = e107::getPref('inputdate', '%d/%m/%Y %H:%M:%S');
			break;

			case 'forum': // DEPRECATED - temporary here from BC reasons only
			default: 
				//BC - old 'forum' call
				if(strpos($mask, '%') === FALSE)
				{
					$mask = e107::getPref('forumdate');
				}
			break;
		}
		
		$datestamp += TIMEOFFSET;
		return strftime($mask, $datestamp);
	}



	
	/**
	 * Convert date string back to integer (unix timestamp)
	 * NOTE: after some tests, strptime (compat mode) is adding +1 sec. after parsing to time, investigate!
	 * 
	 * @param object $date_string
	 * @param object $mask [optional]
	 * @return integer
	 */
	function toTime($date_string, $mask = 'input')
	{
		switch($mask)
		{
			case 'long':
				$mask = e107::getPref('longdate');
			break;
			
			case 'short':
				$mask = e107::getPref('shortdate');
			break;

			case 'input': //New - use inputdate as mask; FIXME - add inputdate to Date site prefs 
				$mask = e107::getPref('inputdate', '%d/%m/%Y %H:%M:%S');
			break;
		}
		
		// see php compat handler
		$tdata = strptime($date_string, $mask);
		if(empty($tdata))
		{
			return null;
		}
		$unxTimestamp = mktime( 
			$tdata['tm_hour'], 
			$tdata['tm_min'], 
			$tdata['tm_sec'], 
			$tdata['tm_mon'] + 1, 
			$tdata['tm_mday'], 
			$tdata['tm_year'] + 1900 
		); 
		
		//var_dump($tdata, $date_string, $this->convert_date($unxTimestamp, $mask), $unxTimestamp);
		return $unxTimestamp;
	}






	/**
	 * Tolerant date/time input routine - doesn't force use of specific delimiters, and can sometimes allow no delimiters at all
	 * The format string defines the critical day/month/year order.
	 * As examples, with suitable format specifiers all of the following will be interpreted into valid (and sensible) dates/times:
	 *  09122003 153045		-> 9-12-03 at 15:30:45 (requires dmy or mdy format specifier)
	 *	20031209 12:30:32	-> 9-12-03 at 12:30:32 (requires ymd specifier)
	 *	091203 1530			-> 9-12-09 at 15:30:00
	 *  9/12/3 12			-> 9-12-09 at 12:00:00
	 *	6-3/4 15-45:27		-> 6-3-04 at 15:45:27
	 *
	 * @param string $input - usually date/time string with numeric values for relevant fields, and almost any separator. e.g. dd-mm-yy hh:mm
	 *							Date and time must be separated by one or more spaces. In times, minutes and seconds are optional, and default to zero
	 *							One special value is allowed - 'now'
	 * @param string $decode - one of 'date', 'time', 'datetime', 'timedate'
	 * @param string $format - sets field order for dates. Valid strings are dmy, mdy, ymd. Add suffix 'z' to return UTC/GMT
	 * @param boolean $endDay - if TRUE, and no time entered, includes a time of 23:59:59 in the entered date
	 *
	 * @return integer time stamp.  returns zero on any error
	 */
	public function decodeDateTime($input, $decode = 'date', $format = 'dmy', $endDay = FALSE)
	{
		if ($input == 'now') return time();		// Current time   TODO: option to return UTC or local time here
		$useLocale = TRUE;
		if (substr($format,-1,1) == 'z')
		{
			$useLocale = FALSE;
			$format = substr($format,0,-1);		// Remove local disable string
		}
		switch ($decode)
		{
			case 'date' :
				$timeString = '';
				$dateString = $input;
				break;
			case 'time' :
				$timeString = $input;
				$dateString = '';
				break;
			case 'datetime' :		// Date then time, separated by space
				$input = str_replace('  ',' ', $input);
				list($dateString, $timeString) = explode(' ',$input,2);
				break;
			case 'timedate' :		// Time then date, separated by space
				$input = str_replace('  ',' ', $input);
				list($timeString, $dateString) = explode(' ',$input,2);
				break;
			default :
				return 0;
		}
		$timeString = trim($timeString);
		$dateString = trim($dateString);
		$dateVals = array (1 => 0, 2 => 0, 3 => 0);		// Preset date in case 
		$timeVals = array (1 => 0, 2 => 0, 3 => 0);		// Preset time in case 
		if ($dateString)
		{
			if (is_numeric($dateString))
			{
				if (strlen($dateString) == 6)
				{	// Probably fixed format numeric without separators
					$dateVals = array(1 => substr($dateString,0,2), 2 => substr($dateString,2,2), 3 => substr($dateString,-2));
				}
				elseif (strlen($dateString) == 8)
				{	// Trickier - year may be first or last!
					if ($format == 'ymd')
					{
						$dateVals = array(1 => substr($dateString,0,4), 2 => substr($dateString,4,2), 3 => substr($dateString,-2));
					}
					else
					{
						$dateVals = array(1 => substr($dateString,0,2), 2 => substr($dateString,2,2), 3 => substr($dateString,-4));
					}
				}
			}
			else
			{  // Assume standard 'nn-nn-nn', 'nnnn-nn-nn' or 'nn-nn-nnnn' type format
				if (!preg_match('#(\d{1,4})\D(\d{1,2})\D(\d{1,4})#', $dateString, $dateVals))
				{
					return 0;			// Can't decode date
				}
			}
		}
		if ($timeString)
		{
			if (is_numeric($timeString))
			{
				if (strlen($timeString) == 6)
				{	// Assume hhmmss
					$timeVals = array(1 => substr($timeString,0,2), 2 => substr($timeString,2,2), 3 => substr($timeString,-2));
				}
				elseif (strlen($timeString) == 4)
				{	// Assume hhmm
					$timeVals = array(1 => substr($timeString,0,2), 2 => substr($timeString,-2), 3 => 0);
				}
				else
				{	// Hope its just hours!
					if ($timeString < 24)
					{
						$timeVals[1] = $timeString;
					}
				}
			}
			else
			{
				preg_match('#(\d{1,2})(?:\D(\d{1,2})){0,1}(?:\D(\d{1,2})){0,1}#', $timeString, $timeVals);
			}
		}
		elseif ($endDay)
		{
			$timeVals = array (1 => 23, 2 => 59, 3 => 59);		// Last second of day
		}
		// Got all the values now - the rest is simple!
		switch ($format)
		{
			case 'dmy' :
				$month = $dateVals[2]; $day = $dateVals[1]; $year = $dateVals[3]; break;
			case 'mdy' :
				$month = $dateVals[1]; $day = $dateVals[2]; $year = $dateVals[3]; break;
			case 'ymd' :
				$month = $dateVals[2]; $day = $dateVals[3]; $year = $dateVals[1]; break;
			default :
				echo "Unsupported format string: {$format}<br />";
				return 0;
		}
		if ($useLocale)
		{
			return mktime($timeVals[1], $timeVals[2], $timeVals[3], $month, $day, $year);
		}
		return gmmktime($timeVals[1], $timeVals[2], $timeVals[3], $month, $day, $year);
	}



	/**
	 * Calculate difference between two dates for display in terms of years/months/weeks....
	 * 
	 * @param integer $older_date - time stamp
	 * @param integer|boolean $newer_date - time stamp.  Defaults to current time if FALSE
	 * @param boolean $mode -if TRUE, return value is an array. Otherwise return value is a string
	 * @param boolean $show_secs
	 * @param string $format - controls display format. 'short' misses off year. 'long' includes everything
	 * @return array|string according to $mode, array or string detailing the time difference
	 */
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
		$newer_date = ($newer_date === FALSE ? (time()) : $newer_date);
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