<?php

if(!is_array($parm))
{
	$parm = array();
}
                        
$parm['type'] = !empty($parm['type']) ? $parm['type'] : 'side';
$parm['layout'] = !empty($parm['layout']) ? $parm['layout'] : $parm['type'];

require_once(e_CORE."shortcodes/single/navigation.php");
        
$text = navigation_shortcode($parm);        

$caption = isset($parm['caption'][e_LANGUAGE]) ? $parm['caption'][e_LANGUAGE] : LAN_PLUGIN_NAVIGATION_NAME;

e107::getRender()->tablerender($caption, $text);
                            
