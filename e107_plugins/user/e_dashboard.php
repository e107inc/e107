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
	
	function chart() 
	{
		$config = array();
	
		$config[] = array(
			
			'text'		=> $this->registered('thismonth'),
			'caption'	=> $this->title,
		);
		
		return $config;
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

		$this->title = 'New Users ('.$sum.')';
	
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
			'vAxis'		=> array('title'=>'New Users', 'minValue'=>0, 'maxValue'=>10, 'titleFontSize'=>16, 'titleTextStyle'=>array('color' => '#ccc'), 'gridlines'=>array('color'=>'#696969', 'count'=>5), 'format'=>'', 'textStyle'=>array('color' => '#ccc') ),
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
		e107::js('footer-inline', "
		
			
			$('a[data-toggle=\"tab\"]').on('shown.bs.tab', function (e) {
			  	//	drawLast();
			  		drawThismonth();
			})
						
			
		");
		
		
		return "<div class='height:50%'>".$cht->render($id, $width, $height)."</div>";
		
		// return "<div class='height:50%'>".$cht->render('projection', 320, 380)."</div>";
		
	}	

	
}