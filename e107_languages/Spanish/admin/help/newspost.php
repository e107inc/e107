<?php
$caption = "Ayuda de mensaje Noticias";
$text = "<b>General</b><br />
El cuerpo será desplegado en la página principal, 
el texto completo será leído pulsando en 'Leer más'. 
Recurso y URL es donde está publicada la noticia.
<br />
<br />
<b>Atajos</b><br />
Puede usar estos atajos en vez de escribir toda la etiqueta , 
al enviar la noticia el atajo será convertido en código xhtml.
<br /><br />
<b>Enlaces</b>
<br />
Por favor use direcciones completas a cualquier enlace aún si es local.
<br /><br />
<b>Mostrar solo Título</b>
<br />
Permite mostrar solamente el título de la noticia con un enlace para leer la noticia completa.
<br /><br />
<b>Estado</b>
<br />
Si pulsa el botón para deshabilitar la noticia no será mostrada en tu página principal.
<br /><br />
<b>Activación</b>
<br />
Si configura una fecha de inicio/fin tu noticia solo será mostrada entre estas fechas.
";
$ns -> tablerender($caption, $text);
?>