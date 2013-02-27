    // **************************************************************
    function CheckFieldType() {
    // Verify that all number forms are numbers and are within range
    // If error, display error and return false.
    // You don't need to do this at the client, but it is quicker than using ajax at the server.
       var qid = document.getElementById('field_id').value;
	   
		var url='index.php';
		var params = 'option=com_redform&controller=values&task=checkfieldtype&field_id='+qid+'&format=raw';
		ajaxFunction(url, params);
    }
	
    // **************************************************************
    function ajaxFunction(url, params){
       var xmlHttp;
       try{
          // Opera 8.0+, Firefox, Safari
          xmlHttp = new XMLHttpRequest();
       } catch (e){
          // Internet Explorer Browsers
          try{
             xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
          } catch (e) {
             try{
                xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
             } catch (e){
                // Something went wrong
                alert("Incompatible Browser");
                var ajaxDisplay = document.getElementById('ajaxDiv');
                statusDisplay.innerHTML = "Incompatible Browser";
                return false;
             }
          }
       }
	   xmlHttp.open("POST",url,true);
	   
		/* Send the proper header information along with the request */
		xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlHttp.setRequestHeader("Content-length", params.length);
		xmlHttp.setRequestHeader("Connection", "close");
		xmlHttp.onreadystatechange = function() {
			if (xmlHttp.readyState == 4  && xmlHttp.status==200) {
				var ajaxResponse = trim(xmlHttp.responseText);
				document.getElementById('newfieldtype').innerHTML=ajaxResponse;
			}
		}
		xmlHttp.send(params);
    }


    // **************************************************************
    function trim(str) {
       str = str.replace(/^\s+/, '');
       for (var i = str.length - 1; i > 0; i--) {
          if (/\S/.test(str.charAt(i))) {
             str = str.substring(0, i + 1);
             break;
          }
       }
       return str;
    }