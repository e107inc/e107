<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/filemanager.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");

if(!getperms("6")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");

$imagedir = e_IMAGE."filemanager/";
$path = str_replace("../", "", (e_QUERY ? e_QUERY : e_FILE));
if(!$path){ $path =  str_replace("../", "", e_FILE); }

if(IsSet($_POST['deletefile'])){
	if(!$_POST['ac'] == md5(ADMINPWCHANGE)){ exit; }
	$destination_file  = e_BASE.$_POST['deleteconfirm'];
	if(@unlink($destination_file)){
		$message = FMLAN_26." '".$destination_file."' ".FMLAN_27.".";
	}else{
		$message = FMLAN_28." '".$destination_file."'.";
	}
}

if(IsSet($_POST['upload'])){
	if(!$_POST['ac'] == md5(ADMINPWCHANGE)){ exit; }
	$pref['upload_storagetype'] = "1";
	require_once(e_HANDLER."upload_handler.php");
	
	$files = $_FILES['file_userfile'];

	


	foreach($files['name'] as $key => $name){
		if($files['size'][$key]){
			$uploaded = file_upload(e_BASE.$_POST['upload_dir'][$key]);
		}
	}
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

if(strpos(e_QUERY, ".")){
	echo "<iframe style=\"width:100%\" src=\"".e_BASE.e_QUERY."\" height=\"300\" scrolling=\"yes\"></iframe><br /><br />";
	if(!strpos(e_QUERY, "/")){
		$path = "";
	}else{
		$path = substr($path, 0, strrpos(substr($path, 0, -1), "/"))."/";
	}	
}

$files=array();
$dirs=array();
$path=explode("?",$path);
$path=$path[0];
$path=explode(".. ",$path);
$path=$path[0];

if($handle = opendir(e_BASE.$path)){
	while (false !== ($file = readdir($handle))){ 
		if($file != "." && $file != ".."){

			if(getenv('windir') && is_file(e_BASE.$path."\\".$file)){
				if(is_file(e_BASE.$path."\\".$file)){
					$files[] = $file;
				}else{
					$dirs[] = $file;
				}
			}else{
				if(is_file(e_BASE.$path."/".$file)){
					$files[] = $file;
				}else{
					$dirs[] = $file;
				}
			}
		}
	}
}
closedir($handle); 

if(count($files) != 0){
	sort($files);
}
if(count($dirs) != 0){
	sort($dirs);
}

if(count($files) == 1){
	$cstr = FMLAN_12;
}else{
	$cstr = FMLAN_13;
}

if(count($dirs) == 1){
	$dstr = FMLAN_14;
}else{
	$dstr = FMLAN_15;
}

$pathd = $path;
$text = "<div class=\"border\">
<div class=\"caption\">
".FMLAN_29.": <b>root/".$pathd."</b>&nbsp;&nbsp;[ ".count($dirs)." ".$dstr.", ".count($files)." ".$cstr." ]
</div>
</div>
<br />
<div style=\"text-align:center\">
<table style=\"width:95%\">

<form ENCTYPE=\"multipart/form-data\" action=\"".e_SELF.(e_QUERY ? "?".e_QUERY : "")."\" method=\"post\">
<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"1000000\">
";

if($path != e_FILE){
	if(substr_count($path, "/") == 1){
		$pathup = e_SELF;
	}else{
		$pathup = e_SELF."?".substr($path, 0, strrpos(substr($path, 0, -1), "/"))."/";
	}
	$text .= "<tr><td colspan=\"5\" class=\"forumheader3\"><a href=\"".$pathup."\"><img src=\"".$imagedir."updir.png\" alt=\"".FMLAN_30."\" style=\"border:0\" /> </a>
	<a href=\"filemanager.php\"><img src=\"".$imagedir."home.png\" alt=\"".FMLAN_16."\" style=\"border:0\" /></a>
	</td>
	</tr>";
}

$text .= "<tr>
<td style=\"width:5%\" class=\"forumheader3\">&nbsp;</td>
<td style=\"width:30%\" class=\"forumheader3\"><b>".FMLAN_17."</b></td>
<td class=\"forumheader3\"><b>".FMLAN_18."</b></td>
<td style=\"width:30%\" class=\"forumheader3\"><b>".FMLAN_19."</b></td>
<td class=\"forumheader3\"><b>".FMLAN_20."</b></td>
</tr>";




$c=0;
while($dirs[$c]){
	$dirsize = dirsize($path.$dirs[$c]);
	$text .= "<tr>
	<td style=\"width:5%\" class=\"forumheader3\" style=\"vertical-align:middle; text-align:center\">
	<a href=\"".e_SELF."?".$path.$dirs[$c]."/\"><img src=\"".$imagedir."folder.png\" alt=\"".$dirs[$c]." ".FMLAN_31."\" style=\"border:0\" /></a>
	<td style=\"width:30%\" class=\"forumheader3\">
	<a href=\"".e_SELF."?".$path.$dirs[$c]."/\">".$dirs[$c]."</a>
	</td>
	<td class=\"forumheader3\">".$dirsize."
	</td>
	<td class=\"forumheader3\">&nbsp;</td>
	<td class=\"forumheader3\">";
	if(FILE_UPLOADS){
		$text .= "<input class=\"button\" type=\"button\" name=\"erquest\" value=\"".FMLAN_21."\" onClick=\"expandit(this)\">
		<div style=\"display:none; &{head}\">
		<input class=\"tbox\" type=\"file\" name=\"file_userfile[]\" size=\"50\"> 
		<input class=\"button\" type=\"submit\" name=\"upload\" value=\"".FMLAN_22."\" />
		<input type=\"hidden\" name=\"upload_dir[]\" value=\"".$path.$dirs[$c]."\">
		</div>";
	}else{
		$text .= "&nbsp;";
	}
	$text .= "</td>
	</tr>
	
	
	";
	$c++;
}

$c=0;
while($files[$c]){
	$img = substr(strrchr($files[$c], "."), 1, 3);
	if(!$img || !eregi("css|exe|gif|htm|jpg|js|php|png|txt|xml|zip", $img)){
		$img = "def";
	}
	$size = parsesize(filesize(e_BASE.$path."/".$files[$c]));
	$text .= "<tr>
	<td style=\"width:5%\" class=\"forumheader3\" style=\"vertical-align:middle; text-align:center\">
	<img src=\"".$imagedir.$img.".png\" alt=\"".$files[$c]."\" style=\"border:0\" /></a>
	<td style=\"width:30%\" class=\"forumheader3\">
	<a href=\"".e_SELF."?".$path.$files[$c]."\">".$files[$c]."</a>
	</td>";
	$text .= "<td style=\"width:10%\" class=\"forumheader3\">".$size."</td>
	<td style=\"width:30%\" class=\"forumheader3\">".date("F j Y, g:i a", filemtime(e_BASE.$path."/".$files[$c]))."</td>
	<td class=\"forumheader3\"><input class=\"button\" type=\"submit\" name=\"deletefile\" value=\"".FMLAN_23."\" />
	<input type=\"checkbox\" name=\"deleteconfirm\" value=\"".$path.$files[$c]."\"><span class=\"smalltext\"> ".FMLAN_24."</span></td>
	</tr>";
	$c++;
}

$text .= "
<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
</form>
</table>
</div>";

$ns -> tablerender("<div style=\"text-align:center\">".FMLAN_25."</div>", $text);

function dirsize($dir){
	$_SERVER["DOCUMENT_ROOT"].e_HTTP.$dir;
	$dh = @opendir($_SERVER["DOCUMENT_ROOT"].e_HTTP.$dir);
	$size = 0;
	while($file = @readdir($dh)){
		if($file != "." and $file != "..") {
			$path = $dir."/".$file;
			if(is_file($_SERVER["DOCUMENT_ROOT"].e_HTTP.$path)){
				$size += filesize($_SERVER["DOCUMENT_ROOT"].e_HTTP.$path);
			}else{
				$size += dirsize($path."/");
			}
		}
	}
	@closedir($dh);
	return parsesize($size);
}

function parsesize($size){
	$kb = 1024;
	$mb = 1024 * $kb;
	$gb = 1024 * $mb;
	$tb = 1024 * $gb;
	if($size < $kb) {
		return $size." b";
	}else if($size < $mb) {
		return round($size/$kb,2)." kb";
	}else if($size < $gb) {
		return round($size/$mb,2)." mb";
	}else if($size < $tb) {
		return round($size/$gb,2)." gb";
	}else {
		return round($size/$tb,2)." tb";
	}
}

require_once("footer.php");
?>	