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
 * @subpackage handlers
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
	 * @param @target - e_SELF by default

	 * @param $other - unused at the moment. 
	 */
	public function open($name, $method=null, $target=null, $options=null)
	{
		if($target == null)
		{
			$target = e_SELF . (e_QUERY ? "?".e_QUERY : "");	
		}
		
		if($method == null)
		{
			$method = "post";	
		}
	
		parse_str($options,$options);
	
		$target = str_replace("&", "&amp;", $target);
		
		if(vartrue($options['class']))
		{
			$class = "class='".$options['class']."'";
		}
		
		$text = "\n<form {$class} action='{$target}' id='".$this->name2id($name)."' method = '{$method}'>\n";
		
		if($method == 'get' && strpos($target,'='))
		{
			list($url,$qry) = explode("?",$target);
			
			parse_str($qry,$m);
			foreach($m as $k=>$v)
			{
				$text .= $this->hidden($k, $v);					
			}
			
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
		$options['class'] = 'tbox input-text e-tags';
		return $this->text($name, $value, $maxlength, $options);	
	}

	/**
	 * Text-Field Form Element
	 * @param $name
	 * @param $value
	 * @param $maxlength
	 * @param $options
	 */
	function text($name, $value, $maxlength = 80, $options= null)
	{

		if(vartrue($options) && is_string($options))
		{
			parse_str($options,$options);
		}
		
		if(!vartrue($options['class']))
		{
			if($maxlength < 10)
			{
				$options['class'] = 'tbox span3';
			}
			
			if($maxlength < 50)
			{
				$options['class'] = 'tbox span7';	
			}
		
			if($maxlength > 99)
			{
				 $options['class'] = 'tbox span7';
			}
		}	
		

		$options = $this->format_options('text', $name, $options);
		//never allow id in format name-value for text fields
		return "<input type='text' name='{$name}' value='{$value}' maxlength='{$maxlength}'".$this->get_attributes($options, $name)." />";
	}
	
	function number($name, $value, $maxlength = 200, $options = array())
	{
		if(is_string($options)) parse_str($options, $options);
		$maxlength = vartrue($parms['maxlength'], 255);
		unset($parms['maxlength']);
		if(!vartrue($parms['size'])) $parms['size'] = 15;
		if(!vartrue($parms['class'])) $parms['class'] = 'tbox number e-spinner input-small';
		if(!$value) $value = '0';
		return $this->text($key, $value, $maxlength, $parms);	
	}
	
	
	
	function email($name, $value, $maxlength = 200, $options = array())
	{
		$options = $this->format_options('text', $name, $options);
		//never allow id in format name-value for text fields
		return "<input type='email' name='{$name}' value='{$value}' maxlength='{$maxlength}'".$this->get_attributes($options, $name)." />
		";
	}

	function iconpreview($id, $default, $width='', $height='') // FIXME
	{
		// XXX - $name ?!
		$parms = $name."|".$width."|".$height."|".$id;
		$sc_parameters .= 'mode=preview&default='.$default.'&id='.$id;
		return e107::getParser()->parseTemplate("{ICONPICKER=".$sc_parameters."}");
	}

	/**
	 * @param $name
	 * @param $default value
	 * @param $label
	 * @param $sc_parms
	 * @param $ajax
	 */
	function iconpicker($name, $default, $label, $sc_parameters = '', $ajax = true)
	{
    	// TODO - Hide the <input type='text'> element, and display the icon itself after it has been chosen.
		// eg. <img id='iconview' src='".$img."' style='border:0; ".$blank_display."' alt='' />
		// The button itself could be replaced with an icon just for this purpose.
		return $this->imagepicker($name, $default, $label, 'media=_icon');

		$e107 = e107::getInstance();
		$id = $this->name2id($name);
		$sc_parameters .= '&id='.$id;
		$jsfunc = $ajax ? "e107Ajax.toggleUpdate('{$id}-iconpicker', '{$id}-iconpicker-cn', 'sc:iconpicker=".urlencode($sc_parameters)."', '{$id}-iconpicker-ajax', { overlayElement: '{$id}-iconpicker-button' })" : "e107Helper.toggle('{$id}-iconpicker')";
		$ret = $this->text($name, $default);
	//	$ret .= $this->iconpreview($id,$default); //FIXME
		$ret .= $this->admin_button($name.'-iconpicker-button', $label, 'action', '', array('other' => "onclick=\"{$jsfunc}\""));
		$ret .= "
			<div id='{$id}-iconpicker' class='e-hideme'>
				<div class='expand-container' id='{$id}-iconpicker-cn'>
					".(!$ajax ? $e107->tp->parseTemplate('{ICONPICKER='.$sc_parameters.'}') : '')."
				</div>
			</div>
		";

		return $ret;
	}

	/**
	 * Internal Function used by imagepicker and filepicker
	 */ 
	private function mediaUrl($category = '', $label = '', $tagid='', $extras='')
	{
		
		$cat = ($category) ? '&amp;for='.$category : "";
		if(!$label) $label = ' Upload an image or file';
		if($tagid) $cat .= '&amp;tagid='.$tagid; 
		
		parse_str($extras);
		
		if($bbcode) $cat .= '&amp;bbcode=1'; 
		
		if(!$mode) $mode = 'main';
		if(!$action) $action = 'dialog';

		// $tabs // TODO - option to choose which tabs to display.  
		
		//TODO Parse selection data back to parent form. 

		$url = e_ADMIN_ABS."image.php?mode={$mode}&amp;action={$action}".$cat;
		$url .= "&amp;iframe=1";
		$title = "Media Manager : ".$category;

		$ret = "<a title=\"{$title}\" rel='external' class='e-dialog' href='".$url."'>".$label."</a>";
	
	//	$footer = "<div style=\'padding:5px;text-align:center\' <a href=\'#\' >Save</a></div>";
	$footer = '';
		if(!e107::getRegistry('core/form/mediaurl'))
		{
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
		$blankImg 	= $tp->thumbUrl(e_IMAGE."generic/blank_avatar.jpg",$attr);
		$localonly 	= true; //TODO add a pref for allowing external or internal avatars or both. 
		$idinput 	= $this->name2id($name);
		$previnput	= $idinput."-preview";
		$optioni 	= $idinput."-options";
		
		$img = (strpos($curVal,"://")!==false) ? $curVal : $tp->thumbUrl(e_MEDIA."avatars/".$curVal,"aw=".$width."&ah=".$height);
		
		if(!$curVal)
		{
			$img = $blankImg;	
		}
		
		if($localonly == true)
		{
			$text = "<input class='tbox' style='width:80%' id='{$idinput}' type='hidden' name='image' size='40' value='{$curVal}' maxlength='100' />";			
			$text .= "<img src='".$img."' id='{$previnput}' class='e-expandit e-tip avatar' style='cursor:pointer; width:".$pref['im_width']."px; height:".$pref['im_height']."px' title='Choose an avatar for yourself'/>"; // TODO LAN
		}
		else
		{			
			$text = "<input class='tbox' style='width:80%' id='{$idinput}' type='text' name='image' size='40' value='$curVal' maxlength='100' title=\"".LAN_SIGNUP_111."\" />";
			$text .= "<img src='".$img."' id='{$previnput}' style='display:none' />";
			$text .= "<input class='button e-expandit' type ='button' style='cursor:pointer' size='30' value=\"Choose Avatar\"  />"; //TODO Common LAN. 
		}
						
		$avFiles = e107::getFile()->get_files(e_MEDIA."avatars/",".jpg|.png|.gif|.jpeg|.JPG|.GIF|.PNG");
			
		$text .= "\n<div id='{$optioni}' style='display:none' >\n"; //TODO unique id. 
		
		if ($pref['avatar_upload'] && FILE_UPLOADS && vartrue($options['upload']))
		{
				$diz = LAN_USET_32.($pref['im_width'] || $pref['im_height'] ? "\n".str_replace(array('--WIDTH--','--HEIGHT--'), array($pref['im_width'], $pref['im_height']), LAN_USER_86) : "");
	
				$text .= "<div>".LAN_USET_26."<br />
				<input class='tbox' name='file_userfile[avatar]' type='file' size='47' title=\"{$diz}\" />
				</div>
				<div class='divider'><span>OR</span></div>";
		}
		
				
		foreach($avFiles as $fi)
		{
			$img_path = $tp->thumbUrl(e_MEDIA_ABS."avatars/".$fi['fname'],$attr);	
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
	 * FIXME - better GUI, {IMAGESELECTOR} rewrite, flexibility, thumbnails, tooltip image preivew, etc.
	 * FIXME - use the media-manager as an image selector.
	 * SC Parameter list:
	 * - media: if present - load from media table
	 * - path: server pats to be listed (separated by |) - only if 'media' param is not present
	 * - subdirs: folder search depth (default is 10)
	 * - width: preview width in pixels
	 * - height: preview height in pixels
	 * Additional usage is <code>$sc_parameters = 'news'</code>
	 * where
	 * Full list can be found in shortcodes/imageselector.php
	 * @param string $name input name
	 * @param string $default default value
	 * @param string $label custom label
	 * @param string $sc_parameters shortcode parameters
	 * @return string html output
	 */
	function imagepicker($name, $default, $label = '', $sc_parameters = '')
	{
		$tp = e107::getParser();
		$name_id = $this->name2id($name);
		if(is_string($sc_parameters))
		{
			if(strpos($sc_parameters, '=') === false) $sc_parameters = 'media='.$sc_parameters;
			parse_str($sc_parameters, $sc_parameters);
		}
		
		$default_thumb = $default;
		if($default)
		{
			if('{' != $default[0])
			{
				// convert to sc path
				$default_thumb = $tp->createConstants($default, 'nice');
				$default = $tp->createConstants($default, 'mix');
			}
			$default_url = $tp->replaceConstants($default, 'abs');
			$blank = FALSE;
		}
		else
		{
			//$default = $default_url = e_IMAGE_ABS."generic/blank.gif";
			$default_url = e_IMAGE_ABS."generic/blank.gif";
			$blank = TRUE;
		}
		
		//$width = intval(vartrue($sc_parameters['width'], 150));
		$cat = $tp->toDB($sc_parameters['media']);	
		
		if($cat == '_icon')
		{
			$ret = "<div class='imgselector-container'  style='display:block;width:64px;min-height:64px'>";
			$thpath = isset($sc_parameters['nothumb']) || $hide ? $default : $default_thumb;
			$style = ($blank) ? "border:1px dashed black;width:64px;height:64px" : "border:1px dashed black";
			$label = "<img id='{$name_id}_prev' src='{$default_url}' alt='{$default_url}' class='image-selector' style='{$style}' />";
				
		}
		else
		{
			$ret = "<div class='imgselector-container'  style='display:block;width:120px;min-height:100px'>";
			$att = 'aw=120&ah=100';
			$thpath = isset($sc_parameters['nothumb']) || $hide ? $default : $tp->thumbUrl($default_thumb, $att, true);
			$label = "<img id='{$name_id}_prev' src='{$default_url}' alt='{$default_url}' class='image-selector' style='width:120px;height:100px;border:1px dashed black;' />";
			
		}
		
		
		
		$ret .= $this->mediaUrl($cat, $label,$name_id);
		$ret .= "</div>\n";
		$ret .=	"<input type='hidden' name='{$name}' id='{$name_id}' value='{$default}' />"; 
	//	$ret .=	$this->text($name,$default); // to be hidden eventually. 
		return $ret;
		

		
		
		
		// ----------------

	}



			
		
	function filepicker($name, $default, $label = '', $sc_parameters = '')
	{
		$tp = e107::getParser();
		$name_id = $this->name2id($name);
		if(is_string($sc_parameters))
		{
			if(strpos($sc_parameters, '=') === false) $sc_parameters = 'media='.$sc_parameters;
			parse_str($sc_parameters, $sc_parameters);
		}

		$cat 		= vartrue($sc_parameters['media']) ? $tp->toDB($sc_parameters['media']) : "_common_file";	
		
		$default_label 	= ($default) ? $default : "Choose a file";
		$label 		= "<span id='{$name_id}_prev' >".basename($default_label)."</span>";
			
		
	//	$ret .= $this->mediaUrl($cat, $label,$name_id,"mode=dialog&action=list");
			$ret .= $this->mediaUrl($cat, $label,$name_id,"mode=main&action=dialog");
		$ret .=	"<input type='hidden' name='{$name}' id='{$name_id}' value='{$default}' style='width:400px' />"; 
			
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
	 * 
	 * @url http://trentrichardson.com/examples/timepicker/
	 */
	function datepicker($name, $datestamp = false, $options = null)
	{
		if(vartrue($options) && is_string($options))
		{
			parse_str($options,$options);	
		} 
		
		$dateFormat = varset($options['dateformat']) ? trim($options['dateformat']) :e107::getPref('inputdate', '%Y-%m-%d');
		$timeFormat = varset($options['timeformat']) ? trim($options['timeformat']) :e107::getPref('inputtime', '%H:%M:%S'); 
						
		$type		= varset($options['type']) ? trim($options['type']) : "date"; // 'datetime'
		
	//	echo "TYPE=".$type;
			
		$ampm		= (preg_match("/%l|%I|%p|%P/",$timeFormat)) ? 'true' : 'false';
				
		$convert = array(
			'%d'	=> 'dd',
			'%m'	=> 'mm',
			'%y'	=> 'y',
			'%Y'	=> 'yy',
			
			'%a'	=> 'D', 	// An abbreviated textual representation of the day
			'%A'	=> 'DD', 	// A full textual representation of the day
			'%B'	=> 'MM', 	// Full month name, based on the locale
			'%b'	=> 'M', 	// Abbreviated month name, based on the locale
			'%h'	=> 'M', 	// Abbreviated month name, based on the locale (an alias of %b)
			
			'%l'	=> 'h',		// 12 hour format - no leading zero
			'%I'	=> 'hh',	// 12 hour format - leading zero
			'%H'	=> 'hh',	// 24 hour format - leading zero
			'%M'	=> 'mm',
			'%S'	=> 'ss',
			'%p'	=> 'TT',	//	%p	UPPER-CASE 'AM' or 'PM' based on the given time
			'%P'	=> 'tt',		// %P	lower-case 'am' or 'pm' based on the given time
			'%T' 	=> 'hh:mm:ss',
			'%r' 	=> "hh:mmm:ss TT" // 12 hour format
		
		);
			
		$s = array_keys($convert);
		$r = array_values($convert);
			
		$dformat = str_replace($s,$r,$dateFormat);
		$tformat = str_replace($s,$r,$timeFormat);
				
		$id = $this->name2id($name);
		
		$classes = array(
			'date'		=> 'e-date',
		//	'time'		=> 'e-time',
			'datetime'	=> 'e-datetime'
		);
		
		$def = array(
			'date'		=> $dateFormat,
		//	'time'		=> $timeFormat,
			'datetime'	=> $dateFormat." ".$timeFormat
		);
			
		$defdisp = (isset($def[$type])) ? $def[$type] : $def['date'];
		
		if ($datestamp)
		{
		   $value = is_numeric($datestamp) ? e107::getDate()->convert_date($datestamp, $defdisp) : $datestamp; //date("d/m/Y H:i:s", $datestamp);
		}

		$text = "";
	
		$class 		= (isset($classes[$type])) ? $classes[$type] : "tbox e-date";
		$size 		= vartrue($options['size']) ? intval($options['size']) : 40;
		$required 	= vartrue($options['required']) ? "required" : "";
		
		if(vartrue($options['inline']))
		{
			$text .= "<div class='{$class}' id='inline-{$id}' data-date-format='{$dformat}' data-time-format='{$tformat}' data-date-ampm='{$ampm}' ></div>
				<input  type='hidden' name='{$name}' id='{$id}' value='{$value}' data-date-format='{$dformat}' data-time-format='{$tformat}' data-date-ampm='{$ampm}' />
			";		
		}
		else
		{
			$text .= "<input class='{$class}' type='text' size='{$size}' name='{$name}' id='{$id}' value='{$value}' data-date-format='{$dformat}' data-time-format='{$tformat}' data-date-ampm='{$ampm}' {$required} />";		
		}
		
		return $text;
			// Keep this info here: 
			/*
				 * $options allowed keys:
	
		 * 
		 *   d - day of month (no leading zero)
		    dd - day of month (two digit)
		    o - day of the year (no leading zeros)
		    oo - day of the year (three digit)
		    D - day name short
		    DD - day name long
		    m - month of year (no leading zero)
		    mm - month of year (two digit)
		    M - month name short
		    MM - month name long
		    y - year (two digit)
		    yy - year (four digit)
		    @ - Unix timestamp (ms since 01/01/1970)
		     ! - Windows ticks (100ns since 01/01/0001)
		    '...' - literal text
		    '' - single quote
		    anything else - literal text 
		
		    ATOM - 'yy-mm-dd' (Same as RFC 3339/ISO 8601)
		    COOKIE - 'D, dd M yy'
		    ISO_8601 - 'yy-mm-dd'
		    RFC_822 - 'D, d M y' (See RFC 822)
		    RFC_850 - 'DD, dd-M-y' (See RFC 850)
		    RFC_1036 - 'D, d M y' (See RFC 1036)
		    RFC_1123 - 'D, d M yy' (See RFC 1123)
		    RFC_2822 - 'D, d M yy' (See RFC 2822)
		    RSS - 'D, d M y' (Same as RFC 822)
		    TICKS - '!'
		    TIMESTAMP - '@'
		    W3C - 'yy-mm-dd' (Same as ISO 8601)
		 * 
		 * h    Hour with no leading 0
		 * hh    Hour with leading 0
		 * m    Minute with no leading 0
		 * mm    Minute with leading 0
		 * s    Second with no leading 0
		 * ss    Second with leading 0
		 * l    Milliseconds always with leading 0
		 * t    a or p for AM/PM
		 * T    A or P for AM/PM
		 * tt    am or pm for AM/PM
		 * TT    AM or PM for AM/PM 
			
			*/
		
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
		if(!is_array($options)) parse_str($options, $options);

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
		//TODO - external JS
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

	}
	
	
	/**
	 * A 'Rating' element
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
		
		$options = $this->format_options('text', $name, $options);
		$options['class'] = vartrue($options['class'],'e-password');
		//never allow id in format name-value for text fields
		return "<input type='password' name='{$name}' value='{$value}' maxlength='{$maxlength}'".$this->get_attributes($options, $name)." />";
	}

	// autoexpand done
	function textarea($name, $value, $rows = 10, $cols = 80, $options = array(), $counter = false)
	{
		if(is_string($options)) parse_str($options, $options);
		// auto-height support
		if(!vartrue($options['noresize']))
		{
			$options['class'] = (isset($options['class']) && $options['class']) ? $options['class'].' e-autoheight' : 'tbox textarea e-autoheight';
		}

		$options = $this->format_options('textarea', $name, $options);
		//never allow id in format name-value for text fields
		return "<textarea name='{$name}' rows='{$rows}' cols='{$cols}'".$this->get_attributes($options, $name).">{$value}</textarea>".(false !== $counter ? $this->hidden('__'.$name.'autoheight_opt', $counter) : '');
	}

	/**
	 * Bbcode Area. Name, value, template, form-id, size, options array eg. counter
	 * IMPORTANT: $$mediaCat is also used is the media-manager category identifier
	 */
	function bbarea($name, $value, $template = '', $mediaCat='_common', $size = 'large', $options = array())
	{		
		//size - large|medium|small
		//width should be explicit set by current admin theme
		switch($size)
		{
			case 'small':
				$rows = '7';
				$height = "style='height:250px'"; // inline required for wysiwyg
			break;
						
			case 'medium':
				$rows = '10';
				$height = "style='height:375px'"; // inline required for wysiwyg
			break;

			case 'large':
			default:
				$rows = '15';
				$size = 'large';
				$height = "style='height:500px;width:1025px'"; // inline required for wysiwyg
			break;
		}

		// auto-height support
	   	$options['class'] 	= 'tbox bbarea '.($size ? ' '.$size : '').' e-wysiwyg e-autoheight';
		$bbbar 				= '';
		

		$help_tagid 		= $this->name2id($name)."--preview"; 
		$options['other'] 	= "onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);' {$height}";
	
		$counter 			= vartrue($options['counter'],false); 
		
		$ret = "
		<div class='bbarea {$size}'>
		<div class='field-spacer'><!-- --></div>\n";
	

		$ret .=	e107::getBB()->renderButtons($template,$help_tagid);
		$ret .=	$this->textarea($name, $value, $rows, 50, $options, $counter);
			
		$ret .= "</div>\n";
		
		$_SESSION['media_category'] = $mediaCat; // used by TinyMce. 
		
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
	* checkbox
	* parm $name
	* parm $value
	* parm $checked
	* @return void
	* @author
	*/
	function checkbox($name, $value, $checked = false, $options = array())
	{
		$options = $this->format_options('checkbox', $name, $options);
		$options['checked'] = $checked; //comes as separate argument just for convenience
		return "<input type='checkbox' name='{$name}' value='{$value}'".$this->get_attributes($options, $name, $value)." />";

	}

	function checkbox_label($label_title, $name, $value, $checked = false, $options = array())
	{
		return $this->checkbox($name, $value, $checked, $options).$this->label($label_title, $name, $value);
	}

	function checkbox_switch($name, $value, $checked = false, $label = '')
	{
		return $this->checkbox($name, $value, $checked).$this->label($label ? $label : LAN_ENABLED, $name, $value);
	}

	function checkbox_toggle($name, $selector = 'multitoggle', $id = false)
	{
		$selector = 'jstarget:'.$selector;
		if($id) $id = $this->name2id($id);
		return $this->checkbox($name, $selector, false, array('id' => $id,'class' => 'checkbox toggle-all'));
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

	function _uc_checkbox_cb($treename, $classnum, $current_value, $nest_level, $field_options)
	{
		if($classnum == e_UC_BLANK)
			return '';

		$tmp = explode(',', $current_value); //TODO add support for when $current_value is an array.

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

		return "<div class='field-spacer{$class}'{$style}>".$this->checkbox($treename.'[]', $classnum, in_array($classnum, $tmp), $field_options).$this->label($this->_uc->uc_get_classname($classnum).$descr, $treename.'[]', $classnum)."</div>\n";
	}


	function uc_label($classnum)
	{
		return $this->_uc->uc_get_classname($classnum);
	}

	function radio($name, $value, $checked = false, $options = array())
	{
		$options = $this->format_options('radio', $name, $options);
		$options['checked'] = $checked; //comes as separate argument just for convenience
		// $options['class'] = 'inline';	
		return "<input type='radio' name='{$name}' value='".$value."'".$this->get_attributes($options, $name, $value)." />";

	}

	function radio_switch($name, $checked_enabled = false, $label_enabled = '', $label_disabled = '',$options=array())
	{
		$options_on = varset($options['enabled'],array());
		$options_off = varset($options['disabled'],array());
		
		if($options['class'] == 'e-expandit') // See admin->prefs 'Single Login' for an example. 
		{
			$options_on = array('class' => 'e-expandit-on');
			$options_off = array('class' => 'e-expandit-off');			
		}
		
		if(vartrue($options['reverse'])) // reverse order. 
		{
			unset($options['reverse']);
			return $this->radio($name, 0, !$checked_enabled, $options_off)."".$this->label($label_disabled ? $label_disabled : LAN_DISABLED, $name, 0)."&nbsp;&nbsp;".
			$this->radio($name, 1, $checked_enabled, $options_on)."".$this->label($label_enabled ? $label_enabled : LAN_ENABLED, $name, 1);			
		}
		
		return $this->radio($name, 1, $checked_enabled, $options_on)."".$this->label($label_enabled ? $label_enabled : LAN_ENABLED, $name, 1)."&nbsp;&nbsp;
			".$this->radio($name, 0, !$checked_enabled, $options_off)."".$this->label($label_disabled ? $label_disabled : LAN_DISABLED, $name, 0);

	}


	/**
	 * @param string $name 
	 * @param array $elements = arrays value => label
	 * @param string/integer $checked = current value
	 * @param boolean $multi_line
	 * @param mixed $help array of field help items or string of field-help (to show on all)
	 */
	function radio_multi($name, $elements, $checked, $multi_line = false, $help = null)
	{
		$text = array();
		if(is_string($elements)) parse_str($elements, $elements);
		
		foreach ($elements as $value => $label)
		{
			$label = defset($label, $label);
			$helpLabel = (is_array($help)) ? vartrue($help[$value]) : $help;
			$text[] = $this->radio($name, $value, (string) $checked === (string) $value)."".$this->label($label, $name, $value).(isset($helpLabel) ? "<div class='field-help'>".$helpLabel."</div>" : '');
		}
		if(!$multi_line)
			return implode("&nbsp;&nbsp;", $text);

		return "<div class='field-spacer'>".implode("</div><div class='field-spacer'>", $text)."</div>";

	}

	function label($text, $name = '', $value = '')
	{
		$for_id = $this->_format_id('', $name, $value, 'for');
		return "<label$for_id class='e-tip '>{$text}</label>";
	}
	
	function help($text)
	{
		return !empty($text) ? '<div class="field-help">'.$text.'</div>' : '';
	}

	function select_open($name, $options = array())
	{
		$options = $this->format_options('select', $name, $options);
		if($options['multiple'])
		{
			$options['class'] .= ' e-multiselect';	
		}		
		return "<select name='{$name}'".$this->get_attributes($options, $name).">";
	}

	/**
	 *
	 * @param string $name
	 * @param array $option_array
	 * @param boolean $selected [optional]
	 * @param string|array $options [optional]
	 * @param boolean $defaultBlank [optional] set to TRUE if the first entry should be blank
	 * @return string HTML text for display
	 */
	function selectbox($name, $option_array, $selected = false, $options = array(), $defaultBlank= false)
	{
		if(!is_array($options)) parse_str($options, $options);

		if($option_array === 'yesno')
		{
			$option_array = array(1 => LAN_YES, 0 => LAN_NO);
		}

		if($options['multiple'])
		{
			$name = (strpos($name, '[') === false) ? $name.'[]' : $name;
			$selected = explode(",",$selected);
		}
		$text = $this->select_open($name, $options)."\n";

		if(isset($options['default']))
		{
			$text .= $this->option($options['default'], varset($options['defaultValue']));
		}
		elseif($defaultBlank)
		{
			$text .= $this->option('&nbsp;', '');
		}

		$text .= $this->option_multi($option_array, $selected)."\n".$this->select_close();
		return $text;
	}

	function uc_select($name, $current_value, $uc_options, $select_options = array(), $opt_options = array())
	{
		return $this->select_open($name, $select_options)."\n".$this->_uc->vetted_tree($name, array($this, '_uc_select_cb'), $current_value, $uc_options, $opt_options)."\n".$this->select_close();
	}

	// Callback for vetted_tree - Creates the option list for a selection box
	function _uc_select_cb($treename, $classnum, $current_value, $nest_level)
	{
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
		return $this->option($prefix.$this->_uc->uc_get_classname($classnum), $classnum, ($current_value !== '' && in_array($classnum, $tmp)), array("style"=>"{$style}"))."\n";
	}

	function optgroup_open($label, $disabled = false)
	{
		return "<optgroup class='optgroup' label='{$label}'".($disabled ? " disabled='disabled'" : '').">";
	}

	function option($option_title, $value, $selected = false, $options = array())
	{
		if(false === $value) $value = '';
		$options = $this->format_options('option', '', $options);
		$options['selected'] = $selected; //comes as separate argument just for convenience
		return "<option value='{$value}'".$this->get_attributes($options).">".defset($option_title, $option_title)."</option>";
	}

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
					$text .= $this->option($lab, $val, (is_array($selected) ? in_array($val, $selected) : $selected == $val), $options)."\n";
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
		$options = $this->format_options('submit_image', $name, $options);
		switch ($image)
		{
			case 'edit':
				$image = ADMIN_EDIT_ICON_PATH;
				$options['class'] = $options['class'] == 'action' ? 'action edit' : $options['class'];
			break;

			case 'delete':
				$image = ADMIN_DELETE_ICON_PATH;
				$options['class'] = $options['class'] == 'action' ? 'action delete' : $options['class'];
				$options['other'] = 'data-confirm="'.LAN_JSCONFIRM.'"';
			break;

			case 'execute':
				$image = ADMIN_EXECUTE_ICON_PATH;
				$options['class'] = $options['class'] == 'action' ? 'action execute' : $options['class'];
			break;

			case 'view':
				$image = ADMIN_VIEW_ICON_PATH;
				$options['class'] = $options['class'] == 'action' ? 'action view' : $options['class'];
			break;
		}
		$options['title'] = $title;//shorthand

		return "<input class='e-tip' data-placement='left' type='image' src='{$image}' name='{$name}' value='{$value}'".$this->get_attributes($options, $name, $value)." />";
	}

	/**
	 *
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
		$options['class'] = 'btn '.$action.' ';//shorthand
		if(empty($label)) $label = $value;
		
		switch ($action)
		{
			case 'update':
			case 'create':
			case 'import':
			case 'submit':
				$options['class'] .= 'btn-success';
			break;

			case 'delete':
				$options['class'] .= 'btn-danger';
				$options['other'] = 'data-confirm="'.LAN_JSCONFIRM.'"';
			break;

			case 'execute':
				$options['class'] .= 'btn-success';
			break;
			
			case 'other':
			case 'login':	
				$options['class'] .= 'btn-primary';
			break;	
			
			case 'warning':
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
					
				case 'pattern':
					if($optval) $ret .= " pattern='{$optval}'";
					break;

				case 'other':
					if($optval) $ret .= " $optval";
					break;
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
		return rtrim(str_replace(array('[]', '[', ']', '_', '/'), array('-', '-', '', '-', '-'), $name), '-');
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
	function format_options($type, $name, $user_options)
	{
		if(is_string($user_options)) parse_str($user_options, $user_options);

		$def_options = $this->_default_options($type);

		foreach (array_keys($user_options) as $key)
		{
			if(!isset($def_options[$key])) unset($user_options[$key]);//remove it?
		}

		$user_options['name'] = $name; //required for some of the automated tasks
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
			'other' 		=> ''
			//	'multiple' => false, - see case 'select'
		);

		switch ($type) {
			case 'hidden':
				$def_options = array('id' => false, 'disabled' => false, 'other' => '');
				break;

			case 'text':
				$def_options['class'] = 'tbox input-text';
				unset($def_options['selected'], $def_options['checked']);
				break;

			case 'file':
				$def_options['class'] = 'tbox file';
				unset($def_options['selected'], $def_options['checked']);
				break;

			case 'textarea':
				$def_options['class'] = 'tbox textarea';
				unset($def_options['selected'], $def_options['checked'], $def_options['size']);
				break;

			case 'select':
				$def_options['class'] = 'tbox select';
				$def_options['multiple'] = false;
				unset($def_options['checked']);
				break;

			case 'option':
				$def_options = array('class' => '', 'selected' => false, 'other' => '', 'disabled' => false, 'label' => '');
				break;

			case 'radio':
				$def_options['class'] = 'radio inline';
				unset($def_options['size'], $def_options['selected']);
				break;

			case 'checkbox':
				$def_options['class'] = 'checkbox';
				unset($def_options['size'],  $def_options['selected']);
				break;

			case 'submit':
				$def_options['class'] = 'button';
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
        $text = "
		<div class='col-selection-cont e-tip' data-placement='left' title='Select columns to display'>
			<a href='#".$id."' class='e-show-if-js e-expandit' >"
				."<img class='icon' src='".e_IMAGE_ABS."admin_images/select_columns_16.png' alt='select columns' />"
			."</a>
			<div id='".$id."' class='e-show-if-js e-hideme col-selection'><div class='col-selection-body'>
		";
        unset($columnsArray['options'], $columnsArray['checkboxes']);

		foreach($columnsArray as $key => $fld)
		{
			if (!varset($fld['forced']) && !vartrue($fld['nolist']) && vartrue($fld['type'])!='upload')
			{
				$checked = (in_array($key,$columnsDefault)) ?  TRUE : FALSE;
				$ttl = isset($fld['title']) ? defset($fld['title'], $fld['title']) : $key;
				$text .= "
					<div class='field-spacer'>
						".$this->checkbox_label($ttl, 'e-columns[]', $key, $checked)."
					</div>
				";
			}
		}

		// has issues with the checkboxes.
        $text .= "
				<div id='{$id}-button' class='right'>
					".$this->admin_button('etrigger_ecolumns', LAN_SAVE, 'other')."
				</div>
			</div></div>
		</div>
		";

		$text .= "";
		return $text;
	}

	function colGroup($fieldarray, $columnPref = '')
	{
        $text = "";
        $count = 0;
		foreach($fieldarray as $key=>$val)
		{
			if ((in_array($key, $columnPref) || $key=='options' || varsettrue($val['forced'])) && !vartrue($val['nolist']))
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

                if($querypattern!="" && !varsettrue($val['nosort']) && $key != "options" && $key != "checkboxes")
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
	  		<tr>".$text."</tr>
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
			// shouldn't happen...
			if(!isset($fieldvalues[$field]) && $data['alias'])
			{
				$fieldvalues[$data['alias']] = $fieldvalues[$data['field']];
				$field = $data['alias'];
			}
			
			//Not found
			if((!varset($data['forced']) && !in_array($field, $currentlist)) || varset($data['nolist']))
			{
				continue;
			}
			elseif($data['type'] != 'method' && !$data['forced'] && !isset($fieldvalues[$field]) && $fieldvalues[$field] !== NULL)
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
			// there is no other way for now - prepare user data
			if('user' == $data['type']/* && isset($data['readParms']['idField'])*/)
			{
				if(is_string($data['readParms'])) parse_str($data['readParms'], $data['readParms']);
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
	
		$tp = e107::getParser();
		switch($field) // special fields
		{
			case 'options':
				
				if($attributes['type']=='method') // Allow override with 'options' function.
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
					
					$value = "";
					
					if(vartrue($parms['sort']))//FIXME use a global variable such as $fieldpref
					{
						$value .= "<a class='e-sort' style='cursor:move' href='".e_SELF."?".(e_QUERY ? e_QUERY."&amp;ajax_used=1" : "ajax_used=1")."' title='Re-order'>".ADMIN_SORT_ICON."</a> ";	
					}		
			
					$value .= "<a href='".e_SELF."?{$query}' class='e-tip' title='".LAN_EDIT."' data-placement='left'>
					<img class='icon action edit list' src='".ADMIN_EDIT_ICON_PATH."' alt='".LAN_EDIT."' /></a>";

					$delcls = vartrue($attributes['noConfirm']) ? ' no-confirm' : '';
					if(varset($parms['deleteClass']))
					{
						$cls = (deftrue($parms['deleteClass'])) ? constant($parms['deleteClass']) : $parms['deleteClass'];
						if(check_class($cls))
						{
							$value .= $this->submit_image('etrigger_delete['.$id.']', $id, 'delete', LAN_DELETE.' [ ID: '.$id.' ]', array('class' => 'action delete'.$delcls));
						}
					}
					else
					{
						$value .= $this->submit_image('etrigger_delete['.$id.']', $id, 'delete', LAN_DELETE.' [ ID: '.$id.' ]', array('class' => 'action delete'.$delcls));
					}
				}
				//$attributes['type'] = 'text';
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
							
			//	$value = $pre.vartrue($tmp[$value]).$post; // FIXME "Fatal error: Only variables can be passed by reference" featurebox list page. 
			break;

			case 'dropdown':
				// XXX - should we use readParams at all here? see writeParms check below
				
				if($parms && is_array($parms)) // FIXME - add support for multi-level arrays (option groups)
				{
					$value = vartrue($parms['pre']).vartrue($parms[$value]).vartrue($parms['post']);
					break;
				}

				// NEW - multiple (array values) support
				// FIXME - add support for multi-level arrays (option groups)
				if(!is_array($attributes['writeParms'])) parse_str($attributes['writeParms'], $attributes['writeParms']);
				$wparms = $attributes['writeParms'];
				if(!is_array(varset($wparms['__options']))) parse_str($wparms['__options'], $wparms['__options']);

				$opts = $wparms['__options'];
				unset($wparms['__options']);

				if($opts['multiple'] || $attributes['data']=='comma')
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
				
				// return ;
			break;

			case 'radio':
				if($parms && is_array($parms)) // FIXME - add support for multi-level arrays (option groups)
				{
					$value = vartrue($parms['pre']).vartrue($parms[$value]).vartrue($parms['post']);
					break;
				}

				if(!is_array($attributes['writeParms'])) parse_str($attributes['writeParms'], $attributes['writeParms']);
				$value = vartrue($parms['pre']).vartrue($parms[$value]).vartrue($parms['post']);
			break;

			case 'text':
				
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
				if(vartrue($parms['link']) && $id && is_numeric($id) ) 
				{
					$link = str_replace('[id]',$id,$parms['link']);
					$link = $tp->replaceConstants($link); // SEF URL is not important since we're in admin.
					$dialog = vartrue($parms['dialog']) ? "e-dialog" : "";
					$value = "<a class='e-tip {$dialog}' href='".$link."' title='Quick View'>".$value."</a>";
				}
				

				$value = vartrue($parms['pre']).$value.vartrue($parms['post']);
			break;

			case 'bbarea':
			case 'textarea':
				$expand = '...';
				$toexpand = false;
				if($attributes['type'] == 'bbarea' && !isset($parms['bb'])) $parms['bb'] = true; //force bb parsing for bbareas
				$elid = trim(str_replace('_', '-', $field)).'-'.$id;
				if(!vartrue($parms['noparse'])) $value = $tp->toHTML($value, (vartrue($parms['bb']) ? true : false), vartrue($parms['parse']));
				if(vartrue($parms['expand']) || vartrue($parms['truncate']) || vartrue($parms['htmltruncate']))
				{
					$ttl = vartrue($parms['expand'], '&nbsp;...');
					$expand = '&nbsp;<a href="#'.$elid.'-expand" class="e-show-if-js e-expandit">'.defset($ttl, $ttl)."</a>";
				}

				$oldval = $value;
				if(vartrue($parms['truncate']))
				{
					$value = $oldval = strip_tags($value);
					$value = $tp->text_truncate($value, $parms['truncate'], $expand);
					$truncated = str_replace($expand,'',$value);
					$toexpand = $value != $oldval;
				}
				elseif(vartrue($parms['htmltruncate']))
				{
					$value = $tp->html_truncate($value, $parms['htmltruncate'], $expand);
					$toexpand = $value != $oldval;
				}
				if($toexpand)
				{
					// force hide! TODO - core style .expand-c (expand container)
					$value .= '<div class="expand-c" style="display: none" id="'.$elid.'-expand"><div>'.str_replace($truncated,' ',$oldval).'</div></div>';
				}
			break;

			case 'icon':
				$value = '<img src="'.$tp->replaceConstants(vartrue($parms['pre']).$value, 'abs').'" alt="'.basename($value).'" class="icon'.(vartrue($parms['class']) ? ' '.$parms['class'] : '').'" />';
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
				
					if(!preg_match("/[a-zA-z0-9_-\s\(\)]+\.(png|jpg|jpeg|gif|PNG|JPG|JPEG|GIF)$/",$value))
					{
						$icon = "{e_IMAGE}filemanager/zip_32.png";	
						$src = $tp->replaceConstants(vartrue($parms['pre']).$icon, 'abs');
						return '<img src="'.$src.'" alt="'.$value.'" class="e-thumb" title="'.$value.'" />';
					}
					
					if(vartrue($parms['thumb']))
					{
						$src = $tp->replaceConstants(vartrue($parms['pre']).$value, 'abs');
						$thumb = $parms['thumb'];
						$thparms = array();
						if(is_numeric($thumb) && '1' != $thumb)
						{
							$thparms['w'] = intval($thumb);
						}
						elseif(vartrue($parms['thumb_aw']))
						{
							$thparms['aw'] = intval($parms['thumb_aw']);
						}
						$thsrc = $tp->thumbUrl(vartrue($parms['pre']).$value, $thparms, varset($parms['thumb_urlraw']));
						$alt = $src;
						$ttl = '<img src="'.$thsrc.'" alt="'.$alt.'" class="e-thumb" />';
						$value = '<a href="'.$src.'" class="e-dialog e-image-preview" title="'.$alt.'" rel="external">'.$ttl.'</a>';
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

			case 'datestamp':
				$value = $value ? e107::getDate()->convert_date($value, vartrue($parms['mask'], 'short')) : '';
			break;
			
			case 'date':
				// just show original value
			break;

			case 'userclass':
				$value = $this->_uc->uc_get_classname($value);
			break;

			case 'userclasses':
				$classes = explode(',', $value);
				$value = array();
				foreach ($classes as $cid)
				{
					$value[] = $this->_uc->uc_get_classname($cid);
				}
				$value = implode(vartrue($parms['separator'],"<br />"), $value);
			break;

			/*case 'user_name':
			case 'user_loginname':
			case 'user_login':
			case 'user_customtitle':
			case 'user_email':*/
			case 'user':
				/*if(is_numeric($value))
				{
					$value = get_user_data($value);
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
				$ttl = '';

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


				if(vartrue($parms['link']) && $id && $ttl && is_numeric($id))
				{
					$value = '<a href="'.e107::getUrl()->create('user/profile/view', array('id' => $id, 'name' => $ttl)).'" title="Go to user profile">'.$ttl.'</a>';
				}
				else
				{
					$value = $ttl;
				}
			break;

			case 'bool':
			case 'boolean':
				$false = vartrue($parms['trueonly']) ? "" : ADMIN_FALSE_ICON;
				
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
				$value = call_user_func_array(array($this, $method), array($value, 'read', $parms));
			break;

			case 'hidden':
				return (vartrue($parms['show']) ? ($value ? $value : vartrue($parms['empty'])) : '');
			break;

			case 'lanlist':
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
	function renderElement($key, $value, $attributes, $required_data = array())
	{

		$parms = vartrue($attributes['writeParms'], array());
		$tp = e107::getParser();

		if(is_string($parms)) parse_str($parms, $parms);

		// Two modes of read-only. 1 = read-only, but only when there is a value, 2 = read-only regardless.
		if(vartrue($attributes['readonly']) && (vartrue($value) || vartrue($attributes['readonly'])==2)) // quick fix (maybe 'noedit'=>'readonly'?)
		{
			if(vartrue($attributes['writeParms'])) // eg. different size thumbnail on the edit page. 
			{
				$attributes['readParms'] = $attributes['writeParms'];
			}
			return $this->renderValue($key, $value, $attributes).$this->hidden($key, $value); //
		}

		switch($attributes['type'])
		{
			case 'number':
				$maxlength = vartrue($parms['maxlength'], 255);
				unset($parms['maxlength']);
				if(!vartrue($parms['size'])) $parms['size'] = 15;
				if(!vartrue($parms['class'])) $parms['class'] = 'tbox number e-spinner';
				if(!$value) $value = '0';
				$ret =  vartrue($parms['pre']).$this->text($key, $value, $maxlength, $parms).vartrue($parms['post']);
			break;

			case 'ip':
				$ret =  $this->text($key, e107::getIPHandler()->ipDecode($value), 32, $parms);
			break;

			case 'url':
			case 'email':
			case 'text':
			case 'password': // encrypts to md5 when saved. 
				$maxlength = vartrue($parms['maxlength'], 255);
				unset($parms['maxlength']);
				$ret =  vartrue($parms['pre']).$this->text($key, $value, $maxlength, vartrue($parms['__options'])).vartrue($parms['post']);
			break;

			case 'textarea':
				$text = "";
				if($parms['append']) // similar to comments - TODO TBD. a 'comment' field type may be better.
				{
					$attributes['readParms'] = 'bb=1';
					$text = $this->renderValue($key, $value, $attributes).$this->hidden($key, $value).'<br />';
					$value = "";
				}

				$text .= $this->textarea($key, $value, vartrue($parms['rows'], 5), vartrue($parms['cols'], 40), vartrue($parms['__options']), varset($parms['counter'], false));
				$ret =  $text;
			break;

			case 'bbarea':
				$options = array('counter' => varset($parms['counter'], false)); 
				$ret =  $this->bbarea($key, $value, vartrue($parms['template']), vartrue($parms['helptag']), vartrue($parms['size'], 'medium'),$options );
			break;

			case 'image': //TODO - thumb, image list shortcode, js tooltip...
				$label = varset($parms['label'], 'LAN_EDIT');
				unset($parms['label']);
				$ret =  $this->imagepicker($key, $value, defset($label, $label), $parms);
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

				if(vartrue($parms['hidden']))
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
				$ret =  (vartrue($parms['raw']) ? $layouts[0] : $this->radio_multi($key, $layouts[0], $value, true, $info));
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

			case 'dropdown':
				
				$eloptions  = vartrue($parms['__options'], array());
				if(is_string($eloptions)) parse_str($eloptions, $eloptions);
				unset($parms['__options']);
				if(vartrue($eloptions['multiple']) && !is_array($value)) $value = explode(',', $value);
				$ret =  vartrue($eloptions['pre']).$this->selectbox($key, $parms, $value, $eloptions).vartrue($eloptions['post']);
			break;

			case 'radio':
				// TODO - more options (multi-line, help)
				/*$eloptions  = vartrue($parms['__options'], array());
				if(is_string($eloptions)) parse_str($eloptions, $eloptions);
				unset($parms['__options']);*/
				$ret =  vartrue($eloptions['pre']).$this->radio_multi($key, $parms, $value, false).vartrue($eloptions['post']);
			break;

			case 'userclass':
			case 'userclasses':
				$uc_options = vartrue($parms['classlist'], 'public,guest,nobody,member,admin,main,classes'); // defaults to 'public,guest,nobody,member,classes' (userclass handler)
				unset($parms['classlist']);
				$method = ($attributes['type'] == 'userclass') ? 'uc_select' : 'uc_select';
				if($atrributes['type'] == 'userclasses'){ $parms['multiple'] = true; }
				$ret =  $this->$method($key, $value, $uc_options, vartrue($parms, array()));
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

				if((empty($value) && vartrue($parms['currentInit'])) || vartrue($parms['current']))
				{
					$value = USERID;
					if(vartrue($parms['current']))
					{
						$parms['__options']['readonly'] = true;
					}
				}

				if(!is_array($value))
				{
					$value = $value ? e107::getSystemUser($value, true)->getUserData() : array();// get_user_data($value);
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
				$lenabled = vartrue($parms['enabled'], 'LAN_ENABLED');
				$ldisabled = vartrue($parms['disabled'], 'LAN_DISABLED');
				unset($parms['enabled'], $parms['disabled']);
				$ret =  $this->radio_switch($key, $value, defset($lenabled, $lenabled), defset($ldisabled, $ldisabled),$parms);
			break;

			case 'method': // Custom Function
				$ret =  call_user_func_array(array($this, $key), array($value, 'write', $parms));
			break;

			case 'upload': //TODO - from method
				// TODO uploadfile SC is now processing uploads as well (add it to admin UI), write/readParms have to be added (see uploadfile.php parms)
				$disbut = varset($parms['disable_button'], '0');
				$ret =  $tp->parseTemplate("{UPLOADFILE=".(vartrue($parms['path']) ? e107::getParser()->replaceConstants($parms['path']) : e_UPLOAD)."|nowarn&trigger=etrigger_uploadfiles&disable_button={$disbut}}");
			break;

			case 'hidden':
				$ret = (vartrue($parms['show']) ? ($value ? $value : varset($parms['empty'], $value)) : '');
				$value = (vartrue($parms['value'])) ? $parms['value'] : $value;
				echo "key=".$key."<br />value=".$value;
				$ret =  $ret.$this->hidden($key, $value);
			break;

			case 'lanlist':
			case 'language':
				$options = e107::getLanguage()->getLanSelectArray();

				$eloptions  = vartrue($parms['__options'], array());
				if(!is_array($eloptions)) parse_str($eloptions, $eloptions);
				unset($parms['__options']);
				if(vartrue($eloptions['multiple']) && !is_array($value)) $value = explode(',', $value);
				$ret =  vartrue($eloptions['pre']).$this->selectbox($key, $options, $value, $eloptions).vartrue($eloptions['post']);
			break;

			default:
				$ret =  $value;
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
			$query = isset($options['query']) ? $options['query'] : e_QUERY ;
			if($_GET['action'] == 'list')
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
						<table class='table adminlist' id='{$elid}-list-table'>
							".$this->colGroup($fields, $current_fields)."
							".$this->thead($fields, $current_fields, varset($options['head_query']), varset($options['query']))."
							<tbody id='e-sort'>
			";

			if(!$tree)
			{
				$text .= "
								<tr>
									<td colspan='".count($current_fields)."' class='center middle'>".LAN_NO_RECORDS."</td>
								</tr>
				";
			}
			else
			{

				foreach($tree as $model)
				{
					e107::setRegistry('core/adminUI/currentListModel', $model);
					$text .= $this->renderTableRow($fields, $current_fields, $model->getData(), $options['pid']);
				}
				e107::setRegistry('core/adminUI/currentListModel', null);
			}

			$text .= "
							</tbody>
						</table>
						".vartrue($options['table_post'])."
			";


			if($tree && $amount)
			{
				// New nextprev SC parameters
				$parms = 'total='.$total;
				$parms .= '&amount='.$amount;
				$parms .= '&current='.$from;
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
	 * 		'tabs' => true, // TODO - NOT IMPLEMENTED YET - enable tabs (only if fieldset count is > 1)
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

			$text .= "
				<form method='post' action='".$url."' id='{$form['id']}-form' enctype='multipart/form-data'>
				<div>
				".vartrue($form['header'])."
				".$this->token()."
			";

			foreach ($form['fieldsets'] as $elid => $data)
			{
				$elid = $form['id'].'-'.$elid;
				$text .= $this->renderCreateFieldset($elid, $data, $model);
			}

			$text .= "
			".vartrue($form['footer'])."
			</div>
			</form>
			";
			
			e107::js('footer-inline',"Form.focusFirstElement('{$form['id']}-form');",'prototype');
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
	 */
	function renderCreateFieldset($id, $fdata, $model)
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
			// convert aliases - not supported in edit mod
			if($att['alias'] && !$model->hasData($key))
			{
				$key = $att['field'];
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

			// type null - system (special) fields
			if($att['type'] !== null && !vartrue($att['noedit']) && $key != $model->getFieldIdName())
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
				$text .= "
					<tr>
						<td>
							".$required."<span{$required_class}>".defset($att['title'], $att['title'])."</span>".$label."
						</td>
						<td>
							".$this->renderElement($keyName, $model->getIfPosted($valPath), $att, varset($model_required[$key], array()))."
							{$help}
						</td>
					</tr>
				";
			}
			//if($bckp) $model->remove($bckp);

		}

		if($required_help)
		{
			$required_help = '<div class="form-note">'.$this->getRequiredString().' - required fields</div>'; //TODO - lans
		}

		$text .= "
					</tbody>
				</table>
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
						$text .= '
							<div class="options">
								After submit: '.$this->radio_multi('__after_submit_action', $submitopt, $selected, false).'
							</div>
						';
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
						$text .= $this->admin_button('etrigger_'.$trigger, $tdata[0], $tdata[1]);
						if(isset($tdata[2]))
						{
							$text .= $this->hidden($trigger.'_value', $tdata[2]);
						}
					}

		$text .= "
				</div>
			</fieldset>
			".vartrue($fdata['fieldset_post'])."
		";
		return $text;
	}

	// JUST A DRAFT - generic renderForm solution
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

	// JUST A DRAFT - generic renderFieldset solution, will be split to renderTable, renderCol/Row/Box etc
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

	// The 2 functions below are for demonstration purposes only, and may be moved/modified before release.
	function filterType($fieldarray)
	{
		return " frm-> filterType() is Deprecated &nbsp;&nbsp;  ";
	}

	function filterValue($type = '', $fields = '')
	{
		return " frm-> filterValue() is Deprecated.&nbsp;&nbsp;   ";
	}

	/**
	 * Generates a batch options select component
	 * This component is generally associated with a table of items where one or more rows in the table can be selected (using checkboxes).
	 * The list options determine some processing that wil lbe applied to all checked rows when the form is submitted.
	 *
	 * @param array $options associative array of option elements, keyed on the option value
	 * @param array ucOptions [optional] associative array of userclass option groups to display, keyed on the option value prefix
	 * @return string the HTML for the form component
	 */
	function batchoptions($options, $ucOptions = null)
	{
		$text = "
         <div class='f-left btn-group'>
         <img src='".e_IMAGE_ABS."generic/branchbottom.gif' alt='' class='icon action' />
			".$this->select_open('execute_batch', array('class' => 'tbox select batch e-autosubmit', 'id' => false))."
				".$this->option('With selected...', '')."
		";


		//used for getperms() check
		$permissions = vartrue($options['__permissions'], array());
		//used for check_classs() check
		$classes = vartrue($options['__check_class'], array());
		unset($options['__permissions'], $options['__check_class']);

		foreach ($options as $key => $val)
		{
			if(isset($permissions[$key]) && !getperms($permissions[$key]))
			{
				continue;
			}
			$disabled = false;
			if(isset($classes[$key]) && !is_array($classes[$key]) && !check_class($classes[$key]))
			{
				$disabled = true;
			}
			if(!is_array($val))
			{
				if($disabled) $val = $val.' ('.LAN_NOPERMISSION.')';
				$text .= "\t".$this->option('&nbsp;&nbsp;&nbsp;&nbsp;'.$val, $key, false, array('disabled' => $disabled))."\n";
			}
			else
			{
				if($disabled) $val[0] = $val[0].' ('.LAN_NOPERMISSION.')';

				$text .= "\t".$this->optgroup_open($val[0], $disabled)."\n";
		      	foreach ($val[1] as $k => $v)
		      	{
		      		$disabled = false;
					if(isset($classes[$key][$k]) && !check_class($classes[$key][$k]))
					{
						$disabled = true;
						$v = $v.' ('.LAN_NOPERMISSION.')';
					}
					$text .= "\t\t".$this->option($v, $key.'_selected_'.$k, false, array('disabled' => $disabled))."\n";
		      	}
		      	$text .= $this->optgroup_close()."\n";

			}
		}


		if ($ucOptions) // Userclass List.
		{
	   		foreach ($ucOptions as $ucKey => $ucVal)
			{
				$text .= "\t".$this->optgroup_open($ucVal[0])."\n";
	      		foreach ($ucVal[1] as $key => $val)
	      		{
	      			$text .= "\t\t".$this->option($val['userclass_name']['userclass_name'], $ucKey.'_selected_'.$val['userclass_name']['userclass_id'])."\n";
	      		}
	      		$text .= $this->optgroup_close()."\n";
			}
		}


		$text .= "
				".$this->select_close().$this->admin_button('trigger_execute_batch', 'trigger_execute_batch', 'submit multi e-hide-if-js', 'Go')."
			</div><div class='clear'></div>
		";

		return $text;
	}
}

class form 
{
	function form_open($form_method, $form_action, $form_name = "", $form_target = "", $form_enctype = "", $form_js = "") 
	{
		$method = ($form_method ? "method='".$form_method."'" : "");
		$target = ($form_target ? " target='".$form_target."'" : "");
		$name = ($form_name ? " id='".$form_name."' " : " id='myform'");
		return "\n<form action='".$form_action."' ".$method.$target.$name.$form_enctype.$form_js."><div>".e107::getForm()->token()."</div>";
	}

	function form_text($form_name, $form_size, $form_value, $form_maxlength = FALSE, $form_class = "tbox", $form_readonly = "", $form_tooltip = "", $form_js = "") {
		$name = ($form_name ? " id='".$form_name."' name='".$form_name."'" : "");
		$value = (isset($form_value) ? " value='".$form_value."'" : "");
		$size = ($form_size ? " size='".$form_size."'" : "");
		$maxlength = ($form_maxlength ? " maxlength='".$form_maxlength."'" : "");
		$readonly = ($form_readonly ? " readonly='readonly'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		return "\n<input class='".$form_class."' type='text' ".$name.$value.$size.$maxlength.$readonly.$tooltip.$form_js." />";
	}

	function form_password($form_name, $form_size, $form_value, $form_maxlength = FALSE, $form_class = "tbox", $form_readonly = "", $form_tooltip = "", $form_js = "") {
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
		return "\n<input class='button' type='".$form_type."' ".$form_js." value='".$form_value."'".$name.$image.$tooltip." />";
	}

	function form_textarea($form_name, $form_columns, $form_rows, $form_value, $form_js = "", $form_style = "", $form_wrap = "", $form_readonly = "", $form_tooltip = "") {
		$name = ($form_name ? " id='".$form_name."' name='".$form_name."'" : "");
		$readonly = ($form_readonly ? " readonly='readonly'" : "");
		$tooltip = ($form_tooltip ? " title='".$form_tooltip."'" : "");
		$wrap = ($form_wrap ? " wrap='".$form_wrap."'" : "");
		$style = ($form_style ? " style='".$form_style."'" : "");
		return "\n<textarea class='tbox' cols='".$form_columns."' rows='".$form_rows."' ".$name.$form_js.$style.$wrap.$readonly.$tooltip.">".$form_value."</textarea>";
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
		return "\n<select id='".$form_name."' name='".$form_name."' class='tbox' ".$form_js." >";
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