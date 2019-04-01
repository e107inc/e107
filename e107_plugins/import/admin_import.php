<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */


/**
 *	e107 Import plugin
 *
 *	@package	e107_plugins
 *	@subpackage	import


Routine manages import from other databases
Options supported:
	CSV (with format file)
	WordPress (users)
	Mambo/Joomla
	PHPBB2
	PHPBB3
	SMF
	PHPNuke
	proboards
	PHPFusion
*/


 define('IMPORT_DEBUG',TRUE);
// define('IMPORT_DEBUG',TRUE);

require_once("../../class2.php");
// define("USE_PERSISTANT_DB",TRUE);


$frm = e107::getForm();
$mes = e107::getMessage();

e107::lan('import', true);

//XXX A Fresh Start 
class import_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'import_main_ui',
			'path' 			=> null,
			'ui' 			=> 'import_admin_form_ui',
			'uipath' 		=> null
		),
		'cat'		=> array(
			'controller' 	=> 'import_cat_ui',
			'path' 			=> null,
			'ui' 			=> 'import_cat_form_ui',
			'uipath' 		=> null
		)					
	);	

	protected $adminMenu = array(
		'main/list'		=> array('caption'=> LAN_LIST, 'perm' => '0'),
	//	'main/create'	=> array('caption'=> 'Create import', 'perm' => '0'),
//		'cat/list' 		=> array('caption'=> 'Categories', 'perm' => '0'),
//		'cat/create' 	=> array('caption'=> "Create Category", 'perm' => '0'),
	//	'main/prefs' 	=> array('caption'=> LAN_PREFS, 'perm' => '0'),
	//	'main/custom'	=> array('caption'=> 'Custom Page', 'perm' => '0')		
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = LAN_PLUGIN_IMPORT_NAME;
}


class import_main_ui extends e_admin_ui
{
	
	protected $pluginTitle			= LAN_PLUGIN_IMPORT_NAME;
	protected $pluginName			= 'import';
	protected $table				= false;
	
	protected $providers			= array(); // the different types of import. 
	protected $deleteExisting		= false; // delete content from existing table during import. 
	protected $selectedTables		= array(); // User selection of what tables to import. eg. news, pages etc. 
	protected $importClass			= null;
	protected $checked_class_list	= '';
	
	// Definitions of available areas to import
	protected $importTables = array(
		'users' 		=> array('message' => LAN_CONVERT_25, 			'classfile' => 'import_user_class.php', 'classname' => 'user_import'),
		'userclass' 	=> array('message' => LAN_CONVERT_73, 			'nolist'=>true, 'classfile' => 'import_user_class.php', 'classname' => 'userclass_import'),

		'news' 			=> array('message' => LAN_CONVERT_28,			'classfile' => 'import_news_class.php', 'classname' => 'news_import'),
		'newscategory' 	=> array('message' => LAN_CONVERT_74,		'nolist'=>true, 'classfile' => 'import_news_class.php', 'classname' => 'newscategory_import'),

		'page' 			=> array('message' => LAN_CONVERT_65,				    'classfile' => 'import_page_class.php', 'classname' => 'page_import'),
		'pagechapter' 	=> array('message' => LAN_CONVERT_66,			'nolist'=>true, 'classfile' => 'import_page_class.php', 'classname' => 'pagechapter_import'),
		'links' 		=> array('message' => LAN_CONVERT_67, 					'classfile' => 'import_links_class.php', 'classname' => 'links_import'),	
		'media' 		=> array('message' => LAN_CONVERT_68, 					'classfile' => 'import_media_class.php', 'classname' => 'media_import'),
		'forum' 		=> array('message' => LAN_CONVERT_69, 					'classfile' => 'import_forum_class.php', 'classname' => 'forum_import'),
		'forumthread' 	=> array('message' => LAN_CONVERT_70, 	'classfile' => 'import_forum_class.php', 'classname' => 'forumthread_import', 'nolist'=>true),
		'forumpost' 	=> array('message' => LAN_CONVERT_71, 			'classfile' => 'import_forum_class.php', 'classname' => 'forumpost_import', 'nolist'=>true),
		'forumtrack' 	=> array('message' => LAN_CONVERT_72, 			'classfile' => 'import_forum_class.php', 'classname' => 'forumtrack_import', 'nolist'=>true),
		//	'comments' 		=> array('message'=> LAN_COMMENTS),

	//	'polls' 		=> array('message' => LAN_CONVERT_27)
	);	
	
		// without any Order or Limit. 

		
		
	function init()
	{
		$fl = e107::getFile();
		
		$importClassList = $fl->get_files(e_PLUGIN.'import/providers', "^.+?_import_class\.php$", "standard", 1);
		
		foreach($importClassList as $file)
		{


			$tag = str_replace('_class.php','',$file['fname']);
			
			$key = str_replace("_import_class.php","",$file['fname']);

			if($key === 'template')
			{
				continue;
			}

			include_once($file['path'].$file['fname']);		// This will set up the variables
			
			$this->providers[$key] = $this->getMeta($tag);

			if(!empty($_GET['type']))
			{
				$this->importClass = filter_var($_GET['type'])."_import";
			}
			
		}	


		uksort($this->providers,'strcasecmp');

		
	}	
	
	
	function help()
	{
		
		return "Some help text for admin-ui";	
		
	}
	
	
	
	
	
	function getMeta($class_name)
	{
		if(class_exists($class_name))
		{
			$obj = new $class_name;
			return array('title' => vartrue($obj->title), 'description' => vartrue($obj->description), 'supported' => vartrue($obj->supported));
		}
		else
		{
			e107::getMessage()->addDebug("Missing class: ".$class_name);	
		}
		
	}

	
	
		
	
	// After selection - decide where to route things. 	
	function importPage()
	{
		
	//	print_a($_POST);
	
		if(!empty($_POST['import_delete_existing_data']))
		{
			$this->deleteExisting = varset($_POST['import_delete_existing_data'],0);
		}
		
		if(!empty($_POST['classes_select']))
		{
			$this->checked_class_list = implode(',',$_POST['classes_select']);
		}
		
		if(!empty($_POST['createUserExtended'])) //TODO 
		{
			$this->createUserExtended = true; 
		}
		
		if(!empty($_POST['selectedTables']))
		{
			$this->selectedTables = $_POST['selectedTables'];
		}
			
		if(!empty($_POST['runConversion'])) // default method. 
		{
			$this->runConversion($_POST['import_source']);
			return;	
		}
		
				

		
		$this->showImportOptions($_GET['type']);	
		
	}
	
	
	
	
	
	
	
			
		
