<?php

function navigation_shortcode($parm='')
{
	$types = array(
		'main'		=> 1,
		'side'		=> 2,
		'footer'	=> 3,
		'alt'		=> 4,
		'alt5'		=> 5,
		'alt6'		=> 6,
	);
	
	$category 		= varset($types[$parm], 1);
	$tmpl 			= vartrue($parm, 'main');
	$nav			= e107::getNav();
	
	$template		= e107::getCoreTemplate('navigation', $tmpl);	
	$data 			= $nav->initData($category);
//	$data 			= $nav->collection($category);

	return $nav->render($data, $template);

}
	