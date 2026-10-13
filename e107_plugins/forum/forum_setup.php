<?php
/*
* e107 website system
*
* Copyright 2008-2010 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Forum plugin version tools
*
* $URL$
* $Id$
*
*/

class forum_setup
{
	function install_pre($var)
	{
		// print_a($var);
		// echo "custom install 'pre' function<br /><br />";
	}

	function install_post($var)
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();

		$this->protectAttachments();
		$this->reportRawEdits();

		/*if($sql -> db_Update("user", "user_forums='0'")) // deprecated in 0.8
		{
			$mes->add("Setting all user_forums to 0.", E_MESSAGE_SUCCESS);
		}*/
	}

	/**
	 * Cover the attachment directory with a deny rule.
	 *
	 * Runs on install and on upgrade, because the sites this matters to are the
	 * ones already holding attachments: a post's attachments are readable
	 * through a route that asks which forum the post is in, and were readable a
	 * second way that asks nothing, by their raw path. Nothing a member does is
	 * needed for the cover to go down.
	 *
	 * @return void
	 */
	private function protectAttachments()
	{
		require_once(e_PLUGIN.'forum/forum_attachments.php');

		if(!forum_attachments::protect())
		{
			e107::getMessage()->addWarning(
				'Could not write the deny rule for '.e_MEDIA.'plugins/forum/attachments/. '
				.'Post attachments there remain fetchable by their direct URL.');
		}
	}

	function uninstall_post($var)
	{
		$sql = e107::getDb();
		$mes = e107::getMessage();

/*		if($sql -> db_Update("user", "user_forums='0'")) // deprecated in 0.8
		{
			$mes->add("Setting all user_forums to 0.", E_MESSAGE_SUCCESS);
		}*/
	}
	
	/*
	 * Call During Upgrade Check. May be used to check for existance of tables etc and if not found return TRUE to call for an upgrade. 
	 * 
	 */
	function upgrade_required()
	{
		if(!e107::getDb()->isTable('forum_thread'))
		{
			return true;
		}


		if(!e107::getDb()->field('forum_thread','thread_id'))
		{
			return true;	 // true to trigger an upgrade alert, and false to not. 	
		}

		if(e107::getDb()->field('forum_thread', 'thread_sef'))
		{
			e107::getDb()->gen("ALTER TABLE `#forum_thread` DROP `thread_sef` ");
		}

		$legacyMenuPref = e107::getConfig('menu')->getPref();
		//if(isset($legacyMenuPref['newforumposts_caption']))
		//{

	//	}

		// Check if e_print addon is loaded
		if(!e107::getAddon('forum','e_print'))
		{
			return true;
		}

		if(!e107::getPlugConfig('forum')->get('edit_body_checked'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Report how many edited posts hold markup that their author or their last editor may not post; nothing stored is changed.
	 *
	 * @return int|null the number of such posts, or null where check_class() grants every class and cannot tell
	 */
	public function reportRawEdits()
	{
		if(check_class(e_UC_NOBODY))
		{
			return null;
		}

		$pairs = e107::getDb()->retrieve('forum_post', 'post_user, post_edit_user, COUNT(*) AS posts', "post_edit_datestamp > 0 AND post_entry LIKE '%<%' GROUP BY post_user, post_edit_user", true);
		$postsHtml = array();
		$count = 0;

		foreach($pairs ?: array() as $pair)
		{
			foreach(array((int) $pair['post_user'], (int) $pair['post_edit_user']) as $uid)
			{
				if(!isset($postsHtml[$uid]))
				{
					$user = e107::getSystemUser($uid, false);
					$postsHtml[$uid] = $user->getId() && $user->checkClass(e107::getPref('post_html', e_UC_MAINADMIN), false);
					e107::setRegistry('core/e107/user/'.$uid, null);
				}
			}

			if(!$postsHtml[(int) $pair['post_user']] || !$postsHtml[(int) $pair['post_edit_user']])
			{
				$count += (int) $pair['posts'];
			}
		}

		if($count > 0)
		{
			e107::getMessage()->addWarning('Edited forum posts that hold markup their author or last editor may not post: '.$count.'. Some may be legitimate HTML, written while their authors could still post it. This update changed none of them; the release notes say how to find and review them.');
		}

		e107::getPlugConfig('forum')->set('edit_body_checked', 1)->save(false, true, false);

		return $count;
	}

	function upgrade_pre($var)
	{

		$sql = e107::getDb();

		if(!$sql->isTable('forum_t') || !$sql->isEmpty('forum_thread')) // no table, so run a default plugin install procedure.
		{
			return false;
		//	e107::getSingleton('e107plugin')->refresh('forum');
		}
		else
		{
			e107::getRedirect()->go(e_PLUGIN_ABS.'forum/forum_update.php?e-token='.defset('e_TOKEN')); //Redirect upgrade to customized upgrade routine
		}
		
		//header('Location: '.e_PLUGIN.'forum/forum_update.php');
	}

	// After Automatic Upgrade Routine has completed.. run this. ;-)
	function upgrade_post($var)
	{
		$sql = e107::getDb();

		$this->protectAttachments();
		$this->reportRawEdits();

		$config = e107::getPref('url_config');

		if(!empty($config['forum']))
		{
			e107::getConfig()
			->removePref('url_config/forum')
			->removePref('url_locations/forum')
			->save(false,true);

			if(file_exists(e_PLUGIN."forum/url/url.php"))
			{
				@unlink(e_PLUGIN."forum/url/url.php");
				@unlink(e_PLUGIN."forum/url/rewrite_url.php");
			}

			$bld = new eRouter;
			$bld->buildGlobalConfig();

		}





		if($sql->isEmpty('forum_thread') === true && $sql->isTable('forum_t') && $sql->isEmpty('forum_t') === false)
		{
			$mes = e107::getMessage();
			$mes->addSuccess("Migration is required. Please click 'Continue'.<br /><a class='btn btn-primary' href='".e_PLUGIN."forum/forum_update.php?e-token=".defset('e_TOKEN')."'>Continue</a>");
		}


		if(!e107::getAddon('forum','e_print'))
		{
			e107::getPlug()->clearCache()->buildAddonPrefLists();	
		}

	}
}
