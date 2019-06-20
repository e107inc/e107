<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * News handler
 *
*/

/**
 *
 * @package     e107
 * @subpackage	e107_handlers
 * @author      e107inc
 *
 * Classes:
 * news - old news class
 * e_news_item - news data model - the future
 * e_news_tree - news items collection
 * e_news_category_item - news category data model
 * e_news_category_tree - news category items collection
 */

if (!defined('e107_INIT')) { exit; }

class news {

	protected static $_rewrite_data = array();
	protected static $_rewrite_map = null;

	//FIXME - LANs
	//TODO - synch WIKI docs, add rewrite data to the event data
	//@Deprecated and no longer used by newspost.php 
	function submit_item($news, $smessages = false)
	{
		$tp = e107::getParser();
		$sql = e107::getDb();
		$admin_log = e107::getAdminLog();
		$pref = e107::getPref();
		$e_event = e107::getEvent();
		$e107cache = e107::getCache();
		$emessage = e107::getMessage();
		

		$error = false;
		if(empty($news['news_title']))
		{
			$error = true;
			$message = LAN_ERROR_47;
			$emessage->add(LAN_ERROR_47, E_MESSAGE_ERROR, $smessages);
			if(!empty($news['news_sef']))
			{
				$news['news_sef'] = eHelper::secureSef($news['news_sef']);
			}
		}
		else
		{
			// first format sef...
			if(empty($news['news_sef']))
			{
				$news['news_sef'] = eHelper::title2sef($news['news_title']);
			}
			else 
			{
				$news['news_sef'] = eHelper::secureSef($news['news_sef']);
			}
		}
		
		// ...then check it
		if(empty($news['news_sef']))
		{
			$error = true;
			$message = LAN_ERROR_48;
			$emessage->add(LAN_ERROR_48, E_MESSAGE_ERROR, $smessages);
		}
		elseif($sql->db_Count('news', '(news_id)', ($news['news_sef'] ? 'news_id<>'.intval($news['news_id']).' AND ' : '')."news_sef='".$tp->toDB($news['news_sef'])."'"))
		{
			$error = true;
			$message = LAN_ERROR_49;
			$emessage->add(LAN_ERROR_49, E_MESSAGE_ERROR, $smessages);
		}

		if(empty($news['news_category']))
		{
			$error = true;
			$message = LAN_ERROR_50;
			$emessage->add(LAN_ERROR_50, E_MESSAGE_ERROR, $smessages);
		}


		$data = array();
		//DB Array
		$data['data']['news_title'] = $news['news_title'];
		$data['_FIELD_TYPES']['news_title'] = 'todb';
		
		$data['data']['news_sef'] = $news['news_sef'];
		$data['_FIELD_TYPES']['news_sef'] = 'todb';

		$data['data']['news_body'] = $news['news_body'];
		$data['_FIELD_TYPES']['news_body'] = 'todb';

		$data['data']['news_extended'] = $news['news_extended'];
		$data['_FIELD_TYPES']['news_extended'] = 'todb';

		$data['data']['news_datestamp'] = $news['news_datestamp'];
		$data['_FIELD_TYPES']['news_datestamp'] = 'int';

		$data['data']['news_author'] = $news['news_author'] ? $news['news_author'] : USERID;
		$data['_FIELD_TYPES']['news_author'] = 'int';

		$data['data']['news_category'] = $news['news_category'];
		$data['_FIELD_TYPES']['news_category'] = 'int';

		$data['data']['news_allow_comments'] = $news['news_allow_comments'];
		$data['_FIELD_TYPES']['news_allow_comments'] = 'int';

		$data['data']['news_start'] = $news['news_start'];
		$data['_FIELD_TYPES']['news_start'] = 'int';

		$data['data']['news_end'] = $news['news_end'];
		$data['_FIELD_TYPES']['news_end'] = 'int';

		$data['data']['news_class'] = $news['news_class'];
		$data['_FIELD_TYPES']['news_class'] = 'todb';

		$data['data']['news_render_type'] = $news['news_render_type'];
		$data['_FIELD_TYPES']['news_render_type'] = 'todb';


		//news_comment_total

		$data['data']['news_summary'] = $news['news_summary'];
		$data['_FIELD_TYPES']['news_summary'] = 'todb';

		$data['data']['news_thumbnail'] = $news['news_thumbnail'];
		$data['_FIELD_TYPES']['news_thumbnail'] = 'todb';

		$data['data']['news_sticky'] = $news['news_sticky'];
		$data['_FIELD_TYPES']['news_sticky'] = 'int';

		$data['data']['news_meta_keywords'] = eHelper::formatMetaKeys($news['news_meta_keywords']);
		$data['_FIELD_TYPES']['news_meta_keywords'] = 'todb';

		$data['data']['news_meta_description'] = eHelper::formatMetaDescription($news['news_meta_description']); //handle bbcodes
		$data['_FIELD_TYPES']['news_meta_description'] = 'todb';

		if($error)
		{
			$data['error'] = true;
			return $data;
		}

		
		// Calculate short strings for admin logging - no need to clog up the log with potentially long items
		$logData = $data['data'];
		if (isset($logData['news_body'])) $logData['news_body'] = $tp->text_truncate($tp->toDB($logData['news_body']),300,'...');
		if (isset($logData['news_extended'])) $logData['news_extended'] = $tp->text_truncate($tp->toDB($logData['news_extended']),300,'...');

		//XXX - Now hooks are executed only if no mysql error is found. Should it stay so? Seems sensible to me!
		if ($news['news_id'])
		{
			// Updating existing item
			$data['WHERE'] = 'news_id='.intval($news['news_id']);

			//$vals = "news_datestamp = '".intval($news['news_datestamp'])."', ".$author_insert." news_title='".$news['news_title']."', news_body='".$news['news_body']."', news_extended='".$news['news_extended']."', news_category='".intval($news['cat_id'])."', news_allow_comments='".intval($news['news_allow_comments'])."', news_start='".intval($news['news_start'])."', news_end='".intval($news['news_end'])."', news_class='".$tp->toDB($news['news_class'])."', news_render_type='".intval($news['news_rendertype'])."' , news_summary='".$news['news_summary']."', news_thumbnail='".$tp->toDB($news['news_thumbnail'])."', news_sticky='".intval($news['news_sticky'])."' WHERE news_id='".intval($news['news_id'])."' ";
			if ($sql->db_Update('news', $data))
			{
				
			
				e107::getAdminLog()->logArrayAll('NEWS_09', $logData);

		
				$data['data']['news_id'] = $news['news_id'];
	
				e107::getEvent()->trigger('newsupd', $data['data']);
				e107::getEvent()->trigger('admin_news_updated', $data['data']);
				$message = LAN_UPDATED;
				$emessage->add(LAN_UPDATED, E_MESSAGE_SUCCESS, $smessages);
				e107::getCache()->clear('news.php');



				//FIXME - triggerHook should return array(message, message_type)
				$evdata = array('method'=>'update', 'table'=>'news', 'id'=>$news['news_id'], 'plugin'=>'news', 'function'=>'submit_item');
				$emessage->add(e107::getEvent()->triggerHook($evdata), E_MESSAGE_INFO, $smessages);
			}
			else
			{
				if($sql->getLastErrorNumber())
				{
					$error = true;
					$emessage->add(LAN_NEWS_5, E_MESSAGE_ERROR, $smessages);
					$message = "<strong>".LAN_NEWS_5."</strong>";
				}
				else
				{
					$data['data']['news_id'] = $news['news_id'];
	
					$emessage->add(LAN_NO_CHANGE, E_MESSAGE_INFO, $smessages);
					$message = "<strong>".LAN_NO_CHANGE."</strong>";
					

					//FIXME - triggerHook should return array(message, message_type)
					$evdata = array('method'=>'update', 'table'=>'news', 'id'=>$news['news_id'], 'plugin'=>'news', 'function'=>'submit_item');
					$emessage->add(e107::getEvent()->triggerHook($evdata), E_MESSAGE_INFO, $smessages);
				}

			}
		}
		else
		{
			// Adding item
			$data['data']['news_id'] = $sql->db_Insert('news', $data);
			$news['news_id'] = $data['data']['news_id'];
			//$news['news_id'] = $sql ->db_Insert('news', "0, '".$news['news_title']."', '".$news['news_body']."', '".$news['news_extended']."', ".intval($news['news_datestamp']).", ".intval($news['news_author']).", '".intval($news['cat_id'])."', '".intval($news['news_allow_comments'])."', '".intval($news['news_start'])."', '".intval($news['news_end'])."', '".$tp->toDB($news['news_class'])."', '".intval($news['news_rendertype'])."', '0' , '".$news['news_summary']."', '".$tp->toDB($news['news_thumbnail'])."', '".intval($news['news_sticky'])."' ")
			if ($data['data']['news_id'])
			{
				$data['news_id'] = $news['news_id'];
				$message = LAN_NEWS_6;
				$emessage->add(LAN_CREATED, E_MESSAGE_SUCCESS, $smessages);
				e107::getCache()->clear('news.php');

				//moved down - prevent wrong mysql_insert_id
				e107::getAdminLog()->logArrayAll('NEWS_08', $logData);
				e107::getEvent()->trigger('newspost', $data['data']);
				e107::getEvent()->trigger('admin_news_created', $data['data']);

				//XXX - triggerHook after trigger?
				$evdata = array('method'=>'create', 'table'=>'news', 'id'=>$data['data']['news_id'], 'plugin'=>'news', 'function'=>'submit_item');
				$emessage->add($e_event->triggerHook($evdata), E_MESSAGE_INFO, $smessages);
			}
			else
			{
				$error = true;
				$message = "<strong>".LAN_NEWS_7."</strong>";
				$emessage->add(LAN_UPDATED, E_MESSAGE_ERROR, $smessages);
			}
		}



		//return $message;
		$data['message'] = $message;
		$data['error'] = $error;
		return $data;
	}
	
