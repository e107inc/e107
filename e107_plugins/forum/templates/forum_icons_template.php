<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum icons template - default
 *
 */

if (!defined('e107_INIT')) { exit(); }

$tp = e107::getParser();
if(deftrue("FONTAWESOME", false)) {

define('IMAGE_e', 					'e');
define('IMAGE_new', 				$tp->toGlyph('fa-star', 'size=2x'));
define('IMAGE_nonew', 				$tp->toGlyph('fa-comment', 'size=2x'));
define('IMAGE_new_small',  			$tp->toGlyph('fa-star'));
define('IMAGE_nonew_small',  		$tp->toGlyph('fa-comment'));
define('IMAGE_new_popular',  		$tp->toGlyph('fa-comments', 'size=2x'));
define('IMAGE_nonew_popular', 		$tp->toGlyph('fa-comments-o', 'size=2x'));
define('IMAGE_new_popular_small',  	$tp->toGlyph('fa-comments'));
define('IMAGE_nonew_popular_small', $tp->toGlyph('fa-comments-o'));
define('IMAGE_sticky',  			$tp->toGlyph('fa-thumb-tack', 'size=2x'));
define('IMAGE_stickyclosed',  		$tp->toGlyph('fa-lock', 'size=2x'));
define('IMAGE_sticky_small', 		$tp->toGlyph('fa-thumb-tack'));
define('IMAGE_stickyclosed_small',  $tp->toGlyph('fa-lock'));
define('IMAGE_announce',  			$tp->toGlyph('fa-bullhorn', 'size=2x'));
define('IMAGE_announce_small',  	$tp->toGlyph('fa-bullhorn'));
define('IMAGE_closed_small',  		$tp->toGlyph('fa-lock'));
define('IMAGE_closed', 				$tp->toGlyph('fa-lock', 'size=2x'));
define('IMAGE_noreplies', 			$tp->toGlyph('fa-comment-o', 'size=2x'));
define('IMAGE_noreplies_small', 	$tp->toGlyph('fa-comment-o'));
define('IMAGE_track', 		        $tp->toGlyph('fa-bell'));
define('IMAGE_untrack', 	        $tp->toGlyph('fa-bell-o'));
    
} else {

// Thread info
define('IMAGE_e', 					'<img src="'.img_path('e.png').'" alt="" title="" />');
define('IMAGE_new', 				'<img src="'.img_path('new.png').'" alt="'.LAN_FORUM_4001.'" title="'.LAN_FORUM_4001.'" />');
define('IMAGE_nonew', 				'<img src="'.img_path('nonew.png').'" alt="'.LAN_FORUM_4002.'" title="'.LAN_FORUM_4002.'" />');
define('IMAGE_new_small', 			'<img src="'.img_path('new_small.png').'" alt="'.LAN_FORUM_4001.'" title="'.LAN_FORUM_4001.'" />');
define('IMAGE_nonew_small', 		'<img src="'.img_path('nonew_small.png').'" alt="'.LAN_FORUM_4002.'" title="'.LAN_FORUM_4002.'" />');
define('IMAGE_new_popular', 		'<img src="'.img_path('new_popular.png').'" alt="'.LAN_FORUM_4003.'" title="'.LAN_FORUM_4003.'" />');
define('IMAGE_nonew_popular', 		'<img src="'.img_path('nonew_popular.png').'" alt="'.LAN_FORUM_4004.'" title="'.LAN_FORUM_4004.'" />');
define('IMAGE_new_popular_small', 	'<img src="'.img_path('new_popular_small.png').'" alt="'.LAN_FORUM_4003.'" title="'.LAN_FORUM_4003.'" />');
define('IMAGE_nonew_popular_small',	'<img src="'.img_path('nonew_popular_small.png').'" alt="'.LAN_FORUM_4004.'" title="'.LAN_FORUM_4004.'" />');
define('IMAGE_sticky', 				'<img src="'.img_path('sticky.png').'" alt="'.LAN_FORUM_1011.'" title="'.LAN_FORUM_1011.'" />');
define('IMAGE_sticky_small', 		'<img src="'.img_path('sticky_small.png').'" alt="'.LAN_FORUM_1011.'" title="'.LAN_FORUM_1011.'" />');
define('IMAGE_stickyclosed', 		'<img src="'.img_path('sticky_closed.png').'" alt="'.LAN_FORUM_1012.'" title="'.LAN_FORUM_1012.'" />');
define('IMAGE_stickyclosed_small', 	'<img src="'.img_path('sticky_closed_small.png').'" alt="'.LAN_FORUM_1012.'" title="'.LAN_FORUM_1012.'" />');
define('IMAGE_announce', 			'<img src="'.img_path('announce.png').'" alt="'.LAN_FORUM_1013.'" title="'.LAN_FORUM_1013.'" />');
define('IMAGE_announce_small', 		'<img src="'.img_path('announce_small.png').'" alt="'.LAN_FORUM_1013.'" title="'.LAN_FORUM_1013.'" />');
define('IMAGE_closed_small', 		'<img src="'.img_path('closed_small.png').'" alt="'.LAN_FORUM_1014.'" title="'.LAN_FORUM_1014.'" />');
define('IMAGE_closed', 				'<img src="'.img_path('closed.png').'" alt="'.LAN_FORUM_1014.'" title="'.LAN_FORUM_1014.'" />');

define('IMAGE_track', 		'<img src="'.img_path('track.png').'" alt="'.LAN_FORUM_4009.'" title="'.LAN_FORUM_4009.'" class="icon S16 action" />');
define('IMAGE_untrack', 	'<img src="'.img_path('untrack.png').'" alt="'.LAN_FORUM_4010.'" title="'.LAN_FORUM_4010.'" class="icon S16 action" />');

}

