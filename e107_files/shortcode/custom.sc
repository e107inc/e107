$ret = "";
switch($parm)
{
	case "login":
	case "login noprofile":
		@include(e_PLUGIN."login_menu/languages/".e_LANGUAGE.".php");
		@include(e_PLUGIN."login_menu/languages/English.php");
		
		if(USER == TRUE)
		{
			$ret .= "<span class='mediumtext'>".LOGIN_MENU_L5." ".USERNAME."&nbsp;&nbsp;&nbsp;.:. ";
			if(ADMIN == TRUE)
			{
				$ret .= "<a href='".e_ADMIN."admin.php'>".LOGIN_MENU_L11."</a> .:. ";
			}
			$ret .= ($custom != "login noprofile") ? "<a href='".e_BASE."user.php?id.".USERID."'>".LOGIN_MENU_L13."</a>\n.:. ":"";
			$ret .= "<a href='" . e_BASE . "usersettings.php'>".LOGIN_MENU_L12."</a> .:. <a href='".e_BASE."?logout'>".LOGIN_MENU_L8."</a> .:.</span>";
		}
		else
		{
			$ret .= "<form method='post' action='".e_SELF."'>\n<p>\n".LOGIN_MENU_L1."<input class='tbox' type='text' name='username' size='15' value='$username' maxlength='20' />&nbsp;&nbsp;\n".LOGIN_MENU_L2."<input class='tbox' type='password' name='userpass' size='15' value='' maxlength='20' />&nbsp;&nbsp;\n<input type='checkbox' name='autologin' value='1' />".LOGIN_MENU_L6."&nbsp;&nbsp;\n<input class='button' type='submit' name='userlogin' value='Login' />";
			if($pref['user_reg'])
			{
				$ret .= "&nbsp;&nbsp;<a href='".e_SIGNUP."'>".LOGIN_MENU_L3."</a>";
			}
			$ret .= "</p>\n</form>";
		}
		return $ret;
		break;

	case "search":
		if(!USER && $pref['search_restrict'] == 1)
		{
			return "";
		}
		$searchflat = TRUE;
		include(e_PLUGIN."search_menu/search_menu.php");
		return "";
		break;

	case "quote":
		if(!file_exists(e_BASE."quote.txt"))
		{
			$quote = "Quote file not found ($qotd_file)";
		}
		else
		{
			$quotes = file(e_BASE."quote.txt");
			$quote = stripslashes(htmlspecialchars($quotes[rand(0, count($quotes))]));
		}
		return $quote;
		break;

	case "clock":
		$clock_flat = TRUE;
		require_once(e_PLUGIN."clock_menu/clock_menu.php");
		return "";
		break;

	case "welcomemessage":
		if(GUEST == TRUE && $sql -> db_Select("wmessage",wm_text,"wm_id = 1 AND wm_active = 1"))
		{
			$row = $sql -> db_Fetch();
			$ret .= $tp -> toHTML($row['wm_text']);
		}
				
		if(USER == TRUE && $sql -> db_Select("wmessage",wm_text,"wm_id = 2 AND wm_active = 1"))
		{
			$row = $sql -> db_Fetch();
			$ret .= $tp -> toHTML($row['wm_text']);
		}
		
		if(ADMIN == TRUE && $sql -> db_Select("wmessage",wm_text,"wm_id = 3 AND wm_active = 1"))
		{
			$row = $sql -> db_Fetch();
			$ret .= $tp -> toHTML($row['wm_text']);
		}
		define("WMFLAG", TRUE);
		return $ret;
		break;
}
