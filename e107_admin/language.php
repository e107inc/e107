<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Languages
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/language.php,v $
 * $Revision: 1.28 $
 * $Date: 2009-12-13 21:52:31 $
 * $Author: e107steved $
 *
 */
require_once ("../class2.php");
if (!getperms('0'))
{
	header("location:".e_BASE."index.php");
	exit;
}
include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);
$e_sub_cat = 'language';
require_once ("auth.php");
require_once (e_HANDLER."form_handler.php");
require_once (e_HANDLER."message_handler.php");
$frm = new e_form();
$emessage = &eMessage::getInstance();
$tabs = table_list(); // array("news","content","links");
$lanlist = explode(",", e_LANLIST);
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
		$emessage->add(LAN_SETSAVED, E_MESSAGE_SUCCESS);
	}
	else
	{
		$emessage->add(LAN_NO_CHANGE);
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
				$message .= sprintf(LANG_LAN_28, $_POST['lang_choices'].' '.$del_table).'[!br!]';
				$emessage->add(sprintf(LANG_LAN_28, $_POST['lang_choices'].' '.$del_table), E_MESSAGE_SUCCESS);
			}
			else
			{
				$message .= sprintf(LANG_LAN_29, $_POST['lang_choices'].' '.$del_table).'[!br!]';
				$emessage->add(sprintf(LANG_LAN_29, $_POST['lang_choices'].' '.$del_table), E_MESSAGE_WARNING);
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
				$message .= sprintf(LANG_LAN_30, $_POST['language'].' '.$value).'[!br!]';
				$emessage->add(sprintf(LANG_LAN_30, $_POST['language'].' '.$value), E_MESSAGE_SUCCESS);
			}
			else
			{
				if (!$_POST['drop'])
				{
					$message .= sprintf(LANG_LAN_00, $_POST['language'].' '.$value).'[!br!]';
					$emessage->add(sprintf(LANG_LAN_00, $_POST['language'].' '.$value), E_MESSAGE_WARNING);
				}
				else
				{
					$message .= sprintf(LANG_LAN_01, $_POST['language'].' '.$value).'[!br!]';
					$emessage->add(sprintf(LANG_LAN_01, $_POST['language'].' '.$value), E_MESSAGE_WARNING);
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
					$message .= $_POST['language'].' '.$value.' '.LAN_DELETED.'[!br!]';
					$emessage->add($_POST['language'].' '.$value.' '.LAN_DELETED, E_MESSAGE_SUCCESS);
				}
				else
				{
					$message .= sprintf(LANG_LAN_02, $_POST['language'].' '.$value).'[!br!]';
					$emessage->add(sprintf(LANG_LAN_02, $_POST['language'].' '.$value), E_MESSAGE_WARNING);
				}
			}
			else
			{
				// leave table. LANG_LAN_32
				$message .= sprintf(LANG_LAN_32, $_POST['language'].' '.$value).'[!br!]';
				$emessage->add(sprintf(LANG_LAN_32, $_POST['language'].' '.$value));
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
		$emessage->add(LANG_LAN_25.': '.$text);	
	}
	else
	{
		$emessage->add(LANG_LAN_36,E_MESSAGE_WARNING);		
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

if(varset($_POST['searchDeprecated']) && varset($_POST['deprecatedLans']))
{
	$mes = e107::getMessage();

	$lanfile = $_POST['deprecatedLans'];

				
	$scriptname = str_replace("lan_","",basename($lanfile));
	
	if(is_readable(e_ADMIN.$script))
	{
		$script = e_ADMIN.$scriptname; // matching files. lan_xxxx.php and xxxx.php
	}
	
	// Exceptions - same language loaded by several scripts. 
	if($lanfile == e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_e107_update.php")
	{
		$script = e_ADMIN."update_routines.php,".e_ADMIN."e107_update.php";
	}
		
	if(is_readable($lanfile))
	{
		if($res = unused($lanfile,$script))
		{
			$ns -> tablerender($res['caption'],$mes->render(). $res['text']);
		} 		
	}
	else
	{
		// echo 'PROBLEM';	
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
			<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='2'>
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
						<td class='label'>".ucfirst(str_replace("_", " ", $table_name))."</td>
						<td class='control'>
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
						<td class='label'><strong>".LANG_LAN_07."</strong></td>
						<td class='control'>
							".$frm->checkbox('drop', 1)."
							<div class='smalltext field-help'>".$frm->label(LANG_LAN_08, 'drop', 1)."</div>
						</td>
					</tr>
					<tr>
						<td class='label'><strong>".LAN_CONFDELETE."</strong></td>
						<td class='control'>
							".$frm->checkbox('remove', 1)."
							<div class='smalltext field-help'>".$frm->label(LANG_LAN_11, 'remove', 1)."</div>
						</td>
					</tr>
				</tbody>
			</table>
			<div class='buttons-bar center'>
				<input type='hidden' name='language' value='{$_POST['lang_choices']}' />
				<button class='{$baction}' type='submit' name='create_tables' value='no-value'><span>{$bcaption}</span></button>
			</div>
		</fieldset>
	</form>
	";
	$ns->tablerender($_POST['lang_choices'], $emessage->render().$text);
}
require_once (e_ADMIN."footer.php");
// ---------------------------------------------------------------------------


function multilang_prefs()
{
	global $pref,$lanlist,$emessage;
	$text = "
	<form method='post' action='".e_SELF."' id='linkform'>
		<fieldset id='core-language-settings'>
			<legend class='e-hideme'>".LANG_LAN_13."</legend>
			<table cellpadding='0' cellspacing='0' class='adminform'>
				<colgroup span='2'>
					<col class='col-label' />
					<col class='col-control' />
				</colgroup>
				<tbody>
					<tr>
						<td class='label'>".LANG_LAN_14.": </td>
						<td class='control'>
						<select name='mainsitelanguage' class='box select'>";
						$sellan = preg_replace("/lan_*.php/i", "", $pref['sitelanguage']);
						foreach ($lanlist as $lan)
						{
							$sel = ($lan == $sellan) ? " selected='selected'" : "";
							$text .= "
													<option value='{$lan}'{$sel}>".$lan."</option>
								";
						}
						$text .= "</select>
						</td>
					</tr>
					<tr>
						<td class='label'>".LANG_LAN_12.": </td>
						<td class='control'>
							<div class='auto-toggle-area autocheck'>";
						$checked = ($pref['multilanguage'] == 1) ? " checked='checked'" : "";
						$text .= "
													<input class='checkbox' type='checkbox' name='multilanguage' value='1'{$checked} />
							</div>
						</td>
					</tr>
					<tr>
						<td class='label'>".LANG_LAN_26.":</td>
						<td class='control'>
							<div class='auto-toggle-area autocheck'>\n";
					$checked = ($pref['noLanguageSubs'] == 1) ? " checked='checked'" : "";
					$text .= "
								<input class='checkbox' type='checkbox' name='noLanguageSubs' value='1'{$checked} />
								<div class='smalltext field-help'>".LANG_LAN_27."</div>
							</div>
						</td>
					</tr>
					<tr>
						<td class='label'>
							".LANG_LAN_18."
							<div class='label-note'>".LANG_LAN_19."</div>
						</td>
						<td class='control'>
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
							<td class='label'>
							Language by Domain Name
							<div class='label-note'>Domain determines the site's language. Enter domain without the 'www.'</div>
							</td>
							<td class='control'><table style='margin-left:0px;width:400px'>".$opt."</table></td>
						</tr>";
					}
					
					$text .= "
				</tbody>
			</table>
			<div class='buttons-bar center'>
				<button class='update' type='submit' name='submit_prefs' value='no-value'><span>".LAN_SAVE."</span></button>
			</div>
		</fieldset>
	</form>\n";
	
	e107::getRender()->tablerender(LANG_LAN_PAGE_TITLE.' - '.LANG_LAN_13, $emessage->render().$text); // "Language Preferences";
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
	global $pref,$tp,$frm,$emessage,$lanlist,$tabs;
	
	$sql = e107::getDb();
	
	if (isset($pref['multilanguage']) && $pref['multilanguage'])
	{
		// Choose Language to Edit:
		$text = "
			<fieldset id='core-language-list'>
				<legend class='e-hideme'>".LANG_LAN_16."</legend>
				<table cellpadding='0' cellspacing='0' class='adminlist'>
					<colgroup span='3'>
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
				$text .= "<button class='create' type='submit' name='create_edit_existing' value='no-value'><span>".LAN_CREATE."</span></button>";
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
		
		e107::getRender()->tablerender(LANG_LAN_PAGE_TITLE.' - '.LANG_LAN_16, $emessage->render().$text);
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
				<table cellpadding='0' cellspacing='0' class='adminform'>
					<colgroup span='3'>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>
						<tr>
							<td class='label'>".LAN_CHECK_1."</td>
							<td class='control'>
								<select name='language' class='tbox select'>
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
								$text .= "</select>
								<button class='submit' type='submit' name='language_sel' value='no-value'><span>".LAN_CHECK_2."</span></button>
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
				<table cellpadding='0' cellspacing='0' class='adminform'>
					<colgroup span='2'>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>
						<tr>
							<td class='label'>".LANG_LAN_23."</td>
							<td class='control'>
								<select name='language' class='tbox select'>
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
								
								<button class='submit' type='submit' name='ziplang' value='no-value'><span>".LANG_LAN_24."</span></button>
								<input type='checkbox' name='contribute_pack' value='1' /> Check to share your language-pack with the e107 community.
							</td>
						</tr>";
						
								
						$text .= "						
						<tr>
							<td class='label'>Search for Deprecated Lans</td>
							<td class='control'>
								<select name='deprecatedLans' class='tbox select'>
									<option value=''>".LAN_SELECT."</option>";
									
									$fl = e107::getFile();
									$fl->mode = 'full';
									$lans = $fl->get_files(e_LANGUAGEDIR."English/admin");
									
									$exclude = array('lan_admin.php');							
									
									
									foreach($lans as $script=>$lan)
									{								
										if(in_array(basename($lan),$exclude))
										{
											continue;
										}
										$selected = ($lan == varset($_POST['deprecatedLans'])) ? "selected='selected'" : "";
										$text .= "<option value='".$lan."' {$selected}>".str_replace(e_LANGUAGEDIR."English/","",$lan)."</option>\n";
									}
									
								$text .= "
								</select>".$frm->admin_button('searchDeprecated',"Check")."
							</td>
						</tr>";
						
						
						$text .= "				
					</tbody>
				</table>
			</fieldset>
		</form>
	";
	

	
	e107::getRender()->tablerender(LANG_LAN_PAGE_TITLE.' - '.LANG_LAN_21, $mes->render().$text);
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
		$text .= "<table cellpadding='0' cellspacing='0' class='adminlist'>";
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
	global $action,$pref;
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
	e_admin_menu(ADLAN_132, $action, $var);
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
function unused($lanfile,$script)
{
	$lanDefines = file_get_contents($lanfile);
	
	$tmp = explode(",",$script);
	foreach($tmp as $scr)
	{
		$compare[$scr] = file_get_contents($scr);	
	}
	
	
	$mes = e107::getMessage();

	if(!$compare)
	{
		$mes = e107::getMessage();
		$mes->add("Couldn't read ".$script, E_MESSAGE_ERROR);
	}
	
	if(!$lanDefines)
	{
		$mes = e107::getMessage();
		$mes->add("Couldn't read ".$lanfile, E_MESSAGE_ERROR);
	}


	$srch = array("<?php","<?","?>");
	$lanDefines = str_replace($srch,"",$lanDefines);
	$lanDefines = explode("\n", $lanDefines);
	
	if($lanDefines && $compare)
	{

		$text = "<table class='adminlist' style='width:100%'>
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
		   		$disabled = (eregi("^//",$line)) ? " (disabled)" : FALSE;
				if($match = getDefined($line))
				{
					$text .= compareit($match['define'],$compare,$match['value'],$disabled);					
	    		}			
	   	 		
			}
	 	}

		$text .= "</tbody></table>";

		$ret['text'] = $text;
		$ret['caption'] = "Deprecated LAN Check (experimental!)";

		return $ret;
	}
	else
	{
    	return FALSE;
	}

}

function getDefined($line)
{
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



function compareit($needle,$haystack,$value='',$disabled=FALSE){
	
	
//	return "Need=".$needle."<br />hack=".$haystack."<br />val=".$val;
	//TODO Move this into a separate function (use a class for this whole script)
	
	$commonPhrases = file_get_contents(e_LANGUAGEDIR."English/admin/lan_admin.php");	
	$commonLines = explode("\n",$commonPhrases);
	
	foreach($commonLines as $line)
	{
		if($match = getDefined($line))
		{
			$id = $match['define'];
			$ar[$id] = $match['value'];
		}
	}

	// Check if a common phrases was used. 
	foreach($ar as $def=>$common)
	{
    	if(strtoupper(trim($value)) == strtoupper($common))
		{
			//$text .= "<div style='color:yellow'><b>$common</b></div>";
			$foundCommon = TRUE;
			break;
		}
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
				$text .= " Line:<b>".$count."</b>  "; // "' Found";

				$found = TRUE;
			}

			$count++;	
		}

		if(!$found)
		{
			$text .= "-";
		}
		$text .= "</td>";
		
	}

	$color = $found ? "" : "background-color:pink";

	if($foundCommon && $found)
	{	
		$color = "background-color:yellow";
		$disabled .= "<br /><i>".$common."</i> is a common phrase.<br />(Use <b>".$def."</b> instead.)";
		// return "<tr><td style='width:25%;'>".$needle .$disabled. "</td><td></td></tr>";
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
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
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
