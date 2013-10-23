<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Error Templates
 *
 * $Id: $
 */

/**
 * 
 *	@package     e107
 *	@subpackage	e107_templates
 *	@version 	$Id$;
 *
*/

if (!defined('e107_INIT')) { exit; }

$ERROR_TEMPLATE = array();

$ERROR_TEMPLATE['404']['start'] = '<div class="error-404">';
$ERROR_TEMPLATE['404']['body'] = '
	<h3><i class="icon-exclamation-sign alert-danger" title="'.LAN_ERROR_45.'"></i> '.LAN_ERROR_45.'</h3>
	<p>
		'.LAN_ERROR_21.'<br />'.LAN_ERROR_9.'
	</p>
	<a href="{siteUrl}">'.LAN_ERROR_20.'</a><br />
	<a href="{searchUrl}">'.LAN_ERROR_22.'</a>
';
$ERROR_TEMPLATE['404']['end'] = '</div>';


$ERROR_TEMPLATE['403']['start'] = '<div class="error-403">';
$ERROR_TEMPLATE['403']['body'] = '
	<h3><i class="icon-exclamation-sign alert-danger" title="'.LAN_ERROR_4.'"></i> '.LAN_ERROR_4.'</h3>
	<p>
		'.LAN_ERROR_5.'<br />'.LAN_ERROR_6.'<br /><br />'.LAN_ERROR_2.'
	</p>
	<a href="{siteUrl}">'.LAN_ERROR_20.'</a><br />
';
$ERROR_TEMPLATE['403']['end'] = '</div>';

