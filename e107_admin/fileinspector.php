<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - File inspector
 * 
 */
ini_set('zlib.output_compression', 0);
header('Content-Encoding: none'); // turn off gzip. 
ob_implicit_flush(true);
ob_end_flush();

require_once('../class2.php');

e107::coreLan('fileinspector', true);

if(!getperms('Y'))
{
	e107::redirect('admin');
	exit;
}

$error_handler->debug = FALSE;

$DOCS_DIRECTORY = $HELP_DIRECTORY;		// Give a sensible, albeit probably invalid, value

if(substr($HELP_DIRECTORY,-5,5) == 'help/')
{
	$DOCS_DIRECTORY = substr($HELP_DIRECTORY,0,-5);		// Whatever $HELP_DIRECTORY is set to, assume docs are in a subdirectory called 'help' off it
}

$maindirs = array(
	'admin' 	=> $ADMIN_DIRECTORY, 
	'files' 	=> $FILES_DIRECTORY, 
	'images'	=> $IMAGES_DIRECTORY, 
	'themes' 	=> $THEMES_DIRECTORY, 
	'plugins' 	=> $PLUGINS_DIRECTORY, 
	'handlers' 	=> $HANDLERS_DIRECTORY, 
	'languages' => $LANGUAGES_DIRECTORY, 
	'downloads' => $DOWNLOADS_DIRECTORY, 
	'docs' 		=> $DOCS_DIRECTORY
);

foreach ($maindirs as $maindirs_key => $maindirs_value) 
{
	$coredir[$maindirs_key] = substr($maindirs_value, 0, -1);
}

require_once('core_image.php');

set_time_limit(18000);
$e_sub_cat = 'fileinspector';


if(isset($_GET['scan']))
{
	session_write_close();
	while (@ob_end_clean()); 
	
	//header("Content-type: text/html; charset=".CHARSET, true);
	//$css_file = file_exists(e_THEME.$pref['admintheme'].'/'.$pref['admincss']) ? e_THEME.$pref['admintheme'].'/'.$pref['admincss'] : e_THEME.$pref['admintheme'].'/'.$pref['admincss'];
	//	$fi = new file_inspector;

	$fi = e107::getSingleton('file_inspector');

	echo "<!DOCTYPE html>
	<html> 
	<head>  	
		<title>Results</title>
		<script type='text/javascript' src='https://cdn.jsdelivr.net/jquery/2.1.4/jquery.min.js'></script>
		<link  rel='stylesheet' media='all' property='stylesheet' type='text/css' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css' />
	 
		".$fi->headerCss()." ".headerjs()."
		<body style='height:100%;background-color:#2F2F2F'>\n";

		//	define('e_IFRAME', true);
		//	require_once(e_ADMIN."auth.php");
			
		// echo "<br />loading..";
		
		// echo "..";
		//flush();

		$_POST = $_GET;
		
		if(vartrue($_GET['exploit']))
		{
			$fi->exploit();	
		}
		else
		{
			$fi->scan_results();	
		}
		
		//	require_once(e_ADMIN."footer.php");
	
	echo "</body>
	</html>";
	
	exit();
		
}
else
{
	// $fi = new file_inspector;
	$fi = e107::getSingleton('file_inspector');

	require_once(e_ADMIN.'auth.php');
	
	
	//	if(e_QUERY) {
	// $fi -> snapshot_interface();
	//}

	if(varset($_POST['scan'])) 
	{
		 $fi->exploit_interface();
		 $fi->scan_config();
	} 
	elseif($_GET['mode'] == 'run')
	{
		$mes = e107::getMessage();
		$mes->addInfo(FR_LAN_32);//Run a Scan first	
		echo $mes->render();
	}
	else 
	{
		$fi->scan_config();
	}
}



class file_inspector {
	
	var $root_dir;
	var $files = array();
	var $parent;
	var $count = array();
	var $results = 0;
	var $totalFiles = 0;
	var $coredir = array();
	var $progress_units = 0;
	private $langs = array();
	private $lang_short = array();

	private $excludeFiles = array( '.', '..','/','.svn', 'CVS' ,'Thumbs.db', '.git');

	private $knownSecurityIssues = array('htmlarea', 'e107_docs/docs.php');
//	private $icon = array();
	private $iconTag = array();

	private $options = array(
		'core'          => '',
		'type'          =>'list',
		'missing'       => 0,
		'noncore'       => 9,
		'nolang'        => 1,
		'oldcore'       => 0,
		'integrity'     => 1,
		'regex'         => 0,
		'mod'           => '',
		'num'           => 0,
		'line'          => 0
	);

	function setOptions($post)
	{
		foreach($this->options as $k=>$v)
		{
			if(isset($post[$k]))
			{
				$this->options[$k] = $post[$k];
			}
		}
	}

	function __construct()
	{
		$lng    = e107::getLanguage();
		$langs  = $lng->installed();

		if(isset($_GET['scan']))
		{
			$this->setOptions($_GET);
		}

		$lang_short = array();
		
		foreach($langs as $k=>$val)
		{
		    if($val == "English") // Core release language, so ignore it.
		    {
				unset($langs[$k]);
				continue;
			}

			$lang_short[] = $lng->convert($val);
		}

		$this->langs = $langs;
		$this->lang_short = $lang_short;

		$this->glyph = array(
			'folder_close'      => array('<i class="fa fa-times-circle-o"></i>'),
			'folder_up'         => array('<i class="fa fa-folder-open-o"></i>'),
			'folder_root'       => array('<i class="fa fa-folder-o"></i>'),

			'warning'           => array('<i class="fa fa-exclamation-triangle text-warning" ></i>'),
			'info'              => array('<i class="fa fa-info-circle text-primary" ></i>'),
			'fileinspector'     => array('<i class="fa fa-folder text-success" style="color:#F6EDB0;"></i>'),

			'folder'            => array('<i class="fa fa-folder text-success" style="color:#F6EDB0;"></i>'),
			'folder_check'      => array('<i class="fa fa-folder text-success" style="color:#F6EDB0" ></i>', FC_LAN_24 ),
			'folder_fail'       => array('<i class="fa fa-folder text-danger" ></i>', FC_LAN_25 ),
			'folder_missing'    => array('<i class="fa fa-folder-o text-danger" ></i>', FC_LAN_26 ),
			'folder_warning'    => array('<i class="fa fa-folder text-warning" ></i>'),
			'folder_old'        => array('<i class="fa fa-folder-o text-warning" ></i>', FC_LAN_27 ),
			'folder_old_dir'    => array('<i class="fa fa-folder-o text-warning" ></i>'),
			'folder_unknown'    => array('<i class="fa fa-folder-o text-primary" ></i>', FC_LAN_28 ),

			'file_check'        => array('<i class="fa fa-file text-success" style="color:#F6EDB0" ></i>', FC_LAN_29),
			'file_core'        	=> array('<i class="fa fa-file-o text-success" style="color:#F6EDB0" ></i>', FC_LAN_30),
			'file_fail'         => array('<i class="fa fa-file text-danger" ></i>', FC_LAN_31 ),
			'file_missing'      => array('<i class="fa fa-file-o text-danger" ></i>', FC_LAN_32 ),
			'file_old'          => array('<i class="fa fa-file-o text-warning" ></i>', FC_LAN_33 ),
			'file_uncalc'       => array('<i class="fa fa-file-o " ></i>', FC_LAN_34 ),
			'file_warning'      => array('<i class="fa fa-file text-warning" ></i>', FC_LAN_35 ),
			'file_unknown'      => array('<i class="fa fa-file-o text-primary" ></i>', FC_LAN_36 ),
		);

		foreach($this->glyph as $k=>$v)
		{
			$this->iconTag[$k] = $this->glyph[$k][0];
		}

		global $e107, $core_image;
		
		//$this->totalFiles =  count($core_image,COUNT_RECURSIVE);
		$this->countFiles($core_image);
		
		$this->root_dir = $e107 -> file_path;
		
		if(substr($this->root_dir, -1) == '/')
		{
			$this->root_dir = substr($this->root_dir, 0, -1);
		}

		if($_POST['core'] == 'fail')
		{
			$_POST['integrity'] = TRUE;
		}

		if(MAGIC_QUOTES_GPC && vartrue($_POST['regex']))
		{
			$_POST['regex'] = stripslashes($_POST['regex']);
		}

		if($_POST['regex']) 
		{	
			if($_POST['core'] == 'fail') 
			{
				$_POST['core'] = 'all';
			}
		
			$_POST['missing'] = 0;
			$_POST['integrity'] = 0;
		}
	}	


