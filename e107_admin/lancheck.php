<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     �Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_admin/lancheck.php,v $
|     $Revision$
|     $Date$
|     $Author$
|	  With code from Izydor and Lolo.
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("0")) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'language';
require_once("auth.php");

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
		$diz .= "+---------------------------------------------------------------+\n";
		$diz .= "|        e107 website system ".$lan." Language File\n";
		$diz .= "|        Released under the terms and conditions of the\n";
		$diz .= "|        GNU General Public License (http://gnu.org).\n";
		$diz .= "|\n";
		$diz .= "|        ".chr(36)."Source: $writeit ".chr(36)."\n";
		$diz .= "|        ".chr(36)."Revision: 1.0 ".chr(36)."\n";
		$diz .= "|        ".chr(36)."Date: ".date("Y/m/d H:i:s")." ".chr(36)."\n";
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
		$caption = LAN_ERROR;
		$message = LAN_CHECK_17;
	}
	else
	{
		$caption = LAN_SAVED." <b>$lan/".$writeit."</b>";
	}
	fclose($writeit);

	$message .= "<form method='post' action='".e_SELF."' id='select_lang'>
	<div style='text-align:center'><br />";
	$message .= "<br /><br /><input class='button' type='submit' name='language_sel' value=\"".LAN_BACK."\" />
	<input type='hidden' name='language' value='$lan' /></div></form>";


	$ns -> tablerender($caption, $message);
	require_once(e_ADMIN."footer.php");
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

	edit_lanfiles($dir1,$dir2,$f1,$f2);

}

// ===========================================================================

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
"khatru","kubrick","lamb","leaf","reline","sebes","vekna_blue");


if(isset($_POST['language_sel']) && isset($_POST['language'])){

	$ns -> tablerender(LAN_CHECK_3.": ".$_POST['language'],check_core_lanfiles($_POST['language']));
	$ns -> tablerender(LAN_CHECK_3.": ".$_POST['language']."/admin",check_core_lanfiles($_POST['language'],"admin/"));

	$plug_text = "<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr>
	<td class='fcaption'>".LAN_PLUGIN."</td>
	<td class='fcaption'>".LAN_CHECK_16."</td>
	<td class='fcaption'>".$_POST['language']."</td>
	<td class='fcaption'>".LAN_OPTIONS."</td></tr>";

	foreach($core_plugins as $plugs)
	{
		if(is_readable(e_PLUGIN.$plugs))
		{
			$plug_text .= check_lanfiles('P',$plugs,"English",$_POST['language']);
		}
	}
	$plug_text .= "</table>";
	$ns -> tablerender(ADLAN_CL_7,$plug_text);

	$theme_text = "<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr>
	<td class='fcaption'>Theme</td>
	<td class='fcaption'>".LAN_CHECK_16."</td>
	<td class='fcaption'>".$_POST['language']."</td>
	<td class='fcaption'>".LAN_OPTIONS."</td></tr>";
	foreach($core_themes as $them)
	{
		if(is_readable(e_THEME.$them))
		{
			$theme_text .= check_lanfiles('T',$them,"English",$_POST['language']);
		}
	}
	$theme_text .= "</table>";

	$ns -> tablerender("Themes",$theme_text);
	require_once(e_ADMIN."footer.php");
	exit;
}


