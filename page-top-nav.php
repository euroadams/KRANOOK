<?php

require_once ('page-common-headers.php');

$membersCount=$t1=$privilege=$privilegeShown=$newAvatarLikeCount=$welcomeGreeting=$dashboardName=$pmCounts=$newInboxCount=
$lsCounts=$newLikesShares=$qmCounts=$newQmCount=$fsCounts=$newFsCount=$ftCounts=$newFtCount=$fmCounts=$newFmCount="";

global $ENGINE, $FORUM, $dbm, $GLOBAL_username, $GLOBAL_userId, $GLOBAL_mediaRootFav, $GLOBAL_mediaRootAvt,
$GLOBAL_mediaRootAvtXCL, $GLOBAL_siteName, $GLOBAL_siteDomain, $GLOBAL_privilege, $ACCOUNT, $GLOBAL_page_self_rel,
$GLOBAL_isTrusted, $GLOBAL_isAdmin, $GLOBAL_isStaff, $GLOBAL_isSeasonGreetings, $GLOBAL_isValentineGreetings,
$FA_user, $FA_signIn, $FA_signOut, $FA_envelope, $FA_home, $FA_retweet, $FA_tasks, $FA_infoCircle, $FA_wrench, $FA_inbox,
$FA_cog, $FA_search, $FA_hdd, $FA_book, $FA_trash, $FA_contact, $FA_about;

$sessUsername = $GLOBAL_username;
$sessUid = $GLOBAL_userId;
$mediaRootFav =  $GLOBAL_mediaRootFav;
$mediaRootAvt =  $GLOBAL_mediaRootAvt;
$mediaRootAvtXCL =  $GLOBAL_mediaRootAvtXCL;
$siteName =  $GLOBAL_siteName;
$siteDomain =  $GLOBAL_siteDomain;


//////GET THE USER PRIVILEGE//////

$privilege = $GLOBAL_privilege;

$trustedUser = $GLOBAL_isTrusted;

if($GLOBAL_isStaff)
	$privilegeShown = $privilege.$ACCOUNT->SESS->getUserSeal($sessUsername);


$newCountClass = '';
$newCountClsDrp = '';

////GET TOTAL REGISTERED MEMBERS/////////

////PDO QUERY///////

$sql = "SELECT COUNT(*) FROM users WHERE USERNAME !='' ";
$membersCount = $ENGINE->format_number($dbm->query($sql)->fetchColumn());