// User info
define('IMAGE_website', '<img src="'.img_path('website.png').'" alt="'.LAN_FORUM_2034.'" title="'.LAN_FORUM_2034.'" />');
define('IMAGE_email', 	'<img src="'.img_path('email.png').'" alt="'.LAN_FORUM_2044.'" title="'.LAN_FORUM_2044.'" class="icon S16 action" />');
define('IMAGE_profile', '<img src="'.img_path('profile.png').'" alt="'.LAN_FORUM_4007.'" title="'.LAN_FORUM_4007.'" />');

// action
define('IMAGE_pm', 			'<img src="'.img_path('pm.png').'" alt="'.LAN_FORUM_4008.'" title="'.LAN_FORUM_4008.'" class="icon S16 action" />');
define('IMAGE_edit', 		'<img src="'.img_path('edit.png').'" alt="'.LAN_EDIT.'" title="'.LAN_EDIT.'" class="icon S16 action" />');
define('IMAGE_quote', 		'<img src="'.img_path('quote.png').'" alt="'.LAN_FORUM_2041.'" title="'.LAN_FORUM_2041.'" class="icon S16 action" />');

define('IMAGE_admin_edit', 	'<img src="'.img_path('admin_edit.png').'" alt="'.LAN_EDIT.'" title="'.LAN_EDIT.'" class="icon S16 action" />');
define('IMAGE_admin_move', 	'<img src="'.img_path('admin_move.png').'" alt="'.LAN_FORUM_2042.'" title="'.LAN_FORUM_2042.'" class="icon S16 action" />');
define('IMAGE_admin_split', '<img src="'.img_path('admin_split.png').'" alt="'.LAN_FORUM_2043.'" title="'.LAN_FORUM_2043.'" class="icon S16 action" />');
define('IMAGE_admin_move2',	'<img src="'.img_path('admin_move.png').'" alt="'.LAN_FORUM_2042.'" title="'.LAN_FORUM_2042.'" class="icon S16 action" />');
define('IMAGE_report', 		'<img src="'.img_path('report.png').'" alt="'.LAN_FORUM_2046.'" title="'.LAN_FORUM_2046.'" class="icon S16 action" />');
define('IMAGE_attachment', 	'<img src="'.img_path('attach.png').'" alt="'.LAN_FORUM_3013.'" title="'.LAN_FORUM_3013.'" class="icon S16 action" />');
define('IMAGE_post',		'<img src="'.img_path('post.png').'" alt="" title="" />');
define('IMAGE_post2', 		'<img src="'.img_path('post2.png').'" alt="" title="" class="icon S16 action" />');

// Admin <input> Icons
define('IMAGE_admin_delete',	'src="'.img_path('admin_delete.png').'" alt="'.LAN_DELETE.'" title="'.LAN_DELETE.'" ');
define('IMAGE_admin_stick',		'src="'.img_path('admin_stick.png').'" alt="'.LAN_FORUM_4011.'" title="'.LAN_FORUM_4011.'" ');
define('IMAGE_admin_unstick',	'src="'.img_path('admin_unstick.png').'" alt="'.LAN_FORUM_4012.'" title="'.LAN_FORUM_4012.'" ');
define('IMAGE_admin_lock',		'src="'.img_path('admin_lock.png').'" alt="'.LAN_FORUM_4013.'" title="'.LAN_FORUM_4013.'" ');
define('IMAGE_admin_unlock',	'src="'.img_path('admin_unlock.png').'" alt="'.LAN_FORUM_4014.'" title="'.LAN_FORUM_4014.'" ');

// Multi Language Images
define('IMAGE_newthread',				'<img src="'.img_path('newthread.png').'" alt="'.LAN_FORUM_2005.'" title="'.LAN_FORUM_2005.'" />');
define('IMAGE_reply',					'<img src="'.img_path('reply.png').'" alt="'.LAN_FORUM_2006.'" title="'.LAN_FORUM_2006.'" />');
define('IMAGE_rank_moderator_image',	'<img src="'.img_path('moderator.png', '', 'rank_moderator_image').'" alt="" />');
define('IMAGE_rank_main_admin_image',	'<img src="'.img_path('main_admin.png', '', 'rank_main_admin_image').'" alt="" />');
define('IMAGE_rank_admin_image', 		'<img src="'.img_path('admin.png', '', 'rank_admin_image').'" alt="" />');


