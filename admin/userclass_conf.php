<?php
/*
+---------------------------------------------------------------+
+---------------------------------------------------------------+
*/
require_once("../class2.php");

if(!getperms("P")){ header("location:../index.php"); }

if(IsSet($_POST['updateclasses'])){
	foreach (array_keys($_POST) as $p){
		list($_name,$_id)=split("_",$p);
		if($_name == 'classname'){
			$sql -> db_Update("userclass_classes", "userclass_name='".strtoupper($_POST[$p])."' WHERE userclass_id='".$_id."' ");
		}
		if($_name == 'classdesc'){
			$sql -> db_Update("userclass_classes", "userclass_description='".$_POST[$p]."' WHERE userclass_id='".$_id."' ");
		}
	}
	header("location:userclass_conf.php?u");
}

if(preg_match("/^del\./",$_SERVER['QUERY_STRING'])){
	list($tmp,$_id)=explode(".",$_SERVER['QUERY_STRING']);
	$sql -> db_Delete("userclass_classes", "userclass_id='".$_id."' ");
	$myuserlist=uc_UserList("list");
	foreach(array_keys($myuserlist) as $key){
		uc_Deluserclass($key,$_id);
	}
	header("location:userclass_conf.php?d");
}

if(IsSet($_POST['addclass'])){
	$vars="0,'".USERNAME."','".$real_userid."','".time()."',0,'".$pm_subject."','".$pm_text."'";
	$sql -> db_Insert("userclass_classes", "'0','".strtoupper($_POST['newclassname'])."','".$_POST['newclassdesc']."' ");
	header("location:userclass_conf.php?a");
}

require_once("auth.php");

if($_SERVER['QUERY_STRING'] == "u"){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>Userclass settings updated.</b></div>");
}

if($_SERVER['QUERY_STRING'] == "a"){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>Userclass added.</b></div>");
}

if($_SERVER['QUERY_STRING'] == "d"){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>Userclass deleted.</b></div>");
}

require_once("header.php");

if(IsSet($_POST['updateuser'])){
	$newclasslist.=".";
	foreach(array_keys($_POST) as $key){
		if(strstr($key,"uclass_")){
			if($_POST[$key]=="on"){
				list($tmp,$_class)=explode("_",$key);
				$newclasslist.=$_class.".";
			}
		}
	}
	list($uid,$uname)=explode(".",$_POST['uname']);
	$sql->db_Select("userclass_users","userclass_user","userclass_user='".$uid."' ");
	if($sql->db_Fetch()){
		$sql -> db_Update("userclass_users", "userclass_class='".$newclasslist."' WHERE userclass_user='".$uid."' ");
	} else {
		$sql->db_Insert("userclass_users","'".$uid."','".$newclasslist."' ");
	}
	$ns->tablerender("","Setting for ".$uname." updated.");
	$_POST['au']="yes";
	$_POST['uname']=$uid.".".$uname;
}

if(IsSet($_POST['updateclass'])){
	list($class,$classname)=explode(".",$_POST['uclass']);
	$myuserlist=uc_UserList("list");
	foreach(array_keys($myuserlist) as $curuid){
		$newclasslist="";
		$pname="uname_".$curuid;
		$sql->db_Select("userclass_users","userclass_class","userclass_user='".$curuid."' ");
		if(list($_uclasslist)=$sql->db_Fetch()){
			if($_POST[$pname]){
				if(!strstr($_uclasslist,".".$class.".")){
					if(strlen($_uclasslist)==0){
						$newclasslist=".";
					}
					$newclasslist.=$_uclasslist.$class.".";
					$text.="Added class $classname to user [".$myuserlist[$curuid]."] <br />";
				}
			} else {
				if(strstr($_uclasslist,".".$class.".")){
					uc_Deluserclass($curuid,$class);
//					$newclasslist=str_replace(".".$class.".",".",$_uclasslist);
					$text.="Removed class $classname from user [".$myuserlist[$curuid]."] <br />";
				}
			}
			if($newclasslist){
				$sql -> db_Update("userclass_users", "userclass_class='".$newclasslist."' WHERE userclass_user='".$curuid."' ");
			}
		} else {
			if($_POST[$pname]){
				$sql -> db_Insert("userclass_users","'".$curuid."','.".$class.".'");
				$text.="Inserted class $classname to user [".$myuserlist[$curuid]."] <br />";
			}
		}
	}
	$ns->tablerender("",$text);
	$text="";
	$_POST['ac']="yes";
}