	function listPage()
	{
		$mes = e107::getMessage();
		$frm = e107::getForm();
		
		$tableCount = 0;
	//	$mes->addDebug(print_a($this->providers,true));
		
		$text = "
			<form method='get' action='".e_SELF."' id='core-import-form'>
				<fieldset id='core-import-select-type'>
				<legend class='e-hideme'>".'DBLAN_10'."</legend>
				".$frm->hidden('mode','main')."
				".$frm->hidden('action','import')."
		            <table class='table table-striped table-bordered'>
					<colgroup>
					<col />";

					 foreach($this->importTables as $key=>$val)
					 {
					 	if(!empty($val['nolist'])){ continue; }
		 			 	$text .= "<col style='width:5%' />\n";
					 }


					$text .= "
					<col />

					</colgroup>
					<thead>
					<tr>
		            	<th>".LAN_CONVERT_06."</th>";
		                foreach($this->importTables as $val)   // 1 column for each of users, news, forum etc.
						{
							if(vartrue($val['nolist'])){ continue; }
		                	$text .= "<th class='center'>".$val['message']."</th>";
							$tableCount++;
		 				}
		
						$text.="
						<th class='center'>".LAN_OPTIONS."</th>
		
					</tr>
					</thead>
					<tbody>";
					/*
					$text .= "
		
					<tr>
					<td><img src='".e_PLUGIN."import/images/csv.png' alt='' style='float:left;height:32px;width:32px;margin-right:4px'>CSV</td>
					<td class='center'>".ADMIN_TRUE_ICON."</td>";
					
					for ($i=0; $i < $tableCount-1; $i++) 
					{ 
						$text .= "<td>&nbsp;</td>";	
					}
		
					
					$text .= "<td class='center middle'>".$frm->admin_button('import_type', 'csv', 'other',"Select")."</td></tr>";
					*/
		
		        foreach ($this->providers as $k=>$info)
				{
					$title = $info['title'];
					
					$iconFile = e_PLUGIN."import/images/".str_replace("_import","",strtolower($k)).".png";		
					
					$icon = (file_exists($iconFile)) ? "<img src='{$iconFile}' alt='' style='float:left;height:32px;width:32px;margin-right:8px'>" : "";
					
		          	$text .= "<!-- $title -->
					<tr><td >".$icon.$title."<div class='smalltext'>".$info['description']."</div></td>\n";
		
					 foreach($this->importTables as $key=>$val)
					 {
					 	if(vartrue($val['nolist'])){ continue; }
		 			 	$text .= "<td class='center'>".(in_array($key,$info['supported']) ? ADMIN_TRUE_ICON : "&nbsp;")."</td>\n";
					 }
		
		             $text .= "
					 	<td class='center middle'>";
						
						$text .= $frm->admin_button('type', $k, 'other',LAN_CONVERT_64);
					// 	$text .= $frm->admin_button('import_type', $k, 'other',"Select");
						
						$text .= "
					 	</td>
					 </tr>";
				}
		
		
				$text .= "
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$frm->hidden('trigger_import',1)."
						
					</div>
				</fieldset>
			</form>";
		
			echo $mes->render().$text; 
			// $ns->tablerender(LAN_PLUGIN_IMPORT_NAME, $mes->render().$text);
		
	}


	
	
	
	function runConversion($import_source)
	{
		$frm = e107::getForm();	
		$ns = e107::getRender();	
		$mes = e107::getMessage();
		
		$abandon = TRUE;
	
	 	switch ($import_source)
		{
			case 'csv' : 
			
			break;
	
			case 'db' :
				if($this->dbImport() == false)
				{
					$abandon = true;
				}
			break;
			
			case 'rss' :
				if($this->rssImport() == false)
				{
					$abandon = true;
				}
			break;
		}
		
		
//		if ($msg)
//		{
//			$mes->add($msg, E_MESSAGE_INFO); //  $ns -> tablerender(LAN_CONVERT_30, $msg);
//			$msg = '';
//		}
	
		if ($abandon)
		{
		//	unset($_POST['do_conversion']);
			$text = "
			<form method='get' action='".e_SELF."'>
			<div class='center'>
			".$frm->admin_button('dummy_continue',LAN_CONTINUE, 'execute')."
			</div>
			</form>";
			echo $mes->render(). $text;
			
		//	$ns -> tablerender(LAN_CONVERT_30,$mes->render(). $text);
			
		}
	}




	function renderConfig()
	{
		
	}






	
		
