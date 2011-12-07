<?php
/*
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 * 
 * Page tempaltes - under construction
 */
global $sc_style;

$sc_style['CPAGEAUTHOR|default']['pre'] = '';
$sc_style['CPAGEAUTHOR|default']['post'] = ", ";

$sc_style['CPAGESUBTITLE|default']['pre'] = '<h2>';
$sc_style['CPAGESUBTITLE|default']['post'] = '</h2>';

$sc_style['CPAGEMESSAGE|default']['pre'] = '';
$sc_style['CPAGEMESSAGE|default']['post'] = '<div class="clear"><!-- --></div>';

$sc_style['CPAGENAV|default']['pre'] = '<div class="f-right">';
$sc_style['CPAGENAV|default']['post'] = '</div>';

#### default template - BC ####
	// used only for parsing comment outside of the page tablerender-ed content
	// leave empty if you integrate page comments inside the main page template
	$PAGE_TEMPLATE['default']['page'] = '
		{PAGE}
		{PAGECOMMENTS}
	'; 
	
	// always used - it's inside the {PAGE} sc from 'page' template
	$PAGE_TEMPLATE['default']['start'] = '<div class="cpage_body">'; 
	
	// page body
	$PAGE_TEMPLATE['default']['body'] = '
		{CPAGEMESSAGE|default}
		
		<div class="f-right">{CPAGEAUTHOR|default}{CPAGEDATE|default}</div>
		{CPAGESUBTITLE|default}
		<div class="clear"><!-- --></div>
		
		{CPAGENAV|default}
		{CPAGEBODY|default}
		
		<div class="clear"><!-- --></div>
		{CPAGERATING|default}
	'; 
	
	// used only when password authorization is required
	$PAGE_TEMPLATE['default']['authorize'] = '
		<div class="cpage-restrict">
			{message}
			{form_open}
				<h2>{caption}</h2>
				<div clas="center">{label} {password} {submit}</div>
			{form_close}
		</div>
	';
	
	// used when access is denied (restriction by class)
	$PAGE_TEMPLATE['default']['restricted'] = '
		{text}
	';
	
	// used when page is not found
	$PAGE_TEMPLATE['default']['notfound'] = '
		{text}
	';
	
	// always used
	$PAGE_TEMPLATE['default']['end'] = '</div>'; 
	
	// options per template - disable table render
	$PAGE_TEMPLATE['default']['noTableRender'] = false;
	
	// define different tablerender mode here
	$PAGE_TEMPLATE['default']['tableRender'] = 'cpage';

	
#### No table render example template ####
	$PAGE_TEMPLATE['custom']['start'] = '<div class="cpage_body">'; 
	
	$PAGE_TEMPLATE['custom']['body'] = ''; 
	
	$PAGE_TEMPLATE['custom']['authorize'] = '
	
	';
	
	$PAGE_TEMPLATE['custom']['restricted'] = '
	
	';
	
	$PAGE_TEMPLATE['custom']['end'] = '</div>'; 
	

	$PAGE_TEMPLATE['custom']['noTableRender'] = true;
	$PAGE_TEMPLATE['custom']['tableRender'] = '';
	
