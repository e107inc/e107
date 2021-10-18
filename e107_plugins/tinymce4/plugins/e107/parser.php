<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2021 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!isset($_POST['content']) || !isset($_POST['mode'])) return;

require_once(__DIR__."/../../../../class2.php");

/** @var tinymce4_parse $hookObj */
$hookObj = e107::getAddon('tinymce4', 'e_parse');
switch ($_POST['mode'])
{
	case 'tohtml':
		echo $hookObj->toWYSIWYG($_POST['content']);
		break;
	case 'tobbcode':
		echo $hookObj->toDB($_POST['content'], ['type' => 'bbarea']);
		break;
	default:
		echo '';
}