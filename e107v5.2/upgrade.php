<?php
/*
+---------------------------------------------------------------+
|	e107 website system													|
|	/template.php																|
|																						|
|	©Steve Dunstan 2001-2002										|
|	http://jalist.com															|
|	stevedunstan@jalist.com											|
|																						|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).				|
+---------------------------------------------------------------+
*/
define("VERSION", "5.05");
require_once("class2.php");
require_once(HEADERF);

if(IsSet($_POST['usubmit'])){
	$a_name = $_POST['a_name'];
	$a_password = md5($_POST['a_password']);

	if($sql -> db_Select("admin", "*", "admin_name='$a_name' AND admin_password='$a_password' ")){
		$row = $sql -> db_Fetch();
		extract($row);
		if($admin_permissions != 0){
			$error = "Unable to continue - upgrade process must be carried out by main site administrator.";
		}else{

			if(!$sql -> db_Select("prefs", "*", "pref_name='cb_linkc' ")){
				$sql -> db_Insert("prefs", "'cb_linkc', '- link -' ");
			}
			if(!$sql -> db_Select("prefs", "*", "pref_name='cb_wordwrap' ")){
				$sql -> db_Insert("prefs", "'cb_wordwrap', '30' ");
			}
			if(!$sql -> db_Select("prefs", "*", "pref_name='cb_linkreplace' ")){
				$sql -> db_Insert("prefs", "'cb_linkreplace', 'enabl' ");
			}
			$d1 = "<div class=\"spacer\"><img src=\"themes/e107/images/bullet2.gif\" alt=\"bullet\" /><b>NICKNAME</b><br /><span class=\"smalltext\">DATE</span><br />";
			$d2 = "<div class=\"smallblack\">MESSAGE</div></div>";
			$d3 = "<br />";
			if(!$sql -> db_Select("prefs", "*", "pref_name='cb_display1' ")){
				$sql -> db_Insert("prefs", "'cb_display1', '$d1' ");
			}
			if(!$sql -> db_Select("prefs", "*", "pref_name='cb_display2' ")){
				$sql -> db_Insert("prefs", "'cb_display2', '$d2' ");
			}
			if(!$sql -> db_Select("prefs", "*", "pref_name='cb_display3' ")){
				$sql -> db_Insert("prefs", "'cb_display3', '$d3' ");
			}

			$text .= "Chatbox pref features added<br />";

			if(!$sql -> db_Select("prefs", "*", "pref_name='log_lvcount' ")){
				$sql -> db_Insert("prefs", "'log_lvcount', '10' ");
			}

			mysql_query("ALTER TABLE ".$mySQLprefix."e107_stat_info CHANGE info_name info_name TEXT NOT NULL");
			$text .= "Stat_info table altered<br />";

$stat_last_table = "CREATE TABLE ".$mySQLprefix."stat_last (
  stat_last_date int(11) unsigned NOT NULL default '0',
  stat_last_info text NOT NULL
) TYPE=MyISAM;";

			mysql_query($stat_last_table);
			$text .= "stat_last table created<br />";


			$sql -> db_Update("e107", "e107_version='5.2'");
			$sql -> db_Update("e107", "e107_build ='1'");
			$text .= "<b>Version number updated to 5.2.</b><br />";

		}
	}else{
		$error = "Unable to continue - administrator not found in database.";
	}

	if($error != ""){
		$ns -> tablerender("Upgrade Error", $error);
	}else{
		$ns -> tablerender("Upgrade Completed", $text);
	}

	require_once(FOOTERF);
	exit;
}

$text = "
Please enter your main administrator name and password to begin upgrade.<br /><br />


<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
<table style=\"width:95%\">
<tr>
<td style=\"width:30%\" class=\"mediumtext\">Main administrator name:</td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"text\" name=\"a_name\" size=\"60\" value=\"\" maxlength=\"100\" />
</td>
</tr>
<tr>
<td style=\"width:30%\" class=\"mediumtext\">Main administrator Password:</td>
<td style=\"width:70%\">
<input class=\"tbox\" type=\"password\" name=\"a_password\" size=\"60\" value=\"\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td colspan=\"2\" style=\"text-align:center\">
<br />
<input class=\"button\" type=\"submit\" name=\"usubmit\" value=\"Submit\" />
</td>
</tr>

</table>";

$ns -> tablerender("e107 Upgrade", $text);
require_once(FOOTERF);
exit;
?>