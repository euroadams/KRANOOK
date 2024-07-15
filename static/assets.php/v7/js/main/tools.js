var global_spinner = '<span class="spinner custom-spinner spinner-dot spinner-xs"><i></i><i></i><i></i></span>';

///////RESISTOR SCRIPT BEGINS///////////

$(".rc3 select, .rc3-base [data-toggle=tab]").on("change click", function(){rc3();});

function rc3(){		
	
	$(".rc3").each(function(){

		var d1=d2=d3=col=mpl=mpl_v=tol_v=mpl_unit=tol=tempCo=$t=$to=$tos=divisionOperator=resultHtml=resistorVal='',
		d1Bar = 'digit1-bar', d2Bar = 'digit2-bar', d3Bar = 'digit3-bar', tolBar = 'tolerance-bar', xBar = 'multiplier-bar',
		xBar2 = xBar + '2', tempCoBar = 'temp-coeff-bar', d1Cls = 'digit1', d2Cls = 'digit2', d3Cls = 'digit3', 
		tolCls = 'tolerance', xCls = 'multiplier', tempCoCls = 'temp-coeff', fieldCls = 'field';

		$to = $(this);
		$tos = $to.closest("form").prev(".bars");

		$to.children("div").children("select").each(function(){	
				
			$t = $(this);

			if($t.hasClass(d1Cls)){

				d1 = $(this).val();
				col = d1.replace(/[^a-z]/gi, '');
				d1 = d1.replace(/[^0-9]/gi, '');
				$tos.children('.' + d1Bar).removeClass().addClass(d1Bar + ' ' + color2Css(col)).attr("data-val", d1);
				$t.removeClass().addClass(fieldCls + ' ' + d1Cls + ' ' + color2Css(col));

			}else if( $t.hasClass(d2Cls)){

				d2 = $(this).val();	
				col = d2.replace(/[^a-z]/gi, '');
				d2 = d2.replace(/[^0-9]/gi, '');
				$tos.children('.' + d2Bar).removeClass().addClass(d2Bar + ' ' + color2Css(col)).attr("data-val", d2);
				$t.removeClass().addClass(fieldCls + ' ' + d2Cls + ' ' + color2Css(col));

			}else if($t.hasClass(d3Cls)){

				d3 = $(this).val();	
				col = d3.replace(/[^a-z]/gi, '');
				d3 = d3.replace(/[^0-9]/gi, '');
				$tos.children('.' + d3Bar).removeClass().addClass(d3Bar + ' ' + color2Css(col)).attr("data-val", d3);
				$t.removeClass().addClass(fieldCls + ' ' + d3Cls + ' ' + color2Css(col));

			}else if($t.hasClass(xCls)){

				mpl = $(this).val();
				col = mpl.replace(/((x|÷)[0-9]+([a-z]{1})? )/gi, '');
				mpl_v = mpl.replace(/( [a-z]+)$/gi, '');
				mpl_unit = (/^(x[0-9]+k)/i.test(mpl))? 1000 : ((/^(x[0-9]+m)/i.test(mpl))? 1000000 : ((/^(x[0-9]+g)/i.test(mpl))? 1000000000 : 1));
				mpl = parseInt(mpl.replace(/[^0-9]/gi, '')) * mpl_unit;
				$tos.children('.' + xBar).removeClass().addClass(xBar + ' ' + color2Css(col)).attr("data-val", mpl_v);
				$tos.children('.' + xBar2).removeClass().addClass(xBar2 + ' ' + color2Css(col)).attr("data-val", mpl_v);
				$t.removeClass().addClass(fieldCls + ' ' + xCls + ' ' + color2Css(col));
				divisionOperator = ($t.children(":selected").attr("data-division-operator"))? true : divisionOperator;

			}else if($t.hasClass(tolCls)){

				tol = $(this).val();
				col = tol.replace(/[^a-z]/gi, '');
				tol_v = tol.replace(/( [a-z]+)$/gi, '');
				tol = parseFloat(tol.replace(/[^0-9.]/gi, ''));
				$tos.children('.' + tolBar).removeClass().addClass(tolBar + ' ' + color2Css(col)).attr("data-val", tol_v);
				$t.removeClass().addClass(fieldCls + ' ' + tolCls + ' ' + color2Css(col));

			}else if($t.hasClass(tempCoCls)){

				tempCo = $(this).val();
				col = tempCo.replace(/[^a-z]/gi, '');
				tempCo = parseFloat(tempCo.replace(/[^0-9]/gi, ''));
				$tos.children('.' + tempCoBar).removeClass().addClass(tempCoBar + ' ' + color2Css(col)).attr("data-val", tempCo);
				$t.removeClass().addClass(fieldCls + ' ' + tempCoCls + ' ' + color2Css(col));

			}
			
		})				
																														
		resistorVal = (divisionOperator? (parseInt(d1 + d2 + (d3?  d3 : '')) /  mpl) : (parseInt(d1 + d2 + (d3?  d3 : '')) *  mpl));

		resultHtml = '<div class="panel panel-pink"><h3 class="panel-head page-title">Result</h3><div class="panel-body hr-dividers">\
					<div><b>Resistance:</b><span class="blue"> ' + formatNumber(resistorVal, '', 2)
					+ ' ohms</span> &nbsp;&nbsp;</div><div><b>Maximum Resistance:</b><span class="red"> ' + formatNumber(resistorVal + (resistorVal * (tol / 100)))
					+ ' ohms</span> &nbsp;&nbsp;</div><div><b>Minimum Resistance:</b><span class="red"> '  + formatNumber(resistorVal - (resistorVal * (tol / 100)))
					+' ohms</span> &nbsp;&nbsp;</div><div><b>Tolerance:</b><span class=""> &#177; '+ tol + '%</span>'
					+ (tempCo? '</span> &nbsp;&nbsp;</div><div><b>Temperature Coefficient:</b><span class=""> '+ tempCo +' ppm/&deg;C</span>' : '')
					+ '</div></div></div>';
		
		$to.children(".rcc-res").html(resultHtml);			
		
	})
}


