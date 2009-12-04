<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Language check
 * With code from Izydor and Lolo.
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/lancheck.php,v $
 * $Revision: 1.21 $
 * $Date: 2009-12-04 12:00:05 $
 * $Author: e107coders $
 *
*/
require_once("../class2.php");
if (!getperms("0")) {
	header("location:".e_BASE."index.php");
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'language';
require_once("auth.php");
require_once(e_HANDLER."message_handler.php");
require_once(e_HANDLER."form_handler.php");

$frm = new e_form();
$emessage = &eMessage::getInstance();
$lck = new lancheck;


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
		$emessage->add(LAN_CHECK_17, E_MESSAGE_ERROR);
	}
	else
	{
		$caption = LAN_CHECK_PAGE_TITLE.' - '.LAN_CHECK_24;
		$emessage->add(sprintf(LAN_CHECK_23, basename($writeit)), E_MESSAGE_SUCCESS);
	}
	fclose($writeit);

	$message .= "
	<form method='post' action='".e_SELF."' id='core-lancheck-save-file-form'>
	<div class='center'>
		".$frm->admin_button('language_sel', LAN_BACK)."
		".$frm->hidden('language', $lan)."
	</div>
	</form>";
	


	$e107->ns->tablerender($caption, $emessage->render().$message);
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

$core_themes = array("_blank", "e107v4a", "jayya", "khatru", "leaf", "vekna_blue");


if(isset($_POST['language_sel']) && isset($_POST['language']))
{

	$text = $lck->check_core_lanfiles($_POST['language']).$lck->check_core_lanfiles($_POST['language'],"admin/");

	$text .= "
		<fieldset id='core-lancheck-plugin'>
			<legend>".ADLAN_CL_7."</legend>
			<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='4'>
					<col style='width: 25%'></col>
					<col style='width: 25%'></col>
					<col style='width: 40%'></col>
					<col style='width: 10%'></col>
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
			<legend>".LAN_CHECK_22."</legend>
			<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='4'>
					<col style='width: 25%'></col>
					<col style='width: 25%'></col>
					<col style='width: 40%'></col>
					<col style='width: 10%'></col>
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
		$mes->add(LAN_CHECK_27.'<b>'.$lck->error_count.'</b>', E_MESSAGE_SUCCESS);		
	}
	else  
	{
		$mes->add(LAN_CHECK_27.'<b>'.$lck->error_count.'</b>', E_MESSAGE_WARNING);
	}
	

	$ns->tablerender(LAN_CHECK_25, $mes->render(). $text);


	
	require_once(e_ADMIN."footer.php");
	exit;
}

class lancheck
{
	var $error_count=0;
	
