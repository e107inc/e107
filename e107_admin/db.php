<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Database Utilities
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/db.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

require_once ("../class2.php");
$theme = e107::getPref('sitetheme');
define("EXPORT_PATH","{e_THEME}".$theme."/install/");

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

if(isset($_POST['db_update']) || varset($_GET['mode'])=='db_update')
{
	header("location: ".e_ADMIN."e107_update.php");
	exit();
}

if(isset($_POST['verify_sql']) || varset($_GET['mode'])=='verify_sql')
{
	header("location: ".e_ADMIN."db_verify.php");
	exit();
}

if(isset($_POST['exportXmlFile']))
{
	if(exportXmlFile($_POST['xml_prefs'],$_POST['xml_tables'],$_POST['package_images']))
	{
		$emessage = eMessage::getInstance();
		$emessage->add(LAN_SUCCESS, E_MESSAGE_SUCCESS);
	}
	
}

require_once ("auth.php");
require_once (e_HANDLER."form_handler.php");
$frm = new e_form();
$st = new system_tools;


/* No longer needed after XML feature added. 

if(isset($_POST['backup_core']) || $_GET['mode']=='backup_core')
{
	backup_core();
	//message_handler("MESSAGE", DBLAN_1);
	$emessage->add(DBLAN_1, E_MESSAGE_SUCCESS);
}

*/











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
			'sc_override_scan'		=> array('diz'=>DBLAN_55, 'label'=> DBLAN_56),
			'convert_to_utf8'		=> array('diz'=>'Convert Database to UTF-8','label'=>'Convert DB to UTF-8')
		);
		
		//TODO Merge db_verify.php into db.php 
		

		
		if(isset($_POST['delplug']))
		{
			$this->delete_plugin_entry($_POST['pref_type']);	
		}
		
		if(isset($_POST['upload']))
		{	
			$this->importXmlFile();		
		}
		
		if(isset($_POST['delpref']) || (isset($_POST['delpref_checked']) && isset($_POST['delpref2'])))
		{
			$this->del_pref_val($_POST['pref_type']);
		}
		
		if(isset($_POST['verify_sql_record']) || varset($_GET['mode'])=='verify_sql_record' || isset($_POST['check_verify_sql_record']) || isset($_POST['delete_verify_sql_record']))
		{
			// $this->verify_sql_record();  - currently performed in db_verify.php
		}
		
		if(isset($_POST['importForm']) ||  $_GET['mode']=='importForm')
		{
			$this->importForm();	
		}
		
				
		if(isset($_POST['convert_to_utf8']) ||  $_GET['mode']=='convert_to_utf8')
		{
			$this->convertUTF8Form();	
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
			$type = isset($_GET['type']) ? $_GET['type'] : "core";
			$this->pref_editor($type);
		}
		
		if(isset($_POST['sc_override_scan']) || $_GET['mode']=='sc_override_scan')
		{
			$this->scan_override();
		}
		
		if(isset($_POST['plugin_scan']) || e_QUERY == "plugin" || isset($_POST['delplug']) || $_GET['mode']=='plugin_scan')
		{
			$this->plugin_viewscan();
		}
		
		if(vartrue($_POST['perform_utf8_convert']))
		{
			$this->perform_utf8_convert();
		}
		
		if(!vartrue($_GET['mode']))
		{		
			$this->render_options();
		}
		

					
	}
	
	private function convertUTF8Form()
	{
		$emessage = e107::getMessage();
		$frm = e107::getForm();
		//TODO a function to call the e107_config information in e107_class.php. 
		require(e_BASE."e107_config.php");	
		$dbtable = $mySQLdefaultdb;
		
		//TODO LAN
		$message = '
			This function will permanently modify all tables in your database. ('.$mySQLdefaultdb.')<br />
			It is <b>HIGHLY</b> recommended that you backup your database first.<br />
			If possible use a copy of your database.<br />
			Do not forget to purge unnecessary input - e.g. old chatbox messages, pm, …<br />
			as well as to set the maintenance flag to main admins only.<br />
			<br />
			Be sure to click the “Convert Database” button only once.<br />
			The conversion process can take up to one minute or much much more depending on the size of your database.<br />
			<br />
			Known problems (list non-exhaustive):
			<ul>
			<li>The MySQL user needs privileges to ALTER the database - this is mandatory.</li>
			<li>The conversion does not work with serialised arrays.<br />
			<strong>Be sure</strong> you followed all steps of the upgrade process first.</li>
			<li>It should work without troubles for databases of sites using only UTF-8 charset. Probably not with other charsets.</li>
			<li>The function uses the information_schema database for now.</li>
			</ul>			
			';

		$emessage->add($message, E_MESSAGE_WARNING);

		$text = "
			<form method='post' action='".e_SELF."' id='linkform'>
				<fieldset id='core-db-utf8-convert'>
					<legend class='e-hideme'>"."Convert Database"."</legend>
					<div class='buttons-bar center'>
						".$frm->admin_button('perform_utf8_convert', "Convert Database")."
					</div>
				</fieldset>
			</form>";
		
		e107::getRender()->tablerender("Convert Database to UTF-8", $emessage->render().$text);	
						   
	}
	
	private function perform_utf8_convert()
	{
		require(e_BASE."e107_config.php");
		
		$dbtable = $mySQLdefaultdb;
		
		//TODO Add a check to be sure the database is not already utf-8. 
		// yep, needs more methods - possibly a class in e107_handler
		
		$sql = e107::getDb();	
		$mes = e107::getMessage();
		
		$ERROR = FALSE;
			
		if(!mysql_query("USE information_schema;"))
		{
			$mes->add("Couldn't read information_schema", E_MESSAGE_ERROR);
			return;
		}
		
		$queries = array();		
		$queries[] = $this->getQueries("SELECT CONCAT('ALTER TABLE ', table_name, ' MODIFY ', column_name, ' ', REPLACE(column_type, 'char', 'binary'), ';') FROM columns WHERE table_schema = '".$dbtable."' and data_type LIKE '%char%';");
		$queries[] = $this->getQueries("SELECT CONCAT('ALTER TABLE ', table_name, ' MODIFY ', column_name, ' ', REPLACE(column_type, 'text', 'blob'), ';') FROM columns WHERE table_schema = '".$dbtable."' and data_type LIKE '%text%';");
		
		$queries2 = array();	
		$queries2[] = $this->getQueries("SELECT CONCAT('ALTER TABLE ', table_name, ' MODIFY ', column_name, ' ', column_type, ' CHARACTER SET utf8;') FROM columns WHERE table_schema = '".$dbtable."' and data_type LIKE '%char%';");
		$queries2[] = $this->getQueries("SELECT CONCAT('ALTER TABLE ', table_name, ' MODIFY ', column_name, ' ', column_type, ' CHARACTER SET utf8;') FROM columns WHERE table_schema = '".$dbtable."' and data_type LIKE '%text%';");
		
		
		mysql_query("USE ".$dbtable);
			
		foreach($queries as $qry)
		{
			foreach($qry as $q)
			{
				if(!$sql->db_Query($q))
				{
					$mes->add($q, E_MESSAGE_ERROR);	
					$ERROR = TRUE;
				}
			}			
		}
		
		//------------
				
		$result = mysql_list_tables($dbtable);
		while ($row = mysql_fetch_array($result, MYSQL_NUM))
		{
   			$table = $row[0]; 
			$tab_query = "ALTER TABLE ".$table." charset=utf8; ";
			if(!$sql->db_Query($tab_query))
			{
				$mes->add($tab_query, E_MESSAGE_ERROR);	
				$ERROR = TRUE;	
			}
		}
		
		// ---------------
		
		foreach($queries2 as $qry)
		{
			foreach($qry as $q)
			{
				if(!$sql->db_Query($q))
				{
					$mes->add($q, E_MESSAGE_ERROR);
					$ERROR = TRUE;	
				}
			}			
		}

		//------------
		
		$lastQry = "ALTER DATABASE `".$dbtable."` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
		
		if(!$sql->db_Query($lastQry))
		{
			$mes->add($lastQry, E_MESSAGE_ERROR);		
		}
		elseif($ERROR != TRUE)
		{
			$message = "Database Converted successfully to UTF-8. <br />
			Please now add the following line to your e107_config.php file:<br /> 
			<b>\$mySQLcharset   = 'utf8';</b>
			";
			
			$mes->add($message, E_MESSAGE_SUCCESS);			
		}
		
		
	}
	
	function getQueries($query)
	{
		if(!$result = mysql_query($query))
		{
			$mes->add("Query Failed", E_MESSAGE_ERROR);
			return;
		}
		while ($row = mysql_fetch_array($result, MYSQL_NUM))
		{
   			 $qry[] = $row[0]; 
		}
		
		return $qry;	
	}


	/**
	 * Delete selected preferences. 
	 * @return none
	 */	
	private function del_pref_val($mode='core')
	{
		global $emessage;
		
		$deleted_list = "";
		
		$config = ($mode == 'core' || $mode='') ? e107::getConfig('core') : e107::getPlugConfig($mode);
		
		// Single Pref Deletion	using button
		if(varset($_POST['delpref']))
		{
			$delpref = key($_POST['delpref']);
			if($config->remove($delpref))
			{
				$deleted_list .= "<li>".$delpref."</li>";	
			}						
		}
	
		// Multiple Pref deletion using checkboxes
		if(varset($_POST['delpref2']))
		{	
			foreach($_POST['delpref2'] as $k => $v)
			{
				if($config->remove($k))
				{
					$deleted_list .= "<li>".$k."</li>";	
				}						
			}
		}	
		
		if($deleted_list && $config->save())
		{			
			$emessage->add(LAN_DELETED."<ul>".$deleted_list."</ul>");
			e107::getCache()->clear();
		}

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
	 * @return none
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
	 * @return none
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
	 * @return  none
	 */	
	private function exportXmlForm()
	{
		$emessage = eMessage::getInstance();
		$frm = e107::getSingleton('e_form');
		
		
	//TODO LANs
		
		$text = "<form method='post' action='".e_SELF."?".e_QUERY."' id='core-db-export-form'>
			<fieldset id='core-db-export'>
			<legend class='e-hideme'>Export Options</legend>
				<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='2'>
					
					<col style='width: 80%'></col>
					<col style='width: 20%'></col>
				</colgroup>	
				<thead>
				<tr>
					<th>".$frm->checkbox_toggle('check-all-verify', 'xml_prefs')." Preferences</th>
					<th class='right'>Rows</th>
					
				</tr>	
				</thead>
				<tbody>
	
				";
	
					$pref_types  = e107::getConfig()->aliases;
					unset($pref_types['core_old'],$pref_types['core_backup']);		
			//		$exclusions = array('core_old'=>1,'core_backup'=>1);
				//	$filteredprefs = array_diff($pref_types,$exclusions);
					
					foreach($pref_types as $key=>$description)
					{
						$checked = ($_POST['xml_prefs'][$key] == $key) ? 1: 0;	

						$text .= "<tr>
							<td>
								".$frm->checkbox("xml_prefs[".$key."]", $key, $checked)."
							".LAN_PREFS.": ".$key."</td>
							<td>&nbsp;</td>
	
							</tr>";
					
					}
					$text .= "</tbody>
				</table>
				<table cellpadding='0' cellspacing='0' class='adminlist'>
				
				<colgroup span='2'>
					
					<col style='width: 80%'></col>
					<col style='width: 20%'></col>
				</colgroup>	
				<thead>
				<tr>
					<th>".$frm->checkbox_toggle('check-all-verify', 'xml_tables')."Tables</th>
					<th class='right'>Rows</th>
					
				</tr>	
				</thead>
				<tbody>\n";
				
					$tables = table_list();
					
					foreach($tables as $name=>$count)
					{	
						$checked = ($_POST['xml_tables'][$name] == $name) ? 1: 0;			
						$text .= "<tr>					
							<td>
								".$frm->checkbox("xml_tables[".$name."]", $name, $checked)." Table Data: ".$name." 
							</td>
							<td class='right'>$count</td>
						</tr>";
					}
	
					$text .="
					
					</tbody>
				</table>
				
				<table cellpadding='0' cellspacing='0' class='adminlist'>
				<colgroup span='2'>
					
					<col style='width: 80%'></col>
					<col style='width: 20%'></col>
				</colgroup>	
				<thead>
				<tr>
					<th colspan='2'>".LAN_OPTIONS."</th>
				</tr>	
				</thead>
				<tbody>
				<tr>
						<td colspan='2'>";
						$checked = (vartrue($_POST['package_images'])) ? 1: 0;	
						$text .= $frm->checkbox("package_images",'package_images', $checked)." Convert paths and package images and xml into: <i>".e107::getParser()->replaceConstants(EXPORT_PATH)."</i>    
					
						</td>
					</tr>
				</tbody>
				</table>
				
				<div class='buttons-bar center'>
					".$frm->admin_button('exportXmlFile', "Export File", 'exportXmlFile')."
				</div>
			</fieldset>
		</form>	";
		
	
		e107::getRender()->tablerender("Export Options",$emessage->render(). $text);		
		
		
	}

	/**
	 * Import XML Dump
	 * @return none
	 */
	private function importXmlFile()
	{
		$ret = e107::getSingleton('xmlClass')->e107Import($_FILES['file_userfile']['tmp_name'][0]);

		foreach($ret['success'] as $table)
		{
			eMessage::getInstance()->add("Inserted $table", E_MESSAGE_SUCCESS);		
		}
		
		foreach($ret['failed'] as $table)
		{
			eMessage::getInstance()->add("Failed to Insert $table", E_MESSAGE_ERROR);		
		}				
	}
	
	/**
	 * Optimize SQL
	 * @return none
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
	 * @return string text for display
	 */
	private function pref_editor($type='core')
	{
		//TODO Add drop-down for editing personal perfs also. ie. user pref of self. (admin)
		
		global $pref, $e107, $emessage, $frm;
		

		
		$config = ($type == 'core') ? e107::getConfig('core') : e107::getPlugConfig($type);

		$spref = $config->getPref();
	
		ksort($spref);	
	
		$text = "
				<form method='post' action='".e_ADMIN."db.php?mode=".$_GET['mode']."&amp;type=".$type."' id='pref_edit'>
					<fieldset id='core-db-pref-edit'>
						<legend class='e-hideme'>".DBLAN_20."</legend>";
		
		$text .= "<select class='tbox' name='type_select' onchange='urljump(this.options[selectedIndex].value)' >
		<option value='".e_ADMIN."db.php?mode=".$_GET['mode']."&amp;type=core'>Core</option>\n";
		
	//	e107::getConfig($type)->aliases
		
		e107::getDb()->db_Select_gen("SELECT e107_name FROM #core WHERE e107_name LIKE ('plugin_%') ORDER BY e107_name");
		while ($row = e107::getDb()->db_Fetch())
		{
			$key = str_replace("plugin_","",$row['e107_name']);
			$selected = (varset($_GET['type'])==$key) ? "selected='selected'" : "";
			$text .= "<option value='".e_ADMIN."db.php?mode=".$_GET['mode']."&amp;type=".$key."' {$selected}>".ucwords($key)."</option>\n";	
		}				
		
		
		$text .= "</select></div>
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
	
		foreach($spref as $key => $val)
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
							".$frm->admin_button('delpref_checked', LAN_DELCHECKED, 'delete')."
							".$frm->admin_button('back', LAN_BACK, 'back')."
							<input type='hidden' name='pref_type' value='".$type."' />
						</div>
					</fieldset>
				</form>\n\n";
				
		e107::getRender()->tablerender(DBLAN_10.' :: '.DBLAN_20." :: ".ucwords($type), $emessage->render().$text);
	
		return $text;
	}
	
	/**
	 * Preferences Editor
	 * @return none
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
	 * @return none
	 */		
	private function plugin_viewscan()
	{
		$error_messages = array(0 => DBLAN_31, 1 => DBLAN_32, 2 => DBLAN_33, 3 => DBLAN_34);
		$error_image = array("integrity_pass.png", "integrity_fail.png", "warning.png", "blank.png");
	
	
	
		global $e107;
		$sql = e107::getDb();
		$tp = e107::getParser();
		$frm = e107::getForm();
		$emessage = e107::getMessage();

		
		
	
		require_once (e_HANDLER."plugin_class.php");
		$ep = new e107plugin();
		$ep->update_plugins_table(); // scan for e_xxx changes and save to plugin table.
		$ep->save_addon_prefs(); // generate global e_xxx_list prefs from plugin table.
	
		/* we all are awaiting for PHP5 only support - method chaining...
		$emessage->add(DBLAN_22.' - '.DBLAN_23, E_MESSAGE_SUCCESS)
				 ->add("<a href='".e_SELF."'>".LAN_BACK."</a>", E_MESSAGE_SUCCESS)
				 ->add(DBLAN_30);
		*/
	
		$emessage->add(DBLAN_23, E_MESSAGE_SUCCESS);
		$emessage->add("<a href='".e_SELF."'>".LAN_BACK."</a>", E_MESSAGE_SUCCESS);
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
			e107::loadLanFiles($row['plugin_path'],'admin');
			
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


/**
 * Export XML File and Copy Images.
 * @param object $prefs
 * @param object $tables
 * @param object $debug [optional]
 * @return none
 */
function exportXmlFile($prefs,$tables,$package=FALSE,$debug=FALSE)
{
	$xml = e107::getSingleton('xmlClass');
	$tp = e107::getParser();
	$emessage = eMessage::getInstance();
	
	//TODO LANs
	
	if(vartrue($package))
	{
		
		$xml->convertFilePaths = TRUE;
		$xml->filePathDestination = EXPORT_PATH;
		$xml->filePathPrepend = array(
			'news_thumbnail'	=> "{e_IMAGE}newspost_images/"
		);
		
		
		$desinationFolder = $tp->replaceConstants($xml->filePathDestination);
	
		if(!is_writable($desinationFolder))
		{			
			$emessage->add($desinationFolder." is not writable", E_MESSAGE_ERROR);
			return ;
		}
	}
	

	if($xml->e107Export($prefs,$tables,$debug))
	{
		$emessage->add("Created: ".$desinationFolder."install.xml", E_MESSAGE_SUCCESS);
		if(varset($xml->fileConvertLog))
		{
			foreach($xml->fileConvertLog as $oldfile)
			{
				$file = basename($oldfile);
				$newfile = $desinationFolder.$file;
				if($oldfile == $newfile || (copy($oldfile,$newfile)))
				{
					$emessage->add("Copied: ".$newfile, E_MESSAGE_SUCCESS);
				}
				else
				{
					$emessage->add("Couldn't copy: ".$newfile, E_MESSAGE_ERROR);	
				}			
			}
		}
	}

}



function table_list()
{
	// grab default language lists.
	//TODO - a similar function is in db_verify.php. Should probably all be moved to mysql_class.php. 
	
	$exclude = array();
	$exclude[] = "core";
	$exclude[] = "rbinary";
	$exclude[] = "parser";
	$exclude[] = "tmp";
	$exclude[] = "online";
	$exclude[] = "upload";
	$exclude[] = "user_extended_country";
	$exclude[] = "plugin";
	
	$coreTables = e107::getDb()->db_TableList('nolan');

	$tables = array_diff($coreTables,$exclude);
	
	foreach($tables as $e107tab)
	{		
		$count = e107::getDb()->db_Select_gen("SELECT * FROM #".$e107tab);
			
		if($count)
		{
			$tabs[$e107tab] = $count; 
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
			$emessage->add("<a href='".e_SELF."'>".LAN_BACK."</a>", E_MESSAGE_DEBUG);
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
						".$frm->admin_button('back', LAN_BACK, 'back')."
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
									<th class='center last'>".LAN_OPTIONS."</th>
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
											".$frm->checkbox('del_dbrec['.$ctype.']['.$delkey.'][]', '1').$frm->label(LAN_DELETE, 'del_dbrec['.$ctype.']['.$delkey.'][]', '1')."
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
					".$frm->admin_button('delete_verify_sql_record', LAN_DELCHECKED, 'delete')."
					".$frm->admin_button('verify_sql_record', LAN_BACK, 'back')."

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