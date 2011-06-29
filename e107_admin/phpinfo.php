<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
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
$phpinfo = str_replace('border="0"','class="fborder"',$phpinfo);
$phpinfo = str_replace('name=','id=',$phpinfo);
$phpinfo = str_replace('class="e"','class="forumheader2"',$phpinfo);
$phpinfo = str_replace('class="v"','class="forumheader3"',$phpinfo);
$phpinfo = str_replace('class="v"','class="forumheader3"',$phpinfo);
$phpinfo = str_replace('class="h"','class="fcaption"',$phpinfo);



$security_risks = array(
	"allow_url_fopen"	=> 'If you have Curl enabled, you should consider disabling this feature.',
	"allow_url_include"	=> 'This is a security risk and is not needed by e107.',
	"display_errors"	=> 'On a production server, it is better to disable the displaying of errors in the browser.',
	"expose_php"		=> 'Disabling this will hide your PHP version from browsers.',
	"register_globals"	=> 'This is a security risk and should be disabled.'
	);

	foreach($security_risks as $risk=>$diz)
	{
		if(ini_get($risk))
		{
			$srch = '<tr><td class="forumheader2">'.$risk.'</td><td class="forumheader3">';
			$repl = '<tr><td class="forumheader2">'.$risk.'</td><td title="'.$diz.'" class="forumheader3" style="background-color:red">';
			$phpinfo = str_replace($srch,$repl,$phpinfo);	
		}	
	}


// $phpinfo = preg_replace("#^.*<body>#is", "", $phpinfo);
ob_end_clean();
$ns->tablerender("PHPInfo", $phpinfo);
require_once("footer.php");
?>