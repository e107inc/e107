<?php
/*
+----------------------------------------------------------------------------+
|     e107 website system
|
|     (c)Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_admin/fileinspector.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once('../class2.php');
if (!getperms('Y')) {
	header('location:'.e_BASE.'index.php');
	exit;
}

$e_sub_cat = 'fileinspector';

require_once('auth.php');
require_once(e_HANDLER.'form_handler.php');
$rs = new form;
$fi = new file_inspector;

$DOCS_DIRECTORY = $HELP_DIRECTORY;		// Give a sensible, albeit probably invalid, value
if (substr($HELP_DIRECTORY,-5,5) == 'help/')
{
	$DOCS_DIRECTORY = substr($HELP_DIRECTORY,0,-5);		// Whatever $HELP_DIRECTORY is set to, assume docs are in a subdirectory called 'help' off it
}
$maindirs = array('admin' => $ADMIN_DIRECTORY, 'files' => $FILES_DIRECTORY, 'images' => $IMAGES_DIRECTORY, 'themes' => $THEMES_DIRECTORY, 'plugins' => $PLUGINS_DIRECTORY, 'handlers' => $HANDLERS_DIRECTORY, 'languages' => $LANGUAGES_DIRECTORY, 'downloads' => $DOWNLOADS_DIRECTORY, 'docs' => $DOCS_DIRECTORY);
foreach ($maindirs as $maindirs_key => $maindirs_value) {
	$coredir[$maindirs_key] = substr($maindirs_value, 0, -1);
}

require_once('core_image.php');
$core_image[$coredir['admin']]['filetypes.php'] = ' ';


if (e_QUERY) {
	$fi -> snapshot_interface();
} else if (isset($_POST['scan'])) {
	$fi -> scan_results();
	$fi -> scan_config();
} else {
	$fi -> scan_config();
}

class file_inspector {
	
	var $root_dir;
	var $files = array();
	var $parent;
	var $count = array();
	var $results = 0;
	
	function file_inspector()
	{
		global $e107;
		set_time_limit(240);
		$this -> root_dir = $e107 -> file_path;
		if (substr($this -> root_dir, -1) == '/') {
			$this -> root_dir = substr($this -> root_dir, 0, -1);
		}
		if ($_POST['core'] == 'fail') {
			$_POST['integrity'] = TRUE;
		}
		if (MAGIC_QUOTES_GPC && $_POST['regex']) {
			$_POST['regex'] = stripslashes($_POST['regex']);
		}
		if ($_POST['regex']) {
			if ($_POST['core'] == 'fail') {
				$_POST['core'] = 'all';
			}
			$_POST['missing'] = 0;
			$_POST['integrity'] = 0;
		}
	}
	
	function scan_config() {
		global $ns, $rs, $pref;

		$text = "<div style='text-align: center'>
		<form action='".e_SELF."' method='post' id='scanform'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='fcaption' colspan='2'>".FC_LAN_2."</td>
		</tr>";
		
		$text .= "<tr>
		<td class='forumheader3' style='width: 35%'>
		".FC_LAN_3." ".FC_LAN_5.":
		</td>
		<td colspan='2' class='forumheader3' style='width: 65%'>
		<input type='radio' name='core' value='all'".(($_POST['core'] == 'all' || !isset($_POST['core'])) ? " checked='checked'" : "")." /> ".FC_LAN_4."&nbsp;&nbsp;
		<input type='radio' name='core' value='fail'".($_POST['core'] == 'fail' ? " checked='checked'" : "")." /> ".FC_LAN_6."&nbsp;&nbsp;
		<input type='radio' name='core' value='none'".($_POST['core'] == 'none' ? " checked='checked'" : "")." /> ".FC_LAN_12."&nbsp;&nbsp;
		</td>
		</tr>";
		
		$text .= "<tr>
		<td class='forumheader3' style='width: 35%'>
		".FC_LAN_3." ".FC_LAN_13.":
		</td>
		<td colspan='2' class='forumheader3' style='width: 65%'>
		<input type='radio' name='missing' value='1'".(($_POST['missing'] == '1' || !isset($_POST['missing'])) ? " checked='checked'" : "")." /> ".FC_LAN_9."&nbsp;&nbsp;
		<input type='radio' name='missing' value='0'".($_POST['missing'] == '0' ? " checked='checked'" : "")." /> ".FC_LAN_10."&nbsp;&nbsp;
		</td>
		</tr>";
		
		$text .= "<tr>
		<td class='forumheader3' style='width: 35%'>
		".FC_LAN_3." ".FC_LAN_7.":
		</td>
		<td colspan='2' class='forumheader3' style='width: 65%'>
		<input type='radio' name='noncore' value='1'".(($_POST['noncore'] == '1' || !isset($_POST['noncore'])) ? " checked='checked'" : "")." /> ".FC_LAN_9."&nbsp;&nbsp;
		<input type='radio' name='noncore' value='0'".($_POST['noncore'] == '0' ? " checked='checked'" : "")." /> ".FC_LAN_10."&nbsp;&nbsp;
		</td>
		</tr>";
		
		$text .= "<tr>
		<td class='forumheader3' style='width: 35%'>
		".FC_LAN_3." ".FC_LAN_21.":
		</td>
		<td colspan='2' class='forumheader3' style='width: 65%'>
		<input type='radio' name='oldcore' value='1'".(($_POST['oldcore'] == '1' || !isset($_POST['oldcore'])) ? " checked='checked'" : "")." /> ".FC_LAN_9."&nbsp;&nbsp;
		<input type='radio' name='oldcore' value='0'".($_POST['oldcore'] == '0' ? " checked='checked'" : "")." /> ".FC_LAN_10."&nbsp;&nbsp;
		</td>
		</tr>";
		
		$text .= "<tr>
		<td class='forumheader3' style='width: 35%'>
		".FC_LAN_8.":
		</td>
		<td class='forumheader3' style='width: 65%; vertical-align: top'>
		<input type='radio' name='integrity' value='1'".(($_POST['integrity'] == '1' || !isset($_POST['integrity'])) ? " checked='checked'" : "")." /> ".FC_LAN_9."&nbsp;&nbsp;
		<input type='radio' name='integrity' value='0'".($_POST['integrity'] == '0' ? " checked='checked'" : "")." /> ".FC_LAN_10."&nbsp;&nbsp;
		</td></tr>";
		
		$text .= "<tr>
		<td class='forumheader3' style='width: 35%'>
		".FC_LAN_14.":
		</td>
		<td colspan='2' class='forumheader3' style='width: 65%'>
		<input type='radio' name='type' value='tree'".(($_POST['type'] == 'tree' || !isset($_POST['type'])) ? " checked='checked'" : "")." /> ".FC_LAN_15."&nbsp;&nbsp;
		<input type='radio' name='type' value='list'".($_POST['type'] == 'list' ? " checked='checked'" : "")." /> ".FC_LAN_16."&nbsp;&nbsp;
		</td>
		</tr>";
		
		if ($pref['developer']) {
			$text .= "<tr>
			<td class='fcaption' colspan='2'>".FC_LAN_17."</td>
			</tr>";
			
			$text .= "<tr>
			<td class='forumheader3' style='width: 35%'>
			".FC_LAN_18.":
			</td>
			<td colspan='2' class='forumheader3' style='width: 65%'>
			#<input class='tbox' type='text' name='regex' size='40' value='".htmlentities($_POST['regex'], ENT_QUOTES)."' />#<input class='tbox' type='text' name='mod' size='5' value='".$_POST['mod']."' />
			</td>
			</tr>";
			
			$text .= "<tr>
			<td class='forumheader3' style='width: 35%'>
			".FC_LAN_19.":
			</td>
			<td colspan='2' class='forumheader3' style='width: 65%'>
			<input type='checkbox' name='num' value='1'".(($_POST['num'] || !isset($_POST['num'])) ? " checked='checked'" : "")." />
			</td>
			</tr>";
			
			$text .= "<tr>
			<td class='forumheader3' style='width: 35%'>
			".FC_LAN_20.":
			</td>
			<td colspan='2' class='forumheader3' style='width: 65%'>
			<input type='checkbox' name='line' value='1'".(($_POST['line'] || !isset($_POST['line'])) ? " checked='checked'" : "")." />
			</td>
			</tr>";
		}
		
		$text .= "<tr>
		<td colspan='2' style='text-align:center' class='forumheader'>".$rs -> form_button('submit', 'scan', FC_LAN_11)."</td>
		</tr>
		</table>
		</form>
		</div>";

		$ns -> tablerender(FC_LAN_1, $text);
		
	}
	
	function scan($dir, $image) {
		$handle = opendir($dir.'/');
		while (false !== ($readdir = readdir($handle))) {
			if ($readdir != '.' && $readdir != '..' && $readdir != '/' && $readdir != 'CVS' && $readdir != 'Thumbs.db' && (strpos('._', $readdir) === FALSE)) {
				$path = $dir.'/'.$readdir;
				if (is_dir($path)) {
					$dirs[$path] = $readdir;
				} else if (!isset($image[$readdir])) {
					$files[$readdir] = $this -> checksum($path, TRUE);
				}
			}
		}
		closedir($handle);
		
		if (isset($dirs)) {
			ksort ($dirs);
			foreach ($dirs as $dir_path => $dir_list) {
				$list[$dir_list] = ($set = $this -> scan($dir_path, $image[$dir_list])) ? $set : array();
			}
		}
		
		if (isset($files)) {
			ksort ($files);
			foreach ($files as $file_name => $file_list) {
				$list[$file_name] = $file_list;
			}
		}
		
		return $list;
	}

	// Given a full path and filename, looks it up in the list to determine valid actions; returns:
	//	  'check' - file is expected to be present, and validity is to be checked
	//	  'ignore' - file may or may not be present - check its validity if found, but not an error if missing
	//	  'uncalc' - file must be present, but its integrity cannot be checked.
	//	  'nocalc' - file may be present, but its integrity cannot be checked. Not an error if missing
	function check_action($dir, $name)
	{

	  global $coredir;
	  
	  if ($name == 'e_inspect.php') { return 'nocalc'; }		// Special case for plugin integrity checking
	  
	  $filename = $dir.'/'.$name;
	  $admin_dir = $this->root_dir.'/'.$coredir['admin'].'/';
	  $image_dir  = $this->root_dir.'/'.$coredir['images'].'/';
	  $test_list = array();

	  // Files that are unable to be checked
	  $test_list[$admin_dir.'core_image.php'] = 'uncalc';
	  $test_list[$this->root_dir.'/e107_config.php'] = 'uncalc';

      // Files that are likely to be renamed by user
	  $test_list[$admin_dir.'filetypes_.php'] = 'ignore';
	  $test_list[$this->root_dir.'/e107.htaccess'] = 'ignore';
	  $test_list[$this->root_dir.'/e107.robots.txt'] = 'ignore';
	  
	  if (isset($test_list[$filename])) { return $test_list[$filename]; }
	  return 'check';
	}

	
	// This function does the real work
	//  $list -
	//	$deprecated
	// 	$level
	//	$dir
	//	&$tree_end
	//	&$parent_expand
	function inspect($list, $deprecated, $level, $dir, &$tree_end, &$parent_expand)
	{
	  global $coredir;
	
	  unset ($childOut);
	  $parent_expand = false;
	  if (substr($dir, -1) == '/')
	  {
		$dir = substr($dir, 0, -1);
	  }
	  $dir_id = dechex(crc32($dir));
	  $this -> files[$dir_id]['.']['level'] = $level;
	  $this -> files[$dir_id]['.']['parent'] = $this -> parent;
	  $this -> files[$dir_id]['.']['file'] = $dir;
	  $directory = $level ? basename($dir) : SITENAME;
	  $level++;
		
	  foreach ($list as $key => $value)
	  {
		$this -> parent = $dir_id;
		if (is_array($value))
		{ // Entry is a subdirectory - recurse another level
		  $path = $dir.'/'.$key;
		  $child_open = false;
		  $child_end = true;
		  $sub_text .= $this -> inspect($value, $deprecated[$key], $level, $path, $child_end, $child_expand);
		  $tree_end = false;
		  if ($child_expand)
		  {
			$parent_expand = true;
			$last_expand = true;
		  }
		}
		else
		{
		  $path = $dir.'/'.$key;
		  $fid = strtolower($key);
		  $this -> files[$dir_id][$fid]['file'] = ($_POST['type'] == 'tree') ? $key : $path;
		  if (($this -> files[$dir_id][$fid]['size'] = filesize($path)) !== FALSE)
		  {	// We're checking a file here
			if ($_POST['core'] != 'none')
			{		// Look at core files
			  $this -> count['core']['num']++;
			  $this -> count['core']['size'] += $this -> files[$dir_id][$fid]['size'];
			  if ($_POST['regex'])
			  {	// Developer prefs activated - search file contents according to regex
				$file_content = file($path);		// Get contents of file
				if (($this -> files[$dir_id][$fid]['size'] = filesize($path)) !== FALSE)
				{
				  if ($this -> files[$dir_id][$fid]['lines'] = preg_grep("#".$_POST['regex']."#".$_POST['mod'], $file_content))
				  {	// Search string found - add file to list
					$this -> files[$dir_id][$fid]['file'] = ($_POST['type'] == 'tree') ? $key : $path;
					$this -> files[$dir_id][$fid]['icon'] = 'file_core.png';
					$dir_icon = 'fileinspector.png';
					$parent_expand = TRUE;
					$this -> results++;
				  }
				  else
				  {	// Search string not found - discard from list
					unset($this -> files[$dir_id][$fid]);
					$known[$dir_id][$fid] = true;
					$dir_icon = ($dir_icon == 'fileinspector.png') ? $dir_icon : 'folder.png';
				  }
				}
			  }
			  else
			  {
				if ($_POST['integrity'])
				{	// Actually check file integrity
				  switch ($this_action = $this->check_action($dir,$key))
				  {
					case 'ignore' :
				    case 'check' :
					  if ($this -> checksum($path) != $value)
					  {
						$this -> count['fail']['num']++;
						$this -> count['fail']['size'] += $this -> files[$dir_id][$fid]['size'];
						$this -> files[$dir_id][$fid]['icon'] = 'file_fail.png';
						$dir_icon = 'folder_fail.png';
						$parent_expand = TRUE;
					  }
					  else
					  {
						$this -> count['pass']['num']++;
						$this -> count['pass']['size'] += $this -> files[$dir_id][$fid]['size'];
						if ($_POST['core'] != 'fail')
						{
						  $this -> files[$dir_id][$fid]['icon'] = 'file_check.png';
						  $dir_icon = ($dir_icon == 'folder_fail.png' || $dir_icon == 'folder_missing.png') ? $dir_icon : 'folder_check.png';
						}
						else
						{
						  unset($this -> files[$dir_id][$fid]);
						  $known[$dir_id][$fid] = true;
						}
					  }
					  break;
					case 'uncalc' :
					case 'nocalc' :
					  $this -> count['uncalculable']['num']++;
					  $this -> count['uncalculable']['size'] += $this -> files[$dir_id][$fid]['size'];
					  if ($_POST['core'] != 'fail')
					  {
						$this -> files[$dir_id][$fid]['icon'] = 'file_uncalc.png';
					  }
					  else
					  {
						unset($this -> files[$dir_id][$fid]);
						$known[$dir_id][$fid] = true;
					  }
					  break;
				  }
				}
				else
				{	// Just identify as core file
				  $this -> files[$dir_id][$fid]['icon'] = 'file_core.png';
				}
			  }
			}
			else
			{
			  unset ($this -> files[$dir_id][$fid]);
			  $known[$dir_id][$fid] = true;
			}
		  }
		  else if ($_POST['missing'])
		  {
			switch ($this_action = $this->check_action($dir,$key))
			{
			  case 'check' :
			  case 'uncalc' :
				$this -> count['missing']['num']++;
				$this -> files[$dir_id][$fid]['icon'] = 'file_missing.png';
				$dir_icon = ($dir_icon == 'folder_fail.png') ? $dir_icon : 'folder_missing.png';
				$parent_expand = TRUE;
				break;
			  case 'ignore' :
			  case 'nocalc' :
			    // These files can be missing without error - delete from the list
				unset ($this -> files[$dir_id][$fid]);
				$known[$dir_id][$fid] = true;
			    break;
			}
		  }
		  else
		  {
			unset ($this -> files[$dir_id][$fid]);
		  }
		}
	  }
		
		if ($_POST['noncore'] || $_POST['oldcore']) {
			$handle = opendir($dir.'/');
			while (false !== ($readdir = readdir($handle))) {
				if ($readdir != '.' && $readdir != '..' && $readdir != '/' && $readdir != 'CVS' && $readdir != 'Thumbs.db' && (strpos('._', $readdir) === FALSE)) {
					if (is_dir($dir.'/'.$readdir)) {
						if (!isset($list[$readdir]) && ($level > 1 || $readdir == 'e107_install')) {
							$child_open = false;
							$child_end = true;
							$sub_text .= $this -> inspect(array(), $deprecated[$readdir], $level, $dir.'/'.$readdir, $child_end, $child_expand);
							$tree_end = false;
							if ($child_expand) {
								$parent_expand = true;
								$last_expand = true;
							}
						}
					} else {
						$aid = strtolower($readdir);
						if (!isset($this -> files[$dir_id][$aid]['file']) && !$known[$dir_id][$aid]) {
							if (strpos($dir.'/'.$readdir, 'htmlarea') === false) {
								if (isset($deprecated[$readdir])) {
									if ($_POST['oldcore']) {
										$this -> files[$dir_id][$aid]['file'] = ($_POST['type'] == 'tree') ? $readdir : $dir.'/'.$readdir;
										$this -> files[$dir_id][$aid]['size'] = filesize($dir.'/'.$readdir);
										$this -> files[$dir_id][$aid]['icon'] = 'file_old.png';
										$this -> count['deprecated']['num']++;
										$this -> count['deprecated']['size'] += $this -> files[$dir_id][$aid]['size'];
									}
								} else {
									if ($_POST['noncore']) {
										$this -> files[$dir_id][$aid]['file'] = ($_POST['type'] == 'tree') ? $readdir : $dir.'/'.$readdir;
										$this -> files[$dir_id][$aid]['size'] = filesize($dir.'/'.$readdir);
										$this -> files[$dir_id][$aid]['icon'] = 'file_unknown.png';
										$this -> count['unknown']['num']++;
										$this -> count['unknown']['size'] += $this -> files[$dir_id][$aid]['size'];
									}
								}
							} else {
								$this -> files[$dir_id][$aid]['file'] = ($_POST['type'] == 'tree') ? $readdir : $dir.'/'.$readdir;
								$this -> files[$dir_id][$aid]['size'] = filesize($dir.'/'.$readdir);
								$this -> files[$dir_id][$aid]['icon'] = 'file_warning.png';
								$this -> count['warning']['num']++;
								$this -> count['warning']['size'] += $this -> files[$dir_id][$aid]['size'];
								$this -> count['deprecated']['num']++;
								$this -> count['deprecated']['size'] += $this -> files[$dir_id][$aid]['size'];
								$dir_icon = 'folder_warning.png';
								$parent_expand = TRUE;
							}
							if ($_POST['regex']) {
								$file_content = file($dir.'/'.$readdir);
								if ($this -> files[$dir_id][$aid]['lines'] = preg_grep("#".$_POST['regex']."#".$_POST['mod'], $file_content)) {
									$dir_icon = 'fileinspector.png';
									$parent_expand = TRUE;
									$this -> results++;
								} else {
									unset($this -> files[$dir_id][$aid]);
									$dir_icon = ($dir_icon == 'fileinspector.png') ? $dir_icon : 'folder.png';
								}
							} else {
								if (isset($deprecated[$readdir])) {
									if ($_POST['oldcore']) {
										$dir_icon = ($dir_icon == 'folder_warning.png' || $dir_icon == 'folder_fail.png' || $dir_icon == 'folder_missing.png' || $dir_icon == 'folder_old_dir.png') ? $dir_icon : 'folder_old.png';
										$parent_expand = TRUE;
									}
								} else {
									if ($_POST['noncore']) {
										$dir_icon = ($dir_icon == 'folder_warning.png' || $dir_icon == 'folder_fail.png' || $dir_icon == 'folder_missing.png' || $dir_icon == 'folder_old.png' || $dir_icon == 'folder_old_dir.png') ? $dir_icon : 'folder_unknown.png';
										$parent_expand = TRUE;
									}
								}
							}
						} else if ($_POST['core'] == 'none') {
							unset($this -> files[$dir_id][$aid]);
						}
					}
				}
			}
			closedir($handle);
		}
		
		$dir_icon = $dir_icon ? $dir_icon : 'folder.png';
		$icon = "<img src='".e_IMAGE."fileinspector/".$dir_icon."' class='i' alt='' />";
		$hide = ($last_expand && $dir_icon != 'folder_core.png') ? "" : "style='display: none'";
		$text = "<div class='d' style='margin-left: ".($level * 8)."px'>";
		$text .= $tree_end ? "<img src='".e_IMAGE."fileinspector/blank.png' class='e' alt='' />" : "<span onclick=\"ec('".$dir_id."')\"><img src='".e_IMAGE."fileinspector/".($hide ? 'expand.png' : 'contract.png')."' class='e' alt='' id='e_".$dir_id."' /></span>";
		$text .= "&nbsp;<span onclick=\"sh('f_".$dir_id."')\">".$icon."&nbsp;".$directory."</span>";
		$text .= $tree_end ? "" : "<div ".$hide." id='d_".$dir_id."'>".$sub_text."</div>";
		$text .= "</div>";
		
		$this -> files[$dir_id]['.']['icon'] = $dir_icon;

		return $text;
	}

	function scan_results() {
		global $ns, $rs, $core_image, $deprecated_image;
		$scan_text = $this -> inspect($core_image, $deprecated_image, 0, $this -> root_dir);

		if ($_POST['type'] == 'tree') {
			$text = "<div style='text-align:center'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td class='fcaption' colspan='2'>".FR_LAN_2."</td>
			</tr>";

			$text .= "<tr style='display: none'><td style='width:50%'></td><td style='width:50%'></td></tr>";
		
			$text .= "<tr>
			<td class='forumheader3' style='width:50%'>
			<div style='height: 400px; overflow: auto'>
			".$scan_text."
			</div>
			</td>
			<td class='forumheader3' style='width:50%; vertical-align: top'><div style='height: 400px; overflow: auto'>";
		} else {
			$text = "<div style='text-align:center'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td class='fcaption' colspan='2'>".FR_LAN_2."</td>
			</tr>";
			
			$text .= "<tr>
			<td class='forumheader3' colspan='2'>";
		}

		$text .= "<table class='t' id='initial'>";
		
		if ($_POST['type'] == 'tree') {
			$text .= "<tr><td class='f' style='padding-left: 4px'>
			<img src='".e_IMAGE."fileinspector/fileinspector.png' class='i' alt='' />&nbsp;<b>".FR_LAN_3."</b></td>
			<td class='s' style='text-align: right; padding-right: 4px' onclick=\"sh('f_".dechex(crc32($this -> root_dir))."')\">
			<img src='".e_IMAGE."fileinspector/forward.png' class='i' alt='' /></td></tr>";
		} else {
			$text .= "<tr><td class='f' style='padding-left: 4px' colspan='2'>
			<img src='".e_IMAGE."fileinspector/fileinspector.png' class='i' alt='' />&nbsp;<b>".FR_LAN_3."</b></td>
			</tr>";
		}

		if ($_POST['core'] != 'none') {
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_core.png' class='i' alt='' />&nbsp;".FR_LAN_4.":&nbsp;".($this -> count['core']['num'] ? $this -> count['core']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$this -> parsesize($this -> count['core']['size'], 2)."</td></tr>";
		}
		if ($_POST['missing']) {
			$text .= "<tr><td class='f' colspan='2'><img src='".e_IMAGE."fileinspector/file_missing.png' class='i' alt='' />&nbsp;".FR_LAN_22.":&nbsp;".($this -> count['missing']['num'] ? $this -> count['missing']['num'] : FR_LAN_21)."&nbsp;</td></tr>";
		}
		if ($_POST['noncore']) {
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_unknown.png' class='i' alt='' />&nbsp;".FR_LAN_5.":&nbsp;".($this -> count['unknown']['num'] ? $this -> count['unknown']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$this -> parsesize($this -> count['unknown']['size'], 2)."</td></tr>";
		}
		if ($_POST['oldcore']) {
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_old.png' class='i' alt='' />&nbsp;".FR_LAN_24.":&nbsp;".($this -> count['deprecated']['num'] ? $this -> count['deprecated']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$this -> parsesize($this -> count['deprecated']['size'], 2)."</td></tr>";
		}
		if ($_POST['core'] == 'all') {
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file.png' class='i' alt='' />&nbsp;".FR_LAN_6.":&nbsp;".($this -> count['core']['num'] + $this -> count['unknown']['num'] + $this -> count['deprecated']['num'])."&nbsp;</td><td class='s'>".$this -> parsesize($this -> count['core']['size'] + $this -> count['unknown']['size'] + $this -> count['deprecated']['size'], 2)."</td></tr>";
		}
		
		if ($this -> count['warning']['num']) {
			$text .= "<tr><td colspan='2'>&nbsp;</td></tr>";
			$text .= "<tr><td style='padding-left: 4px' colspan='2'>
			<img src='".e_IMAGE."fileinspector/warning.png' class='i' alt='' />&nbsp;<b>".FR_LAN_26."</b></td></tr>";
		
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_warning.png' class='i' alt='' />&nbsp;".FR_LAN_28.":&nbsp;".($this -> count['warning']['num'] ? $this -> count['warning']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$this -> parsesize($this -> count['warning']['size'], 2)."</td></tr>";
			
			$text .= "<tr><td class='w' colspan='2'><img src='".e_IMAGE."fileinspector/info.png' class='i' alt='' />&nbsp;".FR_LAN_27."</td></tr>";

		}
		if ($_POST['integrity'] && $_POST['core'] != 'none') {
			$integrity_icon = $this -> count['fail']['num'] ? 'integrity_fail.png' : 'integrity_pass.png';
			$integrity_text = $this -> count['fail']['num'] ? '( '.$this -> count['fail']['num'].' '.FR_LAN_19.' )' : '( '.FR_LAN_20.' )';
			$text .= "<tr><td colspan='2'>&nbsp;</td></tr>";
			$text .= "<tr><td class='f' style='padding-left: 4px' colspan='2'>
			<img src='".e_IMAGE."fileinspector/".$integrity_icon."' class='i' alt='' />&nbsp;<b>".FR_LAN_7."</b> ".$integrity_text."</td></tr>";
		
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_check.png' class='i' alt='' />&nbsp;".FR_LAN_8.":&nbsp;".($this -> count['pass']['num'] ? $this -> count['pass']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$this -> parsesize($this -> count['pass']['size'], 2)."</td></tr>";
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_fail.png' class='i' alt='' />&nbsp;".FR_LAN_9.":&nbsp;".($this -> count['fail']['num'] ? $this -> count['fail']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$this -> parsesize($this -> count['fail']['size'], 2)."</td></tr>";
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_uncalc.png' class='i' alt='' />&nbsp;".FR_LAN_25.":&nbsp;".($this -> count['uncalculable']['num'] ? $this -> count['uncalculable']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$this -> parsesize($this -> count['uncalculable']['size'], 2)."</td></tr>";
		
			$text .= "<tr><td colspan='2'>&nbsp;</td></tr>";

			$text .= "<tr><td class='f' colspan='2'><img src='".e_IMAGE."fileinspector/info.png' class='i' alt='' />&nbsp;".FR_LAN_10.":&nbsp;</td></tr>";

			$text .= "<tr><td style='padding-right: 4px' colspan='2'>
			<ul><li>
			<a href=\"javascript: expandit('i_corrupt')\">".FR_LAN_11."...</a><div style='display: none' id='i_corrupt'>
			".FR_LAN_12."<br /><br /></div>
			</li><li>
			<a href=\"javascript: expandit('i_date')\">".FR_LAN_13."...</a><div style='display: none' id='i_date'>
			".FR_LAN_14."<br /><br /></div>
			</li><li>
			<a href=\"javascript: expandit('i_edit')\">".FR_LAN_15."...</a><div style='display: none' id='i_edit'>
			".FR_LAN_16."<br /><br /></div>
			</li><li>
			<a href=\"javascript: expandit('i_cvs')\">".FR_LAN_17."...</a><div style='display: none' id='i_cvs'>
			".FR_LAN_18."<br /><br /></div>
			</li></ul>
			</td></tr>";
		}
		
		if ($_POST['type'] == 'tree' && !$this -> results && $_POST['regex']) {
			$text .= "</td></tr>
			<tr><td style='padding-right: 4px; text-align: center' colspan='2'><br />".FR_LAN_23."</td></tr>";
		}

		$text .= "</table>";
		
		if ($_POST['type'] != 'tree') {
			$text .= "<br /></td></tr><tr>
			<td class='forumheader3' colspan='2'>
			<table class='t'>";
			if (!$this -> results && $_POST['regex']) {
				$text .= "<tr><td class='f' style='padding-left: 4px; text-align: center' colspan='2'>".FR_LAN_23."</td></tr>";
			}
		}

		foreach ($this -> files as $dir_id => $fid) {
			ksort($fid);
			$text .= ($_POST['type'] == 'tree') ? "<table class='t' style='display: none' id='f_".$dir_id."'>" : "";
			$initial = FALSE;
			foreach ($fid as $key => $stext) {
				if (!$initial) {
					if ($_POST['type'] == 'tree') {
						$text .= "<tr><td class='f' style='padding-left: 4px' ".($stext['level'] ? "onclick=\"sh('f_".$stext['parent']."')\"" : "").">
						<img src='".e_IMAGE."fileinspector/".($stext['level'] ? "folder_up.png" : "folder_root.png")."' class='i' alt='' />".($stext['level'] ? "&nbsp;.." : "")."</td>
						<td class='s' style='text-align: right; padding-right: 4px' onclick=\"sh('initial')\"><img src='".e_IMAGE."fileinspector/close.png' class='i' alt='' /></td></tr>";
					}
				} else {
					if ($_POST['type'] != 'tree') {
						$stext['file'] = str_replace($this -> root_dir."/", "", $stext['file']);
					}
					$text .= "<tr>
					<td class='f'><img src='".e_IMAGE."fileinspector/".$stext['icon']."' class='i' alt='' />&nbsp;".$stext['file']."&nbsp;";
					if ($_POST['regex']) {
						if ($_POST['num'] || $_POST['line']) {
							$text .= "<br />";
						}
						foreach ($stext['lines'] as $rkey => $rvalue) {
							if ($_POST['num']) {
								$text .= "[".($rkey + 1)."] ";
							}
							if ($_POST['line']) {
								$text .= htmlspecialchars($rvalue)."<br />";
							}
						}
						$text .= "<br />";
					} else {
						$text .= "</td>
						<td class='s'>".$this -> parsesize($stext['size']);
					}
					$text .= "</td></tr>";
				}
				$initial = TRUE;
			}
			$text .= ($_POST['type'] == 'tree') ? "</table>" : "";
		}
		
		if ($_POST['type'] != 'tree') {
			$text .= "</td>
			</tr></table>";
		}

		$text .= "</td></tr>";
		
		$text .= "</table>
		</dit><br />";

		$ns -> tablerender(FR_LAN_1.'...', $text);
	}
	
	function create_image($dir) {
		global $core_image, $deprecated_image, $coredir;
		
		foreach ($coredir as $trim_key => $trim_dirs) {
			$search[$trim_key] = "'".$trim_dirs."'";
			$replace[$trim_key] = "\$coredir['".$trim_key."']";
		}
		
		$data = "<?php\n";
		$data .= "/*\n";
		$data .= "+ ----------------------------------------------------------------------------+\n";
		$data .= "|     e107 website system\n";
		$data .= "|\n";
		$data .= "|     �Steve Dunstan 2001-2002\n";
		$data .= "|     http://e107.org\n";
		$data .= "|     jalist@e107.org\n";
		$data .= "|\n";
		$data .= "|     Released under the terms and conditions of the\n";
		$data .= "|     GNU General Public License (http://gnu.org).\n";
		$data .= "|\n";
		$data .= "|     \$Source: /cvs_backup/e107_0.7/e107_admin/fileinspector.php,v $\n";
		$data .= "|     \$Revision$\n";
		$data .= "|     \$Date$\n";
		$data .= "|     \$Author$\n";
		$data .= "+----------------------------------------------------------------------------+\n";
		$data .= "*/\n\n";
		$data .= "if (!defined('e107_INIT')) { exit; }\n\n";
		
		$scan_current = ($_POST['snaptype'] == 'current') ? $this -> scan($dir) : $core_image;
		$image_array = var_export($scan_current, true);
		$image_array = str_replace($search, $replace, $image_array);
		$data .= "\$core_image = ".$image_array.";\n\n";
		
		$scan_deprecated = ($_POST['snaptype'] == 'deprecated') ? $this -> scan($dir, $core_image) : $deprecated_image;
		$image_array = var_export($scan_deprecated, true);
		$image_array = str_replace($search, $replace, $image_array);
		$data .= "\$deprecated_image = ".$image_array.";\n\n";

		$data .= "?>";
		$fp = fopen(e_ADMIN.'core_image.php', 'w');
		fwrite($fp, $data);
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
		<input class='tbox' type='text' name='snapshot_path' size='60' value='".(isset($_POST['snapshot_path']) ? $_POST['snapshot_path'] : $this -> root_dir)."' />
		</td></tr>
		
		<tr>
		<td class='forumheader3' style='width: 35%'>
		Create snapshot of current or deprecated core files:
		</td>
		<td colspan='2' class='forumheader3' style='width: 65%'>
		<input type='radio' name='snaptype' value='current'".($_POST['snaptype'] == 'current' || !isset($_POST['snaptype']) ? " checked='checked'" : "")." /> Current&nbsp;&nbsp;
		<input type='radio' name='snaptype' value='deprecated'".($_POST['snaptype'] == 'deprecated' ? " checked='checked'" : "")." /> Deprecated&nbsp;&nbsp;
		</td>
		</tr>
		
		<tr>
		<td class='forumheader' style='text-align:center' colspan='2'>".$rs -> form_button('submit', 'create_snapshot', 'Create Snapshot')."</td>
		</tr>
		</table>
		</form>
		</div>";

		$ns -> tablerender('Snapshot', $text);

	}
	
	function checksum($filename) {
		$checksum = md5(str_replace(array(chr(13),chr(10)), "", file_get_contents($filename)));
		return $checksum;
	}
	
	function parsesize($size, $dec = 0) {
		$size = $size ? $size : 0;
		$kb = 1024;
		$mb = 1024 * $kb;
		$gb = 1024 * $mb;
		$tb = 1024 * $gb;
		if ($size < $kb) {
			return $size." b";
		} else if($size < $mb) {
			return round($size/$kb)." kb";
		} else if($size < $gb) {
			return round($size/$mb, $dec)." mb";
		} else if($size < $tb) {
			return round($size/$gb, $dec)." gb";
		} else {
			return round($size/$tb, $dec)." tb";
		}
	}
	
	function regex_match($file) {
		$file_content = file_get_contents($file);
		$match = preg_match($_POST['regex'], $file_content);
		return $match;
	}
}

