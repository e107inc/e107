<?php
/** Fixture theme: the tpstate4_legacybs shape plus a $FPW_TABLE global set in theme.php itself. */

if(!defined('e107_INIT')) { exit; }

$themename = "TP State 4 Global FPW";
$themeversion = "1.0";
$themeauthor = "e107 Inc";

define('BOOTSTRAP', 3);

function tablestyle($caption, $text, $mode = '')
{
	echo "<div class='tp-block'><h2 class='tp-caption'>".$caption."</h2><div class='tp-body'>".$text."</div></div>";
}

$HEADER = "<div id='tpstate4-globalfpw'>";
$FOOTER = "</div>";

$FPW_TABLE = "
<div id='tpstate4-globalfpw-template'>
<p>TPSTATE4_GLOBALFPW_MARKER</p>
{FPW_USEREMAIL}
{FPW_SUBMIT}
</div>";
