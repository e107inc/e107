<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (c) e107 Inc. 2008-2010
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms('0'))
{
    header("location:".e_BASE."index.php");
    exit;
}

$e_sub_cat = 'language';

require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."file_class.php");
require_once(e_HANDLER."language_class.php");
$ln = new language;
$fl = new e_file;
$rs = new form;

$tabs = table_list(); // array("news","content","links");

$lanlist = getLanlist();
$message = "";




if (e_QUERY) {
    $tmp = explode('.', e_QUERY);
    $action = $tmp[0];
    $sub_action = $tmp[1];
    $id = $tmp[2];
    unset($tmp);
}

if (isset($_POST['submit_prefs']) && isset($_POST['mainsitelanguage'])) {

    $pref['multilanguage']  = $_POST['multilanguage'];
    $pref['multilanguage_subdomain'] = $_POST['multilanguage_subdomain'];
    $pref['sitelanguage'] = $_POST['mainsitelanguage'];

    save_prefs();
    $ns->tablerender(LAN_SAVED, "<div style='text-align:center'>".LAN_SETSAVED."</div>");

}


// ----------------- delete tables ---------------------------------------------
if (isset($_POST['del_existing']) && $_POST['lang_choices']) {

    $lang = strtolower($_POST['lang_choices']);
    foreach ($tabs as $del_table) {
        if (db_Table_exists($lang."_".$del_table)) {
            $qry = "DROP TABLE ".$mySQLprefix."lan_".$lang."_".$del_table;
        echo $qry;
            $message .= (mysql_query($qry)) ? $_POST['lang_choices']." ".$del_table." deleted<br />" :
             $_POST['lang_choices']." $del_table couldn't be deleted<br />";
        }
    }
    global $cachevar;
    unset($cachevar['table_list']);

}

// ----------create tables -----------------------------------------------------

if (isset($_POST['kreate_tbl']) && $_POST['language']) {

    $table_to_copy = array();
    $lang_to_create = array();


    foreach ($tabs as $value) {
        $lang = strtolower($_POST['language']);
        if (isset($_POST[$value])) {
            $copdata = ($_POST['copydata_'.$value]) ? 1 : 0;
            if (copy_table($value, "lan_".$lang."_".$value, $_POST['drop'],$copdata)) {
                $message .= " ".$_POST['language']." ".$value." created<br />";
            } else {
                $message .= (!$_POST['drop'])? " ".$_POST['language']." ".$value." ".LANG_LAN_00."<br />" : $_POST['language']." ".$value." ".LANG_LAN_01."<br />";
            }
        } elseif(db_Table_exists($lang."_".$value)) {
            if ($_POST['remove']) {
                // Remove table.
                $message .= (mysql_query("DROP TABLE ".$mySQLprefix."lan_".$lang."_".$value)) ? $_POST['language']." ".$value." ".LAN_DELETED."<br />" :  $_POST['language']." $value ".LANG_LAN_02."<br />";
            } else {
                // leave table.
                $message = $_POST['language']." ".$value." was disabled but left intact.";
            }
        }
    }
    global $cachevar;
    unset($cachevar['table_list']);
}






if (varset($_POST['ziplang']) && varset($_POST['language']))
{
		$status = zip_up_lang($_POST['language']);
		if($status['error']==FALSE)
		{	
			$text = $status['message']."<br />";
			$text .= share($status['file']); 
			$ns->tablerender(LAN_CREATED, $text );
			
		}
		else
		{
			$ns->tablerender(LAN_CREATED_FAILED, $status['message']);
		}
}

    if(isset($message) && $message){
        $ns->tablerender(LAN_OK, $message);
    }

/**
 * Share Language File
 * @param object $newfile
 * Usage of e107 is granted to you provided that this function is not modified or removed in any way. 
 * @return 
 */
