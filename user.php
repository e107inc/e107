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
e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

// Next bit is to fool PM plugin into doing things
global $user;
$user['user_id'] = USERID;

// BC for v1.x template
$bcList = array(
	'LAN_112'   => 'LAN_USER_60',// Email Address
	'LAN_138'   => 'LAN_USER_52', // Registered members
	'LAN_139'   => 'LAN_USER_57', // Order
	"LAN_142"   => "LAN_USER_58", // Member
	"LAN_145"   => "LAN_USER_59", // Joined
	"LAN_146"   => "LAN_USER_66", // Visits since...
	"LAN_147"   => "LAN_USER_67", // Chatbox posts
	"LAN_148"   => "LAN_USER_68", // Comments posted
	"LAN_149"   => "LAN_USER_69", // Forum posts
	"LAN_308"   => "LAN_USER_63", // Real Name
	"LAN_403"   => "LAN_USER_64", // Site Stats
	"LAN_404"   => "LAN_USER_65", // Last visit
	"LAN_419"   => "LAN_SHOW", // Show
	"LAN_425"   => "LAN_USER_62" // Send Private Message
);

e107::getLanguage()->bcDefs($bcList);



if(e_AJAX_REQUEST)
{
	if(vartrue($_POST['q']))
	{
		$db = e107::getDb();
		$tp = e107::getParser();

		$q = $tp->filter($_POST['q']);
		$l = vartrue($_POST['l']) ? intval($_POST['l']) : 10;

		$where = "user_name LIKE '". $q."%' ";

		//TODO FIXME Filter by userclass.  - see $frm->userlist().


		if($db->select("user", "user_id,user_name", $where. " ORDER BY user_name LIMIT " . $l))
		{
			$data = array();
			while($row = $db->fetch())
			{
				$data[] = array(
					'value' => $row['user_id'],
					'label' => $row['user_name'],
				);
			}

			if(count($data))
			{
				$ajax = e107::getAjax();
				$ajax->response($data);
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
		$row = $sql->fetch();
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
	e107::getMessage()->addDebug( "Using v1.x user template");
	include(e107::coreTemplatePath('user')); //correct way to load a core template. (don't use 'include_once' in case it has already been loaded).
}
else
{
	$USER_FULL_TEMPLATE         = $USER_TEMPLATE['view'];
	$USER_SHORT_TEMPLATE_START  = $USER_TEMPLATE['list']['start'] ;
	$USER_SHORT_TEMPLATE        = $USER_TEMPLATE['list']['item'] ;
	$USER_SHORT_TEMPLATE_END    = $USER_TEMPLATE['list']['end'];
}

$USER_FULL_TEMPLATE = str_replace('{USER_EMBED_USERPROFILE}','{USER_ADDONS}', $USER_FULL_TEMPLATE); // BC Fix

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
	$ns->tablerender(LAN_ERROR, "<div style='text-align:center'>".LAN_USER_55."</div>");
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
	$user_exists = $sql->count("user","(*)", "WHERE user_id = ".$id."");
	if($id == 0 || $user_exists == false)
	{
		$text = "<div style='text-align:center'>".LAN_USER_49." ".SITENAME."</div>";
		$ns->tablerender(LAN_ERROR, $text);
		require_once(FOOTERF);
		exit;
	}

	$loop_uid = $id;

	$ret = e107::getEvent()->trigger("showuser", $id);
	$ret2 = e107::getEvent()->trigger('user_profile_display',$id);

	if (!empty($ret) || !empty($ret2))
	{
		$text = "<div style='text-align:center'>".$ret."</div>";
		$ns->tablerender(LAN_ERROR, $text);
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
		$ns->tablerender(LAN_ERROR, $text);
	}
	unset($text);
	require_once(FOOTERF);
	exit;
}

// $users_total = $sql->count("user","(*)", "WHERE user_ban = 0");



	// --------------------- List Users ------------------------  //TODO Put all of this into a class.

	$users_total=  $sql->count("user","(*)", "WHERE user_ban = 0");
	$query = "SELECT u.*, ue.* FROM `#user` AS u LEFT JOIN `#user_extended` AS ue ON u.user_id = ue.user_extended_id WHERE u.user_ban = 0 ORDER BY u.user_id ".$order." LIMIT ".intval($from).",".intval($records);

	if (!$data = $sql->retrieve($query,true))

	// if (!$sql->select("user", "*", "user_ban = 0 ORDER BY user_id $order LIMIT $from,$records"))
	{
		echo "<div style='text-align:center'><b>".LAN_USER_53."</b></div>";
	}
	else
	{
		// $userList = $sql->db_getList();
		$sc = e107::getScBatch('user');
		$text = $tp->parseTemplate($USER_SHORT_TEMPLATE_START, TRUE, $sc);

		foreach ($data as $row)
		{
			$loop_uid = $row['user_id'];

		//	$text .= renderuser($row, "short");
			$sc->setVars($row);
			$sc->wrapper('user/list');

			$text .= $tp->parseTemplate($USER_SHORT_TEMPLATE, TRUE, $sc);
		}

		$text .= $tp->parseTemplate($USER_SHORT_TEMPLATE_END, TRUE, $sc);
	}

	$ns->tablerender(LAN_USER_52, $text, 'user-list');

	$parms = $users_total.",".$records.",".$from.",".e_SELF.'?[FROM].'.$records.".".$order;
	echo "<div class='nextprev form-inline'>&nbsp;".$tp->parseTemplate("{NEXTPREV={$parms}}")."</div>";











function renderuser($uid, $mode = "verbose")
{
	global $pref, $sc_style, $user_shortcodes;
	global $EXTENDED_START, $EXTENDED_TABLE, $EXTENDED_END, $USER_SHORT_TEMPLATE, $USER_FULL_TEMPLATE, $USER_TEMPLATE;
	global $user;

	$tp = e107::getParser();

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

	$user_shortcodes->setVars($user);
	$user_shortcodes->setScVar('userProfile', $user);

	e107::setRegistry('core/user/profile', $user);

	if($mode == 'verbose')
	{
		return $tp->parseTemplate( $USER_FULL_TEMPLATE, TRUE, $user_shortcodes);
	}
	else
	{
		return $tp->parseTemplate($USER_SHORT_TEMPLATE, TRUE, $user_shortcodes);
	}
}

require_once(FOOTERF);
?>
