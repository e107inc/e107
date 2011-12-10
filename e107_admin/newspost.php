<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * News Administration
 *
 * $URL $
 * $Id$
*/

require_once('../class2.php');

if (!getperms('H|N'))
{
	header('Location:'.e_BASE.'index.php');
	exit;
}

include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);

// -------- Presets. ------------  // always load before auth.php
require_once(e_HANDLER.'preset_class.php');
$pst = new e_preset();
$pst->form = "core-newspost-create-form"; // form id of the form that will have it's values saved.
$pst->page = "newspost.php?create"; // display preset options on which page(s).
$pst->id = "admin_newspost";
// ------------------------------
// done in class2: require_once(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_admin.php"); // maybe this should be put in class2.php when 'admin' is detected.
$newspost = new admin_newspost(e_QUERY, $pst);
e107::setRegistry('_newspost_admin', $newspost);
$gen = new convert();


//Handle Ajax Calls
if($newspost->ajax_observer()) exit;

e107::getJs()->requireCoreLib('core/admin.js');


function headerjs()
{
	$newspost = e107::getRegistry('_newspost_admin');
/*
    $ret .= "<script type='text/javascript'>

    function UpdateForm(id)
	{
        new e107Ajax.Updater('filterValue', '".e_SELF."?searchValue', {
            method: 'post',
			evalScripts: true,
            parameters: {filtertype: id}
		});
   }

   </script>";

*/
	// TODO - move this to external JS when news becomes a plugin
	$ret .= "
		<script type='text/javascript'>
			if(typeof e107Admin == 'undefined') var e107Admin = {}

			/**
			 * OnLoad Init Control
			 */
			e107Admin.initRules = {
				'Helper': true,
				'AdminMenu': false
			}

            //custom expand
			Element.addMethods( {
				newsDescToggle: function(element) {
					element = \$(element);
					if(!element.visible())
				    	element.fxToggle();

				    return element;
				},

				newsScrollToMe: function(element) {
					element = \$(element);
					new Effect.ScrollTo(element);
					return element;
				},

				newsUpdateButtonSpan: function(element, str, swapClass) {
					element = \$(element);
					if(swapClass) {
						var swapO = swapClass.split('::');
						element.removeClassName(swapO[0]).addClassName(swapO[1]);
					}

					if(element.down('span')) {
						element.down('span').update(str);
					}
					return element;
				}
			});

			//fix form action if needed
			document.observe('dom:loaded', function() {
				if(\$('core-newspost-create-form')) {
					\$('core-newspost-create-form').observe('submit', function(event) {
						var form = event.element();
						action = form.readAttribute('action') + document.location.hash;
						//if(\$('create-edit-stay-1') && \$('create-edit-stay-1').checked)
							form.writeAttribute('action', action);
					});
				}
			});
		</script>
	";

	if($newspost->getAction() == 'cat')
	{
		$ret .= "
		<script type='text/javascript'>
			var e_npadmin_ajaxsave = function(action, element) {
				var id = element.name.gsub(/[^\d]/, ''),
					cl = element.value,
					url = '#{e_ADMIN}newspost.php?' + action + '.' + id + '.' + cl;

				element.startLoading();
				new e107Ajax.Request(url.parsePath(), {
					onComplete: function(transport) {
						element.stopLoading();
						if(transport.responseText)
							alert(transport.responseText);//error
					}
				});
			}

			//e107 onload custom event
	        e107.runOnLoad( function(event) {
	        	var celement = event.memo['element'] ? \$(event.memo.element) : \$\$('body')[0];

	        	//Unobtrusive AJAX category list reload
	        	if(\$('trigger-list-refresh')) {
	            	\$('trigger-list-refresh').observe('click', function(event) {
						event.stop();
						\$('core-newspost-cat-list-form').submitForm(
							'core-newspost-cat-list-cont',
							{ overlayPage: \$\$('body')[0]  },
							\$('core-newspost-cat-list-form').action + '_list_refresh'
						);
					});
				}

				//Unobtrusive AJAX save category manage permissions
				celement.select('select[name^=multi_category_manager]').invoke('observe', 'change', function(event) {
					e_npadmin_ajaxsave('catmanager', event.element());
				});

				//Category order fields - user convenience
				celement.select('input[name^=multi_category_order]').invoke('observe', 'focus', function(event) {
					event.element().select();
				});

				//Unobtrusive AJAX save category order
				celement.select('input[name^=multi_category_order]').invoke('observe', 'blur', function(event) {
					e_npadmin_ajaxsave('catorder', event.element());
				});

				//Fill form - click observer (Unobtrusive AJAX edit category)
            	\$\$('a.action[id^=core-news-catedit-]').each(function(element) {
					element.observe('click', function(event) {
						event.stop();
						var el = event.findElement('a');
						$('core-newspost-cat-create-form').fillForm(\$\$('body')[0], { handler: el.readAttribute('href') });
					});
				});

			}, null, true);
    	</script>
		";
	}
	elseif ($newspost->getAction() == 'pref')
	{
		$ret .= "
			<script type='text/javascript'>
				document.observe('dom:loaded', function(){
					\$('newsposts').observe('change', function(event) {
						new e107Ajax.Updater(
							'newsposts-archive-cont',
							'".e_SELF."?pref_archnum.' + (event.element().selectedIndex + 1) + '.' + event.element().readAttribute('tabindex'),
							{ overlayElement: 'newsposts-archive-cont' }
						);
					});
				});
			</script>
			";
	}
	$ret .= $newspost->_cal->load_files();

   	return $ret;
}
$e_sub_cat = 'news';

require_once('auth.php');

/*
 * Observe for delete action
 */
$newspost->observer();

/*
 * Show requested page
 */
$newspost->show_page();



/* OLD JS? Can't find references to this func
echo "
<script type=\"text/javascript\">
function fclear() {
	document.getElementById('core-newspost-create-form').data.value = \"\";
	document.getElementById('core-newspost-create-form').news_extended.value = \"\";
}
</script>\n";
*/

require_once("footer.php");
exit;


// FIXME - news2plugin! Full rewrite.


class admin_newspost
{
	var $_request = array();
	var $_cal = array();
	var $_pst;
	var $_fields;
	var $_sort_order;
	var $_sort_link;
	var $fieldpref;
	var $news_categories;
	var $news_renderTypes = array();

	public $error = false;