function share($newfile)
{
	global $pref;
	
	if(!$newfile)
	{
		return;
	}
	
	global $tp;
	$full_link = $tp->createConstants($newfile);
	
	$email_message = "<br />Site: <a href='".SITEURL."'>".SITENAME."</a>
	<br />User: ".USERNAME."\n
	<br />Email: ".USEREMAIL."\n
	<br />Language: ".$_POST['language']."\n
	<br />IP:".USERIP."
	<br />...would like to contribute the following language pack for e107. (see attached)<br />:
		
	
	<br />Missing Files: ".$_SESSION['lancheck_'.$_POST['language']]['file']."
	<br />Bom Errors : ".$_SESSION['lancheck_'.$_POST['language']]['bom']."
	<br />UTF Errors : ".$_SESSION['lancheck_'.$_POST['language']]['utf']."
	<br />Definition Errors : ".$_SESSION['lancheck_'.$_POST['language']]['def']."
	<br />Total Errors: ".$_SESSION['lancheck_'.$_POST['language']]['total'];
	
	
	
	require_once(e_HANDLER."mail.php");
	
	$send_to = (!$_POST['contribute_pack']) ? "languagepacks@e107inc.org" : "certifiedpack@e107inc.org"; 
	$to_name = "e107 Inc.";
	$Cc = "";
	$Bcc = "";
	$returnpath='';
	$returnreceipt='';
	$inline ="";
		
	$subject = (!$_POST['contribute_pack']) ? "[0.7 LanguagePack] " : "[0.7 Certified LanguagePack] ";		
	$subject .= basename($newfile);
	
	if(!@sendemail($send_to, $subject, $email_message, $to_name, '', '', $newfile, $Cc, $Bcc, $returnpath, $returnreceipt,$inline))
	{
		$text = "<div style='padding:40px'>";
		$text .= defined('LANG_LAN_EML') ?  "<b>".LANG_LAN_EML."</b>" : "<b>There was a problem sending the language-pack. Please email your verified language pack to:</b>";
		$text .= " <a href='mailto:".$send_to."?subject=".$subject."'>".$send_to."</a>";
		$text .= "</div>";
		
		return $text;	
	}
	elseif($_POST['contribute_pack'])
	{
		return "<div style='padding:40px'>Pack Sent to e107 Inc. A confirmation email will be sent to ".$pref['siteadminemail']." once it is received.<br />Please also make sure that email coming from ".$send_to." is not blocked by your spam filter.</div>";
	}

	

}

unset($text);


if (!e_QUERY || $action == 'main' && !$_POST['language'] && !$_POST['edit_existing']) {
    multilang_prefs();
}

if ($action == 'db') {
    multilang_db();
}


if($action == "tools"){
    show_tools();
}





// Grab Language configuration. ---
if ($_POST['edit_existing']) {

    $text .= "
    <form method='post' action='".e_SELF."?db' >
    <div style='text-align:center'>
    <table class='fborder' style='".ADMIN_WIDTH."'>\n";

    foreach ($tabs as $table_name) {
        $installed = strtolower($_POST['lang_choices'])."_".$table_name;
        if (stristr($_POST['lang_choices'], $installed) === FALSE) {
            $text .= "<tr>
                <td style='width:30%' class='forumheader3'>".ucfirst(str_replace("_", " ", $table_name))."</td>\n
                <td style='width:70%' class='forumheader3'>\n";
            $selected = (db_Table_exists($installed)) ? "checked='checked'" : "";
            $text .= "<input type=\"checkbox\" id='$table_name' name=\"$table_name\" value=\"1\" $selected onclick=\"if(document.getElementById('$table_name').checked){document.getElementById('datacopy_$table_name').style.display = '';} \"  />";
            $text .= "<span id='datacopy_$table_name' style='display:none'>".LANG_LAN_15."<input type=\"checkbox\" name=\"copydata_$table_name\" value=\"1\" /> </span>";
            $text .= "</td></tr>\n";
        }
    }

    $text .= "
    <tr><td class='forumheader3' colspan='2'>&nbsp;
    <input type='hidden' name='language' value='".$_POST['lang_choices']."' />
    </td></tr>";

    // ===========================================================================

    // Drop tables ?
    $text .= "<tr><td class='forumheader3'><b>".LANG_LAN_07."</b></td>
        <td class='forumheader3'>".$rs->form_checkbox("drop", 1)."\n
        <span class=\"smalltext\" >".LANG_LAN_08."</span></td></tr>\n

        <tr>
            <td class='forumheader3'><b>".LANG_LAN_10."</b></td>
            <td class='forumheader3'>".$rs->form_checkbox("remove", 1)."\n
            <span class=\"smalltext\" >".LANG_LAN_11."</span></td>
        </tr>

        <tr>
            <td colspan='2' style='width:100%; text-align: center;' class='forumheader' >";

            $button_capt = LAN_CREATE. " / ". LAN_UPDATE;
            $text .="<input type='submit' class='button' name='kreate_tbl' value=\"".$button_capt."\" />";

       $text .="</td>
        </tr>

    </table></div>\n";

    $text .= $rs->form_close();
    $ns->tablerender($_POST['lang_choices'], $text);
}