if(IsSet($_POST['au'])){
	list($_id,$_user)=explode(".",$_POST['uname']);
	$classlist=uc_UserclassList("list");

	$sql->db_Select("userclass_users","userclass_class","userclass_user='".$_id."' ");
	list($curclass)=$sql->db_Fetch();
	$text.="
	<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
	<input type=\"hidden\" name=\"uname\" value=\"".$_POST['uname']."\">
	<table style=\"fborder\" align=\"center\" cellspacing=\"5\" cellpadding=\"5\">
	<tr><td class=\"fcaption\" >Class name</td><td class=\"fcaption\">Class description</td></tr>";
	
	foreach(array_keys($classlist) as $c){
		list($_cname,$_cdesc)=explode(":",$classlist[$c]);
		if(strstr($curclass,".".$c.".")){
			$val=" CHECKED";
		} else {
			$val="";
		}
		$text.="<tr><td><input name=\"uclass_".$c."\" type=\"checkbox\" class=\"tbox\" ".$val."> $_cname</td><td>$_cdesc </td></tr>\n";
	}
	$text.="
	<tr><td colspan=\"2\"><input type=\"submit\" class=\"button\" name=\"updateuser\" value=\"Update user classes\">  <input type=\"submit\" class=\"button\" name=\"x\" value=\"Return to main \"></td></tr>
	</table></form>
	";
	$ns->tablerender("assign groups - $_user",$text);
	require_once("footer.php");
	exit;
}


if(IsSet($_POST['ac'])){
	list($_cid,$_cname)=explode(".",$_POST['uclass']);
	$userlist=uc_UserList("list");

	$text.="
	<form action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">
	<input type=\"hidden\" name=\"uclass\" value=\"".$_POST['uclass']."\">
	<table class=\"fborder\" align=\"center\" cellspacing=\"5\" cellpadding=\"5\">
	<tr><td class=\"fcaption\">User</td></tr>";

	foreach(array_keys($userlist) as $uid){
		$sql->db_Select("userclass_users","userclass_class","userclass_user='".$uid."' ");
		list($curclass)=$sql->db_Fetch();
		if(strstr($curclass,".".$_cid.".")){
			$val=" CHECKED";
		} else {
			$val="";
		}
		$text.="<tr><td><input name=\"uname_".$uid."\" type=\"checkbox\" class=\"tbox\" ".$val."> $userlist[$uid]</td></tr>\n";
	}
	$text.="
	<tr><td><input type=\"submit\" class=\"button\" name=\"updateclass\" value=\"Update class assignments\">  <input type=\"submit\" class=\"button\" name=\"x\" value=\"Return to main \"></td></tr>
	</table></form>
	";
	$ns->tablerender("assign class - $_cname",$text);
	require_once("footer.php");
	exit;
}



$text.="<form name=\"edituserclass\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";
$text.="<table class=\"fborder\" align=\"center\">";

