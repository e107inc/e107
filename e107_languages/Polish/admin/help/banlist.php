<?php
$caption = "Zablokowanie użytkowników na twojej stronie";
$text = "Z tego miejsca możesz blokować użytkowników.<br />
Wprowadź pełny adres IP lub wstaw gwiazdkę zastępując ciąg znaków aby zablokować grupę adresów IP. Możesz również wpisać adres email zarejestrowanego użytkownika na twojej stronie, który zostanie zablokowany.<br /><br />
<b>Blokowanie poprzez adres IP:</b><br />
Wpisując adres IP: 123.123.123.123 zostanie zablokowany dostęp do strony dla użytkownika o takim adresie.<br />
Wpisując adres IP: 123.123.123.* zostanie zablokowany dostęp do strony dla wszystkich użytkowników o taki początkowym adresie.<br /><br />
<b>Blokowanie poprzez adres email</b><br />
Wpisując adres email foo@bar.com zablokujesz dostęp do strony dla każdego użytkownika używające tego adresu email.<br />
Wpisując adres email *@bar.com zablokujesz dostęp do strony dla użytkowników mających adres email z jednej domeny.
";
$ns -> tablerender($caption, $text);
?>