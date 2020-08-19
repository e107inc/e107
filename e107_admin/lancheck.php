<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Language check
 * With code from Izydor and Lolo.
 *
*/
if (!defined('e107_INIT'))
{
	require_once("../class2.php");
}

e107::coreLan('lancheck', true);

$e_sub_cat = 'language';
// require_once("auth.php");

$frm = e107::getForm();
$mes = e107::getMessage();
// $lck = new lancheck;


/*
$qry = explode("|",e_QUERY);
$f = $qry[0];
$lan = $qry[1];
$mode = $qry[2];

// Write the language file.
if(isset($_POST['submit']))
{
	unset($input);
	$kom_start = chr(47)."*";
	$kom_end = "*".chr(47);

	if($_POST['root'])
	{
		$writeit = $_POST['root'];
	}

	$old_kom = "";
	$in_kom=0;
	$data = file($writeit);
	foreach($data as $line)
	{

		if (strpos($line,$kom_start) !== False && $old_kom == "")
		{
			$in_kom=1;
		}
		if ($in_kom) { $old_kom.=$line; }
		if (strpos($line,$kom_end) !== False && $in_kom) {$in_kom = 0;}
	}


	$message = "<div style='text-align:left'><br />";
	$input .= chr(60)."?php\n";
	if ($old_kom == "")
	{
		// create CVS compatible description.
		$diz = chr(47)."*\n";
		$diz .= " * e107 website system\n";
		$diz .= " *\n";
		$diz .= " * Copyright (C) 2008-2009 e107 Inc (e107.org)\n";
		$diz .= " * Released under the terms and conditions of the\n";
		$diz .= " * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)\n";
		$diz .= " *\n";
		$diz .= " * Language File\n";
		$diz .= " *\n";
		$diz .= " * ".chr(36)."Source: ".str_replace(array(e_LANGUAGEDIR, e_PLUGIN), array(e_LANGUAGEDIR_ABS, e_PLUGIN_ABS), $writeit)." ".chr(36)."\n";
		$diz .= " * ".chr(36)."Revision: 1.0 ".chr(36)."\n";
		$diz .= " * ".chr(36)."Date: ".date("Y/m/d H:i:s")." ".chr(36)."\n";
		$diz .= " *  ".chr(36)."Author: ".USERNAME." ".chr(36)."\n";
		$diz .= " *\n";
		$diz .= "*".chr(47)."\n\n";
	}
	else
	{
		$diz = $old_kom;
	}

	$input .= $diz;
	$message .= str_replace("\n","<br />",$diz);

	for ($i=0; $i<count($_POST['newlang']); $i++)
	{
		$notdef_start = "";
		$notdef_end = "\n";
		$deflang = (MAGIC_QUOTES_GPC === TRUE) ? stripslashes($_POST['newlang'][$i]) : $_POST['newlang'][$i];
		$func = "define";
		$quote = chr(34);

		if (strpos($_POST['newdef'][$i],"ndef++") !== FALSE )
		{
			$defvar = str_replace("ndef++","",$_POST['newdef'][$i]);
			$notdef_start = "if (!defined(".chr(34).$defvar.chr(34).")) {";
			$notdef_end = "}\n";
		}
		else
		{
			$defvar = $_POST['newdef'][$i];
		}

		if($_POST['newdef'][$i] == "LC_ALL" && isset($_POST['root']))
		{
			$message .= $notdef_start.'setlocale('.htmlentities($defvar).','.$deflang.');<br />'.$notdef_end;
			$input .= $notdef_start."setlocale(".$defvar.",".$deflang.");".$notdef_end;
		}
		else
		{
			$message .= $notdef_start.$func.'('.$quote.htmlentities($defvar).$quote.',"'.$deflang.'");<br />'.$notdef_end;
			$input .= $notdef_start.$func."(".$quote.$defvar.$quote.", ".chr(34).$deflang.chr(34).");".$notdef_end;
		}
	}

	$message .="<br />";
	$message .="</div>";
	$input .= "\n\n?>";

	// Write to file.
	$fp = @fopen($writeit,"w");
	if(!@fwrite($fp, $input))
	{
		$caption = LAN_CHECK_PAGE_TITLE.' - '.LAN_ERROR;
		$message = '';
		$mes->addError(LAN_CHECK_17);
	}
	else
	{
		$caption = LAN_CHECK_PAGE_TITLE.' - '.LAN_SUMMARY;
		$mes->addSuccess(sprintXXX(str_replace("[x]", "%s", LAN_CHECK_23), basename($writeit)));
	}
	fclose($writeit);

	$message .= "
	<form method='post' action='".e_SELF."' id='core-lancheck-save-file-form'>
	<div class='center'>
		".$frm->admin_button('language_sel', LAN_BACK)."
		".$frm->hidden('language', $lan)."
	</div>
	</form>";
	


	$ns->tablerender($caption, $mes->render().$message);
//	require_once(e_ADMIN."footer.php");
	exit;
}

// ============================================================================

// Edit the Language File.

if($f != ""){

	if (!$mode)
	{
		$dir1 =  e_BASE.$LANGUAGES_DIRECTORY."English/";
		$f1=$f;
		$dir2 =  e_BASE.$LANGUAGES_DIRECTORY.$lan."/";
		$f2=$f;
	}
	else
	{
		$fullpath_orig = $f;
		$fullpath_trans = str_replace("English",$lan,$f);

		$f1 = basename($fullpath_orig);
		$f2 = basename($fullpath_trans);
		$dir1 = dirname($fullpath_orig)."/";
		$dir2 = dirname($fullpath_trans)."/";
	}

	$lck->edit_lanfiles($dir1,$dir2,$f1,$f2);

}

// ===========================================================================

$core_plugins = array(
	"alt_auth", "banner_menu", "blogcalendar_menu", "calendar_menu", "chatbox_menu",
	"clock_menu", "comment_menu", "content", 'download', "featurebox", "forum",
	"gsitemap", "links_page", "linkwords", "list_new", "log", "login_menu",
	"newforumposts_main", "newsfeed", "newsletter", "online", "other_news_menu",
	"pdf", "pm", "poll", "rss_menu", "search_menu", "siteinfo", "trackback",
	"tree_menu", "user_menu"
);

$core_themes = array("bootstrap", $pref['sitetheme']);


if(isset($_POST['language_sel']) && isset($_POST['language']))
{

	$text = $lck->check_core_lanfiles($_POST['language']).$lck->check_core_lanfiles($_POST['language'],"admin/");

	$text .= "
		<fieldset id='core-lancheck-plugin'>
			<legend>".ADLAN_CL_7."</legend>
			<table class='table adminlist'>
				<colgroup>
					<col style='width: 25%' />
					<col style='width: 25%' />
					<col style='width: 40%' />
					<col style='width: 10%' />
				</colgroup>
				<thead>
					<tr>
						<th>".LAN_PLUGIN."</th>
						<th>".LAN_CHECK_16."</th>
						<th>".$_POST['language']."</th>
						<th class='center last'>".LAN_OPTIONS."</th>
					</tr>
				</thead>
				<tbody>
	";

	foreach($core_plugins as $plugs)
	{
		if(is_readable(e_PLUGIN.$plugs))
		{
			$text .= $lck->check_lanfiles('P',$plugs,"English",$_POST['language']);
		}
	}
	$text .= "
				</tbody>
			</table>
		</fieldset>
	";

	$text .= "
		<fieldset id='core-lancheck-theme'>
			<legend>".LAN_THEME."</legend>
			<table class='table adminlist'>
				<colgroup>
					<col style='width: 25%' />
					<col style='width: 25%' />
					<col style='width: 40%' />
					<col style='width: 10%' />
				</colgroup>
				<thead>
					<tr>
						<th>".LAN_CHECK_21."</th>
						<th>".LAN_CHECK_16."</th>
						<th>".$_POST['language']."</th>
						<th class='center last'>".LAN_OPTIONS."</th>
					</tr>
				</thead>
				<tbody>
	";
	foreach($core_themes as $them)
	{
		if(is_readable(e_THEME.$them))
		{
			$text .= $lck->check_lanfiles('T',$them,"English",$_POST['language']);
		}
	}
	$text .= "
				</tbody>
			</table>
		</fieldset>
	";
	
	$mes = e107::getMessage();
	if($lck->error_count == 0)
	{
		e107::getConfig()->setPref('lancheck/'.$_POST['language'],1);
		e107::getConfig()->save(FALSE);
		$mes->addSuccess(LAN_CHECK_27.'<b>: '.$lck->error_count.'</b>');		
	}
	else  
	{
		$mes->addWarning(LAN_CHECK_27.'<b>: '.$lck->error_count.'</b>');
	}
	

	$ns->tablerender(LAN_THEMES, $mes->render(). $text);


	
	require_once(e_ADMIN."footer.php");
	exit;
}


*/

