<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Docs
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/docs.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
require_once("../class2.php");
if (!ADMIN) {
	header("location:".e_BASE."index.php");
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);


$e_sub_cat = 'docs';
require_once("auth.php");

require_once (e_HANDLER.'file_class.php');
$fl = new e_file();
$doc_fpath = e_DOCS.e_LANGUAGE.'/';
$doc_fpath_alt = e_DOCS.'English/';

$helplist_all = $fl->get_files($doc_fpath_alt);
if(!is_dir($doc_fpath) || $doc_fpath == $doc_fpath_alt)
{
	$helplist = $helplist_all;
}
else
{
	$helplist = $fl->get_files($doc_fpath);
}

//Titles in Admin Area are requested by the community
define('e_PAGETITLE', LAN_DOCS);

if (e_QUERY) {
	$i = intval(e_QUERY) - 1;
	$filename = $doc_fpath.$helplist[$i]['fname'];
	$filename_alt = $doc_fpath_alt.$helplist[$i]['fname'];

	if(is_readable($filename))
		$text = file_get_contents($filename);
	else
		$text = file_get_contents($filename_alt);

	$text = $tp->toHTML($text, TRUE);
	$text = preg_replace('/Q\>(.*?)A>/si', "<img src='".e_IMAGE_ABS."generic/question.png' class='icon' alt='Q' /><strong>\\1</strong>A>", $text);
	$text = str_replace("A>", "<img src='".e_IMAGE_ABS."generic/answer.png' class='icon' alt='A' />", $text);

	$ns->tablerender(LAN_DOCS.' - '.str_replace("_", " ", $helplist[$i]['fname']), $text);
	unset($text);
	require_once("footer.php");
	exit;
}

/*
 * NEW 0.8
 * Show All
 */

$text = '';
$text_h = '';
foreach ($helplist as $key => $helpdata)
{
	$filename = $doc_fpath.$helpdata['fname'];
	$filename_alt = $doc_fpath_alt.vartrue($$helpdata['fname']);

	if(is_readable($filename))
		$tmp = file_get_contents($filename);
	else
		$tmp = file_get_contents($filename_alt);

	//$tmp = $tp->toHTML(trim($tmp), TRUE);
	$tmp = preg_replace('/Q\>(.*?)A>/si', "###QSTART###<div class='qitem'><img src='".e_IMAGE_ABS."generic/question.png' class='icon S16 middle' alt='".LAN_DOCS_QUESTION."' />\\1</div>###QEND###", $tmp);
	$tmp = preg_replace('/###QEND###(.*?)###QSTART###/si', "<div class='aitem'><img src='".e_IMAGE_ABS."generic/answer.png' class='icon S16 middle' alt='".LAN_DOCS_ANSWER."' />\\1</div>", $tmp);
	$tmp = str_replace(array('###QSTART###', '###QEND###'), array('', "<div class='aitem'><img src='".e_IMAGE_ABS."generic/answer.png' class='icon S16 middle' alt='".LAN_DOCS_ANSWER."' />"), $tmp)."</div>";

	$id = 'doc-'.$key;
	$text_h .= "
		<div class='qitem'>".E_16_DOCS." <a href='#{$id}' class='scroll-to'>".str_replace("_", " ", $helpdata['fname'])."</a></div>
	";
	$text .= "
		<div class='docs-item' id='{$id}'>
			<h4>".str_replace("_", " ", $helpdata['fname'])."</h4>
			{$tmp}
			<div class='gotop'><a href='#docs-list' class='scroll-to'>".LAN_DOCS_GOTOP."</a></div>
		</div>";

}

$text_h = "<div id='docs-list'><h4>".LAN_DOCS_SECTIONS."</h4>".$text_h."</div>";
$text = $text_h.$text;

//Allow scroll navigation for bottom sections
$text .= "
	<div id='docs-bottom-nav'><!-- --></div>
";

$ns->tablerender(LAN_DOCS, $text, 'docs');
require_once("footer.php");
?>