require_once(e_ADMIN."footer.php");

// ---------------------------------------------------------------------------
function multilang_prefs() {
    global $ns, $pref,$lanlist;

    $text = "<div style='text-align:center'>
        <form method='post' action='".e_SELF."' id='linkform'>
        <table style='".ADMIN_WIDTH."' class='fborder'>";


    $text .= "<tr>

        <td style='width:80%' class='forumheader3'>".LANG_LAN_14.": </td>
        <td style='width:20%; text-align:center' class='forumheader3'>";


    $text .= "
        <select name='mainsitelanguage' class='tbox'>\n";
        $sellan = preg_replace("/lan_*.php/i", "", $pref['sitelanguage']);
        foreach($lanlist as $lan){
            $sel =  ($lan == $sellan) ? "selected='selected'" : "";
            $text .= "<option value='{$lan}' {$sel}>".$lan."</option>\n";
        }

    $text .= "</select>
        </td>
        </tr>";

    $text .= "
        <tr>
        <td style='width:80%' class='forumheader3'>".LANG_LAN_12.": </td>
        <td style='width:20%;text-align:center' class='forumheader3'>";
    $checked = ($pref['multilanguage'] == 1) ? "checked='checked'" : "";
    $text .= "<input type='checkbox' name='multilanguage'   value='1' $checked />
        </td>
        </tr>
        ";

    $text .= "
    <tr>
    <td style='width:80%' class='forumheader3'>".LANG_LAN_18."<br />
    <span class='smalltext'>".LANG_LAN_19."<br />".LANG_LAN_20."</span></td>
    <td style='width:20%;text-align:center' class='forumheader3'>";
    $text .= "<textarea name='multilanguage_subdomain' rows='5' cols='15' style='width:80%'>".$pref['multilanguage_subdomain']."</textarea>
    </td>
    </tr>
    ";


    $text .= "<tr style='vertical-align:top'>
        <td colspan='2' style='text-align:center' class='forumheader'>";
    $text .= "<input class='button' type='submit' name='submit_prefs' value='".LAN_SAVE."' />";
    $text .= "</td>
        </tr>
        </table>
        </form>
        </div>";

    $caption = LANG_LAN_13; // "Language Preferences";
    $ns->tablerender($caption, $text);
}

// ----------------------------------------------------------------------------

function db_Table_exists($table)
{
    global $mySQLdefaultdb;
    $tables = getcachedvars("table_list");
    if(!$tables)
    {
        $tablist = mysql_list_tables($mySQLdefaultdb);
        while($tmp = mysql_fetch_array($tablist))
        {
            $tables[] = $tmp[0];
        }
        cachevars("table_list", $tables);
    }
    return in_array(strtolower(MPREFIX."lan_".$table), $tables);
}
// ----------------------------------------------------------------------------

