<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /class2.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
//unset any globals created by register_globals being turned ON
while (list($global) = each($GLOBALS)){
	if (!preg_match('/^(_POST|_GET|_COOKIE|_SERVER|_FILES|GLOBALS|HTTP.*|_REQUEST)$/', $global)){
		unset($$global);
	}
}
unset($global);

// If you need to change the names of any of your directories, change the value here then rename the respective folder on your server ...
$ADMIN_DIRECTORY = "e107_admin/";
$FILES_DIRECTORY = "e107_files/";
$IMAGES_DIRECTORY = "e107_images/";
$THEMES_DIRECTORY = "e107_themes/";
$PLUGINS_DIRECTORY = "e107_plugins/";
$HANDLERS_DIRECTORY = "e107_handlers/";
$LANGUAGES_DIRECTORY = "e107_languages/";
$HELP_DIRECTORY = "e107_docs/help/";
$DOWNLOADS_DIRECTORY =  "e107_files/downloads/";
// $DOWNLOADS_DIRECTORY =  "<fullpath>/downloads/";
// eg. $DOWNLOADS_DIRECTORY =  "/home/downloads/";

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
//ob_start ("ob_gzhandler")
ob_start ();
$timing_start = explode(' ', microtime());
error_reporting(E_ERROR | E_WARNING | E_PARSE);


if(!$mySQLserver){
        @include("e107_config.php");
        $a=0; $p="";
        while(!$mySQLserver && $a<5){
                $a++;
                $p.="../";
                @include($p."e107_config.php");
        }
        if(!defined("e_HTTP")){ header("Location:install.php"); exit; }
}

$link_prefix="";
$url_prefix=substr($_SERVER['PHP_SELF'],strlen(e_HTTP),strrpos($_SERVER['PHP_SELF'],"/")+1-strlen(e_HTTP));
$tmp=explode("?",$url_prefix);
$num_levels=substr_count($tmp[0],"/");
for($i=1;$i<=$num_levels;$i++){
        $link_prefix.="../";
}
if(strstr($_SERVER['QUERY_STRING'], "'") || strstr($_SERVER['QUERY_STRING'], ";") ){ die("Access denied."); }
// if( strstr($_SERVER['QUERY_STRING'], "&")){ die("Access denied."); }
if(preg_match("/\[(.*?)\].*?/i", $_SERVER['QUERY_STRING'], $matches)){
define("e_MENU", $matches[1]);
        define("e_QUERY", str_replace($matches[0], "", eregi_replace("&|/?PHPSESSID.*", "", $_SERVER['QUERY_STRING'])));
}else{
        define("e_QUERY", eregi_replace("&|/?PHPSESSID.*", "", $_SERVER['QUERY_STRING']));
}
if(strstr(e_MENU, "debug")){ error_reporting(E_ALL); }
$_SERVER['QUERY_STRING'] = e_QUERY;
define('e_BASE',$link_prefix);
define("e_ADMIN", e_BASE.$ADMIN_DIRECTORY);
define("e_IMAGE", e_BASE.$IMAGES_DIRECTORY);
define("e_THEME", e_BASE.$THEMES_DIRECTORY);
define("e_PLUGIN", (defined("CORE_PATH") ? e_BASE.SUBDIR_SITE."/".$PLUGINS_DIRECTORY : e_BASE.$PLUGINS_DIRECTORY));
define("e_FILE", e_BASE.$FILES_DIRECTORY);
define("e_HANDLER", e_BASE.$HANDLERS_DIRECTORY);
define("e_LANGUAGEDIR", e_BASE.$LANGUAGES_DIRECTORY);
define("e_DOCS", e_BASE.$HELP_DIRECTORY);
define("e_DOCROOT",$_SERVER['DOCUMENT_ROOT']."/");
define("e_UC_PUBLIC", 0);
define("e_UC_READONLY", 251);
define("e_UC_GUEST", 252);
define("e_UC_MEMBER", 253);
define("e_UC_ADMIN", 254);
define("e_UC_NOBODY", 255);
define("ADMINDIR", $ADMIN_DIRECTORY);

if(!@include(e_HANDLER."errorhandler_class.php")){
        echo "<div style='text-align:center; font: 12px Verdana, Tahoma'>Path error</div>";
        exit;
}
set_error_handler("error_handler");
if(!$mySQLuser){ header("location:install.php"); exit; }
define("MPREFIX", $mySQLprefix);

@require_once(e_HANDLER."message_handler.php");
@require_once(e_HANDLER."mysql_class.php");

$sql = new db;
$sql -> db_SetErrorReporting(FALSE);
$merror = $sql -> db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);

if($merror == "e1"){ message_handler("CRITICAL_ERROR", 6,  ": generic, ", "class2.php"); exit;
}else if($merror == "e2"){ message_handler("CRITICAL_ERROR", 7,  ": generic, ", "class2.php"); exit;}

// New parser code #########
$parsethis=array();
if($sql -> db_Select("parser", "parser_pluginname,parser_regexp", "")){
        while($row = $sql -> db_Fetch('nostrip')){
                $parsethis[$row['parser_regexp']]=$row['parser_pluginname'];
        }
}
// End parser code #########

$sql -> db_Select("core", "*", "e107_name='pref' ");
$row = $sql -> db_Fetch();

$tmp = stripslashes($row['e107_value']);
$pref=unserialize($tmp);
if(!is_array($pref)){
        $pref=unserialize($row['e107_value']);
        if(!is_array($pref)){
                ($sql -> db_Select("core", "*", "e107_name='pref' ") ? message_handler("CRITICAL_ERROR", 1,  __LINE__, __FILE__) : message_handler("CRITICAL_ERROR", 2,  __LINE__, __FILE__));
                if($sql -> db_Select("core", "*", "e107_name='pref_backup' ")){
                        $row = $sql -> db_Fetch(); extract($row);
                        $tmp = addslashes(serialize($e107_value ));
                        $sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='pref' ");
                        message_handler("CRITICAL_ERROR", 3,  __LINE__, __FILE__);
                }else{
                message_handler("CRITICAL_ERROR", 4,  __LINE__, __FILE__);
                exit;
        }
}
}

