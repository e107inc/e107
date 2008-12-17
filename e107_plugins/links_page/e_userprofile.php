<?php

if(!function_exists('e_userprofile_links_page'))
{
	function e_userprofile_links_page()
	{
		global $qs, $sql, $tp;

		$id=intval($qs[1]);
		include_lan(e_PLUGIN."links_page/languages/".e_LANGUAGE.".php");	// Need the LAN file in some places even if no links found

		$qry = "
		SELECT l.*, lc.*
		FROM #links_page AS l
		LEFT JOIN #links_page_cat AS lc ON lc.link_category_id = l.link_category
		WHERE l.link_author = '".$id."'
		ORDER BY l.link_name";

		$qry1 = $qry." LIMIT 0,3";

		$text = '';

		$total = $sql -> db_Select_gen($qry);
		if ($sql -> db_Select_gen($qry1))
		{
			while ($row = $sql -> db_Fetch())
			{
				$LINK_APPEND = "<a class='linkspage_url' href='".$row['link_url']."' onclick=\"open_window('".e_PLUGIN."links_page/links.php?view.".$row['link_id']."','full');return false;\" >";
				
				$icon = $LINK_APPEND."<img class='linkspage_button' style='width:50px; height:50px;' src='".e_PLUGIN."links_page/images/blank.gif' alt='' /></a>";
				if ($row['link_button']) 
				{
					if (strpos($row['link_button'], "http://") !== FALSE) 
					{
						$icon = $LINK_APPEND."<img class='linkspage_button' src='".$row['link_button']."' alt='' /></a>";
					} 
					else 
					{
						if(strstr($row['link_button'], "/"))
						{
							if(is_readable(e_BASE.$row['link_button']))
							{
								$icon = $LINK_APPEND."<img class='linkspage_button' style='width:50px; height:50px;' src='".e_BASE.$row['link_button']."' alt='' /></a>";
							}
						}
						else
						{
							if(is_readable(e_PLUGIN."links_page/link_images/".$row['link_button']))
							{
								$icon = $LINK_APPEND."<img class='linkspage_button' style='width:50px; height:50px;' src='".e_PLUGIN."links_page/link_images/".$row['link_button']."' alt='' /></a>";
							}
						}
					}
				}

				$date = strftime("%d %b %Y", $row['link_datestamp']);
				$heading = ($row['link_name'] ? $LINK_APPEND.$tp->toHTML($row['link_name'], TRUE)."</a><br />" : '');

				$text .= "
				<div style='clear:both; padding-bottom:10px;'>
					<div style='float:left; padding-bottom:10px;'>".$icon."</div> 
					<div style='margin-left:60px; padding-bottom:10px;'>
						".$heading."
						<span class='smalltext'>".$date."</span>
					</div>
				</div>";
			}
			$text .= "<div style='clear:both; padding-bottom:10px;'><a href='".e_PLUGIN."links_page/links.php'>".LCLAN_USERPROFILE_1."</a></div>";
		}
		$caption = str_replace('{total}',$total, LCLAN_USERPROFILE_2);
		return array('caption'=>$caption, 'text'=>$text);
	}
}

?>