<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Area - Languages

 */
require_once ("../class2.php");
if (!getperms('L'))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('language', true);

$e_sub_cat = 'language';

if(!empty($_GET['iframe']))
{
	define('e_IFRAME', true);
}

	class language_admin extends e_admin_dispatcher
	{

		protected $modes = array(

			'main'	=> array(
				'controller' 	=> 'language_ui',
				'path' 			=> null,
				'ui' 			=> 'language_form_ui',
				'uipath' 		=> null
			),
			'db'	=> array(
				'controller' 	=> 'language_ui',
				'path' 			=> null,
				'ui' 			=> 'language_form_ui',
				'uipath' 		=> null
			),

		);


		protected $adminMenu = array(

			'main/prefs'	=> array('caption'=> LAN_PREFS, 'perm' => '0'),
			'main/tools'    => array('caption'=>LANG_LAN_21, 'perm'=>'L')

		);

		protected $adminMenuAliases = array(
			'main/edit'	=> 'main/list',
	//		'main/download'	=> 'main/tools'
		);

		protected $adminMenuIcon = 'e-language-24';

		protected $menuTitle = ADLAN_132;

		function init()
		{
			$pref = e107::getPref();

			if (!empty($pref['multilanguage']))
			{
				$this->adminMenu = array(
					'main/prefs'    => $this->adminMenu['main/prefs'],
					'main/db'       => 	array('caption'=> LANG_LAN_03, 'perm' => 'P'),
					'main/tools'    => $this->adminMenu['main/tools'],
				);
			}

			if(e_DEVELOPER == true)
			{
				$this->adminMenu['main/deprecated'] = array('caption'=>LANG_LAN_04, 'perm'=>'0');
			}

		}
	}





	class language_ui extends e_admin_ui
	{

		protected $pluginTitle		= ADLAN_132;
		protected $pluginName		= 'core';
		protected $eventName		= 'language';
	//	protected $table			= 'language';
	//	protected $pid				= 'gen_id';
	//	protected $perPage			= 10;
	//	protected $batchDelete		= true;
	//	protected $batchCopy		= true;

		//	protected $sortField		= 'somefield_order';
		//	protected $orderStep		= 10;
		//	protected $tabs			= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.

	//	protected $listQry      	= "SELECT * FROM `#language` WHERE gen_type='language'  "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

	//	protected $listOrder		= 'gen_id DESC';

	//	protected $fields 		= array ();

	//	protected $fieldpref = array('gen_ip', 'gen_intdata');


		protected $prefs = array(
			'sitelanguage'		        => array('title'=> LANG_LAN_14, 'type'=>'dropdown', 'data' => 'str','help'=>'', 'writeParms'=>array('useValues'=>1)),
			'adminlanguage'		        => array('title'=> LANG_LAN_50, 'type'=>'dropdown', 'data' => 'str','help'=>'', 'writeParms'=>array('useValues'=>1,"default" => LANG_LAN_14)),
			'multilanguage'		        => array('title'=> LANG_LAN_12, 'type'=>'boolean', 'data' => 'int','help'=>''),
			'noLanguageSubs'            => array('title'=> LANG_LAN_26, 'type'=>'boolean', 'data'=>'int', 'help'=> LANG_LAN_27),
			'multilanguage_subdomain'   => array('title'=> LANG_LAN_18, 'type'=>'textarea', 'data'=>'str', 'help'=> LANG_LAN_19, 'writeParms'=>array('rows'=>3, 'placeholder'=>'mydomain.com')),
			'multilanguage_domain'      => array('title'=> LANG_LAN_106, 'type'=>'method', 'data'=>'str', 'help'=> LANG_LAN_107),
			'multilanguage_verify_errorsonly'    => array('title'=> LANG_LAN_33, 'type'=>'boolean', 'data' => 'int','help'=>''),

		);

		protected $installedLanguages = array();
		protected $localPacks = array();
		protected $onlinePacks = array();

		public function init()
		{
			$this->installedLanguages = e107::getLanguage()->installed();
			$this->prefs['sitelanguage']['writeParms']['optArray'] = $this->installedLanguages;
			$this->prefs['adminlanguage']['writeParms']['optArray'] = $this->installedLanguages;

			e107::css('inline', "

				.language-name { padding-left:15px }

			");


		}

		private function loadPackInfo()
		{
			/** @var lancheck $lck */
			$lck = e107::getSingleton('lancheck', e_ADMIN."lancheck.php");

			$this->onlinePacks  = $lck->getOnlineLanguagePacks();
			$this->localPacks   = $lck->getLocalLanguagePacks();

		}


		function deprecatedPage()
		{

			if(e_DEVELOPER !== true)
			{
				return false;
			}

			$lnd = new lanDeveloper;

			$text = lanDeveloper::form();


			if($result = $lnd->run())
			{
				$text .= $result['text'];
			}

			return $text;
		}



		function ToolsPage()
		{
			$this->loadPackInfo();
			$pref = e107::getPref();
			$lck = e107::getSingleton('lancheck', e_ADMIN."lancheck.php");

			$lck->errorsOnly($pref['multilanguage_verify_errorsonly']);
			// show_packs();

			if($return = $lck->init())
			{
				if($return['caption'])
				{
					$this->addTitle($return['caption']);
				}

				return $return['text'];
			}


			return $this->renderLanguagePacks();


		}




		function DownloadPage()
		{
			$this->loadPackInfo();

			$lan = $this->getId();

			if(empty($lan))
			{
				return LAN_ERROR;
			}

			if(empty($this->onlinePacks[$lan]['url']))
			{
				return LAN_ERROR;
			}


			$result = e107::getFile()->unzipGithubArchive($this->onlinePacks[$lan]['url']);

			if(!empty($result['success']))
			{
				e107::getMessage()->addSuccess(print_a($result['success'],true));
				$_SESSION['lancheck'][$lan]['total'] = 0; // reset errors to zero.
			}

			if(!empty($result['error']))
			{
				e107::getMessage()->addError(print_a($result['error'],true));
			}

			$this->addTitle(LANG_LAN_114);
			$this->addTitle($lan);


			return e107::getMessage()->render();
		}





		/**
		 * List the installed language packs.
		 * @return string
		 */
		private function renderLanguagePacks()
		{
			$frm = e107::getForm();
			$ns = e107::getRender();
			$tp = e107::getParser();

		//	if(is_readable(e_ADMIN."ver.php"))
			{
			//	include(e_ADMIN."ver.php");
				list($ver, $tmp) = explode(" ", e_VERSION);
			}

			$lck = e107::getSingleton('lancheck', e_ADMIN."lancheck.php");

			$release_diz = defset("LANG_LAN_30","Release Date");
			$compat_diz = defset("LANG_LAN_31", "Compatibility");
			$lan_pleasewait = (deftrue('LAN_PLEASEWAIT')) ?  $tp->toJS(LAN_PLEASEWAIT) : "Please Wait";


			$text = "<form id='lancheck' method='post' action='".e_REQUEST_URI."'>
				<table class='table adminlist table-striped'>
				<colgroup>
					<col style='width:20%' />
					<col style='width:20%' />
					<col style='width:auto' />
					<col style='width:10%' />
					<col style='width:25%' />
				</colgroup>";
			$text .= "<thead>
			<tr>
			<th>".ADLAN_132."</th>
			<th class='text-left'>".$release_diz."</th>
			<th class='text-left'>".$compat_diz."</th>
			<th class='text-center'>".LAN_STATUS."</td>
			<th class='text-right' style='white-space:nowrap'>".LAN_OPTIONS."</td>
			</tr>
			</thead>
			";

			$text .= "<tr><th colspan='5'>".LAN_INSTALLED."</th></tr>";

		//	$onlinePacks = $lck->getOnlineLanguagePacks();
		//	$localPacks = $lck->getLocalLanguagePacks();

			foreach($this->localPacks as $language=>$value)
			{

				$errFound = (isset($_SESSION['lancheck'][$language]['total']) && $_SESSION['lancheck'][$language]['total'] > 0) ?  TRUE : FALSE;


				$text .= "<tr>
				<td><span class='language-name'>".$language."</a></td>
				<td class='text-left'>".$value['date']."</td>
				<td class='text-left'>".$value['compatibility']."</td>
				<td class='text-center'>".( $errFound ? ADMIN_FALSE_ICON : ADMIN_TRUE_ICON )."</td>
				<td class='text-right'>";

			//	$text .= "<input type='submit' name='language_sel[{$language}]' value=\"".LAN_CHECK_2."\" class='btn btn-primary' />";
				$text .= "<a href='".e_REQUEST_URI."&amp;sub=verify&amp;lan=".$language."' class='btn btn-default' >".$tp->toGlyph('fa-search').LAN_CHECK_2."</a>";

			/*	$text .= "
				<input type='submit' name='ziplang[{$language}]' value=\"".LANG_LAN_23."\" class='btn btn-default' onclick=\"this.value = '".$lan_pleasewait."'\" />";
			*/
				$text .= "</td>
				</tr>";
			}

			$text .= "<tr><th colspan='5'>".defset('LANG_LAN_151','Available')."</th></tr>"; // don't translate this.

			$text .= $this->renderOnlineLanguagePacks();

			$text .= "
			</tr></table>";

			$creditLan = defset('LANG_LAN_152', "Courtesy of the [e107 translation team]"); // don't translate this.

			$srch = array("[","]");
			$repl = array("<a rel='external' href='https://github.com/orgs/e107translations/teams'>","</a>");

			$text .= "<div class='nav navbar'><small class='navbar-text'>".str_replace($srch,$repl,$creditLan)."</small></div>";



/*
			$text .= "<table class='table table-striped'>";

			$text .= "<thead><tr><th>".LAN_OPTIONS."</th></tr></thead><tbody>";

			$srch = array("[","]");
			$repl = array("<a rel='external' href='https://github.com/orgs/e107translations/teams'>","</a>");
			$diz = (deftrue("LANG_LAN_28")) ? LANG_LAN_28 : "Check this box if you are a member of the [e107 translation team].";

			$checked = varset($_COOKIE['e107_certified']) == 1 ? true : false;

			$text .= "<tr><td>";
			$text .= $frm->checkbox('contribute_pack',1,$checked,array('label'=>str_replace($srch,$repl,$diz)));
			;


			$text .= "</td>
			</tr>
			<tr>
			<td>";

			$text .= " </td>
			</tr>";

			$text .= "</tbody></table>";
			*/

			$text .= "</form>";

		//	$text .= "<div class='text-right text-muted' style='padding-top:50px'><small>".LANG_LAN_AGR."</small></div>";



			return $text;



		}


		private function renderOnlineLanguagePacks()
		{

			$text = '';

			$tp = e107::getParser();


			foreach($this->onlinePacks as $lan=>$value)
			{

				if(!empty($this->localPacks[$lan]))
				{

					if($this->localPacks[$lan]['compatibility'] == $value['compatibility'] && !deftrue('e_DEBUG'))
					{
						continue;
					}

				//	$status = $tp->toGlyph('fa-star');
					$class = 'btn-primary';
				}
				else
				{
					$status = "&nbsp;";
					$class = 'btn-default';
				}


				$text .= "<tr>
					<td><span class='language-name'><a rel='external' href='".$value['infoURL']."' title=\"".LAN_MOREINFO."\">".$value['name']."</a></span>";




					$text .= "</td>";

			/*		$text .= "
						<td>".$value['version']."</td>
						<td><a href='".$value['authorURL']."'>".$value['author']."</a></td>";*/


				$url = 'language.php?mode=main&action=download&id='.$value['name']; // $value['url']

				$text .= "
					<td class='text-left'>".$value['date']."</td>
					<td class='text-left'>".$value['version'];

					if(strpos($value['tag'],'-') !==false)
					{
						$text .= " <span class='label label-warning'>".LANG_LAN_153."</span>";
					}

					$text .="</td>
					<td class='text-center'>".$status."</td>
					<td class='text-right'><a  class='btn ".$class."' href='".$url."'><i class='fa fa-arrow-down'></i> ".ADLAN_121."</a></td>
					</tr>";
			}

			return $text;

		}



		private function getTables()
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

				$tables = e107::getDb()->tables('nolan'); // db table list without language tables.
				return array_diff($tables,$exclude);
		}

		private function dbPageEditProcess()
		{
			$tabs = $this->getTables();
			$sql = e107::getDb();
			$tp = e107::getParser();
			$mes = e107::getMessage();

			$message = '';

			if(!empty($_POST['language']))
			{
				$_POST['language'] = e107::getParser()->filter($_POST['language'],'w');
			}

			// ----------------- delete tables ---------------------------------------------
			if (isset($_POST['del_existing']) && $_POST['lang_choices'] && getperms('0'))
			{
				$lang = strtolower($_POST['lang_choices']);

				$_POST['lang_choices'] = e107::getParser()->filter($_POST['lang_choices'],'w');

				foreach ($tabs as $del_table)
				{
					if ($sql->isTable($del_table, $lang))
					{
						//	echo $del_table." exists<br />";
						$qry = "DROP TABLE ".MPREFIX."lan_".$lang."_".$del_table;
						if ($sql->gen($qry))
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
							if (empty($_POST['drop']))
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
					elseif ($sql->isTable($value,$_POST['language']))
					{
						if ($_POST['remove'])
						{
							// Remove table.
							if ($sql->gen("DROP TABLE ".MPREFIX."lan_".$lang."_".$value))
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


		}


		private function dbPageEdit()
		{


			$frm = e107::getForm();
			$tp = e107::getParser();
			$tabs = $this->getTables();
			$sql = e107::getDb();

			$languageSelected = $tp->filter($_GET['lan'],'w');

			$text = "
			<form method='post' action='".e_SELF."?mode=main&action=db'>
				<fieldset id='core-language-edit'>
					<legend class='e-hideme'>".$languageSelected."</legend>
					<table class='table adminlist table-striped'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
			";
			foreach ($tabs as $table_name)
			{
				$installed = 'lan_'.strtolower($languageSelected)."_".$table_name;
				if (stristr($languageSelected, $installed) === FALSE)
				{

					$selected = ($sql->isTable($table_name,$languageSelected)) ? " checked='checked'" : "";

					$tableName = ucfirst(str_replace("_", " ", $table_name));
					$tableLabel = ($selected) ? "<span class='label label-success'>".$tableName."</span>" : $tableName;

					$text .= "
					<tr>
						<td>".$tableLabel."</td>
						<td>
							<div>
							<div class='auto-toggle-area  e-pointer'>
			";



					$text .= "
								<input type='checkbox' class='checkbox e-expandit' data-return='true' data-target='language-datacopy-{$table_name}' id='language-action-{$table_name}' name='{$table_name}' value='1'{$selected}  />
							</div>

									<div id='language-datacopy-{$table_name}' class='offset1 e-hideme e-pointer'>".
						$frm->checkbox("copydata_".$table_name, 1, false, LANG_LAN_15)."

									</div>


							</div>
						</td>
					</tr>
			";
				}
			}
			// ===========================================================================
			// Drop tables ? isset()
			if (varset($_GET['sub'])=='create')
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
							<tr class='warning'>
								<td><strong>".LANG_LAN_07."</strong></td>
								<td>
									".$frm->checkbox('drop', 1)."
									<div class='smalltext field-help'>".$frm->label(LANG_LAN_08, 'drop', 1)."</div>
								</td>
							</tr>
							<tr class='warning'>
								<td><strong>".LAN_CONFDELETE."</strong></td>
								<td >
									".$frm->checkbox('remove', 1)."
									<div class='smalltext field-help'>".$frm->label(LANG_LAN_11, 'remove', 1)."</div>
								</td>
							</tr>
						</tbody>
					</table>
					<div class='buttons-bar center'>
						<input type='hidden' name='language' value='{$languageSelected}' />
						".$frm->admin_button('create_tables','no-value',$baction,$bcaption)."
					</div>
				</fieldset>
			</form>
			";

			$this->addTitle($languageSelected);

			return $text;
			//$ns->tablerender(ADLAN_132.SEP.LANG_LAN_03.SEP.$languageSelected, $mes->render().$text);
			//return true;

		}



		public function dbPage()
		{
			if(!getperms('0'))
			{
				return "Access Denied";
			}

			$this->dbPageEditProcess();

			if($_GET['sub'] == 'edit' || $_GET['sub'] == 'create')
			{
				return $this->dbPageEdit();

			}

		//	$lanlist = e107::getLanguage()->installed();
			$lanlist = $this->installedLanguages;

			$tabs = $this->getTables();

			$sql = e107::getDb();
			$frm = e107::getForm();
			$tp = e107::getParser();
			$mes = e107::getMessage();
			$pref = e107::getPref();

			if(empty($pref['multilanguage']))
			{
				return false;
			}

			$lck = e107::getSingleton('lancheck', e_ADMIN."lancheck.php");




			// Choose Language to Edit:
				$text = "
			<fieldset id='core-language-list'>
				<legend class='e-hideme'>".LANG_LAN_16."</legend>
				<table class='table table-striped adminlist'>
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
						<tr class='active'>
							<td>".$pref['sitelanguage']."</td>
							<td><i>".LANG_LAN_17."</i></td>
							<td></td>
						</tr>
		";
				sort($lanlist);

				foreach ($lanlist as $e_language)
				{

					if ($e_language == $pref['sitelanguage'])
					{
						continue;
					}


					$installed = array();

					$text .= "<tr><td>{$e_language}</td><td>";

					foreach ($tabs as $tab_name)
					{
						if ($e_language != $pref['sitelanguage'] && $sql->isTable($tab_name,$e_language))
						{
							$installed[] = $tab_name;
							$text .= "<span class='label label-success'>".$tab_name."</span> ";
						}
					}

					$text .= (!count($installed)) ? "<span class='label label-danger label-important'>".LANG_LAN_05."</span>" : "";


					$text .= "</td>\n";
					$text .= "<td>
					<form style='margin:0px' id='core-language-form-".str_replace(" ", "-", $e_language)."' action='".e_SELF."?mode=main&action=db' method='post'>\n";
					$text .= "<div>";

					if(count($installed))
					{
						$text .= "<a class='btn btn-primary edit' href='".e_SELF."?mode=main&action=db&sub=edit&lan=".$e_language."'>".LAN_EDIT."</a>";
						// $text .= "<button class='btn btn-primary edit' type='submit' name='edit_existing' value='no-value'><span>".LAN_EDIT."</span></button>";
						$text .= $frm->admin_button('del_existing',LAN_DELETE,'delete');
					//	$text .= "<button class='btn btn-danger delete' type='submit' name='del_existing' value='no-value' title='".$tp->lanVars(LANG_LAN_105, $e_language).' '.LAN_JSCONFIRM."'><span>".LAN_DELETE."</span></button>";
					}
					elseif ($e_language != $pref['sitelanguage'])
					{
						// $text .= "<button class='create' type='submit' name='create_edit_existing' value='no-value'><span>".LAN_CREATE."</span></button>";
						//	$text .= $frm->admin_button('create_edit_existing','no-value','create',LAN_CREATE);
						$text .= "<a class='btn btn-primary create' href='".e_SELF."?mode=main&action=db&sub=create&lan=".$e_language."'>".LAN_CREATE."</a>";
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

				return $text;
			//	e107::getRender()->tablerender(ADLAN_132.SEP.LANG_LAN_16, $mes->render().$text); // Languages -> Tables
		}





	}



	class language_form_ui extends e_admin_form_ui
	{
		function multilanguage_domain($curVal, $mode)
		{
			$pref = e107::getPref();
			$opt = "";
			$langs = explode(",",e_LANLIST);

			foreach($langs as $val)
			{
				if($val != $pref['sitelanguage'])
				{
					$opt .= "<tr><td class='middle' style='width:5%'>".$val."</td><td class='left inline-text'><input type='text' class='form-control' name='multilanguage_domain[".$val."]' value=\"".$pref['multilanguage_domain'][$val]."\" /></td></tr>";
				}
			}

			if($opt)
			{
				return "<table class='table table-striped table-bordered' style='margin-left:0px;width:600px'>".$opt."</table>";
			}
		}
	}


	new language_admin();

	require_once(e_ADMIN."auth.php");

	e107::getAdminUI()->runPage();

	require_once(e_ADMIN."footer.php");
	exit;














/*











require_once ("auth.php");


$frm = e107::getForm();
$mes = e107::getMessage();

e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_lancheck.php");
require_once(e_ADMIN."lancheck.php");
require_once(e_HANDLER."language_class.php");

// $ln = new language;
$ln = $lng;

$lck = e107::getSingleton('lancheck', e_ADMIN."lancheck.php");

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
		if ($sql->isTable($del_table, $lang))
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
		elseif ($sql->isTable($value,$_POST['language']))
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

 if(isset($message) && $message)
 {
 $ns->tablerender(LAN_OK, $message);
 }

 




 










$debug = "<br />f=".$_GET['f'];
$debug .= "<br />mode=".$_GET['mode'];
$debug .= "<br />lan=".$_GET['lan'];
// $ns->tablerender("Debug",$debug);

 $rendered = $lck->init(); // Lancheck functions.












new lanDeveloper;







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
							<textarea name='multilanguage_subdomain' rows='5' cols='15' placeholder='mydomain.com'>{$pref['multilanguage_subdomain']}</textarea>
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
						//TODO class2.php check.
						$text .= "	
						<tr>
							<td>
							".LANG_LAN_106."
							<div class='label-note'>".LANG_LAN_107."</div>
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


*/



class lanDeveloper
{

	private $lanFile = null;
	private $scriptFile = null;
	private $adminFile = false;
	private $commonPhrases = array();
	private $errors = 0;

	function __construct()
	{
		$ns = e107::getRender();
		$mes = e107::getMessage();
		$tp = e107::getParser();

	// ------------------------------ TODO -------------------------------

		if(!empty($_POST['disabled-unused']) && !empty($_POST['disable-unused-lanfile']))
		{
			$disUnusedLanFile = $tp->filter($_POST['disable-unused-lanfile'], 'file');

			$mes = e107::getMessage();

			$data = file_get_contents($disUnusedLanFile);

			$new = $this->disableUnused($data);
			if(file_put_contents($disUnusedLanFile,$new))
			{
				$mes->addSuccess(LANG_LAN_135.$disUnusedLanFile);//Overwriting
			}
			else
			{
				$mes->addError(LANG_LAN_136.$disUnusedLanFile);//Couldn't overwrite
			}

			$ns->tablerender(LANG_LAN_137.SEP.$disUnusedLanFile,$mes->render()."<pre>".htmlentities($new)."</pre>");//Processed
		}





	}


	function run()
	{
		$tp = e107::getParser();
		$mes = e107::getMessage();

		if(varset($_POST['searchDeprecated']) && varset($_POST['deprecatedLans']))
		{

		//	print_a($_POST);
			// $lanfile = $_POST['deprecatedLans'];
			$script = $tp->filter($_POST['deprecatedLans']);

			foreach($script as $k=>$scr)
			{
				if(strpos($scr,e_ADMIN)!==false) // CORE
				{
					$mes->addDebug("Mode: Core Admin Calculated");
					//$scriptname = str_replace("lan_","",basename($lanfile));
					$lanfile[$k] = e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_".basename($scr);
					$this->adminFile = true;
				}
				else  // Root
				{
					$mes->addDebug("Mode: Search Core Root lan calculated");
					$lanfile[$k] = e_LANGUAGEDIR.e_LANGUAGE."/lan_".basename($scr);
					$lanfile[$k] = str_replace("lan_install", "lan_installer", $lanfile[$k]); //quick fix.,

					//$lanfile = $this->findIncludedFiles($script,vartrue($_POST['deprecatedLansReverse']));
				}

				if(!is_readable($scr))
				{
					$mes->addError(LAN_NOTREADABLE.$scr);
					// $script = $scriptname; // matching files. lan_xxxx.php and xxxx.php
				}
			}


		//	$found = $this->findIncludedFiles($script,vartrue($_POST['deprecatedLansReverse']));

//	print_a($found);

			// Exceptions - same language loaded by several scripts.
		//	if($lanfile == e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_e107_update.php")
		//	{
		//		$script = e_ADMIN."update_routines.php,".e_ADMIN."e107_update.php";
		//	}

			if($_POST['deprecatedLanFile'][0] !='auto') //override.
			{
				$lanfile = $tp->filter($_POST['deprecatedLanFile'], 'file');
			}



			$this->lanFile = $lanfile;
			$this->scriptFile = $script;
			$this->commonPhrases = $this->getCommon();

			//	print_a($this->commonPhrases);
			$reverse = vartrue($_POST['deprecatedLansReverse']);
			$reverse = $tp->filter($reverse);

			if($res = $this->unused($lanfile, $script, $reverse))
			{
				return $res;
			//	$ns->tablerender($res['caption'],$mes->render(). $res['text']);
			}

		}

		return false;


	}



	function disableUnused($data)
	{
		$data = str_replace("2008-2010","2008-2017", $data);
		$data = str_replace(' * $URL$
 * $Revision$
 * $Id$
 * $Author$',"",$data);	// TODO FIXME ?

		$tmp = explode("\n",$data);
		foreach($tmp as $line)
		{
			$ret = $this->getDefined($line);
			$newline[] = (in_array($ret['define'],$_SESSION['language-tools-unused']) && substr($line,0,2) !='//') ? "// ".$line : $line;
		}

		return implode("\n",$newline);

	}




	function getDefined($line,$script=false)
	{

		if($script == true)
		{
			return array('define'=>$line,'value'=>'-');
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



	static function form()
	{
		$frm = e107::getForm();
		$mes = e107::getMessage();
		$text = "";

		$text .= "
		<form id='lanDev' method='post' action='".e_REQUEST_URI."'>
			<fieldset id='core-language-package'>

				<table class='table adminform'>
					<colgroup>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>";


		$fl = e107::getFile();
		$fl->mode = 'full';

		// $_SESSION['languageTools_lanFileList'] = null;

		if(!$_SESSION['languageTools_lanFileList'])
		{

			$_SESSION['languageTools_lanFileList'] = $fl->get_files(e_LANGUAGEDIR."English",'.*?(English|lan_).*?\.php$','standard',3);
		}

		//	print_a($_SESSION['languageTools_lanFileList']);


		$text .= "	<tr>
						<td><div class='alert-info alert alert-block'>".e107::getParser()->toHTML(LANG_LAN_140, true)."</div></td>
					</tr>
					<tr>
						<td class='form-inline'>
							<select name='deprecatedLans[]' multiple style='height:200px'>
								<option value=''>".LANG_LAN_141."</option>";


		$omit = array('languages','\.png','\.gif','handlers');
		$lans = $fl->get_files(e_ADMIN,'.php','standard',0);
		asort($lans);

		$fl->setFileFilter(array("^e_"));
		$root = $fl->get_files(e_BASE,'.*?/?.*?\.php',$omit,0);
		asort($root);

		$templates = $fl->get_files(e_CORE."templates",'.*?/?.*?\.php',$omit,0);
		asort($templates);

		$shortcodes = $fl->get_files(e_CORE."shortcodes",'.*?/?.*?\.php',$omit,1);
		asort($shortcodes);

		$exclude = array('lan_admin.php');

		$srch = array(e_ADMIN,e_PLUGIN, e_CORE, e_BASE );


		$text .= "<optgroup label='".LAN_ADMIN."'>";
		foreach($lans as $script=>$lan)
		{
			if(in_array(basename($lan),$exclude))
			{
				continue;
			}
			$selected = (!empty($_POST['deprecatedLans']) && in_array($lan, $_POST['deprecatedLans'])) ? "selected='selected'" : "";
			$text .= "<option value='".$lan."' {$selected}>".str_replace('../e107_',"",$lan)."</option>\n";
		}

		$text .= "</optgroup>";

		$text .= "<optgroup label='".LAN_ROOT."'>";
		foreach($root as $script=>$lan)
		{
			if(in_array(basename($lan),$exclude))
			{
				continue;
			}
			$selected = (!empty($_POST['deprecatedLans']) && in_array($lan, $_POST['deprecatedLans'])) ? "selected='selected'" : "";
			$text .= "<option value='".$lan."' {$selected}>".str_replace($srch,"",$lan)."</option>\n";
		}

		$text .= "</optgroup>";


		$text .= "<optgroup label='".LAN_TEMPLATES."'>";
		foreach($templates as $script=>$lan)
		{
			if(in_array(basename($lan),$exclude))
			{
				continue;
			}
			$selected = (!empty($_POST['deprecatedLans']) && in_array($lan, $_POST['deprecatedLans'])) ? "selected='selected'" : "";
			$text .= "<option value='".$lan."' {$selected}>".str_replace($srch,"",$lan)."</option>\n";
		}

		$text .= "</optgroup>";

		$text .= "<optgroup label='".LAN_SHORTCODES."'>";
		foreach($shortcodes as $script=>$lan)
		{
			if(in_array(basename($lan),$exclude))
			{
				continue;
			}
			$selected = (!empty($_POST['deprecatedLans']) && in_array($lan, $_POST['deprecatedLans'])) ? "selected='selected'" : "";
			$text .= "<option value='".$lan."' {$selected}>".str_replace($srch,"",$lan)."</option>\n";
		}

		$text .= "</optgroup>";
		
//TODO LANs - not sure if this can be replaced with LANS?
		$depOptions = array(
			1 => "Script > Lan File",
			0 => "Script < Lan File"

		);

		$text .= "
								</select> ".
			$frm->select('deprecatedLansReverse',$depOptions,e107::getParser()->filter($_POST['deprecatedLansReverse']),'class=select')." ";

		$search = array(e_PLUGIN,e_ADMIN,e_LANGUAGEDIR."English/",e_THEME);
		$replace = array("Plugins ","Admin ","Core ","Themes ");


		$prev = 'Core';
		$text .= "<select name='deprecatedLanFile[]' multiple style='height:200px'>

								";

		$selected = ($_POST['deprecatedLanFile'][0] == 'auto') ? "selected='selected'" :"";
		$text .= "<option value='auto' {$selected}>".LANG_LAN_142."</option><optgroup label='".LANG_LAN_143."'>\n";//Auto-Detect
		asort($_SESSION['languageTools_lanFileList']);
		foreach($_SESSION['languageTools_lanFileList'] as $val)
		{
			if(strstr($val,e_SYSTEM))
			{
				continue;
			}


			$selected = (!empty($_POST['deprecatedLanFile']) && in_array($val, $_POST['deprecatedLanFile'])) ? "selected='selected'" : "";
			$diz 		= str_replace($search,$replace,$val);
			list($type,$label) = explode(" ",$diz);

			if($type !== $prev)
			{
				$text .= "</optgroup><optgroup label='".$type."'>\n";
			}

			$text .= "<option value='".$val."' ".$selected.">".$label."</option>\n";
			$prev = $type;

		}

		$text .= "</optgroup>";
		$text .= "</select>";

		// $frm->select('deprecatedLanFile',$_SESSION['languageTools_lanFileList'], $_POST['deprecatedLanFile'],'class=select&useValues=1','Select Language File (optional)').
		$text .= $frm->admin_button('searchDeprecated',LAN_GO,'other');
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

		return $mes->render().$text;



	}

	function getCommon()
	{
		$commonPhrases = file_get_contents(e_LANGUAGEDIR."English/English.php");

		if($this->adminFile == true)
		{
			$commonPhrases .= file_get_contents(e_LANGUAGEDIR."English/admin/lan_admin.php");
		}

		$commonLines = explode("\n",$commonPhrases);

		$ar = array();

		foreach($commonLines as $line)
		{
			if($match = $this->getDefined($line))
			{
				$id = $match['define'];
				$ar[$id] = $match['value'];
			}
		}

		return $ar;
	}



	function isFound($needle, $haystack)
	{
		$found = array();

		foreach($haystack as $file => $content)
		{
			$count = 1;
			$lines = explode("\n",$content);
			foreach($lines as $ln)
			{
				if(preg_match("/\b".$needle."\b/i",$ln, $mtch))
				{
					$found[$file]['count'][] = $count;
					$found[$file]['line'][] = $ln;
				}
				$count++;
			}

		}

		if(!empty($found))
		{
			return $found;
		}

		return false;
		// print_a($haystack);

	}


	function compareit($needle,$haystack, $value='',$disabled=false, $reverse=false)
	{

		$found = $this->isFound($needle, $haystack);

//	return "Need=".$needle."<br />hack=".$haystack."<br />val=".$val;
		$foundSimilar = FALSE;
		$foundCommon = FALSE;
		$ar = $this->commonPhrases;
		$commonArray = array_keys($ar);



		// Check if a common phrases was used.
		foreach($ar as $def=>$common)
		{
			similar_text($value, $common, $p);

			if(strtoupper(trim($value)) == strtoupper($common))
			{
				//$text .= "<div style='color:yellow'><b>$common</b></div>";

				$foundCommon = true;
				break;
			}
			elseif($p > 75)
			{
				$foundSimilar = true;
				break;
			}
			$p = 0 ;
		}


		$text = '';
		$text2 = '';


		foreach($haystack as $file=>$script)
		{
		//	$lines = explode("\n",$script);

			$text .= "<td>";
		//	$text2 .= ($reverse == true) ? "<td>" : "";

			if(!empty($found[$file]['count']))
			{
				if($disabled)
				{
					$text .= ADMIN_WARNING_ICON;
					$label = " <span class='label label-important label-danger'>".LANG_LAN_144."</span>";//Must be re-enabled
					$this->errors++;
					// $text .= "blabla";
					//	$class = 'alert alert-warning';
				}
				elseif($reverse == true)
				{
					$value = ADMIN_TRUE_ICON;
					$value .= " ".LAN_LINE."<b>".implode(", ",$found[$file]['count']) ."</b>  "; // "' Found";
					foreach($found[$file]['line'] as $defLine)
					{
						$text .= print_a($defLine, true);
					}

				}
				else
				{
					$text .= " ".LAN_LINE.":<b>".implode(", ",$found[$file]['count']) ."</b>  "; // "' Found";
				}

			}


			if($reverse == true && in_array($needle,$commonArray))
			{
				$found = false;

			}

			if(empty($found))
			{
				// echo "<br />Unused: ".$needle;
				if($reverse == true)
				{
					if(in_array($needle,$commonArray))
					{
					//	print_a($needle);
						//$color = "background-color:#E9EAF2";
						$class = '';
						$value = ADMIN_TRUE_ICON;
						$label = "<span class='label label-success'>".LANG_LAN_130."</span>"; // Common Term.
					}
					else
					{
						//	$color = "background-color:yellow";
						$value = "<a href='#' title=".LAN_MISSING.">".ADMIN_WARNING_ICON."</a>";
						$this->errors++;
						$label = "<span class='label label-important label-danger'>".LANG_LAN_131."</span>";
				//		$class = "alert alert-warning";
					}

				}
				elseif(empty($disabled))
				{
					// $color = "background-color:pink";
					$class = ' ';
					$label = " <span class='label label-important label-danger'>".LAN_UNUSED."</span>";
					$text .= "-";
					$this->errors++; 
				}

				if(!$disabled)
				{
					$_SESSION['language-tools-unused'][] = $needle;
				}
			}
			$text .= "</td>";

		}


		if($foundCommon && $found)
		{
			//$color = "background-color:yellow";
			//	$class = "alert alert-warning";
			$label .= "<div class='label label-important label-danger'><i>".$common."</i> ".LANG_LAN_132."<br />(".LANG_LAN_133." <b>".$def."</b> ".LANG_LAN_134.")</div>";

			// return "<tr><td style='width:25%;'>".$needle .$disabled. "</td><td></td></tr>";
		}

		elseif($foundSimilar && $found && substr($def,0,4) == "LAN_")
		{
			// $color = "background-color:#E9EAF2";
			$label .= "  <span class='label label-warning' style='cursor:help' title=\"".$common."\">".round($p)."% like ".$def."</span> ";
			// $disabled .= " <a class='e-tip' href='#' title=\"".$common."\">" . $def."</a>"; //  $common;
		}

		if($disabled !==false)
		{
			$color = "font-style:italic";
			$class = 'muted text-important ';
			$label .= " <span class='label label-inverse'>".LAN_DISABLED."</span>";
		}

		if(empty($found) && $disabled === true)
		{
			// $needle = "<span class='e-tip' style='cursor:help' title=\"".$value."\">".$needle."</span>";
		}

		return "<tr><td class='".$class."' style='width:15%;$color'>".$needle ."</td><td>".$label. "</td>
	<td class='".$class."'>".print_r($value,true)."</td>
	".$text.$text2."</tr>";
	}





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
			$mes->addDebug("REVERSE MODE ");
			$exclude = array("e_LANGUAGE","e_LANGUAGEDIR","e_LAN","e_LANLIST","e_LANCODE",  "LANGUAGES_DIRECTORY", "e_LANGUAGEDIR_ABS", "LAN");
			$data = '';
			foreach($script as $d)
			{
				$data .= file_get_contents($d)."\n";
			}

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



			$tmp = is_array($lanfile) ? $lanfile : explode(",", $lanfile);
			foreach($tmp as $scr)
			{
				$mes->addDebug("Script : ".$scr);

				if(!file_exists($scr))
				{
					$mes->addError("Reverse Mode: ".LANG_LAN_121." ".$scr);
					continue;
				}

				$compare[$scr] = file_get_contents($scr);
				$mes->addDebug("LanFile: ".$scr);

			}

			$lanfile = $script;
		}
		else
		{
			$mes->addDebug("NORMAL MODE "); 
			$lanDefines = '';
			foreach($lanfile as $arr)
			{
				$lanDefines .= file_get_contents($arr);
				$mes->addDebug("LanFile: ".$arr);
			}


			$tmp = is_array($script) ? $script : explode(",",$script);
			foreach($tmp as $scr)
			{
				if(!file_exists($scr))
				{
					$mes->addError(LANG_LAN_148.": ".LANG_LAN_121." ".$scr);
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
			$mes->addError(LAN_LINE." ".__LINE__.": ".LANG_LAN_121." ".$script);
		}

		if(!$lanDefines)
		{
			$mes->addError(LAN_LINE." ".__LINE__.": ".LANG_LAN_121." ".$lanfile);
		}

		$srch = array("<?php","<?","?>");
		$lanDefines = str_replace($srch,"",$lanDefines);
		$lanDefines = explode("\n", $lanDefines);

		if($lanDefines)
		{
			$text = $frm->open('language-unused');
			$text .= "<table class='table adminlist table-striped table-bordered'>
			<colgroup>
				<col style='width:10%' />
				<col style='width:5%' />
				<col style='width:auto' />";

				foreach($lanfile as $l)
				{
					$text .= "<col style='width:auto' />\n";
				}

				$text .= "
			</colgroup>
			<thead>
			<tr>
				<th>".str_replace(e_LANGUAGEDIR,"",implode("<br />", $lanfile))."</th>
				<th>".LAN_STATUS."</th>";

				if($reverse == false)
				{
					$text .= "<th>".LANG_LAN_149."</th>";
				}

				foreach($compare as $k=>$val)
				{
					$text .= "<th>".str_replace("../","",$k)."</th>";
				}



				if($reverse == true)
				{
					$text .= "<th>".LANG_LAN_124."</th>";
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
			        $disabled = (preg_match("#^//#i",$line)) ? " (".LAN_DISABLED.")" : false;
					if($match = $this->getDefined($line,$reverse))
					{
						$text .= $this->compareit($match['define'], $compare, $match['value'], $disabled, $reverse);

		            }
				}
		    }


			$text .= "</tbody></table>";
	/*
			if(count($_SESSION['language-tools-unused'])>0 && $reverse == false)
			{
				$text .= "<div class='buttons-bar center'>".$frm->admin_button('disabled-unused',LANG_LAN_126,'delete').
				$frm->hidden('disable-unused-lanfile',$lanfile).
				$frm->hidden('deprecatedLans',$script).

				"</div>";
			}
	*/

			$text .= $frm->close();

			if($reverse != true)
			{
				$mes->addInfo(e107::getParser()->toHTML(LANG_LAN_150, true)); //Search Everywhere before commenting out
			}

			$ret['text'] = $mes->render().$text;
			$ret['caption'] = LAN_ERRORS.": ".intval($this->errors);

			return $ret;
		}
		else
		{
	        return FALSE;
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





}





