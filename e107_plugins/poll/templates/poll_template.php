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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/poll/templates/poll_template.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-03-03 23:00:34 $
|     $Author: stevedunstan $
|
+----------------------------------------------------------------------------+
*/

/* ################################# */
/*		template for polls when user HASN'T voted ...			*/


$POLL_NOTVOTED_START = "
<div style='text-align:center'>
<br />
<b><i>{QUESTION}</i></b>
<hr />
</div>
<br />";

$POLL_NOTVOTED_LOOP = "
{OPTIONBUTTON}<b>{OPTION}</b>
<br /><br />";

$POLL_NOTVOTED_END = "
<div style='text-align:center' class='smalltext'>
{SUBMITBUTTON}
<br /><br />
{AUTHOR}
<br />
{VOTE_TOTAL} {COMMENTS}
<br />
{OLDPOLLS}
</div>";


/* ################################# */
/*		template for polls when user HAS voted ...			*/

$POLL_VOTED_START = "
<div style='text-align:center'>
<br />
<b><i>{QUESTION}</i></b>
<hr />
</div>
<br />
";

$POLL_VOTED_LOOP = "
<b>{OPTION}</b>
<br />{BAR}<br />
<span class='smalltext'>{VOTES} | {PERCENTAGE}</span>
<br /><br />
";

$POLL_VOTED_END = "
<div style='text-align:center' class='smalltext'>
{AUTHOR}
<br />
{VOTE_TOTAL} {COMMENTS}
<br />
{OLDPOLLS}
</div>
";


/* ################################# */
/*		template for polls when user CANNOT vote ...		*/


$POLL_DISALLOWED_START = "
<div style='text-align:center'>
<br />
<b><i>{QUESTION}</i></b>
<hr />
</div>
<br />
";

$POLL_DISALLOWED_LOOP = "
<b>{OPTION}</b>
<br /><br />
";

$POLL_DISALLOWED_END = "
<div style='text-align:center' class='smalltext'>
{DISALLOWMESSAGE}<br /><br />
{VOTE_TOTAL} {COMMENTS}
<br />
{OLDPOLLS}
</div>
";


/* ################################# */
/*		template for forum polls when user HASN'T voted*/

$POLL_FORUM_NOTVOTED_START = "
<div style='text-align:center; margin-left: auto; margin-right: auto;'>
<table class='fborder' style='width: 350px;'>
<tr>
<td class='forumheader' style='width: 100%; text-align: center;'>
<b><i>{QUESTION}</i></b>
</td>
</tr>
<tr>
<td class='forumheader3' style='width: 100%;'>";

$POLL_FORUM_NOTVOTED_LOOP = "
{OPTIONBUTTON}<b>{OPTION}</b>
<br /><br />";

$POLL_FORUM_NOTVOTED_END = "
</td>
</tr>

<tr>
<td class='forumheader' style='width: 100%;'>
<div style='text-align:center' class='smalltext'>
{SUBMITBUTTON}
</div>
</td>
</tr>
</table>
</div>";


/* ################################# */
/*		template for forum polls when user HAS voted		*/

$POLL_FORUM_VOTED_START = "
<div style='text-align:center; margin-left: auto; margin-right: auto;'>
<table class='fborder' style='width: 350px;'>
<tr>
<td class='forumheader' style='width: 100%; text-align: center;'>
<b><i>{QUESTION}</i></b>
</td>
</tr>
<tr>
<td class='forumheader3' style='width: 100%;'>
";

$POLL_FORUM_VOTED_LOOP = "
<b>{OPTION}</b>
<br />{BAR}<br />
<span class='smalltext'>{VOTES} | {PERCENTAGE}</span>
<br /><br />
";

$POLL_FORUM_VOTED_END = "
</td>
</tr>

<tr>
<td class='forumheader' style='width: 100%;'>
<div style='text-align:center' class='smalltext'>
{VOTE_TOTAL}
</div>
</td>
</tr>
</table>
</div>
";

?>