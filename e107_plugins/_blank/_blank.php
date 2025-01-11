<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Blank Plugin
 *
*/
if (!defined('e107_INIT'))
{
	require_once(__DIR__.'/../../class2.php');
}



class _blank_front
{

	function __construct()
	{
		e107::js('_blank','js/my.js','jquery');	// Load Plugin javascript and include jQuery framework
		e107::css('_blank','css/my.css');		// load css file
		e107::lan('_blank'); 					// load language file ie. e107_plugins/_blank/languages/English.php
		e107::meta('keywords','some words');	// add meta data to <HEAD>

	}


	public function run()
	{

		$sql = e107::getDb(); 					// mysql class object
		$tp = e107::getParser(); 				// parser for converting to HTML and parsing templates etc.
		$frm = e107::getForm(); 				// Form element class.
		$ns = e107::getRender();				// render in theme box.


		$this->setBreadcrumb();                 // custom method (see below) - define breadcrumb.

		$text = '';


	//	$sc = e107::getScBatch('_blank',true, '_blank');
	//	$template = e107::getTemplate('_blank','_blank','default');
	//	$text = $tp->parseTemplate($template['start'],true, $sc);

		if($rows = $sql->retrieve('blank','*',false,true)) 	// combined select and fetch function - returns an array.
		{
			// print_a($rows);
			foreach($rows as $key=>$value)		// loop throug
			{

			//	$sc->setVars($value); // if shortcodes are enabled.
			//	$text .= $tp->parseTemplate($template['item'],true, $sc);

				$text .=  $tp->toHTML(varset($value['blank_type']))."<br />";
			}

		//	$text .= $tp->parseTemplate($template['end'],true, $sc);

			$ns->tablerender("My Caption", $text);

		}

		echo $this->renderComments();

	}



	/**
	 * Custom function to calculate breadcrumb for the current page.
	 * @return null
	 */
	private function setBreadcrumb()
	{
		$breadcrumb = array();

		$breadcrumb[] = array('text' => 'Blank Plugin', 'url' => e107::url('_blank', 'index')); // @see e_url.php

		if(!empty($_GET['other'])) // @see e_url 'other' redirect.
		{
			$breadcrumb[] = array('text' => 'Other', 'url' => null); // Use null to omit link for current page.
		}

		e107::breadcrumb($breadcrumb); // assign values to the Magic Shortcode:  {---BREADCRUMB---}

		return null;
	}




	private function renderComments()
	{

		/**
		 * Returns a rendered commenting area. (html) v2.x
		 * This is the only method a plugin developer should require in order to include user comments.
		 * @param string $plugin - directory of the plugin that will own these comments.
		 * @param int $id - unique id for this page/item. Usually the primary ID of your plugin's database table.
		 * @param string $subject
		 * @param bool|false $rate true = will rendered rating buttons, false will not.
		 * @return null|string
		 */

		$plugin = '_blank';
		$id     = 3;
		$subject = 'My blank item subject';
		$rate   = true;

		return e107::getComment()->render($plugin, $id, $subject, $rate);

	}



}


$_blankFront = new _blank_front;
require_once(HEADERF); 					// render the header (everything before the main content area)
$_blankFront->run();
require_once(FOOTERF);					// render the footer (everything after the main content area)


// For a more elaborate plugin - please see e107_plugins/gallery

