<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Info panel admin view
 *
 */

if (!defined('e107_INIT'))
{
	exit;
}


define('ADMINFEEDMORE', 'https://e107.org/blog');





class adminstyle_infopanel
{
	
	private $iconlist = array();
	
	function __construct()
	{

		if(!ADMIN)
		{
			return null;
		}


		$coreUpdateCheck = '';
		$addonUpdateCheck = '';


		if( e107::getSession()->get('core-update-status') !== true)
		{
			$coreUpdateCheck = "
				$('#e-admin-core-update').html('<i title=\"".LAN_CHECKING_FOR_UPDATES."\" class=\"fa fa-spinner fa-spin\"></i>');
  		    	$.get('".e_ADMIN."admin.php?mode=core&type=update', function( data ) {
 		    	
  		    	var res = $.parseJSON(data);
		    
  		    	if(res === true)
  		    	{
  		    	    $('#e-admin-core-update').html('<span class=\"text-info\"><i class=\"fa fa-database\"></i></span>');
  		    	    
  		    	     $('[data-toggle=\"popover\"]').popover('show');
	                 $('.popover').on('click', function() 
	                 {
	                     $('[data-toggle=\"popover\"]').popover('hide');
	           		});
  		    	}
  		    	else
  		    	{
  		    	    // Hide li element.
  		    		$('#e-admin-core-update').parent().hide();
  		    	}
			   
			});
			
			";

		}

		if( e107::getSession()->get('addons-update-checked') !== true)
		{
			$addonUpdateCheck = "
			$('#e-admin-addons-update').load('".e_ADMIN."admin.php?mode=addons&type=update');
			";

		}



		$code = "
		jQuery(function($){
  			$('#e-adminfeed').load('".e_ADMIN."admin.php?mode=core&type=feed');
  		    $('#e-adminfeed-plugin').load('".e_ADMIN."admin.php?mode=addons&type=plugin');
  		    $('#e-adminfeed-theme').load('".e_ADMIN."admin.php?mode=addons&type=theme');
  		    
  		    ".$coreUpdateCheck."
  		    ".$addonUpdateCheck."
		
		});
		";







		
		e107::js('inline',$code,'jquery');
		
		if (isset($_POST['submit-mye107']) || varset($_POST['submit-mymenus']))
		{
			$this->savePref('core-infopanel-mye107', $_POST['e-mye107']);
			$this->savePref('core-infopanel-menus', $_POST['e-mymenus']);
		}

		$this->iconlist = e107::getNav()->adminLinks();
	}

	/**
	 * Save preferences.
	 *
	 * @param $key
	 * @param $value
	 */
	public function savePref($key, $value)
	{
		// Get "Apply dashboard preferences to all administrators" setting.
		$adminPref = e107::getConfig()->get('adminpref', 0);

		// If "Apply dashboard preferences to all administrators" is checked.
		// Save as $pref.
		if($adminPref == 1)
		{
			e107::getConfig()
				->setPosted($key, $value)
				->save();
		}
		// Save as $user_pref.
		else
		{
			e107::getUser()
				->getConfig()
				->set($key, $value)
				->save();
		}
	}

	/**
	 * Get preferences.
	 *
	 * @return mixed
	 */
	public function getUserPref()
	{
		// Get "Apply dashboard preferences to all administrators" setting.
		$adminPref = e107::getConfig()->get('adminpref', 0);

		// If "Apply dashboard preferences to all administrators" is checked.
		// Get $pref.
		if($adminPref == 1)
		{
			$user_pref = e107::getPref();
		}
		// Get $user_pref.
		else
		{
			$user_pref = e107::getUser()->getPref();
		}

		return $user_pref;
	}

	/**
	 * Allow to get Icon List.
	 */
	function getIconList()
	{
		return $this->iconlist;
	}
	
