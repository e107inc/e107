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
|     $Source: /cvs_backup/e107_0.7/e107_files/import/phpbb2.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-03-14 15:32:04 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

require_once("../../class2.php");
require_once(HEADERF);


if(!isset($_POST['do_conversion']))
{

	$text = "
	<table style='width: 100%;' class='fborder'>
	<tr>
	<td class='forumheader3' style='text-align: center; margin-left: auto; margin-right: auto;'>
	This script will import your phpBB2 database to e107. It will copy over users, forums, forum posts and polls.<br /><br /><br /><b>*** IMPORTANT ***<br />Running this script will empty your e107 forum, users and polls table - make sure you have a backup before continuing!</b>

	<br /><br /><br />\n


	<form method='post' action='".e_SELF."'>
	Please enter the details for your phpBB2 database ...<br /><br />

	<table style='width: 50%;' class='fborder'>
	<tr>
	<td style='width: 50%; text-align: right;'>Host&nbsp;&nbsp;</td>
	<td style='width: 50%; text-align: left;'><input class='tbox' type='text' name='phpbb2Host' size='30' value='localhost' maxlength='100' />
	</tr>
	<tr>
	<td style='width: 50%; text-align: right;'>Username&nbsp;&nbsp;</td>
	<td style='width: 50%; text-align: left;'><input class='tbox' type='text' name='phpbb2Username' size='30' value='' maxlength='100' />
	</tr>
	<tr>
	<td style='width: 50%; text-align: right;'>Password&nbsp;&nbsp;</td>
	<td style='width: 50%; text-align: left;'><input class='tbox' type='text' name='phpbb2Password' size='30' value='' maxlength='100' />
	</tr>
	<tr>
	<td style='width: 50%; text-align: right;'>Database&nbsp;&nbsp;</td>
	<td style='width: 50%; text-align: left;'><input class='tbox' type='text' name='phpbb2Database' size='30' value='phpbb2' maxlength='100' />
	</tr>
	<tr>
	<td style='width: 50%; text-align: right;'>Table Prefix&nbsp;&nbsp;</td>
	<td style='width: 50%; text-align: left;'><input class='tbox' type='text' name='phpbb2Prefix' size='30' value='phpbb_' maxlength='100' />
	</tr>
	</table>
	<br /><br />
	<input class='button' type='submit' name='do_conversion' value='Continue' />
	</td>
	</tr>
	</table>";
	
	$ns -> tablerender("phpBB2 to e107 Conversion Script", $text);
	require_once(FOOTERF);
	exit;
}

if(!isset($_POST['phpbb2Host']) || !isset($_POST['phpbb2Username']) || !isset($_POST['phpbb2Password']) || !isset($_POST['phpbb2Database']))
{
	echo "Field(s) left blank, please go back and re-enter values.";
	require_once(FOOTERF);
	exit;
}

if(!isset($_POST['phpbb2Prefix']))
{
	$phpbb2Prefix = "";
}

extract($_POST);

echo "<table style='width: 100%;' class='fborder'>
<tr>
<td class='forumheader3' style='text-align: center; margin-left: auto; margin-right: auto;'>
Attempting to connect to phpBB database [ $phpbb2Database @ $phpbb2Host ] ...<br />\n";
flush();

$phpbbConnection = mysql_connect($phpbb2Host, $phpbb2Username, $phpbb2Password, TRUE);
if(!mysql_select_db($phpbb2Database, $phpbbConnection))
{
	goError("Error! Cound not connect to phpBB database. Please go back to the previous page and check your settings");
}

$e107Connection = mysql_connect($mySQLserver, $mySQLuser, $mySQLpassword, TRUE);
if(!mysql_select_db($mySQLdefaultdb, $e107Connection))
{
	goError("Error! Cound not connect to e107 database.");
}

echo "Successfully connected to phpBB and e107 databases ...<br><br />";


$phpbb_res = mysql_query("SELECT * FROM {$phpbb2Prefix}users", $phpbbConnection);
if(!$phpbb_res)
{
	goError("Error! Unable to access ".$phpbb2Prefix."users table.");
}


