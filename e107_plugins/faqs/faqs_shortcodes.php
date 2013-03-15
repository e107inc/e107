<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */
 
if (!defined('e107_INIT')) { exit; }

/**
 *
 * @package     e107
 * @subpackage  faqs
 * @version     $Id$
 * @author      e107inc
 *
 *	FAQ shortcodes
 */
 

class faqs_shortcodes extends e_shortcode
{
	public $counter = 1;
	
	// Simply FAQ count when needed. 
	function sc_faq_counter($parm='')
	{
		return $this->counter;	
	}
	
	
	function sc_faq_question($parm='')
	{
		$tp = e107::getParser();
		$parm = eHelper::scDualParams($parm);
		$param = $parm[1];
		$params = $parm[2];
		
		if($param == 'expand')
		{
			$tags = '';
			if(vartrue($params['tags']) && $this->var['faq_tags'])
			{
				$tags = "<div class='faq-tags'>".$this->sc_faq_tags()."</div>";	
			}
			$id = "faq_".$this->var['faq_id'];
			$text = "<a class='e-expandit faq-question' href='#{$id}'>".$tp->toHTML($this->var['faq_question'],true)."</a>
			<div id='{$id}' class='e-hideme faq-answer faq_answer'>".$tp->toHTML($this->var['faq_answer'],TRUE).$tags."</div>
			";	

		}
		else
		{
			$text = $tp->toHTML($this->var['faq_question'],true);		
		}
		return $text;
	}
	
	function sc_faq_question_link($parm='')
	{
		$tp = e107::getParser();
		return "<a class='faq-question' href='". e107::getUrl()->create('faqs/view/item', array('id' => $this->var['faq_id']))."' >".$tp -> toHTML($this->var['faq_question'])."</a>";	
	}
	
	function sc_faq_answer()
	{
		return e107::getParser()->toHTML($this->var['faq_answer'],true); 
	}
	
	
	function sc_faq_tags($parm='')
	{
		$tags = $this->var['faq_tags'];
		if(!$tags) return '';
		
		if(!$parm) $parm = '&nbsp;|&nbsp;';
		
		$ret = $urlparms = array();
		if($this->category) $urlparms['category'] = $this->category;
		$tags = array_map('trim', explode(',', $tags));
		foreach ($tags as $tag) 
		{
			$urlparms['tag'] = $tag;
			$url = e107::getUrl()->create('faqs/list/all', $urlparms);
			$tag = htmlspecialchars($tag, ENT_QUOTES, 'utf-8');
			$ret[] = '<a href="'.$url.'" title="'.$tag.'">'.$tag.'</a>';
		}
		
		return implode($parm, $ret);
	}
	
	function sc_faq_current_tag($parm='')
	{
		if(!$this->tag) return '';
		
		$tag = $this->tag;
		if($parm == 'raw') return $tag;
		
		$urlparms = array();
		if($this->category) $urlparms['category'] = $this->category;
		$urlparms['tag'] = $tag;
		$url = e107::getUrl()->create('faqs/list/all', $urlparms);
		if($parm == 'url') return $url;
		
		return '<a href="'.$url.'" title="'.$tag.'">'.$tag.'</a>';
	}
	
	function sc_faq_edit()
	{
		$tp = e107::getParser();
		$faqpref = e107::getPlugConfig('faqs')->getPref();
		if(($faqpref['add_faq'] && $this->var['faq_author'] == USERID) || ADMIN )
		{
			// UNDER CONSTRUCTION
		 	//return "[ <a href='faqs.php?edit.".$this->var['faq_parent'].".".$this->var['faq_id']."'>Edit</a> ]";
		}	
	}
	
	function sc_faq_category($parm = '')
	{
		$tp = e107::getParser();
		if($parm == 'extend' && $this->tag)
		{
			return "<a href='".$this->sc_faq_current_tag('url')."'>".$tp->toHTML($this->var['faq_info_title'])." &raquo; ".$this->sc_faq_current_tag('raw')."</a>";
		}
		return "<a href='".e107::getUrl()->create('faqs/list/all', array('category' => $this->var['faq_info_id']))."'>".$tp->toHTML($this->var['faq_info_title'])."</a>";	
	}
	
	function sc_faq_caturl()
	{
		return e107::getUrl()->create('faqs/list/all', array('category' => $this->var['faq_info_id']));	
	}
	
	function sc_faq_count()
	{
		$tp = e107::getParser();
 		return $this->var['f_count'];
	}
	
	function sc_faq_cat_diz()
	{
		$tp = e107::getParser();
		return $tp->toHTML($this->var['faq_info_about'], true);	
	}

	function sc_faq_icon()
	{
		return "<img src='".e_PLUGIN_ABS."faq/images/faq.png'  alt='' />";	
	}

	function sc_faq_submit_question()
	{
		return ''; // UNDER CONSTRUCTION
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
		return ''; // UNDER CONSTRUCTION
		$tp = e107::getParser();
		return "<div style='text-align:center'><br />".$tp->parseTemplate("{SEARCH=faqs}")."</div>";
	}

}
