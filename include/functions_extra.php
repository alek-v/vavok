<?php
// (c) vavok.net
// 29.04.2020. 0:26:24

function downloadFile($fullPath, $newname = '') {

  // Must be fresh start
  if( headers_sent() )
    die('Headers Sent');

  // Required for some browsers
  if(ini_get('zlib.output_compression'))
    ini_set('zlib.output_compression', 'Off');

  // File Exists?
  if( file_exists($fullPath) ){
   
    // Parse Info / Get Extension
    $fsize = filesize($fullPath);
    $path_parts = pathinfo($fullPath);
    $ext = strtolower($path_parts["extension"]);
   
    // Determine Content Type
    switch ($ext) {
      case "pdf": $ctype="application/pdf"; break;
      case "exe": $ctype="application/octet-stream"; break;
      case "zip": $ctype="application/zip"; break;
      case "doc": $ctype="application/msword"; break;
      case "xls": $ctype="application/vnd.ms-excel"; break;
      case "ppt": $ctype="application/vnd.ms-powerpoint"; break;
      case "gif": $ctype="image/gif"; break;
      case "png": $ctype="image/png"; break;
      case "jpeg":
      case "jpg": $ctype="image/jpg"; break;
      default: $ctype="application/force-download";
    }

    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false); // required for certain browsers
    header("Content-Type: $ctype");
    if (empty($newname)) {
    header("Content-Disposition: attachment; filename=\"".basename($fullPath)."\";" );
  } else {
  	header("Content-Disposition: attachment; filename=\"".$newname."\";" );
  }
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".$fsize);
    ob_clean();
    flush();
    readfile( $fullPath );

  } else
    die('File Not Found');

}


?>