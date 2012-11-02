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
 |     $Revision$
 |     $Date$
 |     $Author$
 +----------------------------------------------------------------------------+
 */
if (!defined('e107_INIT')) { exit; }

// register_shortcode('faqs_shortcodes', true);
// initShortcodeClass('faqs_shortcodes');

class faqs_shortcodes extends e_shortcode
{
	// var $var;
	
	function sc_faq_question($parm='')
	{
		$tp = e107::getParser();
				
		if($parm == 'expand')
		{
			$id = "faq_".$this->var['faq_id'];
			$text = "<a class='e-expandit faq-question' href='#{$id}'>".$tp->toHtml($this->var['faq_question'])."</a>
			<div id='{$id}' class='e-hideme faq-answer faq_answer'>".$tp->toHTML($this->var['faq_answer'],TRUE)."</div>";		
		}
		else
		{
			$text = $tp->toHtml($this->var['faq_question']);		
		}
		return $text;
	}
	
	function sc_faq_question_link($parm='')
	{
		$tp = e107::getParser();
		return "<a class='faq-question' href='faqs.php?cat.".$this->var['faq_info_id'].".".$this->var['faq_id']."' >".$tp -> toHtml($this->var['faq_question'])."</a>";	
	}
	
	function sc_faq_answer()
	{
		$tp = e107::getParser();
		return "<div class='faq-answer'>".$tp -> toHtml($this->var['faq_answer'])."</div>";	
	}
	
	function sc_faq_edit()
	{
		$tp = e107::getParser();
		$faqpref = e107::getPlugConfig('faqs')->getPref();
		if(($faqpref['add_faq'] && $this->var['faq_author'] == USERID) || ADMIN )
		{
		 	return "[ <a href='faqs.php?edit.".$this->var['faq_parent'].".".$this->var['faq_id']."'>Edit</a> ]";
		}	
	}
	
	function sc_faq_category()
	{
		$tp = e107::getParser();
		return "<a href='".e_SELF."?cat.".$this->var['faq_info_id']."'>".$tp->toHtml($this->var['faq_info_title'])."</a>";	
	}
	
	function sc_faq_catlink()
	{
		$tp = e107::getParser();
		return "<a href='".e_SELF."?cat.".$this->var['faq_info_id']."'>".($this->var['faq_info_title'] ? $tp -> toHtml($this->var['faq_info_title']) : "[".NWSLAN_42."]")."</a>";	
	}
	
	function sc_faq_count()
	{
		$tp = e107::getParser();
 		return $this->var['f_count'];
	}
	
	function sc_faq_cat_diz()
	{
		$tp = e107::getParser();
		return $tp->toHtml($this->var['faq_info_about'])."&nbsp;";	
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
			$text = "<div class='faq-submit-question-container'><a class='e-expandit faq-submit-question' href='faqs.php'>Submit a Question</a>
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
		return "<div style='text-align:center'><br />".$tp->parseTemplate("{SEARCH=faqs}")."</div>";
	}

}


?>