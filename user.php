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

	renderuser($sql -> db_Fetch());
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
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".LAN_140."</div>", $text);

if(!$sql -> db_Select("user", "*",  "ORDER BY user_id $order LIMIT $from,$records", $mode="no_where")){
	echo "<div style='text-align:center'><b>".LAN_141."</b></div>";
}else{
	$sql2 = new db;
	if($sql2 -> db_Select("core", " e107_value", " e107_name='user_entended'")){
		$row = $sql2 -> db_Fetch();
		$user_entended = unserialize($row[0]);
	}
	while($row = $sql -> db_Fetch()){
		renderuser($row, $user_entended);
	}
}
require_once(e_HANDLER."np_class.php");
$ix = new nextprev("user.php", $from, $records, $users_total, LAN_138, $records.".".$order);

function renderuser($row, $user_entended){
	global $sql;
	$sql2 = new db;
	extract($row);
	$caption = LAN_142." ".$user_id.": ".$user_name;
	$text = "<table style='width:95%'>";

	if($user_image){
		require_once(e_HANDLER."avatar_handler.php");
		$user_image = avatar($user_image);
		$text .= "<tr><td colspan='2'><div class='spacer'><img src='".$user_image."' alt='' /></div></td></tr>";
	}


	if($user_login != ""){
		$text .= "<tr><td style='width:40%'>".LAN_308."</td><td style='width:60%'>".$user_login."</td></tr>";
	}


	$text .= "<tr><td style='width:40%'>".LAN_112."</td>
	<td style='width:60%'>";

	if($user_hideemail == 1 && !ADMIN){
		$text .= LAN_143;
	}else{
		$text .= "<a href='mailto:".$user_email."'>".$user_email."</a>";
	}
	$text .= "</td></tr>";
	if($user_homepage != "" && $user_homepage != "http://"){
		$text .= "<tr><td style='width:40%'>".LAN_144."</td>
		<td style='width:60%'><a href='".$user_homepage."'>".$user_homepage."</a></td></tr>";
	}
	if($user_icq != ""){
		$text .= "<tr><td style='width:40%'>".LAN_115."</td>
		<td style='width:60%'>".$user_icq."</td></tr>";
	}
		
	if($user_aim != ""){
		$text .= "<tr><td style='width:40%'>".LAN_116."</td>
		<td style='width:60%'>".$user_aim."</td></tr>";
	}

	if($user_msn != ""){
		$text .= "<tr><td style='width:40%'>".LAN_117."</td>
		<td style='width:60%'>".$user_msn."</td></tr>";
	}

	if($user_location != ""){
		$text .= "<tr><td style='width:40%'>".LAN_119."</td>
		<td style='width:60%'>".$user_location."</td></tr>";
	}

	if($user_birthday != "" && $user_birthday != "0000-00-00"){

	if (ereg ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $user_birthday, $regs)) {
		$user_birthday = "$regs[3].$regs[2].$regs[1]";
	}

	$text .= "<tr><td style='width:40%'>".LAN_118."</td>
		<td style='width:60%'>".$user_birthday."</td></tr>";
	}

	$aj = new textparse();

	if($user_signature != ""){
		$user_signature = $aj -> tpa($user_signature);
		$text .= "<tr><td style='width:40%; vertical-align:top'>".LAN_120."</td>
		<td style='width:60%'>".$user_signature."</td></tr>";
	}

	if(is_array($user_entended)){
		$user_prefs = unserialize($user_prefs);
		while(list($key, $u_entended) = each($user_entended)){
			$text .= "<tr><td style='width:40%'>".$u_entended.":</td>
			<td style='width:60%'>".$user_prefs[$u_entended]."</td></tr>";
		}
	}



	$gen = new convert;
	$datestamp = $gen->convert_date($user_join, "long");

	$text .= "<tr><td style='width:40%'>".LAN_145."</td>
	<td style='width:60%'>".$datestamp."</td></tr>

	<tr><td style='width:40%'>".LAN_146."</td>
	<td style='width:60%'>".$user_visits."</td></tr>

	<tr><td style='width:40%'>".LAN_147."</td>
	<td style='width:60%'>".$user_chats."</td></tr>

	<tr><td style='width:40%'>".LAN_148."</td>
	<td style='width:60%'>".$user_comments."</td></tr>

	<tr><td style='width:40%'>".LAN_149."</td>
	<td style='width:60%'>".$user_forums."</td></tr>";
	$text .= "</table>";
	$ns = new e107table;
	$ns -> tablerender($caption, $text);
}

require_once(FOOTERF);
?>