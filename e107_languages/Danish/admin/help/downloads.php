<?php
/*
+---------------------------------------------------------------+
|	e107 website system 3.1				        |
|	/admin/help/downloads ver 3.1	                	|
|								|
|	�William Moffett II 2001-2002				|
|	http://qnome.d2g.com					|
|	qnome@attbi.com						|
|								|
|	Released under the terms and conditions of the		|
|	GNU General Public License (http://gnu.org).		|
+---------------------------------------------------------------+
*/

$text = "Downloads er indelt i forskællige kategorier efter dit valg.<br /> 
Downloads i den nye kategori vil blive vist i download blokken hvis aktiveret.";
$ns -> tablerender("Downloads Hjælp", $text);
$text = "";

if(IsSet($_POST['unactivate_download'])){
	$sql -> dbUpdate("UPDATE ".MUSER."prefs SET pref_value='0' WHERE pref_name='download_activate' ");
       $message = "Nyt Download Blok deaktiver, instillinger opdateret i databasen.";
}

if(IsSet($_POST['activate_download'])){
	$sql -> dbUpdate("UPDATE ".MUSER."prefs SET pref_value='1' WHERE pref_name='download_activate' ");
        $message = "Nyt Download Blok aktiver, instillinger opdateret i databasen.";
}

if(IsSet($_POST['updatepref_block'])){
	$sql -> dbUpdate("UPDATE ".MUSER."prefs SET pref_value='$download_block_posts' WHERE pref_name='download_block_posts' ");
        $message = "Blok post instillinger opdateret i databasen.";
        /* unset ($download_block_posts); */

}

if(IsSet($_POST['updatepref_download'])){
	$sql -> dbUpdate("UPDATE ".MUSER."prefs SET pref_value='$download_posts' WHERE pref_name='download_posts' ");
               $message = "Download poster opdateret i databasen."; 
              /* unset ($download_posts); */
}

if(!$sql -> dbQuery("SELECT * FROM ".MUSER."prefs WHERE pref_name='download_activate' ")){
	$sql -> dbInsert("INSERT INTO ".MUSER."prefs VALUES ('download_activate', '0')");
              $message = "Ingen Blok instillinger fundet i databasen. Download Blok instillinger opdateret i databasen.";
}


if(!$sql -> dbQuery("SELECT * FROM ".MUSER."prefs WHERE pref_name='download_block_posts' ")){
	$sql -> dbInsert("INSERT INTO ".MUSER."prefs VALUES ('download_block_posts', '5')");
              $message = "Ingen Blok Post instillinger fundet i databasen. Download Blok Post instillinger opdateret i databasen";
}

if(!$sql -> dbQuery("SELECT * FROM ".MUSER."prefs WHERE pref_name='download_posts' ")){
	$sql -> dbInsert("INSERT INTO ".MUSER."prefs VALUES ('download_posts', '5')");
              $message = "Ingen Download Post instillinger fundet i databasen. Download Download Post instillinger opdateret i databasen.";
}


$sql -> dbQuery("SELECT * FROM ".MUSER."prefs WHERE pref_name='download_activate' ");
$row = $sql -> dbFetch();

$text .= "<form method=\"post\" action=\"$PHP_SELF\">
<table style=\"width:95%\">
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\">
Sæt dine download blok instillinger herfra.: </td>
</tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\" style=\"text-align:center\">";
if($row[1] == 1){
	$text .= "<br />Klik på knappen herunder for at sætte download blokken <b>fra</b><br /><br /><input class=\"button\" type=\"submit\" name=\"unactivate_download\" value=\"Download Blokken FRA\" /><br /><br /></td>
</tr>
</table> ";
}else{
	$text .= "<br />Klik på knappen herunder for at sætte download blokken <b>til</b><br /><br /><input class=\"button\" type=\"submit\" name=\"activate_download\" value=\"Download Blokken TIL\" /><br /><br /></td>
</tr>
</table> ";
}


$sql -> dbQuery("SELECT * FROM ".MUSER."prefs WHERE pref_name='download_block_posts' ");
$dbprow = $sql -> dbFetch();


$text .= "<form method=\"post\" action=\"$PHP_SELF\">
<table style=\"width:95%\">
<tr>
<td style=\"width:70%\">Downloads der skal vises i blokken?: </td>
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
<input class=\"button\" type=\"submit\" name=\"updatepref_block\" value=\"Opdater Blok\" />
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
Sæt dine download side instillinger herfra.:<br /><br />
</td>
</tr>
<tr>
<td style=\"width:70%\">Downloads der vises pr. side?: </td>
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
<input class=\"button\" type=\"submit\" name=\"updatepref_download\" value=\"Opdater Side\" />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("Download Konfiguration", $text);


?>