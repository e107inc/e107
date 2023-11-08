<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Cron Administration - Scheduled Tasks 
 *
 */

require_once(__DIR__.'/../class2.php');
if (!getperms('U'))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('cron', true);

class cron_admin extends e_admin_dispatcher
{

	protected $modes = array(
		'main'		=> array(
			'controller' 	=> 'cron_admin_ui',
			'path' 			=> null,
			'ui' 			=> 'cron_admin_form_ui',
			'uipath' 		=> null
		)				
	);	


	protected $adminMenu = array(
		'main/list'		=> array('caption'=> LAN_MANAGE, 'perm' => '0'),
		'main/refresh' 	=> array('caption'=> LAN_CRON_M_02, 'perm' => '0','url'=>'cron.php', 'icon'=>'fa-sync'),
	//	'main/prefs' 	=> array('caption'=> 'Settings', 'perm' => '0'),
	//	'main/custom'	=> array('caption'=> 'Custom Page', 'perm' => '0')		
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = PAGE_NAME;

	protected $adminMenuIcon = 'e-cron-24';

}

class cron_admin_ui extends e_admin_ui
{
		
		protected $pluginTitle	= PAGE_NAME;
		protected $pluginName	= 'core';
		protected $table		= "cron";
		protected $pid			= "cron_id";
		protected $listOrder	= 'cron_category desc'; // Show 'upgrades' on first page. 
		protected $perPage		= 10;
		protected $batchDelete	= TRUE;
			   	
    	protected $fields = array(
			'checkboxes'		=> array('title'=> '',				'type' => null, 			'width' =>'5%', 	'forced'=> TRUE, 'thclass'=>'center', 'class'=>'center'),
			'cron_id'			=> array('title'=> LAN_ID,			'type' => 'number',			'width' =>'5%', 	'forced'=> FALSE, 'nolist'=>TRUE),
       		'cron_category'		=> array('title'=> LAN_CATEGORY, 	'type' => 'method', 		'data' => 'str',		'width'=>'auto','readonly' => 1,	'thclass' => '', 'batch' => TRUE, 'filter'=>TRUE),
       		'cron_name'			=> array('title'=> LAN_NAME,		'type' => 'text',			'data'=>'str', 'width' => 'auto',	'readonly' => 1),
         	'cron_description'	=> array('title'=> LAN_DESCRIPTION,	'type' => 'text',			'data'=>'str', 'width' => '35%',	'readonly' => 1),
         	'cron_function'		=> array('title'=> LAN_CRON_2,		'type' => 'text',			'data'=>'str', 'width' => 'auto', 	'thclass' => 'left first', 'readonly' => 1),
         	'cron_tab'			=> array('title'=> LAN_CRON_3,		'type' => 'method',			'width' => 'auto'), // Display name
		 	'cron_lastrun'		=> array('title'=> LAN_CRON_4,		'type' => 'datestamp',		'data' => 'int',	'width' => 'auto', 'readonly' => 2, 'readParms'=>['mask'=>'dd M yyyy hh:ii:ss']),
     		'cron_active' 		=> array('title'=> LAN_ACTIVE,		'type' => 'boolean',		'data'=> 'int', 'thclass' => 'center', 'class'=>'center', 'inline'=>true, 'filter' => true, 'batch' => true,	'width' => 'auto'),
			'options' 			=> array('title'=> LAN_OPTIONS,		'type' => 'method',			'data'=> null, 'noedit'=>TRUE, 'forced'=>TRUE, 'width' => '10%', 'thclass' => 'center last', 'class' => 'right')
		);
		
		
		// public function beforeCreate($new_data)
		// {
		
		// }
		private $curCrons = array();
		private $activeCrons = 0;
				
