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
|     $Source: /cvs_backup/e107_0.7/e107_admin/footer.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-09-28 11:37:59 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
@include(e_LANGUAGEDIR.e_LANGUAGE."/admin/lan_footer.php");
@include(e_LANGUAGEDIR."English/admin/lan_footer.php");

if(ADMIN){
	if($pref['cachestatus']){
		if(!$sql -> db_Select("tmp", "*", " tmp_ip='var_store' && tmp_time='1' ")){                // var_store 1 == cache empty time
			$sql -> db_Insert("tmp", "'var_store', 1, '".$e107info['e107_datestamp']."' ");
		}else{
			$row = $sql -> db_Fetch(); extract($row);
			if(($tmp_info+604800) < time()){
				$sql -> db_Delete("cache");
				$sql -> db_Update("tmp", "tmp_info='".time()."' WHERE tmp_ip='var_store' AND tmp_time=1 ");
			}
		}
	}
}

parse_admin($ADMIN_FOOTER);
?>

</body>
</html>

<?php
$sql -> db_Close();
?>