function color2Css(c){

	c = c? c.toLowerCase() : c;

	switch(c){

		case 'black': c = 'blk'; break;

		case 'brown': c = 'brw'; break;

		case 'red': c = 'red'; break;

		case 'orange': c = 'org'; break;

		case 'yellow': c = 'yel'; break;

		case 'green': c = 'grn'; break;

		case 'blue': c = 'blu'; break;

		case 'violet': c = 'vio'; break;

		case 'gray': c = 'gry'; break;

		case 'white': c = 'wht'; break;

		case 'gold': c = 'gld'; break;

		case 'silver': c = 'slv'; break;

	}
	
	return 'rcc-' + c;

}
///////RESISTOR SCRIPT ENDS///////////



///////CALENDAR SCRIPT BEGINS///////////
var glob_yrLen = 200;
//ENFORCE CALENDAR WITH SERVER TIME
var globRefTime = parseInt(dom("serveT").innerHTML) * 1000;

function monthGen(){

	var month=sindex=mtoday=monthArr='';
	month = dom('month');
	sindex = 0;
	mtoday = new Date(globRefTime).getMonth();
	monthArr = ['JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER'];
	month.options[0] = new Option('select month');

	for(var ii = 0; ii < monthArr.length; ii++){

		month.options[month.length] = new Option(monthArr[ii]);

		if(mtoday == ii)
			sindex = ii;
		
	}

	month.selectedIndex = sindex + 1;

}




function yearGen(){

	var year=ytoday=start=sindex='';
	year = dom('year');
	ytoday = new Date(globRefTime).getFullYear();
	start = 1900;
	sindex = 0;
	year.options[0] = new Option('select year');

	for(var ii = 0; ii < glob_yrLen; ii++){

		year.options[year.length] = new Option(start + ii);

		if((start + ii == ytoday))
			sindex = ii + 1;
		
	}

	year.selectedIndex = sindex;
}




function weekGen(){

	var week=weekarr='';
	week = dom('wks');
	weekarr = ['Sun','Mon','Tue','Wed','Thur','Fri','Sat'];

	for (var ii = 0; ii < weekarr.length; ii++)
		week.innerHTML += '<th>' + weekarr[ii] + '</th>';
	
}



function monthlen(year, month){

	var oneDay_ms=curm=nxtm=len='';
	oneDay_ms = (1000*60*60*24);
	curm = new Date(year, month, 1);
	nxtm = new Date(year, month + 1, 1);
	len = Math.ceil((nxtm.getTime() - curm.getTime()) / oneDay_ms);
	return len;

}


function fday(year, month){

	var fday = new Date(year, month, 1);
	return fday.getDay();

}



