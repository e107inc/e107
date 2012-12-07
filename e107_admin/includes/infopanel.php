<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Info panel admin view
 *
 * $URL$
 * $Id$
 */

if (!defined('e107_INIT'))
{
	exit;
}


class adminstyle_infopanel
{
	
	function __construct()
	{
		e107::js('core','tweet/jquery.tweet.js');
	//	e107::css('core','tweet/jquery.tweet.css');
		
		$code = <<<EOF
		jQuery(function($){
	        $("#e-tweet").tweet({
	            username: "e107",
	            join_text: "auto",
	            avatar_size: 16,
	 			retweets: false,     
	            count: 3,
	            fetch: 5,
	            template: "{text}<br />- {time} » {retweet_action}",
	            filter: function(t){ return ! /^@\w+/.test(t.tweet_raw_text); },
                auto_join_text_default: "",
	            auto_join_text_ed: "",
	            auto_join_text_ing: "",
	            auto_join_text_reply: "",
	            auto_join_text_url: "",
	            loading_text: " Loading news...",
	            refresh_interval: 60
	        });
    	});
EOF;
	
		
	  	$this->getStats();
		
		e107::js('inline',$code,'jquery');
		
		
		if (isset($_POST['submit-mye107']) || varset($_POST['submit-mymenus']))
		{
			$user_pref['core-infopanel-mye107'] = $_POST['e-mye107'];
			
			save_prefs('user');
			
			$pref['core-infopanel-menus'] = $_POST['e-mymenus'];
			
			save_prefs();
		}
	}
	
	
	function render()
	{
		$tp = e107::getParser();
		$ns = e107::getRender();
		$sql = e107::getDb();
		$mes = e107::getMessage();
		$pref = e107::getPref();
		$frm = e107::getForm();
		
		/*
		XXX Check Bootstrap bug is fixed. 
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
	
		//TODO LANs throughout.
		
		global $style;

		// ---------------------- Start Panel --------------------------------

//		$text = "<div >";
		if (getperms('0') && !vartrue($user_pref['core-infopanel-mye107'])) // Set default icons.
		{
			$defArray = array (
			  	0 => 'e-administrator',
			    1 => 'e-cpage',
			    2 => 'e-frontpage',
			    3 => 'e-mailout',
			    4 => 'e-image',
			    5 => 'e-menus',
			    6 => 'e-meta',
			    7 => 'e-newspost',
			    8 => 'e-plugin',
			    9 => 'e-prefs',
			    10 => 'e-links',
			    11 => 'e-theme',
			    12 => 'e-userclass2',
			    13 => 'e-users',
			    14 => 'e-wmessage'
			  );
			$user_pref['core-infopanel-mye107'] = vartrue($pref['core-infopanel-default'],$defArray);
			
		}
		
		$array_functions_assoc = e107::getNav()->adminLinks('assoc');
	
		$iconlist = array_merge($array_functions_assoc, e107::getNav()->pluginLinks(E_16_PLUGMANAGER, "array"));
		
	//	"<form method='post' action='".e_SELF."?".e_QUERY."'>";
		
		$tp->parseTemplate("{SETSTYLE=core-infopanel}");
		
		// Personalized Panel 
		// Rendering the saved configuration.
		
		$mainPanel = "
		<div id='core-infopanel_mye107' >
			
				<div class='left' style='padding:32px'>";
			
			foreach ($iconlist as $key=>$val)
			{
				if (!vartrue($user_pref['core-infopanel-mye107']) || in_array($key, $user_pref['core-infopanel-mye107']))
				{
					$mainPanel .= e107::getNav()->renderAdminButton($val['link'], $val['title'], $val['caption'], $val['perms'], $val['icon_32'], "div-icon-only");
				}
			}
	
			$mainPanel .= "<div class='clear'>&nbsp;</div>";
			$mainPanel .= "</div>
	      
			</div>";
	
		$text = $ns->tablerender(ucwords(USERNAME)."'s Control Panel", $mainPanel, "core-infopanel_mye107",true);
		
	
	//  ------------------------------- e107 News --------------------------------
		
		$text2 .= $ns->tablerender("e107 News","<div id='e-tweet'></div>","core-infopanel_news",true);
	
		// REQUIRES Log Plugin to be installed. 
		if (e107::isInstalled('log')) 
		{
		 		$text2 .= $ns->tablerender("Visitors Last 10 Days", $this->renderStats(),"core-infopanel_stats",true);
		}
		else
		{
			$text2 .= $ns->tablerender("Visitors This Week", "Log Statistics Plugin Not Installed","core-infopanel_stats",true);	
		}
	
	
	// ---------------------Latest Stuff ---------------------------
	
		require_once (e_CORE."shortcodes/batch/admin_shortcodes.php");
		
		$text .= $ns->tablerender(ADLAN_LAT_1,$tp->parseTemplate("{ADMIN_LATEST=norender}"),"core-infopanel_latest",true);
		$text .= $ns->tablerender(LAN_STATUS,$tp->parseTemplate("{ADMIN_STATUS=norender}"),"core-infopanel_latest",true);
	/*
	
			$text .= "<li class='span6'>
				".$tp->parseTemplate("{ADMIN_LATEST=norender}").
				$tp->parseTemplate("{ADMIN_STATUS=norender}")."
						</div>";
		
		*/
	
	
	// ---------------------- Who's Online  ------------------------
	// TODO Could use a new _menu item instead.
	
	
	$ol = e107::getOnline();
//	echo "Users: ".print_a($ol->userList());
		
	$panelOnline = "
		
			<table class='table adminlist'>
			<colgroup>
				<col style='width: 10%' />
	            <col style='width: 25%' />
				<col style='width: 10%' />
				<col style='width: 40%' />
				<col style='width: auto' />
			</colgroup>
			<thead>
				<tr>
					<th>Timestamp</th>
					<th>Username</th>
					<th>IP</th>
					<th>Page</th>
					<th>Agent</th>
				</tr>
			</thead>
			<tbody>";
		
		
			

			$online = $ol->userList();
	
			foreach ($online as $val)
			{
				$panelOnline .= "<tr>
					<td class='nowrap'>".e107::getDateConvert()->convert_date($val['user_currentvisit'],'%H:%M:%S')."</td>
						<td>".$this->renderOnlineName($val['online_user_id'])."</td>
						<td>".e107::getIPHandler()->ipDecode($val['user_ip'])."</td>
						<td><a class='e-tip' href='".$val['user_location']."' title='".$val['user_location']."'>".basename($val['user_location'])."</a></td>
						<td><a class='e-tip' href='#' title='".$val['user_agent']."'>".$this->browserIcon($val)."</a></td>
					</tr>
					";
			}
//$tp->text_truncate(str_replace("/"," / ",$val['user_agent']),20,'...')
	
		$panelOnline .= "</tbody></table>
		";
		
		$text2 .= $ns->tablerender('Visitors Online : '.$nOnline, $panelOnline,'core-infopanel_online',true);
		
	// --------------------- User Selected Menus -------------------
		
	
		if (varset($pref['core-infopanel-menus']))
		{
			foreach ($pref['core-infopanel-menus'] as $val)
			{
				$id = $frm->name2id('core-infopanel_'.$val);			
				$inc = $tp->parseTemplate("{PLUGIN=$val|TRUE}");
				$text .= $inc;
				// $text .= $ns->tablerender("", $inc, $id,true);
			}
		}
	
	
		
		
		
		
		
	//	$text .= "<div class='clear'>&nbsp;</div>";
		
		$text .= $this->render_infopanel_options();
		
		
		
	//	$text .= "</div>";
		
		if($_GET['mode'] != 'customize')
		{
			// $ns->tablerender(ADLAN_47." ".ADMINNAME, $emessage->render().$text);	
			echo $mes->render().'

			<!-- INFOPANEL -->
			<div class="span6">
			 <ul class="thumbnails">'.$text.'</ul>
			 </div>
			 
			 <div class="span6">
			 <ul class="thumbnails">'.$text2.'</ul>
			 </div>
						 
			<!--  -->  
			 
			 
			 ';
		}
		else
		{
			echo $frm->open('infopanel');
			echo $this->render_infopanel_options(true);	
			echo $frm->close();
		}

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
			return "<i class='browser e-bot-32'></i>";	
		}
		
