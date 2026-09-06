<?php
/** Fixture theme: the tpstate3_plain shape plus a $FPW_TABLE global set in theme.php itself. */

if(!defined('e107_INIT')) { exit; }

function tablestyle($caption, $text, $mode = '')
{
	echo "<div class='tp-block'><h2 class='tp-caption'>".$caption."</h2><div class='tp-body'>".$text."</div></div>";
}

$LAYOUT['default'] = "<div id='tpstate3-globalfpw'>{---}</div>";

$FPW_TABLE = "
<div id='tpstate3-globalfpw-template'>
<p>TPSTATE3_GLOBALFPW_MARKER</p>
{FPW_USEREMAIL}
{FPW_SUBMIT}
</div>";
