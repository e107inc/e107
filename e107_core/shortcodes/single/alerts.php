<?php


function alerts_shortcode($parm = '')
{
	return e107::getMessage()->setUnique()->render();
}
