global $pref;
if (trim($code_text) == "") return ""; 						// Do nothing on empty file
if (preg_match("#\.php\?.*#",$code_text)){return "";}
global $IMAGES_DIRECTORY, $FILES_DIRECTORY, $e107;
$search = array('"', '{E_IMAGE}', '{E_FILE}', '{e_IMAGE}', '{e_FILE}');
$replace = array('&#039;', $e107->base_path.$IMAGES_DIRECTORY, $e107->base_path.$FILES_DIRECTORY, $e107->base_path.$IMAGES_DIRECTORY, $e107->base_path.$FILES_DIRECTORY);
$code_text = str_replace($search, $replace, $code_text);
unset($imgParms);
$imgParms['class']="bbcode";  
$imgParms['alt']='';

$code_text = $tp -> toAttribute($code_text);

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

// Only look for file if not a url - suspected bug in PHP 5.2.5 on XP
if((strpos($code_text,'../') === FALSE) && (strpos($code_text,'://') === FALSE) && file_exists(e_IMAGE."newspost_images/".$code_text))
{
    $code_text = e_IMAGE_ABS."newspost_images/".$code_text;
}
if (varsettrue($pref['image_post']) && check_class($pref['image_post_class']))
{
	return "<img src='".$code_text."' {$parmStr} />";
}
switch ($pref['image_post_disabled_method'])
{
	case '1' :
		return CORE_LAN17;
	case '2' :
		return '';
}
return CORE_LAN18.$code_text;
