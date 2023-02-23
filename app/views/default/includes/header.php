<!DOCTYPE html>
<html{@page_language}}>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="{@HOMEDIR}}themes/default/node_modules/bootstrap/dist/css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="{@HOMEDIR}}themes/default/css/framework.min.css" />
        <link rel="stylesheet" type="text/css" href="{@HOMEDIR}}themes/default/css/style.min.css" />
        {@head_metadata}} {@cookie_consent}}
        <title>{@title}}</title>
    </head>
    <body class="d-flex flex-column">
        <nav class="navbar navbar-expand-lg top-navigation">
            <div class="container-fluid">
                <a class="navbar-brand rounded" href="{@HOMEDIR}}"><img src="{@HOMEDIR}}themes/default/images/logo-147x29.png" width="147" height="29" alt="Vavok logo" /></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                      {@authentication}}
                  </ul>
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {@localization[localization]}}
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{@HOMEDIR}}users/changelang/?lang=en" rel="nofollow">English</a></li>
                            <li><a class="dropdown-item" href="{@HOMEDIR}}users/changelang/?lang=sr" rel="nofollow">Српски</a></li>
                        </ul>
                    </div>
                    <span><a class="btn navi-contact" href="{@HOMEDIR}}contact">{@localization[contact]}}</a></span>
                </div>
            </div>
        </nav>
        <div class="container side-space">