	private function opt($key)
	{
		return $this->options[$key];
	}


	// Find the Total number of core files before scanning begins. 
	function countFiles($array)
	{
		foreach($array as $k=>$val)
		{
			if(is_array($val))
			{
				$this->countFiles($val);
			}
			elseif($val)
			{
				$this->totalFiles++;		
			}		
		}	
	}


	private function getDiz($key)
	{
		if(!empty($this->glyph[$key][1]))
		{
			return $this->glyph[$key][1];
		}

		return $key;
	}


	public function getLegend()
	{
		return $this->glyph;
	}


	function renderHelp()
	{
		$text = "<table>";
		
		foreach($this->iconTag as $k=>$v)
		{
			$text .=  "<tr><td>".$v."</td><td>".$k."</td></tr>";

		}
		$text .= "</table>";
		// echo $text;
	}

	
	function scan_config() 
	{
		$frm 	= e107::getForm();
		$ns 	= e107::getRender();
		$pref 	= e107::pref('core');

		if($_GET['mode'] == 'run')
		{
			return;	
		}
		
		$tab = array();

		$head = "<div>
		<form action='".e_SELF."?mode=run' method='post' id='scanform'>";

		$text = "
		<table class='table  adminform'>";

	/*	$text .= "
		<tr>
		<td class='fcaption' colspan='2'>".LAN_OPTIONS."</td>
		</tr>";*/
		
		$coreOpts = array('full'=>FC_LAN_6, 'all'=>LAN_ALL, 'none'=> LAN_NONE);
		
		$text .= "<tr>
		<td style='width: 35%'>
		".LAN_SHOW." ".FC_LAN_5.":
		</td>
		<td colspan='2' style='width: 65%'>".$frm->select('core',$coreOpts,$_POST['core'])."	</td>
		</tr>";
		
		
		$dispOpt = array('tree'=>FC_LAN_15, 'list'=>LAN_LIST);	
		$text .= "<tr>
		<td style='width: 35%'>
		".FC_LAN_14.":
		</td>
		<td colspan='2' style='width: 65%'>".$frm->select('type', $dispOpt, $_POST['type'])."	</td>
		</td>
		</tr>";
		
		
		$text .= "<tr>
		<td style='width: 35%'>
		".LAN_SHOW." ".FC_LAN_13.":
		</td>
		<td colspan='2' style='width: 65%'>
		<input type='radio' name='missing' value='1'".(($_POST['missing'] == '1' || !isset($_POST['missing'])) ? " checked='checked'" : "")." /> ".LAN_YES."&nbsp;&nbsp;
		<input type='radio' name='missing' value='0'".($_POST['missing'] == '0' ? " checked='checked'" : "")." /> ".LAN_NO."&nbsp;&nbsp;
		</td>
		</tr>";
		
		$text .= "<tr>
		<td style='width: 35%'>
		".LAN_SHOW." ".FC_LAN_7.":
		</td>
		<td colspan='2' style='width: 65%'>
		<input type='radio' name='noncore' value='1'".(($_POST['noncore'] == '1' || !isset($_POST['noncore'])) ? " checked='checked'" : "")." /> ".LAN_YES."&nbsp;&nbsp;
		<input type='radio' name='noncore' value='0'".($_POST['noncore'] == '0' ? " checked='checked'" : "")." /> ".LAN_NO."&nbsp;&nbsp;
		<input type='checkbox' name='nolang' value='1'".(($_POST['nolang'] == '1' || !isset($_POST['nolang'])) ? " checked='checked'" : "")." /> ".FC_LAN_23."&nbsp;&nbsp;
		</td>
		</tr>";
		
		$text .= "<tr>
		<td style='width: 35%'>
		".LAN_SHOW." ".FC_LAN_21.":
		</td>
		<td colspan='2' style='width: 65%'>
		<input type='radio' name='oldcore' value='1'".(($_POST['oldcore'] == '1' || !isset($_POST['oldcore'])) ? " checked='checked'" : "")." /> ".LAN_YES."&nbsp;&nbsp;
		<input type='radio' name='oldcore' value='0'".($_POST['oldcore'] == '0' ? " checked='checked'" : "")." /> ".LAN_NO."&nbsp;&nbsp;
		</td>
		</tr>";
		
		$text .= "<tr>
		<td style='width: 35%'>
		".FC_LAN_8.":
		</td>
		<td style='width: 65%; vertical-align: top'>
		<input type='radio' name='integrity' value='1'".(($_POST['integrity'] == '1' || !isset($_POST['integrity'])) ? " checked='checked'" : "")." /> ".LAN_YES."&nbsp;&nbsp;
		<input type='radio' name='integrity' value='0'".($_POST['integrity'] == '0' ? " checked='checked'" : "")." /> ".LAN_NO."&nbsp;&nbsp;
		</td></tr>";

		$text .= "</table>";
	
		$tab['basic'] = array('caption'=>LAN_OPTIONS, 'text'=>$text);
		
		if($pref['developer']) {

			$text2 = "<table class='table adminlist'>";
		/*	$text2 .= "<tr>
			<td class='fcaption' colspan='2'>".FC_LAN_17."</td>
			</tr>";*/
			
			$text2 .= "<tr>
			<td style='width: 35%'>
			".FC_LAN_18.":
			</td>
			<td colspan='2' style='width: 65%'>
			#<input class='tbox' type='text' name='regex' size='40' value='".htmlentities($_POST['regex'], ENT_QUOTES)."' />#<input class='tbox' type='text' name='mod' size='5' value='".$_POST['mod']."' />
			</td>
			</tr>";
			
			$text2 .= "<tr>
			<td style='width: 35%'>
			".FC_LAN_19.":
			</td>
			<td colspan='2' style='width: 65%'>
			<input type='checkbox' name='num' value='1'".(($_POST['num'] || !isset($_POST['num'])) ? " checked='checked'" : "")." />
			</td>
			</tr>";
			
			$text2 .= "<tr>
			<td style='width: 35%'>
			".FC_LAN_20.":
			</td>
			<td colspan='2' style='width: 65%'>
			<input type='checkbox' name='line' value='1'".(($_POST['line'] || !isset($_POST['line'])) ? " checked='checked'" : "")." />
			</td>
			</tr>";

			$text2 .= "
			</table>";

			$tab['advanced'] = array('caption'=>FC_LAN_17, 'text'=>$text2);
		}

		$tabText = e107::getForm()->tabs($tab);


		$foot = "
		<div class='buttons-bar center'>
		".$frm->admin_button('scan', LAN_GO, 'other')."
		</div>
		</form>
		</div>";

		$text = $head.$tabText.$foot;

		$ns->tablerender(FC_LAN_1, $text);
		
	}
	
