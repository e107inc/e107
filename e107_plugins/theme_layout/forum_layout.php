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
require_once(e_ADMIN."auth.php");

if(IsSet($_POST['loadtemplate'])){
	$filename = e_PLUGIN."theme_layout/layouts/".$_POST['layout'].".php";
	$fd = fopen ($filename, "r");
	$data = fread($fd, filesize($filename));
	fclose($fd);
	$data = htmlspecialchars($data);
}

if(IsSet($_POST['savefile'])){
	if(!$_POST['filename']){
		$message = "No filename specified.";
	}else if(file_exists(e_PLUGIN."theme_layout/layouts/".$_POST['filename'])){
		$message = "File already exists, please choose different file name.";
	}else if(!is_writable(e_PLUGIN."theme_layout/layouts")){
		$message = "Unable to write to ".e_PLUGIN."theme_layout/layouts/ folder, please ensure it has it's permissions set to 777 (CHMOD 777).";
	}else{
		$fp = fopen(e_PLUGIN."theme_layout/layouts/".$_POST['filename']."_forum.php","w");
		@fwrite($fp, $_POST['data']);
		fclose($fp);
		$message = "File succesfully saved as ".str_replace("../../", "", e_PLUGIN)."theme_layout/layouts/".$_POST['filename']."_forum.php";
		$data = stripslashes($_POST['data']);
	}
}


$handle=opendir(e_PLUGIN."theme_layout/layouts/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && strstr($file, "_forum.php")){
		$dirlist[] = eregi_replace("\..*", "", $file);
	}
}
closedir($handle);


if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(!$data){ $data = "\$FORUMSTART = \"\n\n\n\n\n\";\n\n\$FORUMTHREADSTYLE = \"\n\n\n\n\n\";\n\n\$FORUMREPLYSTYLE = \"\n\n\n\n\n\";\n\n\$FORUMEND = \"\n\n\n\n\n\";"; }


$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' name='dataform'>
<table style='width:85%' class='fborder'>
<tr>
<td colspan='2' style='text-align:center' class='forumheader'>
<span class='defaulttext'>Load Template</span> 
<select name='layout' class='tbox'>\n";

$counter = 0;
while(IsSet($dirlist[$counter])){
	$text .= "<option>".$dirlist[$counter]."</option>\n";
	$counter++;
}
$text .= "</select>
<input class='button' type='submit' name='loadtemplate' value='Load' />


</td>
</tr>

<tr>
<td style='width:30%; text-align:center; vertical-align:top' class='forumheader3'>
<b>Functions</b><br /><br />

