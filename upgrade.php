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
define("VERSION", "5.3");
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

			if(!$sql -> db_Select("prefs", "*", "pref_name='meta_tag' ")){
				$sql -> db_Insert("prefs", "'meta_tag', '<meta name=\"copyright\" content=\"&copy;2002 e107 powered site. All rights reserved.\">' ");
			}

			$text = "Metatag prefs entry created ...<br />";

			$sql -> db_Delete("prefs", "pref_name='cb_display1' OR pref_name='cb_display2' OR pref_name='cb_display3' ");

			$text .= "Unused prefs entries removed ...<br />";

			$userclass_classes_table = "CREATE TABLE ".$mySQLprefix."userclass_classes (
  userclass_id int(10) unsigned NOT NULL auto_increment,
  userclass_name varchar(100) NOT NULL default '',
  userclass_description varchar(250) NOT NULL default '',
  PRIMARY KEY  (userclass_id)
) TYPE=MyISAM;";

			$userclass_users_table = "CREATE TABLE ".$mySQLprefix."userclass_users (
  userclass_user int(10) unsigned NOT NULL default '0',
  userclass_class varchar(100) NOT NULL default ''
) TYPE=MyISAM;";

			mysql_query($userclass_classes_table);
			mysql_query($userclass_users_table);

			mysql_query("ALTER TABLE ".$mySQLprefix."banlist CHANGE banlist_ip banlist_ip VARCHAR( 150 ) NOT NULL");
			mysql_query("ALTER TABLE ".$mySQLprefix."wmessage ADD wm_id TINYINT UNSIGNED NOT NULL FIRST");

			$sql -> db_Update("wmessage", "wm_id='1'");

			mysql_query("INSERT INTO ".$mySQLprefix."wmessage VALUES ('2', 'Member message ----- This text (if activated) will appear at the top of your front page all the time - only logged in members will see this.', '0')");
			mysql_query("INSERT INTO ".$mySQLprefix."wmessage VALUES ('3', 'Administrator message ----- This text (if activated) will appear at the top of your front page all the time - only logged in administrators will see this.', '0')");


			$text .= "Userclass tables created ...<br />";

			$sql -> db_Update("e107", "e107_version='5.3b2'");
			$sql -> db_Update("e107", "e107_build ='1'");
			

			if($sql -> db_Select("prefs", "pref_name='sitetag' AND pref_value REGEXP('Website System') ")){
				$sql -> db_Update("prefs", "pref_value ='Website System v5.3b2' WHERE pref_name='sitetag' ");
			}

			$text .= "<br /><b>Version number updated to 5.3b2.</b><br />";
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