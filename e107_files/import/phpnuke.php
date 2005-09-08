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
|     $Source: /cvs_backup/e107_0.7/e107_files/import/phpnuke.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-09-08 21:01:03 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/


define("ASYSTEM", "php-nuke");
define("DEFAULTPREFIX", "nuke_");


require_once("../../class2.php");
require_once(HEADERF);


if(!isset($_POST['do_conversion']))
{

	$text = "
	<table style='width: 100%;' class='fborder'>
	<tr>
	<td class='forumheader3' style='text-align: center; margin-left: auto; margin-right: auto;'>
	This script will import your ".ASYSTEM." database to e107. <br /><br /><br /><b>*** IMPORTANT ***<br />Running this script will empty most of your e107 tables - make sure you have a backup before continuing!</b>

	<br /><br /><br />\n


	<form method='post' action='".e_SELF."'>
	Please enter the details for your ".ASYSTEM." database ...<br /><br />

	<table style='width: 50%;' class='fborder'>
	<tr>
	<td style='width: 50%; text-align: right;'>Host&nbsp;&nbsp;</td>
	<td style='width: 50%; text-align: left;'><input class='tbox' type='text' name='dbHost' size='30' value='localhost' maxlength='100' />
	</tr>
	<tr>
	<td style='width: 50%; text-align: right;'>Username&nbsp;&nbsp;</td>
	<td style='width: 50%; text-align: left;'><input class='tbox' type='text' name='dbUsername' size='30' value='' maxlength='100' />
	</tr>
	<tr>
	<td style='width: 50%; text-align: right;'>Password&nbsp;&nbsp;</td>
	<td style='width: 50%; text-align: left;'><input class='tbox' type='text' name='dbPassword' size='30' value='' maxlength='100' />
	</tr>
	<tr>
	<td style='width: 50%; text-align: right;'>Database&nbsp;&nbsp;</td>
	<td style='width: 50%; text-align: left;'><input class='tbox' type='text' name='dbDatabase' size='30' value='' maxlength='100' />
	</tr>
	<tr>
	<td style='width: 50%; text-align: right;'>Table Prefix&nbsp;&nbsp;</td>
	<td style='width: 50%; text-align: left;'><input class='tbox' type='text' name='dbPrefix' size='30' value='".DEFAULTPREFIX."' maxlength='100' />
	</tr>
	</table>
	<br /><br />
	<input class='button' type='submit' name='do_conversion' value='Continue' />
	</td>
	</tr>
	</table>";
	
	$ns -> tablerender(ASYSTEM." to e107 Conversion Script", $text);
	require_once(FOOTERF);
	exit;
}

if(!isset($_POST['dbHost']) || !isset($_POST['dbUsername']) || !isset($_POST['dbPassword']) || !isset($_POST['dbDatabase']))
{
	echo "Field(s) left blank, please go back and re-enter values.";
	require_once(FOOTERF);
	exit;
}

if(!isset($_POST['dbPrefix']))
{
	$nukePrefix = "";
}

extract($_POST);

echo "<table style='width: 100%;' class='fborder'>
<tr>
<td class='forumheader3' style='text-align: center; margin-left: auto; margin-right: auto;'>
Attempting to connect to ".ASYSTEM." database [ {$dbDatabase} @ {$dbHost} ] ...<br />\n";
flush();

$ASystemConnection = mysql_connect($dbHost, $dbUsername, $dbPassword, TRUE);
if(!mysql_select_db($dbDatabase, $ASystemConnection))
{
	goError("Error! Cound not connect to ".ASYSTEM." database. Please go back to the previous page and check your settings");
}

$e107Connection = mysql_connect($mySQLserver, $mySQLuser, $mySQLpassword, TRUE);
if(!mysql_select_db($mySQLdefaultdb, $e107Connection))
{
	goError("Error! Cound not connect to e107 database.");
}

echo "Successfully connected to ".ASYSTEM." and e107 databases ...<br><br />";

/* ++++++++++++++ USERS ++++++++++++++ */
$result = mysql_query("SELECT * FROM {$dbPrefix}users", $ASystemConnection);
if(!$result)
{
	goError("Error! Unable to access ".$dbPrefix."users table.");
}
$pass = 0; $fail = 0;
while($aResult = mysql_fetch_array($result))
{
	$aArray = convertUsers();
	$query = createQuery($aArray, $aResult, $mySQLprefix."user");		
	if(mysql_query($query, $e107Connection)){$pass++;}else{$fail++;}
	flush();
}
echo "Inserted $pass users into database ($fail fails).<br />";
/* +++++++++++++++ END +++++++++++++++ */

/* ++++++++++++++ NEWS ++++++++++++++ */
$query = "SELECT * FROM {$dbPrefix}stories
LEFT JOIN {$dbPrefix}users ON {$dbPrefix}stories.aid={$dbPrefix}users.username
ORDER BY {$dbPrefix}stories.sid ASC";

