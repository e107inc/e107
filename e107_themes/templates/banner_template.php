<?php

// ##### LOGIN TABLE -------------------------------------------------------------------------------
if(!$BANNER_LOGIN_TABLE){
		$BANNER_LOGIN_TABLE = "
		<div style='align:center'>\n
		<form method='post' action='".e_SELF."'>\n
		<table style='width:40%'>
		<tr>
			<td style='width:15%' class='defaulttext'>".LAN_16."</td>
			<td>{BANNER_LOGIN_TABLE_LOGIN}\n</td>
		</tr>
		<tr>
			<td style='width:15%' class='defaulttext'>".LAN_17."</td>
			<td>{BANNER_LOGIN_TABLE_PASSW}\n</td>
		</tr>
		<tr>
			<td style='width:15%'></td>
			<td>{BANNER_LOGIN_TABLE_SUBMIT}</td>
		</tr>
		</table>
		</form>
		</div>";

}
// ##### ------------------------------------------------------------------------------------------

// ##### BANNER TABLE -----------------------------------------------------------------------------
if(!$BANNER_TABLE_START){
		$BANNER_TABLE_START = "
		<table class='fborder' style='width:98%; border:1px solid #000' border='1'>
		<tr><td colspan='7' style='text-align:center' class='fcaption'>".LAN_21."</td></tr>
		<tr>
			<td class='forumheader' style='text-align:center'>".LAN_22."</td>
			<td class='forumheader' style='text-align:center'>".LAN_23."</td>
			<td class='forumheader' style='text-align:center'>".LAN_24."</td>
			<td class='forumheader' style='text-align:center'>".LAN_25."</td>
			<td class='forumheader' style='text-align:center'>".LAN_26."</td>
			<td class='forumheader' style='text-align:center'>".LAN_27."</td>
			<td class='forumheader' style='text-align:center'>".LAN_28."</td>
		</tr>";
}
if(!$BANNER_TABLE){
		$BANNER_TABLE = "
		<tr>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_CLIENTNAME}</td>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_BANNER_ID}</td>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_BANNER_CLICKS}</td>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_CLICKPERCENTAGE}</td>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_BANNER_IMPRESSIONS}</td>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_IMPRESSIONS_PURCHASED}</td>
			<td class='forumheader3' style='text-align:center'>{BANNER_TABLE_IMPRESSIONS_LEFT}</td>
		</tr>
		<tr>
			<td class='forumheader3' style='text-align:center'>&nbsp;</td>
			<td colspan='2' class='forumheader3' style='text-align:center'>{BANNER_TABLE_ACTIVE}</td>
			<td colspan='4' class='forumheader3' style='text-align:center'>{BANNER_TABLE_STARTDATE} {BANNER_TABLE_ENDDATE}</td>";

			if($BANNER_TABLE_IP){
				$BANNER_TABLE .= "
				</tr>
				<tr>
					<td class='forumheader3'>".LAN_35.": {BANNER_TABLE_IP_LAN}</td>
					<td colspan='6' class='forumheader3'>{BANNER_TABLE_IP}</td>";
			}

		$BANNER_TABLE .= "
		</tr>
		<tr><td colspan='7'>&nbsp;</td></tr>";
}
if(!$BANNER_TABLE_END){
		$BANNER_TABLE_END = "
		</table>";
}
// ##### ------------------------------------------------------------------------------------------





?>