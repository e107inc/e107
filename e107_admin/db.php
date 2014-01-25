<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration - Database Utilities
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

$frm = e107::getForm();
$mes = e107::getMessage();

/*
 * Execute trigger
 */
if(isset($_POST['db_execute']))
{
	$type = key($_POST['db_execute']);
	
	if(!varset($_POST['db_execute']))
	{
		$mes->add(DBLAN_53, E_MESSAGE_WARNING);
	}
	else
	{
		$_POST[$type] = true;
	}
}





if(isset($_POST['exportXmlFile']))
{
	if(exportXmlFile($_POST['xml_prefs'],$_POST['xml_tables'],$_POST['package_images']))
	{
		$mes = e107::getMessage();
		$mes->add(LAN_SUCCESS, E_MESSAGE_SUCCESS);
	}

}

if(e_AJAX_REQUEST )
{

	session_write_close();
	while (@ob_end_clean()); 

	if(varset($_GET['mode']) == 'backup') //FIXME - not displaying progress until complete. Use e-progress?
	{
		echo "Starting file backup...<br />";
		
		$data = array();
		$data[] = e_MEDIA;
		$data[] = e_LOG;
		$data[] = e_IMPORT;
		$data[] = e_TEMP;
		$data[] = e_SYSTEM."filetypes.xml";
		$data[] = e_THEME.e107::getPref('sitetheme');
		
		$plugins = e107::getPlugin()->getOtherPlugins();
		foreach($plugins as $dir)
		{
			$data[] = e_PLUGIN.$dir;
		}
		
		$newFile = eHelper::title2sef(SITENAME)."_".date("Y-m-d-H-i-s");
			
		$zip = e107::getFile()->zip($data, e_BACKUP.$newFile.".zip");	
			
		echo "File backup complete! <small>(".$zip.")</small><br />";
		
		echo "Starting database backup...<br />";

		$dbfile = e107::getDb()->backup('*', $newFile.".sql", array('nologs'=>1, 'droptable'=>1));
		
		echo "Database backup complete! <small>(".$dbfile.")</small>";

		e107::getAdminLog()->addSuccess($zip." ".$dbfile, false)->save('Full site backup completed.');
		
	}
	
	exit;
	
}

require_once ("auth.php");

$st = new system_tools;


/* No longer needed after XML feature added.

if(isset($_POST['backup_core']) || $_GET['mode']=='backup_core')
{
	backup_core();
	//message_handler("MESSAGE", DBLAN_1);
	$mes->add(DBLAN_1, E_MESSAGE_SUCCESS);
}

*/











require_once ("footer.php");

class system_tools
{

	public $_options = array();
	
	private $_utf8_exclude = array();


	function __construct()
	{
		global $mySQLdefaultdb;
		
		$this->_utf8_exclude = array(MPREFIX."core");

		

		$this->_options = array(
			"db_update"				=> array('diz'=>DBLAN_15, 'label'=>DBLAN_16),
			"verify_sql"			=> array('diz'=>DBLAN_4, 'label'=>DBLAN_5),
			'optimize_sql'			=> array('diz'=>DBLAN_6, 'label'=> DBLAN_7),
			'plugin_scan'			=> array('diz'=>DBLAN_28, 'label'=> DBLAN_29),
			'pref_editor'			=> array('diz'=>DBLAN_19, 'label'=> DBLAN_20),
		//	'backup_core'			=> array('diz'=>DBLAN_8, 'label'=> DBLAN_9),
		//	'verify_sql_record'		=> array('diz'=>DBLAN_35, 'label'=> DBLAN_36),
			'importForm'			=> array('diz'=>DBLAN_59, 'label'=> DBLAN_59),
			'exportForm'			=> array('diz'=>DBLAN_58, 'label'=> DBLAN_58),
			'sc_override_scan'		=> array('diz'=>DBLAN_55, 'label'=> DBLAN_56),
			'convert_to_utf8'		=> array('diz'=>'Check Database Charset','label'=>'Check Charset'),
			'correct_perms'			=> array('diz'=>'Correct File and Directory permissions','label'=>'Correct Perms'),
			'backup'				=> array('diz'=>'Backup Database, Files and Folders','label'=>'Backup Site')									
		);
		
		if(vartrue($_SERVER['E_DEV']))
		{
			$this->_options['multisite'] = array('diz'=>'', 'label'=> 'Multi-Site');	
		}

		$this->_options = multiarray_sort($this->_options, 'label');
				
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
		
		if(isset($_POST['verify_sql']) || varset($_GET['mode'])=='verify_sql')
		{
			require_once(e_HANDLER."db_verify_class.php");
			$dbv = new db_verify;
			$dbv->backUrl = e_SELF."?mode=verify_sql";
			$dbv->verify();
			return;
		}
		
	
		
	//	if(isset($_POST['verify_sql_record']) || varset($_GET['mode'])=='verify_sql_record' || isset($_POST['check_verify_sql_record']) || isset($_POST['delete_verify_sql_record']))
	//	{
		
			 //$this->verify_sql_record(); // - currently performed in db_verify_class.php
	//	}

		if(isset($_POST['importForm']) ||  $_GET['mode']=='importForm')
		{
			$this->importForm();
		}
		
		if(isset($_POST['db_update']) || varset($_GET['mode'])=='db_update') // Requires further testing. 
		{
		//	header("location: ".e_ADMIN."e107_update.php");
			require_once(e_ADMIN."update_routines.php");
			new e107Update($dbupdate);
			return;
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
			$this->plugin_viewscan('refresh');
		}
		
		if(isset($_POST['create_multisite']))
		{
			$this->multiSiteProcess();	
		}	

		if(vartrue($_POST['perform_utf8_convert']))
		{
			$this->perform_utf8_convert();
			return;
		}
		
		if(varset($_GET['mode'])=='correct_perms')
		{
			$this->correct_perms();	
			return;
		}
		
		if(varset($_GET['mode'])=='multisite')
		{
			$this->multiSite();	
			return;
		}
		
		if(varset($_GET['mode']) == 'backup')
		{
			$this->backup();
			return;
		}

		if(!vartrue($_GET['mode']) && !isset($_POST['db_execute']))
		{
			$this->render_options();
		}



	}