function copy_table($oldtable, $newtable, $drop = FALSE, $data = FALSE)
{
    global $sql;
    $old = MPREFIX.strtolower($oldtable);
    $new = MPREFIX.strtolower($newtable);
    if($drop)
    {
        $sql->db_Select_gen("DROP TABLE IF EXISTS {$new}");
    }

    //Get $old table structure
    $sql->db_Select_gen('SET SQL_QUOTE_SHOW_CREATE = 1');
    $qry = "SHOW CREATE TABLE {$old}";
    if($sql->db_Select_gen($qry))
    {
        $row = $sql->db_Fetch();
        $qry = $row[1];
//        $qry = str_replace($old, $new, $qry);
		$qry = preg_replace("#CREATE\sTABLE\s`{0,1}".$old."`{0,1}\s#", "CREATE TABLE `{$new}` ", $qry, 1);	// More selective search
    }
    $result = mysql_query($qry);
    if(!$result)
    {
        return FALSE;
    }
    if ($data)  //We need to copy the data too
    {
        $qry = "INSERT INTO {$new} SELECT * FROM {$old}";
        $sql->db_Select_gen($qry);
    }
    return TRUE;
}

// ----------------------------------------------------------------------------

function table_list() {
    // grab default language lists.
    global $mySQLdefaultdb;

    $exclude[] = "banlist";     $exclude[] = "banner";
    $exclude[] = "cache";       $exclude[] = "core";
    $exclude[] = "online";      $exclude[] = "parser";
    $exclude[] = "plugin";      $exclude[] = "user";
    $exclude[] = "upload";      $exclude[] = "userclass_classes";
    $exclude[] = "rbinary";     $exclude[] = "session";
    $exclude[] = "tmp";         $exclude[] = "flood";
    $exclude[] = "stat_info";   $exclude[] = "stat_last";
    $exclude[] = "submit_news"; $exclude[] = "rate";
    $exclude[] = "stat_counter";$exclude[] = "user_extended";
    $exclude[] = "user_extended_struc";
    $exclude[] = "pm_messages";
    $exclude[] = "pm_blocks";

    $tables = mysql_list_tables($mySQLdefaultdb);

    while (list($temp) = mysql_fetch_array($tables))
    {
        if ((MPREFIX=='') ||(strpos($temp, MPREFIX) === 0))
        {
            $e107tab = str_replace(MPREFIX, "", $temp);
            if (!in_array($e107tab, $exclude) && stristr($e107tab, "lan_") === FALSE)
            {
                $tabs[] = $e107tab;
            }
        }
    }

    return $tabs;
}