function check_core_lanfiles($checklan,$subdir=''){
	global $lanfiles,$_POST;

	$English = get_comp_lan_phrases(e_LANGUAGEDIR."English/".$subdir,$checklan);
	$check = get_comp_lan_phrases(e_LANGUAGEDIR.$checklan."/".$subdir,$checklan);

	$text .= "<table class='fborder' style='".ADMIN_WIDTH."'>
	<tr>
	<td class='fcaption'>".LAN_CHECK_16."</td>
	<td class='fcaption'>".$_POST['language']." File</td>
	<td class='fcaption'>".LAN_OPTIONS."</td></tr>";

	$keys = array_keys($English);

	sort($keys);

	foreach($keys as $k)
	{
		if($k != "bom")
		{
			$lnk = $k;
			$k_check = str_replace("English",$checklan,$k);
			if(array_key_exists($k,$check))
			{
				$text .= "<tr><td class='forumheader3' style='width:45%'>{$lnk}</td>";
				$subkeys = array_keys($English[$k]);

				$er="";
				$utf_error = "";

				$bomkey = str_replace(".php","",$k_check);
				$bom_error = ($check['bom'][$bomkey]) ? "<i>".LAN_CHECK_15."</i><br />" : ""; // illegal chars

				foreach($subkeys as $sk)
				{
					if($utf_error == "" && !is_utf8($check[$k][$sk]))
					{
						$utf_error = "<i>".LAN_CHECK_19."</i><br />";
					}

					if($sk == "LC_ALL"){
						$check[$k][$sk] = str_replace(chr(34).chr(34),"",$check[$k][$sk]);
					}

					if((!array_key_exists($sk,$check[$k]) && $English[$k][$sk] != "") || (trim($check[$k][$sk]) == "" && $English[$k][$sk] != ""))
					{

						$er .= ($er) ? "<br />" : "";
						$er .= $sk." ".LAN_CHECK_5;
					}
				}

				$style = ($er) ? "forumheader2" : "forumheader3";
				$text .= "<td class='{$style}' style='width:50%'><div class='smalltext'>";
				$text .= $bom_error . $utf_error;
				$text .= (!$er && !$bom_error && !$utf_error) ? "<img src='".e_IMAGE."fileinspector/integrity_pass.png' alt='".LAN_OK."' />" : $er."<br />";
				$text .= "</div></td>";
			}
			else
			{
				$text .= "<tr>
				<td class='forumheader3' style='width:45%'>{$lnk}</td>
				<td class='forumheader' style='width:50%'>".LAN_CHECK_4."</td>"; // file missing.
			}
			// Leave in EDIT button for all entries - to allow re-translation of bad entries.
			$subpath = ($subdir!='') ? $subdir.$k : $k;
			$text .="<td class='forumheader3' style='width:5%;text-align:center'>
			<input class='tbox' type='button' style='width:60px' name='but_$i' value=\"".LAN_EDIT."\" onclick=\"window.location='".e_SELF."?".$subpath."|".$_POST['language']."'\" /> ";
			$text .="</td></tr>";
		}
	}
	$text .= "</table>";

	return $text;
}


function get_lan_file_phrases($dir1,$dir2,$file1,$file2){

	$ret = array();
	$fname = $dir1.$file1;
	$type='orig';

	if(is_file($fname))
	{
		$data = file($fname);
		$ret=$ret + fill_phrases_array($data,$type);
		if(substr($data[0],0,5) != "<?php")
		{
			$key = str_replace(".php","",$fname);
			$ret['bom'][$key] = $fname;
		}
	}

	$fname = $dir2.$file2;
	$type='tran';

	if(is_file($fname))
	{
		$data = file($fname);
		$ret=$ret + fill_phrases_array($data,$type);
		if(substr($data[0],0,5) != "<?php")
		{
			$key = str_replace(".php","",$fname);
			$ret['bom'][$key] = $fname;
		}
	}
	

	return $ret;
}


function get_comp_lan_phrases($comp_dir,$lang,$depth=0)
{
	require_once(e_HANDLER."file_class.php");
	$fl = new e_file;
	$ret = array();

	if($lang_array = $fl->get_files($comp_dir, ".php","standard",$depth)){
		sort($lang_array);
	}

	$regexp = (strpos($comp_dir,e_LANGUAGEDIR) !== FALSE) ? "#.php#" : "#".$lang."#";

	foreach($lang_array as $f)
	{
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

			$ret=$ret + fill_phrases_array($data,$relpath.$f['fname']);

		}
	}

	return $ret;

}

