global $sql,$pref,$tp,$NEWSCAT,$NEWSCAT_ITEM;
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
			{NEWSCATICON}
			&nbsp;
			{NEWSCATEGORY}
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

	$param['itemlink'] = NEWSCAT_ITEMLINK;
	$param['thumbnail'] = NEWSCAT_THUMB;
	$param['catlink'] = NEWSCAT_CATLINK;
	$param['caticon'] = NEWSCAT_CATICON;

	$sql2 = new db;
	$sql2->db_Select("news_category", "*", "category_id!='' ORDER BY category_name ASC");

	$text3 = "\n\n\n
	<div style='width:100%;text-align:center;margin-left:auto;margin-right:auto'>
	<table  style='".NEWSCAT_STYLE."'  cellpadding='0' cellspacing='0'>
	\n";
	$t = 0;
			$wid = floor(100/$nbr_cols);
	while ($row3 = $sql2->db_Fetch()) {
		extract($row3);

		$search[0] = "/\{NEWSCATICON\}(.*?)/si";
		$replace[0] = ($category_icon) ? "<a href='".e_BASE."news.php?cat.".$category_id."'><img src='".e_IMAGE."icons/".$category_icon."' alt='' style='".$param['caticon']."' /></a>" : "";

		$search[1] = "/\{NEWSCATEGORY\}(.*?)/si";
		$replace[1] = ($category_name) ? "<a href='".e_BASE."news.php?cat.".$category_id."' style='".$param['catlink']."' >".$tp->toHTML($category_name)."</a>" : "";

		$text3 .= ($t % $nbr_cols == 0) ? "<tr>" : "";
		$text3 .= "\n<td style='vertical-align:top; width:$wid%;'>\n";

// Grab each news item.--------------

		$count = $sql->db_Select("news", "*", "news_category='$category_id' AND news_class IN (".USERCLASS_LIST.") AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().")  ORDER BY news_datestamp DESC LIMIT 0,".NEWSCAT_AMOUNT);
		while ($row = $sql->db_Fetch()) {

			$row['category_name'] = $category_name;
			$row['category_icon'] = $category_icon;

			$textbody .= $ix->parse_newstemplate($row,$NEWSCAT_ITEM,$param);

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
		$text3 .= "<td style='width:$wid'>&nbsp;</td>\n";
		$text3 .= (($t+1) % nbr_cols == 0) ? "</tr>" : "";
		$t++;
	}

	$text3 .= "</table></div>";

return $text3;