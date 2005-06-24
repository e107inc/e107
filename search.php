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
|     $Revision: 1.42 $
|     $Date: 2005-06-24 12:21:14 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

require_once('class2.php');

if (!check_class($pref['search_restrict'])) {
	require_once(HEADERF);
	$ns -> tablerender(LAN_SEARCH_20, "<div style='text-align: center'>".LAN_SEARCH_21."</div>");
	require_once(FOOTERF);
	exit;
}

$search_prefs = $sysprefs -> getArray('search_prefs');

// load search routines
$search_info = array();
$auto_order = 1000;
function search_info($id, $type, $plug_require, $info='') {
	global $tp, $search_prefs, $auto_order;
	if (check_class($search_prefs[$type.'_handlers'][$id]['class'])) {
		if ($plug_require) {
			require_once($plug_require);
			$ret = $search_info[0];
		} else {
			$ret = $info;
		}
		$ret['chars'] = $search_prefs[$type.'_handlers'][$id]['chars'];
		$ret['results'] = $search_prefs[$type.'_handlers'][$id]['results'];
		$ret['pre_title'] = $search_prefs[$type.'_handlers'][$id]['pre_title'];
		$ret['pre_title_alt'] = $tp -> toHtml($search_prefs[$type.'_handlers'][$id]['pre_title_alt']);
		$ret['order'] = (isset($search_prefs[$type.'_handlers'][$id]['order']) && $search_prefs[$type.'_handlers'][$id]['order']) ? $search_prefs[$type.'_handlers'][$id]['order'] : $auto_order;
		$auto_order++;
		return $ret;
	} else {
		return false;
	}
}

//core search routines
$search_id = 0;
if ($search_info[$search_id] = search_info('news', 'core', false, array('sfile' => e_HANDLER.'search/search_news.php', 'qtype' => LAN_98, 'refpage' => 'news.php', 'advanced' => e_HANDLER.'search/advanced_news.php'))) {
	$search_id++;
}
if ($search_info[$search_id] = search_info('comments', 'core', false, array('sfile' => e_HANDLER.'search/search_comment.php', 'qtype' => LAN_99, 'refpage' => 'comment.php', 'advanced' => e_HANDLER.'search/advanced_comment.php'))) {
	$search_id++;
}
if ($search_info[$search_id] = search_info('users', 'core', false, array('sfile' => e_HANDLER.'search/search_user.php', 'qtype' => LAN_140, 'refpage' => 'user.php', 'advanced' => e_HANDLER.'search/advanced_user.php'))) {
	$search_id++;
}
if ($search_info[$search_id] = search_info('downloads', 'core', false, array('sfile' => e_HANDLER.'search/search_download.php', 'qtype' => LAN_197, 'refpage' => 'download.php', 'advanced' => e_HANDLER.'search/advanced_download.php'))) {
	$search_id++;
}
if ($search_info[$search_id] = search_info('pages', 'core', false, array('sfile' => e_HANDLER.'search/search_pages.php', 'qtype' => LAN_418, 'refpage' => 'page.php', 'advanced' => e_HANDLER.'search/advanced_pages.php'))) {
	$search_id++;
}
//plugin search routines
foreach ($search_prefs['plug_handlers'] as $plug_dir => $active) {
	if(is_readable(e_PLUGIN.$plug_dir."/e_search.php")) {
		if ($search_info[$search_id] = search_info($plug_dir, 'plug', e_PLUGIN.$plug_dir."/e_search.php")) {
			$search_id++;
		}
	}
}

// order search routines
function array_sort($array, $column, $order = SORT_DESC) {
	$i = 0;
	foreach($array as $info) {
		$sortarr[] = $info[$column];
		$i++;
	}
	array_multisort($sortarr, $order, $array, $order);
	return($array);
}

$search_info = array_sort($search_info, 'order', SORT_ASC);

// validate search query
$perform_search = true;

function magic_search($data) {
	foreach ($data as $key => $value) {
		if (is_array($value)) {
			$data[$key] = magic_search($value);	
		} else {
			$data[$key] = stripslashes($value);	
		}
	}
	return $data;
}

if (!e_QUERY) {
	$enhanced = true;
}

