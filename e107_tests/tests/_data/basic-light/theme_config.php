<?php

if (!defined('e107_INIT')) { exit; }

// Theme Configuration File.
class theme_basic_light implements e_theme_config
{
	
	function process()
	{
		$pref = e107::getConfig();
		
		$theme_pref = array();

		$pref->set('sitetheme_pref', $theme_pref);
		return $pref->dataHasChanged();
	}


	function config()
	{

		return $var;
	}
	

	function help()
	{
		$text = '
<div class="container-fluid">
    <header>
        <h2 class="text-left" style="margin-top: 40px; margin-bottom: 40px">BASIC LIGHT</h2>
        <h5>e107 Bootstrap 3 Theme v.1 - March 2016 - for e107 v.2.</h5>
        <hr />
    </header>
    <div class="row-fluid row">
		<div class="span6 alpha col-sm-6">
			<h4>License</h4>
            <p>The MIT License (MIT) <br />

            Copyright (c) 2013 Thomas Park <br />

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions: <br />

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software. <br />

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.</p>
		</div>
		<div class="span6 omega col-sm-6">
			<h4>Installation</h4>
			<p><b> Very Important:</b> before to upload the theme, download and install latest e107 v.2 from: <b>https://github.com/e107inc/e107</b> 
            <br /> Extract the theme from zip archive. If you use Winrar click on "Extract here". In the theme folder you must have files and folders like theme.php, theme.xml ... and folders like images, linguages ...
            <br /> After latest e107 v.2 installation, uplod the theme folder on your server in "e107_themes" folder via FTP. If the theme do not works, you have a corupted file, so upload again the theme.</p> 
		</div>
    </div> 
    <div class="row-fluid row">
		<div class="span4 col-sm-4">
			<h4>Theme Javascript Framework</h4>
			<p>The theme use jQuery but it is not loaded by the theme. To load it you need to go in Admin Area > Preferences > Javascript Faramework and set <b>JQUERY (local) to AUTO and PROTOTYPE (local) to DISABLED</b>.</p>
		</div> 
		<div class="span4 omega col-sm-4">
			<h4>Theme Compatibility</h4>
			<p>This theme is compatible with e107 v.2 and up and was tested in IE10+11, FF and Chrome latest versions. The theme use HTML5, CSS3, jQuery and is not working OK in old browsers with not support for HTML5 and CSS3 or javascript.</p>
            <p>For more IE compatibility you can add in Admin Area > Meta Tags this meta tag:
            <div class="alert alert-success">
            &#60;meta http-equiv="X-UA-Compatible" content="IE=edge"&#62;&#60;/meta&#62;
            </div></p>
		</div>
		<div class="span4 alpha col-sm-4">
			<h4>Premium and Custom Themes</h4>
			<p>If you want more complex themes or an unique looking theme for your site please visit <a href="http://www.manatwork.info/">Man at Work</a> website where you can buy the best Premium Themes or you can order your best Custom Unique Theme for e107.</p>
		</div>     
    </div>  
</div> 
		';
		
		return $text;

	}
}
?>