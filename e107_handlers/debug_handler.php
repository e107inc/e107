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
|     $Revision: 1.5 $
|     $Date: 2005-01-27 15:11:56 $
|     $Author: mrpete $
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
		if ($this->debug_level & 32768) { // Debug constants are defined in class2 for now
			error_reporting(E_ALL);
		} else {
			error_reporting(E_ERROR | E_WARNING | E_PARSE);
		}
	}
}

class e107_db_debug {
	
	var $sDBdbg;		// DB debug string (deprecated)
	var $aSQLdetails;       // DB query analysis (in pieces for further analysis)
	var $aDBbyTable;
	var $aOBMarks;		// Track output buffer level at each time mark
	var $aMarkNotes;	// Other notes can be added and output...
	var $aTimeMarks;	// Overall time markers
	var $curTimeMark;
	var $nTimeMarks;     // Provide an array index for time marks. Stablizes 'current' function
	var $aTrafficCounts;    // Overall system traffic counters
	
	function e107_db_debug() {
                global $eTimingStart;
		$this->aDBbyTable = array();
		$this->aSQLdetails = array();
		$this->aOBMarks = array(0 =>'');
		$this->aMarkNotes = array();
		$this->aTimeMarks[0] = array('Index'=>0,'What' => 'Start', '%Time'=>0,'%DB Time'=>0,'%DB Count'=>0,'Time' =>($eTimingStart), 'DB Time'=>0,'DB Count'=>0);
		$this->curTimeMark = 'Start';
		$this->aGoodQueries = array();
		$this->aBadQueries = array();
		$this->nTimeMarks = 0;
		$this->aTraffic = array();
	}

	function Mark_Time($sMarker) { // Should move to traffic_class?
		$nMarks = ++$this->nTimeMarks;
		$timeNow = explode(' ',microtime());
		if (!strlen($sMarker)) {
		    $sMarker = "Mark not set";
		}
		$this->aTimeMarks[$nMarks]=array(
		    'Index'=>($this->nTimeMarks),
		    'What' => $sMarker, 
		    '%Time'=>0, 
		    '%DB Time'=>0, 
		    '%DB Count'=>0,
		    'Time' => $timeNow, 
		    'DB Time'=>0, 
		    'DB Count'=>0);
		$this->aOBMarks[$nMarks] = ob_get_level().'('.ob_get_length().')';
		$this->curTimeMark = $sMarker;

// Add any desired notes to $aMarkNotes[$nMarks]... e.g.
//global $eTimingStart;
//$this->aMarkNotes[$nMarks] .= "verify start: ".$eTimingStart."<br/>";
 	}

	function Mark_Query($query, $rli, $aTrace) {
		global $sql;
		$query = str_replace(","," , ",$query);
		$sQryRes =  is_null($rli) ? mysql_query("EXPLAIN $query") : mysql_query("EXPLAIN $query",$rli);
		$nFields = "";
		if ($sQryRes) { // There's something to explain
			$nFields = mysql_num_fields($sQryRes);
		}
		$sCallingFile=$aTrace[1]['file'];
		$sCallingLine=$aTrace[1]['line'];
                $iQuery=$sql->db_QueryCount();
                $this->aSQLdetails[$iQuery]['marker']= $this->curTimeMark;
                $this->aSQLdetails[$iQuery]['caller']= "$sCallingFile($sCallingLine)";
                $this->aSQLdetails[$iQuery]['query']= $query;
                $this->aSQLdetails[$iQuery]['ok']= $sQryRes ? TRUE : FALSE;
                $this->aSQLdetails[$iQuery]['error']= $sQryRes ? '' : mysql_error();
                $this->aSQLdetails[$iQuery]['nFields']= $nFields;
		if ($sQryRes) {
			$bRowHeaders = FALSE;
			while ($row = @mysql_fetch_assoc($sQryRes)) {
				if (!$bRowHeaders) {
				    $bRowHeaders = TRUE;
                                    $this->aSQLdetails[$iQuery]['explain'] =
					"<tr><td class='forumheader3'><b>".
			                implode("</b></td><td class='forumheader3'><b>",array_keys($row)).
			                "</b></td></tr>\n";
				}
				$this->aSQLdetails[$iQuery]['explain'] .= "<tr><td class='forumheader3'>".
				  implode("&nbsp;</td><td class='forumheader3'>",array_values($row)).
				  "&nbsp;</td></tr>\n";
			}
		} else {
		    $this->aSQLdetails[$iQuery]['explain'] = '';
                }

		return $nFields;
	}

