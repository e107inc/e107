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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/log/stats.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-02-07 13:13:25 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

require_once("../../class2.php");
require_once(HEADERF);

if (!$pref['statActivate']) {
	$text = (ADMIN ? "<div style='text-align:center'>".LAN_371."</div>" : "<div style='text-align:center'>The features on this page have been disabled.</div>");
	$ns->tablerender("Site Statistics", $text);
	require_once(FOOTERF);
	exit;
}

if(strstr(e_QUERY, ".")) {
	list($action, $order) = explode(".", e_QUERY);
} else {
	$action = e_QUERY;
	$order = FALSE;
}

$action = intval($action);
$order = intval($order);

$stat = new siteStats();

if($stat -> error) {
	$ns->tablerender("Site statistics", $stat -> error);
	require_once(FOOTERF);
	exit;
}


/* stats displayed will depend on the query string. For example, ?1.2.4 will render today's stats, all time stats and browser stats */
/*
1: today's stats
2: all time total and unique
3: browsers
4: operating systems
5: domains
6: screen resolution/colour depth
7: referers
8: search engine strings
*/






switch($action) {
	case 1:
		$text = $stat -> renderTodaysVisits();
		break;
	case 2:
			$text = $stat -> renderAlltimeVisits();
		break;
	case 3:
		if($pref['statBrowser']) {
			$text = $stat -> renderBrowsers();
		} else {
			$text = "Statistics for this type is not being gathered.";
		}
		break;
	case 4:
		if($pref['statOs']) {
			$text = $stat -> renderOses();
		} else {
			$text = "Statistics for this type is not being gathered.";
		}
		break;
	case 5:
		if($pref['statDomain']) {
			$text = $stat -> renderDomains();
		} else {
			$text = "Statistics for this type is not being gathered.";
		}
		break;
	case 6:
		if($pref['statScreen']) {
			$text = $stat -> renderScreens();
		} else {
			$text = "Statistics for this type is not being gathered.";
		}
		break;
	case 7:
		if($pref['statRefer']) {
			$text = $stat -> renderRefers();
		} else {
			$text = "Statistics for this type is not being gathered.";
		}
		break;
	case 8:
		if($pref['statQuery']) {
			$text = $stat -> renderQueries();
		} else {
			$text = "Statistics for this type is not being gathered.";
		}
		break;
}


/* render links */
$path = e_PLUGIN."log/stats.php";
$links = "<div style='text-align: center;'>".
(e_QUERY != 1 ? "<a href='$path?1'>Today's Stats</a>" : "<b>Today's Stats</b>")." | ".
(e_QUERY != 2 ? "<a href='$path?2'>Alltime Stats</a>" : "<b>Alltime Stats</b>")." | ".
(e_QUERY != 3 && $pref['statBrowser'] ? "<a href='$path?3'>Browser Stats</a> | " : ($pref['statBrowser'] ? "<b>Browser Stats</b> |" : "")).
(e_QUERY != 4 && $pref['statOs'] ? "<a href='$path?4'>Operating System Stats</a> | " : ($pref['statOs'] ? "<b>Operating System Stats</b> | " : "")).
(e_QUERY != 5 && $pref['statDomain'] ? "<a href='$path?5'>Domain Stats</a> | " : ($pref['statDomain'] ? "<b>Domain Stats</b> | " : "")).
(e_QUERY != 6 && $pref['statScreen'] ? "<br /><a href='$path?6'>Screen Resolution / Color Depth Stats</a> | " : ($pref['statScreen'] ? "<br /><b>Screen Resolution / Color Depth Stats</b> | " : "")).
(e_QUERY != 7 && $pref['statRefer'] ? "<a href='$path?7'>Referral Stats</a> | " : ($pref['statRefer'] ? "<b>Referral Stats</b> | " : "")).
(e_QUERY != 8 && $pref['statQuery'] ? "<a href='$path?8'>Search String Stats</a> | " : ($pref['statQuery'] ? "<b>Search String Stats</b> | " : "")).

"</div><br /><br />";



$ns->tablerender("Site statistics", $links.$text);
require_once(FOOTERF);

