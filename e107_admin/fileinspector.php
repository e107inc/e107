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
|     $Source: /cvs_backup/e107_0.7/e107_admin/fileinspector.php,v $
|     $Revision: 1.10 $
|     $Date: 2005-05-19 09:50:05 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once('../class2.php');
if (!getperms('Y')) {
	header('location:'.e_BASE.'index.php');
	exit;
}

$e_sub_cat = 'fileinspector';

require_once('auth.php');
$DOCS_DIRECTORY = str_replace('help/', '', $HELP_DIRECTORY);
require_once('core_image.php');
$fi = new file_inspector;
require_once(e_HANDLER.'form_handler.php');
$rs = new form;

if (e_QUERY == 'snapshot') {
	$fi -> snapshot_interface();
} else if (e_QUERY == 'results') {
	$fi -> scan_results();
	$fi -> scan_config();
} else {
	$fi -> scan_config();
}

class file_inspector {
	
	var $root_dir;
	var $files_text = array();
	var $image = array();
	var $parent;
	var $count = array();
	
	function file_inspector() {
		$this -> root_dir = $_SERVER['DOCUMENT_ROOT'].e_HTTP;
		if (substr($this -> root_dir, -1) == '/') {
			$this -> root_dir = substr($this -> root_dir, 0, -1);
		}
		if ($_POST['display'] == '3') {
			$_POST['integrity'] = TRUE;
		}
	}

	function inspect($dir, $level, &$tree_end, &$tree_open) {
		global $core_image;
		unset ($text);
		unset ($childOut);
		$dir_id = dechex(crc32($dir));
		$fid = '.';
		$this -> files_text[$dir_id][$fid] = "<tr><td class='f' style='padding-left: 4px' ".($level ? "onclick=\"sh('f_".$this -> parent['id']."')\"" : "").">
		<img src='".e_IMAGE."fileinspector/".($level ? "folder_up.png" : "folder_root.png")."' class='i' alt='' />".($level ? "&nbsp;.." : "")."</td>
		<td class='s' style='text-align: right; padding-right: 4px' onclick=\"sh('initial')\">
		<img src='".e_IMAGE."fileinspector/close.png' class='i' alt='' /></td></tr>";
		
		$directory = $level ? basename($dir) : SITENAME;
		$level++;
		$handle = opendir($dir);
		while (false !== ($readdir = readdir($handle))) {
			if ($readdir != '.' && $readdir != '..' && $readdir != '/' && $readdir != 'CVS' && $readdir != 'Thumbs.db' && (strpos('._', $readdir) === FALSE)) {
				$this -> parent = array('id' => $dir_id, 'name' => $directory);
				$path = $dir.'/'.$readdir;
				$i_path = str_replace($this -> root_dir.'/', '', $path);
				if (is_dir($path)) {
					$child_open = false;
					$child_end = true;
					$childOut .= $this -> inspect($path, $level, $child_end, $child_open);
					$tree_end = false;
					if ($child_open == 'warning' || $child_open == 'unknown') {
						$parent_open = TRUE;
					}
				} else {
					if ($_POST['display'] == '0' || ($_POST['display'] == '3' && isset($core_image[$i_path]) && $readdir != 'core_image.php' && $this -> checksum($path) != $core_image[$i_path]) || ($_POST['display'] == '1' && isset($core_image[$i_path])) || ($_POST['display'] == '2' && !isset($core_image[$i_path]))) {
						$fid = strtolower($readdir);
						$filesize = filesize($path);
						$size = $this -> parsesize($filesize);
						if (isset($core_image[$i_path])) {
							$this -> count['core']['num']++;
							$this -> count['core']['size'] += $filesize;
						}
						$this -> files_text[$dir_id][$fid] .= "<tr><td class='f'>";
						if ($_POST['display'] != '3' && !isset($core_image[$i_path])) {
							$this -> count['unknown']['num']++;
							$this -> count['unknown']['size'] += $filesize;
							$file_icon = 'file_unknown.png';
							$tree_open = ($tree_open == 'warning') ? 'warning' : 'unknown';
						} else if ($_POST['display'] != '3' && !$_POST['integrity']) {
							$file_icon = 'file_core.png';
							$tree_open = ($tree_open == 'unknown') ? 'unknown' : 'core';
						} else if ($readdir != 'core_image.php') {
							if ($_POST['display'] == '3' || $this -> checksum($path) != $core_image[$i_path]) {
								$this -> count['fail']['num']++;
								$this -> count['fail']['size'] += $filesize;
								$file_icon = 'file_warning.png';
								$tree_open = 'warning';
							} else {
								$this -> count['pass']['num']++;
								$this -> count['pass']['size'] += $filesize;
								$file_icon = 'file_check.png';
								$tree_open = ($tree_open == 'warning' || $tree_open == 'unknown') ? $tree_open : 'check';
							}
						}
						$this -> files_text[$dir_id][$fid] .= "<img src='".e_IMAGE."fileinspector/".$file_icon."' class='i' alt='' />&nbsp;".$readdir."&nbsp;</td><td class='s'>".$size."</td></tr>";
					}
				}
			}
		}

		if ($tree_open == 'warning') {
			$dir_icon = 'folder_warning.png';
		} else if ($tree_open == 'unknown') {
			$dir_icon = 'folder_unknown.png';
		} else if ($tree_open == 'core') {
			$dir_icon = 'folder_core.png';
		} else if ($tree_open == 'check') {
			$dir_icon = 'folder_check.png';
		} else if ($tree_open == 'core') {
			$dir_icon = 'folder_core.png';
		} else {
			$dir_icon = 'folder.png';
		}
		$icon = "<img src='".e_IMAGE."fileinspector/".$dir_icon."' class='i' alt='' />";
		$hide = ($parent_open && $tree_open != 'core') ? "" : "style='display: none'";
		$text .= "<div class='d' style='margin-left: ".($level * 8)."px'>";
		$text .= $tree_end ? "<img src='".e_IMAGE."fileinspector/blank.png' class='e' alt='' />" : "<span onclick=\"expandit('d_".$dir_id."')\"><img src='".e_IMAGE."fileinspector/expand.png' class='e' alt='' /></span>";
		$text .= $tree_end ? "&nbsp;<span onclick=\"sh('f_".$dir_id."')\">".$icon."&nbsp;".$directory."</span>" : "&nbsp;<span onclick=\"sh('f_".$dir_id."')\">".$icon."&nbsp;".$directory."</span>";
		$text .= $tree_end ? "" : "<div ".$hide." id='d_".$dir_id."'>".$childOut."</div>";
		$text .= "</div>";

		return $text;
		closedir($handle);
	}
	
