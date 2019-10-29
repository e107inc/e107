<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!e107::isInstalled('download')) { exit(); }

class download
{
	
	private $qry = array();
	
	private $orderOptions = array('download_id','download_datestamp','download_filesize','download_name','download_author','download_requested');
	
	private $templateHeader = '';
	private $templateFooter = '';

	private $subCategories = array();
	private $categories = array();
	private $template = array();
	private $sc = null; // shortcode object.
	private $rows = array();
	
	function __construct()
	{
				
		require_once(e_PLUGIN."download/download_shortcodes.php");
		
		if(deftrue('BOOTSTRAP')) // v2.x 
		{
			$this->templateHeader = e107::getTemplate('download','download','header');
			$this->templateFooter = e107::getTemplate('download','download','footer');
		}
		else 
		{
			$this->templateHeader = '';
			$this->templateFooter = '';
		}

		$pref = e107::getPref();

	//	$catObj = new downloadCategory(varset($pref['download_subsub'],1),USERCLASS_LIST,null, varset($pref['download_incinfo'], false));
	//	$this->categories = $catObj->cat_tree;
		$this->loadCategories();


	}

	private function loadCategories()
	{
		$sql = e107::getDb();
		$data = $sql->retrieve("download_category", '*', 'ORDER BY download_category_order',true);

		foreach($data as $row)
		{

			$id = (int) $row['download_category_parent'];
			$sub= (int) $row['download_category_id'];

			$this->subCategories[$id][$sub] = $row;
			if(check_class($row['download_category_class']))
			{
				$this->categories[$sub] = $row;
			}

		}


		return $this;
	}

	private function getCategory($id)
	{
		return !empty($this->categories[$id]) ? $this->categories[$id] : false;

	}

	private function getParent($id)
	{
		$parent = $this->categories[$id]['download_category_parent'];
		return $this->getCategory($parent);
	}

	private function getChildren($parent)
	{
		return !empty($this->subCategories[$parent]) ? $this->subCategories[$parent] : false;
	}
	
	public function init()
	{

		$tp = e107::getParser();
		$pref = e107::getPref();
	
		$tmp = explode('.', e_QUERY);
		
		$order = str_replace("download_","",$pref['download_order']);
						
		// Set Defaults
		$this->qry['action']		= 'maincats';
		$this->qry['order'] 		= vartrue($order, 'datestamp');
		$this->qry['sort']			= vartrue($pref['download_sort'], 'desc');
		$this->qry['view'] 			= vartrue($pref['download_view'], 10);
		$this->qry['from']			= 0;
			
		// v2.x 
		if(!empty($_GET['action']))
		{
			$this->qry['action'] 	= (string) $_GET['action'];
			$this->qry['view'] 		= varset($_GET['view']) ? intval($_GET['view']) : $this->qry['view'];
			$this->qry['id']		= intval($_GET['id']);
			$this->qry['order'] 	= vartrue($_GET['order']) && in_array("download_".$_GET['order'],$this->orderOptions) ? $_GET['order'] : $this->qry['order'];
			$this->qry['sort'] 		= (varset($_GET['sort']) == 'asc') ? "asc" : 'desc';	
			$this->qry['from']		= varset($_GET['from'],0);

			if($this->qry['action'] == 'error')
			{
				$this->qry['error'] = intval($this->qry['id']);
			}
		}
		else // v1.x Legacy URL support. 
		{
			if (is_numeric($tmp[0]))	//legacy		// $tmp[0] at least must be valid
			{
			   $this->qry['action'] 	= varset(preg_replace("#\W#", "", $tp->toDB($tmp[1])),'list');
			   $this->qry['id'] 		= intval($tmp[2]);
			   $this->qry['view'] 		= intval($tmp[3]);
			   $this->qry['order'] 		= preg_replace("#\W#", "", $tp->toDB($tmp[4]));
			   $this->qry['sort'] 		= preg_replace("#\W#", "", $tp->toDB($tmp[5]));
		   	}
			elseif($tmp[1])
		   	{
			   $this->qry['action'] 	= preg_replace("#\W#", "", $tp->toDB($tmp[0]));
			   $this->qry['id'] 		= intval($tmp[1]);
			   $this->qry['error']		= intval(varset($tmp[2],0));
			}	
			
		}	
		
		// v1.x
		if(varset($_POST['view'])) 
		{
			$this->qry['view'] 		= varset($_POST['view']) ? intval($_POST['view']) : 10;
			$this->qry['order'] 	= varset($_POST['order']) && in_array("download_".$_POST['order'],$this->orderOptions) ? $_POST['order'] : 'datestamp';
			$this->qry['sort'] 		= (strtolower($_POST['sort']) == 'asc') ? "asc" : 'desc';	
		}	
		

	}  



	/**
	 * Auto-detected loading of the appropriate headers/data.
	 */
	public function load()
	{

		$pref = e107::getPref();



		if($this->qry['action'] == 'maincats')
		{
		//
		}

		if($this->qry['action'] == 'list')
		{
			$this->loadList();
		}

		if($this->qry['action'] == 'view')
		{
			$this->loadView();
		}

		if ($this->qry['action'] == "report" && check_class($pref['download_reportbroken']))
		{
			$this->loadReport();
		}

		if($this->qry['action'] == 'mirror')
		{
		//
		}

		if($this->qry['action'] == 'error')
		{

		}

	}


