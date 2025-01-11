<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2017 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

	 $NEWFORUMPOSTS_MENU_TEMPLATE['default']['start']   = "<ul class='media-list list-unstyled newforumposts-menu'>{SETIMAGE: w=48&h=48&crop=1}";
	 $NEWFORUMPOSTS_MENU_TEMPLATE['default']['item']    = "<li class='media d-flex mt-3'>
														<div class='media-left me-3'>
															<a class='mr-3' href='{POST_URL}'>{POST_AUTHOR_AVATAR: shape=circle}</a>
															</div>
															<div class='media-body'>
																<h4 class='mt-0 mb-0 media-heading'><a href='{POST_URL}'>{POST_TOPIC}</a></h4>{POST_CONTENT}<br /><small class='text-muted muted'>{LAN=FORUM_MENU_001} {POST_AUTHOR_NAME} {POST_DATESTAMP}</small>
														</div></li>";
	 $NEWFORUMPOSTS_MENU_TEMPLATE['default']['end']     = "</ul>";




	 $NEWFORUMPOSTS_MENU_TEMPLATE['minimal']['start']   = "<ul class='media-list newforumposts-menu'>{SETIMAGE: w=48&h=48&crop=1}";
	 $NEWFORUMPOSTS_MENU_TEMPLATE['minimal']['item']    = "<li class='media'>
															<div class='media-left'>
															<a href='{POST_URL}'>{POST_AUTHOR_AVATAR: shape=circle}</a>
															</div>
															<div class='media-body'>
																<a href='{POST_URL}'>{LAN=FORUM_MENU_001}</a> {POST_AUTHOR_NAME} <small class='text-muted muted'>{POST_DATESTAMP}</small><br />{POST_CONTENT}<br />
															</div></li>";
	 $NEWFORUMPOSTS_MENU_TEMPLATE['minimal']['end']     = "</ul>";




	 $NEWFORUMPOSTS_MENU_TEMPLATE['main']['start']      = "<!-- newforumposts -->
														<table class='table table-bordered table-striped fborder'>
														<tr>
														<td style='width:5%' class='forumheader'>&nbsp;</td>
														<td style='width:45%' class='forumheader'>{LAN=FORUM_1003}</td>
														<td style='width:15%; text-align:center' class='forumheader'>{LAN=USER}</td>
														<td style='width:5%; text-align:center' class='forumheader'>{LAN=FORUM_1005}</td>
														<td style='width:5%; text-align:center' class='forumheader'>{LAN=FORUM_0003}</td>
														<td style='width:25%; text-align:center' class='forumheader'>{LAN=FORUM_0004}</td>
														</tr>";

	$NEWFORUMPOSTS_MENU_TEMPLATE['main']['item']        = "<tr>
														<td style='width:5%; text-align:center' class='forumheader3'>{TOPIC_ICON}</td>
														<td style='width:45%' class='forumheader3'><a href='{POST_URL}'>{TOPIC_NAME}</a> <small class='smalltext'>(<a href='{FORUM_URL}'>{FORUM_NAME}</a>)</small></td>
														<td style='width:15%; text-align:center' class='forumheader3'>{TOPIC_AUTHOR_NAME}</td>
														<td style='width:5%; text-align:center' class='forumheader3'>{TOPIC_VIEWS}</td>
														<td style='width:5%; text-align:center' class='forumheader3'>{TOPIC_REPLIES}</td>
														<td style='width:25%; text-align:center' class='forumheader3'>{TOPIC_LASTPOST_AUTHOR}<br /><span class='smalltext'>{TOPIC_LASTPOST_DATE}&nbsp;</span></td>
														</tr>";

	$NEWFORUMPOSTS_MENU_TEMPLATE['main']['end']         = "<tr>
														<td colspan='6' style='text-align:center' class='forumheader2'>
														<span class='smalltext'>{LAN=FORUM_0002}: <b>{TOTAL_TOPICS}</b> | {LAN=FORUM_0003}: <b>{TOTAL_REPLIES}</b> | {LAN=FORUM_1005}: <b>{TOTAL_VIEWS}</b></span>
														</td>
														</tr>
														</table>
														";
