if(ADMIN){
	global $sql,$pst,$ns,$tp;
	if($pst->form && $pst->page){
		$thispage = urlencode(e_SELF."?".e_QUERY);
		if(is_array($pst->page)){
		for ($i=0; $i<count($pst->page); $i++) {
			if(eregi(urlencode($pst->page[$i]),$thispage)){
				$query = urlencode($pst->page[$i]);

				$theform = $pst->form[$i];
				$pid = $i;
				}
			}
		}else{
			$query = urlencode($pst->page);
			$theform = $pst->form;
			$pid = 0;
		}

		 if(eregi($query,$thispage)){
			$pst_text = "
			<form method='post' action='".e_SELF."?clr_preset' id='e_preset'>
			<div style='text-align:center'>";
			if(!$sql->db_Count("preset", "(*)", " WHERE preset_name='".$pst->id."'  ")){
				$pst_text .= "<input type='button' class='button' name='save_preset' value='".LAN_SAVE."' onclick=\"savepreset('".$theform."',$pid)\" />";
			}else{
				$pst_text .= "<input type='button' class='button' name='save_preset' value='".LAN_UPDATE."' onclick=\"savepreset('".$theform."',$pid)\" />";
				$pst_text .= "<input type='hidden' name='del_id' value='$pid' />
				<input type='submit' class='button' name='delete_preset' value='".LAN_DELETE."' onclick=\"return jsconfirm('".$tp->toJS(LAN_PRESET_CONFIRMDEL." [".$pst->id."]")."')\" />";
			}
			$pst_text .= "</div></form>";
			return $ns -> tablerender(LAN_PRESET, $pst_text);
		}
	}
}