if (isset($_GET['q']) || isset($_GET['in']) || isset($_GET['ex']) || isset($_GET['ep']) || isset($_GET['beg'])) {
	if (MAGIC_QUOTES_GPC == true) {
		$_GET = magic_search($_GET);
	}
	$full_query = $_GET['q'];
	if ($_GET['in']) {
		$en_in = explode(' ', $_GET['in']);
		foreach ($en_in as $en_in_key) {
			$full_query .= " +".$en_in_key;
		}
		$enhanced = true;
	}
	if ($_GET['ex']) {
		$en_ex = explode(' ', $_GET['ex']);
		foreach ($en_ex as $en_ex_key) {
			$full_query .= " -".$en_ex_key;
		}
		$enhanced = true;
	}
	if ($_GET['ep']) {
		$full_query .= " \"".$_GET['ep']."\"";
		$enhanced = true;
	}
	if ($_GET['be']) {
		$en_be = explode(' ', $_GET['be']);
		foreach ($en_be as $en_be_key) {
			$full_query .= " ".$en_be_key."*";
		}
		$enhanced = true;
	}

	if (isset($_GET['r']) && !is_numeric($_GET['r'])) {
		$perform_search = false;
		$SEARCH_MESSAGE = LAN_201;
		$result_flag = 0;
	} else if (strlen($full_query) == 0) {
		$perform_search = false;
		$SEARCH_MESSAGE = LAN_201;
	} else if (strlen($full_query) < 3) {
		$perform_search = false;
		$SEARCH_MESSAGE = LAN_417;
	} else if ($search_prefs['time_restrict']) {
		$time = time() - $search_prefs['time_secs'];
		$query_check = $tp -> toDB($full_query);
		$ip = getip();
		if ($sql -> db_Select("tmp", "tmp_ip, tmp_time, tmp_info", "tmp_info LIKE 'type_search%' AND tmp_ip='".$ip."'")) {
			$row = $sql -> db_Fetch();
			if (($row['tmp_time'] > $time) && ($row['tmp_info'] != 'type_search '.$query_check)) {
				$perform_search = false;
				$SEARCH_MESSAGE = LAN_SEARCH_17.$search_prefs['time_secs'].LAN_SEARCH_18;
			} else {
				$sql -> db_Update("tmp", "tmp_time='".time()."', tmp_info='type_search ".$query_check."' WHERE tmp_info LIKE 'type_search%' AND tmp_ip='".$ip."'");
			}
		} else {
			$sql -> db_Insert("tmp", "'".$ip."', '".time()."', 'type_search ".$query_check."'");
		}
	}
	if ($perform_search) {
		$result_flag = $_GET['r'];
	}
	$query = trim($full_query);
}

// forward user if searching in google
$search_count = count($search_info);
$google_id = $search_count + 1;
if ($search_prefs['selector'] == 1) {
	if ($perform_search && isset($_GET['t'][$google_id]) && $_GET['t'][$google_id]) {
		header("location:http://www.google.com/search?q=".stripslashes(str_replace(" ", "+", $query)));
		exit;
	}
} else {
	if ($perform_search && isset($_GET['t']) && $_GET['t'] == $google_id) {
		header("location:http://www.google.com/search?q=".stripslashes(str_replace(" ", "+", $query)));
		exit;
	}
}

// determine referer for preselected search area

if (isset($_SERVER['HTTP_REFERER'])) {
	if (!$refpage = substr($_SERVER['HTTP_REFERER'], (strrpos($_SERVER['HTTP_REFERER'], "/")+1))) {
		$refpage = "index.php";
	}
} else {
	$refpage = "";
}

// determine areas being searched
if (!$search_prefs['user_select'] && $_GET['r'] < 1) {
	foreach($search_info as $key => $value) {
		$searchtype[$key] = true;
	}
} else {
	if (isset($_GET['t'])) {
		if (is_array($_GET['t'])) {
			$searchtype = $_GET['t'];
		} else {
			$searchtype[$_GET['t']] = true;
		}
	} else {
		foreach($search_info as $key => $value) {
			if ($value['refpage']) {
				if (eregi($value['refpage'], $refpage)) {
					$searchtype[$key] = true;
					$_GET['t'] = $key;
				}
			}
		}

		if (!isset($searchtype) && isset($query)) {
			if ($search_prefs['multisearch']) {
				$searchtype['all'] = true;
			} else {
				$searchtype[0] = true;
			}
		}
	}
}

// standard search config
if ($search_prefs['selector'] == 2) {
	$SEARCH_DROPDOWN = "<select name='t' id='t' class='tbox' onchange=\"ab()\">";
	if ($search_prefs['multisearch']) {
		$SEARCH_DROPDOWN .= "<option value='all'>".LAN_SEARCH_22."</option>";
	}
} else {
	$SEARCH_MAIN_CHECKBOXES = '';
}

