global $pref;
if (preg_match("#\.php\?.*#",$code_text)){return "";}
$code_text = str_replace('"','&039;',$code_text);
unset($imgParms);
$imgParms['alt']='';
$imgParms['style']="vertical-align:middle; border:0";
if($parm) {
	$parm = preg_replace('#onerror *=#i','',$parm);
	parse_str($parm,$tmp);
	foreach($tmp as $p => $v) {
		$imgParms[$p]=$v;
	}
}
$parmStr="";
foreach($imgParms as $k => $v) {
	$parmStr .= "$k='{$v}' ";
}
if (!$postID) {
	return "<img src='{$code_text}' {$parmStr} />";
} else {
	if(strstr($postID,'class:')) {
		$uc = substr($postID,6);
	}
	if ($pref['image_post']) {
		if($uc == '') {
			if (!function_exists('e107_userGetuserclass')) {
				require_once(e_HANDLER.'user_func.php');
			}
			$uc = e107_userGetuserclass($postID);
		}
		if (check_class($pref['image_post_class'],$uc)) {
			return "<img src='{$code_text}' {$parmStr} />";
		}
		if ($pref['image_post_disabled_method']) {
			return '[ image disabled ]';
		} else {
			return "Image: $code_text";
		}
	}
}
