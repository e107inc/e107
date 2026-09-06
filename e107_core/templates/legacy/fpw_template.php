<?php

if (!defined('e107_INIT')) { exit; }

if (!defined("USER_WIDTH")){ define("USER_WIDTH","width:70%"); }

$FPW_TEMPLATE['form'] = "
		<div style='text-align:center'>
		<table style='".USER_WIDTH."' class='table fborder'>

		<tr>
		<td class='fcaption' colspan='2'>".LAN_05."</td>
		</tr>";

if((int) e107::getPref('allowEmailLogin') === 0)
{
	$FPW_TEMPLATE['form'] .= "
			<tr>
			<td class='forumheader3' style='width:70%'>".LAN_FPW1.":</td>
			<td class='forumheader3' style='width:30%;text-align:center'>
			{FPW_USERNAME}
			</td>
			</tr>";
}

$FPW_TEMPLATE['form'] .= "
		<tr>
		<td class='forumheader3' style='width:70%'>".LAN_FPW22.":</td>
		<td class='forumheader3 text-left' style='width:30%'>
		{FPW_USEREMAIL}
		</td>
		</tr>";

if(deftrue('USE_IMAGECODE'))
{
	$FPW_TEMPLATE['form'] .= "
				<tr>
					<td class='forumheader3' style='width:70%'>{FPW_CAPTCHA_LAN}</td>
					<td class='forumheader3 text-left' style='width:30%;'>{FPW_CAPTCHA_HIDDEN} {FPW_CAPTCHA_IMG}<br />
					{FPW_CAPTCHA_INPUT}<br />
					</td>
				</tr>";
}

$FPW_TEMPLATE['form'] .= "
		<tr style='vertical-align:top'>
		<td class='forumheader' colspan='2' style='text-align:center'>
		{FPW_SUBMIT}	
		</td>
		</tr>
		</table>
		</div>";

$FPW_TEMPLATE['header'] = "
		<div style='width:100%;text-align:center;margin-left:auto;margin-right:auto'>
			<div><br />
			{FPW_LOGIN_LOGO}
			<br />";

$FPW_TEMPLATE['footer'] = "</div></div>";
