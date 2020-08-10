<?php

if (!defined('e107_INIT')) { exit; }

e107::lan('core','contact');

$head = '<form id="contact-menu" action="'.e_HTTP.'contact.php" method="post" >';


//XXX Template must conform to Bootstrap specs: http://twitter.github.com/bootstrap/base-css.html#forms
//TODO Security Image. 


$foot = '</form>'; 

$template = e107::getCoreTemplate('contact','menu');
$contact_shortcodes = e107::getScBatch('contact');                
$text = $tp->parseTemplate($head. $template . $foot, true, $contact_shortcodes);


$ns->tablerender(LANCONTACT_00, $text, 'contact-menu');


