<?php
//error_reporting(E_ALL);

if (!defined('e107_INIT'))
{
	exit;
}

include_once (e_PLUGIN.'facebook/facebook_function.php');

if (isset($_POST['fb_sig_in_canvas']))
{
	return;
}

$fb = e107::getSingleton('e_facebook',e_PLUGIN.'facebook/facebook_function.php');
$html = $fb->fb_login();

$caption = 'Facebook';
// $text = $tp->parseTemplate($html, true, $facebook_shortcodes);

$ns->tablerender($caption, $html);

?>