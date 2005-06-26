<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Spanish/lan_installer.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-06-26 22:53:13 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/

define("LANINS_001", "Instalación e107");


define("LANINS_002", "Etapa ");
define("LANINS_003", "1");
define("LANINS_004", "Selección del idioma");
define("LANINS_005", "Por favor, escoja el idioma a usar durante la instalación");
define("LANINS_006", "Fijar idioma");
define("LANINS_007", "2");
define("LANINS_008", "PHP &amp; MySQL Comprobación de versiones / Comprobación de permisos de archivos");
define("LANINS_009", "Reinicie los permisos del archivo");
define("LANINS_010", "Archivo no escribible: ");
define("LANINS_011", "Error");
define("LANINS_012", "Las funciones MySQL no parecen existir. Esto probablemente significa que la extensión PHP MySQL no fué instalada o configurada correctamente."); // help for 012
define("LANINS_013", "No se pudo determinar la versión de su MySQL. Esto podría significar que su servidor MySQL está caído o rechazando las conexiones.");
define("LANINS_014", "Permisos de archivo");
define("LANINS_015", "Versión PHP");
define("LANINS_016", "MySQL");
define("LANINS_017", "OK");
define("LANINS_018", "Asegúrese de que estos archivos listados tienen permisos de escritura. Si tiene algún problema a la hora de configurarlos, contacte con su servidor.");
define("LANINS_019", "La versión de PHP instalada en su servidor no es operativa para funcionar con e107. e107 requiere una versión mayor de 4.3.0 para funcionar correctamente. Actualice su versión PHP o contacte con su servidor para actualizar.");
define("LANINS_020", "Continúe la instalación");
define("LANINS_021", "3");
define("LANINS_022", "Detalles del servidor MySQL");
define("LANINS_023", "Por favor, escriba los ajustes de MySQL aquí.
			  
Si tiene permisos de administrador, podrá crear una nueva base de datos activando la caja, si no, debería crear una base de datos o usar una ya existente.

Si solo tiene una base de datos, utilice el prefijo para que otros scripts puedan utilizar la misma base de datos.
Si no tiene datos de su MySQL, contacte con su servidor.");
define("LANINS_024", "Servidor MySQL:");
define("LANINS_025", "Nombre de usuario MySQL:");
define("LANINS_026", "Contraseña MySQL:");
define("LANINS_027", "Base de datos MySQL:");
define("LANINS_028", "¿Crear base de datos?");
define("LANINS_029", "Prefijo de la tabla:");
define("LANINS_030", "El servidor MySQL que quiere utilizar. Puede incluir el puerto utilizado. ej. \"servidor:puerto\" o una ruta local ej. \":/ruta/a/socket\" para localhost.");
define("LANINS_031", "El nombre de usuario que desea para conectar a su servidor MySQL");
define("LANINS_032", "La contraseña del usuario");
define("LANINS_033", "La base de datos MySQL que desea utilizar en e107, algunas veces referenciada como una estructura. Si el usuario tiene permiso para crear bases de datos, tiene la opción de hacerlo si actualmente no existe.");
define("LANINS_034", "El prefijo que desea utilizar en e107 para encabezar las tablas. Útil para instalaciones múltiples de e107 en una misma base de datos.");
define("LANINS_035", "Continuar");
define("LANINS_036", "4");
define("LANINS_037", "Verificación de conexión MySQL");
define("LANINS_038", " y creación de la base de datos");
define("LANINS_039", "Asegúrese que ha rellenado todos los campos más importantes, Servidor, Usuario y Base de datos MySQL.(Son necesarios para el servidor MySQL)");
define("LANINS_040", "Errores");
define("LANINS_041", "e107 no pudo establecer conexión con el servidor MySQL usando la información dada. Por favor, vuelva a la última página para asegurarse que la información es correcta.");
define("LANINS_042", "Conexión con el servidor MySQL establecida y verificada.");
define("LANINS_043", "Imposible crear la base de datos, por favor, asegúrese que tiene permisos suficientes para crear bases de datos en su servidor.");
define("LANINS_044", "Base de datos creada con éxito.");
define("LANINS_045", "Por favor, pulse el botón para pasar a la siguiente estapa.");
define("LANINS_046", "5");
define("LANINS_047", "Detalles del Administrador");
define("LANINS_048", "Vuelva al útlima paso");
define("LANINS_049", "Las dos contraseñas no coinciden. Por favor, vuelva e inténtelo de nuevo.");
define("LANINS_050", "Extensión XML");
define("LANINS_051", "Instalada");
define("LANINS_052", "No instalada");
define("LANINS_053", "e107 .700 requiere la extensión PHP XML para ser instalado. Por favor, contacte con su servidor o lea la información en ");
define("LANINS_054", " antes de continuar");
define("LANINS_055", "Confirmación de instalación");
define("LANINS_056", "6");
define("LANINS_057", " e107 ahora tiene toda la información para completar la instalación.

Por favor, pulse el botón para crear la base de datos y guardar sus ajustes.

");
define("LANINS_058", "7");
define("LANINS_060", "Imposible leer el archivo de datos sql

Por favor, asegúrese que <b>core_sql.php</b> existe en el directorio <b>/e107_admin/sql</b>.");
define("LANINS_061", "e107 no pudo crear las tablas necesarias.
Por favor, limpie la base de datos y rectifique cualquier problema antes de probar de nuevo.");
define("LANINS_062", "¡Bienvenido a su nuevo sitio!");
define("LANINS_063", "e107 se ha instalado con éxito y ahora está preparado para ejecutarse.");
define("LANINS_064", "Su sección de administración se encuentra");
define("LANINS_065", "localizada aquí");
define("LANINS_066", "Click para ir ahora. Necesitará conectarse utilizando el nombre y contraseña facilitados durante el proceso de instalación.");
define("LANINS_067", "Encontrará la documentación y FAQ aquí.");
define("LANINS_068", "Gracias por probar e107, esperamos que cumpla sus espectativas y necesidades.\n(Puede borrar este mensaje desde su sección de noticias dentro del Administrador.)\n\n<b>Por favor, tenga en cuenta que esta versión de e107 es beta y no está plenamente preparada para usarse en páginas webs completas.</b>");
define("LANINS_069", "¡e107 Se ha instalado con éxito!

Por razones de seguridad debería fijar los permisos en el archivo <b>e107_config.php</b> a 644.

También debería borrar el directorio e107_install de su servidor después de pulsar el botón siguiente
");
define("LANINS_070", "e107 no pudo guardar la archivo de configuración principal en su servidor.

Asegúrese que el archivo <b>e107_config.php</b> tiene los permisos correctos");
define("LANINS_071", "Finalizando la instalación");
define("LANINS_072", "Nombre Admin");
define("LANINS_073", "Este es el nombre que usará al conectarse. Si lo desea, utilícelo como nombre a mostrar");
define("LANINS_074", "Nombre Admin a mostrar");
define("LANINS_075", "Este es el nombre que se mostrará en su perfil y en todo el sitio. Si desea mostrar el mismo nombre que el nombre de conexión, déjelo en blanco.");
define("LANINS_076", "Contraseña Admin");
define("LANINS_077", "Por favor, indique la contraseña que asignará al administrador");
define("LANINS_078", "Admin Password Confirmation");
define("LANINS_079", "Vuelva a escribir la contraseña para confrimarla");
define("LANINS_080", "Email Admin");
define("LANINS_081", "Escriba su dirección email");

?>