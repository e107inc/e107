if(ADMIN){
	global $ns;
	$i=1;
	if (!$handle=opendir(e_DOCS.e_LANGUAGE."/")) {
	 $handle=opendir(e_DOCS."English/");
	}
	while ($file = readdir($handle)){
	        if($file != "." && $file != ".."){
	                $helplist[$i] = $file;
	                $i++;
	        }
	}
	closedir($handle);

	unset($e107_var);
	foreach ($helplist as $key => $value) {
	        $e107_var['x'.$key]['text'] = $value;
	        $e107_var['x'.$key]['link'] = e_ADMIN."docs.php?".$key;
	}

	$text = get_admin_treemenu(FOOTLAN_14,$act,$e107_var);
	return $ns -> tablerender(FOOTLAN_14,$text);
}

