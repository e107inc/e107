<?php

function navigation_shortcode($parm='')
{
	$types = array(
		'main'		=> 1,
		'side'		=> 2,
		'footer'	=> 3,
		'alt'		=> 4
	);
	
	$cat = varset($types[$parm], 1);
	$tmpl = vartrue($parm, 'main');
	
	$data = e107::getNav()->getData($cat);		
			
	return e107::getNav()->render($data, $tmpl);				
}
	