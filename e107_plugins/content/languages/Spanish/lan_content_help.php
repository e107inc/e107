<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/content/languages/Spanish/lan_content_help.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-06-03 22:34:18 $
|     $Author: natxocc $
+----------------------------------------------------------------------------+
*/
define("CONTENT_ADMIN_HELP_ORDER_1", "
<i>Esta página muestra todas las categorías y subcategorías existentes.</i><br /><br />
<b>Lista detallada</b><br />Verá el Id de la categoría y su nombre. También verá algunas opciones para gestionar el orden de las categorías.<br />
<br />
<b>Explicación de iconos</b><br />
".CONTENT_ICON_ORDERALL." Gestiona el orden global de contenidos sin contar con la categoría.<br />
".CONTENT_ICON_ORDERCAT." Gestiona el orden de los contenidos de una categoría específica.<br />
<img src='".e_IMAGE."admin_images/up.png' alt='' /> El botón arriba le permite subir un escalon el orden de contenido.<br />
<img src='".e_IMAGE."admin_images/down.png' alt='' /> El botón abajo le permite bajar un escalón el orden del contenido.<br />
<br />
<b>Orden</b><br />Aquí puede fijar el orden de todas las categorías de cada padre manualmente. Necesita cambiar los valores de las cajas al orden que desea y pulsar en actualizar para guardar en nuevo orden.<br />
");
define("CONTENT_ADMIN_HELP_ORDER_2", "
<i>Esta página muestra todos los contenidos de la categoría seleccionada.</i><br /><br />
<b>detailed list</b><br />Verá el ID de contenido, el autor y su cabecera. También verá algunas opciones para gestionar el orden de los contenidos.<br />
<br />
<b>Explicación de iconos</b><br />
<img src='".e_IMAGE."admin_images/up.png' alt='' /> El botón arriba le permite subir un escalon el orden de contenido.<br />
<img src='".e_IMAGE."admin_images/down.png' alt='' /> El botón abajo le permite bajar un escalón el orden del contenido.<br />
<br />
<b>Orden</b><br />Aquí puede fijar el orden de todas las categorías de cada padre manualmente. Necesita cambiar los valores de las cajas al orden que desea y pulsar en actualizar para guardar en nuevo orden.<br />
");
define("CONTENT_ADMIN_HELP_ORDER_3", "
<i>Esta página muestra todos los contenidos de la categoría seleccionada.</i><br /><br />
<b>Lista detallada</b><br />Verá el ID de contenido, el autor y su cabecera. También verá algunas opciones para gestionar el orden de los contenidos.<br />
<br />
<b>Explicación de iconos</b><br />
<img src='".e_IMAGE."admin_images/up.png' alt='' /> El botón arriba le permite subir un escalon el orden de contenido.<br />
<img src='".e_IMAGE."admin_images/down.png' alt='' /> El botón abajo le permite bajar un escalón el orden del contenido.<br />
<br />
<b>Orden</b><br />Aquí puede fijar el orden de todas las categorías de cada padre manualmente. Necesita cambiar los valores de las cajas al orden que desea y pulsar en actualizar para guardar en nuevo orden.<br />
");



define("CONTENT_ADMIN_HELP_SUBMIT_1", "
<i>En esta página verá una lista de contenidos enviados por los ususarios.</i><br /><br />
<b>Lista detallada</b><br />Verá una lista de estos contenidos con su ID, icono, padre principal, cabecera [subcabecera], autor y opciones.<br /><br />
<b>Opciones</b><br />Puede enviar o eliminar contenido usando los botones mostrados.
");



define("CONTENT_ADMIN_HELP_CAT_1", "
<i>Esta página muestra todas las categorías y subcategorías existentes.</i><br /><br />
<b>Lista detallada</b><br />Verá una lista de subcategorías con su ID, autor, icono, categoría [subcabecera] y opciones.<br />
<br />
<b>Explicación de los iconos</b><br />
".CONTENT_ICON_EDIT." : para todas las categorías puede pulsar el botón para editar la categoría.<br />
".CONTENT_ICON_DELETE." : para todas las categorías puede pulsar el botón para eliminar la categoría.<br />
".CONTENT_ICON_OPTIONS." : solo para la categoría principal (arriba en la lista) puede pulsar este botón para fijar y controlar todas las opciones.<br />
");
define("CONTENT_ADMIN_HELP_CAT_2", "
".CONTENT_ICON_CONTENTMANAGER_SMALL." : (solo sitio admin) para cada subcategoría puede pulsar este botón para gestionar el perfil de otros administradores.<br />
<br />
<b>Gestiones personales</b><br />Puede asiganr administradores a ciertas categorías. Haciendo ésto, esos administradores pueden gestionar sus propios contenidos fueras de la página del administrador (content_manager.php).
");
define("CONTENT_ADMIN_HELP_CAT_3", "
<i>Esta página le permite crear una nueva categoría</i><br /><br />
¡ESCOJA SIEMPRE UN PADRE ANTES DE RELLENAR OTROS CAMPOS!<br /><br />
Esto debe hacerse, porque debe cargarse en el sistema una categoría única de preferencias.
");
define("CONTENT_ADMIN_HELP_CAT_4", "
<i>Esta página muestra el formulario de edici´n de la categoría.</i><br /><br />
<b>Formulario de edición de categoría</b><br /> Usted puede editar toda la información de esta (sub)categoría y enviar sus cambios.
");
define("CONTENT_ADMIN_HELP_CAT_5", "
");
define("CONTENT_ADMIN_HELP_CAT_6", "
<i>Esta página muestra las opciones que puede fijar en el padre principal. Cada padre principal tiene su juego de opciones, asegurese de fijarlas correctamente.</i><br /><br />
<b>Valores por defecto</b><br />Por defecto todos los valores están presentes y actualizados en las preferencias que se muestran en la página, pero cambie algún parámetro a sus estandares.<br /><br />
<b>División en 8 secciones</b><br />Las opciones están divididas en 8 secciones principales. Verá las diferencias entre las secciones en el menúde la derecha. Haga click en ellas para ir al juego de cada sección.<br /><br />
<b>Crear</b><br />iEn esta sección puede especificar las opciones para la creación de contenidos en las páginas del administrador.<br /><br />
<b>Ruta y tema</b><br />En esta sección puede fijar un tema para este padre principal, y proveer rotas donde almacenará sus imágenes para este padre principal.<br /><br /><b>General</b><br />En esta sección puede especificar opciones generales para usar a través de las páginas de contenidos.<br /><br />
<b>Lista de páginas</b><br />En esta sección puede especificar opciones en las páginas, donde se listarán los contenidos.<br /><br />
<b>Páginas de categorías</b><br />En esta sección puede especificar opciones sobre como mostrar las páginas de categorías.<br /><br />
<b>Páginas de contenidos</b><br />En esta sección puede especificar opciones sobre como mostrar los contenidos en la página.<br /><br />
<b>Menú</b><br />En esta sección puede especificar opciones para el menú de sete padre principal.<br /><br />
");
define("CONTENT_ADMIN_HELP_CAT_7", "
<i>En esta página puede asignar administradores a las categorías seleccionadas</i><br /><br />
Asigne administradores desde la columna izquierda pulsando sobre su nombre. Verá que los nombres se mueven a la columna derecha. Despues de pulsar sobre los administradores de la derecha se asignarán a esta categoría.
");

define("CONTENT_ADMIN_HELP_ITEMCREATE_1", "
<b>Categoría</b><br />Seleccione una categoría desde la caja de selección para crear contenidos en ella.<br />
");
define("CONTENT_ADMIN_HELP_ITEMCREATE_2", "
¡Siempre seleccione una categoría antes de compretar otros campos!<br />
Es necesarioa hacer esto, porque cada padre principal de la categoría (y subcategorías en ella) pueden tener diferentes ajustes.<br /><br />
<b>Formulario de creación</b><br />Usted puede proveer toda la información para este contenido y enviarla.<br /><br />
Tenga en cuenta que diferentes categorías padres principales pueden tener diferente juego de ajustes, y pueden haber más campos disponibles para rellenar.
");


define("CONTENT_ADMIN_HELP_ITEM_1", "
<i>Si no ha añadido categorías padres principales, hágalo en la página <a href='".e_SELF."?type.0.cat.create'>Crear nueva categoría</a>.</i><br /><br />
<b>Categoría</b><br />Seleccione una categoría desde el menú desplegable para gestionar el contenido de esa categoría.<br /><br />
Los padres principales se mostrarán en negrita y tendrán (TODAS) las extensiones detrás de ellas. Seleccionando una deéstas se mostrarán todos los elementos de este padre principal.<br /><br />
Para cada padre principal todas las subcategorías se mostrarán incluyendo la propia categoría padre principal(estas mostraras en texto plano). Seleccionando solo en estas categorías se mostrarán los elementos, solo se mostrarán los elementos de esa categoría.
");
define("CONTENT_ADMIN_HELP_ITEM_2", "
<b>Primeras letras</b><br />Si múltiples primeras letras de contenidos existen(cabeceras de contenidos), verá los botones para seleccionar solo los contenidos con esa letra. Seleccionando el botón 'todo' se mostrará una lista conteniendo todos los contenidos en esa categoría.<br /><br />
<b>Lista detallada</b><br />Verá una lista de todos los caontenidos con su id, icono, autor, cabecera [subcabecera] y opciones.<br /><br />
<b>Explicación de iconos</b><br />
".CONTENT_ICON_EDIT." : Editar el contenido.<br />
".CONTENT_ICON_DELETE." : Eliminar el contenido.<br />
");


define("CONTENT_ADMIN_HELP_ITEMEDIT_1", "
<b>Editar formulario</b><br />Puede editar toda la información de este contenido y enviar los cambios.<br /><br />
Si cambia la categoría de este contenido a otro padre principal, probeblemente quiera re-editar este elemento después de cambiar la categoría.<br />Because you change the main parent category other preferences may be available to fill in.
");

define("CONTENT_ADMIN_HELP_1", "Area de ayuda de la gestión de contenidos");


define("CONTENT_ADMIN_HELP_ITEM_LETTERS", "Abajo verá letras distintivas de cabeceras de contenidos de esta categoría.<br />Pulsando en una de las letras, verá una lista de elementos comenzando poe la misma. También puede escoger el botón TODO para mostrar todos los elementos de esta categoría.");


?>