class lancheck
{

	public $core_plugins = array();

	public $core_themes = array("bootstrap3", "voux");
			
	private $errorsOnly = false;
	
	private $coreImage = array();

	private $transLanguage = '';

	private $thirdPartyPlugins = true;

	private $deprecatedFiles = array('lan_download.php', 'lan_parser_functions.php', 'lan_prefs.php', 'admin/lan_download.php', 'admin/lan_modcomment.php');

	private $installed_languages = array();

	function __construct()
	{
		$this->core_plugins = e107::getPlugin()->getCorePlugins();
	}

	public function thirdPartyPlugins($val)
	{
		$this->thirdPartyPlugins = $val;
	}

	public function errorsOnly($val)
	{
		$this->errorsOnly = $val;

	}
	
	public function init()
	{

		$mode   = $_GET['sub'];
		$lan    = $_GET['lan'];
		$file   = $_GET['file'];

		$this->transLanguage = $lan;


		$ns = e107::getRender();
		$tp = e107::getParser();
		$pref = e107::getPref();
		
		// Check current theme also (but do NOT add to generated zip)

		if(deftrue('e_DEBUG'))
		{
			$this->core_themes[] = $pref['sitetheme'];
			$this->core_themes = array_unique($this->core_themes);
		}/*
		if(E107_DEBUG_LEVEL > 0)
		{
			print_a($this->core_plugins);
		}*/


		$acceptedLans = explode(",",e_LANLIST);
	

		if(!empty($_POST['ziplang']))
		{
			$lang = key($_POST['ziplang']);
			return $this->zipLang($lang);
		}


		// Verify
		if($mode == 'verify' && !empty($lan))
		{
			// $_SESSION['lancheck-errors-only'] 	= ($_POST['errorsonly']==1 ) ?  1 : 0;
			// $this->errorsOnly 					= ($_POST['errorsonly']==1) ?  TRUE : FALSE;
			return $this->check_all('render', $lan);

		}
		
		// Write the language file.
		if(isset($_POST['saveLanguageFile']) && vartrue($_POST['lan']) && in_array($_POST['lan'],$acceptedLans))
		{
			
			$this->write_lanfile($_POST['lan']);	
			return true;
		} 
		
		// Edit the Language File.
		if($mode == 'edit' && vartrue($file) && !empty($lan) && in_array($lan, $acceptedLans))
		{
			
			if (empty($_GET['type']))
			{
				$dir1 =  e_LANGUAGEDIR."English/";
				$f1= $tp->toDB($file);
				$dir2 =  e_LANGUAGEDIR.$lan."/";
				$f2= $tp->toDB($file);
			}
			else
			{
				$fullpath_orig = $tp->toDB($file);
				$fullpath_trans = str_replace("English", $lan, $tp->toDB($file));
		
				$f1 = basename($fullpath_orig);
				$f2 = basename($fullpath_trans);
				$dir1 = dirname($fullpath_orig)."/";
				$dir2 = dirname($fullpath_trans)."/";
			}
			
			return $this->edit_lanfiles($dir1,$dir2,$f1,$f2,$lan);
			// return true;
		}	
		
		return FALSE;
	}
	
	
	function countFiles($array)
	{
		foreach($array as $k=>$val)
		{
			if(is_array($val))
			{
				$key = key($val);
				$this->coreImage[$key] = $val;
			}
		/*	elseif($val)
			{
				$this->totalFiles++;
			}	
			*/
		}	
	}


	/**
	 * @param $language
	 * @return array|string
	 */
	function zipLang($language)
	{
		$mes = e107::getMessage();

		/* NO LONGER USED

		$certVal = isset($_POST['contribute_pack']) ? 1 : 0;

		if(!varset($_COOKIE['e107_certified']))
		{
			cookie('e107_certified',$certVal,(time() + 3600 * 24 * 30));
		}
		else
		{
			$_COOKIE['e107_certified'] = $certVal;
		}*/

		//	$_POST['language'] = key($_POST['ziplang']);

		// If no session data, scan before zipping.
		if(!isset($_SESSION['lancheck'][$language]['total']) || $_SESSION['lancheck'][$language]['total']!='0')
		{
			$this->check_all('norender', $language);
		}

		$status = $this->makeLanguagePack($language);
		//print_a($status);

		if($status['error'] == FALSE)
		{
			$srch = array('[', ']');
			$rpl = array("<a href='https://github.com/e107translations/Translator-Information' target='_blank'>", "</a>");

			$text = str_replace($srch, $rpl, LANG_LAN_154);
			$text .= "<br />"; 
			$text .= $status['message'];
			//$text .= $this->share($status['file']); // no longer notify by email, but only offer to download 
			$mes->addSuccess($text); 
		}
		else
		{
			$mes->addError($status['error']);
		}

		return array('text'=> $mes->render(), 'caption'=>'');
	}


