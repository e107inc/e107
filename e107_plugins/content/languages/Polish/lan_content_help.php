<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/e107_plugins/content/languages/Polish/lan_content_help.php,v $
|     $Revision: 1.6 $
|     $Date: 2005-06-25 21:08:29 $
|     $Author: jacek_c $
+----------------------------------------------------------------------------+
*/

define("CONTENT_ADMIN_HELP_1", "Pomoc dla Content Management");

define("CONTENT_ADMIN_HELP_ITEM_1", "<i>jeżeli nie dodałeś jeszcze głównych nadrzędnych kategorii, zrób to teraz <a href='".e_SELF."?cat.create'>Utwórz Nową Kategorię</a>.</i><br /><br />
<b>kategorie</b><br />wybierz kategorię z rozwijanej listy menu ,aby zarządzać zawartością w tej kategorii.<br /><br />wybierając główną nadrzędną z rozwijanej listy menu,zobaczysz wszystkie pozycje zawartości w danej głównej kategorii.<br />Wybierając podkategorie, zobaczysz tylko te pozycje które są dopisane do danej podkategorii.<br /><br />Możesz również, użyć menu po prawej stronie aby mieć wgląd na wszystkie pozycje zawartości w okreslonej kategorii.");

define("CONTENT_ADMIN_HELP_ITEM_2", "<b>pierwsze litery</b><br />Jeżeli złożone pozycje zawartości rozpoczynają się z nagłówka zawartości, zobaczysz buttony do wybrania tylko tych pozycji zawartości rozpoczynających się od tej litery.Wybierając 'wszystkie' zobaczysz listę zawierającą wszystkie pozycje zawartości w danej kategorii.<br /><br /><b>szczegółowy spis</b><br />Tutaj widzisz listę wszystkich pozycji zawartości wraz z ich: id,ikonami,autorami,nagłówkami(podtytułami) oraz opcjami.<br /><br /><b>wyjaśnienie ikon</b><br />".CONTENT_ICON_USER." : link do profilu autora<br />".CONTENT_ICON_LINK." : link do pozycji zawartości<br />".CONTENT_ICON_EDIT." : edycja pozycji zawartości<br />".CONTENT_ICON_DELETE." : usunięcie pozycji zawartości<br />");

define("CONTENT_ADMIN_HELP_ITEMEDIT_1", "<b>formularz edycji</b><br />możesz teraz edytować całą informację dla tej pozycji zawartości oraz zapisać zmiany.<br /><br />Jeżeli musisz zmienić kategorię dla tej pozycji zawartości, to zrób to najpierw.Po tym jak wybrałeś właściwą kategorię,zmień lub dodaj wszystkie obecne pola,zanim zapiszesz zmiany.");

define("CONTENT_ADMIN_HELP_ITEMCREATE_1", "<b>kategorie</b><br />wybierz kategorię z pola wyboru aby utworzyć pozycję zawartości.<br />");

define("CONTENT_ADMIN_HELP_ITEMCREATE_2", "<b>formularz tworzenia</b><br />możesz teraz dostarczać wszystkie informacje dla tej pozycji zawartości oraz wysłać je.<br /><br />Bądź świadomy faktu że różne nadrzędne główne kategorie mogą mieć różne ustawienia pierszeństwa; różne pola mogą być dostępne dla ciebie do wypełnienia.Ponadto zawsze możesz wybrać kategorię najpierw zanim zaczniesz wypełniać pola.!");

define("CONTENT_ADMIN_HELP_CAT_1", "<i>ta strona pokazuje wszystkie kategorie oraz podkategorie.</i><br /><br /><b>szczegółowy spis</b><br />Tutaj widzisz listę wszystkich podkategorii wraz z ich : id,ikanami,autorami,kategoriami(podtutułami) oraz opcjami.<br /><br /><b>wyjaśnienie ikon</b><br />".CONTENT_ICON_USER." : link do profilu autora<br />".CONTENT_ICON_LINK." : link do kategorii<br />".CONTENT_ICON_EDIT." : edycja kategorii<br />".CONTENT_ICON_DELETE." : usunięcie>kategorii<br />");

define("CONTENT_ADMIN_HELP_CAT_2", "<i>na tej stronie możesz tworzyć nowe kategorie</i><br /><br />Zawsze wybieraj nadrzędną kategorię zanim wypełnisz inne pola !<br /><br />To musi być zrobione ,ponieważ niektóre unikalne kategorie muszą być załadowane do systemu.<br /><br />Standardowa pokazywana jest strona kategorii, aby utworzyć nową główna kategorię.");

define("CONTENT_ADMIN_HELP_CAT_3", "<i>ta strona pokazuje formularz edycji kategorii.</i><br /><br /><b>formularz edycji kategorii</b><br />tutaj możesz edytować wszystkie informacje dla tej kategorii/podkategorii oraz zapisać zmiany.<br /><br />Jeżeli chcesz zmienić nadrzędną lokalizację dla tej kategorii,zrób to najpierw. Po tym jak ustawiłeś właściwą kategorię edytuj wszystkie pozostałe pola.");

define("CONTENT_ADMIN_HELP_ORDER_1", "<i>ta strona pokazuje wszystkie występujące kategorie oraz podkategorie.</i><br /><br /><b>szczegółowy spis</b><br />widzisz id kategorii oraz jej nazwę. Widzisz również kilkanaście opcji do zarządzania kolejnościami kategorii.<br /><br /><b>wyjaśnienie ikon</b><br />".CONTENT_ICON_USER." : link do profilu autora<br />".CONTENT_ICON_LINK." : link do kategorii<br />".CONTENT_ICON_ORDERALL." : ogólne zarządzanie kolejnościami pozycji kategorii bez względu na kategorie.<br />".CONTENT_ICON_ORDERCAT." : zarządzanie kolejnościami pozycji zawartości w określonej kategorii.<br />".CONTENT_ICON_ORDER_UP." : button do góry pozwala przesunąć pozycję zawartości w kolejności o jeden w górę.<br />".CONTENT_ICON_ORDER_DOWN." : button do dół pozwala przesunąć pozycję zawartości w kolejności o jeden w dół.<br /><br /><b>kolejność</b><br />tutaj możesz ręcznie ustawić kolejność wszystkich kategorii.Musisz zmienić wartości w polach zaznaczenia na tobie odpowiadające a następnie zapisać zmiany przez kliknięcie buttona poniżej.<br />");

define("CONTENT_ADMIN_HELP_ORDER_2", "<i>ta strona pokazuje wszystkie pozycje zawartości z zaznaczonej kategorii.</i><br /><br /><b>szczegółowy spis</b><br />tutaj widzisz : id zawartości,autora zawartości oraz nagłówek zawartości. Również widzisz kilkanaście opcji do zarządzania kolejnościami pozycji zawartości.<br /><br /><b>wyjaśnienie ikon</b><br />".CONTENT_ICON_USER." : link do profilu autora<br />".CONTENT_ICON_LINK." : link do pozycji zawartości<br />".CONTENT_ICON_ORDER_UP." : button do góry pozwala przesunąć pozycję zawartości w kolejności o jeden w górę<br />".CONTENT_ICON_ORDER_DOWN." : button do dół pozwala przesunąć pozycję zawartości w kolejności o jeden w dół.<br /><br /><b>kolejność</b><br />tutaj możesz ręcznie ustawić kolejność wszystkich kategorii dla tej głównej nadrzędnej. Musisz zmienić wartości w polach zaznaczenia na tobie odpowiadające a następnie zapisać zmiany przez kliknięcie buttona poniżej.<br />");

define("CONTENT_ADMIN_HELP_ORDER_3", "<i>ta strona pokazuje wszystkie pozycje zawartości z głównej nadrzędnej kategorii ,którą zaznaczyłeś.</i><br /><br /><b>szczegółowy spis</b><br />tutaj widzisz : id zawartości,autora zawartości oraz nagłówek zawartości. Również widzisz kilkanaście opcji do zarządzania kolejnościami pozycji zawartości.<br /><br /><b>wyjaśnienie ikon</b><br />".CONTENT_ICON_USER." : link do profilu autora<br />".CONTENT_ICON_LINK." : link do pozycji zawartości<br />".CONTENT_ICON_ORDER_UP." : button do góry pozwala przesunąć pozycję zawartości w kolejności o jeden w górę.<br />".CONTENT_ICON_ORDER_DOWN." : button do dół pozwala przesunąć pozycję zawartości w kolejności o jeden w dół.<br /><br /><b>kolejność</b><br />tutaj możesz ręcznie ustawić kolejność wszystkich kategorii dla tej głównej nadrzędnej. Musisz zmienić wartości w polach zaznaczenia na tobie odpowiadające a następnie zapisać zmiany przez kliknięcie buttona poniżej.<br />");

define("CONTENT_ADMIN_HELP_OPTION_1", "Na tej stronie możesz wybrać główną nadrzędną kategorię aby ustawić opcje, lub możesz wybrać domyślne ustawienia w celu ich edycji.<br /><br /><b>wyjaśnienie ikon</b><br />".CONTENT_ICON_USER." : link do profilu autora<br />".CONTENT_ICON_LINK." : link do kategorii<br />".CONTENT_ICON_OPTIONS." : edycja opcji<br />");

define("CONTENT_ADMIN_HELP_OPTION_2", "<i>ta strona pokazuje opcje ,które możesz ustawić dla tej głównej nadrzędnej. Każda główna nadrzędna ,ma własny sprecyzowany komplet opcji,jeżeli zmieniasz ustawienia to bądź pewien że odpowiednio je ustawiasz.</i><br /><br />
<b>ustawienia domyślne</b><br />Gdy przeglądasz tę stronę to wszystkie wszystkie obecne wartości są ustawione domyślnie oraz zapisane w ustawieniach, lecz możesz zmienić icj ustawienia na własne.<br /><br />
<b>podział do osiem segmentów</b><br />opcje są podzielone na osiem głównych sekcji. W prawym menu widzisz różne sekcje. Po kliknięciu , zostaniesz przeniesiony do określonych ustawień dla tej sekcji.<br /><br />
<b>tworzenie</b><br />w tej sekcji możesz wyszczególnić opcje dla tworzonych pozycji zawartości na stronach administracyjnych.<br /><br />
<b>wyślij</b><br />w tej sekcji możesz wyszczególnić opcje dla formularza wysyłającego pozycje zawartości.<br /><br />
<b>ścieżka i wygląd</b><br />w tej sekcji możesz ustawić wygląd graficzny (temat) dla głównej nadrzędnej, oraz określić lokalizację do zapisanych grafik dla głównej nadrzędnej.<br /><br /><b>ogólne</b><br />w tej sekcji możesz określić ogólne opcje do użycia na wszystkich stronach zawartości.<br /><br />
<b>lista stron</b><br />w tej sekcji możesz wyszczególnić opcje na stronach, na których pozycje zawartości będą pokazane.<br /><br />
<b>strony kategorii</b><br />w tej sekcji możesz wyszczególnić w jaki sposób pokazywać strony kategorii.<br /><br />
<b>strony zawartości</b><br />w tej sekcji możesz wyszczególnić w jaki sposób pokazywać pozycje zawartości.<br /><br />
<b>menu</b><br />w tej sekcji możesz wyszczególnić opcje dla menu dla głównych nadrzędnych.<br /><br />
");

define("CONTENT_ADMIN_HELP_MANAGER_1", "Na tej stronie widzisz listę wszystkich kategorii. Możesz zarządzać 'personal content manager' przez kliknięcie w ikonę.<br /><br /><b>wyjaśnienie ikon</b><br />".CONTENT_ICON_USER." : link do profilu autora<br />".CONTENT_ICON_LINK." : link do kategorii<br />".CONTENT_ICON_CONTENTMANAGER_SMALL." : edycja personal content managers<br />");

define("CONTENT_ADMIN_HELP_MANAGER_2", "<i>na tej stronie możesz,przez kliknięcie ,przydzielić użytkowników dla zaznaczonej kategorii</i><br /><br /><b>personal manager</b><br />możesz przydzielić użytkowników do pewnych kategorii. Użytkownicy mogą zarządzać swoimi własnymi pozycjami zawartości w kategorii z panelu admina (content_manager.php).<br /><br />Przydziel użytkowników widocznych w lewej kolumnie klikając w ich nazwę. Zobaczysz ich nazwy przeniesione do prawej kolumny. Po kliknięciu w button przydziału, użytkownicy w prawym menu zostaną przydzieleni do tej kategorii.");

define("CONTENT_ADMIN_HELP_SUBMIT_1", "<i>Na tej stronie widzisz listę wszystkich pozycji zawartości wysłanych przez użytkowników.</i><br /><br /><b>szczegółowy spis</b><br />Widzisz listę tych pozycji zawartości wraz z ich :id,ikonami,głównymi nadrzędnymi,nagłówkami (podtytułami) ,autorami oraz opcjami.<br /><br /><b>opcje</b><br />możesz pisać lub kasować pozycje zawartości używając pokazanych buttonów.");

?>