	function render_newsitem($news, $mode = 'default', $n_restrict = '', $NEWS_TEMPLATE = '', $param = array())
	{
		global $NEWSSTYLE, $NEWSLISTSTYLE;
		
		if ($override_newsitem = e107::getSingleton('override', true)->override_check('render_newsitem')) 
		{
			$result = call_user_func($override_newsitem, $news, $mode, $n_restrict, $NEWS_TEMPLATE, $param);
			if ($result == 'return')
			{
				return;
			}
		}

		if ($n_restrict == 'userclass')
		{
			$news['news_id'] 				= 0;
			$news['news_title'] 			= LAN_NEWS_1;
			$news['data'] 					= LAN_NEWS_2;
			$news['news_extended'] 			= "";
			$news['news_allow_comments'] 	= 1;
			$news['news_start'] 			= 0;
			$news['news_end'] 				= 0;
			$news['news_render_type'] 		= 0;
			$news['comment_total'] 			= 0;
		}

		$tmp = array();
		$tmp['caticon'] 				= defset('ICONSTYLE');
		$tmp['commentoffstring'] 		= defset('COMMENTOFFSTRING', '');
		$tmp['commentlink'] 			= defset('COMMENTLINK', e107::getParser()->toGlyph('fa-comment'));
		$tmp['trackbackstring'] 		= defset('TRACKBACKSTRING');
		$tmp['trackbackbeforestring'] 	= defset('TRACKBACKBEFORESTRING');
		$tmp['trackbackafterstring'] 	= defset('TRACKBACKAFTERSTRING');
		$tmp['itemlink'] 				= defset('NEWSLIST_ITEMLINK');
		$tmp['thumbnail'] 				= defset('NEWSLIST_THUMB', "border:0px");
		$tmp['catlink']  				= defset('NEWSLIST_CATLINK');
		$tmp['caticon'] 				= defset('NEWSLIST_CATICON', defset('ICONSTYLE'));

		if(!$param) $param = array();
		$param = array_merge($tmp, $param);


// Next three images aren't always defined by the caller, even if most of $param is.
//XXX All of this should be done via CSS from v2 on. 

		if (!isset($param['image_nonew_small']))
		{
		  if (!defined("IMAGE_nonew_small"))
		  {
			define("IMAGE_nonew_small", (file_exists(THEME."images/nonew_comments.png") ? "<img src='".THEME_ABS."images/nonew_comments.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/nonew_comments.png' alt=''  />"));
		  }
		  $param['image_nonew_small'] = IMAGE_nonew_small;
		}

		if (!isset($param['image_new_small']))
		{
		  if (!defined("IMAGE_new_small"))
		  {
			define("IMAGE_new_small", (file_exists(THEME."images/new_comments.png") ? "<img src='".THEME_ABS."images/new_comments.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/new_comments.png' alt=''  /> "));
		  }
		  $param['image_new_small'] = IMAGE_new_small;
		}

		if (!isset($param['image_sticky']))
		{
		  if (!defined("IMAGE_sticky"))
		  {
			define("IMAGE_sticky", (file_exists(THEME."images/sticky.png") ? "<img src='".THEME_ABS."images/sticky.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/sticky.png' alt='' style='width: 14px; height: 14px; vertical-align: bottom' /> "));
		  }
		  $param['image_sticky'] = IMAGE_sticky;
		}

		cachevars('current_news_item', $news);
		cachevars('current_news_param', $param);

		if ($news['news_render_type'] == 1 && $mode != "extend") 
		{
			if (function_exists("news_list")) 
			{
				$NEWS_PARSE = news_list($news);
			}
			else if ($NEWSLISTSTYLE) 
			{
				$NEWS_PARSE = $NEWSLISTSTYLE;
			} 
			else 
			{
				$NEWS_PARSE = "{NEWSICON}&nbsp;<b>{NEWSTITLELINK}</b><div class='smalltext'>{NEWSAUTHOR} ".LAN_NEWS_100." {NEWSDATE} | {NEWSCOMMENTS}</div>";
			}
		}
		else 
		{
			if ($NEWS_TEMPLATE) 
			{
				$NEWS_PARSE = $NEWS_TEMPLATE;
			} 
			else 
			{
				if (function_exists("news_style")) 
				{
					$action = varset($param['current_action'], 'default');
					$NEWS_PARSE = news_style($news, $action, $param);
				}
				else 
				{
					$NEWS_PARSE = $NEWSSTYLE;
				}
			}
		}
		//$loop_uid = $news['news_author']; - no references found

		//require_once(e_CORE.'shortcodes/batch/news_shortcodes.php');
		/* DEPRECATED
		setScVar('news_shortcodes', 'news_item', $news);
		setScVar('news_shortcodes', 'param', $param);
		*/

		// Set the Values for the social shortcode usage.
		$tp = e107::getParser();
		$socialArray = array('url'=>e107::getUrl()->create('news/view/item', $news, 'full=1'), 'title'=>$tp->toText($news['news_title']), 'tags'=>$news['news_meta_keywords']);
		$socialObj = e107::getScBatch('social');

		if(is_object($socialObj))
		{
			$socialObj->setVars($socialArray);
		}


		// Retrieve batch sc object, set required vars

		$wrapperKey = (!empty($param['template_key'])) ? $param['template_key'].'/item' : 'news/view/item';

		$editable = array(
			'table' => 'news',
			'pid'   => 'news_id',
			'vars'  => 'news_item',
			'perms' => 'H|H4',
			'shortcodes'    => array(
					'news_title'        => array('field'=>'news_title', 'type'=>'text', 'container'=>'span'),
					'news_description'  => array('field'=>'news_meta_description','type'=>'text', 'container'=>'span'),
					'news_body'         => array('field'=>'news_body', 'type'=>'html', 'container'=>'div'),
					'news_summary'      => array('field'=>'news_summary', 'type'=>'text', 'container'=>'span'),
			)

		);




		$sc = e107::getScBatch('news')
			->wrapper($wrapperKey)
			->setScVar('news_item', $news)
			->setScVar('param', $param)
			->editable($editable);


		$text = e107::getParser()->parseTemplate($NEWS_PARSE, true, $sc);

		if ($mode == 'return' || !empty($param['return']))
		{
			return $text;
		}
		else
		{
			echo $text;
			return TRUE;
		}
	}

