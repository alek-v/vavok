<?php
// (c) vavok.net


// set main website data

// check is there any data
if (file_exists('../used')) {
    // don't overwrite existing data
    return;
}

// set write permissions on data directory
if (file_exists('../used')) {
	chmod("../used", 0777);
} else {
	mkdir('../used', 0777);
}

// set write permissions on plugin directory
if (file_exists('../include/plugins')) {
    chmod("../include/plugins", 0777);
}

// create data directories
$directories = array(
	'databck', 'datacache', 'dataconfig', 'datados', 'datagallery', 'datalang', 'datalog', 'dataphoto', 'datasessions', 'datatmp', 'datamain', 'dataadmin'
);

foreach ($directories as $dir) {
	if (!file_exists('../used/' . $dir)) {
		mkdir('../used/' . $dir, 0755);
	}
}

// create data files
$dataFiles = array(
	'.htaccess', 'adminchat.dat', 'antiflood.dat', 'antiword.dat', 'ban.dat', 'config.dat', 'countries.txt', 
	'flood.dat', 'headmeta.dat', 'index.php', 'local.dat', 'logfiles.dat', 'referer.dat', 'subnames.dat', 'email_queue_sent.dat',
	'dataconfig/gallery.dat', 'datagallery/fotobase.dat', 
	'datalog/ban.dat', 'datalog/dberror.dat', 'datalog/error.dat', 'datalog/error401.dat', 'datalog/error402.dat', 'datalog/error403.dat', 'datalog/error404.dat', 'datalog/error406.dat', 'datalog/error500.dat', 'datalog/error502.dat'
);

foreach ($dataFiles as $file) {
	if (!file_exists('../used/' . $file)) {
		fopen('../used/' . $file, "w");
	}
}

// set write permissions to files and folders
$dir = opendir("../used");
while ($file = readdir($dir)) {
    if ($file != '.' && $file != '..' && $file != '.htaccess') {
        if (is_dir("../used/" . $file)) {
            $dires[] = $file;
        } else {
            $files[] = $file;
        } 
    } 
} 
closedir($dir);

$count = count($files);
for($i = 0;$i < $count;$i++) {
    if (!is_writeable('../used/' . $files[$i])) {
        chmod($files[$i], 0777);
    }
}

$count = count($dires);
for($i = 0;$i < $count;$i++) {
    if (!is_writeable('../used/' . $dires[$i])) {
        chmod($dires[$i], 0777);
    }
}

// write data to .htaccess
chmod('../used/.htaccess', 0777);
$htaccessData = 'deny from all
AddDefaultCharset UTF-8';
$fp = fopen('../used/.htaccess', 'a');
fwrite($fp, $htaccessData);
fclose($fp);
// change chmod - read and write for owner, nothing for everybody else
chmod('../used/.htaccess', 0600);

// write data to config.dat
$configData = '||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||';
$fp = fopen('../used/config.dat', 'a');
fwrite($fp, $configData);
fclose($fp);

// write data to countries.txt
$countriesData = 'Afghanistan
Albania
Algeria
Andorra
Angola
Antigua & Deps
Argentina
Armenia
Australia
Austria
Azerbaijan
Bahamas
Bahrain
Bangladesh
Barbados
Belarus
Belgium
Belize
Benin
Bhutan
Bolivia
Bosnia Herzegovina
Botswana
Brazil
Brunei
Bulgaria
Burkina
Burundi
Cambodia
Cameroon
Canada
Cape Verde
Central African Rep
Chad
Chile
China
Colombia
Comoros
Congo
Congo {Democratic Rep}
Costa Rica
Croatia
Cuba
Cyprus
Czech Republic
Denmark
Djibouti
Dominica
Dominican Republic
East Timor
Ecuador
Egypt
El Salvador
Equatorial Guinea
Eritrea
Estonia
Ethiopia
Fiji
Finland
France
Gabon
Gambia
Georgia
Germany
Ghana
Greece
Grenada
Guatemala
Guinea
Guinea-Bissau
Guyana
Haiti
Honduras
Hungary
Iceland
India
Indonesia
Iran
Iraq
Ireland {Republic}
Israel
Italy
Ivory Coast
Jamaica
Japan
Jordan
Kazakhstan
Kenya
Kiribati
Korea North
Korea South
Kuwait
Kyrgyzstan
Laos
Latvia
Lebanon
Lesotho
Liberia
Libya
Liechtenstein
Lithuania
Luxembourg
Macedonia
Madagascar
Malawi
Malaysia
Maldives
Mali
Malta
Marshall Islands
Mauritania
Mauritius
Mexico
Micronesia
Moldova
Monaco
Mongolia
Montenegro
Morocco
Mozambique
Myanmar, {Burma}
Namibia
Nauru
Nepal
Netherlands
New Zealand
Nicaragua
Niger
Nigeria
Norway
Oman
Pakistan
Palau
Panama
Papua New Guinea
Paraguay
Peru
Philippines
Poland
Portugal
Qatar
Romania
Russian Federation
Rwanda
St Kitts & Nevis
St Lucia
Saint Vincent & the Grenadines
Samoa
San Marino
Sao Tome & Principe
Saudi Arabia
Senegal
Serbia
Seychelles
Sierra Leone
Singapore
Slovakia
Slovenia
Solomon Islands
Somalia
South Africa
South Sudan
Spain
Sri Lanka
Sudan
Suriname
Swaziland
Sweden
Switzerland
Syria
Taiwan
Tajikistan
Tanzania
Thailand
Togo
Tonga
Trinidad & Tobago
Tunisia
Turkey
Turkmenistan
Tuvalu
Uganda
Ukraine
United Arab Emirates
United Kingdom
United States
Uruguay
Uzbekistan
Vanuatu
Vatican City
Venezuela
Vietnam
Yemen
Zambia
Zimbabwe';
$fp = fopen('../used/countries.txt', 'a');
fwrite($fp, $countriesData);
fclose($fp);

// write data to local.dat
$localData = '0|0|0|0|0|0|0|0|0|0|';
$fp = fopen('../used/local.dat', 'a');
fwrite($fp, $localData);
fclose($fp);

// write data to admin pages
if (!file_exists('../used/dataadmin/moderator_pages.dat')) {
    fopen('../used/dataadmin/moderator_pages.dat', "w");
    $moderator_pagesdata = 'adminchat.php||admchat||adminchat
minichat.php||chatmod||minichat
book.php||bookmod||book
adminlist.php||modlist||adminlist
reglist.php||notconf||reglist';
    file_put_contents('../used/dataadmin/moderator_pages.dat', $moderator_pagesdata);
}




?>