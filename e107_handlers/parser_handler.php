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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/parser_handler.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:26 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/


function register_parser($plugin_name,$regexp) {
        if($plugin_name != '' OR $regexp != '') {
                if(file_exists(e_PLUGIN."{$plugin_name}/parser.php")) {
                        require_once(e_PLUGIN."{$plugin_name}/parser.php");
                        if(function_exists($plugin_name.'_parse')) {
                                if(IsRegExp($regexp)) {
                                        $oursql = new db;
                                        $oursql -> db_Select("parser", "*", "parser_pluginname='".$plugin_name."' AND parser_regexp='".$regexp."'");
                                        if ($row = $oursql -> db_Fetch()) {
                                                // Already exists, handle error if needed.
                                        } else {
                                                $regexp = str_replace("\\" , "\\\\", $regexp);
                                                $oursql -> db_Insert("parser", "0, '".$plugin_name."', '".$regexp."'");
                                                return 1;
                                        }
                                } else {
                                        // handle error if wanted to - not a valid regexp
                                }
                        } else {
                                // handle error if wanted to - function does not exist
                        }
                } else {
                        // handle error if wanted to - Unable to include file
                }
        } else {
                // handle error if wanted to - plugin name or regexp value empty
        }
}

function IsRegExp($sREGEXP) {
        $sPREVIOUSHANDLER = Set_Error_Handler("TrapError");
        Preg_Match ($sREGEXP, "");
        Restore_Error_Handler ($sPREVIOUSHANDLER);
        Return !TrapError ();
}

function TrapError () {
        Static $iERRORES;
        if(!Func_Num_Args())  {
                $iRETORNO = $iERRORES;
                $iERRORES = 0;
                return $iRETORNO;
        } else {
                $iERRORES++;
        }
}

?>