	function scan_results() {
		global $ns, $rs;
		$text = "<script type=\"text/javascript\">
		<!--
		var hideid=\"initial\";
		function sh(showid){
			if (hideid!=showid){
				show=document.getElementById(showid).style;
				hide=document.getElementById(hideid).style;
				show.display=\"\";
				hide.display=\"none\";
				hideid = showid;
			}
		}
		//-->
		</script>

		<div style='text-align:center'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='fcaption' colspan='2'>Scan Results</td>
		</tr>";

		$scan_text = $this -> inspect($this -> root_dir, 0);

		$text .= "<tr style='display: none'><td style='width:50%'></td><td style='width:50%'></td></tr>";
		
		$text .= "<tr>
		<td class='forumheader3' style='width:50%'>
		<div style='height: 300px; overflow: auto'>
		".$scan_text."
		</div>
		</td>
		<td class='forumheader3' style='width:50%; vertical-align: top'><div style='height: 300px; overflow: auto'>";

		$text .= "<table class='t' id='initial'>
		<tr><td class='f' style='padding-left: 4px'>
		<img src='".e_IMAGE."fileinspector/fileinspector.png' class='i' alt='' />&nbsp;<b>Overview</b></td>
		<td class='s' style='text-align: right; padding-right: 4px' onclick=\"sh('f_".dechex(crc32($this -> root_dir))."')\">
		<img src='".e_IMAGE."fileinspector/forward.png' class='i' alt='' /></td></tr>";
		if ($_POST['display'] != '3' && $_POST['display'] != '2') {
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_core.png' class='i' alt='' />&nbsp;Core files scanned:&nbsp;".($this -> count['core']['num'] ? $this -> count['core']['num'] : 'none')."&nbsp;</td><td class='s'>".$this -> parsesize($this -> count['core']['size'], 2)."</td></tr>";
		}
		if ($_POST['display'] != '3' && $_POST['display'] != '1') {
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_unknown.png' class='i' alt='' />&nbsp;Non core files scanned:&nbsp;".($this -> count['unknown']['num'] ? $this -> count['unknown']['num'] : 'none')."&nbsp;</td><td class='s'>".$this -> parsesize($this -> count['unknown']['size'], 2)."</td></tr>";
		}
		if ($_POST['display'] == '0') {
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file.png' class='i' alt='' />&nbsp;Total files scanned:&nbsp;".($this -> count['core']['num'] + $this -> count['unknown']['num'])."&nbsp;</td><td class='s'>".$this -> parsesize($this -> count['core']['size'] + $this -> count['unknown']['size'], 2)."</td></tr>";
		}
		if ($_POST['integrity']) {
			$integrity_icon = $this -> count['fail']['num'] ? 'integrity_fail.png' : 'integrity_pass.png';
			$integrity_text = $this -> count['fail']['num'] ? '( '.$this -> count['fail']['num'].' files failed )' : '( All files passed )';
			$text .= "<tr><td colspan='2'>&nbsp;</td></tr>";
			$text .= "<tr><td class='f' style='padding-left: 4px' colspan='2'>
			<img src='".e_IMAGE."fileinspector/".$integrity_icon."' class='i' alt='' />&nbsp;<b>Integrity Check</b> ".$integrity_text."</td></tr>";
		
			if ($_POST['display'] != '3' && $_POST['display'] != '2' && $_POST['integrity']) {
				$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_check.png' class='i' alt='' />&nbsp;Core files passed:&nbsp;".($this -> count['pass']['num'] ? $this -> count['pass']['num'] : 'none')."&nbsp;</td><td class='s'>".$this -> parsesize($this -> count['pass']['size'], 2)."</td></tr>";
			}
			if ($_POST['display'] != '2' && $_POST['integrity']) {
				$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_warning.png' class='i' alt='' />&nbsp;Core files failed:&nbsp;".($this -> count['fail']['num'] ? $this -> count['fail']['num'] : 'none')."&nbsp;</td><td class='s'>".$this -> parsesize($this -> count['fail']['size'], 2)."</td></tr>";
			}
		
			$text .= "<tr><td colspan='2'>&nbsp;</td></tr>";

			$text .= "<tr><td class='f' colspan='2'><img src='".e_IMAGE."fileinspector/info.png' class='i' alt='' />&nbsp;Possible reasons for files to fail:&nbsp;</td></tr>";

			$text .= "<tr><td style='padding-right: 4px' colspan='2'>
			<ul><li>
			<a href=\"javascript: expandit('i_corrupt')\">The file is corrupted...</a><div style='display: none' id='i_corrupt'>
			This could be for a number of reasons such as the file being corrupted in the zip, got corrupted during 
			extraction or got corrupted during file upload via FTP. You should try reuploading the file to your server 
			and re-run the scan to see if this resolves the error.<br /><br /></div>
			</li><li>
			<a href=\"javascript: expandit('i_date')\">The file is out of date...</a><div style='display: none' id='i_date'>If the file is from an older release of e107 to the version you are 
			running then it will fail the integrity check. Make sure you have uploaded the newest version of this file.<br /><br /></div>
			</li><li>
			<a href=\"javascript: expandit('i_edit')\">The file has been edited...</a><div style='display: none' id='i_edit'>If you have edited this file in anyway it will not pass the integrity check. If you 
			intentionally edited this file then you need not worry and can ignore this integrity check fail. If however 
			the file was edited by someone else without authorisation you may want to re-upload the proper version of 
			this file from the e107 zip.<br /><br /></div>
			</li><li>
			<a href=\"javascript: expandit('i_cvs')\">If you are a CVS user...</a><div style='display: none' id='i_cvs'>If you run checkouts of the e107 CVS on your site instead of the official e107 stable 
			releases, then you will discover files have failed integrity check because they have been edited by a dev 
			after the latest core image snapshot was created.<br /><br /></div>
			</li></ul>
			</td></tr>";
		}

		$text .= "</table>";
		
		foreach ($this -> files_text as $dir_id => $stext) {
			ksort($stext);
			$text .= "<table class='t' style='display: none' id='f_".$dir_id."'>";
			foreach ($stext as $key => $stext2) {
				$text .= $stext2;
			}
			$text .= "</table>";
		}

		$text .= "</div></td></tr>";
		
		$text .= "</table>
		</div><br />";

		$ns -> tablerender('Scanning...', $text);
	}
	
