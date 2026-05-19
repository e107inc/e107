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
$phpinfo = ob_get_clean();

// Keep only the <body>…</body> payload so the admin <head>/<html> shell isn't duplicated.
if(preg_match('#<body\b[^>]*>(.*?)</body>#is', $phpinfo, $m))
{
	$phpinfo = $m[1];
}

// Drop phpinfo()'s own <style> block so its low-contrast palette can't bleed into the admin layout.
// The hardcoded HTML presentational attributes phpinfo() emits (width, cellpadding, cellspacing,
// align, bgcolor, valign…) map to the lowest-specificity CSS, so any author rule scoped under
// .phpinfo-wrapper overrides them — no attribute-stripping or class-renaming pass needed.
$phpinfo = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $phpinfo);

// Wrap once. .phpinfo-wrapper scopes all CSS to this page and provides a safety overflow-x.
// The inline CSS below is purely STRUCTURAL (layout, spacing, wrapping). The COLOR PALETTE
// for .phpinfo-wrapper lives in each admin theme stylesheet (e.g. modern-light.css,
// modern-dark.css), so phpinfo respects the theme the admin is using.
$phpinfo = '<div class="phpinfo-wrapper">'.$phpinfo.'</div>';

// Structural-only inline CSS: no background-color, no color, no border-color. These are
// theme palette decisions and belong in the admin theme stylesheets.
e107::css('inline', '
.phpinfo-wrapper { max-width: 100%; overflow-x: auto; box-sizing: border-box; }
.phpinfo-wrapper * { box-sizing: border-box; }
.phpinfo-wrapper h1,
.phpinfo-wrapper h2,
.phpinfo-wrapper h3,
.phpinfo-wrapper h4 { margin-top: 1rem; }
.phpinfo-wrapper img { max-width: 100%; height: auto; }
.phpinfo-wrapper table {
    width: 100%; max-width: 100%;
    border-collapse: collapse; margin-bottom: 1rem;
}
.phpinfo-wrapper td,
.phpinfo-wrapper th {
    padding: 6px 10px; border-width: 1px; border-style: solid;
    text-align: left; vertical-align: top;
    word-break: break-word; overflow-wrap: anywhere;
}
.phpinfo-wrapper tr.h th { text-align: center; font-weight: bold; }
.phpinfo-wrapper td.e   { font-weight: bold; }
.phpinfo-wrapper td.v   { font-family: Menlo, Consolas, "Liberation Mono", monospace; }
/* In multi-column rows (label + value), keep the label cell on a single line so a long value
   in td.v cannot squeeze the label into a 1-char-wide column. Single-cell rows (1-column
   sub-tables like PHP QA Team / License / Documentation) keep wrapping so they stay inside
   the admin column width. */
.phpinfo-wrapper tr > td.e:not(:only-child) { white-space: nowrap; }
');


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
            $srch = '<tr><td class="e">'.$risk.'</td><td class="v">';
            $repl = '<tr><td class="e">'.$risk.'</td><td title="'.e107::getParser()->toAttribute($diz).'" class="v alert alert-danger">';
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


if(deftrue('e_DEBUG'))
{
	$mes->addDebug("Session ID: ".session_id());
}

e107::getRender()->tablerender("PHPInfo", $mes->render(). $phpinfo);
require_once("footer.php");

