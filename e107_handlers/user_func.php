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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/user_func.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:27 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

function e107_userGetuserclass($user_id){
        global $cachevar;
        $key = 'userclass_'.$user_id;
        $val = getcachedvars($key);
        if($val)
        {
                return $cachevar[$key];
        }
        else
        {
                $uc_sql = new db;
                if($uc_sql -> db_Select("user","user_class","user_id={$user_id}"))
                {
                        $row = $uc_sql -> db_Fetch();
                        return $row['user_class'];
                }
                else
                {
                        return "";
                }
        }
}
?>