<?php
/** Fixture theme: the tpstate3_plain shape plus a v1-shaped login_template.php at the theme root. */

if(!defined('e107_INIT')) { exit; }

function tablestyle($caption, $text, $mode = '')
{
	echo "<div class='tp-block'><h2 class='tp-caption'>".$caption."</h2><div class='tp-body'>".$text."</div></div>";
}

$LAYOUT['default'] = "<div id='tpstate3-rootlogin'>{---}</div>";
