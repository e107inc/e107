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

".str_replace("POLLSTYLE", "pollstyle", $_POST['data'])."

?".chr(62);
		$fp = fopen(e_PLUGIN."theme_layout/layouts/".$_POST['filename']."_poll.php","w");
		@fwrite($fp, $dts);
		fclose($fp);
		$message = "File succesfully saved as ".str_replace("../../", "", e_PLUGIN)."theme_layout/layouts/".$_POST['filename']."_poll.php";
		$pollstyle = stripslashes($_POST['data']);
	}
}


$handle=opendir(e_PLUGIN."theme_layout/layouts/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && strstr($file, "_poll.php")){
		$dirlist[] = eregi_replace("\..*", "", $file);
	}
}
closedir($handle);


if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(!$pollstyle){ $pollstyle = "\n\n\n\n\n"; }


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

<div class='spacer'><input class='button' type='button' value='QUESTION' onclick=\"addtext('{QUESTION}')\" onMouseOver=\"help('Inserts the poll question.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='VOTE_TOTAL' onclick=\"addtext('{VOTE_TOTAL}')\" onMouseOver=\"help('Inserts the total amount of votes the poll has received.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='COMMENTS' onclick=\"addtext('{COMMENTS}')\"  onMouseOver=\"help('Inserts the amount of comments the poll has received, with a link to the comments page.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='OLDPOLLS' onclick=\"addtext('{OLDPOLLS}')\"  onMouseOver=\"help('Inserts a link to the old polls page.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='OPTIONS=' onclick=\"addtext('{OPTIONS=}')\"  onMouseOver=\"help('Opens the poll options.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='OPTION' onclick=\"addtext('OPTION')\"  onMouseOver=\"help('Inserts poll option (choice).')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='BAR' onclick=\"addtext('BAR')\"  onMouseOver=\"help('Inserts the result bar graphic showing how many votes the option has got.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='PERCENTAGE' onclick=\"addtext('PERCENTAGE')\"  onMouseOver=\"help('Shows what percentage of the votes the option has received.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='VOTES' onclick=\"addtext('VOTES')\"  onMouseOver=\"help('Shows how many votes the option has received.')\" onMouseOut=\"help('')\"></div>
<br /><br />
<textarea class='tbox' name='helpb' cols='30' rows='15' style='overflow:hidden;'></textarea>






</td>

<td style='width:70%; text-align:center; vertical-align:top' class='forumheader3'>
<textarea class='tbox' name='data' cols='80' rows='40' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>\$POLLSTYLE = \"".$pollstyle."\";</textarea>
<br />
File Name: <input class='tbox' type='text' name='filename' size='30' value='' maxlength='50' />
<input class='button' type='submit' name='savefile' value='Save File' />

</td>
</tr>
</table>
<input type='hidden' name='pollstyle' value=\"".addslashes(htmlentities($pollstyle))."\">

</form>
<br />
<table style='width:85%' class='fborder'>
<tr>
<td style='text-align:center' class='forumheader3'>
\$POLLSTYLE defines how your polls are rendered, as with the other templates it's just normal HTML and template functions.<br />This template is slightly different to the others in that you have to define how the options are shown by enclosing all the option text in {OPTION=<i>text</i>} - see pre-existing themes or templates to see how this is done.
Everything between the quotation marks will be defined as \$POLLSTYLE.<br />
</b>

</td>
</tr>
</table>
</div>";

$ns -> tablerender("Layout Editor - Polls [ <a href='".e_PLUGIN."theme_layout/theme_layout.php'>return to front page</a> ]", $text);

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