if($sessUsername){
			
	///////FETCH USER OLD DETAILS //////////	
		
	$oldQm = $ACCOUNT->SESS->getQuotesMentionsLastViewTime();				
	$oldAl = $ACCOUNT->SESS->getAvatarLikesLastViewTime();	

	//// NEW AVATAR LIKES///////////	
	$dplCounts = $FORUM->avatarLikesHandler(array('uid'=>$sessUid,'countNewSessLikes'=>true));
			 
	if($dplCounts)				
		$newAvatarLikeCount =  '<a href="/avatar-likes" class="links">Avatar Likes(<span class="'.$newCountClass.'">'.$dplCounts.'</span>)</a>';		 
		 

	/////////////FETCH TOTAL MESSAGE IN USER'S INBOX////////
	$pmCounts = $SITE->pmHandler(array('countSessNew'=>true));	
	$newInboxCount = $pmCounts? '(<span class="'.$newCountClass.'">'.$pmCounts. '</span>)' : '';

	///////////FETCH TOTAL NUMBER OF NEW LIKES & SHARES///////	
	$lsCounts = ($FORUM->votesHandler(array('type'=>'u','uid'=>$sessUid,'countSessNewUpvotes'=>true)) + $FORUM->sharesHandler(array('type'=>'u','uid'=>$sessUid,'countSessNewShares'=>true)));		
	$newLikesShares = $lsCounts? '(<span class="'.$newCountClass.'">'.$lsCounts.'</span>)' : '';
	
	
	//////FETCH TOTAL NUMBER OF NEW QUOTES & TAGS//////
	///////////PDO QUERY/////
	$sql = "SELECT COUNT(*) FROM posts WHERE MESSAGE LIKE ? AND TIME > ?";
	$valArr = array("%".$sessUsername."%", $oldQm);
	$qmCounts = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
	
	///////FETCH TOTAL NUMBER OF NEW POSTS IN FOLLOWED TOPICS/////////
	$ftCounts = $FORUM->followedTopicsHandler(array('uid'=>$sessUid,'countNewNonSessPost'=>true));

	/////////FETCH TOTAL NUMBER OF NEW POSTS IN FOLLOWED SECTIONS/////////	
	$fsCounts = $FORUM->followedSectionsHandler(array('uid'=>$sessUid,'countNewNonSessPost'=>true));
		
	/////FETCH TOTAL NUMBER OF NEW POSTS FROM FOLLOWED MEMBERS//////////		
	$fmCounts = $FORUM->followedMembersHandler(array('uid'=>$sessUid,'countNewNonSessPost'=>true));	
	
	//////ADD STYLES//////
	$newQmCount = $qmCounts?  '(<span class="'.$newCountClass.'">'.$qmCounts.'</span>)' : '';
	
	$newFtCount = $ftCounts? '(<span class="'.$newCountClass.'">'.$ftCounts.'</span>)' : '';
	
	$newFsCount = $fsCounts? '(<span class="'.$newCountClass.'">'.$fsCounts.'</span>)' : '';
	
	$newFmCount = $fmCounts? '(<span class="'.$newCountClass.'">'.$fmCounts.'</span>)' : '';
	
			
	//////UPDATE LAST_SEEN COLUMN ON THE FLY/////////
							
	######COUNT USER DAILY VISITS#########
	$subUpdate = "";
	
	////MAKE SURE YOU HAVEN'T COUNTED IT TODAY////
	if(!$ENGINE->get_date_safe('', "d-M-Y", array('cmpRef'=>$ACCOUNT->SESS->getLastDailyVisitDate()))){
	
		$subUpdate = ", DVC = ".($ENGINE->get_date_safe('', "d-M-Y", array('cmpRef'=>$ACCOUNT->SESS->getNextDailyVisitDate()))? "(DVC + 1)" : "0").", LDVD = NOW()";
	
		/////RESET DAILY PM COUNTER DPMC EVERY NEW DAY; AUTOMATICALLY INCREMENTED WHEN A USER SENDS PM
		$subUpdate .= ", DPMC = 0 " ;
	
	}
	
	$ACCOUNT->updateUser($sessUsername, 'LAST_SEEN=NOW()'.$subUpdate);
	
	/////CHECK IF USER HAS CAMPAIGN///////
	$adsCampaign = new AdsCampaign('');
	$hasBannerCampaign = $adsCampaign->countCampaign($sessUid);
	$adsCampaign->setCampaignType('text');
	$hasTextCampaign = $adsCampaign->countCampaign($sessUid);
	$hasAdCampaigns = ($hasBannerCampaign || $hasTextCampaign);




}   

////ACCUMULATE FLOATING MENU NAV////////

$dp=$dpNoAnchor=$accNav=$home=$join=$login=$viewProfLink=$inbox=$logout=$sendMssgLink=$welcome=$guest="";

if(!$sessUsername){
	
	$guest = 'Guest';
	
	$join = '<li><a href="/signup" title="Register/join Now">'.$FA_user.'Signup</a></li>';
	
	$login = '<li><a href="/login#lun" title="Login into your account">'.$FA_signIn.'<b>Login</b></a></li>';
}


