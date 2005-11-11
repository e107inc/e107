<?php

// ##### FPW TABLE -----------------------------------------------------------------------------
if(!$FPW_TABLE){
		$FPW_TABLE = "
		<div style='text-align:center'>
		<form method='post' action='".e_SELF."'>\n
		<table style='width:85%' class='fborder'>

		<tr>
		<td class='forumheader3' colspan='2' style='text-align:center'>".LAN_05."</td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:25%'>".LAN_FPW1."</td>
		<td class='forumheader3' style='width:75%;text-align:center'>
		<input class='tbox' type='text' name='username' size='60' value='' maxlength='100' />
		</td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:25%'>".LAN_112."</td>
		<td class='forumheader3' style='width:75%; text-align:center'>
		<input class='tbox' type='text' name='email' size='60' value='' maxlength='100' />
		</td>
		</tr>";

		if($FPW_TABLE_SECIMG_SECIMG){
				$FPW_TABLE .= "
				<tr>
					<td class='forumheader3' style='width:25%'>{FPW_TABLE_SECIMG_LAN}</td>
					<td class='forumheader3' style='width:75%;text-align:left'>{FPW_TABLE_SECIMG_HIDDEN} {FPW_TABLE_SECIMG_SECIMG}<br />
					{FPW_TABLE_SECIMG_TEXTBOC}<br />
					</td>
				</tr>";
		}

		$FPW_TABLE .="
		<tr style='vertical-align:top'>
		<td class='forumheader' colspan='2' style='text-align:center'>
		<input class='button' type='submit' name='pwsubmit' value='".LAN_156."' />
		</td>
		</tr>
		</table>
		</form>
		</div>";
}
// ##### ------------------------------------------------------------------------------------------

// ##### FPW HEADER TABLE -------------------------------------------------------------------------
if(!$FPW_TABLE_HEADER){
		$FPW_TABLE_HEADER = "
		<div style='width:100%;text-align:center;margin-left:auto;margin-right:auto'>
			<div style='width:70%;margin-left:auto;margin-right:auto;text-align:center;'><br />
			".(file_exists(THEME."images/login_logo.png") ? "<img src='".THEME."images/login_logo.png' alt='' />\n" : "<img src='".e_IMAGE."logo.png' alt='' />\n")."
			<br />";
}
// ##### ------------------------------------------------------------------------------------------

// ##### FPW FOOTER TABLE -------------------------------------------------------------------------
if(!$FPW_TABLE_FOOTER){
		$FPW_TABLE_FOOTER = "</div></div>";
}
// ##### ------------------------------------------------------------------------------------------

?>