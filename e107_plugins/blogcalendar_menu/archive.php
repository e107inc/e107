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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/blogcalendar_menu/archive.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:06 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
| Based on code by: Thomas Bouve (crahan@gmx.net)
*/

require_once("../../class2.php");
require_once(e_HANDLER."userclass_class.php");

$lan_file=e_PLUGIN."blogcalendar_menu/languages/".e_LANGUAGE.".php";
if(file_exists($lan_file)){
        require_once($lan_file);
} else {
        require_once(e_PLUGIN."blogcalendar_menu/languages/English.php");
};
require_once("calendar.php");
require_once("functions.php");
require_once(HEADERF);

// ---------------------
// initialize some cruft
// ---------------------
$sql = new db;
$prefix = e_PLUGIN."blogcalendar_menu";
$marray = array(BLOGCAL_M1,BLOGCAL_M2,BLOGCAL_M3,BLOGCAL_M4,
                BLOGCAL_M5,BLOGCAL_M6,BLOGCAL_M7,BLOGCAL_M8,
                BLOGCAL_M9,BLOGCAL_M10,BLOGCAL_M11,BLOGCAL_M12);
// if nr of rows per month is not set, default to 3
$months_per_row = $pref['blogcal_mpr']?$pref['blogcal_mpr']:"3";
$pref['blogcal_ws'] = "monday";

// -------------------------------------
// what year are we supposed to display?
// -------------------------------------
$cur_year = date("Y");
$cur_month = date("n");
$cur_day = date("j");
if(strstr(e_QUERY, "year")){
    $tmp = explode(".", e_QUERY);
    $req_year = $tmp[1];
}else{
    $req_year = $cur_year;
}


// --------------------------------
// look for the first and last year
// --------------------------------
$sql -> db_Select_gen("SELECT news_id, news_datestamp from ".MPREFIX."news ORDER BY news_datestamp LIMIT 0,1");
$first_post = $sql -> db_Fetch();
$start_year = date("Y", $first_post['news_datestamp']);
$end_year = $cur_year;


// ----------------------
// build the yearselector
// ----------------------
$year_selector = "<div class='forumheader' style='text-align: center; margin-bottom: 2px;'>";
$year_selector .= "".BLOGCAL_ARCHIV1.": <select name='activate' onChange='urljump(this.options[selectedIndex].value)' class='tbox'>";

for($i=$start_year; $i<=$end_year; $i++){
    $start = mktime(0,0,0,1,1,$req_year);
    $end = mktime(23,59,59,12,31,$req_year);
    if($sql -> db_Select("news", "news_id, news_datestamp, news_class","news_datestamp > $start AND news_datestamp < $end")){
        // create the option entry
        $year_link=$prefix."/archive.php?year.".$i;
        $year_selector .= "<option value='".$year_link."'";

        if($i == $req_year){
            $year_selector .= " selected";
            while($news = $sql -> db_Fetch()){
                                if(check_class($news['news_class'])){
                                        list($xmonth, $xday) = explode(" ",date("n j",$news['news_datestamp']));
                                        if(!$day_links[$xmonth][$xday]){
                                                $day_links[$xmonth][$xday]=e_BASE."news.php?day.".formatdate($req_year,$xmonth,$xday);
                                        }
                                }
            }
        }
        $year_selector .= ">".$i."</option>";
    }
}

$year_selector .= "</select>";


// --------------------------
// create the archive display
// --------------------------
$newline = 0;
$archive = "<div style='text-align:center'><table border='0' cellspacing='7'><tr>";
$archive .= "<td colspan='$months_per_row'><div>$year_selector</div></td></tr><tr>";
for($i=1; $i<=12; $i++){
    if(++$newline == $months_per_row+1){
        $archive .= "</tr><tr>";
        $newline = 1;
    }
    $archive .= "<td style='vertical-align:top'>";
    $archive .= "<div class='fcaption' style='text-align:center; margin-bottom:2px;'>";

    // href the current month regardless of newsposts or any month with news
    if(($req_year == $cur_year && $i == $cur_month) || $day_links[$i]){
        $archive .= "<a class='forumlink' href='".e_BASE."news.php?month.".formatDate($req_year,$i)."'>".$marray[$i-1]."</a>";
    }else{
        $archive .= $marray[$i-1];
    }

    $archive .= "</div>";
    if(($req_year == $cur_year) && ($i == $cur_month)){
        $req_day = $cur_day;
    }else{
        $req_day = "";
    }
    $archive .= "<div>".calendar($req_day, $i, $req_year, $day_links[$i], $pref['blogcal_ws'])."</div></td>\n";
}
$archive .= "</tr></table></div>";
$ns -> tablerender(BLOGCAL_L2 ."&nbsp;$req_year", $archive);

require_once(FOOTERF);
?>