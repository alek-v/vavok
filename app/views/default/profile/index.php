{@header}}
    <div class="row">
        <div class="col-sm">
            <div class="row">
                <h1>{@localization[profile_settings]}}</h1>
                {@profile_form}}
                <hr />
                {@change_password}}
            </div>
                <div class="row">
                    <p><a href="{@HOMEDIR}}profile/delete" class="btn btn-danger">{@localization[delete_profile]}}</a></p>
                </div>
        </div>
        <div class="col-sm">
            <div class="photo">
                {@profile_photo}}
            </div>
        </div>
    </div>
{@footer}}