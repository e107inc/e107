<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once(__DIR__.'/../class2.php');

if(!getperms("0"))
{
	e107::redirect('admin');
    exit;
}

e107::coreLan('phpinfo', true);

$e_sub_cat = 'phpinfo';
require_once("auth.php");

ob_start();
phpinfo();
$phpinfo = ob_get_contents();

$phpinfo = preg_replace("#^.*<body>#is", "", $phpinfo);
// Strip any leftover <style> block emitted by phpinfo() so it cannot bleed into the admin layout.
$phpinfo = preg_replace('#<style[^>]*>.*?</style>#is', '', $phpinfo);
$phpinfo = str_replace("font","span",$phpinfo);
$phpinfo = str_replace("</body></html>","",$phpinfo);
$phpinfo = str_replace('border="0"','',$phpinfo);
// Remove hard-coded width/cellpadding/cellspacing attributes that cause horizontal overflow.
$phpinfo = preg_replace('/\s(width|cellpadding|cellspacing|align)="[^"]*"/i', '', $phpinfo);
//$phpinfo = str_replace('<table ','<table class="table table-striped adminlist" ',$phpinfo);
$phpinfo = str_replace('name=','id=',$phpinfo);
$phpinfo = str_replace('class="e"','class="forumheader2 text-left"',$phpinfo);
$phpinfo = str_replace('class="v"','class="forumheader3 text-left"',$phpinfo);
$phpinfo = str_replace('class="v"','class="forumheader3 text-left"',$phpinfo);
$phpinfo = str_replace('class="h"','class="fcaption"',$phpinfo);
$phpinfo = preg_replace('/<table[^>]*>/i', '<table class="table table-striped table-bordered adminlist phpinfo-table"><colgroup><col style="width:30%" /><col style="width:auto" /></colgroup>', $phpinfo);

// Wrap each rendered table in a Bootstrap responsive container so wide rows scroll instead of overflowing.
$phpinfo = preg_replace('#(<table class="table table-striped table-bordered adminlist phpinfo-table">)#', '<div class="table-responsive">$1', $phpinfo);
$phpinfo = str_replace('</table>', '</table></div>', $phpinfo);

// Local CSS to keep long values from breaking the layout.
e107::css('inline', '
.phpinfo-wrapper { max-width: 100%; overflow-x: hidden; }
.phpinfo-wrapper .table-responsive { margin-bottom: 1.25rem; }
.phpinfo-wrapper table.phpinfo-table { width: 100%; table-layout: fixed; word-wrap: break-word; }
.phpinfo-wrapper table.phpinfo-table td,
.phpinfo-wrapper table.phpinfo-table th { word-break: break-word; overflow-wrap: anywhere; vertical-align: top; }
.phpinfo-wrapper h1, .phpinfo-wrapper h2 { font-size: 1.25rem; margin-top: 1rem; }
.phpinfo-wrapper img { max-width: 100%; height: auto; }
');

$phpinfo = '<div class="phpinfo-wrapper">' . $phpinfo . '</div>';


$mes = e107::getMessage();

$extensionCheck = array(
			'curl'      => array('label'=> 'Curl Library',          'status' => function_exists('curl_version'),        'url'=> 'http://php.net/manual/en/book.curl.php'),
			'exif'      => array('label'=> "EXIF Extension",        'status' => function_exists('exif_imagetype'),      'url'=> 'http://php.net/manual/en/book.exif.php'),
			'fileinfo'  => array('label'=> "FileInfo. Extension",   'status' => extension_loaded('fileinfo'),      'url'=> 'https://www.php.net/manual/en/book.fileinfo'),
			'gd'        => array('label'=> 'GD Library',            'status' => function_exists('gd_info'),             'url'=> 'http://php.net/manual/en/book.image.php'),
			'mb'        => array('label'=> 'MB String Library',     'status' => extension_loaded('mbstring'),       'url'=> 'http://php.net/manual/en/book.mbstring.php'),
			'pdo'       => array('label'=> "PDO (MySQL)",           'status' => extension_loaded('pdo_mysql'),          'url'=> 'https://php.net/manual/en/book.pdo.php'),
			'xml'       => array('label'=> "XML Extension",         'status' => function_exists('utf8_encode') && class_exists('DOMDocument', false),  'url'=> 'http://php.net/manual/en/ref.xml.php'),
);

$languages = e107::getLanguage()->installed('abbr');

if(isset($languages['en']))
{
	unset($languages['en']);
}

if(!empty($languages)) // non-english languages present.
{
	$extensionCheck['intl'] = array('label'=> 'Internationalization Functions',      'status' => extension_loaded('intl'),        'url'=> 'https://www.php.net/manual/en/book.intl.php');
}


foreach($extensionCheck as $var)
{
	if($var['status'] !== true)
	{
		$erTitle    = deftrue('PHP_LAN_7', "PHP Configuration Issue(s) Found:");
		$def        = deftrue('PHP_LAN_8', "[x] is missing from the PHP configuration and needs to be installed.");
		$message    = e107::getParser()->lanVars($def,$var['label'],true);

		$mes->setIcon('fa-hand-stop-o', E_MESSAGE_ERROR)->setTitle($erTitle,E_MESSAGE_ERROR)->addError($message." <a class='alert-link' href='".$var['url']."' target='_blank' title=\"".$var['url']."\">".ADMIN_INFO_ICON."</a> ");

	}

}



$security_risks = array(
    "allow_url_fopen"   => PHP_LAN_1,
    "allow_url_include" => PHP_LAN_2,
    "display_errors"    => PHP_LAN_3,
    "expose_php"        => PHP_LAN_4,
    "register_globals"  => PHP_LAN_5
    );

    foreach($security_risks as $risk=>$diz)
    {
        if(ini_get($risk))
        {
            $srch = '<tr><td class="forumheader2 text-left">'.$risk.'</td><td class="forumheader3">';
            $repl = '<tr><td class="forumheader2 text-left">'.$risk.'</td><td  title="'.e107::getParser()->toAttribute($diz).'" class="forumheader3 alert alert-danger">';
            $phpinfo = str_replace($srch,$repl,$phpinfo);   
            $mes->addWarning("<b>".$risk."</b>: ".$diz);
        }   
    }

	$sessionSaveMethod = ini_get('session.save_handler');

	if($sessionSavePath = ini_get('session.save_path'))
	{
		if(!is_writable($sessionSavePath) && $sessionSaveMethod === 'files')
		{
			$mes->addError(e107::getParser()->toHTML(PHP_LAN_6, true));	
		}
	}


// $phpinfo = preg_replace("#^.*<body>#is", "", $phpinfo);
ob_end_clean();



if(deftrue('e_DEBUG'))
{
	$mes->addDebug("Session ID: ".session_id());
}

e107::getRender()->tablerender("PHPInfo", $mes->render(). $phpinfo);
require_once("footer.php");

