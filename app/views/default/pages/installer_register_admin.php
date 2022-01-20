{@header}}
    <p><img src="{@HOMEDIR}}themes/images/img/partners.gif" alt="" /> This will make you register as an administrator of this website.<br>After successful registration, delete folder "install"<br></p>
    <hr/>
    <form method="post" action="../install/register_admin">
    <fieldset>
    <legend>Register</legend>
    <label for="name">Username (max20):</label><br />
    <input name="name" id="name" maxlength="20" /><br />
    <label for="password">Password (max20):</label><br />
    <input name="password" id="password" type="password" maxlength="20" /><br />
    <label for="password2">Password again:</label><br />
    <input name="password2" id="password2" type="password" maxlength="20" /><br>
    <label for="email">Email:</label><br />
    <input name="email" id="email" maxlength="100" /><br />
    <label for="osite">Site address:</label><br />
    <input name="osite" id="osite" value="{@site_address}}" maxlength="100" /><br />
    <input value="Continue..." type="submit" />
    </fieldset>
    </form>
    <hr />
{@footer}}