// ------------- render form ---------------------------------------------------
function multilang_db(){
    global $pref,$ns,$tp,$rs,$lanlist,$tabs;

    if(isset($pref['multilanguage']) && $pref['multilanguage']){
        $caption = LANG_LAN_16; // language
        $text = MLAD_LAN_4."<br /><br />";


        // Choose Language to Edit:
        $text = "<div style='text-align:center'>
        <div style='".ADMIN_WIDTH.";margin-left: auto; margin-right: auto;'>
        <table class='fborder' style='width:99%; margin-top: 1px;'>
        <tr><td class='fcaption'>".ADLAN_132."</td>
        <td class='fcaption'>".LANG_LAN_03."</td>
        <td class='fcaption'>".LAN_OPTIONS."</td>
        </tr>\n\n";
        sort($lanlist);
        for($i = 0; $i < count($lanlist); $i++)
        {
            $installed = 0;

            $text .= "<tr><td class='forumheader3' style='width:30%'>".$lanlist[$i]."</td><td class='forumheader3'>\n";
            foreach ($tabs as $tab_name) {
                if (db_Table_exists(strtolower($lanlist[$i])."_".$tab_name)) {
                    $text .= $tab_name.", ";
                    $installed++;
                }
            }
            if($lanlist[$i] == $pref['sitelanguage']){
                $text .= "<div style='text-align:center'><i>".LANG_LAN_17."</i></div>";
            }else{
                $text .= (!$installed)? "<div style='text-align:center'><i>".LANG_LAN_05."</i></div>" : "";
            }
            $text .= "</td><td class='forumheader3' style='width:20%;white-space:nowrap;text-align:right'>\n";
            $text .= $rs->form_open("post", e_SELF."?modify", "lang_form_".str_replace(" ", "_", $lanlist[$i]));
            $text .= "<div style='text-align: center'>\n";
            if ($installed) {
                $text .= " <input type='submit' class='button' name='edit_existing' value='".LAN_EDIT."' />\n";
                $text .= " <input type='submit' class='button' name='del_existing' value='".LAN_DELETE."' onclick=\"return jsconfirm('Delete all tables in ".$lanlist[$i]." ?')\" />\n";
            } elseif($lanlist[$i] != $pref['sitelanguage']) {
                $text .= "<input type='submit' class='button' name='edit_existing' value='".LAN_CREATE."' />\n";
            }
            $text .= "<input type='hidden' name='lang_choices' value='".$lanlist[$i]."' />";
            $text .= "</div>";
            $text .= $rs->form_close();
            $text .= "</td></tr>";
        }

        $text .= "</table></div></div>";

        $ns->tablerender($caption, $text);
    }
}


// ----------------------------------------------------------------------------

function show_tools()
{
    global $ns;

    include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_lancheck.php");

    $text .= "
    <form id='lancheck' method='post' action='".e_ADMIN."lancheck.php'>
    <table class='fborder' style='".ADMIN_WIDTH."'>
    <tr>
    <td class='fcaption' style='width:30%'>".LAN_CHECK_1."</td>
    <td class='forumheader3'>
    <select name='language' class='tbox'>
    <option value=''>".LAN_SELECT."</option>";

    $languages = getLanList();

    foreach($languages as $lang)
    {
        if($lang != "English")
        {
        	
            $text .= "<option value='{$lang}'>{$lang}</option>\n";
        }
    }

    $text .= "
    </select>
    <input type='submit' name='language_sel' value=\"".LAN_CHECK_2."\" class='button' />
    </td></tr>
    </table></form>";
	
	  $text .= "
    <form id='tools' method='post' action='".e_SELF."?tools'>
    <table class='fborder' style='".ADMIN_WIDTH."'>
    <tr>
    <td class='fcaption' style='width:30%'>".LANG_LAN_23."</td>
    <td class='forumheader3' >
    <select name='language' class='tbox'>
    <option value=''>".LAN_SELECT."</option>";

  

    foreach($languages as $lang)
    {
        if($lang != "English")
        {
        	$sel = ($_POST['language']==$lang) ? "selected='selected'" : "";
            $text .= "<option value='{$lang}' {$sel}>{$lang}</option>\n";
        }
    }

    $text .= "
    </select>
    <input type='submit' name='ziplang' value=\"".LANG_LAN_24."\" class='button' />";
	
	$srch = array("[","]");
	$repl = array("<a rel='external' href='http://e107.org/news.php?extend.876.1'>","</a>");
	$diz = (defined("LANG_LAN_28")) ? LANG_LAN_28 : "Check this box if you're an [e107 certified translator].";
	
	$text .= " <input type='checkbox' name='contribute_pack' value='1' />".str_replace($srch,$repl,$diz);
	
	$text .= "
    </td></tr>
    </table></form>";
	
	$text .= "<div class='smalltext' style='padding-top:50px;text-align:center'>".LANG_LAN_AGR."</div>";

    $ns->tablerender(LANG_LAN_21, $text);
}

