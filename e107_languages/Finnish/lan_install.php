<?php

/*
Finnish Translation File
Author: Mika Lehtinen
Email: mika.lehtinen@oulu.fi
*/

define("INSLAN1", "Asennusvaihe");
define("INSLAN2", "PHP / mySQL -versioiden sekä tiedosto-oikeuksien tarkistus");
define("INSLAN3", "PHP-versio");
define("INSLAN4", "Hylätty");
define("INSLAN5", "<b>Käyttämäsi PHP:n versio ei ole yhteensopiva e107:n kanssa.<br />(e107 vaatii vähintään version 4.1.0).</b><br /><br />Jos käytät paikallista palvelinta omalla koneellasi, sinun täytyy päivittää<br />PHP uudempaan versioon jatkaaksesi, lisäohjeita löydät <a href='http://php.net'>php.net</a>:stä. Mikäli yrität<br />asentaa e107:n muulle palvelimelle, ota yhteyttä<br />palvelimen ylläpitäjään ja pyydä heitä päivittämään PHP.<br />Aja tämä skripti uudelleen PHP:n päivityksen jälkeen.");
define("INSLAN6", "Skripti pysäytetty.");
define("INSLAN7", "Hyväksytty");
define("INSLAN8", "mySQL-versio");
define("INSLAN9", "<b>e107 ei kyennyt määrittämään MySQL:n versiota.</b><br /><br />Tämä voi tarkoittaa että MySQL ei ole asennettu, se ei ole käynnissä tai<br />käyttämäsi versio ei ilmoita oikein versionumeroaan<br />(tunnettu ongelma versiossa v5.x ). Jos asennuksen seuraava vaihe epäonnistuu,<br />tarkista MySQL-tilanne.");
define("INSLAN10", "Tiedostojen oikeudet");
define("INSLAN11", "kirjoitus estetty");
define("INSLAN12", "hakemistoihin kirjoitus estetty");
define("INSLAN13", "Ole hyvä ja varmista että yllämainittujen kirjoitusoikeudet on asetettu<br />oikein palvelimellasi. Oikeuksien pitäisi olla 777. <br />Muuttaaksesi oikeudet, klikkaa tiedostoa oikealla napilla FTP-ohjelmassasi ja valitse Chmod tai<br />Set File Permissions, avautuvaan ikkunaan syötä 777 tai jos valittavana on vain rasteja,<br />rastita kaikki.<br /><br />Suorita testi uudestaan muutettuasi oikeudet.");
define("INSLAN14", "e107 asentuu");
define("INSLAN15", "KRIITTINEN VIRHE");
define("INSLAN16", "Vaikka MySQL:n versiomääritys ei onnistunut, jatka seuraavaan vaiheeseen.");
define("INSLAN17", "Jatka");
define("INSLAN18", "Testaa tiedosto-oikeudet uudestaan");
define("INSLAN19", "Kaikki palvelintestit suoritettu hyväksytysti, klikkaa nappia jatkaaksesi");
define("INSLAN20", "mySQL info");
define("INSLAN21", "Syötä MySQL asetuksesi tähän.<br />Jos sinulla on root-oikeudet, voit luoda uuden tietokannan valitsemalla oikean rastin. Jos et ole,<br />sinun täytyy luoda tietokanta tai käyttää vanhaa kantaa. <br />Jos sinulla on vain yksi tietokanta käytössäsi, valitse etuliite jotta muutkin skriptit voivat käyttää samaa kantaa.<br />Jos et tiedä MySQL tietojasi, ota yhteys palveluntarjoajaasi.");
define("INSLAN22", "MySQL-palvelin");
define("INSLAN23", "MySQL-käyttäjätunnus");
define("INSLAN24", "MySQL-salasana");
define("INSLAN25", "MySQL-tietokanta");
define("INSLAN26", "Tiedostojen etuliite");
define("INSLAN27", "Virhe");
define("INSLAN28", "Tapahtui virhe");
define("INSLAN29", "Jätit vaaditun kentän tyhjäksi, syötä MySQL-tietosi uudelleen");
define("INSLAN30", "e107 ei saanut yhteyttä MySQL-palvelimeen antamillasi tiedoilla.<br />Ole hyvä ja palaa edelliselle sivulle tarkistamaan ovatko syöttämäsi tiedot oikein.");
define("INSLAN31", "MySQL-tarkistus");
define("INSLAN32", "Yhteys MySQL-palvelimeen saatu ja varmistettu.");
define("INSLAN33", "Yritetään luoda tietokanta");
define("INSLAN34", "Tietokannan luonti epäonnistui, varmista että sinulla on oikeudet tietokannan luomiseen palvelimella.");
define("INSLAN35", "Tietokanta luotu.");
define("INSLAN36", "Klikkaa nappia jatkaaksesi.");


