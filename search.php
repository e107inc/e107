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
|     $Revision: 1.18 $
|     $Date: 2005-03-10 07:16:19 $
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

if (isset($_POST['searchquery']) && strlen($_POST['searchquery']) > 2) {
	$query = trim($_POST['searchquery']);
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

if (isset($query) && isset($_POST['searchtype'][$google_id]) && $_POST['searchtype'][$google_id]) {
	header("location:http://www.google.com/search?q=".stripslashes(str_replace(" ", "+", $query)));
	exit;
}

require_once(HEADERF);
	
if (!isset($query) && isset($_POST['searchquery'])) {
	$ns->tablerender(LAN_180, LAN_201);
}

$con = new convert;

if (isset($_SERVER['HTTP_REFERER'])) {
	if (!$refpage = substr($_SERVER['HTTP_REFERER'], (strrpos($_SERVER['HTTP_REFERER'], "/")+1))) {
		$refpage = "index.php";
	}
} else {
	$refpage = "";
}

if (isset($_POST['searchtype']) && $_POST['searchtype']) {
	$searchtype = $_POST['searchtype'];
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
	$SEARCH_MAIN_CHECKBOXES .= $PRE_CHECKBOXES."<input onclick='uncheckG();' type='checkbox' name='searchtype[".$key."]' ".$sel." />".$si['qtype'].$POST_CHECKBOXES;
}

if ($search_prefs['google']) {
	$SEARCH_MAIN_CHECKBOXES .= $PRE_CHECKBOXES."<input id='google' type='checkbox' name='searchtype[".$google_id."]' onclick='uncheckAll(this)' />Google".$POST_CHECKBOXES;
}
$value = isset($query) ? $query : "";
$SEARCH_MAIN_SEARCHFIELD = "<input class='tbox' type='text' name='searchquery' size='60' value='".$value."' maxlength='50' />";
$SEARCH_MAIN_CHECKALL = "<input class='button' type='button' name='CheckAll' value='".LAN_SEARCH_1."' onclick='checkAll(this);' />";
$SEARCH_MAIN_UNCHECKALL = "<input class='button' type='button' name='UnCheckAll' value='".LAN_SEARCH_2."' onclick='uncheckAll(this); uncheckG();' />";
$SEARCH_MAIN_SUBMIT = "<input class='button' type='submit' name='searchsubmit' value='".LAN_180."' />";
	
$text = preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_MAIN_TABLE);
	
$ns->tablerender(PAGE_NAME." ".SITENAME, $text);

if (isset($query)) {
	foreach ($search_info as $key => $a) {
		if (isset($searchtype[$key])) {
			unset($text);
			if (file_exists($search_info[$key]['sfile'])) {
				@require_once($search_info[$key]['sfile']);
				$ns->tablerender(LAN_195." ".$search_info[$key]['qtype']." : ".LAN_196.": ".$results, $text);
			}
		}
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
		document.searchform[\"searchtype[\" + i + \"]\"].checked = true ;
		uncheckG();
	}
		 
	function uncheckAll(allbox) {
		for (var i = 0; i < ".$search_count."; i++)
		document.searchform[\"searchtype[\" + i + \"]\"].checked = false ;
	}
		
	function uncheckG() {
		document.searchform[\"searchtype[".$google_id."]\"].checked = false ;
	}
	</script>\n";
	return $script;
}
	
require_once(FOOTERF);
?>