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
				
			$sql -> db_Update("e107", "e107_version='5.1'");
			$text = "Version number updated to 5.05.<br />";

			if(!$sql -> db_Select("prefs", "*", "pref_name='time_offset' ")){
				$sql -> db_Insert("prefs", "'time_offset', '0' ");
			}
			$text .= "Time offset pref added<br />";

			if(!$sql -> db_Select("prefs", "*", "pref_name='flood_time' ")){
				$sql -> db_Insert("prefs", "'flood_time', '30' ");
			}
			if(!$sql -> db_Select("prefs", "*", "pref_name='flood_hits' ")){
				$sql -> db_Insert("prefs", "'flood_hits', '100' ");
			}
			$text .= "Basic flood prefs added<br />";

			mysql_query("ALTER TABLE ".$mySQLprefix."prefs  CHANGE pref_value pref_value TEXT NOT NULL");
			$text .= "Prefs table altered<br />";
			mysql_query("ALTER TABLE ".$mySQLprefix."forum_t ADD thread_s TINYINT(1) UNSIGNED NOT NULL");
			$text .= "Forum_t table altered<br />";

			if($sql -> db_Select("prefs", "*", "pref_name='sitetag' AND pref_value='Website System Version 5.05' ")){
				$sql -> db_Update("prefs", "pref_value='Website System Version 5.1' WHERE pref_name='sitetag' ");
			}

			mysql_query("CREATE TABLE ".$mySQLprefix."flood ( flood_url text NOT NULL, flood_time int(10) unsigned NOT NULL default '0') TYPE=MyISAM;");
			$text .= "Flood table created<br />";

			mysql_query("ALTER TABLE e107_admin CHANGE admin_permissions admin_permissions TEXT NOT NULL");
			$text .= "Admin table updated<br />";

			$text .= "<br /><br />Upgrade process complete - now running e107 version ".VERSION.".";
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