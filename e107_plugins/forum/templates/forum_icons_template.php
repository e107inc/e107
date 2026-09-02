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

$icons = array(
	'IMAGE_e'                   => function() { return 'e'; },
	'IMAGE_new'                 => function() use ($tp) { return $tp->toGlyph('fa-star', 'size=2x'); },
	'IMAGE_nonew'               => function() use ($tp) { return $tp->toGlyph('fa-comment', 'size=2x'); },
	'IMAGE_new_small'           => function() use ($tp) { return $tp->toGlyph('fa-star'); },
	'IMAGE_nonew_small'         => function() use ($tp) { return $tp->toGlyph('fa-comment'); },
	'IMAGE_new_popular'         => function() use ($tp) { return $tp->toGlyph('fa-comments', 'size=2x'); },
	'IMAGE_nonew_popular'       => function() use ($tp) { return $tp->toGlyph('fa-comments-o', 'size=2x'); },
	'IMAGE_new_popular_small'   => function() use ($tp) { return $tp->toGlyph('fa-comments'); },
	'IMAGE_nonew_popular_small' => function() use ($tp) { return $tp->toGlyph('fa-comments-o'); },
	'IMAGE_sticky'              => function() use ($tp) { return $tp->toGlyph('fa-thumb-tack', 'size=2x'); },
	'IMAGE_stickyclosed'        => function() use ($tp) { return $tp->toGlyph('fa-lock', 'size=2x'); },
	'IMAGE_sticky_small'        => function() use ($tp) { return $tp->toGlyph('fa-thumb-tack'); },
	'IMAGE_stickyclosed_small'  => function() use ($tp) { return $tp->toGlyph('fa-lock'); },
	'IMAGE_announce'            => function() use ($tp) { return $tp->toGlyph('fa-bullhorn', 'size=2x'); },
	'IMAGE_announce_small'      => function() use ($tp) { return $tp->toGlyph('fa-bullhorn'); },
	'IMAGE_closed_small'        => function() use ($tp) { return $tp->toGlyph('fa-lock'); },
	'IMAGE_closed'              => function() use ($tp) { return $tp->toGlyph('fa-lock', 'size=2x'); },
	'IMAGE_noreplies'           => function() use ($tp) { return $tp->toGlyph('fa-comment-o', 'size=2x'); },
	'IMAGE_noreplies_small'     => function() use ($tp) { return $tp->toGlyph('fa-comment-o'); },
	'IMAGE_track'               => function() use ($tp) { return $tp->toGlyph('fa-bell'); },
	'IMAGE_untrack'             => function() use ($tp) { return $tp->toGlyph('fa-bell-o'); },
);

} else {

// Thread info
$icons = array(
	'IMAGE_e'                   => function() { return '<img src="'.img_path('e.png').'" alt="" title="" />'; },
	'IMAGE_new'                 => function() { return '<img src="'.img_path('new.png').'" alt="'.LAN_FORUM_4001.'" title="'.LAN_FORUM_4001.'" />'; },
	'IMAGE_nonew'               => function() { return '<img src="'.img_path('nonew.png').'" alt="'.LAN_FORUM_4002.'" title="'.LAN_FORUM_4002.'" />'; },
	'IMAGE_new_small'           => function() { return '<img src="'.img_path('new_small.png').'" alt="'.LAN_FORUM_4001.'" title="'.LAN_FORUM_4001.'" />'; },
	'IMAGE_nonew_small'         => function() { return '<img src="'.img_path('nonew_small.png').'" alt="'.LAN_FORUM_4002.'" title="'.LAN_FORUM_4002.'" />'; },
	'IMAGE_new_popular'         => function() { return '<img src="'.img_path('new_popular.png').'" alt="'.LAN_FORUM_4003.'" title="'.LAN_FORUM_4003.'" />'; },
	'IMAGE_nonew_popular'       => function() { return '<img src="'.img_path('nonew_popular.png').'" alt="'.LAN_FORUM_4004.'" title="'.LAN_FORUM_4004.'" />'; },
	'IMAGE_new_popular_small'   => function() { return '<img src="'.img_path('new_popular_small.png').'" alt="'.LAN_FORUM_4003.'" title="'.LAN_FORUM_4003.'" />'; },
	'IMAGE_nonew_popular_small' => function() { return '<img src="'.img_path('nonew_popular_small.png').'" alt="'.LAN_FORUM_4004.'" title="'.LAN_FORUM_4004.'" />'; },
	'IMAGE_sticky'              => function() { return '<img src="'.img_path('sticky.png').'" alt="'.LAN_FORUM_1011.'" title="'.LAN_FORUM_1011.'" />'; },
	'IMAGE_sticky_small'        => function() { return '<img src="'.img_path('sticky_small.png').'" alt="'.LAN_FORUM_1011.'" title="'.LAN_FORUM_1011.'" />'; },
	'IMAGE_stickyclosed'        => function() { return '<img src="'.img_path('sticky_closed.png').'" alt="'.LAN_FORUM_1012.'" title="'.LAN_FORUM_1012.'" />'; },
	'IMAGE_stickyclosed_small'  => function() { return '<img src="'.img_path('sticky_closed_small.png').'" alt="'.LAN_FORUM_1012.'" title="'.LAN_FORUM_1012.'" />'; },
	'IMAGE_announce'            => function() { return '<img src="'.img_path('announce.png').'" alt="'.LAN_FORUM_1013.'" title="'.LAN_FORUM_1013.'" />'; },
	'IMAGE_announce_small'      => function() { return '<img src="'.img_path('announce_small.png').'" alt="'.LAN_FORUM_1013.'" title="'.LAN_FORUM_1013.'" />'; },
	'IMAGE_closed_small'        => function() { return '<img src="'.img_path('closed_small.png').'" alt="'.LAN_FORUM_1014.'" title="'.LAN_FORUM_1014.'" />'; },
	'IMAGE_closed'              => function() { return '<img src="'.img_path('closed.png').'" alt="'.LAN_FORUM_1014.'" title="'.LAN_FORUM_1014.'" />'; },

	'IMAGE_track'               => function() { return '<img src="'.img_path('track.png').'" alt="'.LAN_FORUM_4009.'" title="'.LAN_FORUM_4009.'" class="icon S16 action" />'; },
	'IMAGE_untrack'             => function() { return '<img src="'.img_path('untrack.png').'" alt="'.LAN_FORUM_4010.'" title="'.LAN_FORUM_4010.'" class="icon S16 action" />'; },
);

}

