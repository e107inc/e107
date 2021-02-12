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

// Usage: sublink_type[x]['title'].
//  x should be the same as the plugin folder.

e107::lan('forum', "admin", true);
//include_lan(e_PLUGIN.'forum/languages/'.e_LANGUAGE.'/lan_forum_admin.php'); // FIXME needs changing after forum lan rewrite

$sublink_type['forum']['title'] = FORLAN_155; // "News Categories"; // FIXME needs changing after forum lan rewrite
$sublink_type['forum']['table'] = 'forum';
$sublink_type['forum']['query'] = "forum_parent !='0' ORDER BY forum_order ASC";
$sublink_type['forum']['url'] = "{e_PLUGIN}forum/forum_viewforum.php?#";
$sublink_type['forum']['fieldid'] = 'forum_id';
$sublink_type['forum']['fieldname'] = 'forum_name';
$sublink_type['forum']['fielddiz'] = 'forum_description';
$sublink_type['forum']['sef'] = 'forum/forum';


