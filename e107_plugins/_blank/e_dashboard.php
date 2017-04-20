<?php
if (!defined('e107_INIT')) { exit; }


class _blank_dashboard // include plugin-folder in the name.
{
	private $title; // dynamic title.
	
	function chart()
	{
		$config = array();


		$config[] = array(

			'text'		=> $this->activity(),
			'caption'	=> $this->title,
		);

		return $config;
	}


	/**
	 * Non-functional example.
	 * Chart of last 30 days.
	 * @return bool|string
	 */
	function activity()
	{
		$sql = e107::getDb();
		$cht = e107::getChart();
		$cht->setProvider('google');


		$id             = '_blank_activity_chart';

		$amt            = array();
		$ticks          = array();
		$data           = array();

		$width          = '100%';
		$height         = 450;

		$month_start    = strtotime('1 month ago');
		$month_end      = time()+7200;

		$label          = date('M Y', $month_start)." - ".date('M Y', $month_end);

		if(!$sql->gen("SELECT id,datestamp,referred FROM `#_blank` WHERE datestamp BETWEEN ".$month_start." AND ".$month_end))
		{
			return false;
		}

		while($row = $sql->fetch())
		{

			$key = date('Y-n-j', $row['datestamp']);

			switch($row['referred'])
			{
				case "Facebook":
					$amt[$key]['facebook'] += 1;
					break;

				case "Google":
					$amt[$key]['google'] += 1;
					break;

				case "Bing":
					$amt[$key]['bing'] += 1;
					break;

				case "Friend":
					$amt[$key]['friend'] += 1;
				break;

				default:
					$amt[$key]['other'] += 1;

			}

			$dateName[$key] = date('jS', $row['datestamp']);
		}

		$sum = array_sum($amt);

		$data[] = array('Day', "Other", "Friend", "Bing", "Google", "Facebook" );


		$this->title = 'Referrals ('.$sum.')';

	//	$c = 0;
		foreach($amt as $k=>$v)
		{
			list($yearNumber,$monthNumber,$day) = explode('-',$k);
			$diz = date('D jS', mktime(1,1,1,$monthNumber,$day, $yearNumber));
			$data[] = array($diz, $amt[$k]['other'], $amt[$k]['friend'], $amt[$k]['bing'], $amt[$k]['google'], $amt[$k]['facebook']); //	$dateName[$i]
			$ticks[] = $k;
		//	$c++;
		}

		$options = array(
			'chartArea'	=>array('left'=>'40', 'right'=>20, 'width'=>'100%', 'top'=>'30'),
			'legend'	=> array('position'=> 'none', 'alignment'=>'center', 'textStyle' => array('fontSize' => 14, 'color' => '#ccc')),
			'vAxis'		=> array('title'=>null, 'minValue'=>0, 'maxValue'=>10, 'titleFontSize'=>16, 'titleTextStyle'=>array('color' => '#ccc'), 'gridlines'=>array('color'=>'#696969', 'count'=>5), 'format'=>'', 'textStyle'=>array('color' => '#ccc') ),
			'hAxis'		=> array('title'=>$label, 'slantedText'=>true, 'slantedTextAngle'=>60, 'ticks'=>$ticks, 'titleFontSize'=>14, 'titleTextStyle'=>array('color' => '#ccc'), 'gridlines' => array('color'=>'transparent'), 'textStyle'=>array('color' => '#ccc') ),
			'colors'	=> array('#999999', '#0D9071','#FEB801', '#DC493C', '#3B5999'),
			'animation'	=> array('duration'=>1000, 'easing' => 'out'),
			'areaOpacity'	=> 0.8,
			'isStacked' => true,

			'backgroundColor' => array('fill' => 'transparent' )
		);

		$cht->setType('column');
		$cht->setOptions($options);
		$cht->setData($data);


		return "<div>".$cht->render($id, $width, $height)."</div>";

	}
	
	
	function status() // Status Panel in the admin area
	{

		$var[0]['icon'] 	= "<img src='".e_PLUGIN."_blank/images/blank_16.png' alt='' />";
		$var[0]['title'] 	= "My Title";
		$var[0]['url']		= e_PLUGIN_ABS."_blank/_blank.php";
		$var[0]['total'] 	= 10;

		return $var;
	}	
	
	
	function latest() // Latest panel in the admin area.
	{
		$var[0]['icon'] 	= "<img src='".e_PLUGIN."_blank/images/blank_16.png' alt='' />";
		$var[0]['title'] 	= "My Title";
		$var[0]['url']		= e_PLUGIN_ABS."_blank/_blank.php";
		$var[0]['total'] 	= 10;

		return $var;
	}	
	
	
}
?>