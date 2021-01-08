<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)

*/

$ONLINE_MENU_TEMPLATE = array();

// Last seen Menu
$ONLINE_MENU_TEMPLATE['lastseen']['start']	                        = "<ul class='list-group lastseen-menu'>";
$ONLINE_MENU_TEMPLATE['lastseen']['item']	                        = "<li class='list-group-item d-flex justify-content-between align-items-center'>{LASTSEEN_USERLINK} <small class='muted pull-right'>{LASTSEEN_DATE}</small></li>";
$ONLINE_MENU_TEMPLATE['lastseen']['end']	                        = "</ul>";




// Online Menu - default.

$ONLINE_MENU_TEMPLATE['default']['enabled']                         = "
																	<ul class='list-group online-menu'>
																	{ONLINE_GUESTS}
																	{ONLINE_MEMBERS}
																	{ONLINE_MEMBERS_LIST}
																	{ONLINE_MEMBERS_LIST_EXTENDED}
																	{ONLINE_ONPAGE}
																	{ONLINE_MEMBERS_TOTAL}
																	{ONLINE_MEMBER_NEWEST}
																	<li class='list-group-item'>
																	{ONLINE_MOST}
																	<small class='muted'>
																	{ONLINE_MOST_GUESTS}
																	{ONLINE_MOST_MEMBERS}
																	{ONLINE_MOST_DATESTAMP}
																	</small>
																	</li>
																	</ul>
																	";

$ONLINE_MENU_TEMPLATE['default']['disabled']                        = "{ONLINE_TRACKING_DISABLED}";
$ONLINE_MENU_TEMPLATE['default']['online_members_list_extended']    = "{SETIMAGE: w=40}<li class='list-group-item media'><span class='media-object pull-left float-left mr-3'>{ONLINE_MEMBER_IMAGE: type=avatar&shape=circle}</span><span class='media-body'>{ONLINE_MEMBER_USER} <small>".LAN_ONLINE_7." {ONLINE_MEMBER_PAGE}</small></span></li>";




$ONLINE_MENU_WRAPPER['default']['ONLINE_GUESTS']                   = "<li class='list-group-item'>".LAN_ONLINE_1."{---}</li>";
$ONLINE_MENU_WRAPPER['default']['ONLINE_MEMBERS']                  = "<li class='list-group-item'>".LAN_ONLINE_2."{---}</li>";
$ONLINE_MENU_WRAPPER['default']['ONLINE_MEMBERS_LIST']             = "<li class='list-group-item'><ul>{---}</ul></li>";
$ONLINE_MENU_WRAPPER['default']['ONLINE_MEMBERS_LIST_EXTENDED']    = "<ul class='unstyled list-unstyled py-0'>{---}</ul>";
$ONLINE_MENU_WRAPPER['default']['ONLINE_ONPAGE']                   = "<li class='list-group-item'>".LAN_ONLINE_3."{---}</li>";
$ONLINE_MENU_WRAPPER['default']['ONLINE_MEMBERS_TOTAL']             = "<li class='list-group-item'>".LAN_ONLINE_11."{---}</li>";
$ONLINE_MENU_WRAPPER['default']['ONLINE_MEMBER_NEWEST']            = "<li class='list-group-item'>".LAN_ONLINE_6."{---}</li>";
$ONLINE_MENU_WRAPPER['default']['ONLINE_MOST']                     = LAN_ONLINE_8."{---}<br />";
$ONLINE_MENU_WRAPPER['default']['ONLINE_MOST_MEMBERS']             = LAN_ONLINE_2."{---}";
$ONLINE_MENU_WRAPPER['default']['ONLINE_MOST_GUESTS']              = LAN_ONLINE_1."{---}";
$ONLINE_MENU_WRAPPER['default']['ONLINE_MOST_DATESTAMP']           = LAN_ONLINE_9."{---}";
$ONLINE_MENU_WRAPPER['default']['ONLINE_MEMBERS_REGISTERED']       = "<li class='list-group-item'>".LAN_ONLINE_11."{---}";


//##### ONLINE MEMBER LIST EXTENDED -------------------------------------------

