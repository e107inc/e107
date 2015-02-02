<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Languages

 */
require_once ("../class2.php");
if (!getperms('L'))
{
	header("location:".e_BASE."index.php");
	exit;
}
//include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);
e107::coreLan('language', true);

$e_sub_cat = 'language';
require_once ("auth.php");


$frm = e107::getForm();
$mes = e107::getMessage();

include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_lancheck.php");
require_once(e_ADMIN."lancheck.php");
require_once(e_HANDLER."language_class.php");

// $ln = new language;
$ln = $lng;

$lck = new lancheck;

$tabs = table_list(); // array("news","content","links");
$lanlist = e107::getLanguage()->installed();// Bugfix - don't use e_LANLIST as it's cached (SESSION)
$message = '';

if (e_QUERY)
{
	$tmp = explode('.', e_QUERY);
	$action = varset($tmp[0]);
	$sub_action = varset($tmp[1]);
	$id = varset($tmp[2]);
	unset($tmp);
}
elseif(!getperms('0'))
{
	$action = 'tools';
}


if (isset($_POST['submit_prefs']) && isset($_POST['mainsitelanguage']) && getperms('0'))
{
	unset($temp);
	$changes = array();
	$temp['multilanguage'] = $_POST['multilanguage'];
	$temp['multilanguage_subdomain'] = $_POST['multilanguage_subdomain'];
	$temp['multilanguage_domain'] = $_POST['multilanguage_domain'];
	$temp['sitelanguage'] = $_POST['mainsitelanguage'];
	$temp['adminlanguage'] = $_POST['mainadminlanguage'];
	$temp['noLanguageSubs'] = $_POST['noLanguageSubs'];
		
	e107::getConfig()->setPref($temp)->save(true);
	
	e107::getSession()->clear('e_language');

}
// ----------------- delete tables ---------------------------------------------
if (isset($_POST['del_existing']) && $_POST['lang_choices'] && getperms('0'))
{
	$lang = strtolower($_POST['lang_choices']);
	foreach ($tabs as $del_table)
	{
		if ($sql->db_Table_exists($lang."_".$del_table,TRUE))
		{
			//	echo $del_table." exists<br />";
			$qry = "DROP TABLE ".$mySQLprefix."lan_".$lang."_".$del_table;
			if (mysql_query($qry))
			{
				$msg = $tp->lanVars(LANG_LAN_100, $_POST['lang_choices'].' '.$del_table);
				$message .= $msg.'[!br!]'; 
				$mes->addSuccess($msg);
			}
			else
			{
				$msg = $tp->lanVars(LANG_LAN_101, $_POST['lang_choices'].' '.$del_table);
				$message .= $msg.'[!br!]';  
				$mes->addWarning($msg);
			}
		}
	}
	e107::getLog()->add('LANG_02', $message.'[!br!]', E_LOG_INFORMATIVE, '');
	$sql->db_ResetTableList();

	if ($action == 'modify')
		$action = 'db';//FIX - force db action when deleting all lan tables
}
// ----------create tables -----------------------------------------------------
if (isset($_POST['create_tables']) && $_POST['language'])
{
	$table_to_copy = array();
	$lang_to_create = array();
	foreach ($tabs as $value)
	{
		$lang = strtolower($_POST['language']);
		if (isset($_POST[$value]))
		{
			$copdata = ($_POST['copydata_'.$value]) ? 1 : 0;
			if ($sql->db_CopyTable($value, "lan_".$lang."_".$value, $_POST['drop'], $copdata))
			{
				$msg = $tp->lanVars(LANG_LAN_103,  $_POST['language'].' '.$value); 
				$message .= $msg . '[!br!]'; // Used in admin log. 
				$mes->addSuccess($msg);
			}
			else
			{
				if (!$_POST['drop'])
				{
					$msg = $tp->lanVars(LANG_LAN_00, $_POST['language'].' '.$value);
					$message .= $msg . '[!br!]';
					$mes->addWarning($msg);
				}
				else
				{
					$msg = $tp->lanVars(LANG_LAN_01, $_POST['language'].' '.$value);
					$message .= $msg . '[!br!]';
					$mes->addWarning($msg);
				}
			}
		}
		elseif ($sql->db_Table_exists($value,$_POST['language']))
		{
			if ($_POST['remove'])
			{
				// Remove table.
				if (mysql_query("DROP TABLE ".$mySQLprefix."lan_".$lang."_".$value))
				{
					$message .= $_POST['language'].' '.$value.' '.LAN_DELETED.'[!br!]'; // can be removed?
					$mes->addSuccess($_POST['language'].' '.$value.' '.LAN_DELETED);
				}
				else
				{
					$msg = $tp->lanVars(LANG_LAN_02, $_POST['language'].' '.$value);
					$message .= $msg . '[!br!]';
					$mes->addWarning($msg);
				}
			}
			else
			{
				// leave table. LANG_LAN_104
			
				$msg = $tp->lanVars(LANG_LAN_104, $_POST['language'].' '.$value);
				$message .= $msg . '[!br!]';
				$mes->addInfo($msg);
			}
		}
	}
	e107::getLog()->add('LANG_03', $message, E_LOG_INFORMATIVE, '');
	$sql->db_ResetTableList();
}
/*
 if(isset($message) && $message)
 {
 $ns->tablerender(LAN_OK, $message);
 }
 */
 




 
 
 
 
unset($text);
if (!e_QUERY || $action == 'main' && !$_POST['language'] && !$_POST['edit_existing'])
{
	multilang_prefs();
}
if (varset($action) == 'db')
{
	multilang_db();
}


/*

if (varset($_POST['ziplang']) && varset($_POST['language']))
{
	if(varset($pref['lancheck'][$_POST['language']]) == 1)
	{
		$text = zip_up_lang($_POST['language']);
		e107::getLog()->add('LANG_04', $_POST['language'], E_LOG_INFORMATIVE, '');
		$mes->addInfo(LANG_LAN_25.': '.$text);	
	}
	else
	{
		$mes->addWarning(LANG_LAN_36);		
	}
}
*/


// imported from e107 v1
if (varset($_POST['ziplang']))
{
	$certVal = isset($_POST['contribute_pack']) ? 1 : 0;
	
	if(!varset($_COOKIE['e107_certified']))
	{
		cookie('e107_certified',$certVal,(time() + 3600 * 24 * 30));	
	}
	else
	{
		$_COOKIE['e107_certified'] = $certVal; 	
	}
			
	$_POST['language'] = key($_POST['ziplang']);
	
	// If no session data, scan before zipping. 	
	if(!isset($_SESSION['lancheck'][$_POST['language']]['total']) || $_SESSION['lancheck'][$_POST['language']]['total']!='0')
	{
		$_POST['language_sel'] = $_POST['ziplang'];	
		$lck->check_all('norender');
		unset($_POST['language_sel']);
	}
	
	$status = zip_up_lang($_POST['language']);
	
	if($status['error']==FALSE)
	{	
		$text = $status['message']."<br />";
		$text .= share($status['file']); 
		$mes->addSuccess($text);
		//$ns->tablerender(LAN_CREATED, $text );
		
	}
	else
	{
		$mes->addError($status['message']);
		//$ns->tablerender(LAN_CREATED_FAILED, $status['message']);
	}
	
	echo $mes->render();
}

