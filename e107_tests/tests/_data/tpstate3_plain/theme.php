<?php
/** Fixture theme: a theme.xml with no <libraries> block and no framework define, which is the #6017 shape. */

if(!defined('e107_INIT')) { exit; }

function tablestyle($caption, $text, $mode = '')
{
	echo "<div class='tp-block'><h2 class='tp-caption'>".$caption."</h2><div class='tp-body'>".$text."</div></div>";
}

$LAYOUT['default'] = "<div id='tpstate3-plain'><div id='tp-search'>{SEARCH}</div><div id='tp-menu'>{MENU=2}</div>{---}</div>";
