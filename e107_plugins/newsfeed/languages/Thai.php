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
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/newsfeed/languages/Thai.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-04-13 16:03:08 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/


define("NFLAN_01", "ข่าวป้อน");
define("NFLAN_02", "โปรแกรมเสริมนี้จะรับเอาข่าวป้อนรูปแบบ rss มาจากเว็ปไซท์ที่คุณต้องการมาแสดง");
define("NFLAN_03", "ตั้งค่าข่าวป้อน");
define("NFLAN_04", "โปรแกรมเสริมข่าวป้อนได้ติดตั้งเรียบร้อยแล้ว  การเพิ่มและการตั้งค่า, ให้ไปที่หน้าศูนย์กลางระบบและเลือกกำหนดค่าข่าวป้อนจากส่วนการจัดการโปรแกรมเสริมระบบ.");
define("NFLAN_05", "แก้ไข");
define("NFLAN_06", "ลบ");
define("NFLAN_07", "ข่าวป้อนที่มีอยู่");
define("NFLAN_08", "หน้าแรกข่าวป้อน");
define("NFLAN_09", "สร้างข่าวป้อน");
define("NFLAN_10", "ที่อยู่URL เพื่อป้อนเป็น rss");
define("NFLAN_11", "ที่อยู่เส้นทางของภาพประกอบข่าว");
define("NFLAN_12", "การเปิดใช้");
define("NFLAN_13", "ยังไม่ใช้งาน");
define("NFLAN_14", "ในเมนูเท่านั้น");
define("NFLAN_15", "สร้างข่าวป้อน");
define("NFLAN_16", "ปรับปรุงข่าวป้อน");
define("NFLAN_17", "พิมพ์ 'default' ในกรอบเพื่อใช้ค่าที่กำหนดไว้ในข่าวป้อนที่กำหนดเส้นทางที่อยู่ของรูปภาพเอาไว้,หากว่างไว้หมายถึงไม่ใช้ภาพประกอบข่าวป้อน.");
define("NFLAN_18", "ปรับปรุงช่วงห่างเป็นวินาที");
define("NFLAN_19", "ie, 3600: ข่าวป้อนจะปรับปรุงใหม่ทุกชั่วโมง");
define("NFLAN_20", "ในหน้าหลักข่าวป้อนเท่านั้น");
define("NFLAN_21", "ในทั้งสองเมนูและหน้าข่าวป้อน");
define("NFLAN_22", "เลือกว่าจะให้ข่าวป้อนไปแสดงที่ใด");
define("NFLAN_23", "เพิ่มข่าวป้อนในฐานข้อมูลแล้ว.");
define("NFLAN_24", "กรอกข้อมูลยังไม่ครบ.");
define("NFLAN_25", "แรับปรุงข่าวป้อนในฐานข้อมูลแล้ว.");
define("NFLAN_26", "ปรับปรุงช่วงห่างเวลา");
define("NFLAN_27", "เลือกค่า");
define("NFLAN_28", "URL");
define("NFLAN_29", "ข่าวป้อนที่ให้ใช้ได้");
define("NFLAN_30", "ชื่อข่าวป้อน");
define("NFLAN_31", "กลับไปที่รายการข่าวป้อน");
define("NFLAN_32", "ไม่พบข่าวป้อนตามรายการที่ต้องการ.");
define("NFLAN_33", "วันที่ตีพิมพ์เผยแพร่: ");
define("NFLAN_34", "ไม่ทราบ");
define("NFLAN_35", "ส่งโดย ");
define("NFLAN_36", "คำอธิบาย");
define("NFLAN_37", "สรุปย่อข่าวป้อน, พิมพ์ 'default' เพื่อใช้คำอธิบายสรุปข่าวสั้นๆจากข่าวป้อน");
define("NFLAN_38", "หัวข้อข่าว");
define("NFLAN_39", "รายละเอียดข่าว");
define("NFLAN_40", "ลบข่าวป้อนแล้ว");
define("NFLAN_41", "ยังไม่ได้กำหนดข่าวป้อน");

define("NFLAN_42", "<b>&raquo;</b> <u>ชื่อข่าวป้อน:</u>
	The identifying name of the feed, can be anything you like.
	<br /><br />
	<b>&raquo;</b> <u>URL to rss feed:</u>
	ที่อยู่ของข่าวป้อน rss
	<br /><br />
	<b>&raquo;</b> <u>เส้นทางของภาพ:</u>
	If the feed has an image defined in it, enter 'default' to use it. To use your own image, enter the full path to it. Leave blank to use no image at all.
	<br /><br />
	<b>&raquo;</b> <u>คำอธิบาย:</u>
	Enter a short description of the feed, or 'default' to use the description defined in the feed (if there is one).
	<br /><br />
	<b>&raquo;</b> <u>Update interval in seconds:</u>
	The amount of seconds that elapse before the feed is updated, for example, 1800: 30 minutes, 3600: an hour.
	<br /><br />
	<b>&raquo;</b> <u>การเปิดใช้:</u>
	where you want the feed results to be displayed, to see menu feeds you will need to activate the newsfeeds menu on the <a href='".e_ADMIN."menus.php'>menus page</a>.
	<br /><br />For a good list of available feeds, see <a href='http://www.syndic8.com/' rel='external'>syndic8.com</a> or <a href='http://feedfinder.feedster.com/index.php' rel='external'>feedster.com</a>");
define("NFLAN_43", "ข่วยเหลือข่าวป้อน");
define("NFLAN_44", "คลิกอ่าน");

?>