function popuCal(meta){

	meta = meta? meta.toLowerCase() : meta;

	if(meta == 'ini'){
	
		monthGen(); 
		yearGen(); 
		weekGen();
	
	}else if(meta == '+y')
		navCalYear('+');

	else if(meta == '-y')
		navCalYear('-');

	else if(meta == '+m')
		navCalMonth('+');

	else if(meta == '-m')
		navCalMonth('-');
	
	var smonthIndex=smonth=syearIndex=syear=calHead=mnthLen=firstDay=calBody=counter=done=newR=newC=dt=mt=yt=DT=YMT='';
	smonthIndex = dom('month').selectedIndex;
	smonth = dom('month').options[smonthIndex].text;
	syearIndex = dom('year').selectedIndex;
	syear = dom('year').options[syearIndex].text;
	DT = new Date(globRefTime);
	dt = DT.getDate();
	mt = DT.getMonth();
	yt = DT.getFullYear();
	YMT = (mt == smonthIndex - 1 && yt == syear)? true : false;
	sdate = YMT? dt : 1;
	calHead = dom('cal-head');
	calHead.innerHTML = '<marquee behavior="alternate" direction="right" scrollamount="3"><span id="mq-sdate">' + sdate + '</span> ' + smonth + ' ' + syear + '</marquee>';
	mnthLen = monthlen(syear, smonthIndex - 1);
	firstDay = fday(syear, smonthIndex - 1);
	//dom('chking').innerHTML = mnthLen + ' ' + firstDay;
	calBody = dom('cal-body');

	while(calBody.rows.length > 0){

		calBody.deleteRow(0);

	}
	
	if(syearIndex == 0 || smonthIndex == 0)
		return true;
	counter = 1; done = false;

	while(!done){

		newR = calBody.insertRow(calBody.rows.length);

		for(var ii = 0; ii < 7; ii++){

			newC = newR.insertCell(newR.cells.length);

			if(calBody.rows.length == 1 && ii < firstDay){

				newC.innerHTML = '';
				continue;

			}

			if(counter == mnthLen)
				done = true;
			
			newC.innerHTML = (counter > mnthLen)? '' : '<button  class="sdate-btn ' + ((YMT && dt == counter)? 'active' : '') + '" onclick="updateOutput(this.innerHTML,this)">' + (counter++) + '</button>';

		}
	}
	
	updateOutput(sdate);

}


function updateOutput(sdate,sdClickEl){

	var smonthIndex=smonth=syearIndex=syear=output=idate=leadZero=curVal='';
	sdClickEl = sdClickEl || false;

	if(sdClickEl){

		$(".sdate-btn").removeClass("active-select active");
		$(sdClickEl).addClass("active-select");

	}

	smonthIndex = dom('month').selectedIndex;
	smonth = dom('month').options[smonthIndex].text;
	syearIndex = dom('year').selectedIndex;
	syear = dom('year').options[syearIndex].text;
	output = dom('output');
	curVal = output.value;
	idate = curVal.substr(0,2);
	
	if(idate > 0 && !sdate){

		sdate = idate;
		leadZero = sdate.charAt(0);

		if(leadZero == 0)
			sdate = sdate.charAt(1);					
		

	}			

	if(curVal.length == 10)
		output.value = '';			

	if(smonthIndex < 10)
		smonthIndex = '0' + smonthIndex;			

	if(sdate < 10)
		sdate = '0' + sdate + ((!sdate)? 1 : '');			

	output.value = sdate + '/' + smonthIndex + '/' + syear;
	dom('mq-sdate').textContent = sdate;			

}



function navCalMonth(t){			

	var month=nxtIndex=curIndex=inc=dec='';
	inc = (t == '+')? true : false;
	dec = (t == '-')? true : false;
	month = dom('month');
	curIndex = month.selectedIndex;	
	nxtIndex = ((dec && curIndex == 1)? 12 : ((dec)? (curIndex - 1) : 
	(inc && curIndex == 12)? 1 : ((inc)? (curIndex + 1) : 1)));			
	month.selectedIndex = nxtIndex;				
			
}


function navCalYear(t){			

	var yr=nxtIndex=curIndex=inc=dec='';
	inc = (t == '+')? true : false;
	dec = (t == '-')? true : false;
	yr = dom('year');
	curIndex = yr.selectedIndex;
	nxtIndex = ((dec && curIndex == 1)? glob_yrLen : ((dec)? (curIndex - 1) : 
	(inc && curIndex == glob_yrLen)? 1 : ((inc)? (curIndex + 1) : 1)));		
	yr.selectedIndex = nxtIndex;				
			
}
///////CALENDAR SCRIPT ENDS///////////



