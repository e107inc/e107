<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/db_debug_class.php,v $
|     $Revision: 1.13 $
|     $Date: 2009-09-04 14:35:01 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

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
	var $scbbcodes = array();
	var $scbcount;
	var $deprecated_funcs = array();
	var $aLog = array();	// Generalized debug log (only seen during debug)

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
		'DB Count' => 0,
		'Memory'   => 0
		);
		
		register_shutdown_function('e107_debug_shutdown');
	}

//
// Add your new Show function here so it will display in debug output!
//
	function Show_All() 
	{
		global $eTraffic;

		$this->ShowIf('Debug Log', $this->Show_Log());
		$this->ShowIf('Traffic Counters', $eTraffic->Display());
		$this->ShowIf('Time Analysis', $this->Show_Performance());
		$this->ShowIf('SQL Analysis', $this->Show_SQL_Details());
		$this->ShowIf('Shortcodes / BBCode',$this->Show_SC_BB());
		$this->ShowIf('Paths', $this->Show_PATH());
		$this->ShowIf('Deprecated Function Usage', $this->Show_DEPRECATED());
		$this->ShowIf('Included Files', $this->Show_Includes());
	}
	
	function ShowIf($title,$str)
	{
		global $ns,$style;
		$style='debug';
		
		if (!isset($ns)) {
			echo "Why did ns go away?<br />";
			$ns = new e107table;
		}
		
		if (strlen($str)) {
			$ns->tablerender($title, $str);
		}
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
		'DB Count' => 0,
		'Memory'   => ((function_exists("memory_get_usage"))? memory_get_usage() : 0)
		);
	
		$this->aOBMarks[$nMarks]=ob_get_level().'('.ob_get_length().')';
		$this->curTimeMark=$sMarker;
	
		// Add any desired notes to $aMarkNotes[$nMarks]... e.g.
		//global $eTimingStart;
		//$this->aMarkNotes[$nMarks] .= "verify start: ".$eTimingStart."<br />";
	}


	function Mark_Query($query, $rli, $origQryRes, $aTrace, $mytime, $curtable) 
	{
	  global $sql;

	  // Explain the query, if possible...
	  list($qtype,$args) = explode(" ", ltrim($query), 2);

	  $nFields=0;
	  $bExplained = FALSE;
	  $ExplainText = '';
	  // Note the subtle bracket in the second comparison! Also, strcasecmp() returns zero on match
	  if (!strcasecmp($qtype,'SELECT') || !strcasecmp($qtype,'(SELECT'))
	  {	// It's a SELECT statement - explain it
		// $rli should always be set by caller
		$sQryRes = (is_null($rli) ? mysql_query("EXPLAIN {$query}") :  mysql_query("EXPLAIN {$query}", $rli));
		if ($sQryRes) 
		{ // There's something to explain
		  $nFields = mysql_num_fields($sQryRes);
		  $bExplained = TRUE;
		}
	  } 
	  else 
	  {	// Don't run 'EXPLAIN' on other queries
		$sQryRes = $origQryRes;			// Return from original query could be TRUE or a link resource if success
	  }

		// Record Basic query info
		$sCallingFile	= varset($aTrace[1]['file']);
		$sCallingLine	= varset($aTrace[1]['line']);

		$t 				= &$this->aSQLdetails[$sql->db_QueryCount()];
		$t['marker']	= $this->curTimeMark;
		$t['caller']	= "$sCallingFile($sCallingLine)";
		$t['query']		= $query;
		$t['ok']		= $sQryRes ? TRUE : FALSE;
		$t['error']		= $sQryRes ? '' : mysql_error();
		$t['nFields']	= $nFields;
		$t['time']		= $mytime;

		if ($bExplained) 
		{
		  $bRowHeaders=FALSE;
		  while ($row = @mysql_fetch_assoc($sQryRes))
		  {
			if (!$bRowHeaders) 
			{
			  $bRowHeaders=TRUE;
			  $t['explain']="<tr><td class='forumheader3'><b>".implode("</b></td><td class='forumheader3'><b>", array_keys($row))."</b></td></tr>\n";
			}
			$t['explain'] .= "<tr><td class='forumheader3'>".implode("&nbsp;</td><td class='forumheader3'>", array_values($row))."&nbsp;</td></tr>\n";
		  }
		} 
		else 
		{
		  $t['explain'] = $ExplainText;
		}

		$this->aTimeMarks[$this->nTimeMarks]['DB Time'] += $mytime;
		$this->aTimeMarks[$this->nTimeMarks]['DB Count']++;

		if (array_key_exists($curtable, $this->aDBbyTable)) 
		{
			$this->aDBbyTable[$curtable]['DB Time'] += $mytime;
			$this->aDBbyTable[$curtable]['DB Count']++;
		} 
		else 
		{
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
		if (!E107_DBG_SQLQUERIES && !E107_DBG_SQLDETAILS)
		{
			return FALSE;
		}


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
			$text .= "<tr><td class='fcaption'><b>Index</b></td><td class='fcaption'><b>Query / Error</b></td></tr>\n";

			foreach ($this->aSQLdetails as $idx => $cQuery) {
				if (!$cQuery['ok']) {
					$text .= "<tr><td class='forumheader3' rowspan='2' style='text-align:right'>{$idx}&nbsp;</td>
    	       	        <td class='forumheader3'>".$cQuery['query']."</td></tr>\n<tr><td class='forumheader3'>".$cQuery['error']."</td></tr>\n";
				}
			}
			$text .= "\n</table><br />\n";
		}

		//
		// Optionally list good queries
		//
		if ($okCount && E107_DBG_SQLDETAILS) {
			$text .= "\n<table class='fborder'>\n";
			$text .= "<tr><td class='fcaption' colspan='3'><b>{$okCount[TRUE]} Good Queries</b></td></tr>\n";
			$text .= "<tr><td class='fcaption'><b>Index</b></td><td class='fcaption'><b>Qtime</b></td><td class='fcaption'><b>Query</b></td></tr>\n
				 <tr><td class='fcaption'>&nbsp;</td><td class='fcaption'><b>(msec)</b></td><td class='fcaption'>&nbsp;</td></tr>\n
				 ";

			foreach ($this->aSQLdetails as $idx => $cQuery) {
				if ($cQuery['ok']) {
					$text .= "<tr><td class='forumheader3' style='text-align:right'>{$idx}&nbsp;</td>
	       	        <td class='forumheader3' style='text-align:right'>".number_format($cQuery['time'] * 1000.0, 4)."&nbsp;</td><td class='forumheader3'>".$cQuery['query'].'<br />['.$cQuery['marker']." - ".$cQuery['caller']."]</td></tr>\n";
				}
			}
			$text .= "\n</table><br />\n";
		}


		//
		// Optionally list query details
		//
		if (E107_DBG_SQLDETAILS) {
			foreach ($this->aSQLdetails as $idx => $cQuery) {
				$text .= "\n<table class='fborder' style='width: 100%;'>\n";
				$text .= "<tr><td class='forumheader3' colspan='".$cQuery['nFields']."'><b>".$idx.") Query:</b> [".$cQuery['marker']." - ".$cQuery['caller']."]<br />".$cQuery['query']."</td></tr>\n";
				if (isset($cQuery['explain'])) {
					$text .= $cQuery['explain'];
				}
				if (strlen($cQuery['error'])) {
					$text .= "<tr><td class='forumheader3' ><b>Error in query:</b></td></tr>\n<tr><td class='forumheader3'>".$cQuery['error']."</td></tr>\n";
				}

				$text .= "<tr><td class='forumheader3'  colspan='".$cQuery['nFields']."'><b>Query time:</b> ".number_format($cQuery['time'] * 1000.0, 4).' (ms)</td></tr></table><br />'."\n";
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

		$totTime=$eTraffic->TimeDelta($eTimingStart, $eTimingStop);
		$text = "\n<table class='fborder'>\n";
		$bRowHeaders=FALSE;
		reset($this->aTimeMarks);
		$aSum=$this->aTimeMarks[0]; // create a template from the 'real' array
		$aSum['Index']='';
		$aSum['What']='Total';
		$aSum['Time']=0;
		$aSum['DB Time']=0;
		$aSum['DB Count']=0;
		$aSum['Memory']='';

		while (list($tKey, $tMarker) = each($this->aTimeMarks)) {
			if (!$bRowHeaders) {
				// First time: emit headers
				$bRowHeaders=TRUE;
				$text .= "<tr><td class='fcaption' style='text-align:right'><b>".implode("</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>", array_keys($tMarker))."</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>OB Lev&nbsp;</b></td></tr>\n";
				$aUnits = $tMarker;
				foreach ($aUnits as $key=>$val) {
					switch ($key) {
						case 'DB Time':
						case 'Time':
						$aUnits[$key] = '(msec)';
						break;
						default:
						$aUnits[$key] = '';
						break;
					}
				}
				$aUnits['OB Lev'] = 'lev(buf bytes)';
				$aUnits['Memory'] = '(kb)';
				$text .= "<tr><td class='fcaption' style='text-align:right'><b>".implode("</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>", $aUnits)."</b>&nbsp;</td></tr>\n";
			}

			$tMem = $tMarker['Memory'];
			$tMarker['Memory'] = ($tMem ? number_format($tMem/1024.0, 1) : '?'); // display if known
			if ($tMarker['What'] == 'Stop') {
				$tMarker['Time']='&nbsp;';
				$tMarker['%Time']='&nbsp;';
				$tMarker['%DB Count']='&nbsp;';
				$tMarker['%DB Time']='&nbsp;';
				$tMarker['DB Time']='&nbsp;';
				$tMarker['OB Lev']=$this->aOBMarks[$tKey];
				$tMarker['DB Count']='&nbsp;';
				} else {
				// Convert from start time to delta time, i.e. from now to next entry
				$nextMarker=current($this->aTimeMarks);
				$aNextT=$nextMarker['Time'];
				$aThisT=$tMarker['Time'];
	
				$thisDelta=$eTraffic->TimeDelta($aThisT, $aNextT);
				$aSum['Time'] += $thisDelta;
				$aSum['DB Time'] += $tMarker['DB Time'];
				$aSum['DB Count'] += $tMarker['DB Count'];
				$tMarker['Time']=number_format($thisDelta*1000.0, 1);
				$tMarker['%Time']=$totTime ? number_format(100.0 * ($thisDelta / $totTime), 0) : 0;
				$tMarker['%DB Count']=number_format(100.0 * $tMarker['DB Count'] / $sql->db_QueryCount(), 0);
				$tMarker['%DB Time']=$db_time ? number_format(100.0 * $tMarker['DB Time'] / $db_time, 0) : 0;
				$tMarker['DB Time']=number_format($tMarker['DB Time']*1000.0, 1);
				
				$tMarker['OB Lev']=$this->aOBMarks[$tKey];
			}

			$text .= "<tr><td class='forumheader3' >".implode("&nbsp;</td><td class='forumheader3'  style='text-align:right'>", array_values($tMarker))."&nbsp;</td></tr>\n";

			if (isset($this->aMarkNotes[$tKey])) {
				$text .= "<tr><td class='forumheader3' >&nbsp;</td><td class='forumheader3' colspan='4'>";
				$text .= $this->aMarkNotes[$tKey]."</td></tr>\n";
			}
			if ($tMarker['What'] == 'Stop') break;
		}

		$aSum['%Time']=$totTime ? number_format(100.0 * ($aSum['Time'] / $totTime), 0) : 0;
		$aSum['%DB Time']=$db_time ? number_format(100.0 * ($aSum['DB Time'] / $db_time), 0) : 0;
		$aSum['%DB Count']=($sql->db_QueryCount()) ? number_format(100.0 * ($aSum['DB Count'] / ($sql->db_QueryCount())), 0) : 0;
		$aSum['Time']=number_format($aSum['Time']*1000.0, 1);
		$aSum['DB Time']=number_format($aSum['DB Time']*1000.0, 1);

		$text .= "<tr><td class='fcaption'><b>".implode("</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>", $aSum)."</b>&nbsp;</td><td class='fcaption'>&nbsp;</td></tr>\n";
		$text .= "\n</table><br />\n";

		//
		// Stats by Table
		//

		$text .= "\n<table class='fborder'>\n";

		$bRowHeaders=FALSE;
		$aSum=$this->aDBbyTable['core']; // create a template from the 'real' array
		$aSum['Table']='Total';
		$aSum['%DB Count']=0;
		$aSum['%DB Time']=0;
		$aSum['DB Time']=0;
		$aSum['DB Count']=0;

		foreach ($this->aDBbyTable as $curTable) {
			if (!$bRowHeaders) {
				$bRowHeaders=TRUE;
				$text .= "<tr><td class='fcaption'><b>".implode("</b></td><td class='fcaption'><b>", array_keys($curTable))."</b></td></tr>\n";
				$aUnits = $curTable;
				foreach ($aUnits as $key=>$val) {
					switch ($key) {
						case 'DB Time':
						$aUnits[$key] = '(msec)';
						break;
						default:
						$aUnits[$key] = '';
						break;
					}
				}
				$text .= "<tr><td class='fcaption' style='text-align:right'><b>".implode("</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>", $aUnits)."</b>&nbsp;</td></tr>\n";
			}

			$aSum['DB Time'] += $curTable['DB Time'];
			$aSum['DB Count'] += $curTable['DB Count'];
			$curTable['%DB Count']=number_format(100.0 * $curTable['DB Count'] / $sql->db_QueryCount(), 0);
			$curTable['%DB Time']=number_format(100.0 * $curTable['DB Time'] / $db_time, 0);
			$curTable['DB Time']=number_format($curTable['DB Time']*1000.0, 1);
			$text .= "<tr><td class='forumheader3'>".implode("&nbsp;</td><td class='forumheader3' style='text-align:right'>", array_values($curTable))."&nbsp;</td></tr>\n";
		}

		$aSum['%DB Time']=$db_time ? number_format(100.0 * ($aSum['DB Time'] / $db_time), 0) : 0;
		$aSum['%DB Count']=($sql->db_QueryCount()) ? number_format(100.0 * ($aSum['DB Count'] / ($sql->db_QueryCount())), 0) : 0;
		$aSum['DB Time']=number_format($aSum['DB Time']*1000.0, 1);
		$text .= "<tr><td class='fcaption'>".implode("&nbsp;</td><td class='fcaption' style='text-align:right'>", array_values($aSum))."&nbsp;</td></tr>\n";
		$text .= "\n</table><br />\n";

		return $text;
	}

	function logDeprecated(){

		$back_trace = debug_backtrace();

		print_r($back_trace);

		$this->deprecated_funcs[] =	array (
		'func'	=> (isset($back_trace[1]['type']) && ($back_trace[1]['type'] == '::' || $back_trace[1]['type'] == '->') ? $back_trace[1]['class'].$back_trace[1]['type'].$back_trace[1]['function'] : $back_trace[1]['function']),
		'file'	=> $back_trace[1]['file'],
		'line'	=> $back_trace[1]['line']
		);

	}

	function logCode($type, $code, $parm, $postID)
	{
		if (!E107_DBG_BBSC)
		{
			return FALSE;
		}
		$this -> scbbcodes[$this -> scbcount]['type'] = $type;
		$this -> scbbcodes[$this -> scbcount]['code'] = $code;
		$this -> scbbcodes[$this -> scbcount]['parm'] = htmlentities($parm);
		$this -> scbbcodes[$this -> scbcount]['postID'] = $postID;
		$this -> scbcount ++;
	}

	function Show_SC_BB()
	{
		if (!E107_DBG_BBSC)
		{
			return FALSE;
		}


		$text = "<table class='fborder' style='width: 100%'>
			<tr><td class='fcaption' colspan='4'><b>Shortcode / BBCode</b></td></tr>
			<tr>
			<td class='fcaption' style='width: 10%;'>Type</td>
			<td class='fcaption' style='width: 10%;'>Code</td>
			<td class='fcaption' style='width: 10%;'>Parm</td>
			<td class='fcaption' style='width: 10%;'>Post ID</td>
			</tr>\n";

 		foreach($this -> scbbcodes as $codes)
		{
			$text .= "<tr>
				<td class='forumheader3' style='width: 10%;'>".($codes['type'] == 1 ? "BBCode" : "Shortcode")."</td>
				<td class='forumheader3' style='width: 10%;'>".(isset($codes['code']) ? $codes['code'] : "&nbsp;")."</td>
				<td class='forumheader3' style='width: 10%;'>".($codes['parm'] ? $codes['parm'] : "&nbsp;")."</td>
				<td class='forumheader3' style='width: 10%;'>".($codes['postID'] ? $codes['postID'] : "&nbsp;")."</td>
				</tr>\n";
		}
		$text .= "</table>";
		return $text;
	}

	function Show_PATH()
	{
		if (!E107_DBG_PATH)
		{
			return FALSE;
		}
		global $e107;
		$text = "<table class='fborder' style='width: 100%'>
			<tr><td class='fcaption' colspan='4'><b>Paths</b></td></tr>
			<tr>
			<td class='forumheader3'>\n";

		$text .= "e_HTTP: '".e_HTTP."'<br />";
		$text .= "e_BASE: '".e_BASE."'<br />";
		$text .= "\$_SERVER['PHP_SELF']: '".$_SERVER['PHP_SELF']."'<br />";
		$text .= "\$_SERVER['DOCUMENT_ROOT']: '".$_SERVER['DOCUMENT_ROOT']."'<br />";
		$text .= "\$_SERVER['HTTP_HOST']: '".$_SERVER['HTTP_HOST']."'<br />";


  	  	$text .= "<pre>";
        $text .= htmlspecialchars(print_r($e107,TRUE));
	  	$text .= "</pre>";

		$text .= "</td></tr></table>";
		return $text;
	}


	function Show_DEPRECATED(){
		if (!E107_DBG_DEPRECATED){
			return FALSE;
		} else {
			$text = "<table class='fborder' style='width: 100%'>
			<tr><td class='fcaption' colspan='4'><b>The following deprecated functions were used:</b></td></tr>
			<tr>
			<td class='fcaption' style='width: 10%;'>Function</td>
			<td class='fcaption' style='width: 10%;'>File</td>
			<td class='fcaption' style='width: 10%;'>Line</td>
			</tr>\n";

			foreach($this->deprecated_funcs as $funcs)
			{
				$text .= "<tr>
				<td class='forumheader3' style='width: 10%;'>{$funcs['func']}()</td>
				<td class='forumheader3' style='width: 10%;'>{$funcs['file']}</td>
				<td class='forumheader3' style='width: 10%;'>{$funcs['line']}</td>
				</tr>\n";
			}
			$text .= "</table>";
			return $text;
		}
	}
	
//
// Simple debug-level 'console' log
// Record a "nice" debug message with
// $db_debug->log("message");
//
	function log($message,$TraceLev=1)
	{
		if (!E107_DBG_BASIC){
			return FALSE;
		}
		if ($TraceLev)
		{
			$bt = debug_backtrace();
			$this->aLog[] =	array (
				'Message'   => $message,
				'Function'	=> (isset($bt[$TraceLev]['type']) && ($bt[$TraceLev]['type'] == '::' || $bt[$TraceLev]['type'] == '->') ? $bt[$TraceLev]['class'].$bt[$TraceLev]['type'].$bt[$TraceLev]['function'].'()' : $bt[$TraceLev]['function']).'()',
				'File'	=> $bt[$TraceLev]['file'],
				'Line'	=> $bt[$TraceLev]['line']
			);
		} else {
			$this->aLog[] =	array (
				'Message'   => $message,
				'Function' => '',
				'File' => '',
				'Line' => ''
			);
		}
	}

	function Show_Log(){
		if (!E107_DBG_BASIC || !count($this->aLog)){
			return FALSE;
		}
		//
		// Dump the debug log
		//

		$text .= "\n<table class='fborder'>\n";

		$bRowHeaders=FALSE;
		
		foreach ($this->aLog as $curLog) 
		{
			if (!$bRowHeaders) {
				$bRowHeaders=TRUE;
				$text .= "<tr class='fcaption'><td><b>".implode("</b></td><td><b>", array_keys($curLog))."</b></td></tr>\n";
			}

			$text .= "<tr class='forumheader3'><td>".implode("&nbsp;</td><td>", array_values($curLog))."&nbsp;</td></tr>\n";
		}

		$text .= "</table><br />\n";

		return $text;
	}
	
	function Show_Includes()
	{
		if (!E107_DBG_INCLUDES) return FALSE;

		$aIncList = get_included_files();
		$text = "<table class='fborder'>\n";
		$text .= "<tr><td class='forumheader3'>".
							implode("&nbsp;</td></tr>\n<tr><td class='forumheader3'>", $aIncList).
							"&nbsp;</td></tr>\n";
		$text .= "</table>\n";
		return $text;
	}
}

