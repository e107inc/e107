<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/phpinfo.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

require_once("../class2.php");
if (!getperms("0")) {
    header("location:".e_BASE."index.php");
    exit;
}
$e_sub_cat = 'phpinfo';
require_once("auth.php");

ob_start();
phpinfo();
$phpinfo .= ob_get_contents();
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
$phpinfo = str_replace('<table  cellpadding="3" width="600">', '<table class="table table-striped adminlist"><colgroup><col style="width:30%" /><col style="width:auto" /></colgroup>', $phpinfo);

$mes = e107::getMessage();

$security_risks = array(
    "allow_url_fopen"   => 'If you have Curl enabled, you should consider disabling this feature.',
    "allow_url_include" => 'This is a security risk and is not needed by e107.',
    "display_errors"    => 'On a production server, it is better to disable the displaying of errors in the browser.',
    "expose_php"        => 'Disabling this will hide your PHP version from browsers.',
    "register_globals"  => 'This is a security risk and should be disabled.'
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
	
	if($sessionSavePath = ini_get('session.save_path'))
	{
		if(!is_writable($sessionSavePath))
		{
			$mes->addError("<b>session.save_path</b> is not writable! That can cause major issues with your site.");	
		}
	}


// $phpinfo = preg_replace("#^.*<body>#is", "", $phpinfo);
ob_end_clean();
$ns->tablerender("PHPInfo", $mes->render(). $phpinfo);
require_once("footer.php");
?>