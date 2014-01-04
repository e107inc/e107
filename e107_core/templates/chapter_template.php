<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/
/**
 * Template for Book and Chapter Listings, as well as navigation on those pages. 
 */

$CHAPTER_TEMPLATE['default']['listPages']['caption']				= "{CHAPTER_NAME}";
$CHAPTER_TEMPLATE['default']['listPages']['start'] 					= "<ul class='page-pages-list'>";
$CHAPTER_TEMPLATE['default']['listPages']['item'] 					= "<li><a href='{CPAGEURL}'>{CPAGETITLE}</a></li>";
$CHAPTER_TEMPLATE['default']['listPages']['end'] 					= "</ul>";	
	
$CHAPTER_TEMPLATE['default']['listChapters']['start']				= "<ul class='page-chapters-list'>";
$CHAPTER_TEMPLATE['default']['listChapters']['item']				= "<li><h4>{CHAPTER_NAME}</h4>{PAGES}";
$CHAPTER_TEMPLATE['default']['listChapters']['end']					= "</ul>";

$CHAPTER_TEMPLATE['default']['listBooks']['start']					= "<ul class='page-chapters-list'>";
$CHAPTER_TEMPLATE['default']['listBooks']['item']					= "<li><h3>{BOOK_NAME}</h3>{CHAPTERS}";
$CHAPTER_TEMPLATE['default']['listBooks']['end']					= "</ul>";



$CHAPTER_TEMPLATE['nav']['listChapters']['caption']					= "Articles";

$CHAPTER_TEMPLATE['nav']['listChapters']['start'] 					= '<ul class="page-nav">';
	
$CHAPTER_TEMPLATE['nav']['listChapters']['item']					= '
																	<li>
																		<a role="button" href="{LINK_URL}" >
																		 {LINK_NAME} 
																		</a> 
																	</li>
																	';
	

$CHAPTER_TEMPLATE['nav']['listChapters']['item_submenu']	 		= '
																	<li>
																		<a role="button" href="{LINK_URL}" >
																		 {LINK_NAME} 
																		</a> 
																		{LINK_SUB}
																	</li>
																	';
	
$CHAPTER_TEMPLATE['nav']['listChapters']['item_submenu_active']		= '
																	<li class="active">
																		<a role="button"  href="{LINK_URL}">
																		 {LINK_NAME}
																		</a>
																		{LINK_SUB}
																	</li>
																	';	
																	
$CHAPTER_TEMPLATE['nav']['listChapters']['item_active'] 			= '
																	<li class="active">
																		<a crole="button" href="{LINK_URL}">
																		 {LINK_NAME}
																		</a>
																	</li>
																	';	

$CHAPTER_TEMPLATE['nav']['listChapters']['end'] 					= '</ul>';		

	
$CHAPTER_TEMPLATE['nav']['listChapters']['submenu_start'] 			= '<ul class="page-nav" id="page-nav-{LINK_PARENT}" role="menu" >';
	
	
$CHAPTER_TEMPLATE['nav']['listChapters']['submenu_item'] 			= '
																	<li role="menuitem" >
																		<a href="{LINK_URL}">{LINK_NAME}</a>
																		{LINK_SUB}
																	</li>
																	';
	
$CHAPTER_TEMPLATE['nav']['listChapters']['submenu_loweritem']		= '
																		<li role="menuitem" >
																			<a href="{LINK_URL}">{LINK_NAME}</a>
																			{LINK_SUB}
																		</li>
																	';
$CHAPTER_TEMPLATE['nav']['listChapters']['submenu_loweritem_active'] = '
																			<li role="menuitem" class="active">
																				<a href="{LINK_URL}">{LINK_NAME}</a>
																				{LINK_SUB}
																			</li>
																		';

$CHAPTER_TEMPLATE['nav']['listChapters']['submenu_item_active'] 	= '
																			<li role="menuitem" class="active">
																				<a href="{LINK_URL}">{LINK_NAME}</a>
																				{LINK_SUB}
																			</li>
																		';

$CHAPTER_TEMPLATE['nav']['listChapters']['submenu_end'] 			= '</ul>';	


$CHAPTER_TEMPLATE['nav']['listBooks'] = $CHAPTER_TEMPLATE['nav']['listChapters'];
$CHAPTER_TEMPLATE['nav']['listPages'] = $CHAPTER_TEMPLATE['nav']['listChapters'];
$CHAPTER_TEMPLATE['nav']['showPage'] = $CHAPTER_TEMPLATE['nav']['listChapters'];




?>