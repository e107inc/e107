<?php
/*
+---------------------------------------------------------------+
|	e107 website system 3.1				        |
|	/admin/help/downloads ver 3.1	                	|
|								|
|	©William Moffett II 2001-2002				|
|	http://qnome.d2g.com					|
|	qnome@attbi.com						|
|								|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).		|
+---------------------------------------------------------------+
*/

$text = "Downloads are seperated into different categories of your choice.<br /> 
Downloads in the new categorie will be displayed in the download block if activated.";
$ns -> tablerender("Downloads Help", $text);
$text = "";

if(IsSet($_POST['unactivate_download'])){
	$sql -> dbUpdate("UPDATE ".MUSER."prefs SET pref_value='0' WHERE pref_name='download_activate' ");
       $message = "New Download Block deactivate, pref updated in database.";
}

if(IsSet($_POST['activate_download'])){
	$sql -> dbUpdate("UPDATE ".MUSER."prefs SET pref_value='1' WHERE pref_name='download_activate' ");
        $message = "New Download Block activate, pref updated in database.";
}

if(IsSet($_POST['updatepref_block'])){
	$sql -> dbUpdate("UPDATE ".MUSER."prefs SET pref_value='$download_block_posts' WHERE pref_name='download_block_posts' ");
        $message = "Block post pref updated in database.";
        /* unset ($download_block_posts); */

}

if(IsSet($_POST['updatepref_download'])){
	$sql -> dbUpdate("UPDATE ".MUSER."prefs SET pref_value='$download_posts' WHERE pref_name='download_posts' ");
               $message = "Download posts updated in database."; 
              /* unset ($download_posts); */
}

if(!$sql -> dbQuery("SELECT * FROM ".MUSER."prefs WHERE pref_name='download_activate' ")){
	$sql -> dbInsert("INSERT INTO ".MUSER."prefs VALUES ('download_activate', '0')");
              $message = "No Block pref found in database. Download Block pref updated in database.";
}


if(!$sql -> dbQuery("SELECT * FROM ".MUSER."prefs WHERE pref_name='download_block_posts' ")){
	$sql -> dbInsert("INSERT INTO ".MUSER."prefs VALUES ('download_block_posts', '5')");
              $message = "No Block Post pref found in database. Download Block Post pref updated in database.";
}

if(!$sql -> dbQuery("SELECT * FROM ".MUSER."prefs WHERE pref_name='download_posts' ")){
	$sql -> dbInsert("INSERT INTO ".MUSER."prefs VALUES ('download_posts', '5')");
              $message = "No Download Post pref found in database. Download Download Post pref updated in database.";
}


$sql -> dbQuery("SELECT * FROM ".MUSER."prefs WHERE pref_name='download_activate' ");
$row = $sql -> dbFetch();

$text .= "<form method=\"post\" action=\"$PHP_SELF\">
<table style=\"width:95%\">
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">
Set your download block preferences from here.: </td>
</tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\" style=\"text-align:center\">";
if($row[1] == 1){
	$text .= "<br />Click the button below to turn download block <b>off</b><br /><br /><input class=\"button\" type=\"submit\" name=\"unactivate_download\" value=\"Download Block OFF\" /><br /><br /></td>
</tr>
</table> ";
}else{
	$text .= "<br />Click the button below to turn download block <b>on</b><br /><br /><input class=\"button\" type=\"submit\" name=\"activate_download\" value=\"Download Block ON\" /><br /><br /></td>
</tr>
</table> ";
}


$sql -> dbQuery("SELECT * FROM ".MUSER."prefs WHERE pref_name='download_block_posts' ");
$dbprow = $sql -> dbFetch();


$text .= "<form method=\"post\" action=\"$PHP_SELF\">
<table style=\"width:95%\">
<tr>
<td style=\"width:70%\">Downloads to display in block?: </td>
<td style=\"width:30%\">
<select name=\"download_block_posts\" class=\"tbox\">";
if($dbprow[1] == 5){
	$text .= "<option selected>5</option>\n";
}else{
	$text .= "<option>5</option>\n";
}
if($dbprow[1] == 10){
	$text .= "<option selected>10</option>\n";
}else{
	$text .= "<option>10</option>\n";
}
if($dbprow[1] == 15){
	$text .= "<option selected>15</option>\n";
}else{
	$text .= "<option>15</option>\n";
}
if($dbprow[1] == 20){
	$text .= "<option selected>20</option>\n";
}else{
	$text .= "<option>20</option>\n";
}
if($dbprow[1] == 25){
	$text .= "<option selected>25</option>\n";
}else{
	$text .= "<option>25</option>\n";
}

$text .= "</select>
</td>
</tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">
<input class=\"button\" type=\"submit\" name=\"updatepref_block\" value=\"Update Block\" />
</td>
</tr>
</table>
</form>";

$sql -> dbQuery("SELECT * FROM ".MUSER."prefs WHERE pref_name='download_posts' ");
$row = $sql -> dbFetch();

$sql -> dbQuery("SELECT * FROM ".MUSER."prefs WHERE pref_name='download_posts' ");
$dprow = $sql -> dbFetch();

$text .= "<form method=\"post\" action=\"$PHP_SELF\">
<table style=\"width:95%\">
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\"><br />
Set your download Page preferences from here.:<br /><br />
</td>
</tr>
<tr>
<td style=\"width:70%\">Downloads to display per page?: </td>
<td style=\"width:30%\">
<select name=\"download_posts\" class=\"tbox\">";
if($dprow[1] == 5){
	$text .= "<option selected>5</option>\n";
}else{
	$text .= "<option>5</option>\n";
}
if($dprow[1] == 10){
	$text .= "<option selected>10</option>\n";
}else{
	$text .= "<option>10</option>\n";
}
if($dprow[1] == 15){
	$text .= "<option selected>15</option>\n";
}else{
	$text .= "<option>15</option>\n";
}
if($dprow[1] == 20){
	$text .= "<option selected>20</option>\n";
}else{
	$text .= "<option>20</option>\n";
}
if($dprow[1] == 25){
	$text .= "<option selected>25</option>\n";
}else{
	$text .= "<option>25</option>\n";
}

$text .= "</select>
</td>
</tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">
<input class=\"button\" type=\"submit\" name=\"updatepref_download\" value=\"Update Page\" />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("Download Config", $text);


?>