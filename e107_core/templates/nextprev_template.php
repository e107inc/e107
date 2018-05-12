<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * NEXTPREV shortcode template
*/

/*
 * Default (old) page navigation, key prefix 'default'
 * Shortcodes are lowercase (simple parser)
 */

 
 // XXX LEGACY DEFAULT. 
 
$NEXTPREV_TEMPLATE['default_start'] = '
<!-- Start of Next/Prev -->
<div class="btn-group nextprev ">
';

$NEXTPREV_TEMPLATE['default_end'] = '
</div>
<!-- End of Next/Prev -->
';

//$NEXTPREV_TEMPLATE['default_nav_caption'] = '<span class="nexprev-caption center">{caption}</span>&nbsp;'; XXX - awaiting the new front-end themes & templates
$NEXTPREV_TEMPLATE['default_nav_caption'] = ''; // NP_3.'&nbsp;';

$NEXTPREV_TEMPLATE['default_nav_first'] = '<a class="btn btn-default btn-secondary nextprev-item first" href="{url}" title="{url_label}">{label}</a>';
$NEXTPREV_TEMPLATE['default_nav_prev'] = '<a class="btn btn-default btn-secondary nextprev-item prev" href="{url}" title="{url_label}">{label}</a>';
$NEXTPREV_TEMPLATE['default_nav_last'] = '<a class="btn btn-default btn-secondary nextprev-item last" href="{url}" title="{url_label}">{label}</a>';
$NEXTPREV_TEMPLATE['default_nav_next'] = '<a class="btn btn-default btn-secondary nextprev-item next" href="{url}" title="{url_label}">{label}</a>';

$NEXTPREV_TEMPLATE['default_items_start'] = '';
$NEXTPREV_TEMPLATE['default_item'] = '<a class="btn btn-default btn-secondary nextprev-item" href="{url}" title="{url_label}">{label}</a>';
$NEXTPREV_TEMPLATE['default_item_current'] = '<a class="btn btn-default btn-secondary nextprev-item current active" href="#" onclick="return false;" title="{url_label}">{label}</a>';
$NEXTPREV_TEMPLATE['default_items_end'] = '';

//$NEXTPREV_TEMPLATE['default_separator'] = '<span class="nextprev-sep"><!-- --></span>';
$NEXTPREV_TEMPLATE['default_separator'] = '&nbsp;';


/*  ****************** Default when Bootstrap is enabled ************** */

$NEXTPREV_TEMPLATE['bootstrap_start']			= "<!-- Start of Next/Prev -->\n<nav>\n<ul class='pagination'>";
$NEXTPREV_TEMPLATE['bootstrap_end'] 			= "</ul></nav><!-- End of Next/Prev -->";
$NEXTPREV_TEMPLATE['bootstrap_nav_caption'] 	= '';

$NEXTPREV_TEMPLATE['bootstrap_nav_first'] 		= '<li><a class="first hidden-xs" href="{url}" title="{url_label}">{label}</a></li>';
$NEXTPREV_TEMPLATE['bootstrap_nav_prev'] 		= '<li><a class="prev" href="{url}" title="{url_label}">{label}</a></li>';
$NEXTPREV_TEMPLATE['bootstrap_nav_last'] 		= '<li><a class="last hidden-xs" href="{url}" title="{url_label}">{label}</a></li>';
$NEXTPREV_TEMPLATE['bootstrap_nav_next'] 		= '<li><a class="next" href="{url}" title="{url_label}">{label}</a></li>';

$NEXTPREV_TEMPLATE['bootstrap_items_start'] 	= '';
$NEXTPREV_TEMPLATE['bootstrap_item'] 			= '<li><a class="hidden-xs" href="{url}" title="{url_label}">{label}</a></li>';
$NEXTPREV_TEMPLATE['bootstrap_item_current'] 	= '<li class="active disabled"><a  href="#" onclick="return false;" title="{url_label}">{label}</a></li>';
$NEXTPREV_TEMPLATE['bootstrap_items_end'] 		= '';

$NEXTPREV_TEMPLATE['bootstrap_separator'] 		= '';


/*  ************************************************************ */




























// Basic template - as used in admin theme-manager - find themes. 
//XXX FIXME - use $NEXTPREV_TEMPLATE['basic']['start'] format.??

$NEXTPREV_TEMPLATE['basic_start'] 				= '<!-- Start of Next/Prev --><div class="nextprev-basic btn-group ">';
$NEXTPREV_TEMPLATE['basic_end'] 				= '</div><!-- End of Next/Prev -->';
$NEXTPREV_TEMPLATE['basic_nav_caption'] 		= ''; 
$NEXTPREV_TEMPLATE['basic_nav_first'] 			= '';
$NEXTPREV_TEMPLATE['basic_nav_prev'] 			= '<a class="btn btn-default btn-secondary nextprev-item prev" href="{url}" title="{url_label}" {disabled}><i class="fa fa-backward"></i></a>';
$NEXTPREV_TEMPLATE['basic_nav_last'] 			= ''; 
$NEXTPREV_TEMPLATE['basic_nav_next'] 			= '<a class="btn btn-default btn-secondary nextprev-item next " href="{url}" title="{url_label}" {disabled}><i class="fa fa-forward"></i></a>';
$NEXTPREV_TEMPLATE['basic_items_start'] 		= '';
$NEXTPREV_TEMPLATE['basic_item'] 				= ''; 
$NEXTPREV_TEMPLATE['basic_item_current'] 		= '<a class="btn btn-default btn-secondary">{label}</a>';
$NEXTPREV_TEMPLATE['basic_items_end'] 			= '';
$NEXTPREV_TEMPLATE['basic_separator'] 			= '';




// ADMIN TEMPLATE see admin-ui. 


