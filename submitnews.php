<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/submitnews.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);

if(IsSet($_POST['submit'])){
	if($_POST['author_name'] != "" && $_POST['author_email'] != "" && $_POST['item'] != ""){
		$ip = getip();	
		$fp = new floodprotect;
		if($fp -> flood("submitnews", "submitnews_datestamp") == FALSE){
			header("location:".e_HTTP."index.php");
			die();
		}
		$aj = new textparse;
		$itemtitle = $aj -> tp($_POST['itemtitle']);
		$item = $aj -> tp($_POST['item']);
				 
		$sql -> db_Insert("submitnews", "0, '".$_POST['author_name']."', '".$_POST['author_email']."', '$itemtitle', '$item', '".time()."', '$ip', '0' ");
		$ns -> tablerender(LAN_133, LAN_134);
		require_once(FOOTERF);
		exit;
	}
}


$text = "
<form method=\"post\" action=\"".e_SELF."\">\n
<table style=\"width:95%\">
<tr>
<td style=\"width:20%\">".LAN_7."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"author_name\" size=\"60\" value=\"$author_name\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">".LAN_112."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"author_email\" size=\"60\" value=\"$author_email\" maxlength=\"100\" />
</td>
</tr>

<tr>
<td style=\"width:20%\">".LAN_62."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"itemtitle\" size=\"60\" value=\"$itemtitle\" maxlength=\"200\" />
</td>
</tr>

<tr> 
<td style=\"width:20%\">".LAN_135."</td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"item\" cols=\"70\" rows=\"10\"></textarea>
</td>
</tr>\n
<tr style=\"vertical-align:top\"> 
<td style=\"width:20%\"></td>
<td style=\"width:80%\">
<input class=\"button\" type=\"submit\" name=\"submit\" value=\"".LAN_136."\" />
<br />
<br />
<span class=\"smalltext\">
".LAN_10."
</span>
</td>
</tr>
</table>
</form>";

$ns -> tablerender(LAN_136, $text);

require_once(FOOTERF);
?>