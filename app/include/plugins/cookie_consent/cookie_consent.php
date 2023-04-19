<?php
$this->head_data .= str_replace('  ', '', $this->replaceNewLines('
<style>
    .cookieConsentContainer {
        z-index: 999;
        width: 350px;
        min-height: 20px;
        box-sizing: border-box;
        padding: 30px 30px 30px 30px;
        background: #232323;
        overflow: hidden;
        position: fixed;
        bottom: 30px;
        right: 30px;
        display: none;
    }
    .cookieConsentContainer .cookieTitle {
        font-family: OpenSans, arial, "sans-serif";
        color: #FFFFFF;
        font-size: 22px;
        line-height: 20px;
        display: block;
    }
    .cookieConsentContainer .cookieTitle button {
        font-family: OpenSans, arial, "sans-serif";
        color: #FFFFFF;
        font-size: 22px;
        line-height: 20px;
        display: block;
    }
    .cookieConsentContainer .cookieDesc p {
        margin: 0;
        padding: 0;
        font-family: OpenSans, arial, "sans-serif";
        color: #FFFFFF;
        font-size: 13px;
        line-height: 20px;
        display: block;
        margin-top: 10px;
    } .cookieConsentContainer .cookieDesc button {
        font-family: OpenSans, arial, "sans-serif";
        color: #FFFFFF;
        text-decoration: underline;
    }
    .cookieConsentContainer .cookieButton button {
        display: inline-block;
        font-family: OpenSans, arial, "sans-serif";
        color: #FFFFFF;
        font-size: 14px;
        font-weight: bold;
        margin-top: 14px;
        background: #000000;
        box-sizing: border-box; 
        padding: 15px 24px;
        text-align: center;
        transition: background 0.3s;
    }
    .cookieConsentContainer .cookieButton button:hover { 
        cursor: pointer;
        background: #3E9B67;
    }

    @media (max-width: 980px) {
        .cookieConsentContainer {
            bottom: 0px !important;
            left: 0px !important;
            width: 100%  !important;
        }
    }
</style>
', ' '));

$this->head_data .= '<script>
var purecookieTitle = "{@localization[cookies]}}"; // Title
var purecookieDesc = "{@localization[purecookieDesc]}}"; // Description
var purecookieLink = \'{@localization[purecookieLink]}}\'; // Cookie policy link
var purecookieButton = "{@localization[purecookieButton]}}"; // Button text
</script>';
$this->head_data .= '<script src="' . $this->websiteHomeAddress() . '/themes/default/js/cookie-consent/cookie-consent.min.js" async></script>';