if($sql -> db_Select("userclass_classes", "*", "", $mode="no_where")){

	$text.="
	<tr><td class=\"fcaption\" >Class Name</td><td class=\"fcaption\" >Class Description</td></tr>\n";
	while(list($_userclass_id,$_userclass_name, $_userclass_description) = $sql-> db_Fetch()){
		$nameid="classname_".$_userclass_id;
		$descid="classdesc_".$_userclass_id;
		$text.="<tr>
		<td><a href=\"".$_SERVER['PHP_SELF']."?del.".$_userclass_id."\">[Delete]</a>  <input class=\"tbox\" type=\"text\" size=\"15\" maxlength=\"25\" name=\"".$nameid."\" value=\"".$_userclass_name."\"></td>\n
		<td style=\"align:center\"><input class=\"tbox\" type=\"text\" size=\"35\" maxlength=\"85\" name=\"".$descid."\" value=\"".$_userclass_description."\"></td>\n
		</tr>\n";
	}
	$text.="<tr><td colspan=\"2\" style=\"text-align:center\"><input class=\"button\" type=\"submit\" name=\"updateclasses\" value=\"update current classes\"><br /><br /></td></tr>\n";
}

$text.="</form>\n";
$text.="
<form name=\"adduserclass\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n";
$text.="
<tr><td class=\"fcaption\" >Class Name</td><td class=\"fcaption\" >Class Description</td></tr>\n";
$text.="<tr>
<td><input class=\"tbox\" type=\"text\" size=\"15\" maxlength=\"25\" name=\"newclassname\"></td>\n
<td><input class=\"tbox\" type=\"text\" size=\"35\" maxlength=\"85\" name=\"newclassdesc\"></td>\n
</tr>";
$text.="<tr><td colspan=\"2\" style=\"text-align:center\"><input class=\"button\" type=\"submit\" name=\"addclass\" value=\"Add New class\">\n";
$text.="</td></tr></form></table>\n";

$text.="<br /><br /><table class=\"fborder\" align=\"center\">\n
<form name=\"editgroups\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">\n
<tr>
<td colspan=\"2\" class=\"fcaption\">Update user / class assignments</td>
</tr>
<tr>
<td><select name=\"uname\" class=\"tbox\">".uc_UserList()."</select></td>\n
<td><select name=\"uclass\" class=\"tbox\">".uc_UserclassList()."</select></td>\n
</tr>
<tr>
<td><input type=\"submit\" class=\"button\" name=\"au\" value=\"Assign class(s) to user\"> &nbsp; </td>\n
<td> &nbsp; <input type=\"submit\" class=\"button\" name=\"ac\" value=\"Assign user(s) to class\"></td>\n
</tr>
</form>
</table>";


$ns -> tablerender("<div style=\"text-align:center\">User Class Settings</div>", $text);
require_once("footer.php");

function uc_UserList($mode="dropdown"){
	$pm_sql=new db;
	$pm_sql -> db_Select("user", "*", "ORDER BY user_name ASC", "no_where");
	$userlist="";
	while(list($uid,$uname) = $pm_sql-> db_Fetch()){
		if($mode=="dropdown"){
			$ret.="<option value=\"".$uid.".".$uname."\">".$uname."\n";
		} else {
			$userlist[$uid]=$uname;
		}
	}
	if($mode=="dropdown"){
		return $ret;
	}
	return $userlist;
}

function uc_UserclassList($mode="dropdown"){
	$pm_sql=new db;
	$pm_sql -> db_Select("userclass_classes", "userclass_id,userclass_name,userclass_description");
	$classlist="";
	while(list($ucid,$ucname,$ucdesc) = $pm_sql-> db_Fetch()){
		if($mode=="dropdown"){
			$ret.="<option value=\"".$ucid.".".$ucname."\">".$ucname."\n";
		} else {
			$classlist[$ucid]=$ucname.":".$ucdesc;
		}
	}
	if($mode=="dropdown"){
		return $ret;
	}
	return $classlist;
}

function uc_Deluserclass($who,$class){
	$my_sql=new db;
	$my_sql->db_Select("userclass_users","userclass_class","userclass_user='".$who."' ");
	if(list($_uclasslist)=$my_sql->db_Fetch()){
		if(strstr($_uclasslist,".".$class.".")){
			$newclasslist=str_replace(".".$class.".",".",$_uclasslist);
			$my_sql -> db_Update("userclass_users", "userclass_class='".$newclasslist."' WHERE userclass_user='".$who."' ");
		}
	}
}

?>