	/**
	 * Share Language File
	 *
	 * DEPRECATED - NO LONGER USED AS TRANSLATIONS ARE NOW MANAGED THROUGH GITHUB REPOSITORIES (https://github.com/e107translations)
	 * 
	 * @param object $newfile
	 * Usage of e107 is granted to you provided that this function is not modified or removed in any way.
	 * @return
	 */
	private function share($newfile)
	{
		global $pref;

		if(!$newfile || E107_DEBUG_LEVEL > 0)
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


	<br />Missing Files: ".$_SESSION['lancheck'][$_POST['language']]['file']."
	<br />Bom Errors : ".$_SESSION['lancheck'][$_POST['language']]['bom']."
	<br />UTF Errors : ".$_SESSION['lancheck'][$_POST['language']]['utf']."
	<br />Definition Errors : ".$_SESSION['lancheck'][$_POST['language']]['def']."
	<br />Total Errors: ".$_SESSION['lancheck'][$_POST['language']]['total']."
	<br />
	<br />XML file: ".$_SESSION['lancheck'][$_POST['language']]['xml'];



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


	/**
	 * @param $language
	 * @return bool|string
	 */
	private function findLocale($language)
	{
		if(!is_readable(e_LANGUAGEDIR.$language."/".$language.".php"))
		{
			return FALSE;
		}

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





	/**
	 * @param $language
	 * @return array
	 */
	private function makeLanguagePack($language)
	{

		$tp = e107::getParser();

		$ret = array();
		$ret['file'] = "";

		if($_SESSION['lancheck'][$language]['total'] > 0 && !E107_DEBUG_LEVEL)
		{
			$ret = array();
			$ret['error'] = TRUE;
			$message = LANG_LAN_115;
			$ret['message'] = str_replace("[x]",$_SESSION['lancheck'][$language]['total'],$message);
			return $ret;
		}

		if(!isset($_SESSION['lancheck'][$language]))
		{
			$ret = array();
			$ret['error'] = TRUE;
			$ret['message'] = LANG_LAN_116;
			return $ret;
		}

		if(varset($_POST['contribute_pack']) && varset($_SESSION['lancheck'][$language]['total']) !='0')
		{
			$ret['error'] = TRUE;
			$ret['message'] = LANG_LAN_117;
			$ret['message']	 .= "<br />";
			$ret['message']	 .= LANG_LAN_116;
			return $ret;
		}


		require_once(e_HANDLER.'pclzip.lib.php');
		list($ver, $tmp) = explode(" ", e_VERSION);
		if(!$locale = $this->findLocale($language))
		{
			$ret['error'] = TRUE;
			$file = "e107_languages/{$language}/{$language}.php";
			$def = (defined('LANG_LAN_25')) ? LANG_LAN_25 : LANG_LAN_119;
			$ret['message'] = str_replace("[x]",$file,$def); //
			return $ret;
		}


		global $THEMES_DIRECTORY, $PLUGINS_DIRECTORY, $LANGUAGES_DIRECTORY, $HANDLERS_DIRECTORY, $HELP_DIRECTORY;

		if(($HANDLERS_DIRECTORY != "e107_handlers/") || ( $LANGUAGES_DIRECTORY != "e107_languages/") || ($THEMES_DIRECTORY != "e107_themes/") || ($HELP_DIRECTORY != "e107_docs/help/") || ($PLUGINS_DIRECTORY != "e107_plugins/"))
		{
			$ret['error'] = TRUE;
			$ret['message'] = (defined('LANG_LAN_26')) ? LANG_LAN_26 : LANG_LAN_120;
			return $ret;
		}

		$newfile = e_MEDIA_FILE."e107_".$ver."_".$language."_".$locale."-utf8.zip";

		$archive = new PclZip($newfile);

		$file = $this->getFileList($language);

		$data = implode(",", $file);

		if ($archive->create($data,PCLZIP_OPT_REMOVE_PATH,e_BASE) == 0)
		{
			$ret['error'] = TRUE;
			$ret['message'] = $archive->errorInfo(true);
			return $ret;
		}
		else
		{

			$fileName = e_FILE."public/".$language.".xml";
			if(is_readable($fileName))
			{
				@unlink($fileName);
			}

			$fileData = '<?xml version="1.0" encoding="utf-8"?>
<e107Language name="'.$language.'" compatibility="'.$ver.'" date="'.date("Y-m-d").'" >
<author name ="'.USERNAME.'" email="'.USEREMAIL.'" url="'.SITEURL.'" />
</e107Language>';

			if(file_put_contents($fileName,$fileData))
			{
				$addTag = $archive->add($fileName, PCLZIP_OPT_ADD_PATH, 'e107_languages/'.$language, PCLZIP_OPT_REMOVE_PATH, e_FILE.'public/');
				$_SESSION['lancheck'][$language]['xml'] = "Yes";
			}
			else
			{
				$_SESSION['lancheck'][$language]['xml'] = "No";
			}

			@unlink($fileName);



			$ret['file']  = $newfile;
			$ret['message'] = str_replace("../", "", e_MEDIA_FILE)."<a href='".$newfile."' >".basename($newfile)."</a>";
			$ret['error'] = FALSE;
			return $ret;
		}
	}


	private function getFileList($language)
	{
		if(empty($language))
		{
			return false;
		}

		$PLUGINS_DIRECTORY      = e107::getFolder('plugins');
		$THEMES_DIRECTORY       = e107::getFolder('themes');
		$HELP_DIRECTORY         = e107::getFolder('help');
		$HANDLERS_DIRECTORY     = e107::getFolder('handlers');

		$core       = $this->getFilePaths(e_LANGUAGEDIR.$language."/", $language,''); // includes admin area.
	//	$core_admin = $this->getFilePaths(e_BASE.$LANGUAGES_DIRECTORY.$language."/admin/", $language,'');
		$core_admin = array();
		$plugs      = $this->getFilePaths(e_BASE.$PLUGINS_DIRECTORY, $language, $this->core_plugins); // standardized path.
		$theme      = $this->getFilePaths(e_BASE.$THEMES_DIRECTORY, $language, $this->core_themes);
		$docs       = $this->getFilePaths(e_BASE.$HELP_DIRECTORY,$language);
		$handlers   = $this->getFilePaths(e_BASE.$HANDLERS_DIRECTORY,$language); // standardized path.

		$file = array_merge($core,$core_admin, $plugs, $theme, $docs, $handlers);

		$file = array_unique($file);

		return $file;


	}

	/** todo */
	/*function removeLanguagePack($language)
	{
		$files = $this->getFileList($language);



	}*/



	/**
	 * @param $path
	 * @param $language
	 * @param string $filter
	 * @return array|bool
	 */
	public function getFilePaths($path, $language, $restrict=array())
	{
		$fl = e107::getFile();

		if ($lanlist = $fl->get_files($path, "", "standard", 4)) // (\.php|\.xml)$
		{
			sort($lanlist);
		}
		else
		{
			return array();
		}



		$pzip = array();
		foreach ($lanlist as $p)
		{
			$fullpath = $p['path'].$p['fname'];

			if (strpos($fullpath, $language) !== false)
			{
				$pzip[] = $fullpath;
			}
		}


		if(!empty($restrict)) // strip the list according to inclusion list.
		{
			$newlist = array();
			foreach($pzip as $k=>$p)
			{
				foreach($restrict as $accept)
				{
					if(strpos($p, '/'.$accept.'/')!==false)
					{

						$newlist[] = $p;
					}

				}

			}

			$pzip = $newlist;
		}



		return $pzip;
	}

	/**
	 * Get Installed Language-Pack Meta Data.
	 * @return array
	 */
	function getLocalLanguagePacks()
	{
		$this->installed_languages = e107::getLanguage()->installed();

		$xml = e107::getXml();

		$arr = array();

		foreach($this->installed_languages as $language)
		{
			if($language == "English")
			{
				continue;
			}

			$metaFile = e_LANGUAGEDIR.$language."/".$language.".xml";

			if(is_readable($metaFile))
			{
				$rawData = $xml->loadXMLfile($metaFile,true);

				if($rawData)
				{
					$value = $rawData['@attributes'];
				}
				else
				{
					$value = array(
						'date' 			=> "&nbsp;",
						'compatibility' => '&nbsp;'
					);
				}
			}
			else
			{
				$value = array(
					'date' 			=> "&nbsp;",
					'compatibility' => '&nbsp;'
				);
			}

			$value['type'] = 'local';

			$arr[$language] = $value;
		}

		return $arr;
	}




	/**
	 * Get Online Language-Pack Meta Data.
	 * @return array|bool
	 */
	public function getOnlineLanguagePacks()
	{
		$xml = e107::getXml();

		$feed = 'https://e107.org/languagepacks.xml';

		$version = e_VERSION;

		if(!empty($version))
		{
			list($ver,$tmp) = explode("-", $version);
			$feed .= "?ver=". preg_replace('/[^\d\.]/','', $ver);
		}

		e107::getDebug()->log("Language Pack Feed: ".$feed);

		$languages = array();

		if($rawData = $xml -> loadXMLfile($feed, true))
		{

			if(empty($rawData['language']))
			{
				return false;
			}

			foreach($rawData['language'] as $key => $att)
			{
				// issue #3059 in case array @attributes is in $att
				if (is_int($key) && is_array($att) && array_key_exists('@attributes', $att))
				{
					$att = $att['@attributes'];
				}
				// issue #3059 Language list didn't load
				elseif ($key != '@attributes')
				{
					continue;
				}

				$id = $att['name'];

				// fix github double url bug...
				if (stripos($att['url'], 'https://github.comhttps://github.com') !== false)
				{
					$att['url'] = str_ireplace('https://github.comhttps://github.com', 'https://github.com', $att['url']);
				}
				if (stripos($att['infourl'], 'https://github.comhttps://github.com') !== false)
				{
					$att['infourl'] = str_ireplace('https://github.comhttps://github.com', 'https://github.com', $att['infourl']);
				}

				$languages[$id] = array(
					'name'          => $att['name'],
					'author'        => $att['author'],
					'infoURL'       => $att['infourl'],
					'tag'           => $att['tag'],
				//	'folder'        => $att['folder'],
					'version'       => $att['version'],
					'date'          => $att['date'],
					'compatibility' => $att['compatibility'],
					'url'           => $att['url'],
					'type'          => 'online'

				);
			}


		}
		else
		{
			e107::getDebug()->log("Language Pack Feed Failed: ".$xml->getLastErrorMessage());
		}


		return $languages;
	}





	function check_all($mode='render', $lan=null)
	{
		// global $ns,$tp;
		$mes = e107::getMessage();
		$tp = e107::getParser();

		if(empty($lan))
		{
			echo "debug: ".__METHOD__." missing \$lan";
			return false;
		}
			
	//	$lan = key($_POST['language_sel']);

		$_SESSION['lancheck'][$lan] = array();
		$_SESSION['lancheck'][$lan]['file']	= 0;
		$_SESSION['lancheck'][$lan]['def']	= 0;
		$_SESSION['lancheck'][$lan]['bom']	= 0;
		$_SESSION['lancheck'][$lan]['utf']	= 0;
		$_SESSION['lancheck'][$lan]['total']	= 0;
	
	
		$core_text 	= $this->check_core_lanfiles($lan);
		$core_admin = $this->check_core_lanfiles($lan,"admin/");
		$plug_text = "";
		$theme_text = "";
	
	
		// Plugins -------------
		$plug_header = "<table class='table table-striped'>
		<tr>
		<td class='fcaption'>".LAN_PLUGIN."</td>
		<td class='fcaption'>".LAN_CHECK_16."</td>
		<td class='fcaption'>".$lan."</td>
		<td class='fcaption'>".LAN_OPTIONS."</td></tr>";
	
		foreach($this->core_plugins as $plugs)
		{
			if(is_readable(e_PLUGIN.$plugs))
			{
				$plug_text .= $this->check_lanfiles('P',$plugs,"English",$lan);
			}
		}
		
		$plug_footer = "</table>";
	
		// Themes  -------------
		$theme_header = "<table class='table table-striped'>
		<tr>
		<td class='fcaption'>".LAN_THEME."</td>
		<td class='fcaption'>".LAN_CHECK_16."</td>
		<td class='fcaption'>".$lan."</td>
		<td class='fcaption'>".LAN_OPTIONS."</td></tr>";
		foreach($this->core_themes as $them)
		{
			if(is_readable(e_THEME.$them))
			{
				$theme_text .= $this->check_lanfiles('T',$them,"English",$lan);
			}
		}
		$theme_footer = "</table>";
		
		// -------------------------
		

		
		
		if($mode != 'render')
		{
			 return null;
		}
	
		$message = "
		<form id='lancheck' method='post' action='".e_ADMIN."language.php?mode=main&action=tools'>
		<div>\n";
		
	//	$icon = ($_SESSION['lancheck'][$lan]['total']>0) ? ADMIN_FALSE_ICON : ADMIN_TRUE_ICON;
		
		
		$errors_diz = (deftrue('LAN_CHECK_23')) ? LAN_CHECK_23 : "Errors Found";

		$message .= $errors_diz.": ".$_SESSION['lancheck'][$lan]['total'];
	
		$just_go_diz = (deftrue('LAN_CHECK_20')) ? LAN_CHECK_20 : "Generate Language Pack";
		$lang_sel_diz = (deftrue('LAN_CHECK_21')) ? LAN_CHECK_21 : "Verify Again";
		$lan_pleasewait = (deftrue('LAN_PLEASEWAIT')) ?  $tp->toJS(LAN_PLEASEWAIT) : "Please Wait";
		
		$message .= "
		<br /><br />
		<input type='hidden' name='language' value='".$lan."' />
		<input type='hidden' name='errorsonly' value='".$_SESSION['lancheck-errors-only']."' />    
	    <input class='btn btn-primary' type='submit' name='ziplang[".$lan."]' value=\"".$just_go_diz."\"  onclick=\"this.value = '".$lan_pleasewait."'\" />
	    <a href='".e_REQUEST_URI."' class='btn btn-default'>".$lang_sel_diz."</a>
		</div>
	    </form>
		";
		
//	print_a($_SESSION['lancheck'][$lan]);

		$plug_text = ($plug_text) ? $plug_header.$plug_text.$plug_footer : "<div class='alert alert-success'>".LAN_OK."</div>";
		$theme_text = ($theme_text) ? $theme_header.$theme_text.$theme_footer : "<div class='alert alert-success'>".LAN_OK."</div>";

		$mesStatus = ($_SESSION['lancheck'][$lan]['total']>0) ? E_MESSAGE_INFO : E_MESSAGE_SUCCESS;
			
		$mes->add($message, $mesStatus);	
			
	//	$ns -> tablerender(LAN_SUMMARY.": ".$lan,$message);



		$ret = array();
		$ret['text'] = $mes->render();

		$tabs = array(
			'core'   => array('caption'=> LAN_CHECK_26, 'text'=>$core_text),
			'admin'  => array('caption'=> LAN_ADMIN,    'text'=>$core_admin),
			'plugin' => array('caption'=> ADLAN_CL_7,   'text'=>$plug_text),
			'theme'  => array('caption'=> LAN_THEMES,   'text'=>$theme_text),
		);

		$ret['text'] .= e107::getForm()->tabs($tabs);

		$ret['caption'] = LAN_CHECK_2.SEP.$lan;

		return $ret;
/*
		$ns -> tablerender(LANG_LAN_21.SEP.$lan.SEP.LAN_CHECK_2, $core_text);
		$ns -> tablerender(LAN_CHECK_3.": ".$lan."/admin", $core_admin);
		$ns -> tablerender(ADLAN_CL_7, $plug_text);
		$ns -> tablerender(LAN_THEMES, $theme_text);	*/
		//TODO Add a return statement here.
	}
	





	function write_lanfile($lan='')
	{
		if(!$lan){ 	return; }

		global $ns;
		
		unset($input);
		$kom_start = chr(47)."*";
		$kom_end = "*".chr(47);
	
		if(!empty($_SESSION['lancheck-edit-file']))
		{
			$writeit = $_SESSION['lancheck-edit-file'];
		}
		else
		{
			e107::getMessage()->addError("There is a problem with sessions");
			return;
		}
	
		$old_kom = "";
		$in_kom=0;
		
		if(is_readable($writeit)) // File Exists; 
		{
			$data = file($writeit);
			foreach($data as $line)
			{
		
				if (strpos($line,$kom_start) !== False && $old_kom == "")
				{
					$in_kom=1;
				}
				if ($in_kom) { $old_kom .= $line; }
				if (strpos($line,$kom_end) !== False && $in_kom) {$in_kom = 0;}
			}	
		}
		
	
	
		$message = "<div style='text-align:left'><br />";
		$input = chr(60)."?php\n";
		if ($old_kom == "")
		{
			// create CVS compatible description.
			$diz = chr(47)."*\n";
			$diz .= "+---------------------------------------------------------------+\n";
			$diz .= "|        e107 website content management system ".$lan." Language File\n";
			$diz .= "|        Released under the terms and conditions of the\n";
			$diz .= "|        GNU General Public License (http://gnu.org).\n";
			$diz .= "|        Last Modified: ".date("Y/m/d H:i:s")."\n";
			$diz .= "|\n";
		//	$diz .= "|        ".chr(36)."URL: $writeit ".chr(36)."\n";
		//	$diz .= "|        ".chr(36)."Revision: 1.0 ".chr(36)."\n";
		//	$diz .= "|        ".chr(36)."Id: ".date("Y/m/d H:i:s")." ".chr(36)."\n";
			$diz .= "|        ".chr(36)."Author: ".USERNAME." ".chr(36)."\n";
			$diz .= "+---------------------------------------------------------------+\n";
			$diz .= "*".chr(47)."\n\n";
		}
		else
		{
			$diz = $old_kom;
		}
	
		$input .= $diz;
		$message .= str_replace("\n","<br />",$diz);
	
		for ($i=0; $i<count($_POST['newlang']); $i++)
		{
			$notdef_start = "";
			$notdef_end = "\n";
			$deflang = (MAGIC_QUOTES_GPC === TRUE) ? stripslashes($_POST['newlang'][$i]) : $_POST['newlang'][$i];
			$func = "define";
			$quote = chr(34);
	
			if (strpos($_POST['newdef'][$i],"ndef++") !== FALSE )
			{
				$defvar = str_replace("ndef++","",$_POST['newdef'][$i]);
				$notdef_start = "if (!defined(".chr(34).$defvar.chr(34).")) {";
				$notdef_end = "}\n";
			}
			else
			{
				$defvar = $_POST['newdef'][$i];
			}

			if(empty($deflang))
			{
				continue; 
			}
	
			if($_POST['newdef'][$i] == "LC_ALL" && vartrue($_SESSION['lancheck-edit-file']))
			{
				$message .= $notdef_start.'setlocale('.htmlentities($defvar).','.$deflang.');<br />'.$notdef_end;
				$input .= $notdef_start."setlocale(".$defvar.",".$deflang.");".$notdef_end;
			}
			else
			{
				$message .= $notdef_start.$func.'('.$quote.htmlentities($defvar).$quote.',"'.$deflang.'");<br />'.$notdef_end;
				$input .= $notdef_start.$func."(".$quote.$defvar.$quote.", ".chr(34).$deflang.chr(34).");".$notdef_end;
			}
		}
	
		$message .="<br />";
		$message .="</div>";
		/*
		 $input .= "\n\n?>";
		*/
		// Write to file.
		
		$writeit = str_replace("//","/",$writeit); // Quick Fix. 
		
		$fp = @fopen($writeit,"w");
		if(!@fwrite($fp, $input))
		{
			$caption = LAN_ERROR;
			$message = LAN_CHECK_17;
			$status = e107::getMessage()->addError($caption)->render();
		}
		else
		{
			$caption = LAN_SAVED." <b>".$lan."/".$writeit."</b>";
			$status = e107::getMessage()->addSuccess($caption)->render();
		}
		fclose($fp);

	/*
		$message .= "<form method='post' action='".e_SELF."?tools' id='select_lang'>
		<div style='text-align:center'><br />";
		$message .= "<br /><br /><input class='btn' type='submit' name='language_sel[".$lan."]' value=\"".LAN_BACK."\" />
		</div></form>";*/
	
		unset($_SESSION['lancheck-edit-file']);



		$ns->tablerender($caption, $status. $message);
	}
	
		
		
		
	function check_core_lanfiles($checklan,$subdir='')
	{
		$tp = e107::getParser();
	//	$sql->db_Mark_Time('Start Get Core Lan Phrases English');
		$English = $this->get_comp_lan_phrases(e_LANGUAGEDIR."English/".$subdir,"English");
		
	//	$sql->db_Mark_Time('End Get Core Lan Phrases English');
		$check = $this->get_comp_lan_phrases(e_LANGUAGEDIR.$checklan."/".$subdir,$checklan);
		
	//	print_a($check);
	//	return;
		$text = "";
	
		$header = "<table class='table table-striped'>
		<tr>
		<th>".LAN_CHECK_16."</th>
		<th>".$this->transLanguage." ".LAN_FILE."</th>
		<th>".LAN_OPTIONS."</th></tr>";
	
		$keys = array_keys($English);
	
		sort($keys);
		$er = "";
	
		foreach($keys as $k)
		{

			if($k == 'bom')
			{
				continue;
			}


				$lnk = $k;
				$k_check = str_replace("English",$checklan,$k);
				if(array_key_exists($k,$check))
				{
					// $text .= "<tr><td class='forumheader3' style='width:45%'>{$lnk}</td>";
					$subkeys = array_keys($English[$k]);
	
					$er="";
					$utf_error = "";
	
					$bomkey = str_replace(".php","",$k_check);

	
					if(!empty($check['bom'][$bomkey]))
					{
						$bom_error = "<i>".$tp->lanVars(LAN_CHECK_15,array("'&lt;?php'","'?&gt;'"))."</i><br />";  // illegal chars
						$this->checkLog('bom',1);;
					}
					else
					{
						$bom_error = "";	
					}
	
					foreach($subkeys as $sk)
					{
						if($utf_error == "" && !empty($check[$k][$sk]) && !$this->is_utf8($check[$k][$sk]))
						{
							$utf_error = "<i>".LAN_CHECK_19."</i><br />";
							$this->checkLog('utf',1);
						}
	
						if($sk == "LC_ALL"){
							$check[$k][$sk] = str_replace(chr(34).chr(34),"",$check[$k][$sk]);
						}
					
						$er .= $this->check_lan_errors($English[$k],$check[$k],$sk);
					}
					
					if($this->errorsOnly == TRUE && !$er && !$utf_error && !$bom_error)
					{
						continue;		
					}
						
					$text .= "<tr><td class='forumheader3' style='width:45%'>{$lnk}</td>";
					$style = ($er) ? "forumheader2" : "forumheader3";
					$text .= "<td class='{$style}' style='width:50%'><div class='smalltext'>";
					$text .= $bom_error . $utf_error;
					$text .= (!$er && !$bom_error && !$utf_error) ? ADMIN_TRUE_ICON : $er."<br />";
					$text .= "</div></td>";
				}
				else
				{
					$this->checkLog('file',1);
				//	$this->newFile(e_LANGUAGEDIR.$checklan."/".$subdir.$lnk,$checklan);
					$text .= "<tr>
					<td class='forumheader3' style='width:45%'>{$lnk}</td>
					<td class='forumheader' style='width:50%'>".LAN_CHECK_4."</td>"; // file missing.
				}

				// Leave in EDIT button for all entries - to allow re-translation of bad entries.
				$subpath = ($subdir!='') ? $subdir.$k : $k;
				$parms = $_GET;
				$parms['sub'] = 'edit';
				$parms['file'] = $subpath;
				$parms['lan'] = $this->transLanguage;
				$parms['iframe'] = 1;

				$editUrl = e_REQUEST_SELF."?".http_build_query($parms,'&amp;');
				$text .="<td class='center' style='width:5%'>
				<a href='".$editUrl."'  data-modal-caption='".$subpath."' class='e-modal btn btn-primary' type='button'>".LAN_EDIT."</a>";
				$text .="</td></tr>";

		}

		$footer = "</table>";
		
		if($text)
		{
			return $header.$text.$footer;	
		}
		else
		{
		 	return "<div>".LAN_OK."</div>";
		}
	}



	
	function check_lan_errors($english,$translation,$def, $opts=array())
	{
		$eng_line = $english[$def];
		$trans_line = !empty($translation[$def]) ? $translation[$def] : '';
		
		// return $eng_line."<br />".$trans_line."<br /><br />";
			
		$error = array();
		$warning = array();
			
		if((is_array($translation) && !array_key_exists($def,$translation) && $eng_line != "") || (trim($trans_line) == "" && $eng_line != ""))
		{
			$this->checkLog('def',1);
			return $def.": ".LAN_CHECK_5."<br />";
		}

		if(empty($opts['no-warning']) && ($eng_line == $trans_line && !empty($eng_line)))
		{
			$warning[] = "<span class='text-warning'>".$def. ": ".LAN_CHECK_29."</span>";
		}
		
		if((strpos($eng_line,"[link=")!==FALSE && strpos($trans_line,"[link=")===FALSE) || (strpos($eng_line,"[b]")!==FALSE && strpos($trans_line,"[b]")===FALSE))
		{
			$error[] = $def. ": ".LAN_CHECK_30;
		}
		elseif((strpos($eng_line,"[")!==FALSE && strpos($trans_line,"[")===FALSE) || (strpos($eng_line,"]")!==FALSE && strpos($trans_line, "]")===FALSE))
		{
			$error[] = $def. ": ".LAN_CHECK_31;
		}
		
		if((strpos($eng_line,"--LINK--")!==false && strpos($trans_line,"--LINK--")===false))
		{
			$error[] = $def. ": Missing --LINK--";
		}
		
		if((strpos($eng_line,"e107.org")!==false && strpos($trans_line,"e107.org")===false))
		{
			$error[] = $def. ": Missing e107.org URL";
		}
		
		if((strpos($eng_line,"e107coders.org")!==FALSE && strpos($trans_line,"e107coders.org")===false))
		{
			$error[] = $def. ": Missing e107coders.org URL";
		}
		
		if(strip_tags($eng_line) != $eng_line)
		{
			$stripped = strip_tags($trans_line);
					
			if(($stripped == $trans_line))
			{					
				// echo "<br /><br />".$def. "<br />".$stripped."<br />".$trans_line;
				$error[] = $def. ": ".LAN_CHECK_32; 		
			}
		}
		
		$this->checkLog('def',count($error));
	
		$text = ($error) ? implode("<br />",$error)."<br />" : "";
		$text .= ($warning) ? implode("<br />",$warning)."<br />" : "";

		if($text)
		{
			return $text;
		}
		
	}
	
	
	
	
	function checkLog($type='error',$count)
	{
		$lan = $this->transLanguage;
		$_SESSION['lancheck'][$lan][$type] += $count;
		$_SESSION['lancheck'][$lan]['total'] += $count;
	}
	
	
	
	function get_lan_file_phrases($dir1,$dir2,$file1,$file2){
	
		$ret = array();
		$fname = $dir1.$file1;
		$type='orig';
	
		if(is_file($fname))
		{
			$data = file_get_contents($fname);
			$ret= $ret + $this->fill_phrases_array($data,$type);
			if(substr($data,0,5) != "<?php")
			{
				$key = str_replace(".php","",$fname);
				$ret['bom'][$key] = $fname;
			}
		}
	
		$fname = $dir2.$file2;
		$type='tran';
	
		if(is_file($fname))
		{
			$data = file_get_contents($fname);
			$ret=$ret + $this->fill_phrases_array($data,$type);
			if(substr($data,0,5) != "<?php")
			{
				$key = str_replace(".php","",$fname);
				$ret['bom'][$key] = $fname;
			}
		}
		elseif(substr($fname,-4) == ".php") 
		{
			file_put_contents($fname,"<?php\n\n?>");
		}
		
	
		return $ret;
	}
	
	
	
	
	function get_comp_lan_phrases($comp_dir,$lang,$depth=0)
	{
		if(!is_dir($comp_dir))
		{
			return array();
		}
		

		$fl = e107::getFile();
		$tp = e107::getParser();

		$ret = array();
			
		if($lang_array = $fl->get_files($comp_dir, ".php$","standard",$depth)){
			sort($lang_array);
		}

		foreach($lang_array as $k=> $f)
		{
			$path = str_replace(e_LANGUAGEDIR.$lang."/", "", $f['path'].$f['fname']);

			if(in_array($path, $this->deprecatedFiles))
			{
				unset($lang_array[$k]);
			}
		}




		if(strpos($comp_dir,e_LANGUAGEDIR) !== false)
		{
			$regexp = "#.php#";
			$mode = 'core';
		}
		elseif(strpos($comp_dir,e_THEME) !== false)
		{
			$regexp = "#".$lang."#";
			$mode = 'themes';
			//	var_dump($lang_array);
		}
		else
		{
			$regexp = "#".$lang."#";
			$mode = 'plugins';
		}

	//	$regexp = (strpos($comp_dir,e_LANGUAGEDIR) !== FALSE) ? "#.php#" : "#".$lang."#";



		foreach($lang_array as $f)
		{
			if($mode == 'plugins')
			{
				$tmpDir = str_replace($comp_dir,'',$f['path']);
			//	echo "<br />".$tmpDir;
				list($pluginDirectory, $other) = explode("/",$tmpDir, 2);


				if($mode == 'plugins' && ($this->thirdPartyPlugins !== true) && !in_array($pluginDirectory, $this->core_plugins))
				{
					continue;
				}
			}

			if($mode == 'themes')
			{
				$tmpDir = str_replace($comp_dir,'',$f['path']);
			//	echo "<br />".$tmpDir;
				list($themeDirectory, $other) = explode("/",$tmpDir, 2);


				if($mode == 'themes' && ($this->thirdPartyPlugins !== true) && !in_array($themeDirectory, $this->core_themes))
				{
					continue;
				}
			}

			if(preg_match($regexp,$f['path'].$f['fname']) && is_file($f['path'].$f['fname']))
			{
				$allData = file_get_contents($f['path'].$f['fname']);
				$data = explode("\n",$allData);
				// $data = file($f['path'].$f['fname']);
				$relpath = str_replace($comp_dir,"",$f['path']);
				
				$key = str_replace(".php","",$relpath.$f['fname']);
				
				if(substr($data[0],0,5) != "<?php")
				{
					
					$ret['bom'][$key] = $f['fname'];
				}
						
				$end_of_file = 0;
							
				foreach($data as $line)
				{
					if($end_of_file == 1)
					{
						$ret['bom'][$key] = $f['fname'];
					}
											
					$line = trim($line);
					if($line == "?>")
					{
						$end_of_file = 1;  	
					}
				}
					
			
				
				if($f['path'].$f['fname'] == e_LANGUAGEDIR.$lang."/".$lang.".php")
				{
					$f['fname'] = "English.php";  // change the key for the main language file.
				}
	
				if($f['path'].$f['fname'] == e_LANGUAGEDIR.$lang."/".$lang."_custom.php")
				{
					$f['fname'] = "English_custom.php";  // change the key for the main language file.
				}
	
				$ret=$ret + $this->fill_phrases_array($allData,$relpath.$f['fname']);
	
			}
		}




		return $ret;
	
	}
	
	
	
	// for plugins and themes - checkes what kind of language files directory structure we have
	function check_lanfiles($mode,$comp_name,$base_lan="English",$target_lan)
	{


		$tp = e107::getParser();

		$folder['P'] = e_PLUGIN.$comp_name;
		$folder['T'] = e_THEME.$comp_name;
		$comp_dir = $folder[$mode];
	
		$baselang 	= $this->get_comp_lan_phrases($comp_dir."/languages/","English",1);
		$check 		= $this->get_comp_lan_phrases($comp_dir."/languages/",$target_lan,1);




		$text = "";
		$keys = array_keys($baselang);
		sort($keys);
	
		foreach($keys as $k)
		{
			
			if($k == 'bom')
			{
				continue;
			}
			
			$lnk = $k;
			//echo "klucz ".$k."<br />";
			$k_check = str_replace("English",$target_lan,$k);
			if(array_key_exists($k_check,$check))
			{
				
	
				$subkeys = array_keys($baselang[$k]);
				$er="";
				$utf_error = "";
	
				$bomkey = str_replace(".php","",$k_check);
				if($check['bom'][$bomkey])
				{
					$bom_error = "<i>".$tp->lanVars(LAN_CHECK_15,array("'&lt;?php'","'?&gt;'"))."</i><br />";
					$this->checkLog('bom',1);
				}
				else
				{
					$bom_error = "";	
				}
			// 	$bom_error = ($check['bom'][$bomkey]) ? "<i>".LAN_CHECK_15."</i><br />" : ""; // illegal chars
			
				foreach($subkeys as $sk)
				{
					if($utf_error == "" && !empty($check[$k_check][$sk]) && !$this->is_utf8($check[$k_check][$sk]))
					{
						$utf_error = "<i>".LAN_CHECK_19."</i><br />";
						$this->checkLog('utf',1);
					}
					
					/*
					if(!array_key_exists($sk,$check[$k_check]) || (trim($check[$k_check][$sk]) == "" && $baselang[$k][$sk] != ""))
					{
						$er .= ($er) ? "<br />" : "";
						$er .= $sk." ".LAN_CHECK_5;
					}
					*/
					$er .= $this->check_lan_errors($baselang[$k],$check[$k_check],$sk);
				}
	
				if($this->errorsOnly == TRUE && !$er && !$utf_error && !$bom_error)
				{
					continue;		
				}
	
				$text .= "<tr>
				<td class='forumheader3' style='width:20%'>".$comp_name."</td>
				<td class='forumheader3' style='width:25%'>".str_replace("English/","",$lnk)."</td>";
	
				$style = ($er) ? "forumheader2" : "forumheader3";
				$text .= "<td class='{$style}' style='width:50%'><div class='smalltext'>";
				$text .= $bom_error . $utf_error;
				$text .= (!$er && !$bom_error && !$utf_error) ? ADMIN_TRUE_ICON : $er."<br />";
				$text .= "</div></td>";
			}
			else
			{
				$this->checkLog('file',1);
				$this->newFile($comp_dir."/languages/".$lnk,$target_lan);
				
				$text .= "<tr>
				<td class='forumheader3' style='width:20%'>".$comp_name."</td>
				<td class='forumheader3' style='width:25%'>".str_replace("English/","",$lnk)."</td>
				<td class='forumheader' style='width:50%'><span style='cursor:pointer' title=\"".str_replace("English",$target_lan,$lnk)."\">".LAN_CHECK_4."</span></td>";
			}
	
			$text .="<td class='forumheader3' style='width:5%;text-align:center'>";

		//	$text .= "<input class='btn btn-primary' type='button' style='width:60px' name='but_$i' value=\"".LAN_EDIT."\" onclick=\"window.location='".e_SELF."?f=".$comp_dir."/languages/".$lnk."&amp;lan=".$target_lan."&amp;mode={$mode}'\" /> ";

			$parms = $_GET;
			$parms['sub'] = 'edit';
			$parms['file'] = $comp_dir."/languages/".$lnk;
			$parms['lan'] = $this->transLanguage;
			$parms['iframe'] = 1;
			$parms['type'] = $mode;

			$editUrl = e_REQUEST_SELF."?".http_build_query($parms,'&amp;');

			$text .= "<a href='".$editUrl."'  class='e-modal btn btn-primary' data-modal-caption='".str_replace("../","",$comp_dir)."'>".LAN_EDIT."</a> "; // href='".e_REQUEST_URI."&amp;f=".$comp_dir."/languages/".$lnk."&amp;lan=".$target_lan."&amp;type={$mode}'
		//	<a href='".$editUrl."'  data-modal-caption='".$subpath."' class='e-modal btn btn-primary' type='button' style='width:60px'>".LAN_EDIT."</a>";


			$text .="</td></tr>";
		}
	
	
	
		// if (!$known) {$text = LAN_CHECK_18." : --> ".$fname." :: ".$dname;}
		return $text;
	}
	
	function newFile($lnk,$target_lan)
	{
		if($target_lan == 'English') 
		{
			return;	
		}
				
		$newfile = str_replace("English",$target_lan,$lnk);
		$dir = dirname($newfile);
				
		if($dir != '.' && !is_dir($dir))
		{
		//	echo "<br />dir: ".$dir;
			mkdir($dir,0755);	
		} 
		
		if(!file_exists($newfile))
		{
		//	echo "<br />file: ".$newfile;
			$data = chr(60)."?php\n\n// define(\"EXAMPLE\",\"Generated Empty Language File\");";
			file_put_contents($newfile,$data);	
		}
	}
	
	
	function edit_lanfiles($dir1,$dir2,$f1,$f2,$lan)
	{
		if($lan == '')
		{
			echo "Language selection was lost. ";
			return null;
		}
		
	//	$ns = e107::getRender();
		$sql = e107::getDb();

	
		/*    echo "<br />dir1 = $dir1";
		echo "<br />file1 = $f1";
	
		echo "<br />dir2 = $dir2";
		echo "<br />file2 = $f2";*/
		
	
	
		if($dir2.$f2 == e_LANGUAGEDIR.$lan."/English.php") // it's a language config file.
		{
			$f2 = $lan.".php";
			$root_file = e_LANGUAGEDIR.$lan."/".$lan.".php";
		}
		else
		{
			$root_file = $dir2.$f2;
		}
	
		if($dir2.$f2 == e_LANGUAGEDIR.$lan."/English_custom.php") // it's a language config file.
		{
			$f2 = $lan."_custom.php";
			$root_file = e_LANGUAGEDIR.$lan."/".$lan."_custom.php";
		}

		$this->newFile($dir2.$f2,$lan);

		$writable = is_writable($dir2);
		$trans = $this->get_lan_file_phrases($dir1,$dir2,$f1,$f2);
		$keys = array_keys($trans);
		sort($keys);
	
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."' id='transform'>
		<table class='table table-striped'>
		<thead>
		<tr>
			<th>LAN</th>
			<th>English</th>
			<th>".$lan."</th>
		</tr>
		</thead><tbody>";
	
		$subkeys = array_keys($trans['orig']);
		foreach($subkeys as $sk)
		{
			$rowamount = round(strlen($trans['orig'][$sk])/34)+1;
			$hglt1=""; $hglt2="";
			if ($trans['tran'][$sk] == "" && $trans['orig'][$sk]!="") {
				$hglt1="<span class='label label-danger label-important e-tip' title='".LAN_MISSING."'>";//Missing
				$hglt2="</span>";
			}
			elseif($trans['tran'][$sk] == $trans['orig'][$sk])
			{
				$hglt1="<span class='label label-warning e-tip' title='".LAN_CHECK_28."'>";//Identical
				$hglt2="</span>";
			}
			$text .="<tr>
			<td style='width:10%;vertical-align:top'>".$hglt1.htmlentities($sk).$hglt2."</td>
			<td style='width:40%;vertical-align:top'>".htmlentities(str_replace("ndef++","",$trans['orig'][$sk])) ."</td>";
			$text .= "<td class='forumheader3' style='width:50%;vertical-align:top'>";
			$text .= ($writable) ? "<textarea  class='input-xxlarge' name='newlang[]' rows='$rowamount' cols='45' style='height:100%'>" : "";
			$text .= htmlentities(str_replace("ndef++","",$trans['tran'][$sk]));
			$text .= ($writable) ? "</textarea>" : "";
			//echo "orig --> ".$trans['orig'][$sk]."<br />";
			if (strpos($trans['orig'][$sk],"ndef++") !== False)
			{
				//echo "+orig --> ".$trans['orig'][$sk]." <> ".strpos($trans['orig'][$sk],"ndef++")."<br />";
				$text .= "<input type='hidden' name='newdef[]' value='ndef++".$sk."' />";
			}
			else
			{
				$text .= "<input type='hidden' name='newdef[]' value='".$sk."' />";
			}
			$text .="</td></tr>";
		}
	
		unset($_SESSION['lancheck-edit-file']);
	
		//Check if directory is writable
		
		$text .= "</tbody></table>";
		
		if($writable)
		{
			$text .="<div class='buttons-bar center'>
			<input type='hidden' name='lan' value='{$lan}' />
			<input class='btn btn-warning' type='submit' name='saveLanguageFile' value=\"".LAN_SAVE." ".str_replace($dir2,"",$root_file)." \" />
			</div>";
	
			if($root_file)
			{			
				$_SESSION['lancheck-edit-file'] = $root_file;
			}
	
			
		}
	
		$text .= "
		
		</form>
		</div>";
	
		$text .= "<form method='post' action='".e_SELF."?tools' id='select_lang'>
		<div style='text-align:center'><br />";
		$text .= (!$writable) ? "<br />".$dir2.$f2.LAN_NOTWRITABLE : "";
	//	$text .= "<br /><br /><input class='btn' type='submit' name='language_sel[{$lan}]' value=\"".LAN_BACK."\" />";
		$text .= "</div></form>";
	
		$capFile = str_replace("../","",$dir2.$f2);
		$caption = LANG_LAN_21.SEP.$lan.SEP.LAN_CHECK_2.SEP.LAN_EDIT.SEP.$capFile;

		return array('caption'=>$caption, 'text'=>$text, 'mode'=>'edit', 'file'=>$capFile);

		// $ns->tablerender($caption, $text);

	
	}
	
	
	
	function fill_phrases_array($data,$type)
	{	
		$retloc = array();
		
		if(preg_match_all('/(\/\*[\s\S]*?\*\/)/i',$data, $multiComment))
		{
			$data = str_replace($multiComment[1],'',$data);	// strip multi-line comments. 	
		}
					
		if(preg_match('/^\s*?setlocale\s*?\(\s*?([\w]+)\s*?,\s*?(.+)\s*?\)\s*?;/im',$data,$locale)) // check for setlocale();
		{
			$retloc[$type][$locale[1]]= $locale[2];	
		}
				
		if(preg_match_all('/^\s*?define\s*?\(\s*?(\'|\")([\w]+)(\'|\")\s*?,\s*?(\'|\")([\s\S]*?)\s*?(\'|\")\s*?\)\s*?;/imu',$data,$matches))
		{
			$def = $matches[2];
			$values = $matches[5];	
	
			foreach($def as $k=>$d)
			{
				$retloc[$type][$d]= $values[$k];
			}	
		}
			
		return $retloc;
		
		/*
		echo "<h2>Raw Data ".$type."</h2><pre>";
		echo htmlentities($data);
		echo "</pre>";	
	
		*/
			
	}
	
	
	
	//--------------------------------------------------------------------
	
	
	function is_utf8($str) {
		/*
		* @see http://hsivonen.iki.fi/php-utf8/   validation.php
		*/
		if(strtolower(CHARSET) != "utf-8" || $str == "")
		{
			return TRUE;
		}
	
		return (preg_match('/^.{1}/us',$str,$ar) == 1);
	}
	

	/**
	 * Clean-up definitions in a language file removed closing php tags and strip specific html..
	 * @param array $defKeys array of constants to comment out.
	 * @param string $path path to the language file to edit.
	 */
	function cleanFile($path, $defKeys=null)
	{
		if(empty($path) || !file_exists($path) || stripos($path,'English')!==false)
		{
			return null;
		}

		$content = file_get_contents($path);
		$lines = explode("\n",$content);

		$srch = array();
		$repl = array();

		$srch[] = '<b>';
		$srch[] = '</b>';

		$repl[] = '[b]';
		$repl[] = '[/b]';


		if(!empty($defKeys))
		{
			foreach($defKeys as $const)
			{
				$srch[] = "define('".$const."'";
				$srch[] = 'define("'.$const.'"';

				$repl[] = "// define('".$const."'";
				$repl[] = '// define("'.$const.'"';
			}
		}

		$new = '';
		foreach($lines as $ln)
		{
			if(strpos($ln,'?>') !==false)
			{   continue;

			}

			if(strpos($ln, '""') !== false || strpos($ln, "''") !== false) // empty
			{
				continue;
			}

			if(strpos($ln,'//') !==false)
			{
				$new .= $ln."\n";
				continue;
			}

			if(!empty($srch))
			{
				$new .= str_replace($srch,$repl,$ln)."\n";
			}
			else
			{
				$new .= $ln."\n";
			}
		}

		if(file_put_contents($path,$new))
		{
			return true;
		}

		return false;


	}

}