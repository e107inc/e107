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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/login_menu/login_menu.php,v $
|     $Revision: 1.7 $
|     $Date: 2008-02-01 00:37:10 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

if(defined("FPW_ACTIVE"))
{
	return;      // prevent failed login attempts when fpw.php is loaded before this menu.
}

global $eMenuActive, $e107, $tp, $use_imagecode, $ADMIN_DIRECTORY, $LOGIN_MENU_MESSAGE, $LOGIN_MENU_STATITEM,
       $login_menu_shortcodes, $LOGIN_MENU_LOGGED, $LOGIN_MENU_STATS, $LOGIN_MENU_EXTERNAL_LINK;
$ip = $e107->getip();

//shortcodes
    require_once(e_PLUGIN."login_menu/login_menu_shortcodes.php");

//Bullet
	if(defined("BULLET"))
	{
   		$bullet = "<img src='".THEME_ABS."images/".BULLET."' alt='' style='vertical-align: middle;' />";
	}
	elseif(file_exists(THEME."images/bullet2.gif"))
	{
		$bullet = "<img src='".THEME_ABS."images/bullet2.gif' alt='bullet' style='vertical-align: middle;' />";
	}
	else
	{
		$bullet = "";
	}

//Corrup cookie
    if (defined('CORRUPT_COOKIE') && CORRUPT_COOKIE == TRUE)
    {
    	$text = "<div class='core-sysmsg loginbox'>".LOGIN_MENU_L7."<br /><br />
    	{$bullet} <a href='".SITEURL."index.php?logout'>".LOGIN_MENU_L8."</a></div>";
    	$ns->tablerender(LOGIN_MENU_L9, $text, 'loginbox_error');
    }
    
//Image code
    $use_imagecode = ($pref['logcode'] && extension_loaded('gd'));
    
    if ($use_imagecode)
    {
    	global $sec_img;
    	include_once(e_HANDLER.'secure_img_handler.php');
    	$sec_img = new secure_image;
    }

    $text = '';
    
// START LOGGED CODE
if (USER == TRUE || ADMIN == TRUE)
{
    require_once(e_PLUGIN."login_menu/login_menu_class.php");

    //login class ??? - REMOVE IT
	if ($sql->db_Select('online', 'online_ip', "`online_ip` = '{$ip}' AND `online_user_id` = '0' "))
	{	// User now logged in - delete 'guest' record (tough if several users on same IP)
		$sql->db_Delete('online', "`online_ip` = '{$ip}' AND `online_user_id` = '0' ");
	}

	//get templates
    if (!isset($LOGIN_MENU_LOGGED)) {
		if (file_exists(THEME."login_menu_template.php")){
	   		require(THEME."login_menu_template.php");
		}else{
			require(e_PLUGIN."login_menu/login_menu_template.php");
		}
	}
	if(!$LOGIN_MENU_LOGGED){
    	require(e_PLUGIN."login_menu/login_menu_template.php");
	}

    //prepare
	$new_total = 0;
	$time = USERLV;
	$menu_data = array();

		// ------------ News Stats -----------

		if (varsettrue($menu_pref['login_menu']['new_news']))
		{
			$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
            $menu_data['new_news'] = $sql->db_Count("news", "(*)", "WHERE `news_datestamp` > {$time} AND news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (news_class REGEXP ".$nobody_regexp.")");
			$new_total += $menu_data['new_news'];
		}

		// ------------ Comments Stats -----------

		if (varsettrue($menu_pref['login_menu']['new_comments']))
		{
			$menu_data['new_comments'] = $sql->db_Count('comments', '(*)', 'WHERE `comment_datestamp` > '.$time);
			$new_total += $menu_data['new_comments'];
		}
		
/*
		// ------------ Chatbox Stats -----------

		if (varsettrue($menu_pref['login_menu']['new_chatbox']) && in_array('chatbox_menu',$eMenuActive)) 
        {
            $menu_data['new_chat'] = $sql->db_Count('chatbox', '(*)', 'WHERE `cb_datestamp` > '.$time);
			$new_total += $menu_data['new_chat'];
		}

		// ------------ Forum Stats -----------

		if (varsettrue($menu_pref['login_menu']['new_forum']) && array_key_exists('forum', $pref['plug_installed'])) 
        {
			$qry = "
			SELECT  count(*) as count FROM #forum_t  as t
			LEFT JOIN #forum as f
			ON t.thread_forum_id = f.forum_id
			WHERE t.thread_datestamp > {$time} and f.forum_class IN (".USERCLASS_LIST.")
			";
			
			if($sql->db_Select_gen($qry))
			{
				$row = $sql->db_Fetch();
				$menu_data['new_forum'] = $row['count'];
				$new_total += $menu_data['new_forum'];
			}
		}
*/

		// ------------ Member Stats -----------

		if (varsettrue($menu_pref['login_menu']['new_members'])) 
        {
			$menu_data['new_users'] = $sql->db_Count('user', '(user_join)', 'WHERE user_join > '.$time);
			$new_total += $menu_data['new_users'];
		}
		
		// ------------ Enable stats / other ---------------
		
		$menu_data['enable_stats'] = $menu_data ? true : false;
		$menu_data['new_total'] = $new_total;
		$menu_data['link_bullet'] = $bullet;
		
		// ------------ List New Link ---------------
		
		$menu_data['listnew_link'] = '';
		if ($new_total && array_key_exists('list_new', $pref['plug_installed'])) 
        {
            $menu_data['listnew_link'] = e_PLUGIN.'list_new/list.php?new';
		}

		// ------------ Pass the data & parse ------------
		cachevars('login_menu_data', $menu_data);
		$text = $tp->parseTemplate($LOGIN_MENU_LOGGED, true, $login_menu_shortcodes);
    
    //menu caption
	if (file_exists(THEME.'images/login_menu.png')) {
		$caption = '<img src="'.THEME_ABS.'images/login_menu.png" alt="" />'.LOGIN_MENU_L5.' '.USERNAME;
	} else {
		$caption = LOGIN_MENU_L5.' '.USERNAME;
	}
	
	//render
	$ns->tablerender($caption, $text, 'login');

// END LOGGED CODE	
} 
// START NOT LOGGED CODE	
else 
{
    //get templates
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
    
    
	//if (strpos(e_SELF, $ADMIN_DIRECTORY) === FALSE)
	//{
        /*
		if (LOGINMESSAGE != '') {
			$text = $tp->parseTemplate($LOGIN_MENU_MESSAGE, true, $login_menu_shortcodes);
		}*/

		$text = '<form method="post" action="'.e_SELF.(e_QUERY ? '?'.e_QUERY : '').'">';
		$text .= $tp->parseTemplate($LOGIN_MENU_FORM, true, $login_menu_shortcodes);
		$text .= '</form>';
	//} else {
	//	$text = $tp->parseTemplate("<div style='padding-top: 150px'>{LM_FPW_LINK}</div>", true, $login_menu_shortcodes);
	//}


	if (file_exists(THEME.'images/login_menu.png')) {
		$caption = '<img src="'.THEME_ABS.'images/login_menu.png" alt="" />'.LOGIN_MENU_L5;
	} else {
		$caption = LOGIN_MENU_L5;
	}
	$ns->tablerender($caption, $text, 'loginbox');
}
// END NOT LOGGED CODE
?>