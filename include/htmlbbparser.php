<?php
// (c) vavok.net

// debugging prefs..
$check_tag_balance = false;

// bbcocode to xhtml
function bb2html() {
global $check_tag_balance, $insert_link, $prevent_xss;

$bb2html = func_get_arg(0);
if (func_num_args() == 2) {
	$title = func_get_arg(1);
	$id_title = make_valid_id($title); // fix up bad id's
} else { 
	$id_title = $title = '';
}

	// init.. [useful global array]
	$GLOBALS['cbparser']['state'] = 0;
	$GLOBALS['cbparser']['close_tags'] = '';
	$GLOBALS['cbparser']['text'] = slash_it($bb2html);
if (!empty($GLOBALS['do_debug'])) { debug("\n\n".'cbparser incoming [$bb2html]: '. $bb2html ."\n\n"); }// :debug:

	// oops!
	if ($bb2html == '') {
		return false;
	}

	// grab any *real* square brackets first, store 'em..
	$bb2html = str_replace('[[[[', '**$@$**[[', $bb2html); // catch demo tags next to demo tags
	$bb2html = str_replace(']]]]', ']]**@^@**', $bb2html); // ditto
	$bb2html = str_replace('[[[', '**$@$**[', $bb2html); // catch tags next to demo tags
	$bb2html = str_replace(']]]', ']**@^@**', $bb2html); // ditto
	$bb2html = str_replace('[[', '**$@$**', $bb2html); // finally!
	$bb2html = str_replace(']]', '**@^@**', $bb2html);


	// ensure bbcode is lowercase..
	// fix this. it is lowercase url's
	// $bb2html = bbcode_to_lower($bb2html);

	/*
		pre-formatted text

		even bbcode inside [pre] text will remain untouched, as it should be.
		there may be multiple [pre] or [ccc] blocks, so we grab them all and create arrays..
		*/

	$pre = array(); $i = 9999;
	while ($pre_str = stristr($bb2html, '[pre]')) {
if (!empty($GLOBALS['do_debug'])) debug("\n".'$pre_str: '."$pre_str\n\n");// :debug:
		$pre_str = substr($pre_str, 0, strpos($pre_str, '[/pre]') + 6);
		$bb2html = str_replace($pre_str, "***pre_string***$i", $bb2html);
		$pre[$i] = encode(str_replace(array('**$@$**', '**@^@**'), array('[[', ']]'), $pre_str));
if (!empty($GLOBALS['do_debug'])) debug("\n".'$pre[$i]: '."$pre[$i]\n\n");// :debug:
		$i++; //	^^	we encode this, for html tags, etc.
	}

	// rudimentary tag balance checking..
	//if ($check_tag_balance) { $bb2html = check_balance($bb2html); }
	//if ($GLOBALS['cbparser']['state'] == 1)  { return false; } // imbalanced tags

	// generic entity encode
	//$bb2html = htmlentities($bb2html, ENT_NOQUOTES, 'utf-8');
	$bb2html = str_replace('[sp]', '&nbsp;', $bb2html);

	// ordinary transformations..

	// we rely on the browser producing \r\n (DOS) carriage returns, as per spec.
	$bb2html = str_replace('<br />',"<br />\r", $bb2html);		// the \n remains, and makes the raw html readable
	$bb2html = str_replace("\r\n","<br />\r", $bb2html);		// the \n remains, and makes the raw html readable
	$bb2html = str_replace('[b]', '<b>', $bb2html);
	$bb2html = str_replace('[/b]', '</b>', $bb2html);
	$bb2html = str_replace('[strong]', '<strong>', $bb2html);
	$bb2html = str_replace('[/strong]', '</strong>', $bb2html);
	$bb2html = str_replace('[i]', '<em>', $bb2html);
	$bb2html = str_replace('[/i]', '</em>', $bb2html);
	$bb2html = str_replace('[u]', '<span class="underline">', $bb2html);
	$bb2html = str_replace('[/u]', '<!--u--></span>', $bb2html);
	$bb2html = str_replace('[big]', '<big>', $bb2html);
	$bb2html = str_replace('[/big]', '</big>', $bb2html);
	$bb2html = str_replace('[small]', '<small>', $bb2html);
	$bb2html = str_replace('[/small]', '</small>', $bb2html);

	// images
	$bb2html = preg_replace('#\[img\](.*?)\[/img\]#si', '<img src="\1" alt="" />', $bb2html);


	// other URLs..
    while (strpos($bb2html, "[url=") !== false) {
        list ($r1, $r2) = explode("[url=", $bb2html, 2);
        if (strpos($r2, "]") !== false) {
            list ($r2, $r3) = explode("]", $r2, 2);
            if (strpos($r3, "[/url]") !== false) {
                list($r3, $r4) = explode("[/url]", $r3, 2);
                $target = ' target="_blank"';
                if (substr($r2, 0, 7) == "mailto:" || stristr($vavok->get_configuration('homeUrl'), $r2) || !stristr('http://', $r2)) {
                    $target = "";
                } 
                $bb2html = $r1 . '<a href="' . $r2 . '"' . $target . '>' . $r3 . '</a>' . $r4;
            } else {
                $bb2html = $r1 . "[url\n=" . $r2 . "]" . $r3;
            } 
        } else {
            $bb2html = $r1 . "[url\n=" . $r2;
        } 
    } 
    $bb2html = str_replace("[url\n=", "[url=", $bb2html); 
    // //[url]
    // /default url link setting
    $bb2html = setlinks($bb2html, "http://");
    $bb2html = setlinks($bb2html, "https://");
    $bb2html = setlinks($bb2html, "ftp://");
    $bb2html = setlinks($bb2html, "mailto:"); 

	// floaters..
	$bb2html = str_replace('[right]', '<div class="right">', $bb2html);
	$bb2html = str_replace('[/right]', '<!--right--></div>', $bb2html);
	$bb2html = str_replace('[left]', '<div class="left">', $bb2html);
	$bb2html = str_replace('[/left]', '<!--left--></div>', $bb2html);

	// code
	$bb2html = str_replace('[code]', '<span class="code">', $bb2html);
	$bb2html = str_replace('[/code]', '<!--code--></span>', $bb2html);

	// divisions..
	$bb2html = str_replace('[hr]', '<hr />', $bb2html);

	$bb2html = str_replace('[h1]', '<h1>', $bb2html);
	$bb2html = str_replace('[/h1]', '</h1>', $bb2html);
	$bb2html = str_replace('[h2]', '<h2>', $bb2html);
	$bb2html = str_replace('[/h2]', '</h2>', $bb2html);
	$bb2html = str_replace('[h3]', '<h3>', $bb2html);
	$bb2html = str_replace('[/h3]', '</h3>', $bb2html);
	$bb2html = str_replace('[h4]', '<h4>', $bb2html);
	$bb2html = str_replace('[/h4]', '</h4>', $bb2html);
	$bb2html = str_replace('[h5]', '<h5>', $bb2html);
	$bb2html = str_replace('[/h5]', '</h5>', $bb2html);
	$bb2html = str_replace('[h6]', '<h6>', $bb2html);
	$bb2html = str_replace('[/h6]', '</h6>', $bb2html);

	// fix up input spacings..
	$bb2html = str_replace('</h1><br />', '</h1>', $bb2html);
	$bb2html = str_replace('</h2><br />', '</h2>', $bb2html);
	$bb2html = str_replace('</h3><br />', '</h3>', $bb2html);
	$bb2html = str_replace('</h4><br />', '</h4>', $bb2html);
	$bb2html = str_replace('</h5><br />', '</h5>', $bb2html);
	$bb2html = str_replace('</h6><br />', '</h6>', $bb2html);

	// oh, all right then..
	// my [color=red]colour[/color] [color=blue]test[/color] [color=#C5BB41]test[/color]
	$bb2html = preg_replace('/\[color\=(.+?)\](.+?)\[\/color\]/is', "<span style=\"color:$1\">$2<!--color--></span>", $bb2html);

	// I noticed someone trying to do these at the org. use standard pixel sizes
	$bb2html = preg_replace('/\[size\=(.+?)\](.+?)\[\/size\]/is', "<span style=\"font-size:$1px\">$2<!--size--></span>", $bb2html);
	
	// show youtube videos
	$bb2html = preg_replace("/\[youtube\](?:http?s??:\/\/)?(?:www\.)?youtu(?:\.be\/|be\.com\/watch\?v=)([A-Z0-9\-_]+)(?:&(.*?))?\[\/youtube\]/i", "<iframe class=\"youtube-player\" type=\"text/html\" width=\"640\" height=\"385\" src=\"http://www.youtube.com/embed/$1\" frameborder=\"0\"></iframe>", $bb2html);
  

	// get back any real square brackets..
	//$bb2html = str_replace('**$@$**', '[', $bb2html);
	//$bb2html = str_replace('**@^@**', ']', $bb2html);

	// prevent some twat running arbitary php commands on our web server
	// I may roll this into the xss prevention and just keep it all enabled. hmm.
	$php_str = $bb2html;
	$bb2html = preg_replace("/<\?(.*})\? ?>/is", "<strong>possible xss attack: &lt;?\\1 ?&gt;</strong>", $bb2html);
	if ($php_str != $bb2html) { $GLOBALS['cbparser']['state'] = 5; }

	// re-insert the preformatted text blocks..
	$cp = count($pre) + 9998;
	for ($i=9999;$i <= $cp;$i++) {
		$bb2html = str_replace("***pre_string***$i", '<pre>'.$pre[$i].'</pre>', $bb2html);
	}
if (!empty($GLOBALS['do_debug'])) debug("\n".'$bb2html (after pre back in): '."$bb2html\n\n");// :debug:

	$bb2html = slash_it($bb2html);
if (!empty($GLOBALS['do_debug'])) { debug("\n\n".'cbparser outgoing [$bb2html]: '. $bb2html ."\n\n"); }// :debug:

	return $bb2html;

}
// end function bb2html()


