<?php

$sc_style['LM_SIGNUP_LINK']['pre'] = "<br />[ ";
$sc_style['LM_SIGNUP_LINK']['post'] = " ]";
if (!isset($LOGIN_MENU_FORM)){

	$LOGIN_MENU_FORM = "
	<div style='text-align: center'>".
    LOGIN_MENU_L1."
	<br />\n
	{LM_USERNAME_INPUT}
	<br />".
	LOGIN_MENU_L2."
	<br />\n
    {LM_PASSWORD_INPUT}
	<br />\n
  {LM_IMAGECODE}
	{LM_LOGINBUTTON}
  {LM_REMEMBERME}
	<br />
	{LM_SIGNUP_LINK}
	<br />
	[ {LM_FPW_LINK} ]
	</div>
	";
}
?>
