<?php
// (c) vavok.net

$issetLang = array(
"delfrombase" => 'Uspešno obrisano iz baze',

// inbox
"mail" => 'Vaše pismo uspešno poslato',
"nouz" => 'Niste upisali za koga je pismo',
"alldelpriv" => 'Sanduče očišćeno',
"delpriv" => 'Poruka uspešno obrisana',
"selectpriv" => 'Izabrane poruke obrisane',
"nomess" => 'Pismo je kratko, nije poslato',
"noprivlog" => 'Ne možete poslati pismo samom sebi',
"fullmail" => 'Sanduče korisnika prepunjeno, ne možete mu pisati dok ne oslobodi mesta u sandučetu',

// settings
"editprofil" => 'Profil uspešno izmenjen',
"editpass" => 'Šifra uspešno izmenjena',
"editsetting" => 'Podešavanja uspešno izmenjena',
"noemail" => 'Nepravilna email adresa',
"noname" => 'Niste napisali ime',
"lostpass" => 'Šifra uspesno generisana!<br />Nova šifra je poslata na email iz vašeg profila<br />',
"nolostpass" => 'Upisali ste neodgovarajuće podatke, šifra ne može biti poslata',
"incorrect" => 'U poljima upišite manje od 50 karaktera</b>',
"nopost" =>  'Greška! Predugačak komentar ili tema',
"inlogin" => 'Nedozvoljeni simboli u šifri, koristite samo latinicu i brojeve ',
"nopass" => 'Stara šifra netačna',
"nonewpass" => 'Nove šifre se ne poklapaju! U oba polja morate upisati iste',
"inmail" => 'Upisali ste netačnu email adresu! Email mora biti u formatu name@name.name',
"inhappy" => 'Nepravilan format datuma rođenja<br /> On mora biti u formatu dd.mm.gggg',
"biginfo" => 'Predugačko korisničko ime ili šifra',
"smallinfo" => 'Kratko korisničko ime ili šifra',
"insite" => 'Nepravilna adresa sajta<br />Adresa mora biti u formatu http://my_site.domen',
"nouser" => 'Korisnik sa traženim korisničkim imenom ne postoji',
"names" =>  'Niste naveli ime, ili je prekratko',
"posts" =>  'Niste upisali poruku, ili je poruka prekratka.',
"noreg" => 'Nedozvoljeni znakovi u korisničkom imenu ili šifri<br />Koristite samo latinicu i brojeve',
"antirega" => 'Već ste registrovani',
"nopassword" => 'Niste upisali šifru ili je nepravilna',

// admin panel isset
"mp_yesset" => 'Podešavanja sistema uspešno sačuvana',
"mp_nosset" => 'Greška pri izmeni, verovarno niste popunili neko polje',
"mp_votesyes" => 'Anketa uspešno izmenjena',
"mp_votesno" => 'Greška pri izmeni, verovarno niste popunili neko polje',
"mp_addvotes" => 'Novа anketa uspešno napravljena',
"mp_delvotes" => 'Anketa uspešno izbrisana iz arhive',
"mp_editfiles" => 'Fajl uspešno izmenjen',
"mp_newfiles" => 'Novi fajl uspesno napravljen',
"mp_pageexists" => 'Stranica postoji u bazi',
"mp_nonewfiles" => 'Greška! Nedozvoljen naziv fajla',
"mp_nodelfiles" => 'Fajl nije obrisan',
"mp_delfiles" => 'Fajl uspešno obrisan',
"mp_noyesfiles" => 'Fajl sa takvim imenom postoji',
"mp_delchat" => 'Mini-chat uspešno obrisan',
"mp_delpostchat" => 'Poruka obrisana',
"mp_editpostchat" => 'Poruka izmenjena',
"mp_dellogs" => 'Log fajl ispražnjen',
"mp_editstatus" => 'Statusi izmenjeni',
"mp_noeditstatus" => 'Greška pri izmeni statusa! Proverite sva polja',
"mp_delsubmail" => 'Prijavljeni na novosti uspešno izbrisan iz baze',
"mp_nodelsubmail" => 'Greška pri brisanju iz baze',
"mp_delsuball" => 'Svi prijavljeni na novosti sajta uspešno obrisani',
"mp_ydelconf" => 'Registracija uspešno potvrđena',
"ap_noaccess" => 'Nemate potrebne dozvole da pristupite stranici',

// user isset
"quarantine" => 'Uključen je karantin! Novi članovi ne mogu pisati komentare i poruke tačno ' . round(get_configuration("quarantine") / 3600) . ' časova posle registracije',
"addfoto" => 'Fotografija uspešno dodata',
"delfoto" => 'Fotografija uspešno izbrisana',
"editfoto" => 'Fotografija uspešno izmenjena',
"addkomm" => 'Vaš komentar uspešno dodat',
"delkomm" => 'Komentar uspešno izbrisan',

// contact and ignore list
"contactb_noadd" => 'Dodavanje novog kontakta je neuspešno! Proverite da li ste prekoračili limit.',
"contactb_add" => 'Uspešno ste dodali novi kontakt',
"noaddcontactb" => 'Dodavanje novog kontakta je neuspešno! Niste upisali važne podatke.',
"contactb_del" => 'Kontakt uspešno obrisan.',
"contactb_nodel" => 'Kontakt nije obrisan.',
"useletter" =>  'Morate koristiti slova u korisničkom imenu',
"ignor_add" => 'Član je uspesno dodat u ignor-listu',
"ignor_noadd" => 'Greška pri dodavanju u ignor listu',
"ignor_del" => 'Član uspešno obrisan iz ignor liste',
"ignor_nodel" => 'Greška pri brisanju iz ignor liste',
"ignoring" => 'Član kome šaljete pismo vas je stavio u ignor listu! Pismo nije poslato.',
"kontakt_add" => 'Član je uspesno dodat u kontakt listu',
"kontakt_noadd" => 'Greška pri dodavanju u kontakt listu',
"kontakt_del" => 'Član uspesno obrisan iz kontakt liste',
"kontakt_nodel" => 'Greška pri brisanju iz kontakt liste'
);

// forms
$formsArray = array(
"antiflood" =>  'Antiflood! Sledeću poruku možete upisati za ' . (int)get_configuration("floodTime") . ' sekundi',
"nobody" => 'Niste upisali tekst poruke',
"addon" =>  'Poruka uspešno upisana!<br />',
"noadduzer" => 'Navedeni član ne postoji',
"nologin" => 'Niste se prijavili, molimo da se prijavite',
"vrcode" => 'Pogrešan kod za proveru',
"savedok" => 'Uspešno sačuvano',
"inputoff" => 'Greška<br />Netačno korisničko ime ili šifra<br />',
"exit" => 'Uspešno ste se odjavili',
"fixerrors" => 'Ispravite greške da biste nastavili',
"valid" => 'Ispravno',
"namerequired" => 'Ime je obavezno',
"mailwrong" => 'Pogrešan format adrese',
"msgshort" => 'Poruka je prekratka'
);

$issetLang = array_merge($issetLang, $formsArray);
?>