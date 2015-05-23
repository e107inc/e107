<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Form Handler
 *
 * $URL$
 * $Id$
 *
*/

if (!defined('e107_INIT')) { exit; }

/**
 * 
 * @package e107
 * @subpackage e107_handlers
 * @version $Id$
 * @todo hardcoded text
 * 
 * Automate Form fields creation. Produced markup is following e107 CSS/XHTML standards
 * If options argument is omitted, default values will be used (which OK most of the time)
 * Options are intended to handle some very special cases.
 *
 * Overall field options format (array or GET string like this one: var1=val1&var2=val2...):
 *
 *  - id => (mixed) custom id attribute value
 *  if numeric value is passed it'll be just appended to the name e.g. {filed-name}-{value}
 *  if false is passed id will be not created
 *  if empty string is passed (or no 'id' option is found)
 *  in all other cases the value will be used as field id
 * 	default: empty string
 *
 *  - class => (string) field class(es)
 * 	Example: 'tbox select class1 class2 class3'
 * 	NOTE: this will override core classes, so you have to explicit include them!
 * 	default: empty string
 *
 *  - size => (int) size attribute value (used when needed)
 *	default: 40
 *
 *  - title (string) title attribute
 *  default: empty string (omitted)
 *
 *  - readonly => (bool) readonly attribute
 * 	default: false
 *
 *  - selected => (bool) selected attribute (used when needed)
 * 	default: false
 *
 *  checked => (bool) checked attribute (used when needed)
 *  default: false
 *  - disabled => (bool) disabled attribute
 *  default: false
 *
 *  - tabindex => (int) tabindex attribute value
 *	default: inner tabindex counter
 *
 *  - other => (string) additional data
 *  Example: 'attribute1="value1" attribute2="value2"'
 *  default: empty string
 */
class e_form
{
	protected $_tabindex_counter = 0;
	protected $_tabindex_enabled = true;
	protected $_cached_attributes = array();

	/**
	 * @var user_class
	 */
	protected $_uc;

	protected $_required_string;

	function __construct($enable_tabindex = false)
	{
		$this->_tabindex_enabled = $enable_tabindex;
		$this->_uc = e107::getUserClass();
		$this->setRequiredString('<span class="required">*&nbsp;</span>');
	}

	/**
	 * Open a new form
	 * @param string name
	 * @param $method - post|get  default is post
	 * @param @target - e_REQUEST_URI by default
	 * @param $other - unused at the moment.
	 * @return string
	 */
	public function open($name, $method=null, $target=null, $options=null)
	{
		if($target == null)
		{
			$target = e_REQUEST_URI;	
		}
		
		if($method == null)
		{
			$method = "post";	
		}
	
		$class 			= "";
		$autoComplete 	= "";
	
		parse_str($options,$options);
	
		if(vartrue($options['class']))
		{
			$class = "class='".$options['class']."'";
		}
		else  // default 
		{
			$class= "class='form-horizontal'"; 
		}
		
		if(isset($options['autocomplete'])) // leave as isset()
		{
			$autoComplete = " autocomplete='".($options['autocomplete'] ? 'on' : 'off')."'";	
		}

		
		if($method == 'get' && strpos($target,'='))
		{
			list($url,$qry) = explode("?",$target);
			$text = "\n<form {$class} action='{$url}' id='".$this->name2id($name)."' method = '{$method}'{$autoComplete}>\n";
			
			parse_str($qry,$m);
			foreach($m as $k=>$v)
			{
				$text .= $this->hidden($k, $v);					
			}
			
		}	
		else 
		{
			$target = str_replace("&", "&amp;", $target);
			$text = "\n<form {$class} action='{$target}' id='".$this->name2id($name)."' method = '{$method}'{$autoComplete}>\n";
		}
		return $text;	
	}
	
	/**
	 * Close a Form
	 */
	public function close()
	{
		return "</form>";	
		
	}


	/**
	 * Get required field markup string
	 * @return string
	 */
	public function getRequiredString()
	{
		return $this->_required_string;
	}

	/**
	 * Set required field markup string
	 * @param string $string
	 * @return e_form
	 */
	public function setRequiredString($string)
	{
		$this->_required_string = $string;
		return $this;
	}
	
	// For Comma separated keyword tags. 
	function tags($name, $value, $maxlength = 200, $options = array())
	{
		if(is_string($options)) parse_str($options, $options);
		$options['class'] = 'tbox span1 e-tags';
		$options['size'] = 7;
		return $this->text($name, $value, $maxlength, $options);	
	}





	
	/**
	 * Render Bootstrap Tabs
	 * @param $array
	 * @param $options
	 * @example
	 * $array = array(
	 * 		'home' => array('caption' => 'Home', 'text' => 'some tab content' ),
	 * 		'other' => array('caption' => 'Other', 'text' => 'second tab content' )
	 * 	);
	 */
	function tabs($array,$options = array())
	{
		
		$text  ='
		<!-- Nav tabs -->
			<ul class="nav nav-tabs">';

		$c = 0;
		foreach($array as $key=>$tab)
		{
			if(is_numeric($key))
			{
				$key = 'tab-'.$this->name2id($tab['caption']);
			}
			
			$active = ($c == 0) ? ' class="active"' : '';
			$text .= '<li'.$active.'><a href="#'.$key.'" data-toggle="tab">'.$tab['caption'].'</a></li>';
			$c++;
		}
		
		$text .= '</ul>';

		$text .= '
		<!-- Tab panes -->
		<div class="tab-content">';
		
		$c=0;
		foreach($array as $key=>$tab)
		{
			if(is_numeric($key))
			{
				$key = 'tab-'.$this->name2id($tab['caption']);
			}
			
			$active = ($c == 0) ? ' active' : '';
			$text .= '<div class="tab-pane'.$active.'" id="'.$key.'">'.$tab['text'].'</div>';
			$c++;
		}
		
		$text .= '
		</div>';

		return $text;

	}


	/**
	 * Render Bootstrap Carousel
	 * @param string $name : A unique name
	 * @param array $array
	 * @param array $options : default, interval, pause, wrap
	 * @return string
	 * @example
	 * $array = array(
	 *        'slide1' => array('caption' => 'Slide 1', 'text' => 'first slide content' ),
	 *        'slide2' => array('caption' => 'Slide 2', 'text' => 'second slide content' ),
	 *        'slide3' => array('caption' => 'Slide 3', 'text' => 'third slide content' )
	 *    );
	 */
	function carousel($name="e-carousel", $array, $options = null)
	{
		$interval = null;
		$wrap = null;
		$pause = null;
				
		$act = varset($options['default'], 0);
		
		if(isset($options['wrap']))
		{
			$wrap = 'data-wrap="'.$options['wrap'].'"';	
		}
		
		if(isset($options['interval']))
		{
			$interval = 'data-interval="'.$options['interval'].'"';	
		}
		
		if(isset($options['pause']))
		{
			$interval = 'data-pause="'.$options['pause'].'"';	
		}
		
		$text  ='
		<!-- Carousel -->
		
		<div id="'.$name.'" class="carousel slide" data-ride="carousel" '.$interval.' '.$wrap.' '.$pause.'>
  		<!-- Indicators -->
  		<ol class="carousel-indicators">
		';

		$c = 0;
		foreach($array as $key=>$tab)
		{
			$active = ($c == $act) ? ' class="active"' : '';
			$text .=  '<li data-target="#'.$name.'" data-slide-to="'.$c.'" '.$active.'></li>';
			$c++;
		}
		
		$text .= '
		</ol>

		<div class="carousel-inner">
		';

		
		$c=0;
		foreach($array as $key=>$tab)
		{
			$active = ($c == $act) ? ' active' : '';
			$text .= '<div class="item'.$active.'" id="'.$key.'">';
			$text .= $tab['text'];
			
			if(!empty($tab['caption']))
			{
				$text .= '<div class="carousel-caption">'.$tab['caption'].'</div>';	
			}
			
			$text .= '</div>';
			$c++;
		}
		
		$text .= '
		</div>';
		
		$text .= '
		<a class="left carousel-control" href="#'.$name.'" role="button" data-slide="prev">
    	<span class="glyphicon glyphicon-chevron-left"></span>
		</a>
		<a class="right carousel-control" href="#'.$name.'" role="button" data-slide="next">
		<span class="glyphicon glyphicon-chevron-right"></span>
		</a>';
		
		$text .= '</div><!-- End Carousel -->';

		return $text;

	}	

	/**
	 * Same as $this->text() except it adds input validation for urls. 
	 * At this stage, checking only for spaces. Should include sef-urls. 
	 */
	function url($name, $value = '', $maxlength = 80, $options= array())
	{
		$options['pattern'] = '^\S*$';
		return $this->text($name, $value, $maxlength, $options);
	}

	/**
	 * Text-Field Form Element
	 * @param $name
	 * @param $value
	 * @param $maxlength
	 * @param $options
	 *  - size: mini, small, medium, large, xlarge, xxlarge
	 *  - class:
	 *  - typeahead: 'users'
	 *
	 * @return string
	 */
	function text($name, $value = '', $maxlength = 80, $options= array())
	{
		if(is_string($options))
		{
			parse_str($options,$options);
		}

		if(!vartrue($options['class']))
		{
			$options['class'] = "tbox";		
		}

		if(deftrue('BOOTSTRAP') === 3)
		{
			$options['class'] .= ' form-control';
		}
		
		/*
		if(!vartrue($options['class']))
		{
			if($maxlength < 10)
			{
				$options['class'] = 'tbox input-text span3';
			}
			
			elseif($maxlength < 50)
			{
				$options['class'] = 'tbox input-text span7';	
			}
		
			elseif($maxlength > 99)
			{
				 $options['class'] = 'tbox input-text span7';
			}
			else
			{
				$options['class'] = 'tbox input-text';
			}
		}	
		*/
		
		if(vartrue($options['typeahead']))
		{
			if(vartrue($options['typeahead']) == 'users')
			{
				$options['data-source'] = e_BASE."user.php";	
				$options['class'] .= " e-typeahead";			
			}		
		}
		
		if(vartrue($options['size']) && !is_numeric($options['size']))
		{
			$options['class'] .= " input-".$options['size'];	
			unset($options['size']); // don't include in html 'size='. 	
		}
			
		$mlength = vartrue($maxlength) ? "maxlength=".$maxlength : "";
		
		$type = varset($options['type']) == 'email' ? 'email' : 'text'; // used by $this->email(); 
				
		$options = $this->format_options('text', $name, $options);
		
	
		//never allow id in format name-value for text fields
		return "<input type='".$type."' name='{$name}' value='{$value}' {$mlength} ".$this->get_attributes($options, $name)." />";
	}


	
	function number($name, $value=0, $maxlength = 200, $options = array())
	{
		if(is_string($options)) parse_str($options, $options);
		if (vartrue($options['maxlength'])) $maxlength = $options['maxlength'];
		unset($options['maxlength']);
		if(!vartrue($options['size'])) $options['size'] = 15;
		if(!vartrue($options['class'])) $options['class'] = 'tbox number e-spinner input-small form-control';
		
		
		$options['type'] ='number';
		
		$mlength = vartrue($maxlength) ? "maxlength=".$maxlength : "";

		$min = varset($options['min']) ? 'min="'.$options['min'].'"' : '';
		$max = vartrue($options['max']) ? 'max="'.$options['max'].'"' : '';

		$options = $this->format_options('text', $name, $options);
		

		
		//never allow id in format name-value for text fields
		if(deftrue('BOOTSTRAP'))
		{
			return "<input pattern='[0-9]*' type='number' name='{$name}' value='{$value}' {$mlength}  {$min} {$max} ".$this->get_attributes($options, $name)." />";
		}
		
		return $this->text($name, $value, $maxlength, $options);	
	}


	
	function email($name, $value, $maxlength = 200, $options = array())
	{
		$options['type'] = 'email';
		return $this->text($name,$value,$maxlength,$options);
	}



	function iconpreview($id, $default, $width='', $height='') // FIXME
	{
		// XXX - $name ?!
	//	$parms = $name."|".$width."|".$height."|".$id;
		$sc_parameters = 'mode=preview&default='.$default.'&id='.$id;
		return e107::getParser()->parseTemplate("{ICONPICKER=".$sc_parameters."}");
	}

	/**
	 * @param $name
	 * @param $default value
	 * @param $label
	 * @param $options - gylphs=1 
	 * @param $ajax
	 */
	function iconpicker($name, $default, $label, $options = array(), $ajax = true)
	{


		$options['media'] = '_icon';
		
		return $this->imagepicker($name, $default, $label, $options);
		

	}

	/**
	 * Internal Function used by imagepicker and filepicker
	 */ 
	private function mediaUrl($category = '', $label = '', $tagid='', $extras=null)
	{
		
		$cat = ($category) ? '&amp;for='.$category : "";
		if(!$label) $label = ' Upload an image or file';
		if($tagid) $cat .= '&amp;tagid='.$tagid; 
		
		if(is_string($extras))
		{
			parse_str($extras,$extras);
		}
		
		if(vartrue($extras['bbcode'])) $cat .= '&amp;bbcode=1'; 	
		$mode = vartrue($extras['mode'],'main');
		$action = vartrue($extras['action'],'dialog'); 
		// $tabs // TODO - option to choose which tabs to display.  
		
		//TODO Parse selection data back to parent form. 

		$url = e_ADMIN_ABS."image.php?mode={$mode}&amp;action={$action}".$cat;
		$url .= "&amp;iframe=1";
		
		if(vartrue($extras['w']))
		{
			$url .= "&amp;w=".$extras['w'];	
		}

		if(vartrue($extras['glyphs']))
		{
			$url .= "&amp;glyphs=1";	
		}	
		
		if(vartrue($extras['video']))
		{
			$url .= "&amp;video=1";	
		}			
		
		$title = "Media Manager : ".$category;

	//	$ret = "<a title=\"{$title}\" rel='external' class='e-dialog' href='".$url."'>".$label."</a>"; // using colorXXXbox. 
	 $ret = "<a title=\"{$title}\" class='e-modal' data-modal-caption='Media Manager' data-cache='false' data-target='#uiModal' href='".$url."'>".$label."</a>"; // using bootstrap. 

	
	//	$footer = "<div style=\'padding:5px;text-align:center\' <a href=\'#\' >Save</a></div>";
	$footer = '';
		if(!e107::getRegistry('core/form/mediaurl'))
		{
			/*
			e107::js('core','core/admin.js','prototype');
			e107::js('core','core/dialog.js','prototype');
			e107::js('core','core/draggable.js','prototype');
			e107::css('core','core/dialog/dialog.css','prototype');
			e107::css('core','core/dialog/e107/e107.css','prototype');
			e107::js('footer-inline','
			$$("a.e-dialog").invoke("observe", "click", function(ev) {
					var element = ev.findElement("a");
					ev.stop();
					new e107Widgets.URLDialog(element.href, {
						id: element["id"] || "e-dialog",
						width: 890,
						height: 680
		
					}).center().setHeader("Media Manager : '.$category.'").setFooter('.$footer.').activate().show();
				});
			
			','prototype');
			*/
			e107::setRegistry('core/form/mediaurl', true);
		}
		return $ret;
	}


	/**
	 * Avatar Picker
	 * @param $name - form element name ie. value to be posted. 
	 * @param $curVal - current avatar value. ie. the image-file name or URL. 
	 */
	function avatarpicker($name,$curVal='',$options=array())
	{
		
		$tp 		= e107::getParser();
		$pref 		= e107::getPref();
		
		$attr 		= "aw=".$pref['im_width']."&ah=".$pref['im_height'];
		$tp->setThumbSize($pref['im_width'],$pref['im_height']);
		
		$blankImg 	= $tp->thumbUrl(e_IMAGE."generic/blank_avatar.jpg",$attr);
		$localonly 	= true; //TODO add a pref for allowing external or internal avatars or both. 
		$idinput 	= $this->name2id($name);
		$previnput	= $idinput."-preview";
		$optioni 	= $idinput."-options";
		
		
		$path = (substr($curVal,0,8) == '-upload-') ? '{e_AVATAR}upload/' : '{e_AVATAR}default/';
		$newVal = str_replace('-upload-','',$curVal);
	
		$img = (strpos($curVal,"://")!==false) ? $curVal : $tp->thumbUrl($path.$newVal);
				
		if(!$curVal)
		{
			$img = $blankImg;	
		}
		
		if($localonly == true)
		{
			$text = "<input class='tbox' style='width:80%' id='{$idinput}' type='hidden' name='image' size='40' value='{$curVal}' maxlength='100' />";			
			$text .= "<img src='".$img."' id='{$previnput}' class='img-rounded e-expandit e-tip avatar' style='cursor:pointer; width:".$pref['im_width']."px; height:".$pref['im_height']."px' title='Click on the avatar to change it'/>"; // TODO LAN
		}
		else
		{			
			$text = "<input class='tbox' style='width:80%' id='{$idinput}' type='text' name='image' size='40' value='$curVal' maxlength='100' title=\"".LAN_SIGNUP_111."\" />";
			$text .= "<img src='".$img."' id='{$previnput}' style='display:none' />";
			$text .= "<input class='img-rounded btn btn-default button e-expandit' type ='button' style='cursor:pointer' size='30' value=\"Choose Avatar\"  />"; //TODO Common LAN. 
		}
						
		$avFiles = e107::getFile()->get_files(e_AVATAR_DEFAULT,".jpg|.png|.gif|.jpeg|.JPG|.GIF|.PNG");
			
		$text .= "\n<div id='{$optioni}' style='display:none;padding:10px' >\n"; //TODO unique id. 
		
		if (vartrue($pref['avatar_upload']) && FILE_UPLOADS && vartrue($options['upload']))
		{
				$diz = LAN_USET_32.($pref['im_width'] || $pref['im_height'] ? "\n".str_replace(array('--WIDTH--','--HEIGHT--'), array($pref['im_width'], $pref['im_height']), LAN_USER_86) : "");
	
				$text .= "<div style='margin-bottom:10px'>".LAN_USET_26."
				<input  class='tbox' name='file_userfile[avatar]' type='file' size='47' title=\"{$diz}\" />
				</div>";
				
				if(count($avFiles) > 0)
				{
					$text .= "<div class='divider'><span>OR</span></div>";
				}
		}
		
				
		foreach($avFiles as $fi)
		{
			$img_path = $tp->thumbUrl(e_AVATAR_DEFAULT.$fi['fname']);	
			$text .= "\n<a class='e-expandit' title='Choose this avatar' href='#{$optioni}'><img src='".$img_path."' alt=''  onclick=\"insertext('".$fi['fname']."', '".$idinput."');document.getElementById('".$previnput."').src = this.src;\" /></a> ";			
			//TODO javascript CSS selector 		
		}
		
		
		
		
		$text .= "<br />
		</div>";
		
		// Used by usersettings.php right now. 
		
	
		
		
		
		
		
		return $text;
		
		//TODO discuss and FIXME
		    // Intentionally disable uploadable avatar and photos at this stage
			if (false && $pref['avatar_upload'] && FILE_UPLOADS)
			{
				$text .= "<br /><span class='smalltext'>".LAN_SIGNUP_25."</span> <input class='tbox' name='file_userfile[]' type='file' size='40' />
				<br /><div class='smalltext'>".LAN_SIGNUP_34."</div>";
			}
		
			if (false && $pref['photo_upload'] && FILE_UPLOADS)
			{
				$text .= "<br /><span class='smalltext'>".LAN_SIGNUP_26."</span> <input class='tbox' name='file_userfile[]' type='file' size='40' />
				<br /><div class='smalltext'>".LAN_SIGNUP_34."</div>";
			}  
	}




