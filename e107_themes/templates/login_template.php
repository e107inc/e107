<?php

// ##### LOGIN HEADER TABLE -----------------------------------------------------------------------
if(!$LOGIN_TABLE_HEADER){
	$LOGIN_TABLE_HEADER .= "
	<div style='width:100%;text-align:center; margin-left: auto;margin-right: auto'>
		<div style='text-align:center;width:70%;margin-left: auto;margin-right: auto'>
			".(file_exists(THEME."images/login_logo.png") ? "<img src='".THEME."images/login_logo.png' alt='' />\n" : "<img src='".e_IMAGE."logo.png' alt='' />\n" );
}

// ##### LOGIN TABLE -----------------------------------------------------------------------------
if(!$LOGIN_TABLE){
		$LOGIN_TABLE = "";
		if($LOGIN_TABLE_LOGINMESSAGE != ""){
				$LOGIN_TABLE .= "<div style='text-align:center'>{LOGIN_TABLE_LOGINMESSAGE}</div>";
		}
		$LOGIN_TABLE .= "
		<div style='text-align:center'>
		".$rs -> form_open("post", e_SELF)."
		<table class='fborder' style='width:30%'>
		<tr><td class='forumheader' colspan='2' style='text-align:center;width:30%'><strong>".LAN_LOGIN_4."</strong></td></tr>
		<tr>
			<td class='forumheader3' style='width:30%'>".LAN_LOGIN_1."</td>
			<td class='forumheader3' style='width:70%; text-align:right'>{LOGIN_TABLE_USERNAME}</td>
		</tr>
		<tr>
			<td class='forumheader3' style='width:30%'>".LAN_LOGIN_2."</td>
			<td class='forumheader3' style='width:70%; text-align:right'>{LOGIN_TABLE_PASSWORD}</td>
		</tr>";

		if($LOGIN_TABLE_SECIMG_SECIMG){
			$LOGIN_TABLE .= "
			<tr>
				<td class='forumheader3'>{LOGIN_TABLE_SECIMG_LAN}</td>
				<td class='forumheader3'>{LOGIN_TABLE_SECIMG_HIDDEN} {LOGIN_TABLE_SECIMG_SECIMG}<br />
				{LOGIN_TABLE_SECIMG_TEXTBOC}<br />
				</td>
			</tr>";
		}

		$LOGIN_TABLE .= "
		<tr>
		<td class='forumheader2' colspan='2' style='text-align:center'>
			{LOGIN_TABLE_AUTOLOGIN}<span class='smalltext'>{LOGIN_TABLE_AUTOLOGIN_LAN}</span><br />
			{LOGIN_TABLE_SUBMIT}
		</td>
		</tr></table>".
		$rs -> form_close()."
		</div>";
}
// ##### ------------------------------------------------------------------------------------------

// ##### LOGIN TABLE FOOTER -----------------------------------------------------------------------
if(!$LOGIN_TABLE_FOOTER){
			$LOGIN_TABLE_FOOTER = "
			<div style='width:70%;margin-right:auto;margin-left:auto'>
				<div style='text-align:center'><br />
					{LOGIN_TABLE_FOOTER_USERREG}
					&nbsp;&nbsp;&nbsp;<a href='fpw.php'>".LAN_LOGIN_12."</a>
				</div>
			</div>
		</div>
	</div>";
}
// ##### ------------------------------------------------------------------------------------------


?>