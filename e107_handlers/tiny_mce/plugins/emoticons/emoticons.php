<?php
require_once("../../../../class2.php");
if(!USER || check_class($pref['post_html']) == FALSE){
exit;
}
$HEADER = "";
$FOOTER = "";
require_once(HEADERF);


	$emote = $sysprefs -> getArray('emote');
	$str = "<div class='spacer' style='text-align:center'>";
	$c = 0;
	$orig = array();
	while(list($code, $name) = @each($emote[$c])){
		if(!array_key_exists($name,$orig)){
	$str .= "\n<a href=\"javascript:insertEmotion('$name')\"><img src=\"".e_IMAGE."emoticons/$name\" style=\"border:0; padding-top:2px;\" alt=\"\" /></a> ";
			$orig[$name] = TRUE;
		}
		$c++;
	}

	$str .= "</div>";

 echo $str;

function headerjs(){
	global $IMAGES_DIRECTORY;
	$js = "<script type='text/javascript' src='../../tiny_mce_popup.js'></script>";
	$js .= " <script type='text/javascript'>
	function insertEmotion(file_name) {
		if (window.opener) {
			tinyMCE.insertImage('".SITEURL.$IMAGES_DIRECTORY."emoticons/' + file_name);
			window.close();
		}
	}
</script>";

return $js;

}

echo "</body></html>";


?>