if(!$pref['cookie_name']){ $pref['cookie_name'] = "e107cookie"; }
//if($pref['user_tracking'] == "session"){ @require_once(e_HANDLER."session_handler.php"); }        // if your server session handling is misconfigured uncomment this line and comment the next to use custom session handler
if($pref['user_tracking'] == "session"){ session_start(); }

define("e_SELF", ($pref['ssl_enabled'] ? "https://".$_SERVER['HTTP_HOST'].($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_FILENAME']) : "http://".$_SERVER['HTTP_HOST'].($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_FILENAME'])));

$sql -> db_Select("core", "*", "e107_name='menu_pref' ");
$row = $sql -> db_Fetch();
$tmp = stripslashes($row['e107_value']);
$menu_pref=unserialize($tmp);

$page = substr(strrchr($_SERVER['PHP_SELF'], "/"), 1);
define("e_PAGE", $page);

if($pref['frontpage'] && $pref['frontpage_type'] == "splash"){
	$ip = getip();
	if(!$sql -> db_Count("online", "(*)", "WHERE online_ip='{$ip}' ")){
		online();
		if(is_numeric($pref['frontpage'])){
			header("location:".e_BASE."article.php?".$pref['frontpage'].".255");
			exit;
		} else if(eregi("http", $pref['frontpage'])) {
			header("location: ".$pref['frontpage']);
			exit;
		} else {
			header("location: ".e_BASE.$pref['frontpage'].".php");
			exit;
		}
	}
}

if($pref['cachestatus']){
	require_once(e_HANDLER."cache_handler.php");
	$e107cache = new ecache;
}

function retrieve_cache($query){
	global $e107cache;
	if(!is_object($e107cache)){return FALSE;}
   return $e107cache -> retrieve($query);
}

function set_cache($query, $text){
	global $e107cache;
	if(!is_object($e107cache)){return FALSE;}
	$e107cache -> set($query,$text);
}

function clear_cache($query){
	global $e107cache;
	if(!is_object($e107cache)){return FALSE;}
	return $e107cache -> clear($query);
}

if($pref['del_unv']){
        $threshold = (time() - ($pref['del_unv']*60));
        $sql -> db_Delete("user", "user_ban = 2 AND user_join<'$threshold' ");
}
if($pref['modules']){
	$mods = explode(",",$pref['modules']);
	foreach($mods as $mod){
		if(file_exists(e_PLUGIN."{$mod}/module.php")){
			@require_once(e_PLUGIN."{$mod}/module.php");
		}
	}
}
init_session();
online();

$fp = ($pref['frontpage'] ? $pref['frontpage'].".php" : "news.php index.php");
define("e_SIGNUP", (file_exists(e_BASE."customsignup.php") ? "customsignup.php" : "signup.php"));

if($pref['membersonly_enabled'] && !USER && e_PAGE != e_SIGNUP && e_PAGE != "index.php" && e_PAGE != "fpw.php" && e_PAGE != "login.php" && !strstr(e_PAGE, "admin") && e_PAGE != 'membersonly.php'){
        header("location: ".e_BASE."membersonly.php");
        exit;
}

$sql -> db_Delete("tmp", "tmp_time < '".(time()-300)."' AND tmp_ip!='data' AND tmp_ip!='adminlog' AND tmp_ip!='submitted_link' AND tmp_ip!='reported_post' AND tmp_ip!='var_store' ");

define("SITENAME", $pref['sitename']);
define("SITEURL", (substr($pref['siteurl'], -1) == "/" ? $pref['siteurl'] : $pref['siteurl']."/"));
define("SITEBUTTON", $pref['sitebutton']);
define("SITETAG", $pref['sitetag']);
define("SITEDESCRIPTION", $pref['sitedescription']);
define("SITEADMIN", $pref['siteadmin']);
define("SITEADMINEMAIL", $pref['siteadminemail']);

