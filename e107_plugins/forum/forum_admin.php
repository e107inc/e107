<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

$eplug_admin = true;
require_once(__DIR__.'/../../class2.php');
if (!getperms('P'))
{
	e107::redirect('admin');
	exit;
}


e107::lan('forum', 'global', true);
e107::lan('forum', 'admin', true);
e107::lan('forum', 'front', true);


e107::css('inline',"

	img.level-1 { margin:0 5px 0 25px; }
	img.level-2 { margin:0 5px 0 50px; }

");





	class forum_admin extends e_admin_dispatcher
	{
		protected $modes = array(

			'main'	=> array(
				'controller' 	=> 'forum_ui',
				'path' 			=> null,
				'ui' 			=> 'forum_form_ui',
				'uipath' 		=> null
			),
			'mods'	=> array(
				'controller' 	=> 'forum_ui',
				'path' 			=> null,
				'ui' 			=> 'forum_form_ui',
				'uipath' 		=> null
			),
			'report'	=> array(
				'controller' 	=> 'reported_ui',
				'path' 			=> null,
				'ui' 			=> 'reported_form_ui',
				'uipath' 		=> null
			),
			'post'	=> array(
				'controller' 	=> 'post_ui',
				'path' 			=> null,
				'ui' 			=> 'post_form_ui',
				'uipath' 		=> null
			),

		);

		protected $adminMenu = array(
			'main/list'			=> array('caption' => LAN_MANAGE, 'perm' => 'P'),
			'main/create'		=> array('caption' => LAN_CREATE, 'perm' => 'P'),

			'main/opt1'         => array('divider' => true),

			'main/rules'		=> array('caption' => WMGLAN_5, 'perm' => 'P'),
			'mods/list'         => array('caption' => LAN_FORUM_2003, 'perm'=>'P', 'icon'=>'fas-user-tie'),
			'main/prefs' 		=> array('caption' => LAN_PREFS, 'perm' => 'P'),

			'main/opt2'         => array('divider' => true),

			'report/list'       => array('caption' => FORLAN_116, 'perm'=>'P', 'icon'=>'fas-flag'),
			'post/list'         => array('caption' => LAN_PLUGIN_FORUM_LATESTPOSTS, 'perm'=>'P'),
			'main/prune'		=> array('caption' => LAN_PRUNE, 'perm' => 'P', 'icon'=>'fas-filter'),
			'main/tools'        => array('caption' => FORLAN_153, 'perm'=>'p')
		);

		protected $adminMenuAliases = array(
			'main/edit'	=> 'main/list'
		);

		protected $menuTitle = LAN_PLUGIN_FORUM_NAME;

		function init()
		{
			if(E107_DEBUG_LEVEL > 0)
			{
				$this->adminMenu['opt3'] = array('divider'=>true);
				$this->adminMenu['main/update'] = array('caption'=>"Redo v1.x Forum Upgrade", 'perm'=>0, 'uri'=>'{e_PLUGIN}forum/forum_update.php');
			}
		}
	}

	class forum_ui extends e_admin_ui
	{

		protected $pluginTitle		= LAN_PLUGIN_FORUM_NAME;
		protected $pluginName		= 'forum';
		//	protected $eventName		= 'forum-forum'; // remove comment to enable event triggers in admin.
		protected $table			= 'forum';
		protected $pid				= 'forum_id';
		protected $perPage			= 30;
		protected $batchDelete		= true;
		protected $batchCopy		= true;
		protected $sortField		= 'forum_order';
		protected $sortParent       = 'forum_parent';
		protected $orderStep		= 50;

		/*
		//	protected $tabs				= array('Tabl 1','Tab 2'); // Use 'tab'=>0  OR 'tab'=>1 in the $fields below to enable.
		//	protected $listQry      	= "SELECT *, xAND forum_order > 45 THEN forum_order ELSE forum_order + forum_parent END AS Sort FROM `#forum` "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.
		//	protected $listQry      	= "SELECT *, CASE WHEN forum_parent = 0 OR forum_order =0 THEN forum_id + (forum_order/1000) ELSE  forum_parent + (forum_order/1000) END AS Sort FROM `#forum` "; // Example Custom Query. LEFT JOINS allowed. Should be without any Order or Limit.
		//	protected $listOrder 	    = ' COALESCE(NULLIF(forum_parent,0), forum_order), forum_parent > 0, forum_order '; //FIXME works with parent/child but doesn't respect parent order.
		//	protected $listQry           = "SELECT * , forum_parent + forum_order AS Sort FROM `#forum` ";
		*/

		protected $listQry          = "SELECT a. *, CASE WHEN a.forum_parent = 0 THEN a.forum_order ELSE b.forum_order + (( a.forum_order)/1000) END AS Sort FROM `#forum` AS a LEFT JOIN `#forum` AS b ON a.forum_parent = b.forum_id ";
		protected $listOrder		= 'Sort,forum_order ';
	//	protected $listOrder        = 'forum_order';
	//	protected $listOrder 	    = ' COALESCE(NULLIF(forum_parent,0), forum_id), forum_parent > 0, forum_order ';

		protected $fields 		= array (
			'checkboxes'                =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
			'forum_id'                  =>   array ( 'title' => LAN_ID, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'forum_name'                =>   array ( 'title' => LAN_TITLE, 'type' => 'method', 'inline'=>true,  'data' => 'str', 'width' => '40%', 'help' => FORLAN_223, 'readParms' => '', 'writeParms' => '', 'validate' => true,'class' => 'left', 'thclass' => 'left',  ),
			'forum_sef'                 =>   array ( 'title' => LAN_SEFURL, 'type' => 'text', 'batch'=>true, 'inline'=>true, 'noedit'=>false, 'data' => 'str', 'width' => 'auto', 'help' => 'Leave blank to auto-generate it from the title above.', 'readParms' => '', 'writeParms' => 'sef=forum_name&size=xxlarge', 'class' => 'left', 'thclass' => 'left',  ),
			'forum_description'         =>   array ( 'title' => LAN_DESCRIPTION, 'type' => 'textarea', 'data' => 'str', 'width' => '30%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'forum_image'               =>   array ( 'title' => LAN_IMAGE, 'type' => 'image', 'batch'=>false, 'inline'=>false, 'noedit'=>false, 'data' => 'str', 'width' => 'auto', 'help' => 'Image that will be displayed using {FORUMIMAGE}', 'readParms' => '', 'writeParms' => 'media=forum&max=1', 'class' => 'center', 'thclass' => 'center',  ),
			'forum_icon'               	=> 	 array ('title' => LAN_ICON, 'type' => 'icon', 'data' => 'str', 'width' => 'auto', 'help' => 'Icon that will be displayed using {FORUMICON}', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'center',),
			'forum_parent'              =>   array ( 'title' => FORLAN_75, 'type' => 'dropdown', 'data' => 'int', 'width' => '10%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'forum_sub'                 =>   array ( 'title' => LAN_FORUM_1002, 'type' => 'dropdown', 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => array(), 'writeParms' => array(), 'class' => 'center', 'thclass' => 'center',  ),
			'forum_moderators'          =>   array ( 'title' => LAN_FORUM_2003, 'type' => 'userclass', 'inline'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => 'classlist=admin,main,classes', 'writeParms' => "classlist=admin,main,mods,classes", 'class' => 'left', 'thclass' => 'left',  ),
			'forum_threads'             =>   array ( 'title' => LAN_FORUM_1003, 'type' => 'number', 'data' => 'int', 'noedit'=>true, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
			'forum_replies'             =>   array ( 'title' => LAN_FORUM_0003, 'type' => 'number', 'data' => 'int', 'noedit'=>true, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
			'forum_lastpost_user'       =>   array ( 'title' => LAN_AUTHOR, 'type' => 'hidden', 'noedit'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'forum_lastpost_user_anon'  =>   array ( 'title' => FORLAN_204, 'type' => 'hidden','noedit'=>true, 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
			'forum_lastpost_info'       =>   array ( 'title' => LAN_PLUGIN_FORUM_LATESTPOSTS, 'type' => 'hidden', 'noedit'=>true, 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
			'forum_class'               =>   array ( 'title' => LAN_VISIBILITY, 'type' => 'userclass', 'data' => 'int', 'width' => 'auto', 'batch' => true, 'filter' => true, 'inline' => true, 'help' => '', 'readParms' => array('classlist'=>'public,guest,nobody,member,admin,main,new,mods,classes'), 'writeParms' => array('classlist'=>'public,guest,nobody,member,admin,main,new,mods,classes'), 'class' => 'left', 'thclass' => 'left',  ),
			'forum_order'               =>   array ( 'title' => LAN_ORDER, 'type' => 'text', 'data' => 'int', 'inline'=>true, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'forum_postclass'           =>   array ( 'title' => FORLAN_205, 'type' => 'userclass', 'inline'=>true,'filter'=>true, 'batch'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => array('classlist'=>'public,guest,nobody,member,admin,main,new,mods,classes'), 'writeParms' => array('classlist'=>'public,guest,nobody,member,admin,main,new,mods,classes'), 'class' => 'center', 'thclass' => 'center',  ),
			'forum_threadclass'         =>   array ( 'title' => FORLAN_206, 'type' => 'userclass', 'inline'=>true, 'filter'=>true, 'batch'=>true, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => array('classlist'=>'public,guest,nobody,member,admin,main,new,mods,classes'), 'writeParms' => array('classlist'=>'public,guest,nobody,member,admin,main,new,mods,classes'), 'class' => 'center', 'thclass' => 'center',  ),
			'forum_datestamp'           =>   array ( 'title' => LAN_DATESTAMP, 'type' => 'datestamp', 'data' => 'int', 'noedit'=>true, 'width' => 'auto', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'right', 'thclass' => 'center',  ),
			//             'Sort' =>   array ( 'title' => 'Sort', 'type' => 'text', 'data' => 'str', 'noedit'=>true, 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
			'forum_options'             =>   array ( 'title' => LAN_OPTIONS, 'type' => 'hidden', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
			'options'                   =>   array ( 'title' => LAN_OPTIONS, 'type' => null, 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1', 'sort'=>1 ),
		);

		protected $fieldpref = array('forum_name', 'forum_parent', 'Sort', 'forum_description',  'forum_class', 'forum_postclass', 'forum_threadclass', 'forum_order');

		protected $preftabs = array();


		protected $prefs = array(
			'enclose'		        => array('title' => FORLAN_44, 'type'=>'boolean', 'data' => 'int','help'=>FORLAN_45),
			'title'		            => array('title' => FORLAN_65, 'type'=>'text', 'data' => 'str','help'=>''),
			'notify'		        => array('title' => FORLAN_47, 'type'=>'boolean', 'data' => 'int','help'=>FORLAN_48),
			'notify_on'	            => array('title' => FORLAN_177, 'type'=>'boolean', 'data' => 'int','help'=>FORLAN_178),
			'poll'		            => array('title' => FORLAN_49, 'type'=>'userclass', 'data' => 'int','help'=>FORLAN_50),
			'attach'		        => array('title' => FORLAN_70, 'type'=>'boolean', 'data' => 'str','help'=>FORLAN_71),
			'maxwidth'	            => array('title' => FORLAN_134, 'type'=>'number', 'data' => 'str','help'=>FORLAN_135),
			'linkimg'		        => array('title' => FORLAN_136, 'type'=>'boolean', 'data' => 'int','help'=>FORLAN_137),
			'track'		            => array('title' => FORLAN_200, 'type'=>'boolean', 'data' => 'int','help'=>FORLAN_201),
			'trackemail'		    => array('title' => FORLAN_202, 'type'=>'boolean', 'data' => 'int','help'=>FORLAN_203),
			'redirect'	            => array('title' => FORLAN_112, 'type'=>'boolean', 'data' => 'int','help'=>FORLAN_113),
			//'reported_post_email'	=> array('title' => FORLAN_116, 'type'=>'boolean', 'data' => 'int','help'=>FORLAN_122),
			'tooltip'	            => array('title' => FORLAN_126, 'type'=>'boolean', 'data' => 'int','help'=>FORLAN_127),
			'ranks'                 => array('title' => FORLAN_63, 'type'=>'boolean', 'data' => 'int','help'=>''),
			'tiplength'	            => array('title' => FORLAN_128, 'type'=>'number', 'data' => 'int','help'=>FORLAN_129),
			'eprefix'	            => array('title' => FORLAN_53, 'type'=>'text', 'data' => 'string','help'=>FORLAN_54),
			'popular'	            => array('title' => FORLAN_55, 'type'=>'number', 'data' => 'int','help'=>FORLAN_56),
			'postspage'	            => array('title' => FORLAN_57, 'type'=>'number', 'data' => 'int','help'=>FORLAN_58),
			'threadspage'	        => array('title' => FORLAN_186, 'type'=>'number', 'data' => 'int','help'=>FORLAN_187),

			'editor'                => array('title' => FORLAN_220, 'type' => 'dropdown', 'data' => 'str', 'readParms' => array(), 'writeParms' => array('optArray' => array(), 'defaultValue' => 'default'), 'help' => FORLAN_221),
			'quickreply'            => array('title' => FORLAN_222, 'type' => 'dropdown', 'data' => 'str', 'readParms' => array(), 'writeParms' => array('optArray' => array(), 'defaultValue' => 'default'), 'help' => '')
		);

		public $forumParents = array();
		public $forumObj    = null;


		// Correct bad ordering based on parent/child relationship.
		private function checkOrder()
		{
			$sql = e107::getDb();
			$sql2 = e107::getDb('sql2');
			$count = $sql->select('forum', 'forum_id', 'forum_order = 0');

			if($count > 1)
			{
				$sql->gen("SELECT forum_id,forum_name,forum_parent,forum_order FROM `#forum` ORDER BY COALESCE(NULLIF(forum_parent,0), forum_id), forum_parent > 0, forum_order ");

				$c = 0;
				while($row = $sql->fetch())
				{
					//print_a($row);

					if($row['forum_parent'] == 0)
					{
						$c = $c + 100;
					}
					else
					{
						$c = $c+1;
					}

					$sql2->update('forum', 'forum_order = '.$c.' WHERE forum_id = '.$row['forum_id'].' LIMIT 1');
				}
			}
		}


		public function init()
		{

			$this->checkOrder();


			$this->prefs['editor']['writeParms']['optArray']['default'] = FORLAN_217; 
			$this->prefs['editor']['writeParms']['optArray']['bbcode'] = 'BBCode';

			$editors = e107::getPlug()->getInstalledWysiwygEditors();
			if (!empty($editors))
			{
				$this->prefs['editor']['writeParms']['optArray'] = array_merge($this->prefs['editor']['writeParms']['optArray'], $editors);
			}

			$this->prefs['quickreply']['writeParms']['optArray'] = array(
				'default' => FORLAN_218, 
				'wysiwyg' => FORLAN_219 
			);

			if(e107::isInstalled('poll') == false)
			{
				$this->prefs['poll']['writeParms']['post'] = " <span class='label label-important label-danger'>".FORLAN_215."</span>";
			}

			if($this->getMode() == 'mods')
			{
				$this->fieldpref = array('forum_name', 'forum_moderators');
				$this->fields['forum_moderators']['width'] = '50%';
				$this->fields['options']['nolist'] = true;
				$this->fields['options']['forced'] = false;
				$this->fields['forum_class']['batch'] = false;
				$this->fields['forum_postclass']['batch'] = false;
				$this->fields['forum_threadclass']['batch'] = false;
				$this->fields['forum_moderators']['batch'] = true;
			}

			require_once(e_PLUGIN.'forum/forum_class.php');
			$this->forumObj	=  new e107forum;

			if(E107_DEBUG_LEVEL > 0) // check fpr legacy prefs in debug mode. Should normally be done during upgrade.
			{
				$this->forumObj->upgradeLegacyPrefs();
			}

			if (!empty($_POST['do_prune']) && !empty($_POST['prune_days']) && !empty($_POST['pruneForum']))
			{
				$msg = $this->forumObj->forumPrune($_POST['prune_type'], $_POST['prune_days'], $_POST['pruneForum']);
				e107::getMessage()->addSuccess($msg);
			}

			if(!empty($_POST['frsubmit']))
			{
				$this->saveRules();
			}

			if($this->getAction() == 'edit')
			{
				$this->fields['forum_order']['noedit'] = true;
			}

			$data = e107::getDb()->retrieve('forum', 'forum_id,forum_name,forum_parent', 'forum_id != 0',true);
			$this->forumParents[0] = FORLAN_216;
			$forumSubParents = array();

			foreach($data as $val)
			{
				$id = $val['forum_id'];

				if($val['forum_parent'] == 0)
				{
					$this->forumParents[$id] = $val['forum_name'];
				}
				else
				{
					$forumSubParents[$id] = $val['forum_name'];
				}

			}

			$this->fields['forum_parent']['writeParms'] = $this->forumParents;
			$this->fields['forum_sub']['writeParms']['optArray'] = $forumSubParents;
			$this->fields['forum_sub']['writeParms']['default'] = 'blank';
			
		}


		// ------- Customize Create --------

		public function afterSort($result, $selected)
		{
			return;

			$sql = e107::getDb();

			$data2 = $sql->retrieve('forum','forum_id,forum_name,forum_parent,forum_order','forum_parent = 0',true);
			foreach($data2 as $val)
			{
				$id = $val['forum_id'];
				$parent[$id] = $val['forum_order'];
			}

			$previous = 0;

			$data = $sql->retrieve('forum','*','forum_parent != 0 ORDER BY forum_order',true);
			foreach($data as $row)
			{
				$p = $row['forum_parent'];

				if($p != $previous)
				{
					$c = $parent[$p];
				}

				$c++;
				$previous = $p;

			//	echo "<br />".$row['forum_name']." with parent: ".$p." old: ".$row['forum_order']."  new: ".$c;
				$sql->update('forum','forum_order = '.$c.' WHERE forum_id = '.intval($row['forum_id']).' LIMIT 1');
			}
		}



		public function beforeCreate($new_data, $old_data)
		{
			$sql = e107::getDb();
			$parentOrder = $sql->retrieve('forum','forum_order','forum_id='.$new_data['forum_parent']." LIMIT 1");

			$new_data['forum_order'] = $parentOrder + 50;

			if (!empty($new_data['forum_name']))
			{
				// make sure the forum_name contains only plain text, no bbcode or html
				$new_data['forum_name'] = trim(e107::getParser()->toText($new_data['forum_name']));
			}

			if(empty($new_data['forum_sef']))
			{
				$new_data['forum_sef'] = eHelper::title2sef($new_data['forum_name']);
			}
			else
			{
				// issue #3245 correct any possible errors/misformatting in the forum_sef
				$new_data['forum_sef'] = eHelper::title2sef($new_data['forum_sef']);
			}

			return $new_data;
		}

		public function afterCreate($new_data, $old_data, $id)
		{
			// do something
			e107::getCache()->clear_sys('forum_perms');
		}

		public function onCreateError($new_data, $old_data)
		{
			// do something
		}


		// ------- Customize Update --------

		public function beforeUpdate($new_data, $old_data, $id)
		{
			if (!empty($new_data['forum_name']))
			{
				// make sure the forum_name contains only plain text, no bbcode or html
				$new_data['forum_name'] = trim(e107::getParser()->toText($new_data['forum_name']));
			}
			if(isset($new_data['forum_sef']) && empty($new_data['forum_sef']) && !empty($new_data['forum_name']))
			{
				$new_data['forum_sef'] = eHelper::title2sef($new_data['forum_name']);
			}
			elseif(!empty($new_data['forum_sef']))
			{
				// issue #3245 correct any possible errors/misformatting in the forum_sef
				$new_data['forum_sef'] = eHelper::title2sef($new_data['forum_sef']);
			}

			return $new_data;
		}

		public function afterUpdate($new_data, $old_data, $id)
		{
			// do something
			e107::getCache()->clear_sys('forum_perms',true);
		}


		public function afterDelete($deleted_data, $id, $deleted_check)
		{
			e107::getCache()->clear_sys('forum_perms');
		}

		public function onUpdateError($new_data, $old_data, $id)
		{
			// do something
		}


		// optional - a custom page.
		public function prunePage()
		{
			$ns = e107::getRender();
			$sql = e107::getDB();
			$frm = e107::getForm();

			//		$sql->select("forum", "forum_id, forum_name", "forum_parent!=0 ORDER BY forum_order ASC");
				$qry = "
			SELECT f.forum_id, f.forum_name, sp.forum_name AS sub_parent, fp.forum_name AS forum_parent
			FROM #forum AS f
			LEFT JOIN #forum AS sp ON sp.forum_id = f.forum_sub
			LEFT JOIN #forum AS fp ON fp.forum_id = f.forum_parent
			WHERE f.forum_parent != 0
			ORDER BY f.forum_parent ASC, f.forum_sub, f.forum_order ASC
			";

			$forums = $sql->retrieve($qry, true);
			// 	$forums = $sql->db_getList();

			e107::getMessage()->addWarning(FORLAN_60);

				$text = "
			<form method='post' action='".e_SELF."?mode=main&action=prune'>\n
			<table class='table adminlist'>
				<colgroup>
					<col class='col-label' />
					<col class='col-control' />

				</colgroup>

			<tr>
				<td>".FORLAN_87."</td>
				<td>".$frm->number('prune_days',0,3,array('min'=>1))."</td>
			</tr>

			<tr>
				<td>".LAN_TYPE."".$frm->help(FORLAN_2)."</td>
				<td>
					".$frm->radio('prune_type','delete',false, array('label'=>FORLAN_89)).
					$frm->radio('prune_type','make_inactive',true, array('label'=>FORLAN_90))."
				</td>
			</tr>

			<tr>
			<td>".FORLAN_138."</td>
			<td>";

				foreach($forums as $forum)
				{
					$for_name = $forum['forum_parent']." &gg; ";
					$for_name .= ($forum['sub_parent'] ? $forum['sub_parent']." &gg; " : "");
					$for_name .= $forum['forum_name'];
					$text .= $frm->checkbox('pruneForum[]',$forum['forum_id'], false, $for_name); //  "<input type='checkbox' name='pruneForum[]' value='".$forum['forum_id']."' /> ".$for_name."<br />";
				}


				$text .= "
			</table>
			<div class='buttons-bar center'>
				".$frm->admin_button('do_prune', LAN_PRUNE, 'submit')."
			</div>
			</form>";

			return $text;
		}


		function saveRules()
		{
			$tp = e107::getParser();
			$sql = e107::getDb();

			$guestrules 	= $tp->toDB($_POST['guestrules']);
			$memberrules 	= $tp->toDB($_POST['memberrules']);
			$adminrules 	= $tp->toDB($_POST['adminrules']);

			if(!$sql->update("generic", "gen_chardata ='$guestrules', gen_intdata='".$_POST['guest_active']."' WHERE gen_type='forum_rules_guest' "))
			{
				$sql->insert("generic", "0, 'forum_rules_guest', '".time()."', 0, '', '".$_POST['guest_active']."', '$guestrules' ");
			}
			if(!$sql->update("generic", "gen_chardata ='$memberrules', gen_intdata='".$_POST['member_active']."' WHERE gen_type='forum_rules_member' "))
			{
				$sql->insert("generic", "0, 'forum_rules_member', '".time()."', 0, '', '".$_POST['member_active']."', '$memberrules' ");
			}
			if(!$sql->update("generic", "gen_chardata ='$adminrules', gen_intdata='".$_POST['admin_active']."' WHERE gen_type='forum_rules_admin' "))
			{
				$sql->insert("generic", "0, 'forum_rules_admin', '".time()."', 0, '', '".$_POST['admin_active']."', '$adminrules' ");
			}

			e107::getMessage()->addSuccess(LAN_SAVED);
			return null;
		}

		function rulesPage()
		{

			$pref 	= e107::getPref();
			$ns 	= e107::getRender();
			$sql 	= e107::getDB();
			$tp 	= e107::getParser();
			$frm 	= e107::getForm();

			/*
			$sql->select("wmessage");
			list($null) = $sql->fetch();
			list($null) = $sql->fetch();
			list($null) = $sql->fetch();
			list($id, $guestrules, $wm_active4) = $sql->fetch();
			list($id, $memberrules, $wm_active5) = $sql->fetch();
			list($id, $adminrules, $wm_active6) = $sql->fetch();
			*/

			if($sql->select('generic','*',"gen_type='forum_rules_guest'"))
			{
				$guest_rules = $sql->fetch();
			}
			if($sql->select('generic','*',"gen_type='forum_rules_member'"))
			{
				$member_rules = $sql->fetch();
			}
			if($sql->select('generic','*',"gen_type='forum_rules_admin'"))
			{
				$admin_rules = $sql->fetch();
			}

			$guesttext 	= $tp->toForm(vartrue($guest_rules['gen_chardata']));
			$membertext = $tp->toForm(vartrue($member_rules['gen_chardata']));
			$admintext 	= $tp->toForm(vartrue($admin_rules['gen_chardata']));

			$text = "
			<form method='post' action='".e_SELF."?mode=main&action=rules'  id='wmform'>
			<table class='table adminform'>
			<colgroup span='2'>
    			<col class='col-label' />
    			<col class='col-control' />
    		</colgroup>
			<tr>
				<td>".WMGLAN_1.": <br />
				".WMGLAN_6.":";
			if (vartrue($guest_rules['gen_intdata']))
			{
				$text .= "<input type='checkbox' name='guest_active' value='1'  checked='checked' />";
			}
			else
			{
				$text .= "<input type='checkbox' name='guest_active' value='1' />";
			}
			$text .= "</td>

				<td>
					".$frm->bbarea('guestrules', $guesttext)."
				</td>
			</tr>

			<tr>
				<td>".WMGLAN_2.": <br />
				".WMGLAN_6.":";
			if (vartrue($member_rules['gen_intdata']))
			{
				$text .= "<input type='checkbox' name='member_active' value='1'  checked='checked' />";
			}
			else
			{
				$text .= "<input type='checkbox' name='member_active' value='1' />";
			}
			$text .= "</td>

				<td>
					".$frm->bbarea('memberrules', $membertext)."
				</td>
			</tr>

			<tr>
				<td>".WMGLAN_3.": <br />
				".WMGLAN_6.": ";

			if (vartrue($admin_rules['gen_intdata']))
			{
				$text .= "<input type='checkbox' name='admin_active' value='1'  checked='checked' />";
			}
			else
			{
				$text .= "<input type='checkbox' name='admin_active' value='1' />";
			}

			$text .= "</td>
				<td>
					".$frm->bbarea('adminrules', $admintext)."
				</td>
			</tr>
			</table>
			<div class='buttons-bar center'>
				".$frm->admin_button('frsubmit', LAN_UPDATE, 'submit')."
			</div>
			</form>";

			return $text;

			$ns->tablerender(WMGLAN_5, $text);

/*
			echo "
			<script type=\"text/javascript\">
			function addtext1(sc){
				document.getElementById('wmform').guestrules.value += sc;
			}
			function addtext2(sc){
				document.getElementById('wmform').memberrules.value += sc;
			}
			function addtext3(sc){
				document.getElementById('wmform').adminrules.value += sc;
			}
			function help1(help){
				document.getElementById('wmform').helpguest.value = help;
			}
			function help2(help){
				document.getElementById('wmform').helpmember.value = help;
			}
			function help3(help){
				document.getElementById('wmform').helpadmin.value = help;
			}
			</script>
			";*/
		}

		function modsPage()
		{


			$ns = e107::getRender();
			$sql = e107::getDB();
			//$e107 = e107::getInstance();

			$forumList      = $this->forumObj->forum_getforums('all');
			$parentList     = $this->forumObj->forum_getparents('list');
			$subList        = $this->forumObj->forumGetSubs('bysub');

			$frm = e107::getForm();
			$tp = e107::getParser();

			$txt = "<form method='post' action='".e_SELF."?".e_QUERY."'>
			<table class='table adminlist'>
			<colgroup span='2'>
	    		<col class='col-label' />
	    		<col class='col-control' />
	    	</colgroup>";

			foreach($parentList as $p)
			{
				$txt .= "
			<tr>
				<td colspan='2' ><strong>".$tp->toHTML($p['forum_name'])."</strong></td>
			</tr>
			";

				foreach($forumList[$p['forum_id']] as $f)
				{
					$txt .= "
				<tr>
					<td>{$f['forum_name']}</td>
					<td>".e107::getUserClass()->uc_dropdown("mods[{$f['forum_id']}]", $f['forum_moderators'], 'admin,classes')."</td>
				</tr>
				";
					foreach($subList[$f['forum_id']] as $s)
					{
						$txt .= "
					<tr>
						<td>&nbsp;&nbsp;&nbsp;&nbsp;{$s['forum_name']}</td>
						<td>".e107::getUserClass()->uc_dropdown("mods[{$s['forum_id']}]", $s['forum_moderators'], 'admin,classes')."</td>
					</tr>
					";
					}
				}
			}
			$txt .= "
			</table>
			<div class='buttons-bar center'>
				".$frm->admin_button('setMods', LAN_UPDATE, 'update')."
			</div>
			</form>";

			return $txt;
		}

		function toolsPageProcess()
		{
			$mes = e107::getMessage();

			$fList = array();

			if(isset($_POST['tools']))
			{
				$msg = '';
				if(isset($_POST['forum_all']))
				{
					$fList[]='all';
				}
				else
				{
					foreach(array_keys($_POST['forumlist']) as $k)
					{
						$fList[] = $k;
					}
				}
				foreach($fList as $fid)
				{
					if(isset($_POST['counts']))
					{
						$this->forumObj->forumUpdateCounts($fid, $_POST['counts_threads']);
						$msg .= FORLAN_167.": $fid <br />";
					}
					if(isset($_POST['lastpost']))
					{
						$with_threads = (!empty($_POST['lastpost_nothread'])) ? FALSE : TRUE;
						$this->forumObj->forumUpdateLastpost('forum', $fid, $with_threads);
						$msg .= FORLAN_168.": $fid <br />";
					}
				}
				if(isset($_POST['userpostcounts']))
				{
					$ue = e107::getUserExt();

					$list = $this->forumObj->getUserCounts();
					foreach($list as $uid => $cnt)
					{
						$ue->user_extended_setvalue($uid, 'user_plugin_forum_posts', $cnt, 'int');
					}
					$msg .= FORLAN_169.' <br />';
				}

				$mes->addSuccess($msg);
			//	$ns->tablerender($caption, $mes->render().$text);
			}
		}

		//TODO Add SEF-url generation for forum and threads where missing. 
		function toolsPage()
		{
			$sql = e107::getDb();
			$ns = e107::getRender();
			$tp = e107::getParser();
			$frm = e107::getForm();

			$this->toolsPageProcess();

			$txt = "
			<form method='post' action='".e_SELF."?".e_QUERY."'>
			<table class='table adminlist'>
			<colgroup span='3'>
	    		<col class='col-label' />
	    		<col class='col-control' />
	    		<col class='col-control' style='width:50%' />
	    	</colgroup>
			<tr>
				<td>".FORLAN_156."</td>

				<td colspan='2'>
				";
				if($sql->select("forum", "*", "1 ORDER BY forum_order"))
				{
					$fList = $sql->db_getList();
					foreach($fList as $f)
					{
						$key = 'forumlist['.$f['forum_id'].']';
						$txt .= $frm->checkbox($key, 1, false, $tp->toHTML($f['forum_name']));
					//	$txt .= "<input type='checkbox' name='forumlist[{$f['forum_id']}]' value='1' /> ".$tp->toHTML($f['forum_name'])."<br />";
					}
					$txt .= "<hr />";
					$txt .= $frm->checkbox('forum_all', 1, false, LAN_PLUGIN_FORUM_ALLFORUMS);
				//	$txt .= "<input type='checkbox' name='forum_all' value='1' /> <strong>".LAN_PLUGIN_FORUM_ALLFORUMS."</strong>";
				}


				//TODO LAN - see below
				$txt .= "
				</td>
			</tr>
			<tr>
				<td>".FORLAN_158."</td>

				<td>".$frm->checkbox('lastpost', 1, false, LAN_ACTIVE).
					"</td><td>".
				$frm->select('lastpost_nothread', array(0=>FORLAN_207, 1=>FORLAN_208))."

				</td>
			</tr>
			<tr>
				<td>".FORLAN_161."</td>

			<td>".$frm->checkbox('counts', 1, false, LAN_ACTIVE).
			"</td><td>".
			$frm->checkbox('counts_threads', 1, false, FORLAN_182)."
			<span class='text-warning'>".FORLAN_183."</span>

				</td>
			</tr>
			<tr>
				<td>".FORLAN_163."</td>

				<td colspan='2'>".$frm->checkbox('userpostcounts',1, false, LAN_ACTIVE)."</td>
			</tr>
			</table>
			<div class='buttons-bar center'>
				".$frm->admin_button('tools', LAN_GO, 'submit')."
			</div>
			</form>
			";

			return $txt;
			//	$ns->tablerender(FORLAN_166, $txt);
		}
	}

	class forum_form_ui extends e_admin_form_ui
	{
		function forum_name($curVal,$mode,$parm)
		{
			$frm = e107::getForm();

			if($mode == 'read')
			{
				return $curVal;

				$parent 	= $this->getController()->getListModel()->get('forum_parent');
				$id			= $this->getController()->getListModel()->get('forum_id');
				$sub     = $this->getController()->getListModel()->get('forum_sub');

				$level = 1;

				if(!empty($sub))
				{
					$level = 3;
				}

				$linkQ = e_SELF."?searchquery=&filter_options=page_chapter__".$id."&mode=page&action=list";
			//	$level_image = $parent ? '<img src="'.e_IMAGE_ABS.'generic/branchbottom.gif" class="icon" alt="" style="margin-left: '.($level * 20).'px" />&nbsp;' : '';

				$level_image = $parent ? str_replace('level-x','level-'.$level, ADMIN_CHILD_ICON) : '';

				return ($parent) ?  $level_image.$curVal : $curVal;
			}

			if($mode == 'write')
			{
				return $frm->text('forum_name',$curVal,255,'size=xxlarge');
			}

			if($mode == 'filter')
			{
				return;
			}
			if($mode == 'batch')
			{
				return;
			}

			if($mode == 'inline')
			{
				$ret = array('inlineType'=>'text');

				$parent 	= $this->getController()->getListModel()->get('forum_parent');
				$sub     = $this->getController()->getListModel()->get('forum_sub');

				if(!empty($parent))
				{

					$level = 1;

					if(!empty($sub))
					{
						$level = 3;
					}

				//	$ret['inlineParms'] = array('pre'=>'<img src="'.e_IMAGE_ABS.'generic/branchbottom.gif" class="level-'.$level.' icon" alt="" />');

					$ret['inlineParms'] = array('pre'=> str_replace('level-x','level-'.$level, ADMIN_CHILD_ICON));

				}


				return $ret;
			}
		}


		// Custom Method/Function
		function forum_parent($curVal,$mode)
		{
			$frm = e107::getForm();

			switch($mode)
			{
				case 'read': // List Page
					return $curVal;
					break;

				case 'write': // Edit Page
					return $frm->text('forum_parent',$curVal);
					break;

				case 'filter':
				case 'batch':
				//	return  $array;
					break;
			}
		}


		// Custom Method/Function
		function forum_sub($curVal,$mode)
		{
			$frm = e107::getForm();

			switch($mode)
			{
				case 'read': // List Page
					return $curVal;
					break;

				case 'write': // Edit Page
					return $frm->text('forum_sub',$curVal);
					break;

				case 'filter':
				case 'batch':
				//	return  $array;
					break;
			}
		}

		// Custom Method/Function
		function forum_lastpost_info($curVal,$mode)
		{
			$frm = e107::getForm();

			switch($mode)
			{
				case 'read': // List Page
					return $curVal;
					break;

				case 'write': // Edit Page
					return $frm->text('forum_lastpost_info',$curVal);
					break;

				case 'filter':
				case 'batch':
				//	return  $array;
					break;
			}
		}

		// Custom Method/Function
		function forum_options($curVal,$mode)
		{
			$frm = e107::getForm();

			switch($mode)
			{
				case 'read': // List Page
					return $curVal;
					break;

				case 'write': // Edit Page
					return $frm->text('forum_options',$curVal);
					break;

				case 'filter':
				case 'batch':
			//		return  $array;
					break;
			}
		}

	}


	class reported_ui extends e_admin_ui
	{

		protected $pluginTitle		=  LAN_PLUGIN_FORUM_NAME;
		protected $pluginName		= 'forum';
		protected $table			= 'generic';
		protected $pid				= 'gen_id';
		protected $perPage 			= 10;
		protected $listQry			= "SELECT g.*,u.user_name FROM `#generic` AS g LEFT JOIN `#user` AS u ON g.gen_user_id = u.user_id WHERE g.gen_type='reported_post' OR g.gen_type='Reported Forum Post' ORDER BY g.gen_datestamp DESC";

		protected $fields 		= array (
			'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
            'gen_id' 				=> array ( 'title' => LAN_ID,	 'nolist'=>true,	'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			//	  'gen_type' 			=> array ( 'title' => LAN_BAN, 	'type' => 'method', 'data' => 'str', 'width' => 'auto', 'batch' => true, 'filter' => true, 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
            'gen_datestamp' 		=> array ( 'title' => LAN_DATESTAMP, 'type' => 'datestamp', 'data' => 'int', 'width' => '10%', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'gen_ip' 				=> array ( 'title' => FORLAN_209, 'type' => 'text', 'data' => 'str', 'width' => '20%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'gen_chardata' 		=> array ( 'title' => FORLAN_210, 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'gen_user_id' 		=> array ( 'title' => FORLAN_211, 'type' => 'user', 'batch'=>true, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left nowrap',  ),
	      	'gen_intdata' 		=> array ( 'title' =>  FORLAN_209, 'type' => 'number', 'nolist'=>true, 'batch'=>false, 'data' => 'int', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'center', 'thclass' => 'center',  ),
		     'options'				=> array ( 'title' => LAN_OPTIONS, 'type' => 'method', 'data' => null, 'width' => '10%', 'thclass' => 'center last', 'class' => 'center last', 'forced' => '1', 'readParms'=>'edit=0'  ),
		);

		protected $fieldpref = array('gen_datestamp', 'gen_ip', 'gen_user_id', 'gen_chardata');

		// optional
		public function init()
		{

		}

		public function afterDelete($deleted_data, $id, $deleted_check)
		{
			//	$sql2->db_Delete('banlist', "banlist_ip='{$banIP}'");
		}

		public function renderHelp()
		{
			$help_text = str_replace("[br]", "<br />", FORLAN_189);
			$help_text = str_replace(array("[", "]"), array("<a href='".e_ADMIN_ABS."notify.php'>"), $help_text); 
			
			return array('caption' => LAN_HELP, 'text' => $help_text);
		}
	}


	class reported_form_ui extends e_admin_form_ui
	{
		// Custom Method/Function
		function gen_intdata($curVal,$mode)
		{
			$frm = e107::getForm();

			switch($mode)
			{
				case 'read': // List Page

					return "<a class='e-modal btn btn-primary'  data-modal-caption='Topic#: ".$curVal."'  href='".e_SELF."?mode=post&action=list&id=". $curVal."' rel='external'>#". $curVal."</a>";
					return $curVal;
					break;

				case 'write': // Edit Page
					return $frm->text('gen_type',$curVal);
					break;

				case 'filter':
				case 'batch':
					return  array(1=>LAN_BAN);
					break;
			}
		}

		// Custom Method/Function
		function gen_chardata($curVal,$mode)
		{
			$frm = e107::getForm();

			switch($mode)
			{
				case 'read': // List Page
					return str_replace(":::","<br />",$curVal);
					break;

				case 'write': // Edit Page
					return $frm->text('gen_chardata',$curVal);
					break;

				case 'filter':
				case 'batch':
					//	return  $array;
					break;
			}
		}

		function options($att, $value, $id, $attributes)
		{

			if($attributes['mode'] == 'read')
			{

				$topic = $this->getController()->getListModel()->get('gen_ip');
				$topidId = $this->getController()->getListModel()->get('gen_intdata');
				$text = "<div class='btn-group'>";
				$text .= "<a class='e-modal btn btn-default'  data-modal-caption='Topic: ".$topic."'  href='".e_SELF."?mode=post&action=list&id=". $topidId."' rel='external'>".ADMIN_VIEW_ICON."</a>";

				$text .= $this->renderValue('options',$value,array('readParms'=>'edit=0'), $id);
				$text .= "</div>";
				return $text;
			}
		}
	}

	class post_ui extends e_admin_ui
	{

		protected $pluginTitle		= LAN_PLUGIN_FORUM_NAME;
		protected $pluginName		= 'forum';
		protected $table			= 'forum_post';
		protected $pid				= 'post_id';
		protected $perPage 			= 10;
	//	protected $listQry			= "SELECT g.*,u.user_name FROM `#generic` AS g LEFT JOIN `#user` AS u ON g.gen_user_id = u.user_id WHERE g.gen_type='reported_post' OR g.gen_type='Reported Forum Post' ORDER BY g.gen_datestamp DESC";

		protected $listOrder		= 'post_datestamp';

		protected $fields 		= array (
			'checkboxes' =>   array ( 'title' => '', 'type' => null, 'data' => null, 'width' => '5%', 'thclass' => 'center', 'forced' => '1', 'class' => 'center', 'toggle' => 'e-multiselect',  ),
			'post_id' 				=> array ( 'title' => LAN_ID,	 'nolist'=>true,	'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			//	  'gen_type' 			=> array ( 'title' => LAN_BAN, 	'type' => 'method', 'data' => 'str', 'width' => 'auto', 'batch' => true, 'filter' => true, 'inline' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'post_datestamp' 		=> array ( 'title' => LAN_DATESTAMP, 'type' => 'datestamp', 'data' => 'int', 'width' => '10%', 'filter' => true, 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'post_entry' 				=> array ( 'title' => FORLAN_213, 'type' => 'bbarea', 'data' => 'str', 'width' => '20%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
		//	'gen_chardata' 		=> array ( 'title' => "Issue", 'type' => 'method', 'data' => 'str', 'width' => 'auto', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'post_user' 		=> array ( 'title' => LAN_USER, 'type' => 'user', 'batch'=>false, 'data' => 'int', 'width' => '5%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left nowrap',  ),
			'post_attachments' 		=> array ( 'title' =>  FORLAN_212, 'type' => 'method', 'batch'=>false, 'data' => 'int', 'width' => '10%', 'help' => '', 'readParms' => '', 'writeParms' => '', 'class' => 'left', 'thclass' => 'left',  ),
			'options'				=> array ( 'title' => LAN_OPTIONS, 'type' => null, 'data' => null, 'width' => '2%', 'thclass' => 'right last', 'class' => 'right last', 'forced' => '1', 'readParms'=>'edit=0'  ),
		);

		protected $fieldpref = array('post_datestamp', 'post_entry', 'post_user', 'post_attachments');


		// optional
		public function init()
		{

			$id = (int) varset($_GET['id']);

			if(!empty($id))
			{
				define('e_IFRAME', true);
				$this->listQry = "SELECT p.*,u.user_name FROM `#forum_post` AS p LEFT JOIN `#user` AS u ON p.post_user = u.user_id WHERE p.post_thread = ".intval($id);
			}
			else
			{
				$this->listOrder		= 'post_datestamp DESC';
				$this->listQry = "SELECT p.*,u.user_name FROM `#forum_post` AS p LEFT JOIN `#user` AS u ON p.post_user = u.user_id ";
			}
		}

		public function afterDelete($deleted_data, $id, $deleted_check)
		{
			//	$sql2->db_Delete('banlist', "banlist_ip='{$banIP}'");
		}
	}


	class post_form_ui extends e_admin_form_ui
	{
		function post_attachments($curVal,$mode)
		{


			switch($mode)
			{
				case 'read': // List Page
					$data = e107::unserialize($curVal);
					$tp = e107::getParser();
					$fl = e107::getFile();

					$text = '';

					if(!empty($data['img']))
					{
						$text .= "<ul class='list-unstyled'>";
						foreach($data['img'] as $v)
						{
							$text .= "<li><span class='label label-primary'>".$tp->toGlyph('fa-file-image-o').$v['name']."</span> <small>".$fl->file_size_encode($v['size'])."</small></li>";
						}

						$text .= "</ul>";
					}

					if(!empty($data['file']))
					{
						$text .= "<ul class='list-unstyled'>";
						foreach($data['file'] as $v)
						{
							$text .= "<li><span class='label label-primary'>".$tp->toGlyph('fa-file-text-o').$v['name']."</span> <small>".$fl->file_size_encode($v['size'])."</small></li>";
						}

						$text .= "</ul>";
					}

					return $text;
					break;

				case 'write': // Edit Page
					$data = e107::unserialize($curVal);
					return print_a($data,true);
					break;

				case 'filter':
				case 'batch':
				//	return  $array;
					break;
			}
		}
	}

new forum_admin();

require_once(e_ADMIN."auth.php");
e107::getAdminUI()->runPage();
require_once(e_ADMIN."footer.php");



