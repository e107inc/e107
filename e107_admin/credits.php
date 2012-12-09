<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system

|     Copyright (C) 2008-2012 e107 Inc (e107.org)
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.7/e107_admin/credits.php $
|     $Id: credits.php 12477 2011-12-28 03:12:22Z e107coders $
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");

$css = "body 				{ text-align: left; font-size:13px; line-height:1.5em; font-weight:normal; font-family:Arial, Helvetica, sans-serif; }
			p 					{ margin:0px 5px 10px 5px; }
			h1					{ font-size:20px; color:#134B63; font-weight:normal; line-height:1em; margin:20px 0px 5px 0px; }
			a					{ color:#F6931E; text-decoration:none; }
			a:hover				{ color:#134B63; text-decoration:none; }
			.bold				{ font-weight:bold; }
			.center				{ text-align:center; }
			.wrapper			{ width:800px; height:415px; margin:0px auto 0px 0px; padding-bottom:10px; background-color: #ebeef0; border-radius: 3px; -webkit-border-radius: 3px; -moz-border-radius: 3px; }
			.wrapper-top		{ height:10px; }
			.wrapper-bottom		{ height:6px; }
			.wrapper-middle		{ min-height:389px; background-color:#FDFDFD0; }
			.logo				{  }
			.credits-content	{ padding:160px 20px 20px 20px; background:url(".e_IMAGE_ABS."admin_images/credits_logo.png) no-repeat 50% 30px; }
        	.copyright			{ text-align:center; margin-top:30px}
      ";

e107::css('inline',$css);


require_once(e_ADMIN."auth.php");


 $text ='<div class="wrapper">
        	<div class="wrapper-middle">
                <div class="credits-content">
                    <h1>v2+ Developers</h1>
                    <p>
                        Cameron Hanly, Miroslav Yovchev, Steven Davies,<br />
                        Henk Jongedijk, James Currie, Martin Nicholls,<br /> 
                        Steven Davies, Thom Michelbrink, Tijn Kuyper
                    </p>
                    <h1>3rd Parties</h1>
                    <p>
                        Twitter Bootstrap, MagpieRSS, PCLZip, PCLTar, TinyMCE,<br />
                        Nuvolo Icons, PHPMailer, FPDF, UFPDF, PHP UTF8
                    </p>
                    <div class="copyright">Copyright <a href="http://e107.org/content/About-Us:The-Team" title="e107 Team">e107 Inc.</a> 2008-2012</div>
                </div>
            </div>
        	
		</div>';

$ns->tablerender("",$text);

require_once(e_ADMIN."footer.php");

exit;

?>