$ONLINE_MENU_TEMPLATE['extended']['disabled']                       = "{ONLINE_TRACKING_DISABLED}";
$ONLINE_MENU_TEMPLATE['extended']['enabled']                        = "
																		<ul class='list-group online-menu online-menu-extended list-unstyled'>
																		{ONLINE_GUESTS}
																		{ONLINE_MEMBERS}
																		{ONLINE_MEMBERS_LIST_EXTENDED}
																		{ONLINE_ONPAGE}
																		{ONLINE_MEMBER_NEWEST: type=avatar}
																		{ONLINE_MEMBERS_REGISTERED}
																		<li class='list-group-item d-flex justify-content-between online-menu-extended-label'>
																		{ONLINE_MOST}
																		<div id='online-menu-extended-most' class='text-muted text-right' style='display:none'>
																			<small>
																			{ONLINE_MOST_GUESTS}<br />
																			{ONLINE_MOST_MEMBERS}<br />
																			{ONLINE_MOST_DATESTAMP=long}
																			</small>
																		</div>
																		</li>
																		</ul>
																	";

$ONLINE_MENU_TEMPLATE['extended']['online_members_list_extended']   = "{SETIMAGE: w=48&h=48&crop=1}<li class='media d-flex'><div class='media-left mr-3 me-3'>{ONLINE_MEMBER_IMAGE: type=avatar&shape=circle}</div><div class='media-body'><span class='online-menu-user'>{ONLINE_MEMBER_USER}</span><small class='text-muted'>{ONLINE_MEMBER_PAGE}</small></div></li>";
$ONLINE_MENU_TEMPLATE['extended']['online_member_newest']           = "{SETIMAGE: w=48&h=48&crop=1}<li class='media d-flex'><div class='media-left mr-3 me-3'>{ONLINE_MEMBER_IMAGE: type=avatar&shape=circle}</div><div class='media-body'><span class='online-menu-user'>{ONLINE_MEMBER_USER}</span></div></li>";


// Shortcode wrappers
$ONLINE_MENU_WRAPPER['extended']['ONLINE_GUESTS']                   = "<li class='list-group-item d-flex justify-content-between align-items-center online-menu-extended-label'>".LAN_ONLINE_1."<span class='badge bg-primary rounded-pill label label-primary pull-right float-right'>{---}</span></li>";
$ONLINE_MENU_WRAPPER['extended']['ONLINE_MEMBERS']                  = "<li class='list-group-item d-flex justify-content-between align-items-center online-menu-extended-label'>".LAN_ONLINE_2."<span class='badge bg-primary rounded-pill label label-primary pull-right float-right'>{---}</span></li>";
$ONLINE_MENU_WRAPPER['extended']['ONLINE_MEMBERS_LIST']             = "<ul>{---}</ul>";
$ONLINE_MENU_WRAPPER['extended']['ONLINE_MEMBERS_LIST_EXTENDED']    = "<li class='list-group-item'><ul class='unstyled list-unstyled py-0'>{---}</ul></li>";
$ONLINE_MENU_WRAPPER['extended']['ONLINE_ONPAGE']                   = "<li class='list-group-item d-flex justify-content-between align-items-center'>".LAN_ONLINE_3."<span class='badge bg-primary rounded-pill label label-default pull-right float-right'>{---}</span></li>";
$ONLINE_MENU_WRAPPER['extended']['ONLINE_MEMBERS_TOTAL']             = "<li class='list-group-item d-flex justify-content-between align-items-center'>".LAN_ONLINE_11."<span class='badge bg-primary rounded-pill label label-default pull-right float-right'>{---}</span></li>";
$ONLINE_MENU_WRAPPER['extended']['ONLINE_MEMBER_NEWEST']            = "<li class='list-group-item online-menu-extended-label'>".LAN_ONLINE_6."</li><li class='list-group-item'><ul class='unstyled list-unstyled py-0'>{---}</ul></li>";
$ONLINE_MENU_WRAPPER['extended']['ONLINE_MOST']                     = "<a class='e-expandit' href='#online-menu-extended-most'>".LAN_ONLINE_8."</a><span class='badge bg-primary rounded-pill label label-default pull-right float-right'>{---}</span><br />";
$ONLINE_MENU_WRAPPER['extended']['ONLINE_MOST_MEMBERS']             = LAN_ONLINE_2."{---}";
$ONLINE_MENU_WRAPPER['extended']['ONLINE_MOST_GUESTS']              = LAN_ONLINE_1."{---}";
$ONLINE_MENU_WRAPPER['extended']['ONLINE_MOST_DATESTAMP']           = "{---}";
$ONLINE_MENU_WRAPPER['extended']['ONLINE_MEMBERS_REGISTERED']       = "<li class='list-group-item d-flex justify-content-between align-items-center online-menu-extended-label'>".LAN_ONLINE_11."<span class='badge bg-primary rounded-pill label label-default pull-right float-right'>{---}</span></li>";
$ONLINE_MENU_WRAPPER['extended']['ONLINE_MEMBER_PAGE']              = LAN_ONLINE_7." {---}";