$result = mysql_query($query, $ASystemConnection);
if(!$result)
{
	goError("Error! Unable to access ".$dbPrefix."stories table.");
}
$pass = 0; $fail = 0;
while($aResult = mysql_fetch_array($result))
{
	$aArray = convertNews();
	$query = createQuery($aArray, $aResult, $mySQLprefix."news");		
	if(mysql_query($query, $e107Connection)){$pass++;}else{$fail++;}
	flush();
}
echo "Inserted $pass news items into database ($fail fails).<br />";
/* +++++++++++++++ END +++++++++++++++ */

/* ++++++++++++++ BANLIST ++++++++++++++ */
$result = mysql_query("SELECT * FROM {$dbPrefix}banned_ip", $ASystemConnection);
if(!$result)
{
	goError("Error! Unable to access ".$dbPrefix."banned_ip table.");
}
$pass = 0; $fail = 0;
while($aResult = mysql_fetch_array($result))
{
	$aArray = convertBans();
	$query = createQuery($aArray, $aResult, $mySQLprefix."banlist");		
	if(mysql_query($query, $e107Connection)){$pass++;}else{$fail++;}
	flush();
}
echo "Inserted $pass banned IP addresses into database ($fail fails).<br />";
/* +++++++++++++++ END +++++++++++++++ */

/* ++++++++++++++ CUSTOM PAGES ++++++++++++++ */
$result = mysql_query("SELECT * FROM {$dbPrefix}pages", $ASystemConnection);
if(!$result)
{
	goError("Error! Unable to access ".$dbPrefix."pages table.");
}
$pass = 0; $fail = 0;
while($aResult = mysql_fetch_array($result))
{
	$aArray = convertPages();
	$query = createQuery($aArray, $aResult, $mySQLprefix."page");		
	if(mysql_query($query, $e107Connection)){$pass++;}else{$fail++;}
	flush();
}
echo "Inserted $pass custom pages into database ($fail fails).<br />";
/* +++++++++++++++ END +++++++++++++++ */


/* ++++++++++++++ FORUMS ++++++++++++++ 

$result = mysql_query("SHOW COLUMNS FROM {$mySQLprefix}forum", $e107Connection);
if(!$result)
{
	goError("Error! Unable to access the e107 'forum' table - have you installed the e107 Forum System plugin?");
}
$result = mysql_query("SELECT * FROM {$dbPrefix}bbforums ORDER BY forum_order ASC", $ASystemConnection);
if(!$result)
{
	goError("Error! Unable to access ".$dbPrefix."bbforums table.");
}
$pass = 0; $fail = 0;
while($aResult = mysql_fetch_array($result))
{
	$aArray = convertForums();
	$query = createQuery($aArray, $aResult, $mySQLprefix."forum");		
	if(mysql_query($query, $e107Connection)){$pass++;}else{$fail++;}
	flush();
}

	$query = "INSERT INTO {$mySQLprefix}forum VALUES (0, 'Default Forum Parent', 'This parent has been created by the ".ASYSTEM." conversion script, you can edit it from admin -> forums', '0', '0', '".time()."', '', '0', '0', '', '', '', '0', '0')";
	mysql_query($query, $e107Connection);
	$id = mysql_insert_id();
	$query = "UPDATE {$mySQLprefix}forum SET forum_parent='$id' WHERE forum_name!='Default Forum Parent'";
	mysql_query($query, $e107Connection);

echo "Inserted $pass forums into database ($fail fails).<br />";
/* +++++++++++++++ END +++++++++++++++ */


/* ++++++++++++++ FORUM POSTS ++++++++++++++ 
$query = "SELECT * FROM {$dbPrefix}bbposts
LEFT JOIN {$dbPrefix}bbposts_text ON {$dbPrefix}bbposts.post_id={$dbPrefix}bbposts_text.post_id 
LEFT JOIN {$dbPrefix}users ON {$dbPrefix}bbposts.poster_id={$dbPrefix}users.user_id  
ORDER BY {$dbPrefix}bbposts.post_id ASC";

$result = mysql_query($query, $ASystemConnection);
if(!$result)
{
	goError("Error! Unable to access ".$dbPrefix."stories table.");
}
$pass = 0; $fail = 0;
while($aResult = mysql_fetch_array($result))
{
	$aArray = convertForumPosts();
	$query = createQuery($aArray, $aResult, $mySQLprefix."forum_t");
	$poster = ($aResult['poster_id'] == -1 ? "0.".($aResult['username'] ? $aResult['username'] : "Anonymous") : $aResult['poster_id'].".".$aResult['username']);
	$query = str_replace("''", "'$poster'", $query);
	if(mysql_query($query, $e107Connection)){$pass++;}else{$fail++;}
	flush();
}
echo "Inserted $pass forum posts into database ($fail fails).<br />";
 +++++++++++++++ END +++++++++++++++ */



echo "</td></tr></table>";

require_once(FOOTERF);


function goError($error)
{
	echo "<b>{$error}</b></td></tr></table>";
	require_once(FOOTERF);
	exit;
}


