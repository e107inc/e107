<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin Administration - gsitemap
 *
*/
require_once(__DIR__.'/../../class2.php');
if(!getperms("P") || !e107::isInstalled('gsitemap'))
{ 
	e107::redirect('admin');
	exit();
}
//require_once(e_ADMIN."auth.php");
//require_once(e_HANDLER."userclass_class.php");

e107::lan('gsitemap',true);
e107::css('inline', '#admin-gsitemap-main-instructions .block-text ol > li { padding:1rem }');


class gsitemap_adminArea extends e_admin_dispatcher
{

	protected $modes = array(	
	
		'main'	=> array(
			'controller' 	=> 'gsitemap_ui',
			'path' 			=> null,
			'ui' 			=> 'gsitemap_form_ui',
			'uipath' 		=> null
		),
		

	);	
	
	
	protected $adminMenu = array(

		'main/list'			=> array('caption'=> 'LAN_MANAGE', 'perm' => 'P'),
		'main/create'		=> array('caption'=> 'GSLAN_22', 'perm' => 'P'),

		// 'main/div0'        => array('divider'=> true),
		 'main/import'		=> array('caption'=> 'LAN_IMPORT', 'perm' => 'P'),
		 'main/instructions' => array('caption'=> 'GSLAN_53', 'perm' => 'P', 'icon'=>'fa-info-circle'),
		
	);

	protected $adminMenuAliases = array(
		'main/edit'	=> 'main/list'				
	);	
	
	protected $menuTitle = 'LAN_PLUGIN_GSITEMAP_NAME';
}




				
class gsitemap_ui extends e_admin_ui
{
			
		protected $pluginTitle		= 'LAN_PLUGIN_GSITEMAP_NAME';
		protected $pluginName		= 'gsitemap';
	//	protected $eventName		= 'gsitemap-gsitemap'; // remove comment to enable event triggers in admin. 		
		protected $table			= 'gsitemap';
		protected $pid				= 'gsitemap_id';
		protected $perPage			= 10; 
		protected $batchDelete		= true;
		protected $batchExport     = true;
		protected $batchCopy		= true;

	//	protected $sortField		= 'somefield_order';
	//	protected $sortParent      = 'somefield_parent';
	//	protected $treePrefix      = 'somefield_title';

		protected $tabs				= array(LAN_GENERAL,LAN_ADVANCED); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.
		
	//	protected $listQry      	= "SELECT * FROM `#tableName` WHERE field != '' "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.
	
		protected $listOrder		= 'gsitemap_id DESC';
	
