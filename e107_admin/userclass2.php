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
|     $Source: /cvs_backup/e107_0.7/e107_admin/userclass2.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-05 16:57:37 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("4")){ header("location:".e_BASE."index.php"); exit;}
require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
$aj = new textparse;
$sql2=new db;

function check_allowed($class_id){
        global $sql;
        if(!$sql -> db_Select("userclass_classes","*","userclass_id = {$class_id}")){
                header("location:".SITEURL);
                exit;
        }
        $row = $sql -> db_Fetch();
        extract($row);
        if(!getperms("0") && !check_class($userclass_editclass)){
                header("location:".SITEURL);
                exit;
        }
}

if(strstr(e_QUERY, "clear")){
        $tmp = explode(".", e_QUERY);
        $class_id = $tmp[1];
        check_allowed($class_id);
        if($sql -> db_Select("user", "*", "user_class REGEXP('^{$class_id}\.') OR user_class REGEXP('\.{$class_id}\.') OR user_class REGEXP('\.{$class_id}$')")){
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $cl=explode(".",$user_class);
                        $i=array_search($class_id,$cl);
                        if(is_numeric($i)){
                                unset($cl[$i]);
                                $user_class=implode(".",$cl);
                                $sql2 -> db_Update("user", "user_class='$user_class' WHERE user_id='$user_id' ");
                        }
                }
                $message = UCSLAN_1;
        }
}else if(e_QUERY){
        $tmp2 = explode("-", e_QUERY);
        $class_id = $tmp2[0];
        check_allowed($class_id);
        $tmp = explode(".", $tmp2[1]);
        $tmp=array_flip($tmp);
        $message = UCSLAN_2;

        $sql -> db_Select("user", "*");
        while($row = $sql -> db_Fetch()){
                extract($row);
                if(array_key_exists($user_id,$tmp)){  //Add user to the class
                        $cl=explode(".",$user_class);
                        $flip=array_flip($cl);
                        if(!array_key_exists($class_id,$flip)){
                                array_push($cl,$class_id);
                                $user_class=implode(".",$cl);
                                $sql2 -> db_Update("user", "user_class='$user_class' WHERE user_id='$user_id' ");
                        }
                } else {  //Remove user from class
                        $cl=explode(".",$user_class);
                        $i=array_search($class_id,$cl);
                        if(is_numeric($i)){  //Belongs to array?
                                unset($cl[$i]);
                                $user_class=implode(".",$cl);
                                $sql2 -> db_Update("user", "user_class='$user_class' WHERE user_id='$user_id' ");
                        }
                }
        }
}

If(IsSet($_POST['delete'])){
        $sql2=new db;
        $class_id = $_POST['existing'];
   check_allowed($class_id);
        if($_POST['confirm']){
                $sql -> db_Delete("userclass_classes", "userclass_id='".$_POST['existing']."' ");
                $sql -> db_Select("user", "user_id, user_class", "user_class REGEXP('^{$class_id}\.') OR user_class REGEXP('\.{$class_id}\.') OR user_class REGEXP('\.{$class_id}$')");
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $classes=explode(".",$user_class);
                        $newclass="";
                        foreach($classes as $c){
                                if($c !==$_POST['existing'] && $c){
                                        $newclass.=".".$c;
                                }
                        }
                        $sql2 -> db_Update("user","user_class='$newclass' WHERE user_id='$user_id' ");
                }
                $message = UCSLAN_3;
        } else {
                $message = UCSLAN_4;
        }
}

If(IsSet($_POST['edit'])){
        check_allowed($_POST['existing']);
        $sql -> db_Select("userclass_classes", "*", "userclass_id='".$_POST['existing']."' ");
        $row = $sql -> db_Fetch(); extract($row);
}

if(IsSet($_POST['updateclass'])){
        check_allowed($_POST['userclass_id']);
        $_POST['userclass_name'] = $aj -> formtpa($_POST['userclass_name'], "admin");
        $_POST['userclass_description'] = $aj -> formtpa($_POST['userclass_description'], "admin");
        $sql -> db_Update("userclass_classes", "userclass_editclass={$_POST['userclass_editclass']}, userclass_name='".$_POST['userclass_name']."', userclass_description='".$_POST['userclass_description']."' WHERE userclass_id='".$_POST['userclass_id']."' ");
        $message = UCSLAN_5;
}

