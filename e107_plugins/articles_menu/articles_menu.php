<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/articles_menu.php
|
|	©Edwin van der Wal 2003
|	http://e107.org
|	evdwal@xs4all.nl
|	Based on the articles_menu.php
|
|	Released under the terms and conditions of the	
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
$text = ($menu_pref['articles_mainlink'] ? "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."article.php?0.list.0'> ".$menu_pref['articles_mainlink']."</a><br/>" : "");
//$q=explode(".",e_QUERY);

$sql2=new db;

if($menu_pref['articles_parents']){
	$text.="<br />";
	if($i = $sql -> db_Select("content", "*", "content_type='0' AND content_page='0' ")){
		$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."article.php?0.list.0.-1'>No Parent</a> (".$i.")<br />";
	}

	if($sql -> db_Select("content", "*", "content_type='6' ORDER BY content_heading ASC")){
		while($row = $sql -> db_Fetch()){
			extract($row);
			if(check_class($content_class)){
				if($i = $sql2 -> db_Select("content", "*", "content_type='0' AND content_page='".$content_id."' ")){
					$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."article.php?0.list.0.".$content_id."'>".$content_heading."</a> (".$i.")<br />";
				}
			}
		}
	}
	$text.="<br />";
}
	
if($sql -> db_Select("content", "*", "content_type='0' ORDER BY content_datestamp DESC limit 0, ".$menu_pref['articles_display'])){
	while($row = $sql-> db_Fetch()){
		extract($row);
		if(check_class($content_class)){
			$ok=0;
			if($content_page==0){
				$ok=1;
			} else {
				if($sql2 -> db_Select("content","content_class","content_id = '{$content_page}'")){
					$row2 = $sql2 -> db_Fetch();
					if(check_class($row2['content_class'])){
						$ok=1;
					}
				}
			}
			if($ok){
				$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."article.php?".$content_id.".0"."'>".$content_heading."</a><br />";
			}
		}
	}
	$ns -> tablerender($menu_pref['article_caption'], $text);
}
?>
