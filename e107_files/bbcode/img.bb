if (preg_match("#\.php\?.*#",$code_text)){return "";}
$matches[1] = preg_replace('#onerror *=#','',$matches[1]);
unset($imgParms);
$imgParms['alt']='';
$imgParms['style']="vertical-align:middle; border:0";
if($parm) {
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
	if ($pref['image_post']) {
		if (!function_exists('e107_userGetuserclass')) {
			require_once(e_HANDLER.'user_func.php');
		}
		if (check_class($pref['image_post_class'],e107_userGetuserclass($poster_userid))) {
			return "<img src='{$code_text}' {$parmStr} />";
		}
		if ($pref['image_post_disabled_method']) {
			return '[ image disabled ]';
		} else {
			return "Image: $code_text";
		}
	}
}
