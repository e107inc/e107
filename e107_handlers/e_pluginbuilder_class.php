<?php


/**
 * Plugin Admin Generator by CaMer0n. //TODO - Added dummy template and shortcode creation, plus e_search, e_cron, e_xxxxx etc.
 */
class e_pluginbuilder
{

		var $fields = array();
		var $table = '';
		var $pluginName = '';
		var $special = array();
		var $tableCount = 0;
		var $tableList = array();
		var $createFiles = false;
		private $buildTable = false;


		function __construct()
		{

			$this->special['checkboxes'] =  array('title'=> '','type' => null, 'data' => null,	 'width'=>'5%', 'thclass' =>'center', 'forced'=> TRUE,  'class'=>'center', 'toggle' => 'e-multiselect', 'fieldpref'=>true);
			$this->special['options'] = array( 'title'=> 'LAN_OPTIONS', 'type' => null, 'data' => null, 'width' => '10%',	'thclass' => 'center last', 'class' => 'center last', 'forced'=>TRUE, 'fieldpref'=>true);


		}


		function run()
		{

			if(!empty($_GET['newplugin']))
			{
				$this->pluginName = e107::getParser()->filter($_GET['newplugin'],'file');
			}

			if(!empty($_GET['createFiles']))
			{
				$this->createFiles	= true;
			}

			if(vartrue($_POST['step']) == 4)
			{
				return $this->step4();
			}

			if(vartrue($_GET['step']) == 3)
			{
				return $this->step3();
			}




			if(!empty($_GET['newplugin']) && $_GET['step']==2)
			{
				return $this->step2();
			}



			return $this->step1();


		}



		function step1()
		{

			$fl = e107::getFile();
			$frm = e107::getForm();
			$ns = e107::getRender();
			$mes = e107::getMessage();
			$tp = e107::getParser();

			$newDir = [];
			$lanDir = [];

			$plugFolders = $fl->get_dirs(e_PLUGIN);
			foreach($plugFolders as $dir)
			{
				$lanDir[$dir] = $dir;
				if(E107_DEBUG_LEVEL == 0 && file_exists(e_PLUGIN.$dir."/admin_config.php"))
				{
					continue;
				}
				$newDir[$dir] = $dir;
			}


			$info = EPL_ADLAN_102;
			$info .= "<ul>";
			$info .= "<li>".str_replace(array('[x]', '[b]', '[/b]'), array(e_PLUGIN, '<strong>', '</strong>'), EPL_ADLAN_103)."</li>";
		//	$info .= "<li>".EPL_ADLAN_104."</li>";
			$info .= "<li>".str_replace(array('[b]', '[/b]'), array('<strong>', '</strong>'), EPL_ADLAN_105)."</li>";
			$info .= "<li>".EPL_ADLAN_106."</li>";
			$info .= "</ul>";

		//	$mes->addInfo($tp->toHTML($info,true));

			$text = $frm->open('createPlugin','get', e_SELF);
			$text .= $frm->hidden('action', 'build');

			$text .= "<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
							<col class='col-control' />
						</colgroup>
				<tr>
					<td>".EPL_ADLAN_107."</td>
					<td><div class='input-append form-inline'>".$frm->open('createPlugin','get',e_SELF."?mode=create").$frm->select("newplugin",$newDir, false, 'size=xlarge').$frm->admin_button('step', 2,'other',LAN_GO)."</div> ".$frm->checkbox('createFiles',1,1,EPL_ADLAN_255).$frm->close()."</td>
					<td><div class='alert alert-info'>".$info."</div></td>
				</tr>
				
				<tr>
					<td>".EPL_ADLAN_108."</td>
					<td><div class='input-append form-inline'>".$frm->open('checkPluginLangs','get',e_SELF."?mode=lans").$frm->select("newplugin",$lanDir, false, 'size=xlarge').$frm->admin_button('step', 2,'other',LAN_GO)."</div> ".$frm->close()."</td>
					<td><div class='alert alert-info'>".EPL_ADLAN_254."</div></td>
				</tr>";


			/* NOT a good idea - requires the use of $_POST which would prevent browser 'go Back' navigation.
			if(e_DOMAIN == FALSE) // localhost.
			{
				$text .= "<tr>
					<td>Pasted MySql Dump Here</td>
					<td>".$frm->textarea('mysql','', 10,80)."
					<span class='field-help'>eg. </span></td>
					</tr>";
			}
			*/


			$text .= "				
				</table>
				<div class='buttons-bar center'>
				
				</div>";

			$text .= $frm->close();

			return $text;

		//	$ns->tablerender(ADLAN_98.SEP.EPL_ADLAN_114, $mes->render() . $text);



		//	$var['lans']['text'] = EPL_ADLAN_226;
		//		$var['lans']['link'] = e_SELF."?mode=lans";


		}


		function enterMysql()
		{

			$frm = e107::getForm();
			return "<div>".$frm->textarea('mysql','')."</div>";

		}


		/**
		 * @param string $table
		 * @param string $file
		 */
		private function buildSQLFile($table, $file)
		{

			$table = e107::getParser()->filter($table);

			e107::getDb()->gen("SHOW CREATE TABLE `#".$table."`");
			$data = e107::getDb()->fetch('num');

			if(!empty($data[1]))
			{
				$createData = str_replace("`".MPREFIX, '`', $data[1]);
				$createData .= ";";
				if(!file_exists($file)/* && empty($this->createFiles)*/)
				{
					file_put_contents($file,$createData);
				}
			}

		}




		function step3()
		{

		//	require_once(e_HANDLER."db_verify_class.php");
		//	$dv = new db_verify;
			$dv = e107::getSingleton('db_verify', e_HANDLER."db_verify_class.php");

			$frm = e107::getForm();
			$ns = e107::getRender();
			$mes = e107::getMessage();
			$tp = e107::getParser();


			$newplug = $tp->filter($_GET['newplugin'],'file');
			$this->pluginName = $newplug;

			$sqlFile = e_PLUGIN.$newplug."/".$newplug."_sql.php";

			if(!empty($_GET['build']) && !file_exists($sqlFile))
			{
				$this->buildSQLFile($_GET['build'], $sqlFile);
			}

			$ret = array();

			if(file_exists($sqlFile))
			{
				$data = file_get_contents($sqlFile);
				$ret =  $dv->getSqlFileTables($data);
			}
			else
			{
				e107::getDebug()->log("SQL File Not Found");
		//		$this->buildTable = true;
			}

			$text = $frm->open('newplugin-step3','post', e_SELF.'?mode=create&action=build&newplugin='.$newplug.'&createFiles='.$this->createFiles.'&step=3');

			$text .= "<ul class='nav nav-tabs'>\n";
			$text .= "<li class='active'><a data-toggle='tab' data-bs-toggle='tab' href='#xml'>".EPL_ADLAN_109."</a></li>";

			$this->tableCount = !empty($ret['tables']) ? count($ret['tables']) : 0;

			if(!empty($ret['tables']))
			{
				foreach($ret['tables'] as $key=>$table)
				{
					$label = "Table: ".$table;
					$text .= "<li><a data-toggle='tab' data-bs-toggle='tab'  href='#".$table."'>".$label."</a></li>";
					$this->tableList[] = $table;
				}
			}


			$text .= "<li><a data-toggle='tab' data-bs-toggle='tab'  href='#preferences'>".LAN_PREFS."</a></li>";
			$text .= "<li><a data-toggle='tab' data-bs-toggle='tab'  href='#addons'>".LAN_ADDONS."</a></li>"; //TODO LAN


			$text .= "</ul>";

			$text .= "<div class='tab-content'>\n";

			$text .= "<div class='tab-pane active' id='xml'>\n";
			$text .= $this->pluginXml();
			$text .= "</div>";

			if(!empty($ret['tables']))
			{
				foreach($ret['tables'] as $key=>$table)
				{
					$text .= "<div class='tab-pane' id='".$table."'>\n";
					$fields = $dv->getFields($ret['data'][$key]);
					$text .= $this->form($table,$fields);
					$text .= "</div>";
				}
			}




			$text .= "<div class='tab-pane' id='preferences'>\n";
			$text .= $this->prefs();
			$text .= "</div>";


			$text .= "<div class='tab-pane' id='addons'>\n";
			$text .= $this->addons();
			$text .= "</div>";


			if(empty($ret['tables']))
			{
				$text .= $frm->hidden($this->pluginName.'_ui[mode]','main');
				$text .= $frm->hidden($this->pluginName.'_ui[pluginName]', $this->pluginName);
			}

			$text .= "</div>";

			$text .= "
			<div class='buttons-bar center'>
			".$frm->hidden('newplugin', $this->pluginName)."
			".$frm->admin_button('step', 4,'other', LAN_GENERATE)."
			</div>";

			$text .= $frm->close();

			$mes->addInfo(EPL_ADLAN_112);

			$mes->addInfo(EPL_ADLAN_113);

			return array('caption'=>EPL_ADLAN_115, 'text'=> $text);
		//	$ns->tablerender(ADLAN_98.SEP.EPL_ADLAN_114.SEP., $mes->render() . $text);
		}



