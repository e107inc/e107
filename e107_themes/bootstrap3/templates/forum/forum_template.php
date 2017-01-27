<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

$FORUM_TEMPLATE['main']['start']			= "{FORUM_BREADCRUMB}
											<div class=''>

												<div class='form-group right'>
													{SEARCH}
												</div>
											</div>
											<div id='forum' >
											<table class='table table-striped table-bordered table-hover'>
											<colgroup>
											<col style='width:3%' />
											<col />
											<col class='hidden-xs' style='width:10%' />
											<col style='width:10%' />
											<col class='hidden-xs' style='width:20%' />
											</colgroup>
											<tr>
											<th colspan='5'>{FORUMTITLE}</th>
											</tr>";

$FORUM_TEMPLATE['main']['parent']			= 	"<tr>
											<th colspan='2'>{PARENTIMG:w=100&fmt=img}{PARENTSTATUS}</th>
											<th class='hidden-xs text-center'>".LAN_FORUM_0003."</th>
											<th class='text-center'>".LAN_FORUM_0002."</th>
											<th class='hidden-xs text-center'>".LAN_FORUM_0004."</th>
											</tr>";



$FORUM_TEMPLATE['main']['forum']			= 	"<tr>
											<td>{NEWFLAG}</td>
											<td>{FORUMIMG:w=450&fmt=img}<br /><small>{FORUMDESCRIPTION}</small>{FORUMSUBFORUMS}</td>
											<td class='hidden-xs text-center'>{REPLIESX}</td>
											<td class='text-center'>{THREADSX}</td>
											<td class='hidden-xs text-center'><small>{LASTPOST:type=username} {LASTPOST:type=datelink}</small></td>
											</tr>";


$FORUM_TEMPLATE['main']['end']				= "</table><div class='forum-footer center'><small>{USERINFOX}</small></div></div>";


// Tracking
$FORUM_TEMPLATE['track']['start']       = "{FORUM_BREADCRUMB}<div id='forum-track'>
											<table class='table table-striped table-bordered table-hover'>
											<colgroup>
											<col style='width:5%' />
											<col />
											<col style='width:15%' />
											<col style='width:5%' />
											</colgroup>
											<thead>
											<tr>

												<th colspan='2'>".LAN_FORUM_1003."</th>
												<th class='hidden-xs text-center'>".LAN_FORUM_0004."</th>
												<th class='text-center'>".LAN_FORUM_1020."</th>
												</tr>
											</thead>
											";

$FORUM_TEMPLATE['track']['item']        = "<tr>
											<td class='text-center'>{NEWIMAGE}</td>
											<td>{TRACKPOSTNAME}</td>
											<td class='hidden-xs text-center'><small>{LASTPOSTUSER} {LASTPOSTDATE}</small></td>
											<td class='text-center'>{UNTRACK}</td>
											</tr>";


$FORUM_TEMPLATE['track']['end']         = "</table>\n</div>";


?>
