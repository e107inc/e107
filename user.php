<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /user.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");

if(strstr(e_QUERY, "delp")){
        $tmp = explode(".", e_QUERY);
        if(USERID == $tmp[1] || (ADMIN && getperms("4"))){
                $sql -> db_Select("user", "user_sess", "user_id='". USERID."'");
                $row = $sql -> db_Fetch(); extract($row);
                @unlink(e_FILE."public/avatars/".$user_sess);
                $sql -> db_Update("user", "user_sess='' WHERE user_id=".$tmp[1]);
                header("location:".e_SELF."?id.".$tmp[1]);
                exit;
        }
}

require_once(HEADERF);

if(!USER){
        $ns -> tablerender(LAN_20, "<div style='text-align:center'>".LAN_416."</div>");
        require_once(FOOTERF);
        exit;
}

if(IsSet($_POST['records'])){
        $records = $_POST['records'];
        $order = $_POST['order'];
        $from = 0;
}else if(!e_QUERY){
        $records = 20;
        $from = 0;
        $order="DESC";
}else{
        $qs = explode(".", e_QUERY);
        if($qs[0] == "id"){
                $id = $qs[1];
        }else{
                $qs = explode(".", e_QUERY);
                $from = $qs[0];
                $records = $qs[1];
                $order = $qs[2];
        }
}
if($records >30){ $records = 30; }


if(IsSet($id)){

        if($id == 0){
                $text = "<div style='text-align:center'>".LAN_137." ".SITENAME."</div>";
                $ns -> tablerender("<div style='text-align:center'>".LAN_20."</div>", $text);
                require_once(FOOTERF);
                exit;
        }

        if(!$sql -> db_Select("user", "*", "user_id='".$id."' ")){
                $text = "<div style='text-align:center'>".LAN_400."</div>";
                $ns -> tablerender("<div style='text-align:center'>".LAN_20."</div>", $text);
                require_once(FOOTERF);
                exit;
        }

        $text = renderuser($sql -> db_Fetch());
        $ns -> tablerender("<div style='text-align:center'>".LAN_402."</div>", $text);
        require_once(FOOTERF);
        exit;
}

$users_total = $sql -> db_Count("user");

$text = "<div style='text-align:center'>
".LAN_138." ".$users_total."<br /><br />
<form method='post' action='".e_SELF."'>
<p>
".LAN_419.": ";

if($records == 10){
        $text .= "<select name='records' class='tbox'>
<option selected>10</option>
<option>20</option>
<option>30</option>
</select>  ";
}else if($records == 20){
        $text .= "<select name='records' class='tbox'>
<option>10</option>
<option selected>20</option>
<option>30</option>
</select>  ";
}else{
        $text .= "<select name='records' class='tbox'>
<option>10</option>
<option>20</option>
<option selected>30</option>
</select>  ";
}
$text .= LAN_139;

if($order == "ASC"){
        $text .= "<select name='order' class='tbox'>
<option>".LAN_420."</option>
<option selected>".LAN_421."</option>
</select>";
}else{
        $text .= "<select name='order' class='tbox'>
<option selected>".LAN_420."</option>
<option>".LAN_421."</option>
</select>";
}

$text .= " <input class='button' type='submit' name='submit' value='".LAN_422."' />
<input type='hidden' name='from' value='$from' />
</p>
</form>\n\n<br /><br />";



if(!$sql -> db_Select("user", "*",  "ORDER BY user_id $order LIMIT $from,$records", $mode="no_where")){
        echo "<div style='text-align:center'><b>".LAN_141."</b></div>";
}else{
        $sql2 = new db;
        if($sql2 -> db_Select("core", " e107_value", " e107_name='user_entended'")){
                $row = $sql2 -> db_Fetch();
                $user_entended = unserialize($row[0]);
        }

        $text .= "
        <table style='width:95%' class='fborder'>
        <tr>
        <td class='fcaption' style='width:2%'>&nbsp;</td>
        <td class='fcaption' style='width:20%'>".LAN_142."</td>
        <td class='fcaption' style='width:20%'>".LAN_112."</td>
        <td class='fcaption' style='width:20%'>".LAN_145."</td>
        </tr>";

        while($row = $sql -> db_Fetch()){
                $text .= renderuser($row, $user_entended, "short");
        }

        $text .= "</table>\n</div>";

}

