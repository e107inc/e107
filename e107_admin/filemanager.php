<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_admin/filemanager.php,v $
|     $Revision: 1.8 $
|     $Date: 2005-02-13 00:39:55 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("6")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'filemanage';
require_once("auth.php");

if(str_replace("../","",e_QUERY) == str_replace("../","",e_FILE."public/")){
	$pubfolder = TRUE;
}else{
	$pubfolder = FALSE;
}

$imagedir = e_IMAGE."filemanager/";

$choice .= e_FILE;

$admin_choice = $_POST['admin_choice'];

if ($admin_choice == "Custom" && getperms("I")) {
	$choice = e_PLUGIN."custom/";
}
if ($admin_choice == "Custompages" && getperms("I")) {
	$choice = e_PLUGIN."custompages/";
}

if ($admin_choice == "newsimages" && getperms("I")) {
	$choice = e_IMAGE."newspost_images/";
}

if (isset($_POST['admin_choice'])) {
	header("location:".e_SELF."?".$choice);
	 exit;
}

$path = str_replace("../", "", (e_QUERY ? e_QUERY : $choice));
if (!$path) {
	$path = str_replace("../", "", $choice);
}

foreach($_POST['deleteconfirm'] as $key=>$delfile){
	// check for delete.
	if (isset($_POST['selectedfile'][$key]) && isset($_POST['deletefiles'])) {
		if (!$_POST['ac'] == md5(ADMINPWCHANGE)) {
			exit;
		}
		$destination_file = e_BASE.$delfile;
		if (@unlink($destination_file)) {
			$message .= FMLAN_26." '".$destination_file."' ".FMLAN_27.".<br />";
		} else {
			$message .= FMLAN_28." '".$destination_file."'.<br />";
		}
	}

	// check for move to downloads or downloadimages.
	if (isset($_POST['selectedfile'][$key]) && (isset($_POST['movetodls'])|| isset($_POST['movetodlimages'])) ){
	$newfile = str_replace($path,"",$delfile);

	// downloads folder
		if (isset($_POST['movetodls'])){
			if (preg_match("#^/#",$DOWNLOADS_DIRECTORY) || preg_match("#.:#",$DOWNLOADS_DIRECTORY)){
				$newpath = $DOWNLOADS_DIRECTORY.$newfile;
			} else {
				$newpath = e_BASE.$DOWNLOADS_DIRECTORY.$newfile;
			}
		}
	// download images folder.
		if (isset($_POST['movetodlimages'])){
			$newpath = e_FILE."downloadimages/".$newfile;
		}

		if (rename(e_BASE.$delfile,$newpath)){
			$message .= FMLAN_38." ".$newpath."<br />";
		} else {
			$message .= FMLAN_39." ".$newpath."<br />";
		}
	}


}



if (isset($_POST['upload'])) {
	if (!$_POST['ac'] == md5(ADMINPWCHANGE)) {
		exit;
	}
	$pref['upload_storagetype'] = "1";
	require_once(e_HANDLER."upload_handler.php");

	$files = $_FILES['file_userfile'];




	foreach($files['name'] as $key => $name) {
		if ($files['size'][$key]) {
			$uploaded = file_upload(e_BASE.$_POST['upload_dir'][$key]);
		}
	}
}

if (isset($message)) {
	$ns->tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

if (strpos(e_QUERY, ".")) {
	echo "<iframe style=\"width:100%\" src=\"".e_BASE.e_QUERY."\" height=\"300\" scrolling=\"yes\"></iframe><br /><br />";
	if (!strpos(e_QUERY, "/")) {
		$path = "";
	} else {
		$path = substr($path, 0, strrpos(substr($path, 0, -1), "/"))."/";
	}
}

$files = array();
$dirs = array();
$path = explode("?", $path);
$path = $path[0];
$path = explode(".. ", $path);
$path = $path[0];

if ($handle = opendir(e_BASE.$path)) {
	while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != "..") {

			if (getenv('windir') && is_file(e_BASE.$path."\\".$file)) {
				if (is_file(e_BASE.$path."\\".$file)) {
					$files[] = $file;
				} else {
					$dirs[] = $file;
				}
			} else {
				if (is_file(e_BASE.$path."/".$file)) {
					$files[] = $file;
				} else {
					$dirs[] = $file;
				}
			}
		}
	}
}
closedir($handle);

