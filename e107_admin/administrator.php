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
|     $Source: /cvs_backup/e107_0.7/e107_admin/administrator.php,v $
|     $Revision: 1.22 $
|     $Date: 2005-09-09 19:57:24 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("3"))
{
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'admin';
require_once("auth.php");

if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	unset($tmp);
}




if (isset($_POST['update_admin']))
{
	$sql->db_Select("user", "*", "user_id='".$_POST['a_id']."' ");
	$row = $sql->db_Fetch();
	$a_name = $row['user_name'];
	if ($_POST['a_password'] == "")
	{
		$admin_password = $row['user_password'];
	}
	else
	{
		$admin_password = md5($_POST['a_password']);
	}

	for ($i = 0; $i <= count($_POST['perms']); $i++)
	{
		if (strlen($_POST['perms'][$i]))
		{
			$perm .= $_POST['perms'][$i].".";
		}
	}
	$message = ($sql->db_Update("user", "user_password='$admin_password', user_perms='$perm' WHERE user_name='$a_name' ")) ? ADMSLAN_56." ".$_POST['ad_name']." ".ADMSLAN_2."<br />" : LAN_UPDATED_FAILED;
	unset($ad_name, $a_password, $a_perms);

}

if ($_POST['edit_admin'] || $action == "edit")
{
	$edid = array_keys($_POST['edit_admin']);
    $theid = ($edid[0]) ? $edid[0] : $sub_action;
	$sql->db_Select("user", "*", "user_id=".$theid);
	$row = $sql->db_Fetch();

	if ($a_perms == "0")
	{
		$text = "<div style='text-align:center'>$ad_name ".ADMSLAN_3."
		<br /><br />
		<a href='administrator.php'>".ADMSLAN_4."</a></div>";
		$ns->tablerender(LAN_ERROR, $text);
		require_once("footer.php");
		exit;
	}
}

if (isset($_POST['del_admin']))
{
	$delid = array_keys($_POST['del_admin']);
	$sql->db_Select("user", "*", "user_id= ".$delid[0]);
	$row = $sql->db_Fetch();

	if ($row['user_perms'] == "0")
	{
		$text = "<div style='text-align:center'>".$row['user_name']." ".ADMSLAN_6."
		<br /><br />
		<a href='administrator.php'>".ADMSLAN_4."</a>";
		$ns->tablerender(ADMSLAN_5, $text);
		require_once("footer.php");
		exit;
	}

	$message = ($sql->db_Update("user", "user_admin=0, user_perms='' WHERE user_id= ".$delid[0])) ? ADMSLAN_61 : LAN_DELETED_FAILED;

}

if (isset($message))
{
	$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}



if($_POST['edit_admin'] || $action == "edit"){
	edit_administrator($row);
}else{
   show_admins();
}




function show_admins(){
    global $sql,$tp,$ns,$pref;

	$sql->db_Select("user", "*", "user_admin='1'");

	$text = "<div style='text-align:center'><div style='padding: 1px; ".ADMIN_WIDTH."; margin-left: auto; margin-right: auto;'>
	<form action=\"".e_SELF."\" method=\"post\" id=\"del_administrator\" >
	<div>
	<input type=\"hidden\" name=\"del_administrator_confirm\" id=\"del_administrator_confirm\" value=\"1\" />
	<table class='fborder' style='width:99%'>
	<tr>
	<td style='width:5%' class='fcaption'>ID</td>
	<td style='width:20%' class='fcaption'>".ADMSLAN_56."</td>
	<td style='width:65%' class='fcaption'>".ADMSLAN_18."</td>
	<td style='width:10%' class='fcaption'>".LAN_OPTIONS."</td>
	</tr>";

	while ($row = $sql->db_Fetch())
	{

		$text .= "<tr>
		<td style='width:5%' class='forumheader3'>".$row['user_id']."</td>
		<td style='width:20%' class='forumheader3'>".$row['user_name']."</td>
		<td style='width:65%' class='forumheader3'>";

		$permtxt = "";
        $text .= renderperms($row['user_perms'],$row['user_id'],"words");
   		$text .= "</td>

		<td style='width:10%; text-align:center' class='forumheader3'>";
		if($row['user_perms'] != "0")
		{
    		$text .= "
			<input type='image' name='edit_admin[{$row['user_id']}]' value='edit' src='".e_IMAGE."admin_images/edit_16.png' title='".LAN_EDIT."' />
			<input type='image' name='del_admin[{$row['user_id']}]' value='del' src='".e_IMAGE."admin_images/delete_16.png' onclick=\"return jsconfirm('".$tp->toJS(ADMSLAN_59."? [".$row['user_name']."]")."') \"  title='".ADMSLAN_59."' style='border:0px' />";
    	}
		$text .= "&nbsp;</td>

		</tr>";
	}

	$text .= "</table></div>\n</form></div>\n</div>";

	$ns->tablerender(ADMSLAN_13, $text);

}




