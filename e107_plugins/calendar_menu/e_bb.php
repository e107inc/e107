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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/e_bb.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-04 21:09:47 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+

BBCode template for calendar menu (pretend we're custom page)
*/
$BBCODE_TEMPLATE_CPAGE = "
	{BB_HELP=ec_event}<br />
	{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=center}{BB=left}{BB=right}
	{BB=bq}{BB=list}{BB=fontcol}{BB=fontsize}{BB=emotes}
	{BB_PREIMAGEDIR=".e_IMAGE."newspost_images/}
	{BB=preimage}{BB=prefile}
";



?>