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
|     $Source: /cvs_backup/e107_0.7/email.php,v $
|     $Revision: 1.6 $
|     $Date: 2005-02-07 19:04:35 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);
	
$qs = explode(".", e_QUERY, 2);
if ($qs[0] == "") {
	header("location:".e_BASE."index.php");
	 exit;
}
$source = $qs[0];
$parms = $qs[1];
	
$comments = $tp->post_toHTML($_POST['comment'], TRUE);
$author = $tp->post_toHTML($_POST['author_name']);
$email_send = check_email($_POST['email_send']);

if (isset($_POST['emailsubmit']))
{
	if (!$email_send)
	{
		$error .= LAN_106;
	}
	if ($comments == "")
	{
		$message = LAN_188." ".SITENAME." (".SITEURL.")";
		if (USER == TRUE)
		{
			$message .= "\n\n".LAN_email_1." ".USERNAME;
		}
		else
		{
			$message .= "\n\n".LAN_email_1." ".$author;
		}
	}
	else
	{
		$message .= $comments;
	}
	$ip = getip();
	$message .= "\n\n".LAN_email_2." ".$ip."\n\n";
	 
	if(strpos($source,'plugin:') !== FALSE)
	{
		$plugin = substr($source,7);
		if(file_exists(e_PLUGIN.$plugin."/e_emailprint.php"))
		{
			include_once(e_PLUGIN.$plugin."/e_emailprint.php");
			$text = email_item($parms);
		}
		$message .= $text;
	}
	else
	{
		$sql->db_Select("news", "*", "news_id='$parms'");
		list($news_id, $news_title, $news_body, $news_extended, $news_datestamp, $news_author, $news_source, $news_url, $news_category, $news_allow_comments) = $sql->db_Fetch();
		$message .= $tp->toHTML($news_title, TRUE)."\n".$tp->toHTML($news_body, TRUE)."\n".$tp->toHTML($news_extended, TRUE)."\n\n".SITEURL.e_BASE."comment.php?comment.news.".$parms;
	}
	if ($error == "") {
		require_once(e_HANDLER."mail.php");
		if (sendemail($email_send, LAN_email_3.SITENAME, $message)) {
			$text = "<div style='text-align:center'>".LAN_10." ".$email_send."</div>";
		} else {
			$text = "<div class='center'>".LAN_9."</div>";
		}
		$ns->tablerender(LAN_11, $text);
	} else {
		$ns->tablerender(LAN_12, "<div style='text-align:center'>".$error."</div>");
	}
}
	
$text = "<form method='post' action='".e_SELF."?".e_QUERY."'>\n
	<table class='defaulttable'>";
	
if (USER != TRUE) {
	$text .= "<tr>
		<td style='width:20%'>".LAN_7."</td>
		<td style='width:80%'>
		<input class='tbox' type='text' name='author_name' size='60' value='$author' maxlength='100' />
		</td>
		</tr>";
}
$text .= "<tr>
	<td style='width:20%'>".LAN_8."</td>
	<td style='width:80%'>
	<textarea class='tbox' name='comment' cols='70' rows='4'>".LAN_email_6." ".SITENAME." (".SITEURL.")";
if (USER == TRUE) {
	$text .= "\n\n".LAN_email_1." ".USERNAME;
}
	
$text .= "</textarea>
	</td>
	</tr>
	 
	<tr>
	<td style='width:20%'>".LAN_187."</td>
	<td style='width:80%'>
	<input class='tbox' type='text' name='email_send' size='60' value='$email_send' maxlength='100' />
	</td>
	</tr>
	 
	<tr style='vertical-align:top'>
	<td style='width:20%'></td>
	<td style='width:80%'>
	<input class='button' type='submit' name='emailsubmit' value='".LAN_email_4."' />
	</td>
	</tr>
	</table>
	</form>";
	
$ns->tablerender(LAN_email_5, $text);
	
	
	
require_once(FOOTERF);
?>