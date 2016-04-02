<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin administration area
 *
 */

require_once("../class2.php");
if (!getperms("Z"))
{
	e107::redirect('admin');
	exit;
}

e107::coreLan('plugin', true);

$e_sub_cat = 'plug_manage';

define('PLUGIN_SHOW_REFRESH', FALSE);
define('PLUGIN_SCAN_INTERVAL', !empty($_SERVER['E_DEV']) ? 0 : 360);

global $user_pref;

require_once(e_HANDLER.'plugin_class.php');
require_once(e_HANDLER.'file_class.php');
$plugin = new e107plugin;
$pman = new pluginManager;
define("e_PAGETITLE",ADLAN_98." - ".$pman->pagetitle);

if(e_AJAX_REQUEST) // Ajax 
{
	print_a($_POST);
	print_a($_GET);
	exit; 	
	
}


if(e_AJAX_REQUEST && isset($_GET['action'])) // Ajax 
{
	if($_GET['action'] == 'download')
	{
		$string =  base64_decode($_GET['src']);	
		parse_str($string, $p);
		
	//	print_a($p);
		
	//	$mp = $pman->getMarketplace();
	//	$mp->generateAuthKey($e107SiteUsername, $e107SiteUserpass);
	
	
	
		// Server flush useless. It's ajax ready state 4, we can't flush (sadly) before that (at least not for all browsers) 
		echo "<pre>Connecting...\n"; flush(); // FIXME change the modal default label, default is Loading...
		// download and flush
	//	$mp->download($p['plugin_id'], $p['plugin_mode'], 'plugin');
		
		echo "</pre>"; flush();
	}
	/*$string =  base64_decode($_GET['src']);	
	parse_str($string,$p);
	$remotefile = $p['plugin_url'];
	
	$localfile = md5($remotefile.time()).".zip";
	$status = "Downloading...";
	
	$fl = e107::getFile();
	$fl->setAuthKey($e107SiteUsername,$e107SiteUserpass);
	$fl->download($remotefile,'plugin');*/
	exit;
}

if(isset($_POST['uninstall_cancel']))
{
	header("location:".e_SELF);
	exit;		
}


class pluginmanager_form extends e_form
{
	
	var $plug;
	var $plug_vars;
		
	//FIXME _ there's a problem with calling this. 
	function plugin_website($parms, $value, $id, $attributes)
	{
		return ($plugURL) ? "<a href='{$plugURL}' title='{$plugURL}' >".ADMIN_URL_ICON."</a>" : "";	
		
	}
	
	
	function options($val, $curVal)
	{
		
		$tp = e107::getParser();
		
		$_path = e_PLUGIN.$this->plug['plugin_path'].'/';
		
		$icon_src = (isset($this->plug_vars['plugin_php']) ? e_PLUGIN : $_path).$this->plug_vars['administration']['icon'];
		$plugin_icon = $this->plug_vars['administration']['icon'] ? "<img src='{$icon_src}' alt='' class='icon S32' />" : $tp->toGlyph('e-cat_plugins-32');
   		$conf_file = "#";
		$conf_title = "";
		
		if ($this->plug_vars['administration']['configFile'] && $this->plug['plugin_installflag'] == true)
		{
			$conf_file = e_PLUGIN. $this->plug['plugin_path'].'/'.$this->plug_vars['administration']['configFile'];
			$conf_title = LAN_CONFIGURE.' '.$tp->toHtml($this->plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable");
			$plugin_icon = "<a title='{$conf_title}' href='{$conf_file}' >".$plugin_icon."</a>";
			$plugin_config_icon = "<a class='btn btn-default' title='{$conf_title}' href='{$conf_file}' >".ADMIN_CONFIGURE_ICON."</a>";
		}
				
		$text = "<div class='btn-group'>";
		
		$text .= vartrue($plugin_config_icon);
		
		if ($this->plug_vars['@attributes']['installRequired'])
		{
			
			if ($this->plug['plugin_installflag'])
			{
		  		$text .= ($this->plug['plugin_installflag'] ? "<a class='btn btn-default' href=\"".e_SELF."?uninstall.{$this->plug['plugin_id']}\" title='".EPL_ADLAN_1."'  >".ADMIN_UNINSTALLPLUGIN_ICON."</a>" : "<a class='btn' href=\"".e_SELF."?install.{$this->plug['plugin_id']}\" title='".EPL_ADLAN_0."' >".ADMIN_INSTALLPLUGIN_ICON."</a>");
                           //   $text .= ($this->plug['plugin_installflag'] ? "<button type='button' class='delete' value='no-value' onclick=\"location.href='".e_SELF."?uninstall.{$this->plug['plugin_id']}'\"><span>".EPL_ADLAN_1."</span></button>" : "<button type='button' class='update' value='no-value' onclick=\"location.href='".e_SELF."?install.{$this->plug['plugin_id']}'\"><span>".EPL_ADLAN_0."</span></button>");
				if (e_DEBUG && !vartrue($this->plug_vars['plugin_php']))
				{
			//		$text .= "<br /><br /><input type='button' class='btn btn-default button' onclick=\"location.href='".e_SELF."?refresh.{$this->plug['plugin_id']}'\" title='".'Refresh plugin settings'."' value='".'Refresh plugin settings'."' /> ";
				}
			}
			else
			{
			  //	$text .=  "<input type='button' class='btn' onclick=\"location.href='".e_SELF."?install.{$this->plug['plugin_id']}'\" title='".EPL_ADLAN_0."' value='".EPL_ADLAN_0."' />";
			  //	$text .= "<button type='button' class='update' value='no-value' onclick=\"location.href='".e_SELF."?install.{$this->plug['plugin_id']}'\"><span>".EPL_ADLAN_0."</span></button>";
	           	$text .= "<a class='btn btn-default' href=\"".e_SELF."?install.{$this->plug['plugin_id']}\" title='".EPL_ADLAN_0."' >".ADMIN_INSTALLPLUGIN_ICON."</a>";
			}
			
		}
		else
		{
			if ($this->plug_vars['menuName'])
			{
				$text .= EPL_NOINSTALL.str_replace("..", "", e_PLUGIN.$this->plug['plugin_path'])."/ ".EPL_DIRECTORY;
			}
			else
			{
				$text .= EPL_NOINSTALL_1.str_replace("..", "", e_PLUGIN.$this->plug['plugin_path'])."/ ".EPL_DIRECTORY;
				if($this->plug['plugin_installflag'] == false)
				{					
					e107::getDb()->db_Delete('plugin', "plugin_installflag=0 AND (plugin_path='{$this->plug['plugin_path']}' OR plugin_path='{$this->plug['plugin_path']}/' )  ");
				}
			}
		}

		if ($this->plug['plugin_version'] != $this->plug_vars['@attributes']['version'] && $this->plug['plugin_installflag'])
		{
		  //	$text .= "<br /><input type='button' class='btn' onclick=\"location.href='".e_SELF."?upgrade.{$this->plug['plugin_id']}'\" title='".EPL_UPGRADE." to v".$this->plug_vars['@attributes']['version']."' value='".EPL_UPGRADE."' />";
			$text .= "<a class='btn btn-default' href='".e_SELF."?upgrade.{$this->plug['plugin_id']}' title=\"".EPL_UPGRADE." to v".$this->plug_vars['@attributes']['version']."\" >".ADMIN_UPGRADEPLUGIN_ICON."</a>";
		}

		if ($this->plug['plugin_installflag'] && e_DEBUG == true)
		{
				$text .= "<a class='btn btn-default' href='".e_SELF."?refresh.".$this->plug['plugin_id']."' title='".'Repair plugin settings'."'> ".ADMIN_REPAIRPLUGIN_ICON."</a>";
		}



		$text .="</div>	";
				
		return $text;
	}	


	
}


require_once("auth.php");
$pman->pluginObserver();
$mes = e107::getMessage();
$frm = e107::getForm();

function e_help()
{
	$help_text = str_replace('[x]', (PLUGIN_SCAN_INTERVAL ? PLUGIN_SCAN_INTERVAL / 60 : 0), EPL_ADLAN_228);
	return array(
		'caption'	=> EPL_ADLAN_227,
		'text'		=> $help_text."<p><a class='btn btn-xs btn-mini btn-primary' href='".e_SELF."?refresh'>".EPL_ADLAN_229."</a></p>"
	);
}

require_once("footer.php");
exit;


// FIXME switch to admin UI
class pluginManager{

	var $plugArray;
	var $action;
	var $id;
	var $frm;
	var $fieldpref;
	var $titlearray 		= array();
	var $pagetitle;
	
	/**
	 * Marketplace handler instance
	 * @var e_marketplace
	 */
	var $mp;
		
	protected $pid = 'plugin_id';
	
	protected $fields = array(

		   		"checkboxes"			=> array("title" => "", 'type'=>null, "forced"=>TRUE, "width"=>"3%", 'thclass'=>'center','class'=>'center'),
				"plugin_icon"			=> array("title" => EPL_ADLAN_82, "type"=>"icon", "width" => "5%", "thclass" => "middle center",'class'=>'center', "url" => ""),
				"plugin_name"			=> array("title" => EPL_ADLAN_10, 'forced'=>true, "type"=>"text", "width" => "auto", 'class'=>'left', "thclass" => "middle", "url" => ""),
 				"plugin_version"		=> array("title" => EPL_ADLAN_11, "type"=>"numeric", "width" => "5%", "thclass" => "middle", "url" => ""),
    			"plugin_date"			=> array("title" => "Released ", 	"type"=>"text", "width" => "8%", "thclass" => "middle"),
    			
    			"plugin_folder"			=> array("title" => EPL_ADLAN_64, "type"=>"text", "width" => "10%", "thclass" => "middle"),
				"plugin_category"		=> array("title" => LAN_CATEGORY, "type"=>"text", "width" => "auto", "thclass" => "middle"),
                "plugin_author"			=> array("title" => LAN_AUTHOR, "type"=>"text", "width" => "10%", "thclass" => "middle"),
                "plugin_license"		=> array("title" => "License", 	 'nolist'=>true,	"forced"=>true, "type"=>"text", "width" => "5%", "thclass" => "left"),	
  		//		"plugin_price"			=> array("title" => "Price", 	 'nolist'=>true,	"forced"=>true, "type"=>"text", "width" => "5%", "thclass" => "left"),	
  				"plugin_compatible"		=> array("title" => EPL_ADLAN_13, "type"=>"text", "width" => "5%", "thclass" => "middle"),
				"plugin_description"	=> array("title" => EPL_ADLAN_14, "type"=>"bbarea", "width" => "30%", "thclass" => "middle center",  'readParms' => 'expand=1&truncate=180&bb=1'),
				"plugin_compliant"		=> array("title" => EPL_ADLAN_81, "type"=>"text", "width" => "5%", "thclass" => "middle center", "url" => ""),
		//		"plugin_release"		=> array("title" => EPL_ADLAN_81, "type"=>"text", "width" => "5%", "thclass" => "middle center", "url" => ""),
		//		"plugin_notes"			=> array("title" => EPL_ADLAN_83, "type"=>"url", "width" => "5%", "thclass" => "middle center", "url" => ""),
			
				"options"				=> array("title" => LAN_OPTIONS, 'forced'=>TRUE, 'type'=> 'method', "width" => "15%", "thclass" => "right last", 'class'=>'right'),

	);
	
	

