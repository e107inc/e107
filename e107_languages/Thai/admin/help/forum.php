<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ฉSteve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Thai/admin/help/forum.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-03-22 00:34:29 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
แปลและพัฒนาส่วนระบบภาษาไทยโดย ผศ.ประชิด ทิณบุตร เมื่อวันที่ 18 มีนาคม 2549  แก้ไขล่าสุด 28 พย.2549
อาจารย์ประจำสาขาวิชาศิลปกรรม มหาวิทยาลัยราชภัฏจันทรเกษม ถนนรัชดาภิเษก เขตจตุจักร กทม 10900.โทร.(66) 0 2942 6900  ต่อ 3011,3014
Thai Developer & Translation : Assistant Professor Prachid Tinnabutr : Division of Art ,Chandrakasem Rajabhat University,Jatuchak,Bangkok ,Thailand.10900. Tel :(66) 02 9426900 ext:3011,3014
Last update:28 nov 2006 .
Personal Address : 144/157 Moo 1 ,Changwatana Rd.Pakkret District ,Nonthaburi Province,Thailand,11120 Tel/Fax:(66)0 2962 9505  prachid@prachid.com,prachid@wittycomputer.com ,Mobile Phone : (66) 08 9667 0091
URL : http://www.prachid.com, http://www.wittycomputer.com, http://www.e107thailand.com
*/

if (!defined('e107_INIT')) { exit; }

$caption = "Forum Help";
$text = "<b>General</b><br />
Use this screen to create or edit your forums<br />
<br />
<b>Parents/Forums</b><br />
A parent is a heading that other forums are displayed under, this makes layout simpler and makes navigating around your forums much simpler for visitors.
<br /><br />
<b>Accessibility</b>
<br />
You can set your forums to only be accessible to certain visitors. Once you have set the 'class' of the visitors you can tick the 
class to only allow those visitors access to the forum. You can set parents or individual forums up in this way.
<br /><br />
<b>Moderators</b>
<br />
Tick the names of the listed administrators to give them moderator status on the forum. The administrator must have forum moderation permissions to be listed here.
<br /><br />
<b>Ranks</b>
<br />
Set your user ranks from here. If the image fields are filled in, images will be used, to use rank names enter the names and make sure the corresponding rank image field is blank.<br />The threshold is the number of points the user needs to gain before his level changes.";
$ns -> tablerender($caption, $text);
unset($text);
?>