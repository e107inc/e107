<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/themes/templates/templateh.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
$ns = new table;
echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo SITENAME; ?></title>
<link rel="stylesheet" href="<?php echo THEME; ?>style.css" />
<link rel="stylesheet" href="<?php echo e_BASE."files/"; ?>e107.css" />
<?php
if(file_exists(e_BASE."files/style.css")){ echo "\n<link rel='stylesheet' href='".e_BASE."files/style.css' />\n"; }
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
	<?php
echo $pref['meta_tag'][1]."\n";
	?>

<script type="text/javascript" src="files/e107.js"></script>
<?php
if(file_exists(THEME."theme.js")){echo "<script type='text/javascript' src='".THEME."theme.js'></script>";}
if(file_exists(e_BASE."files/user.js")){echo "<script type='text/javascript' src='".e_BASE."files/user.js'></script>\n";}
?>
</head>
<body>
<?php

$page = substr(strrchr(e_SELF, "/"), 1);
if(eregi($page, $CUSTOMPAGES) ? parseheader($CUSTOMHEADER) : parseheader($HEADER)) ;
unset($text);




if($pref['cache_activate'][1]){
	$excempt = "forum_post|test|search";
	$pref['cache_timeout'][1] = ($pref['cache_timeout'][1] ? $pref['cache_timeout'][1] : 3600);
	$sql -> db_Delete("cache", "cache_datestamp+".$pref['cache_timeout'][1]."<".time());
	$url = (e_QUERY ? $_SERVER['PHP_SELF']."?".e_QUERY : $_SERVER['PHP_SELF']);
	if($sql -> db_Select("cache", "*", "cache_url='".$url."' ") && !eregi($excempt, $page)){
		$row = $sql -> db_Fetch(); extract($row);
		echo stripslashes(gzuncompress($cache_data));
		$cachestring = "Cache system activated (content originally served ".strftime("%A %d %B %Y - %H:%M:%S", $cache_datestamp).").";
		$setcache = TRUE;
		require_once(FOOTERF);
		exit;
	}
}