	function __construct()
	{
        global $user_pref,$admin_log;

        $tmp = explode('.', e_QUERY);
	  	$this -> action = ($tmp[0]) ? $tmp[0] : "installed";
		$this -> id = varset($tmp[1]) ? intval($tmp[1]) : "";
		$this -> titlearray = array('installed'=>EPL_ADLAN_22,'avail'=>EPL_ADLAN_23, 'upload'=>EPL_ADLAN_38);
		
		if(isset($_GET['mode']))
		{
			$this->action = $_GET['mode'];
		}

		if($this->action == 'online')
		{
		//	$this->fields["plugin_price"]['nolist'] = false; //  = array("title" => "Price", "forced"=>true, "type"=>"text", "width" => "5%", "thclass" => "middle center");		
			$this->fields["plugin_license"]['nolist'] = false; 
		}

        $keys = array_keys($this -> titlearray);
		$this->pagetitle = (in_array($this->action,$keys)) ? $this -> titlearray[$this->action] : $this -> titlearray['installed'];

/*		if(isset($_POST['uninstall-selected']))
		{
        	foreach($_POST['checkboxes'] as $val)
			{
            	$this -> id = intval($val);
                $this -> pluginUninstall();
			}
      		$this -> action = "installed";
			$this -> pluginRenderList();
			return;

			// Complicated, as each uninstall system is different.
		}*/

    }

	/**
	 * Temporary, e107::getMarketplace() coming soon
	 * @return e_marketplace
	 */
	public function getMarketplace()
	{
		if(null === $this->mp)
		{
			require_once(e_HANDLER.'e_marketplace.php');
			$this->mp = new e_marketplace(); // autodetect the best method
		}
		return $this->mp;
	}



    function pluginObserver()
	{

        global $user_pref,$admin_log;
        
    	if (isset($_POST['upload']))
		{
        	$this -> pluginProcessUpload();
			$this->action = 'avail'; 
		}

        if(isset($_POST['etrigger_ecolumns']))
		{
			$user_pref['admin_pluginmanager_columns'] = $_POST['e-columns'];
			save_prefs('user');
		}

		$user_pref['admin_pluginmanager_columns'] = false;
		
		$this -> fieldpref = (vartrue($user_pref['admin_pluginmanager_columns'])) ? $user_pref['admin_pluginmanager_columns'] : array("plugin_icon","plugin_name","plugin_version","plugin_date","plugin_description","plugin_category","plugin_compatible","plugin_author","plugin_website","plugin_notes");


		foreach($this->fields as $key=>$val)
		{
			if(vartrue($val['forced']) && substr($key,0,6)=='plugin')
			{
				$this->fieldpref[] = $key;	
			}		
		}
		
		if($this->action == 'download')
		{
			$this->pluginDownload();
			return; 	
			
		}
	

        if($this->action == 'avail' || $this->action == 'installed')   // Plugin Check is done during upgrade_routine.
		{
			$this -> pluginCheck();
		}

		if($this->action == "uninstall")
		{
        	$this -> pluginUninstall();
			$this -> pluginCheck(true); // forced
		}
		
		if($this->action == "refresh")
		{
        	$this -> pluginCheck(true); // forced
		}

        if($this->action == "install" || $this->action == "refresh")
		{
        	$this -> pluginInstall();
    		$this -> action = "installed";
		}

		if($this->action == 'create')
		{
			$pc = new pluginBuilder;
			return;
				
		}
		
		if($this->action == 'lans')
		{
			$pc = new pluginLanguage;
			return;
				
		}

		if($this->action == "upgrade")
		{
        	$this -> pluginUpgrade();
      		$this -> action = "installed";
		}

		if($this->action == "refresh")
		{
        	$this -> pluginRefresh();
		}
		if($this->action == "upload")
		{
        	$this -> pluginUpload();
		}
		
		if($this->action == "online")
		{
        	$this -> pluginOnline();
			return;
		}
		
	//	print_a($_POST);

		if(isset($_POST['install-selected']))
		{
        	foreach($_POST['multiselect'] as $val)
			{
            	$this -> id = intval($val);
                $this -> pluginInstall();
			}
      		$this -> action = "installed";
		}

        if($this->action != 'avail' && varset($this->fields['checkboxes']))
		{
		 	unset($this->fields['checkboxes']); //  = FALSE;
		}

		if($this->action !='upload' && $this->action !='uninstall')
		{
			$this -> pluginRenderList();
		}



	}
	
	
	private function compatibilityLabel($val='')
	{
		$badge = (vartrue($val) > 1.9) ? "<span class='label label-warning'>".EPL_ADLAN_88."</span>" : '1.x';
		return $badge;	
	}
	
	
	
	function pluginOnline()
	{
		global $plugin, $e107SiteUsername, $e107SiteUserpass;
		$tp = e107::getParser();
		$frm = e107::getForm();
		
		$caption	= EPL_ADLAN_89;
		
		$e107 = e107::getInstance();
		$xml = e107::getXml();
		$mes = e107::getMessage();
		
	//	$mes->addWarning("Some older plugins may produce unpredictable results.");
		// check for cURL
		if(!function_exists('curl_init'))
		{
			$mes->addWarning(EPL_ADLAN_90);
		}
		
		//TODO use admin_ui including filter capabilities by sending search queries back to the xml script. 
		$from = isset($_GET['frm']) ? intval($_GET['frm']) : 0;
		$srch = preg_replace('/[^\w]/','', vartrue($_GET['srch'])); 
		
	
		$mp = $this->getMarketplace();

		// auth
		$mp->generateAuthKey($e107SiteUsername, $e107SiteUserpass);
		
		// do the request, retrieve and parse data
		$xdata = $mp->call('getList', array(
			'type' => 'plugin', 
			'params' => array('limit' => 10, 'search' => $srch, 'from' => $from)
		));
		$total = $xdata['params']['count'];
	
		// OLD BIT OF CODE ------------------------------->
	/*
	//	$file = SITEURLBASE.e_PLUGIN_ABS."release/release.php";  // temporary testing
		$file = "http://e107.org/feed?type=plugin&frm=".$from."&srch=".$srch."&limit=10";
		
		$xml->setOptArrayTags('plugin'); // make sure 'plugin' tag always returns an array
		$xdata = $xml->loadXMLfile($file,'advanced');

		$total = $xdata['@attributes']['total'];
		
		echo 'file='.$file;
	//	print_a($xdata);
		
		$xdata['data'] = $xdata['plugin'];
		*/
		// OLD BIT OF CODE END ------------------------------->
		
		
// print_a($xdata);
		 
		$c = 1;
		foreach($xdata['data'] as $row)
		{
			//$row = $r['@attributes'];
			
			//	print_a($row);
			
				$badge 		= $this->compatibilityLabel($row['compatibility']);;
				$featured 	= ($row['featured']== 1) ? " <span class='label label-info'>".EPL_ADLAN_91."</span>" : '';
				$price 		= (!empty($row['price'])) ? "<span class='label label-primary'>".$row['price']." ".$row['currency']."</span>" : "<span class='label label-success'>".EPL_ADLAN_93."</span>";
			
				$data[] = array(
					'plugin_id'				=> $row['params']['id'],
					'plugin_mode'			=> $row['params']['mode'],
					'plugin_icon'			=> vartrue($row['icon'],'e-plugins-32'),
					'plugin_name'			=> stripslashes($row['name']),
					'plugin_featured'		=> $featured,
					'plugin_sef'			=> '',
					'plugin_folder'			=> $row['folder'],
					'plugin_date'			=> vartrue($row['date']),
					'plugin_category'		=> vartrue($row['category'], 'n/a'),
					'plugin_author'			=> vartrue($row['author']),
					'plugin_version'		=> $row['version'],
					'plugin_description'	=> nl2br(vartrue($row['description'])),
					'plugin_compatible'		=> $badge,
				
					'plugin_website'		=> vartrue($row['authorUrl']),
					'plugin_url'			=> $row['urlView'],
					'plugin_notes'			=> '',
					'plugin_price'			=> $row['price'],
					'plugin_license'		=> $price
				);	
				
			$c++;
		}

		$fieldList = $this->fields;
		unset($fieldList['checkboxes']);

		$text = "
			<form class='form-search form-inline' action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='get'>
			<div id='admin-ui-list-filter' class='e-search '>".$frm->search('srch', $srch, 'go', $filterName, $filterArray, $filterVal).$frm->hidden('mode','online')."
			</div>
			</form>
			
			<form action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='post'>
				<fieldset class='e-filter' id='core-plugin-list'>
					<legend class='e-hideme'>".$caption."</legend>
					
					
					
					
					
					<table id=core-plugin-list' class='table adminlist table-striped'>
						".$frm->colGroup($fieldList,$this->fieldpref).
						$frm->thead($fieldList,$this->fieldpref)."
						<tbody>
		";	
		
		
	
		
		foreach($data as $key=>$val	)
		{
		//	print_a($val);
			$text .= "<tr>";
						
			foreach($this->fields as $v=>$foo)
			{
				if(!in_array($v,$this->fieldpref) || $v == 'checkboxes')
				{
					continue;	
				}
				
				$_value = $val[$v];
				if($v == 'plugin_name') $_value .= $val['plugin_featured'];
				// echo '<br />v='.$v;
				$text .= "<td style='height: 40px' class='".vartrue($this->fields[$v]['class'],'left')."'>".$frm->renderValue($v, $_value, $this->fields[$v], $key)."</td>\n";
			}
			$text .= "<td class='right'>".$this->options($val)."</td>";
			$text .= "</tr>";		
			
		}
		
		
		$text .= "
						</tbody>
					</table>";
		$text .= "
				</fieldset>
			</form>
		";
		
		$amount = 30;
		
		
		if($total > $amount)
		{
			$parms = $total.",".$amount.",".$from.",".e_SELF.'?mode='.$_GET['mode'].'&amp;frm=[FROM]';
			$text .= "<div class='control-group form-inline input-inline' style='text-align:center;margin-top:10px'>".$tp->parseTemplate("{NEXTPREV=$parms}",TRUE)."</div>";
		}
		
		e107::getRender()->tablerender(ADLAN_98.SEP.$caption, $mes->render(). $text);
	}
	
	
	
	function options($data)
	{
			
	//	print_a($data);
		
		/*
		if(!e107::getFile()->hasAuthKey())
		{
		//	return "<a href='".e_SELF."' class='btn btn-primary e-modal' >Download and Install</a>"; 	
			
		}
		*/
				
		$d = http_build_query($data,false,'&');
		//$url = e_SELF."?src=".base64_encode($d);
	//	$url = e_SELF.'?action=download&amp;src='.base64_encode($d);//$url.'&amp;action=download';
		$id = 'plug_'.$data['plugin_id'];
		//<button type='button' data-target='{$id}' data-loading='".e_IMAGE."/generic/loading_32.gif' class='btn btn-primary e-ajax middle' value='Download and Install' data-src='".$url."' ><span>Download and Install</span></button>
		$modalCaption = (!empty($data['plugin_price'])) ? "Purchase ".$data['plugin_name']." ".$data['plugin_version'] : 'Downloading and Installing '.$data['plugin_name']." ".$data['plugin_version'];

		$url = e_SELF.'?mode=download&amp;src='.base64_encode($d);
		$dicon = '<a title="Download and Install" class="e-modal btn btn-default" href="'.$url.'" rel="external" data-loading="'.e_IMAGE.'/generic/loading_32.gif"  data-cache="false" data-modal-caption="'.$modalCaption.'"  target="_blank" >'.ADMIN_INSTALLPLUGIN_ICON.'</a>';
	
	
		// Temporary Pop-up version. 
	//	$dicon = '<a class="e-modal" href="'.$data['plugin_url'].'" rel="external" data-modal-caption="'.$data['plugin_name']." ".$data['plugin_version'].'"  target="_blank" ><img class="top" src="'.e_IMAGE_ABS.'icons/download_32.png" alt=""  /></a>';
		
	//	$dicon = "<a data-toggle='modal' data-modal-caption=\"Downloading ".$data['plugin_name']." ".$data['plugin_version']."\" href='{$url}' data-cache='false' data-target='#uiModal' title='".$LAN_DOWNLOAD."' ><img class='top' src='".e_IMAGE_ABS."icons/download_32.png' alt=''  /></a> ";
	
		return "<div id='{$id}' class='right' >
		{$dicon}
		</div>";				
	}



