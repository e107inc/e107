<?php
// $Id: login_template.php 11346 2010-02-17 18:56:14Z secretr $

if (!defined('e107_INIT')) { exit; }

// ##### LOGIN HEADER TABLE -----------------------------------------------------------------------
if(!isset($LOGIN_TABLE_HEADER))
{
	$LOGIN_TABLE_HEADER = "
	<div style='width:100%;text-align:center; margin-left: auto;margin-right: auto;  margin-top: 0px'><br />
		<div style='text-align:center;width:70%;margin-left: auto;margin-right: auto'>
			".(file_exists(THEME."images/login_logo.png") ? "<img src='".THEME_ABS."images/login_logo.png' alt='' />" : "<img src='".e_IMAGE."logo.png' alt='' />" );
}

// ##### LOGIN TABLE -----------------------------------------------------------------------------
if(!isset($LOGIN_TABLE))
{
		$LOGIN_TABLE = '';
		if($LOGIN_TABLE_LOGINMESSAGE != '')
		{
			$LOGIN_TABLE .= "<div style='text-align:center'>{LOGIN_TABLE_LOGINMESSAGE}</div>";
		}
		if (!isset($LOGIN_TABLE_SECIMG_SECIMG))
		{
			$LOGIN_TABLE_SECIMG_SECIMG = FALSE;
		}
		$LOGIN_TABLE .= "
		<div style='text-align:center;'>
		".$rs -> form_open("post", e_SELF)."
			<table style='width:60%' >
				<tr>
					<td style='text-align:center; font-weight: bold; padding: 20px 0 30px 0; font-zise: 15px' colspan='3'>
					".LAN_LOGIN_4."
					</td>
				</tr>
				<tr>
					<td class='title_clean'  style='text-align:left; width: 40%'>
						".LAN_LOGIN_1."
					</td>
					<td style='text-align:right; width: 40%'>
						{LOGIN_TABLE_USERNAME}
					</td>
					<td rowspan='".($LOGIN_TABLE_SECIMG_SECIMG ? 3 : 2)."' style='width: 20%; vertical-align: middle; margin-left: auto; margin-right: auto; text-align: center;'>
						".(file_exists(THEME."images/password.png") ? "<img src='".THEME_ABS."images/password.png' alt='' />" : "<img src='".e_IMAGE."generic/".IMODE."/password.png' alt='' />" )."
					</td>
				</tr>
				<tr>
					<td class='title_clean' style='text-align:left;'>
					".LAN_LOGIN_2."
					</td>
					<td style='text-align:right; width: 40%'>
						{LOGIN_TABLE_PASSWORD}
					</td>
				</tr>
		";
	if($LOGIN_TABLE_SECIMG_SECIMG){
		$LOGIN_TABLE .= "
				<tr>
					<td>
					{LOGIN_TABLE_SECIMG_LAN}
					</td>
					<td class='forumheader3'>
					{LOGIN_TABLE_SECIMG_HIDDEN} {LOGIN_TABLE_SECIMG_SECIMG} {LOGIN_TABLE_SECIMG_TEXTBOC}
					</td>
				</tr>";
	}

	$LOGIN_TABLE .= "
				<tr>
					<td style='text-align:center; padding: 10px 0 0 0;' colspan='3'>
					{LOGIN_TABLE_AUTOLOGIN}<span class='smalltext' style='padding: 0 5px 5px 5px;'>{LOGIN_TABLE_AUTOLOGIN_LAN}</span><br /><br />{LOGIN_TABLE_SUBMIT}
					</td>
				</tr>
			</table>".
	$rs -> form_close()."</div>";
}
// ##### ------------------------------------------------------------------------------------------

// ##### LOGIN TABLE FOOTER -----------------------------------------------------------------------
if(!isset($LOGIN_TABLE_FOOTER))
{
			$LOGIN_TABLE_FOOTER = "
			<div style='width:70%;margin-right:auto;margin-left:auto; font-weight: bold;'>
				<div style='text-align:center'><br />
					{LOGIN_TABLE_FOOTER_USERREG}&nbsp;&nbsp;&nbsp;<a href='".e_BASE."fpw.php'>".LAN_LOGIN_12."</a>
				</div>
			</div>
		</div>
	</div>";
}
// ##### ------------------------------------------------------------------------------------------


?>