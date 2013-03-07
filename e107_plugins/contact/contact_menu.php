<?php

if (!defined('e107_INIT')) { exit; }

e107::lan('core','contact');

$head = '<form id="contact-menu" action="'.e_BASE.'contact.php" method="post" >';


//XXX Template must conform to Bootstrap specs: http://twitter.github.com/bootstrap/base-css.html#forms
//TODO Security Image. 

$template = '
	<div>
		<div class="control-group">
			<label >Name</label>
   			 {CONTACT_NAME}
		 </div>
		 
		<div class="control-group">
			<label class="control-label" for="contactEmail">Email</label>
				{CONTACT_EMAIL}
		</div>
		<div class="control-group">
			<label>Comments</label>
			{CONTACT_BODY=rows=5&cols=50}							
		</div>
		{CONTACT_SUBMIT_BUTTON}
	</div>       
  ';
 
$foot = '</form>'; 


$contact_shortcodes = e107::getScBatch('contact');                
$text = $tp->parseTemplate($head. $template . $foot, true, $contact_shortcodes);


$ns->tablerender("Contact Us", $text, 'contact-menu');


?>