	private function pluginDownload()
	{
		define('e_IFRAME', true);
		$frm = e107::getForm();
		$mes = e107::getMessage();
		
	//	print_a($_GET); 	
		
		$string =  base64_decode($_GET['src']);	
		parse_str($string, $data);

		if(!empty($data['plugin_price']))
		{
			e107::getRedirect()->go($data['plugin_url']);
			return true;
		}

		$mp = $this->getMarketplace();
	//	$mp->generateAuthKey($e107SiteUsername, $e107SiteUserpass);
	

		
		// Server flush useless. It's ajax ready state 4, we can't flush (sadly) before that (at least not for all browsers) 
	 	$mes->addSuccess(EPL_ADLAN_94);

		if($mp->download($data['plugin_id'], $data['plugin_mode'], 'plugin'))
		{
			$text = e107::getPlugin()->install($data['plugin_folder']); 

			$mes->addInfo($text); 
			echo $mes->render('default', 'success'); 
		}
		else
		{
			// Unable to continue
			echo $mes->addError(EPL_ADLAN_95)->render('default', 'error');
		}
		
		echo $mes->render('default', 'debug'); 
		return; 
		
		
		
		$text ="<iframe src='".$data['plugin_url']."' style='width:99%; height:500px; border:0px'>Loading...</iframe>";	
	//	print_a($data); 
		$text .= $frm->open('upload-url-form','post');
		
		$text .= "<div class='form-inline' style='padding:20px'>";
		$text .= "<input type='text' name='upload_url' size='255' style='width:70%;height:50px;text-align:center' placeholder='".EPL_ADLAN_96."' />";
		$text .= $frm->admin_button('upload_remote_url',1,'create','Install');
	    $text .= "</div>";
		$text .= "</div>\n\n";
		
		$text .= $frm->close();
		echo $text; 
		
	}
	
	
	// FIXME - move it to plugin handler, similar to install_plugin() routine
	function pluginUninstall()
	{
			$pref = e107::getPref();
			$admin_log = e107::getAdminLog();
			$plugin = e107::getPlugin();
			$tp = e107::getParser();
			$sql = e107::getDb();
			$eplug_folder = '';
			if(!isset($_POST['uninstall_confirm']))
			{	// $id is already an integer
				$this->pluginConfirmUninstall($this->id);
   				return;
			}

			$plug = $plugin->getinfo($this->id);
			$text = '';
			//Uninstall Plugin
			if ($plug['plugin_installflag'] == TRUE )
			{
				$eplug_folder = $plug['plugin_path'];
				$_path = e_PLUGIN.$plug['plugin_path'].'/';

				if(file_exists($_path.'plugin.xml'))
				{
					unset($_POST['uninstall_confirm']);
					$text .= $plugin->install_plugin_xml($this->id, 'uninstall', $_POST); //$_POST must be used.
				}
				else
				{	// Deprecated - plugin uses plugin.php
					include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

					$func = $eplug_folder.'_uninstall';
					if (function_exists($func))
					{
						$text .= call_user_func($func);
					}

					if($_POST['delete_tables'])
					{
						if (is_array($eplug_table_names))
						{
							$result = $plugin->manage_tables('remove', $eplug_table_names);
							if ($result !== TRUE)
							{
								$text .= EPL_ADLAN_27.' <b>'.$mySQLprefix.$result.'</b> - '.EPL_ADLAN_30.'<br />';
							}
							else
							{
								$text .= EPL_ADLAN_28."<br />";
							}
						}
					}
					else
					{
						$text .= EPL_ADLAN_49."<br />";
					}

					if (is_array($eplug_prefs))
					{
						$plugin->manage_prefs('remove', $eplug_prefs);
						$text .= EPL_ADLAN_29."<br />";
					}

					if (is_array($eplug_comment_ids))
					{
						$text .= ($plugin->manage_comments('remove', $eplug_comment_ids)) ? EPL_ADLAN_50."<br />" : "";
					}

					if (is_array($eplug_array_pref))
					{
						foreach($eplug_array_pref as $key => $val)
						{
							$plugin->manage_plugin_prefs('remove', $key, $eplug_folder, $val);
						}
					}

					if ($eplug_menu_name)
					{
						$sql->db_Delete('menus', "menu_name='{$eplug_menu_name}' ");
					}

					if ($eplug_link)
					{
						$plugin->manage_link('remove', $eplug_link_url, $eplug_link_name);
					}

					if ($eplug_userclass)
					{
						$plugin->manage_userclass('remove', $eplug_userclass);
					}

					$sql->update('plugin', "plugin_installflag=0, plugin_version='{$eplug_version}' WHERE plugin_id='{$this->id}' ");
					$plugin->manage_search('remove', $eplug_folder);

					$plugin->manage_notify('remove', $eplug_folder);
					
					// it's done inside install_plugin_xml(), required only here
					if (isset($pref['plug_installed'][$plug['plugin_path']]))
					{
						unset($pref['plug_installed'][$plug['plugin_path']]);
					}
					e107::getConfig('core')->setPref($pref);
					$plugin->rebuildUrlConfig();
					e107::getConfig('core')->save();
				}

				$logInfo = deftrue($plug['plugin_name'],$plug['plugin_name']). " v".$plug['plugin_version']." ({e_PLUGIN}".$plug['plugin_path'].")";
				e107::getLog()->add('PLUGMAN_03', $logInfo, E_LOG_INFORMATIVE, '');
			}

			if(!empty($_POST['delete_files'])  && ($plug['plugin_installflag'] == true))
			{
				if(!empty($eplug_folder))
				{
					$result = e107::getFile()->rmtree(e_PLUGIN.$eplug_folder);
					$text .= ($result ? '<br />'.EPL_ADLAN_86.e_PLUGIN.$eplug_folder : '<br />'.EPL_ADLAN_87.'<br />'.EPL_ADLAN_31.' <b>'.e_PLUGIN.$eplug_folder.'</b> '.EPL_ADLAN_32);
				}
			}
			else
			{
				$text .= '<br />'.EPL_ADLAN_31.' <b>'.e_PLUGIN.$eplug_folder.'</b> '.EPL_ADLAN_32;
			}

			$plugin->save_addon_prefs('update');

			$this->show_message($text, E_MESSAGE_SUCCESS);
		 //	$ns->tablerender(EPL_ADLAN_1.' '.$tp->toHtml($plug['plugin_name'], "", "defs,emotes_off,no_make_clickable"), $text);
			$text = '';
			$this->action = 'installed';
			return;

   }

   function pluginProcessUpload()
   {
			if (!$_POST['ac'] == md5(ADMINPWCHANGE))
			{
				exit;
			}
			
			$fl = e107::getFile();
			$data = $fl->getUploaded(e_TEMP); 
			$mes = e107::getMessage();
			
			if(empty($data[0]['error']))
			{
				if($fl->unzipArchive($data[0]['name'],'plugin'))
				{
					$mes->addSuccess(EPL_ADLAN_43); 
				}
				else 
				{
					$mes->addError(EPL_ADLAN_97);
				}
			}
			
		//	$data = process_uploaded_files(e_TEMP);
		//	print_a($data); 
			
			echo $mes->render(); 
			
			return; 

			// ----------------- Everything below is unused. 
			
			extract($_FILES);
			/* check if e_PLUGIN dir is writable ... */
			if(!is_writable(e_PLUGIN))
			{
				// still not writable - spawn error message 
				e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_39);
			}
			else
			{
				// e_PLUGIN is writable
				require_once(e_HANDLER."upload_handler.php");
				$fileName = $file_userfile['name'][0];
				$fileSize = $file_userfile['size'][0];
				$fileType = $file_userfile['type'][0];

				if(strstr($file_userfile['type'][0], "gzip"))
				{
					$fileType = "tar";
				}
				else if (strstr($file_userfile['type'][0], "zip"))
				{
					$fileType = "zip";
				}
				else
				{
					// not zip or tar - spawn error message
					e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_41);
					return false; 
				}

				if ($fileSize)
				{
					$uploaded = file_upload(e_PLUGIN);
					$archiveName = $uploaded[0]['name'];

					// attempt to unarchive 

					if($fileType == "zip")
					{
						require_once(e_HANDLER."pclzip.lib.php");
						$archive = new PclZip(e_PLUGIN.$archiveName);
						$unarc = ($fileList = $archive -> extract(PCLZIP_OPT_PATH, e_PLUGIN, PCLZIP_OPT_SET_CHMOD, 0666));
					}
					else
					{
						require_once(e_HANDLER."pcltar.lib.php");
						$unarc = ($fileList = PclTarExtract($archiveName, e_PLUGIN));
					}

					if(!$unarc)
					{
						// unarc failed ... 
						if($fileType == "zip")
						{
							$error = EPL_ADLAN_46." '".$archive -> errorName(TRUE)."'";
						}
						else
						{
							$error = EPL_ADLAN_47.PclErrorString().", ".EPL_ADLAN_48.intval(PclErrorCode());
						}
						e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_42." ".$archiveName." ".$error);
						require_once("footer.php");
						exit;
					}

					// ok it looks like the unarc succeeded - continue */

					// get folder name ...  
					
					$folderName = substr($fileList[0]['stored_filename'], 0, (strpos($fileList[0]['stored_filename'], "/")));

					if(file_exists(e_PLUGIN.$folderName."/plugin.php") || file_exists(e_PLUGIN.$folderName."/plugin.xml"))
					{
						/* upload is a plugin */
						e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_43);
					}
					elseif(file_exists(e_PLUGIN.$folderName."/theme.php") || file_exists(e_PLUGIN.$folderName."/theme.xml"))
					{
						/* upload is a menu */
						e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_45);
					}
					else
					{
						/* upload is unlocatable */
						e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_98.' '.$fileList[0]['stored_filename']);
					}

					/* attempt to delete uploaded archive */
					@unlink(e_PLUGIN.$archiveName);
				}
			}
   }


// -----------------------------------------------------------------------------
// TODO FIXME - This needs cleaning: e107::getMessage(), limit the globals, etc. 

   function pluginInstall()
   {
        global $plugin,$admin_log,$eplug_folder;
		$text = $plugin->install_plugin($this->id);
		
		$log = e107::getAdminLog();
			
			
			
		if ($text === FALSE)
		{ // Tidy this up
			$this->show_message(EPL_ADLAN_99, E_MESSAGE_ERROR);
		}
		else
		{
			$plugin->save_addon_prefs('update');
			$info = $plugin->getinfo($this->id);
			 
			$name = deftrue($info['plugin_name'],$info['plugin_name']). " v".$info['plugin_version']. "({e_PLUGIN}".$info['plugin_path'].")";
			 
			$log->log_event('PLUGMAN_01', $name, E_LOG_INFORMATIVE, '');
		
			$this->show_message($text, E_MESSAGE_SUCCESS);
		}

   }


