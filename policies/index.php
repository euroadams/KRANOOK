<?php

require_once ('../page-common-headers.php');

$ACCOUNT->authorizeSiteTakeDownAccess();

/////////VARIABLE INITIALIZATION//////////////

$pageUrl=$pageUrlLowerCase=$path_cleaned=$std_link="";

$cBullet =  'circular-bullet';
$hasCbullet =  'has-'.$cBullet;
$siteDomain =  $GLOBAL_siteDomain;
$siteName =  $GLOBAL_siteName;
$npDomain = $GLOBAL_npDomain;
$GLOBAL_isStaff;
$siteNameBold = '<b>'.$siteName.'</b>';
$policySubNav = '<li><a href="/'.($polSubUrl = 'policies').'">Policies</a></li>';

$baseUrl = '/'.$polSubUrl.'/';
																																											
$urlList = array(//FORMAT => urlSlug:urlLabel:urlIcon:cond2Add
	($tosPolTab = 'terms-of-service').':terms of service', 
	($privacyPolTab = 'privacy').':privacy policy',
	($dmcaPolTab = 'dmca').':DMCA',
	($adsPolTab = 'ads').':ad policy',
	($gfrPolTab = 'general-community-rules').':community rules',
	($modsPolTab = 'mods-agreement').':mods agreement',
	($badgeRepPolTab = 'badges-reputations').':badges & reputations',
);


$condPages_arr =  array(($seenTosPol = "seen_tos"), ($seenPrivacyPol = "seen_privacy"), ($seenDmcaPol = "seen_dmca"), ($seenAdsPol = "seen_ads_policy"), ($seenBadgeRepPol = "seen_badges"), ($seenGfrPol = "seen_gfr"));

if($ENGINE->is_global_var_set('ss', $condPages_arr)){

	list($tos, $privacy, $dmca, $adsPol, $badge, $gfr) = $ENGINE->get_global_var('ss', $condPages_arr);
	
	if($GLOBAL_userId && (($tos & $privacy & $adsPol & $badge & $gfr) == $GLOBAL_userId)){

		//////AWARD LICIT BADGE///////////
		$badgesAndReputations->badgeAwardFly($GLOBAL_userId, 'licit');
			
		$ENGINE->unset_global_var('ss', $condPages_arr);

	}
}

$path = $ENGINE->get_page_path('page_url', '', true);

if($path){	
	
	/***GET/HANDLE THE  REQUEST URL*******/
	$pathArr = explode("/", $path);
	
	if(is_array($pathArr) && isset($pathArr[0]))
		$subDomain = $pathArr[0];				
	
	$pageUrl = (isset($pathArr[1]))? $pathArr[1] : '';
	
	$pageUrlLowerCase = strtolower($pageUrl);
	
	$fall2Index = ($subDomain && !$pageUrlLowerCase)? true : false;

	if($fall2Index)
		$pageUrlLowerCase = 'home-index';
		
}

$policyNavTabs = $SITE->buildLinearNav(array(
	"baseUrl" => $polSubUrl,
	"hasRelBase" => true,
	"urlListCls" => ($fall2Index? 'nav-pills' : 'nav-tabs')." justified justified-bom",
	"urlList" => $urlList,
	"active" => $pageUrlLowerCase

));


