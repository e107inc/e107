<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/admin_template.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../../class2.php");
if(!getperms("P")){ header("location:".e_BASE."index.php"); exit; }

extract($_POST);

if(is_array($go)){
	$path = e_PLUGIN."theme_layout/";
	switch($go[0]){
		case "Main Layout":
			header("location:".$path."main_layout.php");
			exit;
		break;

		case "News":
			header("location:".$path."news_layout.php");
			exit;
		break;

		case "Comments":
			header("location:".$path."comment_layout.php");
			exit;
		break;

		case "Chatbox":
			header("location:".$path."chatbox_layout.php");
			exit;
		break;

		case "Poll":
			header("location:".$path."poll_layout.php");
			exit;
		break;

		case "Forum":
			header("location:".$path."forum_layout.php");
			exit;
		break;
	}		
}

require_once(e_ADMIN."auth.php");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:85%' class='fborder'>
<tr>
<td style='text-align:center' class='forumheader3'>
This script will help you in creating a theme for e107. Although it will not completely write the theme for you it will provide you with the basic tools that you need to make the templates for your theme.<br /><br />From here you can create or edit the main site layout, the news template, the comments template, the chatbox template, the poll template and the forum template, although you dont need to define them all, if one or more elements isn't defined the default template will be used.<br /><br />
Once all your templates are written and saved, copy them all into the theme.php file in the userfiles folder and save it as theme.php in a new folder in your themes directory. Define your style.css file (its much easier to use an existing one from a different theme and just edit it to your liking), and the theme images and your theme will be ready to go.<br /><br />
Click on a button below to start creating your templates ...
<br /><br />
<div class='spacer'><input class='button' type='submit' style='width:200px' name='go[]' value='Main Layout' /></div>
<div class='spacer'><input class='button' type='submit' style='width:200px' name='go[]' value='News' /></div>
<div class='spacer'><input class='button' type='submit' style='width:200px' name='go[]' value='Comments' /></div>
<div class='spacer'><input class='button' type='submit' style='width:200px' name='go[]' value='Chatbox' /></div>
<div class='spacer'><input class='button' type='submit' style='width:200px' name='go[]' value='Poll' /></div>
<div class='spacer'><input class='button' type='submit' style='width:200px' name='go[]' value='Forum' /></div>
</td></tr></table></form></div>";

$ns -> tablerender("Theme Layout Creator", $text);

require_once(e_ADMIN."footer.php");

?>