///////DEVICE INFO SCRIPT BEGINS///////////
function deviceInfos(){			

	var stat = '', acc = '', tmp;
	var scrn = dom("di-screen-js");
	var titleCls = 'class="' + scrn.getAttribute('data-title-color') + '"';
	var offColor = scrn.getAttribute('data-off-color');
	var offCls = 'class="' + offColor + '"';
	var wdholder = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
	var htholder = window.innerHeight  || document.documentElement.clientHeight || document.body.clientHeight;
	
	acc += '<p><span ' + titleCls + '>YOUR WINDOW RESOLUTION IS: </span>' +
		wdholder + ' x ' + htholder + '</p>';

	acc += '<p><span ' + titleCls + '>YOUR SCREEN HEIGHT IS: </span>' + 
		screen.height + 'px </p>';

	acc += '<p><span ' + titleCls + '>YOUR SCREEN WIDTH IS: </span>' +
		screen.width + 'px </p>';

	acc += '<p><span ' + titleCls + '>YOUR SCREEN AVAILABLE HEIGHT IS: </span>' +
		screen.availHeight + 'px </p>';

	acc += '<p><span ' + titleCls + '>YOUR SCREEN AVAILABLE WIDTH IS: </span>' +
		screen.availWidth + 'px </p>';

	acc += '<p><span ' + titleCls + '>YOUR SCREEN COLOR DEPTH IS: </span>' +
		screen.colorDepth + 'px </p>';

	acc += '<p><span ' + titleCls + '>YOUR SCREEN PIXEL DEPTH IS: </span>' +
		screen.pixelDepth + 'px </p>';

	stat = navigator.onLine;
	stat = stat? 'Online' : 'Offline';
								
	acc += '<p ' + (stat == 'Offline'? offCls : '') + '><span ' + titleCls + '>YOUR BROWSER IS: </span>' + stat + '</p>';

	stat = navigator.cookieEnabled;
	stat = stat? 'Enabled' : 'Disabled';

	acc += '<p ' + (stat == 'Disabled'? offCls : '') + '><span ' + titleCls + '>YOUR BROWSER IS COOKIE: </span>' + stat + '</p>';

	acc += '<p><span ' + titleCls + '>YOUR BROWSER NAME IS: </span>' +
		navigator.appName + ' <span ' + titleCls + '>WITH CODE NAME: </span>' + 
		navigator.appCodeName + '</p>';

	acc += '<p><span ' + titleCls + '>YOUR BROWSER ENGINE NUMBER IS: </span>' +
		navigator.product + '</p>';

	acc += '<p><span ' + titleCls + '>YOUR BROWSER VERSION IS: </span>' +
		navigator.appVersion + '</p>';

	acc += '<p><span ' + titleCls + '>YOUR BROWSER USER-AGENT IS: </span>' +
		navigator.userAgent + '</p>';

	acc += '<p><span ' + titleCls + '>YOUR BROWSER PLATFORM/ OPERATING SYSTEM IS: </span>' +
		navigator.platform + '</p>';

	acc += '<p><span ' + titleCls + '>YOUR BROWSER LANGUAGE IS: </span>' +
		navigator.language + '</p>';

	stat = navigator.javaEnabled();
	stat = stat? 'Enabled' : 'Disabled';
	acc += '<p ' + (stat == 'Disabled'? offCls : '') + '><span ' + titleCls + '>YOUR BROWSER IS JAVA: </span>' + stat + ' </p>';

	stat = (typeof(Storage) !== 'undefined')? 'Yes' : 'No';	
	acc += '<p ' + (stat == 'No'? offCls : '') + '><span ' + titleCls + '>WEB STORAGE SUPPORT: </span>' + stat + '</p>';
	acc += '<p><span ' + titleCls + '>AD BLOCKER: </span><span id="di-ad-blocker" data-off-color="' + offColor + '">Enabled</span></p>';
	scrn.innerHTML = acc;

}	
	
///////DEVICE INFO SCRIPT ENDS///////////



///////GEO LOC SCRIPT BEGINS///////////
	
/////FUNCTION TO FETCH USERS GEOLOCATIONS///////
	
