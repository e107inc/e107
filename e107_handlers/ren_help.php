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
|     $Revision: 1.53 $
|     $Date: 2006-07-07 20:18:55 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }



function ren_help($mode = 1, $addtextfunc = "addtext", $helpfunc = "help")
{
    // ren_help() is deprecated - use the shortcode or display_help().
    return display_help("helpb", $mode, $addtextfunc, $helpfunc = "help");
}

/*
	You may also use only the shortcode instead of display_help():
    $BBCODE_TEMPLATE = "{BB_HELP=$mode}<br />{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}";
	require_once(e_FILE."shortcode/batch/bbcode_shortcodes.php");
	$text .= $tp->parseTemplate($BBCODE_TEMPLATE, true, $bbcode_shortcodes);

*/


function display_help($tagid="helpb", $mode = 1, $addtextfunc = "addtext", $helpfunc = "help")
{
    if(e_WYSIWYG){ return; }
	global $tp, $pref, $eplug_bb, $bbcode_func, $register_bb, $bbcode_help, $bbcode_helpactive, $bbcode_helptag;

	$bbcode_func = $addtextfunc;
 	$bbcode_help = $helpfunc;
    $bbcode_helptag = $tagid;

    // load the template
	if(is_readable(THEME."bbcode_template.php"))
	{
		include(THEME."bbcode_template.php");
	}
	else
	{
		include(e_THEME."templates/bbcode_template.php");
	}


	if($mode != 2)
	{
    	$bbcode_helpactive = TRUE;
	}

    // Load the Plugin bbcode AFTER the templates, so they can modify or replace.
	foreach($pref['e_bb_list'] as $val)
	{
    	if(is_readable(e_PLUGIN.$val."/e_bb.php"))
		{
        	require(e_PLUGIN.$val."/e_bb.php");
		}
	}

    $temp['news'] 		= $BBCODE_TEMPLATE_NEWSPOST;
	$temp['extended']	= $BBCODE_TEMPLATE_NEWSPOST;
	$temp['admin']		= $BBCODE_TEMPLATE_ADMIN;
	$temp['mailout']	= $BBCODE_TEMPLATE_ADMIN."{BB=shortcode}";

	if($temp[$mode])
	{
        $BBCODE_TEMPLATE = $temp[$mode];
	}

 	require_once(e_FILE."shortcode/batch/bbcode_shortcodes.php");
  	return $tp->parseTemplate($BBCODE_TEMPLATE, FALSE, $bbcode_shortcodes);

}


?>