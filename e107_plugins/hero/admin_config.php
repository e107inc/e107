<?php

// Generated e107 Plugin Admin Area 

require_once(__DIR__.'/../../class2.php');
if (!getperms('P')) 
{
	e107::redirect('admin');
	exit;
}

//e107::lan('hero',true);
e107::lan('hero',true, true);

e107::css('hero', 'css/bootstrap-iconpicker.min.css');





class hero_adminArea extends e_admin_dispatcher
{

	protected $modes = array(	
	
		'main'	=> array(
			'controller' 	=> 'hero_ui',
			'path' 			=> null,
			'ui' 			=> 'hero_form_ui',
			'uipath' 		=> null
		),
		

	);	
	
	
	protected $adminMenu = array(

		'main/list'			=> array('caption'=> LAN_MANAGE, 'perm' => 'P'),
		'main/create'		=> array('caption'=> LAN_CREATE, 'perm' => 'P'),
			
		'main/prefs' 		=> array('caption'=> LAN_PREFS, 'perm' => 'P'),	

		// 'main/custom'		=> array('caption'=> 'Custom Page', 'perm' => 'P')
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'Hero';
}




				
class hero_ui extends e_admin_ui
{

		protected $pluginTitle		= 'Hero';
		protected $pluginName		= 'hero';

		protected $table			= 'hero';
		protected $pid				= 'hero_id';
		protected $perPage			= 10; 
		protected $batchDelete		= true;
		protected $batchExport      = true;
		protected $batchCopy		= true;

		protected $sortField		= 'hero_order';
	//	protected $sortParent      = 'somefield_parent';
	//	protected $treePrefix      = 'somefield_title';

		protected $tabs				= array(LAN_GENERAL, LAN_ADVANCED); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.

	//	protected $listQry      	= "SELECT * FROM `#tableName` WHERE field != '' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.

		protected $listOrder		= 'hero_order';