foreach($search_info as $key => $value) {
	if ($search_prefs['selector'] == 2) {
		$sel = (isset($searchtype[$key]) && $searchtype[$key]) ? " selected='selected'" : "";
	} else {
		$sel = (isset($searchtype[$key]) && $searchtype[$key]) ? " checked='checked'" : "";
	}
	$google_js = check_class($search_prefs['google']) ? "onclick=\"uncheckG();\" " : "";
	if ($search_prefs['selector'] == 2) {
		$SEARCH_DROPDOWN .= "<option value='".$key."' ".$sel.">".$value['qtype']."</option>";
	} else if ($search_prefs['selector'] == 1) {
		$SEARCH_MAIN_CHECKBOXES .= $PRE_CHECKBOXES."<input ".$google_js." type='checkbox' name='t[".$key."]' ".$sel." />".$value['qtype'].$POST_CHECKBOXES;
	} else {
		$SEARCH_MAIN_CHECKBOXES .= $PRE_CHECKBOXES."<input type='radio' name='t' value='".$key."' ".$sel." />".$value['qtype'].$POST_CHECKBOXES;		
	}
}

if (check_class($search_prefs['google'])) {
	if ($search_prefs['selector'] == 2) {
		$SEARCH_DROPDOWN .= "<option value='".$google_id."'>Google</option>";
	} else if ($search_prefs['selector'] == 1) {
		$SEARCH_MAIN_CHECKBOXES .= $PRE_CHECKBOXES."<input id='google' type='checkbox' name='t[".$google_id."]' onclick='uncheckAll(this)' />Google".$POST_CHECKBOXES;
	} else {
		$SEARCH_MAIN_CHECKBOXES .= $PRE_CHECKBOXES."<input id='google' type='radio' name='t' value='".$google_id."' />Google".$POST_CHECKBOXES;
	}
}

if ($search_prefs['selector'] == 2) {
	$SEARCH_DROPDOWN .= "</select>";
}

$value = isset($_GET['q']) ? $_GET['q'] : "";
$SEARCH_MAIN_SEARCHFIELD = "<input class='tbox m_search' type='text' id='q' name='q' size='35' value='".$value."' maxlength='50' />";
if ($search_prefs['selector'] == 1) {
	$SEARCH_MAIN_CHECKALL = "<input class='button' type='button' name='CheckAll' value='".LAN_SEARCH_1."' onclick='checkAll(this);' />";
	$SEARCH_MAIN_UNCHECKALL = "<input class='button' type='button' name='UnCheckAll' value='".LAN_SEARCH_2."' onclick='uncheckAll(this); uncheckG();' />";
}

$SEARCH_MAIN_SUBMIT = "<input type='hidden' name='r' value='0' /><input class='button' type='submit' name='s' value='".LAN_180."' />";

$ENHANCED_ICON = "<img src='".e_IMAGE."generic/".IMODE."/search_enhanced.png' style='width: 16px; height: 16px; vertical-align: top' 
alt='".LAN_SEARCH_23."' title='".LAN_SEARCH_23."' onclick=\"expandit('en_in'); expandit('en_ex'); expandit('en_ep'); expandit('en_be')\"/>";

$enhanced_types['in'] = LAN_SEARCH_24.':';
$enhanced_types['ex'] = LAN_SEARCH_25.':';
$enhanced_types['ep'] = LAN_SEARCH_26.':';
$enhanced_types['be'] = LAN_SEARCH_27.':';

$ENHANCED_DISPLAY = $enhanced ? "" : "style='display: none'";

// advanced search config
if ($_GET['adv'] && !isset($_GET['a'])) {
	$_GET['a'] = $_GET['t'];
} else if (!$_GET['adv'] || $_GET['t'] == 'all' || $_GET['a'] != $_GET['t']) {
	foreach ($_GET as $gk => $gv) {
		if ($gk != 't' && $gk != 'q' && $gk != 'r' && $gk != 'in' && $gk != 'ex' && $gk != 'ep' && $gk != 'be' && $gk != 'adv') {
			unset($_GET[$gk]);
		} 
	}
	if ($_GET['adv']) {
		$_GET['a'] = $_GET['t'];
	}
}

if (isset($_GET['a'])) {
	if (isset($search_info[$_GET['t']]['advanced'])) {
		$SEARCH_MAIN_SUBMIT .= "<input type='hidden' name='a' value='".$_GET['a']."' />";
	}
}

