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

".str_replace("CHATBOXSTYLE", "chatboxstyle", $_POST['data'])."

?".chr(62);

		$fp = fopen(e_PLUGIN."theme_layout/layouts/".$_POST['filename']."_chatbox.php","w");
		@fwrite($fp, $dts);
		fclose($fp);
		$message = "File succesfully saved as ".str_replace("../../", "", e_PLUGIN)."theme_layout/layouts/".$_POST['filename']."_chatbox.php";
		$chatboxstyle = stripslashes($_POST['data']);
	}
}


$handle=opendir(e_PLUGIN."theme_layout/layouts/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && strstr($file, "_chatbox.php")){
		$dirlist[] = eregi_replace("\..*", "", $file);
	}
}
closedir($handle);


if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(!$chatboxstyle){ $chatboxstyle = "\n\n\n\n\n"; }


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

<div class='spacer'><input class='button' type='button' value='USERNAME' onclick=\"addtext('{USERNAME}')\" onMouseOver=\"help('Inserts name of person who posted chatbox post.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='TIMEDATE' onclick=\"addtext('{TIMEDATE}')\" onMouseOver=\"help('Inserts date and time when chatbox post was posted.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='MESSAGE' onclick=\"addtext('{MESSAGE}')\"  onMouseOver=\"help('Inserts the actual chatbox post itself.')\" onMouseOut=\"help('')\"></div>
<br /><br />
<textarea class='tbox' name='helpb' cols='30' rows='15' style='overflow:hidden;'></textarea>






</td>

<td style='width:70%; text-align:center; vertical-align:top' class='forumheader3'>
<textarea class='tbox' name='data' cols='80' rows='40' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>\$CHATBOXSTYLE = \"".$chatboxstyle."\";</textarea>
<br />
File Name: <input class='tbox' type='text' name='filename' size='30' value='' maxlength='50' />
<input class='button' type='submit' name='savefile' value='Save File' />

</td>
</tr>
</table>
<input type='hidden' name='chatboxstyle' value=\"".addslashes(htmlentities($chatboxstyle))."\">

</form>
<br />
<table style='width:85%' class='fborder'>
<tr>
<td style='text-align:center' class='forumheader3'>
\$CHATBOXSTYLE defines how your chatox posts are rendered, as with the other templates it's just normal HTML and template functions.<br />
Everything between the quotation marks will be defined as \$CHATBOXSTYLE.<br />
</b>

</td>
</tr>
</table>
</div>";

$ns -> tablerender("Layout Editor - Chatbox [ <a href='".e_PLUGIN."theme_layout/theme_layout.php'>return to front page</a> ]", $text);

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

