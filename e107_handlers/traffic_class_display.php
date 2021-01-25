<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/traffic_class_display.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT'))
{
	exit;
}

//
// This is the content-code for e107_traffic::Display()
// It is separated out to avoid parsing when not in debug mode
//
// See traffic_class.php
//

if (count($this->aTraffic))
{ // Simple counts
	$text .= "\n<table class='table table-condensed table-striped'>\n";
	$text .= "<thead>
            		<tr>
            		<th style='width:15%'>Item</th>
                    <th style='text-align:right;width:15%'>Count&nbsp;</th>
                    <th>&nbsp;</th>
                    </tr>
                    </thead>\n";
	foreach ($this->aTraffic as $key => $aVals)
	{
		$text .= "<tr>
                        <td>" .
			$key . "</td>
                        <td style='text-align:right;width:20%'>" .
			$aVals['Count'] . "&nbsp;</td><td>&nbsp;</td></tr>\n";

		if (isset($this->aTrafficWho[$key]))
		{
			$text .= "<tr><td valign='top'>Callers:</td>
                            <td colspan='2'>";
			$bFirst = true;
			foreach ($this->aTrafficWho[$key] as $sWho)
			{
				if ($bFirst)
				{
					$bFirst = false;
				}
				else
				{
					$text .= "<br />\n";
				}
				$text .= $sWho;
			}
			$text .= "</td></tr>\n";
		}

	}
	$text .= "</table><br />\n";
}
//
// Fancy timed counts
//
if (count($this->aTrafficTimed))
{
	$text .= "\n<table class='fborder table table-condensed table-striped'>\n";
	$text .= "
            <thead>
            	<tr>
            		<th>Item</th>
            		<th>Count&nbsp;</th>
                    <th>Tot Time (ms)&nbsp;</th>
                    <th>Avg Time (us)&nbsp;</th>
                    <th>Min Time (us)&nbsp;</th>
                    <th>Max Time (us)&nbsp;</th>
				</tr>
             </thead>\n";


	foreach ($this->aTrafficTimed as $key => $aVals)
	{
		if (strpos($key, 'TRAF_CAL') === 0)
		{
			continue;
		}
		$text .= "<tr>
                        <td>" .
			$key . "</td>
                        <td style='text-align:right'>" .
			$aVals['Count'] . "&nbsp;</td>";
		if ($aVals['Count'] && isset($aVals['Time']) && $aVals['Time'])
		{
			$sTot = number_format($aVals['Time'] * 1000.0, 4);
			$sAvg = number_format($aVals['Time'] * 1000000.0 / $aVals['Count'], 1);
			$sMin = number_format($aVals['Min'] * 1000000.0, 1);
			$sMax = number_format($aVals['Max'] * 1000000.0, 1);
		}
		else
		{
			$sTot = $sAvg = $sMin = $sMax = '';
		}
		$text .= "<td style='text-align:right'>" .
			$sTot . "&nbsp;</td>
                        <td style='text-align:right'>" .
			$sAvg . "&nbsp;</td>
                        <td style='text-align:right'>" .
			$sMin . "&nbsp;</td>
                        <td style='text-align:right'>" .
			$sMax . "&nbsp;</td>
                    </tr>\n";

		if (isset($this->aTrafficWho[$key]))
		{
			$text .= "<tr><td valign='top'>Callers:</td>
                            <td colspan='5'>";
			$bFirst = true;
			foreach ($this->aTrafficWho[$key] as $sWho)
			{
				if ($bFirst)
				{
					$bFirst = false;
				}
				else
				{
					$text .= "<br />\n";
				}
				$text .= $sWho;
			}
			$text .= "</td></tr>\n";
		}

	}
	$cal1 = number_format($this->calPassOne * 1000000.0, 1);
	$cal2 = number_format($this->calPassBoth * 1000000.0, 1);
	$cTot = number_format($this->calTime * 1000.0, 4);

	$text .= "<tr><td colspan='6'>
            <b>Note:</b> These times have been decreased by the calibration offset:<br />
            $cal2 usec per call(start,stop); $cal1 usec per call(start). Total adjustment: $cTot msec.</td></tr>\n";
	$text .= "</table><br />\n";
}
