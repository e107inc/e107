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
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?php echo SITENAME; ?></title>
    <link rel="stylesheet" href="<?php echo THEME; ?>style.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="content-style-type" content="text/css" />
	<?php
	echo $pref['meta_tag'][1]."\n";
	?>
	<script type="text/javascript" src="files/e107.js"></script>
  </head>
<body>
<?php

$page = substr(strrchr($_SERVER['PHP_SELF'], "/"), 1);
if(eregi($page, $CUSTOMPAGES) ? parseheader($CUSTOMHEADER) : parseheader($HEADER)) ;
unset($text);

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function parseheader($LAYOUT){
	$tmp = explode("\n", $LAYOUT);
	for($c=0; $c < count($tmp); $c++){ 
		if(ereg("{|}", $tmp[$c])){
			$str = checklayout($tmp[$c]);
		}else{
			echo $tmp[$c];
		}
	}
}
function checklayout($str){
	global $pref, $style, $userthemes;
	if(strstr($str, "LOGO")){
		echo "<img src=\"themes/shared/logo.png\" alt=\"Logo\" />\n";
	}else if(strstr($str, "SITENAME")){
		echo SITENAME."\n";
	}else if(strstr($str, "SITETAG")){
		echo SITETAG."\n";
	}else if(strstr($str, "SITELINKS")){
		$linktype = substr($str,(strpos($str, "=")+1), 4);
		define(LINKDISPLAY, ($linktype == "menu" ? 2 : 1));
		sitelinks();
	}else if(strstr($str, "MENU")){
		$sql = new db;
		$ns = new table;
		$menu = trim(chop(preg_replace("/\{MENU=(.*?)\}/si", "\\1", $str)));
		$sql9 = new db;
		$sql9 -> db_Select("menus", "*",  "menu_location='$menu' ORDER BY menu_order");
		while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql9-> db_Fetch()){
			if(eregi("private", $menu_name) && USERCLASS_PRIVATEMENU != TRUE){
				break;
			}else if(eregi("admin", $menu_name) && ADMIN != TRUE){
				break;
			}else{
				require_once("menus/".$menu_name.".php");
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
					<td class=\"mediumtext\">Welcome ".USERNAME."&nbsp;&nbsp;&nbsp;</td>
					<td>.:.</td>";
					if(ADMIN == TRUE){
						echo "<td> <a href=\"admin/admin.php\">Admin</a></td><td>.:.</td>";
					}
					echo "<td> <a href=\"usersettings.php\">Settings</a></td>
					<td>.:.</td>
					<td><a href=\"".$_SERVER['PHP_SELF']."?logout\">Logout</a></td>
					<td>.:.</td>
					</tr></table> ";
				}else{
					echo  "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
					<p>
					Username: <input class=\"tbox\" type=\"text\" name=\"username\" size=\"15\" value=\"$username\" maxlength=\"20\" />&nbsp;&nbsp;
					Password: <input class=\"tbox\" type=\"password\" name=\"userpass\" size=\"15\" value=\"\" maxlength=\"20\" />&nbsp;&nbsp;
					<input type=\"checkbox\" name=\"autologin\" value=\"1\" /> Auto Login&nbsp;&nbsp;
					<input class=\"button\" type=\"submit\" name=\"userlogin\" value=\"Login\" />&nbsp;&nbsp;<a href=\"signup.php\">Signup</a>
					</p>
					</form>";
				}
			}
		}else if($custom == "search"){
			echo "<form method=\"post\" action=\"search.php\">
			<p>
			<input class=\"tbox\" type=\"text\" name=\"searchquery\" size=\"20\" value=\"\" maxlength=\"50\" />
			<input class=\"button\" type=\"submit\" name=\"searchsubmit\" value=\"Search\" />
			</p>
			</form>";
		}else if($custom == "quote"){
			$qotd_file = $pref['qotd_file'][1];
			if(!file_exists($qotd_file)){
				$quote = "Quote file not found ($qotd_file)";
			}else{
				$quotes = file($qotd_file);
				$quote = htmlspecialchars($quotes[rand(0, count($quotes))]);
			}
			echo $quote;
		}
	}else if(strstr($str, "BANNER")){
		$handle=opendir("themes/shared/banners");
		while ($file = readdir($handle)){	
			if($file != "." && $file != ".." && $file != "bannerimages"){
				$files[] = $file;
			}
		}
		closedir($handle);

		$tmp = $files[rand(0,(count($files)-1))];
		require_once("themes/shared/banners/".$tmp);
	}

	







}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
?>