class siteStats {

	var $dbPageInfo;
	var $fileInfo;
	var $fileBrowserInfo;
	var $fileOsInfo;
	var $fileScreenInfo;
	var $fileDomainInfo;
	var $fileReferInfo;
	var $fileQueryInfo;
	var $error;
	var $barImage;
	var $order;

	function siteStats() {
		/* constructor */
		global $sql;

		/* set image for bar */
		$this -> barImage = e_PLUGIN."log/images/bar.png";

		/* get today's logfile ... */
		$logfile = e_PLUGIN."log/logs/log_".date("z.Y", time()).".php";
		if(is_readable($logfile)) {
			require($logfile);
		} else {
			$this -> error = "Unable to read logfile ".$logfile.".";
			return;
		}

		/* set order var */
		global $order;
		$this -> order = $order;

		$this -> fileInfo = $pageInfo;
		$this -> fileBrowserInfo = $browserInfo;
		$this -> fileOsInfo = $osInfo;
		$this -> fileScreenInfo = $screenInfo;
		$this -> fileDomainInfo = $domainInfo;
		$this -> fileReferInfo = $refInfo;
		$this -> fileQueryInfo = $searchInfo;

		/* get main stat info from database */
		if($sql -> db_Select("logstats", "*", "ORDER BY log_uniqueid DESC LIMIT 0, 1", "nowhere")){
			$row = $sql -> db_Fetch();
			$this -> dbPageInfo = unserialize($row[2]);
		} else {
			$this -> dbPageInfo = array();
		}

		/* temp consolidate today's info (if it exists)... */
		if(is_array($pageInfo)) {
			foreach($pageInfo as $key => $info) {
				$key = preg_replace("/\?.*/", "", $key);
				if(array_key_exists($key, $this -> dbPageInfo)) {
					$this -> dbPageInfo[$key]['ttlv'] += $info['ttl'];
					$this -> dbPageInfo[$key]['unqv'] += $info['unq'];
				} else {
					$this -> dbPageInfo[$key]['url'] = $info['url'];
					$this -> dbPageInfo[$key]['ttlv'] = $info['ttl'];
					$this -> dbPageInfo[$key]['unqv'] = $info['unq'];
				}
			}
		}


		/* end constructor */
	}

	function arraySort($array, $column, $order = SORT_DESC){
		/* sorts multi-dimentional array based on which field is passed */
		$i=0;
		foreach($array as $info) {
			$sortarr[]=$info[$column];
			$i++;
		}
		array_multisort($sortarr, $order, $array, $order);
		return($array);
		/* end method */
	}

	function renderTodaysVisits() {
		/* renders information for today only */
		$totalArray = $this -> arraySort($this -> fileInfo, "ttl");
		foreach($totalArray as $key => $info) {
			$totalv += $info['ttl'];
			$totalu += $info['unq'];
		}


		$text = "<table class='fborder' style='width: 100%;'>\n<tr>\n<td class='fcaption' style='width: 20%;'>Page</td>\n<td class='fcaption' style='width: 70%;'>Visits Today</td>\n<td class='fcaption' style='width: 10%; text-align: center;'>%</td>\n</tr>\n";
		foreach($totalArray as $key => $info) {
			$percentage = round(($info['ttl']/$totalv) * 100, 2);
			$text .= "<tr class='forumheader'>\n<td style='width: 20%;'><img src='".e_PLUGIN."log/images/html.png' alt='' style='vertical-align: middle;'> <a href='".$info['url']."'>".$key."</a></td>\n<td style='width: 70%;'><img src='".$this -> barImage."' style='width: $percentage%; height: 10px; vertical-align: middle; border: 1px solid #000;' alt='' /> ".$info['ttl']." [".$info['unq']."]</td>\n<td style='width: 10%; text-align: center;'>".$percentage."%</td>\n</tr>\n";
		}
		$text .= "<tr class='forumheader'><td colspan='2'>Total</td><td style='text-align: center;'>$totalv</td></tr>\n<tr class='forumheader'><td colspan='2'>Unique</td><td style='text-align: center;'>$totalu</td></tr>\n</table>";
		return $text;
	}

