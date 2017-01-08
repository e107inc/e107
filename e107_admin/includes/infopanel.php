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


define('ADMINFEEDMORE', 'http://e107.org/blog');





class adminstyle_infopanel
{
	
	private $iconlist = array();
	
	function __construct()
	{
		$code = "
		jQuery(function($){
  			$('#e-adminfeed').load('".e_ADMIN."admin.php?mode=core&type=feed');
  		    $('#e-adminfeed-plugin').load('".e_ADMIN."admin.php?mode=addons&type=plugin');
  		    $('#e-adminfeed-theme').load('".e_ADMIN."admin.php?mode=addons&type=theme');
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
		


		
		$text2 .= $ns->tablerender(LAN_WEBSITE_STATUS, $this->renderWebsiteStatus(),"",true);	
		
		
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

	private function renderChart()
	{
	

		// REQUIRES Log Plugin to be installed. 		
		if (e107::isInstalled('log')) 
		{
			return $this->renderStats('log');
		}
		elseif(e107::isInstalled('awstats')) 
		{
			return $this->renderStats('awstats');
		}
		else
		{
			return $this->renderStats('demo');
		}
		
	}


	function renderWebsiteStatus()
	{
		$tp = e107::getParser();
		/* 
		 // Settings button if needed. 
		<div class="tab-header">
		          <span class="pull-right">
		          <span class="options">
		            <div class="btn-group">
		              <a class="dropdown-toggle" data-toggle="dropdown"><i class="icon-cog"></i></a>
		              <ul class="dropdown-menu black-box-dropdown dropdown-left">
		                <li><a href="#">Action</a></li>
		                <li><a href="#">Another action</a></li>
		                <li><a href="#">Something else here</a></li>
		                <li class="divider"></li>
		                <li><a href="#">Separated link</a></li>
		              </ul>
		            </div>
		          </span>
		        </span>
		  </div>
		 */
		
		$tab = array();
		$tab['e-stats'] = array('caption'=>$tp->toGlyph('fa-signal').' '.LAN_STATS, 'text'=>$this->renderChart());
		$tab['e-online'] = array('caption'=>$tp->toGlyph('fa-user').' '.LAN_ONLINE.' ('.$this->renderOnlineUsers('count').')', 'text'=>$this->renderOnlineUsers());
		


		if($plugs = e107::getAddonConfig('e_dashboard',null, 'chart'))
		{
			foreach($plugs as $plug => $val)
			{
				foreach($val as $item)
				{
					if(!empty($item))
					{
						$tab[] = $item;	
					}	
				}			
			}
		}

		return e107::getForm()->tabs($tab);
		

	}





	function renderOnlineUsers($data=false)
	{
		
		$ol = e107::getOnline();
		$tp = e107::getParser();
		$multilan = e107::getPref('multilanguage');

		$panelOnline = "
				
				<table class='table table-condensed table-striped' >
				<colgroup>
					<col style='width: 10%' />
		            <col style='width: 25%' />
					<col style='width: 10%' />
					<col style='width: 40%' />
					<col style='width: auto' />";


		$panelOnline .= (!empty($multilan)) ? "<col style='width: auto' />" : "";


		$panelOnline .= "

				</colgroup>
				<thead>
					<tr class='first'>
						<th>".LAN_TIMESTAMP."</th>
						<th>".LAN_USER."</th>
						<th>".LAN_IP."</th>
						<th>".LAN_PAGE."</th>
						<th class='center'>".LAN_AGENT."</th>";

		$panelOnline .= (!empty($multilan)) ? "<th class='center'>".LAN_LANG."</th>" : "";

		$panelOnline .= "
					</tr>
				</thead>
				<tbody>";



		$online = $ol->userList() + $ol->guestList();
		
		if($data == 'count')
		{
			return count($online);	
		}
				
	//		echo "Users: ".print_a($online);

		$lng = e107::getLanguage();

		foreach ($online as $val)
		{
			$panelOnline .= "
			<tr>
				<td class='nowrap'>".e107::getDateConvert()->convert_date($val['user_currentvisit'],'%H:%M:%S')."</td>
				<td>".$this->renderOnlineName($val['online_user_id'])."</td>
				<td>".e107::getIPHandler()->ipDecode($val['user_ip'])."</td>
				<td><a class='e-tip' href='".$val['user_location']."' title='".$val['user_location']."'>".$tp->html_truncate(basename($val['user_location']),50,"...")."</a></td>
				<td class='center'><a class='e-tip' href='#' title='".$val['user_agent']."'>".$this->browserIcon($val)."</a></td>";

			$panelOnline .= (!empty($multilan)) ? "<td class='center'><a class='e-tip' href='#' title=\"".$lng->convert($val['user_language'])."\">".$val['user_language']."</a></td>" : "";


			$panelOnline .= "
			</tr>
			";
		}

	
		$panelOnline .= "</tbody></table>";
		
		return $panelOnline;
	}	


	function browserIcon($row)
	{
	
		$types = array(
			"ie" 		=> "MSIE",
			'chrome'	=> 'Chrome',
			'firefox'	=> 'Firefox',
			'seamonkey'	=> 'Seamonkey',
		//	'Chromium/xyz
			'safari'	=> "Safari",
			'opera'		=> "Opera"
		);
				
	
		if($row['user_bot'] === true)
		{
			return "<i class='browser e-bot-16'></i>";	
		}
		
		foreach($types as $icon=>$b)
		{
			if(strpos($row['user_agent'], $b)!==false)
			{
				return "<i class='browsers e-".$icon."-16' ></i>";	
			}
		}

		return "<i class='browsers e-firefox-16'></i>"; // FIXME find a default icon. 
	}

	
	function renderOnlineName($val)
	{
		if($val==0)
		{
			return LAN_GUEST;
		}
		return $val;
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
	
	
	private function getStats($type) 
	{
		
		
		if(file_exists(e_PLUGIN."awstats/awstats.graph.php"))  
		{
			require_once(e_PLUGIN."awstats/awstats.graph.php");
			$stat = new awstats;
			
			if($data = $stat->getData())
			{
				return $data;
			}
			
		//	return;	
		}

		if($type == 'demo')
		{
			$data = array();
		
			$months = e107::getDate()->terms('month');
		
			$data['labels'] = array($months[0], //"January",
 						$months[1], //"February",
 						$months[2], //"March",
 						$months[3], //"April",
 						$months[4], //"May",
 						$months[5], //"June",
 						$months[6]  //"July"
 			);
			
			$data['datasets'][]	= array(
							'fillColor'			=> "rgba(220,220,220,0.5)",
							'strokeColor'		=> "rgba(220,220,220,1)",
							'pointColor '		=> "rgba(220,220,220,1)",
							'pointStrokeColor'	=> "#fff",
							'data'				=> array(65,59,90,81,56,55,40),
							'title'				=> ADLAN_168// "Visits"
			);
			
			$data['datasets'][]	= array(
							'fillColor'			=> "rgba(151,187,205,0.5)",
							'strokeColor'		=> "rgba(151,187,205,1)",
							'pointColor '		=> "rgba(151,187,205,1)",
							'pointStrokeColor'	=> "#fff",
							'data'				=> array(28,48,40,19,96,27,100),
							'title'				=> ADLAN_169 //"Unique Visits"
			);
			
			return $data;
		}

	
				
		$sql = e107::getDB();

		$td = date("Y-m-j", time());
		$dayarray[$td] = array();
		$pagearray = array();

		$qry = "
		SELECT * from #logstats WHERE log_id REGEXP('[[:digit:]]+\-[[:digit:]]+\-[[:digit:]]+')
		ORDER BY CONCAT(LEFT(log_id,4), SUBSTRING(log_id, 6, 2), LPAD(SUBSTRING(log_id, 9), 2, '0'))
		DESC LIMIT 0,9
		";

		if($amount = $sql->gen($qry)) 
		{
			$array = $sql->db_getList();

			$ttotal = 0;
			$utotal = 0;

			foreach($array as $key => $value) 
			{
				extract($value);
				$log_id = substr($log_id, 0, 4).'-'.substr($log_id, 5, 2).'-'.str_pad(substr($log_id, 8), 2, '0', STR_PAD_LEFT);
				if(is_array($log_data)) {
					$entries[0] = $log_data['host'];
					$entries[1] = $log_data['date'];
					$entries[2] = $log_data['os'];
					$entries[3] = $log_data['browser'];
					$entries[4] = $log_data['screen'];
					$entries[5] = $log_data['referer'];
				} 
				else 
				{
					$entries = explode(chr(1), $log_data);
				}

				$dayarray[$log_id]['daytotal'] = $entries[0];
				$dayarray[$log_id]['dayunique'] = $entries[1];

				unset($entries[0]);
				unset($entries[1]);
				
				foreach($entries as $entry) 
				{
					if($entry) 
					{
						list($url, $total, $unique) = explode("|", $entry);
						if(strstr($url, "/")) 
						{
							$urlname = preg_replace("/\.php|\?.*/", "", substr($url, (strrpos($url, "/")+1)));
						} 
						else 
						{
							$urlname = preg_replace("/\.php|\?.*/", "", $url);
						}
						$dayarray[$log_id][$urlname] = array('url' => $url, 'total' => $total, 'unique' => $unique);
						if (!isset($pagearray[$urlname]['total'])) $pagearray[$urlname]['total'] = 0;
						if (!isset($pagearray[$urlname]['unique'])) $pagearray[$urlname]['unique'] = 0;
						$pagearray[$urlname]['total'] += $total;
						$pagearray[$urlname]['unique'] += $unique;
						$ttotal += $total;
						$utotal += $unique;
					}
				}
			}
		}

		$logfile = e_LOG.'logp_'.date('z.Y', time()).'.php'; // was logi_ ??
		if(is_readable($logfile)) 
		{
			require($logfile);
		}



		if(vartrue($pageInfo))
		{
			foreach($pageInfo as $fkey => $fvalue)
			{
				$dayarray[$td][$fkey]['total'] += $fvalue['ttl'];
				$dayarray[$td][$fkey]['unique'] += $fvalue['unq'];
				$dayarray[$td]['daytotal'] += $fvalue['ttl'];
				$dayarray[$td]['dayunique'] += $fvalue['unq'];
				$pagearray[$fkey]['total'] += $fvalue['ttl'];
				$pagearray[$fkey]['unique'] += $fvalue['unq'];
				$ttotal += $fvalue['ttl'];
				$utotal += $fvalue['unq'];
			}
		}

	
		$visitors = array();
		$unique = array();
		
	
		ksort($dayarray);
		foreach($dayarray as $k=>$v)
		{
			$unix = strtotime($k);
			
			$visitors[] = intval(vartrue($v['daytotal']));
			$unique[] = intval(vartrue($v['dayunique']));
			$label[] = "'".date("D",$unix)."'";				
		}
		
		$data = array();
		
		$data['labels'] 	= $label; 
		
		//visitors
		$data['datasets'][]	= array(
							'fillColor' 		=> "rgba(220,220,220,0.5)",
							'strokeColor'  		=>  "rgba(220,220,220,1)",
							'pointColor '  		=>  "rgba(220,220,220,1)",
							'pointStrokeColor'  =>  "#fff",
							'data'				=> $visitors	
			
		);
		
		
		//Unique Visitors
		$data['datasets'][]	= array(
							'fillColor' 		=> "rgba(151,187,205,0.5)",
							'strokeColor'  		=>  "rgba(151,187,205,1)",
							'pointColor '  		=>  "rgba(151,187,205,1)",
							'pointStrokeColor'  =>  "#fff",
							'data'				=> $unique		
		);
		
		
		
		return $data;
		
	
	}
	
	

	
	private function renderStats($type)
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
		
	}
	
}
?>

