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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/forum/templates/forum_post_template.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-01-28 10:38:48 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

// the user box and subject box are not always displayed, therefore we need to define them /in case/ they are, if not they'll be ignored.

$userbox = "<tr>
<td class='forumheader2' style='width:20%'>".LAN_61."</td>
<td class='forumheader2' style='width:80%'>
<input class='tbox' type='text' name='anonname' size='71' value='".$anonname."' maxlength='20' />
</td>
</tr>";

$subjectbox = "<tr>
<td class='forumheader2' style='width:20%'>".LAN_62."</td>
<td class='forumheader2' style='width:80%'>
<input class='tbox' type='text' name='subject' size='71' value='".$subject."' maxlength='100' />
</td>
</tr>";

// ------------

$FORUMPOST = "
<div style='text-align:center'>
{FORMSTART}
<table style='width:95%' class='fborder'>
<tr>
<td colspan='2' class='fcaption'>{BACKLINK}
</td>
</tr>
{USERBOX}
{SUBJECTBOX}
<tr>
<td class='forumheader2' style='width:20%'>{POSTTYPE}</td>
<td class='forumheader2' style='width:80%'>
{POSTBOX}<br />{EMOTES}<br />{EMAILNOTIFY}<br />{POSTTHREADAS}
</td>
</tr>

<tr>
<td colspan='2' class='fcaption'>".LAN_4."</td>
</tr>
<tr>
<td colspan='2' class='forumheader3'>
<span class='smalltext'>".LAN_386."
</td>
</tr>
{POLL}
<tr>
<td colspan='2' class='fcaption'>".LAN_390."</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".LAN_392."</td>
<td style='width:80%' class='forumheader3'>
{FILEATTACH}
</td>
</tr>

<tr style='vertical-align:top'>
<td colspan='2' class='forumheader' style='text-align:center'>
{BUTTONS}
</table>
{FORMEND}
</div>
{FORUMJUMP}
";
	
?>