	function renderAlltimeVisits() {
		/* renders information for alltime, total and unique */
		$totalArray = $this -> arraySort($this -> dbPageInfo, "ttlv");
		
		foreach($totalArray as $key => $info) {
			$total += $info['ttlv'];
		}
		$text .= "<table class='fborder' style='width: 100%;'>\n<tr>\n<td class='fcaption' style='width: 20%;'>Page</td>\n<td class='fcaption' style='width: 70%;'>Total Visits</td>\n<td class='fcaption' style='width: 10%; text-align: center;'>%</td>\n</tr>\n";
		foreach($totalArray as $key => $info) {
			$percentage = round(($info['ttlv']/$total) * 100, 2);
			$text .= "<tr class='forumheader'>
			<td style='width: 20%;'><img src='".e_PLUGIN."log/images/html.png' alt='' style='vertical-align: middle;'> <a href='".$info['url']."'>".$key."</a></td>
			<td style='width: 70%;'><img src='".$this -> barImage."' style='width: $percentage%; height: 10px; vertical-align: middle; border: 1px solid #000;' alt='' /> ".$info['ttlv']."</td>
			<td style='width: 10%; text-align: center;'>".$percentage."%</td>
			</tr>\n";
		}
		$text .= "<tr class='forumheader'><td colspan='2'>Total</td><td style='text-align: center;'>$total</td></tr>\n</table>";

		$uniqueArray = $this -> arraySort($this -> dbPageInfo, "unqv");
		foreach($uniqueArray as $key => $info) {
			$totalv += $info['unqv'];
		}
		$text .= "<br /><table class='fborder' style='width: 100%;'>\n<tr>\n<td class='fcaption' style='width: 20%;'>Page</td>\n<td class='fcaption' style='width: 70%;'>Total Unique Visits</td>\n<td class='fcaption' style='width: 10%; text-align: center;'>%</td>\n</tr>\n";
		foreach($uniqueArray as $key => $info) {
			$percentage = round(($info['unqv']/$total) * 100, 2);
			$text .= "<tr class='forumheader'>
			<td style='width: 20%;'><img src='".e_PLUGIN."log/images/html.png' alt='' style='vertical-align: middle;'> <a href='".$info['url']."'>".$key."</a></td>
			<td style='width: 70%;'><img src='".$this -> barImage."' style='width: $percentage%; height: 10px; vertical-align: middle; border: 1px solid #000;' alt='' /> ".$info['unqv']."</td>
			<td style='width: 10%; text-align: center;'>".$percentage."%</td>
			</tr>\n";
		}
		$text .= "<tr class='forumheader'><td colspan='2'>Total</td><td style='text-align: center;'>$totalv</td></tr>\n</table>";
		return $text;
	}

