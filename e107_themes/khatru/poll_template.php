<?php

if (!defined('e107_INIT')) { exit; }

$POLL_NOTVOTED_START = "
<div style='text-align:center'>
<p>
{QUESTION}
</p>
</div>
<p>
<br />
";

$POLL_NOTVOTED_LOOP = "
{OPTIONBUTTON}{OPTION}
<br /><br />";

$POLL_NOTVOTED_END = "
</p>
<div style='text-align:center' class='smalltext'>
<p>
{SUBMITBUTTON}
<br /><br />
{AUTHOR}
<br />
{VOTE_TOTAL} {COMMENTS}
<br />
{OLDPOLLS}
</p>
</div>";

/* ################################# */
/*		template for polls when user HAS voted ...			*/

$POLL_VOTED_START = "
<div style='text-align:center'>
{QUESTION}
</div>
<br />
";

$POLL_VOTED_LOOP = "
<b>{OPTION}</b>
<br /><div class='spacer'>{BAR}</div><br />
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
{QUESTION}
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
<div id='pollquestion'>{QUESTION}</div>
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
<div id='pollquestion'>{QUESTION}</div>
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