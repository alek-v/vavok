<!DOCTYPE html>
<html{@page_language}}>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="{@HOMEDIR}}themes/default/node_modules/bootstrap/dist/css/bootstrap.min.css" />
        <link rel="stylesheet" type="text/css" href="{@HOMEDIR}}themes/default/css/framework.min.css" />
        <link rel="stylesheet" type="text/css" href="{@HOMEDIR}}themes/default/css/style.min.css" />
        <!-- this page head data -->
        <script type="text/javascript" src="/include/js/jquery.js"></script>
        <script type="text/javascript" src="/include/js/jquery.form.min.js"></script>
        <script type="text/javascript">
        $(document).ready(function() { 
            var progressbox     = $('#progressbox');
            var progressbar     = $('#progressbar');
            var statustxt       = $('#statustxt');
            var completed       = '0%';
            
            var options = { 
                    target:   '#output',   // target element(s) to be updated with server response 
                    beforeSubmit:  beforeSubmit,  // pre-submit callback 
                    uploadProgress: OnProgress,
                    success:       afterSuccess,  // post-submit callback 
                    resetForm: true        // reset the form after successful submit 
                }; 
                
             $('#MyUploadForm').submit(function() { 
                    $(this).ajaxSubmit(options);              
                    // return false to prevent standard browser submit and page navigation 
                    return false; 
                });
            
        //when upload progresses    
        function OnProgress(event, position, total, percentComplete)
        {
            //Progress bar
            progressbar.width(percentComplete + '%') //update progressbar percent complete
            statustxt.html(percentComplete + '%'); //update status text
            if(percentComplete>50)
                {
                    statustxt.css('color','#fff'); //change status text to white after 50%
                }
        }

        //after succesful upload
        function afterSuccess()
        {
            $('#submit-btn').show(); //hide submit button
            $('#loading-img').hide(); //hide submit button

        }

        //function to check browser and progres
        function beforeSubmit(){
            //check whether browser fully supports all File API
           if (window.File && window.FileReader && window.FileList && window.Blob)
            {

                if( !$('#imageInput').val()) //check empty input filed
                {
                    $("#output").html("Are you kidding me?");
                    return false
                }
                
                //Progress bar
                progressbox.show(); //show progressbar
                progressbar.width(completed); //initial value 0% of progressbar
                statustxt.html(completed); //set status text
                statustxt.css('color','#000'); //initial color of status text
                        
                $('#submit-btn').hide(); //hide submit button
                $('#loading-img').show(); //hide submit button
                $("#output").html("");  
            }
            else
            {
                //Output error to older unsupported browsers that doesn't support HTML5 File API
                $("#output").html("Please upgrade your browser, because your current browser lacks some new features we need!");
                return false;
            }
        }

        //function to format bites
        function bytesToSize(bytes) {
           var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
           if (bytes == 0) return '0 Bytes';
           var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
           return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
        }

        }); 
        </script>
        <style>
        #upload-wrapper {
            width: 50%;
            margin-right: auto;
            margin-left: auto;
            margin-top: 50px;
            background: #F5F5F5;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 1px 1px 3px #AAA;
        }
        #upload-wrapper h3 {
            padding: 0px 0px 10px 0px;
            margin: 0px 0px 20px 0px;
            margin-top: -30px;
            border-bottom: 1px dotted #DDD;
        }
        #upload-wrapper input[type=file] {
            border: 1px solid #DDD;
            padding: 6px;
            background: #FFF;
            border-radius: 5px;
        }
        #upload-wrapper #submit-btn {
            border: none;
            padding: 10px;
            background: #61BAE4;
            border-radius: 5px;
            color: #FFF;
        }
        #output{
            padding: 5px;
            font-size: 12px;
        }
        #output img {
            border: 1px solid #DDD;
            padding: 5px;
        }
        /* progress bar style */
        #progressbox {
            border: 1px solid #92C8DA;
            padding: 1px; 
            position:relative;
            width:400px;
            border-radius: 3px;
            margin: 10px;
            display:none;
            text-align:left;
        }
        #progressbar {
            height:20px;
            border-radius: 3px;
            background-color: #77E0FA;
            width:1%;
        }
        #statustxt {
            top:3px;
            left:50%;
            position:absolute;
            display:inline-block;
            color: #000000;
        }
        </style>
        <!-- end of this page head data -->
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
                            <li><a class="dropdown-item" href="{@HOMEDIR}}users/changelang/?lang=en" rel="nofollow"><img src="{@HOMEDIR}}themes/default/images/flag_great_britain_32.png" alt="english language" /> English</a></li>
                            <li><a class="dropdown-item" href="{@HOMEDIR}}users/changelang/?lang=sr" rel="nofollow"><img src="{@HOMEDIR}}themes/default/images/serbia_flag_32.png" alt="српски језик" /> Српски</a></li>
                        </ul>
                    </div>
                    <span><a class="btn navi-contact" href="{@HOMEDIR}}contact">{@localization[contact]}}</a></span>
                </div>
            </div>
        </nav>
        <div class="container">
            <p>{@localization[chfile]}}:</p>
            <div id="loading"></div>
            <form action="/adminpanel/finish_upload" method="post" enctype="multipart/form-data" id="MyUploadForm">
            <input name="upload"  id="imageInput"  type="file" size="20">
            <br><br>
            Rename file (optional):
            <input name="rename" size="20" />
            <br />
            Convert to lower case:
            <input type="checkbox" name="lowercase" value="lower" />
            <br />
            Image option - > resize to width:
            <input name="width" size="20" /><br /><br />
            <input type="submit"  id="submit-btn" value="Upload" />
            <img src="../themes/images/img/loading.gif" id="loading-img" style="display:none;" alt="Please Wait"/>
            </form>
            <div id="progressbox" style="display:none;"><div id="progressbar"></div><div id="statustxt">0%</div></div>
            <div id="output"></div>
            {@content}}
{@footer}}