	function showImportOptions($type='csv')
	{
		global $csv_names, $e_userclass;
		$mode = $this->importClass;
		
		$frm = e107::getForm();
		$ns = e107::getRender();
		
		$mes = e107::getMessage();
		
		if (class_exists($mode))
		{
			$mes->addDebug("Class Available: ".$mode);   
			$proObj = new $mode;
			if($proObj->init()===FALSE)
			{
				return;
			}
		}
	
		$message = "<strong>".LAN_CONVERT_05."</strong>";
		$mes->add($message, E_MESSAGE_WARNING);
	
		$text = "
		<form method='post' action='".e_SELF."?action=main&action=import&type=".$type."'>
	    <table class='table adminform'>
	    	<colgroup>
	    		<col class='col-label' />
	    		<col class='col-control' />
	    	</colgroup>";
	
		
	
		/*
		if($mode == "csv")
		{
			$text .= "
			<tr>
			  <td>".LAN_CONVERT_07."</td>
			  <td><select name='csv_format' class='tbox'>\n";
			  foreach ($csv_names as $k => $v)
			  {
				$s = ($current_csv == $k) ? " selected='selected'" : '';
				$text .= "<option value='{$k}'{$s}>{$v}</option>\n";
			  }
		  	$text .= "</select>\n
			  </td>
			</tr>
	
			<tr>
			<td>".LAN_CONVERT_36."</td>
			<td><input class='tbox' type='text' name='csv_data_file' size='30' value='{$csv_data_file}' maxlength='100' /></td>
			</tr>
	
			<tr><td>".LAN_CONVERT_17."
			</td>
			<td>
	
			<input type='hidden' name='import_source' value='csv' />
			<input type='checkbox' name='csv_pw_not_encrypted' value='1'".($csv_pw_not_encrypted ? " checked='checked'" : '')."/>
			<span class='smallblacktext'>".LAN_CONVERT_18."</span></td>
			</tr>
			";
	
		}
		else
		*/
		
		$importType = $proObj->title;
		
		if($proObj->sourceType == 'db' || !$proObj->sourceType) // STANDARD db Setup 
		{
	    	$databases = $this->getDatabases();
	    	$prefix = (varset($_POST['dbParamPrefix']) ? $_POST['dbParamPrefix'] : $proObj->mprefix);
	/*
	    	$text .= "
			<tr>
			<td>$importType ".LAN_CONVERT_19."</td>
			<td><input class='tbox' type='text' name='dbParamHost' size='30' value='".(varset($_POST['dbParamHost']) ? $_POST['dbParamHost'] : 'localhost')."' maxlength='100' /></td>
			</tr>
			<tr>
			<td >$importType ".LAN_CONVERT_20."</td>
			<td >
				<input class='tbox' type='text' name='dbParamUsername' size='30' data-tooltipvalue='".varset($_POST['dbParamUsername'])."' maxlength='100' />
				<div class='field-help' data-placement='right'>Must be different from the one e107 uses.</div>
			</td>
			</tr>
			<tr>
			<td >$importType ".LAN_CONVERT_21."</td>
			<td ><input class='tbox' type='text' name='dbParamPassword' size='30' value='".varset($_POST['dbParamPassword'])."' maxlength='100' /></td>
			</tr>
			";*/

			$text .= "
			<tr>
			<td >$importType ".LAN_CONVERT_22."</td>
			<td >".$frm->select('dbParamDatabase', $databases, null, array('required'=>1), LAN_SELECT."...")."</td>
			</tr>
			<tr>
			<td >$importType ".LAN_CONVERT_23."</td>
			<td >".$frm->text('dbParamPrefix', $prefix, 100)."
			<input type='hidden' name='import_source' value='db' />
	  		</td>
			</tr>";
	
		}
	
	
		if(method_exists($proObj,"config")) // Config Found in Class - render options from it. 
		{
			if($ops  = $proObj->config())
			{
				foreach($ops as $key=>$val)
				{
					$text .= "<tr>
						<td>".$val['caption']."</td>
						<td>".$val['html'];
					$text .= (vartrue($val['help'])) ? "<div class='field-help'>".$val['help']."</div>" : "";
					$text .= "</td>
					</tr>\n";
				}
			}
		}
	
	
		if($proObj->sourceType)
		{
			$text .= "<input type='hidden' name='import_source' value='".$proObj->sourceType."' />\n";	
		} 
		else
		{
			$text .= "<input type='hidden' name='import_source' value='db' />";	
		}
			
	
	
	
	//	if($mode != 'csv')
		{
			$text .= "
			<tr>
			<td >$importType ".LAN_CONVERT_24."</td>
			<td>";
	
			$defCheck = (count($proObj->supported)==1) ? true : false;
	   	  	foreach ($this->importTables as $k => $v)
		  	{
				if(in_array($k, $proObj->supported)) // display only the options supported.
				{
					$text .= $frm->checkbox('selectedTables['.$k.']', $k, $defCheck,array('label'=>$v['message'])); 	
						
					
					//$text .= "<input type='checkbox' name='import_block_{$k}' id='import_block_{$k}' value='1' {$defCheck} />&nbsp;".$v['message'];
					// $text .= "<br />";
				}
		  	}
		  	$text .= "</td></tr>";		
		}
	
	
		$text .= "
			<tr>
				<td>".LAN_CONVERT_38."</td>
				<td>".$frm->radio_switch('import_delete_existing_data', $_POST['import_delete_existing_data'])."
				<div class='field-help'>".LAN_CONVERT_39."</div></td>
			</tr>";
		
		//TODO 
		/*
		if(in_array('users',$proObj->supported))
		{
			$text .= "<tr>
				<td>Create Extended User Fields</td>
				<td>".$frm->checkbox('createUserExtended', 1,'', array('label'=>'&nbsp;','title'=>'Will automatically add missing user-fields when found.'))."
				</td>
			</tr>";	
		}
		*/
		
		if(varset($proObj->defaultClass) !== false)
		{
			$text .= "
			<tr><td>".LAN_CONVERT_16."</td>
			<td>";
	  		$text .= $e_userclass->vetted_tree('classes_select',array($e_userclass,'checkbox'), varset($_POST['classes_select']),'main,admin,classes,matchclass, no-excludes');
	  		$text .= "</td></tr>";
		}
	 	
	  	$action = varset($proObj->action,'runConversion');
	  	$text .= "</table>
		<div class='buttons-bar center'>".$frm->admin_button($action,LAN_CONTINUE, 'execute').
		
		$frm->admin_button('back',LAN_CANCEL, 'cancel')."
		<input type='hidden' name='db_import_type' value='$mode' />
		<input type='hidden' name='import_type' value='".$mode."' />
		</div>
		</form>";
	
		// Now a little bit of JS to initialise some of the display divs etc
	//  	$temp = '';
	//  	if(varset($import_source)) { $temp .=  "disp('{$import_source}');"; }
	//  	if (varset($current_db_type)) $temp .= " flagbits('{$current_db_type}');";
	//  	if (varset($temp)) $text .= "<script type=\"text/javascript\"> {$temp}</script>";
		
		$this->addTitle($importType); 
		echo $mes->render().$text; 
		
	//  	$ns -> tablerender(LAN_PLUGIN_IMPORT_NAME.SEP.$importType, $mes->render().$text);
	
	}
	
	
	private function getDatabases()
	{
		$tmp = e107::getDb()->gen("SHOW DATABASES");
		$databases = e107::getDb()->db_getList();

		$arr = array();

		$exclude = array('mysql', 'information_schema', 'performance_schema', 'phpmyadmin');

		foreach($databases as $v)
		{
			$id = $v['Database'];

			if(in_array($id,$exclude))
			{
				continue;
			}

			$arr[$id] = $id;

		}

	    return $arr;


	}

	
		
