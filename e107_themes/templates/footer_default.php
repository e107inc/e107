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
|     $Source: /cvs_backup/e107_0.7/e107_themes/templates/footer_default.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-10-06 02:47:32 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
if(!is_object($sql)){
        // reinstigate db connection if another connection from third-party script closed it ...
        global $sql, $mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb, $CUSTOMFOOTER, $FOOTER;
        $sql = new db;
        $sql -> db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);
}

unset($fh);
if($e107_popup!=1){
        $custompage = explode(" ", $CUSTOMPAGES);
        if($CUSTOMFOOTER){
                while(list($key, $kpage) = each($custompage)){
                        if(strstr(e_SELF, $kpage)){
                                $fh = TRUE;
                                break;
                        }
                }
        }
        parseheader(($fh ? $CUSTOMFOOTER : $FOOTER));

        $timing_stop = explode(' ', microtime());
        $rendertime = number_format((($timing_stop[0]+$timing_stop[1])-($timing_start[0]+$timing_start[1])), 4);
        if($pref['displayrendertime']){ $rinfo .= "Render time: ".$rendertime." second(s). "; }
        if($pref['displaysql']){ $rinfo .= "DB queries: ".$dbq.". "; }
        if($pref['displaycacheinfo']){ $rinfo .= $cachestring."."; }
        echo ($rinfo ? "<div style='text-align:center' class='smalltext'>$rinfo</div>" : "");
		  echo ($sDBdbg ? "<div style='text-align:left' class='smalltext'>$sDBdbg</div>\n" : "");
        
}
echo "</body>
</html>";

$sql -> db_Close();
ob_end_flush();
?>