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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/poll/poll_menu.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:35:40 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

global $e107;

if(defined("POLLRENDERED"))
{
//	return;
}
if(!defined("POLLCLASS"))
{
	require(e_PLUGIN."poll/poll_class.php");
}
if(!isset($poll) || !is_object($poll))
{
	$poll = new poll;
}

if(!defined("POLL_1"))
{
	/* if menu is being called from comments, lan files have to be included manually ... */
	@include_once(e_PLUGIN."poll/languages/".e_LANGUAGE.".php");
	@include_once(e_PLUGIN."poll/languages/English.php");
}

$query = "SELECT p.*, u.user_name FROM #polls AS p 
LEFT JOIN #user AS u ON p.poll_admin_id = u.user_id
WHERE p.poll_vote_userclass!=255 AND p.poll_type=1
ORDER BY p.poll_datestamp DESC LIMIT 0,1
";

$poll->render_poll($query, "menu", "query");

?>