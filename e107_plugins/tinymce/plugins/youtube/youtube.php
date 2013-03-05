<?php
define('e_ADMIN_AREA',true);
require_once("../../../../class2.php");


define("e_IFRAME",true);



e107::css('inline',"

	.sizeblock { display:inline-block; width: 180px; margin:6px; font-size:14px}
	input[type='radio'] {  vertical-align: middle; padding-right:10px; }
	.e-footer-info		{ color: silver }
	body 	{ background-color: #EEEEEE }
	label 	{ vertical-align:middle; line-height:12px}
");

e107::js('tinymce','tiny_mce_popup.js'); 
e107::js('inline',"

$(document).ready(function()
{
		$('#e-insert').click(function () {
					
			var url = $('#youtubeURL').val();
			var size = $('input:radio[name=size]:checked').val();
			var convert = {};
			
			convert['tiny'] 	= {width: '302px', height: '205px'}; 
			convert['small'] 	= {width: '560px', height: '340px'};
			convert['medium'] 	= {width: '604px', height: '385px'}; 
			convert['large'] 	= {width: '853px', height: '505px'};
			convert['huge'] 	= {width: '1280px',height: '745px'};
				
			if(url === null)
			{
				
				alert('Please enter a valid Youtube URL');
				return false;
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
				var w = convert[size]['width'];
				var h = convert[size]['height'];
			}
			
		//	alert('width='+w + ' height='+h);
				      
        	if (code === '') 
        	{
        		alert('Please enter a valid Youtube URL');
				return;	
        	}
			 
			 
			var html = '<iframe width=\"'+ w +'\" height=\"'+ h +'\" src=\"http://www.youtube.com/embed/YNrn-7zjmYw\" frameborder=\"0\" allowfullscreen></iframe>';
			
			tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
  	 		tinyMCEPopup.close();
			
			
		//	tinyMCEPopup.editor.execCommand('mceInsertContent', false, '<img class=\"youtube-' + size + '\" src=\"http://img.youtube.com/vi/' + code + '/0.jpg\"   alt=\"' + code + '\" style=\"' + style + '\" />');

      		
		});
		
		
		$('#e-cancel').click(function () {
					
			tinyMCEPopup.close();
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

$text = '<div>
<p>
	<input id="youtubeURL" placeholder="Youtube URL or Code" name="youtubeURL" type="text" class="text" style="width:97%" autofocus />
</p><label class="radio sizeblock"><input type="radio" name="size" value="tiny" checked="checked" /> Tiny (320 x 205)</label>
	<label class="radio sizeblock"><input type="radio" name="size" value="small" /> Small (560 x 340)</label>
	<label class="radio sizeblock"><input type="radio" name="size" value="medium" /> Medium (640 x 385)</label>
	<label class="radio sizeblock"><input type="radio" name="size" value="large" /> Large (854 x 505)</label>
	<label class="radio sizeblock form-inline"><input type="radio" name="size" value="huge" /> Huge (1280 x 745)</label>
	<label class="youtube-custom sizeblock" style="white-space:nowrap"><input type="radio" name="size" value="custom" /> Custom 
	<input class="span1" type="text" id="width" name="width" maxlength="4" value="" size="3" /> x <input class="span1" type="text" id="height" name="height" value="" size="3" maxlength="4" />
	</label>
	
	<div class="right" style="padding:10px">
		<input class="btn btn-primary" type="button" id="e-insert" name="insert" value="Insert"  />		
		<input class="btn" type="button" id="e-cancel" name="cancel" value="'.LAN_CANCEL.'"/>		
	</div>
</div>';

$ns = e107::getRender();
$ns->tablerender("Insert Youtube Video",$text);
require_once(e_ADMIN."footer.php");
exit;
?>