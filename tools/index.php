<?php 

require_once ('../page-common-headers.php');

$ACCOUNT->authorizeSiteTakeDownAccess();

/////////VARIABLE INITIALIZATION//////////////

$pageUrl=$pageUrlLowerCase=$path_cleaned=$std_link="";

$subDomain = 'tools';
$selfHostedRsrcRoot = $SITE->getResourceRoot();
$devHostRsrcRoot = $SITE->getResourceRoot('dev');
$cacheVer = $SITE->getCacheVer();
$cacheVerToolsJs = $SITE->getCacheVer('tools-js');
$linkToolsJs = '<script type="text/javascript" src="'.$selfHostedRsrcRoot.'js/main/tools.min.js'.$cacheVerToolsJs.'"></script>
<script type="text/javascript" src="'.$selfHostedRsrcRoot.'js/main/prebid-ads.js'.$cacheVerToolsJs.'"></script>';
$toolsSubNav = '<li><a href="/'.$subDomain.'">Site Tools</a></li>';
$path = $ENGINE->get_page_path('page_url', '', true);
		
$urlList = array(//FORMAT => urlSlug:urlSlugLabel:urlSlugIcon:cond2Add
	($resistorCodeSlug = 'resistor-color-code-calculator').':resistor color code calculator:<i class="fas fa-th"></i>', 
	($calenderSlug = 'calendar').':calendar:<i class="fas fa-calendar"></i>',
	($deviceInfoSlug = 'device-info').':device infos:<i class="fas fa-laptop"></i>',
	($geoLocSlug = 'geolocation').':geo locator:<i class="fas fa-compass"></i>',
	($whatsAppSlug = 'whatsapp').':whatsapp:<i class="fab fa-whatsapp"></i>',
	($qrCodeSlug = 'qr-code').':qr code:<i class="fa fa-qrcode"></i>'
);


if($path){	
	
	/***GET/HANDLE THE REQUEST URL*******/
	$pagePathArr = explode("/", $path);
	
	if(is_array($pagePathArr) && isset($pagePathArr[0]))
		$subDomain = strtolower($pagePathArr[0]);				
	
	$pageUrl = (isset($pagePathArr[1]))? $pagePathArr[1] : '';
	
	$pageUrlLowerCase = strtolower($pageUrl);
	
	$fall2Index = ($subDomain && !$pageUrlLowerCase)? true : false;

	if($fall2Index)
		$pageUrlLowerCase = 'home-index';
	
}