		function init()
		{
			$pref	= e107::getPref();
			$sql	= e107::getDb();
			
			if(vartrue($_POST['cron_execute']))
			{
				$executeID = key($_POST['cron_execute']);
				$this->cronExecute($executeID);
			}
	
			
			if (empty(e107::getPref('e_cron_pwd')) || !empty($_POST['generate_pwd']))
			{
				$this->setCronPwd();
			}
			
			$sql->gen("SELECT cron_function,cron_active FROM #cron ");
			while($row = $sql->fetch())
			{
				$this->curCrons[] = $row['cron_function'];
				if($row['cron_active']==1)
				{
					$this->activeCrons++;	
				}
			}
			
			$this->lastRefresh();
			// Import Core and Plugin e_cron data 
			
			$cronDefaults['_system'] = array(
				0 => array(
					'name' 			=> LAN_CRON_01_1,
					'function' 		=> 'sendEmail',
					'category'		=> 'mail',
					'description'   => str_replace("[eml]",$pref['siteadminemail'],LAN_CRON_01_2) ."<br />". LAN_CRON_01_3
					),
				1 => array(
					'name' 			=> LAN_CRON_02_1,
					'category'		=> 'mail',
					'function' 		=> 'procEmailQueue',
					'description' 	=> LAN_CRON_02_2
					),
				2 => array(
					'name' 			=> LAN_CRON_03_1,
					'category'		=> 'mail',
					'function' 		=> 'procEmailBounce',
					'description' 	=> LAN_CRON_03_2
				//	'available' 	=> vartrue($pref['mail_bounce_auto'])
				),
				3 => array(
					'name' 			=> LAN_CRON_04_1,
					'category'		=> 'user',
					'function' 		=> 'procBanRetrigger',
					'description' 	=> LAN_CRON_04_2 ."<br />". LAN_CRON_04_3,
					'available' 	=> e107::getPref('ban_retrigger')
				),
				4 => array(
					'name' 			=> LAN_CRON_05_1,
					'category'		=> 'backup',
					'function' 		=> 'dbBackup',
					'description' 	=> LAN_CRON_05_2 .' '.e_SYSTEM.'backups/'
				//	'available' 	=> e107::getPref('ban_retrigger')
				),
				5 => array(
					'name' 			=> LAN_CRON_06_1,
					'category'		=> 'user',
					'function' 		=> 'procBanRetrigger',
					'description' 	=> defset('LAN_CRON_06_2') ."<br />". defset('LAN_CRON_06_3'),
				//	'available' 	=> e107::getPref('ban_retrigger')
				),
				6 => array(
					'name' 			=> LAN_CRON_20_1,
					'category'		=> 'update',
					'function' 		=> 'checkCoreUpdate',
					'description' 	=> LAN_CRON_20_2 ."<br />". LAN_CRON_20_3,
				//	'available' 	=> e107::getPref('ban_retrigger')
				),
				
			);
			
			if(is_dir(e_BASE.".git"))
			{
				$cronDefaults['_system'][7] = array(
					'name' 			=> LAN_CRON_20_4,
					'category'		=> 'update',
					'function' 		=> 'gitrepo',
					'description' 	=> LAN_CRON_20_5."<br />".LAN_CRON_20_6."<br /><span class='label label-warning'>".LAN_WARNING."</span> ".LAN_CRON_20_8,
				//	'available' 	=> e107::getPref('ban_retrigger')
				);

			}


			if(is_dir(e_THEME.$pref['sitetheme']."/.git"))
			{
				$cronDefaults['_system'][8] = array(
					'name' 			=> LAN_CRON_65,
					'category'		=> 'update',
					'function' 		=> 'gitrepoTheme',
					'description' 	=> LAN_CRON_20_6."<br /><span class='label label-warning'>".LAN_WARNING."</span> ".LAN_CRON_20_8,
				//	'available' 	=> e107::getPref('ban_retrigger')
				);

			}
			
			
			
	
			if(!vartrue($_GET['action']) || $_GET['action'] == 'refresh')
			{
				
				$this->cronImport($cronDefaults);	// import Core Crons (if missing)
				$this->cronImport(e107::getAddonConfig('e_cron'));	// Import plugin Crons
				$this->cronImportLegacy(); // Import Legacy Cron Tab Settings	
			}

			$this->renderHelp();

		}
		
		

