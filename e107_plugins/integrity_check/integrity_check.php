<?php

require_once("../../class2.php");

//Only "Master of Desaster" can use this thing!
//Change to "P" If you wanna give your plugin-Admins the same rights
if (!getperms("0")) { header("location:".e_Base."index.php"); exit; }

if(e_QUERY){
	$query = explode(".", e_QUERY);
}

//Language-definitions
@include_once("languages/".e_LANGUAGE.".php");
@include_once("languages/English.php");

//Load normal Header ?
if (in_array("header", $query)) {
        require_once(e_ADMIN."auth.php");
}
else {
	$adminfpage = (!$pref['adminstyle'] || $pref['adminstyle'] == "default" ? "admin.php" : $pref['adminstyle'].".php");
     	//Alternative Header
     	$text = "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n
        <!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
        <html xmlns=\"http://www.w3.org/1999/xhtml\">
        <head>
        <title>".$sitename."</title>
        <link rel=\"stylesheet\" href=\"".THEME."style.css\" />";
        if(file_exists(e_FILE."e107.css")){ $text .= "\n<link rel='stylesheet' href='".e_FILE."e107.css' />\n"; }
        if(file_exists(e_FILE."style.css")){ $text .= "\n<link rel='stylesheet' href='".e_FILE."style.css' />\n"; }
        $text .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".CHARSET."\" />
        <meta http-equiv=\"content-style-type\" content=\"text/css\" />
        <script type='text/javascript' src='".e_FILE."e107.js'></script>";
        if(file_exists(THEME."theme.js")){ $text .= "<script type='text/javascript' src='".THEME."theme.js'></script>"; }
        if(file_exists(e_FILE."user.js")){ $text .= "<script type='text/javascript' src='".e_FILE."user.js'></script>\n"; }
        $text .= "</head>
        <body><div style='text-align:center'>
        <img src='".e_IMAGE."adminlogo.png' alt='Logo' />
        </div><br />
        <div>
        <table style='width:100%' cellspacing='10' cellpadding='10'>
        <tr>
        <td style='width:15%; vertical-align: top;'>";
        echo $text;
        $text ="<a href='".e_ADMIN.$adminfpage."'>".Integ_24."</a><br /><a href='".e_BASE."index.php'>".Integ_25."</a><br /><br />";
        $ns -> tablerender("Admin", $text);
        echo "</td>
        <td style='width:60%; vertical-align: top;'>";
}
$text = "";



//check Version you are using
if(file_exists(e_ADMIN."ver.php")){
	include_once(e_ADMIN."ver.php");
}

//Arrays for replacing Directorys (if non-standard)
$dirs_1 = array($ADMIN_DIRECTORY, $FILES_DIRECTORY, $IMAGES_DIRECTORY, $THEMES_DIRECTORY, $PLUGINS_DIRECTORY, $HANDLERS_DIRECTORY, $LANGUAGES_DIRECTORY, $HELP_DIRECTORY);
$dirs_2 =array("e107_admin/", "e107_files/", "e107_images/", "e107_themes/", "e107_plugins/", "e107_handlers/", "e107_languages/", "e107_docs/help/");

//Files / Dirs never coming into core-sfv
$exclude = array($FILES_DIRECTORY."backend", $FILES_DIRECTORY."downloadimages", $FILES_DIRECTORY."downloads", $FILES_DIRECTORY."downloadthumbs", $FILES_DIRECTORY."images", $FILES_DIRECTORY."misc", $FILES_DIRECTORY."public" , substr($IMAGES_DIRECTORY, 0, strlen($IMAGES_DIRECTORY)-1), $PLUGINS_DIRECTORY."custom", "e107_config.php");

//Output-Path
$o_path = "crc/";

unset($message);

//for core-crc-files: merging arrays together
if (IsSet($_POST['activate'])) {
	if (IsSet($_POST['Arr'])){
		$_arr = array_merge($_POST['Arr'], array($_POST['activate']));
	}
	else {
		$_arr = array($_POST['activate']);
	}
} else { $_arr = array(); }



