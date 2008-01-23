<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/login_menu/login_menu_class.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-01-23 01:12:15 $
|     $Author: secretr $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

/*
e_loginbox.php example:

//Example link 1
$LBOX_LINK = array();
$LBOX_LINK['link_label']  = 'My link 1';
$LBOX_LINK['link_url']    = e_PLUGIN_ABS.'myplug/me.php?1';
//Additional information?
$lbox_links[] = $LBOX_LINK;

//Not implemented yet
$LBOX_STAT = array();
$LBOX_STAT['stat_item']  = 'my item';
$LBOX_STAT['stat_items']  = 'my items';
$LBOX_STAT['stat_new']    = '1';
$LBOX_STAT['stat_nonew']    = 'no my items';//or empty to omit
//Additional information?
$lbox_stats[] = $LBOX_STAT;
*/

class login_menu_class
{
    function get_external_list($sort = true) {
        global $sql, $pref, $menu_pref;
        
        require_once(e_HANDLER."file_class.php");
		$fl = new e_file;
		$list = array();
		
		$list_arr = $fl->get_files(e_PLUGIN, "e_loginbox\.php$", "standard", 1);
		
		if($list_arr) {
            foreach ($list_arr as $item) {
                $tmp = end(explode('/', trim($item['path'], '/.')));
                
                if(array_key_exists($tmp, $pref['plug_installed'])) {
                    $list[] = $tmp;
                }
            }
        }

        if($sort && $menu_pref['login_menu']['external_links']) {
            $tmp = array_flip(explode(',', $menu_pref['login_menu']['external_links']));
            
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
    
    function parse_external_list($list) {
        
        //prevent more than 1 call
        if(($tmp = getcachedvars('loginbox_elist')) !== FALSE) return $tmp;

        $ret = array();
        foreach ($list as $item) { 
        	if(file_exists(e_PLUGIN.$item."/e_loginbox.php")) { 
        	    
                $lbox_links = array();
                $lbox_stats = array();
                require(e_PLUGIN.$item."/e_loginbox.php");
                
                /* Front-end only!
                if($check) {
                    $lbox_links = login_menu_class::clean_links($lbox_links);
                }*/
                
                if(!empty($lbox_links)) $ret['links'][$item] = $lbox_links;
                if(!empty($lbox_stats)) $ret['stats'][$item] = $lbox_stats;
                
            }
        }
        cachevars('loginbox_elist', $ret);
        
        return $ret;
    }
    
    function render_config_links() {
        global $menu_pref;
        
        $ret = '';
        $list = login_menu_class::get_external_list(true);
        $lbox_infos = login_menu_class::parse_external_list($list);
        if(!varsettrue($lbox_infos['links'])) return '';

        $enabled = varsettrue($menu_pref['login_menu']['external_links']) ? explode(',', $menu_pref['login_menu']['external_links']) : array();
        
        $num = 1;
        foreach ($lbox_infos['links'] as $id => $stack) {
            $links = array();
            foreach ($stack as $value) {
            	$links[] = '<a href="'.$value['link_url'].'">'.varsettrue($value['link_label'], '['.LOGIN_MENU_L44.']').'</a>';
            }
            
            $links = implode(', ', $links);
            
        	$ret .= '
            	<tr>
            	<td style="width:30%" class="forumheader3">'.LOGIN_MENU_L37.' '.$links.'</td>
            	<td style="width:70%; text-align: left;" class="forumheader3">

                   <table style="margin-left: 0px">
            	   <tr>
                    <td>
                	   <input type="checkbox" name="external_links['.$id.']" value="1"'.(in_array($id, $enabled) ? ' checked="checked"' : '').' />
                    </td>
                    <td>
                        '.LOGIN_MENU_L43.': <input type="text" class="tbox" style="text-align: right" size="4" maxlength="3" name="external_links_order['.$id.']" value="'.$num.'" />
                    </td>                   
                   </tr>
                   </table>
                   
                </td>
            	</tr>
            ';
            $num++;
        }
        
        if($ret) {
            $ret = '<tr><td colspan="2" class="fcaption">'.LOGIN_MENU_L38.'</td></tr>'.$ret;
        }
        
        return $ret;
    }
    
    
    function clean_links($link_items) {
    
        if(empty($link_items)) return;
    
        foreach($link_items as $key => $value) {
            if(!varsettrue($value['link_url'])) {
                unset($link_items[$key]);
            }
        }
        
        return $link_items;
    }

}

?>