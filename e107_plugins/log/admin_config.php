<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	Stats logging plugin - admin functions
 *
 */

require_once('../../class2.php');

if (!getperms('P') || !e107::isInstalled('log')) 
{
	e107::redirect('admin');
	exit;
}


if(e_AJAX_REQUEST && varset($_GET['action']) == 'rebuild')
{
	require_once(e_PLUGIN."log/consolidate.php");
	$lgc = new logConsolidate;

	$count = $_SESSION['stats_log_files_count'];
	$file = $_SESSION['stats_log_files'][$count]['path'];
	$totalFiles = $_SESSION['stats_log_files_total'] - 1;
	//$process = true;

	$lg = e107::getAdminLog();
//	$lg->addDebug(print_r($logVals, true));


	if($_SESSION['stats_log_files'][$count]['complete'] != 1)
	{
		$_SESSION['stats_log_files'][$count]['complete'] = 1;
	//	$lg->addSuccess($count."/".$totalFiles."\t".$file." processing", false);
	//	if($process)
		if($lgc->processRawBackupLog($file, true))
		{

		//	sleep(3);
			$lg->addSuccess($count."/".$totalFiles."\t".$file." processed.", false);
			$_SESSION['stats_log_files'][$count]['complete'] = 1;
			$_SESSION['stats_log_files_count']++;
		}
		else
		{
			$lg->addError($count."/".$totalFiles."\t".$file." failed.", false);
		}
	}
	else
	{
		$lg->addWarning($count."/".$totalFiles."\t".$file." skipped", false);
		$_SESSION['stats_log_files_count']++;
	}


	$totalOutput = round(( $count/ $totalFiles) * 100, 1);

	if($totalOutput > 99.9)
	{
		echo 100;
		if($lgc->collatePageTotalDB())
		{
			$lg->addSuccess("Processed All-Time PageTotal", false);
		}
		else
		{
			$lg->addError("Failed to Process All-Time PageTotal", false);
		}

		$lg->addSuccess("Processing Complete.", false);

	}
	else
	{
		echo $totalOutput;
	}

	$lg->toFile('SiteStatsUpgrade','Statistics Update Log', true);

	exit;

}

define('LogFlagFile', 'LogFlag.php');

e107::includeLan(e_PLUGIN.'log/languages/'.e_LANGUAGE.'.php');
e107::includeLan(e_PLUGIN.'log/languages/'.e_LANGUAGE.'_admin.php');

if(!is_writable(e_LOG))
{
	//$message = "<b>".ADSTAT_LAN_38."</b>"; 
	e107::getMessage()->addError(ADSTAT_LAN_28);
}