//Make a new core sfv-File
if (IsSet($_POST['donew']) && IsSet($_POST['save_file_name'])) {
	$file_array = hex_getdirs(e_BASE, $exclude , "1");
	sort($file_array);
	unset($t_array);
	reset($file_array);
	$data="";
	foreach($file_array as $v){
		$data .= str_replace($dirs_1, $dirs_2, $v)."<-:sfv:->".generate_sfv_checksum(e_BASE.$v)."\n";
	}
	if (!IsSet($_POST['gz_core'])){
		$dh=@fopen($o_path.$_POST['save_file_name'], "w");
		if (@fwrite($dh, $data)){
			$message = "<div align='center'>".Integ_01."</div>";
		}
		else {
			$message = "<div align='center'>".Integ_02."</div>";
		}
		fclose($dh);
	}
	else {
		$dh=@gzopen($o_path.$_POST['save_file_name'].".gz", "wb");
		if (@gzwrite($dh, $data)){
			$message = "<div align='center'>".Integ_01."</div>";
		}
		else {
			$message = "<div align='center'>".Integ_02."</div>";
		}
		gzclose($dh);
	}
}


//Make a new plugin-crc-file
if (IsSet($_POST['doplugfile']) && $_POST['save_plug_name'] != "") {
	$file_array = hex_getdirs($_POST['plug_activate']."/", array() , "1", e_PLUGIN."/");
	sort($file_array);
	unset($t_array);
	reset($file_array);
	$data="";
	foreach($file_array as $v){
		$data .= str_replace($dirs_1, $dirs_2, $v)."<-:sfv:->".generate_sfv_checksum(e_BASE."/".$v)."\n";
	}
	if (!IsSet($_POST['gz_plug'])){
		$dh=@fopen($o_path.$_POST['save_plug_name'], "w");
		if (@fwrite($dh, $data)){
			$message = "<div align='center'>".Integ_01."</div>";
		}
		else {
			$message = "<div align='center'>".Integ_02."</div>";
		}
		fclose($dh);
	}
	else {
		$dh=@gzopen($o_path.$_POST['save_plug_name'].".gz", "wb");
		if (@gzwrite($dh, $data)){
			$message = "<div align='center'>".Integ_01."</div>";
		}
		else {
			$message = "<div align='center'>".Integ_02."</div>";
		}
		gzclose($dh);
	}
}

//Check existing sfv-File
if (IsSet($_POST['docheck']) && $_POST['input_files'] != "") {
	if (file_exists($_POST['input_files'])) { $message .= check_sfv_file($_POST['input_files'],$_POST['theme_folders']); }
	else { $message = Integ_05; }
}

//Message-Output
if (IsSet($message)) {
	$ns -> tablerender("", "<b>".$message."</b>");
}


//Load existing core-sfv-Files matching your Version
unset($file_array);
$t_array = array();
$core_array = hex_getfiles($o_path, $PLUGINS_DIRECTORY, $e107info);
$t_array = array();

//Search plugin-crc-files
if($file_array = hex_getfiles(e_PLUGIN, $PLUGINS_DIRECTORY)){
	sort($file_array);
}
unset($t_array);


//Start output here
$text .="<div style='text-align:center'>
<form method='post' action='".e_SELF."?".e_QUERY."' name='integrity_check'>
<table style='width:95%'>

<tr>
<td class='fcaption' colspan='2'>".Integ_06."
</td>
</tr>
<tr>
<td style='width:60%' class='forumheader3'>";
if ($core_array[0]) {
	$text .="<b><u>".Integ_19.":</u></b><br />";
	reset($core_array);
	foreach($core_array as $v){
		$text .= "<input type='radio' name='input_files' value='".$v."' >".str_replace(array(e_BASE, e_PLUGIN, $o_path), array("", "", ""), $v)."<br />";
	}	
}
else {
	$text .= "<b>".Integ_16."</b><br />";
}
if ($file_array[0]) {
	$text .="<hr><b><u>".Integ_20.":</u></b><br />";
	reset($file_array);
	foreach($file_array as $v){
		$text .= "<input type='radio' name='input_files' value='".$v."' >".str_replace(array(e_BASE, e_PLUGIN, $PLUGINS_DIRECTORY), array("", "", ""), $v)."<br />";
	}
}
else {
	$text .= "<b>".Integ_17."</b><br />";
}
if ($core_array[0]) {
	$text .="<br /><hr>
	<input type='checkbox' name='theme_folders' value='on'>".Integ_23."<br />";
}

