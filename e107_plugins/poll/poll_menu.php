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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/poll/poll_menu.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-03-04 12:41:24 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
if(!defined("e_HANDLER")){ exit; }

if(defined("POLLRENDERED"))
{
//	return;
}
if(!defined("POLLCLASS"))
{
	require_once(e_PLUGIN."poll/poll_class.php");
}
if(!is_object($poll))
{
	$poll = new poll;
}

if(!defined("POLL_1"))
{
	/* if menu is being called from comments, lan files have to be included manually ... */
	@include(e_PLUGIN."poll/languages/".e_LANGUAGE.".php");
	@include(e_PLUGIN."poll/languages/English.php");
}

$query = "SELECT p.*, u.user_name FROM #polls AS p 
LEFT JOIN #user AS u ON p.poll_admin_id = u.user_id
WHERE p.poll_vote_userclass!=255 AND p.poll_type=1
ORDER BY p.poll_datestamp DESC LIMIT 0,1
";

if ($sql->db_Select_gen($query))
{
	$pollArray = $sql -> db_Fetch();

	if (!check_class($pollArray['poll_vote_userclass']))
	{
		$POLLMODE = "disallowed";
	}
	else
	{

		switch($pollArray['poll_storage_method'])
		{
			case 0:
				$userid = "";
				$cookiename = "poll_".$pollArray['poll_id'];
				if(isset($_COOKIE[$cookiename]))
				{
					$POLLMODE = "voted";
				}
				else
				{
					$POLLMODE = "notvoted";
				}
			break;

			case 1:
				$userid = getip();
				$voted_ids = explode("^", substr($pollArray['poll_ip'], 0, -1));
				if (in_array($userid, $voted_ids))
				{
					$POLLMODE = "voted";
				}
				else
				{
					$POLLMODE = "notvoted";
				}
			break;

			case 2:
				if(!USER)
				{
					$POLLMODE = "disallowed";
				}
				else
				{
					$userid = USERID;
					$voted_ids = explode("^", substr($pollArray['poll_ip'], 0, -1));
					if (in_array($userid, $voted_ids))
					{
						$POLLMODE = "voted";
					}
					else
					{
						$POLLMODE = "notvoted";
					}
				}
			break;
		}
	}

	if(isset($_POST['pollvote']))
	{
			if ($_POST['votea'])
			{
				$sql -> db_Select("polls", "*", "poll_vote_userclass!=255 AND poll_type=1 ORDER BY poll_datestamp DESC LIMIT 0,1");
				$row = $sql -> db_Fetch();
				extract($row);
				$votes = explode(chr(1), $poll_votes);
				if(is_array($_POST['votea']))
				{
					/* multiple choice vote */
					foreach($_POST['votea'] as $vote)
					{
						$votes[($vote-1)] ++;
					}
				}
				else
				{
					$votes[($_POST['votea']-1)] ++;
				}

				$votep = implode(chr(1), $votes);
				$pollArray['poll_votes'] = $votep;

				$sql->db_Update("polls", "poll_votes = '$votep', poll_ip='".$poll_ip.$userid."^' WHERE poll_id=".$poll_id);
				$POLLMODE = "voted";

			
		}
	}

	$poll->render_poll($pollArray, "menu", $POLLMODE);
}

?>