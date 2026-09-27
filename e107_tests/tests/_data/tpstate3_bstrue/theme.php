<?php
/** Fixture theme: the pre-2.1 spelling of "this theme is Bootstrap", which theme_handler.php never normalises to a number. */

if(!defined('e107_INIT')) { exit; }

define('BOOTSTRAP', true);

function tablestyle($caption, $text, $mode = '')
{
	echo "<div class='tp-block'><h2 class='tp-caption'>".$caption."</h2><div class='tp-body'>".$text."</div></div>";
}

$LAYOUT['default'] = "<div id='tpstate3-bstrue'><div id='tp-search'>{SEARCH}</div><div id='tp-menu'>{MENU=2}</div>{---}</div>";
