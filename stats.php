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
|     $Source: /cvs_backup/e107_0.7/stats.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:45 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);

if(!$pref['log_activate']){
        if(ADMIN){
                $text = "<div style='text-align:center'>".LAN_371."</div>";
        }else{
                $text = "<div style='text-align:center'>".LAN_372."</div>";
        }
        $ns -> tablerender(LAN_132, $text);
        require_once(FOOTERF);
        exit;
}

$header = "colspan='2' class='forumheader3'";
$leftcolumn = "style='width:25%' class='forumheader3'";
$rightcolumn = "style='width:75%; text-align:right' class='forumheader3'";

$text = "<div style='text-align:center'>\n<table style='width:95%' class='fborder'>\n";

if($sql -> db_Select("stat_counter", "*", "ORDER BY counter_date", "no-where")){
        $row = $sql -> db_Fetch();
        $tmp = explode("-", $row['counter_date']);
        $tmp2 = getdate(mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]));
        $logstart = $tmp[2]." ".$tmp2['month']." ".$tmp[0];
        $logdays = intval(abs(((((strtotime ($logstart) - time())/60)/60)/24)));
}else{
        $logstart = LAN_373;
        $logdays = 0;
}

//$date_secs = strtotime ($logstart); echo $date_secs;
//$timediff = (strtotime ($logstart) - time());


$text .= "<tr>\n<td $leftcolumn>".LAN_374."</td>\n<td $rightcolumn><b>".$logstart."</b> ($logdays ".LAN_418.")</td>\n</tr>\n";
$action = e_QUERY;

$total_unique_views = $sql -> db_Count("SELECT sum(counter_unique) FROM ".MPREFIX."stat_counter", "generic");
$total_page_views = $sql -> db_Count("SELECT sum(counter_total) FROM ".MPREFIX."stat_counter", "generic");

$daily_average = @round(($total_page_views/$logdays), 0);
$weekly_average = @round(($total_page_views/($logdays/7)), 0);
$monthly_average = @round(($total_page_views/($logdays/30)), 0);

$text .= "<tr>\n<td $leftcolumn>".LAN_124."</td>\n<td $rightcolumn><b>".($total_unique_views ? $total_unique_views : "&nbsp;")."</b></td>\n</tr>\n<tr>\n<td $leftcolumn>".LAN_125."</td>\n<td $rightcolumn><b>".($total_page_views? $total_page_views : "&nbsp;")."</b></td>\n</tr>\n

<tr>
<td $leftcolumn>".LAN_419.":</td>
<td $rightcolumn>".($daily_average ? $daily_average : LAN_422)."</td>
</tr>

<tr>
<td $leftcolumn>".LAN_420.":</td>
<td $rightcolumn>".($weekly_average ? $weekly_average : LAN_422)."</td>
</tr>

<tr>
<td $leftcolumn>".LAN_421.":</td>
<td $rightcolumn>".($monthly_average ? $monthly_average : LAN_422)."</td>
</tr>


</table>\n<br />
";

$sql -> db_Select_gen("SELECT counter_url, sum(counter_unique) FROM ".MPREFIX."stat_counter GROUP BY counter_url");
$text .= ($action == 1 ? parse_data($row, 0, $total_unique_views, 1, LAN_126) : parse_data($row, 10, $total_unique_views, 1, LAN_126));

$sql -> db_Select_gen("SELECT counter_url, sum(counter_total) FROM ".MPREFIX."stat_counter GROUP BY counter_url");
$text .= ($action == 2 ? parse_data($row, 0, $total_page_views, 2, LAN_127) : parse_data($row, 10, $total_page_views, 2, LAN_127));

$con = new convert;
$tmp = str_replace("10", $pref['log_lvcount'], LAN_376);
$text .= "<table style='width:95%' class='fborder'>
<tr>
<td class='forumheader3'><b>$tmp</b></td>
</tr>";

$sql -> db_Select("stat_last", "*", "ORDER BY stat_last_date DESC LIMIT 0,".$pref['log_lvcount'], "no_where");