if($sessUsername){
	
	$dp = $ACCOUNT->SESS->getDp($sessUsername, array('type'=>''));
	$dp = $dp? '<div class="nav-avatar no-hover-bg _hv-react-base">'.$dp.'</div>' : $dp;
	$anchorDp = $ACCOUNT->SESS->getDp($sessUsername, array('anchor'=>false, 'type'=>'vcard', 'cardSize'=>'xs', 'ipane'=>false, 'bcls'=>'dsk-platform-dpn'))
					.' <span class="mob-platform-dpn-i">'.$FA_user.'</span> ';
	$welcomeGreeting = $ACCOUNT->sanitizeUserSlug($sessUsername, array('anchor'=>true, 'youRef'=>false, 'titleCase'=>false, 'urlText'=>$anchorDp.ucfirst($ACCOUNT->SESS->getFirstName())));
	
	$dashboardName = ucwords($ACCOUNT->SESS->getFullName());
	$email = '('.$ACCOUNT->SESS->getEmail().')';

	$sendMssgLink = '<li><a href="/pm" >'.$FA_envelope.'Send PM</a></li>';
	$sendMssgLink .= '<li><a href="/email" >'.$FA_envelope.'Send Email</a></li>';
	
}

	
	$welcome = '<li><span class="timed-greeting"></span>&nbsp;&nbsp;<b>'.$guest.$welcomeGreeting.'</b>:</li>';
	$home = '<li><a href="/" >'.$FA_home.'Home</a></li>';
	$logout = '<li><b><a href="/logout" title="logout from this device and location">Logout'.$FA_signOut.'</a></b></li>';
	$logoutInDropMenu = $logout.'<li><b><a href="/logout-all" title="logout from all devices and locations">Logout All'.$FA_signOut.'</a></b></li>';
	$accNav .= '<li><a href="/trending-topics" >Trending</a></li>';
	$accNav .= '<li><a href="/featured-hot-topics" >Featured Hot</a></li>';
	$accNav .= '<li><a href="/new-topics" >New Topics</a></li>';
	$accNav .= '<li><a href="/latest-post" >Latest Posts</a></li>';
	$inbox = '<li '.hasNcount($pmCounts).'><a href="/inbox">Inbox'.$newInboxCount.'</a></li>';
	$ls = '<li '.hasNcount($lsCounts).'><a  href="/votes-shares/upvotes">Upvotes & Shares'.$newLikesShares.'</a></li>';
	$qm = '<li '.hasNcount($qmCounts).'><a href="/posts-you-were-quoted-or-tagged/quotes">Quoted/Tagged You'.$newQmCount.'</a></li>';
	$fs = '<li '.hasNcount($fsCounts).'><a href="/followed-sections/posts">Followed Sections'.$newFsCount.'</a></li>';
	$ft = '<li '.hasNcount($ftCounts).'><a href="/followed-topics">Followed Topics'.$newFtCount.'</a></li>';
	$fm = '<li '.hasNcount($fmCounts).'><a href="/followed-members/posts">Followed Members'.$newFmCount.'</a></li>';
	$cmpg = '<li><a href="/ads-campaign/banner-campaign">Ads Campaign</a></li>';
	
if($sessUsername){

	if($dplCounts)
		$viewProfLink .= '<li '.hasNcount($dplCounts).'>'.$newAvatarLikeCount.'</li>';

	$pmCounts? ($accNav .= $inbox) : '';

	$lsCounts? ($accNav .= $ls) : '';

	$qmCounts? ($accNav .= $qm) : '';

	$fsCounts? ($accNav .= $fs) : '';

	$ftCounts? ($accNav .= $ft) : '';

	$fmCounts? ($accNav .= $fm) : '';

	$hasAdCampaigns? ($accNav .= $cmpg) : '';


}

$dropDownTipsData = ' data-field-tip="true"  data-tip-pos="left" data-tip-loader="'.($dropDownTipLoader='_234drpDwnLd').'" ';

$membersCount .= ' member'.(($membersCount == 1)? '' : 's');

$menunav = $join.$login.$viewProfLink/*.$sendMssgLink*/.$accNav;


//////////ACCUMULATE DROPDOWN MENU//////////
 
 $dropdownNav="";
 
if($sessUsername){

	$dropdownNav = '<li class="has-dropdown">
						<a class="has-caret" data-toggle="nav-dropdown" >'.$anchorDp.$dashboardName.'</a>
						<ul class="dropdown-menu">			
							<li>'.$ACCOUNT->sanitizeUserSlug($sessUsername, array('anchor'=>true, 'youRef'=>false, 'titleCase'=>false, 'urlText'=>$anchorDp.'Profile')).'</li> 
							<li><a href="/edit-profile" >Edit Profile</a></li> 							
							'.$inbox.$sendMssgLink.$cmpg.$logoutInDropMenu.'
						</ul>
					</li>';

	$dropdownNav .= '<li class="has-dropdown">
						<a class="has-caret" data-toggle="nav-dropdown" >My Zone</a>
						<ul class="dropdown-menu">			 							
							'.$ls.$qm.$fs.$ft.$fm.'							
							<li><a href="/posts-you-upvoted">Posts You Upvoted</a></li>
							<li><a href="/posts-you-downvoted">Posts You Downvoted</a></li>
							<li><a href="/posts-you-shared">Posts You Shared</a></li>
							<li><a href="/posts-shared-with-you">Posts Shared With You</a></li>
							<li><a href="/posts-you-quoted">Posts You Quoted</a></li>
						</ul>
					</li>';
					

}

