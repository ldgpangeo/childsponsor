
// Get the HTTP Object

function getHTTPObject(){
	if (window.ActiveXObject) return new ActiveXObject("Microsoft.XMLHTTP");
	else if (window.XMLHttpRequest) return new XMLHttpRequest();
	else {
		alert("Your browser does not support AJAX.");
		return null;
	}
}



// Change the value of the outputText field

function setOutput(){
	if(httpObject.readyState == 4){
//		alert(httpObject.responseText);
		document.getElementById('payment_area').innerHTML = httpObject.responseText;
//		$("ajax_text").replaceWith(httpObject.responseText);
	}
}



// Implement business logic

function doWork(id){
	httpObject = getHTTPObject();
	if (httpObject != null) {
		httpObject.open("GET", "lib/get_payment.php?item_id="+id , true);
		httpObject.send(null);
		httpObject.onreadystatechange = setOutput;
	}
}



var httpObject = null;