		protected $fields 		= array (
		   'checkboxes'         =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
		  'hero_id'           =>   array ( 'title' => LAN_ID, 'type' => null, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'hero_media'       => array('title'=> LAN_HERO_ADMIN_004, 'type'=>'image', 'noedit'=>true, 'data'=>'str', 'readParms'=>array('thumb'=>'100x80'), 'writeParms'=>array('media'=>'hero^',  'video' => 1)),
          'hero_bg'         => array('title'=> LAN_HERO_ADMIN_003, 'type'=>'image', 'noedit'=>true, 'data'=>'str', 'readParms'=>array('thumb'=>'100x80'), 'writeParms'=>array('media'=>'hero^')),

		  'hero_title'        =>   array ( 'title' => LAN_TITLE, 'type' => 'text', 'data' => 'str', 'width' => '18%', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => array('size'=>'block-level'), 'class' => 'left', 'thclass' => 'left',  ),
		  'hero_description'  =>   array ( 'title' => LAN_DESCRIPTION, 'type' => 'text', 'data' => 'str', 'width' => '30%', 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => array('size'=>'block-level'), 'class' => 'left', 'thclass' => 'left',  ),
		  'hero_bullets'      =>   array ( 'title' => LAN_ICON, 'type' => 'method', 'data' => 'json', 'width' => '35%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'hero_images'        => array('title'=> LAN_HERO_ADMIN_005, 'type'=>'method', 'data'=>false, 'nolist'=>true),
		  'hero_button1'      =>   array ( 'title' => LAN_HERO_ADMIN_015.'-1', 'type' => 'method', 'data' => 'json', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		  'hero_button2'      =>   array ( 'title' => LAN_HERO_ADMIN_015.'-2', 'type' => 'method', 'data' => 'json', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		   'hero_order'      =>   array ( 'title' => LAN_ORDER, 'tab'=>1 , 'type' => 'number', 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
	       'hero_class'      =>   array ( 'title' => LAN_VISIBILITY, 'tab'=>1, 'type' => 'userclass', 'data' => 'int', 'inline'=>true, 'width' => '10%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),

	        'options'             =>   array ( 'title' => LAN_OPTIONS, 'type' => null, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1',  ),
		);		

		protected $fieldpref = array('hero_media', 'hero_bg', 'hero_title', 'hero_description', 'hero_bullets', 'hero_button1');


	//	protected $preftabs        = array('General', 'Other' );
		protected $prefs = array(
			'visibility'		=> array('title'=> LAN_VISIBILITY, 'tab'=>0, 'type'=>'userclass', 'data' => 'str', 'help'=>'', 'writeParms'=>array('classlist' => 'public,member,guest,admin,main,classes,nobody, no-excludes')),
			'icon_pack'		=> array('title'=> LAN_HERO_ADMIN_009, 'tab'=>0, 'type'=>'method', 'data' => 'str', 'writeParms'=>array(),'help'=>''),
			'slide_interval'    => array('title'=>LAN_HERO_ADMIN_011, 'type'=>'dropdown', 'data'=>'int', 'writeParms'=>array('optArray'=>array())),
		);


		public function init()
		{
			// Set drop-down values (if any).

			$tp = e107::getParser();
			$r = range(1000,10000,1000);

			$opts = array(
				'false' => LAN_DISABLED
			);


			foreach($r as $v)
			{
				$x = str_replace('000', '', $v);
				$opts[$v] = $tp->lanVars(LAN_HERO_ADMIN_016, $x);
			}

			$this->prefs['slide_interval']['writeParms']['optArray'] = $opts;

		}
/*
		function EditObserver()
		{
			$this->fields['hero_media']['noedit'] = true;
			$this->fields['hero_bg']['noedit'] = true;

			$this->fields['hero_images'] = array('title'=> "Image/Video", 'type'=>'method', 'data'=>'str');

			parent::EditObserver();


		//		  'hero_media'       => array('title'=> "Image/Video", 'type'=>'image', 'data'=>'str', 'readParms'=>array('thumb'=>'100x80'), 'writeParms'=>array('media'=>'hero^',  'video' => 1)),
      //    'hero_bg'       => array('title'=> "Background", 'type'=>'image', 'data'=>'str', 'readParms'=>array('thumb'=>'100x80'), 'writeParms'=>array('media'=>'hero^')),



		}
		*/
		/** @var string[] the fields that hold a button, its address among its parts */
		private $buttons = array('hero_button1', 'hero_button2');

		/**
		 * @param array $slide a slide as posted, or as stored with its buttons decoded
		 * @return array each button's address, under the button's field
		 */
		private function buttonUrls($slide)
		{
			$urls = array();

			foreach($this->buttons as $field)
			{
				if(isset($slide[$field]) && is_array($slide[$field]) && isset($slide[$field]['url']))
				{
					$urls[$field] = $slide[$field]['url'];
				}
			}

			return $urls;
		}

		/**
		 * @param array $stored the slide as stored, its buttons as JSON
		 * @return array each button's address as stored
		 */
		private function storedButtonUrls($stored)
		{
			foreach($this->buttons as $field)
			{
				$stored[$field] = e107::unserialize(varset($stored[$field]));
			}

			return $this->buttonUrls($stored);
		}

		// ------- Customize Create --------

		public function beforeCreate($new_data,$old_data)
		{
			if(!$this->maySaveLinkAddresses($this->buttons, $this->buttonUrls($new_data), $this->storedButtonUrls($old_data)))
			{
				return false;
			}

			$new_data = $this->processGlyph($new_data, $old_data);

			return $new_data;
		}

		public function afterCreate($new_data, $old_data, $id)
		{
			// do something
		}

		public function onCreateError($new_data, $old_data)
		{
			// do something		
		}		


		// ------- Customize Update --------

		public function beforeUpdate($new_data, $old_data, $id)
		{
			return $this->beforeCreate($new_data, $old_data);
		}

		private function processGlyph($new_data, $old_data)
		{
			$trust = $this->authorTrust();

			foreach(array('hero_button1', 'hero_button2') as $field)
			{
				if(isset($new_data[$field]) && is_array($new_data[$field]))
				{
					$new_data[$field] = $this->storedPart($new_data[$field], $this->storedValue($old_data, $field), $trust, false);
				}
			}

			$bullets = $this->storedValue($old_data, 'hero_bullets');

			if(isset($new_data['hero_bullets']) && is_array($new_data['hero_bullets']))
			{
				foreach($new_data['hero_bullets'] as $key => $row)
				{
					$stored = (isset($bullets[$key]) && is_array($bullets[$key])) ? $bullets[$key] : array();
					$new_data['hero_bullets'][$key] = is_array($row) ? $this->storedPart($row, $stored, $trust, true) : array();
				}
			}

			return $new_data;

		}

		/**
		 * @param array $part one bullet or button as posted
		 * @param array $stored the same part as stored, empty for a new one
		 * @param int $trust what the current user may post, as {@see hero_ui::authorTrust()} says
		 * @param bool $glyph whether a changed icon is stored as a glyph, as a bullet's is
		 * @return array the part as stored: each value posted back untouched kept with its level, any other judged at $trust
		 */
		private function storedPart(array $part, array $stored, $trust, $glyph)
		{
			$tp = e107::getParser();
			$form = $this->getUI();
			$levels = (isset($stored['__trust__']) && is_array($stored['__trust__'])) ? $stored['__trust__'] : array();
			$recorded = array();
			unset($part['__trust__']);

			foreach($part as $key => $value)
			{
				$unchanged = isset($stored[$key]) && $form->isPostedBack($key, $value, $stored[$key]);
				$part[$key] = $unchanged ? $stored[$key] : (($key === 'url') ? $value : $tp->toDB($value));
				$recorded[$key] = $unchanged ? (isset($levels[$key]) ? $levels[$key] : null) : $trust;

				if($glyph && !$unchanged && $key === 'icon' && is_string($part[$key]) && $part[$key] !== '' && strpos($part[$key], '.glyph') === false)
				{
					$part[$key] .= '.glyph';
				}
			}

			$part['__trust__'] = array_filter($recorded, 'is_int');

			return $part;
		}

		/**
		 * @param array $old_data the slide as stored, empty for a new one
		 * @param string $field 'hero_bullets', 'hero_button1' or 'hero_button2'
		 * @return array that field's parts as stored
		 */
		private function storedValue($old_data, $field)
		{
			$stored = isset($old_data[$field]) ? e107::unserialize($old_data[$field]) : array();

			return is_array($stored) ? $stored : array();
		}

		/**
		 * @return int what the current user may post once {@see e_parse::toDB()} has stored it: 2 scripts and HTML (post_script), 1 HTML (post_html), 0 text
		 */
		private function authorTrust()
		{
			$pref = e107::getConfig();

			if(check_class($pref->get('post_script', e_UC_NOBODY)))
			{
				return 2;
			}

			return check_class($pref->get('post_html', e_UC_MAINADMIN)) ? 1 : 0;
		}

		public function afterUpdate($new_data, $old_data, $id)
		{
			// do something	
		}

		public function onUpdateError($new_data, $old_data, $id)
		{
			// do something		
		}		

		// left-panel help menu area. 
		public function renderHelp()
		{
			$caption = LAN_HELP;
			$text = LAN_HERO_ADMIN_007;

			return array('caption'=>$caption,'text'=> $text);

		}

	/*	
		// optional - a custom page.  
		public function customPage()
		{
			$text = 'Hello World!';
			$otherField  = $this->getController()->getFieldVar('other_field_name');
			return $text;

		}




	*/
			
}
				


class hero_form_ui extends e_admin_form_ui
{

	function icon_pack($curVal,$mode)
	{
			$custom = e107::getThemeGlyphs();


			$loaded = array();
			foreach($custom as $val)
			{
				$loaded[] = $val['name'];
			}


			$supported = array(

				'materialdesign',
				'ionicon',
				'weathericon',
				'mapicon',
				'octicon',
				'typicon',
				'elusiveicon',
				'flagicon'
			);

			$opts = array(
				'fontawesome' => "FontAwesome",
				'glyphicon' => "Bootstrap"
			);

			foreach($supported as $gl)
			{

				$name = ucfirst($gl);

				if(!in_array($gl,$loaded))
				{
					$gl = "__".$gl;
				}

				$opts[$gl] = $name;


			}

			$text = $this->select_open('icon_pack');

			foreach($opts as $key=>$val)
			{
				$disabled = (strpos($key,'__')===0) ? 'disabled' : '';
				$selected = ($curVal === $key) ? "selected" : "";

				$text .= "<option value='".$key."' ".$disabled." ".$selected.">".$val."</option>\n";

			}

			$text .= $this->select_close();


			return $text;

	}
	
	// Custom Method/Function 
	function hero_bullets($curVal,$mode)
	{
		$value = array();

		if(!empty($curVal))
		{
			$value = e107::unserialize($curVal);
		}

		 		
		switch($mode)
		{
			case 'read': // List Page
				if(empty($value))
				{
					return null;
				}

			$tp = e107::getParser();

			$text = '<table style="background-color:transparent" cellspacing="4">';

				foreach($value as $row)
				{
					$text .= "<tr><td style='width:30px;vertical-align:top'>";
					$text .= $tp->toIcon($tp->attributeValue($row['icon']));
					$text .= "</td><td>";
					$text .= " ".$tp->attributeValue($row['text']);
					$text .= "</td></tr>";

				}
			$text .= "</table>";
				return $text;

			
			case 'write': // Edit Page

				$amt = range(0,4);

				$optAnimation =  array(	"bounce",    "flash",     "pulse",
    "rubberBand",
    "shake",
    "headShake",
    "swing",
    "tada",
    "wobble",
"jello",
 "bounceIn",
    "bounceInDown",
    "bounceInLeft",
    "bounceInRight",
"bounceInUp",
"bounceOut",
    "bounceOutDown",
    "bounceOutLeft",
    "bounceOutRight",
"bounceOutUp",
 "fadeIn",
    "fadeInDown",
    "fadeInDownBig",
    "fadeInLeft",
    "fadeInLeftBig",
    "fadeInRight",
    "fadeInRightBig",
    "fadeInUp",
"fadeInUpBig",
 "fadeOut",
    "fadeOutDown",
    "fadeOutDownBig",
    "fadeOutLeft",
    "fadeOutLeftBig",
    "fadeOutRight",
    "fadeOutRightBig",
    "fadeOutUp",
"fadeOutUpBig",
 "flip",
    "flipInX",
    "flipInY",
    "flipOutX",
"flipOutY",
 "lightSpeedIn",
"lightSpeedOut",
 "rotateIn",
    "rotateInDownLeft",
    "rotateInDownRight",
    "rotateInUpLeft",
"rotateInUpRight",
 "rotateOut",
    "rotateOutDownLeft",
    "rotateOutDownRight",
    "rotateOutUpLeft",
"rotateOutUpRight",
  "hinge",
    "jackInTheBox",
    "rollIn",
"rollOut",
  "zoomIn",
    "zoomInDown",
    "zoomInLeft",
    "zoomInRight",
"zoomInUp",
  "zoomOut",
    "zoomOutDown",
    "zoomOutLeft",
    "zoomOutRight",
"zoomOutUp",
  "slideInDown",
    "slideInLeft",
    "slideInRight",
"slideInUp",
 "slideOutDown",
    "slideOutLeft",
    "slideOutRight",
"slideOutUp"


		);

				$text = "<table class='table table-condensed table-bordered' style='margin:0;'>
				<colgroup>
					<col style='width:5%' />
					<col style='width:5%' />
					<col />
					<col />
					<col />
				</colgroup>
				<tr>
					<th class='text-center'>".LAN_ICON."</th>
					<th class='text-center'>".LAN_HERO_ADMIN_010."</th>
					<th>".LAN_HERO_ADMIN_012."</th>
					<th>".LAN_HERO_ADMIN_013."</th>
					<th>".LAN_HERO_ADMIN_014."</th>
					</tr>";


					$tmp = range(0,60);
					$optDelay = array();

					foreach($tmp as $val)
					{
						$optDelay[$val] = $val/10;
					}


					$optStyle = array('default', 'primary', 'success', 'info', 'warning', 'danger');

				foreach($amt as $v)
				{
					$name = 'hero_bullets['.$v.']';
					$val = varset($value[$v], array());

					$text .= "<tr>
								<td class='text-center'>".$this->glyphPicker($name.'[icon]', varset($val['icon']))."</td>
								<td>".$this->btnClass($name.'[icon_style]', varset($val['icon_style']))."</td>
								<td>".$this->textarea($name.'[text]', e107::getParser()->toAttribute($this->shownValue('text', varset($val['text'])), true),1,80,array('size'=>'block-level'))."</td>
								<td>".$this->select($name.'[animation]',$optAnimation, varset($val['animation']), array( 'useValues'=>1), true)."</td>
								<td>".$this->select($name.'[animation_delay]',$optDelay, varset($val['animation_delay']), array('size'=>'small'), true)."</td>
							</tr>";

				}

				$text .= "</table>";


				return $text;
			
			case 'filter':
			case 'batch':
				return  array();
		}
	}


	function glyphPicker($name,$value)
	{
		$tp = e107::getParser();

		$pack = e107::pref('hero','icon_pack', 'fontawesome');

		return '<button class="btn btn-block btn-default iconpicker" role="iconpicker" name="'.$name.'" data-iconset="'.$tp->attributeValue($pack).'" data-icon="'.$tp->toAttribute($this->shownValue('icon', $value), true).'"></button>';

	}

	/**
	 * @param string $key a value of a bullet or button: 'icon', 'text', 'label', 'url' or a style
	 * @param mixed $posted the value as posted
	 * @param mixed $stored the value as stored
	 * @return bool whether a browser posts $posted back for $stored from this form's element for it left untouched
	 */
	public function isPostedBack($key, $posted, $stored)
	{
		return e107::getParser()->isUnchangedPost($posted, $this->shownValue($key, $stored), array('type' => ($key === 'text') ? 'textarea' : 'text'));
	}

	/**
	 * @param string $key a value of a bullet or button
	 * @param mixed $value that value as stored
	 * @return mixed the value as the edit form's element shows it: icon, text and label decoded, an icon without '.glyph'
	 */
	private function shownValue($key, $value)
	{
		if(!is_string($value) || !in_array($key, array('icon', 'text', 'label'), true))
		{
			return $value;
		}

		$value = ($key === 'icon') ? str_replace('.glyph', '', $value) : $value;

		return html_entity_decode(e107::getParser()->attributeValue($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	function btnClass($name,$value,$options=array())
	{


		$text = $this->select_open($name);

		$optStyle = array('default', 'primary', 'success', 'info', 'warning', 'danger');

		$text .= "<option value=''>".LAN_INACTIVE."</option>";

		foreach($optStyle as $s)
		{
			$selected = $s === $value ? "selected='selected'" : "";
			$text .= "<option class='label-".$s."' value='".$s."' {$selected}>".$s."</option>";
		}

		$text .= "</select>";

		return $text;

	}


	private function heroButtonInput($name, $val)
	{


				$text = "<table style='width:100%;margin:2px' cellspacing='3'>
				<colgroup>
					<col style='width:5%' />
					<col />
					<col />
				
				</colgroup>
				";


					$text .= "<tr>
								<td class='text-center'>".$this->glyphPicker($name.'[icon]', varset($val['icon']))."</td>
								<td>".$this->text($name.'[label]', $this->shownValue('label', varset($val['label'])), 255, array('size'=>'block-level', 'placeholder'=>LAN_HERO_ADMIN_006))."</td>
								<td> ".$this->text($name.'[url]', varset($val['url']), 255, array('size'=>'block-level', 'placeholder'=>'URL'))."</td>
								<td> ".$this->btnClass($name.'[class]', varset($val['class']))."</td>
							</tr>";



				$text .= "</table>";

				return $text;

	}


	function renderHeroButton($value)
	{
		if(empty($value['label']) && empty($value['class']))
		{
			return null;
		}

		$tp = e107::getParser();
		$icon = $tp->toIcon($tp->attributeValue(varset($value['icon'])));

		return "<a href='".$tp->toUrlAttribute(varset($value['url']))."' class='btn btn-sm btn-block btn-".$tp->attributeValue(varset($value['class']))."'>".$icon." ".$tp->attributeValue(varset($value['label']))."</a>";

	}




	
	// Custom Method/Function 
	function hero_button1($curVal,$mode)
	{
		$value = null;

		if(!empty($curVal))
		{
			$value = e107::unserialize($curVal);
		}
		 		
		switch($mode)
		{
			case 'read': // List Page

				return $this->renderHeroButton($value);
			
			case 'write': // Edit Page
				return $this->heroButtonInput('hero_button1', $value);
			
			case 'filter':
			case 'batch':
				return  array();
		}
	}

	
	// Custom Method/Function 
	function hero_button2($curVal,$mode)
	{
		$value = null;

		if(!empty($curVal))
		{
			$value = e107::unserialize($curVal);
		}
		 		
		switch($mode)
		{
			case 'read': // List Page
				return $this->renderHeroButton($value);
			
			case 'write': // Edit Page
				return $this->heroButtonInput('hero_button2', $value);
			
			case 'filter':
			case 'batch':
				return  array();
		}
	}

	// Edit mode for hero_bg and hero_media
	function hero_images($curVal,$mode,$att)
	{
		$value = null;

		if($mode !== 'write')
		{
			return null;
		}

		$bg = $this->getController()->getFieldVar('hero_bg');
		$media = $this->getController()->getFieldVar('hero_media');

		return "<table class='table table-condensed table-bordered' style='margin:0;width:auto'>
				<tr>
					<th class='text-center'>".LAN_HERO_ADMIN_003."</th>
					<th class='text-center'>".LAN_HERO_ADMIN_004."</th>
				</tr>
				<tr>
					<td class='text-center' style='padding-left:15px'>".$this->imagepicker('hero_bg', $bg, null, array('media'=>'hero^'))."</td>
					<td class='text-center' style='padding-left:15px'>".$this->imagepicker('hero_media', $media, null, array('media'=>'hero^',  'video' => 1))."</td>
				</tr>
				</table>";



	}

}		
		
		
new hero_adminArea();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

e107::js('hero', 'js/bootstrap-iconpicker-iconset-all.min.js');
e107::js('hero', 'js/bootstrap-iconpicker.min.js');


require_once(e_ADMIN."footer.php");
