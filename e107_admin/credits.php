<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Credits
 *
*/

require_once("../class2.php");

$css = "body 				{ text-align: left; font-size:13px; line-height:1.5em; font-weight:normal; font-family:Arial, Helvetica, sans-serif; }
			p 					{ margin:0px 5px 10px 5px; }
		
			a					{ color:#F6931E; text-decoration:none; }
			a:hover				{ color:#134B63; text-decoration:none; }
			.bold				{ font-weight:bold; }
			.center				{ text-align:center; }
			.wrapper			{ width:600px;  margin:0px auto 0px 0px; padding-bottom:10px;  }
			.wrapper-middle		{ min-height:389px;  }
			.wrapper-text		{  }
			.logo				{ margin-bottom:20px }
			.credits-content	{ padding:20px 40px;}
        	.copyright			{  margin-top:30px}
      ";

e107::css('inline',$css);


require_once(e_ADMIN."auth.php");


 $text .='<div class="wrapper">
        	<div class="wrapper-middle">
                <div class="well credits-content">
                	<img class="logo" src="'.e_IMAGE_ABS.'admin_images/credits_logo.png" alt="e107 Logo" />
                	<div class="wrapper-text">
	                    <h4 class="text-info">Developers</h4>
	                    <p>
	                        Senior Developers: Cameron Hanly, Miroslav Yovchev, Steven Davies <br />
	                        Junior Developers: Tijn Kuyper, Henk Jongedijk <br />
	                        Early Developers: Thom Michelbrink, James Currie, Martin Nicholls 
	                    </p>
	                     <h4 class="text-info">3rd Party</h4>
	                    <p>
	                        Twitter Bootstrap, MagpieRSS, PCLZip, PCLTar, TinyMCE,<br />
	                        Nuvolo Icons, PHPMailer, TCPDF, PHP UTF8
	                    </p>
                   
                    	<div class="copyright">Copyright <a target="_blank" href="http://e107.org/community" title="e107 Team">e107 Inc.</a> 2008-2017</div>
               		 </div>
			    </div>
            </div>
        	
		</div>';

$ns->tablerender("",$text);

require_once(e_ADMIN."footer.php");

exit;

?>
