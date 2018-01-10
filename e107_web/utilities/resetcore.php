<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc
|     http://e107.org/
|
|     Standalone page aimed to reset core preferences
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_files/utilities/resetcore.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/


/*
@TODO: Needs detailed review/fixing:
	- Handle XML-based default prefs
	- Review existing definitions of constants
	- Define new constants (e_CORE, etc) properly
*/

/* ####################################################


To use this file, you must edit the following line, removing the // so it reads:
define("ACTIVE", true);
If you don't, the script will not be usable.

When you have finished with resetcore you should swap true for false so it can't be used by
people it shouldn't be used by.

*/

define('ACTIVE', false);
//define('ACTIVE', TRUE);


/* #################################################### */



if(ACTIVE !== true) 
{
	echo "<span class='headertext2'>Currently disabled. To enable please open this file in a text editor and follow the instructions to activate.</span>";
	exit;
}

$register_globals = true;
if(function_exists('ini_get'))
{
	$register_globals = ini_get('register_globals');
}
if($register_globals == true)
{
	while (list($global) = each($GLOBALS))
	{
		if (!preg_match('/^(_POST|_GET|_COOKIE|_SERVER|_FILES|GLOBALS|HTTP.*|_REQUEST|eTimingStart)$/', $global))
		{
			unset($$global);
		}
	}
	unset($global);
}


// build e_CACHE and other constants from e107_config.php - resetcore.php is a stand alone page
$siteRoot = realpath(dirname(__FILE__).'./../../').'/';
require_once($siteRoot.'e107_config.php');
if (!isset($mySQLdefaultdb)) return FALSE;
if (!isset($mySQLprefix)) return FALSE;

$hash = substr(md5($mySQLdefaultdb.".".$mySQLprefix),0,10);	


mysql_connect($mySQLserver, $mySQLuser, $mySQLpassword);
mysql_select_db($mySQLdefaultdb);
define("MAGIC_QUOTES_GPC", (ini_get('magic_quotes_gpc') ? TRUE : FALSE));

define('e_CACHE', $siteRoot.$SYSTEM_DIRECTORY.$hash.'/cache/');
define('e_CORE', $siteRoot.'core/');				// @TODO: Allow for override

define('e107_INIT', TRUE);


require_once('../../'.$HANDLERS_DIRECTORY.'core_functions.php');
$eArrayStorage = new e_array();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>e107 resetcore</title>
<link rel="stylesheet" href="style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-style-type" content="text/css" />
</head>
<body>
<div class='mainbox'>
<a href="http://e107.org"><img src="../../<?php echo($IMAGES_DIRECTORY); ?>logo_template_large.png" alt="Logo" style="border: 0px; vertical-align: middle;" /></a> <span class='headertext'>e107 Reset Core Utility</span>
<br />
<br />
<br />
<br />
<?php


if (isset($_POST['usubmit'])) 
{
	if (($row = e_verify()) !== FALSE) 
	{
		extract($row);

		$result = mysql_query("SELECT * FROM ".$mySQLprefix."core WHERE e107_name='pref_backup' ");
		$bu_exist = ($row = mysql_fetch_array($result) ? TRUE : FALSE);

		$admin_directory = "e107_admin";

//			<input type='radio' name='mode' value='1' /> <span class='headertext2'>Manually edit core values</span><br />

		echo "<span class='headertext2'><b>Please select which method you want to use, then click the button to proceed ...</b></span><br /><br /><br /><br />
			<table style='width: auto; margin-left:auto; margin-right: auto;'>
			<tr>
			<td>
			<form method='post' action='".$_SERVER['PHP_SELF']."'>
			<input type='radio' name='mode' value='2' /> <span class='headertext2'>Reset core to default values</span><br />". ($bu_exist ? "<input type='radio' name='mode' value='3' /> <span class='headertext2'>Restore core backup</span>" : "<br />( There is no backed-up core - unable to offer option to restore backup )")."<br /><br /><input class='btn button' type='submit' name='reset_core_sub' value='Select method then click here to continue' />
				 
			<input type='hidden' name='a_name' value='".$_POST['a_name']."' />
			<input type='hidden' name='a_password' value='".$_POST['a_password']."' />
				 
			</form>
			</td>
			</tr>
			</table>
			";

		$END = TRUE;
	} else {
		$message = "<b>Administrator not found in database / incorrect password / insufficient permissions - aborting.</b><br />";
		$END = TRUE;
	}
}


