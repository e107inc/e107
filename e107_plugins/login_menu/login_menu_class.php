<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/login_menu/login_menu_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/**
 *	e107 Login menu plugin
 *
 *	Handles the login menu options
 *
 *	@package	e107_plugins
 *	@subpackage	login
 *	@version 	$Id$;
 *
 *	@todo	delete references to forum and chatbox plugins
 */


if (!defined('e107_INIT')) { exit; }

/*
e_loginbox.php example:

//Example link 1
$LBOX_LINK = array();
$LBOX_LINK['link_label']  = 'My link 1';
$LBOX_LINK['link_url']    = e_PLUGIN_ABS.'myplug/me.php?1';
$lbox_links[] = $LBOX_LINK;

//Example stats
$LBOX_STAT = array();
$LBOX_STAT['stat_item']  = 'my item';
$LBOX_STAT['stat_items']  = 'my items';
$LBOX_STAT['stat_new']    = '1';
$LBOX_STAT['stat_nonew']    = 'no my items';//or empty to omit
$lbox_stats[] = $LBOX_STAT;
*/

class login_menu_class
{
	protected $e107;
	protected $loginPrefs;		// Array of our menu prefs

	public function __construct()
	{
		$this->e107 = e107::getInstance();
		$this->loginPrefs = e107::getConfig('menu')->getPref('login_menu');
	}


    function get_coreplugs($active=true) 
	{
        $list = array('forum', 'chatbox_menu');
        $ret = array();
        
        foreach ($list as $value) 
		{
            if(!$active || e107::isInstalled($value))
                $ret[] = $value;
        }

        return $ret;
    }


    function get_external_list($sort = true) 
	{
        require_once(e_HANDLER.'file_class.php');
		$fl = new e_file;
		$list = array();
		
		$list_arr = $fl->get_files(e_PLUGIN, "e_loginbox\.php$", "standard", 1);
		
		if($list_arr) 
		{
            foreach ($list_arr as $item) 
			{
                $tmp = end(explode('/', trim($item['path'], '/.')));
                
                if(e107::isInstalled($tmp)) 
				{
                    $list[] = $tmp;
                }
            }
        }

        if($sort && $this->loginPrefs['external_links']) 
		{
            $tmp = array_flip(explode(',', $this->loginPrefs['external_links']));
            
            $cnt = count($tmp);
            foreach ($list as $value) {
            	$list_ord[$value] = varset($tmp[$value], $cnt++);
            }
            
            asort($list_ord);
            $list = array_keys($list_ord);
            unset($list_ord);
        }
        
		return $list;
    }


    function parse_external_list($active=false, $order=true) 
	{
        //prevent more than 1 call
        if(($tmp = getcachedvars('loginbox_elist')) !== FALSE) return $tmp;
        
        $ret = array();
        //$lbox_admin = varsettrue($eplug_admin, false);
        $coreplugs = $this->get_coreplugs(); 
        
        $lprefs = vartrue($this->loginPrefs['external_links']) ? explode(',', $this->loginPrefs['external_links']) : array();
        $sprefs = vartrue($this->loginPrefs['external_stats']) ? explode(',', $this->loginPrefs['external_stats']) : array();
        
        if($active) 
		{
            $tmp =  array_flip($lprefs);
            $tmp1 = array_flip($sprefs);
            $list = array_keys(array_merge($tmp, $tmp1));
        } 
		else 
		{
            $list = array_merge($coreplugs, $this->get_external_list($order));
        } 
        
        foreach ($list as $item) 
		{
            //core
            if(in_array($item, $coreplugs)) 
			{
//                if($tmp = call_user_func(array('login_menu_class', "get_{$item}_stats"), $get_stats))
                if($tmp = call_user_func(array('login_menu_class', "get_{$item}_stats")))		// $get_stats appears to be no longer used
                    $ret['stats'][$item] = $tmp;  
                       
                continue;
            }
        	    
            $lbox_links = array();
            $lbox_stats = array();
            $lbox_links_active = (!$active || in_array($item, $lprefs));
            $lbox_stats_active = (!$active || in_array($item, $sprefs));

        	if(file_exists(e_PLUGIN.$item."/e_loginbox.php")) { 

                
                include(e_PLUGIN.$item."/e_loginbox.php");
                
                if(!empty($lbox_links) && $lbox_links_active) $ret['links'][$item] = $lbox_links;
                if(!empty($lbox_stats) && $lbox_stats_active) $ret['stats'][$item] = $lbox_stats;
                
            }
        }
        cachevars('loginbox_elist', $ret);
        
        return $ret;
    }


