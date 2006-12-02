<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/search/advanced_comment.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:34:04 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$advanced['type']['type'] = 'dropdown';
$advanced['type']['text'] = LAN_SEARCH_57.':';
$advanced['type']['list'][] = array('id' => 'all', 'title' => LAN_SEARCH_58);

$advanced_caption['id'] = 'type';
$advanced_caption['title']['all'] = LAN_SEARCH_59;

foreach ($search_prefs['comments_handlers'] as $h_key => $value) {
	if (check_class($value['class'])) {
		$path = ($value['dir'] == 'core') ? e_HANDLER.'search/comments_'.$h_key.'.php' : e_PLUGIN.$value['dir'].'/search/search_comments.php';
		require_once($path);
		$advanced['type']['list'][] = array('id' => 's_'.$value['id'], 'title' => $comments_title);
		$advanced_caption['title']['s_'.$value['id']] = LAN_SEARCH_60.' '.$comments_title;
	}
}

$advanced['date']['type'] = 'date';
$advanced['date']['text'] = LAN_SEARCH_50.':';

$advanced['author']['type'] = 'author';
$advanced['author']['text'] = LAN_SEARCH_61.':';

?>