$ns -> tablerender("<div style='text-align:center'>".LAN_140."</div>", $text);

require_once(e_HANDLER."np_class.php");
$ix = new nextprev("user.php", $from, $records, $users_total, LAN_138, $records.".".$order);

function renderuser($row, $user_entended, $mode="verbose"){

        global $sql, $id, $pref;
        extract($row);
        $aj = new textparse;
        $gen = new convert;
        if($mode != "verbose"){
                $datestamp = $gen->convert_date($user_join, "short");
                return "
                <tr>
                <td class='forumheader3' style='width:2%'><a href='".e_SELF."?id.$user_id'><img src='".e_IMAGE."generic/user.png' alt='' style='border:0' /></a></td>
                <td class='forumheader' style='width:20%'>".$user_id.": <a href='".e_SELF."?id.$user_id'>".$user_name."</a></td>
                <td class='forumheader3' style='width:20%'>".($user_hideemail && !ADMIN ? "<i>".LAN_143."</i>" : "<a href='mailto:".$user_email."'>".$user_email."</a>")."</td>
                <td class='forumheader3' style='width:20%'>$datestamp</td>
                </tr>";
        }else{

                $chatposts = $sql -> db_Count("chatbox");
                $commentposts = $sql -> db_Count("comments");
                $forumposts = $sql -> db_Count("forum_t");

                $chatper = round(($user_chats/$chatposts)*100,2);
                $commentper = round(($user_comments/$commentposts)*100,2);
                $forumper = round(($user_forums/$forumposts)*100,2);
                require_once(e_HANDLER."level_handler.php");

                $ldata = get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref);

                if(strstr($ldata[0], "IMAGE_rank_main_admin_image")){
                        $level = LAN_417;
                }else if(strstr($ldata[0], "IMAGE")){
                        $level = LAN_418;
                }else{
                        $level = $ldata[1];
                }

                $datestamp = $gen->convert_date($user_join, "long");
                $lastvisit = ($user_currentvisit ? $gen->convert_date($user_currentvisit, "long") : "<i>".LAN_401."</i>");
                $daysregged = max(1, round((time() - $user_join)/86400))." ".LAN_405;
                $str = "
                <div style='text-align:center'>
                <table style='width:95%' class='fborder'>
                <tr><td colspan='2' class='fcaption' style='text-align:center'>".LAN_142." ".$user_id.": ".$user_name."</td></tr>
                <tr><td rowspan='8' class='forumheader3' style='width:20%; vertical-align:middle; text-align:center'>";

                if($user_sess && file_exists(e_FILE."public/avatars/".$user_sess)){
                        $str .= "<img src='".e_FILE."public/avatars/".$user_sess."' alt='' />";

                        if(ADMIN && getperms("4")){
                                $str .= "<br /><span class='smalltext'>".$user_sess."</span>";
                        }

                        if(USERID == $user_id || (ADMIN && getperms("4"))){
                                $str .= "<br /><br /><span class='smalltext'>[ <a href='".e_SELF."?delp.$user_id'>".LAN_413."</a> ]</span>";
                        }

                }else{
                        $str .= LAN_408;
                }


                $str .= "</td>

                <td style='width:80%'class='forumheader3'>
                        <table style='width:100%'><tr><td style='width:30%'><img src='".e_IMAGE."generic/rname.png' alt='' style='vertical-align:middle' /> ".LAN_308."</td><td style='width:70%; text-align:right'>".($user_login ? $user_login : "<i>".LAN_401."</i>")."</td></tr></table>
                </td></tr>

                <td style='width:80%'class='forumheader3'>
                        <table style='width:100%'><tr><td style='width:30%'><img src='".e_IMAGE."generic/email.png' alt='' style='vertical-align:middle' /> ".LAN_112."</td><td style='width:70%; text-align:right'>".($user_hideemail && !ADMIN ? "<i>".LAN_143."</i>" : $user_email."</a>")."</td></tr></table>
                </td></tr>

                <td style='width:80%'class='forumheader3'>
                        <table style='width:100%'><tr><td style='width:30%'> <img src='".e_IMAGE."generic/icq.png' alt=''  style='vertical-align:middle' /> ".LAN_115."</td><td style='width:70%; text-align:right'>".($user_icq ? $user_icq : "<i>".LAN_401."</i>")."</td></tr></table>
                </td></tr>

                <td style='width:80%'class='forumheader3'>
                        <table style='width:100%'><tr><td style='width:30%'> <img src='".e_IMAGE."generic/aim.png' alt=''  style='vertical-align:middle' /> ".LAN_116."</td><td style='width:70%; text-align:right'>".($user_aim ? $user_aim : "<i>".LAN_401."</i>")."</td></tr></table>
                </td></tr>

                <td style='width:80%'class='forumheader3'>
                        <table style='width:100%'><tr><td style='width:30%'> <img src='".e_IMAGE."generic/msn.png' alt=''  style='vertical-align:middle' /> ".LAN_117."</td><td style='width:70%; text-align:right'>".($user_msn ? $user_msn : "<i>".LAN_401."</i>")."</td></tr></table>
                </td></tr>

                <td style='width:80%'class='forumheader3'>
                        <table style='width:100%'><tr><td style='width:30%'> <img src='".e_IMAGE."generic/hme.png' alt=''  style='vertical-align:middle' /> ".LAN_144."</td><td style='width:70%; text-align:right'>".($user_homepage ? "<a href=\"javascript:open_window('".$user_homepage."')\">$user_homepage</a>" : "<i>".LAN_401."</i>")."</td></tr></table>
                </td></tr>

                <td style='width:80%'class='forumheader3'>
                        <table style='width:100%'><tr><td style='width:30%'> <img src='".e_IMAGE."generic/location.png' alt=''  style='vertical-align:middle' /> ".LAN_119."</td><td style='width:70%; text-align:right'>".($user_location ? $user_location : "<i>".LAN_401."</i>")."</td></tr></table>
                </td></tr>";


                if($user_birthday != "" && $user_birthday != "0000-00-00" && ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $user_birthday, $regs)){
                                $user_birthday = "$regs[3].$regs[2].$regs[1]";
                }else{
                        $user_birthday = "<i>".LAN_401."</i>";
                }

                $str .= "<td style='width:80%'class='forumheader3'>
                        <table style='width:100%'><tr><td style='width:30%'> <img src='".e_IMAGE."generic/bday.png' alt=''  style='vertical-align:middle' /> ".LAN_118."</td><td style='width:70%; text-align:right'>$user_birthday</td></tr></table>
                </td></tr>

                ".($user_signature ? "<tr><td colspan='2' class='forumheader3' style='text-align:center'><i>".$aj -> tpa($user_signature)."</i></td></tr>" : "");



                //        extended fields ...

                if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){

                // added by cam
                require_once(e_HANDLER."user_extended.php");

                        $row = $sql -> db_Fetch();
                        $user_entended = unserialize($row[0]);

                        $str .= "<tr><td colspan='2' class='forumheader'>".LAN_410."</td></tr>";

                        $user_prefs = unserialize($user_prefs);
                        while(list($key, $u_entended) = each($user_entended)){
							$ut = explode("|", $u_entended);
							if(!$ut[5] || check_class($ut[5])==TRUE){
                                $str .= "<tr><td style='width:40%' class='forumheader3'>".user_extended_name($u_entended)."</td>
                                <td style='width:60%' class='forumheader3'>".($user_prefs[$u_entended] ? $user_prefs[$u_entended] : "<i>".LAN_401."</i>")."</td></tr>";
							}
                        }
                }




                //        end extended fields

                $str .= "<tr><td colspan='2' class='forumheader'>".LAN_403."</td></tr>

                <tr>
                <td style='width:30%'class='forumheader3'>".LAN_145."</td>
                <td style='width:70%'class='forumheader3'>$datestamp ( $daysregged )</td>
                </tr>

                <tr>
                <td style='width:30%'class='forumheader3'>".LAN_147."</td>
                <td style='width:70%'class='forumheader3'>$user_chats ( ".$chatper."% )</td>
                </tr>

                <tr>
                <td style='width:30%'class='forumheader3'>".LAN_148."</td>
                <td style='width:70%'class='forumheader3'>$user_comments ( ".$commentper."% )</td>
                </tr>";

				if($user_comments){
					$str .= "
					<tr>
					<td colspan='2' class='forumheader3'><a href='".e_BASE."userposts.php?0.comments.".$user_id."'>".LAN_423."</a></td>
					</tr>";
				}
				$str .= "

                <tr>
                <td style='width:30%'class='forumheader3'>".LAN_149."</td>
                <td style='width:70%'class='forumheader3'>$user_forums ( ".$forumper."% )</td>
                </tr>";

				if($user_forums){
					$str .= "
					<tr>
					<td colspan='2' class='forumheader3'><a href='".e_BASE."userposts.php?0.forums.".$user_id."'>".LAN_424."</a></td>
					</tr>";
				}
				$str .= "

                <tr>
                <td style='width:30%'class='forumheader3'>".LAN_146."</td>
                <td style='width:70%'class='forumheader3'>$user_visits</td>
                </tr>

                <tr>
                <td style='width:30%'class='forumheader3'>".LAN_404."</td>
                <td style='width:70%'class='forumheader3'>$lastvisit</td>
                </tr>

                <tr>
                <td style='width:30%'class='forumheader3'>".LAN_406."</td>
                <td style='width:70%'class='forumheader3'>$level</td>
                </tr>";

                if(USERID == $user_id){
                        $str .= "<tr><td colspan='2' class='forumheader3' style='text-align:center'><a href='".e_BASE."usersettings.php'>".LAN_411."</a></td></tr>";
                }else if(ADMIN && getperms("4") && !$user_admin){
                        $str .= "<tr><td colspan='2' class='forumheader3' style='text-align:center'><a href='".e_BASE."usersettings.php?".$user_id."'>".LAN_412."</a></td></tr>";
                }

                $sql -> db_Select("user", "user_id, user_name",  "ORDER BY user_id ASC", "no-where");
                $c = 0;
                while($row = $sql -> db_Fetch()){
                        $array[$c]['id'] = $row['user_id'];
                        $array[$c]['name'] = $row['user_name'];
                        if($row['user_id'] == $id){
                                $prevuser['id'] = $array[$c-1]['id'];
                                $prevuser['name'] = $array[$c-1]['name'];
                                $row = $sql -> db_Fetch();
                                $nextuser['id'] = $row['user_id'];
                                $nextuser['name'] = $row['user_name'];
                                break;
                        }
                        $c++;
                }

                $str .= "<tr><td colspan='2' class='forumheader3' style='text-align:center'>
                <table style='width:95%'>
                <tr>
                <td style='width:50%'>".($prevuser['id'] ? "<< ".LAN_414." [ <a href='".e_SELF."?id.".$prevuser['id']."'>".$prevuser['name']."</a> ]" : "&nbsp;")."</td>
                <td style='width:50%; text-align:right'>".($nextuser['id'] ? "[ <a href='".e_SELF."?id.".$nextuser['id']."'>".$nextuser['name']."</a> ] ".LAN_415." >>" : "&nbsp;")."</td>
                </tr>
                </table>
                </td>
                </tr>";
                $str .= "</table></div>";
                return $str;

        }
}

require_once(FOOTERF);
?>