	function rssImport()
	{
		global $current_db_type;
		
		$mes = e107::getMessage();
		$mes->addDebug("Loading: RSS");	
		
		if(!varset($_POST['runConversion']))
		{
			$mes->addWarning("Under Construction"); 	
		}	
		
		return $this->dbImport('xml');
		
	}
	
	
	
	/** MAIN IMPORT AREA */
	function dbImport($mode='db')
	{
		
		$mes = e107::getMessage();
		$tp = e107::getParser();
		
		$mes->addDebug("dbImport(): Loading: ".$this->importClass);
		
		if(!is_array($this->importTables))
		{
			$mes->addError("dbImport(): No areas selected for import");  // db connect failed
			return false;	
		}
		
		if (class_exists($this->importClass))
		{
			$mes->addDebug("dbImport(): Converter Class Available: ".$this->importClass);   
			$converter = new $this->importClass ;
			$converter->init();
		}
		else
		{
			$mes->addError(LAN_CONVERT_42. "[".$this->importClass."]");
			$mes->addDebug("dbImport(): Class NOT Available: ".$this->importClass);   
			
			return false;
		}
		

		if($mode == 'db') // Don't do DB check on RSS/XML 
		{
			if (empty($_POST['dbParamDatabase']))
			{
				$mes->addError(LAN_CONVERT_41);
				return false;
			}
		
			$result = $converter->database($tp->filter($_POST['dbParamDatabase']),  $tp->filter($_POST['dbParamPrefix']));

		//	$result = $converter->database($tp->filter($_POST['dbParamDatabase']),  $tp->filter($_POST['dbParamPrefix']), true);

			if ($result !== true)
			{
				$mes->addError(LAN_CONVERT_43.": ".$result);  // db connect failed
				return false;
			}	
		}	
		
	
		if(vartrue($converter->override))
		{
			$mes->addDebug("dbImport(): Override Active!" );
			return;
		}	
		
		
		
		// Return 
		foreach($this->selectedTables as $k => $tm)
		{
			$v = $this->importTables[$k];
			
			$loopCounter = 0;
			$errorCounter = 0;
				
			if (is_readable($v['classfile'])) // Load our class for either news, pages etc. 
			{
				$mes->addDebug("dbImport(): Including File: ".$v['classfile']);
				require_once($v['classfile']);
			}
			else
			{
				$mes->addError(LAN_CONVERT_45.': '.$v['classfile']);   // can't read class file.
				return false;
			}
			
			$mes->addDebug("dbImport(): Importing: ".$k);
			
			$exporter = new $v['classname'];		// Writes the output data
	
		  	if(is_object($exporter))
			{
				$mes->addDebug("dbImport(): Exporter Class Initiated: ".$v['classname']);	
				
				if(is_object($exporter->helperClass))
				{
					$mes->addDebug("dbImport(): Initiated Helper Class");		
					$converter->helperClass = $exporter->helperClass;
				}
				
			}
			else
			{
				$mes->addDebug("dbImport(): Couldn't Initiate Class: ".$v['classname']);		
			}
			
			
		  	$result = $converter->setupQuery($k, !$this->deleteExisting);
							
			if ($result !== true)
			{
				$mes->addError(LAN_CONVERT_44.' '.$k);   // couldn't set query
				break;
			}
					
		
		  
				 				 	
			if($k == 'users')  // Do any type-specific default setting
			{
				$mes->addDebug("dbImport(): Overriding Default for user_class: ".$this->checked_class_list);	
				$exporter->overrideDefault('user_class', $this->checked_class_list);
			//	break;
			}
					
			if ($this->deleteExisting == true)
			{
				$mes->addDebug("dbImport(): Emptying target table. ");
				$exporter->emptyTargetDB();		// Clean output DB - reasonably safe now	
			} 
					
			while ($row = $converter->getNext($exporter->getDefaults(),$mode))
			{
				$loopCounter++;
				$result = $exporter->saveData($row);
				if ($result !== TRUE)
				{
					$errorCounter++;
					$line_error = $exporter->getErrorText($result);
				//	if ($msg) $msg .= "<br />";
					$msg = str_replace(array('[x]','[y]'),array($line_error,$k),LAN_CONVERT_46).$loopCounter;
					$mes->addError($msg);   // couldn't set query
				}
			}
					
			$converter->endQuery(); 
					
			unset($exporter);
					
					
			$msg = str_replace(array('[x]','[y]', '[z]','[w]'),
			array($loopCounter,$loopCounter-$errorCounter,$errorCounter, $k),LAN_CONVERT_47);
			$mes->addSuccess($msg);   // couldn't set query				
		}
		
		
		
		
		
		
		
		
		
		
		return true;
		
		// $abandon = FALSE;	
	}

	
	
	
	
}






