function find_locale($language)
{
	$code = file_get_contents(e_LANGUAGEDIR.$language."/".$language.".php");
	$tmp = explode("\n",$code);
	
	$srch = array("define","'",'"',"(",")",";","CORE_LC2","CORE_LC",",");
		
	foreach($tmp as $line)
	{
		if(strpos($line,"CORE_LC") !== FALSE && (strpos($line,"CORE_LC2") === FALSE))
		{
			$lc = trim(str_replace($srch,"",$line));
		}
		elseif(strpos($line,"CORE_LC2") !== FALSE)
		{
			$lc2 = trim(str_replace($srch,"",$line));
		}		
			
	}
	
	if(!isset($lc) || !isset($lc2) || $lc=="" || $lc2=="")
	{
		return FALSE;	
	}
		
	 return substr($lc,0,2)."_".strtoupper(substr($lc2,0,2)); 
	// 
}
// ----------------------------------------------------------------------------

function zip_up_lang($language)
{
	global $tp;
	$ret = array();
	$ret['file'] = "";
	
	if(!isset($_SESSION['lancheck_'.$language]))
	{
		$ret = array();
		$ret['error'] = TRUE;
		$ret['message'] = (defined('LANG_LAN_27')) ? LANG_LAN_27 : "Please verify your language files ('Verify') then try again.";
		return $ret;	
	}
	
		
	if(!is_writable(e_FILE."public"))
	{
		$ret['error'] = TRUE;
		$ret['message'] = LAN_UPLOAD_777 . " ".e_FILE."public";
		return $ret;		
	}
	
	if (is_readable(e_ADMIN."ver.php"))
	{
		include (e_ADMIN."ver.php");
	}
	
	 $core_plugins = array(
	"alt_auth","banner_menu","blogcalendar_menu","calendar_menu","chatbox_menu",
	"clock_menu","comment_menu","compliance_menu","content","counter_menu",
	"featurebox","forum","gsitemap","integrity_check","lastseen","links_page",
	"linkwords","list_new","log","login_menu","newforumposts_main","newsfeed",
	"newsletter","online_extended_menu","online_menu","other_news_menu","pdf",
	"pm","poll","powered_by_menu","rss_menu","search_menu","sitebutton_menu",
	"trackback","tree_menu","userlanguage_menu","usertheme_menu"
	);
	 
	 $core_themes = array("crahan","e107v4a","human_condition","interfectus","jayya",
	 "khatru","kubrick","lamb","leaf","newsroom","reline","sebes","vekna_blue");

	require_once (e_HANDLER.'pclzip.lib.php');
	list($ver, $tmp) = explode(" ", $e107info['e107_version']);
	if(!$locale =  find_locale($language))
	{
		$ret['error'] = TRUE;
		$file = "e107_languages/{$language}/{$language}.php";
		$def = (defined('LANG_LAN_25')) ? LANG_LAN_25 : "Please check that CORE_LC and CORE_LC2 have values in [lcpath] and try again.";
		$ret['message'] = str_replace("[lcpath]",$file,$def); // 
		return $ret;	
	};
		
	global $THEMES_DIRECTORY, $PLUGINS_DIRECTORY, $LANGUAGES_DIRECTORY, $HANDLERS_DIRECTORY, $HELP_DIRECTORY;
		
	if(($HANDLERS_DIRECTORY != "e107_handlers/") || ( $LANGUAGES_DIRECTORY != "e107_languages/") || ($THEMES_DIRECTORY != "e107_themes/") || ($HELP_DIRECTORY != "e107_docs/help/") || ($PLUGINS_DIRECTORY != "e107_plugins/"))
	{
		$ret['error'] = TRUE;
		$ret['message'] = (defined('LANG_LAN_26')) ? LANG_LAN_26 : "Please make sure you are using default folder names in e107_config.php (eg. e107_languages/, e107_plugins/ etc.) and try again.";
		return $ret;	
	}	
		
	$newfile = e_FILE."public/e107_".$ver."_".$language."_".$locale."-utf8.zip";
	
	$archive = new PclZip($newfile);
	$core = grab_lans(e_LANGUAGEDIR.$language."/", $language,'',0);
	$core_admin = grab_lans(e_BASE.$LANGUAGES_DIRECTORY.$language."/admin/", $language,'',2);
	$plugs = grab_lans(e_BASE.$PLUGINS_DIRECTORY, $language, $core_plugins); // standardized path. 
	$theme  = grab_lans(e_BASE.$THEMES_DIRECTORY, $language, $core_themes);
	$docs = grab_lans(e_BASE.$HELP_DIRECTORY,$language);
	$handlers = grab_lans(e_BASE.$HANDLERS_DIRECTORY,$language); // standardized path. 
		
	$file = array_merge($core,$core_admin, $plugs, $theme, $docs, $handlers);
	$data = implode(",", $file);
				
	if ($archive->create($data) == 0)
	{		
		$ret['error'] = TRUE;
		$ret['message'] = $archive->errorInfo(true);
		return $ret;
	}
	else
	{
	
		$ret['file']  = $newfile; 
		$ret['message'] = str_replace("../", "", e_FILE.'public/')."<a href='".$newfile."' >".basename($newfile)."</a>"; 
		$ret['error'] = FALSE;
		return $ret;
	}
}

