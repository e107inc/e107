<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/review.php
|
|        (C)Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|
|		$Source: /cvs_backup/e107_0.7/e107_plugins/content/handlers/content_class.php,v $
|		$Revision: 1.113 $
|		$Date: 2009-11-19 11:45:50 $
|		$Author: marj_nl_fr $
+---------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

global $plugindir, $plugintable, $datequery;
$plugindir		= e_PLUGIN."content/";
$plugintable	= "pcontent";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)
$datequery		= " AND content_datestamp < ".time()." AND (content_enddate=0 || content_enddate>".time().") ";

require_once($plugindir."handlers/content_defines.php");

if(!is_object($sql)){ $sql = new db; }

class content{

		function ContentDefaultPrefs(){
			global $tp;

			//ADMIN CREATE FORM
			$content_pref['content_admin_subheading'] = "1";				//should subheading be available
			$content_pref['content_admin_summary'] = "1";					//should summary be available
			$content_pref['content_admin_startdate'] = "1";					//should start date be available
			$content_pref['content_admin_enddate'] = "1";					//should end date be available
			$content_pref['content_admin_icon'] = "0";						//should icon be available to add when creating an item
			$content_pref['content_admin_attach'] = "0";					//should file be available to add when creating an item
			$content_pref['content_admin_images'] = "0";					//should image be available to add when creating an item
			$content_pref['content_admin_comment'] = "1";					//should comment be available to add when creating an item
			$content_pref['content_admin_rating'] = "1";					//should rating be available to add when creating an item
			$content_pref['content_admin_score'] = "1";						//should score be available to add when creating an item
			$content_pref['content_admin_pe'] = "1";						//should printemailicons be available to add when creating an item
			$content_pref['content_admin_visibility'] = "1";				//should visibility be available to add when creating an item
			$content_pref['content_admin_meta'] = "0";						//should metatags be available to add when creating an item
			$content_pref['content_admin_custom_number'] = "0";				//how many customtags should be available to add when creating an item
			$content_pref['content_admin_images_number'] = "0";				//how many images should be available to add when creating an item
			$content_pref['content_admin_files_number'] = "0";				//how many files should be available to add when creating an item
			$content_pref['content_admin_layout'] = "0";					//should the option for choosing a layout template be shown
			$content_pref['content_admin_customtags'] = "0";				//should options for adding additional data be shown
			$content_pref['content_admin_presettags'] = "0";				//should preset data tags be shown

			//ADMIN CREATE CATEGORY FORM
			$content_pref['content_admincat_subheading'] = '1';				//should subheading be available
			$content_pref['content_admincat_startdate'] = '1';				//should startdate be available
			$content_pref['content_admincat_enddate'] = '1';				//should enddate be available
			$content_pref['content_admincat_uploadicon'] = '1';				//should uploadicon be available
			$content_pref['content_admincat_selecticon'] = '1';				//should selecticon be available
			$content_pref['content_admincat_comment'] = '1';				//should comment be available
			$content_pref['content_admincat_rating'] = '1';					//should rating be available
			$content_pref['content_admincat_pe'] = '1';						//should print email icons be available
			$content_pref['content_admincat_visibility'] = '1';				//should visibility be available

			//SUBMIT FORM
			$content_pref['content_submit'] = "0";							//should users be able to submit an item
			$content_pref['content_submit_class'] = "0";					//define which userclass is able to submit an item
			$content_pref['content_submit_directpost'] ="0";				//should submission be direclty posted as an item, or have them validated by admins
			$content_pref['content_submit_subheading'] = '1';				//should subheading be available
			$content_pref['content_submit_summary'] = '1';					//should summary be available
			$content_pref['content_submit_startdate'] = '1';				//should startdate be available
			$content_pref['content_submit_enddate'] = '1';					//should enddate be available
			$content_pref['content_submit_icon'] = "0";						//should icon be available to add when submitting an item
			$content_pref['content_submit_attach'] = "0";					//should file be available to add when submitting an item
			$content_pref['content_submit_images'] = "0";					//should image be available to add when submitting an item
			$content_pref['content_submit_comment'] = "0";					//should comment be available to add when submitting an item
			$content_pref['content_submit_rating'] = "0";					//should rating be available to add when submitting an item
			$content_pref['content_submit_score'] = "0";					//should score be available to add when submitting an item
			$content_pref['content_submit_pe'] = "0";						//should printemailicons be available to add when submitting an item
			$content_pref['content_submit_visibility'] = "0";				//should visibility be available to add when submitting an item
			$content_pref['content_submit_meta'] = "0";						//should metatags be available to add when submitting an item
			$content_pref['content_submit_custom_number'] = "0";			//how many customtags should be available to add when submitting an item
			$content_pref['content_submit_images_number'] = "0";			//how many images should be available to add when submitting an item
			$content_pref['content_submit_files_number'] = "0";				//how many files should be available to add when submitting an item
			$content_pref['content_submit_layout'] = "0";					//should the option for choosing a layout template be shown
			$content_pref['content_submit_customtags'] = "0";				//should options for adding additional data be shown
			$content_pref['content_submit_presettags'] = "0";				//should preset data tags be shown

			//PATH THEME CSS
			$content_pref['content_cat_icon_path_large'] = "{e_PLUGIN}content/images/cat/48/";	//default path to large categry icons
			$content_pref['content_cat_icon_path_small'] = "{e_PLUGIN}content/images/cat/16/";	//default path to small category icons

			$content_pref['content_icon_path'] = "{e_PLUGIN}content/images/icon/";				//default path to item icons
			$content_pref['content_icon_path_tmp'] = "{e_PLUGIN}content/images/icon/tmp/";		//default tmp path to item icons
			
			$content_pref['content_image_path'] = "{e_PLUGIN}content/images/image/";			//default path to item images
			$content_pref['content_image_path_tmp'] = "{e_PLUGIN}content/images/image/tmp/";	//default tmp path to item images
			
			$content_pref['content_file_path'] = "{e_PLUGIN}content/images/file/";				//default path to item file attachments
			$content_pref['content_file_path_tmp'] = "{e_PLUGIN}content/images/file/tmp/";		//default tmp path to item file attachments
			
			$content_pref['content_theme'] = "{e_PLUGIN}content/templates/default/";			//choose theme for main parent
			$content_pref['content_layout'] = "content_content_template.php";					//choose default layout scheme

			//GENERAL
			$content_pref['content_log'] = "0";								//activate log
			$content_pref['content_blank_icon'] = "0";						//use blank icon if no icon present
			$content_pref['content_blank_caticon'] = "0";					//use blank caticon if no caticon present
			$content_pref['content_breadcrumb_catall'] = "0";				//show breadcrumb on all categories page
			$content_pref['content_breadcrumb_cat'] = "0";					//show breadcrumb on single category page
			$content_pref['content_breadcrumb_authorall'] = "0";			//show breadcrumb on all author page
			$content_pref['content_breadcrumb_author'] = "0";				//show breadcrumb on single author page
			$content_pref['content_breadcrumb_recent'] = "0";				//show breadcrumb on recent page
			$content_pref['content_breadcrumb_item'] = "0";					//show breadcrumb on content item page
			$content_pref['content_breadcrumb_top'] = "0";					//show breadcrumb on top rated page
			$content_pref['content_breadcrumb_archive'] = "0";				//show breadcrumb on archive page
			$content_pref['content_breadcrumb_seperator{$id}'] = ">";		//seperator character between breadcrumb
			$content_pref['content_breadcrumb_rendertype'] = "2";			//how to render the breadcrumb
			$content_pref['content_navigator_catall'] = "0";				//show navigator on all categories page
			$content_pref['content_navigator_cat'] = "0";					//show navigator on single category page
			$content_pref['content_navigator_authorall'] = "0";				//show navigator on all author page
			$content_pref['content_navigator_author'] = "0";				//show navigator on single author page
			$content_pref['content_navigator_recent'] = "0";				//show navigator on recent page
			$content_pref['content_navigator_item'] = "0";					//show navigator on content item page
			$content_pref['content_navigator_top'] = "0";					//show navigator on top rated page
			$content_pref['content_navigator_archive'] = "0";				//show navigator on archive page
			$content_pref['content_search_catall'] = "0";					//show search keyword on all categories page
			$content_pref['content_search_cat'] = "0";						//show search keyword on single category page
			$content_pref['content_search_authorall'] = "0";				//show search keyword on all author page
			$content_pref['content_search_author'] = "0";					//show search keyword on single author page
			$content_pref['content_search_recent'] = "0";					//show search keyword on recent page
			$content_pref['content_search_item'] = "0";						//show search keyword on content item page
			$content_pref['content_search_top'] = "0";						//show search keyword on top rated page
			$content_pref['content_search_archive'] = "0";					//show search keyword on archive page
			$content_pref['content_ordering_catall'] = "0";					//show ordering on all categories page
			$content_pref['content_ordering_cat'] = "0";					//show ordering on single category page
			$content_pref['content_ordering_authorall'] = "0";				//show ordering on all author page
			$content_pref['content_ordering_author'] = "0";					//show ordering on single author page
			$content_pref['content_ordering_recent'] = "0";					//show ordering on recent page
			$content_pref['content_ordering_item'] = "0";					//show ordering on content item page
			$content_pref['content_ordering_top'] = "0";					//show ordering on top rated page
			$content_pref['content_ordering_archive'] = "0";				//show ordering on archive page
			$content_pref['content_searchmenu_rendertype'] = "1";			//rendertype for searchmenu (1=echo, 2=in separate menu)
			$content_pref['content_nextprev'] = "1";						//use nextprev buttons
			$content_pref['content_nextprev_number'] = "10";				//how many items on a page
			$content_pref['content_defaultorder'] = "orderddate";			//default sort and order method
			//upload icon/image size handling
			$content_pref['content_upload_image_size'] = "500";				//resize size of uploaded image
			$content_pref['content_upload_image_size_thumb'] = "100";		//resize size of created thumb on uploaded image
			$content_pref['content_upload_icon_size'] = "100";				//resize size of uploaded icon

			//CONTENT ITEM PREVIEW
			$content_pref['content_list_icon'] = "0";						//show icon
			$content_pref['content_list_subheading'] = "1";					//show subheading
			$content_pref['content_list_summary'] = "1";					//show summary
			$content_pref['content_list_text'] = "0";						//show (part of) text
			$content_pref['content_list_date'] = "0";						//show date
			$content_pref['content_list_authorname'] = "0";					//show authorname
			$content_pref['content_list_authorprofile'] = "0";				//show link to author profile
			$content_pref['content_list_authoremail'] = "0";				//show authoremail
			$content_pref['content_list_authoricon'] = "0";					//show link to author list
			$content_pref['content_list_rating'] = "1";						//show rating system
			$content_pref['content_list_peicon'] = "1";						//show printemailicons
			$content_pref['content_list_parent'] = "0";						//show parent cat
			$content_pref['content_list_refer'] = "0";						//show refer count
			$content_pref['content_list_subheading_char'] = "100";			//how many subheading characters
			$content_pref['content_list_subheading_post'] = "[...]";		//use a postfix for too long subheadings
			$content_pref['content_list_summary_char'] = "100";				//how many summary characters
			$content_pref['content_list_summary_post'] = "[...]";			//use a postfix for too long summary
			$content_pref['content_list_text_char'] = "60";					//how many text words
			$content_pref['content_list_text_post'] = CONTENT_LAN_16;		//use a postfix for too long text
			$content_pref['content_list_text_link'] = "1";					//show link to content item on postfix
			$content_pref['content_list_authoremail_nonmember'] = "0";		//show email non member author
			$content_pref['content_list_peicon_all'] = "0";					//override printemail icons
			$content_pref['content_list_rating_all'] = "0";					//override rating system
			$content_pref['content_list_editicon'] = "0";					//show link to admin edit item
			$content_pref['content_list_datestyle'] = "%d %b %Y";			//choose datestyle for given date
			$content_pref['content_list_caption'] = CONTENT_LAN_23;			//caption for recent list
			$content_pref['content_list_caption_append_name'] = '1';		//append category heading to caption

			//CATEGORY PAGES
			//sections of content category in 'view all categories page'
			$content_pref['content_catall_icon'] = "1";						//show icon
			$content_pref['content_catall_subheading'] = "1";				//show subheading
			$content_pref['content_catall_text'] = "0";						//show text
			$content_pref['content_catall_date'] = "0";						//show date
			$content_pref['content_catall_rating'] = "1";					//show rating
			$content_pref['content_catall_authorname'] = "0";				//show author name
			$content_pref['content_catall_authoremail'] = "0";				//show author email
			$content_pref['content_catall_authorprofile'] = "0";			//show link to author profile
			$content_pref['content_catall_authoricon'] = "0";				//show link to author list
			$content_pref['content_catall_peicon'] = "1";					//show pe icons
			$content_pref['content_catall_comment'] = "1";					//show amount of comments
			$content_pref['content_catall_amount'] = "0";					//show amount of items
			$content_pref['content_catall_text_char'] = "65";				//define amount of words of text to display
			$content_pref['content_catall_text_post'] = CONTENT_LAN_16;		//define postfix is text is too long
			$content_pref['content_catall_text_link'] = "1";				//define if link to category should be added on postfix
			$content_pref['content_catall_caption'] = CONTENT_LAN_25;		//caption for all categories page
			//sections of content category in 'view category' page
			$content_pref['content_cat_icon'] = "1";						//show icon
			$content_pref['content_cat_subheading'] = "1";					//show subheading
			$content_pref['content_cat_text'] = "0";						//show text
			$content_pref['content_cat_date'] = "0";						//show date
			$content_pref['content_cat_authorname'] = "0";					//show author name
			$content_pref['content_cat_authoremail'] = "0";					//show author email
			$content_pref['content_cat_authorprofile'] = "0";				//show link to author profile
			$content_pref['content_cat_authoricon'] = "0";					//show link to author list
			$content_pref['content_cat_rating'] = "1";						//show rating
			$content_pref['content_cat_peicon'] = "1";						//show pe icons
			$content_pref['content_cat_comment'] = "1";						//show amount of comments
			$content_pref['content_cat_amount'] = "1";						//show amount of items
			$content_pref['content_cat_caption'] = CONTENT_LAN_26;			//caption for single category page
			$content_pref['content_cat_caption_append_name'] = '1';			//append category heading to caption
			$content_pref['content_cat_sub_caption'] = CONTENT_LAN_28;		//caption for subcategories
			$content_pref['content_cat_item_caption'] = CONTENT_LAN_31;		//caption for items in category

			//sections of subcategories in 'view category page'
			$content_pref['content_catsub_icon'] = "1";						//show icon
			$content_pref['content_catsub_subheading'] = "1";				//show subheading
			$content_pref['content_catsub_amount'] = "1";					//show amount of items
			$content_pref['content_cat_showparent'] = "1";					//show parent item in category page
			$content_pref['content_cat_showparentsub'] = "1";				//show subcategories in category page
			$content_pref['content_cat_listtype'] = "0";					//also show items from subategories
			$content_pref['content_cat_menuorder'] = "1";					//order of parent and child items
			$content_pref['content_cat_rendertype'] = "2";					//render method of the menus
			$content_pref['content_cat_text_char'] = "65";					//define amount of words of text to display
			$content_pref['content_cat_text_post'] = CONTENT_LAN_16;			//define postfix is text is too long
			$content_pref['content_cat_text_link'] = "1";					//define if link to category should be added on postfix
			$content_pref['content_cat_authoremail_nonmember'] = "0";		//define if the email of a non-member will be displayed
			$content_pref['content_cat_peicon_all'] = "0";					//override printemail icons
			$content_pref['content_cat_rating_all'] = "0";					//override rating system

			//CONTENT PAGE
			$content_pref['content_content_icon'] = "0";					//show icon
			$content_pref['content_content_subheading'] = "1";				//show subheading
			$content_pref['content_content_summary'] = "1";					//show summary
			$content_pref['content_content_date'] = "0";					//show date
			$content_pref['content_content_authorname'] = "1";				//show authorname
			$content_pref['content_content_authorprofile'] = "0";			//show link to author profile
			$content_pref['content_content_authoremail'] = "0";				//show suthoremail
			$content_pref['content_content_authoricon'] = "0";				//show link to author list
			$content_pref['content_content_parent'] = "0";					//show parent category
			$content_pref['content_content_rating'] = "1";					//show rating system
			$content_pref['content_content_peicon'] = "1";					//show printemailicons
			$content_pref['content_content_refer'] = "0";					//show refer count
			$content_pref['content_content_comment'] = "0";					//show amount of comments
			$content_pref['content_content_authoremail_nonmember'] = "0";	//show email non member
			$content_pref['content_content_peicon_all'] = "0";				//override printemail icons
			$content_pref['content_content_rating_all'] = "0";				//override rating system
			$content_pref['content_content_comment_all'] = "0";				//override comment system				
			$content_pref['content_content_editicon'] = "0";				//show link in content page to admin edit item
			$content_pref['content_content_customtags'] = "0";				//should additional data be shown
			$content_pref['content_content_presettags'] = "0";				//should preset data tags be shown
			$content_pref['content_content_attach'] = "0";					//show attachments
			$content_pref['content_content_images'] = "0";					//show images
			$content_pref['content_content_pagenames_rendertype'] = "0";	//rendertype for articleindex on multipage content items
			$content_pref['content_content_multipage_preset'] = "0";		//render custom/preset in multipage item first/last page

			//AUTHOR PAGE
			$content_pref['content_author_lastitem'] = "0";					//show last item reference
			$content_pref['content_author_amount'] = "1";					//show amount of items from this author
			$content_pref['content_author_nextprev'] = "1";					//use next prev buttons
			$content_pref['content_author_nextprev_number'] = "20";			//amount of items per page
			$content_pref['content_author_index_caption'] = CONTENT_LAN_32;	//caption for author index page
			$content_pref['content_author_caption'] = CONTENT_LAN_32;		//caption for single author page
			$content_pref['content_author_caption_append_name'] = '1';		//append author name to caption

			//ARCHIVE PAGE
			$content_pref['content_archive_nextprev'] = "1";				//archive : choose to show next/prev links
			$content_pref['content_archive_nextprev_number'] = "30";		//archive : choose amount to use in next/prev
			$content_pref['content_archive_letterindex'] = "0";				//archive : letter index
			$content_pref['content_archive_datestyle'] = "%d %b %Y";		//archive : choose datestyle for given date
			$content_pref['content_archive_date'] = "1";					//archive : section: show date
			$content_pref['content_archive_authorname'] = "0";				//archive : section: show authorname
			$content_pref['content_archive_authorprofile'] = "0";			//archive : section: show link to author profile
			$content_pref['content_archive_authoricon'] = "0";				//archive : section: show link to author list
			$content_pref['content_archive_authoremail'] = "0";				//archive : section: show author email
			$content_pref['content_archive_authoremail_nonmember'] = "0";	//archive : show link to email of non-member author
			$content_pref['content_archive_caption'] = CONTENT_LAN_84;		//caption for archive page

			//TOP RATED PAGE
			$content_pref['content_top_icon'] = "0";						//top : section: show icon
			$content_pref['content_top_authorname'] = "0";					//top : section: show authorname
			$content_pref['content_top_authorprofile'] = "0";				//top : section: show link to author profile
			$content_pref['content_top_authoricon'] = "0";					//top : section: show link to author list
			$content_pref['content_top_authoremail'] = "0";					//top : section: show author email
			$content_pref['content_top_authoremail_nonmember'] = "0";		//top : show link to email of non-member author
			$content_pref['content_top_icon_width'] = '';					//use this size for icon
			$content_pref['content_top_caption'] = CONTENT_LAN_38;			//caption for top rated page
			$content_pref['content_top_caption_append_name'] = '1';			//append category heading to caption

			//TOP SCORE PAGE
			$content_pref['content_score_icon'] = "0";						//score : section: show icon
			$content_pref['content_score_authorname'] = "0";				//score : section: show authorname
			$content_pref['content_score_authorprofile'] = "0";				//score : section: show link to author profile
			$content_pref['content_score_authoricon'] = "0";				//score : section: show link to author list
			$content_pref['content_score_authoremail'] = "0";				//score : section: show author email
			$content_pref['content_score_authoremail_nonmember'] = "0";		//score : show link to email of non-member author
			$content_pref['content_score_icon_width'] = '';					//use this size for icon
			$content_pref['content_score_caption'] = CONTENT_LAN_87;		//caption for top score page
			$content_pref['content_score_caption_append_name'] = '1';		//append category heading to caption

			//MENU OPTIONS
			$content_pref['content_menu_caption'] = CONTENT_MENU_LAN_0;		//caption of menu
			$content_pref['content_menu_search'] = "0";						//show search keyword
			$content_pref['content_menu_sort'] = "0";						//show sorting methods
			$content_pref['content_menu_links'] = "1";						//show content links
			$content_pref['content_menu_links_dropdown'] = "0";				//rendertype of content links (in dropdown or as normal links)
			$content_pref['content_menu_links_icon'] = "0";					//define icon for content links (only with normallinks)
			$content_pref['content_menu_links_caption'] = CONTENT_MENU_LAN_4;	//define caption for link list (only is normallinks is selected)
			$content_pref['content_menu_viewallcat'] = "1";					//menu: view link to all categories
			$content_pref['content_menu_viewallauthor'] = "1";				//menu: view link to all authors
			$content_pref['content_menu_viewallitems'] = "1";				//menu: view link to all items (archive)
			$content_pref['content_menu_viewtoprated'] = "0";				//menu: view link to top rated items
			$content_pref['content_menu_viewtopscore'] = "0";				//menu: view link to top score items
			$content_pref['content_menu_viewrecent'] = "1";					//menu: view link to recent items
			$content_pref['content_menu_viewsubmit'] = "0";					//view link to submit content item (only if it is allowed)
			$content_pref['content_menu_viewicon'] = "0";					//choose icon to display for links
			$content_pref['content_menu_cat'] = "1";						//view categories
			$content_pref['content_menu_cat_main'] = "1";					//show main parent in the category list				
			$content_pref['content_menu_cat_number'] = "1";					//show number of items in category				
			$content_pref['content_menu_cat_icon'] = "0";					//choose icon to display for categories
			$content_pref['content_menu_cat_icon_default'] = "0";			//choose default icon is no icon present (only if category_icon is selected)
			$content_pref['content_menu_cat_caption'] = CONTENT_MENU_LAN_3;	//define caption for category list
			$content_pref['content_menu_cat_dropdown'] = "0";				//rendertype of categories (in dropdown or as normal links)
			$content_pref['content_menu_recent'] = "1";						//view recent list
			$content_pref['content_menu_recent_caption'] = CONTENT_MENU_LAN_2;	//caption of recent list
			$content_pref['content_menu_recent_number'] = "5";				//number of recent items to show
			$content_pref['content_menu_recent_date'] = "0";				//show date in recent list
			$content_pref['content_menu_recent_datestyle'] = "%d %b %Y";	//choose datestyle for given date
			$content_pref['content_menu_recent_author'] = "0";				//show author in recent list
			$content_pref['content_menu_recent_subheading'] = "0";			//show subheading in recent list
			$content_pref['content_menu_recent_subheading_char'] = "80";	//number of characters of subheading to show
			$content_pref['content_menu_recent_subheading_post'] = "[...]";	//postfix for too long subheadings
			$content_pref['content_menu_recent_icon'] = "0";				//choose icon to display for recent items
			$content_pref['content_menu_recent_icon_width'] = "50";			//specify width of icon (only if content_icon is set)

			$content_pref['content_inherit'] = '0';							//inherit options from default preferences

			//CONTENT MANAGER
			$content_pref['content_manager_approve'] = '0';					//class for managers who can approve submitted items
			$content_pref['content_manager_personal'] = '0';				//class for managers who can manage personal items
			$content_pref['content_manager_category'] = '0';				//class for managers who can manage all items in a category

			//PAGE RESTRICTION (NOT YET IN USE)
			$content_pref['content_restrict_managecontent'] = '0';
			$content_pref['content_restrict_createcontent'] = '0';
			$content_pref['content_restrict_managecat'] = '0';
			$content_pref['content_restrict_createcat'] = '0';
			$content_pref['content_restrict_order'] = '0';
			$content_pref['content_restrict_options'] = '0';
			$content_pref['content_restrict_adminmanager'] = '0';
			$content_pref['content_restrict_restrict'] = '0';
			$content_pref['content_restrict_recent'] = '0';
			$content_pref['content_restrict_allcat'] = '0';
			$content_pref['content_restrict_onecat'] = '0';
			$content_pref['content_restrict_contentitem'] = '0';
			$content_pref['content_restrict_author'] = '0';
			$content_pref['content_restrict_archive'] = '0';
			$content_pref['content_restrict_toprated'] = '0';
			$content_pref['content_restrict_topscore'] = '0';
			$content_pref['content_restrict_submit'] = '0';
			$content_pref['content_restrict_frontmanager'] = '0';

			return $content_pref;
		}