if(IsSet($_POST['createclass'])){
        $_POST['userclass_name'] = $aj -> formtpa($_POST['userclass_name'], "admin");
        $_POST['userclass_description'] = $aj -> formtpa($_POST['userclass_description'], "admin");

        if(getperms("0") || check_class($_POST['userclass_editclass']) && $_POST['userclass_editclass']){
                $editclass = $_POST['userclass_editclass'];
                $i=1;
                while($sql -> db_Select("userclass_classes", "*", "userclass_id='".$i."' ") && $i<255){
                        $i++;
                }
                if($i<255){
                        $sql -> db_Insert("userclass_classes", $i.", '".strip_tags(strtoupper($_POST['userclass_name']))."', '".$_POST['userclass_description']."',{$editclass} ");
                }
                $message = UCSLAN_6;
        } else {
                header("location:".SITEURL);
                exit;
        }
}

if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$class_total = $sql -> db_Select("userclass_classes");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' id='classForm'>
<table class='fborder' style='width:95%'>
<tr>
<td class='forumheader' style='text-align:center' colspan='2'>";

if($class_total == "0"){
        $text .= UCSLAN_7;
} else {
        $text .= "<span class='defaulttext'>".UCSLAN_8.":</span>
        <select name='existing' class='tbox'>";
        while($row = $sql-> db_Fetch()){
                if(check_class($row['userclass_editclass']) || getperms("0")){
                        $text .= "<option value='{$row['userclass_id']}'>{$row['userclass_name']}</option>";
                }
        }
        $text .= "</select>
        <input class='button' type='submit' name='edit' value='".UCSLAN_9."' />
        <input class='button' type='submit' name='delete' value='".UCSLAN_10."' />
        <input type='checkbox' name='confirm' value='1' /><span class='smalltext'> ".UCSLAN_11."</span>
        </td>
        </tr>";
}

$text.="
<tr>
<td class='forumheader3' style='width:30%'>".UCSLAN_12."</td>
<td class='forumheader3' style='width:70%'>
<input class='tbox' type='text' size='30' maxlength='25' name='userclass_name' value='$userclass_name' /></td>
</tr>
<tr>
<td class='forumheader3'>".UCSLAN_13."</td>
<td class='forumheader3' style='width:70%'><input class='tbox' type='text' size='60' maxlength='85' name='userclass_description' value='$userclass_description' /></td>
</tr>
";

$text .= "
<tr>
<td class='forumheader3'>".UCSLAN_24."</td>
<td class='forumheader3'>".r_userclass("userclass_editclass",$userclass_editclass,"off","classes,matchclass,public")."</td>
</tr>
";

$text .="
<tr><td colspan='2' style='text-align:center' class='forumheader'>";

If(IsSet($_POST['edit'])){
        $text .= "<input class='button' type='submit' name='updateclass' value='".UCSLAN_14."' />
        <input type='hidden' name='userclass_id' value='$userclass_id' />";
}else{
        $text .= "<input class='button' type='submit' name='createclass' value='".UCSLAN_15."' />";
}

$text .= "</td></tr></table>";

