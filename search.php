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
|     $Source: /cvs_backup/e107_0.7/search.php,v $
|     $Revision: 1.23 $
|     $Date: 2005-03-16 17:38:53 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");
e107_require(e_HANDLER."search_class.php");
$sch = new e_search;
$search_prefs = $sysprefs -> getArray('search_prefs');

if (!USER && $pref['search_restrict'] == 1) {
	require_once(HEADERF);
	$ns->tablerender(LAN_20, "<div style='text-align:center'>".LAN_416."</div>");
	require_once(FOOTERF);
	exit;
}

if (isset($_GET)) {
	$url_query = explode('.', e_QUERY);
	if (isset($_GET['q']) && strlen($_GET['q']) > 2) {
		$query = trim($_GET['q']);
	}
} else if (isset($_POST['q']) && strlen($_POST['q']) > 2) {
	$query = trim($_POST['q']);
}

$search_info = array();
	
//load all core search routines
if ($search_prefs['core_handlers']['news']) {
	$search_info[] = array('sfile' => e_HANDLER.'search/search_news.php', 'qtype' => LAN_98, 'refpage' => 'news.php');
}
if ($search_prefs['core_handlers']['comments']) {
	$search_info[] = array('sfile' => e_HANDLER.'search/search_comment.php', 'qtype' => LAN_99, 'refpage' => 'comment.php');
}
if ($search_prefs['core_handlers']['users']) {
	$search_info[] = array('sfile' => e_HANDLER.'search/search_user.php', 'qtype' => LAN_140, 'refpage' => 'user.php');
}
if ($search_prefs['core_handlers']['downloads']) {
	$search_info[] = array('sfile' => e_HANDLER.'search/search_download.php', 'qtype' => LAN_197, 'refpage' => 'download.php');
}

//load plugin search routines
foreach ($search_prefs['plug_handlers'] as $plug_dir => $active) {
	if ($active) {
		require_once(e_PLUGIN.$plug_dir."/e_search.php");
	}
}

$search_count = count($search_info);
$google_id = $search_count + 1;

if (isset($query) && isset($_GET['t'][$google_id]) && $_GET['t'][$google_id]) {
	header("location:http://www.google.com/search?q=".stripslashes(str_replace(" ", "+", $query)));
	exit;
}

$perform_search = TRUE;
if ($search_prefs['time_restrict']) {
	if (isset($query)) {
		$time = time() - $search_prefs['time_secs'];
		$query_check = $tp -> toDB($query);
		$ip = getip();
		if ($sql -> db_Select("generic", "gen_datestamp, gen_chardata, gen_ip", "gen_type='search' AND gen_ip='".$ip."'")) {
			$row = $sql -> db_Fetch();
			if (($row['gen_datestamp'] > $time) && ($row['gen_chardata'] != $query_check)) {
				$perform_search = FALSE;
			} else {
				$sql -> db_Update("generic", "gen_datestamp='".time()."', gen_chardata='".$query_check."' WHERE gen_type='search' AND gen_ip='".$ip."'");
			}
		} else {
			$sql -> db_Insert("generic", "0, 'search', '".time()."', '', '".$ip."', '', '".$query_check."'");
		}
	}
}

require_once(HEADERF);
	
if (!isset($query) && isset($_GET['q'])) {
	if (isset($_GET['q']) && strlen($_GET['q']) > 0) {
		$ns->tablerender(LAN_180, LAN_417);
	} else {
		$ns->tablerender(LAN_180, LAN_201);
	}
}

$con = new convert;

if (isset($_SERVER['HTTP_REFERER'])) {
	if (!$refpage = substr($_SERVER['HTTP_REFERER'], (strrpos($_SERVER['HTTP_REFERER'], "/")+1))) {
		$refpage = "index.php";
	}
} else {
	$refpage = "";
}

if (!$search_prefs['user_select'] && $_GET['r'] < 1) {
	foreach($search_info as $key => $si) {
		$searchtype[$key] = TRUE;
	}
} else {
	if (isset($_GET['t']) && $_GET['t']) {
		$searchtype = $_GET['t'];
	} else {
		foreach($search_info as $key => $si) {
			if ($si['refpage']) {
				if (eregi($si['refpage'], $refpage)) {
					$searchtype[$key] = TRUE;
				}
			}
		}

		if (!isset($searchtype) && isset($query)) {
			$searchtype[0] = TRUE;
		}
	}
}

