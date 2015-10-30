<?php
if (!defined('e107_INIT')){ exit; } 
//TODO add checks so that it's only loaded when needed.

if(USER_AREA)
{
	e107::css('core','camera/css/camera.css','jquery');
}
?>