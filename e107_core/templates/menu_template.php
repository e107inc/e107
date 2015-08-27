<?php


#### Panel Template - Used by menu_class.php  for Custom Menu Content. 


	$MENU_TEMPLATE['default']['start'] 					= ''; 
	$MENU_TEMPLATE['default']['body'] 					= '{CMENUBODY}'; 
	$MENU_TEMPLATE['default']['end'] 					= ''; 

	$MENU_TEMPLATE['button']['start'] 					= '<div class="cpage-menu">'; 
	$MENU_TEMPLATE['button']['body'] 					= '<div>{CMENUBODY}</div>{CPAGEBUTTON}';
	$MENU_TEMPLATE['button']['end'] 					= '</div>'; 

	### Additional control over image thumbnailing is possible via SETIMAGE e.g. {SETIMAGE: w=200&h=150&crop=1}
	$MENU_TEMPLATE['buttom-image']['start'] 			= '<div class="cpage-menu">'; 
	$MENU_TEMPLATE['buttom-image']['body'] 				= '<div>{CMENUIMAGE}</div>{CPAGEBUTTON}';
	$MENU_TEMPLATE['buttom-image']['end'] 				= '</div>'; 

	$MENU_TEMPLATE['image-only']['start'] 					= ''; 
	$MENU_TEMPLATE['image-only']['body'] 					= '{CMENUIMAGE}'; 
	$MENU_TEMPLATE['image-only']['end'] 					= ''; 	
?>