/*

// Source DB types (i.e. CMS types) supported. Key of each element is the 'short code' for the type
$import_class_names = array();			// Title
$import_class_comment = array();		// Descriptive comment
$import_class_support = array();		// Array of data types supported

// Definitions of available areas to import
$db_import_blocks = array(
	'users' 		=> array('message' => LAN_CONVERT_25, 	'classfile' => 'import_user_class.php', 'classname' => 'user_import'),
	'news' 			=> array('message' => LAN_CONVERT_28,	'classfile' => 'import_news_class.php', 'classname' => 'news_import'),
	'page' 			=> array('message' => "Pages",			'classfile' => 'import_page_class.php', 'classname' => 'page_import'),
	'links' 		=> array('message' => "Links", 			'classfile' => 'import_links_class.php', 'classname' => 'links_import'),	
	'media' 		=> array('message' => "Media", 			'classfile' => 'import_media_class.php', 'classname' => 'media_import'),
	'comments' 		=> array('message'=> "Comments"),
//	'forumdefs' 	=> array('message' => LAN_CONVERT_26),
//	'forumposts' 	=> array('message' => LAN_CONVERT_48), 
//	'polls' 		=> array('message' => LAN_CONVERT_27)
);


// See what DB-based imports are available (don't really want it here, but gets it into the header script)
require_once(e_HANDLER.'file_class.php');

$fl = new e_file;
$importClassList = $fl->get_files(e_PLUGIN.'import/providers', "^.+?_import_class\.php$", "standard", 1);
foreach($importClassList as $file)
{
	$tag = str_replace('_class.php','',$file['fname']);
	include_once($file['path'].$file['fname']);		// This will set up the variables
}
unset($importClassList);
unset($fl);
asort($import_class_names);



if(varset($_POST['import_source']))
{
	$import_source = varset($_POST['import_source'],'csv');
	if(varset($_POST['classes_select']))
	{
		$checked_class_list = implode(',',$_POST['classes_select']);
	}
 	$import_delete_existing_data = varset($_POST['import_delete_existing_data'],0);

	$current_csv = varset($_POST['csv_format'],'default');
	$csv_pw_not_encrypted = varset($_POST['csv_pw_not_encrypted'],0);
	$csv_data_file = varset($_POST['csv_data_file'],'import.csv');

	$current_db_type = varset($_POST['db_import_type'],key($import_class_names));
}

$db_blocks_to_import = array();


foreach ($db_import_blocks as $k => $v)
{
  if (isset($_POST['import_block_'.$k]))
  {
	$db_blocks_to_import[$k] = 1;
  }
}

// require_once(e_ADMIN."auth.php");

if (!is_object($e_userclass))
{
  require_once(e_HANDLER."userclass_class.php");		// Modified class handler
  $e_userclass = new user_class;
}




define('CSV_DEF_FILE','csv_import.txt');		// Supplementary CSV format definitions

// Definitions of available CSV-based imports
$csv_formats = array('default' => 'user_name,user_password');
$csv_names = array('default' => LAN_CONVERT_12);
$csv_options = array('default' => 'simple');
$csv_option_settings = array(
		'simple' 	=> array('separator' => ',', 'envelope' => ''),
		'simple_sq'	=> array('separator' => ',', 'envelope' => "'"),
		'simple_dq' => array('separator' => ',', 'envelope' => '"'),
		'simple_semi' => array('separator' => ',', 'envelope' => ';'),
		'simple_bar' => array('separator' => ',', 'envelope' => '|')
	);

// See what CSV format definitions are available
if (is_readable(CSV_DEF_FILE))
{
  $csv_temp = file(CSV_DEF_FILE);
  foreach ($csv_temp as $line)
  {
	$line = trim(str_replace("\n","",$line));
	if ($line)
	{
	  list($temp,$name,$options,$line) = explode(',',$line,4);
	  $temp = trim($temp);
	  $name = trim($name);
	  $options = trim($options);
	  $line = trim($line);
	  if ($temp && $name && $options && $line)  
	  {
		$csv_formats[$temp] = $line;		// Add any new definitions
		$csv_names[$temp] = $name;
		$csv_options[$temp] = $options;
	  }
	}
  }
  unset($csv_temp);
}



$msg = '';

//======================================================
// 		Executive routine - actually do conversion
//======================================================
if(isset($_POST['do_conversion']))
{
	$abandon = TRUE;
	
 	switch ($import_source)
	{
		case 'csv' : 
			if (!isset($csv_formats[$current_csv])) $msg = "CSV File format error<br /><br />";
		  	if (!is_readable($csv_data_file)) $msg = LAN_CONVERT_31;
		  	if (!isset($csv_options[$current_csv])) $msg = LAN_CONVERT_37.' '.$current_csv;
		  	if (!isset($csv_option_settings[$csv_options[$current_csv]]))
			{
				$msg = LAN_CONVERT_37.' '.$csv_options[$current_csv];	
			} 
		  
		  	if (!$msg)
		  	{
				$field_list = explode(',',$csv_formats[$current_csv]);
				$separator = $csv_option_settings[$csv_options[$current_csv]]['separator'];
				$enveloper = $csv_option_settings[$csv_options[$current_csv]]['envelope'];
				if (IMPORT_DEBUG) echo "CSV import: {$current_csv}  Fields: {$csv_formats[$current_csv]}<br />";
				require_once('import_user_class.php');
				$usr = new user_import;
				$usr->overrideDefault('user_class',$checked_class_list);
				if (($source_data = file($csv_data_file)) === FALSE) $msg = LAN_CONVERT_32;
				if ($import_delete_existing_data) $usr->emptyTargetDB();				// Delete existing users - reasonably safe now
				$line_counter = 0;
				$error_counter = 0;
				$write_counter = 0;
				foreach ($source_data as $line)
				{
			  		$line_counter++;
			  		$line_error = FALSE;
			  		if ($line = trim($line))
			  		{
						$usr_data = $usr->getDefaults();		// Reset user data
						$line_data = csv_split($line, $separator, $enveloper);
						$field_data = current($line_data);
						foreach ($field_list as $f)
						{
				  			if ($field_data === FALSE) $line_error = TRUE;
				  			if ($f != 'dummy') $usr_data[$f] = $field_data;
				  			$field_data = next($line_data);
						}
						if ($line_error)
						{
				  			if ($msg) $msg .= "<br />";
				  			$msg .= LAN_CONVERT_33.$line_counter;
				  			$error_counter++;
						}
						else
						{
							if ($csv_pw_not_encrypted)
							{
								$usr_data['user_password'] = md5($usr_data['user_password']);
							}
				 			$line_error = $usr->saveData($usr_data);
							if ($line_error === TRUE)
							{
								$write_counter++;
							}
							else
							{
								$line_error = $usr->getErrorText($line_error);
								if ($msg) $msg .= "<br />";
								$msg .= str_replace('--ERRNUM--',$line_error,LAN_CONVERT_34).$line_counter;
								$error_counter++;
							}
						}
					}
				}

			if ($msg) $msg .= "<br />";
			if ($import_delete_existing_data) $msg .= LAN_CONVERT_40.'<br />';
			$msg .= str_replace(array('--LINES--','--USERS--', '--ERRORS--'),array($line_counter,$write_counter,$error_counter),LAN_CONVERT_35);
			}
		break;

		case 'db' :
			if(dbImport() == false)
			{
				$abandon = true;
			}
		break;
		
		case 'rss' :
			if(rssImport() == false)
			{
				$abandon = true;
			}
		break;
	}

	if ($msg)
	{
		$mes->add($msg, E_MESSAGE_INFO); //  $ns -> tablerender(LAN_CONVERT_30, $msg);
		$msg = '';
	}

	if ($abandon)
	{
	//	unset($_POST['do_conversion']);
		$text = "
		<form method='get' action='".e_SELF."'>
		<div class='center'>
		".$frm->admin_button('dummy_continue',LAN_CONTINUE, 'execute')."
		</div>
		</form>";
		$ns -> tablerender(LAN_CONVERT_30,$mes->render(). $text);
		require_once(e_ADMIN."footer.php");
		exit;
	}
}
*/
/*

function rssImport()
{
	global $current_db_type, $db_import_blocks, $import_delete_existing_data,$db_blocks_to_import;
	
	$mes = e107::getMessage();
	$mes->addDebug("Loading: RSS");	
	if(!varset($_POST['do_conversion']))
	{
		$mes->addWarning("Under Construction"); 	
	}	
	
	return dbImport('xml');
	
}

function dbImport($mode='db')
{
	global $current_db_type, $db_import_blocks, $import_delete_existing_data,$db_blocks_to_import;
	
	$mes = e107::getMessage();
	
	// if (IMPORT_DEBUG) echo "Importing: {$current_db_type}<br />";
	$mes->addDebug("Loading: ".$current_db_type);
	
	if (class_exists($current_db_type))
	{
		$mes->addDebug("Class Available: ".$current_db_type);   
		$converter = new $current_db_type;
		$converter->init();
	}
	else
	{
		$mes->addError(LAN_CONVERT_42. "[".$current_db_type."]");
		return false;
	}
	
	if($mode == 'db') // Don't do DB check on RSS/XML 
	{
		if (!isset($_POST['dbParamHost']) || !isset($_POST['dbParamUsername']) || !isset($_POST['dbParamPassword']) || !isset($_POST['dbParamDatabase']))
		{
			$mes->addError(LAN_CONVERT_41);
			return false;
		}
	
		$result = $converter->db_Connect($_POST['dbParamHost'],	$_POST['dbParamUsername'], $_POST['dbParamPassword'], $_POST['dbParamDatabase'],  $_POST['dbParamPrefix']);
		if ($result !== TRUE)
		{
			$mes->addError(LAN_CONVERT_43.": ".$result);  // db connect failed
			return false;
		}	
	}	

	if(!is_array($db_import_blocks))
	{
		$mes->addError("No areas selected for import");  // db connect failed
		return false;	
	}

	if(vartrue($converter->override))
	{
		return;
	}
		


	foreach ($db_import_blocks as $k => $v)
	{
		if (isset($db_blocks_to_import[$k]))
		{
			$loopCounter = 0;
			$errorCounter = 0;
			
			if (is_readable($v['classfile']))
			{
				require_once($v['classfile']);
			}
			else
			{
				$mes->addError(LAN_CONVERT_45.': '.$v['classfile']);   // can't read class file.
				return false;
			}

			if (varset($_POST["import_block_{$k}"],0) == 1)
			{
				//if (IMPORT_DEBUG) echo "Importing: {$k}<br />";
				$mes->addDebug("Importing: ".$k);
				
			  	$result = $converter->setupQuery($k,!$import_delete_existing_data);
						
			  	if ($result !== TRUE)
			  	{
					$mes->addError(LAN_CONVERT_44.' '.$k);   // couldn't set query
					//	$msg .= "Prefix = ".$converter->DBPrefix;
					break;
			  	}
				
			  	$exporter = new $v['classname'];		// Writes the output data
			 				 	
				switch ($k)  // Do any type-specific default setting
				{
					case 'users' :
					  $exporter->overrideDefault('user_class',$checked_class_list);
					  break;
				}
				
			  	if ($import_delete_existing_data)
				{
					$exporter->emptyTargetDB();		// Clean output DB - reasonably safe now	
				} 
				
				while ($row = $converter->getNext($exporter->getDefaults(),$mode))
				{
					$loopCounter++;
		   			$result = $exporter->saveData($row);
					if ($result !== TRUE)
					{
						$errorCounter++;
						$line_error = $exporter->getErrorText($result);
					//	if ($msg) $msg .= "<br />";
						$msg = str_replace(array('--ERRNUM--','--DB--'),array($line_error,$k),LAN_CONVERT_46).$loopCounter;
						$mes->addError($msg);   // couldn't set query
					}
				}
				
				$converter->endQuery(); 
				
				unset($exporter);
				
				
				$msg = str_replace(array('--LINES--','--USERS--', '--ERRORS--','--BLOCK--'),
				array($loopCounter,$loopCounter-$errorCounter,$errorCounter, $k),LAN_CONVERT_47);
				$mes->addSuccess($msg);   // couldn't set query
			}
			else
			  {
			  		$mes->addDebug("Error: _POST['import_block_{$k}'] = ".$_POST['import_block_{$k}']);   // cou
					
			  }
		  }
		  else
		  {
		  		$mes->addDebug("\$db_blocks_to_import doesn't contain key: ".$k);   // cou
				
		  }
		}

//	  $msg = LAN_CONVERT_29;
	return true;
	// $abandon = FALSE;	
}
*/

