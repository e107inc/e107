<?php
$plugintable = "pcontent";
$reported_content = $sql -> db_Count($plugintable, '(*)', "WHERE content_refer='sa' ");
$text .= "
<div style='padding-bottom: 2px;'>
<img src='".e_PLUGIN."content/images/articles_16.png' style='width: 16px; height: 16px; vertical-align: bottom' />
";
if($reported_content) {
	$text .= " <a href='".e_PLUGIN."content/admin_content_config.php?type.0.sa'>Submitted Content Items: $reported_content</a>";
} else {
	$text .= "Submitted Content Items: ".$reported_content;
}
$text .= "</div>";
?>