	function render()
	{
		$tp = e107::getParser();
		$ns = e107::getRender();
		$sql = e107::getDb();
		$mes = e107::getMessage();
		$pref = e107::getPref();
		$frm = e107::getForm();
		
		
	//	XXX Check Bootstrap bug is fixed. 
	/*
		echo '
          <ul class="thumbnails">
            <li class="span4">
              <a href="#" class="thumbnail">
                <img src="http://placehold.it/360x270" alt="">
              </a>
            </li>
             <li class="span4">
              <a href="#" class="thumbnail">
                <img src="http://placehold.it/360x270" alt="">
              </a>
            </li>
             <li class="span4">
              <a href="#" class="thumbnail">
                <img src="http://placehold.it/360x270" alt="">
              </a>
            </li>
             <li class="span4">
              <a href="#" class="thumbnail">
                <img src="http://placehold.it/360x270" alt="">
              </a>
            </li>
             <li class="span4">
              <a href="#" class="thumbnail">
                <img src="http://placehold.it/360x270" alt="">
              </a>
            </li>
             <li class="span4">
              <a href="#" class="thumbnail">
                <img src="http://placehold.it/360x270" alt="">
              </a>
            </li>
          </ul>

		';
	*/	

		$user_pref = $this->getUserPref();

		// ---------------------- Start Panel --------------------------------

//		$text = "<div >";
		$myE107 = varset($user_pref['core-infopanel-mye107'], array());
		if(empty($myE107)) // Set default icons.
		{
			$defArray = array(
				0  => 'e-administrator',
				1  => 'e-cpage',
				2  => 'e-frontpage',
				3  => 'e-mailout',
				4  => 'e-image',
				5  => 'e-menus',
				6  => 'e-meta',
				7  => 'e-newspost',
				8  => 'e-plugin',
				9  => 'e-prefs',
				10 => 'e-links',
				11 => 'e-theme',
				12 => 'e-userclass2',
				13 => 'e-users',
				14 => 'e-wmessage'
			);
			$user_pref['core-infopanel-mye107'] = $defArray;
		}
		
       
		
	//	"<form method='post' action='".e_SELF."?".e_QUERY."'>";
		
		$tp->parseTemplate("{SETSTYLE=core-infopanel}");

		// Personalized Panel 
		// Rendering the saved configuration.
		
		$mainPanel = "
		<div id='core-infopanel_mye107' >
		";
		
		/*
		$mainPanel .= '<span class="pull-right">
		          <span class="options">
		            <div class="btn-group">
		              <a class="dropdown-toggle" data-toggle="dropdown"><i class="icon-cog"></i></a>
		              <ul class="dropdown-menu black-box-dropdown dropdown-right">
		                <li>'.$this->render_infopanel_icons().'</li>
		              </ul>
		            </div>
		          </span>
		        </span>';
		
		*/
		
	//	print_a($user_pref['core-infopanel-mye107']);
        
		$mainPanel .= "
		
		
		
		
			
				<div class='left'>";
			
			foreach ($this->iconlist as $key=>$val)
			{
				if (!vartrue($user_pref['core-infopanel-mye107']) || in_array($key, $user_pref['core-infopanel-mye107']))
				{
					$mainPanel .= e107::getNav()->renderAdminButton($val['link'], $val['title'], $val['caption'], $val['perms'], $val['icon_32'], "div");
				}
			}
	
			// $mainPanel .= "<div class='clear'>&nbsp;</div>";
			$mainPanel .= "</div>
	      
			</div>";

		$caption = $tp->lanVars(LAN_CONTROL_PANEL, ucwords(USERNAME));

		$text3 = $this->renderAddonDashboards();

		$text = $ns->tablerender($caption, $mainPanel, "core-infopanel_mye107",true);
		
	
	//  ------------------------------- e107 News --------------------------------

		$newsTabs = array();
		$newsTabs['coreFeed'] = array('caption'=>LAN_GENERAL,'text'=>"<div id='e-adminfeed' style='min-height:300px'></div><div class='right'><a rel='external' href='".ADMINFEEDMORE."'>".LAN_MORE."</a></div>");
		$newsTabs['pluginFeed'] = array('caption'=>LAN_PLUGIN,'text'=>"<div id='e-adminfeed-plugin'></div>");
		$newsTabs['themeFeed'] = array('caption'=>LAN_THEMES,'text'=>"<div id='e-adminfeed-theme'></div>");

		$text2 = $ns->tablerender(LAN_LATEST_e107_NEWS,e107::getForm()->tabs($newsTabs, array('active'=>'coreFeed')),"core-infopanel_news",true);
	
	
	
	
	// ---------------------Latest Stuff ---------------------------
	
		//require_once (e_CORE."shortcodes/batch/admin_shortcodes.php");
		e107::getScBatch('admin');
		


		
	//	$text3 .= $ns->tablerender(LAN_WEBSITE_STATUS, $this->renderWebsiteStatus(),"",true);


		
		
	//	$text .= $ns->tablerender(ADLAN_LAT_1,$tp->parseTemplate("{ADMIN_LATEST=norender}"),"core-infopanel_latest",true);
	//	$text .= $ns->tablerender(LAN_STATUS,$tp->parseTemplate("{ADMIN_STATUS=norender}"),"core-infopanel_latest",true);
	/*
	
			$text .= "<li class='span6'>
				".$tp->parseTemplate("{ADMIN_LATEST=norender}").
				$tp->parseTemplate("{ADMIN_STATUS=norender}")."
						</div>";
		
		*/
	
	
	$text .= $this->renderLatestComments();
	
	
	// ---------------------- Who's Online  ------------------------
	// TODO Could use a new _menu item instead.
	
	
	//	$text2 .= $ns->tablerender('Visitors Online : '.vartrue($nOnline), $panelOnline,'core-infopanel_online',true);
		
	// --------------------- User Selected Menus -------------------


		if(varset($user_pref['core-infopanel-menus']))
		{
			foreach($user_pref['core-infopanel-menus'] as $val)
			{
				// Custom menu (core).
				if(is_numeric($val))
				{
					$inc = e107::getMenu()->renderMenu($val, null, null, true);
				}
				// Plugin defined menu.
				else
				{
					$inc = $tp->parseTemplate("{PLUGIN=$val|TRUE}");
				}

				$text .= $inc;
			}
		}
	
	
		
		
		
		
		
	//	$text .= "<div class='clear'>&nbsp;</div>";
		
		$text .= $this->render_infopanel_options();
		
		
		
	//	$text .= "</div>";
		
		if(vartrue($_GET['mode']) != 'customize')
		{
			// $ns->tablerender(ADLAN_47." ".ADMINNAME, $emessage->render().$text);	
			echo $mes->render().'



			<!-- INFOPANEL -->

			<div class="row">
				 <div class="span6 col-md-12">
				    '.$text3.'
				 </div>
			</div>


			<div class="row">
				<div class="span6 col-md-6">
				    '.$text.'
				 </div>

				 <div class="span6 col-md-6">
				    '.$text2.'
				 </div>
			</div>
			<!--  -->  
			 
			 
			 ';
		}
		else
		{
			echo $frm->open('infopanel','post', e_SELF);
			echo $this->render_infopanel_options(true);	
			echo $frm->close();
		}

	}
/*
	private function renderChart()
	{
	

		// REQUIRES Log Plugin to be installed. 		
		if (e107::isInstalled('log')) 
		{
			return $this->renderStats('log');
		}

		else
		{
			return $this->renderStats('demo');
		}
		
	}*/


