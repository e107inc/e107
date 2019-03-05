<?php 
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Traffic handler
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/traffic_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT'))
{
	exit;
}

class e107_traffic
{
	var $aTraffic = array(); // Overall system traffic counters
	var $aTrafficTimed = array(); // Timed traffic counters
	var $aTrafficWho = array(); // Overall system traffic source tracking
	var $calPassBoth = 0.0; // Calibration offset when both parameters are passed
	var $calPassOne = 0.0; // Calibration offset when only one parameter is passed
	var $calTime = 0.0; // Total time spent in overhead, based on calibration
	var $qTimeOn = 0; // Quick Timer: when it started
	var $qTimeTotal = 0.0; // Quick Timer: Accumulated time
	
	/**
	 * Constructor
	 */
	public function __construct($calibrate = true)
	{
		//auto calibrate
		if($calibrate)
		{
			$this->Calibrate($this);
		}
	}
	
	/**
	 * @return float         Time difference
	 * @param time $tStart   Start time - unexploded microtime result
	 * @param time $tStop    Finish time - unexploded microtime result
	 * @desc Calculate time difference between to microtimes
	 * @access public
	 */
	function TimeDelta($tStart, $tFinish)
	{
		$tFrom = explode(' ', $tStart);
		$tTo = explode(' ', $tFinish);
		$tTot = ((float) $tTo[0] + (float) $tTo[1]) - ((float) $tFrom[0] + (float) $tFrom[1]);
		return $tTot;
	}
	
	/**
	 * @return float         Absolute time from microtime
	 * @param time $tStart   time - unexploded microtime result
	 * @desc Return absolute time
	 * @access public
	 */
	function TimeAbs($tStart)
	{
		$tFrom = explode(' ', $tStart);
		return (float) $tFrom[0] + (float) $tFrom[1];
	}
	
	/**
	 * @return void
	 * @param string $sWhat  what to count
	 * @param time $tStart Start time - unexploded microtime result
	 * @param time $tStop  Finish time - unexploded microtime result
	 * @desc Count one of anything, optionally with time used
	 * @access public
	 */
	function Bump($sWhat, $tStart = 0, $tFinish = 0)
	{
		$x = microtime(); // on my system:
		// 0        err: $e=microtime(); $eTraffic->Bump('foo',$b,$e);
		// ~15 usec err: $eTraffic->Bump('foo',$b,microtime());
		// ~25 usec err: $eTraffic->Bump('foo',$b);
		
		if (!defined("E107_DBG_TRAFFIC") || !E107_DBG_TRAFFIC)
		{
			return;
		}
		
		if ($tStart)
		{
			$vName = 'aTrafficTimed';
			$bTimed = TRUE;
		}
		else
		{
			$vName = 'aTraffic';
			$bTimed = FALSE;
		}
		if (!isset($this-> {$vName} [$sWhat]))
		{
			$this-> {$vName} [$sWhat] = array();
			$t = &$this-> {$vName} [$sWhat];
			$t['Count'] = 0;
			if ($bTimed)
			{
				$t['Time'] = 0.0;
				$t['Min'] = 999999999.0;
				$t['Max'] = 0.0;
			}
		}
		
		$this-> {$vName} [$sWhat]['Count']++;
		
		if ($bTimed)
		{
			$t = &$this->aTrafficTimed[$sWhat];
			if (!$tFinish)
			{
				$tFinish = $x;
				$offset = $this->calPassOne;
			}
			else
			{
				$offset = $this->calPassBoth;
			}
			$time = $this->TimeDelta($tStart, $tFinish) - $offset;
			$this->calTime += $offset;
			$t['Time'] += $time;
			if ($time < $t['Min'])
				$t['Min'] = $time;
			if ($time > $t['Max'])
				$t['Max'] = $time;
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
	function BumpWho($sWhat, $level = 0, $tStart = 0, $tFinish = 0)
	{
		$x = microtime();
		if (!defined("E107_DBG_TRAFFIC") || !E107_DBG_TRAFFIC)
		{
			return;
		}
		
		$this->Bump($sWhat, $tStart, ($tFinish ? $tFinish : $x));
		
		if (!isset($this->aTrafficWho[$sWhat]))
		{
			$this->aTrafficWho[$sWhat] = array();
		}
		$aTrace = debug_backtrace();
		if ($level >= count($aTrace))
		{
			$level = count($aTrace) - 1;
		}
		$sFile = $aTrace[$level]['file'];
		$sLine = $aTrace[$level]['line'];
		
		$this->aTrafficWho[$sWhat][] = "$sFile($sLine)";
	}
	
	function Calibrate(e107_traffic $tObject, $count = 10)
	{
		if (!defined("E107_DBG_TRAFFIC") || !E107_DBG_TRAFFIC)
		{
			return;
		}
		if ($tObject != $this)
		{
			message_handler("CRITICAL_ERROR", "Bad traffic object", __LINE__ - 2, __FILE__);
		}
		if ($count <= 0)
			return; // no calibration
			
		$this->calPassBoth = $this->calPassOne = 0.0;
		
		for ($i = 0; $i < $count; $i++)
		{
			$b = microtime();
			$e = microtime();
			$tObject->Bump('TRAF_CAL1', $b, $e); // emulate the normal non-insider call
			$b = microtime();
			$tObject->Bump('TRAF_CAL2', $b);
		}
		$t = $tObject->aTrafficTimed['TRAF_CAL1'];
		$this->calPassBoth = $t['Time'] / $t['Count'];
		$t = $tObject->aTrafficTimed['TRAF_CAL2'];
		$this->calPassOne = $t['Time'] / $t['Count'];
	}
	
	function Display()
	{
		if (!defined("E107_DBG_TRAFFIC") || !E107_DBG_TRAFFIC || E107_DBG_BASIC) // 'Basic' should not display Traffic.
		{
			return '';
		}
		
		$text = '';
		@ include_once (e_HANDLER.'traffic_class_display.php');
		return $text;
	}
}

//
// This is a set of quick-n-simple tools to measure ONE bit of render time,
// without any need for debug to be working. You can copy to somewhere else if needed
// such as before this class has been loaded

if (!isset($qTimeOn))
{
	$qTimeOn = 0;
	$qTimeTotal = 0;
	function eQTimeOn()
	{
		$GLOBALS['qTimeOn'] = explode(' ', microtime());
	}
	function eQTimeOff()
	{
		$e = explode(' ', microtime());
		$diff = ((float) $e[0] + (float) $e[1]) - ((float) $qTimeOn[0] + (float) $qTimeOn[1]);
		$GLOBALS['qTimeTotal'] += $diff;
	}
	function eQTimeElapsed()
	{
		// return elapsed time so far, as text in microseconds, or blank if zero
		if (isset($GLOBALS['qTimeTotal']))
		{
			return number_format($GLOBALS['qTimeTotal'] * 1000000.0, 1);
		}
		else
		{
			return '';
		}
	}
	
}
