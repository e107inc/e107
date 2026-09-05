<?php


if (!defined("USER_WIDTH")){ define("USER_WIDTH","width:70%"); }

// ##### FPW TABLE -----------------------------------------------------------------------------
if(!isset($FPW_TABLE))
{
		$FPW_TABLE = "
		<div style='text-align:center'>
		<form method='post' action='".SITEURL."fpw.php' autocomplete='off'>
		<table style='".USER_WIDTH."' class='table fborder'>

		<tr>
		<td class='fcaption' colspan='2'>".LAN_05."</td>
		</tr>";

		if($pref['allowEmailLogin']==0)
		{
			$FPW_TABLE .= "
			<tr>
			<td class='forumheader3' style='width:70%'>".LAN_FPW1.":</td>
			<td class='forumheader3' style='width:30%;text-align:center'>
			{FPW_USERNAME}
			</td>
			</tr>";
		}


		$FPW_TABLE .="
		<tr>
		<td class='forumheader3' style='width:70%'>".LAN_112.":</td>
		<td class='forumheader3 text-left' style='width:30%'>
		{FPW_USEREMAIL}
		</td>
		</tr>";

		if(deftrue('USE_IMAGECODE'))
		{
				$FPW_TABLE .= "
				<tr>
					<td class='forumheader3' style='width:70%'>{FPW_TABLE_SECIMG_LAN}</td>
					<td class='forumheader3 text-left' style='width:30%;'>{FPW_TABLE_SECIMG_HIDDEN} {FPW_TABLE_SECIMG_SECIMG}<br />
					{FPW_TABLE_SECIMG_TEXTBOC}<br />
					</td>
				</tr>";
		}

		$FPW_TABLE .="
		<tr style='vertical-align:top'>
		<td class='forumheader' colspan='2' style='text-align:center'>
		{FPW_SUBMIT}	
		</td>
		</tr>
		</table>
		</form>
		</div>";
}
// ##### ------------------------------------------------------------------------------------------

// ##### FPW HEADER TABLE -------------------------------------------------------------------------
if(!isset($FPW_TABLE_HEADER))
{
		$FPW_TABLE_HEADER = "
		<div style='width:100%;text-align:center;margin-left:auto;margin-right:auto'>
			<div><br />
			{FPW_LOGIN_LOGO}
			<br />";
}
// ##### ------------------------------------------------------------------------------------------

// ##### FPW FOOTER TABLE -------------------------------------------------------------------------
if(!isset($FPW_TABLE_FOOTER))
{
		$FPW_TABLE_FOOTER = "</div></div>";
}
// ##### ------------------------------------------------------------------------------------------
