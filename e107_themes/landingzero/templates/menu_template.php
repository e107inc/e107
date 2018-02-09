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
 
 
	$MENU_TEMPLATE['aboutmodal']['start'] 			= '<div id="aboutModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">'; 
	$MENU_TEMPLATE['aboutmodal']['body'] 				= '        	
			<div class="modal-body">
        		<h2 class="text-center">{CMENUTITLE}</h2>
        		<h5 class="text-center">
        		    {CPAGETITLE}
        		</h5>       
        		<p class="text-justify">
							{CMENUBODY}       		
						</p>               
        		<p class="text-center">{CPAGEBUTTON}</p>
        		<br/>
        		<button class="btn btn-primary btn-lg center-block" data-dismiss="modal" aria-hidden="true">'.LAN_LZ_THEME_00.' </button>
        	</div>';
	$MENU_TEMPLATE['aboutmodal']['end'] 				= '        
					</div>
        </div>
    </div>';
		
	/* <p class="text-faded"> need be inserted in menu body */	
	$MENU_TEMPLATE['sectionone']['start'] 			= ''; 
	$MENU_TEMPLATE['sectionone']['body'] 				= '        	
	              <div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 text-center">
                    <h2 class="margin-top-0 text-primary">{CMENUTITLE}</h2>
                    <br>                   
                    <p class="text-faded">{CMENUBODY}</p> 
                    <a href="{CMENUURL}" class="btn btn-default btn-secondary btn-xl page-scroll">{CMENUTEXT}</a>
                </div>';
	$MENU_TEMPLATE['sectionone']['end'] 				= '';		
	
	
	$MENU_TEMPLATE['sectiontwo']['start'] 			= ''; 
	$MENU_TEMPLATE['sectiontwo']['body'] 				= '        	
	             <div class="col-lg-4 col-md-4 text-center">
                    <div class="feature">
                        <i class="icon-lg {CMENUICON=css} wow fadeIn" data-wow-delay=".3s"></i>
                        <h3>{CMENUTITLE}</h3>
                        <p class="text-muted">{CMENUBODY}</p>
                    </div>
                </div>';
	$MENU_TEMPLATE['sectiontwo']['end'] 				= '';		
	
		  
	$MENU_TEMPLATE['verticalfeatures-fadeInRight']['start'] 			= ''; 
	$MENU_TEMPLATE['verticalfeatures-fadeInRight']['body'] 				= '        	
                <div class="media wow fadeInRight">
                    <h3>{CMENUTITLE}</h3>
                    <div class="media-body media-middle">
                        <p>{CMENUBODY}</p>
                    </div>
                    <div class="media-right">
                        <i class="icon-lg {CMENUICON=css}"></i>
                    </div>
                </div>';
	$MENU_TEMPLATE['verticalfeatures-fadeInRight']['end'] 				= '';	         
	
	$MENU_TEMPLATE['verticalfeatures-fadeIn']['start'] 			= ''; 
	$MENU_TEMPLATE['verticalfeatures-fadeIn']['body'] 				= '        	
                <div class="media wow fadeIn">
                    <h3>{CMENUTITLE}</h3>
                    <div class="media-left">
                        <i class="icon-lg {CMENUICON=css}"></i>
                    </div>
                    <div class="media-body media-middle">
                        <p>{CMENUBODY}</p>
                    </div>
                </div>';
	$MENU_TEMPLATE['verticalfeatures-fadeIn']['end'] 				= '';		
	
	$MENU_TEMPLATE['call-to-action']['start'] 			= ''; 
	$MENU_TEMPLATE['call-to-action']['body'] 				= '        	
            <div class="call-to-action">
                <h2 class="text-primary">{CMENUTITLE}</h2>
                <a href="{CMENUURL}" target="ext" class="btn btn-default btn-secondary btn-lg wow flipInX">{CMENUTEXT}</a>
            </div>
            <br>
            <hr/>
            <br>
            <div class="row">
                <div class="col-lg-10 col-lg-offset-1">
                    <div class="row">
                        <h6 class="wide-space text-center">{CPAGETITLE}</h6>
                        {CPAGEBODY}
                    </div>
                </div>
            </div>';
	$MENU_TEMPLATE['call-to-action']['end'] 				= '';	
	
	
	
?>


