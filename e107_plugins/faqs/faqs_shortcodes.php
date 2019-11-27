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
	public $item = false;
	private $share = false;
	private $datestamp = false;
	private $questionCharLimit = 255;


	public function __construct()
	{
		$pref = e107::pref('faqs');


		if(!empty($pref['display_social']) && e107::isInstalled('social')==true)
		{
			$this->share = true;
		}

		if(!empty($pref['display_datestamp']))
		{
			$this->datestamp = true;
		}

		if(!empty($pref['submit_question_char_limit']))
		{
			$this->questionCharLimit = intval($pref['submit_question_char_limit']);
		}

	}
	
	// Simply FAQ count when needed. 
	function sc_faq_counter($parm='')
	{
		return $this->counter;	
	}

	function sc_faq_hide($parm=null)
	{
		if(empty($parm))
		{
			$parm = 'collapse';
		}

		return ($this->item != $this->var['faq_id']) ? $parm : '';
	}
	
	
	function sc_faq_question($parm='')
	{
		$tp = e107::getParser();
		$parm = eHelper::scDualParams($parm);
		$param = $parm[1];
		$params = $parm[2];

		$new = e107::pref('faqs','new',3);

		$newDate = strtotime($new." days ago");

		$faqNew = ($this->var['faq_datestamp'] > $newDate) ? " faq-new" : "";

		if($param == 'expand' && !empty($this->var['faq_answer']))
		{

			$id         = "faq_".$this->var['faq_id'];
			$url        = e107::url('faqs','item', $this->var, 'full');
			$question   = $tp->toHTML($this->var['faq_question'],true,'TITLE');
			$hide       = ($this->item != $this->var['faq_id']) ? 'e-hideme' : '';

			$text = "
			<a class='e-expandit faq-question{$faqNew}' href='#{$id}'>".$question."</a>
			<div id='{$id}' class='".$hide." faq-answer faq_answer clearfix {$faqNew}'>";

			$text .=  $tp->toHTML($this->var['faq_answer'],true,'BODY');

			$text .= "<div class='faq-extras'>";

			if(vartrue($params['tags']) && $this->var['faq_tags'])
			{
				$text .= "<div class='faq-tags'>".LAN_FAQS_001.": ".$this->sc_faq_tags()."</div>";
			}

			if($this->datestamp == true)
			{
				$text .= "<div class='faq-datestamp'>".$tp->toDate($this->var['faq_datestamp'])."</div>";
			}

			if($this->share == true)
			{
				$text .= "<div class='faq-share'>".$tp->parseTemplate("{SOCIALSHARE: size=sm&type=basic&url=".$url."&title=".$question."&tags=".$this->var['faq_tags']."}",true)."</div>";
			}

			$text .= "</div></div>
			";

		}
		else
		{
			$text = $tp->toHTML($this->var['faq_question'],true, 'TITLE');
		}
		return $text;
	}


	function sc_faq_share($parm=null)
	{
		$tp = e107::getParser();

		$url        = e107::url('faqs','item', $this->var, 'full');
		$question   = $tp->toHTML($this->var['faq_question'],true,'TITLE');

		return $tp->parseTemplate("{SOCIALSHARE: size=sm&type=basic&url=".$url."&title=".$question."&tags=".$this->var['faq_tags']."}",true);

	}

	
	function sc_faq_question_link($parm='')
	{
		$tp = e107::getParser();
		return "<a class='faq-question' href='". e107::url('faqs', 'item', $this->var)."' >".$tp -> toHTML($this->var['faq_question'],true,'TITLE')."</a>";
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
	//	$url = e107::getUrl()->create('faqs/list/all', $urlparms);
		$url = e107::url('faqs', 'tag', $urlparms);
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
	
	/* {FAQ_CATEGORY_ID} */ 
	function sc_faq_category_id($parm = '')
	{
	  return $this->var['faq_parent'];
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
		return e107::url('faqs', 'category', $this->var);
	}


	function sc_faq_datestamp($parm)
	{
		$type = vartrue($parm, 'relative');
		return e107::getParser()->toDate($this->var['faq_datestamp'], $type);
	}

	function sc_faq_caption()
	{

		$customCaption = e107::pref('faqs', 'page_title');

		if(!empty($customCaption[e_LANGUAGE]))
		{
			return e107::getParser()->toHTML($customCaption[e_LANGUAGE],true);
		}

		return LAN_PLUGIN_FAQS_FRONT_NAME;
	}


	function sc_faq_count()
	{
		$faqTotal = e107::pref('faqs', 'display_total');

		if(!empty($faqTotal))
		{
			return "<span class='faq-total'>(".($this->counter -1).")</span>";
		}

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

	function sc_faq_submit_question($parms)
	{

		$faqpref = e107::pref('faqs');

		if(empty($faqpref['submit_question_language']) && e107::getPref('sitelanguage') != e_LANGUAGE)
		{
			return false;
		}


		if(!empty($parms['expand']) && $faqpref['submit_question'] != e_UC_NOBODY)
		{
			$hide = 'e-hideme';
			$button = "<a class='btn btn-primary e-expandit faq-submit-question' href='#form-ask-a-question'>".LAN_FAQS_ASK_A_QUESTION."</a>";
		}
		else
		{
			$hide = "";
			$button = "";
		}

		if ($faqpref['submit_question'] != e_UC_NOBODY)
		{
			$frm = e107::getForm();

			$text = $button;

			$text .= "<div id='form-ask-a-question' class='alert alert-info alert-block ".$hide." form-group faq-submit-question-form'>";

			if(check_class($faqpref['submit_question']))
			{
				$text .= $frm->open('faq-ask-question','post');
				//TODO LAN ie. [x] character limit.
				$text .= "<div>".$frm->textarea('ask_a_question','',3, 80 ,array('maxlength' =>$this->questionCharLimit, 'size' =>'xxlarge', 'placeholder' =>LAN_FAQS_012, 'wrap' =>'soft'))."
				<div class='faq-char-limit'><small>".$this->questionCharLimit." ".LAN_FAQS_013."</small></div>".$frm->submit('submit_a_question',LAN_SUBMIT)."</div>";

				$text .= $frm->close();
			}
			elseif($faqpref['submit_question'] == e_UC_MEMBER)
			{
				$srp = array(
					'[' => "<a href='".e_SIGNUP."'>",
					']' => "</a>"
				);

				$text .= str_replace(array_keys($srp), array_values($srp), LAN_FAQS_014);
			}
			else
			{

			$text .= LAN_FAQS_015;
			}

			$text .= "</div>";

			return $text;
		}

	}


	function sc_faq_submit_question_list()
	{
		$faqpref = e107::pref('faqs');

		if (check_class($faqpref['submit_question']))
		{
			$tp = e107::getParser();

			$list = e107::getDb()->retrieve('faqs','faq_question,faq_datestamp',"faq_answer='' AND faq_author_ip = '".USERIP."' ORDER BY faq_datestamp DESC ", true);

			$text = "";

			if(!empty($list))
			{

				$text = "<div class='alert alert-warning alert-block faq-submit-question-list'>";
				$text .= "<h4>".LAN_FAQS_016."</h4>";
				$text .= "<ul>";

				foreach($list as $row)
				{
					$text .= "<li>".$tp->toHTML($row['faq_question'],true)."</li>";
				}

				$text .= "</ul>";
				$text .= "</div>";

			}

			return $text;
		}




	}

	
	function sc_faq_search($parm='')
	{
		
			$frm = e107::getForm();
			$tp = e107::getParser();

			$target = e107::url('faqs','search');
			
			$text = $frm->open('faq-search-form','get', $target);
			$text .= '<span class="input-group e-search">';
			$text .= $frm->text('srch', $_GET['srch'], 20,'class=search-query&placeholder='.LAN_SEARCH).'
   			 <span class="input-group-btn"><button class="btn btn-primary"  type="submit">'.$tp->toGlyph('fa-search').'</button>';
			$text .= '</span></span>';
			$text .= $frm->close();

			return $text;

	}

	
	function sc_faq_breadcrumb() //TODO Category Detection. and proper SEF Urls with category names. 
	{
		$array = array();
	//	$array[0] = array('url'=> e_REQUEST_SELF, 'text'=>LAN_PLUGIN_FAQS_NAME);
		$array[0] = array('url'=> e107::url('faqs','index'), 'text'=>LAN_PLUGIN_FAQS_NAME);

		if(!empty($_GET['srch']))
		{
			$array[1] = array('url'=> null, 'text'=>LAN_FAQS_002 .": ".e107::getParser()->filter($_GET['srch'], 'w'));
		}
			
		return e107::getForm()->breadcrumb($array);
		
	}
	
}
