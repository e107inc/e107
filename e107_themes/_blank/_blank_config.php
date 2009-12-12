<?php

if (!defined('e107_INIT')) { exit; }

// Dummy Theme Configuration File.
class theme__blank implements e_theme_config
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
		$var[0]['html'] = "<input type='text' name='_blank_example' value='".e107::getThemePref('example', 'default')."' />";

		$var[1]['caption'] = "Featurebox Tab Category - number of items per tab";
		$var[1]['html'] = "<input type='text' name='fb_tabs_cols' value='".e107::getThemePref('fb_tabs_cols', 1)."' />";
		return $var;
	}

	function help()
	{
	 	return "
			<div class='block-text' style='text-align:left;margin-left:auto;margin-right:auto;width:80%'>
	
	
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