while($user = mysql_fetch_array($phpbb_res))
{
	$userArray = convertUsers();
	if($user['user_level'] != 1 && $user['user_id'] != -1)
	{
		$query = createQuery($userArray, $user, $mySQLprefix."user");		
		echo (mysql_query($query, $e107Connection) ? "Successfully inserted user: ".$user['user_id'].": ".$user['username'] : "Unable to insert user: ".$user['user_id'].": ".$user['username']."<br />".mysql_errno() . ": " . mysql_error())."<br />";
		flush();
	}
}


// ### get phpbb categrories and inset them as forum parents

mysql_query("TRUNCATE TABLE {$mySQLprefix}forum", $e107Connection);


$phpbb_res = mysql_query("SELECT * FROM {$phpbb2Prefix}categories", $phpbbConnection);
if(!$phpbb_res)
{
	goError("Error! Unable to access ".$phpbb2Prefix."categories table.");
}

$catcount = 500;
while($parent = mysql_fetch_array($phpbb_res))
{

	$parentArray = convertParents($catcount);
	
	$query = createQuery($parentArray, $parent, $mySQLprefix."forum");	
	echo (mysql_query($query, $e107Connection) ? "Successfully inserted parent: ".$parent['cat_id'].": ".$parent['cat_title'] : "Unable to insert parent: ".$parent['cat_id'].": ".$parent['cat_title']."<br />".mysql_errno() . ": " . mysql_error())."<br />";
	flush();

	$phpbb_res2 = mysql_query("SELECT * FROM {$phpbb2Prefix}forums WHERE cat_id = ".$parent['cat_id'], $phpbbConnection);
	if($phpbb_res2)
	{
		while($forum = mysql_fetch_array($phpbb_res2))
		{
			$forumArray = convertForums($catcount);
			$query = createQuery($forumArray, $forum, $mySQLprefix."forum");
			echo (mysql_query($query, $e107Connection) ? "Successfully inserted forum: ".$parent['cat_id'].": ".$parent['cat_title'] : "Unable to insert forum: ".$parent['cat_id'].": ".$parent['cat_title']."<br />".mysql_errno() . ": " . mysql_error())."<br />";
			flush();
		}
	}
	else
	{
		echo "Didn't find any forums for parent '".$parent['cat_title']."'<br />";
	}
	$catcount ++;
}

mysql_query("TRUNCATE TABLE {$mySQLprefix}forum_t", $e107Connection);
mysql_query("TRUNCATE TABLE {$mySQLprefix}polls", $e107Connection);

$query = "SELECT * FROM {$phpbb2Prefix}topics 
LEFT JOIN {$phpbb2Prefix}posts_text ON ({$phpbb2Prefix}topics.topic_title = {$phpbb2Prefix}posts_text.post_subject)
LEFT JOIN {$phpbb2Prefix}posts ON ({$phpbb2Prefix}posts.post_id = {$phpbb2Prefix}posts_text.post_id)
ORDER BY topic_time ASC";

