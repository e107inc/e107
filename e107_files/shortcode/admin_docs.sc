if(ADMIN){
	global $ns;
	$c=1;
	if (!$handle=opendir(e_DOCS.e_LANGUAGE."/")) {
	 $handle=opendir(e_DOCS."English/");
	}
	while ($file = readdir($handle)){
	        if($file != "." && $file != ".."){
	                $helplist[$c] = $file;
 	               $c++;
	        }
	}
	closedir($handle);

	unset($e107_var);
	while(list($key, $value) = each($helplist)){
	        $e107_var['x'.$key]['text'] = $value;
	        $e107_var['x'.$key]['link'] = e_ADMIN."docs.php?".$key;
	}

	$text = get_admin_treemenu(FOOTLAN_14,$act,$e107_var);
	$ns -> tablerender(FOOTLAN_14,$text);
}