e107::css('inline', 'td.last.options { padding-right:20px } ');

	// Needed on Windoze platforms - not an ideal solution!
	if (!function_exists('nl_langinfo'))
	{
		define('MON_1',86400);
		define('MON_2',2764800);
		define('MON_3',5443200);
		define('MON_4',8035200);
		define('MON_5',10800000);
		define('MON_6',13392000);
		define('MON_7',15811200);
		define('MON_8',19008000);
		define('MON_9',21168000);
		define('MON_10',23760000);
		define('MON_11',26352000);
		define('MON_12',28944000);

		function nl_langinfo($mon)
		{
			return date('F',$mon);
		}
	}




	class log_adminArea extends e_admin_dispatcher
	{

		protected $modes = array(

			'main'	=> array(
				'controller' 	=> 'logstats_ui',
				'path' 			=> null,
				'ui' 			=> 'logstats_form_ui',
				'uipath' 		=> null
			),


		);


		protected $adminMenu = array(

			'main/prefs'		=> array('caption'=> LAN_SETTINGS, 'perm' => 'P'),
			'main/list'			=> array('caption'=> ADSTAT_LAN_48, 'perm' => 'P'),
			'main/export'		=> array('caption'=> ADSTAT_LAN_36, 'perm' => 'P'),
			'main/datasets'		=> array('caption'=> ADSTAT_LAN_63, 'perm' => 'P'),
			'main/rempage'		=> array('caption'=> ADSTAT_LAN_26, 'perm' => 'P'),
			'main/history'		=> array('caption'=> ADSTAT_LAN_69, 'perm' => 'P'),
			'main/rebuild'      => array('caption'=> ADSTAT_LAN_87, 'perm'=> 'P'),
		);





		protected $adminMenuAliases = array(
			'main/edit'	=> 'main/list'
		);

		protected $menuTitle = ADSTAT_L3;
	}


	// List of the non-page-based info which is gathered - historically only 'all-time' stats, now we support monthly as well
	$stats_list = array('statBrowser'=>ADSTAT_LAN_6,'statOs'=>ADSTAT_LAN_7,'statScreen'=>ADSTAT_LAN_8,'statDomain'=>ADSTAT_LAN_9,'statReferer'=>ADSTAT_LAN_10,'statQuery'=>ADSTAT_LAN_11);

	$separator_list = array(1 => ADSTAT_LAN_57, 2 => ADSTAT_LAN_58);
	$separator_char = array(1 => ',', 2 => '|');
	$quote_list = array(1 => ADSTAT_LAN_50, 2 => ADSTAT_LAN_55, 3 => ADSTAT_LAN_56);
	$quote_char = array(1 => '', 2 => "'", 3 => '"');


	//---------------------------------------------
	//		Export data file
	//---------------------------------------------
	$export_filter = '';		// can be 'LIKE', 'REGEX', or simple equality
	$export_type = $tp->toDB(varset($_POST['export_type'],'page'));				// Page data or one of the other bits of info
	$export_date = intval(varset($_POST['export_date'],1));
	$export2_date = intval(varset($_POST['export2_date'],3));
	$export_year = intval(varset($_POST['export_year'],date('Y')));
	$export_month = intval(varset($_POST['export_month'],date('m')));
	$export_day = intval(varset($_POST['export_day'],date('j')));
	$export_char = varset($_POST['export_char'], 1);
	$export_quote = varset($_POST['export_quote'], 1);
	$export_stripurl = varset($_POST['export_stripurl'], 0);








	class logstats_ui extends e_admin_ui
	{

		protected $pluginTitle		= ADSTAT_L3;
		protected $pluginName		= 'log';
		//	protected $eventName		= 'log-logstats'; // remove comment to enable event triggers in admin.
		protected $table			= 'logstats';
		protected $pid				= 'log_uniqueid';
		protected $perPage			= 10;
		protected $batchDelete		= true;
		//	protected $batchCopy		= true;
		//	protected $sortField		= 'somefield_order';
		//	protected $orderStep		= 10;
		//	protected $tabs				= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.
		protected $listQry              = "SELECT * FROM `#logstats` WHERE `log_id` = 'pageTotal' OR (`log_id` REGEXP '^[0-9]' AND LENGTH(log_id) > 7 AND LENGTH(log_id) < 11) ";

		//	protected $listQry      	= "SELECT * FROM `#tableName` WHERE field != '' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

		protected $listOrder		= 'CASE log_id WHEN "pageTotal" THEN 9999 ELSE DATE(log_id) END DESC  ';


		protected $fields 		= array (  'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		                                    'log_uniqueid' =>   array ( 'title' => LAN_ID, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		                                    'log_id' =>   array ( 'title' => LAN_DATE, 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		                                    'log_data' =>   array ( 'title' => ADSTAT_L21, 'type' => 'method', 'data' => 'str', 'width' => '20%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'right', 'thclass' => 'right',  ),
		                                    'log_data2' =>   array ( 'title' => ADSTAT_L22, 'type' => 'method', 'data' => false, 'width' => '20%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'right', 'thclass' => 'right',  ),

		                                    'options' =>   array ( 'title' => LAN_OPTIONS, 'type' => 'method', 'data' => null, 'width' => '15%', 'thclass' => 'right last', 'class' => 'right last', 'forced' => '1',  ),
		);

		protected $fieldpref = array();


		//	protected $preftabs        = array('General', 'Other' );
		protected $prefs = array(
		);


		public function init()
		{
			if(!empty($_POST['rebuild']))
			{
				require_once(e_PLUGIN.'log/consolidate.php');
				$lgc = new logConsolidate();
				$file = $_POST['rebuild']."_SiteStats.log";
				$lgc->processRawBackupLog($file, true);
			}

			if(!empty($_POST['rebuildTotal']))
			{
				require_once(e_PLUGIN.'log/consolidate.php');
				$lgc = new logConsolidate();

				if($lgc->collatePageTotalDB())
				{
					e107::getMessage()->addSuccess(LAN_UPDATED);
				}
				else
				{
					e107::getMessage()->addError(LAN_UPDATED_FAILED);
				}

			}

			// Set drop-down values (if any).
			if (isset($_POST['updatesettings']))
			{
				$this->prefsPageSubmit();
			}

			if(isset($_POST['remSelP']))
			{
				$this->rempagePageSubmit();	 // Do the deletions - then redisplay the list of pages
			}


			if(isset($_POST['wipeSubmit']))
			{
				$this->wipe(); 
			}
		}


		/**
		 * Wipe accumulated stats
		 */
		private function wipe()
		{
			$sql = e107::getDb();
			
			$logStr = '';
			foreach($_POST['wipe'] as $key => $wipe)
			{
				switch($key)
				{
					case "statWipePage":
						$sql->update("logstats", "log_data='' WHERE log_id='pageTotal' ");
						$sql->update("logstats", "log_data='' WHERE log_id='statTotal' ");
						$sql->update("logstats", "log_data='' WHERE log_id='statUnique' ");
						break;

					case "statWipeBrowser":
						$sql->update("logstats", "log_data='' WHERE log_id='statBrowser' ");
						break;

					case "statWipeOs":
						$sql->update("logstats", "log_data='' WHERE log_id='statOs' ");
						break;

					case "statWipeScreen":
						$sql->update("logstats", "log_data='' WHERE log_id='statScreen' ");
						break;

					case "statWipeDomain":
						$sql->update("logstats", "log_data='' WHERE log_id='statDomain' ");
						break;

					case "statWipeRefer":
						$sql->update("logstats", "log_data='' WHERE log_id='statReferer' ");
						break;

					case "statWipeQuery":
						$sql->update("logstats", "log_data='' WHERE log_id='statQuery' ");
						break;

				}

				$logStr .= '[!br!]'.$key;
			}
				
			e107::getLog()->add('STAT_01',ADSTAT_LAN_81.$logStr,'');

			e107::getMessage()->addSuccess(LAN_UPDATED);

		}

		private function get_for_delete($keep_year,$keep_month = 1, $filter='*')
		{

			$sql = e107::getDb();

			global $stats_list;
			$ret = array();
			// Its tedious, but the filter criteria are sufficiently tricky that its probably best to read all records and decide what can go
			if ($sql->select('logstats','log_id'))
			{
				while ($row = $sql->fetch())
				{
					$can_go = FALSE;
					$check = FALSE;
					$data_type = 'unknown';
					$date_info = $row['log_id'];
					if (($temp = strpos($date_info,':')) !== FALSE)
					{  // its monthly browser stats and similar
//	    echo "Checking {$date_info}, posn = {$temp} ";
						$data_type = substr($date_info,0,$temp);
						$date_info = substr($date_info,$temp+1);
						$check = TRUE;
//		echo "Date string: {$date_info}, data type: {$data_type}<br />";
						if (isset($stats_list[$data_type])) $data_type = $stats_list[$data_type];
					}
					list($poss_year,$poss_month,$poss_day) = explode('-',$date_info.'--',3);
					if (!$check)
					{
						if (is_numeric($poss_year))
						{
							$check = TRUE;
							if ($poss_day > 0) $data_type = 'daily'; else $data_type = 'monthly';
						}
					}
					if ($check)
					{
						if ($keep_year == $poss_year)
						{
							if (($poss_month > 0) && ($poss_month < $keep_month)) $can_go = TRUE;
						}
						elseif ($keep_year > $poss_year) $can_go = TRUE;
					}
					if ($can_go)
					{
						$ret[$row['log_id']] = $row['log_id']." - ".$data_type;
					}
				}
			}

			return $ret;

		}

		private function data_type_select($name,$value)
		{
			global $stats_list;

			$ret = "<select name='{$name}' class='tbox'  onchange=\"settypebox(this.value);\">\n
			<option value='page' ".($value == 'page' ? " selected='selected'" : "").">".ADSTAT_LAN_52."</option>\n";

			foreach ($stats_list as $k=>$v)
			{
				$ret .= "<option value='{$k}' ".($value == $k ? " selected='selected'" : "").">{$v}</option>\n";
			}
			$ret .= "</select>\n";
			return $ret;
		}



		private function gen_select($prompt,$name,$value)
		{
			$ret = "<div class='control-group clearfix' >
	        <span class='pull-left float-left'>".$prompt."</span><span class='pull-right'><select name='{$name}' class='tbox'>\n
			<option value='0' ".($value == 0 ? " selected='selected'" : "").">".ADSTAT_LAN_50."</option>\n
			<option value='1' ".($value == 1 ? " selected='selected'" : "").">".ADSTAT_LAN_49."</option>\n
			<option value='2' ".($value == 2 ? " selected='selected'" : "").">".ADSTAT_LAN_48."</option>\n
			</select></span></div>";
			return $ret;
		}


		function datasetsPage()
		{
			return $this->export('datasets');
		}

		function exportPage()
		{
			return $this->export('export');
		}

		private function export($action)
		{
			global $export_type, $export_date, $export2_date, $export_day, $export_month, $export_year, $separator_list,
			       $export_char, $quote_list, $export_quote, $export_filter;

			$frm = e107::getForm();
			$sql = e107::getDb();

			$text = "<div style='text-align:center'>";

			if ($action == 'export')
			{
				$text .= "<form method='post' action='".e_PLUGIN."log/stats_csv.php?export'>";
			}
			else
			{
				$text .= "<form method='post' action='".e_SELF."?".e_QUERY."'>";
			}

			$text .= "<table class='table adminform'>
			<colgroup>
			  <col style='width:50%' />
			  <col style='width:50%' />
			</colgroup>
			";

			if ($action == 'export')
			{
				$text .= "<tr><td colspan = '2'>".ADSTAT_LAN_67."</td></tr>";
			}
			else
			{
				$text .= "<tr><td colspan = '2'>".ADSTAT_LAN_68."</td></tr>";
			}

			// Type of output data - page data, browser stats....
			$text .= "<tr><td>".ADSTAT_LAN_51."</td><td>\n".$this->data_type_select('export_type',$export_type).'</td></tr>';
				// Period selection type for page data
			$text .= "<tr><td>".ADSTAT_LAN_41."</td><td>\n
			<select class='tbox' name='export_date' id='export_date' onchange=\"setdatebox(this.value);\" ".($export_type=='page' ? "" : "style='display:none'" ).">\n
			<option value='1' ".($export_date==1 ? " selected='selected'" : '') . ">".ADSTAT_LAN_42."</option>\n
			<option value='2' ".($export_date==2 ? " selected='selected'" : '') . ">".ADSTAT_LAN_43."</option>\n
			<option value='3' ".($export_date==3 ? " selected='selected'" : '') . ">".ADSTAT_LAN_44."</option>\n
			<option value='4' ".($export_date==4 ? " selected='selected'" : '') . ">".ADSTAT_LAN_45."</option>\n
			<option value='5' ".($export_date==5 ? " selected='selected'" : '') . ">".ADSTAT_LAN_62."</option>\n
			</select>";

			// Period selection type for non-page data
			$text .= "
				<select class='tbox' name='export2_date' id='export2_date' onchange=\"setdatebox(this.value);\"  ".($export_type=='page' ? "style='display:none'" : "").">\n
				<option value='3'".($export2_date==3 ? " selected='selected'" : "").">".ADSTAT_LAN_44."</option>\n
				<option value='4'".($export2_date==4 ? " selected='selected'" : "").">".ADSTAT_LAN_45."</option>\n
				</select>";

			$text .= "</td></tr>";



			$text .= "<tr><td>".ADSTAT_LAN_46."</td><td>\n";


					// Now put the various dropdowns - their visibility is controlled by the export_type dropdown

					$text .= "<select class='tbox' name='export_day' id='export_day'>\n";
					for ($i = 1; $i < 32; $i++)
					{
						$selected = $export_day == $i ? " selected='selected'" : "";
						$text .= "<option value='{$i}' {$selected} >{$i}</option>\n";
					};
					$text .= "</select>\n&nbsp;&nbsp;&nbsp;";


					$text .= "<select class='tbox' name='export_month' id='export_month'>\n";
					for ($i = 1; $i < 13; $i++)
					{
						$selected = $export_month == $i ? " selected='selected'" : "";
						$text .= "<option value='{$i}' {$selected}>".nl_langinfo(constant('MON_'.$i))."</option>\n";
					};
					$text .= "</select>\n&nbsp;&nbsp;&nbsp;";

					$this_year = date("Y");
					$text .= "<select class='tbox' name='export_year' id='export_year'>\n";
					for ($i = $this_year; $i > $this_year - 6; $i--)
					{
						$selected = $export_year == $i ? " selected='selected'" : "";
						$text .= "<option value='{$i}' {$selected}>{$i}</option>\n";
					};
					$text .= "</select>\n&nbsp;&nbsp;&nbsp;";

					$text .= "<span id='export_cumulative' style='display: none'>".ADSTAT_LAN_53."</span>\n";

					$text .= "</td></tr>";


					if ($action == 'export')
					{
						// Separators, quotes
						$text .= "<tr><td>".ADSTAT_LAN_59."</td><td>\n
					<select class='tbox' name='export_char'>";

						foreach ($separator_list as $k=>$v)
						{
							$selected = $export_char == $k ? " selected='selected'" : "";
							$text .= "<option value='{$k}' {$selected}>{$v}</option>\n";
						}
						$text .= "</select>\n&nbsp;&nbsp;&nbsp;&nbsp;<select class='tbox' name='export_quote'>\n";
						foreach ($quote_list as $k=>$v)
						{
							$selected = $export_quote == $k ? " selected='selected'" : "";
							$text .= "<option value='{$k}'".$selected.">{$v}</option>\n";
						}
						$text .= "</select>\n</td></tr>";

						$text .= "<tr>
			<td>".ADSTAT_LAN_60."</td>
			<td>".$frm->checkbox('export_stripurl', 1)."<span class='field-help'>".ADSTAT_LAN_61."</span></td>
			</tr>";
					}


					if ($export_filter)
					{
						if (getperms('0')) $text .= "<tr><td>".ADSTAT_LAN_65."</td><td>".$export_filter."</td></tr>";
						$sql ->select("logstats", "log_id", "{$export_filter} ");
						$text .= "<tr><td>".ADSTAT_LAN_64."</td><td>";
						while($row = $sql ->fetch())
						{
							$text .= $row['log_id']."<br />";
						}
						$text .= "</td></tr>";
					}

					$text .= "
			</table>
				<div class='buttons-bar center'>
			".$frm->admin_button('create_export', ($action == 'export' ? LAN_CREATE : ADSTAT_LAN_66), 'update')."
			</div>
			</form>
			</div>";

			// Set up the date display boxes
			$text .= "<script type=\"text/javascript\"> settypebox('{$export_type}');</script>";

			return $text;
			//$ns->tablerender(ADSTAT_LAN_40, $text);
		}



		private function rempagePageSubmit()
		{

			$ipAddresses = null;
			$siteTotal = null;
			$siteUnique = null;

			$sql = e107::getDb();

			$pageInfo = array();

			$sql->select("logstats", "*", "log_id='pageTotal' ");
			$row = $sql ->fetch();
			$pageTotal = unserialize($row['log_data']);
			$logfile = e_LOG."logp_".date("z.Y", time()).".php";

			if(is_readable($logfile))
			{
				require($logfile);
			}

			foreach($_POST['remcb'] as $page)
			{
				unset($pageInfo[$page]);
				unset($pageTotal[$page]);
			}

			$pagetotal = serialize($pageTotal);

			if(!$sql->update("logstats", "log_data='{$pagetotal}' WHERE log_id='pageTotal' "))
			{
				$sql->insert("logstats", "0, 'pageTotal', '{$pagetotal}' ");
			}

			e107::getLog()->add('STAT_03',ADSTAT_LAN_80."[!br!]".implode("[!br!]",$_POST['remcb']),'');

			$varStart = chr(36);
			$quote = chr(34);

			$data = chr(60)."?php\n". chr(47)."* e107 website system: Log file: ".date("z:Y", time())." *". chr(47)."\n\n".
				$varStart."ipAddresses = ".$quote.$ipAddresses.$quote.";\n".
				$varStart."siteTotal = ".$quote.$siteTotal.$quote.";\n".
				$varStart."siteUnique = ".$quote.$siteUnique.$quote.";\n";

			$loop = FALSE;
			$data .= $varStart."pageInfo = array(\n";
			foreach($pageInfo as $info)
			{
				$page = preg_replace("/(\?.*)|(\_.*)|(\.php)|(\s)|(\')|(\")|(eself)|(&nbsp;)/", "", basename ($info['url']));
				$page = str_replace("\\", "", $page);
				$info['url'] = preg_replace("/(\s)|(\')|(\")|(eself)|(&nbsp;)/", "", $info['url']);
				$info['url'] = str_replace("\\", "", $info['url']);
				$page = trim($page);
				if($page && !strstr($page, "cache") && !strstr($page, "file:"))
				{
					if($loop){ $data .= ",\n"; }
					$data .= $quote.$page.$quote." => array('url' => '".$info['url']."', 'ttl' => ".$info['ttl'].", 'unq' => ".$info['unq'].")";
					$loop = 1;
				}
			}

			$data .= "\n);\n\n?".  chr(62);

			if ($handle = fopen($logfile, 'w'))
			{
				fwrite($handle, $data);
			}
			fclose($handle);
		}



		function rempagePage()
		{
			$sql = e107::getDb();
			$frm = e107::getForm();
			$pageInfo = array();
			$tp = e107::getParser();

			$logfile = e_LOG."logp_".date("z.Y", time()).".php";
		//	$logfile = e_PLUGIN."log/logs/logp_".date("z.Y", time()).".php";
			if(is_readable($logfile))
			{
				require($logfile);
			}

			$sql ->select("logstats", "*", "log_id='pageTotal' ");
			$row = $sql ->fetch();
			$pageTotal = unserialize($row['log_data']);



			foreach($pageInfo as $url => $tmpcon) 
			{
				$pageTotal[$url]['url'] = $tmpcon['url'];
				$pageTotal[$url]['ttlv'] += $tmpcon['ttl'];
				$pageTotal[$url]['unqv'] += $tmpcon['unq'];
			}



			$text = "
			<form method='post' action='".e_REQUEST_URL."'>
			<table class='table adminlist'>

			<tr>
			<th style='width:100px; text-align: center;'>".ADSTAT_LAN_30." ...</th>
			<th style='width:100px' class='text-right'>".ADSTAT_LAN_86."</th>
			<th style='width:30%' class='forumheader'>".ADSTAT_LAN_29."</th>
			<th style='width:auto' class='forumheader'>URL</th>
			</tr>
			";

			foreach($pageTotal as $key => $page)
			{

				list($name,$lang) = explode("|",$key);

						$text .= "
				<tr>
				<td style='width:100px; text-align: center;'><input type='checkbox' name='remcb[]' value='{$key}' /></td>
				<td class='text-right' style='width:100px'>".number_format($page['ttlv'])."</td>
				<td style='width:30%'>{$name}</td>
				<td style='width:auto'>".$tp->text_truncate($page['url'],100)."</td>

				</tr>
				";
			}

			$text .= "
			</table>
			<div class='buttons-bar center'>
			".$frm->admin_button('remSelP', ADSTAT_LAN_31, 'delete')."
			</form>

			";

			return $text;

		//	$ns -> tablerender(ADSTAT_LAN_32, $text);
		}


		function rebuildPage()
		{
			$frm  = e107::getForm();
			$mes = e107::getMessage();
			$tp = e107::getParser();

			$mes->addWarning(ADSTAT_LAN_84);
			$text = $frm->open('rebuild');

			$files = e107::getFile()->get_files(e_LOG."log",'_SiteStats\.log$');

		//	print_a($_SESSION['stats_log_files']);

			$_SESSION['stats_log_files'] = array();
			$_SESSION['stats_log_files_count'] = 0;

			foreach($files as $f)
			{
				$_SESSION['stats_log_files'][] = array('path'=> $f['fname'], 'complete'=>0);
			}

			$_SESSION['stats_log_files_total'] = count($_SESSION['stats_log_files']);

		//	$text .=  //  . " log files have been found. Click the button below to process these files.</p>";
			$mes->addWarning($tp->lanVars(ADSTAT_LAN_85, $_SESSION['stats_log_files_total'], true));

			if(!empty($_SESSION['stats_log_files_total']))
			{
				$text .= $frm->progressBar('rebuild-progress',0,array("btn-label"=> ADSTAT_LAN_88, 'url'=>e_REQUEST_URI));
			}

			$text .= $frm->close();
			return $text;


		}



		function historyPage()
		{
			$mes = e107::getMessage();
			$frm = e107::getForm();
			$sql = e107::getDb();

			$mes->addWarning(ADSTAT_LAN_76);
			$text = "

			<form method='post' action='".e_SELF."?history'>
			<table class='table adminlist'>
			<colgroup>
			  <col style='width:50%' />
			  <col style='width:50%' />
			</colgroup>";
			
			$keep_month = varset($_POST['delete_month'],0);
			$keep_year = varset($_POST['delete_year'],0);
			
			if (isset($_POST['delete_history']))
			{
						$text .= "<tr><td>".ADSTAT_LAN_72."</td><td>".nl_langinfo(constant('MON_'.$keep_month))." ".$keep_year."</td></tr>
				<tr><td colspan='2'  style='text-align:center' class='forumheader'>
				<input type='hidden' name='delete_month' value='{$keep_month}' />
				<input type='hidden' name='delete_year' value='{$keep_year}' />
				".$frm->admin_button('actually_delete', LAN_CONFDELETE, 'delete')."<br />".ADSTAT_LAN_74."
				</td></tr>";
						$text .= "<tr><td>".ADSTAT_LAN_75."</td><td>".implode("<br />",$this->get_for_delete($keep_year,$keep_month))."</td></tr>";
			}
			else
			{
				if (isset($_POST['actually_delete']))
				{
							$delete_list = $this->get_for_delete($keep_year,$keep_month);
							$logStr = '';
							//	    $text .= "<tr><td colspan='2'>Data notionally deleted {$keep_month}-{$keep_year}</td></tr>";
							$text .= "<tr><td>".ADSTAT_LAN_77."</td><td>";
							
							foreach ($delete_list as $k => $v)
							{
								$sql->delete('logstats',"log_id='{$k}'");
								$text .= $v."<br />";
								$logStr .= "[!br!]{$k} => ".$v;
							}
							
							$text .= "</td></tr>";
							e107::getLog()->add('STAT_04',ADSTAT_LAN_83.$logStr,'');
				}
						
				$text .= "<tr><td>".ADSTAT_LAN_70."</td>";
				$text .= "<td><select class='tbox' name='delete_month'>\n";
				$match_month = date("n");
				
				for ($i = 1; $i < 13; $i++)
				{
					$selected = $match_month == $i ? " selected='selected'" : "";
					$text .= "<option value='{$i}'".$selected.">".nl_langinfo(constant('MON_'.$i))."</option>\n";
				}
				
				$text .= "</select>\n&nbsp;&nbsp;&nbsp;";
				$this_year = date("Y");
						
				$text .= "<select class='tbox' name='delete_year' id='export_year'>\n";
				
				for ($i = $this_year; $i > $this_year - 6; $i--)
				{
					$selected = ($this_year - 2) == $i ? " selected='selected'" : "";
					$text .= "<option value='{$i}'{$selected}>{$i}</option>\n";
				}
				
				$text .= "</select>\n</td></tr>";
			}

			$text .= "</table>

			<div class='buttons-bar center'>
			".$frm->admin_button('delete_history',LAN_DELETE,'delete')."
			</div>

			</form>";

			return $text;
		//	$ns->tablerender(ADSTAT_LAN_69, $mes->render().$text);
		}



		private function prefsPageSubmit()
		{

				$statList = array(		// Type = 0 for direct text, 1 for integer
					'statActivate' 		=> 0,
					'statCountAdmin' 	=> 0,
					'statUserclass' 	=> 0,
					'statBrowser'		=> 1,
					'statOs'			=> 1,
					'statScreen' 		=> 1,
					'statDomain' 		=> 1,
					'statRefer' 		=> 1,
					'statQuery' 		=> 1,
					'statRecent' 		=> 1,
					'statDisplayNumber' => 0,
					'statPrevMonth'		=> 1
				);
				$logStr = '';

				$pref = array();

				foreach ($statList as $k => $type)
				{
					switch ($type)
					{
						case 0 : $pref[$k] = $_POST[$k]; break;
						case 1 : $pref[$k] = intval($_POST[$k]); break;
					}
					$logStr .= "[!br!]{$k} => ".$pref[$k];
				}

				e107::getConfig()->setPref($pref)->save(false,true,true);

				file_put_contents(e_LOG.LogFlagFile, "<?php\n\$logEnable={$pref['statActivate']};\n?>\n");		// Logging task uses to see if logging enabled
				e107::getLog()->add('STAT_02',ADSTAT_LAN_82.$logStr,'');


		}


		public function prefsPage()
		{

			global $pref;

			$frm = e107::getForm();

			$text = "
			<form method='post' action='".e_SELF."'>
			<table class='table adminform'>
			<colgroup>
				<col style='width:40%' />
				<col style='width:60%' />
			</colgroup>

			<tr>
				<td>".ADSTAT_LAN_4."</td>
				<td>".$frm->radio_switch('statActivate', $pref['statActivate'])."</td>
			</tr>
			<tr>
				<td>".ADSTAT_LAN_18."</td>
				<td>".r_userclass("statUserclass", $pref['statUserclass'],'off','public, member, admin, classes')."</td>
			</tr>
			<tr>
				<td>".ADSTAT_LAN_20."</td>
				<td>".$frm->radio_switch('statCountAdmin', $pref['statCountAdmin'])."</td>
			</tr>
			<tr>
				<td>".ADSTAT_LAN_21."</td>
				<td><input class='tbox' type='text' name='statDisplayNumber' size='8' value='".$pref['statDisplayNumber']."' maxlength='3' /></td>
			</tr>
			<tr>
				<td>".ADSTAT_LAN_5."</td>
				<td>
				".$this->gen_select(ADSTAT_LAN_6, 'statBrowser',$pref['statBrowser'])
				.$this->gen_select(ADSTAT_LAN_7, 'statOs',$pref['statOs'])
				.$this->gen_select(ADSTAT_LAN_8, 'statScreen',$pref['statScreen'])
				.$this->gen_select(ADSTAT_LAN_9, 'statDomain',$pref['statDomain'])
				.$this->gen_select(ADSTAT_LAN_10, 'statRefer',$pref['statRefer'])
				.$this->gen_select(ADSTAT_LAN_11, 'statQuery',$pref['statQuery'])
				."<div class='clearfix' style='padding-bottom: 4px'><span class='pull-left float-left'>".ADSTAT_LAN_19."</span><span class='pull-right float-right'>
				 ".$frm->radio_switch('statRecent', $pref['statRecent'])."</span></div>
				</td>
			</tr>

			<tr>
			<td>".ADSTAT_LAN_78."</td>
				<td>".$frm->checkbox('statPrevMonth', 1, varset($pref['statPrevMonth'],0))."<span class='field-help'>".ADSTAT_LAN_79."</span></td>
			</tr>
			<tr>
				<td>".ADSTAT_LAN_12."</td>
				<td>
					".$frm->checkbox('wipe[statWipePage]', 1, false, array('label'=> ADSTAT_LAN_14 ))."
					".$frm->checkbox('wipe[statWipeBrowser]', 1, false, array('label'=>ADSTAT_LAN_6))."
					".$frm->checkbox('wipe[statWipeOs]', 1, false, array('label'=> ADSTAT_LAN_7 ))."
					".$frm->checkbox('wipe[statWipeScreen]', 1, false, array('label'=> ADSTAT_LAN_8 ))."
					".$frm->checkbox('wipe[statWipeDomain]', 1, false, array('label'=> ADSTAT_LAN_9 ))."
					".$frm->checkbox('wipe[statWipeRefer]', 1, false, array('label'=> ADSTAT_LAN_10 ))."
					".$frm->checkbox('wipe[statWipeQuery]', 1, false, array('label'=> ADSTAT_LAN_11 ))."
					<br />
					".$frm->admin_button('wipeSubmit', LAN_RESET, 'delete')."<span class='field-help'>".ADSTAT_LAN_13."</span>
				</td>
			</tr>
			<tr>
				<td>".ADSTAT_LAN_26."</td>
				<td>".$frm->admin_button('openRemPageD', ADSTAT_LAN_28, 'other')."<span class='field-help'>".ADSTAT_LAN_27."</span>	</td>
			</tr>
			</table>
			<div class='buttons-bar center'>
				".$frm->admin_button('updatesettings', LAN_UPDATE, 'update')."
			</div>
			</form>";

			return $text;
		//	$ns->tablerender(ADSTAT_LAN_16, $text);
		}




		/*
			// optional - a custom page.
			public function customPage()
			{
				$text = 'Hello World!';
				return $text;

			}
		*/

	}



	class logstats_form_ui extends e_admin_form_ui
	{

		function log_id($curVal,$mode)
		{
			switch($mode)
			{
				case 'read': // List Page

					if($curVal == 'pageTotal')
					{
						return ADSTAT_LAN_45; // All Time.
					}

					return $curVal;
					break;

				case 'write': // Edit Page
					return '';
					break;

				case 'filter':
				case 'batch':
					return  array();
					break;
			}

		}


		function parseLogData($type)
		{
			$row = $this->getController()->getListModel()->getData();
			$curVal = $row['log_data'];

			if($row['log_id'] == 'pageTotal')
			{
				$tmp = unserialize($curVal);

				$ttl = 0;
				$unq = 0;

				foreach($tmp as $k=>$v)
				{
					$ttl += $v['ttlv'];
					$unq += $v['unqv'];
				}

				if($type == 'total')
				{
					return number_format($ttl);
				}
				elseif($type == 'unique')
				{
					return  number_format($unq);
				}

				return "<div class='col-md-2'>".ADSTAT_L21.": ".number_format($ttl)."</div><div class='col-md-2'>".ADSTAT_L22.": ".number_format($unq)."</div><div class='col-md-2'>Total Pages: ".number_format(count($tmp))."</div>";


				//	return print_a($tmp,true);
			}

			if(!empty($curVal))
			{
				$tmp = explode(chr(1), $curVal, 3);

				if($type == 'total')
				{
					return number_format($tmp[0]);
				}
				elseif($type == 'unique')
				{
					return  number_format($tmp[1]);
				}

				// return "<div class='col-md-1'>".ADSTAT_L21.": ".number_format($tmp[0])."</div><div class='col-md-2'>".ADSTAT_L22.": ".number_format($tmp[1])."</div>";
			}

		}

		// Total Hits
		function log_data($curVal,$mode)
		{

			switch($mode)
			{
				case 'read': // List Page

					return $this->parseLogData('total');
					break;

				case 'write': // Edit Page
					// return $this->text('log_data',$curVal, 255, 'size=large');
					break;

				case 'filter':
				case 'batch':
					return  array();
					break;
			}

			return null;
		}


		// unique hits
		function log_data2($curVal,$mode)
		{

			switch($mode)
			{
				case 'read': // List Page

					return $this->parseLogData('unique');
					break;

				case 'write': // Edit Page
					// return $this->text('log_data',$curVal, 255, 'size=large');
					break;

				case 'filter':
				case 'batch':
					return  array();
					break;
			}

			return null;
		}



		function options($curVal,$mode)
		{
			$row = $this->getController()->getListModel()->getData();
			$date = $row['log_id'];

			if($date == 'pageTotal')
			{
				return $this->button('rebuildTotal', 1, 'delete', ADSTAT_LAN_89);
			}


			$unix = strtotime($date);

			if(empty($unix))
			{
				return null;
			}

			$datestamp = date("Y-m-d", $unix);

			$file = e_LOG."log/".$datestamp."_SiteStats.log";

			if(is_readable($file))
			{
				return $this->button('rebuild', $datestamp, 'delete', ADSTAT_LAN_89);
			}
			else
			{
				return null;
			}

		}

	}


	new log_adminArea();

	require_once(e_ADMIN."auth.php");
	e107::getAdminUI()->runPage();

	require_once(e_ADMIN."footer.php");
	exit;

