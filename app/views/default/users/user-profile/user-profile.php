<style>
    .user-profile {
        margin-bottom: 15px;
    }
    .user-profile p:empty {
        display: inline;
    }
    .photo img {
        max-width: 130px;
        max-height: 130px;
    }
</style>
<div class="user-profile">
    <div class="d-block b rounded">
        {@sex-img}} {@first_name}} {@last_name}} <em>{@nickname}}</em> {@user-online}}
    </div>
    <div class="row mt-5">
        <div class="col">
            <p>{@regCheck}}</p>
            <p>{@banned}}</p>
            <p>{@personalStatus}}</p>
            <p>{@sex}}: {@usersSex}}</p>
            <p>{@city}}</p>
            <p>{@about}}</p>
            <p>{@birthday}}</p>
            <p>{@browser}}</p>
            <p>{@siteSkin}}</p>
            <p>{@site}}</p>
            <p>{@regDate}}</p>
            <p>{@last_visit}}</p>
            <p>{@ip-address}}</p>
            <p>{@userMenu}}</p>
        </div>
        <div class="col">
            <div class="photo">
                {@userPhoto}}
            </div>
        </div>
    </div>
</div>
{@homepage}}