// -----------------------------------------------------------------------------

	function pluginUpgrade()
	{
		$pref 		= e107::getPref();
		$admin_log 	= e107::getAdminLog();
		$plugin 	= e107::getPlugin();

	  	$sql 		= e107::getDb();
   		$mes 		= e107::getMessage(); 
		$plug 		= $plugin->getinfo($this->id);

		$_path = e_PLUGIN.$plug['plugin_path'].'/';
		if(file_exists($_path.'plugin.xml'))
		{
			$plugin->install_plugin_xml($this->id, 'upgrade');
		}
		else
		{
			include(e_PLUGIN.$plug['plugin_path'].'/plugin.php');

			$func = $eplug_folder.'_upgrade';
			if (function_exists($func))
			{
				$text .= call_user_func($func);
			}

			if (is_array($upgrade_alter_tables))
			{
				$result = $plugin->manage_tables('upgrade', $upgrade_alter_tables);
				if (true !== $result)
				{
					//$text .= EPL_ADLAN_9.'<br />';
					$mes->addWarning(EPL_ADLAN_9)
						->addDebug($result);
				}
				else
				{
					$text .= EPL_ADLAN_7."<br />";
				}
			}

			if (is_array($upgrade_add_prefs))
			{
				$plugin->manage_prefs('add', $upgrade_add_prefs);
				$text .= EPL_ADLAN_8.'<br />';
			}

			if (is_array($upgrade_remove_prefs))
			{
				$plugin->manage_prefs('remove', $upgrade_remove_prefs);
			}

			if (is_array($upgrade_add_array_pref))
			{
				foreach($upgrade_add_array_pref as $key => $val)
				{
					$plugin->manage_plugin_prefs('add', $key, $eplug_folder, $val);
				}
			}

			if (is_array($upgrade_remove_array_pref))
			{
				foreach($upgrade_remove_array_pref as $key => $val)
				{
					$plugin->manage_plugin_prefs('remove', $key, $eplug_folder, $val);
				}
			}

			$plugin->manage_search('upgrade', $eplug_folder);
			$plugin->manage_notify('upgrade', $eplug_folder);

			$eplug_addons = $plugin -> getAddons($eplug_folder);

			$info = $plugin->getinfo($this->id);
				 
			$name = deftrue($info['plugin_name'],$info['plugin_name']). " v".$eplug_version. "({e_PLUGIN}".$info['plugin_path'].")";

			e107::getLog()->add('PLUGMAN_02', $name, E_LOG_INFORMATIVE, '');
			$text .= (isset($eplug_upgrade_done)) ? '<br />'.$eplug_upgrade_done : "<br />".LAN_UPGRADE_SUCCESSFUL;
			$sql->update('plugin', "plugin_version ='{$eplug_version}', plugin_addons='{$eplug_addons}' WHERE plugin_id='$this->id' ");
			$pref['plug_installed'][$plug['plugin_path']] = $eplug_version; 			// Update the version
			
			e107::getConfig('core')->setPref($pref);
			$plugin->rebuildUrlConfig();
			e107::getConfig('core')->save();
		}


		$mes->addSuccess($text);
		$plugin->save_addon_prefs('update');

   }


// -----------------------------------------------------------------------------

   function pluginRefresh()
   {
       global $plug;

			$plug = e107::getSingleton('e107plugin')->getinfo($this->id);

			$_path = e_PLUGIN.$plug['plugin_path'].'/';
			if(file_exists($_path.'plugin.xml'))
			{
				// $text .= $plugin->install_plugin_xml($this->id, 'refresh');
				e107::getSingleton('e107plugin')->refresh($plug['plugin_path']);
				e107::getLog()->add('PLUGMAN_04', $this->id.':'.$plug['plugin_path'], E_LOG_INFORMATIVE, '');
			}

    }

// -----------------------------------------------------------------------------

		// Check for new plugins, create entry in plugin table ...
    function pluginCheck($force=false)
	{
		global $plugin;

		if(!PLUGIN_SCAN_INTERVAL)
		{
			$plugin->update_plugins_table('update');
			return;
		}
		
		if((time() > vartrue($_SESSION['nextPluginFolderScan'],0)) || $force == true)
		{
			$plugin->update_plugins_table('update');
		}
		
		$_SESSION['nextPluginFolderScan'] = time() + PLUGIN_SCAN_INTERVAL;
		//echo "TIME = ".$_SESSION['nextPluginFolderScan'];
		
    }
		// ----------------------------------------------------------
		//        render plugin information ...


// -----------------------------------------------------------------------------


    function pluginUpload()
	{
         global $plugin;
		 $frm = e107::getForm();

		//TODO 'install' checkbox in plugin upload form. (as it is for theme upload)

		/* plugin upload form */

			if(!is_writable(e_PLUGIN))
			{
			   	e107::getRender()->tablerender(EPL_ADLAN_40, EPL_ADLAN_44);
			}
			else
			{
			  // Get largest allowable file upload
			  require_once(e_HANDLER.'upload_handler.php');
			  $max_file_size = get_user_max_upload();

			  $text = "
				<form enctype='multipart/form-data' method='post' action='".e_SELF."'>
                <table class='table adminform'>
                	<colgroup>
                		<col class='col-label' />
                		<col class='col-control' />
                	</colgroup>
				<tr>
				<td>".EPL_ADLAN_37."</td>
				<td>
				<input type='hidden' name='MAX_FILE_SIZE' value='{$max_file_size}' />
				<input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
				<input class='tbox' type='file' name='file_userfile[]' size='50' />
				</td>
                </tr>
				</table>

				<div class='center buttons-bar'>";
                $text .= $frm->admin_button('upload', EPL_ADLAN_38, 'submit', EPL_ADLAN_38);

				$text .= "
				</div>

				</form>\n";
			}

         e107::getRender()->tablerender(ADLAN_98.SEP.EPL_ADLAN_38, $text);
	}

// -----------------------------------------------------------------------------

	function pluginRenderList() // Uninstall and Install sorting should be fixed once and for all now !
	{

		global $plugin;
		$frm = e107::getForm();
		$e107 = e107::getInstance();
		$mes = e107::getMessage();

		if($this->action == "" || $this->action == "installed")
		{
			$installed = $plugin->getall(1);
			$caption = EPL_ADLAN_22;
			$pluginRenderPlugin = $this->pluginRenderPlugin($installed);
			$button_mode = "uninstall-selected";
			$button_caption = EPL_ADLAN_85;
			$button_action = "delete";
		}
		if($this->action == "avail")
		{
			$uninstalled = $plugin->getall(0);		
			$caption = EPL_ADLAN_23;
			$pluginRenderPlugin = $this->pluginRenderPlugin($uninstalled);
			$button_mode = "install-selected";
			$button_caption = EPL_ADLAN_84;
			$button_action = "update";
		}

		$text = "
			<form action='".e_SELF."?".e_QUERY."' id='core-plugin-list-form' method='post'>
				<fieldset id='core-plugin-list'>
					<legend class='e-hideme'>".vartrue($caption)."</legend>
					<table class='table adminlist table-striped'>
						".$frm->colGroup($this->fields,$this->fieldpref).
						$frm->thead($this->fields,$this->fieldpref)."
						<tbody>
		";

		if(vartrue($pluginRenderPlugin))
		{
			$text .= $pluginRenderPlugin;
		}
		else
		{
			$text .= "<tr><td class='center' colspan='".count($this->fields)."'>";
 			$text .= str_replace("[x]", "<a href='".e_ADMIN."plugin.php?avail'>".EPL_ADLAN_100."</a>", EPL_ADLAN_101);
			$text .= "</td></tr>";
		}

		$text .= "
						</tbody>
					</table>";

		if($this->action == "avail")
		{
			$text .= "
					<div class='buttons-bar center'>".$frm->admin_button($button_mode, $button_caption, $button_action)."</div>";
		}
		$text .= "
				</fieldset>
			</form>
		";

		e107::getRender()->tablerender(ADLAN_98.SEP.$caption, $mes->render(). $text);
	}


