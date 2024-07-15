var INCR = 0, T = 0, xmlhttp = '';

//JS AJAX
function ajax(m, url, qstr, cb, rh_nv){
	
	var mLC="";
	mLC	= m.toLowerCase();
	if(mLC == 'get' && qstr){
		url += '?' + qstr;
	}
	if(window.XMLHttpRequest){
		// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	}
	else{
		// code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}

	xmlhttp.onreadystatechange = cb;

	xmlhttp.open(m, url, true);
	
	//SET HEADERS///
	for(x in rh_nv){
	
		xmlhttp.setRequestHeader(x, rh_nv[x]);
	
	}
	
	xmlhttp.setRequestHeader('X-REQUESTED-WITH', 'XMLHttpRequest');
	//SET HEADER BELOW TO POST DATA LIKE AN HTML FORM///
	//xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xmlhttp.send((mLC == 'post')? qstr : '');
	
}

(function updateNavTime(){
	
	ajax('POST', '/' + dom("html", 't')[0].getAttribute("data-servert-loader"), '', function(){
	
		if(xmlhttp.readyState == 4 && xmlhttp.status == 200){
	
			var TD_arr = '';
			TD_arr  = xmlhttp.responseText.split("|");
			dom("serveT").innerHTML = TD_arr[0];
			dom("date").innerHTML = TD_arr[1];
			//REFRESH INCREMENT VERY VITAL
			INCR = 0;
			//REVALIDATE CLIENT WITH SERVER TIME EVERY 2MINS
			setTimeout(updateNavTime, 120000);
	
		}
	});		
})();


function navWelcome(){
	
	var t, H, H_indx, Tsuf, curGreeting, greeting = 'Good ', am = 'am'; pm = 'pm', 
	serverTDom = 'serveT', serverTSufDom = 'serveTS', greetingDom = 'timed-greeting';

	//INCREMENT EVERY SECOND(1000ms)
	INCR = INCR + 1000;

	//CONVERT SERVER TIME TO ms AND INCREMENT EVERY SECONDS
	t = T = parseInt(dom(serverTDom).innerHTML) * 1000 + INCR;
	t = new Date(t).toTimeString();
	
	//GET TIME PART hh:mm:ss
	t = t.substring(0, 8);
	
	//TRIM LEADING HOUR ZERO
	H_indx = parseInt(t.substring(0, 1));
	t = (H_indx == 0)? t.substring(1) : t;
	
	//GET HOUR PART
	H = t.split(":")[0];

	//GET SERVER AM/PM
	Tsuf = dom(serverTSufDom).innerHTML;
	Tsuf = Tsuf.toLowerCase();

	//APPEND AM/PM,TIMEZONE....
	t += '' + Tsuf + ' (WAT)';
	
	//DECIDE GREETINGS
	if(H < 12 && Tsuf == am)
		greeting += "morning ";
	else if(((H >= 12 && H < 17)  || (H >= 12 && H < 5)) && Tsuf == pm)
		greeting += "afternoon ";
	else if(((H >= 17 && H <= 23) || (H >= 5)) && Tsuf == pm)
		greeting += "evening ";

	//ENSURE PROPER TIME SUFFIX SWITCHING
	if(H >= 12 && Tsuf == am)
		dom(serverTSufDom).innerHTML = 'PM';
	else if((H >= 0 && H <= 11) && Tsuf == pm)
		dom(serverTSufDom).innerHTML = 'AM';

	curGreeting = dom(greetingDom, "c")[0].innerHTML;

	if(greeting != curGreeting){

		dom(greetingDom, "c")[0].innerHTML = greeting;
		dom(greetingDom, "c")[1].innerHTML = greeting;

	}

	dom("time").innerHTML = t;
	
}


navWelcome();
var set_call = setInterval(navWelcome, 1000);

dom("uagent").innerHTML = "<span class='prime'><i>You are browsing with:</i> </span>" +
navigator.userAgent + "<a target='_blank' href='/tools/device-info' class='links'>(details)</a><br/>"+ new Date(T) + '.';