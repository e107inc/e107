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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/login_menu/login_menu.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

if(defined("FPW_ACTIVE"))
{
	return;      // prevent failed login attempts when fpw.php is loaded before this menu.
}

global $eMenuActive, $e107, $tp, $use_imagecode, $ADMIN_DIRECTORY,$bullet;
require_once(e_PLUGIN."login_menu/login_menu_shortcodes.php");
$ip = $e107->getip();

if (defined('CORRUPT_COOKIE') && CORRUPT_COOKIE == TRUE)
{
	$text = "<div style='text-align:center'>".LOGIN_MENU_L7."<br /><br />
	".$bullet." <a href='".e_BASE."index.php?logout'>".LOGIN_MENU_L8."</a></div>";
	$ns->tablerender(LOGIN_MENU_L9, $text, 'login');
}
$use_imagecode = ($pref['logcode'] && extension_loaded('gd'));

if ($use_imagecode)
{
	global $sec_img;
	include_once(e_HANDLER.'secure_img_handler.php');
	$sec_img = new secure_image;
}
$text = '';
if (USER == TRUE || ADMIN == TRUE)
{
	if (!isset($LOGIN_MENU_LOGGED)) {
		if (file_exists(THEME."login_menu_template.php")){
	   		require(THEME."login_menu_template.php");
		}else{
			require(e_PLUGIN."login_menu/login_menu_template.php");
		}
	}

	if(!$LOGIN_MENU_LOGGED){ // if still doesn't exist in the user template.
    	require(e_PLUGIN."login_menu/login_menu_template.php");
	}

    $text = $tp->parseTemplate($LOGIN_MENU_LOGGED, true, $login_menu_shortcodes);

	if ($sql->db_Select('online', 'online_ip', "`online_ip` = '{$ip}' AND `online_user_id` = '0' "))
	{	// User now logged in - delete 'guest' record (tough if several users on same IP)
		$sql->db_Delete('online', "`online_ip` = '{$ip}' AND `online_user_id` = '0' ");
	}

	$new_total = 0;
	$time = USERLV;

		// ------------ News Stats -----------

		if (isset($menu_pref['login_menu']) && $menu_pref['login_menu']['new_news'] == true)
		{
			$new_news = $sql->db_Count("news", "(*)", "WHERE `news_datestamp` > {$time} AND news_class REGEXP '".e_CLASS_REGEXP."'");
			$new_total += $new_news;
			if (!$new_news)
			{
				$new_news = LOGIN_MENU_L26;
			}
			$NewItems[] = $new_news.' '.($new_news == 1 ? LOGIN_MENU_L14 : LOGIN_MENU_L15);
		}

		// ------------ Comments Stats -----------

		if (varsettrue($menu_pref['login_menu']['new_comments'], false) == true)
		{
			$new_comments = 0;
			$new_comments = $sql->db_Count('comments', '(*)', 'WHERE `comment_datestamp` > '.$time);
			$new_total += $new_comments;
			if (!$new_comments)
			{
				$new_comments = LOGIN_MENU_L26;
			}
			$NewItems[] = $new_comments.' '.($new_comments == 1 ? LOGIN_MENU_L18 : LOGIN_MENU_L19);
		}

		// ------------ Chatbox Stats -----------

		if (isset($menu_pref['login_menu']['new_chatbox']) && $menu_pref['login_menu']['new_chatbox'] == true) {
			$display_chats = TRUE;

			if(in_array('chatbox_menu',$eMenuActive)){
				$new_chat = $sql->db_Count('chatbox', '(*)', 'WHERE `cb_datestamp` > '.$time);
				$new_total += $new_chat;
			} else {
				$display_chats = FALSE;
			}
			if (isset($new_chat) && !$new_chat) {
				$new_chat = ($display_chats ? LOGIN_MENU_L26 : '');
			}
			if ($display_chats == true) {
				$NewItems[] = $new_chat.' '.($new_chat == 1 ? LOGIN_MENU_L16 : LOGIN_MENU_L17);

			}
		}

		// ------------ Forum Stats -----------

		if (isset($menu_pref['login_menu']['new_forum']) && $menu_pref['login_menu']['new_forum'] == true) {
			$qry = "
			SELECT  count(*) as count FROM #forum_t  as t
			LEFT JOIN #forum as f
			ON t.thread_forum_id = f.forum_id
			WHERE t.thread_datestamp > {$time} and f.forum_class IN (".USERCLASS_LIST.")
			";
			if($sql->db_Select_gen($qry))
			{
				$row = $sql->db_Fetch();
				$new_forum = $row['count'];
				$new_total += $new_forum;
			}
			if (!$new_forum) {
				$new_forum = LOGIN_MENU_L26;
			}
			$NewItems[] = $new_forum.' '.($new_forum == 1 ? LOGIN_MENU_L20 : LOGIN_MENU_L21);
		}

		// ------------ Member Stats -----------

		if (isset($menu_pref['login_menu']['new_members']) && $menu_pref['login_menu']['new_members'] == true) {
			$new_users = $sql->db_Count('user', '(user_join)', 'WHERE user_join > '.$time);
			$new_total += $new_users;
			if (!$new_users) {
				$new_users = LOGIN_MENU_L26;
			}
			$NewItems[] = $new_users.' '.($new_users == 1 ? LOGIN_MENU_L22 : LOGIN_MENU_L23);
		}
		if (isset($NewItems) && $NewItems) {
			$text .= '<br /><br /><span class="smalltext">'.LOGIN_MENU_L25.'<br />'.implode(',<br />', $NewItems).'</span>';
			if ($new_total) {
				if ($sql -> db_Select("plugin", "plugin_installflag", "plugin_path='list_new' AND plugin_installflag='1'"))
				{
					$text .= '<br /><a href="'.e_PLUGIN.'list_new/list.php?new">'.LOGIN_MENU_L24.'</a>';
				}
			}
		}

	if (file_exists(THEME.'images/login_menu.png')) {
		$caption = '<img src="'.THEME_ABS.'images/login_menu.png" alt="" />'.LOGIN_MENU_L5.' '.USERNAME;
	} else {
		$caption = LOGIN_MENU_L5.' '.USERNAME;
	}
	$ns->tablerender($caption, $text, 'login');
} else {
	if (!$LOGIN_MENU_FORM || !$LOGIN_MENU_MESSAGE) {
		if (file_exists(THEME."login_menu_template.php")){
	   		require_once(THEME."login_menu_template.php");
		}else{
			require_once(e_PLUGIN."login_menu/login_menu_template.php");
		}
	}
	if(!$LOGIN_MENU_FORM || !$LOGIN_MENU_MESSAGE){
    	require(e_PLUGIN."login_menu/login_menu_template.php");
	}

	if (strpos(e_SELF, $ADMIN_DIRECTORY) === FALSE)
	{

		if (LOGINMESSAGE != '') {
			$text = $tp->parseTemplate($LOGIN_MENU_MESSAGE, true, $login_menu_shortcodes);
		}

		$text .= '<form method="post" action="'.e_SELF.(e_QUERY ? '?'.e_QUERY : '').'">';
		$text .= $tp->parseTemplate($LOGIN_MENU_FORM, true, $login_menu_shortcodes);
		$text .= '</form>';
	} else {
		$text = $tp->parseTemplate("<div style='padding-top: 150px'>{LM_FPW_LINK}</div>", true, $login_menu_shortcodes);
	}


	if (file_exists(THEME.'images/login_menu.png')) {
		$caption = '<img src="'.THEME_ABS.'images/login_menu.png" alt="" />'.LOGIN_MENU_L5;
	} else {
		$caption = LOGIN_MENU_L5;
	}
	$ns->tablerender($caption, $text, 'login');
}

?>