// -----------------------------------------------------------------------------

	function pluginRenderPlugin($pluginList)
	{
			global $plugin; 
			
			if (empty($pluginList)) return '';

			$tp = e107::getParser();
			$frm = e107::getForm();
			
			$pgf = new pluginmanager_form; 

			$text = "";

			foreach($pluginList as $plug)
			{
				e107::loadLanFiles($plug['plugin_path'],'admin');
				
				if($this->action == "avail")
				{
					e107::lan($plug['plugin_path'],'global', true); // Load language files. 
				}
					
				

				$_path = e_PLUGIN.$plug['plugin_path'].'/';

				$plug_vars = false;
				$plugin_config_icon = "";

				if($plugin->parse_plugin($plug['plugin_path']))
				{
					$plug_vars = $plugin->plug_vars;
				}

				if(varset($plug['plugin_category']) == "menu") // Hide "Menu Only" plugins.
				{
					continue;
				}

				if($plug_vars)
				{

					$icon_src = (isset($plug_vars['plugin_php']) ? e_PLUGIN : $_path).$plug_vars['administration']['icon'];
			
                   	$plugin_icon = $plug_vars['administration']['icon'] ? $icon_src : $tp->toGlyph('e-cat_plugins-32');
              
                    
                    $conf_file = "#";
					$conf_title = "";

					if ($plug_vars['administration']['configFile'] && $plug['plugin_installflag'] == true)
					{
						$conf_file = e_PLUGIN.$plug['plugin_path'].'/'.$plug_vars['administration']['configFile'];
						$conf_title = LAN_CONFIGURE.' '.$tp->toHtml($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable");
					//	$plugin_icon = "<a title='{$conf_title}' href='{$conf_file}' >".$plugin_icon."</a>";
						$plugin_config_icon = "<a class='btn btn-default' title='{$conf_title}' href='{$conf_file}' >".ADMIN_CONFIGURE_ICON."</a>";
					}

					$plugEmail = varset($plug_vars['author']['@attributes']['email'],'');
					$plugAuthor = varset($plug_vars['author']['@attributes']['name'],'');
					$plugURL = varset($plug_vars['author']['@attributes']['url'],'');
					$plugDate	= varset($plug_vars['@attributes']['date'],'');
					$compatibility	= varset($plug_vars['@attributes']['compatibility'],'');
					
					$description = varset($plug_vars['description']['@attributes']['lang']) ? $tp->toHTML($plug_vars['description']['@attributes']['lang'], false, "defs,emotes_off, no_make_clickable") : $tp->toHTML($plug_vars['description']['@value'], false, "emotes_off, no_make_clickable") ;
					
                    $plugReadme = "";
					if(varset($plug['plugin_installflag']))
					{
						$plugName = "<a title='{$conf_title}' href='{$conf_file}' >".$tp->toHTML($plug['plugin_name'], false, "defs,emotes_off, no_make_clickable")."</a>";
                    }
                    else
					{
                    	$plugName = $tp->toHTML($plug['plugin_name'], false, "defs,emotes_off, no_make_clickable");
					}
					if(varset($plug_vars['readme']))   // 0.7 plugin.php
					{
                    	$plugReadme = $plug_vars['readme'];
					}
					if(varset($plug_vars['readMe'])) // 0.8 plugin.xml
					{
                    	$plugReadme = $plug_vars['readMe'];
					}

					if(!file_exists($plugin_icon))
					{
						$plugin_icon = 'e-cat_plugins-32'; // e_IMAGE."admin_images/cat_plugins_32.png";
					}

						
					$data = array(
					'plugin_id'				=> $plug['plugin_id'],
					'plugin_icon'			=> $plugin_icon,
					'plugin_name'			=> $plugName,
					'plugin_folder'			=> $plug['plugin_path'],
					'plugin_date'			=> $plugDate,
					'plugin_category'		=> vartrue($plug['plugin_category']),
					'plugin_author'			=> vartrue($plugAuthor), // vartrue($plugEmail) ? "<a href='mailto:".$plugEmail."' title='".$plugEmail."'>".$plugAuthor."</a>" : vartrue($plugAuthor),
					'plugin_version'		=> $plug['plugin_version'],
					'plugin_description'	=> $description,
					'plugin_compatible'		=> $this->compatibilityLabel($plug_vars['@attributes']['compatibility']),
				
					'plugin_website'		=> vartrue($row['authorUrl']),
			//		'plugin_url'			=> vartrue($plugURL), // ; //  ? "<a href='{$plugURL}' title='{$plugURL}' >".ADMIN_URL_ICON."</a>" : "",
					'plugin_notes'			=> ''
					);	


					$pgf->plug_vars = $plug_vars;
					$pgf->plug		= $plug;
					$text 			.= $pgf->renderTableRow($this->fields, $this->fieldpref, $data, 'plugin_id');


/*

					//LEGACY CODE 




					$text .= "<tr>";

					if(varset($this-> fields['checkboxes']))
					{
                 		$rowid = "checkboxes[".$plug['plugin_id']."]";
                		$text .= "<td class='center middle'>".$frm->checkbox($rowid, $plug['plugin_id'])."</td>\n";
					}

				//	$text .= (in_array("plugin_status",$this->fieldpref)) ? "<td class='center'>".$img."</td>" : "";
				
			
                    $text .= (in_array("plugin_icon",$this->fieldpref)) ? "<td class='center middle'>".$plugin_icon."</td>" : "";
                    $text .= (in_array("plugin_name",$this->fieldpref)) ? "<td class='middle'>".$plugName."</td>" : "";
                    $text .= (in_array("plugin_version",$this->fieldpref)) ? "<td class='middle'>".$plug['plugin_version']."</td>" : "";
					$text .= (in_array("plugin_date",$this->fieldpref)) ? "<td class='middle'>".$plugDate."</td>" : "";
					
					$text .= (in_array("plugin_folder",$this->fieldpref)) ? "<td class='middle'>".$plug['plugin_path']."</td>" : "";
					$text .= (in_array("plugin_category",$this->fieldpref)) ? "<td class='middle'>".$plug['plugin_category']."</td>" : "";
                    $text .= (in_array("plugin_author",$this->fieldpref)) ? "<td class='middle'><a href='mailto:".$plugEmail."' title='".$plugEmail."'>".$plugAuthor."</a>&nbsp;</td>" : "";
                    $text .= (in_array("plugin_website",$this->fieldpref)) ? "<td class='center middle'>".($plugURL ? "<a href='{$plugURL}' title='{$plugURL}' >".ADMIN_URL_ICON."</a>" : "")."</td>" : "";

                   	$text .= (in_array("plugin_compatible",$this->fieldpref)) ? "<td class='center middle'>".$this->compatibilityLabel($plug_vars['@attributes']['compatibility'])."</td>" : "";
					
					$text .= (in_array("plugin_description",$this->fieldpref)) ? "<td class='middle'>".$description."</td>" : "";
                 	$text .= (in_array("plugin_compliant",$this->fieldpref)) ? "<td class='center middle'>".((varset($plug_vars['compliant']) || varsettrue($plug_vars['@attributes']['xhtmlcompliant'])) ? ADMIN_TRUE_ICON : "&nbsp;")."</td>" : "";
	     			$text .= (in_array("plugin_notes",$this->fieldpref)) ? "<td class='center middle'>".($plugReadme ? "<a href='".e_PLUGIN.$plug['plugin_path']."/".$plugReadme."' title='".$plugReadme."'>".ADMIN_INFO_ICON."</a>" : "&nbsp;")."</td>" : "";
			


				


                	// Plugin options Column --------------

   					$text .= "<td class='options center middle'>
   					<div class='btn-group'>".$plugin_config_icon;


						if ($plug_vars['@attributes']['installRequired'])
						{
							if ($plug['plugin_installflag'])
							{
						  		$text .= ($plug['plugin_installflag'] ? "<a class='btn' href=\"".e_SELF."?uninstall.{$plug['plugin_id']}\" title='".EPL_ADLAN_1."'  >".ADMIN_UNINSTALLPLUGIN_ICON."</a>" : "<a class='btn' href=\"".e_SELF."?install.{$plug['plugin_id']}\" title='".EPL_ADLAN_0."' >".ADMIN_INSTALLPLUGIN_ICON."</a>");

                             //   $text .= ($plug['plugin_installflag'] ? "<button type='button' class='delete' value='no-value' onclick=\"location.href='".e_SELF."?uninstall.{$plug['plugin_id']}'\"><span>".EPL_ADLAN_1."</span></button>" : "<button type='button' class='update' value='no-value' onclick=\"location.href='".e_SELF."?install.{$plug['plugin_id']}'\"><span>".EPL_ADLAN_0."</span></button>");
								if (PLUGIN_SHOW_REFRESH && !varsettrue($plug_vars['plugin_php']))
								{
									$text .= "<br /><br /><input type='button' class='btn button' onclick=\"location.href='".e_SELF."?refresh.{$plug['plugin_id']}'\" title='".'Refresh plugin settings'."' value='".'Refresh plugin settings'."' /> ";
								}
							}
							else
							{
							  //	$text .=  "<input type='button' class='btn' onclick=\"location.href='".e_SELF."?install.{$plug['plugin_id']}'\" title='".EPL_ADLAN_0."' value='".EPL_ADLAN_0."' />";
							  //	$text .= "<button type='button' class='update' value='no-value' onclick=\"location.href='".e_SELF."?install.{$plug['plugin_id']}'\"><span>".EPL_ADLAN_0."</span></button>";
                            	$text .= "<a class='btn' href=\"".e_SELF."?install.{$plug['plugin_id']}\" title='".EPL_ADLAN_0."' >".ADMIN_INSTALLPLUGIN_ICON."</a>";
							}
						}
						else
						{
							if ($plug_vars['menuName'])
							{
								$text .= EPL_NOINSTALL.str_replace("..", "", e_PLUGIN.$plug['plugin_path'])."/ ".EPL_DIRECTORY;
							}
							else
							{
								$text .= EPL_NOINSTALL_1.str_replace("..", "", e_PLUGIN.$plug['plugin_path'])."/ ".EPL_DIRECTORY;
								if($plug['plugin_installflag'] == false)
								{					
									e107::getDb()->db_Delete('plugin', "plugin_installflag=0 AND (plugin_path='{$plug['plugin_path']}' OR plugin_path='{$plug['plugin_path']}/' )  ");
								}
							}
						}

						if ($plug['plugin_version'] != $plug_vars['@attributes']['version'] && $plug['plugin_installflag'])
						{
						  //	$text .= "<br /><input type='button' class='btn' onclick=\"location.href='".e_SELF."?upgrade.{$plug['plugin_id']}'\" title='".EPL_UPGRADE." to v".$plug_vars['@attributes']['version']."' value='".EPL_UPGRADE."' />";
							$text .= "<a class='btn' href='".e_SELF."?upgrade.{$plug['plugin_id']}' title=\"".EPL_UPGRADE." to v".$plug_vars['@attributes']['version']."\" >".ADMIN_UPGRADEPLUGIN_ICON."</a>";
						}

					$text .="</div></td>";
              //      $text .= "</tr>";




*/








				}
			}
			return $text;
	}


// -----------------------------------------------------------------------------



		function pluginConfirmUninstall()
		{
			global $plugin;

			$frm 	= e107::getForm();
			$tp 	= e107::getParser();
			$mes 	= e107::getMessage();

			$plug = $plugin->getinfo($this->id);

			if ($plug['plugin_installflag'] == true )
			{
				if($plugin->parse_plugin($plug['plugin_path']))
				{
					$plug_vars = $plugin->plug_vars;
				}
				else
				{
					return FALSE;
				}
			}
			else
			{
				return FALSE;
			}
			$userclasses = '';
			$eufields = '';
			if (isset($plug_vars['userClasses']))
			{
				if (isset($plug_vars['userclass']['@attributes']))
				{
					$plug_vars['userclass'][0]['@attributes'] = $plug_vars['userclass']['@attributes'];
					unset($plug_vars['userclass']['@attributes']);
				}
				$spacer = '';
				foreach ($plug_vars['userClasses']['class'] as $uc)
				{
					$userclasses .= $spacer.$uc['@attributes']['name'].' - '.$uc['@attributes']['description'];
					$spacer = '<br />';
				}
			}
			if (isset($plug_vars['extendedFields']))
			{
				if (isset($plug_vars['extendedFields']['@attributes']))
				{
					$plug_vars['extendedField'][0]['@attributes'] = $plug_vars['extendedField']['@attributes'];
					unset($plug_vars['extendedField']['@attributes']);
				}
				$spacer = '';
				foreach ($plug_vars['extendedFields']['field'] as $eu)
				{
					$eufields .= $spacer.'plugin_'.$plug_vars['folder'].'_'.$eu['@attributes']['name'];
					$spacer = '<br />';
				}
			}

			if(is_writable(e_PLUGIN.$plug['plugin_path']))
			{
				$del_text = $frm->select('delete_files','yesno',0);
			}
			else
			{
				$del_text = "
				".EPL_ADLAN_53."
				<input type='hidden' name='delete_files' value='0' />
				";
			}

			$text = "
			<form action='".e_SELF."?".e_QUERY."' method='post'>
			<fieldset id='core-plugin-confirmUninstall'>
			<legend>".EPL_ADLAN_54." ".$tp->toHtml($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable")."</legend>
            <table class='table adminform'>
            	<colgroup>
            		<col class='col-label' />
            		<col class='col-control' />
            	</colgroup>
 			<tr>
				<td>".EPL_ADLAN_55."</td>
				<td>".LAN_YES."</td>
			</tr>";

			$opts = array();

			$opts['delete_tables'] = array(
					'label'			=> EPL_ADLAN_57,
					'helpText'		=> EPL_ADLAN_58,
					'itemList'		=> array(1=>LAN_YES,0=>LAN_NO),
					'itemDefault' 	=> 1
			);

			if ($userclasses)
			{
				$opts['delete_userclasses'] = array(
					'label'			=> EPL_ADLAN_78,
					'preview'		=> $userclasses,
					'helpText'		=> EPL_ADLAN_79,
					'itemList'		=> array(1=>LAN_YES,0=>LAN_NO),
					'itemDefault' 	=> 1
				);
			}

			if ($eufields)
			{
				$opts['delete_xfields'] = array(
					'label'			=> EPL_ADLAN_80,
					'preview'		=> $eufields,
					'helpText'		=> EPL_ADLAN_79,
					'itemList'		=> array(1=>LAN_YES,0=>LAN_NO),
					'itemDefault' 	=> 0
				);
			}

			$med = e107::getMedia();
			$icons = $med->listIcons(e_PLUGIN.$plug['plugin_path']);

			if(count($icons)>0)
			{
				foreach($icons as $key=>$val)
				{
					$iconText .= "<img src='".$tp->replaceConstants($val)."' alt='' />";
				}

				$opts['delete_ipool'] = array(
					'label'			=>'Remove icons from Media-Manager',
					'preview'		=> $iconText,
					'helpText'		=> EPL_ADLAN_79,
					'itemList'		=> array(1=>LAN_YES,0=>LAN_NO),
					'itemDefault' 	=> 1
				);
			}

			if(is_readable(e_PLUGIN.$plug['plugin_path']."/".$plug['plugin_path']."_setup.php"))
			{
				include_once(e_PLUGIN.$plug['plugin_path']."/".$plug['plugin_path']."_setup.php");


				$mes->add("Loading ".e_PLUGIN.$plug['plugin_path']."/".$plug['plugin_path']."_setup.php", E_MESSAGE_DEBUG);

				$class_name = $plug['plugin_path']."_setup";

				if(class_exists($class_name))
				{
					$obj = new $class_name;
					if(method_exists($obj,'uninstall_options'))
					{
						$arr = call_user_func(array($obj,'uninstall_options'), $this);
						foreach($arr as $key=>$val)
						{
							$newkey = $plug['plugin_path']."_".$key;
							$opts[$newkey] = $val;
						}
					}
				}
			}

			foreach($opts as $key=>$val)
			{
				$text .= "<tr>\n<td class='top'>".$tp->toHTML($val['label'],FALSE,'TITLE');
				$text .= varset($val['preview']) ? "<div class='indent'>".$val['preview']."</div>" : "";
				$text .= "</td>\n<td>".$frm->select($key,$val['itemList'],$val['itemDefault']);
				$text .= varset($val['helpText']) ? "<div class='field-help'>".$val['helpText']."</div>" : "";
				$text .= "</td>\n</tr>\n";
			}


			$text .="<tr>
			<td>".EPL_ADLAN_59."</td>
			<td>{$del_text}
			<div class='field-help'>".EPL_ADLAN_60."</div>
			</td>
			</tr>
			</table>
			<div class='buttons-bar center'>";
			
			$text .= $frm->admin_button('uninstall_confirm',EPL_ADLAN_3,'submit');
			$text .= $frm->admin_button('uninstall_cancel',EPL_ADLAN_62,'cancel');

			/*
			$text .= "<input class='btn' type='submit' name='uninstall_confirm' value=\"".EPL_ADLAN_3."\" />&nbsp;&nbsp;
			<input class='btn' type='submit' name='uninstall_cancel' value='".EPL_ADLAN_62."' onclick=\"location.href='".e_SELF."'; return false;\"/>";
			*/
             //   $frm->admin_button($name, $value, $action = 'submit', $label = '', $options = array());

			$text .= "</div>
			</fieldset>
			</form>
			";
			e107::getRender()->tablerender(EPL_ADLAN_63.SEP.$tp->toHtml($plug_vars['@attributes']['name'], "", "defs,emotes_off, no_make_clickable"),$mes->render(). $text);

		}

        function show_message($message, $type = E_MESSAGE_INFO, $session = false)
		{
		// ##### Display comfort ---------
			$mes = e107::getMessage();
			$mes->add($message, $type, $session);
		}

        function pluginMenuOptions()
		{
		   //	$e107 = &e107::getInstance();

				$var['installed']['text'] = EPL_ADLAN_22;
				$var['installed']['link'] = e_SELF;

				$var['avail']['text'] = EPL_ADLAN_23;
				$var['avail']['link'] = e_SELF."?avail";

				
				$var['online']['text'] = EPL_ADLAN_220;
				$var['online']['link'] = e_SELF."?mode=online";
				
				
				if(E107_DEBUG_LEVEL > 0)
				{	
					$var['upload']['text'] = EPL_ADLAN_38;
					$var['upload']['link'] = e_SELF."?mode=upload";
				}

				$var['create']['text'] = EPL_ADLAN_114;
				$var['create']['link'] = e_SELF."?mode=create";
				
				
				
				

				$keys = array_keys($var);

				$action = (in_array($this->action,$keys)) ? $this->action : "installed";
				
				if($this->action == 'lans')
				{
					$action = 'create';
				}
					

				e107::getNav()->admin(ADLAN_98, $action, $var);
		}



		

} // end of Class.



