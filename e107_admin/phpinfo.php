<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once("../class2.php");

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
$phpinfo = str_replace("font","span",$phpinfo);
$phpinfo = str_replace("</body></html>","",$phpinfo);
$phpinfo = str_replace('border="0"','',$phpinfo);
//$phpinfo = str_replace('<table ','<table class="table table-striped adminlist" ',$phpinfo);
$phpinfo = str_replace('name=','id=',$phpinfo);
$phpinfo = str_replace('class="e"','class="forumheader2 text-left"',$phpinfo);
$phpinfo = str_replace('class="v"','class="forumheader3 text-left"',$phpinfo);
$phpinfo = str_replace('class="v"','class="forumheader3 text-left"',$phpinfo);
$phpinfo = str_replace('class="h"','class="fcaption"',$phpinfo);
$phpinfo = preg_replace('/<table[^>]*>/i', '<table class="table table-striped adminlist"><colgroup><col style="width:30%" /><col style="width:auto" /></colgroup>', $phpinfo);


$mes = e107::getMessage();

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
            $repl = '<tr><td class="forumheader2 text-left">'.$risk.'</td><td  title="'.$tp->toAttribute($diz).'" class="forumheader3 alert alert-danger">';
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

$ns->tablerender("PHPInfo", $mes->render(). $phpinfo);
require_once("footer.php");
?>