if (!isset($SEARCH_MAIN_TABLE)) {
	if (file_exists(THEME."search_template.php")) {
		require_once(THEME."search_template.php");
	} else {
		require_once(e_BASE.$THEMES_DIRECTORY."templates/search_template.php");
	}
}

$SEARCH_MAIN_CHECKBOXES = '';
foreach($search_info as $key => $si) {
	(isset($searchtype[$key]) && $searchtype[$key]) ? $sel=" checked" : $sel="";
	$SEARCH_MAIN_CHECKBOXES .= $PRE_CHECKBOXES."<input onclick='uncheckG();' type='checkbox' name='t[".$key."]' ".$sel." />".$si['qtype'].$POST_CHECKBOXES;
}

if ($search_prefs['google']) {
	$SEARCH_MAIN_CHECKBOXES .= $PRE_CHECKBOXES."<input id='google' type='checkbox' name='t[".$google_id."]' onclick='uncheckAll(this)' />Google".$POST_CHECKBOXES;
}
$value = isset($query) ? $query : "";
$SEARCH_MAIN_SEARCHFIELD = "<input class='tbox' type='text' name='q' size='60' value='".$value."' maxlength='50' />";
$SEARCH_MAIN_CHECKALL = "<input class='button' type='button' name='CheckAll' value='".LAN_SEARCH_1."' onclick='checkAll(this);' />";
$SEARCH_MAIN_UNCHECKALL = "<input class='button' type='button' name='UnCheckAll' value='".LAN_SEARCH_2."' onclick='uncheckAll(this); uncheckG();' />";
$SEARCH_MAIN_SUBMIT = "<input type='hidden' name='r' value='0' /><input class='button' type='submit' name='s' value='".LAN_180."' />";
	
$text = preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_TOP_TABLE);

if ($search_prefs['user_select']) {
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_CAT_TABLE);
}


$text .= preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_BOT_TABLE);
	
$ns->tablerender(PAGE_NAME." ".SITENAME, $text);

if (isset($query)) {
	if ($perform_search) {
		foreach ($search_info as $key => $a) {
			if (isset($searchtype[$key])) {
				unset($text);
				if (file_exists($search_info[$key]['sfile'])) {
					@require_once($search_info[$key]['sfile']);
					$parms = $results.",".$search_prefs['search_res'].",".$_GET['r'].",".e_SELF."?q=".$_GET['q']."&t%5B".$key."%5D=on&r=[FROM]";
					if ($results > $search_prefs['search_res']) {
						$nextprev = ($results > $search_prefs['search_res']) ? LAN_SEARCH_10."&nbsp;".$tp->parseTemplate("{NEXTPREV={$parms}}") : "";
						$text .= "<div class='nextprev' style='text-align:center'>".$nextprev."</div>";
					}
					if ($results > 0) {
						$res_from = $_GET['r'] + 1;
						$res_to = ($_GET['r'] + $search_prefs['search_res']) > $results ? $results : ($_GET['r'] + $search_prefs['search_res']);
						$res_display = $res_from." - ".$res_to." ".LAN_SEARCH_12." ".$results;
					} else {
						$res_display = "";
					}
					$ns->tablerender(LAN_SEARCH_11." ".$res_display." ".LAN_SEARCH_13." ".$search_info[$key]['qtype'], $text);
				}
			}
		}
	} else {
		$ns->tablerender(LAN_SEARCH_16, LAN_SEARCH_17.$search_prefs['time_secs'].LAN_SEARCH_18);
	}
}

function parsesearch($text, $match) {
	$text = strip_tags($text);
	$temp = stristr($text, $match);
	$pos = strlen($text) - strlen($temp);
	$matchedText = substr($text,$pos,strlen($match));
	if ($pos < 70) {
		$text = "...".substr($text, 0, 100)."...";
	} else {
		$text = "...".substr($text, ($pos-50), $pos+30)."...";
	}
	$text = eregi_replace($match, "<span class='searchhighlight'>$matchedText</span>", $text);
	return($text);
}

function headerjs() {
	global $search_count, $google_id;
	$script = "<script type='text/javascript'>
	function checkAll(allbox) {
		for (var i = 0; i < ".$search_count."; i++)
		document.searchform[\"t[\" + i + \"]\"].checked = true ;
		uncheckG();
	}
		 
	function uncheckAll(allbox) {
		for (var i = 0; i < ".$search_count."; i++)
		document.searchform[\"t[\" + i + \"]\"].checked = false ;
	}
		
	function uncheckG() {
		document.searchform[\"t[".$google_id."]\"].checked = false ;
	}
	</script>\n";
	return $script;
}
	
require_once(FOOTERF);
?>