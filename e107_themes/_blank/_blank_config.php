<?php

// Dummy Theme Configuration File.

function _blank_process()
{
	global $theme_pref;
	$theme_pref['example'] = $_POST['_blank_example'];
	$theme_pref['example2'] = $_POST['_blank_example2'];
	save_prefs('theme');
}


function _blank_config()
{
	global $theme_pref;

	$var[0]['caption'] = "Sample configuration field";
	$var[0]['html'] = "<input type='text' name='_blank_example' value='".$theme_pref['_blank_example']."' />";

	$var[1]['caption'] = "Another Example";
	$var[1]['html'] = "<input type='text' name='_blank_example2' value='".$theme_pref['_blank_example2']."' />";

	return $var;
}

function _blank_help()
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


?>