/*

require_once(e_ADMIN.'auth.php');

require_once(e_HANDLER.'userclass_class.php');
$frm = e107::getForm();
$mes = e107::getMessage();

*/


if (e_QUERY) 
{
	$sl_qs = explode('.', e_QUERY);
}
$action = varset($sl_qs[0],'config');
$params = varset($sl_qs[1],'');




if (isset($_POST['create_export']) && (($action == 'export') || ($action == 'datasets')))
{
	$first_date = 0;
	$last_date = 0;
	$date_error = FALSE;
	if ($export_type == 'page')
	{
		switch ($export_date)
		{
			case '1' :		//	Single day
				$first_date = gmmktime(0,0,0,$export_month,$export_day,$export_year);
				$last_date = $first_date+86399;
				$export_filter = " `log_id`='".date("Y-m-j",$first_date)."'";
				break;
			case '2' :		// Daily for a month
				$first_date = gmmktime(0,0,0,$export_month,1,$export_year);
				$last_date = gmmktime(0,0,0,$export_month+1,1,$export_year) - 1;
				$export_filter = " LEFT(`log_id`,8)='".gmstrftime("%Y-%m-",$first_date)."'";
				break;
			case '3' :		// Monthly for a Year
				$first_date = gmmktime(0,0,0,1,1,$export_year);
				$last_date = gmmktime(0,0,0,1,1,$export_year+1) - 1;
				$export_filter = " LENGTH(`log_id`)=7 AND LEFT(`log_id`,5)='".gmstrftime("%Y-",$first_date)."'";
				break;
			case '4' :		// Accumulated
			case '5' :
				$export_filter = "`log_id`='pageTotal'";
				$date_error = 'ignore';
				break;
		}
	}
	else
	{  // Calculate strings for non-page sources
		$prefix_len = 0;
		$export_date = $export2_date;
		if (isset($stats_list[$export_type]))
		{
			$prefix_len = strlen($export_type) + 1;
			switch ($export2_date)
			{
				case '3' :		// Monthly for a Year
					if ($prefix_len > 0)
					{
						$first_date = gmmktime(0,0,0,1,1,$export_year);
						$last_date = gmmktime(0,0,0,1,1,$export_year+1) - 1;
						$export_filter = " LENGTH(`log_id`)='".($prefix_len + 7)."' AND LEFT(`log_id`,".($prefix_len + 5).")='".$export_type.":".gmstrftime("%Y-",$first_date)."'";
					}
					break;
				case '4' :		// Accumulated
					$export_filter = " `log_id`='".$export_type."'";
					$date_error = 'ignore';
					break;
			}
		}
		else
		{
			$message = ADSTAT_LAN_54;
		}
	}
	if (($date_error != 'ignore') && (($first_date == 0) || ($last_date == 0) || $date_error))
	{
		$message = ADSTAT_LAN_47;
	}
}



