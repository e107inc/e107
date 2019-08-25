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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/download_shortcodes.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

/**
 * download_shortcodes
 */
class download_shortcodes extends e_shortcode
{
	public $qry;
	public $dlsubrow;
	public $dlsubsubrow;
	public $mirror;

	public $parent;
	public $grandparent;
	
   /**
    * download_shortcodes constructor
    */
	function __construct()
	{

	}


	public function breadcrumb()
	{
		$breadcrumb 	= array();

		switch ($this->qry['action'])
		{
			case 'mirror':
				$breadcrumb[]	= array('text' => LAN_PLUGIN_DOWNLOAD_NAME,					'url' => e107::url('download', 'index'));
				$breadcrumb[]	= array('text' => $this->var['download_category_name'],	    'url' => e107::url('download', 'category', $this->var)); // e_SELF."?action=list&id=".$this->var['download_category_id']);
				$breadcrumb[]	= array('text' => $this->var['download_name'],			    'url' => e107::url('download', 'item', $this->var)); //  e_SELF."?action=view&id=".$this->var['download_id']);
				$breadcrumb[]	= array('text' => LAN_dl_67,							    'url' => null);
			break;

			case 'maincats':
				$breadcrumb[]	= array('text' => LAN_PLUGIN_DOWNLOAD_NAME,							'url' => e107::url('download','index'));
			break;

			default:
				$breadcrumb[]	= array('text' => LAN_PLUGIN_DOWNLOAD_NAME,							'url' => e107::url('download','index'));

				if(!empty($this->grandparent))
				{
					$breadcrumb[]	= array('text' => $this->grandparent['download_category_name'],	'url' => ($this->grandparent['download_category_id']) ? e107::url('download', 'category', $this->grandparent) : null);
				}

				if(!empty($this->parent))
				{
					$breadcrumb[]	= array('text' => $this->parent['download_category_name'],	'url' => ($this->parent['download_category_id']) ?  e107::url('download', 'category', $this->parent) : null);
				}

				$breadcrumb[]	= array('text' => $this->var['download_category_name'],	'url' => ($this->var['download_category_id']) ?  e107::url('download', 'category', $this->var) : null);
				$breadcrumb[]	= array('text' => varset($this->var['download_name']),			'url' => null);
			break;
		}


		e107::breadcrumb($breadcrumb);

	}
	
	function sc_download_breadcrumb($parm='')
	{
		$tp = e107::getParser();
		$frm = e107::getForm();

		$breadcrumb = e107::breadcrumb();

		return $frm->breadcrumb($breadcrumb);
		
	}
		
	
	// Category ************************************************************************************
   public function sc_download_cat_main_name()
   {
      $tp = e107::getParser();

		if(!empty($this->var['d_count']))
		{
			$url = e107::url('download', 'category', $this->var);
			return "<a href='".$url."'>".$tp->toHTML($this->var['download_category_name'], FALSE, 'TITLE')."</a>";
		}

      return $tp->toHTML($this->var['download_category_name'], FALSE, 'TITLE');
   }
   
   public function sc_download_cat_main_description()
   {
      $tp = e107::getParser();
      return $tp->toHTML($this->var['download_category_description'], TRUE, 'DESCRIPTION');
   }
   
   public function sc_download_cat_main_icon()
   {
      // Pass count as 1 to force non-empty icon
      return $this->_sc_cat_icons($this->var['download_category_icon'], 1, $this->var['download_category_name']);
   }

	public function sc_download_cat_main_count()
	{
		if(!empty($this->var['d_count']))
		{
			return intval($this->var['d_count']);
		}
	}

	public function sc_download_cat_main_size()
	{
		if(!empty($this->var['d_count']))
		{
			return eHelper::parseMemorySize($this->var['d_size']);
		}
	}


	public function sc_download_cat_main_downloaded()
	{
		if(!empty($this->var['d_count']))
		{
			return intval($this->var['d_requests']);
		}
	}
   
	// Sub-Category ********************************************************************************
   function sc_download_cat_sub_name() 
   {
	  $tp = e107::getParser();

	  $class = 'category-name';
	  $class .= $this->isNewDownload($this->dlsubrow['d_last']) ? ' new' : '';
	  
      if ($this->dlsubrow['d_count'])
      {

		$url = e107::url('download', 'category', $this->dlsubrow);
	    return "<a class='".$class."' href='".$url."'>".$tp->toHTML($this->dlsubrow['download_category_name'], FALSE, 'TITLE')."</a>";
  
  //       return "<a class='".$class."' href='".e_PLUGIN_ABS."download/download.php?action=list&id=".$this->dlsubrow['download_category_id']."'>".$tp->toHTML($this->dlsubrow['download_category_name'], FALSE, 'TITLE')."</a>";
      }
      else
      {
         return $tp->toHTML($this->dlsubrow['download_category_name'], FALSE, 'TITLE');
      }
   }

