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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/traffic_class.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-27 19:52:29 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
class e107_traffic {
	var $aTraffic;
	// Overall system traffic counters
	var $aTrafficWho; // Overall system traffic source tracking
	var $calPassBoth;
	// Calibration offset when both parameters are passed
	var $calPassOne;
	// Calibration offset when only one parameter is passed
	var $calTime;
	// Total time spent in overhead, based on calibration
	 
	function e107_traffic() {
		$this->aTraffic = array();
		$this->aTrafficWho = array();
		$this->calPassBoth = $this->calPassOne = $this->calTime = 0.0;
	}
	 
	/**
	* @return float         Time difference
	* @param time $tStart   Start time - unexploded microtime result
	* @param time $tStop    Finish time - unexploded microtime result
	* @desc Calculate time difference between to microtimes
	* @access public
	*/
	function TimeDelta($tStart, $tFinish ) {
		$tFrom = explode(' ', $tStart);
		$tTo = explode(' ', $tFinish);
		$tTot = ((float)$tTo[0] + (float)$tTo[1]) - ((float)$tFrom[0] + (float)$tFrom[1]);
		return $tTot;
	}
	 
	/**
	* @return float         Absolute time from microtime
	* @param time $tStart   time - unexploded microtime result
	* @desc Return absolute time
	* @access public
	*/
	function TimeAbs($tStart ) {
		$tFrom = explode(' ', $tStart);
		return (float)$tFrom[0] + (float)$tFrom[1];
	}
	 
	/**
	* @return void
	* @param string $sWhat  what to count
	* @param time $tStart Start time - unexploded microtime result
	* @param time $tStop  Finish time - unexploded microtime result
	* @desc Count one of anything, optionally with time used
	* @access public
	*/
	function Bump($sWhat, $tStart = 0, $tFinish = 0) {
		$x = microtime(); // on my system:
		// 0        err: $e=microtime(); $eTraffic->Bump('foo',$b,$e);
		// ~15 usec err: $eTraffic->Bump('foo',$b,microtime());
		// ~25 usec err: $eTraffic->Bump('foo',$b);
		 
		if (!E107_DBG_TRAFFIC) return;
		 
		if (!isset($this->aTraffic[$sWhat])) {
			$this->aTraffic[$sWhat] = array();
			$t = & $this->aTraffic[$sWhat];
			$t['Count'] = 0;
			$t['Time'] = 0.0;
			$t['Min'] = 999999999.0;
			$t['Max'] = 0.0;
		}
		$this->aTraffic[$sWhat]['Count']++;
		 
		if ($tStart) {
			$t = & $this->aTraffic[$sWhat];
			if (!$tFinish) {
				$tFinish = $x;
				$offset = $this->calPassOne;
			} else {
				$offset = $this->calPassBoth;
			}
			$time = $this->TimeDelta($tStart, $tFinish ) - $offset;
			$this->calTime += $offset;
			$t['Time'] += $time;
			if ($time < $t['Min']) $t['Min'] = $time;
			if ($time > $t['Max']) $t['Max'] = $time;
		}
	}
	 
	/**
	* @return void
	* @param string $sWhat  what to count
	* @param int  $level  who to record: default caller. 1-999=N levels up the call tree
	* @param time $tStart Start time - unexploded microtime result
	* @param time $tStop  Finish time - unexploded microtime result
	* @desc Count one of anything, optionally with time used.
	* @access public
	*/
	function BumpWho($sWhat, $level = 0, $tStart = 0, $tFinish = 0) {
		$x = microtime();
		if (!E107_DBG_TRAFFIC) return;
		 
		$this->Bump($sWhat, $tStart, ($tFinish? $tFinish : $x));
		 
		if (!isset($this->aTrafficWho[$sWhat])) {
			$this->aTrafficWho[$sWhat] = array();
		}
		$aTrace = debug_backtrace();
		if ($level >= count($aTrace)) {
			$level = count($aTrace)-1;
		}
		$sFile = $aTrace[$level]['file'];
		$sLine = $aTrace[$level]['line'];
		 
		$this->aTrafficWho[$sWhat][] = "$sFile($sLine)";
	}
	 
