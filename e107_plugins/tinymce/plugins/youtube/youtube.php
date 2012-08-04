<?php
define('e_ADMIN_AREA',true);
require_once("../../../../class2.php");


define("e_IFRAME",true);
e107::css('inline',"

.sizeblock { display:inline-block; width: 180px; padding:4px }

");

e107::js('tinymce','tiny_mce_popup.js'); 
e107::js('inline',"

$(document).ready(function()
{
		$('#insert').click(function () {
					
			var url = $('#youtubeURL').val();
			var size = $('input:radio[name=size]:checked').val();
			var convert = {};
			
			convert['tiny'] 	= 'width:320px;height:205px';
			convert['small'] 	= 'width:560px;height:340px';
			convert['medium'] 	= 'width:640px;height:385px';
			convert['large'] 	= 'width:853px;height:505px';
			convert['huge'] 	= 'width:1280px;height:745px';
				
			if(url === null)
			{
				
				alert('Please enter a valid Youtube URL');
				return;
			}
				

        	var code, regexRes;
        	regexRes = url.match('[\\?&]v=([^&#]*)');
        	code = (regexRes === null) ? url : regexRes[1];
        
        	if(size == 'custom')
			{		
				var w = $('#width').val();
				var h = $('#height').val();
				size =  w + ',' + h;
				style = 'width:' + w + 'px;height:' + h + 'px';
			}
			else
			{
				var style = convert[size];
			}
				      
        	if (code === '') {
        		alert('Please enter a valid Youtube URL');
				return;	
        	}
			
			var s = '[youtube='+size+']'+code+'[/youtube]';
			
			var p = $.ajax({
					type: 'POST',
					url: '".e_PLUGIN_ABS."tinymce/plugins/e107bbcode/parser.php',
					data: { content: s, mode: 'tohtml' },
					async: false,

					dataType: 'html',
					success: function(html) {
				      return html;
				    }
				}).responseText;
			
			tinyMCEPopup.editor.execCommand('mceInsertContent', false, p);
      		 
		//	tinyMCEPopup.editor.execCommand('mceInsertContent', false, '<img class=\"youtube-' + size + '\" src=\"http://img.youtube.com/vi/' + code + '/0.jpg\"   alt=\"' + code + '\" style=\"' + style + '\" />');
       		parent.$.colorbox.close()	
		});
		
		
		$('#cancel').click(function () {
					
			parent.$.colorbox.close()	
		});
	
});		


",'jquery');


require_once(e_ADMIN."auth.php");
/*
	case 'tiny':
				$params['w'] = 320; // 200;
				$params['h'] = 205; // 180;
			break;
			
			case 'small':
				$params['w'] = 560; // 445;
				$params['h'] = 340; // 364;
			break;
			
			case 'medium':
				$params['w'] = 640; // 500;
				$params['h'] = 385; // 405;
			break;
			
			case 'big':
			case 'large':
				$params['w'] = 853; // 660;
				$params['h'] = 505; // 525;
			break;
			
			case 'huge':
				$params['w'] = 1280; // 980;
				$params['h'] = 745; // 765;
			break;
*/

$text = '<div><form onsubmit="YoutubeDialog.insert();return false;" action="#">
	<p><label for="youtubeURL">Youtube URL or Code</label>
    <input id="youtubeURL" name="youtubeURL" type="text" class="text" style="width:97%" autofocus="autofocus" /></p>

	<div class="sizeblock"><input type="radio" name="size" value="tiny" checked="checked" />Tiny (320 x 205)</div>
	<div class="sizeblock"><input type="radio" name="size" value="small" />Small (560 x 340)</div>
	<div class="sizeblock"><input type="radio" name="size" value="medium" />Medium (640 x 385)</div>
	<div class="sizeblock"><input type="radio" name="size" value="large" />Large (854 x 505)</div>
	<div class="sizeblock"><input type="radio" name="size" value="huge" />Huge (1280 x 745)</div>
	<div class="sizeblock"><input type="radio" name="size" value="custom" />Custom 
	<input type="text" id="width" name="width" value="" size="3" /> x <input type="text" id="height" name="height" value="" size="3" /></div>
	
	<div style="padding:10px">
		<input type="button" id="insert" name="insert" value="Insert"  />		
		<input type="button" id="cancel" name="cancel" value="'.LAN_CANCEL.'" />		
	</div>
</form>
</div>';

$ns = e107::getRender();
$ns->tablerender("Insert Youtube Video",$text);
require_once(e_ADMIN."footer.php");
exit;
?>