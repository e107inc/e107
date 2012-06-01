<?php
/*
+ ----------------------------------------------------------------------------+
|	e107 website system - Converter for plugin.php to plugin.xml
|
|	$Source: /cvs_backup/e107_0.8/e107_files/utilities/pluginxmlgen.php,v $
|	$Revision$
|	$Date$
|	$Author$
+----------------------------------------------------------------------------+
*/

/*
Doesn't (can't) do everything, but sorts what it can.

Intended to be run from e107_files/utilities directory.

Usage: Just browse to this file and follow the prompts.

*/

require('../../class2.php');
require_once(e_HANDLER."file_class.php");
if (!check_class(e_UC_MAINADMIN))
{
  exit;
}

$fl = new e_file;
if ($pluginList = $fl->get_files(e_PLUGIN, "^plugin\.php$", "standard", 1))
{
  sort($pluginList);
}
foreach ($pluginList as $k => $p)
{
  $pluginList[$k]['shortpath'] = substr(str_replace(e_PLUGIN,"",$p['path']),0,-1);
}



function genFileSelect($name,$fl)
{
  $ret = "<select name='{$name}' class='tbox'>\n<option value=''>----</option>\n";
  foreach ($fl as $k => $f)
  {
    $ret .= "<option value='{$f['shortpath']}'>{$f['shortpath']}</option>\n";
  }
  $ret .= "</select>\n";
  return $ret;
}


define('TAB_CHAR',chr(9));

define('LAN_XMLGEN_01','Create a plugin.xml file from a plugin.php file');
define('LAN_XMLGEN_02','plugin.xml creation');
define('LAN_XMLGEN_03','Convert');
define('LAN_XMLGEN_04','Select plugin');
define('LAN_XMLGEN_05','No plugin selected - nothing changed');
define('LAN_XMLGEN_06','Processing directory: ');
define('LAN_XMLGEN_07','Conversion successful');
define('LAN_XMLGEN_08','Cannot write to file: ');
define('LAN_XMLGEN_09','Cannot open file for writing: ');
define('LAN_XMLGEN_10','Cannot read ');
define('LAN_XMLGEN_11','Copyright ');
define('LAN_XMLGEN_12','URL to check for updates ');
define('LAN_XMLGEN_13','(optional)');
define('LAN_XMLGEN_14','(Any existing plugin.xml file will be renamed to plugin.bak)');
define('LAN_XMLGEN_15','Cannot rename existing plugin.xml to plugin.bak');
define('LAN_XMLGEN_16','Cannot delete existing plugin.bak');
define('LAN_XMLGEN_17','Installation');
define('LAN_XMLGEN_18','Upgrade');
define('LAN_XMLGEN_19','Uninstallation');
define('LAN_XMLGEN_20','Installation Management');
define('LAN_XMLGEN_21','Installation required');
define('LAN_XMLGEN_22','(Not used if no installation required)');
define('LAN_XMLGEN_23','Yes');
define('LAN_XMLGEN_24','No');
define('LAN_XMLGEN_25','Type');
define('LAN_XMLGEN_26','Function');
define('LAN_XMLGEN_27','Class');
define('LAN_XMLGEN_28','File name');
define('LAN_XMLGEN_29','Function/method name');
define('LAN_XMLGEN_30','When');
define('LAN_XMLGEN_31','Pre');
define('LAN_XMLGEN_32','Post');
define('LAN_XMLGEN_33','Specify file name only - will default to \'.php\' if no extension specified');
define('LAN_XMLGEN_34','Class name');
define('LAN_XMLGEN_35','If specifying a function, leave the class name blank');
define('LAN_XMLGEN_36','');
define('LAN_XMLGEN_37','');
define('LAN_XMLGEN_38','');



$managementOptions = array(
  'headings' => array('rowname' => '&nbsp;', 'when' => LAN_XMLGEN_30, 'type' => LAN_XMLGEN_25, 'file' => LAN_XMLGEN_28, 'class' => LAN_XMLGEN_34, 'function' => LAN_XMLGEN_29),
  'install' => array('rowname' => LAN_XMLGEN_17, 'when' => TRUE, 'type' => TRUE, 'file' => TRUE, 'class' => TRUE, 'function' => TRUE),
  'uninstall' => array('rowname' => LAN_XMLGEN_19, 'when' => TRUE, 'type' => TRUE, 'file' => TRUE, 'class' => TRUE, 'function' => TRUE),
  'upgrade' => array('rowname' => LAN_XMLGEN_18, 'when' => TRUE, 'type' => TRUE, 'file' => TRUE, 'class' => TRUE, 'function' => TRUE),
  'help' => array('rowname' => '&nbsp;', 'when' => '&nbsp;', 'type' => '&nbsp;', 'file' => LAN_XMLGEN_33, 'class' => LAN_XMLGEN_35, 'function' => '&nbsp;')
);


