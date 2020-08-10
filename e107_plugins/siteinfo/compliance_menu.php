<?php

if (!defined('e107_INIT')) { exit; }

$text = "<div style='text-align:center'>
	<a href='http://validator.w3.org/check?uri=".e_SELF.(e_QUERY ? '?'.e_QUERY : '')."'><img src='".e_PLUGIN_ABS."siteinfo/images/valid-xhtml11.png' alt='Valid XHTML 1.1!' height='31' width='88' /></a><br />
	<a href='http://jigsaw.w3.org/css-validator/validator?uri=".e_SELF.(e_QUERY ? '?'.e_QUERY : '')."'><img src='".e_PLUGIN_ABS."siteinfo/images/vcss.png' alt='Valid CSS!' height='31' width='88' /></a>
	</div>";
$caption = (file_exists(THEME."images/compliance_menu.png") ? "<img src='".THEME_ABS."images/compliance_menu.png' alt='' /> ".COMPLIANCE_L1 : COMPLIANCE_L1);
$ns->tablerender($caption, $text, 'compliance');