	/**
	 * Auto-detected Render of the appropriate download page. 
	 */
	public function render()
	{
		
		$pref = e107::getPref();
		
		if($this->qry['action'] == 'maincats')
		{
			return $this->renderCategories();
		}
	
		if($this->qry['action'] == 'list')
		{
			return $this->renderList();
		}
		
		if($this->qry['action'] == 'view')
		{
			return $this->renderView();
		}
		
		if ($this->qry['action'] == "report" && check_class($pref['download_reportbroken']))
		{
			return $this->renderReport();		
		}
		
		if($this->qry['action'] == 'mirror')
		{
			return $this->renderMirror();	
		}
		
		if($this->qry['action'] == 'error')
		{
			return $this->renderError();	
		}	
		
	}






	/**
	 * Render Download Categories. 
	 * @todo Cache 
	 */
	private function renderCategories()
	{
		$tp = e107::getParser();
		$ns = e107::getRender();
		$pref = e107::getPref();
		

	//	if ($cacheData = $e107cache->retrieve("download_cat".$maincatval,720)) // expires every 12 hours. //TODO make this an option
		{
	   //  	echo $cacheData;
		//	return;
		}


		
		if(deftrue('BOOTSTRAP')) // v2.x 
		{
			$template = e107::getTemplate('download','download','categories');
			
			$DOWNLOAD_CAT_TABLE_START 	= varset($template['start']);
			$DOWNLOAD_CAT_PARENT_TABLE	= $template['parent'];
			$DOWNLOAD_CAT_CHILD_TABLE	= $template['child'];
			$DOWNLOAD_CAT_SUBSUB_TABLE	= $template['subchild'];
			$DOWNLOAD_CAT_TABLE_END		= varset($template['end']);
				
//			$DL_VIEW_NEXTPREV			= varset($template['nextprev']);
//			$DL_VIEW_PAGETITLE			= varset($template['pagetitle']);
//			$DL_VIEW_CAPTION			= varset($template['caption'],"{DOWNLOAD_VIEW_CAPTION}");
		}
		else // Legacy v1.x 
		{
			$DOWNLOAD_CAT_TABLE_START 	= null;
			$DOWNLOAD_CAT_PARENT_TABLE	= null;
			$DOWNLOAD_CAT_CHILD_TABLE	= null;
			$DOWNLOAD_CAT_SUBSUB_TABLE	= null;
			$DOWNLOAD_CAT_TABLE_END		= null;


			$template_name = 'download_template.php';
			
			if (is_readable(THEME."templates/".$template_name))
			{
				require_once(THEME."templates/".$template_name);
			}
			elseif (is_readable(THEME.$template_name))
			{
				require_once(THEME.$template_name);
			}
			else
			{
				require_once(e_PLUGIN."download/templates/".$template_name);
			}	
		}

		/** @var download_shortcodes $sc */
		$sc = e107::getScBatch('download',true);
		$sc->wrapper('download/categories');
		$sc->breadcrumb();
		$sc->qry 	= $this->qry;	
		
	
		
		if(!defined("DL_IMAGESTYLE")){ define("DL_IMAGESTYLE","border:1px solid blue");}

	   // Read in tree of categories which this user is allowed to see
		$dlcat = new downloadCategory(varset($pref['download_subsub'],1),USERCLASS_LIST,$maincatval,varset($pref['download_incinfo'],FALSE));

		if ($dlcat->down_count == 0)
	   	{

			return $ns->tablerender(LAN_PLUGIN_DOWNLOAD_NAME, "<div class='alert alert-warning' style='text-align:center'>".LAN_NO_RECORDS_FOUND."</div>",'download-categories',true);
		}
				
		$download_cat_table_string = "";
		foreach($dlcat->cat_tree as $dlrow)  // Display main category headings, then sub-categories, optionally with sub-sub categories expanded
		{
			$sc->setVars($dlrow); 
			$download_cat_table_string .= $tp->parseTemplate($DOWNLOAD_CAT_PARENT_TABLE, TRUE, vartrue($sc));
			
			foreach($dlrow['subcats'] as $dlsubrow)
			{
				$sc->dlsubrow = $dlsubrow;
				
				
				$download_cat_table_string .= $tp->parseTemplate($DOWNLOAD_CAT_CHILD_TABLE, TRUE, $sc);
				
				foreach($dlsubrow['subsubcats'] as $dlsubsubrow)
				{
					$sc->dlsubsubrow = $dlsubsubrow;
					$download_cat_table_string .= $tp->parseTemplate($DOWNLOAD_CAT_SUBSUB_TABLE, TRUE, $sc);
				}
			}
	   }

	//    e107::getDebug()->log($dlcat->cat_tree);

	  
		$dl_text = $tp->parseTemplate($this->templateHeader, TRUE, $sc);
		$dl_text .= $tp->parseTemplate($DOWNLOAD_CAT_TABLE_START, TRUE, $sc);
		$dl_text .= $download_cat_table_string;
		$dl_text .= $tp->parseTemplate($DOWNLOAD_CAT_TABLE_END, TRUE, $sc);
	   
		$caption = varset($DOWNLOAD_CAT_CAPTION) ? $tp->parseTemplate($DOWNLOAD_CAT_CAPTION, TRUE, $sc) : LAN_PLUGIN_DOWNLOAD_NAME;
		
		//ob_start();
		
		$dl_text .= $tp->parseTemplate($this->templateFooter, TRUE, $sc);


	   
		return $ns->tablerender($caption, $dl_text, 'download-categories',true);

	  // $cache_data = ob_get_flush();
	 //  $e107cache->set("download_cat".$maincatval, $cache_data);	
		
		
	}