function find_locale($language)
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
 * Share Language File
 * @param object $newfile
 * Usage of e107 is granted to you provided that this function is not modified or removed in any way. 
 * @return 
 */
function share($newfile)
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




$debug = "<br />f=".$_GET['f'];
$debug .= "<br />mode=".$_GET['mode'];
$debug .= "<br />lan=".$_GET['lan'];
// $ns->tablerender("Debug",$debug);

 $rendered = $lck->init(); // Lancheck functions. 


if (varset($action) == "tools" && !$rendered)
{
	show_tools();
	if($languagePacks = available_langpacks() )
	{
		e107::getRender()->tablerender(LANG_LAN_34,$languagePacks );	
	}	
}


	function findIncludedFiles($script,$reverse=false)
	{
		$mes = e107::getMessage();
		
		$data = file_get_contents($script);
		
		if(strpos($data, 'e_admin_dispatcher')!==false)
		{
			$reverse = false;	
		}
		
		$dir = dirname($script);
		
		$dir = str_replace("/includes","",$dir);
		$plugin = basename($dir);
		
		if(strpos($script,'admin')!==false || strpos($script,'includes')!==false) // Admin Language files. 
		{
			
			$newLangs = array(
				0 		=>  $dir."/languages/English/English_admin_".$plugin.".php",
				1 		=>  $dir."/languages/English_admin_".$plugin.".php",
				2 		=>  $dir."/languages/English_admin.php",
				3 		=>  $dir."/languages/English/English_admin.php"
			);
		}
		else 
		{
			$newLangs = array(
				0 		=>  $dir."/languages/English/English_".$plugin.".php",
				1 		=>  $dir."/languages/English_admin_".$plugin.".php",
				2 		=>  $dir."/languages/English_front.php",
				3 		=>  $dir."/languages/English/English_front.php",
				4 		=>  $dir."/languages/English_front.php",
				5 		=>  $dir."/languages/English/English_front.php"
			);
		}
	//	if(strpos($data, 'e_admin_dispatcher')!==false)
		{
			foreach($newLangs as $path)
			{
				if(file_exists($path) && $reverse == false)
				{
					return $path; 	
				}	
			}
		}


		
		preg_match_all("/.*(include_lan|require_once|include|include_once) ?\((.*e_LANGUAGE.*?\.php)/i",$data,$match);
		
		$srch = array(" ",'e_PLUGIN.', 'e_LANGUAGEDIR', '.e_LANGUAGE.', "'", '"', "'.");
		$repl = array("", e_PLUGIN, e_LANGUAGEDIR, "English", "", "", "");

		foreach($match[2] as $lanFile)
		{
			$arrt = str_replace($srch,$repl,$lanFile);	
		//	if(strpos($arrt,'admin'))
			{
				//return $arrt;	
				$arr[] = $arrt;
			}
		}
		
		return implode(",",$arr);
		
			
	//	return $arr[0];
	}


if(vartrue($_POST['disabled-unused']) && vartrue($_POST['disable-unused-lanfile']))
{
	$mes = e107::getMessage();
	
	$data = file_get_contents($_POST['disable-unused-lanfile']);
	
	$new = disableUnused($data);
	if(file_put_contents($_POST['disable-unused-lanfile'],$new))
	{
		$mes->addSuccess("Overwriting ".$_POST['disable-unused-lanfile']);
	}
	else 
	{
		$mes->addError("Couldn't overwrite ".$_POST['disable-unused-lanfile']);	
	}
	
	$ns->tablerender("Processed".SEP.$_POST['disable-unused-lanfile'],$mes->render()."<pre>".htmlentities($new)."</pre>");
	
}

