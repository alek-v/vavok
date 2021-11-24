{@header}}
<form id="message-form" method="post" action="send_pm.php?who={@who}}">
<div class="form-group">
<label for="chatbarText"></label>
<input name="pmtext" class="send_pm form-control" id="chatbarText" placeholder="{@website_language[message]}}..." {@send_readonly}}/>
</div>
<input type="hidden" name="who" id="who" value="{@who}}" class="send_pm" />

<input type="hidden" name="lastid" id="lastid" value="{@who}}" />
<button type="submit" class="btn btn-primary" onclick="send_message(); return false;">{@website_language[send]}}</button>
</form><br />
<div id="message_box" class="message_box" style="overflow-y: scroll; height:400px;overflow-x: hidden;">
{@content}}
<p id="outputList" class="outputList"></p>
</div>
{@footer}}