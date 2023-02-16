<style>
    .user_profile {
    overflow: hidden;
    margin-bottom: 15px;
    }
    .prof_left {
    float: left;
    height: 100%;
    margin-right: 15px;
    margin-left: 5px;
    }
    .prof_right {
    position: relative;
    height: 100%;
    }
    .photo img {
    max-width: 100px;
    max-height: 100px;
    overflow: hidden;
    }
</style>
<div class="user_profile">
    <div class="b">
        {@sex-img}} {@first_name}} {@last_name}} <i>{@nickname}}</i> {@user-online}}
    </div>
    <div>
        <div class="prof_left">
            <p>
                {@regCheck}}
                {@banned}}
                {@personalStatus}}
                {@sex}}: {@usersSex}}<br />
                {@city}}
                {@about}}
                {@birthday}}
                {@forumPosts}}
                {@browser}}
                {@siteSkin}}
                {@site}}
                {@regDate}}
                {@last_visit}}
                {@ip-address}}
                {@userMenu}}
            </p>
        </div>
        <div class="prof_right">
            <div class="photo">
                {@userPhoto}}
            </div> <!-- photo -->
        </div> <!-- prof_right -->
    </div>
</div> <!-- user profile -->
{@homepage}}
