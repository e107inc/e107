<?php
require_once("../../../../class2.php");
if(!USER || check_class($pref['post_html']) == FALSE){
exit;
}


define("e_IFRAME",true); //FIXME Not functioning on front-end yet. 
$HEADER = "";
$FOOTER = "";

e107::css('inline',"

.selectEmote { display:inline-block; cursor:pointer;margin:3px }

");

e107::js('tinymce','tiny_mce_popup.js'); 
e107::js('inline',"

$(document).ready(function()
{
		$('.selectEmote').click(function () {			
			var file_name = $(this).attr('src');
			var html = '<img src=\"' + file_name + '\" alt=\"\" />';
			tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
			parent.$.colorbox.close()	
		});
				
			
});


",'jquery');

			
		

require_once(HEADERF);


	$emotes = $sysprefs->getArray("emote_".$pref['emotepack']);
	$str = "<div style='text-align:center;margin:0px'><div class='spacer' style='white-space:wrap;width:180px;text-align:center'>";
    foreach($emotes as $key => $value){
		$key = str_replace("!", ".", $key);
		$key = preg_replace("#_(\w{3})$#", ".\\1", $key);
		$value2 = substr($value, 0, strpos($value, " "));
		$value = ($value2 ? $value2 : $value);
	//	$str .= "\n<a href='javascript:void(0);' onmousedown=\"javascript:insertEmotion('$key')\"><img src=\"".e_IMAGE_ABS."emotes/" . $pref['emotepack'] . "/$key\" style=\"border:0; padding-top:2px;\" alt=\"\" /></a> ";
	
		$str .= "\n<img class='selectEmote' src=\"".e_IMAGE_ABS."emotes/" . $pref['emotepack'] . "/$key\" style=\"border:0; padding-top:2px;\" alt=\"\" />";
		
	}

	$str .= "</div>
	</div>";

 echo $str;

function headerjs(){
	global $pref;
//	$js = "<script type='text/javascript' src='../../tiny_mce_popup.js'></script>";
	$js .= " <script type='text/javascript'>

 //   function init() {
//		tinyMCEPopup.resizeToInnerSize();
//	}

	function insertEmotion(file_name, title) {
		var html = '<img src=\'".e_IMAGE_ABS."emotes/".$pref['emotepack']."/' + file_name + '\' alt=\'' + file_name + '\' />';
		tinyMCE.execCommand('mceInsertContent', false, html);
		tinyMCEPopup.close();
	}
	";

	$js .= "</script>";
	return $js;

}

//require_once(FOOTERF);
//exit;

echo "</body></html>";
exit;

?>