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
|     $Source: /cvs_backup/e107_0.7/e107_admin/auth.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-12 09:38:55 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
@include(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_admin.php");
@include(e_LANGUAGEDIR."English/admin/lan_admin.php");
if(ADMIN){
        require_once(e_ADMIN."header.php");
}else{
        if($_POST['authsubmit']){
                $obj = new auth;

                $row = $authresult = $obj -> authcheck($_POST['authname'], $_POST['authpass']);
                if($row[0] == "fop"){
                        echo "<script type='text/javascript'>document.location.href='admin.php?e'</script>\n";
                }else if($row[0] == "fon"){
                        echo "<script type='text/javascript'>document.location.href='admin.php?f'</script>\n";
                }else{

                        $userpass = md5($_POST['authpass']);
                        $cookieval = $row['user_id'].".".md5($userpass);

                        $sql -> db_Select("user", "*", "user_name='".$_POST['authname']."'");
                        list($user_id, $user_name, $userpass) = $sql-> db_Fetch();
                        if($pref['tracktype'] == "session"){
                                $_SESSION[$pref['cookie_name']] = $cookieval;
                        }else{
                                cookie($pref['cookie_name'], $cookieval, ( time()+3600*24*30));
                        }
                        echo "<script type='text/javascript'>document.location.href='admin.php'</script>\n";
                }
        }
        
        $e_sub_cat = 'logout';
        require_once(e_ADMIN."header.php");

        if(e_QUERY == "e"){
                $text = "<div style=\"text-align:center\">".ADLAN_86."</div>";
                $ns -> tablerender("Unable to login", $text);
        }
        if(e_QUERY == "f"){
                $text = "<div style=\"text-align:center\">".ADLAN_87."</div>";
                $ns -> tablerender(ADLAN_88, $text);
        }

        if(ADMIN == FALSE){
                $obj = new auth;
                $obj -> authform();
                require_once(e_ADMIN."footer.php");
                exit;
        }
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class auth{

        function authform(){
                /*
                # Admin auth login
                #
                # - parameters                none
                # - return                                null
                # - scope                                        public
                */

                $text =  "<div style='text-align:center'>
                <form method='post' action='".e_SELF."'>\n
<table style='width:50%' class='fborder'>
<tr>
<td style='width:35%' class='forumheader3'>".ADLAN_89."</td>
<td class='forumheader3' style='text-align:center'><input class='tbox' type='text' name='authname' size='30' value='$authname' maxlength='20' />\n</td>
</tr>
<tr>
<td style='width:35%' class='forumheader3'>".ADLAN_90."</td>
<td class='forumheader3' style='text-align:center'><input class='tbox' type='password' name='authpass' size='30' value='' maxlength='20' />\n</td>
</tr>
<tr>
<td colspan='2' style='text-align:center' class='forumheader'>

<input class='button' type='submit' name='authsubmit' value='".ADLAN_91."' />
</td>
</tr>
</table>
</form>
</div>";

$au = new e107table;
$au -> tablerender(ADLAN_92, $text);
        }

        function authcheck($authname, $authpass){
                /*
                # Admin auth check
                # - parameter #1:                string $authname, entered name
                # - parameter #2:                string $authpass, entered pass
                # - return                                boolean if fail, else result array
                # - scope                                        public
                */
                $sql_auth = new db;
                $authname = ereg_replace("\sOR\s|\=|\#", "", $authname);
                if($sql_auth -> db_Select("user", "*", "user_name='$authname' AND user_admin='1' ")){
                        if($sql_auth -> db_Select("user", "*", "user_name='$authname' AND user_password='".md5($authpass)."' AND user_admin='1' ")){
                                $row = $sql_auth -> db_Fetch();
                                return $row;
                        }else{
                                $row = array("fop");
                                return $row;
                        }
                }else{
                        $row = array("fon");
                        return $row;
                }
        }
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//


?>