		private function step2()
		{


			$frm = e107::getForm();

			$tables = e107::getDb()->tables();


			$text = $frm->open('buildTab', 'get', e_REQUEST_SELF);

			$text .= "<table class='table adminform'>
				<tr><td colspan='2'><h4>".ucfirst(LAN_OPTIONAL)."</h4></td></tr>

				<tr>
				<td class='col-label'>To generate your <em>".$this->pluginName."_sql.php</em> table creation file, please select your sql table then click 'Refresh'</td>
				<td class='form-inline'>";

			$text .= $frm->select('build', $tables, null, array('useValues'=>1), "(".LAN_OPTIONAL.")");


		//	$text .= "<a href='#' id='build-table-submit' class='btn btn-success'>Refresh</a>";
		//	$text .= $frm->button('step', 3, 'submit', "Continue");
				unset($_GET['step']);
			foreach($_GET as $k=>$v)
			{
				$text .= $frm->hidden($k,$v);

			}
		//	$text .= $frm->hidden("build_table_url", e_REQUEST_SELF.'?'.$qry, array('id'=>'build-table-url'));


			$text .= "</td></tr>
			<tr><td>&nbsp;</td><td>
			".$frm->button('step', 3, 'submit', LAN_CONTINUE)."
			</td></tr></table>";

			$text .=  $frm->close();

/*
			e107::js('footer-inline','

				  $(document).on("click", "#build-table-submit", function(e){

					e.preventDefault();

					$(this).addClass("disabled");

                    var url = $("#build-table-url").val();
                    var sel = $("#build-table-tbl").val();

                    url = url + "&build=" + sel;

					window.location.href = url;

					return false;
				});





			');*/
			$ns = e107::getRender();
			return array('caption'=>EPL_ADLAN_115, 'text'=>$text);
		//	$ns->tablerender(ADLAN_98.SEP.EPL_ADLAN_114.SEP.EPL_ADLAN_115,  $text);


			return $text;

		}





		private function buildTemplateFile()
		{
			$dirName  = e_PLUGIN.$this->pluginName. "/templates";

			if(!is_dir($dirName))
			{
				mkdir($dirName,0755);
			}


			$file    = $dirName. "/".$this->pluginName."_template.php";
			$shortFileName = "templates/".$this->pluginName."_template.php";

			if(file_exists($file) && empty($this->createFiles))
			{
				return e107::getParser()->lanVars(EPL_ADLAN_256,$shortFileName);
			}


$content = <<<TMPL
<?php

// Template File
TMPL;

$upperName = strtoupper($this->pluginName);

$content .=  "
// ".$this->pluginName." Template file

if (!defined('e107_INIT')) { exit; }


\$".$upperName."_TEMPLATE = array();

\$".$upperName."_TEMPLATE['default']['start'] \t= '{SETIMAGE: w=400&h=300}';

\$".$upperName."_TEMPLATE['default']['item'] \t= '';

\$".$upperName."_TEMPLATE['default']['end'] \t= '';



";


			return file_put_contents($file,$content)? LAN_CREATED.': '.$shortFileName : LAN_CREATED_FAILED.': '.$shortFileName;
		}