function plugin_adminmenu()
{
	global $pman;
	$pman -> pluginMenuOptions();
}



class pluginLanguage
{
	
	private $scriptFiles 	= array();
	private $lanFiles 		= array(); 
	
	private $lanDefs 		= array();
	private $scriptDefs 	= array(); 
	
	private $lanDefsData 	= array();
	private $scriptDefsData = array(); 
	
	private $unused			= array();
	private $unsure			= array();

	private $excludeLans 	= array('CORE_LC', 'CORE_LC2', 'e_LAN', 'e_LANGUAGE', 'e_LANGUAGEDIR', 'LAN', 'LANGUAGE');
	
	private $useSimilar		= false; 
	
	
	function __construct()
		{
		
			if(vartrue($_GET['newplugin']) && $_GET['step']==2)
			{
				return $this->step2($_GET['newplugin']);	
			}
			
		
		
			// return $this->step1();
		}



	
	
		function step2($path)
		{
			$this->plugin = $path; 
			
			$fl = e107::getFile();
			
			$files = $fl->get_files(e_PLUGIN.$path.'/languages',null,null,3);	
			$files2 = $fl->get_files(e_PLUGIN.$path,'\.php|\.sc|\.bb|\.xml','languages',3);	
			
			$this->scanLanFile(e_LANGUAGEDIR."English/English.php");
			$this->scanLanFile(e_LANGUAGEDIR."English/admin/lan_admin.php");		
			
			foreach($files as $v)
			{
				if(strpos($v['path'],'English')!==false OR strpos($v['fname'],'English')!==false)
				{
					$path = $v['path'].$v['fname'];
					$this->lanFiles[] = $path;
					
					$this->scanLanFile($path);	
				}
			}
				
			foreach($files2 as $v)
			{
				$path = $v['path'].$v['fname'];
				$this->scriptFiles[] = 	$path;
				$this->scanScriptFile($path);
			}
				
		
			$this->renderResults(); 

			
		}
		
		
		function findSimilar($data)
		{
			$sim = array(); 
			
			foreach($this->lanDefsData as $k=>$v)
			{
				if(empty($v['value']))
				{
					continue; 	
				}
				
				if($this->useSimilar == true)
				{
					similar_text($v['value'], $data['value'], $percentSimilar);
				}
				else
				{
					$percentSimilar = 0; 	
				}
				
				if((($v['value'] == $data['value'] || $percentSimilar > 89) && $data['file'] != $v['file']))
				{
					if(strpos($v['lan'],'LAN')===false) // Defined constants that don't contain 'LAN'. 
					{
						$v['status'] = 2; 
					}
					else
					{
						$v['status'] = (in_array($v['lan'],$this->used)) ? 1 : 0; 	
					}
					
					$sim[] = $v; 
				
				}	
			}	
			
			
			
			return $sim; 	
			
		}
		
		
		function renderSimilar($data,$mode='')
		{
			
			$sim = $this->findSimilar($data); 
			
			
			if(empty($sim) || ($mode == 'script' && count($sim) < 2))
			{
				return; //  ADMIN_TRUE_ICON; 	
			}
			
			$text = "<table class='table table-striped table-bordered'>
			";
			
			foreach($sim as $k=>$val)
			{
				$text .= "<tr>
				<td style='width:30%'>".$this->shortPath($val['file'])."</td>
				<td style='width:45%'>".$val['lan']."<br /><small>".$val['value']."</small></td>
				<td style='width:25%'>".$this->renderStatus($val['status'])."</td>
				</tr>";	
				
			}
			
			$text .= "</table>";
			return $text;
			
		}
		
		function renderFilesList($list)
		{
			$l= array(); 
			foreach($list as $v)
			{
				$l[] = $this->shortPath($v,'script');
					
				
			}	
			
			if(!empty($l))
			{
				return implode("<br />",$l);	
			}
			
			
		}
		
		function renderStatus($val,$mode='lan')
		{
			$diz = array(
				'lan'		=> array(0 => 'Unused by '.$this->plugin, 1=>'Used by '.$this->plugin, 2=>'Unsure'),
				'script'	=> array(0=> 'Missing from Language Files', 1=>'Found in Language Files', 3=>"Generic")
			);
			
			
			
			if($val ==1)
			{
				return "<span class='label label-success'>".$diz[$mode][$val]."</span>";		
			}
			
			if($val == 2)
			{
				return "<span class='label label-warning'>".$diz[$mode][$val]."</span>";			
			}
			
			return "<span class='label label-important label-danger'>".$diz[$mode][$val]."</span>";
		}
		
		function shortPath($path,$mode='lan')
		{
			
			if($path == e_LANGUAGEDIR.'English/English.php')
			{
				return "<i>Core Frontend Language File</i>";	
			}
			
			if($path == e_LANGUAGEDIR.'English/admin/lan_admin.php')
			{
				return "<i>Core Admin-area Language File</i>";	
			}
			
			if($mode == 'script')
			{
				return str_replace(e_PLUGIN.$this->plugin.'/','',$path); 		
			}
			else
			{
			
				$text = str_replace(e_PLUGIN.$this->plugin.'/languages/','',$path); 
				
				if(strpos($path,'_front.php')===false && strpos($path,'_admin.php')===false && strpos($path,'_global.php')===false && strpos($path,'_menu.php')===false && strpos($path,'_notify.php')===false && strpos($path,'_search.php')===false)
				{
					return "<span class='text-error e-tip' title='File name should be either English_front.php, English_admin.php or English_global.php'>".$text."</span>";	
				} 
				
				return $text;
				
			}
				
		}
		

		function renderTable($array,$mode)
		{
			if(empty($array))
			{
				return "<div class='alert alert-info alert-block'>No Matches</div>";
			}
			
			$text2 = '';
			
			if($mode == 'unsure')
			{
				$text2 .= "<div class='alert alert-info alert-block'>LAN items in this list have been named incorrectly. They should include 'LAN' in their name. eg. LAN_".strtoupper($this->plugin)."_001</div>";	
				
			}
			
			$text2 .= "<table class='table table-striped  table-bordered'>
			<tr>
			<th>LAN</th>
			<th>File</th>
			<th>Value</th>
			<th>Duplicate or Similar Value</th>
			</tr>
			";
			
			foreach($array as $k=>$v)
			{
				$text2 .= "<tr>
					<td style='width:5%'>".$v."</td>
					<td>".$this->shortPath($this->lanDefsData[$k]['file'])."</td>
					<td style='width:20%'>".$this->lanDefsData[$k]['value']."</td>
					<td>".$this->renderSimilar($this->lanDefsData[$k])."</td>
					</tr>";	
				
			}
			
			
			$text2 .= "</table>";	
			
			return $text2;
		}

