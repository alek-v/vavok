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
	'databck', 'datacache', 'dataconfig', 'datados', 'datagallery', 'datalog', 'dataphoto', 'datasessions', 'datatmp', 'datamain'
);

foreach ($directories as $dir) {
	if (!file_exists('../used/' . $dir)) {
		mkdir('../used/' . $dir, 0755);
	}
}
mkdir('../fls/', 0755);

// Create data files in 'used' directory
$dataFiles = array(
	'.htaccess', 'adminchat.dat', 'antiflood.dat', 'antiword.dat', 'ban.dat', 'config.dat',
	'flood.dat', 'headmeta.dat', 'index.php', 'logfiles.dat', 'referer.dat', 'subnames.dat', 'email_queue_sent.dat',
	'dataconfig/gallery.dat', 'datagallery/fotobase.dat', 
	'datalog/ban.dat', 'datalog/dberror.dat', 'datalog/error.dat', 'datalog/error401.dat', 'datalog/error402.dat', 'datalog/error403.dat', 'datalog/error404.dat', 'datalog/error406.dat', 'datalog/error500.dat', 'datalog/error502.dat'
);

// Create index.php in upload directory
fopen('../fls/index.php', "w");

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

// Put main .htaccess file
if (!file_exists('../.htaccess')) {
	if (!copy('include/.htaccess.sample', '../.htaccess')) {
	    echo 'failed to copy to ../.htaccess';
	}
}

// Put main robots.txt file
if (!file_exists('../robots.txt')) {
	if (!copy('include/robots.txt.sample', '../robots.txt')) {
	    echo 'failed to copy to ../robots.txt';
	}
}
?>