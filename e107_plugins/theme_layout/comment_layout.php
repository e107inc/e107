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
	require_once(e_PLUGIN."theme_layout/layouts/".$_POST['layout'].".php");
}

if(IsSet($_POST['savefile'])){
	if(!$_POST['filename']){
		$message = "No filename specified.";
	}else if(file_exists(e_PLUGIN."theme_layout/layouts/".$_POST['filename'])){
		$message = "File already exists, please choose different file name.";
	}else if(!is_writable(e_PLUGIN."theme_layout/layouts")){
		$message = "Unable to write to ".e_PLUGIN."theme_layout/layouts/ folder, please ensure it has it's permissions set to 777 (CHMOD 777).";
	}else{
		$dts = chr(60)."?php

".str_replace("COMMENTSTYLE", "commentstyle", $_POST['data'])."

?".chr(62);

		$fp = fopen(e_PLUGIN."theme_layout/layouts/".$_POST['filename']."_comment.php","w");
		@fwrite($fp, $dts);
		fclose($fp);
		$message = "File succesfully saved as ".str_replace("../../", "", e_PLUGIN)."theme_layout/layouts/".$_POST['filename']."_comment.php";
		$commentstyle = stripslashes($_POST['data']);
	}
}


$handle=opendir(e_PLUGIN."theme_layout/layouts/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && strstr($file, "_comment.php")){
		$dirlist[] = eregi_replace("\..*", "", $file);
	}
}
closedir($handle);


if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(!$commentstyle){ $commentstyle = "\n\n\n\n\n"; }


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

<div class='spacer'><input class='button' type='button' value='USERNAME' onclick=\"addtext('{USERNAME}')\" onMouseOver=\"help('Inserts name of person who posted comment.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='TIMEDATE' onclick=\"addtext('{TIMEDATE}')\" onMouseOver=\"help('Inserts date and time when comment was posted.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='AVATAR' onclick=\"addtext('{AVATAR}')\"  onMouseOver=\"help('Inserts poster's avatar if they have one defined.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='COMMENTS' onclick=\"addtext('{COMMENTS}')\"  onMouseOver=\"help('Inserts amount of comments the poster has left.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='COMMENT' onclick=\"addtext('{COMMENT}')\"  onMouseOver=\"help('Inserts the actual comment itself.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='SIGNATURE' onclick=\"addtext('{SIGNATURE}')\"  onMouseOver=\"help('Inserts posters signature of they have one defined.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='JOINED' onclick=\"addtext('{JOINED}')\"  onMouseOver=\"help('Inserts the date and time the poster registered at the site.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='ADMINOPTIONS' onclick=\"addtext('{ADMINOPTIONS}')\"  onMouseOver=\"help('Inserts the admin options (block, delete and info) if an admin with the correct permissions is recognised.')\" onMouseOut=\"help('')\"></div>
<br /><br />
<textarea class='tbox' name='helpb' cols='30' rows='15' style='overflow:hidden;'></textarea>






</td>

<td style='width:70%; text-align:center; vertical-align:top' class='forumheader3'>
<textarea class='tbox' name='data' cols='80' rows='40' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>\$COMMENTSTYLE = \"".$commentstyle."\";</textarea>
<br />
File Name: <input class='tbox' type='text' name='filename' size='30' value='' maxlength='50' />
<input class='button' type='submit' name='savefile' value='Save File' />

</td>
</tr>
</table>
<input type='hidden' name='commentstyle' value=\"".addslashes(htmlentities($commentstyle))."\">

</form>
<br />
<table style='width:85%' class='fborder'>
<tr>
<td style='text-align:center' class='forumheader3'>
\$COMMENTSTYLE defines how your comments are rendered, as with the other templates it's just normal HTML and template functions.<br />
Everything between the quotation marks will be defined as \$COMMENTSTYLE.<br />
</b>

</td>
</tr>
</table>
</div>";

$ns -> tablerender("Layout Editor - Comments [ <a href='".e_PLUGIN."theme_layout/theme_layout.php'>return to front page</a> ]", $text);

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