switch($pageUrlLowerCase){
	
	case $resistorCodeSlug:
	
		$rccBlankImg = $GLOBAL_mediaRootFav.'rcc-blank-img.png';													
		
		$divOp = '&#247;';
		$plusMinus = '&#177;';
		$nameKey = 'name';
		$clsKey = 'class';
		$digKey = 'bandDigit';
		$mulKey = 'multiplier';
		$tolKey = 'tol';
		$tCoKey = 'tempCo';
		$tCoUnitKey = 'tCoUnit';

		$metaArr = array(				

			($blackKey = 'black') => array($nameKey => 'Black', $clsKey => 'rcc-blk', $digKey => '0', $mulKey => 'x1', $tolKey => '', $tCoKey => ''),
			($brownKey = 'brown')  => array($nameKey => 'Brown', $clsKey => 'rcc-brw', $digKey => '1', $mulKey => 'x10', $tolKey => $plusMinus.'1%', $tCoKey => '100'),
			($redKey = 'red')  => array($nameKey => 'Red', $clsKey => 'rcc-red', $digKey => '2', $mulKey => 'x100', $tolKey => $plusMinus.'2%', $tCoKey => '50'),
			($orangeKey = 'orange')  => array($nameKey => 'Orange', $clsKey => 'rcc-org', $digKey => '3', $mulKey => 'x1K', $tolKey => $plusMinus.'3%', $tCoKey => '15'),
			($yellowKey = 'yellow')  => array($nameKey => 'Yellow', $clsKey => 'rcc-yel', $digKey => '4', $mulKey => 'x10K', $tolKey => $plusMinus.'4%', $tCoKey => '25'),
			($greenKey = 'green')  => array($nameKey => 'Green', $clsKey => 'rcc-grn', $digKey => '5', $mulKey => 'x100K', $tolKey => $plusMinus.'0.5%', $tCoKey => ''),
			($blueKey = 'blue')  => array($nameKey => 'Blue', $clsKey => 'rcc-blu', $digKey => '6', $mulKey => 'x1M', $tolKey => $plusMinus.'0.25%', $tCoKey => '10'),
			($violetKey = 'violet')  => array($nameKey => 'Violet', $clsKey => 'rcc-vio', $digKey => '7', $mulKey => 'x10M', $tolKey => $plusMinus.'0.10%', $tCoKey => '5'),
			($grayKey = 'gray')  => array($nameKey => 'Gray', $clsKey => 'rcc-gry', $digKey => '8', $mulKey => 'x100M', $tolKey => $plusMinus.'0.05%', $tCoKey => ''),
			($whiteKey = 'white')  => array($nameKey => 'White', $clsKey => 'rcc-wht', $digKey => '9', $mulKey => 'x1G', $tolKey => '', $tCoKey => ''),
			($goldKey = 'gold')  => array($nameKey => 'Gold', $clsKey => 'rcc-gld', $digKey => '10', $mulKey => $divOp.'10', $tolKey => $plusMinus.'5%', $tCoKey => ''),
			($silverKey = 'silver')  => array($nameKey => 'Silver', $clsKey => 'rcc-slv', $digKey => '11', $mulKey => $divOp.'100', $tolKey => $plusMinus.'10%', $tCoKey => ''),
			$tCoUnitKey => 'ppm/ºC'

		);

		$digit1Options=$digit2Options=$digit3Options=$multiplierOptions=$tolOptions=$tCoOptions=
		$bandNamesTr=$multipliersTr=$tolsTr=$tCosTr='';
		
		$digit1Arr = array($brownKey, $redKey, $orangeKey, $yellowKey, $greenKey, $blueKey, $violetKey, $grayKey, $whiteKey);
		$digit3Arr = $digit2Arr = $bandNamesArr = array_merge(array($blackKey), $digit1Arr);		
		$mulArr = array_merge($digit2Arr, array($goldKey, $silverKey));
		$tolArr = array($brownKey, $redKey, $orangeKey, $yellowKey, $greenKey, $blueKey, $violetKey, $grayKey, $goldKey, $silverKey);
		$tCoArr = array($brownKey, $redKey, $orangeKey, $yellowKey, $blueKey, $violetKey);
		
		foreach($digit1Arr as $bandColorKey)
			$digit1Options .= '<option class="'.$metaArr[$bandColorKey][$clsKey].'"  '.((mt_rand(1, 9) == $metaArr[$bandColorKey][$digKey])? 'selected="selected"' : '').'>'.$metaArr[$bandColorKey][$digKey].' '.$metaArr[$bandColorKey][$nameKey].'</option>';
		
		foreach($digit2Arr as $bandColorKey)
			$digit2Options .= '<option class="'.$metaArr[$bandColorKey][$clsKey].'"  '.((mt_rand(0, 9) == $metaArr[$bandColorKey][$digKey])? 'selected="selected"' : '').'>'.$metaArr[$bandColorKey][$digKey].' '.$metaArr[$bandColorKey][$nameKey].'</option>';
		
		foreach($digit3Arr as $bandColorKey)
			$digit3Options .= '<option class="'.$metaArr[$bandColorKey][$clsKey].'"  '.((mt_rand(0, 9) == $metaArr[$bandColorKey][$digKey])? 'selected="selected"' : '').'>'.$metaArr[$bandColorKey][$digKey].' '.$metaArr[$bandColorKey][$nameKey].'</option>';
		
		foreach($mulArr as $bandColorKey)
			$multiplierOptions .= '<option class="'.$metaArr[$bandColorKey][$clsKey].'"  '.((mt_rand(0, 11) == $metaArr[$bandColorKey][$digKey])? 'selected="selected"' : '').' '.(in_array($bandColorKey, array($goldKey, $silverKey))? 'data-division-operator="true"' : '').'>'.$metaArr[$bandColorKey][$mulKey].' '.$metaArr[$bandColorKey][$nameKey].'</option>';
		
		foreach($tolArr as $bandColorKey)
			$tolOptions .= '<option class="'.$metaArr[$bandColorKey][$clsKey].'"  '.((mt_rand(0, 9) == $metaArr[$bandColorKey][$digKey])? 'selected="selected"' : '').'>'.$metaArr[$bandColorKey][$tolKey].' '.$metaArr[$bandColorKey][$nameKey].'</option>';
		
		foreach($tCoArr as $bandColorKey)
			$tCoOptions .= '<option class="'.$metaArr[$bandColorKey][$clsKey].'"  '.((mt_rand(1, 7) == $metaArr[$bandColorKey][$digKey])? 'selected="selected"' : '').'>'.$metaArr[$bandColorKey][$tCoKey].' '.$metaArr[$bandColorKey][$nameKey].'</option>';
		
		foreach($bandNamesArr as $bandColorKey)
			$bandNamesTr .= '<tr><td class="'.$metaArr[$bandColorKey][$clsKey].'">'.$metaArr[$bandColorKey][$nameKey].'</td> <td>'.$metaArr[$bandColorKey][$digKey].'</td></tr>';
		
		foreach($mulArr as $bandColorKey)
			$multipliersTr .= '<tr><td class="'.$metaArr[$bandColorKey][$clsKey].'">'.$metaArr[$bandColorKey][$nameKey].'</td> <td>'.str_ireplace(array('K', 'M', 'G'), array('000', '000000', '000000000'), $metaArr[$bandColorKey][$mulKey]).'</td></tr>';		
		
		foreach($tolArr as $bandColorKey)
			$tolsTr .= '<tr><td class="'.$metaArr[$bandColorKey][$clsKey].'">'.$metaArr[$bandColorKey][$nameKey].'</td> <td>'.$metaArr[$bandColorKey][$tolKey].'</td></tr>';
													
		foreach($tCoArr as $bandColorKey)
			$tCosTr .= '<tr><td class="'.$metaArr[$bandColorKey][$clsKey].'">'.$metaArr[$bandColorKey][$nameKey].'</td> <td>'.$metaArr[$bandColorKey][$tCoKey].$metaArr[$tCoUnitKey].'</td></tr>';
																			
		
		function getBandForm($band=4, $h, $h_img){		
			
			global $digit1Options, $digit2Options, $digit3Options, $multiplierOptions, $tolOptions, $tCoOptions;
																																	
			return $h.'	
					<div class="bars">'.$h_img.'<div class="digit1-bar"></div><div class="digit2-bar"></div>'.(($band == 4)? '<div class="multiplier-bar"></div>' : '').'<div class="tolerance-bar"></div>'.(($band >= 5)? '<div class="digit3-bar"></div><div class="multiplier-bar2"></div>' : '').(($band == 6)? '<div class="temp-coeff-bar"></div>' : '').'</div>
					<form class="inline-form block-label" method="post" action="">
						<fieldset>
							<div class="rc3">						
								<div class="field-ctrl">
									<label>1st Digit</label>
									<select class="field digit1">
										'.$digit1Options.'																
									</select>					
								</div>
								<div class="field-ctrl">
									<label>2nd Digit</label>
									<select class="field digit2">
										'.$digit2Options.'										
									</select>					
								</div>
								'.((in_array($band, array(5, 6)))? '
								<div class="field-ctrl">
									<label>3rd Digit</label>
									<select class="field digit3">
										'.$digit3Options.'										
									</select>					
								</div>' : '').'
								<div class="field-ctrl">
									<label>Multiplier</label>
									<select class="field multiplier">
										'.$multiplierOptions.'										
									</select>					
								</div>
								<div class="field-ctrl">
									<label>Tolerance</label>
									<select class="field tolerance">	
										'.$tolOptions.'															
									</select>					
								</div>
								'.(($band == 6)? '
								<div class="field-ctrl">
									<label>Temp co</label>
									<select class="field temp-coeff">
										'.$tCoOptions.'																
									</select>					
								</div>' : '').'
								<div class="rcc-res"></div>
							</div>
						</fieldset>
					</form>
					';
		
		}
		
		$SITE->buildPageHtml(array('pageTitle'=>'Resistor Color Code Calculator', 'pageBodyMetas'=>$linkToolsJs,	
			'preBodyMetas'=>$SITE->getNavBreadcrumbs($toolsSubNav.'<li><a href="'.$GLOBAL_page_self_rel.'" title="">Resistor Color Code Calculator</a></li>'),
			'pageBody'=>'				
			<div class="single-base blend">			
				<div class="base-ctrl">				
					<div class="panel panel-mine-1 rc3-base">	
						<h1 class="panel-head page-title">RESISTOR COLOR CODE CALCULATOR</h1>
						<div class="panel-body">
							<div class="tab tab-classic">			
								<a class="" data-default-tab="true" data-toggle="tab">4-band</a>	
								<a class="" data-toggle="tab">5-band</a>	
								<a class="" data-toggle="tab" >6-band</a>
								<a class="" data-toggle="tab">Notes</a>
							</div>
							<div class="tab-contents has-tab-close fade-panel">  				
								<div class="tab-content">						  
								  '.getBandForm(4, '<h1 class="prime">4 Band Resistor</h1>', '<img class="" src="'.$rccBlankImg.'" alt="4-band resistors" />').'
								</div>					
								<div class="tab-content">						  
								  '.getBandForm(5, '<h1 class="prime">5 Band Resistor</h1>', '<img class="" src="'.$rccBlankImg.'" alt="5-band resistors" />').'
								</div>			
								<div class="tab-content">						  
								  '.getBandForm(6, '<h1 class="prime">6 Band Resistor</h1>', '<img class="" src="'.$rccBlankImg.'" alt="6-band resistors" />').'
								</div>	
								<div class="tab-content">						  
									<h1 class="prime">Notes On Resistor Color Codes</h1>
									<div class="hr-dividers">
										Are you having trouble reading resistor color codes? If your answer is yes, then this tool is specifically designed for you! 
										Our Resistor Color Code Calculator is a handy tool for reading carbon-composition resistors whether it’s a 4-band, 5-band or 6-band type. 
										<p>
											To use this tool, simply click on a particular color and number and watch how the actual bands on the resistor illustration change. 
											The resistance value is displayed on the result box below together with the tolerance and the temperature coefficient.  
										</p>
										<h3>RESISTOR COLOR CODE READING GUIDE</h3>
										<p>
											As shown above, a carbon-composition resistor can have 4 to 6 bands. A 5-band resistor is more precise compared to a 4-band type because 
											of the inclusion of a third significant digit. A 6-band resistor is like a 5-band resistor but includes a temperature coefficient band (the 6th band).
										</p>
										<div class="table-responsive">									
											<table class="table-basic">
												<th>Bands:</th><th>4-band</th><th>5-band</th><th>6-band</th>
												<tr><td>1st band</td> <td>1st significant digit</td> <td>1st significant digit</td> <td>1st significant digit</td></tr>
												<tr><td>2nd band</td> <td>2nd significant digit</td> <td>2nd significant digit</td> <td>2nd significant digit</td></tr>
												<tr><td>3rd band</td> <td>multiplier</td> <td>3rd significant digit</td> <td>3rd significant digit</td></tr>
												<tr><td>4th band</td> <td>tolerance</td> <td>multiplier</td> <td>multiplier</td></tr>
												<tr><td>5th band</td> <td>N/A</td> <td>tolerance</td> <td>tolerance</td></tr>
												<tr><td>6th band</td> <td>N/A</td> <td>N/A</td> <td>temperature coefficient</td></tr>
											</table>											
										</div>	
										<div class="table-responsive">											
											<p>
												<h3>BAND DIGIT</h3>
												Each color represents a number if it’s located from the 1st to 2nd band for a 4-band type or 1st to 3rd band for a 5-band and 6-band type.
											</p>									
											<table class="table-basic">												
												<th>Color</th><th>Value</th>
												'.$bandNamesTr.'
											</table>									
											<p>Mnemonics were created to easily memorize the sequence of the colors.</p>
											<div class="alert alert-warning prime"> 
												The most popular mnemonic is "<b>B</b>ig <b>B</b>oys <b>R</b>ace <b>O</b>ur <b>Y</b>oung <b>G</b>irls <b>B</b>ut <b>V</b>iolet <b>G</b>enerally <b>W</b>ins" 
												where the first letter of each word corresponds to the first letter of the color. 
											</div>
										</div>																								
										<div class="table-responsive">
											<p>
												<h3>MULTIPLIER</h3>
												If the color is found on the 3rd band for a 4-band type or the 4th band for a 5-band and 6-band type, then it’s a multiplier.
											</p>									
											<table class="table-basic">												
												<th>Color</th><th>Value</th>
												'.$multipliersTr.'
											</table>
											<div class="alert alert-info">Notice that the number of zeroes is equal to the color’s number as per the previous table.</div>
										</div>											
										<div class="table-responsive">
											<p>
												<h3>TOLERANCE</h3>
												The fourth band (or 5th for the 5-band and 6-band) indicates the tolerance values. Here, two colors are added (gold and silver).
											</p>									
											<table class="table-basic">												
												<th>Color</th><th>Value</th>																	
												'.$tolsTr.'
											</table>									
										</div>										
										<div class="table-responsive">
											<p>
												<h3>TEMPERATURE COEFFICIENT</h3>
												The 6th band for a 6-band type resistor is the temperature coefficient. 
												This indicates how much the actual resistance value of the resistor changes when the temperature changes.
											</p>									
											<table class="table-basic">												
												<th>Color</th><th>Value</th>																	
												'.$tCosTr.'
											</table>
											<div class="alert alert-info"> 
												NOTE: Temperature coefficient is not applicable to any color band that was not listed in this table												
											</div>									
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>'
		));
		
		break;
		
		
		
		
		
		
	case $calenderSlug:
	
		$SITE->buildPageHtml(array('pageTitle'=>'Calendar', 'pageBodyOnload'=>'popuCal(\'ini\');', 'pageBodyMetas'=>$linkToolsJs,		  
			'preBodyMetas'=>$SITE->getNavBreadcrumbs($toolsSubNav.'<li><a href="'.$GLOBAL_page_self_rel.'" title="">Calendar</a></li>'),
			'pageBody'=>'			
			<div class="single-base blend">			
				<div class="base-ctrl">							
					<div class="panel panel-mine-1">	
						<h1 class="panel-head page-title">CALENDAR</h1>
						<div class="table-responsive panel-body">
							<table class="calendar no-bdc">
								<tr>
									<th colspan="7" class="cal-head" id="cal-head"></th>
								</tr>
								<tr id="wks"></tr>
								<tbody class="cal-body" id="cal-body"></tbody>
								<tr>
									<td colspan="7">
										<div class="cal-foot">
											<div class="cal-nvg clear">
												<label for="month" class="flab prime">select month:</label>
												<select onchange="popuCal();" id="month"></select>
												<button title="previous" onclick="popuCal(\'-m\');">&#8678;</button><button title="next" onclick="popuCal(\'+m\')">&#8680;</button>
												<hr/>
											</div>
											<div class="cal-nvg clear">
												<label for="year" class="flab prime">select year:</label>
												<select onchange="popuCal();" id="year"></select>
												<button title="previous" onclick="popuCal(\'-y\')">&#8678;</button><button title="next" onclick="popuCal(\'+y\')">&#8680;</button>
												<hr/>
											</div>
											<label for="output" class="flab prime">Current Selection:</label>
											<output class="output" id="output"></output>								
										</div>
									</td>
								</tr>
							</table>												
						</div>
					</div>
				</div>
			</div>'
		));
		
		break;
	
		
		
		
		
	/**DEVICE INFO**/
	case $deviceInfoSlug:
		
		$titleColor = 'blue';
		$onColor = 'lgreen';
		$offColor = 'red';
		$dataColors = 'data-title-color="'.$titleColor.'" data-off-color="'.$offColor.'"';
		$clsBlue = 'class="'.$titleColor.'"';
		
		$SITE->buildPageHtml(array("pageTitle"=>'Device Informations',
		"preBodyMetas"=>$SITE->getNavBreadcrumbs($toolsSubNav.'<li><a href="'.$deviceInfoSlug.'" >Device Infos</a></li>'),
		'pageBodyOnload'=>'deviceInfos(); adBlockerDetect();', 'pageBodyMetas'=>$linkToolsJs,							
		"pageBody"=>'
			<div class="single-base blend">
				<div class="base-ctrl panel panel-mine-1">
					<h1 class="panel-head page-title">Device Informations</h1>
					<div class="panel-body sides-padless align-l">
						<div class="'.$onColor.' hr-dividers">
							<p class="prime">WELCOME! - please find below your device/browser details.</p>
							<p><span '.$clsBlue.'>IP: </span>'.$ENGINE->get_ip().' </p>
						</div>
						<div class="'.$onColor.' hr-dividers" id="di-screen-js" '.$dataColors.'>'.$GLOBAL_spinnerXs.'</div>
						<noscript>
							<div class="alert alert-danger">Please enable javascript on your device to view your device details</div>
						</noscript>
					</div>
				</div>
			</div>'
		));
		
		break;
	
		
		
		
		
		
		
		
	/**GEOLOCATION**/
	case $geoLocSlug:
		
		$linkToolsJs .= '<script src="http://maps.google.com/maps/api/js?sensor=false"></script>';
		
		$SITE->buildPageHtml(array("pageTitle"=>'Geo Locator', "pageBodyMetas"=>$linkToolsJs,
				"preBodyMetas"=>$SITE->getNavBreadcrumbs($toolsSubNav.'<li><a href="'.$geoLocSlug.'" title="Shows your current position on a map" >Geo Locator</a></li>'),
				"pageBody"=>'
				<div class="single-base blend">
					<div class="base-ctrl">			
						<h1 class="page-title pan bg-mine-1" id="f1"><i class="fas fa-compass"></i> GEO LOCATOR</h1>'. 										
						(isset($alertUser)? $alertUser : '').'
						<div class="base-pad">	
							<output id="show_geoloc_alerts"></output>
							<form method="post" action="/" class="inline-form">
								<div class="field-ctrl">
									<label>Map Type:</label>
									<select class="field" id="mapType">
										<option>ROADMAP</option>
										<option>SATELLITE</option>
										<option>TERRAIN</option>
									</select>
								</div>
								<div class="field-ctrl">
									<a href="javascript:void()" class="btn btn-success" onclick="getLocation(false,event);">Load Dynamic</a>
									<a href="javascript:void()" class="btn btn-warning" onclick="getLocation(true,event);">Load Static</a>
								</div>
							</form>
							<div class="map"  id="show_geoloc_map" ></div>		
						</div>
					</div>
				</div>', "pageBodyOnload"=>'getLocation(false,event);'
		));
		
		break;
		
		
		
		
		
		
		
	/**WHATSAPP**/
	case $whatsAppSlug:
		
		if(isset($_POST["chat_whatsapp_num"])){
			
			$countryCode = isset($_POST[$K="country_code"])? $_POST[$K] : '';
			$phoneNumber = $_POST["phone"];
			
			header("Location:http://api.whatsapp.com/send?phone=".$countryCode.$phoneNumber);
			exit();
			
		}
		
		
		
		$SITE->buildPageHtml(array("pageTitle"=>'Whatsapp',
				"preBodyMetas"=>$SITE->getNavBreadcrumbs($toolsSubNav.'<li><a href="'.$whatsAppSlug.'" title="" >Whatsapp</a></li>'),
				"pageBody"=>'
				<div class="single-base blend">
					<div class="base-ctrl">			
						<h1 class="page-title pan bg-dcyan"><i class="fab fa-whatsapp"></i> WHATSAPP</h1>'. 										
						(isset($alertUser)? $alertUser : '').'
						<div class="base-pad">	
							<form method="post" action="'.$whatsAppSlug.'" class="inline-form">
								<div class="field-ctrl">
									<label>Phone Number:</label>
									'.$SITE->getCountryList(true).'
									<input type="text" name="phone" required="required" class="field" placeholder="phone number" />
								</div>
								<div class="field-ctrl">
									<input type="submit" name="chat_whatsapp_num" value="Chat Number on Whatsapp" class="form-btn" />
								</div>
							</form>		
						</div>
					</div>
				</div>'
		));
		
		break;
		
		
	

	
		
		
	/**QR CODE**/
	case $qrCodeSlug:
		
		$requestTab = $subNav = $linkToolsJs = '';

		/***************************BEGIN URL CONTROLLER****************************/
			
		$tab = isset($pagePathArr[2])? strtolower($pagePathArr[2]) : '';
		
		if(in_array($tab, array($generate = 'generate', $scan = 'scan', $result = 'result'))){
							
			$pathKeysArr = array('subDomain', 'pageUrl', 'tab');
			$maxPath = 3;
			$requestTab = $tab;
			$subNav = '<li><a href="'.$GLOBAL_page_self_rel.'" title="">'.ucwords($tab).'</a></li>';
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 2;
		
		}
		
		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/	
		$qrContentFieldName = 'qr-content';
		$base64ImgFieldName = 'base64-img';

		if($requestTab == $result){

			$qrContent = isset($_POST[$qrContentFieldName])? $_POST[$qrContentFieldName] : '';
			$base64Img = isset($_POST[$base64ImgFieldName])? $_POST[$base64ImgFieldName] : '';

			if($ENGINE->str_is_url($qrContent)){

				header("Location:".$qrContent);
				exit();

			}
		
		}elseif($requestTab == $generate){

			$responseArr = $SITE->qrCodeRequestHandler();

		}elseif($requestTab == $scan){
			
			$_GET[RSRC_PLUGS['qr']] = true;

		}
		
		$checked = 'checked="checked"';
		
		
		$SITE->buildPageHtml(array("pageTitle"=>($headTitle = 'Qr Code'.($requestTab? ' - '.$requestTab : '')),
				"preBodyMetas"=>$SITE->getNavBreadcrumbs($toolsSubNav.'<li><a href="/'.$ENGINE->get_page_path('page_url', 2).'" title="" >Qr Code</a></li>'.$subNav),				
				"pageBody"=>'
				<div class="single-base blend">
					<div class="base-ctrl">			
						<h1 class="page-title pan bg-dcyan"><i class="fa fa-qrcode"></i> '.ucwords($headTitle).'</h1>'. 										
						(isset($alertUser)? $alertUser : '').'
						<div class="base-pad">'.
						(($requestTab == $generate)?	
							'<form method="post" action="'.$GLOBAL_page_self_rel.'" class="inline-form inline-form-defaultX block-label">
								<div class="field-ctrl w-ctrl col-w-10 align-c">
									<label>Qr Data Content:</label>
									<textarea name="'.($K = 'data').'" class="field col-w-8" placeholder="Enter the desired content data for Qr Code">'.$ENGINE->get_assoc_arr($responseArr, $K).'</textarea>
								</div>								
								<div class="hide" id="'.($customFieldGrp = 'custom-field-grp').'">
									<div class="field-ctrl">
										<label>Qr Label:</label>
										<input type="text" name="'.($K = 'label').'" class="field" placeholder="Enter the desired Qr label text" value="'.$ENGINE->get_assoc_arr($responseArr, $K).'" />	
									</div>
									<div class="field-ctrl">
										<label>Qr Size(px):</label>
										<input type="number" name="'.($K = 'size').'" min="100" max="1000" class="field" placeholder="Enter the desired Qr size value" value="'.$ENGINE->get_assoc_arr($responseArr, $K).'" />	
									</div>
									<div class="field-ctrl">
										<label>Qr Margin(px):</label>
										<input type="number" name="'.($K = 'margin').'" min="5" max="50" class="field" placeholder="Enter the desired Qr margin value" value="'.$ENGINE->get_assoc_arr($responseArr, $K).'" />	
									</div>
									<div class="field-ctrl">	
										<label>Qr Logo Resize to Width(px):</label>
										<input type="number" name="'.($K = 'logoR2w').'" min="20" max="100" class="field" placeholder="Enter the desired logo resize to width value" value="'.$ENGINE->get_assoc_arr($responseArr, $K).'" />	
									</div>									
									<div class="field-ctrl">	
										<label>Qr Logo Resize to Height(px):</label>
										<input type="number" name="'.($K = 'logoR2H').'" min="20" max="100" class="field" placeholder="Enter the desired logo resize to height value" value="'.$ENGINE->get_assoc_arr($responseArr, $K).'" />	
									</div>
									<div class="field-ctrl">
										<label>Qr CharSet:</label>
										<input type="text" name="'.($K = 'charSet').'" class="field" placeholder="Enter the data enconding character set" value="'.$ENGINE->get_assoc_arr($responseArr, $K).'" />	
									</div>
									<div class="field-ctrl">
										<label>Qr Foreground '.($rgbaTxt = 'Color(R, G, B, alpha):').'</label>
										<input type="text" name="'.($K = 'foreColor').'" class="field" placeholder="(R, G, B) E.g (0, 0, 255)" value="'.$ENGINE->get_assoc_arr($responseArr, $K).'" />	
									</div>
									<div class="field-ctrl">
										<label>Qr Background '.$rgbaTxt.'</label>
										<input type="text" name="'.($K = 'backColor').'" class="field" placeholder="(R, G, B) E.g (0, 0, 255)" value="'.$ENGINE->get_assoc_arr($responseArr, $K).'" />	
									</div>
									<div class="field-ctrl">
										<label>Qr Label Text '.$rgbaTxt.'</label>
										<input type="text" name="'.($K = 'labelColor').'" class="field" placeholder="(R, G, B) E.g (0, 0, 255)" value="'.$ENGINE->get_assoc_arr($responseArr, $K).'" />	
									</div>
									<div class="field-ctrl">
										<label>Qr File Save Name:</label>
										<input type="text" name="'.($K = 'saveName').'" class="field" placeholder="Enter the desired name to save the Qr Code with" value="'.$ENGINE->get_assoc_arr($responseArr, $K).'" />	
									</div>
									<div class="my-5 field-ctrl">									
										<div class="field-ctrl">'.
											$SITE->getHtmlComponent('switch-slider', array('label'=>'Save the Qr Code:', 'title'=>'Save the Qr Code to file', 'wrapClass'=>'red', 'fieldName'=>($K = 'save2File'), 'on'=>($ENGINE->get_assoc_arr($responseArr, $K)? $checked : ''))).
										'</div>
										<div class="field-ctrl">'.
											$SITE->getHtmlComponent('switch-slider', array('label'=>'Qr Punchout Background:', 'title'=>'punchout the Qr Code background', 'wrapClass'=>'red', 'fieldName'=>($K = 'punchoutBg'), 'on'=>($ENGINE->get_assoc_arr($responseArr, $K)? $checked : ''))).'
										</div>										
										<div class="field-ctrl">'.
											$SITE->getHtmlComponent('switch-slider', array('label'=>'Qr Direct Output:', 'title'=>'Directly output the Qr Code to the page', 'wrapClass'=>'red', 'fieldName'=>($K = 'forceOutput'), 'on'=>($ENGINE->get_assoc_arr($responseArr, $K)? $checked : ''))).'
										</div>										
									</div>								
								</div>
								<div class="clear">
									<a class="btn btn-danger pull-l" href="/" data-toggle="smartToggler" data-id-targets="'.$customFieldGrp.'" data-toggle-attr="text|Hide Custom Options">Show Custom Options</a>									
								</div>
								<div class="field-ctrl">
									<input type="submit" name="generate-qr" value="Generate Qr Code" class="form-btn" />
								</div>
							</form>'
							
						: (($requestTab == $scan)?

							'
							<div class="container-fluid col-w-5">
								<div class="">
									<div id="'.($camNameId = 'camera-name').'"></div>
									<video class="media-responsive" id="'.($camId = 'camera-view').'"></video>									
									<div id="scan-res"></div>
									<button id="'.($camStopBtnId = 'stop-camera').'" class="btn btn-danger btn-block bold" data-js-display-mode="true">Exit Camera</button>
									<div id="scan-res-img"></div>
								</div>
								<form id="'.($formId = 'response-form').'" action="/'.$subDomain.'/'.$qrCodeSlug.'/'.$result.'" method="post">
									<input type="hidden" name="'.$qrContentFieldName.'" id="'.($contentFieldId = 'qr-content').'" />
									<input type="hidden" name="'.$base64ImgFieldName.'" id="'.($base64ImgFieldId = 'qr-img').'" />								
								</form>
							</div>
							<script type="text/javascript">

								var camera;

								let scanner = new Instascan.Scanner({
									
									video: dom("'.$camId.'"),
									captureImage: true
								
								});
								
								Instascan.Camera.getCameras().then((cameras) => {
									
									if(cameras.length > 0){

										camera = cameras[0];
										scanner.start(camera);

									}else{

										console.error("No Cameras Found");

									}

								}).catch(function(e){

									console.error(e);

								})

								
								scanner.addListener("active", () => {
									
									dom("'.$camStopBtnId.'").style.display = "block";
									dom("'.$camNameId.'").innerHTML = camera.name;

								});

								
								scanner.addListener("inactive", () => {
									
									//dom("'.$camStopBtnId.'").style.display = "none";
									//dom("'.$camNameId.'").style.display = "none";

								});

								dom("'.$camStopBtnId.'").addEventListener("click", function(){
									
									scanner.stop().then(() => {

										dom("'.$camStopBtnId.'").style.display = "none";
										dom("'.$camNameId.'").style.display = "none";

									});
									
								});
								
								scanner.addListener("scan", (content, base64Img) => {
									
									//dom("scan-res").innerHTML = content;									
									//dom("scan-res-img").innerHTML = \'<img src="\'+ base64Img +\'" />\';
									dom("'.$contentFieldId.'").value = content;
									dom("'.$base64ImgFieldId.'").value = base64Img;
									dom("'.$formId.'").submit();

								});
								


							</script>
							'
						:

							'<nav class="nav-base">					
								<ul class="nav panel-style panel-head-md nav-pills nav-inline justified-center">
									<li><a class="links" href="/'.$subDomain.'/'.$qrCodeSlug.'/'.$generate.'">Generate Qr Code</a></li>
									<li><a class="links" href="/'.$subDomain.'/'.$qrCodeSlug.'/'.$scan.'">Scan Qr Code</a></li>												
								</ul>
							</nav>'

						))												
							.'		
						</div>
					</div>
				</div>'
		));
		
		break;
		
		
		
		
		
		
		
	case 'home-index':
		$SITE->buildPageHtml(array('pageTitle'=>'Site Tools',
			'preBodyMetas'=>$SITE->getNavBreadcrumbs($toolsSubNav),
			'pageBody'=>'						
			<div class="single-base blend">			
				<div class="base-ctrl">
					<h1 class="page-title pan bg-mine-1">SITE TOOLS</h1>
					'.$SITE->buildLinearNav(array(
						"baseUrl" => $subDomain,
						"urlListCls" => "panel-style panel-icon-block panel-head-md nav-pills nav-inline align-l",
						"urlList" => $urlList						
					
					)).'									
					<noscript class="base-pad">
						<div class="alert alert-danger">It seems javascript has been disabled on your browser!<br/>To access our site tools, You must use a javascript enabled browser.</div>
					</noscript>
				</div>
			</div>'
		));
		
		break;
		
		

		
		
	
	default:{
		
		/**IF ABOVE REQUEST URL NOT FOUND THEN FALL BACK TO 404 PAGE ERROR**/
		include_once(DOC_ROOT."/page-error.php");
		exit();	
						
	}
	
}


?>