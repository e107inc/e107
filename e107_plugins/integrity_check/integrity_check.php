<?php
/*
+----------------------
|
|	Integrity-Check-Plugin v0.03
|
|	Checks for corrupted and missing files
|
|	©HeX0R 2004
|
|	for the
|
|	e107 website system
|	©Steve Dunstan 2001-2004
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
|
|
+-----------------------
*/

//The following is for php < 4.3.0 not knowing file_get_contents
if (!function_exists('file_get_contents')) {
	function file_get_contents($filename, $use_include_path = 0) {
		$file = @fopen($filename, 'rb', $use_include_path);
		if ($file) {
			if ($fsize = @filesize($filename)) { $data = fread($file, $fsize); }
			else {
				while (!feof($file)) { $data .= fread($file, 1024); }
			}
			fclose($file);
		} 
		return $data;
	}
}

//Count lines of File
function lines($filename){
	if (strpos($filename, ".gz") == strlen($filename)-3) { $p = 1; }
	else { $p = 0; }
	$dh = ($p==0 ? @fopen($filename, "r") : @gzopen($filename, "rb"));
	$i = 0;
	$end = ($p==0 ? feof($dh) : gzeof($dh));
	while (!$end) {
		$a = ($p==0 ? fgets($dh, 4096) : gzgets($dh, 4096));
		$i++;
		$end = ($p==0 ? feof($dh) : gzeof($dh));
	}
	$a = ($p==0 ? fclose($dh) : gzclose($dh));
	return $i;
}

