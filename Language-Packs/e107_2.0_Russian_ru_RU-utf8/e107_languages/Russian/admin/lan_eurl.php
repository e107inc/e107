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
 * $Source: e_LANGUAGEDIR_ABSRussian/admin/lan_eurl.php $
 * $Revision: 1.0 $
 * $Date: 2012/12/16 13:24:14 $
 *  $Author: NixanR $
 *
*/

define("LAN_EURL_NAME", "Управление URL сайта");
define("LAN_EURL_NAME_CONFIG", "Профили");
define("LAN_EURL_NAME_ALIASES", "Альясы");
define("LAN_EURL_NAME_SETTINGS", "Общие параметры");
define("LAN_EURL_NAME_HELP", "Справка");
define("LAN_EURL_EMPTY", "Список пуст");
define("LAN_EURL_LEGEND_CONFIG", "Выберите профиль URL на область сайта");
define("LAN_EURL_LEGEND_ALIASES", "Сконфигурируйте псевдонимы Базового URL на Профиль URL");
define("LAN_EURL_DEFAULT", "по умолчанию");
define("LAN_EURL_PROFILE", "Профиль");
define("LAN_EURL_INFOALT", "Информация");
define("LAN_EURL_PROFILE_INFO", "Информация профиля, не доступна");
define("LAN_EURL_LOCATION", "Расположение профиля:");
define("LAN_EURL_LOCATION_NONE", "Конфигурационный файл не доступен");
define("LAN_EURL_FORM_HELP_DEFAULT", "Псевдоним, когда на языке по умолчанию.");
define("LAN_EURL_FORM_HELP_ALIAS_0", "Значение по умолчанию");
define("LAN_EURL_FORM_HELP_ALIAS_1", "Псевдоним, когда в");
define("LAN_EURL_FORM_HELP_EXAMPLE", "Базовый URL:");
define("LAN_EURL_ERR_ALIAS_MODULE", "Альяс &quot;%1\$s&quot; не может быть сохранен. Есть системный профиль URL с тем же именем. Выберите другое значение псевдонима для системного профиля URL &quot;%2\$s&quot;");
define("LAN_EURL_SETTINGS_PATHINFO", "Удалите имя файла из URL");
define("LAN_EURL_SETTINGS_MAINMODULE", "Корневое пространство имен");
define("LAN_EURL_SETTINGS_MAINMODULE_HELP", "Выберите, какая область сайта будет связана с Вашим основным URL сайта.");
define("LAN_EURL_SETTINGS_REDIRECT", "Перенаправление в систему - не найдена страница");
define("LAN_EURL_SETTINGS_REDIRECT_HELP", "Если установлено в ложь, не найденная страница, будет обработана на месте (без перенаправления браузера)");
define("LAN_EURL_SETTINGS_SEFTRANSLATE", "Автоматизированное создание строки типа - SEF");
define("LAN_EURL_SETTINGS_SEFTRANSLATE_HELP", "Выберите, как будет собрана строка SEF, когда она автоматически создана из значения Заголовка (например, в новостях, пользовательских страницах, и т.д.)");
define("LAN_EURL_SETTINGS_SEFTRTYPE_NONE", "Just secure it");
define("LAN_EURL_SETTINGS_SEFTRTYPE_DASHL", "dasherize-to-lower-case");
define("LAN_EURL_SETTINGS_SEFTRTYPE_DASHC", "Dasherize-To-Camel-Case");
define("LAN_EURL_SETTINGS_SEFTRTYPE_DASH", "Dasherize-with-no-case-CHANGE");
define("LAN_EURL_SETTINGS_SEFTRTYPE_UNDERSCOREL", "underscore_to_lower_case");
define("LAN_EURL_SETTINGS_SEFTRTYPE_UNDERSCOREC", "Underscore_To_Camel_Case");
define("LAN_EURL_SETTINGS_SEFTRTYPE_UNDERSCORE", "Underscore_with_no_case_CHANGE");
define("LAN_EURL_SETTINGS_SEFTRTYPE_PLUSL", "plus+separator+to+lower+case");
define("LAN_EURL_SETTINGS_SEFTRTYPE_PLUSC", "Plus+Separator+To+Camel+Case");
define("LAN_EURL_SETTINGS_SEFTRTYPE_PLUS", "Plus+separator+with+no+case+CHANGE");
define("LAN_EURL_MODREWR_DESCR", "Удаляет имя файла сценария записи (rewrite.php) из Ваших URL. Требуется установленный модуль mod_rewrite на Вашем сервере (веб-сервер Apache). После включения этой установки, перейдите в корневую папку сайта, переименуйте htaccess.txt в .htaccess и измените <em>&quot;RewriteBase&quot;</em> директивы, если требуется.");
define("LAN_EURL_MENU", "URL сайта");
define("LAN_EURL_MENU_CONFIG", "URL профили");
define("LAN_EURL_MENU_ALIASES", "Альясы");
define("LAN_EURL_MENU_SETTINGS", "Настройки");
define("LAN_EURL_MENU_HELP", "Справка");
define("LAN_EURL_UC", "На реконструкции");
define("LAN_EURL_CORE_MAIN", "Корневое Пространство имен сайта - псевдоним не используется.");
define("LAN_EURL_CORE_NEWS", "Новости");
define("LAN_EURL_NEWS_DEFAULT_LABEL", "По умолчанию");
define("LAN_EURL_NEWS_DEFAULT_DESCR", "Наследие прямых URL. Примеры: <br />http://yoursite.com/news.php<br />http://yoursite.com/news.php?extend.1 <em>(view news item)</em>");
define("LAN_EURL_NEWS_REWRITE_LABEL", "Дружественные URL без ID (не производительно, более дружественный)");
define("LAN_EURL_NEWS_REWRITE_DESCR", "Демонстрирует парсинг связи, обновляемой вручную и сборку. <br />Examples: <br />http://yoursite.com/news<br />http://yoursite.com/news/News Title <em>(view news item)</em>");
define("LAN_EURL_NEWS_REWRITEX_LABEL", "Дружественные URL с ID (разумная производительность)");
define("LAN_EURL_NEWS_REWRITEX_DESCR", "Демонстрирует автоматизированный парсинг ссылки и сборку на основе предопределенных правил маршрута. <br />Examples: <br />http://yoursite.com/news<br />http://yoursite.com/news/1/News Title <em>(view news item)</em>");
define("LAN_EURL_NEWS_REWRITEF_LABEL", "Полностью дружественные URL (не влияет на производительность и самые дружественные)");
define("LAN_EURL_NEWS_REWRITEF_DESCR", "Examples: <br />http://yoursite.com/news/News Category/News Title<em>(view news item)</em><br />http://yoursite.com/news/Category/News Category <em>(list news items)</em>");
define("LAN_EURL_CORE_USER", "Пользователи");
define("LAN_EURL_USER_DEFAULT_LABEL", "По умолчанию");
define("LAN_EURL_USER_DEFAULT_DESCR", "Унаследованный прямой URL. Пример: http://yoursite.com/user.php?id.1");
define("LAN_EURL_USER_REWRITE_LABEL", "Дружественные URL");
define("LAN_EURL_USER_REWRITE_DESCR", "Поисковая система и удобные для пользователя URL. <br />Example: http://yoursite.com/user/UserDisplayName");
define("LAN_EURL_CORE_PAGE", "Пользовательские страницы");
define("LAN_EURL_PAGE_DEFAULT_LABEL", "По умолчанию");
define("LAN_EURL_PAGE_DEFAULT_DESCR", "Унаследованный прямой URL. Пример: http://yoursite.com/page.php?1");
define("LAN_EURL_PAGE_SEF_LABEL", "Дружественные URL с ID (производительность)");
define("LAN_EURL_PAGE_SEF_DESCR", "Поисковая система и удобные для пользователя URL. <br />Example: http://yoursite.com/page/1/Page-Name");
define("LAN_EURL_PAGE_SEFNOID_LABEL", "Дружественные URL без ID (не производительно, более дружественный)");
define("LAN_EURL_PAGE_SEFNOID_DESCR", "Поисковая система и удобные для пользователя URL. <br />Example: http://yoursite.com/page/Page-Name");
define("LAN_EURL_CORE_SEARCH", "Поиск");
define("LAN_EURL_SEARCH_DEFAULT_LABEL", "URL поиска по умолчанию");
define("LAN_EURL_SEARCH_DEFAULT_DESCR", "Унаследованный прямой URL. Пример: http://yoursite.com/search.php");
define("LAN_EURL_SEARCH_REWRITE_LABEL", "Дружественный URL");
define("LAN_EURL_SEARCH_REWRITE_DESCR", "Example: http://yoursite.com/search/");
define("LAN_EURL_CORE_SYSTEM", "Система");
define("LAN_EURL_SYSTEM_DEFAULT_LABEL", "URL-адреса системы по умолчанию");
define("LAN_EURL_SYSTEM_DEFAULT_DESCR", "URL-адреса для страниц: Not Found, Acess denied, etc. Example: http://yoursite.com/?route=system/error/notfound");
define("LAN_EURL_SYSTEM_REWRITE_LABEL", "Дружественные URL системы");
define("LAN_EURL_SYSTEM_REWRITE_DESCR", "URL-адреса для страниц: Not Found, Acess denied, etc. <br />Example: http://yoursite.com/system/error404");
define("LAN_EURL_CORE_INDEX", "Главная страница");
define("LAN_EURL_CORE_INDEX_INFO", "Главная страница не может иметь псевдоним.");


?>