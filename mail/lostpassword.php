<?php
// (c) vavok.net
require_once"../include/strtup.php";
$my_title = $lang_home['lostpass'];
include_once"../themes/$config_themes/index.php";
?>

<style type="text/css">


.form_wrapper{
background-color: transparent;
	width:100%;
	font-size:16px;
}
.form_wrapper h3{
	padding:20px 30px 20px 30px;
	font-size:18px;
	border-bottom:1px solid #ddd;
}
.form_wrapper form{
	display:none;
}
.form_wrapper .column{
	width:47%;
	float:left;
}
form.active{
	display:block;
}
form.login{
	width:100%;
}
form.register{
	width:550px;
}
form.forgot_password{
	width:300px;
}
.form_wrapper a{
	text-decoration:none;
	color:#777;
	font-size:12px;
}
.form_wrapper a:hover{
	color:#000;
}
.form_wrapper label{
	display:block;
	padding:10px 30px 0px 30px;
	margin:10px 0px 0px 0px;
}
.form_wrapper input[type="text"],
.form_wrapper input[type="password"]{
	border: solid 1px #E5E5E5;
	margin: 5px 30px 0px 30px;
	padding: 9px;
	display:block;
	font-size:16px;
	width:76%;
	background: 
		-webkit-gradient(
			linear,
			left top,
			left 25,
			from(#FFFFFF),
			color-stop(4%, #EEEEEE),
			to(#FFFFFF)
		);
	background: 
		-moz-linear-gradient(
			top,
			#000000,
			#EEEEEE 1px,
			#FFFFFF 25px
			);
	-moz-box-shadow: 0px 0px 8px #f0f0f0;
	-webkit-box-shadow: 0px 0px 8px #f0f0f0;
	box-shadow: 0px 0px 8px #f0f0f0;
}
.form_wrapper input[type="text"]:focus,
.form_wrapper input[type="password"]:focus{
	background:#feffef;
}
.form_wrapper .bottom{
background-color: transparent;
	border-top:1px solid #ddd;
	margin-top:20px;
	text-shadow:1px 1px 1px #000;
}
.form_wrapper .bottom a{
	display:block;
	clear:both;
	padding:10px 30px;
	text-align:right;
	color:#ffa800;
	text-shadow:1px 1px 1px #000;
}
.form_wrapper a.forgot{
	float:right;
	font-style:italic;
	line-height:24px;
	color:#ffa800;
	text-shadow:1px 1px 1px #fff;
}
.form_wrapper a.forgot:hover{
	color:#000;
}
.form_wrapper div.remember{
	float:left;
	width:100%;
	margin:20px 0px 20px 30px;
	font-size:11px;
}
.form_wrapper div.remember input{
	float:left;
	margin:2px 5px 0px 0px;
}
.form_wrapper span.error{
	visibility:hidden;
	color:red;
	font-size:11px;
	font-style:italic;
	display:block;
	margin:4px 30px;
}
.form_wrapper input[type="submit"] {
	background: #e3e3e3;
	border: 1px solid #ccc;
	color: #333;
	font-family: "Trebuchet MS", "Myriad Pro", sans-serif;
	font-size: 14px;
	font-weight: bold;
	padding: 8px 0 9px;
	text-align: center;
	width: 150px;
	cursor:pointer;
	float:right;
	margin:15px 20px 10px 10px;
	text-shadow: 0px 1px 0px #fff;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
	-moz-box-shadow: 0px 0px 2px #fff inset;
	-webkit-box-shadow: 0px 0px 2px #fff inset;
	box-shadow: 0px 0px 2px #fff inset;
}
.form_wrapper input[type="submit"]:hover {
	background: #d9d9d9;
	-moz-box-shadow: 0px 0px 2px #eaeaea inset;
	-webkit-box-shadow: 0px 0px 2px #eaeaea inset;
	box-shadow: 0px 0px 2px #eaeaea inset;
	color: #222;
}


</style>


<?php
if (isset($_GET['page'])) {$page = check($_GET['page']);} else {$page = '';}
if (isset($_POST['logus'])) {$logus = check($_POST['logus']);}
if (isset($_POST['mailsus'])) {$mailsus = check($_POST['mailsus']);}

if (empty($page) || $page == 'index') {
	
	echo '

				<div id="form_wrapper" class="form_wrapper">
					<form class="login active" method="post" action="lostpassword.php?page=send">
						<h3>' . $lang_mail['howtolostpass'] . '</h3>
						<div>
							<label>' . $lang_home['username'] . ':</label>
							<input type="text" name="logus" maxlength="40" />
							<span class="error">This is an error</span>
						</div>
						
												<div>
							<label>Email:</label>
							<input  type="text" value="@" name="mailsus" maxlength="50" />
							<span class="error">This is an error</span>
						</div>
						
							<div>
							
							<label>' . $lang_home['captcha'] . '<img id="captcha" src="../include/plugins/securimage/securimage_show.php" alt="CAPTCHA Image" /><br /><a href="#" onclick="document.getElementById(\'captcha\').src = \'../include/plugins/securimage/securimage_show.php?\' + Math.random(); return false">[ Different Image ]</a></label>
							
							<input name="captcha_code" type="text" maxlength="6" size="10" />
							<span class="error">This is an error</span>
						</div>
						
						
						<div class="bottom">
							<input type="submit" value="' . $lang_home['send'] . '"></input>';
							echo '<div class="clear"></div>
						</div>
					</form>
</div>
<div style="clear: left; overflow: hidden; width: 100%; "></div>

';
	

} 
if ($page == 'send') {
    if (!empty($logus) && !empty($mailsus)) {
        $userx_id = $users->getidfromnick($logus);
        $show_userx = $db->select('vavok_about', "uid='" . $userx_id . "'", '', 'email');

        $checkmail = trim($show_userx['email']);

        if ($mailsus == $checkmail) {
require_once '../include/plugins/securimage/securimage.php';
$securimage = new Securimage();

if ($securimage->check($_POST['captcha_code']) == true) {

                $newpas = generate_password();
                $new = md5($newpas);

                $subject = $lang_mail['newpassfromsite'] . ' ' . $config["title"];
                $mail = "" . $lang_mail['hello'] . " " . $logus . "\r\n" . $lang_mail['yournewdata'] . " " . $config["homeUrl"] . "\n" . $lang_home['username'] . ": " . $logus . "\n" . $lang_home['pass'] . ": " . $newpas . "\r\n\r\n" . $lang_mail['lnkforautolog'] . ":\r\n
								" . $config["homeUrl"] . "/input.php?log=" . $logus . "&pass=" . $newpas . "&cookietrue=1
								\r\n" . $lang_mail['ycchngpass']  . "\r\n";

                sendmail($mailsus, $subject, $mail); 
                // update user's profile
                mysql_query("UPDATE vavok_users SET pass='" . $new . "' WHERE id='" . $userx_id . "'");

                echo '<b>' . $lang_mail['passgen'] . '<br></b><br>';
            } else {
                echo $lang_mail['wrongcaptcha'] . '!<br><br>';
                echo '<a href="lostpassword.php" class="sitelink">' . $lang_home['back'] . '</a><br>';;
            } 
        } else {
            echo $lang_mail['wrongmail'] . '!<br><br>';
            echo '<a href="lostpassword.php" class="sitelink">' . $lang_home['back'] . '</a><br>';;
        } 
    } else {
        echo $lang_mail['noneededdata'] . '!<br><br>';
        echo '<a href="lostpassword.php" class="sitelink">' . $lang_home['back'] . '</a><br>';;
    } 
} 

echo '<p><a href="../" class="homepage">' . $lang_home['home'] . '</a></p>';
include_once"../themes/" . $config_themes . "/foot.php";

?>