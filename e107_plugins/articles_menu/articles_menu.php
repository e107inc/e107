<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/articles_menu/articles_menu.php,v $
|     $Revision: 1.8 $
|     $Date: 2004-12-13 13:20:41 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
if($cache = $e107cache->retrieve("article_menu")){
	echo $cache;
}else{
	ob_start();
	$text = ($menu_pref['articles_mainlink'] ? "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."content.php?article'> ".($menu_pref['articles_mainlink'] ? $menu_pref['articles_mainlink'] : ARTICLE_MENU_L1)."</a><br/>" : "");
	$sql2=new db;
	if($menu_pref['articles_parents'])	{
		$text.="<br />";
if($i = $sql -> db_Select("content", "*", "content_type='0' AND content_parent='0' "))	{
			$catText = "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_BASE."content.php?article.cat.0'>".ARTICLE_MENU_L2."</a> (".$i.")<br />";
		}

if($sql -> db_Select("content", "*", "content_type='6' ORDER BY content_heading ASC")){
			while($row = $sql -> db_Fetch()){
				extract($row);
				if(check_class($content_class))	{
						$i = $sql2 -> db_Select("content", "content_class", "content_type='0' AND content_parent='".$content_id."' ");
					if($i){
						while($row2 = $sql2 -> db_Fetch()){
							extract($row2);
							if(!check_class($content_class)){
								$i = $i - 1;
							}
						}
						$artText .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."content.php?article.cat.".$content_id."'>".$content_heading."</a> (".$i.")<br />";
					}
				}
			}
		}
		if($i > 0)
		{
			$text .= $catText.$artText.'<br />';
		}
	}
if($sql -> db_Select("content", "*", "content_type='0' ORDER BY content_datestamp DESC limit 0, ".$menu_pref['articles_display']))	{
		while($row = $sql-> db_Fetch()){
			extract($row);
			if(check_class($content_class))	{
				$ok=0;
				if($content_parent==0)	{
					$ok=1;
				}else{
						if($sql2 -> db_Select("content","content_class","content_id = '{$content_parent}'")){
						$row2 = $sql2 -> db_Fetch();
						if(check_class($row2['content_class'])){
							$ok=1;
						}
					}
				}
				if($ok){
					$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."content.php?article.".$content_id."'>".$content_heading."</a><br />";
				}
			}
		}

		if($menu_pref['articles_submitlink'] && check_class($pref['article_submit_class']))	{
			$text .= "<br /><img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."subcontent.php?article'> ".ARTICLE_MENU_L11."</a>";
		}
		$caption = (file_exists(THEME."images/article_menu.png") ? "<img src='".THEME."images/article_menu.png' alt='' style='vertical-align:middle' /> ".$menu_pref['article_caption'] : $menu_pref['article_caption']);

		$ns -> tablerender($caption, $text, 'articles');
	}

	if($pref['cachestatus']){
		$aj = new textparse;
		$cache = ob_get_contents();
		$e107cache->set("article_menu", $cache);
	}
	ob_end_clean();
}

?>