	function renderBrowsers() {
		global $sql;

		if($sql -> db_Select("logstats", "*", "log_id='statBrowser'")) {
			$row = $sql -> db_Fetch();
			$statBrowser = unserialize($row['log_data']);
		}

		/* temp consolidate today's data ... */
		foreach($this -> fileBrowserInfo as $name => $count) {
			$statBrowser[$name] += $count;
		}

		if(!is_array($statBrowser)) {
			return "<div style='text-align: center;'>No statistics yet.</div>";
		}

		if(is_array($statBrowser)) {
			if($this -> order) {
				ksort($statBrowser);
				reset ($statBrowser);
				$browserArray = $statBrowser;
			} else {
				$browserArray = $this -> arraySort($statBrowser, 0);
			}
			$total = array_sum($browserArray);
			$text = "<table class='fborder' style='width: 100%;'>\n<tr>\n<td class='fcaption' style='width: 20%;'><a title='".($this -> order ? "sort by total" : "sort alphabetically")."'href='".e_SELF."?3".($this -> order ? "" : ".1" )."'>Browser</a></td>\n<td class='fcaption' style='width: 70%;'>Total</td>\n<td class='fcaption' style='width: 10%; text-align: center;'>%</td>\n</tr>\n";
			foreach($browserArray as $key => $info) {

				$image = "";
				if(strstr($key, "Netscape")) {	$image = "netscape.png"; }
				if(strstr($key, "Konqueror")) {	$image = "konqueror.png"; }
				if(strstr($key, "Opera")) {	$image = "opera.png"; }
				if(strstr($key, "Explorer")) {	$image = "explorer.png"; }
				if(strstr($key, "Firebird")) {	$image = "firebird.png"; }
				if(strstr($key, "Firefox")) {	$image = "firefox.png"; }
				if(strstr($key, "Links") || strstr($key, "Lynx")) {	$image = "lynx.png"; }
				if(strstr($key, "Netcaptor")) {	$image = "netcaptor.png"; }
				if(strstr($key, "Mozilla")) {	$image = "mozilla.png"; }
				if(strstr($key, "Unknown")) {	$image = "unknown.png"; }

				$percentage = round(($info/$total) * 100, 2);
				$text .= "<tr class='forumheader'>
				<td style='width: 20%;'>".($image ? "<img src='".e_PLUGIN."log/images/$image' alt='' style='vertical-align: middle;'> " : "").$key."</td>
				<td style='width: 70%;'><img src='".$this -> barImage."' style='width: ".($percentage > 97 ? 97 : $percentage)."%; height: 10px; vertical-align: middle; border: 1px solid #000;' alt='' /> ".$info."</td>
				<td style='width: 10%; text-align: center;'>".$percentage."%</td>
				</tr>\n";
			}
			$text .= "<tr class='forumheader'><td colspan='2'>Total</td><td style='text-align: center;'>$total</td></tr>\n</table>";
		}
		return $text;
	}

	function renderOses() {
		global $sql;

		if($sql -> db_Select("logstats", "*", "log_id='statOs'")) {
			$row = $sql -> db_Fetch();
			$statOs = unserialize($row['log_data']);
		}

		/* temp consolidate today's data ... */
		foreach($this -> fileOsInfo as $name => $count) {
			$statOs[$name] += $count;
		}

		if(!is_array($statOs)) {
			return "<div style='text-align: center;'>No statistics yet.</div>";
		}

		if($this -> order) {
			ksort($statOs);
			reset ($statOs);
			$osArray = $statOs;
		} else {
			$osArray = $this -> arraySort($statOs, 0);
		}

		$total = array_sum($osArray);
		$text = "<table class='fborder' style='width: 100%;'>\n<tr>\n<td class='fcaption' style='width: 20%;'><a title='".($this -> order ? "sort by total" : "sort alphabetically")."'href='".e_SELF."?4".($this -> order ? "" : ".1" )."'>Operating System</a></td>\n<td class='fcaption' style='width: 70%;'>Total</td>\n<td class='fcaption' style='width: 10%; text-align: center;'>%</td>\n</tr>\n";
		foreach($osArray as $key => $info) {

			$image = "";
			if(strstr($key, "Windows")) {	$image = "windows.png"; }
			if(strstr($key, "Mac")) {	$image = "mac.png"; }
			if(strstr($key, "Linux")) {	$image = "linux.png"; }
			if(strstr($key, "BeOS")) {	$image = "beos.png"; }
			if(strstr($key, "FreeBSD")) {	$image = "freebsd.png"; }
			if(strstr($key, "NetBSD")) {	$image = "netbsd.png"; }
			if(strstr($key, "Unspecified")) {	$image = "unspecified.png"; }

			$percentage = round(($info/$total) * 100, 2);
			$text .= "<tr class='forumheader'>
			<td style='width: 20%;'>".($image ? "<img src='".e_PLUGIN."log/images/$image' alt='' style='vertical-align: middle;'> " : "").$key."</td>
			<td style='width: 70%;'><img src='".$this -> barImage."' style='width: ".($percentage > 97 ? 97 : $percentage)."%; height: 10px; vertical-align: middle; border: 1px solid #000;' alt='' /> ".$info."</td>
			<td style='width: 10%; text-align: center;'>".$percentage."%</td>
			</tr>\n";
		}
		$text .= "<tr class='forumheader'><td colspan='2'>Total</td><td style='text-align: center;'>$total</td></tr>\n</table>";
		return $text;
	}


