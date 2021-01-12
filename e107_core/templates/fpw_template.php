<?php
// $Id$


if (!defined('e107_INIT')) { exit; }


$FPW_TEMPLATE['form'] = '
					<div class="row">
						<div class="col-sm-12 col-md-8">
						<p>{FPW_TEXT}</p>
						<div class="form-group">{FPW_USEREMAIL}</div>
						<div class="form-group">{FPW_CAPTCHA_IMG}{FPW_CAPTCHA_INPUT}</div>
							<div class="row">	
								<div class="col-xs-12 col-sm-4 col-sm-offset-8">
								{FPW_SUBMIT}
								</div>
							</div>		
						</div>
					</div>
					';

$FPW_TEMPLATE['header'] = '<div id="fpw-page" class="container">';
$FPW_TEMPLATE['footer'] = '</div>';






