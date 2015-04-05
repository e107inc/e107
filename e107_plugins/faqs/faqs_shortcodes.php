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
				$tags = "<div class='faq-tags'>".LAN_FAQS_TAGS.": ".$this->sc_faq_tags()."</div>";
			}
			$id = "faq_".$this->var['faq_id'];
			$text = "<a class='e-expandit faq-question' href='#{$id}'>".$tp->toHTML($this->var['faq_question'],true,'TITLE')."</a>
			<div id='{$id}' class='e-hideme faq-answer faq_answer clearfix'>".$tp->toHTML($this->var['faq_answer'],true,'BODY').$tags."</div>
			";	

		}
		else
		{
			$text = $tp->toHTML($this->var['faq_question'],true, 'TITLE');
		}
		return $text;
	}
	
	function sc_faq_question_link($parm='')
	{
		$tp = e107::getParser();
		return "<a class='faq-question' href='". e107::getUrl()->create('faqs/view/item', array('id' => $this->var['faq_id']))."' >".$tp -> toHTML($this->var['faq_question'],true,'TITLE')."</a>";
	}
	
	function sc_faq_answer()
	{
		return e107::getParser()->toHTML($this->var['faq_answer'],true,'BODY'); 
	}
	
	
	function sc_faq_tags($parm='')
	{
		$tags = $this->var['faq_tags'];
		if(!$tags) return '';
		
		if(!$parm) $parm = ' ';
		
		$ret = $urlparms = array();
		if($this->category) $urlparms['category'] = $this->category;
		$tags = array_map('trim', explode(',', $tags));
		foreach ($tags as $tag) 
		{
			$urlparms['tag'] = $tag;
		//	$url = e107::getUrl()->create('faqs/list/all', $urlparms);
			$url = e107::url('faqs', 'tag',$urlparms);
			$tag = htmlspecialchars($tag, ENT_QUOTES, 'utf-8');
			$ret[] = '<a href="'.$url.'" title="'.$tag.'"><span class="label label-info">'.$tag.'</span></a>';
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
	//	$tp = e107::getParser();
	//	return $tp->toHTML($this->var['faq_info_title']);
		
		
		$tp = e107::getParser();
		$url = e107::url('faqs','category', $this->var); //@See faqs/e_url.php 
		return "<a href='".$url."'>".$tp->toHTML($this->var['faq_info_title'])."</a>";	
		/*

		return "<a href='".e107::getUrl()->create('faqs/list/all', array('category' => $this->var['faq_info_id']))."'>".$tp->toHTML($this->var['faq_info_title'])."</a>";	
		
		
		
		$tp = e107::getParser();
		if($parm == 'extend' && $this->tag)
		{
			return "<a href='".$this->sc_faq_current_tag('url')."'>".$tp->toHTML($this->var['faq_info_title'])." &raquo; ".$this->sc_faq_current_tag('raw')."</a>";
		}
		
		if($parm == 'raw')
		{
			return $tp->toHTML($this->var['faq_info_title']);	
		}
		
		return "<a href='".e107::getUrl()->create('faqs/list/all', array('category' => $this->var['faq_info_id']))."'>".$tp->toHTML($this->var['faq_info_title'])."</a>";	
	*/
	}
	
	function sc_faq_category_description($parm='')
	{
		$tp = e107::getParser();
		return $tp->toHTML($this->var['faq_info_about'],true, 'BODY');	
		
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

		$faqpref = e107::pref('faqs');

		if (check_class($faqpref['submit_question']))
		{
			$frm = e107::getForm();

			$text = "<a class='btn btn-primary e-expandit faq-submit-question' href='#ask-a-question'>Ask a Question</a>
			<div id='ask-a-question' class='alert alert-info alert-block e-hideme form-group faq-submit-question-form'>";

			$text .= $frm->open('faq-ask-question','post');

			$text .= "<div>".$frm->text('ask_a_question','',255,array('size'=>'xxlarge','placeholder'=>'Type your question here..')).'<br />'.$frm->submit('submit_a_question','Submit')."</div>";

			$text .= $frm->close();

			$text .= "</div>";

			return $text;
		}

	}
	
	function sc_faq_search($parm='')
	{
		
		if($parm == 'ajax') //TODo Ajax JS. 
		{
			$frm = e107::getForm();
			$tp = e107::getParser();
			
			$text = $frm->open('faq-search-form','get', e_REQUEST_SELF);
			$text .= '<span class="input-group e-search">';
			$text .= $frm->text('srch', $_GET['srch'], 20,'class=search-query&placeholder='.LAN_SEARCH).'
   			 <span class="input-group-btn"><button class="btn btn-primary"  type="submit">'.$tp->toGlyph('fa-search').'</button>';
			$text .= '</span></span>';
			$text .= $frm->close();	
			return $text;
		}
		
		
		return ''; // UNDER CONSTRUCTION
	//	$tp = e107::getParser();
	//	return "<div style='text-align:center'><br />".$tp->parseTemplate("{SEARCH=faqs}")."</div>";
	}

	
	function sc_faq_breadcrumb() //TODO Category Detection. and proper SEF Urls with category names. 
	{
		$array = array();
		$array[0] = array('url'=> e_REQUEST_SELF, 'text'=>LAN_PLUGIN_FAQS_NAME);
			
		return e107::getForm()->breadcrumb($array);
		
	}
	
}