//======================================================
// 					Display front page
//======================================================
new import_admin();
require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
 exit;




/*
 *	Currently unused function - shows available import methods and capabilities
 */
/*
function showStartPage()
{
    global $emessage, $frm, $import_class_names, $import_class_support, $db_import_blocks, $import_class_comment;

	$frm = e107::getForm();
	

	$text = "
	<form method='get' action='".e_SELF."' id='core-import-form'>
		<fieldset id='core-import-select-type'>
		<legend class='e-hideme'>".'DBLAN_10'."</legend>
		".$frm->hidden('mode','main')."
		".$frm->hidden('action','import')."
            <table class='table adminlist'>
			<colgroup>
			<col />
			<col />
			<col />
			<col />
			<col />
			</colgroup>
			<thead>
			<tr>
            	<th>".LAN_CONVERT_06."</th>";
                foreach($db_import_blocks as $name)   // 1 column for each of users, news, forum etc.
				{
                	$text .= "<th class='center'>".$name['message']."</th>";
 				}

				$text.="
				<th class='center'>".LAN_OPTIONS."</th>

			</tr>
			</thead>
			<tbody>

			<tr>
			<td><img src='".e_PLUGIN."import/images/csv.png' alt='' style='float:left;height:32px;width:32px;margin-right:4px'>CSV</td>
			<td class='center'>".ADMIN_TRUE_ICON."</td>";
			
			for ($i=0; $i < count($db_import_blocks)-1; $i++) 
			{ 
				$text .= "<td>&nbsp;</td>";	
			}

			
			$text .= "<td class='center middle'>".$frm->admin_button('import_type', 'csv', 'other',"Select")."</td></tr>";


        foreach ($import_class_names as $k => $title)
		{
			$iconFile = e_PLUGIN."import/images/".str_replace("_import","",strtolower($k)).".png";		
			$icon = (file_exists($iconFile)) ? "<img src='{$iconFile}' alt='' style='float:left;height:32px;width:32px;margin-right:4px'>" : "";
			
          	$text .= "<!-- $title -->
			<tr><td>".$icon.$title."<div class='smalltext'>".$import_class_comment[$k]."</div></td>\n";

			 foreach($db_import_blocks as $key=>$val)
			 {
 			 	$text .= "<td class='center'>".(in_array($key,$import_class_support[$k]) ? ADMIN_TRUE_ICON : "&nbsp;")."</td>\n";
			 }

             $text .= "
			 	<td class='center middle'>";
				
				$text .= $frm->admin_button('type', $k, 'other',"Select");
			// 	$text .= $frm->admin_button('import_type', $k, 'other',"Select");
				
				$text .= "
			 	</td>
			 </tr>";
		}


		$text .= "
				</tbody>
			</table>
			<div class='buttons-bar center'>
				".$frm->hidden('trigger_import',1)."
				
			</div>
		</fieldset>
	</form>";

	echo $emessage->render().$text; 
	// $ns->tablerender(LAN_PLUGIN_IMPORT_NAME, $emessage->render().$text);

}




function showImportOptions($mode='csv')
{
	global $text, $emessage, $csv_names, $import_class_names, $e_userclass, $db_import_blocks, $import_class_support, $import_default_prefix;
	
	$frm = e107::getForm();
	$ns = e107::getRender();
	
	$mes = e107::getMessage();
	
	if (class_exists($mode))
	{
		$mes->addDebug("Class Available: ".$mode);   
		$proObj = new $mode;
		if($proObj->init()===FALSE)
		{
			return;
		}
	}

	$message = "<strong>".LAN_CONVERT_05."</strong>";
	$emessage->add($message, E_MESSAGE_WARNING);

	$text = "
	<form method='post' action='".e_SELF."?import_type=".$_GET['import_type']."'>
    <table class='table adminform'>
    	<colgroup>
    		<col class='col-label' />
    		<col class='col-control' />
    	</colgroup>";

	if($mode == "csv")
	{
		$text .= "
		<tr>
		  <td>".LAN_CONVERT_07."</td>
		  <td><select name='csv_format' class='tbox'>\n";
		  foreach ($csv_names as $k => $v)
		  {
			$s = ($current_csv == $k) ? " selected='selected'" : '';
			$text .= "<option value='{$k}'{$s}>{$v}</option>\n";
		  }
	  	$text .= "</select>\n
		  </td>
		</tr>

		<tr>
		<td>".LAN_CONVERT_36."</td>
		<td><input class='tbox' type='text' name='csv_data_file' size='30' value='{$csv_data_file}' maxlength='100' /></td>
		</tr>

		<tr><td>".LAN_CONVERT_17."
		</td>
		<td>

		<input type='hidden' name='import_source' value='csv' />
		<input type='checkbox' name='csv_pw_not_encrypted' value='1'".($csv_pw_not_encrypted ? " checked='checked'" : '')."/>
		<span class='smallblacktext'>".LAN_CONVERT_18."</span></td>
		</tr>
		";

	}
	elseif(method_exists($proObj,"config"))
	{
		$ops  = $proObj->config();
		foreach($ops as $key=>$val)
		{
			$text .= "<tr>
				<td>".$val['caption']."</td>
				<td>".$val['html'];
			$text .= (vartrue($val['help'])) ? "<div class='field-help'>".$val['help']."</div>" : "";	
			$text .= "</td>
			</tr>\n";		
		}
		
		if($proObj->sourceType)
		{
			$text .= "<input type='hidden' name='import_source' value='".$proObj->sourceType."' />\n";	
		} 			
				
	}
	else
	{
    	$importType = $import_class_names[$mode];

    	$text .= "
		<tr>
		<td>$importType ".LAN_CONVERT_19."</td>
		<td><input class='tbox' type='text' name='dbParamHost' size='30' value='".(varset($_POST['dbParamHost']) ? $_POST['dbParamHost'] : 'localhost')."' maxlength='100' /></td>
		</tr>
		<tr>
		<td >$importType ".LAN_CONVERT_20."</td>
		<td ><input class='tbox' type='text' name='dbParamUsername' size='30' value='".varset($_POST['dbParamUsername'])."' maxlength='100' /></td>
		</tr>
		<tr>
		<td >$importType ".LAN_CONVERT_21."</td>
		<td ><input class='tbox' type='text' name='dbParamPassword' size='30' value='".varset($_POST['dbParamPassword'])."' maxlength='100' /></td>
		</tr>
		<tr>
		<td >$importType ".LAN_CONVERT_22."</td>
		<td ><input class='tbox' type='text' name='dbParamDatabase' size='30' value='".varset($_POST['dbParamDatabase'])."' maxlength='100' /></td>
		</tr>
		<tr>
		<td >$importType ".LAN_CONVERT_23."</td>
		<td ><input class='tbox' type='text' name='dbParamPrefix' size='30' value='".(varset($_POST['dbParamPrefix']) ? $_POST['dbParamPrefix'] : $import_default_prefix[$mode])."' maxlength='100' />
		<input type='hidden' name='import_source' value='db' />
  		</td>
		</tr>";

	}

	if($mode != 'csv')
	{
		$text .= "
		<tr>
		<td >$importType ".LAN_CONVERT_24."</td>
		<td >";

		$defCheck = (count($import_class_support[$mode])==1) ? "checked='checked'" : "";
   	  	foreach ($db_import_blocks as $k => $v)
	  	{
			if(in_array($k, $import_class_support[$mode])) // display only the options supported.
			{
				$text .= "<input type='checkbox' name='import_block_{$k}' id='import_block_{$k}' value='1' {$defCheck} />&nbsp;".$v['message'];
				$text .= "<br />";
			}
	  	}
	  	$text .= "</td></tr>";		
	}


	$text .= "<tr><td>".LAN_CONVERT_38."</td>
	<td><input type='checkbox' name='import_delete_existing_data' value='1'".(varset($_POST['import_delete_existing_data']) ? " checked='checked'" : '')."/>
	<span class='smallblacktext'>".LAN_CONVERT_39."</span></td>
	</tr>";
	
	if(varset($proObj->defaultClass) !== false)
	{
		$text .= "
		<tr><td>".LAN_CONVERT_16."</td>
		<td>";
  		$text .= $e_userclass->vetted_tree('classes_select',array($e_userclass,'checkbox'), varset($_POST['classes_select']),'main,admin,classes,matchclass, no-excludes');
  		$text .= "</td></tr>";
	}
 	
  	$action = varset($proObj->action,'do_conversion');
  	$text .= "</table>
	<div class='buttons-bar center'>".$frm->admin_button($action,LAN_CONTINUE, 'execute').
	
	$frm->admin_button('back',LAN_CANCEL, 'cancel')."
	<input type='hidden' name='db_import_type' value='$mode' />
	<input type='hidden' name='import_type' value='".$mode."' />
	</div>
	</form>";

	// Now a little bit of JS to initialise some of the display divs etc
  	$temp = '';
  	if(varset($import_source)) { $temp .=  "disp('{$import_source}');"; }
  	if (varset($current_db_type)) $temp .= " flagbits('{$current_db_type}');";
  	if (varset($temp)) $text .= "<script type=\"text/javascript\"> {$temp}</script>";

  	$ns -> tablerender(LAN_PLUGIN_IMPORT_NAME.SEP.$importType, $emessage->render().$text);

}
*/