	function __construct($qry, $pstobj)
	{
		global $user_pref;
		
		
		
		$this->parseRequest($qry);

		require_once(e_HANDLER."cache_handler.php");
		require_once(e_HANDLER."news_class.php");
		require_once(e_HANDLER."calendar/calendar_class.php");
		$this->_cal = new DHTML_Calendar(true);

		$this->_pst = $pstobj;

		$this->fieldpref = varset($user_pref['admin_news_columns'], array('news_id', 'news_title', 'news_author', 'news_render_type', 'options'));

		$this->fields = array(
				'checkboxes'	   		=> array('title' => '', 			'type' => null, 		'width' => '3%', 	'thclass' => 'center first', 	'class' => 'center', 	'nosort' => true, 'toggle' => 'news_selected', 'forced' => TRUE),
				'news_id'				=> array('title' => LAN_NEWS_45, 	'type' => 'number', 	'width' => '5%', 	'thclass' => 'center', 			'class' => 'center',  	'nosort' => false),
 				'news_title'			=> array('title' => NWSLAN_40, 		'type' => 'text', 		'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
				'news_sef'				=> array('title' => 'SEF URL', 		'type' => 'text', 		'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
    			'user_name'				=> array('title' => LAN_NEWS_50, 	'type' => 'text', 		'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
				'news_datestamp'		=> array('title' => LAN_NEWS_32, 	'type' => 'datestamp', 	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false, 'parms' => 'mask=%A %d %B %Y'),
                'category_name'			=> array('title' => NWSLAN_6, 		'type' => 'text', 		'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
  				'news_class'			=> array('title' => NWSLAN_22, 		'type' => 'userclass', 	'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
				'news_render_type'		=> array('title' => LAN_NEWS_49, 	'type' => 'number', 		'width' => 'auto', 	'thclass' => 'center', 			'class' => null, 		'nosort' => false),
			   	'news_thumbnail'		=> array('title' => LAN_NEWS_22, 	'type' => 'text', 		'width' => 'auto', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
		  		'news_sticky'			=> array('title' => LAN_NEWS_28, 	'type' => 'boolean', 	'width' => 'auto', 	'thclass' => 'center', 			'class' => 'center', 	'nosort' => false),
                'news_allow_comments' 	=> array('title' => NWSLAN_15, 		'type' => 'boolean', 	'width' => 'auto', 	'thclass' => 'center', 			'class' => 'center', 	'nosort' => false),
                'news_comment_total' 	=> array('title' => LAN_NEWS_60, 	'type' => 'number', 	'width' => '10%', 	'thclass' => '', 				'class' => null, 		'nosort' => false),
				'options'				=> array('title' => LAN_OPTIONS, 	'type' => null, 		'width' => '10%', 	'thclass' => 'center last', 	'class' => 'center', 	'nosort' => true, 'forced' => TRUE)

		);

/*		$ren_type = array(NWSLAN_75,NWSLAN_76,NWSLAN_77,NWSLAN_77." 2");
		$r_array = array();
		foreach($ren_type as $key=>$value)
		{
			$this->news_renderTypes[$key] = $value;
		}*/

		$this->news_renderTypes = array(NWSLAN_75,NWSLAN_76,NWSLAN_77,NWSLAN_77." 2");

	}

	function parseRequest($qry)
	{
		$tmp = explode(".", $qry);
		$action = varsettrue($tmp[0], 'main');
		$sub_action = varset($tmp[1], '');
		$id = isset($tmp[2]) && is_numeric($tmp[2]) ? intval($tmp[2]) : 0;
		$this->_sort_order = isset($tmp[2]) && !is_numeric($tmp[2]) ? $tmp[2] : 'desc';
		$from = intval(varset($tmp[3],0));
		unset($tmp);

        if ($this->_sort_order != 'asc') $this->_sort_order = 'desc';
		$this->_sort_link = ($this->_sort_order) == 'asc' ? 'desc' : 'asc';

		$this->_request = array($action, $sub_action, $id, $sort_order, $from);
	}

	function getAction()
	{
		return $this->_request[0];
	}

	/**
	 * @param string $action
	 * @return admin_newspost
	 */
	function setAction($action)
	{
		$this->_request[0] = $action;
		return $this;
	}

	function getSubAction()
	{
		return $this->_request[1];
	}

	/**
	 * @param string $action
	 * @return admin_newspost
	 */
	function setSubAction($action)
	{
		$this->_request[1] = $action;
		return $this;
	}

	function getId()
	{
		return $this->_request[2];
	}

	/**
	 * @param integer $id
	 * @return admin_newspost
	 */
	function setId($id)
	{
		$this->_request[2] = intval($id);
		return $this;
	}

	function getSortOrder()
	{
		return $this->_request[3];
	}

	function getFrom()
	{
		return $this->_request[4];
	}

	function clear_cache()
	{
		$ecache = e107::getCache();
		$ecache->clear("news.php"); //TODO change it to 'news_*' everywhere

		$ecache->clear("news_", false, true); //NEW global news cache prefix
		//$ecache->clear("nq_news_"); - supported by cache::clear() now
		//$ecache->clear("nomd5_news_"); supported by cache::clear() now

		$ecache->clear("othernews"); //TODO change it to 'news_othernews' everywhere
		$ecache->clear("othernews2"); //TODO change it to 'news_othernews2' everywhere
		return $this;
	}

	function clear_rwcache($sefstr = '')
	{
		// obsolete
	}

	function set_rwcache($sefstr, $data)
	{
		// obsolete
	}

	function ajax_observer()
	{
		$method = 'ajax_exec_'.$this->getAction();

		if(e_AJAX_REQUEST && method_exists($this, $method))
		{
			$this->$method();
			return true;
		}
		return false;
	}

	function observer()
	{
		e107::getDb()->db_Mark_Time('News Administration');
		$this->news_categories = array();
		if(e107::getDb()->db_Select('news_category', '*', (getperms('0') ? '' : 'category_manager IN ('.USERCLASS_LIST.')')))
		{
			$this->news_categories = e107::getDb()->db_getList('ALL', FALSE, FALSE, 'category_id');
		}

		//Required on create & savepreset action triggers
		if(isset($_POST['news_userclass']) && is_array($_POST['news_userclass']))
		{
			$_POST['news_class'] = implode(",", $_POST['news_userclass']);
			unset($_POST['news_userclass']);
		}
		$main = getperms('0');
		if(isset($_POST['delete']) && is_array($_POST['delete']))
		{
			$this->_observe_delete();
		}
		elseif(isset($_POST['execute_batch']))
		{
			$this->process_batch($_POST['news_selected']);
		}
		elseif(isset($_POST['submit_news']))
		{
			$this->_observe_submit_item($this->getSubAction(), $this->getId());
		}
		elseif($main && isset($_POST['create_category']))
		{
			$this->_observe_create_category();
		}
		elseif($main && isset($_POST['update_category']))
		{
			$this->_observe_update_category();
		}
		elseif($main && isset($_POST['multi_update_category']))
		{
			$this->_observe_multi_create_category();
		}
		elseif($main && isset($_POST['save_prefs']))
		{
			$this->_observe_save_prefs();
		}
		elseif(isset($_POST['submitupload']))
		{
			$this->_observe_upload();
		}
		elseif(isset($_POST['news_comments_recalc']))
		{
			$this->_observe_newsCommentsRecalc();
		}

		if(isset($_POST['etrigger_ecolumns'])) //elseif fails.
		{
        	$this->_observe_saveColumns();
		}
	}

	function show_page()
	{
		
	//	print_a($POST);
		
		switch ($this->getAction()) {
			case 'savepreset':
			case 'clr_preset':
				$this->_pst->save_preset('news_datestamp', false); // save and render result using unique name. Don't save item datestamp
				$_POST = array();
				$this->parseRequest('');
				$this->show_existing_items();
			break;
			case 'create':
				$this->_pst->read_preset('admin_newspost');  //only works here because $_POST is used.
				$this->show_create_item();
			break;

			case 'cat':
				if(!getperms('0|7'))
				{
					$this->noPermissions();
				}
				$this->show_categories();
			break;

			case 'sn':
				$this->show_submitted_news();
			break;

			case 'pref':
				if(!getperms('0'))
				{
					$this->noPermissions();
				}
				$this->show_news_prefs();
			break;

			case 'maint' :
				if(!getperms('0'))
				{
					$this->noPermissions();
				}
				$this->showMaintenance();
				break;

			default:
				$this->show_existing_items();
			break;
		}
	}

	function _observe_delete()
	{
		$admin_log = e107::getAdminLog();
		//FIXME - SEF URL cache
		$tmp = array_keys($_POST['delete']);
		list($delete, $del_id) = explode("_", $tmp[0]);
		$del_id = intval($del_id);

		if(!$del_id) return false;

		$e107 = e107::getInstance();

		switch ($delete) {
			case 'main':
							
				if ($e107->sql->db_Count('news','(*)',"news_id={$del_id}"))
				{
					e107::getEvent()->trigger("newsdel", $del_id);
					if($e107->sql->db_Delete("news", "news_id={$del_id}"))
					{
						$admin_log->log_event('NEWS_01',$del_id,E_LOG_INFORMATIVE,'');
						$this->show_message(NWSLAN_31." #".$del_id." ".NWSLAN_32, E_MESSAGE_SUCCESS);
						$this->clear_cache();

						$data = array('method'=>'delete', 'table'=>'news', 'id'=>$del_id, 'plugin'=>'news', 'function'=>'delete');
						$this->show_message(e107::getEvent()->triggerHook($data), E_MESSAGE_WARNING);

						admin_purge_related("news", $del_id);
					}
				}
			break;

			case 'category':
				
				if(!getperms('0|7')) $this->noPermissions();

				if (($count = $e107->sql->db_Count('news','(news_id)',"news_category={$del_id}")) === false || $count > 0)
				{
					$this->show_message('Category is in used in <strong>'.$count.'</strong> news items and cannot be deleted.', E_MESSAGE_ERROR);
					return false;
				}
				
				if ($e107->sql->db_Count('news_category','(*)',"category_id={$del_id}"))
				{
					e107::getEvent()->trigger("newscatdel", $del_id);
					if ($e107->sql->db_Delete("news_category", "category_id={$del_id}"))
					{
						$admin_log->log_event('NEWS_02',$del_id,E_LOG_INFORMATIVE,'');
						$this->show_message(NWSLAN_33." #".$del_id." ".NWSLAN_32, E_MESSAGE_SUCCESS);
						$this->clear_cache();
					}
				}
			break;

			case 'sn':
				if ($e107->sql->db_Delete("submitnews", "submitnews_id={$del_id}"))
				{
					$admin_log->log_event('NEWS_03',$del_id,E_LOG_INFORMATIVE,'');
					$this->show_message(NWSLAN_34." #".$del_id." ".NWSLAN_32);
					$this->clear_cache();
				}
			break;

			default:
				return  false;
		}

		return true;
	}

	function _observe_submit_item($sub_action, $id)
	{
		// ##### Format and submit item to DB
		
		$ix = new news;

		if($_POST['news_start'])
		{
			$tmp = explode("/", $_POST['news_start']);
			$_POST['news_start'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_start'] = 0;
		}

		if($_POST['news_end'])
		{
			$tmp = explode("/", $_POST['news_end']);
			$_POST['news_end'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_end'] = 0;
		}

		$matches = array();
		if(preg_match('#(.*?)/(.*?)/(.*?) (.*?):(.*?):(.*?)$#', $_POST['news_datestamp'], $matches))
		{
			$_POST['news_datestamp'] = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[1], $matches[3]);
		}
		else
		{
			$_POST['news_datestamp'] = time();
		}

		if($_POST['update_datestamp'])
		{
			$_POST['news_datestamp'] = time();
		}

		if ($id && $sub_action != "sn" && $sub_action != "upload")
		{
			$_POST['news_id'] = $id;
		}
		else
		{
			e107::getDb()->db_Update('submitnews', "submitnews_auth=1 WHERE submitnews_id ={$id}");
			e107::getAdminLog()->log_event('NEWS_07', $id, E_LOG_INFORMATIVE,'');
		}
		if (!isset($_POST['cat_id']))
		{
			$_POST['cat_id'] = 0;
		}
		$_POST['news_category'] = $_POST['cat_id'];
		if(!isset($this->news_categories[$_POST['news_category']]))
		{
			 $this->noPermissions();
		}

		/*if(isset($_POST['news_thumbnail']))
		{
			$_POST['news_thumbnail'] = urldecode(basename($_POST['news_thumbnail']));
		}*/

        $tmp = explode(chr(35), $_POST['news_author']);
        $_POST['news_author'] = $tmp[0];
		
        $ret = $ix->submit_item($_POST, !vartrue($_POST['create_edit_stay']));
		if($ret['error'])
		{
			e107::getMessage()->mergeWithSession() //merge with session messages
				->add(($id ? LAN_UPDATED_FAILED : LAN_CREATED_FAILED), E_MESSAGE_ERROR);
				
			$_POST['news_sef'] = $ret['data']['news_sef']; 
			return false;
		}
        $this->clear_cache();

        if(isset($_POST['create_edit_stay']) && !empty($_POST['create_edit_stay']))
        {
			if($this->getSubAction() != 'edit')
			{
	        	session_write_close();
				$rurl = e_SELF.(varsettrue($ret['id']) ? "?create.edit.".$ret['id'] : '');
				header('Location:'.($rurl ? $rurl : e_SELF));
				exit;
			}
        }
        else
        {
			session_write_close();
			header('Location:'.e_SELF);
			exit;
        }
	}

	function _observe_create_category()
	{
		if(!getperms('0|7'))
		{
			 $this->noPermissions();
		}
		//FIXME - lan, e_model based news administration model
		$this->error = false;
		if(empty($_POST['category_name']))
		{
			$this->show_message('Validation Error: Missing Category name', E_MESSAGE_ERROR);
			$this->error = true;
			if(!empty($_POST['category_sef']))
			{
				$_POST['category_sef'] = eHelper::secureSef($_POST['category_sef']);
			}
		}
		else
		{
			// first format sef...
			if(empty($_POST['category_sef']))
			{
				$_POST['category_sef'] = eHelper::title2sef($_POST['category_name']);
			}
			else 
			{
				$_POST['category_sef'] = eHelper::secureSef($_POST['category_sef']);
			}
		}
		
		// ...then check it
		if(empty($_POST['category_sef']))
		{
			$this->error = true;
			$this->show_message('Validation error: News Category SEF URL value is required field and can\'t be empty!', E_MESSAGE_ERROR);
		}
		elseif(e107::getDb()->db_Count('news_category', '(category_id)', "category_sef='".e107::getParser()->toDB($_POST['category_sef'])."'"))
		{
			$this->error = true;
			$this->show_message('Validation error: News Category SEF URL is unique field - current value already in use! Please choose another SEF URL value.', E_MESSAGE_ERROR);
		}

		if (!$this->error)
		{
			$inserta = array();

			$inserta['data']['category_icon'] = $_POST['category_icon'];
			$inserta['_FIELD_TYPES']['category_icon'] = 'todb';

			$inserta['data']['category_name'] = $_POST['category_name'];
			$inserta['_FIELD_TYPES']['category_name'] = 'todb';
			
			$inserta['data']['category_sef'] = $_POST['category_sef'];
			$inserta['_FIELD_TYPES']['category_sef'] = 'todb';

			$inserta['data']['category_meta_description'] = eHelper::formatMetaDescription($_POST['category_meta_description']);
			$inserta['_FIELD_TYPES']['category_meta_description'] = 'todb';

			$inserta['data']['category_meta_keywords'] = eHelper::formatMetaKeys($_POST['category_meta_keywords']);
			$inserta['_FIELD_TYPES']['category_meta_keywords'] = 'todb';

			$inserta['data']['category_manager'] = $_POST['category_manager'];
			$inserta['_FIELD_TYPES']['category_manager'] = 'int';

			$inserta['data']['category_order'] = $_POST['category_order'];
			$inserta['_FIELD_TYPES']['category_order'] = 'int';

			$id = e107::getDb()->db_Insert('news_category', $inserta);
			if($id)
			{
				$inserta['data']['category_id'] = $id;
				
				//admin log now supports DB array and method chaining
				e107::getAdminLog()->log_event('NEWS_04', $inserta, E_LOG_INFORMATIVE, '');

				$this->show_message(NWSLAN_35, E_MESSAGE_SUCCESS);
				$this->clear_cache();

				//TODO - add to WIKI docs
				e107::getEvent()->trigger("newscatpost", array_merge($inserta['data'], $rwinserta['data']));
			}
			else
			{
				//debug + error message
				if(e107::getDb()->getLastErrorNumber())
				{
					$this->error = true;
					$this->show_message('mySQL Error detected!', E_MESSAGE_ERROR);
					eMessage::getInstance()->addS('mySQL error #'.e107::getDb()->getLastErrorNumber().': '.e107::getDb()->getLastErrorText(), E_MESSAGE_DEBUG);
				}
			}
		}
	}

	function _observe_update_category()
	{
		if(!getperms('0|7'))
		{
			 $this->noPermissions();
		}
		$this->setId(intval($_POST['category_id']));

		if(!$this->getId())
		{
			return;
		}

		//FIXME - lan, e_model based news administration model
		$this->error = false;
		if(empty($_POST['category_name']))
		{
			$this->show_message('Validation Error: Missing Category name', E_MESSAGE_ERROR);
			$this->error = true;
			if(!empty($_POST['category_sef']))
			{
				$_POST['category_sef'] = eHelper::secureSef($_POST['category_sef']);
			}
		}
		else
		{
			// first format sef...
			if(empty($_POST['category_sef']))
			{
				$_POST['category_sef'] = eHelper::title2sef($_POST['category_name']);
			}
			else 
			{
				$_POST['category_sef'] = eHelper::secureSef($_POST['category_sef']);
			}
		}
		
		// ...then check it
		if(empty($_POST['category_sef']))
		{
			$this->error = true;
			$this->show_message('Validation error: News Category SEF URL value is required field and can\'t be empty!', E_MESSAGE_ERROR);
		}
		elseif(e107::getDb()->db_Count('news_category', '(category_id)', "category_id<>".$this->getId()." AND category_sef='".(e107::getParser()->toDB($_POST['category_sef'])."'")))
		{
			$this->error = true;
			$this->show_message('Validation error: News Category SEF URL is unique field - current value already in use! Please choose another SEF URL value.', E_MESSAGE_ERROR);
		}

		if (!$this->error)
		{
			$updatea = array();
			$updatea['data']['category_icon'] = $_POST['category_icon'];
			$updatea['_FIELD_TYPES']['category_icon'] = 'todb';

			$updatea['data']['category_name'] = $_POST['category_name'];
			$updatea['_FIELD_TYPES']['category_name'] = 'todb';
			
			$updatea['data']['category_sef'] = $_POST['category_sef'];
			$updatea['_FIELD_TYPES']['category_sef'] = 'todb';
			
			$updatea['data']['category_meta_description'] = strip_tags($_POST['category_meta_description']);
			$updatea['_FIELD_TYPES']['category_meta_description'] = 'str';

			$updatea['data']['category_meta_keywords'] = $_POST['category_meta_keywords'];
			$updatea['_FIELD_TYPES']['category_meta_keywords'] = 'str';

			$updatea['data']['category_manager'] = $_POST['category_manager'];
			$updatea['_FIELD_TYPES']['category_manager'] = 'int';

			$updatea['data']['category_order'] = $_POST['category_order'];
			$updatea['_FIELD_TYPES']['category_order'] = 'int';

			$updatea['WHERE'] = 'category_id='.$this->getId();

			$inserta = array();
			$rid = 0;

			$upcheck = e107::getDb()->db_Update("news_category", $updatea);
			$rwupcheck = false;
			if($upcheck || !e107::getDb()->getLastErrorNumber())
			{
		
				if ($upcheck || $rwupcheck)
				{
					//admin log now supports DB array and method chaining
					$updatea['data']['category_id'] = $this->getId();
					if($upcheck) e107::getAdminLog()->log_event('NEWS_05', $updatea['data'], E_LOG_INFORMATIVE, '');
					if($rwupcheck && $inserta['data']) e107::getAdminLog()->log_event('NEWS_10', $inserta['data'], E_LOG_INFORMATIVE, '');

					$this->show_message(NWSLAN_36, E_MESSAGE_SUCCESS);
					$this->clear_cache();

					//TODO - add to WIKI docs
					e107::getEvent()->trigger("newscatupd", array_merge($updatea['data'], $inserta['data']));
				}
				else
				{
					$this->show_message(LAN_NO_CHANGE);
				}

				
				$this->setId(0);
			}
			else
			{
				$this->error = true;
				$this->setSubAction('edit');
				$this->show_message('mySQL Error detected!', E_MESSAGE_ERROR);
				$this->show_message('#'.e107::getDb()->getLastErrorNumber().': '.e107::getDb()->getLastErrorText(), E_MESSAGE_DEBUG);
				return;
			}
		}
	}

	function _observe_multi_create_category()
	{
		if(!getperms('0|7'))
		{
			 $this->noPermissions();
		}
		$cnt = 0;
		foreach ($_POST['multi_category_manager'] as $cid => $val)
		{
			$order = $_POST['multi_category_order'][$cid];
			$cnt += (int) e107::getDb()->db_Update('news_category', 'category_manager='.intval($val).', category_order='.intval($order).' WHERE category_id='.intval($cid));
		}
		if($cnt) eMessage::getInstance()->add(LAN_UPDATED, E_MESSAGE_SUCCESS);
	}

	function _observe_save_prefs()
	{
		if(!getperms('0'))
		{
			 $this->noPermissions();
		}
		$temp = array();
		$temp['newsposts'] 				= intval($_POST['newsposts']);
	   	$temp['newsposts_archive'] 		= intval($_POST['newsposts_archive']);
		$temp['newsposts_archive_title'] = e107::getParser()->toDB($_POST['newsposts_archive_title']);
		$temp['news_cats'] 				= intval($_POST['news_cats']);
		$temp['nbr_cols'] 				= intval($_POST['nbr_cols']);
		$temp['subnews_attach'] 		= intval($_POST['subnews_attach']);
		$temp['subnews_resize'] 		= intval($_POST['subnews_resize']);
		$temp['subnews_class'] 			= intval($_POST['subnews_class']);
		$temp['subnews_htmlarea'] 		= intval($_POST['subnews_htmlarea']);
		$temp['news_subheader'] 		= e107::getParser()->toDB($_POST['news_subheader']);
		$temp['news_newdateheader'] 	= intval($_POST['news_newdateheader']);
		$temp['news_unstemplate'] 		= intval($_POST['news_unstemplate']);
		$temp['news_editauthor']		= intval($_POST['news_editauthor']);
		$temp['news_sefbase']			= preg_replace('#[^\w\pL\-]#u', '', $_POST['news_sefbase']);

		e107::getConfig()->updatePref($temp);

		if(e107::getConfig()->save(false))
		{
			e107::getAdminLog()->logArrayDiffs($temp, e107::getPref(), 'NEWS_06');
			$this->clear_cache();
			//$this->show_message(NWSLAN_119, E_MESSAGE_SUCCESS);
		}
	}

	function _observe_upload()
	{
		//$pref['upload_storagetype'] = "1";
		require_once(e_HANDLER."upload_handler.php");

		$uploaded = file_upload(e_NEWSIMAGE);

		foreach($_POST['uploadtype'] as $key=>$uploadtype)
		{
			if($uploadtype == "thumb")
			{
				rename(e_NEWSIMAGE.$uploaded[$key]['name'],e_NEWSIMAGE."thumb_".$uploaded[$key]['name']);
			}

			if($uploadtype == "file")
			{
				rename(e_NEWSIMAGE.$uploaded[$key]['name'],e_DOWNLOAD.$uploaded[$key]['name']);
			}

			if ($uploadtype == "resize" && $_POST['resize_value'])
			{
				require_once(e_HANDLER."resize_handler.php");
				resize_image(e_NEWSIMAGE.$uploaded[$key]['name'], e_NEWSIMAGE.$uploaded[$key]['name'], $_POST['resize_value'], "copy");
			}
		}
	}


	function _observe_saveColumns()
	{
		global $user_pref,$admin_log;
		$user_pref['admin_news_columns'] = $_POST['e-columns'];
		save_prefs('user');
		$this->fieldpref = $user_pref['admin_news_columns'];
	}

	function show_existing_items()
	{
		$user_pref = e107::getUser()->getPref(); 
		if(!getperms('H'))
		{
        	return;
		}

		//require_once(e_HANDLER."form_handler.php");
		$frm = e107::getForm(true); //enable inner tabindex counter

	   	// Effectively toggle setting for headings


		$amount = 10;//TODO - pref

		if(!is_array($user_pref['admin_news_columns']))
		{
        	$user_pref['admin_news_columns'] = array("news_id","news_title","news_author","news_render_type");
		}


		$field_columns = $this->fields;

		$e107 = e107::getInstance();

        // ------ Search Filter ------

        $text .= "
			<form method='post' action='".e_SELF."'>
			<div class='left' style='padding:20px'>
			<input type='text' name='searchquery' value='".$_POST['searchquery']."' />";
			$text .= $frm->admin_button('searchsubmit', NWSLAN_63, 'search');
			$text .= "
			</div></form>
		";

        // --------------------------------------------

		$query = "
			SELECT n.*, nc.*, u.user_name, u.user_id FROM #news AS n
			LEFT JOIN #news_category AS nc ON n.news_category=nc.category_id
			LEFT JOIN #user AS u ON n.news_author=u.user_id
		";

		$check_perms = !getperms('0') ? " nc.category_manager IN (".USERCLASS_LIST.") " : '';
		if (vartrue($_POST['searchquery']))
		{
			$query .= "WHERE {$check_perms}n.news_title REGEXP('".$_POST['searchquery']."') OR n.news_body REGEXP('".$_POST['searchquery']."') OR n.news_extended REGEXP('".$_POST['searchquery']."') ORDER BY n.news_datestamp DESC";
		}
		else
		{
			$ordfield = 'n.news_datestamp';

			if($this->getSubAction() == 'user_name')
			{
				$ordfield = "u.user_name";
			}
			elseif(strpos($this->getSubAction(), 'category_'))
			{
				$ordfield = 'nc.'.$this->getSubAction();
			}
			elseif($this->getSubAction())
			{
				$ordfield = 'n.'.$this->getSubAction();
			}

			$query .= ($check_perms ? "WHERE {$check_perms}" : '')."ORDER BY {$ordfield} ".strtoupper($this->_sort_order)." LIMIT ".$this->getFrom().", {$amount}";
		}


		if ($e107->sql->db_Select_gen($query))
		{
			$newsarray = $e107->sql->db_getList();

			$text .= "
				<form action='".e_SELF."' id='newsform' method='post'>
					<fieldset id='core-newspost-list'>
						<legend class='e-hideme'>".NWSLAN_4."</legend>
						<table cellpadding='0' cellspacing='0' class='adminlist'>
							".$frm->colGroup($this->fields, $this->fieldpref)."
							".$frm->thead($this->fields, $this->fieldpref, 'main.[FIELD].[ASC].[FROM]')."
							<tbody>";

			$ren_type = array("default","title","other-news","other-news 2"); // Shortened
			
			foreach($newsarray as $row)
			{
				// PREPARE SOME DATA
				// safe to pass $row as it contains username and id only (no sensitive data), user_id and user_name will be internal converted to 'id', 'name' vars 
				$row['user_name'] 			= "<a href='".e107::getUrl()->create('user/profile/view', $row)."' title='{$row['user_name']}'>{$row['user_name']}</a>";
				$row['news_thumbnail'] 		= ($row['news_thumbnail'] && is_readable(e_NEWSIMAGE.$row['news_thumbnail'])) ? "<a href='".e_NEWSIMAGE_ABS.$row['news_thumbnail']."' title='{$row['news_thumbnail']}' rel='external' class='e-image-preview'>".e107::getParser()->text_truncate($row['news_thumbnail'], 20, '...')."</a>" : "";
				$row['news_title'] 			= "<a href='".e107::getUrl()->create('news/view/item', $row)."'>".$e107->tp->toHTML($row['news_title'], false, 'TITLE')."</a>";
				$row['category_name'] 		= "<a href='".e107::getUrl()->create('news/list/items', $row)."'>".$row['category_name']."</a>";
				$row['news_render_type'] 	= $ren_type[$row['news_render_type']];
	
				$row['news_allow_comments'] = !$row['news_allow_comments'] ? true : false; // old reverse logic
				$row['options'] 			= "
												<a class='action' href='".e_SELF."?create.edit.{$row['news_id']}' tabindex='".$frm->getNext()."'>".ADMIN_EDIT_ICON."</a>
												".$frm->submit_image("delete[main_{$row['news_id']}]", LAN_DELETE, 'delete', NWSLAN_39." [ID: {$row['news_id']}]")."
											";
				$row['checkboxes'] 			= $row['news_id'];

				// AUTO RENDER
				$text .= $frm->renderTableRow($this->fields, $this->fieldpref, $row, 'news_id');
			}

			$text .= "
							</tbody>
						</table>";
			$text .= "<div class='buttons-bar center'>".$this->show_batch_options()."</div>";
			$text .= "
					</fieldset>
				</form>
			";

		}
		else
		{
			$tmp = NWSLAN_43;
			if(vartrue($_POST['searchquery']))
			{
				$tmp = sprintf(NWSLAN_121, '<em>&quot;'.$_POST['searchquery'])."&quot;</em> <a href='".e_SELF."'>&laquo; ".LAN_BACK."</a>";
			}
			$text = "<div class='center warning'>{$tmp}</div>";
		}



		$newsposts = $e107->sql->db_Count('news');

		if (!vartrue($_POST['searchquery']))
		{
			$parms = $newsposts.",".$amount.",".$this->getFrom().",".e_SELF."?".$this->getAction().'.'.($this->getSubAction() ? $this->getSubAction() : 0).'.'.$this->_sort_order.".[FROM]";
			$nextprev = $e107->tp->parseTemplate("{NEXTPREV={$parms}}");
			if ($nextprev) $text .= "<div class='nextprev-bar'>".$nextprev."</div>";

		}

		e107::getRender()->tablerender(NWSLAN_4, e107::getMessage()->render().$text);
	}

	function show_batch_options()
	{
		$classes = e107::getUserClass()->uc_get_classlist();

		// Grab news Category Names;
		e107::getDb()->db_Select('news_category', '*');
        $newscatarray = e107::getDb()->db_getList();
		$news_category = $news_manage = array();
        foreach($newscatarray as $val)
		{
        	$news_category[$val['category_id']] = $val['category_name'];
			$news_manage[$val['category_id']] = $val['category_manager'];
		}

		$comments_array = array('Allow Comments', 'Disable Comments', 'Reverse Allow/Disalow');
		$sticky_array = array(1 => 'Sticky', 0 => 'Not Sticky', 2 => 'Reverse Them'); // more proper controls order

		return e107::getForm()->batchoptions(
			array(
					'delete_selected'		=> LAN_DELETE,
					'category' 				=> array('Modify Category', $news_category),
					'sticky_selected'		=> array('Modify Sticky', $sticky_array),
					'rendertype'			=> array('Modify Render-type', $this->news_renderTypes),
					'comments'				=> array('Modify Comments', $comments_array),
					'__check_class' 		=> array('category' => $news_manage)
			),
		    array(
		         	'userclass'    			=> array('Assign Visibility...',$classes),
			)
	   );
	}

	function batch_category($ids, $value)
	{
		if(!isset($this->news_categories[$value]))
		{
			 $this->noPermissions();
		}
		$sql = e107::getDb();
		$count = $sql->db_Update("news","news_category = ".$value." WHERE news_id IN (".implode(",",$ids).") ");
	}

	function batch_comments($ids, $value)
	{
		$sql = e107::getDb();
		$value = intval($value);
		if(2 === $value) //reverse it
		{
			$count = $sql->db_Update("news","news_allow_comments=1-news_allow_comments WHERE news_id IN (".implode(",",$ids).") ");
		}
		else //set it
		{
			$count = $sql->db_Update("news","news_allow_comments=".$value." WHERE news_id IN (".implode(",",$ids).") ");
		}
	}

	function batch_rendertype($ids, $value)
	{
		$sql = e107::getDb();
		$count = $sql->db_Update("news","news_render_type = ".$value." WHERE news_id IN (".implode(",",$ids).") ");
	}

	function batch_userclass($ids, $value)
	{
		$sql = e107::getDb();
		$count = $sql->db_Update("news","news_class = ".$value." WHERE news_id IN (".implode(",",$ids).") ");
	}

	function batch_delete($ids, $value)
	{
		$sql = e107::getDb();
		$count = $sql->db_Delete("news","news_id IN (".implode(",",$ids).") ");
	}
	
	function batch_subdelete($ids, $value)
	{
		$sql = e107::getDb();
		$count = $sql->db_Delete("submitnews","submitnews_id IN (".implode(",",$ids).") ");
	}
	
	function batch_subcategory($ids, $value)
	{
		if(!isset($this->news_categories[$value]))
		{
			 $this->noPermissions();
		}
		$sql = e107::getDb();
		$count = $sql->db_Update("submitnews","submitnews_category = ".$value." WHERE submitnews_id IN (".implode(",",$ids).") ");
	}

	function batch_sticky($ids, $value)
	{
		$sql = e107::getDb();
		$value = intval($value);
		if(2 === $value) //reverse it
		{
			$count = $sql->db_Update("news","news_sticky=1-news_sticky WHERE news_id IN (".implode(",",$ids).") ");
		}
		else //set it
		{
			$count = $sql->db_Update("news","news_sticky=".$value." WHERE news_id IN (".implode(",",$ids).") ");
		}
	}


	function process_batch($id_array)
	{
		list($type, $tmp, $value) = explode("_",$_POST['execute_batch']);
		$method = "batch_".$type;
		if (method_exists($this,$method) && isset($id_array) )
		{
			$this->$method($id_array,$value);
		}
	}




	function _pre_create()
	{
		if($this->getSubAction() == "edit" && !$_POST['preview'])
		{
			if(!isset($_POST['submit_news']))
			{
				if(e107::getDb()->db_Select('news', '*', 'news_id='.intval($this->getId())))
				{
					$row = e107::getDb()->db_Fetch();

					if(!isset($this->news_categories[$row['news_category']]))
					{
						$this->noPermissions();
					}

					$_POST['news_title'] = $row['news_title'];
					$_POST['news_sef'] = $row['news_sef'];
					$_POST['news_body'] = $row['news_body'];
					$_POST['news_author'] = $row['news_author'];
					$_POST['news_extended'] = $row['news_extended'];
					$_POST['news_allow_comments'] = $row['news_allow_comments'];
					$_POST['news_class'] = $row['news_class'];
					$_POST['news_summary'] = $row['news_summary'];
					$_POST['news_sticky'] = $row['news_sticky'];
					$_POST['news_datestamp'] = ($_POST['news_datestamp']) ? $_POST['news_datestamp'] : $row['news_datestamp'];

					$_POST['cat_id'] = $row['news_category'];
					$_POST['news_start'] = $row['news_start'];
					$_POST['news_end'] = $row['news_end'];
					$_POST['comment_total'] = e107::getDb()->db_Count("comments", "(*)", " WHERE comment_item_id={$row['news_id']} AND comment_type='0'");
					$_POST['news_render_type'] = $row['news_render_type'];
					$_POST['news_thumbnail'] = $row['news_thumbnail'];
					$_POST['news_meta_keywords'] = $row['news_meta_keywords'];
					$_POST['news_meta_description'] = $row['news_meta_description'];
				}
			}
		}
	}

	function show_create_item()
	{
		$pref = e107::getPref();
		$this->_pre_create();

		require_once(e_HANDLER."userclass_class.php");
		require_once(e_HANDLER."form_handler.php");
		$frm = new e_form(true); //enable inner tabindex counter

		$text = '';
		if (isset($_POST['preview']))
		{
			$text = $this->preview_item($this->getId());
		}


		$sub_action = $this->getSubAction();
		$id = $this->getSubAction() != 'sn' && $this->getSubAction() != 'upload' ? $this->getId() : 0;

		$e107 = e107::getInstance();
		$tp = e107::getParser();
		$sql = e107::getDb();

		if ($sub_action == "sn" && !varset($_POST['preview']))
		{
			if ($sql->db_Select("submitnews", "*", "submitnews_id=".$this->getId(), TRUE))
			{
				//list($id, $submitnews_name, $submitnews_email, $_POST['news_title'], $submitnews_category, $_POST['news_body'], $submitnews_datestamp, $submitnews_ip, $submitnews_auth, $submitnews_file) = $sql->db_Fetch();
				$row = $sql->db_Fetch();
				$_POST['news_title'] = $row['submitnews_title'];
				$_POST['news_body'] = $row['submitnews_item'];
				$_POST['cat_id'] = $row['submitnews_category'];

				if (defsettrue('e_WYSIWYG'))
				{
				  if (substr($_POST['news_body'],-7,7) == '[/html]') $_POST['news_body'] = substr($_POST['news_body'],0,-7);
				  if (substr($_POST['news_body'],0,6) == '[html]') $_POST['news_body'] = substr($_POST['news_body'],6);
					$_POST['news_body'] .= "<br /><b>".NWSLAN_49." {$row['submitnews_name']}</b>";
					$_POST['news_body'] .= ($row['submitnews_file'])? "<br /><br /><img src='{e_NEWSIMAGE}{$row['submitnews_file']}' class='f-right' />": '';
				}
				else
				{
					$_POST['news_body'] .= "\n[[b]".NWSLAN_49." {$row['submitnews_name']}[/b]]";
					$_POST['news_body'] .= ($row['submitnews_file'])?"\n\n[img]{e_NEWSIMAGE}{$row['submitnews_file']}[/img]": "";
				}
				$_POST['data'] = $tp->dataFilter($_POST['data']);		// Filter any nasties
				$_POST['news_title'] = $tp->dataFilter($_POST['news_title']);
			}
		}

		if ($sub_action == "upload" && !varset($_POST['preview']))
		{
			if ($sql->db_Select('upload', '*', "upload_id=".$this->getId())) {
				$row = $sql->db_Fetch();
				$post_author_id = substr($row['upload_poster'], 0, strpos($row['upload_poster'], "."));
				$post_author_name = substr($row['upload_poster'], (strpos($row['upload_poster'], ".")+1));
				$match = array();
				//XXX DB UPLOADS STILL SUPPORTED?
				$upload_file = "pub_" . (preg_match('#Binary\s(.*?)\/#', $row['upload_file'], $match) ? $match[1] : $row['upload_file']);
				$_POST['news_title'] = LAN_UPLOAD.": ".$row['upload_name'];
				$_POST['news_body'] = $row['upload_description']."\n[b]".NWSLAN_49." [link=".$e107->url->create('user/profile/view', 'id='.$post_author_id.'&name='.$post_author_name)."]".$post_author_name."[/link][/b]\n\n[file=request.php?".$upload_file."]{$row['upload_name']}[/file]\n";
			}
		}

		$text .= "
		<div class='admintabs' id='tab-container'>
			<ul class='e-tabs e-hideme' id='core-emote-tabs'>
				<li id='tab-general'><a href='#core-newspost-create'>".LAN_NEWS_52."</a></li>
				<li id='tab-seo'><a href='#core-newspost-seo'>SEO</a></li>
				<li id='tab-advanced'><a href='#core-newspost-edit-options'>".LAN_NEWS_53."</a></li>
			</ul>
			<form method='post' action='".e_SELF."?".e_QUERY."' id='core-newspost-create-form' ".(FILE_UPLOADS ? "enctype='multipart/form-data'" : "")." >
				<fieldset id='core-newspost-create'>
					<legend>".LAN_NEWS_52."</legend>
					<table cellpadding='0' cellspacing='0' class='adminedit'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td>".NWSLAN_6.": </td>
								<td>
		";

		if (!$this->news_categories)
		{
			$text .= NWSLAN_10;
		}
		else
		{
			$text .= "
									".$frm->select_open('cat_id')."
			";

			foreach ($this->news_categories as $row)
			{
					$text .= $frm->option($tp->toHTML($row['category_name'], FALSE, "LINKTEXT"), $row['category_id'], varset($_POST['cat_id']) == $row['category_id']);
			}
			$text .= "
									</select>
			";
		}
		$text .= "
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_12.":</td>
								<td>
									".$frm->text('news_title', $tp->post_toForm($_POST['news_title']))."
								</td>
							</tr>

							<tr>
								<td>".LAN_NEWS_27.":</td>
								<td>
									".$frm->text('news_summary', $tp->post_toForm($_POST['news_summary']), 250)."
								</td>
							</tr>
		";


		// -------- News Author ---------------------
        $text .="
							<tr>
								<td>".LAN_NEWS_50.":</td>
								<td>
		";

		if(!getperms('0') && !check_class($pref['news_editauthor']))
		{
			$auth = ($_POST['news_author']) ? intval($_POST['news_author']) : USERID;
			$e107->sql->db_Select("user", "user_name", "user_id={$auth} LIMIT 1");
           	$row = $e107->sql->db_Fetch(MYSQL_ASSOC);
			$text .= "<input type='hidden' name='news_author' value='".$auth.chr(35).$row['user_name']."' />";
			$text .= "<a href='".$e107->url->create('user/profile/view', 'name='.$row['user_name'].'&id='.$_POST['news_author'])."'>".$row['user_name']."</a>";
		}
        else // allow master admin to
		{
			$text .= $frm->select_open('news_author');
			$qry = "SELECT user_id,user_name FROM #user WHERE user_perms = '0' OR user_perms = '0.' OR user_perms REGEXP('(^|,)(H)(,|$)') ";
			if($pref['subnews_class'] && $pref['subnews_class']!= e_UC_GUEST && $pref['subnews_class']!= e_UC_NOBODY)
			{
				if($pref['subnews_class']== e_UC_MEMBER)
				{
					$qry .= " OR user_ban != 1";
				}
				elseif($pref['subnews_class']== e_UC_ADMIN)
				{
	            	$qry .= " OR user_admin = 1";
				}
				else
				{
	            	$qry .= " OR FIND_IN_SET(".intval($pref['subnews_class']).", user_class) ";
				}
			}

	        $sql->db_Select_gen($qry);
	        while($row = $sql->db_Fetch())
	        {
	        	if($_POST['news_author'])
				{
		        	$sel = ($_POST['news_author'] == $row['user_id']);
		        }
				else
				{
		        	$sel = (USERID == $row['user_id']);
				}
				$text .= $frm->option($row['user_name'], $row['user_id'].chr(35).$row['user_name'], $sel);
			}

			$text .= "</select>
			";
		}

		$text .= "
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_13.":<br /></td>
								<td>";

		$val = (strstr($tp->post_toForm($_POST['news_body']), "[img]http") ? $tp->post_toForm($_POST['news_body']) : str_replace("[img]../", "[img]", $tp->post_toForm($_POST['news_body'])));
        $text .= $frm->bbarea('news_body', $val, 'news', 'helpb');

		// Extended news form textarea
		// Fixes Firefox issue with hidden wysiwyg textarea.
		// XXX - WYSIWYG is already plugin, this should go
  //		if(defsettrue('e_WYSIWYG')) $ff_expand = "tinyMCE.execCommand('mceResetDesignMode')";
		$val = (strstr($tp->post_toForm($_POST['news_extended']), "[img]http") ? $tp->post_toForm($_POST['news_extended']) : str_replace("[img]../", "[img]", $tp->post_toForm($_POST['news_extended'])));
		$text .= "
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_14.":</td>
								<td>
									".$frm->bbarea('news_extended', $val, 'extended', 'helpc')."
									<!-- <div class='field-help'>".NWSLAN_83."</div> -->
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_66.":</td>
								<td>";

		$text .= $frm->mediaUrl('news', NWSLAN_69);

		$text .= "
								</td>
							</tr>
							<tr>
								<td>".NWSLAN_67.":</td>
								<td>
		";

		$text .= $frm->imagepicker('news_thumbnail', $_POST['news_thumbnail'],'','news');

		$text .= "
								<div class='field-help'>".LAN_NEWS_23."</div>
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
		";

		//BEGIN SEO block
		$text .= "
				<fieldset id='core-newspost-seo'>
					<legend>SEO</legend>
					<table cellpadding='0' cellspacing='0' class='adminedit'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
						
							<tr>
								<td class='label'>Friendly URL string: </td>
								<td class='control'>
									".$frm->text('news_sef', $tp->post_toForm($_POST['news_sef']), 255)."
									<div class='field-help'>If left empty will be automatically created from current News Title based on your current <a href='".e_ADMIN_ABS."eurl.php?mode=main&amp;action=settings' title='To URL settings area' rel='external'>URL settings</a></div>
								</td>
							</tr>

							<tr>
								<td class='label'>Meta keywords: </td>
								<td class='control'>".$frm->text('news_meta_keywords', $tp->post_toForm($_POST['news_meta_keywords']), 255)."</td>
							</tr>
							<tr>
								<td class='label'>Meta description: </td>
								<td class='control'>".$frm->textarea('news_meta_description', $tp->post_toForm($_POST['news_meta_description']), 7)."</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
		";

		//BEGIN Options block
		$text .= "
				<fieldset id='core-newspost-edit-options'>
					<legend>".LAN_NEWS_53."</legend>
					<table cellpadding='0' cellspacing='0' class='adminedit'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".NWSLAN_15.":</td>
								<td class='control'>
									".$frm->radio('news_allow_comments', 0, !$_POST['news_allow_comments'])."".$frm->label(LAN_ENABLED, 'news_allow_comments', 0)."&nbsp;&nbsp;
									".$frm->radio('news_allow_comments', 1, $_POST['news_allow_comments'])."".$frm->label(LAN_DISABLED, 'news_allow_comments', 1)."
									<div class='field-help'>
										".NWSLAN_18."
									</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_73.":</td>
								<td class='control'>

		";


		$text .= "
										".$frm->radio_multi('news_render_type', $this->news_renderTypes, $_POST['news_render_type'], true)."
										<div class='field-help'>
											".NWSLAN_74."
										</div>
									</td>
								</tr>
								<tr>
									<td class='label'>".NWSLAN_19.":</td>
									<td class='control'>
										<div class='field-spacer'>".NWSLAN_21.":</div>
										<div class='field-spacer'>
		";

		$_startdate = ($_POST['news_start'] > 0) ? date("d/m/Y", $_POST['news_start']) : "";

		$cal_options['showsTime'] = false;
		$cal_options['showOthers'] = false;
		$cal_options['weekNumbers'] = false;
		$cal_options['ifFormat'] = "%d/%m/%Y";
		$cal_attrib['class'] = "tbox";
		$cal_attrib['size'] = "10";
		$cal_attrib['name'] = "news_start";
		$cal_attrib['value'] = $_startdate;
		$cal_attrib['tabindex'] = $frm->getNext();
		$text .= $this->_cal->make_input_field($cal_options, $cal_attrib);

		$text .= " - ";

		$_enddate = ($_POST['news_end'] > 0) ? date("d/m/Y", $_POST['news_end']) : "";

		unset($cal_options);
		unset($cal_attrib);
		$cal_options['showsTime'] = false;
		$cal_options['showOthers'] = false;
		$cal_options['weekNumbers'] = false;
		$cal_options['ifFormat'] = "%d/%m/%Y";
		$cal_attrib['class'] = "tbox";
		$cal_attrib['size'] = "10";
		$cal_attrib['name'] = "news_end";
		$cal_attrib['value'] = $_enddate;
		$cal_attrib['tabindex'] = $frm->getNext();
		$text .= $this->_cal->make_input_field($cal_options, $cal_attrib);

		$text .= "
										</div>
										<div class='field-help'>
											".NWSLAN_72."
										</div>
									</td>
								</tr>
								<tr>
									<td class='label'>".LAN_NEWS_32.":</td>
									<td class='control'>
										<div class='field-spacer'>
		";

		$_update_datestamp = ($_POST['news_datestamp'] > 0 && !strpos($_POST['news_datestamp'],"/")) ? date("d/m/Y H:i:s", $_POST['news_datestamp']) : trim($_POST['news_datestamp']);
		unset($cal_options);
		unset($cal_attrib);
		$cal_options['showsTime'] = true;
		$cal_options['showOthers'] = true;
		$cal_options['weekNumbers'] = false;
		$cal_options['ifFormat'] = "%d/%m/%Y %H:%M:%S";
		$cal_options['timeFormat'] = "24";
		$cal_attrib['class'] = "tbox";
		$cal_attrib['name'] = "news_datestamp";
		$cal_attrib['value'] = $_update_datestamp;
		$text .= $this->_cal->make_input_field($cal_options, $cal_attrib);

		$text .= "
										</div>
										<div class='field-spacer'>
											".$frm->checkbox('update_datestamp', '1', $_POST['update_datestamp']).$frm->label(NWSLAN_105, 'update_datestamp', '1')."
										</div>
										<div class='field-help'>
											".LAN_NEWS_33."
										</div>
									</td>
								</tr>
		";




        // --------------------- News Userclass ---------------------------

		$text .= "
								<tr>
									<td class='label'>".NWSLAN_22.":</td>
									<td class='control'>
										".$frm->uc_checkbox('news_userclass', $_POST['news_class'], 'nobody,public,guest,member,admin,classes,language', 'description=1')."
										<div class='field-help'>
											".NWSLAN_84."
										</div>
									</td>
								</tr>
								<tr>
									<td class='label'>".LAN_NEWS_28.":</td>
									<td class='control'>
										".$frm->checkbox('news_sticky', '1', $_POST['news_sticky']).$frm->label(LAN_NEWS_30, 'news_sticky', '1')."
										<div class='field-help'>
											".LAN_NEWS_29."
										</div>
									</td>
								</tr>
		";

		if($pref['trackbackEnabled']){
			$text .= "
								<tr>
									<td class='label'>".LAN_NEWS_34.":</td>
									<td class='control'>
										<a class='e-pointer' onclick='expandit(this);'>".LAN_NEWS_35."</a>
										<div class='e-hideme'>
											<div class='field-spacer'>
												<span class='smalltext'>".LAN_NEWS_37."</span>
											</div>
											<div class='field-spacer'>
												<textarea class='tbox textarea' name='trackback_urls' style='width:95%' cols='80' rows='5'>".$_POST['trackback_urls']."</textarea>
											</div>
										</div>
									</td>
								</tr>
			";
		}
		//triggerHook
		$data = array('method'=>'form', 'table'=>'news', 'id'=>$id, 'plugin'=>'news', 'function'=>'create_item');
		$hooks = e107::getEvent()->triggerHook($data);
		if(!empty($hooks))
		{
			$text .= "
								<tr>
									<td colspan='2' >".LAN_HOOKS." </td>
								</tr>
			";
			foreach($hooks as $hook)
			{
				if(!empty($hook))
				{
					$text .= "
								<tr>
									<td class='label'>".$hook['caption']."</td>
									<td class='control'>".$hook['text']."</td>
								</tr>
					";
				}
			}
		}

		$text .= "
						</tbody>
					</table>
				</fieldset>
				<div class='buttons-bar center'>
					".$frm->admin_button('preview', isset($_POST['preview']) ? NWSLAN_24 : NWSLAN_27 , 'submit')."
					".$frm->admin_button('submit_news', ($id && $sub_action != "sn" && $sub_action != "upload") ? NWSLAN_25 : NWSLAN_26 , 'update')."
					".$frm->checkbox('create_edit_stay', 1, isset($_POST['create_edit_stay'])).$frm->label(LAN_NEWS_54, 'create_edit_stay', 1)."
					<input type='hidden' name='news_id' value='{$id}' />
				</div>
			</form>
		</div>

		";

		$emessage = eMessage::getInstance();
		$e107->ns->tablerender($this->getSubAction() == 'edit' ? NWSLAN_29a : NWSLAN_29, $emessage->render().$text);
	}


	function preview_item($id)
	{
		$ix = new news;

		$e107 = &e107::getInstance();

		$_POST['news_title'] = $e107->tp->toDB($_POST['news_title']);
		$_POST['news_summary'] = $e107->tp->toDB($_POST['news_summary']);

		$_POST['news_id'] = $id;

		if($_POST['news_start'])
		{
			$tmp = explode("/", $_POST['news_start']);
			$_POST['news_start'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_start'] = 0;
		}

		if($_POST['news_end'])
		{
			$tmp = explode("/", $_POST['news_end']);
			$_POST['news_end'] = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		}
		else
		{
			$_POST['news_end'] = 0;
		}

		$matches = array();
		if(preg_match("#(.*?)/(.*?)/(.*?) (.*?):(.*?):(.*?)$#", $_POST['news_datestamp'], $matches))
		{
			$_POST['news_datestamp'] = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[1], $matches[3]);
		}
		else
		{
			$_POST['news_datestamp'] = time();
		}

		if($_POST['update_datestamp'])
		{
			$_POST['news_datestamp'] = time();
		}

		$e107->sql->db_Select("news_category", "*", "category_id='".intval($_POST['cat_id'])."'");
		list($_POST['category_id'], $_POST['category_name'], $_POST['category_icon']) = $e107->sql->db_Fetch();

	   	list($_POST['user_id'],$_POST['user_name']) = explode(chr(35), $_POST['news_author']);
		$_POST['news_author'] = $_POST['user_id'];
		$_POST['comment_total'] = $id ? $e107->sql->db_Count("comments", "(*)", " WHERE comment_item_id={$id} AND comment_type='0'") : 0;
		$_PR = $_POST;

		$_PR['news_body'] = $e107->tp->post_toHTML($_PR['news_body'],FALSE);
		$_PR['news_title'] = $e107->tp->post_toHTML($_PR['news_title'],FALSE,"emotes_off, no_make_clickable");
		$_PR['news_summary'] = $e107->tp->post_toHTML($_PR['news_summary']);
		$_PR['news_extended'] = $e107->tp->post_toHTML($_PR['news_extended']);
		$_PR['news_file'] = $_POST['news_file'];
		$_PR['news_thumbnail'] = basename($_POST['news_thumbnail']);

		//$ix->render_newsitem($_PR);

		return "
				<fieldset id='core-newspost-preview'>
					<legend>".NWSLAN_27."</legend>
					<table cellpadding='0' cellspacing='0' class='admininfo'>
					<tbody>
						<tr>
							<td class='label' colspan='2'>
								".$e107->tp->parseTemplate('{NEWSINFO}').$ix->render_newsitem($_PR, 'return')."
							</td>
						</tr>
					</tbody>
					</table>
				</fieldset>
		";
	}

	function ajax_exec_cat()
	{
		if(!getperms('0|7'))
		{
			exit;
		}
		//require_once (e_HANDLER.'js_helper.php');
		$e107 = e107::getInstance();

		$category = array();
		if ($e107->sql->db_Select("news_category", "*", "category_id=".$this->getId()))
		{
			$category = $e107->sql->db_Fetch();
		}

		if(empty($category))
		{
			e_jshelper::sendAjaxError(404, 'Page not found!', 'Requested news category was not found in the DB.', true);
		}
		$jshelper = new e_jshelper();

		$jshelper->addResponseAction('fill-form', $category);

		//show cancel and update, hide create buttons; disable create button (just in case)
		$jshelper->addResponseAction('element-invoke-by-id', array(
			'show' => 		'category-clear,update-category',
			'disabled,1' => 'create-category',
			'hide' => 		'create-category',
			'newsScrollToMe' => 'core-newspost-cat-create'
		));


		//Send the prefered response type
		$jshelper->sendResponse('XML');
	}

	function ajax_exec_cat_list_refresh()
	{
		if(!getperms('0|7'))
		{
			exit;
		}
		echo $this->show_categoriy_list();
	}

	function ajax_exec_catorder()
	{
		if(!getperms('0|7'))
		{
			exit;
		}
		//interactive category order
		$check = e107::getDb()->db_Update('news_category', 'category_order='.intval($this->getId()).' WHERE category_id='.intval($this->getSubAction()));
		if(e107::getDb()->getLastErrorNumber())
		{
			echo 'mySQL Error #'.e107::getDb()->getLastErrorNumber().': '.e107::getDb()->getLastErrorText();
			return;
		}
		if($check)
		{
			e107::getAdminLog()->log_event('NEWS_05', 'category_id='.intval($this->getSubAction()).', category_order='.intval($this->getId()), E_LOG_INFORMATIVE, '');
		}
	}

	function ajax_exec_catmanager()
	{
		if(!getperms('0|7'))
		{
			exit;
		}
		//interactive category manage permissions
		$check = e107::getDb()->db_Update('news_category', 'category_manager='.intval($this->getId()).' WHERE category_id='.intval($this->getSubAction()));
		if(e107::getDb()->getLastErrorNumber())
		{
			echo 'mySQL Error #'.e107::getDb()->getLastErrorNumber().': '.e107::getDb()->getLastErrorText();
			retrun;
		}
		if($check)
		{
			$class_name = e107::getUserClass()->uc_get_classname($this->getId());
			e107::getAdminLog()->log_event('NEWS_05', 'category_id='.intval($this->getSubAction()).', category_manager='.intval($this->getId()).' ('.$class_name.')', E_LOG_INFORMATIVE, '');
		}
	}

	function show_categories()
	{

		$frm = e107::getForm(false, true);

		$category = array();
		
		if ($this->getSubAction() == "edit" && !isset($_POST['update_category']))
		{
			if (e107::getDb()->db_Select("news_category", "*", "category_id=".$this->getId()))
			{
				$category = e107::getDb()->db_Fetch();
			}
			
		}

		if($this->error && (isset($_POST['update_category']) || isset($_POST['create_category'])))
		{
			foreach ($_POST as $k=>$v)
			{
				if(strpos($k, 'category_') === 0)
				{
					$category[$k] = e107::getParser()->post_toForm($v);
					continue;
				}

				if(strpos($k, 'news_rewrite_') === 0)
				{
					$category_rewrite[$k] = e107::getParser()->post_toForm($v);
					continue;
				}
			}
		}

		//FIXME - lan
		$text = "
			<form method='post' action='".e_SELF."?cat' id='core-newspost-cat-create-form'>
				<fieldset id='core-newspost-cat-create'>
					<legend>".NWSLAN_56."</legend>
					<table cellpadding='0' cellspacing='0' class='adminform'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".NWSLAN_52."</td>
								<td class='control'>
									".$frm->text('category_name', $category['category_name'], 200)."
									<div class='field-help'>Required field</div>
								</td>
							</tr>
							<tr>
								<td class='label'>Category friendly URL string</td>
								<td class='control'>
									".$frm->text('category_sef', $category['category_sef'], 200)."
									<div class='field-help'>If left empty will be automatically created from current Category Title based on your current <a href='".e_ADMIN_ABS."eurl.php?mode=main&amp;action=settings' title='To URL settings area' rel='external'>URL settings</a></div>
								</td>
							</tr>
							<tr>
								<td class='label'>Category meta keywords</td>
								<td class='control'>
									".$frm->text('category_meta_keywords', $category['category_meta_keywords'], 255)."
									<div class='field-help'>Used on news categoty list page</div>
								</td>
							</tr>
							<tr>
								<td class='label'>Category meta description</td>
								<td class='control'>
									".$frm->textarea('category_meta_description', $category['category_meta_description'], 5)."
									<div class='field-help'>Used on news categoty list page</div>
								</td>
							</tr>
							<tr>
								<td class='label'>Category management permissions</td>
								<td class='control'>
									".$frm->uc_select('category_manager',  vartrue($category['category_manager'], e_UC_ADMIN), 'main,admin,classes')."
									<div class='field-help'>Which group of site administrators are able to manage this category related news</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_53."</td>
								<td class='control'>
									".$frm->iconpicker('category_icon', $category['category_icon'], NWSLAN_54)."
									".$frm->hidden('category_order', $category['category_order'])."
									
								</td>
							</tr>
						</tbody>
					</table>
					<div class='buttons-bar center'>
		";

		if($this->getId())
		{
			$text .= "
				".$frm->admin_button('update_category', NWSLAN_55, 'update')."
				".$frm->admin_button('category_clear', LAN_CANCEL, 'cancel')."
				".$frm->hidden("category_id", $this->getId())."
			";
		}
		else
		{
			$text .= "
				".$frm->admin_button('create_category', NWSLAN_56, 'create')."
				".$frm->admin_button('update_category', NWSLAN_55, 'update', '', 'other=style="display:none"')."
				".$frm->admin_button('category_clear', LAN_CANCEL, 'cancel', '', 'other=style="display:none"')."
				".$frm->hidden("category_id", 0)."
			";
		}

		$text .= "
					</div>
				</fieldset>
			</form>
			<div id='core-newspost-cat-list-cont'>
				".$this->show_categoriy_list()."
			</div>
		";



		e107::getRender()->tablerender(NWSLAN_46a, e107::getMessage()->render().$text);
	}

	function show_categoriy_list()
	{
		$frm = e107::getForm();

		//FIXME - lan
		$text = "

			<form action='".e_SELF."?cat' id='core-newspost-cat-list-form' method='post'>
				<fieldset id='core-newspost-cat-list'>
					<legend>".NWSLAN_51."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='6'>
							<col style='width: 	5%'></col>
							<col style='width:  10%'></col>
							<col style='width:  40%'></col>
							<col style='width:  20%'></col>
							<col style='width:  15%'></col>
							<col style='width:  10%'></col>
						</colgroup>
						<thead>
							<tr>
								<th class='center'>".LAN_NEWS_45."</th>
								<th class='center'>".NWSLAN_122."</th>
								<th>".NWSLAN_6."</th>
								<th>Manage Permissions</th>
								<th class='center last'>".LAN_OPTIONS."</th>
								<th class='center'>Order</th>
							</tr>
						</thead>
						<tbody>
		";
		if ($category_total = e107::getDb()->db_Select_gen("SELECT ncat.* FROM #news_category AS ncat  ORDER BY ncat.category_order ASC"))
		{
			$tindex = 100;
			while ($category = e107::getDb()->db_Fetch()) {

				$icon = '';
				if ($category['category_icon'])
				{
					$icon = (strstr($category['category_icon'], "images/") ? THEME_ABS.$category['category_icon'] : (strpos($category['category_icon'], '{') === 0 ? e107::getParser()->replaceConstants($category['category_icon'], 'abs') : e_IMAGE_ABS."icons/".$category['category_icon']));
					$icon = "<img class='icon action' src='{$icon}' alt='' />";
				}

				$url = '<a href="'.e107::getUrl()->create('news/list/category', $category).'" title="'.$category['category_name'].'" rel="external">'.$category['category_name'].'</a>';
				$text .= "
							<tr>
								<td class='center middle'>{$category['category_id']}</td>
								<td class='center middle'>{$icon}</td>
								<td class='middle'>{$url}</td>
								<td class='middle'>".$frm->uc_select('multi_category_manager['.$category['category_id'].']',  vartrue($category['category_manager'], e_UC_ADMIN), 'main,admin,classes')."</td>
								<td class='center middle'>
									<a class='action' id='core-news-catedit-{$category['category_id']}' href='".e_SELF."?cat.edit.{$category['category_id']}' tabindex='".$frm->getNext()."'>".defset('ADMIN_EDIT_ICON', '<img src="'.e_IMAGE_ABS.'admin_images/edit_16.png" alt="Edit" />')."</a>
									".$frm->submit_image("delete[category_{$category['category_id']}]", $category['category_id'], 'delete', e107::getParser()->toJS(NWSLAN_37." [ID: {$category['category_id']} ]"))."
								</td>
								<td class='middle center'>".$frm->text('multi_category_order['.$category['category_id'].']', $category['category_order'], 3, 'size=2&tabindex='.$tindex)."</td>
							</tr>
				";
				$tindex++;
			}

			$text .= "
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$frm->admin_button('multi_update_category', LAN_UPDATE, 'update e-hide-if-js')."
						".$frm->admin_button('trigger_list_refresh', 'Refresh List', 'refresh')."
					</div>
			";
			}
			else
			{
				$text .= "<div class='center'>".NWSLAN_10."</div>";
			}

		$text .= "
				</fieldset>
			</form>
		";

		return $text;
	}

	function _optrange($num, $zero = true)
	{
		$tmp = range(0, $num < 0 ? 0 : $num);
		if(!$zero) unset($tmp[0]);

		return $tmp;
	}

	function ajax_exec_pref_archnum()
	{
		$frm = e107::getForm();
		echo $frm->selectbox('newsposts_archive', $this->_optrange(intval($this->getSubAction()) - 1), intval(e107::getPref('newsposts_archive')), 'class=tbox&tabindex='.intval($this->getId()));
	}

/*
    function ajax_exec_searchValue()
	{
		$frm = e107::getForm();
		echo $frm->filterValue($_POST['filtertype'], $this->fields);
	}
*/

	function show_news_prefs()
	{
		$pref = e107::getPref();
		$frm = e107::getForm();

		$text = "
			<form method='post' action='".e_SELF."?pref' id='core-newspost-settings-form'>
				<fieldset id='core-newspost-settings'>
					<legend class='e-hideme'>".NWSLAN_90."</legend>
					<table cellpadding='0' cellspacing='0' class='adminform'>
						<colgroup span='2'>
							<col class='col-label' />
							<col class='col-control' />
						</colgroup>
						<tbody>
							<tr>
								<td class='label'>".NWSLAN_127."</td>
								<td class='control'>
									".$frm->text('news_sefbase', $pref['news_sefbase'])."
									<div class='field-help'>".sprintf(NWSLAN_128, e_ADMIN_ABS.'eurl.php').'<strong>'.SITEURL.($pref['news_sefbase'] ? $pref['news_sefbase'].'/' : '')."</strong></div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_86."</td>
								<td class='control'>
									".$frm->checkbox_switch('news_cats', 1, $pref['news_cats'])."
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_87."</td>
								<td class='control'>
									".$frm->selectbox('nbr_cols', $this->_optrange(6, false), $pref['nbr_cols'], 'class=tbox')."
								</td>
							</tr>
							<tr>
							<td class='label'>".NWSLAN_88."</td>
							<td class='control'>
								".$frm->selectbox('newsposts', $this->_optrange(50, false), $pref['newsposts'], 'class=tbox')."
							</td>
							</tr>
		";


		// ##### ADDED FOR NEWS ARCHIVE --------------------------------------------------------------------
		// the possible archive values are from "0" to "< $pref['newsposts']"
		// this should really be made as an onchange event on the selectbox for $pref['newsposts'] ...
		//SecretR - Done
		$text .= "
							<tr>
								<td class='label'>".NWSLAN_115."</td>
								<td class='control'>
									<div id='newsposts-archive-cont'>".$frm->selectbox('newsposts_archive', $this->_optrange(intval($pref['newsposts']) - 1), intval($pref['newsposts_archive']), 'class=tbox')."</div>
									<div class='field-help'>".NWSLAN_116."</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_117."</td>
								<td class='control'>
									".$frm->text('newsposts_archive_title', $pref['newsposts_archive_title'])."
								</td>
							</tr>
		";
		// ##### END --------------------------------------------------------------------------------------

		$text .= "
							<tr>
								<td class='label'>".LAN_NEWS_51."</td>
								<td class='control'>
									".$frm->uc_select('news_editauthor', $pref['news_editauthor'], 'nobody,main,admin,classes')."
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_106."</td>
								<td class='control'>
									".$frm->uc_select('subnews_class', $pref['subnews_class'], 'nobody,public,guest,member,admin,classes')."
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_107."</td>
								<td class='control'>
									".$frm->checkbox_switch('subnews_htmlarea', '1', $pref['subnews_htmlarea'])."
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_100."</td>
								<td class='control'>
									".$frm->checkbox_switch('subnews_attach', '1', $pref['subnews_attach'])."
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_101."</td>
								<td class='control'>
									".$frm->text('subnews_resize', $pref['subnews_resize'], 5, 'size=6&class=tbox')."
									<div class='field-help'>".NWSLAN_102."</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_111."</td>
								<td class='control'>
									<div class='auto-toggle-area autocheck'>
										".$frm->checkbox_switch('news_newdateheader', '1', $pref['news_newdateheader'])."
										<div class='field-help'>".NWSLAN_112."</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_113."</td>
								<td class='control'>
									<div class='auto-toggle-area autocheck'>
										".$frm->checkbox_switch('news_unstemplate', '1', $pref['news_unstemplate'])."
										<div class='field-help'>".NWSLAN_114."</div>
									</div>
								</td>
							</tr>
							<tr>
								<td class='label'>".NWSLAN_120."</td>
								<td class='control'>
									".$frm->bbarea('news_subheader', stripcslashes($pref['news_subheader']), 2, 'helpb')."
								</td>
							</tr>
						</tbody>
					</table>
					<div class='buttons-bar center'>
						".$frm->admin_button('save_prefs', NWSLAN_89, 'update')."
					</div>
				</fieldset>
			</form>
		";
		e107::getRender()->tablerender(NWSLAN_90, e107::getMessage()->render().$text);
	}


	function show_submitted_news()
	{	
	
	//TODO - image upload path should be e_MEDIA and using generic upload handler on submitnews.php. 
	
		$e107 = e107::getInstance();
		$frm = e107::getForm();
		$tp = e107::getParser();
		$sql = e107::getDb();
		
		$newsCat = array();
		$sql->db_Select('news_category');
		while($row = $sql->db_Fetch())
		{
			$newsCat[$row['category_id']] = $tp->toHTML($row['category_name'],FALSE,'TITLE');
		}
		
		
		if ($sql->db_Select("submitnews", "*", "submitnews_id !='' ORDER BY submitnews_id DESC"))
		{
			$text .= "
			<form action='".e_SELF."?sn' method='post'>
				<fieldset id='core-newspost-sn-list'>
					<legend class='e-hideme'>".NWSLAN_47."</legend>
					<table cellpadding='0' cellspacing='0' class='adminlist'>
						<colgroup span='6'>
							<col style='width: 2%'></col>
							<col style='width: 5%'></col>
							<col style='width: 60%'></col>
							<col style='width: auto'></col>
							<col style='width: auto'></col>
							<col style='width: auto'></col>
							<col style='width: auto'></col>
							<col style='width: 20%'></col>
						</colgroup>
						<thead>
							<tr>
								<th class='center'>&nbsp;</th>
								<th class='center'>ID</th>
								<th>".NWSLAN_57."</th>
								<th>".LAN_DATE."</th>
								<th>".LAN_AUTHOR."</th>
								<th>".NWSLAN_6."</th>
								<th>".NWSLAN_123."</th>
								<th class='center last'>".LAN_OPTIONS."</th>							
							</tr>
						</thead>
						<tbody>
			";
			while ($row = $sql->db_Fetch())
			{
				$buttext = ($row['submitnews_auth'] == 0)? NWSLAN_58 :	NWSLAN_103;

				if (substr($row['submitnews_item'], -7, 7) == '[/html]') $row['submitnews_item'] = substr($row['submitnews_item'], 0, -7);
				if (substr($row['submitnews_item'],0 , 6) == '[html]') $row['submitnews_item'] = substr($row['submitnews_item'], 6);

				$text .= "
					<tr>
						<td class='center'><input type='checkbox' name='news_selected[".$row['submitnews_id']."]' value='".$row['submitnews_id']."' /></td>
						<td class='center'>{$row['submitnews_id']}</td>
						<td>
					
						<a href=\"javascript:expandit('submitted_".$row['submitnews_id']."')\">";
				$text .= $tp->toHTML($row['submitnews_title'],FALSE,'TITLE');
				$text .= '</a>';
			//	$text .=  [ '.NWSLAN_104.' '.$submitnews_name.' '.NWSLAN_108.' '.date('D dS M y, g:ia', $submitnews_datestamp).']<br />';
				$text .= "<div id='submitted_".$row['submitnews_id']."' style='display:none'>".$tp->toHTML($row['submitnews_item'],TRUE);
				$text .= ($row['submitnews_file']) ? "<br /><img src='".e_IMAGE_ABS."newspost_images/".$row['submitnews_file']."' alt=\"".$row['submitnews_file']."\" />" : "";
				$text .= "
				</div>
						
						</td>";
						
				$text .= "<td class='nowrap'>".date('D jS M, Y, g:ia', $row['submitnews_datestamp'])."</td>
				<td><a href=\"mailto:".$row['submitnews_email']."?subject=[".SITENAME."] ".trim($row['submitnews_title'])."\" title='".$row['submitnews_email']." - ".$e107->ipDecode($row['submitnews_ip'])."'>".$row['submitnews_name']."</a></td>
				<td>".$newsCat[$row['submitnews_category']]."</td>
				<td class='center'>".($row['submitnews_auth'] == 0 ?  "-" : ADMIN_TRUE_ICON)."</td>		
						
				
						<td>
							<div class='field-spacer center nowrap'>
								".$frm->admin_button("category_view_{$row['submitnews_id']}", NWSLAN_27, 'action', '', array('id'=>false, 'other'=>"onclick=\"expandit('submitted_".$row['submitnews_id']."')\""))."
								".$frm->admin_button("category_edit_{$row['submitnews_id']}", $buttext, 'action', '', array('id'=>false, 'other'=>"onclick=\"document.location='".e_SELF."?create.sn.{$row['submitnews_id']}'\""))."
								".$frm->admin_button("delete[sn_{$row['submitnews_id']}]", LAN_DELETE, 'delete', '', array('id'=>false, 'title'=>$e107->tp->toJS(NWSLAN_38." [".LAN_NEWS_45.": {$row['submitnews_id']} ]")))."
							</div>
						</td>
					</tr>
				";
			}
			
			
			$text .= "
						</tbody>
					</table>";
			$text .= "<div class='buttons-bar center'>";
			$text .= e107::getForm()->batchoptions(array(
				'subdelete_selected'		=> LAN_DELETE,
				'subcategory' 				=> array('Modify Category', $newsCat)
				));
				
			
			$text .= "</div>
			
			
				</fieldset>
				
			</form>
			";
		}
		else
		{
			$text .= "<div class='center'>".NWSLAN_59."</div>";
		}
		e107::getRender()->tablerender(NWSLAN_47, e107::getMessage()->render().$text);
	}



	function showMaintenance()
	{
		require_once(e_HANDLER."form_handler.php");
		$frm = e107::getForm();


		$text = "
			<form method='post' action='".e_SELF."?maint' id='core-newspost-maintenance-form'>
				<fieldset id='core-newspost-maintenance'>
					<legend class='e-hideme'>".LAN_NEWS_59."</legend>
					<table class='adminform' cellpadding='0' cellspacing='0'>
					<colgroup span='3'>
						<col class='col-label'></col>
						<col class='col-control'></col>
						<col class='col-control'></col>
					</colgroup>
					<tbody>
						<tr>
							<td class='label'>".LAN_NEWS_56."</td>
							<td class='control'>
								".$frm->checkbox('newsdeletecomments', '1', '0').LAN_NEWS_61."
							</td>
							<td class='control'>
								".$frm->admin_button('news_comments_recalc', LAN_NEWS_57, 'update')."
							</td>
						</tr>
					</tbody>
					</table>
				</fieldset>
			</form>
		";

		e107::getRender()->tablerender(LAN_NEWS_59, e107::getMessage()->render().$text);
	}


	function _observe_newsCommentsRecalc()
	{
		if(!getperms('0'))
		{
			$this->noPermissions();
		}

		$qry = "SELECT 
			COUNT(`comment_id`) AS c_count,
			`news_id`, `news_comment_total`, `news_allow_comments`
			FROM `#news` LEFT JOIN `#comments` ON `news_id`=`comment_item_id` 
			WHERE (`comment_type`='0') OR (`comment_type`='news')
			GROUP BY `comment_item_id`";

		$deleteCount = 0;
		$updateCount = 0;
		$canDelete = isset($_POST['newsdeletecomments']);
		if ($result = e107::getDb()->db_Select_gen($qry))
		{
			while ($row = e107::getDb()->db_Fetch(MYSQL_ASSOC))
			{
				if ($canDelete && ($row['news_allow_comments'] != 0) && ($row['c_count'] > 0))	// N.B. sense of 'news_allow_comments' is 0 = allow!!!
				{		// Delete comments
					e107::getDb('sql2')->db_Delete('comments', 'comment_item_id='.$row['news_id']);
					$deleteCount = $deleteCount + $row['c_count'];
					$row['c_count'] = 0;		// Forces update of news table if necessary
				}
				if ($row['news_comment_total'] != $row['c_count'])
				{
					e107::getDb('sql2')->db_Update('news', 'news_comment_total = '.$row['c_count'].' WHERE news_id='.$row['news_id']);
					$updateCount++;
				}
			}
			$this->show_message(str_replace(array('--UPDATE--', '--DELETED--'), array($updateCount, $deleteCount), LAN_NEWS_58), E_MESSAGE_SUCCESS);
		}
		else
		{
			$this->show_message(LAN_NEWS_62, E_MESSAGE_WARNING);
		}
	}



	function show_message($message, $type = E_MESSAGE_INFO, $session = false)
	{
		// ##### Display comfort ---------
		e107::getMessage()->add($message, $type, $session);
	}

	function noPermissions($qry = '')
	{
		$url = e_SELF.($qry ? '?'.$qry : '');
		if($qry !== e_QUERY)
		{
			$this->show_message('Insufficient permissions!', E_MESSAGE_ERROR, true);
			session_write_close();
			header('Location: '.$url);
		}
		exit;
	}

	function show_options()
	{
		$e107 = e107::getInstance();

		$var['main']['text'] = NWSLAN_44;
		$var['main']['link'] = e_SELF;
		$var['main']['perm'] = "H";

		$var['create']['text'] = NWSLAN_45;
		$var['create']['link'] = e_SELF."?create";
		$var['create']['perm'] = "H";

		$var['cat']['text'] = NWSLAN_46;
		$var['cat']['link'] = e_SELF."?cat";
		$var['cat']['perm'] = "7";

		$var['pref']['text'] = NWSLAN_90;
		$var['pref']['link'] = e_SELF."?pref";
		$var['pref']['perm'] = "0";

//TODO remove commented code before release.
	//	$c = $e107->sql->db_Count('submitnews');
	//	if ($c) {
			$var['sn']['text'] = NWSLAN_47." ({$c})";
			$var['sn']['link'] = e_SELF."?sn";
			$var['sn']['perm'] = "N";
	//	}

		if (getperms('0'))
		{
			$var['maint']['text'] = LAN_NEWS_55;
			$var['maint']['link'] = e_SELF."?maint";
			$var['maint']['perm'] = "N";
		}

		e_admin_menu(NWSLAN_48, $this->getAction(), $var);
	}

}

function newspost_adminmenu()
{
	e107::getRegistry('_newspost_admin')->show_options();
}
