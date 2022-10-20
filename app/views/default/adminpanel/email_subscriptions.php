{@header}}
    <h1>{@localization[subscription_options]}}</h1>
    <div class="container shadow p-4 mb-5 bg-body rounded">
        {@content}}
        {@all_options}}
    </div>
    <div class="mt-5">
        <form action="./subscription_options" method="post">
            <legend>{@localization[add_subscription_option]}}</legend>
            <div style="max-width: 520px;">
                <div class="mb-3">
                    <label for="subscription_option" class="form-label">{@localization[subscription_option_no_space]}}:</label>
                    <input id="subscription_option" name="subscription_option" type="text" value="" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="subscription_description" class="form-label">{@localization[subscription_short_desc]}}:</label>
                    <input id="subscription_description" name="subscription_description" value="" class="form-control" />
                </div>
            </div>
            <button class="btn btn-primary">{@localization[save]}}</button>
        </form>
    </div>
    {@bottom_links}}
{@footer}}