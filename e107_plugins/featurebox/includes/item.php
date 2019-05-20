<?php
/*
* e107 website system
*
* Copyright (c) 2008-2009 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Featurebox Item model
*
* $Source: /cvs_backup/e107_0.8/e107_plugins/featurebox/includes/item.php,v $
* $Revision$
* $Date$
* $Author$
*
*/

if (!defined('e107_INIT')) { exit; }

// TODO - sc_* methods
class plugin_featurebox_item extends e_model
{
	/**
	 * @see e_model::_field_id
	 * @var string
	 */
	protected $_field_id = 'fb_id';

	/**
	 * @see e_model::_db_table
	 * @var string
	 */
	protected $_db_table = 'featurebox';

	/**
	 * @var plugin_featurebox_category
	 */
	protected $_category = null;

	/**
	 * Parameter list (GET string format):
	 * - alt: return title as tag attribute text
	 * - url: add url tag to the output (only if 'fb_imageurl' is available)
	 * - rel: rel tag attribute
	 *
	 * @param string $parm
	 * @return string
	 */
	public function sc_featurebox_title($parm = null)
	{
		if(!empty($parm) && is_string($parm))
		{
			parse_str($parm, $parm);
		}

		$tp = e107::getParser();
		if(isset($parm['alt']))
		{
			return $tp->toAttribute($this->get('fb_title'));
		}

		$ret = $tp->toHTML($this->get('fb_title'), false, 'TITLE');
		if(isset($parm['url']) && $this->get('fb_imageurl'))
		{
			return '<a id="featurebox-titleurl-'.$this->getId().'" href="'.$tp->replaceConstants($this->get('fb_imageurl'), 'full').'" title="'.$tp->toAttribute($this->get('fb_title')).'" rel="'.$tp->toAttribute(vartrue($parm['rel'], '')).'">'.$ret.'</a>';
		}

		return $ret;
	}

	/**
	 * Parameter list (GET string format):
	 * - text: used if href is true
	 * - href (1/0): return only URL if false, else return tag
	 * - rel: rel tag attribute
	 *
	 * @param string $parm
	 * @return string
	 */
	public function sc_featurebox_url($parm = null)
	{
		$tp = e107::getParser();
		$url = $tp->replaceConstants($this->get('fb_imageurl'), 'full');
		
		if(empty($url)) return '';

		parse_str($parm, $parm);
				
		if(vartrue($parm['href']))
		{
			return $tp->replaceConstants($url);
		}

		$title = vartrue($parm['text']) ? defset($parm['text']) : LAN_MORE;
		$alt = $tp->toAttribute($this->get('fb_title'), false, 'TITLE');
		
		$buttonCls = vartrue($parm['button']) ? 'class="btn btn-primary btn-featurebox" ' : "";
		
		
		
		return '<a '.$buttonCls.'id="featurebox-url-'.$this->getId().'" href="'.$url.'" title="'.$alt.'" rel="'.$tp->toAttribute(vartrue($parm['rel'], '')).'">'.$title.'</a>';
	}
	
	
	
	public function sc_featurebox_button($parm='')
	{
		return $this->sc_featurebox_url('button=1');
		
	}
		
		
	
	

	public function sc_featurebox_text()
	{
		return e107::getParser()->toHTML($this->get('fb_text'), true, 'BODY');
	}