	function renderWebsiteStatus()
	{
		return;
	/*	$tp = e107::getParser();

		$tab = array();
		$tab['e-stats'] = array('caption'=>$tp->toGlyph('fa-signal').' '.LAN_STATS, 'text'=>$this->renderChart());
		$tab['e-online'] = array('caption'=>$tp->toGlyph('fa-user').' '.LAN_ONLINE.' ('.$this->renderOnlineUsers('count').')', 'text'=>$this->renderOnlineUsers());

		return e107::getForm()->tabs($tab);*/

	}


	function getAddonDashboards()
	{
		$arr = array();

		if($plugs = e107::getAddonConfig('e_dashboard',null, 'chart'))
		{
			foreach($plugs as $plug => $val)
			{
				$adg = e107::getAddon($plug,'e_dashboard');

				if(!empty($adg->chartCaption))
				{
					$cap = $adg->chartCaption;
				}
				else
				{
					$cap = defset('LAN_PLUGIN_'.strtoupper($plug).'_NAME', ucfirst($plug));
				}

				foreach($val as $k=>$item)
				{


					if(!empty($item))
					{
					//	$var[] = $item;
						$renderMode = 'plug-infopanel-'.$plug."-".intval($k);



						if(!isset($item['text']))
						{

							foreach ($item as $key => $v) // make sure the ids are unique.
							{
								$newkey = eHelper::dasherize($plug.'-'.$k.'-'.$key);
							    $item[$newkey] = $v;
							    unset($item[$key]);
							}

							$t = e107::getForm()->tabs($item);



						//	$text .= $ns->tablerender($cap, $t, $renderMode, true);
							$arr[] = array('caption'=>$cap, 'text'=>$t, 'mode'=>$renderMode);
						}
						else
						{
						//	$text .= $ns->tablerender($item['caption'], $item['text'], $renderMode, true);
							$arr[] = array('caption'=>$item['caption'], 'text'=>$item['text'], 'mode'=>$renderMode);
						}
					}
				}

			}
		}


		return $arr;
	}