	//@TDODO deprecated?
	function make_xml_compatible($original)
	{
		$original = e107::getParser()->toHTML($original, TRUE);
		$original = str_replace('&pound', '&amp;#163;', $original);
		$original = str_replace('&copy;', '(c)', $original);
		return htmlspecialchars($original, ENT_COMPAT, CHARSET);
	}


	/**
	 * Render a news Grid. (currently used in news_grid_menu ) // new v2.1.5
	 * @param array $parm
	 * @param string    $parm['caption']        text or constant
	 * @param integer   $parm['titleLimit']     number of chars fo news title
	 * @param integer   $parm['summaryLimit']   number of chars for new summary
	 * @param string    $parm['source']         latest (latest news items) | sticky (news items) | template (assigned to news-grid layout)
	 * @param integer   $parm['order']          n.news_datestamp DESC
	 * @param integer   $parm['limit']          10
	 * @param string   $parm['template']        default | or any key as defined in news_grid_template.php
	 *
	 * @return string
	 */
	function render_newsgrid($parm=null)
	{
		$cacheString = 'nq_news_grid_menu_'.md5(serialize($parm));

		$cached = e107::getCache()->retrieve($cacheString);

		if(false === $cached)
		{
			e107::plugLan('news');

			if(is_string($parm))
			{
				parse_str($parm, $parms);
			}
			else
			{
				$parms = $parm;

				e107::getDebug()->log($parms);
			}

			if(isset($parms['caption'][e_LANGUAGE]))
			{
				$parms['caption'] = $parms['caption'][e_LANGUAGE];
			}

			if(!empty($parms['caption']) && defined($parms['caption']))
			{
				$parms['caption'] = constant($parms['caption']);
			}

			/** @var e_news_tree $ntree */
			$ntree = e107::getObject('e_news_tree');

			if($legacyTemplate  = e107::getTemplate('news', 'news_menu', 'grid')) // BC
			{
				$template = $legacyTemplate;
				$parms['tmpl']      = 'news_menu';
				$parms['tmpl_key']  = 'grid';
			}
			else // New in v2.1.5
			{
				$tmpl = !empty($parms['layout']) ? $parms['layout'] : 'col-md-4';
				$template = e107::getTemplate('news', 'news_grid', $tmpl);
				$parms['tmpl']      = 'news_grid';
				$parms['tmpl_key']  = $tmpl;

			}

			if(empty($parms['mode']))
			{
				$parms['mode'] = 'news_grid_menu';
			}

		//	$gridSize       = vartrue($parms['layout'],'col-md-4');

			$parmSrch       = array(
								'{NEWSGRID}',
								'_titleLimit_',
								'_summaryLimit_'
							);

			$parmReplace    = array(
							//	$gridSize,
							//	vartrue($parms['titleLimit'], 0),
						//		vartrue($parms['summaryLimit'], 0)
							);

			$template = str_replace($parmSrch , '', $template); // clean up deprecated elements.

			$render = (empty($parms['caption'])) ? false: true;



			if(empty($parms['count']))
			{
				$parms['count'] = 3;
			}

			$parms['order']     = 'n.news_datestamp DESC';


			$treeparm = array();

			if(!empty($parms['count']))
			{
				 $treeparm['db_limit'] = '0, '.intval($parms['count']);
			}

			if(!empty($parms['limit']))
			{
				$treeparm['db_limit'] = '0, '.intval($parms['limit']);
			}

			if(!empty($parms['order']))
			{
				$treeparm['db_order'] = e107::getParser()->toDB($parms['order']);
			}

			$parms['return'] = true;

			if(varset($parms['source']) == 'template')
			{
				$treeparm['db_where']     = 'FIND_IN_SET(6, n.news_render_type)';
			}

			if(varset($parms['source']) == 'sticky')
			{
				$treeparm['db_where']     = 'n.news_sticky=1';
			}

			$cached = $ntree->loadJoinActive(vartrue($parms['category'], 0), false, $treeparm)->render($template, $parms, $render);

			e107::getCache()->set($cacheString, $cached);
		}

		return $cached;

	}


}


