<?php

if (!defined('e107_INIT')) { exit; }

if(deftrue('BOOTSTRAP'))
{
	$EMAILPRINT_TEMPLATE['ICON_EMAIL']  = "<i class='fas fa-envelope'></i>"; //$tp->toGlyph('fa-envelope',false); 
	$EMAILPRINT_TEMPLATE['ICON_PRINT']  = "<i class='fas fa-print'></i>"; //$tp->toGlyph('fa-print',false);
}
else // BC
{
	$EMAILPRINT_TEMPLATE['ICON_EMAIL']  = "<img src='".e_IMAGE_ABS."generic/email.png'  alt='".LAN_EMAIL_7."'  />";
	$EMAILPRINT_TEMPLATE['ICON_PRINT']  = "<img src='".e_IMAGE_ABS."generic/printer.png'  alt='".LAN_PRINT_1."'  />";	
}

$EMAILPRINT_TEMPLATE['email'] = "<a rel='alternate' class='e-tip hidden-print btn btn-default btn-secondary hidden-print' href='{EMAILICON: url=1}' title='".LAN_EMAIL_7."'>".$EMAILPRINT_TEMPLATE['ICON_EMAIL']."</a>";
$EMAILPRINT_TEMPLATE['print'] = "<a rel='alternate' class='e-tip btn btn-default btn-secondary hidden-print' href='{PRINTICON: url=1}' title='".LAN_PRINT_1."'>".$EMAILPRINT_TEMPLATE['ICON_PRINT']."</a>";
