<?php
global $sql, $pref;
//---------------------------------------------------------------------------------------
function hilite($link,$enabled=''){
	global $PLUGINS_DIRECTORY,$tp,$pref;
    if(!$enabled){ return FALSE; }

    $link = $tp->replaceConstants($link,TRUE);
  	$tmp = explode("?",$link);
    $link_qry = (isset($tmp[1])) ? $tmp[1] : "";
    $link_slf = (isset($tmp[0])) ? $tmp[0] : "";
	$link_pge = basename($link_slf);
	$link_match = strpos(e_SELF,$tmp[0]);

    if(e_MENU == "debug" && getperms('0')) {
		echo "<br />link= ".$link;
		echo "<br />link_q= ".$link_qry;
		echo "<br />url= ".e_PAGE;
		echo "<br />url_query= ".e_QUERY."<br />";
	}

// ----------- highlight overriding - set the link matching in the page itself.

	if(defined("HILITE")) {
		if(strpos($link,HILITE)) {
        	return TRUE;
		}
	}


// --------------- highlighting for 'HOME'. ----------------
	global $pref;
 	list($fp,$fp_q) = explode("?",$pref['frontpage']['all']."?");
	if(strpos(e_SELF,"/".$pref['frontpage']['all'])!== FALSE && $fp_q == $tmp[1] && $link == e_HTTP."index.php"){
	  	
		return TRUE;
	}

// --------------- highlighting for plugins. ----------------
		if(stristr($link, $PLUGINS_DIRECTORY) !== FALSE && stristr($link, "custompages") === FALSE){

			if($link_qry)
			{  // plugin links with queries
                $subq = explode("?",$link);
				if(strpos(e_SELF,$subq[0]) && e_QUERY == $subq[1]){
			   		return TRUE;
				}else{
				  	return FALSE;
				}
			}
			else
			{  // plugin links without queries
				$link = str_replace("../", "", $link);
		   		if(stristr(dirname(e_SELF), dirname($link)) !== FALSE){
 			 		return TRUE;
				}
			}
            return FALSE;
		}

// --------------- highlight for news items.----------------
// eg. news.php, news.php?list.1 or news.php?cat.2 etc
	if(substr(basename($link),0,8) == "news.php")
	{

		if (strpos($link, "news.php?") !== FALSE && strpos(e_SELF,"/news.php")!==FALSE) {

			$lnk = explode(".",$link_qry); // link queries.
			$qry = explode(".",e_QUERY); // current page queries.

			if($qry[0] == "item")
			{
				return ($qry[2] == $lnk[1]) ? TRUE : FALSE;
     		}

			if($qry[0] == "all" && $lnk[0] == "all")
			{
            	return TRUE;
			}

			if($lnk[0] == $qry[0] && $lnk[1] == $qry[1])
			{
            	return TRUE;
			}

			if($qry[1] == "list" && $lnk[0] == "list" && $lnk[1] == $qry[2])
			{
            	return TRUE;
			}

		}
		elseif (!e_QUERY && e_PAGE == "news.php")
		{

		   	return TRUE;
		}
			return FALSE;

	}
// --------------- highlight for Custom Pages.----------------
// eg. page.php?1

		if (strpos($link, "page.php?") !== FALSE && strpos(e_SELF,"/page.php")) {
            list($custom,$page) = explode(".",$link_qry);
			list($q_custom,$q_page) = explode(".",e_QUERY);
			if($custom == $q_custom){
            	return TRUE;
			}else{
              	return FALSE;
			}
		}

// --------------- highlight default ----------------
		if(strpos($link, "?") !== FALSE){

			$thelink = str_replace("../", "", $link);
			if((strpos(e_SELF,$thelink) !== false) && (strpos(e_QUERY,$link_qry) !== false)){
		   		return true;
			}
		}
		if(!preg_match("/all|item|cat|list/", e_QUERY) && (strpos(e_SELF, str_replace("../", "",$link)) !== false)){
		  	return true;
		}

   		if((!$link_qry && !e_QUERY) && (strpos(e_SELF,$link) !== FALSE)){
			return TRUE;
		}

		if(($link_slf == e_SELF && !link_qry) || (e_QUERY && strpos(e_SELF."?".e_QUERY,$link)!== FALSE) ){
          	return TRUE;
		}

	return FALSE;
}
// ----------------------------------------------------

function adnav_cat($cat_title, $cat_link, $cat_id=FALSE, $cat_open=FALSE) {
	global $tp;

	$cat_link = (strpos($cat_link, '://') === FALSE && strpos($cat_link, 'mailto:') !== 0 ? e_HTTP.$cat_link : $cat_link);
	
	if ($cat_open == 4 || $cat_open == 5){
		$dimen = ($cat_open == 4) ? "600,400" : "800,600";
		$href = " href=\"javascript:open_window('".$cat_link."',".$dimen.")\"";
	} else {
		$href = "href='".$cat_link."'";
	}

	$text = "<a ".$href." ";
	
	if ($cat_open == 1){
		$text .= " rel='external' ";
	}
	
	if ($cat_id) {
		$text .= ">".$tp->toHTML($cat_title,"","defs, no_hook")."</a>";
	} else {
		$text .= ">".$tp->toHTML($cat_title,"","defs, no_hook")."</a>";
	}
	
	return $text;
}
	
