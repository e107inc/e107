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
|     $Source: /cvs_backup/e107_0.8/e107_admin/filemanager.php,v $
|     $Revision: 1.8 $
|     $Date: 2009-07-07 06:50:55 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("6"))
{
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'filemanage';
require_once("auth.php");
require_once(e_HANDLER.'upload_handler.php');
require_once(e_HANDLER."form_handler.php");
$frm = new e_form();

require_once (e_HANDLER."message_handler.php");
$emessage = &eMessage::getInstance();
//FIXME - messages

$pubfolder = (str_replace("../","",e_QUERY) == str_replace("../","",e_FILE."public/")) ? TRUE : FALSE;


$imagedir = e_IMAGE."filemanager/";

	$dir_options[0] = FMLAN_47;
	$dir_options[1] = FMLAN_35;
	$dir_options[2] = FMLAN_40;


	$adchoice[0] = e_FILE."public/";
	$adchoice[1] = e_FILE;
	$adchoice[2] = e_IMAGE."newspost_images/";


$path = str_replace("../", "", e_QUERY);
if (!$path)
{
	$path = str_replace("../", "", $adchoice[0]);
}

if($path == "/")
{
	$path = $adchoice[0];
	echo "<b>Debug</b> ".$path." <br />";
}


// ===============================================


if (isset($_POST['deleteconfirm']))
{
	$deleteList = array();
	$moveList = array();
	foreach($_POST['deleteconfirm'] as $key=>$delfile)
	{
		// check for delete.
		if (isset($_POST['selectedfile'][$key]) && isset($_POST['deletefiles']))
		{
			if (!$_POST['ac'] == md5(ADMINPWCHANGE))
			{
				exit;
			}
			$destination_file = e_BASE.$delfile;
			if (@unlink($destination_file))
			{
				$message .= FMLAN_26." '".$destination_file."' ".FMLAN_27.".<br />";
				$deleteList[] = $destination_file;
			}
			else
			{
				$message .= FMLAN_28." '".$destination_file."'.<br />";
			}
		}

		// check for move to downloads or downloadimages.
		if (isset($_POST['selectedfile'][$key]) && (isset($_POST['movetodls'])) )
		{
			$newfile = str_replace($path,"",$delfile);

			// Move file to whatever folder.
			if (isset($_POST['movetodls']))
			{
				$newpath = $_POST['movepath'];
				if (rename(e_BASE.$delfile,$newpath.$newfile))
				{
					$message .= FMLAN_38." ".$newpath.$newfile."<br />";
					$moveList[] = e_BASE.$delfile.'=>'.$newpath.$newfile;
				}
				else
				{
					$message .= FMLAN_39." ".$newpath.$newfile."<br />";
					$message .= (!is_writable($newpath)) ? $newpath.LAN_NOTWRITABLE : "";
				}
			}
		}
	}
	if (count($deleteList))
	{
		$admin_log->log_event('FILEMAN_01',implode('[!br!]',$deleteList),E_LOG_INFORMATIVE,'');
	}
	if (count($moveList))
	{
		$admin_log->log_event('FILEMAN_02',implode('[!br!]',$moveList),E_LOG_INFORMATIVE,'');
	}
}



if (isset($_POST['upload']))
{
	if (!$_POST['ac'] == md5(ADMINPWCHANGE))
	{
		exit;
	}
	$uploadList = array();
	$pref['upload_storagetype'] = "1";
	require_once(e_HANDLER."upload_handler.php");
	$files = $_FILES['file_userfile'];
	foreach($files['name'] as $key => $name)
	{
		if ($files['size'][$key])
		{
			$uploaded = file_upload(e_BASE.$_POST['upload_dir'][$key]);
			$uploadList[] = $_POST['upload_dir'][$key].$uploaded[0]['name'];
		}
	}
	if (count($uploadList))
	{
		$admin_log->log_event('FILEMAN_03',implode('[!br!]',$uploadList),E_LOG_INFORMATIVE,'');
	}
}



if (isset($message))
{
	$ns->tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}



if (strpos(e_QUERY, ".") && !is_dir(realpath(e_BASE.$path)))
{
	echo "
	<div>
		<iframe style='width:99%' src='".e_BASE.e_QUERY."' height='300' scrolling='yes'>asdas</iframe>
	</div>
	";
	if (!strpos(e_QUERY, "/"))
	{
		$path = "";
	}
	else
	{
		$path = substr($path, 0, strrpos(substr($path, 0, -1), "/"))."/";
	}
}



$files = array();
$dirs = array();
$path = explode("?", $path);
$path = $path[0];
$path = explode(".. ", $path);
$path = $path[0];

if ($handle = opendir(e_BASE.$path))
{
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

$text = "
	<form method='post' action='".e_SELF."?".e_QUERY."'>
	<div class='buttons-bar left'>
	".FMLAN_32."
	<select name='admin_choice' class='tbox' onchange=\"location.href=this.options[selectedIndex].value\">";


	foreach($dir_options as $key=>$opt){
		$select = (str_replace("../","",$adchoice[$key]) == e_QUERY) ? "selected='selected'" : "";
		$text .= "<option value='".e_SELF."?".str_replace("../","",$adchoice[$key])."' $select>".$opt."</option>";
	}

$text .= "</select>
	</div>
	</form>
";
// $ns->tablerender(FMLAN_34, $text);


// Get largest allowable file upload
$max_file_size = get_user_max_upload();

if ($path != e_FILE) {
	if (substr_count($path, "/") == 1) {
		//$pathup = e_SELF;
		$pathup = '';
	} else {

		$pathup = "<a class='action' href='".e_SELF."?".substr($path, 0, strrpos(substr($path, 0, -1), "/"))."/'><img class='icon S24' src='".$imagedir."updir.png' alt='".FMLAN_30."' /></a>";
	}
}


$text .= "
	<form enctype='multipart/form-data' action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."' method='post'>
		<fieldset id='core-filemanager'>
			<legend class='e-hideme'>XX</legend>
			<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='4'>
					<col style='width:  5%'></col>
					<col style='width: 40%'></col>
					<col style='width: 20%'></col>
					<col style='width: 15%'></col>
				</colgroup>
				<thead>
					<tr>
						<th class='center'>
							".$pathup."
						<!-- <a href='filemanager.php'><img src='".$imagedir."home.png' alt='".FMLAN_16."' /></a> -->
							<input type='hidden' name='MAX_FILE_SIZE' value='{$max_file_size}' />
						</th>
						<th class='center'>".FMLAN_17."</th>
						<th class='center'>".FMLAN_18."</th>
						<th class='center'>".FMLAN_19."</th>
					</tr>
				</thead>
				<tbody>
";




$c = 0;
while ($dirs[$c]) {
	$dirsize = dirsize($path.$dirs[$c]);
	$el_id = str_replace(array('/','_',' ','\\'),'-',$path.$dirs[$c]);

	if (FILE_UPLOADS && is_writable(e_BASE.$path.$dirs[$c])) {
		$up_button = $frm->admin_button('erquest', FMLAN_21, 'action', '', array('id'=>false, 'other'=>"onclick='e107Helper.toggle(\"{$el_id}\")'"));
	} else {
		$up_button = "&nbsp;leave_32.png";
	}
	//FIXME - upload link not working, raplace with image
	$text .= "
					<tr>
						<td class='center middle'><a href='#{$el_id}' class='e-expandit'>upload</a></td>
						<td>
							<a class='action' href='".e_SELF."?".$path.$dirs[$c]."/'><img class='icon action S16' src='".$imagedir."folder.png' alt='".$dirs[$c]." ".FMLAN_31."' style='border:0' /></a>
							<a href='".e_SELF."?".$path.$dirs[$c]."/'>".$dirs[$c]."</a>
							<div class='e-hideme' id='{$el_id}'>
								<div class='field-spacer'>".$frm->file('file_userfile[]', array('id'=>false, 'size'=>'20')).$frm->admin_button('upload', FMLAN_22, '', '', array('id'=>false))."</div>
								<input type='hidden' name='upload_dir[]' value='".$path.$dirs[$c]."' />
							</div>
						</td>
						<td class='right'>".$dirsize."</td>
						<td class='right'>&nbsp;</td>
					</tr>
	";
	$c++;
}

$c = 0;
while ($files[$c]) {
	$img = substr(strrchr($files[$c], "."), 1, 3);
	if (!$img || !preg_match("/css|exe|gif|htm|jpg|js|php|png|txt|xml|zip/i", $img)) {
		$img = "def";
	}
	$size = $e107->parseMemorySize(filesize(e_BASE.$path."/".$files[$c]));
	$gen = new convert;
	$filedate = $gen -> convert_date(filemtime(e_BASE.$path."/".$files[$c]), "forum");

	$text .= "
					<tr>
						<td class='center middle autocheck'>
							".$frm->checkbox("selectedfile[$c]", 1, false, array('id'=>false))."
							<input type='hidden' name='deleteconfirm[$c]' value='".$path.$files[$c]."' />
						</td>
						<td>
							<img class='icon' src='".$imagedir.$img.".png' alt='".$files[$c]."' />
							<a href='".e_SELF."?".$path.$files[$c]."'>".$files[$c]."</a>
						</td>
						<td class='right'>".$size."</td>
						<td class='right'>".$filedate."</td>
					</tr>
	";
	$c++;
}

	$text .= "
				</tbody>
			</table>
			<div class='buttons-bar left'>
				".$frm->admin_button('check_all', 'jstarget:selectedfile', 'action', LAN_CHECKALL, array('id'=>false))."
				".$frm->admin_button('uncheck_all', 'jstarget:selectedfile', 'action', LAN_UNCHECKALL, array('id'=>false))."
	";

	if ($pubfolder || e_QUERY == ""){
        require_once(e_HANDLER."file_class.php");
		$fl = new e_file;
		$dl_dirlist = $fl->get_dirs(e_DOWNLOAD);
		$movechoice = array();
        $movechoice[] = e_DOWNLOAD;
		foreach($dl_dirlist as $dirs){
        	$movechoice[] = e_DOWNLOAD.$dirs."/";
		}
		sort($movechoice);
		$movechoice[] = e_FILE."downloadimages/";
		if(e_QUERY != str_replace("../","",e_FILE."public/")){
        	$movechoice[] = e_FILE."public/";
		}
		if(e_QUERY != str_replace("../","",e_FILE."downloadthumbs/")){
        	$movechoice[] = e_FILE."downloadthumbs/";
		}
		if(e_QUERY != str_replace("../","",e_FILE."misc/")){
        	$movechoice[] = e_FILE."misc/";
		}
		if(e_QUERY != str_replace("../","",e_IMAGE)){
        	$movechoice[] = e_IMAGE;
		}
		if(e_QUERY != str_replace("../","",e_IMAGE."newspost_images/")){
        	$movechoice[] = e_IMAGE."newspost_images/";
		}



		//FIXME - form elements
        $text .= FMLAN_48."&nbsp;<select class='tbox' name='movepath'>\n";
        foreach($movechoice as $paths){
        	$text .= "<option value='$paths'>".str_replace("../","",$paths)."</option>\n";
		}
		$text .= "</select>".$frm->admin_button('movetodls', FMLAN_50, 'move', '', array('other' => "onclick=\"return e107Helper.confirm('".$tp->toJS(FMLAN_49)."') \""));
	}

	$text .= "
				".$frm->admin_button('deletefiles', FMLAN_43, 'delete', '', array('title' => $tp->toJS(FMLAN_46)))."
				<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
			</div>
		</fieldset>
	</form>
	";

$ns->tablerender(FMLAN_29.": <b>root/".$pathd."</b>&nbsp;&nbsp;[ ".count($dirs)." ".$dstr.", ".count($files)." ".$cstr." ]", $text);


function dirsize($dir)
{
	global $e107;
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
	return $e107->parseMemorySize($size);
}


require_once("footer.php");
/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
	require_once(e_HANDLER.'js_helper.php');
	$ret = "
		<script type='text/javascript'>
			//add required core lan - delete confirm message
			(".e_jshelper::toString(LAN_JSCONFIRM).").addModLan('core', 'delete_confirm');
			if(typeof e107Admin == 'undefined') var e107Admin = {}

			/**
			 * OnLoad Init Control
			 */
			e107Admin.initRules = {
				'Helper': true,
				'AdminMenu': false
			}
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

	return $ret;
}
?>