$selectOptions = array(
  'when' => array('pre' => LAN_XMLGEN_31, 'post' => LAN_XMLGEN_32),
  'type' => array('fileFunction' => LAN_XMLGEN_26, 'classFunction' => LAN_XMLGEN_27)
);


// Writes a single value within open tag and close tag
function writeTag($tag,$value,$level=1)
{
  if (!$value) return '';
  return str_repeat(TAB_CHAR,$level)."<{$tag}>{$value}</{$tag}>\n";
}


// Writes a tag with some attributes
function writeTagList($tag,$values,$closeTag = TRUE,$level=1)
{
	if (!count($values)) return '';
	$ret = str_repeat(TAB_CHAR,$level).'<'.$tag;
	foreach ($values as $aname => $aval)
	{
		if ($aval)
		{
			$ret .= ' '.$aname.'="'.$aval.'"';
		}
	}
	if ($closeTag) { $ret .= ' /'; }
	$ret .= ">\n";
	return $ret;
}


function listPrefs($prefList, $arrayPrefList)
{
  if (!is_array($prefList)) return '';
  $text = '';
  foreach ($prefList as $k => $v)
  {
    if (is_array($v))
	{
	  $text .= TAB_CHAR.TAB_CHAR.'<pref name="'.$k.'" type="array">'."\n";
	  foreach ($v as $sk => $sv)
	  {
		$text .= str_repeat(TAB_CHAR,3).'	<key name="'.$sk.'" value="'.$sv.'" />'."\n";
	  }
	  $text .= TAB_CHAR.TAB_CHAR."</pref>\n";
	}
	else
	{
	  $text .= TAB_CHAR.TAB_CHAR.'<pref name="'.$k.'" value="'.$v.'" />'."\n";
	}
  }
  if (!is_array($arrayPrefList)) return $text;
  foreach ($arrayPrefList as $k => $v)
  {
	$text .= TAB_CHAR.TAB_CHAR.'<listPref name="'.$k.'" value="'.$v.'" />'."\n";
  }
  return $text;
}




