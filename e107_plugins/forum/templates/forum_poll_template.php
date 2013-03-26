<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

/*	v2.x template for forum polls when user has not voted 	*/

$FORUM_POLL_TEMPLATE = array();


$FORUM_POLL_TEMPLATE['form']['start'] = "
<div class='clearfix'>
	<div class='well span6'>
		<div class='control-group'>
			<ul class='nav nav-list'>
				<li class='nav-header'>
					Poll: {QUESTION}
				</li>
";

$FORUM_POLL_TEMPLATE['form']['item'] = "
			<li>
				{ANSWER} 
			</li>";

$FORUM_POLL_TEMPLATE['form']['end'] = "
			</ul>
		</div>
		<div class='control-group'>
			 <div class='controls text-center'>
				{SUBMITBUTTON}	
			</div>
		</div>
	</div>
</div>
";



/*	v2.x template for forum polls when user HAS voted		*/

$FORUM_POLL_TEMPLATE['results']['start'] = "
<div class='clearfix'>
	<div class='well span6'>
		<h5>Poll: {QUESTION}</h5>
";

$FORUM_POLL_TEMPLATE['results']['item'] = "
			<strong>{OPTION}</strong><small class='pull-right'><a href='#' class='e-tip' title=\"{VOTES}\">{PERCENTAGE}</a></small>
			{BAR}
";

$FORUM_POLL_TEMPLATE['results']['end'] = "
		<div class='text-center'><small>{VOTE_TOTAL}</small></div>
	</div>
</div>
";






?>