		protected $fields 		= array (
			'checkboxes'              => array (  'title' => '',  'type' => null,  'data' => null,  'width' => '5%',  'thclass' => 'center',  'forced' => true,  'class' => 'center',  'toggle' => 'e-multiselect',  'readParms' =>  array (),  'writeParms' =>  array (),),
			'gsitemap_id'             => array (  'title' => 'LAN_ID',  'type'=>'number', 'data' => 'int',  'width' => '5%',  'help' => '',  'readParms' =>  array (),  'writeParms' =>  array (),  'class' => 'left',  'thclass' => 'left',),
			'gsitemap_name'           => array (  'title' => LAN_TITLE,  'type' => 'text',  'data' => 'safestr',  'width' => 'auto',  'inline' => true,  'help' => '',  'readParms' =>  array (),  'writeParms' =>  array ('size'=>'xxlarge'),  'class' => 'left',  'thclass' => 'left',),
			'gsitemap_url'            => array (  'title' => LAN_URL,  'type' => 'url',  'data' => 'safestr',  'width' => 'auto',  'inline' => true,  'help' => '',  'readParms' =>  array (),  'writeParms' =>  array ('size'=>'xxlarge'),  'class' => 'left',  'thclass' => 'left',),
			'gsitemap_table'          => array (  'title' => 'Table', 'tab'=>1, 'type' => 'text',  'data' => 'safestr',  'width' => 'auto',  'filter' => true,  'help' => '',  'readParms' =>  array (),  'writeParms' =>  array (),  'class' => 'left',  'thclass' => 'left',  'batch' => false,),
			'gsitemap_table_id'       => array (  'title' => LAN_ID,  'tab'=>1,'type' => 'number',  'data' => 'int',  'width' => '5%',  'readonly' => false,  'help' => '',  'readParms' =>  array (),  'writeParms' =>  array (),  'class' => 'left',  'thclass' => 'left',),
			'gsitemap_lastmod'        => array (  'title' => 'GSLAN_27', 'tab'=>1, 'type' => 'datestamp', 'readonly'=>2, 'data' => 'int',  'width' => 'auto',  'help' => '',  'readParms' =>  array (),  'writeParms' =>  array (),  'class' => 'left',  'thclass' => 'left',  'filter' => false,  'batch' => true,),
			'gsitemap_freq'           => array (  'title' => 'GSLAN_28',  'type' => 'dropdown',  'data' => 'safestr',  'width' => 'auto',  'help' => '',  'readParms' =>  array (),  'writeParms' =>  array (),  'class' => 'left',  'thclass' => 'left',  'filter' => true,  'batch' => true,),
			'gsitemap_priority'       => array (  'title' => 'GSLAN_9',  'type' => 'method',  'data' => 'safestr',  'width' => 'auto',  'help' => '',  'readParms' =>  array (),  'writeParms' =>  array (),  'class' => 'left',  'thclass' => 'left',  'filter' => true,  'batch' => true,),
			'gsitemap_cat'            => array (  'title' => 'LAN_CATEGORY', 'tab'=>1, 'type' => 'text',  'data' => 'safestr',  'width' => 'auto',  'batch' => true,  'filter' => true,  'help' => '',  'readParms' =>  array (),  'writeParms' =>  array (),  'class' => 'left',  'thclass' => 'left',),
			'gsitemap_order'          => array (  'title' => LAN_ORDER,  'type' => 'number',  'data' => 'int',  'width' => 'auto',  'help' => '',  'readParms' =>  array (),  'writeParms' =>  array (),  'class' => 'left',  'thclass' => 'left',),
			'gsitemap_img'            => array (  'title' => LAN_IMAGE,  'type' => 'image',  'data' => 'safestr',  'width' => 'auto',  'help' => '',  'readParms' => 'thumb=80x80',  'writeParms' =>  array (),  'class' => 'left',  'thclass' => 'left',),
			'gsitemap_active'         => array (  'title' => LAN_VISIBILITY,  'type' => 'userclass',  'data' => 'int',  'width' => 'auto',  'filter' => true,  'help' => '',  'readParms' =>  array (),  'writeParms' =>  array (),  'class' => 'left',  'thclass' => 'left',),
			'options'                 => array (  'title' => LAN_OPTIONS,  'type' => null,  'data' => null,  'width' => '10%',  'thclass' => 'center last',  'class' => 'center last',  'forced' => true,  'readParms' =>  array (),  'writeParms' =>  array (),),
		);		
		
		protected $fieldpref = array('gsitemap_name','gsitemap_url','gsitemap_lastmod','gsitemap_freq','gsitemap_priority');
		

	//	protected $preftabs        = array('General', 'Other' );
		protected $prefs = array(
		);

		protected $freqList = array
											(
												"always"	=>	GSLAN_11,
												"hourly"	=>	GSLAN_12,
												"daily"		=>	GSLAN_13,
												"weekly"	=>	GSLAN_14,
												"monthly"	=>	GSLAN_15,
												"yearly"	=>	GSLAN_16,
												"never"		=>	LAN_NEVER
											);

	
		public function init()
		{
			// This code may be removed once plugin development is complete. 
			if(!e107::isInstalled('gsitemap'))
			{
				e107::getMessage()->addWarning("This plugin is not yet installed. Saving and loading of preference or table data will fail.");
			}
			
			// Set drop-down values (if any). 
			$this->fields['gsitemap_table']['writeParms']['optArray'] = array('gsitemap_table_0','gsitemap_table_1', 'gsitemap_table_2'); // Example Drop-down array. 
			$this->fields['gsitemap_cat']['writeParms']['optArray'] = array('gsitemap_cat_0','gsitemap_cat_1', 'gsitemap_cat_2'); // Example Drop-down array. 
			$this->fields['gsitemap_freq']['writeParms']['optArray'] = $this->freqList;


			if(!empty($_POST['import_links']))
			{
				$this->importLink();
			}

		}