//EXPERIMENTAL

require_once(e_HANDLER.'model_class.php');

class e_news_item extends e_front_model
{
	protected $_db_table = 'news';
	protected $_field_id = 'news_id';
	protected $_cache_string = 'news_item_{ID}';

	/**
	 * Shortcodes - simple field getter (basic formatting)
	 * THIS IS ONLY TEST, maybe useful for fields requiring simple formatting - it's a way too complicated for designers,
	 * could be inner used inside the rest of news SCs.
	 *
	 * @param string $news_field name without the leading 'news_' prefix
	 * @param mixed $default
	 * @return string field value
	 */
	public function sc_news_field($parm = '')
	{
		$tmp = explode('|', $parm, 2);
		$field = $tmp[0];

		if(!is_array($parm))
		{
			parse_str(varset($tmp[1]), $parm);
		}
		$val = $this->field($field, '');

		//do more with $parm array, just an example here
		if(vartrue($parm['format']))
		{
			switch ($parm['format'])
			{
				//USAGE: {NEWS_FIELD=body|format=html&arg=1,BODY} -> callback e107->toHTML($value, true, 'BODY');
				case 'html':
					$method = 'toHTML';
					$callback = e107::getParser();
					$parm['arg'] = explode(',', varset($parm['arg']));
					$parm['arg'][0] = vartrue($parm['arg'][0]) ? true : false; //to boolean
					$params = array($val); //value is always the first callback argument
					$params += $parm['arg'];
				break;

				//USAGE: {NEWS_FIELD=body|format=html_truncate&arg=200,...} -> callback e107->html_truncate($value, 200, '...');
				case 'html_truncate':
					$val = e107::getParser()->toHTML($val, true);

				//USAGE: {NEWS_FIELD=title|format=text_truncate&arg=100,...} -> callback e107->text_truncate($value, 200, '...');
				case 'text_truncate':
					$method = $parm['format'];
					$callback = e107::getParser();
					$params = array($val); //value is always the first callback argument
					$params = array_merge($params, explode(',', $parm['arg']));
				break;

				//USAGE: {NEWS_FIELD=title|format=date} -> strftime($pref[shortdate], $value);
				//USAGE: {NEWS_FIELD=title|format=date&arg=%Y} -> strftime('%Y', $value);
				case 'date':
					$method = $parm['format'];
					$callback = e107::getParser();
					$params = array($val); //value is always the first callback argument
					$params = array_merge($params, explode(',', $parm['arg']));
					//should be done with date handler (awaiting for modifications)
					return strftime(varset($parm['arg'], e107::getPref('shortdate')), $val);
				break;

				default:
					return $val;
				break;

			}
			return call_user_func_array(array($callback, $method), $params);
		}

		return $val;
	}