		function getContentPref($id="") {
			global $sql, $plugintable, $qs, $tp, $eArrayStorage;

			$plugintable = "pcontent";

//echo "get content pref : ".$id."<br />";

			if($id && $id!="0"){	//if $id; use prefs from content table
				$id = intval($id);
				$num_rows = $sql -> db_Select($plugintable, "content_pref", "content_id='$id' ");
				$row = $sql -> db_Fetch();
				if (empty($row['content_pref'])) {
					//if no prefs present yet, get them from core (default preferences)
					$num_rows = $sql -> db_Select("core", "*", "e107_name='$plugintable' ");
					//if those are not present, insert the default ones given in this file
					if ($num_rows == 0) {
						$content_pref = $this -> ContentDefaultPrefs();
						$tmp = $eArrayStorage->WriteArray($content_pref);
						$sql -> db_Insert("core", "'$plugintable', '{$tmp}' ");
						$sql -> db_Select("core", "*", "e107_name='$plugintable' ");
					}
					$row = $sql -> db_Fetch();
					$content_pref = $eArrayStorage->ReadArray($row['e107_value']);
					
					//create array of custom preset tags
					foreach($content_pref['content_custom_preset_key'] as $ck => $cv){
						if(!empty($cv)){
							$string[] = $cv;
						}
					}
					if($string){
						$content_pref['content_custom_preset_key'] = $string;
					}else{
						unset($content_pref['content_custom_preset_key']);
					}

					//finally we can store the new default prefs into the db
					$tmp1 = $eArrayStorage->WriteArray($content_pref);
					$sql -> db_Update($plugintable, "content_pref='{$tmp1}' WHERE content_id='$id' ");
					$sql -> db_Select($plugintable, "content_pref", "content_id='$id' ");
					$row = $sql -> db_Fetch();
				}
				$content_pref = $eArrayStorage->ReadArray($row['content_pref']);

				if(e_PAGE == "admin_content_config.php" && isset($qs[0]) && $qs[0] == 'option'){
				}else{
					//check inheritance, if set, get core prefs (default prefs)
					if(isset($content_pref['content_inherit']) && $content_pref['content_inherit']!=''){
						$sql -> db_Select("core", "*", "e107_name='$plugintable' ");
						$row = $sql -> db_Fetch();
						$content_pref = $eArrayStorage->ReadArray($row['e107_value']);
					}
				}

			}else{					//if not $id; use prefs from default core table
				$num_rows = $sql -> db_Select("core", "*", "e107_name='$plugintable' ");
				if ($num_rows == 0) {
					$content_pref = $this -> ContentDefaultPrefs();
					$tmp = $eArrayStorage->WriteArray($content_pref);
					$sql -> db_Insert("core", "'$plugintable', '{$tmp}' ");
					$sql -> db_Select("core", "*", "e107_name='$plugintable' ");
				}
				$row = $sql -> db_Fetch();
				$content_pref = $eArrayStorage->ReadArray($row['e107_value']);
			}
			return $content_pref;
		}