	function Calibrate($tObject, $count = 10 ) {
		if ($tObject != $this) {
			message_handler("CRITICAL_ERROR", "Bad traffic object", __LINE__-2, __FILE__);
		}
		if ($count <= 0) return;
		// no calibration
		 
		$this->calPassBoth = $this->calPassOne = 0.0;
		 
		for ($i = 0; $i < $count; $i++) {
			$b = microtime();
			$e = microtime();
			$tObject->Bump('TRAF_CAL1', $b, $e); // emulate the normal non-insider call
			$b = microtime();
			$tObject->Bump('TRAF_CAL2', $b);
		}
		$t = $tObject->aTraffic['TRAF_CAL1'];
		$this->calPassBoth = $t['Time']/$t['Count'];
		$t = $tObject->aTraffic['TRAF_CAL2'];
		$this->calPassOne = $t['Time']/$t['Count'];
	}
	 
	function Display() {
		$text = '';
		if (E107_DBG_TRAFFIC && count($this->aTraffic)) {
			$text .= "\n<table class='fborder'>\n";
			$text .= "<tr><td class='fcaption'>Item</td><td class='fcaption'>Count&nbsp;</td>
				<td class='fcaption'>Tot Time (ms)&nbsp;</td>
				<td class='fcaption'>Avg Time (us)&nbsp;</td>
				<td class='fcaption'>Min Time (us)&nbsp;</td>
				<td class='fcaption'>Max Time (us)&nbsp;</td>
				</tr\n";
			foreach ($this->aTraffic as $key => $aVals) {
				if (substr($key, 0, 8) == 'TRAF_CAL') continue;
				$text .= "<tr>
					<td class='forumheader3'>". $key."</td>
					<td class='forumheader3' style='text-align:right'>". $aVals['Count']."&nbsp;</td>";
				if ($aVals['Count'] && isset($aVals['Time']) && $aVals['Time']) {
					$sTot = number_format($aVals['Time'] * 1000.0, 4);
					$sAvg = number_format($aVals['Time'] * 1000000.0/$aVals['Count'], 4);
					$sMin = number_format($aVals['Min'] * 1000000.0, 4);
					$sMax = number_format($aVals['Max'] * 1000000.0, 4);
				} else {
					$sTot = $sAvg = $sMin = $sMax = '';
				}
				$text .= "<td class='forumheader3' style='text-align:right'>". $sTot."&nbsp;</td>
					<td class='forumheader3' style='text-align:right'>". $sAvg."&nbsp;</td>
					<td class='forumheader3' style='text-align:right'>". $sMin."&nbsp;</td>
					<td class='forumheader3' style='text-align:right'>". $sMax."&nbsp;</td>
					</tr>\n";
				 
				if (isset($this->aTrafficWho[$key])) {
					$text .= "<tr><td class='forumheader3' valign='top'>Callers:</td>
						<td class='forumheader3' colspan='5'>";
					$bFirst = TRUE;
					foreach ($this->aTrafficWho[$key] as $sWho) {
						if ($bFirst ) {
							$bFirst = FALSE;
						} else {
							$text .= "<br />\n";
						}
						$text .= $sWho;
					}
					$text .= "</td></tr>\n";
				}
				 
			}
			$cal1 = number_format($this->calPassOne * 1000000.0, 4);
			$cal2 = number_format($this->calPassBoth * 1000000.0, 4);
			$cTot = number_format($this->calTime * 1000.0, 4);
			 
			$text .= "<tr><td class='forumheader3' colspan='6'>
				Calibration: $cal2 / $cal1 usec for both/one times passed in; $cTot msec traffic overhead total.</td></tr>\n";
			$text .= "</table><br />\n";
		}
		return $text;
	}
	 
}
	
?>