$SEARCH_TYPE_SEL = "<input type='radio' name='adv' value='0' ".($_GET['adv'] ? "" : "checked='checked'")." /> ".LAN_SEARCH_29."&nbsp;
<input type='radio' name='adv' value='1' ".($_GET['adv'] ? "checked='checked'" : "" )." /> ".LAN_SEARCH_30;

foreach ($search_info as $key => $value) {
	if (!isset($search_info[$key]['advanced'])) {
		$js_adv .= " && abid != ".$key;
	}
}

if (isset($search_info[$_GET['t']]['advanced'])) {
	$SEARCH_TYPE_DISPLAY = "";
} else {
	$SEARCH_TYPE_DISPLAY = "style='display: none'";
}

if (check_class($search_prefs['google'])) {
	$js_adv .= " && abid != ".$google_id;
}

if ($perform_search) {
	$con = new convert;
	e107_require(e_HANDLER.'search_class.php');
	$sch = new e_search;

	// omitted words message
	$stop_count = count($sch -> stop_keys);
	if ($stop_count) {
		if ($stop_count > 1) {
			$SEARCH_MESSAGE = LAN_SEARCH_32.": ";
		} else {
			$SEARCH_MESSAGE = LAN_SEARCH_33.": ";
		}
		$i = 1;
		foreach ($sch -> stop_keys as $stop_key) {
			$SEARCH_MESSAGE .= $stop_key;
			if ($i != $stop_count) {
				$SEARCH_MESSAGE .= ', ';
			}
			$i++;
		}
	}
}

require_once(HEADERF);

// render search config

if (!isset($SEARCH_TOP_TABLE)) {
	if (file_exists(THEME."search_template.php")) {
		require_once(THEME."search_template.php");
	} else {
		require_once(e_BASE.$THEMES_DIRECTORY."templates/search_template.php");
	}
}

$text = preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_TOP_TABLE);
foreach ($enhanced_types as $en_id => $ENHANCED_TEXT) {
	$ENHANCED_DISPLAY_ID = "en_".$en_id;
	$ENHANCED_FIELD = "<input class='tbox' type='text' id='".$en_id."' name='".$en_id."' size='35' value='".$_GET[$en_id]."' maxlength='50' />";
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_ENHANCED);
}
if ($search_prefs['user_select']) {
	$text .= preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_CATS);
}

$text .= preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_TYPE);

if (isset($_GET['a'])) {
	if (isset($search_info[$_GET['t']]['advanced'])) {
		@require_once($search_info[$_GET['t']]['advanced']);
		$SEARCH_MAIN_SUBMIT .= "<input type='hidden' name='a' value='".$_GET['a']."' />";
		foreach ($advanced as $adv_key => $adv_value) {
			if ($adv_value['type'] == 'single') {
				$SEARCH_ADV_TEXT = $adv_value['text'];
				$text .= preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_ADV_COMBO);
			} else {
				if ($adv_value['type'] == 'dropdown') {
					$SEARCH_ADV_A = $adv_value['text'];
					$SEARCH_ADV_B = "<select name='".$adv_key."' class='tbox'>";
					foreach ($adv_value['list'] as $list_item) {
						$SEARCH_ADV_B .= "<option value='".$list_item['id']."' ".($_GET[$adv_key] == $list_item['id'] ? "selected='selected'" : "").">".$list_item['title']."</option>";
					}
					$SEARCH_ADV_B .= "</select>";
				} else if ($adv_value['type'] == 'date') {
					$SEARCH_ADV_A = $adv_value['text'];
					$SEARCH_ADV_B = "<select id='on' name='on' class='tbox'>
					<option value='new' ".($_GET['on'] == 'new' ? "selected='selected'" : "").">".LAN_SEARCH_34."</option>
					<option value='old' ".($_GET['on'] == 'old' ? "selected='selected'" : "").">".LAN_SEARCH_35."</option>
					</select>&nbsp;<select id='time' name='time' class='tbox'>";
					$time = array(LAN_SEARCH_36 => 'any', LAN_SEARCH_37 => 86400, LAN_SEARCH_38 => 172800, LAN_SEARCH_39 => 259200, LAN_SEARCH_40 => 604800, LAN_SEARCH_41 => 1209600, LAN_SEARCH_42 => 1814400, LAN_SEARCH_43 => 2628000, LAN_SEARCH_44 => 5256000, LAN_SEARCH_45 => 7884000, LAN_SEARCH_46 => 15768000, LAN_SEARCH_47 => 31536000, LAN_SEARCH_48 => 63072000, LAN_SEARCH_49 => 94608000);
					foreach ($time as $time_title => $time_secs) {
						$SEARCH_ADV_B .= "<option value='".$time_secs."' ".($_GET['time'] == $time_secs ? "selected='selected'" : "").">".$time_title."</option>";
					}
					$SEARCH_ADV_B .= "</select>";
				} else if ($adv_value['type'] == 'author') {
					require_once(e_HANDLER.'user_select_class.php');
					$us = new user_select;
					$SEARCH_ADV_A = $adv_value['text'];
					$SEARCH_ADV_B = $us -> select_form('popup', $adv_key, $_GET[$adv_key]);
				} else if ($adv_value['type'] == 'dual') {
					$SEARCH_ADV_A = $adv_value['adv_a'];
					$SEARCH_ADV_B = $adv_value['adv_b'];
				}
				$text .= preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_ADV);
			}
		}
	} else {
		unset($_GET['a']);
	}
}