$text .="</td>
<td style='width:40%' class='forumheader3'>
<input class='button' type='submit' name='docheck' size='20' value='".Integ_08."' />
</td>
</tr>";

if(is_writable($o_path)){
	
	//do_core_file.php only available 4 dev-team sorry guys...
	if (file_exists("do_core_file.php")) {
		require_once("do_core_file.php");
	}
	$text .="<tr>
	<td class='fcaption' colspan='2'>".Integ_18."
	</td>
	</tr>
	<tr>
	<td style='width:60%' class='forumheader3'>
	
	<select name='plug_activate' class='tbox' onChange=\"document.integrity_check.save_plug_name.value=hex_strReplace(this.options[selectedIndex].value, '".e_PLUGIN."','')+'.crc' \" ><option></option>";
	$file_array = hex_getdirs(e_PLUGIN, $exclude, "3");
	sort($file_array);
	unset($t_array);
	reset($file_array);
	foreach($file_array as $v){
		if (!in_array($v, $_arr)) {
			$v = str_replace($PLUGINS_DIRECTORY, "", $v);
			$text .= "<option value='".e_PLUGIN.$v."'>".$v."</option>";
		}
	}
	$text .= "</select>
	</td>
	<td style='width:40%' class='forumheader3' >".Integ_21."
	</td>
	</tr>
	<tr>
	<td style='width:60%' class='forumheader3'>".Integ_11."&nbsp;
	<input class='tbox' type='text' name='save_plug_name' size='40' value='' readonly>
	</td>
	<td style='width:40%' class='forumheader3'>
	<input type='checkbox' name='gz_plug' value='.gz' checked />".Integ_22."
	</td>
	</tr>
	<td class='forumheade3' colspan='2'>
	<div align='center'>
	<input class='button' type='submit' name='doplugfile' size='20' value='".Integ_12."' />
	</div>
	</td>
	</tr>
	</td></tr></table></form></div>";
}
else {
	$text .="<tr>
	<td class='forumheader3' colspan='2'>".str_replace("{output}", $o_path, Integ_14)."
	</td>
	</tr></table></form></div>";
}
$text .= "<br /><br /><br /><a href='".e_SELF."?header'>".Integ_26."</a><br />
	<a href='".e_SELF."?footer'>".Integ_27."</a><br />
	<a href='".e_SELF."?header.footer'>".Integ_28."</a>";	

//Render this fuck
$ns -> tablerender(Integ_13, $text);

//Load normal footer?
if (in_array("footer", $query)) {
        require_once(e_ADMIN."footer.php");
}
else {
     	//Alternative Footer
     	echo "\n
        </td>
        </tr>
        </table></div>
        </body>
        </html>";
}

/*
+----------------------
|
|   Loads of stupid
|         Functions
|         no one
|         really
|         needs
|
|         ;)
|
+-----------------------
*/

//The following is for php < 4.3.0 not knowing file_get_contents
if (!function_exists('file_get_contents'))
{
    function file_get_contents($filename, $use_include_path = 0)
    { 
        $file = @fopen($filename, 'rb', $use_include_path); 
        if ($file) 
        { 
            if ($fsize = @filesize($filename)) 
            { 
                $data = fread($file, $fsize); 
            } 
            else 
            { 
                while (!feof($file)) 
                { 
                    $data .= fread($file, 1024); 
                } 
            } 
            fclose($file); 
        } 
        return $data;
    } 
}

