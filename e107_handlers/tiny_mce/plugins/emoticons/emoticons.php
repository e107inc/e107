<?php
require_once("../../../../class2.php");
if(!USER || check_class($pref['post_html']) == FALSE){
exit;
}
$HEADER = "";
require_once(HEADERF);


	$emotes = $sysprefs->getArray("emote_".$pref['emotepack']);
	$str = "<div class='spacer' style='white-space:wrap;width:130px;text-align:center'>";
    foreach($emotes as $key => $value){
		$key = str_replace("!", ".", $key);
		$key = preg_replace("#_(\w{3})$#", ".\\1", $key);
		$value2 = substr($value, 0, strpos($value, " "));
		$value = ($value2 ? $value2 : $value);
		$str .= "\n<a href='javascript:void(0);' onmousedown=\"javascript:insertEmotion('$key')\"><img src=\"".e_IMAGE_ABS."emotes/" . $pref['emotepack'] . "/$key\" style=\"border:0; padding-top:2px;\" alt=\"\" /></a> ";
	}

	$str .= "</div>";

 echo $str;

function headerjs(){
	global $pref;
	$js = "<script type='text/javascript' src='../../tiny_mce_popup.js'></script>";
	$js .= " <script type='text/javascript'>

    function init() {
		tinyMCEPopup.resizeToInnerSize();
	}

	function insertEmotion(file_name, title) {
		var html = '<img src=\'".e_IMAGE_ABS."emotes/".$pref['emotepack']."/' + file_name + '\' style=\'border:0px\' alt=\'' + file_name + '\' />';
		tinyMCE.execCommand('mceInsertContent', false, html);
		tinyMCEPopup.close();
	}
	";

	$js .= "</script>";
	return $js;

}

echo "</body></html>";


?>