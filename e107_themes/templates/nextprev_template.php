<?php
/*
 * Copyright (c) e107 Inc 2009 - e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 *
 * NEXTPREV shortcode template
*/

/*
 * Default (old) page navigation, key prefix 'default'
 * Shortcodes are lowercase (simple parser)
 */

$NEXTPREV_TEMPLATE['default_start'] = '
<!-- Start of Next/Prev -->
<div class="nextprev">
';

$NEXTPREV_TEMPLATE['default_end'] = '
</div>
<!-- End of Next/Prev -->
';

//$NEXTPREV_TEMPLATE['default_nav_caption'] = '<span class="nexprev-caption center">{caption}</span>&nbsp;'; XXX - awaiting the new front-end themes & templates
$NEXTPREV_TEMPLATE['default_nav_caption'] = NP_3.'&nbsp;';

$NEXTPREV_TEMPLATE['default_nav_first'] = '<a class="nextprev-item first" href="{url}" title="{url_label}">{label}</a>';
$NEXTPREV_TEMPLATE['default_nav_prev'] = '<a class="nextprev-item prev" href="{url}" title="{url_label}">{label}</a>';
$NEXTPREV_TEMPLATE['default_nav_last'] = '<a class="nextprev-item last" href="{url}" title="{url_label}">{label}</a>';
$NEXTPREV_TEMPLATE['default_nav_next'] = '<a class="nextprev-item next" href="{url}" title="{url_label}">{label}</a>';

$NEXTPREV_TEMPLATE['default_items_start'] = '';
$NEXTPREV_TEMPLATE['default_item'] = '<a class="nextprev-item" href="{url}" title="{url_label}">{label}</a>';
$NEXTPREV_TEMPLATE['default_item_current'] = '<a class="nextprev-item current" href="#" onclick="return false;" title="{url_label}">{label}</a>';
$NEXTPREV_TEMPLATE['default_items_end'] = '';

//$NEXTPREV_TEMPLATE['default_separator'] = '<span class="nextprev-sep"><!-- --></span>';
$NEXTPREV_TEMPLATE['default_separator'] = '&nbsp;';


/*
 * Dropdown page navigation, key prefix 'dropdown'
 * Shortcodes are lowercase (simple parser)
 * TODO - do the slide-down via JS, make it unobtrusive
 */

$NEXTPREV_TEMPLATE['dropdown_start'] = '
<!-- Start of Next/Prev -->
<div class="nextprev">
';

$NEXTPREV_TEMPLATE['dropdown_end'] = '
</div>
<!-- End of Next/Prev -->
';

//$NEXTPREV_TEMPLATE['default_nav_caption'] = '<span class="nexprev-caption center">{caption}</span>&nbsp;'; XXX - awaiting the new front-end themes & templates
$NEXTPREV_TEMPLATE['default_nav_caption'] = NP_3.'&nbsp;';

$NEXTPREV_TEMPLATE['dropdown_nav_first'] = '';
$NEXTPREV_TEMPLATE['dropdown_nav_last'] = '';

// 'tbox npbutton' classes are deprecated!!!
$NEXTPREV_TEMPLATE['dropdown_nav_prev'] = '<a class="nextprev-item prev tbox npbutton" href="{url}" title="{url_label}">{label}</a>';
// 'tbox npbutton' classes are deprecated!!!
$NEXTPREV_TEMPLATE['dropdown_nav_next'] = '<a class="nextprev-item next tbox npbutton" href="{url}" title="{url_label}">{label}</a>';

// 'npdropdown' class is deprecated!!!
$NEXTPREV_TEMPLATE['dropdown_items_start'] = '<select class="tbox npdropdown nextprev-select" name="pageSelect" onchange="window.location.href=this.options[selectedIndex].value">';
$NEXTPREV_TEMPLATE['dropdown_item'] = '<option value="{url}">{label}</option>';
$NEXTPREV_TEMPLATE['dropdown_item_current'] = '<option value="{url}" selected="selected">{label}</option>';
$NEXTPREV_TEMPLATE['dropdown_items_end'] = '</select>';

//$NEXTPREV_TEMPLATE['dropdown_separator'] = '<span class="nextprev-sep"><!-- --></span>';
$NEXTPREV_TEMPLATE['dropdown_separator'] = '&nbsp;';

?>