$dropdownNav .= '<li class="has-dropdown">
					<a class="has-caret" data-toggle="nav-dropdown" >'.$siteName.'</a>
					<ul class="dropdown-menu">
						<li><a href="/about-us">'.$FA_about.'About</a></li>
						<li><a href="/contact-us">'.$FA_contact.'Contact</a></li>
						<li><a href="/feedback">'.$FA_retweet.'Support/Feedback</a></li>
						<li><a href="/policies">'.$FA_infoCircle.'Policies</a></li>
						<li><a href="/faq">'.$FA_infoCircle.'Faq</a></li>
						<li><a href="/ads-campaign#hta">'.$FA_infoCircle.'How to Advertise</a></li>
						<li><a href="/estimated-ad-rates">'.$SITE->getFA('fa-dollar-sign').'Estimated Ad Rates</a></li>
						<li><a href="/badges">'.$FA_tasks.'Badges</a></li>
						<li><a href="/tools">'.$FA_wrench.'Site Tools</a></li>
					</ul>
				</li>';
	
if($GLOBAL_isStaff){
			
		$dropdownNav .=  '<li class="has-dropdown">
								<a class="has-caret"  data-toggle="nav-dropdown" >'.$FA_wrench.'Mod Tools</a>
								<ul class="dropdown-menu">
									<li><a href="/mod-tools" >'.$FA_wrench.'Moderation Tools</a></li>
									<li><a href="/reported-cases/manage-reports" >'.$FA_inbox.'Reports & Bans</a></li>'.
									($trustedUser? '<li><a href="/ad-campaign-manager/banner-campaign" >'.$FA_cog.'Campaign Manager</a></li>' : '').'
									<li><a href="/search-users" >'.$FA_search.'Search a User</a></li>
								</ul>
							</li>';
									
	if($GLOBAL_isAdmin){
		
		$dropdownNav .=  '<li class="has-dropdown">
								<a class="has-caret" data-toggle="nav-dropdown" >'.$FA_wrench.'Admins Only</a>
								<ul class="dropdown-menu">		
									<li><a href="/received-feedbacks" >'.$FA_retweet.'FeedBacks</a></li>
									<li><a href="/ad-campaign-manager/banner-campaign" >'.$FA_cog.'Campaign Manager</a></li>
									<li><a href="/db-manager/manage-sections" >'.$FA_hdd.'DB Manager</a></li>
									<li><a href="/dynamic-cms/page-manager" >'.$FA_book.'Page Manager</a></li>
									<li><a href="/auto-pilot-page" >'.$FA_wrench.'Run Pilots</a></li>	
									<li><a href="/site-back-doors" >'.$FA_wrench.'Back Doors</a></li>								
									<li><a href="/search-ip-address" >'.$FA_search.'Ip Search </a></li>									
									<li><a href="/site-clean-up" >'.$FA_trash.'Clean Up</a></li>
								</ul>
							</li>';
	}

}

$brandLogo = $SITE->getBrandLogo();
$searchBar = $SITE->getNavSearch('tn2');
			
function hasNcount($n, $cls='', $c_only=false){
	
	$cls = $cls? $cls : 'has-ncount';
	return ($n? ($c_only? ' '.$cls.' ' : 'class="'.$cls.'"' ) : '');
		
}

function getNavToggle($eTag='', $pCls='',  $cls='', $brandLogo=false){
	
	global $SITE;
	
	$eTag = $eTag? $eTag : 'div';
	
	return '<'.$eTag.' class="nav-toggle-bar '.$pCls.'">					
				<span class="nav-toggle '.$cls.'" data-toggle="smartToggler" data-id-targets="mobile-main-nav" data-target-attr="class|slideUpX" data-attr-only="true" title="Toggle Navigation" >
					<i class="menu-iconbar"></i><i class="menu-iconbar"></i><i class="menu-iconbar"></i>
				</span>'.($brandLogo? $SITE->getBrandLogo() : '').' 
			</'.$eTag.'>';
}