$phpbb_res = mysql_query($query, $phpbbConnection);
if(!$phpbb_res)
{
	goError("Error! Unable to access ".$phpbb2Prefix."topics table.");
}
while($topic = mysql_fetch_array($phpbb_res))
{

	//echo "<pre>"; print_r($topic); echo "</pre>";

	if($topic['topic_vote'])
	{
		// poll attached to this topic ...
		$topic['topic_title'] = "[poll] ".$topic['topic_title'];
		$query = "SELECT * FROM {$phpbb2Prefix}vote_desc WHERE topic_id=".$topic['topic_id'];
		$phpbb_res3 = mysql_query($query, $phpbbConnection);
		$pollQ = mysql_fetch_array($phpbb_res3);
		
		$query = "SELECT * FROM {$phpbb2Prefix}vote_results WHERE vote_id=".$pollQ['vote_id'];
		$phpbb_res3 = mysql_query($query, $phpbbConnection);
		$options = "";
		$votes = "";
		while($pollO = mysql_fetch_array($phpbb_res3))
		{
			$options .= $pollO['vote_option_text'].chr(1);
			$votes .= $pollO['vote_result'].chr(1);
		}

		extract($pollQ);
		$query = "INSERT INTO ".$mySQLprefix."polls VALUES ('0', $vote_start, $vote_start, 0, 0, '$vote_text', '$options', '$votes', '', 2, 0, 0, 0, 255, 0)";
		echo (mysql_query($query, $e107Connection) ? "Poll successfully inserted" : "Unable to insert poll ($query)")."<br />";
	}


	if($topic['topic_poster'] == 2)
	{
		$topic['topic_poster'] = 1;
	}

	if($topic['topic_poster'] == -1)
	{
		$topic['topic_poster'] = 0;
		$poster = ($topic['post_username'] ? $topic['post_username'] : "Anonymous");
	}

	$topicArray = convertTopics($poster);
	$query = createQuery($topicArray, $topic, $mySQLprefix."forum_t");	

	if(!mysql_query($query, $e107Connection))
	{
		echo "Unable to insert topic: ".$topic['topic_id']."<br />";
		flush();
	}
	else
	{
		echo "Successfully inserted topic: ".$topic['topic_id']."<br />";
		flush();
		$parent_id = mysql_insert_id();
		$topic_id = $topic['topic_id'];

		//echo "PARENT: $parent_id, TOPIC: $topic_id<br />"; 

		$query = "SELECT * FROM {$phpbb2Prefix}posts LEFT JOIN {$phpbb2Prefix}posts_text ON ({$phpbb2Prefix}posts.post_id = {$phpbb2Prefix}posts_text.post_id) WHERE topic_id='$topic_id' AND post_subject = '' ORDER BY post_time DESC";
		$phpbb_res2 = mysql_query($query, $phpbbConnection);
		if(!$phpbb_res2)
		{
			goError("Error! Unable to access ".$phpbb2Prefix."posts / ".$phpbb2Prefix."posts_text table.");
		}
		while($post = mysql_fetch_array($phpbb_res2))
		{
			
			if($post['poster_id'] == 2)
			{
				$post['poster_id'] = 1;
			}
			if($post['poster_id'] == -1)
			{
				$post['poster_id'] = 0;
				$poster = ($post['post_username'] ? $post['post_username'] : "Anonymous");
			}
	

			$postArray = convertForumPosts($parent_id, $poster);
			$query = createQuery($postArray, $post, $mySQLprefix."forum_t");	
			echo (mysql_query($query, $e107Connection) ? "Successfully inserted thread: ".$post['post_id'] : "Unable to insert thread: ".$parent['cat_id'].": ".$parent['cat_title']."<br />".mysql_errno() . ": " . mysql_error())."<br />";
			flush();
		}
	}
}


echo "</td></tr></table>";

require_once(FOOTERF);


function goError($error)
{
	echo "<b>$error</b></td></tr></table>";
	require_once(FOOTERF);
	exit;
}


function convertUsers()
{
	$usersArray = array(
		array("phpbb" => "user_id", "e107" => "user_id", "type" => "INT"),
		array("phpbb" => "username", "e107" => "user_name", "type" => "STRING"),
		array("phpbb" => "user_password", "e107" => "user_password", "type" => "STRING"),
		array("phpbb" => "user_email", "e107" => "user_email", "type" => "STRING"),
		array("phpbb" => "user_website", "e107" => "user_homepage", "type" => "STRING"),
		array("phpbb" => "user_from", "e107" => "user_location", "type" => "STRING"),
		array("phpbb" => "user_sig", "e107" => "user_signature", "type" => "STRING"),
		array("phpbb" => "user_viewemail", "e107" => "user_hideemail", "type" => "INT"),
		array("phpbb" => "user_regdate", "e107" => "user_join", "type" => "INT"), 
		array("phpbb" => "user_posts", "e107" => "user_forums", "type" => "INT"), 
		array("phpbb" => "user_level", "e107" => "user_admin", "type" => "INT")
	);
	return $usersArray;
}

