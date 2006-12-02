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
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:36:12 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
// How to register your own BBcode button.
// Uncomment the 2 commented lines below to see it in action. (only applies to the user area)

// $register_bb['blank'] = array("", "[blank][/blank]","Blank example helper text",e_IMAGE."generic/bbcode/template.png");

$BBCODE_TEMPLATE = "
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}{BB=right}
	{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
";

// $BBCODE_TEMPLATE .= "{BB=blank}";


// $sc_style['BB_HELP']['pre'] = "<div style='text-align:center'>";
// $sc_style['BB_HELP']['post'] = "</div>";



// --------   Admin Templates ----------------------

$BBCODE_TEMPLATE_ADMIN = "
	{BB_HELP=admin}<br />
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}
	{BB=right}{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
	{BB_PREIMAGEDIR=".e_IMAGE."}
	{BB=preimage}{BB=prefile}{BB=flash}
";

// $BBCODE_TEMPLATE_ADMIN .= "{BB=blank}";

$BBCODE_TEMPLATE_NEWSPOST = "
	{BB_HELP=$mode}<br />
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}
	{BB=right}{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
	{BB_PREIMAGEDIR=".e_IMAGE."newspost_images/}
	{BB=preimage}{BB=prefile}{BB=flash}
";

$BBCODE_TEMPLATE_CPAGE = "
	{BB_HELP}<br />
	{BB=newpage}
	{BB=links}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}{BB=right}
	{BB=bq}{BB=code}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
	{BB_PREIMAGEDIR=".e_IMAGE."custom/}
	{BB=preimage}{BB=prefile}{BB=flash}
";



?>