$icons += array(

// User info
	'IMAGE_website'             => function() { return '<img src="'.img_path('website.png').'" alt="'.LAN_FORUM_2034.'" title="'.LAN_FORUM_2034.'" />'; },
	'IMAGE_email'               => function() { return '<img src="'.img_path('email.png').'" alt="'.LAN_FORUM_2044.'" title="'.LAN_FORUM_2044.'" class="icon S16 action" />'; },
	'IMAGE_profile'             => function() { return '<img src="'.img_path('profile.png').'" alt="'.LAN_FORUM_4007.'" title="'.LAN_FORUM_4007.'" />'; },

// action
	'IMAGE_pm'                  => function() { return '<img src="'.img_path('pm.png').'" alt="'.LAN_FORUM_4008.'" title="'.LAN_FORUM_4008.'" class="icon S16 action" />'; },
	'IMAGE_edit'                => function() { return '<img src="'.img_path('edit.png').'" alt="'.LAN_EDIT.'" title="'.LAN_EDIT.'" class="icon S16 action" />'; },
	'IMAGE_quote'               => function() { return '<img src="'.img_path('quote.png').'" alt="'.LAN_FORUM_2041.'" title="'.LAN_FORUM_2041.'" class="icon S16 action" />'; },

	'IMAGE_admin_edit'          => function() { return '<img src="'.img_path('admin_edit.png').'" alt="'.LAN_EDIT.'" title="'.LAN_EDIT.'" class="icon S16 action" />'; },
	'IMAGE_admin_move'          => function() { return '<img src="'.img_path('admin_move.png').'" alt="'.LAN_FORUM_2042.'" title="'.LAN_FORUM_2042.'" class="icon S16 action" />'; },
	'IMAGE_admin_split'         => function() { return '<img src="'.img_path('admin_split.png').'" alt="'.LAN_FORUM_2043.'" title="'.LAN_FORUM_2043.'" class="icon S16 action" />'; },
	'IMAGE_admin_move2'         => function() { return '<img src="'.img_path('admin_move.png').'" alt="'.LAN_FORUM_2042.'" title="'.LAN_FORUM_2042.'" class="icon S16 action" />'; },
	'IMAGE_report'              => function() { return '<img src="'.img_path('report.png').'" alt="'.LAN_FORUM_2046.'" title="'.LAN_FORUM_2046.'" class="icon S16 action" />'; },
	'IMAGE_attachment'          => function() { return '<img src="'.img_path('attach.png').'" alt="'.LAN_FORUM_3013.'" title="'.LAN_FORUM_3013.'" class="icon S16 action" />'; },
	'IMAGE_post'                => function() { return '<img src="'.img_path('post.png').'" alt="" title="" />'; },
	'IMAGE_post2'               => function() { return '<img src="'.img_path('post2.png').'" alt="" title="" class="icon S16 action" />'; },

// Admin <input> Icons
	'IMAGE_admin_delete'        => function() { return 'src="'.img_path('admin_delete.png').'" alt="'.LAN_DELETE.'" title="'.LAN_DELETE.'" '; },
	'IMAGE_admin_stick'         => function() { return 'src="'.img_path('admin_stick.png').'" alt="'.LAN_FORUM_4011.'" title="'.LAN_FORUM_4011.'" '; },
	'IMAGE_admin_unstick'       => function() { return 'src="'.img_path('admin_unstick.png').'" alt="'.LAN_FORUM_4012.'" title="'.LAN_FORUM_4012.'" '; },
	'IMAGE_admin_lock'          => function() { return 'src="'.img_path('admin_lock.png').'" alt="'.LAN_FORUM_4013.'" title="'.LAN_FORUM_4013.'" '; },
	'IMAGE_admin_unlock'        => function() { return 'src="'.img_path('admin_unlock.png').'" alt="'.LAN_FORUM_4014.'" title="'.LAN_FORUM_4014.'" '; },

// Multi Language Images
	'IMAGE_newthread'           => function() { return '<img src="'.img_path('newthread.png').'" alt="'.LAN_FORUM_2005.'" title="'.LAN_FORUM_2005.'" />'; },
	'IMAGE_reply'               => function() { return '<img src="'.img_path('reply.png').'" alt="'.LAN_FORUM_2006.'" title="'.LAN_FORUM_2006.'" />'; },

	'IMAGE_rank_moderator_image'  => function() { return '<img src="'.img_path('moderator.png', '', 'rank_moderator_image').'" alt="" />'; },
	'IMAGE_rank_main_admin_image' => function() { return '<img src="'.img_path('main_admin.png', '', 'rank_main_admin_image').'" alt="" />'; },
	'IMAGE_rank_admin_image'      => function() { return '<img src="'.img_path('admin.png', '', 'rank_admin_image').'" alt="" />'; },
);

foreach($icons as $forumIcon => $forumIconValue)
{
	if(!defined($forumIcon))
	{
		define($forumIcon, $forumIconValue());
	}
}

unset($icons, $forumIcon, $forumIconValue);
