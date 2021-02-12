<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Comment menu default template
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/comment_menu/comment_menu_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/



// Shortcode Wrappers
$COMMENT_MENU_WRAPPER['CM_DATESTAMP']   = ' {---}';
$COMMENT_MENU_WRAPPER['CM_AUTHOR']      = CM_L13.'{---}';
$COMMENT_MENU_WRAPPER['CM_TYPE']        = '<span class="label label-default badge badge-secondary bg-secondary ">{---}</span>';


// Template
$COMMENT_MENU_TEMPLATE['start']         = "<ul class='media-list list-unstyled comment-menu'>";
	
$COMMENT_MENU_TEMPLATE['item']          = "<li class='media d-flex mb-2' >
											<div class='media-left mr-3 me-3'>{CM_AUTHOR_AVATAR: shape=circle&size=48&crop=1}</div>
											<div class='media-body'>
												{CM_TYPE} {CM_URL_PRE}{CM_HEADING}{CM_URL_POST}
												<div>{CM_COMMENT}</div>
												<small class='text-muted muted'> {CM_AUTHOR} {CM_DATESTAMP}</small>
											</div>
											
											</li>";
	
$COMMENT_MENU_TEMPLATE['end']           = "</ul>";

