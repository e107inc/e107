<?php

if (!defined('e107_INIT')) { exit; }

// Dummy Theme Configuration File.
class theme_bootstrap implements e_theme_config
{
	
	function process()
	{
		$pref = e107::getConfig();
		
		$theme_pref = array();
		$theme_pref['example'] = $_POST['_blank_example'];
		$theme_pref['fb_tabs_cols'] = intval($_POST['fb_tabs_cols']);

		$pref->set('sitetheme_pref', $theme_pref);
		return $pref->dataHasChanged();
	}


	function config()
	{
		$var[0]['caption'] = "Sample configuration field";
		$var[0]['html'] = "<input type='text' name='boostrap_example' value='".e107::getThemePref('example', 'default')."' />";

		$var[1]['caption'] = "Featurebox Tab Category - number of items per tab";
		$var[1]['html'] = "<input type='text' name='fb_tabs_cols' value='".e107::getThemePref('fb_tabs_cols', 1)."' />";
		return $var;
	}
	

	function help()
	{
		$text = "Need reasons to love <a href='http://twitter.github.com/bootstrap'>Bootstrap</a>? Look no further.
<h3>By nerds, for nerds.</h3>Built at Twitter by @mdo and @fat, Bootstrap utilizes LESS CSS, is compiled via Node, and is managed through GitHub to help nerds do awesome stuff on the web.
<h3>Made for everyone.</h3>Bootstrap was made to not only look and behave great in the latest desktop browsers (as well as IE7!), but in tablet and smartphone browsers via responsive CSS as well.
<h3>Packed with features.</h3>A 12-column responsive grid, dozens of components, JavaScript plugins, typography, form controls, and even a web-based Customizer to make Bootstrap your own.
		";
		
		return $text;
		
		
		
	 	return "
			<div class='block-text'>
	
	
			 	<a href='http://e107.org'>All the HTML that you want</a>.<br /><br />
	
	
		    Mauris sit amet arcu arcu. Curabitur ultrices, felis ac sagittis elementum, justo dolor posuere eros, eu sollicitudin eros mi nec leo. Quisque sapien justo, ultricies at sollicitudin non; rhoncus vel nisi. Fusce egestas orci a diam vestibulum ut gravida ipsum tristique. Nullam et turpis nibh; eu dapibus ligula. Fusce ornare massa ac ante tincidunt euismod varius augue volutpat? Suspendisse potenti. Morbi eget velit in nulla tristique ultricies suscipit consequat ligula. Integer quis arcu vel sem scelerisque gravida vitae vel tortor! Suspendisse tincidunt scelerisque nibh, quis consectetur mauris varius sit amet! Pellentesque et urna vel est rutrum viverra?
	
			<br /><br />
		    <ul>
				<li>Curabitur ultrices</li>
			    <li>Ollicitudin eros</li>
				<li>Felis ac sagittis</li>
				<li>Quisque sapien</li>
			</ul>
	
	
	
			</div>
		";
	}
}


?>