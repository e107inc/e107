<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/includes/tabbed.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

new tabbed;

class tabbed
{
    protected $links = array();
    
    function __construct()
    {
     //   $core = e107::getNav()->adminLinks('assoc');
     //   $plugs = e107::getNav()->pluginLinks(E_16_PLUGMANAGER, "array");
        
     //   $this->links = array_merge($core,$plugs);
    //    $this->links = multiarray_sort($this->links,'title'); //XXX Move this function in e107_class? 
        
        $this->links = e107::getNav()->adminLinks();
        $this->render();         
    }  
    
    
    function render()
    {
        $ns         = e107::getRender();
        $mes        = e107::getMessage();
        $admin_cat  = e107::getNav()->adminCats(); 

        $text = "<div>";
        $text .= "<ul class='nav nav-tabs'>";
        
        foreach ($admin_cat['id'] as $cat_key => $cat_id)
        {
            $cls = ($cat_key == 1) ? "class='active'" : "";
            $text .= "<li {$cls} ><a data-toggle='tab' data-bs-toggle='tab' href='#core-main-".$cat_key."'>".$admin_cat['title'][$cat_key]."</a></li>";
        }
        
        $text .= "</ul>";
    //    print_a($this->links);
           $text .= "<div class='tab-content adminform clearfix'>";
         foreach ($admin_cat['id'] as $id => $cat_id)
         {
             $cls = ($id == 1) ? "active" : "";
            $text .= "<div class='tab-pane {$cls} adminform' style='padding-top:10px' id='core-main-{$id}'>".$this->renderCat($id)."</div>";
         }
        $text .= "</div>";
          $text .= "</div>";
        
        
        
        
        
        
        
        
        
        $ns->tablerender(ADLAN_47." ".ADMINNAME, $mes->render().$text);      
            
        
        
    } 
    
    
    function renderCat($cat)
    {
        $text = "";
        foreach($this->links as $val)
        {
            if($val['cat'] != $cat)
            {
            //    echo "<br />".$funcinfo['cat']." != ".$cat;
               continue;    
            }
            
            $text   .= e107::getNav()->renderAdminButton($val['link'], $val['title'], $val['caption'], $val['perms'], $val['icon_32'], "div");
         }        
 
        return $text;
        
    }   
        
        
    
    function render2()
    {
        $mes = e107::getMessage();
        
        $admin_cat = e107::getNav()->adminCats();
        
        $text = "<div class='center'>
        	   
        			<ul class='nav nav-tabs'>";
        
                    foreach ($admin_cat['id'] as $cat_key => $cat_id)
        			{
        				// $text .= "<li id='tab-main_".$cat_key."' ><span style='white-space:nowrap'><img class='icon S16' src='".$admin_cat['img'][$cat_key]."' alt='' style='margin-right:3px' /><a href='#core-main_".$cat_key."'>".$admin_cat['title'][$cat_key]."</a></span></li>";
        				$text .= "<li id='tab-main_".$cat_key."' ><a data-toggle='tab' data-bs-toggle='tab' href='#core-main_".$cat_key."'>".$admin_cat['title'][$cat_key]."</a></li>";
        			}
        			$text .= "</ul>";
        
        $text .= "<div id='tab-content'>";
        
        print_a($admin_cat);
        
        foreach ($admin_cat['id'] as $cat_key => $cat_id)
        {
        	$text_check = FALSE;
        
        	$text_cat = "";
        
        
        	if ($cat_key != 5) // Note the Plugin category.
        	{
        		foreach ($newarray as $key => $funcinfo)
        		{
        			if ($funcinfo[4] == $cat_key)
        			{
        				$text_rend = e107::getNav()->renderAdminButton($funcinfo[0], $funcinfo[1], $funcinfo[2], $funcinfo[3], $funcinfo[6], 'div');
        				if ($text_rend)
        				{
        					$text_check = TRUE;
        				}
        				$text_cat .= $text_rend;
        			}
        		}
        	}
        	else // Plugin category.
        	{
        		$text_rend  = e107::getNav()->pluginLinks(E_32_PLUGMANAGER, "div");
        
        		if ($text_rend)
        		{
        			$text_check = TRUE;
        		}
        		$text_cat .= $text_rend;
        	}
        	//$text_cat .= render_clean();
        	
        	if ($text_check)
        	{
        	    $text .= "<div class='tab-pane adminform' id='core-main_".$cat_key."'>\n";
                $text .= " <div class='main_caption bevel'><b>".$admin_cat['title'][$cat_key]."</b></div>";
        		$text .= $text_cat ;
        		$text .= "</div><!-- End tab-pane -->";
        	}
            
           
        	
        }

    
    
        $text .= "</div></div>";
    
        $ns->tablerender(ADLAN_47." ".ADMINNAME, $mes->render().$text);
    
    }
}