// function html2bb()
function html2bb() {
if (func_num_args() == 2) { $id_title = func_get_arg(1); } else { $id_title = ''; }
$html2bb = func_get_arg(0);

	// we presume..
	$GLOBALS['cbparser']['state'] = 0;

	// pre-formatted text
	$pre = array();$i=9999;
	while ($pre_str = stristr($html2bb,'<pre>')) {
		$pre_str = substr($pre_str,0,strpos($pre_str,'</pre>')+6);
		$html2bb = str_replace($pre_str, "***pre_string***$i", $html2bb);
		$pre[$i] = str_replace("\n","\r\n",$pre_str);
		$i++;
	}

	// let's remove all the linefeeds, unix
	$html2bb = str_replace(chr(10), '', $html2bb); // "\n"
	// and Mac (windows uses both)
	$html2bb = str_replace(chr(13), '', $html2bb); // "\r"

	// 'ordinary' transformations..
	$html2bb = str_replace('<strong>', '[b]', $html2bb);
	$html2bb = str_replace('</strong>', '[/b]', $html2bb);
	$html2bb = str_replace('<b>', '[b]', $html2bb);
	$html2bb = str_replace('</b>', '[/b]', $html2bb);
	$html2bb = str_replace('<strong>', '[strong]', $html2bb);
	$html2bb = str_replace('</strong>', '[/strong]', $html2bb);
	$html2bb = str_replace('<em>', '[i]', $html2bb);
	$html2bb = str_replace('</em>', '[/i]', $html2bb);
	$html2bb = str_replace('<span class="underline">', '[u]', $html2bb);
	$html2bb = str_replace('<!--u--></span>', '[/u]', $html2bb);
	$html2bb = str_replace('<big>', '[big]', $html2bb);
	$html2bb = str_replace('</big>', '[/big]', $html2bb);
	$html2bb = str_replace('<small>', '[small]', $html2bb);
	$html2bb = str_replace('</small>', '[/small]', $html2bb);

	// images..
	$html2bb = str_replace('<img src="', '[img]', $html2bb); // catch certain legacy entries
	$html2bb = str_replace('" alt="" />', '[/img]', $html2bb);
	$html2bb = str_replace('" alt="">', '[/img]', $html2bb);
	
	// url
	$html2bb = preg_replace("/\<a href\=\"(.+?)\"\ target\=\"_blank\">(.+?)\<\/a\>/i", "[url=$1]$2[/url]", $html2bb);
	$html2bb = preg_replace("/\<a href\=\"(.+?)\"\>(.+?)\<\/a\>/i", "[url=$1]$2[/url]", $html2bb);

	// floaters..
	$html2bb = str_replace('<div class="right">','[right]', $html2bb);
	$html2bb = str_replace('<!--right--></div>','[/right]', $html2bb);
	$html2bb = str_replace('<div class="left">','[left]', $html2bb);
	$html2bb = str_replace('<!--left--></div>','[/left]', $html2bb);

	// code..
	$html2bb = str_replace('<span class="code">', '[code]', $html2bb);
	$html2bb = str_replace('<!--code--></span>', '[/code]', $html2bb);

	// etc..
	$html2bb = str_replace('<hr />', '[hr]', $html2bb);


	$html2bb = str_replace('<h1>', '[h1]', $html2bb);
	$html2bb = str_replace('</h1>', '[/h1]<br />', $html2bb);
	$html2bb = str_replace('<h2>', '[h2]', $html2bb);
	$html2bb = str_replace('</h2>', '[/h2]<br />', $html2bb);
	$html2bb = str_replace('<h3>', '[h3]', $html2bb);
	$html2bb = str_replace('</h3>', '[/h3]<br />', $html2bb);
	$html2bb = str_replace('<h4>', '[h4]', $html2bb);
	$html2bb = str_replace('</h4>', '[/h4]<br />', $html2bb);
	$html2bb = str_replace('<h5>', '[h5]', $html2bb);
	$html2bb = str_replace('</h5>', '[/h5]<br />', $html2bb);
	$html2bb = str_replace('<h6>', '[h6]', $html2bb);
	$html2bb = str_replace('</h6>', '[/h6]<br />', $html2bb);

	// pfff..
	$html2bb = preg_replace("/\<span style\=\"color:(.+?)\"\>(.+?)\<\!--color--\>\<\/span\>/is", "[color=$1]$2[/color]", $html2bb);

	// size, in pixels.
	$html2bb = preg_replace("/\<span style\=\"font-size:(.+?)px\"\>(.+?)\<\!--size--\>\<\/span\>/is", "[size=$1]$2[/size]", $html2bb);

	// bring back the brackets
	$html2bb = str_replace('***^***', '[[', $html2bb);
	$html2bb = str_replace('**@^@**', ']]', $html2bb);

	// I just threw this down here for the list fixes.
	$html2bb = str_replace('<br />', "\r\n", $html2bb);
	$html2bb = str_replace('<br/>', "\r\n", $html2bb);
	$html2bb = str_replace('<br>', "\r\n", $html2bb);


	//$html2bb = str_replace('&amp;', '&', $html2bb);


	$cp = count($pre) + 9998; // it all hinges on simple arithmetic
	for ($i=9999 ; $i <= $cp ; $i++) {
		$html2bb = str_replace("***pre_string***$i", '[pre]'.substr($pre[$i],5,-6).'[/pre]', $html2bb);
	}
if (!empty($GLOBALS['do_debug'])) { debug("\n\n".'cbparser outgoing [$html2bb]: '. $html2bb ."\n\n"); }// :debug:
//if (!empty($GLOBALS['do_debug'])) { debug('$GLOBALS: '."\t".print_r($GLOBALS, true)."\n\n\n"); }// :debug:

	return ($html2bb);
}

