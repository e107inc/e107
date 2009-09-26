<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/integrity_check/languages/Thai.php,v $
|     $Revision: 1.4 $
|     $Date: 2009-09-26 15:20:56 $
|     $Author: yarodin $
+----------------------------------------------------------------------------+
/*แปลและพัฒนาส่วนระบบภาษาไทยโดย ผศ.ประชิด ทิณบุตร เมื่อวันที่ 18 มีนาคม 2549  แก้ไขล่าสุด 28 มีนาคม.2550
อาจารย์ประจำสาขาวิชาศิลปกรรม มหาวิทยาลัยราชภัฏจันทรเกษม ถนนรัชดาภิเษก เขตจตุจักร กทม 10900.โทร.(66) 0 2942 6900  ต่อ 3011,3014
Thai Developer & Translation : Assistant Professor Prachid Tinnabutr : Division of Art ,Chandrakasem Rajabhat University,Jatuchak,Bangkok ,Thailand.10900. Tel :(66) 02 9426900 ext:3011,3014
Last update:28 March 2007 .
Personal Address : 144/157 Moo 1 ,Changwatana Rd.Pakkret District ,Nonthaburi Province,Thailand,11120 Tel/Fax:(66)0 2962 9505  prachid@prachid.com,prachid@wittycomputer.com ,Mobile Phone : (66) 08 9667 0091
URL : http://www.prachid.com, http://www.wittycomputer.com, http://www.e107thailand.com(Official International Sites)
*/
define("Integ_01", "การบันทึกเรียบร้อยแล้ว");
define("Integ_02", "การบันทึกล้มเหลว");
define("Integ_03", "ไฟล์ที่หายไป:");
define("Integ_04", "ความผิดพลาดของCRC:");
define("Integ_05", "ไม่สามารถเปิดไฟล์...");
define("Integ_06", "ตรวจสอบความคงสภาพของไฟล์");
define("Integ_07", "ไม่มีไฟล์ที่ใช้ได้");
define("Integ_08", "ตรวจสอบความคงสภาพไฟล์");
define("Integ_09", "สร้างไฟล์ sfv");
define("Integ_10", "แฟ้มที่เลือกไว้จะ<u>ไม่ถูกบันทึก</u> เข้ารวมเป็น crc-file.");
define("Integ_11", "ชื่อไฟล์:");
define("Integ_12", "สร้างไฟล์ sfv");
define("Integ_13", "ตรวจสอบการคงสภาพไฟล์");
define("Integ_14", "สร้างไฟล์ SFV ไม่ได้, เป็นเพราะแฟ้ม ".e_PLUGIN."integrity_check/<b>{output}</b> ไม่อนุญาตให้เขียน-บันทึกได้.กรุณาไปอนุญาตไฟล์หรือเปลี่ยนโหมดอนุญาตไฟล์ของแฟ้มนี้เป็นโหมด 777!");
define("Integ_15", "ได้ตรวจสอบทุกไฟล์แล้ว พบว่ายังเป็นปกติ o.k.!");
define("Integ_16", "ไม่มี core-crc-file ที่ให้ใช้ได");
define("Integ_17", "ไม่มี plugin-crc-files ที่ให้ใช้ได้");
define("Integ_18", "สร้างโปรแกรมเสริม CRC-File ใหม่");
define("Integ_19", "ตรวจสอบไฟล์ระบบหลัก(Core-Checksum-Files)");
define("Integ_20", "ตรวจสอบไฟล์โปรแกรมเสริม(Plugin-Checksum-Files)");
define("Integ_21", "เลือกโปรแกรมเสริมสำหรับที่จะสร้างเป็น crc-file .");
define("Integ_22", "ใช้ gzip");
define("Integ_23", "ตรวจสอบชุดแบบ(Theme)ที่ติดตั้งไว้แล้วเท่านั้น");
define("Integ_24", "หน้าศูนย์กลางระบบ");
define("Integ_25", "ออกจากศูนย์กลาง");
define("Integ_26", "เรียกเว็ปไซท์ด้วย normal header");
define("Integ_27", "คลิกที่นี่เพื่อเลือกรายการที่ต้องการตรวจสอบไฟล์หลักของระบบ");
define("Integ_30", "สำหรับการใช้งานCPUให้น้อยลงนั้น ,คุณสามารถทำแต่ขั้นตอน นับแต่ขั้นตอนที่ 1 - 10.");
define("Integ_31", "ขั้นตอน: ");
define("Integ_32", "มีไฟล์ที่ชื่อ<b>log_crc.txt</b>อยู่ในแฟ้ม crc . กรุณาลบออก! (หรือ กระพริบจอใหม่)");
define("Integ_33", "มีไฟล์ที่ชื่อ<b>log_miss.txt</b> อยู่ในแฟ้ม crc . กรุณาลบออก! (หรือ กระพริบจอใหม่)");
define("Integ_34", "แฟ้ม Crc-ไม่สามารถเขียน-บันทึกได้!");
define("Integ_35", "อาจเป็นเพราะคุณอนุญาตไว้เฉพาะรายการที่เลือกไว้เท่านั้น <b>one</b> step:");
define("Integ_36", "คลิกที่นี่, หากไม่ต้องการรอถึง 5 วินาที จนกว่าจะถึงขั้นต่อไป:");
define("Integ_37", "คลิกที่นี่");
define("Integ_38", "อื่นๆ <u><i>{counts}</i></u> lines to do...");
define("Integ_39", "กรุณาลบไฟล์:<br />".e_PLUGIN."ตรวจสอบไฟล์ที่/<u><i>do_core_file.php</i></u>!<br />ล้าสมัยและไม่มีความสำคัญจำเป็นต่อไปแล้ว...");


?>