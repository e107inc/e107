<?php

if (!defined('e107_INIT')) { exit; }

// Dummy Theme Configuration File.
class theme__blank implements e_theme_config
{
	function process() // Save posted values from config() fields. 
	{
		$pref = e107::getConfig();
		$tp = e107::getParser();
		
		$theme_pref 					= array();
		$theme_pref['example']			= $tp->toDb($_POST['_blank_example']);
		$theme_pref['example2'] 		= $tp->toDb($_POST['_blank_example2']);

		$pref->set('sitetheme_pref', $theme_pref);
		return $pref->dataHasChanged();
	}

	function config()
	{
		$frm = e107::getForm();
		
		$var[0]['caption'] 	= "Sample configuration field";
		$var[0]['html'] 	= $frm->text('_blank_example', e107::pref('theme', 'example', 'default'));
		$var[0]['help']		= "Example help text for this input field"; 

		$var[1]['caption'] 	= "Sample configuration field 2";
		$var[1]['html'] 	= $frm->text('_blank_example2', e107::pref('theme', 'example2', 'default'));
		
		return $var;
	}

	function help()
	{
	 	return "
			<div class='well'>
		 	<a href='http://e107.org'>All the HTML that you want</a>.<br /><br />
		    Mauris sit amet arcu arcu. Curabitur ultrices, felis ac sagittis elementum, justo dolor posuere eros, eu sollicitudin eros mi nec leo. Quisque sapien justo, ultricies at sollicitudin non; rhoncus vel nisi. Fusce egestas orci a diam vestibulum ut gravida ipsum tristique. Nullam et turpis nibh; eu dapibus ligula. Fusce ornare massa ac ante tincidunt euismod varius augue volutpat? Suspendisse potenti. Morbi eget velit in nulla tristique ultricies suscipit consequat ligula. Integer quis arcu vel sem scelerisque gravida vitae vel tortor! Suspendisse tincidunt scelerisque nibh, quis consectetur mauris varius sit amet! Pellentesque et urna vel est rutrum viverra?
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