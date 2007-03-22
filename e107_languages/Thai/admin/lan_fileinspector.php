<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Thai/admin/lan_fileinspector.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-03-22 00:34:30 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
แปลและพัฒนาส่วนระบบภาษาไทยโดย ผศ.ประชิด ทิณบุตร เมื่อวันที่ 18 มีนาคม 2549  แก้ไขล่าสุด 28 พย.2549
อาจารย์ประจำสาขาวิชาศิลปกรรม มหาวิทยาลัยราชภัฏจันทรเกษม ถนนรัชดาภิเษก เขตจตุจักร กทม 10900.โทร.(66) 0 2942 6900  ต่อ 3011,3014
Thai Developer & Translation : Assistant Professor Prachid Tinnabutr : Division of Art ,Chandrakasem Rajabhat University,Jatuchak,Bangkok ,Thailand.10900. Tel :(66) 02 9426900 ext:3011,3014
Last update:28 nov 2006 .
Personal Address : 144/157 Moo 1 ,Changwatana Rd.Pakkret District ,Nonthaburi Province,Thailand,11120 Tel/Fax:(66)0 2962 9505  prachid@prachid.com,prachid@wittycomputer.com ,Mobile Phone : (66) 08 9667 0091
URL : http://www.prachid.com, http://www.wittycomputer.com, http://www.e107thailand.com
*/

define("FC_LAN_1", "ตัวตรวจสอบไฟล์");
define("FC_LAN_2", "เลือกค่าการตรวจตรา");
define("FC_LAN_3", "แสดง");
define("FC_LAN_4", "ทั้งหมด");
define("FC_LAN_5", "ไฟล์หลักของระบบ");
define("FC_LAN_6", "ความคงสภาพไฟล์ ที่ล้มเหลวเท่านั้น");
define("FC_LAN_7", "ไม่ใช่ไฟล์หลักของระบบ");
define("FC_LAN_8", "ตรวจสอบความคงสภาพไฟล์หลักของระบบ");
define("FC_LAN_9", "เปิด");
define("FC_LAN_10", "ปิด");
define("FC_LAN_11", "ตรวจสอบเดี๋ยวนี้");
define("FC_LAN_12", "ไม่มี");
define("FC_LAN_13", "ไฟล์หลักของระบบที่หายไปคือ");
define("FC_LAN_14", "แสดงผลเป็น");
define("FC_LAN_15", "ลำดับโครงสร้างของแฟ้มไฟล์");
define("FC_LAN_16", "รายการ");
define("FC_LAN_17", "คำค้นที่สอดคล้อง");
define("FC_LAN_18", "Regular expression");
define("FC_LAN_19", "แสดงเลขบรรทัด");
define("FC_LAN_20", "แสดงบรรทัดที่สอดคล้อง");
define("FC_LAN_21", "ไฟล์หลักเดิมของระบบ");

define("FR_LAN_1", "ตรวจหา");
define("FR_LAN_2", "ผลการตรวจ");
define("FR_LAN_3", "ภาพรวม");
define("FR_LAN_4", "ไฟล์หลักของระบบ");
define("FR_LAN_5", "ไม่ใช่ไฟล์หลักของระบบ");
define("FR_LAN_6", "ไฟล์ทั้งหมด");
define("FR_LAN_7", "ตรวจสอบความคงสภาพไฟล์");
define("FR_LAN_8", "ไฟล์หลักของระบบ ผ่านการตรวจสอบ");
define("FR_LAN_9", "ไฟล์หลักของระบบที่ล้มเหลว");
define("FR_LAN_10", "ความล้มเหลวอาจจะเกิดจาก");
define("FR_LAN_11", "ไฟล์ไม่สมบูรณ์หรือทำงานขัดข้องเนื่องจาก...");
define("FR_LAN_12", "This could be for a number of reasons such as the file being corrupted in the zip, got corrupted during 
extraction or got corrupted during file upload via FTP. You should try reuploading the file to your server 
and re-run the scan to see if this resolves the error.");
define("FR_LAN_13", "เป็นไฟล์ที่ล้าสมัย");
define("FR_LAN_14", "If the file is from an older release of e107 to the version you are 
running then it will fail the integrity check. Make sure you have uploaded the newest version of this file.");
define("FR_LAN_15", "ได้แก้ไขไฟล์แล้ว");
define("FR_LAN_16", "If you have edited this file in any way it will not pass the integrity check. If you
intentionally edited this file then you need not worry and can ignore this integrity check fail. If however
the file was edited by someone else without authorisation you may want to re-upload the proper version of
this file from the e107 zip.");
define("FR_LAN_17", "หากคุณเป็นผู้ใช้ CVS ");
define("FR_LAN_18", "If you run checkouts of the e107 CVS on your site instead of the official e107 stable 
releases, then you will discover files have failed integrity check because they have been edited by a dev 
after the latest core image snapshot was created.");
define("FR_LAN_19", "ไฟล์ที่ล้มเหลว");
define("FR_LAN_20", "ไฟล์ที่ผ่านการตรวจสอบ");
define("FR_LAN_21", "ไม่มี");
define("FR_LAN_22", "ไฟล์หลักของระบบที่หายไป");
define("FR_LAN_23", "ไม่พบรายการที่ค้นหา.");
define("FR_LAN_24", "ไฟล์หลักเดิมของระบบ ");
define("FR_LAN_25", "ไม่สามารถคำนวณหาได้");

define("FR_LAN_26", "คำเตือน! ตรวจพบว่าไม่ปลอดภัย!");
define("FR_LAN_27", "ไฟล์ที่อยู่ในระบบของคุณที่ไม่ได้ใช้ประโยชน์อันใด และสมควรรีบลบออกจากระบบโดยทันที.");
define("FR_LAN_28", "ไฟล์ที่ไม่ปลอดภัย");
?>