	function renderAddonDashboards()
	{
		$ns = e107::getRender();
		$arr = $this->getAddonDashboards();
		$text = '';

		foreach($arr as $val)
		{
			$text .= $ns->tablerender($val['caption'], $val['text'], $val['mode'], true);

		}

		return $text;

	}


	function renderLatestComments()
	{
		$sql = e107::getDb();
		$tp = e107::getParser();

		if(!check_class('B')) // XXX problems?
		{
	//		return;
		}		
				
		if(!$rows = $sql->retrieve('comments','*','comment_blocked=2 ORDER BY comment_id DESC LIMIT 25',true) )
		{
			return null;
		}
		

		$sc = e107::getScBatch('comment');
				
		$text = '
		  <ul class="media-list unstyled list-unstyled">';
		// <button class='btn btn-mini'><i class='icon-pencil'></i> Edit</button> 
		
		//XXX Always keep template hardcoded here - heavy use of ajax and ids. 
		$count = 1;

		$lanVar = array('x' =>'{USERNAME}', 'y'=>'{TIMEDATE=relative}');
				
		foreach($rows as $row) 
		{
			$hide = ($count > 3) ? ' hide' : '';

			$TEMPLATE = "{SETIMAGE: w=40&h=40}
			<li id='comment-".$row['comment_id']."' class='media".$hide."'>
				<span class='media-object pull-left'>{USER_AVATAR=".$row['comment_author_id']."}</span> 
				<div class='btn-group pull-right'>
	            	<button data-target='".e_BASE."comment.php' data-comment-id='".$row['comment_id']."' data-comment-action='delete' class='btn btn-sm btn-mini btn-danger'><i class='icon-remove'></i> ".LAN_DELETE."</button>
	            	<button data-target='".e_BASE."comment.php' data-comment-id='".$row['comment_id']."' data-comment-action='approve' class='btn btn-sm btn-mini btn-success'><i class='icon-ok'></i> ".LAN_APPROVE."</button>
	            </div>
				<div class='media-body'>
					<small class='muted smalltext'>".$tp->lanVars(LAN_POSTED_BY_X, $lanVar)."</small><br />
					<p>{COMMENT}</p> 
				</div>
				</li>";
			
			$sc->setVars($row);  
		 	$text .= $tp->parseTemplate($TEMPLATE,true,$sc);
			$count++;
		}
        

    	$text .= '
     		</ul>
		    <div class="right">
		      <a class="btn btn-xs btn-mini btn-primary text-right" href="'.e_ADMIN.'comment.php?searchquery=&filter_options=comment_blocked__2">'.LAN_VIEW_ALL.'</a>
		    </div>
		 ';		
		// $text .= "<small class='text-center text-warning'>Note: Not fully functional at the moment.</small>";
		
		$ns = e107::getRender();
		return $ns->tablerender(LAN_LATEST_COMMENTS,$text,'core-infopanel_online',true);		
	}
		
		
		
	
	
	
	
	
	
	function render_info_panel($caption, $text)
	{
		return "<div class='main_caption bevel left'><b>".$caption."</b></div>
	    <div class='left block-text' >".$text."</div>";
	}
	
	
	
	
		
	function render_infopanel_options($render = false)
	{
		$frm = e107::getForm();
		$mes = e107::getMessage();
		$ns = e107::getRender();
		
	    if($render == false){ return ""; }

		$text2 = $ns->tablerender(LAN_PERSONALIZE_ICONS, $this->render_infopanel_icons(),'personalize',true);
		$text2 .= "<div class='clear'>&nbsp;</div>";
		$text2 .= $ns->tablerender(LAN_PERSONALIZE_MENUS, $this->render_infopanel_menu_options(),'personalize',true);
		$text2 .= "<div class='clear'>&nbsp;</div>";
		$text2 .= "<div id='button' class='buttons-bar center'>";
		$text2 .= $frm->admin_button('submit-mye107', LAN_SAVE, 'create');
		$text2 .= "</div>";

		return $mes->render().$text2;
	}


