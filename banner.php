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
|     $Source: /cvs_backup/e107_0.7/banner.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:09:30 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");

if(e_QUERY){
        $sql -> db_Select("banner", "*", "banner_id='".e_QUERY."' ");
        $row = $sql -> db_Fetch(); extract($row);
        $ip = getip();
        $newip = (preg_match("/".$ip."\^/", $banner_ip) ? $banner_ip : $banner_ip.$ip."^");
        $sql -> db_Update("banner", "banner_clicks=banner_clicks+1, banner_ip='$newip' WHERE banner_id='".e_QUERY."' ");
        header("location: ".$banner_clickurl);
        exit;
}

require_once(HEADERF);

if(IsSet($_POST['clientsubmit'])){

        if(!$sql -> db_Select("banner", "*", "banner_clientlogin='".$_POST['clientlogin']."' AND banner_clientpassword='".$_POST['clientpassword']."' ")){
                $ns -> tablerender("Error", "<br /><div style='text-align:center'>".LAN_20."</div><br />");
                require_once(FOOTERF);
                exit;
        }

        $row = $sql -> db_Fetch(); extract($row);

        $banner_total = $sql -> db_Select("banner", "*", "banner_clientname='$banner_clientname' ");

        $text = "<table class='fborder' style='width:98%'>
        <tr><td colspan='7' style='text-align:center' class='fcaption'>".LAN_21."</td></tr>
        <tr>
        <td class='forumheader' style='text-align:center'><span class='smallblacktext'>".LAN_22."</span></td>
        <td class='forumheader' style='text-align:center'><span class='smallblacktext'>".LAN_23."</span></td>
        <td class='forumheader' style='text-align:center'><span class='smallblacktext'>".LAN_24."</span></td>
        <td class='forumheader' style='text-align:center'><span class='smallblacktext'>".LAN_25."</span></td>
        <td class='forumheader' style='text-align:center'><span class='smallblacktext'>".LAN_26."</span></td>
        <td class='forumheader' style='text-align:center'><span class='smallblacktext'>".LAN_27."</span></td>
        <td class='forumheader' style='text-align:center'><span class='smallblacktext'>".LAN_28."</span></td>
        </tr>";

        if(!$banner_total){
                $text .= "<tr>
                <td colspan='7' class='forumheader2' style='text-align:center'>".LAN_29."</td>";
        }else{
                while($row = $sql-> db_Fetch()){
                        extract($row);

                        $clickpercentage = ($banner_clicks && $banner_impressions ? round(($banner_clicks / $banner_impressions) * 100)."%" : "-");
                        $impressions_left = ($banner_impurchased ? $banner_impurchased - $banner_impressions : LAN_30);
                        $impressions_purchased = ($banner_impurchased ? $banner_impurchased : LAN_30);

                        $start_date = ($banner_startdate ? strftime("%d %B %Y", $banner_startdate) : LAN_31);
                        $end_date = ($banner_enddate ? strftime("%d %B %Y", $banner_enddate) : LAN_31);

                        $text.="<tr>
                        <td class='forumheader3' style='text-align:center'>".$banner_clientname."</td>
                        <td class='forumheader3' style='text-align:center'>".$banner_id."</td>
                        <td class='forumheader3' style='text-align:center'>".$banner_clicks."</td>
                        <td class='forumheader3' style='text-align:center'>".$clickpercentage."</td>
                        <td class='forumheader3' style='text-align:center'>".$banner_impressions."</td>
                        <td class='forumheader3' style='text-align:center'>".$impressions_purchased."</td>
                        <td class='forumheader3' style='text-align:center'>".$impressions_left."</td>
                        </tr>
                        <td colspan='7' class='forumheader3' style='text-align:center'>

                        ".LAN_36. ($banner_active ? LAN_32 : "<b>".LAN_33."</b>")." |

                        ".LAN_36. $start_date.", ".LAN_34.": ".$end_date."</td></tr>";

                        if($banner_ip){
                                $tmp = explode("^", $banner_ip);
                                $text .= "<tr><td class='forumheader3'>
                                ".LAN_35.": ".(count($tmp)-1)."</td>
                                <td colspan='6' class='forumheader3'>";
                                for($a=0; $a<=(count($tmp)-2); $a++){
                                        $text .= $tmp[$a]."<br />";
                                }
                        }


                        $text .= "</td>
                        <tr><td colspan='8'>&nbsp;</td></tr>";
                }
        }

        $text .= "</table>";

        echo $text;

        require_once(FOOTERF);
        exit;
}

$text = "<div style='align:center'>\n
<form method='post' action='".e_SELF."'>\n
<table style='width:40%'>
<tr>
<td style='width:15%' class='defaulttext'>".LAN_16."</td>
<td><input class='tbox' type='text' name='clientlogin' size='30' value='$id' maxlength='20' />\n</td>
</tr>
<tr>
<td style='width:15%' class='defaulttext'>".LAN_17."</td>
<td><input class='tbox' type='password' name='clientpassword' size='30' value='' maxlength='20' />\n</td>
</tr>
<tr>
<td style='width:15%'></td>
<td>
<input class='button' type='submit' name='clientsubmit' value='".LAN_18."' />
</td>
</tr>
</table></form></div>";
$ns -> tablerender(LAN_19, $text);
require_once(FOOTERF);


?>