function getLocationNames()	{
		
	dom("geoposname").innerHTML = "Current Location: "+geoplugin_city()+" "+geoplugin_region()+" "+geoplugin_countryName();	
	
}
	

var geoAlerts = dom("show_geoloc_alerts");
var geoMapHolder = dom("show_geoloc_map");
/////REQUEST STATIC/DYNAMIC MAP VERSION////////
	
function getLocation($static, e){

	var options = {

		enableHighAccuracy: true,
		timeout: 10000,
		maximumAge: 50000

	};

	geoAlerts.innerHTML = "";
	
    if(navigator.geolocation){

		geoMapHolder.innerHTML = '<b class="red">Loading map ' + global_spinner + '</b>';
		navigator.geolocation.getCurrentPosition(($static? loadStatic : loadDynamic), geoError, options);
		//navigator.geolocation.watchPosition(($static? loadStatic : loadDynamic), geoError, options);
	  
    } else
        geoAlerts.innerHTML = '<span class="alert alert-danger">Geolocation is not enabled or supported by this browser!.</span>';
	
}




////DYNAMIC MAP////
function loadDynamic(position){

	var lat=lon=latlon=mapholder=geoOptions=map=mapType=marker='';
    lat = position.coords.latitude;
    lon = position.coords.longitude;
	geoAlerts.innerHTML = '<div class="success">Position: Latitude ' + lat + '&deg; Longitude ' + lon + '&deg; ' + '</div>';
    latlon = new google.maps.LatLng(lat, lon);
    mapholder = geoMapHolder;
    mapholder.style.height = '900px';
    mapholder.style.width = '1500px';
	mapType = dom("mapType").options[dom("mapType").selectedIndex].text;

    geoOptions = {

		center: latlon,
		zoom: 14,
		enableHighAccuracy: true,
		mapTypeId: google.maps.MapTypeId[mapType],
		mapximumAge: 30000,
		timeout: 25000,
		mapTypeControl: false,
		navigationControlOptions: {style:google.maps.NavigationControlStyle.SMALL}

    }
    
    map = new google.maps.Map(mapholder, geoOptions);
    marker = new google.maps.Marker({position:latlon,map:map,title:"You are here!"});
	
}
	
///////STATIC MAP/////////	
function loadStatic(position){

	var lat=lon=latlon='';
	lat = position.coords.latitude;
    lon = position.coords.longitude;
	geoAlerts.innerHTML = '<div class="success">Position: Latitude ' + lat + '&deg; Longitude ' + lon + '&deg; ' + '</div>';
	latlon = lat + ',' + lon;
	var mapType = dom("mapType").options[dom("mapType").selectedIndex].text;
    var imgSrc = 'https://maps.googleapis.com/maps/api/staticmap?center='+latlon+'&zoom=14&size=1500x900&maptype='+mapType+'&sensor=false';
    /*img_src = 'https://maps.googleapis.com/maps/api/staticmap?center=Brooklyn+Bridge,New+York,NY&zoom=13&size=600x300&maptype=roadmap\
					&markers=color:blue|label:S|40.702147,-74.015794&markers=color:green|label:G|40.711614,-74.012318\
					&markers=color:red|label:C|40.718217,-73.998284&key=YOUR_API_KEY';
	*/
	
    geoMapHolder.innerHTML = '<img src="' + imgSrc + '" alt="google map" />';
		
}	
	
function geoError(error){
    
	var x = geoAlerts;
	geoMapHolder.innerHTML='';
	
	switch(error.code) {
		
        case error.PERMISSION_DENIED:
            x.innerHTML = '<span class="alert alert-danger">Sorry! The User denied the request for Geolocation!.</span>';
            break;
		
        case error.POSITION_UNAVAILABLE:
            x.innerHTML = '<span class="alert alert-danger">Please turn on location services on your device to proceed!.</span>';
            break;
		
        case error.TIMEOUT:
            x.innerHTML = '<span class="alert alert-danger">Sorry! The request to get user location timed out!.</span>';
            break;
		
        case error.UNKNOWN_ERROR:
            x.innerHTML = '<span class="alert alert-danger">Sorry! An unknown error occurred!.</span>';
            break;

    }
	
}	

///////GEO LOC SCRIPT ENDS///////////


///////LENGTH CONVERTER SCRIPT BEGINS///////////




///////LENGTH CONVERTER SCRIPT ENDS///////////


