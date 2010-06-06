<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_admin/auth.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

/* done in class2
@include_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_admin.php");
@include_once(e_LANGUAGEDIR."English/admin/lan_admin.php");
*/
if (ADMIN)
{
    define("ADMIN_PAGE", TRUE);
    require_once(e_ADMIN."header.php");
}
else
{
    $use_imagecode = ($pref['logcode'] && extension_loaded("gd"));
    if ($use_imagecode)
    {
        require_once(e_HANDLER."secure_img_handler.php");
        $sec_img = new secure_image;
    }

    if ($_POST['authsubmit'])
    {
        $obj = new auth;

        if($use_imagecode)
        {
            if (!$sec_img->verify_code($_POST['rand_num'], $_POST['code_verify']))
            {
                echo "<script type='text/javascript'>document.location.href='../index.php'</script>\n";
                header("location: ../index.php");
                exit;
            }
        }

        $row = $authresult = $obj->authcheck($_POST['authname'], $_POST['authpass']);
        if ($row[0] == "authfail") {
            echo "<script type='text/javascript'>document.location.href='../index.php'</script>\n";
            header("location: ../index.php");
            exit;
        } else {

            $userpass = md5($_POST['authpass']);
            $cookieval = $row['user_id'].".".md5($userpass);

            $sql->db_Select("user", "*", "user_name='".$tp -> toDB($_POST['authname'])."'");
            list($user_id, $user_name, $userpass) = $sql->db_Fetch();
            if ($pref['user_tracking'] == "session") {
                $_SESSION[$pref['cookie_name']] = $cookieval;
            } else {
                cookie($pref['cookie_name'], $cookieval, (time()+3600 * 24 * 30));
            }
			
			$edata_li = array("user_id" => $user_id, "user_name" => $user_name, "user_admin" => 1);
			$e_event->trigger("login", $edata_li);
			
            echo "<script type='text/javascript'>document.location.href='admin.php'</script>\n";
        }
    }

    $e_sub_cat = 'logout';
    require_once(e_ADMIN."header.php");

    if (ADMIN == FALSE) {
        $obj = new auth;
        $obj->authform();
        require_once(e_ADMIN."footer.php");
        exit;
    }
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class auth
{

    function authform()
    {
        /*
        # Admin auth login
        #
        # - parameters  none
        # - return      null
        # - scope       public
        */

        global $use_imagecode, $sec_img;

        $text = "<div style='padding:20px;text-align:center'>
            <form method='post' action='".e_SELF."'>\n
            <table style='width:50%' class='fborder'>
            <tr>
            <td rowspan='4' style='vertical-align:middle;width:65px'>".(file_exists(THEME."images/password.png") ? "<img src='".THEME_ABS."images/password.png' alt='' />\n" : "<img src='".e_IMAGE."generic/".IMODE."/password.png' alt='' />\n" )."</td>
            <td style='width:35%' class='forumheader3'>".ADLAN_89."</td>
            <td class='forumheader3' style='text-align:center'><input class='tbox' type='text' name='authname' size='30' value='$authname' maxlength='20' />\n</td>

            </tr>
            <tr>
            <td style='width:35%' class='forumheader3'>".ADLAN_90."</td>
            <td class='forumheader3' style='text-align:center'><input class='tbox' type='password' name='authpass' size='30' value='' maxlength='20' />\n</td>
            </tr>
            ";

        if ($use_imagecode) {
            $text .= "
            <tr>
            <td style='width:35%' class='forumheader3'>".ADLAN_152."</td>
            <td style='text-align:center'>
            <input type='hidden' name='rand_num' value='".$sec_img->random_number."' />".
            $sec_img->r_image().
            "<br /><input class='tbox' type='text' name='code_verify' size='15' maxlength='20' /></td>
            </tr>
            ";
        }

        $text .= "
            <tr>
            <td colspan='2' style='text-align:center' class='forumheader'>

            <input class='button' type='submit' name='authsubmit' value='".ADLAN_91."' />
            </td>
            </tr>
            </table>
            </form>
            </div>";

        $au = new e107table;
        $au->tablerender(ADLAN_92, $text);
    }

    function authcheck($authname, $authpass)
    {
        /*
        # Admin auth check
        # - parameter #1:                string $authname, entered name
        # - parameter #2:                string $authpass, entered pass
        # - return                                boolean if fail, else result array
        # - scope                                        public
        */
        global $tp;
        $sql_auth = new db;
        $authname = $tp -> toDB(preg_replace("/\sOR\s|\=|\#/", "", $authname));
        if ($sql_auth->db_Select("user", "*", "user_loginname='$authname' AND user_admin='1' "))
        {
            $row = $sql_auth->db_Fetch();
        }
        else
        {
            if ($sql_auth->db_Select("user", "*", "user_name='$authname' AND user_admin='1' "))
            {
                $row = $sql_auth->db_Fetch();
            }
        }
        if($row['user_id'])
        {
            if($row['user_password'] == md5($authpass))
            {
                return $row;
            }
        }
        return array("authfail");
    }
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
?>
