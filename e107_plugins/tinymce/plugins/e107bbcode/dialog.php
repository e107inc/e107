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
	span.badge		{ cursor: pointer }
	span.label		{ cursor: pointer }

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
		
		
		$('span.label, span.badge').click(function () {
                var cls = $(this).attr('class');
                var html = '<span class=\"' + cls + '\">' + $(this).text() + '</span>&nbsp;';
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
				
	private $styleClasses = array(''=>'Default', 'primary'=>"Primary", 'success'=>"Success", 'info'=>"Info", 'warning'=>"Warning",'danger'=>"Danger",'inverse'=>"Inverse");
			
		
	
	function init()
	{
		$ns = e107::getRender();
		
		$text = '
		<ul class="nav nav-tabs">
		<li class="active" ><a href="#mbuttons" data-toggle="tab">Buttons</a></li>';
		
		$text .= '<li><a href="#badges" data-toggle="tab">Labels &amp; Badges</a></li>';
		
		$text .= '</ul>';
		 
		$text .= '<div class="tab-content">
		<div class="tab-pane active left" id="mbuttons">'.$this->buttonForm().'</div>';
		
		$text .= '<div class="tab-pane left" id="badges">'.$this->badgeForm().'</div>';
		
		$text .= '</div>';

		echo $text;
			
	}
	
	
	
	
	
	
	function buttonForm()
	{
		$frm = e107::getForm();
		
		$buttonSizes = array(''=>'Default', 'btn-mini'=>"Mini", 'btn-small'=>"Small", 'btn-large' => "Large");
		
		$buttonTypes = $this->styleClasses;
			
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



	function badgeForm()
	{
		unset($this->styleClasses['primary']);
		
		foreach($this->styleClasses as $key=>$type)
		{
			$classLabel = ($key != '') ? " label-".$key : "";
			$classBadge = ($key != '') ? " badge-".$key : "";	
			
			$text .= '<div style="padding:10px"><span class="label'.$classLabel.'">'.$type.'</span>&nbsp;';
			$text .= '<span class="badge'.$classBadge.'">'.$type.'</span>';
			$text .= "</div>";
		} 
		
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