	private function backup()
	{
			
		$mes = e107::getMessage();
		
		$message = "This will create a database dump and a zipped backup of all non-core plugins, your site theme, your media files and system logs";
		$message .= "<br /><a class='e-ajax btn btn-success' data-loading-text='Please wait...' href='#backupstatus' data-src='".e_SELF."?mode=backup' >".LAN_CREATE."</a>";
		
		
		$mes->addInfo($message);
		
		$text = "<div id='backupstatus' style='margin-top:20px'></div>";
		
		
		e107::getRender()->tablerender(DBLAN_10.SEP."Backup", $mes->render().$text);		
	}



	/**
	 * Correct Folder and File permissions. 
	 */
	function correct_perms()
	{
		$mes = e107::getMessage();
		$fl = e107::getFile();
		ob_start();
		$fl->chmod(e_BASE);
		$fl->chmod(e_BASE."cron.php",0755);
		$errors = ob_get_clean();
		
		if($errors !='')
		{
			$mes->addError($errors);		
		}
		else
		{
			$mes->addSuccess("Folder and File permissions have been updated");			
		}
		
		e107::getRender()->tablerender(DBLAN_10.SEP."Correcting File and Directory Permissions", $mes->render());	
		
	}
	
	private function multiSiteProcess()
	{	
		$sql 		= e107::getDb('new');
		$mes 		= e107::getMessage();
		
		$user 		= $_POST['name'];
		$pass 		= $_POST['password'];
		$server 	= e107::getMySQLConfig('server'); // $_POST['server'];
		$database 	= $_POST['db'];
		$prefix		= $_POST['prefix'];
			
		if($connect = $sql->connect($server,$user, $pass, true))
		{
			$mes->addSuccess("Connecting to server");
			
			if(vartrue($_POST['createdb']))
			{
			
				if($sql->gen("CREATE DATABASE ".$database." CHARACTER SET `utf8`"))
				{
					$mes->addSuccess("Creating Database");
					
				//	$sql->gen("CREATE USER ".$user."@'".$server."' IDENTIFIED BY '".$pass."';");
					$sql->gen("GRANT ALL ON `".$database."`.* TO ".$user."@'".$server."';");
					$sql->gen("FLUSH PRIVILEGES;");		
				}
				else
				{
					$mes->addError("Creating Database");
					return;
				}
			}
			
			if(!$sql->database($database))
			{
				$mes->addError("Selecting database");
			}
					
			$mes->addSuccess("Selecting database");
					
			if($this->multiSiteCreateTables($sql, $prefix))
			{
				$coreConfig = e_CORE. "xml/default_install.xml";		
				$ret = e107::getXml()->e107Import($coreConfig, 'add', true, false, $sql); // Add core pref values
				$mes->addInfo(print_a($ret,true));
			}	
				
		}
		else
		{
			$mes->addSuccess("Connecting to server");
		}
		
		if($error = $sql->getLastErrorText())
		{
			$mes->addError($error);
		}
			
		//	print_a($_POST);

		
	}
	
