<?php

define("e_ADMIN_AREA", true);
require_once("../../../../class2.php");
//e107::lan('core','admin',TRUE);
define("e_IFRAME",true);
//require_once(e_ADMIN."auth.php");			


if(!USER || check_class($pref['post_html']) == FALSE){
exit;
}

e107::css('inline',"

	.selectEmote 		{ display:inline-block; cursor:pointer;margin:3px }
	body				{ text-align:center }
	.area				{  
					 	  margin-top:-1px; padding:20px; 
						}
	span.badge			{ cursor: pointer }
	span.label			{ cursor: pointer }
	ul.glyphicons		{ list-style:none; margin-left:0px; font-size:120%}
	ul.glyphicons  li	{ float:left;  cursor:pointer; width:190px; padding:5px; }
	a, li					{  outline: 0; }    

");

// e107::js('tinymce4','plugins/compat3x/tiny_mce_popup.js');

e107::js('inline',"

$(document).ready(function()
{
		$('#insertButton').click(function () {
						
			var buttonType = $('input:radio[name=buttonType]:checked').val();
			var buttonSize = $('input:radio[name=buttonSize]:checked').val();
					
			var buttonText = $('#buttonText').val();
			var buttonUrl = $('#buttonUrl').val();
			
			var buttonClass = (buttonType != '') ? 'btn-'+buttonType : '';
					

			var html = '<a class=\"btn ' + buttonClass + ' ' + buttonSize + '\" href=\"' + buttonUrl + '\" >' + buttonText + '</a>  ';
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
		
	
		$('ul.glyphicons li, #glyph-save').click(function () {
		
				var color = $('#glyph-color').val();	
				var custom = $('#glyph-custom').val();			
                var cls = (custom != '') ? custom : $(this).find('i').attr('class');	
	
                var html = '<i class=\"' + cls + '\"></i>&nbsp;';
				
			//	alert(html);
				tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
				tinyMCEPopup.close();
		});
	
		$('#bbcodeInsert').click(function () 
		{
				s = $('#bbcodeValue').val();
				s = s.trim(s);
	
				var html = $.ajax({
					type: 'POST',
					url: './parser.php',
					data: { content: s, mode: 'tohtml' },
					async       : false,

					dataType: 'html',
					success: function(html) {
				      return html;
				    }
				}).responseText;

				html = '<bbcode alt=\"'+encodeURIComponent(s)+'\">' + html + '</bbcode>   ' ;
alert(html);
				tinyMCEPopup.editor.execCommand('mceInsertContent', false, html);
				tinyMCEPopup.close();
		});
	
		$('a.bbcodeSelect').click(function () {
			var html = $(this).html();	
			$('#bbcodeValue').val(html);
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
		
		
		if(e_QUERY == 'bbcode')
		{
			echo $this->bbcodeForm();		
			return;
		}
					
				
			
		
		$text = "<div class='alert alert-warning'>Warning: These will only work if you have a bootstrap-based theme installed</div>";
		
		
		$text .= '
		<ul class="nav nav-tabs">';
		
		$text .= '<li class="active" ><a href="#mbuttons" data-toggle="tab">Buttons</a></li>';
		
		$text .= '<li><a href="#badges" data-toggle="tab">Labels &amp; Badges</a></li>';
	
		$text .= '<li><a href="#glyphs" data-toggle="tab">Glyphicons</a></li>';	
		
		$text .= '</ul>';
		 
		$text .= '<div class="tab-content">';
		
		$text .= '<div class="tab-pane active left" id="mbuttons">'.$this->buttonForm().'</div>';
		
		$text .= '<div class="tab-pane left" id="badges">'.$this->badgeForm().'</div>';
		
		$text .= '<div class="tab-pane left" id="glyphs">'.$this->glyphicons().'</div>';
		
	
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
			<td><p>".$frm->text('buttonUrl','',255,'size=xxlarge')."</p></td>
		</tr>
			
				
		</table>

		<div class='center'>". $frm->admin_button('insertButton','save','other',"Insert") ."
		<button class='btn btn-default btn-secondary ' id='e-cancel'>".LAN_CANCEL."</button>
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
			
			$text .= '<div class="area"><span class="label'.$classLabel.'">'.$type.'</span>&nbsp;';
			$text .= '<span class="badge'.$classBadge.'">'.$type.'</span>';
			$text .= "</div>";
		} 
		
		return $text;      
	
	}
	
	
	function bbcodeForm()
	{
		$list = e107::getPref('bbcode_list');
		
		$text .= "
		<h4>e107 Bbcodes</h4>
		<div class='well'>
		<table class='table table-striped'>
		<tr><th>Plugin</th>
		<th>Bbcode</th>
		</tr>
		";
		foreach($list as $plugin=>$val)
		{
			$text .= "<tr><td>".$plugin."</td>
			<td>";
			
			foreach($val as $bb=>$v)
			{
				$text .= "<a href='#' class='bbcodeSelect' style='cursor:pointer'>[".$bb."][/".$bb."]</a>";
			}
			$text .= "</td>
			</tr>";
		}
			
		$text .= "</table>
		</div>";
			
		$frm = e107::getForm();
		$text .= $frm->text('bbcodeValue','',false,'size=xlarge');
		$text .= $frm->button('bbcodeInsert','go','other','Insert');
			
		
		return $text;
				
		
	}
				
	
			
	function glyphicons()
	{
		$icons = array(
			"icon-glass",
            "icon-music",
            "icon-search",
            "icon-envelope",
            "icon-heart",
            "icon-star",
            "icon-star-empty",
            "icon-user",
            "icon-film",
            "icon-th-large",
            "icon-th",
            "icon-th-list",
            "icon-ok",
            "icon-remove",
            "icon-zoom-in",
            "icon-zoom-out",
            "icon-off",
            "icon-signal",
            "icon-cog",
            "icon-trash",
            "icon-home",
            "icon-file",
            "icon-time",
            "icon-road",
            "icon-download-alt",
            "icon-download",
            "icon-upload",
            "icon-inbox",
            "icon-play-circle",
            "icon-repeat",
            "icon-refresh",
            "icon-list-alt",
            "icon-lock",
            "icon-flag",
            "icon-headphones",
            "icon-volume-off",
            "icon-volume-down",
            "icon-volume-up",
            "icon-qrcode",
            "icon-barcode",
            "icon-tag",
            "icon-tags",
            "icon-book",
            "icon-bookmark",
            "icon-print",
            "icon-camera",
            "icon-font",
            "icon-bold",
            "icon-italic",
            "icon-text-height",
            "icon-text-width",
            "icon-align-left",
            "icon-align-center",
            "icon-align-right",
            "icon-align-justify",
            "icon-list",

            "icon-indent-left",
            "icon-indent-right",
            "icon-facetime-video",
            "icon-picture",
            "icon-pencil",
            "icon-map-marker",
            "icon-adjust",
            "icon-tint",
            "icon-edit",
            "icon-share",
            "icon-check",
            "icon-move",
            "icon-step-backward",
            "icon-fast-backward",
            "icon-backward",
            "icon-play",
            "icon-pause",
            "icon-stop",
            "icon-forward",
            "icon-fast-forward",
            "icon-step-forward",
            "icon-eject",
            "icon-chevron-left",
            "icon-chevron-right",
            "icon-plus-sign",
            "icon-minus-sign",
            "icon-remove-sign",
            "icon-ok-sign",

            "icon-question-sign",
            "icon-info-sign",
            "icon-screenshot",
            "icon-remove-circle",
            "icon-ok-circle",
            "icon-ban-circle",
            "icon-arrow-left",
            "icon-arrow-right",
            "icon-arrow-up",
            "icon-arrow-down",
            "icon-share-alt",
            "icon-resize-full",
            "icon-resize-small",
            "icon-plus",
            "icon-minus",
            "icon-asterisk",
            "icon-exclamation-sign",
            "icon-gift",
            "icon-leaf",
            "icon-fire",
            "icon-eye-open",
            "icon-eye-close",
            "icon-warning-sign",
            "icon-plane",
            "icon-calendar",
            "icon-random",
            "icon-comment",
            "icon-magnet",

            "icon-chevron-up",
            "icon-chevron-down",
            "icon-retweet",
            "icon-shopping-cart",
            "icon-folder-close",
            "icon-folder-open",
            "icon-resize-vertical",
            "icon-resize-horizontal",
            "icon-hdd",
            "icon-bullhorn",
            "icon-bell",
            "icon-certificate",
            "icon-thumbs-up",
            "icon-thumbs-down",
            "icon-hand-right",
            "icon-hand-left",
            "icon-hand-up",
            "icon-hand-down",
            "icon-circle-arrow-right",
            "icon-circle-arrow-left",
            "icon-circle-arrow-up",
            "icon-circle-arrow-down",
            "icon-globe",
            "icon-wrench",
            "icon-tasks",
            "icon-filter",
            "icon-briefcase",
            "icon-fullscreen"
       );					

		$frm = e107::getForm();
		$sel = array(''=>'Dark Gray','icon-white'=>'White');	
			
		$text .= "<div  class='area'>";
		$text .= "<div class='inline-form'>Color: ".$frm->select('glyph-color',$sel)."     Custom: ".$frm->text('glyph-custom','').$frm->button('glyph-save','Go')."</div>";	
					
		$text .= "<ul class='glyphicons well clearfix'>";
		
		$inverse = (e107::getPref('admincss') == "admin_dark.css") ? " icon-white" : "";
		
		foreach($icons as $ic)
		{
			$text .= '<li><i class="'.$ic.$inverse.'"></i> '.$ic.'</li>';
			$text .= "\n";
		}
					
		$text .= "</ul>";	
		$text .= "</div>";

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


