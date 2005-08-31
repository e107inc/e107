<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_plugins/content/languages/Spanish/lan_content_help.php,v $
|     $Revision: 1.1 $
|     $Date: 2005-08-31 20:10:07 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/
define("CONTENT_ADMIN_HELP_1", "Área de ayuda de la página de contenidos");

define("CONTENT_ADMIN_HELP_ITEM_1", "
<i>Si no ha añadido categorías principales, hágalo en la página <a href='".e_SELF."?type.0.cat.create'>Crear nueva categoría</a>.</i><br /><br />
<b>Categoría</b><br />Seleccione una categoría desde el menú desplegable para gestionar el contenido de esa categoría.<br /><br />
Las categorías principales se mostrarán en negrita y tendrán (TODAS) las extensiones detrás de ellas. Seleccionando una de éstas se mostrarán todos los elementos de esta categoría principal.<br /><br />Para cada categoría principal todas las subcategorías se mostrarán incluyendo la propia categoría principal (éstas mostradas en texto plano). Seleccionando en estas categorías se mostrarán los elementos, solo se mostrarán los elementos de esa categoría.");
define("CONTENT_ADMIN_HELP_ITEM_2", "
<b>Primeras letras</b><br />Si múltiples primeras letras de contenidos existen (cabeceras de contenidos), verá los botones para seleccionar solo los contenidos con esa letra. Seleccionando el botón 'todo' se mostrará una lista conteniendo todos los contenidos en esa categoría.<br /><br />
<b>Lista detallada</b><br />Verá una lista de todos los caontenidos con su id, icono, autor, cabecera [subcabecera] y opciones.<br /><br />
<b>Explicación de iconos</b><br />".CONTENT_ICON_EDIT." : Editar el contenido.<br />".CONTENT_ICON_DELETE." : Eliminar el contenido.<br />");

define("CONTENT_ADMIN_HELP_ITEMEDIT_1", "
<b>Editar formulario</b><br />Puede editar toda la información de este contenido y enviar los cambios.<br /><br />
Si cambia la categoría de este contenido, por favor hágalo antes. Después de seleccionar la categoría correcta, cambie o añada campos existentes, antes de enviar los nuevos cambios.");

define("CONTENT_ADMIN_HELP_ITEMCREATE_1", "<b>Categoría</b><br />Por favor, seleccione una categoría desde la caja de selección para crear su contenido.<br />");
define("CONTENT_ADMIN_HELP_ITEMCREATE_2", "<b>Formulario de creación</b><br />Ahora puede proveer toda la información a este cotenido y enviarlo.<br /><br />Cuidado con el hecho de que diferentes categorías principales pueden tener diferentes juegos de ajustes; diferentes campos pueden estar disponibles. ¡De todas maneras siempre necesitará seleccionar un a categoría primero antes de rellenar los campos!");

define("CONTENT_ADMIN_HELP_CAT_1", "<i>Esta página muestra todas las categorías y subcategorías existentes.</i><br /><br /><b>Lista detallada</b><br />Verá una lista con todas las subcategorías con su id, icono, autor, categoría [subcabecera] y opciones.<br /><br /><b>Explicación de los iconos</b><br />".CONTENT_ICON_USER." : Enlace al perfil del autor<br />".CONTENT_ICON_LINK." : Enlace a la categoría<br />".CONTENT_ICON_EDIT." : Editar la categoría<br />".CONTENT_ICON_DELETE." : Eliminar la categoría<br />");
define("CONTENT_ADMIN_HELP_CAT_2", "<i>Esta página le permite crear una nueva categoría</i><br /><br />¡Siempre escoja una categoría antes de rellenar cualquier campo !<br /><br />Es necesario hacerlo, porque se necesita cargar unas únicas preferencias de categoría en el sistema.<br /><br />Por defecto la página de categorías se muestra para crear una nueva categoría principal.");
define("CONTENT_ADMIN_HELP_CAT_3", "<i>Esta página muestra el formulario para editar la categoría.</i><br /><br /><b>Formulario de edición de categoría</b><br />Ahora puede editar toda la información para esta (sub)categoría y enviar los cambios.<br /><br />Si quiere cambiar la zona de la categoría principal de esta categoría, por favor, hágalo antes. Despues de fijar la correcta categoría, edite todos sus campos.");

define("CONTENT_ADMIN_HELP_ORDER_1", "<i>Esta página muestra todas las categorías y subcategorías existentes.</i><br /><br /><b>LIsta detallada</b><br />Verá el id y nombre de la categoría. También verá algunas opciones para gestionar el orden de las categorías.<br /><br /><b>eExplicación de iconos</b><br />".CONTENT_ICON_USER." : Enlace al perfil del autor<br />".CONTENT_ICON_LINK." : Enlace a la categoría<br />".CONTENT_ICON_ORDERALL." : Gestionar el orden de los contenidos sin tener en cuenta la categoría.<br />".CONTENT_ICON_ORDERCAT." : Gestionar el orden de los contenidos en una categoría específica.<br />".CONTENT_ICON_ORDER_UP." : El botón arriba le permite mover un contenido en ese orden.<br />".CONTENT_ICON_ORDER_DOWN." : El botón abajo le permite mover el contenido en ese orden.<br /><br /><b>Orden</b><br />Aquí puede manualmente fijar el orden de todas las categorías de cada categoría principal. Necesita cambiar los valores en la cajas al orden que desea establecer y pulsar el botón actualizar para enviar los cambios.<br />");
define("CONTENT_ADMIN_HELP_ORDER_2", "<i>Esta página muestra todos los contenidos de las páginas que ha seleccionado.</i><br /><br /><b>Lista detallada</b><br />Verá el id, autor, cabecera y algunas opciones para gestionar el orden de los contenidos.<br /><br /><b>Explicación de iconos</b><br />".CONTENT_ICON_USER." : Enlace al perfil del autor<br />".CONTENT_ICON_LINK." : Enlace al contenido<br />".CONTENT_ICON_ORDER_UP." : El botón arriba le permite mover un contenido en ese orden.<br />".CONTENT_ICON_ORDER_DOWN." : El botón abajo le permite mover el contenido en ese orden.<br /><br /><b>Orden</b><br />Aquí puede fijar manualmente el orden de todas las categorías de esta categoría principal. Necesita cambiar los valores en la cajas para fijar el orden deseado, luego pulsar en actualizar para enviar los cambios.<br />");
define("CONTENT_ADMIN_HELP_ORDER_3", "<i>Esta página muestra todos los contenidos de la categoría principal que ha seleccionado.</i><br /><br /><b>Lista detallada</b><br />Verá el id, autor, cabeceray algunas opciones para gestionar el roden de los contenidos.<br /><br /><b>Explicación de iconos</b><br />".CONTENT_ICON_USER." : Enlace al perfil del autor<br />".CONTENT_ICON_LINK." : Enlace al contenido<br />".CONTENT_ICON_ORDER_UP." : El botón arriba le permite mover un contenido en ese orden.<br />".CONTENT_ICON_ORDER_DOWN." : El botón abajo le permite mover el contenido en ese orden.<br /><br /><b>Orden</b><br />Aquí puede fijar manualmente el orden de todas las categorías de su categoría principal. Necesita cambiar el valor de las cajas para fijar el roden deseado, luego pulsar en actualizar para enviar los cambios.<br />");

define("CONTENT_ADMIN_HELP_OPTION_1", "En esta página puede seleccionar una categoría principal para fijar las opciones, o puede escojer editar las preferencias por defecto.<br /><br /><b>Explicación de los iconos</b><br />".CONTENT_ICON_USER." : Enlace al perfil del autor<br />".CONTENT_ICON_LINK." : enlace a la categoría<br />".CONTENT_ICON_OPTIONS." : Editar las opciones<br /><br /><br />
  	 Las preferencias predeterminadas solo se usan cuando crea una categoría principal. Por lo tanto, cuando crea una nueva categoría principal esas preferencias se almacenarán en ella. Puede cambiarlas asegurándose que las nuevas categorías principales creadas ya tienen este juego de opciones existentes.
  	 <br /><br />
  	 Cada categoría principal tiene su propio juego de opciones, las cuales son únicas para esa categoría principal específica");
/*define("CONTENT_ADMIN_HELP_OPTION_2", "
<i>Esta página muestra las opciones que puede fijar en la categoría principal. Cada categoría principal tiene su propio juego de opciones, por lo que asegúrese de fijarlas correctamente.</i><br /><br />
<b>Valores por defecto</b><br />Por defecto, todos los valores existentes se actualizan en las preferencias cuendo navegue por esta página, pero los puede cambiar según sus estándares<br/><br />
");
*/
define("CONTENT_ADMIN_HELP_MANAGER_1", "En esta página verá una lista de categorías. Puede gestionar el 'Gestor personal de contenidos' para cada categoría pulsando en el icono.<br /><br /><b>Explicación de inconos</b><br />".CONTENT_ICON_USER." : Enlace al perfil del autor<br />".CONTENT_ICON_LINK." : Enlace a la categoría<br />".CONTENT_ICON_CONTENTMANAGER_SMALL." : Editar los gestores personales de contenidos<br />");
define("CONTENT_ADMIN_HELP_MANAGER_2", "<i>En esta página puede asignar usuarios a las categorías seleccionadas</i><br /><br /><b>Gestor personal</b><br />Puede asignar usuarios a ciertas categorías. Haciendo ésto, esos ususarios pueden gestionar su contenido personal de esas categorías fuera las área del administrador (content_manager.php).<br /><br />Asigne usuarios desde la columna de la izquierda pulsando sobre su nombre. Verá como esos nombres se mueven a la columna de la derecha. Después de pulsar el botón de asignación, los usuarios de la columan de la derecha serán asignados a esa categoría.");

define("CONTENT_ADMIN_HELP_SUBMIT_1", "<i>En esta página verá una lista de todos los contenidos que han enviado los usuarios.</i><br /><br /><b>Lista detallada</b><br />Verá una lista de esos contenidos con su id, autor, categoría principal, cabecera [subcabecera] y opciones.<br /><br /><b>Opciones</b><br />Puede enviar o eliminar contenidos usando los botones mostrados.");

define("CONTENT_ADMIN_HELP_OPTION_DIV_1", "this page allows you to set options for the admin create item page.<br /><br />You can define which sections are available when an admin (or personal content manager) creates a new content item<br /><br /><b>custom data tags</b><br />you can allow a user or admin to add optional fields to the content item by using these custom data tags. These optional fields are blank key=>value pairs. For instance: you could add a key field for 'photographer' and provide the value field with 'all photos by me'. Both these key and value fields are empty textfields which will be present in the create form.<br /><br /><b>preset data tags</b><br />apart from the custom data tags, you can provide preset data tags. The difference is that in preset data tags, the key field already is given and the user only needs to provide the value field for the preset. In the same example as above 'photographer' can be predefined, and the user needs to provide 'all photos by me'. You can choose the element type by selecting one option in the selectbox. In the popup window, you can provide all the information for the preset data tag.<br />");
  	 
define("CONTENT_ADMIN_HELP_OPTION_DIV_2", "El envío de opciones afectan sobre el evío de contenidos del ususario.<br /><br />Puede definir que secciones están disponibles para el usuario cuando envío un contenido.<br /><br />".CONTENT_ADMIN_OPT_LAN_11.":<br />".CONTENT_ADMIN_OPT_LAN_12."");
  	 
define("CONTENT_ADMIN_HELP_OPTION_DIV_3", "En la ruta y opciones de temas, puede definir donde se almacenarán las imágenes y archivos.<br /><br />Puede definir que tema se usará como principal. Puede crear temas adicionales copiando y renombrando el directorio  'default' en sus carpetas de templates.<br /><br />Puede definir una estructura de plantilla por defecto para los nuevos contenidos. Puede crear una nueva plantilla creando el archivo content_content_template_XXX.php en su carpeta 'templates/default'. Estas plantillas pueden ser usadas para diferentes contenidos en la categoría principal.<br /><br />");
  	 
define("CONTENT_ADMIN_HELP_OPTION_DIV_4", "Las opciones generales son opciones que se usan a través de páginas de contenidos del plugin gestor de contenidos.");
  	 
define("CONTENT_ADMIN_HELP_OPTION_DIV_5", "Estas opciones afectan sobre el área de los contenidos personales en el área del administrador del gestor de contenidos.<br /><br />".CONTENT_ADMIN_OPT_LAN_63."");
  	 
define("CONTENT_ADMIN_HELP_OPTION_DIV_6", "Estas opciones se usan en el menú para esta categoría principal si la ha activado en el menú.<br /><br />".CONTENT_ADMIN_OPT_LAN_68."<br /><br />".CONTENT_ADMIN_OPT_LAN_118.":<br />".CONTENT_ADMIN_OPT_LAN_119."<br /><br />");
  	 
define("CONTENT_ADMIN_HELP_OPTION_DIV_7", "Las opciones de previsualización de contenidos afectan sobre la pequeña previsualización que se da a cada contenido.<br /><br />Esta previsualización se da en diversas páginas, como la página reciente, la vista de elementos en la página de categorías y en la vista de elementos por autor.<br /><br />".CONTENT_ADMIN_OPT_LAN_68."");
  	 
define("CONTENT_ADMIN_HELP_OPTION_DIV_8", "Las páginas de categorías muestran información de las categorías de contenidos.<br /><br />Existen dos áreas presentes:<br /><br />Página de todas las categorías:<br />Esta página muestra todas las categorías de este principal<br /><br />Ver página de categoría:<br />Esta página muestra la categoría, opcionalmente sus subcategorías y los contenidos presentes en ellas<br />");
  	 
define("CONTENT_ADMIN_HELP_OPTION_DIV_9", "La página de contenidos muestra los contenidos.<br /><br />Puede definir que secciones mostrar activando/desactivando las cajas de selección.<br /><br />Puede mostrar la dirección de correo de un autor no-miembro.<br /><br />Puede sobreescribir los iconos email/imprimir/pdf , el sistema de valoración y los comentarios.<br /><br />".CONTENT_ADMIN_OPT_LAN_74."");
  	 
define("CONTENT_ADMIN_HELP_OPTION_DIV_10", "La página del autor muestra una lista única de autores de los contenidos.<br /><br />Puede definir que seccione smostrar activando/desactivando las cajas de selección.<br /><br />Puede limitar el número de contenidos por página.<br />");
  	 
define("CONTENT_ADMIN_HELP_OPTION_DIV_11", "La página de archivos muestra todos los contenidos.<br /><br />Puede definir que secciones mostrar activando/desactivando las cajas de selección.<br /><br />Puede mostrar las direcciones de correo de los autores no.miembros.<br /><br />Puede limitar el número de elementos a mostrar por página.<br /><br />".CONTENT_ADMIN_OPT_LAN_66."<br /><br />".CONTENT_ADMIN_OPT_LAN_68."");
  	 
define("CONTENT_ADMIN_HELP_OPTION_DIV_12", "La página más valorada muestra los contenidos que han sido valorados por los usuarios.<br /><br />Puede escojer que secciones mostrar activando las cajas de selección.<br /><br />También puede definir si se mostrará la dirección de correo del un autor no-miembro.");
  	 
define("CONTENT_ADMIN_HELP_OPTION_DIV_13", "La página más puntuada muestra todos los contenidos que han sido puntuados por el autor en los contenidos.<br /><br />Puede escojer que secciones mostrar activando las cajas de selección.<br /><br />También puede definir si se mostrará la dirección de correo del un autor no-miembro.");

?>