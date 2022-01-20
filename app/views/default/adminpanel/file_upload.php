{@header}}
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