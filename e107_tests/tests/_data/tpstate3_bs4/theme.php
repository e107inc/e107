<?php
/** Fixture theme: a theme.xml declaring Bootstrap 4, the version core's own markup keeps forgetting. */

if(!defined('e107_INIT')) { exit; }

function tablestyle($caption, $text, $mode = '')
{
	echo "<div class='tp-block'><h2 class='tp-caption'>".$caption."</h2><div class='tp-body'>".$text."</div></div>";
}

$LAYOUT['default'] = "<div id='tpstate3-bs4'><div id='tp-search'>{SEARCH}</div><div id='tp-menu'>{MENU=2}</div>{---}</div>";