	function renderDomains() {
		global $sql;

		if($sql -> db_Select("logstats", "*", "log_id='statDomain'")) {
			$row = $sql -> db_Fetch();
			$statDom = unserialize($row['log_data']);
		}
		
		/* temp consolidate today's data ... */
		foreach($this -> fileDomainInfo as $name => $count) {
			$statDom[$name] += $count;
		}

		if(!is_array($statDom)) {
			return "<div style='text-align: center;'>No statistics yet.</div>";
		}
		
		if($this -> order) {
			ksort($statDom);
			reset ($statDom);
			$domArray = $statDom;
		} else {
			$domArray = $this -> arraySort($statDom, 0);
		}

		$total = array_sum($domArray);
		$text = "<table class='fborder' style='width: 100%;'>\n<tr>\n<td class='fcaption' style='width: 20%;'><a title='".($this -> order ? "sort by total" : "sort alphabetically")."'href='".e_SELF."?5".($this -> order ? "" : ".1" )."'>Countries / Domains</a></td>\n<td class='fcaption' style='width: 70%;'>Total</td>\n<td class='fcaption' style='width: 10%; text-align: center;'>%</td>\n</tr>\n";
		foreach($domArray as $key => $info) {
			$percentage = round(($info/$total) * 100, 2);
			$text .= "<tr class='forumheader'>
			<td style='width: 20%;'>".$key."</td>
			<td style='width: 70%;'><img src='".$this -> barImage."' style='width: ".($percentage > 97 ? 97 : $percentage)."%; height: 10px; vertical-align: middle; border: 1px solid #000;' alt='' /> ".$info."</td>
			<td style='width: 10%; text-align: center;'>".$percentage."%</td>
			</tr>\n";
		}
		$text .= "<tr class='forumheader'><td colspan='2'>Total</td><td style='text-align: center;'>$total</td></tr>\n</table>";
		return $text;
	}


	function renderScreens() {
		global $sql;

		if($sql -> db_Select("logstats", "*", "log_id='statScreen'")) {
			$row = $sql -> db_Fetch();
			$statScreen = unserialize($row['log_data']);
		}

		/* temp consolidate today's data ... */
		foreach($this -> fileScreenInfo as $name => $count) {
			$statScreen[$name] += $count;
		}

		if(!is_array($statScreen)) {
			return "<div style='text-align: center;'>No statistics yet.</div>";
		}

		if($this -> order) {
			$nsarray = array();
			foreach($statScreen as $key => $info) {
				if(preg_match("/(\d+)x/", $key, $match)) {
					$nsarray[$key] = array('width' => $match[1], 'info' => $info);
				}
			}
			$nsarray = $this -> arraySort($nsarray, 'width', SORT_ASC);
			reset($nsarray);
			$screenArray = array();
			foreach($nsarray as $key => $info) {
				$screenArray[$key] = $info['info'];
			}
			
		} else {
			$screenArray = $this -> arraySort($statScreen, 0);
		}
		
		$total = array_sum($screenArray);
		$text = "<table class='fborder' style='width: 100%;'>\n<tr>\n<td class='fcaption' style='width: 20%;'><a title='".($this -> order ? "sort by total" : "sort alphabetically")."'href='".e_SELF."?6".($this -> order ? "" : ".1" )."'>Screen Resolution</a></td>\n<td class='fcaption' style='width: 70%;'>Total</td>\n<td class='fcaption' style='width: 10%; text-align: center;'>%</td>\n</tr>\n";
		foreach($screenArray as $key => $info) {
			if(strstr($key, "@") && !strstr($key, "undefined") && preg_match("/(\d+)x(\d+)@(\d+)/", $key)) {
				$percentage = round(($info/$total) * 100, 2);
				$text .= "<tr class='forumheader'>
				<td style='width: 20%;'><img src='".e_PLUGIN."log/images/screen.png' alt='' style='vertical-align: middle;'> ".$key."</td>
				<td style='width: 70%;'><img src='".$this -> barImage."' style='width: ".($percentage > 97 ? 97 : $percentage)."%; height: 10px; vertical-align: middle; border: 1px solid #000;' alt='' /> ".$info."</td>
				<td style='width: 10%; text-align: center;'>".$percentage."%</td>
				</tr>\n";
			}
		}
		$text .= "<tr class='forumheader'><td colspan='2'>Total</td><td style='text-align: center;'>$total</td></tr>\n</table>";
		return $text;
	}