	private function multiSiteCreateTables($sql, $prefix)
	{
		$mes = e107::getMessage();
		
		$sql_data = file_get_contents(e_CORE."sql/core_sql.php");
		$sql_data = preg_replace("#\/\*.*?\*\/#mis", '', $sql_data);		// Strip comments

		if (!$sql_data)
		{
			$mes->addError("Couldn't read core sql file");
		}

		preg_match_all("/create(.*?)(?:myisam|innodb);/si", $sql_data, $result );
		
		$sql->gen('SET NAMES `utf8`');

		foreach ($result[0] as $sql_table)
		{
			$sql_table = preg_replace("/create table\s/si", "CREATE TABLE ".$prefix, $sql_table);

			if (!$sql->gen($sql_table))
			{
				$mes->addError($sql->getLastErrorText());
				return false;
			}
			else
			{
				// $mes->addDebug($sql_table);
			}
		}	
		
		return true;
	}
	
	
	private function multiSite()
	{
		$mes = e107::getMessage();
		$frm = e107::getForm();
		
		e107::lan('core','installer');
		
		e107::getMySQLConfig('user'); // prefix|server|user|password|
		
		if(!isset($POST['create_multisite']))
		{
			$mes->addInfo("This will create a fresh installation of e107 at the domain you specify. Using your server administration software (eg. cPanel) - park your other domain on top of ".e_DOMAIN);
		}
		
		$text = $frm->open('multisite')."
			<table class='table table-striped' >
			<tr>
					<td><label for='server'>Parked Domain</label></td>
					<td>
						<input class='tbox' type='text' placeholder='mydomain.com' id='domain' name='domain' autofocus size='40' value='' maxlength='100' required='required' />
						<span class='field-help'>The parked domain which will become a new e107 website.</span>
					</td>
				</tr>
				";
			/*		
				$text .= "
				<tr>
					<td><label for='server'>".LANINS_024."</label></td>
					<td>
						<input class='tbox' type='text' id='server' name='server' autofocus size='40' value='localhost' maxlength='100' required='required' />
						<span class='field-help'>".LANINS_030."</span>
					</td>
				</tr>";
			*/
				$text .= "
				
				<tr>
					<td><label for='name'>".LANINS_025."</label></td>
					<td>
						<input class='tbox' type='text' name='name' id='name' size='40' value='".e107::getMySQLConfig('user')."' maxlength='100' required='required' />
						<span class='field-help'>".LANINS_031."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='password'>".LANINS_026."</label></td>
					<td>
						<input class='tbox' type='password' name='password' size='40' id='password' value='".e107::getMySQLConfig('password')."' maxlength='100'  />
						<span class='field-help'>".LANINS_032."</span>
					</td>
				</tr>
				";
			
				$text .= "
				<tr>
					<td><label for='db'>".LANINS_027."</label></td>
					<td class='input-inline'>
						<input type='text' name='db' size='20' id='db' value='' maxlength='100' required='required' />
						<label class='checkbox inline'><input type='checkbox' name='createdb' value='1' />".LANINS_028."</label>
						<span class='field-help'>".LANINS_033."</span>
					</td>
				</tr>";

			
				
				$text .= "
				
				<tr>
					<td><label for='prefix'>".LANINS_029."</label></td>
					<td>
						<input type='text' name='prefix' size='20' id='prefix' value='e107_'  pattern='[a-z0-9]*_$' maxlength='100' required='required' />
						<span class='field-help'>".LANINS_034."</span>
					</td>
				</tr>
	
	
			\n";	
		
		$text .= "
			
				<tr>
					<td><label for='u_name'>".LANINS_072."</label></td>
					<td>
						<input class='tbox' type='text' autofocus name='u_name' id='u_name' placeholder='admin' size='30' required='required' value='".USERNAME."' maxlength='60' />
						<span class='field-help'>".LANINS_073."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='d_name'>".LANINS_074."</label></td>
					<td>
						<input class='tbox' type='text' name='d_name' id='d_name' size='30' placeholder='Administrator'  value='".USERNAME."' maxlength='60' />
						<span class='field-help'>".LANINS_123."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='pass1'>".LANINS_076."</label></td>
					<td>
						<input type='password' name='pass1' size='30' id='pass1' value='' maxlength='60' required='required' />
						<span class='field-help'>".LANINS_124."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='pass2'>".LANINS_078."</label></td>
					<td>
						<input type='password' name='pass2' size='30' id='pass2' value='' maxlength='60' required='required' />
						<span class='field-help'>".LANINS_079."</span>
					</td>
				</tr>
				
				<tr>
					<td><label for='email'>".LANINS_080."</label></td>
					<td>
						<input type='text' name='email' size='30' id='email' required='required' placeholder='admin@mysite.com' value='".USEREMAIL."' maxlength='100' />
					<span class='field-help'>".LANINS_081."</span>
					</td>
				</tr>
			</table>
			<div class='buttons-bar text-center'>
			".$frm->admin_button('create_multisite',1,'submit','Create New Site')."
			</div>
			\n";
		
		$text .= $frm->close();
		
			
		e107::getRender()->tablerender(DBLAN_10.SEP."Multi-Site".SEP.$config['mySQLdefaultdb'], $mes->render().$text);
		
	}


