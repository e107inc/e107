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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/pm_menu/pm_conf.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-18 16:11:57 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("../../class2.php");
require_once("pm_inc.php");
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."userclass_class.php");

@include_once(e_PM."languages/admin/".e_LANGUAGE.".php");
@include_once(e_PM."languages/admin/English.php");


function Create_yes_no_dropdown($Selname,$SelectedVal){
        $ret_text="<select class='tbox' name='".$Selname."'>\n";
        $sqlotd=new db;
        $sel="";
        if($SelectedVal == "1"){
                $sel=" SELECTED";
        }
        $ret_text.="<option value='1'".$sel.">".PM_ADLAN_33."\n";
        $sel="";
        if($SelectedVal == "0"){
                $sel=" SELECTED";
        }
        $ret_text.="<option value='0'".$sel.">".PM_ADLAN_34."\n";
        $ret_text.="</select>\n";
        return $ret_text;
}

if(!getperms("P")){ header("location:../index.php"); exit;}

function pm_UpdateParm($parmname){
        global $pref;
        $pref[$parmname]=$_POST[$parmname];
}

if(isset($_POST['updatesettings'])){

        if($_POST['pm_userclass']==e_UC_PUBLIC){$_POST['pm_userclass']=e_UC_MEMBER;}
        pm_UpdateParm("pm_title");
        pm_UpdateParm("pm_show_animated");
        pm_UpdateParm("pm_user_dropdown");
        pm_UpdateParm("pm_read_timeout");
        pm_UpdateParm("pm_unread_timeout");
        pm_UpdateParm("pm_delete_read");
        pm_UpdateParm("pm_popup");
        pm_UpdateParm("pm_popdelay");
        pm_UpdateParm("pm_userclass");
        pm_UpdateParm("pm_sendemail");
        save_prefs();
        header("location:pm_conf.php?u");
        exit;
}

function pm_del_messages($msg,$where=""){
   global $sql;
   global $ns;
   global $mySQLprefix;
   $sql->db_Delete("pm_messages",$where);
   if(!$where){
           $sql->db_Select_gen("ALTER TABLE ".$mySQLprefix."pm_messages AUTO_INCREMENT = 1");
   }
        $ns->tablerender("",$msg);
}

function pm_userdropdown(){
        $ret="<option SELECTED value=''>".PM_ADLAN_4."\n";
        $pm_sql=new db;
        $pm_sql -> db_Select("user", "user_name");
        while(list($uname) = $pm_sql-> db_Fetch()){
                $ret.="<option value='".$uname."'>".$uname."\n";
        }
        return $ret;
}


if($_POST['cancel']){
        header("location:".e_SELF);
        exit;
}

if($_POST['confirmdelall']){
        pm_del_messages(PM_ADLAN_5);
}

if($_POST['confirmdeluser']){
        $msg=PM_ADLAN_6." '".$_POST['to']."' ".PM_ADLAN_7.".";
        $where="pm_to_user='".$_POST['to']."' OR pm_from_user='".$_POST['to']."' ";
        pm_del_messages($msg,$where);
}

if($_POST['confirmdeldays']){
        $oneday=86400;
        $msg=PM_ADLAN_8.$_POST['numdays']." ".PM_ADLAN_9." ".PM_ADLAN_7.".";
        $dstamp=mktime(0,0,0)-($_POST['numdays']*$oneday);
        $where="pm_sent_datestamp < '".$dstamp."' ";
        pm_del_messages($msg,$where);
}

if($_POST['deldays']){
        if($_POST['numdays']){
                $text=PM_ADLAN_10.$_POST['numdays']." ".PM_ADLAN_9.".<br /><br />";
                $text.="<div style='text-align:center'>
        <form method='POST' action='".e_SELF."'>
        <input class='button' type='submit' name='confirmdeldays' value='".PM_ADLAN_11."'>
        <input class='button' type='submit' name='cancel' value='".PM_ADLAN_12."'>
        <input type='hidden' name='numdays' value='".$_POST['numdays']."'>
        </form></div>
        ";
        } else {
                $ns->tablerender("",PM_ADLAN_13);
                require_once(e_ADMIN."footer.php");
                exit;
        }
        $ns->tablerender(PM_ADLAN_14,$text);
        require_once(e_ADMIN."footer.php");
        exit;
}

if($_POST['deluser']){
        if($_POST['to']){
                $text=PM_ADLAN_15.$_POST['to']."<br /><br />";
                $text.="<div style='text-align:center'>
        <form method='POST' action='".e_SELF."'>
        <input class='button' type='submit' name='confirmdeluser' value='".PM_ADLAN_11."'>
        <input class='button' type='submit' name='cancel' value='".PM_ADLAN_12."'>
        <input type='hidden' name='to' value='".$_POST['to']."'>
        </form></div>
        ";
        } else {
                $ns->tablerender("",PM_ADLAN_16);
                require_once(e_ADMIN."footer.php");
                exit;
        }
        $ns->tablerender(PM_ADLAN_17,$text);
        require_once(e_ADMIN."footer.php");
        exit;
}

