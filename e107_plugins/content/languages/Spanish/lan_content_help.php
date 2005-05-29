<?php


//require_once(e_PLUGIN."content/handlers/content_class.php");

define("CONTENT_ADMIN_HELP_LAN_0", "<i>Si no ha creado ninguna categoría principal todavía, hagalo en la página de crear categorías.</i><br /><br /><b>botones principales</b><br />seleccione la categoría principal pulsando el botón.");
define("CONTENT_ADMIN_HELP_LAN_1", "<i>Esta página muestra todos los contenidos de la principal categoría padre que ha seleccionado en la página de contenidos.</i><br />");
define("CONTENT_ADMIN_HELP_LAN_2", "<br /><i>Ha seleccionado una categoría específica para mostrar contenidos.<br />la lista contendrá solo los contenidos de la categoría seleccionada.</i><br />");
define("CONTENT_ADMIN_HELP_LAN_3", "<br /><b>Primeras letras</b><br />Si las letras iniciales de los multiples contenidos de la cabecera de contenido está rpesente, verá los botones solo para aquellos contenidos comenzando con esa letra.<br /><br /><b>lista detallada</b><br />Verá una lista con todos los contenidos y su id, icono, autor, cabecera [subcabecera] y opciones.<br /><br /><b>opciones</b><br />puede editar o eliminar un contenido con los botones mostrados.");
define("CONTENT_ADMIN_HELP_LAN_4", "<i>Las principales categorías padre se mostrarán como botones.</i><br /><br /><b>botones padre principales</b><br /> por favor seleccione una categoría del padre principal para crear su contenido.<br />");
define("CONTENT_ADMIN_HELP_LAN_5", "<i>Esta página muestra el formulario de creación de contenidos</i><br /><br /><b>Crear formulario</b><br />usted puede proporcionar toda la información para el nuevo contenido.<br /><br /><b>bbcode</b><br />con los tags bbcode puede especificar ciertos estilos para partes del texto, insertar enlaces y demás.<br /><br /><b>[newpage=name]</b><br />Con el tag [newpage] puede cortar la información en varias páginas.<br />Uso del tag [newpage]: Si quiere crear múltiples páginas, inserte un tag  [newpage] antes de cada página (¡y no olvide de insertar uno al principio de cada contenido!).<br />El nuevo método [newpage=name] le permite dar un nombre a cada página, que se mostrará en el índice del contenido<br />");
define("CONTENT_ADMIN_HELP_LAN_6", "<i>Esta página muestra el formulario de edición del contenido</i><br /><br /><b>Formulario de edición</b><br />Puede editar toda la información del formulario y enviar los cambios");
define("CONTENT_ADMIN_HELP_LAN_7", "<i>Verá los botones de la principal categoría padre y del formulario de creación </i><br /><br /><b>Padre principal predeterminado</b><br />por defecto, este formulario le permite crear un nuevo padre. (la tira de categorías está vacía)<br /><br /><b>Botones padre principal</b><br />Si quiere crear una subcategoría dentro de una existente en las categorías principales padre, pulse el botón para seleccionar la categoría principal padre.");
define("CONTENT_ADMIN_HELP_LAN_8", "<i>Ha seleccionado la categoría principal padre para crear una subcategoría en</i><br /><br /><b>Tira de categorías</b><br />Verá que la tira de categoría contiene todas la subcategorías existentes de su categoría principal padre</b><br /> Ahora puede añadir toda la información de la nueva categoría.");
define("CONTENT_ADMIN_HELP_LAN_9", "<i>Verá los botones de la categoría principal padre.</i><br /><br /><b>Botones padre principal</b><br />Primero debe pulsar el botón para selecciona la categoría padre principal.");
define("CONTENT_ADMIN_HELP_LAN_10", "
  	 <i>Esta página mostrará todas las categorías de la categoría padre principal que seleccionó en la página anterior.</i><br /><br />
  	 <b>lista detallada</b><br />Verá una lista de todas las subcategorías con su id, icono, autor, categoría [subcabecera] y opciones.<br />
  	 <br />
  	 <b>explicación de los iconos</b><br />
  	 ".CONTENT_ICON_EDIT." : Para todas las categorías haga click en este botón para editarlas.<br />
  	 ".CONTENT_ICON_DELETE." : Para todas las categorías haga click en este botón pata eliminarlas.<br />
  	 ".CONTENT_ICON_OPTIONS." : Solo para la categoría principal (al tope de la lista) haga click en este botón para fijar y controlar las opciones.<br />
  	 ".CONTENT_ICON_CONTENTMANAGER_SMALL." : (Solo Admin del sitio) para cada subcategoría haga click en este botón para manejar el configurador personal de otros administradores.<br />
  	 ");
define("CONTENT_ADMIN_HELP_LAN_11", "<i>Esta página muestra el formulario de edición de la categoría. </i><br /><br /><b>Formulario de edición de categoría</b><br /> Ahora puede añadir toda la información para esta (sub)categoría y enviar los cambios.");
define("CONTENT_ADMIN_HELP_LAN_12", "
  	 <i>Esta página muestra las opciones que puede fijar para este padre principal. Cada padre principal tiene su propio juego de opciones, asi que esté seguro de fijarlas correctamente.</i><br /><br />
  	 <b>Valores por defecto</b><br />Por defecto, todos los valroes están presentes y actualizados en las preferencias cuando Usted mira esta página, pero cambie lo que quiera de sus propios estandares.<br /><br />
  	 <b>División en 8 secciones</b><br />Las opciones están divididas en 8 secciones principales. Verá cada sección en el menú de la derecha. Puede hacer click en ellas e ir al juego específico de opciones para esa sección.<br /><br />
  	 <b>Crear</b><br />En esta sección puede especificar las opciones para la creación de contenidos en las páginas de los Admins.<br /><br />
  	 <b>submit</b><br />En esta sección puede especificar las opciones para el formulario de envíos de contenidos.<br /><br />
  	 <b>ruta y tema</b><br />En esta sección puede fijar un tema para este padre principal, y proveer las direcciones donde tiene almacenadas las imágenes para este padre principal.<br /><br /><b>General</b><br />En esta sección puede especificar las opciones generales para usar a través de todas las páginas de contenidos.<br /><br />
  	 <b>Lista de páginas</b><br />En esta sección puede especificar las opciones de las páginas, donde se listan los contenidos.<br /><br />
  	 <b>Página de categorías</b><br />En esta sección puede especificar las opciones de como mostrar las páginas de categorías.<br /><br />
  	 <b>Página de contenidos</b><br />En esta sección puede especificar las opciones de como mostrar las páginas de contenidos.<br /><br />
  	 <b>Menú</b><br />En esta sección puede especificar las opciones para el menú en el padre principal.<br /><br />
  	 ");
define("CONTENT_ADMIN_HELP_LAN_13", "<i>En esta página puede listar todos los contenidos que se han enviado por los usuarios .</i><br /><br /><b>lista de detalles</b><br />Verá una lista de los contenidos con su id, icono, padre principal, cabecera [subcabecera], autor y opciones.<br /><br /><b>opciones</b><br />Puede enviar o eliminar contenidos usando los botones mostrados.");
define("CONTENT_ADMIN_HELP_LAN_14", "Área de ayuda de configuración de contenidos");
define("CONTENT_ADMIN_HELP_LAN_15", "<br /><br /><b>configuración personal</b><br />Puede asignar administradores a ciertas categorías.Haciendo esto, esos administradores pueden configurar sus contenidos personales fuera del control de la página del administrador de categorías (content_manager.php).");
define("CONTENT_ADMIN_HELP_LAN_16", "<i>en esta página puede asignar administradores a las categorías que ha seleccionado </i><br /><br />Asigne un administrador en la columna de la izquierda pulsando su nombre. Después de pulse el botón de asignación  en la columna derecha para asignarle a esta categoría.");
define("CONTENT_ADMIN_HELP_LAN_17", "<i>Si no ha añadido aún categorías padres principales, por favor cree una nueva página de categorías.</i><br /><br /><b>Botones padre principales</b><br /> Seleccione una categoría padre principal pulsando el botón .");
define("CONTENT_ADMIN_HELP_LAN_18", "
  	 <i>Esta página muestra todas las categorías de la categoría principal padre que seleccionó en la página de ordenación de contenidos.</i><br /><br />
  	 <b>Lista detallada</b><br />Verá la categoría id y su nombre. También verá varias opciones para manejar el orden de las categorías.<br />
  	 <br />
  	 <b>Explicación de los iconos</b><br />
  	 ".CONTENT_ICON_ORDERALL." Manejar el orden global de los contenidos sin contar con la categoría.<br />
  	 ".CONTENT_ICON_ORDERCAT." Manejar el orden de los contenidos de la categoría específicada.<br />
  	 <img src='".e_IMAGE."admin_images/up.png' alt=''> El botón arriba le permite mover el orden del contenido un valor arriba.<br />
  	 <img src='".e_IMAGE."admin_images/down.png' alt=''> El botón abajo le permite mover el orden del contenido un valor abajo.<br />
  	 <br />
  	 <b>order</b><br />Aquí puede manualmente fijar el orden de todas las categorías en el padre principal. Necesita cambiar los valores en las cajas seleccionadas en el orden que desee y pulsar el botón de abajo para guardar el orden.<br />
  	 ");
define("CONTENT_ADMIN_HELP_LAN_19", "
  	 <i>Esta página muestra todos los contenidos de la categoría que ha seleccionado.</i><br /><br />
  	 <b>Lista detallada</b><br />Puede ver el contenido id, autor, y cabecera También puede ver varias opciones para manejar el orden de los contenidos.<br />
  	 <br />
  	 <b>Explicación de los iconos</b><br />
  	 <img src='".e_IMAGE."admin_images/up.png' alt=''> El botón arriba le permite mover el orden del contenido un valor arriba.<br />
  	 <img src='".e_IMAGE."admin_images/down.png' alt=''> El botón abajo le permite mover el orden del contenido un valor abajo.<br />
  	 <br />
  	 <b>Orden</b><br />Aquí puede manualmente fijar el orden de todas las categorías de este padre principal. Necesita cambiar los valores de las cajas de selección para ordenarlos como desee y pulsar el botón de abajo  para guardar el nuevo orden.<br />
  	 ");
define("CONTENT_ADMIN_HELP_LAN_20", "<i>Si no ha añadido una categoría padre principal, por favor, cree una nueva página de categorías.</i><br /><br /><b>Botones padre principal</b><br />seleccione una categoría padre principal usando el botón.<br /><br /><b>3 tipos de ordenación</b><br />hay 3 tipos de ordenación diferentes disponibles, que no interfieren con cada uno.<br /><b>ordenar categoría</b><br />primero debe ordenar las subcategorías del padre principal. (esto se usará al ojear las categorías)<br /><b>ordenarlas en cada categoría </b><br />segundo puede ordenar los contenidos en subcategorías separadas (esto se usará al ojear los contenidos de las categorías)<br /><b>ordenarlos todos en una lista</b><br />tercero puede ordenar todos los contenidos del padre principal(esto se usará en la lista reciente de los contenidos).");

?>