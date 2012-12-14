<?php

function navigation_shortcode($parm='')
{
	$types = array(
		'main'		=> 1,
		'side'		=> 2,
		'footer'	=> 3,
		'alt'		=> 4
	);
	
	$category = varset($types[$parm], 1);
	$tmpl = vartrue($parm, 'main');
	
	//$data = e107::getNav()->getData($cat);		
			
	//return e107::getNav()->render($data, $tmpl);			
	$nav = e107::getNav();
	
	$template		= e107::getCoreTemplate('navigation', $tmpl);	
	$data 			= $nav->collection($category);
	
	return $nav->render($data, $template);
}
	