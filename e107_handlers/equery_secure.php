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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/equery_secure.php,v $
|     $Revision: 1.1 $
|     $Date: 2004-09-21 19:10:26 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/

if(file_exists(e_LANGUAGEDIR.e_LANGUAGE."/lan_equery_secure.php")){
  include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_equery_secure.php");
}else{
  include_once(e_LANGUAGEDIR."English/lan_equery_secure.php");
}
require_once(HEADERF);

$caption = EQSEC_LAN2;
$text = "<div style='text-align: center;' >
<div class='fcaption'>".EQSEC_LAN1."</div>
<br />";
$text .= "<table style='text-align:center;'>
<tr><td><b>".EQSEC_LAN4."</b></td><td> ".($tmp_s_ref!="" ? $tmp_s_ref : EQSEC_LAN3 )."</td></tr>
<tr><td><b>".EQSEC_LAN5."</b></td><td> ".e_SELF."</td></tr></table>
<br />";
$text .= "<br />
<form action=\"".e_SELF."?".e_QUERY."\" method=\"post\" >
<div><input class='button' type='submit' value='".EQSEC_LAN6."' name='equery_secure' id='equery_secure' /></div>
</form>
<br /><br />

<br />
<a href=\"javascript:void(0);\" onclick=\"history.go(-1);\" >".EQSEC_LAN7."</a>

</div>";

$ns -> tablerender($caption, $text);

require_once(FOOTERF);
exit;
?>