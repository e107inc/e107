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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/pm_menu/pm_finduser.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:12:36 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
@include_once(e_PLUGIN."pm_menu/languages/".e_LANGUAGE.".php");

echo "
<link rel=stylesheet href='".THEME."style.css'>
<script language='JavaScript' type='text/javascript'>
<!--
function SelectUser()
{
   var d = window.document.results.usersel.value;
   parent.opener.document.pm.to.value = d;
        this.close();
}
//-->
</script>
";


function findusers($s){
        global $sql;
        if($sql -> db_Select("user","*","user_name LIKE '%{$s}%' ")){
                while($row = $sql -> db_Fetch()){
                        $ret[] = $row['user_name'];
                }
        } else {
                $ret[]="No matches found";
        }
        return $ret;
}
function close_window(){
        this.close;
}
$text = "
<form action=".e_SELF." method='POST'>
<table style='width:100%'>
<tr>
        <td class='forumheader3'><input type='text' name='srch' class='tbox' value='".$_POST['srch']."'>
        <input class='button' type='submit' name='dosrch' class='tbox' value='Search'></td>
</tr>
</table>
</form>
";

if($_POST['dosrch']){
        $userlist=findusers($_POST['srch']);
        $text .= "
<form name='results' action=".e_SELF." method='POST'>
<table style='width:100%'>
<tr><td class='fcaption'>".count($userlist)." ".PMLAN_55."</td></tr>
<tr>
        <td class='forumheader2'>
        <select name='usersel' width='60' ondblclick='SelectUser()'>
        ";
        foreach($userlist as $u){
                $text .= "<option value='{$u}'>{$u}";
        }
        $text .= "
        </select>
                <input type='button' class='button' value='".PMLAN_54."' onClick='SelectUser()'>
        </td>

</tr>
</table>
</form>
";
}

$text .= "<div style='text-align:center'><br /><a class='button' href='javascript:window.close()'>".PMLAN_56."</a></div>";
$ns -> tablerender(PMLAN_53,$text);

?>