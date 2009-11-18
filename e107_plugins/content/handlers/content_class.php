<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/content/handlers/content_class.php,v $
 * $Revision: 1.25 $
 * $Date: 2009-11-18 01:05:28 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }

global $plugindir, $plugintable, $datequery;
$plugindir = e_PLUGIN."content/";
$plugintable = "pcontent";
$datequery = " AND content_datestamp < ".time()." AND (content_enddate=0 || content_enddate>".time().") ";

require_once($plugindir."handlers/content_defines.php");

if(!is_object($sql)){ $sql = new db; }

class content{

	function ContentDefaultPrefs(){
		global $tp;

		//ADMIN CREATE FORM
		$cp['content_admin_subheading'] = '1';				//should subheading be available
		$cp['content_admin_summary'] = '1';					//should summary be available
		$cp['content_admin_startdate'] = '1';				//should start date be available
		$cp['content_admin_enddate'] = '1';					//should end date be available
		$cp['content_admin_icon'] = '';						//should icon be available to add when creating an item
		$cp['content_admin_attach'] = '';					//should file be available to add when creating an item
		$cp['content_admin_images'] = '';					//should image be available to add when creating an item
		$cp['content_admin_comment'] = '1';					//should comment be available to add when creating an item
		$cp['content_admin_rating'] = '1';					//should rating be available to add when creating an item
		$cp['content_admin_score'] = '1';					//should score be available to add when creating an item
		$cp['content_admin_pe'] = '1';						//should printemailicons be available to add when creating an item
		$cp['content_admin_visibility'] = '1';				//should visibility be available to add when creating an item
		$cp['content_admin_meta'] = '';						//should metatags be available to add when creating an item
		$cp['content_admin_custom_number'] = '';			//how many customtags should be available to add when creating an item
		$cp['content_admin_images_number'] = '2';			//how many images should be available to add when creating an item
		$cp['content_admin_files_number'] = '1';			//how many files should be available to add when creating an item
		$cp['content_admin_layout'] = '';					//should the option for choosing a layout template be shown
		$cp['content_admin_customtags'] = '';				//should options for adding additional data be shown
		$cp['content_admin_presettags'] = '';				//should preset data tags be shown
		$cp['content_admin_loadicons'] = 0;					//load all icons, or only the 'tmp' icons, when assigning an icon
		$cp['content_admin_loadattach'] = 0;				//load all attachments, or only the 'tmp' attachments, when assigning an attachment

		//ADMIN CREATE CATEGORY FORM
		$cp['content_admincat_subheading'] = '1';			//should subheading be available
		$cp['content_admincat_startdate'] = '1';			//should startdate be available
		$cp['content_admincat_enddate'] = '1';				//should enddate be available
		$cp['content_admincat_uploadicon'] = '1';			//should uploadicon be available
		$cp['content_admincat_selecticon'] = '1';			//should selecticon be available
		$cp['content_admincat_comment'] = '1';				//should comment be available
		$cp['content_admincat_rating'] = '1';				//should rating be available
		$cp['content_admincat_pe'] = '1';					//should print email icons be available
		$cp['content_admincat_visibility'] = '1';			//should visibility be available

		//PATH THEME CSS
		$cp['content_cat_icon_path_large'] = '{e_PLUGIN}content/images/cat/48/';	//default path to large categry icons
		$cp['content_cat_icon_path_small'] = '{e_PLUGIN}content/images/cat/16/';	//default path to small category icons

		$cp['content_icon_path'] = '{e_PLUGIN}content/images/icon/';				//default path to item icons
		$cp['content_icon_path_tmp'] = '{e_PLUGIN}content/images/icon/tmp/';		//default tmp path to item icons
		
		$cp['content_image_path'] = '{e_PLUGIN}content/images/image/';				//default path to item images
		$cp['content_image_path_tmp'] = '{e_PLUGIN}content/images/image/tmp/';		//default tmp path to item images
		
		$cp['content_file_path'] = '{e_PLUGIN}content/images/file/';				//default path to item file attachments
		$cp['content_file_path_tmp'] = '{e_PLUGIN}content/images/file/tmp/';		//default tmp path to item file attachments
		
		$cp['content_theme'] = '{e_PLUGIN}content/templates/default/';				//choose theme for main parent
		$cp['content_layout'] = 'content_content_template.php';						//choose default layout scheme

		//GENERAL
		$cp['content_log'] = '';							//activate log
		$cp['content_blank_icon'] = '';						//use blank icon if no icon present
		$cp['content_blank_caticon'] = '';					//use blank caticon if no caticon present
		$cp['content_breadcrumb_catall'] = '';				//show breadcrumb on all categories page
		$cp['content_breadcrumb_cat'] = '';					//show breadcrumb on single category page
		$cp['content_breadcrumb_authorall'] = '';			//show breadcrumb on all author page
		$cp['content_breadcrumb_author'] = '';				//show breadcrumb on single author page
		$cp['content_breadcrumb_recent'] = '';				//show breadcrumb on recent page
		$cp['content_breadcrumb_item'] = '';				//show breadcrumb on content item page
		$cp['content_breadcrumb_top'] = '';					//show breadcrumb on top rated page
		$cp['content_breadcrumb_archive'] = '';				//show breadcrumb on archive page
		$cp['content_breadcrumb_seperator'] = '>';			//seperator character between breadcrumb
		$cp['content_breadcrumb_rendertype'] = '2';			//how to render the breadcrumb
		$cp['content_navigator_catall'] = '';				//show navigator on all categories page
		$cp['content_navigator_cat'] = '';					//show navigator on single category page
		$cp['content_navigator_authorall'] = '';			//show navigator on all author page
		$cp['content_navigator_author'] = '';				//show navigator on single author page
		$cp['content_navigator_recent'] = '';				//show navigator on recent page
		$cp['content_navigator_item'] = '';					//show navigator on content item page
		$cp['content_navigator_top'] = '';					//show navigator on top rated page
		$cp['content_navigator_archive'] = '';				//show navigator on archive page
		$cp['content_search_catall'] = '';					//show search keyword on all categories page
		$cp['content_search_cat'] = '';						//show search keyword on single category page
		$cp['content_search_authorall'] = '';				//show search keyword on all author page
		$cp['content_search_author'] = '';					//show search keyword on single author page
		$cp['content_search_recent'] = '';					//show search keyword on recent page
		$cp['content_search_item'] = '';					//show search keyword on content item page
		$cp['content_search_top'] = '';						//show search keyword on top rated page
		$cp['content_search_archive'] = '';					//show search keyword on archive page
		$cp['content_ordering_catall'] = '';				//show ordering on all categories page
		$cp['content_ordering_cat'] = '';					//show ordering on single category page
		$cp['content_ordering_authorall'] = '';				//show ordering on all author page
		$cp['content_ordering_author'] = '';				//show ordering on single author page
		$cp['content_ordering_recent'] = '';				//show ordering on recent page
		$cp['content_ordering_item'] = '';					//show ordering on content item page
		$cp['content_ordering_top'] = '';					//show ordering on top rated page
		$cp['content_ordering_archive'] = '';				//show ordering on archive page
		$cp['content_searchmenu_rendertype'] = '1';			//rendertype for searchmenu (1=echo, 2=in separate menu)
		$cp['content_nextprev'] = '1';						//use nextprev buttons
		$cp['content_nextprev_number'] = '10';				//how many items on a page
		$cp['content_defaultorder'] = 'orderddate';			//default sort and order method
		//upload icon/image size handling
		$cp['content_upload_image_size'] = '500';			//resize size of uploaded image
		$cp['content_upload_image_size_thumb'] = '100';		//resize size of created thumb on uploaded image
		$cp['content_upload_icon_size'] = '100';			//resize size of uploaded icon

		//CONTENT ITEM PREVIEW
		$cp['content_list_icon'] = '1';						//show icon
		$cp['content_list_subheading'] = '1';				//show subheading
		$cp['content_list_summary'] = '1';					//show summary
		$cp['content_list_text'] = '';						//show (part of) text
		$cp['content_list_date'] = '';						//show date
		$cp['content_list_authorname'] = '';				//show authorname
		$cp['content_list_authorprofile'] = '';				//show link to author profile
		$cp['content_list_authoremail'] = '';				//show authoremail
		$cp['content_list_authoricon'] = '';				//show link to author list
		$cp['content_list_rating'] = '1';					//show rating system
		$cp['content_list_peicon'] = '1';					//show printemailicons
		$cp['content_list_parent'] = '';					//show parent cat
		$cp['content_list_refer'] = '';						//show refer count
		$cp['content_list_subheading_char'] = '100';		//how many subheading characters
		$cp['content_list_subheading_post'] = '[...]';		//use a postfix for too long subheadings
		$cp['content_list_summary_char'] = '100';			//how many summary characters
		$cp['content_list_summary_post'] = '[...]';			//use a postfix for too long summary
		$cp['content_list_text_char'] = '60';				//how many text words
		$cp['content_list_text_post'] = CONTENT_LAN_16;		//use a postfix for too long text
		$cp['content_list_text_link'] = '1';				//show link to content item on postfix
		$cp['content_list_authoremail_nonmember'] = '';		//show email non member author
		$cp['content_list_peicon_all'] = '';				//override printemail icons
		$cp['content_list_rating_all'] = '';				//override rating system
		$cp['content_list_editicon'] = '';					//show link to admin edit item
		$cp['content_list_datestyle'] = '%d %b %Y';			//choose datestyle for given date
		$cp['content_list_caption'] = CONTENT_LAN_23;		//caption for recent list
		$cp['content_list_caption_append_name'] = '1';		//append category heading to caption

		//CATEGORY PAGES
		//sections of content category in 'view all categories page'
		$cp['content_catall_icon'] = '1';					//show icon
		$cp['content_catall_subheading'] = '1';				//show subheading
		$cp['content_catall_text'] = '';					//show text
		$cp['content_catall_date'] = '';					//show date
		$cp['content_catall_rating'] = '1';					//show rating
		$cp['content_catall_authorname'] = '';				//show author name
		$cp['content_catall_authoremail'] = '';				//show author email
		$cp['content_catall_authorprofile'] = '';			//show link to author profile
		$cp['content_catall_authoricon'] = '';				//show link to author list
		$cp['content_catall_peicon'] = '1';					//show pe icons
		$cp['content_catall_comment'] = '1';				//show amount of comments
		$cp['content_catall_amount'] = '';					//show amount of items
		$cp['content_catall_text_char'] = '65';				//define amount of words of text to display
		$cp['content_catall_text_post'] = CONTENT_LAN_16;	//define postfix is text is too long
		$cp['content_catall_text_link'] = '1';				//define if link to category should be added on postfix
		$cp['content_catall_caption'] = CONTENT_LAN_25;		//caption for all categories page
		$cp['content_catall_defaultorder'] = 'orderaheading';	//default order for categories on the all categories page
		//sections of content category in 'view category' page
		$cp['content_cat_icon'] = '1';						//show icon
		$cp['content_cat_subheading'] = '1';				//show subheading
		$cp['content_cat_text'] = '';						//show text
		$cp['content_cat_date'] = '';						//show date
		$cp['content_cat_authorname'] = '';					//show author name
		$cp['content_cat_authoremail'] = '';				//show author email
		$cp['content_cat_authorprofile'] = '';				//show link to author profile
		$cp['content_cat_authoricon'] = '';					//show link to author list
		$cp['content_cat_rating'] = '1';					//show rating
		$cp['content_cat_peicon'] = '1';					//show pe icons
		$cp['content_cat_comment'] = '1';					//show amount of comments
		$cp['content_cat_amount'] = '1';					//show amount of items
		$cp['content_cat_caption'] = CONTENT_LAN_26;		//caption for single category page
		$cp['content_cat_caption_append_name'] = '1';		//append category heading to caption
		$cp['content_cat_sub_caption'] = CONTENT_LAN_28;	//caption for subcategories
		$cp['content_cat_item_caption'] = CONTENT_LAN_31;	//caption for items in category
		$cp['content_cat_defaultorder'] = 'orderaheading';	//default order for the subcategories on the single category page

		//sections of subcategories in 'view category page'
		$cp['content_catsub_icon'] = '1';					//show icon
		$cp['content_catsub_subheading'] = '1';				//show subheading
		$cp['content_catsub_amount'] = '1';					//show amount of items
		$cp['content_cat_showparent'] = '1';				//show parent item in category page
		$cp['content_cat_showparentsub'] = '1';				//show subcategories in category page
		$cp['content_cat_listtype'] = '';					//also show items from subategories
		$cp['content_cat_menuorder'] = '1';					//order of parent and child items
		$cp['content_cat_rendertype'] = '2';				//render method of the menus
		$cp['content_cat_text_char'] = '65';				//define amount of words of text to display
		$cp['content_cat_text_post'] = CONTENT_LAN_16;		//define postfix is text is too long
		$cp['content_cat_text_link'] = '1';					//define if link to category should be added on postfix
		$cp['content_cat_authoremail_nonmember'] = '';		//define if the email of a non-member will be displayed
		$cp['content_cat_peicon_all'] = '';					//override printemail icons
		$cp['content_cat_rating_all'] = '';					//override rating system

		//CONTENT PAGE
		$cp['content_content_icon'] = '';					//show icon
		$cp['content_content_subheading'] = '1';			//show subheading
		$cp['content_content_summary'] = '1';				//show summary
		$cp['content_content_date'] = '1';					//show date
		$cp['content_content_authorname'] = '1';			//show authorname
		$cp['content_content_authorprofile'] = '';			//show link to author profile
		$cp['content_content_authoremail'] = '';			//show suthoremail
		$cp['content_content_authoricon'] = '';				//show link to author list
		$cp['content_content_parent'] = '';					//show parent category
		$cp['content_content_rating'] = '1';				//show rating system
		$cp['content_content_peicon'] = '1';				//show printemailicons
		$cp['content_content_refer'] = '';					//show refer count
		$cp['content_content_comment'] = '1';				//show amount of comments
		$cp['content_content_authoremail_nonmember'] = '';	//show email non member
		$cp['content_content_peicon_all'] = '';				//override printemail icons
		$cp['content_content_rating_all'] = '';				//override rating system
		$cp['content_content_comment_all'] = '';			//override comment system				
		$cp['content_content_editicon'] = '';				//show link in content page to admin edit item
		$cp['content_content_customtags'] = '';				//should additional data be shown
		$cp['content_content_presettags'] = '';				//should preset data tags be shown
		$cp['content_content_attach'] = '';					//show attachments
		$cp['content_content_images'] = '';					//show images
		$cp['content_content_pagenames_rendertype'] = '';	//rendertype for articleindex on multipage content items
		$cp['content_content_multipage_preset'] = '';		//render custom/preset in multipage item first/last page
		$cp['content_content_pagenames_nextprev_prevhead'] = '< {PAGETITLE}';		//link to next page in multipage item
		$cp['content_content_pagenames_nextprev_nexthead'] = '{PAGETITLE} >';		//link to prev page in multipage item

		//AUTHOR PAGE
		$cp['content_author_lastitem'] = '';						//show last item reference
		$cp['content_author_amount'] = '1';							//show amount of items from this author
		$cp['content_author_nextprev'] = '1';						//use next prev buttons
		$cp['content_author_nextprev_number'] = '20';				//amount of items per page
		$cp['content_author_index_caption'] = CONTENT_LAN_32;		//caption for author index page
		$cp['content_author_caption'] = CONTENT_LAN_32;				//caption for single author page
		$cp['content_author_caption_append_name'] = '1';			//append author name to caption

		//ARCHIVE PAGE
		$cp['content_archive_nextprev'] = '1';						//archive : choose to show next/prev links
		$cp['content_archive_nextprev_number'] = '30';				//archive : choose amount to use in next/prev
		$cp['content_archive_letterindex'] = '1';					//archive : letter index
		$cp['content_archive_datestyle'] = '%d %b %Y';				//archive : choose datestyle for given date
		$cp['content_archive_date'] = '1';							//archive : section: show date
		$cp['content_archive_authorname'] = '';						//archive : section: show authorname
		$cp['content_archive_authorprofile'] = '';					//archive : section: show link to author profile
		$cp['content_archive_authoricon'] = '';						//archive : section: show link to author list
		$cp['content_archive_authoremail'] = '';					//archive : section: show author email
		$cp['content_archive_authoremail_nonmember'] = '';			//archive : show link to email of non-member author
		$cp['content_archive_caption'] = CONTENT_LAN_84;			//caption for archive page
		$cp['content_archive_defaultorder'] = 'orderaheading';		//default order for content items on the archive page

		//TOP RATED PAGE
		$cp['content_top_icon'] = '';								//top : section: show icon
		$cp['content_top_authorname'] = '';							//top : section: show authorname
		$cp['content_top_authorprofile'] = '';						//top : section: show link to author profile
		$cp['content_top_authoricon'] = '';							//top : section: show link to author list
		$cp['content_top_authoremail'] = '';						//top : section: show author email
		$cp['content_top_authoremail_nonmember'] = '';				//top : show link to email of non-member author
		$cp['content_top_icon_width'] = '';							//use this size for icon
		$cp['content_top_caption'] = CONTENT_LAN_38;				//caption for top rated page
		$cp['content_top_caption_append_name'] = '1';				//append category heading to caption

		//TOP SCORE PAGE
		$cp['content_score_icon'] = '';								//score : section: show icon
		$cp['content_score_authorname'] = '';						//score : section: show authorname
		$cp['content_score_authorprofile'] = '';					//score : section: show link to author profile
		$cp['content_score_authoricon'] = '';						//score : section: show link to author list
		$cp['content_score_authoremail'] = '';						//score : section: show author email
		$cp['content_score_authoremail_nonmember'] = '';			//score : show link to email of non-member author
		$cp['content_score_icon_width'] = '';						//use this size for icon
		$cp['content_score_caption'] = CONTENT_LAN_87;				//caption for top score page
		$cp['content_score_caption_append_name'] = '1';				//append category heading to caption

		//MENU OPTIONS
		$cp['content_menu_caption'] = CONTENT_MENU_LAN_0;			//caption of menu
		$cp['content_menu_search'] = '';							//show search keyword
		$cp['content_menu_sort'] = '';								//show sorting methods
		$cp['content_menu_visibilitycheck'] = '';					//show menu only on content pages of this top level category?
		$cp['content_menu_links'] = '1';							//show content links
		$cp['content_menu_links_dropdown'] = '';					//rendertype of content links (in dropdown or as normal links)
		$cp['content_menu_links_icon'] = '';						//define icon for content links (only with normallinks)
		$cp['content_menu_links_caption'] = CONTENT_MENU_LAN_4;		//define caption for link list (only is normallinks is selected)
		$cp['content_menu_viewallcat'] = '1';						//menu: view link to all categories
		$cp['content_menu_viewallauthor'] = '1';					//menu: view link to all authors
		$cp['content_menu_viewallitems'] = '1';						//menu: view link to all items (archive)
		$cp['content_menu_viewtoprated'] = '';						//menu: view link to top rated items
		$cp['content_menu_viewtopscore'] = '';						//menu: view link to top score items
		$cp['content_menu_viewrecent'] = '1';						//menu: view link to recent items
		$cp['content_menu_viewsubmit'] = '1';						//view link to submit content item (only if it is allowed)
		$cp['content_menu_viewicon'] = '';							//choose icon to display for links
		$cp['content_menu_cat'] = '1';								//view categories
		$cp['content_menu_cat_main'] = '1';							//show main parent in the category list				
		$cp['content_menu_cat_number'] = '1';						//show number of items in category				
		$cp['content_menu_cat_icon'] = '';							//choose icon to display for categories
		$cp['content_menu_cat_icon_default'] = '';					//choose default icon is no icon present (only if category_icon is selected)
		$cp['content_menu_cat_caption'] = CONTENT_MENU_LAN_3;		//define caption for category list
		$cp['content_menu_cat_dropdown'] = '';						//rendertype of categories (in dropdown or as normal links)
		$cp['content_menu_recent'] = '1';							//view recent list
		$cp['content_menu_recent_caption'] = CONTENT_MENU_LAN_2;	//caption of recent list
		$cp['content_menu_recent_number'] = '5';					//number of recent items to show
		$cp['content_menu_recent_date'] = '';						//show date in recent list
		$cp['content_menu_recent_datestyle'] = '%d %b %Y';			//choose datestyle for given date
		$cp['content_menu_recent_author'] = '';						//show author in recent list
		$cp['content_menu_recent_subheading'] = '';					//show subheading in recent list
		$cp['content_menu_recent_subheading_char'] = '80';			//number of characters of subheading to show
		$cp['content_menu_recent_subheading_post'] = '[...]';		//postfix for too long subheadings
		$cp['content_menu_recent_icon'] = '';						//choose icon to display for recent items
		$cp['content_menu_recent_icon_width'] = '50';				//specify width of icon (only if content_icon is set)

		$cp['content_inherit'] = '';								//inherit options from default preferences

		//CONTENT MANAGER
		$cp['content_manager_submit'] = '255';						//class for managers who can submit items in a category
		$cp['content_manager_approve'] = '255';						//class for managers who can approve submitted items
		$cp['content_manager_personal'] = '255';					//class for managers who can manage personal items
		$cp['content_manager_category'] = '255';					//class for managers who can manage all items in a category
		$cp['content_manager_inherit'] = '';						//inherit options from default (manager) preferences

		//manager : submit options
		$cp['content_manager_submit_directpost'] ='';				//should submission be direclty posted as an item, or have them validated by admins
		$cp['content_manager_submit_subheading'] = '';				//should subheading be available
		$cp['content_manager_submit_summary'] = '';					//should summary be available
		$cp['content_manager_submit_startdate'] = '';				//should startdate be available
		$cp['content_manager_submit_enddate'] = '';					//should enddate be available
		$cp['content_manager_submit_icon'] = '';					//should icon be available to add when submitting an item
		$cp['content_manager_submit_attach'] = '';					//should file be available to add when submitting an item
		$cp['content_manager_submit_images'] = '';					//should image be available to add when submitting an item
		$cp['content_manager_submit_comment'] = '';					//should comment be available to add when submitting an item
		$cp['content_manager_submit_rating'] = '';					//should rating be available to add when submitting an item
		$cp['content_manager_submit_score'] = '';					//should score be available to add when submitting an item
		$cp['content_manager_submit_pe'] = '';						//should printemailicons be available to add when submitting an item
		$cp['content_manager_submit_visibility'] = '';				//should visibility be available to add when submitting an item
		$cp['content_manager_submit_meta'] = '';					//should metatags be available to add when submitting an item
		$cp['content_manager_submit_layout'] = '';					//should the option for choosing a layout template be shown
		$cp['content_manager_submit_customtags'] = '';				//should options for adding additional data be shown
		$cp['content_manager_submit_presettags'] = '';				//should preset data tags be shown
		$cp['content_manager_submit_custom_number'] = '';			//how many customtags should be available to add when submitting an item
		$cp['content_manager_submit_images_number'] = '';			//how many images should be available to add when submitting an item
		$cp['content_manager_submit_files_number'] = '';			//how many files should be available to add when submitting an item

		//manager : manager options
		$cp['content_manager_manager_subheading'] = '';
		$cp['content_manager_manager_summary'] = '';
		$cp['content_manager_manager_startdate'] = '';
		$cp['content_manager_manager_enddate'] = '';
		$cp['content_manager_manager_icon'] = '';
		$cp['content_manager_manager_attach'] = '';
		$cp['content_manager_manager_images'] = '';
		$cp['content_manager_manager_comment'] = '';
		$cp['content_manager_manager_rating'] = '';
		$cp['content_manager_manager_score'] = '';
		$cp['content_manager_manager_pe'] = '';
		$cp['content_manager_manager_visibility'] = '';
		$cp['content_manager_manager_meta'] = '';
		$cp['content_manager_manager_layout'] = '';
		$cp['content_manager_manager_customtags'] = '';
		$cp['content_manager_manager_presettags'] = '';
		$cp['content_manager_manager_custom_number'] = '';
		$cp['content_manager_manager_images_number'] = '2';
		$cp['content_manager_manager_files_number'] = '1';

		foreach($cp as $k => $v){
			if( !empty($v) ){
				$content_pref[$k] = $tp->toDB($v);
			}
		}
		return $content_pref;
	}