		private function buildShortcodesFile()
		{
			$file    = e_PLUGIN.$this->pluginName. "/".$this->pluginName."_shortcodes.php";

$content = <<<TMPL
<?php
	

TMPL;

$content .=  "
// ".$this->pluginName." Shortcodes file

if (!defined('e107_INIT')) { exit; }

class plugin_".$this->pluginName."_".$this->pluginName."_shortcodes extends e_shortcode
{

";

		if(!empty($_POST['bullets_ui']['fields']))
		{
			foreach($_POST['bullets_ui']['fields'] as $key=>$row)
			{

				if($key === 'options' || $key === 'checkboxes')
				{
					continue;
				}

$content .= "
	/**
	* {".strtoupper($key)."}
	*/
	public function sc_".$key."(\$parm=null)
	{
	
		return \$this->var['".$key."'];
	}
	

";










			}


		}


$content .= '}';

			return file_put_contents($file,$content)? LAN_CREATED.': '.$this->pluginName."_shortcodes.php" : LAN_CREATED_FAILED.': '.$this->pluginName."_shortcodes.php";
		}


		private function createAddons($list)
		{

			$srch = array('_blank','blank');
			$result = array();

			foreach($list as $addon)
			{
				$addonDest = str_replace("_blank",$this->pluginName,$addon);
				$source         = e_PLUGIN."_blank/".$addon.".php";
				$destination    = e_PLUGIN.$this->pluginName. "/".$addonDest.".php";

				if(file_exists($destination) && empty($this->createFiles))
				{
					$result[] = e107::getParser()->lanVars(EPL_ADLAN_256,$addonDest.'.php');
					continue;
				}

				if($addon === '_blank_template')
				{
					$result[] = $this->buildTemplateFile();
					continue;
				}

				if($addon === '_blank_shortcodes')
				{
					$result[] = $this->buildShortcodesFile();
					continue;
				}

				if($content = file_get_contents($source))
				{
					$content = str_replace($srch, $this->pluginName, $content);

					if(file_exists($destination) && empty($this->createFiles))
					{
						$result[] = e107::getParser()->lanVars(EPL_ADLAN_256,$addonDest.'.php');
					}
					else
					{
						if(file_put_contents($destination,$content))
						{
							$result[] = LAN_CREATED." : ".$addonDest.".php";
						}
					}
				}


			}

			return $result;
		}




		private function addons()
		{
			$plg = e107::getPlugin();

			$list = $plg->getAddonsList();
			$frm = e107::getForm();
			$text = "<table class='table table-striped adminlist' >";


		//Todo LANS
			$dizOther = array(
				'_blank' => "Simple frontend script",
				'_blank_setup' => "Create default table data during install, upgrade, uninstall etc",
				'_blank_menu' => "Menu item for use in the menu manager.",
				'_blank_template' => "Template to allow layout customization by themes.",
				'_blank_shortcodes' => "Shortcodes for the template."
			);

			array_unshift($list,'_blank', '_blank_setup', '_blank_menu', '_blank_template', '_blank_shortcodes');

			$templateFiles = scandir(e_PLUGIN."_blank");



	//print_a($list);
		//	$list[] = "_blank";
		//	$list[] = "_blank_setup";

			foreach($list as $v)
			{

				if(!in_array($v.".php", $templateFiles) && $v != '_blank_template' && $v!='_blank_shortcodes')
				{
					continue;
				}

				$diz = !empty($dizOther[$v]) ? $dizOther[$v] : $plg->getAddonsDiz($v);
				$label = str_replace("_blank", $this->pluginName, $v);
				$id = str_replace('_blank', 'blank', $v);

				$text .= "<tr>";
				$text .= "<td>".$frm->checkbox('addons[]',$v,false,$label)."</td>";
				$text .= "<td><label for='".$frm->name2id('addons-'.$id)."'>".$diz."</label></td>";
				$text .= "</tr>";
			}

			$text .= "</table>";

			return $text;

		}



		function prefs()
		{
			$frm = e107::getForm();

			$text = '';

				$options = array(
					'text'		=> EPL_ADLAN_116,
					'number'	=> EPL_ADLAN_117,
					'url'		=> EPL_ADLAN_118,
					'textarea'	=> EPL_ADLAN_119,
					'bbarea'	=> EPL_ADLAN_120,
					'boolean'	=> EPL_ADLAN_121,
					"method"	=> EPL_ADLAN_122,
					"image"		=> EPL_ADLAN_123,

					"dropdown"	=> EPL_ADLAN_124,
					"userclass"	=> EPL_ADLAN_125,
					"language"	=> EPL_ADLAN_126,

					"icon"		=> EPL_ADLAN_127,

					"file"		=> EPL_ADLAN_128,

				);


			$text = "<table class='table table-striped'>";

			for ($i=0; $i < 10; $i++)
			{
				$text .= "<tr><td>".
				$frm->text("pluginPrefs[".$i."][index]", '',40,'placeholder='.EPL_ADLAN_129)."</td><td>".
				$frm->text("pluginPrefs[".$i."][value]", '',50,'placeholder='.EPL_ADLAN_130)."</td><td>".
				$frm->select("pluginPrefs[".$i."][type]", $options, '', 'class=null', EPL_ADLAN_131)."</td><td>".
				$frm->text("pluginPrefs[".$i."][help]", '',80,'size=xxlarge&placeholder='.EPL_ADLAN_174)."</td>".
				"</tr>";
			}

			$text .= "</table>";
			return $text;
		}


		function pluginXml()
		{


			//TODO Plugin.xml Form Fields. .

			$data = array(
				'main' 			=> array('name','lang','version','date', 'compatibility'),
				'author' 		=> array('name','url'),
				'summary' 		=> array('summary'),
				'description' 	=> array('description'),
				'keywords' 		=> array('one','two','three'),
				'category'		=> array('category'),
				'copyright'		=> array('copyright'),
		//		'adminLinks'	=> array('url','description','icon','iconSmall','primary'),
		//		'sitelinks'		=> array('url','description','icon','iconSmall')
			);

			// Load old plugin.php file if it exists;
			$legacyFile = e_PLUGIN.$this->pluginName."/plugin.php";
			if(file_exists($legacyFile))
			{
				$eplug_name = $eplug_author = $eplug_url = $eplug_description = "";
				$eplug_tables = array();

				require_once($legacyFile);
				$mes = e107::getMessage();
				$mes->addInfo("Loading plugin.php file");

				$defaults = array(
					"main-name"					=> $eplug_name,
					"author-name"				=> $eplug_author,
					"author-url"				=> $eplug_url,
					"description-description"	=> $eplug_description,
					"summary-summary"			=> $eplug_description
				);

				if(count($eplug_tables) && !file_exists(e_PLUGIN.$this->pluginName."/".$this->pluginName."_sql.php"))
				{

					$cont = '';
					foreach($eplug_tables as $tab)
					{
						if(strpos($tab,"INSERT INTO")!==FALSE)
						{
							continue;
						}

						$cont .= "\n".str_replace("\t"," ",$tab);

					}

					if(file_put_contents(e_PLUGIN.$this->pluginName."/".$this->pluginName."_sql.php",$cont))
					{
						$info = str_replace('[x]', $this->pluginName."_sql.php", EPL_ADLAN_132);
						$mes->addInfo($info,'default',true);
						$red = e107::getRedirect();
						$red->redirect(e_REQUEST_URL);
					//	$red->redirect(e_SELF."?mode=create&newplugin=".$this->pluginName."&createFiles=1&step=2",true);
					}
					else
					{
						$msg = str_replace('[x]', $this->pluginName."_sql.php", EPL_ADLAN_133)."<br />";
						$msg .= str_replace(array('[x]','[y]'), array($this->pluginName."_sql.php",$cont), EPL_ADLAN_134);
						$mes->addWarning($msg);
					}


				}
			}

			$existingXml = e_PLUGIN.$this->pluginName."/plugin.xml";
			if(file_exists($existingXml))
			{
				$p = e107::getXml()->loadXMLfile($existingXml,true);

		//		print_a($p);
				$defaults = array(
					"main-name"					=> varset($p['@attributes']['name']),
					"main-lang"					=> varset($p['@attributes']['lan']),
					"author-name"				=> varset($p['author']['@attributes']['name']),
					"author-url"				=> varset($p['author']['@attributes']['url']),
					"description-description"	=> varset($p['description']),
					"summary-summary"			=> varset($p['summary'], $p['description']),
					"category-category"			=> varset($p['category']),
					"copyright-copyright"			=> varset($p['copyright']),
					"keywords-one"				=> varset($p['keywords']['word'][0]),
					"keywords-two"				=> varset($p['keywords']['word'][1]),
					"keywords-three"			=> varset($p['keywords']['word'][2]),
				);

				unset($p);

			}

			$text = "<table class='table adminform'>";

			foreach($data as $key=>$val)
			{
				$text.= "<tr><td>$key</td><td>
				<div class='controls'>";
				foreach($val as $type)
				{
					$nm = $key.'-'.$type;
					$name = "xml[$nm]";
					$size = (count($val)==1) ? 'span7 col-md-7' : 'span2 col-md-2';
					$text .= "<div class='$size'>".$this->xmlInput($name, $key."-". $type, vartrue($defaults[$nm]))."</div>";
				}

				$text .= "</div></td></tr>";


			}
			$text .= "</table>";

			return $text;
		}


		function xmlInput($name, $info, $default='')
		{
			$frm = e107::getForm();
			list($cat,$type) = explode("-",$info);

			$size 		= 30; // Textbox size.
			$help		= '';
			$pattern	= "";
			$required	= false;

			switch ($info)
			{

				case 'main-name':
					$help 		= EPL_ADLAN_135;
					$required 	= true;
					$pattern 	= "[A-Za-z0-9 -]*";
					$xsize		= 'medium';
				break;

				case 'main-lang':
					$help 		= EPL_ADLAN_136;
			//		$required 	= false;
					$placeholder= " ";
					$pattern 	= "[A-Z0-9_]*";
					$xsize		= 'medium';
				break;

				case 'main-date':
					$help 		= EPL_ADLAN_137;
					$required 	= true;
					$xsize		= 'medium';
				break;

				case 'main-version':
					$default 	= '1.0';
					$required 	= true;
					$help 		= EPL_ADLAN_138;
					$pattern	= "^[\d]{1,2}\.[\d]{1,2}(\.[\d]{1,2})?$";
					$xsize		= 'small';
				break;

				case 'main-compatibility':
					$default 	= '2.0';
					$required 	= true;
					$help 		= EPL_ADLAN_139;
					$pattern	= "^[\d]{1,2}\.[\d]{1,2}$";
					$xsize		= 'small';
				break;

				case 'author-name':
					$default 	= (vartrue($default)) ? $default : USERNAME;
					$required 	= true;
					$help 		= EPL_ADLAN_140;
					$pattern	= "[A-Za-z \.0-9]*";
					$xsize		= 'medium';
				break;

				case 'author-url':
					$required 	= true;
					$help 		= EPL_ADLAN_141;
				//	$pattern	= "https?://.+";
					$xsize		= 'medium';
				break;

				//case 'main-installRequired':
				//	return "Installation required: ".$frm->radio_switch($name,'',LAN_YES, LAN_NO);
				//break;

				case 'summary-summary':
					$help 		= EPL_ADLAN_142."<br />".EPL_ADLAN_143;
					$required 	= true;
					$size 		= 130;
					$placeholder= " ";
					$pattern	= "[A-Za-z -\.0-9]*";
					$xsize		= 'block-level';
				break;

				case 'keywords-one':
					$type = 'keywordDropDown';
					$required = true;
					$help 		= EPL_ADLAN_144;
				break;

				case 'keywords-three':
				case 'keywords-two':
					$help 		= EPL_ADLAN_144."<br />".EPL_ADLAN_143;
					$required 	= true;
					$size 		= 20;
					$placeholder= " ";
					$pattern 	= '^[a-z]*$';
					$xsize		= 'medium';
				break;

				case 'description-description':
					$help 		= EPL_ADLAN_145."<br />".EPL_ADLAN_143;
					$required 	= true;
					$size 		= 100;
					$placeholder = " ";
					$pattern	= "[A-Za-z -\.0-9]*";
					$xsize		= 'block-level';
				break;


				case 'category-category':
					$help 		= EPL_ADLAN_146;
					$required 	= true;
					$size 		= 20;
				break;

				default:

				break;
			}

			$req = $required ? "&required=1" : "";
			$placeholder = (varset($placeholder)) ? $placeholder : $type;
			$pat = !empty($pattern) ? "&pattern=".$pattern : "";
			$sz = !empty($xsize) ? "&size=".$xsize : "";

			switch ($type)
			{
				case 'date':
					$text = $frm->datepicker($name, time(), 'format=yyyy-mm-dd&return=string'.$req . $sz);
				break;

				case 'description':
					$text = $frm->textarea($name,$default, 3, 100, $req.$sz);	// pattern not supported.
				break;


				case 'category':
					$options = array(
					'settings'	=> EPL_ADLAN_147,
					'users'		=> EPL_ADLAN_148,
					'content'	=> EPL_ADLAN_149,
					'tools'		=> EPL_ADLAN_150,
					'manage'	=> EPL_ADLAN_151,
					'misc'		=> EPL_ADLAN_152,
					'menu'		=> EPL_ADLAN_153,
					'about'		=> EPL_ADLAN_154
					);

					$text = $frm->select($name, $options, $default,'required=1&class=form-control', true);
				break;

				case 'keywordDropDown':

					$options = array(

						'generic',
						'admin',
					    'messaging',
					    'enhancement',
					    'date',
					    'commerce',
					    'form',
					    'gaming',
					    'intranet',
					    'multimedia',
					    'information',
					    'mail',
					    'search',
						'stats',
						'files',
						'security',
						'generic',
						'language'
					);

					sort($options);

					$text = $frm->select($name, $options, $default,'required=1&class=form-control&useValues=1', true);


				break;


				default:
					$text = $frm->text($name, $default, $size, 'placeholder='.$placeholder . $sz. $req. $pat);
				break;
			}


			$text .= ($help) ? "<span class='field-help'>".$help."</span>" : "";
			return $text;

		}

		function createXml($data)
		{
		//	print_a($_POST);
			$ns = e107::getRender();
			$mes = e107::getMessage();
			$tp = e107::getParser();
			$newArray = [];

			foreach($data as $key=>$val)
			{
				$key = strtoupper(str_replace("-","_",$key));
				$newArray[$key] = $val;
			}

			$newArray['DESCRIPTION_DESCRIPTION'] = strip_tags($tp->toHTML($newArray['DESCRIPTION_DESCRIPTION'],true));

			$_POST['pluginPrefs'] = $tp->filter($_POST['pluginPrefs']);

			$plugPref = array();

			foreach($_POST['pluginPrefs'] as $val)
			{
				if(vartrue($val['index']))
				{
					$id = $val['index'];
					$plugPref[$id] = $val['value'];
				}
			}

		//	print_a($_POST['pluginPrefs']);

			if(!empty($plugPref))
			{
				$xmlPref = "<pluginPrefs>\n";
				foreach($plugPref as $k=>$v)
				{
					$xmlPref .= "		<pref name='".$k."'>".$v."</pref>\n";
				}

				$xmlPref .= "	</pluginPrefs>";
				$newArray['PLUGINPREFS'] = $xmlPref;
			}

			//	print_a($newArray);
			// print_a($this);

$template = <<<TEMPLATE
<?xml version="1.0" encoding="utf-8"?>
<e107Plugin name="{MAIN_NAME}" lan="{MAIN_LANG}" version="{MAIN_VERSION}" date="{MAIN_DATE}" compatibility="{MAIN_COMPATIBILITY}" installRequired="true" >
	<author name="{AUTHOR_NAME}" url="{AUTHOR_URL}" />
	<summary lan="">{SUMMARY_SUMMARY}</summary>
	<description lan="">{DESCRIPTION_DESCRIPTION}</description>
	<keywords>
		<word>{KEYWORDS_ONE}</word>
		<word>{KEYWORDS_TWO}</word>
		<word>{KEYWORDS_THREE}</word>
	</keywords>
	<category>{CATEGORY_CATEGORY}</category>
	<copyright>{COPYRIGHT_COPYRIGHT}</copyright>
	<adminLinks>
		<link url="admin_config.php" description="{ADMINLINKS_DESCRIPTION}" icon="images/icon_32.png" iconSmall="images/icon_16.png" icon128="images/icon_128.png" primary="true" >LAN_CONFIGURE</link>
	</adminLinks>
	{PLUGINPREFS}
</e107Plugin>
TEMPLATE;


// pluginPrefs




// TODO
/*
	<siteLinks>
		<link url="{e_PLUGIN}_blank/_blank.php" perm="everyone">Blank</link>
	</siteLinks>
	<pluginPrefs>
		<pref name="blank_pref_1">1</pref>
		<pref name="blank_pref_2">[more...]</pref>
	</pluginPrefs>
	<userClasses>
		<class name="blank_userclass" description="Blank Userclass Description" />
	</userClasses>
	<extendedFields>
		<field name="custom" type="EUF_TEXTAREA" default="0" active="true" />
	</extendedFields>
*/


			$result = e107::getParser()->simpleParse($template, $newArray);
			$path = e_PLUGIN.$this->pluginName."/plugin.xml";

			if(file_exists($path) && empty($this->createFiles))
			{
				return  htmlentities($result);
			}


			if($this->createFiles || !file_exists($path))
			{
				if(file_put_contents($path,$result) )
				{
					$mes->addSuccess(EPL_ADLAN_155." ".$path);
				}
				else {
					$mes->addError(EPL_ADLAN_156." ".$path);
				}
			}
			return  htmlentities($result);

		//	$ns->tablerender(LAN_CREATED.": plugin.xml", "<pre  style='font-size:80%'>".htmlentities($result)."</pre>");
		}






		function form($table,$fieldArray)
		{

			$frm = e107::getForm();

			$modes = array(
				"main"    => EPL_ADLAN_157,
				"cat"     => EPL_ADLAN_158,
				"other1"  => EPL_ADLAN_159,
				"other2"  => EPL_ADLAN_160,
				"other3"  => EPL_ADLAN_161,
				"other4"  => EPL_ADLAN_162,
				'exclude' => EPL_ADLAN_163,
			);

		//	echo "TABLE COUNT= ".$this->tableCount ;
			$defaultMode = [];

			$this->table = $table."_ui";

			$c=0;
			foreach($modes as $id=>$md)
			{
				if($tbl = varset($this->tableList[$c], false))
				{
					$defaultMode[$tbl] = $id;
					$c++;
				}
			}

		//	print_a($defaultMode);

			$text = 	$frm->hidden($this->table.'[pluginName]', $this->pluginName, 15).
						$frm->hidden($this->table.'[table]', $table, 15);

			if($this->tableCount > 1)
			{
				$text .= "<table class='table adminform'>\n";
				$text .= "
					<tr>
						<td>Mode</td>
						<td>".$frm->select($this->table."[mode]",$modes, $defaultMode[$table], 'required=1&class=null', true)."</td>
					</tr>
					
				";
			}
			else
			{
				$text .= $frm->hidden($this->table.'[mode]','main');
			}

			$text .= "</table>".$this->special('checkboxes');

			$text .= "<table class='table adminlist'>
						<thead>
						<tr>
							<th>".EPL_ADLAN_164."</th>
							<th>".EPL_ADLAN_165."</th>
							<th>".EPL_ADLAN_166."</th>
							<th>".EPL_ADLAN_167."</th>
							<th>".EPL_ADLAN_168."</th>
							<th class='center'>".EPL_ADLAN_169."</th>
							<th class='center'>".EPL_ADLAN_170."</th>
							<th class='center'>".EPL_ADLAN_171."</th>
							<th class='center' title='".EPL_ADLAN_177."'>".EPL_ADLAN_172."</th>
							<th class='center' title='".EPL_ADLAN_178."'>".EPL_ADLAN_173."</th>
							<th class='center' title='".EPL_ADLAN_257."'>R/O</th>
							
							<th>".EPL_ADLAN_174."</th>
							<th>".EPL_ADLAN_175."</th>
							<th>".EPL_ADLAN_176."</th>
						</tr>
						</thead>
						<tbody>
						";

			foreach($fieldArray as $name=>$val)
			{
				$text .= "<tr>
					<td>".$name."</td>
					<td>".$frm->text($this->table."[fields][".$name."][title]", $this->guess($name, $val,'title'),35, 'required=1')."</td>
					<td>".$this->fieldType($name, $val)."</td>
					<td>".$this->fieldData($name, $val)."</td>
					<td>".$frm->text($this->table."[fields][".$name."][width]", $this->guess($name, $val,'width'), 4, 'size=mini')."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][batch]", 1, $this->guess($name, $val,'batch'))."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][filter]", 1, $this->guess($name, $val,'filter'))."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][inline]", 1, $this->guess($name, $val,'inline'))."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][validate]", 1)."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][fieldpref]", 1, $this->guess($name, $val,'fieldpref'))."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][readonly]", 1)."</td>
					<td>".$frm->text($this->table."[fields][".$name."][help]",'', 50,'size=medium')."</td>
					<td>".$frm->text($this->table."[fields][".$name."][readParms]",'', 60,'size=small')."</td>
					<td>".$frm->text($this->table."[fields][".$name."][writeParms]",'', 60,'size=small').
					$frm->hidden($this->table."[fields][".$name."][class]", $this->guess($name, $val,'class')).
					$frm->hidden($this->table."[fields][".$name."][thclass]", $this->guess($name, $val,'thclass')).
					"</td>
					</tr>";

			}
			//'width' => '20%',	'thclass' => 'center',	'batch' => TRUE, 'filter'=>TRUE, 'parms' => 'truncate=30', 'validate' => false, 'help' => 'Enter blank URL here', 'error' => 'please, ener valid URL'),
			$text .= "</tbody></table>".$this->special('options');


			return $text;

		}

		// Checkboxes and Options.
		function special($name)
		{
			$frm = e107::getForm();
			$text = "";

			foreach($this->special[$name] as $key=>$val)
			{
				$text .= $frm->hidden($this->table."[fields][".$name."][".$key."]", $val);
			}

			return $text;

		}


		/**
		 * @param $name
		 * @param $val
		 * @return string
		 */
		function fieldType($name, $val)
		{
			$type = strtolower($val['type']);
			$frm = e107::getForm();

			if(strtolower($val['default']) == "auto_increment")
			{
				$key = $this->table."[pid]";
				return "Primary Id".
				$frm->hidden($this->table."[fields][".$name."][type]",'number').
				$frm->hidden($key, $name );	//
			}

			switch ($type)
			{
				case 'date':
				case 'datetime':
				case 'time':
				case 'timestamp':
					$array = array(
					'text'		=> EPL_ADLAN_179,
					"hidden"	=> EPL_ADLAN_180,
					"method"	=> EPL_ADLAN_186,
					);
				break;

				case 'int':
				case 'tinyint':
				case 'bigint':
				case 'smallint':
				case 'mediumint':
					$array = array(
					"boolean"	=> EPL_ADLAN_181,
					"number"	=> EPL_ADLAN_182,
					"dropdown"	=> EPL_ADLAN_183,
					"userclass"	=> EPL_ADLAN_184,
					"datestamp"	=> LAN_DATE,
					"method"	=> EPL_ADLAN_186,
					"hidden"	=> EPL_ADLAN_187,
					"user"		=> EPL_ADLAN_188,
					);
				break;

				case 'decimal':
				case 'double':
				case 'float':

					$array = array(
					"number"	=> EPL_ADLAN_182,
					"dropdown"	=> EPL_ADLAN_190,
					"method"	=> EPL_ADLAN_191,
					"hidden"	=> EPL_ADLAN_192,
					);
				break;

				case 'varchar':
				case 'tinytext':
				case 'tinyblob':
				$array = array(
					'text'		=> EPL_ADLAN_193,
					"url"		=> EPL_ADLAN_194,
					"email"		=> EPL_ADLAN_195,
					"ip"		=> EPL_ADLAN_196,
					"number"	=> EPL_ADLAN_197,
					"password"	=> EPL_ADLAN_198,
					"tags"		=> EPL_ADLAN_199,

					"dropdown"	=> EPL_ADLAN_200,
					"userclass"	=> EPL_ADLAN_201,
					"language"	=> EPL_ADLAN_202,

					"icon"		=> EPL_ADLAN_203,
					"image"		=> EPL_ADLAN_204,
					"file"		=> EPL_ADLAN_205,
					"method"	=> EPL_ADLAN_206,

					"hidden"	=> EPL_ADLAN_207
					);
				break;

				case 'enum':
				$array = array(
					"dropdown"	=> EPL_ADLAN_200,
					"tags"		=> EPL_ADLAN_211,
					"method"	=> EPL_ADLAN_212,
					"hidden"	=> EPL_ADLAN_215
					);
				break;

				case 'text':
				case 'mediumtext':
				case 'longtext':
				case 'blob':
				case 'mediumblob':
				case 'longblob':
				$array = array(
					'textarea'	=> EPL_ADLAN_208,
					'bbarea'	=> EPL_ADLAN_209,
					'text'		=> EPL_ADLAN_210,
					"tags"		=> EPL_ADLAN_211,
					"method"	=> EPL_ADLAN_212,
					"image"		=> EPL_ADLAN_213,
					"images"	=> EPL_ADLAN_214,
					"hidden"	=> EPL_ADLAN_215
					);
				break;

				default:
				 $array = [];
			}

		//	asort($array);

			$fname = $this->table."[fields][".$name."][type]";
			return $frm->select($fname, $array, $this->guess($name, $val),'required=1&class=null', true);

		}

		// Guess Default Field Type based on name of field.
		function guess($data, $val=null,$mode = 'type')
		{
			$tmp = explode("_",$data);
			$name = '';

			if(count($tmp) == 3) // eg Link_page_title
			{
				$name = $tmp[2];
			}
			elseif(count($tmp) == 2) // Link_description
			{
				$name = $tmp[1];
			}
			elseif(count($tmp) === 1)
			{
				$name = $data;
			}

			$ret['title'] = ucfirst($name);
			$ret['width'] = 'auto';
			$ret['class'] = 'left';
			$ret['thclass'] = 'left';

		//	echo "<br />name=".$name;
			switch ($name)
			{

				case 'id':
					$ret['title'] = 'LAN_ID';
					$ret['type'] = 'boolean';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = false;
					$ret['width'] = '5%';
				break;

				case 'start':
				case 'end':
				case 'datestamp':
				case 'date':
					$ret['title'] = 'LAN_DATESTAMP';
					$ret['type'] = 'datestamp';
					$ret['batch'] = false;
					$ret['filter'] = true;
					$ret['fieldpref'] = true;
					$ret['inline'] = false;
				break;

				case 'prename':
				case 'firstname':
				case 'lastname':
				case 'company':
				case 'city':
					$ret['title'] = ucfirst($name);
					$ret['type'] = 'text';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['fieldpref'] = true;
					$ret['inline'] = true;
				break;


				case 'name':
				case 'title':
				case 'subject':
				case 'summary':
					$ret['title'] = 'LAN_TITLE';
					$ret['type'] = 'text';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['fieldpref'] = true;
					$ret['inline'] = true;
				break;

				case 'email':
				case 'email2':
					$ret['title'] = 'LAN_EMAIL';
					$ret['type'] = 'email';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['fieldpref'] = false;
					$ret['inline'] = true;
				break;


				case 'ip':
					$ret['title'] = 'LAN_IP';
					$ret['type'] = 'ip';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['fieldpref'] = false;
					$ret['inline'] = false;
				break;

				case 'user':
				case 'userid':
				case 'author':
					$ret['title'] = 'LAN_AUTHOR';
					$ret['type'] = 'user';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = false;
				break;

				case 'thumb':
				case 'thumbnail':
				case 'image':
					$ret['title'] = 'LAN_IMAGE';
					$ret['type'] = 'image';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = false;
				break;

				case 'total':
				case 'order':
				case 'limit':
					$ret['title'] = 'LAN_ORDER';
					$ret['type'] = 'number';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = false;
				break;

				case 'code':
				case 'zip':
					$ret['title'] = ucfirst($name);
					$ret['type'] = 'number';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = true;
				break;

				case 'state':
				case 'country':
				case 'category':
					$ret['title'] = ($name == 'category') ? 'LAN_CATEGORY' : ucfirst($name);
					$ret['type'] = 'dropdown';
					$ret['batch'] = true;
					$ret['filter'] = true;
					$ret['fieldpref'] = true;
					$ret['inline'] = true;
				break;

				case 'type':
					$ret['title'] = 'LAN_TYPE';
					$ret['type'] = 'dropdown';
					$ret['batch'] = true;
					$ret['filter'] = true;
					$ret['fieldpref'] = true;
					$ret['inline'] = true;
				break;

				case 'icon':
				case 'button':
					$ret['title'] = 'LAN_ICON';
					$ret['type'] = 'icon';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = false;
				break;

				case 'website':
				case 'url':
				case 'homepage':
					$ret['title'] = 'LAN_URL';
					$ret['type'] = 'url';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['inline'] = true;
				break;

				case 'visibility':
				case 'class':
					$ret['title'] = 'LAN_USERCLASS';
					 $ret['type'] = 'userclass';
					 $ret['batch'] = true;
					 $ret['filter'] = true;
					 $ret['fieldpref'] = true;
					$ret['inline'] = true;
				break;

				case 'notes':
				case 'comment':
				case 'comments':
				case 'address':
				case 'description':
					$ret['title'] = ($name == 'description') ? 'LAN_DESCRIPTION' : ucfirst($name);
					 $ret['type'] = ($val['type'] == 'TEXT') ? 'textarea' : 'text';
					 $ret['width'] = '40%';
					$ret['inline'] = false;
				break;

				default:
					$ret['type'] = 'boolean';
					$ret['class'] = 'left';
					$ret['batch'] = false;
					$ret['filter'] = false;
					$ret['thclass'] = 'left';
					$ret['width'] = 'auto';
					$ret['inline'] = false;
					break;
			}

			return vartrue($ret[$mode]);

		}




		function fieldData($name, $val)
		{
			$frm = e107::getForm();
			$type = $val['type'];

			$strings = array('time','timestamp','datetime','year','tinyblob','blob',
							'mediumblob','longblob','tinytext','mediumtext','longtext','text','date');


			$type = strtolower($type);

			if(in_array($type,$strings))
			{
				$value = 'str';
			}
			elseif($type === 'varchar' || $type === 'char')
			{
				$value = 'safestr';
			}
			elseif($type === 'decimal' || $type === 'float')
			{
				$value = 'float';
			}
			else
			{
				$value = 'int';
			}


			$fname = $this->table."[fields][".$name."][data]";

			return $frm->hidden($fname, $value). "<a href='#' class='e-tip' title='$type' >".$value."</a>" ;

		}




