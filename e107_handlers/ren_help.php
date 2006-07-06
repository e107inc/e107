<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/ren_help.php,v $
|     $Revision: 1.50 $
|     $Date: 2006-07-06 03:28:50 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

/*
	You may also use only the shortcode instead:

	require_once(e_FILE."shortcode/batch/bbcode_shortcodes.php");
	$text .= $tp->parseTemplate($BBCODE_TEMPLATE, true, $bbcode_shortcodes);

*/

function ren_help($mode = 1, $addtextfunc = "addtext", $helpfunc = "help")
{
    // ren_help() is deprecated - use the shortcode or display_help().
    return display_help("helpb", $mode, $addtextfunc, $helpfunc = "help");

}

// display_help is deprecated - use shortcodes instead.

function display_help($tagid="helpb", $mode = 1, $addtextfunc = "addtext", $helpfunc = "help")
{

	global $tp, $bbcode_func, $bbcode_help, $bbcode_helpactive, $bbcode_helptag;

	$bbcode_func = $addtextfunc;
 	$bbcode_help = $helpfunc;
    $bbcode_helptag = $tagid;

	$BBCODE_TEMPLATE = "
		{BB=link}{BB=b}{BB=i}{BB=u}
        {BB=img}{BB=center}{BB=left}
        {BB=right}{BB=bq}{BB=code}
        {BB=list}{BB=emotes}";

	if($mode != 2)
	{
    	$bbcode_helpactive = TRUE;
	}

    if($mode == TRUE)
	{
		$BBCODE_TEMPLATE .= "{BB=fontcol}{BB=fontsize}";
	}

	if($mode == "news" || $mode == "extended")
	{
    	$BBCODE_TEMPLATE = "
		{BB_HELP=$mode}<br />
        {BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}
        {BB=right}{BB=bq}{BB=code}{BB=list}{BB=emotes}
	    {BB_IMAGEDIR=".e_IMAGE."newspost_images/}
        {BB=preimage}{BB=prefile}{BB=flash}";
	}

 	require_once(e_FILE."shortcode/batch/bbcode_shortcodes.php");
  	return $tp->parseTemplate($BBCODE_TEMPLATE, FALSE, $bbcode_shortcodes);

}


?>