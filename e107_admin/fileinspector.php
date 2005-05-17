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
|     $Revision: 1.3 $
|     $Date: 2005-05-17 19:32:33 $
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
	
	function file_inspector() {
		$this -> root_dir = $_SERVER['DOCUMENT_ROOT'].e_HTTP;
		if (substr($this -> root_dir, -1) == '/') {
			$this -> root_dir = substr($this -> root_dir, 0, -1);
		}
	}

	function inspect($dir, $level, &$tree_end, &$tree_open) {
		global $core_image;
		unset ($text);
		unset ($childOut);
		$dir_id = crc32($dir);

		if ($level) {
			$this -> files_text[$dir_id] .= "<div style='margin: 2px 0px 1px 8px'><span onclick=\"showhideit('f_".$this -> parent['id']."')\">
			<img src='".e_IMAGE."fileinspector/folder_up.png' alt='' style='width: 16px; height: 16px' />&nbsp;..</span></div>";
		} else {
			$this -> files_text[$dir_id] .= "<span></span>";
		}
		
		$directory = $level ? basename($dir) : SITENAME;
		$level++;
		$handle = opendir($dir);
		while (false !== ($readdir = readdir($handle))) {
			if ($readdir != '.' && $readdir != '..' && $readdir != '/' && $readdir != 'CVS' && $readdir != 'Thumbs.db' && (strpos('._', $readdir) === FALSE)) {
				$this -> parent = array('id' => $dir_id, 'name' => $directory);
				$path = $dir.'/'.$readdir;
				if (is_dir($path)) {
					$child_open = false;
					$child_end = true;
					$childOut .= $this -> inspect($path, $level, $child_end, $child_open);
					$tree_end = false;
					// ?
					if ($child_open == 'warning') {
						$tree_open = 'warning';
					} else if ($child_open == 'core') {
						$tree_open = ($tree_open == 'unknown') ? 'unknown' : 'core';
					} else if ($child_open == 'unknown') {
						$tree_open = ($tree_open == 'warning') ? 'warning' : 'unknown';
					}
					// ?
				} else {
					if ($_POST['display'] == '0' || ($_POST['display'] == '3' && $readdir != 'core_image.php' && $this -> checksum($path) != $core_image[$path]) || ($_POST['display'] == '1' && isset($core_image[$path])) || ($_POST['display'] == '2' && !isset($core_image[$path]))) {
						 $size = $this -> parsesize(filesize($path));
						 $this -> files_text[$dir_id] .= "<div style='margin: 2px 0px 1px 8px'>";
						 if ($_POST['display'] != '3' && !isset($core_image[$path])) {
							$file_icon = 'file_unknown.png';
							$tree_open = ($tree_open == 'warning') ? 'warning' : 'unknown';
						} else if ($_POST['display'] != '3' && !$_POST['integrity']) {
							$file_icon = 'file.png';
							$tree_open = ($tree_open == 'unknown') ? 'unknown' : 'core';
						} else if ($readdir != 'core_image.php' && $this -> checksum($path) != $core_image[$path]) {
							$file_icon = 'file_warning.png';
							$tree_open = 'warning';
						} else {
							$file_icon = 'file_check.png';
						}
						$this -> files_text[$dir_id] .= "<img src='".e_IMAGE."fileinspector/".$file_icon."' alt='' style='width: 16px; height: 16px' />&nbsp;".$readdir."&nbsp;".$size."</div>";
					}
				}
			}
		}

		if ($tree_open == 'warning') {
			$dir_icon = 'folder_warning.png';
		} else if ($tree_open == 'unknown') {
			$dir_icon = 'folder_unknown.png';
		} else if ($tree_open == 'core' || $_POST['display'] == '2') {
			$dir_icon = 'folder.png';
		} else {
			$dir_icon = 'folder_check.png';
		}
		$icon = "<img src='".e_IMAGE."fileinspector/".$dir_icon."' alt='' style='width: 16px; height: 16px' />";
		$hide = ($tree_open && $tree_open != 'core') ? "" : "style='display: none'";
		$text .= "<div style='margin: 2px 0px 1px ".($level * 8)."px; cursor: default'>";
		$text .= $tree_end ? "<img src='".e_IMAGE."fileinspector/blank.png' alt='' style='width: 9px; height: 9px' />" : "<span onclick=\"expandit('d_".$dir_id."')\"><img src='".e_IMAGE."fileinspector/expand.png' alt='' style='width: 9px; height: 9px' /></span>";
		$text .= $tree_end ? "&nbsp;<span onclick=\"showhideit('f_".$dir_id."')\">".$icon."&nbsp;".$directory."</span>" : "&nbsp;<span onclick=\"showhideit('f_".$dir_id."')\">".$icon."&nbsp;".$directory."</span>";
		$text .= $tree_end ? "" : "<div ".$hide." id='d_".$dir_id."'>".$childOut."</div>";
		$text .= "</div>";

		return $text;
		closedir($handle);
	}
	
	function image_scan($dir) {
		$handle = opendir($dir);
		while (false !== ($readdir = readdir($handle))) {
			if ($readdir != '.' && $readdir != '..' && $readdir != '/' && $readdir != 'CVS' && $readdir != 'Thumbs.db' && (strpos('._', $readdir) === FALSE)) {
				$path = $dir.'/'.$readdir;
				if (is_dir($path)) {
					$this -> image_scan($path);
				} else {
					$this -> image[$path] = $this -> checksum($path);
				}
			}
		}
		return FALSE;
		closedir($handle);
	}
	
	function create_image($dir) {
		global $ADMIN_DIRECTORY, $FILES_DIRECTORY, $IMAGES_DIRECTORY, $THEMES_DIRECTORY, $PLUGINS_DIRECTORY, $HANDLERS_DIRECTORY, $LANGUAGES_DIRECTORY, $HELP_DIRECTORY, $DOWNLOADS_DIRECTORY;
		$this -> image_scan($dir);
		$data = "<?php\n";
		$data .= "\$core_image = array(
		";
		foreach($this -> image as $path_key => $path_value) {
			$root = str_replace($dir."/", "\$_SERVER['DOCUMENT_ROOT'].e_HTTP.'", $path_key);
			$search = array("'".$ADMIN_DIRECTORY, "'".$FILES_DIRECTORY, "'".$IMAGES_DIRECTORY, "'".$THEMES_DIRECTORY, "'".$PLUGINS_DIRECTORY, "'".$HANDLERS_DIRECTORY, "'".$LANGUAGES_DIRECTORY, "'".$HELP_DIRECTORY, "'".$DOWNLOADS_DIRECTORY);
			$replace = array("\$ADMIN_DIRECTORY.'", "\$FILES_DIRECTORY.'", "\$IMAGES_DIRECTORY.'", "\$THEMES_DIRECTORY.'", "\$PLUGINS_DIRECTORY.'", "\$HANDLERS_DIRECTORY.'", "\$LANGUAGES_DIRECTORY.'", "\$HELP_DIRECTORY.'", "\$DOWNLOADS_DIRECTORY.'");
			$root = str_replace($search, $replace, $root);
			$core_array[] = $root."' => '".$path_value."'";
		}
		$data .= implode($core_array, ', 
		');
		$data .= "\n);\n";
		$data .= "?>";
		$fp = fopen(e_ADMIN.'core_image.php', 'w');
		fwrite($fp, $data);
	}
	
	function checksum($filename) {
		$checksum = dechex(crc32(file_get_contents($filename)));
		return $checksum;
	}
	
	function scan_results() {
		global $ns, $rs;
		$text = "<script type=\"text/javascript\">
		<!--
		var hideid=\"f_".crc32($this -> root_dir)."\";
		function showhideit(showid){
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

		$text .= "<tr>
		<td class='forumheader3' style='width:50%'>
		<div style='height: 300px; overflow: auto'>
		".$scan_text."
		</div>
		</td>
		<td class='forumheader3' style='width:50%; vertical-align: top'><div style='height: 300px; overflow: auto'>";

		$initial = FALSE;
		foreach ($this -> files_text as $dir_id => $stext) {
			$hide = $initial ? "style='display: none'" : "";
			$text .= "<div ".$hide." id='f_".$dir_id."'>\n";
			$text .= $stext;
			$text .= "\n</div>\n";
			$initial = TRUE;
		}

		$text .= "</div></td></tr>";
/*
		$text .= "<tr>
		<td colspan='2' style='text-align:center' class='forumheader'>".$rs -> form_button('submit', 'updatesettings', 'Delete Selected')."</td>
		</tr>";
*/
		$text .= "</table>
		</div><br />";

		$ns -> tablerender('Scanning...', $text);
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
		Scan:
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
	
	function parsesize($size) {
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
			return round($size/$mb)." mb";
		}
		else if($size < $tb) {
			return round($size/$gb)." gb";
		} else {
			return round($size/$tb)." tb";
		}
	}
}

require_once('footer.php');

?>