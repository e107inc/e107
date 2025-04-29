<?php



$mes = e107::getMessage();
$mes->setTitle(defset('LAN_STATUS'), 'info');
echo $mes->render('default','info',false);


