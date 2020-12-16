<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Hero menu file.
 *
 */


if (!defined('e107_INIT')) { exit; }

// $sql = e107::getDB(); 				// mysql class object
// $tp = e107::getParser(); 			// parser for converting to HTML and parsing templates etc.
// $frm = e107::getForm(); 				// Form element class.
// $ns = e107::getRender();				// render in theme box.

//require_once("../../class2.php");
// define('e_IFRAME', true);
//require_once(HEADERF);

$text = "";

if(!empty($parm))
{
//	$text .= print_a($parm,true); // e_menu.php form data.
}

$data = e107::getDb()->retrieve('hero','*',"hero_class IN(".USERCLASS_LIST.") ORDER BY hero_order",true);


$sc = e107::getScBatch('hero',true, 'hero');

$template = e107::getTemplate('hero','hero','menu');


$tp = e107::getParser();

$totalSlides = count($data);

$default = array('hero_total_slides'=>$totalSlides);

if(!is_object($sc))
{
	return "Hero shortcodes failed to load";
}

$sc->setVars($default);

$text = $tp->parseTemplate($template['header'],true, $sc);

foreach($data as $k=>$row)
{
	$bullet = e107::unserialize($row['hero_bullets']);
	$row['hero_bullets'] = $bullet;

	$button1 = e107::unserialize($row['hero_button1']);
	$row['hero_button1'] = $button1;

	$button2 = e107::unserialize($row['hero_button2']);
	$row['hero_button2'] = $button2;

	$row['hero_slide_active'] = ($k == 0) ? 'active' : '';
	$row['hero_total_slides'] = $totalSlides;

	$sc->setVars($row);

	$text .= $tp->parseTemplate($template['start'],true,$sc);

	foreach($row['hero_bullets'] as $cnt=>$row2)
	{
		if(empty($row2['text']))
		{
			continue;
		}

		$sc->count = $cnt;


		$text .= $tp->parseTemplate($template['item'],true,$sc);

	}


	$text .= $tp->parseTemplate($template['end'],true,$sc);



}

$text .= $tp->parseTemplate($template['footer'], true, $sc);

e107::getRender()->tablerender(null, $text, 'hero-menu');

/*
$arr = array(
	0 => array('caption'=>'Slide 1', 'text'=>'<div class="text-center">Slide 1 text</div>'),
	1 => array('caption'=> 'Slide 2', 'text'=> '<div class="text-center">Slide 2 text</div>')
);

$text = e107::getForm()->carousel('my-carousel',$arr);

e107::getRender()->tablerender("Core", print_a($text,true), 'hero-menu');*/

//require_once(FOOTERF);

