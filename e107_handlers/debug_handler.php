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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/debug_handler.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-26 02:56:37 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

function search_validtheme() {
	$theme_found = 0;
	$th = substr(e_THEME, 0, -1);
	$handle = opendir($th);
	while ($file = readdir($handle)) {
		if (is_dir(e_THEME.$file) && is_readable(e_THEME.$file.'/theme.php')) {
			closedir($handle);
			return $file;
		}
	}
	closedir($handle);
}

class e107_debug {
	
	var $debug_level = 1;
	
	function e107_debug() {
		if (preg_match('/debug=(.*)/', e_MENU, $debug_param)) {
			$this->debug_level = $debug_param[1];
		}
	}
		
	function set_error_reporting() {
		if ($this->debug_level > 1) {
			error_reporting(E_ALL);
		} else {
			error_reporting(E_ERROR | E_WARNING | E_PARSE);
		}
	}
}

class e107_db_debug {
	
	var $sDBdbg;		// DB debug string
	var $aDBbyTable;
	var $aOBMarks;		// Track output buffer level at each time mark
	var $aMarkNotes;	// Other notes can be added and output...
	var $aTimeMarks;	// Overall time markers
	var $curTimeMark;
	
	function e107_db_debug() {
		$this->sDBdbg = '';
		$this->aDBbyTable = array();
		$this->aOBMarks = array();
		$this->aMarkNotes = array();
		$this->aTimeMarks['Start'] = array('What' => 'Start', '%Time'=>0,'%DB Time'=>0,'%DB Count'=>0,'Time' => $timing_start, 'DB Time'=>0,'DB Count'=>0);
		$this->curTimeMark = 'Start';
	}

	function Mark_Time($sMarker) {
		$timeNow = explode(' ',microtime());
		$this->aTimeMarks[$sMarker]=array('What' => $sMarker, '%Time'=>0, '%DB Time'=>0, '%DB Count'=>0,'Time' => $timeNow, 'DB Time'=>0, 'DB Count'=>0);
		$this->aOBMarks[$sMarker] = ob_get_level().'('.ob_get_length().')';
		$this->curTimeMark = $sMarker;
	}

	function Mark_Query($query, $rli, $aTrace) {
		$query = str_replace(","," , ",$query);
		$sQryRes =  is_null($rli) ? mysql_query("EXPLAIN $query") : mysql_query("EXPLAIN $query",$rli);
		$nFields = "";
		if ($sQryRes) { // There's something to explain
			$nFields = mysql_num_fields($sQryRes);
		}
		$sCallingFile=$aTrace[1]['file'];
		$sCallingLine=$aTrace[1]['line'];

		$this->sDBdbg .= "\n<table width=\"100%\" border='1' cellpadding='2' cellspacing='1'>\n";
		$this->sDBdbg .= "<tr><td colspan=\"$nFields\"><b>Query:</b> [{$this->curTimeMark} - $sCallingFile($sCallingLine)]<br/>$query</td></tr>\n";
		if ($sQryRes) {
			$bRowHeaders = FALSE;
			while ($row = @mysql_fetch_assoc($sQryRes)) {
				if (!$bRowHeaders) {
					$bRowHeaders = TRUE;
					$sDBdbg .= "<tr><td><b>".implode("</b></td><td><b>",array_keys($row))."</b></td></tr>\n";
				}
				$this->sDBdbg .= "<tr><td>".implode("&nbsp;</td><td>",array_values($row))."&nbsp;</td></tr>\n";
			}
		}
		return $nFields;
	}

