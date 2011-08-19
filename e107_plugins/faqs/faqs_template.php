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

if(!defined("USER_WIDTH"))
{
	define("USER_WIDTH","width:97%");
}

// FAQ - NEW LOOK (all you need is here) -------------------------------------------------------------

	$FAQ_START	= "<div class='faq-start'>\n";
	$FAQ_END	= "<div class='faq-submit-question'>{FAQ_SUBMIT_QUESTION}</div>
					<div class='faq-search'>{FAQ_SEARCH}</div>
					</div>";

	$FAQ_LISTALL_START = "<div><h2 class='faq-listall'>{FAQ_CATEGORY}</h2><ul class='faq-listall'>\n";
	$FAQ_LISTALL_LOOP = "<li class='faq-listall'>{FAQ_QUESTION=expand}</li>\n";
	$FAQ_LISTALL_END = "</ul></div>\n";


// FAQ - CLASSIC LOOK BELOW #####################################################

// FAQ - PARENT LIST ---------------------------------------------------------

	$FAQ_CAT_START = "
		<table class='fborder' style='".USER_WIDTH."'>
		<tr>
			<td colspan='2' style='text-align:center; width:55%' class='fcaption'>".FAQLAN_41."</td>
			<td class='fcaption' style='width:20%;text-align:center'>".FAQLAN_42."</td>
		</tr>";

    $FAQ_CAT_PARENT = "
		<tr>
			<td colspan='3' style='width:55%' class='forumheader'>
			{FAQ_CATEGORY}
			<span class='smalltext'>&nbsp;&nbsp;&nbsp;{FAQ_CAT_DIZ}</span>
			</td>
		</tr>";

	$FAQ_CAT_CHILD = "
		<tr>
			<td class='forumheader3' style='width:30px'>{FAQ_ICON}</td>
			<td class='forumheader2' style='width:95%'>{FAQ_CATLINK}
			<br /><span class='smalltext'>{FAQ_CAT_DIZ}</span></td>
			<td style='width:100px; text-align:center' class='forumheader2'>
			{FAQ_COUNT}
			</td>
		</tr>";

    $FAQ_CAT_END = "</table></div></div>";
	
	
// FAQ - LIST ---------------------------------------------------------

	$FAQ_LIST_START = "
        <table class='fborder' style='".USER_WIDTH."'>       ";

    $FAQ_LIST_LOOP .= "
            <tr>
            <td class='forumheader3' style='width:30px;vertical-align:top'>{FAQ_ICON}</td>
            <td class='forumheader3'>&nbsp;{FAQ_QUESTION_LINK}</td>
            </tr>";

    $FAQ_LIST_END = "</table>";


// FAQ - VIEW ----------------------------------------------------------------
// FAQ - VIEW

	$FAQ_VIEW_TEMPLATE =
        "
        <table class='fborder' style='margin-left:auto;margin-right:auto;padding:0px;".USER_WIDTH.";' >
        <tr>
			<td class='forumheader3' style='vertical-align:top;width:30px'>
				<img src='".e_PLUGIN."faq/images/q.png' alt='' />
			</td>
       	 	<td class='forumheader3' style='vertical-align:top'>
				{FAQ_QUESTION}
			</td>
		</tr>
		
        <tr>
			<td class='forumheader3' style='width:30px;vertical-align:top'>
				<img src='".e_PLUGIN."faq/images/a.png' alt='' />
			</td>
        	<td class='forumheader3'>
				<div class='faq_answer'>{FAQ_ANSWER}</div>
			</td>
		</tr>
		</table>
		<div style='text-align:right; width:100%'>
		{FAQ_EDIT}
		&nbsp;&nbsp;</div>";







?>