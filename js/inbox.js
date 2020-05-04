/*
(c) vavok.net
*/
window.onload=function() {
	var objDiv = document.getElementById("message_box");
	objDiv.scrollTop = objDiv.scrollHeight;
}

var GetMsgUrl = "receive_pm.php";

var lastID = 0; // initial value will be replaced by the latest message id

const queryString = window.location.search;
const urlParams = new URLSearchParams(queryString);
const Who = urlParams.get('who'); // with who is user chatting

window.onload = initJavaScript;

function initJavaScript() {
	document.forms['message-form'].elements['pmtext'].setAttribute('autocomplete','off'); //this non standard attribute prevents firefox' autofill function to clash with this script
	receiveChatText(); //initiates the first data query
}

//initiates the first data query
function receiveChatText() {
	if (httpReceiveMsg.readyState == 4 || httpReceiveMsg.readyState == 0) {
	  	httpReceiveMsg.open("GET",GetMsgUrl + '?lastid=' + lastID + '&who=' + Who, true);
	    httpReceiveMsg.onreadystatechange = handlehhttpReceiveMsg; 
	  	httpReceiveMsg.send(null);
	}
}

//deals with the servers' reply to requesting new content
function handlehhttpReceiveMsg() {
  if (httpReceiveMsg.readyState == 4) {
    results = httpReceiveMsg.responseText.split(':|:'); //the fields are seperated by :|:

    if (results.length > 2) {
	    for(i=0;i < (results.length);i=i+5) { //goes through the result one message at a time

	    	if (lastID !== 0) {
	    		insertNewMessage(results[i],results[i+1], results[i+3], results[i+4]); //inserts the new content into the page
	    	}

	    	lastID = results[i+2];

	    }

    }

    setTimeout('receiveChatText();', 2000); //executes the next data query in 4 seconds
  }
}


//inserts the new content into the page
//inserts the new content into the page
function insertNewMessage(liName, liText, id, time_sent) {
	insertO = document.getElementById("outputList");
	oSpan = document.createElement('span');
	oSpan.className = 'msg'; // oSpan.setAttribute('class','name');
	oName = document.createTextNode(liName);
	oText = document.createTextNode(liText);
	oSpan.appendChild(oName);
	oSpan.innerHTML = ('<a href="../pages/user.php?uz=' + id + '">' + liName + '</a> ' + time_sent + '<br />' + liText + '<hr />');
	insertO.insertBefore(oSpan, insertO.firstChild);
}

//deals with the servers' reply to sending a comment
function handlehHttpSendChat() {
  if (httpSendChat.readyState == 4) {
  	receiveChatText(); //refreshes the chat after a new comment has been added (this makes it more responsive)
  }
}


//initiates the XMLHttpRequest object
function getHTTPObject() {
  var xmlhttp;

  if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
    try {
      xmlhttp = new XMLHttpRequest();
    } catch (e) {
      xmlhttp = false;
    }
  }
  return xmlhttp;
}

function send_message() {
    var elements = document.getElementsByClassName("send_pm");
    var formData = new FormData();

    for(var i=0; i<elements.length; i++) {
        formData.append(elements[i].name, elements[i].value);
    }
    var xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = function()
        {
            if(xmlHttp.readyState == 4 && xmlHttp.status == 200) {
                //alert(xmlHttp.responseText);
            }
        }
        xmlHttp.open("post", "send_pm.php"); 
        xmlHttp.send(formData); 

        document.forms['message-form'].elements['pmtext'].value = '';
}


// initiates the two objects for sending and receiving data
var httpReceiveMsg = getHTTPObject();

