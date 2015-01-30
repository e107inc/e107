<?php
// $Id$

if (!defined('e107_INIT')) { exit; }

/*
if(!isset($CONTACT_INFO))
{
	$CONTACT_INFO = "
	<table style='".USER_WIDTH."' cellpadding='1' cellspacing='7'>
	<tr>
		<td>
		{SITECONTACTINFO}
		<br />
		</td>
	</tr>
	</table>";
}
*/

$CONTACT_TEMPLATE['info'] = "

	<div id='contactInfo' >
		<address>{SITECONTACTINFO}</address>
	</div>

";


$CONTACT_TEMPLATE['menu'] =  '
	<div class="contactMenuForm">
		<div class="control-group form-group">
			<label >Name</label>
   			 {CONTACT_NAME}
		 </div>
		 
		<div class="control-group form-group">
			<label class="control-label" for="contactEmail">Email</label>
				{CONTACT_EMAIL}
		</div>
		<div class="control-group form-group">
			<label>Comments</label>
			{CONTACT_BODY=rows=5&cols=30}							
		</div>
		{CONTACT_SUBMIT_BUTTON}
	</div>       
 ';
 
 
	// Option I - new sc style variable and format, global available per shortcode (mode also applied)
	// sc_style is renamed to sc_wrapper and uppercased now - distinguished from the legacy $sc_style variable and compatible with the new template standards, we deprecate $sc_style soon
 
	// $SC_WRAPPER['CONTACT_EMAIL_COPY'] 		= "<tr><td>{---}".LANCONTACT_07."</td></tr>";
	// $SC_WRAPPER['CONTACT_PERSON'] 			= "<tr><td>".LANCONTACT_14."<br />{---}</td></tr>";
	// $SC_WRAPPER['CONTACT_IMAGECODE'] 			= "<tr><td>".LANCONTACT_16."<br />{---}";
	// $SC_WRAPPER['CONTACT_IMAGECODE_INPUT'] 	= "{---}</td></tr>";
 
 	
	// Option II - Wrappers, used ONLY with batch objects, requires explicit wrapper registration
	// In this case (see contact.php) e107::getScBatch('contact')->wrapper('contact/form')
	// Only one Option is used - WRAPPER > SC_STYLE

	$CONTACT_WRAPPER['form']['CONTACT_IMAGECODE'] 			= "<tr><td>".LANCONTACT_16."<br />{---}";
	$CONTACT_WRAPPER['form']['CONTACT_IMAGECODE_INPUT'] 	= "{---}</td></tr>";
	$CONTACT_WRAPPER['form']['CONTACT_EMAIL_COPY'] 			= "<tr><td>{---}".LANCONTACT_07."</td></tr>";
	$CONTACT_WRAPPER['form']['CONTACT_PERSON']				= "<tr><td>".LANCONTACT_14."<br />{---}</td></tr>";

	//FIXME Upgrade to bootstrap3 non-table format for phone/tablet compatibility. 
	$CONTACT_TEMPLATE['form'] = "
	<form action='".e_SELF."' method='post' id='contactForm' >
	<table class='table'>
	{CONTACT_PERSON}
	<tr><td>".LANCONTACT_03."<br />
	{CONTACT_NAME}
	</td></tr>
	<tr><td>".LANCONTACT_04."<br />
	{CONTACT_EMAIL}
	</td></tr>
	<tr><td>
	".LANCONTACT_05."<br />
	{CONTACT_SUBJECT}
	</td></tr>
	{CONTACT_EMAIL_COPY}
	<tr><td>
    ".LANCONTACT_06."<br />
    {CONTACT_BODY}
	</td></tr>
	{CONTACT_IMAGECODE}
	{CONTACT_IMAGECODE_INPUT}
	<tr><td>
	{CONTACT_SUBMIT_BUTTON}
	</td></tr>
	</table>
	</form>";

			
		
	

?>