	/**
	 * Shorthand getter for news fields
	 *
	 * @param string $news_field name without the leading 'news_' prefix
	 * @param mixed $default
	 * @return mixed data
	 */
	public function field($news_field, $default = null)
	{
		return parent::get('news_'.$news_field, $default);
	}

	/**
	 * Load news item by id
	 *
	 * @param integer $id
	 * @param boolean $force
	 * @return e_news_item
	 */
	public function load($id=null, $force = false)
	{
		
		$id = intval($id);
		$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";

	  	$query = "SELECT n.*, u.user_id, u.user_name, u.user_customtitle, u.user_image, nc.category_name, nc.category_sef, nc.category_icon FROM #news AS n
		LEFT JOIN #user AS u ON n.news_author = u.user_id
		LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
		WHERE n.news_id={$id} AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
		AND n.news_start < ".time()." AND (n.news_end=0 || n.news_end>".time().")";

		$this->setParam('db_query', $query);

		parent::load($id, $force);
		return $this;
	}
}

class e_news_tree extends e_front_tree_model
{
	protected $_db_table = 'news';
	protected $_field_id = 'news_id';
	protected $_cache_string = 'news_tree_';
	
	/**
	 * Current tree news category id
	 *
	 * @var integer|array
	 */
	protected $_current_category_id;

