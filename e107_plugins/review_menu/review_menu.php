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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/review_menu/review_menu.php,v $
|     $Revision: 1.6 $
|     $Date: 2005-01-05 10:23:01 $
|     $Author: pholzmann $
+----------------------------------------------------------------------------+
*/
if($cache = $e107cache->retrieve("review_menu")){
	echo $cache;
}
else
{

	ob_start();
	$text = ($menu_pref['reviews_mainlink'] ? "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."content.php?review'> ".$menu_pref['reviews_mainlink']."</a><br/>" : "");
	$sql2=new db;

	if($menu_pref['reviews_parents'])
	{
		$text.="<br />";
		if($i = $sql -> db_Select("content", "*", "content_type='3' AND content_parent='0' "))
		{
			$text .= "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_BASE."content.php?review.cat.0'>Uncategorized</a> (".$i.")<br />";
		}
		unset($i);
		if($sql -> db_Select("content", "*", "content_type='10' ORDER BY content_heading ASC"))
		{
			while($row = $sql -> db_Fetch())
			{
				extract($row);
				if(check_class($content_class))
				{
					$i = $sql2 -> db_Select("content", "content_class", "content_type='3' AND content_parent='".$content_id."' ");
					if($i)
					{
						while($row2 = $sql2 -> db_Fetch())
						{
							extract($row2);
							if(!check_class($content_class)){
								$i = $i - 1;
							}
						}
						if($i)
						{
							$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."content.php?review.cat.".$content_id."'>".$content_heading."</a> (".$i.")<br />";
						}
					}
				}
			}
		}
		$text.="<br />";
	}

	if($sql -> db_Select("content", "*", "content_type='3' ORDER BY content_datestamp DESC limit 0, ".$menu_pref['reviews_display']))
	{
		while($row = $sql-> db_Fetch())
		{
			extract($row);
			if(check_class($content_class))
			{
				$ok=0;
				if($content_parent==0){
					$ok=1;
				}
				else
				{
					if($sql2 -> db_Select("content","content_class","content_id = '{$content_parent}'"))
					{
						$row2 = $sql2 -> db_Fetch();
						if(check_class($row2['content_class']))
						{
							$ok=1;
						}
					}
				}
				if($ok)
				{
					$text .= "<img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."content.php?review.".$content_id."'>".$content_heading."</a><br />";
				}
			}
		}
		if($menu_pref['reviews_submitlink'] && check_class($pref['review_submit_class']))
		{
			$text .= "<br /><img src='".THEME."images/bullet2.gif' alt='bullet' /> <a href='".e_BASE."subcontent.php?review'> ".LAN_190."</a>";
		}
		$caption = (file_exists(THEME."images/review_menu.png") ? "<img src='".THEME."images/review_menu.png' alt='' style='vertical-align:middle' /> ".$menu_pref['reviews_caption'] : $menu_pref['reviews_caption']);
		$ns -> tablerender($caption, $text, 'review');
	}
	if($pref['cachestatus'])
	{
		$aj = new textparse;
		$cache = $aj -> formtpa(ob_get_contents(), "admin");
		$e107cache->set("review_menu", $cache);
	}
	
	ob_end_flush(); /* dump collected data */
}

?>
