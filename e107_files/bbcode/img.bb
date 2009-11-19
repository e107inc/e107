// $Id: img.bb,v 1.14 2009-11-19 13:46:18 e107coders Exp $

// General purpose image bbcode. As well as the obvious insertion of a picture:
// 	a) if filname begins with 'th_' or 'thumb_', creates link to main image opening in new window
//	b) If filename contains '*', treats it as a wildcard, and displays a random image from all matching file names found
//
// Can use simple classes for float - e.g.:
// .floatleft {clear: right; float: left; margin: 0px 5px 5px 0px; padding:2px; border: 0px;}
// .floatright {clear: left; float: right; margin: 0px 0px 0px 5px; padding:2px; border: 0px;}
// Currently defaults class to 'floatnone' - overridden by bbcode

if (trim($code_text) == "") return ""; 						// Do nothing on empty file
if (preg_match("#\.php\?.*#",$code_text)){return "";}
$addlink = FALSE;

global $pref;


$search = array('"', '{E_IMAGE}', '{E_FILE}', '{e_IMAGE}', '{e_FILE}');
$replace = array('&#039;', e_IMAGE_ABS, e_FILE_ABS, e_IMAGE_ABS, e_FILE_ABS);
$replaceInt = array('&#039;', e_IMAGE, e_FILE, e_IMAGE, e_FILE);
$intName = str_replace($search, $replaceInt, $code_text);			// Server-relative file names
unset($imgParms);
$imgParms['class']="bbcode floatnone";  //  This will be overridden if a new class is specified

$imgParms['alt']='';

$code_text = str_replace($search, $replace, $code_text);
$code_text = $tp -> toAttribute($code_text);
$img_file = pathinfo($code_text);		// 'External' file name. N.B. - might still contain a constant such as e_IMAGE

if($parm)
{
	$parm = preg_replace('#onerror *=#i','',$parm);
	$parm = str_replace("amp;", "&", $parm);
	parse_str($parm,$tmp);
	foreach($tmp as $p => $v)
	{
		$imgParms[$p]=$v;
	}
}
$parmStr="";
foreach($imgParms as $k => $v)
{
  $parmStr .= $tp -> toAttribute($k)."='".$tp -> toAttribute($v)."' ";
}



// Select a random file if required
if (strpos($img_file['basename'],'*') !== FALSE)
{
	$fileList = array();
	$intFile = pathinfo($intName);		// N.B. - might still contain a constant such as e_IMAGE
	$matchString = '#'.str_replace('*','.*?',$intFile['basename']).'#';
	$dirName = $tp->replaceConstants($intFile['dirname'].'/');			// we want server-relative directory
	if (($h = opendir($dirName)) !== FALSE)
	{
		while (($f = readdir($h)) !== FALSE)
		{
			if (preg_match($matchString,$f))
			{
				$fileList[] = $f;		// Just need to note file names
			}
		}
		closedir($h);
	}
	else
	{
		echo "Error opening directory: {$dirName}<br />";
		return '';
	}
	if (count($fileList))
	{
		$img_file['basename'] = $fileList[mt_rand(0,count($fileList)-1)];		// Just change name of displayed file - no change on directory
		$code_text = $img_file['dirname']."/".$img_file['basename'];
	}
	else
	{
		echo 'No file: '.$code_text;
		return '';
	}
}


// Check for whether we can display image down here - so we can show image name if appropriate
if (!varsettrue($pref['image_post']) || !check_class($pref['image_post_class']))
{
	switch ($pref['image_post_disabled_method'])
	{
		case '1' :
			return CORE_LAN17;
		case '2' :
			return '';
	}
	return CORE_LAN18.$code_text;
}


// Check for link to main image if required
if (strpos($img_file['basename'],'th_') === 0)
{
	$addlink = TRUE;
	$main_name = $img_file['dirname']."/".substr($img_file['basename'],3);     // delete the 'th' prefix from file name
}
elseif (strpos($img_file['basename'],'thumb_') === 0)
{
	$addlink = TRUE;
	$main_name = $img_file['dirname']."/".substr($img_file['basename'],6);     // delete the 'thumb' prefix from file name
}


if ($addlink)
{
	return "<a href='".$main_name."' rel='external'><img src='".$code_text."' {$parmStr} /></a>";
}
else
{
	return "<img src='".$code_text."' {$parmStr} />";
}