		//admin
		function UpdateContentPref($id){
			global $qs, $plugintable, $sql, $tp, $eArrayStorage;

			if(!is_object($sql)){ $sql = new db; }

			//insert default preferences into core
			if($id == "0"){
				$num_rows = $sql -> db_Select("core", "*", "e107_name='$plugintable' ");
				if ($num_rows == 0) {
					$sql -> db_Insert("core", "'$plugintable', '' ");
				}else{
					$row = $sql -> db_Fetch();
				}

				//prepare custom tags: use the posted value
				$cp = $_POST['content_custom_preset_key'];

			//insert category preferences into plugintable
			}else{
				//first get the existing prefs and parent
				$sql -> db_Select($plugintable, "content_pref, content_parent", "content_id='".intval($id)."' ");
				$row = $sql -> db_Fetch();
				$current = $eArrayStorage->ReadArray($row['content_pref']);
				$currentparent = $row['content_parent'];

				//if we are updating options
				if(isset($qs[0]) && $qs[0] == 'option' ){
					//only use the manager prefs from the existing set
					foreach($current as $k => $v){
						if( strpos($k, "content_manager_") === 0 || strpos($k, "content_restrict_") === 0 ){
							$content_pref[$k] = $tp->toDB($v);
						}
					}

					//prepare custom tags: use the posted values
					$cp = $_POST['content_custom_preset_key'];

				//if we are updating manager
				}elseif(isset($qs[0]) && ($qs[0] == 'manager' || $qs[0] == 'restrict')){
					//if this is a top level category we need to keep all existing options
					if($currentparent=='0'){
						$content_pref = $current;
					}

					//prepare custom tags: use the existing content_pref values
					$cp = $content_pref['content_custom_preset_key'];
				}
			}

			//parse custom tags and covert them in $_POST values ($cp is derived above)
			$string = array();
			foreach($cp as $ck => $cv){
				if(!empty($cv)){
					$string[] = $cv;
				}
			}
			if(is_array($string) && !empty($string[0])){
				$_POST['content_custom_preset_key'] = $string;
			}

			//convert all $_POST to $content_pref for storage, and renew the existing stored prefs
			foreach($_POST as $k => $v){
				if(strpos($k, "content_") === 0){
					$content_pref[$k] = $tp->toDB($v);
				}
			}

			//create new array of preferences
			$tmp = $eArrayStorage->WriteArray($content_pref);

			//update core table
			if($id == "0"){
				$sql -> db_Update("core", "e107_value = '{$tmp}' WHERE e107_name = '$plugintable' ");
			//update plugin table
			}else{
				$sql -> db_Update($plugintable, "content_pref='{$tmp}' WHERE content_id='".intval($id)."' ");
			}

			return $content_pref;
		}


		function CONTENTREGEXP($var){
			return "(^|,)(".str_replace(",", "|", $var).")(,|$)";
		}

		function getCategoryTree($id, $parent, $classcheck=TRUE){
			//id	:	content_parent of an item
			global $plugintable, $datequery;
			global $agc;

			if($parent){
				$agc = "";
				$qrygc = " content_id = '".intval($parent)."' ";
			}else{
				$qrygc = " content_parent = '0' ";
			}
			if($id){
				$qrygc = " content_parent = '0.".intval($id)."' ";
			}

			if($classcheck == TRUE){
				$qrygc .= " AND content_class REGEXP '".e_CLASS_REGEXP."' ";
			}

			$datequery		= " AND content_datestamp < ".time()." AND (content_enddate=0 || content_enddate>".time().") ";

			$sqlgetcat = new db;
			if($sqlgetcat -> db_Select($plugintable, "content_id, content_heading, content_parent", " ".$qrygc." ".$datequery." " )){
				while($row = $sqlgetcat -> db_Fetch()){
					if($agc){
						if($row['content_parent'] != "0"){
							if(array_key_exists(substr($row['content_parent'],2), $agc)){
								if(is_array($agc[substr($row['content_parent'],2)])){
									$agc[$row['content_id']] = array_merge_recursive($agc[substr($row['content_parent'],2)], array($row['content_id'], $row['content_heading']));
								}else{
									$agc[$row['content_id']] = array($agc[substr($row['content_parent'],2)], array($row['content_id'], $row['content_heading']));
								}

							}else{
								$agc[$row['content_id']] = array($row['content_id'], $row['content_heading']);
							}
						}else{
							$agc[$row['content_id']] = array($row['content_id'], $row['content_heading']);
						}
					}else{
						$agc[$row['content_id']] = array($row['content_id'], $row['content_heading']);
					}
					$this -> getCategoryTree($row['content_id'], "", $classcheck);
				}
			}
			return $agc;
		}