		foreach($types as $icon=>$b)
		{
			if(strpos($row['user_agent'], $b)!==false)
			{
				return "<i class='browsers e-".$icon."-32' ></i>";	
			}
		}

		return "<i class='browsers e-firefox-32'></i>"; // FIXME find a default icon. 
	}

	
	function renderOnlineName($val)
	{
		if($val==0)
		{
			return "Guest";
		}
		return $val;
	}
	
	
	
	
	function render_info_panel($caption, $text)
	{
		return "<div class='main_caption bevel left'><b>".$caption."</b></div>
	    <div class='left block-text' >".$text."</div>";
	}
	
	
	
	
		
	function render_infopanel_options($render = false)
	{
		// $frm = e107::getSingleton('e_form');
		$frm = e107::getForm();
		$mes = e107::getMessage();
		$ns = e107::getRender();
		
		$start = "<div>
		To customize this page, please <a title = 'Customize Admin' href='".e_SELF."?mode=customize&amp;iframe=1' class='e-modal-iframe'>click here</a>.
		</div>
	    ";
	    
	    if($render == false){ return ""; }
	    
		$text2 = "<div id='customize_icons' class='forumheader3' style='border:0px;margin:0px'>
	    <form method='post' id='e-modal-form' action='".e_SELF."'>";
	    
		$text2 .= $ns->tablerender("Personalize Icons",render_infopanel_icons(),'personalize',true); 
		$text2 .= "<div class='clear'>&nbsp;</div>";
		$text2 .= $ns->tablerender("Personalize Menus",render_infopanel_menu_options(),'personalize',true); 
	//	$text2 .= render_infopanel_icons();
		//$text2 .= "<div class='clear'>&nbsp;</div>";
	//	$text2 .= "<h3>Menus</h3>";
	//	$text2 .= render_infopanel_menu_options();
		$text2 .= "<div class='clear'>&nbsp;</div>";
		$text2 .= "<div id='button' class='buttons-bar center'>";
		$text2 .= $frm->admin_button('submit-mye107', 'Save', 'create');
		$text2 .= "</div></form>";
	//	$text2 .= "</div>";
		
	//	$end = "</div>";
			
		
		return $mes->render().$text2.$end;
	}


	function render_infopanel_icons()
	{
		$frm = e107::getSingleton('e_form');
		global $iconlist,$pluglist, $user_pref;
			
		$text = "";
	
	
		foreach ($iconlist as $key=>$icon)
		{
			if (getperms($icon['perms']))
			{
				$checked = (varset($user_pref['core-infopanel-mye107']) && in_array($key, $user_pref['core-infopanel-mye107'])) ? true : false;
				$text .= "<div class='left f-left list field-spacer' style='display:block;height:24px;width:200px;'>
		                        ".$icon['icon'].' '.$frm->checkbox_label($icon['title'], 'e-mye107[]', $key, $checked)."</div>";
			}
		}
		if (is_array($pluglist))
		{
			foreach ($pluglist as $key=>$icon)
			{
				if (getperms($icon['perms']))
				{
					$checked = (in_array('p-'.$key, $user_pref['core-infopanel-mye107'])) ? true : false;
					$text .= "<div class='left f-left list field-spacer' style='display:block;height:24px;width:200px;'>
			                         ".$icon['icon'].$frm->checkbox_label($icon['title'], 'e-mye107[]', $key, $checked)."</div>";
				}
			}
		}
		$text .= "<div class='clear'>&nbsp;</div>";
		return $text;
	}




	function render_infopanel_menu_options()
	{
		if(!getperms('0'))
		{
			return;
		}

		$frm = e107::getForm();
		$pref = e107::getPref();
		
	
		$text = "";
		$menu_qry = 'SELECT * FROM #menus WHERE menu_id!= 0  GROUP BY menu_name ORDER BY menu_name';
		$settings = varset($pref['core-infopanel-menus'],array());
	
		if (e107::getDb()->db_Select_gen($menu_qry))
		{
			while ($row = e107::getDb()->db_Fetch())
			{
				// if(!is_numeric($row['menu_path']))
				{
					$label = str_replace("_menu","",$row['menu_name']);
					$path_to_menu = $row['menu_path'].$row['menu_name'];
					$checked = ($settings && in_array($path_to_menu, $settings)) ? true : false;
					$text .= "\n<div class='left f-left list field-spacer' style='display:block;height:24px;width:200px;'>";
					$text .= $frm->checkbox_label($label, "e-mymenus[]",$path_to_menu, $checked);
					$text .= "</div>";
				}
			}
		}
		
		$text .= "<div class='clear'>&nbsp;</div>";
		return $text;
	}
	
	
	function getStats() 
	{
		

		
		
				
		$sql = e107::getDB();

		$td = date("Y-m-j", time());
		$dayarray[$td] = array();
		$pagearray = array();

		$qry = "
		SELECT * from #logstats WHERE log_id REGEXP('[[:digit:]]+\-[[:digit:]]+\-[[:digit:]]+')
		ORDER BY CONCAT(LEFT(log_id,4), SUBSTRING(log_id, 6, 2), LPAD(SUBSTRING(log_id, 9), 2, '0'))
		DESC LIMIT 0,9
		";

		if($amount = $sql -> db_Select_gen($qry)) 
		{
			$array = $sql -> db_getList();

			$ttotal = 0;
			$utotal = 0;

			foreach($array as $key => $value) 
			{
				extract($value);
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

	
		ksort($dayarray);
		foreach($dayarray as $k=>$v)
		{
			$unix = strtotime($k);
			
			$day[] = intval($v['daytotal']);
			$label[] = "'".date("D",$unix)."'";				
		}
		
		e107::js('log','js/awesomechart.js');
		e107::js('inline',"
		 function drawMyChart()
		 {
	        if(!!document.createElement('canvas').getContext) //check that the canvas element is supported
	        { 
	            var mychart = new AwesomeChart('canvas1');
	  
	          	mychart.chartType = 'pareto';
	
	       
				mychart.data = [".implode(", ",$day)."];
				mychart.labels = [".implode(", ",$label)."];
	            mychart.colors = ['#0088CC', '#FF6600','#0088CC', '#FF6600','#0088CC', '#FF6600','#0088CC', '#FF6600','#0088CC'];
	            mychart.animate = true;
            	mychart.animationFrames = 30;
            //	mychart.randomColors = true;
           // 	mychart.dataValueFontHeight = 20;
           mychart.yAxisLabelFontHeight = 15;
            	mychart.chartMarkerSize = 20;
            	 mychart.chartHorizontalLineStrokeStyle = '#999';
    			mychart.chartHorizontalLineWidth = 1;
	            mychart.draw();
	        }
	      }
	      
		window.onload = drawMyChart;
	  
      ");
		
	//	print_a($dayarray);;
	
	}
	
	
	function renderStats()
	{
		return '<canvas id="canvas1" class="center" width="710" height="300" style="width:100%; height:100%">
        	Your web-browser does not support the HTML 5 canvas element.
   		 </canvas>';	
		
		
		
	}
	
}
?>
