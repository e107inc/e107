<?php

if (!defined('e107_INIT'))
{
	require_once("class2.php");
	define('e_IFRAME', true);

	$css = "body 				{ background-color: rgb(55, 55, 55); padding:50px; color: white; text-align: left; font-size:16px; line-height:1.5em; font-weight:normal; font-family:Arial, Helvetica, sans-serif; }
			p 					{ margin:0px 5px 10px 5px; }
			a					{ color:#F6931E; text-decoration:none; }
			a:hover				{ color:#fdce8a; text-decoration:none; }
			.bold				{ font-weight:bold; }
			.center				{ text-align:center; }
			.wrapper			{ width:600px;  margin:0px auto 0px 0px; margin-left: auto; margin-right: auto; padding-bottom:10px;  }
			.wrapper-middle		{ min-height:389px;  }
			.logo				{ margin-bottom:20px }
			.credits-content	{ padding:20px 40px;}
        	.copyright			{ margin-top:30px}
        	.well               { min-height: 20px; padding: 19px; border: 1px solid #0f0f0f; border-radius: 6px; -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.05);
								  box-shadow: inset 0 1px 1px rgba(0,0,0,.05); color: #eee; background-color: #222; 
	}  
      ";

	e107::css('inline',$css);
	require_once(HEADERF);
}


 $text ='<div class="wrapper">
        	<div class="wrapper-middle">
                <div class="well credits-content">
                	<img class="logo" src="'.e_IMAGE_ABS.'admin_images/credits_logo.png" alt="e107 Logo" />
                	<div class="wrapper-text">
	                    <h4 class="text-info">Developers</h4>
	                    <p>
	                        <a target="_blank" title="View Github profile" href="https://github.com/CaMer0n">CaMer0n</a>, <a target="_blank" title="View Github profile"  href="https://github.com/Moc">Moc</a>, <a target="_blank" title="View Github profile" href="https://github.com/Deltik">Deltik</a>.<br />
	                        A complete list of the past and present contributors <a target="_blank" title="View all contributors on Github" href="https://github.com/e107inc/e107/graphs/contributors">can be found here</a>. 
	                    </p>
				
	                     <h4 class="text-info">Third Party Code</h4>
	                    <p>
	                        jQuery, Twitter Bootstrap, FontAwesome, HybridAuth, PhpMailer, Intervention, Minify, MagpieRSS, PCLZip, PCLTar, TinyMCE, Nuvolo Icons, TCPDF, PHP UTF8
	                    </p>
                   		<h4 class="text-info">Sponsors</h4>
	                    <p>
	                        <a target="_blank" href="https://www.jetbrains.com/?from=e107" title="Visit JetBrains">JetBrains</a>, <a target="_blank" href="https://stemaidinstitute.com" title="Visit Stemaid Institute">Stemaid</a>.<br />
	                    </p>
                    	<div class="copyright">Copyright <a target="_blank" href="https://e107.org/community" title="e107 Team">e107.org</a> 2008-'.date('Y').'.<br />Released under the terms of the GNU GPL License.</div>
               		 </div>
			    </div>
            </div>
        	
		</div>';

e107::getRender()->tablerender("", $text);

if(deftrue('e_IFRAME'))
{
	require_once(FOOTERF);
}
