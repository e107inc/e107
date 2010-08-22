<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
|
|     31/1/2006  Changes by Albert Drent
|                Aducom Software
|                www.aducom.com
|
|	  20/4/2006  Tweaks by steved, based on information from Prodigal and forum thread:
|					Processing routine made more generic
|					BBCode processing options - strip, phpbb processing, mapping
|					Allows setting of default for null field
|					Forces null integer field to zero (required for mySQL5?)
|					Sets forum_moderators to empty
|					User last visit and reg. date added (may not be suitable format)
|
|	  17/5/2006  Tweak to bring across admins as well, other than user ID = 1 (which will be E107 main admin)
|				 Include of mapper function moved to earlier in file to try and avoid error.
|	  18/5/2006  Table added to convert IMG and URL bbcodes to lower case.
|	  25/5/2006  Modifications to original script made by McFly (version 1.5) added where appropriate.
|	  10/6/2006  Bug fix - user_join mapping line specified twice
|				 BBCodes now decoded in signatures as well as forums
|	  11/6/2006  zeronull code added to try and sort user_perms
|
| Note: Apparently no field in the phpbb database with which to populate 'last post' - but can
|			be recalculated through E107.
|
| Note: Typically phpbb tables are prefixed '_phpbb_'
+----------------------------------------------------------------------------+
*/

require_once("../../class2.php");
require_once(e_ADMIN."auth.php");

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
	require_once(e_ADMIN."footer.php");
	exit;
}

if(!isset($_POST['phpbb2Host']) || !isset($_POST['phpbb2Username']) || !isset($_POST['phpbb2Password']) || !isset($_POST['phpbb2Database']))
{
	echo "Field(s) left blank, please go back and re-enter values.";
	require_once(e_ADMIN."footer.php");
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
Attempting to connect to phpBB database [ {$phpbb2Database} @ {$phpbb2Host} ] ...<br />\n";
flush();

$phpbbConnection = mysql_connect($phpbb2Host, $phpbb2Username, $phpbb2Password, TRUE);
if(!mysql_select_db($phpbb2Database, $phpbbConnection))
{
	goError("Error! Could not connect to phpBB database. Please go back to the previous page and check your settings");
}

$e107Connection = mysql_connect($mySQLserver, $mySQLuser, $mySQLpassword, TRUE);
if(!mysql_select_db($mySQLdefaultdb, $e107Connection))
{
	goError("Error! Could not connect to e107 database.");
}

echo "Successfully connected to phpBB and e107 databases ...<br /><br />";


$phpbb_res = mysql_query("SELECT * FROM {$phpbb2Prefix}users", $phpbbConnection);
if(!$phpbb_res)
{
	goError("Error! Unable to access ".$phpbb2Prefix."users table.");
}

require_once('import_mapper.php');


//------------------------------------------------------
//      Convert users
//------------------------------------------------------
while($user = mysql_fetch_array($phpbb_res))
{
	$userArray = convertUsers();
//	if($user['user_level'] != 1 && $user['user_id'] != -1)
// Convert any user other than ID=1 (which will be E107 main admin)
	if($user['user_id'] > 1)
	{
		$query = createQuery($userArray, $user, $mySQLprefix."user");		
		echo (mysql_query($query, $e107Connection) ? "Successfully inserted user: ".$user['user_id'].": ".$user['username'] : "Unable to insert user: ".$user['user_id'].": ".$user['username']."<br />".mysql_errno() . ": " . mysql_error())."<br />";
		flush();
	}
}


//-----------------------------------------------------------
// ### get phpbb categories and insert them as forum parents
//-----------------------------------------------------------

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


//------------------------------------------------------
//          Read in forum topics
//------------------------------------------------------

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
		$vote_text = $tp->toDB($vote_text);		// McFly added 25/5/06
        $options = $tp->toDB($options);			// McFly added 25/5/06
		$query = "INSERT INTO ".$mySQLprefix."polls VALUES ('0', {$vote_start}, {$vote_start}, 0, 0, '{$vote_text}', '{$options}', '{$votes}', '', 2, 0, 0, 0, 255, 0)";
		echo (mysql_query($query, $e107Connection) ? "Poll successfully inserted" : "Unable to insert poll ({$query})")."<br />";
	}


	if($topic['topic_poster'] == 2)
	{
		$topic['topic_poster'] = 1;
	}

	if($topic['topic_poster'] == -1)
	{
		$poster = ($topic['post_username'] ? $topic['post_username'] : "Anonymous");
		$topic['topic_poster'] = "0.".$poster; 		// McFly moved, edited 25/5/06
	}

	$topicArray = convertTopics();					// McFly edited 25/5/06
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

