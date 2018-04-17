<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc (e107.org)
|     http://e107.org
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/db_debug_class.php,v $
|     $Revision$
|     $Date$
|     $Author$
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
	var $aIncList = array(); // Included files
	
	function __construct()
    {
       
        
        
    }

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
		$this->ShowIf('Debug Log', $this->Show_Log());
		$this->ShowIf('Traffic Counters', e107::getSingleton('e107_traffic')->Display());
		$this->ShowIf('Time Analysis', $this->Show_Performance());
		$this->ShowIf('SQL Analysis', $this->Show_SQL_Details());
		$this->ShowIf('Shortcodes / BBCode',$this->Show_SC_BB());
		$this->ShowIf('Paths', $this->Show_PATH());
		$this->ShowIf('Deprecated Function Usage', $this->Show_DEPRECATED());

        if(E107_DBG_INCLUDES)
        {
            $this->aIncList = get_included_files(); 
        }

		$this->ShowIf('Included Files: '.count($this->aIncList), $this->Show_Includes());
	}
	
	function ShowIf($title,$str)
	{

		if(!empty($str))
		{
			//e107::getRender()->setStyle('debug');
			echo "<h4>".$title."</h4>";
			echo $str;
			//e107::getRender()->tablerender($title, $str);
		}
	}

	function Mark_Time($sMarker) { // Should move to traffic_class?
		$timeNow=microtime();
		$nMarks=++$this->nTimeMarks;
	
		if (!strlen($sMarker)) {
			$sMarker = "Mark not set";
		}

		$srch = array('[',']');
		$repl = array("<small>","</small>");

		$this->aTimeMarks[$nMarks]=array(
		'Index' => ($this->nTimeMarks),
		'What' => str_replace($srch,$repl,$sMarker),
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


	/**
	 * @param $query
	 * @param $rli
	 * @param $origQryRes
	 * @param $aTrace
	 * @param $mytime
	 * @param $curtable
	 */
	function Mark_Query($query, $rli, $origQryRes, $aTrace, $mytime, $curtable)
	{
	//  global $sql;
		$sql = e107::getDb( $rli);

		 // Explain the query, if possible...
		list($qtype,$args) = explode(" ", ltrim($query), 2); 

		$nFields=0;
		$bExplained = FALSE;
		$ExplainText = '';
	  // Note the subtle bracket in the second comparison! Also, strcasecmp() returns zero on match
		if (!strcasecmp($qtype,'SELECT') || !strcasecmp($qtype,'(SELECT'))
		{	// It's a SELECT statement - explain it
		// $rli should always be set by caller
	//	$sQryRes = (is_null($rli) ? mysql_query("EXPLAIN {$query}") :  mysql_query("EXPLAIN {$query}", $rli));
	
		$sQryRes = $sql->gen("EXPLAIN {$query}");
		
			if ($sQryRes)  // There's something to explain
			{ 
				//$nFields = mysql_num_fields($sQryRes);
				$nFields = $sql->columnCount($sQryRes); // mysql_num_fields($sQryRes);
				$bExplained = TRUE;
			}
		} 
		else 
		{	// Don't run 'EXPLAIN' on other queries
			$sQryRes = $origQryRes;			// Return from original query could be TRUE or a link resource if success
		}

		// Record Basic query info
		$sCallingFile	= varset($aTrace[2]['file']);
		$sCallingLine	= varset($aTrace[2]['line']);

		$t 				= &$this->aSQLdetails[$sql->db_QueryCount()];
		$t['marker']	= $this->curTimeMark;
		$t['caller']	= "$sCallingFile($sCallingLine)";
		$t['query']		= $query;
		$t['ok']		= ($sQryRes !==false) ? true : false;
		$t['error']		= $sQryRes ? '' : $sql->getLastErrorText(); // mysql_error();
		$t['nFields']	= $nFields;
		$t['time']		= $mytime;

		if ($bExplained) 
		{
			$bRowHeaders=FALSE;
		//  while ($row = @mysql_fetch_assoc($sQryRes))
			while ($row = $sql->fetch())
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
			$this->aDBbyTable[$curtable]['Table']		= $curtable;
			$this->aDBbyTable[$curtable]['%DB Time']	= 0;  // placeholder
			$this->aDBbyTable[$curtable]['%DB Count']	= 0; // placeholder
			$this->aDBbyTable[$curtable]['DB Time']		= $mytime;
			$this->aDBbyTable[$curtable]['DB Count']	= 1;
		}
	}



	function Show_SQL_Details($force=false) {
		global $sql;
		//
		// Show stats from aSQLdetails array
		//
		if (!E107_DBG_SQLQUERIES && !E107_DBG_SQLDETAILS  && ($force === false))
		{
			return false;
		}


		$text='';
		$nQueries=$sql->db_QueryCount();

		if (!$nQueries) return $text;

		//
		// ALWAYS summarize query errors
		//
		$badCount=0;
		$okCount=0;

		foreach ($this->aSQLdetails as $cQuery) 
		{
			if ($cQuery['ok']==1) 
			{
				$okCount++;
			} 
			else 
			{
				$badCount++;
			}
		}

		if ($badCount) {
			$text .= "\n<table class='fborder table table-striped table-bordered'>\n";
			$text .= "<tr><td class='fcaption' colspan='2'><b>$badCount Query Errors!</b></td></tr>\n";
			$text .= "<tr><td class='fcaption'><b>Index</b></td><td class='fcaption'><b>Query / Error</b></td></tr>\n";

			foreach ($this->aSQLdetails as $idx => $cQuery) {
				if (!$cQuery['ok']) 
				{
					$text .= "<tr><td class='forumheader3' rowspan='2' style='text-align:right'>{$idx}&nbsp;</td>
    	       	        <td class='forumheader3'>".$cQuery['query']."</td></tr>\n<tr><td class='forumheader3'>".$cQuery['error']."</td></tr>\n";
				}
			}
			$text .= "\n</table><br />\n";
		}

		//
		// Optionally list good queries
		//
		
		if ($okCount && E107_DBG_SQLDETAILS)
		{
			$text .= "\n<table class='fborder table table-striped table-bordered'>\n";
			$text .= "<tr><td class='fcaption' colspan='3'><b>".$this->countLabel($okCount)." Good Queries</b></td></tr>\n";
			$text .= "<tr><td class='fcaption'><b>Index</b></td><td class='fcaption'><b>Qtime</b></td><td class='fcaption'><b>Query</b></td></tr>\n
				 <tr><td class='fcaption'>&nbsp;</td><td class='fcaption'><b>(msec)</b></td><td class='fcaption'>&nbsp;</td></tr>\n
				 ";

			$count = 0;
			foreach ($this->aSQLdetails as $idx => $cQuery) 
			{
				if($count > 500)
				{
					$text .= "<tr class='danger'><td colspan='6'><b>Too many queries. Ending... </b></td></tr>"; // NO LAN - debug only.
					break;
				}


				if ($cQuery['ok'])
				{
					$text .= "<tr><td class='forumheader3' style='text-align:right'>{$idx}&nbsp;</td>
	       	        <td class='forumheader3' style='text-align:right'>".number_format($cQuery['time'] * 1000.0, 4)."&nbsp;</td>
	       	        <td class='forumheader3'>".$cQuery['query'].'<br />['.$cQuery['marker']." - ".$cQuery['caller']."]</td></tr>\n";

					$count++;
				}
			}



				$text .= "\n</table><br />\n";
		}


		//
		// Optionally list query details
		//
		if (E107_DBG_SQLDETAILS)
		{
			$count = 0;
			foreach ($this->aSQLdetails as $idx => $cQuery)
			 {
				$text .= "\n<table class='fborder table table-striped table-bordered' style='width: 100%;'>\n";
				$text .= "<tr><td class='forumheader3' colspan='".$cQuery['nFields']."'><b>".$idx.") Query:</b> [".$cQuery['marker']." - ".$cQuery['caller']."]<br />".$cQuery['query']."</td></tr>\n";
				if (isset($cQuery['explain'])) {
					$text .= $cQuery['explain'];
				}
				if (strlen($cQuery['error'])) {
					$text .= "<tr><td class='forumheader3' ><b>Error in query:</b></td></tr>\n<tr><td class='forumheader3'>".$cQuery['error']."</td></tr>\n";
				}

				$text .= "<tr><td class='forumheader3'  colspan='".$cQuery['nFields']."'><b>Query time:</b> ".number_format($cQuery['time'] * 1000.0, 4).' (ms)</td></tr>';
			
				$text .= '</table><br />'."\n";

				if($count > 500)
				{
					$text .= "<div class='alert alert-danger text-center'>Too many queries. Ending...</div>"; // NO LAN - debug only.
					break;
				}


				$count++;
			}
		}

		return $text;
	}
	
	function countLabel($amount)
	{
		if($amount < 30)
		{
			$inc = 'label-success';
		}
		elseif($amount < 50)
		{
			$inc = 'label-warning';	
		}	
		elseif($amount > 49)
		{
			$inc = 'label-danger label-important';		
		}
		
		return "<span class='label ".$inc."'>".$amount."</span>";
	}


	function save($log)
	{
		e107::getMessage()->addDebug("Saving a log");

		$titles = array_keys($this->aTimeMarks[0]);

		$text = implode("\t\t\t",$titles)."\n\n";

		foreach($this->aTimeMarks as $item)
		{
			$item['What'] = str_pad($item['What'],50," ",STR_PAD_RIGHT);
			$text .= implode("\t\t\t",$item)."\n";
		}

		file_put_contents($log, $text, FILE_APPEND);

	}


	private function highlight($label, $value=0,$threshold=0)
	{

		if($value > $threshold)
		{
			return  "<span class='label label-danger'>".$label."</span>";
		}

		return $label;

	}


	function Show_Performance()
	{
			//
			// Stats by Time Marker
			//
			global $db_time;
			global $sql;
			global $eTimingStart, $eTimingStop;

			$this->Mark_Time('Stop');

			if(!E107_DBG_TIMEDETAILS)
			{
				return '';
			}

			$totTime = e107::getSingleton('e107_traffic')->TimeDelta($eTimingStart, $eTimingStop);

			$text = "\n<table class='fborder table table-striped table-condensed'>\n";
			$bRowHeaders = false;
			reset($this->aTimeMarks);
			$aSum = $this->aTimeMarks[0]; // create a template from the 'real' array

			$aSum['Index'] = '';
			$aSum['What'] = 'Total';
			$aSum['Time'] = 0;
			$aSum['DB Time'] = 0;
			$aSum['DB Count'] = 0;
			$aSum['Memory'] = 0;

			// Calculate Memory Usage per entry.
			$prevMem = 0;

			foreach($this->aTimeMarks as $k=>$v)
			{

				$prevKey = $k-1;

				if(!empty($prevKey))
				{
					$this->aTimeMarks[$prevKey]['Memory Used'] = (intval($v['Memory']) - $prevMem);
				}

				$prevMem = intval($v['Memory']);
			}



			while(list($tKey, $tMarker) = each($this->aTimeMarks))
			{
				if(!$bRowHeaders)
				{
					// First time: emit headers
					$bRowHeaders = true;
					$text .= "<tr><td class='fcaption' style='text-align:right'><b>" . implode("</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>", array_keys($tMarker)) . "</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>OB Lev&nbsp;</b></td></tr>\n";
					$aUnits = $tMarker;
					foreach($aUnits as $key => $val)
					{
						switch($key)
						{
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
					$aUnits['Memory Used'] = '(kb)';
					$text .= "<tr><td class='fcaption' style='text-align:right'><b>" . implode("</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>", $aUnits) . "</b>&nbsp;</td></tr>\n";
				}



			//	$tMem =   ($tMarker['Memory'] - $aSum['Memory']);

				$tMem =   ($tMarker['Memory']);

				if($tMem < 0) // Quick Fix for negative numbers.
				{
				//	$tMem = 0.0000000001;
				}

				$tMarker['Memory'] = ($tMem ? number_format($tMem / 1024.0, 1) : '?'); // display if known

				$tUsage = $tMarker['Memory Used'];
				$tMarker['Memory Used'] = number_format($tUsage / 1024.0, 1);

				$tMarker['Memory Used'] = $this->highlight($tMarker['Memory Used'],$tUsage,400000);
/*
				if($tUsage > 400000) // Highlight high memory usage.
				{
					$tMarker['Memory Used'] = "<span class='label label-danger'>".$tMarker['Memory Used']."</span>";
				}*/

				$aSum['Memory'] = $tMem;

				if($tMarker['What'] == 'Stop')
				{
					$tMarker['Time'] = '&nbsp;';
					$tMarker['%Time'] = '&nbsp;';
					$tMarker['%DB Count'] = '&nbsp;';
					$tMarker['%DB Time'] = '&nbsp;';
					$tMarker['DB Time'] = '&nbsp;';
					$tMarker['OB Lev'] = $this->aOBMarks[$tKey];
					$tMarker['DB Count'] = '&nbsp;';
				}
				else
				{
					// Convert from start time to delta time, i.e. from now to next entry
					$nextMarker = current($this->aTimeMarks);
					$aNextT = $nextMarker['Time'];
					$aThisT = $tMarker['Time'];

					$thisDelta = e107::getSingleton('e107_traffic')->TimeDelta($aThisT, $aNextT);
					$aSum['Time'] += $thisDelta;
					$aSum['DB Time'] += $tMarker['DB Time'];
					$aSum['DB Count'] += $tMarker['DB Count'];
					$tMarker['Time'] = number_format($thisDelta * 1000.0, 1);
					$tMarker['Time'] = $this->highlight($tMarker['Time'],$thisDelta,.2);


					$tMarker['%Time'] = $totTime ? number_format(100.0 * ($thisDelta / $totTime), 0) : 0;
					$tMarker['%DB Count'] = number_format(100.0 * $tMarker['DB Count'] / $sql->db_QueryCount(), 0);
					$tMarker['%DB Time'] = $db_time ? number_format(100.0 * $tMarker['DB Time'] / $db_time, 0) : 0;
					$tMarker['DB Time'] = number_format($tMarker['DB Time'] * 1000.0, 1);

					$tMarker['OB Lev'] = $this->aOBMarks[$tKey];
				}

				$text .= "<tr><td class='forumheader3' >" . implode("&nbsp;</td><td class='forumheader3'  style='text-align:right'>", array_values($tMarker)) . "&nbsp;</td></tr>\n";

				if(isset($this->aMarkNotes[$tKey]))
				{
					$text .= "<tr><td class='forumheader3' >&nbsp;</td><td class='forumheader3' colspan='4'>";
					$text .= $this->aMarkNotes[$tKey] . "</td></tr>\n";
				}

				if($tMarker['What'] == 'Stop')
				{
					break;
				}
			}

			$aSum['%Time'] = $totTime ? number_format(100.0 * ($aSum['Time'] / $totTime), 0) : 0;
			$aSum['%DB Time'] = $db_time ? number_format(100.0 * ($aSum['DB Time'] / $db_time), 0) : 0;
			$aSum['%DB Count'] = ($sql->db_QueryCount()) ? number_format(100.0 * ($aSum['DB Count'] / ($sql->db_QueryCount())), 0) : 0;
			$aSum['Time'] = number_format($aSum['Time'] * 1000.0, 1);
			$aSum['DB Time'] = number_format($aSum['DB Time'] * 1000.0, 1);


			$text .= "<tr>
		<td class='fcaption'>&nbsp;</td>
		<td class='fcaption' style='text-align:right'><b>Total</b></td>
		<td class='fcaption' style='text-align:right'><b>" . $aSum['%Time'] . "</b></td>
		<td class='fcaption' style='text-align:right'><b>" . $aSum['%DB Time'] . "</b></td>
		<td class='fcaption' style='text-align:right'><b>" . $aSum['%DB Count'] . "</b></td>
		<td class='fcaption' style='text-align:right' title='Time (msec)'><b>" . $aSum['Time'] . "</b></td>
		<td class='fcaption' style='text-align:right' title='DB Time (msec)'><b>" . $aSum['DB Time'] . "</b></td>
		<td class='fcaption' style='text-align:right'><b>" . $aSum['DB Count'] . "</b></td>
		<td class='fcaption' style='text-align:right' title='Memory (Kb)'><b>" . number_format($aSum['Memory'] / 1024, 1) . "</b></td>
		<td class='fcaption' style='text-align:right' title='Memory (Kb)'><b>" . number_format($aSum['Memory'] / 1024, 1) . "</b></td>

			<td class='fcaption' style='text-align:right'><b>" . $tMarker['OB Lev'] . "</b></td>

		</tr>
		";


			//	$text .= "<tr><td class='fcaption'><b>".implode("</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>", $aSum)."</b>&nbsp;</td><td class='fcaption'>&nbsp;</td></tr>\n";

			$text .= "\n</table><br />\n";


			//
			// Stats by Table
			//

			$text .= "\n<table class='fborder table table-striped table-condensed'>
			<colgroup>
				<col style='width:auto' />
				<col style='width:9%' />
					<col style='width:9%' />
						<col style='width:9%' />
							<col style='width:9%' />
			</colgroup>\n";

			$bRowHeaders = false;
			$aSum = $this->aDBbyTable['core']; // create a template from the 'real' array
			$aSum['Table'] = 'Total';
			$aSum['%DB Count'] = 0;
			$aSum['%DB Time'] = 0;
			$aSum['DB Time'] = 0;
			$aSum['DB Count'] = 0;

			foreach($this->aDBbyTable as $curTable)
			{
				if(!$bRowHeaders)
				{
					$bRowHeaders = true;
					$text .= "<tr><td class='fcaption'><b>" . implode("</b></td><td class='fcaption' style='text-align:right'><b>", array_keys($curTable)) . "</b></td></tr>\n";
					$aUnits = $curTable;
					foreach($aUnits as $key => $val)
					{
						switch($key)
						{
							case 'DB Time':
								$aUnits[$key] = '(msec)';
								break;
							default:
								$aUnits[$key] = '';
								break;
						}
					}
					$text .= "<tr><td class='fcaption' style='text-align:right'><b>" . implode("</b>&nbsp;</td><td class='fcaption' style='text-align:right'><b>", $aUnits) . "</b>&nbsp;</td></tr>\n";
				}

				$aSum['DB Time'] += $curTable['DB Time'];
				$aSum['DB Count'] += $curTable['DB Count'];
				$curTable['%DB Count'] = number_format(100.0 * $curTable['DB Count'] / $sql->db_QueryCount(), 0);
				$curTable['%DB Time'] = number_format(100.0 * $curTable['DB Time'] / $db_time, 0);
				$timeLabel = number_format($curTable['DB Time'] * 1000.0, 1);
				$curTable['DB Time'] = $this->highlight($timeLabel, ($curTable['DB Time'] * 1000), 500); // 500 msec

				$text .= "<tr><td class='forumheader3'>" . implode("&nbsp;</td><td class='forumheader3' style='text-align:right'>", array_values($curTable)) . "&nbsp;</td></tr>\n";
			}

			$aSum['%DB Time'] = $db_time ? number_format(100.0 * ($aSum['DB Time'] / $db_time), 0) : 0;
			$aSum['%DB Count'] = ($sql->db_QueryCount()) ? number_format(100.0 * ($aSum['DB Count'] / ($sql->db_QueryCount())), 0) : 0;
			$aSum['DB Time'] = number_format($aSum['DB Time'] * 1000.0, 1);
			$text .= "<tr><td class='fcaption'><b>" . implode("&nbsp;</td><td class='fcaption' style='text-align:right'><b>", array_values($aSum)) . "&nbsp;</b></td></tr>\n";
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

	function logCode($type, $code, $parm, $details)
	{
		if (!E107_DBG_BBSC)
		{
			return FALSE;
		}
				
		$this->scbbcodes[$this->scbcount]['type'] = $type;
		$this->scbbcodes[$this->scbcount]['code'] = $code;
		$this->scbbcodes[$this->scbcount]['parm'] = (string)$parm;
		$this->scbbcodes[$this->scbcount]['details'] = $details;
		$this->scbcount ++;
	}

	function Show_SC_BB($force=false)
	{
		if (!E107_DBG_BBSC  && ($force === false))
		{
			return false;
		}

		$text = "<table class='fborder table table-striped table-condensed' style='width: 100%'>
			
			<thead>
			<tr>
				<th class='fcaption' style='width: 10%;'>Type</th>
				<th class='fcaption' style='width: 30%;'>Code</th>
				<th class='fcaption' style='width: 20%;'>Parm</th>
				<th class='fcaption' style='width: 40%;'>Details</th>
			</tr>
			</thead>
			<tbody>\n";

		$description = array(1=>'Bbcode',2=>'Shortcode',3=>'Wrapper', 4=>'Shortcode Override', -2 => 'Shortcode Failure');
		$style = array(1 => 'label-info', 2=>'label-primary', 3=>'label-warning', 'label-danger', -2 => 'label-danger');

 		foreach($this -> scbbcodes as $codes)
		{

			$type = $codes['type'];

			$text .= "<tr>
				<td class='forumheader3' style='width: 10%;'><span class='label ".$style[$type]."'>".($description[$type])."</span></td>
				<td class='forumheader3' style='width: auto;'>".(isset($codes['code']) ? $codes['code'] : "&nbsp;")."</td>
				<td class='forumheader3' style='width: auto;'>".($codes['parm'] ? $codes['parm'] : "&nbsp;")."</td>
				<td class='forumheader3' style='width: 40%;'>".($codes['details'] ? $codes['details'] : "&nbsp;")."</td>
				</tr>\n";
		}
		$text .= "</tbody></table>";
		return $text;
	}

	function Show_PATH($force=false)
	{
		if (!E107_DBG_PATH && ($force === false))
		{
			return FALSE;
		}
		
		global $e107;
		$sql = e107::getDb();
		
		$text = "<table class='fborder table table-striped table-condensed debug-footer' style='width:100%'>
		<colgroup>
		<col style='width:20%' />
		<col style='width:auto' />
		</colgroup>
		<thead>
			<tr>
				<th class='fcaption debug-footer-caption left' colspan='2'><b>Paths &amp; Variables</b></th>
			</tr>
		</thead>
		<tbody>\n";


		$inc = array(
			'BOOTSTRAP','HEADERF','FOOTERF','FILE_UPLOADS','FLOODPROTECT','FLOODTIMEOUT','CHARSET',
			'GUESTS_ONLINE','MEMBERS_ONLINE','PAGE_NAME','STANDARDS_MODE','TIMEOFFSET',
			'TOTAL_ONLINE','THEME','THEME_ABS','THEME_LAYOUT', 'THEME_LEGACY','THEME_STYLE','META_OG','META_DESCRIPTION','MPREFIX','VIEWPORT','BODYTAG','CSSORDER'
		);
		
		$userCon = get_defined_constants(true);
		ksort($userCon['user']);
		
		foreach($userCon['user'] as $k=>$v)
		{
			if(E107_DBG_ALLERRORS || in_array($k,$inc) ||  substr($k,0,5) == 'ADMIN'  ||  substr($k,0,2) == 'E_' || substr($k,0,2) == 'e_' || substr($k,0,4) == 'E107' || substr($k,0,4) == 'SITE' ||  substr($k,0,4) == 'USER' || substr($k,0,4) == 'CORE')
			{
				$text .= "
				<tr>
					<td class='forumheader3'>".$k."</td>
					<td class='forumheader3'>".htmlspecialchars($v)."</td>
				</tr>";		
			}
		}
				
		$sess = e107::getSession();
					
		$text .= "
			
		
			<tr>
				<td class='forumheader3'>SQL Language</td>
				<td class='forumheader3'>".$sql->mySQLlanguage."</td>
			</tr>
";
	if($_SERVER['E_DEV'] == 'true')
	{
			$text .= "
				<tr>
					<td class='forumheader3' colspan='2'><pre>".htmlspecialchars(print_r($e107,TRUE))."</pre></td>
				</tr>";
	}

		$text .="
			<tr>
				<td class='fcaption' colspan='2'><h2>Session</h2></td>
			</tr>
			<tr>
				<td class='forumheader3'>Session lifetime</td>
				<td class='forumheader3'>".$sess->getOption('lifetime')." seconds</td>
			</tr>
			<tr>
				<td class='forumheader3'>Session domain</td>
				<td class='forumheader3'>".$sess->getOption('domain')."</td>
			</tr>
			<tr>
				<td class='forumheader3'>Session save method</td>
				<td class='forumheader3'>".$sess->getSaveMethod()."</td>
			</tr>
			
			
			
			<tr>
				<td class='forumheader3' colspan='2'><pre>".htmlspecialchars(print_r($_SESSION,TRUE))."</pre></td>
			</tr>
			
		</tbody>
		</table>";

		return $text;
	}


	function Show_DEPRECATED($force=false)
	{
		if (!E107_DBG_DEPRECATED  && ($force === false))
		{
			return false;
		} 
		else 
		{
			$text = "<table class='fborder table table-striped table-condensed' style='width: 100%'>
			<tr><td class='fcaption' colspan='4'><b>The following deprecated functions were used:</b></td></tr>
			<thead>
			<tr>
			<th class='fcaption' style='width: 10%;'>Function</th>
			<th class='fcaption' style='width: 10%;'>File</th>
			<th class='fcaption' style='width: 10%;'>Line</th>
			</tr>
			</thead>
			<tbody>\n";

			foreach($this->deprecated_funcs as $funcs)
			{
				$text .= "<tr>
				<td class='forumheader3' style='width: 10%;'>{$funcs['func']}()</td>
				<td class='forumheader3' style='width: 10%;'>{$funcs['file']}</td>
				<td class='forumheader3' style='width: 10%;'>{$funcs['line']}</td>
				</tr>\n";
			}
			$text .= "</tbody></table>";
			return $text;
		}
	}


	/**
	 * var_dump to debug log
	 * @param mixed $message
	 */
	function dump($message, $TraceLev= 1)
	{
		ob_start();
	    var_dump($message);
	    $content = ob_get_contents();
	    ob_end_clean();

	    $bt = debug_backtrace();

		$this->aLog[] =	array(
			'Message'   => $content,
			'Function'	=> (isset($bt[$TraceLev]['type']) && ($bt[$TraceLev]['type'] == '::' || $bt[$TraceLev]['type'] == '->') ? $bt[$TraceLev]['class'].$bt[$TraceLev]['type'].$bt[$TraceLev]['function'].'()' : $bt[$TraceLev]['function']).'()',
				'File'	=> varset($bt[$TraceLev]['file']),
				'Line'	=> varset($bt[$TraceLev]['line'])
			);

	   // $this->aLog[] =	array ('Message'   => $content, 'Function' => '', 	'File' => '', 'Line' => '' 	);

	}
//
// Simple debug-level 'console' log
// Record a "nice" debug message with
// $db_debug->log("message");
//
	function log($message,$TraceLev=1)
	{



		if(is_array($message) || is_object($message))
		{
			$message = "<pre>".print_r($message,true)."</pre>";
		}

		if (!deftrue('E107_DBG_BASIC') && !deftrue('E107_DBG_ALLERRORS') && !deftrue('E107_DBG_SQLDETAILS') && !deftrue('E107_DBG_NOTICES'))
		{
			return false;
		}

		if ($TraceLev)
		{
			$bt = debug_backtrace();
			$this->aLog[] =	array (
				'Message'   => $message,
				'Function'	=> (isset($bt[$TraceLev]['type']) && ($bt[$TraceLev]['type'] == '::' || $bt[$TraceLev]['type'] == '->') ? $bt[$TraceLev]['class'].$bt[$TraceLev]['type'].$bt[$TraceLev]['function'].'()' : $bt[$TraceLev]['function']).'()',
				'File'	=> varset($bt[$TraceLev]['file']),
				'Line'	=> varset($bt[$TraceLev]['line'])
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

	function Show_Log()
	{
		if (empty($this->aLog))
		{
			return FALSE;
		}
		//
		// Dump the debug log
		//

		$text = "\n<table class='fborder table table-striped'>\n";

		$bRowHeaders=FALSE;
		
		foreach ($this->aLog as $curLog) 
		{
			if (!$bRowHeaders)
			{
				$bRowHeaders = true;
				$text .= "<tr><td class='fcaption' style='text-align:left'><b>".implode("</b></td><td class='fcaption' style='text-align:left'><b>", array_keys($curLog))."</b></td></tr>\n";
			}

			$text .= "<tr ><td class='forumheader3'>".implode("&nbsp;</td><td class='forumheader3'>", array_values($curLog))."&nbsp;</td></tr>\n";
		}

		$text .= "</table><br />\n";

		return $text;
	}
	
	function Show_Includes($force=false)
	{
		if (!E107_DBG_INCLUDES  && ($force === false)) return false;

		
        
		$text = "<table class='fborder table table-striped'>\n";
		$text .= "<tr><td class='forumheader3'>".
							implode("&nbsp;</td></tr>\n<tr><td class='forumheader3'>", $this->aIncList).
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
	if(e_AJAX_REQUEST) // extra output will break json ajax returns ie. comments 
	{
		return; 	
	}
	
	
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

	if (isset($error_handler))
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