function disableUnused($data)
{
	$data = str_replace("2008-2010","2008-2013", $data);
	$data = str_replace(' * $URL$
 * $Revision$
 * $Id$
 * $Author$',"",$data);	// TODO FIXME ?

	$tmp = explode("\n",$data);
	foreach($tmp as $line)
	{
		$ret = getDefined($line);	
		$newline[] = (in_array($ret['define'],$_SESSION['language-tools-unused']) && substr($line,0,2) !='//') ? "// ".$line : $line;	
	}
	
	return implode("\n",$newline);
	
}


if(varset($_POST['searchDeprecated']) && varset($_POST['deprecatedLans']))
{
	$mes = e107::getMessage();

	// $lanfile = $_POST['deprecatedLans'];
	$script = $_POST['deprecatedLans'];

	if(strpos($script,e_ADMIN)!==false) // CORE
	{
		$mes->addDebug("Mode: Core Admin Calculated");
		//$scriptname = str_replace("lan_","",basename($lanfile));
		$lanfile = e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_".basename($script);		
	}
	else  // Plugin 
	{
		$mes->addDebug("Mode: Search Plugins");
		$lanfile = findIncludedFiles($script,vartrue($_POST['deprecatedLansReverse']));		
	}	
	
	if(!is_readable($script))
	{
		$mes->addError("Not Readable: ".$script);
		// $script = $scriptname; // matching files. lan_xxxx.php and xxxx.php
	}
	
	$found = findIncludedFiles($script,vartrue($_POST['deprecatedLansReverse']));
	
//	print_a($found);
	
	// Exceptions - same language loaded by several scripts. 
	if($lanfile == e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_e107_update.php")
	{
		$script = e_ADMIN."update_routines.php,".e_ADMIN."e107_update.php";
	}
	
	if(vartrue($_POST['deprecatedLanFile'])) //override. 
	{
		$lanfile = $_POST['deprecatedLanFile'];	
	}

	if($res = unused($lanfile, $script, vartrue($_POST['deprecatedLansReverse'])))
	{
		$ns -> tablerender($res['caption'],$mes->render(). $res['text']);
	} 		


	

} 










//FIX - create or edit check
if (isset($_POST['create_edit_existing']))
	$_POST['edit_existing'] = true;
// Grab Language configuration. ---
if (isset($_POST['edit_existing']))
{
	//XXX - JS ok with the current functionality?
	$text .= "
	<form method='post' action='".e_SELF."?db'>
		<fieldset id='core-language-edit'>
			<legend class='e-hideme'>".$_POST['lang_choices']."</legend>
			<table class='table adminlist'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
	";
	foreach ($tabs as $table_name)
	{
		$installed = 'lan_'.strtolower($_POST['lang_choices'])."_".$table_name;
		if (stristr($_POST['lang_choices'], $installed) === FALSE)
		{
			$text .= "
					<tr>
						<td>".ucfirst(str_replace("_", " ", $table_name))."</td>
						<td>
							<div class='auto-toggle-area f-left e-pointer'>
			";
			$selected = ($sql->db_Table_exists($table_name,$_POST['lang_choices'])) ? " checked='checked'" : "";
			$text .= "
								<input type='checkbox' class='checkbox' id='language-action-{$table_name}' name='{$table_name}' value='1'{$selected} onclick=\"if(document.getElementById('language-action-{$table_name}').checked){document.getElementById('language-datacopy-{$table_name}').style.display = '';}\" />
							</div>

							<div class='f-left'>
								<span id='language-datacopy-{$table_name}' class='e-hideme e-pointer'>
									<input type='checkbox' class='checkbox' name='copydata_{$table_name}' id='copydata-{$table_name}' value='1' />
									&nbsp;&nbsp;<label for='copydata-{$table_name}'>".LANG_LAN_15."</label>
								</span>
							</div>
						</td>
					</tr>
			";
		}
	}
	// ===========================================================================
	// Drop tables ? isset()
	if (varset($_POST['create_edit_existing']))
	{
		$baction = 'create';
		$bcaption = LANG_LAN_06;
	}
	else
	{
		$baction = 'update';
		$bcaption = LAN_UPDATE;
	}
	$text .= "
					<tr>
						<td><strong>".LANG_LAN_07."</strong></td>
						<td>
							".$frm->checkbox('drop', 1)."
							<div class='smalltext field-help'>".$frm->label(LANG_LAN_08, 'drop', 1)."</div>
						</td>
					</tr>
					<tr>
						<td><strong>".LAN_CONFDELETE."</strong></td>
						<td>
							".$frm->checkbox('remove', 1)."
							<div class='smalltext field-help'>".$frm->label(LANG_LAN_11, 'remove', 1)."</div>
						</td>
					</tr>
				</tbody>
			</table>
			<div class='buttons-bar center'>
				<input type='hidden' name='language' value='{$_POST['lang_choices']}' />
				".$frm->admin_button('create_tables','no-value',$baction,$bcaption)."
			</div>
		</fieldset>
	</form>
	";
	$ns->tablerender($_POST['lang_choices'], $mes->render().$text);
}
require_once (e_ADMIN."footer.php");
// ---------------------------------------------------------------------------


function multilang_prefs()
{
	if(!getperms('0'))
	{
		return;
	}
	
	global $lanlist;
	$pref = e107::getPref();
	$mes = e107::getMessage();
	$frm = e107::getForm();
	
	//XXX Remove later. 
	// Enable only for developers - SetEnv E_ENVIRONMENT develop
//	if(!isset($_SERVER['E_DEV_LANGUAGE']) || $_SERVER['E_DEV_LANGUAGE'] !== 'true') 
//	{
	//	$lanlist = array('English'); 
	//	$mes->addInfo("Alpha version currently supports only the English language. After most features are stable and English terms are optimized - translation will be possible.");
//	}
	
	$text = "
	<form method='post' action='".e_SELF."' id='linkform'>
		<fieldset id='core-language-settings'>
			<legend class='e-hideme'>".LANG_LAN_13."</legend>
			<table class='table adminform'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td>".LANG_LAN_14.": </td>
						<td>";

						$sellan = preg_replace("/lan_*.php/i", "", $pref['sitelanguage']);
					
						$text .= $frm->select('mainsitelanguage',$lanlist,$sellan,"useValues=1");
						$text .= "
						</td>
					</tr>";
					
					
				//	if(isset($_SERVER['E_DEV_LANGUAGE']) &&  $_SERVER['E_DEV_LANGUAGE'] === 'true') 
					{
					
						$text .= "	
						<tr>
							<td>".LANG_LAN_50.": </td>
							<td>";
	
							$sellan = preg_replace("/lan_*.php/i", "", $pref['adminlanguage']);
						
							$text .= $frm->select('mainadminlanguage',$lanlist,$sellan,array("useValues"=>1,"default" => LANG_LAN_14));
							$text .= "
							</td>
						</tr>";
					
					}



					$text .= "
					<tr>
						<td>".LANG_LAN_12.": </td>
						<td>
							<div class='auto-toggle-area autocheck'>";
						$checked = ($pref['multilanguage'] == 1) ? " checked='checked'" : "";
						$text .= "
													<input class='checkbox' type='checkbox' name='multilanguage' value='1'{$checked} />
							</div>
						</td>
					</tr>
					<tr>
						<td>".LANG_LAN_26.":</td>
						<td>
							<div class='auto-toggle-area autocheck'>\n";
					$checked = ($pref['noLanguageSubs'] == 1) ? " checked='checked'" : "";
					$text .= "
								<input class='checkbox' type='checkbox' name='noLanguageSubs' value='1'{$checked} />
								<div class='smalltext field-help'>".LANG_LAN_27."</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							".LANG_LAN_18."
							<small>".LANG_LAN_19."</small>
						</td>
						<td>
							<textarea name='multilanguage_subdomain' rows='5' cols='15'>{$pref['multilanguage_subdomain']}</textarea>
							<div class='smalltext field-help'>".LANG_LAN_20."</div>
						</td>
						
					</tr>";
					
					
					$opt = "";
					$langs = explode(",",e_LANLIST);
					foreach($langs as $val)
					{
						if($val != $pref['sitelanguage'])
						{
							$opt .= "<tr><td class='middle' style='width:5%'>".$val."</td><td class='left inline-text'><input type='text' name='multilanguage_domain[".$val."]' value=\"".$pref['multilanguage_domain'][$val]."\" /></td></tr>";	
						}		
					}
					
					if($opt)
					{
						//TODO LANs and class2.php check. 
						$text .= "	
						<tr>
							<td>
							Language by Domain Name
							<div class='label-note'>Domain determines the site's language. Enter domain without the 'www.'</div>
							</td>
							<td><table style='margin-left:0px;width:400px'>".$opt."</table></td>
						</tr>";
					}
					
					$text .= "
				</tbody>
			</table>
			<div class='buttons-bar center'>".
				$frm->admin_button('submit_prefs','no-value','update',LAN_SAVE)."
			</div>
		</fieldset>
	</form>\n";
	
	e107::getRender()->tablerender(ADLAN_132.SEP.LAN_PREFS, $mes->render().$text); // "Language Preferences";
}

// ----------------------------------------------------------------------------


function table_list()
{
	// grab default language lists.
		
	$exclude = array();
	$exclude[] = "banlist";
	$exclude[] = "banner";
	$exclude[] = "cache";
	$exclude[] = "core";
	$exclude[] = "online";
	$exclude[] = "parser";
	$exclude[] = "plugin";
	$exclude[] = "user";
	$exclude[] = "upload";
	$exclude[] = "userclass_classes";
	$exclude[] = "rbinary";
	$exclude[] = "session";
	$exclude[] = "tmp";
	$exclude[] = "flood";
	$exclude[] = "stat_info";
	$exclude[] = "stat_last";
	$exclude[] = "submit_news";
	$exclude[] = "rate";
	$exclude[] = "stat_counter";
	$exclude[] = "user_extended";
	$exclude[] = "user_extended_struct";
	$exclude[] = "pm_messages";
	$exclude[] = "pm_blocks";
	
	$tables = e107::getDb()->db_TableList('nolan'); // db table list without language tables. 
	return array_diff($tables,$exclude);

}
// ------------- render form ---------------------------------------------------


function multilang_db()
{
	if(!getperms('0'))
	{
		return "Access Denied";
	}
	
	
	global $lanlist, $tabs;
	
	$sql = e107::getDb();
	$frm = e107::getForm();
	$tp = e107::getParser();
	$mes = e107::getMessage();
	$pref = e107::getPref();
	
	if (isset($pref['multilanguage']) && $pref['multilanguage'])
	{
		// Choose Language to Edit:
		$text = "
			<fieldset id='core-language-list'>
				<legend class='e-hideme'>".LANG_LAN_16."</legend>
				<table class='table adminlist'>
					<colgroup>
						<col style='width:20%' />
						<col style='width:60%' />
						<col style='width:20%' />
					</colgroup>
					<thead>
						<tr>
							<th>".ADLAN_132."</th>
							<th>".LANG_LAN_03."</th>
							<th class='last'>".LAN_OPTIONS."</th>
						</tr>
					</thead>
					<tbody>
		";
		sort($lanlist);
		
		foreach ($lanlist as $e_language)
		{
			$installed = array();
			
			if(strtolower($e_language) == $pref['sitelanguage'])
			{
				$e_language = "";
			}
			$text .= "<tr><td>{$e_language}</td><td>";
			
			foreach ($tabs as $tab_name)
			{
				if ($e_language != $pref['sitelanguage'] && $sql->db_Table_exists($tab_name,$e_language))
				{
					$installed[] = $tab_name;
				}
			}
			
			$text .= implode(", ",$installed);
			
			if ($e_language == $pref['sitelanguage'])
			{
				$text .= "<span>".LANG_LAN_17."</span>";
			}
			else
			{
				$text .= (!count($installed)) ? "<span>".LANG_LAN_05."</span>" : "";
			}
			
			$text .= "</td>\n";
			$text .= "<td>
				<form id='core-language-form-".str_replace(" ", "-", $e_language)."' action='".e_SELF."?modify' method='post'>\n";
			$text .= "
								<div>
			";
			if (count($installed))
			{
			
				$text .= "<button class='btn btn-primary edit' type='submit' name='edit_existing' value='no-value'><span>".LAN_EDIT."</span></button>
						<button class='btn btn-danger delete' type='submit' name='del_existing' value='no-value' title='".$tp->lanVars(LANG_LAN_105, $e_language).' '.LAN_JSCONFIRM."'><span>".LAN_DELETE."</span></button>";
			}
			elseif ($e_language != $pref['sitelanguage'])
			{
				// $text .= "<button class='create' type='submit' name='create_edit_existing' value='no-value'><span>".LAN_CREATE."</span></button>";
				$text .= $frm->admin_button('create_edit_existing','no-value','create',LAN_CREATE);
			}
			$text .= "<input type='hidden' name='lang_choices' value='".$e_language."' />
								</div>
								</form>
							</td>
						</tr>
			";
		}
		$text .= "
					</tbody>
				</table>
			</fieldset>
		";
		
		e107::getRender()->tablerender(ADLAN_132.SEP.LANG_LAN_16, $mes->render().$text); // Languages -> Tables
	}
}
// ----------------------------------------------------------------------------


function getLanList()
{
	global $ln;
	
	$lst = explode(",",e_LANLIST);

	$list = array();
	
	foreach($lst as $lang)
	{
		if($ln->isValid($lang))
		{
			$list[] = $lang;
		}
	}
	
	sort($list);
	return $list;	
}



/**
 * List the installed language packs. 
 * @return 
 */
function show_packs()
{
	$frm = e107::getForm();
	$ns = e107::getRender();
	$tp = e107::getParser();
	
	if(is_readable(e_ADMIN."ver.php"))
	{
		include(e_ADMIN."ver.php");
		list($ver, $tmp) = explode(" ", $e107info['e107_version']);
	}
		
	$lans = getLanList();
	
	$release_diz = defined("LANG_LAN_30") ? LANG_LAN_30 : "Release Date";
	$compat_diz = defined("LANG_LAN_31") ?  LANG_LAN_31 : "Compatibility";
	$lan_pleasewait = (defsettrue('LAN_PLEASEWAIT')) ?  $tp->toJS(LAN_PLEASEWAIT) : "Please Wait";
	$lan_displayerrors = (defsettrue('LANG_LAN_33')) ?  LANG_LAN_33 : "Display only errors during verification";
	
	
	$text = "<form id='lancheck' method='post' action='".e_SELF."?tools'>
			<table class='table table-striped'>";
	$text .= "<thead>
		<tr>
		<th>".ADLAN_132."</th>
		<th>".$release_diz."</th>		
		<th>".$compat_diz."</th>
		<th>".LAN_STATUS."</td>
		<th style='width:25%;white-space:nowrap'>".LAN_OPTIONS."</td>
		</tr>
		</thead>
		";
	
	require_once(e_HANDLER."xml_class.php");
	$xm = new XMLParse();
	
	foreach($lans as $language)
	{
		if($language == "English")
		{
			continue;
		}
		$metaFile = e_LANGUAGEDIR.$language."/".$language.".xml";
		
		if(is_readable($metaFile))
		{
			$rawData = file_get_contents($metaFile);
			if($rawData)
			{
				$array = $xm->parse($rawData);
				$value = $array['e107Language']['attributes'];	
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
		
		$errFound = (isset($_SESSION['lancheck'][$language]['total']) && $_SESSION['lancheck'][$language]['total'] > 0) ?  TRUE : FALSE;
		
						
		$text .= "<tr>
			<td >".$language."</td>
			<td>".$value['date']."</td>
			<td>".$value['compatibility']."</td>
			<td>".($ver != $value['compatibility'] || $errFound ? ADMIN_FALSE_ICON : ADMIN_TRUE_ICON )."</td>
			<td><input type='submit' name='language_sel[{$language}]' value=\"".LAN_CHECK_2."\" class='btn btn-primary' />
			<input type='submit' name='ziplang[{$language}]' value=\"".LANG_LAN_23."\" class='button' onclick=\"this.value = '".$lan_pleasewait."'\" /></td>	
			</tr>";
		}
		
		$text .= "
		
		</tr></table>";
		
		$text .= "<table class='table table-striped'>";
		
		$text .= "<thead><tr><th>".LAN_OPTIONS."</th></tr></thead><tbody>";
		
		$srch = array("[","]");
		$repl = array("<a rel='external' href='http://e107.org/content/About-Us:The-Team#translation-team'>","</a>");
		$diz = (defsettrue("LANG_LAN_28")) ? LANG_LAN_28 : "Check this box if you're an [e107 certified translator].";
	
		$checked = varset($_COOKIE['e107_certified']) == 1 ? true : false;
		
		$text .= "<tr><td>";
		$text .= $frm->checkbox('contribute_pack',1,$checked,array('label'=>str_replace($srch,$repl,$diz)));
		 ;
		
		
		$text .= "</td>
		</tr>
		<tr>
		<td>";
		
		$echecked = varset($_SESSION['lancheck-errors-only']) == 1 ? true : false;		
		$text .= $frm->checkbox('errorsonly',1,$echecked,array('label'=>$lan_displayerrors));
		$text .= " </td>
		
		</tr>";
		
//		$text .= "
//		<tr>
//		<td>".$frm->checkbox('non-core-plugs-themes',1,$echecked,array('label'=>$lan_displayerrors))."</td>
//		</tr>
//		";
		
		$text .= "</tbody></table>";
		
		
		$text .= "</form>";
	
	$text .= "<div class='smalltext center' style='padding-top:50px'>".LANG_LAN_AGR."</div>";	
	$ns->tablerender(ADLAN_132.SEP.LANG_LAN_32, $text);		
	return;
		
}





function show_tools()
{
	$frm = e107::getForm();
	$mes = e107::getMessage();
	
	include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_lancheck.php");
	
	show_packs();
	
	return; 
	
	if(!vartrue($_SERVER['E_DEV']))
	{
		return; 
	}
	
	
	/*
	$text = "
		<form id='core-language-lancheck-form' method='post' action='".e_SELF."?tools'>
			<fieldset id='core-language-lancheck'>
				<legend class='e-hideme'>".LAN_CHECK_1."</legend>
				<table class='table adminform'>
					<colgroup>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>
						<tr>
							<td>".LAN_CHECK_1."</td>
							<td class='form-inline'>
								<select name='language'>
									<option value=''>".LAN_SELECT."</option>";
								$languages = explode(",", e_LANLIST);
								sort($languages);
								foreach ($languages as $lang)
								{
									if ($lang != "English")
									{
										$text .= "
																<option value='{$lang}' >{$lang}</option>
										";
									}
								}
								$text .= "</select>".
								$frm->admin_button('language_sel','no-value','other',LAN_CHECK_2)."
							</td>
						</tr>
					</tbody>
				</table>
			</fieldset>
		</form>";
		*/
		$text = "";
		
		$text .= "
		<form id='ziplang' method='post' action='".e_SELF."?tools'>
			<fieldset id='core-language-package'>
				<legend class='e-hideme'>".LANG_LAN_23."</legend>
				<table class='table adminform'>
					<colgroup>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>";
					
				/*	
				$text .= "
						<tr>
							<td>".LANG_LAN_23."</td>
							<td class='form-inline'>
								<select name='language'>
									<option value=''>".LAN_SELECT."</option>";
								$languages = explode(",", e_LANLIST);
								sort($languages);
								foreach ($languages as $lang)
								{
									if ($lang != "English")
									{
										$text .= "
																<option value='{$lang}' >{$lang}</option>
										";
									}
								}
								$text .= "
								</select>
								".$frm->admin_button('ziplang','no-value','other',LANG_LAN_24)."
								<input type='checkbox' name='contribute_pack' value='1' /> Check to share your language-pack with the e107 community.
							</td>
						</tr>";
				*/
			
						
						$fl = e107::getFile();
						$fl->mode = 'full';
							
						if(!$_SESSION['languageTools_lanFileList'])
						{
							
							$_SESSION['languageTools_lanFileList'] = $fl->get_files(e_BASE,'.*?(English|lan_).*?\.php$','standard',5);
						}
						
								
						$text .= "						
						<tr>
							<td>Search for Deprecated Lans</td>
							<td class='form-inline'>
								<select name='deprecatedLans'>
									<option value=''>Select Script...</option>";
									
									
									$omit = array('languages','\.png','\.gif','handlers');
									$lans = $fl->get_files(e_ADMIN,'.php','standard',0);
									$fl->setFileFilter(array("^e_"));
									$plugs = $fl->get_files(e_PLUGIN,'.*?/?.*?\.php',$omit,2);
							
									$exclude = array('lan_admin.php');	
															
									$srch = array(e_ADMIN,e_PLUGIN);
									
									$text .= "<optgroup label='Admin Area'>";
									foreach($lans as $script=>$lan)
									{								
										if(in_array(basename($lan),$exclude))
										{
											continue;
										}
										$selected = ($lan == varset($_POST['deprecatedLans'])) ? "selected='selected'" : "";
										$text .= "<option value='".$lan."' {$selected}>".str_replace($srch,"",$lan)."</option>\n";
									}
									
									$text .= "</optgroup>";
									
									$text .= "<optgroup label='Plugins'>";
									foreach($plugs as $script=>$lan)
									{								
										if(in_array(basename($lan),$exclude))
										{
											continue;
										}
										$selected = ($lan == varset($_POST['deprecatedLans'])) ? "selected='selected'" : "";
										$text .= "<option value='".$lan."' {$selected}>".str_replace($srch,"",$lan)."</option>\n";
									}
									
									$text .= "</optgroup>";
									
								
								$depOptions = array(
									1 => "Script > Lan File",
									0 => "Script < Lan File"
									
								);
									
								$text .= "
								</select> ".
								$frm->select('deprecatedLansReverse',$depOptions,$_POST['deprecatedLansReverse'],'class=select')." ";
								
								$search = array(e_PLUGIN,e_ADMIN,e_LANGUAGEDIR,e_THEME);
								$replace = array("Plugins ","Admin ","Core ","Themes ");
								
								
								$prev = 'Core';
								$text .= "<select name='deprecatedLanFile'>
								<option value=''>Auto-Detect</option>
								<optgroup label='CORE'>\n";
								
								foreach($_SESSION['languageTools_lanFileList'] as $val)
								{
									if(strstr($val,e_SYSTEM))
									{
										continue;	
									}
									
									
								 	$selected = ($val === $_POST['deprecatedLanFile']) ? "selected='selected'" : "";
									$diz 		= str_replace($search,$replace,$val);
									list($type,$label) = explode(" ",$diz);
									
									if($type !== $prev)
									{
										$text .= "</optgroup><optgroup label='".$type."'>\n";	
									}
									
									$text .= "<option value='".$val."' ".$selected.">".$label."</option>\n";
									$prev = $type;
									
								}
											
								$text .= "</optgroup></select>";		
									
								// $frm->select('deprecatedLanFile',$_SESSION['languageTools_lanFileList'], $_POST['deprecatedLanFile'],'class=select&useValues=1','Select Language File (optional)'). 
								$text .= $frm->admin_button('searchDeprecated',"Check",'other');
						//		$text .= "<span class='field-help'>".(count($lans) + count($plugs))." files found</span>";
								$text .= "
							</td>
						</tr>";
						
						
						$text .= "				
					</tbody>
				</table>
			</fieldset>
		</form>
	";
	

	
	e107::getRender()->tablerender(ADLAN_132.SEP.LANG_LAN_21, $mes->render().$text);
}


// ----------------------------------------------------------------------------

function available_langpacks()
{

	$xml = e107::getXml();
	
	$feed = e107::getPref('xmlfeed_languagepacks');
		
	if($rawData = $xml -> loadXMLfile($feed, TRUE))
	{
		if(!varset($rawData['language']))
		{
			return FALSE;
		}
		
		$text .= "<div class='block-text'>".LANG_LAN_35."</div>";
		$text .= "<table class='table adminlist'>";
		foreach($rawData['language'] as $val)
		{
			$att = $val['@attributes'];
			$name = $att['folder'];
			$languages[$name] = array(
			'name' => $att['name'],
            'author' => $att['author'],
            'authorURL' => $att['authorURL'],
            'folder' => $att['folder'],
            'version' => $att['version'],
            'date' => $att['date'],
            'compatibility' => $att['compatibility'],
            'url' => $att['url']
			);	
		}

		ksort($languages);
		
		//TODO LANs
		
		$text .= "<thead>
		<tr>
		<th>Name</th>
		<th>Version</th>
		<th>Author</th>
		<th>Release-date</th>		
		<th>Compatible</th>
		<th>Download</th>
		</tr>
		</thead>
		<tbody>";
	
		foreach($languages as $value)
		{
			$text .= "<tr>
				<td>".$value['name']."</td>
				<td>".$value['version']."</td>
				<td><a href='".$value['authorURL']."'>".$value['author']."</a></td>
				<td>".$value['date']."</td>
				<td>".$value['compatibility']."</td>
				
				<td><a href='".$value['url']."'>Download Pack</a></td>
				</tr>";
		}
		$text .= "</tbody></table>";
		
		return $text;
	}
	
	
	
	
	
	
		
}

function language_adminmenu()
{
	$pref = e107::getPref();
	
	$action = e_QUERY;
	
	if ($action == "")
	{
		$action = getperms('0') ? "main" : "tools";
	}
	if ($action == "modify")
	{
		$action = "db";
	}
	
	if(getperms('0'))
	{
		$var['main']['text'] = LAN_PREFS;
		$var['main']['link'] = e_SELF;
		
		if (isset($pref['multilanguage']) && $pref['multilanguage'])
		{
			$var['db']['text'] = LANG_LAN_03;
			$var['db']['link'] = e_SELF."?db";
		}
	}
	
//	$lcnt = explode(",", e_LANLIST);
//	if (count($lcnt) > 1)
//	{
		$var['tools']['text'] = LANG_LAN_21;
		$var['tools']['link'] = e_SELF."?tools";
//	}
	e107::getNav()->admin(ADLAN_132, $action, $var);
}
// Zip up the language pack.
// ===================================================

function zip_up_lang($language)
{
	global $tp;
	$ret = array();
	$ret['file'] = "";
	
	if($_SESSION['lancheck'][$language]['total'] > 0 && !E107_DEBUG_LEVEL)
	{
		$ret = array();
		$ret['error'] = TRUE;
		$message = (defined('LANG_LAN_34')) ? LANG_LAN_34 : "Please verify and correct the remaining [x] error(s) before attempting to create a language-pack.";
		$ret['message'] = str_replace("[x]",$_SESSION['lancheck'][$language]['total'],$message);
		return $ret;		
	}
		
	if(!isset($_SESSION['lancheck'][$language]))
	{
		$ret = array();
		$ret['error'] = TRUE;
		$ret['message'] = (defined('LANG_LAN_27')) ? LANG_LAN_27 : "Please verify your language files ('Verify') then try again.";
		return $ret;	
	}
	
	if(varset($_POST['contribute_pack']) && varset($_SESSION['lancheck'][$language]['total']) !='0')
	{
		$ret['error'] = TRUE;
		$ret['message'] = (defined("LANG_LAN_29")) ? LANG_LAN_29 : "You should correct the remaining errors before contributing your language pack.";	
		$ret['message']	 .= "<br />";
		$ret['message']	 .= (defined('LANG_LAN_27')) ? LANG_LAN_27 : "Please verify your language files ('Verify') then try again.";
		return $ret;
	}
	
		
	if(!is_writable(e_FILE."public"))
	{
		$ret['error'] = TRUE;
		$ret['message'] = LAN_UPLOAD_777 . " ".e_FILE."public";
		return $ret;		
	}
	
	if(is_readable(e_ADMIN."ver.php"))
	{
		include(e_ADMIN."ver.php");
	}
	
	$core_plugins = array(
		"alt_auth","banner","blogcalendar_menu","calendar_menu","chatbox_menu",
		"clock_menu","comment_menu","download","faqs", "featurebox", "forum","gallery", "gsitemap","import", "links_page",
		"linkwords","list_new","log","login_menu","newforumposts_main","newsfeed",
		"news", "newsletter","online", "page",
		"pm","poll","rss_menu","search_menu","siteinfo","tagwords", "tinymce",
		"trackback","tree_menu","user_menu"
	);
	 
	 $core_themes = array("bootstrap");

	require_once(e_HANDLER.'pclzip.lib.php');
	list($ver, $tmp) = explode(" ", $e107info['e107_version']);
	if(!$locale = find_locale($language))
	{
		$ret['error'] = TRUE;
		$file = "e107_languages/{$language}/{$language}.php";
		$def = (defined('LANG_LAN_25')) ? LANG_LAN_25 : "Please check that CORE_LC and CORE_LC2 have values in [lcpath] and try again.";
		$ret['message'] = str_replace("[lcpath]",$file,$def); // 
		return $ret;	
	}
		
		
	global $THEMES_DIRECTORY, $PLUGINS_DIRECTORY, $LANGUAGES_DIRECTORY, $HANDLERS_DIRECTORY, $HELP_DIRECTORY;
		
	if(($HANDLERS_DIRECTORY != "e107_handlers/") || ( $LANGUAGES_DIRECTORY != "e107_languages/") || ($THEMES_DIRECTORY != "e107_themes/") || ($HELP_DIRECTORY != "e107_docs/help/") || ($PLUGINS_DIRECTORY != "e107_plugins/"))
	{
		$ret['error'] = TRUE;
		$ret['message'] = (defined('LANG_LAN_26')) ? LANG_LAN_26 : "Please make sure you are using default folder names in e107_config.php (eg. e107_languages/, e107_plugins/ etc.) and try again.";
		return $ret;	
	}	
		
	$newfile = e_MEDIA_FILE."e107_".$ver."_".$language."_".$locale."-utf8.zip";
	
	$archive = new PclZip($newfile);
 
	$core = grab_lans(e_LANGUAGEDIR.$language."/", $language,'',0);
	$core_admin = grab_lans(e_BASE.$LANGUAGES_DIRECTORY.$language."/admin/", $language,'',2);
	$plugs = grab_lans(e_BASE.$PLUGINS_DIRECTORY, $language, $core_plugins); // standardized path. 
	$theme  = grab_lans(e_BASE.$THEMES_DIRECTORY, $language, $core_themes);
	$docs = grab_lans(e_BASE.$HELP_DIRECTORY,$language);
	$handlers = grab_lans(e_BASE.$HANDLERS_DIRECTORY,$language); // standardized path. 		
		
	$file = array_merge($core,$core_admin, $plugs, $theme, $docs, $handlers);
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



/*
function zip_up_lang($language)
{
	if (is_readable(e_ADMIN."ver.php"))
	{
		include (e_ADMIN."ver.php");
	}
	
	$tp = e107::getParser();
	

	require_once (e_HANDLER.'pclzip.lib.php');
	list($ver, $tmp) = explode(" ", $e107info['e107_version']);
	$newfile = e_UPLOAD."e107_".$ver."_".$language."_utf8.zip";
	$archive = new PclZip($newfile);
	$core = grab_lans(e_LANGUAGEDIR.$language."/", $language);
	$plugs = grab_lans(e_PLUGIN, $language);
	$theme = grab_lans(e_THEME, $language);
	$file = array_merge($core, $plugs, $theme);
	$data = implode(",", $file);
	if ($archive->create($data) == 0)
	{
		return $archive->errorInfo(true);
	}
	else
	{
		if($_POST['contribute_pack'])
		{
			$full_link = $tp->createConstants($newfile);
			$email_message = "Site: ".SITENAME."
			User: ".USERNAME."\n
			IP:".USERIP."
			...would like to contribute the following language pack for e107 v".$e107info['e107_version'].".
			Please see attachment.";
			$subject = basename($newfile);
			//TODO - send email to languagepack@e107.org with attachment. 
		}
		
		return LANG_LAN_22." (".str_replace("../", "", e_UPLOAD)."<a href='".$newfile."' >".basename($newfile)."</a>).";
	}
}

*/

function grab_lans($path, $language, $filter = "")
{
	$fl = e107::getFile();
	
	if ($lanlist = $fl->get_files($path, "", "standard", 4))
	{
		sort($lanlist);
	}
	else
	{
		return;
	}
	$pzip = array();
	foreach ($lanlist as $p)
	{
		$fullpath = $p['path'].$p['fname'];
		if (strpos($fullpath, $language) !== FALSE)
		{
			$pzip[] = $fullpath;
		}
	}
	return $pzip;
}


// -----------------------


/**
 * Compare Language File against script and find unused LANs
 * @param object $lanfile
 * @param object $script
 * @return string|boolean FALSE on error
 */
function unused($lanfile,$script,$reverse=false)
{
	
	$mes = e107::getMessage();
	$frm = e107::getForm();

	unset($_SESSION['language-tools-unused']);
//	$mes->addInfo("LAN=".$lanfile."<br />Script = ".$script);

	
	if($reverse == true)
	{
		
		$exclude = array("e_LANGUAGE","e_LANGUAGEDIR","e_LAN","e_LANLIST","e_LANCODE");
		
		$data = file_get_contents($script);	
		
		if(preg_match_all("/([\w_]*LAN[\w_]*)/", $data, $match))
		{
			// print_a($match);
			$foundLans = array();
			foreach($match[1] as $val)
			{
				if(!in_array($val, $exclude))
				{
					$foundLans[] = $val;	
				}	
			}
			sort($foundLans);
			$foundLans = array_unique($foundLans);
			$lanDefines = implode("\n",$foundLans);

		}	
		
		$mes->addDebug("Script: ".$script);

		$tmp = explode(",", $lanfile);
		foreach($tmp as $scr)
		{
			if(!file_exists($scr))
			{
				$mes->addError("Couldn't Load: ".$scr);
				continue;	
			}
			
			$compare[$scr] = file_get_contents($scr);	
			$mes->addDebug("LanFile: ".$scr);
		
		}	
		
		$lanfile = $script;
	}
	else
	{
		$lanDefines = file_get_contents($lanfile);
		$mes->addDebug("LanFile: ".$lanfile);
		
		$tmp = explode(",",$script);
		foreach($tmp as $scr)
		{
			if(!file_exists($scr))
			{
				$mes->addError("Couldn't Load: ".$scr);
				continue;	
			}
			$compare[$scr] = file_get_contents($scr);	
			$mes->addDebug("Script: ".$scr);
		}		
	}
		

	
//	print_a($compare);
//	print_a($lanDefines);

	if(!$compare)
	{
		$mes->addError("Couldn't read ".$script);
	}
	
	if(!$lanDefines)
	{
		$mes->addError("Couldn't read ".$lanfile);
	}

	$srch = array("<?php","<?","?>");
	$lanDefines = str_replace($srch,"",$lanDefines);
	$lanDefines = explode("\n", $lanDefines);
	
	if($lanDefines)
	{
		$text = $frm->open('language-unused');
		$text .= "<table class='table adminlist'>
		<colgroup>
			<col style='width:40%' />
			<col style='auto' />
		</colgroup>
		<thead>
		<tr>
			<th>".$lanfile."</th>";
			
			foreach($compare as $k=>$val)
			{
				$text .= "<th>".$k."</th>";	
			}
			
			if($reverse == true)
			{
				$text .= "<th>Definition</th>";	
			}
			
			$text .= "
			</tr>
			</thead>
			<tbody>";
		
	// 	for ($i=0; $i<count($lanDefines); $i++)
	//	{
		
		foreach($lanDefines as $line)
		{
			if(trim($line) !="")
			{	    		
		   		$disabled = (preg_match("#^//#i",$line)) ? " (disabled)" : FALSE;
				if($match = getDefined($line,$reverse))
				{
					$text .= compareit($match['define'],$compare,$match['value'],$disabled,$reverse);					
	    		}			   	 		
			}
	 	}
		
		
		
		
		

		$text .= "</tbody></table>";
		
		if(count($_SESSION['language-tools-unused'])>0 && $reverse == false)
		{
			$text .= "<div class='buttons-bar center'>".$frm->admin_button('disabled-unused','Disable All Unused','delete').
			$frm->hidden('disable-unused-lanfile',$lanfile).
			$frm->hidden('deprecatedLans',$script).
			
			"</div>";
		}
		
		$text .= $frm->close();
		
		if($reverse != true)
		{
			$mes->addInfo("<b>Pink items are likely to be unused LANs.<br />Comment out and test thoroughly.</b>");
		}
		
		$ret['text'] = $mes->render().$text;
		$ret['caption'] = "Deprecated LAN Check (experimental!)";

		return $ret;
	}
	else
	{
    	return FALSE;
	}

}

function getDefined($line,$script=false)
{
	
	if($script == true)
	{
		return array('define'=>$line,'value'=>'unknown');
	}
	
	if(preg_match("#\"(.*?)\".*?\"(.*)\"#",$line,$match) ||
					preg_match("#\'(.*?)\'.*?\"(.*)\"#",$line,$match) ||
					preg_match("#\"(.*?)\".*?\'(.*)\'#",$line,$match) ||
					preg_match("#\'(.*?)\'.*?\'(.*)\'#",$line,$match) ||
					preg_match("#\((.*?)\,.*?\"(.*)\"#",$line,$match) ||
					preg_match("#\((.*?)\,.*?\'(.*)\'#",$line,$match))
	{

		return array('define'=>$match[1],'value'=>$match[2]);
	}
	
}



function compareit($needle,$haystack,$value='',$disabled=FALSE, $reverse=false){
	
	
//	return "Need=".$needle."<br />hack=".$haystack."<br />val=".$val;
	//TODO Move this into a separate function (use a class for this whole script)
	
	$commonPhrases = file_get_contents(e_LANGUAGEDIR."English/admin/lan_admin.php");	
	$commonLines = explode("\n",$commonPhrases);
	
	$foundSimilar = FALSE;
	$foundCommon = FALSE;
	
	foreach($commonLines as $line)
	{
		if($match = getDefined($line))
		{
			$id = $match['define'];
			$ar[$id] = $match['value'];
		}
	}

	$commonArray = array_keys($ar);

	// Check if a common phrases was used. 
	foreach($ar as $def=>$common)
	{
		similar_text($value, $common, $p);
		
    	if(strtoupper(trim($value)) == strtoupper($common))
		{
			//$text .= "<div style='color:yellow'><b>$common</b></div>";
			$foundCommon = TRUE;
			break;
		}
		elseif($p > 55)
		{
			$foundSimilar = TRUE;
			break;	
		}	
		$p = 0 ; 
	}

	
	
	foreach($haystack as $script)
	{
		$lines = explode("\n",$script);
		
		$text .= "<td>";
		$text2 .= ($reverse == true) ? "<td>" : "";
		
		$count = 1;
		foreach($lines as $ln)
		{	
			if(preg_match("/\b".$needle."\b/i",$ln, $mtch))
			{
				if($disabled)
				{
					$text .= ADMIN_WARNING_ICON;
				}
				elseif($reverse == true)
				{
					$text .= ADMIN_TRUE_ICON;
				}	
				$text .= " Line:<b>".$count."</b>  "; // "' Found";
				
				if($reverse == true)
				{
					$text2 .= print_a($ln,true);
				}
				$found = TRUE;
			}

			$count++;	
		}

		if(!$found)
		{
			// echo "<br />Unused: ".$needle;
			if($reverse == true)
			{
				if(in_array($needle,$commonArray))
				{
					$color = "background-color:#E9EAF2";
					$text .= ADMIN_TRUE_ICON;	
					$value = "Common Term";
				} 
				else
				{
					$color = "background-color:yellow";	
					$text .= "<a href='#' title=\"Missing\">".ADMIN_WARNING_ICON."</a>";	
					$value = "Missing from language file";	
				}

			}
			else
			{
				$color = "background-color:pink";	
				$text .= "-";		
			}
			
			if(!$disabled)
			{
				$_SESSION['language-tools-unused'][] = $needle;	
			} 	
		}
		$text .= "</td>";
		$text2 .= ($reverse == true) ? "</td>" : "";
	}

//	$color = $found ? "" : "background-color:pink";


	if($foundCommon && $found)
	{	
		$color = "background-color:yellow";
		$disabled .= "<br /><i>".$common."</i> is a common phrase.<br />(Use <b>".$def."</b> instead.)";
		
		// return "<tr><td style='width:25%;'>".$needle .$disabled. "</td><td></td></tr>";
	}
	
	elseif($foundSimilar && $found && substr($def,0,4) == "LAN_")
	{
		$color = "background-color:#E9EAF2";
		$disabled .= "  <a class='e-tip' href='#' title=\"".$value." :: ".$common."\">".round($p)."% like ".$def."</a> ";
		// $disabled .= " <a class='e-tip' href='#' title=\"".$common."\">" . $def."</a>"; //  $common; 		
	}
	
	if($disabled == " (disabled)")
	{
		$color = "background-color:#DFFFDF";
	}	

	if(!$found)
	{
		$needle = "<a class='e-tip' href='#' title=\"".$value."\">".$needle."</a>";	
	}
	
	return "<tr><td style='width:25%;$color'>".$needle .$disabled. "</td>".$text.$text2."</tr>";
}







/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */













function headerjs()
{
	//FIXME breaking functionality.
	return;
	require_once (e_HANDLER.'js_helper.php');
	$ret = "
		<script type='text/javascript' src='".e_JS."core/admin.js'></script>
		<script type='text/javascript'>
			//add required core lan - delete confirm message
			(".e_jshelper::toString(LAN_JSCONFIRM).").addModLan('core', 'delete_confirm');

			//core object
			e107Admin.CoreLanguage = {};
			//show Table Copy option
			e107Admin.CoreLanguage.dataCopy = function(table) {
				if($('language-datacopy-' + table)) {
					$('language-datacopy-' + table).show();
				}
			}

			//registry - selected by default
			e107Admin.CoreLanguage._def_checked = {}

			//document observer
			document.observe('dom:loaded', function() {
				//find lan action checkboxes
				\$\$('input[type=checkbox][id^=language-action-]').each( function(element) {
					if(element.checked) e107Admin.CoreLanguage._def_checked[element.id] = true;// already checked, don't allow data copy
					var carea = element.up('div.auto-toggle-area');

					//clickable container - autocheck + allow data copy
					if(carea) {
						carea.observe('click', function(e) {
							element.checked = !(element.checked);
							if(element.checked && !e107Admin.CoreLanguage._def_checked[element.id]) {
								e107Admin.CoreLanguage.dataCopy(element.id.replace(/language-action-/, ''));
							}
						});
					}

					//checkbox observer
					element.observe('click', function(e) {
						if(e.element().checked && !e107Admin.CoreLanguage._def_checked[element.id])
							e107Admin.CoreLanguage.dataCopy(e.element().id.replace(/language-action-/, ''));
					});
				});
			});
		</script>
	";
	return $ret;
}
?>
