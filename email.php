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
require_once("class2.php");
require_once(HEADERF);

$id = $_SERVER['QUERY_STRING'];
if($id == ""){ header("location:index.php"); }

if(IsSet($_POST['emailsubmit'])){
	if(!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $_POST['email_send'])){
		 $error .= LAN_106;
	 }
	 if($_POST['comment'] == ""){
		 $message = LAN_188." ".SITENAME." (".SITEURL.")";
		if(USER == TRUE){
			$message .= "\n\nFrom ".USERNAME;
		}else{
			$message .= "\n\nFrom: ".$_POST['author_name'];
		}
	 }
	$ip = getip();
	$message .= "\n\nIP address of sender: ".$ip."\n\n";
	$sql -> db_Select("news", "*", "news_id='$id' ");
	list($news_id, $news_title, $news_body, $news_extended, $news_datestamp, $news_author, $news_source, $news_url, $news_category, $news_allow_comments) = $sql-> db_Fetch();
	$message .= $_POST['comment']."\n\n".$news_title."\n".$news_body."\n".$news_extended."\n\n";

	if($error == ""){
		if(@mail($_POST['email_send'], "News item from ".SITENAME, $message, "From: newssend@".SITENAME."\r\n"."Reply-To: ".NULL ."\r\n"."X-Mailer: PHP/" . phpversion())){
			$text = "<div style=\"text-align:center\">Mail sent to ".$_POST['email_send']."</div>";
		}else{
			$text = "<div style=\"text-align:center\">Sorry - unable to send email</div>";
		}
		$ns -> tablerender("Email sent", $text);
	}else{
		$ns -> tablerender("Error", "<div style=\"text-align:center\">".$error."</div>");
	}
}

$text = "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."?$id\">\n
<table style=\"width:95%\">";

if(USER != TRUE){
	$text .= "<tr>
<td style=\"width:20%\">".LAN_7."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"author_name\" size=\"60\" value=\"$author_name\" maxlength=\"100\" />
</td>
</tr>";
}
$text .= "<tr> 
<td style=\"width:20%\">".LAN_8."</td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"comment\" cols=\"70\" rows=\"4\">".LAN_188." ".SITENAME." (".SITEURL.")";
if(USER == TRUE){
	$text .= "\n\nFrom ".USERNAME;
}

$text .= "</textarea>
</td>
</tr>

<tr>
<td style=\"width:20%\">".LAN_187."</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"email_send\" size=\"60\" value=\"$email_send\" maxlength=\"100\" />
</td>
</tr>

<tr style=\"vertical-align:top\"> 
<td style=\"width:20%\"></td>
<td style=\"width:80%\">
<input class=\"button\" type=\"submit\" name=\"emailsubmit\" value=\"".LAN_186."\" />
</td>
</tr>
</table>
</form>";

$ns -> tablerender("Email news item to someone", $text);



require_once(FOOTERF);
?>