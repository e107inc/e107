<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Database Utilities
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/db.php,v $
 * $Revision: 1.24 $
 * $Date: 2009-08-29 18:07:42 $
 * $Author: e107coders $
 *
*/

require_once ("../class2.php");

if(!getperms('0'))
{
	header('location:'.e_BASE.'index.php');
	exit();
}

if(isset($_POST['back']))
{
	header("location: ".e_SELF);
	exit();
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

$e_sub_cat = 'database';

require_once (e_HANDLER."form_handler.php");
$frm = new e_form();

require_once (e_HANDLER."message_handler.php");
$emessage = &eMessage::getInstance();

/*
 * Execute trigger
 */
if(isset($_POST['trigger_db_execute']))
{
	if(!varset($_POST['db_execute']))
	{
		$emessage->add(DBLAN_53, E_MESSAGE_WARNING);
	}
	else
	{
		$_POST[$_POST['db_execute']] = true;
	}
}

if(isset($_POST['db_update']) || $_GET['mode']=='db_update')
{
	header("location: ".e_ADMIN."e107_update.php");
	exit();
}

if(isset($_POST['verify_sql']) || $_GET['mode']=='verify_sql')
{
	header("location: ".e_ADMIN."db_verify.php");
	exit();
}

if(isset($_POST['exportXmlFile']))
{
	exportXmlFile();
	exit();
}

require_once ("auth.php");
require_once (e_HANDLER."form_handler.php");
$frm = new e_form();
$emessage = &eMessage::getInstance(); //nothing wrong with doing it twice

/* No longer needed after XML feature added. 

if(isset($_POST['backup_core']) || $_GET['mode']=='backup_core')
{
	backup_core();
	//message_handler("MESSAGE", DBLAN_1);
	$emessage->add(DBLAN_1, E_MESSAGE_SUCCESS);
}

*/









$st = new system_tools;

require_once ("footer.php");

class system_tools
{
	
	public $_options = array();
		
	
	function __construct()
	{
		global $mySQLdefaultdb;
		
		$this->_options = array(
			"db_update"				=> array('diz'=>DBLAN_15, 'label'=>DBLAN_16),
			"verify_sql"			=> array('diz'=>DBLAN_4, 'label'=>DBLAN_5),
			'optimize_sql'			=> array('diz'=>DBLAN_6, 'label'=> DBLAN_7),
			'plugin_scan'			=> array('diz'=>DBLAN_28, 'label'=> DBLAN_29),
			'pref_editor'			=> array('diz'=>DBLAN_19, 'label'=> DBLAN_20),
		//	'backup_core'			=> array('diz'=>DBLAN_8, 'label'=> DBLAN_9),
			'verify_sql_record'		=> array('diz'=>DBLAN_35, 'label'=> DBLAN_36),
			'importForm'			=> array('diz'=>DBLAN_59, 'label'=> DBLAN_59),
			'exportForm'			=> array('diz'=>DBLAN_58, 'label'=> DBLAN_58),
			'sc_override_scan'		=> array('diz'=>DBLAN_55, 'label'=> DBLAN_56)
		);
		
		//TODO Merge db_verify.php into db.php 
		

		
		if(isset($_POST['delplug']))
		{
			$this->delete_plugin_entry();	
		}
		
		if(isset($_POST['upload']))
		{	
			$this->importCorePrefs();		
		}
		
		if(isset($_POST['delpref']) || (isset($_POST['delpref_checked']) && isset($_POST['delpref2'])))
		{
			$this->del_pref_val();
		}
		
		if(isset($_POST['verify_sql_record']) || $_GET['mode']=='verify_sql_record' || isset($_POST['check_verify_sql_record']) || isset($_POST['delete_verify_sql_record']))
		{
			// $this->verify_sql_record();  - currently performed in db_verify.php
		}
		
		if(isset($_POST['importForm']) ||  $_GET['mode']=='importForm')
		{
			$this->importForm();	
		}
				
		if(isset($_POST['exportForm']) ||  $_GET['mode']=='exportForm')
		{
			$this->exportXmlForm();		
		}

		if(isset($_POST['optimize_sql']) || $_GET['mode']=='optimize_sql')
		{
			$this->optimizesql($mySQLdefaultdb);
		}
		
		if(isset($_POST['pref_editor']) || $_GET['mode']=='pref_editor' || isset($_POST['delpref']) || isset($_POST['delpref_checked']))
		{
			$this->pref_editor();
		}
		
		if(isset($_POST['sc_override_scan']) || $_GET['mode']=='sc_override_scan')
		{
			$this->scan_override();
		}
		
		if(isset($_POST['plugin_scan']) || e_QUERY == "plugin" || $_POST['delplug'] || $_GET['mode']=='plugin_scan')
		{
			$this->plugin_viewscan();
		}
		
		if(!vartrue($_GET['mode']))
		{		
			$this->render_options();
		}
					
	}

	/**
	 * Delete selected preferences. 
	 * @return 
	 */	
	function del_pref_val()
	{
		global $pref, $e107cache, $emessage;
		$del = array_keys($_POST['delpref']);
		$delpref = $del[0];
	
		if($delpref)
		{
			unset($pref[$delpref]);
			$deleted_list .= "<li>".$delpref."</li>";
		}
	
		if($_POST['delpref2'])
		{
	
			foreach($_POST['delpref2'] as $k => $v)
			{
				$deleted_list .= "<li>".$k."</li>";
				unset($pref[$k]);
			}
		}
	
		save_prefs();
		$emessage->add(LAN_DELETED."<ul>".$deleted_list."</ul>");
		$e107cache->clear();
		//$e107->ns->tablerender(LAN_DELETED,$message);	
	}

	private function delete_plugin_entry()
	{
		global $sql, $emessage;
	
		$del = array_keys($_POST['delplug']);
		if($sql->db_Delete("plugin", "plugin_id='".intval($del[0])."' LIMIT 1"))
		{
			$emessage->add(LAN_DELETED, E_MESSAGE_SUCCESS);
		}
		else
		{
			$emessage->add(LAN_DELETED_FAILED, E_MESSAGE_WARNING);
		}
	
	}



	/**
	 * Render Options
	 * @return 
	 */	
	private function render_options()
	{
		$frm = e107::getSingleton('e_form');
			
		$text = "
		<form method='post' action='".e_SELF."' id='core-db-main-form'>
			<fieldset id='core-db-plugin-scan'>
			<legend class='e-hideme'>".DBLAN_10."</legend>
				<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='2'>
					<col style='width: 60%'></col>
					<col style='width: 40%'></col>
				</colgroup>
				<tbody>";
		
		foreach($this->_options as $key=>$val)
		{
			$text .= "<tr>
						<td>".$val['diz']."</td>
						<td>
							".$frm->radio('db_execute', $key).$frm->label($val['label'], 'db_execute', $key)."
						</td>
					</tr>\n";	
			
		}	
			
		$text .= "
	
				</tbody>
				</table>
				<div class='buttons-bar center'>
					".$frm->admin_button('trigger_db_execute', DBLAN_51, 'execute')."
				</div>
			</fieldset>
		</form>
		";
		
		$emessage = eMessage::getInstance();
		e107::getRender()->tablerender(DBLAN_10, $emessage->render().$text);		
	}
	

	/**
	 * Import XML Form
	 * @return 
	 */	
	private function importForm()
	{
		 // Get largest allowable file upload
		 
		 $frm = e107::getSingleton('e_form');
	
		 
				require_once(e_HANDLER.'upload_handler.php');
				  $max_file_size = get_user_max_upload();
	
				  $text = "
					<form enctype='multipart/form-data' method='post' action='".e_SELF."?mode=".$_GET['mode']."'>
	                <table cellpadding='0' cellspacing='0' class='adminform'>
			
	                	<colgroup span='2'>
	                		<col class='col-label' />
	                		<col class='col-control' />
	                	</colgroup>
						
					
					<tbody>
					<tr>
					<td>".LAN_UPLOAD."</td>
					<td>
						<input type='hidden' name='MAX_FILE_SIZE' value='{$max_file_size}' />
						<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
						<input class='tbox' type='file' name='file_userfile[]' accept='text/xml' size='50' />
					</td>
					</tr>
					</tbody>
					</table>
	
					<div class='center buttons-bar'>";
	                $text .= $frm->admin_button('upload', LAN_UPLOAD, 'submit', LAN_UPLOAD);
	
					$text .= "
					</div>
	
					</form>\n";
					
		$emessage = eMessage::getInstance();
		e107::getRender()->tablerender(DBLAN_59, $emessage->render().$text);	
		
	}

	/**
	 * Export XML Dump
	 * @return 
	 */	
	private function exportXmlForm()
	{
		
		$frm = e107::getSingleton('e_form');
		
		$text = "<form method='post' action='".e_SELF."' id='core-db-export-form'>
			<fieldset id='core-db-export'>
			<legend class='e-hideme'>Export Options</legend>
				<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='2'>
					
					<col style='width: 80%'></col>
					<col style='width: 20%'></col>
				</colgroup>
				<thead>
				<tr>
					<th>Name</th>
					<th class='right'>Rows</th>
					
				</tr>	
				</thead>
				<tbody>
	
					<tr>
						<td>
							".$frm->checkbox('xml_core_prefs', '1')." ".LAN_PREFS.": Core
						</td>
						<td>&nbsp;</td>
					</tr>";
					
					e107::getDb()->db_Select("core", "*", "e107_name NOT REGEXP('SitePrefs|SitePrefs_Backup|IconPool|emote|emote_default|notify_prefs|search_prefs|menu_pref|pref_backup') ");
					while ($row = e107::getDb()->db_Fetch())
					{
						$text .= "<tr>
						<td>
							".$frm->checkbox("xml_prefs[".$row['e107_name']."]", '1')."
						".LAN_PREFS.": ".$row['e107_name']."</td>
						<td>&nbsp;</td>

						</tr>";
					}
					
					$tables = table_list();
					
					foreach($tables as $name=>$count)
					{				
						$text .= "<tr>					
							<td>
								".$frm->checkbox("xml_table[".$name."]", $name)." Table Data: ".$name." 
							</td>
							<td class='right'>$count</td>
						</tr>";
					}
	
					$text .="
					</tbody>
				</table>
				<div class='buttons-bar center'>
					".$frm->admin_button('exportXmlFile', "Export File", 'exportXmlFile')."
				</div>
			</fieldset>
		</form>	";
		
	
		e107::getRender()->tablerender("Export Options", $text);		
		
		
	}

	/**
	 * Import XML Dump
	 * @return 
	 */
	private function importCorePrefs()
	{
		//TODO - move to own class and make generic. 
		// SecretR - structure changes / improvements proposal

		$xmlArray = e107::getSingleton('xmlClass')->loadXMLfile($_FILES['file_userfile']['tmp_name'][0],'advanced');
		$emessage = eMessage::getInstance();
		
		if(vartrue($xmlArray['prefs']['core'])) // Save Core Prefs
		{
			foreach ($xmlArray['prefs']['core'] as $val)
			{
			 	$value = (substr($val['@value'],0,7) == "array (") ? e107::getArrayStorage()->ReadArray($val['@value']) : $val['@value'];
			 //	print_a($val['@value']);
			   	e107::getConfig()->set($val['@attributes']['name'], $value);
			}
		
		  	e107::getConfig()->save(FALSE);
		}
		
		if(vartrue($xmlArray['database']))
		{
			foreach($xmlArray['database']['dbTable'] as $val)
			{
				$table = $val['@attributes']['name'];
				
				foreach($val['item'] as $item)
				{
					$insert_array = array();
					foreach($item['field'] as $f)
					{
						$fieldkey = $f['@attributes']['name'];
						$fieldval = $f['@value'];
					
						$insert_array[$fieldkey] = $fieldval;
											
					}
					if(e107::getDB()->db_Replace($table, $insert_array)!==FALSE)
					{					
						$emessage->add("Inserted $table", E_MESSAGE_SUCCESS);					
					}
					else
					{
						$emessage->add("Failed to Inserted $table", E_MESSAGE_ERROR);	
					}
				}
				
				
			}	
			
		}	
	}
	
	/**
	 * Optimize SQL
	 * @return 
	 */
	private function optimizesql($mySQLdefaultdb)
	{
	//	global $emessage;
		$result = mysql_list_tables($mySQLdefaultdb);
		while($row = mysql_fetch_row($result))
		{
			mysql_query("OPTIMIZE TABLE ".$row[0]);
		}
	
	//	$emessage->add(DBLAN_11." $mySQLdefaultdb ".DBLAN_12, E_MESSAGE_SUCCESS);
		e107::getRender()->tablerender(DBLAN_7, DBLAN_11." $mySQLdefaultdb ".DBLAN_12);	
	}
	
	/**
	 * Preferences Editor
	 * @return 
	 */
	private function pref_editor()
	{
		//TODO Add drop-down filter for editing plugin prefs also. 
		
		global $pref, $e107, $emessage, $frm;
		ksort($pref);
	
		$text = "
				<form method='post' action='".e_ADMIN."db.php?mode=".$_GET['mode']."' id='pref_edit'>
					<fieldset id='core-db-pref-edit'>
						<legend class='e-hideme'>".DBLAN_20."</legend>
						<table cellpadding='0' cellspacing='0' class='adminlist'>
							<colgroup span='4'>
								<col style='width: 5%'></col>
								<col style='width: 20%'></col>
								<col style='width: 70%'></col>
								<col style='width: 5%'></col>
							</colgroup>
							<thead>
								<tr>
									<th class='center'>".LAN_DELETE."</th>
									<th>".DBLAN_17."</th>
									<th>".DBLAN_18."</th>
									<th class='center last'>".LAN_OPTIONS."</th>
								</tr>
							</thead>
							<tbody>
			";
	
		foreach($pref as $key => $val)
		{
			$ptext = (is_array($val)) ? "<pre>".print_r($val, TRUE)."</pre>" : htmlspecialchars($val, ENT_QUOTES, 'utf-8');
			$ptext = $e107->tp->textclean($ptext, 80);
	
			$text .= "
				<tr>
					<td class='center autocheck e-pointer'>".$frm->checkbox("delpref2[$key]", 1)."</td>
					<td>{$key}</td>
					<td>{$ptext}</td>
					<td class='center'>".$frm->submit_image("delpref[$key]", LAN_DELETE, 'delete', LAN_CONFIRMDEL." [$key]")."</td>
				</tr>
				";
		}
	
		$text .= "
							</tbody>
						</table>
						<div class='buttons-bar center'>
							".$frm->admin_button('delpref_checked', DBLAN_21, 'delete')."
							".$frm->admin_button('back', DBLAN_13, 'back')."
						</div>
					</fieldset>
				</form>\n\n";
				
		//$text .= "<div style='text-align:center'><a href='".e_SELF."'>".DBLAN_13."</a></div>\n";
		e107::getRender()->tablerender(DBLAN_10.' - '.DBLAN_20, $emessage->render().$text);
	
		return $text;
	}
	
	/**
	 * Preferences Editor
	 * @return 
	 */	
	private function scan_override()
	{
		global $pref, $emessage;
		
		require_once(e_HANDLER.'file_class.php');
		$f = new e_file;
		
		$scList = '';
		$fList = $f->get_files(e_FILE.'shortcode/override', '\.sc$');
		if(count($fList))
		{
			$tmp = array();
			foreach($fList as $file)
			{
				$tmp[] = strtoupper(substr($file['fname'], 0, -3));
			}
			$scList = implode(',', $tmp);
			unset($tmp);
		}
		$pref['sc_override'] = $scList;
		save_prefs();
	//	$emessage->add(DBLAN_57.':<br />'.$pref['sc_override'], E_MESSAGE_SUCCESS);
		e107::getRender()->tablerender(DBLAN_56, DBLAN_57.':<br />'.$pref['sc_override']);
	}

	/**
	 * Plugin Folder Scanner
	 * @return 
	 */		
	private function plugin_viewscan()
	{
		$error_messages = array(0 => DBLAN_31, 1 => DBLAN_32, 2 => DBLAN_33, 3 => DBLAN_34);
		$error_image = array("integrity_pass.png", "integrity_fail.png", "warning.png", "blank.png");
	
		global $sql, $e107, $emessage, $frm;
	
		require_once (e_HANDLER."plugin_class.php");
		$ep = new e107plugin();
		$ep->update_plugins_table(); // scan for e_xxx changes and save to plugin table.
		$ep->save_addon_prefs(); // generate global e_xxx_list prefs from plugin table.
	
		/* we all are awaiting for PHP5 only support - method chaining...
		$emessage->add(DBLAN_22.' - '.DBLAN_23, E_MESSAGE_SUCCESS)
				 ->add("<a href='".e_SELF."'>".DBLAN_13."</a>", E_MESSAGE_SUCCESS)
				 ->add(DBLAN_30);
		*/
	
		$emessage->add(DBLAN_23, E_MESSAGE_SUCCESS);
		$emessage->add("<a href='".e_SELF."'>".DBLAN_13."</a>", E_MESSAGE_SUCCESS);
		$emessage->add(DBLAN_30);
	
		$text = "
				<form method='post' action='".e_ADMIN."db.php?mode=".$_GET['mode']."' id='plug_edit'>
					<fieldset id='core-db-plugin-scan'>
						<legend class='e-hideme'>".ADLAN_CL_7."</legend>
						<table cellpadding='0' cellspacing='0' class='adminlist'>
							<colgroup span='4'>
								<col style='width: 20%'></col>
								<col style='width: 20%'></col>
								<col style='width: 35%'></col>
								<col style='width: 25%'></col>
							</colgroup>
							<thead>
								<tr>
									<th>".DBLAN_24."</th>
									<th>".DBLAN_25."</th>
									<th>".DBLAN_26."</th>
									<th class='center last'>".DBLAN_27."</th>
								</tr>
							</thead>
							<tbody>
			";
	
		$sql->db_Select("plugin", "*", "plugin_id !='' order by plugin_path ASC"); // Must order by path to pick up duplicates. (plugin names may change).
		$previous = '';
		while($row = $sql->db_Fetch())
		{
			$text .= "
								<tr>
									<td>".$e107->tp->toHtml($row['plugin_name'], FALSE, "defs,emotes_off")."</td>
	               					<td>".$row['plugin_path']."</td>
									<td>";
	
			if(trim($row['plugin_addons']))
			{
				//XXX - $nl_code = ''; - OLD VAR?
				foreach(explode(',', $row['plugin_addons']) as $this_addon)
				{
					$ret_code = 3; // Default to 'not checked
					if((strpos($this_addon, 'e_') === 0) && (substr($this_addon, - 4, 4) != '_sql'))
					{
						$ret_code = $ep->checkAddon($row['plugin_path'], $this_addon); // See whether spaces before opening tag or after closing tag
					}
					$text .= "<div class='clear'>";
					$text .= "<img class='icon action S16' src='".e_IMAGE_ABS."fileinspector/".$error_image[$ret_code]."' alt='".$error_messages[$ret_code]."' title='".$error_messages[$ret_code]."' />";
					$text .= trim($this_addon); // $ret_code - 0=OK, 1=content error, 2=access error
					$text .= "</div>";
				}
			}
	
			$text .= "
								</td>
								<td class='center'>
				";
	
			if($previous == $row['plugin_path'])
			{
				$delid = $row['plugin_id'];
				$delname = $row['plugin_name'];
				//Admin delete button
				$text .= $frm->admin_button("delplug[{$delid}]", DBLAN_52, 'delete', '', array('title' => LAN_CONFIRMDEL." ID:{$delid} [$delname]"));
				//Or maybe image submit? -
				//$text .= $frm->submit_image("delplug[{$delid}]", DBLAN_52, 'delete', LAN_CONFIRMDEL." ID:{$delid} [$delname]");
			}
			else
			{
				$text .= ($row['plugin_installflag'] == 1) ? DBLAN_27 : " "; // "Installed and not installed";
			}
			$text .= "
								</td>
							</tr>
				";
			$previous = $row['plugin_path'];
		}
	
		$text .= "
							</tbody>
						</table>
					</fieldset>
				</form>
			";
	
		e107::getRender()->tablerender(DBLAN_10.' - '.DBLAN_22, $emessage->render().$text);
	}
}

//XXX - what is this for (backup core)? <input type='hidden' name='sqltext' value='{$sqltext}' />

function db_adminmenu()
{
	global $st;
	
	foreach($st->_options as $key=>$val)
	{
		$var[$key]['text'] = $val['label'];
		$var[$key]['link'] = e_SELF."?mode=".$key;
	}
			
	e_admin_menu(DBLAN_10, $_GET['mode'], $var);	
}






function exportXmlFile()
{

	//TODO  - move export/import functions to own class. 

	require_once(e_ADMIN."ver.php");
		
	$text = "<?xml version='1.0' encoding='utf-8' ?>\n";
	$text .= "<e107Export version='".$e107info['e107_version']."' timestamp='".time()."' >\n";

	if(varset($_POST['xml_core_prefs'])) // Export Core Preferences. 
	{
		$pref = e107::getPref();
		$text .= "\t<prefs>\n";
		foreach($pref as $key=>$val)
		{
			if(isset($val))
			{
				$val = is_array($val) ? e107::getArrayStorage()->WriteArray($val,FALSE) : $val;
				
				$text .= "\t\t<core name='$key'><![CDATA[".$val."]]></core>\n";
			}
		}
		$text .= "\t</prefs>\n";
	}

	if(varset($_POST['xml_table']))
	{
		$text .= "\t<database>\n";
		foreach($_POST['xml_table'] as $tbl)
		{
			$eTable= str_replace(MPREFIX,"",$tbl);
			e107::getDB()->db_Select($eTable, "*");
			$text .= "\t<dbTable name='$eTable'>\n";
			while($row = e107::getDB()-> db_Fetch())
			{
				$text .= "\t\t<item>\n";
				foreach($row as $key=>$val)
				{
					$text .= "\t\t\t<field name='".$key."'><![CDATA[".$val."]]></field>\n";
				}
				
				$text .= "\t\t</item>\n";
			}
			$text .= "\t</dbTable>\n";	
			
		}
		$text .= "\t</database>\n";
	}
	
	
	
	$text .= "</e107Export>";
	
	header('Content-type: application/xml', TRUE);
	header("Content-disposition: attachment; filename= e107Export_" . date("Y-m-d").".xml");
	header("Cache-Control: max-age=30");
	header("Pragma: public");
	echo $text;
	exit;			
}



function importTables()
{
	// TODO - Import of table data. 
		
}



function table_list()
{
	// grab default language lists.
	//TODO - a similar function is in db_verify.php. Should probably all be moved to mysql_class.php. 
	
	global $mySQLdefaultdb;
	$exclude = array();
	$exclude[] = "core";
	$exclude[] = "rbinary";
	$exclude[] = "parser";
	$exclude[] = "tmp";
	$exclude[] = "online";
	$exclude[] = "upload";
	$exclude[] = "user_extended_country";
	$exclude[] = "plugin";

	
	/*
	$exclude[] = "banlist";		$exclude[] = "banner";
	$exclude[] = "cache";		$exclude[] = "core";
			
	$exclude[] = "plugin";		$exclude[] = "user";
		$exclude[] = "userclass_classes";
			$exclude[] = "session";
	 		$exclude[] = "flood";
	$exclude[] = "stat_info";	$exclude[] = "stat_last";
	$exclude[] = "submit_news";	$exclude[] = "rate";
	$exclude[] = "stat_counter";$exclude[] = "user_extended";
	$exclude[] = "user_extended_struc";
	
	
	*/

	$tables = mysql_list_tables($mySQLdefaultdb);
	while (list($temp) = mysql_fetch_array($tables))
	{
		
		e107::getDB()->db_Rows();
		$t = mysql_query("SELECT * FROM ".$temp);
		$e107tab = str_replace(MPREFIX, "", $temp);
		
		$count = mysql_num_rows($t);
		if($count && (strpos($temp,$prefix)!==TRUE) && !in_array($e107tab,$exclude))
		{
			$tabs[$e107tab] = mysql_num_rows($t);	
		}
		$prefix = MPREFIX."lan_";
		$match = array();
	//	if(preg_match('/^'.$prefix.'(.*)/', $temp, $match))
		{
			$e107tab = str_replace(MPREFIX, "", $temp);
			$pos = strrpos($match[1],"_")+1;
			$core = substr(str_replace("lan_","",$e107tab),$pos);
		//	if (str_replace($exclude, "", $e107tab))
		//	{
			//	$tabs[$core] = $e107tab;
		//	}
		}
	}

	return $tabs;
}
/* Still needed?

function backup_core()
{
	global $pref, $sql;
	$tmp = base64_encode((serialize($pref)));
	if(!$sql->db_Insert("core", "'pref_backup', '{$tmp}' "))
	{
		$sql->db_Update("core", "e107_value='{$tmp}' WHERE e107_name='pref_backup'");
	}
}

*/


function verify_sql_record() // deprecated by db_verify.php ( i think). 
{
	global $emessage, $sql, $sql2, $sql3, $frm, $e107, $tp;

	$sql = e107::getDb();
	$sql2 = e107::getDb('sql2');
	$sql3 = e107::getDb('sql3');

	$tables = array();
	$tables[] = 'rate';
	$tables[] = 'comments';

	if(isset($_POST['delete_verify_sql_record']))
	{

		if(!varset($_POST['del_dbrec']))
		{
			$emessage->add('Nothing to delete', E_MESSAGE_DEBUG);
		}
		else
		{
			$msg = "ok, so you want to delete some records? not a problem at all!<br />";
			$msg .= "but, since this is still an experimental procedure, i won't actually delete anything<br />";
			$msg .= "instead, i will show you the queries that would be performed<br />";
			$text .= "<br />";
			$emessage->add($msg, E_MESSAGE_DEBUG);

			foreach($_POST['del_dbrec'] as $k => $v)
			{

				if($k == 'rate')
				{

					$keys = implode(", ", array_keys($v));
					$qry .= "DELETE * FROM rate WHERE rate_id IN (".$keys.")<br />";

				}
				elseif($k == 'comments')
				{

					$keys = implode(", ", array_keys($v));
					$qry .= "DELETE * FROM comments WHERE comment_id IN (".$keys.")<br />";

				}

			}

			$emessage->add($qry, E_MESSAGE_DEBUG);
			$emessage->add("<a href='".e_SELF."'>".DBLAN_13."</a>", E_MESSAGE_DEBUG);
		}
	}

	//Nothing selected
	if(isset($_POST['check_verify_sql_record']) && (!isset($_POST['table_rate']) && !isset($_POST['table_comments'])))
	{
		$_POST['check_verify_sql_record'] = '';
		unset($_POST['check_verify_sql_record']);
		$emessage->add(DBLAN_53, E_MESSAGE_WARNING);
	}

	if(!isset($_POST['check_verify_sql_record']))
	{
		//select table to verify
		$text = "
			<form method='post' action='".e_SELF."'>
				<fieldset id='core-db-verify-sql-tables'>
					<legend class='e-hideme'>".DBLAN_39."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='1'>
							<col style='width: 100%'></col>
						</colgroup>
						<thead>
							<tr>
								<th class='last'>".DBLAN_37."</th>
							</tr>
						</thead>
						<tbody>
		";
		foreach($tables as $t)
		{
			$text .= "
							<tr>
								<td>
									".$frm->checkbox('table_'.$t, $t).$frm->label($t, 'table_'.$t, $t)."
								</td>
							</tr>
					";
		}
		$text .= "
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$frm->admin_button('check_verify_sql_record', DBLAN_38)."
						".$frm->admin_button('back', DBLAN_13, 'back')."
					</div>
				</fieldset>
			</form>
		";

		$e107->ns->tablerender(DBLAN_10.' - '.DBLAN_39, $emessage->render().$text);
	}
	else
	{

		//function to sort the results
		function verify_sql_record_cmp($a, $b)
		{

			$orderby = array('type' => 'asc', 'itemid' => 'asc');

			$result = 0;
			foreach($orderby as $key => $value)
			{
				if($a[$key] == $b[$key])
					continue;
				$result = ($a[$key] < $b[$key]) ? - 1 : 1;
				if($value == 'desc')
					$result = - $result;
				break;
			}
			return $result;
		}

		//function to display the results
		//$err holds the error data
		//$ctype holds the tablename
		function verify_sql_record_displayresult($err, $ctype)
		{
			global $frm;

			usort($err, 'verify_sql_record_cmp');

			$text = "

					<fieldset id='core-core-db-verify-sql-records-{$ctype}'>
						<legend>".DBLAN_40." ".$ctype."</legend>
						<table cellpadding='0' cellspacing='0' class='adminlist'>
							<colgroup span='4'>
								<col style='width: 20%'></col>
								<col style='width: 10%'></col>
								<col style='width: 50%'></col>
								<col style='width: 20%'></col>
							</colgroup>
							<thead>
								<tr>
									<th>".DBLAN_41."</th>
									<th>".DBLAN_42."</th>
									<th>".DBLAN_43."</th>
									<th class='center last'>".DBLAN_44."</th>
								</tr>
							</thead>
							<tbody>
			";
			if(is_array($err) && !empty($err))
			{


				foreach($err as $k => $v)
				{
					$delkey = $v['sqlid'];
					$text .= "
									<tr>
										<td>{$v['type']}</td>
										<td>{$v['itemid']}</td>
										<td>".($v['table_exist'] ? DBLAN_45 : DBLAN_46)."</td>
										<td class='center'>
											".$frm->checkbox('del_dbrec['.$ctype.']['.$delkey.'][]', '1').$frm->label(DBLAN_47, 'del_dbrec['.$ctype.']['.$delkey.'][]', '1')."
										</td>
									</tr>
					";
				}

			}
			else
			{
				$text .= "
								<tr>
									<td colspan='4'>{$err}</td>
								</tr>
				";
			}
			$text .= "
							</tbody>
						</table>
					</fieldset>
			";

			return $text;
		}

		function verify_sql_record_gettables()
		{
			global $sql2;

			//array which will hold all db tables
			$dbtables = array();

			//get all tables in the db
			$sql2->db_Select_gen("SHOW TABLES");
			while($row2 = $sql2->db_Fetch())
			{
				$dbtables[] = $row2[0];
			}
			return $dbtables;
		}

		$text = "<form method='post' action='".e_SELF.(e_QUERY ? '?'.e_QUERY : '')."'>";

		//validate rate table records
		if(isset($_POST['table_rate']))
		{

			$query = "
			SELECT r.*
			FROM #rate AS r
			WHERE r.rate_id!=''
			ORDER BY r.rate_table, r.rate_itemid";
			$data = array('type' => 'rate', 'table' => 'rate_table', 'itemid' => 'rate_itemid', 'id' => 'rate_id');

			if(!$sql->db_Select_gen($query))
			{
				$text .= verify_sql_record_displayresult(DBLAN_49, $data['type']);
			}
			else
			{
				//the master error array
				$err = array();

				//array which will hold all db tables
				$dbtables = verify_sql_record_gettables();

				while($row = $sql->db_Fetch())
				{

					$ctype = $data['type'];
					$cid = $row[$data['id']];
					$citemid = $row[$data['itemid']];
					$ctable = $row[$data['table']];

					//if the rate_table is an existing table, we need to do more validation
					//else if the rate_table is not an existing table, this is an invalid reference
					//FIXME Steve: table is never found without MPREFIX; Multi-language tables?
					if(in_array(MPREFIX.$ctable, $dbtables))
					{

						$sql3->db_Select_gen("SHOW COLUMNS FROM ".MPREFIX.$ctable);
						while($row3 = $sql3->db_Fetch())
						{
							//find the auto_increment field, since that's the most likely key used
							if($row3['Extra'] == 'auto_increment')
							{
								$aif = $row3['Field'];
								break;
							}
						}

						//we need to check if the itemid (still) exists in this table
						//if the record is not found, this could well be an obsolete record
						//if the record is found, we need to keep this record since it's a valid reference
						if(!$sql2->db_Select("{$ctable}", "*", "{$aif}='{$citemid}' ORDER BY {$aif} "))
						{
							$err[] = array('type' => $ctable, 'sqlid' => $cid, 'table' => $ctable, 'itemid' => $citemid, 'table_exist' => TRUE);
						}

					}
					else
					{
						$err[] = array('type' => $ctable, 'sqlid' => $cid, 'table' => $ctable, 'itemid' => $citemid, 'table_exist' => FALSE);
					}
				}

				$text .= verify_sql_record_displayresult(($err ? $err : DBLAN_54), $ctype);
			}
		}

		//validate comments table records
		if(isset($_POST['table_comments']))
		{

			$query = "
			SELECT c.*
			FROM #comments AS c
			WHERE c.comment_id!=''
			ORDER BY c.comment_type, c.comment_item_id";
			$data = array('type' => 'comments', 'table' => 'comment_type', 'itemid' => 'comment_item_id', 'id' => 'comment_id');

			if(!$sql->db_Select_gen($query))
			{
				$text .= verify_sql_record_displayresult(DBLAN_49, $data['type']);
			}
			else
			{

				//the master error array
				$err = array();

				//array which will hold all db tables
				$dbtables = verify_sql_record_gettables();

				//get all e_comment files and variables
				require_once (e_HANDLER."comment_class.php");
				$cobj = new comment();
				$e_comment = $cobj->get_e_comment();

				while($row = $sql->db_Fetch())
				{

					$ctype = $data['type'];
					$cid = $row[$data['id']];
					$citemid = $row[$data['itemid']];
					$ctable = $row[$data['table']];

					//for each comment we need to validate the referencing record exists
					//we need to check if the itemid (still) exists in this table
					//if the record is not found, this could well be an obsolete record
					//if the record is found, we need to keep this record since it's a valid reference


					// news
					if($ctable == "0")
					{
						if(!$sql2->db_Select("news", "*", "news_id='{$citemid}' "))
						{
							$err[] = array('type' => 'news', 'sqlid' => $cid, 'table' => $ctable, 'itemid' => $citemid, 'table_exist' => TRUE);
						}
						//	article, review or content page
					}
					elseif($ctable == "1")
					{

					//	downloads
					}
					elseif($ctable == "2")
					{
						if(!$sql2->db_Select("download", "*", "download_id='{$citemid}' "))
						{
							$err[] = array('type' => 'download', 'sqlid' => $cid, 'table' => $ctable, 'itemid' => $citemid, 'table_exist' => TRUE);
						}

					//	poll
					}
					elseif($ctable == "4")
					{
						if(!$sql2->db_Select("polls", "*", "poll_id='{$citemid}' "))
						{
							$err[] = array('type' => 'polls', 'sqlid' => $cid, 'table' => $ctable, 'itemid' => $citemid, 'table_exist' => TRUE);
						}

					//	userprofile
					}
					elseif($ctable == "profile")
					{
						if(!$sql2->db_Select("user", "*", "user_id='{$citemid}' "))
						{
							$err[] = array('type' => 'user', 'sqlid' => $cid, 'table' => $ctable, 'itemid' => $citemid, 'table_exist' => TRUE);
						}

					//else if this is a plugin comment
					}
					elseif(isset($e_comment[$ctable]) && is_array($e_comment[$ctable]))
					{
						$var = $e_comment[$ctable];
						$qryp = '';
						//new method must use the 'qry' variable
						if(isset($var) && $var['qry'] != '')
						{
							if($installed = $sql2->db_Select("plugin", "*", "plugin_path = '".$var['plugin_path']."' AND plugin_installflag = '1' "))
							{
								$qryp = str_replace("{NID}", $citemid, $var['qry']);
								if(!$sql2->db_Select_gen($qryp))
								{
									$err[] = array('type' => $ctable, 'sqlid' => $cid, 'table' => $ctable, 'itemid' => $citemid, 'table_exist' => TRUE);
								}
							}
							//old method
						}
						else
						{
							if(!$sql2->db_Select($var['db_table'], $var['db_title'], $var['db_id']." = '{$citemid}' "))
							{
								$err[] = array('type' => $ctable, 'sqlid' => $cid, 'table' => $ctable, 'itemid' => $citemid, 'table_exist' => TRUE);
							}
						}
						//in all other cases
					}
					else
					{
						$err[] = array('type' => $ctable, 'sqlid' => $cid, 'table' => $ctable, 'itemid' => $citemid, 'table_exist' => FALSE);
					}

				}

				$text .= verify_sql_record_displayresult(($err ? $err : DBLAN_54), $ctype);
			}
		}

		$text .= "
				<div class='buttons-bar center'>
					".$frm->admin_button('delete_verify_sql_record', DBLAN_48, 'delete')."
					".$frm->admin_button('verify_sql_record', DBLAN_13, 'back')."

				</div>
			</form>
		";

		$e107->ns->tablerender(DBLAN_10.' - '.DBLAN_50, $emessage->render().$text);
	}
}



/**
 * Handle page DOM within the page header
 *
 * @return string JS source
 */
function headerjs()
{
	require_once (e_HANDLER.'js_helper.php');
	$ret = "
		<script type='text/javascript'>
			if(typeof e107Admin == 'undefined') var e107Admin = {}

			/**
			 * OnLoad Init Control
			 */
			e107Admin.initRules = {
				'Helper': true,
				'AdminMenu': false
			}
		</script>
		<script type='text/javascript' src='".e_FILE_ABS."jslib/core/admin.js'></script>
	";

	return $ret;
}
?>