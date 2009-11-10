<?php
/**
 *  Fan Box - Become a Fan Of...
 *
 * http://wiki.developers.facebook.com/index.php/Fan_Box
 * this must be later inserted in the Control Panel where User can chose below Parameters  :
 * stream = Set to 1 to display stream stories in the Fan Box or 0 to hide stream stories. (Default value is 1.)
 * connections = The number of fans to display in the Fan Box. Specifying 0 hides the list of fans in the Fan Box. You cannot display more than 100 fans. (Default value is 10 connections.)
 *
 * see the render_fun_box() function
 *
 */

if (!defined('e107_INIT'))
{
	exit;
}

include_once(e_PLUGIN.'facebook/facebook_function.php');

if (isset($_POST['fb_sig_in_canvas']))
{
	return;
}

if (is_fb())
{
	
	$html = '';
	
	$html .= Render_Fun_Box('0', '10', '200px');
	
	$caption = 'Fan Box';
	
	$ns->tablerender($caption, $html);

}

?>