function csv_split(&$data,$delim=',',$enveloper='')
{
  $ret_array = array();
  $fldval='';
  $enclosed = false;
// $fldcount=0;
// $linecount=0;
  for($i=0;$i<strlen($data);$i++)
  {
	$c=$data[$i];
	switch($c)
	{
	  case $enveloper :
		if($enclosed && ($i<strlen($data)) && ($data[$i+1]==$enveloper))
		{
		  $fldval .= $c;
		  $i++; //skip next char
		}
		else
		{
		  $enclosed  = !$enclosed;
		}
		break;

	  case $delim :
		if(!$enclosed)
		{
		  $ret_array[]= $fldval;
		  $fldval='';
		}
		else
		{
		  $fldval.=$c;
		}
		break;
	  case "\r":
	  case "\n":
		$fldval .= $c;	// We may want to strip these
		break;
	  default:
		$fldval .= $c;
	}
  }
  if($fldval)
	$ret_array[] = $fldval;
  return $ret_array;
}





function headerjs()
{
	return;
//  global $import_class_names;		// Keys are the various db options
  global $import_class_support;
  global $db_import_blocks;
  global $import_class_comment;

  $vals = "var db_names = new Array();\n";
  $texts = "var db_options = new Array();\n";
  $blocks = "var block_names = new Array();\n";
  $comments = "var comment_text = new Array();\n";
  
  $i = 0;
  foreach ($db_import_blocks as $it => $val)
  {
	$blocks .= "block_names[{$i}]='{$it}';\n";
	$i++;
  }

  $i = 0;
  foreach ($import_class_support as $k => $v)
  {
	$vals .= "db_names[$i] = '{$k}';\n";
	$comments .= "comment_text[$i] = '{$import_class_comment[$k]}';\n";
//	$temp = $import_class_support[$k];		// Array of import types supported
	$j = 0;
	$m = 1;		// Mask bit
	foreach ($db_import_blocks as $it => $val)
	{
	  if (in_array($it,$v)) $j = $j + $m;
	  $m = $m + $m;
	}
	$texts .= "db_options[{$i}] = {$j};\n";
	$i++;
  }

  $text = "
	<script type='text/javascript'>{$vals}{$texts}{$blocks}{$comments}
	function disp(type) 
	{
	  if(type == 'csv')
	  {
		document.getElementById('import_csv').style.display = '';
		document.getElementById('import_db').style.display = 'none';
		return;
	  }

	  if(type =='db')
	  {
        document.getElementById('import_csv').style.display = 'none';
		document.getElementById('import_db').style.display = '';
		return;
	  }
	}
	
	function flagbits(type)
	{
	  var i,j;
	  for (i = 0; i < ".count($import_class_support)."; i++)
	  {
	    if (type == db_names[i])
		{
		  var mask = 1;
		  for (j = 0; j < ".count($db_import_blocks)."; j++)
		  {
			var checkbox = document.getElementById('import_block_'+block_names[j]);
			if (checkbox != null)
			{
			  if (db_options[i] & mask)
			  {
			    checkbox.checked = 'checked';
				checkbox.disabled = '';
			  }
			  else
			  {
				checkbox.checked = '';
				checkbox.disabled = 'disabled';
			  }
			}
			else
			{
			  alert('Could not find: '+'import_block_'+block_names[j]);
			}
			mask = mask + mask;
		  }
		  var checkbox = document.getElementById('db_comment_block');
		  if (checkbox != null) checkbox.innerHTML = comment_text[i];
		  return;
		}
	  }
	  alert('Type not found: '+type);
	}
	</script>";

	return $text;
}



?>