	function Mark_Query_Results($mytime, $curtable, $nFields) {
	        global $sql;
		$this->aTimeMarks[$this->nTimeMarks]['DB Time']+=$mytime;
		$this->aTimeMarks[$this->nTimeMarks]['DB Count']++;

		if(array_key_exists($curtable,$this->aDBbyTable)) {
		    $this->aDBbyTable[$curtable]['DB Time'] += $mytime;
		    $this->aDBbyTable[$curtable]['DB Count'] ++;
                } else {
		    $this->aDBbyTable[$curtable]['Table'] = $curtable;
		    $this->aDBbyTable[$curtable]['%DB Time'] = 0; // placeholder
		    $this->aDBbyTable[$curtable]['%DB Count'] = 0; // placeholder
		    $this->aDBbyTable[$curtable]['DB Time'] = $mytime;
		    $this->aDBbyTable[$curtable]['DB Count'] = 1;
		}

                $iQuery=$sql->db_QueryCount();
                $this->aSQLdetails[$iQuery]['time'] = $mytime;
	}
	
	function Mark_Fetch_Results($mytime, $curtable) {
	}

	function Show_SQL_Details() {
	    global $sql;
	    //
	    // Show stats from aSQLdetails array
	    //
	    $text = '';
	    $nQueries = $sql->db_QueryCount();
	    if (!$nQueries) return $text;
	    
	    //
	    // ALWAYS summarize query errors
	    //
            $badCount = 0;
            $okCount = 0;
            foreach ($this->aSQLdetails as $cQuery) {
                if ($cQuery['ok']) {
                    $okCount++;
                } else {
                    $badCount++;
                }
            }
            
                
            if ($badCount) {
    	        $text .= "\n<table class='fborder'>\n";
                $text .="<tr><td class='fcaption' colspan='2'><b>$badCount Query Errors!</b></td></tr>\n";
                $text .="<tr><td class='fcaption'><b>Index</b></td><td class='fcaption'><b>Query</b></td></tr>\n";
                foreach ($this->aSQLdetails as $idx => $cQuery) {
    	            if (!$cQuery['ok']) {
                        $text .= "<tr><td class='forumheader3' style='text-align:right'>{$idx}&nbsp;</td>
    	       	        <td class='forumheader3'>".$cQuery['query']."</td></tr>\n";
    	            }
                }
    	        $text .= "\n</table><br/>\n";
    	    }
    
	    //
	    // Optionally list good queries
	    //
            if ($okCount && E107_DBG_SQLQUERIES) {
                $text .= "\n<table class='fborder'>\n";
                $text .="<tr><td class='fcaption' colspan='3'><b>{$okCount[TRUE]} Good Queries</b></td></tr>\n";
                $text .="<tr><td class='fcaption'><b>Index</b></td><td class='fcaption'><b>Qtime</b></td><td class='fcaption'><b>Query</b></td></tr>\n";
                foreach ($this->aSQLdetails as $idx => $cQuery) {
	            if ($cQuery['ok']) {
	       	        $text .= "<tr><td class='forumheader3' style='text-align:right'>{$idx}&nbsp;</td>
	       	        <td class='forumheader3' style='text-align:right'>".
	       	        number_format($cQuery['time']*1000.0,4)."&nbsp;</td><td class='forumheader3'>".
                        $cQuery['query'].'<br/>['.
                        $cQuery['marker']." - ".$cQuery['caller']."]</td></tr>\n";
	            }
                }
	        $text .= "\n</table><br/>\n";
	    }
    
	    
	    //
	    // Optionally list query details
	    //
	    if (E107_DBG_SQLDETAILS) {

                foreach ($this->aSQLdetails as $idx => $cQuery) {
		    $text .= "\n<table class='fborder' style='width: 100%;'>\n";
		    $text .= "<tr><td class='forumheader3' colspan='".$cQuery['nFields']."'><b>".
		                $idx.") Query:</b> [".
		                $cQuery['marker']." - ".
		                $cQuery['caller']."]<br/>".
		                $cQuery['query']."</td></tr>\n";
                    $text .=  $cQuery['explain'];
                    if (strlen($cQuery['error'])) {
                        $text .= "<tr><td class='forumheader3' ><b>Error in query:</b></td></tr>\n<tr><td class='forumheader3'>".
		            $cQuery['error'].
		            "</td></tr>\n";
		    }
    		    $text .=  "<tr><td class='forumheader3'  colspan='".
    		        $cQuery['nFields'].
    		        "'><b>Query time:</b> ".
    		        number_format($cQuery['time']*1000.0,4).
    		        ' msec.</td></tr></table><br />'."\n";
    	        }
            }
            
            return $text;
        }
	
