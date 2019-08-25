<?php
/*
 * e107 Bootstrap CMS
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)

 */
 
if (!defined('e107_INIT')) { exit; }

// v2.x Standard  - Simple Dashboard module. 

class user_dashboard // plugin-folder + '_url'
{
	private $title;

	public $chartCaption = LAN_WEBSITE_STATUS;

	function chart() 
	{
		$config = array();
		$tp = e107::getParser();

		$config[] = array(
			0 => array(	'text'		=> $this->renderChart(), 'caption'	=> $tp->toGlyph('fa-signal').' '.LAN_STATS),
			1 => array('caption'    =>$tp->toGlyph('fa-user').' '.LAN_ONLINE.' ('.$this->renderOnlineUsers('count').')', 'text'=>$this->renderOnlineUsers()),

			2 => array(	'text'		=> $this->registered('user_new_thismonth'), 'caption'	=> $this->title),
		);
		
		return $config;
	}


	private function renderChart()
	{
		if (e107::isInstalled('log'))
		{
			return $this->renderStats('log');
		}
		else
		{
			return $this->renderStats('demo');
		}

	}


	private function getStats($type)
	{

		if($type == 'demo')
		{
			$data = array();

			$months = e107::getDate()->terms('month');

			foreach($months as $month)
			{
				// We need only the first 6 months for demo.
				if (!empty($data['labels']) && count($data['labels']) >= 6)
				{
					continue;
				}
				$data['labels'][] = $month;
			}

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

				$log_id = $value['log_id'];
				$log_data = $value['log_data'];
			//	extract($value);

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
			$text .= "<div class='center'><small>".ADLAN_170."<a class='btn btn-xs btn-mini' href='".e_ADMIN."plugin.php?mode=avail&action=list'>".ADLAN_171."</a></small></div>";
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













	/**
	 * TODO Switch to line-filled graph and add unactivated users also.
	 * @param string $when
	 * @param string $id
	 * @return string
	 */
	function registered($id=null)
	{
		$cht = e107::getChart();	
		$sql = e107::getDb();


		$cht->setProvider('google');
		
		$width='100%'; $height = 380; 
	
		$data[] = array('Day', "Registered" );
		
		$amt = array();
		
	//	if($when == 'this')
		{
			$month_start = strtotime('first day of this month', mktime(0,0,0));		
			$month_end = strtotime('last day of this month', mktime(23,59,59));
		}
		
	/*	if($when == 'last')
		{
			$month_start = strtotime('first day of last month', mktime(0,0,0));		
			$month_end = strtotime('last day of last month', mktime(23,59,59));	
		}*/
		
		if(!$sql->gen("SELECT user_id,user_ban,user_join FROM `#user` WHERE user_join BETWEEN ".$month_start." AND ".$month_end." AND user_ban = 0"))
		{
			return false;
		}

		while($row = $sql->fetch())
		{

			$diz = date('j', $row['user_join']);

			if(!isset($amt[$diz]))
			{
				$amt[$diz] = 0;
			}

			$amt[$diz] += 1; 
			$dateName[$diz] = date('jS', $row['user_join']);
		}

		$monthNumber = date('n',$month_start);
		$yearNumber = date('Y', $month_start);
	//	print_a($monthNumber);

		$sum = array_sum($amt);
		
	//	$this->title = 'Registered '.date('M Y',$month_start).' ('.$sum.')';

		$this->title = UC_LAN_9.' ('.$sum.')';
	
		$totalDays = date('t', $month_start);
	
		for ($i=1; $i < ($totalDays +1); $i++) 
		{
			$diz = date('D jS', mktime(1,1,1,$monthNumber,$i, $yearNumber));
			$val = !empty($amt[$i]) ? $amt[$i] : 0;
			$data[] = array($diz, $val); //	$dateName[$i]
			$ticks[] = $i;
		}
		
	//	print_a($data);
			
		$options = array(
			'chartArea'	=>array('left'=>'60', 'width'=>'90%', 'top'=>'25'),
			'legend'	=> array('position'=> 'none', 'alignment'=>'center', 'textStyle' => array('fontSize' => 14, 'color' => '#ccc')),
			'vAxis'		=> array('title'=> UC_LAN_9, 'minValue'=>0, 'maxValue'=>10, 'titleFontSize'=>16, 'titleTextStyle'=>array('color' => '#ccc'), 'gridlines'=>array('color'=>'#696969', 'count'=>5), 'format'=>'', 'textStyle'=>array('color' => '#ccc') ),
			'hAxis'		=> array('title'=>date('M Y', $month_start), 'slantedText'=>true, 'slantedTextAngle'=>60, 'ticks'=>$ticks, 'titleFontSize'=>14, 'titleTextStyle'=>array('color' => '#ccc'), 'gridlines' => array('color'=>'transparent'), 'textStyle'=>array('color' => '#ccc') ),
			'colors'	=> array('#77acd9','#EDA0B6', '#EE8D21', '#5CB85C'),
			'animation'	=> array('duration'=>1000, 'easing' => 'out'), 
			'areaOpacity'	=> 0.8,
	
			'backgroundColor' => array('fill' => 'transparent' )
		);
		//
		$cht->setType('column');
		$cht->setOptions($options);
		$cht->setData($data);
		
		// redraw to fix sizing issue.
	/*	e107::js('footer-inline', "
		
			
			$('a[data-toggle=\"tab\"]').on('shown.bs.tab', function (e) {
			  	//	drawLast();
			  		drawThismonth();
			})
						
			
		");*/
		
		
		return "<div class='height:50%'>".$cht->render($id, $width, $height)."</div>";
		
		// return "<div class='height:50%'>".$cht->render('projection', 320, 380)."</div>";
		
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


		// Fixes #3239: The array merge didn't work correctly by using the + operator
		$online = $ol->userList();
		$online = array_merge($online, $ol->guestList());

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


	private function renderOnlineName($val)
	{
		if($val==0)
		{
			return LAN_GUEST;
		}
		return $val;
	}

	
}