	/**
	 * @return array
	 */

		// ------- Customize Create --------
		
		public function beforeCreate($new_data,$old_data)
		{
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
			return $new_data;
		}

		public function afterUpdate($new_data, $old_data, $id)
		{
			// do something	
		}
		
		public function onUpdateError($new_data, $old_data, $id)
		{
			// do something		
		}		
		
		// left-panel help menu area. (replaces e_help.php used in old plugins)
		public function renderHelp()
		{

		}

		public function importPage()
		{



			global $PLUGINS_DIRECTORY;

			$ns 	= e107::getRender();
			$sql 	= e107::getDb();
			//$sql2 	= e107::getDb('sql2'); not used?
			$frm 	= e107::getForm();
			$mes 	= e107::getMessage();

			$existing = $sql->createQueryBuilder()
				->select('gsitemap_name')->from('gsitemap')
				->fetchColumn('gsitemap_name');


			$importArray = array();

			/* sitelinks ... */
			$nfArray = $sql->createQueryBuilder()
				->select('*')->from('links')
				->orderBy('link_order', 'ASC')
				->fetchAll();
			foreach($nfArray as $row)
			{
				if(!in_array($row['link_name'], $existing))
				{
					$importArray[] = array(
						'table' => 'links',
						'id'    => $row['link_id'],
						'name' => $row['link_name'],
						'url' => !empty($row['link_owner']) && !empty($row['link_sefurl']) ? e107::url($row['link_owner'], $row['link_sefurl']) : $row['link_url'],
						'type' => GSLAN_1,
						'class' => (int) $row['link_class']);
				}
			}

			/* custom pages ... */
			$qb = $sql->createQueryBuilder();
			$data = $qb
				->select('p.page_id', 'p.page_title', 'p.page_sef', 'p.page_class', 'p.page_chapter')->selectAs('ch.chapter_sef', 'chapter_sef')->selectAs('b.chapter_sef', 'book_sef')
				->from('page', 'p')
				->leftJoin('page_chapters', 'ch', $qb->expr()->compareColumns('p.page_chapter', 'ch.chapter_id'))
				->leftJoin('page_chapters', 'b', $qb->expr()->compareColumns('ch.chapter_parent', 'b.chapter_id'))
				->where('page_title', '!=', '')
				->orderBy('page_datestamp', 'ASC')
				->fetchAll();

			foreach($data as $row)
			{
				if(!in_array($row['page_title'], $existing))
				{
					$route = ($row['page_chapter'] == 0) ? "page/view/other" : "page/view/index";

					$importArray[] = array(
						'table' => 'page',
						'id'    => $row['page_id'],
						'name' => $row['page_title'],
						'url' => e107::getUrl()->create($route, $row, array('full'=>1, 'allow' => 'page_sef,page_title,page_id, chapter_sef, book_sef')),
						'type' => "Page",
						'class' => $row['page_class']
						);
				}
			}



			/* Plugins.. - currently: forums ... */
			$addons = e107::getAddonConfig('e_gsitemap', null, 'import');

			foreach($addons as $plug => $config)
			{

				foreach($config as $row)
				{
					if(!in_array($row['name'], $existing))
					{
						$row['plugin'] = $plug;
						$importArray[] = $row;
					}
				}

			}

			$editArray = $_POST;

			$text = "
			<form action='".e_SELF."' id='form' method='post'>
			<table class='table adminlist table-striped table-condensed'>
			<colgroup>
				<col class='center' style='width:5%;' />
				<col style='width:15%' />
				<col style='width:30%' />
				<col style='width:auto' />
				<col style='width:115px' />
			</colgroup>
			<thead>
				<tr>
				<th class='center'><input type='checkbox' name='e-column-toggle' value='jstarget:importid' id='e-column-toggle-jstarget-e-multiselect' class='checkbox checkbox-inline toggle-all form-check-input ui-state-valid' /></th>
				<th>".LAN_TYPE."</th>
				<th>".LAN_NAME."</th>
				<th>".LAN_URL."</th>
				<th class='center'>".defset('GSLAN_50', 'Publicly visible')."</th>
			</tr>
			</thead>
			<tbody>
			";


			$uc = e107::getUserClass();

			foreach($importArray as $k=>$ia)
			{
				$id = 'gs-'.$k;

				$class = '';
				$classLabel = defset('ADMIN_FALSE_ICON');

				if(isset($ia['class']) && ((int) $ia['class'] === e_UC_PUBLIC))
				{
					$class = 'label-success';
					$classLabel = defset('ADMIN_TRUE_ICON');
				}


				$text .= "
				<tr>
					<td class='center'><input id='".$id."' type='checkbox' name='importid[]' 
					value='".$ia['name']."^".$ia['url']."^".$ia['type']."^".$ia['plugin']."^".$ia['table']."^".$ia['id']."' /></td>
					<td><label for='".$id."' style='cursor:pointer' >".$ia['type']."</label></td>
					<td><label for='".$id."' style='cursor:pointer'>".defset($ia['name'],$ia['name'])."</label></td>
					<td><span class='smalltext'>".str_replace(SITEURL,"",$ia['url'])."</span></td>
					<td class='center'>". $classLabel."</td>
				</tr>
				";
			}

			$text .= "
			<tr>
			<td colspan='4' class='center'>
			<div class='buttons-bar'> ".GSLAN_8." &nbsp; ".GSLAN_9." :&nbsp;<select class='tbox' name='import_priority' >\n";

			for ($i=0.1; $i<1.0; $i=$i+0.1)
			{
				$sel = (vartrue($editArray['gsitemap_priority']) == number_format($i,1))? "selected='selected'" : "";
				$text .= "<option value='".number_format($i,1)."' $sel>".number_format($i,1)."</option>\n";
			}

			$text.="</select>&nbsp;&nbsp;&nbsp;".GSLAN_10."
	
		
			
			<select class='tbox' name='import_freq' >\n";
			foreach($this->freqList as $k=>$fq)
			{
				$sel = (vartrue($editArray['gsitemap_freq']) == $k)? "selected='selected'" : "";
				$text .= "<option value='{$k}' {$sel}>{$fq}</option>\n";
			}

			$text .= "</select> <br /><br />
	
			</div>
			
			</td>
			</tr>
			</tbody>
			</table>
			<div class='buttons-bar center'>
			".
			$frm->admin_button('import_links',GSLAN_18)."
			</div>
			</form>
			";

			return $text;
		//	return $ns->tablerender(GSLAN_7, $mes->render(). $text, 'default', true);

			unset($PLUGINS_DIRECTORY);

		}


