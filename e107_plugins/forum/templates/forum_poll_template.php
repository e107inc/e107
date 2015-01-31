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
	<div class='well col-md-8 span6'>
		<div class='form-group control-group'>
			Poll: {QUESTION}
			
";

$FORUM_POLL_TEMPLATE['form']['item'] = "
			<div class='radio'>
				{ANSWER} 
			</div>";

$FORUM_POLL_TEMPLATE['form']['end'] = "
			
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