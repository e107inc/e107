<?php
// $Id: contact_template.php,v 1.1 2006-04-24 21:42:06 e107coders Exp $

if (!defined('e107_INIT')) { exit; }

if(!$CONTACT_INFO){
  $CONTACT_INFO = "
	<table style='width:97%' cellpadding='1' cellspacing='7'>
	<tr>
		<td>{SITECONTACTINFO}
		<br />
		</td>
	</tr>
	</table>";
}


if(!$CONTACT_FORM){
  $CONTACT_FORM = "
	<form action='".e_SELF."' method='post' id='contactForm' >
	<input type='hidden' name='username' value=\"".USERNAME."\" />
	<table style='width:97%' cellpadding='1' cellspacing='7'>
	<tr><td>".LANCONTACT_03."<br />
	<input type='text' name='author_name' size='30' class='tbox' value='' />
	</td></tr>
	<tr><td>".LANCONTACT_04."<br />
	<input type='text' name='email_send' size='30' class='tbox' value='' />
	</td></tr>
	<tr><td>
	".LANCONTACT_05."<br />
	<input type='text' name='subject' size='30' class='tbox' value='' />
	</td></tr>
	<tr><td>
    ".LANCONTACT_06."<br />
	<textarea cols='50' rows='10' name='body' class='tbox'></textarea>
	</td></tr>
	<tr><td>
	<input type='checkbox' name='email_copy'  value='1'  />
	".LANCONTACT_07."
	</td></tr>
	<tr><td>
	<input type='submit' name='send-contactus' value=\"".LANCONTACT_08."\" class='button' />
	</td></tr>
	</table>
	</form>";
}


?>