		public function instructionsPage()
		{
			$mes = e107::getMessage();
			$ns = e107::getRender();


			$LINK_1 = "https://search.google.com/search-console/";
			$LINK_2 = "http://www.google.com/support/webmasters/?hl=en";

			$srch[0] = "[URL]";
			$repl[0] = "<a href='".$LINK_1."' target='_blank'>".$LINK_1."</a>";


			$addons = e107::getAddonConfig('e_gsitemap', 'gsitemap');

			$extraUrls = '';
			foreach($addons as $plug => $item)
			{
				foreach($item as $data )
				{
					$lan = defset("GSLAN_51", "Auto-generated from [x]");

					$key = $plug.'-'.$data['sef'];
					$url = e107::url('gsitemap', $key, [], ['mode'=>'full']);
					$extraUrls .= '<li><a href="'.$url.'" target="_blank">'.$url.'</a> <small><em>('.str_replace('[x]', $plug, $lan).')</em></small></li>';
				}

			}


			$srch[1] = "[SITEMAP_URLS]";
			$repl[1] = "<ul style='margin:10px'><li><a href='".SITEURL."sitemap.xml' target='_blank'>".SITEURL."sitemap.xml</a></li>".$extraUrls."</ul>";

			$srch[2] = "[";
			$repl[2] = "<a href='".e_ADMIN."prefs.php'>";

			$srch[3] = "]";
			$repl[3] = "</a>";

			$default = "Once you have some entries, go to [URL] and enter one of the following URLs in the Sitemaps section.[SITEMAP_URLS]  If any of these urls look incorrect to you, please make sure your site url is correct in [preferences].";

		//	$text = "<b>".GSLAN_33."</b><br /><br />";
$text = "
			<ol>
				<li>".GSLAN_34."</li>
				<li>".GSLAN_35."</li>
				<li>".GSLAN_36."</li>
				<li>".str_replace($srch,$repl,defset("GSLAN_52", $default))."</li>
				<li>".str_replace("[URL]","<a href='".$LINK_2."' target='_blank'>".$LINK_2."</a>",GSLAN_38)."</li>
			</ol>
			";

			return $text;

		}
			
