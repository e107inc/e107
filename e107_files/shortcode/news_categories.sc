/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 *
 * News Categories shortcode
*/
global $e107, $sql,$pref,$tp,$NEWSCAT,$NEWSCAT_ITEM;

$cString = 'nq_news_categories_sc';
$cached = e107::getCache()->retrieve($cString);

if(false !== $cached)
{
	return $cached;
}

require_once(e_HANDLER."news_class.php");
$ix = new news;

$nbr_cols = (isset($pref['nbr_cols'])) ? $pref['nbr_cols'] : 1;
$nbr_cols = (defined("NEWSCAT_COLS")) ? NEWSCAT_COLS : $nbr_cols;

	if(!defined("NEWSCAT_AMOUNT")){
		define("NEWSCAT_AMOUNT",3);
	}

	if(!$NEWSCAT){
		$NEWSCAT = "
			<div style='padding:5px'><div style='border-bottom:1px inset black; padding-bottom:1px;margin-bottom:5px'>
			{NEWSCATICON}&nbsp;{NEWSCATEGORY}
			</div>
			{NEWSCAT_ITEM}
			</div>
			";
	}

	if(!$NEWSCAT_ITEM){
		$NEWSCAT_ITEM = "
		<div style='width:100%;padding-bottom:2px'>
		<table style='width:100%' cellpadding='0' cellspacing='0' border='0'>
		<tr>
		<td style='width:2px;vertical-align:top'>&#8226;
		</td>
		<td style='text-align:left;vertical-align:top;padding-left:3px'>
		{NEWSTITLELINK}
		<br />
		</td></tr>
		</table>
		</div>
		";
	}


	if(!defined("NEWSCAT_CATLINK")){
			define("NEWSCAT_CATLINK","");
	}
	if(!defined("NEWSCAT_ITEMLINK")){
			define("NEWSCAT_ITEMLINK","");
	}
	if(!defined("NEWSCAT_STYLE")){
			define("NEWSCAT_STYLE","width:96%");
	}
	if(!defined("NEWSCAT_CATICON")){
			define("NEWSCAT_CATICON","border:0px");
	}
	if(!defined("NEWSCAT_THUMB")){
			define("NEWSCAT_THUMB","border:0px");
	}

	if(!defined("NEWSCAT_CELL")){
			define("NEWSCAT_CELL","vertical-align:top");
	}



	$param['itemlink'] = NEWSCAT_ITEMLINK;
	$param['thumbnail'] = NEWSCAT_THUMB;
	$param['catlink'] = NEWSCAT_CATLINK;
	$param['caticon'] = NEWSCAT_CATICON;

	$sql2 = new db;
	$qry = "SELECT nc.*, ncr.news_rewrite_string AS news_category_rewrite_string, ncr.news_rewrite_id AS news_category_rewrite_id FROM #news_category AS nc
        LEFT JOIN #news_rewrite AS ncr ON nc.category_id=ncr.news_rewrite_source AND ncr.news_rewrite_type=2
        ORDER BY nc.category_order ASC
    ";
	if(!$sql2->db_Select_gen($qry))
	{
        return '';
    }

	$text3 = "\n\n\n
	<div style='width:100%;text-align:center;margin-left:auto;margin-right:auto'>
	<table style='".NEWSCAT_STYLE."'  cellpadding='0' cellspacing='0'>
	\n";
	$t = 0;
			$wid = floor(100/$nbr_cols);
	while ($row3 = $sql2->db_Fetch()) {
		extract($row3);
		//quick fix
		if($category_icon)
		{
			// new path
			if(strpos($category_icon, '{') === 0)
			{
				$category_icon = e107::getParser()->replaceConstants($category_icon, 'abs');
			}
			else //old paths
			{
				$category_icon = e_IMAGE_ABS."icons/".$category_icon;
			}
		}
		$search[0] = "/\{NEWSCATICON\}(.*?)/si";
		$replace[0] = ($category_icon) ? "<a href='".$e107->url->getUrl('core:news', 'main', 'action=list&id='.$category_id.'&sef='.$news_category_rewrite_string)."'><img src='".$category_icon."' alt='' style='".$param['caticon']."' /></a>" : "";

		$search[1] = "/\{NEWSCATEGORY\}(.*?)/si";
		$replace[1] = ($category_name) ? "<a href='".$e107->url->getUrl('core:news', 'main', 'action=list&id='.$category_id.'&sef='.$news_category_rewrite_string)."' style='".$param['catlink']."' >".$tp->toHTML($category_name,TRUE,"defs")."</a>" : "";

		$text3 .= ($t % $nbr_cols == 0) ? "<tr>" : "";
		$text3 .= "\n<td style='".NEWSCAT_CELL."; width:$wid%;'>\n";

// Grab each news item.--------------
    	$cqry = "SELECT n.*, nr.* FROM #news AS n
            LEFT JOIN #news_rewrite AS nr ON n.news_id=nr.news_rewrite_source AND nr.news_rewrite_type=1
            WHERE news_category='".intval($category_id)."' 
            AND news_class IN (".USERCLASS_LIST.") 
            AND (news_start=0 || news_start < ".time().") 
            AND (news_end=0 || news_end>".time().")
            ORDER BY news_datestamp DESC LIMIT 0,".NEWSCAT_AMOUNT;

        $count = $sql->db_Select_gen($cqry);
		//$count = $sql->db_Select("news", "*", "news_category='".intval($category_id)."' AND news_class IN (".USERCLASS_LIST.") AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().")  ORDER BY news_datestamp DESC LIMIT 0,".NEWSCAT_AMOUNT);
		if($count)
		{
            while ($row = $sql->db_Fetch()) {
    
    			//$row['category_name'] = $category_name;
    			//$row['category_icon'] = $category_icon;
    			$row = array_merge($row, $row3);
    			$textbody .= $ix -> render_newsitem($row, 'return', '', $NEWSCAT_ITEM, $param);
    
    		}
		}
// ----------------------------------

		$search[2] = "/\{NEWSCAT_ITEM\}(.*?)/si";
		$replace[2] = $textbody;

		$text3 .= preg_replace($search, $replace,$NEWSCAT);
		unset($textbody);


		$text3 .= "\n</td>\n";
		if (($t+1) % $nbr_cols == 0) {
			$text3 .= "</tr>";
			$t++;
		} else {
			$t++;
		}
	}

	while ($t % $nbr_cols != 0){
		$text3 .= "<td style='".NEWSCAT_CELL.";width:{$wid}%'>&nbsp;</td>\n";
		$text3 .= (($t+1) % $nbr_cols == 0) ? "</tr>" : "";
		$t++;
	}

	$text3 .= "</table></div>";

e107::getCache()->set($cString, $text3);
return $text3;