	function getContentPref($id="", $parsepaths=false) {
		global $sql, $plugintable, $qs, $tp, $eArrayStorage;

		$plugintable = "pcontent";

		//if $id; use prefs from content table
		if($id && $id!="0"){
			$id = intval($id);
			$num_rows = $sql -> db_Select($plugintable, "content_pref", "content_id='$id' ");
			$row = $sql -> db_Fetch();
			if (empty($row['content_pref'])) {
				//if no prefs present yet, get them from core (default preferences)
				$num_rows = $sql -> db_Select("core", "e107_value", "e107_name='$plugintable' ");
				//if those are not present, insert the default ones given in this file
				if ($num_rows == 0) {
					$content_pref = $this -> ContentDefaultPrefs();
					$tmp = $eArrayStorage->WriteArray($content_pref);
					$sql -> db_Insert("core", "'$plugintable', '{$tmp}' ");
					$sql -> db_Select("core", "e107_value", "e107_name='$plugintable' ");
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

			if(e_PAGE == "admin_content_config.php" && isset($qs[0]) && ($qs[0] == 'option' || $qs[0] == 'manager') ){
			}else{
				//check inheritance, if set, get core prefs (default prefs)
				if(isset($content_pref['content_inherit']) && $content_pref['content_inherit']!=''){
					$sql -> db_Select("core", "e107_value", "e107_name='$plugintable' ");
					$row = $sql -> db_Fetch();
					$content_pref = $eArrayStorage->ReadArray($row['e107_value']);
				}
			}

		//if not $id; use prefs from default core table
		}else{
			$num_rows = $sql -> db_Select("core", "e107_value", "e107_name='$plugintable' ");
			if ($num_rows == 0) {
				$content_pref = $this -> ContentDefaultPrefs();
				$tmp = $eArrayStorage->WriteArray($content_pref);
				$sql -> db_Insert("core", "'$plugintable', '{$tmp}' ");
				$sql -> db_Select("core", "e107_value", "e107_name='$plugintable' ");
			}
			$row = $sql -> db_Fetch();
			$content_pref = $eArrayStorage->ReadArray($row['e107_value']);
		}

		if($parsepaths){
			$content_pref = $this->parseConstants($content_pref);
		}

		return $content_pref;
	}

	function parseConstants($content_pref){
			global $tp;

			//sanitize the paths (first check if exists, else create default paths
			$content_pref['content_cat_icon_path_large'] = varset($content_pref['content_cat_icon_path_large'], "{e_PLUGIN}content/images/cat/48/");
			$content_pref['content_cat_icon_path_small'] = varset($content_pref['content_cat_icon_path_small'], "{e_PLUGIN}content/images/cat/16/");
			$content_pref['content_icon_path'] = varset($content_pref['content_icon_path'], "{e_PLUGIN}content/images/icon/");
			$content_pref['content_icon_path_tmp'] = varset($content_pref['content_icon_path_tmp'], $content_pref['content_icon_path']."tmp/");
			$content_pref['content_image_path'] = varset($content_pref['content_image_path'], "{e_PLUGIN}content/images/image/");
			$content_pref['content_image_path_tmp'] = varset($content_pref['content_image_path_tmp'], $content_pref['content_image_path']."tmp/");
			$content_pref['content_file_path'] = varset($content_pref['content_file_path'], "{e_PLUGIN}content/images/file/");
			$content_pref['content_file_path_tmp'] = varset($content_pref['content_file_path_tmp'], $content_pref['content_file_path']."tmp/");

			//parse constants from the paths
			$content_pref['content_cat_icon_path_large'] = $tp -> replaceConstants($content_pref['content_cat_icon_path_large']);
			$content_pref['content_cat_icon_path_small'] = $tp -> replaceConstants($content_pref['content_cat_icon_path_small']);
			$content_pref['content_icon_path'] = $tp -> replaceConstants($content_pref['content_icon_path']);
			$content_pref['content_image_path'] = $tp -> replaceConstants($content_pref['content_image_path']);
			$content_pref['content_file_path'] = $tp -> replaceConstants($content_pref['content_file_path']);
			$content_pref['content_icon_path_tmp'] = $tp -> replaceConstants($content_pref['content_icon_path_tmp']);
			$content_pref['content_file_path_tmp'] = $tp -> replaceConstants($content_pref['content_file_path_tmp']);
			$content_pref['content_image_path_tmp'] = $tp -> replaceConstants($content_pref['content_image_path_tmp']);
			return $content_pref;
	}

	//admin
	function UpdateContentPref($id){
		global $qs, $plugintable, $sql, $tp, $eArrayStorage;

		if(!is_object($sql)){ $sql = new db; }

		//insert default preferences into core
		if($id == "0"){
			$num_rows = $sql -> db_Select("core", "e107_value", "e107_name='$plugintable' ");
			if ($num_rows == 0) {
				$content_pref = $this -> ContentDefaultPrefs();
				$tmp = $eArrayStorage->WriteArray($content_pref);
				$sql -> db_Insert("core", "'$plugintable', '{$tmp}' ");
				$sql -> db_Select("core", "e107_value", "e107_name='$plugintable' ");
			}
			$row = $sql -> db_Fetch();
			$current = $eArrayStorage->ReadArray($row['e107_value']);

			//if we are updating options
			if(isset($qs[0]) && $qs[0] == 'option' ){
				//only retain the manager prefs from the existing set from getting overwritten
				foreach($current as $k => $v){
					if( strpos($k, "content_manager_") === 0 ){
						$content_pref[$k] = $tp->toDB($v);
					}
				}

				//prepare custom tags: use the posted values
				$cp = $_POST['content_custom_preset_key'];

			//if we are updating manager
			}elseif(isset($qs[0]) && $qs[0] == 'manager'){
				//if this is a top level category we need to retain all existing options
				if($currentparent=='0'){
					$content_pref = $current;
				}

				//prepare custom tags: use the existing content_pref values
				$cp = $content_pref['content_custom_preset_key'];
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
					if( strpos($k, "content_manager_") === 0 ){
						$content_pref[$k] = $tp->toDB($v);
					}
				}

				//prepare custom tags: use the posted values
				$cp = $_POST['content_custom_preset_key'];

			//if we are updating manager
			}elseif(isset($qs[0]) && $qs[0] == 'manager'){
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

	//function to check and require the template file
	//$var: array of all variables that need to be global'ed
	//$var[0]: holds the primary var to check isset on (if the template var exists ($MYTEMPLATE))
	//$file: the actual filename (template.php)
	function gettemplate($var, $file=''){
		global $content_pref, $tp;

		if(is_array($var)){
			$check = $$var[0];
			foreach($var as $t){
				global $$t;
			}
		}else{
			$check = $var;
			global $var;
		}

		if(!isset($check)){
			if(!$content_pref["content_theme"]){
				require_once(e_PLUGIN."content/templates/default/".$file);
			}else{
				if( is_readable($tp->replaceConstants($content_pref["content_theme"]).$file) ){
					require_once($tp->replaceConstants($content_pref["content_theme"]).$file);
				}else{
					require_once(e_PLUGIN."content/templates/default/".$file);
				}
			}
		}
	}

	function getCategoryTree($id, $parent, $classcheck=TRUE, $cache=true){
		//id	:	content_parent of an item
		global $plugintable, $datequery, $agc;

		if($cache){
			$cachestring = md5("cm_gct_".$id."_".$parent."_".$classcheck);
			if($ret = getcachedvars("content_getcategorytree_{$cachestring}"))
			{
				return $ret;
			}
		}

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

		$datequery = " AND content_datestamp < ".time()." AND (content_enddate=0 || content_enddate>".time().") ";

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
				$this -> getCategoryTree($row['content_id'], "", $classcheck, false);
			}
		}

		if($cache){
			cachevars("content_getcategorytree_{$cachestring}", $agc);
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

	function ShowNextPrev($mode='', $from='0', $number, $total, $return=false){
		global $content_pref, $qs, $tp, $plugindir, $content_shortcodes, $CONTENT_NEXTPREV;

		if($total<=$number){
			return;
		}

		$modepref = ($mode ? "content_{$mode}_nextprev" : "content_nextprev");
		if( varsettrue($content_pref[$modepref]) ){
			$np_querystring = e_SELF."?[FROM]".(isset($qs[0]) ? ".".$qs[0] : "").(isset($qs[1]) ? ".".$qs[1] : "").(isset($qs[2]) ? ".".$qs[2] : "").(isset($qs[3]) ? ".".$qs[3] : "").(isset($qs[4]) ? ".".$qs[4] : "");
			$parms = $total.",".$number.",".$from.",".$np_querystring."";
			
			$CONTENT_NEXTPREV = $tp->parseTemplate("{NEXTPREV={$parms}}");

			if($return){
				return $CONTENT_NEXTPREV;
			}else{
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
	}

	function getCrumbPage($mode, $arr, $parent){
		global $qs, $ns, $content_pref, $plugintable;

		if( varsettrue($content_pref["content_breadcrumb_{$mode}"]) ){
			$crumb = '';
			if(array_key_exists($parent, $arr)){
				$sep = varsettrue($content_pref["content_breadcrumb_seperator"], ">");
				if( varsettrue($content_pref["content_breadcrumb_base"]) ){
					$crumb .= "<a href='".e_BASE."'>".CONTENT_LAN_58."</a> ".$sep." ";
				}
				if( varsettrue($content_pref["content_breadcrumb_self"]) ){
					$crumb .= "<a href='".e_SELF."'>".CONTENT_LAN_59."</a> ".$sep." ";
				}
				for($i=0;$i<count($arr[$parent]);$i++){
					if($i == count($arr[$parent])-2){
						$crumb .= "<a href='".e_SELF."?cat.".$arr[$parent][$i]."'>".$arr[$parent][$i+1]."</a>";
						break;
					}else{
						$crumb .= "<a href='".e_SELF."?cat.".$arr[$parent][$i]."'>".$arr[$parent][$i+1]."</a> ".$sep." ";
						$i++;
					}
				}
			}
			if($qs[0] == "recent"){
				$crumb .= " ".$sep." <a href='".e_SELF."?recent.".$arr[$parent][0]."'>".CONTENT_LAN_60."</a>";
			}
			if($qs[0] == "author"){
				$crumb .= " ".$sep." <a href='".e_SELF."?author.list.".$arr[$parent][0]."'>".CONTENT_LAN_85."</a>";
				if(is_numeric($qs[1])){
					global $sql;
					$sql->db_Select($plugintable, "content_author","content_id='".intval($qs[1])."'");
					$row=$sql->db_Fetch();
					$au = $this->getAuthor($row['content_author']);
					$crumb .= " ".$sep." <a href='".e_SELF."?author.".$qs[1]."'>".$au[1]."</a>";
				}
			}
			if($qs[0] == "list"){
				$crumb .= " ".$sep." <a href='".e_SELF."?list.".$arr[$parent][0]."'>".CONTENT_LAN_13."</a>";
			}
			if($qs[0] == "top"){
				$crumb .= " ".$sep." <a href='".e_SELF."?top.".$arr[$parent][0]."'>".CONTENT_LAN_8."</a>";
			}
			if($qs[0] == "score"){
				$crumb .= " ".$sep." <a href='".e_SELF."?score.".$arr[$parent][0]."'>".CONTENT_LAN_12."</a>";
			}
			if($qs[0] == "content"){
				global $row;
				$crumb .= " ".$sep." ".$row['content_heading'];
			}

			$crumb = "<div class='breadcrumb'>".trim($crumb)."</div>";
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

		$cachestring = md5($id."_".$datequery."_".e_CLASS_REGEXP);
		if($ret = getcachedvars("content_countcatitems_{$cachestring}"))
		{
			return $ret;
		}

		if(!is_object($sqlcountitemsincat)){ $sqlcountitemsincat = new db; }
		$n = $sqlcountitemsincat -> db_Count($plugintable, "(*)", "WHERE content_class REGEXP '".e_CLASS_REGEXP."' AND content_parent='".intval($id)."' AND content_refer != 'sa' ".$datequery." ");

		cachevars("content_countcatitems_{$cachestring}", $n);

		return $n;
	}

	function getCategoryHeading($id){
		global $plugintable, $sql;
		$qry = "
		SELECT p.content_heading
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

		}elseif(e_PAGE == "content_manager.php"){
			//manager page : view categories
			if(!e_QUERY){
				$page = CONTENT_PAGETITLE_LAN_0." / ".CONTENT_PAGETITLE_LAN_9;
			}else{
				$page = CONTENT_PAGETITLE_LAN_0." / ".CONTENT_PAGETITLE_LAN_9;

				//manager page : list items
				if($qs[0] == "content" && is_numeric($qs[1]) ){
					$page .= " / ".CONTENT_PAGETITLE_LAN_10;

				//manager page : edit item
				}elseif($qs[0] == "content" && $qs[1] == "edit" && is_numeric($qs[2]) ){
					$page .= " / ".CONTENT_PAGETITLE_LAN_11;

				//manager page : create new item (manager)
				}elseif($qs[0] == "content" && $qs[1] == "create" && is_numeric($qs[2]) ){
					$page .= " / ".CONTENT_PAGETITLE_LAN_12;

				//manager page : create new item (submit)
				}elseif($qs[0] == "content" && $qs[1] == "submit" && is_numeric($qs[2]) ){
					$page .= " / ".CONTENT_PAGETITLE_LAN_8;

				//manager page : approve submitted items (list items)
				}elseif($qs[0] == "content" && $qs[1] == "approve" && is_numeric($qs[2]) ){
					$page .= " / ".CONTENT_PAGETITLE_LAN_16;

				//manager page : post submitted item (edit item)
				}elseif($qs[0] == "content" && $qs[1] == "sa" && is_numeric($qs[2]) ){
					$page .= " / ".CONTENT_PAGETITLE_LAN_17;
				}
			}
		}
		define("e_PAGETITLE", $page);
	}

	function getAuthor($content_author) {
		global $sql, $plugintable, $datequery;

		$cachestring = md5($content_author);
		if($ret = getcachedvars("content_getauthor_{$cachestring}"))
		{
			return $ret;
		}

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
		cachevars("content_getauthor_{$cachestring}", $getauthor);
		return $getauthor;
	}

	function getMainParent($id, $cache=true){
		global $sql, $plugintable;

		if($cache){
			$cachestring = md5("cm_gmp_".$id);
			if($ret = getcachedvars("content_getmainparent_{$cachestring}"))
			{
				return $ret;
			}
		}

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
			$mainparent = $this -> getMainParent( $newid, false );
		}
		$val = ($mainparent ? $mainparent : "0");

		if($cache){
			cachevars("content_getmainparent_{$cachestring}", $val);
		}

		return $val;
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

	function getOrder($mode=''){
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
			if(isset($mode) && $mode=='catall'){
				$orderstring = ($content_pref["content_catall_defaultorder"] ? $content_pref["content_catall_defaultorder"] : "orderaheading" );
			}elseif(isset($mode) && $mode=='cat'){
				$orderstring = ($content_pref['content_cat_defaultorder'] ? $content_pref['content_cat_defaultorder'] : "orderaheading" );
			}elseif(isset($mode) && $mode=='archive'){
				$orderstring = ($content_pref['content_archive_defaultorder'] ? $content_pref['content_archive_defaultorder'] : "orderaheading" );
			}else{
				$orderstring = ($content_pref["content_defaultorder"] ? $content_pref["content_defaultorder"] : "orderddate" );
			}
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
			if(isset($mode) && $mode=='catall'){
				$orderby	= "content_order+0";
			}elseif(isset($mode) && $mode=='cat'){
				$orderby	= "content_order+0";
			}else{
				if($qs[0] == "cat"){
					$orderby	= "SUBSTRING_INDEX(content_order, '.', 1)+0";
				}elseif($qs[0] != "cat"){
					$orderby	= "SUBSTRING_INDEX(content_order, '.', -1)+0";
				}
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
		global $content_pref;

		$blank			= (!$blank ? "0" : $blank);
		$border			= "border:0;";
		$hrefpre		= ($linkid ? "<a href='".e_SELF."?".$linkid."'>" : "");
		$hrefpost		= ($linkid ? "</a>" : "");

		if($mode == "item"){
			$path		= (!$path ? $content_pref['content_icon_path'] : $path);
			$width		= ($width ? "width:".$width."px;" : "");
			//$border		= "border:1px solid #000;";
			$border		= '';
			$icon		= ($icon ? $path.$icon : ($blank ? $content_pref['content_icon_path']."blank.gif" : ""));

		}elseif($mode == "catsmall"){
			$path		= (!$path ? $content_pref['content_cat_icon_path_small'] : $path);
			$icon		= ($icon ? $path.$icon : "");

		}elseif($mode == "catlarge"){
			$path		= (!$path ? $content_pref['content_cat_icon_path_large'] : $path);
			$icon		= ($icon ? $path.$icon : "");
		}else{
			$path		= (!$path ? $content_pref['content_icon_path'] : $path);
			$hrefpre	= "";
			$hrefpost	= "";
			$width		= "";
			$icon		= ($icon ? $path.$icon : ($blank ? $content_pref['content_icon_path']."blank.gif" : ""));
		}

		if($icon && is_readable($icon)){
			$iconstring	= $hrefpre."<img src='".$icon."' alt='' style='".$width." ".$border."' />".$hrefpost;
		}else{
			$iconstring = "";
			if($blank){
				if(is_readable($content_pref['content_icon_path']."blank.gif")){
					if($mode == "catsmall"){
						$width = ($width ? "width:".$width."px;" : "width:16px;");
					}elseif($mode == "catlarge"){
						$width = ($width ? "width:".$width."px;" : "width:48px;");
					}
					$iconstring = $hrefpre."<img src='".$content_pref['content_icon_path']."blank.gif' alt='' style='".$width." ".$border."' />".$hrefpost;
				}
			}
		}
		return $iconstring;
	}

	function prepareAuthor($mode, $author, $id){
		global $aa, $content_pref;
		if($mode == ''){return;}

		$authorinfo = "";
		if( varsettrue($content_pref["content_{$mode}_authorname"]) || varsettrue($content_pref["content_{$mode}_authoremail"]) || varsettrue($content_pref["content_{$mode}_authoricon"]) || varsettrue($content_pref["content_{$mode}_authorprofile"]) ){
			$authordetails = $this -> getAuthor($author);
			if( varsettrue($content_pref["content_{$mode}_authorname"]) ){
				if(isset($content_pref["content_{$mode}_authoremail"]) && $authordetails[2]){
					if($authordetails[0] == "0"){
						if( varsettrue($content_pref["content_{$mode}_authoremail_nonmember"]) && strpos($authordetails[2], "@") ){
							$authorinfo = preg_replace("#([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"\\1\"+\"@\"+\"\\2\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"\\1\"+\"@\"+\"\\2\"; return true;' onmouseout='window.status=\"\";return true;'>".$authordetails[1]."</a>", $authordetails[2]);
						}else{
							$authorinfo = $authordetails[1];
						}
					}else{
						$authorinfo = preg_replace("#([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "<a rel='external' href='javascript:window.location=\"mai\"+\"lto:\"+\"\\1\"+\"@\"+\"\\2\";self.close();' onmouseover='window.status=\"mai\"+\"lto:\"+\"\\1\"+\"@\"+\"\\2\"; return true;' onmouseout='window.status=\"\";return true;'>".$authordetails[1]."</a>", $authordetails[2]);
					}
				}else{
					$authorinfo = $authordetails[1];
				}
				if(USER && is_numeric($authordetails[0]) && $authordetails[0] != "0" && varsettrue($content_pref["content_{$mode}_authorprofile"]) ){
					$authorinfo .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
				}
			}
			if( varsettrue($content_pref["content_{$mode}_authoricon"]) ){
				$authorinfo .= " <a href='".e_SELF."?author.".$id."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
			}
		}
		return $authorinfo;
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
		$content_pref	= $this -> getContentPref($mainparent, true);
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
				$CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_56, 1, "none");

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
				if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_viewsubmit"])){
					$CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option(CONTENT_LAN_75, 0, $plugindir."content_manager.php");
				}
			}
			if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_cat"] && $content_pref["content_menu_cat_dropdown"])){
				if($mode == "page" || ($mode == "menu" && $content_pref["content_menu_links"] && $content_pref["content_menu_links_dropdown"]) ){
					$CONTENT_SEARCH_TABLE_SELECT .= $rs -> form_option("&nbsp;", "0", "none");
				}
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

	//function to create the php menu file
	function CreateParentMenu($parentid){
		global $plugintable, $plugindir, $tp, $datequery;

		if(!is_object($sqlcreatemenu)){ $sqlcreatemenu = new db; }
		if(!$sqlcreatemenu -> db_Select($plugintable, "content_heading", "content_id='".intval($parentid)."'  ")){
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

		$data = chr(60)."?php\n". chr(47)."*\n+---------------------------------------------------------------+\n|        e107 website system\n|        ".e_PLUGIN."content/menus/".$menufile."_menu.php\n|\n|        ©Copyright (C) 2008-2009 e107 Inc (e107.org)\n|        http://e107.org\n|        jalist@e107.org\n|\n|        Released under the terms and conditions of the\n|        GNU General Public License (http://gnu.org).\n+---------------------------------------------------------------+\n\nThis file has been generated by ".e_PLUGIN."content/handlers/content_class.php.\n\n*". chr(47)."\n";
		$data .= "\n";
		$data .= "unset(\$text);\n";
		$data .= "\$text = \"\";\n";
		$data .= "\$menutypeid = \"$parentid\";\n";
		$data .= "\$menuname = \"$menuname\";\n";
		$data .= "\n";
		$data .= "\$plugindir = e_PLUGIN.'content/';\n";
		$data .= "\$plugintable = \"pcontent\";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)\n";
		$data .= "\$datequery = \" AND content_datestamp < \".time().\" AND (content_enddate=0 || content_enddate>\".time().\") \";\n";
		$data .= "\n";
		$data .= "global \$tp;\n";
		$data .= "require_once(e_PLUGIN.'content/content_shortcodes.php');\n";
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
						$bullet = \'<img src="\'.THEME.\'images/\'.BULLET.\'" alt="" class="icon" />\';
					}
					elseif(file_exists(THEME.\'images/bullet2.gif\'))
					{
						$bullet = \'<img src="\'.THEME.\'images/bullet2.gif" alt="" class="icon" />\';
					}
			';

		$data .= "\$content_pref = \$aa -> getContentPref(\$menutypeid, true);\n";
		$data .= "\n";
		$data .= "// load the template --------------------------------------------------\n";
		$data .= "if(!isset(\$CONTENT_MENU)){\n";
		$data .= "	if(!\$content_pref[\"content_theme\"]){\n";
		$data .= "		require_once(\$plugindir.\"templates/default/content_menu_template.php\");\n";
		$data .= "	}else{\n";
		$data .= "		if(is_readable(\$tp->replaceConstants(\$content_pref[\"content_theme\"]).\"content_menu_template.php\")){\n";
		$data .= "			require_once(\$tp->replaceConstants(\$content_pref[\"content_theme\"]).\"content_menu_template.php\");\n";
		$data .= "		}else{\n";
		$data .= "			require_once(\$plugindir.\"templates/default/content_menu_template.php\");\n";
		$data .= "		}\n";
		$data .= "	}\n";
		$data .= "}\n";
		$data .= "\n";
		$data .= "//get category array\n";
		$data .= "\$array = \$aa -> getCategoryTree(\"\", intval(\$menutypeid), TRUE);\n";
		$data .= "\n";
		$data .= "// menu visibility --------------------------------------------------\n";
		$data .= "if(isset(\$content_pref[\"content_menu_visibilitycheck\"]) && \$content_pref[\"content_menu_visibilitycheck\"]){\n";
		$data .= "	\$check='';\n";
		$data .= "	//if url contains plugin/content\n";
		$data .= "	if(strpos(e_SELF, e_PLUGIN_ABS.\"content/\")!==FALSE){\n";
		$data .= "		//if current page is content.php\n";
		$data .= "		if(e_PAGE == 'content.php'){\n";
		$data .= "			if(e_QUERY){\n";
		$data .= "				\$qs=explode(\".\",e_QUERY);\n";
		$data .= "				if(isset(\$qs[0]) && in_array(\$qs[0], array('recent','cat','top','score','author','list','content')) ){\n";
		$data .= "					if(isset(\$qs[1]) && is_numeric(\$qs[1])){\n";
		$data .= "						\$check = intval(\$qs[1]);\n";
		$data .= "					}elseif(isset(\$qs[1]) && \$qs[1]=='list'){\n";
		$data .= "						if(isset(\$qs[2]) && is_numeric(\$qs[2])){\n";
		$data .= "							\$check = intval(\$qs[2]);\n";
		$data .= "						}\n";
		$data .= "					}\n";
		$data .= "					//content item\n";
		$data .= "					if(isset(\$qs[0]) && \$qs[0]=='content' && is_numeric(\$qs[1])){\n";
		$data .= "						if(\$sql -> db_Select('pcontent', \"content_parent\", \" content_id='\".intval(\$check).\"' \")){\n";
		$data .= "							\$row = \$sql -> db_Fetch();\n";
		$data .= "							\$check = \$row['content_parent'];\n";
		$data .= "						}\n";
		$data .= "					}\n";
		$data .= "				}\n";
		$data .= "			}\n";
		$data .= "		}\n";
		$data .= "	}\n";
		$data .= "	if(is_numeric(\$check) && in_array(\$check, array_keys(\$array)) ){\n";
		$data .= "		//continue\n";
		$data .= "	}else{\n";
		$data .= "		//do not show menu, so return empty\n";
		$data .= "		return;\n";
		$data .= "	}\n";
		$data .= "}\n";
		$data .= "// end menu visibility --------------------------------------------------\n";
		$data .= "\n";
		$data .= "global \$icon, \$bullet, \$row, \$CMT_CATEGORY, \$CMT_RECENT;\n";
		$data .= "\$icon = \$tp->parseTemplate(\"{CM_MENU_LINKS_ICON}\",TRUE,\$content_shortcodes);\n";
		$data .= "\n";
		$data .= "//##### CATEGORY LIST --------------------------------------------------\n";
		$data .= "\$CMT_CATEGORY = '';\n";
		$data .= "if(!\$content_pref[\"content_menu_cat_dropdown\"]){\n";
		$data .= "	if(\$content_pref[\"content_menu_cat\"]){\n";
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
		$data .= "		if(\$sql -> db_Select(\$plugintable, \"content_id, content_heading, content_icon\", \" \".\$checkid.\" ORDER BY SUBSTRING_INDEX(content_order, '.', 1)+0 \")){\n";
		$data .= "			\$CMT_CATEGORY .= \$tp->parseTemplate(\$CONTENT_MENU_CATEGORY_START,TRUE,\$content_shortcodes);\n";
		$data .= "			while(\$row = \$sql -> db_Fetch()){\n";
		$data .= "				\$CMT_CATEGORY .= \$tp->parseTemplate(\$CONTENT_MENU_CATEGORY_TABLE,TRUE,\$content_shortcodes);\n";
		$data .= "			}\n";
		$data .= "			\$CMT_CATEGORY .= \$tp->parseTemplate(\$CONTENT_MENU_CATEGORY_END,TRUE,\$content_shortcodes);\n";
		$data .= "		}\n";
		$data .= "	}\n";
		$data .= "}\n";
		$data .= "\n";
		$data .= "//##### RECENT --------------------------------------------------\n";
		$data .= "\$CMT_RECENT = '';\n";
		$data .= "if(\$content_pref[\"content_menu_recent\"]){\n";
		$data .= "	//prepare query paramaters\n";
		$data .= "	\$validparent = implode(\",\", array_keys(\$array));\n";
		$data .= "	\$qry = \" content_parent REGEXP '\".\$aa -> CONTENTREGEXP(\$validparent).\"' \";\n";
		$data .= "\n";
		$data .= "	if(\$resultitem = \$sql -> db_Select(\$plugintable, \"content_id, content_heading, content_subheading, content_author, content_icon, content_datestamp\", \"content_refer !='sa' AND \".\$qry.\" \".\$datequery.\" AND content_class REGEXP '\".e_CLASS_REGEXP.\"' ORDER BY content_datestamp DESC LIMIT 0,\".\$content_pref[\"content_menu_recent_number\"] )){\n";
		$data .= "\n";
		$data .= "		\$CMT_RECENT .= \$tp->parseTemplate(\$CONTENT_MENU_RECENT_START,TRUE,\$content_shortcodes);\n";
		$data .= "		while(\$row = \$sql -> db_Fetch()){\n";
		$data .= "			\$CMT_RECENT .= \$tp->parseTemplate(\$CONTENT_MENU_RECENT_TABLE,TRUE,\$content_shortcodes);\n";
		$data .= "		}\n";
		$data .= "		\$CMT_RECENT .= \$tp->parseTemplate(\$CONTENT_MENU_RECENT_END,TRUE,\$content_shortcodes);\n";
		$data .= "	}\n";
		$data .= "}\n";
		$data .= "\n";
		$data .= "//##### PARSE THE MENU --------------------------------------------------\n";
		$data .= "\$text = \$tp->parseTemplate(\$CONTENT_MENU,TRUE,\$content_shortcodes);\n";
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