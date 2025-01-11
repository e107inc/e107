<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Error templates.
 */

if(!defined('e107_INIT'))
{
	exit;
}

$ERROR_TEMPLATE = array();

/**
 * 400 Bad Request.
 */
$ERROR_TEMPLATE['400'] = '
<h1 class="text-center">
	<strong>{ERROR_TITLE}</strong>
</h1>
<h2 class="text-center">
	{ERROR_SUBTITLE}
</h2>
<br/>
<div class="col-md-8 col-md-offset-2">
	<div class="panel panel-default">
		<div class="panel-heading">
			{ERROR_CAPTION}
		</div>
		<div class="panel-body">
			{ERROR_CONTENT}
		</div>
	</div>
</div>
<br/>
<div class="error-actions text-center">
	{ERROR_LINK_HOME}
</div>
<br/>
';

/**
 * 401 Unauthorized.
 */
$ERROR_TEMPLATE['401'] = '
<h1 class="text-center">
	<strong>{ERROR_TITLE}</strong>
</h1>
<h2 class="text-center">
	{ERROR_SUBTITLE}
</h2>
<br/>
<div class="col-md-8 col-md-offset-2">
	<div class="panel panel-default">
		<div class="panel-heading">
			{ERROR_CAPTION}
		</div>
		<div class="panel-body">
			{ERROR_CONTENT}
		</div>
	</div>
</div>
<br/>
<div class="error-actions text-center">
	{ERROR_LINK_HOME}
</div>
<br/>
';

/**
 * 403 Forbidden.
 */
$ERROR_TEMPLATE['403'] = '
<h1 class="text-center">
	<strong>{ERROR_TITLE}</strong>
</h1>
<h2 class="text-center">
	{ERROR_SUBTITLE}
</h2>
<br/>
<div class="col-md-8 col-md-offset-2">
	<div class="panel panel-default">
		<div class="panel-heading">
			{ERROR_CAPTION}
		</div>
		<div class="panel-body">
			{ERROR_CONTENT}
		</div>
	</div>
</div>
<br/>
<div class="error-actions text-center">
	{ERROR_LINK_HOME}
</div>
<br/>
';

/**
 * 404 Not Found.
 */
$ERROR_TEMPLATE['404'] = '
<h1 class="text-center">
	<strong>{ERROR_TITLE}</strong>
</h1>
<h2 class="text-center">
	{ERROR_SUBTITLE}
</h2>
<br/>
<div class="col-md-8 col-md-offset-2">
	<div class="panel panel-default">
		<div class="panel-heading">
			{ERROR_CAPTION}
		</div>
		<div class="panel-body">
			{ERROR_CONTENT}
		</div>
	</div>
</div>
<br/>
<div class="error-actions text-center">
	{ERROR_LINK_HOME} {ERROR_LINK_SEARCH}
</div>
<br/>
';

/**
 * 500 Internal server error.
 */
$ERROR_TEMPLATE['500'] = '
<h1 class="text-center">
	<strong>{ERROR_TITLE}</strong>
</h1>
<h2 class="text-center">
	{ERROR_SUBTITLE}
</h2>
<br/>
<div class="col-md-8 col-md-offset-2">
	<div class="panel panel-default">
		<div class="panel-heading">
			{ERROR_CAPTION}
		</div>
		<div class="panel-body">
			{ERROR_CONTENT}
		</div>
	</div>
</div>
<br/>
<div class="error-actions text-center">
	{ERROR_LINK_HOME}
</div>
<br/>
';

/**
 * Default error page.
 */
$ERROR_TEMPLATE['DEFAULT'] = '
<h1 class="text-center">
	<strong>{ERROR_TITLE}</strong>
</h1>
<h2 class="text-center">
	{ERROR_SUBTITLE}
</h2>
<br/>
<div class="col-md-8 col-md-offset-2">
	<div class="panel panel-default">
		<div class="panel-heading">
			{ERROR_CAPTION}
		</div>
		<div class="panel-body">
			{ERROR_CONTENT}
		</div>
	</div>
</div>
<br/>
<div class="error-actions text-center">
	{ERROR_LINK_HOME}
</div>
<br/>
';
