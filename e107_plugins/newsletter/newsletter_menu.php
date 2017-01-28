<?php
	/**
	 * e107 website system
	 *
	 * Copyright (C) 2008-2016 e107 Inc (e107.org)
	 * Released under the terms and conditions of the
	 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
	 *
	 */

if (!defined('e107_INIT')) { exit; }
if (!e107::isInstalled('newsletter') || (USER && !ADMIN))
{
	return;
}

$pref = e107::pref('core');

if(!empty($pref['signup_option_class']))
{

	//e107::lan('newsletter');

	$frm = e107::getForm();
	$text = $frm->open('newsletter','post', e_SIGNUP, array('class'=>'form-inline'));
	$text .= "<div class='input-group'>";
	$text .= $frm->text('email','', null, array('placeholder'=> NLLAN_73));
	$text .= "<span class='input-group-btn'>";
	$text .= $frm->button('subscribe', 1, 'submit', NLLAN_52, array('class'=>'btn-default'));
	$text .= "</span>";
	$text .= "</div>";
	$text .= $frm->close();

	$ns->tablerender(LAN_PLUGIN_NEWSLETTER_NAME, $text);
}
elseif(ADMIN)
{
	// This is temporary.
	$message = "Please check your Signup Page preferences, making sure that 'Subscribe to content/mailouts' is set to 'Display' or 'Required'. You should also have one or more 'Newsletter' userclasses set to managed by 'Everyone'.";

	$ns->tablerender(LAN_PLUGIN_NEWSLETTER_NAME, "<div class='alert alert-danger' style='margin:0'>".$message."</div>" );
}

