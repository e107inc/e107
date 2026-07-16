<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id$
 *
 * Image picker shortcode
 *
*/

function iconpicker_shortcode($parm)
{
		if(empty($parm))
		{
			return null;
		}

		$parms = array();

		parse_str($parm, $parms);
		$name = varset($parms['id']);

	
		$sql = e107::getDb();
		$frm = e107::getForm();
		$tp = e107::getParser();
		
	
		// $sc_parameters is currently being used to select the media-category.

		$qb = $sql->createQueryBuilder();
		$qb->select('*')->from('core_media')
			->whereIn('media_userclass', explode(',', USERCLASS_LIST));
		if(vartrue($sc_parameters))
		{
			$qb->where('media_category', $sc_parameters);
		}
		else
		{
			$qb->where($qb->expr()->regexp('media_category', '_icon_16|_icon_32|_icon_48|_icon_64'));
		}
		$qb->orderBy('media_category')->addOrderBy('media_name');

		$str = "";
		$size_section = array();
		$lastsize = "16";

		$iconRows = $qb->fetchAll();
		if($iconRows)
		{
			foreach($iconRows as $row)
			{
				list($tmp,$tmp2,$size) = explode("_",$row['media_category']);
				
								
				if($str !='' && ($size != $lastsize))
				{
					$size_section[] = $str;
					$str = "";						
				}
				
				$str .= "<a href='#".$row['media_url']."'  onclick=\"e107Helper.insertText('{$row['media_url']}','{$name}','{$name}-iconpicker'); return false; \"><img class='icon picker list%%size%%' src='".$tp->replaceConstants($row['media_url'],'abs')."' alt='{$row['media_name']}' /></a>";
								
				$lastsize = $size;
			
			}

			return '<div id="'.$name.'-iconpicker-ajax"><div class="field-spacer iconpicker">'.str_replace('%%size%%', '', implode('</div><div class="field-spacer iconpicker">', $size_section)).'</div></div>';
		}
	
	
}