	private function convertUTF8Form()
	{
		$mes 	= e107::getMessage();
		$frm 	= e107::getForm();
		$config = e107::getMySQLConfig();
		$sql 	= e107::getDb();
		
		$sql->gen('SHOW TABLE STATUS WHERE Name LIKE "'.$config['mySQLprefix'].'%" ');
		
		
		$text = "<table class='table adminlist'>
							<colgroup>
								<col style='width: auto' />
								<col style='width: auto' />
								<col style='width: auto' />
								<col style='width: auto' />
							</colgroup>
							<thead>
								<tr>
									
									<th>Table</th>
									<th>Engine</th>
									<th>Collation</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>";
		
		
		
		$invalidCollations = false;	
		while($row = $sql->fetch())
		{
				if(in_array($row['Name'],$this->_utf8_exclude))
				{
					continue;
				}
					
			
				$text .= "<tr>
					<td>".$row['Name']."</td>
					<td>".$row['Engine']."</td>
					<td>".$row['Collation']."</td>
					<td>".(($row['Collation'] == 'utf8_general_ci') ? ADMIN_TRUE_ICON : ADMIN_FALSE_ICON)."</td>
					</tr>";
			//	 print_a($row);
				
				if($row['Collation'] != 'utf8_general_ci')
				{
					$invalidCollations = true;	
				}

		}
		
		$text .= "</tbody></table>";


		if($invalidCollations == true)
		{
			//TODO LAN
				$message = '
				This function will permanently modify all tables in your database. ('.$config['mySQLdefaultdb'].')<br />
				It is <b>HIGHLY</b> recommended that you first backup your database and switch your site into maintenance mode. 
				<br />
				<br />
				Please note:
				<ul>
				<li>The conversion process can take up to one minute or much much more depending on the size of your database.</li>
				<li>The conversion does not work with serialized arrays.</li>
				<li>Be sure that you have followed all steps of the upgrade process first.</li>
				<li>Core prefs are ignored during the conversion process due to possibility of corruption. </li>
				</ul>
				';
	
			$mes->add($message, E_MESSAGE_WARNING);
	
			$text .= "
				<form method='post' action='".e_SELF."' id='linkform'>
					<fieldset id='core-db-utf8-convert'>
						<legend class='e-hideme'>"."Convert Database"."</legend>
						<div class='buttons-bar center'>
							".$frm->admin_button('perform_utf8_convert', "Convert non-UTF8 Tables",false,"Convert non-UTF8 Tables",'class=btn-success&data-loading-text=Please wait...')."
						</div>
					</fieldset>
				</form>";
			
		}
		else 
		{
			$mes->addSuccess("Your tables are using the correct character set.");	
		}


		e107::getRender()->tablerender(DBLAN_10.SEP."Check Charset".SEP.$config['mySQLdefaultdb'], $mes->render().$text);

	}

