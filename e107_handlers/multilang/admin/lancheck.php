<?php
// Original code from McFly
// Moved to be used multilanguage system

if(file_exists(e_LANGUAGEDIR.e_LANGUAGE."admin/lan_lancheck.php")){
	require_once(e_LANGUAGEDIR.e_LANGUAGE."admin/lan_lancheck.php");
}else{
	require_once(e_LANGUAGEDIR."English/admin/lan_lancheck.php");
}
function show_comparison($language,$filename){
	global $LANGUAGES_DIRECTORY;
	$English = get_lan_phrases("English");
	$check = get_lan_phrases($language);
	
	$keys = array_keys($English[$filename]);
	natsort($keys);
	$ret .= "<table class='fborder'>
	<tr>
	<td class='fcaption' style='text-align:center;'>".LAN_CHECK_7."</td>
	<td class='fcaption' style='text-align:center;'>{$LANGUAGES_DIRECTORY}<br />English/{$filename}</td>
	<td class='fcaption' style='text-align:center;'>{$LANGUAGES_DIRECTORY}<br />{$language}/{$filename}</td>
	</tr>";
	foreach($keys as $k){
		$ret .= "<tr>
		<td class='forumheader3'>{$k}</td>
		<td class='forumheader3'>\"{$English[$filename][$k]}\"</td>
		";
		if(isset($check[$filename][$k])){
			$ret .= "<td class='forumheader3'>\"{$check[$filename][$k]}\"</td>";
		} else {
			$ret .= "<td class='forumheader'>".LAN_CHECK_5."</td>";
		}
		$ret .= "</tr>";
	}
	$ret .= "</table>";
	return $ret;
}


function get_lan_phrases($lang){
	$ret = array();
// Read English lan_ files
	$base_dir = e_LANGUAGEDIR.$lang;
	if($r = opendir($base_dir)){
		while($file = readdir($r)){
			$fname = $base_dir."/".$file;
			if(preg_match("#^lan_#",$file) && is_file($fname)){
				$data = file($fname);
				foreach($data as $line){
					if(preg_match("#\"(.*?)\".*?\"(.*)\"#",$line,$matches)){
						$ret[$file][$matches[1]]=htmlentities($matches[2]);
					}
				}
			}
		}
		closedir($r);
	}
// Read $lang/admin lan_ files
	$base_dir = e_LANGUAGEDIR.$lang."/admin";
	if($r = opendir($base_dir)){
		while($file = readdir($r)){
			$fname = $base_dir."/".$file;
			if(preg_match("#^lan_#",$file) && is_file($fname)){
				$data = file($fname);
				foreach($data as $line){
					if(preg_match("#\"(.*?)\".*?\"(.*)\"#",$line,$matches)){
						$ret["admin/".$file][$matches[1]]=$matches[2];
					}
				}
			}
		}
		closedir($r);
	}
	return $ret;
}



function check_core_lanfiles($checklan){
	$err_int_lg = 0;
	$err2_int_lg = 0;
	$English = get_lan_phrases("English");
	$check = get_lan_phrases($checklan);
	
	$text .= "<table class='fborder'>";
	$keys = array_keys($English);
	sort($keys);
	foreach($keys as $k){
		$lnk = "<a href='?lancheck.{$checklan}.{$k}'>{$k}</a>";
		if(array_key_exists($k,$check)){
			$text .= "<tr><td class='forumheader3'>{$lnk}</td>";
			$subkeys = array_keys($English[$k]);
			sort($subkeys);
			$er="";
			foreach($subkeys as $sk){
				if(!array_key_exists($sk,$check[$k])){
					$er .= ($er) ? "<br />" : "";
					$er .= $sk." ".LAN_CHECK_5;
				}
			}
			if($er){
				$text .= "<td class='forumheader2'><div class='smalltext'>{$er}</div></td></tr>";
				$err2_int_lg++;
			} else {
				$text .= "<td class='forumheader3'><div class='smalltext'>".LAN_CHECK_6."</div></td></tr>";
			}
		} else {
			$text .= "<tr><td class='forumheader3'>{$lnk}</td><td class='forumheader'>".LAN_CHECK_4."</td></tr>";
			$err_int_lg++;
		}
//		$text .= "$k<br />";
	}
	$text .= "</table>";
	
	
	
	if($err_int_lg == 1){
		$text1 = "<b>".LAN_CHECK_10."</b> ".LAN_CHECK_8."<br /><br />";
	}else if($err_int_lg > 1){
		$text1 = "<b>".LAN_CHECK_10."</b> ".$err_int_lg.LAN_CHECK_9."<br /><br />";
	}else{
		$text1 = "<b>".LAN_CHECK_11."</b><br /><br />";
	}
	
	if($err2_int_lg == 1){
		$text2 = "<b>".LAN_CHECK_10."</b> ".LAN_CHECK_12."<br /><br />";
	}else if($err2_int_lg > 1){
		$text2 = "<b>".LAN_CHECK_10."</b> ".$err2_int_lg.LAN_CHECK_13."<br /><br />";
	}else if($err2_int_lg != 0){
		$text2 = "<b>".LAN_CHECK_14."</b><br /><br />";
	}
	
	$text = $text1.$text2.$text;

	return $text;
}

function show_languages(){
	if($r = opendir(e_LANGUAGEDIR)){
		while($file = readdir($r)){
			$fname = e_LANGUAGEDIR.$file;
			if(is_dir($fname) && $file != "English" && $file != "CVS" && $file != "." && $file != ".."){
				$languages[]=$file;
			}
		}
		$text .= "
		<form method='post' action='".e_SELF."?lancheck' >
		<table class='fborder'>
		<tr>
		<td class='forumheader'>".LAN_CHECK_1."</td></tr>
		<tr><td class='forumheader3'>";
		foreach($languages as $lang){
			$text .= "<input type='radio' name='language' value='{$lang}' /> {$lang}<br />";
		}
		$text .= "</td></tr>
		<tr><td class='forumheader2' style='text-align:center;'><input type='submit' name='check_lang' value='".LAN_CHECK_2."' class='button' />
		</td></tr>
		</table></form>";
		return $text;
	}
}

if(e_QUERY && e_QUERY!="lancheck"){
	$qs = explode(".",rawurldecode(e_QUERY),3);
	$text = show_comparison($qs[1],$qs[2]);
	$ns -> tablerender(LAN_CHECK_3.": {$qs[1]}",$text);
}

if($_POST['check_lang']){
	$text = check_core_lanfiles($_POST['language']);
	$ns -> tablerender(LAN_CHECK_3.": ".$_POST['language'],$text);
}

if(e_QUERY=="lancheck" && !$_POST['check_lang']){$ns -> tablerender(" ",show_languages());}

?>