	/**
	 * FIXME {IMAGESELECTOR} rewrite
	
	 * @param string $name input name
	 * @param string $default default value
	 * @param string $label custom label
	 * @param string $sc_parameters shortcode parameters
	 *  --- SC Parameter list --- 
	 * - media: if present - load from media category table
	 * - w: preview width in pixels
	 * - h: preview height in pixels
	 * - help: tooltip
	 * - video: when set to true, will enable the Youtube  (video) tab. 
	 * @example $frm->imagepicker('banner_image', $_POST['banner_image'], '', 'banner'); // all images from category 'banner_image' + common images. 
	 * @example $frm->imagepicker('banner_image', $_POST['banner_image'], '', 'media=banner&w=600');
	 * @return string html output
	 */
	function imagepicker($name, $default, $label = '', $sc_parameters = '')
	{
		$tp = e107::getParser();
		$name_id = $this->name2id($name);
		$meta_id = $name_id."-meta";
		
		if(is_string($sc_parameters))
		{
			if(strpos($sc_parameters, '=') === false) $sc_parameters = 'media='.$sc_parameters;
			parse_str($sc_parameters, $sc_parameters);
		}


	//	print_a($sc_parameters);
	
		if(empty($sc_parameters['media']))
		{
			$sc_parameters['media'] = '_common';	
		}
	
		$default_thumb = $default;
		if($default)
		{
			if($video = $tp->toVideo($default, array('thumb'=>'src')))
			{
				$default_url = $video;	
			}
			else 
			{
				if('{' != $default[0])
				{
					// convert to sc path
					$default_thumb = $tp->createConstants($default, 'nice');
					$default = $tp->createConstants($default, 'mix');
				}
				$default_url = $tp->replaceConstants($default, 'abs');
			}
			$blank = FALSE;
			
			
		}
		else
		{
			//$default = $default_url = e_IMAGE_ABS."generic/blank.gif";
			$default_url = e_IMAGE_ABS."generic/nomedia.png";
			$blank = TRUE;
		}
		
		
		
		//$width = intval(vartrue($sc_parameters['width'], 150));
		$cat = $tp->toDB(vartrue($sc_parameters['media']));	
		
		if($cat == '_icon') // ICONS
		{
			$ret = "<div class='imgselector-container'  style='display:block;width:64px;min-height:64px'>";
			$thpath = isset($sc_parameters['nothumb']) || vartrue($hide) ? $default : $default_thumb;
			$label = "<div id='{$name_id}_prev' class='text-center well well-small image-selector' >";
			
			$label .= $tp->toIcon($default_url);
			
			$label .= "				
			</div>";
			
		//	$label = "<img id='{$name_id}_prev' src='{$default_url}' alt='{$default_url}' class='well well-small image-selector' style='{$style}' />";
				
		}
		else // Images 
		{
			
			$title = (vartrue($sc_parameters['help'])) ? "title='".$sc_parameters['help']."'" : "";
			$width = vartrue($sc_parameters['w'], 120);
			$height = vartrue($sc_parameters['h'], 100);

			$ret = "<div class='imgselector-container e-tip' {$title} style='margin-right:25px; display:inline-block; width:".$width."px;min-height:".$height."px;'>";
			$att = 'aw='.$width."'&ah=".$height."'";
			$thpath = isset($sc_parameters['nothumb']) || vartrue($hide) ? $default : $tp->thumbUrl($default_thumb, $att, true);
			
			
			$label = "<img id='{$name_id}_prev' src='{$default_url}' alt='{$default_url}' class='well well-small image-selector' style='display:block;' />";
			
			if($cat != 'news' && $cat !='page' && $cat !='') 
			{
			 	$cat = $cat . "_image";		
			}
		}
		
		
		$ret .= $this->mediaUrl($cat, $label,$name_id,$sc_parameters);
		$ret .= "</div>\n";
		$ret .=	"<input type='hidden' name='{$name}' id='{$name_id}' value='{$default}' />"; 
		$ret .=	"<input type='hidden' name='mediameta_{$name}' id='{$meta_id}' value='' />"; 
	//	$ret .=	$this->text($name,$default); // to be hidden eventually. 
		return $ret;
		

		
		
		
		// ----------------

	}



			
	/**
	 * File Picker 
	 * @param string name  eg. 'myfield' or 'myfield[]'
	 * @param mixed default
	 * @param string label
	 * @param mixed sc_parameters
	 */		
	function filepicker($name, $default, $label = '', $sc_parameters = '')
	{
		$tp = e107::getParser();
		$name_id = $this->name2id($name);
				
		if(is_string($sc_parameters))
		{
			if(strpos($sc_parameters, '=') === false) $sc_parameters = 'media='.$sc_parameters;
			parse_str($sc_parameters, $sc_parameters);
		}

		$cat = vartrue($sc_parameters['media']) ? $tp->toDB($sc_parameters['media']) : "_common_file";	

		$ret = '';

		if($sc_parameters['data'] === 'array')
		{
			// Do not use $this->hidden() method - as it will break 'id' value. 
			$ret .=	"<input type='hidden' name='".$name."[path]' id='".$this->name2id($name."[path]")."' value='".varset($default['path'])."'  />"; 	
			$ret .=	"<input type='hidden' name='".$name."[name]' id='".$this->name2id($name."[name]")."' value='".varset($default['name'])."'  />"; 	
			$ret .=	"<input type='hidden' name='".$name."[id]' id='".$this->name2id($name."[id]")."' value='".varset($default['id'])."'  />"; 	
		
			$default = $default['path'];
		}	
		else
		{
			$ret .=	"<input type='hidden' name='{$name}' id='{$name_id}' value='{$default}' style='width:400px' />"; 	
		}
		
		
		$default_label 				= ($default) ? $default : "Choose a file";
		$label 						= "<span id='{$name_id}_prev' class='btn btn-default btn-small'>".basename($default_label)."</span>";
			
		$sc_parameters['mode'] 		= 'main';
		$sc_parameters['action'] 	= 'dialog';	
			
		
	//	$ret .= $this->mediaUrl($cat, $label,$name_id,"mode=dialog&action=list");
		$ret .= $this->mediaUrl($cat, $label,$name_id,$sc_parameters);
	
		
	
		
		return $ret;
	
		
	}




	/**
	 *	Date field with popup calendar // NEW in 0.8/2.0
	 *
	 * @param string $name the name of the field
	 * @param integer $datestamp UNIX timestamp - default value of the field
	 * @param array or str 
	 * @example $frm->datepicker('my_field',time(),'type=date');
	 * @example $frm->datepicker('my_field',time(),'type=datetime&inline=1');
	 * @example $frm->datepicker('my_field',time(),'type=date&format=yyyy-mm-dd');
	 * @example $frm->datepicker('my_field',time(),'type=datetime&format=MM, dd, yyyy hh:ii');
	 * 
	 * @url http://trentrichardson.com/examples/timepicker/
	 */
	function datepicker($name, $datestamp = false, $options = null)
	{
		
		if(vartrue($options) && is_string($options))
		{
			parse_str($options,$options);	
		} 
		
		$type		= varset($options['type']) ? trim($options['type']) : "date"; // OR  'datetime'
		$dateFormat = varset($options['format']) ? trim($options['format']) :e107::getPref('inputdate', '%Y-%m-%d');
		$ampm		= (preg_match("/%l|%I|%p|%P/",$dateFormat)) ? 'true' : 'false';	
		$value		= null;
				
		if($type == 'datetime' && !varset($options['format']))
		{
			$dateFormat .= " ".e107::getPref('inputtime', '%H:%M:%S');		
		}

		$dformat = e107::getDate()->toMask($dateFormat);

		$id = $this->name2id($name);

		$classes = array('date'	=> 'e-date', 'datetime'	=> 'e-datetime');
		
		if ($datestamp)
		{
		   $value = is_numeric($datestamp) ? e107::getDate()->convert_date($datestamp, $dateFormat) : $datestamp; //date("d/m/Y H:i:s", $datestamp);
		}

		$text = "";
	//	$text .= 'dformat='.$dformat.'  defdisp='.$dateFormat;
		
		$class 		= (isset($classes[$type])) ? $classes[$type] : "tbox e-date";
		$size 		= vartrue($options['size']) ? intval($options['size']) : 40;
		$required 	= vartrue($options['required']) ? "required" : "";
		$firstDay	= vartrue($options['firstDay']) ? $options['firstDay'] : 0;
		$xsize		= (vartrue($options['size']) && !is_numeric($options['size'])) ? $options['size'] : 'xlarge';
		
		if(vartrue($options['inline']))
		{
			$text .= "<div class='{$class}' id='inline-{$id}' data-date-format='{$dformat}'  data-date-ampm='{$ampm}' data-date-firstday='{$firstDay}' ></div>
				<input  type='hidden' name='{$name}' id='{$id}' value='{$value}' data-date-format='{$dformat}'  data-date-ampm='{$ampm}' data-date-firstday='{$firstDay}' />
			";
		}
		else
		{			
			$text .= "<input class='{$class} input-".$xsize." form-control' type='text' size='{$size}' name='{$name}' id='{$id}' value='{$value}' data-date-format='{$dformat}' data-date-ampm='{$ampm}'  data-date-language='".e_LAN."' data-date-firstday='{$firstDay}' {$required} />";		
		}

	//	$text .= "ValueFormat: ".$dateFormat."  Value: ".$value;
	//	$text .= " ({$dformat}) type:".$dateFormat." ".$value;
			
		return $text;


	}

