/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 *
 * News Categories shortcode
*/
//<?
global $NEWSCAT,$NEWSCAT_ITEM;

// FIXME full rewrite!!!

$e107 = e107::getInstance();
$sql = e107::getDb();
$pref = e107::getPref();
$tp = e107::getParser();
$scbatch = e107::getScBatch('news');

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

	if(!defined("NEWSCAT_AMOUNT"))
	{
		define("NEWSCAT_AMOUNT",3);
	}

	// News templates with BC
	/* if(!$NEWSCAT)
	{
		$tmpl = e107::getTemplate('news', 'news', 'category');
		$NEWSCAT = $tmpl['body'];
	}
	*/
	if(!$NEWSCAT)
	{
		$NEWSCAT = "
			<div style='padding:5px'><div style='border-bottom:1px inset black; padding-bottom:1px;margin-bottom:5px'>
			{NEWSCATICON}&nbsp;{NEWSCATEGORY}
			</div>
			{NEWSCAT_ITEM}
			</div>
			";
	}
	
	// News templates with BC
	/*
	if(!$NEWSCAT_ITEM)
	{
		$tmpl = e107::getTemplate('news', 'news', 'category');
		$NEWSCAT_ITEM = $tmpl['item'];
	}
	*/
	if(!$NEWSCAT_ITEM)
	{
		$NEWSCAT_ITEM = "
		<div style='width:100%;padding-bottom:2px'>
		<table class='news-category table' style='width:100%' cellpadding='0' cellspacing='0' border='0'>
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


	if(!defined("NEWSCAT_CATLINK"))
	{
			define("NEWSCAT_CATLINK","");
	}
	if(!defined("NEWSCAT_ITEMLINK"))
	{
			define("NEWSCAT_ITEMLINK","");
	}
	if(!defined("NEWSCAT_STYLE"))
	{
			define("NEWSCAT_STYLE",'');
	}
	if(!defined("NEWSCAT_CATICON"))
	{
			define("NEWSCAT_CATICON","border:0px");
	}
	if(!defined("NEWSCAT_THUMB"))
	{
			define("NEWSCAT_THUMB","border:0px");
	}

	if(!defined("NEWSCAT_CELL"))
	{
			define("NEWSCAT_CELL","vertical-align:top");
	}



	$param['itemlink'] = NEWSCAT_ITEMLINK;
	$param['thumbnail'] = NEWSCAT_THUMB;
	$param['catlink'] = NEWSCAT_CATLINK;
	$param['caticon'] = NEWSCAT_CATICON;

	// get categories
	$sql2 = e107::getDb('sql2');
	$_time = time();
	$qry = "SELECT nc.*, COUNT(n.news_id) as ccount FROM #news_category AS nc 
	LEFT JOIN #news as n ON n.news_category=nc.category_id
	WHERE   n.news_class IN (".USERCLASS_LIST.") 
            AND (n.news_start=0 || news_start < {$_time}) 
            AND (n.news_end=0 || news_end > {$_time})
	GROUP BY nc.category_id
	ORDER BY nc.category_order ASC";
	if(!$sql2->gen($qry))
	{
        return '';
    }
	$cats = array();
	while ($row = $sql2->db_Fetch()) 
	{
		if($row['ccount'] > 0) $cats[$row['category_id']] = $row;
	}
	if(empty($cats)) return;
	
	$text3 = "\n\n\n
	<div style='width:100%;text-align:center;margin-left:auto;margin-right:auto'>
	<table class='table' style='".NEWSCAT_STYLE."'  cellpadding='0' cellspacing='0'>
	\n";
	$t = 0;
	$wid = floor(100/$nbr_cols);
	foreach($cats as $row3)
	{
		extract($row3);
		$scbatch->setScVar('news_item', $row3);
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

// Grab each news item.--------------
    	$cqry = "SELECT n.* FROM #news AS n
            WHERE news_category='".intval($category_id)."' 
            AND news_class IN (".USERCLASS_LIST.") 
            AND (news_start=0 || news_start < {$_time}) 
            AND (news_end=0 || news_end > {$_time})
            ORDER BY news_datestamp DESC LIMIT 0,".NEWSCAT_AMOUNT;

        $count = $sql->gen($cqry);
		//$count = $sql->db_Select("news", "*", "news_category='".intval($category_id)."' AND news_class IN (".USERCLASS_LIST.") AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().")  ORDER BY news_datestamp DESC LIMIT 0,".NEWSCAT_AMOUNT);
		if($count)
		{
            while ($row = $sql->db_Fetch()) 
            {
    			$scbatch->setScVar('news_item', $row);
    			//$row['category_name'] = $category_name;
    			//$row['category_icon'] = $category_icon;
    			$row = array_merge($row, $row3);
    			$textbody .= $ix -> render_newsitem($row, 'return', '', $NEWSCAT_ITEM, $param);
    
    		}
		}
// ----------------------------------
		$search[0] = "/\{NEWSCATICON\}(.*?)/si";
		$replace[0] = $scbatch->sc_newscaticon('url');

		$search[1] = "/\{NEWSCATEGORY\}(.*?)/si";
		$replace[1] = $scbatch->sc_newscategory();

		$search[2] = "/\{NEWSCAT_ITEM\}(.*?)/si";
		$replace[2] = $textbody;
		

		$text3 .= ($t % $nbr_cols == 0) ? "<tr>" : "";
		$text3 .= "\n<td style='".NEWSCAT_CELL."; width:$wid%;'>\n";

		$text3 .= preg_replace($search, $replace,$NEWSCAT);
		unset($textbody);


		$text3 .= "\n</td>\n";

		if (($t+1) % $nbr_cols == 0) 
		{
			$text3 .= "</tr>";
			$t++;
		} 
		else 
		{
			$t++;
		}
	}

	while ($t % $nbr_cols != 0)
	{
		$text3 .= "<td style='".NEWSCAT_CELL.";width:{$wid}%'>&nbsp;</td>\n";
		$text3 .= (($t+1) % $nbr_cols == 0) ? "</tr>" : "";
		$t++;
	}

	$text3 .= "</table></div>";

e107::getCache()->set($cString, $text3);
return $text3;