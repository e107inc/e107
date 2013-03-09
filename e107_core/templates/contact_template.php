<?php
// $Id$

if (!defined('e107_INIT')) { exit; }
if (!defined("USER_WIDTH")){ define("USER_WIDTH","width:97%"); }

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

$sc_style['CONTACT_EMAIL_COPY']['pre'] = "<tr><td>";
$sc_style['CONTACT_EMAIL_COPY']['post'] = LANCONTACT_07."</td></tr>";

$sc_style['CONTACT_PERSON']['pre'] = "<tr><td>".LANCONTACT_14."<br />   ";
$sc_style['CONTACT_PERSON']['post'] = "</td></tr>";

$sc_style['CONTACT_IMAGECODE']['pre'] = "<tr><td>".LANCONTACT_16."<br />";
$sc_style['CONTACT_IMAGECODE']['post'] = "";

$sc_style['CONTACT_IMAGECODE_INPUT']['pre'] = "";
$sc_style['CONTACT_IMAGECODE_INPUT']['post'] = "</td></tr>";



$CONTACT_TEMPLATE['menu'] =  '
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
 


  $CONTACT_TEMPLATE['form'] = "
	<form action='".e_SELF."' method='post' id='contactForm' >
	<table style='".USER_WIDTH."' cellpadding='1' cellspacing='7'>
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