//Load crc-file and check any of its files
function check_sfv_file($filename, $check=""){
	$errors_miss = "";
	$errors_crc = "";
	if (strpos($filename, ".gz") == strlen($filename)-3) { $p = 1; }
	else { $p = 0; }
	if ($p == 0) { $dh = @fopen($filename, "r"); }
	else { $dh = @gzopen($filename, "rb"); }
	while (!feof($dh)) {
   		if ($p == 0) { $line = fgets($dh, 4096); }
   		else { $line = gzgets($dh, 4096); }
		$a = substr($line, 0, strpos($line, "<-:sfv:->"));
   		if ($a) {
   			$b = substr($line, (strpos($line, "<-:sfv:->")+9));
   			$a = str_replace($dirs_2, $dirs_1, $a);
   			if (file_exists(e_BASE.$a)) {
   				if (trim($b) != trim(generate_sfv_checksum(e_BASE.$a))) {
   					if (!$errors_crc) {
   						$errors_crc = "<br />
   						<div align='center'><u>".Integ_04."*</u></div><br /><ul>";
   					} 
   					$errors_crc  .= "<li>".$a;
   				}
   			}
   			elseif ($a != "install.php" && $a != "upgrade.php" && (strpos($a, "e107_themes/") !== 0 || !$check || strpos($a, "templates/") != 0)) {
   				if (!$errors_miss) {
   					$errors_miss = "<br />
   					<div align='center'><u>".Integ_03."</u></div><br /><ul>";
   				} 
   				$errors_miss .= "<li>".$a;
   			}
   		}
   	}
	if ($p == 0) { fclose($dh); }
	else { gzclose($dh); }
   	//Error-Output
   	$mess = "";
   	if ($errors_crc) { $mess .= $errors_crc."</ul>"; }
	if ($errors_miss) { $mess .= $errors_miss."</ul>"; }
   	if (!$errors_crc && !$errors_miss) {
   		$mess .= "<br />".Integ_15;
   	}
   	if ($errors_crc) { $mess .= Integ_29; }
   	return $mess;
}
//Generating Checksum for File
function generate_sfv_checksum($filename) {
	return strtoupper(dechex(crc32(str_replace(chr(13).chr(10), chr(10) ,file_get_contents(str_replace(" ", "%20", $filename))))));
}

//Get Files for doing a crc-file
function hex_getfiles($dir, $root, $m=""){
	global $t_array;
	$dh = opendir($dir);
	while($file = readdir($dh)){
		if($file != "." and $file != ".." && $file != "index.html" && $file != "null.txt") {
			if(is_file($dir.$file)){
				if ((is_array($m) && strpos($file, "core_".$m['e107_version']."b".$m['e107_build'].".crc") === 0) || ($m == "" && strpos($file, "core_") !== 0 && (strpos($file, ".crc") === strlen($file)-4 || strpos($file, ".crc.gz") === strlen($file)-7))){
					$t_array[] = $dir.$file;
				}
			}else{
				hex_getfiles($dir.$file."/", $root, $m);
			}
		}
	}
	closedir($dh);
	return $t_array;
}

//Load e107-Files ($s = 1) or e107-File-Tree ($s = 2)
function hex_getdirs($dir, $root, $s="1", $path=e_BASE){
	global $t_array, $_arr;
	$dh = opendir($dir);
	$search = array("../", $path);
	$replace = array("", "");
	while($file = readdir($dh)){
		if($file != "." and $file != ".." && $file != "index.html" && $file != "null.txt" && !in_array($file, $root) && !in_array(str_replace($search, $replace, $dir.$file), $root)){
			if(is_file($dir.$file)){
				if ($s == "1") {
					$t_array[] = str_replace($search, $replace, $dir.$file);
				}
			}elseif (!in_array($file, $_arr) && !in_array(str_replace($search, $replace, $dir.$file), $_arr)) {
				if ($s == "2" || $s == "3") {
					$t_array[] = str_replace($search, $replace, $dir.$file);
				}
				if ($s != "3") {
					hex_getdirs($dir.$file."/", $root, $s);
				}
			}
		}
	}
	closedir($dh);
	return $t_array;
}

//Format String : 1 -> 0001 , 12 -> 0012
function format_string($string){
	settype($string, "string");
	$a = "0000".$string;
	$b = strrpos($a, $string);
	return substr($a, (strlen($a)-$b), 4);
}

?>

<script LANGUAGE="JavaScript" type="text/javascript">
<!--
function hex_strReplace(a, b, c){
	return a.split(b).join(c);
}
// -->
</script>
