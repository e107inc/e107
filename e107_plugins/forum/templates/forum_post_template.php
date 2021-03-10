<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

// New in v2.x - requires a bootstrap theme be loaded.  

//$FORUM_POST_TEMPLATE['caption']		= "{FORUM_POST_CAPTION}";
$FORUM_POST_TEMPLATE['form']		= "
									{FORUM_POST_FORM_START}
									<div class='row-fluid'>
										<div>{FORUM_POST_BREADCRUMB}</div>
									</div>

									<div class='form-group row mb-3'>
										<label for='name' class='col-sm-3 control-label'>{LAN=FORUM_3010}</label>
										 <div class='col-sm-9'>{FORUM_POST_AUTHOR}</div>
									</div>

									<div class='form-group row mb-3'>
										<label for='subject' class='col-sm-3 control-label'>{LAN=FORUM_3011}</label>
										 <div class='col-sm-9'>{FORUM_POST_SUBJECT}</div>
									</div>

									<div class='form-group row mb-3'>
										<label class='col-sm-3 control-label'>{FORUM_POST_TEXTAREA_LABEL}</label>
										 <div class='col-sm-9'>
										 	{FORUM_POST_TEXTAREA}
											{FORUM_POST_EMAIL_NOTIFY}
										</div>
									</div>

									<div class='form-group row mb-3'>
										<label class='col-sm-3 control-label'>{FORUM_POST_OPTIONS_LABEL}</label>
										 <div class='col-sm-9'>{FORUM_POST_OPTIONS}</div>
									</div>

									<div class='form-group my-5 text-center'>
										{FORUM_POST_BUTTONS}
									</div>
									{FORUM_POST_FORM_END}


								";



$FORUM_POST_TEMPLATE['reply']	= "";

// $FORUM_POST_WRAPPER['FORUM_POST_TEXTAREA'] = "(pre){---}(post)";  // Custom Wrapper. 


$FORUM_CRUMB['sitename']['value'] = "<a class='forumlink' href='{SITENAME_HREF}'>{SITENAME}</a>";
$FORUM_CRUMB['sitename']['sep'] = " :: ";

$FORUM_CRUMB['forums']['value'] = "<a class='forumlink' href='{FORUMS_HREF}'>{FORUMS_TITLE}</a>";
$FORUM_CRUMB['forums']['sep'] = " :: ";

$FORUM_CRUMB['parent']['value'] = "{PARENT_TITLE}";
$FORUM_CRUMB['parent']['sep'] = " :: ";

$FORUM_CRUMB['subparent']['value'] = "<a class='forumlink' href='{SUBPARENT_HREF}'>{SUBPARENT_TITLE}</a>";
$FORUM_CRUMB['subparent']['sep'] = " :: ";

$FORUM_CRUMB['forum']['value'] = "<a class='forumlink' href='{FORUM_HREF}'>{FORUM_TITLE}</a>";
$FORUM_CRUMB['forum']['sep'] = " :: ";

$FORUM_CRUMB['thread']['value'] = "<a class='forumlink' href='{THREAD_HREF}'>{THREAD_TITLE}</a>";

