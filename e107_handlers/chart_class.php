<?php
 /*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Chart Class for e107. @see https://github.com/FVANCOP/ChartNew.js for details. 
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

  		$options = array('bezierCurve' => false);
  				'
		
		$cht = e107::getChart();
		$cht->setType('line');
  		$cht->setOptions($options);
		$cht->setData($data,'canvas');
		echo $cht->render('canvas');
  */
 
/*
 *   var allopts = {
	//Boolean - If we show the scale above the chart data	  -> Default value Changed
  scaleOverlay : true,
	//Boolean - If we want to override with a hard coded scale
	scaleOverride : false,
	//** Required if scaleOverride is true **
	//Number - The number of steps in a hard coded scale
	scaleSteps : null,
	//Number - The value jump in the hard coded scale
	scaleStepWidth : null,
	//Number - The scale starting value
	scaleStartValue : null,
	//String - Colour of the scale line	
	scaleLineColor : "rgba(0,0,0,.1)",
	//Number - Pixel width of the scale line	
	scaleLineWidth : 1,
	//Boolean - Whether to show labels on the scale	
	scaleShowLabels : true,
	//Interpolated JS string - can access value
	scaleLabel : "<%=value%>",
	//String - Scale label font declaration for the scale label
	scaleFontFamily : "'Arial'",
	//Number - Scale label font size in pixels	
	scaleFontSize : 12,
	//String - Scale label font weight style	
	scaleFontStyle : "normal",
	//String - Scale label font colour	
	scaleFontColor : "#666",	
	///Boolean - Whether grid lines are shown across the chart
	scaleShowGridLines : true,
	//String - Colour of the grid lines
	scaleGridLineColor : "rgba(0,0,0,.05)",
	//Number - Width of the grid lines
	scaleGridLineWidth : 1,	
	//Boolean - Whether the line is curved between points -> Default value Changed 
	bezierCurve : false,
	//Boolean - Whether to show a dot for each point -> Default value Changed
	pointDot : false,
	//Number - Radius of each point dot in pixels
	pointDotRadius : 3,
	//Number - Pixel width of point dot stroke
	pointDotStrokeWidth : 1,
	//Boolean - Whether to show a stroke for datasets
	datasetStroke : true,
	//Number - Pixel width of dataset stroke
	datasetStrokeWidth : 2,
	//Boolean - Whether to fill the dataset with a colour
	datasetFill : true,
	//Boolean - Whether to animate the chart             -> Default value changed
	animation : false,
	//Number - Number of animation steps
	animationSteps : 60,
	//String - Animation easing effect
	animationEasing : "easeOutQuart",
	//Function - Fires when the animation is complete
	onAnimationComplete : null,
  canvasBorders : true,
  canvasBordersWidth : 30,
  canvasBordersColor : "black",
  yAxisLeft : true,
  yAxisRight : true,
  yAxisLabel : "Y axis",
  yAxisFontFamily : "'Arial'",
	yAxisFontSize : 50,
	yAxisFontStyle : "normal",
	yAxisFontColor : "#666",
  xAxisLabel : "",
	xAxisFontFamily : "'Arial'",
	xAxisFontSize : 16,
	xAxisFontStyle : "normal",
	xAxisFontColor : "#666",
  yAxisUnit : "UNIT",
	yAxisUnitFontFamily : "'Arial'",
	yAxisUnitFontSize : 12,
	yAxisUnitFontStyle : "normal",
	yAxisUnitFontColor : "#666",
  graphTitle : "",
	graphTitleFontFamily : "'Arial'",
	graphTitleFontSize : 24,
	graphTitleFontStyle : "bold",
	graphTitleFontColor : "#666",
  graphSubTitle : "",
	graphSubTitleFontFamily : "'Arial'",
	graphSubTitleFontSize : 18,
	graphSubTitleFontStyle : "normal",
	graphSubTitleFontColor : "#666",
  footNote : "Footnote",
	footNoteFontFamily : "'Arial'",
	footNoteFontSize : 50,
	footNoteFontStyle : "bold",
	footNoteFontColor : "#666",
  legend : true,
	legendFontFamily : "'Arial'",
	legendFontSize : 18,
	legendFontStyle : "normal",
	legendFontColor : "#666",
  legendBlockSize : 30,
  legendBorders : true,
  legendBordersWidth : 30,
  legendBordersColor : "#666",
  //  ADDED PARAMETERS 
  graphMin : "DEFAULT",
  graphMax : "DEFAULT"
 */ 

 
 
/**
 * Using with Google Charts: 
 * $cht = e107::getChart();
	$cht->setProvider('google');
	$cht->setType('line');
	$cht->setOptions('demo');
	$cht->setData('demo','canvas');
	$text = $cht->render('canvas');
 */
class e_chart
{
	protected $id;
	protected $data = null;
	protected $type = 'line';
	protected $options = array('scaleFontSize' => 14, 'annotateDisplay' => true, 'bezierCurve' => true, 'inGraphDataShow'=>false);
	protected $provider = null;
	
	
	function __construct()
	{
	//	e107::js('core','chart/Chart.min.js','jquery');	
	
	
	
		
		
	
		// e107::css('inline','canvas.e-graph {  width: 100% !important;  max-width: 800px;  height: auto !important; 	}');	
	}		
	
	public function setProvider($type = null)
	{
		if(!empty($type))
		{
			$this->provider = $type;	
		}	
	}
	
	public function getProvider()
	{
		return $this->provider;	
	}
	
	
	private function getData()
	{
		return json_encode($this->data);
	}
	
	private function getOptions()
	{
		return json_encode($this->options);	
	}