	/**
	 * User auto-complete search
	 *
	 * @param string $name_fld field name for user name
	 * @param string $id_fld field name for user id
	 * @param string $default_name default user name value
	 * @param integer $default_id default user id
	 * @param array|string $options [optional] 'readonly' (make field read only), 'name' (db field name, default user_name)
	 * @return string HTML text for display
	 */
	function userpicker($name_fld, $id_fld, $default_name, $default_id, $options = array())
	{
		$tp = e107::getParser();
		if(!is_array($options)) parse_str($options, $options);
		
		$default_name = vartrue($default_name, '');
		$default_id = vartrue($default_id, 0);
		
		//TODO Auto-calculate $name_fld from $id_fld ie. append "_usersearch" here ?
		
		$fldid = $this->name2id($name_fld);
		$hidden_fldid = $this->name2id($id_fld);
		
		$ret = '<div class="input-append">';
		$ret .= $this->text($name_fld,$default_name,20, "class=e-tip&title=Type name of user&typeahead=users&readonly=".vartrue($options['readonly']))
		.$this->hidden($id_fld,$default_id, array('id' => $this->name2id($id_fld)))."<span class='add-on'>".$tp->toGlyph('fa-user')." <span  id='{$fldid}-id'>".$default_id.'</span></span>';
		$ret .= "<a class='btn btn-inverse' href='#' id='{$fldid}-reset'>reset</a>
		</div>";

		e107::js('footer-inline', "
			\$('#{$fldid}').blur(function () {
				\$('#{$fldid}-id').html(\$('#{$hidden_fldid}').val());
			});
			\$('#{$fldid}-reset').click(function () {
				\$('#{$fldid}-id').html('0');
				\$('#{$hidden_fldid}').val(0);
				\$('#{$fldid}').val('');
				return false;
			});
		");

		return $ret;
		/*
		$label_fld = str_replace('_', '-', $name_fld).'-upicker-lable';

		//'.$this->text($id_fld, $default_id, 10, array('id' => false, 'readonly'=>true, 'class'=>'tbox number')).'
		$ret = '
		<div class="e-autocomplete-c">
			'.$this->text($name_fld, $default_name, 150, array('id' => false, 'readonly' => vartrue($options['readonly']) ? true : false)).'
			<span id="'.$label_fld.'" class="'.($default_id ? 'success' : 'warning').'">Id #'.((int) $default_id).'</span>
			'.$this->hidden($id_fld, $default_id, array('id' => false)).'
				<span class="indicator" style="display: none;">
					<img src="'.e_IMAGE_ABS.'generic/loading_16.gif" class="icon action S16" alt="Loading..." />
				</span>
				<div class="e-autocomplete"></div>
		</div>
		';
				
		e107::getJs()->requireCoreLib('scriptaculous/controls.js', 2);

		e107::getJs()->footerInline("
	            //autocomplete fields
	             \$\$('input[name={$name_fld}]').each(function(el) {

	             	if(el.readOnly) {
	             		el.observe('click', function(ev) { ev.stop(); var el1 = ev.findElement('input'); el1.blur(); } );
	             		el.next('span.indicator').hide();
	             		el.next('div.e-autocomplete').hide();
	             		return;
					}
					new Ajax.Autocompleter(el, el.next('div.e-autocomplete'), '".e_JS."e_ajax.php', {
					  paramName: '{$name_fld}',
					  minChars: 2,
					  frequency: 0.5,
					  afterUpdateElement: function(txt, li) {
					  	if(!\$(li)) return;
					  	var elnext = el.next('input[name={$id_fld}]'),
					  		ellab = \$('{$label_fld}');
					  	if(\$(li).id) {
							elnext.value = parseInt(\$(li).id);
						} else {
							elnext.value = 0
						}
						if(ellab)
						{
							ellab.removeClassName('warning').removeClassName('success');
							ellab.addClassName((elnext.value ? 'success' : 'warning')).update('Id #' + elnext.value);
						}
					  },
					  indicator:  el.next('span.indicator'),
					  parameters: 'ajax_used=1&ajax_sc=usersearch=".rawurlencode('searchfld='.str_replace('user_', '', vartrue($options['name'], 'user_name')).'--srcfld='.$name_fld)."'
					});
				});
		");
		return $ret;
		*/
	}
	
	
	/**
	 * A Rating element
	 * @var $text 
	 */
	function rate($table,$id,$options=null)
	{		
		$table 	= preg_replace('/\W/', '', $table);
		$id 	= intval($id);
		
		return e107::getRate()->render($table, $id, $options);	
	}
		
	function like($table,$id,$options=null)
	{
		$table 	= preg_replace('/\W/', '', $table);
		$id 	= intval($id);	
		
		return e107::getRate()->renderLike($table,$id,$options); 	
	}
		
	
	
	

	function file($name, $options = array())
	{
		$options = $this->format_options('file', $name, $options);
		//never allow id in format name-value for text fields
		return "<input type='file' name='{$name}'".$this->get_attributes($options, $name)." />";
	}

	function upload($name, $options = array())
	{
		return 'Ready to use upload form fields, optional - file list view';
	}

	function password($name, $value = '', $maxlength = 50, $options = array())
	{
		if(is_string($options)) parse_str($options, $options);
		
		$addon = "";
		$gen = "";
		
		if(vartrue($options['generate']))
		{
			$gen = '&nbsp;<a href="#" class="btn btn-default btn-small e-tip" id="Spn_PasswordGenerator" title="Generate a password">Generate</a> ';
			
			if(empty($options['nomask']))
			{
				$gen .= '<a class="btn btn-default btn-small e-tip" href="#" id="showPwd" title="Display the password">Show</a><br />';	
			}
		}
		
		if(vartrue($options['strength']))
		{
			$addon .= "<div style='margin-top:4px'><div id='pwdColor' class='progress' style='float:left;display:inline-block;width:218px'><div class='bar' id='pwdMeter' style='width:0%' ></div></div> <div id='pwdStatus' class='smalltext' style='float:left;display:inline-block;width:150px;margin-left:5px'></span></div>";	
		}
		
		$options['pattern'] = vartrue($options['pattern'],'[\S]{4,}');
		$options['required'] = varset($options['required'], 1);
		$options['class'] = vartrue($options['class'],'e-password');
		
		if(deftrue('BOOTSTRAP') == 3)
		{
			$options['class'] .= ' form-control';
		}
		
		if(vartrue($options['size']) && !is_numeric($options['size']))
		{
			$options['class'] .= " input-".$options['size'];	
			unset($options['size']); // don't include in html 'size='. 	
		}
		
		$type = empty($options['nomask']) ? 'password' : 'text';
		
		$options = $this->format_options('text', $name, $options);

	
		//never allow id in format name-value for text fields
		$text = "<input type='".$type."' name='{$name}' value='{$value}' maxlength='{$maxlength}'".$this->get_attributes($options, $name)." />";

		if(empty($gen) && empty($addon))
		{
			return $text;	
		}
		else 
		{
			return "<span class='form-inline'>".$text.$gen."</span>".vartrue($addon);
		}	
		
	}



	/**
	 * Render a bootStrap ProgressBar. 
	 * @param string $name
	 * @param number $value
	 * @param array $options
	 * @example  Use 
	 */
	public function progressBar($name,$value,$options=array())
	{
		if(!deftrue('BOOTSTRAP'))
		{
			return;
		}		
			
		$class = vartrue($options['class'],'');	
		$target = $this->name2id($name);
		
		$striped = (vartrue($options['btn-label'])) ? ' progress-striped active' : '';	
		
		$text =	"<div class='progress ".$class."{$striped}' >
   		 	<div id='".$target."' class='progress-bar bar' style='width: ".number_format($value,1)."%'></div>
    	</div>";
		
		$loading = vartrue($options['loading'],'Please wait...');
		
		$buttonId = $target.'-start';
		
		
		
		if(vartrue($options['btn-label']))
		{
			$interval = vartrue($options['interval'],1000);
			$text .= '<a id="'.$buttonId.'" data-loading-text="'.$loading.'" data-progress-interval="'.$interval.'" data-progress-target="'.$target.'" data-progress="' . $options['url'] . '" data-progress-mode="'.varset($options['mode'],0).'" data-progress-show="'.$nextStep.'" data-progress-hide="'.$buttonId.'" class="btn btn-primary e-progress" >'.$options['btn-label'].'</a>';
			$text .= ' <a data-progress-target="'.$target.'" class="btn btn-danger e-progress-cancel" >'.LAN_CANCEL.'</a>';
		}
		
		
		return $text;
		
	}


	/**
	 * Textarea Element
	 * @param $name
	 * @param $value
	 * @param $rows
	 * @param $cols
	 * @param $options
	 * @param $count
	 * @return string
	 */
	function textarea($name, $value, $rows = 10, $cols = 80, $options = array(), $counter = false)
	{
		if(is_string($options)) parse_str($options, $options);
		// auto-height support
	
		if(vartrue($options['size']) && !is_numeric($options['size']))
		{
			$options['class'] .= " form-control input-".$options['size'];	
			unset($options['size']); // don't include in html 'size='. 	
		}
		elseif(!vartrue($options['noresize']))
		{
			$options['class'] = (isset($options['class']) && $options['class']) ? $options['class'].' e-autoheight' : 'tbox span7 e-autoheight';
		}

		$options = $this->format_options('textarea', $name, $options);
		
//		print_a($options);
		//never allow id in format name-value for text fields
		return "<textarea name='{$name}' rows='{$rows}' cols='{$cols}'".$this->get_attributes($options, $name).">{$value}</textarea>".(false !== $counter ? $this->hidden('__'.$name.'autoheight_opt', $counter) : '');
	}

	/**
	 * Bbcode Area. Name, value, template, media-Cat, size, options array eg. counter
	 * IMPORTANT: $$mediaCat is also used is the media-manager category identifier
	 * @param $name
	 * @param $value
	 * @param $template
	 * @param $mediaCat _common
	 * @param $size : small | medium | large
	 * @param $options array(); 
	 */
	function bbarea($name, $value, $template = '', $mediaCat='_common', $size = 'large', $options = array())
	{
		if(is_string($options)) parse_str($options, $options);		
		//size - large|medium|small
		//width should be explicit set by current admin theme
	//	$size = 'input-large';
		
		switch($size)
		{
			case 'tiny':
				$rows = '3';
			//	$height = "style='height:250px'"; // inline required for wysiwyg
			break;
			
			
			case 'small':
				$rows = '7';
				$height = "style='height:250px'"; // inline required for wysiwyg
			break;
						
			case 'medium':
				$rows = '10';
               
				$height = "style='height:375px'"; // inline required for wysiwyg
				$size = "input-block-level";
			break;

			case 'large':
			default:
				$rows = '15';
				$size = 'large input-block-level';
			//	$height = "style='height:500px;width:1025px'"; // inline required for wysiwyg
			break;
		}

		// auto-height support
	   	$options['class'] 	= 'tbox bbarea '.($size ? ' '.$size : '').' e-wysiwyg e-autoheight form-control';
		$bbbar 				= '';
		

		$help_tagid 		= $this->name2id($name)."--preview"; 
		$options['other'] 	= "onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);' {$height}";
	
		$counter 			= vartrue($options['counter'],false); 
		
		$ret = "<div class='bbarea {$size}'>
		<div class='field-spacer'><!-- --></div>\n";
		

		$ret .=	e107::getBB()->renderButtons($template,$help_tagid);
		$ret .=	$this->textarea($name, $value, $rows, 70, $options, $counter); // higher thank 70 will break some layouts. 
			
		$ret .= "</div>\n";
		
		$_SESSION['media_category'] = $mediaCat; // used by TinyMce. 
		
		e107::wysiwyg(true); // bbarea loaded, so activate wysiwyg (if enabled in preferences)
	
		
		return $ret;
		
		// Quick fix - hide TinyMCE links if not installed, dups are handled by JS handler
		/*
		
				e107::getJs()->footerInline("
						if(typeof tinyMCE === 'undefined')
						{
							\$$('a.e-wysiwyg-switch').invoke('hide');
						}
				");
		*/
		
		
	}

	/**
	* Render a checkbox 
	* @param string $name
	* @param mixed $value
	* @param boolean $checked
	* @param mixed $options query-string or array or string for a label. eg. label=Hello&foo=bar or array('label'=>Hello') or 'Hello'
	* @return string
	*/
	function checkbox($name, $value, $checked = false, $options = array())
	{
		if(!is_array($options))
		{
			if(strpos($options,"=")!==false)
			{
			 	parse_str($options, $options);
			}
			else // Assume it's a label. 
			{
				$options = array('label'=>$options);
			}
	
		}

		$labelClass = (!empty($options['inline'])) ? 'checkbox-inline' : 'checkbox';
		$labelTitle = '';

		$options = $this->format_options('checkbox', $name, $options);
		
		$options['checked'] = $checked; //comes as separate argument just for convenience
		
		$text = "";

		$active = ($checked === true) ? " active" : ""; // allow for styling if needed.

		if(!empty($options['label'])) // add attributes to <label>
		{
			if(!empty($options['title']))
			{
				$labelTitle = " title=\"".$options['title']."\"";
				unset($options['title']);
			}

			if(!empty($options['class']))
			{
				$labelClass .= " ".$options['class'];
				unset($options['class']);
			}
		}

		$pre = (vartrue($options['label'])) ? "<label class='".$labelClass.$active."'{$labelTitle}>" : ""; // Bootstrap compatible markup
		$post = (vartrue($options['label'])) ? $options['label']."</label>" : "";
		unset($options['label']); // not to be used as attribute; 
		
		$text .= "<input type='checkbox' name='{$name}' value='{$value}'".$this->get_attributes($options, $name, $value)." />";
		
		return $pre.$text.$post;
	}


	/**
	 * Render an array of checkboxes. 
	 * @param string $name
	 * @param array $option_array
	 * @param mixed $checked
	 * @param array $options [optional]
	 */
	function checkboxes($name, $option_array, $checked, $options=array())
	{
		$name = (strpos($name, '[') === false) ? $name.'[]' : $name;
		if(!is_array($checked)) $checked = explode(",",$checked);
		
		$text = "";

		$cname = $name;

		foreach($option_array as $k=>$label)
		{
			if(!empty($options['useKeyValues'])) // ie. auto-generated
			{
				$key = $k;
				$c = in_array($k, $checked) ? true : false;
			}
			else
			{
				$key = 1;
				$cname = str_replace('[]','['.$k.']',$name);
				$c = vartrue($checked[$k]);
			}


			$text .= $this->checkbox($cname, $key, $c, $label);
		}

		return $text;
		
	}
	

	function checkbox_label($label_title, $name, $value, $checked = false, $options = array())
	{
		return $this->checkbox($name, $value, $checked, $options).$this->label($label_title, $name, $value);
	}

	function checkbox_switch($name, $value, $checked = false, $label = '')
	{
		return $this->checkbox($name, $value, $checked).$this->label($label ? $label : LAN_ENABLED, $name, $value);
	}

	function checkbox_toggle($name, $selector = 'multitoggle', $id = false, $label='')
	{
		$selector = 'jstarget:'.$selector;
		if($id) $id = $this->name2id($id);
		
		return $this->checkbox($name, $selector, false, array('id' => $id,'class' => 'checkbox toggle-all','label'=>$label));
	}

	function uc_checkbox($name, $current_value, $uc_options, $field_options = array())
	{
		if(!is_array($field_options)) parse_str($field_options, $field_options);
		return '
			<div class="check-block">
				'.$this->_uc->vetted_tree($name, array($this, '_uc_checkbox_cb'), $current_value, $uc_options, $field_options).'
			</div>
		';
	}


	/**
	 *	Callback function used with $this->uc_checkbox
	 *
	 *	@see user_class->select() for parameters
	 */
	function _uc_checkbox_cb($treename, $classnum, $current_value, $nest_level, $field_options)
	{
		if($classnum == e_UC_BLANK)
			return '';

		if (!is_array($current_value))
		{
			$tmp = explode(',', $current_value);
		}

		$classIndex = abs($classnum);			// Handle negative class values
		$classSign = (substr($classnum, 0, 1) == '-') ? '-' : '';

		$class = $style = '';
		if($nest_level == 0)
		{
			$class = " strong";
		}
		else
		{
			$style = " style='text-indent:" . (1.2 * $nest_level) . "em'";
		}
		$descr = varset($field_options['description']) ? ' <span class="smalltext">('.$this->_uc->uc_get_classdescription($classnum).')</span>' : '';

		return "<div class='field-spacer{$class}'{$style}>".$this->checkbox($treename.'[]', $classnum, in_array($classnum, $tmp), $field_options).$this->label($this->_uc->uc_get_classname($classIndex).$descr, $treename.'[]', $classnum)."</div>\n";
	}


	function uc_label($classnum)
	{
		return $this->_uc->uc_get_classname($classnum);
	}

	/**
	 * A Radio Button Form Element
	 * @param $name
	 * @param @value array pair-values|string - auto-detected. 
	 * @param $checked boolean
	 * @param $options 
	 */
	function radio($name, $value, $checked = false, $options = null)
	{
		
		if(!is_array($options)) parse_str($options, $options);
		
		if(is_array($value))
		{
			return $this->radio_multi($name, $value, $checked, $options);
		}
		
		$labelFound = vartrue($options['label']);
		unset($options['label']); // label attribute not valid in html5
				
		$options = $this->format_options('radio', $name, $options);
		$options['checked'] = $checked; //comes as separate argument just for convenience
		// $options['class'] = 'inline';	
		$text = "";
		

		
	//	return print_a($options,true);
		if($labelFound) // Bootstrap compatible markup
		{
			$dis = (!empty($options['disabled'])) ? " disabled" : "";
			$text .= "<label class='radio inline{$dis}'>";
			
		}
		
	
		
		
		$text .= "<input type='radio' name='{$name}' value='".$value."'".$this->get_attributes($options, $name, $value)." />";
		
		if(vartrue($options['help']))
		{
			$text .= "<div class='field-help'>".$options['help']."</div>";
		}
		
		if($labelFound)
		{
			$text .= "<span>".$labelFound."</span></label>";
		}
		
		return $text;
	}

	/**
	 * Boolean Radio Buttons. 
	 * @param name string
	 * @param check_enabled boolean
	 * @param label_enabled default is LAN_ENABLED
	 * @param label_disabled default is LAN_DISABLED
	 * @param options array - inverse=1 (invert values) or reverse=1 (switch display order) 
	 */
	function radio_switch($name, $checked_enabled = false, $label_enabled = '', $label_disabled = '',$options=array())
	{
		if(!is_array($options)) parse_str($options, $options);
		
		$options_on = varset($options['enabled'],array());
		$options_off = varset($options['disabled'],array());
		
		if(vartrue($options['class']) == 'e-expandit' || vartrue($options['expandit'])) // See admin->prefs 'Single Login' for an example. 
		{
			$options_on = array_merge($options, array('class' => 'e-expandit-on'));
			$options_off = array_merge($options, array('class' => 'e-expandit-off'));	
		}
		
		$options_on['label'] = $label_enabled ? defset($label_enabled,$label_enabled) : LAN_ENABLED; 
		$options_off['label'] = $label_disabled ? defset($label_disabled,$label_disabled) : LAN_DISABLED; 
		
		if(!empty($options['inverse'])) // Same as 'writeParms'=>'reverse=1&enabled=LAN_DISABLED&disabled=LAN_ENABLED'  
		{
			$text = $this->radio($name, 0, !$checked_enabled, $options_on)." 	".$this->radio($name, 1, $checked_enabled, $options_off);		
			
		}
		elseif(!empty($options['reverse'])) // reverse display order. 
		{
			$text = $this->radio($name, 0, !$checked_enabled, $options_off)." ".$this->radio($name, 1, $checked_enabled, $options_on);		
		}
		else
		{
			
			$text = $this->radio($name, 1, $checked_enabled, $options_on)." 	".$this->radio($name, 0, !$checked_enabled, $options_off);	
		}

		return $text;
		
	}


	/**
	 * XXX INTERNAL ONLY - Use radio() instead. array will automatically trigger this internal method.  
	 * @param string $name 
	 * @param array $elements = arrays value => label
	 * @param string/integer $checked = current value
	 * @param boolean $multi_line
	 * @param mixed $help array of field help items or string of field-help (to show on all)
	 */
	private function radio_multi($name, $elements, $checked, $options=array(), $help = null)
	{
		
		
		
		/* // Bootstrap Test. 
		 return'    <label class="checkbox">
    <input type="checkbox" value="">
    Option one is this and that—be sure to include why its great
    </label>
     
    <label class="radio">
    <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked>
    Option one is this and that—be sure to include why its great
    </label>
    <label class="radio">
    <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
    Option two can be something else and selecting it will deselect option one
    </label>';
		*/
		
		
		$text = array();
				
		if(is_string($elements)) parse_str($elements, $elements);
		if(!is_array($options)) parse_str($options, $options);
		$help = '';
		if(vartrue($options['help']))
		{
			$help = "<div class='field-help'>".$options['help']."</div>";
			unset($options['help']);
		}
		
		foreach ($elements as $value => $label)
		{
			$label = defset($label, $label);
			
			$helpLabel = (is_array($help)) ? vartrue($help[$value]) : $help;
		
		// Bootstrap Style Code - for use later. 	
			$options['label'] = $label;
			$options['help'] = $helpLabel;
			$text[] = $this->radio($name, $value, (string) $checked === (string) $value, $options);
	
		//	$text[] = $this->radio($name, $value, (string) $checked === (string) $value)."".$this->label($label, $name, $value).(isset($helpLabel) ? "<div class='field-help'>".$helpLabel."</div>" : '');
		}
		
		if($multi_line === false)
		{
		//	return implode("&nbsp;&nbsp;", $text);
		}
		
		// support of UI owned 'newline' parameter
		if(!varset($options['sep']) && vartrue($options['newline']))  $options['sep'] = '<br />'; // TODO div class=separator?
		$separator = varset($options['sep']," ");
	//	return print_a($text,true);
		return implode($separator, $text).$help;
		
		// return implode("\n", $text);
		//XXX Limiting markup. 
	//	return "<div class='field-spacer' style='width:50%;float:left'>".implode("</div><div class='field-spacer' style='width:50%;float:left'>", $text)."</div>";

	}

	/**
	 * Just for BC - use the $options['label'] instead. 
	 */
	function label($text, $name = '', $value = '')
	{
	//	$backtrack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2); 
	//	e107::getMessage()->addDebug("Deprecated \$frm->label() used in: ".print_a($backtrack,true));
		$for_id = $this->_format_id('', $name, $value, 'for');
		return "<label$for_id class='e-tip legacy'>{$text}</label>";
	}
	
	function help($text)
	{
		return !empty($text) ? '<div class="field-help">'.$text.'</div>' : '';
	}

	function select_open($name, $options = array())
	{
		if(!is_array($options)) parse_str($options, $options);
		
		if(vartrue($options['size']) && !is_numeric($options['size']))
		{
			$options['class'] .= " input-".$options['size'];	
			unset($options['size']); // don't include in html 'size='. 	
		}
		$options = $this->format_options('select', $name, $options);
	
		return "<select name='{$name}'".$this->get_attributes($options, $name).">";
	}


	/**
	 * @DEPRECATED - use select() instead. 
	 */
	function selectbox($name, $option_array, $selected = false, $options = array(), $defaultBlank= false)
	{	
		return $this->select($name, $option_array, $selected, $options, $defaultBlank);	
	}



	/**
	 *
	 * @param string $name
	 * @param array $option_array
	 * @param boolean $selected [optional]
	 * @param string|array $options [optional]
	 * @param boolean|string $defaultBlank [optional] set to TRUE if the first entry should be blank, or to a string to use it for the blank description. 
	 * @return string HTML text for display
	 */
	function select($name, $option_array, $selected = false, $options = array(), $defaultBlank= false)
	{
		if(!is_array($options)) parse_str($options, $options);

		if($option_array === 'yesno')
		{
			$option_array = array(1 => LAN_YES, 0 => LAN_NO);
		}

		if(vartrue($options['multiple']))
		{
			$name = (strpos($name, '[') === false) ? $name.'[]' : $name;
			if(!is_array($selected)) $selected = explode(",",$selected);
		}
		
		$text = $this->select_open($name, $options)."\n";

		if(isset($options['default']))
		{
			if($options['default'] === 'blank')
			{
				$options['default'] = '&nbsp;';			
			}
			$text .= $this->option($options['default'], varset($options['defaultValue'],''));
		}
		elseif($defaultBlank)
		{
			$diz = is_string($defaultBlank) ? $defaultBlank : '&nbsp;';
			$text .= $this->option($diz, '');
		}
		
		if(varset($options['useValues'])) // use values as keys. 
		{
			$new = array();
			foreach($option_array as $v)
			{
				$new[$v] = $v;	
			}	
			$option_array = $new;	
		}

		$text .= $this->option_multi($option_array, $selected)."\n".$this->select_close();
		return $text;
	}
	
	
	
	
	

	/**
	 * Universal Userclass selector - checkboxes, dropdown, everything. 
	 * @param $name - form element name
	 * @param $curval - current userclass value(s) as array or comma separated. 
	 * @param $type - 'checkbox', 'dropdown', 
	 * @param options - query string or array. 'options=admin,mainadmin,classes&vetted=1&exclusions=0' etc. 
	 * @return the userclass form element 
	 */
	function userclass($name, $curval=255, $type=null, $options=null)
	{
		
		switch ($type) 
		{
			case 'checkbox':
				return e107::getUserClass()->uc_checkboxes($name,$curval,$options,null,false);
			break;

			case 'dropdown':
			default:
				return e107::getUserClass()->uc_dropdown($name,$curval,$options); 	
			break;
		}

	}
	
	
	/**
	 * Renders a generic search box. If $filter has values, a filter box will be included with the options provided. 
	 * 
	 */
	function search($name, $searchVal, $submitName, $filterName='', $filterArray=false, $filterVal=false)
	{
		$tp = e107::getParser();
		
		$text = '<span class="input-append e-search">
    		'.$this->text($name, $searchVal,20,'class=search-query&placeholder='.LAN_SEARCH).'
   			 <button class="btn btn-primary" name="'.$submitName.'" type="submit">'.$tp->toGlyph('fa-search',' ').'</button>
    	</span>';
		
		
		
		if(is_array($filter))
		{
			$text .= $this->selectbox($$filterName, $filterArray, $filterVal); 
		}
		
	//	$text .= $this->admin_button($submitName,LAN_SEARCH,'search');
		
		return $text;
		
		/*
		$text .= 
		
						<select style="display: none;" data-original-title="Filter the results below" name="filter_options" id="filter-options" class="e-tip tbox select filter" title="">
							<option value="">Display All</option>
							<option value="___reset___">Clear Filter</option>
								<optgroup class="optgroup" label="Filter by&nbsp;Category">
<option value="faq_parent__1">General</option>
<option value="faq_parent__2">Misc</option>
<option value="faq_parent__4">Test 3</option>
	</optgroup>

						</select><div class="btn-group bootstrap-select e-tip tbox select filter"><button id="filter-options" class="btn dropdown-toggle clearfix" data-toggle="dropdown"><span class="filter-option pull-left">Display All</span>&nbsp;<span class="caret"></span></button><ul style="max-height: none; overflow-y: auto;" class="dropdown-menu" role="menu"><li rel="0"><a tabindex="-1" class="">Display All</a></li><li rel="1"><a tabindex="-1" class="">Clear Filter</a></li><li rel="2"><dt class="optgroup-div">Filter by&nbsp;Category</dt><a tabindex="-1" class="opt ">General</a></li><li rel="3"><a tabindex="-1" class="opt ">Misc</a></li><li rel="4"><a tabindex="-1" class="opt ">Test 3</a></li></ul></div>
						<div class="e-autocomplete"></div>
						
						
			<button type="submit" name="etrigger_filter" value="etrigger_filter" id="etrigger-filter" class="btn filter e-hide-if-js btn-primary"><span>Filter</span></button>
		
						<span class="indicator" style="display: none;">
							<img src="/e107_2.0/e107_images/generic/loading_16.gif" class="icon action S16" alt="Loading...">
						</span>	
		
		*/
	}	
	
	
	
	
	
	

	function uc_select($name, $current_value, $uc_options, $select_options = array(), $opt_options = array())
	{

		if(!empty($select_options['multiple']) && substr($name,-1) != ']')
		{
			$name .= '[]';
		}

		if(empty($current_value) && !empty($uc_options)) // make the first in the opt list the default value.
		{
			$tmp = explode(",", $uc_options);
			$current_value =  e107::getUserClass()->getClassFromKey($tmp[0]);
		}

		return $this->select_open($name, $select_options)."\n".$this->_uc->vetted_tree($name, array($this, '_uc_select_cb'), $current_value, $uc_options, $opt_options)."\n".$this->select_close();
	}

	// Callback for vetted_tree - Creates the option list for a selection box
	function _uc_select_cb($treename, $classnum, $current_value, $nest_level)
	{
		$classIndex = abs($classnum);			// Handle negative class values
		$classSign = (substr($classnum, 0, 1) == '-') ? '-' : '';
		
		if($classnum == e_UC_BLANK)
			return $this->option('&nbsp;', '');

		$tmp = explode(',', $current_value);
		if($nest_level == 0)
		{
			$prefix = '';
			$style = "font-weight:bold; font-style: italic;";
		}
		elseif($nest_level == 1)
		{
			$prefix = '&nbsp;&nbsp;';
			$style = "font-weight:bold";
		}
		else
		{
			$prefix = '&nbsp;&nbsp;'.str_repeat('--', $nest_level - 1).'&gt;';
			$style = '';
		}
		return $this->option($prefix.$this->_uc->uc_get_classname($classnum), $classSign.$classIndex, ($current_value !== '' && in_array($classnum, $tmp)), array("style"=>"{$style}"))."\n";
	}


	function optgroup_open($label, $disabled = false, $options = null)
	{
		return "<optgroup class='optgroup ".varset($options['class'])."' label='{$label}'".($disabled ? " disabled='disabled'" : '').">";
	}

    /**
     * <option> tag generation. 
     * @param $option_title 
     * @param $value
     * @param $selected
     * @param $options (eg. disabled=1)
     */
	function option($option_title, $value, $selected = false, $options = '')
	{
	    if(is_string($options)) parse_str($options, $options);
       
		if(false === $value) $value = '';
		$options = $this->format_options('option', '', $options);
		$options['selected'] = $selected; //comes as separate argument just for convenience
		
		return "<option value='{$value}'".$this->get_attributes($options).">".defset($option_title, $option_title)."</option>";
	}


    /**
    * Use selectbox() instead. 
    */
	function option_multi($option_array, $selected = false, $options = array())
	{
		if(is_string($option_array)) parse_str($option_array, $option_array);

		$text = '';
		foreach ($option_array as $value => $label)
		{
			if(is_array($label))
			{
				$text .= $this->optgroup_open($value);
				foreach($label as $val => $lab)
				{
					
					if(is_array($lab)) 
					{
						$text .= $this->optgroup_open($val,null,array('class'=>'level-2')); // Not valid HTML5 - but appears to work in modern browsers. 
						foreach($lab as $k=>$v)
						{
							$text .= $this->option($v, $k, (is_array($selected) ? in_array($k, $selected) : $selected == $k), $options)."\n";
						}	
						$text .= $this->optgroup_close($val);
					}
					else
					{
						$text .= $this->option($lab, $val, (is_array($selected) ? in_array($val, $selected) : $selected == $val), $options)."\n";
					}
				}
				$text .= $this->optgroup_close();
			}
			else
			{
				$text .= $this->option($label, $value, (is_array($selected) ? in_array($value, $selected) : $selected == $value), $options)."\n";
			}
		}

		return $text;
	}

	function optgroup_close()
	{
		return "</optgroup>";
	}

	function select_close()
	{
		return "</select>";
	}

	function hidden($name, $value, $options = array())
	{
		$options = $this->format_options('hidden', $name, $options);
		return "<input type='hidden' name='{$name}' value='{$value}'".$this->get_attributes($options, $name, $value)." />";
	}

	/**
	 * Generate hidden security field
	 * @return string
	 */
	function token()
	{
		return "<input type='hidden' name='e-token' value='".defset('e_TOKEN', '')."' />";
	}

	function submit($name, $value, $options = array())
	{
		$options = $this->format_options('submit', $name, $options);
		return "<input type='submit' name='{$name}' value='{$value}'".$this->get_attributes($options, $name, $value)." />";
	}

	function submit_image($name, $value, $image, $title='', $options = array())
	{
		$tp = e107::getParser();
		$options = $this->format_options('submit_image', $name, $options);
		switch ($image)
		{
			case 'edit':
				$icon = "e-edit-32";
				$options['class'] = $options['class'] == 'action' ? 'btn btn-default action edit' : $options['class'];
			break;

			case 'delete':
				$icon = "e-delete-32";
				$options['class'] = $options['class'] == 'action' ? 'btn btn-default action delete' : $options['class'];
				$options['other'] = 'data-confirm="'.LAN_JSCONFIRM.'"';
			break;

			case 'execute':
				$icon = "e-execute-32";
				$options['class'] = $options['class'] == 'action' ? 'btn btn-default action execute' : $options['class'];
			break;

			case 'view':
				$icon = "e-view-32";
				$options['class'] = $options['class'] == 'action' ? 'btn btn-default action view' : $options['class'];
			break;
		}
		$options['title'] = $title;//shorthand
		
		return  "<button type='submit' name='{$name}' data-placement='left' value='{$value}'".$this->get_attributes($options, $name, $value)."  >".$tp->toIcon($icon)."</button>";

	
	}

	/**
	 * Alias of admin_button, adds the etrigger_ prefix required for UI triggers
	 * @see e_form::admin_button()
	 */
	function admin_trigger($name, $value, $action = 'submit', $label = '', $options = array())
	{
		return $this->admin_button('etrigger_'.$name, $value, $action, $label, $options);
	}


	/**
	 * Generic Button Element. 
	 * @param string $name
	 * @param string $value
	 * @param string $action [optional] default is submit - use 'dropdown' for a bootstrap dropdown button. 
	 * @param string $label [optional]
	 * @param string|array $options [optional]
	 * @return string
	 */
	public function button($name, $value, $action = 'submit', $label = '', $options = array())
	{
		if(deftrue('BOOTSTRAP') && $action == 'dropdown' && is_array($value))
		{
		//	$options = $this->format_options('admin_button', $name, $options);
			$options['class'] = vartrue($options['class']);
			
			$align = vartrue($options['align'],'left');
					
			$text = '<div class="btn-group pull-'.$align.'">
			    <a class="btn dropdown-toggle '.$options['class'].'" data-toggle="dropdown" href="#">
			    '.($label ? $label : 'No Label Provided').'
			    <span class="caret"></span>
			    </a>
			    <ul class="dropdown-menu">
			    ';
			
			foreach($value as $k=>$v)
			{
				$text .= '<li>'.$v.'</li>';	
			}
			
			$text .= '
			    </ul>
			    </div>';
			
			return $text;	
		}			
				
			
		
		return $this->admin_button($name, $value, $action, $label, $options);
		
	}

	/**
	 * Render a Breadcrumb in Bootstrap format. 
	 * @param $array 
	 */
	function breadcrumb($array)
	{
		if(!is_array($array)){ return; }
		
		$opt = array();
		
		$homeIcon = e107::getParser()->toGlyph('icon-home.glyph',false);
		
		
		$opt[] = "<a href='".e_HTTP."'>".$homeIcon."</a>"; // Add Site-Pref to disable?
		
		$text = '<ul class="breadcrumb">
			<li>';
	
		foreach($array as $val)
		{
			$ret = "";
			$ret .= vartrue($val['url']) ? "<a href='".$val['url']."'>" : "";			
			$ret .= vartrue($val['text'],'');
			$ret .= vartrue($val['url']) ? "</a>" : "";
			
			if($ret != '')
			{
				$opt[] = $ret;
			}	
		}
	
		$sep = (deftrue('BOOTSTRAP') === 3) ? "" : "<span class='divider'>/</span>";
	
		$text .= implode($sep."</li><li>",$opt); 
	
		$text .= "</li></ul>";
		
	//	return print_a($opt,true);
	
		return $text;	

	}
	
	
	
	
	
	
	
	/**
	 * Admin Button - for front-end, use button(); 
	 * @param string $name
	 * @param string $value
	 * @param string $action [optional] default is submit
	 * @param string $label [optional]
	 * @param string|array $options [optional]
	 * @return string
	 */
	function admin_button($name, $value, $action = 'submit', $label = '', $options = array())
	{
		$btype = 'submit';
		if(strpos($action, 'action') === 0) $btype = 'button';
		$options = $this->format_options('admin_button', $name, $options);
		
		$options['class'] = vartrue($options['class']);
		$options['class'] .= ' btn '.$action.' ';//shorthand
		if(empty($label)) $label = $value;
		
		switch ($action)
		{
			case 'update':
			case 'create':
			case 'import':
			case 'submit':
			case 'success':
				$options['class'] .= 'btn-success';
			break;
			
			case 'checkall':
				$options['class'] .= 'btn-mini';
			break;
	
			case 'cancel':
				// use this for neutral colors. 
			break;

			case 'delete':
			case 'danger':
				$options['class'] .= 'btn-danger';
				$options['other'] = 'data-confirm="'.LAN_JSCONFIRM.'"';
			break;

			case 'execute':
				$options['class'] .= 'btn-success';
			break;
			
			case 'other':
			case 'login':	
			case 'primary':
				$options['class'] .= 'btn-primary';
			break;	
			
			case 'warning':
            case 'confirm':
				$options['class'] .= 'btn-warning';
			break;
			
			case 'batch':
			case 'batch e-hide-if-js': // FIXME hide-js shouldn't be here. 
				$options['class'] .= 'btn-primary';
			break;
			
			case 'filter':
			case 'filter e-hide-if-js': // FIXME hide-js shouldn't be here. 
				$options['class'] .= 'btn-primary';
			break;
			
			default:
				$options['class'] .= 'btn-default';
			break;
		}	
		
		return "
			<button  type='{$btype}' name='{$name}' value='{$value}'".$this->get_attributes($options, $name)."><span>{$label}</span></button>
		";
	}

	function getNext()
	{
		if(!$this->_tabindex_enabled) return 0;
		$this->_tabindex_counter += 1;
		return $this->_tabindex_counter;
	}

	function getCurrent()
	{
		if(!$this->_tabindex_enabled) return 0;
		return $this->_tabindex_counter;
	}

	function resetTabindex($reset = 0)
	{
		$this->_tabindex_counter = $reset;
	}

	function get_attributes($options, $name = '', $value = '')
	{
		$ret = '';
		//
		foreach ($options as $option => $optval)
		{
			$optval = trim($optval);
			switch ($option) 
			{

				case 'id':
					$ret .= $this->_format_id($optval, $name, $value);
					break;

				case 'class':
					if(!empty($optval)) $ret .= " class='{$optval}'";
					break;

				case 'size':
					if($optval) $ret .= " size='{$optval}'";
					break;

				case 'title':
					if($optval) $ret .= " title='{$optval}'";
					break;

				case 'label':
					if($optval) $ret .= " label='{$optval}'";
					break;

				case 'tabindex':
					if($optval) $ret .= " tabindex='{$optval}'";
					elseif(false === $optval || !$this->_tabindex_enabled) break;
					else
					{
						$this->_tabindex_counter += 1;
						$ret .= " tabindex='".$this->_tabindex_counter."'";
					}
					break;

				case 'readonly':
					if($optval) $ret .= " readonly='readonly'";
					break;

				case 'multiple':
					if($optval) $ret .= " multiple='multiple'";
					break;

				case 'selected':
					if($optval) $ret .= " selected='selected'";
					break;

				case 'maxlength':
					if($optval) $ret .= " maxlength='{$optval}'";
					break;

				case 'checked':
					if($optval) $ret .= " checked='checked'";
					break;

				case 'disabled':
					if($optval) $ret .= " disabled='disabled'";
					break;
					
				case 'required':
					if($optval) $ret .= " required='required'";
					break;
					
				case 'autofocus':
					if($optval) $ret .= " autofocus='autofocus'";
					break;
					
				case 'placeholder':
					if($optval) $ret .= " placeholder='{$optval}'";
					break;
					
					
				case 'autocomplete':
					if($optval) $ret .= " autocomplete='{$optval}'";
					break;
					
				case 'pattern':
					if($optval) $ret .= " pattern='{$optval}'";
					break;

				case 'other':
					if($optval) $ret .= " $optval";
					break;
			}

			if(substr($option,0,5) =='data-')
			{
				$ret .= " ".$option."='{$optval}'";	
			}
				
		}

		return $ret;
	}

	/**
	 * Auto-build field attribute id
	 *
	 * @param string $id_value value for attribute id passed with the option array
	 * @param string $name the name attribute passed to that field
	 * @param unknown_type $value the value attribute passed to that field
	 * @return string formatted id attribute
	 */
	function _format_id($id_value, $name, $value = '', $return_attribute = 'id')
	{
		if($id_value === false) return '';

		//format data first
		$name = trim($this->name2id($name), '-');
		$value = trim(preg_replace('#[^a-zA-Z0-9\-]#','-', $value), '-');
		//$value = trim(preg_replace('#[^a-z0-9\-]#/i','-', $value), '-');		// This should work - but didn't for me!
		$value = trim(str_replace("/","-",$value), '-');					// Why?
		if(!$id_value && is_numeric($value)) $id_value = $value;

		// clean - do it better, this could lead to dups
		$id_value = trim($id_value, '-');

		if(empty($id_value) ) return " {$return_attribute}='{$name}".($value ? "-{$value}" : '')."'";// also useful when name is e.g. name='my_name[some_id]'
		elseif(is_numeric($id_value) && $name) return " {$return_attribute}='{$name}-{$id_value}'";// also useful when name is e.g. name='my_name[]'
		else return " {$return_attribute}='{$id_value}'";
	}

	function name2id($name)
	{
		$name = strtolower($name);
		return rtrim(str_replace(array('[]', '[', ']', '_', '/', ' ','.', '(', ')'), array('-', '-', '', '-', '-', '-', '-','',''), $name), '-');
	}

	/**
	 * Format options based on the field type,
	 * merge with default
	 *
	 * @param string $type
	 * @param string $name form name attribute value
	 * @param array|string $user_options
	 * @return array merged options
	 */
	function format_options($type, $name, $user_options=null)
	{
		if(is_string($user_options))
		{
			parse_str($user_options, $user_options); 
		}

		$def_options = $this->_default_options($type);
	

		if(is_array($user_options))
		{
			$user_options['name'] = $name; //required for some of the automated tasks
			
			foreach (array_keys($user_options) as $key)
			{
				if(!isset($def_options[$key]) && substr($key,0,5)!='data-') unset($user_options[$key]); // data-xxxx exempt //remove it?
			}	
		}
		else 
		{
			$user_options = array('name' => $name); //required for some of the automated tasks	
		}
		
		return array_merge($def_options, $user_options);
	}

	/**
	 * Get default options array based on the field type
	 *
	 * @param string $type
	 * @return array default options
	 */
	function _default_options($type)
	{
		if(isset($this->_cached_attributes[$type])) return $this->_cached_attributes[$type];

		$def_options = array(
			'id' 			=> '',
			'class' 		=> '',
			'title' 		=> '',
			'size' 			=> '',
			'readonly' 		=> false,
			'selected' 		=> false,
			'checked' 		=> false,
			'disabled' 		=> false,
			'required' 		=> false,	
			'autofocus'		=> false,	
			'tabindex' 		=> 0,
			'label' 		=> '',
			'placeholder' 	=> '',
			'pattern'		=> '',
			'other' 		=> '',
			'autocomplete' 	=> '',
			'maxlength'		=> ''
			//	'multiple' => false, - see case 'select'
		);

		$form_control = (deftrue('BOOTSTRAP') === 3) ? ' form-control' : '';

		switch ($type) {
			case 'hidden':
				$def_options = array('id' => false, 'disabled' => false, 'other' => '');
				break;

			case 'text':
				$def_options['class'] = 'tbox input-text'.$form_control;
				unset($def_options['selected'], $def_options['checked']);
				break;

			case 'file':
				$def_options['class'] = 'tbox file';
				unset($def_options['selected'], $def_options['checked']);
				break;

			case 'textarea':
				$def_options['class'] = 'tbox textarea'.$form_control;
				unset($def_options['selected'], $def_options['checked'], $def_options['size']);
				break;

			case 'select':
				$def_options['class'] = 'tbox select'.$form_control;
				$def_options['multiple'] = false;
				unset($def_options['checked']);
				break;

			case 'option':
				$def_options = array('class' => '', 'selected' => false, 'other' => '', 'disabled' => false, 'label' => '');
				break;

			case 'radio':
				//$def_options['class'] = ' ';
				unset($def_options['size'], $def_options['selected']);
				break;

			case 'checkbox':
				unset($def_options['size'],  $def_options['selected']);
				break;

			case 'submit':
				$def_options['class'] = 'button btn btn-primary';
				unset($def_options['checked'], $def_options['selected'], $def_options['readonly']);
				break;

			case 'submit_image':
				$def_options['class'] = 'action';
				unset($def_options['checked'], $def_options['selected'], $def_options['readonly']);
				break;

			case 'admin_button':
				unset($def_options['checked'],  $def_options['selected'], $def_options['readonly']);
				break;

		}

		$this->_cached_attributes[$type] = $def_options;
		return $def_options;
	}

	function columnSelector($columnsArray, $columnsDefault = '', $id = 'column_options')
	{
		$columnsArray = array_filter($columnsArray);
		
	
		$text = '<div class="col-selection dropdown e-tip pull-right" data-placement="left">
    <a class="dropdown-toggle" title="Select columns to display" data-toggle="dropdown" href="#"><b class="caret"></b></a>
    <ul class="dropdown-menu  col-selection e-noclick" role="menu" aria-labelledby="dLabel">
   
    <li class="navbar-header nav-header">Display Columns</li>
    <li>
     <ul class="nav scroll-menu" >';
		
        unset($columnsArray['options'], $columnsArray['checkboxes']);

		foreach($columnsArray as $key => $fld)
		{
			if (empty($fld['forced']) && empty($fld['nolist']) && vartrue($fld['type'])!='hidden' && vartrue($fld['type'])!='upload')
			{
				$checked = (in_array($key,$columnsDefault)) ?  TRUE : FALSE;
				$ttl = isset($fld['title']) ? defset($fld['title'], $fld['title']) : $key;
				// $text .= "
					// <div class='field-spacer'>
						// ".$this->checkbox_label($ttl, 'e-columns[]', $key, $checked)."
					// </div>
				// ";
// 				
				$text .= "
					<li role='menuitem'><a href='#'>
						".$this->checkbox('e-columns[]', $key, $checked,'label='.$ttl)."
					</a>
					</li>
				";
			}
		}

		// has issues with the checkboxes.
        $text .= "
				</ul>
				</li>
				 <li class='navbar-header nav-header'>
				<div id='{$id}-button' class='right'>
					".$this->admin_button('etrigger_ecolumns', LAN_SAVE, 'btn btn-primary btn-small')."
				</div>
				 </li>
				</ul>
			</div>";
			
	//	$text .= "</div></div>";

		$text .= "";
	
	
	/*
	$text = '<div class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#"><b class="caret"></b></a>
    <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
    <li>hi</li>
    </ul>
    </div>';
	*/	
		return $text;
	}
	
	
	
	
	
	
	
	
	
	

	function colGroup($fieldarray, $columnPref = '')
	{
        $text = "";
        $count = 0;
		foreach($fieldarray as $key=>$val)
		{
			if ((in_array($key, $columnPref) || $key=='options' || vartrue($val['forced'])) && !vartrue($val['nolist']))
			{
				$class = vartrue($val['class']) ? 'class="'.$val['class'].'"' : '';
				$width = vartrue($val['width']) ? ' style="width:'.$val['width'].'"' : '';
				$text .= '<col '.$class.$width.' />
				';
				$count++;
			}
		}

		return '
			<colgroup>
				'.$text.'
			</colgroup>
		';
	}

	function thead($fieldarray, $columnPref = array(), $querypattern = '', $requeststr = '')
	{
        $text = "";

        $querypattern = filter_var($querypattern, FILTER_SANITIZE_STRING);
        if(!$requeststr) $requeststr = rawurldecode(e_QUERY);
        $requeststr = filter_var($requeststr, FILTER_SANITIZE_STRING);

		// Recommended pattern: mode=list&field=[FIELD]&asc=[ASC]&from=[FROM]
		if(strpos($querypattern,'&')!==FALSE)
		{
			// we can assume it's always $_GET since that's what it will generate
			// more flexible (e.g. pass default values for order/field when they can't be found in e_QUERY) & secure
			$tmp = str_replace('&amp;', '&', $requeststr ? $requeststr : e_QUERY);
			parse_str($tmp, $tmp);

			$etmp = array();
			parse_str(str_replace('&amp;', '&', $querypattern), $etmp);
		}
		else // Legacy Queries. eg. main.[FIELD].[ASC].[FROM]
		{
			$tmp = explode(".", ($requeststr ? $requeststr : e_QUERY));
			$etmp = explode(".", $querypattern);
		}

		foreach($etmp as $key => $val)    // I'm sure there's a more efficient way to do this, but too tired to see it right now!.
		{
        	if($val == "[FIELD]")
			{
            	$field = varset($tmp[$key]);
			}

			if($val == "[ASC]")
			{
            	$ascdesc = varset($tmp[$key]);
			}
			if($val == "[FROM]")
			{
            	$fromval = varset($tmp[$key]);
			}
		}

		if(!varset($fromval)){ $fromval = 0; }

        $ascdesc = (varset($ascdesc) == 'desc') ? 'asc' : 'desc';
		foreach($fieldarray as $key=>$val)
		{
     		if ((in_array($key, $columnPref) || $key == 'options' || (vartrue($val['forced']))) && !vartrue($val['nolist']))
			{
				$cl = (vartrue($val['thclass'])) ? " class='".$val['thclass']."'" : "";
				$text .= "
					<th id='e-column-".str_replace('_', '-', $key)."'{$cl}>
				";

                if($querypattern!="" && !vartrue($val['nosort']) && $key != "options" && $key != "checkboxes")
				{
					$from = ($key == $field) ? $fromval : 0;
					$srch = array("[FIELD]","[ASC]","[FROM]");
					$repl = array($key,$ascdesc,$from);
                	$val['url'] = e_SELF."?".str_replace($srch,$repl,$querypattern);
				}

				$text .= (vartrue($val['url'])) ? "<a href='".str_replace(array('&amp;', '&'), array('&', '&amp;'),$val['url'])."'>" : "";  // Really this column-sorting link should be auto-generated, or be autocreated via unobtrusive js.
	            $text .= defset($val['title'], $val['title']);
				$text .= ($val['url']) ? "</a>" : "";
	            $text .= ($key == "options" && !vartrue($val['noselector'])) ? $this->columnSelector($fieldarray, $columnPref) : "";
				$text .= ($key == "checkboxes") ? $this->checkbox_toggle('e-column-toggle', vartrue($val['toggle'], 'multiselect')) : "";

	
	 			$text .= "
					</th>
				";
			}
		}

		return "
		<thead>
	  		<tr >".$text."</tr>
		</thead>
		";

	}


	/**
	 * Render Table cells from hooks.
	 * @param array $data 
	 * @return string
	 */
	function renderHooks($data)
	{
		$hooks = e107::getEvent()->triggerHook($data);
				
		$text = "";	
		
		if(!empty($hooks))
		{
			foreach($hooks as $plugin => $hk)
			{
				$text .= "\n\n<!-- Hook : {$plugin} -->\n";
				
				if(!empty($hk))
				{
					foreach($hk as $hook)
					{
						$text .= "\t\t\t<tr>\n";
						$text .= "\t\t\t<td>".$hook['caption']."</td>\n";
						$text .= "\t\t\t<td>".$hook['html']."";
						$text .= (varset($hook['help'])) ? "\n<span class='field-help'>".$hook['help']."</span>" : "";		
						$text .= "</td>\n\t\t\t</tr>\n";
					}

					
				}
			}
		}
		
		return $text;			
	}



			
	/**
	 * Render Related Items for the current page/news-item etc. 
	 * @param string $type : comma separated list. ie. plugin folder names. 
	 * @param string $tags : comma separated list of keywords to return related items of.
	 * @param array $curVal. eg. array('page'=> current-page-id-value); 
	 */
	function renderRelated($parm,$tags, $curVal) //XXX TODO Cache!
	{
		
		if(empty($tags))
		{
			return;	
		}
		
		if(!varset($parm['limit']))
		{
			$parm = array('limit' => 5);
		}
		
		if(!varset($parm['types']))
		{
			$parm['types'] = 'news';	
		}
		
			
		$tp = e107::getParser();
			
		$types = explode(',',$parm['types']);
		$list = array();
		
		
		foreach($types as $plug)
		{
		
			if(!$obj = e107::getAddon($plug,'e_related'))
			{
				continue;
			}
			
			$parm['current'] = intval(varset($curVal[$plug])); 
		
			$tmp = $obj->compile($tags,$parm);	
		
			if(count($tmp))
			{
				foreach($tmp as $val)
				{
					$list[] = "<li><a href='".$tp->replaceConstants($val['url'],'full')."'>".$val['title']."</a></li>";	
					
				}
			}		
		}
		
		if(count($list))
		{
			return "<div class='e-related clearfix'><hr><h4>Related</h4><ul class='e-related'>".implode("\n",$list)."</ul></div>"; //XXX Tablerender?
		}
		
	}		



	/**
	 * Render Table cells from field listing.
	 * @param array $fieldarray - eg. $this->fields
	 * @param array $currentlist - eg $this->fieldpref
	 * @param array $fieldvalues - eg. $row
	 * @param string $pid - eg. table_id
	 * @return string
	 */
	function renderTableRow($fieldarray, $currentlist, $fieldvalues, $pid)
	{
		$cnt = 0;
		$ret = '';

		/*$fieldarray 	= $obj->fields;
		$currentlist 	= $obj->fieldpref;
		$pid 			= $obj->pid;*/

		$trclass = vartrue($fieldvalues['__trclass']) ?  ' class="'.$trclass.'"' : '';
		unset($fieldvalues['__trclass']);

		foreach ($fieldarray as $field => $data)
		{


			// shouldn't happen... test with Admin->Users with user_xup visible and NULL values in user_xup table column before re-enabling this code.
			/*
			if(!isset($fieldvalues[$field]) && vartrue($data['alias']))
			{
				$fieldvalues[$data['alias']] = $fieldvalues[$data['field']];
				$field = $data['alias'];
			}
			*/
            
			//Not found
			if((!varset($data['forced']) && !in_array($field, $currentlist)) || varset($data['nolist']))
			{
				continue;
			}
			elseif(vartrue($data['type']) != 'method' && !$data['forced'] && !isset($fieldvalues[$field]) && $fieldvalues[$field] !== NULL)
			{
				$ret .= "
					<td>
						Not Found! ($field)
					</td>
				";

				continue;
			}

			$tdclass = vartrue($data['class']);
            
            if($field == 'checkboxes') $tdclass = $tdclass ? $tdclass.' autocheck e-pointer' : 'autocheck e-pointer';
            
			if($field == 'options') $tdclass = $tdclass ? $tdclass.' options' : 'options';
            
            
            
			// there is no other way for now - prepare user data
			if('user' == vartrue($data['type']) /* && isset($data['readParms']['idField'])*/)
			{
				if(varset($data['readParms']) && is_string($data['readParms'])) parse_str($data['readParms'], $data['readParms']);
				if(isset($data['readParms']['idField']))
				{
					$data['readParms']['__idval'] = $fieldvalues[$data['readParms']['idField']];
				}
				elseif(isset($fieldvalues['user_id'])) // Default
				{
					$data['readParms']['__idval'] = $fieldvalues['user_id'];
				}

				if(isset($data['readParms']['nameField']))
				{
					$data['readParms']['__nameval'] = $fieldvalues[$data['readParms']['nameField']];
				}
				elseif(isset($fieldvalues['user_name'])) // Default
				{
					$data['readParms']['__nameval'] = $fieldvalues['user_name'];
				}


			}
			$value = $this->renderValue($field, varset($fieldvalues[$field]), $data, varset($fieldvalues[$pid]));



			if($tdclass)
			{
				$tdclass = ' class="'.$tdclass.'"';
			}
			$ret .= '
				<td'.$tdclass.'>
					'.$value.'
				</td>
			';

			$cnt++;
		}

		if($cnt)
		{
			return '
				<tr'.$trclass.' id="row-'.$fieldvalues[$pid].'">
					'.$ret.'
				</tr>
			';
		}

		return '';
	}

	/**
	 * Create an Inline Edit link. 
	 * @param $dbField : field being edited //TODO allow for an array of all data here. 
	 * @param $pid : primary ID of the row being edited. 
	 * @param $fieldName - Description of the field name (caption)
	 * @param $curVal : existing value of in the field
	 * @param $linkText : existing value displayed
	 * @param $type text|textarea|select|date|checklist
	 * @param $array : array data used in dropdowns etc. 
	 */
	private function renderInline($dbField, $pid, $fieldName, $curVal, $linkText, $type='text', $array=null)
	{
		$jsonArray = array();
				
		if(!empty($array))
		{
			foreach($array as $k=>$v)
			{
				$jsonArray[$k] = str_replace("'", "`", $v);	
			}
		}
		$source = str_replace('"',"'",json_encode($jsonArray, JSON_FORCE_OBJECT)); // SecretR - force object, fix number of bugs
		
		
		$mode = preg_replace('/[^\w]/', '', vartrue($_GET['mode'], ''));
		
		$text = "<a class='e-tip e-editable editable-click' data-name='".$dbField."' ";
		$text .= (is_array($array)) ? "data-source=\"".$source."\"  " : "";
		$text .= " title=\"".LAN_EDIT." ".$fieldName."\" data-type='".$type."' data-inputclass='x-editable-".$this->name2id($dbField)."' data-value=\"{$curVal}\" data-pk='".$pid."' data-url='".e_SELF."?mode={$mode}&amp;action=inline&amp;id={$pid}&amp;ajax_used=1' href='#'>".$linkText."</a>";	
		
		return $text;	
	}



	/**
	 * Render Field Value
	 * @param string $field field name
	 * @param mixed $value field value
	 * @param array $attributes field attributes including render parameters, element options - see e_admin_ui::$fields for required format
	 * @return string
	 */
	function renderValue($field, $value, $attributes, $id = 0)
	{


		$parms = array();
		if(isset($attributes['readParms']))
		{
			if(!is_array($attributes['readParms'])) parse_str($attributes['readParms'], $attributes['readParms']);
			$parms = $attributes['readParms'];
		}
	
		if(vartrue($attributes['inline'])) $parms['editable'] = true; // attribute alias
		if(vartrue($attributes['sort'])) $parms['sort'] = true; // attribute alias
		
		if(!empty($parms['type'])) // Allow the use of a different type in readMode. eg. type=method.
		{
			$attributes['type'] = $parms['type'];	
		}

		$this->renderValueTrigger($field, $value, $parms, $id);

		$tp = e107::getParser();
		switch($field) // special fields
		{
			case 'options':
				
				if(varset($attributes['type']) == "method") // Allow override with 'options' function.
				{
					$attributes['mode'] = "read";
					if(isset($attributes['method']) && $attributes['method'] && method_exists($this, $attributes['method']))
					{
						$method = $attributes['method'];
						return $this->$method($parms, $value, $id, $attributes);
						
					}
					elseif(method_exists($this, 'options'))
					{
						//return  $this->options($field, $value, $attributes, $id); 
						// consistent method arguments, fixed in admin cron administration
						 return $this->options($parms, $value, $id, $attributes); // OLD breaks admin->cron 'options' column
					}
				}

				if(!$value)
				{
					parse_str(str_replace('&amp;', '&', e_QUERY), $query); //FIXME - FIX THIS
					// keep other vars in tact
					$query['action'] = 'edit';
					$query['id'] = $id;

					//$edit_query = array('mode' => varset($query['mode']), 'action' => varset($query['action']), 'id' => $id);
					$query = http_build_query($query);
					
					$value = "<div class='btn-group'>";
					
					if(vartrue($parms['sort']))//FIXME use a global variable such as $fieldpref
					{
						$mode = preg_replace('/[^\w]/', '', vartrue($_GET['mode'], ''));
						$from = intval(vartrue($_GET['from'],0));
						$value .= "<a class='e-sort sort-trigger btn btn-default' style='cursor:move' data-target='".e_SELF."?mode={$mode}&action=sort&ajax_used=1&from={$from}' title='Re-order'>".ADMIN_SORT_ICON."</a> ";	
					}	
					
					$cls = false;
					if(varset($parms['editClass']))
					{
						$cls = (deftrue($parms['editClass'])) ? constant($parms['editClass']) : $parms['editClass'];

					}	
					if((false === $cls || check_class($cls)) && varset($parms['edit'],1) == 1)
					{
						/*
						$value .= "<a href='".e_SELF."?{$query}' class='e-tip btn btn-large' title='".LAN_EDIT."' data-placement='left'>
												<img class='icon action edit list' src='".ADMIN_EDIT_ICON_PATH."' alt='".LAN_EDIT."' /></a>";
												*/
						
						$value .= "<a href='".e_SELF."?{$query}' class='btn btn-default' title='".LAN_EDIT."' data-toggle='tooltip' data-placement='left'>
						".ADMIN_EDIT_ICON."</a>";
					}

					$delcls = vartrue($attributes['noConfirm']) ? ' no-confirm' : '';
					if(varset($parms['deleteClass']) && varset($parms['delete'],1) == 1)
					{
						$cls = (deftrue($parms['deleteClass'])) ? constant($parms['deleteClass']) : $parms['deleteClass'];
						if(check_class($cls))
						{
							$value .= $this->submit_image('etrigger_delete['.$id.']', $id, 'delete', LAN_DELETE.' [ ID: '.$id.' ]', array('class' => 'action delete btn btn-default'.$delcls));
						}
					}
					else
					{
						$value .= $this->submit_image('etrigger_delete['.$id.']', $id, 'delete', LAN_DELETE.' [ ID: '.$id.' ]', array('class' => 'action delete btn btn-default'.$delcls));
					}
				}
				//$attributes['type'] = 'text';
				$value .= "</div>";
				return $value;
			break;

			case 'checkboxes':
				$value = $this->checkbox(vartrue($attributes['toggle'], 'multiselect').'['.$id.']', $id);
				//$attributes['type'] = 'text';
				return $value;
			break;
		}

		switch($attributes['type'])
		{
			case 'number':
				if(!$value) $value = '0';
				if($parms)
				{
					if(!isset($parms['sep'])) $value = number_format($value, $parms['decimals']);
					else $value = number_format($value, $parms['decimals'], vartrue($parms['point'], '.'), vartrue($parms['sep'], ' '));
				}
				
				
				if(!vartrue($attributes['noedit']) && vartrue($parms['editable']) && !vartrue($parms['link'])) // avoid bad markup, better solution coming up
				{
					$mode = preg_replace('/[^\w]/', '', vartrue($_GET['mode'], ''));
					$value = "<a class='e-tip e-editable editable-click' data-name='".$field."' title=\"".LAN_EDIT." ".$attributes['title']."\" data-type='text' data-pk='".$id."' data-url='".e_SELF."?mode={$mode}&action=inline&id={$id}&ajax_used=1' href='#'>".$value."</a>";
				}
				
				$value = vartrue($parms['pre']).$value.vartrue($parms['post']);
				// else same
			break;

			case 'ip':
				//$e107 = e107::getInstance();
				$value = e107::getIPHandler()->ipDecode($value);
				// else same
			break;

			case 'templates':
			case 'layouts':
				$pre = vartrue($parms['pre']);
				$post = vartrue($parms['post']);
				unset($parms['pre'], $parms['post']);
				if($parms)
				{
					$attributes['writeParms'] = $parms;
				}
				elseif(isset($attributes['writeParms']))
				{
					if(is_string($attributes['writeParms'])) parse_str($attributes['writeParms'], $attributes['writeParms']);
				}
				$attributes['writeParms']['raw'] = true;
				$tmp = $this->renderElement($field, '', $attributes);
				
				// Inline Editing.  //@SecretR - please FIXME! 
				if(!vartrue($attributes['noedit']) && vartrue($parms['editable']) && !vartrue($parms['link'])) // avoid bad markup, better solution coming up
				{
					$mode = preg_replace('/[^\w]/', '', vartrue($_GET['mode'], ''));
					$source = str_replace('"',"'",json_encode($wparms));
					$value = "<a class='e-tip e-editable editable-click' data-name='".$field."' data-source=\"".$source."\" title=\"".LAN_EDIT." ".$attributes['title']."\" data-type='select' data-pk='".$id."' data-url='".e_SELF."?mode=&amp;action=inline&amp;id={$id}&amp;ajax_used=1' href='#'>".$value."</a>";
				}
				
				
				
							
			//	$value = $pre.vartrue($tmp[$value]).$post; // FIXME "Fatal error: Only variables can be passed by reference" featurebox list page. 
			break;

			case 'checkboxes':
			case 'comma':
			case 'dropdown':
				// XXX - should we use readParams at all here? see writeParms check below

				if($parms && is_array($parms)) // FIXME - add support for multi-level arrays (option groups)
				{
					//FIXME return no value at all when 'editable=1' is a readParm. See FAQs templates. 
				//	$value = vartrue($parms['pre']).vartrue($parms[$value]).vartrue($parms['post']);
				//	break; 
				}
				
				// NEW - multiple (array values) support
				// FIXME - add support for multi-level arrays (option groups)
				if(!is_array($attributes['writeParms'])) parse_str($attributes['writeParms'], $attributes['writeParms']);
				$wparms = $attributes['writeParms'];
				
				if(!is_array(varset($wparms['__options']))) parse_str($wparms['__options'], $wparms['__options']);

				if(!empty($wparms['optArray']))
				{
					$fopts = $wparms;
					$wparms = $fopts['optArray'];
					unset($fopts['optArray']);
					$wparms['__options'] = $fopts;
				}


				$opts = $wparms['__options'];
				unset($wparms['__options']);
				$_value = $value;
				
				if($attributes['type'] == 'checkboxes' || $attributes['type'] == 'comma')
				{
					$opts['multiple'] = true;	
				}
			
				if(vartrue($opts['multiple']))
				{
					$ret = array();
					$value = is_array($value) ? $value : explode(',', $value);
					foreach ($value as $v)
					{
						if(isset($wparms[$v])) $ret[] = $wparms[$v];
					}
					$value = implode(', ', $ret);
				}
				else
				{
					$ret = '';
					if(isset($wparms[$value])) $ret = $wparms[$value];
					$value = $ret;
				}
			
				$value = ($value ? vartrue($parms['pre']).defset($value, $value).vartrue($parms['post']) : '');
				
				// Inline Editing.  
				// Inline Editing with 'comma' @SecretR - please FIXME - empty values added. @see news 'render type' or 'media-manager' category for test examples. 
				if(!vartrue($attributes['noedit']) && vartrue($parms['editable']) && !vartrue($parms['link'])) // avoid bad markup, better solution coming up
				{				
					$xtype = ($attributes['type'] == 'dropdown') ? 'select' : 'checklist';
					
				//	$value = "<a class='e-tip e-editable editable-click' data-name='".$field."' data-value='{$_value}' data-source=\"".$source."\" title=\"".LAN_EDIT." ".$attributes['title']."\" data-type='".$xtype."' data-pk='".$id."' data-url='".e_SELF."?mode=&amp;action=inline&amp;id={$id}&amp;ajax_used=1' href='#'>".$value."</a>";
					
			
					$value = $this->renderInline($field, $id, $attributes['title'], $_value, $value, $xtype, $wparms);
				}
								
				// return ;
			break;

			case 'radio':
				if($parms && is_array($parms)) // FIXME - add support for multi-level arrays (option groups)
				{
					$value = vartrue($parms['pre']).vartrue($parms[$value]).vartrue($parms['post']);
					break;
				}

				if(!is_array($attributes['writeParms'])) parse_str($attributes['writeParms'], $attributes['writeParms']);
				$value = vartrue($attributes['writeParms']['__options']['pre']).vartrue($attributes['writeParms'][$value]).vartrue($attributes['writeParms']['__options']['post']);
			break;

			case 'tags':
			case 'text':

				if(!empty($parms['constant']))
				{
					$value = defset($value,$value);
				}

				if(vartrue($parms['truncate']))
				{
					$value = $tp->text_truncate($value, $parms['truncate'], '...');
				}
				elseif(vartrue($parms['htmltruncate']))
				{
					$value = $tp->html_truncate($value, $parms['htmltruncate'], '...');
				}
				if(vartrue($parms['wrap']))
				{
					$value = $tp->htmlwrap($value, (int)$parms['wrap'], varset($parms['wrapChar'], ' '));
				}
				if(vartrue($parms['link']) && $id/* && is_numeric($id)*/) 
				{
					$link       = str_replace('[id]',$id,$parms['link']);
					$link       = $tp->replaceConstants($link); // SEF URL is not important since we're in admin.
					
					$dialog     = vartrue($parms['target']) =='dialog' ? " e-dialog" : ""; // iframe
                    $ext        = vartrue($parms['target']) =='blank' ? " rel='external' " : ""; // new window
                    $modal      = vartrue($parms['target']) =='modal' ? " data-toggle='modal' data-cache='false' data-target='#uiModal' " : "";
            
                    if($parms['link'] == 'sef' && $this->getController()->getListModel()) 
                    {
                    	$model = $this->getController()->getListModel();
						// copy url config
						if(!$model->getUrl()) $model->setUrl($this->getController()->getUrl());
						// assemble the url
                    	$link = $model->url();
                    }
                    elseif(vartrue($data[$parms['link']])) // support for a field-name as the link. eg. link_url. 
                    {
                        $link = $tp->replaceConstants(vartrue($data[$parms['link']]));        
                    }
                    
					// in case something goes wrong...
                    if($link) $value = "<a class='e-tip{$dialog}' {$ext} href='".$link."' {$modal} title='Quick View'>".$value."</a>";
				}
				
					
				if(!vartrue($attributes['noedit']) && vartrue($parms['editable']) && !vartrue($parms['link'])) // avoid bad markup, better solution coming up
				{
					$mode = preg_replace('/[^\w]/', '', vartrue($_GET['mode'], ''));
					$value = "<a class='e-tip e-editable editable-click' data-name='".$field."' title=\"".LAN_EDIT." ".$attributes['title']."\" data-type='text' data-pk='".$id."' data-url='".e_SELF."?mode={$mode}&amp;action=inline&amp;id={$id}&amp;ajax_used=1' href='#'>".$value."</a>";
				}

				$value = vartrue($parms['pre']).$value.vartrue($parms['post']);
			break;
            
            

			case 'bbarea':
			case 'textarea':
				
				
				if($attributes['type'] == 'textarea' && !vartrue($attributes['noedit']) && vartrue($parms['editable']) && !vartrue($parms['link'])) // avoid bad markup, better solution coming up
				{
					return $this->renderInline($field,$id,$attributes['title'],$value,substr($value,0,50)."...",'textarea'); //FIXME.
				}
				
				
				$expand = '...';
				$toexpand = false;
				if($attributes['type'] == 'bbarea' && !isset($parms['bb'])) $parms['bb'] = true; //force bb parsing for bbareas
				$elid = trim(str_replace('_', '-', $field)).'-'.$id;
				if(!vartrue($parms['noparse'])) $value = $tp->toHTML($value, (vartrue($parms['bb']) ? true : false), vartrue($parms['parse']));
				if(vartrue($parms['expand']) || vartrue($parms['truncate']) || vartrue($parms['htmltruncate']))
				{
					$ttl = vartrue($parms['expand']);
					if($ttl == 1)
					{
						$ttl = $expand."<button class='btn btn-default btn-mini pull-right'>More..</button>";
						$ttl1 = "<button class='btn btn-default btn-mini pull-right'>..Less</button>";
					}
					else
					{
						$ttl1 = null;
					}
					
					$expands = '<a href="#'.$elid.'-expand" class="e-show-if-js e-expandit">'.defset($ttl, $ttl)."</a>";
					$contracts = '<a href="#'.$elid.'-expand" class="e-show-if-js e-expandit">'.defset($ttl1, $ttl1)."</a>";
					
				}

				$oldval = $value;
				if(vartrue($parms['truncate']))
				{
					$value = $oldval = strip_tags($value);
					$value = $tp->text_truncate($value, $parms['truncate'], '');
					$toexpand = $value != $oldval;
				}
				elseif(vartrue($parms['htmltruncate']))
				{
					$value = $tp->html_truncate($value, $parms['htmltruncate'], '');
					$toexpand = $value != $oldval;
				}
				if($toexpand)
				{
					// force hide! TODO - core style .expand-c (expand container)
					// TODO: Hide 'More..' button when text fully displayed.
					$value .= '<span class="expand-c" style="display: none" id="'.$elid.'-expand"><span>'.str_replace($value,'',$oldval).$contracts.'</span></span>';
					$value .= $expands; 	// 'More..' button. Keep it at the bottom so it does't cut the sentence. 
				}
				
				
				
			break;

			case 'icon':
				
				$value = $tp->toIcon($value,array('size'=>'2x'));
				
			break;
			
			case 'file':
				if(vartrue($parms['base']))
				{
					$url = $parms['base'].$value;
				}
				else $url = e107::getParser()->replaceConstants($value, 'full');
				$name = basename($value);
				$value = '<a href="'.$url.'" title="Direct link to '.$name.'" rel="external">'.$name.'</a>';
			break;

			case 'image': //TODO - thumb, js tooltip...
				if($value)
				{
					
					if(strpos($value,",")!==false)
					{
						$tmp = explode(",",$value);
						$value = $tmp[0];
						unset($tmp);	
					}		
						
					
					$vparm = array('thumb'=>'tag','w'=> vartrue($parms['thumb_aw'],'80'));
					
					if($video = e107::getParser()->toVideo($value,$vparm))
					{
						return $video;
					}

					$fileOnly = basename($value);
					// Not an image but a file.  (media manager)  
					if(!preg_match("/\.(png|jpg|jpeg|gif|PNG|JPG|JPEG|GIF)$/", $fileOnly) && false !== strpos($fileOnly,'.'))
					{
						$icon = "{e_IMAGE}filemanager/zip_32.png";	
						$src = $tp->replaceConstants(vartrue($parms['pre']).$icon, 'abs');
					//	return $value;
						return e107::getParser()->toGlyph('fa-file','size=2x');
				//		return '<img src="'.$src.'" alt="'.$value.'" class="e-thumb" title="'.$value.'" />';
					}
					
					if(vartrue($parms['thumb']))
					{
						$thparms = array();
						
						// Support readParms example: thumb=1&w=200&h=300
						// Support readParms example: thumb=1&aw=80&ah=30
						if(isset($parms['h']))		{ 	$thparms['h'] 	= intval($parms['h']); 		}
						if(isset($parms['ah']))		{ 	$thparms['ah'] 	= intval($parms['ah']); 	}		
						if(isset($parms['w']))		{ 	$thparms['w'] 	= intval($parms['w']); 		}
						if(isset($parms['aw']))		{ 	$thparms['aw'] 	= intval($parms['aw']); 	}
						
						// Support readParms example: thumb=200x300 (wxh)
						if(strpos($parms['thumb'],'x')!==false)
						{
							list($thparms['w'],$thparms['h']) = explode('x',$parms['thumb']); 	
						}
						
						// Support readParms example: thumb={width}
						if(!isset($parms['w']) && is_numeric($parms['thumb']) && '1' != $parms['thumb']) 
						{
							$thparms['w'] = intval($parms['thumb']);
						}
						elseif(vartrue($parms['thumb_aw'])) // Legacy v2. 
						{
							$thparms['aw'] = intval($parms['thumb_aw']);
						}
						
					//	return print_a($thparms,true); 
					
						$src = $tp->replaceConstants(vartrue($parms['pre']).$value, 'abs');
						$thsrc = $tp->thumbUrl(vartrue($parms['pre']).$value, $thparms, varset($parms['thumb_urlraw']));
						$alt = basename($src);
						$ttl = '<img src="'.$thsrc.'" alt="'.$alt.'" class="thumbnail e-thumb" />';
						$value = '<a href="'.$src.'" data-modal-caption="'.$alt.'" data-target="#uiModal" class="e-modal e-image-preview" title="'.$alt.'" rel="external">'.$ttl.'</a>';
					}
					else
					{
						$src = $tp->replaceConstants(vartrue($parms['pre']).$value, 'abs');
						$alt = $src; //basename($value);
						$ttl = vartrue($parms['title'], 'LAN_PREVIEW');
						$value = '<a href="'.$src.'" class="e-image-preview" title="'.$alt.'" rel="external">'.defset($ttl, $ttl).'</a>';
					}
				}
			break;
			
			case 'files':
				$ret = '<ol>';
				for ($i=0; $i < 5; $i++) 
				{				
					$k 		= $key.'['.$i.'][path]';
					$ival 	= $value[$i]['path'];
					$ret .=  '<li>'.$ival.'</li>';		
				}
				$ret .= '</ol>';
				$value = $ret;
			break; 
			
			case 'datestamp':
				$value = $value ? e107::getDate()->convert_date($value, vartrue($parms['mask'], 'short')) : '';
			break;
			
			case 'date':
				// just show original value
			break;

			case 'userclass':
				$dispvalue = $this->_uc->uc_get_classname($value);
					// Inline Editing.  
				if(!vartrue($attributes['noedit']) && vartrue($parms['editable']) && !vartrue($parms['link'])) // avoid bad markup, better solution coming up
				{
					$mode = preg_replace('/[^\w]/', '', vartrue($_GET['mode'], ''));

					$uc_options = vartrue($parms['classlist'], 'public,guest,nobody,member,admin,main,classes'); // defaults to 'public,guest,nobody,member,classes' (userclass handler)
					unset($parms['classlist']);

					$array = e107::getUserClass()->uc_required_class_list($uc_options); //XXX Ugly looking (non-standard) function naming - TODO discuss name change.
					$source = str_replace('"',"'",json_encode($array, JSON_FORCE_OBJECT));
					
					//NOTE Leading ',' required on $value; so it picks up existing value.
					$value = "<a class='e-tip e-editable editable-click' data-placement='left' data-value='".$value."' data-name='".$field."' data-source=\"".$source."\" title=\"".LAN_EDIT." ".$attributes['title']."\" data-type='select' data-pk='".$id."' data-url='".e_SELF."?mode={$mode}&amp;action=inline&amp;id={$id}&amp;ajax_used=1' href='#'>".$dispvalue."</a>";
					
				}
				else 
				{
					$value = $dispvalue;	
				}
			break;

			case 'userclasses':
			//	return $value;
				$classes = explode(',', $value);

				$uv = array();
				foreach ($classes as $cid)
				{
					if(!empty($parms['defaultLabel']) && $cid === '')
					{
						$uv[] = $parms['defaultLabel'];
						continue;
					}

					$uv[] = $this->_uc->getName($cid);
				}



				$dispvalue = implode(vartrue($parms['separator'],"<br />"), $uv);

				// Inline Editing.  
				if(!vartrue($attributes['noedit']) && vartrue($parms['editable']) && !vartrue($parms['link'])) // avoid bad markup, better solution coming up
				{
					$uc_options = vartrue($parms['classlist'], 'public,guest, nobody,member,admin,main,classes'); // defaults to 'public,guest,nobody,member,classes' (userclass handler)
					$array      = e107::getUserClass()->uc_required_class_list($uc_options); //XXX Ugly looking (non-standard) function naming - TODO discuss name change.

					//$mode = preg_replace('/[^\w]/', '', vartrue($_GET['mode'], ''));
					$mode       = $tp->filter(vartrue($_GET['mode'], ''),'w');
					$source     = str_replace('"',"'",json_encode($array, JSON_FORCE_OBJECT));

					//NOTE Leading ',' required on $value; so it picks up existing value.
					$value = "<a class='e-tip e-editable editable-click' data-placement='bottom' data-value=',".$value."' data-name='".$field."' data-source=\"".$source."\" title=\"".LAN_EDIT." ".$attributes['title']."\" data-type='checklist' data-pk='".$id."' data-url='".e_SELF."?mode={$mode}&amp;action=inline&amp;id={$id}&amp;ajax_used=1' href='#'>".$dispvalue."</a>";
				}
				else 
				{
					$value = $dispvalue;	
				}

				unset($parms['classlist']);
				
			break;

			/*case 'user_name':
			case 'user_loginname':
			case 'user_login':
			case 'user_customtitle':
			case 'user_email':*/
			case 'user':
				
				/*if(is_numeric($value))
				{
					$value = e107::user($value);
					if($value)
					{
						$value = $value[$attributes['type']] ? $value[$attributes['type']] : $value['user_name'];
					}
					else
					{
						$value = 'not found';
					}
				}*/
				// Dirty, but the only way for now
				$id = 0;
				$ttl = LAN_ANONYMOUS;

				//Defaults to user_id and user_name (when present) and when idField and nameField are not present.


				// previously set - real parameters are idField && nameField
				$id = vartrue($parms['__idval']);
				if($value && !is_numeric($value))
				{
					$id = vartrue($parms['__idval']);
					$ttl = $value;
				}
				elseif($value && is_numeric($value))
				{
					$id = $value;
					$ttl = vartrue($parms['__nameval']);
				}


				if(!empty($parms['link']) && $id && $ttl && is_numeric($id))
				{
					// Stay in admin area.
					$link = e_ADMIN."users.php?mode=main&action=edit&id=".$id."&readonly=1&iframe=1"; // e107::getUrl()->create('user/profile/view', array('id' => $id, 'name' => $ttl))

					$value = '<a class="e-modal" data-modal-caption="User #'.$id.' : '.$ttl.'" href="'.$link.'" title="Go to user profile">'.$ttl.'</a>';
				}
				else
				{
					$value = $ttl;
				}
				
			break;

			case 'bool':
			case 'boolean':
				$false = vartrue($parms['trueonly']) ? "" : ADMIN_FALSE_ICON;

				if(!vartrue($attributes['noedit']) && vartrue($parms['editable']) && !vartrue($parms['link'])) // avoid bad markup, better solution coming up
				{
					if(isset($parms['false'])) // custom representation for 'false'. (supports font-awesome when set by css)
					{
						$false = $parms['false'];	
					}
					else
					{	
						$false = ($value === '') ? "&square;" : "&cross;";		
					}
					
					$true = varset($parms['true'],'&check;'); // custom representation for 'true'. (supports font-awesome when set by css)
					
					
					$value = intval($value);
							
					$wparms = (vartrue($parms['reverse'])) ? array(0=>$true, 1=>$false) : array(0=>$false, 1=>$true);
					$dispValue = $wparms[$value];

					return $this->renderInline($field, $id, $attributes['title'], $value, $dispValue, 'select', $wparms);
				}
				
				if(vartrue($parms['reverse']))
				{
					$value = ($value) ? $false : ADMIN_TRUE_ICON;	
				}
				else
				{
					$value = $value ? ADMIN_TRUE_ICON : $false;	
				}	
							
			break;

			case 'url':
				if(!$value) break;
				$ttl = $value;
				if(vartrue($parms['href']))
				{
					return $tp->replaceConstants(vartrue($parms['pre']).$value, varset($parms['replace_mod'],'abs'));
				}
				if(vartrue($parms['truncate']))
				{
					$ttl = $tp->text_truncate($value, $parms['truncate'], '...');
				}
				$value = "<a href='".$tp->replaceConstants(vartrue($parms['pre']).$value, 'abs')."' title='{$value}'>".$ttl."</a>";
			break;

			case 'email':
				if(!$value) break;
				$ttl = $value;
				if(vartrue($parms['truncate']))
				{
					$ttl = $tp->text_truncate($value, $parms['truncate'], '...');
				}
				$value = "<a href='mailto:".$value."' title='{$value}'>".$ttl."</a>";
			break;

			case 'method': // Custom Function			
				$method = $attributes['field']; // prevents table alias in method names. ie. u.my_method. 
				$_value = $value;

				if($attributes['data'] == 'array') // FIXME @SecretR - please move this to where it should be.
				{
					$value = e107::unserialize($value); // (saved as array, return it as an array)
				}

				$meth = (!empty($attributes['method'])) ? $attributes['method'] : $method;

				if(method_exists($this,$meth))
				{
					$parms['field'] = $field;
					$value = call_user_func_array(array($this, $meth), array($value, 'read', $parms));
				}
				else
				{
					return "<span class='label label-important'>Missing: ".$method."()</span>";
				}
			//	 print_a($attributes);
					// Inline Editing.  
				if(!vartrue($attributes['noedit']) && vartrue($parms['editable'])) // avoid bad markup, better solution coming up
				{
					
					$mode = preg_replace('/[^\w]/', '', vartrue($_GET['mode'], ''));
					$methodParms = call_user_func_array(array($this, $method), array($value, 'inline', $parms));


					if(!empty($methodParms['inlineType']))
					{
						$attributes['inline'] = $methodParms['inlineType'];
						$methodParms = (!empty($methodParms['inlineData'])) ? $methodParms['inlineData'] : null;
					}

					if(is_string($attributes['inline'])) // text, textarea, select, checklist. 
					{
						switch ($attributes['inline']) 
						{
					
							case 'checklist':
								$xtype = 'checklist';		
							break;
							
							case 'select':
							case 'dropdown':
								$xtype = 'select';		
							break;
							
							case 'textarea':
								$xtype = 'textarea';		
							break;
							
							
							default:
								$xtype = 'text';
								$methodParms = null;
							break;
						}
					}

					if(!empty($xtype))
					{
						$value = $this->renderInline($field, $id, $attributes['title'], $_value, $value, $xtype, $methodParms);
					}
				

				
				}
							
			break;

			case 'hidden':
				return (vartrue($parms['show']) ? ($value ? $value : vartrue($parms['empty'])) : '');
			break;
			
			case 'language': // All Known Languages. 
					
				if(!empty($value))
				{
					$_value = $value;
					if(strlen($value) === 2)
					{
						$value = e107::getLanguage()->convert($value);
					}
				}
				
				if(!vartrue($attributes['noedit']) && vartrue($parms['editable'])) 
				{
					$wparms = e107::getLanguage()->getList();
					return $this->renderInline($field, $id, $attributes['title'], $_value, $value, 'select', $wparms);	
				}	
				
				return $value;
				
			break;

			case 'lanlist': // installed languages. 
				$options = e107::getLanguage()->getLanSelectArray();

				if($options) // FIXME - add support for multi-level arrays (option groups)
				{
					if(!is_array($attributes['writeParms'])) parse_str($attributes['writeParms'], $attributes['writeParms']);
					$wparms = $attributes['writeParms'];
					if(!is_array(varset($wparms['__options']))) parse_str($wparms['__options'], $wparms['__options']);
					$opts = $wparms['__options'];
					if($opts['multiple'])
					{
						$ret = array();
						$value = is_array($value) ? $value : explode(',', $value);
						foreach ($value as $v)
						{
							if(isset($options[$v])) $ret[] = $options[$v];
						}
						$value = implode(', ', $ret);
					}
					else
					{
						$ret = '';
						if(isset($options[$value])) $ret = $options[$value];
						$value = $ret;
					}
					$value = ($value ? vartrue($parms['pre']).$value.vartrue($parms['post']) : '');
				}
				else
				{
					$value = '';
				}
			break;

			//TODO - order

			default:
				//unknown type
			break;
		}

		return $value;
	}

	/**
	 * Auto-render Form Element
	 * @param string $key
	 * @param mixed $value
	 * @param array $attributes field attributes including render parameters, element options - see e_admin_ui::$fields for required format
	 * #param array (under construction) $required_data required array as defined in e_model/validator
	 * @return string
	 */
	function renderElement($key, $value, $attributes, $required_data = array(), $id = 0)
	{
	//	return print_a($value,true);
		$parms = vartrue($attributes['writeParms'], array());

		$tp = e107::getParser();

		if(is_string($parms)) parse_str($parms, $parms);
		
		if(empty($value) && !empty($parms['default'])) // Allow writeParms to set default value. 
		{
			$value = $parms['default'];
		}

		// Two modes of read-only. 1 = read-only, but only when there is a value, 2 = read-only regardless.
		if(vartrue($attributes['readonly']) && (vartrue($value) || vartrue($attributes['readonly'])===2)) // quick fix (maybe 'noedit'=>'readonly'?)
		{
			if(vartrue($attributes['writeParms'])) // eg. different size thumbnail on the edit page. 
			{
				$attributes['readParms'] = $attributes['writeParms'];
			}
			return $this->renderValue($key, $value, $attributes).$this->hidden($key, $value); //
		}
		
		// FIXME standard - writeParams['__options'] is introduced for list elements, bundle adding to writeParms is non reliable way
		$writeParamsOptionable =  array('dropdown', 'comma', 'radio', 'lanlist', 'language', 'user');
		$writeParamsDisabled =  array('layouts', 'templates', 'userclass', 'userclasses');

		// FIXME it breaks all list like elements - dropdowns, radio, etc
		if(vartrue($required_data[0]) || vartrue($attributes['required'])) // HTML5 'required' attribute 
		{
			// FIXME - another approach, raise standards, remove checks
			if(in_array($attributes['type'], $writeParamsOptionable))
			{
				$parms['__options']['required'] = 1;	
			}
			elseif(!in_array($attributes['type'], $writeParamsDisabled))
			{
				$parms['required'] = 1;	
			}
		}
		
		// FIXME it breaks all list like elements - dropdowns, radio, etc
		if(vartrue($required_data[3]) || vartrue($attributes['pattern'])) // HTML5 'pattern' attribute
		{
			// FIXME - another approach, raise standards, remove checks
			if(in_array($attributes['type'], $writeParamsOptionable))
			{
				$parms['__options']['pattern'] = vartrue($attributes['pattern'], $required_data[3]);
			}
			elseif(!in_array($attributes['type'], $writeParamsDisabled))
			{
				$parms['pattern'] = vartrue($attributes['pattern'], $required_data[3]);	
			}
		}

		// XXX Fixes For the above.  - use optArray variable. eg. $field['key']['writeParms']['optArray'] = array('one','two','three');
		if(($attributes['type'] == 'dropdown' || $attributes['type'] == 'radio' || $attributes['type'] == 'checkboxes') && !empty($parms['optArray']))
		{
			$fopts = $parms;
			$parms = $fopts['optArray'];
			unset($fopts['optArray']);
			$parms['__options'] = $fopts;
		}

		$this->renderElementTrigger($key, $value, $parms, $required_data, $id);
		
		switch($attributes['type'])
		{
			case 'number':
				$maxlength = vartrue($parms['maxlength'], 255);
				unset($parms['maxlength']);
				if(!vartrue($parms['size'])) $parms['size'] = 'mini';
				if(!vartrue($parms['class'])) $parms['class'] = 'tbox number e-spinner';
				if(!$value) $value = '0';
				$ret =  vartrue($parms['pre']).$this->number($key, $value, $maxlength, $parms).vartrue($parms['post']);
			break;

			case 'ip':
				$ret = vartrue($parms['pre']).$this->text($key, e107::getIPHandler()->ipDecode($value), 32, $parms).vartrue($parms['post']);
			break;

			case 'email':
				$maxlength = vartrue($parms['maxlength'], 255);
				unset($parms['maxlength']);
				$ret =  vartrue($parms['pre']).$this->email($key, $value, $maxlength, $parms).vartrue($parms['post']); // vartrue($parms['__options']) is limited. See 'required'=>true
			break;

			case 'url':
				$maxlength = vartrue($parms['maxlength'], 255);
				unset($parms['maxlength']);
				$ret =  vartrue($parms['pre']).$this->url($key, $value, $maxlength, $parms).vartrue($parms['post']); // vartrue($parms['__options']) is limited. See 'required'=>true
		
			break; 
		//	case 'email':
		
			case 'password': // encrypts to md5 when saved. 
				$maxlength = vartrue($parms['maxlength'], 255);
				unset($parms['maxlength']);
				$ret =  vartrue($parms['pre']).$this->password($key, $value, $maxlength, $parms).vartrue($parms['post']); // vartrue($parms['__options']) is limited. See 'required'=>true
			
			break; 
		
			case 'text':

				$maxlength = vartrue($parms['maxlength'], 255);
				unset($parms['maxlength']);
				
				if(!empty($parms['password'])) // password mechanism without the md5 storage. 
				{
					$ret =  vartrue($parms['pre']).$this->password($key, $value, $maxlength, $parms).vartrue($parms['post']);	
				}
				else
				{
					$ret =  vartrue($parms['pre']).$this->text($key, $value, $maxlength, $parms).vartrue($parms['post']); // vartrue($parms['__options']) is limited. See 'required'=>true
				}
				
			break;
			
			case 'tags':
				$maxlength = vartrue($parms['maxlength'], 255);
				$ret =  vartrue($parms['pre']).$this->tags($key, $value, $maxlength, $parms).vartrue($parms['post']); // vartrue($parms['__options']) is limited. See 'required'=>true
			break;

			case 'textarea':
				$text = "";
				if(vartrue($parms['append']) && vartrue($value)) // similar to comments - TODO TBD. a 'comment' field type may be better.
				{
					$attributes['readParms'] = 'bb=1';
					
					$text = $this->renderValue($key, $value, $attributes);					
					$text .= '<br />';
					$value = "";
					
					// Appending needs is  performed and customized using function: beforeUpdate($new_data, $old_data, $id)
				}

				$text .= $this->textarea($key, $value, vartrue($parms['rows'], 5), vartrue($parms['cols'], 40), vartrue($parms['__options'],$parms), varset($parms['counter'], false));
				$ret =  $text;
			break;

			case 'bbarea':
				$options = array('counter' => varset($parms['counter'], false)); 
				// Media = media-category owner used by media-manager. 
				$ret =  $this->bbarea($key, $value, vartrue($parms['template']), vartrue($parms['media']), vartrue($parms['size'], 'medium'),$options );
			break;

			case 'image': //TODO - thumb, image list shortcode, js tooltip...
				$label = varset($parms['label'], 'LAN_EDIT');
				unset($parms['label']);
				$ret =  $this->imagepicker($key, $value, defset($label, $label), $parms);
			break;
			
			case 'images':
			//	return print_a($value, true);
				$ret = "";
				$label = varset($parms['label'], 'LAN_EDIT');

				for ($i=0; $i < 5; $i++) 
				{				
					$k 		= $key.'['.$i.'][path]';
					$ival 	= $value[$i]['path'];
					
					$ret .=  $this->imagepicker($k, $ival, defset($label, $label), $parms);		
				}
				
			break;
			
			case 'files':
			
				if($attributes['data'] == 'array')
				{
					$parms['data'] = 'array';	
				}

				$ret = '<ol>';
				for ($i=0; $i < 5; $i++) 
				{				
				//	$k 		= $key.'['.$i.'][path]';
				//	$ival 	= $value[$i]['path'];
					$k 		= $key.'['.$i.']';
					$ival 	= $value[$i];
					$ret .=  '<li>'.$this->filepicker($k, $ival, defset($label, $label), $parms).'</li>';		
				}
				$ret .= '</ol>';
			break;
			
			case 'file': //TODO - thumb, image list shortcode, js tooltip...
				$label = varset($parms['label'], 'LAN_EDIT');
				unset($parms['label']);
				$ret =  $this->filepicker($key, $value, defset($label, $label), $parms);
			break;

			case 'icon':
				$label = varset($parms['label'], 'LAN_EDIT');
				$ajax = varset($parms['ajax'], true) ? true : false;
				unset($parms['label'], $parms['ajax']);
				$ret =  $this->iconpicker($key, $value, defset($label, $label), $parms, $ajax);
			break;

			case 'date': // date will show the datepicker but won't convert the value to unix. ie. string value will be saved. (or may be processed manually with beforeCreate() etc. Format may be determined by $parm. 
			case 'datestamp':
				// If hidden, value is updated regardless. eg. a 'last updated' field.
				// If not hidden, and there is a value, it is retained. eg. during the update of an existing record.
				// otherwise it is added. eg. during the creation of a new record.
				if(vartrue($parms['auto']) && (($value == null) || vartrue($parms['hidden'])))
				{
					$value = time();
				}
				
				if(vartrue($parms['readonly'])) // different to 'attribute-readonly' since the value may be auto-generated. 
				{
					$ret =  $this->renderValue($key, $value, $attributes).$this->hidden($key, $value);
				}
				elseif(vartrue($parms['hidden']))
				{
					$ret =  $this->hidden($key, $value);
				}
				else
				{
					$ret =  $this->datepicker($key, $value, $parms);	
				}				
			break;

			case 'layouts': //to do - exclude param (exact match)
				$location = varset($parms['plugin']); // empty - core
				$ilocation = vartrue($parms['id'], $location); // omit if same as plugin name
				$where = vartrue($parms['area'], 'front'); //default is 'front'
				$filter = varset($parms['filter']);
				$merge = vartrue($parms['merge']) ? true : false;
				$layouts = e107::getLayouts($location, $ilocation, $where, $filter, $merge, true);
				if(varset($parms['default']) && !isset($layouts[0]['default']))
				{
					$layouts[0] = array('default' => $parms['default']) + $layouts[0];
				}
				$info = array();
				if($layouts[1])
				{
					foreach ($layouts[1] as $k => $info_array)
					{
						if(isset($info_array['description']))
						$info[$k] = defset($info_array['description'], $info_array['description']);
					}
				}

				//$this->selectbox($key, $layouts, $value)
				$ret =  (vartrue($parms['raw']) ? $layouts[0] : $this->radio_multi($key, $layouts[0], $value,array('sep'=>"<br />"), $info));
			break;

			case 'templates': //to do - exclude param (exact match)
				$templates = array();
				if(varset($parms['default']))
				{
					$templates['default'] = defset($parms['default'], $parms['default']);
				}
				$location = vartrue($parms['plugin']) ? e_PLUGIN.$parms['plugin'].'/' : e_THEME;
				$ilocation = vartrue($parms['location']);
				$tmp = e107::getFile()->get_files($location.'templates/'.$ilocation, vartrue($parms['fmask'], '_template\.php$'), vartrue($parms['omit'], 'standard'), vartrue($parms['recurse_level'], 0));
				foreach($tmp as $files)
				{
					$k = str_replace('_template.php', '', $files['fname']);
					$templates[$k] = implode(' ', array_map('ucfirst', explode('_', $k))); //TODO add LANS?
				}

				// override
				$where = vartrue($parms['area'], 'front');
				$location = vartrue($parms['plugin']) ? $parms['plugin'].'/' : '';
				$tmp = e107::getFile()->get_files(e107::getThemeInfo($where, 'rel').'templates/'.$location.$ilocation, vartrue($parms['fmask']), vartrue($parms['omit'], 'standard'), vartrue($parms['recurse_level'], 0));
				foreach($tmp as $files)
				{
					$k = str_replace('_template.php', '', $files['fname']);
					$templates[$k] = implode(' ', array_map('ucfirst', explode('_', $k))); //TODO add LANS?
				}
				$ret =  (vartrue($parms['raw']) ? $templates : $this->selectbox($key, $templates, $value));
			break;

			case 'checkboxes':

				if(is_array($parms))
				{
					$eloptions  = vartrue($parms['__options'], array());
					if(is_string($eloptions)) parse_str($eloptions, $eloptions);
					if($attributes['type'] === 'comma') $eloptions['multiple'] = true;
					unset($parms['__options']);

					if(!is_array($value) && !empty($value))
					{
						$value = explode(",",$value);		
					}


					$ret =  vartrue($eloptions['pre']).$this->checkboxes($key, $parms, $value, $eloptions).vartrue($eloptions['post']);


				}
				return $ret;
			break;


			case 'dropdown':
			case 'comma':	
				$eloptions  = vartrue($parms['__options'], array());
				if(is_string($eloptions)) parse_str($eloptions, $eloptions);
				if($attributes['type'] === 'comma') $eloptions['multiple'] = true;
				unset($parms['__options']);
				if(vartrue($eloptions['multiple']) && !is_array($value)) $value = explode(',', $value);
				$ret =  vartrue($eloptions['pre']).$this->selectbox($key, $parms, $value, $eloptions).vartrue($eloptions['post']);
			break;

			case 'radio':
				// TODO - more options (multi-line, help)
				$eloptions  = vartrue($parms['__options'], array());
				if(is_string($eloptions)) parse_str($eloptions, $eloptions);
				unset($parms['__options']);
				$ret =  vartrue($eloptions['pre']).$this->radio_multi($key, $parms, $value, $eloptions, false).vartrue($eloptions['post']);
			break;

			case 'userclass':
			case 'userclasses':
				$uc_options = vartrue($parms['classlist'], 'public,guest,nobody,member,admin,main,classes'); // defaults to 'public,guest,nobody,member,classes' (userclass handler)
				unset($parms['classlist']);

			//	$method = ($attributes['type'] == 'userclass') ? 'uc_select' : 'uc_select';
				if(vartrue($attributes['type']) == 'userclasses'){ $parms['multiple'] = true; }
				$ret =   vartrue($parms['pre']).$this->uc_select($key, $value, $uc_options, vartrue($parms, array())). vartrue($parms['post']);
			break;

			/*case 'user_name':
			case 'user_loginname':
			case 'user_login':
			case 'user_customtitle':
			case 'user_email':*/
			case 'user':
				//user_id expected
				// Just temporary solution, could be changed soon
				if(!isset($parms['__options'])) $parms['__options'] = array();
				if(!is_array($parms['__options'])) parse_str($parms['__options'], $parms['__options']);

				if((empty($value) && varset($parms['currentInit'],USERID)!==0) || vartrue($parms['current'])) // include current user by default.
				{
					$value = USERID;
					if(vartrue($parms['current']))
					{
						$parms['__options']['readonly'] = true;
					}
				}

				if(!is_array($value))
				{
					$value = $value ? e107::getSystemUser($value, true)->getUserData() : array();// e107::user($value);
				}

				$colname = vartrue($parms['nameType'], 'user_name');
				$parms['__options']['name'] = $colname;

				if(!$value) $value = array();
				$uname = varset($value[$colname]);
				$value = varset($value['user_id'], 0);
				$ret =  $this->userpicker(vartrue($parms['nameField'], $key.'_usersearch'), $key, $uname, $value, vartrue($parms['__options']));
			break;

			case 'bool':
			case 'boolean':

				if(varset($parms['label']) === 'yesno')
				{
					$lenabled = 'LAN_YES';
					$ldisabled = 'LAN_NO';
				}
				else
				{
					$lenabled = vartrue($parms['enabled'], 'LAN_ENABLED');
					$ldisabled = vartrue($parms['disabled'], 'LAN_DISABLED');
				}
				unset($parms['enabled'], $parms['disabled'], $parms['label']);
				$ret =  vartrue($parms['pre']).$this->radio_switch($key, $value, defset($lenabled, $lenabled), defset($ldisabled, $ldisabled),$parms).vartrue($parms['post']);
			break;

			case "checkbox":

				$value = (isset($parms['value'])) ? $parms['value'] : $value;
				$ret =  vartrue($parms['pre']).$this->checkbox($key, 1, $value,$parms).vartrue($parms['post']);
			break;

			case 'method': // Custom Function
				$meth = (!empty($attributes['method'])) ? $attributes['method'] : $key;
				$parms['field'] = $key;

				$ret =  call_user_func_array(array($this, $meth), array($value, 'write', $parms));
			break;

			case 'upload': //TODO - from method
				// TODO uploadfile SC is now processing uploads as well (add it to admin UI), write/readParms have to be added (see uploadfile.php parms)
				$disbut = varset($parms['disable_button'], '0');
				$ret =  $tp->parseTemplate("{UPLOADFILE=".(vartrue($parms['path']) ? e107::getParser()->replaceConstants($parms['path']) : e_UPLOAD)."|nowarn&trigger=etrigger_uploadfiles&disable_button={$disbut}}");
			break;

			case 'hidden':

				$value = (isset($parms['value'])) ? $parms['value'] : $value;
				$ret = (vartrue($parms['show']) ? ($value ? $value : varset($parms['empty'], $value)) : '');
				$ret =  $ret.$this->hidden($key, $value);
			break;

			case 'lanlist': // installed languages
			case 'language': // all languages
				
				$options = ($attributes['type'] === 'language') ? e107::getLanguage()->getList() : e107::getLanguage()->getLanSelectArray();

				$eloptions  = vartrue($parms['__options'], array());
				if(!is_array($eloptions)) parse_str($eloptions, $eloptions);
				unset($parms['__options']);
				if(vartrue($eloptions['multiple']) && !is_array($value)) $value = explode(',', $value);
				$ret =  vartrue($eloptions['pre']).$this->selectbox($key, $options, $value, $eloptions).vartrue($eloptions['post']);
			break;

			case null:
			//	Possibly used in db but should not be submitted in form. @see news_extended.
			break;

			default:// No LAN necessary, debug only. 
				$ret =  (ADMIN) ? "<span class='alert alert-error'>".LAN_ERROR." Unknown 'type' : ".$attributes['type'] ."</span>" : $value;
			break;
		}

		if(vartrue($parms['expand']))
		{
			$k = "exp-".$this->name2id($key);
			$text = "<a class='e-expandit e-tip' href='#{$k}'>".$parms['expand']."</a>";
			$text .= vartrue($parms['help']) ? '<div class="field-help">'.$parms['help'].'</div>' : '';
			$text .= "<div id='{$k}' class='e-hideme'>".$ret."</div>";
			return $text;	
		}
		else
		{
			$ret .= vartrue($parms['help']) ? '<div class="field-help">'.$tp->toHtml($parms['help'],false,'defs').'</div>' : '';	
		}

		return $ret;
	}

	/**
	 * Generic List Form, used internal by admin UI
	 * Expected options array format:
	 * <code>
	 * <?php
	 * $form_options['myplugin'] = array(
	 * 		'id' => 'myplugin', // unique string used for building element ids, REQUIRED
	 * 		'pid' => 'primary_id', // primary field name, REQUIRED
	 * 		'url' => '{e_PLUGIN}myplug/admin_config.php', // if not set, e_SELF is used
	 * 		'query' => 'mode=main&amp;action=list', // or e_QUERY if not set
	 * 		'head_query' => 'mode=main&amp;action=list', // without field, asc and from vars, REQUIRED
	 * 		'np_query' => 'mode=main&amp;action=list', // without from var, REQUIRED for next/prev functionality
	 * 		'legend' => 'Fieldset Legend', // hidden by default
	 * 		'form_pre' => '', // markup to be added before opening form element (e.g. Filter form)
	 * 		'form_post' => '', // markup to be added after closing form element
	 * 		'fields' => array(...), // see e_admin_ui::$fields
	 * 		'fieldpref' => array(...), // see e_admin_ui::$fieldpref
	 * 		'table_pre' => '', // markup to be added before opening table element
	 * 		'table_post' => '', // markup to be added after closing table element (e.g. Batch actions)
	 * 		'fieldset_pre' => '', // markup to be added before opening fieldset element
	 * 		'fieldset_post' => '', // markup to be added after closing fieldset element
	 * 		'perPage' => 15, // if 0 - no next/prev navigation
	 * 		'from' => 0, // current page, default 0
	 * 		'field' => 'field_name', //current order field name, default - primary field
	 * 		'asc' => 'desc', //current 'order by' rule, default 'asc'
	 * );
	 * $tree_models['myplugin'] = new e_admin_tree_model($data);
	 * </code>
	 * TODO - move fieldset & table generation in separate methods, needed for ajax calls
	 * @param array $form_options
	 * @param e_admin_tree_model $tree_model
	 * @param boolean $nocontainer don't enclose form in div container
	 * @return string
	 */
	public function renderListForm($form_options, $tree_models, $nocontainer = false)
	{
		$tp = e107::getParser();
		$text = '';
		
		
		// print_a($form_options);
		
		foreach ($form_options as $fid => $options)
		{
			$tree_model = $tree_models[$fid];
			$tree = $tree_model->getTree();
			$total = $tree_model->getTotal();
		
			$amount = $options['perPage'];
			$from = vartrue($options['from'], 0);
			$field = vartrue($options['field'], $options['pid']);
			$asc = strtoupper(vartrue($options['asc'], 'asc'));
			$elid = $fid;//$options['id'];
			$query = vartrue($options['query'],e_QUERY); //  ? $options['query'] :  ;
			if(vartrue($_GET['action']) == 'list')
			{
				$query = e_QUERY; //XXX Quick fix for loss of pagination after 'delete'. 	
			}
			$url = (isset($options['url']) ? $tp->replaceConstants($options['url'], 'abs') : e_SELF);
			$formurl = $url.($query ? '?'.$query : '');
			$fields = $options['fields'];
			$current_fields = varset($options['fieldpref']) ? $options['fieldpref'] : array_keys($options['fields']);
			$legend_class = vartrue($options['legend_class'], 'e-hideme');

			

	        $text .= "
				<form method='post' action='{$formurl}' id='{$elid}-list-form'>
				<div>".$this->token()."
					".vartrue($options['fieldset_pre'])."
					<fieldset id='{$elid}-list'>
						<legend class='{$legend_class}'>".$options['legend']."</legend>
						".vartrue($options['table_pre'])."
						<table class='table adminlist table-striped' id='{$elid}-list-table'>
							".$this->colGroup($fields, $current_fields)."
							".$this->thead($fields, $current_fields, varset($options['head_query']), varset($options['query']))."
							<tbody id='e-sort'>
			";

			if(!$tree)
			{
				$text .= "
							</tbody>
						</table>";
				
				$text .= "<div class='alert alert-block alert-info center middle'>".LAN_NO_RECORDS."</div>"; // not prone to column-count issues. 
			}
			else
			{

				foreach($tree as $model)
				{
					e107::setRegistry('core/adminUI/currentListModel', $model);
					$text .= $this->renderTableRow($fields, $current_fields, $model->getData(), $options['pid']);
				}
				e107::setRegistry('core/adminUI/currentListModel', null);
				
				$text .= "</tbody>
						</table>";
			}

			
			$text .= vartrue($options['table_post']); 


			if($tree && $amount)
			{
				// New nextprev SC parameters
				$parms = 'total='.$total;
				$parms .= '&amount='.$amount;
				$parms .= '&current='.$from;
				if(ADMIN_AREA)
				{
					$parms .= '&tmpl_prefix=admin';
				}
				
				// NOTE - the whole url is double encoded - reason is to not break parms query string
				// 'np_query' should be proper (urlencode'd) url query string
				$url = rawurlencode($url.'?'.(varset($options['np_query']) ? str_replace(array('&amp;', '&'), array('&', '&amp;'),  $options['np_query']).'&amp;' : '').'from=[FROM]');
				$parms .= '&url='.$url;
				//$parms = $total.",".$amount.",".$from.",".$url.'?'.($options['np_query'] ? $options['np_query'].'&amp;' : '').'from=[FROM]';
		    	//$text .= $tp->parseTemplate("{NEXTPREV={$parms}}");
				$nextprev = $tp->parseTemplate("{NEXTPREV={$parms}}");
				if ($nextprev)
				{
					$text .= "<div class='nextprev-bar'>".$nextprev."</div>";
				}
			}

			$text .= "
					</fieldset>
					".vartrue($options['fieldset_post'])."
				</div>
				</form>
			";
		}
		if(!$nocontainer)
		{
			$text = '<div class="e-container">'.$text.'</div>';
		}
		return (vartrue($options['form_pre']).$text.vartrue($options['form_post']));
	}

	/**
	 * Generic DB Record Management Form.
	 * TODO - lans
	 * TODO - move fieldset & table generation in separate methods, needed for ajax calls
	 * Expected arrays format:
	 * <code>
	 * <?php
	 * $forms[0] = array(
	 * 		'id'  => 'myplugin',
	 * 		'url' => '{e_PLUGIN}myplug/admin_config.php', //if not set, e_SELF is used
	 * 		'query' => 'mode=main&amp;action=edit&id=1', //or e_QUERY if not set
	 * 		'tabs' => true, // TODO - NOT IMPLEMENTED YET - enable tabs (only if fieldset count is > 1) //XXX Multiple triggers in a single form?
	 * 		'fieldsets' => array(
	 * 			'general' => array(
	 * 				'legend' => 'Fieldset Legend',
	 * 				'fields' => array(...), //see e_admin_ui::$fields
	 * 				'after_submit_options' => array('action' => 'Label'[,...]), // or true for default redirect options
	 * 				'after_submit_default' => 'action_name',
	 * 				'triggers' => 'auto', // standard create/update-cancel triggers
	 * 				//or custom trigger array in format array('sibmit' => array('Title', 'create', '1'), 'cancel') - trigger name - title, action, optional hidden value (in this case named sibmit_value)
	 * 			),
	 *
	 * 			'advanced' => array(
	 * 				'legend' => 'Fieldset Legend',
	 * 				'fields' => array(...), //see e_admin_ui::$fields
	 * 				'after_submit_options' => array('__default' => 'action_name' 'action' => 'Label'[,...]), // or true for default redirect options
	 * 				'triggers' => 'auto', // standard create/update-cancel triggers
	 * 				//or custom trigger array in format array('submit' => array('Title', 'create', '1'), 'cancel' => array('cancel', 'cancel')) - trigger name - title, action, optional hidden value (in this case named sibmit_value)
	 * 			)
	 * 		)
	 * );
	 * $models[0] = new e_admin_model($data);
	 * $models[0]->setFieldIdName('primary_id'); // you need to do it if you don't use your own admin model extension
	 * </code>
	 * @param array $forms numerical array
	 * @param array $models numerical array with values instance of e_admin_model
	 * @param boolean $nocontainer don't enclose in div container
	 * @return string
	 */
	function renderCreateForm($forms, $models, $nocontainer = false)
	{
		$text = '';
		foreach ($forms as $fid => $form)
		{
			$model = $models[$fid];
			$query = isset($form['query']) ? $form['query'] : e_QUERY ;
			$url = (isset($form['url']) ? e107::getParser()->replaceConstants($form['url'], 'abs') : e_SELF).($query ? '?'.$query : '');
			$curTab = varset($_GET['tab'],0);
			
			$text .= "
				<form method='post' action='".$url."' id='{$form['id']}-form' enctype='multipart/form-data' autocomplete='off' >
				<div>
				".vartrue($form['header'])."
				".$this->token()."
			";

			foreach ($form['fieldsets'] as $elid => $data) //XXX rename 'fieldsets' to 'forms' ?
			{
				$elid = $form['id'].'-'.$elid;
		
								
				if(vartrue($data['tabs'])) // Tabs Present 
				{
					$text .= '<ul class="nav nav-tabs">';
					foreach($data['tabs'] as $i=>$label)
					{	
						$class = ($i == $curTab) ? 'class="active" ' : '';
						$text .= '<li '.$class.'><a href="#tab'.$i.'" data-toggle="tab">'.$label.'</a></li>';
					}
					$text .= ' </ul><div class="tab-content">';	
					
					foreach($data['tabs'] as $tabId=>$label)
					{
						$active = ($tabId == $curTab) ? 'active' : '';
						$text .= '<div class="tab-pane '.$active.'" id="tab'.$tabId.'">';
						$text .= $this->renderCreateFieldset($elid, $data, $model, $tabId);	
						$text .= "</div>";	
					}
					
					$text .= "</div>";			
					$text .= $this->renderCreateButtonsBar($elid, $data, $model, $tabId);	// Create/Update Buttons etc. 	 
				 	
				}
				else   // No Tabs Present 
				{
					$text .= $this->renderCreateFieldset($elid, $data, $model, false);		
					$text .= $this->renderCreateButtonsBar($elid, $data, $model, false);	// Create/Update Buttons etc. 	
				}
				
				
			}

			$text .= "
			".vartrue($form['footer'])."
			</div>
			</form>
			";
			
			// e107::js('footer-inline',"Form.focusFirstElement('{$form['id']}-form');",'prototype');
			// e107::getJs()->footerInline("Form.focusFirstElement('{$form['id']}-form');");
		}
		if(!$nocontainer)
		{
			$text = '<div class="e-container">'.$text.'</div>';
		}
		return $text;
	}

	/**
	 * Create form fieldset, called internal by {@link renderCreateForm())
	 *
	 * @param string $id field id
	 * @param array $fdata fieldset data
	 * @param e_admin_model $model
	 * @return string
	 */
	function renderCreateFieldset($id, $fdata, $model, $tab=0)
	{
		
		$text = vartrue($fdata['fieldset_pre'])."
			<fieldset id='{$id}'>
				<legend>".vartrue($fdata['legend'])."</legend>
				".vartrue($fdata['table_pre'])."
				<table class='table adminform'>
					<colgroup>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>
		";

		// required fields - model definition
		$model_required = $model->getValidationRules();
		$required_help = false;
		$hidden_fields = array();


		foreach($fdata['fields'] as $key => $att)
		{
			if($tab !== false && varset($att['tab'], 0) !== $tab)
			{
				continue;
			}
			
			// convert aliases - not supported in edit mod
			if(vartrue($att['alias']) && !$model->hasData($key))
			{
				$key = $att['field'];
			}
			
			if($key == 'checkboxes' || $key == 'options' || ($att['type'] === null))
			{
				continue;	
			}

			$parms = vartrue($att['formparms'], array());
			if(!is_array($parms)) parse_str($parms, $parms);
			$label = vartrue($att['note']) ? '<div class="label-note">'.deftrue($att['note'], $att['note']).'</div>' : '';
			$help = vartrue($att['help']) ? '<div class="field-help">'.deftrue($att['help'], $att['help']).'</div>' : '';

			$valPath = trim(vartrue($att['dataPath'], $key), '/');
			$keyName = $key;
			if(strpos($valPath, '/')) //not TRUE, cause string doesn't start with /
			{
				$tmp = explode('/', $valPath);
				$keyName = array_shift($tmp);
				foreach ($tmp as $path)
				{
					$keyName .= '['.$path.']';
				}
			}
			
			if(!empty($att['writeParms']) && !is_array($att['writeParms']))
			{
				 parse_str(varset($att['writeParms']), $writeParms);
			}
			else
			{
				 $writeParms = varset($att['writeParms']);
			}
			
			
			
			if('hidden' === $att['type'])
			{
				
				if(!vartrue($writeParms['show']))
				{
					$hidden_fields[] = $this->renderElement($keyName, $model->getIfPosted($valPath), $att, varset($model_required[$key], array()));

					continue;
				}
				unset($tmp);
			}
			
			// type null - system (special) fields
			if(vartrue($att['type']) !== null && !vartrue($att['noedit']) && $key != $model->getFieldIdName())
			{
				$required = '';
				$required_class = '';
				if(isset($model_required[$key]) || vartrue($att['validate']))
				{
					$required = $this->getRequiredString();
					$required_class = ' class="required-label"'; // TODO - add 'required-label' to the core CSS definitions
					$required_help = true;
					if(vartrue($att['validate']))
					{
						// override
						$model_required[$key] = array();
						$model_required[$key][] = true === $att['validate'] ? 'required' : $att['validate'];
						$model_required[$key][] = varset($att['rule']);
						$model_required[$key][] = $att['title'];
						$model_required[$key][] = varset($att['error']);
					}
				}

		/*
				if('hidden' === $att['type'])
				{
					parse_str(varset($att['writeParms']), $tmp);
					if(!vartrue($tmp['show']))
					{
						$hidden_fields[] = $this->renderElement($keyName, $model->getIfPosted($valPath), $att, varset($model_required[$key], array()));
						unset($tmp);
						continue;
					}
					unset($tmp);
				}
				*/

				 
				$leftCell = $required."<span{$required_class}>".defset(vartrue($att['title']), vartrue($att['title']))."</span>".$label;
				$rightCell = $this->renderElement($keyName, $model->getIfPosted($valPath), $att, varset($model_required[$key], array()), $model->getId())." {$help}";
				 
				if(vartrue($att['type']) == 'bbarea' || varset($writeParms['nolabel']) == true)
				{
					$text .= "
					<tr><td colspan='2'>";
					
					$text .= "<div style='padding-bottom:8px'>".$leftCell."</div>";
					$text .= $rightCell."
						</td>
						
					</tr>
				";	
					
				}
				else 
				{

					$leftCellClass = (!empty($writeParms['leftCellClass'])) ? " class='".$writeParms['leftCellClass']."'" : "";
					$rightCellClass = (!empty($writeParms['rightCellClass'])) ? " class='".$writeParms['rightCellClass']."'" : "";


					$text .= "
					<tr>
						<td{$leftCellClass}>
							".$leftCell."
						</td>
						<td{$rightCellClass}>
							".$rightCell."
						</td>
					</tr>
				";
				}
				 
				
				
				
				
			}
			//if($bckp) $model->remove($bckp);

		}
		
		//print_a($fdata);
		
		if($required_help)
		{
			$required_help = '<div class="form-note">'.$this->getRequiredString().' - required fields</div>'; //TODO - lans
		}

		$text .= "
					</tbody>
				</table>";

		$text .= implode("\n", $hidden_fields);

		$text .= "</fieldset>";
				
		$text .= vartrue($fdata['fieldset_post']);
		
		return $text;		
		
		/*		
		$text .= "
				".implode("\n", $hidden_fields)."
				".$required_help."
				".vartrue($fdata['table_post'])."
				<div class='buttons-bar center'>
		";
					// After submit options
					$defsubmitopt = array('list' => 'go to list', 'create' => 'create another', 'edit' => 'edit current');
					$submitopt = isset($fdata['after_submit_options']) ? $fdata['after_submit_options'] : true;
					if(true === $submitopt)
					{
						$submitopt = $defsubmitopt;
					}

					if($submitopt)
					{
						$selected = isset($fdata['after_submit_default']) && array_key_exists($fdata['after_submit_default'], $submitopt) ? $fdata['after_submit_default'] : '';
						
					}

					$triggers = vartrue($fdata['triggers'], 'auto');
					if(is_string($triggers) && 'auto' === $triggers)
					{
						$triggers = array();
						if($model->getId())
						{
							$triggers['submit'] = array(LAN_UPDATE, 'update', $model->getId());
						}
						else
						{
							$triggers['submit'] = array(LAN_CREATE, 'create', 0);
						}
						$triggers['cancel'] = array(LAN_CANCEL, 'cancel');
					}

					foreach ($triggers as $trigger => $tdata)
					{
						$text .= ($trigger == 'submit') ? "<div class=' btn-group'>" : "";
						$text .= $this->admin_button('etrigger_'.$trigger, $tdata[0], $tdata[1]);
						
						if($trigger == 'submit' && $submitopt)
						{
						
							$text .= 
							'<button class="btn btn-success dropdown-toggle left" data-toggle="dropdown">
									<span class="caret"></span>
									</button>
									<ul class="dropdown-menu col-selection">
									<li class="nav-header">After submit:</li>
							';
							
							foreach($defsubmitopt as $k=>$v)
							{
								$text .= "<li><a href='#' class='e-noclick'>".$this->radio('__after_submit_action', $k, $selected,"label=".$v)."</a></li>";	
							}
							
							//$text .= '
							//		<li role="menuitem">
							//			<div class="options left" style="padding:5px">
							//			'.$this->radio_multi('__after_submit_action', $submitopt, $selected, true).'
							//			</div></li>';
										
									
							$text .= '</ul>';
						} 
								
						$text .= ($trigger == 'submit') ?"</div>" : "";
						
						if(isset($tdata[2]))
						{
							$text .= $this->hidden($trigger.'_value', $tdata[2]);
						}
					}

		$text .= "
				</div>
			
			".vartrue($fdata['fieldset_post'])."
		";
		return $text;
		 */
	}
	
	
	function renderCreateButtonsBar($id, $fdata, $model, $tab=0)
	{
		/*
		$text = vartrue($fdata['fieldset_pre'])."
			<fieldset id='{$id}'>
				<legend>".vartrue($fdata['legend'])."</legend>
				".vartrue($fdata['table_pre'])."
				<table class='table adminform'>
					<colgroup>
						<col class='col-label' />
						<col class='col-control' />
					</colgroup>
					<tbody>
		";
		*/
		$text = '';
		
		// required fields - model definition
		$model_required = $model->getValidationRules();
		$required_help = false;
		$hidden_fields = array();
		foreach($fdata['fields'] as $key => $att)
		{
			
			if($tab !== false && varset($att['tab'], 0) !== $tab)
			{
				continue;
			}
			
			// convert aliases - not supported in edit mod
			if(vartrue($att['alias']) && !$model->hasData($key))
			{
				$key = $att['field'];
			}
			
			if($key == 'checkboxes' || $key == 'options')
			{
				continue;	
			}

			$parms = vartrue($att['formparms'], array());
			if(!is_array($parms)) parse_str($parms, $parms);
			$label = vartrue($att['note']) ? '<div class="label-note">'.deftrue($att['note'], $att['note']).'</div>' : '';
			$help = vartrue($att['help']) ? '<div class="field-help">'.deftrue($att['help'], $att['help']).'</div>' : '';

			$valPath = trim(vartrue($att['dataPath'], $key), '/');
			$keyName = $key;
			if(strpos($valPath, '/')) //not TRUE, cause string doesn't start with /
			{
				$tmp = explode('/', $valPath);
				$keyName = array_shift($tmp);
				foreach ($tmp as $path)
				{
					$keyName .= '['.$path.']';
				}
			}
			
			if('hidden' === $att['type'])
			{
				if(!is_array($att['writeParms'])) parse_str(varset($att['writeParms']), $tmp);
				else $tmp = $att['writeParms'];
				
				if(!vartrue($tmp['show']))
				{
					$hidden_fields[] = $this->renderElement($keyName, $model->getIfPosted($valPath), $att, varset($model_required[$key], array()), $model->getId());
					unset($tmp);
					continue;
				}
				unset($tmp);
			}
			
			// type null - system (special) fields
			if(vartrue($att['type']) !== null && !vartrue($att['noedit']) && $key != $model->getFieldIdName())
			{
				$required = '';
				$required_class = '';
				if(isset($model_required[$key]) || vartrue($att['validate']))
				{
					$required = $this->getRequiredString();
					$required_class = ' class="required-label"'; // TODO - add 'required-label' to the core CSS definitions
					$required_help = true;
					if(vartrue($att['validate']))
					{
						// override
						$model_required[$key] = array();
						$model_required[$key][] = true === $att['validate'] ? 'required' : $att['validate'];
						$model_required[$key][] = varset($att['rule']);
						$model_required[$key][] = $att['title'];
						$model_required[$key][] = varset($att['error']);
					}
				}
				 
				/*
				$text .= "
					<tr>
						<td>
							".$required."<span{$required_class}>".defset(vartrue($att['title']), vartrue($att['title']))."</span>".$label."
						</td>
						<td>
							".$this->renderElement($keyName, $model->getIfPosted($valPath), $att, varset($model_required[$key], array()))."
							{$help}
						</td>
					</tr>
				";
				 * */
			}
			//if($bckp) $model->remove($bckp);

		}

		if($required_help)
		{
		//	$required_help = '<div class="form-note">'.$this->getRequiredString().' - required fields</div>'; //TODO - lans
		}

	//	$text .= "
	//				</tbody>
	//			</table></fieldset>";
		
	
				
		$text .= "
				".implode("\n", $hidden_fields)."
				".vartrue($fdata['table_post'])."
				<div class='buttons-bar center'>
		";
					// After submit options
					$defsubmitopt = array('list' => 'go to list', 'create' => 'create another', 'edit' => 'edit current');
					$submitopt = isset($fdata['after_submit_options']) ? $fdata['after_submit_options'] : true;
					if(true === $submitopt)
					{
						$submitopt = $defsubmitopt;
					}

					if($submitopt)
					{
						$selected = isset($fdata['after_submit_default']) && array_key_exists($fdata['after_submit_default'], $submitopt) ? $fdata['after_submit_default'] : '';
						
					}

					$triggers = vartrue($fdata['triggers'], 'auto');
					if(is_string($triggers) && 'auto' === $triggers)
					{
						$triggers = array();
						if($model->getId())
						{
							$triggers['submit'] = array(LAN_UPDATE, 'update', $model->getId());
						}
						else
						{
							$triggers['submit'] = array(LAN_CREATE, 'create', 0);
						}
						$triggers['cancel'] = array(LAN_CANCEL, 'cancel');
					}

					foreach ($triggers as $trigger => $tdata)
					{
						$text .= ($trigger == 'submit') ? "<div class=' btn-group'>" : "";
						$text .= $this->admin_button('etrigger_'.$trigger, $tdata[0], $tdata[1]);
						
						if($trigger == 'submit' && $submitopt)
						{
						
							$text .= 
							'<button class="btn btn-success dropdown-toggle left" data-toggle="dropdown">
									<span class="caret"></span>
									</button>
									<ul class="dropdown-menu col-selection">
									<li class="nav-header">After submit:</li>
							';
							
							foreach($defsubmitopt as $k=>$v)
							{
								$text .= "<li><a href='#' class='e-noclick'>".$this->radio('__after_submit_action', $k, $selected == $k, "label=".$v)."</a></li>";	
							}
							
							//$text .= '
							//		<li role="menuitem">
							//			<div class="options left" style="padding:5px">
							//			'.$this->radio_multi('__after_submit_action', $submitopt, $selected, true).'
							//			</div></li>';
										
									
							$text .= '</ul>';
						} 
								
						$text .= ($trigger == 'submit') ?"</div>" : "";
						
						if(isset($tdata[2]))
						{
							$text .= $this->hidden($trigger.'_value', $tdata[2]);
						}
					}

		$text .= "
				</div>
			
			".vartrue($fdata['fieldset_post'])."
		";
		return $text;
	}


	/**
	 * Generic renderForm solution
	 * @param @forms
	 * @param @nocontainer
	 * @return string
	 */
	function renderForm($forms, $nocontainer = false)
	{
		$text = '';
		foreach ($forms as $fid => $form)
		{
			$query = isset($form['query']) ? $form['query'] : e_QUERY ;
			$url = (isset($form['url']) ? e107::getParser()->replaceConstants($form['url'], 'abs') : e_SELF).($query ? '?'.$query : '');

			$text .= "
				".vartrue($form['form_pre'])."
				<form method='post' action='".$url."' id='{$form['id']}-form' enctype='multipart/form-data'>
				<div>
				".vartrue($form['header'])."
				".$this->token()."
			";

			foreach ($form['fieldsets'] as $elid => $fieldset_data)
			{
				$elid = $form['id'].'-'.$elid;
				$text .= $this->renderFieldset($elid, $fieldset_data);
			}

			$text .= "
			".vartrue($form['footer'])."
			</div>
			</form>
			".vartrue($form['form_post'])."
			";
		}
		if(!$nocontainer)
		{
			$text = '<div class="e-container">'.$text.'</div>';
		}
		return $text;
	}
  
    /**
     * Generic renderFieldset solution, will be split to renderTable, renderCol/Row/Box etc - Still in use. 
     */
	function renderFieldset($id, $fdata)
	{
		$colgroup = '';
		if(vartrue($fdata['table_colgroup']))
		{
			$colgroup = "
				<colgroup span='".count($fdata['table_colgroup'])."'>
			";
			foreach ($fdata['table_colgroup'] as $i => $colgr)
			{
				$colgroup .= "<col ";
				foreach ($colgr as $attr => $v)
				{
					$colgroup .= "{$attr}='{$v}'";
				}
				$colgroup .= " />
				";
			}

			$colgroup = "</colgroup>
			";
		}
		$text = vartrue($fdata['fieldset_pre'])."
			<fieldset id='{$id}'>
				<legend>".vartrue($fdata['legend'])."</legend>
				".vartrue($fdata['table_pre'])."

		";

		if(vartrue($fdata['table_rows']) || vartrue($fdata['table_body']))
		{
			$text .= "
				<table class='table adminform'>
					{$colgroup}
					<thead>
						".vartrue($fdata['table_head'])."
					</thead>
					<tbody>
			";

			if(vartrue($fdata['table_rows']))
			{
				foreach($fdata['table_rows'] as $index => $row)
				{
					$text .= "
						<tr id='{$id}-{$index}'>
							$row
						</tr>
					";
				}
			}
			elseif(vartrue($fdata['table_body']))
			{
				$text .= $fdata['table_body'];
			}

			if(vartrue($fdata['table_note']))
			{
				$note = '<div class="form-note">'.$fdata['table_note'].'</div>';
			}

			$text .= "
						</tbody>
					</table>
					".$note."
					".vartrue($fdata['table_post'])."
			";
		}

		$triggers = vartrue($fdata['triggers'], array());
		if($triggers)
		{
			$text .= "<div class='buttons-bar center'>
				".vartrue($fdata['pre_triggers'], '')."
			";
			foreach ($triggers as $trigger => $tdata)
			{
				if(is_string($tdata))
				{
					$text .= $tdata;
					continue;
				}
				$text .= $this->admin_button('etrigger_'.$trigger, $tdata[0], $tdata[1]);
				if(isset($tdata[2]))
				{
					$text .= $this->hidden($trigger.'_value', $tdata[2]);
				}
			}
			$text .= "</div>";
		}

		$text .= "
			</fieldset>
			".vartrue($fdata['fieldset_post'])."
		";
		return $text;
	}
	
	/**
	 * Render Value Trigger - override to modify field/value/parameters
	 * @param string $field field name
	 * @param mixed $value field value
	 * @param array $params 'writeParams' key (see $controller->fields array)
	 * @param int $id record ID
	 */
	public function renderValueTrigger(&$field, &$value, &$params, $id)
	{
		
	}
	
	/**
	 * Render Element Trigger - override to modify field/value/parameters/validation data
	 * @param string $field field name
	 * @param mixed $value field value
	 * @param array $params 'writeParams' key (see $controller->fields array)
	 * @param array $required_data validation data
	 * @param int $id record ID
	 */
	public function renderElementTrigger(&$field, &$value, &$params, &$required_data, $id)
	{
		
	}
}

// DEPRECATED - use above methods instead ($frm)
class form 
{
	function form_open($form_method, $form_action, $form_name = "", $form_target = "", $form_enctype = "", $form_js = "") 
	{
		$method = ($form_method ? "method='".$form_method."'" : "");
		$target = ($form_target ? " target='".$form_target."'" : "");
		$name = ($form_name ? " id='".$form_name."' " : " id='myform'");
		return "\n<form action='".$form_action."' ".$method.$target.$name.$form_enctype.$form_js."><div>".e107::getForm()->token()."</div>";
	}