if (isset($_POST['reset_core_sub']) && $_POST['mode'] == 2)
{
	if (($at = e_verify()) === FALSE) 
	{
		exit;
	}

	$tmpr = substr(str_replace($_SERVER['DOCUMENT_ROOT'], "", $_SERVER['SCRIPT_FILENAME']), 1);
	$root = "/".substr($tmpr, 0, strpos($tmpr, "/"))."/";
	$e_HTTP = $root;
	$admin_directory = "e107_admin";
	$url_prefix = substr($_SERVER['PHP_SELF'], strlen($e_HTTP), strrpos($_SERVER['PHP_SELF'], "/")+1-strlen($e_HTTP));
	$num_levels = substr_count($url_prefix, "/");
	$link_prefix = '';
	for($i = 1; $i <= $num_levels; $i++) {
		$link_prefix .= "../";
	}

	define("e_ADMIN", $e_HTTP.$admin_directory."/");
	define("e_SELF", "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	define("e_QUERY", preg_replace("#&|/?PHPSESSID.*#i", "", $_SERVER['QUERY_STRING']));
	define('e_BASE', $link_prefix);
	$e_path = (!strpos($_SERVER['SCRIPT_FILENAME'], ".php") ? $_SERVER['PATH_TRANSLATED'] : $_SERVER['SCRIPT_FILENAME']);
	define("e_PATH", $e_path);


	$pref_language = "English";
	include_once("../../".$LANGUAGES_DIRECTORY.'English/lan_prefs.php');
	require_once(e_CORE.'def_e107_prefs.php');

	$PrefOutput = $eArrayStorage->WriteArray($pref);

	mysql_query("DELETE FROM ".$mySQLprefix."core WHERE e107_name='SitePrefs' OR e107_name='SitePrefs_Backup'");
	if (!mysql_query("INSERT INTO ".$mySQLprefix."core VALUES ('SitePrefs', '{$PrefOutput}')")) 
	{
		$message = "Rebuild failed ...";
		$END = TRUE;
	} 
	else 
	{
		mysql_query("INSERT INTO ".$mySQLprefix."core VALUES ('SitePrefs_Backup', '{$PrefOutput}')");
		$message = "Core reset. <br /><br /><a href='../../index.php'>Click here to continue</a>";
		$END = TRUE;
	}
}



function recurse_pref($ppost) 
{
	$search = array("\"", "'", "\\", '\"', "\'", "$", "?");
	$replace = array("&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&#036;", "&copy;");
	foreach ($ppost as $key => $value) {
		if(!is_array($value)){
			$ret[$key] = str_replace($search, $replace, $text);
		} else {
			$ret[$key] = recurse_pref($value);
		}
	}
	return $ret;
}

if (isset($_POST['coreedit_sub']))
{
	if (($at = e_verify()) === FALSE) {
		exit;
	}

	$pref = recurse_pref($_POST);

	$PrefOutput = $eArrayStorage->WriteArray($pref);

	mysql_query("DELETE FROM ".$mySQLprefix."core WHERE e107_name='SitePrefs' OR e107_name='SitePrefs_Backup'");
	mysql_query("INSERT INTO ".$mySQLprefix."core VALUES ('SitePrefs', '{$PrefOutput}')");
	mysql_query("INSERT INTO ".$mySQLprefix."core VALUES ('SitePrefs_Backup', '{$PrefOutput}')");

	$message = "Core settings successfully updated. <br /><br /><a href='../../index.php'>Click here to continue</a>";
	$END = TRUE;
}

if (isset($_POST['reset_core_sub']) && $_POST['mode'] == 3) {
	if (($at = e_verify()) === FALSE) {
		exit;
	}

	$result = mysql_query("SELECT * FROM ".$mySQLprefix."core WHERE e107_name='pref_backup'");
	$row = mysql_fetch_array($result);

	$pref = unserialize(base64_decode($row['e107_value']));

	$PrefOutput = $eArrayStorage->WriteArray($pref);

	mysql_query("DELETE FROM ".$mySQLprefix."core WHERE `e107_name` = 'SitePrefs' OR `e107_name` = 'SitePrefs_Backup'");
	mysql_query("INSERT INTO ".$mySQLprefix."core VALUES ('SitePrefs', '{$PrefOutput}')");
	mysql_query("INSERT INTO ".$mySQLprefix."core VALUES ('SitePrefs_Backup', '{$PrefOutput}')");

	$message = "Core backup successfully restored. <br /><br /><a href='../../index.php'>Click here to continue</a>";
	$END = TRUE;
}


if (isset($_POST['reset_core_sub']) && $_POST['mode'] == 1)
{
	if (($at = e_verify()) === FALSE) {
		exit;
	}

	$result = @mysql_query("SELECT * FROM ".$mySQLprefix."core WHERE e107_name='SitePrefs'");
	$row = @mysql_fetch_array($result);

	$pref = $eArrayStorage->ReadArray($row['e107_value']);

	echo "
		<span class='headertext2'><b>Edit your individual core items and click the button to save - <span class='headertext'>use this script with caution</span>.</b></span><br /><br />
		<form method='post' action='".$_SERVER['PHP_SELF']."'>
		<table style='width:95%'>\n";

	while (list($key, $prefr) = each($pref)) {
		if (is_array($prefr)) {
			foreach ($prefr as $akey => $apref) {
				echo "<tr><td class='headertext2' style='width:50%; text-align:right;'>{$key}[{$akey}]&nbsp;&nbsp;</td>
				<td style='width:50%'><input type='text' name='{$key}[{$akey}]' value='{$apref}' size='50' maxlength='100' /></td></tr>\n";

			}
		} else {
			echo "<tr><td class='headertext2' style='width:50%; text-align:right;'>{$key}&nbsp;&nbsp;</td>
			<td style='width:50%'><input type='text' name='{$key}' value='{$prefr}' size='50' maxlength='100' /></td></tr>\n";
		}
	}
	echo "
		<tr>
		<td colspan='2' style='text-align:center'><br /><input class='btn btn-default btn-secondary button' type='submit' name='coreedit_sub' value='Save Core Settings' /></td>
		</tr>
		</table>
		<input type='hidden' name='a_name' value='".$_POST['a_name']."' />
		<input type='hidden' name='a_password' value='".preg_replace("/\W/", '', $_POST['a_password'])."' />
		</form>";
	$END = TRUE;
}

if (isset($message)) {
	echo "<br /><br /><div style='text-align:center'><span class='headertext2'>{$message}</span></div><br />";
}

if (isset($END)) {
	echo "<br /></div></body></html>";
	exit;
}

echo "<span class='headertext2'>
	This is the e107 resetcore utility. It allows you to completely rebuild your core if it becomes corrupt, or to restore a backup. <br />It won't affect your actual content (news posts, forum posts, articles etc).<br />
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
	<input class='btn btn-default btn-secondary button' type='submit' name='usubmit' value='Continue' />
	</td>
	</tr>
	</table>
	<br />
	</div>
	</body>
	</html>";


/**
 *	Verify main admin data
 */
function e_verify() {
	global $mySQLprefix;
	if (ACTIVE !== TRUE) 
	{
		exit();
	}
	if (MAGIC_QUOTES_GPC == FALSE) 
	{
		$a_name = addslashes($_POST['a_name']);
	}
	else
	{
		$a_name = $_POST['a_name'];
	}

	$a_name = str_replace('/*', '', $a_name);

	$result = mysql_query("SELECT * FROM ".$mySQLprefix."user WHERE user_name='".$a_name."'");
	$row = mysql_fetch_array($result);

	// @TODO: Will need to handle other password encodings as well
	if (($row['user_password'] === md5($_POST['a_password'])) && ($row['user_perms'] === '0') && (ACTIVE === TRUE)) 
	{
		clear_cache();
		return $row;
	} 
	else 
	{
		return FALSE;
	}
}




/**
 *	Clear all cache files
 */
function clear_cache() 
{
	// $dir = "../cache/";
	$dir = e_CACHE;
	$pattern = "*.cache.php";
	$deleted = false;
	$pattern = str_replace(array("\*", "\?"), array(".*", "."), preg_quote($pattern));
	if (substr($dir, -1) != "/") {
		$dir .= "/";
	}
	if (is_dir($dir)) {
		$d = opendir($dir);
		while ($file = readdir($d)) {
			if (is_file($dir.$file) && preg_match("/^{$pattern}$/", $file)) {
				if (unlink($dir.$file)) {
					$deleted[] = $file;
				}
			}
		}
		closedir($d);
		return true;
	} else {
		return false;
	}
}

?>