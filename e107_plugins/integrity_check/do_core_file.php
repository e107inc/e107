<?php
	$text .="<tr><td><br /></td></tr>
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
?>