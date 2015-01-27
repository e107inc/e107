<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * User information
 *
 * $URL$
 * $Id$
 *
 */
//HCL define('PAGE_NAME', 'Members');

require_once("class2.php");
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

// Next bit is to fool PM plugin into doing things
global $user;
$user['user_id'] = USERID;

if(e_AJAX_REQUEST)
{
	if(vartrue($_GET['q']))
	{
		$q = filter_var($_GET['q'], FILTER_SANITIZE_STRING);
		if($sql->select("user", "user_id,user_name", "user_name LIKE '". $q."%' ORDER BY user_name LIMIT 15"))
		{
			while($row = $sql->db_Fetch())
			{
				$id = $row['user_id'];
				$data[$id] = $row['user_name'];
			}
			
			if(count($data))
			{
				echo json_encode($data);	
			}
		}		
	}
	exit;
}


// require_once(e_CORE."shortcodes/batch/user_shortcodes.php");
require_once(e_HANDLER."form_handler.php");

if (isset($_POST['delp']))
{
	$tmp = explode(".", e_QUERY);
	if ($tmp[0]=="self")
	{
		$tmp[1]=USERID;
	}
	if (USERID == $tmp[1] || (ADMIN && getperms("4")))
	{
		$sql->select("user", "user_sess", "user_id='". USERID."'");
		$row = $sql->db_Fetch();
		@unlink(e_AVATAR_UPLOAD.$row['user_sess']);
		$sql->update("user", "user_sess='' WHERE user_id=".intval($tmp[1]));
		header("location:".e_SELF."?id.".$tmp[1]);
		exit;
	}
}

$qs = explode(".", e_QUERY);
$self_page =($qs[0] == 'id' && intval($qs[1]) == USERID);


$USER_TEMPLATE = e107::getCoreTemplate('user');
e107::scStyle($sc_style);

if(empty($USER_TEMPLATE)) // BC Fix for loading old templates. 
{
	 echo "DEBUG: Using v1.x user template";
	include_once(e107::coreTemplatePath('user')); //correct way to load a core template.	
}


$TEMPLATE = str_replace('{USER_EMBED_USERPROFILE}','{USER_ADDONS}', $TEMPLATE); // BC Fix

$user_shortcodes = e107::getScBatch('user');
$user_shortcodes->wrapper('user/view');





/*
if (file_exists(THEME."user_template.php"))
{
	require_once(THEME."user_template.php");
}
else
{
	require_once(e_BASE.$THEMES_DIRECTORY."templates/user_template.php");
}
  */

$user_frm = new form;
require_once(HEADERF);
if (!defined("USER_WIDTH")){ define("USER_WIDTH","width:95%"); }

$full_perms = getperms("0") || check_class(varset($pref['memberlist_access'], 253));		// Controls display of info from other users
if (!$full_perms && !$self_page)
{
	$ns->tablerender(LAN_USER_48, "<div style='text-align:center'>".LAN_USER_55."</div>");
	require_once(FOOTERF);
	exit;
}

if (isset($_POST['records']))
{
	$records = intval($_POST['records']);
	$order = ($_POST['order'] == 'ASC' ? 'ASC' : 'DESC');
	$from = 0;
}
else if(!e_QUERY)
{
	$records = 20;
	$from = 0;
	$order = "DESC";
}
else
{
	if ($qs[0] == "self")
	{
		$id = USERID;
	}
	else
	{
		if ($qs[0] == "id")
		{
			$id = intval($qs[1]);
		}
		else
		{
			$qs = explode(".", e_QUERY);
			$from = intval($qs[0]);
			$records = intval($qs[1]);
			$order = ($qs[2] == 'ASC' ? 'ASC' : 'DESC');
		}
	}
}
if (vartrue($records) > 30)
{
	$records = 30;
}

if (isset($id))
{
	if ($id == 0)
	{
		$text = "<div style='text-align:center'>".LAN_USER_49." ".SITENAME."</div>";
		$ns->tablerender(LAN_USER_48, $text);
		require_once(FOOTERF);
		exit;
	}

	$loop_uid = $id;

	$ret = $e_event->trigger("showuser", $id);
	if ($ret!='')
	{
		$text = "<div style='text-align:center'>".$ret."</div>";
		$ns->tablerender(LAN_USER_48, $text);
		require_once(FOOTERF);
		exit;
	}

	if(vartrue($pref['profile_comments']))
	{
		require_once(e_HANDLER."comment_class.php");
		$comment_edit_query = 'comment.user.'.$id;
	}

	if (isset($_POST['commentsubmit']) && $pref['profile_comments'])
	{
		$cobj = new comment;
		$cobj->enter_comment($_POST['author_name'], $_POST['comment'], 'profile', $id, $pid, $_POST['subject']);
	}

	if($text = renderuser($id))
	{
		$ns->tablerender(LAN_USER_50, $text);
	}
	else
	{
		$text = "<div style='text-align:center'>".LAN_USER_51."</div>";
		$ns->tablerender(LAN_USER_48, $text);
	}
	unset($text);
	require_once(FOOTERF);
	exit;
}

$users_total = $sql->db_Count("user","(*)", "WHERE user_ban = 0");

if (!$sql->db_Select("user", "*", "user_ban = 0 ORDER BY user_id $order LIMIT $from,$records"))
{
	echo "<div style='text-align:center'><b>".LAN_USER_53."</b></div>";
}
else
{
	$userList = $sql->db_getList();

	$text = $tp->parseTemplate($USER_SHORT_TEMPLATE_START, TRUE, $user_shortcodes);
	foreach ($userList as $row)
	{
		$loop_uid = $row['user_id'];
		
		$text .= renderuser($row, "short");
	}
	$text .= $tp->parseTemplate($USER_SHORT_TEMPLATE_END, TRUE, $user_shortcodes);
}

$ns->tablerender(LAN_USER_52, $text);

$parms = $users_total.",".$records.",".$from.",".e_SELF.'?[FROM].'.$records.".".$order;
echo "<div class='nextprev'>&nbsp;".$tp->parseTemplate("{NEXTPREV={$parms}}")."</div>";


function renderuser($uid, $mode = "verbose")
{
	global $sql, $pref, $tp, $sc_style, $user_shortcodes;
	global $EXTENDED_START, $EXTENDED_TABLE, $EXTENDED_END, $USER_SHORT_TEMPLATE, $USER_FULL_TEMPLATE, $USER_TEMPLATE;
	global $user;

	if(is_array($uid))
	{
		$user = $uid;
	}
	else
	{
		if(!$user = e107::user($uid))
		{
			return FALSE;
		}
	}
	
	e107::getScBatch('user')->setVars($user);

	if($mode == 'verbose')
	{
		return $tp->parseTemplate($USER_TEMPLATE['view'], TRUE, $user_shortcodes);
	}
	else
	{
		return $tp->parseTemplate($USER_SHORT_TEMPLATE, TRUE, $user_shortcodes);
	}
}

require_once(FOOTERF);
?>