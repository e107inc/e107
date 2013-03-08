<?php

define("e_ADMIN_AREA", true);
require_once("../../../../class2.php");
//e107::lan('core','admin',TRUE);
define("e_IFRAME",true);
require_once(e_ADMIN."auth.php");			


if(!USER || check_class($pref['post_html']) == FALSE){
exit;
}

e107::css('inline',"

	.selectEmote 	{ display:inline-block; cursor:pointer;margin:3px }
	body			{ text-align:center }
	.area			{ border-left: 1px solid rgb(221, 221, 221); border-bottom: 1px solid rgb(221, 221, 221);
					  background-color: rgb(246, 246, 246); margin-top:-1px 
					}

");

e107::js('tinymce','tiny_mce_popup.js'); 

e107::js('inline',"

$(document).ready(function()
{
		$('#insertButton').click(function () {
						
			var buttonType = $('input:radio[name=buttonType]:checked').val();
			var buttonSize = $('input:radio[name=buttonSize]:checked').val();
					
			var buttonText = $('#buttonText').val();
			var buttonUrl = $('#buttonUrl').val();
			
			var buttonClass = (buttonType != '') ? 'btn-'+buttonType : '';
					

			var html = '<a class=\"btn ' + buttonClass + ' ' + buttonSize + '\" href=\"' + buttonUrl + '\" >' + buttonText + '</a>';
		//	alert(html);		
			tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
			tinyMCEPopup.close();
		});
		
		$('#e-cancel').click(function () {
					
			tinyMCEPopup.close();
		});
		
});


",'jquery');


class e_bootstrap
{
	
	function init()
	{
		$ns = e107::getRender();
		
		$text = '
		<ul class="nav nav-tabs">
		<li class="active" ><a href="#mbuttons" data-toggle="tab">Buttons</a></li>';
		
	//	$text .= '<li><a href="#mprofile" data-toggle="tab">Profile</a></li>';
		
		$text .= '</ul>';
		 
		$text .= '<div class="tab-content">
		<div class="tab-pane active left" id="mbuttons">'.$this->buttonForm().'</div>';
		
	//	$text = '<div class="tab-pane" id="mprofile">Wow</div>';
		
		$text .= '</div>';

		echo $text;
			
	}
	
	
	
	
	
	
	function buttonForm()
	{
		$frm = e107::getForm();
		
		$buttonTypes = array(''=>'Default', 'primary'=>"Primary", 'success'=>"Success", 'info'=>"Info", 'warning'=>"Warning",'danger'=>"Danger",'inverse'=>"Inverse");
		$buttonSizes = array(''=>'Default', 'btn-mini'=>"Mini", 'btn-small'=>"Small", 'btn-large' => "Large");
			
		$butSelect = "";
		$butSelect .= "<div class='form-inline' style='padding:5px'>";	
		foreach($buttonTypes as $type=>$diz)
		{
			
			$label = '<button class="btn btn-'.$type.'" >'.$diz.'</button>';
			$butSelect .= $frm->radio('buttonType', $type, false, array('label'=>$label));
			
		}
		$butSelect .= "</div>";		
		
		$butSize = "<div class='form-inline' style='padding:5px'>";	
		
		foreach($buttonSizes as $size=>$label)
		{
			$selected = ($size == '') ? true : false;
			$butSize .= $frm->radio('buttonSize', $size, $selected, array('label'=>$label));	
		}
		$butSize .= "</div>";		
		
		
		
		$text = "
		<table class='table area'>
		<tr>
			<td>Button Style</td>
			<td>".$butSelect."</td>
		</tr>
		<tr>
			<td>Button Size</td>
			<td><p>".$butSize."</p></td>
		</tr>	
		<tr>
			<td>Button Text</td>
			<td><p>".$frm->text('buttonText',$value,50)."</p></td>
		</tr>
		<tr>
			<td>Button Url</td>
			<td><p>".$frm->text('buttonUrl','',255)."</p></td>
		</tr>
			
				
		</table>

		<div class='center'>". $frm->admin_button('insertButton','save','other',"Insert") ."
		<button class='btn ' id='e-cancel'>".LAN_CANCEL."</button>
		</div>";
		
		
		return $text;
		
	}
	
	
}

       
require_once(e_ADMIN."auth.php");			
//e107::lan('core','admin',TRUE);


$bootObj = new e_bootstrap;
$bootObj->init();





require_once(e_ADMIN."footer.php");
exit;
//


?>