   function sc_download_cat_sub_description() 
   {
	  $tp = e107::getParser();
      return $tp->toHTML($this->dlsubrow['download_category_description'], TRUE, 'DESCRIPTION');
   }

   function sc_download_cat_sub_icon() 
   {
      return $this->_sc_cat_icons($this->dlsubrow['download_category_icon'], $this->dlsubrow['d_count'], $this->dlsubrow['download_category_name']);
   }

   function sc_download_cat_sub_new_icon()
   {
      return ($this->isNewDownload($this->dlsubrow['d_last_subs'])) ? $this->renderNewIcon() : "";
   }
   
   function sc_download_cat_sub_count() 
   {
      return $this->dlsubrow['d_count'];
   }

   function sc_download_cat_sub_size()
   {
      return eHelper::parseMemorySize($this->dlsubrow['d_size']);
   }
   
   function sc_download_cat_sub_downloaded() 
   {
      return intval($this->dlsubrow['d_requests']);
   }
   
   
	// Sub-Sub-Category ****************************************************************************
	
	
   function sc_download_cat_subsub_name() 
   {
   	
	// isNewDownload
		$class = 'category-name';
		$class .= $this->isNewDownload($this->dlsubsubrow['d_last']) ? ' new' : '';
	
	  $tp = e107::getParser();
      if ($this->dlsubsubrow['d_count'])
      {
         $url = e107::url('download', 'category', $this->dlsubsubrow); // /list/category', array('id'=>$this->dlsubsubrow['download_category_id'], 'name'=> vartrue($this->dlsubsubrow['download_category_sef'],'--sef-not-set--')));
			// e_PLUGIN_ABS."download/download.php?action=list&id=".$this->dlsubsubrow['download_category_id']
         return "<a class='".$class."' href='".$url."'>".$tp->toHTML($this->dlsubsubrow['download_category_name'], FALSE, 'TITLE')."</a>";
      }
      else
      {
         return $tp->toHTML($this->dlsubsubrow['download_category_name'], FALSE, 'TITLE');
      }
   }
   
   function sc_download_cat_subsub_description() 
   {   
      return e107::getParser()->toHTML($this->dlsubsubrow['download_category_description'], TRUE, 'DESCRIPTION');
   }
   
   function sc_download_cat_subsub_icon() 
   {
      return $this->_sc_cat_icons($this->dlsubsubrow['download_category_icon'], $this->dlsubsubrow['d_count'], $this->dlsubsubrow['download_category_name']);
   }
   
   function sc_download_cat_subsub_new_icon()
   {
      return ($this->isNewDownload($this->dlsubsubrow['d_last'])) ? $this->renderNewIcon() : "";
   }

   function sc_download_cat_subsub_count() 
   {
      return $this->dlsubsubrow['d_count'];
   }
   
   function sc_download_cat_subsub_size() 
   {
      return eHelper::parseMemorySize($this->dlsubsubrow['d_size']);
   }
   
   function sc_download_cat_subsub_downloaded() 
   {
      return intval($this->dlsubsubrow['d_requests']);
   }


	// List ****************************************************************************************

	
	function sc_download_list_caption($parm='')
	{
	
		$qry = $this->qry;
		
		$qry['sort'] = ($qry['sort'] == 'asc') ? 'desc' : 'asc'; // reverse. 

		switch ($parm) 
		{
			case 'name':
				$qry['order'] = 'name';
				$text = LAN_NAME;
			break;

			case 'datestamp':
				$qry['order'] = 'datestamp';
				$text = LAN_DATE;
			break;
			
			case 'author':
				$qry['order'] = 'author';
				$text = LAN_AUTHOR;
			break;
				
			case 'filesize':
				$qry['order'] = 'filesize';
				$text = LAN_SIZE;
			break;

			case 'requested':
				$qry['order'] = 'requested';
				$text = LAN_dl_29;
			break;

			case 'rating':
				$text = LAN_RATING;
			break;
				
			case 'link':
				$text = LAN_dl_8;
			break;
							
			default:
				$text = "Missing LAN Column"; // debug. 
			break;
		}


		return "<a href='".e_REQUEST_SELF."?".http_build_query($qry, null, '&amp;')."'>".$text."</a>" ;
	}	
	
		
	