if($_POST['delall']){
        $text=PM_ADLAN_18."
        <br /><br /><div style='text-align:center'>
        <form method='POST' action='".e_SELF."'>
        <input class='button' type='submit' name='confirmdelall' value='".PM_ADLAN_11."'>
        <input class='button' type='submit' name='cancel' value='".PM_ADLAN_12."'>
        </form></div>
        ";
        $ns->tablerender(PM_ADLAN_19,$text);
        require_once(e_ADMIN."footer.php");
        exit;
}

if(e_QUERY == "u"){
        $ns -> tablerender("", "<div style='text-align:center'><b>".PM_ADLAN_20."</b></div>");
}

$pm_title = $pref['pm_title'];
$pm_show_animated = $pref['pm_show_animated'];
$pm_user_dropdown = $pref['pm_user_dropdown'];
$pm_read_timeout = $pref['pm_read_timeout'];
$pm_unread_timeout = $pref['pm_unread_timeout'];
$pm_delete_read = $pref['pm_delete_read'];
$pm_popup = $pref['pm_popup'];
$pm_popdelay = $pref['pm_popdelay'];
$pm_userclass = $pref['pm_userclass'];
$pm_sendemail = $pref['pm_sendemail'];

$text = "

<form method='post' action='".e_SELF."'>
<table style='width:95%'>
<tr>
<td>".PM_ADLAN_21.": </td>
<td><input  size='26' class='tbox' type='text' name='pm_title' value='".$pm_title."'>
</tr>";

$dropdown=Create_yes_no_dropdown("pm_show_animated",$pm_show_animated);
$text.= "<tr>
<td>".PM_ADLAN_22."? </td>
<td>".$dropdown."</td>
</tr>";

$dropdown=Create_yes_no_dropdown("pm_user_dropdown",$pm_user_dropdown);
$text.= "
<tr>
<td>".PM_ADLAN_23."? </td>
<td>".$dropdown."</td>
</tr>";

$dropdown=Create_yes_no_dropdown("pm_delete_read",$pm_delete_read);
$text.= "
<tr>
<td>".PM_ADLAN_24."? </td>
<td>".$dropdown."</td>
</tr>";

$text.="<tr>
<td>".PM_ADLAN_25.": </td>
<td><input  size='5' class='tbox' type='text' name='pm_read_timeout' value='".$pm_read_timeout."'> ".PM_ADLAN_39."  (0=".PM_ADLAN_40.")
</tr>";

$text.="<tr>
<td>".PM_ADLAN_26.": </td>
<td><input  size='5' class='tbox' type='text' name='pm_unread_timeout' value='".$pm_unread_timeout."'> ".PM_ADLAN_39." (0=".PM_ADLAN_40.")
</tr>";

$dropdown=Create_yes_no_dropdown("pm_popup",$pm_popup);
$text.="<tr>
<td>".PM_ADLAN_27."? </td>
<td>".$dropdown."</td>
</tr>";

$text.="<tr>
<td>".PM_ADLAN_28.": </td>
<td><input  size='5' class='tbox' type='text' name='pm_popdelay' value='".$pm_popdelay."'> ".PM_ADLAN_41."
</tr>";

$text.="<tr>
<td>".PM_ADLAN_29.": </td>
<td>".r_userclass("pm_userclass",$pm_userclass)."</td>
</tr>";

$dropdown=Create_yes_no_dropdown("pm_sendemail",$pm_sendemail);
$text.="<tr>
<td>".PM_ADLAN_35."? </td>
<td>".$dropdown."</td>
</tr>";


$text.="<tr>
<tr style='vertical-align:top'>
<td colspan='2'  style='text-align:center'><br />
<input class='button' type='submit' name='updatesettings' value='".PM_ADLAN_3."' />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("<div style='text-align:center'>".PM_ADLAN_1."</div>", $text);

$msg_count=$sql -> db_Select("pm_messages");

$text="
<form method='POST' name ='pm' action='".e_SELF."'>
<input class='button' type='submit' name='delall' value='".PM_ADLAN_19."'> (".$msg_count." ".PM_ADLAN_37.")
<br /><br />
<input class='button' type='submit' name='deldays' value='".PM_ADLAN_32." ...'>
&nbsp;&nbsp;<input class='tbox' type='text' name='numdays' size='2'>&nbsp;&nbsp;".PM_ADLAN_9."
<br /><br />

<input class='button' type='submit' name='deluser' value='".PM_ADLAN_14."'>
<input class='tbox' type='text' name='to' maxlength='30'>
<a class='button' href='".e_PLUGIN."pm_menu/pm_finduser.php' onclick=\"window.open('".e_PLUGIN."pm_menu/pm_finduser.php','pmsearch', 'toolbar=no,location=no,status=yes,scrollbars=yes,resizable=yes,width=350,height=350,left=100,top=100'); return false;\">".PM_ADLAN_38."... </a>


</form>
";

$ns -> tablerender("<div style='text-align:center'>".PM_ADLAN_2."</div>", $text);
require_once(e_ADMIN."footer.php");
?>