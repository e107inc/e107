	global $sql,$pref,$tp,$NEWSCAT,$NEWSCAT_ITEM,$NEWSCAT_CATLINKSTYLE,$NEWSCAT_ITEMLINKSTYLE,$NEWSCAT_STYLE;
	$nbr_cols = (isset($pref['nbr_cols'])) ? $pref['nbr_cols'] : 1;

	if(!$NEWSITEM_AMOUNT){
		$NEWSITEM_AMOUNT =  3;
	}

	if(!$NEWSCAT){
		$NEWSCAT = "
		<div style='padding:5px'><div style='border-bottom:1px inset black; font-weight:bold;padding-bottom:1px;margin-bottom:5px'>
		{NEWSCAT_CATICON}&nbsp;
		{NEWSCAT_CATNAME}
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
		<td style='width:2px;vertical-align:top'>&#8226;</td>
		<td style='text-align:left;vertical-align:top;padding-left:3px'>
		{NEWSCAT_TITLE}
		<br />
		</td></tr>
		</table>
		</div>
	";
	}

	if(!$NEWSCAT_CATLINKSTYLE){
		$NEWSCAT_CATLINKSTYLE = "text-decoration:none;";
	}
	if(!$NEWSCAT_ITEMLINKSTYLE){
	$NEWSCAT_ITEMLINKSTYLE = "text-decoration:none;";
	}
    if(!$NEWSCAT_STYLE){
	$NEWSCAT_STYLE = "width:96%";
	}




    $sql2 = new db;
    $sql2->db_Select("news_category", "*", "category_id!='' ORDER BY category_name ASC");

    $text3 = "\n\n\n
    <div style='width:100%;text-align:center;margin-left:auto;margin-right:auto'>
    <table  style='$NEWSCAT_STYLE'  cellpadding='0' cellspacing='0'>
    <tr>\n";
    $t = 0;
    while ($row3 = $sql2->db_Fetch()) {
        extract($row3);

        $search[0] = "/\{NEWSCAT_CATICON\}(.*?)/si";
        $replace[0] = ($category_icon) ? "<a href='news.php?cat.".$category_id."'><img src='".e_IMAGE."newsicons/".$category_icon."' alt='' style='border:0px' /></a>" : "";

        $search[1] = "/\{NEWSCAT_CATNAME\}(.*?)/si";
        $replace[1] = ($category_icon) ? "<a href='news.php?cat.".$category_id."' style='$NEWSCAT_CATLINKSTYLE' >".$tp->toHTML($category_name)."</a>" : "";

        $wid = floor(100/$nbr_cols);
        $text3 .= "\n<td style='vertical-align:top; width:$wid%;'>\n";


// Grab each news item. ==============
        $count = $sql->db_SELECT("news", "*", "news_category='$category_id' AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().")  ORDER BY news_datestamp DESC LIMIT 0,$NEWSITEM_AMOUNT");
        while ($row = $sql->db_Fetch()) {
            extract($row);
            if (check_class($news_class)) {

            $search[3] = "/\{NEWSCAT_TITLE\}(.*?)/si";
                $replace[3] = ($news_title) ? "<a href='news.php?extend.".$news_id."' style='$NEWSCAT_ITEMLINKSTYLE'>".$tp->toHTML($news_title)."</a>":"<a href='news.php?extend.".$news_id."'>Untitled</a>";

                $search[4] = "/\{NEWSCAT_SUMMARY\}(.*?)/si";
                $replace[4] =  ($news_summary) ? $tp->toHTML($news_summary) : "" ;

                $search[5] = "/\{NEWSCAT_THUMBNAIL\}(.*?)/si";
                $replace[5] = ($news_thumb) ? "<a href='news.php?extend.".$news_id."'><img src='".e_IMAGE."newspost_images/".$news_thumb."' alt='' style='border:0px' /></a>" : "";

                $textbody .= preg_replace($search, $replace,$NEWSCAT_ITEM);
            }

        }
            $search[6] = "/\{NEWSCAT_ITEM\}(.*?)/si";
            $replace[6] = $textbody;

        $text3 .= preg_replace($search, $replace,$NEWSCAT);
        unset($textbody);

        $text3 .= "\n</td>\n";
        if ($t == ($nbr_cols-1)) {
            $text3 .= "</tr>

            <tr>";
            $t = 0;
        } else {
            $t++;
        }
    }
    $text3 .= "</tr></table></div>";


return $text3;