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
		$fp = fopen(e_PLUGIN."theme_layout/layouts/".$_POST['filename']."_main.php","w");
		@fwrite($fp, $_POST['data']);
		fclose($fp);
		$message = "File succesfully saved as ".str_replace("../../", "", e_PLUGIN)."theme_layout/layouts/".$_POST['filename']."_main.php";
		$data = stripslashes($_POST['data']);
	}
}


$handle=opendir(e_PLUGIN."theme_layout/layouts/");
while ($file = readdir($handle)){
	if($file != "." && $file != ".." && strstr($file, "_main.php")){
		$dirlist[] = eregi_replace("\..*", "", $file);
	}
}
closedir($handle);


if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

if(!$data){ $data = "\$HEADER = \"\n\n\n\n\n\";\n\n\$FOOTER = \"\n\n\n\n\n\";"; }


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

<div class='spacer'><input class='button' type='button' value='LOGO' onclick=\"addtext('{LOGO}')\" onMouseOver=\"help('This will insert the default logo at the current position. The default logo is called \'logo.png\' and is located in the e107_images folder. To insert your own logo either overwrite the default logo with your own, or use a normal html &lt;img&gt; tag.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='SITENAME' onclick=\"addtext('{SITENAME}')\" onMouseOver=\"help('This will insert the SITENAME variable as defined from the preferences screen.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='SITETAG' onclick=\"addtext('{SITETAG}')\"  onMouseOver=\"help('This will insert the SITETAG variable as defined from the preferences screen.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='SITELINKS' onclick=\"addtext('{SITELINKS}')\"  onMouseOver=\"help('This will insert the main navigation box with your site links.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='MENU' onclick=\"addtext('{MENU=}')\"  onMouseOver=\"help('This will insert a menu area, the syntax is MENU=&lt;menu area&gt;, ie MENU=1. Once the area is defined you will be able to add menu items to the area from your menus screen.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='SETSTYLE' onclick=\"addtext('{SETSTYLE}')\"  onMouseOver=\"help('SETSTYLE allows you to define a style that will be used by the tablerender function, use this to change the look of menu items in different areas. For an example please see the \'example\' theme which uses SETSTYLE to make the menus in the two areas render differently.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='SITEDISCLAIMER' onclick=\"addtext('{SITEDISCLAIMER}')\"  onMouseOver=\"help('This will insert the SITEDISCLAIMER variable as defined from the preferences screen.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='CUSTOM=login' onclick=\"addtext('{CUSTOM=login}')\"  onMouseOver=\"help('This will render the login form at the specified posotion, for example if you wanted the login form along the top of the screen as opposed to inside a menu item.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='CUSTOM=search' onclick=\"addtext('{CUSTOM=search}')\"  onMouseOver=\"help('This will render the search form in the specified position.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='CUSTOM=quote' onclick=\"addtext('{CUSTOM=quote}')\"  onMouseOver=\"help('This will render a random quote, you need to have your quote file in the root directory and it must be called \'quote.php\' or an error will result.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='CUSTOM=clock' onclick=\"addtext('{CUSTOM=clock}')\"  onMouseOver=\"help('This will render the date/clock display at the specified position.')\" onMouseOut=\"help('')\"></div>
<div class='spacer'><input class='button' type='button' value='BANNER' onclick=\"addtext('{BANNER}')\"  onMouseOver=\"help('This will render a random banner at the specified position. If you only wish to use banners from a certain campaign use the syntax {BANNER=your campaign name}.')\" onMouseOut=\"help('')\"></div>
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
The \$HEADER and \$FOOTER variables define how your site is laid out. The code in \$HEADER will be sent to screen first, followed by the main content, followed lastly by the code in \$FOOTER. For example, on the main front page that holds your news items, first \$HEADER is rendered, then your news items, then \$FOOTER.<br />
\$HEADER and \$FOOTER are comprised of normal HTML tags such as table or divs, and the template functions which you can add by clicking on the relevant button down the left column.<br />
Everything between the quotation marks will be defined as the variable.<br />
The easiest way to make your layout is to use a WYSIWYG editor such as Dreamweaver to layout your tables or divs, then add the template functions to the code afterwards.<br />
<b>Template functions have to be on a line on their own to be recognised properly.</b>

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

