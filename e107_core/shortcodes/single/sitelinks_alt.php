<?php 
/*
 + ----------------------------------------------------------------------------+
 |     e107 website system
 |
 |     $Source: /cvs_backup/e107_0.8/e107_files/shortcode/sitelinks_alt.php,v $
 |     $Revision$
 |     $Date$
 |     $Author$
 +----------------------------------------------------------------------------+
 */
 
  
class sitelinks_alt
{
	function sitelinks_alt_shortcode($parm)
	{

		$params = explode('+', $parm);
		
		if (vartrue($params[0]) && ($params[0] != 'no_icons') && ($params[0] != 'default'))
		{
			$icon = $params[0];
		}
		else
		{
			$icon = e_IMAGE."generic/arrow.png";
		}
	
		$js_file = ($params[1] == 'noclick') ? 'nav_menu_alt.js' : 'nav_menu.js';
		
		if (file_exists(THEME.$js_file))
		{
			$text = "<script type='text/javascript' src='".THEME_ABS.$js_file."'></script>";
		}
		else
		{
			$text = "<script type='text/javascript' src='".e_JS.$js_file."'></script>";
		}
		$text .= "<div class='menuBar' style='width:100%; white-space: nowrap'>";
		
		// Setup Parent/Child Arrays ---->
		
		$lnk = e107::getSitelinks();
		$lnk->getlinks(1);
		$linklist = $lnk->getLinkArray();
			
		$tp = e107::getParser();
		
		
		// Loops thru parents.
	//	print_a($linklist);
		
	
		foreach ($linklist['head_menu'] as $lk)
		{

			if(substr($lk['link_url'],0,3) != '{e_' && strpos($lk['link_url'], '://') === false)
			{
				$lk['link_url'] = '{e_BASE}'.$lk['link_url'];
			}

			$lk['link_url'] = $tp->replaceConstants($lk['link_url'], 'abs', true);
			
			if ($params[0] == 'no_icons')
			{
				$link_icon = 'no_icons';
			}
			else
			{
				$link_icon = $lk['link_button']
					? (($lk['link_button'][0] == "{")
						? $tp->replaceConstants($lk['link_button'],'abs')
						: e_IMAGE_ABS.'icons/'.$lk['link_button'])
					: $icon;
			}
			
			$main_linkid = $lk['link_id'];
			if (isset($linklist['sub_'.$main_linkid])) // Has Children.
			{ 
			
				$text .= self::adnav_cat($lk['link_name'], '', $link_icon, 'l_'.$main_linkid);
				$text .= self::render_sub($linklist, $main_linkid, $params, $icon);			
			}
			else // Display Parent only.
			{
				$text .= self::adnav_cat($lk['link_name'], $lk['link_url'], $link_icon, FALSE, $lk['link_open']);
			}
		}
		
		$text .= "</div>";
		
		return $text;
		
	
	}
	
	function adnav_cat($cat_title, $cat_link, $cat_img, $cat_id = FALSE, $cat_open = FALSE)
	{
			$tp = e107::getParser();

		//	$cat_link = (strpos($cat_link, '://') === FALSE && strpos($cat_link, 'mailto:') !== 0 ? e_HTTP.$cat_link : $cat_link);
			
			if ($cat_open == 4 || $cat_open == 5)
			{
				$dimen = ($cat_open == 4) ? "600,400" : "800,600";
				$href = " href=\"javascript:open_window('".$cat_link."',".$dimen.")\"";
			}
			else
			{
				$href = "href='".$cat_link."'";
			}
			
			$text = "<a class='menuButton' ".$href." ";
			if ($cat_img != 'no_icons')
			{
				$text .= "style='background-image: url(".$cat_img."); background-repeat: no-repeat; background-position: 3px 1px; white-space: nowrap' ";
			}
			if ($cat_id)
			{
				$text .= "onclick=\"return buttonClick(event, '".$cat_id."');\" onmouseover=\"buttonMouseover(event, '".$cat_id."');\"";
			}
			if ($cat_open == 1)
			{
				$text .= " rel='external' ";
			}
			$text .= ">".$tp->toHTML($cat_title, "", "defs, no_hook")."</a>";
			return $text;
		}
		
