<?php
/** Fixture theme: every box it draws names its caption, the layout it is in and the table style mode it was drawn in. */

if(!defined('e107_INIT')) { exit; }

function tablestyle($caption, $text, $mode = '')
{
	echo "<div class='route-box' data-caption='".$caption."' data-layout='".THEME_LAYOUT."' data-mode='".$mode."'>".$text."</div>";
}

$LAYOUT['default'] = '{---}';
$LAYOUT['route'] = '{---}';