if (count($files) != 0) {
	sort($files);
}
if (count($dirs) != 0) {
	sort($dirs);
}

if (count($files) == 1) {
	$cstr = FMLAN_12;
} else {
	$cstr = FMLAN_13;
}

if (count($dirs) == 1) {
	$dstr = FMLAN_14;
} else {
	$dstr = FMLAN_15;
}

$pathd = $path;

$text = "<div style='text-align:center'>\n
	<form method='post' action='".e_SELF."?".e_QUERY."'>\n
	<table style='".ADMIN_WIDTH."' class='fborder'>\n
	<tr>\n\n

	<td style='width:70%' class='forumheader3'>\n
	".FMLAN_32."
	</td>\n
	<td class='forumheader3' style='text-align:center; width:30%'>\n
	<select name='admin_choice' class='tbox'>\n";
($admin_choice == "Files" ? $text .= "<option value='Files' selected='selected'>".FMLAN_35."</option>" : $text .= "<option value='Files'>".FMLAN_35."</option>");
if (getperms("I")) {
	($admin_choice == "Custom" ? $text .= "<option value='Custom' selected='selected'>".FMLAN_36."</option>" : $text .= "<option value='Custom'>".FMLAN_36."</option>");
	($admin_choice == "Custompages" ? $text .= "<option value='Custompages' selected='selected'>".FMLAN_37."</option>" : $text .= "<option value='Custompages'>".FMLAN_37."</option>");
	($admin_choice == "newsimages" ? $text .= "<option value='newsimages' selected='selected'>".FMLAN_40."</option>" : $text .= "<option value='newsimages'>".FMLAN_40."</option>");
}
$text .= "</select>\n
	</td>\n
	</tr>\n\n

	<tr style='vertical-align:top'>\n
	<td colspan='2'  style='text-align:center' class='forumheader'>\n
	<input class='button' type='submit' name='updateoptions' value='".FMLAN_33."' />\n
	</td>\n
	</tr>\n\n

	</table>\n
	</form>\n
	</div>";
$ns->tablerender(FMLAN_34, $text);


$text = "<form enctype=\"multipart/form-data\" action=\"".e_SELF.(e_QUERY ? "?".e_QUERY : "")."\" method=\"post\">
	<div style=\"text-align:center\">
	<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"1000000\" />
	<table class='fborder' style=\"".ADMIN_WIDTH."\">";

$text .= "<tr>
	<td style=\"width:5%\" class=\"fcaption\">&nbsp;</td>
	<td style=\"width:30%\" class=\"fcaption\"><b>".FMLAN_17."</b></td>
	<td class=\"fcaption\"><b>".FMLAN_18."</b></td>
	<td style=\"width:30%\" class=\"fcaption\"><b>".FMLAN_19."</b></td>
	<td class=\"fcaption\"><b>".FMLAN_20."</b></td>
	</tr>";

if ($path != e_FILE) {
	if (substr_count($path, "/") == 1) {
		$pathup = e_SELF;
	} else {
		$pathup = e_SELF."?".substr($path, 0, strrpos(substr($path, 0, -1), "/"))."/";
	}
	$text .= "<tr><td colspan=\"5\" class=\"forumheader3\"><a href=\"".$pathup."\"><img src=\"".$imagedir."updir.png\" alt=\"".FMLAN_30."\" style=\"border:0\" /> </a>
		<a href=\"filemanager.php\"><img src=\"".$imagedir."home.png\" alt=\"".FMLAN_16."\" style=\"border:0\" /></a>
		</td>
		</tr>";
}

$c = 0;
while ($dirs[$c]) {
	$dirsize = dirsize($path.$dirs[$c]);
	$text .= "<tr>
		<td class=\"forumheader3\" style=\"vertical-align:middle; text-align:center; width:5%\">
		<a href=\"".e_SELF."?".$path.$dirs[$c]."/\"><img src=\"".$imagedir."folder.png\" alt=\"".$dirs[$c]." ".FMLAN_31."\" style=\"border:0\" /></a>
		</td>
		<td style=\"width:30%\" class=\"forumheader3\">
		<a href=\"".e_SELF."?".$path.$dirs[$c]."/\">".$dirs[$c]."</a>
		</td>
		<td class=\"forumheader3\">".$dirsize."
		</td>
		<td class=\"forumheader3\">&nbsp;</td>
		<td class=\"forumheader3\">";
	if (FILE_UPLOADS) {
		$text .= "<input class=\"button\" type=\"button\" name=\"erquest\" value=\"".FMLAN_21."\" onclick=\"expandit(this)\" />
			<div style=\"display:none;\">
			<input class=\"tbox\" type=\"file\" name=\"file_userfile[]\" size=\"50\" />
			<input class=\"button\" type=\"submit\" name=\"upload\" value=\"".FMLAN_22."\" />
			<input type=\"hidden\" name=\"upload_dir[]\" value=\"".$path.$dirs[$c]."\" />
			</div>";
	} else {
		$text .= "&nbsp;";
	}
	$text .= "</td>
		</tr>


		";
	$c++;
}