		function adnav_main($cat_title, $cat_link, $cat_img, $cat_id = FALSE, $params, $cat_open = FALSE)
		{
			$tp = e107::getParser();
			
			
		//	$cat_link = (strpos($cat_link, '://') === FALSE) ? e_HTTP.$cat_link : $cat_link;
			$cat_link = $tp->replaceConstants($cat_link, 'abs', TRUE);
			
			if ($cat_open == 4 || $cat_open == 5)
			{
				$dimen = ($cat_open == 4) ? "600,400" : "800,600";
				$href = " href=\"javascript:open_window('".$cat_link."',".$dimen.")\"";
			}
			else
			{
				$href = "href='".$cat_link."'";
			}
			
			$text = "<a class='menuItem' ".$href." ";
			if ($cat_id)
			{
				if (isset($params[2]) && $params[2] == 'link')
				{
					$text .= "onmouseover=\"menuItemMouseover(event, '".$cat_id."');\"";
				}
				else
				{
					$text .= "onclick=\"return false;\" onmouseover=\"menuItemMouseover(event, '".$cat_id."');\"";
				}
			}
			if ($cat_open == 1)
			{
				$text .= " rel='external' ";
			}
			$text .= ">";
			if ($cat_img != 'no_icons')
			{
				$text .= "<span class='menuItemBuffer'>".$cat_img."</span>";
			}
			$text .= "<span class='menuItemText'>".$tp->toHTML($cat_title, "", "defs, no_hook")."</span>";
			if ($cat_id)
			{
				$text .= "<span class=\"menuItemArrow\">&#9654;</span>";
			}
			$text .= "</a>";
			return $text;
		}
		
		function render_sub($linklist, $id, $params, $icon)
		{
			$tp = e107::getParser();
			
			$text = "<div id='l_".$id."' class='menu' onmouseover=\"menuMouseover(event)\">";
			foreach ($linklist['sub_'.$id] as $sub)
			{
				// Filter title for backwards compatibility ---->
				
				if (substr($sub['link_name'], 0, 8) == "submenu.")
				{
					$tmp = explode(".", $sub['link_name']);
					$subname = $tmp[2];
				}
				else
				{
					$subname = $sub['link_name'];
				}
				
				// Setup Child Icon --------->
				
				if (!$sub['link_button'] && $params[0] == 'no_icons')
				{
					$sub_icon = 'no_icons';
				}
				else
				{
					
					if(vartrue($sub['link_button']))
					{
						$icon_url =  ($sub['link_button'][0] == "{") ? $tp->replaceConstants($sub['link_button'],'abs') : e_IMAGE_ABS.'icons/'.$sub['link_button'];
						$sub_icon = "<img src='".$icon_url."' alt='' style='border:0px; vertical-align:bottom; width: 16px; height: 16px' />";
					}
					else
					{
						$sub_icon = '';
					}
				
				}
				if (isset($linklist['sub_'.$sub['link_id']])) // Has Children.
				{ 
					$sub_ids[] = $sub['link_id'];
					$text .= self::adnav_main($subname, $sub['link_url'], $sub_icon, 'l_'.$sub['link_id'], $params, $sub['link_open']);
				}
				else
				{
					$text .= self::adnav_main($subname, $sub['link_url'], $sub_icon, null, $params, $sub['link_open']);
				}
				
			}
			$text .= "</div>";
			
			if (isset($sub_ids) && is_array($sub_ids))
			{
				foreach ($sub_ids as $sub_id)
				{
					$text .= self::render_sub($linklist, $sub_id, $params, $icon);
				}
			}
			
			return $text;
		}
}