<?php
// Functions to fix webmasters errors...
// Initial release by Lolo Irie, e107 Dev Team, 2004

function search_validtheme(){
	$theme_found = 0;
	$th = substr(e_THEME, 0, -1);
	$handle = opendir($th);
	while ($file = readdir($handle)){
	        if(!strstr($file, ".")){
				$subhandle=opendir(e_THEME.$file);
				while ($file2 = readdir($subhandle)){
					if($file2 == "theme.php"){
						$theme_found = 1;
						break;
					}
				}
				if($theme_found == 1){closedir($subhandle);break;}
				closedir($subhandle);
	        }
	}
	
	if($theme_found == 1){
		closedir($handle);
		return $file;
	}
	
	closedir($handle);
}

?>