while($row = $sql-> db_Fetch()){
        extract($row);
        $datestamp = $con -> convert_date($stat_last_date, "short");
        $text .= "<tr>\n<td class='forumheader3'><span class='smalltext'>".$datestamp.": ".$stat_last_info."</span></td>\n</tr>";
}
$text .= "\n</table>\n<br />";

$total_browsers = $sql -> db_Count("SELECT sum(info_count) FROM ".MPREFIX."stat_info WHERE info_type='1'", "generic");
$sql -> db_Select_gen("SELECT info_name, SUM(info_count) FROM ".MPREFIX."stat_info WHERE info_type='1' GROUP BY info_name");
$text .= ($action == 3 ? parse_data($row, 0, $total_browsers, 3, LAN_128) : parse_data($row, 10, $total_browsers, 3, LAN_128));

$total_os = $sql -> db_Count("SELECT sum(info_count) FROM ".MPREFIX."stat_info WHERE info_type='2'", "generic");
$sql -> db_Select_gen("SELECT info_name, SUM(info_count) AS Total FROM ".MPREFIX."stat_info WHERE info_type='2' GROUP BY info_name");
$text .= ($action == 4 ? parse_data($row, 0, $total_os, 4, LAN_129) : parse_data($row, 10, $total_os, 4, LAN_129));

$total_country = $sql -> db_Count("SELECT sum(info_count) FROM ".MPREFIX."stat_info WHERE info_type='4'", "generic");
$sql -> db_Select_gen("SELECT info_name, SUM(info_count) AS Total FROM ".MPREFIX."stat_info WHERE info_type='4' GROUP BY info_name");
$text .= ($action == 5 ? parse_data($row, 0, $total_os, 5, LAN_130) : parse_data($row, 10, $total_os, 5, LAN_130));

$total_refer = $sql -> db_Count("SELECT sum(info_count) FROM ".MPREFIX."stat_info WHERE info_type='6'", "generic");
$sql -> db_Select_gen("SELECT info_name, SUM(info_count) AS Total FROM ".MPREFIX."stat_info WHERE info_type='6' GROUP BY info_name");
$text .= ($action == 6 ? parse_data($row, 0, $total_refer, 6, LAN_131) : parse_data($row, 10, $total_refer, 6, LAN_131));

$total_res = $sql -> db_Count("SELECT sum(info_count) FROM ".MPREFIX."stat_info WHERE info_type='5'", "generic");
$sql -> db_Select_gen("SELECT info_name, SUM(info_count) AS Total FROM ".MPREFIX."stat_info WHERE info_type='5' GROUP BY info_name");
$text .= ($action == 7 ? parse_data($row, 0, $total_res, 7, LAN_379) : parse_data($row, 10, $total_res, 7, LAN_379));

$text .= "</div>";

$ns -> tablerender(LAN_132, $text);
require_once(FOOTERF);

