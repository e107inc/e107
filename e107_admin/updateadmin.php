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
|     $Source: /cvs_backup/e107_0.7/e107_admin/updateadmin.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-05 16:57:37 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
require_once("auth.php");

if(IsSet($_POST['update_settings'])){
        if($_POST['ac'] == md5(ADMINPWCHANGE)){
                if($_POST['a_password'] != "" && $_POST['a_password2'] != "" && ($_POST['a_password'] == $_POST['a_password2'])){
                        $sql -> db_Update("user", "user_password='".md5($_POST['a_password'])."', user_pwchange='".time()."' WHERE user_name='".ADMINNAME."' ");
                        $se = TRUE;
			$e_event -> trigger("adpword");
                }else{
                        $message = UDALAN_1;
                }
        }
}

if($se == TRUE){
        $text = "<div style='text-align:center'>".UDALAN_2.".</div>";
        $ns -> tablerender("<div style='text-align:center'>".UDALAN_3." ".($a_name ? $a_name : ADMINNAME)."</div>", $text);
        require_once("footer.php");
        exit;
}

if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='".ADMIN_WIDTH."' class='fborder'>
<tr>
<td style='width:30%' class='forumheader3'>".UDALAN_4.": </td>
<td style='width:70%' class='forumheader3'>
".ADMINNAME."
</td>
</tr>
<tr>
<td style='width:30%' class='forumheader3'>".UDALAN_5.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='password' name='a_password' size='60' value='' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".UDALAN_6.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='password' name='a_password2' size='60' value='' maxlength='100' />
</td>
</tr>

<tr>
<td colspan='2' style ='text-align:center'  class='forumheader'>
<input class='button' type='submit' name='update_settings' value='".UDALAN_7."' />
<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
</td>
</tr>
</table>

</form>
</div>";

$ns -> tablerender(UDALAN_8." ".ADMINNAME, $text);

require_once("footer.php");
?>