	/**
	 * Set current category Id
	 *
	 * @param mixed $category_id
	 * @return e_news_tree
	 */
	function setCurrentCategoryId($category_id)
	{
		$this->_current_category_id = $category_id;
		return $this;
	}
	
	public function getCurrentCategoryId()
	{
		return $this->_current_category_id;
	}
	
	public function hasCurrentCategoryId()
	{
		return $this->_current_category_id !== null;
	}

	/**
	 * Load tree by category id
	 *
	 * @param integer $category_id
	 * @param boolean $force
	 * @param array $params DB query parameters
	 * @return e_news_tree
	 */
	public function load($category_id = 0, $force = false, $params = array())
	{
		$category_id = intval($category_id);
		if(!$this->hasCurrentCategoryId() || $force) $this->setCurrentCategoryId($category_id);
		
		$this->setParam('model_class', 'e_news_item')
			->setParam('db_order', vartrue($params['db_order'], 'news_datestamp DESC'))
			->setParam('db_limit', vartrue($params['db_limit'], '0,10'))
			->setParam('db_where', $category_id ? 'news_category='.$category_id : '')
			->setParam('noCacheStringModify', false);
			
		return parent::load($force);
	}
	
	/**
	 * Load joined tree by category id
	 *
	 * @param mixed $category_id
	 * @param boolean $force
	 * @param array $params DB query parameters
	 * @return e_news_tree|e_tree_model
	 */
	public function loadJoin($category_id = 0, $force = false, $params = array())
	{
        if(is_string($category_id) && strpos($category_id, ','))
        {
            $category_id = array_map('trim', explode(',', $category_id));
        }
        if(is_array($category_id))
        {
            $category_id = array_map('intval', $category_id);
        }
		else $category_id = intval($category_id);

		if(!$this->hasCurrentCategoryId() || $force) $this->setCurrentCategoryId($category_id);
		
		$where = vartrue($params['db_where']);
		if($category_id)
		{
            if(is_array($category_id))
            {
                $where .= ($where ? ' AND ' : '').' n.news_category IN ('.implode(',', $category_id).')';
            }
			else $where .= ($where ? ' AND ' : '').' n.news_category='.$category_id;
		}
		if($where) $where = 'WHERE '.$where;
		
		$this->setParam('model_class', 'e_news_item');
			
		$db_order = vartrue($params['db_order'], 'n.news_datestamp DESC');
		$db_limit = vartrue($params['db_limit'], '0,10');
		
		
		$query = "SELECT  SQL_CALC_FOUND_ROWS n.*, u.user_id, u.user_name, u.user_customtitle, u.user_image, nc.category_id, nc.category_name, nc.category_sef, nc.category_icon FROM #news AS n
			LEFT JOIN #user AS u ON n.news_author = u.user_id
			LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id
			{$where}
			ORDER BY ".$db_order." LIMIT ".$db_limit;

		$this->setParam('db_query', $query);
		
		return parent::loadBatch($force);
	}

	/**
	 * Load active joined tree by category id
	 *
	 * @param mixed $category_id
	 * @param boolean $force
	 * @param array $params DB query parameters
	 * @return e_news_tree
	 */
	public function loadJoinActive($category_id = 0, $force = false, $params = array())
	{
		$where = vartrue($params['db_where']);
		
		$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
		$time = time();
		
		$where .= ($where ? ' AND ' : '')."n.news_start < {$time} AND (n.news_end=0 || n.news_end>{$time})
			AND n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
		";
		
		$params['db_where'] = $where;

		$this->_cache_string = null; // disable sys cache, otherwise we get a new cache file every time the time() changes.
		
		return $this->loadJoin($category_id, $force, $params);
	}
	