	/**
	 * Meta for 'view' mode..to be expanded to handle list and maincats etc.
	 * @param array $row
	 * @todo modes for different actions based on $this->qry['action']
	 */
	private function setMeta($row)
	{
		$tp = e107::getParser();

		$metaImage                      = $tp->thumbUrl($row['download_image'], array('w'=>500), null, true);
		$metaDescription                = $tp->toHTML($row['download_description'],true);
		$metaDescription 				= preg_replace('/\v(?:[\v\h]+)/', '', $metaDescription); // remove all line-breaks and excess whitespace
		$metaDescription 				= $tp->text_truncate($metaDescription, 290); // + '...'

		e107::meta('description',       $tp->toText($metaDescription));
		e107::meta('keywords',          $row['download_keywords']);
		e107::meta('og:description',    $tp->toText($metaDescription));
		e107::meta('og:image',          $metaImage);
		e107::meta('twitter:image:src', $metaImage);

	}


	private function loadReport()
	{
		$sql = e107::getDb();

		$query = "
		SELECT d.*, dc.* FROM #download AS d
		LEFT JOIN #download_category AS dc ON d.download_category = dc.download_category_id
		WHERE d.download_id = {$this->qry['id']}
		  AND download_active > 0
		LIMIT 1";

		if(!$sql->gen($query))
		{
			return;
		}

		$this->rows = $sql->fetch();

		if (isset($_POST['report_download']))
		{
			define("e_PAGETITLE", LAN_PLUGIN_DOWNLOAD_NAME." / ".LAN_dl_45);
			return null;
		}

		$download_name = e107::getParser()->toDB($this->rows['download_name']);
		define("e_PAGETITLE", LAN_PLUGIN_DOWNLOAD_NAME." / ".LAN_dl_45." / ".$download_name);

	}



	/**
	 * @return null
	 */
	private function loadList()
	{
		if($dlrow = $this->getCategory($this->qry['id']))
		{
			define("e_PAGETITLE", LAN_PLUGIN_DOWNLOAD_NAME." / ".$dlrow['download_category_name']);
		}
		else
		{  // No access to this category
			define("e_PAGETITLE", LAN_PLUGIN_DOWNLOAD_NAME);
		}

		return null;
	}


	/**
	 * @return null
	 */
	private function loadView()
	{
		if(deftrue('BOOTSTRAP')) // v2.x
		{
			$this->template = e107::getTemplate('download','download','view');
		}
		else // Legacy v1.x
		{
			$template_name = 'download_template.php';

			$DOWNLOAD_VIEW_TABLE_START = null;
			$DOWNLOAD_VIEW_TABLE		= null;
			$DOWNLOAD_VIEW_TABLE_END	= null;
			$DL_VIEW_NEXTPREV			= null;
			$DL_VIEW_PAGETITLE			= null;
			$DL_VIEW_CAPTION			= null;

			if (is_readable(THEME."templates/".$template_name))
			{
				require_once(THEME."templates/".$template_name);
			}
			elseif (is_readable(THEME.$template_name))
			{
				require_once(THEME.$template_name);
			}
			else
			{
				require_once(e_PLUGIN."download/templates/".$template_name);
			}

			$this->template['start']      = $DOWNLOAD_VIEW_TABLE_START;
			$this->template['item']       = $DOWNLOAD_VIEW_TABLE;
			$this->template['end']        = $DOWNLOAD_VIEW_TABLE_END;
			$this->template['nextprev']   = $DL_VIEW_NEXTPREV;
			$this->template['pagetitle']  = $DL_VIEW_PAGETITLE;
			$this->template['caption']    = varset($DL_VIEW_CAPTION,"{DOWNLOAD_VIEW_CAPTION}");

		}

		if(empty($this->template['newprev']))
		{
	    	$this->template['newprev'] = "
			<div style='text-align:center'>
				<table style='".USER_WIDTH."'>
				<tr>
				<td style='width:40%;'>{DOWNLOAD_VIEW_PREV}</td>
				<td style='width:20%; text-align: center;'>{DOWNLOAD_BACK_TO_LIST}</td>
				<td style='width:40%; text-align: right;'>{DOWNLOAD_VIEW_NEXT}</td>
				</tr>
				</table>
				</div>
				";
	   }

		if(empty($this->template['pagetitle']))
		{
	    	$this->template['pagetitle'] = "{DOWNLOAD_VIEW_NAME} / {DOWNLOAD_CATEGORY} / ".LAN_PLUGIN_DOWNLOAD_NAME;
		}


		// load data

		$sql = e107::getDb();
		$gen = new convert;

		/** @var download_shortcodes $sc */
		$sc = e107::getScBatch('download',true);
		$sc->wrapper('download/view');
		$sc->breadcrumb();
		$sc->qry 	= $this->qry;

		$highlight_search = FALSE;
		if (isset($_POST['highlight_search']))
		{
			$highlight_search = TRUE;
		}

	    $query = "
			SELECT d.*, dc.* FROM #download AS d
			LEFT JOIN #download_category AS dc ON d.download_category = dc.download_category_id
			WHERE d.download_id = {$this->qry['id']} AND d.download_active > 0
			AND d.download_visible IN (".USERCLASS_LIST.")
			AND dc.download_category_class IN (".USERCLASS_LIST.")
			LIMIT 1";

		if(!$sql->gen($query))
		{
			return null;
		}

		if(!defined("DL_IMAGESTYLE"))
		{
			define("DL_IMAGESTYLE","border:0px");
		}

		$dlrow = $sql->fetch();

		$sc->parent = $this->getParent($dlrow['download_category_id']);

		if(!empty( $sc->parent['download_category_parent']))
		{
			$sc->grandparent = $this->getParent( $sc->parent['download_category_id']);
		}


		$sc->setVars($dlrow);
		$this->setMeta($dlrow);



		$this->sc = $sc;
		$this->rows = $dlrow;

		// set Page Title;

		$DL_TITLE = e107::getParser()->parseTemplate($this->template['pagetitle'], true, $sc);

		define("e_PAGETITLE", $DL_TITLE);

		return null;
	}