		/**
		 * Import Cron Settings into Database. 
		*/
		public function cronImport($new_cron = array())
		{
			if(empty($new_cron))
			{
				return null;
			}

			$tp = e107::getParser();

			foreach($new_cron as $class => $ecron)
			{
				foreach($ecron as $val)
				{
					$insert = array(
						'cron_id'			=> 0,
						'cron_name'			=> $val['name'],
						'cron_category'		=> $val['category'],
						'cron_description' 	=> $tp->toDB($val['description']),
						'cron_function'		=> $class."::".$val['function'],
						'cron_tab'			=> varset($val['tab'], '* * * * *'),
						'cron_active'		=> varset($val['active'], '0'),
					);	
					
					$this->cronInsert($insert);							
				}
			}	
		}
		
		
		
		/**
		 * Import Legacy e_cron_pref settings. 
		 */
		public function cronImportLegacy()
		{
			global $pref;
			
			$cronPref = e107::getPref('e_cron_pref');
			
			
			if(!is_array($cronPref))
			{
				return;
			}
									
			foreach($cronPref as $val)
			{
				$update = array(
					'cron_tab'		=> $val['tab'],
					'cron_active'	=> $val['active'],
					'cron_function' => $val['class']."::".$val['function'],
					'WHERE'			=> "cron_function = '".$val['class']."::".$val['function']."'"
				);	
				
				$this->cronUpdate($update);					
			}
			
			e107::getConfig()->remove('e_cron_pref')->save(); 
		}
		
		
		
		
		// Insert a Cron. 
		public function cronInsert($insert)
		{
			// print_a($insert);
			// return;
// 			
			$sql = e107::getDb();
			
			if(in_array($insert['cron_function'],$this->curCrons))
			{
				return;			
			}
			
			if(!$sql->insert('cron',$insert))
			{
				e107::getMessage()->addDebug(LAN_CRON_6);
			}
			else
			{
				e107::getMessage()->add(LAN_CRON_8.": ".$insert['cron_function'], E_MESSAGE_INFO); 
			}	
		}
		
		
		
		/**
		 * Update cron timing - from legacy Pref. 
		 */
		public function cronUpdate($insert)
		{
			 // print_a($insert);
			 // return;
			
			$sql = e107::getDb();
			
			$cron_function = $insert['cron_function'];
			unset($insert['cron_function']);
					
			if($sql->update('cron',$insert)===FALSE)
			{
				e107::getMessage()->add(LAN_CRON_7, E_MESSAGE_ERROR);
			}
			else
			{			
				e107::getMessage()->add(LAN_CRON_8.$cron_function, E_MESSAGE_INFO);
			}	
		}
		
		
		
		
		
		// Process _POST before saving. 
		public function beforeUpdate($new_data, $old_data, $id)
		{

			$tab = [];
			foreach($new_data['tab'] as $t)
			{
				$tab[] = implode(",", $t);
			}

			$new_data['cron_tab'] = implode(" ", $tab);

			e107::getMessage()->addDebug("Cron Tab: ".$new_data['cron_tab']);
			return $new_data;
		}
		
		
		function setCronPwd()
		{
			//global $pref;	
			$userMethods = e107::getUserSession();
			$newpwd = $userMethods->generateRandomString('*^*#.**^*');
			$newpwd = sha1($newpwd.time());

			e107::getConfig()->set('e_cron_pwd', $newpwd)->save(false);
			return true;
	
		}
		
		
		