	function scan($dir, $image)
	{
		$handle = opendir($dir.'/');

		while (false !== ($readdir = readdir($handle)))
		{
			
			if($readdir != '.' && $readdir != '..' && $readdir != '/' && $readdir != 'CVS' && $readdir != 'Thumbs.db' && (strpos('._', $readdir) === FALSE))
			{
				$path = $dir.'/'.$readdir;
				if(is_dir($path))
				{
					$dirs[$path] = $readdir;
				}
				elseif(!isset($image[$readdir]))
				{
					$files[$readdir] = $this->checksum($path, TRUE);
				}
			}
		}
		closedir($handle);
		
		if(isset($dirs)) 
		{
			ksort($dirs);
			
			foreach ($dirs as $dir_path => $dir_list) 
			{
				$list[$dir_list] = ($set = $this->scan($dir_path, $image[$dir_list])) ? $set : array();
			}
		}
		
		if(isset($files)) 
		{
			ksort($files);
			
			foreach ($files as $file_name => $file_list) 
			{
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
	  
		if($name == 'e_inspect.php') { return 'nocalc'; }		// Special case for plugin integrity checking

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

		if(isset($test_list[$filename])) { return $test_list[$filename]; }
		return 'check';
	}

	
	// This function does the real work
	//  $list -
	//	$deprecated
	// 	$level
	//	$dir
	//	&$tree_end
	//	&$parent_expand
	function inspect($list, $deprecated, $level, $dir, &$tree_end = null, &$parent_expand = null)
	{
		global $coredir;

		$sub_text = '';
		$langs = $this->langs;
		$lang_short = $this->lang_short;


		unset ($childOut);
		$parent_expand = false;

		if(substr($dir, -1) == '/')
		{
			$dir = substr($dir, 0, -1);
		}

		$dir_id = dechex(crc32($dir));
		$this->files[$dir_id]['.']['level'] = $level;
		$this->files[$dir_id]['.']['parent'] = $this->parent;
		$this->files[$dir_id]['.']['file'] = $dir;
		$directory = $level ? basename($dir) : SITENAME;
		$level++;
			
		$this->sendProgress(vartrue($this->count['core']['num']),$this->totalFiles,FR_LAN_1);	
			
		foreach ($list as $key => $value)
		{
	 		//   $dir_icon = 'fileinspector'; // default as unknown
			$this->parent = $dir_id;
			
			// Entry is a subdirectory - recurse another level
			if(is_array($value))
			{ 
				$path 		= $dir.'/'.$key;
				$child_open = false;
				$child_end 	= true;
				$dir_icon 	= 'folder_check';
				$sub_text 	.= $this->inspect($value, $deprecated[$key], $level, $path, $child_end, $child_expand);
				$tree_end 	= false;
		  		
		  		if($child_expand)
		  		{
					$parent_expand = true;
					$last_expand = true;
		  		}

			}
			else
			{
				$this->sendProgress(vartrue($this->count['core']['num']),$this->totalFiles,FR_LAN_1);	
			  	$path = $dir.'/'.$key;
		  
		  		$fid = strtolower($key);
		  		$this->files[$dir_id][$fid]['file'] = ($_POST['type'] == 'tree') ? $key : $path;

		  		// We're checking a file here
		  		if(($this->files[$dir_id][$fid]['size'] = filesize($path)) !== false)
		  		{	
		  			// Look at core files
					if($this->opt('core') != 'none')
					{		
						$this->count['core']['num']++;
					  	$this->count['core']['size'] += $this->files[$dir_id][$fid]['size'];

					  	// TODO Max out of Memory when used
			 			if($_POST['regex']) // Developer prefs activated - search file contents according to regex
			  			{                
							// Get contents of file
							$file_content = file($path);		

							if(($this->files[$dir_id][$fid]['size'] = filesize($path)) !== FALSE)
							{
								// Search string found - add file to list
								if($this->files[$dir_id][$fid]['lines'] = preg_grep("#".$_POST['regex']."#".$_POST['mod'], $file_content))
								{	
									$this->files[$dir_id][$fid]['file'] = ($_POST['type'] == 'tree') ? $key : $path;
									$this->files[$dir_id][$fid]['icon'] = 'file_core';
									$dir_icon = 'fileinspector';
									$parent_expand = TRUE;
									$this->results++;
								}
								// Search string not found - discard from list
								else
								{	
									unset($this->files[$dir_id][$fid]);
									$known[$dir_id][$fid] = true;
									$dir_icon = ($dir_icon == 'fileinspector') ?  'folder_unknown': $dir_icon ;
								}
							}
			  			}
			  			else
			  			{
			  				// Actually check file integrity
							if($this->opt('integrity'))
							{	
								switch ($this_action = $this->check_action($dir,$key))
							  	{
									case 'ignore' :
						    		case 'check' :
							  			if($this->checksum($path) != $value)
							  			{	
											$this->count['fail']['num']++;
											$this->count['fail']['size'] += $this->files[$dir_id][$fid]['size'];
											$this->files[$dir_id][$fid]['icon'] = 'file_fail';
											$dir_icon = 'folder_fail';
											$parent_expand = TRUE;
							  			}
									  	else
									  	{
											$this->count['pass']['num']++;
											$this->count['pass']['size'] += $this->files[$dir_id][$fid]['size'];

											if($this->opt('core') != 'fail')
											{
											  $this->files[$dir_id][$fid]['icon'] = 'file_check';
											  $dir_icon = ($dir_icon == 'folder_fail' || $dir_icon == 'folder_missing') ? $dir_icon : 'folder_check';
											}
											else
											{
												unset($this->files[$dir_id][$fid]);
											  	$known[$dir_id][$fid] = true;
											}
							  			}
							  		break;
									case 'uncalc' :
									case 'nocalc' :
								  		$this->count['uncalculable']['num']++;
								  		$this->count['uncalculable']['size'] += $this->files[$dir_id][$fid]['size'];
									
										if($this->opt('core') != 'fail')
										{
											$this->files[$dir_id][$fid]['icon'] = 'file_uncalc';
										}
										else
										{
											unset($this->files[$dir_id][$fid]);
											$known[$dir_id][$fid] = true;
										}
								  	break;
						  		}
							}
							// Just identify as core file
							else
							{	
								$this->files[$dir_id][$fid]['icon'] = 'file_core';
							}
			  			}
					}
					else
					{
			  			unset ($this->files[$dir_id][$fid]);
			  			$known[$dir_id][$fid] = true;
					}
		  		}
		  		elseif($this->opt('missing'))
		  		{
					switch ($this_action = $this->check_action($dir,$key))
					{
			  			case 'check' :
			  			case 'uncalc' :
							$this->count['missing']['num']++;
							$this->files[$dir_id][$fid]['icon'] = 'file_missing';
							$dir_icon = ($dir_icon == 'folder_fail') ? $dir_icon : 'folder_missing';
							$parent_expand = TRUE;
						break;
			  			case 'ignore' :
			  			case 'nocalc' :
			    			// These files can be missing without error - delete from the list
							unset ($this->files[$dir_id][$fid]);
							$known[$dir_id][$fid] = true;
			    		break;
					}
		 		}
		  		else
		  		{
					unset ($this->files[$dir_id][$fid]);
		 		}
			}
	  	}

		if($this->opt('noncore') || $this->opt('oldcore'))
		{
			if(!$handle = opendir($dir.'/'))
			{
				//e107::getMessage()->addInfo("Couldn't Open : ".$dir);
			}

			while (is_resource($handle) && false !== ($readdir = readdir($handle)))
			{
				// $prog_count = $this->count['unknown']['num'] + $this->count['deprecated']['num'];
				//	$this->sendProgress($prog_count,$this->totalFiles,FR_LAN_1);
				
				if(!in_array($readdir,$this->excludeFiles) && (strpos('._', $readdir) === false))
				{
					if(is_dir($dir.'/'.$readdir))
					{
						if(!isset($list[$readdir]) && ($level > 1 || $readdir == 'e107_install'))
						{
							$child_open = false;
							$child_end = true;
							$sub_text .= $this->inspect(array(), $deprecated[$readdir], $level, $dir.'/'.$readdir, $child_end, $child_expand);
							$tree_end = false;
							if($child_expand)
							{
								$parent_expand = true;
								$last_expand = true;
							}
						}
					}
					else 
					{
						if($this->opt('nolang') && !empty($langs) && !empty($lang_short)) // Hide Non-core Languages.
						{							
							// PHP Lang files. 		
							$lreg = "/[\/_](".implode("|",$langs).")/";						
							if(preg_match($lreg, $dir.'/'.$readdir))
							{
								continue;
							}
							
							// TinyMce Lang files. 									
							$lregs = "/[\/_](".implode("|",$lang_short).")_dlg\.js/";
							if(preg_match($lregs, $dir.'/'.$readdir))
							{
								continue;
							}	
							
							// PhpMailer Lang Files. 
							$lregsm = "/[\/_]phpmailer\.lang-(".implode("|",$lang_short).")\.php/";
							if(preg_match($lregsm, $dir.'/'.$readdir))
							{
								continue;
							}	
						}
						
						$aid = strtolower($readdir);

						if(!isset($this->files[$dir_id][$aid]['file']) && !$known[$dir_id][$aid])
						{
							if($this->checkKnownSecurity($dir.'/'.$readdir) === false)
							{
								if(isset($deprecated[$readdir]))
								 {
									if($this->opt('oldcore'))
									{
										$this->files[$dir_id][$aid]['file'] = ($_POST['type'] == 'tree') ? $readdir : $dir.'/'.$readdir;
										$this->files[$dir_id][$aid]['size'] = filesize($dir.'/'.$readdir);
										$this->files[$dir_id][$aid]['icon'] = 'file_old';
										$this->count['deprecated']['num']++;
										$this->count['deprecated']['size'] += $this->files[$dir_id][$aid]['size'];
										$dir_icon = 'folder_old';
									}
								}
								else
								{
									if($this->opt('noncore'))
									{
										$this->files[$dir_id][$aid]['file'] = ($_POST['type'] == 'tree') ? $readdir : $dir.'/'.$readdir;
										$this->files[$dir_id][$aid]['size'] = filesize($dir.'/'.$readdir);
										//echo "<br />dir: ".$dir.'/'.$readdir. " ( ".$this->files[$dir_id][$aid]['size'].")";
										$this->files[$dir_id][$aid]['icon'] = 'file_unknown';
										$this->count['unknown']['num']++;
										$this->count['unknown']['size'] += $this->files[$dir_id][$aid]['size'];
									}
								}
							}
							else
							{
								$this->files[$dir_id][$aid]['file'] = ($_POST['type'] == 'tree') ? $readdir : $dir.'/'.$readdir;
								$this->files[$dir_id][$aid]['size'] = filesize($dir.'/'.$readdir);
								$this->files[$dir_id][$aid]['icon'] = 'file_warning';
								$this->count['warning']['num']++;
								$this->count['warning']['size'] += $this->files[$dir_id][$aid]['size'];
								$this->count['deprecated']['num']++;
								$this->count['deprecated']['size'] += $this->files[$dir_id][$aid]['size'];
								$dir_icon = 'folder_warning';
								$parent_expand = TRUE;
							}

							$regexOpt = $this->opt('regex');
							if(!empty($regexOpt))
							{
								$file_content = file($dir.'/'.$readdir);
								if($this->files[$dir_id][$aid]['lines'] = preg_grep("#".$_POST['regex']."#".$_POST['mod'], $file_content))
								{
									$dir_icon = 'fileinspector';
									$parent_expand = TRUE;
									$this->results++;
								}
								else
								{
									unset($this->files[$dir_id][$aid]);
									$dir_icon = ($dir_icon == 'fileinspector') ? $dir_icon : 'folder';
								}
							}
							else
							{
								if(isset($deprecated[$readdir]))
								{
									if($this->opt('oldcore'))
									 {
										$dir_icon = ($dir_icon == 'folder_warning' || $dir_icon == 'folder_fail' || $dir_icon == 'folder_missing' ) ? $dir_icon : 'folder_old';
										$parent_expand = TRUE;
									}
								}
								else
								{
									if($this->opt('noncore'))
									{
										$dir_icon = ($dir_icon == 'folder_warning' || $dir_icon == 'folder_fail' || $dir_icon == 'folder_missing' || $dir_icon == 'folder_old' || $dir_icon == 'folder_old_dir') ? $dir_icon : 'folder_unknown';
										$parent_expand = TRUE;
									}
								}
							}
						} 
						elseif($this->opt('core') == 'none') 
						{
							unset($this->files[$dir_id][$aid]);
						}
					}
				}
			}
			closedir($handle);
			
		}

		$this->sendProgress($this->count['core']['num'],$this->totalFiles,FR_LAN_1);	
		
		$dir_icon = $dir_icon ? $dir_icon : 'folder_unknown';
		//	$icon = "<img src='".e_IMAGE."fileinspector/".$dir_icon."' class='i' alt='' />";

		$icon = $this->iconTag[$dir_icon];

		$tp = e107::getParser();

		$imgBlank = $tp->toImage('{e_IMAGE}fileinspector/blank.png', array(
			'alt'    => '',
			'legacy' => '{e_IMAGE}fileinspector/',
			'w'      => 9,
			'h'      => 9,
			'class'  => 'c',
		));

		$imgExpand = $tp->toImage('{e_IMAGE}fileinspector/expand.png', array(
			'alt'    => '',
			'legacy' => '{e_IMAGE}fileinspector/',
			'w'      => 15,
			'class'  => 'e',
			'id'     => 'e_' . $dir_id,
		));

		$imgContract = $tp->toImage('{e_IMAGE}fileinspector/contract.png', array(
			'alt'    => '',
			'legacy' => '{e_IMAGE}fileinspector/',
			'w'      => 15,
			'class'  => 'e',
			'id'     => 'e_' . $dir_id,
		));

		$hide = ($last_expand && $dir_icon != 'folder_core') ? "" : "style='display: none'";

		$text = '<div class="d" title="' . $this->getDiz($dir_icon) . '" style="margin-left: ' . ($level * 8) . 'px">';
		$text .= $tree_end ? $imgBlank : '<span onclick="ec(\'' . $dir_id . '\')">' . ($hide ? $imgExpand : $imgContract) . '</span>';
		$text .= '&nbsp;<span onclick="sh(\'f_' . $dir_id . '\')">' . $icon . '&nbsp;' . $directory . '</span>';
		$text .= $tree_end ? '' : '<div ' . $hide . ' id="d_' . $dir_id . '">' . $sub_text . '</div>';
		$text .= '</div>';
		
		$this->files[$dir_id]['.']['icon'] = $dir_icon;
		
		return $text;
	}

	private function checkKnownSecurity($path)
	{

		foreach($this->knownSecurityIssues as $v)
		{
			if(strpos($path, $v) !== false)
			{
				return true;
			}
		}

		return false;
	}



	function scan_results()
	{	
		global $core_image, $deprecated_image;
		$ns = e107::getRender();

		$scan_text = $this->inspect($core_image, $deprecated_image, 0, $this->root_dir);
		
		$this->sendProgress($this->totalFiles,$this->totalFiles,' &nbsp; &nbsp; &nbsp;');
	
		echo "<div style='display:block;height:30px'>&nbsp;</div>";

		if($this->opt('type') == 'tree')
		{
			$text = "<div style='text-align:center'>
			<table class='table adminlist'>
			<tr>
			<th class='fcaption' colspan='2'>".FR_LAN_2."</th>
			</tr>";

			$text .= "<tr style='display: none'><td style='width:60%'></td><td style='width:40%'></td></tr>";
		
			$text .= "<tr>
			<td style='width:60%;padding:0; '>
			<div style=' min-height:400px; max-height:800px; overflow: auto; padding-bottom:50px'>
			".$scan_text."
			</div>
			</td>
			<td style='width:40%; height:5000px; vertical-align: top; overflow:auto'><div>";
		} 
		else 
		{
			$text = "<div style='text-align:center'>
			<table class='table table-striped adminlist'>
			<tr>
			<th class='fcaption' colspan='2'>".FR_LAN_2."</th>
			</tr>";
			
			$text .= "<tr>
			<td colspan='2'>";
		}

		$text .= "<table class='table-striped table adminlist' id='initial'>";
		
		if($this->opt('type') == 'tree')
		{
			$text .= "<tr><th class='f' >".FR_LAN_3."</th>
			<th class='s' style='text-align: right; padding-right: 4px' onclick=\"sh('f_".dechex(crc32($this->root_dir))."')\">
			<b class='caret'></b></th></tr>";
		} 
		else 
		{
			$text .= "<tr><th class='f' colspan='2'>".FR_LAN_3."</th></tr>";
		}

		if($this->opt('core') != 'none')
		{
			$text .= "<tr><td class='f'>".$this->iconTag['file_core']."&nbsp;".FC_LAN_5.":&nbsp;".($this->count['core']['num'] ? $this->count['core']['num'] : LAN_NONE)."&nbsp;</td>
			<td class='s'>".$this->parsesize($this->count['core']['size'], 2)."</td></tr>";
		}
		if($this->opt('missing'))
		{
			$text .= "<tr><td class='f' colspan='2'>".$this->iconTag['file_missing']."&nbsp;".FC_LAN_13.":&nbsp;".($this->count['missing']['num'] ? $this->count['missing']['num'] : LAN_NONE)."&nbsp;</td></tr>";
		}
		if($this->opt('noncore'))
		{
			$text .= "<tr><td class='f'>".$this->iconTag['file_unknown']."&nbsp;".FC_LAN_7.":&nbsp;".($this->count['unknown']['num'] ? $this->count['unknown']['num'] : LAN_NONE)."&nbsp;</td><td class='s'>".$this->parsesize($this->count['unknown']['size'], 2)."</td></tr>";
		}
		if($this->opt('oldcore'))
		{
			$text .= "<tr><td class='f'>".$this->iconTag['file_old']."&nbsp;".FR_LAN_24.":&nbsp;".($this->count['deprecated']['num'] ? $this->count['deprecated']['num'] : LAN_NONE)."&nbsp;</td><td class='s'>".$this->parsesize($this->count['deprecated']['size'], 2)."</td></tr>";
		}
		if($this->opt('core') == 'all')
		{
			$text .= "<tr><td class='f'>".$this->iconTag['file']."&nbsp;".FR_LAN_6.":&nbsp;".($this->count['core']['num'] + $this->count['unknown']['num'] + $this->count['deprecated']['num'])."&nbsp;</td><td class='s'>".$this->parsesize($this->count['core']['size'] + $this->count['unknown']['size'] + $this->count['deprecated']['size'], 2)."</td></tr>";
		}
		
		if($this->count['warning']['num'])
		{
			$text .= "<tr><td colspan='2'>&nbsp;</td></tr>";
			$text .= "<tr><td style='padding-left: 4px' colspan='2'>
			".$this->iconTag['warning']."&nbsp;<b>".FR_LAN_26."</b></td></tr>";
		
			$text .= "<tr><td class='f'>".$this->iconTag['file_warning']." ".FR_LAN_28.": ".($this->count['warning']['num'] ? $this->count['warning']['num'] : LAN_NONE)."&nbsp;</td><td class='s'>".$this->parsesize($this->count['warning']['size'], 2)."</td></tr>";
			
			$text .= "<tr><td class='w' colspan='2'><div class='alert alert-warning'>".FR_LAN_27."</div></td></tr>";

		}
		if($this->opt('integrity') && ($this->opt('core') != 'none'))
		{
			$integrity_icon = $this->count['fail']['num'] ? 'integrity_fail.png' : 'integrity_pass.png';
			$integrity_text = $this->count['fail']['num'] ? '( '.$this->count['fail']['num'].' '.FR_LAN_19.' )' : '( '.FR_LAN_20.' )';
			$text .= "<tr><td colspan='2'>&nbsp;</td></tr>";
			$text .= "<tr><th class='f' colspan='2'>".FR_LAN_7." ".$integrity_text."</th></tr>";
		
			$text .= "<tr><td class='f'>".$this->iconTag['file_check']."&nbsp;".FR_LAN_8.":&nbsp;".($this->count['pass']['num'] ? $this->count['pass']['num'] : LAN_NONE)."&nbsp;</td><td class='s'>".$this->parsesize($this->count['pass']['size'], 2)."</td></tr>";
			$text .= "<tr><td class='f'>".$this->iconTag['file_fail']."&nbsp;".FR_LAN_9.":&nbsp;".($this->count['fail']['num'] ? $this->count['fail']['num'] : LAN_NONE)."&nbsp;</td><td class='s'>".$this->parsesize($this->count['fail']['size'], 2)."</td></tr>";
			$text .= "<tr><td class='f'>".$this->iconTag['file_uncalc']."&nbsp;".FR_LAN_25.":&nbsp;".($this->count['uncalculable']['num'] ? $this->count['uncalculable']['num'] : LAN_NONE)."&nbsp;</td><td class='s'>".$this->parsesize($this->count['uncalculable']['size'], 2)."</td></tr>";
		
			$text .= "<tr><td colspan='2'>&nbsp;</td></tr>";

			$text .= "<tr><td class='f' colspan='2'>".$this->iconTag['info']."&nbsp;".FR_LAN_10.":&nbsp;</td></tr>";

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
		
		if($this->opt('type') == 'tree' && !$this->results && $this->opt('regex'))
		{
			$text .= "</td></tr>
			<tr><td style='padding-right: 4px; text-align: center' colspan='2'><br />".FR_LAN_23."</td></tr>";
		}

		$text .= "</table>";
		
		if($this->opt('type') != 'tree')
		{
			$text .= "<br /></td></tr><tr>
			<td colspan='2'>
			<table class='table table-striped'>";
			if(!$this->results && $this->opt('regex'))
			{
				$text .= "<tr><td class='f' style='padding-left: 4px; text-align: center' colspan='2'>".FR_LAN_23."</td></tr>";
			}


		//	print_a($this->files);
		}




		foreach ($this->files as $dir_id => $fid) 
		{
		
			// $this->sendProgress($cnt,$this->totalFiles,$path);
		
			ksort($fid);
			$text .= ($this->opt('type') == 'tree') ? "<table class='t' style='display: none' id='f_".$dir_id."'>" : "";
			$initial = FALSE;
			foreach ($fid as $key => $stext)
			{

		//		print_a($stext);

				$iconKey = $stext['icon'];

				if(!$initial)
				{
					if($this->opt('type') == 'tree')
					{

						$rootIconKey = ($stext['level'] ? "folder_up" : "folder_root");

						$text .= "<tr><td class='f' title=\"".$this->getDiz($iconKey)."\" style='padding-left: 4px' ".($stext['level'] ? "onclick=\"sh('f_".$stext['parent']."')\"" : "").">";
						$text .= $this->iconTag[$rootIconKey];
						$text .=  ($stext['level'] ? "&nbsp;.." : "")."</td>
						<td class='s' style='text-align: right; padding-right: 4px' onclick=\"sh('initial')\">";
						$text .= $this->iconTag['folder_close'];
						$text .= "</td></tr>";
					}
				}
				else
				{
					if($this->opt('type') != 'tree')
					{
						$stext['file'] = str_replace($this->root_dir."/", "", $stext['file']);
					}

					$text .= $this->renderRow($stext);


				}
				$initial = TRUE;
			}
			$text .= ($this->opt('type') == 'tree') ? "</table>" : "";
		}
		
		if($this->opt('type') != 'tree') {
			$text .= "</td>
			</tr></table>";
		}

		$text .= "</td></tr>";
		
		$text .= "</table>
		</dit><br />";

		echo e107::getMessage()->render();
		echo $text;

		
	 //$ns->tablerender(FR_LAN_1.'...', $text);
		
	}


	function renderRow($stext)
	{

		$mode = $this->opt('core');

		$iconKey = $stext['icon'];

		//	return "<tr><td>".$mode." ( ".$iconKey.")</td></tr>";


		if($mode == 'full' && $iconKey == 'file_check' )
		{
			return '';
		}

		if($mode == 'none')
		{
			//		return '';
		}


		$text = '';
		$text .= "
			<tr>
			<td class='f ".$iconKey."' title=\"".$this->getDiz($iconKey)."\">".$this->iconTag[$iconKey]."&nbsp;".$stext['file']."&nbsp;";

			if($this->opt('regex'))
			{
				if($this->opt('num') || $this->opt('line'))
				{
					$text .= "<br />";
				}

				foreach ($stext['lines'] as $rkey => $rvalue)
				{
					if($this->opt('num'))
					{
						$text .= "[".($rkey + 1)."] ";
					}

					if($this->opt('line'))
					{
						$text .= htmlspecialchars($rvalue)."<br />";
					}
				}

				$text .= "<br />";
			}
			else
			{
				$text .= "</td>
					<td class='s'>".$this->parsesize($stext['size']);
			}

			$text .= "</td></tr>";

		return $text;
	}



	function create_image($dir) 
	{
		global $core_image, $deprecated_image,$coredir;
		
		foreach ($coredir as $trim_key => $trim_dirs) 
		{
			$search[$trim_key] 	= "'".$trim_dirs."'";
			$replace[$trim_key] = "\$coredir['".$trim_key."']";
		}
		
		$data = "<?php\n";
		$data .= "/*\n";
		$data .= "+ ----------------------------------------------------------------------------+\n";
		$data .= "|     e107 website system\n";
		$data .= "|\n";
		$data .= "|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)\n";
		$data .= "|     Copyright (C) 2008-2016 e107 Inc (e107.org)\n";
		$data .= "|\n";
		$data .= "|     Released under the terms and conditions of the\n";
		$data .= "|     GNU General Public License (http://gnu.org).\n";
		$data .= "|\n";
		$data .= "|     \$Source: /cvs_backup/e107_0.7/e107_admin/fileinspector.php,v $\n";
		$data .= "|     \$Revision$\n";
		$data .= "|     \$Id$\n";
		$data .= "|     \$Author$\n";
		$data .= "+----------------------------------------------------------------------------+\n";
		$data .= "*/\n\n";
		$data .= "if(!defined('e107_INIT')) { exit; }\n\n";
		
		$scan_current = ($_POST['snaptype'] == 'current') ? $this->scan($dir) : $core_image;
		$image_array = var_export($scan_current, true);
		$image_array = str_replace($search, $replace, $image_array);
		$data .= "\$core_image = ".$image_array.";\n\n";
		
		$scan_deprecated = ($_POST['snaptype'] == 'deprecated') ? $this->scan($dir, $core_image) : $deprecated_image;
		$image_array = var_export($scan_deprecated, true);
		$image_array = str_replace($search, $replace, $image_array);
		$data .= "\$deprecated_image = ".$image_array.";\n\n";

		$data .= "?>";
		$fp = fopen(e_ADMIN.'core_image.php', 'w');
		fwrite($fp, $data);
	}
	
	function snapshot_interface() 
	{
		$ns = e107::getRender();
		$frm = e107::getRender();
		$text = "";

		if(isset($_POST['create_snapshot'])) 
		{
			$this->create_image($_POST['snapshot_path']);
			$text = "<div style='text-align:center'>
			<form action='".e_SELF."' method='post' id='main_page'>
			<table class='table adminform'>snapshot_interface
			<tr>
			<td class='fcaption'>Snapshot Created</td>
			</tr>";
		
			$text .= "<tr>
			<td style='text-align:center'>
			The snapshot (".e_ADMIN."core_image.php) was successfully created.
			</td>
			</tr>
			<tr>
			<td style='text-align:center' class='forumheader'>".$frm->admin_button('main_page', 'Return To Main Page', 'submit')."</td>
			</tr>
			</table>
			</form>
			</div><br />";
		}
		
		$text .= "<div style='text-align:center'>
		<form action='".e_SELF."?".e_QUERY."' method='post' id='snapshot'>
		<table class='table adminform'>
		<tr>
		<td ccolspan='2'>Create Snapshot</td>
		</tr>";
		
		$text .= "<tr>
		<td style='width:50%'>
		Absolute path of root directory to create image from:
		</td>
		<td style='width:50%'>
		<input class='tbox' type='text' name='snapshot_path' size='60' value='".(isset($_POST['snapshot_path']) ? $_POST['snapshot_path'] : $this->root_dir)."' />
		</td></tr>
		
		<tr>
		<td style='width: 35%'>
		Create snapshot of current or deprecated core files:
		</td>
		<td colspan='2' style='width: 65%'>
		<input type='radio' name='snaptype' value='current'".($_POST['snaptype'] == 'current' || !isset($_POST['snaptype']) ? " checked='checked'" : "")." /> Current&nbsp;&nbsp;
		<input type='radio' name='snaptype' value='deprecated'".($_POST['snaptype'] == 'deprecated' ? " checked='checked'" : "")." /> Deprecated&nbsp;&nbsp;
		</td>
		</tr>
		
		<tr>
		<td class='forumheader' style='text-align:center' colspan='2'>".$frm->admin_button('create_snapshot', 'Create Snapshot', 'create')."</td>
		</tr>
		</table>
		</form>
		</div>";

		$ns->tablerender('Snapshot', $text);

	}
	
	function checksum($filename) 
	{
		$checksum = md5(str_replace(array(chr(13),chr(10)), "", file_get_contents($filename)));
		return $checksum;
	}
	
	function parsesize($size, $dec = 0) {
		$size = $size ? $size : 0;
		$kb = 1024;
		$mb = 1024 * $kb;
		$gb = 1024 * $mb;
		$tb = 1024 * $gb;
		if($size < $kb) {
			return $size." ".CORE_LAN_B;
		} elseif($size < $mb) {
			return round($size/$kb)." ".CORE_LAN_KB;
		} elseif($size < $gb) {
			return round($size/$mb, $dec)." ".CORE_LAN_MB;
		} elseif($size < $tb) {
			return round($size/$gb, $dec)." ".CORE_LAN_GB;
		} else {
			return round($size/$tb, $dec)." ".CORE_LAN_TB;
		}
	}
	
	function regex_match($file) {
		$file_content = file_get_contents($file);
		$match = preg_match($_POST['regex'], $file_content);
		
		return $match;
	}
	
	
	function sendProgress($rand,$total,$diz)
	{
		if($this->progress_units <40 && ($rand != $total))
		{
			$this->progress_units++;
			return;
		}
		else
		{
			$this->progress_units = 0;		
		}
		
		$inc = round(($rand / $total) * 100);
		
		if($inc == 0)
		{
			return;
		}
		
		
		echo "<div  style='display:block;position:absolute;top:20px;width:100%;'>
		<div style='width:700px;position:relative;margin-left:auto;margin-right:auto;text-align:center'>";
		
		$active = "active";
		
		if($inc >= 100)
		{
			$inc = 100;
			$active = "";
		}

		echo e107::getForm()->progressBar('inspector',$inc);
		
		/*	echo '<div class="progress progress-striped '.$active.'">
    			<div class="bar" style="width: '.$inc.'%"></div>
   		 </div>';*/


		echo "</div>
		</div>";

		return;


		//	exit;
		/*	
	    echo "<div style='margin-left:auto;margin-right:auto;border:2px inset black;height:20px;width:700px;overflow:hidden;text-align:left'>    
		<img src='".THEME."images/bar.jpg' style='width:".$inc."%;height:20px;vertical-align:top' />
		</div>";
		*/
		/*
		
		echo "<div style='width:100%;background-color:#EEEEEE'>".$diz."</div>";
		
		
		if($total > 0)
		{
			echo "<div style='width:100%;background-color:#EEEEEE;text-align:center'>".$inc ."%</div>";	
		}
		
		echo "</div>
		</div>";
		*/
	}
	
	
	function exploit_interface()
	{
		//	global $ns;
		$ns = e107::getRender();
		
		$query = http_build_query($_POST);
		
		$text = "

    	<iframe src='".e_SELF."?$query' width='96%' style='margin-left:0; width: 98%; height:100vh; min-height: 100000px; border: 0px' frameborder='0' scrolling='auto' ></iframe>

 		";
		 $ns->tablerender(FR_LAN_1, $text);
	}
		
	
	function headerCss()
	{
		$pref = e107::getPref();
				
		echo "<!-- *CSS* -->\n";
		$e_js =  e107::getJs();
		
		// Core CSS - XXX awaiting for path changes
		if(!isset($no_core_css) || !$no_core_css)
		{
			//echo "<link rel='stylesheet' href='".e_FILE_ABS."e107.css' type='text/css' />\n";
			$e_js->otherCSS('{e_WEB_CSS}e107.css');
		}	
					
				
		if(!deftrue('e_IFRAME') && isset($pref['admincss']) && $pref['admincss'])
		{
			$css_file = file_exists(THEME.'admin_'.$pref['admincss']) ? 'admin_'.$pref['admincss'] : $pref['admincss'];
			//echo "<link rel='stylesheet' href='".$css_file."' type='text/css' />\n";
			$e_js->themeCSS($css_file);
		}
		elseif(isset($pref['themecss']) && $pref['themecss'])
		{
			$css_file = file_exists(THEME.'admin_'.$pref['themecss']) ? 'admin_'.$pref['themecss'] : $pref['themecss'];
			//echo "<link rel='stylesheet' href='".$css_file."' type='text/css' />\n";
			$e_js->themeCSS($css_file);
		}
		else
		{
			$css_file = file_exists(THEME.'admin_style.css') ? 'admin_style.css' : 'style.css';
			//echo "<link rel='stylesheet' href='".$css_file."' type='text/css' />\n";
			$e_js->themeCSS($css_file);
		}
		
						
		$e_js->renderJs('other_css', false, 'css', false);
		echo "\n<!-- footer_other_css -->\n";
		
		// Core CSS
		$e_js->renderJs('core_css', false, 'css', false);
		echo "\n<!-- footer_core_css -->\n";
		
		// Plugin CSS
		$e_js->renderJs('plugin_css', false, 'css', false);
		echo "\n<!-- footer_plugin_css -->\n";
		
		// Theme CSS
		//echo "<!-- Theme css -->\n";
		$e_js->renderJs('theme_css', false, 'css', false);
		echo "\n<!-- footer_theme_css -->\n";
		
		// Inline CSS - not sure if this should stay at all!
		$e_js->renderJs('inline_css', false, 'css', false);
		echo "\n<!-- footer_inline_css -->\n";			
				
			
		/*
		echo "<!-- Theme css -->\n";
		if(strpos(e_SELF.'?'.e_QUERY, 'menus.php?configure') === FALSE && isset($pref['admincss']) && $pref['admincss'] && file_exists(THEME.$pref['admincss'])) {
			$css_file = file_exists(THEME.'admin_'.$pref['admincss']) ? THEME_ABS.'admin_'.$pref['admincss'] : THEME_ABS.$pref['admincss'];
			echo "<link rel='stylesheet' href='".$css_file."' type='text/css' />\n";
		} elseif(isset($pref['themecss']) && $pref['themecss'] && file_exists(THEME.$pref['themecss']))
		{
			$css_file = file_exists(THEME.'admin_'.$pref['themecss']) ? THEME_ABS.'admin_'.$pref['themecss'] : THEME_ABS.$pref['themecss'];
			echo "<link rel='stylesheet' href='".$css_file."' type='text/css' />\n";
		
		
		} 
		else 
		{
			$css_file = file_exists(THEME.'admin_style.css') ? THEME_ABS.'admin_style.css' : THEME_ABS.'style.css';
			echo "<link rel='stylesheet' href='".$css_file."' type='text/css' />\n";
		}
		if(!isset($no_core_css) || !$no_core_css) {
			echo "<link rel='stylesheet' href='".e_WEB_CSS."e107.css' type='text/css' />\n";
		}
		 * */
		 
	}
	
}

function fileinspector_adminmenu() //FIXME - has problems when navigation is on the LEFT instead of the right. 
{
	$var['setup']['text'] = FC_LAN_11;
	$var['setup']['link'] = e_SELF."?mode=setup";
	
	$var['run']['text'] = FR_LAN_2;
	$var['run']['link'] = e_SELF."?mode=run";

	$icon  = e107::getParser()->toIcon('e-fileinspector-24');
	$caption = $icon."<span>".FC_LAN_1."</span>";

	e107::getNav()->admin($caption, $_GET['mode'], $var);
}

function e_help()
{

	//	$fi = new file_inspector;
	$fi = e107::getSingleton('file_inspector');
	$list = $fi->getLegend();

	$text = '';
	foreach($list as $v)
	{
		if(!empty($v[1]))
		{
			$text .= "<div>".$v[0]." ".$v[1]."</div>";
		}

	}

	return array('caption'=>FC_LAN_37, 'text'=>$text); 

}


require_once(e_ADMIN.'footer.php');

function headerjs()
{
	/*$c = e_IMAGE_ABS . 'fileinspector/contract.png';
	$e = e_IMAGE_ABS . 'fileinspector/expand.png';

	$text = '<script type="text/javascript">
	function ec(element) {
		$("#d_"+element).stop().animate({"height": "toggle"}, { duration: 500 });
		var $img = $("#e_"+element);
	    if($img.attr("src") == "' . $e . '") {
	        $img.attr("src", "' . $c . '");
	    } else {
	       $img.attr("src", "' . $e . '");
	    }
	}

	function sh(element) {
		$("#"+element).stop().animate({"height": "toggle"}, { duration: 500 });
	}




</script>';*/

/*
 * // Start of rework
e107::js('footer-inline', "

c = new Image();
c = '".SITEURLBASE.e_IMAGE_ABS."fileinspector/contract.png';
e = '".SITEURLBASE.e_IMAGE_ABS."fileinspector/expand.png';

function ec(ecid) {
	icon = $('#e_' + ecid).src;
	if(icon == e) {
		$('#e_' + ecid).src = c;
	} else {
		$('#e_' + ecid).src = e;
	}
	div = $('#d_' + ecid).style;
	if(div.display == 'none')
	{
		div.display = '';
	}
	else
	{
		div.display = 'none';
	}
}

var hideid = 'initial';
function sh(showid)
{
	if(hideid != showid)
	{
		show = $('#'+showid).style;
		hide = $('#'+hideid).style;
		show.display = '';
		hide.display = 'none';
		hideid = showid;
	}
}




");*/


global $e107;
$text = "<script type='text/javascript'>
<!--
c = new Image(); c = '".SITEURLBASE.e_IMAGE_ABS."fileinspector/contract.png';
e = '".SITEURLBASE.e_IMAGE_ABS."fileinspector/expand.png';
function ec(ecid) {
	icon = document.getElementById('e_' + ecid).src;
	if(icon == e) {
		document.getElementById('e_' + ecid).src = c;
	} else {
		document.getElementById('e_' + ecid).src = e;
	}
	div = document.getElementById('d_' + ecid).style;
	if(div.display == 'none') {
		div.display = '';
	} else {
		div.display = 'none';
	}
}
var hideid = 'initial';
function sh(showid) {
	if(hideid != showid) {
		show = document.getElementById(showid).style;
		hide = document.getElementById(hideid).style;
		show.display = '';
		hide.display = 'none';
		hideid = showid;
	}
}
//-->
</script>";

$text .= "
<style type='text/css'>
<!--\n";
if(vartrue($_POST['regex'])) {
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
i.fa-folder-open-o, i.fa-times-circle-o { cursor:pointer }
-->
</style>\n";
		
return $text;
}

?>