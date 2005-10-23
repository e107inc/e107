<?php
$caption = "Asistenţă forum";
$text = "<b>General</b><br />
Folosiţi acest ecran pentru a vă crea sau edita forumurile<br />
<br />
<b>Părinţi/Forumuri</b><br />
Un părinte este titlul sub care sunt afişate celelalte forumuri, acest lucru făcând afişarea mai simplă şi facilitând navigaţia.
<br /><br />
<b>Accesibilitate</b>
<br />
Puteţi să setaţi forumurile pentru a fi accesibile anumitor vizitatori. Din momentul în care aţi setat o clasă de vizitatori, puteţi bifa clasa pentru a permite doar acelor vizitatori să acceseze forumul. Astfel puteţi seta părinţi sau forumuri individuale.
<br /><br />
<b>Moderatori</b>
<br />
Bifaţi numele administratorilor listaţi pentru a le oferi privilegii de moderator pe forum. Administratorul trebuie să aibă permisiuni de moderare a forumurilor pentru a fi listat aici.
<br /><br />
<b>Ranguri</b>
<br />
Selectaţi rangurile utilizatorilor de aici. Dacă sunt completate câmpurile de imagini, vor fi folosite imagini, pentru a folosi numele rangurilor, introduceţi numele şi asiguraţi-vă că aţi şters câmpul de imagine corespunzător rangului.<br />Bariera e numărul de puncte pe care un utilizator le acumulează înainte de schimbarea nivelului.";
$ns -> tablerender($caption, $text);
unset($text);
?>