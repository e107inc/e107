<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2017 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Printer Friendly
 *
*/

require_once("class2.php");
//include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/lan_'.e_PAGE);

e107::coreLan('print');

$qs = explode(".", e_QUERY,2);
if ($qs[0] == "") {
	header("location:".e_BASE."index.php");
	 exit;
}

$CSS = <<<CSS

	body { background: #fff; color: #000 }

@media print {

	img {
        display: block;
    }
    img, table, ul, ol, .code-snippet {
        page-break-inside: avoid;
        page-break-before: auto;
        page-break-after: auto;
    }

  a[href]:after {
    content: none;
  }

}
CSS;


e107::css('inline',$CSS);

define('e_IFRAME', true); 

$source = preg_replace('/[^\w\d_\:]/',"", $qs[0]);
$parms = varset($qs[1],'');
unset($qs);

if(strpos($source,'plugin:') !== FALSE)
{
	$plugin = substr($source,7);

	if(file_exists(e_PLUGIN.$plugin."/e_emailprint.php"))
	{
		include_once(e_PLUGIN.$plugin."/e_emailprint.php");
		$print_text = print_item($parms);
//		define("e_PAGETITLE", $plugin);
	}
	else
	{
		echo LAN_FILE_NOT_FOUND;
		exit;
	}
}
else
{
	//$con = new convert;
//	$id = intval($parms);
	/** @var e_news_item $nws */
	$nws = e107::getObject('e_news_item');
	$row = $nws->load($parms)->toArray();


	$newsUrl = e107::getUrl()->create('news/view/item', $row, 'full=1');
    $tmp = e107::getTemplate('news', 'news', 'view');

    if(empty($tmp))
    {
        $newsViewTemplate = !empty($row['news_template']) ? $row['news_template'] : 'default';
        $tmp = e107::getTemplate('news', 'news_view', $newsViewTemplate);
    }

    $title = $tp->toText($row['news_title']);
    define('e_PAGETITLE', '[print] '. $title);
    e107::meta('robots', 'noindex');

	$template = $tmp['item'];
	unset($tmp);
//	ob_start();
	require_once(e_HANDLER."news_class.php");
	$ix = new news;

	$print_text = $ix->render_newsitem($row, 'return', '', $template, null);
	//$print_text = ob_get_flush();

	$print_text .= "<br /><br /><hr />".
	LAN_PRINT_303."<b>".SITENAME."</b>
	<br />
	".$newsUrl."
	";
	
	

}


if(defined("TEXTDIRECTION") && TEXTDIRECTION == "rtl"){
	$align = 'right';
}else{
	$align = 'left';
}

// Header down here to give us a chance to set a page title
require_once(HEADERF);

//temporary solution - object of future cahges
if(is_readable(THEME.'print_template.php'))
{
	$PRINT_TEMPLATE = '';
	include_once(THEME.'print_template.php');
	echo $tp->parseTemplate($PRINT_TEMPLATE);
}
else 
{
	echo "
		<div style='background-color:white'>
		<div style='text-align:".$align."'>".$tp->parseTemplate("{LOGO: h=100}", TRUE)."</div><hr />
		<div style='text-align:".$align."'>".$print_text."</div><br /><br />
		<form action='#'><div class='hidden-print' style='text-align:center'><input class='btn btn-primary ' type='button' value='".LAN_PRINT_307."' onclick='window.print()' /></div></form></div>";
}
require_once(FOOTERF);

?>