	/**
	 * Render a single download
	 * @todo cache
	 */
	private function renderView()
	{

		$tp = e107::getParser();
		$ns = e107::getRender();
		/** @var download_shortcodes $sc */
		$sc = $this->sc;


		// @see: #3056 fixes fatal error in case $sc is empty (what happens, if no record was found)
		$count = empty($sc) ? 0 : $sc->getVars();

		if(empty($count))
		{
			return $ns->tablerender(LAN_PLUGIN_DOWNLOAD_NAME, "<div style='text-align:center'>".LAN_NO_RECORDS_FOUND."</div>", 'download-view', true);
		}

		$sc->breadcrumb();

		$DL_TEMPLATE = $this->template['start'].$this->template['item'].$this->template['end'];
		
		$text = $tp->parseTemplate($this->templateHeader, TRUE, $sc);
		$text .= $tp->parseTemplate($DL_TEMPLATE, TRUE, $sc);

		
			// ------- Next/Prev -----------
	   	$text .= $tp->parseTemplate($this->template['nextprev'], TRUE, $sc);
		$caption = $tp->parseTemplate($this->template['caption'], TRUE, $sc);
		
		$text .= $tp->parseTemplate($this->templateFooter, TRUE, $sc);
		
		$ret = $ns->tablerender($caption, $text, 'download-view', true);
	
		unset($text);

		$dlrow = $this->rows;
	
		if ($dlrow['download_comment']) 
		{			
			$comments = e107::getComment()->compose_comment("download", "comment", $dlrow['download_id'], null, $dlrow['download_name'], FALSE, true);
			$ret .= $ns->tablerender($comments['caption'], $comments['comment'].$comments['comment_form'], 'download-comments', true);
		}	
		
		
		
	//	print_a($comments);
		
		return $ret;
		
	}










	
	/**
	 * Render a list of files in a particular download category. 
	 * 
	 */
	private function renderList()
	{
		
		if(deftrue('BOOTSTRAP')) // v2.x 
		{
			$template = e107::getTemplate('download','download');
			
			$DOWNLOAD_LIST_CAPTION 		= $template['list']['caption'];	
			$DOWNLOAD_LIST_TABLE_START 	= $template['list']['start'];	
			$DOWNLOAD_LIST_TABLE 		= $template['list']['item'];
			$DOWNLOAD_LIST_TABLE_END 	= $template['list']['end'];		
			$DOWNLOAD_LIST_NEXTPREV		= $template['list']['nextprev'];
			
			$DOWNLOAD_CAT_TABLE_START 	= varset($template['categories']['start']);
		//	$DOWNLOAD_CAT_PARENT_TABLE	= $template['categories']['parent'];
		//	$DOWNLOAD_CAT_CHILD_TABLE	= $template['categories']['child'];
			$DOWNLOAD_CAT_SUBSUB_TABLE	= $template['categories']['subchild'];
			$DOWNLOAD_CAT_TABLE_END		= varset($template['categories']['end']);
		}
		else // Legacy v1.x 
		{
			$template_name = 'download_template.php';	
			
			if (is_readable(THEME."templates/".$template_name))
			{
				require_once(THEME."templates/".$template_name);
			}
			elseif (is_readable(THEME.$template_name))
			{
				require_once(THEME.$template_name);
			}
			else
			{
				require_once(e_PLUGIN."download/templates/".$template_name);
			}	
		}
		
		
		$sql = e107::getDb('dlrow');
		$tp = e107::getParser();
		$ns = e107::getRender();
		$pref = e107::getPref();
		

		/** @var download_shortcodes $sc */
		$sc = e107::getScBatch('download',true);
		$sc->wrapper('download/list');

		$sc->qry 	= $this->qry;

		

		//if (!isset($this->qry['from'])) $this->qry['from'] = 0;

	      // Get category type, page title
	//	if ($sql->select("download_category", "download_category_name,download_category_sef,download_category_description,download_category_parent,download_category_class", "(download_category_id='{$this->qry['id']}') AND (download_category_class IN (".USERCLASS_LIST."))") )

		if($dlrow = $this->getCategory($this->qry['id']))
		{
	   	  //  $dlrow = $sql->fetch();

			$catRows = $dlrow;
	   	   $sc->setVars($dlrow);	// Used below for header / breadcrumb.

	   	   $sc->parent = $this->getParent($this->qry['id']);

	   	   if(!empty( $sc->parent['download_category_parent']))
		   {
		        $sc->grandparent = $this->getParent( $sc->parent['download_category_id']);
		   }

			$sc->breadcrumb();

	   //	   $type = $dlrow['download_category_name'];


		   $this->qry['name'] = $dlrow['download_category_sef'];
		   
	   //	   define("e_PAGETITLE", LAN_PLUGIN_DOWNLOAD_NAME." / ".$dlrow['download_category_name']);
		}
		else
		{  // No access to this category
	   	//   define("e_PAGETITLE", LAN_PLUGIN_DOWNLOAD_NAME);
	   	   return $ns->tablerender(LAN_PLUGIN_DOWNLOAD_NAME, "<div class='alert alert-info' style='text-align:center'>".LAN_NO_RECORDS_FOUND."</div>",'download-list',true);
		}
		
		if ($dlrow['download_category_parent'] == 0)  // It's a main category - change the listing type required
	      { 
	      //   $action = 'maincats';
	  // 	  	 $maincatval = $id;
		}
		
		$dl_text = $tp->parseTemplate($this->templateHeader, TRUE, $sc);
						
		$total_downloads = $sql->count("download", "(*)", "WHERE download_category = '{$this->qry['id']}' AND download_active > 0 AND download_visible REGEXP '" . e_CLASS_REGEXP . "'");
		
		
		/* SHOW SUBCATS ... */
		if($this->getChildren($this->qry['id']))
		{

			/* there are subcats - display them ... */
			$qry = "
			SELECT dc.*, dc2.download_category_name AS parent_name, dc2.download_category_icon as parent_icon, SUM(d.download_filesize) AS d_size,
			COUNT(d.download_id) AS d_count,
			MAX(d.download_datestamp) as d_last,
			SUM(d.download_requested) as d_requests
			FROM #download_category AS dc
			LEFT JOIN #download AS d ON dc.download_category_id = d.download_category AND d.download_active > 0 AND d.download_visible IN (" . USERCLASS_LIST . ")
			LEFT JOIN #download_category as dc2 ON dc2.download_category_id='{$this->qry['id']}'
			WHERE dc.download_category_class IN (" . USERCLASS_LIST . ") AND dc.download_category_parent='{$this->qry['id']}'
			GROUP by dc.download_category_id ORDER by dc.download_category_order
			";
			
			if($sql->gen($qry))
			{
				
				$scArray = $sql->db_getList();

				$subText = "";
								
				/** @DEPRECATED **/
			//	if(!defined("DL_IMAGESTYLE"))
			//	{
			//		define("DL_IMAGESTYLE", "border:1px solid blue");
			//	}
	
				$download_cat_table_string = "";

				if(!empty($DOWNLOAD_CAT_TABLE_PRE)) // 0.8 BC Fix.
				{
					$subText .= $tp->parseTemplate($DOWNLOAD_CAT_TABLE_PRE, TRUE, $sc);
				}
				$subText .= $tp->parseTemplate($DOWNLOAD_CAT_TABLE_START, TRUE, $sc);
				
				foreach($scArray as $dlsubsubrow)
				{
					$sc->dlsubsubrow = $dlsubsubrow;
					$sc->dlsubrow = $dlsubsubrow;
					$subText .= $tp->parseTemplate($DOWNLOAD_CAT_SUBSUB_TABLE, TRUE, $sc);
					
				}
				
				$subText .= $tp->parseTemplate($DOWNLOAD_CAT_TABLE_END, TRUE, $sc);
				
		 	 //  $dl_text .= $ns->tablerender($dl_title, $subText, 'download-list', true);
		 	     $dl_text .=  $subText;
			}
			
		}// End of subcategory display

		// Now display individual downloads
		$download_category_class = 0;
		
		if(!check_class($download_category_class))
		{
			$ns->tablerender(LAN_PLUGIN_DOWNLOAD_NAME, "<div class='alert alert-info'>	" . LAN_NO_RECORDS_FOUND . "</div>");
			return null;
		}

		if($total_downloads < $this->qry['view'])
		{
			$this->qry['from'] = 0;
		}

		if(!defined("DL_IMAGESTYLE"))
		{
			define("DL_IMAGESTYLE", "border:1px solid blue");
		}

		require_once (e_HANDLER . "rate_class.php");
		$dltdownloads = 0;

		// $this->qry['from'] - first entry to show  (note - can get reset due to reuse of query,
		// even if values overridden this time)
		// $this->qry['view'] - number of entries per page
		// $total_downloads - total number of entries matching search criteria
		$filetotal = $sql->select("download", "*", "download_category='{$this->qry['id']}' AND download_active > 0 AND download_visible IN (" . USERCLASS_LIST . ") ORDER BY download_{$this->qry['order']} {$this->qry['sort']} LIMIT {$this->qry['from']}, ".$this->qry['view']);


		$caption = varset($DOWNLOAD_LIST_CAPTION) ? $tp->parseTemplate($DOWNLOAD_LIST_CAPTION, TRUE, $sc) : LAN_PLUGIN_DOWNLOAD_NAME;

		if(empty($filetotal))
		{

			$dl_text .= $tp->parseTemplate($this->templateFooter, TRUE, $sc);
			return ($dl_text) ? $ns->tablerender($caption, $dl_text, 'download-list', true) : '';
		}


		// Only show list if some files in it
		$dl_text .= $tp->parseTemplate($DOWNLOAD_LIST_TABLE_START, TRUE, $sc);
			
		global $dlft, $dltdownloads;
			
		$dlft = ($filetotal < $this->qry['view'] ? $filetotal: $this->qry['view']);

		$current_row = 1;

		while($dlrow = $sql->fetch())
		{
				$sc->setVars($dlrow);

				$agreetext = $tp->toHTML($pref['agree_text'], TRUE, 'DESCRIPTION');
				$current_row = ($current_row) ? 0: 1;
				// Alternating CSS for each row.(backwards compatible)
				$template = ($current_row == 1)? $DOWNLOAD_LIST_TABLE: str_replace("forumheader3", "forumheader3 forumheader3_alt", $DOWNLOAD_LIST_TABLE);
				
				$dltdownloads += $dlrow['download_requested'];
				
				$dl_text .= $tp->parseTemplate($template, TRUE, $sc);
		}

		$dl_text .= $tp->parseTemplate($DOWNLOAD_LIST_TABLE_END, TRUE, $sc);
		$dl_text .= $tp->parseTemplate($this->templateFooter, TRUE, $sc);

		$text = $ns->tablerender($caption, $dl_text, 'download-list', true);


		if(!isset($DOWNLOAD_LIST_NEXTPREV))
		{
			$sc_style['DOWNLOAD_LIST_NEXTPREV']['pre'] = "
			<div class='nextprev'>
				";
						$sc_style['DOWNLOAD_LIST_NEXTPREV']['post'] = "
			</div>";
			
						$DOWNLOAD_LIST_NEXTPREV = "
			<div style='text-align:center;margin-left:auto;margin-right:auto'>
				{DOWNLOAD_BACK_TO_CATEGORY_LIST}
				<br />
				<br />
				{DOWNLOAD_LIST_NEXTPREV}
			</div>";
		}


		$nextprevQry = $this->qry;
		$nextprevQry['from'] = '[FROM]';
		unset($nextprevQry['id'],$nextprevQry['name'],$nextprevQry['action']);

		$newUrl = e107::url('download','category',$catRows, array('query'=>$nextprevQry));

		$nextprev = array(
				'tmpl_prefix'	=>'default',
				'total'			=> $total_downloads,
				'amount'		=> intval($this->qry['view']),
				'current'		=> $this->qry['from'],
				'url'			=> urldecode($newUrl)
		);

		global $nextprev_parms;
	
		$nextprev_parms  = http_build_query($nextprev,false,'&'); // 'tmpl_prefix='.deftrue('NEWS_NEXTPREV_TMPL', 'default').'&total='. $total_downloads.'&amount='.$amount.'&current='.$newsfrom.$nitems.'&url='.$url;

		$text .= $tp->parseTemplate($DOWNLOAD_LIST_NEXTPREV, TRUE, $sc);	

		return $text;
		
	}



