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



	$MENU_TEMPLATE['2-column_1:1_text-left']['start'] 	= '{SETIMAGE: w=700&h=450}<div class="row">';
	$MENU_TEMPLATE['2-column_1:1_text-left']['body'] 	= '			
													       <div class="cpage-menu col-lg-6 col-md-6 col-sm-6"><h2>{CMENUICON}{CMENUTITLE}</h2>{CMENUBODY}<p>{CPAGEBUTTON}</p></div>
													       <div class="cpage-menu col-lg-6 col-md-6 col-sm-6">{CMENUIMAGE}</div>
													       '; 
	$MENU_TEMPLATE['2-column_1:1_text-left']['end'] 	= '</div>';
	
	
	$MENU_TEMPLATE['2-column_1:1_text-right']['start'] = '{SETIMAGE: w=700&h=450}<div class="row">';
	$MENU_TEMPLATE['2-column_1:1_text-right']['body'] 	= '
															<div class="cpage-menu col-lg-6 col-md-6 col-sm-6">{CMENUIMAGE}</div>
															<div class="cpage-menu col-lg-6 col-md-6 col-sm-6"><h2>{CMENUICON}{CMENUTITLE}</h2>{CMENUBODY}<p>{CPAGEBUTTON}</p></div>
														'; 		
	$MENU_TEMPLATE['2-column_1:1_text-right']['end'] 	= '</div>';
          
 
	$MENU_TEMPLATE['2-column_2:1_text-left']['start'] 	= '<div class="row">';
	$MENU_TEMPLATE['2-column_2:1_text-left']['body'] 	= '			
													       <div class="cpage-menu col-lg-8 col-md-8"><h4>{CMENUICON}{CMENUTITLE}</h4>{CMENUBODY}</div>
													       <div class="cpage-menu col-lg-4 col-md-4">
													       <a class="btn btn-lg btn-primary pull-right" href="{CPAGEBUTTON=href}">'.LAN_READ_MORE.'</a>
													       </div>
													       '; 
	$MENU_TEMPLATE['2-column_2:1_text-left']['end'] 	= '</div>';
 
 
 
       
         
	
	