//Load crc-file and check any of its files		
function check_sfv_file($filename, $check="", $from=0, $counts=0){
	global $_log, $dirs_1, $dirs_2, $o_path;
	$_counter=1;
	$the_end=1;
	if ($_log[4]>1) {
		$dh_crc= (file_exists($o_path."log_crc.txt") ? @fopen($o_path."log_crc.txt", "a") : FALSE);
		$dh_miss= (file_exists($o_path."log_miss.txt") ? @fopen($o_path."log_miss.txt", "a") : FALSE);
	}
	if (strpos($filename, ".gz") == strlen($filename)-3) { $p = 1; }
	else { $p = 0; }
	$dh = ($p == 0 ? @fopen($filename, "r") : @gzopen($filename, "rb"));
	$end = ($p==0 ? feof($dh) : gzeof($dh));
	while (!$end && $counts >= $_counter) {
		if ($from==0){
   			$line = ($p == 0 ? fgets($dh, 4096) : $line = gzgets($dh, 4096));
   			++$_counter;
   		}
   		else {
   			if ($p==0) { fseek($dh, $from); }
   			else { gzseek($dh, $from); }
   			$line = ($p == 0 ? fgets($dh, 4096) : gzgets($dh, 4096));
   			++$_counter;
			$from=0;
		}
		$a = substr($line, 0, strpos($line, "<-:sfv:->"));
   		if ($a) {
   			$b = substr($line, (strpos($line, "<-:sfv:->")+9));
   			$a = str_replace($dirs_2, $dirs_1, $a);
   			if (file_exists(e_BASE.$a)) {
   				if (trim($b) != trim(generate_sfv_checksum(e_BASE.$a))) {
   					if ($_log[4] == 1) {
						$_log['crc']  .= "<li>".$a."</li>";
					}
					else {
						if (!$dh_crc) { $dh_crc = @fopen($o_path."log_crc.txt", "w"); }
						@fwrite($dh_crc, "<li>".$a."</li>\n");
					}
   				}
   			}
   			elseif ($a != "install.php" && $a != "upgrade.php" && (strpos($a, "e107_themes/") !== 0 || !$check || strpos($a, "templates/") != 0)) {
   				if ($_log[4] == 1) {
					$_log['miss'] .= "<li>".$a."</li>";
				}
				else {
					if (!$dh_miss) { $dh_miss = @fopen($o_path."log_miss.txt", "w"); }
					@fwrite($dh_miss, "<li>".$a."</li>\n");
				}
   			}
   		}
   		$end = ($p==0 ? feof($dh) : gzeof($dh));
   	}
	if ($end) { $the_end=0; }
	if ($p == 0) {
		$_log[5]=ftell($dh);
		fclose($dh);
	}
	else {
		$_log[5]=gztell($dh);
		gzclose($dh);
	}
	if ($_log[4]>1){
		if ($dh_crc) { fclose($dh_crc); }
		if ($dh_miss) { fclose($dh_miss); }
	}
   	return $the_end;
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


require_once("../../class2.php");

//Output-Path
$o_path = "crc/";

if (!getperms("P")) { header("location:".e_BASE."index.php"); exit; }

if(e_QUERY){
	$query = explode(".", e_QUERY);
}

//Language-definitions
@include_once((file_exists("languages/".e_LANGUAGE.".php") ? "languages/".e_LANGUAGE.".php" : "languages/English.php"));

if (file_exists($o_path."log.txt")){
	$_log = explode(".-.", stripslashes(file_get_contents($o_path."log.txt")));
	$steps=intval($_log[2] / $_log[4])+1;
}
else {
	if (file_exists($o_path."log_crc.txt")) {
		$err_1 = @unlink($o_path."log_crc.txt");
	}
	if (file_exists($o_path."log_miss.txt")) {
		$err_2 = @unlink($o_path."log_miss.txt");
	}
	
}
//Load normal Header ?
if (in_array("header", $query) && ($_log[3] <= $steps || !isset($_log)) && (!isset($_POST['steps']) || $_POST['steps'] == 1)) {
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
        <meta http-equiv=\"content-style-type\" content=\"text/css\" />\n";
        if ((file_exists($o_path."log.txt") && $_log[3] > $steps) || ($_POST['steps'] > 1 && file_exists($_POST['input_files']))) {
		$text .= "<meta http-equiv=\"refresh\" content=\"5;url='".e_PLUGIN."integrity_check\integrity_check.php?".e_QUERY." '\">\n";
	}
        $text .="<script type='text/javascript' src='".e_FILE."e107.js'></script>";
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

//check Version you are using
if(file_exists(e_ADMIN."ver.php")){
	include_once(e_ADMIN."ver.php");
}

//Arrays for replacing Directorys (if non-standard)
$dirs_1 = array($ADMIN_DIRECTORY, $FILES_DIRECTORY, $IMAGES_DIRECTORY, $THEMES_DIRECTORY, $PLUGINS_DIRECTORY, $HANDLERS_DIRECTORY, $LANGUAGES_DIRECTORY, $HELP_DIRECTORY);
$dirs_2 = array("e107_admin/", "e107_files/", "e107_images/", "e107_themes/", "e107_plugins/", "e107_handlers/", "e107_languages/", "e107_docs/help/");

//Files / Dirs never coming into core-sfv
$exclude = array($FILES_DIRECTORY."backend", $FILES_DIRECTORY."downloadimages", $FILES_DIRECTORY."downloads", $FILES_DIRECTORY."downloadthumbs", $FILES_DIRECTORY."images", $FILES_DIRECTORY."misc", $FILES_DIRECTORY."public" , substr($IMAGES_DIRECTORY, 0, strlen($IMAGES_DIRECTORY)-1), $PLUGINS_DIRECTORY."custom", "e107_config.php", "CVS", $PLUGINS_DIRECTORY."integrity_check/crc");

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


if (file_exists("do_core_file.php")) {
	require_once("do_core_file.php");
	if (!function_exists('docorefile')){ $message = "<div align='center'><b>".Integ_39."</b></div>"; }
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

//Check existing sfv-File START
if (IsSet($_POST['docheck']) && $_POST['input_files'] != "") {
	if (file_exists($_POST['input_files'])) {
		$_log = array();
		$_log[2]=lines($_POST['input_files']);
		$steps = intval($_log[2] / $_POST['steps'])+1;
		$_log[3]=$_log[2]-$steps;
		$_log[4]=$_POST['steps'];
		$the_end = check_sfv_file($_POST['input_files'],$_POST['theme_folders'],0,$steps);
		if ($_log[3] > 0) {
			$text = "<div align='center'>".str_replace("{counts}", $_log[3], Integ_38)."<br />".
			Integ_36."<br /><a href=\"".e_PLUGIN."integrity_check/integrity_check.php?".e_QUERY." \">".Integ_37."</a>
			</div>";
			$ns -> tablerender("", "<b>".$text."</b>");
		}
		if ($the_end != 0) {
			$_log[0]=$_POST['input_files'];
			$_log[1]=$_POST['theme_folders'];
			$tmp = addslashes($_log[0].".-.".$_log[1].".-.".$_log[2].".-.".$_log[3].".-.".$_log[4].".-.".$_log[5]);
			$handle = @fopen($o_path."log.txt", "w");
			@fwrite($handle, $tmp);
			@fclose($handle);
			exit;
		}
		elseif ($_log['crc'] || $_log['miss']) {
			$message = "";
			if ($_log['crc']) {
				$message .= "<br />
   				<div align='center'><u>".Integ_04."*</u></div><br /><ul>".
   				$_log['crc']."</ul>";
   			}
   			if ($_log['miss']) {
   				$message .= "<br />
   				<div align='center'><u>".Integ_03."</u></div><br /><ul>".
   				$_log['miss']."</ul>";
   			}
   			if ($_log['crc']) {
   				$message .= Integ_29;
   			}
   		}
   		else {
   			$message = "<br />".Integ_15;
   		}
	}
	else { $message = Integ_05; }
}
if (file_exists($o_path."log.txt")) {
	$the_end = check_sfv_file($_log[0],$_log[1],$_log[5],$steps);
	$_log[3] = $_log[3] - $steps;
	if ($_log[3] > 0) {
		$text = "<div align='center'>".str_replace("{counts}", $_log[3], Integ_38)."<br />".
		Integ_36."<br /><a href=\"".e_PLUGIN."integrity_check/integrity_check.php?".e_QUERY." \">".Integ_37."</a>
		</div>";
		$ns -> tablerender("", "<b>".$text."</b>");
	}
	if ($the_end != 0 && $_log[3] > 0) {
		$tmp = addslashes($_log[0].".-.".$_log[1].".-.".$_log[2].".-.".$_log[3].".-.".$_log[4].".-.".$_log[5]);
		$handle = @fopen($o_path."log.txt", "w");
		@fwrite($handle, $tmp);
		@fclose($handle);
		exit;
	}
	elseif (file_exists($o_path."log_crc.txt") || file_exists($o_path."log_miss.txt")) {
		$message = "";
		if (file_exists($o_path."log_crc.txt")) {
			$message .= "<br />
   			<div align='center'><u>".Integ_04."*</u></div><br /><ul>".
   			file_get_contents($o_path."log_crc.txt")."</ul>";
   		}
   		if (file_exists($o_path."log_miss.txt")) {
   			$message .= "<br />
   			<div align='center'><u>".Integ_03."</u></div><br /><ul>".
   			file_get_contents($o_path."log_miss.txt")."</ul>";
   			@unlink($o_path."log_miss.txt");
   		}
   		if (file_exists($o_path."log_crc.txt")) {
   			$message .= Integ_29;
   			@unlink($o_path."log_crc.txt");
   		}
   		
   	}
   	else {
   		$message = "<br />".Integ_15;
   	}
   	@unlink($o_path."log.txt");
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
$text ="<div style='text-align:center'>
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
if ($err_1 || $err_2 || !is_writeable($o_path)) {
	$err_m = Integ_35."<br />";
	if ($err_1) {
		$err_m .= Integ_32."<br />";
	}
	if ($err_2) {
		$err_m .= Integ_33."<br />";
	}
	if (!is_writeable($o_path)) {
		$err_m .= Integ_34."<br />";
	}
}
else {
	$err_m = "";
}
	
$text .= "<hr>".Integ_30."<br />".($err_m != "" ? $err_m."<br />" : "")."<br />".Integ_31."<select name='steps'>
<option value='1' selected='selected'>1</option>\n";
if (is_writeable($o_path) && !$err_1 && !$err_2) {
	for($i=2;$i<11;$i++){
		$text .= "<option value='".$i."'>".$i."</option>\n";
	}
}

$text .="</select>
</td>
<td style='width:40%' class='forumheader3'>
<input class='button' type='submit' name='docheck' size='20' value='".Integ_08."' />
</td>
</tr>";

if(is_writable($o_path)){
	
	//do_core_file.php only available 4 dev-team sorry guys...
	if (function_exists('docorefile')) { $text .= docorefile(); }
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
$text .= "<br /><br /><br /><a href='".e_SELF."?header'>".Integ_26."</a><br />";	

//Render this fuck
$ns -> tablerender(Integ_13, $text);

//Footer
echo "\n
</td>
</tr>
</table></div>
</body>
</html>";

?>

<script LANGUAGE="JavaScript" type="text/javascript">
<!--
function hex_strReplace(a, b, c){
	return a.split(b).join(c);
}
// -->
</script>