function makeXML($pluginDir, $extras=array())
{
  if (substr($pluginDir,-1,1) != '/') $pluginDir .= '/';
  $sourceFile = $pluginDir.'plugin.php';
  $destFile  = $pluginDir.'plugin.xml';
  $backFile  = $pluginDir.'plugin.bak';
  $baseFolder = substr($pluginDir,0,-1);
//  echo $baseFolder;
  $baseFolder = substr($baseFolder,strrpos($baseFolder,'/')+1);
//  echo "Transcribing from {$sourceFile} to {$destFile}...<br />";
  $adminText = '';
  $mainPrefText = '';
  $manageText = '';
  $fileText  = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n\n";
  $fileText .= '<!-- $'.'Id: plugin.xml,v 0.0 2008/06/26 20:44:10 e107steved Exp '.'$ -'."->\n\n";	// Split it to stop message getting edited when this file committed!

  if (!is_readable($sourceFile))
  {
    return LAN_XMLGEN_10.$sourceFile."<br />";
  }

  include_once($sourceFile);
// Transcribe variables
	$fileText .= writeTagList('e107Plugin', 
						array('name' => $eplug_name,
							'version' => $eplug_version,
							'compatibility' => '0.8',
							'installRequired' => $extras['installationrequired'] ? 'true' : 'false'
							), FALSE);
	
	$fileText .= writeTagList('author',
 						array('name' => $eplug_author,
							'url' => $eplug_url,
							'email' => $eplug_email
							), TRUE);
  $fileText .= writeTag('description',$eplug_description);
  $fileText .= writeTag('readMe',$eplug_readme);
  $fileText .= writeTag('folder',$baseFolder);
  if (isset($eplug_comment_ids) && is_array($eplug_comment_ids))
  {
	foreach ($eplug_comment_ids as $cid)
	{
		if (is_numeric($cid)) $cid = '***'.$baseFolder.$cid.'***';			// Should be text - so draw attention to it
		$fileText .= writeTag('commentID', $cid);
	}
  }
  foreach ($extras as $k => $v)
  {
    if (in_array($k,array('copyright','update_url'))) $fileText .= writeTag($k,$v);
  }
  if (!varsettrue($extras['copyright']))
  {
	$fileText .= writeTag('copyright','Copyright e107 Inc e107.org, Licensed under GPL (http://www.gnu.org/licenses/gpl.txt)');
  }
  $baseFolder .= '/';
  // 'commentID' tags needed
  $adminText .= writeTag('configFile',$eplug_conffile,2);
  $adminText .= writeTag('icon',str_replace($baseFolder,'',$eplug_icon),2);
  $adminText .= writeTag('iconSmall',str_replace($baseFolder,'',$eplug_icon_small),2);
  $adminText .= writeTag('caption',$eplug_caption,2);
  $adminText .= writeTag('installDone',$eplug_done,2);
  $fileText .= writeTag('administration',"\n".$adminText.TAB_CHAR);
  if (varsettrue($eplug_link) && varsettrue($eplug_link_name) && varsettrue($eplug_link_url))
  {
	$fileText .= TAB_CHAR.'<menuLink name="'.$eplug_link_name.'" url="'.str_replace(e_PLUGIN,'',$eplug_link_url).'" />'."\n";
  }
  // Could add more menuLink options
  
  $fileText .= writeTag('mainPrefs',"\n".listPrefs($eplug_prefs, varset($eplug_array_pref,'')).TAB_CHAR);

  // Could add userclasses


  // Management section
  $temp = '';
  foreach ($extras as $k => $v)
  {
    if (in_array($k,array('install','uninstall', 'upgrade')))
	{
	  $temp1 = '';
	  foreach (array('when','type','file','class','function') as $t)
	  {
	    if (isset($v[$t])) $temp1 .= ' '.$t.'="'.$v[$t].'"';
	  }
	  if ($temp1)
	  {
		$temp .= TAB_CHAR.TAB_CHAR.'<'.$k.$temp1.' />'."\n";
	  }
	}
  }
  if ($temp)
  {	// Only add management section if something to add
	$fileText .= TAB_CHAR."<management>\n".$temp.TAB_CHAR."</management>\n";
  }

  
  $fileText .= "</e107Plugin>";	

  // All assembled - write file
  if (is_readable($backFile))
  {  // Delete any existing backup
    if (!unlink($backFile))
	{
	  return LAN_XMLGEN_16;
	}
  }

  if (is_readable($destFile))
  {	// Rename existing plugin.xml
    if (!rename($destFile,$backFile))
	{
	  return LAN_XMLGEN_15;
	}
  }

  if (($fh = fopen($destFile,'wt')) === FALSE)
  {
    return LAN_XMLGEN_09.$destFile."<br />";
  }
  if (fwrite($fh,$fileText) == FALSE)
  {
    return LAN_XMLGEN_08.$destFile."<br />";
  }
  fclose($handle);
  return LAN_XMLGEN_07;		// Return success
}




$HEADER = '';
$FOOTER = '';
require(HEADERF);


$message = '';
//========================================================
//						ACTION
//========================================================
if (isset($_POST['do_conversion']))
{
  if (varset($_POST['selected_plugin'],FALSE))
  {
    $extras['copyright'] = varset($_POST['copyright'],'');
    $extras['update_url'] = varset($_POST['update_url'],'');
	$extras['installationrequired'] = varset($_POST['installationrequired'],1);

	
	// Calculate the array of management features
  foreach ($managementOptions as $k => $v)
  {
	if ($k == 'headings') continue;
	foreach ($v as $r => $s)
	{
	  $el_name = $k.'_'.$r;
	  if (varset($_POST[$el_name]))
	  {
	    switch ($r)
		{
		  case 'rowname' :		// Shouldn't happen - but allow for it in case
		  case 'help' :
			break;
		  case 'when' :
		    $extras[$k][$r] = $_POST[$el_name];
			break;
		  case 'type' :
		    $extras[$k][$r] = $_POST[$el_name];
			break;
		  case 'file' :
  		    $extras[$k][$r] = $_POST[$el_name];
			if (strtolower(substr($extras[$k][$r],-4)) != '.php') $extras[$k][$r].= '.php';
			break;
		  case 'function' :
		  case 'class' :
  		    $extras[$k][$r] = $_POST[$el_name];
//		    switch ($extras[$k]['type'])
		    break;
		}
	  }
	}
	if (!isset($extras[$k]['file']) || (!isset($extras[$k]['function'])) || (isset($extras[$k]['type']) && ($extras[$k]['type'] == 'classFunction') && !isset($extras[$k]['class'])))
	{
		unset($extras[$k]);		// Incomplete definition
	}
  }



	$message = LAN_XMLGEN_06.e_PLUGIN.$_POST['selected_plugin']."<br />";
	$message .= makeXML(e_PLUGIN.$_POST['selected_plugin'], $extras);
  }
  else
  {
    $message = LAN_XMLGEN_05;
  }
}