	/**
	 * Render a 'Broken Download' Report form. 
	 */
	private function renderReport()
	{
		$sql 	= e107::getDb();
		$tp 	= e107::getParser();
		$ns 	= e107::getRender();
		$frm 	= e107::getForm();
		$pref 	= e107::getPref();
		$mes 	= e107::getMessage();
						
		$dlrow = $this->rows;
	
		// Check if user is allowed to make reports, and if user is allowed to view the actual download item
		if(!check_class($dlrow['download_class']) || !check_class($pref['download_reportbroken']))
		{	
			$mes->addError(LAN_dl_79);
			return $ns->tablerender(LAN_PLUGIN_DOWNLOAD_NAME, $mes->render(), 'download-report', true);
		}

		$download_name 	= $tp->toDB($dlrow['download_name']);
		$download_sef 	= $dlrow['download_sef'];
		$download_id 	= (int) $dlrow['download_id'];

		$breadcrumb 	= array();
		$breadcrumb[]	= array('text' => LAN_PLUGIN_DOWNLOAD_NAME,			'url' => e107::url('download','index', $dlrow));
		$breadcrumb[]	= array('text' => $dlrow['download_category_name'],	'url' => e107::url('download','category', $dlrow)); 
		$breadcrumb[]	= array('text' => $dlrow['download_name'],			'url' => e107::url('download','item', $dlrow)); 
		$breadcrumb[]	= array('text' => LAN_dl_45,						'url' => null);

		e107::breadcrumb($breadcrumb);
	
		if (isset($_POST['report_download'])) 
		{
			$report_add = $tp->toDB($_POST['report_add']);
			$user 		= USER ? USERNAME : LAN_GUEST;
			$ip 		= e107::getIPHandler()->getIP(false); 
		
			// Replaced by e_notify 
			/*
			if ($pref['download_email']) 
			{    // this needs to be moved into the NOTIFY, with an event.
				require_once(e_HANDLER."mail.php");
				$subject = LAN_dl_60." ".SITENAME;
				$report = LAN_dl_58." ".SITENAME.":\n".(substr(SITEURL, -1) == "/" ? SITEURL : SITEURL."/")."download.php?view.".$download_id."\n
				".LAN_dl_59." ".$user."\n".$report_add;
				sendemail(SITEADMINEMAIL, $subject, $report);
			}*/

			$brokendownload_data = array(
				'download_id' 	=> $download_id,
				'download_sef'  => $download_sef,
				'download_name' => $download_name, 
				'report_add' 	=> $report_add,
				'user'			=> $user,
				'ip'			=> $ip, 
			);

			e107::getEvent()->trigger('user_download_brokendownload_reported', $brokendownload_data);

			$sql->insert('generic', "0, 'Broken Download', ".time().",'".USERID."', '{$download_name}', {$download_id}, '{$report_add}'");
	

			$text = $frm->breadcrumb($breadcrumb);
	
			$text .= "<div class='alert alert-success'>".LAN_dl_48."</div>
			<a class='btn btn-primary' href='".e107::url('download','item', $dlrow)."'>".LAN_dl_49."</a>";

			return $ns->tablerender(LAN_PLUGIN_DOWNLOAD_NAME, $text, 'download-report', true);
		}
		else 
		{
			$text = $frm->breadcrumb($breadcrumb);


			$formUrl = e107::url('download', 'report', $dlrow);
			$fileUrl = e107::url('download', 'view', $dlrow);

			$text .= "<form action='".$formUrl."' method='post'>
			   <div class='form-group'>
			   	     <p> ".LAN_DOWNLOAD.": <a href='".$fileUrl."'>".$download_name."</a></p>
			        <p>".LAN_dl_54."<br />".LAN_dl_55."</p>
			  </div>
			   <div class='form-group clearfix'> ".$frm->textarea('report_add', '')."</div>
				<div class='form-group text-center'>
					".$frm->button('report_download',LAN_dl_45,'submit')."
				</div>
		   </form>";
		   	  
			return $ns->tablerender(LAN_PLUGIN_DOWNLOAD_NAME, $text, 'download-report', true);
		}
	}
				
			
	/**
	 * Render Download Mirrors for the selected file. 
	 */
	private function renderMirror()
	{
		
		$sql = e107::getDb();
		$tp = e107::getParser();
		$ns = e107::getRender();
		$pref = e107::getPref();
		
		if(deftrue('BOOTSTRAP')) // v2.x 
		{
			$template = e107::getTemplate('download','download','mirror');
			
			$DOWNLOAD_MIRROR_START 		= $template['start'];	
			$DOWNLOAD_MIRROR	 		= $template['item'];
			$DOWNLOAD_MIRROR_END 		= $template['end'];		

		}
		else // Legacy v1.x 
		{
			$template_name = 'download_template.php';	
			
			if (is_readable(THEME."templates/".$template_name))
			{
				require_once(THEME."templates/".$template_name);
			}
			elseif (is_readable(THEME.$template_name))
			{
				require_once(THEME.$template_name);
			}
			else
			{
				require_once(e_PLUGIN."download/templates/".$template_name);
			}	
		
		}

		/** @var download_shortcodes $sc */
		$sc = e107::getScBatch('download',true);
		$sc->wrapper('download/mirror');
		$sc->breadcrumb();
		$sc->qry 	= $this->qry;
		
	//	$load_template = 'download_template';
	//	if (!isset($DOWNLOAD_MIRROR_START)) eval($template_load_core);
	
		$sql->select("download_mirror");
		$mirrorList = $sql->db_getList("ALL", 0, 200, "mirror_id");
	
	    $query = "
			SELECT d.*, dc.* FROM #download AS d
			LEFT JOIN #download_category AS dc ON d.download_category = dc.download_category_id
			WHERE d.download_id = ".$this->qry['id']."
			LIMIT 1";
	
	//	global $dlmirrorfile, $dlmirror;	
	
	
		if($sql->gen($query))
		{
			$dlrow = $sql->fetch();
		//	$dlrow['mirrorlist'] = $mirrorList;
			$sc->setVars($dlrow);

			
			$array = explode(chr(1), $dlrow['download_mirror']);
			
			if (2 == varset($pref['mirror_order']))
			{
	         // Order by name, sort array manually
				usort($array, "sort_download_mirror_order");
			}
	      //elseif (1 == varset($pref['mirror_order']))
	      //{
	      //   // Order by ID  - do nothing order is as stored in DB
	      //}
			elseif (0 == varset($pref['mirror_order'], 0))
			{
				   // Shuffle the mirror list into a random order
				   $c = count($array);
				   for ($i=1; $i<$c; $i++)
				   {
				     $d = mt_rand(0, $i);
				     $tmp = $array[$i];
				     $array[$i] = $array[$d];
				     $array[$d] = $tmp;
				   }
			}
	
			$dl_text = $tp->parseTemplate($this->templateHeader, TRUE, $sc);
	
		   	$dl_text .= $tp->parseTemplate($DOWNLOAD_MIRROR_START, TRUE, $sc);
			$download_mirror = 1;
				
		
				
			foreach($array as $mirrorstring)
			{
				if($mirrorstring)
				{
					$dlmirrorfile = explode(",", $mirrorstring);
				//	$dlmirror = $mirrorList[$dlmirrorfile[0]];
					
					$sc->mirror['dlmirrorfile'] = $dlmirrorfile;
					$sc->mirror['dlmirror'] = $mirrorList[$dlmirrorfile[0]];
					
					$dl_text .= $tp->parseTemplate($DOWNLOAD_MIRROR, TRUE, $sc);
				}
			}
			   
			$dl_text .= $tp->parseTemplate($DOWNLOAD_MIRROR_END, TRUE, $sc);
			
			$dl_text .= $tp->parseTemplate($this->templateFooter, TRUE, $sc);
			
		   	return $ns->tablerender(LAN_PLUGIN_DOWNLOAD_NAME, $dl_text, 'download-mirror', true);	
		
		
		}


	}


