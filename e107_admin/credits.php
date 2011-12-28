<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Id$
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
        <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
        <meta http-equiv="content-style-type" content="text/css" />
        <link rel='icon' href='<?php echo e_BASE; ?>favicon.ico' type='image/x-icon' />
		<link rel='shortcut icon' href='<?php echo e_BASE; ?>favicon.ico' type='image/xicon' />
        <title>e107 Content Management System - Credits</title>
		<style type="text/css">
			body 				{ text-align: left; font-size:13px; line-height:1.5em; font-weight:normal; font-family:Arial, Helvetica, sans-serif; background:#134B63 url(<?php echo e_IMAGE_ABS; ?>admin_images/credits_shine.png) no-repeat 50% 0; }
			p 					{ margin:0px 5px 10px 5px; }
			h1					{ font-size:20px; color:#134B63; font-weight:normal; line-height:1em; margin:20px 0px 5px 0px; }
			a					{ color:#F6931E; text-decoration:none; }
			a:hover				{ color:#134B63; text-decoration:none; }
			.bold				{ font-weight:bold; }
			.center				{ text-align:center; }
			.wrapper			{ width:800px; height:415px; margin:100px auto 0px auto; padding-bottom:10px; background-color: #ebeef0; border-radius: 3px; -webkit-border-radius: 3px; -moz-border-radius: 3px; }
			.wrapper-top		{ height:10px; }
			.wrapper-bottom		{ height:6px; }
			.wrapper-middle		{ min-height:389px; background-color:#FDFDFD0; }
			.logo				{  }
			.credits-content	{ padding:160px 20px 20px 20px; background:url(<?php echo e_IMAGE_ABS; ?>admin_images/credits_logo.png) no-repeat 50% 30px; }
        	.copyright			{ text-align:center; margin-top:30px}
        </style>
	</head>
	<body>
    	<div class="wrapper">
        	<div class="wrapper-middle">
                <div class="credits-content">
                    <h1>Developers</h1>
                    <p>
                        Cameron Hanly, Carl Cedergren, Eric Vanderfeesten,<br />
                        Henk Jongedijk, James Currie, Martin Nicholls, Miroslav Yovchev,<br />
                        Pete Holzmann, Steve Dunstan, Steven Davies, Thom Michelbrink, William Moffett
                    </p>
                    <h1>3rd Parties</h1>
                    <p>
                        MagpieRSS, PCLZip, PCLTar, TinyMCE,<br />
                        Nuvolo Icons, PHPMailer, Brainjar DHTML Menu,<br />
                        DHTML / JavaScript Calendar, FPDF, UFPDF, PHP UTF8
                    </p>
                    <div class="copyright">Copyright <a href="http://e107.org/content/About-Us:The-Team" title="e107 Team">e107 Inc.</a> 2008-2012</div>
                </div>
            </div>
        	
		</div>
	</body>
</html>

<?php
exit;

?>