function getLanList()
{
	global $ln;
	
	$lst = explode(",",e_LANLIST);
	$valid_langs = $ln->list;
	$list = array();
	
	foreach($lst as $lang)
	{
		if(in_array($lang,$valid_langs))
		{
			$list[] = $lang;
		}
	}
	
	sort($list);
	return $list;	
}

function grab_lans($path, $language, $filter = "",$depth=5)
{
	global $fl,$ln;
	
	if ($lanlist = $fl->get_files($path, "^[\w]+(\.php|\.js){0,1}$", "standard", $depth))
	{
		sort($lanlist);
	}
	else
	{
		return array();
	}
	
	
	$pzip = array();
	
	$isocode = $ln->convert($language);
	

	
	foreach ($lanlist as $p)
	{	
		$fullpath = $p['path'].$p['fname'];
						
		if($p['fname'] == ($language."_custom.php") || ($p['fname'] == ($language."_config.php")))
		{
			continue;
		}
		
		$phpmailer = "phpmailer.lang-".$isocode.".php";
		$tinyMce1 = "/langs/".$isocode.".js";
		$tinyMce2 = "/langs/".$isocode."_dlg.js";
		
		
		if (strpos($fullpath, $language) !== FALSE || strpos($fullpath,$phpmailer)!==FALSE || strpos($fullpath,$tinyMce1)!==FALSE || strpos($fullpath,$tinyMce2)!==FALSE)
		{
			if(is_array($filter))
			{
				$dir =  basename(dirname($p['path']));
				foreach($filter as $val)
				{
					if(strpos($fullpath,$val)!==FALSE)
					{
						$pzip[] = $fullpath;	
					}
				}
		
			}
			else
			{
				$pzip[] = $fullpath;	
			}
			
		}
	}
	
	return $pzip;
}



// --------------------------------------------------------------------------


function language_adminmenu() {
    global $action,$pref;
    if ($action == "") {
        $action = "main";
    }

    if($action == "modify"){
        $action = "db";
    }
    $var['main']['text'] = LAN_PREFS;
    $var['main']['link'] = e_SELF;

    if(isset($pref['multilanguage']) && $pref['multilanguage']){
        $var['db']['text'] = LANG_LAN_03;
        $var['db']['link'] = e_SELF."?db";
    }

    $lcnt = explode(",",e_LANLIST);
    if(count($lcnt) > 1)
    {
        $var['tools']['text'] = ADLAN_CL_6;
        $var['tools']['link'] = e_SELF."?tools";
    }

    show_admin_menu(ADLAN_132, $action, $var);
}

?>
