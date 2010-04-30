var institutions = [ "middlebury.edu", "miis.edu" ];

function doAction(name, args) {					
	var XMLHttpRequestObject = false;
	if (window.XMLHttpRequest) {
		XMLHttpRequestObject = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		XMLHttpRequestObject = new ActiveXObject("Microsoft.XMLHTTP");
	}
	
	if (XMLHttpRequestObject) {
		XMLHttpRequestObject.open("GET", "functions.php?name=" + name + "&args=" + escape(args));
		XMLHttpRequestObject.onreadystatechange = function()
		{
			if (XMLHttpRequestObject.readyState == 4 &&
				XMLHttpRequestObject.status == 200) {
				
				doResponse(XMLHttpRequestObject.responseXML);
			}
		}
		
		XMLHttpRequestObject.send(null);
	}
}

function createCode() {
	var institution = institutions[0];
	if (institutions.length > 1) {
		for (i = 1; i < institutions.length; i++) {
			var checkbox = document.getElementById("create_inst_" + i);
			if (checkbox.checked) {
				institution = institutions[i];
			}
		}
	}
	
	var public = "1";
	if (document.getElementById("public_no").checked) {
		public = "0";
	}

	doAction("create",
			"code=" + document.getElementById("create_code").value + ";" +
			"institution=" + institution + ";" +
			"url=" + escape(document.getElementById("create_url").value) + ";" +
			"public=" + public + ";" +
			"description=" + document.getElementById("create_description").value);
}

function deleteCode(code, institution) {
	var index = document.getElementById(institution + "/" + code).rowIndex;
	var table = document.getElementById("codes");

	doAction("delete", "code=" + code + ";institution=" + institution);
	
	for (i = 3; i >= 0; i--) {
		table.deleteRow(index + i);
	}
}

function createAlias() {
	var institution = institutions[0];
	if (institutions.length > 1) {
		for (i = 1; i < institutions.length; i++) {
			var checkbox = document.getElementById("alias_inst_" + i);
			if (checkbox.checked) {
				institution = institutions[i];
			}
		}
	}

	doAction("alias",
			"name=" + document.getElementById("alias_name").value + ";" +
			"code=" + document.getElementById("alias_code").value + ";" +
			"institution=" + institution);
}

function deleteAlias(alias, institution, code) {
	doAction("delalias", 
		"alias=" + alias + ";" +
		"institution=" + institution + ";" +
		"code=" + code);
		
	var ul = document.getElementById(institution + "/" + code + "_" + alias).parentNode;
	for (i = 0; i < ul.childNodes.length; i++) {
		if (ul.childNodes[i].id == institution + "/" + code + "_" + alias) {
			ul.removeChild(ul.childNodes[i]);
		}
	}
}

function addUser(code, institution) {
	var user = document.getElementById("adduser_" + institution + "/" + code).value;

	doAction("adduser",
		"user=" + user + ";" +
		"code=" + code + ";" +
		"institution=" + institution);
		
	var ul = document.getElementById("users_" + institution + "/" + code);
	var li = document.createElement("li");
	li.id = user + "_" + institution + "/" + code;
	li.innerHTML = user + "&nbsp;";
	
	var button = document.createElement("input");
	button.type = "button";
	button.value = "Delete";
	button.setAttribute('onclick', "deleteUser('" + code + "', '" + institution + "', '" + user + "');");
	li.insertBefore(button, li.lastChild.nextSibling);
	
	ul.insertBefore(li, ul.lastChild.nextSibling);
}

function deleteUser(code, institution, user) {
	doAction("deluser",
		"user=" + user + ";" + 
		"institution=" + institution + ";" +
		"code=" + code);
		
	var ul = document.getElementById("users_" + institution + "/" + code);
	for (i = 0; i < ul.childNodes.length; i++) {
		if (ul.childNodes[i].id == user + "_" + institution + "/" + code) {
			ul.removeChild(ul.childNodes[i]);
		}
	}
}

function update(code, institution) {
	var newinst = institutions[0];
	if (institutions.length > 1) {
		for (i = 1; i < institutions.length; i++) {
			var checkbox = document.getElementById("inst_" + i + "_" + institution + "/" + code);
			if (checkbox.checked) {
				newinst = institutions[i];
			}
		}
	}
	
	var public = "1";
	if (document.getElementById("public_no_" + institution + "/" + code).checked) {
		public = "0";
	}
	
	doAction("update",
		"code=" + code + ";" +
		"oldinst=" + institution + ";" +
		"url=" + escape(document.getElementById("upurl_" + institution + "/" + code).value) + ";" +
		"description=" + document.getElementById("updesc_" + institution + "/" + code).value + ";" +
		"public=" + public + ";" +
		"newinst=" + newinst);
}

function notify() {
	var notify = 0;
	if (document.getElementById("notify_yes").checked) {
		notify = 1;
	}

	doAction("notify", "notify=" + notify);
}

function doResponse(response) {
	var respdiv = document.getElementById("response");
	respdiv.innerHTML = "";
	
	var message = response.documentElement.firstChild;
	respdiv.style.color = message.attributes.getNamedItem("color").value;
	respdiv.innerHTML = message.firstChild.nodeValue;
}