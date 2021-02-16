<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Contact Template
 */
 // $Id$

if (!defined('e107_INIT')) { exit; }

$CONTACT_WRAPPER['info']['CONTACT_INFO'] = "<div>{---}</div>";
$CONTACT_WRAPPER['info']['CONTACT_INFO: type=organization'] = "<h4>{---}</h4>";
$CONTACT_WRAPPER['info']['CONTACT_INFO: type=message'] = "<p>{---}</p>";
$CONTACT_WRAPPER['info']['CONTACT_INFO: type=address'] = "<address>{GLYPH=fa-map-marker} {---}</address>";
$CONTACT_WRAPPER['info']['CONTACT_INFO: type=email1'] = "<div>{GLYPH=fa-envelope} {---}</div>";
$CONTACT_WRAPPER['info']['CONTACT_INFO: type=email2'] = "<div>{GLYPH=fa-envelope} {---}</div>";
$CONTACT_WRAPPER['info']['CONTACT_INFO: type=phone1'] = "<div>{GLYPH=fas-phone-alt} {---}</div>";
$CONTACT_WRAPPER['info']['CONTACT_INFO: type=phone2'] = "<div>{GLYPH=fas-phone-alt} {---}</div>";
$CONTACT_WRAPPER['info']['CONTACT_INFO: type=phone3'] = "<div>{GLYPH=fas-phone-alt} {---}</div>";
$CONTACT_WRAPPER['info']['CONTACT_INFO: type=fax'] = "<div>{GLYPH=fa-fax} {---}</div>";
$CONTACT_WRAPPER['info']['CONTACT_INFO: type=hours'] = "<div>{GLYPH=fa-clock} {---}</div>";

$CONTACT_TEMPLATE['info'] = "

	<div id='contactInfo' >
		
		<!-- Backward Compat. Contact Info -->
		{SITECONTACTINFO}
		<!-- New Contact Info -->
		{CONTACT_INFO: type=organization}
		{CONTACT_INFO: type=message}
		<div class='row'>
			<div class ='col-md-6 col-lg-4'>			
				{CONTACT_INFO: type=address}	
				<div class='form-group'>	
				{CONTACT_INFO: type=phone1}
				{CONTACT_INFO: type=phone2}
				{CONTACT_INFO: type=phone3}
				{CONTACT_INFO: type=fax}
				</div>
				{CONTACT_INFO: type=email1}
				{CONTACT_INFO: type=email2}
				<br />
				{CONTACT_INFO: type=hours}
			</div>
			<div class ='col-md-6 col-lg-8 flex-row'>
				{CONTACT_MAP: zoom=city}
			</div>
		</div>
	</div>

";


$CONTACT_TEMPLATE['menu'] =  '
	<div class="contactMenuForm">
		<div class="control-group form-group">
			<label for="contactName">'.LANCONTACT_03.'</label>
				{CONTACT_NAME}
		 </div>
		 
		<div class="control-group form-group">
			<label class="control-label" for="contactEmail">'.LANCONTACT_04.'</label>
				{CONTACT_EMAIL}
		</div>
		<div class="control-group form-group">
			<label for="contactBody" >'.LANCONTACT_06.'</label>
				{CONTACT_BODY=rows=5&cols=30}
		</div>
		<div class="form-group"><label for="gdpr">'.LANCONTACT_24.'</label>
			<div class="checkbox form-check">
				<label>{CONTACT_GDPR_CHECK} '.LANCONTACT_21.'</label>
				<div class="help-block">{CONTACT_GDPR_LINK}</div> 
			</div>
		</div>
		{CONTACT_SUBMIT_BUTTON: class=btn btn-sm btn-small btn-primary button}
	</div>       
 ';
 


// Shortcode wrappers.
$CONTACT_WRAPPER['form']['CONTACT_IMAGECODE'] 			= "<div class='control-group form-group'><label for='code-verify'>{CONTACT_IMAGECODE_LABEL}</label> {---}";
$CONTACT_WRAPPER['form']['CONTACT_IMAGECODE_INPUT'] 	= "{---}</div>";
$CONTACT_WRAPPER['form']['CONTACT_EMAIL_COPY'] 			= "<div class='control-group form-group'>{---}".LANCONTACT_07."</div>";
$CONTACT_WRAPPER['form']['CONTACT_PERSON']				= "<div class='control-group form-group'><label for='contactPerson'>".LANCONTACT_14."</label>{---}</div>";




$CONTACT_TEMPLATE['form'] = "
	<form action='".e_SELF."' method='post' id='contactForm' >

	{CONTACT_PERSON}
	<div class='control-group form-group'><label for='contactName'>".LANCONTACT_03."</label>
		{CONTACT_NAME}
	</div>
	<div class='control-group form-group'><label for='contactEmail'>".LANCONTACT_04."</label>
		{CONTACT_EMAIL}
	</div>
	<div class='control-group form-group'><label for='contactSubject'>".LANCONTACT_05."</label>
		{CONTACT_SUBJECT}
	</div>

		{CONTACT_EMAIL_COPY}

	<div class='control-group form-group'><label for='contactBody'>".LANCONTACT_06."</label>
		{CONTACT_BODY}
	</div>

	{CONTACT_IMAGECODE}
	{CONTACT_IMAGECODE_INPUT}

	<div class='form-group'><label for='gdpr'>".LANCONTACT_24."</label>
		<div class='checkbox'>
			<label>{CONTACT_GDPR_CHECK} ".LANCONTACT_21."</label>
			<div class='help-block'>{CONTACT_GDPR_LINK}</div> 
		</div>
	</div>
	
	

	<div class='form-group'>
	{CONTACT_SUBMIT_BUTTON}
	</div>

	</form>";

	// Customize the email subject
	// Variables:  CONTACT_SUBJECT and CONTACT_PERSON as well as any custom fields set in the form. )
$CONTACT_TEMPLATE['email']['subject'] = "{CONTACT_SUBJECT}";

	