require_once('footer.php');

function headerjs() {
global $e107;
$text = "<script type='text/javascript'>
<!--
c = new Image(); c = '".SITEURLBASE.e_IMAGE_ABS."fileinspector/contract.png';
e = '".SITEURLBASE.e_IMAGE_ABS."fileinspector/expand.png';
function ec(ecid) {
	icon = document.getElementById('e_' + ecid).src;
	if (icon == e) {
		document.getElementById('e_' + ecid).src = c;
	} else {
		document.getElementById('e_' + ecid).src = e;
	}

	div = document.getElementById('d_' + ecid).style;
	if (div.display == 'none') {
		div.display = '';
	} else {
		div.display = 'none';
	}
}

var hideid = 'initial';
function sh(showid) {
	if (hideid != showid) {
		show = document.getElementById(showid).style;
		hide = document.getElementById(hideid).style;
		show.display = '';
		hide.display = 'none';
		hideid = showid;
	}
}
//-->
</script>
<style type='text/css'>
<!--\n";
if ($_POST['regex']) {
	$text .= ".f { padding: 1px 0px 1px 8px; vertical-align: bottom; width: 90% }\n";
} else {
	$text .= ".f { padding: 1px 0px 1px 8px; vertical-align: bottom; width: 90%; white-space: nowrap }\n";
}
$text .= ".d { margin: 2px 0px 1px 8px; cursor: default; white-space: nowrap }
.s { padding: 1px 8px 1px 0px; vertical-align: bottom; width: 10%; white-space: nowrap }
.t { margin-top: 1px; width: 100%; border-collapse: collapse; border-spacing: 0px }
.w { padding: 1px 0px 1px 8px; vertical-align: bottom; width: 90% }
.i { width: 16px; height: 16px }
.e { width: 9px; height: 9px }
-->
</style>\n";
		
return $text;
}

?>