//========================================================
//						FORM
//========================================================
$text = 
	"<div style='text-align:center; width:700px'>
	<form method='post' action='".e_SELF."'>
	<table style='width:95%' class='fborder'>
	<colgroup>
	<col style='width:60%' />
	<col style='width:40%' />
	</colgroup>";

if ($message)
{
  $text .= "<tr>
	  <td colspan='2' class='forumheader3' style='text-align:center'>".$message."
	  </td>
	</tr>";
}

$text .= "<tr>
	  <td colspan='2' class='forumheader3' style='text-align:center'>".LAN_XMLGEN_01."<br /><span class='smallblacktext'>".LAN_XMLGEN_14."</span></td>
	</tr>

	<tr>
	  <td class='forumheader3'>".LAN_XMLGEN_04."</td>
	  <td class='forumheader3'>".genFileSelect('selected_plugin',$pluginList)."
	  </td>
	</tr>

	<tr>
	  <td class='forumheader3'>".LAN_XMLGEN_11."<br /><span class='smallblacktext'>".LAN_XMLGEN_13."</span></td>
	  <td class='forumheader3'>
	    <input class='tbox' type='text' size='60' maxlength='100' name='copyright' value='' />
	  </td>
	</tr>

	<tr>
	  <td class='forumheader3'>".LAN_XMLGEN_12."<br /><span class='smallblacktext'>".LAN_XMLGEN_13."</span></td>
	  <td class='forumheader3'>
	    <input class='tbox' type='text' size='60' maxlength='150' name='update_url' value='' />
	  </td>
	</tr>

	<tr>
	  <td class='forumheader3'>".LAN_XMLGEN_21."</td>
	  <td class='forumheader3'>
	  <select name='installationrequired'>\n
	  <option value='1' selected='selected'>".LAN_XMLGEN_23."</option>\n
	  <option value='0'>".LAN_XMLGEN_24."</option>\n
	  </select>
	  </td>
	</tr>



	<tr><td class='forumheader3'>".LAN_XMLGEN_20."<br /><span class='smallblacktext'>".LAN_XMLGEN_22."</span></td><td class='forumheader3'>
	
	<table>";
foreach ($managementOptions as $k => $v)
{
	$text .= "<tr>";
	foreach ($v as $r => $s)
	{
	  if ($k == 'headings')
	  {
		$text .= '<td>'.$s.'</td>';
	  }
	  elseif ($k == 'help')
	  {
		$text .= "<td><span class='smallblacktext'>".$s.'</span></td>';
	  }
	  else
	  {
	    $el_name = $k.'_'.$r;
	    switch ($r)
		{
		  case 'rowname' :
			$text .= '<td>'.$s.'</td>';
			break;
		  case 'when' :
		  case 'type' :
		    if (!$s) 
			{
			  $text .= '<td>&nbsp;</td>';
			  break;
			}
			$text .= "<td><select name='{$el_name}' class='tbox'>\n";
			foreach ($selectOptions[$r] as $o => $t)
			{
			  $text .= "<option value='{$o}'>{$t}</option>\n";
			}
			$text .= "</select>\n</td>";
			break;
		  case 'file' :
		  case 'function' :
		  case 'class' :
			$text .= "<td>
				<input class='tbox' type='text' size='30' maxlength='60' name='{$el_name}' value='' />
			  </td>";
		    break;
		}
	  }
	}
	$text .= "</tr>";
}


$text .= "</table></td></tr>
	";


  $text .= "
	<tr>
	  <td class='forumheader3' colspan='3' style='text-align:center'>
		<input class='button' type='submit' name='do_conversion' value='".LAN_XMLGEN_03."' />
	  </td>
	</tr>";


$text .= "
	</table>\n
	</form>
	</div><br />";
	$ns->tablerender(LAN_XMLGEN_02, $text);

require(FOOTERF);


?>