// ******************************** CODE GENERATION AREA *************************************************

		function step4()
		{

			$tp = e107::getParser();
			$xmlText = null;
			$addonResults = null;

			$pluginTitle = $tp->filter($_POST['xml']['main-name']);

			if($_POST['xml'])
			{
				$_POST['xml'] = $tp->filter($_POST['xml']);
				$xmlText =	$this->createXml($_POST['xml']);
			}

			if(!empty($_POST['addons']))
			{
				$_POST['addons'] = $tp->filter($_POST['addons']);
				$addonResults = $this->createAddons($_POST['addons']);
			}

		//	e107::getDebug()->log($_POST);

			unset($_POST['step'],$_POST['xml'], $_POST['addons']);
			$pluginFolder = $tp->filter($_POST['newplugin'],'file');

			// Create some default plugin icons.
			$imgDir = e_PLUGIN.$pluginFolder.'/images';
			if(!is_dir($imgDir))
			{
				mkdir($imgDir,0775);
				$icons = [16,32,128];
				foreach($icons as $size)
				{
					@copy(e_PLUGIN.'_blank/images/icon_'.$size.'.png', $imgDir.'/icon_'.$size.'.png');
				}
			}

			$text = $this->buildAdminUI($_POST, $pluginFolder, $pluginTitle);

			$ns = e107::getRender();
			$mes = e107::getMessage();

			$generatedFile = e_PLUGIN.$pluginFolder."/admin_config.php";

			$startPHP = chr(60)."?php";
			$endPHP =  '';

			if(!empty($addonResults))
			{
				foreach($addonResults as $v)
				{
					$mes->addSuccess($v);
				}
			}

			if(file_exists($generatedFile) && empty($this->createFiles))
			{
				$message = e107::getParser()->lanVars(EPL_ADLAN_256,"admin_config.php");
				$mes->addSuccess($message);
			}
			else
			{
				if(file_put_contents($generatedFile, $startPHP .$text . $endPHP))
				{
					$message = str_replace("[x]", "<a class='alert-link' href='".$generatedFile."'>".EPL_ADLAN_216."</a>", EPL_ADLAN_217);
					$mes->addSuccess($message);
				}
				else
				{
					$mes->addError(str_replace('[x]', $generatedFile, EPL_ADLAN_218));
				}
			}

		//	echo $mes->render();

			$ret = "<h3>plugin.xml</h3>";
			$ret .= "<pre style='font-size:80%'>".$xmlText."</pre>";
			$ret .= "<h3>admin_config.php</h3>";
			$ret .= "<pre style='font-size:80%'>".$text."</pre>";

			e107::getPlug()->clearCache();

			return array('caption'=>EPL_ADLAN_253, 'text'=> $ret);


		}


		/**
		 * @param array  $fields
		 * @param string $table
		 * @param string $type
		 * @return string
		 */
		private function buildAdminUIBatchFilter($fields, $table, $type=null)
	{
		$text = '';

		$typeUpper = ucfirst($type);

		$params = ($type === 'batch') ? "\$selected, \$type" : "\$type";

		foreach($fields as $fld=>$val)
		{
			if(varset($val['type']) !== 'method')
			{
				continue;
			}



			$text .= "
	
	 // Handle ".$type." options as defined in ".str_replace("_ui", "_form_ui", $table)."::".$fld.";  'handle' + action + field + '".$typeUpper."'
	 // @important \$fields['".$fld."']['".$type."'] must be true for this method to be detected. 
	 // @param \$selected
	 // @param \$type
	function handleList".eHelper::camelize($fld,true).$typeUpper."(".$params.")
	{
";

	if($type === 'filter')
	{
		$text .= "
		\$this->listOrder = '".$fld." ASC';
	";

	}
	else
	{
		$text .= "
		\$ids = implode(',', \$selected);\n";

	}

$text .= "
		switch(\$type)
		{
			case 'custom".$type."_1':
";

$text .= ($type === 'batch') ? "				// do something" : "				// return ' ".$fld." != 'something' '; ";


$text .= "
				e107::getMessage()->addSuccess('Executed custom".$type."_1');
				break;

			case 'custom".$type."_2':
";

$text .= ($type === 'batch') ? "				// do something" : "				// return ' ".$fld." != 'something' '; ";

$text .= "
				e107::getMessage()->addSuccess('Executed custom".$type."_2');
				break;

		}


	}
";
		}


		return $text;

	}


		/**
		 * @param array $vars
		 * @return null|string|string[]
		 */
		private function buildAdminUIFields($vars)
	{
			$srch = array(

				"\n",
			//	"),",
				"    ",
				"'forced' => '1'",
				"'batch' => '1'",
				"'filter' => '1'",
				"'batch' => '0'",
				"'filter' => '0'",
				"'inline' => '1'",
				"'validate' => '1'",
				"'readonly' => '1'",
			//	", 'fieldpref' => '1'",
				"'type' => ''",
				"'data' => ''",
				"  array (  )",
				'  ',
			 );

			$repl = array(

				 "",
			//	 "),\n\t\t",
				 " ",
				"'forced' => true",
				"'batch' => true",
				"'filter' => true",
				"'batch' => false",
				"'filter' => false",
				"'inline' => true",
				"'validate' => true",
				"'readonly' => true",
			//	"",
				"'type' => null",
				"'data' => null",
				"[]",
				' '
				  );



			foreach($vars['fields'] as $key=>$val)
			{
				if(isset($val['type']))
				{
					if(($val['type'] === 'dropdown' || $val['type'] === 'method') && empty($val['filter']))
					{
						$vars['fields'][$key]['filter'] = '0';
					}

					if(($val['type'] === 'dropdown' || $val['type'] === 'method') && empty($val['batch']))
					{
						$vars['fields'][$key]['batch'] = '0';
					}

					if($val['type'] == 'image' && empty($val['readParms']))
					{
						$vars['fields'][$key]['readParms'] = 'thumb=80x80'; // provide a thumbnail preview by default.
					}
				}
				if(empty($vars['fields'][$key]['readParms']))
				{
					$vars['fields'][$key]['readParms'] = array();
				}

				if(empty($vars['fields'][$key]['writeParms']))
				{
					$vars['fields'][$key]['writeParms'] = array();
				}


				unset($vars['fields'][$key]['fieldpref']);

			}

			$FIELDS = "array (\n";

			foreach($vars['fields'] as $key=>$val)
			{
				$FIELDS .= "\t\t\t'".str_pad($key."'",25)."=> ".str_replace($srch,$repl,var_export($val,true)).",\n";
			}

			$FIELDS .= "\t\t)";

		//	$FIELDS = var_export($vars['fields'],true);
		//	$FIELDS = str_replace($srch,$repl,var_export($vars['fields'],true));
		return preg_replace("#('([A-Z0-9_]*?LAN[_A-Z0-9]*)')#","$2",$FIELDS); // remove quotations from LANs.

	}

	/**
	 * @param array $post POSTED data from form.
	 * @param string $pluginFolder
	 * @param string $pluginTitle
	 * @return string
	 */
	public function buildAdminUI($post, $pluginFolder, $pluginTitle)
	{

		unset($post['step'], $post['xml'], $post['addons']);

		$tp = e107::getParser();
		
		$text = "\n
// Generated e107 Plugin Admin Area 

require_once('../../class2.php');
if (!getperms('P')) 
{
	e107::redirect('admin');
	exit;
}

// e107::lan('" . $pluginFolder . "',true);


class " . $pluginFolder . "_adminArea extends e_admin_dispatcher
{

	protected \$modes = array(	
	";


		unset($post['newplugin'], $post['mode']);

		foreach($post as $table => $vars) // LOOP Through Tables.
		{
			if(!empty($vars['mode']) && $vars['mode'] != 'exclude')
			{

				$vars['mode'] = $tp->filter($vars['mode']);

				$text .= "
		'" . $vars['mode'] . "'	=> array(
			'controller' 	=> '" . $table . "',
			'path' 			=> null,
			'ui' 			=> '" . str_replace("_ui", "_form_ui", $table) . "',
			'uipath' 		=> null
		),
		
";
			}
		} // END LOOP
		/*
				'cat'		=> array(
					'controller' 	=> 'faq_cat_ui',
					'path' 			=> null,
					'ui' 			=> 'faq_cat_form_ui',
					'uipath' 		=> null
				)
			);
		*/

		$text .= "
	);	
	
	
	protected \$adminMenu = array(
";
		foreach($post as $table => $vars) // LOOP Through Tables.
		{
			if(!empty($vars['mode']) && $vars['mode'] != 'exclude' && !empty($vars['table']))
			{

				$vars['mode'] = $tp->filter($vars['mode']);
				$text .= "
		'" . $vars['mode'] . "/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		'" . $vars['mode'] . "/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
";
			}
		}

		if($post['pluginPrefs'][0]['index'])
		{

			$text .= "			
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),	
";
		}
		$text .= "
		// 'main/div0'      => array('divider'=> true),
		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P'),
		
	);

	protected \$adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected \$menuTitle = '" . vartrue($pluginTitle, $pluginFolder) . "';
}



