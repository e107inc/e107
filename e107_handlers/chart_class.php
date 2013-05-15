<?php
 /*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Chart Class for e107. see http://www.chartjs.org for details. 
 *
 */
 
 /**
  * 	
  * 
  * Example of Usage: 
  * 
  * 	$data = array();
		
		$data['labels'] 	= array("January","February","March","April","May","June","July");
		
		
		$data['datasets'][]	= array(
							'fillColor' 		=> "rgba(220,220,220,0.5)",
							'strokeColor'  		=>  "rgba(220,220,220,1)",
							'pointColor '  		=>  "rgba(220,220,220,1)",
							'pointStrokeColor'  =>  "#fff",
							'data'				=> array(65,59,90,81,56,55,40)	
			
		);
		
		$data['datasets'][]	= array(
							'fillColor' 		=> "rgba(151,187,205,0.5)",
							'strokeColor'  		=>  "rgba(151,187,205,1)",
							'pointColor '  		=>  "rgba(151,187,205,1)",
							'pointStrokeColor'  =>  "#fff",
							'data'				=> array(28,48,40,19,96,27,100)		
		);

		
		$cht = e107::getChart();
		$cht->setType('line');
		$cht->setData($data,'canvas');
		echo $cht->render('canvas');
  */
 
 

class e_chart
{
	protected $id;
	protected $data = null;
	protected $type = 'line';
	
	
	function __construct()
	{
		e107::js('core','chart/Chart.min.js','jquery');	
		e107::css('inline','canvas.e-graph {  width: 100% !important;  max-width: 800px;  height: auto !important; 	}');	
	}		
	
	
	private function getData()
	{
		return json_encode($this->data);
	}
	
	/**
	 * Set the type of graph
	 * @param string $type - line | bar | pie | radar | doughnut | polar 
	 */
	public function setType($type)
	{
		
		$this->type = $type;
		return $this;	
	}


	/**
	 * Set the data values
	 * @param array $data
	 * @param string $id of canvas element
	 */
	public function setData($data,$id)
	{
		$this->id = $id;
		$this->data = $data;
		return $this;		
	}
	
	/**
	 * Render Graph
	 * @param string $id of canvas
	 * @param integer $width
	 * @param integer $height
	 * @return null
	 */
	public function render($id, $width=800,$height=300)
	{
		
		if($this->data == null)
		{
			return "No chart data provided";	
		}
		
		
		$js = "var ChartData = ".$this->getData()."\n";
		
		switch ($this->type) 
		{

			case 'bar':
				
				$js .= 'var myLine = new Chart(document.getElementById("'.$id.'").getContext("2d")).Bar(ChartData);';
			break;

			case 'radar':
				//TODO
			break;

			case 'polar':
				//TODO
			break;

			case 'doughnut':
				$js .= 'var myDoughnut = new Chart(document.getElementById("'.$id.'").getContext("2d")).Doughnut(ChartData);';	
			break;
			
			case 'pie':
				//TODO
			break;

			default:
			case 'line':
				
				
				//TODO Chart Options. 
				$js .= '
				//	var lineChartData = '.$this->getData().'
				//	var ChartOptions = "{ scaleFontSize : 18 }" 
					var myLine = new Chart(document.getElementById("'.$id.'").getContext("2d")).Line(ChartData);
									
				';
				
			break;
		}
			
	

		e107::js('footer-inline',$js);

		return '<canvas class="e-graph" id="'.$id.'" height="'.$height.'" width="'.$width.'" >HTML5 Canvas not supported.</canvas>';
		
	}
	
	
	
	
	
} 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
?>
 