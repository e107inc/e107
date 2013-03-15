<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

$FAQS_TEMPLATE['start']	= "
<div class='faq-start'>
";

$FAQS_TEMPLATE['end']	= "
	<div class='faq-submit-question'>{FAQ_SUBMIT_QUESTION}</div>
	<div class='faq-search'>{FAQ_SEARCH}</div>
</div>
";

$FAQS_TEMPLATE['all']['start'] = "
<div>
	<h2 class='faq-listall'>{FAQ_CATEGORY=extend}</h2>
	<ul class='faq-listall'>
";
$FAQS_TEMPLATE['all']['item'] = "
		<li class='faq-listall'>{FAQ_QUESTION=expand|tags=1}</li>
";
$FAQS_TEMPLATE['all']['end'] = "
	</ul>
</div>
";