		function getCrumbItem($id, $arr){
			//$id	:	content_parent of item
			//$arr	:	array of all categories
			$crumb = "";
			if(is_array($arr)){
				if(array_key_exists($id, $arr)){
					for($i=0;$i<count($arr[$id]);$i++){
						$crumb .= "<a href='".e_SELF."?cat.".$arr[$id][$i]."'>".$arr[$id][$i+1]."</a> > ";
						$i++;
					}
					$crumb = substr($crumb,0,-3);
				}
			}
			return $crumb;
		}

		function ShowNextPrev($mode='', $from='0', $number, $total){
			global $content_pref, $qs, $tp, $plugindir, $content_shortcodes, $CONTENT_NEXTPREV;

			if($total<=$number){
				return;
			}

			$modepref = ($mode ? "content_{$mode}_nextprev" : "content_nextprev");
			if(isset($content_pref[$modepref]) && $content_pref[$modepref]){
				$np_querystring = e_SELF."?[FROM]".(isset($qs[0]) ? ".".$qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
				$parms = $total.",".$number.",".$from.",".$np_querystring."";
				
				$CONTENT_NEXTPREV = $tp->parseTemplate("{NEXTPREV={$parms}}");

				if(!isset($CONTENT_NP_TABLE)){
					if(!$content_pref["content_theme"]){
						require_once($plugindir."templates/default/content_np_template.php");
					}else{
						if(is_readable($tp->replaceConstants($content_pref["content_theme"])."content_np_template.php")){
							require_once($tp->replaceConstants($content_pref["content_theme"])."content_np_template.php");
						}else{
							require_once($plugindir."templates/default/content_np_template.php");
						}
					}
				}
				echo $tp -> parseTemplate($CONTENT_NP_TABLE, FALSE, $content_shortcodes);
			}
		}

		function getCrumbPage($mode, $arr, $parent){
			global $qs, $ns, $content_pref, $plugintable;

			if(isset($content_pref["content_breadcrumb_{$mode}"]) && $content_pref["content_breadcrumb_{$mode}"]){
				$crumb = '';
				if(array_key_exists($parent, $arr)){
					$sep = (isset($content_pref["content_breadcrumb_seperator"]) ? $content_pref["content_breadcrumb_seperator"] : ">");
					if($content_pref["content_breadcrumb_base"] && isset($content_pref["content_breadcrumb_base"])){
						$crumb .= "<a href='".e_BASE."'>".CONTENT_LAN_58."</a> ".$sep." ";
					}
					if($content_pref["content_breadcrumb_self"] && isset($content_pref["content_breadcrumb_self"])){
						$crumb .= "<a href='".e_SELF."'>".CONTENT_LAN_59."</a> ".$sep." ";
					}
					for($i=0;$i<count($arr[$parent]);$i++){
						$crumb .= "<a href='".e_SELF."?cat.".$arr[$parent][$i]."'>".$arr[$parent][$i+1]."</a> ".$sep." ";
						$i++;
					}
				}
				if($qs[0] == "recent"){
					$crumb .= "<a href='".e_SELF."?recent.".$arr[$parent][0]."'>".CONTENT_LAN_60."</a>";
				}
				if($qs[0] == "author"){
					$crumb .= "<a href='".e_SELF."?author.list.".$arr[$parent][0]."'>".CONTENT_LAN_85."</a>";
					if(is_numeric($qs[1])){
						global $sql;
						$sql->db_Select($plugintable, "content_author","content_id='".intval($qs[1])."'");
						$row=$sql->db_Fetch();
						$au = $this->getAuthor($row['content_author']);
						$crumb .= " ".$sep." <a href='".e_SELF."?author.".$qs[1]."'>".$au[1]."</a>";
					}
				}
				if($qs[0] == "list"){
					$crumb .= "<a href='".e_SELF."?list.".$arr[$parent][0]."'>".CONTENT_LAN_13."</a>";
				}
				if($qs[0] == "top"){
					$crumb .= "<a href='".e_SELF."?top.".$arr[$parent][0]."'>".CONTENT_LAN_8."</a>";
				}
				if($qs[0] == "score"){
					$crumb .= "<a href='".e_SELF."?score.".$arr[$parent][0]."'>".CONTENT_LAN_12."</a>";
				}
				if($qs[0] == "content"){
					global $row;
					$crumb .= $row['content_heading'];
				}
				$crumb = trim($crumb);
				if(substr($crumb,-strlen(trim($sep))) == trim($sep)){
					$crumb = substr($crumb,0,-strlen(trim($sep)));
				}

				$crumb = "<div class='breadcrumb'>".$crumb."</div>";
				if(isset($content_pref["content_breadcrumb_rendertype"]) && $content_pref["content_breadcrumb_rendertype"] == "1"){
					echo $crumb;
					return "";
				}elseif(isset($content_pref["content_breadcrumb_rendertype"]) && $content_pref["content_breadcrumb_rendertype"] == "2"){
					$ns -> tablerender(CONTENT_LAN_24, $crumb);
					return "";
				}else{
					return $crumb;
				}
			}else{
				return "";
			}
		}



		function countCatItems($id){
			global $sqlcountitemsincat, $plugintable, $datequery;
			//$id	:	category content_id

			if(!is_object($sqlcountitemsincat)){ $sqlcountitemsincat = new db; }
			$n = $sqlcountitemsincat -> db_Count($plugintable, "(*)", "WHERE content_class REGEXP '".e_CLASS_REGEXP."' AND content_parent='".intval($id)."' AND content_refer != 'sa' ".$datequery." ");

			return $n;
		}


		function getCategoryHeading($id){
			global $plugintable, $sql;
			$qry = "
			SELECT c.*, p.*
			FROM #pcontent as c
			LEFT JOIN #pcontent as p ON p.content_id = c.content_parent
			WHERE c.content_id = '".intval($id)."' ";
			$sql -> db_Select_gen($qry);
			$row2 = $sql -> db_Fetch();
			return $row2['content_heading'];
		}
		function getPageHeading($id){
			global $plugintable, $sql;
			$sql -> db_Select($plugintable, "content_heading", "content_id='".intval($id)."' ");
			$row2 = $sql -> db_Fetch();
			return $row2['content_heading'];
		}
		function setPageTitle(){
			global $plugintable, $sql, $qs;

			//content page
			if(e_PAGE == "content.php"){
				//main parent overview
				if(!e_QUERY){
					$page = CONTENT_PAGETITLE_LAN_0;
				}else{
					$sql -> db_Select($plugintable, "content_heading", "content_id = '".intval($qs[1])."' ");
					$row = $sql -> db_Fetch();

					$page = CONTENT_PAGETITLE_LAN_0;

					//recent of parent='2'
					if($qs[0] == "recent" && is_numeric($qs[1]) && !isset($qs[2])){
						$page .= " / ".$row['content_heading']." / ".CONTENT_PAGETITLE_LAN_2;

					//item
					}elseif($qs[0] == "content" && isset($qs[1]) && is_numeric($qs[1]) ){
						$page .= " / ".$this -> getCategoryHeading($qs[1])." / ".$this -> getPageHeading($qs[1]);

					//all categories of parent='2'
					}elseif($qs[0] == "cat" && $qs[1] == "list" && is_numeric($qs[2])){
						$page .= " / ".$this -> getPageHeading($qs[2])." / ".CONTENT_PAGETITLE_LAN_13;

					//category of parent='2' and content_id='5'
					}elseif($qs[0] == "cat" && is_numeric($qs[1]) && (!isset($qs[2]) || isset($qs[2]) && $qs[2]=='view') ){
						$page .= " / ".CONTENT_PAGETITLE_LAN_3." / ".$row['content_heading'];

					//top rated of parent='2'
					}elseif($qs[0] == "top" && is_numeric($qs[1]) && !isset($qs[2])){
						$page .= " / ".$this -> getPageHeading($qs[1])." / ".CONTENT_PAGETITLE_LAN_4;

					//top score of parent='2'
					}elseif($qs[0] == "score" && is_numeric($qs[1]) && !isset($qs[2])){
						$page .= " / ".$this -> getPageHeading($qs[1])." / ".CONTENT_PAGETITLE_LAN_15;

					//authorlist of parent='2'
					}elseif($qs[0] == "author" && $qs[1] == "list" && is_numeric($qs[2])){
						$page .= " / ".$this -> getPageHeading($qs[2])." / ".CONTENT_PAGETITLE_LAN_14;

					//authorlist of parent='2' and content_id='5'
					}elseif($qs[0] == "author" && is_numeric($qs[1]) && !isset($qs[2])){
						$sql -> db_Select($plugintable, "content_author", "content_id='".intval($qs[1])."' ");
						$row2 = $sql -> db_Fetch();
						$authordetails = $this -> getAuthor($row2['content_author']);
						$page .= " / ".CONTENT_PAGETITLE_LAN_5." / ".$authordetails[1];

					//archive of parent='2'
					}elseif($qs[0] == "list" && is_numeric($qs[1]) && !isset($qs[2])){
						$page .= " / ".CONTENT_PAGETITLE_LAN_6;
					}
				}

			}elseif(e_PAGE == "content_submit.php"){
				//submit page : view categories
				if(!e_QUERY){
					$page = CONTENT_PAGETITLE_LAN_0." / ".CONTENT_PAGETITLE_LAN_7;
				}else{
					$page = CONTENT_PAGETITLE_LAN_0;

					//submit page : submit item
					if($qs[0] == "content" && $qs[1] == "submit" && is_numeric($qs[2]) ){
						$page = " / ".CONTENT_PAGETITLE_LAN_8;
					}
				}

			}elseif(e_PAGE == "content_manager.php"){
				//manager page : view categories
				if(!e_QUERY){
					$page = CONTENT_PAGETITLE_LAN_0." / ".CONTENT_PAGETITLE_LAN_9;
				}else{
					$page = CONTENT_PAGETITLE_LAN_0." / ".CONTENT_PAGETITLE_LAN_9;

					//manager page : view items
					if($qs[0] == "content" && is_numeric($qs[1]) ){
						$page .= " / ".CONTENT_PAGETITLE_LAN_10;

					//manager page : edit item
					}elseif($qs[0] == "content" && $qs[1] == "edit" && is_numeric($qs[2]) ){
						$page .= " / ".CONTENT_PAGETITLE_LAN_11;

					//manager page : create new item
					}elseif($qs[0] == "content" && $qs[1] == "create" && is_numeric($qs[2]) ){
						$page .= " / ".CONTENT_PAGETITLE_LAN_12;
					}
				}
			}
			define("e_PAGETITLE", $page);

		}



		function getAuthor($content_author) {
			global $sql, $plugintable, $datequery;

			if(is_numeric($content_author)){
				if(!$sql -> db_Select("user", "user_id, user_name, user_email", "user_id=$content_author")){
					$author_id = "0";
					$author_name = "";
					$author_email = "";
				}else{
					list($author_id, $author_name, $author_email) = $sql -> db_Fetch();
				}
				$getauthor = array($author_id, $author_name, $author_email, $content_author);
			}else{
				$tmp = explode("^", $content_author);
				if(isset($tmp[0]) && is_numeric($tmp[0]) ){
					$author_id		= $tmp[0];
					$author_name	= (isset($tmp[1]) ? $tmp[1] : "");
					$author_email	= (isset($tmp[2]) ? $tmp[2] : "");
				}else{
					$author_id		= "0";
					$author_name	= $tmp[0];
					$author_email	= (isset($tmp[1]) ? $tmp[1] : "");
				}
				$getauthor = array($author_id, $author_name, $author_email, $content_author);
			}
			return $getauthor;
		}



		function getMainParent($id){
			global $sql, $plugintable;

			$category_total = $sql -> db_Select($plugintable, "content_id, content_parent", "content_id='".intval($id)."' ");
			$row = $sql -> db_Fetch();
			if($row['content_parent'] == 0){
				$mainparent = $row['content_id'];
			}else{
				if(strpos($row['content_parent'], ".")){
					$newid = substr($row['content_parent'],2);
				}else{
					$newid = $row['content_parent'];
				}
				$mainparent = $this -> getMainParent( $newid );
			}
			return ($mainparent ? $mainparent : "0");
		}



		//$mode : managecontent, createcontent, category
		function ShowOption($currentparent="", $mode=''){
			global $qs, $sql, $rs, $plugintable, $tp, $content_pref, $stylespacer;

			if( ($mode=='managecontent' || $mode=='createcontent') && $currentparent == "submit"){
				$mainparent		= $this -> getMainParent( intval($qs[2]) );
				$catarray		= $this -> getCategoryTree("", intval($mainparent), FALSE);
			}else{
				$catarray = $this -> getCategoryTree("", "", FALSE);
			}
			$array = array_keys($catarray);

			$string = "";
			foreach($array as $catid){
				$category_total = $sql -> db_Select($plugintable, "content_id, content_heading, content_parent", "content_id='".intval($catid)."' ");
				$row = $sql -> db_Fetch();

				$pre = "";
				//sub level
				if($row['content_parent'] != "0"){
					for($b=0;$b<(count($catarray[$catid])/2)-1;$b++){
						$pre .= "&nbsp;&nbsp;";
					}
				}
				if($row['content_parent'] == 0){
					$name	= $row['content_heading'];
					$js		= "style='font-weight:bold;'";
				}else{
					$js		= "";
					$name	= $pre.$row['content_heading'];
				}

				if($mode=='managecontent'){
					$checkid	= ($currentparent ? $currentparent : "");
					if($qs[0] == 'content' && ($qs[1]=='create' || $qs[1]=='submit') ){
						$value		= e_SELF."?content.".$qs[1].".".$catid;
					}else{
						$value		= e_SELF."?content.".$catid;
					}
				}elseif($mode=='createcontent'){
					if($qs[1] == "create" || $qs[1] == "submit"){
						$checkid	= (isset($qs[2]) && is_numeric($qs[2]) ? $qs[2] : "");
						$value		= $catid;
					}else{
						$checkid	= ($currentparent ? $currentparent : "");
						$value		= $qs[2].".".$catid;
					}
				}elseif($mode=='category'){
					if($qs[1] == "create"){
						$checkid	= (isset($qs[2]) && is_numeric($qs[2]) ? $qs[2] : "");
						$value		= e_SELF."?cat.create.".$catid;
					}elseif($qs[1] == "edit"){
						$checkid	= ($currentparent ? $currentparent : "");
						$value		= e_SELF."?cat.edit.".$qs[2].".".$catid;
					}
				}
				$sel = ($catid == $checkid ? "1" : "0");
				$string	.= $rs -> form_option($name, $sel, $value, $js);
			}

			if($mode=='managecontent'){
				$selectjs = " onchange=\" if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; } \"";
				$text  = $rs -> form_select_open("parent1", $selectjs);
				$text .= $rs -> form_option(CONTENT_ADMIN_MAIN_LAN_28, "0", "none");
				$text .= $string;
				$text .= $rs -> form_select_close();

			}elseif($mode=='createcontent'){
				$redirecturl = e_SELF."?content.".$qs[1].".";
				$selectjs = " onchange=\" if(this.options[this.selectedIndex].value != 'none'){ return document.location='".$redirecturl."'+this.options[this.selectedIndex].value; } \"";
				$text  = $rs -> form_select_open("parent1", $selectjs);
				$text .= $rs -> form_option(CONTENT_ADMIN_MAIN_LAN_28, "0", "none");
				$text .= $string;
				$text .= $rs -> form_select_close();

			}elseif($mode=='category'){
				$selectjs = " onchange=\" if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; } \"";
				$text = $rs -> form_select_open("parent1", $selectjs);
				if($qs[1] == "create"){
					$text .= $rs -> form_option(CONTENT_ADMIN_MAIN_LAN_29."&nbsp;&nbsp;", (isset($qs[2]) ? "0" : "1"), e_SELF."?cat.create", "style='font-weight:bold;'");
				}else{
					$text .= $rs -> form_option(CONTENT_ADMIN_MAIN_LAN_29."&nbsp;&nbsp;", (isset($qs[2]) ? "0" : "1"), e_SELF."?cat.edit.".$qs[2].".0", "style='font-weight:bold;'");
				}
				$text .= $string;
				$text .= $rs -> form_select_close();
			}
			return $text;
		}



		function getOrder(){
			global $qs, $content_pref;

			if(isset($qs[0]) && substr($qs[0],0,5) == "order"){
				$orderstring	= $qs[0];
			}elseif(isset($qs[1]) && substr($qs[1],0,5) == "order"){
				$orderstring	= $qs[1];
			}elseif(isset($qs[2]) && substr($qs[2],0,5) == "order"){
				$orderstring	= $qs[2];
			}elseif(isset($qs[3]) && substr($qs[3],0,5) == "order"){
				$orderstring	= $qs[3];
			}else{
				$orderstring	= ($content_pref["content_defaultorder"] ? $content_pref["content_defaultorder"] : "orderddate" );
			}

			if(substr($orderstring,6) == "heading"){
				$orderby		= "content_heading";
				$orderby2		= "";
			}elseif(substr($orderstring,6) == "date"){
				$orderby		= "content_datestamp";
				$orderby2		= ", content_heading ASC";
			}elseif(substr($orderstring,6) == "parent"){
				$orderby		= "content_parent";
				$orderby2		= ", content_heading ASC";
			}elseif(substr($orderstring,6) == "refer"){
				$orderby		= "content_refer";
				$orderby2		= ", content_heading ASC";
			}elseif(substr($orderstring,6) == "author"){

			}elseif(substr($orderstring,6) == "order"){
				if($qs[0] == "cat"){
					$orderby	= "SUBSTRING_INDEX(content_order, '.', 1)+0";
				}elseif($qs[0] != "cat"){
					$orderby	= "SUBSTRING_INDEX(content_order, '.', -1)+0";
				}
				$orderby2		= ", content_heading ASC";
			}else{
				$orderstring	= "orderddate";
				$orderby		= "content_datestamp";
				$orderby2		= ", content_heading ASC";
			}
			$order = " ORDER BY ".$orderby." ".(substr($orderstring,5,1) == "a" ? "ASC" : "DESC")." ".$orderby2." ";
			return $order;
		}



		function getIcon($mode, $icon, $path="", $linkid="", $width="", $blank=""){
			global $content_cat_icon_path_small, $content_cat_icon_path_large, $content_icon_path, $content_pref;

			$blank			= (!$blank ? "0" : $blank);
			$border			= "border:0;";
			$hrefpre		= ($linkid ? "<a href='".e_SELF."?".$linkid."'>" : "");
			$hrefpost		= ($linkid ? "</a>" : "");

			if($mode == "item"){
				$path		= (!$path ? $content_icon_path : $path);
				$width		= ($width ? "width:".$width."px;" : "");
				//$border		= "border:1px solid #000;";
				$border		= '';
				$icon		= ($icon ? $path.$icon : ($blank ? $content_icon_path."blank.gif" : ""));

			}elseif($mode == "catsmall"){
				$path		= (!$path ? $content_cat_icon_path_small : $path);
				$icon		= ($icon ? $path.$icon : "");

			}elseif($mode == "catlarge"){
				$path		= (!$path ? $content_cat_icon_path_large : $path);
				$icon		= ($icon ? $path.$icon : "");
			}else{
				$path		= (!$path ? $content_icon_path : $path);
				$hrefpre	= "";
				$hrefpost	= "";
				$width		= "";
				$icon		= ($icon ? $path.$icon : ($blank ? $content_icon_path."blank.gif" : ""));
			}

			if($icon && file_exists($icon)){
				$iconstring	= $hrefpre."<img src='".$icon."' alt='' style='".$width." ".$border."' />".$hrefpost;
			}else{
				$iconstring = "";
				if($blank){
					if(file_exists($content_icon_path."blank.gif")){
						if($mode == "catsmall"){
							$width = ($width ? "width:".$width."px;" : "width:16px;");
						}elseif($mode == "catlarge"){
							$width = ($width ? "width:".$width."px;" : "width:48px;");
						}
						$iconstring = $hrefpre."<img src='".$content_icon_path."blank.gif' alt='' style='".$width." ".$border."' />".$hrefpost;
					}
				}
			}
			return $iconstring;
		}

		function prepareAuthor($mode, $author, $id){
			global $aa, $content_pref;
			if($mode == ''){return;}

			$authorinfo = "";
			if( (isset($content_pref["content_{$mode}_authorname"]) && $content_pref["content_{$mode}_authorname"]) || (isset($content_pref["content_{$mode}_authoremail"]) && $content_pref["content_{$mode}_authoremail"]) || (isset($content_pref["content_{$mode}_authoricon"]) && $content_pref["content_{$mode}_authoricon"]) || (isset($content_pref["content_{$mode}_authorprofile"]) && $content_pref["content_{$mode}_authorprofile"]) ){
				$authordetails = $this -> getAuthor($author);
				if(isset($content_pref["content_{$mode}_authorname"]) && $content_pref["content_{$mode}_authorname"]){
					if(isset($content_pref["content_{$mode}_authoremail"]) && $authordetails[2]){
						if($authordetails[0] == "0"){
							if(isset($content_pref["content_{$mode}_authoremail_nonmember"]) && $content_pref["content_{$mode}_authoremail_nonmember"] && strpos($authordetails[2], "@") ){
								//$authorinfo = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";
								
								$authorinfo = preg_replace("#([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"\\1\"+\"@\"+\"\\2\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"\\1\"+\"@\"+\"\\2\"; return true;' onmouseout='window.status=\"\";return true;'>".$authordetails[1]."</a>", $authordetails[2]);
							}else{
								$authorinfo = $authordetails[1];
							}
						}else{
							//$authorinfo = "<a href='mailto:".$authordetails[2]."'>".$authordetails[1]."</a>";

							$authorinfo = preg_replace("#([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"\\1\"+\"@\"+\"\\2\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"\\1\"+\"@\"+\"\\2\"; return true;' onmouseout='window.status=\"\";return true;'>".$authordetails[1]."</a>", $authordetails[2]);
						}
					}else{
						$authorinfo = $authordetails[1];
					}
					if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0" && isset($content_pref["content_{$mode}_authorprofile"]) && $content_pref["content_{$mode}_authorprofile"]){
						$authorinfo .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
					}
				}
				if(isset($content_pref["content_{$mode}_authoricon"]) && $content_pref["content_{$mode}_authoricon"]){
					$authorinfo .= " <a href='".e_SELF."?author.".$id."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
				}
			}
			return $authorinfo;
		}