";


		$tableCount = 1;
		foreach($post as $table => $vars) // LOOP Through Tables.
		{

			if($table == 'pluginPrefs' || $vars['mode'] == 'exclude')
			{
				continue;
			}

			$vars['mode']       = $tp->filter($vars['mode']);
			$vars['pluginName'] = $tp->filter($vars['pluginName']);
			$vars['table']      = !empty($vars['table']) ? $tp->filter($vars['table']) : '';
			$vars['pid']        = $tp->filter($vars['pid']);

			$FIELDS             = $this->buildAdminUIFields($vars);
			$FIELDPREF          = array();

			foreach($vars['fields'] as $k => $v)
			{

				if(isset($v['fieldpref']) && $k != 'checkboxes' && $k != 'options')
				{
					$FIELDPREF[] = "'" . $k . "'";
				}
			}

			$text .=
				"
				
class " . $table . " extends e_admin_ui
{
			
		protected \$pluginTitle		= '" . $pluginTitle . "';
		protected \$pluginName		= '" . $vars['pluginName'] . "';
	//	protected \$eventName		= '" . $vars['pluginName'] . "-" . $vars['table'] . "'; // remove comment to enable event triggers in admin. 		
		protected \$table			= '" . $vars['table'] . "';
		protected \$pid				= '" . $vars['pid'] . "';
		protected \$perPage			= 10; 
		protected \$batchDelete		= true;
		protected \$batchExport     = true;
		protected \$batchCopy		= true;

	//	protected \$sortField		= 'somefield_order';
	//	protected \$sortParent      = 'somefield_parent';
	//	protected \$treePrefix      = 'somefield_title';

	//	protected \$tabs				= array('tab1'=>'Tab 1', 'tab2'=>'Tab 2'); // Use 'tab'=>'tab1'  OR 'tab'=>'tab2' in the \$fields below to enable. 
		
	//	protected \$listQry      	= \"SELECT * FROM `#tableName` WHERE field != '' \"; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.
	
		protected \$listOrder		= '" . $vars['pid'] . " DESC';
	
		protected \$fields 		= " . $FIELDS . ";		
		
		protected \$fieldpref = array(" . implode(", ", $FIELDPREF) . ");
		
";


			if($post['pluginPrefs'] && ($vars['mode'] == 'main'))
			{
				$text .= "
	//	protected \$preftabs        = array('General', 'Other' );
		protected \$prefs = array(\n";

				foreach($post['pluginPrefs'] as $k => $val)
				{
					if(!empty($val['index']))
					{
						$index = $tp->filter($val['index']);
						$type = vartrue($val['type'], 'text');
						$help = str_replace("'", '', vartrue($val['help']));

						$text .= "\t\t\t'" . $index . "'\t\t=> array('title'=> '" . ucfirst($index) . "', 'tab'=>0, 'type'=>'" . $tp->filter($type) . "', 'data' => 'str', 'help'=>'" . $tp->filter($help) . "', 'writeParms' => []),\n";
					}

				}


				$text .= "\t\t); \n\n";

			}


			$text .= "	
		public function init()
		{
			// This code may be removed once plugin development is complete. 
			if(!e107::isInstalled('" . $vars['pluginName'] . "'))
			{
				e107::getMessage()->addWarning(\"This plugin is not yet installed. Saving and loading of preference or table data will fail.\");
			}
			
			// Set drop-down values (if any). 
";

			foreach($vars['fields'] as $k => $v)
			{
				if(isset($v['type']) && ($v['type'] === 'dropdown'))
				{
					$text .= "\t\t\t\$this->fields['" . $k . "']['writeParms']['optArray'] = array('" . $k . "_0','" . $k . "_1', '" . $k . "_2'); // Example Drop-down array. \n";
				}
			}


			$text .= "	
		}
";


$text .= <<<UICODE

		// ------- Customize Create --------
		
		public function beforeCreate(\$new_data,\$old_data)
		{
			return \$new_data;
		}
	
		public function afterCreate(\$new_data, \$old_data, \$id)
		{
			// do something
		}

		public function onCreateError(\$new_data, \$old_data)
		{
			// do something		
		}		
		
		
		// ------- Customize Update --------
		
		public function beforeUpdate(\$new_data, \$old_data, \$id)
		{
			return \$new_data;
		}

		public function afterUpdate(\$new_data, \$old_data, \$id)
		{
			// do something	
		}
		
		public function onUpdateError(\$new_data, \$old_data, \$id)
		{
			// do something		
		}		
		
		// left-panel help menu area. (replaces e_help.php used in old plugins)
		public function renderHelp()
		{
			\$caption = LAN_HELP;
			\$text = 'Some help text';

			return array('caption'=>\$caption,'text'=> \$text);

		}
			
	/*	
		// optional - a custom page.  
		public function customPage()
		{
			if(\$this->getPosted('custom-submit')) // after form is submitted. 
			{
				e107::getMessage()->addSuccess('Changes made: '. \$this->getPosted('example'));
			}

			\$this->addTitle('My Custom Title');


			\$frm = \$this->getUI();
			\$text = \$frm->open('my-form', 'post');

				\$tab1 = "<table class='table table-bordered adminform'>
					<colgroup>
						<col class='col-label'>
						<col class='col-control'>
					</colgroup>
					<tr>
						<td>Label ".\$frm->help('A help tip')."</td>
						<td>".\$frm->text('example', \$this->getPosted('example'), 80, ['size'=>'xlarge'])."</td>
					</tr>
					</table>";

			// Display Tab
			\$text .= \$frm->tabs([
				'general'   => ['caption'=>LAN_GENERAL, 'text' => \$tab1],
			]);

			\$text .= "<div class='buttons-bar text-center'>".\$frm->button('custom-submit', 'submit', 'submit', LAN_CREATE)."</div>";
			\$text .= \$frm->close();

			return \$text;
			
		}
		
UICODE;




			$text .= $this->buildAdminUIBatchFilter($vars['fields'], $table, 'batch');
			$text .= $this->buildAdminUIBatchFilter($vars['fields'], $table, 'filter');

			$text .= "	
		
		
	*/
			
}
				


class " . str_replace("_ui", "_form_ui", $table) . " extends e_admin_form_ui
{
";

			foreach($vars['fields'] as $fld => $val)
			{
				if(varset($val['type']) != 'method')
				{
					continue;
				}

				$text .= "
	
	// Custom Method/Function 
	function " . $fld . "(\$curVal,\$mode)
	{
		\$otherField  = \$this->getController()->getFieldVar('other_field_name');
		 		
		switch(\$mode)
		{
			case 'read': // List Page
				return \$curVal;
			break;
			
			case 'write': // Edit Page
				return \$this->text('" . $fld . "',\$curVal, 255, 'size=large');
			break;
			
			case 'filter':
				return array('customfilter_1' => 'Custom Filter 1', 'customfilter_2' => 'Custom Filter 2');
			break;
			
			case 'batch':
				return array('custombatch_1' => 'Custom Batch 1', 'custombatch_2' => 'Custom Batch 2');
			break;
		}
		
		return null;
	}
";
			}


			foreach($post['pluginPrefs'] as $fld => $val)
			{
				if(varset($val['type']) !== 'method' || empty($val['index']))
				{
					continue;
				}

				$index = $tp->filter($val['index']);

				$text .= "
	
	// Custom Method/Function (pref)
	function " . $index . "(\$curVal,\$mode)
	{

		 		
		switch(\$mode)
		{			
			case 'write': // Edit Page
			
				return \$this->text('" . $index . "',\$curVal, 255, 'size=large');
			break;
			
		}
		
		return null;
	}
";
			}


			$text .= "
}		
		
";

			$tableCount++;

		} // End LOOP.

		$text .= '		
new ' . $pluginFolder . '_adminArea();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

';

		return $text;
	}


}
