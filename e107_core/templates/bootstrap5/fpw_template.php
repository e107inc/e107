<?php
// $Id$

if (!defined('e107_INIT'))
{
	exit;
}
 

$FPW_WRAPPER['form']['FPW_CAPTCHA_IMG']  =
"<div class='row align-items-center my-2'>
	<label class='col-md-4'>&nbsp;</label>
	<div class='col-md-8'>{---}</div>
</div>";

$FPW_WRAPPER['form']['FPW_CAPTCHA_INPUT']  =
"<div class='row align-items-center my-2'>
	<label class='col-md-4 col-form-label' for='code-verify'>" . e107::getSecureImg()->renderLabel() . "</label>
	<div class='col-md-8'>{---}</div>
</div>";


$FPW_TEMPLATE['form'] = '
	<div class="row">
	<div class="col-md-12">
	<div class="form-text">{FPW_TEXT}</p>
	<div class="row align-items-center my-2">
	<div class="col-md-12">{FPW_USEREMAIL}</div>
	</div>	 
	{FPW_CAPTCHA_IMG}{FPW_CAPTCHA_INPUT}
	<div class="row g-3 my-3 align-items-center  w-50 m-auto">
	{FPW_SUBMIT}
	</div>	
	</div>
	</div>
';

$FPW_TEMPLATE['header'] = '<div id="fpw-page" class="container">';
$FPW_TEMPLATE['footer'] = '</div>';
