<?php
// Bing-Translated Language file 
// Generated for e107 v2.x by the Multi-Language Plugin
// https://github.com/e107inc/multilan

if (!defined('e107_INIT')) { exit; }

$text = "Des notifications par mail sont envoyées lorsque surviennent des évènements e107.<br /><br />
Par exemple, définir 'IP exclue pour flood du site' sur le groupe 'Administrateur' et tous les administrateurs recevront un mail quand votre site est floodé.<br /><br />
Vous pouvez également, comme autre exemple, définir 'Article posté par un administrateur' sur le groupe 'Membres' et tous vos utilisateurs recevront un email les avertissant de la publication d'un article par un administrateur.<br /><br />
Si vous voulez que les notifications par mail soient envoyées à une adresse mail alternative, sélectionnez l'option 'Email' et renseignez l'adresse email dans le champ prévu.";

$ns -> tablerender("Aide notifications", $text);
