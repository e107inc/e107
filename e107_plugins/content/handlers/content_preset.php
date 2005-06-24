<?php
require_once("../../../class2.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;

$lan_file = e_PLUGIN."content/languages/".e_LANGUAGE."/lan_content.php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."content/languages/English/lan_content.php");

if(isset($_POST['addpreset'])){
	if(!$_POST['field']){
		$err = "<br /><b>".CONTENT_PRESET_LAN_0."</b><br /><br />";
	}else{
		$err = "";
		if($_POST['type'] == "text"){
			if(!($_POST['text_size'] && $_POST['text_maxsize'] && is_numeric($_POST['text_size']) && is_numeric($_POST['text_maxsize'])) ){
				$err .= "<br /><b>".CONTENT_PRESET_LAN_1."<br />".CONTENT_PRESET_LAN_3."</b><br />";
			}else{
				$value = $_POST['field']."^".$_POST['type']."^".$_POST['text_size']."^".$_POST['text_maxsize'];
			}
		}
		if($_POST['type'] == "area"){
			if(!($_POST['area_cols'] && $_POST['area_rows'] && is_numeric($_POST['area_cols']) && is_numeric($_POST['area_rows'])) ){
				$err .= "<br /><b>".CONTENT_PRESET_LAN_1."<br />".CONTENT_PRESET_LAN_4."</b><br />";
			}else{
				$value = $_POST['field']."^".$_POST['type']."^".$_POST['area_cols']."^".$_POST['area_rows'];
			}
		}
		if($_POST['type'] == "select"){
			$options = implode("^", $_POST['options']);
			if(!$_POST['options']){
				$err .= "<br /><b>".CONTENT_PRESET_LAN_1."<br />".CONTENT_PRESET_LAN_5."</b><br />";
			}else{
				$value = $_POST['field']."^".$_POST['type']."^".$options;
			}
		}
		if($_POST['type'] == "date"){
			if(!($_POST['date_year_from'] && $_POST['date_year_to'] && is_numeric($_POST['date_year_from']) && is_numeric($_POST['date_year_to'])) ){
				$err .= "<br /><b>".CONTENT_PRESET_LAN_1."<br />".CONTENT_PRESET_LAN_6."</b><br />";
			}else{
				$value = $_POST['field']."^".$_POST['type']."^".$_POST['date_year_from']."^".$_POST['date_year_to'];
			}
		}
		if($_POST['type'] == "checkbox"){
			if(!$_POST['checkbox_value']){
				$err .= "<br /><b>".CONTENT_PRESET_LAN_1."<br />".CONTENT_PRESET_LAN_20."</b><br />";
			}else{
				$value = $_POST['field']."^".$_POST['type']."^".$_POST['checkbox_value'];
			}
		}
		if($_POST['type'] == "radio"){			
			if(!($_POST['radio_value'] && $_POST['radio_text'] && $_POST['radio_value']!="" && $_POST['radio_text']!="")){
				$err .= "<br /><b>".CONTENT_PRESET_LAN_1."<br />".CONTENT_PRESET_LAN_19."</b><br />";
			}else{
				if(count($_POST['radio_value']) != count($_POST['radio_text'])){
					$err .= CONTENT_PRESET_LAN_19;
				}else{
					for($i=0;$i<count($_POST['radio_text']);$i++){
						$radio .= $_POST['radio_text'][$i]."^".$_POST['radio_value'][$i]."^";
					}
					$radio = substr($radio,0,-1);
					$value = $_POST['field']."^".$_POST['type']."^".$radio;
				}
			}
		}
	}
	if(!$err){

		$js = "		
		<script type='text/javascript'>

		var ecopy		= 'upline_new';
		var epaste		= 'div_content_custom_preset';
		var type		= window.opener.document.getElementById(ecopy).nodeName; // get the tag name of the source copy.
		var br			= window.opener.document.createElement('br');

		var field		= window.opener.document.createElement('input');
		field.type		= 'text';
		field.size		= '50';
		field.name		= 'content_custom_preset_key[]';
		field.value		= '".$value."';
		field.className	= 'tbox';

		var but			= window.opener.document.createElement('input');
		but.type		= 'button';
		but.value		= 'x';
		but.className	= 'button';
		but.onclick		= function(){ this.parentNode.parentNode.removeChild(this.parentNode); };

		var destination = window.opener.document.getElementById(epaste);
		var source      = window.opener.document.getElementById(ecopy).cloneNode(true);
		var newentry	= window.opener.document.createElement(type);

		newentry.appendChild(source);
		newentry.value='';
		newentry.appendChild(br);
		newentry.appendChild(field);
		newentry.appendChild(but);
		newentry.appendChild(br);

		destination.appendChild(newentry);

		window.close();
		</script>\n
		";
	}
}


$text .= "
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd'>\n
<html>\n
<head>\n
<title>".CONTENT_PRESET_LAN_7."</title>\n
<script type='text/javascript' src='../../../e107_files/e107.js'></script>
".$js."
<style type='text/css'>\n
html,body{
	text-align:center;
	font-family: verdana, arial, helvetica, tahoma, sans-serif;
	font-size: 11px;
	color: #444;
	margin-left: auto;
  	margin-right: auto;	
  	margin-top:0px;
	margin-bottom:0px;
	padding: 0px;
	background-color:#FFF;
	height:100%;
	cursor:default;
}
td{
	padding:5px;
	font-size:11px;
	text-align:left;
}
.fborder{
	width:80%;
	margin:0;
	padding:0;
}
.fcaption{
	font-size:12px;
	font-weight:bold;
	white-space:nowrap;
}
.err{
	font-size:11px;
	font-weight:bold;
	color: #FF0000;
}
.leftcell{
	width:10%;
	white-space:nowrap;
}
.button{
	padding:2px;
	cursor:pointer;
}
</style>
</head>
<body>";

$qs = explode(".", e_QUERY);

$text .= "
<form method='post' action='".e_SELF."?".e_QUERY."'>\n
<table class='fborder'>
".($err ? "<tr><td colspan='2' class='err' style='padding-bottom:10px;'>".$err."</td></tr>" : "")."
<tr><td colspan='2' class='fcaption' style='padding-bottom:10px;'>".CONTENT_PRESET_LAN_8." : ".$qs[0]."</td></tr>
<tr><td class='leftcell'>".CONTENT_PRESET_LAN_9."</td><td>".$rs -> form_text("field", 40, $_POST['field'], 50)."</td></tr>";

if($qs[0] == "text"){
	$text .= "
	<tr><td class='leftcell'>".CONTENT_PRESET_LAN_10."</td><td>".$rs -> form_text("text_size", 3, $_POST['text_size'], 3)."</td></tr>
	<tr><td class='leftcell'>".CONTENT_PRESET_LAN_11."</td><td>".$rs -> form_text("text_maxsize", 3, $_POST['text_maxsize'], 3)."</td></tr>
	";
}

if($qs[0] == "area"){
	$text .= "
	<tr><td class='leftcell'>".CONTENT_PRESET_LAN_12."</td><td>".$rs -> form_text("area_cols", 3, $_POST['area_cols'], 3)."</td></tr>
	<tr><td class='leftcell'>".CONTENT_PRESET_LAN_13."</td><td>".$rs -> form_text("area_rows", 3, $_POST['area_rows'], 3)."</td></tr>
	";
}

if($qs[0] == "date"){
	$text .= "
	<tr><td class='leftcell'>".CONTENT_PRESET_LAN_14."</td><td>".$rs -> form_text("date_year_from", 3, $_POST['date_year_from'], 4)."</td></tr>
	<tr><td class='leftcell'>".CONTENT_PRESET_LAN_15."</td><td>".$rs -> form_text("date_year_to", 3, $_POST['date_year_to'], 4)."</td></tr>
	";
}

if($qs[0] == "select"){
	$text .= "
	<tr><td class='leftcell' style='vertical-align:top;'>".CONTENT_PRESET_LAN_16."</td>
		<td>
		<div id='select_container' style='width:40%;white-space:nowrap;'>
		<span id='selectline' style='white-space:nowrap;'>
			<input class='tbox' type='text' name='options[]' size='10' maxlength='50' />
		<input type='button' class='button' value='".CONTENT_PRESET_LAN_17."' onclick=\"duplicateHTML('selectline','select_container');\"  />
		</span><br />
		</div>
	</td></tr>
	";
}
if($qs[0] == "checkbox"){
	//form_checkbox($form_name, $form_value, $form_checked = 0, $form_tooltip = "", $form_js = "")
	$text .= "
	<tr><td class='leftcell'>".CONTENT_PRESET_LAN_22."</td><td>".$rs -> form_text("checkbox_value", 3, $_POST['checkbox_value'], 4)."</td></tr>
	";
}
if($qs[0] == "radio"){
	//form_radio($form_name, $form_value, $form_checked = 0, $form_tooltip = "", $form_js = "")
	$text .= "
	<tr><td class='leftcell'></td>
		<td>
		<div id='radio_container' style='width:40%;white-space:nowrap;'>
		<span id='radioline' style='white-space:nowrap;'>
			".CONTENT_PRESET_LAN_21." <input class='tbox' type='text' name='radio_text[]' value='".$_POST['radio_text[]']."' size='8' maxlength='50' />
			".CONTENT_PRESET_LAN_22." <input class='tbox' type='text' name='radio_value[]' value='".$_POST['radio_value[]']."' size='2' maxlength='50' />
		<input type='button' class='button' value='".CONTENT_PRESET_LAN_17."' onclick=\"duplicateHTML('radioline','radio_container');\"  />
		</span><br />
		</div>
	</td></tr>
	";
}

//process button
$text .= "
<tr><td class='leftcell'></td><td style='text-align:right;'><input type='hidden' name='type' value='".$qs[0]."' /><input type='submit' class='button' name='addpreset' value='".CONTENT_PRESET_LAN_18."' /></td></tr>\n
</table>\n
</form>\n
</body>\n
</html>\n";

echo $text;

?>