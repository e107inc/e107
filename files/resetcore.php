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
require_once("../config.php");

if(IsSet($_POST['usubmit'])){
	$a_name = $_POST['a_name'];
	$a_password = md5($_POST['a_password']);

	mysql_connect($mySQLserver, $mySQLuser, $mySQLpassword);
	mysql_select_db($mySQLdefaultdb);
	if($result = mysql_query("SELECT * FROM ".$mySQLprefix."user WHERE user_name='$a_name' AND user_password='$a_password' ")){
		if($row = mysql_fetch_array($result)){
			extract($row);
			if($admin_permissions != 0){
				$error = "Unable to continue - upgrade process must be carried out by main site administrator.";
			}else{
				$admin_directory = "admin";
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
				$pref['sitename'][1] = "e107 powered website";
				$pref['siteurl'][1] = e_HTTP;
				$pref['sitebutton'][1] = e_HTTP."button.png";
				$pref['sitetag'][1] = "Website System 5.4b1";
				$pref['sitedescription'][1] = "";
				$pref['siteadmin'][1] = "SiteAdmin";
				$pref['siteadminemail'][1] = "SiteAdmin@".e_HTTP;
				$pref['sitetheme'][1] = "e107";
				$pref['sitedisclaimer'][1] = "All trademarks are &copy; their respective owners, all other content is &copy; e107 powered website.<br />e107 is &copy; e107.org 2002/2003 and is released under the <a href='http://www.gnu.org/'>GNU GPL license</a>.";
				$pref['newsposts'][1] = "10";
				$pref['flood_protect'][1] = "";
				$pref['flood_timeout'][1] = "5";
				$pref['flood_time'][1] = "30";
				$pref['flood_hits'][1] = "100";
				$pref['anon_post'][1] = "1";
				$pref['user_reg'][1] = "1";
				$pref['use_coppa'][1] = "1";
				$pref['profanity_filter'][1] = "1";
				$pref['profanity_replace'][1] = "[censored]";
				$pref['chatbox_posts'][1] = "10";
				$pref['smiley_activate'][1] = "";
				$pref['log_activate'][1] = "";
				$pref['log_refertype'][1] = "1";
				$pref['longdate'][1] = "%A %d %B %Y - %H:%M:%S";
				$pref['shortdate'][1] = "%d %b : %H:%M";
				$pref['forumdate'][1] = "%a %b %d %Y, %I:%M%p";
				$pref['sitelanguage'][1] = "English";
				$pref['maintainance_flag'][1] = "0";
				$pref['time_offset'][1] = "0";
				$pref['cb_linkc'][1] = " -link- ";
				$pref['cb_wordwrap'][1] = "30";
				$pref['cb_linkreplace'][1] = "1";
				$pref['log_lvcount'][1] = "10";
				$pref['meta_tag'][1] = "";
				$pref['user_reg_veri'][1] = "1";
				$tmp = serialize($pref);
				mysql_query("UPDATE ".$mySQLprefix."core SET e107_value='$tmp' WHERE e107_name='pref' ");
				$message = "Core reset. <a href='../index.php'>Click here to continue</a>";
			}
		}else{
			$message = "<b>Administrator not found in database/incorrect password - aborting.</b><br />";
			$abort = TRUE;
		}
	}else{
		$message = "<b>Administrator not found in database/incorrect password - aborting.</b><br />";
		$abort = TRUE;
	}
}
?>

<?php
echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><e107 resetcore></title>
<link rel="stylesheet" href="../themes/e107/style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
</head>
<body>

<div style="text-align:center">
<table style="width:100%" cellspacing="0" cellpadding="0">
<tr>
<td style="width:66%; background-color:#E2E2E2; text-align:left'>
<img src="../themes/shared/logo.png" alt="Logo" />
</td>
<td style="background-color:#E2E2E2; text-align:right; vertical-align:bottom" class="smalltext">
©Steve Dunstan 2002. See gpl.txt for licence details.
</td>
<tr> 
<td colspan="2" style="background-color:#000; vertical-align: top;"></td>
</tr>
<tr>
<tr> 
<td colspan="2" style="background-color:#ccc; vertical-align: top;">
<div class="mediumtext">&nbsp;resetcore</div>
</td>
</tr>
<tr> 
<td colspan="2" style="background-color:#000; vertical-align: top;"></td>
</tr>
<tr>
<td colspan="2" style="vertical-align: top; text-align:center"><br />
<table style="width:66%" class="fborder">
<tr>
<td class="installb">
<br /><img src="../themes/e107/images/installlogo.png" alt="" /><br /><span class="smalltext">php/mySQL website system</span><br />
<br />
<span class="installe">e107 Reset Core Utility</span><br />
This script will reset your preferences to their default value
<br /><br />

<?php

if($message){
	echo "<span class='installh'>".$message."</span>
	</td></tr></table><br /></body></html>";
	exit;
}

if($abort == TRUE){
	exit;
}

echo "
Please enter your main administrator name and password ...<br /><br />
<form method='post' action='".$_SERVER['PHP_SELF']."'>
<table style='width:95%'>
<tr>
<td style='width:30%' class='mediumtext'>Main administrator name:</td>
<td style='width:70%'>
<input class='tbox' type='text' name='a_name' size='60' value='' maxlength='100' />
</td>
</tr>
<tr>
<td style='width:30%' class='mediumtext'>Main administrator Password:</td>
<td style='width:70%'>
<input class='tbox' type='password' name='a_password' size='60' value='' maxlength='100' />
</td>
</tr>
<tr>
<td colspan='2' style='text-align:center'>
<br />
<input class='button' type='submit' name='usubmit' value='RESET CORE TO DEFAULT' />
</td>
</tr>
</table>
<br />
</body>
</html>";
?>