	function image_scan($dir) {
		$handle = opendir($dir);
		while (false !== ($readdir = readdir($handle))) {
			if ($readdir != '.' && $readdir != '..' && $readdir != '/' && $readdir != 'CVS' && $readdir != 'Thumbs.db' && (strpos('._', $readdir) === FALSE)) {
				$path = $dir.'/'.$readdir;
				if (is_dir($path)) {
					$this -> image_scan($path);
				} else {
					$this -> image[$path] = $this -> checksum($path, TRUE);
				}
			}
		}
		return FALSE;
		closedir($handle);
	}
	
	function create_image($dir) {
		global $ADMIN_DIRECTORY, $FILES_DIRECTORY, $IMAGES_DIRECTORY, $THEMES_DIRECTORY, $PLUGINS_DIRECTORY, $HANDLERS_DIRECTORY, $LANGUAGES_DIRECTORY, $HELP_DIRECTORY, $DOWNLOADS_DIRECTORY, $DOCS_DIRECTORY;
		$this -> image_scan($dir);
		$data = "<?php\n";
		$data .= "\$core_image = array(
		";
		foreach($this -> image as $path_key => $path_value) {
			$root = str_replace($dir."/", "'", $path_key);
			$search = array("'".$ADMIN_DIRECTORY, "'".$FILES_DIRECTORY, "'".$IMAGES_DIRECTORY, "'".$THEMES_DIRECTORY, "'".$PLUGINS_DIRECTORY, "'".$HANDLERS_DIRECTORY, "'".$LANGUAGES_DIRECTORY, "'".$HELP_DIRECTORY, "'".$DOWNLOADS_DIRECTORY, "'".$DOCS_DIRECTORY);
			$replace = array("\$ADMIN_DIRECTORY.'", "\$FILES_DIRECTORY.'", "\$IMAGES_DIRECTORY.'", "\$THEMES_DIRECTORY.'", "\$PLUGINS_DIRECTORY.'", "\$HANDLERS_DIRECTORY.'", "\$LANGUAGES_DIRECTORY.'", "\$HELP_DIRECTORY.'", "\$DOWNLOADS_DIRECTORY.'", "\$DOCS_DIRECTORY.'");
			$root = str_replace($search, $replace, $root);
			$core_array[] = $root."' => '".$path_value."'";
		}
		$data .= implode(', 
		', $core_array);
		$data .= "\n);\n";
		$data .= "?>";
		$fp = fopen(e_ADMIN.'core_image.php', 'w');
		fwrite($fp, $data);
	}
	
	function checksum($filename, $create = FALSE) {
		$checksum = md5(str_replace(chr(13).chr(10), chr(10), file_get_contents($filename)));
		return $checksum;
	}
	
	function snapshot_interface() {
		global $ns, $rs;
		$text = "";
		if (isset($_POST['create_snapshot'])) {
			$this -> create_image($_POST['snapshot_path']);
			$text = "<div style='text-align:center'>
			<form action='".e_SELF."' method='post' id='main_page'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td class='fcaption'>Snapshot Created</td>
			</tr>";
		
			$text .= "<tr>
			<td class='forumheader3' style='text-align:center'>
			The snapshot (".e_ADMIN."core_image.php) was successfully created.
			</td>
			</tr>
			<tr>
			<td style='text-align:center' class='forumheader'>".$rs -> form_button('submit', 'main_page', 'Return To Main Page')."</td>
			</tr>
			</table>
			</form>
			</div><br />";
		}
		
		$text .= "<div style='text-align:center'>
		<form action='".e_SELF."?".e_QUERY."' method='post' id='snapshot'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='fcaption' colspan='2'>Create Snapshot</td>
		</tr>";
		
		$text .= "<tr>
		<td class='forumheader3' style='width:50%'>
		Absolute path of root directory to create image from:
		</td>
		<td class='forumheader3' style='width:50%'>
		<input class='tbox' type='text' name='snapshot_path' size='60' value='".$this -> root_dir."' />
		</td></tr>
		<tr>
		<td class='forumheader' style='text-align:center' colspan='2'>".$rs -> form_button('submit', 'create_snapshot', 'Create Snapshot')."</td>
		</tr>
		</table>
		</form>
		</div>";

		$ns -> tablerender('Snapshot', $text);

	}
	
	function scan_config() {
		global $ns, $rs;

		$text = "<div style='text-align: center'>
		<form action='".e_SELF."?results' method='post' id='scanform'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='fcaption' colspan='2'>Scan Options</td>
		</tr>";
		
		$text .= "<tr>
		<td class='forumheader3' style='width: 35%'>
		Show:
		</td>
		<td colspan='2' class='forumheader3' style='width: 65%'>
		<input type='radio' name='display' value='0'".(($_POST['display'] == '0' || !isset($_POST['display'])) ? " checked='checked'" : "")." /> All Files&nbsp;&nbsp;
		<input type='radio' name='display' value='1'".($_POST['display'] == '1' ? " checked='checked'" : "")." /> Core Files&nbsp;&nbsp;
		<input type='radio' name='display' value='3'".($_POST['display'] == '3' ? " checked='checked'" : "")." /> Core Files (Integrity Fail)&nbsp;&nbsp;
		<input type='radio' name='display' value='2'".($_POST['display'] == '2' ? " checked='checked'" : "")." /> Non Core Files&nbsp;&nbsp;
		</td>
		</tr>";
		
		$text .= "<tr>
		<td class='forumheader3' style='width: 35%'>
		Check Integrity Of Core Files:
		</td>
		<td class='forumheader3' style='width: 65%; vertical-align: top'>
		<input type='radio' name='integrity' value='1'".(($_POST['integrity'] == '1' || !isset($_POST['integrityy'])) ? " checked='checked'" : "")." /> On&nbsp;&nbsp;
		<input type='radio' name='integrity' value='0'".($_POST['integrity'] == '0' ? " checked='checked'" : "")." /> Off&nbsp;&nbsp;
		</td></tr>
		<tr>
		<td colspan='2' style='text-align:center' class='forumheader'>".$rs -> form_button('submit', 'scan', 'Scan Now')."</td>
		</tr>
		</table>
		</form>
		</div>";

		$ns -> tablerender('File Inspector', $text);
		
	}
	
	function parsesize($size = 0, $dec = 0) {
		if (!$size) { return FALSE; }
		$kb = 1024;
		$mb = 1024 * $kb;
		$gb = 1024 * $mb;
		$tb = 1024 * $gb;
		if ($size < $kb) {
			return $size." b";
		}
		else if($size < $mb) {
			return round($size/$kb)." kb";
		}
		else if($size < $gb) {
			return round($size/$mb, $dec)." mb";
		}
		else if($size < $tb) {
			return round($size/$gb, $dec)." gb";
		} else {
			return round($size/$tb, $dec)." tb";
		}
	}
}

require_once('footer.php');

function headerjs() {
	$text = "<style type='text/css'>
	<!--
	.f { padding: 1px 0px 1px 8px; vertical-align: bottom; width: 90%; white-space: nowrap }
	.d { margin: 2px 0px 1px 8px; cursor: default; white-space: nowrap }
	.s { padding: 1px 8px 1px 0px; vertical-align: bottom; width: 10%; white-space: nowrap }
	.t { margin-top: 1px; width: 100%; border-collapse: collapse; border-spacing: 0px }
	.i { width: 16px; height: 16px }
	.e { width: 9px; height: 9px }
	-->
	</style>";
		
	return $text;
}

?>