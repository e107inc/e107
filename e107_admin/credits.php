<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_admin/credits.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-03-02 13:15:42 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
require_once("auth.php");


$creditsArray = array(
	array(	"name" => "MagpieRSS", 
				"url" => "http://magpierss.sourceforge.net/", 
				"description" => "MagpieRSS provides an XML-based (expat) RSS parser in PHP.", 
				"version" => "0.71.1", 
				"licence" => "GPL, permission granted"
			),
	array(	"name" => "PCLZip", 
				"url" => "http://www.phpconcept.net/pclzip/index.en.php", 
				"description" => "PclZip library offers compression and extraction functions for Zip formatted archives (WinZip, PKZIP).", 
				"version" => "2.3", 
				"licence" => "GPL"
			),
	array(	"name" => "PCLTar", 
				"url" => "http://www.phpconcept.net/pcltar/index.en.php", 
				"description" => "PclTar offer the ability to archive a list of files or directories with or without compression. The archives created by PclTar are readeable by most of gzip/tar applications and by the Windows WinZip application.", 
				"version" => "1.3", 
				"licence" => "GPL"
			),
	array(	"name" => "TinyMCE", 
				"url" => "http://tinymce.moxiecode.com/", 
				"description" => "TinyMCE is a platform independent web based Javascript HTML WYSIWYG editor control released as Open Source under LGPL by Moxiecode Systems AB. It has the ability to convert HTML TEXTAREA fields or other HTML elements to editor instances.", 
				"version" => "1.42", 
				"licence" => "GPL"
			),
	array(	"name" => "Nuvolo Icons", 
				"url" => "http://www.icon-king.com", 
				"description" => "Icons used in e107", 
				"version" => "1.0", 
				"licence" => "GPL"
			),
	array(	"name" => "PHPMailer", 
				"url" => "http://phpmailer.sourceforge.net/", 
				"description" => "Full featured email transfer class for PHP", 
				"version" => "1.72", 
				"licence" => "GPL"
			),
	array(	"name" => "Brainjar DHTML Menu", 
				"url" => "http://www.brainjar.com/dhtml/menubar/", 
				"description" => "Menu system used in Jayya theme", 
				"version" => "0.1", 
				"licence" => "GPL, permission granted"
			),
	);


	

$text = "<div style='text-align: center; margin-left: auto; margin-right: auto;'><table style='width: 90%;' class='fborder'>

<tr>
<td class='forumheader'><b>".CRELAN_2."</b></td>
</tr>\n";

$type = 4;
foreach($creditsArray as $credits)
{
	$text .= "<tr>
	<td style='forumheader".$type."'><br /><a href='".$credits['url']."' rel='external'>".$credits['name']."</a> ".CRELAN_7." ".$credits['version']."<br /></td>
	</tr>
	<tr>
	<td style='forumheader".$type."'>".$credits['description']."</td>
	</tr>\n";
	$type = ($type == 3 ? 4 : 3);
}

$text .= "</table></div>";

$ns->tablerender(CRELAN_1, $text);
	
require_once("footer.php");
?>