	function render_infopanel_icons()
	{
	
		$frm = e107::getForm();
		$user_pref = $this->getUserPref();

		$text = "<div style='padding-left:20px'>";


		$myE107 = varset($user_pref['core-infopanel-mye107'], array());
		if(empty($myE107)) // Set default icons.
		{
			$defArray = array(
				0  => 'e-administrator',
				1  => 'e-cpage',
				2  => 'e-frontpage',
				3  => 'e-mailout',
				4  => 'e-image',
				5  => 'e-menus',
				6  => 'e-meta',
				7  => 'e-newspost',
				8  => 'e-plugin',
				9  => 'e-prefs',
				10 => 'e-links',
				11 => 'e-theme',
				12 => 'e-userclass2',
				13 => 'e-users',
				14 => 'e-wmessage'
			);
			$user_pref['core-infopanel-mye107'] = $defArray;
		}
	
	
		foreach ($this->iconlist as $key=>$icon)
		{
			if (getperms($icon['perms']))
			{
				$checked = (varset($user_pref['core-infopanel-mye107']) && in_array($key, $user_pref['core-infopanel-mye107'])) ? true : false;
				$text .= "<div class='left f-left list field-spacer form-inline' style='display:block;height:24px;width:200px;'>
		                        ".$icon['icon'].' '.$frm->checkbox_label($icon['title'], 'e-mye107[]', $key, $checked)."</div>";
								
			}
		}
		
		if (isset($pluglist) && is_array($pluglist))
		{
			foreach ($pluglist as $key=>$icon)
			{
				if (getperms($icon['perms']))
				{
					$checked = (in_array('p-'.$key, $user_pref['core-infopanel-mye107'])) ? true : false;
					$text .= "<div class='left f-left list field-spacer form-inline' style='display:block;height:24px;width:200px;'>
			                         ".$icon['icon'].$frm->checkbox_label($icon['title'], 'e-mye107[]', $key, $checked)."</div>";
				}
			}
		}
		$text .= "</div><div class='clear'>&nbsp;</div>";
		return $text;
	}




	function render_infopanel_menu_options()
	{
		if(!getperms('0'))
		{
			return;
		}

		$frm = e107::getForm();
		$user_pref = $this->getUserPref();
		
	
		$text = "<div style='padding-left:20px'>";
		$menu_qry = 'SELECT * FROM #menus WHERE menu_id!= 0  GROUP BY menu_name ORDER BY menu_name';
		$settings = varset($user_pref['core-infopanel-menus'],array());
	
		if (e107::getDb()->gen($menu_qry))
		{
			while ($row = e107::getDb()->db_Fetch())
			{
				// Custom menu (core).
				if(is_numeric($row['menu_path']))
				{
					$path_to_menu = $row['menu_path'];
				}
				// Plugin defined menu.
				else
				{
					$path_to_menu = $row['menu_path'].$row['menu_name'];
				}

				$label = str_replace("_menu","",$row['menu_name']);
				$checked = ($settings && in_array($path_to_menu, $settings)) ? true : false;
				$text .= "\n<div class='left f-left list field-spacer' style='display:block;height:24px;width:200px;'>";
				$text .= $frm->checkbox_label($label, "e-mymenus[]",$path_to_menu, $checked);
				$text .= "</div>";
			}
		}
		
		$text .= "</div><div class='clear'>&nbsp;</div>";
		return $text;
	}
	

	

	
/*	private function renderStats($type)
	{

		$data = $this->getStats($type);

		
		$cht = e107::getChart();
		$cht->setType('line');
		$cht->setOptions(array(
			'annotateDisplay' => true,
			'annotateFontSize' => 8
		));
		$cht->setData($data,'canvas');
		$text = $cht->render('canvas');
	
			
		if($type == 'demo')
		{
			$text .= "<div class='center'><small>".ADLAN_170."<a class='btn btn-xs btn-mini' href='".e_ADMIN."plugin.php?avail'>".ADLAN_171."</a></small></div>";
		}
		else
		{
			$text .= "<div class='center'><small>
			<span style='color:rgba(220,220,220,0.5)'>&diams;</span>".ADLAN_168."&nbsp;&nbsp;
			<span style='color:rgba(151,187,205,1)'>&diams;</span>".ADLAN_169."
			</small></div>";
		}
		
		
		return $text;
		
	}*/
	
}
?>

