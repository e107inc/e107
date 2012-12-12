<?php

function navigation_shortcode($parm='')
{
	$data = e107::getNav()->getData(1);		
	return e107::getNav()->render($data);				
}
	