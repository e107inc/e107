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
	 |     $Source: /cvs_backup/e107_0.7/e107_handlers/db_debug_class.php,v $
	 |     $Revision: 1.3 $
	 |     $Date: 2005-01-29 17:08:38 $
	 |     $Author: mrpete $
	 +----------------------------------------------------------------------------+
	 */

	 class e107_db_debug {
		 var $aSQLdetails = array();     // DB query analysis (in pieces for further analysis)
		 var $aDBbyTable = array();
		 var $aOBMarks = array(0 => ''); // Track output buffer level at each time mark
		 var $aMarkNotes = array();      // Other notes can be added and output...
		 var $aTimeMarks = array();      // Overall time markers
		 var $curTimeMark = 'Start';
		 var $nTimeMarks = 0;            // Provide an array index for time marks. Stablizes 'current' function
		 var $aGoodQueries = array();
		 var $aBadQueries = array();

		 function e107_db_debug() {
			 global $eTimingStart;

			 $this->aTimeMarks[0]=array(
				  'Index' => 0,
				  'What' => 'Start',
				  '%Time' => 0,
				  '%DB Time' => 0,
				  '%DB Count' => 0,
				  'Time' => ($eTimingStart),
				  'DB Time' => 0,
				  'DB Count' => 0
				  );
		 }

		 function Mark_Time($sMarker) { // Should move to traffic_class?
			 $timeNow=microtime();
			 $nMarks=++$this->nTimeMarks;

			 if (!strlen($sMarker)) {
				 $sMarker = "Mark not set";
			 }

			 $this->aTimeMarks[$nMarks]=array(
				  'Index' => ($this->nTimeMarks),
				  'What' => $sMarker,
				  '%Time' => 0,
				  '%DB Time' => 0,
				  '%DB Count' => 0,
				  'Time' => $timeNow,
				  'DB Time' => 0,
				  'DB Count' => 0
				  );

			 $this->aOBMarks[$nMarks]=ob_get_level().'('.ob_get_length().')';
			 $this->curTimeMark=$sMarker;

		 // Add any desired notes to $aMarkNotes[$nMarks]... e.g.
		 //global $eTimingStart;
		 //$this->aMarkNotes[$nMarks] .= "verify start: ".$eTimingStart."<br/>";
		 }

		function Mark_Query($query, $rli, $origQryRes, $aTrace, $mytime, $curtable) {
			global $sql;  
			
			// Explain the query, if possible...
			list($qtype,$args) = explode(" ", ltrim($query), 2);

			$nFields=0;
			if (!strcasecmp($qtype,'SELECT')) { 
			 	$query=str_replace(",", " , ", $query);
			 	$sQryRes=is_null($rli) ? mysql_query("EXPLAIN $query") : mysql_query("EXPLAIN $query", $rli);

			 	if ($sQryRes) { // There's something to explain
					$nFields = mysql_num_fields($sQryRes);
			 	}
         } else {
         	$sQryRes = $origQryRes; 
         	$nFields=0;
         }
         
         // Record Basic query info
			$sCallingFile=$aTrace[1]['file'];
			$sCallingLine=$aTrace[1]['line'];

			$t = &$this->aSQLdetails[$sql->db_QueryCount()];
			$t['marker']=$this->curTimeMark;
			$t['caller']="$sCallingFile($sCallingLine)";
			$t['query']=$query;
			$t['ok']=$sQryRes ? TRUE : FALSE;
			$t['error']=$sQryRes ? '' : mysql_error();
			$t['nFields']=$nFields;
			$t['time']=$mytime;

			 if ($sQryRes) {
				 $bRowHeaders=FALSE;
				 while ($row = @mysql_fetch_assoc($sQryRes)) {
					 if (!$bRowHeaders) {
						 $bRowHeaders=TRUE;
						 $t['explain']="<tr><td class='forumheader3'><b>".implode("</b></td><td class='forumheader3'><b>", array_keys($row))."</b></td></tr>\n";
					 }

					 $t['explain'] .= "<tr><td class='forumheader3'>".implode("&nbsp;</td><td class='forumheader3'>", array_values($row))."&nbsp;</td></tr>\n";
				 }
			 } else {
				 $t['explain'] = '';
			 }
          
			 $this->aTimeMarks[$this->nTimeMarks]['DB Time'] += $mytime;
			 $this->aTimeMarks[$this->nTimeMarks]['DB Count']++;
          
			 if (array_key_exists($curtable, $this->aDBbyTable)) {
				 $this->aDBbyTable[$curtable]['DB Time'] += $mytime;
				 $this->aDBbyTable[$curtable]['DB Count']++;
			 } else {
				 $this->aDBbyTable[$curtable]['Table']=$curtable;
				 $this->aDBbyTable[$curtable]['%DB Time']=0;  // placeholder
				 $this->aDBbyTable[$curtable]['%DB Count']=0; // placeholder
				 $this->aDBbyTable[$curtable]['DB Time']=$mytime;
				 $this->aDBbyTable[$curtable]['DB Count']=1;
			 }
		 }

		 function Show_SQL_Details() {
			 global $sql;
			 //
			 // Show stats from aSQLdetails array
			 //
			 $text='';
			 $nQueries=$sql->db_QueryCount();

			 if (!$nQueries) return $text;

			 //
			 // ALWAYS summarize query errors
			 //
			 $badCount=0;
			 $okCount=0;

			 foreach ($this->aSQLdetails as $cQuery) {
				 if ($cQuery['ok']) {
					 $okCount++;
				 } else {
					 $badCount++;
				 }
			 }

			 if ($badCount) {
				 $text .= "\n<table class='fborder'>\n";
				 $text .= "<tr><td class='fcaption' colspan='2'><b>$badCount Query Errors!</b></td></tr>\n";
				 $text .= "<tr><td class='fcaption'><b>Index</b></td><td class='fcaption'><b>Query</b></td></tr>\n";

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
				 $text .= "<tr><td class='fcaption' colspan='3'><b>{$okCount[TRUE]} Good Queries</b></td></tr>\n";
				 $text .= "<tr><td class='fcaption'><b>Index</b></td><td class='fcaption'><b>Qtime</b></td><td class='fcaption'><b>Query</b></td></tr>\n";

				 foreach ($this->aSQLdetails as $idx => $cQuery) {
					 if ($cQuery['ok']) {
						 $text .= "<tr><td class='forumheader3' style='text-align:right'>{$idx}&nbsp;</td>
	       	        <td class='forumheader3' style='text-align:right'>".number_format($cQuery['time'] * 1000.0, 4)."&nbsp;</td><td class='forumheader3'>".$cQuery['query'].'<br/>['.$cQuery['marker']." - ".$cQuery['caller']."]</td></tr>\n";
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
					 $text .= "<tr><td class='forumheader3' colspan='".$cQuery['nFields']."'><b>".$idx.") Query:</b> [".$cQuery['marker']." - ".$cQuery['caller']."]<br/>".$cQuery['query']."</td></tr>\n";
					 if (isset($cQuery['explain'])) {
					 	$text .= $cQuery['explain'];
					}
					 if (strlen($cQuery['error'])) {
						 $text .= "<tr><td class='forumheader3' ><b>Error in query:</b></td></tr>\n<tr><td class='forumheader3'>".$cQuery['error']."</td></tr>\n";
					 }

					 $text .= "<tr><td class='forumheader3'  colspan='".$cQuery['nFields']."'><b>Query time:</b> ".number_format($cQuery['time'] * 1000.0, 4).' msec.</td></tr></table><br />'."\n";
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
			 global $eTimingStart, $eTimingStop, $eTraffic;

			 $this->Mark_Time('Stop');

			 if (!E107_DBG_TIMEDETAILS) return '';

			 $text='';
			 $totTime=$eTraffic->TimeDelta($eTimingStart, $eTimingStop);
			 $text .= "\n<table class='fborder'>\n";
			 $bRowHeaders=FALSE;
			 reset($this->aTimeMarks);
			 $aSum=$this->aTimeMarks[0]; // create a template from the 'real' array
			 $aSum['Index']='';
			 $aSum['What']='Total';
			 $aSum['Time']=0;
			 $aSum['DB Count']=0;
			 $aSum['DB Time']=0;

			 while (list($tKey, $tMarker) = each($this->aTimeMarks)) {
				 if (!$bRowHeaders) {
					 // First time: emit headers
					 $bRowHeaders=TRUE;
					 $text .= "<tr><td class='fcaption' style='text-align:right'><b>".implode("</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>", array_keys($tMarker))."</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>OB Lev&nbsp;</b></td></tr>\n";
				 }

				 if ($tMarker['What'] == 'Stop') {
					 break; // We're on the 'stop' mark
				 }

				 // Convert from start time to delta time, i.e. from now to next entry
				 $nextMarker=current($this->aTimeMarks);
				 $aNextT=$nextMarker['Time'];
				 $aThisT=$tMarker['Time'];
				 
				 $thisDelta=$eTraffic->TimeDelta($aThisT, $aNextT);
				 $thisWhat=$tMarker['What'];
				 $aSum['Time'] += $thisDelta;
				 $aSum['DB Time'] += $tMarker['DB Time'];
				 $aSum['DB Count'] += $tMarker['DB Count'];
				 $tMarker['Time']=number_format($thisDelta*1000.0, 1);
				 $tMarker['%Time']=$totTime ? number_format(100.0 * ($thisDelta / $totTime), 0) : 0;
				 $tMarker['%DB Count']=number_format(100.0 * $tMarker['DB Count'] / $sql->db_QueryCount(), 0);
				 $tMarker['%DB Time']=$db_time ? number_format(100.0 * $tMarker['DB Time'] / $db_time, 0) : 0;
				 $tMarker['DB Time']=number_format($tMarker['DB Time']*1000.0, 1);
				 $tMarker['OB Lev']=$this->aOBMarks[$tKey];
				 $text .= "<tr><td class='forumheader3' >".implode("&nbsp;</td><td class='forumheader3'  style='text-align:right'>", array_values($tMarker))."&nbsp;</td></tr>\n";

				 if (isset($this->aMarkNotes[$tKey])) {
					 $text .= "<tr><td class='forumheader3' >&nbsp;</td><td class='forumheader3' colspan='4'>";
					 $text .= $this->aMarkNotes[$tKey]."</td></tr>\n";
				 }
			 }

			 $aSum['%Time']=$totTime ? number_format(100.0 * ($aSum['Time'] / $totTime), 0) : 0;
			 $aSum['%DB Time']=$db_time ? number_format(100.0 * ($aSum['DB Time'] / $db_time), 0) : 0;
			 $aSum['%DB Count']=($sql->db_QueryCount()) ? number_format(100.0 * ($aSum['DB Count'] / ($sql->db_QueryCount())), 0) : 0;
			 $aSum['Time']=number_format($aSum['Time']*1000.0, 1);
			 $aSum['DB Time']=number_format($aSum['DB Time']*1000.0, 1);

			 $text .= "<tr><td class='fcaption'><b>".implode("</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>", $aSum)."</b>&nbsp;</td><td class='fcaption'>&nbsp;</td></tr>\n";
			 $text .= "\n</table><br/>\n";

			 //
			 // Stats by Table
			 //

			 $text .= "\n<table class='fborder'>\n";

			 $bRowHeaders=FALSE;

			 foreach ($this->aDBbyTable as $curTable) {
				 if (!$bRowHeaders) {
					 $bRowHeaders=TRUE;
					 $text .= "<tr><td class='fcaption'><b>".implode("</b></td><td class='fcaption'><b>", array_keys($curTable))."</b></td></tr>\n";
				 }

				 $curTable['%DB Count']=number_format(100.0 * $curTable['DB Count'] / $sql->db_QueryCount(), 0);
				 $curTable['%DB Time']=number_format(100.0 * $curTable['DB Time'] / $db_time, 0);
				 $curTable['DB Time']=number_format($curTable['DB Time'], 4);
				 $text .= "<tr><td class='forumheader3'>".implode("&nbsp;</td><td class='forumheader3' style='text-align:right'>", array_values($curTable))."&nbsp;</td></tr>\n";
			 }

			 $text .= "\n</table><br/>\n";

			 return $text;
		 }
	 }
?>