		//admin
		function popupHelp($text, $image="", $width="320", $title=""){
			//$image	:	full path to the image you want to show on screen (uses a default doc image)
			//$width	:	the width of the popup (uses a default width of 500)
			//$title	:	the window title of the popup (uses a default title of ...)
			//$text		:	the help text to show into the popup

			if(!$image || !file_exists($image)){
				$image = e_IMAGE."admin_images/docs_16.png";
			}
			if(!$width){ $width = "320"; }
			if(!$title){ $title = "content management help area"; }

			$popup = "<a href=\"javascript:openHelpPopup(".$width.",'".$title."','".$text."')\" style='cursor:pointer;' onmouseover=\"window.status='click for help on this page'; return true;\" onmouseout=\"window.status=''; return true;\" ><img src='".$image."' style='border:0;' alt='' /></a>";

			return $popup;
		}



		//search by keyword
		function showOptionsSearch($mode, $searchtypeid=""){
			global $plugindir, $plugintable, $qs, $rs;

			if(!is_object($rs)){
				require_once(e_HANDLER."form_handler.php");
				$rs = new form;
			}
			if(!isset($searchtypeid)){
				$searchtypeid = (is_numeric($qs[1]) ? $qs[1] : $qs[2]);
			}

			if($mode == "menu"){
				$CONTENT_SEARCH_TABLE_KEYWORD = $rs -> form_open("post", $plugindir."content.php?recent.$searchtypeid", "contentsearchmenu_{$mode}", "", "enctype='multipart/form-data'")."<div><input class='tbox' size='20' type='text' id='searchfieldmenu_{$mode}' name='searchfieldmenu_{$mode}' value='".(isset($_POST['searchfieldmenu_{$mode}']) ? $_POST['searchfieldmenu_{$mode}'] : CONTENT_LAN_18)."' maxlength='100' onfocus=\"document.forms['contentsearchmenu_{$mode}'].searchfieldmenu_$mode.value='';\" /> <input class='button' type='submit' name='searchsubmit' value='".CONTENT_LAN_19."' /></div>".$rs -> form_close();
			}else{
				$searchfieldname = "searchfield_{$mode}";
				$CONTENT_SEARCH_TABLE_KEYWORD = $rs -> form_open("post", $plugindir."content.php?recent.$searchtypeid", "contentsearch_{$mode}", "", "enctype='multipart/form-data'")."<div>
				<input class='tbox' size='27' type='text' id='$searchfieldname' name='$searchfieldname' value='".(isset($_POST[$searchfieldname]) ? $_POST[$searchfieldname] : CONTENT_LAN_18)."' maxlength='100' onfocus=\"document.forms['contentsearch_{$mode}'].$searchfieldname.value='';\" />
				<input class='button' type='submit' name='searchsubmit' value='".CONTENT_LAN_19."' /></div>
				".$rs -> form_close();
			}
			return $CONTENT_SEARCH_TABLE_KEYWORD;
		}



