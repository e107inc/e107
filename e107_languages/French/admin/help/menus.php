<?php
/**
 * Fichiers utf-8 français pour le CMS e107 version 0.8 α
 * accessoirement compatible 0.7.11
 * Licence GNU/GPL
 * Traducteurs: communauté française e107 http://etalkers.tuxfamily.org/
 *
 * $Source: /cvsroot/touchatou/e107_french/e107_languages/French/admin/help/menus.php,v $
 * $Revision: 1.8 $
 * $Date: 2008/06/16 15:03:43 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }

if(!defined('e_HTTP')){ die('Accès non autorisé');}
if (!getperms('2')) {
    header('location:'.e_BASE.'index.php');
     exit;
}
global $sql;
if(isset($_POST['reset'])){
        for($mc=1;$mc<=5;$mc++){
            $sql -> db_Select('menus','*', "menu_location='".$mc."' ORDER BY menu_order");
            $count = 1;
            $sql2 = new db;
            while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
                $sql2 -> db_Update('menus', "menu_order='$count' WHERE menu_id='$menu_id' ");
                $count++;
            }
            $text = '<strong>Menus réinitialisés en base de données</strong><br /><br />';
        }
}else{
    unset($text);
}

$text .= 'D\'ici vous pouvez gérer où et dans quel ordre vos menus apparaissent.<br />
Utiliser les listes déroulantes pour déplacer vers le haut ou le bas jusqu\'à ce que vous soyez satisfait de leur positionnement.<br /><br />
Si vous trouvez que les menus ne sont pas mis à jour proprement, cliquez sur le bouton rafraichir.<br />
<form method="post" id="menurefresh" action="'.$_SERVER['PHP_SELF'].'">
<div><input type="submit" class="button" name="reset" value="Rafraichir" /></div>
</form><br />
<div class="indent"><span style="color:red">*</span> les droits de lisibilité des menus ont été modifiés</div>
';

$ns -> tablerender('Aide menus ', $text);