$NEXTPREV_TEMPLATE['admin_start'] = '<!-- Start of Next/Prev --><div class="pagination nextprev "><ul class="pagination">';

$NEXTPREV_TEMPLATE['admin_end'] = '</ul></div><!-- End of Next/Prev -->';

//$NEXTPREV_TEMPLATE['admin_nav_caption'] = '<span class="nexprev-caption center">{caption}</span>&nbsp;'; XXX - awaiting the new front-end themes & templates
$NEXTPREV_TEMPLATE['admin_nav_caption'] = ''; // NP_3.'&nbsp;';

$NEXTPREV_TEMPLATE['admin_nav_first'] = '<li><a class="nextprev-item first e-tip" href="{url}" title="{url_label}"><i class="fa fa-fast-backward"></i></a></li>';
$NEXTPREV_TEMPLATE['admin_nav_prev'] = '<li><a class="nextprev-item prev e-tip" href="{url}" title="{url_label}"><i class="fa fa-backward"></i></a></li>';
$NEXTPREV_TEMPLATE['admin_nav_last'] = '<li><a class="nextprev-item last e-tip" href="{url}" title="{url_label}"><i class="fa fa-fast-forward"></i></a></li>';
$NEXTPREV_TEMPLATE['admin_nav_next'] = '<li><a class="nextprev-item next e-tip" href="{url}" title="{url_label}"><i class="fa fa-forward"></i></a></li>';

$NEXTPREV_TEMPLATE['admin_items_start'] = '';
$NEXTPREV_TEMPLATE['admin_item'] = '<li><a class="nextprev-item e-tip" href="{url}" title="{url_label}">{label}</a></li>';
$NEXTPREV_TEMPLATE['admin_item_current'] = '<li class="active"><a class="nextprev-item current active" href="#" onclick="return false;" title="{url_label}">{label}</a></li>';
$NEXTPREV_TEMPLATE['admin_items_end'] = '';

//$NEXTPREV_TEMPLATE['admin_separator'] = '<span class="nextprev-sep"><!-- --></span>';
$NEXTPREV_TEMPLATE['admin_separator'] = '&nbsp;';


// ######################################






/*
 * Dropdown page navigation, key prefix 'dropdown'
 * Shortcodes are lowercase (simple parser)
 * TODO - do the slide-down via JS, make it unobtrusive
 */

$NEXTPREV_TEMPLATE['dropdown_start'] = '
<!-- Start of Next/Prev -->
<div class="nextprev form-group form-inline">
';

$NEXTPREV_TEMPLATE['dropdown_end'] = '
</div>
<!-- End of Next/Prev -->
';

//$NEXTPREV_TEMPLATE['default_nav_caption'] = '<span class="nexprev-caption center">{caption}</span>&nbsp;'; XXX - awaiting the new front-end themes & templates
$NEXTPREV_TEMPLATE['default_nav_caption'] = LAN_GOPAGE.'&nbsp;';

$NEXTPREV_TEMPLATE['dropdown_nav_first'] = '';
$NEXTPREV_TEMPLATE['dropdown_nav_last'] = '';

// 'tbox npbutton' classes are deprecated!!!
$NEXTPREV_TEMPLATE['dropdown_nav_prev'] = '<a class="btn btn-default btn-secondary nextprev-item prev tbox npbutton" href="{url}" title="{url_label}">{label}</a>&nbsp;';
// 'tbox npbutton' classes are deprecated!!!
$NEXTPREV_TEMPLATE['dropdown_nav_next'] = '&nbsp;<a class="btn btn-default btn-secondary nextprev-item next tbox npbutton" href="{url}" title="{url_label}">{label}</a>';

// 'npdropdown' class is deprecated!!!
$NEXTPREV_TEMPLATE['dropdown_items_start'] = '<select class="tbox npdropdown nextprev-select form-control" name="pageSelect" onchange="window.location.href=this.options[selectedIndex].value">';
$NEXTPREV_TEMPLATE['dropdown_item'] = '<option value="{url}">{label}</option>';
$NEXTPREV_TEMPLATE['dropdown_item_current'] = '<option value="{url}" selected="selected">{label}</option>';
$NEXTPREV_TEMPLATE['dropdown_items_end'] = '</select>';

//$NEXTPREV_TEMPLATE['dropdown_separator'] = '<span class="nextprev-sep"><!-- --></span>';
$NEXTPREV_TEMPLATE['dropdown_separator'] = '';

/**
 * Default Page core area navigation
 */
$NEXTPREV_TEMPLATE['page_start'] = '
<!-- Start of Next/Prev -->
<div class="cpage-nav">
';

$NEXTPREV_TEMPLATE['page_end'] = '
</div>
<!-- End of Next/Prev -->
';

$NEXTPREV_TEMPLATE['page_nav_caption'] = '';

$NEXTPREV_TEMPLATE['page_nav_first'] = '';
$NEXTPREV_TEMPLATE['page_nav_prev'] = '';
$NEXTPREV_TEMPLATE['page_nav_last'] = '';
$NEXTPREV_TEMPLATE['page_nav_next'] = '';

$NEXTPREV_TEMPLATE['page_items_start'] = '';
$NEXTPREV_TEMPLATE['page_item'] = "{bullet}&nbsp;<a class='cpage-np' href='{url}' title=\"{url_label}\">{label}</a>";
$NEXTPREV_TEMPLATE['page_item_current'] = "{bullet}&nbsp;<a class='cpage-np current' href='#' onclick='return false;' title=\"{url_label}\">{label}</a>";
$NEXTPREV_TEMPLATE['page_items_end'] = '';

//$NEXTPREV_TEMPLATE['default_separator'] = '<span class="nextprev-sep"><!-- --></span>';
$NEXTPREV_TEMPLATE['page_separator'] = '<br />';
?>