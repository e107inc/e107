<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/template.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../e107_config.php");
mysql_connect($mySQLserver, $mySQLuser, $mySQLpassword);
mysql_select_db($mySQLdefaultdb);

echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><e107 resetcore></title>
<link rel="stylesheet" href="../e107_docs/style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
</head>
<body>

<div class='mainbox'>

<a href="http://e107.org"><img src="../e107_images/logo.png" alt="Logo" style="border:0" /></a>
<br /><br />
<span class="smalltext">©Steve Dunstan 2002-2004. See gpl.txt for license details.</span><br /><br /><br />


<?php

if(IsSet($_POST['usubmit'])){

	$a_name = $_POST['a_name'];
	$a_password = md5($_POST['a_password']);

	if($result = mysql_query("SELECT * FROM ".$mySQLprefix."user WHERE user_name='$a_name' AND user_password='$a_password' AND user_perms=0")){
		if($row = mysql_fetch_array($result)){
			extract($row);

			$result = mysql_query("SELECT * FROM ".$mySQLprefix."core WHERE e107_name='pref_backup' ");
			$bu_exist = ($row = mysql_fetch_array($result) ? TRUE : FALSE);
			
			$admin_directory = "e107_admin";

			echo "<span class='headertext2'><b>Please select which method you want to use, then click the button to proceed ...</b></span><br /><br /><br /><br />
			<div style='text-align:center'>
			<form method='post' action='".$_SERVER['PHP_SELF']."'>
			<input type='radio' name='mode' value='1'> <span class='headertext'>Manually edit core values</span><br />
			<input type='radio' name='mode' value='2'> <span class='headertext'>Reset core to default values</span><br />".
			($bu_exist ? "<input type='radio' name='mode' value='3'> <span class='headertext'>Restore core backup</span>" : " <span class='headertext'>[ No core backup found - unable to restore core ]</span>")."<br /><br /><input class='button' type='submit' name='reset_core_sub' value='Select method then click here to continue' />

			<input type='hidden' name='a_name' value='$a_name' />
			<input type='hidden' name='a_password' value='$a_password' />

			</form>
			</div>";
			$END = TRUE;
		}else{
			$message = "<b>Administrator not found in database / incorrect password / insufficient permissions - aborting.</b><br />";
			$END = TRUE;
		}
	}else{
		$message = "<b>Administrator not found in database / incorrect password / insufficient permissions - aborting.</b><br />";
		$END = TRUE;
	}
}


