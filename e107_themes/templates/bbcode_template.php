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
|     $Source: /cvs_backup/e107_0.8/e107_themes/templates/bbcode_template.php,v $
|     $Revision: 1.6 $
|     $Date: 2009-07-07 07:25:27 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
// How to register your own BBcode button.
// Uncomment the 2 commented lines below to see it in action. (only applies to the user area)

// $register_bb['blank'] = array("", "[blank][/blank]","Blank example helper text",e_IMAGE."bbcode/template.png");

$BBCODE_TEMPLATE = "
	<div class='field-spacer'>
		{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}{BB=right}
		{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
	</div>
";

// $BBCODE_TEMPLATE .= "{BB=blank}";


// $sc_style['BB_HELP']['pre'] = "<div style='text-align:center'>";
// $sc_style['BB_HELP']['post'] = "</div>";

$BBCODE_TEMPLATE_SUBMITNEWS = "
	<div class='field-spacer'>
		{BB_HELP}
	</div>
	<div class='field-spacer'>
	    {BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}{BB=right}
		{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}{BB=flash}
	</div>
";


// --------   Admin Templates ----------------------

$BBCODE_TEMPLATE_ADMIN = "
	<div class='field-spacer'>
		{BB_HELP=admin}
	</div>
	<div class='field-spacer'>
		{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}
		{BB=right}{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
		{BB_PREIMAGEDIR=".e_IMAGE."}
		{BB=preimage}{BB=prefile}{BB=flash}
	</div>
";

// $BBCODE_TEMPLATE_ADMIN .= "{BB=blank}";

$BBCODE_TEMPLATE_NEWSPOST = "
	<div class='field-spacer'>
		{BB_HELP=$mode}
	</div>
	<div class='field-spacer'>
		{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}
		{BB=right}{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
		{BB_PREIMAGEDIR=".e_IMAGE."newspost_images/}
		{BB=preimage}{BB=prefile}{BB=flash}
	</div>
";

$BBCODE_TEMPLATE_CPAGE = "
	<div class='field-spacer'>
		{BB_HELP}
	</div>
	<div class='field-spacer'>
		{BB=newpage}
		{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}{BB=right}
		{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
		{BB_PREIMAGEDIR=".e_IMAGE."custom/}
		{BB=preimage}{BB=prefile}{BB=flash}
	</div>
";
?>