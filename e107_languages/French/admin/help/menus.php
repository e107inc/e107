<?php
/*
+---------------------------------------------------------------+
| Fichiers de langage Français e107 CMS (utf-8). License GNU/PGL
| Traducteurs: communauté française e107
|     $Source: /cvs_backup/e107_langpacks/e107_languages/French/admin/help/menus.php,v $
|     $Revision: 1.1 $
|     $Date: 2006-04-08 19:49:11 $
|     $Author: daddycool78 $
+---------------------------------------------------------------+
*/
  if(!defined('e_HTTP')){ die("Accès Non Autorisé");}
  if (!getperms("2")) {
      header("location:".e_BASE."index.php");
       exit;
  }
  global $sql;
  if(isset($_POST['reset'])){
          for($mc=1;$mc<=5;$mc++){
              $sql -> db_Select("menus","*", "menu_location='".$mc."' ORDER BY menu_order");
              $count = 1;
              $sql2 = new db;
              while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
                  $sql2 -> db_Update("menus", "menu_order='$count' WHERE menu_id='$menu_id' ");
                  $count++;
              }
              $text = "<strong>Menus réinitialisés en base de données</strong><br /><br />";
          }
  }else{
      unset($text);
  }
  
  $text .= "
  Vous pouvez gérer ou et dans quel ordre vos menus apparaîtront depuis cette section. 
  Utiliser les listes déroulante pour déplacer vers le haut ou le bas jusqu'a ce que vous soyez satisfait de leur positionnement.
  <br />
  <br />
  Si vous trouvez que les menus ne sont pas mis à jour proprement cliquez sur le bouton rafraîchir.
  <br />
  <form method='post' id='menurefresh' action='".$_SERVER['PHP_SELF']."'>
  <div><input type='submit' class='button' name='reset' value='Rafraîchir' /></div>
  </form>
  <br />
  <div class='indent'><span style='color:red'>*</span> les droits de lisibilité des menus ont été modifiés</div>
  ";
  
  $ns -> tablerender("Aide Menus ", $text);
  ?>