function parse_data($row, $amount, $total, $action_n, $lan){
        global $sql, $action;

        $str .= "<table style='width:95%' class='fborder'>\n<tr>\n<td colspan='4' class='forumheader3'><b>$lan</b> ".
        ($action == $action_n ? LAN_377 : LAN_378.(($action_n == 6 && ADMIN) || $action_n != 6 ? " ( <a href='".e_SELF."?$action_n'>".LAN_375."</a> )" : ""))."</td>\n</tr>";


        while($row = $sql -> db_Fetch()){
                $data[$c][0] = $row[1];
                $data[$c][1] = $row[0];
                $data[$c][2] = @round(($data[$c][0]/$total)*100,2);
                $c++;
        }
        if($c){
                rsort($data);
        }
        $c=0;
        if(!$amount && $amount != 6){
                while($data[$c][0]){
                        if($action_n == 3 || $action_n == 4){
                                $imagepath = e_IMAGE."log/";
                                if(eregi("windows", $data[$c][1])){ $image = $imagepath."windows.png";
                                }else if(eregi("netscape", $data[$c][1])){ $image = $imagepath."netscape.png";
                                }else if(eregi("konqueror", $data[$c][1])){ $image = $imagepath."konqueror.png";
                                }else if(eregi("opera", $data[$c][1])){ $image = $imagepath."opera.png";
                                }else if(eregi("links", $data[$c][1]) || eregi("lynx",$data[$c][1])){ $image = $imagepath."lynx.png";
                                }else if(eregi("mac", $data[$c][1])){ $image = $imagepath."mac.png";
                                }else if(eregi("explorer", $data[$c][1])){ $image = $imagepath."explorer.png";
                                }else if(eregi("firebird", $data[$c][1])){ $image = $imagepath."firebird.png";
                                }else if(eregi("firefox", $data[$c][1])){ $image = $imagepath."firefox.png";
                                }else if(file_exists($imagepath.strtolower($data[$c][1])).".png"){ $image = $imagepath.strtolower($data[$c][1]).".png";}else{unset($image);}
                        }

                        $tmp = explode(".", $data[$c][2]); $width = $tmp[0];
                        if(eregi("http://", $data[$c][1])){
                                $data[$c][1] = "<a href='".$data[$c][1]."'>".$data[$c][1]."</a>";
                        }
                        $str .= "<tr>\n<td style='width:25%' class='forumheader3'>";

                        if($image){
                                $str .= "<img src='$image' alt='' /> ";
                        }
                        $str .= $data[$c][1]."</td>\n<td style='width:55%' class='forumheader3'>\n<img src='".THEME."images/bar2edge.gif' width='1' height='8' alt='' /><img src='".THEME."images/bar2.gif' style='width:".$width."%' height='8' alt='' /><img src='".THEME."images/bar2edge.gif' width='1' height='8' alt='' />\n</td>\n<td style='width:10%; text-align:center' class='forumheader3'>".$data[$c][0]."</td>\n<td style='width:10%; text-align:center' class='forumheader3'>".$data[$c][2]."%</td>\n</tr>\n";
                        $c++;
                }
        }else{
                for($r=0; $r<=9; $r++){
                        if($data[$c][0]){
                                if($action_n == 3 || $action_n == 4){
                                        $imagepath = e_IMAGE."log/";
                                        if(eregi("windows", $data[$c][1])){ $image = $imagepath."windows.png";
                                        }else if(eregi("netscape", $data[$c][1])){ $image = $imagepath."netscape.png";
                                        }else if(eregi("konqueror", $data[$c][1])){ $image = $imagepath."konqueror.png";
                                        }else if(eregi("opera", $data[$c][1])){ $image = $imagepath."opera.png";
                                        }else if((eregi("links", $data[$c][1]) || eregi("lynx",$data[$c][1])) && !eregi("php", $data[$c][1])){ $image = $imagepath."lynx.png";
                                        }else if(eregi("mac", $data[$c][1])){ $image = $imagepath."mac.png";
                                        }else if(eregi("explorer", $data[$c][1])){ $image = $imagepath."explorer.png";
                                        }else if(eregi("firebird", $data[$c][1])){ $image = $imagepath."firebird.png";
                                        }else if(eregi("firefox", $data[$c][1])){ $image = $imagepath."firefox.png";
                                        }else if(file_exists($imagepath.strtolower($data[$c][1])).".png"){ $image = $imagepath.strtolower($data[$c][1]).".png";}else{unset($image);}
                                }


                                $tmp = explode(".", $data[$c][2]); $width = $tmp[0];
                                if(eregi("http://", $data[$c][1])){
                                        $data[$c][1] = "<a href='".$data[$c][1]."' rel='external'>".$data[$c][1]."</a>";
                                }
                                $str .= "<tr>\n<td style='width:25%' class='forumheader3'>";

                                if($image){
                                        $str .= "<img src='$image' alt='' /> ";
                                }

                                $str .= $data[$c][1]."</td>\n<td style='width:55%; white-space:nowrap;' class='forumheader3'>\n<img src='".THEME."images/bar2edge.gif' width='1' height='8' alt='' /><img src='".THEME."images/bar2.gif' style='width:".$width."%' height='8' alt='' /><img src='".THEME."images/bar2edge.gif' width='1' height='8' alt='' />\n</td>\n<td style='width:10%; text-align:center' class='forumheader3'>".$data[$c][0]."</td>\n<td style='width:10%; text-align:center' class='forumheader3'>".$data[$c][2]."%</td>\n</tr>\n";
                                $c++;
                        }
                }
        }



        $str .= "</table><br />";
        return $str;
}
?>