<?php

if (!defined('e107_INIT')) { exit; }

e107::lan('core','contact');

$head = '<form id="contact-menu" action="'.e_HTTP.'contact.php" method="post" >';


//XXX Template must conform to Bootstrap specs: http://twitter.github.com/bootstrap/base-css.html#forms
//TODO Security Image. 


$foot = '</form>'; 

$template = e107::getCoreTemplate('contact','menu');
$contact_shortcodes = e107::getScBatch('contact');                
$text = e107::getParser()->parseTemplate($head. $template . $foot, true, $contact_shortcodes);


e107::getRender()->tablerender(defset('LAN_CONTACT_00', 'Contact Us'), $text, 'contact-menu');