		function lastRefresh()
		{
			$pref 	= e107::getPref();
			$mes 	= e107::getMessage();
			$frm = e107::getForm();
			
			if(file_exists(e_CACHE.'cronLastLoad.php'))
			{
				$lastload = intval(@file_get_contents(e_CACHE.'cronLastLoad.php'));
			}
			else
			{
				$lastload = 0;
			}

			$ago = (time() - $lastload);
	
			$active = ($ago < 1200) ? true : false; // longer than 20 minutes, so lets assume it's inactive.
			$status = ($active) ? "<span class='label label-success'>".LAN_ENABLED."</span>" : "<span class='label label-danger'>".LAN_DISABLED."</span>"; // "Enabled" : "Offline";
	
			$mins = floor($ago / 60);
			$secs = $ago % 60;

			$srch = array("[x]","[y]");
			$repl = array($mins,$secs);
	
			$lastRun = ($mins) ? str_replace($srch,$repl,LAN_CRON_9) : str_replace($srch,$repl,LAN_CRON_10); // FIX: check syntax

			$lastRefresh = ($ago < 10000) ? "<p><span class='pull-right;'><b>$lastRun</b><small>(".date('g:i A',$lastload).")</small></span>" : "<span class='pull-right'><b>".LAN_NEVER."</b></span>";
	
			$mes->addInfo('<p>'.LAN_STATUS.":<span class='pull-right'><b>".$status."</b></span></p>");
			$mes->addInfo('<p>'.LAN_CRON_11.":<span class='pull-right'><spab class='badge'>".$this->activeCrons."</span></span></p><br />");
			$mes->addInfo(LAN_CRON_12.":".$lastRefresh."<br /><br />");


			// extensions of exe, com, bat and cmd.
			
			$isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
			$actualPerm = substr(decoct(fileperms(e_BASE."cron.php")),3);

			if($isWin)
			{
				$mes->addWarning(LAN_CRON_13);
			}
			if (!$isWin && $actualPerm != 755) // is_executable() is not reliable. 
			{
				$mes->addWarning(LAN_CRON_14);
			}
			elseif (!$active) // show instructions
			{
				$setpwd_message = $frm->open("generate").LAN_CRON_15.":
				<br /><pre style='user-select:all; cursor:pointer; padding-top:20px; padding-bottom:25px; max-width:326px; overflow-x:scroll'>".e_ROOT."cron.php token=".$pref['e_cron_pwd'].' >/dev/null 2>&1';
				
				$setpwd_message .= "</pre>". LAN_CRON_16;
				if(e_DOMAIN && file_exists("/usr/local/cpanel/version"))
				{
					$setpwd_message .= "<div style='margin-top:10px'><a rel='external' class='btn btn-primary' href='".e_HTTP."cpanel'>".LAN_CRON_60."</a></div>";
					
				}
				$setpwd_message .= "<br /><br />".$frm->admin_button('generate_pwd', 1, 'delete', LAN_CRON_61 ,array('class'=>'btn btn-sm'));
				$setpwd_message .= $frm->close();	
				
				$mes->add($setpwd_message, E_MESSAGE_INFO);
			}
	
		}

		function cronExecute($cron_id)
		{
			$sql = e107::getDb();
			$class_func = '';

			if($sql->select("cron","cron_name,cron_function","cron_id = ".intval($cron_id)))
			{
				$row = $sql->fetch();
				$class_func = $row['cron_function'];
				$cron_name = $row['cron_name'];	
			}
			
			if(!$class_func)
			{
				return;
			}

			
			list($class_name, $method_name) = explode("::", $class_func);
			$mes = e107::getMessage();
			$taskName = $class_name;
			
			if ($class_name == '_system')
			{
				require_once(e_HANDLER.'cron_class.php');
			}
			else
			{
				require_once(e_PLUGIN.$class_name.'/e_cron.php');
			}
			$class_name .= '_cron';
			$status = $this->cronExecuteMethod($class_name, $method_name) ? E_MESSAGE_SUCCESS : E_MESSAGE_ERROR;
			$mes->add(LAN_CRON_RUNNING.":<b>".$cron_name."</b>", $status);
		}
		
		
		
		function cronExecuteMethod($class_name, $method_name, $return = 'boolean')
		{
			$mes = e107::getMessage();
	
			if (class_exists($class_name))
			{
				$obj = new $class_name;
				if (method_exists($obj, $method_name))
				{
					$message = str_replace('[x]', $class_name." : ".$method_name, LAN_CRON_62);//Executing config function [b][x][/b]
					$message = e107::getParser()->toHTML($message,true);
					$mes->add($message, E_MESSAGE_DEBUG);
					if ($return == 'boolean')
					{
						call_user_func(array($obj, $method_name));
						return TRUE;
					}
					else
					{
						return call_user_func(array($obj, $method_name));
					}
				}
				else
				{
					$message = str_replace('[x]', $method_name."()", LAN_CRON_63 );//Config function [b][x][/b] NOT found.
					$message = e107::getParser()->toHTML($message,true);
					$mes->add($message, E_MESSAGE_DEBUG);
				}
			}
			return FALSE;
		}		

