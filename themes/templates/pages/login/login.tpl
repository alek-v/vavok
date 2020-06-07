<div id="form_wrapper" class="form_wrapper">

	<h1>{@website_language[login]}}</h1>

	<form class="login active" method="post" action="../pages/input.php">

	<div>
		<label>{@website_language[mailoruser]}}:</label>
		<input type="text" name="log" value="{@username}}"/>
		<span class="error">This is an error</span>
	</div>

	<div>
		<label>{@website_language[pass]}}: <a href="/mail/lostpassword.php" rel="forgot_password" class="forgot linkform">{@website_language[lostpass]}}</a></label>
		<input name="pass" type="password" />
		<span class="error">This is an error</span>
	</div>

	<div class="bottom">

		<div class="remember">
			<input name="cookietrue" type="checkbox" value="1" checked" />
			<span>{@website_language[rembme]}}</span>
		</div>

		<input type="hidden" name="ptl" value="{@page_to_load}}" />

		<input type="submit" value="{@website_language[login]}}"></input>
		
		<a href="/pages/registration.php" rel="register" class="linkform">{@website_language[registerHere]}}</a>
		
		<div class="clear"></div>

	</div>
	</form>

</div>

<p><a href="../" class="btn btn-primary homepage">{@website_language[home]}}</a></p>
