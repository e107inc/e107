<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/fileinspector.php,v $
 * $Revision: 1.18 $
 * $Date: 2009-11-12 14:30:07 $
 * $Author: marj_nl_fr $
 */

if ($_SERVER['QUERY_STRING'] == 'alone')
{
//@TODO make multi-language if possible
// Standalone file inspector - requires suitably edited authorisation file, otherwise it just exits with blank screen. This bit is intended to facilitate the
// checking of a totally dead installation.

  error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
  ini_set("display_errors", "off");

  require_once('check_inspector.php');
  if (!defined('e107_INIT')) { exit; }
  if ( defined('e107_FILECHECK')) { exit; }
  if (!defined('e107_STANDALONE')) { exit; }

  ini_set("display_errors", "on");

// Use XHTML transitional - we're not aiming to be pretty here!
  echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang=\"en\">
<head>
<title>Standalone File Scan</title>
</head>\n
<body>\n
Standalone file scan - checks for a valid set of E107 files<br />";
  
// Sort out other directories
	@include_once('./../e107_config.php');
	if(!isset($ADMIN_DIRECTORY))
	{
	// e107_config.php is either empty, not valid or doesn't exist so use some defaults
	  echo "e107_config.php not found or not configured. Using default directory structure.<br />";
	  $ADMIN_DIRECTORY     = "e107_admin/";
	  $FILES_DIRECTORY     = "e107_files/";
	  $IMAGES_DIRECTORY    = "e107_images/";
	  $THEMES_DIRECTORY    = "e107_themes/";
	  $PLUGINS_DIRECTORY   = "e107_plugins/";
	  $HANDLERS_DIRECTORY  = "e107_handlers/";
	  $LANGUAGES_DIRECTORY = "e107_languages/";
	  $HELP_DIRECTORY      = "e107_docs/help/";
	  $DOWNLOADS_DIRECTORY = "e107_files/downloads/";
	}
	else
	{
	  echo "Using directory structure from e107_config.php<br />";
	}


  $fi = new file_inspector(TRUE);

// Needed to make everything work
  define("e107_INIT", TRUE);
  define("e_BASE", $fi->root_web."/");		// No trailing slash stored
  define("e_IMAGE", e_BASE.$IMAGES_DIRECTORY);
  define("e_LANGUAGEDIR", e_BASE.$LANGUAGES_DIRECTORY);

  echo "Image Directory: ".e_IMAGE."<br />";
  
  if (!realpath(__FILE__))
  {
    echo '<b>realpath() function disabled by host - E107 will not run without modification</b><br /><br />';
  }

  if (!function_exists('ob_end_clean'))
  {
    echo "function ob_end_clean() does not exist. Creating temporary function.<br />";
	function ob_end_clean() { return FALSE; }
  }

  if (!file_exists('core_image.php'))
  {
    echo 'Cannot continue - core_image.php does not exist<br />';
	return;
  }
  
  if (!file_exists(e_LANGUAGEDIR.'English/admin/lan_fileinspector.php'))
  {
    echo 'Language file not found - cannot continue<br />';
	return;
  }
  
  // Strip trailing '/' off each directory name
  $DOCS_DIRECTORY = str_replace('help/', '', $HELP_DIRECTORY);
  $maindirs = array('admin' => $ADMIN_DIRECTORY, 'files' => $FILES_DIRECTORY, 'images' => $IMAGES_DIRECTORY, 'themes' => $THEMES_DIRECTORY, 'plugins' => $PLUGINS_DIRECTORY, 'handlers' => $HANDLERS_DIRECTORY, 'languages' => $LANGUAGES_DIRECTORY, 'downloads' => $DOWNLOADS_DIRECTORY, 'docs' => $DOCS_DIRECTORY);
  foreach ($maindirs as $maindirs_key => $maindirs_value) 
  {
	$coredir[$maindirs_key] = substr($maindirs_value, 0, -1);
	if (!file_exists(e_BASE.$coredir[$maindirs_key]))
	{
	  echo "Complete directory missing: ".$coredir[$maindirs_key]."<br />";
	}
  }
  
  require_once('core_image.php');
  require_once(e_LANGUAGEDIR.'English/admin/lan_fileinspector.php');
  define ("SITENAME", "Current E107 Site");
  $fi -> scan_results();
  echo "</body></html>";
  return;
}

