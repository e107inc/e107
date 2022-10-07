<?php

if (!defined('e107_INIT')) { exit; }
 

	// e107 v2.x

	$MEMBERSONLY_TEMPLATE['default']['caption']	= LAN_MEMBERS_0;
	$MEMBERSONLY_TEMPLATE['default']['header']	= "<div class='container text-center' style='margin-right:auto;margin-left:auto'><br /><br />";
	$MEMBERSONLY_TEMPLATE['default']['body']	= "<div class='alert alert-block text-danger'>
														{MEMBERSONLY_RESTRICTED_AREA} {MEMBERSONLY_LOGIN}
														{MEMBERSONLY_SIGNUP}<br /><br />{MEMBERSONLY_RETURNTOHOME}

													</div>
													";

	$MEMBERSONLY_TEMPLATE['default']['footer'] = "</div>";



	$MEMBERSONLY_TEMPLATE['signup']['header'] = ' 
<div class="container">
   <div class="row justify-content-center">
      <div class="col-lg-5">
		 <div class="m-auto">
			{LOGO: login}
		 </div>';
	$MEMBERSONLY_TEMPLATE['signup']['footer'] = "</div></div</div";



