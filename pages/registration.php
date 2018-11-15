<?php
// (c) vavok.net
require_once"../include/strtup.php";

$mediaLikeButton = 'off'; // dont show like buttons

$my_title = $lang_reg['registration'];
include_once"../themes/" . $config_themes . "/index.php";
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
$log = isset($log) ? $log = check($log) : $log = '';

if ($config["openReg"] == "1") {
	if (is_reg()) {
		echo '<br>' . $log . ', ' . $lang_reg['againreg'] . '<br>';
	} else {
		if (isset($_GET['isset'])) {
		$isset = check($_GET['isset']);
		echo '<div align="center"><b><font color="#FF0000">';
		echo get_isset();
		echo '</font></b></div>';
		}


		echo '
		<div id="form_wrapper" class="form_wrapper">
		<form class="login active" method="post" action="reguser.php">
		<h3>Register</h3>
		<div>
		<label>' . $lang_home['username'] . ':</label>
		<input type="text" name="log" maxlength="40" />
		<span class="error">This is an error</span>
		</div>
		<div>
		<label>' . $lang_home['pass'] . ':</label>
		<input name="par" type="password" maxlength="20" />
		<span class="error">This is an error</span>
		</div>
		<div>
		<label>' . $lang_reg['passagain'] . ':</label>
		<input name="pars" type="password" maxlength="20" />
		<span class="error">This is an error</span>
		</div>

		<div>
		<label>Email:</label>
		<input type="text" name="meil" maxlength="40" />
		<span class="error">This is an error</span>
		</div>

		<div>

		<label>' . $lang_home['captcha'] . '<img id="captcha" src="../include/plugins/securimage/securimage_show.php" alt="CAPTCHA Image" /><br /><a href="#" onclick="document.getElementById(\'captcha\').src = \'../include/plugins/securimage/securimage_show.php?\' + Math.random(); return false">[ Different Image ]</a></label>

		<input name="captcha_code" type="text" maxlength="6" size="10" />
		<span class="error">This is an error</span>
		</div>';

		if (!empty($_GET['ptl'])) {
		echo '<input type="hidden" name="ptl" value="' . check($_GET['ptl']) . '" />';
		}

		echo '						<div class="bottom">
		<input type="submit" value="' . $lang_reg['register'] . '"></input>';
		echo '<div class="clear"></div>
		</div>
		</form>
		</div>
		';

		echo $lang_reg['reginfo'];
		if ($config["regConfirm"] == "1") {
		echo $lang_reg['keyinfo'] . '<br>';
		} 
		if ($config["quarantine"] > 0) {
		echo '<font color="#FF0000">' . $lang_reg['quarantine1'] . ' ' . round($config["quarantine"] / 3600) . ' ' . $lang_reg['quarantine2'] . '</font><br>';
		}
		echo '<br><a href="siterules.php" class="sitelink">' . $lang_reg['siterules'] . '</a><br>';
		}
} else {
	echo '<img src="../images/img/error.gif" alt=""> ' . $lang_reg['regstoped'] . '!<br><br>';
} 

echo '<p><a href="../" class="homepage">' . $lang_home['home'] . '</a></p>';

include_once"../themes/" . $config_themes . "/foot.php";

?>