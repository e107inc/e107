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
|     $Revision: 1.1 $
|     $Date: 2005-01-27 15:10:26 $
|     $Author: mrpete $
+----------------------------------------------------------------------------+
*/
class e107_traffic {
    var $aTraffic;    // Overall system traffic counters
    
    function e107_traffic() {
        $b = microtime();
        $this->aTraffic = array();
    }
    
    /**
    * @return float         Time difference
    * @param time $tStart   Start time - unexploded microtime result
    * @param time $tStop    Finish time - unexploded microtime result
    * @desc Calculate time difference between to microtimes
    * @access public
    */
    function TimeDelta( $tStart, $tFinish ) {
                $tFrom = explode(' ',$tStart);
                $tTo   = explode(' ',$tFinish);
                $tTot  = ((float)$tTo[0] + (float)$tTo[1]) - ((float)$tFrom[0] + (float)$tFrom[1]);
                return $tTot;
        }
        
    /**
    * @return float         Absolute time from microtime
    * @param time $tStart   time - unexploded microtime result
    * @desc Return absolute time
    * @access public
    */
    function TimeAbs( $tStart ) {
                $tFrom = explode(' ',$tStart);
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
    function Bump($sWhat, $tStart=0, $tFinish=0) {
        $x=microtime(); // on my system, grabbing it here costs 25usec; in the call costs 15usec
        if (!E107_DBG_TRAFFIC) return;
        
        if (!isset($this->aTraffic[$sWhat])) {
            $this->aTraffic[$sWhat] = array();
            $t = & $this->aTraffic[$sWhat];
            $t['Count']=0;
            $t['Time']=0.0;
            $t['Min']=999999999.0;
            $t['Max']=0.0;
        }
        $this->aTraffic[$sWhat]['Count']++;
        
        if ($tStart) {
            $t = & $this->aTraffic[$sWhat];
            if (!$tFinish) {
                $tFinish = $x;
            }
            $time= $this->TimeDelta( $tStart, $tFinish );
            $t['Time'] += $time;
            if ($time < $t['Min']) $t['Min'] = $time;
            if ($time > $t['Max']) $t['Max'] = $time;
        }
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
            foreach ($this->aTraffic as $key=>$aVals) {
                $text .= "<tr>
                        <td class='forumheader3'>".
                    $key."</td>
                        <td class='forumheader3' style='text-align:right'>".
                    $aVals['Count']."&nbsp;</td>";
                if ($aVals['Count'] && isset($aVals['Time']) && $aVals['Time']) {
                    $sTot = number_format($aVals['Time']*1000.0,4);
                    $sAvg = number_format($aVals['Time']*1000000.0/$aVals['Count'],4);
                    $sMin = number_format($aVals['Min']*1000000.0,4);
                    $sMax = number_format($aVals['Max']*1000000.0,4);
                } else {
                    $sTot = $sAvg = $sMin = $sMax = '';
                }
                $text .= "<td class='forumheader3' style='text-align:right'>".
                    $sTot."&nbsp;</td>
                        <td class='forumheader3' style='text-align:right'>".
                    $sAvg."&nbsp;</td>
                        <td class='forumheader3' style='text-align:right'>".
                    $sMin."&nbsp;</td>
                        <td class='forumheader3' style='text-align:right'>".
                    $sMax."&nbsp;</td>
                    </tr>\n";
            }
            $text .="</table><br />\n";
        }
        return $text;
    }

}

?>