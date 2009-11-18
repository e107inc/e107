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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/handlers/download_class.php,v $
 * $Revision: 1.5 $
 * $Date: 2009-11-18 02:03:35 $
 * $Author: marj_nl_fr $
 */

if (!e107::isInstalled('download')) { exit(); }

class download
{
   var $e107;
   function download()
   {
		$this->e107 = e107::getInstance();
   }
   function displayCategoryList() {
   }
   function getBreadcrumb($arr)
   {
      $dlbreadcrumb = array();
      $ix = 0;
      foreach ($arr as $key=>$crumb) {
         $dlbreadcrumb[$ix]['sep'] = " :: ";
         $ix++;
         if (is_int($key))
         {
            $dlbreadcrumb[$ix]['value'] = $crumb;
         }
         else
         {
            $dlbreadcrumb[$ix]['value'] = "<a href='{$crumb}'>".$key."</a>";
         }
      }
      $dlbreadcrumb['fieldlist'] = implode(",", array_keys($dlbreadcrumb));
      return $dlbreadcrumb;
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
      if (!$sql->db_Select_gen($qry))
      {
        	return "Error reading categories<br />";
        	exit;
      }
      $boxinfo .= "<select name='{$name}' id='download_category' class='tbox'>
      	<option value=''>{$blankText}</option>\n";
      // Its a structured display option - need a 2-step process to create a tree
      $catlist = array();
      while ($dlrow = $sql->db_Fetch(MYSQL_ASSOC))
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
?>