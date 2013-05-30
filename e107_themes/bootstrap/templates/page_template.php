<?php
/*
 *  <ul class="nav nav-list bs-docs-sidenav">
          <li><a href="#download-bootstrap"><i class="icon-chevron-right"></i> Download</a></li>
          <li><a href="#file-structure"><i class="icon-chevron-right"></i> File structure</a></li>
          <li><a href="#contents"><i class="icon-chevron-right"></i> What's included</a></li>
          <li><a href="#html-template"><i class="icon-chevron-right"></i> HTML template</a></li>
          <li><a href="#examples"><i class="icon-chevron-right"></i> Examples</a></li>
          <li><a href="#what-next"><i class="icon-chevron-right"></i> What next?</a></li>
        </ul>
 */
	$PAGE_TEMPLATE['navdoc']['caption']				= " ";
	
	$PAGE_TEMPLATE['navdoc']['start'] 					= '<ul class="nav nav-list bs-docs-sidenav">';
	
// Main Link
	$PAGE_TEMPLATE['navdoc']['item']				 	= '
														<li>
															<a role="button" href="{LINK_ANCHOR}" ><i class="icon-chevron-right"></i>
															 {LINK_NAME} 
															</a> 
														</li>
													';
	
// Main Link which has a sub menu. 
	$PAGE_TEMPLATE['navdoc']['item_submenu']	 		= '
														<li>
															<a role="button" href="{LINK_URL}" >
															 {LINK_NAME} 
															</a> 
															{LINK_SUB}
														</li>
													';
	
	$PAGE_TEMPLATE['navdoc']['item_submenu_active'] 	= '
														<li class="active">
															<a role="button"  href="{LINK_URL}">
															 {LINK_IMAGE} {LINK_NAME}
															</a>
															{LINK_SUB}
														</li>
													';	
	
	$PAGE_TEMPLATE['navdoc']['item_active'] 			= '
														<li class="active">
															<a crole="button" href="{LINK_URL}">
															 {LINK_IMAGE} {LINK_NAME}
															</a>
														</li>
													';	
	
	$PAGE_TEMPLATE['navdoc']['end'] 					= '</ul>';		
	
	
	$PAGE_TEMPLATE['navdoc']['submenu_start'] 			= '<ul class="page-navdoc" id="page-navdoc-{LINK_PARENT}" role="menu" >';
	
	
	$PAGE_TEMPLATE['navdoc']['submenu_item'] 			= '
														<li role="menuitem" >
															<a href="{LINK_URL}">{LINK_IMAGE}{LINK_NAME}</a>
															{LINK_SUB}
														</li>
													';
	
	$PAGE_TEMPLATE['navdoc']['submenu_loweritem'] 		= '
														<li role="menuitem" >
															<a href="{LINK_URL}">{LINK_IMAGE}{LINK_NAME}</a>
															{LINK_SUB}
														</li>
													';
	$PAGE_TEMPLATE['navdoc']['submenu_loweritem_active'] 		= '
														<li role="menuitem" class="active">
															<a href="{LINK_URL}">{LINK_IMAGE}{LINK_NAME}</a>
															{LINK_SUB}
														</li>
													';
	
	$PAGE_TEMPLATE['navdoc']['submenu_item_active'] 	= '
														<li role="menuitem" class="active">
															<a href="{LINK_URL}">{LINK_IMAGE}{LINK_NAME}</a>
															{LINK_SUB}
														</li>
													';
	
	$PAGE_TEMPLATE['navdoc']['submenu_end'] 			= '</ul>';	


?>