<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/	


	$PAGE_TEMPLATE['docs']['page'] = '
		{PAGE}
		{PAGECOMMENTS}
	'; 
	
	$PAGE_TEMPLATE['docs']['start'] = '<div class="cpage_body">'; 
	

	$PAGE_TEMPLATE['docs']['body'] = '
		{CPAGEMESSAGE|default}
		<section id="{CPAGEANCHOR}">
		
		{CPAGEBODY}
		<br />
		<div class="muted text-right">
		<small>
		{CPAGERATING|default}
		{CPAGEDATE|default}
		{CPAGEAUTHOR|default}
		</small>
		</div>
		 </section>
	'; 
	
	
	

	$PAGE_TEMPLATE['docs']['authorize'] = '
		<div class="cpage-restrict">
			{message}
			{form_open}
				<h2>{caption}</h2>
				<div clas="center">{label} {password} {submit}</div>
			{form_close}
		</div>
	';
	

	$PAGE_TEMPLATE['docs']['restricted'] = '
		{text}
	';
	
	$PAGE_TEMPLATE['docs']['notfound'] = '
		{text}
	';
	

	$PAGE_TEMPLATE['docs']['end'] = '</div>'; 

	$PAGE_TEMPLATE['docs']['tableRender'] = 'cpage';
	


	


?>