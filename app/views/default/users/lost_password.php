{@header}}
	<div id="form_wrapper" class="form_wrapper">
		<h1>{@localization[lostpass]}}</h1>
		<form class="login active" method="post" action="lostpassword">
			<p>{@localization[howtolostpass]}}</p>
			<div>
				<label>{@localization[username]}}:</label>
				<input type="text" name="logus" maxlength="40" />
				<span class="error">This is an error</span>
			</div>
			<div>
				<label>{@localization[yemail]}}:</label>
				<input  type="text" value="" name="mailsus" maxlength="50" />
				<span class="error">This is an error</span>
			</div>
			<div>
				{@security_code}}
			</div>
			<div class="bottom">
				<button class="btn btn-primary" type="submit">{@localization[send]}}</button>
			</div>
		</form>
	</div>
{@footer}}