	function Mark_Query_Results($mytime, $curtable, $nFields) {
		$this->aTimeMarks[$this->curTimeMark]['DB Time']+=$mytime;
		$this->aTimeMarks[$this->curTimeMark]['DB Count']++;

		$this->aDBbyTable[$curtable]['Table'] = $curtable;
		$this->aDBbyTable[$curtable]['%DB Time'] = 0; // placeholder
		$this->aDBbyTable[$curtable]['%DB Count'] = 0; // placeholder
		if(array_key_exists('DB Time',$this->aDBbyTable[$curtable])) {
			$this->aDBbyTable[$curtable]['DB Time'] += $mytime;
		} else {
			$this->aDBbyTable[$curtable]['DB Time'] = $mytime;
		}
				
		if(array_key_exists('DB Count',$this->aDBbyTable[$curtable])) {
			$this->aDBbyTable[$curtable]['DB Count'] ++;
		} else {
			$this->aDBbyTable[$curtable]['DB Count'] = 1;
		}

		$mytime = number_format($mytime,4);  //round for local display
		$this->sDBdbg .=  "<tr><td colspan=\"$nFields\"><b>Query time:</b> $mytime</td></tr></table><br />";
	}
	
	function Show_Performance() {
		//
		// Stats by Time Marker
		//
		global $dbq;
		global $db_time;
		global $sql;
		
		$this->Mark_Time('Stop');
		
		$text = '';
		$startTime=$timing_start[0]+$timing_start[1];
		$stopTime=$timing_stop[0]+$timing_stop[1];
		$totTime =$stopTime-$startTime;
		$text .= "\n<table border='1' cellpadding='2' cellspacing='1'>\n";
		$bRowHeaders = FALSE;
		foreach ($this->aTimeMarks as $tMarker) {
			if (!$bRowHeaders) {
				// First time: emit headers
				$bRowHeaders = TRUE;
				$text .= "<tr><td><b>".implode("</b></td><td><b>",array_keys($tMarker))."</b></td><td><b>OB Lev</b></td></tr>\n";
			}
			if ($tMarker['What'] == 'Stop') {
				break;        // We're on the 'stop' mark
			}
			// Convert from start time to delta time, i.e. from now to next entry
			$nextMarker = current($this->aTimeMarks);
			$nextTime = $nextMarker['Time'][0]+$nextMarker['Time'][1];
			$thisTime = $tMarker['Time'][0]+$tMarker['Time'][1];
			$thisDelta=$nextTime-$thisTime;
			$thisWhat = $tMarker['What'];
			$tMarker['Time'] = number_format($thisDelta, 4);
			$tMarker['%Time'] = $totTime ? number_format(100.0*($thisDelta/$totTime),0) : 0;
			$tMarker['%DB Count'] = number_format(100.0*$tMarker['DB Count']/$sql->mySQLquerycount,0);
			$tMarker['%DB Time'] = number_format(100.0*$tMarker['DB Time']/$db_time,0);
			$tMarker['DB Time'] = number_format($tMarker['DB Time'],4);
			$tMarker['OB Lev'] = $aOBMarks[$thisWhat];
			$text .= "<tr><td>".implode("&nbsp;</td><td style='text-align:right'>",array_values($tMarker))."&nbsp;</td></tr>\n";
			if (strlen($this->aMarkNotes[$thisWhat])) {
				$text .= '<tr><td>&nbsp;</td><td colspan="6">';
				$text .= $this->aMarkNotes[$thisWhat].'</td></tr>\n';
			}
		}
		$text .= "\n</table><br/>\n";

		//
		// Stats by Table
		//

		$text .= "\n<table border='1' cellpadding='2' cellspacing='1'>\n";

		$bRowHeaders = FALSE;
		foreach ($this->aDBbyTable as $curTable) {
			if (!$bRowHeaders) {
				$bRowHeaders = TRUE;
				$text .= "<tr><td><b>".implode("</b></td><td><b>",array_keys($curTable))."</b></td></tr>\n";
			}
			$curTable['%DB Count'] = number_format(100.0*$curTable['DB Count']/$sql->mySQLquerycount,0);
			$curTable['%DB Time'] = number_format(100.0*$curTable['DB Time']/$db_time,0);
			$curTable['DB Time'] = number_format($curTable['DB Time'],4);
			$text .= "<tr><td>".implode("&nbsp;</td><td style='text-align:right'>",array_values($curTable))."&nbsp;</td></tr>\n";
		}
		$text .= "\n</table><br/>\n";
		return $text;
	}

}

?>