	/*	
		// optional - a custom page.  
		public function customPage()
		{
			$text = 'Hello World!';
			$otherField  = $this->getController()->getFieldVar('other_field_name');
			return $text;
			
		}
		

	
	 // Handle batch options as defined in gsitemap_form_ui::gsitemap_lastmod;  'handle' + action + field + 'Batch'
	 // @important $fields['gsitemap_lastmod']['batch'] must be true for this method to be detected. 
	 // @param $selected
	 // @param $type
	function handleListGsitemapLastmodBatch($selected, $type)
	{

		$ids = implode(',', $selected);

		switch($type)
		{
			case 'custombatch_1':
				// do something
				e107::getMessage()->addSuccess('Executed custombatch_1');
				break;

			case 'custombatch_2':
				// do something
				e107::getMessage()->addSuccess('Executed custombatch_2');
				break;

		}


	}

	
	 // Handle batch options as defined in gsitemap_form_ui::gsitemap_freq;  'handle' + action + field + 'Batch'
	 // @important $fields['gsitemap_freq']['batch'] must be true for this method to be detected. 
	 // @param $selected
	 // @param $type
	function handleListGsitemapFreqBatch($selected, $type)
	{

		$ids = implode(',', $selected);

		switch($type)
		{
			case 'custombatch_1':
				// do something
				e107::getMessage()->addSuccess('Executed custombatch_1');
				break;

			case 'custombatch_2':
				// do something
				e107::getMessage()->addSuccess('Executed custombatch_2');
				break;

		}


	}

	
	 // Handle filter options as defined in gsitemap_form_ui::gsitemap_lastmod;  'handle' + action + field + 'Filter'
	 // @important $fields['gsitemap_lastmod']['filter'] must be true for this method to be detected. 
	 // @param $selected
	 // @param $type
	function handleListGsitemapLastmodFilter($type)
	{

		$this->listOrder = 'gsitemap_lastmod ASC';
	
		switch($type)
		{
			case 'customfilter_1':
				// return ' gsitemap_lastmod != 'something' '; 
				e107::getMessage()->addSuccess('Executed customfilter_1');
				break;

			case 'customfilter_2':
				// return ' gsitemap_lastmod != 'something' '; 
				e107::getMessage()->addSuccess('Executed customfilter_2');
				break;

		}


	}

	
	 // Handle filter options as defined in gsitemap_form_ui::gsitemap_freq;  'handle' + action + field + 'Filter'
	 // @important $fields['gsitemap_freq']['filter'] must be true for this method to be detected. 
	 // @param $selected
	 // @param $type
	function handleListGsitemapFreqFilter($type)
	{

		$this->listOrder = 'gsitemap_freq ASC';
	
		switch($type)
		{
			case 'customfilter_1':
				// return ' gsitemap_freq != 'something' '; 
				e107::getMessage()->addSuccess('Executed customfilter_1');
				break;

			case 'customfilter_2':
				// return ' gsitemap_freq != 'something' '; 
				e107::getMessage()->addSuccess('Executed customfilter_2');
				break;

		}


	}
	
		
		
	*/
	function importLink()
	{

		$sql = e107::getDb();
		$tp = e107::getParser();
		$log = e107::getLog();


		foreach ($_POST['importid'] as $import)
		{
			list($name, $url, $type, $plugin, $table, $id) = explode("^", $import);

			$insert = array(
				'gsitemap_id'       => 0,
				'gsitemap_name'     => $tp->toDB($name),
				'gsitemap_url'      => $tp->toDB($url),
				'gsitemap_plugin'   => $tp->toDB($plugin),
				'gsitemap_table'    => $tp->toDB($table),
				'gsitemap_table_id' => (int) $id,
				'gsitemap_lastmod'  => time(),
				'gsitemap_freq'     => $_POST['import_freq'],
				'gsitemap_priority' => $_POST['import_priority'],
				'gsitemap_cat'      => $type,
				'gsitemap_order'    => '0',
				'gsitemap_img'      => '',
				'gsitemap_active'   => '0',
			);

			if ($sql->createQueryBuilder()->insert('gsitemap')->valuesTyped($insert, $sql->getFieldDefs('gsitemap')['_FIELD_TYPES'])->execute())
			{
				e107::getMessage()->addSuccess(LAN_CREATED);
				$log->add('GSMAP_01', LAN_CREATED);
			}
			else
			{
				e107::getMessage()->addError(LAN_CREATED_FAILED);
			}

			//	$sql->insert("gsitemap", "0, '$name', '$url', '".time()."', '".$_POST['import_freq']."', '".$_POST['import_priority']."', '$type', '0', '', '0' ");
		}

		//	$this->message = count($_POST['importid'])." link(s) imported.";
	//	$log->add('GSMAP_01', $gsitemap->message);
	}

}
				