   function sc_download_list_name($parm='')
   {
 	  $tp = e107::getParser();
	  $pref = e107::getPref();
	  
      if ($parm == "nolink")
      {
      	return $tp->toHTML($this->var['download_name'],TRUE,'LINKTEXT');
      }
	  
      if ($parm == "request")
      {
      	$agreetext = $tp->toJS($tp->toHTML($pref['agree_text'],FALSE,'DESCRIPTION'));
		
      	if ($this->var['download_mirror_type'])
      	{
      		$text = ($pref['agree_flag'] ? "<a href='".e_PLUGIN_ABS."download/download.php?mirror.".$this->var['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_PLUGIN_ABS."download/download.php?mirror.".$this->var['download_id']."' title='".LAN_DOWNLOAD."'>");
      	}
      	else
      	{
      		$text = ($pref['agree_flag'] ? "<a href='".e_PLUGIN_ABS."download/request.php?".$this->var['download_id']."' onclick= \"return confirm('{$agreetext}');\">" : "<a href='".e_PLUGIN_ABS."download/request.php?".$this->var['download_id']."' title='".LAN_DOWNLOAD."'>");
      	}
		
      	$text .= $tp->toHTML($this->var['download_name'], FALSE, 'TITLE')."</a>";
		
      	return $text;
      }
	  
	  $url = e107::url('download', 'item', $this->var);
      return  "<a href='".$url."'>".$tp->toHTML($this->var['download_name'],TRUE,'LINKTEXT')."</a>";
 
    //  return  "<a href='".e_PLUGIN_ABS."download/download.php?action=view&id=".$this->var['download_id']."'>".$tp->toHTML($this->var['download_name'],TRUE,'LINKTEXT')."</a>";
   }

   function sc_download_list_author()
   {
      return $this->var['download_author'];
   }
   
   function sc_download_list_requested()
   {
      return $this->var['download_requested'];
   }
   
   function sc_download_list_newicon()
   {
      return $this->isNewDownload($this->var['download_datestamp']) ? $this->renderNewIcon() : "";
   }
   
   function sc_download_list_recenticon()
   {
      $pref = e107::getPref();
      // convert "recent_download_days" to seconds
      return ($this->var['download_datestamp'] > time()-($pref['recent_download_days']*86400) ? $this->renderNewIcon() : '');
   }
   
   function sc_download_list_filesize()
   {
      return eHelper::parseMemorySize($this->var['download_filesize']);
   }

   function sc_download_list_datestamp()
   {
 
      $tp = e107::getParser();
      return $tp->toDate($this->var['download_datestamp'], "short");
   }
   
   function sc_download_list_thumb($parm='')
   {
	  $tp = e107::getParser();
	  
      $img = "";

      if(!empty($this->var['download_thumb']))
      {
           $opts = array(
             'legacy' => "{e_FILE}downloadthumbs/",
             'class'  => 'download-image img-responsive img-fluid',
              'alt'     => $this->var['download_name']
         );

         $img = $tp->toImage($this->var['download_thumb'], $opts);
      }


      if ($parm == "link" && $this->var['download_thumb'])
      {
      	$url = e107::url('download', 'item', $this->var);
		return "<a  href='".$url."'>".$img."</a>";	
      //	return "<a href='".e_PLUGIN_ABS."download/download.php?action=view&id=".$this->var['download_id']."'>".$img."</a>";
      }
      else
      {
      	return $img;
      }
   }
   
   function sc_download_list_id()
   {
      return $this->var['download_id'];
   }
   
   function sc_download_list_rating()
   {
	  return e107::getForm()->rate("download", $this->var['download_id']);
   }
   
   function sc_download_list_link($parm='')
   {
		$tp = e107::getParser();
		$pref = e107::getPref();

      	$agreetext = $tp->toJS($tp->toHTML($pref['agree_text'],FALSE,'DESCRIPTION'));

		$img = "<img src='".IMAGE_DOWNLOAD."' alt='".LAN_DOWNLOAD."' title='".LAN_DOWNLOAD."' />";
      
		if(deftrue('BOOTSTRAP'))
		{
			$img = e107::getParser()->toGlyph('fa-download',false);
		//	$img = '<i class="icon-download"></i>'; 
		}
	  	
     	if ($this->var['download_mirror_type'])
     	{
     		return "<a class='e-tip' title='".LAN_DOWNLOAD."' href='".e_PLUGIN_ABS."download/download.php?mirror.".$this->var['download_id']."'>{$img}</a>";
     	}
     	else
     	{
     		$url = $tp->parseTemplate("{DOWNLOAD_REQUEST_URL}",true, $this); // $this->sc_download_request_url();
     	  	return ($pref['agree_flag'] ? "<a class='e-tip' title='".LAN_DOWNLOAD."' href='".$url."' onclick= \"return confirm('{$agreetext}');\">{$img}</a>" : "<a class='e-tip' title='".LAN_DOWNLOAD."' href='".$url."' >{$img}</a>");
   
		//	return ($pref['agree_flag'] ? "<a class='e-tip' title='".LAN_DOWNLOAD."' href='".e_PLUGIN_ABS."download/request.php?".$this->var['download_id']."' onclick= \"return confirm('{$agreetext}');\">{$img}</a>" : "<a class='e-tip' title='".LAN_DOWNLOAD."' href='".e_PLUGIN_ABS."download/request.php?".$this->var['download_id']."' >{$img}</a>");
     	}
   }


   function sc_download_request_url($parm=null)
   {
    	return e107::url('download', 'get', $this->var); // /request/item',array('id'=>$this->var['download_id'], 'name'=>$this->var['download_sef']));

   }

	function sc_download_filename($parm=null)
	{
		return basename($this->var['download_url']);

	}
   
   function sc_download_list_icon($parm='') //XXX FIXME $img. 
   {
      if ($parm == "link")
      {
      	$url = e107::url('download', 'item', $this->var);
		return "<a href='".$url."' >".$img."</a>";
     // 	return "<a href='".e_PLUGIN_ABS."download/download.php?action=view&id=".$this->var['download_id']."' >".$img."</a>";
      }
      else
      {
      	return $img;
      }
      return;
   }
   
   function sc_download_list_imagefull($parm='')
   {
	
		$img = "";

		if(!empty($this->var['download_image']))
        {
            $img = $this->sc_download_view_imagefull();
        }

		if($parm == "link" && $this->var['download_image'])
		{
			$url = e107::url('download', 'item', $this->var);
			return "<a title=\"".LAN_dl_53."\" href='".$url."'>".$img."</a>";
				
	//	return "<a title=\"".LAN_dl_53."\" href='".e_PLUGIN_ABS."download/download.php?action=view&id=".$this->var['download_id']."'>".$img."</a>";
		}
		else
		{
			return $img;
		}
	}
   
   
   function sc_download_list_nextprev()
   {
     	global $nextprev_parms;
     	return e107::getParser()->parseTemplate("{NEXTPREV={$nextprev_parms}}");
   }

   function sc_download_list_total_amount() 
   {
      global $dltdownloads;
      return intval($dltdownloads)." ".LAN_dl_16;
   }
   
   function sc_download_list_total_files() 
   {
      global $dlft;
      return intval($dlft)." ".strtolower(LAN_FILES);
   }
   
   
  
   
	// View ****************************************************************************************
	
	
   function sc_download_view_id()
   {
      return $this->var['download_id'];
   }
   
   function sc_download_admin_edit()
   {
   		$icon = (deftrue('BOOTSTRAP')) ? e107::getParser()->toGlyph('fa-edit') : "<img src='".e_IMAGE_ABS."generic/edit.png' alt='*' style='padding:0px;border:0px' />";
	
		$url = e_PLUGIN_ABS."download/admin_download.php?action=edit&id=".$this->var['download_id'];
	
		return (ADMIN && getperms('6')) ? "<a class='e-tip btn btn-default btn-secondary hidden-print' href='".$url."' title='".LAN_EDIT."'>".$icon."</a>" : "";
   }
   
   function sc_download_category()
   {
      return $this->var['download_category_name'];
   }
   
   function sc_download_category_description()
   {
      global $tp,$dl,$parm;
	  
      $text = $tp -> toHTML($dl['download_category_description'], TRUE,'DESCRIPTION');
      if ($parm){
      	return substr($text,0,$parm);
      }else{
      	return $text;
      }
   }
   function sc_download_view_name($parm='')
   {
		$tp = e107::getParser();

		$viewUrl =  e107::url('download', 'item', $this->var);
		$requestUrl = 	$url = $tp->parseTemplate("{DOWNLOAD_REQUEST_URL}",true,$this); // $this->sc_download_request_url();

		$link['view'] = "<a href='".$viewUrl."'>".$this->var['download_name']."</a>";
		$link['request'] = "<a href='".$requestUrl."' title='".LAN_dl_46."'>".$this->var['download_name']."</a>";

		// $link['view'] = "<a href='".e_PLUGIN_ABS."download/download.php?action=view&id=".$this->var['download_id']."'>".$this->var['download_name']."</a>";
		// $link['request'] = "<a href='".e_PLUGIN_ABS."download/request.php?".$this->var['download_id']."' title='".LAN_dl_46."'>".$this->var['download_name']."</a>";
		
		
		if ($parm)
		{
      		return $tp->toHTML($link[$parm],true, 'TITLE');
		}
	  
		return $this->var['download_name'];
   }

   function sc_download_view_name_linked()
   {
      global $dl;
	  $tp = e107::getParser();
	  $pref = e107::getPref();
	  $url = 	$url = $tp->parseTemplate("{DOWNLOAD_REQUEST_URL}",true,$this);  //$this->sc_download_request_url();
	  
      if ($pref['agree_flag'] == 1) 
      {
       	return "<a href='".$url."' onclick= \"return confirm('".$tp->toJS($tp->toHTML($pref['agree_text'],FALSE,'DESCRIPTION'))."');\" title='".LAN_dl_46."'>".$dl['download_name']."</a>";  	
    //  	return "<a href='".e_PLUGIN_ABS."download/request.php?".$dl['download_id']."' onclick= \"return confirm('".$tp->toJS($tp->toHTML($pref['agree_text'],FALSE,'DESCRIPTION'))."');\" title='".LAN_dl_46."'>".$dl['download_name']."</a>";
      } 
      else 
      {
      	return "<a href='".$url."' title='".LAN_dl_46."'>".$dl['download_name']."</a>";
   
    //  	return "<a href='".e_PLUGIN_ABS."download/request.php?".$dl['download_id']."' title='".LAN_dl_46."'>".$dl['download_name']."</a>";
      }
   }
   
   function sc_download_view_author()
   {
      return ($this->var['download_author'] ? $this->var['download_author'] : "");
   }
   
   function sc_download_view_authoremail()
   {
      return ($this->var['download_author_email']) ? e107::getParser()->toHTML($this->var['download_author_email'], TRUE, 'LINKTEXT') : "";
   }
   
   function sc_download_view_authorwebsite()
   {
      return ($this->var['download_author_website']) ? e107::getParser()->toHTML($this->var['download_author_website'], TRUE,'LINKTEXT') : "";
   }
   
   function sc_download_view_description($parm='')
   {
      $maxlen = ($parm ? intval($parm) : 0);
      $text = ($this->var['download_description'] ?  e107::getParser()->toHTML($this->var['download_description'], TRUE, 'DESCRIPTION') : "");
	  
      if ($maxlen)
      {
      	return substr($text, 0, $maxlen);
      }
      else
      {
      	return $text;
      }
	  
      return $text;
   }

   function sc_download_view_date($parm='')
   {
      return ($this->var['download_datestamp']) ? e107::getParser()->toDate($this->var['download_datestamp'], $parm) : "";
   }
   
   /**
    * @Deprecated DOWNLOAD_VIEW_DATE should be used instead.
    */
   function sc_download_view_date_short()
   {
      return $this->sc_download_view_date('short');
   }
   
    /**
    * @Deprecated DOWNLOAD_VIEW_DATE should be used instead.
    */
   function sc_download_view_date_long()
   {
      return $this->sc_download_view_date('long');
   }

   function sc_download_view_image()
   {
      $tp = e107::getParser();

		$url = e107::url('download', 'image', $this->var);
	//$url =  e_PLUGIN_ABS . "download/request.php?download." . $this->var['download_id'];

      if($this->var['download_thumb'])
      {
         $opts = array(
             'legacy' => "{e_FILE}downloadthumbs/",
             'class'  => 'download-image dl_image img-responsive img-fluid'
         );
         $image = $tp->toImage($this->var['download_thumb'], $opts);


         return ($this->var['download_image'] ? "<a href='" . $url . "'>" . $image . "</a>" : $image);
      }
      elseif($this->var['download_image'])
      {
	      $opts = array(
		      //'legacy' => "{e_FILE}downloadthumbs/",
		      'class'  => 'download-image dl_image img-responsive img-fluid',
		      'w' => 200
	      );
	      $image = $tp->toImage($this->var['download_image'], $opts);


	      return "<a href='" . $url . "'>" . $image . "</a>";
          //return "<a href='" . $url . "'>" . LAN_dl_40 . "</a>";
      }
      else
      {
         return LAN_dl_75;
      }
   }

   /**
    * {DOWNLOAD_VIEW_LINK: class=thumbnail}
    */   
   function sc_download_view_imagefull($parm=array())
   {

      if(!empty($this->var['download_image']))
      {

         $opts = array(
                'legacy' => "{e_FILE}downloadimages/",
                'class'  => 'download-image dl_image download-view-image img-responsive img-fluid '.vartrue($parm['class']),
               'alt' => basename($this->var['download_image'])
         );

         return e107::getParser()->toImage($this->var['download_image'], $opts);
      }

   }
   
   /**
    * {DOWNLOAD_VIEW_LINK: size=2x}
    */
	function sc_download_view_link($parm)
	{
   		$tp = e107::getParser();
		$pref = e107::getPref();

		$click = "";
		
		$img = "<img src='".IMAGE_DOWNLOAD."' alt='".LAN_DOWNLOAD."' title='".LAN_DOWNLOAD."' />";
      
		if(deftrue('BOOTSTRAP'))
		{
			$img = e107::getParser()->toGlyph('fa-download',$parm); // '<i class="icon-download"></i>';
		}	
		
		if ($pref['agree_flag'] == 1) 
		{
      		$click = " onclick='return confirm(\"".$tp->toJS($tp->toHTML($pref['agree_text'],true,'emotes, no_tags'))."\")'";
		}
		
		$url = 	$url = $tp->parseTemplate("{DOWNLOAD_REQUEST_URL}",true,$this); //$this->sc_download_request_url();
     	
     	if(varset($parm['type']) == 'href')
		{
			return $url;	
		}
     	
     	$dnld_link = "<a href='".$url."'{$click}>";
     	
		if ($this->var['download_mirror'])
		{
	      	if ($this->var['download_mirror_type'])
	      	{
	      		return "<a href='".e_PLUGIN_ABS."download/download.php?mirror.".$this->var['download_id']."'>".LAN_dl_66."</a>";
	      	}
	      	else
	      	{
	      		return $dnld_link.$img."</a>";
	      	}
		}
		else
		{
			return $dnld_link.$img."</a>";
		}
	}

	function sc_download_view_filesize()
	{
      return ($this->var['download_filesize']) ? eHelper::parseMemorySize($this->var['download_filesize']) : "";
	}
   
	function sc_download_view_rating()
	{
   
		$frm = e107::getForm();
		$options = array('label'=>' ','template'=>'RATE|VOTES|STATUS');
	  	return $frm->rate("download", $this->var['download_id'], $options);
		
		/*
      	require_once(e_HANDLER."rate_class.php");
      	$rater = new rater;
      	
      	$text = "
      		<table style='width:100%'>
      		<tr>
      		<td style='width:50%'>";
      	if ($ratearray = $rater->getrating("download", $this->var['download_id'])) {
      		for($c = 1; $c <= $ratearray[1]; $c++) {
      			$text .= "<img src='".e_IMAGE."rate/star.png' alt='*' />";
      		}
      		if ($ratearray[2]) {
      			$text .= "<img src='".e_IMAGE."rate/".$ratearray[2].".png'  alt='*' />";
      		}
      		if ($ratearray[2] == "") {
      			$ratearray[2] = 0;
      		}
      		$text .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
      		$text .= ($ratearray[0] == 1 ? LAN_dl_43 : LAN_dl_44);
      	} else {
      		$text .= LAN_dl_13;
      	}
      	$text .= "</td><td style='width:50%; text-align:right'>";
      	if (!$rater->checkrated("download", $this->var['download_id']) && USER) {
      		$text .= $rater->rateselect("&nbsp;&nbsp;&nbsp;&nbsp; <b>".LAN_dl_14, "download", $this->var['download_id'])."</b>";
      	}
      	else if (!USER) {
      		$text .= "&nbsp;";
      	} else {
      		$text .= LAN_THANK_YOU;
      	}
      	$text .= "</td></tr></table>";
      return $text;
		 */
	}

	function sc_download_report_link()
	{
		$pref = e107::getPref();
		if(check_class($pref['download_reportbroken']))
		{
			//$url = e_PLUGIN_ABS."download/download.php?action=report&id=".$this->var['download_id'];
			$url = e107::url('download','report', $this->var);
			return "<a href='".$url."'>".LAN_dl_45."</a>";
		}

		return '';
	}
   
	function sc_download_view_caption()
	{
		$text = $this->var['download_category_name'];
     	$text .= ($this->var['download_category_description']) ? " [ ".$this->var['download_category_description']." ]" : "";
		return $text;
	}
   
   
	// Mirror **************************************************************************************
	
	function sc_download_mirror_request() 
	{
	   return $this->var['download_name'];
	}
	
	function sc_download_mirror_request_icon() 
	{
      return ($this->var['download_thumb'] ? "<img src='".e107::getParser()->replaceConstants($this->var['download_thumb'])."' alt='*'/>" : "");
	}
	
	function sc_download_mirror_name() 
	{
      return "<a href='{$this->mirror['dlmirror']['mirror_url']}' rel='external'>".$this->mirror['dlmirror']['mirror_name']."</a>";
	}
	
	function sc_download_mirror_image() 
	{
	   $tp = e107::getParser();
      return ($this->mirror['dlmirror']['mirror_image'] ? "<a href='{$this->mirror['dlmirror']['mirror_url']}' rel='external'><img src='".$tp->replaceConstants($this->mirror['dlmirror']['mirror_image'])."' alt='*'/></a>" : "");
	}
	
	function sc_download_mirror_location() 
	{
      return ($this->mirror['dlmirror']['mirror_location'] ? $this->mirror['dlmirror']['mirror_location'] : "");
	}
	
	function sc_download_mirror_description() 
	{
      return ($this->mirror['dlmirror']['mirror_description'] ? e107::getParser()->toHTML($this->mirror['dlmirror']['mirror_description'], TRUE) : "");
	}
	
	function sc_download_mirror_filesize() 
	{
      return eHelper::parseMemorySize($this->mirror['dlmirrorfile'][3]);
	}
	
	function sc_download_mirror_link() 
	{
		$tp = e107::getParser();
		$pref = e107::getPref();
	   
 		$click = " onclick='return confirm(\"".$tp->toJS($tp->toHTML($pref['agree_text'],FALSE,'DESCRIPTION'))."\")'";
		
		$img = "<img src='".IMAGE_DOWNLOAD."' alt='".LAN_DOWNLOAD."' title='".LAN_DOWNLOAD."' />";
      
		if(deftrue('BOOTSTRAP'))
		{
			$img = '<i class="icon-download"></i>'; 
		}	
		
		return "<a href='".e_PLUGIN_ABS."download/download.php?mirror.{$this->var['download_id']}.{$this->mirror['dlmirrorfile'][0]}' title='".LAN_DOWNLOAD."'{$click}>".$img."</a>";
	}
	
	function sc_download_mirror_requests() 
	{
      return (ADMIN ? LAN_dl_73.$this->mirror['dlmirrorfile'][2] : "");
	}
	
	function sc_download_total_mirror_requests() 
	{
	   return (ADMIN ? LAN_dl_74.$this->mirror['dlmirror']['mirror_count'] : "");
	}
	
	
   // --------- Download View Lans -----------------------------
   
   function sc_download_view_author_lan()
   {

      return ($this->var['download_author']) ? LAN_AUTHOR : "";
   }
   
   function sc_download_view_authoremail_lan()
   {

      return ($this->var['download_author_email']) ? LAN_dl_30 : "";
   }
   
   function sc_download_view_authorwebsite_lan()
   {

      return ($this->var['download_author_website']) ? LAN_dl_31 : "";
   }
   
   function sc_download_view_date_lan()
   {

      return ($this->var['download_datestamp']) ? LAN_DATE: "";
   }
   
   function sc_download_view_image_lan()
   {
      return LAN_IMAGE;
   }
   
   function sc_download_view_requested()
   {

      return $this->var['download_requested'];
   }
   
   function sc_download_view_rating_lan()
   {
      return LAN_RATING;
   }
   
   function sc_download_view_filesize_lan()
   {
      return LAN_SIZE;
   }
   
   function sc_download_view_description_lan()
   {
      return LAN_DESCRIPTION;
   }
   
   function sc_download_view_requested_lan()
   {
      return LAN_dl_77;
   }
   
   function sc_download_view_link_lan()
   {
      return LAN_DOWNLOAD;
   }
   
   
      //  -----------  Download View : Previous and Next  ---------------
   
   	/**
	 * {DOWNLOAD_VIEW_PREV: x=y}
	 */   
   function sc_download_view_prev($parm='')
   {
		$sql = e107::getDb();
	  	$tp = e107::getParser();
	  
      	$dlrow_id = intval($this->var['download_id']);
		
      	if ($sql->select("download", "*", "download_category='".intval($this->var['download_category_id'])."' AND download_id < {$dlrow_id} AND download_active > 0 && download_visible IN (".USERCLASS_LIST.") ORDER BY download_datestamp DESC LIMIT 1")) 
      	{
      		$dlrowrow = $sql->fetch();
			
			$url = e107::url('download', 'item', $dlrowrow);

			$icon = (deftrue('BOOTSTRAP')) ? $tp->toGlyph('fa-chevron-left') : '&lt;&lt;';
			
	    	return "<a class='e-tip' href='".$url ."' title=\"".$dlrowrow['download_name']."\">".$icon." ".LAN_PREVIOUS."</a>\n";
   		
      	//	return "<a href='".e_PLUGIN_ABS."download/download.php?action=view&id=".$dlrowrow['download_id']."'>&lt;&lt; ".LAN_dl_33." [".$dlrowrow['download_name']."]</a>\n";
      	}
		else
		{
      		return "&nbsp;";
      	}
   }
   
   	/**
	 * {DOWNLOAD_VIEW_NEXT: x=y}
	 */
	function sc_download_view_next($parm='')
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		$dlrow_id = intval($this->var['download_id']);
	  
		if ($sql->select("download", "*", "download_category='".intval($this->var['download_category_id'])."' AND download_id > {$dlrow_id} AND download_active > 0 && download_visible IN (".USERCLASS_LIST.") ORDER BY download_datestamp ASC LIMIT 1")) 
      	{
      		$dlrowrow = $sql->fetch();
			extract($dlrowrow);
			$url = 	$url = e107::url('download', 'item', $dlrowrow);

			$icon = (deftrue('BOOTSTRAP')) ? $tp->toGlyph('fa-chevron-right') : '&gt;&gt;';

			return "<a class='e-tip' href='".$url."' title=\"".$dlrowrow['download_name']."\">".LAN_NEXT." ".$icon."</a>\n";
   		 
      //		return "<a href='".e_PLUGIN_ABS."download/download.php?action=view&id=".$dlrowrow['download_id']."'>[".$dlrowrow['download_name']."] ".LAN_dl_34." &gt;&gt;</a>\n";
      	}
      	else 
      	{
      		return "&nbsp;";
      	}
	}
   
   	/**
	 * {DOWNLOAD_BACK_TO_LIST: x=y}
	 */
   function sc_download_back_to_list($parm)
   {
   		$url = e107::url('download', 'category', $this->var);
		// e_PLUGIN_ABS."download/download.php?action=list&id=".$this->var['download_category']
		
		$title = "Back to [x]";
		
		
		return "<a class='e-tip' title=\"".e107::getParser()->lanVars($title,array('x'=>$this->var['download_category_name']))."\" href='".$url."'>".LAN_BACK."</a>";
   }
   
   function sc_download_back_to_category_list()
   {
		if(!empty($this->parent))
		{
			$link = e107::url('download', 'category', $this->parent);
		}
		else
		{
			$link = ($this->var['download_category_id']) ? e107::url('download', 'category', $this->var) : null;
		}

      	return "<a class='btn btn-default btn-secondary btn-xs btn-mini' href='".$link."'>".LAN_dl_9."</a>";
   }
   
   
   // Misc stuff ---------------------------------------------------------------------------------
   function sc_download_cat_newdownload_text()
   {
      return $this->renderNewIcon()." ".LAN_dl_36;
   }
   
   function sc_download_cat_search()
   {
		$tp = e107::getParser();
   		$text = "<form class='form-search form-inline' method='get' action='".e_HTTP."search.php'>";
   		$text .= '<div><div class="input-group">';
		$text .= "<input class='tbox form-control search-query' type='text' name='q' size='30' value='' placeholder=\"".LAN_SEARCH."\" maxlength='50' />
		 			<input type='hidden' name='r' value='0' />
		 			<input type='hidden' name='t' value='download' />
		 			";
			
		$text .= '
              <span class="input-group-btn">
              <button class="btn btn-default btn-secondary" type="submit" name="s"  value="1">';
              
              $text .= $tp->toIcon('fa-search.glyph');

             $text .= '</button>
             </span>
             </div><!-- /input-group -->
        </div></form>';
	
	return $text;
	
     /* return "<form class='form-search form-inline' method='get' action='".e_HTTP."search.php'>
      		  <div class='input-group'>
      		  <input class='tbox form-control search-query' type='text' name='q' size='30' value='' placeholder=\"".LAN_SEARCH."\" maxlength='50' />
      		  <button class='btn btn-primary button' type='submit' name='s'  value='1' />".LAN_GO."</button>
      		  <input type='hidden' name='r' value='0' />
      		  </div>
      		  </form>";*/
   }
   
   
   
	/**
	 * @private
	 */
	function _sc_cat_icons($source, $count, $alt)
	{
	   if (!$source) return "&nbsp;";
	 //  list($ret[TRUE],$ret[FALSE]) = explode(chr(1), $source.chr(1)); //XXX ???
	//   if (!$ret[FALSE]) $ret[FALSE] = $ret[TRUE]; //XXX ???
	    $parms = array('legacy'=> "{e_IMAGE}icons/");
	   return e107::getParser()->toIcon($source, $parms);
		//return "<img src='".e_IMAGE."icons/{$ret[($count!=0)]}' alt='*'/>";
	}
	
	
   private function isNewDownload($last_val)
	{
		if (USER && ($last_val > USERLV))
		{
			return true;
		}
		else
		{
			return false; 
		}
	}
	
	private function renderNewIcon()
	{
		if(strstr(IMAGE_NEW,'<i ') || strstr(IMAGE_NEW,'<span'))
		{
			return IMAGE_NEW;	
		}
			
		 return e107::getParser()->toIcon(IMAGE_NEW);
		
	//	return "<img src='".IMAGE_NEW."' alt='*' style='vertical-align:middle' />";	
	}
}
?>