// Amending next line to not require blank post_subject might work better
//		$query = "SELECT * FROM {$phpbb2Prefix}posts LEFT JOIN {$phpbb2Prefix}posts_text ON ({$phpbb2Prefix}posts.post_id = {$phpbb2Prefix}posts_text.post_id) WHERE topic_id='{$topic_id}' AND post_subject = '' ORDER BY post_time DESC";
		$query = "SELECT * FROM {$phpbb2Prefix}posts LEFT JOIN {$phpbb2Prefix}posts_text ON ({$phpbb2Prefix}posts.post_id = {$phpbb2Prefix}posts_text.post_id) WHERE topic_id='{$topic_id}' ORDER BY post_time DESC";
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
				$poster = ($post['post_username'] ? $post['post_username'] : "Anonymous");
				$post['poster_id'] = "0.".$poster;		// McFly moved, edited 25/5/06
			}
	

			$postArray = convertForumPosts($parent_id, $poster);
			$query = createQuery($postArray, $post, $mySQLprefix."forum_t",$mapdata);	
			echo (mysql_query($query, $e107Connection) ? "Successfully inserted thread: ".$post['post_id'] : "Unable to insert thread: ".$parent['cat_id'].": ".$parent['cat_title']."<br />".mysql_errno() . ": " . mysql_error())."<br />";
			flush();
		}
	}
}

//------------------------------------------------------
//          Consider polls here later
//------------------------------------------------------

echo "</td></tr></table>";

require_once(e_ADMIN."footer.php");


function goError($error)
{
	echo "<b>{$error}</b></td></tr></table>";
	require_once(e_ADMIN."footer.php");
	exit;
}

//-----------------------------------------------------------
//     Table to convert selected bbcodes to lower case
//-----------------------------------------------------------
//$mapdata = array("URL" => "url","IMG" => "img");
$mapdata = array();

function convertUsers()
{
	$usersArray = array(
		array("srcdata" => "user_id", "e107" => "user_id", "type" => "INT"),
		array("srcdata" => "username", "e107" => "user_name", "type" => "STRING"),
		array("srcdata" => "username", "e107" => "user_loginname", "type" => "STRING"),
		array("srcdata" => "user_password", "e107" => "user_password", "type" => "STRING"),
		array("srcdata" => "user_email", "e107" => "user_email", "type" => "STRING"),
		array("srcdata" => "user_sig", "e107" => "user_signature", "type" => "STRING", "sproc" => "usebb,phpbb,bblower"),
		array("srcdata" => "user_viewemail", "e107" => "user_hideemail", "type" => "INT"),
		array("srcdata" => "user_regdate", "e107" => "user_join", "type" => "INT"),
		array("srcdata" => "user_posts", "e107" => "user_forums", "type" => "INT"),
		array("srcdata" => "user_level", "e107" => "user_admin", "type" => "INT"),
		array("srcdata" => "user_lastvisit","e107" => "user_lastvisit", "type" => "INT"),
// Rest of these added by McFly 
		array("srcdata" => "null", "e107" => "user_prefs", "type" => "INT", "value" => 0),
        array("srcdata" => "null", "e107" => "user_new", "type" => "INT", "value" => 0),
        array("srcdata" => "null", "e107" => "user_realm", "type" => "INT", "value" => 0),
        array("srcdata" => "null", "e107" => "user_class", "type" => "INT", "value" => 0),
        array("srcdata" => "null", "e107" => "user_viewed", "type" => "INT", "value" => 0),
// This one changed from McFly's code to try and get null string if non-admin
        array("srcdata" => "user_level", "e107" => "user_perms", "type" => "INT", "sproc" => "zeronull")	);
	return $usersArray;
}

function convertParents($catid)
{
	$parentArray = array(
		array("srcdata" => "cat_id", "e107" => "forum_id", "type" => "INT", "value" => $catid),
		array("srcdata" => "cat_title", "e107" => "forum_name", "type" => "STRING"),
		array("srcdata" => "cat_order", "e107" => "forum_order", "type" => "INT"),
// Rest of these added by McFly
		array("srcdata" => "cat_desc", "e107" => "forum_description", "type" => "STRING"),
        array("srcdata" => "null", "e107" => "forum_moderators", "type" => "INT", "value" => 254)
	);
	return $parentArray;
}

