<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* Custom Pages shortcode batch
*/

if (!defined('e107_INIT')) { exit; }

/**
 *	@package    e107
 *	@subpackage	shortcodes
 *	@version 	$Id$
 *
 *	Shortcodes for custom page display
 */
class cpage_shortcodes extends e_shortcode
{
	// var $var; // parsed DB values

	function sc_cpagetitle($parm='')
	{
		return e107::getParser()->toHTML($this->getParserVars()->title, true, 'TITLE');
	}
	
	function sc_cpagesubtitle()
	{
		$subtitle = $this->getParserVars()->sub_title;
		return $subtitle ? e107::getParser()->toHTML($subtitle, true, 'TITLE') : '';
	}


	function sc_cpagebody($parm='')
	{
		// already parsed
		return $this->getParserVars()->text;
	}

	function sc_cpageauthor($parm)
	{
		$parms = eHelper::scParams($parm);
		$author = '';
		$url = e107::getUrl()->create('user/profile/view', array('name' => $this->page['user_name'], 'id' => $this->page['user_id']));
		
		if(isset($parms['url']))
		{
			return $url;
		}
		
		if($this->page['page_author'])
		{
			// currently this field used as Real Name, no matter what the db name says
			if($this->page['user_login'] && !isset($parms['user'])) $author = $this->page['user_login'];
			elseif($this->page['user_name']) $author = preg_replace('/[^\w\pL\s]+/u', ' ', $this->page['user_name']);
		}
		
		if(empty($author)) return '';
		
		
		
		if(isset($parms['nolink']))
		{
			return $author;
		}
		//TODO title lan
		return '<a class="cpage-author" href="'.$url.'" title="">'.$author.'</a>';
	}

	function sc_cpagedate($parm)
	{
		if(empty($parm))
		{
			return e107::getDateConvert()->convert_date($this->page['page_datestamp'], 'long');
		}
		return e107::getDateConvert()->convert_date($this->page['page_datestamp'], $parm);
	}

	function sc_cpageid()
	{
		return $this->page['page_id'];
	}

	function sc_cpageanchor()
	{
		$frm = e107::getForm();
		return $frm->name2id($this->page['page_title']);
	}

	// Not a shortcode really, as it shouldn't be cached at all :/
	function cpagecomments()
	{
		$com = $this->getParserVars()->comments;
		//if($parm && isset($com[$parm])) return $com[$parm];
		return $com['comment'].$com['comment_form'];
	}
	
	function sc_cpagenav()
	{
		return $this->getParserVars()->np;
	}
	
	function sc_cpagerating()
	{
		return $this->getParserVars()->rating;
	}
	
	function sc_cpagemessage()
	{
		return e107::getMessage()->render();
	}

	/**
	 * Auto-thumbnailing now allowed.
	 * New sc parameter standards
	 * Exampes: 
	 * - {CPAGETHUMBNAIL=e_MEDIA/images/someimage.jpg|type=tag&w=200} render link with thumbnail max width 200px
	 * - {CPAGETHUMBNAIL=images/someimage.jpg|w=200} same as above
	 * - {CPAGETHUMBNAIL=images/someimage.jpg|type=src&aw=200&ah=200} return thumb link only, size forced to 200px X 200px (smart thumbnailing close to how Facebook is doing it)
	 * 
	 * @see eHelper::scDualParams()
	 * @see eHelper::scParams()
	 */
	function sc_cpagethumbnail($parm = '')
	{
		$parms = eHelper::scDualParams($parm);
		if(empty($parms[1])) return '';
		
		$tp = e107::getParser();
		$path = rawurldecode($parms[1]);
		
		if(substr($path, 0, 2) === 'e_') $path = str_replace($tp->getUrlConstants('raw'), $tp->getUrlConstants('sc'), $path);
		elseif($path[0] !== '{') $path = '{e_MEDIA}'.$path;
		
		$thumb = $tp->thumbUrl($path);
		$type = varset($parms[2]['type'], 'tag');

		switch($type)
		{
			case 'src':
				return $thumb;
			break;

			case 'link':
				return '<a href="'.$tp->replaceConstants($path, 'abs').'" class="cpage-image" rel="external image"><img class="cpage-image" src="'.$src.'" alt="'.varset($parms[1]['alt']).'" /></a>';
			break;

			case 'tag':
			default:
				return '<img class="cpage-image" src="'.$thumb.'" alt="'.varset($parms[1]['alt']).'" />';
			break;
		}
	}
	
	// For Future Use..
	function sc_cpageimage($parm = '')
	{
		list($num,$size) = explode("|",$parm);
		if($this->page['page_images'])
		{
			$img = explode(",",$this->page['page_images']);
			
		}	
	}

	function sc_cpagelink($parm)
	{
		$url = $this->sc_cpageurl();
		
		if($parm == 'href' || !$url)
		{
			return $url;
		}
		return '<a class="cpage" href="'.$url.'">'.$this->sc_cpagetitle().'</a>';
	}
	
	function sc_cpagebutton($parm)
	{
		$url = $this->sc_cpageurl();
		
		if($parm == 'href' || !$url)
		{
			return $url;
		}
		
		parse_str($parm,$options);
		
		$text = vartrue($options['text'], "Read more..");
		$size = vartrue($options['size'], "");
		$inc = ($size) ? " btn-".$size : "";
		
		return '<a class="cpage btn'.$inc.'" href="'.$url.'">'.$text.'</a>';
	}	
	
	
	function sc_cmenutitle($parm='')
	{
		$tp 	= e107::getParser(); 
		$title 	= $tp->toGlyph($this->page['menu_title']); // (preg_replace('/i_([\w]*)/',"<i class='i_$1'></i>",$this->page['menu_title']); 
		
		return $tp->toHTML($title, true, 'TITLE');
	}	


	function sc_cmenubody($parm='')
	{
		// print_a($this);
		return e107::getParser()->toHTML($this->page['menu_text'], true, 'BODY');
	}
	
	
	function sc_cmenuimage($parm='')
	{
		// print_a($this);
		$img = e107::getParser()->thumbUrl($this->page['menu_image']);
		if($parm == 'url')
		{
			return $img;	
		}
		
		return "<img src='".$img."' alt='' />";
	}	


	function sc_cpageurl()
	{
		return e107::getUrl()->create('page/view', $this->page, array('allow' => 'page_sef,page_title,page_id'));
	}
}