	function form_text($form_name, $form_size, $form_value, $form_maxlength = FALSE, $form_class = "tbox form-control", $form_readonly = "", $form_tooltip = "", $form_js = "") {
		$name = ($form_name ? " id='".$form_name."' name='".$form_name."'" : "");
		$value = (isset($form_value) ? " value='".$form_value."'" : "");
		$size = ($form_size ? " size='".$form_size."'" : "");
		$maxlength = ($form_maxlength ? " maxlength='".$form_maxlength."'" : "");
		$readonly = ($form_readonly ? " readonly='readonly'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		return "\n<input class='".$form_class."' type='text' ".$name.$value.$size.$maxlength.$readonly.$tooltip.$form_js." />";
	}

	function form_password($form_name, $form_size, $form_value, $form_maxlength = FALSE, $form_class = "tbox form-control", $form_readonly = "", $form_tooltip = "", $form_js = "") {
		$name = ($form_name ? " id='".$form_name."' name='".$form_name."'" : "");
		$value = (isset($form_value) ? " value='".$form_value."'" : "");
		$size = ($form_size ? " size='".$form_size."'" : "");
		$maxlength = ($form_maxlength ? " maxlength='".$form_maxlength."'" : "");
		$readonly = ($form_readonly ? " readonly='readonly'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		return "\n<input class='".$form_class."' type='password' ".$name.$value.$size.$maxlength.$readonly.$tooltip.$form_js." />";
	}

	function form_button($form_type, $form_name, $form_value, $form_js = "", $form_image = "", $form_tooltip = "") {
		$name = ($form_name ? " id='".$form_name."' name='".$form_name."'" : "");
		$image = ($form_image ? " src='".$form_image."' " : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."' " : "");
		return "\n<input class='btn btn-default button' type='".$form_type."' ".$form_js." value='".$form_value."'".$name.$image.$tooltip." />";
	}

	function form_textarea($form_name, $form_columns, $form_rows, $form_value, $form_js = "", $form_style = "", $form_wrap = "", $form_readonly = "", $form_tooltip = "") {
		$name = ($form_name ? " id='".$form_name."' name='".$form_name."'" : "");
		$readonly = ($form_readonly ? " readonly='readonly'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		$wrap = ($form_wrap ? " wrap='".$form_wrap."'" : "");
		$style = ($form_style ? " style='".$form_style."'" : "");
		return "\n<textarea class='tbox form-control' cols='".$form_columns."' rows='".$form_rows."' ".$name.$form_js.$style.$wrap.$readonly.$tooltip.">".$form_value."</textarea>";
	}

	function form_checkbox($form_name, $form_value, $form_checked = 0, $form_tooltip = "", $form_js = "") {
		$name = ($form_name ? " id='".$form_name.$form_value."' name='".$form_name."'" : "");
		$checked = ($form_checked ? " checked='checked'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		return "\n<input type='checkbox' value='".$form_value."'".$name.$checked.$tooltip.$form_js." />";

	}

	function form_radio($form_name, $form_value, $form_checked = 0, $form_tooltip = "", $form_js = "") {
		$name = ($form_name ? " id='".$form_name.$form_value."' name='".$form_name."'" : "");
		$checked = ($form_checked ? " checked='checked'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		return "\n<input type='radio' value='".$form_value."'".$name.$checked.$tooltip.$form_js." />";

	}

	function form_file($form_name, $form_size, $form_tooltip = "", $form_js = "") {
		$name = ($form_name ? " id='".$form_name."' name='".$form_name."'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		return "<input type='file' class='tbox' size='".$form_size."'".$name.$tooltip.$form_js." />";
	}

	function form_select_open($form_name, $form_js = "") {
		return "\n<select id='".$form_name."' name='".$form_name."' class='tbox form-control' ".$form_js." >";
	}

	function form_select_close() {
		return "\n</select>";
	}

	function form_option($form_option, $form_selected = "", $form_value = "", $form_js = "") {
		$value = ($form_value !== FALSE ? " value='".$form_value."'" : "");
		$selected = ($form_selected ? " selected='selected'" : "");
		return "\n<option".$value.$selected." ".$form_js.">".$form_option."</option>";
	}

	function form_hidden($form_name, $form_value) {
		return "\n<input type='hidden' id='".$form_name."' name='".$form_name."' value='".$form_value."' />";
	}

	function form_close() {
		return "\n</form>";
	}
}

/*
Usage
echo $rs->form_open("post", e_SELF, "_blank");
echo $rs->form_text("testname", 100, "this is the value", 100, 0, "tooltip");
echo $rs->form_button("submit", "testsubmit", "SUBMIT!", "", "Click to submit");
echo $rs->form_button("reset", "testreset", "RESET!", "", "Click to reset");
echo $rs->form_textarea("textareaname", 10, 10, "Value", "overflow:hidden");
echo $rs->form_checkbox("testcheckbox", 1, 1);
echo $rs->form_checkbox("testcheckbox2", 2);
echo $rs->form_hidden("hiddenname", "hiddenvalue");
echo $rs->form_radio("testcheckbox", 1, 1);
echo $rs->form_radio("testcheckbox", 1);
echo $rs->form_file("testfile", "20");
echo $rs->form_select_open("testselect");
echo $rs->form_option("Option 1");
echo $rs->form_option("Option 2");
echo $rs->form_option("Option 3", 1, "defaultvalue");
echo $rs->form_option("Option 4");
echo $rs->form_select_close();
echo $rs->form_close();
*/


?>
