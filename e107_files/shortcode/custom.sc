$ret = "";
$custom_query = explode('+', $parm);
switch($custom_query[0])
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
                        $ret .= "<form method='post' action='".e_SELF."'>\n<p>\n".LOGIN_MENU_L1."<input class='tbox loginc user' type='text' name='username' size='15' value='$username' maxlength='20' />&nbsp;&nbsp;\n".LOGIN_MENU_L2."<input class='tbox loginc pass' type='password' name='userpass' size='15' value='' maxlength='20' />&nbsp;&nbsp;\n<input type='checkbox' name='autologin' value='1' />".LOGIN_MENU_L6."&nbsp;&nbsp;\n<input class='button loginc' type='submit' name='userlogin' value='Login' />";
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

        case "language":

                require_once(e_HANDLER."file_class.php");
                $fl = new e_file;
                $reject = array('.','..','/','CVS','thumbs.db','*._$');
                $lanlist = $fl->get_files(e_LANGUAGEDIR,"",$reject);
                sort($lanlist);
                $lantext = "<form method='post' action='".e_SELF."'>
                <div><select name='sitelanguage' class='tbox' onchange='this.form.submit()'>";

                foreach($lanlist as $langval) {
                        $langname = $langval;
                        $langval = ($langval['dir'] == $pref['sitelanguage']) ? "" : $langval['dir'];
                        $selected = ($langval == USERLAN) ? "selected='selected'" : "";
                        $lantext .= "<option value='".$langval."' $selected>".$langname['dir']."</option>\n ";
                }

                $lantext .= "</select>";
                $lantext .= "<input type='hidden' name='setlanguage' value='1' />";
                $lantext .= "</div></form>";
                return $lantext;
                break;



        case "clock":
                $clock_flat = TRUE;
                include_once(e_PLUGIN."clock_menu/clock_menu.php");
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