	/**
	 * Render Download Errors. 
	 */
	private function renderError()
	{
		$ns = e107::getRender();
		$pref = e107::getPref();
		$tp = e107::getParser();
		
		$sc = e107::getScBatch('download',true);
		
		$header = $tp->parseTemplate($this->templateHeader,true, $sc);
		$footer = $tp->parseTemplate($this->templateFooter,true, $sc);
		
		switch ($this->qry['error'])
		{
			case 1 :	// No permissions
	            if (strlen($pref['download_denied']) > 0) 
	            {
					$errmsg = $tp->toHTML($pref['download_denied'],true, 'DESCRIPTION');
	   	      	} 
	   	      	else 
	   	      	{
					$errmsg = LAN_dl_63;
				}
			break;
			  
			case 2 :	// Quota exceeded
				$errmsg = LAN_dl_62;
			break;
			
			default: // Generic error - shouldn't happen
	   	     $errmsg = LAN_ERROR." ".$this->qry['error'];		
		}
		
		return $ns->tablerender(LAN_PLUGIN_DOWNLOAD_NAME, $header. "<div class='alert alert-error alert-danger alert-block' style='text-align:center'>".$errmsg."</div>". $footer, 'download-error', true);
		
	}
   
   
   
   
   function getCategorySelectList($currentID=0, $incSubSub=true, $groupOnMain=true, $blankText="&nbsp;", $name="download_category")
   {
      global $sql,$parm;
     	$boxinfo = "\n";
     	$qry = "
        	SELECT dc.download_category_name, dc.download_category_order, dc.download_category_id, dc.download_category_parent,
        	dc1.download_category_parent AS d_parent1
        	FROM #download_category AS dc
        	LEFT JOIN #download_category as dc1 ON dc1.download_category_id=dc.download_category_parent AND dc1.download_category_class IN (".USERCLASS_LIST.")
         LEFT JOIN #download_category as dc2 ON dc2.download_category_id=dc1.download_category_parent ";
      if (ADMIN === FALSE) $qry .= " WHERE dc.download_category_class IN (".USERCLASS_LIST.") ";
      $qry .= " ORDER by dc2.download_category_order, dc1.download_category_order, dc.download_category_order";   // This puts main categories first, then sub-cats, then sub-sub cats
      if (!$sql->gen($qry))
      {
        	return "Error reading categories<br />";
        	exit;
      }
      $boxinfo .= "<select name='{$name}' id='download_category' class='tbox form-control'>
      	<option value=''>{$blankText}</option>\n";
      // Its a structured display option - need a 2-step process to create a tree
      $catlist = array();
      while ($dlrow = $sql->fetch())
      {
         $tmp = $dlrow['download_category_parent'];
        	if ($tmp == '0')
        	{
       	$dlrow['subcats'] = array();
          	$catlist[$dlrow['download_category_id']] = $dlrow;
        	}
        	else
        	{
          	if (isset($catlist[$tmp]))
       	   {  // Sub-Category
            	$catlist[$tmp]['subcats'][$dlrow['download_category_id']] = $dlrow;
            	$catlist[$tmp]['subcats'][$dlrow['download_category_id']]['subsubcats'] = array();
       	   }
       	   else
       	   {  // Its a sub-sub category
            	if (isset($catlist[$dlrow['d_parent1']]['subcats'][$tmp]))
            	{
             		$catlist[$dlrow['d_parent1']]['subcats'][$tmp]['subsubcats'][$dlrow['download_category_id']] = $dlrow;
            	}
       	   }
        	}
      }
  		// Now generate the options
      foreach ($catlist as $thiscat)
      {  // Main categories
         if (count($thiscat['subcats']) > 0)
         {
            if ($groupOnMain)
            {
            	$boxinfo .= "<optgroup label='".htmlspecialchars($thiscat['download_category_name'])."'>";
             	$scprefix = '';
            }
            else
            {
            	$boxinfo .= "<option value='".$thiscat['download_category_id']."'";
            	if ($currentID == $thiscat['download_category_id']) {
            	   $boxinfo .= " selected='selected'";
            	}
               $boxinfo .= ">".htmlspecialchars($thiscat['download_category_name'])."</option>\n";
             	$scprefix = '&nbsp;&nbsp;&nbsp;';
            }
            foreach ($thiscat['subcats'] as $sc)
            {  // Sub-categories
            	$sscprefix = '--> ';
            	$boxinfo .= "<option value='".$sc['download_category_id']."'";
            	if ($currentID == $sc['download_category_id']) {
            	   $boxinfo .= " selected='selected'";
            	}
               $boxinfo .= ">".$scprefix.htmlspecialchars($sc['download_category_name'])."</option>\n";
               if ($incSubSub)
               {  // Sub-sub categories
               	foreach ($sc['subsubcats'] as $ssc)
               	{
                 		$boxinfo .= "<option value='".$ssc['download_category_id']."'";
                 		if ($currentID == $ssc['download_category_id']) { $boxinfo .= " selected='selected'"; }
                 		$boxinfo .= ">".htmlspecialchars($sscprefix.$ssc['download_category_name'])."</option>\n";
               	}
               }
            }
            if ($groupOnMain)
            {
               $boxinfo .= "</optgroup>\n";
            }
         }
         else
         {
         	$sel = ($currentID == $thiscat['download_category_id']) ? " selected='selected'" : "";
           	$boxinfo .= "<option value='".$thiscat['download_category_id']."' {$sel}>".htmlspecialchars($thiscat['download_category_name'])."</option>\n";
         }
      }
      $boxinfo .= "</select>\n";
      return $boxinfo;
   }
}

function sort_download_mirror_order($a, $b)
{
   $a = explode(",", $a);
   $b = explode(",", $b);
   if ($a[1] == $b[1]) {
      return 0;
   }
   return ($a[1] < $b[1]) ? -1 : 1;
}
?>