function convertForums($catid)
{
	$forumArray = array(
		array("srcdata" => "forum_id", "e107" => "forum_id", "type" => "INT"),
		array("srcdata" => "cat_id", "e107" => "forum_parent", "type" => "STRING", "value" => $catid),
		array("srcdata" => "forum_name", "e107" => "forum_name", "type" => "STRING"),
		array("srcdata" => "forum_desc", "e107" => "forum_description", "type" => "STRING"),
		array("srcdata" => "forum_order", "e107" => "forum_order", "type" => "INT"),
		array("srcdata" => "forum_topics", "e107" => "forum_threads", "type" => "INT"),
		array("srcdata" => "forum_posts", "e107" => "forum_replies", "type" => "INT"),
//		array("srcdata" => "null", "e107" => "forum_moderators", "type" => "STRING")
// Previous line replaced with this on the basis that McFly knows best
		array("srcdata" => "null", "e107" => "forum_moderators", "type" => "INT", "value" => 254)
	);
	return $forumArray;
}


//function convertTopics($poster)
// Changed by McFly
function convertTopics()
{
	$topicArray = array(
		array("srcdata" => "forum_id", "e107" => "thread_forum_id", "type" => "INT"),
		array("srcdata" => "topic_title", "e107" => "thread_name", "type" => "STRING"),
		array("srcdata" => "post_text", "e107" => "thread_thread", "type" => "STRING", "default" => "", "sproc" => "usebb,phpbb,bblower"),
//		array("srcdata" => "topic_poster", "e107" => "thread_user", "type" => "INT"),
// Previous line replaced by next - GAT McFly
		array("srcdata" => "topic_poster", "e107" => "thread_user", "type" => "STRING"),
		array("srcdata" => "null", "e107" => "thread_active", "type" => "INT", "value" => 1),
		array("srcdata" => "topic_time", "e107" => "thread_datestamp", "type" => "INT"),
		array("srcdata" => "topic_views", "e107" => "thread_views", "type" => "INT"),
		array("srcdata" => "topic_replies", "e107" => "thread_total_replies", "type" => "INT"),
		array("srcdata" => "null", "e107" => "thread_parent", "type" => "INT", "value" => 0),
	);
	return $topicArray;
}




function convertForumPosts($parent_id, $poster)
{
	$postArray = array(
		array("srcdata" => "post_text", "e107" => "thread_thread", "type" => "STRING", "default" => "", "sproc" => "usebb,phpbb,bblower"),
		array("srcdata" => "forum_id", "e107" => "thread_forum_id", "type" => "INT"),
		array("srcdata" => "post_time", "e107" => "thread_datestamp", "type" => "INT"),
		array("srcdata" => "topic_views", "e107" => "thread_views", "type" => "INT"),
		array("srcdata" => "post_time", "e107" => "thread_lastpost", "type" => "INT"),
//		array("srcdata" => "poster_id", "e107" => "thread_user", "type" => "INT"),
// Previous line replaced by next - GAT McFly
		array("srcdata" => "poster_id", "e107" => "thread_user", "type" => "STRING"),
		array("srcdata" => "post_subject", "e107" => "thread_name", "type" => "STRING"),
		array("srcdata" => "null", "e107" => "thread_parent", "type" => "INT", "value" => $parent_id),
	);
	return $postArray;
}


/*
-- --------------------------------------------------------
PHPBB uses three tables to record a poll. Looks wildly different to E107!
-- 
-- Table structure for table `_phpbb_vote_desc`
-- 

CREATE TABLE `_phpbb_vote_desc` (
  `vote_id` mediumint(8) unsigned NOT NULL auto_increment,
  `topic_id` mediumint(8) unsigned NOT NULL default '0',
  `vote_text` text NOT NULL,
  `vote_start` int(11) NOT NULL default '0',
  `vote_length` int(11) NOT NULL default '0',
  PRIMARY KEY  (`vote_id`),
  KEY `topic_id` (`topic_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=36 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `_phpbb_vote_results`
-- 

CREATE TABLE `_phpbb_vote_results` (
  `vote_id` mediumint(8) unsigned NOT NULL default '0',
  `vote_option_id` tinyint(4) unsigned NOT NULL default '0',
  `vote_option_text` varchar(255) NOT NULL default '',
  `vote_result` int(11) NOT NULL default '0',
  KEY `vote_option_id` (`vote_option_id`),
  KEY `vote_id` (`vote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

-- 
-- Table structure for table `_phpbb_vote_voters`
-- 

CREATE TABLE `_phpbb_vote_voters` (
  `vote_id` mediumint(8) unsigned NOT NULL default '0',
  `vote_user_id` mediumint(8) NOT NULL default '0',
  `vote_user_ip` char(8) NOT NULL default '',
  KEY `vote_id` (`vote_id`),
  KEY `vote_user_id` (`vote_user_id`),
  KEY `vote_user_ip` (`vote_user_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

*/

?>