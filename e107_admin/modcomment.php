<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * Exists only for BC. 
 */

require_once(__DIR__.'/../class2.php');

if (!getperms("B")) 
{
	e107::redirect('admin');
	exit;
}


$tmp	= explode(".", e_QUERY);
$table	= $tmp[0];
$id		= (int) varset($tmp[1]);
$editid	= (int) varset($tmp[2]);

$url = e_ADMIN_ABS."comment.php?searchquery=".$id."&filter_options=comment_type__".e107::getComment()->getCommentType($table);

e107::getRedirect()->go($url);


