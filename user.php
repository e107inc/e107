<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/user.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");

if(strstr(e_QUERY, "delp")){
	$tmp = explode(".", e_QUERY);
	if(USERID == $tmp[1]){
		$sql -> db_Select("user", "user_sess", "user_id='". USERID."'");
		$row = $sql -> db_Fetch(); extract($row);
		@unlink(e_FILE."public/avatars/".$user_sess);
		$sql -> db_Update("user", "user_sess='' WHERE user_id='".USERID."' ");
		header("location:".e_SELF."?id.".USERID);
		exit;
	}
}

require_once(HEADERF);

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
Show: ";

if($records == 10){
	$text .= "<select name='records' class='tbox'>
<option selected>10</option>
<option>20</option>
<option>30</option>
</select>  ";
}else if($records == 10){
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
<option>DESC</option>
<option selected>ASC</option>
</select>";
}else{
	$text .= "<select name='order' class='tbox'>
<option selected>DESC</option>
<option>ASC</option>
</select>";
}

$text .= " <input class='button' type='submit' name='submit' value='Go' />
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
	global $sql, $id;
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
		$level = getlevel($user_join, $user_forums, $user_comments, $user_chats, $user_visits);

		$datestamp = $gen->convert_date($user_join, "long");
		$lastvisit = ($user_lastvisit ? $gen->convert_date($user_lastvisit, "long") : "<i>".LAN_401."</i>");
		$daysregged = max(1, round((time() - $user_join)/86400))." ".LAN_405;
		$str = "
		<div style='text-align:center'>
		<table style='width:95%' class='fborder'>
		<tr><td colspan='2' class='fcaption' style='text-align:center'>".LAN_142." ".$user_id.": ".$user_name."</td></tr>
		<tr><td rowspan='8' class='forumheader3' style='width:20%; vertical-align:middle; text-align:center'>";
		
		
		if($user_sess && file_exists(e_FILE."public/avatars/".$user_sess)){
			$str .= "<img src='".e_FILE."public/avatars/".$user_sess."' alt='' />";

			if(ADMIN){
				$str .= "<br /><span class='smalltext'>".$user_sess."</span>";
			}

			if(USERID == $user_id || ADMIN){
				$str .= "<br /><br /><span class='smalltext'>[ <a href='".e_SELF."?delp.".USERID."'>".LAN_413."</a> ]</span>";
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
			<table style='width:100%'><tr><td style='width:30%'> <img src='".e_IMAGE."generic/hme.png' alt=''  style='vertical-align:middle' /> ".LAN_144."</td><td style='width:70%; text-align:right'>".($user_homepage ? "<a href=\"javascript:openwindow('".$user_homepage."')\">$user_homepage</a>" : "<i>".LAN_401."</i>")."</td></tr></table>
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



		//	extended fields ...

		if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){
			$row = $sql -> db_Fetch();
			$user_entended = unserialize($row[0]);

			$str .= "<tr><td colspan='2' class='forumheader'>".LAN_410."</td></tr>";

			$user_prefs = unserialize($user_prefs);
			while(list($key, $u_entended) = each($user_entended)){
				$str .= "<tr><td style='width:40%' class='forumheader3'>".$u_entended."</td>
				<td style='width:60%' class='forumheader3'>".($user_prefs[$u_entended] ? $user_prefs[$u_entended] : "<i>".LAN_401."</i>")."</td></tr>";
			}
		}




		//	end extended fields

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
		</tr>

		<tr>
		<td style='width:30%'class='forumheader3'>".LAN_149."</td>
		<td style='width:70%'class='forumheader3'>$user_forums ( ".$forumper."% )</td>
		</tr>

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
		}else if(ADMIN){
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
		
function getlevel($user_join, $user_forums, $user_comments, $user_chats, $user_visits){
	global $pref;
	$level = ceil((($user_forums*5) + ($user_comments*5) + ($user_chats*2) + $user_visits)/4);
	$points = $level;
	if($level <= 100){
		$level = 0;
	}else if($level >= 101 && $level <= 1000){
		$level = 1;
	}else if($level >= 1001 && $level <= 2500){
		$level = 2;
	}else if($level >= 2501 && $level <= 4500){
		$level = 3;
	}else if($level >= 4501 && $level <= 7000){
		$level = 4;
	}else if($level >= 7001 && $level <= 10000){
		$level = 5;
	}else if($level >= 10001 && $level <= 13500){
		$level = 6;
	}else if($level >= 13501 && $level <= 17500){
		$level = 7;
	}else if($level >= 17501 && $level <= 22000){
		$level = 8;
	}else if($level >= 22001 && $level <= 27000){
		$level = 9;
	}else if($level >= 27001){
		$level = 10;
	}
		
	if($pref['forum_levels']){
		$tmp = explode(",", $pref['forum_levels']);
		$LEVEL = "[ ".trim(chop($tmp[$level]))." ]";
	}else{
		for($a=0; $a<=($level-1); $a++){
			$LEVEL .= "<img src='".e_IMAGE."generic/star3.gif' alt='rating' />";
		}
	}
	return (!$level ? "<i>".LAN_407."</i>" : $LEVEL)." ( $points ".LAN_409." )";
}

require_once(FOOTERF);
?>