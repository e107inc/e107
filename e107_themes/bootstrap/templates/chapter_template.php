<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/

	$CHAPTER_TEMPLATE['docs']['listPages']['start'] = 				'';
	$CHAPTER_TEMPLATE['docs']['listPages']['item']	= 				'
																	<section id="{CPAGEANCHOR}">
															          <div class="page-header">
															            <h1>{CPAGETITLE}</h1>
															          </div>
															          {CPAGEBODY}
															          </section>
															     	';
	$CHAPTER_TEMPLATE['docs']['listPages']['end'] = 				'';
	
	
	$CHAPTER_TEMPLATE['docs']['listChapters']['start'] = 			'';
	$CHAPTER_TEMPLATE['docs']['listChapters']['item'] = 			"<section id='{CHAPTER_ANCHOR}'><a href='{CHAPTER_URL}'><h1>{CHAPTER_ICON}{CHAPTER_NAME}</h1></a>
																		{CHAPTER_DESCRIPTION}
																	</section>
																	";
	$CHAPTER_TEMPLATE['docs']['listChapters']['end'] = 				'';


	$CHAPTER_TEMPLATE['docs']['listBooks']['caption'] = 			'';
	$CHAPTER_TEMPLATE['docs']['listBooks']['start'] = 				'';
	$CHAPTER_TEMPLATE['docs']['listBooks']['item'] = 				"<section id='{BOOK_ANCHOR}'><a href='{BOOK_URL}'><h1>{BOOK_ICON}{BOOK_NAME}</h1></a>
																		{BOOK_DESCRIPTION}
																	</section>
																	";
	$CHAPTER_TEMPLATE['docs']['listBooks']['end'] = 				'';




	$CHAPTER_TEMPLATE['navdocs']['listChapters']['caption'] = 		" ";
	
	$CHAPTER_TEMPLATE['navdocs']['listChapters']['start'] = 		'<ul class="nav nav-list bs-docs-sidenav">';
	
	$CHAPTER_TEMPLATE['navdocs']['listChapters']['item'] = 			'
																	<li class="{LINK_ACTIVE}">
																		<a role="button" href="{LINK_ANCHOR}" ><i class="icon-chevron-right"></i>
																		 {LINK_NAME} 
																		</a> 
																	</li>
																	';
	$CHAPTER_TEMPLATE['navdocs']['listChapters']['end'] = 			'</ul>';
	
	// These templates are the same as above, so we just give them the same value. 
	$CHAPTER_TEMPLATE['navdocs']['listBooks']  	= $CHAPTER_TEMPLATE['navdocs']['listChapters'];
	$CHAPTER_TEMPLATE['navdocs']['listPages'] 	= $CHAPTER_TEMPLATE['navdocs']['listChapters'];
	$CHAPTER_TEMPLATE['navdocs']['showPage'] 	= $CHAPTER_TEMPLATE['navdocs']['listChapters'];
	
	
	
	
	
?>