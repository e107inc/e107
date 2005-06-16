<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/content/languages/Spanish/lan_content_help.php,v $
|     $Revision: 1.6 $
|     $Date: 2005-06-16 13:46:23 $
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

define("CONTENT_ADMIN_HELP_MANAGER_1", "En esta página verá una lista de categorías. Puede gestionar el 'Gestor personal de contenidos' para cada categoría pulsando en el icono.<br /><br /><b>Explicación de inconos</b><br />".CONTENT_ICON_USER." : Enlace al perfil del autor<br />".CONTENT_ICON_LINK." : Enlace a la categoría<br />".CONTENT_ICON_CONTENTMANAGER_SMALL." : Editar los gestores personales de contenidos<br />");
define("CONTENT_ADMIN_HELP_MANAGER_2", "<i>En esta página puede asignar usuarios a las categorías seleccionadas</i><br /><br /><b>Gestor personal</b><br />Puede asignar usuarios a ciertas categorías. Haciendo ésto, esos ususarios pueden gestionar su contenido personal de esas categorías fuera las área del administrador (content_manager.php).<br /><br />Asigne usuarios desde la columna de la izquierda pulsando sobre su nombre. Verá como esos nombres se mueven a la columna de la derecha. Después de pulsar el botón de asignación, los usuarios de la columan de la derecha serán asignados a esa categoría.");

define("CONTENT_ADMIN_HELP_SUBMIT_1", "<i>En esta página verá una lista de todos los contenidos que han enviado los usuarios.</i><br /><br /><b>Lista detallada</b><br />Verá una lista de esos contenidos con su id, autor, categoría principal, cabecera [subcabecera] y opciones.<br /><br /><b>Opciones</b><br />Puede enviar o eliminar contenidos usando los botones mostrados.");

?>