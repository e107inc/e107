<?php
/*
+ ----------------------------------------------------------------------------+
|     $Sitio web e107 - Archivos del lenguaje $
|     $Versión: 0.7.16 $
|     $Date: 2009/09/16 17:51:27 $
|     $Author: E107 <www.e107.org> $
|     $Traductor: Josico <www.e107.es> $
+----------------------------------------------------------------------------+
*/
setlocale(LC_ALL, 'es_ES.UTF-8', 'es_ES.utf8', 'esp_esp.utf8', 'es');
define("CORE_LC", 'es');
define("CORE_LC2", 'es');
// define("TEXTDIRECTION","rtl");
define("CHARSET", "utf-8");  // for a true multi-language site. :)
define("CORE_LAN1","Error : tema no encontrado.\\n\\nCambie el tema usado en preferencias (administración) o copie los archivos del tema seleccionado al servidor.");

//v.616
//obsolete define("CORE_LAN2"," \\1 wrote:");// "\\1" represents the username.
//obsolete define("CORE_LAN3","file attachment disabled");

//v0.7+
define("CORE_LAN4", "Por favor, elimine install.php de su servidor");
define("CORE_LAN5", "Si no lo hace, pone en riesgo potencial su sitio web");
// v0.7.6 
define("CORE_LAN6", "La protección por flood ha sido activada en este sitio y se le avisa que continúa requeriendo páginas podrá ser baneado."); 
define("CORE_LAN7", "El núcleo está intentando recuperar ajustes del backup automático."); 
define("CORE_LAN8", "Ajustes del núcleo en error"); 
define("CORE_LAN9", "El núcleo no puede recuperar del backup automático. Ejecución fallida."); 
define("CORE_LAN10", "Detectada cookie corrupta - desconectando.");
define("CORE_LAN11", "Tiempo de renderizado: "); 
define("CORE_LAN12", " seg, "); 
define("CORE_LAN13", " de éstos para llamadas sql. "); 
define("CORE_LAN14", "");                       // Used in 0.8 
define("CORE_LAN15", "Llamadas sql: "); 
define("CORE_LAN16", "Uso memoria: "); 

// img.bb
define('CORE_LAN17', '[ imagen deshabilitada ]');
define('CORE_LAN18', 'Imagen: ');

define("CORE_LAN_B", "b"); 
define("CORE_LAN_KB", "kb"); 
define("CORE_LAN_MB", "Mb"); 
define("CORE_LAN_GB", "Gb"); 
define("CORE_LAN_TB", "Tb"); 

define("LAN_WARNING", "¡Atención!"); 
define("LAN_ERROR", "Error");
define("LAN_ANONYMOUS", "Anónimo");
define("LAN_EMAIL_SUBS", "-email-");
?>