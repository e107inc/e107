<?php


function docorefile() {
	global $exclude, $t_array, $_arr, $e107info;
	$text ="<tr><td><br /></td></tr>
	<tr>
	<td class='fcaption' colspan='2'>".Integ_09."
	</td>
	</tr>
	<tr>
	<td style='width:60%' class='forumheader3'>
	<select name='activate' onChange='submit(this.options[selectedIndex].value)' class='tbox'><option></option>";
	$file_array = hex_getdirs(e_BASE, $exclude, "2");
	sort($file_array);
	unset($t_array);
	reset($file_array);
	foreach($file_array as $v){
		if (!in_array($v, $_arr)) {
			$text .= "<option value='".$v."'>".$v."</option>";
		}
	}
	$text .= "</select>
	</td>
	<td style='width:40%' class='forumheader3' >".Integ_10."
	</td>
	</tr>
	<tr>
	<td style='width:60%' class='forumheader3'>".Integ_11."&nbsp;
	<input class='tbox' type='text' name='save_file_name' value='".strtolower("core_".$e107info['e107_version']."b".$e107info['e107_build'].".crc")."' readonly>
	</td>
	<td style='width:40%' class='forumheader3'>
	<input type='checkbox' name='gz_core' value='.gz' checked />".Integ_22."
	</td>
	</tr>
	<td class='forumheade3' colspan='2'>
	<div align='center'>
	<input class='button' type='submit' name='donew' size='20' value='".Integ_12."' /></div>
	</td>
	</tr><tr><td><br /><br /></td></tr>";
	reset($_arr);
	foreach($_arr as $v){
		$text .="<input type='hidden' name='Arr[]' value='".$v."' />";
	}
	return $text;
}

//Make a new core sfv-File
if (IsSet($_POST['donew']) && IsSet($_POST['save_file_name'])) {
	$file_array = hex_getdirs(e_BASE, $exclude , "1");
	sort($file_array);
	unset($t_array);
	reset($file_array);
	$data="";
	foreach($file_array as $v){
		$data .= str_replace($dirs_1, $dirs_2, $v)."<-:sfv:->".generate_sfv_checksum(e_BASE.$v)."\n";
	}
	if (!IsSet($_POST['gz_core'])){
		$dh=@fopen($o_path.$_POST['save_file_name'], "w");
		if (@fwrite($dh, $data)){
			$message = "<div align='center'>".Integ_01."</div>";
		}
		else {
			$message = "<div align='center'>".Integ_02."</div>";
		}
		fclose($dh);
	}
	else {
		$dh=@gzopen($o_path.$_POST['save_file_name'].".gz", "wb");
		if (@gzwrite($dh, $data)){
			$message = "<div align='center'>".Integ_01."</div>";
		}
		else {
			$message = "<div align='center'>".Integ_02."</div>";
		}
		gzclose($dh);
	}
}
?>