	/**
	 * Set the type of graph
	 * @param string $type - line | bar | pie | radar | doughnut | polar
	 * @return $this
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
	 * @return $this
	 */
	public function setData($data)
	{
	//	$this->id = $id;
		if($data == 'demo')
		{
			$this->data = $this->getDemoData();	
		}
		else
		{
			$this->data = $data;	
		}
		
		return $this;		
	}
	
	private function getDemoData()
	{
			$data = array();

			if($this->provider == 'google')
			{
				
				$data = array(
					array('Year', 'Sales', 'Expenses'),
					array('2013',  1000,      400),
					array('2014',  1170,      460),
					array('2015',  660,       1120),
					array('2016',  1030,      540)
				);
					
				return $data;
			}
			
			
		
			$data['labels'] 	= array("January","February","March","April","May","June","July");

			$data['datasets'][]	= array(
								'fillColor' 		=> "rgba(220,220,220,0.5)",
								'strokeColor'  		=>  "rgba(220,220,220,1)",
								'pointColor '  		=>  "rgba(220,220,220,1)",
								'pointStrokeColor'  =>  "#fff",
								'data'				=> array(65,59,90,81,56,55,40),
								'title'				=> "Visits"
				
			);
			
			$data['datasets'][]	= array(
								'fillColor' 		=> "rgba(151,187,205,0.5)",
								'strokeColor'  		=>  "rgba(151,187,205,1)",
								'pointColor '  		=>  "rgba(151,187,205,1)",
								'pointStrokeColor'  =>  "#fff",
								'data'				=> array(28,48,40,19,96,27,100),
								'title'				=> "Unique Visits"		
			);	
			
			return $data;	
		
		
	}


	public function setOptions($data)
	{
		
		if($this->provider == 'google' && $data == 'demo')
		{
			$this->options =	array(
				'title' => 'Company Performance',
				'hAxis' => array('title'=>'Year', 'titleTextStyle'=>array('color'=>'#333')),
				'vAxis'	=> array('minValue'=>0)
			);
			
		}
		else 
		{
			$this->options = $data;	
		}
		
		
		return $this;		
	}
	
	/**
	 * Render Graph
	 * @param string $id of canvas
	 * @param integer $width
	 * @param integer $height
	 * @return null
	 */
	public function render($id, $width='100%',$height=300)
	{
		
		
		if($this->data == null)
		{
			return "<div class='alert alert-info alert-block'>No chart data provided</div>";	
		}
		
		
		if($this->provider == 'google')
		{
			$this->options['width'] = $width;
			$this->options['height']= $height;

			$fName = 'draw'.ucfirst($id);	
				
		      $js = " google.load('visualization', '1', {packages:['corechart']});
			      google.setOnLoadCallback(".$fName.");
			      function ".$fName."() {
			        var data = google.visualization.arrayToDataTable(".$this->getData().");
			
			        var options = ".$this->getOptions()." ;
					";
			
			
				switch ($this->type) 
				{
		
					case 'bar':					
						//
					break;
		
					case 'column':
						$js .= "var chart = new google.visualization.ColumnChart(document.getElementById('".$id."'));	";
						
					break;
		
					case 'polar':
						//TODO
					break;
		
					case 'doughnut':
						//
					break;
					
					case 'pie':
						$js .= "var chart = new google.visualization.PieChart(document.getElementById('".$id."'));	";
					break;
		
					default:
					case 'line':
					case 'area':
						
						$js .= "var chart = new google.visualization.AreaChart(document.getElementById('".$id."'));	";
						
					break;
				}
			
	
				$js .= "
			        chart.draw(data, options);
			      }

				$(window).resize(function(){
					  ".$fName."();
					
					});

		      ";

			e107::js('footer','https://www.google.com/jsapi');
			e107::js('footer-inline', $js);

			return "<div class='e-graph e-chart' id='".$id."' style='width: ".$width."; height: ".$height."px;'></div>";


		}
		
		
		e107::js('core','chart/ChartNew.js','jquery');		
		
		$js = "var ChartData = ".$this->getData()."\n";
		$js .=  'var ChartOptions = '.$this->getOptions(); 
		$js .= ";\n";
		
		switch ($this->type) 
		{

			case 'bar':
				
				$js .= 'var myLine = new Chart(document.getElementById("'.$id.'").getContext("2d")).Bar(ChartData, ChartOptions);';
			break;

			case 'radar':
				//TODO
			break;

			case 'polar':
				//TODO
			break;

			case 'doughnut':
				$js .= 'var myDoughnut = new Chart(document.getElementById("'.$id.'").getContext("2d")).Doughnut(ChartData, ChartOptions);';	
			break;
			
			case 'pie':
				//TODO
			break;

			default:
			case 'line':
				
				$js .= '

					var myLine = new Chart(document.getElementById("'.$id.'").getContext("2d")).Line(ChartData, ChartOptions);
									
				';
				
			break;
		}
			
	

		
		// Auto-resize the canvas. //TODO Get it working with multiple instances. 
		e107::js('footer-inline', "
			var c = $('#".$id."');
		    var ct = c.get(0).getContext('2d');
		    var container = $(c).parent();
		
		    //Run function when browser resizes
		    $(window).resize( respondCanvas );
		
		    function respondCanvas(){ 
		        c.attr('width', $(container).width() ); //max width
		        c.attr('height', $(container).height() ); //max height
		
		        //Call a function to redraw other content (texts, images etc)
		    }
		
		    //Initial call 
		    respondCanvas();
   		 ");
		

		e107::js('footer-inline',$js);

		return '<canvas class="e-graph e-chart" id="'.$id.'" height="'.$height.'" width="'.$width.'" >HTML5 Canvas not supported.</canvas>';
		
	}
	
	
	
	
	
} 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
?>
 