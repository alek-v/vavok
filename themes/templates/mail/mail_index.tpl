<form method="post" action="index.php?action=go">
    <fieldset>
	    {@usernameAndMail}}
	    <label for="body">{@$lang_home['message']}}:</label>
	    <textarea name="body" id="body"></textarea><br />

	    <label for="captcha_code">{@$lang_home['captcha']}}:</label>
	    <img id="captcha" src="../include/plugins/securimage/securimage_show.php" alt="CAPTCHA Image" />
	    <br />
	    <input type="text" name="captcha_code" id="captcha_code" size="10" maxlength="6" required/>
		<a href="#" onclick="document.getElementById('captcha').src = '../include/plugins/securimage/securimage_show.php?' + Math.random(); return false">[ Different Image ]</a>
	    <br />
	    
	    <br /><input value="{@$lang_home['send']}}" name="go" type="submit" />
    </fieldset>
</form>

<p><a href="../" class="homepage">{@$lang_home['home']}}</a></p>