<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2008 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Docs
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/docs.php,v $
 * $Revision: 1.3 $
 * $Date: 2008-12-16 15:16:09 $
 * $Author: secretr $
 *
*/
require_once("../class2.php");
if (!ADMIN) {
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'docs';
require_once("auth.php");

$i = 1;
$lang = e_LANGUAGE;
if (!$handle = opendir(e_DOCS.e_LANGUAGE."/")) {
	$lang = "English";
	$handle = opendir(e_DOCS."English/");
}

while ($file = readdir($handle)) {
	if ($file != "." && $file != ".." && $file != "CVS") {
		$helplist[$i] = $file;
		$i++;
	}
}
closedir($handle);


if (e_QUERY) {
	$filename = e_DOCS.$lang."/".$helplist[e_QUERY];
	$fd = fopen ($filename, "r");
	$text .= fread ($fd, filesize ($filename));
	fclose ($fd);

	$text = $tp->toHTML($text, TRUE);
	$text = preg_replace("/Q\>(.*?)A>/si", "<img src='".e_IMAGE_ABS."generic/question.png' class='icon' alt='' /><b>\\1</b>A>", $text);
	$text = str_replace("A>", "<img src='".e_IMAGE."generic/answer.png' class='icon' alt='' />", $text);

	$ns->tablerender(str_replace("_", " ", $helplist[e_QUERY]), $text."<br />");
	unset($text);
}

require_once("footer.php");
?>