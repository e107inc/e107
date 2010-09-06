<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_themes/templates/bbcode_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

// How to register your own BBcode button.
// Uncomment the 2 commented lines below to see it in action. (only applies to the user area)

// $register_bb['blank'] = array("", "[blank][/blank]","Blank example helper text",e_IMAGE."bbcode/template.png");

$BBCODE_TEMPLATE = "
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}{BB=right}
	{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}{BB=youtube}
	<div class='field-spacer'><!-- --></div>
";

// $BBCODE_TEMPLATE .= "{BB=blank}";


// $sc_style['BB_HELP']['pre'] = "<div style='text-align:center'>";
// $sc_style['BB_HELP']['post'] = "</div>";

$BBCODE_TEMPLATE_SUBMITNEWS = "
	
	{BB_HELP}
	<div class='field-spacer'><!-- --></div>
    {BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}{BB=right}
	{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}{BB=flash}{BB=youtube}
	<div class='field-spacer'><!-- --></div>
";


// --------   Admin Templates ----------------------

$BBCODE_TEMPLATE_ADMIN = "
	{BB_HELP=admin}
	<div class='field-spacer'><!-- --></div>
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}
	{BB=right}{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
	{BB_PREIMAGEDIR=".e_IMAGE."}
	{BB=preimage}{BB=prefile}{BB=flash}{BB=youtube}
	<div class='field-spacer'><!-- --></div>
";

$BBCODE_TEMPLATE_MAILOUT = "
	{BB_HELP=admin}
	<div class='field-spacer'><!-- --></div>
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}
	{BB=right}{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
	{BB_PREIMAGEDIR=".e_IMAGE."}
	{BB=preimage}{BB=prefile}{BB=flash}{BB=shortcode}
	<div class='field-spacer'><!-- --></div>
";

// $BBCODE_TEMPLATE_ADMIN .= "{BB=blank}";

$BBCODE_TEMPLATE_NEWSPOST = "
	{BB_HELP=$mode}
	<div class='field-spacer'><!-- --></div>
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}
	{BB=right}{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
	{BB_PREIMAGEDIR=".e_IMAGE."newspost_images/}
	{BB=preimage}{BB=prefile}{BB=flash}{BB=youtube}
	<div class='field-spacer'><!-- --></div>
";

$BBCODE_TEMPLATE_CPAGE = "
	{BB_HELP}
	<div class='field-spacer'><!-- --></div>
	{BB=newpage}
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}{BB=right}
	{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
	{BB_PREIMAGEDIR=".e_IMAGE."custom/}
	{BB=preimage}{BB=prefile}{BB=flash}{BB=youtube}
	<div class='field-spacer'><!-- --></div>
";
?>