    function get_forum_stats($get_stats=true) 
	{
		$sql = e107::getDb();
        
        if(!e107::isInstalled('forum'))
            return array();
        
        $lbox_stats = array();
        $lbox_stats[0]['stat_item']    = LAN_LOGINMENU_20;
        $lbox_stats[0]['stat_items']   = LAN_LOGINMENU_21;
        $lbox_stats[0]['stat_new']     = 0;
        $lbox_stats[0]['stat_nonew']   = LAN_LOGINMENU_26.' '.LAN_LOGINMENU_21;
        if($get_stats) {

            $nobody_regexp = "'(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)'";
        	$qry = "
        	SELECT  count(*) as count FROM #forum_thread  as t
        	LEFT JOIN #forum as f
        	ON t.thread_forum_id = f.forum_id
        	WHERE t.thread_datestamp > ".USERLV." and f.forum_class IN (".USERCLASS_LIST.") AND NOT (f.forum_class REGEXP ".$nobody_regexp.")
        	";
        	
        	if($sql->db_Select_gen($qry)) 
			{
        		$row = $sql->db_Fetch();
        		$lbox_stats['forum'][0]['stat_new'] = $row['count'];
        	}
        }
    	
    	return $lbox_stats;
    }


    function get_chatbox_menu_stats() 
	{
		$sql = e107::getDb();
        
        if(!e107::isInstalled('chatbox_menu'))
            return array();
        
        $lbox_stats[0]['stat_item']     = LAN_LOGINMENU_16;
        $lbox_stats[0]['stat_items']    = LAN_LOGINMENU_17;
        $lbox_stats[0]['stat_new']      = 0;
        $lbox_stats[0]['stat_nonew']    = LAN_LOGINMENU_26.' '.LAN_LOGINMENU_17;
        if(vartrue($get_stats)) {
            $lbox_stats['chatbox_menu'][0]['stat_new']  = $sql->db_Count('chatbox', '(*)', 'WHERE `cb_datestamp` > '.USERLV);
        }
        
        return $lbox_stats;
    }
    

    function render_config_links() 
	{
        $ret = '';

        $lbox_infos = $this->parse_external_list(false);
        if(!vartrue($lbox_infos['links'])) return '';
        
        $enabled = vartrue($this->loginPrefs['external_links']) ? explode(',', $this->loginPrefs['external_links']) : array();
        
        $num = 1;
        foreach ($lbox_infos['links'] as $id => $stack) {
            $links = array();
            foreach ($stack as $value) {
            	$links[] = '<a href="'.$value['link_url'].'">'.vartrue($value['link_label'], '['.LAN_LOGINMENU_44.']').'</a>';
            }
            
            $plug_data = $this->get_plugin_data($id);
            
            $links = implode(', ', $links);
            
        	$ret .= '
            	<tr>
            	<td class="forumheader3">'.LAN_LOGINMENU_37.' '.(varset($plug_data['eplug_name']) ? LAN_LOGINMENU_45.LAN_LOGINMENU_45a." {$plug_data['eplug_name']} ".LAN_LOGINMENU_45b."<br />" : '').$links.'</td>
            	<td style="text-align: left;" class="forumheader3">

                   <table style="margin-left: 0px">
            	   <tr>
                    <td>
                	   <input type="checkbox" name="external_links['.$id.']" value="1"'.(in_array($id, $enabled) ? ' checked="checked"' : '').' />
                    </td>
                    <td>
                        '.LAN_LOGINMENU_43.': <input type="text" class="tbox" style="text-align: right" size="4" maxlength="3" name="external_links_order['.$id.']" value="'.$num.'" />
                    </td>                   
                   </tr>
                   </table>
                   
                </td>
            	</tr>
            ';
            $num++;
        }
        
        if($ret) 
		{
            $ret = '<tr><td colspan="2" class="fcaption">'.LAN_LOGINMENU_38.'</td></tr>'.$ret;
        }
        
        return $ret;
    }


