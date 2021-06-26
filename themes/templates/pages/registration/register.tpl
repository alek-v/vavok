
<div id="form_wrapper" class="form_wrapper">

	<h1>{@website_language[registration]}}</h1>

	<form class="login active" method="post" action="registration.php?action=reguser">

		<div>
			<label>{@website_language[username]}}:</label>
			<input type="text" name="log" maxlength="40" />
			<span class="error">This is an error</span>
		</div>

		<div>
			<label>{@website_language[pass]}}:</label>
			<input name="par" type="password" maxlength="20" />
			<span class="error">This is an error</span>
		</div>

		<div>
			<label>{@website_language[passagain]}}:</label>
			<input name="pars" type="password" maxlength="20" />
			<span class="error">This is an error</span>
		</div>

		<div>
			<label>{@website_language[yemail]}}:</label>
			<input type="text" name="meil" maxlength="40" />
			<span class="error">This is an error</span>
		</div>

		<div>
			<label>
				{@security_code}}
			</label>
		</div>

		<input type="hidden" name="ptl" value="{@page_to_load}}" />

		<div>
			<input type="submit" value="{@website_language[register]}}"></input>
			<div class="clear"></div>
		</div>

	</form>

</div>

<p>{@registration_info}}{@registration_key_info}}.</p>
<p>{@quarantine_info}}</p>

<p><a href="siterules.php" class="btn btn-outline-primary sitelink">{@website_language[siterules]}}</a></p>

<p><a href="../" class="btn btn-primary homepage">{@website_language[home]}}</a></p>