		function renderHelp()
		{
			return array('caption'=>LAN_HELP, 'text'=>e107::getParser()->toHTML(LAN_CRON_64, true));
		}
		
}

class cron_admin_form_ui extends e_admin_form_ui
{
	
	var $min_options = array(
						"*"																						 => LAN_CRON_30,
						"0,2,4,6,8,10,12,14,16,18,20,22,24,26,28,30,32,34,36,38,40,42,44,46,48,50,52,54,56,58"	 => LAN_CRON_31,
						"0,5,10,15,20,25,30,35,40,45,50,55"														 => LAN_CRON_32,
						"0,10,20,30,40,50"																		 => LAN_CRON_33,
						"0,15,30,45"																			 => LAN_CRON_34,
						"0,30"																					 => LAN_CRON_35
		);

	var	$hour_options = array(
						"*"								 => LAN_CRON_36,
						"0,2,4,6,8,10,12,14,16,18,20,22" => LAN_CRON_37,
						"0,3,6,9,12,15,18,21"			 => LAN_CRON_38,
						"0,6,12,18"						 => LAN_CRON_39
	);
	
	
	var $cronCategories = array(					
						'backup'	=> LAN_CRON_BACKUP,
						'content'	=> ADLAN_CL_3,
						'log'		=> LAN_CRON_LOGGING,
						'mail'		=> ADLAN_136,				
						'notify'	=> ADLAN_149, 
						'user'		=> LAN_USER,
						'plugin'	=> ADLAN_CL_7,
						'update'	=> LAN_UPDATE
	);
	
	/**
	 * Render cron_tab field
	 */
	function cron_tab($curVal,$mode)  
	{ 
		if($mode == 'read')
		{
			$sep = array();
			list($min, $hour, $day, $month, $weekday) = explode(" ", $curVal);
			$text = (isset($this->min_options[$min])) ? $this->min_options[$min] : LAN_CRON_50 ." ". $min;	// Minute(s)
			$text .= "<br />";
			$text .= (isset($this->hour_options[$hour])) ? $this->hour_options[$hour] : LAN_CRON_51 ." ". $hour;	// Hour(s)		
			$text .= "<br />";
			$text .= ($day != '*') ? LAN_CRON_52 ." ". $day : LAN_CRON_40;  // Day(s)
			$text .= "<br />";
			$text .= ($month != '*') ? LAN_CRON_53 ." ". eShims::strftime("%B", mktime(00, 00, 00, (int) $month, 1, 2000)) : LAN_CRON_41; // Month(s)
			$text .= "<br />";		 
			$text .= ($weekday != '*') ? LAN_CRON_54 ." ". eShims::strftime("%A", mktime(00, 00, 00, 5, (int) $weekday, 2000)) : LAN_CRON_42; // Weekday(s)
			
			
			return "<a class='e-tip' href=''>".defset('ADMIN_INFO_ICON')."</a>
			<div class='field-help'>".$text."</div>";

		}
		
		if($mode == 'write')
		{
			return $this->editTab($curVal);
		}
		
		if($mode == 'filter') // Custom Filter List 
		{		
			return;
		}
		
		if($mode == 'batch')
		{
			return;
		}
	}
	
	
	function cron_category($curVal,$mode)
	{
		if($mode == 'read')
		{
			return isset($this->cronCategories[$curVal]) ? 	$this->cronCategories[$curVal] : "";
		}
		
		if($mode == 'write')
		{
			return isset($this->cronCategories[$curVal]) ? 	$this->cronCategories[$curVal] : "";	
		}
		
		if($mode == 'filter')
		{
			return $this->cronCategories;	
		}
		if($mode == 'batch')
		{
			return;
		}
	}
	
