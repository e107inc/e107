<?php
// $Id: contact_template.php,v 1.5 2006-07-27 01:42:22 e107coders Exp $

if (!defined('e107_INIT')) { exit; }
if (!defined("USER_WIDTH")){ define("USER_WIDTH","width:97%"); }

if(!$CONTACT_INFO){
  $CONTACT_INFO = "
	<table style='".USER_WIDTH."' cellpadding='1' cellspacing='7'>
	<tr>
		<td>{SITECONTACTINFO}
		<br />
		</td>
	</tr>
	</table>";
}

$sc_style['CONTACT_EMAIL_COPY']['pre'] = "<tr><td>";
$sc_style['CONTACT_EMAIL_COPY']['post'] = LANCONTACT_07."</td></tr>";

$sc_style['CONTACT_PERSON']['pre'] = "<tr><td>".LANCONTACT_14."<br />   ";
$sc_style['CONTACT_PERSON']['post'] = "</td></tr>";

if(!$CONTACT_FORM){
  $CONTACT_FORM = "
	<form action='".e_SELF."' method='post' id='contactForm' >
	<table style='".USER_WIDTH."' cellpadding='1' cellspacing='7'>
	{CONTACT_PERSON}
	<tr><td>".LANCONTACT_03."<br />
	<input type='text' name='author_name' size='30' class='tbox' value='' />
	</td></tr>
	<tr><td>".LANCONTACT_04."<br />
	<input type='text' name='email_send' size='30' class='tbox' value='".USEREMAIL."' />
	</td></tr>
	<tr><td>
	".LANCONTACT_05."<br />
	<input type='text' name='subject' size='30' class='tbox' value='' />
	</td></tr>
	{CONTACT_EMAIL_COPY}
	<tr><td>
    ".LANCONTACT_06."<br />
	<textarea cols='50' rows='10' name='body' class='tbox'></textarea>
	</td></tr>
	<tr><td>
	<input type='submit' name='send-contactus' value=\"".LANCONTACT_08."\" class='button' />
	</td></tr>
	</table>
	</form>";
}


?>