switch($pageUrlLowerCase){
	
	case $tosPolTab:

		/////TRACK SESSION//////
		$ENGINE->set_global_var('ss', $seenTosPol, $GLOBAL_userId);

		$SITE->buildPageHtml(array('pageTitle'=>'Terms Of Service',
			'preBodyMetas'=>$SITE->getNavBreadcrumbs($policySubNav.'<li><a href="'.$baseUrl.$tosPolTab.'" title="">Terms Of Service</a></li>'),
			'pageBody'=>'		
			<div class="single-base blend">
				<div class="base-ctrl">								
					'.$policyNavTabs.'				
					<div class="panel panel-mine-1">
						<h1 class="panel-head page-title">Terms Of Service</h1>
						<div class="panel-body align-l">
							<h4 class="alert alert-warning">
								<span class="lgreen">'.$siteName.'’s terms of service was last updated on September 18, 2017.<br/></span>
								These terms may be subject to change at any time without notice. Hence users are advised to check this page periodically.
							</h4>					
							<p>
								The following terms and conditions govern all use of the '.$siteNameBold.' website and all contents, services and products 
								available at or through the website.
								<br/>The website is owned and operated by '.$siteName.' Inc. (‘'.$siteName.'’). The website 
								is offered subject to your acceptance without modification of all of the terms and conditions contained herein and all other operating rules,
								policies (including, without limitation, '.$siteName.' <a href="'.$baseUrl.$privacyPolTab.'" class="links">Privacy Policy</a>) and 
								procedures that may be published from time to time on this Site by '.$siteName.' (collectively, the “Agreement”).
								<div class="alert alert-warning">
									Please read this Agreement carefully before accessing or using the website. By accessing or using any part of the website, you agree to 
									become bound by the terms and conditions of this agreement. If you do not agree to all the terms and conditions of this agreement, 
									then you may not access the website or use any of its services. <br/>The website is available only to individuals who are at least <b>'.SITE_ACCESS_MIN_AGE.' years old</b>.
								</div>
							</p>
							<ol class="ol">
								<li>
									<h4>Your '.$siteName.' Account:</h3> If you create a '.$siteName.' account or a thread on the website, you are responsible for maintaining the 
									security of your account, and you are fully responsible for all activities that occur under the account and any other actions taken in connection with your account. 
									You must not describe or assign keywords to your threads in a misleading or unlawful manner, including in a manner intended to trade on the name or reputation of others, and 
									 '.$siteName.' may change or remove any description or keyword that it considers inappropriate or unlawful, or otherwise likely to cause '.$siteName.' liability. 
									You must immediately notify '.$siteName.' of any unauthorized uses of your threads, your account or any other breaches of security. 
									'.$siteName.' will not be liable for any acts or omissions by You, including any damages of any kind incurred as a result of such acts or omissions.
								</li>	 
								<li>
									<h4>Responsibility of Contributors:</h4> If you operate a thread, comment on a thread, post material to the website, post links on the website, or otherwise make 
									(or allow any third party to make) material available by means of the website (any such material, “Content”), You are entirely responsible for the content and any harm resulting from 
									that Content. 
									<br/>That is the case regardless of whether the Content in question constitutes text, graphics, an audio file, or computer software. By making Content available, you represent and warrant that:<br/>
									<p class="'.$hasCbullet.'">
										The downloading, copying and use of the Content will not infringe the proprietary rights, including but not limited to the copyright, patent, trademark or trade secret rights, of any third party;
										<br/>If your employer has rights to intellectual property you create, you have either:<br/>
										<ol claims="ol has-circular-bullets" type="i">
											<li>
												received permission from your employer to post or make available the Content, including but not limited to any software or
											</li>
											<li>
												secured from your employer a waiver as to all rights in or to the Content;<br/>
												You have fully complied with any third-party licenses relating to the Content, and have done all things necessary to successfully pass through to end users any required 
												terms;
												<br/> The Content does not contain or install any viruses, worms, malware, trojan horses or other harmful or destructive content;												
												<br/>The Content is not spam, is not machine- or randomly-generated, and does not contain unethical or unwanted commercial content designed to drive traffic to third party sites or 
												boost the search engine rankings of third party sites, or to further unlawful acts (such as phishing) or mislead recipients as to the source of the material (such as spoofing);
												<br/>The Content is not libelous or defamatory, does not contain threats or incite violence towards individuals or entities, and does not violate the privacy or publicity rights of 
												any third party;												
												<br/>Your thread is not named in a manner that misleads your readers into thinking that you are another person or company. For example, your thread’s name is not the name of a person other 
												than yourself or company other than your own;
												<br/>You have, in the case of Content that includes computer code, accurately categorized and/or described the type, nature, uses and effects of the materials, whether requested to do so
												by '.$siteName.' or otherwise.
												By posting Content to '.$siteName.', you grant '.$siteName.' a world-wide, royalty-free, and non-exclusive license to reproduce, modify, adapt and publish the Content 
												solely for the purpose of displaying, distributing and promoting your content. If you delete Content, '.$siteName.' will use reasonable efforts to remove it from the website, 
												but you acknowledge that caching or references to the Content may not be made immediately unavailable.
											</li>
											<li>
												Without limiting any of those representations or warranties, '.$siteName.' has the right (though not the obligation) to, in '.$siteName.'’s sole discretion
												<p class="'.$hasCbullet.'">
													refuse or remove any content that, in '.$siteName.'’s reasonable opinion, violates any '.$siteName.' <a href="/policies" class="links">policy</a> or is in any way harmful or 
													objectionable, or
												</p>
												<p class="'.$hasCbullet.'">
													terminate or deny access to and use of the website to any individual or entity for any reason, in '.$siteName.'’s sole discretion. '.$siteName.' will have no obligation to 
													provide a refund of any amounts previously paid(same applies to account termination by users themselves).
												</p>
											</li>
										</ol>
									</p>
								</li>
								<li>
									<h4>Responsibility of Website Users:</h4> '.$siteName.' has not reviewed, and cannot review, all of the material, including computer software, posted to the website, and cannot therefore be 
									responsible for that material’s content, use or effects. By operating the website, '.$siteName.' does not represent or imply that it endorses the material there posted, or that it believes such material to
									be accurate, useful or non-harmful. You are responsible for taking precautions as necessary to protect yourself and your computer systems from viruses, worms, trojan horses, and other harmful or destructive content. 
									The website may contain content that is offensive, indecent, or otherwise objectionable, as well as content containing technical inaccuracies, typographical mistakes, and other errors. The website may also contain material that
									violates the privacy or publicity rights, or infringes the intellectual property and other proprietary rights, of third parties, or the downloading, copying or use of which is subject to additional terms and conditions, stated or unstated. 
									'.$siteName.' disclaims any responsibility for any harm resulting from the use by visitors of the website, or from any downloading by those visitors of content there posted.
								</li>
								<li>
									<h4>Content Posted on Other Websites:</h4> We have not reviewed, and cannot review, all of the material, including computer software, made available through the websites and webpages to which '.$siteName.' link, and that link to '.$siteName.'. 
									'.$siteName.' does not have any control over those non-'.$siteName.' websites and webpages, and is not responsible for their contents or their use. By linking to a non-'.$siteName.' website or webpage, '.$siteName.'
									does not represent or imply that it endorses such website or webpage. You are responsible for taking precautions as necessary to protect yourself and your computer systems from viruses, worms, trojan horses, and other harmful or destructive content.  
									'.$siteName.' disclaims any responsibility for any harm resulting from your use of non-'.$siteName.' websites and webpages.
								</li>
								<li>
									<h4>Copyright Infringement:</h4> As '.$siteName.' ask others to respect its intellectual property rights, it respects the intellectual property rights of others. If you believe that material located 
									on or linked to by '.$siteName.' violates your copyright, you are encouraged to file a <a href="'.$baseUrl.$dmcaPolTab.'" class="links">DMCA Take Down Notice</a>. 
									'.$siteName.' will respond to all such <b>valid</b> notices, including as required or appropriate by removing the infringing material or disabling all links to the infringing material. In the case of a user who may infringe or 
									repeatedly infringes the copyrights or other intellectual property rights of '.$siteName.' or others, '.$siteName.' may, in its discretion, terminate or deny access to and use of the website. 
									In the case of such termination, '.$siteName.' will have no obligation to provide a refund of any amounts previously paid to '.$siteName.'.
								</li>
								<li>
									<h4>Intellectual Property:</h4> This Agreement does not transfer from '.$siteName.' to you any  '.$siteName.' or third party intellectual property, and all right, title and interest in and to such property will remain 
									(as between the parties) solely with '.$siteName.'.  '.$siteName.', the '.$siteName.' logo, and all other trademarks, service marks, graphics and logos used in connection with 
									'.$siteName.', or the website are trademarks or registered trademarks of '.$siteName.' or '.$siteName.'’s licensors. Other trademarks, service marks, graphics and logos used in connection with the website may be 
									 the trademarks of other third parties. Your use of the website grants you no right or license to reproduce or otherwise use any '.$siteName.' or third-party trademarks.
								</li>														
								<li>
									<h4>Disclaimer of Warranties:</h4> The website is provided “as is”. '.$siteName.' and its suppliers and licensors hereby disclaim all warranties of any kind, express or implied, including, without limitation, 
									the warranties of merchantability, fitness for a particular purpose and non-infringement. Neither '.$siteName.' nor its suppliers and licensors, makes any warranty that the website will be error free or that access thereto 
									will be continuous or uninterrupted. You understand that you download from, or otherwise obtain content or services through, the website at your own discretion and risk.
								</li>
								<li>
									<h4>Limitation of Liability:</h4> In no event will '.$siteName.', or its suppliers or licensors, be liable with respect to any subject matter of this agreement under any contract, negligence, strict liability or other legal or equitable 
									theory for:<br/>
									<b class="'.$hasCbullet.'">any special, incidental or consequential damages</b><br/>
									<b class="'.$hasCbullet.'">the cost of procurement or substitute products or services</b><br/>
									<b class="'.$hasCbullet.'">for interruption of use or loss or corruption of data or</b><br/>
									<b class="'.$hasCbullet.'">for any amounts that exceed the fees paid by you to '.$siteName.'.</b><br/>
									<p>'.$siteName.' shall have no liability for any failure or delay due to matters beyond their reasonable control. The foregoing shall not apply to the extent prohibited by applicable law.</p>
								</li>
								<li>
									<h4>General Representation and Warranty:</h4>
									You represent and warrant that:<br/>
									<p class="'.$hasCbullet.'">
										your use of the website will be in strict accordance with the '.$siteName.' <a class="links" href="'.$baseUrl.$privacyPolTab.'">Privacy Policy</a>, with this Agreement and with all 
										applicable laws and regulations (including without limitation any local laws or regulations in your country, state, city, or other governmental area, regarding online conduct and acceptable content, and
										including all applicable laws regarding the transmission of technical data exported from '.SITE_COUNTRY.' or the country in which you reside) and 
									</p>
									<p class="'.$hasCbullet.'">
										your use of the website will not infringe or misappropriate the intellectual property rights of any third party.
									</p>
								</li>
								<li>
									<h4>Indemnification:</h4> You agree to indemnify and hold harmless  '.$siteName.', its contractors, and its licensors, and their respective directors, officers, employees and agents from and 
									against any and all claims and expenses, including attorneys’ fees, arising out of your use of the website, including but not limited to your violation of this Agreement.
								</li>
								<li>
									<h4>Miscellaneous:</h4> This Agreement constitutes the entire agreement between '.$siteName.' and you concerning the subject matter hereof, and they may only be 
									modified by a written amendment signed by an authorized executive of '.$siteName.', or by the posting by '.$siteName.' of a revised version. 
									The Agreement shall be governed by the laws of the federal republic of '.SITE_COUNTRY.' without regard to its choice or law or conflict of laws provisions. All legal actions in connection with the 
									Agreement shall be brought in the state or federal courts. The prevailing party in any action or proceeding to enforce this Agreement shall be entitled to costs and attorneys’ fees. 
									If any part of this Agreement is held invalid or unenforceable, that part will be construed to reflect the parties’ original intent, and the remaining portions will remain in full force and effect.
									A waiver by either party of any term or condition of this Agreement or any breach thereof, in any one instance, will not waive such term or condition or any subsequent breach thereof. 
									Rights under this Agreement may be assigned to any party that consents to, and agrees to be bound by, its terms and conditions; '.$siteName.' may assign its rights under 
									this Agreement without condition. This Agreement will be binding upon and will inure to the benefit of the parties, their successors and permitted assigns.
								</li>
								<li>
									<h4 class="red">Account Termination:</h4> 
									While we hope you’ll remain a lifelong '.$siteName.' member, if for some reason you ever want to
									terminate your account, just go <a href="/cancelaccount" class="links red">here</a> and follow the instructions.
									There are no refunds for any fees paid. 
									<b>You are solely responsible for terminating your account and this agreement</b>.<br/>
									'.$siteName.' may terminate your access to all or any part of the website at any time, with or without cause, with or without notice, effective immediately.
								</li>
								<li>
									<h4>Revisions:</h4>'.$siteName.' reserves the right, at its sole discretion, to modify or replace any part of this Agreement. 
									In most cases, we’ll let you know one way or another; such as by revising 
									the date at the very top of this page or providing you with additional notice 
									(such as flash notifications) or by adding a statement on our homepage.
									However, It is your responsibility to check this Agreement periodically for changes. Your continued use of or access to 
									the website following the posting of any changes to this Agreement constitutes acceptance of those changes. 
									'.$siteName.' may also, in the future, offer new services and/or features through the website. Such new features and/or services shall be subject to the terms and conditions of this Agreement.
								</li>
							</ol>						
						</div>
					</div>
				</div>
			</div>'
		));

		break;





	case $privacyPolTab:

		/////TRACK SESSION//////
		$ENGINE->set_global_var('ss', $seenPrivacyPol, $GLOBAL_userId);
				
		$SITE->buildPageHtml(array('pageTitle'=>'Privacy Policy',
			'preBodyMetas'=>$SITE->getNavBreadcrumbs($policySubNav.'<li><a href="'.$baseUrl.$privacyPolTab.'" title="">Privacy Policy</a></li>'),
			'pageBody'=>'						
			<div class="single-base blend">
				<div class="base-ctrl">		
					'.$policyNavTabs.'				
					<div class="panel panel-mine-1">	
						<h1 class="panel-head page-title">Privacy Policy</h1>
						<div class="panel-body align-l">	
							<h4 class="alert alert-warning">
								<span class="lgreen">'.$siteName.'’s privacy policy was last updated on September 18, 2017.<br/></span>
								This policy may be subject to change at any time without notice. Hence we advise that users check this page periodically.
							</h4>									
							<p>
								'.$siteName.' is a community to express yourself, learn, share and build your career. '.$siteName.' has
								over 100 sections where you live in the moment, learn about the world, have fun, immerse yourself in global events and  get knowledge and informations
								in all fields and walks of life ranging from science and technology to entertainment.
							</p>
							<p>
								When you use these services and any others we may roll out, 
								you’ll share some information with us. We get that that can affect your privacy.
								So we want to be upfront about the information we collect, how we use it and whom we share it with;
								that’s why we’ve written this privacy policy. 							
								<br/>
								Of course, if you still have questions about our
								policy, please feel free to <a class="links" href="/contact-us">contact us</a>.
							</p>

							<h2>Information We Collect</h2>

							There are two basic categories of information we collect:<br/>
							<ol class="ol has-circular-bullets no-list-type">
								<li>Information you choose to give us.</li>
								<li>Information we get when you use our services.</li>							
							</ol>
							<p>Here’s a little more detail on each of these categories.</p>

							<h3>Information You Choose to Give Us</h3>
							<p>
								When you register and interact with our services, we collect the information that you choose to share with us. 
								For example, our registration page requires you to set up a basic '.$siteName.' account, so we need to collect 
								a few important details about you, such as: a unique username you’d like to go by, a password, an email 
								address, first name, last name and your date of birth. To make it easier for others to find you, we may also 
								ask you to provide us with some additional information that will be publicly visible on our services, 
								such as profile pictures, location or other useful identifying informations.
							</p>
							<p>
								'.$siteName.' is a community aimed at the general public. All informations sent when you make a post or upload
								a file will be visible to the whole world.
								Only send messages or share content that you would want someone to see, save or share.
							</p>
							<p>
								It probably goes without saying, but we’ll say it anyway: When you contact <a href="/contact-us" class="links">'.$siteName.' Support</a> 
								or communicate with us in any other way, we’ll collect whatever information you volunteer.
							</p>
							
							<h3>Information We Get When You Use Our Services</h3>
							<p>
								When you use our services, we may collect information about which of those services you’ve used and how you’ve used them. 
								We might know, for instance, that you viewed a thread, saw a specific ad for a certain period of time.
								<br/>Here’s a fuller explanation of the types of information we collect when you use our services :
							</p>
							<p class="'.$hasCbullet.'">
								<b class="blue">Usage Information: </b>
								We may collect information about your activity through our services. For example, we may collect information 
								about:<br/>
								how you interact with the services, such as which section or thread you view most, pages you visited before
								or after navigating to our website, which filters you apply to Search, or which search queries you submit.<br/>
								how you communicate with other '.$siteName.' members, such as their names, the time and date of your communications,
								the number of private messages(pm) you exchange with them, which members you exchange messages with the most, 
								and your interactions with messages (such as when you open a message).
							</p>
							<p class="'.$hasCbullet.'">
								<b class="blue">Content Information:</b>
								We may collect information about the content you provide and the metadata that is provided with the content.
							</p>							
							<p class="'.$hasCbullet.'">
								<b class="blue">Device Information:</b>
								We may collect device-specific information, such as the hardware model, operating system version, advertising identifier,
								IP address, browser type, language, wireless network, and mobile network information.
							</p>													
							<p class="'.$hasCbullet.'">
								<b class="blue">Location Information:</b>
								When you use our services, we may collect information about your location. With your consent, we may also
								collect information about your precise location using methods that include GPS, wireless networks, 
								cell towers, Wi-Fi access points, and other sensors, such as gyroscopes, accelerometers, and compasses.
							</p>
							<p class="'.$hasCbullet.'">
								<b class="blue">Information Collected by Cookies and Other Technologies:</b> 
								Like most online services and internet applications, we may use cookies and other technologies, such as 
								web beacons, web storage, and unique advertising identifiers, to collect information about your activity, 
								browser, and device. 
								Most web browsers are set to accept cookies by default. If you prefer, you can often remove or reject 
								browser cookies through the settings on your browser or device. 
								Keep in mind though, that removing or rejecting cookies could affect the availability and functionality 
								of our services.
							</p>
								
							<h3>Information We Collect from Third Parties</h3>
							<p>
								We may collect information that other users provide about you when they use our services. For example, 
								if a user allows us to collect information from their device phone book and you’re on their 
								contact list; we may combine the information we collect from that user’s phone book with other information we
								have collected about you. We may also obtain information from other companies that are owned or operated 
								by us, or any other third-party sources, and combine that with the information we collect through our services.
							</p>
							
							<h2>How We Use Information</h2>

							<p>What do we do with the information we collect?</p>
							<b class="green">The short answer is:</b>
							<ol class="ol has-circular-bullets no-list-type">
								<li>
									To develop, operate, improve, deliver, maintain, and protect our products and services and
									provide you with an amazing set of products and services that we relentlessly improve.
								</li>
								<li>
									To send you communications, including by email. For example, we may use email to respond to support inquiries 
									or to share information about our products, services, and promotional offers that we think may interest you.
								</li>
								<li>
									To monitor and analyze trends and usage.
								</li>
								<li>
									To personalize the services by, among other things, customizing the content we show you, including ads.
								</li>
								<li>
									To improve ad targeting and measurement using information we’ve collected from cookies and other
									technology to enhance the services and your experience with them..									
								</li>
								<li>
									To enhance the safety and security of our products and services.
								</li>
								<li>
									To verify your identity and prevent fraud or other unauthorized or illegal activity.
								</li>
							
							</ol>
							<p>
								We may also store some information locally on your device. For example, we may store information as local cache
								so that you can open the website and view content faster.
							</p>

							<h2>How We Share Information</h2>

							<p>We may share information about you in the following ways:</p>
							<p>
								<b class="blue">Personal Information:</b>
								We may share information about you, such as your username, full name, birthday, location and profile picture.
							</p>
							<p>
								<b class="blue">Personal Content:</b>
								content you post or send, your followers, posts you share or posts you like; will all be shared with the general public.
							</p>															
							<p>
								<b class="blue">Third parties for legal reasons:</b>							
								We may share information about you if we reasonably believe that disclosing the information is needed to:
								<ol class="ol has-circular-bullets no-list-type">
									<li>comply with any valid legal process, governmental request, or applicable law, rule, or regulation.</li>
									<li>investigate, remedy, or enforce potential Terms of Service violations.</li>
									<li>protect the rights, property, and safety of us, our users, or others.</li>
									<li>detect and resolve any fraud or security concerns.</li>									
								</ol>
								We may also share with third parties(such as advertisers), non-personally identifiable, or de-identified information.
							</p>							
																	
							<b>Third-Party Content:</b>
							The services may also contain third-party links.
							Through these links, you may be providing information (including personal information) directly to the 
							third party, us, or both.
							<br/> You acknowledge and agree that we are not responsible for how those third parties 
							collect or use your information. As always, we encourage you to review the privacy policy of every third-party
							website or service that you visit or use, including those third parties you interact with through our service
							<br/>This information may be used to, among other things, analyze and 
							track data, determine the popularity of certain content, and better understand your online activity.
							
							<p>
								<b>Revoking Permissions:</b> If you change your mind about our ongoing ability to collect 
								information from certain sources that you have already consented to, 
								such as your phone’s location services, you can simply revoke your consent by 
								changing the settings on your device if your device offers those options. 
								Of course, if you do that, certain services may lose full functionality.
							</p>
							<p>
								<b class="red">Account Termination:</b>
								While we hope you’ll remain a lifelong '.$siteName.' member, if for some reason you ever want to
								terminate your account, just go <a href="/cancelaccount" class="links red">here</a> and follow the instructions.
							</p>

							<b>Revisions to the Privacy Policy:</b>

							We may change this privacy policy from time to time. But when we do, 
							we’ll let you know one way or another. Sometimes, we’ll let you know by revising 
							the date at the very top of this privacy policy page.
							Other times, we may provide you with additional notice 
							(such as flash notifications) or by adding a statement on our homepage.																																
						</div>
					</div>
				</div>
			</div>'
		));

		break;





	case $adsPolTab:

		/////TRACK SESSION//////
		$ENGINE->set_global_var('ss', $seenAdsPol, $GLOBAL_userId);			

		$SITE->buildPageHtml(array('pageTitle'=>'Ad Policy',
			'preBodyMetas'=>$SITE->getNavBreadcrumbs($policySubNav.'<li><a href="'.$baseUrl.$adsPolTab.'" title="">Ad Policy</a></li>'),
			'pageBody'=>'
			<div class="single-base blend">
				<div class="base-ctrl">								
					'.$policyNavTabs.'				
					<div class="panel panel-mine-1">
						<h1 class="panel-head page-title">Ad Policy</h1>
						<div class="panel-body align-l">
							<h4 class="alert alert-warning">
								<span class="lgreen">'.$siteName.'’s ad policy was last updated on September 18, 2017.<br/></span>
								This policy is subject to change at any time without notice. Advertisers are advised to check this page periodically.
							</h4>									
							<div class="bg-warning base-pad">									
								<p class="'.$hasCbullet.'">							
									Advertisers on '.$siteName.' are responsible for their '.$siteName.' Ads. 
									Advertisers are responsible for understanding and complying with all applicable laws and regulations. Failure to comply may result in a variety of 
									consequences, including the cancellation of ads you have placed in any section of this community and termination of your account.
									<br/>If you are managing ads on behalf of other advertisers, you are responsible for ensuring that each advertiser complies with this Advertising Policy.													
								</p>
								<p class="'.$hasCbullet.'">							
									The <b><a class="links" href="/estimated-ad-rates">cost of advertising</a></b> on the various sections of this community are estimated cost on the long run.
									<br/>'.$siteName.' therefore reserves the right to subject it to variations especially on short term period during which the advertising costs
									are strongly correlated with traffic levels.						 
								</p>
								<p class="'.$hasCbullet.'">
									Advertisers are advised to place their ads on suitable sections only.<br/>
									We reserve the right to reject, approve, disapprove or remove any ad for any reason, in our sole discretion, including ads that negatively 
									affect our relationship with our users or that promote content, services, or activities, contrary to our competitive position, interests, or 
									advertising philosophy.								
								
								</p>
								<p class="'.$hasCbullet.'">
									In order to make our community mobile friendly, Ads may either be <b class="red">shuffled</b> or <b class="red">hidden</b> <b>on mobile devices</b> depending on the ad type and techinical specifications.
								</p>
							</div>
							<p>							
								This article describes our advertising policy. Our policy require you to follow the law, but they are not legal advice
								and it applies to all ads and commercial content served by or purchased through '.$siteName.' community.
							</p>
							<p>We organize our policy around four broad areas:</p>
							<ol class="ol">							
								<li>
									<b class="red">PROHIBITED CONTENT</b> - Content you can’t advertise on the '.$siteName.' community
								</li>
								<li>
									<b class="red">PROHIBITED PRACTICES</b> - Things you can’t do if you want to advertise with us
								</li>
								<li>
									<b class="red">RESTRICTED CONTENT</b> - Contents you can advertise, but with limitations
								</li>
								<li>
									 <b class="red">EDITORIAL AND TECHNICAL</b> - Quality standards for your ads, websites, and apps
								</li>
							</ol>
								 
								  
							<h3>PROHIBITED CONTENT</h3>
							<ol class="ol has-circular-bullets no-list-type">							
								<li>
									<b class="blue">Counterfeit products:</b>
									'.$siteName.' prohibits the sale or promotion for sale of counterfeit goods. 								
								</li>							
								<li>
									<b class="blue">Dangerous products or services:</b>
									We want to help keep people safe both online and offline, so we don’t allow the promotion of 
									some products or services that cause damage, harm, or injury such as weapons, ammunition, or explosives.
								</li>
								<li>
									<b class="blue">Violent or confronting content:</b>
									We value diversity and respect for others, and we strive to avoid offending users, so we don’t allow ads 
									or destinations that display shocking content or promote hatred, intolerance, discrimination, or violence against a
									person’s race, religion, beliefs, age, sexual orientation or practices, gender identity,
									disability, medical condition (including physical or mental health), financial status, criminal record, or name.
								</li>																																					
							</ol> 
							
							
							<h3>PROHIBITED PRACTICES</h3>
							<ol class="ol has-circular-bullets no-list-type">							
								<li>
									<b class="blue">Misrepresentation:</b>
									We don’t want users to feel misled by ads that we deliver, so we strive to be clear and honest, and provide the information that users need to make informed decisions. We don’t allow ads or destinations that intend to deceive users by excluding 
									relevant information or giving misleading information about products, services, or businesses.
								</li>
								<li>
									<b class="blue">Abusing the ad network:</b>
									We want ads across the '.$siteName.' community to be useful, varied, relevant, and safe for users. We don’t allow ads, content, or destinations that are malicious or attempt to trick or circumvent our 
									ad review processes. We take this issue very seriously, so play fair.
								</li>
								<li>
									<b class="blue">Minors:</b>
									Ads targeted to minors must not promote products, services, or content that are inappropriate, illegal,
									or unsafe, or that exploit, mislead, or exert undue pressure on the age groups targeted.
								</li>
								<li>
									<b class="blue">Data collection and use:</b>
									We want users to trust that information about them will be respected and handled with appropriate care. As such, our advertising partners should not misuse this information, nor collect it for 
									unclear purposes or without appropriate security measures.
								</li>
								<li>
									<b class="blue">Lead Ad:</b>								
									Advertisers must not create Lead Ad questions to request the following types of information without
									our prior written permission: Religion or philosophical beliefs, Race or ethnicity, Usernames or passwords, Insurance information, Health information,
									Account numbers, Financial information, Government-issued identifiers(Social Security numbers, passport numbers or driver’s license numbers), Criminal or arrest history, Trade Union membership status, Political affiliation.
									
								</li>
							</ol> 
							
							
							
							<h3>RESTRICTED CONTENT</h3>
							<div>
								The policy below cover content that is sometimes legally or culturally sensitive. Online advertising can be a powerful way to reach customers, but in sensitive areas,
								we also work hard to avoid showing these ads when and where they might be inappropriate.
								<br/>For that reason, we allow the promotion of the content below, but restrict them to a particular section.							
							</div>
							<ol class="ol has-circular-bullets no-list-type">							
								<li>
									<b class="blue">Adult content(<i class="red">18+</i>):</b>
									Ads should respect user preferences and comply with legal regulations, so we don’t allow certain kinds of adult content in ads and destinations. 
									Some kinds of adult-oriented ads and destinations are allowed if they comply with our policy and don’t target minors, 
									
									but they will only show in Adult sections of the community based on user search queries, user age, and local laws where the ad is being served.
								</li>
								<li>
									<b class="blue">Alcohol:</b>
									We abide by local alcohol laws and industry standards, so we don’t allow certain kinds of alcohol-related advertising, 
									both for alcohol and drinks that resemble alcohol. Some types of alcohol-related ads are allowed if they comply with our policy, 
									don’t target minors, and target only countries that are explicitly allowed to show alcohol ads.
								</li>
								<li>
									<b class="blue">Healthcare and medicines:</b>
									We are dedicated to following advertising regulations for healthcare and medicine, so we expect that ads and destinations follow appropriate laws and industry standards. 
									Some healthcare-related content can’t be advertised at all, while others can only be advertised if the advertiser is certified and targets only approved countries. 
									Check local regulations for the areas you want to target.
								</li>
								<li>
									<b class="blue">Copyrights & Trademarks:</b>
									We don’t allow ads with Content that infringes upon or violates the rights of any third party, including copyright, trademark, 
									privacy, publicity, or other personal or proprietary rights.<br/>								
									If you are legally authorized to use copyrighted content, apply for certification to advertise. 
									If you see unauthorized content, submit a copyright-related complaint using <a class="links" href="'.$baseUrl.$dmcaPolTab.'">our DMCA notice</a> channel.
								</li>													
								<li>
									<b class="blue">Real Money Gambling and games:</b>
									We support responsible gambling advertising and abide by local gambling laws and industry standards, so we don’t allow certain kinds of gambling-related advertising.
									Gambling-related ads are allowed if they comply with our policy and the advertiser has received the proper certification. Gambling ads must target approved countries, 
									have a landing page that displays information about responsible gambling, and never target minors. Check local regulations for the areas you want to target.
								</li>							
								<li>
									<b class="blue">Political content:</b>
									We expect all political ads and destinations to comply with the local campaign and election laws for any area the ads target. This policy includes legally mandated election “silence periods.”
								</li>
								<li>
									<b class="blue">Financial services:</b>
									We want users to have adequate information to make informed financial decisions. Our policy is designed to give users information to weigh the costs associated with financial products 
									and to protect users from harmful or deceitful practices. For the purposes of this policy, we consider financial 
									products and services to be those related to the management and investment of money, including personalized advice.
									<br/>When promoting financial services and products, you must comply with state and local regulations for any region that your ads target.
								</li>							
							</ol>
							
							
							<h3>EDITORIAL AND TECHNICAL</h3>
							<div>
								We want to deliver ads that are engaging for users without being annoying or difficult
								to interact with, so we’ve developed editorial requirements to help keep your ads appealing to users. 
								We’ve also specified technical requirements to help users and advertisers get the most out of the variety of ad formats we offer.
							</div>
							<ol class="ol" type="i">	
								<li>
									<b class="blue">Editorial:</b>
									<ol class="ol has-circular-bullets no-list-type">							
										<li>
											In order to provide a quality user experience, '.$siteName.' requires that all ads, extensions, and destinations meet high 
											professional and editorial standards. We only allow ads that are clear, professional in appearance, and that lead users to content that 
											is relevant, useful, and easy to interact with.	
											<br>
											<b>Relevancy:</b> All components of an ad, including any text, images, or other media, must be relevant and 
											appropriate to the product or service being offered and the audience viewing the ad.
											<br>
											<b>Accuracy:</b> Ads must clearly represent the company, product, service, or brand that is being advertised.

										</li>									
									</ol>
								</li>
								<li>
									<b class="blue">Destination Requirement:</b>
									<ol class="ol has-circular-bullets no-list-type">							
										<li>
											We want users to have a good experience when they click on an ad, so ad destinations must offer unique value to users
											and be functional, useful, and easy to navigate.										
											The destination site may not offer or link to any prohibited product or service.

										</li>									
									</ol>
								</li>
								<li>
									<b class="blue">Technical requirements:</b><br/>
									To help us keep ads clear and functional, advertisers must meet our technical requirements.
									<ol class="ol has-circular-bullets no-list-type">							
										<li>
											Ad format requirements:<br/>
											In order to help you provide a quality user experience and deliver attractive, professional looking ads, 
											we only allow promotions that comply with specific requirements for each ad. Review the format-specific requirements for the type of campaign you wish to run.<br/>
											Please comply with character limits for the ad headline or body, image size requirements, file size limits, and aspect ratios.
										</li>									
									</ol>
								</li>								
							</ol> 
							<h3>About Our Policy</h3>
							<div>
								'.$siteName.'’s <b>'.AD_MATRIX.'</b> ad matrix platform enables businesses of all sizes from around the world, to promote a wide variety of products, services, applications, and websites on '.$siteName.' community. 
								We want to help you reach existing and potential customers and targeted audiences. However, to help create a safe and positive experience for users, we listen to their <a href="/feedback" class="links">feedback</a> and concerns about the types of ads they see. 
								We also regularly review changes in online trends and practices, industry norms, and regulations. And finally, in crafting our policy, we also think about our values and culture as a company, as well as operational, technical, 
								and business considerations. As a result, we have created a set of policy that apply to all promotions on the '.$siteName.' community.						
								<br/>'.$siteName.' requires that advertisers comply with all applicable laws and regulations and the '.$siteName.'’s ad policy described above. It’s important that you familiarize yourself with and 
								keep up to date on these requirements for a smooth ad campaign on this community. When we find content that violates these requirements, we may block it from appearing, 
								and in cases of repeated or egregious violations, we may stop you from advertising with us.
							</div>
							
							<p>
								<b>Revisions to the ad policy:</b>
								We may change this ad policy from time to time. But when we do, 
								we’ll let you know one way or another. Sometimes, we’ll let you know by revising 
								the date at the very top of this page.
								Other times, we may provide you with additional notice 
								(such as adding a statement to our website homepage).
							</p>						
							<p>
								<br/><b class="red">Need help?</b><br/>
								If you have questions about our policy, Please do let us know by contacting <a href="/contact-us" class="links">'.$siteName.' Support</a>
							</p>																
						</div>
					</div>
				</div>
			</div>'
		));
		
		break;




		
	case $badgeRepPolTab:
	
		/////TRACK SESSION//////
		$ENGINE->set_global_var('ss', $seenBadgeRepPol, $GLOBAL_userId);
		
		$SITE->buildPageHtml(array('pageTitle'=>'Badges & Reputation',
			'preBodyMetas'=>$SITE->getNavBreadcrumbs($policySubNav.'<li><a href="'.$baseUrl.$badgeRepPolTab.'" title="">Badges & Reputations</a></li>'),
			'pageBody'=>'			
			<div class="single-base blend">
				<div class="base-ctrl">		
					'.$policyNavTabs.'
					<div class="panel panel-mine-1">	
						<h1 class="panel-head page-title">Badges & Reputations</h1>
						<div class="panel-body align-l">
							<h2 id="badges" class="hash-focus">What are badges?</h2>
							<p>
								Badges are awarded to users in recognition of their contributions to the community. 
								There are many ways to contribute, and consequently, there are many badges.
								The badges are of various ranks and irrespective of the type, shape, color or design are
								generally classified into three classes: 
								<br/><b><i '.BRONZE_INLINE_STYLE.' class="'.$cBullet.'"></i>Bronze</b><br/>
								<b><i '.SILVER_INLINE_STYLE.' class="'.$cBullet.'"></i>Silver</b><br/> 
								<b><i '.GOLD_INLINE_STYLE.' class="'.$cBullet.'"></i>Gold</b>
							</p>
							<p>
								Bronze badges are relatively easy to get and are often gained by completing simple community tasks.							 
								<br/>Silver badges are more difficult to earn, and can be gained for things like posting extremely insightful questions,
								answers or contributions as well as a dedication to moderation and improvement of site content. 
								<br/>Gold badges are the most difficult to earn, and generally signify outstanding dedication or achievement.
							</p>
							All badges that a user has earned are displayed on the user’s profile broken down by rank and optionally in all the user’s post. 						
							<div>
								Most badges a user can earn don’t have any effect on site functionality; they are simply signs of accomplishment and bragging rights. 
								<p class="text-info bold">User’s abilities are governed not by badges, but by their reputation points.</p> 
							</div>

							<h2 id="reputations" class="hash-focus">What are reputations? How do I earn or lose it?</h2>
							<p>
								Reputation is a rough measurement of how much the community trusts you; it is earned by convincing your peers that you know what
								you’re talking about and that your post/comment is credible.<br/>
								Basic use of the site, including creating a thread and making posts does not require any reputation at all.
								But the more reputation you earn, the more privileges you gain.
							</p>
							<p>
								The primary way to gain reputation is by creating interesting threads and posting interesting  and useful contributions. 
								Votes on these posts cause you to gain (or sometimes lose) reputation.<br/>							
								You gain reputation when:<br/>												
															
								<b class="'.$hasCbullet.'">your post is voted up: +'.POST_UPVOTED_REP.'</b><br/>		
								<b class="'.$hasCbullet.'">your post is shared within the community: +'.POST_SHARED_REP.'</b><br/>							
								<b class="'.$hasCbullet.'">your post is shared socially: +'.POST_SOCIAL_SHARED_REP.'</b><br/>							
								<b class="'.$hasCbullet.'">your thread is shared socially: +'.THREAD_SOCIAL_SHARED_REP.'</b><br/>							
								<b class="'.$hasCbullet.'">your thread gain followers: +'.THREAD_GAIN_FOLLOWS_REP.'</b><br/>							
								<b class="'.$hasCbullet.'">you gain followers: +'.MEMBER_GAIN_FOLLOWS_REP.'</b><br/><br/>						
								<!--<b class="'.$hasCbullet.'">you gain new badge: +accompanied reputation</b><br/><br/>-->

								You lose reputation when:<br/>

								<b class="'.$hasCbullet.'">your post is down voted: -'.POST_DOWNVOTED_REP.'</b><br/>
								<b class="'.$hasCbullet.'">your post is unshared within the community: -'.POST_UNSHARED_REP.'</b><br/>
								<b class="'.$hasCbullet.'">your post receives four ('.N_POST_FLAG.') flags: -'.FOUR_POST_FLAGS_REP.'</b><br/>							
								<b class="'.$hasCbullet.'">your thread lose a follower: -'.THREAD_LOSE_FOLLOWS_REP.'</b><br/>							
								<b class="'.$hasCbullet.'">you lose a follower: -'.MEMBER_LOSE_FOLLOWS_REP.'</b><br/>							
								<!--<b class="'.$hasCbullet.'">you lose a badge : - accompanied reputation</b><br/>-->
							</p>
							<p>
								All users start with one reputation point, and reputation can never drop below 1. 
								Deleted posts do not affect reputation, for voters, authors or anyone else involved. 
								<br/>If a user reverses a vote, the corresponding reputation loss or gain will be reversed as well. 
								Vote reversal as a result of voting fraud will also return lost or gained reputation.
							</p>
							<p>
								At the very end of this reputation spectrum there is little difference between users 
								with high reputation and community moderators. That is intentional. 
							</p>
							<h2 id="privileges" class="hash-focus">What privileges do i get with my reputation?</h2>
							<p>
								<div class="table-responsive table-basic">
									<table>
										<tr><th>S/N</th><th>REPUTATION</th><th>PRIVILEGE</th><tr>
										<tr><td>1</td>  <td>'.$ENGINE->format_number(FLAG_POST_REP).'</td>  <td>Flag posts</td></tr>
										<tr><td>2</td>  <td>'.$ENGINE->format_number(VOTE_UP_REP).'</td>  <td>Up vote</td></tr>
										<tr><td>3</td>  <td>'.$ENGINE->format_number(VOTE_DOWN_REP).'</td>  <td>Down vote</td></tr>
										<tr><td>4</td>  <td>'.$ENGINE->format_number(TAG_HOT_REP).'</td>    <td>Tag your thread hot</td></tr>
										<tr><td>5</td>  <td>'.$ENGINE->format_number(POST_IN_PROTECTED_REP).'</td>    <td>Post in protected threads(Depending on the protection level, the reputation may be higher or lower)</td></tr>
										<tr><td>6</td>  <td>'.$ENGINE->format_number(RENAME_THREAD_REP).'</td>    <td>Rename your threads</td></tr>
										<tr><td>7</td>  <td>'.$ENGINE->format_number(MOVE_THREAD_REP).'</td>    <td>Move your threads to new section</td></tr>
										<tr><td>8</td>  <td>'.$ENGINE->format_number(LOCK_POST_REP).'</td>    <td>Lock/Unlock your posts</td></tr>
										<tr><td>9</td>  <td>'.$ENGINE->format_number(HIDE_POST_REP).'</td>    <td>Hide/Show your posts</td></tr>
										<tr><td>10</td>	<td>'.$ENGINE->format_number(CLOSE_THREAD_REP).'</td>	 <td>Close/Open your threads</td></tr>
										<tr><td>11</td>	<td>'.$ENGINE->format_number(PROTECT_THREAD_REP).'</td>	 <td>Protect your threads</td></tr>
										<tr><td>12</td>	<td>'.$ENGINE->format_number(LOCK_THREAD_REP).'</td>	 <td>Lock your thread</td></tr>
										<tr><td>13</td>	<td>'.$ENGINE->format_number(MOD_REP).'</td>	 <td>Moderator(Two section privileges and Access to mod tools)</td></tr>
										<tr><td>14</td> <td>'.$ENGINE->format_number(SUPER_MOD_REP).'</td>	 <td>Super Moderator(Supervisor Access - one category privileges)</td></tr>
										<tr><td>15</td> <td>'.$ENGINE->format_number(ULTIMATE_MOD_REP).'</td>	 <td>Ultimate User(Manager Access - Trusted global user with delete privileges)</td></tr>							
									</table>
								</div>
							</p>
							
							<h2>What thread protection level can i access with my reputation?</h2>
							<p>
								<div class="table-responsive table-basic">
									<table>
										<tr><th>S/N</th><th>REPUTATION</th><th>PROTECTION LEVEL</th><tr>
										<tr><td>1</td>  <td>'.$ENGINE->format_number(STD_LV_ACCESS_REP).'</td>  <td>STANDARD</td></tr>
										<tr><td>2</td>  <td>'.$ENGINE->format_number(CLS_LV_ACCESS_REP).'</td>  <td>CLASSIC</td></tr>
										<tr><td>3</td>  <td>'.$ENGINE->format_number(PRM_LV_ACCESS_REP).'</td>   <td>PREMIUM</td></tr>
										<tr><td>4</td>  <td>'.$ENGINE->format_number(ELT_LV_ACCESS_REP).'</td>  <td>ELITE</td></tr>
										<tr><td>5</td>  <td>'.$ENGINE->format_number(LRD_LV_ACCESS_REP).'</td>  <td>LORD</td></tr>
										<tr><td>6</td>  <td>'.$ENGINE->format_number(MST_LV_ACCESS_REP).'</td>  <td>MASTER</td></tr>
										<tr><td>7</td>  <td>'.$ENGINE->format_number(ROY_LV_ACCESS_REP).'</td>  <td>ROYAL</td></tr>
										<tr><td>8</td>	<td>'.$ENGINE->format_number(ULT_LV_ACCESS_REP).'</td>	 <td>ULTIMATE</td></tr>									
									</table>
								</div>							
							</p>
							<p>
								If users do not meet the required reputation of a protected thread, they are still allowed to view the thread but they won’t be able 
								to participate(post, vote, share ...).<br/>
								An ultimate protection level changes this behavior and users who do not meet the required reputation cannot view the contents of the 
								thread at all.
								
							</p>
							<p class="alert alert-info">						
								NOTE: If you are unable to carry out an action using your reputation or privilege, please know that an action of a
								higher ranking staff or moderator is overriding it. Please resolve such issues by contacting the relevant teams.						
							</p>
													
							<h2>How do users get badges?</h2>
							Users get badges by participating on the site. The list of badges below summarizes what is specifically needed to gain each badge. 
							When a user meets the criteria for a badge, an automated background process adds the badge to the user’s account.
							<p class="alert alert-success">
								Some badges (especially Bronze) are yours to keep once earned even if you no longer meet the criterias.
								However, in some cases, the system will confiscate a Silver, Gold or even Bronze badge
								if you no longer meet the criteria to keep it.
							</p>
							<p>
								Some badges can be earned more than once. 
								The badges listing on a user’s profile will display a multiplier next to each badge indicating 
								the number of times it has been earned by the user.
								<br/>
								In some cases badges gained or lost may not show up immediately, but it will instead be awarded the next time the system recalculates badges,
								which occurs periodically.
							</p>
													
							<h2 class="prime">Badges('.$badgesAndReputations->getBadgeCount(array('cid'=>'')).')</h2>
							 <span class="sky-blue">(the multiplier(x) denotes the number of times awarded '.($GLOBAL_isStaff? 'and the adder(+) signify the accompanied reputation' : '').')</span>
							<ol class="ol" class="no-list-type">
								'.$badgesAndReputations->loadBadges(array('n'=>10, 'cid'=>GENERIC_BADGE_CATEGORY)).'
								<div><b><a href="/badges" class="links">view all badges >>></a></b></div>
							</ol>
							All badges are intelligently awarded by the system base on laid down algorithm.<br/>
							Upon successful registration all members are awarded the studentship badge.
							Other badges are subsquently awarded over time base on performance, commitment and contributions.<br/>
							<span class="alert alert-warning">NOTE: Badges are not awarded in the order listed above and new badges upon request and evaluation will be added subsequently.</span>
																													
						</div>
					</div>
				</div>
			</div>'
		));

		break;




	
	case $modsPolTab:

		$SITE->buildPageHtml(array('pageTitle'=>'Mods Policy',
			'preBodyMetas'=>$SITE->getNavBreadcrumbs($policySubNav.'<li><a href="'.$baseUrl.$modsPolTab.'" title="">Mods Policy</a></li>'),
			'pageBody'=>'			
			<div class="single-base blend">
				<div class="base-ctrl">								
					'.$policyNavTabs.'			
					<div class="panel panel-mine-1">	
						<h1 class="panel-head page-title">Moderators Agreement</h1>
						<div class="panel-body align-l">
							<h4 class="alert alert-warning">
								<span class="lgreen">'.$siteName.'’s moderators agreement was last updated on September 18, 2017.<br/></span>
								This agreement is subject to change at any time without notice. Hence moderators are advised to check this page periodically.
							</h4>										
							
							In order to access the moderator functions of our community, moderators must review and 
							accept the following terms:<br/>
							<b>The agreement:</b><br/>
							I acknowledge and agree that as a moderator for '.$siteName.';<br/>
							<ol class="ol">
								<li>
									I will abide by the then-current <a href="'.$baseUrl.$tosPolTab.'" class="links">Terms of Service</a> of '.$siteName.' and other moderator policies made available to me.
								</li>
								<li>
									I acknowledge that I may have access to potentially personally-identifying information about '.$siteName.' users and 
									that in connection with such access
									I will use such information solely in accordance with the then-current <a href="'.$baseUrl.$privacyPolTab.'" class="links">Privacy Policy</a>
									of '.$siteName.'.<br/>
									<b class="'.$hasCbullet.'">I will not disclose this information to anyone</b><br/>
									<b class="'.$hasCbullet.'">I will not store or copy this information and </b><br/>
									<b class="'.$hasCbullet.'">I will only use such information in connection with performance as a '.$siteName.' moderator for the benefit of '.$siteName.'.</b><br/>
								</li>
								<li>
									I acknowledge and agree that I am an independent volunteer moderator to '.$siteName.' and I am not an employee, agent or representative of '.$siteName.', 
									and I have no authority to bind '.$siteName.' in any manner.
								</li>
								<li>
									'.$siteName.' reserves the right to terminate my privileges as a moderator at any time without notice.
								</li>
							</ol>
						</div>
					</div>
				</div>
			</div>'
		));

		break;



			

	case $gfrPolTab:
	
		/////TRACK SESSION//////
		$ENGINE->set_global_var('ss', $seenGfrPol, $GLOBAL_userId);

		$SITE->buildPageHtml(array('pageTitle'=>'General Community Rules',
			'preBodyMetas'=>$SITE->getNavBreadcrumbs($policySubNav.'<li><a href="'.$baseUrl.$gfrPolTab.'" title="">General Community Rules</a></li>'),
			'pageBody'=>'			
			<div class="single-base blend">
				<div class="base-ctrl">								
					'.$policyNavTabs.'				
					<div class="panel panel-mine-1">
						<h1 class="panel-head page-title">General Community Rules</h1>
						<div class="panel-body align-l">
							<h4 class="alert alert-warning">
								<span class="lgreen">'.$siteName.'’s general community rules were last updated on September 18, 2017.<br/></span>
								These rules are subject to change at any time without notice. Hence users are advised to check this page periodically.
							</h4>					
							'.$SITE->getCommunityRules(false, false).'		
						</div>
					</div>
				</div>
			</div>'
		));

		break;


	

	case $dmcaPolTab:
	
		/////TRACK SESSION//////
		$ENGINE->set_global_var('ss', $seenDmcaPol, $GLOBAL_userId);

		$SITE->buildPageHtml(array('pageTitle'=>'Digital Millennium Copyright Act',
			'preBodyMetas'=>$SITE->getNavBreadcrumbs($policySubNav.'<li><a href="'.$baseUrl.$dmcaPolTab.'" title="">DMCA</a></li>'),
			'pageBody'=>'			
			<div class="single-base blend">
				<div class="base-ctrl">								
					'.$policyNavTabs.'				
					<div class="panel panel-mine-1">
						<h1 class="panel-head page-title">Digital Millennium Copyright Act(DMCA)</h1>
						<div class="panel-body align-l">
							<h4 class="alert alert-warning">
								<span class="lgreen">'.$siteName.'’s Digital Millennium Copyright Act(DMCA) was last updated on September 18, 2017.<br/></span>
								Our DMCA is subject to change at any time without notice. Hence users are advised to check this page periodically.
							</h4>
							<p>
								As '.$siteNameBold.' ask others to respect its intellectual property rights, it respects the intellectual property rights of others. 
								<br/>'.$siteNameBold.' is open to all copyright holders and ready to work with them to ensure that all infringing contents and materials are 
								removed from our services.
								<p>
									We do our best in monitoring contents or materials served through our services,
									however If you believe that materials located on or linked to by '.$siteName.' violates your copyright or copyright of someone you represent, you are encouraged to file a "<b>DMCA Take Down Notice</b>". 
									<br/>'.$siteNameBold.' will respond to all such <b>valid</b> notices that comply with the requirements of the Digital Millennium Copyright Act(DMCA) and other intellectual property laws by removing the infringing material(s) or disabling all links to the infringing material(s).
								</p>
								<h4 class="lgreen">DMCA Take Down Notice Requirements</h4>
								<ol class="ol prime">
									<li>Your full name</li>
									<li><b>Precise</b> link(s) to the infringing material(s)</li>
									<li>Proof of patent right</li>
								</ol>
								<h4>To file a DMCA complaint, please E-mail <b class="red">all</b> the above requirements to '.DMCA_EMAIL_ADDR.'</h4>
							</p>	
						</div>
					</div>
				</div>
			</div>'
		));

		break;




		
	case 'home-index':
			
		$SITE->buildPageHtml(array('pageTitle'=>'Policies',
			'preBodyMetas'=>$SITE->getNavBreadcrumbs('<li><a href="/policies" title="">Policies</a></li>'),
			'pageBody'=>'			
			<div class="single-base blend">
				<div class="base-ctrl">																
					<div class="panel panel-mine-1">
						<h1 class="panel-head page-title">Our Policies</h1>
						<div class="panel-body align-l">
							<div class="alert alert-warning align-c">
								Hey there! thank you for visiting <a class="links" href="/">'.$npDomain.'</a>, we are glad to have You.
								<br/> We have compiled below all the policies that govern our operations. Please do take your time to go through them.
								<p class="red">By using our website/services, we take it that you have read and accepted all our operational policies outlined in the links below.</p>
							</div>
							'.$policyNavTabs.'					
						</div>
					</div>
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