// add slashes to a string, or don't..
function slash_it($string) {
	if (get_magic_quotes_gpc()) { 
		return stripslashes($string);
	} else {
		return $string;
	}
}


/* 
	make a xhtml strict valid id..

	this function exists in the main corzblog functions,
	but cbparser goes out on its own, so...
								*/
function make_valid_id ($title) {
	$title = str_replace(' ', '-', strip_tags($title));
	$id_title = preg_replace("/[^a-z0-9-]*/i", '', $title);
	while (is_numeric((substr($id_title, 0, 1))) or substr($id_title, 0, 1) == '-') {
		$id_title = substr($id_title, 1);
	}
	return trim(str_replace('--', '-',$id_title));
}


/*
encode to html entities (for <pre> tags	*/
function encode($string) {
	//$string = str_replace("\r\n", "\n", slash_it($string));
	$string = str_replace("\r\n", "\n", $string);
	$string = str_replace(array('[pre]','[/pre]'),'', $string );
	return htmlentities($string, ENT_NOQUOTES, 'utf-8'); // this is plenty
}

// check balance and attempt to close some tags for final publishing
function check_balance($bb2html) {
	// some tags would be pointless to attempt to close, like image tags
	// and lists, and such. better if they just fix those themselves.
	// could still use a '[img] => [/img]' type array, and include more tags.
	$GLOBALS['cbparser']['close_tags'] = '';
	$tags_to_close = array(
		'[b]',
		'[strong]',
		'[i]',
		'[u]',
		'[big]',
		'[small]',
		'[ul]',
		'[list]',
		'[ol]',
		'[left]',
		'[right]',
		'[code]',
		'[block]',
		'[h1]',
		'[h2]',
		'[h3]',
		'[h4]',
		'[h5]',
		'[h6]',
		'[quote]',
		'[color]');

	foreach ($tags_to_close as $key => $value) {
		
		$open = substr_count($bb2html, $value);
		$close_tag = '[/'.substr($value, 1);

		while (substr_count($bb2html, $close_tag) < $open) {				
			$bb2html .= $close_tag;
			$GLOBALS['cbparser']['close_tags'] .= $close_tag;
			$GLOBALS['cbparser']['state'] = 2;
		}
	}

	$GLOBALS['cbparser']['text'] .= $GLOBALS['cbparser']['close_tags'];

	if ($GLOBALS['cbparser']['state'] == 2) {
		$GLOBALS['cbparser']['warning_message'] .= $GLOBALS['cbparser']['warnings']['balance_fixed'];
	}

	// some sums..
	$check_string = preg_replace("/\[(.+)\/\]/Ui","",$bb2html); // self-closers
	$check_string = preg_replace("/\[\!--(.+)--\]/i","",$check_string); // we support comments!
	$removers = array('[hr]','[hr2]','[hr3]','[hr4]','[sp]','[*]','[/*]');
	$check_string = str_replace($removers, '', $check_string);

	if ( ((substr_count($check_string, "[")) != (substr_count($check_string, "]")))
	or  ((substr_count($check_string, "[/")) != ((substr_count($check_string, "[")) / 2))
	// a couple of common errors (definitely the main culprits for tag mixing errors)..
	or  (substr_count($check_string, "[b]")) != (substr_count($check_string, "[/b]"))
	or  (substr_count($check_string, "[i]")) != (substr_count($check_string, "[/i]")) ) {
		$GLOBALS['cbparser']['state'] = 1;
		$GLOBALS['cbparser']['warning_message'] .= $GLOBALS['cbparser']['warnings']['imbalanced'];
		return false;
	}

if (!empty($GLOBALS['do_debug'])) { debug("\n".'$bb2html Final: '."$bb2html\n\n");  }// :debug:

	return $bb2html;
}

// another possibility is to scan the comment and work out which tags are used, close them.
// simply create a no-check list of non-closing tags to check against, and close others.
// the non-symetrical tags can cause problems, though.


/*
	bbcode to lowercase.

	ensure all bbcode is lower case..
	don't lowercase URIs, though.
								 */
function bbcode_to_lower($tring) {
	while ($str = strstr($tring, '[')) {
		if (strpos($str, ']') > (strpos($str, '"'))) { $k = '"'; } else { $k = ']'; }
		$str = substr($str, 1, strpos($str, $k));
		$tring = str_replace('['.$str, '**%^%**'.strtolower($str), $tring);
	} 
	return str_replace('**%^%**', '[', $tring);
}




?>