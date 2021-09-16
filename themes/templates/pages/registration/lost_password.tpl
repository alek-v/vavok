<div id="form_wrapper" class="form_wrapper">
	<h1>{@website_language[lostpass]}}</h1>
	<form class="login active" method="post" action="lostpassword.php?action=send">
		<p>{@website_language[howtolostpass]}}</p>
		<div>
			<label>{@website_language[username]}}:</label>
			<input type="text" name="logus" maxlength="40" />
			<span class="error">This is an error</span>
		</div>
		<div>
			<label>{@website_language[yemail]}}:</label>
			<input  type="text" value="" name="mailsus" maxlength="50" />
			<span class="error">This is an error</span>
		</div>
		<div>
			{@security_code}}
		</div>
		<div class="bottom">
			<button class="btn btn-primary" type="submit">{@website_language[send]}}</button>
			<div class="clear"></div>
		</div>
	</form>
</div>
<div style="clear: left; overflow: hidden; width: 100%; "></div>