function convertUsers()
{
	$rArray = array(
		array("asystem" => "user_id", "e107" => "user_id", "type" => "INT"),
		array("asystem" => "name", "e107" => "user_login", "type" => "STRING"),
		array("asystem" => "username", "e107" => "user_name", "type" => "STRING"),
		array("asystem" => "username", "e107" => "user_loginname", "type" => "STRING"),
		array("asystem" => "user_password", "e107" => "user_password", "type" => "STRING"),
		array("asystem" => "user_email", "e107" => "user_email", "type" => "STRING"),
		array("asystem" => "user_avatar", "e107" => "user_image", "type" => "STRING"),
		array("asystem" => "user_regdate", "e107" => "user_join", "type" => "STRTOTIME"),
		array("asystem" => "user_sig", "e107" => "user_signature", "type" => "STRING"),
		array("asystem" => "user_viewemail", "e107" => "user_hideemail", "type" => "INT"),
		array("asystem" => "user_posts", "e107" => "user_forums", "type" => "INT"), 
		array("asystem" => "user_lastvisit", "e107" => "user_lastvisit", "type" => "INT"), 
		array("asystem" => "user_timezone", "e107" => "user_timezone", "type" => "STRING")
	);
	return $rArray;
}

function convertNews()
{
	$rArray = array(
		array("asystem" => "sid", "e107" => "news_id", "type" => "INT"),
		array("asystem" => "user_id", "e107" => "news_author", "type" => "INT"),
		array("asystem" => "title", "e107" => "news_title", "type" => "STRING"),
		array("asystem" => "time", "e107" => " news_datestamp", "type" => "STRTOTIME"),
		array("asystem" => "hometext", "e107" => " news_body", "type" => "STRING"),
		array("asystem" => "bodytext", "e107" => " news_extended", "type" => "STRING"),
		array("asystem" => "comments", "e107" => " news_comment_total", "type" => "INT"),
		array("asystem" => "catid", "e107" => " news_category", "type" => "INT")
	);
	return $rArray;
}

function convertBans()
{
	$rArray = array(
		array("asystem" => "ip_address", "e107" => " banlist_ip", "type" => "STRING"),
		array("asystem" => "reason", "e107" => "banlist_reason", "type" => "STRING")
	);
	return $rArray;
}


function convertPages()
{
	$rArray = array(
		array("asystem" => "pid", "e107" => "page_id", "type" => "INT"),
		array("asystem" => "title", "e107" => "page_title", "type" => "STRING"),
		array("asystem" => "text", "e107" => "page_text", "type" => "STRING"), 
		array("asystem" => "date", "e107" => "page_datestamp", "type" => "STRTOTIME")
	);
	return $rArray;
}

/*
function convertForums()
{
	$rArray = array(
		array("asystem" => "forum_id", "e107" => "forum_id", "type" => "INT"),
		array("asystem" => "forum_name", "e107" => "forum_name", "type" => "STRING"),
		array("asystem" => "forum_desc", "e107" => "forum_description", "type" => "STRING"), 
		array("asystem" => "forum_topics", "e107" => "forum_threads", "type" => "INT"), 
		array("asystem" => "forum_posts", "e107" => "forum_replies", "type" => "INT"), 
		array("asystem" => "null", "e107" => "forum_postclass", "type" => "INT", "value" => 253), 
		array("asystem" => "null", "e107" => "forum_moderators", "type" => "INT", "value" => 2), 
		array("asystem" => "null", "e107" => "forum_class", "type" => "INT", "value" => 0)
	);
	return $rArray;
}


 function convertForumPosts()
{
	$rArray = array(
		array("asystem" => "post_id", "e107" => "thread_id", "type" => "INT"),
		array("asystem" => "topic_id", "e107" => "thread_parent", "type" => "INT"),
		array("asystem" => "forum_id", "e107" => "thread_forum_id", "type" => "INT"), 
		array("asystem" => "null", "e107" => "thread_active", "type" => "INT", "value" => 1), 
		array("asystem" => "poster", "e107" => "thread_user", "type" => "STRING"), 
		array("asystem" => "post_time", "e107" => "thread_datestamp", "type" => "INT"), 
		array("asystem" => "post_subject", "e107" => "thread_name", "type" => "STRING"), 
		array("asystem" => "post_text", "e107" => "thread_thread", "type" => "STRING")
	);
	return $rArray;
}
*/














function createQuery($convertArray, $dataArray, $table)
{
	global $tp;

	$columns = "(";
	$values = "(";


	foreach($convertArray as $convert)
	{
		if($convert['type'] == "STRING")
		{
			$dataArray[$convert['asystem']] = preg_replace("#\[.*\]#", "", $tp -> toDB($dataArray[$convert['asystem']]));
		}
		else if($convert['type'] == "STRTOTIME")
		{
			$dataArray[$convert['asystem']] = strtotime($dataArray[$convert['asystem']]);
		}

		$columns .= $convert['e107'].",";
		$values .= (array_key_exists("value", $convert) ? "'".$convert['value']."'," : "'".$dataArray[$convert['asystem']]."',");
	}
	

	$columns = substr($columns, 0, -1).")";
	$values = substr($values, 0, -1).")";

	return "INSERT INTO $table $columns VALUES $values";
	
}	










?>