	/**
	 * Parameter list (GET string format):
	 * - src: return image src URL only
	 * - nourl: force no url tag
	 *
	 * @param string $parm
	 * @return string
	 */
	public function sc_featurebox_image($parm = null)
	{
		if(!$this->get('fb_image') && $parm != 'placeholder')
		{
			return '';
		}
		
		if($video = e107::getParser()->toVideo($this->get('fb_image')))
		{
			return $video;	
		}

		if(is_string($parm))
		{
			parse_str($parm, $parm);
		}

		$tp = e107::getParser();
		
		$imageSrc = ($parm != 'placeholder') ? $this->get('fb_image') : "";
		
		if($tp->thumbWidth > 100 || $tp->thumbHeight > 100) //Guessing it's a featurebox image.  Use {SETIMAGE} inside theme.php to configure. 
		{
			$src = $tp->thumbUrl($imageSrc); //XXX TODO TBD Add a pref to use without resizing? Or, detect {SETIMAGE} in template to enable?
		}
		else 
		{
			$src = $tp->replaceConstants($imageSrc, 'full');
		}
		
		if(isset($parm['src']))
		{
			return $src;
		}
		$tag = '<img id="featurebox-image-'.$this->getId().'" src="'.$src.'" alt="'.$tp->toAttribute($this->get('fb_title')).'" class="featurebox img-responsive img-fluid" />';
		if(isset($parm['nourl']) || !$this->get('fb_imageurl'))
		{
			return $tag;
		}
		return '<a id="featurebox-imageurl-'.$this->getId().'" href="'.$tp->replaceConstants($this->get('fb_imageurl'), 'full').'" title="'.$tp->toAttribute($this->get('fb_title')).'" rel="'.$tp->toAttribute(vartrue($parm['rel'], 'external')).'">'.$tag.'</a>';
	}
	
	public function sc_featurebox_thumb($parm=null)
	{
		$tp = e107::getParser();
		if(!$this->get('fb_image'))
		{
			return '';
		}
		parse_str($parm, $parm);
		$att = ($parm['aw']) ? "aw=".$parm['aw'] : 'aw=100&ah=60';
		$src = e107::getParser()->thumbUrl($this->get('fb_image'),$att);
			
		if(isset($parm['src']))
		{
			return $src;		
		}
		else
		{
			return '<img id="featurebox-thumb-'.$this->getId().'" src="'.$src.'" alt="'.$tp->toAttribute($this->get('fb_title')).'" class="featurebox" />';
			
		}
				
	}
	
	
	/**
	 * Returns 'active' for the first-slide. - often used by Bootstrap. 
	 */
	public function sc_featurebox_active()
	{
		$count = $this->getParam('counter', 1);
		return ($count == 1) ? "active" : "";
	}
	
	
	

	/**
	 * Item counter number (starting from 1)
	 * @param optional - to strat from 0 if needed. (bootstrap 3)
	 */
	public function sc_featurebox_counter($parm=1)
	{	
		$count = $this->getParam('counter', 1);
		return (empty($parm)) ? $count - 1 : $count;
	}

	/**
	 * Item limit number
	 */
	public function sc_featurebox_limit()
	{
		return $this->getParam('limit', 0);
	}

	/**
	 * Number of items (real) currently loaded
	 */
	public function sc_featurebox_total()
	{
		return $this->getParam('total', 0);
	}

	/**
	 * Total Number of items (no matter of the limit)
	 */
	public function sc_featurebox_all()
	{
		return $this->getCategory()->sc_featurebox_category_all();
	}

	/**
	 * Number of items per column
	 */
	public function sc_featurebox_cols()
	{
		return $this->getParam('cols', 1);
	}

	/**
	 * Item counter number inside a column (1 to sc_featurebox_cols)
	 */
	public function sc_featurebox_colcount()
	{
		return $this->getParam('col_counter', 1);
	}

	/**
	 * Column counter
	 */
	public function sc_featurebox_colscount()
	{
		return $this->getParam('cols_counter', 1);
	}

	/**
	 * Set current category
	 * @param plugin_featurebox_category $category
	 * @return plugin_featurebox_item
	 */
	public function setCategory($category)
	{
		$this->_category = $category;
		return $this;
	}

	/**
	 * Get Category model instance
	 * @return plugin_featurebox_category
	 */
	public function getCategory()
	{
		if(null === $this->_category)
		{
			$this->_category = new plugin_featurebox_category();
			$this->_category->load($this->get('fb_category'));
		}
		return $this->_category;
	}

	/**
	 * Magic call - category shortcodes
	 *
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, $arguments)
	{
		if (strpos($method, "sc_featurebox_") === 0)
		{
			return call_user_func_array(array($this->getCategory(), $method), $arguments);
		}
	}
}



