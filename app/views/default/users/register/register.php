{@header}}
    <div id="form_wrapper" class="form_wrapper">
        <h1>{@localization[registration]}}</h1>
        <form class="login active" method="post" action="{@HOMEDIR}}users/register">
            <div>
                <label>{@localization[username]}}:</label>
                <input type="text" name="log" maxlength="40" />
                <span class="error">This is an error</span>
            </div>
            <div>
                <label>{@localization[pass]}}:</label>
                <input name="par" type="password" maxlength="20" />
                <span class="error">This is an error</span>
            </div>
            <div>
                <label>{@localization[passagain]}}:</label>
                <input name="pars" type="password" maxlength="20" />
                <span class="error">This is an error</span>
            </div>
            <div>
                <label>{@localization[yemail]}}:</label>
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
                <input type="submit" value="{@localization[register]}}">
                <div class="clear"></div>
            </div>
        </form>
    </div>
    <p>{@localization[reginfo]}}{@registration_key_info}}.</p>
    <p>{@quarantine_info}}</p>
{@footer}}