function convertParents($catid)
{
	$parentArray = array(
		array("phpbb" => "cat_id", "e107" => "forum_id", "type" => "INT", "value" => $catid),
		array("phpbb" => "cat_title", "e107" => "forum_name", "type" => "STRING"),
		array("phpbb" => "cat_order", "e107" => "forum_order", "type" => "INT")
	);
	return $parentArray;
}

function convertForums($catid)
{
	$forumArray = array(
		array("phpbb" => "forum_id", "e107" => "forum_id", "type" => "INT"),
		array("phpbb" => "cat_id", "e107" => "forum_parent", "type" => "STRING", "value" => $catid),
		array("phpbb" => "forum_name", "e107" => "forum_name", "type" => "STRING"),
		array("phpbb" => "forum_desc", "e107" => "forum_description", "type" => "STRING"),
		array("phpbb" => "forum_order", "e107" => "forum_order", "type" => "INT"),
		array("phpbb" => "forum_topics", "e107" => "forum_threads", "type" => "INT"),
		array("phpbb" => "forum_posts", "e107" => "forum_replies", "type" => "INT")
	);
	return $forumArray;
}


function convertTopics($poster)
{
	$topicArray = array(
		array("phpbb" => "forum_id", "e107" => "thread_forum_id", "type" => "INT"),
		array("phpbb" => "topic_title", "e107" => "thread_name", "type" => "STRING"),
		array("phpbb" => "post_text", "e107" => "thread_thread", "type" => "STRING"),
		array("phpbb" => "topic_poster", "e107" => "thread_user", "type" => "INT"), 
		array("phpbb" => "topic_time", "e107" => "thread_datestamp", "type" => "INT"),
		array("phpbb" => "topic_views", "e107" => "thread_views", "type" => "INT"),
		array("phpbb" => "topic_replies", "e107" => "thread_total_replies", "type" => "INT"), 
		array("phpbb" => "null", "e107" => "thread_parent", "type" => "INT", "value" => 0), 
		array("phpbb" => "null", "e107" => "thread_anon", "type" => "INT", "value" => $poster)
	);
	return $topicArray;
}




function convertForumPosts($parent_id, $poster)
{
	$postArray = array(
		array("phpbb" => "post_text", "e107" => "thread_thread", "type" => "STRING"),
		array("phpbb" => "forum_id", "e107" => "thread_forum_id", "type" => "INT"),
		array("phpbb" => "post_time", "e107" => "thread_datestamp", "type" => "INT"),
		array("phpbb" => "topic_views", "e107" => "thread_views", "type" => "INT"),
		array("phpbb" => "post_time", "e107" => "thread_lastpost", "type" => "INT"),
		array("phpbb" => "poster_id", "e107" => "thread_user", "type" => "INT"), 
		array("phpbb" => "post_subject", "e107" => "thread_name", "type" => "STRING"), 
		array("phpbb" => "null", "e107" => "thread_parent", "type" => "INT", "value" => $parent_id), 
		array("phpbb" => "null", "e107" => "thread_anon", "type" => "INT", "value" => $poster)
	);
	return $postArray;
}






function createQuery($convertArray, $dataArray, $table)
{
	global $tp;

	$columns = "(";
	$values = "(";


	foreach($convertArray as $convert)
	{
		if($convert['type'] == "STRING")
		{
			$dataArray[$convert['phpbb']] = preg_replace("#\[.*\]#", "", $tp -> toDB($dataArray[$convert['phpbb']]));
		}
		$columns .= $convert['e107'].",";
		$values .= (array_key_exists("value", $convert) ? "'".$convert['value']."'," : "'".$dataArray[$convert['phpbb']]."',");
	}
	

	$columns = substr($columns, 0, -1).")";
	$values = substr($values, 0, -1).")";

	//echo "INSERT INTO $table $columns VALUES $values<br />";

	return "INSERT INTO $table $columns VALUES $values";
	
}	










?>