	/**
	 * Render Category tree
	 *
	 * @param array $template 
	 * @param array $parms [return, parsesc=>1|0, mode=>string]
	 * @param boolean $tablerender
	 * @return string
	 */
	function render($template = array(), $parms = array(), $tablerender = true)
	{
		if(!$this->hasTree())
		{
			return '';
		}

		if(is_string($template) || empty($template))
		{
			return "<div class='alert alert-danger'>Couldn't find template/layout with the following params: ".print_a($parms,true)."</div>";
		}

		$ret = array();
		$tp = e107::getParser();
		$start = '';
		$end = '';

		$param = $parms;
		$param['current_action'] = 'list';
		// TODO more default parameters

		$bullet = defined('BULLET') ? THEME_ABS.'images/'.BULLET : THEME_ABS.'images/bullet2.gif';
		$vars = new e_vars(array(
			'bullet' => $bullet,
			'total' => $this->getTotal(),
			'currentTotal' => count($this->getTree()),
		));
		
		$parser = e107::getParser();
		$batch = e107::getScBatch('news')
			->setScVar('param', $param);

		$wrapperKey = ($parms['tmpl'].'/'.$parms['tmpl_key']);
		$batch->wrapper($wrapperKey);
		$i = 1;


		$items = $this->getTree();

		if(!empty($items))
		{
			/** @var e_tree_model $news */
			foreach ($items as $news)
			{
				$d = $news->toArray();
				$batch->setScVar('news_item',$d); // set news category.
				break;
			}

			$start = $parser->parseTemplate($template['start'], true,$batch,$vars); // must be here in case {SETIMAGE} is present and used for items below.
		}


		$featuredCount = !empty($parms['featured']) ? intval($parms['featured']) : 0;


		foreach ($items as $news)
		{
			$vars->counter = $i;
			$batch->setScVar('news_item', $news->getData());
			$tmpl = (isset($template['featured']) && $i <= $featuredCount) ? 'featured' : 'item';
			$ret[] = $parser->parseTemplate($template[$tmpl], true, $batch, $vars);
			$i++;
		}

		if(!empty($items))
		{
			$end = $parser->parseTemplate($template['end'], true, $vars);
		}
		if($ret)
		{

			$separator = varset($template['separator'], '');
			$ret = $start.implode($separator, $ret).$end;
			$return = isset($parms['return']) ? true : false;

			if($tablerender)
			{
				$caption = vartrue($parms['caption']) ? defset($parms['caption'], $parms['caption']) : LAN_NEWSLATEST_MENU_TITLE; // found in plugins/news/languages/English.php

				if(!empty($parms['caption'][e_LANGUAGE]))
				{
					$caption = $parms['caption'][e_LANGUAGE];
				}

				$mod = true === $tablerender ? 'news_latest_menu' : $tablerender;
				return e107::getRender()->tablerender($caption, $ret, varset($parms['mode'], $mod), $return);
			}

			if($return) return $ret;
			echo $ret;
		}

		return '';
	}
}

class e_news_category_item extends e_front_model
{
	protected $_db_table = 'news_category';
	protected $_field_id = 'category_id';
	
	/**
	 * Shorthand getter for news category fields
	 *
	 * @param string $category_field name without the leading 'category_' prefix
	 * @param mixed $default
	 * @return mixed data
	 */
	public function cat($category_field, $default = null)
	{
		return parent::get('category_'.$category_field, $default);
	}

	public function sc_news_category_title($parm = '')
	{
		if('attribute' == $parm)
		{
			 return e107::getParser()->toAttribute($this->cat('name'));
		}

		return e107::getParser()->toHTML($this->cat('name'),true,'TITLE_PLAIN');
	}

	public function sc_news_category_url($parm = '')
	{

		$url = e107::getUrl()->create('news/list/category', array('id' => $this->getId(), 'name' => $this->cat('sef')));
		switch($parm)
		{
			case 'link':
				return '<a href="'.$url.'" class="news-category">'.$this->sc_news_category_title().'</a>';
			break;

			case 'link_icon':
				return '<a href="'.$url.'" class="news-category">'.$this->sc_news_category_icon().'&nbsp;'.$this->sc_news_category_title().'</a>';
			break;

			default:
				return $url;
			break;
		}
	}

	public function sc_news_category_link()
	{
		return $this->sc_news_category_url('link');
	}

