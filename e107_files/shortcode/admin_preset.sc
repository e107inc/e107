if(ADMIN){
	global $pst,$ns,$tp;
	if($pst->form && $pst->page){
    	$thispage = urlencode(e_SELF."?".e_QUERY);
	    $query = urlencode($pst->page);
	    if(eregi($query,$thispage)){
        	$pst_text = "
	        <form method='post' action='".e_SELF."?clr_preset' id='e_preset'>
	        <div style='text-align:center'>
	        <input type='button' class='button' name='save_preset' value='".LAN_SAVE."' onclick=\"savepreset('".$pst->form."')\" />
	        <input type='submit' class='button' name='delete_preset' value='".LAN_DELETE."' onclick=\"return jsconfirm('".$tp->toJS(LAN_PRESET_CONFIRMDEL)."')\" />
	        </div>
	        </form>";
            return $ns -> tablerender(LAN_PRESET, $pst_text);
	    }
	}
}