		//redirection links in dropdown
		function showOptionsSelect($mode, $searchtypeid=""){
			global $plugindir, $plugintable, $rs, $qs, $content_pref;

			if(!is_object($rs)){
				require_once(e_HANDLER."form_handler.php");
				$rs = new form;
			}
			if(!isset($searchtypeid)){
				$searchtypeid = (is_numeric($qs[1]) ? $qs[1] : $qs[2]);
			}
			$catarray		= "";
			$mainparent		= $this -> getMainParent( $searchtypeid );
			$content_pref	= $this -> getContentPref($mainparent);
			$parent			= $this -> getCategoryTree("", $mainparent, TRUE);
			$parent			= array_merge_recursive($parent);
			for($a=0;$a<count($parent);$a++){
				for($b=0;$b<count($parent[$a]);$b++){
					$newparent[$parent[$a][$b]] = $parent[$a][$b+1];
					$b++;
				}
			}
			if($newparent){
				$emptystring = CONTENT_LAN_14;
				$catarray = $rs -> form_option($emptystring, "0", "none");
			}
			foreach($newparent as $key => $value){
				$n = "";
				if($mode == "page" || ($mode == "menu" && isset($content_pref["content_menu_cat_number"])) ){
					$n = $this -> countCatItems($key);
					$n = " (".$n." ".($n == "1" ? CONTENT_LAN_53 : CONTENT_LAN_54).")";
				}
				if( ($content_pref["content_menu_cat_main"] && $key == $mainparent) || $key != $mainparent ){
					$value = (strlen($value) > 25 ? substr($value,0,25)."..." : $value);
					$catarray .= $rs -> form_option($value.$n, 0, $plugindir."content.php?cat.".$key);
				}
			}

			if($mode == "page" || ($mode == "menu" && ($content_pref["content_menu_links"] && $content_pref["content_menu_links_dropdown"]) || ($content_pref["content_menu_cat"] && $content_pref["content_menu_cat_dropdown"]) ) ){
				if($mode == "menu"){ $style = "style='width:100%;' "; }else{ $style = ""; }
				$CONTENT_SEARCH_TABLE_SELECT = "
				".$rs -> form_open("post", $plugindir."content.php".(e_QUERY ? "?".e_QUERY : ""), "contentredirect".$mode, "", "enctype='multipart/form-data'")."				
				<div><select id='{$mode}value' name='{$mode}value' class='tbox' $style onchange=\"if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; }\">";					

				if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_links"] && $content_pref["content_menu_links_dropdown"]) ){
					$CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_56, 1, "none").$rs -> form_option("&nbsp;", "0", "none");

					if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewallcat"])){
					   $CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_6, 0, $plugindir."content.php?cat.list.".$mainparent);
					}
					if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewallauthor"])){
					   $CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_7, 0, $plugindir."content.php?author.list.".$mainparent);
					}
					if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewallitems"])){
					   $CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_83, 0, $plugindir."content.php?list.".$mainparent);
					}
					if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewtoprated"])){
					   $CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_8, 0, $plugindir."content.php?top.".$mainparent);
					}
					if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewtopscore"])){
					   $CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_12, 0, $plugindir."content.php?score.".$mainparent);
					}
					if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewrecent"])){
					   $CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_61, 0, $plugindir."content.php?recent.".$mainparent);
					}
					if( ($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewsubmit"]) && $content_pref["content_submit"] && check_class($content_pref["content_submit_class"]) ) ){
						$CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_75, 0, $plugindir."content_submit.php");
					}
					$CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option("&nbsp;", "0", "none");
				}
				if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_cat"] && $content_pref["content_menu_cat_dropdown"])){
					$CONTENT_SEARCH_TABLE_SELECT .= $catarray;
				}
				$CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_select_close()."</div>".$rs -> form_close();
			}
			return $CONTENT_SEARCH_TABLE_SELECT;
		}



		//ordering in dropdown
		function showOptionsOrder($mode, $ordertypeid=""){
			global $plugindir, $rs, $qs;

			if(!is_object($rs)){
				require_once(e_HANDLER."form_handler.php");
				$rs = new form;
			}
			if(!isset($ordertypeid)){
				$ordertypeid = (is_numeric($qs[1]) ? $qs[1] : $qs[2]);
			}

			$text = "";
			if(strpos(e_SELF, 'content.php') !== FALSE){
				if(e_QUERY){
					$check = "";
					for($i=0;$i<count($qs);$i++){
						if($qs[$i] && substr($qs[$i],0,5) == "order"){
							$check = $qs[$i];
							break;
						}
					}
					$baseurl = $plugindir."content.php";
					$qry = (isset($qs[0]) && substr($qs[0],0,5) != "order" ? $qs[0] : "").(isset($qs[1]) && substr($qs[1],0,5) != "order" ? ".".$qs[1] : "").(isset($qs[2]) && substr($qs[2],0,5) != "order" ? ".".$qs[2] : "").(isset($qs[3]) && substr($qs[3],0,5) != "order" ? ".".$qs[3] : "");
					$text = $rs -> form_open("post", $baseurl."?$qs[0].$ordertypeid", "contentsearchorder{$mode}", "", "enctype='multipart/form-data'");
					$text .= "<div><select id='ordervalue{$mode}' name='ordervalue{$mode}' class='tbox' onchange=\"if(this.options[this.selectedIndex].value != 'none'){ return document.location=this.options[this.selectedIndex].value; }\">";
					$text .= $rs -> form_option(CONTENT_ORDER_LAN_0, 1, "none");

					if($qs[0] == "author" && $qs[1] == "list"){
						$text .= $rs -> form_option(CONTENT_ORDER_LAN_11, ($check == "orderaauthor" ? "1" : "0"), $baseurl."?".$qry.".orderaauthor" );
						$text .= $rs -> form_option(CONTENT_ORDER_LAN_12, ($check == "orderdauthor" ? "1" : "0"), $baseurl."?".$qry.".orderdauthor" );
					}else{
						$text .= $rs -> form_option(CONTENT_ORDER_LAN_1, ($check == "orderaheading" ? "1" : "0"), $baseurl."?".$qry.".orderaheading" );
						$text .= $rs -> form_option(CONTENT_ORDER_LAN_2, ($check == "orderdheading" ? "1" : "0"), $baseurl."?".$qry.".orderdheading" );
						$text .= $rs -> form_option(CONTENT_ORDER_LAN_3, ($check == "orderadate" ? "1" : "0"), $baseurl."?".$qry.".orderadate" );
						$text .= $rs -> form_option(CONTENT_ORDER_LAN_4, ($check == "orderddate" ? "1" : "0"), $baseurl."?".$qry.".orderddate" );
						$text .= $rs -> form_option(CONTENT_ORDER_LAN_5, ($check == "orderarefer" ? "1" : "0"), $baseurl."?".$qry.".orderarefer" );
						$text .= $rs -> form_option(CONTENT_ORDER_LAN_6, ($check == "orderdrefer" ? "1" : "0"), $baseurl."?".$qry.".orderdrefer" );
						$text .= $rs -> form_option(CONTENT_ORDER_LAN_7, ($check == "orderaparent" ? "1" : "0"), $baseurl."?".$qry.".orderaparent" );
						$text .= $rs -> form_option(CONTENT_ORDER_LAN_8, ($check == "orderdparent" ? "1" : "0"), $baseurl."?".$qry.".orderdparent" );
						$text .= $rs -> form_option(CONTENT_ORDER_LAN_9, ($check == "orderaorder" ? "1" : "0"), $baseurl."?".$qry.".orderaorder" );
						$text .= $rs -> form_option(CONTENT_ORDER_LAN_10, ($check == "orderdorder" ? "1" : "0"), $baseurl."?".$qry.".orderdorder" );
					}
					$text .= $rs -> form_select_close();
					$text .= "</div>";
					$text .= $rs -> form_close();
				}
			}
			return $text;
		}



		function CreateParentMenu($parentid){
			global $plugintable, $plugindir, $tp, $datequery;

			if(!is_object($sqlcreatemenu)){ $sqlcreatemenu = new db; }
			if(!$sqlcreatemenu -> db_Select($plugintable, "*", "content_id='".intval($parentid)."'  ")){
				return FALSE;
			}else{
				$row = $sqlcreatemenu -> db_Fetch();
			}
			
			$content_path_menu			= $plugindir."menus/";
			if(!is_writable($content_path_menu)){
				echo "<b>".CONTENT_ADMIN_ITEM_LAN_22." ".$content_path_menu." ".CONTENT_ADMIN_ITEM_LAN_23."</b><br />";
				return FALSE;
			}

			$menufile = "content_".$row['content_heading'];
			$menuname = $row['content_heading'];

			$data = chr(60)."?php\n". chr(47)."*\n+---------------------------------------------------------------+\n|        e107 website system\n|        ".e_PLUGIN."content/menus/".$menufile."_menu.php\n|\n|        (C)Steve Dunstan 2001-2002\n|        http://e107.org\n|        jalist@e107.org\n|\n|        Released under the terms and conditions of the\n|        GNU General Public License (http://gnu.org).\n+---------------------------------------------------------------+\n\nThis file has been generated by ".e_PLUGIN."content/handlers/content_class.php.\n\n*". chr(47)."\n\n";
			$data .= "\n";
			$data .= "unset(\$text);\n";
			$data .= "\$text = \"\";\n";
			$data .= "\$menutypeid		= \"$parentid\";\n";
			$data .= "\$menuname		= \"$menuname\";\n";
			$data .= "\n";
			$data .= "\$plugindir		= e_PLUGIN.'content/';\n";
			$data .= "\$plugintable	= \"pcontent\";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)\n";
			$data .= "\$datequery		= \" AND content_datestamp < \".time().\" AND (content_enddate=0 || content_enddate>\".time().\") \";\n";
			$data .= "\n";
			$data .= "require_once(e_PLUGIN.'content/handlers/content_class.php');\n";
			$data .= "\$aa = new content;\n";
			$data .= "require_once(e_HANDLER.'form_handler.php');\n";
			$data .= "\$rs = new form;\n";
			$data .= "\$gen = new convert;\n";
			$data .= "global \$tp;\n";
			$data .= "\n";
			$data .= "include_lan(e_PLUGIN.'content/languages/'.e_LANGUAGE.'/lan_content.php');\n";
			$data .= '
					$bullet = \'\';
					if(defined(\'BULLET\'))
					{
						$bullet = \'<img src="\'.THEME.\'images/\'.BULLET.\'" alt="" style="vertical-align: middle;" />\';
					}
					elseif(file_exists(THEME.\'images/bullet2.gif\'))
					{
						$bullet = \'<img src="\'.THEME.\'images/bullet2.gif" alt="" style="vertical-align: middle;" />\';
					}
			';

			$data .= "\$content_pref					= \$aa -> getContentPref(\$menutypeid);\n";
			$data .= "\$content_icon_path				= \$tp -> replaceConstants(\$content_pref[\"content_icon_path\"]);\n";
			$data .= "\$content_cat_icon_path_small	= \$tp -> replaceConstants(\$content_pref[\"content_cat_icon_path_small\"]);\n";
			$data .= "\n";
			$data .= "	\$break = FALSE;\n";
			$data .= "//##### SEARCH SELECT ORDER --------------------------------------------------\n";
			$data .= "//show search box\n";
			$data .= "if(\$content_pref[\"content_menu_search\"]){\n";
			$data .= "	\$text .= \$aa -> showOptionsSearch(\"menu\", \$menutypeid);\n";
			$data .= "	\$break = TRUE;\n";
			$data .= "}\n";
			$data .= "//show select box (with either links to other content pages, to categories, to both, or don't show at all)\n";
			$data .= "if( (\$content_pref[\"content_menu_links\"] && \$content_pref[\"content_menu_links_dropdown\"]) || (\$content_pref[\"content_menu_cat\"] && \$content_pref[\"content_menu_cat_dropdown\"]) ){\n";
			$data .= "	\$text .= \$aa -> showOptionsSelect(\"menu\", \$menutypeid);\n";
			$data .= "	\$break = TRUE;\n";
			$data .= "}\n";
			$data .= "//show order box\n";
			$data .= "if(\$content_pref[\"content_menu_sort\"]){\n";
			$data .= "	\$text .= \$aa -> showOptionsOrder(\"menu\", \$menutypeid);\n";
			$data .= "	\$break = TRUE;\n";
			$data .= "}\n";
			$data .= "\n";
			$data .= "//show links list if chosen so\n";
			$data .= "if(\$content_pref[\"content_menu_links\"] && !\$content_pref[\"content_menu_links_dropdown\"]){\n";
			$data .= "	if(\$break === TRUE){\n";
			$data .= "	   \$text .= \"<br />\";\n";
			$data .= "	}\n";
			$data .= "	\$text .= (\$content_pref[\"content_menu_links_caption\"] != \"\" ? \$content_pref[\"content_menu_links_caption\"] : CONTENT_MENU_LAN_4).\"<br />\";\n";
			$data .= "\n";
			$data .= "	//define icon\n";
			$data .= "	if(\$content_pref[\"content_menu_links_icon\"] == \"0\"){ \$linksicon = \"\";\n";
			$data .= "	}elseif(\$content_pref[\"content_menu_links_icon\"] == \"1\"){ \$linksicon = \$bullet;\n";
			$data .= "	}elseif(\$content_pref[\"content_menu_links_icon\"] == \"2\"){ \$linksicon = \"&middot\";\n";
			$data .= "	}elseif(\$content_pref[\"content_menu_links_icon\"] == \"3\"){ \$linksicon = \"&ordm;\";\n";
			$data .= "	}elseif(\$content_pref[\"content_menu_links_icon\"] == \"4\"){ \$linksicon = \"&raquo;\";\n";
			$data .= "	}\n";
			$data .= "\n";
			$data .= "	if(\$content_pref[\"content_menu_viewallcat\"]){\n";
			$data .= "		\$text .= \$linksicon.\" <a href='\".\$plugindir.\"content.php?cat.list.\".\$menutypeid.\"'>\".CONTENT_LAN_6.\"</a><br />\";\n";
			$data .= "	}\n";
			$data .= "	if(\$content_pref[\"content_menu_viewallauthor\"]){\n";
			$data .= "		\$text .= \$linksicon.\" <a href='\".\$plugindir.\"content.php?author.list.\".\$menutypeid.\"'>\".CONTENT_LAN_7.\"</a><br />\";\n";
			$data .= "	}\n";
			$data .= "	if(\$content_pref[\"content_menu_viewallitems\"]){\n";
			$data .= "		\$text .= \$linksicon.\" <a href='\".\$plugindir.\"content.php?list.\".\$menutypeid.\"'>\".CONTENT_LAN_83.\"</a><br />\";\n";
			$data .= "	}\n";
			$data .= "	if(\$content_pref[\"content_menu_viewtoprated\"]){\n";
			$data .= "		\$text .= \$linksicon.\" <a href='\".\$plugindir.\"content.php?top.\".\$menutypeid.\"'>\".CONTENT_LAN_8.\"</a><br />\";\n";
			$data .= "	}\n";
			$data .= "	if(\$content_pref[\"content_menu_viewtopscore\"]){\n";
			$data .= "		\$text .= \$linksicon.\" <a href='\".\$plugindir.\"content.php?score.\".\$menutypeid.\"'>\".CONTENT_LAN_12.\"</a><br />\";\n";
			$data .= "	}\n";
			$data .= "	if(\$content_pref[\"content_menu_viewrecent\"]){\n";
			$data .= "		\$text .= \$linksicon.\" <a href='\".\$plugindir.\"content.php?recent.\".\$menutypeid.\"'>\".CONTENT_LAN_61.\"</a><br />\";\n";
			$data .= "	}\n";
			$data .= "	if( \$content_pref[\"content_menu_viewsubmit\"] && \$content_pref[\"content_submit\"] && check_class(\$content_pref[\"content_submit_class\"]) ){\n";
			$data .= "		\$text .= \$linksicon.\" <a href='\".\$plugindir.\"content_submit.php'>\".CONTENT_LAN_75.\"</a><br />\";\n";
			$data .= "	}\n";
			$data .= "	\$text .= \"<br />\";\n";
			$data .= "}\n";
			$data .= "\n";
			$data .= "//get category array\n";
			$data .= "\$array = \$aa -> getCategoryTree(\"\", intval(\$menutypeid), TRUE);\n";
			$data .= "\n";
			$data .= "//##### CATEGORY LIST --------------------------------------------------\n";
			$data .= "if(!\$content_pref[\"content_menu_cat_dropdown\"]){\n";
			$data .= "	if(\$content_pref[\"content_menu_cat\"]){\n";
			$data .= "		\$text .= (\$content_pref[\"content_menu_cat_caption\"] != \"\" ? \$content_pref[\"content_menu_cat_caption\"] : CONTENT_MENU_LAN_3).\"<br />\";\n";
			$data .= "\n";
			$data .= "		\$newparent = \"\";\n";
			$data .= "		\$checkid = \"\";\n";
			$data .= "		\$newarray = array_merge_recursive(\$array);\n";
			$data .= "		for(\$a=0;\$a<count(\$newarray);\$a++){\n";
			$data .= "			for(\$b=0;\$b<count(\$newarray[\$a]);\$b++){\n";
			$data .= "				\$newparent[\$newarray[\$a][\$b]] = \$newarray[\$a][\$b+1];\n";
			$data .= "				if( (\$content_pref[\"content_menu_cat_main\"] && \$newarray[\$a][\$b] == \$menutypeid) || \$newarray[\$a][\$b] != \$menutypeid ){\n";
			$data .= "					\$checkid .= \" content_id = '\".\$newarray[\$a][\$b].\"' OR \";\n";
			$data .= "				}\n";
			$data .= "				\$b++;\n";
			$data .= "			}\n";
			$data .= "		}\n";
			$data .= "		\$checkid = substr(\$checkid,0,-3);\n";
			$data .= "		if(!is_object(\$sql)){ \$sql = new db; }\n";
			
			$data .= "		if(\$sql -> db_Select(\$plugintable, \"*\", \" \".\$checkid.\" ORDER BY SUBSTRING_INDEX(content_order, '.', 1)+0 \")){\n";
			$data .= "			while(\$row = \$sql -> db_Fetch()){\n";
			$data .= "\n";
			$data .= "				//define icon\n";
			$data .= "				\$ICON = \"\";\n";
			$data .= "				if(\$content_pref[\"content_menu_cat_icon\"] == \"0\"){ \$ICON = \"\";\n";
			$data .= "				}elseif(\$content_pref[\"content_menu_cat_icon\"] == \"1\"){ \$ICON = \$bullet;\n";
			$data .= "				}elseif(\$content_pref[\"content_menu_cat_icon\"] == \"2\"){ \$ICON = \"&middot\";\n";
			$data .= "				}elseif(\$content_pref[\"content_menu_cat_icon\"] == \"3\"){ \$ICON = \"&ordm;\";\n";
			$data .= "				}elseif(\$content_pref[\"content_menu_cat_icon\"] == \"4\"){ \$ICON = \"&raquo;\";\n";
			$data .= "				}elseif(\$content_pref[\"content_menu_cat_icon\"] == \"5\"){\n";
			$data .= "					if(\$row['content_icon'] != \"\" && file_exists(\$content_cat_icon_path_small.\$row['content_icon']) ){\n";
			$data .= "						\$ICON = \"<img src='\".\$content_cat_icon_path_small.\$row['content_icon'].\"' alt='' style='border:0;' />\";\n";
			$data .= "					}else{\n";
			$data .= "						//default category icon\n";
			$data .= "						if(\$content_pref[\"content_menu_cat_icon_default\"] == \"0\"){ \$ICON = \"\";\n";
			$data .= "						}elseif(\$content_pref[\"content_menu_cat_icon_default\"] == \"1\"){ \$ICON = \$bullet;\n";
			$data .= "						}elseif(\$content_pref[\"content_menu_cat_icon_default\"] == \"2\"){ \$ICON = \"&middot\";\n";
			$data .= "						}elseif(\$content_pref[\"content_menu_cat_icon_default\"] == \"3\"){ \$ICON = \"&ordm;\";\n";
			$data .= "						}elseif(\$content_pref[\"content_menu_cat_icon_default\"] == \"4\"){ \$ICON = \"&raquo;\";\n";
			$data .= "						}\n";
			$data .= "					}\n";
			$data .= "				}\n";
			$data .= "				//display category list\n";
			$data .= "				\$text .= \"<table style='width:100%; text-align:left; border:0;' cellpadding='0' cellspacing='0'>\";\n";
			$data .= "				\$text .= \"<tr>\";\n";
			$data .= "				\$text .= (\$ICON ? \"<td style='width:1%; white-space:nowrap; text-align:left; padding-right:5px;'><a href='\".e_PLUGIN.\"content/content.php?cat.\".\$row['content_id'].\"'>\".\$ICON.\"</a></td>\" : \"\");\n";
			$data .= "				\$text .= \"<td colspan='2'>\";\n";
			$data .= "				\$text .= \"<a href='\".e_PLUGIN.\"content/content.php?cat.\".\$row['content_id'].\"'>\".\$row['content_heading'].\"</a>\";\n";
			$data .= "				\$text .= (\$content_pref[\"content_menu_cat_number\"] ? \" <span class='smalltext'>(\".\$aa -> countCatItems(\$row['content_id']).\")</span>\" : \"\");\n";
			$data .= "				\$text .= \"</td>\";\n";
			$data .= "				\$text .= \"</tr>\";\n";
			$data .= "				\$text .= \"</table>\";\n";
			$data .= "			}\n";
			$data .= "		}\n";
			$data .= "	}\n";
			$data .= "}\n";
			$data .= "\n";
			$data .= "//##### RECENT --------------------------------------------------\n";
			$data .= "if(\$content_pref[\"content_menu_recent\"]){\n";
			$data .= "	\$text .= \"<br />\";\n";
			$data .= "\n";
			$data .= "	//prepare query paramaters\n";
			$data .= "	\$validparent = implode(\",\", array_keys(\$array));\n";
			$data .= "	\$qry = \" content_parent REGEXP '\".\$aa -> CONTENTREGEXP(\$validparent).\"' \";\n";
			$data .= "\n";
			$data .= "	\$sql1 = new db;\n";
			$data .= "	\$contenttotal = \$sql1 -> db_Count(\$plugintable, \"(*)\", \"WHERE content_refer != 'sa' AND \".\$qry.\" \".\$datequery.\" AND content_class REGEXP '\".e_CLASS_REGEXP.\"' \" );\n";
			$data .= "\n";
			$data .= "	if(\$resultitem = \$sql1 -> db_Select(\$plugintable, \"*\", \"content_refer !='sa' AND \".\$qry.\" \".\$datequery.\" AND content_class REGEXP '\".e_CLASS_REGEXP.\"' ORDER BY content_datestamp DESC LIMIT 0,\".\$content_pref[\"content_menu_recent_number\"] )){\n";
			$data .= "\n";
			$data .= "		\$text .= (\$content_pref[\"content_menu_recent_caption\"] != \"\" ? \$content_pref[\"content_menu_recent_caption\"] : CONTENT_MENU_LAN_2).\"<br />\";\n";
			$data .= "		while(\$row = \$sql1 -> db_Fetch()){\n";
			$data .= "\n";
			$data .= "			\$ICON = \"\";\n";
			$data .= "			\$DATE = \"\";\n";
			$data .= "			\$AUTHOR = \"\";\n";
			$data .= "			\$SUBHEADING = \"\";\n";
			$data .= "\n";
			$data .= "			if(\$content_pref[\"content_menu_recent_date\"]){\n";
			$data .= "				\$datestyle = (\$content_pref[\"content_archive_datestyle\"] ? \$content_pref[\"content_archive_datestyle\"] : \"%d %b %Y\");\n";
			$data .= "				\$DATE = strftime(\$datestyle, \$row['content_datestamp']);\n";
			$data .= "			}\n";
			$data .= "			if(\$content_pref[\"content_menu_recent_author\"]){\n";
			$data .= "				\$authordetails = \$aa -> getAuthor(\$row['content_author']);\n";
			$data .= "				\$AUTHOR = \$authordetails[1];\n";
			$data .= "			}\n";
			$data .= "\n";
			$data .= "			//subheading\n";
			$data .= "			if(\$content_pref[\"content_menu_recent_subheading\"] && \$row['content_subheading']){\n";
			$data .= "				if(\$content_pref[\"content_menu_recent_subheading_char\"] && \$content_pref[\"content_menu_recent_subheading_char\"] != \"\" && \$content_pref[\"content_menu_recent_subheading_char\"] != \"0\"){\n";
			$data .= "					if(strlen(\$row['content_subheading']) > \$content_pref[\"content_menu_recent_subheading_char\"]) {\n";
			$data .= "						\$row['content_subheading'] = substr(\$row['content_subheading'], 0, \$content_pref[\"content_menu_recent_subheading_char\"]).\$content_pref[\"content_menu_recent_subheading_post\"];\n";
			$data .= "					}\n";
			$data .= "				}\n";
			$data .= "				\$SUBHEADING = \$row['content_subheading'];\n";
			$data .= "			}\n";
			$data .= "\n";
			$data .= "			//define icon\n";
			$data .= "			\$recenticonwidth = \"\";\n";
			$data .= "			if(\$content_pref[\"content_menu_recent_icon\"] == \"0\"){ \$ICON = \"\";\n";
			$data .= "			}elseif(\$content_pref[\"content_menu_recent_icon\"] == \"1\"){ \$ICON = \$bullet;\n";
			$data .= "			}elseif(\$content_pref[\"content_menu_recent_icon\"] == \"2\"){ \$ICON = \"&middot\";\n";
			$data .= "			}elseif(\$content_pref[\"content_menu_recent_icon\"] == \"3\"){ \$ICON = \"&ordm;\";\n";
			$data .= "			}elseif(\$content_pref[\"content_menu_recent_icon\"] == \"4\"){ \$ICON = \"&raquo;\";\n";
			$data .= "			}elseif(\$content_pref[\"content_menu_recent_icon\"] == \"5\"){\n";
			$data .= "				if(\$content_pref[\"content_menu_recent_icon\"] == \"5\"){\n";
			$data .= "					if(\$content_pref[\"content_menu_recent_icon_width\"]){\n";
			$data .= "						\$recenticonwidth = \" width:\".\$content_pref[\"content_menu_recent_icon_width\"].\"px; \";\n";
			$data .= "					}else{\n";
			$data .= "						\$recenticonwidth = \" width:50px; \";\n";
			$data .= "					}\n";
			$data .= "				}\n";
			$data .= "				if(\$content_pref[\"content_menu_recent_icon\"] == \"5\" && \$row['content_icon'] != \"\" && file_exists(\$content_icon_path.\$row['content_icon'])){\n";
			$data .= "					\$ICON = \"<img src='\".\$content_icon_path.\$row['content_icon'].\"' alt='' style='\".\$recenticonwidth.\" border:0;' />\";\n";
			$data .= "				}\n";
			$data .= "			}\n";
			$data .= "\n";
			$data .= "			//display recent list\n";
			$data .= "			\$text .= \"<table style='width:100%; text-align:left; border:0; margin-bottom:10px;' cellpadding='0' cellspacing='0'>\";\n";
			$data .= "			\$text .= \"<tr>\";\n";
			$data .= "			\$text .= (\$ICON ? \"<td style='width:1%; white-space:nowrap; vertical-align:top; padding-right:5px;'><a href='\".e_PLUGIN.\"content/content.php?content.\".\$row['content_id'].\"'>\".\$ICON.\"</a></td>\" : \"\");\n";
			$data .= "			\$text .= \"<td style='width:99%; vertical-align:top;'>\";\n";
			$data .= "			\$text .= \"<a href='\".e_PLUGIN.\"content/content.php?content.\".\$row['content_id'].\"'>\".\$row['content_heading'].\"</a><br />\";\n";
			$data .= "			\$text .= (\$DATE ? \$DATE.\"<br />\" : \"\" );\n";
			$data .= "			\$text .= (\$AUTHOR ? \$AUTHOR.\"<br />\" : \"\" );\n";
			$data .= "			\$text .= (\$SUBHEADING ? \$SUBHEADING.\"<br />\" : \"\" );\n";
			$data .= "			\$text .= \"</td>\";\n";
			$data .= "			\$text .= \"</tr>\";\n";
			$data .= "			\$text .= \"</table>\";\n";
			$data .= "		}\n";
			$data .= "	}\n";
			$data .= "}\n";
			$data .= "\n";				
			$data .= "if(!isset(\$text)){ \$text = CONTENT_MENU_LAN_1; }\n";
			$data .= "\$caption = (\$content_pref[\"content_menu_caption\"] != \"\" ? \$content_pref[\"content_menu_caption\"] : CONTENT_MENU_LAN_0.\" \".\$menuname);\n";
			$data .= "\$ns -> tablerender(\$caption, \$text, '$menufile');\n";
			$data .= "\n";
			$data .= "?".chr(62);
				 
			if(file_exists($plugindir."menus/".$menufile."_menu.php")){
				$message = "";
			}else{
				$fp = @fopen($plugindir."menus/".$menufile."_menu.php", "w");
				if (!@fwrite($fp, $data)) {
					$message = CONTENT_ADMIN_CAT_LAN_51;
				} else {
					fclose($fp);
					$message = CONTENT_ADMIN_CAT_LAN_50;
				}
			}
			return $message;
		}
}	//close class

?>