	private function perform_utf8_convert()
	{
		$config = e107::getMySQLConfig();
		$dbtable = $config['mySQLdefaultdb'];

		//TODO Add a check to be sure the database is not already utf-8.
		// yep, needs more methods - possibly a class in e107_handler

		$sql = e107::getDb('utf8-convert');
		$mes = e107::getMessage();

		$ERROR = FALSE;

	//	if(!$sql->gen("USE information_schema;"))
	//	{
	//		$mes->add("Couldn't read information_schema", E_MESSAGE_ERROR);
	//		return;
	//	}
		
	
		$queries = array();
		$queries[] = $this->getQueries("SELECT CONCAT('ALTER TABLE `', table_name, '` MODIFY ', column_name, ' ', REPLACE(column_type, 'char', 'binary'), ';') FROM information_schema.columns WHERE TABLE_SCHEMA = '".$dbtable."' AND TABLE_NAME LIKE '".$config['mySQLprefix']."%' AND  COLLATION_NAME != 'utf8_general_ci'  and data_type LIKE '%char%';");
		$queries[] = $this->getQueries("SELECT CONCAT('ALTER TABLE `', table_name, '` MODIFY ', column_name, ' ', REPLACE(column_type, 'text', 'blob'), ';') FROM information_schema.columns WHERE TABLE_SCHEMA = '".$dbtable."' AND TABLE_NAME LIKE '".$config['mySQLprefix']."%' AND  COLLATION_NAME != 'utf8_general_ci' and data_type LIKE '%text%';");

		$queries2 = array();
		$queries2[] = $this->getQueries("SELECT CONCAT('ALTER TABLE `', table_name, '` MODIFY ', column_name, ' ', column_type, ' CHARACTER SET utf8;') FROM information_schema.columns WHERE TABLE_SCHEMA ='".$dbtable."' AND TABLE_NAME LIKE '".$config['mySQLprefix']."%'  AND COLLATION_NAME != 'utf8_general_ci' and data_type LIKE '%char%';");
		$queries2[] = $this->getQueries("SELECT CONCAT('ALTER TABLE `', table_name, '` MODIFY ', column_name, ' ', column_type, ' CHARACTER SET utf8;') FROM information_schema.columns WHERE TABLE_SCHEMA = '".$dbtable."' AND TABLE_NAME LIKE '".$config['mySQLprefix']."%' AND  COLLATION_NAME != 'utf8_general_ci' and data_type LIKE '%text%';");


	//	$sql->gen("USE ".$dbtable);
		
		
	//	print_a($queries2);
	//	echo $mes->render();
	//	return;

	
		// Convert Text tables to Binary. 
		foreach($queries as $qry)
		{
					
			foreach($qry as $q)
			{
				if(!$sql->db_Query($q))
				{
					$mes->addError($q);
					$ERROR = TRUE;
				}
				else
				{
					$mes->addDebug($q);	
				}
			}
		}

		//------------

		// Convert Table Fields to utf8
		$sql2 = e107::getDb('sql2');
		
		$sql->gen('SHOW TABLE STATUS WHERE Collation != "utf8_general_ci" ');
		while ($row = $sql->fetch())
		{
   			$table = $row['Name'];
   			
			if(in_array($row['Name'], $this->_utf8_exclude))
			{
				continue;
			}
			
			
			$tab_query = "ALTER TABLE ".$table."  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci; ";

			//echo "TABQRT= ".$tab_query;

			if(!$sql2->db_Query($tab_query))
			{
				$mes->addError($tab_query);
				$ERROR = TRUE;
			}
			else
			{
				$mes->addDebug($tab_query);	
			}
		}

		// ---------------
		// Convert Table Fields back to Text/varchar etc. 
		foreach($queries2 as $qry)
		{
			foreach($qry as $q)
			{
				if(!$sql->db_Query($q))
				{
					$mes->addError($q);
					$ERROR = TRUE;
				}
				else
				{
					$mes->addDebug($q);	
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
			$message = "Database Converted successfully to UTF-8. ";
			//$message .= "<br />Please now add the following line to your e107_config.php file:<br /><b>\$mySQLcharset   = 'utf8';</b>";

			$mes->add($message, E_MESSAGE_SUCCESS);
			$mes->addSuccess("Please make sure you have the following line in your e107_config.php file:");
			$mes->addSuccess('$mySQLcharset   = "utf8";');
			
		}

		echo $mes->render();
	}

	function getQueries($query)
	{
		
		$mes = e107::getMessage();
		$sql = e107::getDb('utf8-convert');
		
		if($sql->gen($query))
		{
			while ($row = $sql->fetch(MYSQL_NUM))
			{
	   			 $qry[] = $row[0];
			}
		}
		else 
		{
			$mes->addError($query);	
		}

		return $qry;
		
		
		/*
		if(!$result = mysql_query($query))
		{
			$mes->addError("Query Failed: ".$query);
			return;
		}
		while ($row = mysql_fetch_array($result, MYSQL_NUM))
		{
   			 $qry[] = $row[0];
		}

		return $qry;
		 * */
	}


	/**
	 * Delete selected preferences.
	 * @return none
	 */
	private function del_pref_val($mode='core')
	{
		$mes = e107::getMessage();

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
			$mes->add(LAN_DELETED."<ul>".$deleted_list."</ul>");
			e107::getCache()->clear();
		}

	}

	private function delete_plugin_entry()
	{

		$mes = e107::getMessage();
		$sql = e107::getDb();

		$del = array_keys($_POST['delplug']);
		if($sql->db_Delete("plugin", "plugin_id='".intval($del[0])."'"))
		{
			$mes->add(LAN_DELETED, E_MESSAGE_SUCCESS);
		}
		else
		{
			$mes->add(LAN_DELETED_FAILED, E_MESSAGE_WARNING);
		}

	}


	/**
	 * Render Options
	 * @return none
	 */
	private function render_options()
	{
		$frm = e107::getForm();	
		$mes = e107::getMessage(); 
		
		$text = "
		<form method='post' action='".e_SELF."' id='core-db-main-form'>
			<fieldset id='core-db-plugin-scan'>
			<legend class='e-hideme'>".DBLAN_10."</legend>
				<table class='table table-striped adminlist'>
				<colgroup>
					<col style='width: 60%' />
					<col style='width: 40%' />
				</colgroup>
				<tbody>";
				
		$text = "<div>";


		foreach($this->_options as $key=>$val)
		{
			
			$text .= "<div class='pull-left' style='width:50%;padding-bottom:10px'>
			<a class='btn btn-large pull-left' style='margin-right:10px' href='".e_SELF."?mode=".$key."' title=\"".$val['label']."\">".ADMIN_EXECUTE_ICON."</a>
			<h4 style='margin-bottom:3px'><a href='".e_SELF."?mode=".$key."' title=\"".$val['label']."\">".$val['label']."</a></h4><small>".$val['diz']."</small>
			</div>";
		
		}
/*
		$text .= "

				</tbody>
				</table>";
		// $text .= "<div class='buttons-bar center'>
					// ".$frm->admin_button('trigger_db_execute', DBLAN_51, 'execute')."
				// </div>";
		$text .= "
			</fieldset>
		</form>
		";
*/
		e107::getRender()->tablerender(DBLAN_10, $mes->render().$text);
	}


	/**
	 * Import XML Form
	 * @return none
	 */
	private function importForm()
	{
		 // Get largest allowable file upload

		$frm = e107::getSingleton('e_form');
		$mes = e107::getMessage();

				require_once(e_HANDLER.'upload_handler.php');
				  $max_file_size = get_user_max_upload();

				  $text = "
					<form enctype='multipart/form-data' method='post' action='".e_SELF."?mode=".$_GET['mode']."'>
	                <table class='table adminform'>
	                	<colgroup>
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


		e107::getRender()->tablerender(DBLAN_10.SEP.DBLAN_59, $mes->render().$text);

	}

	/**
	 * Export XML Dump
	 * @return  none
	 */
	private function exportXmlForm()
	{
		$mes = e107::getMessage();
		$frm = e107::getSingleton('e_form');


	//TODO LANs

		$text = "<form method='post' action='".e_SELF."?".e_QUERY."' id='core-db-export-form'>
			<fieldset id='core-db-export'>
			<legend class='e-hideme'>Export Options</legend>
				<table class='table adminlist'>
				<colgroup>
					<col style='width: 80%' />
					<col style='width: 20%' />
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
					unset($pref_types['core_old'], $pref_types['core_backup']);
			//		$exclusions = array('core_old'=>1,'core_backup'=>1);
				//	$filteredprefs = array_diff($pref_types,$exclusions);

					foreach($pref_types as $key=>$description)
					{
						$checked = (vartrue($_POST['xml_prefs'][$key]) == $key) ? 1: 0;

						$text .= "<tr>
							<td>
								".$frm->checkbox("xml_prefs[".$key."]", $key, $checked, array('label'=>LAN_PREFS.": ".$key))."
							</td>
							<td>&nbsp;</td>

							</tr>";

					}
					$text .= "</tbody>
				</table>
				<table class='table adminlist'>
				<colgroup>
					<col style='width: 80%' />
					<col style='width: 20%' />
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
						$checked = (vartrue($_POST['xml_tables'][$name]) == $name) ? 1: 0;
						$text .= "<tr>
							<td>
								".$frm->checkbox("xml_tables[".$name."]", $name, $checked, array('label'=>"Table Data: ".$name)).
							"</td>
							<td class='right'>$count</td>
						</tr>";
					}

					$text .="

					</tbody>
				</table>

				<table class='table adminlist'>
				<colgroup>
					<col style='width: 80%' />
					<col style='width: 20%' />
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
					".$frm->admin_button('exportXmlFile', "Export File", 'other')."
				</div>
			</fieldset>
		</form>	";


		e107::getRender()->tablerender(DBLAN_10.SEP."Export Options",$mes->render(). $text);


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
			e107::getMessage()->addSuccess("Inserted $table");
		}