// for plugins and themes - checkes what kind of language files directory structure we have
function check_lanfiles($mode,$comp_name,$base_lan="English",$target_lan){
	global $ns,$sql;

	$folder['P'] = e_PLUGIN.$comp_name;
	$folder['T'] = e_THEME.$comp_name;
	$comp_dir = $folder[$mode];

	$baselang = get_comp_lan_phrases($comp_dir."/languages/","English",1);
	$check = get_comp_lan_phrases($comp_dir."/languages/",$target_lan,1);

	$text = "";
	$keys = array_keys($baselang);
	sort($keys);

	foreach($keys as $k)
	{
		$lnk = $k;
		//echo "klucz ".$k."<br />";
		$k_check = str_replace("English",$target_lan,$k);
		if(array_key_exists($k_check,$check))
		{
			$text .= "<tr>
			<td class='forumheader3' style='width:20%'>".$comp_name."</td>
			<td class='forumheader3' style='width:25%'>".str_replace("English/","",$lnk)."</td>";

			$subkeys = array_keys($baselang[$k]);
			$er="";
			$utf_error = "";

			$bomkey = str_replace(".php","",$k_check);
			$bom_error = ($check['bom'][$bomkey]) ? "<i>".LAN_CHECK_15."</i><br />" : ""; // illegal chars

			foreach($subkeys as $sk)
			{
				if($utf_error == "" && !is_utf8($check[$k_check][$sk]))
				{
					$utf_error = "<i>".LAN_CHECK_19."</i><br />";
				}

				if(!array_key_exists($sk,$check[$k_check]) || (trim($check[$k_check][$sk]) == "" && $baselang[$k][$sk] != ""))
				{
					$er .= ($er) ? "<br />" : "";
					$er .= $sk." ".LAN_CHECK_5;
				}
			}

			$style = ($er) ? "forumheader2" : "forumheader3";
			$text .= "<td class='{$style}' style='width:50%'><div class='smalltext'>";
			$text .= $bom_error . $utf_error;
			$text .= (!$er && !$bom_error && !$utf_error) ? "<img src='".e_IMAGE."fileinspector/integrity_pass.png' alt='".LAN_OK."' />" : $er."<br />";
			$text .= "</div></td>";
		}
		else
		{
			$text .= "<tr>
			<td class='forumheader3' style='width:20%'>".$comp_name."</td>
			<td class='forumheader3' style='width:25%'>".str_replace("English/","",$lnk)."</td>
			<td class='forumheader' style='width:50%'><span style='cursor:pointer' title=\"".str_replace("English",$target_lan,$lnk)."\">".LAN_CHECK_4."</span></td>";
		}

		$text .="<td class='forumheader3' style='width:5%;text-align:center'>
		<input class='tbox' type='button' style='width:60px' name='but_$i' value=\"".LAN_EDIT."\" onclick=\"window.location='".e_SELF."?".$comp_dir."/languages/".$lnk."|".$target_lan."|file'\" /> ";
		$text .="</td></tr>";
	}



	// if (!$known) {$text = LAN_CHECK_18." : --> ".$fname." :: ".$dname;}
	return $text;
}