	public function sc_news_category_icon($parm = '')
	{
		if(!$this->cat('icon'))
		{
			return '';
		}
		if(strpos($this->cat('icon'), '{') === 0)
		{
			$src = e107::getParser()->replaceConstants($this->cat('icon'));
		}
		else
		{
			$src = e_IMAGE_ABS.'icons/'.$this->cat('icon');
		}
		switch($parm)
		{
			case 'src':
				return $src;
			break;
			case 'link':
				return '<a href="'.$this->sc_news_category_url().'" class="news-category" title="'.$this->sc_news_category_title('attribute').'"><img src="'.$src.'" class="icon news-category" alt="'.$this->sc_news_category_title('attribute').'" /></a>';
			break;

			default:
				return '<img src="'.$src.'" class="icon news-category" alt="'.$this->sc_news_category_title('attribute').'" />';
			break;
		}
	}

	public function sc_news_category_news_count($parm = null)
	{
		if(!$this->is('category_news_count'))
		{
			return '';
		}

		if($parm === 'raw')
		{
			return (string) $this->cat('news_count');
		}

		return (string) e107::getParser()->toBadge( $this->cat('news_count'), $parm);
	}
}


class e_news_category_tree extends e_front_tree_model
{
	protected $_field_id = 'category_id';

	/**
	 * Load category data from the DB
	 *
	 * @param boolean $force
	 * @return e_tree_model
	 */
	public function loadBatch($force = false)
	{
		$this->setParam('model_class', 'e_news_category_item')
			->setParam('nocount', true)
			->setParam('db_order', 'category_order ASC')
			->setParam('noCacheStringModify', true)
			->setCacheString('news_category_tree')
			->setModelTable('news_category');
		
		return parent::loadBatch($force);
	}

	/**
	 * Load active categories only (containing active news items)
	 *
	 * @param boolean $force
	 * @return e_tree_model|e_news_category_tree
	 */
	public function loadActive($force = false)
	{
		
		$nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
		$time = time();

			$qry = "
			SELECT COUNT(n.news_id) AS category_news_count, nc.* FROM #news_category AS nc
			LEFT JOIN #news AS n ON n.news_category=nc.category_id
			WHERE n.news_class REGEXP '".e_CLASS_REGEXP."' AND NOT (n.news_class REGEXP ".$nobody_regexp.")
				AND n.news_start < ".$time." AND (n.news_end=0 || n.news_end>".$time.")
			GROUP BY nc.category_id
			ORDER BY nc.category_order ASC
			";
			
			
		$this->setParam('model_class', 'e_news_category_item')
			->setParam('db_query', $qry)
			->setParam('nocount', true)
			->setParam('db_debug', false)
			->setCacheString(true)
			->setModelTable('news_category');
		
		$this->setModelTable('news_category');
		
		return parent::loadBatch($force);
	}

	/**
	 * Render Category tree
	 *
	 * @param array $template 
	 * @param array $parms [return, parsesc=>1|0, mode=>string]
	 * @param boolean $tablerender
	 * @return string
	 */
	function render($template = array(), $parms = array(), $tablerender = true)
	{
		if(!$this->hasTree())
		{
			return '';
		}



		$ret = array();
		$tp = e107::getParser();

		if(!isset($parms['parsesc'])) $parms['parsesc'] = true;
		$parsesc = $parms['parsesc'] ? true : false;
		
		$active = '';
		if(e_PAGE == 'news.php')
		{
			$tmp = explode('.', e_QUERY);
			if(vartrue($tmp[1])) $active = $tmp[1];	
		}
		$bullet = defined('BULLET') ? THEME_ABS.'images/'.BULLET : THEME_ABS.'images/bullet2.gif';
		$obj = new e_vars(array('bullet' => $bullet));

		/** @var e_tree_model $cat */
		foreach ($this->getTree() as $cat)
		{
			$obj->active = '';
			if($active && $active == $cat->getId())
			{
				$obj->active = ' active';
			}

			$ret[] = $cat->toHTML($template['item'], $parsesc, $obj);

		}

		if($ret)
		{
			$separator = varset($template['separator'], '');
			$ret = $template['start'].implode($separator, $ret).$template['end'];
			$return = isset($parms['return']) ? true : false;

			if($tablerender)
			{
				$caption = vartrue($parms['caption']) ? defset($parms['caption'], $parms['caption']) : LAN_NEWSCAT_MENU_TITLE; // found in plugins/news/languages/English.php

				if(!empty($parms['caption'][e_LANGUAGE]))
				{
					$caption = $parms['caption'][e_LANGUAGE];
				}

				$mod = true === $tablerender ? 'news_categories_menu' : $tablerender;
				return e107::getRender()->tablerender($caption, $ret, varset($parms['mode'], $mod), $return);
			}

			if($return) return $ret;
			echo $ret;
		}

		return '';
	}
}

