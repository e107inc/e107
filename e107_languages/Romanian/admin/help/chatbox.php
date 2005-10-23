<?php

$text = "Setaţi preferinţele pentru chatbox aici.<br />Dacă e bifată caseta de înlocuire a linkurilor, orice link introdus va fi înlocuit de textul pe care l-aţi introdus în caseta text, oprind problemele de afişare cauzate de linkuri prea lungi. Încadrarea cuvintelor va segmenta textul care e mai lung decât valoarea specificată aici.";

$ns -> tablerender("Chatbox", $text);
?>