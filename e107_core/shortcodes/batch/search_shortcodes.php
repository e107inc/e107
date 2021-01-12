<?php


if(!defined('e107_INIT'))
{
	exit;
}


class search_shortcodes extends e_shortcode
{
	function sc_search_input($parm=null)
	{
		$size = !empty($parm['size']) ? $parm['size'] : 20;
		$class = isset($parm['class']) ? $parm['class'] : 'tbox search';

		return e107::getForm()->text('q','', 50, ['size'=>$size, 'class'=>$class]);
	//	return "<input class='tbox form-control search' type='text' name='q' size='20' value='' maxlength='50' />";
	}


	function sc_search_button($parm=null)
	{
		$class = isset($parm['class']) ? $parm['class'] : 'btn-default btn-secondary button search';
		return e107::getForm()->button('s','Search','submit', LAN_SEARCH, ['class'=>$class]);
	//	return "<input class='btn btn-default btn-secondary button search' type='submit' name='s' value=\"".LAN_SEARCH."\" />";
	}


}