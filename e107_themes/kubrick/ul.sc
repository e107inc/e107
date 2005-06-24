global $sql, $page, $link_class;
$sql -> db_Select('links', '*', "link_category = 1 and link_name NOT REGEXP('submenu') and link_name NOT REGEXP('child') and link_class IN (".USERCLASS_LIST.") ORDER BY link_order ASC");
$ulmenu = "<ul id='navigation'>";
$r="1";
while($row = $sql -> db_Fetch()){
	if(!$link_class || check_class($link_class) || ($link_class==254 && USER)){
		if($r <= "8"){
			extract($row);


			$ltest = (e_QUERY ? e_PAGE."?".e_QUERY : e_PAGE);
			$rtest=substr(strrchr($link_url, "/"), 1);

			if(!preg_match("#(http:|mailto:|ftp:)#",$link_url)){ $link_url = e_BASE.$link_url; }
			if(eregi("e107_plugins",$link_url)){ $link_url = e_BASE.$link_url; }

			if($ltest == $link_url || $rtest == e_PAGE){ $ulclass = '_onpage'; } else { $ulclass = ''; }
	
			switch ($link_open) { 
				case 1:
					$link_append = " onclick=\"window.open('$link_url'); return false;\"";
					break; 
				case 2:
				   $link_append = " target=\"_parent\"";
					break;
				case 3:
				   $link_append = " target=\"_top\"";
					break;
				default:
				   unset($link_append);
			}
			$ulmenu .= "<li class='nav".$r."$ulclass'><a".($link_description ? " title = '$link_description' " : "")." href='".$link_url."'".$link_append.">".($r !="0" ? "".LINKSTART."" : "" )."$link_name</a></li>";
		}
		$r++;
	}
}
$ulmenu .= "</ul>";
return $ulmenu;