		function renderScriptTable()
		{
			
		//	return print_a($this->scriptDefsData,true);
			
			$text2 = "<table class='table table-striped table-bordered'>
			<tr>
			<th>id</th>
			<th>File</th>
			<th>Detected LAN</th>
			<th>LAN Value</th>
			<th class='right'>Found on Line</th>
			<th style='width:10%'>Status</th>
			<th>Duplicates / Possible Substitions</th>
			</tr>
			";
			
			foreach($this->scriptDefsData as $k=>$v)
			{
				$status = in_array($v['lan'],$this->lanDefs) ? 1 : 0;
			//	$lan = $v['lan'];
			//	$v['value'] = $this->lanDefsRaw[$lan];
			//	$sim = $this->findSimilar($v);
				
				$text2 .= "<tr>
					<td style='width:5%'>".$k."</td>
					<td>".$this->shortPath($v['file'],'script')."</td>
					<td >".$v['lan']."</td>
					<td ><small>".$this->lanDefsRaw[$v['lan']]."</small></td>
					<td class='right'>".$v['line']."</td>
					<td>".$this->renderStatus($status,'script')."</td>
					<td>".$this->renderSimilar($v,'script')."</td> 
					</tr>";	
				
			}
			
			
			$text2 .= "</table>";	
			
			return $text2;	
			
		}

		
		function renderResults()
		{
			$frm = e107::getForm();
			$ns = e107::getRender();
			
			$this->unused = array_diff($this->lanDefs,$this->scriptDefs); 
				
			$this->used = array_intersect($this->lanDefs,$this->scriptDefs); 
				
			foreach($this->unused as $k=>$v)
			{
				if(strpos($v,'LAN')===false)
				{
					unset($this->unused[$k]);
					$this->unsure[$k] = $v;
				}
				
				if(strpos($this->lanDefsData[$k]['file'],$this->plugin) === false || in_array($v,$this->excludeLans))
				{
					unset($this->unused[$k]);
					unset($this->unsure[$k]);
				}
					
		
			}	

//			print_a($this->scriptData); 
			
			$used =  $this->renderTable($this->used, 'used'); 
			$unused =  $this->renderTable($this->unused,'unused'); 
			$unsure =  $this->renderTable($this->unsure,'unsure'); 
			
			
			// echo $text2;
			$tabs = array (
				0	=> array('caption'=>EPL_ADLAN_222, 'text'=> $this->renderScriptTable()),
				1 => array('caption'=>EPL_ADLAN_223, 'text'=>$used),
				2 => array('caption'=>EPL_ADLAN_224, 'text'=>$unused),
				3 => array('caption'=>EPL_ADLAN_225, 'text'=>$unsure),
				

			);
		
		
			
			$ns->tablerender(ADLAN_98.SEP.EPL_ADLAN_114.SEP.EPL_ADLAN_221.SEP.$this->plugin, $frm->tabs($tabs));
				
		}
					
				
			
		
		
		
		function scanScriptFile($path)
		{
			$lines = file($path, FILE_IGNORE_NEW_LINES);  
			
			foreach($lines as $ln=>$row)
			{
				$row = trim($row); 
				if(substr($row,0,2) == '/*')
				{
				//	$skip =true; ;
						
				}	
				if(substr($row,0,2) == '*/')
				{
				//	$skip =false;
				//	continue; 	
				}	
				
				if(empty($row) || $skip == true || substr($row,0,5) == '<?php' || substr($row,0,2) == '?>' || substr($row,0,2)=='//')
				{
					continue; 	
				}
				
				if(preg_match_all("/([\w_]*LAN[\w_]*)/", $row, $match))
				{
					foreach($match[1] as $lan)
					{
						if(!in_array($lan,$this->excludeLans))
						{
							$this->scriptDefs[] = $lan;
							$this->scriptDefsData[] = array('file'=>$path, 'line'=>$ln, 'lan'=>$lan, 'value'=>$this->lanDefsRaw[$lan]); 
						//	$this->scriptData[$path][$ln] = $row; 
						}
					}
				}	
			}
			
	
		}	
					
			
		function scanLanFile($path)
		{
			
			
			$data = file_get_contents($path); 
			
			if(preg_match_all('/(\/\*[\s\S]*?\*\/)/i',$data, $multiComment))
			{
				$data = str_replace($multiComment[1],'',$data);	// strip multi-line comments. 	
			}
				
			
			$type = basename($path); 
			
			if(preg_match_all('/^\s*?define\s*?\(\s*?(\'|\")([\w]+)(\'|\")\s*?,\s*?(\'|\")([\s\S]*?)\s*?(\'|\")\s*?\)\s*?;/im',$data,$matches))
			{
				$def = $matches[2];
				$values = $matches[5];	
		
				foreach($def as $k=>$d)
				{
					if($d == 'e_PAGETITLE' || $d == 'PAGE_NAME' || $d =='CORE_LC' && $d =='CORE_LC2')
					{
							continue; 
					}
					
					$retloc[$type][$d]= $values[$k];
					$this->lanDefs[] = $d;
					$this->lanDefsData[] = array('file'=>$path, 'lan'=>$d, 'value'=>$values[$k]); 
					$this->lanDefsRaw[$d] = $values[$k];
				}	
			}
			
		//print_a($this->lanDefsData); 
			return; 
		}				
						
					
				
			
	
	
}







/**
 * Plugin Admin Generator by CaMer0n. //TODO - Added dummy template and shortcode creation, plus e_search, e_cron, e_xxxxx etc. 
 */
class pluginBuilder
{
	
		var $fields = array();
		var $table = '';
		var $pluginName = '';
		var $special = array();
		var $tableCount = 0;
		var $tableList = array();
		var $createFiles = false; 
	
		function __construct()
		{
			$this->special['checkboxes'] =  array('title'=> '','type' => null, 'data' => null,	 'width'=>'5%', 'thclass' =>'center', 'forced'=> TRUE,  'class'=>'center', 'toggle' => 'e-multiselect', 'fieldpref'=>true);
			$this->special['options'] = array( 'title'=> 'LAN_OPTIONS', 'type' => null, 'data' => null, 'width' => '10%',	'thclass' => 'center last', 'class' => 'center last', 'forced'=>TRUE, 'fieldpref'=>true);		
			
			if(vartrue($_GET['newplugin']))
			{
				$this->pluginName = $_GET['newplugin'];
			}
			
			if(vartrue($_GET['createFiles']))
			{
				$this->createFiles	= true; 
			}
				
			
			if(vartrue($_POST['step']) == 3)
			{
		
				$this->step3();	
				
				
				return;
			}
			
			if(vartrue($_GET['newplugin']) && $_GET['step']==2)
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
			$info .= "<li>".str_replace('[x]', e_PLUGIN, EPL_ADLAN_103)."</li>";
			$info .= "<li>".EPL_ADLAN_104."</li>";
			$info .= "<li>".EPL_ADLAN_105."</li>";
			$info .= "<li>".EPL_ADLAN_106."</li>";
			$info .= "</ul>";

			$mes->addInfo($tp->toHtml($info,true));
			
			$text = $frm->open('createPlugin','get');
			$text .= "<table class='table adminform'>
						<colgroup>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
				<tr>
					<td>".EPL_ADLAN_107."</td>
					<td><div class='input-append form-inline'>".$frm->open('createPlugin','get',e_SELF."?mode=create").$frm->select("newplugin",$newDir).$frm->admin_button('step', 2,'other','Go')."</div> ".$frm->checkbox('createFiles',1,1,'Create Files').$frm->close()."</td>
				</tr>
				
				<tr>
					<td>".EPL_ADLAN_108."</td>
					<td><div class='input-append form-inline'>".$frm->open('checkPluginLangs','get',e_SELF."?mode=lans").$frm->select("newplugin",$lanDir).$frm->admin_button('step', 2,'other','Go')."</div> ".$frm->close()."</td>
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
			
			$ns->tablerender(ADLAN_98.SEP.EPL_ADLAN_114, $mes->render() . $text);
			
			
			
		//	$var['lans']['text'] = EPL_ADLAN_226;
		//		$var['lans']['link'] = e_SELF."?mode=lans";
			
			
		}


		function enterMysql()
		{
			
			$frm = e107::getForm();
			return "<div>".$frm->textarea('mysql','', 10,80)."</div>";	
			
		}




		function step2()
		{
			
			require_once(e_HANDLER."db_verify_class.php");
			$dv = new db_verify;
			
			$frm = e107::getForm();
			$ns = e107::getRender();
			$mes = e107::getMessage();
			
			$newplug = $_GET['newplugin'];
			$this->pluginName = $newplug;
			
		
			
		//	$data = e107::getXml()->loadXMLfile(e_PLUGIN.'links_page/plugin.xml', 'advanced');
		//	print_a($data);
		//	echo "<pre>".var_export($data,true)."</pre>";
			
			$sqlFile = e_PLUGIN.$newplug."/".$newplug."_sql.php";
			
			$ret = array();
			
			if(file_exists($sqlFile))
			{		
				$data = file_get_contents($sqlFile);
				$ret =  $dv->getTables($data);
			}
		
			$text = $frm->open('newplugin-step3','post', e_SELF.'?mode=create&newplugin='.$newplug.'&createFiles='.$this->createFiles.'&step=3');
			
			$text .= "<ul class='nav nav-tabs'>\n";
			$text .= "<li class='active'><a data-toggle='tab' href='#xml'>".EPL_ADLAN_109."</a></li>";
			
			$this->tableCount = count($ret['tables']);
			
			foreach($ret['tables'] as $key=>$table)
			{
				$text .= "<li><a data-toggle='tab'  href='#".$table."'>Table: ".$table."</a></li>";
				$this->tableList[] = $table;
			}
			$text .= "<li><a data-toggle='tab'  href='#preferences'>".LAN_PREFS."</a></li>";
			
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
			
			if(empty($ret['tables']))
			{
				$text .= $frm->hidden($this->pluginName.'_ui[mode]','main');
				$text .= $frm->hidden($this->pluginName.'_ui[pluginName]', $this->pluginName);
			}

			$text .= "</div>";
			
			$text .= "
			<div class='buttons-bar center'>
			".$frm->hidden('newplugin', $this->pluginName)."
			".$frm->admin_button('step', 3,'other', LAN_GENERATE)."
			</div>";
			
			$text .= $frm->close();
			
			$mes->addInfo(EPL_ADLAN_112);

			$mes->addInfo(EPL_ADLAN_113);
		
			
			$ns->tablerender(ADLAN_98.SEP.EPL_ADLAN_114.SEP.EPL_ADLAN_115, $mes->render() . $text);
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
						
			
			for ($i=0; $i < 10; $i++) 
			{ 		
				$text .= "<div class='form-inline'>".
				$frm->text("pluginPrefs[".$i."][index]", '',40,'placeholder='.EPL_ADLAN_129)." ".
				$frm->text("pluginPrefs[".$i."][value]", '',40,'placeholder='.EPL_ADLAN_130)." ".
				$frm->select("pluginPrefs[".$i."][type]", $options, '', 'class=null', EPL_ADLAN_131).
				"</div>";		
			}
			
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
				'keywords' 		=> array('one','two'),
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
						$red->redirect(e_REQUEST_URL,true);
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
					"author-name"				=> varset($p['author']['@attributes']['name']),
					"author-url"				=> varset($p['author']['@attributes']['url']),
					"description-description"	=> varset($p['description']),
					"summary-summary"			=> varset($p['summary'], $p['description']),
					"category-category"			=> varset($p['category']),
					"keywords-one"				=> varset($p['keywords']['word'][0]),
					"keywords-two"				=> varset($p['keywords']['word'][1]),
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
					$text .= "<div class='{$size}'>".$this->xmlInput($name, $key."-". $type, vartrue($defaults[$nm]))."</div>";	
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
			
			$size 		= 30;
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
					$required 	= false;
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
					$pattern	= "^[\d]{1,2}\.[\d]{1,2}$";
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
					$size 		= 100;
					$placeholder= " ";
					$pattern	= "[A-Za-z \.0-9]*";
					$xsize		= 'block-level';
				break;	
				
				case 'keywords-one':
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
					$pattern	= "[A-Za-z \.0-9]*";
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

			$req = ($required == true) ? "&required=1" : "";	
			$placeholder = (varset($placeholder)) ? $placeholder : $type;
			$pat = ($pattern) ? "&pattern=".$pattern : "";
			$sz = ($xsize) ? "&size=".$xsize : "";
			
			switch ($type) 
			{
				case 'date':
					$text = $frm->datepicker($name, time(), 'format=yyyy-mm-dd'.$req . $sz);		
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
				
					$text = $frm->select($name, $options, $default,'required=1&class=null', true);	
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
			
			foreach($data as $key=>$val)
			{
				$key = strtoupper(str_replace("-","_",$key));
				$newArray[$key] = $val;	
				
			}
			
			$newArray['DESCRIPTION_DESCRIPTION'] = strip_tags($tp->toHtml($newArray['DESCRIPTION_DESCRIPTION'],true));
			
			foreach($_POST['pluginPrefs'] as $val)
			{
				if(vartrue($val['index']))
				{
					$id = $val['index'];
					$plugPref[$id] = $val['value'];		
				}	
			}
			
		//	print_a($_POST['pluginPrefs']);
			
			if(count($plugPref))
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
	</keywords>
	<category>{CATEGORY_CATEGORY}</category>
	<copyright>{COPYRIGHT_COPYRIGHT}</copyright>
	<adminLinks>
		<link url="admin_config.php" description="{ADMINLINKS_DESCRIPTION}" icon="" iconSmall="" icon128="" primary="true" >LAN_CONFIGURE</link>
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
			
			
			if($this->createFiles == true)
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
				"main"=>EPL_ADLAN_157,
				"cat"=>EPL_ADLAN_158,
				"other1"=>EPL_ADLAN_159,
				"other2"=>EPL_ADLAN_160,
				"other3"=>EPL_ADLAN_161,
				"other4"=>EPL_ADLAN_162,
				'exclude'=>EPL_ADLAN_163,
			);
			
		//	echo "TABLE COUNT= ".$this->tableCount ;
			
			
			$this->table = $table."_ui";
			
			$c=0;
			foreach($modes as $id=>$md)
			{
				$tbl = $this->tableList[$c];
				$defaultMode[$tbl] = $id;	
				$c++;
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
							<th class='center e-tip' title='".EPL_ADLAN_177."'>".EPL_ADLAN_172."</th>
							<th class='center e-tip' title='".EPL_ADLAN_178."'>".EPL_ADLAN_173."</th>
							<th>".EPL_ADLAN_174."</th>
							<th>".EPL_ADLAN_175."</th>
							<th>".EPL_ADLAN_176."</th>
						</tr>
						</thead>
						<tbody>
						";
						
			foreach($fieldArray as $name=>$val)
			{
				list($tmp,$nameDef) = explode("_",$name,2);
				// 'faq_question', 'faq_answer', 'faq_parent', 'faq_datestamp'
				$text .= "<tr>
					<td>".$name."</td>
					<td>".$frm->text($this->table."[fields][".$name."][title]", $this->guess($name, $val,'title'),35, 'required=1')."</td>
					<td>".$this->fieldType($name, $val)."</td>
					<td>".$this->fieldData($name, $val)."</td>
					<td>".$frm->text($this->table."[fields][".$name."][width]", $this->guess($name, $val,'width'), 4, 'size=mini')."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][batch]", true, $this->guess($name, $val,'batch'))."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][filter]", true, $this->guess($name, $val,'filter'))."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][inline]", true, $this->guess($name, $val,'inline'))."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][validate]", true)."</td>
					<td class='center'>".$frm->checkbox($this->table."[fields][".$name."][fieldpref]", true, $this->guess($name, $val,'fieldpref'))."</td>
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
					
				
			
		
		function fieldType($name, $val)
		{
			$type = strtolower($val['type']);
			$frm = e107::getForm();
			
			if(strtolower($val['default']) == "auto_increment")
			{
				$key = $this->table."[pid]";
				return "Primary Id".$frm->hidden($key, $name );	// 
			}
			
			switch ($type) 
			{
				case 'date':
				case 'datetime':
					$array = array(
					'text'		=> EPL_ADLAN_179,
					"hidden"	=> EPL_ADLAN_180
					);
				break;
			
				case 'int':
				case 'tinyint':
				case 'bigint':
				case 'smallint':
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
					$array = array(
					"number"	=> EPL_ADLAN_189,
					"dropdown"	=> EPL_ADLAN_190,
					"method"	=> EPL_ADLAN_191,
					"hidden"	=> EPL_ADLAN_192,
					);	
				break;
				
				case 'varchar':
				case 'tinytext':
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
				
				case 'text':
				case 'mediumtext':
				case 'longtext':
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
			}
			
		//	asort($array);
			
			$fname = $this->table."[fields][".$name."][type]";
			return $frm->select($fname, $array, $this->guess($name, $val),'required=1&class=null', true);
			
		}

		// Guess Default Field Type based on name of field. 
		function guess($data, $val='',$mode = 'type')
		{
			$tmp = explode("_",$data);	
			
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
							'mediumblob','longblob','tinytext','mediumtext','longtext','text','date','varchar','char');
			
			
			if(in_array(strtolower($type),$strings))
			{
				$value = 'str';	
			}	
			else 
			{
				$value = 'int';
			}
			
			
			$fname = $this->table."[fields][".$name."][data]";
			
			return $frm->hidden($fname, $value). "<a href='#' class='e-tip' title='{$type}' >".$value."</a>" ;
			
		}