		foreach($ret['failed'] as $table)
		{
			e107::getMessage()->addError("Failed to Insert $table");
		}
	}

	/**
	 * Optimize SQL
	 * @return none
	 */
	private function optimizesql($mySQLdefaultdb) //FIXME Use mysql class. 
	{
	//	global $mes;
		$result = mysql_list_tables($mySQLdefaultdb);
		while($row = mysql_fetch_row($result))
		{
			mysql_query("OPTIMIZE TABLE ".$row[0]);
		}

	//	$mes->add(DBLAN_11." $mySQLdefaultdb ".DBLAN_12, E_MESSAGE_SUCCESS);
		e107::getRender()->tablerender(DBLAN_10.SEP.DBLAN_7, DBLAN_11." $mySQLdefaultdb ".DBLAN_12);
	}

	/**
	 * Preferences Editor
	 * @return string text for display
	 */
	private function pref_editor($type='core')
	{
		//TODO Add drop-down for editing personal perfs also. ie. user pref of self. (admin)

		global $e107;
		$frm = e107::getForm();
		$mes = e107::getMessage();
		$tp = e107::getParser();
		$pref = e107::getPref();

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
						<table class='table adminlist'>
							<colgroup>
								<col style='width: 5%' />
								<col style='width: 20%' />
								<col style='width: 70%' />
								<col style='width: 5%' />
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
			$ptext = $tp->textclean($ptext, 80);

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

		e107::getRender()->tablerender(DBLAN_10.SEP.DBLAN_20.SEP.ucwords($type), $mes->render().$text);

		return $text;
	}

	/**
	 * Preferences Editor
	 * @return none
	 */
	private function scan_override()
	{
		$pref = e107::getPref();		
		$mes = e107::getMessage();
		$f = e107::getFile();
		$config = e107::getConfig();

		$scList = '';

		$fList = $f->get_files(e_CORE.'override/shortcodes/single', '\.php$');
		$scList = array();
		if(count($fList))
		{
			foreach($fList as $file)
			{
				$scList[] = strtoupper(substr($file['fname'], 0, -4));
			}
			$scList = implode(',', $scList);
		}
		$config->set('sc_override', $scList)->save(false);
		
		// core batch overrides
		$fList = $f->get_files(e_CORE.'override/shortcodes/batch', '\.php$');
		$scList = array();
		if(count($fList))
		{
			foreach($fList as $file)
			{
				$scList[] = substr($file['fname'], 0, -4);
			}
			$scList = implode(',', $scList);
		}
		
		$config->set('sc_batch_override', $scList)->save(false);
		//$pref['sc_override'] = $scList;
		//save_prefs();
	//	$mes->add(DBLAN_57.':<br />'.$pref['sc_override'], E_MESSAGE_SUCCESS);
		// FIXME lan
		e107::getRender()->tablerender(
			'<strong>'.DBLAN_56, DBLAN_57.':</strong> '
			.($config->get('sc_override') ? '<br />'.$config->get('sc_override') : '(empty)')
			.'<br /><br /><strong>Batch shortcodes:</strong>'
			.($config->get('sc_batch_override') ? '<br />'.$config->get('sc_batch_override') : '(empty)')
		);
	}

	/**
	 * Plugin Folder Scanner
	 * @return none
	 */
	private function plugin_viewscan($mode = 'update')
	{
		$error_messages = array(0 => DBLAN_31, 1 => DBLAN_32, 2 => DBLAN_33, 3 => DBLAN_34);
	//	$error_image = array("integrity_pass.png", "integrity_fail.png", "warning.png", "blank.png");
		$error_glyph = array(ADMIN_TRUE_ICON,ADMIN_FALSE_ICON,"<i class='S16 e-warning-16'></i>","<i style='display:inline-block;width:17px;height:16px;'> </i>");
		
		$error_type = array('warning'=>2, 'error'=>1);


		global $e107;
		$sql = e107::getDb();
		$tp = e107::getParser();
		$frm = e107::getForm();
		$mes = e107::getMessage();

		require_once (e_HANDLER."plugin_class.php");
		$ep = new e107plugin();
		$ep->update_plugins_table($mode); // scan for e_xxx changes and save to plugin table.
		$ep->save_addon_prefs($mode); // generate global e_xxx_list prefs from plugin table.

		/* we all are awaiting for PHP5 only support - method chaining...
		$mes->add(DBLAN_22.' - '.DBLAN_23, E_MESSAGE_SUCCESS)
				 ->add("<a href='".e_SELF."'>".LAN_BACK."</a>", E_MESSAGE_SUCCESS)
				 ->add(DBLAN_30);
		*/

		$mes->add(DBLAN_23, E_MESSAGE_SUCCESS);
		$mes->add("<a href='".e_SELF."'>".LAN_BACK."</a>", E_MESSAGE_SUCCESS);
		$mes->add(DBLAN_30);

		$text = "
				<form method='post' action='".e_ADMIN."db.php?mode=".$_GET['mode']."' id='plug_edit'>
					<fieldset id='core-db-plugin-scan'>
						<legend class='e-hideme'>".ADLAN_CL_7."</legend>
						<table class='table adminlist'>
							<colgroup>
								<col style='width: 20%' />
								<col style='width: 20%' />
								<col style='width: 35%' />
								<col style='width: 25%' />
							</colgroup>
							<thead>
								<tr>
									<th>".LAN_NAME."</th>
									<th>".DBLAN_25."</th>
									<th>".DBLAN_26."</th>
									<th class='center last'>".DBLAN_27."</th>
								</tr>
							</thead>
							<tbody>
			";

		$sql->select("plugin", "*", "plugin_id !='' order by plugin_path ASC"); // Must order by path to pick up duplicates. (plugin names may change).
		$previous = '';
		while($row = $sql->fetch())
		{
			e107::loadLanFiles($row['plugin_path'],'admin');
			e107::plugLan($row['plugin_path'],'global',true);	
			
			$text .= "
								<tr>
									<td>".$tp->toHtml($row['plugin_name'], FALSE, "defs,emotes_off")."</td>
	               					<td>".$row['plugin_path']."</td>
									<td>";

			if(trim($row['plugin_addons']))
			{
				//XXX - $nl_code = ''; - OLD VAR?
				foreach(explode(',', $row['plugin_addons']) as $this_addon)
				{
					$ret_code = 3; // Default to 'not checked
					if((strpos($this_addon, 'e_') === 0) || (substr($this_addon, - 4, 4) == '_sql'))
					{
						$ret_code = $ep->checkAddon($row['plugin_path'], $this_addon); // See whether spaces before opening tag or after closing tag
					}
					elseif(strpos($this_addon, 'sc_') === 0)
					{
						$this_addon = substr($this_addon, 3). ' (sc)';
					}
					
					if(!is_numeric($ret_code)) 
					{
						$errorMessage = $ret_code['msg'];	
						$ret_code = $error_type[$ret_code['type']];	
					}
					else 
					{
						$errorMessage  = $error_messages[$ret_code];
					}
					
					$text .= "<span class='clear e-tip' style='cursor:pointer' title='".$errorMessage."'>";
					$text .= $error_glyph[$ret_code]."&nbsp;";
					
				//	$text .= "<img class='icon action S16' src='".e_IMAGE_ABS."fileinspector/".$error_image[$ret_code]."' alt='".$error_messages[$ret_code]."' title='".$error_messages[$ret_code]."' />";
					$text .= trim($this_addon); // $ret_code - 0=OK, 1=content error, 2=access error
					$text .= "</span><br />";
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
				$text .= ($row['plugin_installflag'] == 1) ? "<span class='label label-warning'>".DBLAN_27."</span>" : " "; // "Installed and not installed";
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

		e107::getRender()->tablerender(DBLAN_10.SEP.DBLAN_22, $mes->render().$text);
	}
}

//XXX - what is this for (backup core)? <input type='hidden' name='sqltext' value='{$sqltext}' />

function db_adminmenu() //FIXME - has problems when navigation is on the LEFT instead of the right. 
{
	global $st;


	foreach($st->_options as $key=>$val)
	{
		$var[$key]['text'] = $val['label'];
		$var[$key]['link'] = e_SELF."?mode=".$key;
	}

	e107::getNav()->admin(DBLAN_10, $_GET['mode'], $var);
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
	$mes = e107::getMessage();

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
			$mes->add($desinationFolder." is not writable", E_MESSAGE_ERROR);
			return ;
		}
	}


	if($xml->e107Export($prefs,$tables,$debug))
	{
		$mes->add("Created: ".$desinationFolder."install.xml", E_MESSAGE_SUCCESS);
		if(varset($xml->fileConvertLog))
		{
			foreach($xml->fileConvertLog as $oldfile)
			{
				$file = basename($oldfile);
				$newfile = $desinationFolder.$file;
				if($oldfile == $newfile || (copy($oldfile,$newfile)))
				{
					$mes->add("Copied: ".$newfile, E_MESSAGE_SUCCESS);
				}
				else
				{
					$mes->add("Couldn't copy: ".$newfile, E_MESSAGE_ERROR);
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

/*
function verify_sql_record() // deprecated by db_verify.php ( i think).
{
	global  $e107;

	$sql = e107::getDb();
	$sql2 = e107::getDb('sql2');
	$sql3 = e107::getDb('sql3');
	$frm = e107::getForm();
	$tp = e107::getParser();
	$mes = e107::getMessage();

	$tables = array();
	$tables[] = 'rate';
	$tables[] = 'comments';

	if(isset($_POST['delete_verify_sql_record']))
	{

		if(!varset($_POST['del_dbrec']))
		{
			$mes->add('Nothing to delete', E_MESSAGE_DEBUG);
		}
		else
		{
			$msg = "ok, so you want to delete some records? not a problem at all!<br />";
			$msg .= "but, since this is still an experimental procedure, i won't actually delete anything<br />";
			$msg .= "instead, i will show you the queries that would be performed<br />";
			$text .= "<br />";
			$mes->add($msg, E_MESSAGE_DEBUG);

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

			$mes->add($qry, E_MESSAGE_DEBUG);
			$mes->add("<a href='".e_SELF."'>".LAN_BACK."</a>", E_MESSAGE_DEBUG);
		}
	}

	//Nothing selected
	if(isset($_POST['check_verify_sql_record']) && (!isset($_POST['table_rate']) && !isset($_POST['table_comments'])))
	{
		$_POST['check_verify_sql_record'] = '';
		unset($_POST['check_verify_sql_record']);
		$mes->add(DBLAN_53, E_MESSAGE_WARNING);
	}

	if(!isset($_POST['check_verify_sql_record']))
	{
		//select table to verify
		$text = "
			<form method='post' action='".e_SELF."'>
				<fieldset id='core-db-verify-sql-tables'>
					<legend class='e-hideme'>".DBLAN_39."</legend>
					<table class='table adminlist'>
						<colgroup>
							<col style='width: 100%' />
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

		$ns->tablerender(DBLAN_10.SEP.DBLAN_39, $mes->render().$text);
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
						<table class='table adminlist'>
							<colgroup>
								<col style='width: 20%' />
								<col style='width: 10%' />
								<col style='width: 50%' />
								<col style='width: 20%' />
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

			$sql2 = e107::getDb('sql2');

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

		$ns->tablerender(DBLAN_10.SEP.DBLAN_50, $mes->render().$text);
	}
}
*/

?>