    function render_config_stats() 
	{
        $ret = '';
        $lbox_infos = $this->parse_external_list(false);
        $lbox_infos = vartrue($lbox_infos['stats'], array());

        if(!$lbox_infos) return '';

        $enabled = vartrue($this->loginPrefs['external_stats']) ? explode(',', $this->loginPrefs['external_stats']) : array();
        
        $num = 1;
        foreach ($lbox_infos as $id => $stack) 
		{

            $plug_data = $this->get_plugin_data($id);

			if (is_array($plug_data) && count($plug_data))
			{
				$ret .= '
					<tr>
					<td class="forumheader3">'.LAN_LOGINMENU_37.' '.LAN_LOGINMENU_46.LAN_LOGINMENU_45a." {$plug_data['eplug_name']} ".LAN_LOGINMENU_45b.'<br /></td>
					<td class="forumheader3">
					   <input type="checkbox" name="external_stats['.$id.']" value="1"'.(in_array($id, $enabled) ? ' checked="checked"' : '').' />
					</td>
					</tr>
				';
				$num++;
			}
        }
        
        if($ret) 
		{
            $ret = '<tr><td colspan="2" class="fcaption">'.LAN_LOGINMENU_47.'</td></tr>'.$ret;
        }
        
        return $ret;
    }


    function get_stats_total() 
	{
        $lbox_infos = $this->parse_external_list(true, false);
        if(!vartrue($lbox_infos['stats']))
		{
			return 0;
		}
            
        $ret = 0;
        $lbox_active_sorted = $this->loginPrefs['external_stats'] ? explode(',', $this->loginPrefs['external_stats']) : array();
        
        foreach ($lbox_active_sorted as $stackid) 
		{ 
            if(!varset($lbox_infos['stats'][$stackid])) 
                continue;
            foreach ($lbox_infos['stats'][$stackid] as $lbox_item) 
			{
                if($lbox_item['stat_new'])
                    $ret += $lbox_item['stat_new'];
            }
        }
        
        return $ret;
    }



	/**
	 *	Get data for a plugin
	 *
	 *	@param string $plugid - name (= base directory) of the required plugin
	 */
    function get_plugin_data($plugid) 
	{
        if(($tmp = getcachedvars('loginbox_eplug_data_'.$plugid)) !== FALSE) return $tmp;

        $ret = array();
		if (is_readable(e_PLUGIN.$plugid.'/plugin.xml'))
		{
			require_once(e_HANDLER.'xml_class.php');
			$xml = new xmlClass;
			$xml->filter = array('name' => FALSE,'version'=>FALSE);			// Just want a couple of variables
			$readFile = $xml->loadXMLfile(e_PLUGIN.$plugid.'/plugin.xml', true, true);
            $ret['eplug_name'] = defined($readFile['name']) ? constant($readFile['name']) : $readFile['name'];
            $ret['eplug_version'] = $readFile['version'];
		}
		elseif (is_readable(e_PLUGIN.$plugid.'/plugin.php')) 
		{
            
            include(e_PLUGIN.$plugid.'/plugin.php');
            $ret['eplug_name'] = defined($eplug_name) ? constant($eplug_name) : $eplug_name;
            $ret['eplug_version'] = $eplug_version;
        }
		else
		{
			return array();
		}
		// Valid data here
		cachevars('loginbox_eplug_data_'.$plugid, $ret);
        return $ret;
    }
    
    
    function clean_links($link_items) 
	{
        if(empty($link_items)) return;
    
        foreach($link_items as $key => $value) 
		{
            if(!vartrue($value['link_url']))
			{
                unset($link_items[$key]);
            }
        }
        
        return $link_items;
    }

}