// ******************************** CODE GENERATION AREA *************************************************

		function step3()
		{
			
			$pluginTitle = $_POST['xml']['main-name'] ;
			
			if($_POST['xml'])
			{
				$xmlText =	$this->createXml($_POST['xml']);
			}
					
			
			
			
			
			unset($_POST['step'],$_POST['xml']);
		$thePlugin = $_POST['newplugin'];

$text = "\n
// Generated e107 Plugin Admin Area 

require_once('../../class2.php');
if (!getperms('P')) 
{
	e107::redirect('admin');
	exit;
}



class ".$thePlugin."_adminArea extends e_admin_dispatcher
{

	protected \$modes = array(	
	";
	

	unset($_POST['newplugin']);

	
			foreach($_POST as $table => $vars) // LOOP Through Tables. 
			{
				if(vartrue($vars['mode']) && $vars['mode'] != 'exclude')
				{
	$text .= "
		'".$vars['mode']."'	=> array(
			'controller' 	=> '".$table."',
			'path' 			=> null,
			'ui' 			=> '".str_replace("_ui", "_form_ui", $table)."',
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
			foreach($_POST as $table => $vars) // LOOP Through Tables. 
			{
				if(vartrue($vars['mode']) && $vars['mode'] != 'exclude' && !empty($vars['table']))
				{
$text .= "
		'".$vars['mode']."/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		'".$vars['mode']."/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
";
}
			}
			
if($_POST['pluginPrefs'][0]['index'])
{
				
$text .= "			
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),	
";
}
$text .= "
		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	);

	protected \$adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected \$menuTitle = '".vartrue($vars['pluginName'], $pluginTitle)."';
}



";			
			// print_a($_POST);

			
			$srch = array(
				
				"\n",
				"),",
				"    ",
				"'batch' => '1'",
				"'filter' => '1'",
				"'inline' => '1'",
				"'validate' => '1'",
				", 'fieldpref' => '1'",
				"'type' => ''",
				"'data' => ''"
			 );
			 
			$repl = array(
				
				 "",
				 "),\n\t\t",
				 " ",
				"'batch' => true",
				"'filter' => true",
				"'inline' => true",
				"'validate' => true",
				"",
				"'type' => null",
				"'data' => null"
				  );
			
	
			
			 
			$tableCount = 1;
			foreach($_POST as $table => $vars) // LOOP Through Tables. 
			{



				if($table == 'pluginPrefs' || $vars['mode'] == 'exclude')
				{
					continue;
				}
				
				
				foreach($vars['fields'] as $key=>$val)
				{
					if($val['type'] == 'image' && empty($val['readParms']))
					{	
						$vars['fields'][$key]['readParms'] = 'thumb=80x80'; // provide a thumbnail preview by default. 
					}	
				}
						

				$FIELDS = str_replace($srch,$repl,var_export($vars['fields'],true));
				$FIELDS = preg_replace("#('([A-Z0-9_]*?LAN[_A-Z0-9]*)')#","$2",$FIELDS); // remove quotations from LANs. 
				$FIELDPREF = array();
				
				foreach($vars['fields'] as $k=>$v)
				{
										
					if(isset($v['fieldpref']) && $k != 'checkboxes' && $k !='options')
					{
						$FIELDPREF[] = "'".$k."'";
					}							
				}
				
$text .= 
"
				
class ".$table." extends e_admin_ui
{
			
		protected \$pluginTitle		= '".$pluginTitle."';
		protected \$pluginName		= '".$vars['pluginName']."';
	//	protected \$eventName		= '".$vars['pluginName']."-".$vars['table']."'; // remove comment to enable event triggers in admin. 		
		protected \$table			= '".$vars['table']."';
		protected \$pid				= '".$vars['pid']."';
		protected \$perPage			= 10; 
		protected \$batchDelete		= true;
	//	protected \$batchCopy		= true;		
	//	protected \$sortField		= 'somefield_order';
	//	protected \$orderStep		= 10;
	//	protected \$tabs				= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the \$fields below to enable. 
		
	//	protected \$listQry      	= \"SELECT * FROM `#tableName` WHERE field != '' \"; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.
	
		protected \$listOrder		= '".$vars['pid']." DESC';
	
		protected \$fields 		= ".$FIELDS.";		
		
		protected \$fieldpref = array(".implode(", ",$FIELDPREF).");
		
";


if($_POST['pluginPrefs'] && ($vars['mode']=='main'))
{
	$text .= "
	//	protected \$preftabs        = array('General', 'Other' );
		protected \$prefs = array(\n";
		
		foreach($_POST['pluginPrefs'] as $k=>$val)
		{
			if(vartrue($val['index']))
			{
				$index = $val['index'];
				$type = vartrue($val['type'],'text');
				
				$text .= "\t\t\t'".$index."'\t\t=> array('title'=> '".ucfirst($index)."', 'tab'=>0, 'type'=>'".$type."', 'data' => 'str', 'help'=>'Help Text goes here'),\n";
			}	
	
		}
		
		
		$text .= "\t\t); \n\n";
				
}
				
			

$text .= "	
		public function init()
		{
			// Set drop-down values (if any). 
";
			
		foreach($vars['fields'] as $k=>$v)
		{
			if($v['type'] == 'dropdown')
			{
				$text .= "\t\t\t\$this->fields['".$k."']['writeParms']['optArray'] = array('".$k."_0','".$k."_1', '".$k."_2'); // Example Drop-down array. \n";
			}
		}
					
				
				
			
			
$text .= "	
		}

		
		// ------- Customize Create --------
		
		public function beforeCreate(\$new_data)
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
		
			
	/*	
		// optional - a custom page.  
		public function customPage()
		{
			\$text = 'Hello World!';
			return \$text;
			
		}
	*/
			
}
				


class ".str_replace("_ui", "_form_ui", $table)." extends e_admin_form_ui
{
";

foreach($vars['fields'] as $fld=>$val)
{
	if(varset($val['type']) != 'method')
	{
		continue;	
	}	
	
$text .= "
	
	// Custom Method/Function 
	function ".$fld."(\$curVal,\$mode)
	{
		\$frm = e107::getForm();		
		 		
		switch(\$mode)
		{
			case 'read': // List Page
				return \$curVal;
			break;
			
			case 'write': // Edit Page
				return \$frm->text('".$fld."',\$curVal, 255, 'size=large');
			break;
			
			case 'filter':
			case 'batch':
				return  array();
			break;
		}
	}
";
}

$text .= "
}		
		
";			
						
	 		$tableCount++;	
					
			} // End LOOP. 
	
$text .= '		
new '.$thePlugin.'_adminArea();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;

';

// ******************************** END GENERATION AREA *************************************************	
					
			$ns = e107::getRender();
			$mes = e107::getMessage();
			
			$generatedFile = e_PLUGIN.$thePlugin."/admin_config.php";
			
			$startPHP = chr(60)."?php";		
			$endPHP =  "?>";
			
			if($this->createFiles == true)
			{
				if(file_put_contents($generatedFile, $startPHP .$text . $endPHP))
				{
					$message = str_replace("[x]", "<a href='".$generatedFile."'>".EPL_ADLAN_216."</a>", EPL_ADLAN_217);
					$mes->addSuccess($message);
				}	
				else 
				{
					$mes->addError(str_replace('[x]', $generatedFile, EPL_ADLAN_218));
				}
			}
			else
			{
				$mes->addInfo(EPL_ADLAN_219);
			}
			
			echo $mes->render();
			
			$ns->tablerender(ADLAN_98.SEP.EPL_ADLAN_114.SEP." plugin.xml", "<pre style='font-size:80%'>".$xmlText."</pre>");
	
			
			$ns->tablerender("admin_config.php", "<pre style='font-size:80%'>".$text."</pre>");
			
		//	
			return;
	
		}
}

?>