If(IsSet($_POST['edit'])){

        $sql -> db_Select("user", "user_id, user_name, user_class, user_login", "ORDER BY user_name", "no-where");
        $c=0; $d=0;
        while($row = $sql -> db_Fetch()){
                extract($row);
                if(check_class($userclass_id, $user_class)){
                        $in_userid[$c] = $user_id;
                        $in_username[$c] = $user_name;
                        $in_userlogin[$c] = $user_login ? "(".$user_login.")" : "";
                        $c++;
                }else{
                $out_userid[$d] = $user_id;
                $out_username[$d] = $user_name;
                $out_userlogin[$d] = $user_login ? "(".$user_login.")" : "";
                $d++;
                }

        }

        $text .= "<br /><table class='fborder' style='width:95%'>
        <tr>
        <td class='fcaption' style='text-align:center;width:30%'>".UCSLAN_16."</td></tr>
        <tr>
        <td class='forumheader3' style='width:70%; text-align:center'>

        <table style='width:90%'>
        <tr>
        <td style='width:45%; vertical-align:top'>
        ".UCSLAN_22."<br />
        <select class='tbox' id='assignclass1' name='assignclass1' size='10' style='width:220px' multiple='multiple' onchange='moveOver();'>";

        for($a=0; $a<=($d-1); $a++){
                $text .= "<option value=".$out_userid[$a].">".$out_username[$a]." ".$out_userlogin[$a]."</option>";
        }

        $text .= "</select>
        </td>
        <td style='width:45%; vertical-align:top'>
        ".UCSLAN_23."<br />
        <select class='tbox' id='assignclass2' name='assignclass2' size='10' style='width:220px' multiple='multiple'>";
        for($a=0; $a<=($c-1); $a++){
                $text .= "<option value=".$in_userid[$a].">".$in_username[$a]." ".$in_userlogin[$a]."</option>";
        }
        $text .= "</select><br /><br />
        <input class='button' type='button' value='".UCSLAN_17."' onclick='removeMe();' />
        <input class='button' type='button' value='".UCSLAN_18."' onclick='clearMe($userclass_id);' />
        <input type='hidden' name='class_id' value='$userclass_id'>

        </td></tr></table>
        </td></tr>
        <tr><td colspan='2' style='text-align:center' class='forumheader'>
        <input class='button' type='button' value='".UCSLAN_19." ".$userclass_name." ".UCSLAN_20."' onclick='saveMe($userclass_id);' />
        </tr>
        </td>
        </table>";

}


$text .= "</form>
</div>";

$ns -> tablerender(UCSLAN_21, $text);

require_once("footer.php");
function headerjs(){

$script_js= "<script type=\"text/javascript\">
//<![CDATA[
// Adapted from original:  Kathi O'Shea (Kathi.O'Shea@internet.com)
function moveOver()
{
var boxLength = document.getElementById('assignclass2').length;
var selectedItem = document.getElementById('assignclass1').selectedIndex;
var selectedText = document.getElementById('assignclass1').options[selectedItem].text;
var selectedValue = document.getElementById('assignclass1').options[selectedItem].value;
var i;
var isNew = true;
if (boxLength != 0) {
for (i = 0; i < boxLength; i++) {
thisitem = document.getElementById('assignclass2').options[i].text;
if (thisitem == selectedText) {
isNew = false;
break;
      }
   }
}
if (isNew) {
newoption = new Option(selectedText, selectedValue, false, false);
document.getElementById('assignclass2').options[boxLength] = newoption;
document.getElementById('assignclass1').options[selectedItem].text = '';
}
document.getElementById('assignclass1').selectedIndex=-1;
}


function removeMe() {
var boxLength = document.getElementById('assignclass2').length;
var boxLength2 = document.getElementById('assignclass1').length;
arrSelected = new Array();
var count = 0;
for (i = 0; i < boxLength; i++) {
if (document.getElementById('assignclass2').options[i].selected) {
arrSelected[count] = document.getElementById('assignclass2').options[i].value;
var valname = document.getElementById('assignclass2').options[i].text;
   for (j = 0; j < boxLength2; j++) {
        if(document.getElementById('assignclass1').options[j].value == arrSelected[count]){
        document.getElementById('assignclass1').options[j].text = valname;
        }
   }

// document.getElementById('assignclass1').options[i].text = valname;
}
count++;
}
var x;
for (i = 0; i < boxLength; i++) {
for (x = 0; x < arrSelected.length; x++) {
if (document.getElementById('assignclass2').options[i].value == arrSelected[x]) {
document.getElementById('assignclass2').options[i] = null;
   }
}
boxLength = document.getElementById('assignclass2').length;
   }
}

function clearMe(clid){
        location.href = document.location + \"?clear.\" + clid;
}

function saveMe(clid) {
var strValues = \"\";
var boxLength = document.getElementById('assignclass2').length;
var count = 0;
if (boxLength != 0) {
for (i = 0; i < boxLength; i++) {
if (count == 0) {
strValues = document.getElementById('assignclass2').options[i].value;
}
else {
strValues = strValues + \".\" + document.getElementById('assignclass2').options[i].value;
}
count++;
   }
}
if (strValues.length == 0) {
//alert(\"You have not made any selections\");
}
else {
location.href = document.location + \"?\" + clid + \"-\" + strValues;
   }
}
//]]>
</script>\n";
 return $script_js;
}



?>