function edit_administrator($row){
    global $sql,$tp,$ns,$pref;
	$lanlist = explode(",",e_LANLIST);

	$a_id = $row['user_id'];
	$ad_name = $row['user_name'];
	$a_perms = $row['user_perms'];

	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."' id='myform' >
	<table style='".ADMIN_WIDTH."' class='fborder'>
	<tr>
	<td style='width:25%' class='forumheader3'>".ADMSLAN_16.": </td>
	<td style='width:75%' class='forumheader3'>
	";

	$text .= $ad_name;
	$text .= "<input type='hidden' name='ad_name' size='60' value='$ad_name' />";

	$text .= "
	</td>
	</tr>";

	$text .="
	<tr>
	<td style='width:25%;vertical-align:top' class='forumheader3'>".ADMSLAN_18.": <br /></td>
	<td style='width:75%' class='forumheader3'>";

	$text .= checkb("1", $a_perms).ADMSLAN_19."<br />";
	$text .= checkb("2", $a_perms).ADMSLAN_20."<br />";
	$text .= checkb("3", $a_perms).ADMSLAN_21."<br />";
	$text .= checkb("4", $a_perms).ADMSLAN_22."<br />"; // Moderate users/bans etc
	$text .= checkb("5", $a_perms).ADMSLAN_23."<br />"; // create/edit custom pages/menus
	$text .= checkb("Q", $a_perms).ADMSLAN_24."<br />"; // Manage download categories
	$text .= checkb("6", $a_perms).ADMSLAN_25."<br />";  //Upload /manage files
	$text .= checkb("Y", $a_perms).ADMSLAN_67."<br />"; // file inspector
	$text .= checkb("O", $a_perms).ADMSLAN_68."<br />"; // notify
	$text .= checkb("7", $a_perms).ADMSLAN_26."<br />";
	$text .= checkb("8", $a_perms).ADMSLAN_27."<br />";
	$text .= checkb("0", $a_perms).ADMSLAN_64."<br />";
	$text .= checkb("9", $a_perms).ADMSLAN_28."<br />";
	$text .= checkb("W", $a_perms).ADMSLAN_65."<br /><br />";

	$text .= checkb("D", $a_perms).ADMSLAN_29."<br />";
	$text .= checkb("E", $a_perms).ADMSLAN_30."<br />";
	$text .= checkb("F", $a_perms).ADMSLAN_31."<br />";
	$text .= checkb("G", $a_perms).ADMSLAN_32."<br />";
	$text .= checkb("S", $a_perms).ADMSLAN_33."<br />";
	$text .= checkb("T", $a_perms).ADMSLAN_34."<br />";
	$text .= checkb("V", $a_perms).ADMSLAN_35."<br />"; // Configure public file uploads
	$text .= checkb("X", $a_perms).ADMSLAN_66."<br />";

	// $text .= checkb("A", $a_perms).ADMSLAN_36."<br />"; // Moderate forums   - PLUGIN.
	$text .= checkb("B", $a_perms).ADMSLAN_37."<br />";
	// $text .= checkb("C", $a_perms).ADMSLAN_38."<br /><br />"; // Moderate/configure chatbox

	$text .= checkb("H", $a_perms).ADMSLAN_39."<br />";
	$text .= checkb("I", $a_perms).ADMSLAN_40."<br />";
	// $text .= checkb("J", $a_perms).ADMSLAN_41."<br />";  // Post articles   - PLUGIN
	// $text .= checkb("K", $a_perms).ADMSLAN_42."<br />";  // Post reviews   - PLUGIN
	$text .= checkb("L", $a_perms).ADMSLAN_43."<br />";
	$text .= checkb("R", $a_perms).ADMSLAN_44."<br />";
	$text .= checkb("U", $a_perms).ADMSLAN_45."<br />";
	$text .= checkb("M", $a_perms).ADMSLAN_46."<br />";
	$text .= checkb("N", $a_perms).ADMSLAN_47."<br /><br />";

	$text .= "<br /><div class='fcaption'>".ADLAN_CL_7."</div><br />";
	$text .= checkb("Z", $a_perms).ADMSLAN_62."<br /><br />";

	$sql->db_Select("plugin", "*", "plugin_installflag='1'");
	while ($row = $sql->db_Fetch())
	{
		$text .= checkb("P".$row['plugin_id'], $a_perms).LAN_PLUGIN." - ".$row['plugin_name']."<br />";
	}

// Language Rights.. --------------
	if($pref['multilanguage'])
	{
		sort($lanlist);
		$text .= "<br /><div class='fcaption'>".ADLAN_132."</div><br />\n";
		$text .= checkb($pref['sitelanguage'], $a_perms).$pref['sitelanguage']."<br />\n";
		foreach($lanlist as $langval)
		{
			$langname = $langval;
			$langval = ($langval == $pref['sitelanguage']) ? "" : $langval;
			if ($langval)
	   		{
				$text .= checkb($langval, $a_perms).$langval."<br />\n";
			}
		}
	}
	// -------------------------


	$text .= "
	<br />
	<a href='".e_SELF."?checkall=1' onclick=\"setCheckboxes('myform', true, 'perms[]'); return false;\">".ADMSLAN_49."</a> -
	<a href='".e_SELF."' onclick=\"setCheckboxes('myform', false, 'perms[]'); return false;\">".ADMSLAN_51."</a>

	</td>
	</tr>";

	$text .= "<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'>";


	$text .= "<input class='button' type='submit' name='update_admin' value='".ADMSLAN_52."' />
	<input type='hidden' name='a_id' value='$a_id' />";


	$text .= "</td>
	</tr>
	</table>
	<div><input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' /></div>
	</form>
	</div>";


	$ns->tablerender(ADMSLAN_52, $text);
}