ob_start();

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function parseheader($LAYOUT){
	$tmp = explode("\n", $LAYOUT);
	for($c=0; $c < count($tmp); $c++){ 
		if(preg_match("/[\{|\}]/", $tmp[$c])){
			$str = checklayout($tmp[$c]);
		}else{
			echo $tmp[$c];
		}
	}
}
function checklayout($str){
	global $pref, $style, $userthemes, $udirs, $userclass, $dbq;
	if(strstr($str, "LOGO")){
		echo "<img src='".e_BASE."themes/shared/logo.png' alt='Logo' />\n";
	}else if(strstr($str, "SITENAME")){
		echo SITENAME."\n";
	}else if(strstr($str, "SITETAG")){
		echo SITETAG."\n";
	}else if(strstr($str, "SITELINKS")){
		$linktype = substr($str,(strpos($str, "=")+1), 4);
		define("LINKDISPLAY", ($linktype == "menu" ? 2 : 1));
		sitelinks();
	}else if(strstr($str, "MENU")){
		$sql = new db;
		$ns = new table;
		$menu = trim(chop(preg_replace("/\{MENU=(.*?)\}/si", "\\1", $str)));
		$sql9 = new db;
		$sql9 -> db_Select("menus", "*",  "menu_location='$menu' ORDER BY menu_order");
		while(list($menu_id, $menu_name, $menu_location, $menu_order, $menu_class) = $sql9-> db_Fetch()){
			$sm = FALSE;
			if(!$menu_class){
				$sm = TRUE;
			}else if($menu_class == 253 && USER){
				$sm = TRUE;
			}else if($menu_class == 254 && ADMIN){
				$sm = TRUE;
			}else if(check_class($menu_class)){
				$sm = TRUE;
			}

			if($sm == TRUE){
//				if(ADMIN){
//					echo "dbq: ".$dbq."<br />";
//				}
				require_once(e_BASE."menus/".$menu_name.".php");
//				if(ADMIN){
//					echo "dbq: ".$dbq."<br />";
//				}
			}
			
		}
	}else if(strstr($str, "SETSTYLE")){
		$tmp = explode("=", $str);
		$style = trim(chop(preg_replace("/\{SETSTYLE=(.*?)\}/si", "\\1", $str)));
	}else if(strstr($str, "SITEDISCLAIMER")){
		echo SITEDISCLAIMER;
	}else if(strstr($str, "CUSTOM")){
		$custom = trim(chop(preg_replace("/\{CUSTOM=(.*?)\}/si", "\\1", $str)));
		if($custom == "login"){
			if($pref['user_reg'][1] == 1){
				if(USER == TRUE){
					echo "<table><tr>
					<td class='mediumtext'>Welcome ".USERNAME."&nbsp;&nbsp;&nbsp;</td>
					<td>.:.</td>";
					if(ADMIN == TRUE){
						echo "<td> <a href='".e_ADMIN."admin.php'>".LAN_89."</a></td><td>.:.</td>";
					}
					echo "<td> <a href='" . e_BASE . "usersettings.php'>".LAN_328."</a></td><td>.:.</td><td><a href='".e_BASE."index.php?logout'>Logout</a></td><td>.:.</td></tr></table> ";
				}else{
					echo  "<form method='post' action='".$_SERVER['PHP_SELF']."'>
					<p>
					".LAN_16."<input class='tbox' type='text' name='username' size='15' value='$username' maxlength='20' />&nbsp;&nbsp;
					".LAN_17."<input class='tbox' type='password' name='userpass' size='15' value='' maxlength='20' />&nbsp;&nbsp;
					<input type='checkbox' name='autologin' value='1' />".LAN_329."&nbsp;&nbsp;
					<input class='button' type='submit' name='userlogin' value='Login' />&nbsp;&nbsp;<a href='signup.php'>".LAN_174."</a>
					</p>
					</form>";
				}
			}
		}else if($custom == "search"){
			echo "<form method='post' action='search.php'>
			<p>
			<input class='tbox' type='text' name='searchquery' size='20' value='' maxlength='50' />
			<input class='button' type='submit' name='searchsubmit' value='".LAN_180."' />
			</p>
			</form>";
		}else if($custom == "quote"){
			if(!file_exists(e_BASE."quote.txt")){
				$quote = "Quote file not found ($qotd_file)";
			}else{
				$quotes = file(e_BASE."quote.txt");
				$quote = stripslashes(htmlspecialchars($quotes[rand(0, count($quotes))]));
			}
			echo $quote;
		}
	}else if(strstr($str, "BANNER")){
		$campaign = trim(chop(preg_replace("/\{BANNER=(.*?)\}/si", "\\1", $str)));
		mt_srand ((double) microtime() * 1000000);
		$seed = mt_rand(1,2000000000);
		if($campaign != "{BANNER}"){
			$query = "banner_active=1 AND (banner_startdate=0 OR banner_startdate<=".time().") AND (banner_enddate=0 OR banner_enddate>".time().") AND (banner_impurchased=0 OR banner_impressions<=banner_impurchased) AND banner_campaign='$campaign' ORDER BY RAND($seed)";
		}else{
			$query = "banner_active=1 AND (banner_startdate=0 OR banner_startdate<=".time().") AND (banner_enddate=0 OR banner_enddate>".time().") AND (banner_impurchased=0 OR banner_impressions<=banner_impurchased) ORDER BY RAND($seed)";
		}
		$sql = new db;
		$sql -> db_Select("banner", "*", $query);
		$row = $sql -> db_Fetch(); extract($row);
		echo "<a href='".e_BASE."banner.php?".$banner_id."'><img src='".e_BASE."themes/shared/banners/".$banner_image."' alt='".$banner_clickurl."' style='border:0' /></a>";
		$sql -> db_Update("banner", "banner_impressions=banner_impressions+1 WHERE banner_id='$banner_id' ");
	}
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
?>