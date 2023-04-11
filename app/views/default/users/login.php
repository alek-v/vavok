{@header}}
    {@show_notification}}
    <div id="form_wrapper" class="form_wrapper">
        <h1>{@localization[login]}}</h1>
        <form class="login active" method="post" action="{@HOMEDIR}}users/login">
        <div>
            <label>{@localization[mailoruser]}}:</label>
            <input type="text" name="log" value="{@username}}"/>
            <span class="error">This is an error</span>
        </div>
        <div>
            <label>{@localization[pass]}}: <a href="{@HOMEDIR}}users/lostpassword" rel="forgot_password" class="forgot linkform">{@localization[lostpass]}}</a></label>
            <input name="pass" type="password" />
            <span class="error">This is an error</span>
        </div>
        <div class="bottom">
            <div class="remember">
                <input name="cookietrue" type="checkbox" value="1" />
                <span>{@localization[rembme]}}</span>
            </div>
            <input type="hidden" name="ptl" value="{@page_to_load}}" />
            <input type="submit" value="{@localization[login]}}"></input>
            <a href="{@HOMEDIR}}users/register" rel="register" class="linkform">{@localization[registerHere]}}</a>
            <div class="clear"></div>
        </div>
        </form>
    </div>
{@footer}}
