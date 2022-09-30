<?php
// modified: 05.06.2020. 20:09:41
$sajtx = $_SERVER['HTTP_HOST'];

$lang_siterules['siterules'] = "Правила сајта";
$lang_siterules['mainrules'] = "
Правила за кориснике сајта " . $sajtx . "<br><br>
1 <b>Opšta pravila</b><br>
1.1 Nije dozvoljeno registrovati više od jednog nadimka.<br>
1.2 Zabranjeno je registrovati vulgarne i uvredljive nadimke<br>
<br>2 <b>Pravila ponašanja</b><br>
2.1 Nije dozvoljeno pisati linkove na sajtu u reklamne svrhe.<br>
2.2 Zabranjena je rasna i verska diskriminacija, vređanje i omalovažavanje članova.<br>
2.3 Nemojte koristiti samo velika slova(Caps Lock)<br>
2.4 Ukoliko uočite temu na forumu, poruku na forumu, poruku u knjizi gostiju itd. koja nije u skladu sa pravilima obavestite administraciju privatnom porukom.<br>
<br>3 <b>Forum</b><br>
3.1 Nije dozvoljeno dopisivati se (<i>chat</i>) na forumu.<br>
3.2 Pri otvaranju teme u imenu teme ne sme se upisivati \"klikni\", \"hitno\" i sl.<br>
3.3 Ime teme bi trebalo da ukratko opiše o čemu je reč u temi.<br>
3.4 Komentari u temama ne smeju sadržati samo smeške i moraju biti u skladu sa temom.<br>
<br>4 <b>Ostalo</b><br>
4.1 Članovi koji se ne pridržavaju pravila mogu biti kažnjeni blokiranjem naloga (<i>ban</i>) od 5 min. do 30 dana.<br>
4.2 Urednici zadržavaju pravo da brišu poruke i teme bez prethodnog upozorenja.<br>
4.3 Urednici zadržavaju pravo da menjaju pravila bez prethodne najave.<br>
4.4 Ukoliko član ignoriše kazne nalog će mu biti obrisan.<br>

";



$lang_home = array_merge($lang_home, $lang_siterules);