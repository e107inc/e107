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

".str_replace("NEWSSTYLE", "newsstyle", $_POST['data'])."

?".chr(62);
		$fp = fopen(e_PLUGIN."theme_layout/layouts/".$_POST['filename']."_news.php","w");
		@fwrite($fp, $dts);
		fclose($fp);
		$message = "File succesfully saved as ".str_replace("../../", "", e_PLUGIN)."theme_layout/layouts/".$_POST['filename']."_news.php";
		$newsstyle = stripslashes($_POST['data']);
	}
}


$handle=opendir(e_PLUGIN."theme_layout/layouts/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && strstr($file, "_news.php")){
		$dirlist[] = eregi_replace("\..*", "", $file);
	}
}
closedir($handle);


if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(!$newsstyle){ $newsstyle = "\n\n\n\n\n"; }


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

<div class='spacer'><input class='button' type='button' value='NEWSTITLE' onclick=\"addtext('{NEWSTITLE}')\" onMouseOver=\"help('Inserts news title.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='NEWSBODY' onclick=\"addtext('{NEWSBODY}')\" onMouseOver=\"help('Inserts main news body, the main text of the news.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='NEWSICON' onclick=\"addtext('{NEWSICON}')\"  onMouseOver=\"help('Inserts the category icon of the news post (the icon assigned to the categroy the news post is in).')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='NEWSHEADER' onclick=\"addtext('{NEWSHEADER}')\"  onMouseOver=\"help('Inserts the category icon but without a link to the category page, useful for creating a news header graphic.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='NEWSCATEGORY' onclick=\"addtext('{NEWSCATEGORY}')\"  onMouseOver=\"help('Inserts the name of the category the news item is in.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='NEWSAUTHOR' onclick=\"addtext('{NEWSAUTHOR}')\"  onMouseOver=\"help('Inserts the author of the news item.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='NEWSDATE' onclick=\"addtext('{NEWSDATE}')\"  onMouseOver=\"help('Inserts the time and date the news item was created.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='NEWSCOMMENTS' onclick=\"addtext('{NEWSCOMMENTS}')\"  onMouseOver=\"help('Inserts the amount of comments for the news item, with a link to the comments page. If links are turned off for the news item it will insert the COMMENTLINKREPLACE variable (see below for explanation of variables).')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='EMAILICON' onclick=\"addtext('{EMAILICON}')\"  onMouseOver=\"help('Inserts the email icon, with a link to the \'email to a friend\' page.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='PRINTICON' onclick=\"addtext('{PRINTICON}')\"  onMouseOver=\"help('Inserts the print icon with a link to the \'printer friendly\' version of the newspost.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='NEWSID' onclick=\"addtext('{NEWSID}')\"  onMouseOver=\"help('Inserts unique ID number of news item.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='ADMINOPTIONS' onclick=\"addtext('{ADMINOPTIONS}')\"  onMouseOver=\"help('Inserts the edit and delete links that are only shown to admins with the correct permissions.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='EXTENDED' onclick=\"addtext('{EXTENDED}')\"  onMouseOver=\"help('Inserts a link to the extended news, for example \'Read More ...\'.')\" onMouseOut=\"help('')\"></div>
<br /><br />
<textarea class='tbox' name='helpb' cols='30' rows='15' style='overflow:hidden;'></textarea>






</td>

<td style='width:70%; text-align:center; vertical-align:top' class='forumheader3'>
<textarea class='tbox' name='data' cols='80' rows='40' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>\$NEWSSTYLE = \"".$newsstyle."\";</textarea>
<br />
File Name: <input class='tbox' type='text' name='filename' size='30' value='' maxlength='50' />
<input class='button' type='submit' name='savefile' value='Save File' />

</td>
</tr>
</table>
<input type='hidden' name='newsstyle' value=\"".addslashes(htmlentities($newsstyle))."\">

</form>
<br />
<table style='width:85%' class='fborder'>
<tr>
<td style='text-align:center' class='forumheader3'>
\$NEWSSTYLE defines how your news items are rendered. Apart from the main layout there are a few variables that need to be set, for example ICONSTYLE sets how your news icon is shown - please see some existing themes to see what variables exist and what they are for.<br />
Everything between the quotation marks will be defined as \$NEWSSTYLE.<br />
</b>

</td>
</tr>
</table>
</div>";

$ns -> tablerender("Layout Editor - News [ <a href='".e_PLUGIN."theme_layout/theme_layout.php'>return to front page</a> ]", $text);

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