function getCollapseNav($dropdownNav, $dp){
	
	global $SITE;
	
	return '<div id="mobile-main-nav" class="collapse-nav afix-full">
				'.getNavToggle('', 'align-r', 'close-icon', true).'											
				<div class="mob-platform-dpn clear">				
					'.$dp.'				
				</div>
				<div class="mob-platform-dpn base-tb-mg">
					'.$SITE->getNavSearch('tnc2').' 				
				</div>				
				<ul class="nav nav-inline nav-classic">'.$dropdownNav.'</ul>
				<div class="align-c" style="margin-top: 50px;">
					'.$SITE->getBrandLogo().'
					<div class="follow-us no-hover-bg">'.$SITE->getSocialLinks(array('type'=>'followus')).'</div>
					<div class="dwhite">'.$SITE->getCopyRight('prime-sc1').'</div>
				</div>
			</div>';
}

$navToggle = getNavToggle();
$inNavLogout = $sessUsername? $logout : '';
$inCollapseNavLogin = $sessUsername? '' : $login;
list($adminOpensCtrlState, $adminOpensCtrlBtn) = $SITE->sessCtrlProps('admin-opens');

?>
<!--PAGE AUTO DIVS OPEN-->
<div class="_base-scale">		
	<!--END PAGE AUTO DIVS OPEN-->
	<!--SITE MAIN NAV-->
	<div class="_tnav-bar">
		<div class="top-nav-flex clear">
			<div class="container">
				<?php 
				 echo ((TAKE_DOWN_SITE && $GLOBAL_isAdmin)? '<div class="alert alert-danger has-close-btn">SITE TAKE DOWN IS <b class="green">ACTIVE</b><br/>
											PLEASE DEACTIVATE TO MAKE SITE VISIBLE TO THE PUBLIC
										</div>' : '');
				?>
				<?php echo $SITE->platformSwitchBtn(false).$brandLogo. 
				($topMostNavItems =
				'<div class="nav-statistics pull-l">
					<div>
						<b class="black">Statistics:</b> 
						<b>'.$membersCount.','.$SITE->siteStatistics().'</b>
					</div>
					<div>
						<b class="black">Date:</b>
						<b id="date" class="">'.date('D, jS M Y').'</b>,
						<b class="black">Time: <span id="time" class="prime-1">'.date('H:i:s A').'</span></b> <span id="serveT" hidden="hidden" >'.time().'</span><span id="serveTS" hidden="hidden" >'.date('A').'</span>						
						&nbsp;&nbsp;<span class="prime-1 bold">'.($GLOBAL_isStaff? $privilegeShown : '').'</span>
					</div>
				</div>	
				<div class="dsk-platform-dpn has-field-elastic pull-r">'.$searchBar.'</div>'
				.$SITE->getThemeModeProps('toggle')
				);
				?>
			</div>
		</div>
		<div class="container">
			<header class="top-nav" >	
				<nav class="nav-base base-xs">
					<ul class="nav nav-inline dropdown-platform-dpn" data-hover-triggered-dropdownsx="true" <?php if(isset($dropDownTipsData)) echo '';?>>							
						<?php 
						echo $welcome.$menunav.$dropdownNav.$inNavLogout;
						?>
					</ul>					
					<?php 
					echo $navToggle.
						getCollapseNav($dropdownNav.$inCollapseNavLogin, $dp);
					?>
					<div class="sticky-nav hide">
						<div class="container">
							<ul class="nav nav-inline nav-sticky dropdown-platform-dpn">
								<?php 
									$stickyCls = 'mob-platform-sticky';
									echo $welcome.$menunav.$dropdownNav.$inNavLogout.
									'<li class="bold">'.($GLOBAL_isStaff? $privilegeShown : '').'</li>'.
									getNavToggle('li', 'pull-mob-l '.$stickyCls).$SITE->getBrandLogo('li', $stickyCls, '', $SITE->getThemeModeProps('toggle', false).'<hr/>'.$adminOpensCtrlBtn);	
									
								?>
							</ul>
						</div>
					</div>
				</nav>
			</header>
		</div>
	</div>
	<span class="hide" id="<?php if(isset($dropDownTipLoader)) echo $dropDownTipLoader;?>">press esc on your keyboard to close this dropdown menu</span>
	<div class="pop-out-base">
		<div id="cab" title="click to hide"></div>		
		<div id="ajax-pop-out-base"></div>		
	</div>
	<noscript>
		<?php 
			echo $SITE->getPops('', true); 
		?>
	</noscript>
	
	<!--END SITE MAIN NAV-->	