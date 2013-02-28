<?php
// $Id$

/**
 * Example usage (valid news data + option for full URL)
 * {URL=news/view/item|news_id=1&news_sef=sef-string&category_id=1&category_sef=category-sef&options[full]=1}
 */
function url_shortcode($parm)
{
	list($route, $parms) = eHelper::scDualParams($parm);
	if(empty($route)) return '';
	
	$options = array();
	if(isset($parms['options'])) 
	{
		$options = $parms['options'];
		unset($parms['options']);
	}
	return e107::getUrl()->create($route, $parms, $options);
}