	// Override the default Options field. 
	function options($parms, $value, $id, $attributes)
	{
		$att = $attributes;
		if($attributes['mode'] == 'read')
		{
			$func = $this->getController()->getFieldVar('cron_function');
		//
			if(substr($func,0,7) === '_system')
			{
				$att['readParms'] = array('disabled'=>'disabled');
			}

			$text = "<div class='btn-group pull-right'>";
			$text .= $this->renderValue('options',$value,$att,$id);
			$text .= $this->submit_image('cron_execute['.$id.']', 1, 'execute', LAN_RUN);
			$text .= "</div>";


			return $text;
		}
	}
	
	
	function editTab($curVal)
	{
		$sep = array();
		list($sep['minute'], $sep['hour'], $sep['day'], $sep['month'], $sep['weekday']) = explode(" ", $curVal);

		foreach ($sep as $key => $value)
		{
			if ($value == "")
			{
				$sep[$key] = "*";
			}
		}

		$minute = explode(",", $sep['minute']);
		$hour = explode(",", $sep['hour']);
		$day = explode(",", $sep['day']);
		$month = explode(",", $sep['month']);
		$weekday = explode(",", $sep['weekday']);

		

		$text = "
		<select style='height:140px' multiple='multiple' name='tab[minute][]'>
		\n";

		foreach ($this->min_options as $key => $val)
		{
			if ($sep['minute'] == $key)
			{
				$sel = "selected='selected'";
				$minute = array();
			}
			else
			{
				$sel = "";
			}
			
			$text .= "
			<option value='$key' $sel>".$val."</option>\n";
		}

		for ($i = 0; $i <= 59; $i++)
		{
			$sel = (in_array(strval($i), $minute)) ? "selected='selected'" : "";
			$text .= "
			<option value='$i' $sel>".$i."</option>\n";
		}
		
		$text .= "
		</select>
		<select style='height:140px' multiple='multiple' name='tab[hour][]'>
		\n";

		foreach ($this->hour_options as $key => $val)
		{
			if ($sep['hour'] == $key)
			{
			$sel = "selected='selected'";
			$hour = array();
			}
			else
			{
				$sel ="";
						}
						$text .= "<option value='$key' $sel>".$val."</option>\n";
					}

					for ($i = 0; $i <= 23; $i++)
					{
						$sel = (in_array(strval($i), $hour)) ? "selected='selected'" : "";
						$diz = mktime($i, 00, 00, 1, 1, 2000);
						$text .= "<option value='$i' $sel>".$i." - ".date("g A", $diz)."</option>\n";
					}
					$text .= "</select>
				
					<select style='height:140px' multiple='multiple' name='tab[day][]'>\n";

					$sel_day = ($day[0] == "*") ? "selected='selected'" : "";

					$text .= "<option value='*' {$sel_day}>".LAN_CRON_40."</option>\n"; // Every Day
					for ($i = 1; $i <= 31; $i++)
					{
						$sel = (in_array($i, $day)) ? "selected='selected'" : "";
						$text .= "<option value='$i' $sel>".$i."</option>\n";
					}
					$text .= "</select>
				
					<select style='height:140px' multiple='multiple' name='tab[month][]'>\n";

					$sel_month = ($month[0] == "*") ? "selected='selected'" : "";
					$text .= "<option value='*' $sel_month>".LAN_CRON_41."</option>\n"; // Every Month

					for ($i = 1; $i <= 12; $i++)
					{
						$sel = (in_array($i, $month)) ? "selected='selected'" : "";
						$diz = mktime(00, 00, 00, $i, 1, 2000);
						$text .= "<option value='$i' $sel>".eShims::strftime("%B", $diz)."</option>\n";
					}
					$text .= "</select>
				
					<select style='height:140px' multiple='multiple' name='tab[weekday][]'>\n";

					$sel_weekday = ($weekday[0] == "*") ? "selected='selected'" : "";
					$text .= "<option value='*' $sel_weekday>".LAN_CRON_42."</option>\n"; // Every Week Day.
				

					for ($i = 0; $i <= 6; $i++)
					{
						$sel = (in_array(strval($i), $weekday)) ? "selected='selected'" : "";
						$text .= "<option value='$i' $sel>".eShims::strftime("%A", mktime(00, 00, 00, 5, $i, 2000))."</option>\n";
					}
					$text .= "</select>
				";
			
			return $text;
		
	}
	
	
	
	
}

new cron_admin();



$e_sub_cat = 'cron';

require_once('auth.php');

e107::getAdminUI()->runPage();
$frm = e107::getForm();
// $cron = new cron();

require_once(e_ADMIN.'footer.php');