$search = array("&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "©");
$replace =  array("\"", "'", "\\", '\"', "\'", "&#169;");
define("SITEDISCLAIMER", str_replace($search, $replace, $pref['sitedisclaimer']));

$language = ($pref['sitelanguage'] ? $pref['sitelanguage'] : "English");

define("e_LAN", $language);
define("e_LANGUAGE", (!USERLAN || !defined("USERLAN") ? $language : USERLAN));

@include(e_LANGUAGEDIR.e_LANGUAGE."/".e_LANGUAGE.".php");

if($pref['maintainance_flag'] && ADMIN == FALSE && !eregi("admin", e_SELF)){
        @include(e_LANGUAGEDIR.e_LANGUAGE."/lan_sitedown.php");
        @include(e_LANGUAGEDIR."English/lan_sitedown.php");
        @require_once(e_BASE."sitedown.php"); exit;
}

if(defined("CORE_PATH") && ($page == "index.php" || !$page)){ $page = "news.php"; }

if(strstr(e_SELF, $ADMIN_DIRECTORY) || strstr(e_SELF, "admin.php")){
        @include(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_".e_PAGE);
        @include(e_LANGUAGEDIR."English/admin/lan_".e_PAGE);
}else{
        @include(e_LANGUAGEDIR.e_LANGUAGE."/lan_".e_PAGE);
        @include(e_LANGUAGEDIR."English/lan_".e_PAGE);
}

if(IsSet($_POST['userlogin'])){
        @require_once(e_HANDLER."login.php");
        $usr = new userlogin($_POST['username'], $_POST['userpass'], $_POST['autologin']);
}

if(e_QUERY == "logout"){
        $ip = getip();
        $udata = (USER === TRUE) ? USERID.".".USERNAME : "0";
        $sql -> db_Update("online", "online_user_id = '0', online_pagecount=online_pagecount+1 WHERE online_user_id = '{$udata}' LIMIT 1");
        if($pref['user_tracking'] == "session"){ session_destroy(); $_SESSION[$pref['cookie_name']] = ""; }
        cookie($pref['cookie_name'], "", (time()-2592000));
        echo "<script type='text/javascript'>document.location.href='".e_BASE."index.php'</script>\n";
        exit;
}
ban();

define("TIMEOFFSET", $pref['time_offset']);
define("FLOODTIME", $pref['flood_time']);
define("FLOODHITS", $pref['flood_hits']);

if(strstr(e_SELF, $ADMIN_DIRECTORY) && $pref['admintheme'] && !$_POST['sitetheme']){
        if(strstr(e_SELF, "menus.php")){
			checkvalidtheme($pref['sitetheme']);
        } else if(strstr(e_SELF, "newspost.php")){
			define("MAINTHEME", e_THEME.$pref['sitetheme']."/");
			checkvalidtheme($pref['admintheme']);
        } else {
			checkvalidtheme($pref['admintheme']);
        }
} else {
         if(USERTHEME != FALSE && USERTHEME != "USERTHEME"){
			checkvalidtheme(USERTHEME);
		} else {
			checkvalidtheme($pref['sitetheme']);
		}
}
@require_once(THEME."theme.php");

if($pref['anon_post'] ? define("ANON", TRUE) : define("ANON", FALSE));
if(Empty($pref['newsposts']) ? define("ITEMVIEW", 15) : define("ITEMVIEW", $pref['newsposts']));
if($pref['flood_protect']){  define(FLOODPROTECT, TRUE); define(FLOODTIMEOUT, $pref['flood_timeout']); }

define ("HEADERF", e_THEME."templates/header".$layout.".php");
define ("FOOTERF", e_THEME."templates/footer".$layout.".php");
if(!file_exists(HEADERF)){message_handler("CRITICAL_ERROR", "Unable to find file: ".HEADERF,  __LINE__-2, __FILE__);}
if(!file_exists(FOOTERF)){message_handler("CRITICAL_ERROR", "Unable to find file: ".HEADERF,  __LINE__-2, __FILE__);}

define("LOGINMESSAGE", "");
$ns = new e107table;

define("OPEN_BASEDIR", (ini_get('open_basedir') ? TRUE : FALSE));
define("SAFE_MODE", (ini_get('safe_mode') ? TRUE : FALSE));
define("MAGIC_QUOTES_GPC", (ini_get('magic_quotes_gpc') ? TRUE : FALSE));
define("FILE_UPLOADS", (ini_get('file_uploads') ? TRUE : FALSE));
define("INIT", TRUE);

define("e_ADMIN", $e_BASE.$ADMIN_DIRECTORY);

//@require_once(e_HANDLER."IPB_int.php");

//@require_once(e_HANDLER."debug_handler.php");

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//


class e107table{
	function tablerender($caption, $text, $mode="default", $return=FALSE){
		/*
		# Render style table
		# - parameter #1:                string $caption, caption text
		# - parameter #2:                string $text, body text
		# - return                                null
		# - scope                                        public
		*/
		if(function_exists("theme_tablerender")){
			$result = call_user_func("theme_tablerender",$caption,$text,$mode,$return);
			if($result == "return"){return;}
			extract($result);
		}
		if($return){
			ob_end_flush();
			ob_start();
			tablestyle($caption, $text, $mode);
			$ret = ob_get_contents();
			ob_end_clean();
			return($ret);
		}else{
		tablestyle($caption, $text, $mode);
	}
}
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function e107_parse($text,$referrer){
	preg_match_all("#{CODE=(.*?)}#",$text,$matches,PREG_SET_ORDER);
	for ($i=0; $i< count($matches); $i++) {
		$p = explode(".",$matches[$i][1]);
		$parse_func = "parse_".$p[1];
		if(!function_exists($parse_func)){
			$parse_file = ('CORE' == $p[0]) ?  e_HANDLER."parse/" : e_PLUGIN.$p[0]."/parse/";
			$parse_file .= "parse_{$p[1]}.php";
			if(file_exists($parse_file)){
				@require_once($parse_file);
			}
		}
		if(function_exists($parse_func)){
			$newtext = call_user_func($parse_func,$matches[$i],$referrer);
		} else {
			$newtext = "";
		}
		$text = str_replace($matches[$i][0],$newtext,$text);
	}

	global $parsethis;
	if($parsethis){
		@require_once(e_HANDLER.'parser_functions.php');
		foreach($parsethis as $parser_regexp => $parser_name){
			preg_match_all($parser_regexp,$text,$matches,PREG_SET_ORDER);
			for ($i=0; $i< count($matches); $i++) {
				if($parser_name != "e107_core" && file_exists(e_PLUGIN.$parser_name.'/parser.php')){
					@require_once(e_PLUGIN.$parser_name.'/parser.php');
				}
				if(function_exists($parser_name.'_parse')) {
					$newtext=call_user_func($parser_name.'_parse',$matches[$i],$referrer);
					$text = str_replace($matches[$i][0],$newtext,$text);
				}
			}
		}
		$text = preg_replace("#{{.*?}}#","",$text);
	}
	return $text;
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class textparse{

        var $emotes;
        var $searcha;
        var $searchb;
        var $replace;
        var $profan;

        function textparse(){
                // constructor
                global $pref;

                if($pref['profanity_filter']){
                        $this->profan = str_replace(",", "|", $pref['profanity_words']);
                }

                if($pref['smiley_activate']){
                        $sql = new db;
                        $sql -> db_Select("core", "*", "e107_name='emote'");
                        $row = $sql -> db_Fetch(); extract($row);
                        $this->emotes = unserialize($e107_value);

                        $c=0;
                        while(list($code, $name) = each($this->emotes[$c])){
                                $this->searcha[$c] = " ".$code;
                                $this->searchb[$c] = "\n".$code;
                                $this->replace[$c] = " <img src='".e_IMAGE."emoticons/$name' alt='' style='vertical-align:middle; border:0' /> ";
                                $c++;
                        }
                }
        }

        function editparse($text, $mode="off"){
                /*
                # Edit parse
                # - parameter #1:                string $text, text to parse
                # - parameter #2:                string $mode, on=links not parsed, default=off
                # - return                                parsed text
                # - scope                                        public
                */
                //                $text = stripslashes($text);
                $search = array();
                $replace = array();
                $search[0] = "/\<div class=\"indent\"\>\<i\>Originally posted by (.*?)\<\/i\>\<br \/\>\"(.*?)\"\<\/div\>/si";
                $replace[0] = '[quote=\1]\2[/quote]';
                $search[1] = "/\<div class=\"indent\"\>\<i\>Originally posted by (.*?)\<\/i\> ...\<br \/\>\"(.*?)\"\<\/div\>/si";
                $replace[1] = '[quote=\1]\2[/quote]';
                $search[2] = "/\<div class=\"indent\"\>(.*?)\<\/div\>/si";
                $replace[2] = '[blockquote]\1[/blockquote]';
                $search[3] = "/\<b>(.*?)\<\/b\>/si";
                $replace[3] = '[b]\1[/b]';
                $search[4] = "/\<i>(.*?)\<\/i\>/si";
                $replace[4] = '[i]\1[/i]';
                $search[5] = "/\<u>(.*?)\<\/u\>/si";
                $replace[5] = '[u]\1[/u]';
                $search[6] = "/\<img alt=\"\" src=\"(.*?)\" \/>/si";
                $replace[6] = '[img]\1[/img]';
                $search[7] =  "/\<div style=\"text-align:center\"\>(.*?)\<\/div\>/si";
                $replace[7] = '[center]\1[/center]';
                $search[8] =  "/\<div style=\"text-align:left\"\>(.*?)\<\/div\>/si";
                $replace[8] = '[left]\1[/left]';
                $search[9] =  "/\<div style=\"text-align:right\"\>(.*?)\<\/div\>/si";
                $replace[9] = '[right]\1[/right]';
                $search[10] = "/\<code>(.*?)\<\/code\>/si";
                $replace[10] = '[code]\1[/code]';
                if($mode == "off"){
                        $search[11] = "/\<a href=\"(.*?)\">(.*?)<\/a>/si";
                        $replace[11] = '[link=\\1]\\2[/link]';
                }
                $search[12] = "#\[edited\](.*?)\[/edited\]#si";
                $replace[12] = '';
                $text = preg_replace($search, $replace, $text);
                return $text;
        }
        

        function tpj($text, $strip=FALSE){
			$search[0] = "#script#si";
			$replace[0] = 'scri<i></i>pt';
			$search[1] = "#document#si";
			$replace[1] = 'docu<i></i>ment';
			$search[2] = "#expression#si";
			$replace[2] = 'expres<i></i>sion';
			$search[3] = "#onmouseover#si";
			$replace[3] = 'onmouse<i></i>over';
			$search[4] = "#onclick#si";
			$replace[4] = 'on<i></i>click';
			$search[5] = "#onmousedown#si";
			$replace[5] = 'onmouse<i></i>down';
			$search[6] = "#onmouseup#si";
			$replace[6] = 'onmouse<i></i>up';
			$search[7] = "#ondblclick#si";
			$replace[7] = 'on<i></i>dblclick';
			$search[8] = "#onmouseout#si";
			$replace[8] = 'onmouse<i></i>out';
			$search[9] = "#onmousemove#si";
			$replace[9] = 'onmouse<i></i>move';
			$search[10] = "#onload#si";
			$replace[10] = 'on<i></i>load';
			$search[11] = "#background:url#si";
			$replace[11] = 'background<i></i>:url';
			if($strip){
				$text = strip_tags($text);
			}
         $text = preg_replace($search, $replace, $text);
         return $text;
        }

        function tpa($text, $mode="off", $referrer="", $highlight_search){
                /*
                # Post parse
                # - parameter #1:                string $text, text to parse
                # - parameter #2:                string $mode, on=line breaks not replaced, default off
                # - return                                        parsed text
                # - scope                                        public
                */
                global $pref;
                $text = " ".$text;
                if($pref['profanity_filter'] && $this->profan){
                        $text = eregi_replace($this->profan, $pref['profanity_replace'], $text);
                }
                if($pref['smiley_activate']){
                        $text = str_replace($this->searcha, $this->replace, $text);
                        $text = str_replace($this->searchb, $this->replace, $text);
                }
                $text = str_replace("$", "&#36;", $text);
			if($referrer != "admin"){
					$text = $this -> tpj($text);
				}
			if($mode != "nobreak"){ $text = nl2br($text); }
			$text = preg_replace("/\n/i", " ", $text);
			$text = str_replace("<br />", " <br />" , $text);
			$text = e107_parse($text,$referrer);
			$text = $this -> wrap($text, $highlight_search);
			$text = $this -> bbcode($text);
			if(MAGIC_QUOTES_GPC){ $text = stripslashes($text); }
			$search = array("&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&lt;span", "&lt;/span");
			$replace =  array("\"", "'", "\\", '\"', "\'", "<span", "</span");
			$text = str_replace($search, $replace, $text);
			$text = str_replace("<br /><br />", "<br />", $text);
			$text = preg_replace("#([\n ])([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $text);
			$text = substr($text, 1);
			$text = code($text, "notdef");
			$text = html($text);
			return $text;
		}

		function wrap($text, $highlight_search=FALSE){
			global $pref;
			$wrapcount = 100;
			$message_array = explode(" ", $text);
			for($i=0; $i<=(count($message_array)-1); $i++){
				if(strlen($message_array[$i]) > $wrapcount){
					if(substr($message_array[$i], 0, 7) == "http://"){
						if(substr($message_array[$i], -1) == "."){
							$message_array[$i] = substr_replace($message_array[$i], "", -1);
					}
						$url = str_replace("http://", "", $message_array[$i]);  
						$url = explode("/", $url);  
						$url = $url[0];
						$message_array[$i] = "<a href='".$message_array[$i]."' rel='external'>[".$url."]</a>";
					}else{
						if(!strstr($message_array[$i], "[link=") && !strstr($message_array[$i], "[url=") && !strstr($message_array[$i], "href=") && !strstr($message_array[$i], "src=") && !strstr($message_array[$i], "action=") && !strstr($message_array[$i], "onclick=") && !strstr($message_array[$i], "url(") && !strstr($message_array[$i], "[img")){
							$message_array[$i] = preg_replace("/([^\s]{".$wrapcount."})/", "$1<br />", $message_array[$i]);
						}
						}
				}else{
					if(!strstr($message_array[$i], "[link=") && !strstr($message_array[$i], "[url=") && !strstr($message_array[$i], "href=") && !strstr($message_array[$i], "src=") && !strstr($message_array[$i], "action=") && !strstr($message_array[$i], "onclick=") && !strstr($message_array[$i], "url(") && !strstr($message_array[$i], "[img")){
						$search = "#([\t\r\n ])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?)#i";
						$replace = ($pref['links_new_window'] ? '\1<a href="http://\2.\3" rel="external";">\2.\3</a>' : '\1<a href="http://\2.\3" >\2.\3</a>');
						$message_array[$i] = preg_replace($search, $replace, $message_array[$i]);
						$search = "#([a-z0-9]+?){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^ \"\n\r\t<]*)?([^.,]))#i";
						$replace = ($pref['links_new_window'] ? '<a href="\1://\2" rel="external";">\1://\2</a>' : '<a href="\1://\2">\1://\2</a>');
						$message_array[$i] = preg_replace($search, $replace, $message_array[$i]);
							if($highlight_search && !strstr($message_array[$i], "http://")){
								$tmp = explode(" ", $_POST['search_query']);
								foreach($tmp as $key){
									if(eregi($key, $message_array[$i])){
										$message_array[$i] = eregi_replace($key, "<span class='searchhighlight'>$key</span>", $message_array[$i]);	
									}
								}
							}
					}
						}
			}
			$text = implode(" ",$message_array);
			return $text;
		}
		function bbcode($text, $mode="off", $referrer="") {
				global $pref;			
				$text = " " . $text;
				if (! (strpos($text, "[") && strpos($text, "]")) )
				{
						$text = substr($text, 1);
						return $text;
				}
			
                $search[0] = "#\[link\]([a-z]+?://){1}(.*?)\[/link\]#si";
                $replace[0] = ($pref['links_new_window'] ? '<a href="\1\2" rel="external">\1\2</a>' : '<a href="\1\2">\1\2</a>');
                $search[1] = "#\[link\](.*?)\[/link\]#si";
                $replace[1] = ($pref['links_new_window'] ? '<a href="http://\1" rel="external">\1</a>' : '<a href="http://\1">\1</a>');
                $search[2] = "#\[link=([a-z]+?://){1}(.*?)\](.*?)\[/link\]#si";
                $replace[2] = ($pref['links_new_window'] ? '<a href="\1\2" rel="external">\3</a>' : '<a href="\1\2">\3</a>');
                $search[3] = "#\[link=(.*?)\](.*?)\[/link\]#si";
                $replace[3] = ($pref['links_new_window'] ? '<a href="\1" rel="external">\2</a>' : '<a href="\1">\2</a>');
                $search[4] = "#\[email\](.*?)\[/email\]#si";
                $replace[4] = '<a href="mailto:\1">\1</a>';
                $search[5] = "#\[email=(.*?){1}(.*?)\](.*?)\[/email\]#si";
                $replace[5] = '<a href="mailto:\1\2">\3</a>';
                $search[6] = "#\[url\]([a-z]+?://){1}(.*?)\[/url\]#si";
                $replace[6] = ($pref['links_new_window'] ? '<a href="\1\2" rel="external">\1\2</a>' : '<a href="\1\2">\1\2</a>');
                $search[7] = "#\[url\](.*?)\[/url\]#si";
                $replace[7] = ($pref['links_new_window'] ? '<a href="http://\1"> rel="external"\1</a>' : '<a href="http://\1">\1</a>');
                $search[8] = "#\[url=([a-z]+?://){1}(.*?)\](.*?)\[/url\]#si";
                $replace[8] = ($pref['links_new_window'] ? '<a href="\1\2" rel="external">\3</a>' : '<a href="\1\2">\3</a>');
                $search[9] = "/\[quote=(.*?)\](.*?)/si";
                $replace[9] = '<div class=\'indent\'><i>Originally posted by \1</i> ...<br />"\2"';
                $search[25] = "/\[\/quote\]/si";
                $replace[25] = '</div>';
                $search[10] = "#\[b\](.*?)\[/b\]#si";
                $replace[10] = '<b>\1</b>';
                $search[11] = "#\[i\](.*?)\[/i\]#si";
                $replace[11] = '<i>\1</i>';
                $search[12] = "#\[u\](.*?)\[/u\]#si";
                $replace[12] = '<u>\1</u>';
                $search[13] = "#\[img\](.*?)\[/img\]#si";
                if(($pref['image_post'] && check_class($pref['image_post_class'])) || $referrer == "admin"){
                        $replace[13] = '<img src=\'\1\' alt=\'\' style=\'vertical-align:middle; border:0\' />';
                }else if(!$pref['image_post_disabled_method'] && !ADMIN){
                        $replace[13] = 'Image: \1';
                }else if(!ADMIN){
                        $replace[13] = '[ image disabled ]';
                }else{
                $replace[13] = '<img src=\'\1\' alt=\'\' style=\'vertical-align:middle; border:0\' />';
				}

				$search[14] = "#\[center\](.*?)\[/center\]#si";
				$replace[14] = '<div style=\'text-align:center\'>\1</div>';
				$search[15] = "#\[left\](.*?)\[/left\]#si";
				$replace[15] = '<div style=\'text-align:left\'>\1</div>';
				$search[16] = "#\[right\](.*?)\[/right\]#si";
				$replace[16] = '<div style=\'text-align:right\'>\1</div>';
				$search[17] = "#\[blockquote\](.*?)\[/blockquote\]#si";
				$replace[17] = '<div class=\'indent\'>\1</div>';
				$search[19] = "/\[color=(.*?)\](.*?)\[\/color\]/si";
				$replace[19] = '<span style=\'color:\1\'>\2</span>';
				$search[20] = "/\[size=([1-2]?[0-9])\](.*?)\[\/size\]/si";
				$replace[20] = '<span style=\'font-size:\1px\'>\2</span>';
				$search[21] = "#\[edited\](.*?)\[/edited\]#si";
				$replace[21] = '<span class=\'smallblacktext\'>[ \1 ]</span>';
				$search[22] = "#\[br\]#si";
				$replace[22] = '<br />';

				if($pref['forum_attach'] && FILE_UPLOADS || $referrer == "admin"){
						$search[23] = "#\[file=(.*?)\](.*?)\[/file\]#si";
						$replace[23] = '<a href="\1"><img src="'.e_IMAGE.'generic/attach1.png" alt="" style="border:0; vertical-align:middle" /> \2</a>';
				}else{
				$search[23] = "#\[file=(.*?)\](.*?)\[/file\]#si";
				$replace[23] = '[ file attachment disabled ]';
				}

				$search[24] = "#\[quote\](.*?)\[/quote\]#si";
				$replace[24] = '<i>"\1"</i>';
				$text = preg_replace($search, $replace, $text);
				return $text;
		}
		
        function formtpa($text, $mode="admin"){
                global $sql, $pref;

                if($mode != "admin" && !ADMIN){

                        for($r=0; $r<=strlen($text); $r++){
                                $chars[$text[$r]] = 1;
                        }
                        $ch = array_count_values($chars);
                        if((strlen($text) > 50 && $ch[1] < 10) || (strlen($text) > 10 && $ch[1] < 3) || (strlen($text) > 100 && $ch[1] < 20)){
                                echo "<script type='text/javascript'>document.location.href='index.php'</script>\n";
                                exit;
                        }
                        $text = code($text);
                        if(!$pref['html_post']){ $text = str_replace("<", "&lt;", $text); str_replace(">", "&gt;", $text); }
                        $text = str_replace("<script", "&lt;script", $text);
                        $text = str_replace("<iframe", "&lt;iframe", $text);
                        /*
                        if(($pref['image_post_class'] == 253 && !USER) || ($pref['image_post_class'] == 254 && !ADMIN)){
                        $text = preg_replace("#\[img\](.*?)\[/img\]#si", '&nbsp;', $text);
                        }else if(!check_class($pref['image_post_class'])){
                        $text = preg_replace("#\[img\](.*?)\[/img\]#si", '&nbsp;', $text);
                        }
                        */

                }else if(ADMIN && !strstr(e_PAGE, "newspost.php") && !strstr(e_PAGE, "article.php") && !strstr(e_PAGE, "review.php")){
                        $text = preg_replace("#\[img\](.*?)\[/img\]#si", '<img src=\'\1\' alt=\'\' style=\'vertical-align:middle; border:0\' />', $text);
                }

                if(MAGIC_QUOTES_GPC){ $text = stripslashes($text); }
                $search = array("\"", "'", "\\", '\"', "\'", "$");
                $replace = array("&quot;", "&#39;", "&#92;", "&quot;", "&#39;", "&#036;");
                $text = str_replace($search, $replace, $text);
                return $text;
        }

        function formtparev($text){
                $search = array("&quot;", "&#39;", "&#92;", "&quot;", "&#39;");
                $replace = array("\"", "'", "\\", '\"', "\'");
                $text = str_replace($search, $replace, $text);
                return $text;
        }

}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class convert{

        function convert_date($datestamp, $mode="long"){
                /*
                # Date convert
                # - parameter #1:                string $datestamp, unix stamp
                # - parameter #2:                string $mode, date format, default long
                # - return                                parsed text
                # - scope                                        public
                */
                global $pref;

                $datestamp += (TIMEOFFSET*3600);
                if($mode == "long"){
                        return strftime($pref['longdate'], $datestamp);
                }else if($mode == "short"){
                        return strftime($pref['shortdate'], $datestamp);
                }else{
                        return strftime($pref['forumdate'], $datestamp);
                }
        }
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function check_email($var){
	return (preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $var)) ? $var : FALSE;
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function check_class($var, $userclass=USERCLASS, $debug=FALSE){
        if(preg_match ("/^([0-9]+)$/", $var)){
                if($var == e_UC_MEMBER && USER==TRUE){return TRUE;}
                if($var == e_UC_GUEST && USER==FALSE){return TRUE;}
                if($var == e_UC_PUBLIC){return TRUE;}
                if($var == e_UC_NOBODY) {return FALSE;}
                if($var == e_UC_ADMIN && ADMIN) {return TRUE;}
                if($var == e_UC_READONLY){return TRUE;}
        }
        if($debug){ echo "USERCLASS: ".$userclass.", \$var = $var : "; }
        if(!defined("USERCLASS") || $userclass == ""){
                if($debug){ echo "FALSE<br />"; }
                return FALSE;
        }
        // user has classes set - continue
        if(preg_match ("/^([0-9]+)$/", $var)){
                $tmp = explode(".", $userclass);
                if(is_numeric(array_search($var,$tmp))){
                        if($debug){ echo "TRUE<br />"; }
                        return TRUE;
                }
        } else {
                // var is name of class ...
                $sql = new db;
                if($sql -> db_Select("userclass_classes", "*", "userclass_name='$var' ")){
                        $row = $sql -> db_Fetch();
                        $tmp = explode(".", $userclass);
                        if(is_numeric(array_search($row['userclass_id'],$tmp))){
                                if($debug){ echo "TRUE<br />"; }
                                return TRUE;
                        }
                }
        }
        if($debug){  echo "NOTNUM! FALSE<br />"; }
        return FALSE;
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function getperms($arg, $ap = ADMINPERMS){
        global $PLUGINS_DIRECTORY;
        if($ap == "0"){return TRUE;}
        if($ap == ""){return FALSE;}
        $ap = ".".$ap;
        if($arg == "P" && preg_match("#(.*?)/".$PLUGINS_DIRECTORY."(.*?)/(.*?)#",e_SELF,$matches) ){
                $psql = new db;
                if($psql -> db_Select("plugin","plugin_id","plugin_path = '".$matches[2]."' ")){
                        $row = $psql -> db_Fetch();
                        $arg = "P".$row[0];
                }
        }
        if(preg_match("#\.".$arg."\.#", $ap)){
                return TRUE;
        } else {
                return FALSE;
        }
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

function save_prefs($table = "core", $uid=USERID){
        global $pref, $user_pref;
        $sql = new db;
        if($table == "core"){
                $tmp = addslashes(serialize($pref));
                $sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='pref'");
        } else {
                $tmp = addslashes(serialize($user_pref));
                $sql -> db_Update("user", "user_prefs='$tmp' WHERE user_id=$uid");
                return $tmp;
        }
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function online(){
        $page = (strstr(e_SELF, "forum_")) ? e_SELF.".".e_QUERY : e_SELF;
        $page = (strstr(e_SELF, "comment")) ? e_SELF.".".e_QUERY : $page;
        $page = (strstr(e_SELF, "content")) ? e_SELF.".".e_QUERY : $page;
        $online_timeout = 300;
        $online_warncount = 90;
        $online_bancount = 100;
        global $sql;
        global $listuserson;
        $ip = getip();
        $udata = (USER === TRUE) ? USERID.".".USERNAME : "0";

        if(USER){
                // Find record that matches IP or visitor, or matches user info
                if($sql -> db_Select("online","*","(online_ip='{$ip}' AND online_user_id = '0') OR online_user_id = '{$udata}'")){
                        $row = $sql -> db_Fetch();
                        extract($row);
                        if($online_user_id == $udata) {  //Matching user record
                                if($online_timestamp < (time() - $online_timeout)){  //It has been at least 'timeout' seconds since this user has connected
                                        //Update user record with timestamp, current IP, current page and set pagecount to 1
                                        $query = "online_timestamp='".time()."', online_ip='{$ip}', online_location='$page', online_pagecount=1 WHERE online_user_id='{$online_user_id}' LIMIT 1";
                                } else {
                                        if(!ADMIN){$online_pagecount++;}
                                        //Update user record with current IP, current page and increment pagecount
                                        $query = "online_ip='{$ip}', online_location='$page', online_pagecount={$online_pagecount} WHERE online_user_id='{$online_user_id}' LIMIT 1";
                                }
                        } else {  //Found matching visitor record (ip only) for this user
                                if($online_timestamp < (time() - $online_timeout)){  //It has been at least 'timeout' seconds since this user has connected
                                        //Update record with timestamp, current IP, current page and set pagecount to 1
                                        $query = "online_timestamp='".time()."', online_user_id='{$udata}', online_location='$page', online_pagecount=1 WHERE online_ip='{$ip}' AND online_user_id='0' LIMIT 1";
                                } else {
                                        if(!ADMIN){$online_pagecount++;}
                                        //Update record with current IP, current page and increment pagecount
                                        $query = "online_user_id='{$udata}', online_location='$page', online_pagecount={$online_pagecount} WHERE online_ip='{$ip}' AND online_user_id='0' LIMIT 1";
                                }
                        }
                        $sql -> db_Update("online", $query);
                } else {
                        $sql -> db_Insert("online", " '".time()."', 'null', '".$udata."', '".$ip."', '".$page."', 1");
                }
        } else {  //Current page request is from a visitor
                if($sql -> db_Select("online","*","online_ip='{$ip}' AND online_user_id = '0'")){
                        $row = $sql -> db_Fetch();
                        extract($row);
                        if($online_timestamp < (time() - $online_timeout)){  //It has been at least 'timeout' seconds since this ip has connected
                                //Update record with timestamp, current page, and set pagecount to 1
                                $query = "online_timestamp='".time()."', online_location='$page', online_pagecount=1 WHERE online_ip='{$ip}' AND online_user_id='0' LIMIT 1";
                        } else {
                                //Update record with current page and increment pagecount
                                $online_pagecount++;
                             //   echo "here {$online_pagecount}";
                                $query = "online_location='$page', online_pagecount={$online_pagecount} WHERE online_ip='{$ip}' AND online_user_id='0' LIMIT 1";
                        }
                        $sql -> db_Update("online", $query);
                } else {
                        $sql -> db_Insert("online", " '".time()."', 'null', '0', '{$ip}', '{$page}', 1");
                }
        }

        if($online_pagecount > $online_bancount && $online_ip !="127.0.0.1"){
                $sql -> db_Insert("banlist", "'$ip', '0', 'Hit count exceeded ($online_pagecount requests within allotted time)' ");
                exit;
        }
        if($online_pagecount == $online_warncount && $online_ip !="127.0.0.1"){
                echo "<div style='text-align:center; font: 11px verdana, tahoma, arial, helvetica, sans-serif;'><b>Warning!</b><br /><br />The flood protection on this site has been activated and you are warned that if you carry on requesting pages you could be banned.<br /></div>";
                exit;
        }

        $sql -> db_Delete("online", "online_timestamp<".(time() - $online_timeout));
        $total_online = $sql -> db_Count("online");
        if($members_online = $sql -> db_Select("online", "*", "online_user_id != '0' ")){
                $listuserson = array();
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        list($oid,$oname) = explode(".",$online_user_id,2);
                        $member_list .= "<a href='".e_BASE."user.php?id.$oid'>$oname</a> ";
                        $listuserson[$online_user_id] = $online_location;
                }
        }
        define("TOTAL_ONLINE", $total_online);
        define("MEMBERS_ONLINE", $members_online);
        define("GUESTS_ONLINE", $total_online - $members_online);
        define("ON_PAGE", $sql -> db_Count("online", "(*)", "WHERE online_location='$page' "));
        define("MEMBER_LIST", $member_list);
}

//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function cachevars($id, $var){
        global $cachevar;
        $cachevar[$id] = $var;
}
function getcachedvars($id){
        global $cachevar;
        return ($cachevar[$id] ? $cachevar[$id] : FALSE);
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function getip(){
        /*
        # Get IP address
        #
        # - parameters                none
        # - return                                valid IP address
        # - scope                                        public
        */
        if(getenv('HTTP_X_FORWARDED_FOR')){
                $ip = $_SERVER['REMOTE_ADDR'];
                if(preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", getenv('HTTP_X_FORWARDED_FOR'), $ip3)){
                        $ip2 = array('/^0\./', '/^127\.0\.0\.1/', '/^192\.168\..*/', '/^172\.16\..*/', '/^10..*/', '/^224..*/', '/^240..*/');
                        $ip = preg_replace($ip2, $ip, $ip3[1]);
                }
        }else{
                $ip = $_SERVER['REMOTE_ADDR'];
        }
        if($ip == ""){ $ip = "x.x.x.x"; }
        return $ip;
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class floodprotect{
        function flood($table, $orderfield){
                /*
                # Test for possible flood
                #
                # - parameter #1                string $table, table being affected
                # - parameter #2                string $orderfield, date entry in respective table
                # - return                                boolean
                # - scope                                        public
                */
                $sql = new db;
                if(FLOODPROTECTION == TRUE){
                        $sql -> db_Select($table, "*", "ORDER BY ".$orderfield." DESC LIMIT 1", "no_where");
                        $row = $sql -> db_Fetch();
                        return ($row[$orderfield] > (time() - FLOODTIMEOUT) ? FALSE : TRUE);
                } else {
                        return TRUE;
                }
        }
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function init_session(){
        /*
        # Validate user
        #
        # - parameters                none
        # - return                                boolean
        # - scope                                public
        */
        global $sql, $pref, $user_pref, $sql;

        if(!$_COOKIE[$pref['cookie_name']] && !$_SESSION[$pref['cookie_name']]){
                define("USER", FALSE); define("USERTHEME", FALSE); define("ADMIN", FALSE);define("GUEST", TRUE);
        } else {
                list($uid, $upw) = ($_COOKIE[$pref['cookie_name']] ? explode(".", $_COOKIE[$pref['cookie_name']]) : explode(".", $_SESSION[$pref['cookie_name']]));
                if(empty($uid) || empty($upw)){         // corrupt cookie?
                        cookie($pref['cookie_name'], "", (time()-2592000));
                        $_SESSION[$pref['cookie_name']] = "";
                        session_destroy();
                        define("ADMIN", FALSE); define("USER", FALSE); define("LOGINMESSAGE", "Corrupted cookie detected - logged out.<br /><br />");
                        return(FALSE);
                }
                if($sql -> db_Select("user", "*", "user_id='$uid' AND md5(user_password)='$upw'")){
                        $result = $sql -> db_Fetch(); extract($result);
                        define("USERID", $user_id); 
                        define("USERNAME", $user_name); 
                        define("USERURL", $user_website); 
                        define("USEREMAIL", $user_email); 
                        define("USER", TRUE); 
                        define("USERCLASS", $user_class);
                        define("USERREALM", $user_realm);
								define("USERVIEWED", $user_viewed);
								define("USERIMAGE", $user_image);
								define("USERSESS", $user_sess);
                        if($user_currentvisit + 3600 < time()){
                                $user_lastvisit = $user_currentvisit;
                                $user_currentvisit = time();
                                $sql -> db_Update("user", "user_visits=user_visits+1, user_lastvisit='$user_lastvisit', user_currentvisit='$user_currentvisit', user_viewed='' WHERE user_name='".USERNAME."' ");
                        }
								define("USERLV", $user_lastvisit); 
                        if($user_ban == 1){ exit; }
                        $user_pref = unserialize($user_prefs);
                        if(IsSet($_POST['settheme'])){
                                $user_pref['sitetheme'] = ($pref['sitetheme'] == $_POST['sitetheme'] ? "" : $_POST['sitetheme']);
                                save_prefs($user);
                        }
                        if(IsSet($_POST['setlanguage'])){
                                $user_pref['sitelanguage'] = ($pref['sitelanguage'] == $_POST['sitelanguage'] ? "" : $_POST['sitelanguage']);
                                save_prefs($user);
                        }

                        define("USERTHEME", ($user_pref['sitetheme'] && file_exists(e_THEME.$user_pref['sitetheme']."/theme.php") ? $user_pref['sitetheme'] : FALSE));
                        global $ADMIN_DIRECTORY,$PLUGINS_DIRECTORY;
                        define("USERLAN", ($user_pref['sitelanguage'] && (strpos(e_SELF,$PLUGINS_DIRECTORY)!==FALSE||(strpos(e_SELF,$ADMIN_DIRECTORY)===FALSE && file_exists(e_LANGUAGEDIR.$user_pref['sitelanguage']."/lan_".e_PAGE))||(strpos(e_SELF,$ADMIN_DIRECTORY)!==FALSE && file_exists(e_LANGUAGEDIR.$user_pref['sitelanguage']."/admin/lan_".e_PAGE))) ? $user_pref['sitelanguage'] : FALSE));

                        if($user_admin){
                                define("ADMIN", TRUE); define("ADMINID", $user_id); define("ADMINNAME", $user_name); define("ADMINPERMS", $user_perms); define("ADMINEMAIL", $user_email); define("ADMINPWCHANGE", $user_pwchange);
                        } else {
                                define("ADMIN", FALSE);
                        }
                } else {
                        define("USER", FALSE); define("USERTHEME", FALSE); define("ADMIN", FALSE);
                        define("CORRUPT_COOKIE",TRUE);
                }
        }
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function ban(){
        $sql = new db;
        $ip = getip();
        $wildcard = substr($ip, 0, strrpos($ip, ".")).".*";
        if($sql -> db_Select("banlist", "*", "banlist_ip='".$_SERVER['REMOTE_ADDR']."' OR banlist_ip='".USEREMAIL."' OR banlist_ip='$ip' OR banlist_ip='$wildcard'")){
                // enter a message here if you want some text displayed to banned users ...
                exit;
        }
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function cookie($name, $value, $expire, $path="/", $domain="", $secure=0){
        setcookie($name, $value, $expire, $path, $domain, $secure);
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function html($string){
        $match_count = preg_match_all("#\[html\](.*?)\[/html\]#si", $string, $result);
        for ($a = 0; $a < $match_count; $a++){
                $after_replace = str_replace("<br />", "", $result[1][$a]);
                $string = str_replace("[html]".$result[1][$a]."[/html]", $after_replace, $string);
        }
        return $string;
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function code($string, $mode="default"){
        $search = array("<", ">", "[", "]", " ");
        $replace = array("&lt;", "&gt;", "&#091;", "&#093;", "&nbsp;");

        if($mode == "default"){
                $match_count = preg_match_all("#\[code\](.*?)\[/code\]#si", $string, $result);
                for ($a = 0; $a < $match_count; $a++){
                        $after_replace = str_replace($search, $replace, $result[1][$a]);
                        $string = str_replace("[code]".$result[1][$a]."[/code]", "[code]".$after_replace."[/code]", $string);
                }
                return $string;
        }

        $match_count = preg_match_all("#\[code\](.*?)\[/code\]#si", $string, $result);
        for ($a = 0; $a < $match_count; $a++){
                $colourtext = str_replace($search, $replace, $result[1][$a]);
                $string = str_replace("[code]".$result[1][$a]."[/code]", "<div class='indent'>".$colourtext."</div>", $string);
        }

        $string = str_replace("&lt;br&nbsp;/&gt;", "<br />", $string);

        return $string;
}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
function checkvalidtheme($theme_check){
	// arg1 = theme to check
	global $ADMIN_DIRECTORY;
    if(@fopen(e_THEME.$theme_check."/theme.php", r)){
		define("THEME", e_THEME.$theme_check."/");
	}else{
		@require_once(e_HANDLER."debug_handler.php");
		$e107tmp_theme = search_validtheme();
		define("THEME", e_THEME.$e107tmp_theme."/");
		if(ADMIN && !strstr(e_SELF, $ADMIN_DIRECTORY)){echo '<script>alert("'.CORE_LAN1.'")</script>';}
	}
}
?>