// Normal operation here
require_once('../class2.php');
if (!getperms('Y')) {
	header('location:'.e_BASE.'index.php');
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'fileinspector';

require_once('auth.php');
require_once(e_HANDLER.'form_handler.php');
$rs = new form;
$fi = new file_inspector;

$DOCS_DIRECTORY = str_replace('help/', '', $HELP_DIRECTORY);
$maindirs = array('admin' => $ADMIN_DIRECTORY, 'files' => $FILES_DIRECTORY, 'images' => $IMAGES_DIRECTORY, 'themes' => $THEMES_DIRECTORY, 'plugins' => $PLUGINS_DIRECTORY, 'handlers' => $HANDLERS_DIRECTORY, 'languages' => $LANGUAGES_DIRECTORY, 'downloads' => $DOWNLOADS_DIRECTORY, 'docs' => $DOCS_DIRECTORY);
foreach ($maindirs as $maindirs_key => $maindirs_value) 
{
  $coredir[$maindirs_key] = substr($maindirs_value, 0, -1);
}



require_once('core_image.php');

$handle = opendir(e_PLUGIN);
while (false !== ($readdir = readdir($handle))) {
	if ($readdir != '.' && $readdir != '..' && $readdir != '/' && $readdir != 'CVS' && $readdir != 'Thumbs.db' && (strpos('._', $readdir) === FALSE)) {
		if (is_dir(e_PLUGIN.$readdir)) {
				if (is_readable(e_PLUGIN.$readdir.'/e_inspect.php')) {
				include_once(e_PLUGIN.$readdir.'/e_inspect.php');
			}
		}
	}
}
closedir($handle);

ksort($core_image[$coredir['plugins']]);

if (e_QUERY) {
	$fi -> snapshot_interface();
} else if (isset($_POST['scan'])) {
	$fi -> scan_results();
	$fi -> scan_config();
} else {
	$fi -> scan_config();
}

class file_inspector {
	
	var $root_dir;		// Note - no trailing '/'
	var $root_web;		// For HTML etc
	var $files = array();
	var $parent;
	var $count = array();
	var $results = 0;
	var $line_results = 0;
	var $BASE_PLUGIN_DIR;
	var $BASE_THEMES_DIR;
	var $alone = FALSE;
	var $dotree = FALSE;
	
	function file_inspector($standalone = FALSE) 
	{
	  global $PLUGINS_DIRECTORY, $THEMES_DIRECTORY;
	  $this->dotree = ($_POST['type'] == 'tree');
	  set_time_limit(240);

	  if ($standalone == TRUE)
	  {
	    $this->alone = TRUE;
		
	    $this->root_dir = realpath(dirname(__FILE__)."/./../");
		echo "Root File Directory: ".$this->root_dir."<br />";

		$this->root_web = './..';
		
		$_POST['type'] = 'list';
		$_POST['core'] = 'fail';
		$_POST['integrity'] = TRUE;
		$_POST['oldcore'] = 0;
		$_POST['noncore'] = 0;
		$_POST['missing'] = 1;
		$this->dotree = FALSE;
		$this->BASE_PLUGIN_DIR = $this -> root_dir.'/'.$PLUGINS_DIRECTORY;
		if (substr($this->BASE_PLUGIN_DIR,-1) == '/') $this->BASE_PLUGIN_DIR = substr($this->BASE_PLUGIN_DIR,0,-1);
		$this->BASE_THEMES_DIR = $this -> root_dir.'/'.$THEMES_DIRECTORY;
		if (substr($this->BASE_THEMES_DIR,-1) == '/') $this->BASE_THEMES_DIR = substr($this->BASE_THEMES_DIR,0,-1);
	    return;
	  }

		global $e107;
		$this -> root_dir = $e107 -> file_path;
		if (substr($this -> root_dir, -1) == '/') 
		{
		  $this -> root_dir = substr($this -> root_dir, 0, -1);
		}
		if ($_POST['core'] == 'fail') 
		{
		  $_POST['integrity'] = TRUE;
		}
		if (MAGIC_QUOTES_GPC && $_POST['regex']) 
		{
		  $_POST['regex'] = stripslashes($_POST['regex']);
		}
		if ($_POST['regex']) 
		{
		  if ($_POST['core'] == 'fail') 
		  {
			$_POST['core'] = 'all';
		  }
		  $_POST['missing'] = 0;
		  $_POST['integrity'] = 0;
		}
		$this->BASE_PLUGIN_DIR = $this -> root_dir.'/'.$PLUGINS_DIRECTORY;
		if (substr($this->BASE_PLUGIN_DIR,-1) == '/') $this->BASE_PLUGIN_DIR = substr($this->BASE_PLUGIN_DIR,0,-1);
		$this->BASE_THEMES_DIR = $this -> root_dir.'/'.$THEMES_DIRECTORY;
		if (substr($this->BASE_THEMES_DIR,-1) == '/') $this->BASE_THEMES_DIR = substr($this->BASE_THEMES_DIR,0,-1);
	}
	
	function scan_config() 
	{
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
		<input type='radio' name='type' value='tree'".(($this->dotree || !isset($_POST['type'])) ? " checked='checked'" : "")." /> ".FC_LAN_15."&nbsp;&nbsp;
		<input type='radio' name='type' value='list'".($this->dotree ? "" : " checked='checked'")." /> ".FC_LAN_16."&nbsp;&nbsp;
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
			<input type='radio' name='num' value='1'".(($_POST['num'] == '1' || !isset($_POST['num'])) ? " checked='checked'" : "")." /> ".FC_LAN_9."&nbsp;&nbsp;
			<input type='radio' name='num' value='0'".($_POST['num'] == '0' ? " checked='checked'" : "")." /> ".FC_LAN_10."&nbsp;&nbsp;
			</td>
			</tr>";
			
			$text .= "<tr>
			<td class='forumheader3' style='width: 35%'>
			".FC_LAN_20.":
			</td>
			<td colspan='2' class='forumheader3' style='width: 65%'>
			<input type='radio' name='line' value='1'".(($_POST['line'] == '1' || !isset($_POST['line'])) ? " checked='checked'" : "")." /> ".FC_LAN_9."&nbsp;&nbsp;
			<input type='radio' name='line' value='0'".($_POST['line'] == '0' ? " checked='checked'" : "")." /> ".FC_LAN_10."&nbsp;&nbsp;
			</td>
			</tr>";
			
			$text .= "<tr>
			<td class='forumheader3' style='width: 35%'>
			".FC_LAN_22.":
			</td>
			<td colspan='2' class='forumheader3' style='width: 65%'>
			<input type='radio' name='highlight' value='1'".(($_POST['highlight'] == '1' || !isset($_POST['highlight'])) ? " checked='checked'" : "")." /> ".FC_LAN_9."&nbsp;&nbsp;
			<input type='radio' name='highlight' value='0'".($_POST['highlight'] == '0' ? " checked='checked'" : "")." /> ".FC_LAN_10."&nbsp;&nbsp;
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
	  
	  if ($name == 'e_inspect.php') return 'nocalc';		// Special case for plugin integrity checking
	  
	  $filename = $dir.'/'.$name;
	  $admin_dir = $this -> root_dir.'/'.$coredir['admin'].'/';
	  $test_list = array(
			$admin_dir.'core_image.php' => 'uncalc',
			$admin_dir.'filetypes.php' => 'uncalc',
			$admin_dir.'filetypes_.php' => 'ignore',
			$admin_dir.'admin_filetypes.php' => 'nocalc',
			$this -> root_dir.'/e107_config.php' => 'uncalc',
			$this -> root_dir.'/e107.htaccess' => 'ignore',
			$this -> root_dir.'/install.php' => 'ignore'
		);
	  if (isset($test_list[$filename])) return $test_list[$filename];
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
	  $sub_text = '';
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
		  if ((($dir == $this->BASE_PLUGIN_DIR) || ($dir == $this->BASE_THEMES_DIR)) && !is_readable($path))
		  {  // Its one of the plugin or theme directories which doesn't exist - that could be OK
//		    echo "Plugin or theme folder missing: {$path}<br />";
			$icon = "<img src='".e_IMAGE."fileinspector/folder_missing.png' class='i' alt='' />";
			$text = "<div class='d' style='margin-left: ".(($level+1) * 8)."px'>";
			$text .= "<img src='".e_IMAGE."fileinspector/contract.png' class='e' alt='' />&nbsp;".$icon."&nbsp;".$key."&nbsp;-&nbsp;".FR_LAN_31;
			$text .= "</div>";
			$sub_text .= $text;
			$sub_id = dechex(crc32($path));
			$this -> files[$sub_id]['.']['icon'] = 'folder_missing.png';
			$this -> files[$sub_id]['.']['level'] = $level+1;
			$this -> files[$sub_id]['.']['parent'] = $dir;
			$this -> files[$sub_id]['.']['file'] = $path;
		  }
		  else
		  {
			$sub_text .= $this -> inspect($value, $deprecated[$key], $level, $path, $child_end, $child_expand);
		  }
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
		  $this -> files[$dir_id][$fid]['file'] = ($this->dotree) ? $key : $path;
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
					$this -> files[$dir_id][$fid]['file'] = ($this->dotree) ? $key : $path;
					$this -> files[$dir_id][$fid]['icon'] = 'file_core.png';
					$dir_icon = 'fileinspector.png';
					$parent_expand = TRUE;
					$this -> results++;
					$this -> line_results += count($this -> files[$dir_id][$fid]['lines']);
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
		  elseif ($_POST['missing']) 
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
								if (isset($deprecated[$readdir]) && $dir.'/'.$readdir != $this -> root_dir.'/'.$coredir['admin'].'/filetypes.php') {
									if ($_POST['oldcore']) {
										$this -> files[$dir_id][$aid]['file'] = ($this->dotree) ? $readdir : $dir.'/'.$readdir;
										$this -> files[$dir_id][$aid]['size'] = filesize($dir.'/'.$readdir);
										$this -> files[$dir_id][$aid]['icon'] = 'file_old.png';
										$this -> count['deprecated']['num']++;
										$this -> count['deprecated']['size'] += $this -> files[$dir_id][$aid]['size'];
									}
								} else {
									if ($_POST['noncore']) {
										$this -> files[$dir_id][$aid]['file'] = ($this->dotree) ? $readdir : $dir.'/'.$readdir;
										$this -> files[$dir_id][$aid]['size'] = filesize($dir.'/'.$readdir);
										$this -> files[$dir_id][$aid]['icon'] = 'file_unknown.png';
										$this -> count['unknown']['num']++;
										$this -> count['unknown']['size'] += $this -> files[$dir_id][$aid]['size'];
									}
								}
							} else {
								$this -> files[$dir_id][$aid]['file'] = ($this->dotree) ? $readdir : $dir.'/'.$readdir;
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

	function scan_results() 
	{
		global $ns, $rs, $core_image, $deprecated_image, $tp, $e107;
		$scan_text = $this -> inspect($core_image, $deprecated_image, 0, $this -> root_dir);

//		if ($_POST['type'] == 'tree') 
		if ($this->dotree)
		{
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
		} 
		else 
		{
			$text = "<div style='text-align:center'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td class='fcaption' colspan='2'>".FR_LAN_2."</td>
			</tr>";
			
			$text .= "<tr>
			<td class='forumheader3' colspan='2'>";
		}

		$text .= "<table class='t' id='initial'>";
		
//		if ($_POST['type'] == 'tree') 
		if ($this->dotree)
		{
			$text .= "<tr><td class='f' style='padding-left: 4px'>
			<img src='".e_IMAGE."fileinspector/fileinspector.png' class='i' alt='' />&nbsp;<b>".FR_LAN_3."</b></td>
			<td class='s' style='text-align: right; padding-right: 4px' onclick=\"sh('f_".dechex(crc32($this -> root_dir))."')\">
			<img src='".e_IMAGE."fileinspector/forward.png' class='i' alt='' /></td></tr>";
		} 
		else 
		{
			$text .= "<tr><td class='f' style='padding-left: 4px' colspan='2'>
			<img src='".e_IMAGE."fileinspector/fileinspector.png' class='i' alt='' />&nbsp;<b>".FR_LAN_3."</b></td>
			</tr>";
		}

		if ($_POST['core'] != 'none') {
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_core.png' class='i' alt='' />&nbsp;".FR_LAN_4.":&nbsp;".($this -> count['core']['num'] ? $this -> count['core']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$e107->parseMemorySize($this -> count['core']['size'], 2)."</td></tr>";
		}
		if ($_POST['missing']) {
			$text .= "<tr><td class='f' colspan='2'><img src='".e_IMAGE."fileinspector/file_missing.png' class='i' alt='' />&nbsp;".FR_LAN_22.":&nbsp;".($this -> count['missing']['num'] ? $this -> count['missing']['num'] : FR_LAN_21)."&nbsp;</td></tr>";
		}
		if ($_POST['noncore']) {
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_unknown.png' class='i' alt='' />&nbsp;".FR_LAN_5.":&nbsp;".($this -> count['unknown']['num'] ? $this -> count['unknown']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$e107->parseMemorySize($this -> count['unknown']['size'], 2)."</td></tr>";
		}
		if ($_POST['oldcore']) {
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_old.png' class='i' alt='' />&nbsp;".FR_LAN_24.":&nbsp;".($this -> count['deprecated']['num'] ? $this -> count['deprecated']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$e107->parseMemorySize($this -> count['deprecated']['size'], 2)."</td></tr>";
		}
		if ($_POST['core'] == 'all') {
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file.png' class='i' alt='' />&nbsp;".FR_LAN_6.":&nbsp;".($this -> count['core']['num'] + $this -> count['unknown']['num'] + $this -> count['deprecated']['num'])."&nbsp;</td><td class='s'>".$e107->parseMemorySize($this -> count['core']['size'] + $this -> count['unknown']['size'] + $this -> count['deprecated']['size'], 2)."</td></tr>";
		}
		if ($_POST['regex']) {
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file.png' class='i' alt='' />&nbsp;".FR_LAN_29.":&nbsp;".($this -> results)."&nbsp;</td><td class='s'>&nbsp;</td></tr>";
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file.png' class='i' alt='' />&nbsp;".FR_LAN_30.":&nbsp;".($this -> line_results)."&nbsp;</td><td class='s'>&nbsp;</td></tr>";
		}
		
		
		if ($this -> count['warning']['num']) {
			$text .= "<tr><td colspan='2'>&nbsp;</td></tr>";
			$text .= "<tr><td style='padding-left: 4px' colspan='2'>
			<img src='".e_IMAGE."fileinspector/warning.png' class='i' alt='' />&nbsp;<b>".FR_LAN_26."</b></td></tr>";
		
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_warning.png' class='i' alt='' />&nbsp;".FR_LAN_28.":&nbsp;".($this -> count['warning']['num'] ? $this -> count['warning']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$e107->parseMemorySize($this -> count['warning']['size'], 2)."</td></tr>";
			
			$text .= "<tr><td class='w' colspan='2'><img src='".e_IMAGE."fileinspector/info.png' class='i' alt='' />&nbsp;".FR_LAN_27."</td></tr>";

		}
		if ($_POST['integrity'] && $_POST['core'] != 'none') {
			$integrity_icon = $this -> count['fail']['num'] ? 'integrity_fail.png' : 'integrity_pass.png';
			$integrity_text = $this -> count['fail']['num'] ? '( '.$this -> count['fail']['num'].' '.FR_LAN_19.' )' : '( '.FR_LAN_20.' )';
			$text .= "<tr><td colspan='2'>&nbsp;</td></tr>";
			$text .= "<tr><td class='f' style='padding-left: 4px' colspan='2'>
			<img src='".e_IMAGE."fileinspector/".$integrity_icon."' class='i' alt='' />&nbsp;<b>".FR_LAN_7."</b> ".$integrity_text."</td></tr>";
		
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_check.png' class='i' alt='' />&nbsp;".FR_LAN_8.":&nbsp;".($this -> count['pass']['num'] ? $this -> count['pass']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$e107->parseMemorySize($this -> count['pass']['size'], 2)."</td></tr>";
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_fail.png' class='i' alt='' />&nbsp;".FR_LAN_9.":&nbsp;".($this -> count['fail']['num'] ? $this -> count['fail']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$e107->parseMemorySize($this -> count['fail']['size'], 2)."</td></tr>";
			$text .= "<tr><td class='f'><img src='".e_IMAGE."fileinspector/file_uncalc.png' class='i' alt='' />&nbsp;".FR_LAN_25.":&nbsp;".($this -> count['uncalculable']['num'] ? $this -> count['uncalculable']['num'] : FR_LAN_21)."&nbsp;</td><td class='s'>".$e107->parseMemorySize($this -> count['uncalculable']['size'], 2)."</td></tr>";
		
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
		
//		if ($_POST['type'] == 'tree' && !$this -> results && $_POST['regex']) 
		if ($this->dotree && !$this -> results && $_POST['regex']) 
		{
			$text .= "</td></tr>
			<tr><td style='padding-right: 4px; text-align: center' colspan='2'><br />".FR_LAN_23."</td></tr>";
		}

		$text .= "</table>";
		
//		if ($_POST['type'] != 'tree') 
		if (!$this->dotree)
		{
			$text .= "<br /></td></tr><tr>
			<td class='forumheader3' colspan='2'>
			<table class='t'>";
			if (!$this -> results && $_POST['regex']) 
			{
			  $text .= "<tr><td class='f' style='padding-left: 4px; text-align: center' colspan='2'>".FR_LAN_23."</td></tr>";
			}
		}




		foreach ($this -> files as $dir_id => $fid) 
		{
			ksort($fid);
			$text .= ($this->dotree) ? "<table class='t' style='display: none' id='f_".$dir_id."'>" : "";
			$initial = FALSE;
			foreach ($fid as $key => $stext) 
			{
//				if ($_POST['type'] == 'tree') 
				if ($this->dotree)
//				if (!$initial) 
				{
					if (!$initial) 
//					if ($_POST['type'] == 'tree') 
					{
						$text .= "<tr><td class='f' style='padding-left: 4px' ".($stext['level'] ? "onclick=\"sh('f_".$stext['parent']."')\"" : "").">
						<img src='".e_IMAGE."fileinspector/".($stext['level'] ? "folder_up.png" : "folder_root.png")."' class='i' alt='' />".($stext['level'] ? "&nbsp;.." : "")."</td>
						<td class='s' style='text-align: right; padding-right: 4px' onclick=\"sh('initial')\"><img src='".e_IMAGE."fileinspector/close.png' class='i' alt='' /></td></tr>";
					}
				} 
				else 
				{
//					if ($_POST['type'] != 'tree') 
					if (!$this->dotree)
					{
						$stext['file'] = str_replace($this -> root_dir."/", "", $stext['file']);
					}
					$text .= "<tr>
					<td class='f'><img src='".e_IMAGE."fileinspector/".$stext['icon']."' class='i' alt='' />&nbsp;".$stext['file']."&nbsp;";
					if ($_POST['regex']) 
					{
						if ($_POST['num'] || $_POST['line']) {
							$text .= "<br />";
						}
						foreach ($stext['lines'] as $rkey => $rvalue) {
							if ($_POST['num']) {
								$text .= "[".($rkey + 1)."] ";
							}
							if ($_POST['line']) {
								if ($_POST['highlight']) {
									$text .= $tp -> e_highlight(htmlspecialchars($rvalue), $_POST['regex'])."<br />";
								} else {
									$text .= htmlspecialchars($rvalue)."<br />";
								}
							}
						}
						$text .= "<br />";
					} 
					else 
					{
						$text .= "</td>
						<td class='s'>".$e107->parseMemorySize($stext['size']);
					}
					$text .= "</td></tr>";
				}
				$initial = TRUE;
			}
			$text .= ($this->dotree) ? "</table>" : "";
		}




		
//		if ($_POST['type'] != 'tree') 
		if (!$this->dotree)
		{
		  $text .= '</table>';
		}
		else
		{
		  $text .= '</div>';
		}

		$text .= "</td></tr>";
		
		$text .= "</table>
		</div><br />";

	    if ($this->alone)
		{
		  echo $text;
		}
		else
		{
		  $ns -> tablerender(FR_LAN_1.'...', $text);
		}
	}
	
	function create_image($dir, $plugin) {
		global $core_image, $deprecated_image, $coredir, $plugin_image, $plugin_deprecated_image, $PLUGINS_DIRECTORY;
		
		if ($plugin && $plugin !='off') {
			$dir = $dir.'/'.$PLUGINS_DIRECTORY.$plugin;
		}
		
		foreach ($coredir as $trim_key => $trim_dirs) {
			$search[$trim_key] = "'".$trim_dirs."'";
			$replace[$trim_key] = "\$coredir['".$trim_key."']";
		}
		
		$data = "<?php\n";
		
		if (!$plugin || $plugin == 'off')
		{
			$data .= '
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $'.'Source: $
 * $'.'Revision: $
 * $'.'Date: $
 * $'.'Author: $
 */
';
		}
		$data .= "if (!defined('e107_INIT')) { exit; }\n\n";

		if ($plugin && $plugin !='off') {
			$scan_current = ($_POST['snaptype'] == 'current') ? $this -> scan($dir) : $core_image[$coredir['plugins']][$plugin];
			$image_array = var_export($scan_current, true);
			$data .= "\$core_image[\$coredir['plugins']]['".$plugin."'] = ".$image_array.";\n\n";
		} else  {
			$scan_current = ($_POST['snaptype'] == 'current') ? $this -> scan($dir) : $core_image;
			$image_array = var_export($scan_current, true);
			$image_array = str_replace($search, $replace, $image_array);
			$data .= "\$core_image = ".$image_array.";\n\n";
		}
		
		if ($plugin && $plugin !='off') {
			$scan_deprecated = ($_POST['snaptype'] == 'deprecated') ? $this -> scan($dir, $core_image) : $deprecated_image[$coredir['plugins']]['".$plugin."'];
			$image_array = var_export($scan_deprecated, true);
			$data .= "\$deprecated_image[\$coredir['plugins']]['".$plugin."'] = ".$image_array.";\n\n";
		} else  {
			$scan_deprecated = ($_POST['snaptype'] == 'deprecated') ? $this -> scan($dir, $core_image) : $deprecated_image;
			$image_array = var_export($scan_deprecated, true);
			$image_array = str_replace($search, $replace, $image_array);
			$data .= "\$deprecated_image = ".$image_array.";\n\n";
		}

		$data .= "?>";
		if ($plugin && $plugin !='off') {
			$fp = fopen(e_PLUGIN.$plugin .'/e_inspect.php', 'w');
		} else {
			$fp = fopen(e_ADMIN.'core_image.php', 'w');
		}
		fwrite($fp, $data);
	}
	
	function snapshot_interface() {
		global $ns, $rs;
		$text = "";
		if (isset($_POST['create_snapshot'])) {
			$this -> create_image($_POST['snapshot_path'], $_POST['plugin']);
			$text = "<div style='text-align:center'>
			<form action='".e_SELF."' method='post' id='main_page'>
			<table style='".ADMIN_WIDTH."' class='fborder'>
			<tr>
			<td class='fcaption'>".FS_LAN_10."</td>
			</tr>";
		
			$text .= "<tr>
			<td class='forumheader3' style='text-align:center'>
			".FS_LAN_11."
			</td>
			</tr>
			<tr>
			<td style='text-align:center' class='forumheader'>".$rs -> form_button('submit', 'main_page', FS_LAN_12)."</td>
			</tr>
			</table>
			</form>
			</div><br />";
		}
		
		$text .= "<div style='text-align:center'>
		<form action='".e_SELF."?".e_QUERY."' method='post' id='snapshot'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td class='fcaption' colspan='2'>".FS_LAN_1."</td>
		</tr>";
		
		$text .= "<tr>
		<td class='forumheader3' style='width:50%'>
		".FS_LAN_2.":
		</td>
		<td class='forumheader3' style='width:50%'>
		<input class='tbox' type='text' name='snapshot_path' size='60' value='".(isset($_POST['snapshot_path']) ? $_POST['snapshot_path'] : $this -> root_dir)."' />
		</td></tr>

		<tr>
		<td class='forumheader3' style='width: 35%'>
		".FS_LAN_3."
		</td>
		<td colspan='2' class='forumheader3' style='width: 65%'>
		<select name='plugin' class='tbox'>
		<option value='off' ".($_POST['plugin'] == 'off' ? "selected='selected'" : "").">".FS_LAN_4."</option>";
		
		$handle = opendir(e_PLUGIN);
		while (false !== ($readdir = readdir($handle))) {
			if ($readdir != '.' && $readdir != '..' && $readdir != '/' && $readdir != 'CVS' && $readdir != 'Thumbs.db' && (strpos('._', $readdir) === FALSE)) {
				if (is_dir(e_PLUGIN.$readdir)) {
					if (is_readable(e_PLUGIN.$readdir.'/e_inspect.php')) {
						// $text .= e_PLUGIN.$readdir.'/e_inspect.php';
						$text .= "<option value='".$readdir."' ".($_POST['plugin'] == $readdir ? "selected='selected'" : "").">".$readdir."</option>";
					}
				}
			}
		}
		closedir($handle);
		
		$text .= "</select>
		</td>
		</tr>
		
		<tr>
		<td class='forumheader3' style='width: 35%'>
		".FS_LAN_5.":
		</td>
		<td colspan='2' class='forumheader3' style='width: 65%'>
		<input type='radio' name='snaptype' value='current'".($_POST['snaptype'] == 'current' || !isset($_POST['snaptype']) ? " checked='checked'" : "")." /> ".FS_LAN_6."&nbsp;&nbsp;
		<input type='radio' name='snaptype' value='deprecated'".($_POST['snaptype'] == 'deprecated' ? " checked='checked'" : "")." /> ".FS_LAN_7."&nbsp;&nbsp;
		</td>
		</tr>
		
		
		<tr>
		<td class='forumheader' style='text-align:center' colspan='2'>".$rs -> form_button('submit', 'create_snapshot', FS_LAN_8)."</td>
		</tr>
		</table>
		</form>
		</div>";

		$ns -> tablerender(FS_LAN_9, $text);

	}
	
	function checksum($filename) {
		$checksum = md5(str_replace(array(chr(13),chr(10)), "", file_get_contents($filename)));
		return $checksum;
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