	function check_core_lanfiles($checklan,$subdir=''){
		global $frm;
		
		$English = $this->get_comp_lan_phrases(e_LANGUAGEDIR."English/".$subdir,$checklan);
		$check = $this->get_comp_lan_phrases(e_LANGUAGEDIR.$checklan."/".$subdir,$checklan);
		$legend_txt = LAN_CHECK_3.": ".$_POST['language']."/".$subdir;
		$fieldset_id = $subdir ? str_replace('/', '', $_POST['language'])."-".str_replace('/', '', $subdir) : str_replace('/', '', $_POST['language']);
		$text .= "
			<fieldset id='core-lancheck-{$fieldset_id}'>
				<legend>{$legend_txt}</legend>
				<table cellpadding='0' cellspacing='0' class='adminlist'>
					<colgroup span='3'>
						<col style='width: 50%'></col>
						<col style='width: 40%'></col>
						<col style='width: 10%'></col>
					</colgroup>
					<thead>
						<tr>
							<th>".LAN_CHECK_16."</th>
							<th>".$_POST['language'].' '.LAN_CHECK_20."</th>
							<th class='center last'>".LAN_OPTIONS."</th>
						</tr>
					</thead>
					<tbody>
		";
	
		$keys = array_keys($English);
	
		sort($keys);
	
		$i = 0;
		foreach($keys as $k)
		{
			if($k != "bom")
			{
				$lnk = $k;
				$k_check = str_replace("English",$checklan,$k);
				$text .= "
						<tr>
				";
				
				if(array_key_exists($k,$check))
				{
					$text .= "
							<td>{$lnk}</td>
					";
					$subkeys = array_keys($English[$k]);
	
					$er="";
					$utf_error = "";
	
					$bomkey = str_replace(".php","",$k_check);
					$bom_error = ($check['bom'][$bomkey]) ? "<span class='error'><em>".LAN_CHECK_15."</em></span><br />" : ""; // illegal chars
	
					foreach($subkeys as $sk)
					{
						if($utf_error == "" && !$this->is_utf8($check[$k][$sk]))
						{
							$utf_error = "<span class='error'><em>".LAN_CHECK_19."</em></span><br />";
						}
	
						if($sk == "LC_ALL"){
							$check[$k][$sk] = str_replace(chr(34).chr(34),"",$check[$k][$sk]);
						}
	
						if((!array_key_exists($sk,$check[$k]) && $English[$k][$sk] != "") || (trim($check[$k][$sk]) == "" && $English[$k][$sk] != ""))
						{
	
							$er .= ($er) ? "<br />" : "";
							$er .= $sk." ".LAN_CHECK_5;
							$this->error_count++;
						}
					}
	
					$style = ($er) ? "warning" : "success";
					$text .= "
							<td class='{$style}' style='width:50%'>
								<div class='smalltext'>
					";
					$text .= $bom_error . $utf_error;
					if(!$er && !$bom_error && !$utf_error)
					{
						$text .= LAN_OK;
					}
					else
					{
						$text .= $er."<br />";
						$this->error_count++;
					}
					$text .= "
								</div>
							</td>
					";
				}
				else
				{
				// file missing
					$text .= "
							<td>{$lnk}</td>
							<td><span class='error'>".LAN_CHECK_4."</span></td>
					";
					$this->error_count++;
				}
				
				// Leave in EDIT button for all entries - to allow re-translation of bad entries.
				$subpath = ($subdir!='') ? $subdir.$k : $k;
				$text .= "
							<td class='center'>
								".$frm->admin_button('but-corelan-'.str_replace(array('/', '\\'), '-', $subdir).$i, LAN_EDIT, 'edit', '', array('other' => "onclick=\"window.location='".e_SELF."?".$subpath."|".$_POST['language']."'\""))."
				";
				$text .= "
							</td>
						</tr>
				";
			}
			$i++;
		}
		$text .= "
					</tbody>
				</table>
			</fieldset>
		";
		
		return $text;
	}
	
	
	function get_lan_file_phrases($dir1,$dir2,$file1,$file2){
	
		$ret = array();
		$fname = $dir1.$file1;
		$type='orig';
	
		if(is_file($fname))
		{
			$data = file($fname);
			$ret=$ret + $this->fill_phrases_array($data,$type);
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
			$ret=$ret + $this->fill_phrases_array($data,$type);
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
		$fl = e107::getFile();

		$ret = array();
	
		if($lang_array = $fl->get_files($comp_dir, '\.php','standard',$depth)){
			sort($lang_array);
		}
	
		$regexp = (strpos($comp_dir,e_LANGUAGEDIR) !== FALSE) ? "#.php#" : "#".$lang."#";
	
		foreach($lang_array as $f)
		{
			if(preg_match($regexp,$f['path'].$f['fname']) && is_file($f['path'].$f['fname']))
			{
				$data = file($f['path'].$f['fname']);
				$relpath = str_replace($comp_dir,"",$f['path']);
				if(substr($data[0],0,5) != "<?php")
				{
					$key = str_replace(".php","",$relpath.$f['fname']);
					$ret['bom'][$key] = $f['fname'];
				}
				if($f['path'].$f['fname'] == e_LANGUAGEDIR.$lang."/".$lang.".php")
				{
					$f['fname'] = "English.php";  // change the key for the main language file.
				}
	
				if($f['path'].$f['fname'] == e_LANGUAGEDIR.$lang."/".$lang."_custom.php")
				{
					$f['fname'] = "English_custom.php";  // change the key for the main language file.
				}
	
				$ret=$ret + $this->fill_phrases_array($data,$relpath.$f['fname']);
	
			}
		}
	
		return $ret;
	
	}
	
	// for plugins and themes - checks what kind of language files directory structure we have
	function check_lanfiles($mode, $comp_name, $base_lan="English", $target_lan)
	{
		global $frm;
		
		$folder['P'] = e_PLUGIN.$comp_name;
		$folder['T'] = e_THEME.$comp_name;
		$comp_dir = $folder[$mode];
	
		$baselang = $this->get_comp_lan_phrases($comp_dir."/languages/","English",1);
		$check = $this->get_comp_lan_phrases($comp_dir."/languages/",$target_lan,1);
	
		$text = "";
		$keys = array_keys($baselang);
		sort($keys);
	
		$i = 0;
		foreach($keys as $k)
		{
			$lnk = $k;
			//echo "klucz ".$k."<br />";
			$k_check = str_replace("English",$target_lan,$k);
			$text .= "
				<tr>
			";
			if(array_key_exists($k_check,$check))
			{
				$text .= "
						<td>".$comp_name."</td>
						<td>".str_replace("English/","",$lnk)."</td>
				";
	
				$subkeys = array_keys($baselang[$k]);
				$er = "";
				$utf_error = "";
	
				$bomkey = str_replace(".php","",$k_check);
				$bom_error = ($check['bom'][$bomkey]) ? "<span class='error'><em>".LAN_CHECK_15."</em></span><br />" : ""; // illegal chars
	
				foreach($subkeys as $sk)
				{
					if($utf_error == "" && !$this->is_utf8($check[$k_check][$sk]))
					{
						$utf_error = "<span class='error'><em>".LAN_CHECK_19."</em></span><br />";
					}
	
					if(!array_key_exists($sk,$check[$k_check]) || (trim($check[$k_check][$sk]) == "" && $baselang[$k][$sk] != ""))
					{
						$er .= ($er) ? "<br />" : "";
						$er .= $sk." ".LAN_CHECK_5;
						$this->error_count++;
					}
				}
	
				$style = ($er) ? "warning" : "success";
				$text .= "
					<td class='{$style}' style='width:50%'>
						<div class='smalltext'>
				";
				$text .= $bom_error . $utf_error;
				$text .= (!$er && !$bom_error && !$utf_error) ? LAN_OK : $er."<br />";
				$text .= "
						</div>
					</td>
				";
			}
			else
			{
				$text .= "
					<td>".$comp_name."</td>
					<td>".str_replace("English/","",$lnk)."</td>
					<td><span class='error' style='cursor:pointer' title='".str_replace("English",$target_lan,$lnk)."'>".LAN_CHECK_4."</span></td>
				";
				$this->error_count++;
			}
	
			$text .="
					<td class='center'>
						".$frm->admin_button('but-corelan-'.str_replace(array('/', '\\'), '-', $comp_dir).$i, LAN_EDIT, 'edit', '', array('other'=> "onclick=\"window.location='".e_SELF."?".$comp_dir."/languages/".$lnk."|".$target_lan."|file'\""))."
			";
			$text .="
					</td>
				</tr>
			";
			$i++;
		}
	
		return $text;
	}
	
	function edit_lanfiles($dir1,$dir2,$f1,$f2){
		global $e107, $emessage, $lan;
	
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
		$trans = $this->get_lan_file_phrases($dir1,$dir2,$f1,$f2);
		$keys = array_keys($trans);
		sort($keys);
	
		$text = "
			<form method='post' action='".e_SELF."?".e_QUERY."' id='transform'>
				<fieldset id='core-lancheck-edit'>
					<legend>".LAN_CHECK_3." ".str_replace(array(e_PLUGIN, e_LANGUAGEDIR), array(e_PLUGIN_ABS, e_LANGUAGEDIR_ABS), $dir2)."{$f2} -&gt; {$lan}</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='3'>
							<col style='width: 20%'></col>
							<col style='width: 40%'></col>
							<col style='width: 40%'></col>
						</colgroup>
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th>".LAN_CHECK_16."</th>
								<th class='last'>Translate to ".$lan."</th>
							</tr>
						</thead>
						<tbody>
		";
	
		$subkeys = array_keys($trans['orig']);
		foreach($subkeys as $sk)
		{
			$rowamount = round(strlen($trans['orig'][$sk])/34)+1;
			$hglt1=""; $hglt2="";
			if ($trans['tran'][$sk] == "" && $trans['orig'][$sk]!="") {
				$hglt1="<span class='error'>";
				$hglt2="</span>";
			}
			$text .= "
							<tr>
								<td>".$hglt1.htmlentities($sk).$hglt2."</td>
								<td>".htmlentities(str_replace("ndef++", "", $trans['orig'][$sk])) ."</td>
								<td>
									".(($writable) ? "<textarea  class='tbox' name='newlang[]' rows='{$rowamount}' cols='45'>" : "")
									.str_replace("ndef++","",$trans['tran'][$sk])
									.(($writable) ? "</textarea>" : "")."
			";
			//echo "orig --> ".$trans['orig'][$sk]."<br />";
			if (strpos($trans['orig'][$sk],"ndef++") !== False)
			{
				//echo "+orig --> ".$trans['orig'][$sk]." <> ".strpos($trans['orig'][$sk],"ndef++")."<br />";
				$text .= "
									<input type='hidden' name='newdef[]' value='ndef++".$sk."' />
				";
			}
			else
			{
				$text .= "
									<input type='hidden' name='newdef[]' value='".$sk."' />
				";
			}
			$text .="
								</td>
							</tr>
			";
		}
		$text .= "
						</tbody>
					</table>
		";
		//Check if directory is writable
		if($writable)
		{
			//FIXME  place of LAN_SAVE
			$text .="
					<div class='buttons-bar center'>
						<button class='update' type='submit' name='submit' value='sprintf'><span>".LAN_SAVE." ".str_replace($dir2, "", $root_file)."</span></button>
						".(($root_file) ? "<input type='hidden' name='root' value='".$root_file."' />" : "")."
					</div>
			";
		}
	
		$text .= "
				</fieldset>
			</form>
		";
	
		$text .= "
			<form method='post' action='".e_SELF."' id='select_lang'>
				<div style='text-align:center'>
					".((!$writable) ? $dir2.$f2.LAN_NOTWRITABLE : "")."
					<br />
					<button class='submit' type='submit' name='language_sel' value='no-value'><span>".LAN_BACK."</span></button>
					<input type='hidden' name='language' value='$lan' />
				</div>
			</form>
		";
	
		$e107->ns->tablerender(LAN_CHECK_PAGE_TITLE.' - '.LAN_CHECK_24, $text);
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
	//@TODO: always TRUE
	//	if(strtolower(CHARSET) != "utf-8" || $str == "")
		{
			return TRUE;
		}
	
		return (preg_match('/^.{1}/us',$str,$ar) == 1);
	}
	
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
	$var['main']['link'] = e_ADMIN_ABS."language.php";

	if(isset($pref['multilanguage']) && $pref['multilanguage']){
		$var['db']['text'] = LANG_LAN_03;
		$var['db']['link'] = e_ADMIN_ABS."language.php?db";
	}

	$var['tools']['text'] = ADLAN_CL_6;
	$var['tools']['link'] = e_ADMIN_ABS."language.php?tools";


	e_admin_menu(ADLAN_132, $action, $var);
}

	$ns -> tablerender(LAN_CHECK_PAGE_TITLE.' - '.LAN_CHECK_1, LAN_CHECK_26);
	require_once(e_ADMIN."footer.php");