$text .= $SEARCH_MESSAGE ? preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_TABLE_MSG) : "";
$text .= preg_replace("/\{(.*?)\}/e", '$\1', $SEARCH_BOT_TABLE);
	
$ns -> tablerender(PAGE_NAME." ".SITENAME, $text);

// parse search
if ($perform_search) {
	foreach ($search_info as $key => $a) {
		if (isset($searchtype[$key]) || isset($searchtype['all'])) {
			unset($text);
			if (file_exists($search_info[$key]['sfile'])) {
				$pre_title = ($search_info[$key]['pre_title'] == 2) ? $search_info[$key]['pre_title_alt'] : $search_info[$key]['pre_title'];
				$search_chars = $search_info[$key]['chars'];
				$search_res = $search_info[$key]['results'];
				@require_once($search_info[$key]['sfile']);
				$parms = $results.",".$search_res.",".$_GET['r'].",".e_SELF."?q=".$_GET['q']."&t=".$key."&r=[FROM]".(isset($_GET['a']) ? "&a=".$_GET['a'] : "");
				$core_parms = array('r' => '', 'q' => '', 't' => '', 'a' => '', 's' => '');
				foreach ($_GET as $pparm_key => $pparm_value) {
					if (!isset($core_parms[$pparm_key])) {
						$parms .= "&".$pparm_key."=".$_GET[$pparm_key];
					}
				}
				if ($results > $search_res) {
					$nextprev = ($results > $search_res) ? LAN_SEARCH_10."&nbsp;".$tp -> parseTemplate("{NEXTPREV={$parms}}") : "";
					$text .= "<div class='nextprev' style='text-align: center'>".$nextprev."</div>";
				}
				if ($results > 0) {
					$res_from = $_GET['r'] + 1;
					$res_to = ($_GET['r'] + $search_res) > $results ? $results : ($_GET['r'] + $search_res);
					$res_display = $res_from." - ".$res_to." ".LAN_SEARCH_12." ".$results;
				} else {
					$res_display = "";
				}
				$ns->tablerender(LAN_SEARCH_11." ".$res_display." ".LAN_SEARCH_13." ".(isset($_GET[$advanced_caption['id']]) ? $advanced_caption['title'][$_GET[$advanced_caption['id']]] : $search_info[$key]['qtype']), $text);
			}
		}
	}
}

// old 6xx search parser for reverse compatability
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
	global $search_count, $google_id, $search_prefs, $js_adv;
	if ($search_prefs['selector'] == 1) {
		$script = "<script type='text/javascript'>
		<!--
		function checkAll(allbox) {
			for (var i = 0; i < ".$search_count."; i++)
			document.getElementById('searchform')[\"t[\" + i + \"]\"].checked = true ;
			uncheckG();
		}
		 
		function uncheckAll(allbox) {
			for (var i = 0; i < ".$search_count."; i++)
			document.getElementById('searchform')[\"t[\" + i + \"]\"].checked = false ;
		}\n";
		
		if (check_class($search_prefs['google'])) {
		$script .= "
		function uncheckG() {
			document.getElementById('searchform')[\"t[".$google_id."]\"].checked = false ;
		}\n";
		}
	
		$script .= "// -->
		</script>";
		
	}
		
	$script .= "<script type='text/javascript'>
	<!--
	function ab() {
		abid = document.getElementById('t').value;
		if (abid != 'all'".$js_adv.") {
			document.getElementById('advanced_type').style.display = '';
		} else {
			document.getElementById('advanced_type').style.display = 'none';
		}
	}
	//-->
	</script>";
	
	return $script;
}
	
require_once(FOOTERF);

?>