class gsitemap_form_ui extends e_admin_form_ui
{

	
	// Custom Method/Function 
	function gsitemap_priority($curVal,$mode)
	{

		 		
		switch($mode)
		{
			case 'read': // List Page
				return $curVal;
			break;
			
			case 'write': // Edit Page
			case 'batch':
			case 'filter':
				$array = [];
				$text = "<select class='tbox' name='gsitemap_priority' >\n";

				for ($i=0.1; $i<1.0; $i=$i+0.1)
				{
					$num = (string) number_format($i,1);
					$sel = ($curVal == $num) ? "selected='selected'" : "";
					$text .= "<option value='".$num."' $sel>".$num."</option>\n";
					$array[$num] = $num;
				}

				$text.="</select>";

				return ($mode === 'write') ? $text : $array;
			break;
			

		}
		
		return null;
	}

	
	// Custom Method/Function 
	function gsitemap_freq($curVal,$mode)
	{

		 		
		switch($mode)
		{
			case 'read': // List Page
				return $curVal;
			break;
			
			case 'write': // Edit Page
				return $this->text('gsitemap_freq',$curVal, 255, 'size=large');
			break;
			
			case 'filter':
				return array('customfilter_1' => 'Custom Filter 1', 'customfilter_2' => 'Custom Filter 2');
			break;
			
			case 'batch':
				return array('custombatch_1' => 'Custom Batch 1', 'custombatch_2' => 'Custom Batch 2');
			break;
		}
		
		return null;
	}

}		
		
		
new gsitemap_adminArea();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();

require_once(e_ADMIN."footer.php");

// loaded automatically.
/*
function admin_config_adminmenu() 
{
	$action = (e_QUERY) ? e_QUERY : "list";
    $var['list']['text'] = GSLAN_20;
	$var['list']['link'] = e_SELF;
	$var['list']['perm'] = "7";
	$var['instructions']['text'] = GSLAN_21 ;
	$var['instructions']['link'] = e_SELF."?instructions";
	$var['instructions']['perm'] = "7";
    $var['new']['text'] = GSLAN_22 ;
	$var['new']['link'] = e_SELF."?new";
	$var['new']['perm'] = "7";
	$var['import']['text'] = GSLAN_23;
	$var['import']['link'] = e_SELF."?import";
	$var['import']['perm'] = "0";
	
	show_admin_menu(LAN_PLUGIN_GSITEMAP_NAME, $action, $var);
}*/