require_once("footer.php");






function checkb($arg, $perms)
{
	if (getperms($arg, $perms))
	{
		$par = "<input type='checkbox' name='perms[]' value='$arg' checked='checked' />\n";
	}
	else
	{
		$par = "<input type='checkbox' name='perms[]' value='$arg' />\n";
	}
	return $par;
}


function renderperms($perm,$id){
	global $pref,$sql,$pt;
	if($perm == "0"){
   		return ADMSLAN_58;
	}
    $sql2 = new db;
	$lanlist = explode(",",e_LANLIST);


	if(!$pt){
    	$pt["1"] = ADMSLAN_19;
		$pt["2"] = ADMSLAN_20;
		$pt["3"] = ADMSLAN_21;
		$pt["4"] = ADMSLAN_22;// Moderate users/bans etc
		$pt["5"] = ADMSLAN_23;// create/edit custom pages/menus
		$pt["Q"] = ADMSLAN_24;// Manage download categories
		$pt["6"] = ADMSLAN_25; //Upload /manage files
		$pt["Y"] = ADMSLAN_67;// file inspector
		$pt["O"] = ADMSLAN_68;// notify
		$pt["7"] = ADMSLAN_26;
		$pt["8"] = ADMSLAN_27;
		$pt["0"] = ADMSLAN_64;
		$pt["9"] = ADMSLAN_28;
		$pt["W"] = ADMSLAN_65;
    	$pt["D"] = ADMSLAN_29;
		$pt["E"] = ADMSLAN_30;
		$pt["F"] = ADMSLAN_31;
		$pt["G"] = ADMSLAN_32;
		$pt["S"] = ADMSLAN_33;
		$pt["T"] = ADMSLAN_34;
		$pt["V"] = ADMSLAN_35;
		$pt["X"] = ADMSLAN_66;
		$pt["B"] = ADMSLAN_37;
		$pt["H"] = ADMSLAN_39;
		$pt["I"] = ADMSLAN_40;
		$pt["L"] = ADMSLAN_43;
		$pt["R"] = ADMSLAN_44;
		$pt["U"] = ADMSLAN_45;
		$pt["M"] = ADMSLAN_46;
		$pt["N"] = ADMSLAN_47;
		$pt["Z"] = ADMSLAN_62;

  //		foreach($lanlist as $lan){
  //      	$pt[$lan] = $lan;
  //		}

		$sql2->db_Select("plugin", "*", "plugin_installflag='1'");
		while ($row2 = $sql2->db_Fetch()){
			$pt[("P".$row2['plugin_id'])] = LAN_PLUGIN." - ".$row2['plugin_name'];
		}
	}

	$tmp = explode(".", $perm);
		$langperm = "";
		foreach($tmp as $pms){
			if(in_array($pms, $lanlist)){
				$langperm .= $pms."&nbsp;";
			}else{
				$permtxt .= $pms;
                if($pt[$pms]){
		   			$ptext[] = $pt[$pms];
				}
			}
		}

	$ret = $permtxt;
	if($pref['multilanguage']){
		$ret .= ",&nbsp;". $langperm;
	}

	$text = "<div onclick=\"expandit('id_$id')\" style='cursor:pointer' >$ret</div>
	<div id='id_$id' style='display:none'><br />".implode("<br />",$ptext)."</div>";
    return $text;


}

?>