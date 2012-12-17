<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Language File
 *
 * $Source: /e107_plugins/alt_auth/languages/Russian/admin_importdb_conf.php $
 * $Revision: 1.0 $
 * $Date: 2012/12/16 19:23:20 $
 *  $Author: NixanR $
 *
*/

define("IMPORTDB_LAN_9", "Метод пароля:");
define("IMPORTDB_LAN_10", "Сконфигурируйте импортированный тип пароля базы данных");
define("IMPORTDB_LAN_11", "Этот параметр следует использовать, когда вы импортировали других пользователей системы, основанной на старой версии E107. Это позволяет принимать закодированные пароли в выбранном нестандартном формате. Каждый  пароль пользователей преобразуется в формат E107, когда они авторизуются.");
define("LAN_AUTHENTICATE_HELP", "Этот метод проверки подлинности будет использоваться <i>только</i>, когда вы импортировали базу данных пользователей в E107 и пароль сохранен в несовместимом формате. Оригинальный пароль считывается из локальной базы данных и проверяется на формат хранения исходной системы. Если он проходит проверку, пароль преобразуется в текущий E107-совместимый формат и хранятся в базе данных. Через некоторое время можете обычно отключить плагин alt-auth, так как активные пользователи, все, сохранят свои пароли в совместимом формате.");


?>