define("INSLAN37", "Edelliselle sivulle");
define("INSLAN38", "Ylläpitäjän tiedot");
define("INSLAN39", "Syötä ylläpitäjän tunnus, salasana ja sähköposti.<br />Näitä tietoja tarvitaan sivuston ylläpitoalueelle pääsemiseen.<br />Pidä tunnus ja salasana turvassa, sillä niitä ei voida palauttaa mikäli unohdat ne.");
define("INSLAN40", "Ylläpitäjän tunnus");
define("INSLAN41", "Ylläpitäjän salasana");
define("INSLAN42", "Vahvista salasana");
define("INSLAN43", "Ylläpitäjän sähköposti");
define("INSLAN44", "Jätit vaaditun kentän tyhjäksi, syötä tiedot uudelleen.");
define("INSLAN45", "Salasanat eivät olleet samoja, syötä tiedot uudelleen.");
define("INSLAN46", "ei ole oikea sähköpostiosoite, syötä tiedot uudelleen.");
define("INSLAN47", "Kaikki valmista!");
define("INSLAN48", "e107 on nyt kerännyt tarvittavat tiedot asennuksen loppuunviemiseksi.<br />Klikkaa nappia luodaksesi taulut tietokantaan ja tallentaaksesi asetukset.");
define("INSLAN49", "Asetusten tallentaminen palvelimelle ei onnistunut.<br />Varmista että tiedostolla <b>e107_config.php</b> on oikeat oikeudet");
define("INSLAN50", "Asennus valmis!");
define("INSLAN51", "Kaikki tehty");
define("INSLAN52", "e107 on onnistuneesti asennettu!<br />Tietoturvasyistä muuta tiedoston <br /><b>e107_config.php</b> oikeudet takaisin 644.<br />Poista myös tiedosto /install.php palvelimeltasi kun olet painanut allaolevaa nappia");
define("INSLAN53", "Klikkaa tästä mennäksesi uudelle webbisivullesi!");
define("INSLAN54", "Sql-tiedoston luku epäonnistui<br /><br />Tarkista että tiedosto <b>core_sql.php</b> on <b>/e107_admin/sql</b> hakemistossa.");
define("INSLAN55", "e107 ei onnistunut luomaan kaikkia tietokannan tauluja.<br />Puhdista tietokanta ja korjaa mahdolliset ongelmat ennenkuin yrität uudelleen.");
define("INSLAN56", "Tervetuloa uudelle webbisivullesi!");

define("INSLAN57", "e107 on asennettu onnistuneesti ja on valmis sisällön vastaanottamiseen.<br />Ylläpitoalueesi löytyy <a href='e107_admin/admin.php'>täältä</a>, klikkaa mennäksesi sinne nyt. Sinun täytyy loggautua samoilla tunnuksilla jotka annoit asennusvaiheessa.");
define("INSLAN58", "UKK ja ohjeet löytyvät täältä.");
define("INSLAN59", "Kiitos kun kokeilet e107:aa, Toivomme että se täyttää tarpeesi.\n(Voit poistaa tämän viestin ylläpitoalueelta.)");
define("INSLAN60", "rastita luodaksesi");
define("INSLAN61", "hakemisto");
define("INSLAN62", "tai");
define("INSLAN63", "Tiedosto-oikeusvirhe");
define("INSLAN64", "Tämän tiedoston loi asennusskripti.");

?>
