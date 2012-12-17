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
 * $Source: /e107_plugins/alt_auth/languages/Russian/admin_ldap_conf.php $
 * $Revision: 1.0 $
 * $Date: 2012/12/16 19:33:39 $
 *  $Author: NixanR $
 *
*/

define("LDAPLAN_1", "Адрес сервера: ");
define("LDAPLAN_2", "Base DN or Domain<br />LDAP - Enter BaseDN<br />AD - enter the fqdn eg ad.mydomain.co.uk");
define("LDAPLAN_3", "LDAP версия просмотра пользователей. <br />Полный контекст пользователя, который в состоянии искать каталог.");
define("LDAPLAN_4", "LDAP версия просмотра пользователей. <br />Пароль для просмотра LDAP пользователей.");
define("LDAPLAN_5", "LDAP версия");
define("LDAPLAN_6", "Настройка LDAP аутентификации");
define("LDAPLAN_7", "eDirectory фильтр поиска:");
define("LDAPLAN_8", "Эта информация будет использоваться, чтобы удостовериться, что имя пользователя находится в корректном дереве <br />e.g. '(objectclass=inetOrgPerson)'");
define("LDAPLAN_9", "Текущий фильтр поиска будет: ");
define("LDAPLAN_10", "Настройки обновлены");
define("LDAPLAN_11", "ВНИМАНИЕ: Похоже, что LDAP модуль в настоящее время недоступен, настройка метода аутентификации для LDAP, вероятно, не работает!");
define("LDAPLAN_12", "Тип сервера");
define("LDAPLAN_13", "Обновление параметров");
define("LDAPLAN_14", "OU for AD (e.g. ou=itdept)");
define("LAN_AUTHENTICATE_HELP", "Этот метод может быть использован для аутентификации по отношению к большинству LDAP серверов, в том числе Novell, eDirectory и Microsoft Active Directory. Он требует, чтобы PHP LDAP расширение было загружено. Обратитесь к вики для получения дополнительной информации.");


?>