function edit_lanfiles($dir1,$dir2,$f1,$f2){
	global $ns,$sql,$lan;

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


	$writable = (is_writable($dir2)) ? TRUE : FALSE;
	$trans = get_lan_file_phrases($dir1,$dir2,$f1,$f2);
	$keys = array_keys($trans);
	sort($keys);

	$text = "<div style='text-align:center'>
	<form method='post' action='".e_SELF."?".e_QUERY."' id='transform'>
	<table style='".ADMIN_WIDTH."' class='fborder'>";

	$subkeys = array_keys($trans['orig']);
	foreach($subkeys as $sk)
	{
		$rowamount = round(strlen($trans['orig'][$sk])/34)+1;
		$hglt1=""; $hglt2="";
		if ($trans['tran'][$sk] == "" && $trans['orig'][$sk]!="") {
			$hglt1="<span style='font-style:italic;font-weight:bold;color:red'>";
			$hglt2="</span>";
		}
		$text .="<tr>
		<td class='forumheader3' style='width:10%;vertical-align:top'>".$hglt1.htmlentities($sk).$hglt2."</td>
		<td class='forumheader3' style='width:40%;vertical-align:top'>".htmlentities(str_replace("ndef++","",$trans['orig'][$sk])) ."</td>";
		$text .= "<td class='forumheader3' style='width:50%;vertical-align:top'>";
		$text .= ($writable) ? "<textarea  class='tbox' name='newlang[]' rows='$rowamount' cols='45' style='height:100%'>" : "";
		$text .= str_replace("ndef++","",$trans['tran'][$sk]);
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

	//Check if directory is writable
	if($writable)
	{
		$text .="<tr style='vertical-align:top'>
		<td colspan='3' style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='submit' value=\"".LAN_SAVE." ".str_replace($dir2,"",$root_file)." \" />";

		if($root_file)
		{
			$text .= "<input type='hidden' name='root' value='".$root_file."' />";
		}

		$text .= "</td></tr>";
	}

	$text .= "
	</table>
	</form>
	</div>";

	$text .= "<form method='post' action='".e_SELF."' id='select_lang'>
	<div style='text-align:center'><br />";
	$text .= (!$writable) ? "<br />".$dir2.$f2.LAN_NOTWRITABLE : "";
	$text .= "<br /><br /><input class='button' type='submit' name='language_sel' value=\"".LAN_BACK."\" />
	<input type='hidden' name='language' value='$lan' /></div></form>";


	$caption = LAN_CHECK_3." <b>".$dir2.$f2."</b> -> <b>".$lan."</b>";
	$ns -> tablerender($caption, $text);
	require_once(e_ADMIN."footer.php");
	exit;

}

function fill_phrases_array($data,$type) {

	$retloc = array();

	foreach($data as $line){
		//echo "line--> ".$line."<br />";
		if (strpos($line,"define(") !== FALSE && strpos($line,");") === FALSE)
		{
			$indef=1;
			$bigline="";
			// echo "big1 -->".$line."<br />";
		}
		if ($indef)
		{
			$bigline.=str_replace("\n","",$line);
			// echo "big2 -->".$line."<br />";
		}
		if (strpos($line,"define(") === FALSE && strpos($line,");") !== FALSE)
		{
			$indef=0;
			$we_have_bigline=1;
			// echo "big3 -->".$line."<br />";
		}

		if(strpos($line,"setlocale(") !== FALSE)
		{
			$indef=1;
			$we_have_bigline=0;
		}

		if ((strpos($line,"define(") !== FALSE && strpos($line,");") !== FALSE && substr(ltrim($line),0,2) != "//") || $we_have_bigline || strpos($line,"setlocale(") !== FALSE)
		{

			if ($we_have_bigline)
			{
				$we_have_bigline=0;
				$line=$bigline;
				// echo "big -->".$line."<br />";
			}
			$ndef = "";
			//echo "_ndefline -->".$line."<br />";
			if (strpos($line,"defined(") !== FALSE )
			{
				$ndef = "ndef++";
				$line = substr($line,strpos($line,"define("));
			}

			if(strpos($line,"setlocale(") !== FALSE)
			{
				$pos = substr(strstr($line,","),1);
				$rep = array(");","\n",'""');
				$val = str_replace($rep,"",$pos);
				$retloc[$type]['LC_ALL']= $val;
//				$retloc['orig']['LC_ALL']= "'en'";
			}
			else
			{

				//echo "ndefline: ".$line."<br />";
				if(preg_match("#\"(.*?)\".*?\"(.*)\"#",$line,$matches) ||
				preg_match("#\'(.*?)\'.*?\"(.*)\"#",$line,$matches) ||
				preg_match("#\"(.*?)\".*?\'(.*)\'#",$line,$matches) ||
				preg_match("#\'(.*?)\'.*?\'(.*)\'#",$line,$matches) ||
				preg_match("#\((.*?)\,.*?\"(.*)\"#",$line,$matches) ||
				preg_match("#\((.*?)\,.*?\'(.*)\'#",$line,$matches))
				{
					//echo "get_lan -->".$matches[1]." :: ".$ndef.$matches[2]."<br />";
					if(!isset($retloc[$type][$matches[1]]))
					{
						$retloc[$type][$matches[1]]= $ndef.$matches[2];
					}
				}
			}
		}
	}

	return $retloc;
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


function lancheck_adminmenu() {

	include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_language.php");

	global $action,$pref;
	if ($action == "") {
		$action = "tools";
	}

	if($action == "modify"){
		$action = "db";
	}
	$var['main']['text'] = LAN_PREFS;
	$var['main']['link'] = e_ADMIN."language.php";

	if(isset($pref['multilanguage']) && $pref['multilanguage']){
		$var['db']['text'] = LANG_LAN_03;
		$var['db']['link'] = e_ADMIN."language.php?db";
	}

	$var['tools']['text'] = ADLAN_CL_6;
	$var['tools']['link'] = e_ADMIN."language.php?tools";


	show_admin_menu(ADLAN_132, $action, $var);
}
