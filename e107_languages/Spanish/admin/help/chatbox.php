<?php

$text = "Configura las preferencias de tu Chatbox desde aquí.<br />
Si la casilla de reemplazo está marcada, cualquier enlace que hayan introducido será reemplazado 
con el texto que escribiste en la caja de texto, 
esto detendrá enlaces largos que causan problemas al mostrar. 
Cortar palabras autocortará los textos que sean más largos que el número especificado aquí.";

$ns -> tablerender("Chatbox", $text);
?>