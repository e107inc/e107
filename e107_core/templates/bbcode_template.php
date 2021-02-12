<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $URL$
 * $Id$
 */

// How to register your own BBcode button.
// Uncomment the 2 commented lines below to see it in action. (only applies to the user area)

// $register_bb['blank'] = array("", "[blank][/blank]","Blank example helper text",e_IMAGE."bbcode/template.png");
// Simplified default bbcode bar - removed P, H, BR and NOBR bbcodes


// This is used on the front-end. ie. comments etc. 
$BBCODE_TEMPLATE = "
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=left}{BB=center}{BB=right}{BB=justify}
	{BB=bq}{BB=list}{BB=emotes}
	<div class='field-spacer'><!-- --></div>
";

$BBCODE_TEMPLATE_COMMENT = ""; // no buttons on comments by default. 

// $BBCODE_TEMPLATE .= "{BB=blank}";

$BBCODE_TEMPLATE_SIGNATURE = "
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=left}{BB=center}{BB=right}{BB=justify}
	{BB=list}
	<div class='field-spacer'><!-- --></div>
";




// $sc_style['BB_HELP']['pre'] = "<div style='text-align:center'>";
// $sc_style['BB_HELP']['post'] = "</div>";

$BBCODE_TEMPLATE_SUBMITNEWS = "
	
	{BB_HELP}
	<div class='field-spacer'><!-- --></div>
    {BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=left}{BB=center}{BB=right}{BB=justify}
	{BB=list}{BB=nobr}{BB=br}{BB=fontcol}{BB=fontsize}{BB=emotes}{BB=flash}{BB=youtube}
	<div class='field-spacer'><!-- --></div>
";


// --------   Admin Templates ----------------------

$BBCODE_TEMPLATE_ADMIN = "
	<div class='btn-toolbar'>
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=format}{BB=left}{BB=center}{BB=right}{BB=justify}
	{BB=list}{BB=table}{BB=fontcol}{BB=fontsize}{BB=emotes}
	{BB_PREIMAGEDIR}{BB=flash}
	<div class='btn-group'>{BB=preimage}{BB=prefile}{BB=youtube}</div>
	</div>
";

$BBCODE_TEMPLATE_MAILOUT = "
	<div class='btn-toolbar'>
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=left}{BB=center}{BB=right}{BB=justify}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
	{BB_PREIMAGEDIR}{BB=flash}
	<div class='btn-group'>{BB=preimage}{BB=prefile}{BB=shortcode}</div>
	</div>
";

// $BBCODE_TEMPLATE_ADMIN .= "{BB=blank}";

$BBCODE_TEMPLATE_NEWSPOST = "
	<div class='btn-toolbar'>
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=format}{BB=left}{BB=center}{BB=right}{BB=justify}
	{BB=list}{BB=table}{BB=fontcol}{BB=fontsize}{BB=emotes}{BB=flash}
	{BB_PREIMAGEDIR=news}
	<div class='btn-group'>{BB=preimage}{BB=prefile}{BB=youtube}</div>
	</div>
";

$BBCODE_TEMPLATE_CPAGE = "
	<div class='btn-toolbar'>
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=format}{BB=left}{BB=center}{BB=right}{BB=justify}
	{BB=list}{BB=table}{BB=fontcol}{BB=fontsize}{BB=emotes}{BB=flash}
	{BB_PREIMAGEDIR=page}
	<div class='btn-group'>{BB=preimage}{BB=prefile}{BB=youtube}</div>
	</div>
";


