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
 * $Source: /cvs_backup/e107_0.8/e107_themes/templates/user_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

//  Bootstrap 4 v2.x Standards.


	$USER_TEMPLATE = array(); // reset the legacy template above.
	$USER_WRAPPER = array(); // reset all the legacy wrappers above.


	$USER_TEMPLATE['addon']  = '
		<div class="row">
			<div class="col-xs-12 col-md-4">{USER_ADDON_LABEL}</div>
			<div class="col-xs-12 col-md-8">{USER_ADDON_TEXT}</div>
		 </div>
		';

	$USER_TEMPLATE['extended']['start'] = '';
	$USER_TEMPLATE['extended']['end']   = '';

	$USER_TEMPLATE['extended']['item'] = '
		<div class="row {EXTENDED_ID}">
		    <div class="ue-label col-xs-12 col-md-4">{EXTENDED_NAME}</div>
		    <div class="ue-value col-xs-12 col-md-8">{EXTENDED_VALUE}</div>
		</div>
		';


	$USER_TEMPLATE['list']['start']  = "
		<div class='content user-list'>
		<div class='center'>".LAN_USER_56." {TOTAL_USERS}
		<br />
		<br />
		{USER_FORM_START}
		<div class='form-inline'>
		".LAN_SHOW.": {USER_FORM_RECORDS} ".LAN_USER_57." {USER_FORM_ORDER}
		{USER_FORM_SUBMIT}
		</div>
		{USER_FORM_END}
		</div>
		<br />
		<br />
		<table style='".USER_WIDTH."' class='table fborder e-list'>
		<thead>
		<tr>
		<th class='fcaption' style='width:2%'>&nbsp;</th>
		<th class='fcaption' style='width:20%'>".LAN_USER_58."</th>
		<th class='fcaption' style='width:20%'>".LAN_USER_60."</th>
		<th class='fcaption' style='width:20%'>".LAN_USER_59."</th>
		</tr>
		</thead>
		<tbody>
		{SETIMAGE: w=40}
	";


	$USER_TEMPLATE['list']['item']  = "
	<tr>
		<td class='forumheader3' style='width:2%'>{USER_PICTURE}</td>
		<td class='forumheader3' style='width:20%'>{USER_ID}: {USER_NAME_LINK}</td>
		<td class='forumheader3' style='width:20%'>{USER_EMAIL}</td>
		<td class='forumheader3' style='width:20%'>{USER_JOIN}</td>
	</tr>
	";

	$USER_TEMPLATE['list']['end']  = "
	</tbody>
	</table>
	</div>
	";


	// View shortcode wrappers.
	$USER_WRAPPER['view']['USER_COMMENTPOSTS']      = '<div class="col-xs-12 col-md-4">'.LAN_USER_68.'</div><div class="col-xs-12 col-md-8">{---}';
	$USER_WRAPPER['view']['USER_COMMENTPER']        = ' ( {---}% )</div>';
	$USER_WRAPPER['view']['USER_SIGNATURE']         = '<div>{---}</div>';
	$USER_WRAPPER['view']['USER_RATING']            = '<div>{---}</div>';
	$USER_WRAPPER['view']['USER_SENDPM']            = '<div>{---}</div>';
	$USER_WRAPPER['view']['PROFILE_COMMENTS']       = '<div class="clearfix">{---}</div>';
//	$USER_WRAPPER['view']['PROFILE_COMMENT_FORM']   = '{---} </div>';

	$USER_TEMPLATE['view'] 				= '
	{SETIMAGE: w=600}
	<div class="user-profile user-profile-bs4 row">
	    <div class="col-md-12">
	        <div class="panel panel-default panel-profile card card-profile  clearfix">
	            <div class="panel-heading card-heading pt-2 pl-2" style="height:180px; background-size: cover;background-image: url( {USER_PHOTO: type=url});">
	                <h5 class="user-id">'.LAN_USER_58.' {USER_ID}</h5>
	            </div>
	            <div class="panel-body card-body text-center">
	                {SETIMAGE: w=200&h=200&crop=1}
	                {USER_PICTURE: shape=circle&link=1}
	                <div class="profile-header">
	                    <h4>{USER_NAME}</h4>
	                    {USER_SIGNATURE}
	                    {USER_RATING}
	                    {USER_SENDPM}
	                </div>
	            </div>
	            <div class="panel-body card-body">
	                <div class="row"><div class="col-xs-12 col-md-4">'.LAN_USER_63.'</div><div class="col-xs-12 col-md-8">{USER_REALNAME}</div></div>
	                <div class="row"><div class="col-xs-12 col-md-4">'.LAN_USER_02.'</div><div class="col-xs-12 col-md-8">{USER_LOGINNAME}</div></div>
	                <div class="row"><div class="col-xs-12 col-md-4">'.LAN_USER_60.'</div><div class="col-xs-12 col-md-8">{USER_EMAIL}</div></div>
	                <div class="row"><div class="col-xs-12 col-md-4">'.LAN_USER_54.'</div><div class="col-xs-12 col-md-8">{USER_LEVEL}</div></div>
	                <div class="row"><div class="col-xs-12 col-md-4">'.LAN_USER_65.'</div><div class="col-xs-12 col-md-8">{USER_LASTVISIT}<br /><small class="padding-left">{USER_LASTVISIT_LAPSE}</small></div></div>
	                <div class="row"><div class="col-xs-12 col-md-4">'.LAN_USER_59.'</div><div class="col-xs-12 col-md-8">{USER_JOIN}<br /><small class="padding-left">{USER_DAYSREGGED}</small></div></div>
	                <div class="row"><div class="col-xs-12 col-md-4">'.LAN_USER_66.'</div><div class="col-xs-12 col-md-8">{USER_VISITS}</div></div>
	                {USER_ADDONS}
	                <div class="row">{USER_COMMENTPOSTS} {USER_COMMENTPER}</div>
	                {USER_EXTENDED_ALL}
	                <div class="row"></div>
	            </div>
	            <div class="panel-body card-body text-center">
	                {USER_UPDATE_LINK}
	            </div>
                   <div class="panel-body card-body">
	                <ul class="pagination d-flex justify-content-between user-view-nextprev">
	                    <li class="page-item previous">
	                       {USER_JUMP_LINK=prev}
	                    </li>
		               <li>
	                       <!-- Back to List? -->
	                    </li>
	                    <li class="page-item next">
	                       {USER_JUMP_LINK=next}
	                    </li>
	                </ul>
	            </div>
	        </div>
			
	          
		
		
	    </div>
	</div>
		<!-- Start Comments -->
	  {PROFILE_COMMENTS}
	  <!-- End Comments -->
	 
	';








