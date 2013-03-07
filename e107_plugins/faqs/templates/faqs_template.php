<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/faqs/faqs_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

// FAQ - NEW LOOK (all you need is here) -------------------------------------------------------------

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
	<h2 class='faq-listall'>{FAQ_CATEGORY}</h2>
	<ul class='faq-listall'>
";
$FAQS_TEMPLATE['all']['item'] = "
		<li class='faq-listall'>{FAQ_QUESTION=expand}</li>
";
$FAQS_TEMPLATE['all']['end'] = "
	</ul>
</div>
";


// FAQ - CLASSIC LOOK BELOW #####################################################

// FAQ - PARENT LIST ---------------------------------------------------------

	$FAQS_TEMPLATE['cat']['start'] = "
		<table class='table fborder' style='".USER_WIDTH."'>
		<tr>
			<td colspan='2' style='text-align:center; width:55%' class='fcaption'>".FAQLAN_41."</td>
			<td class='fcaption' style='width:20%;text-align:center'>".FAQLAN_42."</td>
		</tr>";

    $FAQS_TEMPLATE['cat']['parent'] = "
		<tr>
			<td colspan='3' style='width:55%' class='forumheader'>
			{FAQ_CATEGORY}
			<span class='smalltext'>&nbsp;&nbsp;&nbsp;{FAQ_CAT_DIZ}</span>
			</td>
		</tr>";

	$FAQS_TEMPLATE['cat']['child'] = "
		<tr>
			<td class='forumheader3' style='width:30px'>{FAQ_ICON}</td>
			<td class='forumheader2' style='width:95%'>{FAQ_CATLINK}
			<br /><span class='smalltext'>{FAQ_CAT_DIZ}</span></td>
			<td style='width:100px; text-align:center' class='forumheader2'>
			{FAQ_COUNT}
			</td>
		</tr>";

    $FAQS_TEMPLATE['cat']['end'] = "</table></div></div>";
	
	
// FAQ - LIST ---------------------------------------------------------

	$FAQS_TEMPLATE['list']['start'] = "
        <table class='table fborder' style='".USER_WIDTH."'>       ";

    $FAQS_TEMPLATE['list']['item'] .= "
            <tr>
            <td class='forumheader3' style='width:30px;vertical-align:top'>{FAQ_ICON}</td>
            <td class='forumheader3'>&nbsp;{FAQ_QUESTION_LINK}</td>
            </tr>";

    $FAQS_TEMPLATE['list']['end'] = "</table>";


// FAQ - VIEW ----------------------------------------------------------------
// FAQ - VIEW

	$FAQS_TEMPLATE['view']['start'] = "
        <table class='table fborder' style='margin-left:auto;margin-right:auto;padding:0px;".USER_WIDTH.";' >
        <tr>
			<td class='forumheader3' style='vertical-align:top;width:30px'>
				<img src='".e_PLUGIN_ABS."faqs/images/q.png' alt='' />
			</td>
       	 	<td class='forumheader3' style='vertical-align:top'>
				{FAQ_QUESTION}
			</td>
		</tr>
		
        <tr>
			<td class='forumheader3' style='width:30px;vertical-align:top'>
				<img src='".e_PLUGIN_ABS."faqs/images/a.png' alt='' />
			</td>
        	<td class='forumheader3'>
				<div class='faq_answer'>{FAQ_ANSWER}</div>
			</td>
		</tr>
		</table>
		<div style='text-align:right; width:100%'>
			{FAQ_EDIT}
		</div>
";
