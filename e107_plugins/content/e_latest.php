<?php
if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN.'content/languages/'.e_LANGUAGE.'/lan_content_admin.php');
$plugintable = "pcontent";
$reported_content = $sql -> db_Count($plugintable, '(*)', "WHERE content_refer='sa' ");
$text .= "
<div style='padding-bottom: 2px;'>
<img src='".e_PLUGIN_ABS."content/images/content_16.png' style='width: 16px; height: 16px; vertical-align: bottom' alt='' />
";
if($reported_content) 
{
	$text .= " <a href='".e_PLUGIN_ABS."content/admin_content_config.php?submitted'>".CONTENT_LATEST_LAN_1." $reported_content</a>";
} 
else 
{
	$text .= CONTENT_LATEST_LAN_1." ".$reported_content;
}
$text .= "</div>";

?>