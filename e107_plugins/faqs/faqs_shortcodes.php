<?php
/*
 + ----------------------------------------------------------------------------+
 |     e107 website system
 |
 |     Copyright (c) e107 Inc. 2008-2009
 |     http://e107.org
 |
 |     Released under the terms and conditions of the
 |     GNU General Public License (http://gnu.org).
 |
 |     $Source: /cvs_backup/e107_0.8/e107_plugins/faqs/faqs_shortcodes.php,v $
 |     $Revision: 1.1 $
 |     $Date: 2009-11-09 12:57:34 $
 |     $Author: e107coders $
 +----------------------------------------------------------------------------+
 */
if (!defined('e107_INIT')) { exit; }

register_shortcode('faqs_shortcodes', true);
initShortcodeClass('faqs_shortcodes');

class faqs_shortcodes
{
	var $row;
	
	function sc_faq_question($parm='')
	{
		$tp = e107::getParser();
				
		if($parm == 'expand')
		{
			$text = "<a class='e-expandit faq-question' href='faq.php'>".$tp->toHtml($this->row['faq_question'])."</a>
			<div class='e-hideme faq-answer faq_answer'>".$tp->toHTML($this->row['faq_answer'],TRUE)."</div>";		
		}
		else
		{
			$text = $tp->toHtml($this->row['faq_question']);		
		}
		return $text;
	}
	
	function sc_faq_question_link($parm='')
	{
		$tp = e107::getParser();
		return "<a class='faq-question' href='faq.php?cat.".$this->row['faq_info_id'].".".$this->row['faq_id']."' >".$tp -> toHtml($this->row['faq_question'])."</a>";	
	}
	
	function sc_faq_answer()
	{
		$tp = e107::getParser();
		return "<div class='faq-answer'>".$tp -> toHtml($this->row['faq_answer'])."</div>";	
	}
	
	function sc_faq_edit()
	{
		$tp = e107::getParser();
		$faqpref = e107::getPlugConfig('faqs')->getPref();
		if(($faqpref['add_faq'] && $this->row['faq_author'] == USERID) || ADMIN )
		{
		 	return "[ <a href='faq.php?edit.".$this->row['faq_parent'].".".$this->row['faq_id']."'>Edit</a> ]";
		}	
	}
	
	function sc_faq_category()
	{
		$tp = e107::getParser();
		return $tp->toHtml($this->row['faq_info_title']);	
	}
	
	function sc_faq_catlink()
	{
		$tp = e107::getParser();
		return "<a href='".e_SELF."?cat.".$this->row['faq_info_id']."'>".($this->row['faq_info_title'] ? $tp -> toHtml($this->row['faq_info_title']) : "[".NWSLAN_42."]")."</a>";	
	}
	
	function sc_faq_count()
	{
		$tp = e107::getParser();
 		return $this->row['f_count'];
	}
	
	function sc_faq_cat_diz()
	{
		$tp = e107::getParser();
		return $tp->toHtml($this->row['faq_info_about'])."&nbsp;";	
	}

	function sc_faq_icon()
	{
		return "<img src='".e_PLUGIN."faq/images/faq.png'  alt='' />";	
	}

	function sc_faq_submit_question()
	{
		$faqpref = e107::getPlugConfig('faqs')->getPref();
		$frm = e107::getForm();
		
		if (check_class($faqpref['add_faq']))
		{
			$text = "<div class='faq-submit-question-container'><a class='e-expandit faq-submit-question' href='faq.php'>Submit a Question</a>
			<div class='e-hideme faq-submit-question-form'>
			<form method=\"post\" action=\"".e_SELF."?cat.$id.$idx\" id=\"dataform\">
			<div>".$frm->textarea('ask_a_question','').'<br />'.$frm->submit('submit_a_question','Go')."</div>
			</form>
			</div>
			</div>			
			";
			return $text;
		}		
	}
	
	function sc_faq_search()
	{
		$tp = e107::getParser();
		return "<div style='text-align:center'><br />".$tp->parseTemplate("{SEARCH=faq}")."</div>";
	}

}


?>