//
// Helper functions (not part of the class)
//
function e107_debug_shutdown()
{
global $error_handler,$e107_Clean_Exit,$In_e107_Footer,$ADMIN_DIRECTORY;
	if (isset($e107_Clean_Exit)) return;

	if (!isset($In_e107_Footer))
	{
		if (defset('ADMIN_AREA'))
		{
			$filewanted=realpath(dirname(__FILE__)).'/../'.$ADMIN_DIRECTORY.'footer.php';
			require_once($filewanted);
		} else if (defset('USER_AREA'))
		{
			$filewanted=realpath(dirname(__FILE__)).'/../'.FOOTERF;
			require_once($filewanted);
		}
	}
// 
// Error while in the footer, or during startup, or during above processing
//
	if (isset($e107_Clean_Exit)) return; // We've now sent a footer...
	
//	echo isset($In_e107_Footer) ? "In footer" : "In startup".'<br />';

	while (ob_get_level() > 0) {
		ob_end_flush();
	}

	if (isset($error_handler) )
	{
		if($error_handler->return_errors())
		{
	  		echo "<br /><div 'e107_debug php_err'><h3>PHP Errors:</h3><br />".$error_handler->return_errors()."</div>\n";
			echo "</body></html>";
		}
	}
	else
	{
   		echo "<b>e107 Shutdown while no error_handler available!</b>";
			echo "</body></html>";       
	}

}

?>