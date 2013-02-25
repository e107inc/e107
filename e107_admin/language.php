<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Languages
 *
 * $URL$
 * $Id$
 */
require_once ("../class2.php");
if (!getperms('0'))
{
	header("location:".e_BASE."index.php");
	exit;
}
//include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);
e107::coreLan('language', true);

$e_sub_cat = 'language';
require_once ("auth.php");
require_once (e_HANDLER."form_handler.php");
require_once (e_HANDLER."message_handler.php");
$frm = e107::getForm();
$mes = e107::getMessage();
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
if (isset($_POST['submit_prefs']) && isset($_POST['mainsitelanguage']))
{
	unset($temp);
	$changes = array();
	$temp['multilanguage'] = $_POST['multilanguage'];
	$temp['multilanguage_subdomain'] = $_POST['multilanguage_subdomain'];
	$temp['multilanguage_domain'] = $_POST['multilanguage_domain'];
	$temp['sitelanguage'] = $_POST['mainsitelanguage'];
	$temp['noLanguageSubs'] = $_POST['noLanguageSubs'];
	if ($admin_log->logArrayDiffs($temp, $pref, 'LANG_01'))
	{
		save_prefs(); // Only save if changes
		//$mes->addSuccess(LAN_SETSAVED, E_MESSAGE_SUCCESS);
	}
	else
	{
		$mes->addInfo(LAN_NO_CHANGE);
	}
}
// ----------------- delete tables ---------------------------------------------
if (isset($_POST['del_existing']) && $_POST['lang_choices'])
{
	$lang = strtolower($_POST['lang_choices']);
	foreach ($tabs as $del_table)
	{
		if ($sql->db_Table_exists($lang."_".$del_table,TRUE))
		{
				echo $del_table." exists<br />";
			$qry = "DROP TABLE ".$mySQLprefix."lan_".$lang."_".$del_table;
			if (mysql_query($qry))
			{
				$message .= sprintf(LANG_LAN_28, $_POST['lang_choices'].' '.$del_table).'[!br!]'; // can be removed?
				$mes->addSuccess(sprintf(LANG_LAN_28, $_POST['lang_choices'].' '.$del_table));
			}
			else
			{
				$message .= sprintf(LANG_LAN_29, $_POST['lang_choices'].' '.$del_table).'[!br!]'; // can be removed?
				$mes->addWarning(sprintf(LANG_LAN_29, $_POST['lang_choices'].' '.$del_table));
			}
		}
	}
	$admin_log->log_event('LANG_02', $message, E_LOG_INFORMATIVE, '');
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
				$message .= sprintf(LANG_LAN_30, $_POST['language'].' '.$value).'[!br!]'; // can be removed?
				$mes->addSuccess(sprintf(LANG_LAN_30, $_POST['language'].' '.$value));
			}
			else
			{
				if (!$_POST['drop'])
				{
					$message .= sprintf(LANG_LAN_00, $_POST['language'].' '.$value).'[!br!]'; // can be removed?
					$mes->addWarning(sprintf(LANG_LAN_00, $_POST['language'].' '.$value));
				}
				else
				{
					$message .= sprintf(LANG_LAN_01, $_POST['language'].' '.$value).'[!br!]'; // can be removed?
					$mes->addWarning(sprintf(LANG_LAN_01, $_POST['language'].' '.$value));
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
					$message .= sprintf(LANG_LAN_02, $_POST['language'].' '.$value).'[!br!]'; // can be removed?
					$mes->addWarning(sprintf(LANG_LAN_02, $_POST['language'].' '.$value));
				}
			}
			else
			{
				// leave table. LANG_LAN_32
				$message .= sprintf(LANG_LAN_32, $_POST['language'].' '.$value).'[!br!]'; // can be removed?
				$mes->addInfo(sprintf(LANG_LAN_32, $_POST['language'].' '.$value));
			}
		}
	}
	$admin_log->log_event('LANG_03', $message, E_LOG_INFORMATIVE, '');
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
if (varset($_POST['ziplang']) && varset($_POST['language']))
{
	if(varset($pref['lancheck'][$_POST['language']]) == 1)
	{
		$text = zip_up_lang($_POST['language']);
		$admin_log->log_event('LANG_04', $_POST['language'], E_LOG_INFORMATIVE, '');
		$mes->addInfo(LANG_LAN_25.': '.$text);	
	}
	else
	{
		$mes->addWarning(LANG_LAN_36);		
	}
}
if (varset($action) == "tools")
{
	show_tools();
	if($languagePacks = available_langpacks() )
	{
		e107::getRender()->tablerender(LANG_LAN_34,$languagePacks );	
	}	
}


	function findIncludedFiles($script,$reverse=false)
	{
		
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
				<button class='{$baction}' type='submit' name='create_tables' value='no-value'><span>{$bcaption}</span></button>
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
	global $lanlist;
	$pref = e107::getPref();
	$mes = e107::getMessage();
	$frm = e107::getForm();
	
	//XXX Remove later. 
	// Enable only for developers - SetEnv E_ENVIRONMENT develop
	if(!isset($_SERVER['E_ENVIRONMENT']) || $_SERVER['E_ENVIRONMENT'] !== 'develop') 
	{
		$lanlist = array('English'); 
		$mes->addInfo("Alpha version currently supports only the English language. After most features are stable and English terms are optimized - translation will be possible.");
	}
	
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
					
						$text .= $frm->selectbox('mainsitelanguage',$lanlist,$sellan,"useValues=1");
						$text .= "
						</td>
					</tr>
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
							<div class='label-note'>".LANG_LAN_19."</div>
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
							$opt .= "<tr><td class='middle' style='width:5%'>".$val."</td><td class='left'><input type='text' name='multilanguage_domain[".$val."]' value=\"".$pref['multilanguage_domain'][$val]."\" /></td></tr>";	
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
				//FIXME sprintf
				$text .= "<button class='edit' type='submit' name='edit_existing' value='no-value'><span>".LAN_EDIT."</span></button>
						<button class='delete' type='submit' name='del_existing' value='no-value' title='".sprintf(LANG_LAN_33, $e_language).' '.LAN_JSCONFIRM."'><span>".LAN_DELETE."</span></button>";
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


function show_tools()
{
	$frm = e107::getForm();
	$mes = e107::getMessage();
	
	include_lan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_lancheck.php");
	$text = "
		<form id='core-language-lancheck-form' method='post' action='".e_ADMIN."lancheck.php'>
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
							<td>
								<select name='language' class='tbox'>
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
		
		$text .= "
		<form id='ziplang' method='post' action='".e_SELF."?tools'>
			<fieldset id='core-language-package'>
				<legend class='e-hideme'>".LANG_LAN_23."</legend>
				<table class='table adminform'>
					<colgroup>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>
						<tr>
							<td>".LANG_LAN_23."</td>
							<td>
								<select name='language' class='tbox'>
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
						
								
						$text .= "						
						<tr>
							<td>Search for Deprecated Lans</td>
							<td>
								<select name='deprecatedLans' class='tbox'>
									<option value=''>".LAN_SELECT."</option>";
									
									$fl = e107::getFile();
									$fl->mode = 'full';
									$omit = array('languages','\.png','\.gif','templates','handlers');
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
									0 => "Lan File > Script",
									1 => "Script > Lan File"
								);
									
								$text .= "
								</select>".
								$frm->selectbox('deprecatedLansReverse',$depOptions,$_POST['deprecatedLansReverse']). 
								$frm->admin_button('searchDeprecated',"Check",'other')."
								<span class='field-help'>".(count($lans) + count($plugs))." files found</span>
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
		$action = "main";
	}
	if ($action == "modify")
	{
		$action = "db";
	}
	
	$var['main']['text'] = LAN_PREFS;
	$var['main']['link'] = e_SELF;
	
	if (isset($pref['multilanguage']) && $pref['multilanguage'])
	{
		$var['db']['text'] = LANG_LAN_03;
		$var['db']['link'] = e_SELF."?db";
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
	if (is_readable(e_ADMIN."ver.php"))
	{
		include (e_ADMIN."ver.php");
	}
	
	$tp = e107::getParser();
	
	/*
	 $core_plugins = array(
	 "alt_auth","banner_menu","blogcalendar_menu","calendar_menu","chatbox_menu",
	 "clock_menu","comment_menu","content","featurebox","forum","gsitemap",
	 "links_page","linkwords","list_new","log","login_menu",
	 "newforumposts_main","newsfeed","newsletter","online",
	 "other_news_menu","pdf","pm","poll","rss_menu",
	 "search_menu","siteinfo","trackback","tree_menu","user_menu","userlanguage_menu",
	 "usertheme_menu"
	 );
	 $core_themes = array("crahan","e107v4a","human_condition","interfectus","jayya",
	 "khatru","kubrick","lamb","leaf","newsroom","reline","sebes","vekna_blue");
	 */
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
		
		
		$mes->addInfo("<b>Pink items are likely to be unused LANs.<br />Comment out and test thoroughly.</b>");

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
		
		$count = 1;
		foreach($lines as $ln)
		{	
			if(preg_match("/\b".$needle."\b/i",$ln))
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
	
	return "<tr><td style='width:25%;$color'>".$needle .$disabled. "</td>".$text."</tr>";
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
