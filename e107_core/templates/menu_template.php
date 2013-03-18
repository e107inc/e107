<?php


#### Panel Template - Used by menu_class.php  for Custom Menu Content. 


	$MENU_TEMPLATE['default']['start'] 					= ''; 
	$MENU_TEMPLATE['default']['body'] 					= '{CMENUBODY}'; 
	$MENU_TEMPLATE['default']['noTableRender'] 			= true;


	$MENU_TEMPLATE['button']['start'] 					= '<div class="cpage-menu">'; 
	$MENU_TEMPLATE['button']['body'] 					= '{CMENUBODY}{CPAGEBUTTON}'; 
	$MENU_TEMPLATE['buttom-image']['end'] 				= '</div>'; 
	$MENU_TEMPLATE['button']['noTableRender'] 				= false;

	$MENU_TEMPLATE['buttom-image']['start'] 			= '<div class="cpage-menu">'; 
	$MENU_TEMPLATE['buttom-image']['body'] 				= '{CMENUIMAGE}{CPAGEBUTTON}'; 
	$MENU_TEMPLATE['buttom-image']['end'] 				= '</div>'; 
	$MENU_TEMPLATE['buttom-image']['noTableRender'] 	= false;





?>