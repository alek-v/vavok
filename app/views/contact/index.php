{@header}}
	<div class="container">
		<div class="row">
			<div class="col-md-9 col-md-offset-3">
				<div class="well well-sm">
					<form class="form-horizontal" method="post" action="{@HOMEDIR}}contact/send">
						<fieldset>
							<legend class="text-center">{@website_language[contact]}}</legend>
							{@usernameAndMail}}
							<div class="form-group m-3">
								<div class="col-md-9">
									<label class="col-md-9 control-label" for="body">{@website_language[message]}}:</label>
									<textarea name="body" id="body" class="form-control"></textarea>
								</div>
							</div>
							<div class="form-group m-3">
								<div class="col-md-6">
									{@security_code}}
								</div>
							</div>
							<div class="form-group m-3">
								<div class="col-md-3">
									<button name="go" type="submit" class="btn btn-primary">{@website_language[send]}}</button>
								</div>
							</div>
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div>
		<p><a href="../" class="btn btn-primary homepage">{@website_language[home]}}</a></p>
	</div>
{@footer}}