<div class='spacer'><input class='button' type='button' value='BREADCRUMB' onclick=\"addtext('{BREADCRUMB}')\" onMouseOver=\"help('Inserts the link back to the previous pages and forum front page.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='NEXTPREV' onclick=\"addtext('{NEXTPREV}')\" onMouseOver=\"help('Inserts the links to the next and previous threads.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='TRACK' onclick=\"addtext('{TRACK}')\"  onMouseOver=\"help('Inserts the tracking link (if tracking is enabled).')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='MODERATORS' onclick=\"addtext('{MODERATORS}')\"  onMouseOver=\"help('Inserts the forum moderators.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='GOTOPAGES' onclick=\"addtext('{GOTOPAGES}')\"  onMouseOver=\"help('Inserts the link to next and previous pages if multi-page thread.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='BUTTONS' onclick=\"addtext('{BUTTONS}')\"  onMouseOver=\"help('Inserts the post reply and post new thread buttons.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='NEWFLAG' onclick=\"addtext('{NEWFLAG}')\"  onMouseOver=\"help('If the thread is new since the visitor\'s last visit this will show the new post image.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='POSTER' onclick=\"addtext('{POSTER}')\"  onMouseOver=\"help('This will display the name of the poster.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='THREADDATESTAMP' onclick=\"addtext('{THREADDATESTAMP}')\"  onMouseOver=\"help('Inserts the date and time the post was made.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='QUOTEIMG' onclick=\"addtext('{QUOTEIMG}')\"  onMouseOver=\"help('Inserts the quote and link to reply with quote.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='AVATAR' onclick=\"addtext('{AVATAR}')\"  onMouseOver=\"help('Inserts the poster\'s avatar if they have one defined.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='MEMBERID' onclick=\"addtext('{MEMBERID}')\"  onMouseOver=\"help('Inserts the poster\s unique member ID number.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='RPG' onclick=\"addtext('{RPG}')\"  onMouseOver=\"help('Calls the RPG script to show member statistics.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='LEVEL' onclick=\"addtext('{LEVEL}')\"  onMouseOver=\"help('Shows the poster\s level, based on forum and chatbox posts, comments and visits to site..')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='JOINED' onclick=\"addtext('{JOINED}')\"  onMouseOver=\"help('Inserts date the poster registered at the site.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='POSTS' onclick=\"addtext('{POSTS}')\"  onMouseOver=\"help('Inserts the amount of posts the poster has made.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='LOCATION' onclick=\"addtext('{LOCATION}')\"  onMouseOver=\"help('Inserts the poster's location if he has defined it.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='POST' onclick=\"addtext('{POST}')\"  onMouseOver=\"help('Inserts the actual thread post.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='SIGNATURE' onclick=\"addtext('{SIGNATURE}')\"  onMouseOver=\"help('Inserts the poster\s signature if they have defined it.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='TOP' onclick=\"addtext('{TOP}')\"  onMouseOver=\"help('Inserts a link to top of page.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='PROFILEIMG' onclick=\"addtext('{PROFILEIMG}')\"  onMouseOver=\"help('Inserts image to poster\s profile.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='EMAILIMG' onclick=\"addtext('{EMAILIMG}')\"  onMouseOver=\"help('Inserts the image to poster\'s email.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='WEBSITEIMG' onclick=\"addtext('{WEBSITEIMG}')\"  onMouseOver=\"help('Inserts the image to the poster\'s website.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='PRIVMESSAGE' onclick=\"addtext('{PRIVMESSAGE}')\"  onMouseOver=\"help('Inserts the image to the private message script if installed.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='MODOPTIONS' onclick=\"addtext('{MODOPTIONS}')\"  onMouseOver=\"help('Inserts the moderator options if moderator is recognised.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='FORUMJUMP' onclick=\"addtext('{FORUMJUMP}')\"  onMouseOver=\"help('Inserts a form item to allow users to jumpquickly to other forums.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='QUICKREPLY' onclick=\"addtext('{QUICKREPLY}')\"  onMouseOver=\"help('Inserts a quick reply form allowing a reply to be made without going to main post screen.')\" onMouseOut=\"help('')\"></div>

<br /><br />
<textarea class='tbox' name='helpb' cols='30' rows='15' style='overflow:hidden;'></textarea>
</td>

<td style='width:70%; text-align:center; vertical-align:top' class='forumheader3'>
<textarea class='tbox' name='data' cols='80' rows='40' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>$data</textarea>
<br />
File Name: <input class='tbox' type='text' name='filename' size='30' value='' maxlength='50' />
<input class='button' type='submit' name='savefile' value='Save File' />

</td>
</tr>
</table>

</form>
<br />
<table style='width:85%' class='fborder'>
<tr>
<td style='text-align:center' class='forumheader3'>
There are four variables to define when templating the forum. \$FORUMSTART defines everything at the top of the forum, before the first thread is shown. \$FORUMTHREADSTYLE defines how the starting thread is shown. \$FORUMREPLYSTYLE defines how the reply posts are shown. Finally \$FORUMEND defines how everything after the last post is shown.<br />
If \$FORUMREPLYSTYLE is empty \$FORUMTHREADSTYLE will be used for replies as well.

</td>
</tr>
</table>
</div>";

$ns -> tablerender("Layout Editor [ <a href='".e_PLUGIN."theme_layout/theme_layout.php'>return to front page</a> ]", $text);

require_once(e_ADMIN."footer.php");


?>	

<script type="text/javascript">
<!--
function addtext(text) {
	if (document.dataform.data.createTextRange && document.dataform.data.caretPos) {
		var caretPos = document.dataform.data.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		document.dataform.data.focus();
	} else {
	document.dataform.data.value  += text;
	document.dataform.data.focus();
	}
}
function storeCaret (textEl) {
	if (textEl.createTextRange) 
	textEl.caretPos = document.selection.createRange().duplicate();
}

function help(help){
	document.dataform.helpb.value = help;
}

// -->
</script>