$c = 0;
while ($files[$c]) {
	$img = substr(strrchr($files[$c], "."), 1, 3);
	if (!$img || !eregi("css|exe|gif|htm|jpg|js|php|png|txt|xml|zip", $img)) {
		$img = "def";
	}
	$size = parsesize(filesize(e_BASE.$path."/".$files[$c]));
	$text .= "<tr>
		<td class=\"forumheader3\" style=\"vertical-align:middle; text-align:center; width:5%\">
		<img src=\"".$imagedir.$img.".png\" alt=\"".$files[$c]."\" style=\"border:0\" />
		</td>
		<td style=\"width:30%\" class=\"forumheader3\">
		<a href=\"".e_SELF."?".$path.$files[$c]."\">".$files[$c]."</a>
		</td>";
	$text .= "<td style=\"width:10%\" class=\"forumheader3\">".$size."</td>
		<td style=\"width:30%\" class=\"forumheader3\">".date("F j Y, g:i a", filemtime(e_BASE.$path."/".$files[$c]))."</td>
		<td class=\"forumheader3\">";

	$text .= "<input  type=\"checkbox\" name=\"selectedfile[$c]\" value=\"1\" />";
	$text .="<input type=\"hidden\" name=\"deleteconfirm[$c]\" value=\"".$path.$files[$c]."\" />";

	$text .="</td>
		</tr>";
	$c++;
}

	$text .= "<tr><td colspan='5' class='forumheader' style='text-align:right'>";

	if ($pubfolder){
		$text .="<input class=\"button\" type=\"submit\" name=\"movetodls\" value=\"".FMLAN_41."\" onclick=\"return jsconfirm('".$tp->toJS(FMLAN_44)."') \" />
		<input class=\"button\" type=\"submit\" name=\"movetodlimages\" value=\"".FMLAN_42."\" onclick=\"return jsconfirm('".$tp->toJS(FMLAN_45)."') \" />";
	}

	$text .= "<input class=\"button\" type=\"submit\" name=\"deletefiles\" value=\"".FMLAN_43."\" onclick=\"return jsconfirm('".$tp->toJS(FMLAN_46)."') \" />
		</td></tr></table>
		<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
		</div>
		</form>";

$ns->tablerender(FMLAN_29.": <b>root/".$pathd."</b>&nbsp;&nbsp;[ ".count($dirs)." ".$dstr.", ".count($files)." ".$cstr." ]", $text);

function dirsize($dir) {
	$_SERVER["DOCUMENT_ROOT"].e_HTTP.$dir;
	$dh = @opendir($_SERVER["DOCUMENT_ROOT"].e_HTTP.$dir);
	$size = 0;
	while ($file = @readdir($dh)) {
		if ($file != "." and $file != "..") {
			$path = $dir."/".$file;
			if (is_file($_SERVER["DOCUMENT_ROOT"].e_HTTP.$path)) {
				$size += filesize($_SERVER["DOCUMENT_ROOT"].e_HTTP.$path);
			} else {
				$size += dirsize($path."/");
			}
		}
	}
	@closedir($dh);
	return parsesize($size);
}

function parsesize($size) {
	$kb = 1024;
	$mb = 1024 * $kb;
	$gb = 1024 * $mb;
	$tb = 1024 * $gb;
	if ($size < $kb) {
		return $size." b";
	}
	else if($size < $mb) {
		return round($size/$kb, 2)." kb";
	}
	else if($size < $gb) {
		return round($size/$mb, 2)." mb";
	}
	else if($size < $tb) {
		return round($size/$gb, 2)." gb";
	} else {
		return round($size/$tb, 2)." tb";
	}
}

require_once("footer.php");
?>