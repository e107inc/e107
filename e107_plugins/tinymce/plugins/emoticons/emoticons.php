<?php
require_once("../../../../class2.php");
if(!USER || check_class($pref['post_html']) == FALSE){
exit;
}


define("e_IFRAME",true); //FIXME Not functioning on front-end yet. 
// $HEADER = "";
// $FOOTER = "";

e107::css('inline',"

	.selectEmote { display:inline-block; cursor:pointer;margin:3px }
	body		{ text-align:center }
");

e107::js('tinymce','tiny_mce_popup.js'); 
e107::js('inline',"

$(document).ready(function()
{
		$('.selectEmote').click(function () {			
			var file_name = $(this).attr('src');
			var html = '<img src=\"' + file_name + '\" alt=\"\" />';
			tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
			tinyMCEPopup.close();
		});
		
		$('#e-cancel').click(function () {
					
			tinyMCEPopup.close();
		});
		
});


",'jquery');

			
		
e107::lan('core','admin',TRUE);

require_once(HEADERF);


	$emotes = $sysprefs->getArray("emote_".$pref['emotepack']);
	
	$str = "<div class='center btn-group' style='margin-bottom:20px'>";
    foreach($emotes as $key => $value)
    {
		$key = str_replace("!", ".", $key);
		$key = preg_replace("#_(\w{3})$#", ".\\1", $key);
		$value2 = substr($value, 0, strpos($value, " "));
		$value = ($value2 ? $value2 : $value);
			$str .= "\n<button class='btn btn-large selectEmote pull-left'>
			<img src=\"".e_IMAGE_ABS."emotes/" . $pref['emotepack'] . "/$key\" style=\"min-width:32px; min-height:32px; border:0px\" alt=\"\" />
			</button>";
		
	}

	$str .= "</div>
	<div class='right'>
	<button class='btn ' id='e-cancel'>".LAN_CANCEL."</button></div>
	";

$ns->tablerender("Emoticons",$str);



require_once(FOOTERF);
exit;



?>