	function renderRefers() {
		global $sql;

		if($sql -> db_Select("logstats", "*", "log_id='statReferer'")) {
			$row = $sql -> db_Fetch();
			$statRefer = unserialize($row['log_data']);
		}

		/* temp consolidate today's data ... */
		foreach($this -> fileReferInfo as $name => $count) {
			$statRefer[$name]['ttl'] += $count;
		}

		if(!is_array($statRefer)) {
			return "<div style='text-align: center;'>No statistics yet.</div>";
		}

		$statArray = $this -> arraySort($statRefer, 'ttl');

		foreach($statArray as $key => $info) {
			$total += $info['ttl'];
		}
		$text = "<table class='fborder' style='width: 100%;'>\n<tr>\n<td class='fcaption' style='width: 40%;'>Site Referrals</td>\n<td class='fcaption' style='width: 50%;'>Total</td>\n<td class='fcaption' style='width: 10%; text-align: center;'>%</td>\n</tr>\n";
		foreach($statArray as $key => $info) {
			$percentage = round(($info['ttl']/$total) * 100, 2);
			$text .= "<tr class='forumheader'>
			<td style='width: 40%;'><img src='".e_PLUGIN."log/images/html.png' alt='' style='vertical-align: middle;'> <a href='".$info['url']."' rel='external'>".$key."</a></td>
			<td style='width: 50%;'><img src='".$this -> barImage."' style='width: ".($percentage > 97 ? 97 : $percentage)."%; height: 10px; vertical-align: middle; border: 1px solid #000;' alt='' /> ".$info['ttl']."</td>
			<td style='width: 10%; text-align: center;'>".$percentage."%</td>
			</tr>\n";
		}
		$text .= "<tr class='forumheader'><td colspan='2'>Total</td><td style='text-align: center;'>$total</td></tr>\n</table>";
		return $text;
	}


	function renderQueries() {
		global $sql;

		if($sql -> db_Select("logstats", "*", "log_id='statQuery'")) {
			$row = $sql -> db_Fetch();
			$statQuery = unserialize($row['log_data']);
		}

		/* temp consolidate today's data ... */
		foreach($this -> fileQueryInfo as $name => $count) {
			$statQuery[$name] += $count;
		}

		if(!is_array($statQuery)) {
			return "<div style='text-align: center;'>No statistics yet.</div>";
		}


		$queryArray = $this -> arraySort($statQuery, 0);
		$total = array_sum($queryArray);
		$text = "<table class='fborder' style='width: 100%;'>\n<tr>\n<td class='fcaption' style='width: 60%;'>Search Engine Query Strings</td>\n<td class='fcaption' style='width: 30%;'>Total</td>\n<td class='fcaption' style='width: 10%; text-align: center;'>%</td>\n</tr>\n";
		foreach($queryArray as $key => $info) {
			$percentage = round(($info/$total) * 100, 2);
			$key = str_replace("%20", " ", $key);
			$text .= "<tr class='forumheader'>
			<td style='width: 60%;'><img src='".e_PLUGIN."log/images/screen.png' alt='' style='vertical-align: middle;'> ".$key."</td>
			<td style='width: 30%;'><img src='".$this -> barImage."' style='width: ".($percentage > 97 ? 97 : $percentage)."%; height: 10px; vertical-align: middle; border: 1px solid #000;' alt='' /> ".$info."</td>
			<td style='width: 10%; text-align: center;'>".$percentage."%</td>
			</tr>\n";
		}
		$text .= "<tr class='forumheader'><td colspan='2'>Total</td><td style='text-align: center;'>$total</td></tr>\n</table>";
		return $text;
	}



}

?>