//---------------------------------------------
//		Remove page entries
//---------------------------------------------
if(isset($_POST['openRemPageD']))
{
  $action = 'rempage';
}










echo  $mes->render() ;






switch ($action)
{
  case 'config' :

	break;  // case config
	
  case 'rempage' :			// Remove pages
//	rempage();
    break;
	
	
  case 'export' :			// Export file
  case 'datasets' :
	//===========================================================
	//				EXPORT DATA
	//===========================================================

    break;	// case 'export'
	
  case 'history' :
	//===========================================================
	//				DELETE HISTORY
	//===========================================================

    break;	// case 'history'

}


require_once(e_ADMIN."footer.php");


function headerjs()
{
  $script_js = "<script type=\"text/javascript\">
	//<![CDATA[
	  var names = new Array();
	    names[0] = 'export_day';
	    names[1] = 'export_month';
	    names[2] = 'export_year';
	    names[3] = 'export_cumulative';

	  var dispinfo = new Array();
		dispinfo[1] = new Array();		// Single day
		  dispinfo[1][0] = '';
		  dispinfo[1][1] = '';
		  dispinfo[1][2] = '';
		  dispinfo[1][3] = 'none';
		  
		dispinfo[2] = new Array();		// Month
		  dispinfo[2][0] = 'none';
		  dispinfo[2][1] = '';
		  dispinfo[2][2] = '';
		  dispinfo[2][3] = 'none';
		  
		dispinfo[3] = new Array();		// Year
		  dispinfo[3][0] = 'none';
		  dispinfo[3][1] = 'none';
		  dispinfo[3][2] = '';
		  dispinfo[3][3] = 'none';
		  
		dispinfo[4] = new Array();		// Specials
		  dispinfo[4][0] = 'none';
		  dispinfo[4][1] = 'none';
		  dispinfo[4][2] = 'none';
		  dispinfo[4][3] = '';


	function setdatebox(disptype)
	{
	  var target;
	  var j;

	  if (disptype > 4) disptype = 4;

	  for (j = 0; j < names.length; j++)
	  {
	    target = document.getElementById(names[j]).style;
		target.display = dispinfo[disptype][j];
	  }
	}
	
	function settypebox(pagetype)
	{
	  var newdateformat = 1;
	  var target1 = document.getElementById('export_date');
	  var target2 = document.getElementById('export2_date');
	  if (pagetype == 'page')
	  {
	    target1.style.display = '';
	    target2.style.display = 'none';
		newdateformat = target1.value;
	  }
	  else
	  {
	    target1.style.display = 'none';
	    target2.style.display = '';
		newdateformat = target2.value;
	  }
	  setdatebox(newdateformat);
	}

	//]]>
	</script>\n";
  return $script_js;
}



//---------------------------------------------
//		Remove page entries - prompt/list
//---------------------------------------------
function rempage()
{

}


//---------------------------------------------
//		Remove page entries - action
//---------------------------------------------




function admin_config_adminmenu()
{
  if (e_QUERY) 
  {
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
  }
  if (!isset($action) || ($action == "")) $action = "config";

  $var['config']['text'] = ADSTAT_LAN_35;
  $var['config']['link'] = 'admin_config.php';

  $var['export']['text'] = ADSTAT_LAN_36;
  $var['export']['link'] ='admin_config.php?export';

//  $var['datasets']['text'] = ADSTAT_LAN_63;
//  $var['datasets']['link'] ='admin_config.php?datasets';

  $var['rempage']['text'] = ADSTAT_LAN_26;
  $var['rempage']['link'] ='admin_config.php?rempage';

  $var['history']['text'] = ADSTAT_LAN_69;
  $var['history']['link'] ='admin_config.php?history';

  show_admin_menu(ADSTAT_LAN_39, $action, $var);
}


?>