	function Show_Performance() {
		//
		// Stats by Time Marker
		//
		global $db_time;
		global $sql;
		global $eTimingStart,$eTimingStop, $eTraffic;
		
		$this->Mark_Time('Stop');
		if (!E107_DBG_TIMEDETAILS) return '';
		
		$text = '';
		$totTime =$eTraffic->TimeDelta( $eTimingStart, $eTimingStop );
		$text .= "\n<table class='fborder'>\n";
		$bRowHeaders = FALSE;
		reset( $this->aTimeMarks );
		$aSum=$this->aTimeMarks[0];
		$aSum['Index']=''; 
		$aSum['What']='Total';
		$aSum['Time']=0; $aSum['DB Count']=0; $aSum['DB Time']=0;
		while (list($tKey, $tMarker) = each($this->aTimeMarks)) {
			if (!$bRowHeaders) {
				// First time: emit headers
				$bRowHeaders = TRUE;
				$text .= "<tr><td class='fcaption' style='text-align:right'><b>".
				        implode("</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>",array_keys($tMarker)).
				        "</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>OB Lev&nbsp;</b></td></tr>\n";
			}
			if ($tMarker['What'] == 'Stop') {
				break;        // We're on the 'stop' mark
			}
			// Convert from start time to delta time, i.e. from now to next entry
			$nextMarker = current($this->aTimeMarks);
                        $aNextT = $nextMarker['Time'];
                        $aThisT = $tMarker['Time'];
			$nextTime = $aNextT[0]+$aNextT[1];
			$thisTime = $aThisT[0]+$aThisT[1];
			$thisDelta=$nextTime-$thisTime;
			$thisWhat = $tMarker['What'];
			$aSum['Time']+=$thisDelta;
                        $aSum['DB Time'] += $tMarker['DB Time'];
                        $aSum['DB Count'] += $tMarker['DB Count'];
			$tMarker['Time'] = number_format($thisDelta, 4);
			$tMarker['%Time'] = $totTime ? number_format(100.0*($thisDelta/$totTime),0) : 0;
			$tMarker['%DB Count'] = number_format(100.0*$tMarker['DB Count']/$sql->db_QueryCount(),0);
			$tMarker['%DB Time'] = $db_time ? number_format(100.0*$tMarker['DB Time']/$db_time,0) : 0;
			$tMarker['DB Time'] = number_format($tMarker['DB Time'],4);
			$tMarker['OB Lev'] = $this->aOBMarks[$tKey];
			$text .= "<tr><td class='forumheader3' >".implode("&nbsp;</td><td class='forumheader3'  style='text-align:right'>",array_values($tMarker))."&nbsp;</td></tr>\n";
			if (isset($this->aMarkNotes[$tKey])) {
				$text .= "<tr><td class='forumheader3' >&nbsp;</td><td class='forumheader3' colspan='4'>";
				$text .= $this->aMarkNotes[$tKey]."</td></tr>\n";
			}
                        
		}
		$aSum['%Time']=$totTime ? number_format(100.0*($aSum['Time']/$totTime),0) : 0;
		$aSum['%DB Time']=$db_time ? number_format(100.0*($aSum['DB Time']/$db_time),0) : 0;
		$aSum['%DB Count']=($sql->db_QueryCount()) ? number_format(100.0*($aSum['DB Count']/($sql->db_QueryCount())),0) : 0;
		$aSum['Time'] = number_format($aSum['Time'], 4);
		$aSum['DB Time'] = number_format($aSum['DB Time'], 4);

		$text .= "<tr><td class='fcaption'><b>".implode("</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>",$aSum)."</b>&nbsp;</td><td class='fcaption'>&nbsp;</td></tr>\n";
		$text .= "\n</table><br/>\n";

		//
		// Stats by Table
		//

		$text .= "\n<table class='fborder'>\n";

		$bRowHeaders = FALSE;
		foreach ($this->aDBbyTable as $curTable) {
			if (!$bRowHeaders) {
				$bRowHeaders = TRUE;
				$text .= "<tr><td class='fcaption'><b>".implode("</b></td><td class='fcaption'><b>",array_keys($curTable))."</b></td></tr>\n";
			}
			$curTable['%DB Count'] = number_format(100.0*$curTable['DB Count']/$sql->db_QueryCount(),0);
			$curTable['%DB Time'] = number_format(100.0*$curTable['DB Time']/$db_time,0);
			$curTable['DB Time'] = number_format($curTable['DB Time'],4);
			$text .= "<tr><td class='forumheader3'>".implode("&nbsp;</td><td class='forumheader3' style='text-align:right'>",array_values($curTable))."&nbsp;</td></tr>\n";
		}
		$text .= "\n</table><br/>\n";

		return $text;
	}

}

?>