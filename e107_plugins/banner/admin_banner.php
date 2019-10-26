<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Banner Administration - Handles the display and sequencing of banners on web pages, including counting impressions
 *
*/

require_once('../../class2.php');
if (!getperms('D') && !getperms('P'))
{
	e107::redirect('admin');
	exit;
}

$e_sub_cat = 'banner';

e107::lan('banner');
e107::lan('banner',true);

e107::css('inline', "

.banner-image .tab-content { padding-top:15px;}


");

e107::js('footer-inline','

	$("#banner-campaign-sel").on("change", function() {

		vr = $(this).val();
		if(vr == "_new_")
		{
			$("#banner-campaign").show("slow");
		}
		else
		{
			$("#banner-campaign").hide("slow");
		}

	});




');


class banner_admin extends e_admin_dispatcher
{

	protected $modes = array(	
	
		'main'	=> array(
			'controller' 	=> 'banner_ui',
			'path' 			=> null,
			'ui' 			=> 'banner_form_ui',
			'uipath' 		=> null
		),


	);	
	
	
	protected $adminMenu = array(

		'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		'main/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
	//	'main/menu'			=> array('caption'=> BNRLAN_36, 'perm' => 'P'), //Done in Menu manager #2096
	//	'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),	

		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'Banners';
}





class banner_ui extends e_admin_ui
{
			
		protected $pluginTitle		= LAN_PLUGIN_BANNER_NAME;
		protected $pluginName		= 'banner';
		protected $table			= 'banner';
		protected $pid				= 'banner_id';
		protected $perPage			= 10; 
		protected $batchDelete		= true;
	//	protected $batchCopy		= true;		
	//	protected $sortField		= 'somefield_order';
	//	protected $orderStep		= 10;
		protected $tabs				= array(LAN_BASIC, LAN_ADVANCED); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.
		
	//	protected $listQry      	= "SELECT * FROM `#tableName` WHERE field != '' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.
	
		protected $listOrder		= 'banner_id DESC';
	
		protected $fields 		= array (
		  'checkboxes'				=>   array ( 'title' => '', 		'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'banner_id' 				=>   array ( 'title' => LAN_ID, 	'type' => null, 'data' => 'int', 'width' => '2%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'banner_campaign' 		=>   array ( 'title' => BNRLAN_11, 	'type' => 'method', 'data' => 'str', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => array( 'tdClassRight'=>'form-inline'), 'class' => 'left', 'thclass' => 'left',  ),

		  'banner_clientname'		=>   array ( 'title' => BANNERLAN_22, 'type' => 'method', 'tab'=>1, 'data' => 'str', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'banner_clientlogin' 		=>   array ( 'title' => BNRLAN_12, 'type' => 'method',  'tab'=>1, 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'banner_clientpassword' 	=>   array ( 'title' => LAN_PASSWORD, 'type' => 'text',  'tab'=>1,'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => 'strength=1&password=1&required=0&generate=1&nomask=1', 'class' => 'center', 'thclass' => 'center',  ),
		  'banner_image' 			=>   array ( 'title' => LAN_IMAGE, 	'type' => 'method', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => 'thumb=0x50&legacyPath={e_IMAGE}banners', 'writeParms' => 'media=banner&w=600&legacyPath={e_IMAGE}banners', 'class' => 'left', 'thclass' => 'left',  ),
		  'banner_clickurl' 		=>   array ( 'title' => BNRLAN_15, 	'type' => 'text', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => 'size=xxlarge&required=1', 'class' => 'left', 'thclass' => 'left',  ),
		  'banner_impurchased' 		=>   array ( 'title' => BNRLAN_16, 	'type' => 'number', 'data' => 'int', 'width' => 'auto', 'inline' => true, 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center', 'help'=>'0 = unlimited' ),
		  'banner_tooltip' 		    =>   array ( 'title' => LAN_TOOLTIP, 	'type' => 'text', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => array('size'=>'xxlarge'), 'class' => 'center', 'thclass' => 'center',  ),

		  'banner_description' 		=>   array ( 'title' => LAN_DESCRIPTION, 	'type' => 'textarea', 'data' => 'str', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'banner_keywords' 		=>   array ( 'title' => LAN_KEYWORDS, 	'type' => 'tags', 'data' => 'str', 'width' => 'auto', 'inline' => true, 'help' => 'When news or pages are loaded, this will limit banner result to matching keywords. Use with caution.', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),

		  'banner_startdate' 		=>   array ( 'title' => LAN_START, 	'type' => 'datestamp',  'tab'=>1,'data' => 'int', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'banner_enddate' 			=>   array ( 'title' => LAN_END, 	'type' => 'datestamp',  'tab'=>1, 'data' => 'int', 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		  'banner_active' 			=>   array ( 'title' => LAN_VISIBILITY, 'type' => 'userclass', 'data' => 'int', 'width' => 'auto', 'filter' => true, 'batch'=>true, 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'banner_clicks' 			=>   array ( 'title' => BANNERLAN_24, 		'type' => 'number', 'noedit'=>true, 'readonly'=>2, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),

		  'click_percentage' 		=>   array ( 'title' => BANNERLAN_25, 	'type' => 'method', 'noedit'=>true, 'data' => false, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),

		  'banner_impressions' 		=>   array ( 'title' => BANNERLAN_26, 	'type' => 'method', 'noedit'=>true, 'data' => 'int', 'width' => '12%',  'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'banner_ip' 				=>   array ( 'title' => LAN_IP, 		'type' => 'hidden', 'noedit'=>true, 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'options' 				=>   array ( 'title' => LAN_OPTIONS, 		'type' => null, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);
		
		protected $fieldpref = array('banner_id', 'banner_campaign', 'banner_image', 'banner_clickurl', 'banner_clicks', 'banner_active', 'click_percentage', 'banner_impressions' );
		
		/*
		protected $prefs = array(
			'banner_caption'		=> array('title'=> 'Banner_caption', 'type'=>'text', 'data' => 'string','help'=>'Help Text goes here'),
			'banner_campaign'		=> array('title'=> 'Banner_campaign', 'type'=>'method', 'data' => 'string','help'=>'Help Text goes here'),
			'banner_amount'			=> array('title'=> 'Banner_amount', 'type'=>'number', 'data' => 'string','help'=>'Help Text goes here'),
			'banner_rendertype'		=> array('title'=> 'Banner_rendertype', 'type'=>'text', 'data' => 'string','help'=>'Help Text goes here'),		); 
	*/
	
		public function init()
		{

			if (!empty($_POST['update_menu']))
			{
				$this->menuPageSave();
			}
		}

		
		// ------- Customize Create --------
		
		public function beforeCreate($new_data)
		{
		//	e107::getMessage()->addDebug(print_a($new_data,true)); 
			
			if(!empty($new_data['banner_clientname_sel']))
			{
				$new_data['banner_clientname'] = $new_data['banner_clientname_sel'];
					
			}

			if(!empty($new_data['banner_campaign_sel']) && $new_data['banner_campaign_sel'] != '_new_')
			{
				$new_data['banner_campaign'] = $new_data['banner_campaign_sel'];
					
			}

			if(!empty($new_data['banner_image_remote']))
			{
				$new_data['banner_image'] = $new_data['banner_image_remote'];
			}
			
			return $new_data;
		}
	
		public function afterCreate($new_data, $old_data, $id)
		{
			// do something
		}

		public function onCreateError($new_data, $old_data)
		{
			// do something
			exit;
		}		
		
		
		// ------- Customize Update --------
		
		public function beforeUpdate($new_data, $old_data, $id)
		{
			//	e107::getMessage()->addDebug(print_a($new_data,true)); 
				
			if(!empty($new_data['banner_clientname_sel']))
			{
				$new_data['banner_clientname'] = $new_data['banner_clientname_sel'];
					
			}
			
			if(!empty($new_data['banner_campaign_sel']))
			{
				$new_data['banner_campaign'] = $new_data['banner_campaign_sel'];
			}

			if(!empty($new_data['banner_image_remote']))
			{
				$new_data['banner_image'] = $new_data['banner_image_remote'];
			}
				
			return $new_data;
		}

		public function afterUpdate($new_data, $old_data, $id)
		{
			// do something	
		}
		
		public function onUpdateError($new_data, $old_data, $id)
		{
			// do something		
		}		
		
		private function menuPageSave()
		{
			$temp = array(); 
			$tp = e107::getParser();
			$log = e107::getAdminLog();
			$menu_pref = e107::getConfig('menu')->getPref('');
			
			$temp['banner_caption']		= $tp->toDB($_POST['banner_caption']);
			$temp['banner_amount']		= intval($_POST['banner_amount']);
			$temp['banner_rendertype']	= intval($_POST['banner_rendertype']);
		
			if (isset($_POST['multiaction_cat_active']))
			{
				$cat = implode('|', $tp->toDB($_POST['multiaction_cat_active']));
				$temp['banner_campaign'] = $cat;
			}
			
			
			if ($log->logArrayDiffs($temp,$menu_pref,'BANNER_01'))
			{
				$menuPref = e107::getConfig('menu');
				//e107::getConfig('menu')->setPref('', $menu_pref);
				//e107::getConfig('menu')->save(false, true, false);
				foreach ($temp as $k => $v)
				{
					$menuPref->setPref($k, $v);
				}
				
				$menuPref->save(false, true, false);
				e107::getMessage()->addSuccess(LAN_SAVED);
				
				$menu_pref = e107::getConfig('menu')->getPref('');
				//banners_adminlog('01', $menu_pref['banner_caption'].'[!br!]'.$menu_pref['banner_amount'].', '.$menu_pref['banner_rendertype'].'[!br!]'.$menu_pref['banner_campaign']);
			}	
			
			
			
		}
			
	
		public function menuPage()
		{

			return e107::getMessage()->addInfo("The menu is now configured within the menu-manager.")->render();


			$ns = e107::getRender();
			$sql = e107::getDb();
			$menu_pref = e107::getConfig('menu')->getPref('');
			$frm = e107::getForm();
			$mes = e107::getMessage();
				
			$in_catname = array();		// Notice removal
			$all_catname = array();
			
			$array_cat_in = explode("|", $menu_pref['banner_campaign']);
			
			if (!$menu_pref['banner_caption'])
			{
				$menu_pref['banner_caption'] = BNRLAN_38;
			}
			
			$category_total = $sql->select("banner", "DISTINCT(banner_campaign) as banner_campaign", "ORDER BY banner_campaign", "mode=no_where");
			
			while ($banner_row = $sql -> fetch())
			{
				$all_catname[] = $banner_row['banner_campaign'];
				if (in_array($banner_row['banner_campaign'], $array_cat_in))
				{
					$in_catname[] = $banner_row['banner_campaign'];
				}
			}
						
			$text = "
				<form method='post' action='".e_REQUEST_URI."' id='menu_conf_form'>
					<fieldset id='core-banner-menu'>
						<legend class='e-hideme'>".BNRLAN_36."</legend>
						<table class='table adminform'>
							<colgroup span='2'>
								<col class='col-label' />
								<col class='col-control' />
							</colgroup>
							<tbody>
								<tr>
									<td>".LAN_CAPTION."</td>
									<td>".$frm->text('banner_caption', $menu_pref['banner_caption'],255,'size=xxlarge')."</td>
								</tr>
								<tr>
									<td>".BNRLAN_39."</td>
									<td>
			";
			if($all_catname)
			{
				
				foreach($all_catname as $name)
				{
					$text .= "
							<div class='field-spacer'>
							".$frm->checkbox('multiaction_cat_active[]', $name, in_array($name, $in_catname), $name)."
							</div>
							";
				}
					
				$text .= "
						<div class='field-spacer control-group form-group'>
						".$frm->admin_button('check_all', 'jstarget:multiaction_cat_active', 'checkall', LAN_CHECKALL)."
						".$frm->admin_button('uncheck_all','jstarget:multiaction_cat_active', 'checkall', LAN_UNCHECKALL)."
						</div>
						";
			}
			else
			{
				$text .= BNRLAN_40;
			}
			
			
			$renderTypes = array(BNRLAN_48,'1 - '.BNRLAN_45,'2 - '.BNRLAN_46);
			
				$renderTypes[3] = "3 - ".BNRLAN_47; //TODO 
			
			
			$text .= "
			
										</td>
									</tr>
									<tr>
										<td>".BNRLAN_41."</td>
										<td>".$frm->number('banner_amount', $menu_pref['banner_amount'], 3)."<span class='field-help'>".BNRLAN_42."</span></td>
									</tr>
									<tr>
										<td>".BNRLAN_43."</td>
										<td>".$frm->select('banner_rendertype', $renderTypes, $menu_pref['banner_rendertype'],'size=xxlarge')."</td>
									</tr>
								</tbody>
							</table>
							<div class='buttons-bar center'>".
								$frm->admin_button('update_menu','no-value','update', LAN_UPDATE)."
							</div>
						</fieldset>
					</form>
				";
			
				return $mes->render().$text; 
			
			//	$ns->tablerender(LAN_PLUGIN_BANNER_NAME.SEP.BNRLAN_36, $mes->render() . $text);
		}

		public function renderHelp()
		{
			$help_text = str_replace("[br]", "<br />", BNRLAN_HELP_02);
			return array('caption' => LAN_HELP, 'text' => $help_text);
		}
			
}
				


class banner_form_ui extends e_admin_form_ui
{

	private $campaigns = array();
	private $clients = array();
	private $logins = array();
	private $passwords = array(); 

	function init()
	{
		$sql = e107::getDb();
		if ($sql->select("banner"))
		{

			$this->campaigns['_new_'] =  "(".LAN_ADD.")";


			while ($banner_row = $sql->fetch())
			{
				if (strpos($banner_row['banner_campaign'], "^") !== FALSE) 
				{
					$campaignsplit = explode("^", $banner_row['banner_campaign']);
					$banner_row['banner_campaign'] = $campaignsplit[0];
				}
		
				if ($banner_row['banner_campaign']) 
				{
					$this->campaigns[$banner_row['banner_campaign']] = $banner_row['banner_campaign'];
				}
				
				if ($banner_row['banner_clientname']) 
				{
					$this->clients[$banner_row['banner_clientname']] = $banner_row['banner_clientname'];
				}
		
				if ($banner_row['banner_clientlogin']) 
				{
					$this->logins[] = $banner_row['banner_clientlogin'];
				}
				
				if ($banner_row['banner_clientpassword']) 
				{
					$this->passwords[] = $banner_row['banner_clientpassword'];
				}
			}
		}	
		
		
	}


	function banner_image($curVal,$mode)
	{
		$frm = e107::getForm();

		switch($mode)
		{
			case 'read': // List Page
				return e107::getParser()->toImage($curVal, array('h'=>100, 'legacy'=>'{e_IMAGE}banners'));
			break;

			case 'write': // Edit Page

				$opts =   'media=banner&w=600&legacyPath={e_IMAGE}banners';

				if(strpos($curVal,'http') === 0)
				{
					$val1 = null;
					$val2 = $curVal;
				}
				else
				{
					$val1 = $curVal;
					$val2 = null;
				}

				$tab1 = $this->imagepicker('banner_image',$val1, null, $opts);


				$tab2 = "<p>". $this->text('banner_image_remote',$val2, 255, array('size'=>'xxlarge', 'placeholder'=>'eg. http://some-website.com/banner-image.jpg', 'title'=>'This will override any local image you have set.'))."</p>";

				if(!empty($val2))
				{
					$tab2 .= e107::getParser()->toImage($val2);
				}

				$tabText = array(
					'local' => array('caption'=>BNRLAN_50, 'text'=>$tab1),
					'remote' => array('caption'=>BNRLAN_51, 'text'=>$tab2),
				);

				return "<div class='banner-image'>".$this->tabs($tabText)."</div>";
			//	return $frm->text('banner_clientname',$curVal);
			break;

			case 'filter':
			case 'batch':
				return  $this->clients;
			break;
		}
	}




















	
	// Custom Method/Function 
	function banner_clientname($curVal,$mode)
	{
		$frm = e107::getForm();		
		 		
		switch($mode)
		{
			case 'read': // List Page
				return $curVal;
			break;
			
			case 'write': // Edit Page
				$text = '';
				if (count($this->clients)) 
				{
					$text = $frm->select('banner_clientname_sel',$this->clients, $curVal,'', LAN_SELECT."...");
					$text .= $frm->text('banner_clientname','','',array('placeholder'=> 'Or enter a new client'));	
				}
				else
				{
					
					$text .= $frm->text('banner_clientname',$curVal);
					$text .= "<span class='field-help'>".BNRLAN_29."</span>";
				}
				
				return $text; 
			//	return $frm->text('banner_clientname',$curVal);		
			break;
			
			case 'filter':
			case 'batch':
				return  $this->clients; 
			break;
		}
	}

	
	// Custom Method/Function 
	function banner_clientlogin($curVal,$mode)
	{
		$frm = e107::getForm();		
		 		
		switch($mode)
		{
			case 'read': // List Page
				return $curVal;
			break;
			
			case 'write': // Edit Page
				return $frm->text('banner_clientlogin',$curVal);		
			break;
			
			case 'filter':
			case 'batch':
				return null;
			break;
		}
	}

	// Custom Method/Function 
	function banner_impressions($curVal,$mode)
	{
		$frm = e107::getForm();		
		 		
		switch($mode)
		{
			case 'read': // List Page
				$banner_row = $this->getController()->getListModel()->getData(); 
			//	$impressions_left = ($banner_row['banner_impurchased'] ? $banner_row['banner_impurchased'] - $banner_row['banner_impressions'] : BANNERLAN_30);
				$impressions_purchased = ($banner_row['banner_impurchased'] ? $banner_row['banner_impurchased'] : BANNERLAN_30);
				return $curVal .' / '.$impressions_purchased;
			break;
			
			case 'write': // Edit Page
				return $frm->text('banner_impressions',$curVal);		
			break;
			
			case 'filter':
			case 'batch':
				return  array();
			break;
		}

		return null;
	}
	
	// Custom Method/Function 
	function banner_campaign($curVal,$mode)
	{
		$frm = e107::getForm();		
		 		
		switch($mode)
		{
			case 'read': // List Page
				return $curVal;
			break;
			
			case 'write': // Edit Page
				if (count($this->campaigns)) 
				{
					$text = $frm->select('banner_campaign_sel',$this->campaigns, $curVal,'',LAN_SELECT."...");
					$text .= $frm->text('banner_campaign','','',array('size'=>'xlarge', 'class'=>'e-hideme','placeholder'=> 'Enter a campaign name'));
				}
				else
				{
					$text = $frm->text('banner_campaign',$curVal, '', array('size'=>'xlarge', 'placeholder'=> 'Enter a campaign name'));
				}
				return $text; // $frm->text('banner_campaign',$curVal);		
			break;
			
			case 'filter':
			case 'batch':
				return  $this->campaigns; 
			break;
		}
	}
	
	
	// Custom Method/Function 
	function click_percentage($curVal,$mode)
	{
		if($mode != 'read')
		{
			return null;
		}
		
		$frm = e107::getForm();		
		
		$banner_row = $this->getController()->getListModel()->getData(); 
		 
	//	 return print_a($banner_row,true); 		
		$clickpercentage = ($banner_row['banner_clicks'] && $banner_row['banner_impressions'] ? round(($banner_row['banner_clicks'] / $banner_row['banner_impressions']) * 100,1)."%" : "-");
		
		return $clickpercentage; 
		//$impressions_left = ($banner_row['banner_impurchased'] ? $banner_row['banner_impurchased'] - $banner_row['banner_impressions'] : BANNERLAN_30);
	//	$impressions_purchased = ($banner_row['banner_impurchased'] ? $banner_row['banner_impurchased'] : BANNERLAN_30);
	}	
	
	
	
		
	
	
	

}		
		

new banner_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");
exit;


//TODO - Put client/password in a separate table?






// ---------------------------- UNUSED Below here -------------------------------------------- // 