function render_sub($linklist, $id) {
	$text = "";
	foreach ($linklist['sub_'.$id] as $sub) {
	
		// Filter title for backwards compatibility ---->
		if(substr($sub['link_name'],0,8) == "submenu.") {
			$tmp = explode(".",$sub['link_name']);
			$subname = $tmp[2];
		} else {
			$subname = $sub['link_name'];
		}
			
		if (isset($linklist['sub_'.$sub['link_id']])) {  // Has Children.
			$sub_ids[] = $sub['link_id'];
			
			$text .= "
				<li>".adnav_main($subname, $sub['link_url'], $sub['link_id'],$sub['link_open']);
			
			$text .= "
					<ul>";
					
			$temp = $linklist['sub_'.$sub['link_id']];
			foreach ($temp as $bla) {
				if (isset($linklist['sub_'.$bla['link_id']])) {
					$text .= "
						  <li>".adnav_main($bla['link_name'], $bla['link_url'], $bla['link_id'], $bla['link_open']);
					$text .= "
						  <ul>";
					$text .= render_sub($linklist, $bla['link_id']);
					$text .= "
							</ul></li>";
				} else {
					$text .= "
					<li>".adnav_main($bla['link_name'], $bla['link_url'], null, $bla['link_open']).'</li>';
				}
			}
				
			$text .= "
					</ul>";
			$text .= "
				</li>";
		} else {
			$text .= "
				<li>".adnav_main($subname, $sub['link_url'], null, $sub['link_open'])."</li>";
		}
	}
	
	return $text;
}
	
function adnav_main($cat_title, $cat_link, $cat_id=FALSE, $cat_open=FALSE) {
	global $tp;
	
	$cat_link = (strpos($cat_link, '://') === FALSE) ? e_HTTP.$cat_link : $cat_link;
	$cat_link = $tp->replaceConstants($cat_link,TRUE);

	if ($cat_open == 4 || $cat_open == 5){
		$dimen = ($cat_open == 4) ? "600,400" : "800,600";
		$href = " href=\"javascript:open_window('".$cat_link."',".$dimen.")\"";
	} else {
		$href = "href='".$cat_link."'";
	}

	$text = "<a ".$href." ";
			
	if ($cat_id) {
		$text .= "class='sub'";
	}
	if ($cat_open == 1) {
		$text .= " rel='external' ";
	}
	$text .= ">".$tp->toHTML($cat_title,"","defs, no_hook")."</a>";
	
	return $text;
}

$text .= "
	<div class='menuBar'>
		<ul id='nav'>";

if (defined('FS_START_SEPARATOR') && FS_START_SEPARATOR != false) {
	$text .= "
	<li class='fs-linkSep'>".FS_LINK_SEPARATOR."</li>";
}

// Setup Parent/Child Arrays ---->

$link_total = $sql->db_Select("links", "*", "link_class IN (".USERCLASS_LIST.") AND link_category=1 ORDER BY link_order ASC");
while ($row = $sql->db_Fetch()) {
	if($row['link_parent'] == 0) {
		$linklist['head_menu'][] = $row;
		$parents[] = $row['link_id'];
	} else {
		$pid = $row['link_parent'];
		$linklist['sub_'.$pid][] = $row;
	}
}


// Loops thru parents.--------->
global $tp;
$sepBr = 1;
$sepCount = count($linklist['head_menu']);
foreach ($linklist['head_menu'] as $lk) {
	$lk['link_url'] = $tp -> replaceConstants($lk['link_url'],TRUE);
	$main_linkid = $lk['link_id'];
	
	//if (hilite($lk['link_url'],TRUE)) { echo $lk['link_name']; }
	if (hilite($lk['link_url'],TRUE)) { $hilite_style = " id='active'"; } else {  $hilite_style = ""; }
	
	if (isset($linklist['sub_'.$main_linkid])) {  // Has Children.
		$text .= "
			<li".$hilite_style." class='sub'>".adnav_cat($lk['link_name'], e_SELF.'?'.e_QUERY.'#', $main_linkid)."";
	
		$text .= "
				<ul class='sub'>".render_sub($linklist, $main_linkid)."
				</ul>
			</li>";

	} else {

		// Display Parent only.
		$text .= "
			<li".$hilite_style.">".adnav_cat($lk['link_name'], $lk['link_url'], FALSE, $lk['link_open'])."</li>";
	}
	
	if (defined('FS_LINK_SEPARATOR')) {
		if ($sepBr < $sepCount) {
			$text .= "
			<li class='fs-linkSep'>".FS_LINK_SEPARATOR."</li>";
		}
	}
	
	$sepBr++;
}

if (defined('FS_END_SEPARATOR') && FS_END_SEPARATOR != false) {
	$text .= "
	<li class='fs-linkSep'>".FS_LINK_SEPARATOR."</li>";
}

$text .= "
		</ul>
	</div>
	";
	
$text .= '

';
?>