if(IsSet($_POST['reset_core_sub']) && $_POST['mode'] == 2){
	$a_name = $_POST['a_name'];
	$a_password = $_POST['a_password'];
	if(!$result = mysql_query("SELECT * FROM ".$mySQLprefix."user WHERE user_name='$a_name' AND user_password='$a_password' AND user_perms='0' ")){
		exit;
	}
	$at = ($row = mysql_fetch_array($result) ? TRUE : FALSE);
	if(!$at){ exit; }

	$tmpr = substr(str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']), 1);
	$root = "/".substr($tmpr, 0, strpos($tmpr, "/"))."/";
	define("e_HTTP", $root);
	$admin_directory = "e107_admin";
	$url_prefix=substr($_SERVER['PHP_SELF'],strlen(e_HTTP),strrpos($_SERVER['PHP_SELF'],"/")+1-strlen(e_HTTP));
	$num_levels=substr_count($url_prefix,"/");
	for($i=1;$i<=$num_levels;$i++){
		$link_prefix.="../";
	}

	define("e_ADMIN", e_HTTP.$admin_directory."/");
	define("e_SELF", "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	define("e_QUERY", eregi_replace("&|/?PHPSESSID.*", "", $_SERVER['QUERY_STRING']));
	define('e_BASE',$link_prefix);
	$e_path = (!strpos($_SERVER['SCRIPT_FILENAME'], ".php") ? $_SERVER['PATH_TRANSLATED'] : $_SERVER['SCRIPT_FILENAME']);
	define("e_PATH", $e_path);
	require_once(e_BASE.$FILES_DIRECTORY."def_e107_prefs.php");

	$tmp = addslashes(serialize($pref));

	mysql_query("DELETE FROM ".$mySQLprefix."core WHERE e107_name='pref'");
	if(!mysql_query("INSERT INTO ".$mySQLprefix."core VALUES ('pref', '$tmp')")){
		$message = "Rebuild failed ...";
		$END = TRUE;
	}else{
		$message = "Core reset. <br /><br /><a href='../index.php'>Click here to continue</a>";
		$END = TRUE;
	}
}


if(IsSet($_POST['coreedit_sub'])){
	$a_name = $_POST['a_name'];
	$a_password = $_POST['a_password'];
	if(!$result = mysql_query("SELECT * FROM ".$mySQLprefix."user WHERE user_name='$a_name' AND user_password='$a_password' AND user_perms='0' ")){
		exit;
	}
	$at = ($row = mysql_fetch_array($result) ? TRUE : FALSE);
	if(!$at){ exit; }



	while(list($key, $prefvalue) = each($_POST)){
		$pref[$key] = formtpa($prefvalue);
	}

	$tmp = addslashes(serialize($pref));

	@mysql_query("UPDATE ".$mySQLprefix."core set e107_value='$tmp' WHERE e107_name='pref' ");
	$message = "Core settings successfully updated. <br /><br /><a href='../index.php'>Click here to continue</a>";
	$END = TRUE;
	


}

if(IsSet($_POST['reset_core_sub']) && $_POST['mode'] == 3){
	$a_name = $_POST['a_name'];
	$a_password = $_POST['a_password'];
	if(!$result = mysql_query("SELECT * FROM ".$mySQLprefix."user WHERE user_name='$a_name' AND user_password='$a_password' AND user_perms='0' ")){
		exit;
	}
	$at = ($row = mysql_fetch_array($result) ? TRUE : FALSE);
	if(!$at){ exit; }
	
	$result = @mysql_query("SELECT * FROM ".$mySQLprefix."core WHERE e107_name='pref_backup' ");
	$row = @mysql_fetch_array($result);
	$tmp = stripslashes($row['e107_value']);
	$pref=unserialize($tmp);
	if(!is_array($pref)){
		$pref=unserialize($row['e107_value']);
	}
	@mysql_query("UPDATE ".$mySQLprefix."core set e107_value='".$row['e107_value']."' WHERE e107_name='pref' ");
	$message = "Core backup successfully restored. <br /><br /><a href='../index.php'>Click here to continue</a>";
	$END = TRUE;
}


if(IsSet($_POST['reset_core_sub']) && $_POST['mode'] == 1){

	$a_name = $_POST['a_name'];
	$a_password = $_POST['a_password'];
	if(!$result = mysql_query("SELECT * FROM ".$mySQLprefix."user WHERE user_name='$a_name' AND user_password='$a_password' AND user_perms=0")){
		exit;
	}

	$result = @mysql_query("SELECT * FROM ".$mySQLprefix."core WHERE e107_name='pref' ");
	$row = @mysql_fetch_array($result);
	$tmp = stripslashes($row['e107_value']);
	$pref=unserialize($tmp);

	echo "
	<span class='headertext2'><b>Edit your individual core items and click the button to save - <span class='headertext'>use this script with caution</span>.</b></span><br /><br />
	<form method='post' action='".$_SERVER['PHP_SELF']."'>
	<table style='width:95%'>\n";

	while(list($key, $prefr) = each($pref)){ 
		echo "<tr><td class='headertext' style='width:50%; text-align:right;'>$key&nbsp;&nbsp;</td>
		<td style='width:50%'><input type='text' name='$key' value='$prefr' size='50' maxlength='100' /></td></tr>\n";
	}
	echo "
	<tr>
	<td colspan='2' style='text-align:center'><br /><input class='button' type='submit' name='coreedit_sub' value='Save Core Settings' /></td>
	</tr>
	</table>
	<input type='hidden' name='a_name' value='".$_POST['a_name']."' />
	<input type='hidden' name='a_password' value='".$_POST['a_password']."' />
	</form>";
	$END = TRUE;
}

if($message){
	echo "<br /><br /><div style='text-align:center'><span class='headertext'>".$message."</span></div><br />";
}

if($END){
	echo "<br /></div></body></html>";
	exit;
}

echo "<span class='headertext2'>
This is the e107 resetcore utility. It allows you to completely rebuild your core if it becomes corrupt, or to restore a backup, or to change core settings manually. It won't affect your actual content (news posts, forum posts, articles etc).<br />
<b>Only run this utility if your site is failing to load due to a critical core error, or if you need to change a setting and can't log into your admin area.</b></span><br /><br /><br /><br />

<span class='headertext'>Please enter your main administrator username and password to continue ...</span><br /><br />
<form method='post' action='".$_SERVER['PHP_SELF']."'>
<table style='width:95%'>
<tr>
<td style='width:50%; text-align:right;' class='mediumtext'>Main administrator name:</td>
<td style='width:50%'>
<input class='tbox' type='text' name='a_name' size='30' value='' maxlength='100' />
</td>
</tr>
<tr>
<td style='width:50%; text-align:right;' class='mediumtext'>Main administrator Password:</td>
<td style='width:50%'>
<input class='tbox' type='password' name='a_password' size='30' value='' maxlength='100' />
</td>
</tr>
<tr>
<td colspan='2' style='text-align:center'>
<br />
<input class='button' type='submit' name='usubmit' value='Continue' />
</td>
</tr>
</table>
<br />
</div>
</body>
</html>";

function formtpa($text){
	$search = array("\"", "'", "\\", '\"', "\'", "$");
	$replace = array("&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&#036;");
	$text = str_replace($search, $replace, $text);
	return $text;
}

?>