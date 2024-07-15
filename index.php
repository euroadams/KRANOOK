<?php

require_once ('page-common-headers.php');

$postBtnTxt = 'POST';

/////////VARIABLE INITIALIZATION//////////////

$pageUrl = '';

////ADULT VIEW PROMPT ACCEPT//////
if(isset($_SESSION[$K = 'adult_vds_accepted']) && isset($_GET[$K]) && $_SESSION[$K] == $_GET[$K]){

	$_SESSION['ADULT_VIEW_PROMPT_ACCEPTED'] = true;

	if(isset($_GET[$K = 'adult_vds_accepted_tkid']) && $adultVdsTokenId = $_GET[$K])
		$_SESSION['ADULT_VIEW_PROMPT_ACCEPTED_TOKEN_'.$adultVdsTokenId] = true;

}	

////AJAX PRELOAD COMPOSER REQUEST (SPEEDS UP COMPOSER LOAD)//////
if(isset($_POST['ajaxPreloadComposer'])){
	
	//ACCUMULATE COMPOSER METAS	
	$metaArr['uiBgPreWrap'] = $metaArr['uiBgComposerFormClass'] = 
	$metaArr['uiBgCloseBtn'] = $metaArr['uiBgField'] = $metaArr['alertMods'] = 
	$metaArr['uiBgFillCls'] = $metaArr['uiBgLoadedStyles'] = $metaArr['uiBgLoader'] = 
	$metaArr['uiBgPostWrap'] = $metaArr['threadTagUpdateAccess'] = $metaArr['ftopic'] = 
	$metaArr['uploadFilesView'] = $metaArr['postModifyIdField'] = 
	$metaArr['isPostEdit'] = $metaArr['autofocus'] = $metaArr['pageSelf'] = $metaArr['textContent'] = 
	$metaArr['tags'] = $metaArr['vdsChk'] = $metaArr['fileTooLarge'] = $metaArr['uploadErr'] = 
	$metaArr['syndicateUrl'] = ''; 
	$metaArr['postBtnTxt'] = $postBtnTxt;
	
	$jsonArr['htmlForm'] = $GLOBAL_username? $SITE->getComposer($metaArr) : '';	
	echo json_encode($jsonArr);	
	exit;
	
}	


////AJAX POPULATE MODERATORS SECTION SELECT DROP DOWN LIST//////
if(isset($_POST['ajaxPopulateModsSectionSelectList'])){
	
	list($moderatedSectionListOptions) = $FORUM->getModPageSectionSelectList();
	$jsonArr['options'] = '<option value="">--select section--</option>'.$moderatedSectionListOptions;
	echo json_encode($jsonArr);	
	exit;
	
}	


////AJAX POPULATE MODERATOR FORM THREAD URL//////
if(isset($_POST['ajaxPopulateModFormCurrVals'])){

	$tid = $ENGINE->sanitize_number($ENGINE->get_global_var('post', "tid"));
	$sid = $FORUM->getTopicDetail($tid); 
	$sectionName = $FORUM->sectionIdToggle($sid); 
	$jsonArr['threadUrl'] = '<a class="links" href="'.$SITE->getThreadSlug($tid).'">'.$SITE->topicIdToggle($tid).'</a>';
	$jsonArr['currSectionUrl'] = '<a class="links" href="/'.$ENGINE->sanitize_slug($sectionName).'">'.$sectionName.'</a>';
	$jsonArr['currProtection'] = '<b>'.$FORUM->decodeModerationStatus($FORUM->getTopicDetail($tid, "PROTECTION_LEVEL"))[0].'</b>';
	echo json_encode($jsonArr);	
	exit;
	
}	


////grab redirect url if any is passed//////
list($rdr, $rdrAlt) = $SITE->getRdr();

$path = $ENGINE->get_page_path('page_url', '', true, true);


if($path){	
	
	/***GET/HANDLE THE  REQUEST URL*******/
	$pagePathArr = explode('/', $path);
	
	$pageUrl = $pagePathArr[0];				
	
	$path2 = isset($pagePathArr[1])? $pagePathArr[1] : '';
	
	$pageUrlLowerCase = strtolower($pageUrl);
	
	$fall2Index = (!$path2 && $pageUrlLowerCase == strtolower($_SERVER['HTTP_HOST']));
	
	/******HANDLE FOR VIRTUAL PAGES********/
	$popRequest = strtolower(POP_LOADER);
	$timeUpdateRequest = strtolower(SERVERT_LOADER);
	$uibgLoadMoreRequest = strtolower(MORE_UIBG_LOADER);
	$emoticonsLoadMoreRequest = strtolower(MORE_EMOTICONS_LOADER);
	$emoticonsCaptureRecentRequest = strtolower(REMOTICONS_LOADER);
	$emoticonTabsRequest = strtolower(EMOTICON_TABS_LOADER);
	
	/**HANDLE SITE TAKE DOWN REQUEST**/
	if(TAKE_DOWN_SITE && !in_array($pageUrlLowerCase, array($popRequest, $timeUpdateRequest)) && !$GLOBAL_isAdmin){
	
		$title = 'Site Temporarily Unavailable';
		$SITE->buildPageHtml(array('pageTitle' => $title, 'pageHeader' => false, 'pageFooter' => false, 
						'preBodyMetas' => $SITE->getNavBreadcrumbs(),
						'pageBody' => '
						<div class="single-base blend-top">	
							<div class="base-ctrl">				
								<h1 class="page-title pan bg-mine-1">'.$title.' !<br/><span class="red">(ERROR 500)</span></h1>
								<div class="align-l centered-inline">
									<div class="base-pad">
										<div class="align-c"><img class="img-responsive" src="'.$GLOBAL_mediaRootFav.'hi-robot.png" /></div>
										<p>Hi there! We are currently carrying out some crucial maintenance services.</p>
										<p>It might take a while so please do bear with us.</p>
										<p><b>SERVICE WILL BE RESTORED SHORTLY!</b></p>
									</div>
								</div>
							</div>
						</div>'
				));
				
		exit();
	
	}
	

	switch($pageUrlLowerCase){
		
		/**GET POPS AJAX CONTROL**/
		case $popRequest:
		
			echo $SITE->getPops('', $fixed=true, '', '', urlencode($rdr)); 		
			exit();
		
		
		
		/**HANDLE AJAX TIMESTAMP REQUEST**/
		case $timeUpdateRequest:	
							
			echo time().'|'.$ENGINE->get_date_safe('', 'D, jS M. Y');
			exit();
		
		/**HANDLE AJAX UIBG LOAD MORE REQUEST**/
		case $uibgLoadMoreRequest:
								
			$SITE->uiBgHandler();
			exit();
		
		/**HANDLE AJAX EMOTICONS LOAD MORE REQUEST**/
		case $emoticonsLoadMoreRequest:
								
			$SITE->bbcHandler('', array('action' => 'loadMoreEmoticons'));
			exit();
		
		/**HANDLE AJAX EMOTICONS RECENTLY USED CAPTURE REQUEST**/
		case $emoticonsCaptureRecentRequest:
								
			$SITE->bbcHandler('', array('action' => 'remoticon'));
			exit();
		
		/**HANDLE AJAX EMOTICON TABS LOAD REQUEST**/
		case $emoticonTabsRequest:
								
			$SITE->bbcHandler('', array('action' => 'loadEmoticonTabs'));
			exit();
		
		/**HANDLE DND EMAIL LIST SUB/UNSUB**/
		case ($pageTitle='dnd-mail-list'):
		
			$email = 2;
			$action = $path2;				
			$email = isset($pagePathArr[$email])? $pagePathArr[$email] : '';
			
		
			if(!$action || !$email){
		
				include_once(DOC_ROOT.'/page-error.php');			
				exit();	
		
			}
			
			$SITE->buildPageHtml(array("pageTitle"=>$pageTitle,"pageHeader"=>'',"pageFooter"=>'',
				"pageBody"=>$SITE->dndEmailList($action, $email)
			));
			
			exit();
		
		
		/**HANDLE FOR FILE DOWNLOADS**/	
		case 'downloads':
		
			//init referral, file, cleanUp_arr variables			
			$fileReferralPath = 1; 
			$filePath = 2;
			$cleanUpArr = array('archives');
		
			if(isset($pagePathArr[$fileReferralPath]))
				$fileReferral = $pagePathArr[$fileReferralPath];
		
			if(isset($pagePathArr[$filePath]))
				$file = $pagePathArr[$filePath];
		
			$cleanUp = in_array($fileReferral, $cleanUpArr);
			$dir = $SITE->getDownloadDir($fileReferral);
			$ENGINE->download_handler(array('file' => $file, 'dir' => $dir, 'cleanUp' => $cleanUp));			
			exit();
		
	}
	
	
	/******HANDLE FOR DYNAMIC PAGES********/
	
	/**HANDLE FOR CATEGORIES**/
	if($SITE->isCategorySlug($pageUrl))			
		$pageUrlLowerCase = 'load-category';		
	
	/**HANDLE FOR SECTIONS**/
	elseif($SITE->isSectionSlug($pageUrl))				
		$pageUrlLowerCase = 'load-section';		
		
	/**HANDLE FOR DYNAMIC PAGES**/	
	elseif($pageUrl && $SITE->isPageSlug($pageUrl))		
		$SITE->loadDynamicContents(array('type' => 'page', 'token' => $pageUrl, 'parseFullHtml' => true));
	
	/**HANDLE FOR PROFILE**/
	elseif($SITE->isProfileSlug($pageUrl))							
		$pageUrlLowerCase = 'load-profile';
	
	elseif($fall2Index)
		$pageUrlLowerCase = 'load-home';

	
}




/********************HANDLE SINGLE PAGE APP(SPA) REQUESTS*****************/
	
$sessUsername = $GLOBAL_username;
$sessUsernameSlug = $GLOBAL_usernameSanitized;	
$sessUid = $GLOBAL_userId;
$pageSelf = $GLOBAL_page_self;	
$siteDomain =  $GLOBAL_siteDomain;
$siteName =  $GLOBAL_siteName;
$mediaRootAvt =  $GLOBAL_mediaRootAvt;
$mediaRootAvtXCL =  $GLOBAL_mediaRootAvtXCL;
$mediaRootFav =  $GLOBAL_mediaRootFav;
$mediaRootFavXCL = $GLOBAL_mediaRootFavXCL;
$mediaRootBanner = $GLOBAL_mediaRootBanner;
$mediaRootBannerXCL = $GLOBAL_mediaRootBannerXCL;
$mediaRootPost = $GLOBAL_mediaRootPost;
$mediaRootPostXCL = $GLOBAL_mediaRootPostXCL;	
$filePrvwTip = $SITE->getMeta('file-preview-tip');
$pwdMssg = $SITE->getMeta('valid-password-tip');
$unactivatedUserPoster = $SITE->getMeta('unactivated-user-poster');
$composerFieldId = COMPOSER_ID;

/***************************BEGIN URL CONTROLLER****************************/

$full_path = $ENGINE->get_page_path('page_url', '', true);
$pagePathArr = explode('/', $full_path);

if(count($pagePathArr) == 1){

	$pathKeysArr = array('pageUrl');
	$maxPath = 1;	
	
	$ENGINE->url_controller(array('pathKeys' => $pathKeysArr, 'maxPath' => $maxPath));

}	


/*******************************END URL CONTROLLER***************************/


/***********************SWITCH SPA RQST**********************/	

switch($pageUrlLowerCase){
	
	
	/**AJAX EVENTS ENDPOINT**/
	case 'ajaxion':{			
		
		$allowedArr = array($updateThemeModePref='update-user-preferred-theme-mode', $activateAdminOpens=$SITE->getEndpointUrl('admin-opens'));
		
		/***************************BEGIN URL CONTROLLER****************************/
		
		$path2 = isset($pagePathArr[1])? $pagePathArr[1] : '';				
				
		if(in_array($path2, $allowedArr)){	
									
			$pathKeysArr = array('pageUrl', 'endpoint');
			$maxPath = 2;	
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;	
		
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/
		
		$endpoint = isset($_GET[$K="endpoint"])? strtolower($_GET[$K]) : '';
		
		switch($endpoint){
	
			case $updateThemeModePref:

				if($_POST[$K="themeMode"]){
					
					$storageKey = $SITE->getThemeModeProps('storage-key');
					$ACCOUNT->updateUser($sessUsername, 'PREFERRED_THEME_MODE=?', $ENGINE->sanitize_user_input($themeMode=$_POST[$K]));
					$ENGINE->set_global_var('ss', $storageKey, $themeMode);
					$ENGINE->set_cookie($storageKey, $themeMode, (86400 * 365));
					
				}
				break;

			case $activateAdminOpens:
				$SITE->sessCtrlProps('admin-opens', true, true);
				break;
	
		}
		 
		exit();
		
		break;
		
	}
	
	
	
	
	
	/**AVATAR LIKES**/
	case 'avatar-likes': {
		
		$totalAvatarLikes=$messages="";
		
		$rdr = $GLOBAL_rdr;

		/***************************BEGIN URL CONTROLLER****************************/
		
		$pathKeysArr = array('pageUrl', 'pageId');
		$maxPath = 2;		
		
		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/


		if($sessUsername){								

			///UPDATE VIEWED STATUS/////////				
			$ACCOUNT->updateUser($sessUsername, $cols='OLD_AL_COUNTER=NOW()');

			////GET ALL THE SESSION AVATAR LIKES/////////				
			$counts = $FORUM->avatarLikesHandler(array('uid'=>$sessUid,'count'=>true));
			 
			if($counts)				  		
				$totalAvatarLikes = $counts.' Like'.(($counts > 1 )? 's' : '').$SITE->getFA('far fa-thumbs-up active-done-state');
				
			else
				$totalAvatarLikes = $counts.' likes';
						
			$totalRecords = $counts;

			/**********CREATE THE PAGINATION************/			
			$pageUrl = 'avatar-likes';												
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl));				
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageId = $paginationArr["pageId"];
				
			//////////END OF PAGINATION//////
			
			$sql = "SELECT * FROM avatar_likes WHERE (USER_ID = ? AND STATE=1) ORDER BY TIME DESC LIMIT ".$startIndex.",".$perPage;
			
			$valArr = array($sessUid);
			$stmt = $dbm->doSecuredQuery($sql, $valArr);
			
			///////GET LIKE DETAILS//////
			while($row = $dbm->fetchRow($stmt)){
				
				$uid = $row["LIKER_ID"];	
				$username = $ACCOUNT->memberIdToggle($uid);
				$timeLiked = $row["TIME"];
				
				$U = $ACCOUNT->loadUser($uid);
				$likerHasAvatar = $U->getAvatar();

				///FORMAT THE WAY POST DATES ARE SHOWN/////////
				$date =  $ENGINE->time_ago($timeLiked);
				
				//CHECK IF SESSION HAS LIKED BACK OR NOT AND PRESENT OPTIONS ACCORDINGLY/////										
				$hasLikedBack = $FORUM->avatarLikesHandler(array('uid'=>$uid,'liker'=>$sessUid,'check'=>true));
				
				if($hasLikedBack)
					$likeBackLink = '<span id="avl-'.$uid.'">'.$GLOBAL_sessionUrl.' have also liked <b>'.$ACCOUNT->sanitizeUserSlug($username, array('anchor'=>true)).'`s</b> avatar</span> (<a href="/avatars/unlike/'.$username.'/?_rdr='.$rdr.'" class="links dp_like"  data-user="'.$username.'"  data-action="unlike" data-disp="avl-'.$uid.'">Unlike</a>)';
				
				else
					$likeBackLink = $likerHasAvatar? '<span id="avl-'.$uid.'"></span> (<a href="/avatars/like/'.$username.'/?_rdr='.$rdr.'" class="links dp_like"  data-user="'.$username.'" data-action="like" data-disp="avl-'.$uid.'" >Like Back</a>)' : '';
				
				$messages .= $ACCOUNT->getUserVcard($uid, array('time'=>$timeLiked,'append'=>$likeBackLink));
				
			}	
			  
			$messages = $messages? '<div class="hr-dividers">'.$messages.'</div>' :
						'<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.' nobody has liked your avatar yet</span>';
		}else
			$notLogged = $GLOBAL_notLogged;
		
		
		$getToggle = isset($timeLiked)? 
					'(<a title="Clear all your avatar likes"  href="/clear-avatar-likes" class="links" data-toggle="smartToggler" >Clear All</a>)
					<div class="hide modal-drop red has-close-btn">
						<p>You are about to clear all your avatar likes<br/>Please confirm
						<br/>NOTE: If you clear this record too frequently, You may not be able to earn the <b>Photogenic badge</b> 
						as the system uses it to award the badge.
						</p>
						<a role="button" title="Clear all your avatar likes" href="/clear-avatar-likes" class="links clear_dp_likes btn btn-danger" >Clear All</a>
						<button class="btn close-toggle">Close</button>
					</div>' : '';
			
		$getToggle = ''; // Comment out this line to include to clear avatar likes  
			
		$pageTitle	= 'My Avatar Likes';
		
		$SITE->buildPageHtml(array("pageTitle"=>$pageTitle,
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">'.$pageTitle.'</a></li>'),
					"pageBody"=>'										
					<div class="single-base blend">
						<div class="base-ctrl">
							<div class="panel panel-limex">'.
								(isset($notLogged)? $notLogged : '').
								($sessUsername? '									
									<h1 class="panel-head page-title">MY AVATAR LIKES</h1>'.
									((isset($pageId) && $pageId)? '<div class="cpop">(<span class="cyan">'.$pageId.'</span> of '.$totalPage.')</div>' : '').'									
									<div class="panel-body sides-padless" data-has-avatar-like="true" >									
										<div class="" >'.										
											(isset($totalAvatarLikes)? '<span class="black"> Your avatar has (<span class="cyan">'.$totalAvatarLikes.'</span>)<hr/>' : '').
											$FORUM->getPostQUDS($sessUid).'											
											<hr/>
										</div>'.
										$getToggle.
										(isset($pagination)? $pagination : '').'
										<div class="">'.																									
											($messages? $messages : '').'
											<br/>															
										</div>'.
										(isset($pagination)? $pagination : '').
										$getToggle.'	
									</div>'
								: '').'											
							</div>
						</div>
					</div>'
		));
					
		break;
	
	}
	
	
	/**CLEAR AVATAR LIKES**/
	case 'clear-avatar-likes': {
				
		if($sessUsername){			
		
			////PDO QUERY//////////
			
			$sql = "UPDATE avatar_likes SET STATE=0 WHERE USER_ID=?";
			$valArr = array($sessUid);
			$stmt = $dbm->doSecuredQuery($sql, $valArr);		
		
		}	
		
		header("Location:/avatar-likes");
		exit();
		
	}
	
	
	/**DO AVATAR LIKE**/
	case 'avatars':	{
		
		if($sessUsername){		
					
			$taskAction="";	
		
			if(!isset($_POST["user"])){
					

				/***************************BEGIN URL CONTROLLER****************************/
				
				if(isset($pagePathArr[1]) && (strtolower($pagePathArr[1]) == "like" || strtolower($pagePathArr[1]) == "unlike")){	
		
					$pathKeysArr = array('pageUrl', "taskAction", "user");
					$maxPath = 4;	
			
				}else{
				
					$pathKeysArr = array();
					$maxPath = 0;
				
				}

				$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

				/*******************************END URL CONTROLLER***************************/


			}
			
			if(isset($_POST[$K="user"]) || isset($_GET[$K])){
				
				if(isset($_POST[$K]))	
					$user = $_POST[$K];
							
				if(isset($_GET[$K]))	
					$user = $_GET[$K];
				
				if(isset($_POST[$K="taskAction"]))
					$taskAction = $_POST[$K];
				
				if(isset($_GET[$K]))
					$taskAction = $_GET[$K];
											
				$U = $ACCOUNT->loadUser($user);
				$uid = $U->getUserId();
				
				///////AUTHENTICATE
				if($user && $U->getAvatar() && $sessUid != $uid){
					
					$taskAction = strtolower($taskAction);
											
					$FORUM->avatarLikesHandler(array('uid'=>$uid,'liker'=>$sessUid,'action'=>$taskAction));									
											
					$likes = $FORUM->avatarLikesHandler(array('uid'=>$uid,'count'=>true));										
					$likes = $ENGINE->format_number($likes).' Like'.(($likes > 1)? 's ' : ' ');										
					echo $likes;
					
				}

			}
				
			
		}	

		if(!$GLOBAL_isAjax){
			
			header("Location:".$rdrAlt."#dpl");
			exit();
		}
			
		break;	
		
	}
	
	
	
	/**CLEAR MULTIQUOTES**/
	case 'clear-multiquotes': {
					
		if($sessUsername){
				
			///////////PDO QUERY/////

			$sql = "DELETE FROM mq_trackers WHERE  USER_ID=?";
			$valArr = array($sessUid);
			$stmt = $dbm->doSecuredQuery($sql, $valArr);
				
		}
				
		if(!$GLOBAL_isAjax){	
		
			header("Location:".$rdrAlt);
			exit();		
				
		}
			
		break;	
		
	}
	
	
	
	
	/**DELETE AN AD CAMPIGN**/
	case 'delete-campaign':{
		
		$adsCampaign = new AdsCampaign();
		$adsCampaign->processCampaignDelete();
		
		break;
	}
	
	
	
	
	
	/**DELETE PM**/
	case 'delete-pm':{
		
		$table = 'private_messages';
		
		/***************************BEGIN URL CONTROLLER****************************/

		if(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "clear-inbox"){
		
			$pathKeysArr = array('pageUrl', 'tab');
			$maxPath = 2;
			$rdrLnk = '<a href="/inbox" class="links" >back to inbox</a>';
			$returnUrl = 'inbox';
			$sql = "DELETE FROM ".$table." WHERE USER_ID=? AND INBOX != ''";
		
		}elseif(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "old-pm"){
		
			$pathKeysArr = array('pageUrl', 'tab');
			$maxPath = 2;	
			$returnUrl = 'old-inbox';
			$rdrLnk = '<a href="/old-inbox" class="links" >back to old inbox</a>';
			$sql = "DELETE FROM ".$table." WHERE USER_ID=? AND OLD_INBOX != ''";
		
		}elseif(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "selected-pm"){
		
			$pathKeysArr = array('pageUrl', 'tab');
			$maxPath = 2;
			$returnUrl = 'inbox';
			$rdrLnk = '<a href="/inbox" class="links" >back to inbox</a>';
			$sql = "DELETE FROM ".$table." WHERE USER_ID=? AND SELECTION_STATUS=1 ";
		
		}elseif(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "selected-old-pm"){
		
			$pathKeysArr = array('pageUrl', 'tab');
			$maxPath = 2;	
			$returnUrl = 'old-inbox';
			$rdrLnk = '<a href="/old-inbox" class="links" >back to old inbox</a>';
			$sql = "DELETE FROM ".$table." WHERE USER_ID=? AND SELECTION_STATUS=1 ";
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;
		
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		if($sessUsername){	
			
			///////////PDO QUERY////		
			
			$valArr = array($sessUid);
			$stmt = $dbm->doSecuredQuery($sql, $valArr, true);
			$resCount = $dbm->getRecordCount();				

			$data = '<span class="alert alert-success">('.$resCount.') message'.(($resCount > 1)? 's' : '').' has been deleted successfully </span>';
			$ENGINE->set_global_var('ss', 'SESS_ALERT', $data);
		
		}else
			$notLogged = $GLOBAL_notLogged;
		
		
		header("Location:/".$returnUrl);
		exit();
			
	}
	
	
	
	
	
	/**DO PM DELETE CHECK**/
	case 'do-pm-delete-check':{			

		if(isset($_POST[$K="pm"]) || isset($_GET[$K])){
				
			$data=$checkstatus=$row=$checked=$message="";					
			
			if(isset($_POST[$K]))
				$pmIds = $_POST[$K];				
			
			elseif(isset($_GET[$K]))
				$pmIds = $_GET[$K];
			
			
			$pmIdsArr = explode(',', trim($pmIds, ','));
			$placeHolders = trim(str_repeat('?,', count($pmIdsArr)), ',');
			
			/////////////GET CHECK_STATUS FROM DB///////
			
			/////PDO QUERY////////

			$sql = "SELECT SELECTION_STATUS FROM private_messages WHERE USER_ID = ? AND ID IN (".$placeHolders.")";
			$valArr = array_merge(array($sessUid), $pmIdsArr);
			$checkStatus = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
			
			$checkStatus = $checkStatus? 0 : 1;

			///////EXECUTE THE CHECKING ACCORDINGLY IN THE DB/////////
			///////////PDO QUERY///////					
			$sql = "UPDATE private_messages SET SELECTION_STATUS=? WHERE USER_ID = ? AND ID IN (".$placeHolders.")";
			$valArr = array_merge(array($checkStatus, $sessUid), $pmIdsArr);
			$stmt = $dbm->doSecuredQuery($sql, $valArr);
			
			////////GET TOTAL NUMBER OF MESSAGES CHECKED FOR DELETE//////
				///////////PDO QUERY///////					
			$sql = "SELECT COUNT(*) FROM private_messages WHERE USER_ID = ? AND SELECTION_STATUS=1 ";
			$valArr = array($sessUid);
			$recordCount = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();	
			
			$message = $recordCount? '(<span class="red">'.$recordCount.'</span>) message'.(($recordCount > 1)? 's' : '').' checked for delete' : '';

			$message = '<div class="black"><b>'.$message.'</b></div>';
			
			echo $message;

		}

		if(!$GLOBAL_isAjax){

			header("Location:".$rdrAlt);
			exit();

		}

		break;

	}
		
		
			
	
	
	
	/**FIND AND FOCUS ORIGINAL QUOTED POST**/
	case 'find-post':{
		
		/***************************BEGIN URL CONTROLLER****************************/

		if(isset($pagePathArr[1])){	

			$pathKeysArr = array('pageUrl', "post");
			$maxPath = 2;	
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;
			
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		if(isset($_GET[$K="post"])){
		
			$pid = $ENGINE->sanitize_number($_GET[$K]);
			$tid = $FORUM->getPostDetail($pid, "TOPIC_ID");
			$postNumber = $FORUM->getPostNumber($pid, $tid);
			$postPage = $FORUM->getPostPageNumber($postNumber);
			$foundURL = $SITE->getThreadSlug($tid).$postPage.'#'.$postNumber;
		
			if($tid)
				header('Location:'.$foundURL);
			else		
				include_once(DOC_ROOT."/page-error.php");		
			exit();
		
		}

		break;
		
	}
	
	
	
	
	
	/**POST EVENT HISTORIES**/
	case 'post-event-history':{
		
		$messages=$pageCounter=$pagination=$sid='';
		
		/***************************BEGIN URL CONTROLLER****************************/
		
		$path2 = isset($pagePathArr[1])?  strtolower($pagePathArr[1]) : '';
		$path3 = isset($pagePathArr[2])?  $ENGINE->sanitize_number($pagePathArr[2]) : '';
		
		if(in_array($path2, array('downvotes', 'upvotes', 'shares')) && $path3){
			
			switch($path2){
		
				case 'upvotes': $table = $type = 'upvotes'; $col = 'UPPER_ID'; $headerType = 'Upvoters'; break;
		
				case 'downvotes': $table = $type = 'downvotes';  $col = 'DOWNER_ID'; $headerType = 'Downvoters'; break;
		
				case 'shares': $table = $type = 'shares';  $col = 'SHARER_ID'; $headerType = 'Sharers'; break;
		
			}	
					
			$pathKeysArr = array('pageUrl', 'event', 'pid', 'pageId');
			$maxPath = 4;	
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;
			
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/
		
		$pid = isset($_GET[$K="pid"])? $_GET[$K] : '';
		$getPage = isset($_GET[$K="pageId"])? $_GET[$K] : '';
		$title = 'Post-'.$pid.' '.$type.' histories';
		$header = 'Post-'.$pid.' '.$headerType;
		$adSid = ELITE_SID;
		$sessIsAuthor = ($sessUid == $FORUM->getPostDetail($pid, 'POST_AUTHOR_ID'));
		
		if($sessIsAuthor || $GLOBAL_isStaff){
		
			//PDO QUERY///////
			$sql = "SELECT COUNT(*) FROM ".$table." WHERE (POST_ID=?  AND STATE=1)";
			$totalRecords = $dbm->doSecuredQuery($sql, array($pid))->fetchColumn();
			
			/**********CREATE THE PAGINATION*******/										
			$pageUrl  =  $ENGINE->get_page_path('page_url', 3);
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl));					
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageId = $paginationArr["pageId"];
			
			///////////////END OF PAGINATION/////////////
			if($totalRecords){		
			
				$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => '', 'stop' => 1, 'uniqueColumns' => '', 'filterCnd' => 'posts.ID=?', 'orderBy' => ''));
				
				/////DISPLAY THE POST/////
				list($post, $pid, $tid, $sid) = $FORUM->loadPosts($sql, array($pid));
				
				$sql = "SELECT ".$col.", TIME FROM ".$table." WHERE (POST_ID=? AND STATE=1) ORDER BY TIME DESC LIMIT ".$startIndex.",".$perPage;
				$stmt = $dbm->doSecuredQuery($sql, array($pid));
				
				while($row = $dbm->fetchRow($stmt)){
					
					$messages .= $ACCOUNT->getUserVcard($row[$col], array('time'=>$row["TIME"]));
					
				}
		
				$post = '<button class="btn btn-xs btn-info" data-toggle="smartToggler" data-toggle-attr="text|hide the post" >show the post</button><div class="hide">'.$post.'</div>';
				$messages = $post.'<h2 class="page-title bg-limex">'.$headerType.'</h2><ol class="align-l no-list-type hr-dividers">'.$messages.'</ol>';	
				
				$pageCounter = '<div class="cpop">(page <span class="cyan">'.$pageId.'</span> of '.$totalPage.')</div>'.
								'<h3 class="prime">Total '.$headerType.'('.$totalRecords.')</h3>';
			
			}else
				$messages = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', '.($sessIsAuthor? 'you have' : 'there are').' no '.$type.' history for this post yet</span>';
		
		}else
			$messages = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', you do not have enough privilege to view this event history</span>';
						
		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget($sid);
		
		$SITE->buildPageHtml(array("pageTitle"=>$title,
						"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">'.$header.'</a></li>'),
						"pageBody"=>'				
						<div class="single-base blend">
							<div class="base-ctrl base-container">
								'.$pageTopAds.'
								<div class="row">
									<div class="panel panel-mine-1 base-ctrl base-rad '.$leftWidgetClass.'">
										<h1 class="panel-head page-title">'.$header.'</h1>
										'.$pageCounter.'
										<div class="panel-body sides-padless">
											'.$pagination.$messages.$pagination.
										'</div>
									</div>
									'.$rightWidget.'
								</div>
								'.$pageBottomAds.'
							</div>
						</div>'
		));
		 
		
		break;
		
	}
	
	
	
	
	
	/**MODERATORS**/
	case 'activities':{			
		
		$messages=$pageCounter=$pagination=$title=$header='';	
		$allowedArr = array('moderators', 'super-moderators');
		
		/***************************BEGIN URL CONTROLLER****************************/
		
		$path2 = isset($pagePathArr[1])?  strtolower($pagePathArr[1]) : '';			
		$path3 = isset($pagePathArr[2])?  $pagePathArr[2] : '';	
		
		if(in_array($path2, $allowedArr) && $path3){	
									
			$pathKeysArr = array('pageUrl', 'event', 'eventId', 'pageId');
			$maxPath = 4;	
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;	
		
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/
			
		$event = isset($_GET[$K="event"])? strtolower($_GET[$K]) : '';
		$eventId = isset($_GET[$K="eventId"])? $_GET[$K] : '';
		$getPage = isset($_GET[$K="pageId"])? $_GET[$K] : '';
		$adSid = ELITE_SID;	
		
		switch($event){
		
			case 'moderators': 
				$vname = $SITE->sectionIdToggle($eventId);
				$totalRecords = $SITE->moderatedSectionCategoryHandler(array('scId'=>$eventId,'level'=>1,'action'=>'count'));
				$title = $header = ucwords($vname).' -> Moderators ';
				break;
			
			case 'super-moderators': 
				$vname = $SITE->categoryIdToggle($eventId);
				$totalRecords = $SITE->moderatedSectionCategoryHandler(array('scId'=>$eventId,'level'=>2,'action'=>'count'));
				$title = $header = ucwords($vname).' -> Super Moderators ';
				break;
						
		}
		
		if($vname){
		
			if($totalRecords){
				
				/**********CREATE THE PAGINATION*******/					
				$urlhash='ptab';			
				$pageUrl  = $ENGINE->get_page_path('page_url', 3);			 
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'hash'=>$urlhash));					
				$pagination = $paginationArr["pagination"];
				$totalPage = $paginationArr["totalPage"];
				$n = $paginationArr["perPage"];
				$i = $paginationArr["startIndex"];
				$pageId = $paginationArr["pageId"];
				
				switch($event){
		
					case 'moderators':					
						$messages = $SITE->moderatedSectionCategoryHandler(array('scId'=>$eventId,'level'=>1,'action'=>'get','vcard'=>true,'i'=>$i,'n'=>$n));
						break;
		
					case 'super-moderators':					
						$messages = $SITE->moderatedSectionCategoryHandler(array('scId'=>$eventId,'level'=>1,'action'=>'get','vcard'=>true,'i'=>$i,'n'=>$n));
						break;
		
				}
				
				$pageCounter = '<div class="cpop">(page <span class="cyan">'.$pageId.'</span> of '.$totalPage.')</div>'.
									'<h3 class="prime">Total ('.$totalRecords.')</h3>';
				
				$messages = '<div class="'.(isset($topicBase)? 'topic-base' : 'hr-dividers').'">'.$messages.'</div>';

				///////////////END OF PAGINATION/////////////
			}else
				$messages = '<span class="alert alert-danger">Sorry no records were found</span>';
		
		}else
			$messages = '<span class="alert alert-danger">Sorry we could`nt understand your request</span>';
			
		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget($adSid);
		
		$SITE->buildPageHtml(array("pageTitle"=>$title,
						"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">'.$header.'</a></li>'),
						"pageBody"=>'											
						<div class="single-base blend">
							<div class="base-ctrl base-container">
								'.$pageTopAds.'
								<div class="row">
									<div class="panel panel-limex base-ctrl base-rad '.$leftWidgetClass.'">
										<h1 class="panel-head page-title">'.$header.'</h1>
										'.$pageCounter.'
										<div class="panel-body sides-padless">
											'.$pagination.$messages.$pagination.
										'</div>
									</div>
									'.$rightWidget.'
								</div>
								'.$pageBottomAds.'
							</div>
						</div>'
						
		));
		 
		
		break;
		
	}
			
		
		
		
	
	/**USER EVENTS**/
	case 'user-events':{			
		
		$messages=$pageCounter=$pagination=$title=$header='';
		$allowedArr = array($followers='followers', $following='following', $followedTopics='followed-topics', $followedSections='followed-sections', $inbox='inbox', $oldInbox='old-inbox', $sentPm='sent-pm');
		
		/***************************BEGIN URL CONTROLLER****************************/
		
		$path2 = isset($pagePathArr[1])? $ACCOUNT->memberIdToggle($pagePathArr[1]) : '';			
		$path3 = isset($pagePathArr[2])?  strtolower($pagePathArr[2]) : '';	
				
		if(in_array($path3, $allowedArr) && $path2){	
									
			$pathKeysArr = array('pageUrl', 'user', 'event', 'pageId');
			$maxPath = 4;	
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;	
		
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/
		
		$user = isset($_GET[$K="user"])? strtolower($_GET[$K]) : '';
		$event = isset($_GET[$K="event"])? strtolower($_GET[$K]) : '';
		$getPage = isset($_GET[$K="pageId"])? $_GET[$K] : '';
		$adSid = ELITE_SID;
		$accessGranted = (in_array($event, array($followedTopics, $followedSections, $inbox, $oldInbox)) && !$GLOBAL_isAdmin)? false : true;
		
		if($accessGranted){
		
			switch($event){
		
				case $followers:
					$totalRecords = $FORUM->followedMembersHandler(array('uid'=>$user,'count'=>true));
					$title = $header = ucwords($user).' => Followers';
					break;
		
				case $following:
					$totalRecords = $FORUM->followedMembersHandler(array('uid'=>$user,'follower'=>true,'count'=>true));
					$title = $header = ucwords($user).' => Following';
					break;
		
				case $followedTopics: 
					$totalRecords = $FORUM->followedTopicsHandler(array('uid'=>$user,'count'=>true));
					$title = $header = ucwords($user).' => Followed Topics';
					break;
		
				case $followedSections: 
					$totalRecords = $FORUM->followedSectionsHandler(array('uid'=>$user,'count'=>true));
					$title = $header = ucwords($user).' => Followed Sections';
					break;
		
				case $inbox: 
					$SITE->pmHandler(array('uid'=>$user, 'type'=>'inbox', 'backDoor'=>true));					
					break;
		
				case $oldInbox: 
					$SITE->pmHandler(array('uid'=>$user, 'type'=>'old-inbox', 'backDoor'=>true));					
					break;
		
				case $sentPm: 
					$SITE->pmHandler(array('uid'=>$user, 'type'=>'sent-pm', 'backDoor'=>true));					
					break;
		
			}
			
			if($totalRecords){
				
				/**********CREATE THE PAGINATION*******/					
				$urlhash='ptab';			
				$pageUrl  = $ENGINE->get_page_path('page_url', 3);			 
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'hash'=>$urlhash));					
				$pagination = $paginationArr["pagination"];
				$totalPage = $paginationArr["totalPage"];
				$n = $paginationArr["perPage"];
				$i = $paginationArr["startIndex"];
				$pageId = $paginationArr["pageId"];
				
				switch($event){
		
					case $followers:					
						$messages = $FORUM->followedMembersHandler(array('uid'=>$user,'getUserOnly'=>true,'vcard'=>true,'i'=>$i,'n'=>$n));					
						break;
		
					case $following:					
						$messages = $FORUM->followedMembersHandler(array('uid'=>$user,'follower'=>true,'getUserOnly'=>true,'vcard'=>true,'i'=>$i,'n'=>$n));					
						break;
		
					case $followedTopics: 
						$messages = $FORUM->followedTopicsHandler(array('uid'=>$user,'perUI'=>true,'i'=>$i,'n'=>$n));
						$topicBase = true;
						break;
		
					case $followedSections: 
						$messages = $FORUM->followedSectionsHandler(array('uid'=>$user,'perUI'=>true,'i'=>$i,'n'=>$n));
						$topicBase = true;
						break;
		
				
				}
				
				$pageCounter = '<div class="cpop">(page <span class="cyan">'.$pageId.'</span> of '.$totalPage.')</div>'.
									'<h3 class="prime">Total ('.$totalRecords.')</h3>';
				
				$messages = '<div class="'.(isset($topicBase)? 'topic-base' : 'hr-dividers').'">'.$messages.'</div>';

				///////////////END OF PAGINATION/////////////
			}else
				$messages = '<span class="alert alert-danger">'.$user.' has no '.str_ireplace('-', ' ', $event).' yet</span>';
		
		}else{
		
			$messages = $title = 'Sorry you do not have enough privilege to view this page';
			$messages = '<span class="alert alert-danger">'.$messages.'</span>';
		
		}
		
		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget($adSid);
		
		$SITE->buildPageHtml(array("pageTitle"=>$title,
						"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">'.$header.'</a></li>'),
						"pageBody"=>'				
						<div class="single-base blend">
							<div class="base-ctrl base-container">
								'.$pageTopAds.'
								<div class="row">
									<div class="panel panel-limex base-ctrl base-rad '.$leftWidgetClass.'">
										<h1 class="panel-head page-title">'.$header.'</h1>
										'.$pageCounter.'
										<div class="panel-body sides-padless">
											'.$pagination.$messages.$pagination.
										'</div>
									</div>
									'.$rightWidget.'
								</div>
								'.$pageBottomAds.'
							</div>
						</div>'
						
		));
		 
		
		break;
		
	}
	
	
	
	
	
	
	/**FORM GATE**/
	case 'form-gate':{
						
		header("Location:".$rdrAlt);
		exit();
		
		break;
	}
		
	
	
	
	
	
	
	
	
	


	


	
		
	/**LOGIN**/
	case 'login':{
		
		$incorrectPwd=$incorrectUsername=$nullUsername=$nullPwd=$nullFields=$banAlert=$pwdFieldErr=$usernameFieldErr=
		$fullname=$firstName=$loginUsername=$pwd1=$email=$aboutyou=$userdp=$status=
		$uploadTime=$encodedRdrQstr="";
		
		$fErr = 'field-error'; $asterix = '<span class="asterix">*</span>';
		
		/***************************BEGIN URL CONTROLLER****************************/
					
		$pathKeysArr = array('pageUrl');
		$maxPath = 2;						

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		$spinner = $GLOBAL_spinnerXs;

		if($rdr){
		
			$encodedRdrQstr = '?_rdr='.urlencode($rdrAlt);
			
		}
		
		
		####DO VERIFICATION#####			
			
		////GRAB LGP_VER AND OTHER LOGIN PAGE VERIFICATION(LGP_VER) QSTR////////
		if(isset($_GET["_LGP_VER"]) && isset($_GET["_verification"]) &&  
			isset($_GET["LGP_token"]) && isset($_SESSION["LGP_TOKEN"])){
				
				if($_GET["LGP_token"] == $_SESSION["LGP_TOKEN"]){
				
					//header("Refresh:1;url=".$rdrAlt);
					header("Location:".$rdrAlt);
					exit();
		
				}else
					$ACCOUNT->SESS->validate_session(array('invalidate' => true));
				
			
		}

		//////////GET FORM-GATE RESPONSE//////////	
		$alertUser = $SITE->formGateRefreshResponse();
		

		if(isset($_GET["_ulc"]) && isset($_GET["_ulp"]) && isset($_GET["_ule"]) && isset($_GET["_ulu"])){
			if($_GET["_ulc"] && $_GET["_ulp"] && $_GET["_ule"] && $_GET["_ulu"]){

				$email = $ENGINE->sanitize_user_input($_GET["_ule"]);
				$authCode = $_GET["_ulc"];
				$username = $ENGINE->sanitize_user_input($_GET["_ulu"]);
				
				$U = $ACCOUNT->loadUser($email);
				$username = $U->getUsername();
						
				//GET LOGIN UNLOCK AUTHENTICATION CODE//
				$dbAuthCode = $SITE->getAuthentication($email, AUTH_CODE_KEY_UNLOCK_LOGIN);					
				
				if($authCode && $authCode == $dbAuthCode){
					
					//EXPIRE LOGIN UNLOCK AUTHENTICATION CODE//
					$SITE->expireAuthentication($email, AUTH_CODE_KEY_UNLOCK_LOGIN);
							
					$cols = "UNLOCK_LOGIN=0, LOGIN_ATTEMPT=0";
							
					if($ACCOUNT->updateUser($username, $cols))
						$alertUser = '<span class="alert alert-success">Your account has been successfully <b class="green">UNLOCKED</b>. Please proceed to login now.</span>';

					else 
						$alertUser = '<span class="alert alert-danger">Sorry this link has either been altered, expired or is invalid !!!!</span>';
				}
				else
					$alertUser = '<span class="alert alert-danger">Sorry this link has either been altered, expired or is invalid !!!!</span>';
				
			}else
				$alertUser = '<span class="alert alert-danger">Sorry this link has been altered !!!!</span>';
			
		}


		///////ON LOGIN///////

		if(isset($_POST['login'])){

			$loginUsername = $ENGINE->sanitize_user_input($_POST['loginUsername']);
			$loginPwd = $_POST['loginPwd'];

			if($loginUsername && $loginPwd){
				
				////////PDO QUERY///////
						
				$sql = "SELECT ID, USERNAME, PASSWORD, FIRST_NAME, CONFIRMED, EMAIL, LOGIN_ATTEMPT, UNLOCK_LOGIN FROM users WHERE USERNAME=? LIMIT 1";
				$valArr = array($loginUsername);
				$stmt = $dbm->doSecuredQuery($sql, $valArr);
				$row = $dbm->fetchRow($stmt);
					
				if(!empty($row)){

					$userId = $row["ID"];
					$username = $row['USERNAME'];
					$userPwd = $row['PASSWORD'];
					$email = $row['EMAIL'];
					$allowedAttempts = 5;
					$firstName = $row["FIRST_NAME"];
					$loginAttempts = $row['LOGIN_ATTEMPT'];
					$unlockLogin = $row['UNLOCK_LOGIN'];
					$activationStatus = $row['CONFIRMED'];
							
					//CHECK IF A USER IS UNDER A BAN THEN ALERT HIM OR UNBAN HIM IF HIS BAN HAS EXPIRED////
						
					////////////PDO QUERY///////
																	
					/////PDO QUERY///
					
					$sql = "DELETE FROM moderation_bans WHERE (BAN_DURATION <= NOW() AND USER_ID=? AND BAN_STATUS=1)";						
					$valArr = array($userId);
					$wasBanned = $dbm->doSecuredQuery($sql, $valArr, 'chain')->getRowCount();
					
					$sql = "DELETE FROM spam_controls WHERE (BAN_DURATION <= NOW() AND USER_ID=? AND BAN_STATUS=1)";
					$wasBanned += $dbm->doSecuredQuery($sql, $valArr, 'chain')->getRowCount();			
				
						
					//////CHECK IF THE USER IS STILL UNDER BAN///
					list($spamBan, $modsBan) = $ACCOUNT->getBanStatus($userId);				

					if((strtolower($loginUsername) == strtolower($username)) && ($ACCOUNT->passwordVerify($loginPwd, $userPwd)) && $loginAttempts < $allowedAttempts){
						
						$rehashQry = "UPDATE users SET PASSWORD = ? WHERE ID=".$userId." LIMIT 1";
						if($ACCOUNT->checkRehashDoRehash($loginPwd, $userPwd, $rehashQry)){
							
							if($activationStatus == 1 || 1){
										
								if(!$spamBan  && !$modsBan){

									$ACCOUNT->updateUser($userId, $cols="LOGIN_ATTEMPT=0");
								
									///LOAD SESSION PARAMS////
									
									$sessDatasK_arr = $sessDatasV_arr = array();								
									$lcvToken = $ENGINE->generate_token();
									$sessDatasK_arr[] = "LGP_TOKEN";
									$sessDatasV_arr[] = $lcvToken;

									if($wasBanned >= 1){
								
										$sessDatasK_arr[] = "was_banned";
										$sessDatasV_arr[] = true;

									}

									//SET THE ACCUMULATED DATAS FOR AUTHENTICATION//
									$ENGINE->set_global_var('ss', $sessDatasK_arr, $sessDatasV_arr);
									
									//REGISTER LOGIN TO DATABASE//
									///ALSO CAPTURE IF THE USER EXPLICITLY REQUEST TO STAY LOGGED IN////							
														
									$ACCOUNT->registerLogin(array("username" => $username, "userId" => $userId, "stayLoggedIn" => isset($_POST["stayLoggedIn"])));
									
									$LGP_VER = '/login/?_LGP_VER=true&_verification=true&LGP_token='.$lcvToken.'&_rdr='.urlencode($rdr);
									
									if(in_array(mt_rand(1, 10), array(1, 5, 2, 8)))
										header('Refresh:3; url='.$LGP_VER);												
										
									else{
									
										header("Location:".$rdrAlt);
										exit();
									
									}
									
								}else{
									
									$banAlert="";
																			
									/////PDO QUERY//////
										
									$sql = "SELECT BAN_DURATION, TIME_BANNED FROM spam_controls WHERE BAN_STATUS=1 AND USER_ID=? ORDER BY TIME_BANNED DESC LIMIT 1";
									$valArr = array($userId);
									$stmt = $dbm->doSecuredQuery($sql, $valArr);
									$banRow = $dbm->fetchRow($stmt);																				
											
									if(!empty($banRow)){
										
										$timeBanned = $banRow["TIME_BANNED"];
										$banDur = $banRow["BAN_DURATION"];								
										list($sbd, $banHrs) = $ENGINE->time_difference($banDur, $timeBanned, true);	
										$expires = $SITE->getCountDownClockBox($banDur);

										$banAlert = '<div class="alert alert-danger">Sorry <span class="cyan">'.$username.'</span>, you are under '.$banHrs.' hour'.(($banHrs == 1)? '' : 's').' spam ban and cannot login into 
											your account until your ban is lifted.<br/> your ban will expire within the next '.$expires.'</div>';
									 
									}
										
									$modBanHrs="";
																					
									///PDO QUERY////
										
									$sql = "SELECT BAN_DURATION, TIME FROM moderation_bans WHERE BAN_DURATION >=NOW() AND USER_ID=? AND BAN_STATUS=1 LIMIT 1";
									$valArr = array($userId);
									$stmt = $dbm->doSecuredQuery($sql, $valArr);
									$banRow = $dbm->fetchRow($stmt);																				
									
									if(!empty($banRow)){
										
										$timeBanned = $banRow["TIME"];
										$banDur = $banRow["BAN_DURATION"];
										list($mbd, $modBanHrs) = $ENGINE->time_difference($banDur, $timeBanned, true);
										
										$expires = $SITE->getCountDownClockBox($banDur);
										
										if($modBanHrs)
											$banAlert .= '<div class="alert alert-danger">'.((!$banAlert)? 'Sorry ' : '').'<span class="cyan">'.$username.' </span>, you are '.(($banAlert)? 'also' : '').' under a '.$modBanHrs.' hour'.(($modBanHrs == 1)? '' : 's').' moderator ban and cannot login into 
											your account until your ban is lifted. <br/>your ban will expire within the next: '.$expires.'</div>';
																				
																			
									}
									
								}


							}else{	
																			
								$notConfirmed = '<span class="alert alert-danger">sorry your account has not been activated<br/>please click on the activation link that was sent to your E-mail to activate your account<br/>Thank you<br/>if you did not get your account activation E-mail, <a   href="/resend_confirmation_code?user='.$loginUsername.'&_rdr='.$GLOBAL_rdr.'" class="resend-code links"   data-user="'.$loginUsername.'"  >please click on this link to resend your activation code</a></span>';										
								$uvalue = $loginUsername;
								$pvalue = $loginPwd;	
																										
							}
							
						}else
							$notConfirmed = '<span class="alert alert-danger">An unexpected error has occurred<br/>We are sorry about this</span>';
							
						
					}else{	
					
						if($loginAttempts >= $allowedAttempts){	
									
							$alert = '<span class="alert alert-danger"><h3>ACCOUNT TEMPORARILY LOCKED</h3>We detected some suspicious activities with this account. Please follow the link sent to your registered E-mail to unlock your account.<br/>Thank You<br/> If you did\'nt get your unlock code please <a href="/resend_confirmation_code?_ulp='.$ENGINE->generate_token().'&unlock=true&_ulu='.$loginUsername.'&_rdr='.$GLOBAL_rdr.'"  class="resend-code links"   data-ulu="'.$loginUsername.'" >click here to resend</a></span> ';
									
							if(!$unlockLogin){
									
								$codeEmailed = $ENGINE->generate_token();
								$prank_codeEmailed = $ENGINE->generate_token();
																	
								//LOG LOGIN UNLOCK AUTHENTICATION CODE//
								$done = $SITE->logAuthentication($userId, AUTH_CODE_KEY_UNLOCK_LOGIN, $codeEmailed);
																			
								if($ACCOUNT->updateUser($userId, $cols = "UNLOCK_LOGIN=1") && $done)
									$SITE->mailByTemplate(array('template'=>'confirm-login-unlock', 'to'=>$email.'::'.$firstName, 'code'=>$codeEmailed, 'decoyCode'=>$prank_codeEmailed, 'username'=>$loginUsername));
									
								else
									$alert = '<span class="alert alert-danger">Sorry the system encountered an error!</a>.<br/>Please try again.</span>';
									
							}
									
							////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
							$SITE->formGateRefresh($alert);
							
						}elseif((strtolower($loginUsername) == strtolower($username)) && $loginPwd != $userPwd){
									
							$incorrectPwd= "*";
							$uvalue = $loginUsername;
								
							$ACCOUNT->updateUser($userId, $cols = "LOGIN_ATTEMPT = (LOGIN_ATTEMPT + 1)");
							$pwdFieldErr = $fErr;
							 
							 echo '<script>location.assign("'.$encodedRdrQstr.'#loginPwd")</script>';					
								
						}else{
							 
							$incorrectUsername = "*";
							$usernameFieldErr = $fErr;
							$pvalue = $loginPwd;
							
							echo '<script>location.assign("'.$encodedRdrQstr.'#loginUsername")</script>';
							
						}

					}

					
				}else{						
													
					$alertUser = '<span class="alert alert-danger">Incorrect Login Details </span> ';
					$pvalue = $loginPwd;
					$uvalue = $loginUsername;						
																			
				}
					

			}else{					
															
				if(!$loginUsername && !$loginPwd){					
									
					$usernameFieldErr = $pwdFieldErr = $fErr;						
					$nullFields = $asterix;
					echo '<script>location.assign("'.$encodedRdrQstr.'#loginUsername")</script>';
				}
				
				if($loginUsername && !$loginPwd){
					
					$pwdFieldErr = $fErr;
					$uvalue = $loginUsername;
					$nullPwd = $asterix;
					echo '<script>location.assign("'.$encodedRdrQstr.'#loginPwd")</script>';					
									
				}
				
				if(!$loginUsername && $loginPwd){
					
					$usernameFieldErr = $fErr;
					$pvalue = $loginPwd;
					$nullUsername = $asterix;
					echo '<script>location.assign("'.$encodedRdrQstr.'#loginUsername")</script>';
					
				}	
					
				
			}
			
		}

		$pwd2TextCls = 'login-pwd-plain';

		$stayLoggedTips = 'For security reasons, we highly recommend that you do not check this box on public devices';
		$showPwdTips = 'For security reasons, please make sure there are no prying eyes before you check this box';
		$tipMetas = ' data-field-tip="true" data-tip-w="300" data-tip-xoffset="5" ';
		$stayLoggedTipId = 'show-stay-logged-tips';
		$showPwdTipId = 'show-pwd-tips';

		$SITE->buildPageHtml(array("pageTitle"=>'Login',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/login" title="login to your account">Login</a></li>'),
					"pageBody"=>'
						<div class="single-base blend">
							<div class="base-ctrl base-rad">
								<h1 class="page-title pan bg-mine-1">'.$FA_lock.' Login</h1>
								<div class="base-container base-t-pad">
									'.$banAlert.(isset($_GET["code-resend"])? $ENGINE->get_global_var('ss', "SESS_ALERT") : '').'
									<div>'.
										(isset($notConfirmed)? $notConfirmed : '').(isset($alertUser)? $alertUser : '').'												
									</div>
									<div id="ajax-res" class="text-danger">'.($rdr? ''/*'Please Login First'*/ : '').'</div>
									<div class="form-ui form-ui-basic">
										<form class="inline-form" name="login" method="post" action="/login'.$encodedRdrQstr.'">											
											<div class="red">'.
												(!isset($_COOKIE)? '<span class="alert alert-danger">Please enable cookies on your device!</span>' : '').
												($nullFields? '<span class="alert alert-danger">Please enter your username and password!</span>' : '').
												($nullPwd? '<span class="alert alert-danger">Please enter your password!</span>' : '').
												($nullUsername? '<span class="alert alert-danger">Please enter your username!</span>' : ''). 
												($incorrectUsername? '<span class="alert alert-danger">Incorrect username!</span>' : '').  
												($incorrectPwd? '<span class="alert alert-danger">Incorrect password!</span>' : '').
												(isset($LGP_VER)? '<span class="alert alert-warning"><b>Authenticating Session Please wait '.$spinner.'</b><br/>If you\'re not redirected shortly please <a href="'.$rdrAlt.'" class="links">click here</a>.</span>' : '').'
											</div>'.(isset($LGP_VER)? '' : '
											<br/>
											<div class="field-ctrl">
												<label for="loginUsername">USERNAME<span class="red">'.(isset($nullFields)? $nullFields : '').(isset($nullUsername)? $nullUsername : '').(isset($incorrectUsername)? $incorrectUsername : '').'</span>:</label>
												<input autocomplete="off" class="field '.$usernameFieldErr.'" id="loginUsername" value="'.(isset($uvalue)? $uvalue : '').'" type="text" name="loginUsername" placeholder="username" />
											</div>
											<div class="field-ctrl">
												<label for="loginPwd">PASSWORD<span class="red">'.(isset($nullFields)? $nullFields : '').(isset($incorrectPwd)? $incorrectPwd : '').(isset($nullPwd)? $nullPwd : '').'</span>:</label>
												<input autocomplete="off" id="loginPwd" class="field '.$pwd2TextCls.' '.$pwdFieldErr.'"  value="'.(isset($pvalue)? $pvalue : '').'" type="password" name="loginPwd" placeholder="password" />
											</div>
											<br class="visible-xs" />
											<div class="field-ctrl btn-ctrl col-xs-w-5">
												<button class="form-btn" name="login">'.$FA_lock.' Login</button>
											</div>
											<p>
												<div class="field-ctrl align-c">
													'.$SITE->getHtmlComponent('iconic-checkbox', array('label'=>'Stay logged in', 'title'=>$stayLoggedTips, 'wrapData' => $tipMetas.'data-tip-loader="'.$stayLoggedTipId.'"',  'wrapClass'=>'text-warning', 'fieldName'=>$K='stayLoggedIn', 'on'=>isset($_POST[$K]))).'
													<span class="hide" id="'.$stayLoggedTipId.'">'.$stayLoggedTips.'</span>
												</div>
												<div class="field-ctrl align-c">
													'.$SITE->getHtmlComponent('iconic-checkbox', array('label'=>'Show password', 'title'=>$showPwdTips, 'wrapData' => $tipMetas.'data-tip-loader="'.$showPwdTipId.'"', 'fieldData'=>'data-toggle-password-plain-target="'.$pwd2TextCls.'"', 'wrapClass'=>'text-info', 'fieldName'=>$K='showpassword', 'on'=>isset($_POST[$K]))).'
													<span class="hide" id="'.$showPwdTipId.'">'.$showPwdTips.'</span>
												</div><br/>
												<div class="field-ctrl align-c no-hover-bg">
													<a  href="/forgotpassword" class="red links">Forgot your password?</a><br/>
													<a class="links sky-blue" href="/signup"><span class="text-info">Not registered yet?</span> sign up now &raquo;</a>
												</div>
											</p>').'
										</form>
									</div>
								</div>		
							</div>
						</div>'.''
												
						
		));

		break;					
									
	}
	
	
	
	
	
	/**LOGOUT**/
	case 'logout':{					
									
		$ACCOUNT->SESS->destroy();			

		if(isset($_GET[$K="spam_user"]))	
			$ENGINE->set_cookie("spam_banned", $ENGINE->sanitize_user_input($_GET[$K]));	
		
		header('Location:'.$rdrAlt);					
		exit();					
									
	}
	
	
	/**LOGOUT FROM ALL DEVICES**/
	case 'logout-all':{					
									
		$ACCOUNT->SESS->destroy(array("logoutAll" => true));			
				
									
	}
	
	

	
	
	
	
	
	/**DO FOLLOW MEMBERS**/
	case 'members-follows':{
		
		$taskAction="";

		if($sessUsername){
			
			if(!isset($_POST[$user="user"])){
				

				/***************************BEGIN URL CONTROLLER****************************/

				if(isset($pagePathArr[1]) && (strtolower($pagePathArr[1]) == "follow" || strtolower($pagePathArr[1]) == "unfollow")){
		
					$pathKeysArr = array('pageUrl', "taskAction", $user);
					$maxPath = 4;	
				
				}else{
				
					$pathKeysArr = array();
					$maxPath = 0;
				
				}

				$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

				/*******************************END URL CONTROLLER***************************/

			}
			

			if(isset($_POST[$user]) ||  isset($_GET[$user])){
				
				if(isset($_POST[$user]))	
					$user = $ENGINE->sanitize_user_input($_POST[$user]);
				
				if(isset($_GET[$user]))	
					$user = $ENGINE->sanitize_user_input($_GET[$user]);
				
				if(isset($_POST[$K="taskAction"]))
					$taskAction = $ENGINE->sanitize_user_input($_POST[$K]);
				
				if(isset($_GET[$K]))
					$taskAction = $ENGINE->sanitize_user_input($_GET[$K]);
									
				$taskAction = strtolower($taskAction);
				
				///AUTHENTICATE///
				if($taskAction && ($memberId = $ACCOUNT->memberIdToggle($user, true)) && $memberId != $sessUid){
					
					///FOLLOW/UNFOLLOW THE MEMBER ////////
					$FORUM->followedMembersHandler(array('uid'=>$memberId, 'follower'=>$sessUid, 'action'=>$taskAction));							
				}	
			}
				

		}
								
			
		if(!$GLOBAL_isAjax){
			
			header("Location:".$rdrAlt);
			exit();				
			
		}

		break;				
			
	}
		
	
	
	
	
	/**EDIT OR MODIFY POST FILES**/
	case 'modify-post-file':{

		if((isset($_POST[$post="post"]) || isset($_GET[$post])) && (isset($_POST[$file="file"]) ||  isset($_GET[$file]))){
					
			if(isset($_POST[$file]))
				$filePassed = $_POST[$file];

			if(isset($_GET[$file]))
				$filePassed = $_GET[$file];

			if(isset($_POST[$post]))
				$postId = $_POST[$post];
				
			if(isset($_GET[$post]))
				$postId = $_GET[$post];
			
			$specialPriv = $ACCOUNT->sessionAccess(array('id'=>$FORUM->getPostDetail($postId, 'TOPIC_ID')));

			//VERIFY ACCESS TO EDIT THE POST FILE					
			if($FORUM->getPostDetail($postId, 'POST_AUTHOR_ID') == $sessUid || $specialPriv){										
				
				////DELETE THE FILE FROM SERVER///////																																				
				$FORUM->postedFilesHandler(array('pid'=>$postId, 'files'=>$filePassed, 'del'=>true));
											
			}

		}

		if(!$GLOBAL_isAjax){

			header("Location:".$rdrAlt);
			exit();		
		}
	
		break;

	}
	
	
	
	
	
	/**DO MULTIQUOTE**/
	case 'multi-quote':{		

		if(isset($_POST[$post="post"]) || isset($_GET[$post])){
				
			if(isset($_POST[$post]))
				$postId = $_POST[$post];
			
			if(isset($_GET[$post]))
				$postId = $_GET[$post];
			
			$postId = $ENGINE->sanitize_number($postId);
			
			if($postId){
				
				if($FORUM->highlightedForMultiQuote($sessUid, $postId)){
					
					///////////PDO QUERY///////
				
					$sql = "DELETE FROM mq_trackers WHERE USER_ID=? AND POST_ID=? LIMIT 1";
					$valArr = array($sessUid, $postId);
					$stmt = $dbm->doSecuredQuery($sql, $valArr);
			
				}else{
			
					$sql = "INSERT INTO mq_trackers (USER_ID, POST_ID) VALUES(?,?)";
					$valArr = array($sessUid, $postId);
					$stmt = $dbm->doSecuredQuery($sql, $valArr);
			
				}
			
			}	
			
		}
			
		if(!$GLOBAL_isAjax){
			
			header("Location:".$rdrAlt);
			exit();
			
		}
			
		break;
			
	}
	
	
	
	
	
	/**DO POST LIKE**/
	case 'vote-answer':{
			
		$rdrPageId=$taskAction="";

		if($sessUsername){
			
			if(!$GLOBAL_isAjax){
				

				/***************************BEGIN URL CONTROLLER****************************/
				
				if(isset($pagePathArr[1]) && (strtolower($pagePathArr[1]) == "upvote" ||  strtolower($pagePathArr[1]) == "downvote")){
			
					$pathKeysArr = array('pageUrl', "taskAction", "postId");
					$maxPath = 3;	
			
				}else{
				
					$pathKeysArr = array();
					$maxPath = 0;
				
				}

				$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

				/*******************************END URL CONTROLLER***************************/


			}


			if(isset($_POST[$post='postId']) ||  isset($_GET[$post])){
				
				if(isset($_POST[$post]))
					$postId = $_POST[$post];

				if(isset($_GET[$post]))
					$postId = $_GET[$post];

				$postId = $ENGINE->sanitize_number($postId);
				
				if(isset($_POST[$K="taskAction"]))
					$taskAction = $_POST[$K];

				if(isset($_GET[$K]))
					$taskAction = $_GET[$K];

				if(isset($_GET[$K="rdrPageId"]))
					$rdrPageId = $ENGINE->sanitize_number($_GET[$K]);


				/////////GET LIKE DETAILS VIA THE PASSED POST ID /////


				///////////PDO QUERY/////////
				
				$sql =  "SELECT * FROM posts WHERE ID = ? LIMIT 1";
				$valArr = array($postId);
				$stmt = $dbm->doSecuredQuery($sql, $valArr);
				$row = $dbm->fetchRow($stmt);	
			
				if(!empty($row)){
								
					$topicId = $row["TOPIC_ID"];

					$statArr = $FORUM->getPostStatus($postId);
					$isLockedPost = $statArr["isLocked"];
					$isHiddenPost = $statArr["isHidden"];								
					
					list($postAuthorized, $viewAuthorized, $protectionAlert) = $FORUM->authorizeThreadAccess($topicId);
					
					if($postAuthorized && $viewAuthorized && ((!$isLockedPost && !$isHiddenPost) || $GLOBAL_isStaff)){
						
						$postNum = $FORUM->getPostNumber($postId, $topicId);
						$postAuthorId = $row["POST_AUTHOR_ID"];
						$rdrPageId = $FORUM->getPostPageNumber($postNum);

						if(isset($_GET[$post]))
							$rdrAlt = $SITE->getThreadSlug($topicId).$rdrPageId.'#'.$postNum;							
						
						#####MAKE SURE USERS ARE NOT ALLOWED TO VOTE ON THEIR OWN POST#######
						if($sessUid != $postAuthorId){
										
							$taskAction = strtolower($taskAction);
							
							//////////////////HANDLE FOR DOWNERS //////
							
							///GET AUTHORIZATIONS///
							$modsAuth = $FORUM->authorizeModeration();
							$voteDownAllowed = $modsAuth["voteDown"];
							$voteUpAllowed = $modsAuth["voteUp"];
							$done = true;

							try{
								
								// Run vote casting transaction
								$dbm->beginTransaction();
								
								if($taskAction == "downvote" && $voteDownAllowed){								
									
									//FIRST REMOVE USER FROM UPVOTERS IF HE ALREADY UPPED
									$FORUM->votesHandler(array('uid'=>$sessUid,'pid'=>$postId,'type'=>'u','action'=>'revoke','voter'=>true));

									//HANDLE DOWNVOTE REVOKE
									if($FORUM->votesHandler(array('uid'=>$sessUid,'pid'=>$postId,'type'=>'d','action'=>'check','voter'=>true)))
										$FORUM->votesHandler(array('uid'=>$sessUid,'pid'=>$postId,'type'=>'d','action'=>'revoke','postAuthorId'=>$postAuthorId,'voter'=>true));

									//HANDLE DOWNVOTE CAST
									else
										$FORUM->votesHandler(array('uid'=>$sessUid,'pid'=>$postId,'type'=>'d','action'=>'cast','postAuthorId'=>$postAuthorId,'voter'=>true));
															
								}
								///////HANDLE FOR UPPERS//////////		
								elseif($taskAction == "upvote" && $voteUpAllowed){
									
									//FIRST REMOVE USER FROM DOWNVOTERS IF HE ALREADY DOWNED
									$FORUM->votesHandler(array('uid'=>$sessUid,'pid'=>$postId,'type'=>'d','action'=>'revoke','voter'=>true));

									//HANDLE UPVOTE REVOKE
									if($FORUM->votesHandler(array('uid'=>$sessUid,'pid'=>$postId,'type'=>'u','action'=>'check','voter'=>true)))
										$FORUM->votesHandler(array('uid'=>$sessUid,'pid'=>$postId,'type'=>'u','action'=>'revoke','postAuthorId'=>$postAuthorId,'voter'=>true));

									//HANDLE UPVOTE CAST
									else
										$FORUM->votesHandler(array('uid'=>$sessUid,'pid'=>$postId,'type'=>'u','action'=>'cast','postAuthorId'=>$postAuthorId,'voter'=>true));
									
								}
								
								// If we arrived here then our vote casting transaction was a success, we simply end the transaction
								$dbm->endTransaction();
								
								/////GET NEW TOTAL VOTES/////																								
								//$newCounts = ($FORUM->votesHandler(array('type'=>'u','pid'=>$postId)) - $FORUM->votesHandler(array('type'=>'d','pid'=>$postId)));									
								//echo $ENGINE->format_number($newCounts);
								
							}catch(Throwable $e){
								
								// Rollback if vote casting transaction fails
								$dbm->cancelTransaction();
								
							}
							
						}
						
					}
				}

			}										

		}	
			
		//////REDIRECT FOR NON JAVSCRIPT DEVICES///////
		if(!$GLOBAL_isAjax){
		
			header("Location:".$rdrAlt);					
			exit();	
		
		}	

		break;
		
	}
		
	
	
	
	
	/**DO POST SHARE**/
	case 'post-shares':{
		
		$rdrPageId=$taskAction=$shares="";

		if($sessUsername){
			
			if(!$GLOBAL_isAjax){
				
				/***************************BEGIN URL CONTROLLER****************************/

				if(isset($pagePathArr[1]) && (strtolower($pagePathArr[1]) == "share" ||  strtolower($pagePathArr[1]) == "unshare")){
		
					$pathKeysArr = array('pageUrl', 'taskAction', 'postId');
					$maxPath = 3;	
		
				}else{
				
					$pathKeysArr = array();
					$maxPath = 0;
				
				}

				$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

				/*******************************END URL CONTROLLER***************************/

			}

			if(isset($_POST[$post="postId"])  || isset($_GET[$post])){
				
				if(isset($_POST[$post]))
					$postId = $ENGINE->sanitize_number($_POST[$post]);

				if(isset($_GET[$post]))
					$postId = $ENGINE->sanitize_number($_GET[$post]);
						
				if(isset($_POST[$K="taskAction"]))
					$taskAction = $_POST[$K];

				if(isset($_GET[$K]))
					$taskAction = $_GET[$K];

				if(isset($_GET[$K="rdrPageId"]))
					$rdrPageId = $ENGINE->sanitize_number($_GET[$K]);


				/////////GET LIKE DETAILS VIA THE PASSED POST ID ///////


				///////////PDO QUERY//////

				$sql =  "SELECT * FROM posts WHERE ID = ? LIMIT 1";
				$valArr = array($postId);
				$stmt = $dbm->doSecuredQuery($sql, $valArr);
				$row = $dbm->fetchRow($stmt);			
							
				if(!empty($row)){
								
					$topicId = $row["TOPIC_ID"];	
										
					$statArr = $FORUM->getPostStatus($postId);
					$isLockedPost = $statArr["isLocked"];
					$isHiddenPost = $statArr["isHidden"];	
					
					list($postAuthorized, $viewAuthorized, $protectionAlert) = $FORUM->authorizeThreadAccess($topicId);
					
					if($postAuthorized && $viewAuthorized){
							
						$postNum = $FORUM->getPostNumber($postId, $topicId);
						$rdrPageId = $FORUM->getPostPageNumber($postNum);
						$postAuthorId = $row["POST_AUTHOR_ID"];
						
						if(isset($_GET[$post]))
							$rdrAlt = $SITE->getThreadSlug($topicId).$rdrPageId.'#'.$postNum;											
						
						if(!$isLockedPost || $ACCOUNT->sessionAccess(array('id'=>$topicId))){
							
							$taskAction = strtolower($taskAction);				
							
							//////////CALL SHARE HANDLER///////
							$FORUM->sharesHandler(array('uid'=>$sessUid,'pid'=>$postId,'action'=>$taskAction,'postAuthorId'=>$postAuthorId,'sharer'=>true));
							
							$shares = $FORUM->sharesHandler(array('pid'=>$postId));
							$shares = $ENGINE->format_number($shares).' Share'.(($shares > 1 )? 's ' : ' ');																		
							echo $shares;	
							
						}
						
					}	
				}	
				
			}
			
		}	
		
			
		/////REDIRECT FOR NON JAVSCRIPT DEVICES///
		if(!$GLOBAL_isAjax){
		
			header("Location:".$rdrAlt);					
			exit();
			
		}
			
		break;
			
	}
			
	
	
	
	/**REMOVE AD FROM SECTION**/
	case 'remove-ad-from-section':{
		
		$adsCampaign = new AdsCampaign();
		$adsCampaign->processSectionAdRemove();
		
		break;
			
	}
		
	
	
	
	
	/**REMOVE AVATAR**/
	case 'remove-file':{			

		if($sessUsername){

			if((isset($_POST[$file="file"]) || isset($_GET[$file])) && (isset($_POST[$tgt="tgt"]) || isset($_GET[$tgt]))){
						
				if(isset($_POST[$file]))
					$filePassed = $_POST[$file];

				if(isset($_GET[$file]))
					$filePassed = $_GET[$file];
					
				if(isset($_POST[$tgt]))
					$tgtPassed = $_POST[$tgt];

				if(isset($_GET[$tgt]))
					$tgtPassed = $_GET[$tgt];
					
					
				switch(strtolower($tgtPassed)){
					
					case 'avatar':				
						$cols = "AVATAR=''";
						$path2del = $mediaRootAvtXCL.$filePassed;
						$updateUser = true;
						break;
					
					case 'avatar_bg':				
						$cols = "AVATAR_BG=''";
						$path2del = $mediaRootAvtXCL.$filePassed;
						$updateUser = true;
						break;
					
					
				}
				
				////////UPDATE THE USER DB/////////		
				
				////DELETE THE FILE FROM SERVER///////
						
				
				if(realpath($path2del) && $filePassed){
					
					unlink($path2del);
					
					if(isset($updateUser))
						$ACCOUNT->updateUser($sessUsername, $cols);						
									
					
				}			
				
			}

		}

		header("Location:/edit-profile");
		exit();

		break;
			
	}
	
	
	
	
	
	/**RESEND CONFIRMATION CODES**/
	case 'resend_confirmation_code':{
		
		$rdrAlt .= '?code-resend';

		$authCode = $ENGINE->generate_token();

		if(isset($_POST[$K="user"]) || isset($_GET[$K])){

			if(isset($_POST[$K]))
				$username = $_POST[$K];

			if(isset($_GET[$K]))
				$username = $_GET[$K];
				
			
			$U = $ACCOUNT->loadUser($username);
			$email = $U->getEmail();
			
			if($email && $SITE->relogAuthentication($username, AUTH_CODE_KEY_ACTIVATE_USER, $authCode)
				&& $SITE->mailByTemplate(array('template'=>'confirm-account', 'to'=>$email.'::'.$U->getFirstName(), 'code'=>$authCode, 'username'=>$username)))
				$alert = '<span class="alert alert-success">'.$ACCOUNT->sanitizeUserSlug($username, array('anchor'=>true, 'youRef'=>false)).'<span> your activation link has been resent.<br/>Thank you.</span></span>';	

			else
				$alert = '<span class="alert alert-danger">'.$ACCOUNT->sanitizeUserSlug($username, array('anchor'=>true, 'youRef'=>false)).'<span> the system encountered an error!.<br/>Please try again.</span></span>';	
			

		}elseif(isset($_POST[$K="email"]) || isset($_GET[$K])){

			if(isset($_POST[$K]))
				$ajax = $email = $ENGINE->sanitize_user_input($_POST[$K]);

			if(isset($_GET[$K]))
				$email = $ENGINE->sanitize_user_input($_GET[$K]);

			$U = $ACCOUNT->loadUser($email);
			$email = $U->getEmail();
		
			if($email && $SITE->relogAuthentication($email, AUTH_CODE_KEY_CONFIRM_REG_EMAIL, $authCode)
				&& $SITE->mailByTemplate(array('template'=>'confirm-reg-email', 'to'=>$email, 'code'=>$authCode)))
				$alert = '<span class="alert alert-success"> your confirmation link has been resent to your email: <a href="mailto:'.$email.'">'.$email.'</a>.<br/>Thank you.</span>';	
				
			else
				$alert = '<span class="alert alert-danger">Sorry the system encountered an error!</a>.<br/>Please try again.</span>';
			
		}elseif(isset($_POST[$K="unlock"]) || isset($_GET[$K])){

			if(isset($_POST[$K="_ulu"]))
				$ajax = $loginUsername = $ENGINE->sanitize_user_input($_POST[$K]);

			if(isset($_GET[$K]))
				$loginUsername = $ENGINE->sanitize_user_input($_GET[$K]);	

			$U = $ACCOUNT->loadUser($loginUsername);
			$email = $U->getEmail();
			
			if($email && $SITE->relogAuthentication($email, AUTH_CODE_KEY_UNLOCK_LOGIN, $authCode) && ($prank_codeEmailed = $ENGINE->generate_token())
				&& $SITE->mailByTemplate(array('template'=>'confirm-login-unlock', 'to'=>$email.'::'.$U->getFirstName(), 'code'=>$authCode, 'decoyCode'=>$prank_codeEmailed, 'username'=>$loginUsername)))	
				$alert = '<span class="alert alert-success"> Your unlock code has been resent.</span>'; 
				
			else
				$alert = '<span class="alert alert-danger">'.$ACCOUNT->sanitizeUserSlug($loginUsername, array('anchor'=>true, 'youRef'=>false)).'<span> the system encountered an error!.<br/>Please try again.</span></span>';	
			
		}

		if(isset($alert)){
				
			echo $alert;
			$ENGINE->set_global_var('ss', 'SESS_ALERT', $alert);

		}

		if(!$GLOBAL_isAjax){
			
			header("Location:".$rdrAlt);							
			
		}

		exit();			
		
	}
	
	
	
	
	
	/**RESET CAMPAIGN BANK**/
	case 'reset-cubank':{
		
		$adsCampaign = new AdsCampaign();
		$adsCampaign->processCreditsUsedReset();
		
		break;
			
	}
	
	
	
	
	
	/**PAUSE/RESUME CAMPAIGN**/
	case 'rp-campaign':{
		
		$adsCampaign = new AdsCampaign();
		$adsCampaign->processPauseOrResume();
		
		break;
			
	}
	
	
	
	
	
	/**SECTION FOLLOWS**/
	case 'section-follows':{
					
		$sid=$taskAction="";	

		if($sessUsername){
			
			if(!$GLOBAL_isAjax){
				
				/***************************BEGIN URL CONTROLLER****************************/

				if(isset($pagePathArr[1]) && (strtolower($pagePathArr[1]) == "follow" || strtolower($pagePathArr[1]) == "unfollow")){
			
					$pathKeysArr = array('pageUrl', 'taskAction', 'sid');
					$maxPath = 4;	
			
				}else{
				
					$pathKeysArr = array();
					$maxPath = 0;
				
				}

				$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

				/*******************************END URL CONTROLLER***************************/

			}
			

			////IF SECTION FOLLOW IS SUBMITTED//////


			if(isset($_POST[$sid="sid"]) || isset($_GET[$sid])){		
						
				if(isset($_POST[$sid]))
					$sid = $_POST[$sid];	
						
				if(isset($_GET[$sid]))
					$sid = $_GET[$sid];	
						
				if(isset($_POST[$K="taskAction"]))
					$taskAction = $_POST[$K];	
						
				if(isset($_GET[$K]))
					$taskAction = $_GET[$K];					
					
				$taskAction = $ENGINE->sanitize_user_input($taskAction);
				
				$FORUM->followedSectionsHandler(array('uid'=>$sessUid, 'sid'=>$sid, 'action'=>$taskAction));
				
			}
			
			
		}
			
		if(!$GLOBAL_isAjax){
			
			header("Location:".$rdrAlt);
			exit();
			
		}

		break;
		
	}
	
	
	
	
	
	/**SET SEEN INFOS**/
	case 'set-si':{
		
		if(isset($_GET[$tknName="tkn_name"]) || isset($_POST[$tknName])){
				
			$tknName = $ENGINE->sanitize_user_input((isset($_POST[$tknName])? $_POST[$tknName] : (isset($_GET[$tknName])? $_GET[$tknName] : "")));
			$tknRmd = $ENGINE->sanitize_user_input((isset($_POST[$K="tkn_rmd"])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : "")));
			$tknKey = $ENGINE->sanitize_user_input((isset($_POST[$K="tkn_key"])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : "")));
			
			$tknName = strtolower($tknName);
			$tknRmd = strtolower($tknRmd);
			$uniqueId = $tknKey;
		
			////SET COOKIE TO EXPIRE AFTER 1 YEAR(365 * 1 = 365) OTHERWISE SET TO 1HR 
			$exp = (($tknRmd == '1')? 3600 : (86400 * 365));
			$action = ($tknName == "pops" && $tknKey)? $ENGINE->set_cookie($tknKey, $tknKey, $exp) : '';	
				
							
		}
		
		if(!$GLOBAL_isAjax){				
			
			header("Location:".$rdrAlt);
			exit();						
							
		}	
		
		break;
		
	}
	
	
	
	
	
	/**THREAD/POST SOCIAL SHARING**/
	case 'ss':{
					
		$location=$tid=$socialSiteName=$postId="";

		if(isset($_GET[$loc="loc"])){				
			
			if(!$sessUsername)
				$userId = 0;				
			else	
				$userId = $sessUid;	
				
			
			if(isset($_GET[$loc]))
				$loc = $ENGINE->sanitize_user_input($_GET[$loc], array('urlDecode' => true, 'lowercase' => true));
			
			if(isset($_GET[$K="ref_t"]))
				$tid = $ENGINE->sanitize_number($_GET[$K]);	
			
			if(isset($_GET[$K="ref_p"]))
				$postId = $ENGINE->sanitize_number($_GET[$K]);
			
			if(isset($_GET[$K="ref_ssn"]))
				$socialSiteName = $_GET[$K];
			
			if($loc && $tid){
						
				////////VERIFY TOPIC///
						
				/////PDO QUERY////
					
				$sql = "SELECT COUNT(*) FROM topics WHERE ID=? LIMIT 1"	;
				$valArr = array($tid);
				
				list($postAuthorized, $viewAuthorized, $protectionAlert) = $FORUM->authorizeThreadAccess($tid);
						
				if($dbm->doSecuredQuery($sql, $valArr)->fetchColumn() && $postAuthorized && $viewAuthorized){
										
					$topicStatArr = $FORUM->getThreadStatus($tid);										
					$topicRecycled = $topicStatArr["isRecycled"];					
					$topicClosed = $topicStatArr["tClosed"];

					$threadIsActive = (!$topicClosed && !$topicRecycled);	
			
					if($threadIsActive){
										
						////////GET USER IP////////
						
						$ip = $ENGINE->get_ip();
										
						///////////PDO QUERY////
								
						$sql = $userId? "SELECT COUNT(*) FROM topic_social_shares WHERE TOPIC_ID = ? AND USER_ID = ? LIMIT 1"
								: "SELECT COUNT(*) FROM topic_social_shares WHERE TOPIC_ID = ? AND IP = ? LIMIT 1";
						
						$valArr = $userId? array($tid, $userId) : array($tid, $ip);
									
						if(!$dbm->doSecuredQuery($sql, $valArr)->fetchColumn()){								
									
							///////////PDO QUERY////
								
							$sql =  "INSERT INTO topic_social_shares (USER_ID, TOPIC_ID, TIME, SOCIAL_SITE, IP) VALUES(?,?,NOW(),?,?)";
							$valArr = array($userId, $tid, $socialSiteName, $ip);
							$stmt = $dbm->doSecuredQuery($sql, $valArr);																		
									
						}			
						
						if($postId){
							///////////PDO QUERY////
									
							$sql =  "SELECT COUNT(*) FROM posts WHERE ID = ? AND TOPIC_ID = ? LIMIT 1";
							$valArr = array($postId, $tid);
							
							/////MAKE SURE THE POST IS NOT LOCKED////
							$statArr = $FORUM->getPostStatus($postId);
							$isLockedPost = $statArr["isLocked"];
							$isHiddenPost = $statArr["isHidden"];													
							
							if($dbm->doSecuredQuery($sql, $valArr)->fetchColumn() && ((!$isLockedPost && !$isHiddenPost)  || $GLOBAL_isAdmin)){
								
								///////////PDO QUERY////
										
								$sql =  $userId? "SELECT COUNT(*) FROM post_social_shares WHERE POST_ID = ? AND USER_ID = ? LIMIT 1"
										: "SELECT COUNT(*) FROM post_social_shares WHERE POST_ID = ? AND IP = ? LIMIT 1";
								
								$valArr = $userId? array($postId, $userId) : array($postId, $ip);
								$stmt = $dbm->doSecuredQuery($sql, $valArr);
											
								if(!$dbm->doSecuredQuery($sql, $valArr)->fetchColumn()){
																			
									///////////PDO QUERY////
										
									$sql =  "INSERT INTO post_social_shares (USER_ID, POST_ID, TIME, SOCIAL_SITE, IP) VALUES(?,?,NOW(),?,?)"	;
									$valArr = array($userId, $postId, $socialSiteName, $ip);
									$stmt = $dbm->doSecuredQuery($sql, $valArr);	
											
								}
								
							}
						}
					}		
			
					header("Location:".$loc);
					exit();
						
				}else{	
			
					header("Location:".$loc);
					exit();	
			
				}
						
			}else{
					
				header("Location:".$rdrAlt);
				exit();		
			
			}	
				
			
		}else{
		
			header("Location:".$rdrAlt);
			exit();
		
		}

		break;
		
	}
	
	
	
	
	
	/**TOPIC FOLLOWS**/
	case 'topic-follows':{

		$taskAction="";			

		if($sessUsername){
				
			if(!$GLOBAL_isAjax){
				
				/***************************BEGIN URL CONTROLLER****************************/
				
				if(isset($pagePathArr[1]) && (strtolower($pagePathArr[1]) == "follow" || strtolower($pagePathArr[1]) == "unfollow")){
				
					$pathKeysArr = array('pageUrl', 'taskAction', 'tid');
					$maxPath = 4;
					
				}else{
				
					$pathKeysArr = array();
					$maxPath = 0;
				
				}

				$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

				/*******************************END URL CONTROLLER***************************/

			}
			
			if(isset($_POST[$tid="tid"])  ||  isset($_GET[$tid]) ){

				if(isset($_POST[$tid]))	
					$tid = $_POST[$tid];

				elseif(isset($_GET[$tid]))	
					$tid = $_GET[$tid];
				
				if(isset($_POST[$K="taskAction"]))
					$taskAction = $_POST[$K];
				
				if(isset($_GET[$K]))
					$taskAction = $_GET[$K];
				
				if(strtolower($tid) != "all")
					$tid = $ENGINE->sanitize_number($tid);

				$taskAction = $ENGINE->sanitize_user_input($taskAction);

				$FORUM->requestTopicFollow($tid, $taskAction);
				
			}
			
		}
		
			
		if(!$GLOBAL_isAjax){
			
			header("Location:".$rdrAlt);
			exit();
			
		}

		break;
		
	}
	
	
			
	
	
	
	
	
	/**PLUS 18 ENTER WARNING**/
	case 'plus18':{
		
		$adultVdsToken =  $_SESSION["adult_vds_accepted"] = $ENGINE->generate_token();
		$adultVdsTokenId =  isset($_GET[$K="_tkid"])? $_GET[$K] : '';			
		$httpReferer =  ($K=$ENGINE->get_global_var('sv', "HTTP_REFERER"))? $K : '/';
					
		$SITE->buildPageHtml(array("pageTitle"=>'Plus18', 
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'"><b class="red">18+</b></a></li>'),
					"pageBody"=>'
					<div class="single-base blend-top">	
							<div class="base-ctrl">				
								<h1 class="page-title pan"><span class="red">'.$GLOBAL_siteName.'\'s Adult Enter</span></h1>
								<div class="align-l centered-inline">
									<div class="base-container">
										<p>You are about to enter into a section of this community meant for adults only</p>
										<p> - I am an adult being at least 18 years old</p>
										<p> - I warrant that it is legal to view adult contents in my country and locale</p>												
										<h3 class="align-c no-hover-bg">
											'.$ENGINE->sanitize_slug(($rdr? $rdr : 'adults'), array('ret'=>'url', 'slugSanitized'=>($rdr? true : false), 'urlQstr'=>array('adult_vds_accepted'=>$adultVdsToken, 'adult_vds_accepted_tkid'=>$adultVdsTokenId), 'urlText'=>'I agree take me in', 'cssClass'=>'red')).'
											&nbsp;&nbsp;&nbsp;
											<a class="links text-success" href="'.$httpReferer.'">I do not agree</a>
										</h3>
									</div>
								</div>
							</div>
						</div>'
		));		
									
		break;
		
	}
	
	
	
	
	
	/**FAQ**/
	case 'faq':{
		
		$alertUser=$notLogged=$nullFields="";
		
$sampleCode = '
[cd=php]
//This is a PHP code highlighted using PHP keyword

class{
private $a;
private static $b;

public function __construct($b){
	$this->b = $b;
}

public function base_multiply($a){
	return ($a * $this->b);
}
}[/cd]';

		//////AWARD analyst///////////
		$badgesAndReputations->badgeAwardFly($sessUid, 'analyst');

		$staffSeal = '<img '.STAFF_SEAL.' title="Staff Seal" />';
		$trustSeal = '<img '.TRUSTED_SEAL.' title="Trusted User Seal" />';		
		
		$SITE->buildPageHtml(array("pageTitle"=>'FAQ',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/faq" title="Frequently Asked Questions" >Faq</a></li>'),
					"pageBody"=>'
					<div class="single-base">		
						<div class="base-ctrl">							
							<h1 class="prime" title="Frequently Asked Questions">FAQ</h1>
							<div class="base-container">										
								'.$nullFields.$alertUser.$notLogged.' 										 		
								<div class="faq align-l">			
									<ul class="ol accordions classic apart curve" data-animate="fadeIn">								
										<a class="accordion" >How do i run ad campaign on this community</a>
										<div class="accordion-panel" >
											'.$SITE->file_get_contents($siteDomain.'/ads-campaign?hta=hta').'
										</div>					
										<a class="accordion" >'.($K='What\'s Multi-Quote and How do i multi-quote a post').'</a>
										<div class="accordion-panel" >
											<h4>'.$K.'?</h4>			
											<p>					
												The multiquote option give users the ability and ease to quote multiple post at a go without having to do it individually.
												To multiquote a post simply, highlight all the post you wish to multiquote by <b class="text-danger"> checking the small box beside the multiquote button
												that corresponds to that post</b>. Once you have done this for all the post you wish to multiquote, then <b class="text-danger">simply click 
												any of the multiquote button on that page irrespective of whether you are multiquoting the post corresponding to the button or not.</b>
												<div class="alert alert-warning">
													NOTE: <br/>
													<i class="green">
														Cross-thread multi-quoting is allowed which means that you can multi-quote posts from other threads on the fly.<br/>
														As you browse through a thread click on the checkbox beside the post you may like to multi-quote in another thread later on.<br/>
														Ensure the cross-thread multi-quote feature is activated on your profile otherwise <a class="links" href="/edit-profile">edit your profile</a> accordingly.
													</i>
													<p>All posts highlighted for multi-quote will automatically be unchecked after making a new post</p>
												</div>
											</p>
										</div>					
										<a class="accordion" >'.($K='Who is a moderator and how can i become one').'</a>
										<div class="accordion-panel" >					
											<h4>'.$K.'?</h4>
											<p>								
												At '.$siteName.', we believe moderation starts with the community 
												itself, so in addition to all users gaining privileges through reputation earned, 
												the site has moderators elected through popular vote.<br/> 
												We hold regular elections to determine who becomes a moderator in the community
												(except in the case of new beta sections, which have moderators pro tem(appointed 
												by '.$siteName.').
												Moderators are elected for life, though they may resign (or, in very rare cases, be removed).
											</p>
											<p>
												We generally expect that moderators:<br/>
												<b class="'.($hasCbullet='has-circular-bullet').'">lead by example</b><br/>
												<b class="'.$hasCbullet.'">show respect for their fellow community members in their actions and words</b><br/>
												<b class="'.$hasCbullet.'">are patient and fair</b><br/>																													
												<b class="'.$hasCbullet.'">are open to some light but firm moderation to keep the community on track and resolve (hopefully) 
													uncommon disputes and exceptions
												</b><br/>														
												<b class="'.$hasCbullet.'">Furthermore, all moderators must abide by our <a class="links" href="/policies/mods-agreement">moderator agreement</a>.</b><br/>														
																									
											</p>
											<p>
												<h4>Who are the community moderators and what is their role here?</h4>
												Moderators are human exception handlers put in place to deal with those exceptional conditions that could 
												otherwise disrupt the community.<br/>
												The most common moderator task is to follow up on flagged posts. Every post contains a flag link, 
												which anyone with '.FLAG_POST_REP.' reputation points can use. Posts can be flagged as '.implode(', ', REPORT_FLAGS_ARR).' and with an explanatory comment where necessary. 
												Once flagged, a post increments a flag count that shows up in the post.<br/>
												<b>If you see posts or anything in the system that is evil, weird, or in any way exceptional and deserving of moderator 
												attention for any reason…flag it!</b> 
												<br/>That’s the primary job of a moderator: to look at every flagged post, and take action if necessary.
												Moderators also have some special abilities necessary to handle those rare exceptional conditions:								
												<br/><b class="'.$hasCbullet.'">Moderators can lock posts. Locked posts cannot be voted on or changed in any way.</b><br/>
												<b class="'.$hasCbullet.'">Moderators can protect threads. Protected threads only allow users with the minimum required reputation to participate.</b><br/>
												<b class="'.$hasCbullet.'">Moderators can see more datas and also have more control access in the system.</b><br/>
												<b class="'.$hasCbullet.'">Moderators can place users in timed suspension, and request that user be deleted if necessary.</b><br/>
											</p>
											<p>
												A lot of the moderation work is mundane: deleting obvious spam, closing blatantly off-topic threads, and culling some of the worst-rated posts on the site.
												The ideal moderator does as little as possible, but those little actions may be powerful, visible, and highly concentrated.<br/>
												If you have questions about the reasoning behind a moderator\'s actions, bring them up for discussion by submitting a <a href="/feedback" class="links">support</a> ticket.
												Remember to be constructive and polite; moderators have the best interest of the community in mind, but they may occasionally make mistakes or have to deal with controversial 
												issues on which not everyone agrees.
											</p>
											<p>
												Moderators act as a liaison between the community and '.$siteName.'.<br/>
												Community moderators can also escalate issues of moderation by <a href="/contact-us" class="links">contacting</a> the '.$siteName.' team for guidance and administrative or technical tasks.<br/>
												Additionally, moderators can help draw extra attention to bugs, feature requests, or other issues that affect the community.<br/>
												Community moderators as well as administrators are distinguished from normal users by this medal icon '.$staffSeal.' usually displayed beside their names or signatures.
												<p class="bold"> More medal '.$staffSeal.' means more access.</p>							
												<div class="hr-dividers">
													<div>'.$staffSeal.'  - Basic Moderator.</div>								
													<div>'.$staffSeal.$staffSeal.'  - Super Moderator.</div>								
													<div>'.$staffSeal.$staffSeal.$trustSeal.' &nbsp; or &nbsp; '.$staffSeal.$trustSeal.' - Ultimate Moderator.</div>								
													<div>'.$staffSeal.$staffSeal.$staffSeal.'  - Administrator.</div>								
												</div>
											</p>
											<p>
												Becoming  a moderator on this community is quite easy and straight forward. First meet the required reputation points then read/agree to our <a class="links" href="/policies/mods-agreement">moderator agreement</a>.
												Thereafter indicate by sending us an email at '.CONTACT_MODS_EMAIL_ADDR.'
												The email <b>must</b> be sent from the email address used in registering your account with us and its content <b>must</b> include <b class="red">all</b> the following:
												<ol  class="ol">							
													<li>
														<div class="rarr"></div>
														Your Username
													</li>							
													<li>
														<div class="rarr"></div>									
														The section(s) you wish to moderate
													</li>
															
													<li>
														<div class="rarr"></div>
														Reason(s) why you think you should become a moderator in the section.
													</li>			
													<li>
														<div class="rarr"></div>
														Finally, sign your request email or pm by writing:
														<div data-has-clipboard-copy="true" data-clipboard-copy-btn-text="Copy Electronic Signing Format">
															I ______________________________________, with a clear and open mind wishes to become a moderator in the following section(s):____________________________________
															on the '.$siteName.' community and therefore accept all terms and conditions that binds the moderators team of this community.
														</div>
														<br/><i class="red">NOTE: the first _________ should be replaced with your full name (with username in bracket) while the second should be replaced with the
														sections you are requesting to moderate.</i>
														<div class="alert alert-info">
															Example:<br/>
															I Michael Philips (Miky), with a clear and open mind wishes to become a moderator in the following section(s): homepage, sports, softwares
															on the Kranook community and therefore accept all terms and conditions that binds the moderators team of this community.
														
														</div>
													</li>
													<p>
														All applications will be reviewed by '.$siteName.'. A voting system will be used to draft suitably qualified candidates.
														All successful candidates will be contacted and promoted to their new rank thereafter.
													</p>
												</ol>						
											</p>	
										</div>									
										<a class="accordion" >'.($K='What\'s a locked thread').'</a>
										<div class="accordion-panel" >					
											<h4>'.$K.'?</h4>
											<p>																
												A user can place a lock on a thread to prevent the thread from being :<br/> 								
												<b class="'.$hasCbullet.'">deleted</b>
												<br/><br/>								
												The following are the three(3) types of lock that can be placed on a thread:<br/>
												<b class="'.$hasCbullet.'">self lock</b> - Lock placed by the thread owner<br/>	
												<b class="'.$hasCbullet.'">moderator lock</b> - Lock placed by a moderator - <b class="prime">overrides self lock</b><br/>	
												<b class="'.$hasCbullet.'">administrator lock</b> - Lock placed by an administrator - <b class="prime">overrides self and moderator lock</b><br/>
												<div class="alert alert-success">NOTE: An insecured thread owner may request for an <b class="blue">ADMIN LOCK</b> by contacting the '.$siteName.' <a href="/contact-us" class="links">support</a> teams; thus ensuring that only an administrator may delete the thread.</div>
												Who can lock a thread?<br/>
												Administrators, moderators and reputable users.<br/>								
											</p>	
										</div>									
										<a class="accordion" >'.($K='What\'s a locked post').'</a>
										<div class="accordion-panel" >					
											<h4>'.$K.'?</h4>
											<p>																
												A post which is "locked" cannot be modified in any way.<br/> 
												Locking prevents:<br/>
												<b class="'.$hasCbullet.'">voting on the post</b><br/>
												<b class="'.$hasCbullet.'">editing</b><br/>								
												<b class="'.$hasCbullet.'">Flagging</b><br/>								
												<b class="'.$hasCbullet.'">quoting/multi-quote</b><br/>
												<b class="'.$hasCbullet.'">Sharing of the post</b><br/><br/>
												Who can lock a post?<br/>
												Administrators, moderators and reputable users.<br/>												
											</p>	
										</div>									
										<a class="accordion" >'.($K='How do i tag users in a post').'</a>
										<div class="accordion-panel" >		
											<h4>'.$K.'?</h4>
											<p>
												To tag a user in a post simply use the following syntax:
												<span class="alert alert-info">@username<br>Example: @Mark @Greg @Mary @John</span>
												<span class="alert alert-warning">NOTE: The username must be valid before the system can actually parse the tag</span>
											</p>
										</div>		
										<a class="accordion" >'.($K='How do i post language specific highlighted codes').'</a>
										<div class="accordion-panel" >					
											<h4>'.$K.'?</h4>
											<p>																
												On this <a href="/" class="links">community</a>, there are two(2) types of code that our system recognize and parse: 								
												<br/>									
												<b class="'.$hasCbullet.'">Inline codes</b> - delimited by <span class="blue">[cdi]...[/cdi]</span><br/>	
												<b class="'.$hasCbullet.'">Block codes</b> - delimited by <span class="blue">[cd=js]...[/cd]</span><br/>	
												
												
												<h3>INLINE CODES</h3>
												<div>
													Inline Codes are not highlighted by the system; to make them stand out, the system simply gives them a unique background.
													<br/><span class="bg-white-always inline-block base-pad">
														Example: '.$SITE->bbcHandler('', array('action' => 'decode', 'content' => '[cdi]This is an inline code[/cdi]')).'
													</span>
												</div>
												
												<h3>BLOCK CODES</h3>
												<div class="alert alert-warning">Noticed the "=js" in the block code delimeter above? 
													<div class="text-black-adapt">It\'s a keyword representing the language of the code. The system uses this language keyword to properly highlight the code.
														<br/> By default the keyword is preset to "=js" meaning JavaScript; thus all block codes by default will be highlighted as JavaScript.
														<br/>You can change this keyword by swapping the "js" with the keyword corresponding to your code language.
														<br/>
														<span class="red">
															NOTE: Our system currently may not support all languages and using a wrong keyword will mean that your block codes will not be properly highlighted according to your code language syntax.
															<br/> Use the look up table below to find the code keywords currently supported by our system.
														</span>
														<div>
															Example:'.$SITE->bbcHandler('', array('action' => 'decode', 'content' => $sampleCode)).' 
													
														</div>
													</div>
												</div>
												<div class="table-responsive">
													<h2 class="alert alert-danger align-c">NOTE: keywords are case sensitive</h2>
													<table class="">
														<tr><th>S/N</th><th>KEYWORDS</th><th>LANGUAGES</th></tr>
														<tr><td>1</td><td>bsh, bash, csh, sh</td><td>Bash and other Shell scripting</td></tr>
														<tr><td>2</td><td>c, cc, cpp, cxx, cyc, m</td><td>C, C++, ... etc</td></tr>
														<tr><td>3</td><td>cs</td><td>C# (C Sharp)</td></tr>
														<tr><td>4</td><td>clj</td><td>Clojure</td></tr>
														<tr><td>5</td><td>coffee</td><td>CoffeeScript</td></tr>
														<tr><td>6</td><td>css</td><td>CSS</td></tr>
														<tr><td>7</td><td>dart</td><td>Dart</td></tr>
														<tr><td>8</td><td>pascal</td><td>Delphi</td></tr>
														<tr><td>9</td><td>erl, erlang</td><td>Erlang</td></tr>
														<tr><td>10</td><td>go</td><td>Go</td></tr>
														<tr><td>11</td><td>hs</td><td>Haskell</td></tr>
														<tr><td>12</td><td>html</td><td>HTML</td></tr>
														<tr><td>13</td><td>java</td><td>Java</td></tr>
														<tr><td>14</td><td>js, javascript</td><td>JavaScript</td></tr>
														<tr><td>15</td><td>json</td><td>JSON</td></tr>
														<tr><td>16</td><td>latex, tex</td><td>LaTeX and TeX</td></tr>
														<tr><td>17</td><td>lsp, lisp, scm, ss, cl, el, rkt</td><td>Lisp and Scheme</td></tr>
														<tr><td>18</td><td>lua</td><td>Lua</td></tr>
														<tr><td>19</td><td>fs, ml</td><td>OCaml, SML, F#, ... etc</td></tr>
														<tr><td>20</td><td>pascal</td><td>Pascal</td></tr>
														<tr><td>21</td><td>pl, perl</td><td>Perl</td></tr>
														<tr><td>22</td><td>php</td><td>PHP</td></tr>
														<tr><td>23</td><td>proto</td><td>Protocol buffers</td></tr>
														<tr><td>24</td><td>py, python, cv</td><td>Python</td></tr>
														<tr><td>25</td><td>r, s</td><td>R and S</td></tr>
														<tr><td>26</td><td>regex</td><td>Regex</td></tr>
														<tr><td>27</td><td>rb, ruby</td><td>Ruby</td></tr>
														<tr><td>28</td><td>rs, rc, rust</td><td>Rust</td></tr>
														<tr><td>29</td><td>scala</td><td>Scala</td></tr>
														<tr><td>30</td><td>sql</td><td>SQL</td></tr>
														<tr><td>31</td><td>vhdl, vhd</td><td>VHDL</td></tr>
														<tr><td>32</td><td>vb, vbs</td><td>Visual Basic</td></tr>
														<tr><td>33</td><td>xml</td><td>XML</td></tr>
													</table>
												</div>							
											</p>	
										</div>									
									</ul>				
								</div>					
							</div>
						</div>
					</div>'
		));
				
		break;
				
	}
	
	
	
	
	/**YOUR PM BLACKLIST**/
	case 'pm-blacklist':{
				
		/***************************BEGIN URL CONTROLLER****************************/
		$path2 = isset($pagePathArr[1])?  strtolower($pagePathArr[1]) : '';					
		$path3 = isset($pagePathArr[2])?  $pagePathArr[2] : '';
		$expectedPaths_arr = array($add="add", "remove");

		if(isset($_POST[$K="blacklist_uid"])){

			$path2 = $add;
			$path3 = $_POST[$K];
			$blacklisting = true;

		}
				
		if(in_array($path2, $expectedPaths_arr)){

			$buid = $ACCOUNT->memberIdToggle($path3, true);				
			$pathKeysArr = array('pageUrl', 'action', 'buid');
			$metaArr = array('buid'=>$buid);
			$subNav = '<li><a href="/'.$pageSelf.'" title="">'.$path2.'</a></li>';
			$maxPath = 3;			
				
		}elseif(in_array($path2, array('clear'))){	
								
			$pathKeysArr = array('pageUrl', 'action');
			$subNav = '<li><a href="/'.$pageSelf.'" title="">'.$path2.'</a></li>';
			$maxPath = 2;
				
		}else{
				
			$pathKeysArr = array('pageUrl', 'pageId');
			$subNav = '';
			$maxPath = 2;	
				
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/
		
		$action = isset($blacklisting)? $add : (isset($_GET[$K="action"])? $_GET[$K] : '');
		
		if($action){
			
			$metaArr["action"] = $action;
			$pageTitle	= 'PM Blacklist - '.$action;
			
			$SITE->buildPageHtml(array("pageTitle"=>$pageTitle,
						"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/pm-blacklist" title="">pm blacklist</a></li>'.$subNav),
						"pageBody"=>'										
						<div class="single-base blend">
							<div class="base-ctrl">
								<div class="panel panel-limex">'.
									(!$sessUsername? $GLOBAL_notLogged : 
										'<h1 class="panel-head page-title">'.$pageTitle.'</h1>'.'
										<div class="panel-body sides-padless" >									
											'.$SITE->pmBlacklistHandler($metaArr).'	
										</div>'
									).'											
								</div>
							</div>
						</div>'
			));
				
			
		}else
			$SITE->pmBlacklistHandler(array());
				
		break;
				
	}
	
	
	
	/**SEND PM**/
	case 'pm':{
		
		///VARIABLE INITIALIZATION///////
		$readonly=$reply_to_user=$asterixAll=$subjectFieldErr=$userFieldErr=$navPos=
		$messageFieldErr=$receiver=$messageSubject=$message=$notLogged=$alertUser="";

		///////FORCE SESSION LEVEL ACCESS///////////
		$ACCOUNT->forceSessAccess();
		
		/***************************BEGIN URL CONTROLLER****************************/
				
		$path2 = isset($pagePathArr[1])?  strtolower($pagePathArr[1]) : '';					
		$path3 = isset($pagePathArr[2])?  $pagePathArr[2] : '';
									
		if($path2 && $SITE->isProfileSlug($path2)){	
											
			$pathKeysArr = array('pageUrl', 'receiver');
			$maxPath = 2;
			$captureUser = true;
			$navPos = '<li><a href="/'.$path2.'">'.$ENGINE->title_case($path2).'</a></li>';
				
		}elseif($path2 && $path2 == 'reply' && $path3){	
											
			$pathKeysArr = array('pageUrl', 'reply_url', 'reply');
			$maxPath = 3;
			$captureUser = true;	
				
		}else{
				
			$pathKeysArr = array();
			$maxPath = 1;
					
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		//////////GET FORM-GATE RESPONSE//////////	
		list($alertUser, $pmDispatchSucc)  = $SITE->formGateRefreshResponse(true);	
		$fielErr = 'field-error';

		$isBdWish = (bool)(isset($_GET[$K='bd_msg'])? $_GET[$K] : false);
		$bdWishPmSubj = BD_WISH_PM_SUBJECT;
		$bdWishPreBody = 'Happy Birthday! ';

		if(isset($_GET[$K="reply"]) ){				
			
			$replyId = $ENGINE->sanitize_number($_GET[$K]);
				
			////PDO QUERY//////
			
			$sql = "SELECT SENDER_ID, MESSAGE_SUBJECT FROM private_messages WHERE ID= ? AND USER_ID=? LIMIT 1";
			$valArr = array($replyId, $sessUid);
			$stmt = $dbm->doSecuredQuery($sql, $valArr);
			$row = $dbm->fetchRow($stmt);
			
			if(!empty($row)){
				
				$receiver = $ACCOUNT->memberIdToggle($row["SENDER_ID"]);
				
				$messageSubject = "RE: ".$row["MESSAGE_SUBJECT"];
				
				/////REMOVE LINKS FROM SUBJECT REPLIES///////
				$messageSubject = preg_replace("#\[a(.*)\](.*)\[/a\]#isU", "$2", $messageSubject);
				
				if(strpos($messageSubject, $bdWishPmSubj) !== false){
					
					$isBdWish = true;
					$bdWishPmSubj = $messageSubject;
					$bdWishPreBody = 'Thank You ';
					
					
				}
				
				$navPos = '<li><a href="/'.$pageSelf.'">Re: '.$receiver.'</a></li>';
				
			}
					
		}

		if(isset($_POST[$K="receiver"]))
			$receiver = $_POST[$K];
		
		elseif(isset($_GET[$K]))
			$receiver = $_GET[$K];

		if($receiver){
				
			$readonly = ($SITE->isProfileSlug($receiver) && isset($captureUser))? 'readonly="readonly"' : '';
			$receiver = $ENGINE->sanitize_user_input($receiver);
				
		}

		//////////ON SEND///////

		if(isset($_POST['send_message'])){

			if($sessUsername){
				
				$filterOptArr = array('preserveWhitespace' => 2);		
				$sender = $sessUsername;		
				$senderId = $sessUid;		
				$receiverId = $ACCOUNT->memberIdToggle($receiver, true);
				$message = $ENGINE->sanitize_user_input($_POST['composed_message'], $filterOptArr);
				$messageSubject = $ENGINE->sanitize_user_input($_POST['subject'], $filterOptArr);
			
				$receiverLC = strtolower($receiver);
				$senderLC = strtolower($sender);

				if($messageSubject && $message && $receiver){
					
					if($senderLC != $receiverLC){

						if($receiverId){
								
							$receiverUrl = $ACCOUNT->sanitizeUserSlug($receiver, array('anchor'=>true));
								
							if(!$SITE->pmBlacklistHandler(array('action'=>'check','uid'=>$receiverId,'buid'=>$senderId))){
				
								if($ACCOUNT->SESS->getDailyPmCounter() < DAILY_PM_LIMIT || $GLOBAL_isStaff){
									
									if($SITE->sendPm($senderId, $receiverId, $messageSubject, $message)){
				
										////TRACK DAILY PM SENT///						
										/////UPDATE DAILY PM COUNTER DPMC///////
										$ACCOUNT->updateUser($sender, 'DPMC = (DPMC + 1)');

										$alertUser = $pmDispatchSucc = '<span class="alert alert-success">your message has been sent to '.$receiverUrl.' <br/><a href="/inbox" class="links">Goto Inbox</a> </span>';
				
									}else
										$alertUser = '<span class="alert alert-danger">Ooops! something went wrong; your message was not delivered, please try again</span>';
				
									////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
									$SITE->formGateRefresh(array($alertUser, $pmDispatchSucc), '', '', false);
												
								}else
									$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', your PM could not be dispatched as You have exceeded your daily PM limit of '.DAILY_PM_LIMIT.'.<br/>Note that our PM sytem is not a chat platform and should be used judiciously. Please see our <a class="links" href="/policies/general-community-rules">general community rules</a> for more. </span>';
							}else
								$alertUser = '<span class="alert alert-danger">Sorry '.$GLOBAL_sessionUrl.' are not allowed to send a private message to '.$receiverUrl.'</span>';	
						}else
							$alertUser = '<span class="alert alert-danger">Sorry the user: <span class="blue">'.$receiver.'</span> was not found<br/> Your message was not sent </span>';	
						
					}else
						$alertUser = '<span class="alert alert-danger">sorry you cannot send message to yourself</span>';	
					
				}else{
					
					$alertUser = '<span class="alert alert-danger">Fields marked(*) are required</span>';	
					$asterixAll = '<span class="asterix">*</span>';

					if(!$receiver)
						$userFieldErr = $fielErr;

					if(!$message)
						$messageFieldErr = $fielErr;

					if(!$messageSubject)
						$subjectFieldErr = $fielErr;
											
				}
				
			}else
				$notLogged = $GLOBAL_notLogged;

		}
		
		$SITE->buildPageHtml(array("pageTitle"=>'Private Message (PM)',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/pm" title="">PM</a></li>'.$navPos),
					"pageBody"=>'
						<div class="single-base blend">
							<div class="base-ctrl">				
								<div class="panel panel-limex">
									<h1 class="panel-head page-title">PRIVATE MESSAGE(PM)</h1>
									<div class="panel-body">
										'.$notLogged.'			
										<span id="pmsent">'.$alertUser.'</span>
										<form class="horizontal-form '.EMOJIS_WIDGET_CMN_CLASS.($pmDispatchSucc? ' hide' : '').'" action="/'.$pageSelf.'" method="post">
											'.$SITE->bbcHandler('pm_mfield').'
											<fieldset>
												<div class="field-ctrl">
													<label>Receiver:</label>
													<input maxlength="30" class="field '.$userFieldErr.'" '.$readonly.' placeholder="Type in the username of the receiver here" type="text"  name="receiver" value="'.($receiver? $receiver : '').'" />'.(!$receiver? $asterixAll : '').'
												</div>
												<div class="field-ctrl '.($isBdWish? 'hide' : '').'">
													<label>Subject:</label>
													<input maxlength="100" class="field '.$subjectFieldErr.'" type="text" placeholder="Type in your message subject here " name="subject" value="'.($messageSubject? $messageSubject : ($isBdWish? $bdWishPmSubj : '')).'" />'.(!$messageSubject? $asterixAll : '').'
												</div>
												<div class="field-ctrl">
													<label>Message:</label>
													<textarea class="field '.$messageFieldErr.'" id="pm_mfield" placeholder="Type your message here" name="composed_message">'.($message? $message : ($isBdWish? $bdWishPreBody : '')).'</textarea>'.(!$message? $asterixAll : '').'													
												</div>			
												<div class="field-ctrl">
													<input class="form-btn" type="submit" value="SEND" name="send_message" />
												</div>
											</fieldset>	
										</form>			
									</div>
								</div>
							</div>
						</div>'
		));

		break;

	}
	
	
	
	
	
	
	/**COMPOSE EMAIL**/
	case 'email':{
					
		$receiver="";
		
		///////FORCE SESSION LEVEL ACCESS///////////
		$ACCOUNT->forceSessAccess();
		
		/***************************BEGIN URL CONTROLLER****************************/
		$userQstr = isset($pagePathArr[1])?  $pagePathArr[1] : '';
					
		if($userQstr && $SITE->isProfileSlug($userQstr)){	
							
			$pathKeysArr = array('pageUrl', 'receiver');
			$maxPath = 2;	

		}else{

			$pathKeysArr = array();
			$maxPath = 1;
	
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/
		
		//////////GET FORM-GATE RESPONSE//////////	
		list($alertUser, $edSuccess) = $SITE->formGateRefreshResponse(true);

		if(isset($_POST[$K="receiver"])) 
			$receiver = $_POST[$K];

		elseif(isset($_GET[$K])) 
			$receiver = $_GET[$K];

		$readonly = $SITE->isProfileSlug($receiver)? 'readonly="readonly"' : '';
		$admin = $GLOBAL_isAdmin;

		$adminPass = ($admin && isset($_GET[$K="token"]) && strtolower($_GET[$K]) == "adm_pass"
						&& isset($_GET[$K="em"]) && $_GET[$K]);

		/////////ON SEND////////////////

		if(isset($_POST['send_message'])){

			if($sessUsername){		
								
				$sender = $sessUsername;
				$receiver = $ENGINE->sanitize_user_input($receiver);
				
				if((strtolower($sender) != strtolower($receiver)) || $admin){

					$message = $ENGINE->sanitize_user_input($_POST['composed_message']);
					$messageSubject = $ENGINE->sanitize_user_input($_POST['subject']);
					$receiverSlug = $ACCOUNT->sanitizeUserSlug($receiver, array('anchor'=>true));

					if($message  && $receiver){
						
						if((strtolower($receiver) != strtolower($sender)) || $GLOBAL_isStaff){

							/////PDO QUERY/////

							$sql = "SELECT ID, EMAIL FROM users WHERE USERNAME=? LIMIT 1";
							$valArr = array($receiver);
							$stmt = $dbm->doSecuredQuery($sql, $valArr);
							$row = $dbm->fetchRow($stmt);
							$userExist = !empty($row);
							
							if($adminPass){

								$userExist = true;
								$sender = "Webmaster";
								$row["EMAIL"] = $ENGINE->sanitize_user_input($_GET["em"]);
								$row["ID"] = 0;

							}

							if($userExist){

								if(!$SITE->pmBlacklistHandler(array('action'=>'check','uid'=>$row["ID"],'buid'=>$sessUid))){
									
									$receiverEmail = $adminPass? $ENGINE->sanitize_user_input($_GET["em"]) : $row["EMAIL"];

									$to = $receiverEmail;

									$subject = $messageSubject;
									$footer = 'NOTE: This email was dispatched by '.$GLOBAL_sessionUrl_unOnly.' at <a href="'.$siteDomain.'">'.$siteDomain.'</a> to this email address. If you do not understand the origin of such request, please kindly ignore this message.\n\n\n Please do not reply to this email.';
								
									$SITE->sendMail(array('to'=>$to, 'senderName'=>$sender, 'subject'=>$subject, 'body'=>$message, 'footer'=>$footer));
									
									////CLOAK EMAIL////
									$receiverEmail = ($adminPass || $admin)? $receiverEmail : $ENGINE->cloak($receiverEmail);
									$alertUser = '<span class="alert alert-success">your email has been dispatched to '.($adminPass? $receiver : $receiverSlug.'\'s')
											.' Email Address'.($admin? ': <a href="mailto:'.$receiverEmail.'">'.$receiverEmail.'</a>.' : '').'</span>';
									
									$ACCOUNT->updateUser($sender, 'EDMD=NOW()');//MONITOR DISPATCH FREQ
									////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
									$SITE->formGateRefresh(array($alertUser, 'edSuccess'), '', '', false);

								}else
									$alertUser = '<span class="alert alert-danger">Sorry '.$GLOBAL_sessionUrl.' are not allowed to send a private mail to '.$receiverSlug.'</span>';
							}else
								$alertUser = '<span class="alert alert-danger">Sorry the user: <span class="blue">'.$receiver.'</span> was not found<br/> Your message was not sent </span>';
		
						}else
							$alertUser = '<span class="alert alert-danger">Sorry You can`t dispatch email to yourself</span>';
	
					}else
						$alertUser = '<span class= "alert alert-danger">please fill out the necessary fields</span>';
					
				}else
					$alertUser = '<span class="alert alert-danger">sorry you cannot send email to yourself</span>';	
			}else
				$notLogged = $GLOBAL_notLogged;
		}
		
		$subNav="";
		$subNav = '<li><a href="/email"  title="">EMail</a></li>';		
		if($receiver) $subNav .= '<li>'.$ACCOUNT->sanitizeUserSlug($receiver, array('anchor'=>true)).'</li>' ;
		
		$SITE->buildPageHtml(array("pageTitle"=>'Compose New Email Message',
				"preBodyMetas"=>$SITE->getNavBreadcrumbs($subNav),
				"pageBody"=>'				
					<div class="single-base blend">
						<div class="base-ctrl">			
							<div class="panel panel-limex">					
								<h1 class="panel-head page-title">SEND E-MAIL</h1>
								<div class="panel-body">'.
									(isset($notLogged)? $notLogged : '').											
									(isset($alertUser)?    '<p>'.$alertUser.'</p>' : '').
									(($sessUsername && $ACCOUNT->loadUser($sessUsername)->getEmailDispatchMonitorDate() && !$GLOBAL_isStaff)? 
										($edSuccess? '' : 
											'<div class="alert alert-warning">
												Oops! you recently sent an email<br/> our dispatcher is busy now<br/> please try again after some time.
											</div>') : '
										<form class="horizontal-form" action="/'.$GLOBAL_page_self.'" method="post">
											<fieldset>
												<div class="field-ctrl">
													<label>Receiver:</label>
													<input maxlength="30" class="field" '.$readonly.' placeholder="Type in the username of the receiver here" type="text" name="receiver" value="'.$receiver.'" />
												</div>													
												<div class="field-ctrl">
													<label>Email Subject:</label>
													<input  class="field" type="text"  placeholder="Type in your message subject here " name="subject" value="'.(isset($messageSubject)? $messageSubject : '').'" />
												</div>
												<div class="field-ctrl">
													<label>Compose Message:</label>
													<textarea  class="field" placeholder="Type your message here" name="composed_message">'.(isset($message)? $message : '').'</textarea>
												</div>
												<div class="field-ctrl">
													<input class="form-btn"  type="submit" value="SEND EMAIL" name="send_message" />
												</div>
											</fieldset>											
										</form>').'			
								</div>
							</div>
						</div>
					</div>'
		));
	
		break;
	
	}
	
	
	
	
	
	/**LOAD CATEGORIES**/
	case 'load-category':{

		/////////////////VARIABLE INITIALIZATION////////
		$desc=$topic=$categName=$sectionName=$subNav=$currPageOfTotal=$getSortBy=$populateCategSections=
		$totalSections=$getPage=$sortActive=$addToSubNav=$topics=$pagination_scat=$sortNav=$categTopics=
		$cid=$categorySlugPassed=$tab=$categTitleForSubNav=""; 
		
		$sectionTab=$topicTab=false;
		/***************************BEGIN URL CONTROLLER****************************/

		$pageSelf = $ENGINE->get_page_path('page_url', 1);
		
		if(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "topics"){
	
			$expectedPaths_arr = array("updated", "latest", "views", "posts", "alphabet");
			
			if(isset($pagePathArr[2]) && in_array(strtolower($pagePathArr[2]), $expectedPaths_arr)){
	
				$pathKeysArr = array("category", "topics", "sort_by", "pageId");
				$maxPath = 4;
	
			}else{
			
				$pathKeysArr = array("category", "topics", "pageId");
				$maxPath = 3;
			
			}
			
			$topicTab = true;
			$headerTxt = 'TOPICS';
			$metaArr = array('appendXtn'=>'');	
			
		}else{
		
			$pathKeysArr = array("category");
			$sectionTab = true;	
			$headerTxt = 'SECTIONS';
			$metaArr = array();	
			$maxPath = 1;
				
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		///////GET CATEGORY SLUG/////////////
		if(isset($_GET[$K="category"]))
			$categorySlugPassed = $ENGINE->sanitize_user_input($_GET[$K]);

		////FETCH CORRESPONDING CATEGORY ID/////////////////// 
		$cid = $SITE->categoryIdToggle($categorySlugPassed);
		$vcid = $SITE->sectionIdToggle($categorySlugPassed);
		$adsCampaign = new AdsCampaign();
		
		$tab = $sectionTab? '<li class="active"><a href="/'.$pageSelf.'" class="links">Sections</a></li><li><a href="/'.$categorySlugPassed.'/topics" class="links">Topics</a></li>'
						: '<li><a href="/'.$pageSelf.'" class="links">Sections</a></li><li class="active"><a  href="/'.$categorySlugPassed.'/topics" class="links">Topics</a></li>';	
		
		///////////GET THE HTML TO DISPLAY/////////

		///////////////GET SORT ORDER////////////

		if(isset($_GET[$K="sort_by"]))
			$getSortBy = $_GET[$K];


		/////////////////////////GET THE DESCRIPTION//////////////

		///////////PDO QUERY////////////////////////////////////	
			
		$sql = "SELECT CATEG_NAME, CATEG_DESC FROM categories WHERE ID=? LIMIT 1";
		$valArr = array($cid);
		$stmt = $dbm->doSecuredQuery($sql, $valArr);
		$row = $dbm->fetchRow($stmt);	
						
		if(!empty($row)){
							
				$desc = '<div class="board-desc base-lr-pad" >'.$row["CATEG_DESC"].'</div>';

				$categName = $row["CATEG_NAME"];
				$categSlug = $ENGINE->sanitize_slug($categName, $metaArr);
				
				/******FORCE REDIRECT CONTROLLER***********/
				if($categSlug != $ENGINE->get_page_path('page_url', 1, true, false)){
		
					$pagePathArr[0] = $categSlug;
					header("Location:/".implode('/', $pagePathArr), true, 301);
					exit();
		
				}

		}			
		
		////////GET TOTALS TOPICS AND SECTIONS IN THIS CATEG/////////

		$catQryCndOnly = ' SECTION_ID IN(SELECT ID FROM sections WHERE CATEG_ID=? )  ';
		$catQryCnd = ' WHERE '.$catQryCndOnly;

		///////////PDO QUERY//////////
			
		$sql = "SELECT COUNT(*) FROM topics ".$catQryCnd;
		$valArr = array($cid);	

		$totalSectionTopics = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();

		$totalSectionTopics = $ENGINE->format_number($totalSectionTopics).' Topic'.(($totalSectionTopics == 1)? '' : 's');



		/////FETCH TOTAL BOARDS AND APPEND TOTAL BOARD TOPICS/////

		///////////PDO QUERY//////
		
		$sql = "SELECT s.ID, SECTION_NAME, SECTION_DESC ".$SITE->getPopularitySubQuery()." FROM sections s WHERE CATEG_ID=? AND PARENT_SECTION = '' ORDER BY POPULARITY DESC";
		$valArr = array($cid);
		$stmt = $dbm->doSecuredQuery($sql, $valArr, true);	
						
		$totalSections = $dbm->getRecordCount();
					
		$totalSections = ' <span class="cyan">('.$totalSections.' Sections & '.$totalSectionTopics.')</span>';			

		$counter=0;
		 
		////SECTION TAB//////
		if($sectionTab){
			
			$titleSep = ' - ';
			
			/****POPULATE SECTIONS*****/ 
			while($row = $dbm->fetchRow($stmt)){
				
				$sectionName = $row["SECTION_NAME"];
				$sid = $row["ID"];					
				
				$populateCategSections .= $FORUM->loadSections($sid);

				////ACCUMULATE FIRST 4 SECTIONS TO DISPLAY IN TITLE WHEN LINK IS HOVERED///////
						
				//if($counter <= 9){
							
					$categTitleForSubNav .= $sectionName.$titleSep;
							
					//$counter++;
							
				//}
				
			}
			
			////STRIP THE LAST " - "/////
			$categTitleForSubNav = rtrim($categTitleForSubNav, $titleSep);

		}
		////TOPIC TAB///////////////////
		elseif($topicTab){		
			
			///////////PDO QUERY/////////////
				
			$sql = "SELECT COUNT(*) FROM topics ".$catQryCnd;
			$valArr = array($cid);
			$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
			
			/**********CREATE THE PAGINATION**********/				
			$pageUrl = $categSlug.'/topics';
			$qstrValArr = array($getSortBy);
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'qstrVal'=>$qstrValArr,'perPage'=>30,'hash'=>'tab'));
			$pagination_scat = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageId = $paginationArr["pageId"];

			 
			/////GET THE TOTAL TOPICS IN THIS SECTION////
			 
			$topics=$currPageOfTotal="";

			$currPageOfTotal = '<div class="cpop">(page <span class="cyan">'.$pageId.'</span> of '.$totalPage.')</div>';


			///IF THERE ARE TOPICS IN THIS SECTION THEN POPULATE THE SECTION WITH ITS TOPICS USING THE PAGINATION////

			//////SORT ORDER////////
			
			switch(strtolower( $getSortBy)){
			
				case 'latest':			
					$orderUsed = "TIME DESC";
					break;
			
				case 'views':
					$orderUsed = "TOPIC_VIEWS DESC";		
					break;		
								
				case 'posts':		
					$orderUsed = "TOTAL_POSTS DESC";
					break;
			
				case 'alphabet':	
					$orderUsed = "TOPIC_NAME";
					break;
					break;
			
				case 'updated':	
					$orderUsed = "LAST_POST_TIME DESC";					
					break;
			
				default://pins		
					$orderUsed = "PINNED_BY_MOD DESC, PIN_TIME DESC";
			
			}	
			
			$sortNav = $SITE->buildSortLinks(array(
						'baseUrl' => $categorySlugPassed.'/topics', 'pageId' => $pageId, 'sq' => '', 'urlHash' => 'tab', 
						'activeOrder' => $getSortBy, 'orderList' => array(':pins', 'updated', 'latest', 'views', 'posts', 'alphabet')
						));
					

			if($totalRecords){
									
				//////PDO QUERY////////					
				
				$sql = $SITE->composeQuery(array('type' => 'for_topic', 'start' => $startIndex, 'stop' => $perPage, 'uniqueColumns' => '', 'filterCnd' => $catQryCndOnly, 'orderBy' => $orderUsed));
			
				list($topics) = $FORUM->loadThreads($sql, array($cid), $type="");
										
			}
							
			if(!$topics){
			
				$topics = '<span class="alert alert-danger">Sorry! there are no topics under this category yet.</span>';
				$sortNav='';
			
			}

		}

		//////////////GET THE ADS ON THIS CATEGORY/////////////

		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget($vcid);
		$adSlots = $adsCampaign->getAdSlots($vcid, ' THIS CATEGORY ');
		$SITE->collectSiteTraffic();
		$online = $SITE->displaySiteTraffic("Category");

		if($topicTab){
			$categTopics = '<ul class="topic-base no-tp">
									'.$sortNav.'
									'. $pagination_scat 
									.$topics.'
									'.$pagination_scat.' 
								
								</ul>';
								
			$addToSubNav = '<li><a href="/'.$ENGINE->get_page_path('page_url', 2).'">Topics</a></li>';

		}	

		$contactMods = '<nav class="nav-base base-xs"><ul class="nav nav-tabs justified-center"><li><a href="/contact-moderators?cid='.$cid.'" class="links" title="Contact '.$categName.' moderators" >Contact moderators</a></li></ul></nav>';					
								
		$pageTitle = $categName;
						
		$lavatar = ' '.$ENGINE->build_lavatar($categName, '-7px', true);
							
		$pageBody =	'<div class="single-base blend">
						<div class="align-c">	
							<h2 class="page-title pan" title="'.$cid.'">'.strtoupper($categName).$lavatar.'</h2>
							<div class="cpop">'.$totalSections.'</div>
							<div class="prime-o bold">'.$SITE->moderatedSectionCategoryHandler(array('scId'=>$cid, 'level'=>2, 'action'=>'get', 'vcardMin'=>true, 'n'=>10)).'</div>
							'.$adsCampaign->getAdRate(array("sid"=>$vcid, "noHeader"=>true)).$adSlots.$desc.'
						</div>
						<div class="align-c">																							
							<nav class="nav-base"><ul class="nav nav-tabs justified justified-bom">'.$tab.'</ul></nav>
							'.$pageTopAds.$contactMods.'
						</div>
						<div class="row">
							<div class="base-ctrl base-rad '.$leftWidgetClass.'">
								<h2 class="page-title pan bg-limex">'.$headerTxt.'</h2>
								'.$currPageOfTotal.'
								'.($sectionTab? '<ul class="topic-base sections">'.$populateCategSections.'</ul>' : '')						
								.$categTopics.'
							</div>
							'.$rightWidget.'
						</div>
						<div class="align-c base-b-pad">																				
							'.$contactMods.$online.'						
							'.$pageBottomAds.'										
						</div>
					</div>';	
								
		$subNav = $SITE->getNavBreadcrumbs('<li>'.$ENGINE->sanitize_slug($categName, array('ret'=>'url', 'urlAttr'=>'title="'.$categTitleForSubNav.'"')).'</li>'.$addToSubNav);
			
		$SITE->buildPageHtml(array("pageTitle"=>$pageTitle, "preBodyMetas"=>$subNav, "pageBody"=>$pageBody));
			
		break;
			
	}
	
	
	
	
	
	/**LOAD SECTION**/
	case 'load-section':{
		
		/////////////////VARIABLE INITIALIZATION///////
		$desc=$topic=$cid=$categName=$sectionName=$childSections=$childSids=$childSidArr=$copyParentSection=
		$parentSection=$parentSectionForMob=$subNav=$sortActive=$plus18=
		$getSortBy=$sectionAdRates=$getPage=$sid=$sectionSlugPassed=
		$currPageOfTotal=""; 
		
		/***************************BEGIN URL CONTROLLER****************************/

		$expectedPaths_arr = array("updated", "latest", "views", "posts", "alphabet");
			
		if(isset($pagePathArr[1]) && in_array(strtolower($pagePathArr[1]), $expectedPaths_arr)){
			
			$pathKeysArr = array("section", "sort_by", "pageId");
			$noXtn = true;
			$maxPath = 3;
			
		}else{
		
			$pathKeysArr = array("section", "pageId");
			$maxPath = 2;
		
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/
		$rdr = $GLOBAL_rdr;		

		///////GET SLUG/////////////
		if(isset($_GET[$K="section"]))
			$sectionSlugPassed = $ENGINE->sanitize_user_input($_GET[$K]);

		////SECTION ID/////////////////// 
		$sid = $SITE->sectionIdToggle($sectionSlugPassed) ;
		$pageSelf = $ENGINE->get_page_path('page_url', 1);			

		///////////////GET SORT ORDER//////////////////////////////
		
		$adsCampaign = new AdsCampaign();
		$getSortBy = isset($_GET[$K="sort_by"])? $ENGINE->sanitize_user_input($_GET[$K]) : '';
		$pageIdTmp = isset($_GET[$K="pageId"])? $_GET[$K] : '';

		$metaArr = ($pageIdTmp || isset($noXtn) || isset($_POST["pagination_jump"]))? array('appendXtn'=>'') : array();

		///////GET THE SECTION DETAILS FROM ITS ID //////

		///////////PDO QUERY//////////////
			
		$sql = "SELECT SECTION_NAME,CATEG_ID,PARENT_SECTION,(SELECT GROUP_CONCAT(b.ID) FROM sections b
				WHERE b.PARENT_SECTION != '' AND a.ID=b.PARENT_SECTION) AS CHILD_SECTIONS,
				SECTION_DESC FROM sections a WHERE a.ID=? LIMIT 1";
		$valArr = array($sid);
		$stmt = $dbm->doSecuredQuery($sql, $valArr);
		$row = $dbm->fetchRow($stmt);

		if(!empty($row)){
						
			$sectionName = $row["SECTION_NAME"];
			$sectionSlug = $ENGINE->sanitize_slug($sectionName, $metaArr);

			$cid = $row["CATEG_ID"];
			
			$categName = $SITE->categoryIdToggle($cid);	
			
			$parentSid = $row["PARENT_SECTION"];
			$parentSection = $parentSid? $SITE->sectionIdToggle($parentSid) : '';
			
			if($parentSid == ADULT_SID || $SITE->sectionIdToggle($sectionName) == ADULT_SID){
		
				$plus18 = '(<b title="Topics in this sections contains adult materials suitable for only 18years and above" class="red">18+</b>)';
				$SITE->adultContentsViewPrompt($rdr);
		
			}
		
			if($parentSection)
				$copyParentSection = ' - '.$parentSection;
			
			if($parentSection)
				$parentSectionForMob = $parentSection = '<li>'.$ENGINE->sanitize_slug($parentSection, array('ret'=>'url', 'urlAttr'=>'title="'.$FORUM->getSectionDescription($parentSection).'"')).'</li>';		
			
			
			if($sectionSlug != $ENGINE->get_page_path('page_url', 1, true, false)){
		
				$pagePathArr[0] = $sectionSlug;
				header("Location:/".implode('/', $pagePathArr), true, 301);
				exit();
		
			}	
		
			//////////GET CHILD SCAT IF ANY///////
				
			if($childSids = $row["CHILD_SECTIONS"]){
					
				$childSidArr = explode(',', $childSids);
				$totalChildSections = count($childSidArr);
				
				if(is_array($childSidArr)){
				
					for($idx=0; $idx < $totalChildSections; $idx++){
			
						$childSid = $childSidArr[$idx];
						$childSection = $SITE->sectionIdToggle($childSid);
						//$childSections .= '<div class="font-tiny cyan"><i class="micon" > >> </i> '.$ENGINE->sanitize_slug($childSection, array('ret'=>'url', 'urlAttr'=>'title="'.$FORUM->getSectionDescription($childSection).' - '.$childSid.'"')).' ('.$FORUM->countSectionTopics($childSid, true).')</div>';
						$childSections .= '<span class="font-tiny cyan">'.$ENGINE->sanitize_slug($childSection, array('ret'=>'url', 'urlText'=>$childSection.' ('.$FORUM->countSectionTopics($childSid, true).')', 'cssClass'=>'pill-follower', 'urlAttr'=>'title="'.$FORUM->getSectionDescription($childSection).' - '.$childSid.'"')).'</span>';
				
					}
				
				}

			}
			
			$childSections = $childSids? '<div class="child-sections align-l" ><h4 class="prime">SUB-SECTIONS:</h4><div class="">'.$childSections.'</div></div>' : '';
				
			
			$desc = '<div class="board-desc base-lr-pad" >'.$FORUM->getSectionDescription($sid).'</div>';
			
			$sectionAdRates = $adsCampaign->getAdRate(array("sid"=>$sid, "noHeader"=>true));
			
		}


		///////PDO QUERY//////////

		$sql = "SELECT COUNT(*) FROM topics WHERE SECTION_ID = ? ";
		$valArr = array($sid);
		$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();

		/**********CREATE THE PAGINATION**********/
		
		$pageUrl = $ENGINE->sanitize_slug($sectionName, array('appendXtn'=>false));
		$qstrValArr = array($getSortBy);
		$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'qstrVal'=>$qstrValArr,'perPage'=>30,'hash'=>'tab'));						
		$pagination_scat = $paginationArr["pagination"];
		$totalPage = $paginationArr["totalPage"];
		$perPage = $paginationArr["perPage"];
		$startIndex = $paginationArr["startIndex"];
		$pageId = $paginationArr["pageId"];

		//GET THE TOTAL TOPICS IN THIS SECTION///////
			   
		$topics="";

		$customTotalRecords = $totalRecords? '(<span class="cyan">'.$ENGINE->format_number($totalRecords).'</span> '.(($totalRecords == 1)? 'Topic' : 'Topics').')' : '';
		
		$currPageOfTotal="";

		if($pageId)
			$currPageOfTotal = '<div class="cpop">(page <span class="cyan">'.$pageId.'</span> of '.$totalPage.')</div>';


		/////IF THERE ARE TOPICS IN THIS SECTION THEN POPULATE THE SECTION WITH ITS TOPICS USING THE PAGINATION///

		//////SORT ORDER/////////////////////////////////

		switch(strtolower($getSortBy)){

			case 'latest':
				$orderUsed = "TIME DESC";	
				break;

			case 'views':
				$orderUsed = "TOPIC_VIEWS DESC";
				break;

			case 'posts':
				$orderUsed = "TOTAL_POSTS DESC";			
				break;

			case 'alphabet':
				$orderUsed = "TOPIC_NAME";
				break;				

			case 'updated':
				$orderUsed = "LAST_POST_TIME DESC ";				
				break;

			default://pins
				$orderUsed = "PINNED_BY_MOD DESC, PIN_TIME DESC";

		}
		
		$sortNav = $SITE->buildSortLinks(array(
					'baseUrl' => $sectionSlugPassed, 'pageId' => $pageId, 'sq' => '', 'urlHash' => 'tab', 
					'activeOrder' => $getSortBy, 'orderList' => array(':pins', 'updated', 'latest', 'views', 'posts', 'alphabet')
					));

		if($totalRecords){
				
			////PDO QUERY//////

			$sql = $SITE->composeQuery(array('type' => 'for_topic', 'start' => $startIndex, 'stop' => $perPage, 'filterCnd' => 'SECTION_ID=?', 'orderBy' => $orderUsed));

			list($topics) = $FORUM->loadThreads($sql, array($sid), $type="");	
				
			
		}

		if(!$topics){
			
			$topics = '<span class="alert alert-danger">Sorry! there are no topics in this section yet.</span>';
			$sortNav='';
			
		}
		
		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget($sid);
									
		///GET THE MODERATORS/////////////

		$moderators=$online=$body=$contactCreateLink=$sectionFollowLink="";

		if($sessUsername){
			
			///FOLLOW/UNFOLLOW SECTION/////
						
			if($FORUM->followedSectionsHandler(array('uid'=>$sessUid, 'sid'=>$sid, 'check'=>true)))
				$sectionFollowLink = '<li><a href="/section-follows/unfollow/'.$sid.'/?_rdr='.$rdr.'" class="follow_scat links"  data-sid="'.$sid.'" data-action="unfollow" data-twin="true" data-sname="'.$sectionName.'" title="you are following the '.$sectionName.' section, click to unfollow" >Unfollow this section</a><span></span></li>';		
			
			else
				$sectionFollowLink = '<li><a href="/section-follows/follow/'.$sid.'/?_rdr='.$rdr.'" class="follow_scat links" data-sid="'.$sid.'" data-action="follow" data-twin="true" data-sname="'.$sectionName.'" title="click to follow the '.$sectionName.' section" >Follow this section</a><span></span></li>';		
						
		}
			
		$contactCreateLink = '<li><a href="/contact-moderators?sid='.$sid.'" class="links" title="Contact '.$sectionName.' moderators" >Contact moderators</a></li>';		

		////////////EXEMPT STAFF SECTIONS FROM PUBLIC////////
			
		$disabledSection = in_array($sid, getExceptionParams('virtualSids'));
		$headTitle = 'TOPICS';
		
		if(in_array($sid, getExceptionParams('sidsstaffsonly')) && !$GLOBAL_isStaff){
					
			$body = '<span class="alert alert-danger">Sorry! You do not have enough privilege to view contents on this page.</span>';
			$restrictedView = true;
		
		}else{
				
			if(in_array($sid, VIRTUAL_SIDS_ARR)){////IF THE SECTION IS ELITE SECTION///////
				$body = '<div class="topic-base">
							<div class="base-lr-pad">
								<b class="alert alert-warning">This is a virtual section strictly designed for advertising purpose.<br/> 
								Ads placed on this section will appear on this page and primarily
								'.(($sid == ELITE_SID)? ' in the unique pages
								outlined below:</b>
								<ol  class="align-l has-ra-carets">
									<li><a href="/followed-topics">Followed Topics</a></li>
									<li><a href="/followed-sections/topics">Followed Sections Topic</a></li> 
									<li><a href="/followed-sections/posts">Followed Sections Post</a></li> 
									<li><a href="/followed-members/topics">Followed Members Topic</a></li>
									<li><a href="/followed-members/posts">Followed Members Post</a></li>
									<li><a href="/new-topics">New Topics</a></li>
									<li><a href="/trending-topics">Trending Topics</a></li>
									<li><a href="/featured-hot-topics">Featured Hot Topics</a></li>
									<li><a href="/latest-post">New Posts</a></li>
									<li><a href="/search">Search</a></li>
									<li><a href="/euroadams/latest-topics">Members List of Latest Topics</a></li>
									<li><a href="euroadams/all-topics">Members List of Topics</a></li>
									<li><a href="/euroadams/all-posts">Members List of Posts</a></li>
									<li><a href="/votes-shares/upvotes">Members Upvotes</a></li>
									<li><a href="/votes-shares/downvotes">Members Downvotes</a></li>
									<li><a href="/votes-shares/shares">Members Shares</a></li>
									<li><a href="/posts-you-were-quoted-or-tagged">Post Members were Quoted or Tagged</a></li>
									<li><a href="/posts-you-upvoted">Post Members Upvoted</a></li>
									<li><a href="/posts-you-downvoted">Post Members Downvoted</a></li>
									<li><a href="/posts-you-shared">Post Members Shared</a></li>
									<li><a href="/posts-you-quoted">Post Members Quoted</a></li>
									<li><a href="/posts-shared-with-you">Post Members Shared With Others</a></li>
								</ol>
								<div class="alert alert-info">
									Ads placed on this section will  have almost the same traffic as those placed on <a href="/" >Homepage</a>.
									Since many advertisers are unaware of the existence of the <a href="/elite" >Elite</a> section, its advertising rate is way more cheaper than that of the <a href="/" >Homepage</a>.<br/>
									The <a href="/elite" >Elite</a> Section is a highly recommended section for advertisers because even with its little awareness and low Ad rate or cost, it remains a major rival of the <a href="/" >Homepage</a> in terms of traffic.											
								</div>' : 'on the homepage.</b>').'				
							</div>				
						</div>'; 
		
				$headTitle = 'ATTENTION';
		
				
			}else{

				$body = '<ul class="topic-base no-tp" id="land">
							'.$sortNav
							.$pagination_scat					
							.$topics.'
							'.$pagination_scat.'
							<hr/>							
						</ul>';


				$contactCreateLink .= $disabledSection? '' : '<li><a href="/createtopic?section='.$sid.'" class="links" title="click to create a new thread or topic">Start a thread</a></li>';
				
			}
			
		}

		if($disabledSection)
			$sectionFollowLink = '';//DISABLE FOLLOW/UNFOLLOW BTN
		
		if(!isset($restrictedView))
			$SITE->collectSiteTraffic();

		$online = $SITE->displaySiteTraffic("Section");
		
		$moderators = $SITE->moderatedSectionCategoryHandler(array('scId'=>$sid, 'level'=>1, 'action'=>'get', 'vcardMin'=>true, 'n'=>10));
		
		$contactCreateLink = isset($restrictedView)? '' : '<nav class="nav-base"><ul class="nav nav-tabs justified">'.$contactCreateLink.$sectionFollowLink.'</ul></nav>';
				

		$subNav = ($cid? '<li>'.$ENGINE->sanitize_slug($categName, array('ret'=>'url', 'urlAttr'=>'title="'.($categDesc=$FORUM->getCategoryDescription($cid)).'"')).'</li>' : '')
					.$parentSection.'<li>'.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url', 'urlText'=>$sectionName.($plus18? ' <b class="red">18+</b>' : ''), 'urlAttr'=>'title="'.($sectionDesc=$FORUM->getSectionDescription($sid)).'"')).'</li>';
					
		$subNavForMobile = ($cid? '<li>'.$ENGINE->sanitize_slug($categName, array('ret'=>'url', 'urlText'=>$ENGINE->cloak($categName, '30:20', '-3', '.'), 'urlAttr'=>'title="'.$categDesc.'"')).'</li>' : '')
					.$parentSectionForMob.'<li>'.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url', 'urlText'=>$ENGINE->cloak($sectionName, '30:20', '-3', '.').($plus18? ' <b class="red">18+</b>' : ''), 'urlAttr'=>'title="'.$sectionDesc.'"')).'</li>';

		$subNav = $SITE->getNavBreadcrumbs($subNav, $subNavForMobile);			

		$pageTitle = $sectionName.$copyParentSection.' - '.$categName;

		$lavatar = ' '.$ENGINE->build_lavatar($sectionName, '-7px', true);
			 
		$pageBody =	'<div class="single-base base-rad blend-top">							
						<div class="default-base align-c">				
							<h2 class="" title="'.$sid.'">'.strtoupper($sectionName).''.$lavatar.$plus18.'
							<div class="italicize cpop cyan"  title="Total topics in the '.$sectionName.' section">'.$customTotalRecords.'</div>							
							</h2>
							<div class="prime-o bold">'.$moderators.'</div>						
							'.$sectionAdRates.$adsCampaign->getAdSlots($sid, ' THIS SECTION ').'<div>'.$desc.'</div>'.$childSections.'
						</div>
						'.$pageTopAds.'
						<div class="align-c">				
							'.$contactCreateLink.'
						</div>
						<div class="row">
							<div class="base-ctrl base-rad '.$leftWidgetClass.'">
								<h2 class="page-title pan bg-limex">'.$headTitle.'</h2>
								'.$currPageOfTotal.'
								'.$body.'	
							</div>													
							'.$rightWidget.'
						</div>
						<div class="align-c">

							'.$contactCreateLink.'
							
							'.$online.'
							
							'.$pageBottomAds.' 									

						</div>
					</div>';
		
		$SITE->buildPageHtml(array("pageTitle"=>$pageTitle, "preBodyMetas"=>$subNav, "pageBody"=>$pageBody));

		break;

	}
	
	
	
	
	
	
	/**CREATE TOPIC**/
	case 'createtopic':{

		$asterix=$topicHolder=$msgHolder=$categName=$sectionName=$topicFieldErr=$messageFieldErr=
		$autofocus=$date=$topic=$message=$status=$fileTooLarge=$uploadErr=$sid=
		$parentSection=$invalidSection=$invalidCateg=$vdsChk="";
		$fieldFocus = '#npff';
		$maxPostUploadSize = MAX_POST_UPLOAD_SIZE_STR;
		$requiredMssg = '<span class="alert alert-danger" >Fields marked (*) are required ! </span>';			
		$asterixMssg = '<span class="asterix">*</span>';
		$fError = 'field-error';
		$crtSubj = 'crt_sbjt';

		///////FORCE SESSION LEVEL ACCESS///////////
		$ACCOUNT->forceSessAccess();

		$fileTooLarge = $ENGINE->get_large_upload_limit_error();

		//EXEMPT ELITE, RECYCLE BIN, GENERAL, ENTERTAINMENT, SCIENCE & TECHNNOLOGIES SECTIONS  FROM TOPIC CREATION/////
		///84=Elite,86=RecycleBin,87=General,88=Entertainment,89=Science&Technology/////
		$xceptionsArr = getExceptionParams('sidsnothread');
			 
		if(isset($_POST[$K="section"]))
			$sid = $ENGINE->sanitize_number($_POST[$K]);

		if(isset($_GET[$K]))
			$sid = $ENGINE->sanitize_number($_GET[$K]);

		
		////PDO QUERY////////

		$sql = "SELECT * FROM sections WHERE ID=? LIMIT 1";
		$valArr = array($sid);
		$stmt = $dbm->doSecuredQuery($sql, $valArr);
		$row = $dbm->fetchRow($stmt);
		 
		$sectionFound = !empty($row);

		$sectionName = $row["SECTION_NAME"];
			
		$parentSid = $row["PARENT_SECTION"];
		$parentSection = $parentSid? $SITE->sectionIdToggle($parentSid) : '';

		$cid = $row["CATEG_ID"];

		$sid = $row["ID"];

		$categName = $SITE->categoryIdToggle($cid);

		/////////// ON CREATE OF A NEW TOPIC///////

		if(isset($_POST["create"]) && !in_array($sid, $xceptionsArr)){
			
		/////VERIFY THAT THE USER ATTEMPTING TO CREATE A TOPIC IS ACTUALLY LOGGED///////
				
			if($sessUsername){

				$topic = $ENGINE->title_case($ENGINE->sanitize_user_input($_POST["topicsubject"]));
				$message = $ENGINE->sanitize_user_input($_POST["topicmessage"], false, true);		
				$tags = $ENGINE->sanitize_user_input($_POST["tags"]);		
				$uiBgId = isset($_POST[$K=UIBG_ID_FIELD])? $_POST[$K] : 0;
				
				if(isset($_POST["vds"]))			
					$vdsChk = 'checked="checked"';
				
				///////////CHECK FOR EMPTY SUBJECT/TOPIC//////		
				
				if(!$topic){

					$required = $requiredMssg;
					$asterix = $asterixMssg;
					$topicFieldErr = $fError;			
					$fieldFocus = '#'.$crtSubj;
		
				}

				if($topic)			
					$topicHolder = $topic;
					
				//////////CHECK FOR EMPTY MESSAGE BODY/////////

				if(!$message){	
				
					$required = $requiredMssg;
					$asterix = $asterixMssg;			
					$messageFieldErr = $fError;
			
					if(!$fieldFocus)
						$fieldFocus = '#'.$composerFieldId;
			
				}

				if($message)			
					$msgHolder = $message;
								

				///CHECK IF TOPIC EXISTS ALREADY TO AVOID DUPLICATE TOPICS BY THE SAME USER /////		

				////PDO QUERY//////
			
				$sql = "SELECT ID, TOPIC_AUTHOR_ID, TOPIC_NAME FROM topics WHERE TOPIC_NAME LIKE ? AND SECTION_ID=? LIMIT 1";
				$valArr = array($topic, $sid);
				$stmt = $dbm->doSecuredQuery($sql, $valArr);
				$row = $dbm->fetchRow($stmt);	
				
				$topicExistsErr="";
				
				$topicExists = !empty($row);
				$existingTopicId = $row["ID"];
				$topicExistName = $row["TOPIC_NAME"];

				if($topicExists && !$GLOBAL_isAdmin){
					
					$topicExistsErr = '<div class="alert alert-warning" >
											<strong class="red">WARNING!!!<br/>'.$GLOBAL_sessionUrl_unOnly.',</strong> 			
											<span class="red">
												a similar topic: <a class="links" href="'.$SITE->getThreadSlug($existingTopicId).'">'.$topicExistName.'</a> 
												already exists in this section
											</span>
											<p class="red bold">PLEASE NOTE THAT WE DO NOT CONDOLE SPAMMING</p>
											<p>If however you wish to continue with the same topic name please append some words or 
												characters to the current topic name to show that it`s a continuation of an already existing topic.
											</p>
											<h3>SUGGESTIONS:</h3><ul class="ul default blue align-l"><li>'.$topicExistName.'(2)</li><li>'.$topicExistName.' 2</li><li>'.$topicExistName.'[2]</li><li>'.$topicExistName.' '.date('Y ').'</li><li>'.$topicExistName.' part 2</li></ul>
										</div>';
					
					
				}
					
				if($topic  && !$topicExistsErr && $message){
				
					////////VERIFY THAT SUBCATEG IS PASSED///
		 
					if( isset($sid)){
			
						//////////VERIFY THAT THE PASSED SUBCATEG EXIST///////

						if($sectionFound){
																
							try{
								
								// Run new thread creation transaction
								$dbm->beginTransaction();									
																
								////////////////UPLOAD FILES IF ANY//////////								
								list($userUploads, $originalUserUploads, $uok, $error) = $FORUM->uploadPostFiles($oldUploadsCount=0);
								
								if(!$uok)
									$autofocus = 'autofocus';
				 
								if($uok){  
									
									$tags = $tags? $badgesAndReputations->processTags(array('tags'=>$tags, 'encode'=>true)) : '';
									///////////PDO QUERY///////
							
									$sql = "INSERT INTO topics (TOPIC_AUTHOR_ID, TOPIC_NAME, TIME, SECTION_ID, TAGS)
											VALUES(?,?,NOW(),?,?)";

									$valArr = array($sessUid, $topic, $sid, $tags);
									$stmt = $dbm->doSecuredQuery($sql, $valArr);	
									
									////GET THE NEWLY CREATED TOPIC ID FROM DB////////												
									$newTid = $dbm->getLastInsertId();									

									/////////UPDATE THE POST TABLE///////									
									
									//////VDS_WARNING////////
									if(isset($_POST["vds"]) && $userUploads){
					
										$vdsWarning = 1;
										$vdsChk = 'checked="checked"';
					
									}else
										$vdsWarning = 0;
									
									$syndicateUrl = isset($_POST[$K="src_url"])? $_POST[$K] : '';									

									///////////PDO QUERY///////
				
									$sql = "INSERT INTO posts (POST_AUTHOR_ID, TIME, TOPIC_ID, MESSAGE, VDS_WARNING, SYNDICATE_URL, UIBG_ID)
											VALUES(?,NOW(),?,?,?,?,?)";

									$valArr = array($sessUid, $newTid, $message, $vdsWarning, $syndicateUrl, $uiBgId);
									$stmt = $dbm->doSecuredQuery($sql, $valArr);	
									$newPostId = $dbm->getLastInsertId();
																		
									$FORUM->postedFilesHandler(array('pid'=>$newPostId, 'files'=>$userUploads, 'fileOriginalNames'=>$originalUserUploads));
															
									////AUTOMATICALLY FOLLOW THE TOPIC///////
									$FORUM->requestTopicFollow($newTid);			
									
									// If we arrived here then our thread creation transaction was a success, we simply end the transaction
									$dbm->endTransaction();
									
									////////ON TOPIC CREATION SUCCESS REDIRECT TO THE NEW TOPIC PAGE//////
				  
									header("Location:".$SITE->getThreadSlug($newTid));
									exit();


								}elseif(!$uok)						
									$uploadErr = '<span class="alert alert-danger">Sorry there was an error uploading your file<br/>'.$error.'</span><br/>';												
									
							}catch(Throwable $e){
								
								// Rollback if new registration transaction fails
								$dbm->cancelTransaction();
								$alertUser = $keepForm = '<span class="alert alert-danger">Sorry your thread could not be created! Please try again</span>';
								
							}
								

						}
			
						///IF THE SUBCATEG ID PASSED WAS NOT FOUND///////
			
						else				
							$alertUser = '<span class="alert alert-danger">Sorry that Category or Section was not found</span>';								
						
												
					}
					///IF NO SUBCATEG ID  WAS PASSED/////////
					else				
						$alertUser = '<span class="alert alert-danger"> Sorry no Category was specified</span>';				
					
				}

			}
			else{
						
				header("Location:/login?_rdr=".$GLOBAL_rdr."#loginUsername");
				exit();
				
			}	

		}

		/////////////IF NO SECTION ID OR AN INVALID SECTION ID IS PASSED///////

		if(!$sectionFound || !$sid){
				
			$invalidSection= '<a href="/"  title="this section does not exist" ><span class="red"> Invalid Section </span></a>';
			$invalidCateg = '<a href="/" title="this category does not exist" ><span class="red"> Invalid Category </span></a>';
			$alertUser = '<span class="alert alert-danger">A section mismatch error has occurred! Please verify the link you entered and try again</span>';
				
		}else{
			
			$invalidSection = $ENGINE->sanitize_slug($sectionName, array('ret'=>'url', 'urlAttr'=>'title="'.$FORUM->getSectionDescription($sid).'"'));
			$invalidCateg = $ENGINE->sanitize_slug($categName, array('ret'=>'url', 'urlAttr'=>'title="'.$FORUM->getCategoryDescription($cid).'"'));
		
		}

		if($parentSection)
			$parentSection = '<li>'.$ENGINE->sanitize_slug($parentSection, array('ret'=>'url', 'urlAttr'=>'title="'.$FORUM->getSectionDescription($parentSection).'"')).'</li>';
		
		$subNav='';
		//if(!isset($alertUser))	
			$subNav = ($cid? '<li>'.$invalidCateg.'</li>' : '').$parentSection.'<li>'.$invalidSection.'</li>
						<li><a href="/createtopic?section='. $sid .'#'.$crtSubj.'" title="Create a new topic under the '.$sectionName.' section">New Topic</a></li>';
		
		//ACCUMULATE COMPOSER METAS			
		$metaArr['crtSubj'] = $crtSubj;
		$metaArr['alertUser'] = isset($alertUser)? $alertUser : '';
		$metaArr['keepForm'] = isset($keepForm);
		$metaArr['required'] = isset($required)? $required : '';
		$metaArr['asterix'] = $asterix;
		$metaArr['topicExistsErr'] = isset($topicExistsErr)? $topicExistsErr : '';
		$metaArr['sid'] = $sid;
		$metaArr['cat'] = $categName;
		$metaArr['xceptionsArr'] = $xceptionsArr;
		$metaArr['topicFieldErr'] = $topicFieldErr;
		$metaArr['topicHolder'] = $topicHolder;
		$metaArr['messageFieldErr'] = $messageFieldErr;
		$metaArr['fieldFocus'] = $fieldFocus;
		$metaArr['autofocus'] = $autofocus;
		$metaArr['pageSelf'] = $pageSelf;
		$metaArr['textContent'] = $msgHolder;
		$metaArr['tags'] = isset($_POST[$K="tags"])? $_POST[$K] : '';
		$metaArr['vdsChk'] = $vdsChk;
		$metaArr['fileTooLarge'] = $fileTooLarge;
		$metaArr['uploadErr'] = $uploadErr;
		$metaArr['syndicateUrl'] = isset($syndicateUrl)? $syndicateUrl : '';
		
		$SITE->buildPageHtml(array("pageTitle"=>(isset($categName)? $categName : '').' - '.(isset($sectionName)? $sectionName : '').' - New Topic',			
						"preBodyMetas"=>$SITE->getNavBreadcrumbs($subNav),
						"pageBody"=>'				
						<div class="single-base blend">
							'.$SITE->getComposer($metaArr, 'create').'
						</div>'
		));
					
		break;
		
	}
	
	
	
	
	
	
	
	
	/**MAKE POST**/
	case 'post':{

		$textContent=$uploadedFilesView=$uploadErr=$composerForm=$alertMembers=$fileTooLarge=$spamAlert=$autofocus=$alertMods=
		$pageId=$pagination=$alertUser=$postModifyIdField=$rdrPageId=$vdsChk=$ajaxErr=$ajaxLoginErr=$topicId=$postAuthorized=
		$uiBgId=$uiBgLoader=$uiBgLoadedStyleMetas=$uiBgCloseBtn=$uiBgField=$uiBgFillCls=$silentModsAlert="";
		
		$topicClosed = false;
		
		/***************************BEGIN URL CONTROLLER****************************/

		if(isset($pagePathArr[2]) && strtolower($pagePathArr[2]) == "quote"){
		
			$pathKeysArr = array('pageUrl', 'tid', 'quote', 'quote_id', 'pageId');
			$maxPath = 5;
		
		}elseif(isset($pagePathArr[2]) && strtolower($pagePathArr[2]) == 'multi-quote'){
		
			$pathKeysArr = array('pageUrl', 'tid', 'multi_quote', 'pageId');
			$maxPath = 4;
			
		}elseif(isset($pagePathArr[2]) && strtolower($pagePathArr[2]) == 'edit'){
		
			$pathKeysArr = array('pageUrl', 'tid', 'edit', 'edit_id', 'pageId');
			$editSubNav = true;
			$maxPath = 5;
			
		}else{
		
			$pathKeysArr = array('pageUrl', 'tid', 'pageId');
			$maxPath = 3;
			
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		$isTrusted = $GLOBAL_isTrusted;
		$tops = ($GLOBAL_isAdmin || $isTrusted);
		$maxPostUploadSize = MAX_POST_UPLOAD_SIZE_STR;

		if($sessUsername){
			
			$fileTooLarge = $ENGINE->get_large_upload_limit_error();
			
			///////VERIFY THAT TOPIC ID IS PASSED////////
			
			
			if(isset($_GET[$K="tid"]) || isset($_POST[$K]) ){

				//////////////GET THE TOPIC ID AND SUB CATEGORY NAME/////////////

				if(isset($_GET[$K]))
					$topicId = $_GET[$K];

				if(isset($_POST[$K]))
					$topicId = $_POST[$K];
				
				$topicId =  $ENGINE->sanitize_number($topicId);
				
				if(isset($_GET[$K="rdrPageId"]))
					$rdrPageId = $_GET[$K];

				////////////GET THE TOPIC NAME FROM THE ID PASSED////////////
						

				///////////PDO QUERY/////////////////

				$sql = $SITE->composeQuery(array('type' => 'for_topic', 'stop' => 1, 'filterCnd' => 'topics.ID = ?'));

				$valArr = array($topicId);
				$stmt = $dbm->doSecuredQuery($sql, $valArr);
				$row = $dbm->fetchRow($stmt);

				$topicFound = !empty($row);
					
				//////MAKE SURE THE TOPIC ID PASSED IS ACTUALLY TIED TO A TOPIC////////

					
				if($topicFound){

					$topicAuthorId = $row["TOPIC_AUTHOR_ID"];
					$topicName = $row["TOPIC_NAME"];
					$tags = isset($_POST["tags"])? $_POST["tags"] : $row["TAGS"];
					$sid = $row["SECTION_ID"];			
					$sectionName = $row["SECTION_NAME"];								
					$cid = $row["CATEG_ID"];
					$categName = $row["CATEG_NAME"];
												
					///////////////////VERIFY THE TOPIC STATUS FROM DB//////////					
										
					$topicStatArr = $FORUM->getThreadStatus($topicId);										
					$topicRecycled = $topicStatArr["isRecycled"];					
					$topicClosed = $topicStatArr["tClosed"];
					$newPostLink = "post";

					$threadUrl = $SITE->getThreadSlug($topicId);
					
					list($postAuthorized, $viewAuthorized, $protectionAlert) = $FORUM->authorizeThreadAccess($topicId);
					
					if($viewAuthorized && $postAuthorized){																	
														
						if($topicClosed){

							$alertTopicClosed = $FORUM->getThreadClosedIcon();							
							$silentModsAlert = ($GLOBAL_isStaff? '<span class="alert alert-warning align-c">This thread is <b>CLOSED</b>,<br/> '.(($GLOBAL_isAdmin)? 'administrator\'s' : 'moderator\'s').' access privilege granted</span>' : '');

						}
			
						///CHECK IF U ARE FOLLOWING THIS TOPIC TO GET OPTION TO UNFOLLOW///////////
										
						$topicFollowCheckArr = $FORUM->requestTopicFollowLink($topicId);												
							
						if(isset($topicFollowCheckArr[$K="fnuLink"]))
							$topicFollowLink = '<nav class="nav-base no-pad"><ul class="nav nav-pills justified-center">'.$topicFollowCheckArr[$K].'</ul></nav>';
						
						if(isset($topicFollowCheckArr[$K="ftopicCheckBox"]))
							$ftopic = $topicFollowCheckArr[$K];	
						
									
						/////////IF MODIFY POST IS SET CAPTURE THE ID//////
												
						$postModificationId=$oldUploadsCount="";
						
						if((isset($_POST[$K="edit_id"]) && $_POST[$K]) || (isset($_GET[$K]) && $_GET[$K])){
							
							if(isset($_POST[$K]))
								$postModificationId = $_POST[$K];
						
							elseif(isset($_GET[$K]))
								$postModificationId = $_GET[$K];
							
							$postBtnTxt = 'UPDATE';
							$isPostEdit = true;
							$oldUploadsCount = $FORUM->postedFilesHandler(array('pid'=>$postModificationId, 'count'=>true));
						
						}


						/////////////WHEN A POST IS SET/////////////////

						if(isset($_POST["post"])){				

							if($sessUsername){

								if(isset($_POST["postmessage"]))
									$message = $ENGINE->sanitize_user_input($_POST["postmessage"], array('preserveTags' => true, 'preserveSlashes' => true, 'preserveWhitespace' => 2));/**GOING TO DB**********/

								$textContent = $message;

								/****
									WE DON`T WANT USERS TO POST ONLY WHITE SPACES AS CONTENT SO LET`S TRACK IT USING A NEW VARIABLE @originalMsgContent
									WE SIMPLY REMOVE WHITE SPACES AND USE IT FOR CONDITIONAL CHECKS
								****/
								$originalMsgContent = trim($message);

								$uok = true; //USER MAY NOT BE ATTACHING A FILE	
											
								////////////////UPLOAD FILES IF ANY//////////
								if($originalMsgContent)											
									list($userUploads, $originalUserUploads, $uok, $error) = $FORUM->uploadPostFiles($oldUploadsCount);
								
								if(!$uok)
									$autofocus = 'autofocus';											
								
								$uiBgId = isset($_POST[$K=UIBG_ID_FIELD])? $_POST[$K] : 0;
								$syndicateUrl = isset($_POST[$K="src_url"])? $_POST[$K] : '';
								
								/////////POST A MESSAGE IF EVERYTHING CHECKS OUT FINE/////////

								try{
									
									// Run new post transaction
									$dbm->beginTransaction();
									
									if($originalMsgContent && !$postModificationId  && $uok){
												
										//////VDS_WARNING////////
										if(isset($_POST["vds"]) && $userUploads)
											$vdsWarning = 1;
																			
										else
											$vdsWarning = 0;
										
												
										//////PDO QUERY///////////
										
										$sql = "INSERT INTO posts (POST_AUTHOR_ID, TIME, MESSAGE, TOPIC_ID, VDS_WARNING, SYNDICATE_URL, UIBG_ID)
																VALUES(?,NOW(),?,?,?,?,?)";

										$valArr = array($sessUid, $message, $topicId, $vdsWarning, $syndicateUrl, $uiBgId);
										$stmt = $dbm->doSecuredQuery($sql, $valArr);		
																			
										$newPostId = $dbm->getLastInsertId();
																						
										$FORUM->postedFilesHandler(array('pid'=>$newPostId, 'files'=>$userUploads, 'fileOriginalNames'=>$originalUserUploads));
										
										///////FOLLOW THE TOPIC IF ITS PERMITTED BY THE USER IN THE CHECKBOX/////

										if(isset($_POST["ftopic"]))
											$FORUM->requestTopicFollow($topicId);
											
										elseif(isset($_POST["nptf"]))
											$ENGINE->set_cookie("nptf-".$topicId, true);
																
										///////AWARD EXCAVATOR BADGE/////////
										
										$sql = "SELECT ID, DATEDIFF(NOW(), Max(TIME)) AS DAYS FROM posts WHERE TOPIC_ID = ? HAVING DAYS >= 180 LIMIT 1";
										$valArr = array($topicId);
										$excavator = $dbm->doSecuredQuery($sql, $valArr, 'chain_count')->getSelectCount();
										
										if($excavator){	
																
											//////AWARD excavator///////////
											$badgesAndReputations->badgeAwardFly($sessUid, 'excavator');	
							
										}

									}elseif(!$originalMsgContent)													
										$alertPostEmpty = $contentErr = true;
											
									elseif(!$uok)												
										$uploadErr = $contentErr = '<span class="alert alert-danger">Sorry there was an error uploading your file<br/>'.$error.'</span><br/>';							
										
									

									/////////////////EXECUTE MODIFY IN DB IF IS SET///////////

									if($postModificationId  && $uok && $originalMsgContent){
																						
										////PARSE VDS ONLY IF A FILE IS ASSOCIATED WITH THE POST//////																								
										///////PDO QUERY/////////												
										$sql = "SELECT FILE FROM post_uploads WHERE POST_ID=? LIMIT 1";
										$valArr = array($postModificationId);		
										$postHasFile = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
										
										//////VDS_WARNING////////
										$vdsWarning = (isset($_POST["vds"]) && ($userUploads || $postHasFile))? 1 : 0;
										
										///////AWARD EDITOR BADGE/////////
										
										if(str_word_count($FORUM->getPostDetail($postModificationId, "MESSAGE")) != str_word_count($message))
											
											//////AWARD editor///////////
											$badgesAndReputations->badgeAwardFly($sessUid, 'editor');	
																				
											
										/////THEN MODIFY //////
										
										/////PDO QUERY///////////
										
										$sql = "UPDATE posts SET MESSAGE=?, VDS_WARNING=?, SYNDICATE_URL=?, UIBG_ID=? WHERE ID=? AND TOPIC_ID=? LIMIT 1";
										$valArr = array($message, $vdsWarning, $syndicateUrl, $uiBgId, $postModificationId, $topicId);
										$stmt = $dbm->doSecuredQuery($sql, $valArr);		
										
										/** 
										
											If new files are being uploaded with the post, it will be available in $userUploads otherwise it will be empty 
											and no new file will be uploaded to the database

										**/
										
										$FORUM->postedFilesHandler(array('pid'=>$postModificationId, 'files'=>$userUploads, 'fileOriginalNames'=>$originalUserUploads));
										
										////////FOLLOW THE TOPIC IF ITS PERMITTED BY THE USER IN THE CHECKBOX///

										if(isset($_POST["ftopic"]))													
											$FORUM->requestTopicFollow($topicId);						
											
										elseif(isset($_POST["nptf"]))
											$ENGINE->set_cookie("nptf-".$topicId, true);																								
										
										//////ENCODE THREAD TAGS IF SET
										if(isset($_POST["tags"]))													
											$badgesAndReputations->processTags(array('tags'=>$tags, 'update'=>$topicId));
										
									}
									
									if($originalMsgContent && $uok){
										
										////CLEAR MULTI-QUOTE HIGHLIGHTS (IF ANY) AFTER EVERY POST////

										$FORUM->clearMultiQuoteHighlights($sessUid);
										
										/////////////CHECK IF USERS ARE SPAMMING WITH THEIR POST////////

										$FORUM->spamControl($topicId, $message, (isset($newPostId)? $newPostId : $postModificationId));
										
									}
									
									// If we arrived here then our post transaction was a success, we simply end the transaction
									$dbm->endTransaction();
									
										
									/**
									
										AFTER MODIFYING A POST, REDIRECT TO THE TOPIC AND FOCUS ON THE MODIFIED POST
										AFTER MAKING A NEW POST, REDIRECT TO THE TOPIC AND FOCUS ON THE NEW POST
										REDIRECT AFTER EACH POST TO CLEAR THE $_POST VARIABLES THAT WAS SET TO AVOID DUPS ON PAGE RELOAD
									
									**/
									
									if(!$GLOBAL_isAjax && !isset($contentErr)){
																					
										$postNum = $FORUM->getPostNumber((isset($newPostId)? $newPostId : $postModificationId), $topicId);
																	
										$rdrPageId = $FORUM->getPostPageNumber($postNum);
										
										header("location:".$threadUrl.$rdrPageId."#".$postNum);
										exit();	
										
									}
									
									
								}catch(Throwable $e){
									
									// Rollback if new post transaction fails
									$dbm->cancelTransaction();
									$FORUM->postedFilesHandler(array('unlinkOnly'=>true, 'files'=>$userUploads));
									$alertUser = '<span class="alert alert-danger">Oops! something went wrong! please try again</span>';
									
								}	

							}else{
							
								if(!$GLOBAL_isAjax){
							
									header("Location:/login?_rdr=".$GLOBAL_rdr, true, 302);
									exit();
							
								}
							}

						}


						//////////////////HANDLES FOR TEXT AREA///////////

						////qt_id = message to quote id,  mt_id = message to modify id,////////
						$quote = 'quote_id';
						$mQuote = 'multi_quote';

						if( isset($_GET[$quote]) || isset($_GET[$mQuote]) || isset($postModificationId) ){
							
							$quote_mssg=$mod_topic=$mod_qry=$mod_subcat=				
							$message_in_quote=$message_under_mod="";
							
						
							if(isset($_GET[$quote]))
								$qid = $ENGINE->sanitize_number($_GET[$quote]);
							
								
							if((isset($qid) && $qid) || (isset($postModificationId) && $postModificationId )){
								
								$targetId = (isset($qid))? $qid : $postModificationId;
								
								/////IF A POST IS LOCKED OR HIDDEN THEN DISABLE QUOTE, MULTI-QUOTE AND MODIFY CAPABILITY///////
								$statArr = $FORUM->getPostStatus($targetId);
								$isLockedPost = $statArr["isLocked"];
								$postLocker = $statArr["locker"];
								$isHiddenPost = $statArr["isHidden"];
								$postHider = $statArr["hider"];
																
								list($ranksHigher, $ranksEqual) = $ACCOUNT->sessionRanksHigher($postLocker);
								list($ranksHigher_h, $ranksEqual_h) = $ACCOUNT->sessionRanksHigher($postHider);
								
								if($isLockedPost && !$tops && !$ranksHigher){	
											
									$postLocked = true;
									$alertMods .= '<span class="alert alert-warning align-c">Sorry the referenced post is <b>LOCKED</b><br/> and you do not have enough privilege to Edit, Quote or Multi-Quote it</span>';
									$alertMembers = $hideForm = $alertMods;
							
								}elseif($isLockedPost)									
									$silentModsAlert .= (($GLOBAL_isStaff)? '<span class="alert alert-warning align-c">This post is <b>LOCKED</b>, '.(($GLOBAL_isAdmin)? 'administrator\'s' : 'a moderator\'s').' access privilege granted</span>' : '');									
								
								if($isHiddenPost && !$tops && !$ranksHigher_h){		
															
									$postHidden = true;
									$alertMods .= '<span class="alert alert-warning align-c">Sorry the referenced post is <b>HIDDEN</b><br/> and you do not have enough privilege to Edit, Quote or Multi-Quote it</span>';
									$alertMembers .= $hideForm = $alertMods;	
											
								}elseif($isHiddenPost)									
									$silentModsAlert .= (($GLOBAL_isStaff)? '<span class="alert alert-warning align-c">This post is <b>HIDDEN</b>, '.(($GLOBAL_isAdmin)? 'administrator\'s' : 'a moderator\'s').' access privilege granted</span>' : '');									
								
							}
							
							if(!isset($postLocked) && !isset($postHidden)){
								
								////////////QUOTE A POST////////////
								///////GENERATE THE MESSAGE AND ITS ASSOCIATED CONTENTS IN TEXTAREA FOR POST QUOTE REQUEST///				
								
								if(isset($qid) && $qid){
															
									///////////PDO QUERY/////////////
								
									$sql = "SELECT * FROM posts WHERE ID=? AND TOPIC_ID=? LIMIT 1";
									$valArr = array($qid, $topicId);
									$stmt = $dbm->doSecuredQuery($sql, $valArr);
									$row = $dbm->fetchRow($stmt);		
								
									if(!empty($row)){
										
										$quoteFiltered = $ENGINE->match_nested_regex("#\[quote( .+)?\].*\[/quote\]#sim", "", $row["MESSAGE"]);
										
										/*********ENSURE TO STRIP QUOTES INSIDE A QUOTE BY PREG REPLACE*******************/
										$textContent = '[quote Author='.$ACCOUNT->memberIdToggle($row["POST_AUTHOR_ID"]).' post='.$qid.']'.$quoteFiltered.'[/quote]';
								
									}else
										$topicPostMismatched = true;
									
								}
								
								
								///////////MULTI-QUOTE A POST/////////
								//GENERATE THE MESSAGE AND ITS ASSOCIATED CONTENTS IN TEXTAREA FOR MULTI-QUOTE POST REQUEST////////////
								
								
								if(isset($_GET[$mQuote])){
									
									///////////PDO QUERY//////
									
									$sql = "SELECT p.ID, p.POST_AUTHOR_ID, p.MESSAGE FROM posts p JOIN mq_trackers mq ON p.ID = mq.POST_ID AND mq.USER_ID = ? ".(!ALLOW_XMQ? " AND p.TOPIC_ID = ? " : "")." ORDER BY mq.TIME ASC LIMIT 10";
									$valArr = array($sessUid);
									!ALLOW_XMQ? ($valArr[] = $topicId) : '';												

									$stmt = $dbm->doSecuredQuery($sql, $valArr);																							
															
									while($row = $dbm->fetchRow($stmt)){
										
										$qid = $row["ID"];
										///ENSURE HIDDEN AND LOCKED POSTS ARE NOT MULTI-QUOTED BY UNAUTHORIZED USERS///										
										$statArr = $FORUM->getPostStatus($qid);
										$isLockedPost = $statArr["isLocked"];
										$postLocker = $statArr["locker"];
										$isHiddenPost = $statArr["isHidden"];
										$postHider = $statArr["hider"];										
										
										list($ranksHigher_lm, $ranksEqual_lm) = $ACCOUNT->sessionRanksHigher($postLocker);
										list($ranksHigher_hm, $ranksEqual_hm) = $ACCOUNT->sessionRanksHigher($postHider);
																				
										if(($isLockedPost && !$tops && !$ranksHigher_lm) ||
											($isHiddenPost && !$tops && !$ranksHigher_hm))
											continue;
										
										/*********ENSURE TO STRIP QUOTES INSIDE A QUOTE BY PREG REPLACE***********/
										
										$quoteFiltered = $ENGINE->match_nested_regex("#\[quote( .+)?\].*\[/quote\]#sim", "", $row["MESSAGE"]);
										
										$textContent .= '[quote Author='.$ACCOUNT->memberIdToggle($row["POST_AUTHOR_ID"]).' post='.$qid.']'.$quoteFiltered.'[/quote]';        
											
									}
								}
								

								
								/////////MODIFY A POST//////
								/////GENERATE THE MESSAGE AND ITS ASSOCIATED CONTENTS IN TEXTAREA FOR MODIFY POST REQUEST////
								
								if(isset($postModificationId) && $postModificationId){												
									
									///////////PDO QUERY////////
								
									$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => '', 'stop' => 1, 'postColsOnly' => true, 'uniqueColumns' => '', 'filterCnd' => 'posts.ID=? AND TOPIC_ID=?', 'orderBy' => ''));
									$valArr = array($postModificationId, $topicId);
									$stmt = $dbm->doSecuredQuery($sql, $valArr);
									$row = $dbm->fetchRow($stmt);		
									
									if(!empty($row)){
										
										list($ranksHigher, $ranksEqual) = $ACCOUNT->sessionRanksHigher($postAuthorId=$row["POST_AUTHOR_ID"]);
										
										if($sessUid == $postAuthorId || ($ACCOUNT->sessionAccess(array('id'=>$topicId)) && $ranksHigher)){
												
											$textContent = $row["MESSAGE"];
											$syndicateUrl = $row["SYNDICATE_URL"];
											
											if($uiBgId = $row["UIBG_ID"]){
												
												$uiBgLoader = ' data-uibg-loader="'.$uiBgId.'" ';
												$uiBgRow = $SITE->uiBgHandler('', array('id' => $uiBgId, 'action' => 'getRow'));
												$uiBgStyles = $uiBgRow["BG_STYLES"];
												$uiBgFillCls = '_uibg-ui-fill';
												$uiBgField = '<input type="hidden" name="'.UIBG_ID_FIELD.'" value="'.$uiBgId.'"/>';
												$uiBgContentStyles = $uiBgRow["CONTENT_STYLES"];
												$uiBgRenderStyles = $uiBgStyles.' '.$uiBgContentStyles;
												$uiBgLoadedStyles = $uiBgLoadedStyleMetas = $SITE->uiBgHandler('', array('id' => $uiBgId, 'styles' => $uiBgRenderStyles, 'action' => 'decodeStyle'));
												$uiBgLoadedStyles = 'style="'.$uiBgLoadedStyles.'"';
												$uiBgComposerFormClass = '_uibg-ui _uibg-ui-composer-form';
												$uiBgPreWrap = '<div class="has-back-overlay back-overlay-item-centered content-zoom open-zoom" data-scrollable="true">';
												$uiBgPostWrap = '</div><div class="back-overlay back-overlay-bg"></div>';
												$uiBgCloseBtn = '<div title="close" class="red close-btn _uibg-hibernation-btn">&times;</div>';
								
											}
											
											if($row["VDS_WARNING"])															
												$vdsChk = 'checked="checked"';
											
											if($sessUid == $topicAuthorId || $ACCOUNT->sessionAccess(array('id'=>$topicId)))
												$threadTagUpdateAccess = true;
											
											//////IF THERE ARE FILES TO MODIFY THEN DISPLAY THEM/////
											
											$getfiles="";									
												
											if($row["UPLOADS"]){															
												
												$files = explode(GRPSEP, $row["UPLOADS"]);															
												$file_len = count ($files);
												
												$ii =  0; $c = 1;
												
												while( $ii < $file_len ){
													
													/****ENSURE THERE ARE FILES IN ARRAY********/				
													if(!$files[$ii]){
								
														$ii++;
														continue;
								
													}
													
													$getfiles .= '<div class="inline-block base-l-pad">
																	<b>'.$c.'.</b>&nbsp;&nbsp; 
																	<a class="links file-preview" href="'.$SITE->getDownloadURL($files[$ii], "post").'" data-file="'.$files[$ii].'">file '.$c.'</a>'.
																	$SITE->getBgImg(array('file'=>$mediaRootFav.'delete.png', 'title'=>'Remove this file','ocls'=>'edit-posted-files', 'url'=>'/modify-post-file?post='.$postModificationId.'&file='.$files[$ii].'&_rdr='.$GLOBAL_rdr, 'oAttr'=>'data-file="'.$files[$ii].'" data-mid="'.$postModificationId.'"')).'
																</div>';
												
													$ii++; $c++;
												
												}
												
												$getfiles = '<div class="modify-pf" ><label>'.$FA_file.' Files Uploaded(<b id="post-uploads-count" class="prime">'.($c - 1).'</b>):</label><br/>'.$getfiles.$filePrvwTip.'</div>';
													
											}
										
										
											$uploadedFilesView = $getfiles;
											
											if($sessUid != $row["POST_AUTHOR_ID"])																						
												$silentModsAlert .= ($ACCOUNT->sessionAccess(array('id'=>$topicId))? '<span class="alert alert-warning align-c">you are not the author of this post, '.(($GLOBAL_isAdmin)? 'administrator\'s' : 'moderator\'s').' post modification access privilege granted</span>' : '');												 
											
										
										}else												
											$alertMembers = $alertMods .= $hideForm =  '<span class="alert alert-warning">Sorry! You do not have enough privilege to modify this post'.(!$ranksHigher? ' as the post author outranks you' : '').'</span>';										 
										
																	
											$postModifyIdField = '<input type="hidden" name="edit_id" value="'.$postModificationId.'" />';
									
									}else
										$topicPostMismatched = true;
									
									
								}
							
							}

							
						}	
						
						$tags = !isset($_POST["tags"])? $badgesAndReputations->processTags(array('tags'=>$tags, 'rollBack'=>true)) : $tags;
						
						//////////////COMPOSER AREA///////////														
						
						//ACCUMULATE COMPOSER METAS	
						$metaArr['uiBgPreWrap'] = (isset($uiBgPreWrap)? $uiBgPreWrap : '');
						$metaArr['uiBgComposerFormClass'] = (isset($uiBgComposerFormClass)? $uiBgComposerFormClass : '');
						$metaArr['uiBgCloseBtn'] = $uiBgCloseBtn;
						$metaArr['uiBgField'] = $uiBgField;
						$metaArr['alertMods'] = $silentModsAlert.$alertMods.$alertUser;
						$metaArr['uiBgFillCls'] = (isset($uiBgFillCls)? $uiBgFillCls : '');
						$metaArr['uiBgLoadedStyles'] = (isset($uiBgLoadedStyles)? $uiBgLoadedStyles : '');
						$metaArr['uiBgLoader'] = (isset($uiBgLoader)? $uiBgLoader : '');
						$metaArr['uiBgPostWrap'] = (isset($uiBgPostWrap)? $uiBgPostWrap : '');
						$metaArr['threadTagUpdateAccess'] = isset($threadTagUpdateAccess);
						$metaArr['ftopic'] = $ftopic;
						$metaArr['uploadFilesView'] = $uploadedFilesView;
						$metaArr['postModifyIdField'] = $postModifyIdField;
						$metaArr['postBtnTxt'] = $postBtnTxt;
						$metaArr['isPostEdit'] = isset($isPostEdit)? $isPostEdit : '';
						$metaArr['autofocus'] = $autofocus;
						$metaArr['pageSelf'] = $pageSelf;
						$metaArr['textContent'] = $textContent;
						$metaArr['tags'] = $tags;
						$metaArr['vdsChk'] = $vdsChk;
						$metaArr['fileTooLarge'] = $fileTooLarge;
						$metaArr['uploadErr'] = $uploadErr;
						$metaArr['syndicateUrl'] = isset($syndicateUrl)? $syndicateUrl : '';

						$composerForm = $SITE->getComposer($metaArr);

						if(isset($topicPostMismatched)){
							
							$composerForm = "";
							$topicPostMismatched = '<span class="alert alert-danger">Sorry! an unexpected topic or post mismatch error has occured<br/> Please go back and try again.</span>';
							
						}
						
						///////////DISPLAY THE POSTS/////
			
						list($messages, $pagination, $spamAlert, $pageId, $totalPage, $topicCmsAndStickers) = $FORUM->loadThreadPosts($topicId, "", 10);
						
												
					}else
						$alertUser = $SITE->getMeta('thread-access-decline-msg', array('rs' => $topicRecycled, 'pa' => $protectionAlert))
									.((!$GLOBAL_isAjax)? '<a class="links" href="'.$threadUrl.'">Go Back</a>' : '');
					
					list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget($sid, $cid, $topicId);
					
				}					  
				////////////IF THE TOPIC ID PASSED IS NOT TIED TO ANY TOPIC ALERT TOPIC DOES NOT EXIST////////
				else		   
					$alertUser = '<span class="alert alert-danger"> Sorry that topic was not found</span>';		   		   
				
				  
			}
			///////////IF NO TOPIC ID IS PASSED ALERT PAGE NOT FOUND/////////////
			else		
				$alertUser = '<span class="alert alert-danger">An unexpected error has occurred<br/> We are sorry about this<br/>You must reference a topic before making a post</span>';		
			

		}else{	
	
			if(!$GLOBAL_isAjax){

				header("Location:/login?_rdr=".$GLOBAL_rdr."#loginUsername", true, 302);
				exit();

			}else
				$ajaxLoginErr = $GLOBAL_notLogged;
		}
		
		if($GLOBAL_isAjax){
				
			$ajaxErr .= (!$alertMods? $silentModsAlert : '').$alertMods.$fileTooLarge.$uploadErr.$alertUser;
			
			if(isset($alertPostEmpty))
				$ajaxErr .= '<br/><span class="alert alert-danger">Empty message body</span>';
			
			if(isset($composerForm) && !$topicClosed && !isset($hideForm) && $postAuthorized || 
			($ACCOUNT->sessionAccess(array('id'=>$topicId)) && !isset($hideForm)))  
				$composerForm = $composerForm;
			
			else{
			
				$ajaxHideForm = true;
				$composerForm = '';
				$ajaxErr = '<span class="alert alert-danger">Access Denied</span>'.$ajaxErr;
			
			}
			
			$composerForm = (isset($alertMembers)? $alertMembers : '').$composerForm;
				
			$jsonArr['loadedUiBg'] = $uiBgId;
			$jsonArr['loadedUiBgField'] = $uiBgField;
			$jsonArr['loadedUiBgFillCls'] = $uiBgFillCls;
			$jsonArr['loadedUiBgStyleMetas'] = $uiBgLoadedStyleMetas;
			$jsonArr['loadedUiBgCloseBtn'] = $uiBgCloseBtn;
			$jsonArr['loadedDatas'] = $textContent;
			$jsonArr['htmlForm'] = $composerForm;	
			$jsonArr['postBtnTxt'] = $postBtnTxt;	
			$jsonArr['files'] = $uploadedFilesView;	
			isset($threadTagUpdateAccess)? ($jsonArr['tagsToggleBtn'] = '<button class="btn btn-xs btn-sc" data-toggle="smartToggler" data-id-targets="threadTags">Modify Thread Tags</button>') : '';
			$jsonArr['loadedTags'] = (isset($tags) && isset($threadTagUpdateAccess))? $tags : '';
			$jsonArr['vds'] = (bool)$vdsChk;
			$jsonArr['syndicateUrl'] = isset($syndicateUrl)? $syndicateUrl : '';
			$jsonArr['hideForm'] = isset($ajaxHideForm);
			$jsonArr['errors'] = $ajaxErr;	
			$jsonArr['unloggedUserErrors'] = $ajaxLoginErr;	
			echo json_encode($jsonArr);	
			exit;
			
		}
		
		$subNav="";
		if(isset($categName))
			$subNav = '<li>'.$ENGINE->sanitize_slug($categName, array('ret'=>'url')).'</li>';
			
		if(isset($sectionName))
			$subNav .= '<li>'.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).'</li>';
			
		if(isset($threadUrl) && isset($topicName))
			$subNav .= '<li><a href="'.$threadUrl.'" >'.$topicName.'</a></li>';
			
		$subNav .= (isset($editSubNav))? '<li><a href="/'.$pageSelf.'#'.$composerFieldId.'" >Edit Post</a></li>' : '<li><a href="/'.$pageSelf.'#'.$composerFieldId.'" >New Post</a></li>';


		$SITE->buildPageHtml(array("pageTitle"=>'New Post - '.(isset($topicName)? $topicName : '').' - '.(isset($categName)? $categName : '')." - ". (isset($sectionName)? $sectionName : ''),
						"preBodyMetas"=>$SITE->getNavBreadcrumbs($subNav),
						"pageBody"=>'
						<div class="single-base blend">	
							<div class="base-ctrl">
								<div class="">													
									'.$pageTopAds.'						
								</div>	
								<div class="row">	
									<div class="'.$leftWidgetClass.'">	
										<div class="base-rad base-container">'.
											(isset($topicCmsAndStickers)? $topicCmsAndStickers : '').
											(isset($alertPostEmpty)? '<div class="blink empty-post">EMPTY MESSAGE BODY !!! (<a class="links font-tiny" href="#'.$composerFieldId.'">Go Back</a>)</div>' : '').
											$SITE->getCommunityRules($composerFieldId).(isset($alertMembers)? $alertMembers : '').											
											(isset($topicPostMismatched)? $topicPostMismatched : '').
											((isset($composerForm) && !$topicClosed &&  !isset($hideForm) || 
												($ACCOUNT->sessionAccess(array('id'=>$topicId)) && !isset($hideForm)) 
											)? $composerForm : '<span id="'.$composerFieldId.'"></span>').'
										</div>'.(isset($alertTopicClosed)? $alertTopicClosed : '').(isset($alertUser)? $alertUser : '').
										($spamAlert? '<header>'.$spamAlert.'</header>' : '').										
										((isset($pagination) && $pagination)? '<hr/>' : '').
										(isset($pagination)? $pagination : '').
										((isset($topicFollowLink) && !$topicClosed && $sessUsername)? $topicFollowLink : '').
										((isset($pagination) && $pagination)? '<hr/>' : '').
										(isset($alertTopicClosed)? $alertTopicClosed : '').(isset($messages)? $messages : '').
										((isset($pagination) && $pagination)? '<hr/>' :'').  
										(isset($pagination)? $pagination : '').
										((isset($topicFollowLink) && !$topicClosed && $sessUsername)? $topicFollowLink : '').
										((isset($pagination) && $pagination)? '<hr/>' : '').' 
									</div>
									'.$rightWidget.'
								</div>
								<div class="">													
									'.$pageBottomAds.'						
								</div>
							</div>
						</div>'
		));
						
		break;
			
	}
		
		
		
		
	
	
	/**SHOW TOPIC**/
	case strtolower(THREAD_SLUG_CONSTANT):{

		/////////VARIABLE INITIALIZATION/////
		$html_inside=$page_title=$alertStaffs=$newPostLink=$threadBaseUrl= 
		$postReplyLink=$pageTopAds=$messages=$subNavMob=$tags=$pinnedMsgs=
		$pageError=$pageViewCount=$pageId=$totalPage=$messages=$linkPageId=$plus18=$topicClosedIcon=
		$pagination=$categName=$sectionName=$parentSection=$parentSectionForMob=$threadUrl=$sid=$creatorDetails=
		$spamAlert=$totalPosts=$topicName=$page=$cid=$threadTopHeader=$modStatus=
		$topicHeadStatus=$topicRecycled=$topicLocked=$topicFollowLink=$threadSlugPassed=$viewAuthorized="";
		$loadThread=$loadTagged=$topicClosed=false;
		
		/***************************BEGIN URL CONTROLLER****************************/
		$path2 = isset($pagePathArr[1])? strtolower($pagePathArr[1]) : '';
		$path3 = isset($pagePathArr[2])? strtolower($pagePathArr[2]) : '';
			
		switch($path2){
			
			case 'tagged': $loadTagged = true; break;
			
			default: $loadThread = true;
			
		}
			
		if($loadThread){
			
			switch(strtolower(THREAD_SLUG_STYLE)){
			
				case 'timestamped': $pathKeysArr = array('pageUrl', 'year_created', 'month_created', 'day_created', 'thread', 'thread_slug', 'pageId');
									$timeStampedSlug = true; $slugLen = 6;  $maxPath = 7; break;
			
				default: $pathKeysArr = array('pageUrl', 'thread', 'thread_slug', 'pageId');
						$timeStampedSlug = false; $slugLen = 3;  $maxPath = 4;
			
			}
			
		}else{
			
			if($path3 == 'hash-tagged'){
			
				$pathKeysArr = array('pageUrl', 'tagged_tab', 'hash_tag_tab', 'tag', 'pageId');
				$maxPath = 5;
				$isPostTag = true;
				$term = 'posts';
			
			}elseif($path3){
			
				$pathKeysArr = array('pageUrl', 'tagged_tab',  'tag', 'pageId');
				$maxPath = 4;
				$term = 'topics';
			
			}else{
			
				$pathKeysArr = array();
				$maxPath = 0;
			
			}
		}			

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/			

		///////GET THREAD ID AND PATH/////////////
		$thread = isset($_GET[$K="thread"])? $ENGINE->sanitize_user_input($_GET[$K]) : '';
		$threadSlugPassed = isset($_GET[$K="thread_slug"])? $ENGINE->sanitize_user_input($_GET[$K]) : '';
		$yearCreated = isset($_GET[$K="year_created"])? $ENGINE->sanitize_number($_GET[$K]) : '';
		$monthCreated = isset($_GET[$K="month_created"])? $ENGINE->sanitize_number($_GET[$K]) : '';
		$dayCreated = isset($_GET[$K="day_created"])? $ENGINE->sanitize_number($_GET[$K]) : '';
		$linkPageId = isset($_GET[$K="pageId"])? $_GET[$K] : '';
		
		
		$rdr = $GLOBAL_rdr;
		
		//IF IT'S THREAD////////
		if($loadThread){
			
			///////VERIFY THAT TOPIC ID IS PASSED AND IS ACTUALLY TIED TO A TOPIC///////
			
			/*******SANITIZE THREAD ID*************/
			$topicId = $FORUM->getTopicDetail($ENGINE->sanitize_number($thread), 'ID');
			$threadBaseUrl = $SITE->getThreadSlug($topicId);
			
			if($topicId){
				
				/******FORCE REDIRECT CONTROLLER IF THREAD SLUG IS INCORRECT OR NOT PASSED***********/
				if(!isset($_POST["pagination_jump"]) && $ENGINE->get_page_path('page_url', $slugLen, true) != trim($threadBaseUrl, '/')){
					
					header("Location:".$threadBaseUrl.$linkPageId, true, 301);
					exit;
			
				}


				///////////GET THE TOPIC DETAILS FROM THE ID PASSED//////
				
				///PDO QUERY/////////
				$sql = $SITE->composeQuery(array('type' => 'for_topic', 'loadAll' => true, 'stop' => 1, 'filterCnd' => 'topics.ID = ? '.($timeStampedSlug? ' AND YEAR(topics.TIME) = ? AND MONTH(topics.TIME) = ? AND DAY(topics.TIME) = ? ' : '')));
				
				$valArr = $timeStampedSlug? array($topicId, $yearCreated, $monthCreated, $dayCreated) : array($topicId);
				$stmt = $dbm->doSecuredQuery($sql, $valArr);
				$row = $dbm->fetchRow($stmt);
					
				///MAKE SURE THE TOPIC ID PASSED IS ACTUALLY TIED TO A TOPIC/////
					
				if(!empty($row)){		
					
					///GET AUTHORIZATION///
					list($postAuthorized, $viewAuthorized, $protectionAlert) = $FORUM->authorizeThreadAccess($topicId);
						
					/////FETCH TOPIC DETAILS//////////
				
					list($floatedProtSticker, $unfloatedProtSticker) = $FORUM->getThreadProtectionSticker($row["PROTECTION_LEVEL"]);
										
					$topicStatArr = $FORUM->getThreadStatus($topicId);					
					$topicLocked = $topicStatArr["isLocked"];
					$topicRecycled = $topicStatArr["isRecycled"];
					$modStatus = $topicStatArr["mds"];
					$topicHeadStatus = $topicStatArr["ths"];
					$topicClosed = $topicStatArr["tClosed"];					

					$topicName = $row["TOPIC_NAME"];
					$topicNameSanitized = $ENGINE->sanitize_slug($topicName);
					$topicCreator = $ACCOUNT->memberIdToggle($row["TOPIC_AUTHOR_ID"]);
					$modCloseReason = $row["MOD_REASON"];
					$tags = $row["TAGS"];
					$tags? ($tags = '<div class="base-mpad">'.$FA_tags.' <b class="prime">Tags:</b> '.$badgesAndReputations->processTags(array('tags'=>$tags, 'decode'=>true)).'</div>') : '';
					$dateCreated = $ENGINE->time_ago($row["TIME"]);
					$creatorDetails = $viewAuthorized? '<div class="green base-l-pad">By: '.$ACCOUNT->sanitizeUserSlug($topicCreator, array('anchor'=>true, 'gender'=>true, 'cls'=>' sv-txt-case')).' '.$ENGINE->build_lavatar($topicCreator, '-10px').' '.$dateCreated.'</div>' : "";
					
					$sid = $row["SECTION_ID"];		
					$sectionName = $row["SECTION_NAME"];					
					$cid = $row["CATEG_ID"];
					$categName = $row["CATEG_NAME"];

					$cidInTitle = $GLOBAL_isStaff? ' - '.$cid : '';
					$sidInTitle = $GLOBAL_isStaff? ' - '.$sid : '';
					
					////CHECK FOR PARENT SCAT////////
				
					///////PDO QUERY/////
				
					$sql = "SELECT PARENT_SECTION FROM sections WHERE ID=? LIMIT 1";
					
					$valArr = array($sid);	
							
					$parentSid = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
					
					if($parentSid == ADULT_SID || $sid == ADULT_SID){
			
						$plus18 = ' <b title="Topics in this sections contains adult materials suitable for only 18years and above" class="red">18+</b> ';
						$SITE->adultContentsViewPrompt($rdr, $topicId);
			
					}
					
					$parentSection = $parentSid? $SITE->sectionIdToggle($parentSid) : "";
					
					if($parentSection){
						
						$parentSidInTitle = $GLOBAL_isStaff? ' - '.$parentSid : '';
						$parentSectionForMob = '<li>'.$ENGINE->sanitize_slug($parentSection, array('ret'=>'url', 'urlText'=>$ENGINE->cloak($parentSection, '30:20', '-3', '.'), 'urlAttr'=>'title="'.($sectionDesc=$FORUM->getSectionDescription($parentSection)).$parentSidInTitle.'"')).'</li>';
						$parentSection = '<li>'.$ENGINE->sanitize_slug($parentSection, array('ret'=>'url', 'urlAttr'=>'title="'.$sectionDesc.$parentSidInTitle.'"')).'</li>';			
			
					}
						
					/////VERIFY ACCESS TO THREAD//////
					
					if($viewAuthorized){
								
						if($topicClosed)		
							$topicClosedIcon = $FORUM->getThreadClosedIcon();
					
						///CHECK IF U ARE FOLLOWING THIS TOPIC TO GET OPTION TO UNFOLLOW///////
				
						$topicFollowCheckArr = $FORUM->requestTopicFollowLink($topicId);
						
						if(isset($topicFollowCheckArr[$K="fnuLink"]))
							$topicFollowLink = $topicFollowCheckArr[$K];
						
						if(isset($topicFollowCheckArr[$K="ftopicCheckBox"]))
							$ftopic = $topicFollowCheckArr[$K];	
							
				
						///GET THE TOTAL POST IN TOPIC///////
						$totalPosts = $FORUM->countThreadPosts($topicId, $row["TOTAL_POSTS"]);
					
						////////PROCESS PAGE VIEW COUNT/////////
						$pageViewCount = $FORUM->countThreadViews($topicId, $row["TOPIC_VIEWS"]);
			  
						$newPostLink = "post";

						$threadUrl = $topicId;
																
						//////////////DISPLAY THE POSTS///////////////

						list($messages, $pagination, $spamAlert, $pageId, $totalPage, $topicCmsAndStickers) = $FORUM->loadThreadPosts($topicId, '', '', "reply-box-handle");												
						
						$pinnedMsgs = $FORUM->loadPinnedPosts($topicId);
							  
					}else{
						  					
						$pageError = $SITE->getMeta('thread-access-decline-msg', array('rs' => $topicRecycled, 'pa' => $protectionAlert));

					}					
					
				}
				///////IF THE TOPIC ID PASSED IS NOT TIED TO ANY TOPIC ALERT TOPIC DOES NOT EXIST//////////////
				else	   
					$pageError = $notFound = '<span class="alert alert-danger">Sorry! that topic was not found</span>'; 	   	   
	
			   
			}
			///////////IF NO TOPIC ID IS PASSED ALERT PAGE NOT FOUND////
			else
				$pageError = $notFound = '<span class="alert alert-danger">Sorry! that topic was not found</span>';	
			

			list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget($sid, $cid, $topicId);

			$online=$subNav=""; $socMetasArr=array();
			
			$alertStaffs = isset($topicCmsAndStickers)? $topicCmsAndStickers : ''; 	

			$ajaxLoginErr = $sessUsername? '' : '/login?_rdr='.$rdr;

			if((!$topicClosed  || $GLOBAL_isStaff) && !$pageError)
				$postReplyLink = '<li class="active preloader-dpn"><a href="/'.$newPostLink.'/'.$topicId.'"   class="links reply-box-handle" data-lrdr="'.$ajaxLoginErr.'">'.$FA_reply.' Send a Post/Reply</a></li>
										<a hidden href="/'.$newPostLink.'/'.$topicId.'" class="links load-reply-box">Send a Post/Reply</a>';
			
							
			((!$topicClosed && !$pageError && $sessUsername && $viewAuthorized) || $GLOBAL_isAdmin)? '' : ($topicFollowLink = ''); 
				
			$modCloseReason = ($topicClosed && $modCloseReason)? '<div class="alert alert-warning">'.ucwords($modCloseReason).' - <b class="text-sticker text-sticker-danger text-sticker-sm" title="This thread has been closed">CLOSED</b> </div>' : '';

			if($topicName){
				
				$topicHeaderStickers = $topicHeadStatus? ' <span class="red">['.$topicHeadStatus.']</span>' : '';
				
				$postViewCount = ($totalPosts || $pageViewCount);
				$socMetasArr = $row;
				$socMetasArr["type"] = 'topic';
				$socMetasArr["tid"] = $topicId;
				$socMetasArr["tname"] = $topicName;
				$socMetasArr["tslug"] = $threadBaseUrl;
				$socMetasArr["rdr"] = $GLOBAL_rdr;
				
				$threadTopHeader = '<div class="topic-head ">
										<span class="text-capitalize">'.$topicName.'</span>'.$plus18.$topicHeaderStickers.'  <br/><div class="font-tiny prime">'.(($viewAuthorized && $postViewCount)? '('.$totalPosts.' & '.$pageViewCount.')' : "").'
										<br/> '.($pageId? '(page <span class="cyan">'.$pageId. '</span> of '.$totalPage.')' : "").'</div>
										</div>
										<div class="row" >
											<div class="col-lg-w-5-pull" >'.$creatorDetails.$tags.$modCloseReason.'</div>
											<div class="col-lg-w-5-pull" >'.$floatedProtSticker.((($viewAuthorized && !$topicClosed ) || $GLOBAL_isStaff)? $SITE->getSocialLinks($socMetasArr) : '').'</div>
										</div>';
											
				$subNav = '<li>'.$ENGINE->sanitize_slug($categName, array('ret'=>'url', 'urlAttr'=>'title="'.($categDesc=$FORUM->getCategoryDescription($cid)).$cidInTitle.'"')).'</li>
							'.$parentSection.'
							<li>'.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url', 'urlAttr'=>'title="'.($sectionDesc=$FORUM->getSectionDescription($sid)).$sidInTitle.'"')).'</li>
							<li><a href="'.$threadBaseUrl.'" >'.$topicName.'</a>'.$plus18.'</li>';
								
				$subNavMob = '<li>'.$ENGINE->sanitize_slug($categName, array('ret'=>'url', 'urlText'=>$ENGINE->cloak($categName, '30:20', '-3', '.'), 'urlAttr'=>'title="'.$categDesc.$cidInTitle.'"')).'</li>
							'.$parentSectionForMob.'
							<li>'.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url', 'urlText'=>$ENGINE->cloak($sectionName, '30:20', '-3', '.'), 'urlAttr'=>'title="'.$sectionDesc.$sidInTitle.'"')).'</li>
							<li><a href="'.$threadBaseUrl.'" >'.$ENGINE->cloak($topicName, '30:20', '-3', '.').'</a>'.$plus18.'</li>';
				

			}

			if($pagination || $topicClosedIcon)
				$pagination = '<hr/>'.$pagination.$topicClosedIcon.'<hr/>';
					
			if(!$pageError)
				$SITE->collectSiteTraffic(($slugLen - 1), $topicId);

			$online = $SITE->displaySiteTraffic("Topic");

			list($relatedTopics, $relatedTopicsMob) = $FORUM->relatedTopics($sid, $topicId, $forceMob=true, $forceInline=true);
			
			$pageTitle = (!isset($notFound)? $topicName.' - '.$categName.' - '.$sectionName : 'Thread Not Found');
								
			$pageBody =	'<div class="single-base blend kbp bg-img2">													
								'.$threadTopHeader.$spamAlert.'
								<div class="align-c mob-platform-dpn">
									'.$relatedTopicsMob.'					
								</div>
							<div class="align-c">													
								'.$pageTopAds.'
							</div>					
							'.$alertStaffs.'
							<div class="row">
								<div class="'.$leftWidgetClass.'">
									<div class="align-c default-base">
									'.($K='<nav class="nav-base base-xs"><ul class="nav nav-pills justified-center">'.							
										$topicFollowLink.
										$postReplyLink.'
									</ul></nav>').
										$pagination.
										$pinnedMsgs.
										$messages.
										$pageError.
										$pagination.'						
									</div>
									
									<div class="align-c">'.
										$K.$spamAlert.'
										<div hidden style="padding-top:50px;" class="base-pad relative base-border '.COMPOSER_WRAPPER_CLASS.'" tabindex="-1" id="reply-box"></div>
									</div>
									<div class="align-c">								
										'.($viewAuthorized? $online : "").'						
									</div>
								</div>
								'.$rightWidget.'
							</div>
							<div class="">													
								'.$pageBottomAds.'						
							</div>						
						</div>';
			$ogMetas_arr = array('url'=>$siteDomain.$threadBaseUrl, 'desc'=>$FORUM->getOgDescription($topicId));
			$parseHtmlArr = array("ogMetas"=>$ogMetas_arr, "pageTitle"=>$pageTitle, "preBodyMetas"=>$SITE->getNavBreadcrumbs($subNav, $subNavMob), "pageBody"=>$pageBody);
			
			////LOAD TAGGED////
		}else{
			
			$tag = isset($_GET[$K="tag"])? urldecode($_GET[$K]) : '';	
						
			if(isset($isPostTag))
				$findTagRegx = '%#'.$tag.'%';
				
			else{
				
				$tagId = $badgesAndReputations->getBadgeDetail($tag, 'ID');
				!$tagId? ($tagId = $tag) : '';
				$findTagRegx = '%'.DB_GPREF.$tagId.DB_GSUF.'%';
				
			}
														
			///////////PDO QUERY/////
				
			$sql = isset($isPostTag)? $SITE->composeQuery(array('type' => 'for_post', 'subType' => 'record_count', 'filterCnd' => 'MESSAGE LIKE ?', 'exceptions' => true))
					: $SITE->composeQuery(array('type' => 'for_topic', 'subType' => 'record_count', 'filterCnd' => 'TAGS LIKE ?', 'exceptions' => true));

			$valArr = array($findTagRegx);
			$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();

			/**********CREATE THE PAGINATION***********/			
			$pageUrl = $ENGINE->get_page_path('page_url',isset($isPostTag)? 4 : 3);			
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'perPage'=>20,'hash'=>'ptab'));					
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$page = $paginationArr["pageId"];

			//////END OF PAGINATION//////////////
						
			$customTotalRecords = $totalRecords? '<div class="cpop"> (page <span class="cyan"> '.$page.'</span> of '.$totalPage.')</div>' : '';
			

			//////////////IF THERE ARE TOPICS THEN POPULATE THE  TOPICS USING THE PAGINATION/////////

			if($totalRecords){

				///////////PDO QUERY////
				if(isset($isPostTag)){
					
					$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'uniqueColumns' => '', 'filterCnd' => 'MESSAGE LIKE ?', 'orderBy' => 'TIME DESC'));
		
					list($messages) = $FORUM->loadPosts($sql, array($findTagRegx));
					
				}else{

					$sql = $SITE->composeQuery(array('type' => 'for_topic', 'start' => $startIndex, 'stop' => $perPage, 'uniqueColumns' => '', 'filterCnd' => 'TAGS LIKE ?', 'orderBy' => 'LAST_POST_TIME DESC', 'exceptions' => true));

					list($messages) = $FORUM->loadThreads($sql, array($findTagRegx), $type="");
				
				}	

			}else				
				$messages = '<span class="alert alert-danger">Sorry there are no '.$term.' or discussion associated with that tag yet</span>';				

			list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
				
			$title = ucwords($term.' Tagged '.$tag);
			
			$preIcon = $SITE->getFA('fas fa-tag', array("title"=>$title));
			
			$parseHtmlArr = (array("pageTitle"=>$title,
							"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">'.$title.'</a></li>'),
							"pageBody"=>'
								<div class="single-base blend">		
									<div class="base-ctrl base-b-pad">
										<h1 class="page-title pan">'.$preIcon.$title.$preIcon.'</h1>'.						
										$pageTopAds.'											
										<div class="row base-container">
											<div class="'.$leftWidgetClass.'">'.						
												(isset($pagination)? $customTotalRecords : '').(isset($pagination)? $pagination : '').'													
												<ul class="topic-base">'.(isset($messages)? $messages : '').'</ul>'.
												(isset($pagination)? $pagination : '').'
											</div>'.$rightWidget.'											
										</div>
										<div class="">
											'.$pageBottomAds.$SITE->collectSiteTraffic().$SITE->displaySiteTraffic("Page").'											
										</div>	
									</div>	
								</div>'
				));
				
		}		
						
		$SITE->buildPageHtml($parseHtmlArr);

		break;
				
	}
	
	
	
	
	
	/**ELITE TOPS(FRONT PAGE TOPICS)**/
	case 'elite-tops':{

		$messages="";
								
		/***************************BEGIN URL CONTROLLER****************************/

		if(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "topics"){
				
			$pathKeysArr = array('pageUrl', 'topics', 'pageId');				
			$topicTab = true;
			$maxPath = 3;	
				
		}elseif(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "posts"){
		
			$pathKeysArr = array('pageUrl', 'posts', 'pageId');					
			$postTab = true;
			$maxPath = 3;	
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;
		
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		if(isset($topicTab)){
			
			$eliteTopicsArr = $FORUM->getEliteTopics();
			
			if(isset($eliteTopicsArr[$K="topics"]))
				$topics = $eliteTopicsArr[$K];
		
			if(isset($eliteTopicsArr[$K="pagination"]))
				$pagination_topics = $eliteTopicsArr[$K];
		
			if(isset($eliteTopicsArr[$K="pageId"]))
				$pageId = $eliteTopicsArr[$K];
		
			if(isset($eliteTopicsArr[$K="totalPage"]))
				$totalPage = $eliteTopicsArr[$K];
			
		}elseif(isset($postTab)){
			
			$sql = "SELECT COUNT(*) FROM topics WHERE FEATURE_TIME !=0 ";
			$valArr = array();
			$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
			
			/**********CREATE THE PAGINATION*************/		
			$pageUrl = 'elite-tops/posts';				
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl));						
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageId = $paginationArr["pageId"];
			
			if($totalRecords){

				///////////PDO QUERY///////
				
				$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'primaryTable' => 'topics', 'primaryJoinCnd' => 'topics.ID = posts.TOPIC_ID',
				'uniqueColumns' => '', 'filterCnd' => 'FEATURE_TIME !=0', 'orderBy' => 'FEATURE_TIME DESC'));
				

				///////////DISPLAY THE POSTS/////
				list($messages) = $FORUM->loadPosts($sql, array());
			}
			else
				$messages = '<span class="alert alert-danger">Sorry there are no Elite Posts Yet.</span>';
			
		}

		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(HOMEPAGE_SID);
		
		$hr = (isset($pagination_topics) &&$pagination_topics)? '<hr/>' : '';
		
		$subNav='';
		
		if(isset($topicTab))
			$subNav = '<a href="/'.$pageSelf.'">Elite Topics</a>';
		
		elseif(isset($postTab))
			$subNav = '<a href="/'.$pageSelf.'">Elite Posts</a>';

		$SITE->buildPageHtml(array("pageTitle"=>'Elite Tops',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li>'.$subNav.'</li>'),
					"pageBody"=>'								
						<div class="single-base blend">
							<div class="base-ctrl base-rad base-container">
								<div class="">'.
									$pageTopAds.' 
									<nav class="nav-base"><ul class="nav nav-tabs justified justified-center" data-open-active-mob="true"><li class="'.(isset($postTab)? 'active' : '').'"><a  href="/elite-tops/posts" class="links">Elite Posts</a></li><li class="'.(isset($topicTab)? 'active' : '').'"><a href="/elite-tops/topics" class="links">Elite Topics</a></li></ul></nav>
								</div>
								<div class="row">
									<div class="'.$leftWidgetClass.' base-rad">'.
										(isset($postTab)? '
											<div id="p">																																						
												<h1 class="page-title pan bg-limex">Elite Posts:</h1>
												<hr class="no-tm" />
												<h2>'.(isset($pagination)? '(page <span class="cyan">'.$pageId.'</span>  of '.$totalPage.')' : '').'</h2>'.														
												(isset($pagination)? $pagination : '').(isset($messages)? $messages : '').
												(isset($pagination)? $pagination : '').'
											</div>'				
										: (isset($topicTab)? '														
											<div class="" id="t">													
												<h1 class="page-title bg-limex">'.$SITE->getBgImg(array('file'=>$mediaRootFav.'star_c.png')).'Elite Topics:</h1>
												<hr class="no-tm" />
												<h2>'.(isset($pagination_topics)? '(page <span class="cyan">'.$pageId.'</span>  of '.$totalPage.')' : '').'</h2>'.
												(isset($pagination_topics)?  $hr.$pagination_topics : '').'
											</div>															
											<ul class="topic-base">'.(isset($topics)? $topics : '').'</ul>'.																												
											(isset($pagination_topics)?  $pagination_topics : '') : '')							
										).'
									</div>					
									'.$rightWidget.' 																						
								</div>
								<div class="">
									'.$pageBottomAds.$SITE->collectSiteTraffic().$SITE->displaySiteTraffic("Page").'											
								</div>				
							</div>
						</div>'
		));
						
		break;
		
	}
	
	
	
	
	
	
	
	
	
	/**SIGNUP**/
	case 'signup':{	
		
		$ACCOUNT->handleUserAccountRegistrationRequest();
	
		break;

				
	}
	
	
	
	
	/**ACTIVATE ACCOUNT**/
	case 'activate-account':{			
		
		$ACCOUNT->handleUserAccountActivationRequest();
	
		break;

				
	}
	
	
	
	
	
	
	
	/**LOAD PROFILE**/
	case 'load-profile':{
	
		$ACCOUNT->handleUserProfileLoadRequest();
	
		break;

				
	}
	
	
	
	
	
	
	
	/**EDIT PROFILE**/
	case 'edit-profile':{
		
		$ACCOUNT->handleUserProfileEditRequest();		
		
		break;
		
	}
	
	
	
	
	
	
	/**CHANGE EMAIL**/
	case 'change-email':{
	
		$ACCOUNT->handleUserEmailEditRequest();		
		
		break;
		
	}
	
	
	
	
	
	
	/**CHANGE PASSWORD**/
	case 'changepassword':{
		
		$ACCOUNT->handleUserPasswordEditRequest();	
		
		break;
		
	}
	
	
	
		
		
		
	/**FORGOT PASSWORD**/
	case 'forgotpassword':{
		
		$ACCOUNT->handleUserPasswordForgotRequest();	
		
		break;
		
	}
	
		
		
		


	
		
	/**CANCEL ACCOUNT**/
	case 'cancelaccount':{
		
		$ACCOUNT->handleUserAccountCancelRequest();	
		
		break;
		
	}
	
	
	
	
	
	
	/**BADGES**/
	case 'badges':{
		
		$pagination=$data=$pageCounter=$sortNav=$subNav='';
		
		$headTitle = 'Badges';
		
		/***************************BEGIN URL CONTROLLER****************************/
					
		$path2 = isset($pagePathArr[1])? strtolower($pagePathArr[1]) : '';
		$path3 = isset($pagePathArr[2])? strtolower($pagePathArr[2]) : '';
		//$path4 = isset($pagePathArr[3])? $ENGINE->sanitize_number($pagePathArr[3]) : '';
		$page_self_srt = $ENGINE->get_page_path('page_url', 1);
		
		if($path2 != 'awardees' && in_array($path2, array('general', 'tags', ''))){
										
			$pathKeysArr = array('pageUrl', 'sort', 'sort2', 'pageId');
			$maxPath = 4;
		
		}elseif($path2 == 'awardees' && $path3){	
									
			$pathKeysArr = array('pageUrl', 'awardees', 'bid', 'pageId');
			$maxPath = 4; $awardeesTab = true;
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;
		
		}
		
		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/			
		
		///////GET THE URL DATAS/////////////////
							
		$getPage = isset($_GET[$K="pageId"])? $_GET[$K] : '';								
		$xuid = isset($_GET[$K="xuser"])? $ACCOUNT->memberIdToggle($_GET[$K], true) : '';				
		$bid = isset($_GET[$K="bid"])? $ENGINE->sanitize_number($_GET[$K]) : '';				
		$sort = isset($_GET[$K="sort"])? $ENGINE->sanitize_user_input($_GET[$K], array('lowercase' => true)) : '';				
		$sort2 = isset($_GET[$K="sort2"])? $ENGINE->sanitize_user_input($_GET[$K], array('lowercase' => true)) : '';				
					
		if(!isset($awardeesTab)){
		
			list($sortNav, $cid, $clid, $sort, $sort2) = $badgesAndReputations->getBadgeSortNav(array('s1'=>$sort, 's2'=>$sort2, 'pid'=>$getPage, 'baseUrl'=>$page_self_srt));							
			$badgeCountMetas = array('clid'=>$clid, 'cid'=>$cid);
			$pageUrl = 'badges';							
			$qstrValArr = array($sort, $sort2);
			
		}else{	
		
			$badgeName = $badgesAndReputations->getBadgeDetail($bid, 'BADGE_NAME');
			$subNav = '<li><a href="/'.$pageSelf.'">'.$badgeName.' - Awardees</a></li>';
			$badgeCountMetas = array('uwsb'=>true, 'bid'=>$bid, 'distinct'=>true, 'xuid'=>$xuid);						
			$pageUrl = 'badges/awardees/'.$bid;
			$qstrValArr = array();
			$headTitle .= ' - '.$badgeName;
		
		}
		
		
		$totalRecords = $total_badges = $badgesAndReputations->getBadgeCount($badgeCountMetas);
		
		/**********CREATE THE PAGINATION*************/																						
		$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'qstrVal'=>$qstrValArr,'perPage'=>50,'hash'=>'ptab'));				
		$pagination = $paginationArr["pagination"];
		$totalPage = $paginationArr["totalPage"];
		$n = $paginationArr["perPage"];
		$i = $paginationArr["startIndex"];
		$pageId = $paginationArr["pageId"];
		
		$data = isset($awardeesTab)?  $badgesAndReputations->getBadgeAwardees(array('bid'=>$bid, 'xuid'=>$xuid, 'i'=>$i, 'n'=>$n))
				: $badgesAndReputations->loadBadges(array('cid'=>$cid, 'clid'=>$clid, 'i'=>$i, 'n'=>$n));
					
										
		$data = isset($awardeesTab)? $data : '<ol class="align-l no-list-type hr-dividers">'.$data.'</ol>';			
		
		if($totalPage)
			$pageCounter = '<div class="cpop">(page <span class="cyan">'.$pageId.'</span> of '.$totalPage.')</div>'.
							(!isset($awardeesTab)? '<h3 class="prime">'.$total_badges.' Badge(s)</h3>' :'');
					
		$SITE->buildPageHtml(array("pageTitle"=>$headTitle,
						"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/badges" title="">Badges</a></li>'.$subNav),
						"pageBody"=>'				
						<div class="single-base blend">
							<div class="base-ctrl">
								<div class="panel panel-limex">
									<h1 class="panel-head page-title">'.$headTitle.'</h1>
									'.$pageCounter.'
									<div class="panel-body sides-padless">
										'.$pagination.$sortNav.$data.$pagination.
									'</div>
								</div>
							</div>
						</div>'
		));
		 
		break;
		
	}
	
	
	
	
	/**INBOX**/
	case 'inbox':{
		
		$SITE->pmHandler(array('type'=>'inbox'));
		break;
		
	}
							
	
	/**OLD INBOX**/
	case 'old-inbox':{
		
		$SITE->pmHandler(array('type'=>'old-inbox'));
		break;
		
	}					
	
	/**SENT PM**/
	case 'sent-pm':{
		
		$SITE->pmHandler(array('type'=>'sent-pm'));
		break;
		
	}
	
	
	
	
	
	/**REPORT A POST**/
	case 'report':{
					
		$alertUser=$notLogged=$his_her=$found=$postAuthorized=$canFlagPost=$username=$tid=$postNum=
		$postId=$rdrPageId=$returnUrl=$isSess="";

		$maxRC = 100; $minRC = 15;			

		/***************************BEGIN URL CONTROLLER****************************/

		if(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "post"){
		
			$pathKeysArr = array('pageUrl', 'post', 'postId');
			$maxPath = 3;
			
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/


		if(isset($_GET[$K="postId"]))	
			$postId = $ENGINE->sanitize_number($_GET[$K]);
		
		if(isset($_GET[$K="rdrPageId"]))	
			$rdrPageId = $ENGINE->sanitize_number($_GET[$K]);
			
		if(isset($_POST[$K="post"]))	
			$postId = $_POST[$K];


		//////////GET FORM-GATE RESPONSE//////////	
		//$alertUser = $SITE->formGateRefreshResponse();						


		if($postId){
			
			///////GET REPORT DETAILS VIA THE PASSED POST ID ///////


			///////////PDO QUERY///////
			
			$sql = "SELECT *, (SELECT COUNT(*) FROM reported_posts WHERE reported_posts.POST_ID=posts.ID) AS FLAGS FROM posts WHERE ID = ? LIMIT 1";
			$valArr = array($postId);
			$stmt = $dbm->doSecuredQuery($sql, $valArr);
			$row = $dbm->fetchRow($stmt);
			$foundRes = !empty($row);
			
			if($foundRes){
								
				$tid = $row["TOPIC_ID"];
				$postFlags = $row["FLAGS"];
				list($postLocked, $PLU) = $FORUM->decodeModerationStatus($row["LOCKED"]);
				
				list($postAuthorized, $viewAuthorized, $protectionAlert) = $FORUM->authorizeThreadAccess($tid);
				
				if($postAuthorized){
					
					$canFlagPost = $FORUM->authorizeModeration(FLAG_POST);		
					
					if($canFlagPost){
						
						$postNum = $FORUM->getPostNumber($postId, $tid);		
						$rdrPageId = $FORUM->getPostPageNumber($postNum);
						$uid = $row["POST_AUTHOR_ID"];
						$username = $ACCOUNT->memberIdToggle($uid);
						$usernameSlug = $ACCOUNT->sanitizeUserSlug($username);
						$isSess = (strtolower($sessUsername) == strtolower($username));
		
						$his_her = $ACCOUNT->getGender($username, array('ret'=>'pronoun'));
						
						//////IF REPORT IS SUBMITTED THEN PROCEED AS FOLLOWS////////

						if(isset($_POST['report'])){
		
							//CHECK IF THE TOPIC ID PASSED FOR THE REPORTED POST IS VALID//////

							//PDO QUERY//////
							
							$sql = "SELECT TOPIC_NAME,SECTION_ID FROM topics WHERE ID=? LIMIT 1";
							$valArr = array($tid);
							$stmt = $dbm->doSecuredQuery($sql, $valArr);
							$row = $dbm->fetchRow($stmt);																																							
																			
							$returnUrl = $SITE->getThreadSlug($tid).$rdrPageId.'#'.$postNum;												

							if(!empty($row)){
																			
								$topicName = $row["TOPIC_NAME"];												
								$sid = $row["SECTION_ID"];			
								$sectionName = $SITE->sectionIdToggle($sid);
											
								if(!$postLocked){
									
									if($postFlags != MAX_POST_FLAGS){
										
										$reportContent = $ENGINE->sanitize_user_input($_POST["report_content"]);
										$flagRaised = $ENGINE->sanitize_user_input($_POST["flag"]);
										

										if($sessUsername){

											if($reportContent){
												
												if(mb_strlen($ENGINE->filter_line_chars($reportContent)) >= $minRC && mb_strlen($ENGINE->filter_line_chars($reportContent)) <= $maxRC){

													///////CHECK AND AVOID DUPS////

													///////////PDO QUERY////////
												
													$sql = "SELECT ID FROM reported_posts WHERE POST_ID=? AND REPORTER_ID=? LIMIT 1";
													$valArr = array($postId, $sessUid);
														
													if(!$dbm->doSecuredQuery($sql, $valArr)->fetchColumn()){
														
														////////////PUT THE FILED REPORT INTO DB/////////
														
														///////////PDO QUERY//////
											
														$sql = "INSERT INTO reported_posts (REPORTER_ID, REPORT_DETAILS, POST_ID, FLAG_RAISED, TIME ) 
																	VALUES(?,?,?,?,NOW())";
												
														$valArr = array($sessUid, $reportContent, $postId, $flagRaised);
														$stmt = $dbm->doSecuredQuery($sql, $valArr);
																											
															
														/***GET ALL MODERATORS IN THE SECTION WHERE THE REPORT WAS FILED AND ALERT THEM
																	VIA EMAIL & PM ***/
																	
														list($modsEmails, $modsIds) = $SITE->moderatedSectionCategoryHandler(array('scId'=>$sid, 'level'=>1, 'action'=>'get', 'retType'=>'array-all'));																			
															 
														//////SEND EMAIL TO ALL CONCERNED MODERATORS///

														$to = $modsEmails;

														$subject = 'New Report Filed Against '.$username.' [To All '.$sectionName.' moderators@'.$siteName.']';
																
														$message = '<a href="'.$siteDomain.'/'.$sessUsernameSlug.'">'.$sessUsername.'</a> has filed a report with <b '.EMS_PH_PRE.'RED>flag: '.$flagRaised.'</b> against <a href="'.$siteDomain.'/'.$usernameSlug.'">'.$username.'</a> for making the post referenced below \n <a href="'.$siteDomain.$returnUrl.'">'.$topicName.'</a>\n\nThis report was filed in a topic under the <a href="'.$siteDomain.'/'.$ENGINE->sanitize_slug($sectionName).'">'.$sectionName.'</a> Sections where you moderate\n Please moderators of this section should review the report and take the necessary disciplinary measures/actions.\n\n Thank You.\n\n\n\n';
																	
														$footer = 'NOTE: This email was sent to you because you are either an administrator or a moderator at <a href="'.$siteDomain.'">'.$siteDomain.'</a> \nPlease kindly ignore this email if otherwise.';
																 
														$SITE->sendMail(array('to'=>$to, 'senderName'=>'Webmaster & '.$sessUsername, 'subject'=>$subject, 'body'=>$message, 'footer'=>$footer));
														
														////////////SEND PM TO ALL CONCERNED MODERATORS/////

														///////////FORMAT LINKS IN PM////////
										
														$pmEncodedSessUsername = '[a '.$sessUsername.']'.$sessUsername.'[/a]';											
														$pmEncodedUsername = '[a '.$username.']'.$username.'[/a]';
														$pmEncodedSectionName = '[a '.$ENGINE->sanitize_slug($sectionName).']'.$sectionName.'[/a]';
														$pmEncodedPost = '[a '.$returnUrl.']this post[/a]';

														$subject = 'New Report Filed Against '.$pmEncodedUsername.' [To All '.$pmEncodedSectionName.' moderators@'.$siteName.']';

														$message = $pmEncodedSessUsername.' has filed a report with [col=#FF0000]flag: '.$flagRaised.'[/col] against '.$pmEncodedUsername.' for making '.$pmEncodedPost.'. This report was filed in a topic under the '.$pmEncodedSectionName.' Section where you administrate or moderate. Please administrators and moderators of this section should review the report and take the necessary disciplinary actions/measures.<br/> Thank You.' ;								
																				 
														$modsIds_arr = explode(",", $modsIds);
														
														//sender => "Webmaster"
														$senderId = 0;

														foreach($modsIds_arr as $modId){					
															
															$SITE->sendPm($senderId, $modId, $subject, $message);
															
														}
														
														///////SEND AN APPRECIATION EMAIL AND PM TO THE USER THAT FILED THE REPORT/////

														$U = $ACCOUNT->loadUser($sessUsername);
														

														////////SEND APPRECIATION EMAIL/////

														$to = $U->getEmail();

														$subject = 'Appreciation Notes';
																
														$message = '<a href="'.$siteDomain.'/'.$sessUsername.'">'.$sessUsername.'</a>\n We sincerely appreciate you for taking out time to file a report with <b '.EMS_PH_PRE.'RED>flag: '.$flagRaised.'</b> against <a href="'.$siteDomain.'/'.$usernameSlug.'">'.$username.'</a> whom we believe has exhibited some actions that is against the <a href="'.$siteDomain.'">'.$siteDomain.'</a> rules.\n This act of yours will definitely enable us serve you better.\n\n All moderators in the section where the report was filed has been notified. They will review it and take necessary disciplinary actions/measures against <a href="'.$siteDomain.'/'.$usernameSlug.'">'.$username.'</a> for making <a href="'.$siteDomain.$returnUrl.'">this post</a>.\n\n\n\n';
																	
														$footer = 'NOTE: This email was sent to you because a report was filed at <a href="'.$siteDomain.'">'.$siteDomain.'</a> using an account registered to this email \nPlease kindly ignore this email if otherwise.';
																 
														$SITE->sendMail(array('to'=>$to.'::'.$U->getFirstName(), 'subject'=>$subject, 'body'=>$message, 'footer'=>$footer));
														
														////SEND APPRECIATION PM//////

														$subject = 'Appreciation Notes';

														$message = $pmEncodedSessUsername.', We sincerely appreciate you for taking out time to file a report with [col=#FF0000]flag: '.$flagRaised.'[/col] against '.$pmEncodedUsername.' whom we believe has exhibited some actions that is against the '.$siteName.' rules. This act of yours will definitely enable us serve you better.[br] All moderators in the section where the report was filed has been notified.
														They will review your report and take the necessary disciplinary actions/measures against '.$pmEncodedUsername.' for making '.$pmEncodedPost.'.[br] Thank you.' ;
													
														$SITE->sendPm($senderId, $sessUid, $subject, $message);
											
													}
													
													$alertUser = '<span class="alert alert-success">'.$GLOBAL_sessionUrl_unOnly.' thank you for taking time to file this report<br/> Your report has been submitted successfully.<br/>
													<br/>It will be reviewed by moderators of the '.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).' section where the topic/post is located<br/>Be rest assured that all the necessary disciplinary actions will be taken.<br/>
													<br/>Details of your report are as follows:<br/><a href="/'.$sessUsernameSlug.'" class="links">You</a> reported: <a href="/'.$usernameSlug.'" class="links">'.($isSess? 'Yourself' : $username).'</a>
													for making this <a href="'.$returnUrl.'" class="links">Post</a> FLAG RAISED: <b class="red bg-white">'.$flagRaised.'</b><br/> To continue from where you left off <a href="'.$returnUrl.'" class="links">click here</a></span>';
														
																		
													////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
													//$SITE->formGateRefresh($alertUser);
											 
												}else
													$alertUser = '<span class="alert alert-danger">Please kindly limit your description to a minimum of '.$minRC.' and maximum of '.$maxRC.' characters</span>';

											}else
												$alertUser = '<span class="alert alert-danger">Please kindly tell us the offence that <a href="/'.$usernameSlug.'" class="links" >'.($isSess? 'You' : $username).'</a> '.($isSess? 'have' : 'has').' committed with this '.($isSess? 'your' : $his_her).' post</span>';
										}else
											$notLogged = $GLOBAL_notLogged;
										
									}else
										$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', thank you for taking interest in flagging this post<br/> unfortunately this post has clocked its maximum permissible flag limit</span>';
		
								}else
									$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', thank you for taking interest in flagging this post<br/> unfortunately this post is locked and cannot be flagged at the moment!<br/> <a href="'.$returnUrl.'" class="links">Go Back</a></span>';

							}else
								$alertUser = '<span class="alert alert-danger">An unexpected error has ocurred<br/> We are sorry about that</span>';
							
						}
					
					}else
						$alertUser = '<div class="alert alert-warning">Sorry you do not meet the required reputation to flag a post</div>';
				
				}else
					$alertUser = '<div class="alert alert-warning">Sorry this thread is under a '.$protectionAlert.' protection <br/>you do not meet the required reputation to participate</div>';
				
				$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => '', 'stop' => 1, 'uniqueColumns' => '', 'filterCnd' => 'posts.ID=?', 'orderBy' => ''));
				
				/////DISPLAY THE POST/////
				list($post) = $FORUM->loadPosts($sql, array($postId));
				$post = '<button class="btn btn-xs btn-info" data-toggle="smartToggler" data-toggle-attr="text|hide the post" >show the post</button><div class="hide">'.$post.'</div>';
				
				list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget('', '', $tid);
					
			}else $alertUser =  '<span class="alert alert-danger">Sorry Something Went Wrong!<br/>Please '.$SITE->getBackBtn().' and try again.</span>'; 

		}else $alertUser =  '<span class="alert alert-danger">Sorry Something Went Wrong!<br/>Please go back and try again.</span>'; 

		$SITE->buildPageHtml(array("pageTitle"=>'Report Post - '.$postId,
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" >Make a Report</a></li>'),
					"pageBody"=>'
						<div class="single-base blend">
							<div class="base-ctrl">				
								<div class="row">
									<div class="'.$leftWidgetClass.' base-container base-b-pad">
										<div class="panel panel-limex">
											<h1 class="panel-head page-title">REPORT A POST</h1>
											<div class="panel-body">
												'.$alertUser.$notLogged.												 
												(($postId && $foundRes && $postAuthorized && $canFlagPost)? 
												$post.'
												<form method="post" action="/'.$pageSelf.'">
													<div class="field-ctrl">
														<label>FLAG <small class="prime">( Remaining '.(isset($postFlags)? (MAX_POST_FLAGS - $postFlags) : '' ).')</small>:</label>
														<select class="field" name="flag">
															<option>'.implode('</option><option>', REPORT_FLAGS_ARR).'</option>
														</select>
													</div>
													<div class="field-ctrl">							
														<label>															
															'.$GLOBAL_sessionUrl_unOnly.', What offence '.($isSess? 'have' : 'has').' <a href="/'.$usernameSlug.'" class="links">'.($isSess? 'You' : $username).'</a> Committed with this '.($isSess? 'Your' : $his_her).' post:
														</label>
														<textarea class="field" data-field-count="true" maxLength="'.$maxRC.'" data-minLength="'.$minRC.'"  name="report_content" placeholder="Please Briefly Describe the offence that '.($isSess? 'You have' : $username.' has').' committed with this '.($isSess? 'Your' : $his_her).' post">'.(isset($reportContent)? $reportContent : '').'</textarea>
													</div>
													<div class="field-ctrl btn-ctrl">
														<input  type="hidden"  name="post" value="'.$postId.'" />
														<input class="form-btn" type="submit" name="report" value="SUBMIT REPORT"  />
													</div>
												</form>'										
												 : '').'
											</div>
										</div>
									</div>'.$rightWidget.'
									<div class="">
										'.$pageBottomAds.'
									</div>
								</div>
							</div>
						</div>'
		));

		break;
		
	}
	
	
	
		
		
		
	/**CONTACT MODERATORS**/
	case 'contact-moderators':{
					
		$sectionOrCateg=$sid=$cid=$alertUser=$notLogged=$contactMethod=$subjectFieldErr=$messageFieldErr=$modsIds=
		$getSubject=$getMessage=$modsEmails=$modsUsername=$adminsEmails=$adminsUsername="";

		$fieldError = 'field-error';
		$unexpectedErr = '<span class="alert alert-danger" >An unexpected error has occurred<br/>We apologize for that</span>';

		if(isset($_GET[$K="sid"]))
			$sid = $_GET[$K];

		if(isset($_POST[$K]))
			$sid = $_POST[$K];

		if(isset($_GET[$K="cid"]))
			$cid = $_GET[$K];

		if(isset($_POST[$K]))
			$cid = $_POST[$K];

		if(!$SITE->categoryIdToggle($cid) && !$SITE->sectionIdToggle($sid))
			$altered = $unexpectedErr;

		$sid = $ENGINE->sanitize_number($sid);
		$cid = $ENGINE->sanitize_number($cid);

		//////////GET FORM-GATE RESPONSE//////////	
		$alertUser = $SITE->formGateRefreshResponse();						

		if($cid ||  $sid){	
			
			$sectionOrCateg = $sid? $SITE->sectionIdToggle($sid) : $SITE->categoryIdToggle($cid);

		}


		//////ON SEND////////

		if(isset($_POST['send_message'])){

			$subject = $ENGINE->sanitize_user_input($_POST["subject"]);
			$message = $ENGINE->sanitize_user_input($_POST["composed_message"]);
			$contactMethod = $ENGINE->sanitize_user_input($_POST["contact_method"]);	
			
			if($sessUsername){

				if($subject && $message) {
					
					//////CONTACT BY EMAIL/////////

					if($contactMethod == "E-MAIL"){
						
						if($sid || $cid ){
							
							/////GET MODERATORS AND ADMINS////////
							if($sid)										
								$modsEmails = $SITE->moderatedSectionCategoryHandler(array('scId'=>$sid, 'level'=>1, 'action'=>'get', 'retType'=>'email'));									
								
							elseif($cid)																																				
								$modsEmails = $SITE->moderatedSectionCategoryHandler(array('scId'=>$cid, 'level'=>2, 'action'=>'get', 'retType'=>'email'));

							if($modsEmails){
										
								$to = $modsEmails;

								$subject = $subject.' [To All '.$sectionOrCateg.' Moderators]';																			
											
								$footer = 'NOTE: This email was sent to you because you are a moderator in the <a href="'.$siteDomain.'/'.$ENGINE->sanitize_slug($sectionOrCateg).'">'.$sectionOrCateg.'</a> section at <a href="'.$siteDomain.'">'.$siteName.'</a>';
										 
								$SITE->sendMail(array('to'=>$to, 'senderName'=>'Webmaster & '.$ACCOUNT->loadUser($sessUsername)->getFirstName(), 'subject'=>$subject, 'body'=>$message, 'footer'=>$footer));				
								
								$alertUser = '<span class="alert alert-success">'.$GLOBAL_sessionUrl_unOnly.', your email has been dispatched to all  moderators of the '.$sectionOrCateg.' section</span>';
												
								////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
								$SITE->formGateRefresh($alertUser, '', '', '');

							}else
								$alertUser = $unexpectedErr;

						}else
							$alertUser = $unexpectedErr;

					}

					/////////CONTACT BY PM/////////
					elseif($contactMethod == "PM"){
						
						if($sid || $cid ){
														
							/////GET MODERATORS AND ADMINS////////
							if($sid)														
								$modsIds = $SITE->moderatedSectionCategoryHandler(array('scId'=>$sid, 'level'=>1, 'action'=>'get', 'retType'=>'id'));																													
					
							elseif($cid)																																	
								$modsIds = $SITE->moderatedSectionCategoryHandler(array('scId'=>$cid, 'level'=>2, 'action'=>'get', 'retType'=>'id'));

							if($modsIds){
																	
								$modsAdmins_arr = explode(",", $modsIds);
													
								foreach($modsAdmins_arr as $modsAdminsId){							
										 
									$SITE->sendPm($sessUid, $modsAdminsId, $subject.' [To All '.$sectionOrCateg.' Moderators]', $message);										
									
								}

								 $alertUser = '<span class="alert alert-success">'.$GLOBAL_sessionUrl_unOnly.', your private message(pm) has been dispatched to all moderators of the '.$sectionOrCateg.' section</span>';
													
								////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
								$SITE->formGateRefresh($alertUser, '', '', '');

							}else
								$alertUser =  $unexpectedErr;

						}else
							$alertUser =  $unexpectedErr;

					}

				}else{
					
					$alertUser = '<span class="alert alert-danger">Fields marked (*) are required</span>';	

					if($message)				
						$getMessage = $message;	
							
					if($subject)				
						$getSubject = $subject;
								
					if(!$message)						
						$messageFieldErr = $fieldError;	
														
					if(!$subject)					
						$subjectFieldErr = $fieldError;
						
					$rqd = '<span class="asterix">*</span>';
					
				}

			}else		
				$notLogged = $GLOBAL_notLogged;
			
		}
		
		$subNav = '';
		$subNav = '<li><a href="/'.$pageSelf.'" title="">Contact Mods</a></li>';
		$titleTerm = ($sid == HOMEPAGE_SID)? 'Elite' : $sectionOrCateg;
		
		if($sid || $cid){
		
			$header = $ENGINE->sanitize_slug($sectionOrCateg, array('ret'=>'url', 'urlHash'=>'smods', 'urlText'=>(($sid == HOMEPAGE_SID)? '<img class="icon-sm" src="'.$mediaRootFav.'star_c.png" />Elite' : $sectionOrCateg).' Moderators'));
			$subNav .= '<li>'.$header.'</li>';
		
		}

		$SITE->buildPageHtml(array("pageTitle"=>'Contact '.$titleTerm.' Moderators',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs($subNav),
					"pageBody"=>'			
						<div class="single-base blend">
							<div class="base-ctrl">'.
								(isset($altered)? $altered : '').
								(!isset($altered)? '						
									<div class="panel panel-limex">					
										<h3 class="panel-head page-title">
											Contact '.$header.'
										</h3>
										<div class="panel-body">
											'.$notLogged.$alertUser.'											
											<form class="horizontal-form" action="/'.$pageSelf.'" method="post">
												<fieldset>				
													<div class="field-ctrl">
														<label>Subject:</label>
														<input maxlength="100" class="field '.$subjectFieldErr.'" type="text"  placeholder="Type in your message subject here" name="subject" value="'.(isset($getSubject)? $getSubject : '').'" />'.((isset($rqd)  && !$getSubject)? $rqd : '').'
													</div>
													<div class="field-ctrl">
														<label>Message:</label>
														<textarea class="field '.$messageFieldErr.'"  placeholder="Type your message here" name="composed_message">'.(isset($getMessage)? $getMessage : '').'</textarea>'.((isset($rqd)  && !$getMessage)? $rqd : '').'
														<input type="hidden" name="sid" value="'.(isset($sid)? $sid : '').'" />
														<input  type="hidden" value="'.(isset($cid)? $cid : '').'" name="cid" />					
													</div>
													<div class="field-ctrl">
														<label>Contact Method:</label>				
														<select  name="contact_method" class="field">
															<option title="contact by E-mail" '.((isset($contactMethod) && $contactMethod == "E-MAIL")? 'selected' : '').' >E-MAIL</option>
															<option title="contact by private message(PM)" '.((isset($contactMethod) && $contactMethod == "PM")? 'selected' : '').' >PM</option>
														</select>
													</div>		
													<div class="field-ctrl">
														<input  class="form-btn"  type="submit" value="SEND" name="send_message" />
													</div>
												</fieldset>	
											</form>	
										</div>
									</div>'
								: '').'
							</div>
						</div>'
		));

		break;
		
	}
	
	
	
	
	
	/**ESTIMATED AD RATES**/
	case 'estimated-ad-rates':{
		
		$datas='';

		/***************************BEGIN URL CONTROLLER****************************/
	
		$page_self_srt = $ENGINE->get_page_path('page_url', 1);
		$path2 = isset($pagePathArr[1])? strtolower($pagePathArr[1]) : '';		
		$path3 = isset($pagePathArr[2])? strtolower($pagePathArr[2]) : '';
			
		if(in_array($path2, array('alphabet','popularity'))){
				
			$pathKeysArr = array('pageUrl', 'sort');			
			$maxPath = 2;
	
			if(in_array($path3, array('asc','desc'))){
	
				$pathKeysArr[] = 'orderFlow';
				$maxPath = 3;
	
			}
	
		}else{
	
			$pathKeysArr = array('pageUrl');
			$maxPath = 1;
	
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/
		
		$getSortBy = (isset($_GET[$K="sort"]))? $ENGINE->sanitize_user_input($_GET[$K]) : "";	
		$orderFlow = (isset($_GET[$K="orderFlow"]))? $ENGINE->sanitize_user_input($_GET[$K]) : "";
		
		switch(strtolower($orderFlow)){
	
			case 'asc':		
				$orderFlow = " ASC ";
				$orderFlowLnk = 'asc'; break;
					
			default:
				$orderFlow = " DESC ";
				$orderFlowLnk = 'desc';
	
		}
		
		switch(strtolower($getSortBy)){
	
			case 'alphabet':
				$popularitySubQry = '';
				$orderUsed = "SECTION_NAME ".$orderFlow;
				break;	
			
			default:
				$popularitySubQry = $SITE->getPopularitySubQuery();
				$orderUsed = "POPULARITY ".$orderFlow.", SECTION_NAME ".$orderFlow;
				$getSortBy = 'popularity';
	
		}
	
		$orderUsed = ' ORDER BY '.$orderUsed;

		$sortNav = $SITE->buildSortLinks(array(
					'baseUrl' => $page_self_srt, 'pageId' => '', 'sq' => '', 'urlHash' => '', 
					'activeOrder' => $getSortBy, 'orderList' => array('popularity.desc', 'alphabet.asc'), 
					'activeOrderFlow' => $orderFlowLnk, 'orderFlowList' => array('asc', 'desc')
					));
		
		$adsCampaign = new AdsCampaign();
		
		///////////PDO QUERY////////
			
		$sql =  "SELECT s.ID, SECTION_NAME, SECTION_DESC ".$popularitySubQry." FROM sections s ".$orderUsed;

		$stmt = $dbm->query($sql);
			
		$i = 1;
		
		while($row = $dbm->fetchRow($stmt)){
	
			$sid = $row["ID"];
			$section = $row["SECTION_NAME"];
			$datas .= '<div class=""><span class="base-r-pad">'.$i.'.</span><b>'.$ENGINE->sanitize_slug(($sid == HOMEPAGE_SID? '' : $section), array('ret'=>'url', 'urlText'=>$section, 'urlAttr'=>'title="'.$row["SECTION_DESC"].'"')).'</b> - '.$adsCampaign->getAdSlots($row["ID"], $row["SECTION_NAME"].' section').'<div class="">'.$adsCampaign->getAdRate(array("sid"=>$row["ID"],"type"=>"ear")).'</div></div>';
			
			$i++;

		}
		
					
		$SITE->buildPageHtml(array("pageTitle"=>'Ad Rates',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/estimated-ad-rates" title="">Ad Rates</a></li>'),
					"pageBody"=>'				
						<div class="single-base blend">
							<div class="base-ctrl">								
								<div class="base-rad base-b-pad">
									<h1 class="page-title pan bg-mine-1">Estimated Ad Rates</h1>
									<div class="row">
										<div class="col-lg-w-5-pull">
											<div class="alert alert-warning">
												<b>ATTENTION!<br/> Please note that the prices listed below are estimated cost of advertising on the various sections of this community on the long run.
													It is therefore subject to variation especially on short term period during which the advertising costs are strongly correlated with traffic levels.
													<br/>Please see our <a class="links" href="/policies/ads" >Ad Policy</a>
												</b>
											</div>
										</div>
										<div class="col-lg-w-5-pull">
											<div class="alert alert-info">
												<b>NOTE!</b>'.$adsCampaign->getCampaignNote().'
											</div>
										</div>
									</div>'.$sortNav.'
									<div class="align-l hr-dividers">'.													  													
										(isset($datas)? $datas : '').'										
									</div>
								</div>
							</div>
						</div>'
		));

		break;
	
	}
	
	
	
	
	
	
	
	/**AD CREDIT HISTORY**/
	case 'ad-credit-history':{
		
		$records=$pagination=$isOwner='';
		
		if($sessUsername){				
			
			$adsCampaign = new AdsCampaign();
			
			/***************************BEGIN URL CONTROLLER****************************/
			
			$pathKeysArr = array('pageUrl', 'sort', 'orderFlow', $pageKey='pageId');			
			$maxPath = 4;

			$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

			/*******************************END URL CONTROLLER***************************/								
			
			$pageCount = 100;	
			$urlHash = 'tab';	
			$table = 'transactions_completed_reference';	
			
			list($owner, $ownerId, $alertAdmins, $specQstr, $refTitle, $isOwner) = $SITE->getBackDoorViewOwnersParams();			
			
			//GRAB PAGE RENDER PARAMS
			$getSortBy = (isset($_GET[$K="sort"]))? $ENGINE->sanitize_user_input($_GET[$K]) : '';	
			$orderFlow = (isset($_GET[$K="orderFlow"]))? $ENGINE->sanitize_user_input($_GET[$K]) : '';
			$sq = isset($_POST[$K='sq'])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : '');
			$alertUser = isset($_POST[$K='sq']) && !$sq? '<span class="alert alert-danger">Please enter a search query</span>' : '';
				
			switch(strtolower($orderFlow)){									 
						
				case 'asc':		
					$orderFlow = " ASC ";
					$orderFlowLnk = 'asc';
					break;									 
										
				default:
					$orderFlow = " DESC ";
					$orderFlowLnk = 'desc';									 
						
			}
				
			
			$valArr = array($ownerId, AdsCampaign::$storeName);
			
			$filterCnd = "USER_ID = ? AND STORE_NAME = ?";
			
			if($sq){									 
						
				$filterCnd .= " AND (REFERENCE = ? OR GATEWAY_TRANX_ID = ?) ";
				$valArr[] = $sq;									 
				$valArr[] = $sq;									 
						
			}
			
			$filterCnd? ($filterCnd = ' WHERE ('.$filterCnd.')') : '';
			
			/////PDO QUERY/////
			
			$sql =  "SELECT COUNT(*) FROM ".$table.$filterCnd;
			$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
			
			$altOrderCol = ", AMOUNT ".$orderFlow;									 
						
			switch(strtolower($getSortBy)){									 
						
				case 'amount':
					$orderUsed = "AMOUNT ".$orderFlow.$altOrderCol;
					break;									 
						
				case 'gateway':
					$orderUsed = "GATEWAY_NAME ".$orderFlow.$altOrderCol;
					break;									 
						
				default:
					$orderUsed = "DATE ".$orderFlow;
					$getSortBy = 'date';									 
						
			}									 
						
			$orderUsed = ' ORDER BY '.$orderUsed;
			
			/**********CREATE THE PAGINATION******/		
			$pageUrl = $ENGINE->get_page_path('page_url', 1);
			$qstrValArr = array($getSortBy, $orderFlowLnk);		
			$qstrKeyValArr = array('sq' => $sq);		
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>$pageKey,'jmpKey'=>'jump_page_s','qstrVal'=>$qstrValArr,'qstrKeyVal'=>$qstrKeyValArr,'perPage'=>$pageCount,'hash'=>$urlHash));
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageId = $paginationArr["pageId"];
		
			$sortNav = $SITE->buildSortLinks(array(
						'baseUrl' => $pageUrl, 'pageId' => $pageId, 'sq' => $sq, 'urlHash' => $urlHash, 
						'activeOrder' => $getSortBy, 'orderList' => array('date', 'amount', 'gateway'), 
						'activeOrderFlow' => $orderFlowLnk, 'orderFlowList' => array('asc', 'desc')
						)).
						$SITE->getSearchForm(array('url' => '/'.$pageSelf, 'fieldName' => 'sq', 'pageResetUrl' => $pageUrl, 
						'fieldLabel' => '', 'fieldPH' => 'Search by reference', 'btnName' => 'search', 'btnLabel' => 'GO'));
					
			
			$sql = "SELECT * FROM ".$table.$filterCnd.$orderUsed." LIMIT ".$startIndex.",".$perPage;
			$stmt = $dbm->doSecuredQuery($sql, $valArr);									 
						
			if($totalRecords){									 
						
				while($row = $dbm->fetchRow($stmt)){										 
						
					$records .= '<tr>
								<td>'.$ACCOUNT->sanitizeUserSlug($ACCOUNT->memberIdToggle($row["USER_ID"]), $metaArr=array('anchor'=>true, 'youRef'=>false)).'</td>
								<td>'.($valGiven = $ENGINE->format_number($row["AMOUNT"], 2, false)).'</td>
								<td>'.$valGiven.'</td>
								<td>'.$row["GATEWAY_NAME"].'</td>
								<td>'.$row["REFERENCE"].'</td>
								<td>'.$ENGINE->time_ago($row["DATE"]).'</td>
							</tr>';	
								
				}
				
						
				$records = '<div class="table-responsive"><table class="table-classic"><tr><th>PAYER</th><th>AMOUNT PAID</th><th>VALUE GIVEN</th><th>GATEWAY</th><th>PAYMENT REFERENCE</th><th>DATE</th></tr>'.$records.'</table></div>';
				
			}else
				$alertUser = '<span class="alert alert-danger">Sorry no payment record was found matching your request. '.$SITE->getBackBtn().'</span>';
				
		}else
			$notLogged  = $GLOBAL_notLogged;
			
		$recordCount = '<h2 class="cpop">'.(isset($totalRecords)? $totalRecords : 0).' record'.((isset($totalRecords) && $totalRecords > 1)? 's' : '').' found</h2>';
			
		$pageTitle = 'AdS Campaign Credit History'.(!$isOwner? ' ('.$owner.')' : '');
		
		$SITE->buildPageHtml(array("pageTitle"=>'Ads Campaign - '.$pageTitle,
						"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/ads-campaign" title="">Ads Campaign</a></li><li><a href="/'.$pageSelf.'" title="">'.$pageTitle.'</a></li>'),
						"pageBody"=>'
						<div class="single-base blend">
							<div class="base-ctrl">
								'.(isset($notLogged)? $notLogged : '').
								($sessUsername?
									'<div class="panel panel-lime">
										<h1 class="panel-head page-title">'.strtoupper($pageTitle).($pagination? '<br/>(page <span class="cyan">'.$pageId.'</span>  of '.$totalPage.')' : '').'</h1>
										<div class="panel-body">'.
											(isset($alertAdmins)? $alertAdmins : '').
											(isset($recordCount)? $recordCount : '').
											(isset($totalRecords) && $totalRecords? $sortNav : '').
											$pagination.$alertUser.$records.$pagination.'
										</div>
									</div>'
								: '').'
							</div>
						</div>'
		));
			
		break;
	}
	
	
	
	
	
	/**AD REPORT**/
	case 'ad-traffic-report':{
		
		$QRY=$GRPCMD=$trows=$GRPCOL=$COUNTCOL=$th=$orderFlow=$orderFlowOpt=
		$notLogged=$campaignInfos=$options=$avail_section=$pagination=$grpBy=$grpOpt=
		$tdatas=$sort=$usort=$action=$adType=$CampTable=$urlRetType=$isOwner="";				

		if($sessUsername){				
			
			$adsCampaign = new AdsCampaign();
			
			/***************************BEGIN URL CONTROLLER****************************/
			
			$path2 = isset($pagePathArr[1])? $pagePathArr[1] : '';
			
			if($adsCampaign->validateCampaignType($path2)){
	
				$pathKeysArr = array('pageUrl', 'campaign', 'ad', 'sort', 'group', 'order_flow', 'pageId');
				$maxPath = 7;
			
			}else{	
			
				$pathKeysArr = array();
				$maxPath = 0;	
			
			}

			$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

			/*******************************END URL CONTROLLER***************************/								
			
			$adRptTable = 'ad_traffic_reports';
			
			if(isset($_GET[$K="campaign"]))
				$campaign = $ENGINE->sanitize_user_input($_GET[$K]);
			
			$adsCampaign->setCampaignType($campaign);
			$campaignTable = $adsCampaign->getCampaignTable();
			$urlRetType = '/'.$campaign.'-campaign';
				

			if(isset($_GET[$K="ad"]) && $_GET[$K]){
				
				$adIdEnc = $_GET[$K];
				$adId = $ENGINE->sanitize_number($INT_HASHER->decode($_GET[$K]));
				
				list($owner, $ownerId, $alertAdmins, $specQstr, $refTitle, $isOwner, $intruderAlert) = $SITE->getBackDoorViewOwnersParams();				
					
				//////DELETE REPORT//////
				if(isset($_POST["clear_ad_traffic_report"])){
					
					$subDelQry = "SELECT ID FROM ".$campaignTable." WHERE (USER_ID=? AND ID=?)";
					
					////////PDO QUERY///////																
					$sql = 'DELETE FROM '.$adRptTable.' WHERE (AD_ID IN('.$subDelQry.') AND CAMPAIGN_TYPE=?)';							
					
					$valArr = array($ownerId, $adId, $campaign);
					$stmt = $dbm->doSecuredQuery($sql, $valArr);
					
				}
				
							
				/////////////////GET SORT ORDER IF ANY/////////////
							
				if(isset($_POST[$K="group"]))
					$grpBy = $_POST[$K];
				
				elseif(isset($_GET[$K]))
					$grpBy = $_GET[$K];
					
				$grpBy = !$grpBy? 'None' : $ENGINE->sanitize_user_input($grpBy, array('urlDecode' => true));
				
				if(isset($_POST[$K="sort"]))
					$usort = $_POST[$K];
				
				elseif(isset($_GET[$K]))
					$usort = $_GET[$K];
					
				$usort = !$usort? 'None' : $ENGINE->sanitize_user_input($usort,  array('urlDecode' => true));
				
				if(isset($_POST[$K="order_flow"]))
					$orderFlow = $_POST[$K];
				
				elseif(isset($_GET[$K]))
					$orderFlow = $_GET[$K];
					
				$orderFlow = !$orderFlow? 'Ascending' : $ENGINE->sanitize_user_input($orderFlow,  array('urlDecode' => true));
					
				switch(strtolower($orderFlow)){
					
					case 'descending': $orderFlowDb = 'DESC'; break;
					
					default: $orderFlowDb = 'ASC';
					
				}
				
						
				///DEFAULT COL AND TABLE HEADS///	
				$autoColArr = array("SECTION_NAME","SOURCE_USER_ID","IP","TIME");	
				$autoThArr = array("SECTION","SENDER","IP","TIME");
				
				//////SWITCH GROUPINGS///////
				switch(strtolower($grpBy)){	
										
					case 'section':
					
						$GRPCOL = ", sc.SECTION_NAME, COUNT(*) AS TOTAL_HIT "; $GRPCMD = " GROUP BY SOURCE_SECTION_ID "; 
						$COUNTCOL = 'DISTINCT SOURCE_SECTION_ID';
						$autoColArr = array("SECTION_NAME","TOTAL_HIT");
						$autoThArr = array("SECTION","TOTAL HIT");	
						$disableIntvSrt = true;
							break;
									
					case 'year':
						$GRPCOL = ", COUNT(*) AS TOTAL_HIT, YEAR(r.TIME) AS YEARGRP  "; $GRPCMD = " GROUP BY YEARGRP "; 
						$COUNTCOL = 'DISTINCT YEAR(r.TIME)';
						$autoColArr = array("YEARGRP","TOTAL_HIT");			
						$autoThArr = array("YEAR","TOTAL HIT");
						$disableIntvSrt = true;
						break;			
					
					case 'month':
						$GRPCOL = ", COUNT(*) AS TOTAL_HIT, YEAR(r.TIME) AS YEARGRP, MONTHNAME(r.TIME) AS MONTHGRP  "; $GRPCMD = " GROUP BY YEARGRP,MONTHGRP "; 
						$COUNTCOL = 'DISTINCT  MONTHNAME(r.TIME)';
						$autoColArr = array("YEARGRP","MONTHGRP","TOTAL_HIT");			
						$autoThArr = array("YEAR","MONTH","TOTAL HIT");
						$disableIntvSrt = true;
						break;			
					
					case 'week':
						$GRPCOL = ", COUNT(*) AS TOTAL_HIT, YEAR(r.TIME) AS YEARGRP, WEEK(r.TIME) AS WEEKGRP  "; $GRPCMD = " GROUP BY YEARGRP,WEEKGRP "; 
						$COUNTCOL = 'DISTINCT  WEEK(r.TIME)';
						$autoColArr = array("YEARGRP","WEEKGRP","TOTAL_HIT");			
						$autoThArr = array("YEAR","WEEK","TOTAL HIT");
						$disableIntvSrt = true;
						break;			
					
					case 'day':
						$GRPCOL = ", COUNT(*) AS TOTAL_HIT, YEAR(r.TIME) AS YEARGRP, DAYNAME(r.TIME) AS DAYGRP  "; $GRPCMD = " GROUP BY YEARGRP,DAYGRP "; 
						$COUNTCOL = 'DISTINCT DAYNAME(r.TIME)';
						$autoColArr = array("YEARGRP","DAYGRP","TOTAL_HIT");			
						$autoThArr = array("YEAR","DAY","TOTAL HIT");
						$disableIntvSrt = true;
						break;			
					
					case 'hour':
						$GRPCOL = ", COUNT(*) AS TOTAL_HIT, YEAR(r.TIME) AS YEARGRP, HOUR(r.TIME) AS HOURGRP  "; $GRPCMD = " GROUP BY YEARGRP,HOURGRP "; 
						$COUNTCOL = 'DISTINCT  HOUR(r.TIME)';
						$autoColArr = array("YEARGRP","HOURGRP","TOTAL_HIT");			
						$autoThArr = array("YEAR","HOUR","TOTAL HIT");
						$disableIntvSrt = true;
						break;			
					
					default:
						$GRPCOL = ", sc.SECTION_NAME"; $GRPCMD = ""; $COUNTCOL = '*';						
									
				}			
					
				$xSubQry = "  JOIN ".$campaignTable." cmp ON r.AD_ID=cmp.ID LEFT JOIN sections sc ON r.SOURCE_SECTION_ID=sc.ID ";
				$xCond = " WHERE (cmp.USER_ID=? AND r.AD_ID=? AND r.CAMPAIGN_TYPE=?)  ";			
					
				////////SWITCH INTERVALS/SORTING/////////
				switch(strtolower($usort)){			
						
					case 'hour':
						$QRY = "SELECT *, HOUR(r.TIME) AS HOUR ".$GRPCOL." FROM ".$adRptTable." r ".$xSubQry.$xCond.$GRPCMD."  ORDER BY HOUR ".$orderFlowDb.", CAMPAIGN_TYPE ".$orderFlowDb;												
						
						if(!isset($disableIntvSrt))
							$autoThArr[] = $autoColArr[] = 'HOUR';	
							
						break;
					
					case 'day':
						$QRY = "SELECT *, DAYNAME(r.TIME) AS DAY ".$GRPCOL."  FROM ".$adRptTable." r ".$xSubQry.$xCond.$GRPCMD."  ORDER BY DAY ".$orderFlowDb.", CAMPAIGN_TYPE ".$orderFlowDb;										
						
						if(!isset($disableIntvSrt))
							$autoThArr[] = $autoColArr[] = 'DAY';		
						
						break;
					
					case 'week':
						$QRY = "SELECT *, WEEK(r.TIME) AS WEEK ".$GRPCOL."  FROM  ".$adRptTable." r ".$xSubQry.$xCond.$GRPCMD."  ORDER BY WEEK ".$orderFlowDb.", CAMPAIGN_TYPE ".$orderFlowDb;								
					
						if(!isset($disableIntvSrt))
							$autoThArr[] = $autoColArr[] = 'WEEK';

						break;
					
					case 'month':
						$QRY = "SELECT *, MONTHNAME(r.TIME) AS MONTH ".$GRPCOL."  FROM ".$adRptTable." r ".$xSubQry.$xCond.$GRPCMD."  ORDER BY MONTH ".$orderFlowDb.", CAMPAIGN_TYPE ".$orderFlowDb;
					
						if(!isset($disableIntvSrt))
							$autoThArr[] = $autoColArr[] = 'MONTH';
						
						break;
					
					case 'year':
						$QRY = "SELECT *, YEAR(r.TIME) AS YEAR ".$GRPCOL."  FROM ".$adRptTable." r ".$xSubQry.$xCond.$GRPCMD."  ORDER BY YEAR ".$orderFlowDb.", CAMPAIGN_TYPE ".$orderFlowDb;							
					
						if(!isset($disableIntvSrt))
							$autoThArr[] = $autoColArr[] = 'YEAR';
						
						break;
									
					case 'sender':
						$QRY = "SELECT r.* ".$GRPCOL." FROM ".$adRptTable." r ".$xSubQry." JOIN users u ON r.SOURCE_USER_ID = u.ID ".$xCond." ORDER BY USERNAME ".$orderFlowDb.", CAMPAIGN_TYPE ".$orderFlowDb;
						break;
					
					case 'section':
						$QRY = "SELECT r.* ".$GRPCOL." FROM ".$adRptTable." r ".$xSubQry." JOIN sections sec ON r.SOURCE_SECTION_ID = sec.ID ".$xCond." ORDER BY SECTION_NAME ".$orderFlowDb.", CAMPAIGN_TYPE ".$orderFlowDb;								
						break;
						
					default:
						$QRY = "SELECT r.*  ".$GRPCOL." FROM ".$adRptTable." r ".$xSubQry.$xCond.$GRPCMD." ORDER BY r.TIME ".$orderFlowDb.",CAMPAIGN_TYPE ".$orderFlowDb;
																
									
				}
				
				
				////////PDO QUERY//////////
				
				$sql =  "SELECT COUNT(".$COUNTCOL.") FROM  ".$adRptTable." r ".$xSubQry.$xCond;

				$valArr = array($ownerId, $adId, $campaign);
				$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();			
				
				if($totalRecords){								
												
					/**********CREATE THE PAGINATION***********/							
					$pageUrl = $ENGINE->get_page_path('page_url', 3);
					$qstrValArr = array($usort, $grpBy, $orderFlow);
					$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'qstrVal'=>$qstrValArr,'hash'=>'tab', 'perPage'=>30));					
					$pagination = $paginationArr["pagination"];
					$totalPage = $paginationArr["totalPage"];
					$perPage = $paginationArr["perPage"];
					$startIndex = $paginationArr["startIndex"];
					$pageId = $paginationArr["pageId"];

					/////////////END OF PAGINATION////////////////////
						
					///////PDO QUERY/////////
					
					$sql =  $QRY." LIMIT ".$startIndex.",".$perPage;
					$valArr = array($ownerId, $adId, $campaign);
					$stmt = $dbm->doSecuredQuery($sql, $valArr);
							
					while($row = $dbm->fetchRow($stmt)){			
						
						$tdatas="";			
						
						foreach($autoColArr as $col){
													
							if($col == "TOTAL_HIT")
								$tdatas .= '<td>'.$ENGINE->format_number($row[$col], 0, false).'</td>';							
								
							elseif($col == "SECTION_NAME")
								$tdatas .= '<td>'.$ENGINE->sanitize_slug($row[$col], array('ret'=>'url')).'</td>';							
								
							elseif($col == "SOURCE_USER_ID")
								$tdatas .= '<td>'.($ACCOUNT->memberIdToggle($row[$col])? $ACCOUNT->memberIdToggle($row[$col]) : "A Guest").'</td>';							
								
							elseif($col == "TIME")
								$tdatas .= '<td>'.$ENGINE->time_ago($row[$col]).'</td>';							
								
							else
								$tdatas .= '<td>'.$row[$col].'</td>';
								
						}	
						
						$trows .= 	'<tr>'.$tdatas.'</tr>';
					}
					
					foreach($autoThArr as $head)
						$th .= '<th>'.$head.'</th>';
					
					$tdatas = 	'<div class="table-responsive base-tb-mg" id="tab"><table class="table-classic">						
									'.$th.$trows.'
								</table></div>';

					$sort_arr = array("None", "Section","Sender", "Hour", "Day", "Week", "Month", "Year");
					$grp_arr = array("None", "Section", "Year", "Month", "Week", "Day", "Hour");
					$orderFlow_arr = array("Ascending", "Descending");
					
					foreach($sort_arr as $sort_by)
						$sort .= '<option '.((strtolower($usort) == strtolower($sort_by))? 'selected' : '').'>'.$sort_by.'</option>';
					
					foreach($grp_arr as $grp)
						$grpOpt .= '<option '.((strtolower($grpBy) == strtolower($grp))? 'selected' : '').'>'.$grp.'</option>';
						
					
					foreach($orderFlow_arr as $oflow)
						$orderFlowOpt .= '<option '.((strtolower($orderFlow) == strtolower($oflow))? 'selected' : '').'>'.$oflow.'</option>';
						
						
					$sort = '<div class="field-ctrl">
								<label>Sort by:</label>
								<select name="sort" class="field">'.$sort.'</select>
							</div>
							<div class="field-ctrl">
								<label>Group by:</label>
								<select name="group" class="field">'.$grpOpt.'</select>
							</div>
							<div class="field-ctrl">
								<label>Flow:</label>
								<select name="order_flow" class="field options-inherit-bg">'.$orderFlowOpt.'</select>
							</div>';
										

				}
										

			}else{
				
				header("location:/ads-campaign".$urlRetType);
				exit();
			}

			
			$clearRpt = $adsCampaign->getMeta('both_export_link', array('adId' => $adIdEnc, 'specQstr' => $specQstr, 'refTitle' => $refTitle)).'
						<button class="btn btn-danger" data-toggle="smartToggler" >Clear Traffic Report</button>
						<div class="red modal-drop hide has-close-btn has-caret">
							<p> 
								ARE YOU SURE YOU WANT TO CLEAR THE TRAFFIC RECORDS FOR THIS CAMPAIGN (<b class="cyan">'.$adIdEnc.'</b>)
								<br/> NOTE: This Record cannot be recovered once cleared
							</p>
							<form action="/'.$pageSelf.'" method="post">
								<button type="submit" name="clear_ad_traffic_report" class="btn btn-danger">YES</button>
								<button type="button" class="btn close-toggle">CLOSE</button>
							</form>				
						</div>';

				
		}else
			$notLogged  = $GLOBAL_notLogged;
		
					
		$pageTitle = 'Ad Traffic Report';
		
		$SITE->buildPageHtml(array("pageTitle"=>$pageTitle,
						"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/ads-campaign" title="">Ads Campaign</a></li><li><a href="/ads-campaign'.$urlRetType.'" title="">'.ucwords(trim(str_ireplace("-", " ", $urlRetType), "/")).'</a></li><li><a href="/'.$pageSelf.'" title="">'.$pageTitle.'</a></li>'),
						"pageBody"=>'
						<div class="single-base blend">
							<div class="base-ctrl">
								'.$notLogged.																		
								($sessUsername? 
									'<div class="panel panel-lime">
										<h1 class="panel-head page-title">'.strtoupper($pageTitle).($pagination? '<br/>(page <span class="cyan">'.$pageId.'</span>  of '.$totalPage.')' : '').'</h1>
										<div class="panel-body">'.
											(isset($alertAdmins)? $alertAdmins : '').
											$adsCampaign->getCampaignDetailsCard(array("uid"=>$ownerId, "adId"=>$adId, "adIdEnc"=>$adIdEnc)).
											($sort?													
												(isset($pagination)? $pagination : '').'														
												<form class="inline-form inline-form-auto" action="/'.$ENGINE->get_page_path('page_url', 3).$specQstr.'#tab" method="post">
													'.$sort.'	
													<div class="field-ctrl">						
														<input type="submit" class="btn" name="go" value="Apply" />
													</div>
												</form><br/>' : '').														
												($tdatas? ((isset($clearRpt)? $clearRpt : '').$tdatas.(isset($clearRpt)? $clearRpt : '')) : '').
												(!$tdatas? ((isset($intruderAlert) && $intruderAlert)? $intruderAlert : '<span class="alert alert-danger">No Reports Yet</span>') : ''). 														
											(isset($pagination)? $pagination : '').'
										</div>
									</div>'
								: '').'
							</div>
						</div>'
		));

		break;
		
	}
	
	
	
	
	
	
	
	/**BILLING REPORT**/
	case 'ad-billing-report':{

		$notLogged=$options=$pagination=$tdatas=$grpBy=$grpOpt=$QRY=$GRPCMD=$trows=$sort=$usort=$action=$adType=
		$table=$table2=$urlRetType=$GRPCOL=$th=$orderFlow=$orderFlowOpt=$isOwner="";

		if($sessUsername){
			
			$adsCampaign = new AdsCampaign();
			
			/***************************BEGIN URL CONTROLLER****************************/
			
			$path2 = isset($pagePathArr[1])? $pagePathArr[1] : '';
			
			if($adsCampaign->validateCampaignType($path2)) {
		
				$pathKeysArr = array('pageUrl', 'campaign', 'sort', 'group', 'order_flow', 'pageId');
				$maxPath = 6;
				
			}else{	
			
				$pathKeysArr = array();
				$maxPath = 0;	
			
			}

			$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));
			
			$adBillTable = 'ad_billings';
			
			if(isset($_GET[$K="campaign"]))
				$campaign = $ENGINE->sanitize_user_input($_GET[$K]);
			
			$adsCampaign->setCampaignType($campaign);
			$campaignTable = $adsCampaign->getCampaignTable();
			$urlRetType = '/'.$campaign.'-campaign';
			
			list($owner, $ownerId, $alertAdmins, $specQstr, $refTitle, $isOwner) = $SITE->getBackDoorViewOwnersParams();
			
			/*******************************END URL CONTROLLER***************************/

			////DELETE REPORT//
			if(isset($_POST["clear_campaign_billing_report"])){
				
				////////PDO QUERY///////
				$subDelQry = "SELECT ID FROM ".$campaignTable." WHERE USER_ID=?";
				$sql = 'DELETE FROM '.$adBillTable.' WHERE (CAMPAIGN_TYPE=? AND AD_ID IN('.$subDelQry.'))';
				$valArr = array($campaign, $ownerId);
				$stmt = $dbm->doSecuredQuery($sql, $valArr);
				
			}
			
			////////GET SORT ORDER IF ANY////
				
			if(isset($_POST[$K="group"]))
				$grpBy = $_POST[$K];
			
			elseif(isset($_GET[$K]))
				$grpBy = $_GET[$K];
				
			$grpBy = !$grpBy? 'None' : $ENGINE->sanitize_user_input($grpBy,  array('urlDecode' => true));
			
			if(isset($_POST[$K="sort"]))
				$usort = $_POST[$K];
			
			elseif(isset($_GET[$K]))
				$usort = $_GET[$K];
				
			$usort = !$usort? 'None' : $ENGINE->sanitize_user_input($usort,  array('urlDecode' => true));
			
			if(isset($_POST[$K="order_flow"]))
				$orderFlow = $_POST[$K];
			
			elseif(isset($_GET[$K]))
				$orderFlow = $_GET[$K];
				
			$orderFlow = !$orderFlow? 'Ascending' : $ENGINE->sanitize_user_input($orderFlow,  array('urlDecode' => true));
				
			switch(strtolower($orderFlow)){
				
				case 'descending': $orderFlowDb = 'DESC'; break;	
			
				default: $orderFlowDb = 'ASC';
				
			}
			
			///DEFAULT COL AND TABLE HEADS///	
			$autoColArr = array("AD_ID","CAMPAIGN_TYPE","TOTAL_BILLING","SECTION_NAME","TIME");	
			$autoThArr = array("AD","CAMPAIGN","AMOUNT","SECTION","TIME");
			
			//////SWITCH GROUPINGS///////
			switch(strtolower($grpBy)){	
			
				case 'ad':
					$GRPCOL = ", SUM(BILLING_AMOUNT) AS TOTAL_BILLING "; $GRPCMD = " GROUP BY AD_ID ";  						
					$autoColArr = array("CAMPAIGN_TYPE","AD_ID","TOTAL_BILLING");			
					$autoThArr = array("CAMPAIGN","AD","TOTAL AMOUNT");
					$disableIntvSrt = true;
					break;
				
				case 'section':
					$GRPCOL = ", sc.SECTION_NAME, SUM(BILLING_AMOUNT) AS TOTAL_BILLING "; $GRPCMD = " GROUP BY BILLING_SECTION_ID "; 						
					$autoColArr = array("CAMPAIGN_TYPE","SECTION_NAME","TOTAL_BILLING");
					$autoThArr = array("CAMPAIGN","SECTION","TOTAL AMOUNT");	
					$disableIntvSrt = true;
					break;
				
				case 'year':
					$GRPCOL = ", SUM(BILLING_AMOUNT) AS TOTAL_BILLING, YEAR(b.TIME) AS YEARGRP  "; $GRPCMD = " GROUP BY YEARGRP "; 						
					$autoColArr = array("CAMPAIGN_TYPE","YEARGRP","TOTAL_BILLING");			
					$autoThArr = array("CAMPAIGN","YEAR","TOTAL AMOUNT");
					$disableIntvSrt = true;
					break;			
				
				case 'month':
					$GRPCOL = ", SUM(BILLING_AMOUNT) AS TOTAL_BILLING, YEAR(b.TIME) AS YEARGRP, MONTHNAME(b.TIME) AS MONTHGRP  "; $GRPCMD = " GROUP BY YEARGRP,MONTHGRP "; 						
					$autoColArr = array("CAMPAIGN_TYPE","YEARGRP","MONTHGRP","TOTAL_BILLING");			
					$autoThArr = array("CAMPAIGN","YEAR","MONTH","TOTAL AMOUNT");
					$disableIntvSrt = true;
					break;			
				
				case 'week':
					$GRPCOL = ", SUM(BILLING_AMOUNT) AS TOTAL_BILLING, YEAR(b.TIME) AS YEARGRP, WEEK(b.TIME) AS WEEKGRP  "; $GRPCMD = " GROUP BY YEARGRP,WEEKGRP "; 						
					$autoColArr = array("CAMPAIGN_TYPE","YEARGRP","WEEKGRP","TOTAL_BILLING");			
					$autoThArr = array("CAMPAIGN","YEAR","WEEK","TOTAL AMOUNT");
					$disableIntvSrt = true;
					break;			
				
				case 'day':
					$GRPCOL = ", SUM(BILLING_AMOUNT) AS TOTAL_BILLING, YEAR(b.TIME) AS YEARGRP, DAYNAME(b.TIME) AS DAYGRP  "; $GRPCMD = " GROUP BY YEARGRP,DAYGRP "; 						
					$autoColArr = array("CAMPAIGN_TYPE","YEARGRP","DAYGRP","TOTAL_BILLING");			
					$autoThArr = array("CAMPAIGN","YEAR","DAY","TOTAL AMOUNT");
					$disableIntvSrt = true;
					break;			
				
				case 'hour':
					$GRPCOL = ", SUM(BILLING_AMOUNT) AS TOTAL_BILLING, YEAR(b.TIME) AS YEARGRP, HOUR(b.TIME) AS HOURGRP  "; $GRPCMD = " GROUP BY YEARGRP,HOURGRP "; 
					$COUNTCOL = 'DISTINCT  HOUR(b.TIME)';
					$autoColArr = array("CAMPAIGN_TYPE","YEARGRP","HOURGRP","TOTAL_BILLING");			
					$autoThArr = array("CAMPAIGN","YEAR","HOUR","TOTAL AMOUNT");
					$disableIntvSrt = true;
					break;			
							
				default:		
					$GRPCOL = ", BILLING_AMOUNT AS TOTAL_BILLING, sc.SECTION_NAME"; $GRPCMD = ""; $COUNTCOL = '*';				
								
			}
			
			
			$campaignSubQry = " JOIN ".$campaignTable." bc ON b.AD_ID=bc.ID AND b.CAMPAIGN_TYPE ='".$campaign."' LEFT JOIN sections sc ON b.BILLING_SECTION_ID=sc.ID WHERE bc.USER_ID=? ";
			
			
			////////SWITCH INTERVALS/SORTING/////////
			switch(strtolower($usort)){	
			
				case 'amount':
					$QRY = "SELECT b.* ".$GRPCOL." FROM ".$adBillTable." b ".$campaignSubQry.$GRPCMD." ORDER BY TOTAL_BILLING ".$orderFlowDb.", TIME ".$orderFlowDb;
					break;
			
				case 'ad':
					$QRY = "SELECT b.* ".$GRPCOL." FROM ".$adBillTable." b ".$campaignSubQry.$GRPCMD." ORDER BY AD_ID ".$orderFlowDb.", TIME ".$orderFlowDb;
					break;
					
				case 'section':
					$QRY = "SELECT b.* ".$GRPCOL." FROM ".$adBillTable." b ".$campaignSubQry.$GRPCMD." ORDER BY SECTION_NAME ".$orderFlowDb.", TIME ".$orderFlowDb; 
					break;
			
				case 'hour':
					$QRY = "SELECT b.*, HOUR(b.TIME) AS HOUR ".$GRPCOL." FROM ".$adBillTable." b ".$campaignSubQry.$GRPCMD." ORDER BY HOUR ".$orderFlowDb.", CAMPAIGN_TYPE ".$orderFlowDb;													
					
					if(!isset($disableIntvSrt))
						$autoColArr[] = $autoThArr[] = 'HOUR';							
					
					break;
				
				case 'day':
					$QRY = "SELECT b.*, DAYNAME(b.TIME) AS DAY ".$GRPCOL." FROM ".$adBillTable." b ".$campaignSubQry.$GRPCMD." ORDER BY DAY ".$orderFlowDb.", CAMPAIGN_TYPE ".$orderFlowDb;												
					
					if(!isset($disableIntvSrt))
						$autoColArr[] = $autoThArr[] = 'DAY';
					
					break;
				
				case 'week':
					$QRY = "SELECT b.*, WEEK(b.TIME) AS WEEK ".$GRPCOL." FROM ".$adBillTable." b ".$campaignSubQry.$GRPCMD." ORDER BY WEEK ".$orderFlowDb.", CAMPAIGN_TYPE ".$orderFlowDb;						
				
					if(!isset($disableIntvSrt))
						$autoColArr[] = $autoThArr[] = 'WEEK';
					
					break;
				
				case 'month':
					$QRY = "SELECT b.*, MONTHNAME(b.TIME) AS MONTH ".$GRPCOL." FROM ".$adBillTable." b ".$campaignSubQry.$GRPCMD." ORDER BY MONTH ".$orderFlowDb.", CAMPAIGN_TYPE ".$orderFlowDb;						
				
					if(!isset($disableIntvSrt))
						$autoColArr[] = $autoThArr[] = "MONTH";
					
					break;
				
				case 'year':
					$QRY = "SELECT b.*, YEAR(b.TIME) AS YEAR ".$GRPCOL."  FROM ".$adBillTable." b ".$campaignSubQry.$GRPCMD." ORDER BY YEAR ".$orderFlowDb.", CAMPAIGN_TYPE ".$orderFlowDb;						
					
					if(!isset($disableIntvSrt))
						$autoColArr[] = $autoThArr[] = "YEAR";
					
					break;
					
				default:
					$QRY = "SELECT b.* ".$GRPCOL." FROM ".$adBillTable." b ".$campaignSubQry.$GRPCMD." ORDER BY TIME ".$orderFlowDb.", CAMPAIGN_TYPE ".$orderFlowDb;
												
			}
			
			
			
			////////PDO QUERY//////////
			
			$sql = "SELECT COUNT(*) FROM (".$QRY.") tmp";
			$valArr = array($ownerId);
			$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
			
			if($totalRecords){								
											
				/**********CREATE THE PAGINATION***********/				
				$pageUrl = $ENGINE->get_page_path('page_url', 2);
				$qstrValArr = array($usort, $grpBy, $orderFlow);
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'qstrVal'=>$qstrValArr,'hash'=>'tab','perpage'=>30));					
				$pagination = $paginationArr["pagination"];
				$totalPage = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$pageId = $paginationArr["pageId"];

				/////////////END OF PAGINATION////////////////////
					
				///////PDO QUERY/////////
				
				$sql =  $QRY." LIMIT ".$startIndex.",".$perPage;
				$valArr = array($ownerId);
				$stmt = $dbm->doSecuredQuery($sql, $valArr);
						
				while($row = $dbm->fetchRow($stmt)){			
					
					$tdatas="";			
					
					foreach($autoColArr as $col){
						
						if($col == "CAMPAIGN_TYPE")
							$tdatas .= '<td>'.$row[$col].'</td>';	
					
						elseif($col == "TOTAL_BILLING")
							$tdatas .= '<td>'.$ENGINE->format_number($row[$col], 8, false).AD_CREDIT_SUFFIX.'</td>';	
					
						elseif($col == "SECTION_NAME")
							$tdatas .= '<td>'.$ENGINE->sanitize_slug($row[$col], array('ret'=>'url')).'</td>';	
					
						elseif($col == "TIME")
							$tdatas .= '<td>'.$ENGINE->time_ago($row[$col]).'</td>';	
					
						elseif($col == "AD_ID")
							$tdatas .= '<td>'.$INT_HASHER->encode($row[$col]).'</td>';	
					
						else
							$tdatas .= '<td>'.$row[$col].'</td>';
							
					}	
					
					$trows .= 	'<tr>'.$tdatas.'</tr>';	
					
				}
				
				foreach($autoThArr as $head)
					$th .= '<th>'.$head.'</th>';
				
				$tdatas = 	'<div class="table-responsive"><table class="table-classic">						
								'.$th.$trows.'
							</table></div>';

				$none = 'None';
				$sort_arr = $grp_arr = array("Ad", "Section", "Year", "Month", "Week", "Day", "Hour");
				$orderFlow_arr = array("Ascending", "Descending");
				$sort_arr = array_merge(array($none, "Amount"), $sort_arr);
				$grp_arr = array_merge(array($none), $grp_arr);
				
				foreach($sort_arr as $sort_by)
					$sort .= '<option '.((strtolower($usort) == strtolower($sort_by))? 'selected' : '').'>'.$sort_by.'</option>';
					
				
				foreach($grp_arr as $grp)
					$grpOpt .= '<option '.((strtolower($grpBy) == strtolower($grp))? 'selected' : '').'>'.$grp.'</option>';
					
				
				foreach($orderFlow_arr as $oflow)
					$orderFlowOpt .= '<option '.((strtolower($orderFlow) == strtolower($oflow))? 'selected' : '').'>'.$oflow.'</option>';
				
					
				$sort = '<div class="field-ctrl">
							<label>Sort by:</label>
							<select name="sort" class="field">'.$sort.'</select>
						</div>
						<div class="field-ctrl">
							<label>Group by:</label>
							<select name="group" class="field">'.$grpOpt.'</select>
						</div>
						<div class="field-ctrl">
							<label>Flow:</label>
							<select name="order_flow" class="field options-inherit-bg">'.$orderFlowOpt.'</select>
						</div>';
					
						
			}	
					

		}else
			$notLogged = $GLOBAL_notLogged;
		
		$clearRpt = '<div class="base-tb-mg">'.						
						$adsCampaign->getMeta('billing_export_link', array('specQstr' => $specQstr, 'refTitle' => $refTitle)).
						(!$isOwner? '' : '
							<button class="btn btn-danger" data-toggle="smartToggler" >Clear Billing Report</button>
							<div class="modal-drop hide has-close-btn has-caret red">
								<p> 
									ARE YOU SURE YOU WANT TO CLEAR YOUR '.strtoupper($campaign).' CAMPAIGN BILLING RECORDS?
									<br/> NOTE: This Record cannot be recovered once cleared
								</p>
								<form action="/'.$pageSelf.'" method="post">
									<button type="submit" name="clear_campaign_billing_report" class="btn btn-danger">YES</button>
									<button type="button" class="btn close-toggle">CLOSE</button>
								</form>				
							</div>'
						).'
					</div>';

		$pageTitle = ucwords($campaign).' Campaign Billing Report'.(!$isOwner? ' ('.$owner.')' : '');

		$SITE->buildPageHtml(array("pageTitle"=>$pageTitle,
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/ads-campaign" title="">Ads Campaign</a></li><li><a href="/ads-campaign'.$urlRetType.'" title="">'.ucwords(trim(str_ireplace("-", " ", $urlRetType), "/")).'</a></li><li><a href="/'.$pageSelf.'" title="">'.$pageTitle.'</a></li>'),
					"pageBody"=>'
						<div class="single-base blend">
							<div class="base-ctrl">
								'.$notLogged.											
								($sessUsername? '																							
									<div class="panel panel-limex">
										<h1 class="panel-head page-title" id="tab">'.$pageTitle.'</h1>
										<div class="panel-body">
											'.(isset($alertAdmins)? $alertAdmins : '').'
											<h2 class="">'.($pagination? '(page <span class="cyan">'.$pageId.'</span>  of '.$totalPage.')' : '').'</h2>'.
											($sort?
												(isset($pagination)? $pagination : '').'														
												<form class="inline-form inline-form-auto" action="/'.$ENGINE->get_page_path('page_url', 2, true).$specQstr.'#tab" method="post">
													'.$sort.'
													<div class="field-ctrl">
														<input type="submit" class="btn"  name="go" value="Apply" />
													</div>
												</form><br/>' : ''
											).													
											($tdatas? ((isset($clearRpt)? $clearRpt : '').$tdatas.(isset($clearRpt)? $clearRpt : '')) : '').
											(!$tdatas? '<span class="alert alert-danger">No Reports Yet</span>' : ''). 
											(isset($pagination)?  $pagination : '').'
										</div>
									</div>'			
								: '').'
							</div>
						</div>'
		));
						
		break;
		
	}
	
	
	
	
	
	
	
	
	/**CAMPAIGN TRAFFIC TRACKING**/
	case 'ctt':{
			
		/***************************BEGIN URL CONTROLLER****************************/

		if(isset($pagePathArr[1]) && $pagePathArr[1]){
		
			$pathKeysArr = array('pageUrl', 'campaign', 'ad_id', 'loc');
			$maxPath = 5;	
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;
		
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/
		
		$adsCampaign = new AdsCampaign();
		$adsCampaign->processCampaignTrafficTracking();

		break;
		
	}
	
	
	
	
		
	
	/**EXPORT CAMPAIGN DATAS**/
	case 'export-ads-campaign-datas':{
		
		/***************************BEGIN URL CONTROLLER****************************/
		
		$expectedExportItemPath_arr = array('traffic', 'billing');
		$expectedAdTypePath_arr = array("banner", "text", SESS_ALL);
		$path2 = isset($pagePathArr[1])? strtolower($pagePathArr[1]) : '';
		$path3 = isset($pagePathArr[2])? strtolower($pagePathArr[2]) : '';
		
		if(in_array($path2, $expectedAdTypePath_arr) && in_array($path3, $expectedExportItemPath_arr)){
		
			$pathKeysArr = array('pageUrl', 'campaign', 'export_item', 'item_id');
			$maxPath = 4;	
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;
		
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/
		
		
		$adsCampaign = new AdsCampaign();
		$adsCampaign->processCampaignDataExport();
		
		break;
		
	}
	
	
	
	
	
	
	
	
	
	/**PAYSTACK PAYMENT GATEWAY**/
	case 'paystack-payment-gateway':{
		
		$paystackPaymentGateway = new PaystackPaymentGateway();
		$paystackPaymentGateway->handlePaymentGatewayRequest();
		
		break;
	
	}
	
	
	/**FLUTTERWAVE PAYMENT GATEWAY**/
	case 'flutterwave-payment-gateway':{
		
		$flutterwavePaymentGateway = new FlutterwavePaymentGateway();
		$flutterwavePaymentGateway->handlePaymentGatewayRequest();
		
		break;
	
	}
	
	
	/**MONNIFY PAYMENT GATEWAY**/
	case 'monnify-payment-gateway':{
		
		$monnifyPaymentGateway = new MonnifyPaymentGateway();
		$monnifyPaymentGateway->handlePaymentGatewayRequest();
		
		break;
	
	}
	
	
	
	
	
	
	
	
	/**ADS CAMPAIGN**/
	case 'ads-campaign':{

		$tab="";


		/***************************BEGIN URL CONTROLLER****************************/

		if(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "banner-campaign"){
		
			$pathKeysArr = array('pageUrl', 'tab', 'pageId');
			$maxPath = 3;	
		
		}elseif(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "text-campaign"){
		
			$pathKeysArr = array('pageUrl', 'tab', 'pageId');
			$maxPath = 3;
			
		}else{
		
			$pathKeysArr = array();
			$maxPath = 1;
		
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		if(isset($_GET[$K="tab"]))
			$tab = $ENGINE->sanitize_user_input($_GET[$K], array('lowercase' => true));

		$isBannerCampaign = ($tab == 'banner-campaign');
		
		$adsCampaign = new AdsCampaign();
		$bannerCampaignPauseResumeUrl = $adsCampaign->getPauseNResumeURL();
		
		if(!$isBannerCampaign)
			$adsCampaign->setCampaignType($adsCampaign->getTextCampaignType());
		
		if($isBannerCampaign){
		
			$navPos = '<li><a href="/ads-campaign" class="links">Ads Campaign</a></li><li><a href="/'.$pageSelf.'" title="">Banner Campaign</a></li>';
			$title = 'Banner Campaign';
			$html =  $adsCampaign->generateCampaign();
		
		}elseif($tab == "text-campaign"){
		
			$navPos = '<li><a href="/ads-campaign" class="links">Ads Campaign</a></li><li><a href="/'.$pageSelf.'" title="">Text Campaign</a></li>';
			$title = 'Text Campaign';
			$html =  $adsCampaign->generateCampaign();
		
		}else{
			
			$textCampaignPauseResumeUrl = $adsCampaign->getPauseNResumeURL();
			$banner1Details = $adsCampaign->getBanner1Details();
			$banner2Details = $adsCampaign->getBanner2Details();
			$banner3Details = $adsCampaign->getBanner3Details();
			$adTypesDetails = $adsCampaign->getCampaignAdTypesDetails();
			$campaignMetas = $adsCampaign->getMeta('static');
			$htaCall = ($ENGINE->get_global_var('get', 'hta') == 'hta');
			
			$navPos = '<li><a href="/'.$pageSelf.'" title="">Ads Campaign</a></li>';
			$title = 'How To Advertise With Us';
			$html='';
			$tab = 'ads-campaign';
			$html2 = '<div class="hash-focus"  id="hta">
						<h3 class="page-title pan bg-limex align-c">HOW TO ADVERTISE WITH US</h3>
						<div class="base-mpad align-l">
							<p>
								Thank You for your interest in advertising with us. This community\'s advertising platform will definitely
								deliver your products and services to the door post of your target audience.
							</p>
							<p>We operate two categories of Ads Campaign on our community:</p>
							<ol class="ol">								
								<li>
									<div class="rarr"></div>
									<b><a class="links" href="/ads-campaign/banner-campaign">Banner Campaign</a></b>
								</li>
								<li>
									<div class="rarr"></div>
									<b><a class="links" href="/ads-campaign/text-campaign">Text Campaign</a></b>
								</li>								
							</ol>
							<p>
								This is to enable us  deliver your products and services to a larger population of target audience and also accommodate all our users` campaign needs.
							</p>
							<p>
								The banner campaign is divided into three classes:
								<ol class="ol" type="i">									
									<li>
										<div class="rarr"></div>
										<b>'.($banner1Dimension = $banner1Details[$K="dimension"]).'</b>
									</li>
									<li>
										<div class="rarr"></div>
										<b>'.($banner2Dimension = $banner2Details[$K]).'</b>
									</li>
									<li>
										<div class="rarr"></div>
										<b>'.($banner3Dimension = $banner3Details[$K]).'</b>
									</li>
																							
								</ol>
							</p>
							<p>	To accommodate for all these, we have put in place a <b>'.($adMatrix = $campaignMetas["adMatrix"]).'</b> matrix advertising platform on this community with a very 
								simple user interface and a sophisticated ad management system(AMS). The <b>'.$adMatrix.'</b> matrix system simply means that
								each sections of this community has <b>'.($adMatrixScale = $campaignMetas["adMatrixScale"]).'</b> possible slots for active advertisement. This implies that each sections can only display 
								a maximum of <b> '.$adMatrixScale.'</b> active ads at its peak time(that is the time when all the <b>'.$adMatrixScale.'</b> active ad slots for a section is completely filled). 					
							</p>
							<p> The  <b>'.$adMatrixScale.' </b> slots are allocated per section as follows:
								<ol class="ol" type="i">									
									<li>
										<div class="rarr"></div>
										<b>'.$banner1Dimension.' Banner Campaign Takes '.$adTypesDetails[0]["slots"].' Slots</b>
									</li>
									<li>
										<div class="rarr"></div>
										<b>'.$banner2Dimension.' Banner Campaign Takes '.$adTypesDetails[1]["slots"].' slots</b>
									</li>
									<li>
										<div class="rarr"></div>
										<b>'.$banner3Dimension.' Banner Campaign Takes '.$adTypesDetails[2]["slots"].' slots</b>
									</li>
									<li>
										<div class="rarr"></div>
										<b>Text Campaigns Takes '.$adTypesDetails[3]["slots"].' slots</b>
									</li>																						
								</ol>
							</p>
							<p>					
								The system is very impartial and ensures that ads are filled into the <b>'.$adMatrixScale.'</b> slots according to the placement time.
								Please Note that Placement time(Time that an ad was placed on a section) is different from upload time
								(Time that the ad was uploaded for campaign). Since the system fills up the '. $adMatrixScale.' available slots by sorting ads in a section base on their
								placement time, removing an ad from a section and placing it back to that same section will definitely put You at the bottom of the sort queue. 					
							</p>
							<p>					
								To place an advert on any of the sections of this community simply follow the steps outlined below.					
							</p>
							
							<ol class="ol">
								<li>
									<div class="rarr"></div>First, <a href="/signup" class="links">Register</a> an account or
									<a href="/login" class="links">Login</a> if You already have one.
								</li>
								<li>
									<div class="rarr"></div>If You are going to be running a banner campaign then create your ad banner using any graphics software such as Corel Draw or You can contact a professional
									to create it for You.The banner <b class="red">must be decent and also reflect the name of the product or service. It must
									conform with the following standards:</b>
										
									<ol class="ol" type="i">
										<li>
											<div class="rarr"></div>
											 For  <b>'.$banner1Dimension.'</b> banner class:<br/>
											Width: <b>'.$banner1Details["width"].'px</b>, Height: <b>'.$banner1Details["height"].'px</b>, Max-size: <b>'.($banner1Details["size"] / ($kb2Byte = $campaignMetas["kb2Byte"])).'Kb</b>, '.($bannerExtFmt = $campaignMetas["bannerExtFmt"]).'
										</li>
										<li>
											<div class="rarr"></div>
											 For  <b>'.$banner2Dimension.'</b> banner class:<br/>
											Width: <b>'.$banner2Details["width"].'px</b>, Height: <b>'.$banner2Details["height"].'px</b>, Max-size: <b>'. ($banner2Details["size"] / $kb2Byte).'Kb</b>, '. $bannerExtFmt.'
										</li>
										<li>
											<div class="rarr"></div>
											 For  <b>'.$banner3Dimension.'</b> banner class:<br/>
											Width: <b>'. $banner3Details["width"].'px</b>, Height: <b>'. $banner3Details["height"].'px</b>, Max-size: <b>'. ($banner3Details["size"] / $kb2Byte).'Kb</b>, '. $bannerExtFmt.'
										</li>
									</ol>																											
								</li>
								<li>
									<div class="rarr"></div>If You are going to be running a text campaign then <b class="red"> the Link Text must be decent and also reflect the name of the product or service.</b>
								</li>
								<li>
									<div class="rarr"></div>Now if You are running a banner campaign, navigate to <a href="/ads-campaign/banner-campaign" class="links">banner campaign</a> and click on 
									<a href="/'.$adsCampaign->getMeta('upload_slug', array('type' => 'banner')).'" class="links">Upload New Banner Ad</a>, then select the <b>banner size</b> and the <b>banner</b> You created in step 2 and fill in 
									the <b>landing page</b>(that is the link or website where You want people to be sent when they click the 
									<b>banner</b> You are uploading) and hit the submit button to upload the ad.								
								</li>
								<li>
									<div class="rarr"></div>If You are running a text campaign, navigate to <a href="/ads-campaign/text-campaign" class="links">text campaign</a> and click on 
									<a href="/'.$adsCampaign->getMeta('upload_slug', array('type' => 'text')).'" class="links">Upload New Text Ad</a>, then fill in the <b>link text</b>(that is whatever You want people to read on the link)
									and also the <b>landing page</b>(that is the link or website where You want people to be sent when they click the <b>link text</b>) and hit the submit button to upload the ad.								
								</li>
								<li>
									<div class="rarr"></div>Grab a cup of juice and relax while your ad awaits approval from the relevant teams concerned.
									While waiting for your ad to be approved, You can begin to place your ad into any section of this community where You want it to 
									appear once it passes all the relevant requirements. This is very important as it may likely put You in a favourable position on the sort queue and
									also increase the chances of your ad appearing sooner on that section.
								
								</li>
								<li>
									<div class="rarr"></div>Once your ad is approved, then purchase advertising credits of at least <b class="text-sticker text-sticker-sm text-sticker-success">'.($currencySymbol = $campaignMetas["currencySymbol"]).$ENGINE->format_number($campaignMetas["minAdDeposit"], 0, false).$campaignMetas["currency"].'</b>
									'.($htaCall? '' : $adsCampaign->getMeta('credit_purchase_btn')).'<br/> 
									once your Payment is successfully completed, You will be credited with advertising credits equivalent to the amount purchased.									
								</li>
								
								<li>
									<div class="rarr"></div>Once You receive the advertising credits, then check your <b class="red">campaign status</b> accordingly; <a href="/ads-campaign/banner-campaign#cs" class="links">banner campaign status</a> or <a href="/ads-campaign/text-campaign#cs" class="links">text campaign status</a>
									and ensure that it\'s <b class="green">ACTIVE</b>, otherwise click <a href="'.$bannerCampaignPauseResumeUrl.'" class="links">here</a> to pause/resume banner campaign or <a href="'.$textCampaignPauseResumeUrl.'" class="links">here</a> to pause/resume text campaign. 
									Finally, just flip through the sections You placed your ad to see if it has made it to the top of the sort queue. If not just exercise some patience and your ad will
									definitely show up.
									
								</li>				
							</ol>
							<div class="text-info">
								<h3>IMPORTANT NOTES:</h3>
								<ol class="ol">
									<li>The system will start <b>billing</b> You as soon as your ad shows up in any of the sections. You will be billed for every minute your ad is active in any section with respect to the ad rate of that section
										until You vacate the slot it occupies by  either removing the ad, pausing your campaign or running out of ad credits.
									</li>	
									<li>'.$adsCampaign->getCampaignNote('re-approval').'</li>
									<li>You must have a minimum of at least <b class="text-sticker text-sticker-success">'.$currencySymbol.$ENGINE->format_number($campaignMetas["minAdPlaceCredit"], 2, false).'</b> advertising credits before the
										AMS can display your ad in any section. 
									</li>									
								</ol>
							</div>
							<p>
								Please See our estimated <a href="/estimated-ad-rates" class="links">Ad rates or cost</a> for advertising on each sections of this community.<br/>
								See also <a href="/policies/ads" class="links">our Ad policy</a>.
								<br/><b class="red">PLEASE ENSURE YOU PLACE YOUR ADS IN APPROPRIATE SECTIONS TO AVOID SUDDEN REMOVAL OR DISAPPROVAL.</b>
							</p>
							<p>
								There You go, Congratulations! You have successfully started an active ad campaign on our community.<br/>
								VERY EASY RIGHT? Tell us what You think through our <a href="/feedback" class="links">Feedback</a> channel.<br/>
								For an interactive discussion please see <a href="'.$SITE->getThreadSlug(getAutoPilotState("HOW_TO_RUN_AD_CAMPAIGN_TID")).'" class="links">How to run ad campaign on this community</a>
							</p>
							<em class="prime">
								Thank You for advertising with us.<br/>
								<br/>Warm regards,
								<br/>Adiagwai Godswill.
							<p>For '.$siteName.' Ads campaign team</p>
							</em>
						</div>			
					</div>';			
					
					////IF FILE_GETS_CONTENTS HOW TO ADVERTISE (hta)/////
					if($htaCall){
		
						echo $html2; 
						exit();
		
					}
		
		}

		if(!in_array($tab, array( 'ads-campaign', 'banner-campaign', 'text-campaign'))){
		
			header("Location:/");
			exit();
		
		}

		$SITE->buildPageHtml(array("pageTitle"=>'Ads Campaign - '.$title,
				"preBodyMetas"=>$SITE->getNavBreadcrumbs($navPos),
				"pageBody"=>$html.
					(($tab == 'ads-campaign')? '
						<div class="single-base blend">
							<div class="base-ctrl">					
								<div class="panel panel-mine-1">
									<h1 class="panel-head page-title">ADS CAMPAIGN</h1>
									<div class="panel-body">
										<div class="alert alert-warning prime">Hello '.($GLOBAL_username? $GLOBAL_username : ' Guest').'!<br/> If You are already acquainted with how to advertise on our community then select your desired campaign type below to skip the instructions that follows!</div>
										<nav class="nav-base">
											<ul class="nav nav-tabs justified justified-bom panel-style panel-fw panel-head-sm">
												<li><a href="/ads-campaign/banner-campaign" class="links prime">Banner Campaign</a></li>
												<li><a href="/ads-campaign/text-campaign" class="links prime">Text Campaign</a></li>
											</ul>
										</nav>'.						
										(isset($html2)? $html2 : '').'
									</div>
								</div>
							</div>
						</div>'
					: '')
		));
								
		break;
		
	}
	
	
	
	
	
	
	/**UPLOAD ADS**/
	case AdsCampaign::getStatic('uploadBaseSlug'):{
		
		$subNav=$formEncType="";

		/***************************BEGIN URL CONTROLLER****************************/
		
		$adsCampaign = new AdsCampaign();	

		$staticMetas = $adsCampaign->getMeta('static');

		$expectedAdUploadTargetArr = array($banner = $adsCampaign->getStatic('bannerUploadSlug'), $text = $adsCampaign->getStatic('textUploadSlug'));					
		$path2 = isset($pagePathArr[1])? strtolower($pagePathArr[1]) : '';
		
		if(in_array($path2, $expectedAdUploadTargetArr)){
		
			$pathKeysArr = array('pageUrl', ($adUploadTarget = 'adUploadTarget'));
			$maxPath = 3;
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;
		
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/
		
		$adUploadTarget = isset($_GET[$adUploadTarget])? strtolower($_GET[$adUploadTarget]) : '';					
												
		if($adUploadTarget == $banner){
			
			$pageTitle = 'Upload Campaign Banner';
			$subNav = '<li><a href="/ads-campaign/banner-campaign">Banner Campaign</a></li>';
			$formEncType = 'enctype="multipart/form-data"';
			
			$responseMetaArr = $adsCampaign->processAdUploadPageRequest();

			$pageContent = '
			<div class="field-ctrl">
				<label>BANNER SIZE: <span class="red">(in pixels)</span></label>
				'.$responseMetaArr["bannerDimensionSelectMenu"].'
			</div>				
			<div class="field-ctrl">
				<label>AD BANNER:<br/>'.$adsCampaign->getMeta('adbanner_upload_tip').'</label>
				<input type="file" name="ads_image" value="" class="field upload-field" />
			</div>
			<div class="field-ctrl">
				<label>LANDING PAGE URL:<br/>'.$adsCampaign->getMeta().'</label>
				<input class="field" type="url" name="landing_page" value="'.(!($K=$responseMetaArr["landPage"])? 'http://' : '').$K.'" placeholder="Enter the link where you want people to be sent when they click your Ads Banner "  />
			</div>'.
			$responseMetaArr["instantApprovalBtn"].' 
			<div class="field-ctrl">
				<input type="submit" class="form-btn btn-success" name="upload_ads" value="SUBMIT"  />
			</div>';
			
			$uploadErr = $responseMetaArr["uploadError"].$responseMetaArr["uploadLimitError"];


		}elseif($adUploadTarget == $text){
			
			$pageTitle = 'Upload Text Ads';
			$subNav = '<li><a href="/ads-campaign/text-campaign" title="">Text Campaign</a></li>';
			
			$adsCampaign->setCampaignType($adsCampaign->getTextCampaignType());
			$responseMetaArr = $adsCampaign->processAdUploadPageRequest();

			$pageContent = '
			<div class="field-ctrl">
				<label>LINK TEXT:<br/>
				'.$adsCampaign->getMeta('linktext_upload_tip').'</label>
				<textarea name="linkText" maxLength="'.$adsCampaign->getMeta("static")["maxTextAdLink"].'" data-field-count="true" class="field">'.$responseMetaArr["linkText"].'</textarea>
			</div>
			<div class="field-ctrl">
				<label>LANDING PAGE URL:<br/>'.$adsCampaign->getMeta().'</label>
				<input class="field" type="url" name="landing_page" value="'.(!($K=$responseMetaArr["landPage"])? 'http://' : '').$K.'" placeholder="Enter the link where you want people to be sent when they click your Ads Banner "  />
			</div>'.
			$responseMetaArr["instantApprovalBtn"].' 
			<div class="field-ctrl">
				<input type="submit" class="form-btn btn-success" name="upload_ads" value="SUBMIT"  />
			</div>';					
		
		}
		
		$subNav .= '<li><a href="/'.$pageSelf.'" >'.$pageTitle.'</a></li>';

		
		$SITE->buildPageHtml(array("pageTitle"=>$pageTitle,
					"preBodyMetas"=>$SITE->getNavBreadcrumbs($subNav),
					"pageBody"=>'
					<div class="single-base blend">
						<div class="base-ctrl">
							'.$responseMetaArr["notLogged"].
							($sessUsername? '
								<div class="panel panel-limex">				
									<h1 class="panel-head page-title">'.$pageTitle.'</h1>
									<div class="panel-body">'.
										$responseMetaArr["alert"].
										(isset($uploadErr)? $uploadErr : '').'  
										<form class="" method="post" action="/'.$pageSelf.'" '.$formEncType.'>
											'.$pageContent.'	
										</form>
									</div>
								</div>'
							: '').'
						</div>
					</div>'
		));
						
		break;
			
	}
	
	

	
	
	
	
	
	/**EDIT ADS**/
	case AdsCampaign::getStatic('editBaseSlug'):{
		
		$alert=$subNav=$formEncType="";

		if($sessUsername){
			
			/***************************BEGIN URL CONTROLLER****************************/
			
			$adsCampaign = new AdsCampaign();	

			$staticMetas = $adsCampaign->getMeta('static');

			$expectedAdEditTargetArr = array($banner = $staticMetas['bannerEditSlug'], $text = $staticMetas['textEditSlug'], $landpage = $staticMetas['landPageEditSlug']);					
			$path3 = isset($pagePathArr[2])? strtolower($pagePathArr[2]) : '';
			
			if(in_array($path3, $expectedAdEditTargetArr)){
			
				$pathKeysArr = array('pageUrl', ($ad = 'ad'), ($adEditTarget = 'adEditTarget'));
				$maxPath = 3;
			
			}else{
			
				$pathKeysArr = array();
				$maxPath = 0;
			
			}

			$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

			/*******************************END URL CONTROLLER***************************/
			
			$adEditTarget = isset($_GET[$adEditTarget])? strtolower($_GET[$adEditTarget]) : '';
			
			if(isset($_GET[$ad])){
			
				$adIdEnc = $_GET[$ad];
				$adId = $ENGINE->sanitize_number($INT_HASHER->decode($adIdEnc));
			
			}

			//////////GET FORM-GATE RESPONSE//////////	
			list($gateResponse, $formId) = $SITE->formGateRefreshResponse(true);	
			
			
			if($adId){								

				if($adEditTarget == $banner){
					
					$pageTitle = 'Change Ad Banner';
					$subNav = '<li><a href="/ads-campaign/banner-campaign">Banner Campaign</a></li>';
					$formEncType = 'enctype="multipart/form-data"';

					$responseMetaArr = $adsCampaign->editAdBanner($adId);				 
					$alert = $responseMetaArr["alert"];

					$pageContent = '
					<div class="field-ctrl">
						<label>BANNER SIZE: <span class="red">(in pixels)</span></label>
						'.$responseMetaArr["bannerDimensionSelectMenu"].'
					</div>								
					<div class="field-ctrl">
						<label>UPLOAD YOUR NEW BANNER:<br/>'.$adsCampaign->getMeta('adbanner_upload_tip').'</label>				
						<input class="field upload-field" type="file" name="ads_image" />					
						<input type="hidden" name="ad_id" value="'.(isset($adIdEnc)? $adIdEnc : '').'" />
					</div>'.
					$responseMetaArr["instantApprovalBtn"].'
					<div class="field-ctrl">
						<div class="btn-ctrl sm-mt-0-2">
							<input type="submit" class="form-btn btn-success" name="change_banner"  value="change"  />
						</div>
					</div>';

					$uploadErr = $responseMetaArr["uploadError"].$responseMetaArr["uploadLimitError"];


				}elseif($adEditTarget == $landpage){

					$pageTitle = 'Edit Banner Ad Landing Page';
					
					$responseMetaArr = $adsCampaign->editBannerAdLandPage($adId);				 
					$alert = $responseMetaArr["alert"]; 
					$rawLand = $responseMetaArr["rawLand"];
					$postedLand = $responseMetaArr["postedLand"];

					$pageContent = '
					<div class="field-ctrl">
						<label>YOUR NEW LANDING PAGE URL:<br/>'.$adsCampaign->getMeta().'</label>
						<input class="field" type="text" name="landing_page" placeholder="Enter here the new landing page url" value="'.($postedLand? $postedLand : $rawLand).'" />
						<input type="hidden" name="ad_id" value="'.(isset($adIdEnc)? $adIdEnc : '').'" />
						<div class="btn-ctrl">
							<input type="submit" class="form-btn btn-success" name="change_Landpage" value="change" />
						</div>
					</div>';


				}elseif($adEditTarget == $text){
					
					$pageTitle = 'Edit Text Ad Details';
					$subNav = '<li><a href="/ads-campaign/text-campaign" title="">Text Campaign</a></li>';
					
					$adsCampaign->setCampaignType($adsCampaign->getTextCampaignType());
					$responseMetaArr = $adsCampaign->editTextAdDetails($adId);				 
					$alert = $responseMetaArr["alert"]; 
					$rawLand = $responseMetaArr["rawLand"]; 
					$postedLand = $responseMetaArr["postedLand"];
					$oldLinkText = $responseMetaArr["oldLinkText"];

					$pageContent = '
					<div class="field-ctrl">
						<label>LINK TEXT:<br/>'.$adsCampaign->getMeta('linktext_upload_tip').'</label>
						<textarea name="linkText" maxLength="'.$adsCampaign->getMeta("static")["maxTextAdLink"].'" data-field-count="true" class="field" id="tl">'.$oldLinkText.'</textarea>
					</div>
					<div class="field-ctrl">
						<label>LANDING PAGE URL:<br/>'.$adsCampaign->getMeta().'</label>
						<input class="field" id="lp" type="text" name="landing_page" placeholder="Enter here the new landing page url" value="'.($postedLand? $postedLand : $rawLand).'" />
						<input type="hidden" name="ad_id" value="'.(isset($adIdEnc)? $adIdEnc : '').'" />
					</div>'.
					$responseMetaArr["instantApprovalBtn"].'
					<div class="field-ctrl">
						<div class="btn-ctrl">
							<input type="submit" class="form-btn btn-success" name="update_details"  value="change"  />
						</div>
					</div>';					
				
				}
				
				$subNav .= '<li><a href="/'.$pageSelf.'" >'.$pageTitle.'</a></li>';

			}else
				$alert = '<span class="alert alert-danger">Sorry it seems this link has been altered, no Ad was specified<br/>Please go back and try again</span>';

						
		}else
			$notLogged = $GLOBAL_notLogged;
		

			

		$SITE->buildPageHtml(array("pageTitle"=>$pageTitle,
					"preBodyMetas"=>$SITE->getNavBreadcrumbs($subNav),
					"pageBody"=>'			
						<div class="single-base blend">
							<div class="base-ctrl">
								'.(isset($notLogged)? $notLogged : '').(isset($alert)? $alert : '').								
								(($sessUsername && !$alert)? '																	
									<div class="panel panel-limex">
										<h1 class="panel-head page-title">'.$pageTitle.'</h1>
										<div class="panel-body">'. 
											($alertAdmins=$responseMetaArr["alertAdmins"]).
											$adsCampaign->getCampaignDetailsCard(array("uid"=>$responseMetaArr["adOwnerId"], "adId"=>$adId, "adIdEnc"=>$adIdEnc)).'
											<div class="modal-drop">'.																										
												(isset($gateResponse)? $gateResponse : '').
												(isset($uploadErr)? $uploadErr : $responseMetaArr["error"]).												
												(isset($alertAdmins)? $alertAdmins : '').' 						
												<form class="" method="post" action="/'.$pageSelf.'" '.$formEncType.'>
													'.$pageContent.'
												</form>
											</div>
										</div>
									</div>'				
								: '').'
							</div>
						</div>'
		));
						
		break;
			
	}
	
	

	
	
	
	
	
	
	
	
	
	/**FEEDBACK**/
	case 'feedback':{
		
		$alertUser=$notLogged=$pmEncodedSessUsername=$pmEncodedFeedback="";

		$minFn=8;
		$maxFn=100;

		$rqd = '<span class="asterix">*</span>';
		$fErr = 'field-error';

		/////GET THE REPORT INFORMATIONS FROM THE QUERY STRING///

		if(isset($_GET[$K="pid"]))	
			$postId = $ENGINE->sanitize_number($_GET[$K]);
			
		//////////GET FORM-GATE RESPONSE//////////	
		$alertUser = $SITE->formGateRefreshResponse();						

		///////IF FEEDBACK IS SUBMITTED THEN PROCEED AS FOLLOWS///////

		////////HANDLE FOR USERS AND GUESTS/////
		if(isset($_POST['fbs'])){

			$fbc = $ENGINE->sanitize_user_input($_POST["fbc"]);
			$fn = isset($_POST[$K="fn"])? ucwords($ENGINE->sanitize_user_input($_POST[$K])) : '';
			$em = isset($_POST[$K="em"])? $ENGINE->sanitize_user_input($_POST[$K]) : '';
			
			if($fbc && (($fn && $em) || $sessUsername)){

				if((mb_strlen($fn) >= $minFn && mb_strlen($fn) <= $maxFn) || $sessUsername){
				
					///////////PDO QUERY////////
					if($sessUsername){
								
						$sql = "SELECT ID FROM feedbacks WHERE USER_ID = ? AND FEEDBACK_CONTENT = ? LIMIT 1";
						$valArr = array($sessUid, $fbc);
						$fn = $sessUsername;
						$em = $ACCOUNT->loadUser($sessUsername)->getEmail();

					}else{

						$sql = "SELECT ID FROM feedbacks WHERE EMAIL = ? AND FULL_NAME=? AND FEEDBACK_CONTENT = ? LIMIT 1";
						$valArr = array($em, $fn, $fbc);

					}
					
					$stmt = $dbm->doSecuredQuery($sql, $valArr);
					$row = $dbm->fetchRow($stmt);
					
					if(empty($row)){
							
						////////////PUT THE FEEDBACK INTO DB/////
									
						///////////PDO QUERY////////
						if($sessUsername){

							$sql = "INSERT INTO feedbacks (USER_ID, FEEDBACK_CONTENT, TIME) VALUES(?,?,NOW())";
							$valArr = array($sessUid, $fbc);
							$stmt = $dbm->doSecuredQuery($sql, $valArr);	
				
						}else{

							$sql = "INSERT INTO feedbacks (USER_ID, FULL_NAME, EMAIL, FEEDBACK_CONTENT, TIME) VALUES(?,?,?,?,NOW())";		
							$valArr = array(0, $fn, $em, $fbc);
							$stmt = $dbm->doSecuredQuery($sql, $valArr);

						}
						//////////SEND EMAIL TO ALL ADMINS //////
								
						$adminsEmails = $SITE->getAdmins("email");		
										
						$to = $adminsEmails;

						$subject = 'New Feedback [To All Admins@'.$siteName.']';
								
						$message = ($sessUsername? '<a href="'.$siteDomain.'/'.$sessUsernameSlug.'">' : $fn).' submitted the following feedback\n\n\n<div '.EMS_PH_PRE.'PLAIN_BOX>'.$fbc.'</div>\n\n All Administrators should please review the feedback.\n\n Thank You.\n\n\n\n';
									
						$footer = 'NOTE: This email was sent to you because you are an Administrator at <a href="'.$siteDomain.'">'.$siteDomain.'</a> \nPlease kindly ignore this email if otherwise.';
						 
						$SITE->sendMail(array('to'=>$to, 'senderName'=>'Webmaster & '.$fn, 'subject'=>$subject, 'body'=>$message, 'footer'=>$footer));
						
						///////SEND PM TO ALL ADMINS/////////
										
						///////////FORMAT LINKS IN PM///////
						
						$pmEncodedSessUsername = $sessUsername? '[a '.$sessUsernameSlug.']'.$sessUsername.'[/a]' : '[col="#0000FF"]'.$fn.'[/col]';
						
						$pmEncodedFeedback = '[p][col="#FF0000"]'.$fbc.'[/col][/p]';
										
						$subject = 'New Feedback [To All Admins@'.$siteName.']';

						$message = $pmEncodedSessUsername.' has given the following feedback: [br/]'.$pmEncodedFeedback;										
						
						$adminsIds = $SITE->getAdmins("id");

						if(!$adminsIds)
							$adminsIdsArr = array();

						else
							$adminsIdsArr = explode(",", $adminsIds);
							
						
						foreach($adminsIdsArr as $adminId)
							$SITE->sendPm($sessUid, $adminId, $subject, $message);
					

						if($sessUsername){
							
							//////SEND APPRECIATION PM///////

							//sender => "Webmaster"
							$senderId = 0;

							$subject = 'Appreciation Notes';

							$message = $pmEncodedSessUsername.', We sincerely appreciate you for taking out time to give us a feedback. This act of yours will definitely enable us serve you better. Your feedback:<br/>'.$pmEncodedFeedback.'<br/> has been received and will be reviewed by us shortly Thank you';
										
							$SITE->sendPm($senderId, $sessUid, $subject, $message);
													
						}
				
					}	

					///SEND APPRECIATION EMAIL//////

					$to = $em;

					$subject = 'Appreciation Notes';
							
					$message = ($sessUsername? '<a href="'.$siteDomain.'/'.$sessUsernameSlug.'">'.$sessUsername.'</a>' : $fn).'\n We sincerely appreciate you for taking out time to give us a feedback.\n This act of yours will definitely enable us serve you better.\n\nYour feedback has been received and will be reviewed by us shortly.\n\n\n\n';
								
					$footer = 'NOTE: This email was sent to you because you submitted a feedback at <a href="'.$siteDomain.'">'.$siteDomain.'</a> using this email address \nPlease kindly ignore this email if otherwise.';
							 
					$SITE->sendMail(array('to'=>$to.'::'.$fn, 'subject'=>$subject, 'body'=>$message, 'footer'=>$footer));
					
					$alertUser = '<span class="alert alert-success">'.($sessUsername? $GLOBAL_sessionUrl_unOnly : '<b class="blue">'.$fn.'</b>').' thank you for taking time to give us a feedback<br/> Your feedback has been submitted successfully.<br/>
									<br/>It will be reviewed by us shortly. Thank you </span>';		
										
					////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
					$SITE->formGateRefresh($alertUser);
							
				}else
					$alertUser = '<span class="alert alert-danger">Full Name must be between '.$minFn.' - '.$maxFn.' characters</span>';		
			 
			}else
				$alertUser = '<span class="alert alert-danger">Please kindly fill out '.($sessUsername? 'the message field' : 'all the fields').' to proceed</span>';		
			
		}

		$SITE->buildPageHtml(array("pageTitle"=>'Support/Feedback',			   
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/feedback" title="Support/feedback" >Suport/Feedback</a></li>'),
					"pageBody"=>'
						<div class="single-base blend">
							<div class="base-ctrl base-rad">
								<h1 class="page-title pan bg-dcyan">SUPPORT/FEEDBACK</h1>
								<div class="base-container">
									<br/>'.$alertUser.'														
									<form method="post" action="/feedback" class="clear">'.
										(!$sessUsername? '
											<div class="field-ctrl">
												<label>FULL NAME'.((isset($_POST["fn"]) && !$_POST["fn"])? $rqd : '').':</label>
												<input maxLength="'.$maxFn.'" data-minlength="'.$minFn.'" data-field-count="true" type="text" class="field '.((isset($_POST["fn"]) && !$_POST["fn"])? $fErr : '').'" name="fn" value="'.(isset($_POST["fn"])? $_POST["fn"] : '').'" placeholder="Full Name" />
											</div>
											<div class="field-ctrl">
												<label>EMAIL'.((isset($_POST["em"]) && !$_POST["em"])? $rqd : '').':</label>
												<input type="email" class="field '.((isset($_POST["em"]) && !$_POST["em"])? $fErr : '').'" name="em" value="'.(isset($_POST["em"])? $_POST["em"] : '').'" placeholder="E-mail" />
											</div>' : ''
										).'
										<div class="field-ctrl">
											<label>MESSAGE'.((isset($_POST["fbc"]) && !$_POST["fbc"])? $rqd : '').':</label>
											<textarea class="field '.((isset($_POST["fbc"]) && !$_POST["fbc"])? $fErr : '').'" name="fbc" placeholder="Please Briefly Describe Your Issue">'.(isset($_POST["fbc"])? $_POST["fbc"] : '').'</textarea>
										</div>
										<div class="field-ctrl btn-ctrl pull-l">
											<input class="form-btn" type="submit"  name="fbs" value="SUBMIT"  />
										</div>
									</form>					
								</div>
							</div>
						</div>'
		));

		break;
			
	}
	
	
	
	
	
	/**FOLLOWED MEMBERS**/
	case 'followed-members':{

		$notLogged=$lt="";

		/***************************BEGIN URL CONTROLLER****************************/
		
		if(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "topics"){
			
			$pathKeysArr = array('pageUrl', 'topics', 'pageId');				
			$topicTab = true;
			$maxPath = 3;
				
		}elseif(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "posts"){
			
			$pathKeysArr = array('pageUrl', 'posts', 'pageId');					
			$postTab = true;
			$maxPath = 3;
				
		}else{
			
			$pathKeysArr = array();
			$maxPath = 0;
			
		}	
		
		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		if($sessUsername){						
			
			//////UPDATE OLD_FM_COUNTER IN users//////
			$ACCOUNT->updateUser($sessUsername, $cols='OLD_FM_COUNTER=NOW()');
			
			////////////FETCH THE MEMBERS FOLLOWED FROM DB////////				
			$totalRecords =  $FORUM->followedmembersHandler(array('uid'=>$sessUid,'count'.(isset($postTab)? 'Post' : 'Topic' )=>true));																
			
			if($totalRecords){
		
				/**********CREATE THE PAGINATION************/				
				$pageUrl = 'followed-members/'.(isset($topicTab)? 'topics' : 'posts');											
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'hash'=>'ptab'));											
				$pagination = $paginationArr["pagination"];
				$totalPage = $paginationArr["totalPage"];
				$n = $paginationArr["perPage"];
				$i = $paginationArr["startIndex"];
				$page = $paginationArr["pageId"];

				///////DISPLAY CURRENT AND TOTAL PAGE NUMBER//////////////////// 
		
				$cpop = $totalRecords? '<div class="cpop"> (page <span class="cyan"> '.$page.'</span> of '.$totalPage.')</div>' : '';
				
				$messages = $FORUM->followedMembersHandler(array('uid'=>$sessUid,'get'.(isset($postTab)? 'Post' : 'Topic' )=>true,'i'=>$i,'n'=>$n));					
				
			}else							
				$messages = '<span class="alert alert-danger">Sorry '.$GLOBAL_sessionUrl_unOnly.', '.($FORUM->followedmembersHandler(array('uid'=>$sessUid,'count'=>true))? 'there are no latest posts from members you follow' : ' you are not following any member yet').'</span>';
		
		}else
			$notLogged = $GLOBAL_notLogged;

		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
						
		$SITE->buildPageHtml(array("pageTitle"=>'Followed Members',
						"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">Followed Members</a></li>'),
						"pageBody"=>'
						<div class="single-base blend">
							<div class="base-ctrl">								
								<div class="row">
									'.$notLogged.'															
									<h1 class="page-title pan bg-limex">Followed Members:</h1>'									
									.$pageTopAds.
									($sessUsername? '					
										<nav class="nav-base clear">
											<ul class="nav nav-tabs justified justified-center" data-open-active-mob="true">
												<li class="'.(isset($postTab)? 'active' : '').'"><a href="/followed-members/posts" class="links">Posts</a></li>
												<li class="'.(isset($topicTab)? 'active' : '').'"><a href="/followed-members/topics" class="links">Topics</a></li>
											</ul>
										</nav>	
										<div class="'.$leftWidgetClass.' base-container base-b-pad">
											<div class="base-rad">																		
												<h1 class="page-title pan bg-limex">Latest '.(isset($topicTab)? 'Topics' : 'Posts').' From Members You Follow:</h1>'.									
												(isset($pagination)? $cpop : '').
												(isset($pagination)? $pagination : '').								
												(isset($messages)? $messages  : '').								
												(isset($pagination)? $pagination : '').'																		
											</div>												
										</div>' : ''											
									).$rightWidget.'											
								</div>
								<div class="">
									'.$pageBottomAds.'
								</div>			
							</div>
						</div>'
		));
						
		break;

	}
	
	
	
	
	
	/**FOLLOWED SECTIONS**/
	case 'followed-sections':{

		$notLogged=$messages='';			
				
		/***************************BEGIN URL CONTROLLER****************************/

		if(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "topics"){
		
			$pathKeysArr = array('pageUrl', 'topics', 'pageId');			
			$topicTab = true;
			$maxPath = 3;
			
		}elseif(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == "posts"){
		
			$pathKeysArr = array('pageUrl', 'posts', 'pageId');					
			$postTab = true;
			$maxPath = 3;	
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;
		
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/			

		if($sessUsername){

			//////////GET FORM-GATE RESPONSE//////////	
			$alert = $SITE->formGateRefreshResponse();	

			//////////IF SECTION FOLLOW IS SUBMITTED////////////

			if(isset($_POST["follow_section"])){
				
				if(!empty($_POST["section"])){
		
					$selectedSections = $_POST["section"];
					$selectedSectionCount = count($selectedSections);
		
					foreach($selectedSections as $sectionName){				

						if($sectionName){
						
							$sid = (strtolower($sectionName) == "all sections")? 0 : $SITE->sectionIdToggle($sectionName);
							$FORUM->followedSectionsHandler(array('uid'=>$sessUid, 'sid'=>$sid, 'action'=>'follow'));
								
						}elseif($selectedSectionCount > 1) 
							continue;
		
					}
								
					////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
					$SITE->formGateRefresh($alert);
		
				}else	
					$alert = '<div class="alert alert-danger "'._TIMED_FADE_OUT.'>Please select a section</div>';
				
			}


			////////////GET THE TOTAL SECTIONS YOU ARE FOLLOWING ALREADY/////////////

			$allSections=$urSections=$urSectionsExtended="";				
			
			$fsidSubQry = "SELECT SECTION_ID FROM section_follows  WHERE (USER_ID = ? AND STATE=1)";													
			$copyTotalSections = $totalSections = $FORUM->followedSectionsHandler(array('uid'=>$sessUid,'count'=>true));								
					
			//////////GET ALL THE SECTIONS YOU CURRENTLY FOLLOW///////////
		
			///////////PDO QUERY///////////
		
			$sql = "SELECT ID, SECTION_NAME FROM sections WHERE ID IN(".$fsidSubQry.")";
			$valArr = array($sessUid);
			$stmt = $dbm->doSecuredQuery($sql, $valArr);
			$c = 1; $maxVis = 10;	
				
			while($row = $dbm->fetchRow($stmt)){
				
				$sectionId = $row["ID"];
				$sectionName = $row["SECTION_NAME"];
				
				$perSect = '<span id="sf-'.$sectionId.'-base">'.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).' <a class="follow_scat"  href="/section-follows/unfollow/'.$sectionId.'/?_rdr='.$GLOBAL_rdr.'"  data-sid="'.$sectionId.'"  data-action="unfollow" data-base="sf-'.$sectionId.'-base" ><img  class="delete" src="'.$mediaRootFav.'delete.png"  alt="delete"  title="Un-follow this section"  /></a> | </span>';
				($c > $maxVis)? ($urSectionsExtended .= $perSect) : ($urSections .= $perSect);
				$c++;	
				
			}
			

			if($urSections && $totalSections > $maxVis)
				$urSections = '<div id="all-fs-base">| '.$urSections.'<span class="hide">'.$urSectionsExtended.'</span><button data-toggle-attr="text|less" data-target-prev="true" class="btn btn-xs btn-sc" data-toggle="smartToggler" >more</button>
								<br/><a role="button" href="/section-follows/unfollow/all/?_rdr='.$GLOBAL_rdr.'" data-sid="all" data-action="unfollow"  data-base="all-fs-base" class="follow_scat inline-block btn btn-info" title="Unfollow All Sections" >unfollow all</a></div>';

			elseif($urSections)
				$urSections = '<div>| '.$urSections.'</div>';

			$totalSections = '<span id="fsc" class="cyan">'.$totalSections.'</span>';					
			
			///////////POPULATE SECTION DROP DOWN MENU WITH ONLY THE SECTIONS YOU ARE NOT FOLLOWING//////	

			///////////PDO QUERY/////////////
			
			$sql = "SELECT SECTION_NAME FROM sections WHERE (ID NOT IN(".getExceptionParams('sidsnofollow', 0).") AND ID NOT IN(".$fsidSubQry.")) ORDER BY SECTION_NAME ";
			$valArr = array($sessUid);
			$stmt = $dbm->doSecuredQuery($sql, $valArr);
					
			while($row = $dbm->fetchRow($stmt)){
				
				$allSections .= '<option>'.$row["SECTION_NAME"].'</option>';	
				
			}		

			$allSections = '<option value="">--Select a Section--</option><option>All Sections</option>'.$allSections;	
					
			$totalRecords = $FORUM->followedSectionsHandler(array('uid'=>$sessUid,'count'.(isset($postTab)? 'Post' : 'Topic' )=>true));
									
			/////UPDATE OLD_FS_COUNTER IN users////////
			$ACCOUNT->updateUser($sessUsername, $cols='OLD_FS_COUNTER=NOW()');				

			///////////GET THE DETAILS//////

			if($totalRecords){	
				
				/**********CREATE THE PAGINATION************/					
				$pageUrl = 'followed-sections/'.(isset($topicTab)? 'topics' : 'posts');									
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'hash'=>'ptab'));
				$pagination = $paginationArr["pagination"];
				$totalPage = $paginationArr["totalPage"];
				$n = $paginationArr["perPage"];
				$i = $paginationArr["startIndex"];
				$page = $paginationArr["pageId"];

				///////DISPLAY CURRENT AND TOTAL PAGE NUMBER//////////////////// 
		
				$cpop = $totalRecords? '<div class="cpop"> (page <span class="cyan"> '.$page.'</span> of '.$totalPage.')</div>' : '';
				
				$messages = $FORUM->followedSectionsHandler(array('uid'=>$sessUid,'get'.(isset($postTab)? 'Post' : 'Topic' )=>true,'i'=>$i,'n'=>$n));					
				
			}else							
				$messages = '<span class="alert alert-danger">Sorry '.$GLOBAL_sessionUrl_unOnly.', '.((isset($copyTotalSections) && $copyTotalSections)? 'there are no new topics or posts from any of the sections you follow' : 'you are not following any section yet').'</span>';
		}else
			$notLogged = $GLOBAL_notLogged;

		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
						

		$SITE->buildPageHtml(array("pageTitle"=>'Followed Sections',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">Followed Sections</a></li>'),
					"pageBody"=>'
					<div class="single-base blend">
						<div class="base-ctrl">					
							<div class="row">
								'.$notLogged.
								($sessUsername? '										
									<div class="">									
										<h1 class="page-title pan bg-limex">SECTIONS YOU FOLLOW: (<span class="cyan">'.$totalSections.'</span>)</h1>
										<div class="base-pad" >'.$urSections.'</div><hr/>
										<div class="base-pad col-sm-w-8 col-lg-w-5">
											'.$alert.'
											<form class="" method="post" action="/'.$pageSelf.'">
												<div class="field-ctrl">									
													<label>FOLLOW A SECTION:</label>
													<select class="field" name="section[]" multiple="multiple">
														'.$allSections.'
													</select>
												</div>
												<div class="field-ctrl">
													<input type="submit" name="follow_section" class="form-btn btn-success" value="Follow" />
												</div>
											</form>
										</div>						
									</div>' : ''		
								).$pageTopAds.
								($sessUsername? '					
									<nav class="nav-base">
										<ul class="nav nav-tabs justified justified-center" data-open-active-mob="true">
											<li class="'.(isset($postTab)? 'active' : '').'"><a href="/followed-sections/posts" class="links">Posts</a></li>
											<li class="'.(isset($topicTab)? 'active' : '').'"><a href="/followed-sections/topics" class="links">Topics</a></li>
										</ul>
									</nav>											
									<div class="'.$leftWidgetClass.' base-container base-b-pad">
										<div class="base-rad">																		
											<h1 class="page-title pan bg-limex">Latest '.(isset($topicTab)? 'Topics' : 'Posts').' From Sections You Follow:</h1>'.									
											(isset($pagination)? $cpop : '').
											(isset($pagination)? $pagination : '').								
											(isset($messages)? $messages  : '').								
											(isset($pagination)? $pagination : '').'																		
										</div>												
									</div>' : ''
								).										
								$rightWidget.'																	
							</div>
							<div class="">
								'.$pageBottomAds.'
							</div>
						</div>
					</div>'
		));
						
		break;	
				
	}
	
	
	
	
	
	/**FOLLOWED TOPICS**/
	case 'followed-topics':{
					
		$notLogged=$lt=$newPosts="";

		/***************************BEGIN URL CONTROLLER****************************/
		
		$pathKeysArr = array('pageUrl', 'pageId');
		$maxPath = 2;	
		
		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/	
			
		if($sessUsername){	

			////FETCH FOLLOWED TOPICS INFOS////////
			$totalRecords = $FORUM->followedTopicsHandler(array('uid'=>$sessUid,'count'=>true));	
											
			/////UPDATE OLD_FT_COUNTER IN users//////
			$ACCOUNT->updateUser($sessUsername, $cols='OLD_FT_COUNTER=NOW()');				
			
			///////////////GET THE TOTAL REC//////////
			
			if($totalRecords){

				/**********CREATE THE PAGINATION*************/
				$pageUrl =  'followed-topics';		
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'hash'=>'ptab'));				
				$pagination = $paginationArr["pagination"];
				$totalPage = $paginationArr["totalPage"];
				$n = $paginationArr["perPage"];
				$i = $paginationArr["startIndex"];
				$pageId = $paginationArr["pageId"];

				//////END OF PAGINATION//////
										
				$lt	= $FORUM->followedTopicsHandler(array('uid'=>$sessUid,'getTopic'=>true,'i'=>$i,'n'=>$n));	

			}else{	
				
				$lt = '<span class="alert alert-danger">Sorry '.$GLOBAL_sessionUrl_unOnly.', you are not following any topic yet</span>';
				$noneYet = true;	
				
			}

		}else
			$notLogged = $GLOBAL_notLogged;

		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
		
		function getToggle($unq='uf-drop-top'){
		
			global $GLOBAL_rdr;
		
			return  '<nav class="nav-base no-pad unf-all-cup"><ul class="nav nav-pills justified-center"><li><a href="/topic-follows/unfollow/all/?_rdr='.$GLOBAL_rdr.'" class="links" data-toggle="smartToggler" data-id-targets="'.$unq.'">Unfollow All</a></li></ul></nav>
					<div id="'.$unq.'" class="modal-drop hide red has-close-btn">
						<h5>ARE YOU SURE ?</h5>
						<a role="button" href="/topic-follows/unfollow/all/?_rdr='.$GLOBAL_rdr.'" class="btn btn-danger">Yes</a>
						<button class="btn close-toggle">No</button>
					</div>';
						
		}
		
		if(!isset($noneYet)){
		
			$unfollowAll_t = getToggle();
			$unfollowAll_b = getToggle('uf-drop-bot');
		
		}
						
		$SITE->buildPageHtml(array("pageTitle"=>'Followed Topics',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">Followed Topics</a></li>'),
					"pageBody"=>'				
					<div class="single-base blend">								
							<div class="base-ctrl">				
								<div class="row">
									'.$notLogged.
									($sessUsername? '
									<h1 class="page-title pan bg-limexx">Followed Topics(<span class="cyan" id="ft-count-disp">'.$totalRecords.'</span>)</h1>'.								
									(isset($pagination)? '<div class="cpop unf-all-cup">(page <span class="cyan">'.$pageId.'</span>  of '.$totalPage.')</div>' : '')
									: '').$pageTopAds.
									($sessUsername? '						
										<div class="'.$leftWidgetClass.' base-container base-b-pad">
											<div class="base-radx">
												'.(isset($pagination)? $pagination : '').(isset($unfollowAll_t)? $unfollowAll_t : '').
												(isset($lt)? $lt : '').																
												(isset($unfollowAll_b)? $unfollowAll_b : '').												
												(isset($pagination)? $pagination : '').'																					
											</div>										
										</div>'										
									: '').
									$rightWidget.'											
								</div>
								<div class="">
									'.$pageBottomAds.'
								</div>				
							</div>								
						</div>'
		));
						
		break;
		
	}
	

	
	
	/**TRENDING TOPIC**/
	case 'trending-topics':{						

		/***************************BEGIN URL CONTROLLER****************************/
		
		$pathKeysArr = array('pageUrl', 'pageId');
		$maxPath = 2;	
		

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		///////////////////GET TOTAL RECORDS FROM THE DB FOR PAGINATION//////////

														
		///////////PDO QUERY/////
			
		$sql = $SITE->composeQuery(array('type' => 'for_topic', 'subType' => 'record_count_trending', 'exceptions' => true));
		$totalRecords = $dbm->query($sql)->fetchColumn();

		/**********CREATE THE PAGINATION***********/			
		$pageUrl = 'trending-topics';			
		$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'perPage'=>30,'hash'=>'ptab'));					
		$pagination_topics = $paginationArr["pagination"];
		$totalPage = $paginationArr["totalPage"];
		$perPage = $paginationArr["perPage"];
		$startIndex = $paginationArr["startIndex"];
		$page = $paginationArr["pageId"];

		//////END OF PAGINATION//////////////
		 
		 
		/////DISPLAY CURRENT AND TOTAL PAGE NUMBER//////				 

		$customTotalRecords = $totalRecords? '<div class="cpop"> (page <span class="cyan"> '.$page.'</span> of '.$totalPage.')</div>' : '';
		
		//////////////IF THERE ARE NEW TOPICS THEN POPULATE THE  TOPICS USING THE PAGINATION/////////

		if($totalRecords){

			///////////PDO QUERY////

			$sql = $SITE->composeQuery(array('type' => 'for_topic', 'subType' => 'trending', 'start' => $startIndex, 'stop' => $perPage, 'exceptions' => true));

			list($topics) = $FORUM->loadThreads($sql, array(), $type="");
				

		}else					
			$topics = '<span class="alert alert-danger">Sorry there are no trending topics</span>';				
		

		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
		
		$preIcon = $SITE->getFA('fas fa-fire red', array("title"=>'Trending Topics'));
		
		$SITE->buildPageHtml(array("pageTitle"=>'Trending Topics',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">Trending Topics</a></li>'),
					"pageBody"=>'
						<div class="single-base blend">		
							<div class="base-ctrl base-b-pad">
								<h1 class="page-title pan bg-limexx">Trending Topics</h1>'.						
								(isset($pagination_topics)? $customTotalRecords : '').$pageTopAds.'											
								<div class="row base-container">
									<div class="'.$leftWidgetClass.' base-rad">
										<h1 class="metered-title">'.$preIcon.'Trending'.$preIcon.'</h1>'.						
										(isset($pagination_topics)? $pagination_topics : '').													
										(isset($topics)? $topics : '').
										(isset($pagination_topics)? $pagination_topics : '').'
									</div>'.$rightWidget.'											
								</div>
								<div class="">
									'.$pageBottomAds.$SITE->collectSiteTraffic().$SITE->displaySiteTraffic("Page").'											
								</div>	
							</div>	
						</div>'
		));
						
		break;

	}
	
	
	
	
	
	/**FEATURED HOT TOPICS**/
	case 'featured-hot-topics':{						

		/***************************BEGIN URL CONTROLLER****************************/
		
		$pathKeysArr = array('pageUrl', 'pageId');
		$maxPath = 2;	
		

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/


		///////////////////GET TOTAL RECORDS FROM THE DB FOR PAGINATION//////////

														
		///////////PDO QUERY/////
			
		$sql = $SITE->composeQuery(array('type' => 'for_topic', 'subType' => 'record_count', 'filterCnd' => 'HOT = 1', 'exceptions' => true));
		$totalRecords = $dbm->query($sql)->fetchColumn();

		/**********CREATE THE PAGINATION***********/			
		$pageUrl = 'trending-topics';			
		$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'perPage'=>30,'hash'=>'ptab'));					
		$pagination_topics = $paginationArr["pagination"];
		$totalPage = $paginationArr["totalPage"];
		$perPage = $paginationArr["perPage"];
		$startIndex = $paginationArr["startIndex"];
		$page = $paginationArr["pageId"];

		//////END OF PAGINATION//////////////
		 
		 
		/////DISPLAY CURRENT AND TOTAL PAGE NUMBER//////				 

		$customTotalRecords = $totalRecords? '<div class="cpop"> (page <span class="cyan"> '.$page.'</span> of '.$totalPage.')</div>' : '';
		

		//////////////IF THERE ARE NEW TOPICS THEN POPULATE THE  TOPICS USING THE PAGINATION/////////

		if($totalRecords){

			///////////PDO QUERY////

			$sql = $SITE->composeQuery(array('type' => 'for_topic', 'start' => $startIndex, 'stop' => $perPage, 'uniqueColumns' => '', 'filterCnd' => 'HOT = 1', 'orderBy' => 'LAST_POST_TIME DESC', 'exceptions' => true));

			list($topics) = $FORUM->loadThreads($sql, array(), $type="");
				

		}else					
			$topics = '<span class="alert alert-danger">Sorry there are no featured hot topics</span>';				
		

		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
		
		$preIcon = $SITE->getFA('fas fa-fire red', array("title"=>'Featured Hot Topics'));
		
		$SITE->buildPageHtml(array("pageTitle"=>'Featured Hot Topics',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">Featured Hot Topics</a></li>'),
					"pageBody"=>'
						<div class="single-base blend">		
							<div class="base-ctrl base-b-pad">
								<h1 class="page-title pan bg-limexx">Featured Hot Topics</h1>'.						
								(isset($pagination_topics)? $customTotalRecords : '').$pageTopAds.'											
								<div class="row base-container">
									<div class="'.$leftWidgetClass.' base-rad">
										<h1 class="metered-title">'.$preIcon.'Featured Hot'.$preIcon.'</h1>'.						
										(isset($pagination_topics)? $pagination_topics : '').													
										(isset($topics)? $topics : '').
										(isset($pagination_topics)? $pagination_topics : '').'
									</div>'.$rightWidget.'											
								</div>
								<div class="">
									'.$pageBottomAds.$SITE->collectSiteTraffic().$SITE->displaySiteTraffic("Page").'											
								</div>	
							</div>	
						</div>'
		));
						
		break;

	}
	
	
	
	
	
	
	
	/**NEW TOPIC**/
	case 'new-topics':{								
		
		/***************************BEGIN URL CONTROLLER****************************/
		
		$pathKeysArr = array('pageUrl', 'pageId');
		$maxPath = 2;	

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/
			
		///////////////////GET TOTAL RECORDS FROM THE DB FOR PAGINATION///////
						
		$assumedLatest = ' INTERVAL '.ASSUMED_INTERVAL_LATEST;
		
		///////////PDO QUERY////////////////////////////////////	
			
		$sql = $SITE->composeQuery(array('type' => 'for_topic', 'subType' => 'record_count', 'filterCnd' => '(TIME + '.$assumedLatest.') >= NOW()', 'exceptions' => true));
		$totalRecords = $dbm->query($sql)->fetchColumn();
						
		/**********CREATE THE PAGINATION*********/			
		$pageUrl = 'new-topics';			
		$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'perPage'=>30,'hash'=>'ptab'));
		$pagination_topics = $paginationArr["pagination"];
		$totalPage = $paginationArr["totalPage"];
		$perPage = $paginationArr["perPage"];
		$startIndex = $paginationArr["startIndex"];
		$page = $paginationArr["pageId"];

		///////////////END OF PAGINATION////////////
		 
		/////DISPLAY CURRENT AND TOTAL PAGE NUMBER////////				 	

		$customTotalRecords = $totalRecords? '<div class="cpop"> (page <span class="cyan"> '.$page.'</span> of '.$totalPage.')</div>' : '';
		
		////////IF THERE ARE NEW TOPICS THEN POPULATE THE  TOPICS USING THE PAGINATION/////

		if($totalRecords){

			///////////PDO QUERY////////

			$sql = $SITE->composeQuery(array('type' => 'for_topic', 'start' => $startIndex, 'stop' => $perPage, 'uniqueColumns' => '', 'filterCnd' => '(topics.TIME + '.$assumedLatest.') >= NOW()', 'orderBy' => '', 'exceptions' => true));

			list($topics) = $FORUM->loadThreads($sql, array(), $type="");
									
		}else
			$topics = '<span class="alert alert-danger">Sorry there are no new topics</span>';
		
			
		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
		
		$preIcon = '<i class="text-sticker text-sticker-sc" title="Latest Topics">new</i>';

		$SITE->buildPageHtml(array("pageTitle"=>'Latest Topics',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">Latest Topics</a></li>'),
					"pageBody"=>'
						<div class="single-base blend">
							<div class="base-ctrl base-b-pad">	
								<h1 class="page-title pan bg-limexx">Latest Topics</h1>'.								
								(isset($pagination_topics)? $customTotalRecords : '').$pageTopAds.'																					
								<div class="row base-container">
									<div class="'.$leftWidgetClass.' base-rad">						
										<h1 class="metered-title">'.$preIcon.'Latest Discussions'.$preIcon.'</h1>'.
										(isset($pagination_topics)? $pagination_topics : '').															
										(isset($topics)? $topics : '').
										(isset($pagination_topics)? $pagination_topics : '').'
									</div>'.$rightWidget.'											
								</div>
								<div class="base-container">
									'.$pageBottomAds.$SITE->collectSiteTraffic().$SITE->displaySiteTraffic("Page").'											
								</div>
							</div>
						</div>'
		));

		break;

	}
	
	
	
	
	
	
	
	
	
	
	/**LATEST POSTS**/
	case 'latest-post':{

		$messages="";

		/***************************BEGIN URL CONTROLLER****************************/
		
		$pathKeysArr = array('pageUrl', 'pageId');
		$maxPath = 2;	
		
		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/


		//////GET TOTAL RECORDS FROM THE DB FOR PAGINATION////////

		$assumedLatest = ' INTERVAL '.ASSUMED_INTERVAL_LATEST;
						
		///////////PDO QUERY/////
		
		$sql = $SITE->composeQuery(array('type' => 'for_post', 'subType' => 'record_count', 'filterCnd' => '(TIME + '.$assumedLatest.') >= NOW()', 'exceptions' => true));
		$totalRecords = $dbm->query($sql)->fetchColumn();

		/**********CREATE THE PAGINATION*************/			
		$pageUrl = 'latest-post';		
		$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'perPage'=>30,'hash'=>'ptab'));					
		$pagination = $paginationArr["pagination"];
		$totalPage = $paginationArr["totalPage"];
		$perPage = $paginationArr["perPage"];
		$startIndex = $paginationArr["startIndex"];
		$page = $paginationArr["pageId"];

		////////END OF PAGINATION///////////////
				
		$customTotalRecords = $totalRecords? '<div class="cpop"> (page <span class="cyan"> '.$page.'</span> of '.$totalPage.')</div>' : '';
		
		////////IF THERE ARE NEW POSTS THEN POPULATE THE  TOPICS USING THE PAGINATION////////////////

		if($totalRecords){							
						
			///////////PDO QUERY///////

			$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'uniqueColumns' => '', 'filterCnd' => '(posts.TIME + '.$assumedLatest.') >= NOW()', 'orderBy' => 'posts.TIME DESC', 'exceptions' => true));
			
			list($messages) = $FORUM->loadPosts($sql, array());
		
		}else	
			$messages = '<span class="alert alert-danger">Sorry there are no new posts</span>';
		
			
		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
									
		$preIcon = '<i class="text-sticker text-sticker-sc" title="Latest Posts">new</i>';
		
		$SITE->buildPageHtml(array("pageTitle"=>'Latest Posts',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">Latest Posts</a></li>'),
					"pageBody"=>'					
						<div class="single-base blend">	
							<div  class="base-ctrl base-b-pad">
								<h1 class="page-title pan bg-limexx">Latest Posts</h1>'.
								(isset($pagination)? $customTotalRecords : '').$pageTopAds.'										
								<div class="row base-container">
									<div class="'.$leftWidgetClass.'">'.
										'<h1 class="metered-title">'.$preIcon.'Latest Posts'.$preIcon.'</h1>'.						
										(isset($pagination)? $pagination : '').
										(isset($messages)? $messages : '').							
										(isset($pagination)? $pagination : '').'
									</div>'.$rightWidget.'											
								</div>										
								<div class="base-container">'.
									$pageBottomAds.$SITE->collectSiteTraffic().$SITE->displaySiteTraffic("Page").'											
								</div>											
							</div>
						</div>'
		));

		break;
			
	}
	
	
	
	
	
	
	
	/**POSTS SHARED WITH YOU**/
	case 'posts-shared-with-you':{

		$data=$messages="";
				
		/***************************BEGIN URL CONTROLLER****************************/

		$pathKeysArr = array('pageUrl', 'pageId');
		$maxPath = 2;				

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/			

		if($sessUsername){

			//////COUNT THE POSTS YOU HAVE SHARED///////

			list($totalRecords, $totalCountView) = $FORUM->getPostQUDS($sessUid, $type="shared_with_u", $retType="versions");
			

			/**********CREATE THE PAGINATION*********/				
			$pageUrl = 'posts-shared-with-you';			
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'hash'=>'ptab'));			
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageIdOut = $paginationArr["pageId"];

			//////////END OF PAGINATION///////////
			
			$followedMembersSubQry = "SELECT USER_ID FROM members_follows WHERE FOLLOWER_ID=?";
			
			if($totalRecords){
				
				///////////PDO QUERY/////////
									
				$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'primaryTable' => 'shares', 'primaryJoinCnd' => 'shares.POST_ID = posts.ID AND shares.STATE = 1',
				'uniqueColumns' => '(SELECT s.SHARER_ID FROM shares s WHERE (shares.POST_ID=s.POST_ID AND s.SHARER_ID IN ('.$followedMembersSubQry.')) ORDER BY TIME DESC LIMIT 1) AS SHARER, MAX(shares.TIME) AS STIME', 'filterCnd' => 'SHARER_ID IN('.$followedMembersSubQry.') GROUP BY shares.POST_ID', 'orderBy' => 'shares.TIME DESC'));
				
				
				///////////DISPLAY THE POSTS/////
				list($messages) = $FORUM->loadPosts($sql, array($sessUid, $sessUid), array('type'=>'shares'));
				
			}   
			
			$data = (!$messages? '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', no post has been shared with you yet</span>' : '');
			
		}else
			$notLogged = $GLOBAL_notLogged;
		
		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
		
		$preIcon = $SITE->getFA('fas fa-share-square', array("title"=>'Shared With You'));
		
		$hr = (isset($pagination) &&$pagination)? '<hr/>' : '';
							
		$SITE->buildPageHtml(array("pageTitle"=>'Posts Shared With You',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/posts-shared-with-you" title="">Posts Shared With You</a></li>'),
					"pageBody"=>'
						<div class="single-base blend">	
							<div class="base-ctrl">								
								<div class="row">'.
									(isset($notLogged)? $notLogged : '').  
									
									($sessUsername?	'								
										<h1 class="page-title pan bg-limex">POSTS SHARED WITH YOU'.$preIcon.'</h1>'.			
										($totalPage? '<div class="cpop">(page <span class="cyan">'.$pageIdOut.'</span> of '.$totalPage.')</div>' : '').'
										<div class="topic-base base-rad">'.
											(isset($totalRecords)? '<span class="black"><span class="cyan">'.$totalCountView.'</span> has been shared with you<hr/>' : '').
											$FORUM->getPostQUDS($sessUid, $type="tab", $retType="shared_with_u").'													
										</div>' : ''											
									).$pageTopAds.'																
									<div class="'.$leftWidgetClass.'">'.
										($sessUsername? 																	
											$hr.$pagination.$hr.(isset($data)? $data : '').
											(isset($messages)? $messages : '').$hr.$pagination.$hr													
										: '').'
									</div>'.$rightWidget.'											
								</div>'.$pageBottomAds.' 																					
							</div>
						</div>'
		));
						
		break;
			
	}
	
	

	
	
	
	
	
	/**POSTS YOU DOWNVOTED**/
	case 'posts-you-downvoted':{

		$data=$messages="";

		/***************************BEGIN URL CONTROLLER****************************/

		$pathKeysArr = array('pageUrl', 'pageId');
		$maxPath = 2;	
	

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/


		if($sessUsername){

			////COUNT THE POSTS YOU HAVE LIKED//////

			list($totalRecords, $totalCountView) = $FORUM->getPostQUDS($sessUid, $type="u_downvoted", $retType="versions");
			
			/**********CREATE THE PAGINATION**********/				
			$pageUrl = 'posts-you-downvoted';				
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'hash'=>'ptab'));
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageIdOut = $paginationArr["pageId"];

			///////END OF PAGINATION////////
					
			////GET LIKE AND SHARE DETAILS/////
			

			if($totalRecords){
			
				////PDO QUERY/////
			
				$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'primaryTable' => 'downvotes', 'primaryJoinCnd' => 'downvotes.POST_ID = posts.ID AND downvotes.STATE=1',
				'uniqueColumns' => 'downvotes.DOWNER_ID AS DOWNER,downvotes.TIME AS DTIME', 'filterCnd' => 'downvotes.DOWNER_ID = ?', 'orderBy' => 'downvotes.TIME DESC'));
				
				
				///////////DISPLAY THE POSTS/////
				list($messages) = $FORUM->loadPosts($sql, array($sessUid), array('type'=>'postsyoudv'));
				
		   
			}
			  
			if(!$messages)
				$data = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', you have not down voted any post yet</span>';

		}else
			$notLogged = $GLOBAL_notLogged;
		
		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
		
		$hr = (isset($pagination) && $pagination)? '<hr/>' : '';
		
		$preIcon = $SITE->getFA('far fa-thumbs-down', array("title"=>'Downvotes or Dislikes Made By You'));
		
		$SITE->buildPageHtml(array("pageTitle"=>'Posts You Downvoted',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/posts-you-downvoted" title="">Posts You\'ve Downvoted</a></li>'),
					"pageBody"=>'
						<div class="single-base blend">	
							<div class="base-ctrl">				
								<div class="row">'.
									(isset($notLogged)? $notLogged : ''). 
										
									($sessUsername?	'										
										<h1 class="page-title pan bg-limex">POSTS YOU\'VE DOWNVOTED'.$preIcon.'</h1>'.	
										($totalPage? '<div class="cpop">(page <span class="cyan">'.$pageIdOut.'</span> of '.$totalPage.')</div>' : '').'
										<div class="topic-base base-rad">'. 
											(isset($totalRecords)? '<span class="black"> You have Downvoted '.$totalCountView.'<hr/>' : '').
											$FORUM->getPostQUDS($sessUid, $type="tab", $retType="u_downvoted").'																		
										</div>' : ''
									).$pageTopAds.' 											
									<div class="'.$leftWidgetClass.'">'.
										($sessUsername?																			
											$hr.$pagination.$hr.(isset($data)? $data : '').
											(isset($messages)? $messages : '').$hr.$pagination.$hr
																	
										: '').'
									</div>'.$rightWidget.'												
								</div>'.$pageBottomAds.' 																							
							</div>
						</div>'
		));

		break;
		
	}
	
	
	
	
	
	
	
	
	
	/**POST YOU LIKED**/
	case 'posts-you-upvoted':{

		$data=$messages="";

		/***************************BEGIN URL CONTROLLER****************************/
		
		$pathKeysArr = array('pageUrl', 'pageId');
		$maxPath = 2;				

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		if($sessUsername){

			////COUNT THE POSTS YOU HAVE LIKED//////

			list($totalRecords, $totalCountView) = $FORUM->getPostQUDS($sessUid, $type="u_upvoted", $retType="versions");
			
			/**********CREATE THE PAGINATION**********/					
			$pageUrl = 'posts-you-upvoted';
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'hash'=>'ptab'));
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageIdOut = $paginationArr["pageId"];

			///////END OF PAGINATION////////
					
			////GET LIKE AND SHARE DETAILS/////
			

			if($totalRecords){
			
				////PDO QUERY/////
			
				$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'primaryTable' => 'upvotes', 'primaryJoinCnd' => 'upvotes.POST_ID = posts.ID AND upvotes.STATE=1',
				'uniqueColumns' => 'upvotes.UPPER_ID AS UPPER,upvotes.TIME AS UTIME', 'filterCnd' => 'upvotes.UPPER_ID = ?', 'orderBy' => 'upvotes.TIME DESC'));
				
				
				///////////DISPLAY THE POSTS/////
				list($messages) = $FORUM->loadPosts($sql, array($sessUid), array('type'=>'postsyouliked'));
				
		   
			}
			  
			if(!$messages)
				$data = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', you have not upvoted any post yet</span>';

		}else
			$notLogged = $GLOBAL_notLogged;

		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
		
		$hr = (isset($pagination) && $pagination)? '<hr/>' : '';
		
		$preIcon = $SITE->getFA('far fa-thumbs-up', array("title"=>'Upvotes or Likes Made By You'));
						
		$SITE->buildPageHtml(array("pageTitle"=>'Posts You Upvoted',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/posts-you-upvoted" title="">Posts You\'ve Upvoted</a></li>'),
					"pageBody"=>'
						<div class="single-base blend">	
							<div class="base-ctrl">				
								<div class="row">'.
									(isset($notLogged)? $notLogged : ''). 
										
									($sessUsername? '	
										<h1 class="page-title pan bg-limex">POSTS YOU\'VE UPVOTED'.$preIcon.'</h1>'.	
										($totalPage? '<div class="cpop">(page <span class="cyan">'.$pageIdOut.'</span> of '.$totalPage.')</div>' : '').'
										<div class="topic-base base-rad">'. 
											(isset($totalRecords)? '<span class="black"> You have upvoted '.$totalCountView.'<hr/>' : '').
											$FORUM->getPostQUDS($sessUid, $type="tab", $retType="u_upvoted").'																			
										</div>' : ''
									).$pageTopAds.'											
									<div class="'.$leftWidgetClass.'">'.
										($sessUsername? 																		
											$hr.$pagination.$hr.(isset($data)? $data : '').
											(isset($messages)? $messages : '').$hr.$pagination.$hr																										
										: '').'
									</div>'.$rightWidget.'											
								</div>'.$pageBottomAds.'																					
							</div>
						</div>'
		));
						
		break;
			
	}
	
	
	
	
	
	
	/**POSTS YOU QUOTED**/
	case 'posts-you-quoted':{

		$data=$messages="";

		/***************************BEGIN URL CONTROLLER****************************/
		
		$pathKeysArr = array('pageUrl', 'pageId');
		$maxPath = 2;	
		
		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		if($sessUsername){
			
			////COUNT THE POSTS YOU HAVE QUOTED ///

			list($totalRecords, $totalCountView) = $FORUM->getPostQUDS($sessUid, $type="u_quoted_mentioned", $retType="versions");

			/**********CREATE THE PAGINATION*****/				
			$pageUrl =  'posts-you-quoted';				
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'hash'=>'ptab'));					
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageIdOut = $paginationArr["pageId"];

			////////END OF PAGINATION/////
					
			///////GET THE QUOTES AND TAGS/////////
				
			if($totalRecords){
			
				///////////PDO QUERY/////
			
				$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'uniqueColumns' => '', 'filterCnd' => "POST_AUTHOR_ID = ? AND MESSAGE LIKE '%author=%'", 'orderBy' => 'TIME DESC'));
				
				///////////DISPLAY THE POSTS/////
				list($messages) = $FORUM->loadPosts($sql, array($sessUid));
				
		   
			}

			if(!$messages)
				$data = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', you have not quoted any post yet</span>';

		}else
			$notLogged = $GLOBAL_notLogged;												

		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
		
		$hr = (isset($pagination) && $pagination)? '<hr/>' : '';	
		
		$SITE->buildPageHtml(array("pageTitle"=>'Posts You Quoted',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/posts-you-quoted" title="">Posts You\'ve Quoted</a></li>'),
					"pageBody"=>'
						<div class="single-base blend">	
							<div class="base-ctrl">				
								<div class="row">'.
									(isset($notLogged)? $notLogged : '').
									($sessUsername? '										
										<h1 class="page-title pan bg-limex">POSTS YOU\'VE QUOTED</h1>'.				
										($totalPage? '<div class="cpop">(page <span class="cyan">'.$pageIdOut.'</span> of '.$totalPage.')</div>' : '').'
										<div class="topic-base base-rad">
											<span class="black"> You have quoted in '.$totalCountView.'<hr/>'.
											$FORUM->getPostQUDS($sessUid, $type="tab", $retType="u_quoted_mentioned").'														
										</div>' : ''
									).$pageTopAds.'																		
									<div class="'.$leftWidgetClass.'">'.
										($sessUsername? 										
											$hr.$pagination.$hr.(isset($data)? $data : '').
											(isset($messages)? $messages : '').$hr.$pagination.$hr													
										: '').'
									</div>'.$rightWidget.'											
								</div>'.$pageBottomAds.' 																							
							</div>
						</div>'
		));

		break;
			
	}

	
	
	
	
	
	
	
	/**POSTS YOU SHARED**/
	case 'posts-you-shared':{

		$data=$messages=$totalShares="";


		/***************************BEGIN URL CONTROLLER****************************/
		
		$pathKeysArr = array('pageUrl', 'pageId');
		$maxPath = 2;	
	
		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		if($sessUsername){

			//////COUNT THE POSTS YOU HAVE SHARED///////

			list($totalRecords, $totalCountView) = $FORUM->getPostQUDS($sessUid, $type="u_shared", $retType="versions");
			
			/**********CREATE THE PAGINATION*********/				
			$pageUrl = 'posts-you-shared';				
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'hash'=>'ptab'));					
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageIdOut = $paginationArr["pageId"];

			//////////END OF PAGINATION///////////
					
			//////GET LIKE AND SHARE DETAILS///

			if($totalRecords){
				
				///PDO QUERY///
				
				$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'primaryTable' => 'shares', 'primaryJoinCnd' => 'shares.POST_ID = posts.ID AND shares.STATE = 1',
				'uniqueColumns' => 'shares.SHARER_ID AS SHARER,shares.TIME AS STIME', 'filterCnd' => 'shares.SHARER_ID = ?', 'orderBy' => 'shares.TIME DESC'));
				
				///////////DISPLAY THE POSTS/////
				list($messages) = $FORUM->loadPosts($sql, array($sessUid), array('type'=>'postsyoushared'));		
		   
			}
		   
			if(!$messages)
				$data = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', you have not shared any post yet</span>';

		}else
			$notLogged = $GLOBAL_notLogged;
		
		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
		
		$hr = (isset($pagination) && $pagination)? '<hr/>' : ''; 
		$preIcon = $SITE->getFA('far fa-share-square', array("title"=>'Shared By You'));
						
		$SITE->buildPageHtml(array("pageTitle"=>'Posts You Shared',
						"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/posts-you-shared" title="">Posts You\'ve Shared</a></li>'),
						"pageBody"=>'
						<div class="single-base blend">	
							<div class="base-ctrl">										
								<div class="row">'.
									(isset($notLogged)? $notLogged : ''). 
																					
									($sessUsername? '										
										<h1 class="page-title pan bg-limex">POSTS YOU\'VE SHARED'.$preIcon.'</h1>'.
										($totalPage? '<div class="cpop">(page <span class="cyan">'.$pageIdOut.'</span> of '.$totalPage.')</div>' : '').'
										<div class="topic-base base-rad">'.												
											(isset($totalShares)? '<span class="black"> You have Shared '.$totalCountView.'<hr/>' : '').
											$FORUM->getPostQUDS($sessUid, $type="tab", $retType="u_shared").'													
										</div>'
									: '' ).$pageTopAds.'																			
									<div class="'.$leftWidgetClass.'">'.
										($sessUsername?																					
											$hr.$pagination.$hr.(isset($data)? $data : '').
											(isset($messages)? $messages : '').$hr.$pagination.$hr
										: '').'
									</div>'.$rightWidget.'											
								</div>'.$pageBottomAds.' 																					
							</div>
						</div>'
		));
						
		break;
			
	}
	
	
	
	
	
	
	
	
	
	/**POST YOU WERE QUOTED OR TAGGED(MENTIONED)**/
	case 'posts-you-were-quoted-or-tagged':{

		$notLogged=$messages="";

		/***************************BEGIN URL CONTROLLER****************************/
		
		$pageUrl = $ENGINE->get_page_path('page_url', 1);
		$activeClass = 'active';
		$pathKeysArr = array('pageUrl', 'tab', 'pageId');
		$maxPath = 3;				
		$isQuotesTab = $isTagsTab = false;
		$quotesQryCnd = '('.rtrim(str_repeat('MESSAGE RLIKE ? OR ', 3), 'OR ').')';	
		$cmnQuoteRegex = "Author=";	
		$quotesValArr = array($cmnQuoteRegex.$sessUsername, $cmnQuoteRegex.'"'.$sessUsername, $cmnQuoteRegex."'".$sessUsername);				
		$tagsQryCnd = 'MESSAGE RLIKE ?';
		$tagsValArr = array("@".$sessUsername);
		
		/*
		$quotesValArr = array("(?<=".$cmnQuoteRegex.")".$sessUsername);
		$tagsQryCnd = '('.rtrim(str_repeat('MESSAGE RLIKE ? OR ', 1), 'OR ').')';
		$tagsValArr = array("% ".$sessUsername."%", "%".$sessUsername." %", "% ".$sessUsername." %", "% '".$sessUsername."%", "% \"".$sessUsername."%");
		$tagsValArr = array("/([^a\s]|a[^u\s]|au[^t\s]|aut[^h\s]|auth[^o\s]|autho[^r\s]|author[^=\s]|author=[^/\s]|a$|au$|aut$|auth$|autho$|author$|author=$)".$sessUsername."+/igm");
		*/
		
		
		if(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) ==  "quotes"){
							
			$altTitleText = 'Quotes';				
			$isQuotesTab =  true;	

		}elseif(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) ==  "tags"){
		
			$altTitleText = 'Tags';				
			$isTagsTab =  true;	

		}else{

			$pathKeysArr = array();
			$maxPath = 0;

		}
						

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		if($sessUsername){

			//////////COUNT THE SESSION USER QUOTES AND TAGS////////////////////
			
			/////TAGS////

			///////////PDO QUERY///////
			
			$sql = "SELECT COUNT(*) FROM posts WHERE ".$tagsQryCnd;
			$tagCount = $dbm->doSecuredQuery($sql, $tagsValArr)->fetchColumn();

			$tagCountView = $tagCount.' Tag'.(($tagCount > 1)? 's' : '');
			
			/////////QUOTES////

			///////////PDO QUERY//////////
				
			$sql = "SELECT COUNT(*) FROM posts WHERE ".$quotesQryCnd;
			$quoteCount = $dbm->doSecuredQuery($sql, $quotesValArr)->fetchColumn();

			$quoteCountView = $quoteCount.' Quote'.(($quoteCount > 1)? 's' : '');
			
			
			$totalRecords = $isQuotesTab? $quoteCount : $tagCount;
			$valArr = $isQuotesTab? $quotesValArr : $tagsValArr;
			$qryCnd = $isQuotesTab? $quotesQryCnd : $tagsQryCnd;
			
			////////////UPDATE OLD_QM_COUNTER IN users//////////////

			$ACCOUNT->updateUser($sessUsername, $cols="OLD_QM_COUNTER=NOW()");
			
			/**********CREATE THE PAGINATION****************/				
				
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl.'/'.($isQuotesTab? 'quotes' : 'tags'),'hash'=>'ptab'));					
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageIdOut = $paginationArr["pageId"];

			////////////END OF PAGINATION///////////////////
				

			/////////GET THE QUOTES AND TAGS///////////


			if($totalRecords){

				///////////PDO QUERY//////////
				
				$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'uniqueColumns' => '', 'filterCnd' => $qryCnd, 'orderBy' => 'TIME DESC'));
				
				///////////DISPLAY THE POSTS/////
				list($messages) = $FORUM->loadPosts($sql, $valArr, array("highlighKeywords" => $sessUsername));
				
				
			}else
				$messages = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', you have not been '.($isQuotesTab? 'quoted' : 'tagged').' in any post yet</span>';

		}else
			$notLogged = $GLOBAL_notLogged;			
			
		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);

		$hr = (isset($pagination) && $pagination)? '<hr/>' : '';
		$pageTitle = 'Posts You Were '.($isQuotesTab? 'Quoted' : 'Tagged');
		
		$SITE->buildPageHtml(array("pageTitle"=>$pageTitle,
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'">'.$pageTitle.'</a></li>'),
					"pageBody"=>'
						<div class="single-base blend">			
							<div class="base-ctrl">				
								<div class="row">'.
									$notLogged.
									
									($sessUsername? '				
										<h1 class="page-title pan bg-limex">'.$pageTitle.'</h1>'.
										($totalPage? '<div class="cpop">(page <span class="cyan">'.$pageIdOut.'</span> of '.$totalPage.')</div>' : '').'
										<div class="topic-base base-rad">
											<span class="black"> You have (<span class="cyan">'.$quoteCountView.'</span>) & (<span class="cyan">'.$tagCountView.'</span>)<hr/>'.
											$FORUM->getPostQUDS($sessUid, $type="tab").'													
										</div>'
									: '').$pageTopAds.
									($sessUsername?  '											
										<nav class="nav-base"><ul class="nav nav-tabs justified justified-bom">
											<li class="'.($isQuotesTab? $activeClass : '').'"><a href="/'.$pageUrl.'/quotes" class="links">Quotes</a></li>
											<li class="'.($isTagsTab? $activeClass : '').'"><a href="/'.$pageUrl.'/tags" class="links">Tags</a></li>
										</ul></nav>'
									: '').'											
									<div class="'.$leftWidgetClass.'">'.	
										($sessUsername?
											$hr.$pagination.$hr.
											(isset($messages)? $messages : '').$hr.$pagination.$hr
										: '').'
									</div>'.$rightWidget.'											
								</div>'.$pageBottomAds.'															
							</div>
						</div>'
		));
						
		break;

	}
	
	
	
	
	
	
	
	
	
	/**VOTES AND SHARES**/
	case 'votes-shares':{

		$messages=$notLogged="";	

		/***************************BEGIN URL CONTROLLER****************************/

		if(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) ==  "upvotes"){

			$pathKeysArr = array('pageUrl', 'tab', 'pageId');
			$maxPath = 3;	
			$table = $type = 'upvotes';
			$colSelf = 'UPPER_ID'; $uidAlias = 'UPPER'; $timeAlias = 'UTIME';
			$pageUrl = 'votes-shares/upvotes';						
			$subNav = '<li><a href="/votes-shares/upvotes" title="">My Upvotes</a></li>';
			$counterCol = "OLD_VTU_COUNTER";				
			$header = " UPVOTED POSTS";				
			$isUpvote =  true;	
			$preIcon = $SITE->getFA('far fa-thumbs-up', array("title"=>'Upvoted or Liked'));

		}elseif(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) ==  "downvotes"){

			$pathKeysArr = array('pageUrl', 'tab', 'pageId');
			$maxPath = 3;	
			$table = $type = 'downvotes';
			$colSelf = 'DOWNER_ID'; $uidAlias = 'DOWNER'; $timeAlias = 'DTIME';
			$pageUrl = 'votes-shares/downvotes';						
			$subNav = '<li><a href="/votes-shares/downvotes" title="">My Downvotes</a></li>';
			$counterCol = "OLD_VTD_COUNTER";
			$header = " DOWNVOTED POSTS";				
			$isDownvote =  true;				
			$preIcon = $SITE->getFA('far fa-thumbs-down', array("title"=>'Downvoted or Disliked'));

		}elseif(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) ==  "shares"){

			$pathKeysArr = array('pageUrl', 'tab', 'pageId');
			$maxPath = 3;
			$table = $type = 'shares';
			$colSelf = 'SHARER_ID'; $uidAlias = 'SHARER'; $timeAlias = 'STIME';
			$pageUrl = 'votes-shares/shares';								
			$subNav = '<li><a href="/votes-shares/shares" title="">My Shares</a></li>';
			$counterCol = "OLD_SH_COUNTER";
			$header = " SHARED POSTS ";				
			$isShare =  true;	
			$preIcon = $SITE->getFA('far fa-share-square', array("title"=>'Shared'));

		}else{

			$pathKeysArr = array();
			$maxPath = 0;

		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		if($sessUsername){

			////COUNT THE SESSION USERS POST LIKES AND SHARES////////
			
			//////UPVOTES				
			$totalUpvotes = $FORUM->votesHandler(array('type'=>'u','uid'=>$sessUid));
			$totalUpvotesUNQ = $FORUM->votesHandler(array('type'=>'u','uid'=>$sessUid,'countDistinct'=>true));								
			$totalUpvotes .= ' upvote'.(($totalUpvotes > 1)? 's' : '');
		
			////DOWNVOTES
			$totalDownvotes = $FORUM->votesHandler(array('type'=>'d','uid'=>$sessUid));
			$totalDownvotesUNQ = $FORUM->votesHandler(array('type'=>'d','uid'=>$sessUid,'countDistinct'=>true));				
			$totalDownvotes .= ' Downvote'.(($totalDownvotes > 1)? 's' : '');
			
			////SHARES				
			$totalShares = $FORUM->sharesHandler(array('uid'=>$sessUid));								
			$totalSharesUNQ = $FORUM->sharesHandler(array('uid'=>$sessUid,'countDistinct'=>true));				
			$totalShares .= ' Share'.(($totalShares > 1)? 's' : '');				
			
			if(isset($isUpvote))
				$totalRecords = $totalUpvotesUNQ;

			if(isset($isDownvote))
				$totalRecords = $totalDownvotesUNQ;

			elseif(isset($isShare))
				$totalRecords = $totalSharesUNQ;
			
			
			////////////UPDATE VIEWED_STATUS /////////////	
							
			$ACCOUNT->updateUser($sessUsername, $cols=$counterCol.'=NOW()');
				
			/**********CREATE THE PAGINATION***********/				
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'hash'=>'ptab'));					
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageIdOut = $paginationArr["pageId"];

			////////END OF PAGINATION////////


			/////////////LOAD RELATED POSTS/////////

			if($totalRecords){					
				
				$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'primaryTable' => $table, 'primaryJoinCnd' => $table.'.POST_ID = posts.ID AND '.$table.'.STATE = 1',
				'uniqueColumns' => '(SELECT self.'.$colSelf.' FROM '.$table.' self WHERE '.$table.'.POST_ID=self.POST_ID ORDER BY TIME DESC LIMIT 1) AS '.$uidAlias.', MAX('.$table.'.TIME) AS '.$timeAlias, 'filterCnd' => 'posts.POST_AUTHOR_ID=? GROUP BY '.$table.'.POST_ID', 'orderBy' => $table.'.TIME DESC'));
				
				///////////DISPLAY THE POSTS/////
				list($messages) = $FORUM->loadPosts($sql, array($sessUid), array('type'=>$type));									   				
				
			}else
				$messages = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', you have no '.strtolower($header).' yet</span>';

		}else
			$notLogged = $GLOBAL_notLogged;
			
		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);
		
		$hr = (isset($pagination) && $pagination)? '<hr/>' : '';
			
		$SITE->buildPageHtml(array("pageTitle"=>'My '.$header,
						"preBodyMetas"=>$SITE->getNavBreadcrumbs($subNav),
						"pageBody"=>'										
						<div class="single-base blend">
							<div class="base-ctrl">								
								<div class="row">
									'.$notLogged.
									($sessUsername? '
										<h1 class="page-title pan bg-limex">'.$preIcon.'MY '.$header.$preIcon.'</h1>'.						
										($totalPage? '<div class="cpop">(page <span class="cyan">'.$pageIdOut.'</span> of '.$totalPage.')</div>' : '').'
										<div class="topic-base base-rad">'.													
											(isset($totalUpvotes)? '<span class="black"> You have (<span class="cyan">'.$totalUpvotes.'</span>) 
											, (<span class="cyan">'.$totalDownvotes.'</span>) & (<span class="cyan">'.$totalShares.'</span>)</span><hr/>' : '').
											$FORUM->getPostQUDS($sessUid, $type="tab").'													
										</div>'
									: '').$pageTopAds.
									($sessUsername?  '											
										<nav class="nav-base"><ul class="nav nav-tabs justified justified-bom">
											<li class="'.(isset($isUpvote)? 'active' : '').'"><a href="/votes-shares/upvotes" class="links">Upvotes</a></li>
											<li class="'.(isset($isDownvote)? 'active' : '').'"><a href="/votes-shares/downvotes" class="links">Downvotes</a></li>
											<li class="'.(isset($isShare)? 'active' : '').'"><a href="/votes-shares/shares" class="links">Shares</a></li>
										</ul></nav>'
									: '').'
									<div class="'.$leftWidgetClass.'">'.
										($sessUsername?																					
											$hr.$pagination.$hr.
											(isset($messages)? $messages : '').$hr.$pagination.$hr																																
										: '').'
									</div>'.$rightWidget.'											
								</div>'.$pageBottomAds.'														
							</div>
						</div>'
		));

		break;
		
	}
	
	




	
	
	
	/**SITE BACK DOORS**/
	case 'site-back-doors':{
					
		$ACCOUNT->authorizeTopAccess(false);

		$un = $ENGINE->get_global_var('post', 'username');
		$adId = $ENGINE->get_global_var('post', 'adId');
		$postId = $ENGINE->get_global_var('post', 'postId');
		$adCampaignSlug = $ENGINE->get_global_var('post', 'adCampaignSlug');
		$adEditTarget = $ENGINE->get_global_var('post', 'adEditTarget');
		$eventSlug = $ENGINE->get_global_var('post', 'eventSlug');
		$postEventTarget = $ENGINE->get_global_var('post', 'postEventTarget');
		
		if(isset($_POST["userEvent"]))
			$accessUrl = '/user-events/'.$un.'/'.$eventSlug;
		
		elseif(isset($_POST["billingReport"]))
			$accessUrl = '/ad-billing-report/'.$adCampaignSlug.'/none/none/descending?_owner='.$un;
		
		elseif(isset($_POST["trafficReport"]))
			$accessUrl = '/ad-traffic-report/'.$adCampaignSlug.'/'.$adId.'?_owner='.$un;
		
		elseif(isset($_POST["adEdit"]))
			$accessUrl = '/'.AdsCampaign::getStatic('editBaseSlug').'/'.$adId.'/'.$adEditTarget;
		
		elseif(isset($_POST["postEvent"]))
			$accessUrl = '/post-event-history/'.$postEventTarget.'/'.$postId;
			
		if(isset($accessUrl)){

			header("Location:".$accessUrl);
			exit();

		}

		$req = ' required = "required" ';
		$adIdField = '<input class="field" placeholder="'.($adIdFieldPH = 'Enter the Ad Id').'" type="text" name="'.($adIdFieldName= 'adId').'" '.$req.' />';					

		$cmnMetaArr = array(
			'url' => '/'.$pageSelf, 'formClass' => 'inline-form inline-form-default block-label', 
			($fieldNameKey = 'fieldName') => 'username', ($fieldPhKey = 'fieldPH') => 'Enter the username', 'newWindow' => true,
			'btnLabel' => 'Access', 'btnClass' => 'btn-danger'
		);

		$cmnMetaArrForAdEdit  = $cmnMetaArr;
		$cmnMetaArrForAdEdit[$fieldNameKey] = $adIdFieldName;
		$cmnMetaArrForAdEdit[$fieldPhKey] = $adIdFieldPH;	
		
		$cmnMetaArrForPostEvent  = $cmnMetaArr;
		$cmnMetaArrForPostEvent[$fieldNameKey] = 'postId';
		$cmnMetaArrForPostEvent[$fieldPhKey] = 'Enter the Post Id';	
		$cmnMetaArrForPostEvent['fieldType'] = 'number';	

		$userEventsDropDown = '<select class="field options-inherit-bg" name="eventSlug">
									<optgroup label="Event Slug">
										<option>inbox</option>
										<option>old-inbox</option>
										<option>sent-pm</option>
										<option>followers</option>
										<option>following</option>
										<option>followed-topics</option>
										<option>followed-sections</option>
									</optgroup>
								</select>';

		$campaignTypeDropDown = '<select class="field options-inherit-bg" name="adCampaignSlug">
									<optgroup label="Campaign Slug">
										<option>'.AdsCampaign::getStatic('bannerCampaignType').'</option>
										<option>'.AdsCampaign::getStatic('textCampaignType').'</option>
									</optgroup>
								</select>';

		$campaignEditDropDown = '<select class="field" name="adEditTarget">
									<optgroup label="Select Item to Edit">
										<option value="'.AdsCampaign::getStatic('bannerEditSlug').'">Banner</option>
										<option value="'.AdsCampaign::getStatic('landPageEditSlug').'">Banner Landing Page</option>
										<option value="'.AdsCampaign::getStatic('textEditSlug').'">Text Ad</option>
									</optgroup>
								</select>';

		$postEventDropDown = '<select class="field" name="postEventTarget">
									<optgroup label="Event Slug">
										<option>upvotes</option>
										<option>downvotes</option>
										<option>shares</option>
									</optgroup>
								</select>';



		$SITE->buildPageHtml(array('pageTitle'=>'Site Back Doors',
		'preBodyMetas'=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">Site Back Doors</a></li>'),
		'pageBody'=>'						
		<div class="single-base blend">			
			<div class="base-ctrl">
				<h1 class="page-title pan bg-red">SITE BACK DOORS</h1>
				<div class="hr-dividers">
					<div>
						<h3 class="flab align-l">User Events</h3>
						'.$SITE->getSearchForm(
						array_merge(array(
							 'btnName' => 'userEvent',							
							'moreFields' => array($userEventsDropDown)
						), $cmnMetaArr)).'
					</div>					
					<div>
						<h3 class="flab align-l">Ad Campaign Billing Report</h3>
						'.$SITE->getSearchForm(
						array_merge(array(
							 'btnName' => 'billingReport',							
							'moreFields' => array($campaignTypeDropDown)
						), $cmnMetaArr)).'
					</div>					
					<div>
						<h3 class="flab align-l">Ad Campaign Traffic Report</h3>
						'.$SITE->getSearchForm(
						array_merge(array(
							'btnName' => 'trafficReport',							
							'moreFields' => array($adIdField, $campaignTypeDropDown)
						), $cmnMetaArr)).'
					</div>																
					<div>
						<h3 class="flab align-l">Edit Ad Campaign Item</h3>
						'.$SITE->getSearchForm(
						array_merge(array(
							'btnName' => 'adEdit',							
							'moreFields' => array($campaignEditDropDown)
						), $cmnMetaArrForAdEdit)).'
					</div>																
					<div>
						<h3 class="flab align-l">Post Events</h3>
						'.$SITE->getSearchForm(
						array_merge(array(
							'btnName' => 'postEvent',							
							'moreFields' => array($postEventDropDown)
						), $cmnMetaArrForPostEvent)).'
					</div>																
				</div>
			</div>
		</div>'
		));
						
		break;

	}
	
	
	
	
	
	/**AUTO PILOTS**/
	case 'auto-pilot-page':{
		
		///////AUTHORIZE TOP LEVEL ACCESS///////////
		$ACCOUNT->authorizeTopAccess(false);
		$tagUrlAnchorDelimeter = BT_TAG_ANCHOR_DELIMETER;
		
		if($sessUsername){
			
			$autoPilotTable = 'auto_pilots';
			
			$pilotArr = array('ad_plc'=>'ad_placement', 'lock_mods'=>'lockout_moderators', 
					'ad_bill'=>'ad_billing', 'ad_plc_adminover'=>'ad_placement_admin_override',
							'vol'=>'volatile_qry', 'cron_bdg_awd'=>'cron_badge_award');
							
			$pilotArrLen = count($pilotArr);
			
			$pilotResetArr = array('active_section_ads', 'activity_logs', 'ad_billings', 'ad_pending_placements', 
					'ad_traffic_reports', 'authentications', 'avatar_likes', 'awarded_badges', 'banner_campaigns',
					'dnd_mail_lists', 'downvotes', 'feedbacks', 'moderation_bans', 'members_follows', 
					'moderators', 'mq_trackers', 'site_traffics', 'posts', 'post_social_shares', 'post_uploads',
					'pm_blacklists', 'private_messages', 'reported_posts', 'section_follows', 'shares', 'spam_controls', 'text_campaigns',
					'topics', 'topic_follows', 'topic_social_shares', 'topic_views', 'upvotes', 'users', 'user_sessions',
					'users_metas');
							
			sort($pilotResetArr);
			$pilotResetArrTmp = $pilotResetArr;
			$pilotResetArrLen = count($pilotResetArr);
			
			$pilotResetArrHtml=array();
			
			foreach($pilotResetArr as $v)
				$pilotResetArrHtml[] = '<span>'.$v.'</span>';
			
			
			if(isset($_POST[$K="cst"])){
			
				$CST = 	$volatileEnabled = $INT_HASHER->decode($_POST[$K]);
				$updState = (int)!$CST;
			
			}
			
			//////////GET FORM-GATE RESPONSE///////
			list($alert, $form) = $SITE->formGateRefreshResponse(true);

			$formName = isset($_POST[$K="form_name"])?	$_POST[$K]	 : '';
			
			if(!$GLOBAL_isAdmin)
				$noViewPrivilege = '<span class="alert alert-danger">'.$sessUsername.' Sorry You do not have enough Privilege to view this Page</span>';

			if($GLOBAL_isAdmin){ 							
									
				if(isset($_POST["volatile_action"])){						
									
					if($volatileEnabled){
			
						///////////PDO QUERY////
						$sql="";
						
						/*******MULTI LOOP QUERIES************************/						
						
						//$sql = "ALTER TABLE ".$donation_table." ADD PT_REMATCH_TIME INT NOT NULL AFTER TRANS_NUMBER  ";
						//$sql = "ALTER TABLE ".$donation_table." DROP PT_REMATCH_TIME ";
						//$sql = "ALTER TABLE ".$matching_table." CHANGE CONFIRMED CONFIRMED VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'PENDING'";								
						//$sql = "ALTER TABLE users ADD FULL_NAME VARCHAR(255) NOT NULL AFTER LAST_NAME";
						
						/*******SINGLE LOOP QUERIES************************/						
						////////////ENSURE TO SET BREAK FOR SINGLE LOOP QUERIES/////////////////////						
						//$sql = "UPDATE members SET  COMMENT1='', COMMENT2='', SUSPENSION_STATUS='NO', RECYCLING_DEADLINE=? ";
						//$sql = "UPDATE members SET CURRENT_PACKAGE='NONE', FLOW_DIRECTION='NONE', LOOP_STATUS='COMPLETE', TOTAL_DECL=0, TOTAL_PURGE=0, COMMENT1='', COMMENT2='', SUSPENSION_STATUS='NO', RECYCLING_DEADLINE=?  WHERE USERNAME NOT IN('seer','west','izzy') ";
						//$sql = "UPDATE members SET CURRENT_PACKAGE='NONE', SUSPENSION_STATUS='NO',  FLOW_DIRECTION='NONE', LOOP_STATUS='COMPLETE', TOTAL_DECL=0, TOTAL_PURGE=0, COMMENT1='', COMMENT2='', RECYCLING_DEADLINE=?  WHERE CURRENT_PACKAGE IN ('CLASSIC','PREMIUM','ELITE','NONE') ";
						/*$sql = "ALTER TABLE transactions ADD DONATION1 VARCHAR(50) NOT NULL DEFAULT 'PENDING' AFTER STATUS, 
								ADD DONATION1_TIME INT NOT NULL AFTER DONATION1, ADD DONATION2 VARCHAR(50) NOT NULL DEFAULT 'PENDING' AFTER DONATION1_TIME,
								ADD DONATION2_TIME INT NOT NULL AFTER DONATION2";*/
					
						///////////PDO QUERY//////////
						/*$sql = "SELECT ID,SECTION_NAME FROM sections WHERE CATEG_ID != 0 AND SECTION_SLUG = '' ";*/
						//$sql = "SELECT ID, AD_RATE FROM sections";
						//$sql = "SELECT ID FROM topics";
						
						$valArr = array(); $term='';
						
						if(isset($_POST["run_query"])){
							
							//$sql = "SELECT * FROM user_badges LIMIT 1";
							
						}elseif(isset($_POST["run_site_reset"])){
							
							array_walk($pilotResetArrTmp, function(&$v,$k){
								$v = ' TRUNCATE '.$v.';';
							});
							
							$sql = implode('', $pilotResetArrTmp);
							$term = ' SITE RESET';
							
						}	
						
						if($sql){											
							
							if($dbm->doSecuredQuery($sql, $valArr))
								$alert = '<div class="alert alert-success blink">YOUR'.$term.' QUERY HAS BEEN EXECUTED SUCCESSFULLY!!!</div>';
							
							else
								$alert = '<div  class="alert alert-danger">YOUR'.$term.' QUERY EXECUTION HAS FAILED!!!</div>';
							
						}else
							$alert = '<div  class="alert alert-danger">SORRY INTERNAL VOLATILE QUERY EXECUTION IS DISABLED!!!</div>';
							
					}else
						$alert = '<div  class="alert alert-danger">SORRY EXTERNAL VOLATILE QUERY EXECUTION IS DISABLED!!!</div>';
									
						
					////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
					$SITE->formGateRefresh(array($alert, $formName), '', '#'.$formName);
					
				}
				
				
				//////REVALIDATE ALL TAG LINKS/////
				elseif(isset($_POST["run_auto_rvts"])){
					
					$oldSlug = $ENGINE->sanitize_user_input($_POST["old_slug"]);			
					$newSlug = $ENGINE->sanitize_user_input($_POST["new_slug"]);
						
					if($oldSlug && $newSlug){
					
						///////////PDO QUERY///////////
						$subQryCnd = ' CATEGORY=2 ';
						$sql = "SELECT ID, CRITERIA FROM badges WHERE ".$subQryCnd;
						$valArr = array();
						$stmt = $dbm->doSecuredQuery($sql, $valArr);
					
						while($row = $dbm->fetchRow($stmt)){
					
							$id = $row["ID"];
							$criteria = str_ireplace($oldSlug, $newSlug, $row["CRITERIA"]);
							//$criteria = preg_replace("#".$tagUrlAnchorDelimeter." tag#isU", $tagUrlAnchorDelimeter, $row["CRITERIA"]);
							
							$sql = "UPDATE badges SET CRITERIA=? WHERE ID=? AND ".$subQryCnd." LIMIT 1";
							$valArr = array($criteria, $id);
							$stmt = $dbm->doSecuredQuery($sql, $valArr);
					
						}
						
						$alert = '<div class="alert alert-success">TAGS URL ANCHOR DELIMETER REVALIDATED SUCCESSFULLY!!!</div>';
								
					}else
						$alert = '<div class="alert alert-danger">TAGS URL ANCHOR DELIMETER REVALIDATION FAILED!!!<br/>please specify old and new slugs</div>';
						 
						$SITE->formGateRefresh(array($alert, $formName), '', '#'.$formName);
					
				}
				
				//////UPDATE UI BG METAS/////
				elseif(isset($_POST["run_auto_uibg_update"])){
					
					$table = "ui_bgs";
					
					$holesCount = (UIBG_COUNTS + (isset($_POST[$K="holes_to_add"])? $_POST[$K] : 0));
					$startIndex = isset($_POST[$K])? UIBG_COUNTS : 1;
					
					for($id = $startIndex; $id <= $holesCount; $id++){
						
						$styles = 'background-image: url('.$id.'.jpg);';
						
						if(isset($_POST["uibg_styles_update"])){
					
							$sql = "UPDATE ".$table." SET BG_STYLES='".$styles."', CONTENT_STYLES=CONTENT_STYLES WHERE (ID=? AND IS_SOLID_STYLE=0) LIMIT 1";
							$valArr = array($id);
							$stmt = $dbm->doSecuredQuery($sql, $valArr);
					
						}else{
					
							$sql = "SELECT ID FROM ".$table." WHERE ID=? LIMIT 1";
							$valArr = array($id);
					
							if(!$dbm->doSecuredQuery($sql, $valArr)->fetchColumn()){
					
								$sql = "INSERT INTO ".$table." (ID, LABEL, BG_STYLES, CONTENT_STYLES) VALUES(?,?,?,?)";
								$valArr = array($id, '', $styles, '');
								$stmt = $dbm->doSecuredQuery($sql, $valArr);
					
							}
						}
					}
					
					if(isset($_POST[$K])){
					
						$sql = "UPDATE ".$autoPilotTable." SET STATE = ? WHERE PILOT_NAME = ? LIMIT 1";
						$valArr = array($holesCount, 'UIBG_ADD_HOLE_COUNT');
						$stmt = $dbm->doSecuredQuery($sql, $valArr);
						header("Location:".$rdrAlt);
						exit();
					
					}
					
					$alert = '<div class="alert alert-success">UI BG METAS UPDATED SUCCESSFULLY!!!</div>'; 
					$SITE->formGateRefresh(array($alert, $formName), '', '#'.$formName);
					
				}
				
				//////UPDATE EMOTICONS UICODES/////
				elseif(isset($_POST["run_auto_emoticons_update"])){
					
					$SITE->emoticonsUiCodeGenerator(true);
					$alert = '<div class="alert alert-success">EMOTICON METAS UPDATED SUCCESSFULLY!!!</div>'; 
					$SITE->formGateRefresh(array($alert, $formName), '', '#'.$formName);
					
				}
				
				//////REVALIDATE ALL SECTION SLUGS/////
				elseif(isset($_POST["run_auto_rvss"])){
								
					///////////PDO QUERY///////////
					
					$sql = "SELECT ID, SECTION_NAME FROM sections";
					$valArr = array();
					$stmt = $dbm->query($sql);
					
					while($row = $dbm->fetchRow($stmt)){
					
						$sid = $row["ID"];
						$sectionName = $row["SECTION_NAME"];
						$sectionSlug = $ENGINE->sanitize_slug($sectionName, array('appendXtn'=>false));
						
						$sql = "UPDATE sections SET SECTION_SLUG=? WHERE ID=? LIMIT 1";
						$valArr = array($sectionSlug, $sid);
						$stmt = $dbm->doSecuredQuery($sql, $valArr);
						
					}
					///////////PDO QUERY///////////
					
					$sql = "SELECT ID, CATEG_NAME FROM categories";
					$valArr = array();
					$stmt = $dbm->query($sql, $valArr);
					
					while($row = $dbm->fetchRow($stmt)){
					
						$cid = $row["ID"];
						$categName = $row["CATEG_NAME"];
						$categSlug = $ENGINE->sanitize_slug($categName, array('appendXtn'=>false));
						
						$sql = "UPDATE categories SET CATEG_SLUG=? WHERE ID=? LIMIT 1";
						$valArr = array($categSlug, $cid);
						$stmt = $dbm->doSecuredQuery($sql, $valArr);
						
					}
				
					$alert = '<div class="alert alert-success">SECTION SLUGS REVALIDATED SUCCESSFULLY!!!</div>';
					$SITE->formGateRefresh(array($alert, $formName), '', '#'.$formName);		
					
				}elseif(isset($updState)){
					
					//////AUTHORIZE VOLATILE/////
					if(isset($_POST["run_auto_vol"]))
						$pilotName = $pilotArr["vol"];
					
					//////AUTHORIZE MODERATORS LOCKOUT/////
					elseif(isset($_POST["run_auto_lockout"]))
						$pilotName = $pilotArr["lock_mods"];
						
					
					//////AUTHORIZE CRON BADGE AWARD/////
					elseif(isset($_POST["run_auto_baw"]))
						$pilotName = $pilotArr["cron_bdg_awd"];		
						
							
					//////AUTHORIZE AD PLACEMENT ADMIN OVERRIDE/////
					elseif(isset($_POST["run_auto_place_so"]))
						$pilotName = $pilotArr["ad_plc_adminover"];		
					
					//////AUTHORIZE AD PLACEMENT/////
					elseif(isset($_POST["run_auto_place"])){
						$pilotName = $pilotArr["ad_plc"];			
						
					}
					//////AUTHORIZE AD BILLING/////
					elseif(isset($_POST["run_auto_bill"]))
						$pilotName = $pilotArr["ad_bill"];		
						
					
					///////////PDO QUERY///////////
					
					$sql = "UPDATE ".$autoPilotTable." SET STATE = ? WHERE PILOT_NAME LIKE ? LIMIT 1";
					$valArr = array($updState, $pilotName);
					$done = $dbm->doSecuredQuery($sql, $valArr);
					
					$alert = '<div class="alert alert-'.($done? 'success' : 'danger').'">YOUR QUERY EXECUTION '.($done? 'WAS SUCCESSFUL' : 'FAILED').'!!!</div>';
					$SITE->formGateRefresh(array($alert, $formName), '', '#'.$formName);			
					
				}
				
				//CONVERT TO NUMERIC INDEXES
				$pilotArrNumInd = $pilotArr;
				shuffle($pilotArrNumInd);
				///////////PDO QUERY//////

				$sql = "SELECT PILOT_NAME, STATE FROM ".$autoPilotTable." WHERE (".rtrim(str_repeat(" PILOT_NAME LIKE ? OR", $pilotArrLen), "OR").")";
				$valArr = $pilotArrNumInd;
				$stmt = $dbm->doSecuredQuery($sql, $valArr);
					
				while($row = $dbm->fetchRow($stmt)){
					
					$pilotName = $row["PILOT_NAME"];
					$pilotState = $row["STATE"];
					$state = '<input type="hidden" name="cst" value="'.$INT_HASHER->encode(((int)$pilotState)).'" />';
					$stateAlert = '<div class="'.($pilotState? 'green' : 'red').'"><span class="blue">Current State:</span> <b>'.($pilotState? '' : 'DE').'ACTIVATED</b></div>';
					
					switch(strtolower($pilotName)){
					
						case $pilotArr['ad_plc']: $apState = $state; $ap = $stateAlert; break;
					
						case $pilotArr['lock_mods']: $aloState = $state; $alo = $stateAlert; break;
					
						case $pilotArr['ad_bill']: $abState = $state; $ab = $stateAlert; break;
					
						case $pilotArr['ad_plc_adminover']: $apsoState = $state; $apso = $stateAlert; break;
					
						case $pilotArr['vol']: $volState = $state; $vol = $stateAlert; break;
					
						case $pilotArr['cron_bdg_awd']: $bawState = $state; $baw = $stateAlert; break;
					}
					
				}
			}
			
		}else{
					
			header("Location:/login");
			exit();
					
		}
		
		$subNav = '<li><a href="/'.$pageSelf.'">Auto Pilots</a></li>';	
		$volatile = '<input type="hidden" name="volatile_action" value="true" />';
		
		$SITE->buildPageHtml(array("pageTitle"=>'Auto Pilots',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs($subNav),
					"pageBody"=>'
					<div class="single-base blend">
						<div class="base-ctrl">'.
							(isset($noViewPrivilege)? $noViewPrivilege : ''). 
							($GLOBAL_isAdmin? '																				
								<div class="panel panel-bluex">
									<h1 class="panel-head page-title">AUTO PILOTS</h1>
									<div class="panel-body hr-dividers">
										<div id="'.($tmp='alo').'" class="">'.	
											((isset($alert) && $form == $tmp)? $alert : '').'
											<form method="post" action="/'.$pageSelf.'#'.$tmp.'">
												<label>LOCK OUT MODERATORS</label><br/>'.
												(isset($alo)? $alo : '').'
												'.(isset($aloState)? $aloState : '').'
												<input type="hidden" name="form_name" value="'.$tmp.'" />
												<button type="submit" name="run_auto_lockout" class="btn btn-danger" >RUN</button>				
											</form>
										</div>
										<div id="'.($tmp='baw').'" class="">	'.
											((isset($alert) && $form == $tmp)? $alert : '').'
											<form method="post" action="/'.$pageSelf.'#'.$tmp.'">
												<label>AUTHORIZE BADGE AWARD</label><br/>'.
												(isset($baw)? $baw : '').'
												'.(isset($bawState)? $bawState : '').'
												<input type="hidden" name="form_name" value="'.$tmp.'" />
												<button type="submit" name="run_auto_baw" class="btn btn-danger" >RUN</button>				
											</form>
										</div>
										<div id="'.($tmp='apso').'" class="">'.
											((isset($alert) && $form == $tmp)? $alert : '').'
											<form method="post" action="/'.$pageSelf.'#'.$tmp.'">
												<label>AUTHORIZE AD PLACEMENT ADMIN OVERRIDE</label><br/>'.
												(isset($apso)? $apso : '').'
												'.(isset($apsoState)? $apsoState : '').'
												<input type="hidden" name="form_name" value="'.$tmp.'" />
												<button type="submit" name="run_auto_place_so" class="btn btn-danger" >RUN</button>					
											</form>
										</div>
										<div id="'.($tmp='ap').'" class="">'.	
											((isset($alert) && $form == $tmp)? $alert : '').'
											<form method="post" action="/'.$pageSelf.'#'.$tmp.'">
												<label>AUTHORIZE SECTION AD PLACEMENT</label><br/>'.
												(isset($ap)? $ap : '').'
												'.(isset($apState)? $apState : '').'
												<input type="hidden" name="form_name" value="'.$tmp.'" />
												<button type="submit" name="run_auto_place" class="btn btn-danger" >RUN</button>					
											</form>
										</div>
										<div id="'.($tmp='ab').'" class="">'.
											((isset($alert) && $form == $tmp)? $alert : '').'
											<form method="post" action="/'.$pageSelf.'#'.$tmp.'">
												<label>AUTHORIZE AD BILLING</label><br/>'.
												(isset($ab)? $ab : '').'
												'.(isset($abState)? $abState : '').'
												<input type="hidden" name="form_name" value="'.$tmp.'" />
												<button type="submit" name="run_auto_bill" class="btn btn-danger" >RUN</button>				
											</form>
										</div>
										<div id="'.($tmp='rvss').'" class="">'.
											((isset($alert) && $form == $tmp)? $alert : '').'
											<label class="flab">'.($K='REVALIDATE SECTION & CATEGORY SLUGS').'</label><br/>
											<button class="btn btn-danger" data-toggle="smartToggler">REVALIDATE</button>
											<div class="red hide modal-drop">
												<form method="post" action="/'.$pageSelf.'#'.$tmp.'">								
													<p>ARE YOU SURE YOU WANT TO '.$K.'.<br/> PLEASE CONFIRM</p>
													<input type="hidden" name="form_name" value="'.$tmp.'" />
													<button type="submit" name="run_auto_rvss" class="btn btn-danger" >YES</button>															
													<button type="button" class="btn close-toggle" >CLOSE</button>
												</form>
											</div>
										</div>
										<div id="'.($tmp='rvts').'" class="">'.
											((isset($alert) && $form == $tmp)? $alert : '').'
											<label class="flab">'.($K='REVALIDATE TAG URL ANCHOR DELIMETER').'</label><br/>
											<button class="btn btn-danger" data-toggle="smartToggler">REVALIDATE</button>
											<div class="red hide modal-drop">
												<form method="post" action="/'.$pageSelf.'#'.$tmp.'">								
													<p>ARE YOU SURE YOU WANT TO '.$K.'.<br/> PLEASE CONFIRM</p>
													<div class="field-ctrl">
														<label>old delimeter</label> 
														<input type="text" name="old_slug" class="field" placeholder="old delimiter: '.$tagUrlAnchorDelimeter.'" />
														<label>new delimeter</label> 
														<input type="text" name="new_slug" class="field" placeholder="new delimeter" />
													</div>
													<input type="hidden" name="form_name" value="'.$tmp.'" />
													<button type="submit" name="run_auto_rvts" class="btn btn-danger" >YES</button>															
													<button type="button" class="btn close-toggle" >CLOSE</button>
												</form>
											</div>
										</div>
										<div id="'.($tmp='upduibg').'" class="">'.
											((isset($alert) && $form == $tmp)? $alert : '').'
											<label class="flab">'.($K='CREATE NEW UIBG INSERT HOLES').'</label><br/>
											<button class="btn btn-danger" data-toggle="smartToggler">UPDATE</button>
											<div class="red hide modal-drop">
												<form method="post" action="/'.$pageSelf.'#'.$tmp.'">								
													<p>ARE YOU SURE YOU WANT TO '.$K.'?<br/> PLEASE CONFIRM</p>
													'.$SITE->getHtmlComponent('switch-slider', array('label'=>'update styles only:', 'fieldName'=>$uiBgStylesKey='uibg_styles_update', 'on'=>isset($_POST[$uiBgStylesKey]))).'<br/>
													<input type="hidden" name="form_name" value="'.$tmp.'" />
													<button type="submit" name="run_auto_uibg_update" class="btn btn-danger" >YES</button>															
													<button type="button" class="btn close-toggle" >CLOSE</button>
												</form>
											</div>
										</div>
										<div id="'.($tmp='updemot').'" class="">'.
											((isset($alert) && $form == $tmp)? $alert : '').'
											<label class="flab">'.($K='REVALIDATE EMOTICONS UICODES').'</label><br/>
											<button class="btn btn-danger" data-toggle="smartToggler">REVALIDATE</button>
											<div class="red hide modal-drop">
												<form method="post" action="/'.$pageSelf.'#'.$tmp.'">								
													<p>ARE YOU SURE YOU WANT TO '.$K.'?<br/> PLEASE CONFIRM</p>
													<input type="hidden" name="form_name" value="'.$tmp.'" />
													<button type="submit" name="run_auto_emoticons_update" class="btn btn-danger" >YES</button>															
													<button type="button" class="btn close-toggle" >CLOSE</button>
												</form>
											</div>
										</div>
										<div id="'.($tmp='vol').'" class="">
											<div class="alert alert-warning">
													RUNNING A WRONG QUERY MAY HARM YOUR DATABASE.
													TO RUN POTENTIALLY DANGEROUS QUERIES YOU MUST ENABLE VOLATILE PILOTING
											</div>'.
											((isset($alert) && $form == $tmp)? $alert : '').'
											<form method="post" action="/'.$pageSelf.'#'.$tmp.'">
												<label>AUTHORIZE VOLATILE PILOTS</label><br/>'.
												(isset($vol)? $vol : '').'
												'.(isset($volState)? $volState : '').'
												<input type="hidden" name="form_name" value="'.$tmp.'" />
												<button type="submit" name="run_auto_vol" class="btn btn-danger" >RUN</button>					
											</form>
										</div><br/><br/>
										<div id="'.($tmp='vol_qry').'" class="">'.
												((isset($alert) && $form == $tmp)? $alert : '').'
												<form method="post" action="/'.$pageSelf.'#'.$tmp.'">
													<label>'.($K='RUN VOLATILE QUERY').'</label><br/>
													<button class="btn btn-danger" data-toggle="smartToggler">RUN</button>
													<div class="red hide modal-drop">								
														<p>ARE YOU SURE YOU WANT TO '.$K.'.<br/> PLEASE CONFIRM</p>
														'.(isset($volState)? $volState.$volatile : '').'
														<input type="hidden" name="form_name" value="'.$tmp.'" />
														<button type="submit" name="run_query" class="btn btn-danger" >YES</button>															
														<button type="button" class="btn close-toggle" >CLOSE</button>
													</div>
												</form>	
										</div>'.(IS_DEV?
										'<div id="'.($tmp='sreset').'" class="">
											<form method="post" action="/'.$pageSelf.'#'.$tmp.'">'.
												((isset($alert) && $form == $tmp)? $alert : '').'
												<label>RUN SITE RESET</label><br/>
												<button class="btn btn-danger" data-toggle="smartToggler">RESET WEBSITE</button>
												<div class="red hide modal-drop">								
													<p>
														<div class="alert alert-danger">RESETTING WEBSITE WILL PREPARE THE SITE FOR PRODUCTION<div class="alert alert-info">NOTE: The following database tables('.$pilotResetArrLen.') will be truncated(emptied): <div class="pill-followers">'.implode('', $pilotResetArrHtml).'</div></div></div>
														ARE YOU SURE YOU WANT TO RESET THIS WEBSITE.<br/> PLEASE CONFIRM
													</p>
													'.(isset($volState)? $volState.$volatile : '').'
													<input type="hidden" name="form_name" value="'.$tmp.'" />
													<button type="submit" name="run_site_reset" class="btn btn-danger" >YES</button>															
													<button type="button" class="btn close-toggle" >CLOSE</button>
												</div>
											</form>
										</div>' : '').'						
									</div>
								</div>'
							: '').'
						</div>
					</div>'
		));
				
		break;
				
	}
	
	
	
	
	
	
	
	
	/**MODERATORS ACTIVITIES LOG**/
	case 'mods-activities-log':{

		$logs=$tmp=""; $spc = '&nbsp; &nbsp; &nbsp;';

		$type = isset($_GET[$K="log"])? $ENGINE->sanitize_user_input($_GET[$K]) : '';
		$param = isset($_GET[$K="param"])? $ENGINE->sanitize_number($_GET[$K]) : '';	
		$errorMessage = 'Oops! this link seems to be broken, please verify and try again!';
		
		switch(strtolower($type)){
				
			case "topic": 
				$cnd = '(TYPE=? AND TYPE_ID=?)';  $valArr = array('t', $param);
				$sql = "SELECT COUNT(*) AS TOTAL_RECS FROM activity_logs WHERE ".$cnd;
				$sql1 = "SELECT * FROM activity_logs WHERE ".$cnd;
				$tname = $valid = $SITE->topicIdToggle($param);
				$details = '<div class="alert alert-success"><b>TOPIC ID: '.$param.$spc.'
				TOPIC NAME: <span class="blue bg-white">'.$ENGINE->sanitize_slug($SITE->getThreadSlug($param), array('ret'=>'url', 'urlText'=>$tname, 'slugSanitized'=>true)).'</span></b></div>'; 
				break; 	
			
			case "category":
				$sidsSubQry = "SELECT ID FROM sections WHERE CATEG_ID = ?";
				$cnd = "WHERE (SID IN(".$sidsSubQry.") OR (TYPE=? AND TYPE_ID=?) OR TYPE_ID IN(".$sidsSubQry."))";
				$valArr = array($param, 'c', $param, $param);				
				$sql = "SELECT COUNT(*) AS TOTAL_RECS FROM activity_logs ".$cnd;
				$sql1 = "SELECT * FROM activity_logs ".$cnd;	
				$categName = $valid = $SITE->categoryIdToggle($param);
				$details = '<div class="alert alert-success"><b>CATEGORY ID: '.$param.$spc.' 
				CATEGORY NAME: <span class="blue bg-white">'.$ENGINE->sanitize_slug($categName, array('ret'=>'url')).'</span></b></div>'; 
				break; 	
			
			case "section": 
				$valArr = array('s', $param);	
				$cnd = "WHERE (SID IN(".$param.") OR (TYPE=? AND TYPE_ID=?))";
				$sql = "SELECT COUNT(*) AS TOTAL_RECS FROM activity_logs ".$cnd;
				$sql1 = "SELECT * FROM activity_logs ".$cnd;	
				$sectionName = $valid = $SITE->sectionIdToggle($param);
				$details = '<div class="alert alert-success"><b>SECTION ID: '.$param.$spc.' 
				SECTION NAME: <span class="blue bg-white">'.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).'</span></b></div>'; 
				break; 								
			
		}

		if(isset($sql, $sql1, $valArr) && $param){
				
			$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
							
			/**********CREATE THE PAGINATION*************/
			$qstrKeyValArr = array('log'=>$type, 'param'=>$param);
			$pageUrl = 'mods-activities-log';
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'qstrKeyVal'=>$qstrKeyValArr,'useFlat'=>false,'hash'=>'ptab'));				
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageId = $paginationArr["pageId"];

			////////END OF PAGINATION////////

			$sql1 .= " ORDER BY TIME DESC LIMIT ".$startIndex.",".$perPage;
			$stmt = $dbm->doSecuredQuery($sql1, $valArr);
			
			while($row = $dbm->fetchRow($stmt)){
				
				$act = $FORUM->DecodeModerationActivity($row["ACTIVITY"]);
				$time = $row["TIME"];
				$uid = $row["USER_ID"];
				$username = $ACCOUNT->memberIdToggle($uid);
				$reason = $row["REASON"];
				$reason = $reason? $reason : '----';
				$id = $row["ID"];
				$tmp = $ACCOUNT->sanitizeUserSlug($username, array('anchor'=>true)).' '.$act;
				$logs .= '<tr><td>'.$tmp.'</td><td>'.$reason.'</td><td>'.$ENGINE->time_ago($time).'</td></tr>';			
				
			}
							
			
			$logs = $logs? '<div class="table-responsive" ><table class="table-classic"><tr><th>ACTIVITY</th><th>REASON</th><th>TIME</th></tr>'.$logs.'</table></div>'
						: '<span class="alert alert-danger">'.(!isset($valid)? $errorMessage : 'sorry no logs yet').'</span>';
			
		}else
			$logs = '<span class="alert alert-danger">'.$errorMessage.'</span>';


		$SITE->buildPageHtml(array("pageTitle"=>'Mods Activities Log',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs("<li><a href='/".$pageSelf."' title=''>Mods Activities Log</a></li>"),
					"pageBody"=>'
						<div class="single-base blend">
							<div class="base-ctrl">'.				
								(isset($notLogged)? $notLogged : '').										
								(isset($noViewPrivilege)? $noViewPrivilege : '').'										
								<div class="panel panel-limex">
									<h1 class="panel-head page-title">MODS ACTIVITIES LOG</h1>
									<div class="panel-body">'.
										(isset($details)? $details : '').
										((isset($totalPage) && $totalPage)? '<div class="cpop">(page <span class="cyan">'.$pageId.'</span> of '.$totalPage.')</div>' : '').
										(isset($pagination)? $pagination : '').
										(isset($logs)? $logs : '').
										(!$param? '<span class="alert alert-danger">'.$errorMessage.'</span>' : '').
										(isset($pagination)? $pagination : '').'															
									</div>
								</div>
							</div>
						</div>'
		));
						
		break;
				
	}
	
	
	
	
	
	
	/**PAGE MANAGER**/
	case 'dynamic-cms':{

		require_once('file-uploader-engine.php');
		
		///////AUTHORIZE TOP LEVEL ACCESS///////////
		$ACCOUNT->authorizeTopAccess(false, $GLOBAL_isTrusted);		

		$isPageManager = false;
		$optionSelected = ' selected="selected" ';
		$awaitingDispatchApproval = 0;
		$approvedDispatch = 1;
		$dispatched = 2;

		/***************************BEGIN URL CONTROLLER****************************/

		$page_self_srt = $ENGINE->get_page_path('page_url', 2);
		$pageSelfSortSrch = $ENGINE->get_page_path('page_url', 4);
		$path1 = isset($pagePathArr[1])? strtolower($pagePathArr[1]) : '';
		$baseUrl = $pageUrlLowerCase;
		$pageUrl = $baseUrl.'/'.$path1;

			
		$urlList = array(//FORMAT => urlSlug:urlLabel:urlIcon:ignoreCond
			($pageMngrTab = 'page-manager').':page manager', 
			($dispatchMngrTab = 'dispatch-manager').':dispatch manager'
		);	
						
		if(in_array($path1, array($pageMngrTab, $dispatchMngrTab))){
							
			$pathKeysArr = array('pageUrl', 'tab');			
			$maxPath = 2;
			$isPageManager = (strtolower($path1) == $pageMngrTab);
			$tgtTab = $headTitle = $isPageManager? 'Page' : 'Dispatch';
			$headTitle = $headTitle.' Manager';
			$dbTable = $isPageManager? 'pages' : 'news_letters';
			$subNav = '<li><a href="/'.$pageUrl.'" title="">'.$headTitle.'</a></li>';
				
		}else{
				
			$pathKeysArr = array();
			$maxPath = 0;
				
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/


		$updateId=$tableList=$idTitle=$infos=$metaFields=$dispatchSampleField="";
		
		if(isset($_POST[$K="update_id"])){
				
			$updateId = $_POST[$K];
			$updateId = $ENGINE->sanitize_number($updateId);
				
		}elseif(isset($_POST[$K="id_title"])){
				
			$idTitle = $_POST[$K];
			$sql = "SELECT ID FROM ".$dbTable." WHERE TITLE=? LIMIT 1";
			$valArr = array($idTitle);
			$updateId = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
				
		}
		
		$alertUser = $SITE->formGateRefreshResponse();

		////DELETE A PAGE/////
		if(isset($_POST["del"]) && $updateId){
				
			$sql = "DELETE FROM ".$dbTable." WHERE ID=? LIMIT 1";
			$valArr = array($updateId);
			$stmt = $dbm->doSecuredQuery($sql, $valArr);
			
			$SITE->formGateRefresh();
				
		}


		if(isset($_POST["submit"])){
			
			$title = $idTitle = $_POST["title"];
			$titleSlug = $ENGINE->sanitize_slug($title, array('appendXtn' => false));
			$content = $_POST["content"];
			$theme = $_POST["theme"];
			$oldTitle = $_POST["old_title"];
			
			if($title){
				
				if(($isPageManager && !$SITE->pageSlugConflicts($title)) || (strtolower($title) == strtolower($oldTitle)) || !$isPageManager){
							
					if($updateId){
				
						if($isPageManager){
				
							$valArr = array($titleSlug);
							$uniqCols = 'TITLE_SLUG=?,';
				
						}else{
				
							switch(strtolower($_POST["dispatch_status"])){
				
								case 'dispatched': $dispatchStatusDB = $dispatched; break;
				
								case 'awaiting approval': $dispatchStatusDB = $awaitingDispatchApproval; break;
				
								default : $dispatchStatusDB = $approvedDispatch; 
				
							}
				
							$uniqCols = 'DISPATCH_STATUS=?,';
							$valArr = array($dispatchStatusDB);
				
						}
				
						array_push($valArr, $title, $content, $sessUid, $theme, $updateId);
						
						$sql = "UPDATE ".$dbTable." SET ".$uniqCols." TITLE=?, CONTENT=?, LAST_MODIFIED_BY=? , THEME=? WHERE ID=? LIMIT 1";
						
						if($dbm->doSecuredQuery($sql, $valArr))
							$alertUser = '<span class="alert alert-success">'.$tgtTab.' content updated successfully!</span>';
				
						else
							$alertUser = '<span class="alert alert-danger">something went wrong, please try again!</span>';
						
					}else{
				
						if($isPageManager){
				
							$valArr = array($titleSlug);
							$uniqCols = 'TITLE_SLUG,';
							$uniqPH = '?,';
				
						}else{
				
							$uniqCols = 'DISPATCH_STATUS,';
							$uniqPH = '?';
							$valArr = array(1);
				
						}
				
						array_push($valArr, $title, $content, $sessUid, $theme);
						
						$sql = "INSERT INTO ".$dbTable." (".$uniqCols." TITLE, CONTENT, CREATED_BY, THEME) VALUES(".$uniqPH."?,?,?,?)";
						
						if($dbm->doSecuredQuery($sql, $valArr))
							$alertUser = '<span class="alert alert-success">'.$tgtTab.' was successfully created!</span>';
				
						else
							$alertUser = '<span class="alert alert-danger">something went wrong, please try again!</span>';
						
					}
						
					if(isset($_POST["dispatch_sample"]))
						$SITE->newsLetterDispatcher(($updateId? $updateId : $dbm->getLastInsertId()), $sessUid);
							
					if(!$updateId)
						$SITE->formGateRefresh($alertUser);			
					
				}else
					$alertUser = '<span class="alert alert-danger">Sorry the title: '.$title.' is conflicting with another page; choose another title !</span>';
				
			}else
				$alertUser = '<span class="alert alert-danger">Please enter the title !</span>';
				
		}

		$sql = "SELECT * FROM ".$dbTable." ORDER BY TITLE LIMIT 50";
		$valArr = array();
		$stmt = $dbm->doSecuredQuery($sql, $valArr);
				
		while($row = $dbm->fetchRow($stmt)){
				
			$pid = $row["ID"];
			$pTitle = $row["TITLE"];
			$tableList .= '<option '.($pTitle == $idTitle? $optionSelected : '').'>'.$pTitle.'</option>';
			
		}

		if($tableList)
		$tableList = '<form action="/'.$pageSelf.'#title" method="post" class="inline-form">
							<select name="id_title" class="field"><option>-- add a new '.strtolower($tgtTab).' --</option>'.$tableList.'</select>
							<button type="submit" name="edit" class="btn btn-primary">Edit</button>
							<a class="btn btn-danger" data-toggle="smartToggler" >Delete</a>
							<div class="modal-drop hide red has-close-btn">
								<p>You are about to delete the selected '.$tgtTab.'<br/>Please confirm</p>									
								<button type="submit" name="del" class="btn btn-danger">ok</button>
								<button type="button" class="btn close-toggle">close</button>
							</div>
						</form>';

		$sql = "SELECT * FROM ".$dbTable." WHERE ID=? LIMIT 1";
		$valArr = array($updateId);
		$stmt = $dbm->doSecuredQuery($sql, $valArr);
		$row = $dbm->fetchRow($stmt);
				
		if(!empty($row)){
				
			$_POST["title"] = $oldTitle = $row["TITLE"];
			$_POST["content"] = $row["CONTENT"];
			$_POST["theme"] = $row["THEME"];
			$infos = '<div class="prime-sc1 pill-followers"><div>created by '.$ACCOUNT->sanitizeUserSlug($ACCOUNT->memberIdToggle($row["CREATED_BY"]), ($metaArr=array('anchor'=>true, 'gender'=>true))).' '.$ENGINE->time_ago($row["TIME"]).(($lastModifiedBy=$row["LAST_MODIFIED_BY"])? ' | last modified by '.$ACCOUNT->sanitizeUserSlug($ACCOUNT->memberIdToggle($lastModifiedBy), $metaArr).' '.$ENGINE->time_ago($row["TIME_LAST_UPDATED"]) : '').'</div></div>';
				
			if(!$isPageManager){
				
				$metaFields = '<div class="field-ctrl">
									<label>Dispatch Status</label>
									<select name="dispatch_status" class="field">
										<option '.((($dispatchStatus = $row["DISPATCH_STATUS"]) == $awaitingDispatchApproval)? $optionSelected : '').'>Awaiting Approval</option>
										<option '.(($dispatchStatus == $approvedDispatch)? $optionSelected : '').'>Approved</option>
										<option '.(($dispatchStatus == $dispatched)? $optionSelected : '').'>Dispatched</option></select>
								</div>';
				$dispatchSampleField = '<div class="field-ctrl">
											'.$SITE->getHtmlComponent('switch-slider', array('label'=>'Dispatch a sample to me:', 'fieldName'=>$K='dispatch_sample', 'on'=>isset($_POST[$K]))).'
											<a href="#" role="button" class="" data-toggle="smartToggler" title="what does this mean?">'.$FA_infoCircle.'</a>
											<div class="hide">
												<div class="alert alert-warning">
													We recommend that whenever you have finished creating or editing a dispatch, you should check this box so
													that when you hit the submit button, a sample of the dispatch will be sent to your email.
													<br/> Please when you get the sample dispatch in your mailbox, review it and when you are sure everything is in order then 
													come back to the dispatch manager and update the <b>Dispatch Status to Approved</b>.
												</div>
											</div>
										</div>';
				
				
			}
			
		}			
		
		$dpath = $siteDomain.$SITE->getDownloadURL("", ""); 		

		$tabLink = $SITE->buildLinearNav(array(
			"baseUrl" => $baseUrl,
			"urlList" => $urlList,							
			"active" => $_GET['tab']																				
		
		));
		
		$SITE->buildPageHtml(array("pageTitle"=>'Dynamic CMS - '.$headTitle, "pageBodyMetas"=>'include-editor.php',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs($subNav),
					"pageBody"=>'
						<div class="single-base blend">
							<div class="base-ctrl">'.
								$tabLink.(isset($uploaderHtml)? $uploaderHtml : '').'										
								<h1 class="page-title pan bg-orange">'.$headTitle.'</h1>				
								<div class="base-pad">
									<button class="btn btn-warning" data-toggle="smartToggler" data-id-targets="tips">Useful Tips</button>
									<div class="text-primary modal-drop hide brw" id="tips">
										<h3>Tips</h3>
										<ol class="ol">
											<li>
												'.$SITE->getDynamicContentTips().'	
											</li>
											<li>
												To include a download link to locally hosted files, copy the url below and replace "filename" and ".ext" with the target filename and extension respectively<br/>
												Note: if your upload path is not "clouds" then replace "clouds" in the link below with your chosen path e.g favicon.<br/>
												'.$dpath.'filename <br/>e.g '.$dpath.'usher.mp3														
											</li>
										</ol>
									</div>'.
									(isset($alertUser)? $alertUser : '').
									((isset($tableList) && $tableList)? '<div class="text-capitalize"><b>'.$tableList.'</b></div>' : '').
									(!isset($hideForm)? '											
										<form class="" method="post" action="/'.$pageSelf.'" >
											'.$infos.'
											<div class="field-ctrl">
												<label>Title</label>
												<input id="title" type="text" class="field" name="'.($K='title').'" value="'.(isset($_POST[$K])? $_POST[$K] : '').'" />
											</div>
											<div class="field-ctrl">
												<label>Theme</label>
												<select class="field" name="'.($K='theme').'" >
													<option '.((($K2 = 'blue') && isset($_POST[$K]) && $_POST[$K] == $K2)? $optionSelected : '').'>'.$K2.'</option>
													<option '.((($K2 = 'red') && isset($_POST[$K]) && $_POST[$K] == $K2)? $optionSelected : '').'>'.$K2.'</option>
													<option '.((($K2 = 'orange') && isset($_POST[$K]) && $_POST[$K] == $K2)? $optionSelected : '').'>'.$K2.'</option>
													<option '.((($K2 = 'lime') && isset($_POST[$K]) && $_POST[$K] == $K2)? $optionSelected : '').'>'.$K2.'</option>
													<option '.((($K2 = 'dark cyan') && isset($_POST[$K]) && $_POST[$K] == $K2)? $optionSelected : '').'>'.$K2.'</option>
												</select>
											</div>'.$metaFields.'						
											<div class="field-ctrl">
												<label>Content</label>
												<textarea class="field" id="mce-editor" name="'.($K='content').'" >'.(isset($_POST[$K])? $_POST[$K] : '').'</textarea>
											</div>'.$dispatchSampleField.'
											<div class="field-ctrl">							
												<input type="hidden" name="update_id"  value="'.(isset($updateId)? $updateId : '').'" />
												<input type="hidden" name="old_title"  value="'.(isset($oldTitle)? $oldTitle : '').'" />
												<button type="submit" class="form-btn" name="submit" >'.$SITE->getFA('fas fa-save', array("title"=>'Save')).' Submit</button>
											</div>						
										</form>'
									: '').'
								</div>		
							</div>
						</div>'
		));
						
		break;
				
	}
	
	
	
	
	
	
	
	
	
	/**RECEIVED FEEDBACKS**/
	case 'received-feedbacks':{
		
		///////AUTHORIZE TOP LEVEL ACCESS///////////
		$ACCOUNT->authorizeTopAccess(false);

		$notLogged=$alertUser=$feedbackTable="";

		if($sessUsername){				
			
			//GENERATE REPORTED CASES IN THE SECTIONS THAT THE USER MODERATES//////

			if($GLOBAL_isAdmin){
				
				if(isset($_POST["fbk_ctrl"], $_POST[$K="fbk_id"])){
				
					if($id = $ENGINE->sanitize_number($_POST[$K])){
				
						$sql = "DELETE FROM feedbacks WHERE ID=? LIMIT 1";
						$valArr = array($id);
						$stmt = $dbm->doSecuredQuery($sql, $valArr);
				
					}			
				}
					
				///////////PDO QUERY///
			
				$sql = "SELECT COUNT(*) FROM feedbacks";
				$totalRecords = $dbm->query($sql)->fetchColumn();
				
				/**********CREATE THE PAGINATION*********/					
				$pageUrl = 'received-feedbacks';
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'useFlat'=>false,'hash'=>'ptab'));				
				$pagination = $paginationArr["pagination"];
				$totalPage = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$pageId = $paginationArr["pageId"];

				//////END OF PAGINATION/////
				
			
				///////////PDO QUERY//////
			
				$sql = "SELECT *, (SELECT EMAIL FROM users WHERE feedbacks.USER_ID != '' AND feedbacks.USER_ID = users.ID LIMIT 1) AS REG_EMAIL
						FROM feedbacks ORDER BY TIME DESC LIMIT ".$startIndex.",".$perPage;

				$stmt = $dbm->query($sql);
							
				while($row = $dbm->fetchRow($stmt)){
				
					$fbkId = $row["ID"];
					$senderId = $row["USER_ID"];
					$senderEmail = $row["REG_EMAIL"];
							
					if(!$senderId)
						$sender = $row["FULL_NAME"].' (A Guest)';
				
					else{
				
						$sender =  $ACCOUNT->memberIdToggle($senderId);
						$sender = $sender.'<br/><a title="send private message" href="/pm/'.$sender.'" role="button" class="btn btn-success">PM</a>';
				
					}
				
					if($senderEmail){
				
						$senderEmail = $row["REG_EMAIL"];
						$senderEmail = $senderEmail.'<br/><a title="send Email" href="mailto:'.$senderEmail.'" role="button" class="btn btn-primary">E-mail</a>';
						
					}else{
					
						$senderEmail = $row["EMAIL"];
						$senderEmail = $senderEmail.'<br/><a title="send Email" href="mailto:'.$senderEmail.'" role="button" class="btn btn-primary">E-mail</a>';
					
					}
					
					$ctrlBtn = '<form method="post" class="inline-form" action="/received-feedbacks">
									<div class="field-ctrl">
										<input type="hidden" name="fbk_id" value="'.$fbkId.'" />
									</div>
									<div class="field-ctrl btn-ctrl">
										<button type="button" class="btn btn-danger" data-toggle="smartToggler" data-id-targets="cfm-'.$fbkId.'">DEL</button>
									</div>							
									<div id="cfm-'.$fbkId.'" class="red modal-drop hide has-close-btn">
										<p>ARE YOU SURE</p>
										<input class="btn btn-danger" type="submit" name="fbk_ctrl" value="DEL" />
										<button type="button" class="btn close-toggle">CLOSE</button>
									</div>							
								</form>';
					
					$feedbackTable .= '<tr><td>'.$fbkId.'</td><td>'.$sender.'</td><td>'.$senderEmail.'</td><td>'. $row["FEEDBACK_CONTENT"].'</td>
										<td>'.$ENGINE->time_ago($row["TIME"]).'</td><td>'.$ctrlBtn.'</td></tr>';
						
				}

				$feedbackTable = '<div class="table-responsive" >
										<table class="table-3" >
											<caption class="bg-brown">SUPPORT & FEEDBACKS</caption>
											<tr><th>ID</th><th>SENDER</th><th>SENDER\'S EMAIL</th>
											<th>MESSAGE</th><th>TIME SENT</th><th>CTRL</th></tr>'.$feedbackTable.'
										</table>
									</div>';
				
			}else
				$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', Sorry You do not have enough Privilege to<br/> view this Page</span>';
			

		}else
			$notLogged = $GLOBAL_notLogged;


		$SITE->buildPageHtml(array("pageTitle"=>'Support Requests/Feedbacks',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'">Support Requests/Feedbacks</a></li>'),
					"pageBody"=>'
						<div class="single-base blend">	
							<div class="base-ctrl">'.
								$notLogged.
								($sessUsername? '														
									<div class="panel panel-bluex">									
										<h1 class="panel-head page-title">TABLE OF SUPPORT/FEEDBACK</h1>
										<div class="panel-body">'.
											$alertUser.(isset($pagination)? $pagination : '').
											$feedbackTable.(isset($pagination)? $pagination : '').'																										
										</div>
									</div>'
								: '').'
							</div>
						</div>'
		));
						
		break;
		
	}
	
	
	
	
	
	
	
	
	/**REPORTS**/
	case 'reported-cases':{

		///////AUTHORIZE TOP LEVEL ACCESS///////////
		$ACCOUNT->authorizeTopAccess();

		$rqd=$notLogged=$alertUser=$tabLink=$reportsTable=$action="";

		$actionKey = 'action';
		$optionSelected = 'selected="selected"';
		$banOpt = 'ban';
		$unbanOpt = 'unban';
		$terminateOpt = 'terminate';

		/***************************BEGIN URL CONTROLLER****************************/

		$page_self_srt = $ENGINE->get_page_path('page_url',  2);
		$pageSelfSortSrch = $ENGINE->get_page_path('page_url',  4);
		$baseUrl = $pageUrlLowerCase;
			
		$urlList = array(//FORMAT => urlSlug:urlLabel:urlIcon:ignoreCond
			($reportMngrTab = 'manage-reports').':report manager', 
			($banMngrTab = 'manage-bans').':ban manager'
		);			

		if(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == $reportMngrTab){
					
			$pathKeysArr = array('pageUrl', 'tab', 'pageId');
			$pageUrl = $baseUrl.'/'.$reportMngrTab;				
			$maxPath = 3;
			$pageTitle = 'Report Manager';
			$subNav = '<li><a href="/'.$pageSelf.'" title="">'.$pageTitle.'</a></li>';
			
		}elseif(isset($pagePathArr[1]) && strtolower($pagePathArr[1]) == $banMngrTab){
		
			$pathKeysArr = array('pageUrl', 'tab');
			$pageUrl = $baseUrl.'/'.$banMngrTab;				
			$maxPath = 2;	
			$pageTitle = 'Ban Manager';
			$subNav = '<li><a href="/'.$pageSelf.'" title="">'.$pageTitle.'</a></li>';	
		
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;
		
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		if(isset($_GET[$K="tab"]))
			$tab = $ENGINE->sanitize_user_input($_GET[$K], array('lowercase' => true));

		if($sessUsername){								
			
			//////////GET FORM-GATE RESPONSE//////////	
			$alertUser = $SITE->formGateRefreshResponse();
			
			if(!$GLOBAL_isStaff)
				$noViewPrivilege = '<span class="alert alert-danger">'.$ACCOUNT->sanitizeUserSlug($sessUsername, array('anchor'=>true, 'youRef'=>false)).', Sorry You do not have enough Privilege to view this Page</span>';
							
			
			//DELETE A FLAG///
			if(isset($_POST[$K="rpt_fp"]) || isset($_GET[$K])){
				
				$id = isset($_POST[$K="rpt"])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : '');
				
				if($id = $ENGINE->sanitize_number($ENGINE->sanitize_user_input($id))){
					
					///////////PDO QUERY////////
					$sql = "DELETE FROM reported_posts WHERE ID = ? LIMIT 1";
					$valArr = array($id);
					$stmt = $dbm->doSecuredQuery($sql, $valArr);
					
				}		

				////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
				$SITE->formGateRefresh("", "", "#tab");
		
			}
			
			//TREAT A REPORT///
			if(isset($_POST[$K="rpt_trt"]) || isset($_GET[$K])){
				
				$reportId = isset($_POST[$K="rpt"])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : '');
				
				if($sessUid && $reportId = $ENGINE->sanitize_number($ENGINE->sanitize_user_input($reportId))){
					
					$sql = "UPDATE reported_posts SET TREATED_BY = ? WHERE TREATED_BY = 0 AND ID = ? LIMIT 1";
					$valArr = array($sessUid, $reportId);
					$stmt = $dbm->doSecuredQuery($sql, $valArr);
				
				}		

				////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
				$SITE->formGateRefresh("", "", "#tab");
				
			}
			
			
			//////GENERATE REPORTED CASES IN THE SECTIONS THAT THE USER MODERATES///////
			if($tab == $reportMngrTab){

				if($GLOBAL_isStaff){
					
					///////GET SECTIONS MODERATED SUBQRY
					$scmQry = $SITE->moderatedSectionCategoryHandler(array('action'=>'scm-qry'));
					
					$valArr = $GLOBAL_isAdmin? array() : array($sessUid, $sessUid);
				
					///////////PDO QUERY////////
					
					$sql = "SELECT COUNT(*) FROM reported_posts"
							.($GLOBAL_isAdmin? "" : "
								JOIN posts ON reported_posts.POST_ID=posts.ID 
								JOIN topics ON topics.ID=posts.TOPIC_ID JOIN 
								sections ON topics.SECTION_ID=sections.ID												
								WHERE(													
									sections.ID IN(".$scmQry.")
								)"
							);

					
					$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
				
					if($totalRecords){			

						/**********CREATE THE PAGINATION*********/
						$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'hash'=>'tab'));					
						$pagination = $paginationArr["pagination"];
						$totalPage = $paginationArr["totalPage"];
						$perPage = $paginationArr["perPage"];
						$startIndex = $paginationArr["startIndex"];
						$pageId = $paginationArr["pageId"];


						/////END OF PAGINATION/////
					
						///////////PDO QUERY////////
							
						$sql =  "SELECT r.*,posts.POST_AUTHOR_ID FROM reported_posts r JOIN posts ON r.POST_ID=posts.ID
								".($GLOBAL_isAdmin? "" : "
									JOIN topics ON topics.ID=posts.TOPIC_ID JOIN sections 
									ON topics.SECTION_ID=sections.ID													
									WHERE(														
										sections.ID IN(".$scmQry.")
									)"
								)."
								ORDER BY r.TIME DESC LIMIT ".$startIndex.",".$perPage;

						$stmt = $dbm->doSecuredQuery($sql, $valArr);
								
						while($row = $dbm->fetchRow($stmt)){
							
							$flagRaised = $row["FLAG_RAISED"];
							$treated = $row["TREATED_BY"];
							$rptId = $row["ID"];
							$treatedUser = $treated? $ACCOUNT->memberIdToggle($treated) : "";
							$pid = $row["POST_ID"];
							$tid = $FORUM->getPostDetail($pid, "TOPIC_ID");
							$topicName = $SITE->topicIdToggle($tid);
							
							/*
							/////////PDO QUERY/////////
					
							$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => '', 'stop' => 1, 'uniqueColumns' => '', 'filterCnd' => 'posts.ID=?', 'orderBy' => ''));
							
							list($messages) = $FORUM->loadPosts($sql, array($pid), array('type'=>'main'));
							*/
							$postNumber = $FORUM->getPostNumber($pid, $tid);
							$messages = $ENGINE->sanitize_slug($SITE->getThreadSlug($tid,true,false), array('ret'=>'url', 'slugSanitized'=>true, 'postUrl'=>$FORUM->getPostPageNumber($postNumber), 'urlHash'=>$postNumber, 'urlText'=>'Review', 'urlAttr'=>'target="_blank"'));
							
							$modDelCfm = ' <a role="button"  href="/'.$pageSelf.'?rpt='.$rptId.'&rpt_fp=true" class="" data-toggle="smartToggler" title="Remove this flag">'.$GLOBAL_delBtn.'</a>
										<div class="modal-drop red hide has-close-btn" >
											<form action="/'.$pageSelf.'" method="post">
												You are about to <b class="red">DELETE THIS FLAG</b> <br/><br/>Please confirm<br/><br/>
												<input   type="hidden" name="rpt"   value="'.$rptId.'"  />
												<input   type="submit" name="rpt_fp" class="btn btn-success"   value="OK"  /><br/>
												<input  type="button" class="btn close-toggle" value="CLOSE" />
											</form>
										</div>';
				
							$treater =	'<td>'.($treated?  $ACCOUNT->sanitizeUserSlug($treatedUser, array('anchor'=>true)).'<span class="block small lblack">'.$ENGINE->time_ago($row["TIME_TREATED"]).'</span>'
											: '
											<a role="button"  href="/'.$pageSelf.'?rpt='.$rptId.'&rpt_trt=true" class="btn btn-primary" data-toggle="smartToggler" title="Mark this report as treated">Treat</a>
											<div class="modal-drop red hide has-close-btn" >
												<form action="/'.$pageSelf.'" method="post">
													Are you sure you have reviewed this report <br/><br/>Please confirm<br/><br/>
													<input type="hidden" name="rpt" value="'.$rptId.'" />
													<input type="submit" name="rpt_trt" class="btn btn-success" value="YES" /><br/>
													<input type="button" class="btn close-toggle" value="CLOSE" />
												</form>
											</div>
											').'</td>';
							
							
							
							$reportsTable .= '<tr><td>'.$row["ID"].'</td><td>'.$ACCOUNT->sanitizeUserSlug($ACCOUNT->memberIdToggle($row["REPORTER_ID"]), array('anchor'=>true)).'</td><td>'.$ACCOUNT->sanitizeUserSlug($ACCOUNT->memberIdToggle($row["POST_AUTHOR_ID"]), array('anchor'=>true)).'</td>
											<td>'.$row["REPORT_DETAILS"].'</td><td>'.$messages.'</td><td>'.$flagRaised.$modDelCfm.'</td><td><span class="">'.$ENGINE->time_ago($row["TIME"]).'</span></td>'.$treater.'</tr>';
							
						}

						$reportsTable = '<div class="table-responsive" ><table class="table-classic" ><tr><th>REPORT ID</th><th>REPORTER</th>
							<th>REPORTEE</th><th>COMMENT</th><th>POST</th><th>FLAG</th><th>REPORT TIME </th><th>TREATED BY</th></tr>'.$reportsTable.'</table></div>';
				
					}else
						$alertUser = '<span class="alert alert-danger">sorry no reports has been filed in your section yet</span>';
				
				}		

			}
			
			/////////MANAGE BAN///////////////			

			if(isset($_POST["process"])){
				
				$username = $_POST["username"];
				$uid = $ACCOUNT->memberIdToggle($username, true);
				$usernameSlug = $ACCOUNT->sanitizeUserSlug($username);
				$banDur = $ENGINE->sanitize_number($_POST["ban_dur"]);
				$action = $ENGINE->sanitize_user_input($_POST[$actionKey]);
				$reason = $ENGINE->sanitize_user_input($_POST["reason"]);									
				
				$banDurPassed = !($action == $banOpt && !$banDur);				
				
				if($banDurPassed && $username){						
					
					if($sessUid != $uid){
							
						list($ranksHigher, $ranksEqual) = $ACCOUNT->sessionRanksHigher($username);
						
						if($ranksHigher){

							///VERIFY IF THE UID IS REGISTERED/////
									
							if($ACCOUNT->memberIdToggle($username)){
										
								if($action == $banOpt){
									
									/////CONVERT BAN DURATION FROM HR TO UTC DATE//////
									
									$banStat = 1;

									//////CHECK IF THE USER IS ALREADY UNDER A BAN////////
						
									///////////PDO QUERY/////////
							
									$sql = "SELECT BAN_DURATION, TIME  FROM moderation_bans WHERE USER_ID = ? AND BAN_STATUS=1 LIMIT 1";

									$valArr = array($uid);
									$stmt = $dbm->doSecuredQuery($sql, $valArr);
									$row = $dbm->fetchRow($stmt);
									
									if(!empty($row)){
										
										list($mbd, $modBanHrs) = $ENGINE->time_difference($row["BAN_DURATION"], $row["TIME"], true);
										$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', <a href="/'.$usernameSlug.'" class="links" >'.$username.'</a> is currently under '.$modBanHrs.' hour(s) ban</span>';
										
									}else{

										/***CHECK IF THIS IS THE FIRST TIME THE USER IS BEING BAN AND INSERT OR UPDATE DB ACCORDINGLY**/
								
										///////////PDO QUERY/////
							
										$sql = "SELECT ID FROM moderation_bans WHERE USER_ID = ? LIMIT 1";
										$valArr = array($uid);
										
										if($dbm->doSecuredQuery($sql, $valArr)->fetchColumn()){
									
											///////////PDO QUERY////
							
											$sql = "UPDATE moderation_bans SET BAN_DURATION=(NOW() + INTERVAL ? HOUR), REASONS=?, TREATED_BY_USER_ID=?, BAN_STATUS=?, TIME=NOW() WHERE USER_ID=? LIMIT 1 ";
											$valArr = array($banDur, $reason, $sessUid, $banStat, $uid);
				
											if($stmt = $dbm->doSecuredQuery($sql, $valArr))
																		
												//////////UPDATE BAN_COUNTER IN LOGIN FORM////////														
													
												$ACCOUNT->updateUser($username, $cols = " BAN_COUNTER = (BAN_COUNTER + 1)");
											
										
										}else{																	
								
											///////////PDO QUERY/////
							
											$sql = "INSERT INTO moderation_bans (USER_ID, BAN_DURATION, REASONS, TREATED_BY_USER_ID, BAN_STATUS, TIME) VALUES(?,(NOW() + INTERVAL ? HOUR),?,?,?,NOW()) ";

											$valArr = array($uid, $banDur, $reason, $sessUid, $banStat);
				
											if($stmt = $dbm->doSecuredQuery($sql, $valArr))
																								
												//////////UPDATE BAN_COUNTER IN LOGIN FORM////////																					
														
												$ACCOUNT->updateUser($username, $cols = " BAN_COUNTER = (BAN_COUNTER + 1)");
											
																
										}
							
										$alertUser = '<span class="alert alert-success">'.$GLOBAL_sessionUrl.' have successfully banned <a href="/'.$usernameSlug.'" class="links" >'.$username.'</a></span>';		
																
										////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
										$SITE->formGateRefresh($alertUser);
							
									}
						
								}elseif($action == $unbanOpt){
									
									$banDur = $banStat = 0;
						
									//PDO QUERY///////
							
									$sql = "SELECT ID FROM moderation_bans WHERE USER_ID = ? AND BAN_STATUS=1 LIMIT 1";
									$sql2 = "SELECT ID FROM spam_controls WHERE USER_ID = ? AND BAN_STATUS=1 LIMIT 1";	
									$valArr = array($uid);
											
									if($dbm->doSecuredQuery($sql, $valArr)->fetchColumn() || $dbm->doSecuredQuery($sql2, $valArr)->fetchColumn()){
										
										///////////PDO QUERY///
							
										$sql = "DELETE FROM moderation_bans WHERE USER_ID=? AND BAN_STATUS=1 LIMIT 1 ";
										$stmt = $dbm->doSecuredQuery($sql, $valArr);		

							
										if($GLOBAL_isAdmin){
										
											///////////PDO QUERY/////
								
											$sql = "DELETE FROM spam_controls WHERE BAN_STATUS=1 AND USER_ID=? LIMIT 1 ";
											$stmt = $dbm->doSecuredQuery($sql, $valArr);		
																				 
							
										}

										$alertUser = '<span class="alert alert-success">'.$GLOBAL_sessionUrl.' have successfully unbanned <a href="/'.$usernameSlug.'" class="links" >'.$username.'</a> </span>';
																								
										////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
										$SITE->formGateRefresh($alertUser);
								
									}else
										$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', <a href="/'.$usernameSlug.'" class="links" >'.$username.'</a> is not under a ban</span>';
							

								}elseif($action == $terminateOpt){
																
									$alertUser = '<div class="modal-drop has-close-btn" id="'.($K='adminTerminateCfm').'">													
														You are about to <b class="red">schedule termination</b> of <a href="/'.$usernameSlug.'"
														class="links" >'.$username.'</a>\'s account<br/><br/>
														Please confirm<br/><br/>											
														<input type="button" class="btn btn-success terminate_by_admin" value="OK" data-uid="'.$username.'"  />
														<input type="button" class="btn close-toggle" value="CLOSE" data-close-target="'.$K.'" />											
													</div>';							

								}
							
							}else
								$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', <a href="/'.$usernameSlug.'" class="links" >'.$username.'</a> is not recognized as a member of this community<br/>
													Please verify the username you entered and try again</span>';

						}else
							$alertUser = '<span class="alert alert-danger">sorry '.$GLOBAL_sessionUrl_unOnly.', <a href="/'.$usernameSlug.'" class="links" >'.$username.'</a> '.($ranksEqual? 'has equal ranks with you,' : 'outranks you,').' 
							hence you do not have<br/> enough privilege to manage him</span>';
					}else
						$alertUser = '<span class="alert alert-danger">Sorry '.$GLOBAL_sessionUrl_unOnly.' you cannot ban, unban or terminate yourself</span>';
					
				}else
					$rqd = '<span class="alert alert-danger" >Please fill out the required fields( leave the ban duration field empty if you want to unban or terminate the user)</span>';
				
			}
						
			$tabLink = $SITE->buildLinearNav(array(
				"baseUrl" => $baseUrl,				
				"urlList" => $urlList,							
				"active" => $tab																				
			
			));

		}else
			$notLogged = $GLOBAL_notLogged;
		
		$asterix = '<span class="asterix">*</span>';									 
							
		if($tab == $banMngrTab)
			list($bannedUserLoaded) = $SITE->loadBannedUsers($pageUrl);
		
		$SITE->buildPageHtml(array("pageTitle"=>$pageTitle,
					"preBodyMetas"=>$SITE->getNavBreadcrumbs($subNav),
					"pageBody"=>'
						<div class="single-base blend">
							<div class="base-ctrl">'.
								$notLogged.(isset($noViewPrivilege)? $noViewPrivilege : '').
								
								(($sessUsername && $GLOBAL_isStaff)?  
									
									(($tab == $reportMngrTab)? '																											
										<div class="panel panel-gold">	
											<h1 id="tab" class="panel-head page-title">TABLE OF REPORTS:</h1>
											<div class="panel-body">'.
												(isset($tabLink)? $tabLink : '').
												(isset($pageId)? '<h2>Page (<span class="cyan">'.$pageId.'</span> of '.$totalPage.')</h2>' : '').
												$alertUser.(isset($pagination)? $pagination : '').
												(isset($reportsTable)? $reportsTable : '').
												(isset($pagination)? $pagination : '').'														 							 
											</div>
										</div>'
									
									: (($tab == $banMngrTab)?	'												
										<div class="panel panel-red">
											<h1 id="mban" class="panel-head page-title">MANAGE BANS</h1>
											<div class="panel-body">'.									
												(isset($tabLink)? $tabLink : '').'
												<div class="alert alert-warning">NOTE: you can only ban users you outrank</div>'.
												$alertUser.$rqd.'																												
												<div id="ajax_res" class="cyan"></div>
												<form method="post" action="/'.$pageSelf.'#mban">
													<div class="field-ctrl">
														<label>Username'.($rqd? $asterix : '').':</label>
														<input  class="field" type="text" name="'.($K='username').'" value="'.(isset($_POST[$K])? $ENGINE->sanitize_user_input($_POST[$K]) : '').'" placeholder="Enter here the registered username of the member you wish to ban "  />
													</div>
													<div class="field-ctrl">
														<label>Ban Duration(in hours)'.(($rqd && $action != $unbanOpt)? $asterix : '').':'.($GLOBAL_isTopStaff? '<small class="red"> Leave blank if unbanning or terminating</small>' : '').'</label>
														<input class="field" type="number" min="1" name="ban_dur" value="'.(isset($banDur)? $banDur : '').'" placeholder="Enter here the duration(in hours) of the ban you wish to give the member "  />
													</div>
													<div class="field-ctrl">
														<label>Action:</label>						
														<select name="'.$actionKey.'" class="field" >
															<option value="'.$banOpt.'" '.(($action == $banOpt)? $optionSelected : '').' > Ban this user</option>
															<option value="'.$unbanOpt.'" '.(($action == $unbanOpt)? $optionSelected : '').' > Unban this user</option>'.
															($GLOBAL_isTopStaff? '<option value="'.$terminateOpt.'" '.(($action == $terminateOpt)? $optionSelected : '').'>Terminate User</option>' : '').'
														</select>
													</div>
													<div class="field-ctrl">
														<label>Reason for Ban/Unban: <small class="prime">optional</small></label>
														<textarea class="field" name="reason" placeholder="Please briefly describe the reason why this user is being banned/unbanned">'.(isset($reason)? $reason : '').'</textarea>
													</div>
													<div class="field-ctrl btn-ctrl">
														<input type="submit" class="form-btn btn-danger" name="process" value="PROCESS"  />
													</div>
												</form>'.$bannedUserLoaded.'
											</div>
										</div>'																			
									: ''))		
								: '').'													
							</div>
						</div>'
		));
						
		break;									 
							
	}
	
	
	
	
	
	
	
	/**DB MANAGER**/
	case 'db-manager':{

		///////AUTHORIZE TOP LEVEL ACCESS///////////
		$ACCOUNT->authorizeTopAccess(false);

		$sections=$categories=$pops=$badges=$autoPilots=$uibgs=$emoticons=$daemons=$countries=
		$tabLink=$pops=$pid=$id=$headTitle=$headTitlePlural="";		
		

		/***************************BEGIN URL CONTROLLER****************************/

		$page_self_srt = $ENGINE->get_page_path('page_url', 2);
		$pageSelfSortSrch = $ENGINE->get_page_path('page_url', 4);
		$path1 = isset($pagePathArr[1])? strtolower($pagePathArr[1]) : '';		
		$baseUrl = $pageUrlLowerCase;
		$pageUrl = $baseUrl.'/'.$path1;			
				
		$urlList = array(//FORMAT => urlSlug:urlLabel:urlIcon:ignoreCond
			($sectionMngrTab = 'manage-sections').':sections', 
			($categMngrTab = 'manage-categories').':categories',
			($popMngrTab = 'manage-pops').':pops manager',
			($autoPilotMngrTab = 'manage-auto-pilots').':Auto Pilots',
			($uibgMngrTab = 'manage-ui-bg').':ui bgs',
			($emoticonMngrTab = 'manage-emoticons').':emoticons',
			($badgeMngrTab = 'manage-badges').':badges',
			($daemonMngrTab = 'manage-daemons').':daemons',
			($countryMngrTab = 'manage-countries').':countries',
		);		

									
		if($path1 == $sectionMngrTab){									 
										
			$pathKeysArr = array('pageUrl', 'tab', 'sort', 'filter', 'orderFlow', $pageKey='page_ids');			
			$maxPath = 6;			
			$subNav = '<li><a href="/'.$pageSelf.'" title="">Section Manager</a></li>';
			$headTitle = 'Section';
			$headTitlePlural = $headTitle.'s';									 
			$urlHash = 'scat';									 
							
		}elseif($path1 == $categMngrTab){										 
									
			$pathKeysArr = array('pageUrl', 'tab', $pageKey='page_idc');					
			$maxPath = 3;			
			$subNav = '<li><a href="/'.$pageSelf.'" title="">Category Manager</a></li>';	
			$headTitle = 'Category';
			$headTitlePlural = 'Categories';							 
			$urlHash = 'cat';									 
							
		}elseif($path1 == $popMngrTab){									 
										
			$pathKeysArr = array('pageUrl', 'tab', $pageKey='page_idp');				
			$maxPath = 3;
			$subNav = '<li><a href="/'.$pageSelf.'" title="">Pops Manager</a></li>';	
			$headTitle = 'Pop';
			$headTitlePlural = $headTitle.'s';								 
			$urlHash = 'pop';								 
							
		}elseif($path1 == $badgeMngrTab){										 
									
			$pathKeysArr = array('pageUrl', 'tab', 'sort', $pageKey='page_idb');					
			$maxPath = 4;
			$subNav = '<li><a href="/'.$pageSelf.'" title="">Badge Manager</a></li>';	
			$headTitle = 'Badge';
			$headTitlePlural = $headTitle.'s';								 
			$urlHash = 'bdg';								 
							
		}elseif($path1 == $autoPilotMngrTab){									 
										
			$pathKeysArr = array('pageUrl', 'tab', $pageKey='page_idpi');					
			$maxPath = 3;
			$subNav = '<li><a href="/'.$pageSelf.'" title="">Auto Pilot Manager</a></li>';	
			$headTitle = 'Pilot';
			$headTitlePlural = $headTitle.'s';								 
			$urlHash = 'pilot';								 
							
		}elseif($path1 == $daemonMngrTab){									 
										
			$pathKeysArr = array('pageUrl', 'tab', $pageKey='page_idDae');					
			$maxPath = 3;
			$subNav = '<li><a href="/'.$pageSelf.'" title="">Daemon Manager</a></li>';	
			$headTitle = 'Daemon';
			$headTitlePlural = $headTitle.'s';								 
			$urlHash = 'daemons';								 
							
		}elseif($path1 == $uibgMngrTab){									 
										
			$pathKeysArr = array('pageUrl', 'tab', 'sort', $pageKey='page_idui');				
			$maxPath = 4;			
			$subNav = '<li><a href="/'.$pageSelf.'" title="">UI BG Manager</a></li>';	
			$headTitle = 'uibg';
			$headTitlePlural = $headTitle.'s';								 
			$urlHash = 'uibg';								 
							
		}elseif($path1 == $emoticonMngrTab){									 
										
			$pathKeysArr = array('pageUrl', 'tab', $pageKey='page_idemo');				
			$maxPath = 3;
			$subNav = '<li><a href="/'.$pageSelf.'" title="">Emoticons Manager</a></li>';	
			$headTitle = 'emoticon';
			$headTitlePlural = $headTitle.'s';								 
			$urlHash = 'emo';								 
							
		}elseif($path1 == $countryMngrTab){									 
													
			$pathKeysArr = array('pageUrl', 'tab', 'sort', 'filter', 'orderFlow', $pageKey='page_idCtry');
			$maxPath = 6;			
			$subNav = '<li><a href="/'.$pageSelf.'" title="">Countries Manager</a></li>';	
			$headTitle = 'country';
			$headTitlePlural = 'countries';							 
			$urlHash = 'ctry';									 
							
		}else{									 
							
			$pathKeysArr = array();
			$maxPath = 0;									 
							
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		if(isset($_GET[$K="tab"]))
			$tab = strtolower($ENGINE->sanitize_user_input($_GET[$K]));

		//////////GET FORM-GATE RESPONSE///////
		$alertAdminUser = $SITE->formGateRefreshResponse();

		$dataUrl = ' data-url="/'.$pageSelf.'" ';
		$dataAdding = ' data-adding="1" ';
		$fieldEditable = ' contenteditable="true" ';
		$pageCount = 100;
		
		$adsCampaign = new AdsCampaign();
		$campaignMetas = $adsCampaign->getMeta("static");
		$banner1Details = $adsCampaign->getBanner1Details();
		$banner2Details = $adsCampaign->getBanner2Details();
		$banner3Details = $adsCampaign->getBanner3Details();
		
		if($sessUsername){			
			
			//////CALL LIVES/////////
			$SITE->doAdminLiveUnlocks();
			$SITE->doAdminLiveEdit();
			$SITE->doAdminLiveAdd();
			$SITE->doAdminLiveDelete();
			
				
			//GRAB PAGE RENDER PARAMS
			$getSortBy = (isset($_GET[$K="sort"]))? $ENGINE->sanitize_user_input($_GET[$K]) : '';	
			$filterBy = (isset($_GET[$K="filter"]))? $ENGINE->sanitize_user_input($_GET[$K]) : '';	
			$orderFlow = (isset($_GET[$K="orderFlow"]))? $ENGINE->sanitize_user_input($_GET[$K]) : '';
			$sq = isset($_POST[$K='sq'])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : '');
				
			switch(strtolower($orderFlow)){									 
						
				case 'desc':		
					$orderFlow = " DESC ";
					$orderFlowLnk = 'desc';
					break;									 
										
				default:
					$orderFlow = " ASC ";
					$orderFlowLnk = 'asc';									 
						
			}
				
			
			if($tab == $categMngrTab){
				
				#######CATEGORIES##########
					
				/////PDO QUERY/////
				$table = ' categories ';	
				$tableDecoy = 'cat';	
				$F = array('cat' => ' data-name="cat-name" ', 'catDesc' => ' data-name="cat-desc" ');
				
				$totalRecords = $dbm->getTableCount($table);
				
				/**********CREATE THE PAGINATION******/
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>$pageKey,'jmpKey'=>'jump_page_c','perPage'=>$pageCount,'hash'=>$urlHash));
				$pagination_cat = $paginationArr["pagination"];
				$total_page_c = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$page_idc = $paginationArr["pageId"];
				
				$sql = "SELECT * FROM ".$table." LIMIT ".$startIndex.",".$perPage;
				$valArr = array();
				$stmt = $dbm->doSecuredQuery($sql, $valArr);									 
							
				while($row = $dbm->fetchRow($stmt)){									 
							
					$editParam = 'class="live-edit"  data-table="'.$tableDecoy.'" '.$dataUrl;			
					$cid = $row["ID"];
					$dataId = ' data-id="'.$cid.'" ';									 
							
					if((isset($_SESSION[$K=ADMIN_EDIT_CAT]) && $_SESSION[$K] == $cid) || (isset($_SESSION[$K=ADMIN_EDIT_ALL_CAT]) && $_SESSION[$K]))
						$editParam .= $fieldEditable;									 
							
					$categories .= '<tr>
									<td>'.$cid.'</td>
									<td '.$F['cat'].$editParam.$dataId.' >'.$row["CATEG_NAME"].'</td>
									<td '.$F['catDesc'].$editParam.$dataId.' >'.$row["CATEG_DESC"].'</td>
									'.$SITE->getLiveTableAdminControls($tableDecoy, $cid, $dataUrl, ADMIN_DEL_CAT).'
								</tr>';									 
							
				}
				
				$dataCat = $dataAdding.$fieldEditable;
				
				$rowAdder = '<tr>
								<td></td>						
								<td '.$dataCat.$F['cat'].'></td>
								<td '.$dataCat.$F['catDesc'].'></td>
								'.$SITE->getLiveTableAddControls($tableDecoy, $dataUrl).'
							</tr>';	
						
				$categories = $rowAdder.$categories.$rowAdder;								 
							
				$categories = '<div class="table-responsive">
									<table class="table-classic">
										<thead>
											<tr>
												<th>ID</th><th>NAME</th><th>DESCRIPTION</th><th>CTRL</th>
											</tr>
										</thead>
										<tbody>'.$categories.'</tbody>
									</table>
								</div>';
				
				
			}elseif($tab == $sectionMngrTab){
				
				######SECTIONS#######
													 
							
				switch(strtolower($filterBy)){									 
							
					case 'has-premium-discount':		
						$filterCnd = " DISCOUNT_IS_PREMIUM=1 ";
						break;									 
							
					case 'on-premium-rate':		
						$filterCnd = " ON_PREMIUM_RATE=1 ";
						break;										 
							
					case 'has-premium-rate':		
						$filterCnd = " MIN_PREMIUM_RATE>0 ";
						break;										 
										
					default:
						$filterCnd = "";
						$filterBy = 'none';									 
							
				}									 
							
				$valArr = array();									 
							
				if($sq){									 
							
					$filterCnd = "( SECTION_NAME LIKE ? ".($filterCnd? "AND ".$filterCnd : "").")";
					$valArr[] = '%'.$sq.'%';									 
							
				}
				
				$filterCnd? ($filterCnd = ' WHERE '.$filterCnd) : '';
				
				
				/////PDO QUERY/////
				$table = ' sections ';
				$tableDecoy = 'scat';
				$F = array('accessLevel' => ' data-name="access-level" ', 'cid' => ' data-name="cat-id" ', 'scat' => ' data-name="scat-name" ', 
							'scatDesc' => ' data-name="scat-desc" ', 'pscat' => ' data-name="pscat" ', 
							'fpVis' => ' data-name="fp-visible" ', 'minRate' => ' data-name="min-rate" ', 
							'minPrmRate' => ' data-name="min-prem-rate" ',  'minDisc' => ' data-name="min-disc" ', 
							'onPrm' => ' data-name="on-prem-rate" ', 'discPrm' => ' data-name="disc-is-prem" '
						);
				
				$sql =  "SELECT COUNT(*) FROM ".$table.$filterCnd;
				$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
				
				$popularitySubQry='';
				$altOrderCol = ", SECTION_NAME ".$orderFlow;									 
							
				switch(strtolower($getSortBy)){									 
							
					case 'access-level':
						$orderUsed = "ACCESS_LEVEL ".$orderFlow.$altOrderCol;
						break;									 
							
					case 'parent-id':
						$orderUsed = "PARENT_SECTION ".$orderFlow.$altOrderCol;
						break;									 
							
					case 'alphabet':
						$orderUsed = "SECTION_NAME ".$orderFlow;
						break;										 
							
					case 'popularity':
						$popularitySubQry = $SITE->getPopularitySubQuery($table);
						$orderUsed = "POPULARITY ".$orderFlow;
						break;										 
								
					case 'min-ad-rate':
						$orderUsed = "MIN_AD_RATE ".$orderFlow.$altOrderCol;
						break;									 
							
					case 'min-premium-rate':
						$orderUsed = "MIN_PREMIUM_RATE ".$orderFlow.$altOrderCol;
						break;									 
							
					case 'min-discount':		
						$orderUsed = "MIN_DISCOUNT_RATE ".$orderFlow.$altOrderCol;
						break;									 
										
					default:
						$orderUsed = "ID ".$orderFlow;
						$getSortBy = 'id';									 
							
				}									 
							
				$orderUsed = ' ORDER BY '.$orderUsed;
				
				/**********CREATE THE PAGINATION******/										
				
				$qstrValArr = array($getSortBy, $filterBy, $orderFlowLnk);	
				$qstrKeyValArr = array('sq' => $sq);		
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>$pageKey,'jmpKey'=>'jump_page_s','qstrVal'=>$qstrValArr,'qstrKeyVal'=>$qstrKeyValArr,'perPage'=>$pageCount,'hash'=>$urlHash));
				$pagination_scat = $paginationArr["pagination"];
				$totalPageSrch = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$page_ids = $paginationArr["pageId"];
			
				$sortNav = $SITE->buildSortLinks(array(
							'baseUrl' => $page_self_srt, 'pageId' => $page_ids, 'sq' => $sq, 'urlHash' => $urlHash, 
							'activeOrder' => $getSortBy, 'orderList' => array('id', 'access-level:Access Level', 'alphabet', 'popularity.desc', 'parent-id:Parent Id', 'min-ad-rate:Min Ad Rate', 'min-premium-rate:Min Premium Rate', 'min-discount:Min Discount'), 
							'activeOrderFlow' => $orderFlowLnk, 'orderFlowList' => array('asc', 'desc'), 
							'activeFilter' => $filterBy, 'filterList' => array('none', 'has-premium-rate:Has Premium Rate', 'has-premium-discount:Has Premium Discount', 'on-premium-rate:On Premium Rate')
							)).
							$SITE->getSearchForm(array('url' => '/'.$pageSelf, 'fieldName' => 'sq', 'pageResetUrl' => $pageUrl, 
							'fieldLabel' => '', 'fieldPH' => 'Search by section name', 'btnName' => 'search', 'btnLabel' => 'GO'));
						
				
				$sql = "SELECT ".$table.".ID, CATEG_ID, SECTION_NAME, SECTION_DESC, PARENT_SECTION, FP_VISIBLE, ACCESS_LEVEL ".$popularitySubQry." FROM ".$table.$filterCnd.$orderUsed." LIMIT ".$startIndex.",".$perPage;
				$stmt = $dbm->doSecuredQuery($sql, $valArr);									 
							
				if($totalRecords){									 
							
					while($row = $dbm->fetchRow($stmt)){										 
														
						$editParam = 'class="live-edit"  data-table="'.$tableDecoy.'" '.$dataUrl;			
						$sid = $row["ID"];
						$dataId = ' data-id="'.$sid.'" ';
						$allRatesArr = $adsCampaign->getAdRate(array("sid"=>$sid,"type"=>"all","appendToAll"=>true,"forTable"=>true,"noHeader"=>true));
						
						if((isset($_SESSION[$K=ADMIN_EDIT_SCAT]) && $_SESSION[$K] == $sid) || (isset($_SESSION[$K=ADMIN_EDIT_ALL_SCAT]) && $_SESSION[$K]))
							$editParam .= $fieldEditable;									 
							
						$sections .= '<tr>
									<td>'.$sid.'</td>
									<td '.$F['cid'].$editParam.$dataId.'  >'.$row["CATEG_ID"].'</td>
									<td '.$F['scat'].$editParam.$dataId.'  >'.$row["SECTION_NAME"].'</td>
									<td '.$F['scatDesc'].$editParam.$dataId.' >'.$row["SECTION_DESC"].'</td>
									<td '.$F['pscat'].$editParam.$dataId.' >'.$row["PARENT_SECTION"].'</td>						
									<td '.$F['fpVis'].$editParam.$dataId.' >'.$row["FP_VISIBLE"].'</td>												
									<td '.$F['minRate'].$editParam.$dataId.' >'.$allRatesArr["minAdRate"].'</td>													
									<td '.$F['minPrmRate'].$editParam.$dataId.' >'.$allRatesArr["minPremiumRate"].'</td>												
									<td '.$F['minDisc'].$editParam.$dataId.' >'.$allRatesArr["minDiscountRate"].'</td>							
									<td '.$F['discPrm'].$editParam.$dataId.' >'.$allRatesArr["discountIsPremium"].'</td>											
									<td '.$F['onPrm'].$editParam.$dataId.' >'.$allRatesArr["onPremiumRate"].'</td>	
									<td>'.$allRatesArr['all'].'</td>
									<td '.$F['accessLevel'].$editParam.$dataId.' >'.$row["ACCESS_LEVEL"].'</td>
									'.$SITE->getLiveTableAdminControls($tableDecoy, $sid, $dataUrl, ADMIN_DEL_SCAT).'												
								</tr>';									 
							
									
					}
					
					$dataScat = $dataAdding.$fieldEditable;	
					
					$rowAdder = '<tr>
									<td></td>
									<td '.$dataScat.$F['cid'].'></td>
									<td '.$dataScat.$F['scat'].'></td>
									<td '.$dataScat.$F['scatDesc'].'></td>
									<td '.$dataScat.$F['pscat'].'></td>																
									<td '.$dataScat.$F['fpVis'].'></td>												
									<td '.$dataScat.$F['minRate'].'></td>											
									<td '.$dataScat.$F['minPrmRate'].'></td>												
									<td '.$dataScat.$F['minDisc'].'></td>											
									<td '.$dataScat.$F['discPrm'].'></td>												
									<td '.$dataScat.$F['onPrm'].'></td>
									<td></td>
									<td '.$dataScat.$F['accessLevel'].'></td>
									'.$SITE->getLiveTableAddControls($tableDecoy, $dataUrl).'												
								</tr>';
						
					$sections = $rowAdder.$sections.$rowAdder;
							
					$sections = '<div class="table-responsive">
									<table data-tables-ctrlX="true" class="table-classicx">
										<thead>
											<tr>
												<th>ID</th><th>CID</th><th>NAME</th><th>DESC</th><th>PARENT</th>
												<th>FPV</th><th>MIN AD RATE</th><th><span class="red">MIN PREM. RATE (n%)</span></th>
												<th>MIN DISC (%)</th><th><span class="red">IS PREM. DISC</span></th>
												<th><span class="red">ON PREM. RATE</span></th><th><span class="blue">RATE SUMMARY</span></th>
												<th>ACCESS LEVEL</th><th>CTRL</th>
											</tr>
										</thead>
										<tbody>'.$sections.'</tbody>
									</table>
								</div>';
					
				}else
					$sections = '<span class="alert alert-danger">Sorry no section was found matching your request</span>';
						
						
			}elseif($tab == $popMngrTab){
							
				######POPS########		
					
				/////PDO QUERY/////
				$table = ' pop_outs ';
				$tableDecoy = 'pop';	
				$F = array('content' => ' data-name="pop-content" ', 'state' => ' data-name="pop-state" ', 
							'tgt' => ' data-name="pop-target" ', 'relvc' => ' data-name="pop-relevance" '
						);
				
				$sql =  "SELECT COUNT(*) FROM ".$table;
				$totalRecords = $dbm->query($sql)->fetchColumn();
				
				/**********CREATE THE PAGINATION******/
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>$pageKey,'jmpKey'=>'jump_page_p','perPage'=>$pageCount,'hash'=>$urlHash));
				$pagination_pops = $paginationArr["pagination"];
				$total_page_p = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$page_idp = $paginationArr["pageId"];
				
				$sql = "SELECT * FROM ".$table." LIMIT ".$startIndex.",".$perPage;
				$valArr = array();
				$stmt = $dbm->doSecuredQuery($sql, $valArr);									 
							
				while($row = $dbm->fetchRow($stmt)){										 
							
					$editParam = 'class="live-edit"  data-table="'.$tableDecoy.'" '.$dataUrl;			
					$pid = $row["ID"];
					$dataId = '  data-id="'.$pid.'" ';									 
							
					if((isset($_SESSION[$K=ADMIN_EDIT_POP]) && $_SESSION[$K] == $pid) || (isset($_SESSION[$K=ADMIN_EDIT_ALL_POP]) && $_SESSION[$K]))
						$editParam .= $fieldEditable;									 
							
					$pops .= '<tr>
									<td>'.$pid.'</td>
									<td '.$F['content'].$editParam.$dataId.' >'.htmlentities($row["CONTENT"]).'</td>
									<td '.$F['state'].$editParam.$dataId.' >'.$row["STATE"].'</td>
									<td '.$F['tgt'].$editParam.$dataId.' >'.$row["TARGET"].'</td>
									<td '.$F['relvc'].$editParam.$dataId.' >'.$row["RELEVANCE"].'</td>
									<td>'.$ENGINE->time_ago($row["TIME"]).'</td>
									'.$SITE->getLiveTableAdminControls($tableDecoy, $pid, $dataUrl, ADMIN_DEL_POP).'
								</tr>';									 
							
				}
				
				$dataPop = $dataAdding.$fieldEditable;
				
				$rowAdder = '<tr>
								<td></td>						
								<td '.$dataPop.$F['content'].'></td>
								<td '.$dataPop.$F['state'].'></td>
								<td '.$dataPop.$F['tgt'].'></td>
								<td '.$dataPop.$F['relvc'].'></td>
								<td ></td>
								'.$SITE->getLiveTableAddControls($tableDecoy, $dataUrl).'						
							</tr>';
						
				$pops = $rowAdder.$pops.$rowAdder;									 
							
				$pops = '<div class="table-responsive">
							<table>
								<thead>
									<tr>
										<th>ID</th><th>CONTENT</th><th>STATE</th><th>TARGET</th>
										<th>RELEVANCE</th><th>TIME</th><th>CTRL</th>
									</tr>
								</thead>
								<tbody>'.$pops.'</tbody>
							</table>
						</div>';
								
			}elseif($tab == $badgeMngrTab){
							
				######BADGE########		
					
				/////PDO QUERY/////
				$table = ' badges ';	
				$tableDecoy = 'badge';
				$F = array('cat' => ' data-name="badge-cat" ', 'name' => ' data-name="badge-name" ', 
							'criteria' => ' data-name="criteria" ', 'class' => ' data-name="class" ', 
							'freq' => ' data-name="freq" ', 'reward' => ' data-name="rep-reward" '
						);
				
				$sql =  "SELECT COUNT(*) FROM ".$table;
				$totalRecords = $dbm->query($sql)->fetchColumn();
				
				/**********CREATE THE PAGINATION******/
				
				$qstrValArr = array($getSortBy);
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>$pageKey,'jmpKey'=>'jump_page_b','qstrVal'=>$qstrValArr,'perPage'=>$pageCount,'hash'=>$urlHash));
				$pagination_badge = $paginationArr["pagination"];
				$total_page_b = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$page_idb = $paginationArr["pageId"];
			
				switch(strtolower($getSortBy)){									 
							
					case 'reputation':
						$orderUsed = "REPUTATION_REWARD ASC, BADGE_NAME ASC ";
						break;									 
							
					case 'class':
						$orderUsed = "CLASS ASC, BADGE_NAME ASC ";
						break;									 
										
					case 'alphabet':
						$orderUsed = "BADGE_NAME ASC ";
						break;									 
							
					case 'category':
						$orderUsed = "CATEGORY ASC, BADGE_NAME ASC ";
						break;									 
							
					default:
						$orderUsed = "ID ASC ";
						$getSortBy = 'id';									 
							
				}									 
							
				$orderUsed = ' ORDER BY '.$orderUsed;

				$sortNav = $SITE->buildSortLinks(array(
							'baseUrl' => $page_self_srt, 'pageId' => $page_idb, 'sq' => '', 'urlHash' => $urlHash, 
							'activeOrder' => $getSortBy, 'orderList' => array('category', 'alphabet', 'id', 'class', 'reputation')
							));
			
			
				$sql = "SELECT * FROM ".$table." ".$orderUsed." LIMIT ".$startIndex.",".$perPage;
				$valArr = array();
				$stmt = $dbm->doSecuredQuery($sql, $valArr);									 
							
				while($row = $dbm->fetchRow($stmt)){									 
							
					$editParam = 'class="live-edit"  data-table="'.$tableDecoy.'" '.$dataUrl;			
					$id = $row["ID"];
					$dataId = ' data-id="'.$id.'" ';									 
							
					if((isset($_SESSION[$K=ADMIN_EDIT_BADGE]) && $_SESSION[$K] == $id) || (isset($_SESSION[$K=ADMIN_EDIT_ALL_BADGE]) && $_SESSION[$K]))
						$editParam .= $fieldEditable;									 
							
					$badges .= '<tr>
									<td>'.$id.'</td>
									<td '.$F['cat'].$editParam.$dataId.' >'.$row["CATEGORY"].'</td>
									<td '.$F['name'].$editParam.$dataId.' >'.htmlentities($row["BADGE_NAME"]).'</td>
									<td '.$F['criteria'].$editParam.$dataId.' >'.htmlentities($row["CRITERIA"]).'</td>
									<td '.$F['class'].$editParam.$dataId.' >'.$row["CLASS"].'</td>
									<td '.$F['freq'].$editParam.$dataId.' >'.$row["AWARD_FREQ"].'</td>
									<td '.$F['reward'].$editParam.$dataId.' >'.$row["REPUTATION_REWARD"].'</td>
									<td>'.$badgesAndReputations->loadBadges(array('bid'=>$id, 'appendCount'=>false,  'wrapper'=>'<span>')).'</td>
									'.$SITE->getLiveTableAdminControls($tableDecoy, $id, $dataUrl, ADMIN_DEL_BADGE).'
								</tr>';									 
							
				}
				
				$dataBadge = $dataAdding.$fieldEditable;
				
				$rowAdder = '<tr>
								<td></td>						
								<td '.$dataBadge.$F['cat'].'></td>
								<td '.$dataBadge.$F['name'].'></td>
								<td '.$dataBadge.$F['criteria'].'></td>
								<td '.$dataBadge.$F['class'].'></td>
								<td '.$dataBadge.$F['freq'].'></td>
								<td '.$dataBadge.$F['reward'].'></td>
								<td></td>
								'.$SITE->getLiveTableAddControls($tableDecoy, $dataUrl).'					
							</tr>';	
						
				$badges = $rowAdder.$badges.$rowAdder;								 
							
				$badges = '<div class="table-responsive">
								<table>
									<thead>
										<tr>
											<th>ID</th><th>CATEG</th><th>BADGE NAME</th><th>CRITERIA</th>
											<th>CLASS</th><th>FREQ <br/>(S/M)</th><th>REPUTATION REWARD</th>
											<th>VIEW</th><th>CTRL</th>
										</tr>
									</thead>
									<tbody>'.$badges.'</tbody>
								</table>
							</div>';
								
			}elseif($tab == $autoPilotMngrTab){
							
				######AUTO PILOT########
				
				/////PDO QUERY/////
				$table = ' auto_pilots ';	
				$tableDecoy = 'auto-pilot';	
				$F = array('name' => ' data-name="pilot-name" ', 'stateName' => ' data-name="state-name" ', 
							'state' => ' data-name="status" '
						);
				
				$sql =  "SELECT COUNT(*) FROM ".$table;
				$totalRecords = $dbm->query($sql)->fetchColumn();
				
				/**********CREATE THE PAGINATION******/						
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>$pageKey,'jmpKey'=>'jump_page_pi','perPage'=>$pageCount,'hash'=>$urlHash));
				$pagination_pilot = $paginationArr["pagination"];
				$total_page_pi = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$page_idpi = $paginationArr["pageId"];				
					
				$sql = "SELECT * FROM ".$table." LIMIT ".$startIndex.",".$perPage;
				$valArr = array();
				$stmt = $dbm->doSecuredQuery($sql, $valArr);
			
				while($row = $dbm->fetchRow($stmt)){
			
					$editParam = 'class="live-edit"  data-table="'.$tableDecoy.'" '.$dataUrl;			
					$id = $row["ID"];
					$dataId = ' data-id="'.$id.'" ';
			
					if((isset($_SESSION[$K=ADMIN_EDIT_AUTO_PILOT]) && $_SESSION[$K] == $id) || (isset($_SESSION[$K=ADMIN_EDIT_ALL_AUTO_PILOT]) && $_SESSION[$K]))
						$editParam .= $fieldEditable;
			
					$autoPilots .= '<tr>
									<td>'.$id.'</td>
									<td '.$F['name'].''/*.$editParam.$dataId*/.' >'.$row["PILOT_NAME"].'</td>
									<td '.$F['stateName'].$editParam.$dataId.' >'.htmlentities($row["STATE_NAME"]).'</td>							
									<td '.$F['state'].$editParam.$dataId.' >'.htmlentities($row["STATE"]).'</td>
									'.$SITE->getLiveTableAdminControls($tableDecoy, $id, $dataUrl, ADMIN_DEL_AUTO_PILOT).'
								</tr>';
			
				}
				
				$data = $dataAdding.$fieldEditable;
				
				$rowAdder = '<tr>
								<td></td>						
								<td '.$data.$F['name'].'></td>
								<td '.$data.$F['stateName'].'></td>					
								<td '.$data.$F['state'].'></td>
								'.$SITE->getLiveTableAddControls($tableDecoy, $dataUrl).'
							</tr>';
						
				$autoPilots = $rowAdder.$autoPilots.$rowAdder;
			
				$autoPilots = '<div class="table-responsive">
									<table>
										<thead>
											<tr>
												<th>ID</th><th>PILOT NAME</th><th>STATE NAME</th>
												<th>STATE</th><th>CTRL</th>
											</tr>
										</thead>
										<tbody>'.$autoPilots.'</tbody>
									</table>
								</div>';
								
			}elseif($tab == $daemonMngrTab){
							
				######DAEMONS########
				
				/////PDO QUERY/////
				$table = ' daemons ';	
				$tableDecoy = 'cron';	
				$F = array('name' => ' data-name="name" ', 'cmd' => ' data-name="cmd" ', 
							'cycleInterval' => ' data-name="cycle-interval" '
						);
				
				$sql =  "SELECT COUNT(*) FROM ".$table;
				$totalRecords = $dbm->query($sql)->fetchColumn();
				
				/**********CREATE THE PAGINATION******/						
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>$pageKey,'jmpKey'=>'jump_page_dae','perPage'=>$pageCount,'hash'=>$urlHash));
				$pagination_daemons = $paginationArr["pagination"];
				$total_page_dae = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$page_idDae = $paginationArr["pageId"];				
					
				$sql = "SELECT * FROM ".$table." LIMIT ".$startIndex.",".$perPage;
				$valArr = array();
				$stmt = $dbm->doSecuredQuery($sql, $valArr);
			
				while($row = $dbm->fetchRow($stmt)){
			
					$editParam = 'class="live-edit"  data-table="'.$tableDecoy.'" '.$dataUrl;			
					$id = $row["ID"];
					$dataId = ' data-id="'.$id.'" ';
			
					if((isset($_SESSION[$K=ADMIN_EDIT_DAEMONS]) && $_SESSION[$K] == $id) || (isset($_SESSION[$K=ADMIN_EDIT_ALL_DAEMONS]) && $_SESSION[$K]))
						$editParam .= $fieldEditable;
			
					$daemons .= '<tr>
									<td>'.$id.'</td>
									<td '.$F['name'].''/*.$editParam.$dataId*/.' >'.$row["NAME"].'</td>
									<td '.$F['cmd'].$editParam.$dataId.' >'.$row["COMMAND"].'</td>							
									<td '.$F['cycleInterval'].$editParam.$dataId.' >'.$row["CYCLE_INTERVAL"].'</td>
									<td>'.$ENGINE->time_ago($row["LAST_CYCLE_TIME"]).'</td>
									'.$SITE->getLiveTableAdminControls($tableDecoy, $id, $dataUrl, ADMIN_DEL_DAEMONS).'
								</tr>';
			
				}
				
				$data = $dataAdding.$fieldEditable;
				
				$rowAdder = '<tr>
								<td></td>						
								<td '.$data.$F['name'].'></td>
								<td '.$data.$F['cmd'].'></td>					
								<td '.$data.$F['cycleInterval'].'></td>
								<td></td>
								'.$SITE->getLiveTableAddControls($tableDecoy, $dataUrl).'
							</tr>';
						
				$daemons = $rowAdder.$daemons.$rowAdder;
			
				$daemons = '<div class="table-responsive">
								<table>
									<thead>
										<tr>
											<th>ID</th><th>DAEMON NAME</th><th>CMD (RUN, PAUSE, STOP)</th>
											<th>CYCLE INTERVAL(MINS)</th><th>LAST RUN TIME</th><th>CTRL</th>
										</tr>
									</thead>
									<tbody>'.$daemons.'</tbody>
								</table>
							</div>';
								
			}elseif($tab == $uibgMngrTab){
							
				######UI BG########
				
				/////PDO QUERY/////
				$table = ' ui_bgs ';	
				$tableDecoy = 'uibg';
				$F = array('bgStyle' => ' data-name="uibg-style" ', 'contentStyle' => ' data-name="uibg-content-style" ', 
							'solidStyle' => ' data-name="uibg-solid-style" ', 'cat' => ' data-name="uibg-cat" ',
							'label' => ' data-name="uibg-label" ', 'version' => ' data-name="uibg-version" '
						);

							
				switch(strtolower($getSortBy)){
			
					case 'label':
						$orderUsed = "LABEL";
						break;
			
					case 'version':
						$orderUsed = "RENDER_VERSION";
						break;
			
					case 'solid':
						$orderUsed = "IS_SOLID_STYLE DESC";
						break;
			
					case 'category':
						$orderUsed = "CATEGORY ";
						break;
			
					default:
						$orderUsed = "ID  ";
						$getSortBy = 'id';
			
				}
				
				$sql =  "SELECT COUNT(*) FROM ".$table;
				$totalRecords = $dbm->query($sql)->fetchColumn();
				$qstrValArr = array($getSortBy);

				/**********CREATE THE PAGINATION******/						
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>$pageKey,'jmpKey'=>'jump_page_uibg','qstrVal'=>$qstrValArr,'perPage'=>$pageCount,'hash'=>$urlHash));
				$pagination_uibg = $paginationArr["pagination"];
				$total_page_uibg = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$page_iduibg = $paginationArr["pageId"];
			
				$orderUsed = ' ORDER BY '.$orderUsed;
					
				$sortNav = $SITE->buildSortLinks(array(
							'baseUrl' => $page_self_srt, 'pageId' => $page_iduibg, 'sq' => '', 'urlHash' => $urlHash, 
							'activeOrder' => $getSortBy, 'orderList' => array('category', 'solid', 'label', 'version', 'id')
							));
			
				$sql = "SELECT * FROM ".$table.$orderUsed." LIMIT ".$startIndex.",".$perPage;
				$valArr = array();
				$stmt = $dbm->doSecuredQuery($sql, $valArr);
			
				while($row = $dbm->fetchRow($stmt)){
			
					$editParam = 'class="live-edit"  data-table="'.$tableDecoy.'" '.$dataUrl;			
					$id = $row["ID"];
					$dataId = ' data-id="'.$id.'" ';
			
					if((isset($_SESSION[$K=ADMIN_EDIT_UI_BG]) && $_SESSION[$K] == $id) || (isset($_SESSION[$K=ADMIN_EDIT_ALL_UI_BG]) && $_SESSION[$K]))
						$editParam .= $fieldEditable;
			
					$uibgs .= '<tr>
									<td>'.$id.'</td>
									<td '.$F['bgStyle'].$editParam.$dataId.' >'.$row["BG_STYLES"].'</td>
									<td '.$F['contentStyle'].$editParam.$dataId.' >'.$row["CONTENT_STYLES"].'</td>
									<td '.$F['solidStyle'].$editParam.$dataId.' >'.$row["IS_SOLID_STYLE"].'</td>
									<td '.$F['cat'].$editParam.$dataId.' >'.$row["CATEGORY"].'</td>
									<td '.$F['label'].$editParam.$dataId.' >'.$row["LABEL"].'</td>
									<td '.$F['version'].$editParam.$dataId.' >'.$row["RENDER_VERSION"].'</td>
									<td class="_uibg-render-cmn" style="'.$SITE->uiBgHandler('', array('id' => $id, 'action' => 'getPerBg', 'retRaw' => true)).'">PREVIEW</td>
									'.$SITE->getLiveTableAdminControls($tableDecoy, $id, $dataUrl, ADMIN_DEL_UI_BG).'
								</tr>';
			
				}
				
				$data = $dataAdding.$fieldEditable;
				
				$rowAdder = '<tr>
								<td></td>						
								<td '.$data.$F['bgStyle'].'></td>
								<td '.$data.$F['contentStyle'].'></td>
								<td '.$data.$F['solidStyle'].'></td>
								<td '.$data.$F['cat'].'></td>
								<td '.$data.$F['label'].'></td>
								<td '.$data.$F['version'].'></td>
								<td></td>						
								'.$SITE->getLiveTableAddControls($tableDecoy, $dataUrl).'
							</tr>';
			
				$uibgs = $rowAdder.$uibgs.$rowAdder;
				
				$uibgs = '
							<div  class="base-b-mg">
								<button class="btn btn-danger" data-toggle="smartToggler">ADD NEW HOLES</button>
								<div class="red hide modal-drop">
									<form class="inline-form" method="post" action="/auto-pilot-page?_rdr='.$GLOBAL_rdr.'">								
										<p>ARE YOU SURE YOU WANT TO CREATE NEW UIBG INSERT HOLES?<br/> PLEASE CONFIRM</p>
										<label>number of holes: <input type="number" name="holes_to_add" value="0" min="0" class="field" /></label>
										<button type="submit" name="run_auto_uibg_update" class="btn btn-danger" >YES</button>															
										<button type="button" class="btn close-toggle" >CLOSE</button>
									</form>
								</div>
							</div>
							<div class="table-responsive">
								<table>
									<thead>
										<tr>
											<th>ID</th><th>BG STYLE</th><th>CONTENT STYLE</th>
											<th>SOLID STYLE</th><th>CATEG</th><th>LABEL</th><th>VERSION</th>
											<th>VIEW</th><th>CTRL</th>
										</tr>
									</thead>
									<tbody>'.$uibgs.'</tbody>
								</table>
							</div>';
								
			}elseif($tab == $emoticonMngrTab){
							
				######EMOTICONS########
				
				/////PDO QUERY/////
				$table = ' emoticons ';
				$tableDecoy = 'emot';
				$mediaRootEmoticons = $SITE->getMediaLinkRoot('emoticons');
				$F = array('unicode' => ' data-name="emot-unicode" ', 'ui' => ' data-name="emot-ui" ', 
							'cat' => ' data-name="emot-categ" ', 'label' => ' data-name="emot-label" '
						);
				
				
				$sql =  "SELECT COUNT(*) FROM ".$table;
				$totalRecords = $dbm->query($sql)->fetchColumn();
				
				/**********CREATE THE PAGINATION******/						
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>$pageKey,'jmpKey'=>'jump_page_emo','perPage'=>$pageCount,'hash'=>$urlHash));
				$pagination_emo = $paginationArr["pagination"];
				$total_page_emo = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$page_idemo = $paginationArr["pageId"];				
					
				$sql = "SELECT * FROM ".$table." LIMIT ".$startIndex.",".$perPage;
				$valArr = array();
				$stmt = $dbm->doSecuredQuery($sql, $valArr);	
			
				while($row = $dbm->fetchRow($stmt)){		
			
					$editParam = 'class="live-edit"  data-table="'.$tableDecoy.'" '.$dataUrl;			
					$id = $row["ID"];
					$unicodeSrc = $row["UNICODE_SRC"];
					$uiCode = $row["UI_CODE"];
					$label = $row["LABEL"];
					$categ = $row["CATEGORY"];
					
					$dataId = ' data-id="'.$id.'" ';	
			
					if((isset($_SESSION[$K=ADMIN_EDIT_EMOTICONS]) && $_SESSION[$K] == $id) || (isset($_SESSION[$K=ADMIN_EDIT_ALL_EMOTICONS]) && $_SESSION[$K]))
						$editParam .= $fieldEditable;	
					//'.$F['unicode'].$editParam.$dataId.'
					$emoticons .= '<tr>
									<td>'.$id.'</td>
									<td >'.$row["UNICODE_SRC"].'</td>
									<td '.$F['ui'].$editParam.$dataId.' >'.$row["UI_CODE"].'</td>
									<td '.$F['cat'].$editParam.$dataId.' >'.$row["CATEGORY"].'</td>
									<td '.$F['label'].$editParam.$dataId.' >'.$row["LABEL"].'</td>
									<td class="">'.$SITE->bbcHandler('', array('emoticonId' => $id, 'action' => 'getPerEmoticon', 'ver' => 'v1')).'</td>
									<td class="">'.$SITE->bbcHandler('', array('emoticonId' => $id, 'action' => 'getPerEmoticon', 'ver' => 'v2')).'</td>
									'.$SITE->getLiveTableAdminControls($tableDecoy, $id, $dataUrl, ADMIN_DEL_EMOTICONS).'
								</tr>';	
			
				}
				
				$data = $dataAdding.$fieldEditable;
				
				$rowAdder = '<tr>
								<td></td>						
								<td '.$data.$F['unicode'].'></td>
								<td '.$data.$F['ui'].'></td>
								<td '.$data.$F['cat'].'></td>
								<td '.$data.$F['label'].'></td>
								<td></td>						
								<td></td>						
								'.$SITE->getLiveTableAddControls($tableDecoy, $dataUrl).'
							</tr>';	
							
				$emoticons = $rowAdder.$emoticons.$rowAdder;
			
				$emoticons = '<div class="table-responsive">
								<table>
									<thead>
										<tr>
											<th>ID</th><th>UNICODE SRC</th><th>UI CODE</th><th>CATEG</th>
											<th>LABEL</th><th>V1</th><th>V2</th><th>CTRL</th>
										</tr>
									</thead>
									<tbody>'.$emoticons.'</tbody>
								</table>
							</div>';
								
			}elseif($tab == $countryMngrTab){
							
				######COUNTRIES########
				
				switch(strtolower($filterBy)){									 
							
					case 'country':		
						$filterCnd = " NAME_ISO_ALPHA_3 != '' ";
						break;												 
										
					default:
						$filterCnd = "";
						$filterBy = 'none';									 
							
				}									 
							
				$valArr = array();									 
							
				if($sq){									 
							
					$filterCnd = "((NAME LIKE ? OR NAME_ISO_ALPHA_2 LIKE ? OR NAME_ISO_ALPHA_3 LIKE ? OR CURRENCY_NAME LIKE ? OR CURRENCY_ISO_ALPHA LIKE ?) ".($filterCnd? "AND ".$filterCnd : "").")";
					$valArr[] = '%'.$sq.'%';									 
					$valArr[] = '%'.$sq.'%';									 
					$valArr[] = '%'.$sq.'%';									 
					$valArr[] = '%'.$sq.'%';									 
					$valArr[] = '%'.$sq.'%';									 
							
				}
				
				$filterCnd? ($filterCnd = ' WHERE '.$filterCnd) : '';
				
				
				/////PDO QUERY/////
				$table = ' countries ';	
				$tableDecoy = 'ctry';	
				$F = array('name' => ' data-name="name" ', 'nameIsoAlpha2' => ' data-name="name-iso-alpha-2" ', 
							'nameIsoAlpha3' => ' data-name="name-iso-alpha-3" ', 'nameIsoUn' => ' data-name="name-iso-un-code" ',
							'currencyName' => ' data-name="currency-name" ', 'currencyIsoAlpha' => ' data-name="currency-iso-alpha" ',
							'currencyIsoNum' => ' data-name="currency-iso-num" ', 'countryCode' => ' data-name="country-code" ',
							'intlDialPrefix' => ' data-name="intl-dial-prefix" ', 'natlDialPrefix' => ' data-name="natl-dial-prefix" ',
							'utcDst' => ' data-name="utc-dst" '
						);
				
				$sql =  "SELECT COUNT(*) FROM ".$table.$filterCnd;
				$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
				
				$altOrderCol = ", NAME ".$orderFlow;									 
							
				switch(strtolower($getSortBy)){									 
							
					case 'id':
						$orderUsed = "ID ".$orderFlow;
						break;										 
							
					case 'alpha2':
						$orderUsed = "NAME_ISO_ALPHA_2 ".$orderFlow;
						break;										 
								
					case 'alpha3':
						$orderUsed = "NAME_ISO_ALPHA_3 ".$orderFlow.$altOrderCol;
						break;									 
									
					default:
						$orderUsed = "NAME ".$orderFlow;
						$getSortBy = 'alphabet';									 
							
				}									 
							
				$orderUsed = ' ORDER BY '.$orderUsed;
				
				/**********CREATE THE PAGINATION******/										
				
				$qstrValArr = array($getSortBy, $filterBy, $orderFlowLnk);
				$qstrKeyValArr = array('sq' => $sq);
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>$pageKey,'jmpKey'=>'jump_page_ctry','qstrVal'=>$qstrValArr,'qstrKeyVal'=>$qstrKeyValArr,'perPage'=>$pageCount,'hash'=>$urlHash));
				$pagination_ctry = $paginationArr["pagination"];
				$total_page_ctry = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$page_idCtry = $paginationArr["pageId"];
				
				$sortNav = $SITE->buildSortLinks(array(
							'baseUrl' => $page_self_srt, 'pageId' => $page_idCtry, 'sq' => $sq, 'urlHash' => $urlHash, 
							'activeOrder' => $getSortBy, 'orderList' => array('id', 'alphabet', 'alpha2:Alpha 2', 'alpha3:Alpha 3'), 
							'activeOrderFlow' => $orderFlowLnk, 'orderFlowList' => array('asc', 'desc'), 
							'activeFilter' => $filterBy, 'filterList' => array('none', 'country')
							)).
							$SITE->getSearchForm(array('url' => '/'.$pageSelf, 'fieldName' => 'sq', 'pageResetUrl' => $pageUrl, 
							'fieldLabel' => '', 'fieldPH' => 'Search by country name', 'btnName' => 'search', 'btnLabel' => 'GO'));
				
					
				$sql = "SELECT * FROM ".$table.$filterCnd.$orderUsed." LIMIT ".$startIndex.",".$perPage;
				$stmt = $dbm->doSecuredQuery($sql, $valArr);
			
				while($row = $dbm->fetchRow($stmt)){
			
					$editParam = 'class="live-edit"  data-table="'.$tableDecoy.'" '.$dataUrl;			
					$id = $row["ID"];
					$dataId = ' data-id="'.$id.'" ';
			
					if((isset($_SESSION[$K=ADMIN_EDIT_COUNTRIES]) && $_SESSION[$K] == $id) || (isset($_SESSION[$K=ADMIN_EDIT_ALL_COUNTRIES]) && $_SESSION[$K]))
						$editParam .= $fieldEditable;
			
					$countries .= '<tr>
									<td '.$F['name'].$editParam.$dataId.' >'.$row["NAME"].'</td>
									<td '.$F['nameIsoAlpha2'].$editParam.$dataId.' >'.$row["NAME_ISO_ALPHA_2"].'</td>							
									<td '.$F['nameIsoAlpha3'].$editParam.$dataId.' >'.$row["NAME_ISO_ALPHA_3"].'</td>							
									<td '.$F['nameIsoUn'].$editParam.$dataId.' >'.$row["NAME_ISO_UN_CODE"].'</td>							
									<td '.$F['currencyName'].$editParam.$dataId.' >'.$row["CURRENCY_NAME"].'</td>
									<td '.$F['currencyIsoAlpha'].$editParam.$dataId.' >'.$row["CURRENCY_ISO_ALPHA"].'</td>
									<td '.$F['currencyIsoNum'].$editParam.$dataId.' >'.$row["CURRENCY_ISO_NUMERIC"].'</td>
									<td '.$F['countryCode'].$editParam.$dataId.' >'.$row["COUNTRY_CODE"].'</td>
									<td '.$F['intlDialPrefix'].$editParam.$dataId.' >'.$row["INTL_DIAL_PREFIX"].'</td>
									<td '.$F['natlDialPrefix'].$editParam.$dataId.' >'.$row["NATL_DIAL_PREFIX"].'</td>
									<td '.$F['utcDst'].$editParam.$dataId.' >'.$row["UTC_DST"].'</td>
									'.$SITE->getLiveTableAdminControls($tableDecoy, $id, $dataUrl, ADMIN_DEL_COUNTRIES).'
								</tr>';
			
				}
				
				$data = $dataAdding.$fieldEditable;
				
				$rowAdder = '<tr>						
								<td '.$data.$F['name'].'></td>
								<td '.$data.$F['nameIsoAlpha2'].'></td>
								<td '.$data.$F['nameIsoAlpha3'].'></td>
								<td '.$data.$F['nameIsoUn'].'></td>
								<td '.$data.$F['currencyName'].'></td>
								<td '.$data.$F['currencyIsoAlpha'].'></td>
								<td '.$data.$F['currencyIsoNum'].'></td>					
								<td '.$data.$F['countryCode'].'></td>
								<td '.$data.$F['intlDialPrefix'].'></td>
								<td '.$data.$F['natlDialPrefix'].'></td>
								<td '.$data.$F['utcDst'].'></td>
								'.$SITE->getLiveTableAddControls($tableDecoy, $dataUrl).'
							</tr>';
							
				$countries = $rowAdder.$countries.$rowAdder;
				
				$countries = '<div class="table-responsive">
								<table>
									<thead>
										<tr>
											<th>NAME</th><th>ISO ALPHA 2</th><th>ISO ALPHA 3</th>
											<th>ISO UN CODE</th><th>CURRENCY NAME</th><th>CURRENCY ISO ALPHA</th>
											<th>CURRENCY ISO NUM</th><th>COUNTRY CODE</th><th>INTL DIAL PREFIX</th>
											<th>NATL DIAL PREFIX</th><th>UTC/DST</th><th>CTRL</th>
										</tr>
									</thead>
									<tbody>'.$countries.'</tbody>
								</table>
							</div>';
								
			}	
			
			$recordCount = '<h2 class="cpop">'.((isset($totalRecords) && $totalRecords > 1)?  $totalRecords.' '.$headTitlePlural : $totalRecords.' '.$headTitle).' found</h2>';
			
		}else
			$notLogged = $GLOBAL_notLogged;
				
		
		$tabLink = $SITE->buildLinearNav(array(
			"baseUrl" => $baseUrl,
			"urlList" => $urlList,							
			"active" => $tab																				
		
		));
		
		
		$SITE->buildPageHtml(array("pageTitle"=>'Db Manager - '.$headTitlePlural,
						"preBodyMetas"=>$SITE->getNavBreadcrumbs($subNav),
						"pageBody"=>'
						<div class="single-base full-w blend">
							<div class="base-ctrl">'.				
								(isset($notLogged)? $notLogged : '').(isset($noViewPrivilege)? $noViewPrivilege : '').

								(($sessUsername && $GLOBAL_isAdmin)? '

									<div class="">'.				
										$tabLink.
													
										(($tab == $sectionMngrTab)? '
																																			
											<div class="panel panel-orange">
												<h1 class="panel-head page-title">TABLE OF SECTIONS</h1>
												<div class="panel-body">
													<button class="btn btn-info" data-toggle="smartToggler" >Read Me!</button>
													<div class="text-info panel">
														<h3 class="panel-head prime">IMPORTANT NOTES</h3>
														<div class="panel-body">
															<ol class="ol">
																<li>
																	The red headed columns contain values that are vital to advertisement. 
																	Only edit these fields if you`re sure of what you`re doing.
																</li>
																<li>
																	<span class="red">All rates listed in the table are on '.$campaignMetas["adRateBaseFreq"].' basis</span>
																</li>
																<li>
																	Discount rates are also vital to advertisement;<br/> 
																	obviously we would`nt want to give 100% discount; would we?<br/>
																	By default if this field is set to a value >= 100%, the system will ignore the value and 
																	automatically use '.$campaignMetas["fallBackDiscount"].'% when the need arises.<br/>
																	Therefore this value should be manually set to a reasonable range between 1-99.9%
																</li>
																<li>
																	The estimated ad rate listed in the table by default applies only to banner '.$banner1Details[$K="dimension"].' <br/>
																	Banner '.($banner2Dimension = $banner2Details[$K]).', '.($banner3Dimension = $banner3Details[$K]).' and text ad rates are expressed as a fixed percent of this estimated rate.<br/>
																	Banner '.$banner2Dimension.' rate = (estimated section ad rate + '.$campaignMetas["banner2Charge"].'% of estimated section ad rate)<br/>
																	banner '.$banner3Dimension.' rate = (estimated section ad rate + '.$campaignMetas["banner3Charge"].'% of estimated section ad rate)<br/>
																	Text ad rate = (estimated section ad rate - '.$campaignMetas["textAdCharge"].'% of section ad rate). <br/>
																	
																	The percentage values used can be modified from the auto pilot page at any time.
																</li>
																<li>
																	Text ad discount rate = (min discount rate + ('.$campaignMetas["textAdDiscountCharge"].'% of (100 - min discount rate))<br/> 
																	The percentage value can be modified from the auto pilot page at any time.
																</li>
																<li>
																	Premium advertisers` eligibility amount 
																	=> <b class="green">'.$campaignMetas["currencySymbol"].$ENGINE->format_number($campaignMetas["premiumEligibilityAmount"], 0, false).'</b><br/>
																	The value can be modified from the auto pilot page at any time.
																</li>
																<li>
																	FPV column in the table below => Front Page Visibility of Section Name
																</li>
															</ol>
														</div>
													</div><span id="'.$urlHash.'"></span>'.
													(isset($recordCount)? $recordCount : '').
													((isset($page_ids) && $page_ids)? '<h3 class="">page (<span class="cyan">'.$page_ids.'</span> of '.$totalPageSrch.')</h3>' : '').
													$sortNav.$pagination_scat.$sections.$pagination_scat.'																
												</div>
											</div>'
									
										:(($tab == $categMngrTab)? '
																									
											<div class="panel panel-bronze">
												<h1 id="'.$urlHash.'" class="panel-head page-title">TABLE OF CATEGORIES</h1>
												<div class="panel-body">'.
													(isset($recordCount)? $recordCount : '').
													((isset($page_idc) && $page_idc)? '<h3 class="">page (<span class="cyan">'.$page_idc.'</span> of '.$total_page_c.')</h3>' : '').
													$pagination_cat.$categories.$pagination_cat.'																	
												</div>
											</div>'
											
											
										:(($tab == $popMngrTab)?	'
																
											<div class="panel panel-olive">
												<h1 id="'.$urlHash.'" class="panel-head page-title">TABLE OF POPS</h1>
												<div class="panel-body">
													<button class="btn btn-warning" data-toggle="smartToggler" data-id-targets="tips">View Tips</button>
													<div class="prime modal-drop hide" id="tips">
														<h3>Tips</h3>
														<ol class="ol">
															<li>'.$SITE->getDynamicContentTips().'</li>
															<li>															
																<h3>pop target values</h3>
																<div><span '.($K='data-has-clipboard-copy="true" data-clipboard-copy-btn-text="Copy Target"').'>'.POP_FOR_ALL.'</span>: for all </div>
																<div><span '.$K.'>'.POP_FOR_MODERATORS.'</span>: for moderators </div>
																<div><span '.$K.'>'.POP_FOR_BIRTHDAYS.'</span>: for birthday celebrants </div>
																<div><span '.$K.'>'.POP_FOR_SEASONS.'</span>: for season greetings </div>
																<div><span '.$K.'>'.POP_FOR_VALENTINES.'</span>: for valentine greetings </div>
															</li>
														</ol>
													</div>'.
													(isset($recordCount)? $recordCount : '').
													((isset($page_idp) && $page_idp)? '<h3 class="">page (<span class="cyan">'.$page_idp.'</span> of '.$total_page_p.')</h3>' : '').
													$pagination_pops.$pops.$pagination_pops.'																		
												</div>
											</div>'
																												
										:(($tab == $badgeMngrTab)? '	
																
											<div class="panel panel-gray">
												<h1 id="'.$urlHash.'" class="panel-head page-title">TABLE OF BADGE</h1>
												<div class="panel-body">
													<button class="btn btn-warning" data-toggle="smartToggler" >View Tips</button>
													<div class="prime modal-drop hide">
														<h3>Tips</h3>
														<ol class="ol">
															<li>CATEG: 1 => Badge, 2 => Tag</li>
															<li>CLASS: 1 => Bronze, 2 => Silver, 3 => Gold</li>
															<li>FREQ: S => Single(awarded once), M => Multiple(awarded multiple times)</li>
														</ol>
													</div>'.
													(isset($recordCount)? $recordCount : '').
													((isset($page_idb) && $page_idb)? '<h3 class="">page (<span class="cyan">'.$page_idb.'</span> of '.$total_page_b.')</h3>' : '').
													$sortNav.$pagination_badge.$badges.$pagination_badge.'																				
												</div>
											</div>'
																					
										:(($tab == $autoPilotMngrTab)?	'
																
											<div class="panel panel-redx">
												<h1 id="'.$urlHash.'" class="panel-head page-title">TABLE OF PILOTS</h1>
												<div class="panel-body">'.
													(isset($recordCount)? $recordCount : '').
													((isset($page_idpi) && $page_idpi)? '<h3 class="">page (<span class="cyan">'.$page_idpi.'</span> of '.$total_page_pi.')</h3>' : '').
													$pagination_pilot.$autoPilots.$pagination_pilot.'																																			
												</div>
											</div>'
																					
										:(($tab == $daemonMngrTab)?	'
																
											<div class="panel panel-redx">
												<h1 id="'.$urlHash.'" class="panel-head page-title">TABLE OF DAEMONS</h1>
												<div class="panel-body">'.
													(isset($recordCount)? $recordCount : '').
													((isset($page_idDae) && $page_idDae)? '<h3 class="">page (<span class="cyan">'.$page_idDae.'</span> of '.$total_page_dae.')</h3>' : '').
													$pagination_daemons.$daemons.$pagination_daemons.'																																			
												</div>
											</div>'
											
											:(($tab == $uibgMngrTab)?	'
																	
												<div class="panel panel-bluex">
													<h1 id="'.$urlHash.'" class="panel-head page-title">TABLE OF UI BGs</h1>
													<div class="panel-body">'.
														(isset($recordCount)? $recordCount : '').
														((isset($page_iduibg) && $page_iduibg)? '<h3 class="">page (<span class="cyan">'.$page_iduibg.'</span> of '.$total_page_uibg.')</h3>' : '').
														$sortNav.$pagination_uibg.$uibgs.$pagination_uibg.'																																			
													</div>
												</div>'
												
											:(($tab == $emoticonMngrTab)?	'
																
												<div class="panel panel-dcyan">
													<h1 id="'.$urlHash.'" class="panel-head page-title">TABLE OF EMOTICONS</h1>
													<div class="panel-body">'.
														(isset($recordCount)? $recordCount : '').
														((isset($page_idemo) && $page_idemo)? '<h3 class="">page (<span class="cyan">'.$page_idemo.'</span> of '.$total_page_emo.')</h3>' : '').
														$pagination_emo.$emoticons.$pagination_emo.'																																			
													</div>
												</div>'
											
											:(($tab == $countryMngrTab)?	'
																
												<div class="panel panel-dcyan">
													<h1 id="'.$urlHash.'" class="panel-head page-title">TABLE OF COUNTRIES</h1>
													<div class="panel-body">'.
														(isset($recordCount)? $recordCount : '').
														((isset($page_idCtry) && $page_idCtry)? '<h3 class="">page (<span class="cyan">'.$page_idCtry.'</span> of '.$total_page_ctry.')</h3>' : '').
														$sortNav.$pagination_ctry.$countries.$pagination_ctry.'																																			
													</div>
												</div>'
											
												
												: ''
													
											))))))))).													
										$tabLink.' 																																			
									</div>'
								: '').'
							</div>
						</div>'
		));
		break;
		
	}
	
	
	
	
	
	
	
	
	
	
	/**CAMPAIGN MANAGER**/
	case 'ad-campaign-manager':{
		
		///////AUTHORIZE TOP LEVEL ACCESS///////////
		$ACCOUNT->authorizeTopAccess(false, $GLOBAL_isTrusted);

		$notLogged=$amount=$otherAmount=$fmtdAmount=$action=$sort=$approvalAction=$getCampaigns=$searchRes=
		$emails=$tab=$table=$alternateTD=$alternateTH=$tabLink=$subNav=$bannerTypeQry="";
		
		/***************************BEGIN URL CONTROLLER****************************/

		$page_self_srt = $ENGINE->get_page_path('page_url', 2);
		$pageSelfSortSrch = $ENGINE->get_page_path('page_url', 3);

		$sortList = array(
			($latest = 'latest'), ($clicks = 'clicks'), ($alphabet = 'alphabet'), 
			($pending = 'pending'), ($approved = 'approved'), ($disapproved = 'disapproved')
		);
		
		$defaultOrder = $latest;

		if(isset($pagePathArr[1]) && in_array(strtolower($pagePathArr[1]), array("text-campaign", "banner-campaign"))){
			
			if(isset($pagePathArr[2]) && in_array(strtolower($pagePathArr[2]), array("search-by-ad", "search-by-user"))){
				
				$pathKeysArr = array('pageUrl', 'tab', 'searchTab', 'sq', 'sort_s', 'page_id_s');		
				$maxPath = 6;
				$isSearch = true;
		
			}else{
			
				$pathKeysArr = array('pageUrl', 'tab', 'sort', 'pageId');
				$maxPath = 4;
			
			}
				
		}else{
		
			$pathKeysArr = array();
			$maxPath = 0;
		
		}

		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/



		if(isset($_GET[$K="tab"]))
			$tab = strtolower($ENGINE->sanitize_user_input($_GET[$K]));
		
		$adsCampaign = new AdsCampaign();

		if($tab == "banner-campaign"){
		
			$campaignType = $adsCampaign->getBannerCampaignType();										
			$subNav = '<li><a href="/'.$pageSelf.'" title="">Banner Campaign Manager</a></li>';				
			$isBannerTab = true;
		
		}elseif($tab == "text-campaign"){
		
			$campaignType = $adsCampaign->getTextCampaignType();			
			$subNav = '<li><a href="/'.$pageSelf.'" title="">Text Campaign Manager</a></li>';
			$isTextTab = true;
		
		}
		
		$adsCampaign->setCampaignType($campaignType);
		$campaignTable = $adsCampaign->getCampaignTable();
		
		$pageUrl = 'ad-campaign-manager/'.$campaignType.'-campaign';	
		$searchTab = isset($_GET[$K="searchTab"])? strtolower($_GET[$K]) : '';
		$tabLink = '<li class="'.(isset($isBannerTab)? 'active' : '').'"><a class="links" href="/ad-campaign-manager/banner-campaign">Banner Campaign Manager</a></li><li class="'.(isset($isTextTab)? 'active' : '').'"><a class="links" href="/ad-campaign-manager/text-campaign">Text Campaign Manager</a></li>';
		$asterix = '<span class="asterix">*</span>';

		if($sessUsername){
				
			//////////GET FORM-GATE RESPONSE///////
			list($alertUser, $approvalSuccess) = $SITE->formGateRefreshResponse(true);															
			
			if(!$GLOBAL_isStaff)
				$noViewPrivilege = '<span class="alert alert-danger"> '.$GLOBAL_sessionUrl_unOnly.' Sorry You do not have enough Privilege to view this Page</span>';
				
			
			////IF IS SET APPROVE CAMPAIGN///////
				
			if(isset($_POST["approve_campaign"])){
				
				$adIdEnc = $_POST["ad_id"];
				$adId = $ENGINE->sanitize_number($INT_HASHER->decode($adIdEnc));
				$approvePendingOnly = isset($_POST["approve_all"]);
				$approvalAction = strtolower($_POST["approval_action"]);
				
				switch($approvalAction){
		
					case 'approve':
					case 'approve all': $approvalState = $adsCampaign->getApprovedAdState(); $approvalStateTxt = 'Approved'; break;
		
					case 'disapprove':
					case 'disapprove all': $approvalState = $adsCampaign->getDisapprovedAdState(); $approvalStateTxt = 'Disapproved'; break;
		
				}
				
				$approvalStateTxtUC = strtoupper($approvalStateTxt);
				$approvalStateTxtLC = strtolower($approvalStateTxt);
				
				if(in_array($approvalAction, array('approve all', 'disapprove all')) || $approvePendingOnly){
					
					$pendingApprovalTxt = $approvePendingOnly? ' with PENDING status ' : '';
					$approvalCnd = ' WHERE APPROVAL_STATUS '.($approvePendingOnly? ' = '.$adsCampaign->getPendingAdState() : ' !='.$approvalState.' ');
					
					$maxPerDbRow = $dbm->getMaxRowPerSelect();
					
					for($i = 0 ; ; $i += $maxPerDbRow){
						
						///////////PDO QUERY///
					
						$sql = "SELECT ID, USER_ID FROM ".$campaignTable.$approvalCnd." LIMIT ".$i.",".$maxPerDbRow;
						$stmt = $dbm->query($sql, true);
						
						
						if($dbm->getRecordCount() && $dbm->getSelectCount()){															
									
							/////PDO QUERY/////
						
							$sql =  "UPDATE ".$campaignTable." SET APPROVAL_STATUS = ? ".$approvalCnd;
							$valArr = array($approvalState);
							
							if($dbm->doSecuredQuery($sql, $valArr)){	
							
								while($row = $dbm->fetchRow($stmt)){
									
									$memberUsername = $ACCOUNT->memberIdToggle($row["USER_ID"]);
									$adIdTmp = $row["ID"];
		
									if($approvalStateTxtLC == "approved")
										$approvalTxtHtml = '<b '.EMS_PH_PRE.'GREEN>'.$approvalStateTxtUC.'</b>';
		
									else{
		
										$approvalTxtHtml = '<b '.EMS_PH_PRE.'RED>'.$approvalStateTxtUC.'</b>';
		
										/////////REMOVE THE AD FROM ACTIVE ADS UNTIL ITS APPROVED AGAIN////////
										$adsCampaign->removeFromActiveAdSlots(array("adId"=>$adIdTmp));
		
									}
										
									$email = $ACCOUNT->getUserEmail($memberUsername, "campaign_ntf");
		
									if($email){						
									
										///SEND NOTIFICATION EMAIL IF ALLOWED/////
										$adsCampaign->notifyApprovalByEmail($memberUsername, $approvalTxtHtml, $adIdTmp);							
										
									}
										
								}
						
								$approvalSuccess = '<span class="alert alert-success">All '.$campaignType.' campaigns '.$pendingApprovalTxt.' has been successfully <b class="yellow">'.$approvalStateTxtUC.'</b></span>';
								
							}
						
						}else{
		
							$approvalSuccess = $approvalSuccess? $approvalSuccess : '<span class="alert alert-danger">There are no '.$campaignType.' campaigns matching your request '.$pendingApprovalTxt.' to be <b class="yellow">'.$approvalStateTxtUC.'</b></span>';
							 
							////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
							$SITE->formGateRefresh(array("", $approvalSuccess), '', $adIdEnc);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							break;

						}
					}	
					
				}else{
		
					/////APPROVE PER AD////////////
					
					if($adIdEnc){
							
						///////////PDO QUERY/////
					
						$sql = "SELECT ID, USER_ID FROM ".$campaignTable."  WHERE ID = ? LIMIT 1";
						$valArr = array($adId);
						$stmt = $dbm->doSecuredQuery($sql, $valArr, true);
					
						if($dbm->getRecordCount()){
								
							///////////PDO QUERY/////
					
							$sql = "SELECT ID FROM ".$campaignTable." WHERE ID = ? AND APPROVAL_STATUS = ? LIMIT 1";
							$valArr = array($adId, $approvalState);
						
							if(!$dbm->doSecuredQuery($sql, $valArr)->fetchColumn()){
									
								///////////PDO QUERY///////
						
								$sql = "UPDATE ".$campaignTable." SET APPROVAL_STATUS = ? WHERE ID =? LIMIT 1";
								$valArr = array($approvalState, $adId);
		
								if($dbm->doSecuredQuery($sql, $valArr)){
																				
									while($row = $dbm->fetchRow($stmt)){
										
										$memberUsername = $ACCOUNT->memberIdToggle($row["USER_ID"]);
										$adId = $row["ID"];
		
										if($approvalStateTxtLC == "approved")
											$approvalTxtHtml = '<b '.EMS_PH_PRE.'GREEN>'.$approvalStateTxtUC.'</b>';
		
										else{
		
											$approvalTxtHtml = '<b '.EMS_PH_PRE.'RED>'.$approvalStateTxtUC.'</b>';
		
											/////////REMOVE THE AD FROM ACTIVE ADS UNTIL ITS APPROVED AGAIN////////
											$adsCampaign->removeFromActiveAdSlots(array("adId"=>$adId));
		
										}
										
										$email = $ACCOUNT->getUserEmail($memberUsername,"campaign_ntf");
		
										if($email){						
																			
											////SEND NOTIFICATION EMAIL IF ALLOWED/////	
											$adsCampaign->notifyApprovalByEmail($memberUsername, $approvalTxtHtml, $adId);
											
										}
											
									}
											
									$approvalSuccess = '<span class="alert alert-success">The '.$campaignType.' campaign with ID: <span class="blue">'.$adIdEnc.'</span> has been successfully <b class="yellow">'.$approvalStateTxtUC.'</b></span>';
									
									////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
									$SITE->formGateRefresh(array("", $approvalSuccess), '', $adIdEnc);

								}
					
							}else
								$approvalSuccess = '<span class="alert alert-danger">The '.$campaignType.' campaign with ID: <span class="blue">'.$adIdEnc.'</span> has already been <b class="yellow">'.$approvalStateTxtUC.'</b></span>';
																							
						}else
							$approvalSuccess = '<span class="alert alert-danger">Sorry the ID you entered is not a valid '.$campaignType.' campaign ID; please verify it and try again</span>';
					
					}else
						$approvalIdRqd = $asterix;									  
				}

			}


			
			
			/////IF MANAGE CAMPAIGN CREDITS IS SET///////
				
			if(isset($_POST["manage_campaign_crd"])){
							
				$username = $ENGINE->sanitize_user_input($_POST["username"]);
				$amount = $ENGINE->sanitize_user_input($_POST["amount"]);
				
				$otherAmount = $ENGINE->sanitize_number($_POST["other_amount"], '.');
				
				$action = $_POST["action"];				
								
				$amount = !$otherAmount? $amount : $otherAmount;
				
				list($alertUser, $fmtdAmount, $action, $rqdUname, $rqdCDAmt) = $adsCampaign->creditOrDebitAdvertiser($username, $amount, $action);
				
			}
				
			$adRptTable = 'ad_traffic_reports';
				
			////GENERATE ALL CAMPAIGNS///		
			$orderBy="";
			
			if(isset($_GET[$K="sort"]))
				$sort = $ENGINE->sanitize_user_input($_GET[$K], array('lowercase' => true));
			
			if(!$sort)
				$sort = $defaultOrder;
			
			//////SWITCH SORT CONDITION//////
			switch($sort){
			
				case $pending:
					$cond = $adsCampaign->getPendingAdState();
					break;
			
				case $approved:
					$cond = $adsCampaign->getApprovedAdState();
					break;
			
				case $disapproved:
					$cond = $adsCampaign->getDisapprovedAdState();
					break;
				
				default:
					$cond = '';
						
			}
						
			$cond? ($cond = ' WHERE APPROVAL_STATUS= '.$cond) : '';
			$hitsClicksSubQry = $adsCampaign->getAdHitsNClicksSubQry($campaignTable.'.ID', $campaignType);
			
			if(in_array($sort, array($latest, $clicks))){
				
				$QRY = "SELECT *".$hitsClicksSubQry." FROM ".$campaignTable." ORDER BY ".(($sort == "clicks")? "CLICKS" : "TIME")." DESC ";		
				$COUNTQRY = "SELECT COUNT(*) FROM ".$campaignTable;
				
			}elseif($sort == $alphabet){
				
				$QRY = "SELECT ".$campaignTable.".*, users.USERNAME".$hitsClicksSubQry." FROM ".$campaignTable." 
				JOIN users ON ".$campaignTable.".USER_ID = users.ID  ORDER BY USERNAME ";
				$COUNTQRY = "SELECT COUNT(*) FROM ".$campaignTable." JOIN users ON ".$campaignTable.".USER_ID = users.ID";
				
			}elseif(in_array($sort, array($pending, $approved, $disapproved))){
				
				$QRY = "SELECT *".$hitsClicksSubQry." FROM ".$campaignTable.$cond." ORDER BY ".$campaignTable.".TIME DESC ";
				$COUNTQRY = "SELECT COUNT(*) FROM ".$campaignTable.$cond;	
				
			}
			
			///////////PDO QUERY////
						
			$sql = $COUNTQRY;
			$totalRecords = $dbm->query($sql)->fetchColumn();
					
			/**********CREATE THE PAGINATION*******/				
			$qstrValArr = array($sort);
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'perPage'=>30,'qstrVal'=>$qstrValArr,'hash'=>'tab'));						
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageId = $paginationArr["pageId"];

			///////END OF PAGINATION////////////
				
			///////////PDO QUERY/////
				
			$sql = $QRY." LIMIT ".$startIndex.",".$perPage;		
								
			if($totalRecords){
						
				$apprSubmitRdr = $pageUrl.'/'.$sort.(($pageId > 1)? '/'.$pageId : "");		
				$getCampaigns = $adsCampaign->getPerCampaign($sql, array(), $apprSubmitRdr, $totalRecords);							
									
			}else
				$campErr = '<span class="alert alert-danger">Sorry there are no ads with status: '.$sort.'</span>';
			

					
					
			
			////SEARCH A CAMPAIGN///////
			if(isset($_POST["search"]) || ($searchTab && isset($_GET["sort_s"]))){
				
				$searchParamId=$adId="";
				
				if(isset($_POST[$K="sad"])){
					
					//IF SEARCHING BY AD ID
					$qryValOrg = $searchParamIdEnc = $_POST[$K];	
					$searchParamId = $ENGINE->sanitize_number($INT_HASHER->decode($searchParamIdEnc));	
					$searchByAdId = true;
					
				}elseif(isset($_POST[$K="sud"])){
					
					//IF ADS SEARCHING BY USERNAME
					$adOwnerUsername = $ENGINE->sanitize_user_input($_POST[$K]);													
					$searchParamId = $ACCOUNT->memberIdToggle($adOwnerUsername, true);
					$qryValOrg = $adOwnerUsername;	
					$searchByAdId = false;
					
				}
				
				if(isset($_GET[$K="sq"])){
					
					$sq = $ENGINE->sanitize_user_input($_GET[$K]);												
					$qryValOrg = $sq;
					$searchByAdId = ($searchTab == 'search-by-ad');
					$searchParamId = $searchByAdId? $ENGINE->sanitize_number($INT_HASHER->decode($sq)) : $ACCOUNT->memberIdToggle($sq, true);
				
				}
				
				$qryVal = $searchParamId;
				
				if($qryVal || $qryValOrg){
					
					///GENERATE TARGET CAMPAIGNS///		
					$orderBy="";


					if(isset($_GET[$K="sort_s"]))
						$sort = $ENGINE->sanitize_user_input($_GET[$K], array('lowercase' => true));
					
					if(!$sort)
						$sort = $defaultOrder;
					
					$searchTypeCond = " WHERE ".($searchByAdId? $campaignTable.".ID = ? " : $campaignTable.".USER_ID = ? ");
					
					$apprColCnd = ' AND APPROVAL_STATUS=';
					
					//////SWITCH SORT CONDITION//////
					switch($sort){


						case $pending:
							$searchTypeCond .= $apprColCnd.$adsCampaign->getPendingAdState();
							break;


						case $approved:
							$searchTypeCond .= $apprColCnd.$adsCampaign->getApprovedAdState();
							break;


						case $disapproved:
							$searchTypeCond .= $apprColCnd.$adsCampaign->getDisapprovedAdState();
							break;
						
						default:
							$searchTypeCond .= '';
								
					}
					
					$hitsClicksSubQry = $adsCampaign->getAdHitsNClicksSubQry($campaignTable.'.ID', $campaignType);
					
					if(in_array($sort, array($latest, $clicks))){
						
						$QRY = "SELECT *".$hitsClicksSubQry." FROM  ".$campaignTable.$searchTypeCond." ORDER BY ".(($sort == "clicks")? "CLICKS" : "TIME")." DESC ";		
						$COUNTQRY = "SELECT COUNT(*) AS TOTAL_RECS FROM  ".$campaignTable.$searchTypeCond;
						
					}elseif($sort == $alphabet){
						
						$QRY = "SELECT ".$campaignTable.".*, users.USERNAME".$hitsClicksSubQry." FROM ".$campaignTable." 
						JOIN users ON ".$campaignTable.".USER_ID = users.ID ".$searchTypeCond." ORDER BY USERNAME ";
						$COUNTQRY = "SELECT COUNT(*) AS TOTAL_RECS FROM ".$campaignTable." JOIN users ON ".$campaignTable.".USER_ID = users.ID ".$searchTypeCond;
						
					}else{
						
						$QRY = "SELECT *".$hitsClicksSubQry." FROM ".$campaignTable.$searchTypeCond." ORDER BY TIME DESC ";
						$COUNTQRY = "SELECT COUNT(*) AS TOTAL_RECS FROM ".$campaignTable.$searchTypeCond;
						
					}
					
					///////////PDO QUERY////
								
					$sql = $COUNTQRY;
					$valArr = array($qryVal);
					$totalRecordsSrch = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
										
					/**********CREATE THE PAGINATION****************/
					$pageUrl = $pageUrl.'/'.$searchTab;
					$qstrValArr = array($qryValOrg, $sort);
					
					$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecordsSrch,'url'=>$pageUrl,'pageKey'=>'page_id_s',
					'jmpKey'=>'jump_page_s','qstrVal'=>$qstrValArr,'perPage'=>30,'hash'=>'tab_s'));					
					$paginationSrch = $paginationArr["pagination"];
					$totalPageSrch = $paginationArr["totalPage"];
					$perPageSrch = $paginationArr["perPage"];
					$startRecSrch = $paginationArr["startIndex"];
					$pageIdSrch = $paginationArr["pageId"];

					///////END OF PAGINATION////////////
						
					///////////PDO QUERY/////
						
					$sql = $QRY." LIMIT ".$startRecSrch.",".$perPageSrch;	
					
					$pageSelfSortSrch = $pageSelfSortSrch.'/'.$qryValOrg;
					
					if($totalRecordsSrch){
						
						$apprSubmitRdr = $pageUrl.'/'.$qryValOrg.'/'.$sort.(($pageIdSrch > 1)? '/'.$pageIdSrch : '');							
						$foundSQ = ' for "'.$qryValOrg.'"';
						$searchRes = '<h1><span class="cyan">'.$totalRecordsSrch.'</span> result'.(($totalRecordsSrch == 1)? '' : 's').' found'.$foundSQ.'</h1>'.
						$adsCampaign->getPerCampaign($sql, array($qryVal), $apprSubmitRdr, $totalRecordsSrch);						
											
					}else
						$searchResErr = '<span class="alert alert-danger">Sorry no result was found</span>';
								
				}else						
					$searchResErr = '<span class="alert alert-danger">Please enter an ID or USERNAME in the corresponding fields and try again</span>';
						
			}
			
			
		}else
			$notLogged = $GLOBAL_notLogged;			

		$getSortSrch =	isset($_GET[$K="sort_s"])?	$ENGINE->sanitize_user_input($_GET[$K], array('lowercase' => true)) : '';			
		$get_sort =	isset($_GET[$K="sort"])?	$ENGINE->sanitize_user_input($_GET[$K], array('lowercase' => true)) : '';														
		
		$SITE->buildPageHtml(array("pageTitle"=>'Campaign Manager - '.$campaignType,
						"preBodyMetas"=>$SITE->getNavBreadcrumbs($subNav),
						"pageBody"=>'
						<div class="single-basex">
							<div class="base-ctrl">'.
								$notLogged.(isset($noViewPrivilege)? $noViewPrivilege : '').
								
								(($sessUsername && $GLOBAL_isTopStaff)? '

									<div class="base-pad">
										<nav class="nav-base "><ul class="nav nav-tabs justified justified-bom">'.$tabLink.'</ul></nav>							
											
										<div class="panel panel-brown">
											<h1 id="tab_s" class="panel-head page-title">SEARCH A CAMPAIGN</h1>
											<div class="panel-body">'.
												(isset($searchResErr)? $searchResErr : '').'					
												<div class="inline-form-group">
													'.$SITE->getSearchForm(array('url' => '/'.$ENGINE->get_page_path('page_url', 2).'/search-by-ad#tab_s', 'fieldName' => 'sad', 
													'fieldLabel' => 'SEARCH BY AD ID:', 'fieldPH' => 'Enter the Ad Id', 'btnName' => 'search', 'btnLabel' => 'GO'))
													
													.$SITE->getSearchForm(array('url' => '/'.$ENGINE->get_page_path('page_url', 2).'/search-by-user#tab_s', 'fieldName' => 'sud', 
													'fieldLabel' => 'SEARCH BY USERNAME:', 'fieldPH' => 'Enter the Username of the Ad Owner', 'btnName' => 'search', 'btnLabel' => 'GO')).'
												</div>
												'.((isset($totalPageSrch) && !isset($searchResErr))?   '<h1>(page <span class="cyan">'.$pageIdSrch.'</span> of '.$totalPageSrch.')</h1>' : '').
													(isset($paginationSrch)? $paginationSrch : '').															
													(isset($isSearch)?
														$SITE->buildSortLinks(array(
															'baseUrl' => $pageSelfSortSrch, 'pageId' => $pageId, 'sq' => '', 'urlHash' => 'tab_s', 
															'activeOrder' => $getSortSrch, 'orderList' => $sortList,
															'defaultOrder' => $defaultOrder, 'orderGlobLabel' => 'SORT BY'
														)) : ''
													).													
													(isset($searchRes)? $searchRes : '').(isset($paginationSrch)? $paginationSrch : '').'																													
											</div>
										</div>
										
										
										<div class="row cols-pad">					
											<div class="col-lg-w-6-pull">					
												<div class="panel panel-bluex">					
													<h1 id="crd" class="panel-head page-title">MANAGE CREDITS</h1>
													<div class="panel-body">'.
														((isset($rqdUname) && $rqdUname)? '<span class="alert alert-danger" > Please Enter the target Username !</span>' : '').
														(isset($alertUser)? $alertUser : '').'						
														<form class="inline-form inline-form-default block-label" action="/'.$pageSelf.'#crd" method="post">
																<fieldset>
																	<div class="field-ctrl">
																		<label>USERNAME'.((isset($rqdUname) && $rqdUname)? $rqdUname : '').':</label>
																		<input  class="field" type="text"  placeholder="Type here the target username " name="username" value="'.(isset($username)? $username : '').'" />
																	</div>
																	<div class="field-ctrl">
																		<label>CREDIT AMOUNT'.((isset($rqdCDAmt) && $rqdCDAmt)? $rqdCDAmt : '').':</label>							
																		<select name="amount" class="field" >'.
																			$adsCampaign->getCreditAmountOptions($fmtdAmount).'
																		</select>
																	</div>
																	<div class="field-ctrl">
																		<label>OTHER CREDIT DENOMINATIONS'.((isset($rqdCDAmt) && $rqdCDAmt)? $rqdCDAmt : '').':</label>
																		<input class="field" type="text" name="other_amount" value="'.(isset($otherAmount)? $otherAmount : '').'"  />
																	</div>
																	<div class="field-ctrl">						
																		<label>ACTION: </label>							
																		<select name="action" class="field"  >
																			<option '.(($action == "credit")? 'selected' : '').'>Credit User</option>
																			<option '.(($action == "debit")? 'selected' : '').'>Debit User</option>
																		</select>
																	</div>
																	<div class="btn-group-ctrl">
																		<button type="button" class="form-btn btn-warning" data-toggle="smartToggler" >PROCESS</button>
																		<div class="hide modal-drop has-close-btn red">
																			<h4>Please verify that the details you`ve entered is correct<br/>Are You Sure?</h4>
																			<button  class="btn btn-success" type="submit" name="manage_campaign_crd">PROCESS</button>
																			<button type="button" class="btn close-toggle">CLOSE</button>
																		</div>
																	</div>
															</fieldset>
														</form>			
													</div>
												</div>
											</div>
																
											<div class="col-lg-w-4-pull">
												<div class="panel panel-gold">
													<h1 id="appr" class="panel-head page-title">MANAGE APPROVALS</h1>
													<div class="panel-body">'.
														(isset($approvalIdRqd)? '<span class="alert alert-danger" >Please enter the Ad ID </span>' : '').
														(isset($approvalSuccess)? $approvalSuccess : '').'															
														<form class="inline-form" action="/'.$pageSelf.'#appr" method="post">
															<fieldset>
																<div class="field-ctrl">
																	<label class="t-disable">AD ID'.(isset($approvalIdRqd)? $approvalIdRqd : '').':</label>
																	<input type="text" class="t-disable field" name="ad_id" value="'.(isset($adIdEnc)? $adIdEnc : '').'" />
																</div>
																<div class="field-ctrl">
																	'.$SITE->getHtmlComponent('iconic-checkbox', array('label'=>'APPROVE/DISAPPROVE ALL PENDING:', 'fieldName'=>$K='approve_all', 'fieldData'=>'data-toggle="smartToggler" data-keep-default="true" data-class-targets="t-disable"', 'on'=>isset($_POST[$K]))).'
																</div>
																<div class="field-ctrl">
																	<label>ACTION:</label>
																	<select name="approval_action" class="field" >
																		<option '.(($approvalAction == "approve")? 'selected' : '').'>Approve</option>
																		<option '.(($approvalAction == "disapprove")? 'selected' : '').' >Disapprove</option>
																		<option class="t-disable" '.(($approvalAction == "approve all")? 'selected' : '').' >Approve all</option>
																		<!--<option class="t-disable" '.(($approvalAction == "disapprove all")? 'selected' : '').' >Disapprove all</option>	-->						
																	</select>
																</div>
																<div class="field-ctrl btn-ctrl">
																	<input type="submit"  class="form-btn btn-success"  name="approve_campaign" value="PROCESS" />
																</div>
															</fieldset>
														</form>						
													</div>											
												</div>											
											</div>											
										</div>'.
										
										(!isset($isSearch)? '
											<div class="panel panel-pink">
												<h1 id="tab" class="panel-head page-title">TABLE OF MEMBER\'S CAMPAIGNS</h1>
												<div class="panel-body">'.
													'<h1>(page <span class="cyan">'.$pageId.'</span> of '.$totalPage.')</h1>'.
													(isset($campErr)? $campErr : '').$pagination.																														
													$SITE->buildSortLinks(array(
														'baseUrl' => $page_self_srt, 'pageId' => $pageId, 'sq' => '', 'urlHash' => 'tab', 
														'activeOrder' => $get_sort, 'orderList' => $sortList,
														'defaultOrder' => $defaultOrder, 'orderGlobLabel' => 'SORT BY'
														)).																						
													(isset($getCampaigns)? $getCampaigns : '').$pagination.'																				 														 
												</div>
											</div>' : ''
										).'
									</div>'

								: '').'
							</div>
						</div>'
		));

		break;

	}
	
	
	



	
	
	
	
	/**MODERATORS PAGE**/
	case 'mod-tools':{
		
		///GET AUTHORIZATIONS///
		$admin = $GLOBAL_isAdmin;
		$topStaff = $ACCOUNT->SESS->isTopStaff();

		////VARIABLE INITIALIZATION/////
		$action=$protection=$formName=$notLogged=$alertUser=$sections=$cats=$sectionsModerated=$categModerated=$tabLink=
		$pagination_admin=$alertAdminUser=$rqd_admin=$administrators=$siteMods=$sectionListOptions=
		$sectionName=$badgesOpt=$pageUrl=$categPageUrl=$dropDownAllCats=$selectedSection="";


		/***************************BEGIN URL CONTROLLER****************************/

		$optionSelected = ' selected="selected" ';
		
		$addedTxt = 'added';
		$removedTxt = 'removed';
		$nLogMonths = '(6 months or older)';
		
		//SELECT OPTIONS VALUE
		$addOpt = 'add';
		$subtractOpt = 'subtract';
		$awardOpt = 'award';
		$confiscateOpt = 'confiscate';
		$ultimateLvOpt = 'ultimate-level';
		$sectionModAddOpt = 'add-section-moderator';
		$sectionModRemOpt = 'remove-section-moderator';
		$categoryModAddOpt = 'add-category-moderator';
		$categoryModRemOpt = 'remove-category-moderator';
		$adminAddOpt = 'add-administrator';
		$adminRemOpt = 'remove-administrator';
		$topicLogOpt = 'topic';
		$allTopicLogOpt = 'topics';
		$sectionLogOpt = 'section';
		$allSectionLogOpt = 'sections';
		$categoryLogOpt = 'category';
		$allCategoryLogOpt = 'categories';


		$page_self_srt = $ENGINE->get_page_path($K = 'page_url', 2);
		$pageSelfSortSrch = $ENGINE->get_page_path($K, 4);
		$baseUrl = $pageUrlLowerCase;	
		
		$categTab = 'categories';
		$siteModsTab = 'site-moderators';
		$decentralizedModerationTab = 'dem';
		
		$urlList = array(//FORMAT => urlSlug:urlLabel:urlIcon:ignoreCond
			($adminMngrTab = 'manage-admins').':admins:<i class="fas fa-lock"></i>:'.!$admin, 
			($modsMngrTab = 'manage-moderators').':moderators:<i class="fas fa-users"></i>',
			($topicMngrTab = 'tdem').':topics:<i class="fas fa-newspaper"></i>',
			($postMngrTab = 'pdem').':posts:<i class="fas fa-comments"></i>',
			($badgeMngrTab = 'manage-badges').':badges:<i class="fas fa-trophy"></i>',
			($scatMngrTab = 'categories-sections').':sections:<i class="fas fa-list"></i>',
			($logMngrTab = 'manage-logs').':logs:<i class="fas fa-history"></i>:'.!$admin,
		);
		
		$modToolsTitle = 'Moderation Tools';
		$tabRoute = isset($pagePathArr[1])? strtolower($pagePathArr[1]) : '';

		if($tabRoute == $adminMngrTab){

			$pageTitle = 'Admin Manager';

			if(isset($pagePathArr[2]) && strtolower($pagePathArr[2]) == $siteModsTab){

				$pathKeysArr = array('pageUrl', 'tab', 'subtab', 'page_idm');
				$maxPath = 4;	
				
			}else{

				$pathKeysArr = array('pageUrl', 'tab', 'page_ida');
				$pageUrl = $baseUrl.'/'.$adminMngrTab;					
				$maxPath = 3;
					
			}
			
			$subNav = '<li><a href="/'.$pageSelf.'" title="">'.$pageTitle.'</a></li>';

		}elseif($tabRoute == $logMngrTab){
		
			$pathKeysArr = array('pageUrl', 'tab');
			$maxPath = 2;
			$pageTitle = 'Log Manager';			
			$subNav = '<li><a href="/'.$pageSelf.'">'.$pageTitle.'</a></li>';
		
		}elseif($tabRoute == $badgeMngrTab){
		
			$pathKeysArr = array('pageUrl', 'tab');
			$maxPath = 2;
			$pageTitle = 'Badge Manager';			
			$subNav = '<li><a href="/'.$pageSelf.'">'.$pageTitle.'</a></li>';
			//////GENERATE BADGE DROPDOWN///////
			$badgesOpt = $badgesAndReputations->loadBadges($meta_arr=array('wrapper'=>"<option>", 'selOpt'=>true, 'n'=>500));

		}elseif($tabRoute == $topicMngrTab){
		
			$pathKeysArr = array('pageUrl', 'tab', 'page_idt');
			$pageUrl = $baseUrl.'/'.$topicMngrTab;				
			$maxPath = 3;
			$pageTitle = 'Topic Manager';			
			$subNav = '<li><a href="/'.$pageSelf.'">'.$pageTitle.'</a></li>';
		
		}elseif($tabRoute == $postMngrTab){
		
			$pathKeysArr = array('pageUrl', 'tab');
			$pageUrl = $baseUrl.'/'.$postMngrTab;				
			$maxPath = 2;
			$pageTitle = 'Post Manager';			
			$subNav = '<li><a href="/'.$pageSelf.'">'.$pageTitle.'</a></li>';
		
		}elseif($tabRoute == $modsMngrTab){
		
			$pageTitle = 'Mods Manager';
							
			if(isset($pagePathArr[2]) && strtolower($pagePathArr[2]) == $categTab){
		
				$pathKeysArr = array('pageUrl', 'tab', 'categories', 'page_idc');
				$categPageUrl = $baseUrl.'/'.$modsMngrTab.'/'.$categTab;
				$pageUrl = $baseUrl.'/'.$modsMngrTab;
				$maxPath = 4;	
						
			}else{
			
				$pathKeysArr = array('pageUrl', 'tab', 'page_ids');
				$pageUrl = $baseUrl.'/'.$modsMngrTab;					
				$categPageUrl = $baseUrl.'/'.$modsMngrTab.'/'.$categTab;					
				$maxPath = 3;		
									
			}
			
			$subNav = '<li><a href="/'.$pageSelf.'" >'.$pageTitle.'</a></li>';
			
		}elseif($tabRoute == $scatMngrTab){
		
			$pathKeysArr = array('pageUrl', 'tab', 'page_idcs');
			$pageUrl = $baseUrl.'/'.$scatMngrTab;				
			$maxPath = 3;
			$pageTitle = 'Categories & Sections';			
			$subNav = '<li><a href="/'.$pageSelf.'">'.$pageTitle.'</a></li>';
		
		}elseif($tabRoute == $decentralizedModerationTab){
			
			$FORUM->doForumModeration();	
					
		}else{
			
			$pageTitle = $modToolsTitle;			

			$SITE->buildPageHtml(array('pageTitle'=>$pageTitle,
			'preBodyMetas'=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'">'.$pageTitle.'</a></li>'),
			'pageBody'=>'						
				<div class="single-base blend">			
					<div class="base-ctrl">
						<h1 class="page-title pan bg-mine-1">MODERATION TOOLS</h1>
						'.$SITE->buildLinearNav(array(
							"baseUrl" => $baseUrl,
							"urlListCls" => "panel-style panel-icon-block panel-head-md nav-pills nav-inline align-l",
							"urlList" => $urlList						
						
						)).'
					</div>
				</div>'
			));
		
		}
	
		$ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		/*******************************END URL CONTROLLER***************************/

		if(isset($_GET[$K="tab"]))
			$tab = $ENGINE->sanitize_user_input($_GET[$K], array('lowercase' => true));

		//////////GET FORM-GATE RESPONSE///////
		list($allAlert, $form) = $SITE->formGateRefreshResponse(true);

		$formName = isset($_POST[($K="form_name")])? $_POST[$K] : ($form? $form : '');

		$asterix = '<span class="asterix">*</span>';

		
		///////AUTHORIZE TOP LEVEL ACCESS///////////
		$ACCOUNT->authorizeTopAccess();

		if($sessUsername){

			//////REMEMBER SELECTED INDEX FROM DROPDOWN OPTION/////////

			if(isset($_POST[$K="section_name"]))
				$selectedSection = $ENGINE->sanitize_user_input($_POST[$K]);



			////////MANAGE BADGES////////////
			if(isset($_POST["manage_badges"])){

				$bdguname = $ENGINE->sanitize_user_input($_POST["bdguname"]);
				$bdg_uid = $ACCOUNT->memberIdToggle($bdguname, true);
				$bdgSucc=$bdgFailed="";
				$tgtUserLink = '<a href="/'.$bdguname.'" class="links">'.$bdguname.'</a>';

				if($bdg_uid){

					if(!empty($_POST["bdg"])){

						$action = $ENGINE->sanitize_user_input($_POST["bdgaction"]);
						$bdgArr = $_POST["bdg"];
						$award = ($action == $awardOpt);
						$cmt = ($award? 'AWARDED to ' : '<b class="bg-white red">CONFISCATED</b> from ').$tgtUserLink;							

						foreach($bdgArr as $bdg){
							
							$ok = $badgesAndReputations->awardBadge($bdg_uid, $bdg, $confiscate=!$award);								
							//$rep_reward = $badgesAndReputations->getBadgeDetail($bdg, "REPUTATION_REWARD");									

							if($ok)
								$bdgSucc .= $bdg.', ';
															
							elseif(!$ok ){
													
								if(strtolower($bdg) == 'student' && !$award)
									$why = '<span class="prime"> - badge is non-forfeitable</span>';
													
								elseif($badgesAndReputations->badgeAwardCount($bdg_uid, $bdg) && $award && strtolower($badgesAndReputations->getBadgeDetail($bdg, 'AWARD_FREQ')) != 'm')
									$why = '<span class="prime"> - can only be awarded once to a user</span>';
													
								else
									$why = '<span class="prime"> - '.$bdguname.' does\'nt have the badge</span>';
													
								$bdgFailed .= $bdg.$why.'<br/>';
													
							} 
						}

						$badgeAlert = ($bdgSucc? '<span class="alert alert-success">The following badge(s) were successfully '.$cmt.':<br/>'.trim($bdgSucc, ", ").'</span>' : '').
										($bdgFailed? '<span class="alert alert-warning">The following badges could\'nt be '.strtolower($cmt).':<br/>'.$bdgFailed.'</span>' : '');

					}else
						$badgeAlert = '<span class="alert alert-danger">Please select a badge</span>';
													
				}elseif($bdguname)
					$badgeAlert = '<span class="alert alert-danger">Sorry that username is not registered. Please verify and try again</span>';
													
				else{
													
					$badgeAlert = '<span class="alert alert-danger">Please Enter Target Username.</span>'; 
					$badgeFieldErr = 'field-error';
													
				}
			}


			//////GENERAT LIST OF THE CATEGORIES AND THEIR IDs///////

			/////PDO QUERY////////

			$sql = "SELECT ID, CATEG_NAME FROM categories ORDER BY CATEG_NAME";
			$stmt = $dbm->query($sql);

			while($row = $dbm->fetchRow($stmt)){

				$catName = $row["CATEG_NAME"];
				$catId = $row["ID"];

				if(isset($selectedSection) && strtolower($catName) == strtolower($selectedSection ))
					$dropDownAllCats .= "<option selected>".$catName."</option>";

				else
					$dropDownAllCats .= "<option>".$catName."</option>";

				$actvLink = '<a href="/mods-activities-log?log=category&param='.$catId.'" class="links" target="_blank">View Logs</class>';
				
				$cats .= '<tr><td>'.$catId.'</td><td>'.$ENGINE->sanitize_slug($catName, array('ret'=>'url')).'</td><td>'.$SITE->moderatedSectionCategoryHandler(array('scId'=>$catId, 'level'=>2, 'action'=>'get', 'vcardMin'=>true, 'n'=>10)).'</td><td>'.$actvLink.'</td></tr>';

			}

			$cats = '<div id="ctab" class="table-responsive">
						<table class="table-classic">
							<caption class="">CATEGORIES<caption>
							<tr><th>CATEGORY ID</th><th>CATEGORY NAME</th>
							<th>MODERATORS</th><th>ACTIVITIES</th></tr>'.$cats.'
						</table>
					</div>';


			/////GENERATE LIST OF THE SECTIONS AND THEIR IDs////////////

			////PDO QUERY///////////

			$sql = "SELECT ID, SECTION_NAME FROM sections WHERE ID NOT IN(".getExceptionParams('virtualcsid', false).") ORDER BY SECTION_NAME";
			$stmt = $dbm->query($sql);

			while($row = $dbm->fetchRow($stmt)){
				
				$sectionId = $row["ID"];
				$sectionName = $row["SECTION_NAME"];
				
				$actvLink = '<a href="/mods-activities-log?log=section&param='.$sectionId.'" class="links" target="_blank">View Logs</class>';
				$sections .= '<tr><td>'.$sectionId.'</td><td>'.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).'</td><td>'.$SITE->moderatedSectionCategoryHandler(array('scId'=>$sectionId, 'level'=>1, 'action'=>'get', 'vcardMin'=>true, 'n'=>10)).'</td><td>'.$actvLink.'</td></tr>';

			}

			$sections = '<div id="stab" class="table-responsive">					
							<table class="table-classic">
								<caption class="">SECTIONS<caption>
								<tr><th>SECTION ID</th><th>SECTION NAME</th><th>MODERATORS</th><th>ACTIVITIES</th></tr>'.$sections.'
							</table>
						</div>';



			///GENERATE SECTION SELECT DROPDOWN MENU//////////

			list($moderatedSectionListOptions, $sectionListOptions, $categoryOptionGroup, $homepageOptionGroup) = $FORUM->getModPageSectionSelectList($selectedSection);						

			////////MANAGE ACTIVITIES LOGS//////
			$monthsOldChecked = 'checked="checked"';
			
			if(isset($_POST["manage_logs"])){

				$nmonths = '';
				$nOld = false;
				$action = $_POST["action"];

				if(isset($_POST["n_old"])){

					$nmonths = $nLogMonths;
					$nOld = true;	
		
				}else
					$monthsOldChecked = '';
				
				$lid = $_POST["lid"];

				if(($forTopic = $action == $topicLogOpt)  || $action == $allTopicLogOpt){

					if($forTopic){

						if($lid){

							if($topicName = $SITE->topicIdToggle($lid)){
								
								$SITE->cleanActivityLog('t', $lid, $nOld);						
								$log_alert = '<span class="alert alert-success"> You have successfully cleared the activities log'.$nmonths.' of the topic: '.$ENGINE->sanitize_slug($SITE->getThreadSlug($lid), array('ret'=>'url', 'urlText'=>$topicName, 'slugSanitized'=>true)).'.</span>';

							}else
								$log_alert = '<span class="alert alert-danger">Sorry the topic ID you entered is invalid. <br/>Please verify the topic ID and try again</span>';

						}else{
				
							$log_alert = '<span class="alert alert-danger">Please enter the target topic id !</span>';
							$rqd_log = $asterix;
				
						}
				
					}else{
						
						$SITE->cleanActivityLog('t', '', $nOld);
						$log_alert = '<span class="alert alert-success"> You have successfully cleared the activities log'.$nmonths.' of all the topics</span>';

					}

				}elseif(($forSection = $action == $sectionLogOpt) || $action == $allSectionLogOpt){

					if($forSection){

						if($lid){

							if($sectionName = $SITE->sectionIdToggle($lid)){
								
								$SITE->cleanActivityLog('s', $lid, $nOld);
								$log_alert = '<span class="alert alert-success"> You have successfully cleared the activities log'.$nmonths.' of the '.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).' section.</span>';

							}else
								$log_alert = '<span class="alert alert-danger">Sorry the section ID you entered is invalid. <br/>Please lookup tables of sections and categories for valid IDs and try again</span>';

						}else
							$log_alert = '<span class="alert alert-danger">Please enter the target section id !</span>';
				
					}else{
						
						$SITE->cleanActivityLog('s', '', $nOld);				
						$log_alert = '<span class="alert alert-success"> You have successfully cleared the activities log'.$nmonths.' of all the sections</span>';

					}

				}elseif(($forCategory = $action == $categoryLogOpt) || $action == $allCategoryLogOpt){

					if($forCategory)  {

						if($lid){

							if($categName = $SITE->categoryIdToggle($lid)){
								
								$SITE->cleanActivityLog('c', $lid, $nOld);						
								$log_alert = '<span class="alert alert-success"> You have successfully cleared the activities log'.$nmonths.' of the '.$ENGINE->sanitize_slug($categName, array('ret'=>'url')).' category.</span>';

							}else
								$log_alert = '<span class="alert alert-danger">Sorry the category ID you entered is invalid. <br/>Please lookup tables of sections and categories for valid IDs and try again</span>';

						}else
							$log_alert = '<span class="alert alert-danger">Please enter the target category id !</span>';

					}else{
						
						$SITE->cleanActivityLog('c', '', $nOld);				
						$log_alert = '<span class="alert alert-success"> You have successfully cleared the activities log'.$nmonths.' of all the categories</span>';

					}

				}

			}



			/////MANAGE MOD REPUTATION LEVELS//////

			if(isset($_POST["manage_mrl"])){

				$ok = true;
				$mrl = $ENGINE->sanitize_number($_POST["mrl"]);
				$action = $ENGINE->sanitize_user_input($_POST["mrl_action"]);
				$username = $ENGINE->sanitize_user_input($_POST["uid_mrl"], array('lowercase' => true));
				$usernameSlug = $ACCOUNT->sanitizeUserSlug($username);
				$user = $ACCOUNT->loadUser($username);
				$userFound = $user->getUserId();
				$ultimateLevel = $user->getUltimateLevel(true);
				$isAddAction = ($action == $addOpt);
				$promotionTxt = 'promoted';
				$demotionTxt = '<span class="red">demoted</span>';
				$actionText = $isAddAction? $promotionTxt : $demotionTxt;

				if($username && $userFound){

					if($action == $ultimateLvOpt){
						
						$actionText = $ultimateLevel? $demotionTxt : $promotionTxt;
						$subQry = 'ULTIMATE_LEVEL='.($ultimateLevel? 0 : 1);
						$mrl = 'tusted_ultimate';							
						$ACCOUNT->updateUser($username, $subQry);
						
					}else
						$badgesAndReputations->awardReputation($username, $mrl, $confiscate=($isAddAction? false : true));
					
					$alertMrl = '<span class="alert alert-success">You have successfully <b>'.$actionText.'</b> <a href="/'.$usernameSlug.'" class="links">'.$username.' </a> with '.$mrl.' reputation</span>';
					////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
					$SITE->formGateRefresh(array($alertMrl, $formName),"", "#mrl-admins");


				}elseif($username)
					$alertMrl = '<span class="alert alert-danger">Sorry the user: '.$username.' was not found<br/> Please enter a valid registered member to proceed.</span>';

			}


			
		
			/////MANAGE MODERATORS//////////

			if(isset($_POST["manage_moderators"])){

				$action = $_POST["action"];
				$username = $ENGINE->sanitize_user_input($_POST["username"], array('lowercase' => true));
				$usernameSlug = $ACCOUNT->sanitizeUserSlug($username);
				$sectionName = $_POST["section_name"];					
				
				$isSectionOp = $isCategoryOp = $addMod = $removeMod = false;

				switch($action){

					case $categoryModAddOpt:
						$addMod = $isCategoryOp = $doneActionTxt = $addedTxt; break;

					case $categoryModRemOpt:
						$removeMod = $isCategoryOp = $doneActionTxt = $removedTxt; break;					
					
					case $sectionModAddOpt:
						 $addMod = $isSectionOp = $doneActionTxt = $addedTxt; break;
						
					case $sectionModRemOpt:					
						$removeMod = $isSectionOp = $doneActionTxt = $removedTxt;

				}

				list($ranksHigher, $ranksEqual) = $ACCOUNT->sessionRanksHigher($username);
				
				$isSess = (strtolower($sessUsername) == $username);
				
				if($ranksHigher){
					
					if($action && $username && $sectionName){

						$sid = $SITE->sectionIdToggle($sectionName);
						$uid = $ACCOUNT->memberIdToggle($username, true);
						
						//MANAGE MODERATORS FOR SECTIONS
						if($isSectionOp && !in_array($sid, VIRTUAL_CSID_ARR) ){

							if($sid){
								//////VERIFY THE MODRIGHT////////
								if($SITE->moderatedSectionCategoryHandler(array('uid'=>$sessUid,'scId'=>$sid,'level'=>1,'action'=>'checkBoth'))  || $admin){

									//////VERIFY THAT THE USER EXISTS/////
									if($uid){

										if($addMod){
											
											//RESTRICT NUMBERS OF SECTIONS MODERATABLE
											if($SITE->moderatedSectionCategoryHandler(array('uid'=>$uid,'action'=>'isMod','level'=>1)) < MAX_SECTIONS_MODERATABLE){
											
												/**CHECK IF HE MODERATES THE TARGET SECTION**/
												if(!$SITE->moderatedSectionCategoryHandler(array('uid'=>$uid,'scId'=>$sid,'level'=>1,'action'=>'checkBoth'))){
													$SITE->moderatedSectionCategoryHandler(array('uid'=>$uid,'scId'=>$sid,'level'=>1,'action'=>'add'));
													
													//LOG ACTIVITY//							
													$ACT = 'ADDED %U'.$uid.'%U';
													$SITE->logActivity('s', $sid, $ACT);
													
													////FORMAT LINKS IN PM//////

													$pmEncodedSessUsername = '[a '.$sessUsernameSlug.']'.$sessUsername.'[/a]';										
													$pmEncodedUsername = '[a '.$usernameSlug.']'.$username.'[/a]';
													$pmEncodedSectionName = '[a ' .$ENGINE->sanitize_slug($sectionName).']'.$sectionName.'[/a]';


													////PM ALL MODERATORS IN THE SECTION INCLUDING THE ADMINS///////

													$subject = $pmEncodedSessUsername." added ".$pmEncodedUsername." as a moderator  [To All ".$pmEncodedSectionName." moderators@".$siteName."]";

													$message =  $pmEncodedSessUsername." added ".$pmEncodedUsername." as a Moderator in the ".$pmEncodedSectionName." Section. This message is to notify all existing moderators in this section  " ;
													
													//sender => "Webmaster"
													$senderId = 0;

													$receiversId = $SITE->moderatedSectionCategoryHandler(array('scId'=>$sid,'level'=>1,'action'=>'get','retType'=>'id'));

													$recId_arr = explode(",", $receiversId );

													foreach($recId_arr as $recId)
														$SITE->sendPm($senderId, $recId, $subject, $message);

													
													$alertUser = '<span class="alert alert-success">'.$GLOBAL_sessionUrl.' have successfully <span>'.$doneActionTxt.'</span> <a class="links" href="/'.$usernameSlug.'">'.($isSess? 'Yourself' : $username).'</a>  as a moderator in the '.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).' Section</span>';

												}else
													$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', <a class="links" href="/'.$usernameSlug.'">'.($isSess? 'You' : $username).'</a> '.($isSess? 'are' : 'is').' already a moderator or super-moderator in the '.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).' Section</span>';
											}else
												$alertUser = '<span class="alert alert-danger">Sorry! to ensure effective moderations, '.($isSess? 'You' : $username).' can only moderate a maximum of '.MAX_SECTIONS_MODERATABLE.' sections</span>';

										}elseif($removeMod){

											/**CHECK IF HE MODERATES THE TARGET SECTION**/

											if($SITE->moderatedSectionCategoryHandler(array('uid'=>$uid,'scId'=>$sid,'level'=>1,'action'=>'check'))){
												
												##ENSURES THE USER GETS A REMOVAL NOTIFICATION
												$receiversId = $SITE->moderatedSectionCategoryHandler(array('scId'=>$sid,'level'=>1,'action'=>'get','retType'=>'id'));
												
												$SITE->moderatedSectionCategoryHandler(array('uid'=>$uid,'scId'=>$sid,'level'=>1,'action'=>'del'));
												
												//LOG ACTIVITY//							
												$ACT = 'REMOVED %U'.$uid.'%U';
												$SITE->logActivity('s', $sid, $ACT);

												/////FORMAT LINKS IN PM//////

												$pmEncodedSessUsername = '[a '.$sessUsernameSlug.']'.$sessUsername.'[/a]';										
												$pmEncodedUsername = '[a '.$usernameSlug.']'.$username.'[/a]';
												$pmEncodedSectionName = '[a ' .$ENGINE->sanitize_slug($sectionName).']'.$sectionName.'[/a]';


												//PM ALL MODERATORS IN THE SECTION INCLUDING THE ADMINS///

												$subject = $pmEncodedSessUsername." removed  ".$pmEncodedUsername." as a moderator  [To All ".$pmEncodedSectionName." moderators@".$siteName."]";

												$message =  $pmEncodedSessUsername." removed ".$pmEncodedUsername." as a Moderator in the ".$pmEncodedSectionName." Section. This message is to notify all existing moderators in this section  " ;
												
												//sender => "Webmaster"
												$senderId = 0;

												$recId_arr = explode(",", $receiversId );

												foreach($recId_arr as $recId)
													$SITE->sendPm($senderId, $recId, $subject, $message);


												$alertUser = '<span class="alert alert-success">'.$GLOBAL_sessionUrl.' have successfully <span>'.$doneActionTxt.'</span> <a class="links" href="/'.$usernameSlug.'">'.($isSess? 'Yourself' : $username).'</a>  as a moderator in the '.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).' Section</span>';

											}else
												$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', <a class="links" href="/'.$usernameSlug.'">'.($isSess? 'You' : $username).'</a> '.($isSess? 'are' : 'is').' not a moderator in the '.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).' Section</span>';

										}


									}else
										$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', <a class="links" href="/'.$usernameSlug.'">'.$username.'</a>  is not a member of this <a href="/" class="links">community</a><br/>Only registered members can be added or removed as moderators</span>';

								}else
									$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', Sorry you are not a moderator in the '.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).'
										<br/>section and hence can\'t manage moderators in this section</span>';


							}else
								$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', the section id you entered is Invalid please Look Up Valid section IDs<br/> from the Tables of sections & categories </span>';

						}////MANAGE MODERATORS FOR CATEGORIES/////
						elseif($isCategoryOp && in_array($sid, VIRTUAL_CSID_ARR)){

							///GET THE CATEGORY ID FROM THE NAME PASSED////
							
							$sid = $SITE->categoryIdToggle($sectionName);

							if($sid){

								if($SITE->moderatedSectionCategoryHandler(array('uid'=>$sessUid,'scId'=>$sid,'level'=>2,'action'=>'check'))  || $admin){

									///VERIFY THE USER EXISTS/////

									if($uid = $ACCOUNT->memberIdToggle($username, true)){	
									
										if($addMod){	
										
											//ADD ONLY IF NOT ALREADY A SUPER-MODERATOR IN OTHER CATEGORY
											if(!$SITE->moderatedSectionCategoryHandler(array('uid'=>$uid,'action'=>'isMod','level'=>2))){
												
												/**CHECK IF HE MODERATES THE TARGET CATEGORY**/
												if(!$SITE->moderatedSectionCategoryHandler(array('uid'=>$uid,'scId'=>$sid,'level'=>2,'action'=>'check'))){
													
													$SITE->moderatedSectionCategoryHandler(array('uid'=>$uid,'scId'=>$sid,'level'=>2,'action'=>'add'));
													
													//LOG ACTIVITY//							
													$ACT = 'ADDED %U'.$uid.'%U';
													$SITE->logActivity('c', $sid, $ACT);

													///FORMAT LINKS IN PM//////

													$pmEncodedSessUsername = '[a '.$sessUsernameSlug.']'.$sessUsername.'[/a]';										
													$pmEncodedUsername = '[a '.$usernameSlug.']'.$username.'[/a]';
													$pmEncodedSectionName = '[a ' .$ENGINE->sanitize_slug($sectionName).']'.$sectionName.'[/a]';


													////PM ALL MODERATORS IN THE CATEGORY INCLUDING THE ADMINS///////

													$subject = $pmEncodedSessUsername." added ".$pmEncodedUsername." as a moderator  [To All ".$pmEncodedSectionName." moderators@".$siteName."]";

													$message =  $pmEncodedSessUsername." added ".$pmEncodedUsername." as a Moderator in the ".$pmEncodedSectionName." Category. This message is to notify all existing moderators in this Category  " ;
													
													//sender => "Webmaster"
													$senderId = 0;

													$receiversId = $SITE->moderatedSectionCategoryHandler(array('scId'=>$sid,'level'=>2,'action'=>'get','retType'=>'id'));

													$recId_arr = explode(",", $receiversId );

													foreach($recId_arr as $recId)
														$SITE->sendPm($senderId, $recId, $subject, $message);

													
													$alertUser = '<span class="alert alert-success">'.$GLOBAL_sessionUrl.' have successfully <span>'.$doneActionTxt.'</span> <a class="links" href="/'.$usernameSlug.'">'.($isSess? 'Yourself' : $username).'</a>  as a moderator in the '.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).' Category</span>';

												}else													
													$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', <a class="links" href="/'.$usernameSlug.'">'.($isSess? 'You' : $username).'</a> '.($isSess? 'are' : 'is').' already a moderator in the '.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).' Category</span>';
											}else
												$alertUser = '<span class="alert alert-danger">Sorry! for effective moderations, '.($isSess? 'You' : $username).' can only super moderate in one category</span>';


										}elseif($removeMod){

											/**CHECK IF HE MODERATES THE TARGET CATEGORY**/

											if($SITE->moderatedSectionCategoryHandler(array('uid'=>$uid,'scId'=>$sid,'level'=>2,'action'=>'check'))){
																									
												##ENSURES THE USER GETS A REMOVAL NOTIFICATION
												$receiversId = $SITE->moderatedSectionCategoryHandler(array('scId'=>$sid,'level'=>2,'action'=>'get','retType'=>'id'));													
												
												$SITE->moderatedSectionCategoryHandler(array('uid'=>$uid,'scId'=>$sid,'level'=>2,'action'=>'del'));
											
												//LOG ACTIVITY//							
												$ACT = 'REMOVED %U'.$uid.'%U';
												$SITE->logActivity('c', $sid, $ACT);

												///FORMAT LINKS IN PM////

												$pmEncodedSessUsername = '[a '.$sessUsernameSlug.']'.$sessUsername.'[/a]';										
												$pmEncodedUsername = '[a '.$usernameSlug.']'.$username.'[/a]';
												$pmEncodedSectionName = '[a ' .$ENGINE->sanitize_slug($sectionName).']'.$sectionName.'[/a]';



												///ALERT ALL MODERATORS IN THE CATEGORY INCLUDING THE ADMINS///

												$subject = $pmEncodedSessUsername." removed ".$pmEncodedUsername." as a moderator  [To All ".$pmEncodedSectionName." moderators@".$siteName."]";

												$message =  $pmEncodedSessUsername." removed ".$pmEncodedUsername." as a Moderator in the ".$pmEncodedSectionName." Category. This message is to notify all existing moderators in this Category  " ;
												
												//sender => "Webmaster"
												$senderId = 0;
												
												$recId_arr = explode(",", $receiversId );

												foreach($recId_arr as $recId)
													$SITE->sendPm($senderId, $recId, $subject, $message);

												$alertUser = '<span class="alert alert-success">'.$GLOBAL_sessionUrl.' have successfully <span>'.$doneActionTxt.'</span> <a class="links" href="/'.$usernameSlug.'">'.($isSess? 'Yourself' : $username).'</a>  as a moderator in the '.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).' Category</span>';

											}else
												$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', <a class="links" href="/'.$usernameSlug.'">'.($isSess? 'You' : $username).'</a> '.($isSess? 'are' : 'is').' not a moderator in the '.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).' Category</span>';

										}
								
									}else
										$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', <a class="links" href="/'.$usernameSlug.'">'.$username.'</a>  is not a member of this <a href="/" class="links">community</a><br/>Only registered members can be added or removed as moderators</span>';

								}else
									$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', Sorry you are not a moderator in the '.$ENGINE->sanitize_slug($sectionName, array('ret'=>'url')).'
										<br/>category and hence can\'t manage moderators in this category</span>';


							}else
								$alertUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', the category id you entered is Invalid please Look Up Valid categories IDs<br/> from the Tables of sections & categories </span>';

						}else
							$alertUser = '<span class="alert alert-danger">Please select an appropriate target action</span>';
						
						///////////END OF FOR CATEGORIES///////////

					}else{
					
						$alertUser = '<span class="alert alert-danger"> All fields are required </span>';
						$rqd = $asterix;

					}

				}else
					$alertUser = '<span class="alert alert-danger">Sorry '.$GLOBAL_sessionUrl_unOnly.', the user <a href="/'.$usernameSlug.'" class="links">'.$username.'</a> '.(($ranksEqual)? 'has equal ranks with you,' : 'ranks higher than you,').' hence you do not have enough privilege to manage him/her</span>';

			}




			/////MANAGE ADMINISTRATOR////

			if(isset($_POST["manage_admin"])){

				$action = $_POST["action"];
				$username = $ENGINE->sanitize_user_input($_POST["username"], array('lowercase' => true));
				$usernameSlug = $ACCOUNT->sanitizeUserSlug($username);
				$isSess = (strtolower($sessUsername) == $username);
				$addAdmin = $removeAdmin = false;

				if($action == $adminAddOpt){

					$addAdmin = $doneActionTxt = $addedTxt;

				}elseif($action == $adminRemOpt){

					$removeAdmin = $doneActionTxt = $removedTxt;

				}

				if($action && $username ){
					
					list($ranksHigher, $ranksEqual) = $ACCOUNT->sessionRanksHigher($username);

					if($ranksHigher){

						//VERIFY THE USER EXISTS//

						if($uid = $ACCOUNT->memberIdToggle($username, true)){

							if($uid != $sessUid){

								if($addAdmin){

									if($ACCOUNT->updatePrivilege($uid, $newRank=ADMIN)){

										///////////FORMAT LINKS IN PM///////

										$pmEncodedSessUsername = '[a '.$sessUsernameSlug.']'.$sessUsername.'[/a]';
										$pmEncodedUsername = '[a '.$usernameSlug.']'.$username.'[/a]';
										//$pmEncodedSectionName = '[a ' .$ENGINE->sanitize_slug($sectionName).']'.$sectionName.'[/a]';


										//ALERT THE ULTIMATE ADMINS AND THE NEWLY ADDED ADMIN THROUGH PM//////

										$subject = $pmEncodedSessUsername." added ".$pmEncodedUsername." as an Administrator  [To All Admins@".$siteName."]";

										$message =  $pmEncodedSessUsername." added ".$pmEncodedUsername." as an Administrator. This message is to notify all existing administrators  " ;
										
										//sender => "Webmaster"
										$senderId = 0;

										$receiversIds = $SITE->getAdmins("id");

										$recId_arr = explode(",", $receiversIds );

										foreach($recId_arr as $recId)
											$SITE->sendPm($senderId, $recId, $subject, $message);

										
										$alertAdminUser = '<span class="alert alert-success">'.$GLOBAL_sessionUrl.' have successfully <span>'.$doneActionTxt.'</span> <a class="links" href="/'.$usernameSlug.'">'.($isSess? 'Yourself' : $username).'</a> as an Administrator</span>';																

									}else								
										$alertAdminUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', <a class="links" href="/'.$usernameSlug.'">'.($isSess? 'You' : $username).'</a>  '.($isSess? 'are' : 'is').' already an Administrator </span>';								
									

								}elseif($removeAdmin){

									///////

									/**
										MAKE SURE THE USER IS NOT MODERATING IN ANY SECTION OR CATEGORY B4 RESETING PRIVILEGE TO MEMBER
									**/
											
									if($ACCOUNT->updatePrivilege($uid, $newRank=($SITE->moderatedSectionCategoryHandler(array('uid'=>$uid,'action'=>'isMod'))? MODERATOR : ''), true)){

										////FORMAT LINKS IN PM/////////

										$pmEncodedSessUsername = '[a '.$sessUsernameSlug.']'.$sessUsername.'[/a]';
										$pmEncodedUsername = '[a '.$usernameSlug.']'.$username.'[/a]';
										
										//$pmEncodedSectionName = '[a ' .$ENGINE->sanitize_slug($sectionName).']'.$sectionName.'[/a]';


										////ALERT THE ULTIMATE ADMINS AND THE NEWLY ADDED ADMIN THROUGH PM///

										$subject = $pmEncodedSessUsername." removed ".$pmEncodedUsername." as an Administrator  [To All Admins@".$siteName."]";

										$message =  $pmEncodedSessUsername." removed ".$pmEncodedUsername." as an Administrator. This message is to notify all existing administrators  " ;											
										
										//sender => "Webmaster"
										$senderId = 0;

										$receiversIds = $SITE->getAdmins("id");

										$recId_arr = explode(",", $receiversIds );

										foreach($recId_arr as $recId)
											$SITE->sendPm($senderId, $recId, $subject, $message);

										
										$alertAdminUser = '<span class="alert alert-success">'.$GLOBAL_sessionUrl.' have successfully <span>'.$doneActionTxt.'</span> <a class="links" href="/'.$usernameSlug.'">'.($isSess? 'Yourself' : $username).'</a>  as an Administrator</span>';									

										
									}else								
										$alertAdminUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', <a class="links" href="/'.$usernameSlug.'">'.($isSess? 'You' : $username).'</a> '.($isSess? 'are' : 'is').' not an Administrator </span>';


								}
										
								////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
								$SITE->formGateRefresh(array($alertAdminUser, $formName),"", "#mad");

							}else
								$alertAdminUser = '<span class="alert alert-danger">Sorry '.$GLOBAL_sessionUrl.' cannot manage yourself. Please contact other administrators to manage you</span>';

						}else
							$alertAdminUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', <a class="links" href="/'.$usernameSlug.'">'.$username.'</a> is not a member of this <a href="/" class="links">community</a><br/>Only registered members can be added or removed as Administrators.</span>';

					}else
						$alertAdminUser = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.', Sorry you are just a level 1 Administrator
							<br/>and do not have enough privilege to remove administrators. You can only add new Administrators</span>';


				}else{

					$alertAdminUser = '<span class="alert alert-danger"> All fields are required </span>';

					$rqd_admin = $asterix;


				}


			}



			/////GENERATE LIST OF THE CATEGORIES THAT YOU MODERATE/////
			//GET CATEGORIES MODERATED SUBQRY
			$cmQry = $SITE->moderatedSectionCategoryHandler(array('action'=>'cm-qry'));
			$valArr = $admin? array() : array($sessUid);
			//PDO QUERY//
			$sql = "SELECT COUNT(*) FROM categories ".(!$admin? "WHERE ID IN(".$cmQry.")" : "");
			$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();

			/**********CREATE THE PAGINATION******/
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$categPageUrl,'pageKey'=>'page_idc','jmpKey'=>'jump_page_cat','hash'=>'c-mods'));
			$pagination_cat = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageIdOut = $paginationArr["pageId"];

			/////END OF PAGINATION//////

			$modsActivities="";

			//PDO QUERY////
			$sql = "SELECT ID, CATEG_NAME FROM categories ".(!$admin? "WHERE ID IN(".$cmQry.")" : "")." LIMIT ".$startIndex.",".$perPage;
			$stmt = $dbm->doSecuredQuery($sql, $valArr);
			
			while($row = $dbm->fetchRow($stmt)){
				
				$categId = $row["ID"];
				$categName = $row["CATEG_NAME"];
				
				$modsActivities = '<a href="/mods-activities-log?log=category&param='.$categId.'" class="links" target="_blank">View Logs</class>';

				$categModerated .= "<tr><td>".$categId."</td><td>".$categName."</td><td>".$SITE->moderatedSectionCategoryHandler(array('scId'=>$categId,'level'=>2,'action'=>'get','n'=>10,'sep'=>'<hr/>'))."</td>
								<td>".$modsActivities."</td></tr>";

			}

			$categModerated = "<div class='table-responsive' ><table class='table-classic'><tr><th>CATEG ID</th><th>CATEG NAME</th><th>CATEG MODERATORS</th><th>CATEG ACTIVITIES</th></tr>".$categModerated."</table></div>";
			
			//GET SECTIONS MODERATED SUBQRY
			$scmQry = $SITE->moderatedSectionCategoryHandler(array('action'=>'scm-qry'));
			$valArr = $admin? array() : array($sessUid, $sessUid);
			
			///GENERATE LIST OF THE SECTIONS THAT YOU MODERATE//////				
			
			///PDO QUERY////
			$sql = "SELECT COUNT(*) FROM sections ".(!$admin? "WHERE ID IN(".$scmQry.")" : "");
			$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();

			/**********CREATE THE PAGINATION*********/
			$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>'page_ids','jmpKey'=>'jump_page_scat','hash'=>'s-mods'));
			$pagination_scat = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$page_ids = $paginationArr["pageId"];

			//////END OF PAGINATION/////

			$modsActivities=$sectionsModerated="";

			//PDO QUERY///
			$sql =  "SELECT ID, SECTION_NAME FROM sections ".(!$admin? "WHERE ID IN(".$scmQry.")" : "")." LIMIT ".$startIndex.",".$perPage;
			$stmt = $dbm->doSecuredQuery($sql, $valArr);

			while($row = $dbm->fetchRow($stmt)){

				$modsActivities = '<a href="/mods-activities-log?log=section&param='.($sectionId = $row["ID"]).'" class="links" target="_blank">View Logs</class>';

				$sectionsModerated .= '<tr><td>'.$sectionId.'</td><td>'.$row["SECTION_NAME"].'</td><td>'.$SITE->moderatedSectionCategoryHandler(array('scId'=>$sectionId, 'level'=>1, 'action'=>'get', 'vcardMin'=>true, 'n'=>10, 'sep'=>'<hr/>')).'</td>
								<td>'.$modsActivities.'</td></tr>';

			}

			$sectionsModerated = '<div class="table-responsive" ><table class="table-classic"><tr><th>SECTION ID</th><th>SECTION NAME</th><th>SECTION MODERATORS</th><th>SECTION ACTIVITIES</th></tr>'.$sectionsModerated.'</table></div>';


			/////GENERATE LIST OF TOPIC ACTIVITIES IN THE SECTIONS YOU MODERATE///////

			$modsActivities=$topicsModerated="";

			if($GLOBAL_isStaff){								
				
				$valArr = $admin? array() : array($sessUid, $sessUid);
				
				//PDO QUERY/////

				$sql =  "SELECT COUNT(*) FROM topics ".($admin? "" : "WHERE SECTION_ID IN (".$scmQry.")");
				$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();

				/**********CREATE THE PAGINATION*************/
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>'page_idt','jmpKey'=>'jump_page_topic','hash'=>'s-top'));
				$pagination_topics = $paginationArr["pagination"];
				$totalPage = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$page_idt = $paginationArr["pageId"];

				//////END OF PAGINATION/////


				///PDO QUERY//////
				$sql = $SITE->composeQuery(array('type' => 'for_topic', 'start' => $startIndex, 'stop' => $perPage, 'filterCnd' => $admin? '1=1' : 'SECTION_ID IN('.$scmQry.')', 'orderBy' => 'TIME DESC'));
				$stmt = $dbm->doSecuredQuery($sql, $valArr);

				while($row = $dbm->fetchRow($stmt)){

					$modsActivities = '<a href="/mods-activities-log?log=topic&param='.($topicId = $row["ID"]).'" class="links" target="_blank">View Logs</class>';
					$sectionId = $row["SECTION_ID"];
					$sectionName = $row["SECTION_NAME"];
					$catId = $row["CATEG_ID"];
					$catName = $row["CATEG_NAME"];
					$topicName = $row["TOPIC_NAME"];

					$topicsModerated .= '<tr><td>'.$topicId.'</td><td>'.$ENGINE->sanitize_slug($SITE->getThreadSlug($topicId), array('ret'=>'url', 'urlText'=>$topicName, 'slugSanitized'=>true)).'</td><td>'. $sectionId.'</td><td>'.$catId.'</td>'.
					($admin? '<td>'. $sectionName.'</td><td>'.$catName.'</td>' : '').'<td>'.$ENGINE->time_ago($row["TIME"]).'</td>
					<td>'.$FORUM->getLastActivityModerator('t', $row["ID"]).'</td><td>'.$modsActivities.'</td></tr>';

				}
				
				$topicsModerated = '<div class="table-responsive" ><table class="table-classic"><caption class="bg-limex prime">'.$totalRecords.' topic(s)</caption>
					<tr><th>TOPIC ID</th><th>TOPIC NAME</th>
					<th>SECTION ID</th><th>CATEG ID</th>'.($admin? '<th>SECTION</th><th>CATEGORY</th>' : '').'<th>CREATED</th><th>LAST MODERATED BY</th>
					<th>TOPIC ACTIVITIES</th></tr>'.$topicsModerated.'</table></div>';

			}
			
			
			
			////GENERATE TABLE OF ALL ADMINS////
			
			if($admin && $tab == $adminMngrTab){

				list($administrators, $pagination_admin) = $SITE->getAdmins('table', array('url' => $pageUrl));


				/////GENERATE TABLE OF SITE MODERATORS/////
				
				$totalRecords = $SITE->moderatedSectionCategoryHandler(array('action'=>'count'));

				/**********CREATE THE PAGINATION*************/
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$baseUrl.'/'.$adminMngrTab.'/'.$siteModsTab,'pageKey'=>'page_idm','jmpKey'=>'jump_page_amods','hash'=>'amods'));
				$pagination_amods = $paginationArr["pagination"];
				$totalPage = $paginationArr["totalPage"];
				$n = $paginationArr["perPage"];
				$i = $paginationArr["startIndex"];
				$pageId = $paginationArr["pageId"];

				$reputationLevelList='';

				foreach(array(1, 5, 10, 50, 100, 500, 1000, 2000, 3000, 5000, 8000, 10000, 15000) as $repLev){

					$reputationLevelList .= '<option '.((isset($mrl) && $mrl == $repLev)? $optionSelected : "").'>'.$repLev.'</option>';

				}
				
				$siteMods = $SITE->moderatedSectionCategoryHandler(array('action'=>'get','modsTable'=>true,'sep'=>'<hr/>','i'=>$i,'n'=>$n));

				$adminManager = '
							
							<div class="panel panel-bluex">
								<h1 id="s-admins" class="panel-head page-title">MANAGE ADMINS</h1>
								<div class="panel-body">
									<h2 id="mad" class="prime"><small>Add or Remove Administrators</small></h2>							
									'. $alertAdminUser.'
									'.((isset($allAlert) && $formName == "manage_admin")? $allAlert : "").'							
									<form action="/'.$pageSelf.'#s-admins" method="post">
										<div class="field-ctrl">
											<label>USERNAME'.$rqd_admin.':</label>
											<input type="text" class="field"  placeholder="Type here the registered username of the administrator you want to manage " name="username" value="'.(isset($username)? $username : '').'" />
										</div>
										<div class="field-ctrl">
											<label>ACTION<span class="red">*</span>: </label>
											<select name="action" class="field"  >
												<option value="'.$adminAddOpt.'" '.(($action == $adminAddOpt)? $optionSelected : '').'>Add as an Administrator</option>
												<option value="'.$adminRemOpt.'" '.(($action == $adminRemOpt)? $optionSelected : '').'>Remove as an Administrator</option>
											</select>
										</div>
										<div class="field-ctrl btn-ctrl">
											<input  type="hidden" value="manage_admin" name="form_name" />
											<input  class="form-btn btn-success"  type="submit" value="PROCESS" name="manage_admin" />
										</div>
									</form><br/>
									<h1  id="mrl-admins"  class="page-title pan bg-gold">MANAGE REPUTATION</h1>
									'.((isset($alertMrl))? $alertMrl : "").'
									'.((isset($allAlert) &&  $formName == "manage_mrl")? $allAlert : "").'
									'.((isset($_POST["uid_mrl"]) && !$_POST["uid_mrl"])? '<span class="alert alert-danger">Please enter a username</span>' : "").'
									<form action="/'.$pageSelf.'#mrl-admins" method="post">
										<div class="field-ctrl">
											<label>USERNAME<span class="red">*</span>:</label>
											<input type="text" class="field" placeholder="Type here the registered username of the member " name="uid_mrl" value="'.((isset($_POST["uid_mrl"]))? $ENGINE->sanitize_user_input($_POST["uid_mrl"]) : "").'" />
										</div>
										<div class="field-ctrl">
											<label>REPUTATION LEVEL<span class="red">*</span><small class="prime">Ignore when using trusted action</small>: </label>
											<select name="mrl" class="field">
												'.$reputationLevelList.'
											</select>
										</div>
										<div class="field-ctrl">
											<label>ACTION<span class="red">*</span>: </label>
											<select name="mrl_action" class="field"  >
												<option value="'.$addOpt.'" '.(($action == $addOpt)? $optionSelected : '').'>Add Reputation Point(s)</option>
												<option value="'.$subtractOpt.'" '.(($action == $subtractOpt)? $optionSelected : '').'>Subtract Reputation Point(s)</option>
												<option value="'.$ultimateLvOpt.'" '.(($action == $ultimateLvOpt)? $optionSelected : '').'>Award/Confiscate Trusted Ultimate Level</option>
											</select>
										</div>
										<div class="field-ctrl btn-ctrl">
											<input  type="hidden" value="manage_mrl" name="form_name" />
											<input  class="form-btn btn-success"  type="submit" value="PROCESS" name="manage_mrl" />
										</div>
									</form>
									
									<h1  class="page-title pan bg-red">SITE ADMINISTRATORS</h1>
									<div class="base-tb-mg">								
										<button class="form-btn btn-info" data-toggle="smartToggler" data-id-targets="atab" >Toggle Table of Administrators</button>
										<div  id="atab" class="">'.$pagination_admin.$administrators.$pagination_admin.'</div>
									</div>
									
									<h1  class="page-title pan bg-gray">SITE MODERATORS</h1>
									<div class="base-t-mg">
										<button class="form-btn btn-info" data-toggle="smartToggler" data-id-targets="amods" >Toggle Table of Moderators</button>
										<div  id="amods" class="">'. $pagination_amods.$siteMods.$pagination_amods.'</div>
									</div>
								</div>
							</div>';



			}

			////////REDIRECT USER THAT HAS NO PRIVELEGE TO VIEW THE PAGE/////
			if(!$GLOBAL_isStaff)

				///////AUTHORIZE TOP LEVEL ACCESS///////////
				$ACCOUNT->authorizeTopAccess();


		}else
			$notLogged = $GLOBAL_notLogged;

		$tabLink = $SITE->buildLinearNav(array(
						"baseUrl" => $baseUrl,
						"urlList" => $urlList,										
						"active" => $tab																				
					
					));
	
		
		
		$SITE->buildPageHtml(array("pageTitle"=>$modToolsTitle.' - '.$pageTitle,
						"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$baseUrl.'">'.$modToolsTitle.'</a></li>'.$subNav),
						"pageBody"=>'
						<div class="single-base blend">
							<div class="base-ctrl">'.
								$notLogged.(isset($noViewPrivilege)? $noViewPrivilege : '').
								
								(($sessUsername && $GLOBAL_isStaff)? '

									<div class="staff-cms">'.
										$tabLink.(isset($adminManager)? $adminManager : '').

										(($tab == $scatMngrTab)? '																										
											<div class="panel panel-gold">
												<h1 class="panel-head page-title">TABLES OF CATEGORIES AND SECTIONS</h1>
												<div class="panel-body">
													<button class="form-btn btn-info base-t-mg" data-toggle="smartToggler" data-id-targets="ctab">Toggle Categories</button>
													<button class="form-btn btn-info base-t-mg" data-toggle="smartToggler" data-id-targets="stab" >Toggle Sections</button>'.
													(isset($cats)? $cats : '').$sections.'
												</div>
											</div>'

										:(($tab == $topicMngrTab)? '																			
											<div class="panel panel-bluex">
												<h1 class="panel-head page-title" id="mto">MANAGE TOPICS</h1>
												<div class="mod-tools panel-body">
													<div class="alert alert-warning">NOTE: You can only manage topics within your jurisdiction.</div>'.
													(isset($_POST[MOD_METAS['keys']['topicManager']])? $FORUM->doForumModeration(array("fRet" => true))[0] : '').'
													'.$FORUM->getModerationControls(array("isModPage" => true))[0].'																			
												</div>
											</div>														
											<div class="panel panel-red">
												<h1 id="s-top" class="panel-head page-title">TOPICS YOU MODERATE:</h1>
												<div class="panel-body">
													'.$SITE->getSearchForm(
													array(
														'url' => '/mods-activities-log', 'fieldName' => 'param', 
														'formMethod' => 'get', 'fieldLabel' => 'Search Topic Logs', 
														'fieldPH' => 'Enter the topic id', 'btnLabel' => 'Search',
														'newWindow' => true,
														'hiddenFields' => '<input type="text" name="log" value="topic">'
													)).'
													<button class="form-btn btn-info hide" data-toggle="smartToggler" >Toggle Table of Topic Activivies in your Sections</button>'.
													(isset($pagination_topics)? $pagination_topics : '').
													(isset($topicsModerated)? $topicsModerated : '').
													(isset($pagination_topics)? $pagination_topics : '').'																
												</div>
											</div>'

											
										:(($tab == $postMngrTab)? '																			
										<div class="panel panel-bluex">
											<h1 class="panel-head page-title" id="mto">MANAGE POSTS</h1>
											<div class="mod-tools panel-body">
												<div class="alert alert-warning">NOTE: You can only manage posts within your jurisdiction.</div>'.
												(isset($_POST[MOD_METAS['keys']['postManager']])? $FORUM->doForumModeration(array("fRet" => true))[0] : '').'
												'.$FORUM->getModerationControls(array("isModPage" => true))[1].'																			
											</div>
										</div>'

										:(($admin && $tab == $logMngrTab)? '										
											<div class="panel panel-gray">
												<h1 class="panel-head page-title" id="cal">CLEAR ACTIVITIES LOG</h1>
												<div class="panel-body">'.
													(isset($log_alert)? $log_alert : '').'
													<form action="/'.$pageSelf.'#cal" method="post">
														<div class="field-ctrl">
															<label >TOPIC,SECTION OR CATEGORY ID'.(isset($rqd_log)? $rqd_log : '').':</label>
															<input type="number"  min="1" class="field"  placeholder="Type here the topic or section or category id " name="lid" value="'.(isset($lid)? $lid : '').'" />
														</div>
														<div class="field-ctrl">
															<label>APPLY TO<span class="red">*</span>: </label>
															<select name="action" class="field"  >
																<option value="'.$topicLogOpt.'" '.(($action == $topicLogOpt)? $optionSelected : '').' >Topic</option>
																<option value="'.$allTopicLogOpt.'" '.(($action == $allTopicLogOpt)? $optionSelected : '').' >All Topics</option>
																<option value="'.$sectionLogOpt.'" '.(($action == $sectionLogOpt)? $optionSelected : '').' >Section</option>
																<option value="'.$allSectionLogOpt.'" '.(($action == $allSectionLogOpt)? $optionSelected : '').' >All Sections</option>
																<option value="'.$categoryLogOpt.'" '.(($action == $categoryLogOpt)? $optionSelected : '').' >Category</option>
																<option value="'.$allCategoryLogOpt.'" '.(($action == $allCategoryLogOpt)? $optionSelected : '').' >All Categories</option>
															</select>											
														</div>
														<div class="field-ctrl">
															'.$SITE->getHtmlComponent('iconic-checkbox', array('label'=>$nLogMonths, 'label2R'=>true, 'fieldName'=>'n_old', 'wrapClass'=>'prime', 'on'=>isset($monthsOldChecked)? $monthsOldChecked : '')).'
														</div>
														<div class="field-ctrl btn-ctrl">											
															<input type="hidden" value="manage_logs" name="form_name" />
															<input class="form-btn btn-success" type="submit" value="CLEAR" name="manage_logs" />
														</div>
													</form>
												</div>
											</div>'

										:(($tab == $modsMngrTab)? '								
											<div class="panel panel-violet">
												<h1 class="panel-head page-title" id="mmo">MANAGE MODERATORS</h1>
												<div class="panel-body">
													<div class="alert alert-warning">NOTE: You can only manage(add or remove) moderators within your jurisdiction.</div>'.
													(($alertUser && $formName == "manage_moderators")? $alertUser : '').'										
													<form action="/'.$pageSelf.'#mmo" method="post">
														<div class="field-ctrl">
															<label>USERNAME'.((isset($rqd) && $formName=="manage_moderators")? $rqd : '').':</label>
															<input type="text"  class="field"  placeholder="Type here the registered username of the moderator you want to manage" name="username" value="'.(isset($username)? $username : '').'" />
														</div>
														<div class="field-ctrl">
															<label>SECTION<span class="red">*</span>:</label>
															<select class="field" placeholder="Select the target section" name="section_name" >
															<optgroup label="HOMEPAGE">'.$homepageOptionGroup.'</optgroup>
															<optgroup label="CATEGORIES">'.$categoryOptionGroup.'</optgroup>
															<optgroup label="SECTIONS">'.$sectionListOptions.'</optgroup>
															</select>
														</div>
														<div class="field-ctrl">
															<label>ACTION<span class="red">*</span>: </label>
															<select name="action" class="field"  >
																	<option value="'.$sectionModAddOpt.'" '.(($action == $sectionModAddOpt)? $optionSelected : '').' >Add as moderator to section</option>
																	<option value="'.$sectionModRemOpt.'" '.(($action == $sectionModRemOpt)? $optionSelected : '').'>Remove as moderator from section</option>
																	<option value="'.$categoryModAddOpt.'" '.(($action == $categoryModAddOpt)? $optionSelected : '').' >Add as moderator to category</option>
																	<option value="'.$categoryModRemOpt.'" '.(($action == $categoryModRemOpt)? $optionSelected : '').' >Remove as moderator from category</option>
															</select>
														</div>
														<div class="field-ctrl btn-ctrl">
															<input type="hidden" value="manage_moderators" name="form_name" />
															<input class="form-btn btn-success"  type="submit" value="PROCESS" name="manage_moderators" />
														</div>
													</form>
												</div>
											</div>'.
											(($admin || $SITE->moderatedSectionCategoryHandler(array('uid'=>$sessUid,'action'=>'isMod','level'=>2)))? '
												<div class="panel panel-pink">
													<h1 id="c-mods" class="panel-head page-title">TABLE OF MODERATORS IN ALL YOUR CATEGORIES:</h1>
													<div class="panel-body">
														<button class="form-btn btn-info hide" data-toggle="smartToggler" >Toggle Table of Section Moderators</button>'.
														(isset($pagination_cat)? $pagination_cat : '').
														(isset($categModerated)? $categModerated : '').
														(isset($pagination_cat)? $pagination_cat : '').'																	
													</div>
												</div>'
											: '').'
											<div class="panel panel-brown">
												<h1 id="s-mods" class="panel-head page-title">TABLE OF MODERATORS IN ALL YOUR SECTIONS:</h1>
												<div class="panel-body">
													<button class="form-btn btn-info hide" data-toggle="smartToggler" >Toggle Table of Section Moderators</button>'.
													(isset($pagination_scat)? $pagination_scat : '').
													(isset($sectionsModerated)? $sectionsModerated : '').
													(isset($pagination_scat)? $pagination_scat : '').'																
												</div>
											</div>'

										:(($tab == $badgeMngrTab)? '													
											<div class="panel panel-orange">
												<h1 id="mbr" class="panel-head page-title">MANAGE BADGES</h1>
												<div class="panel-body">
													<div class="alert alert-danger align-l">IMPORTANT NOTE: If a badge is awarded to a user who hasn`t met the criteria or confiscated from a user who has met the criteria, the system will automatically reverse it when it recalculate badges periodically</div>'.
													(isset($badgeAlert)? $badgeAlert : '').(isset($allAlert)? $allAlert : '').'																
													<form action="/'.$pageSelf.'#mbr" method="post">
														<div class="field-ctrl">
															<label >USERNAME<span class="red">*</span>:</label>
															<input type="text" class="field '.(isset($badgeFieldErr)? $badgeFieldErr : '').'"  placeholder="Username" name="bdguname" value="'.(isset($_POST["bdguname"])? $ENGINE->sanitize_user_input($_POST["bdguname"]) : '').'" />'.((isset($_POST["manage_badges"]) && !$_POST["bdguname"])? '<b class="asterix">*</b>' : '').'
														</div>
														<div class="field-ctrl">
															<label>BADGES: </label>
															<select multiple="multiple" name="bdg[]" class="field" >'.
																(isset($badgesOpt)? $badgesOpt : '').'
															</select>
														</div>
														<div class="field-ctrl">
															<label>ACTION: </label>
															<select name="bdgaction" class="field"  >
																<option value="'.$awardOpt.'" '.(($action == $awardOpt)? $optionSelected : '').'>Award Badge</option>
																<option value="'.$confiscateOpt.'" '.(($action == $confiscateOpt)? $optionSelected : '').' >Confiscate Badge</option>
															</select>
														</div>
														<div class="field-ctrl btn-ctrlx">
															<input type="hidden" value="manage_badges" name="form_name" />
															<input class="form-btn btn-success" type="submit" value="GO" name="manage_badges" />
														</div>
													</form>
												</div>
											</div>'
											
											: ''

										)))))).$tabLink.'														
									</div>'
								: '').'
							</div>
						</div>'
		));
						
		break;

	}
	
	
	
	
	
	
	
	
	/**SITE CLEAN UP**/
	case 'site-clean-up':{
					
		$ACCOUNT->authorizeTopAccess(false);
		$option=$notLogged="";

		if($sessUsername){	
							
			if(!$GLOBAL_isAdmin)
				$noViewPrivilege = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.' Sorry You do not have enough Privilege to view this Page</span>';
			
			
			if(isset($_POST["process_delete"])){
						
				//PDO QUERY///////
		
				$sql = "DELETE FROM users WHERE USERNAME='' AND (TIME + INTERVAL 1 WEEK) <= NOW()";
				$resCount = $dbm->query($sql, 'chain')->getRowCount();
							
				$alertUser = $resCount? '<span class="alert alert-success">('.$resCount.') unfinished registration(s) older than one week has been deleted successfully.</span>'
				: '<span class="alert alert-danger">Sorry!, there are no unfinished registrations older than one week.</span>';
				
				
			}
			
			
			////CLEAR FEEDBACKS AND REPORTS///////
			
			if(isset($_POST["fback_report_form"])){
						
				$action = $_POST["action"];
				
				switch($action){
				
					case 'Clear feedbacks older than 6 months':
						$sql = "SELECT ID FROM feedbacks WHERE (TIME + INTERVAL 6 MONTH) <= NOW()";
						$clearSixMonthsOldFeedbacks = true;
						break;
				
					case 'Clear reports older than 6 months':
						$sql = "SELECT ID FROM reported_posts WHERE (TIME + INTERVAL 6 MONTH) <= NOW()";
						$clearSixMonthsOldReports = true;
						break;
				
					case 'Clear feedbacks older than 1 year':
						$sql = "SELECT ID FROM feedbacks WHERE (TIME + INTERVAL 1 YEAR) <= NOW()";
						$clearOneYearOldFeedbacks = true;
						break;
				
					case 'Clear reports older than 1 year':
						$sql = "SELECT ID FROM reported_posts WHERE (TIME + INTERVAL 1 YEAR) <= NOW()";
						$clearOneYearOldReports = true;
						break;
				
				
				}
				
				
				if(isset($sql) && $dbm->query($sql, true) && ($resCount = $dbm->getRecordCount())){
							
					if(isset($clearSixMonthsOldFeedbacks)){
					
						///PDO QUERY///
			
						$sql = "DELETE FROM feedbacks WHERE (TIME + INTERVAL 6 MONTH ) <= NOW()";
						$dbm->query($sql);
							
						$alertUserF2 = '<span class="alert alert-success">('.$resCount.') feedback(s) older than 6 months has been deleted successfully.</span>';
				
					}elseif(isset($clearSixMonthsOldReports)){
						
						////PDO QUERY/////
			
						$sql = "DELETE FROM reported_posts WHERE (TIME + INTERVAL 6 MONTH) <= NOW()";
						$dbm->query($sql);
							
						$alertUserF2 = '<span class="alert alert-success">('.$resCount.') report(s) older than 6 months has been deleted successfully.</span>';
				
					}elseif(isset($clearOneYearOldFeedbacks)){
								
						///PDO QUERY//
			
						$sql = "DELETE FROM feedbacks WHERE (TIME + INTERVAL 1 YEAR) <= NOW()";
						$dbm->query($sql);
							
						$alertUserF2 = '<span class="alert alert-success">('.$resCount.') feedback(s) older than 1 year has been deleted successfully.</span>';
				
					}elseif(isset($clearOneYearOldReports)){
								
						///PDO QUERY////////
			
						$sql =  "DELETE FROM reported_posts WHERE (TIME + INTERVAL 1 YEAR) <= NOW()";
						$dbm->query($sql);
												
						$alertUserF2 = '<span class="alert alert-success">('.$resCount.') report(s) older than 1 year has been deleted successfully.</span>';
				
					}
					
					
				}else{
					
					if(isset($clearSixMonthsOldFeedbacks))
						$alertUserF2 = 'Sorry!, there are no feedbacks older than 6 months.';
											
					if(isset($clearSixMonthsOldReports))
						$alertUserF2 = 'Sorry!, there are no reports older than 6 months.';
											
					if(isset($clearOneYearOldFeedbacks))
						$alertUserF2 = 'Sorry!, there are no feedbacks older than 1 year.';	
										
					if(isset($clearOneYearOldReports))
						$alertUserF2 = 'Sorry!, there are no reports older than 1 year.';
					
					$alertUserF2 = '<span class="alert alert-danger">'.$alertUserF2.'</span>';
					
				}
			
			}
			
			
		}else
			$notLogged = $GLOBAL_notLogged;
		

		$SITE->buildPageHtml(array("pageTitle"=>'Site Clean Up',			
			"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">Site Clean Up</a></li>'),
			"pageBody"=>'
				<div class="single-base blend">
					<div class="base-ctrl">
						<div class="panel panel-limex">
							<h1 class="panel-head page-title">SITE CLEAN UPS</h1>	
							<div class="panel-body">
								'.$notLogged.(isset($noViewPrivilege)? $noViewPrivilege : '').

								(($GLOBAL_isAdmin)?	'								
									<div class="equal-base panels-stacked panels-sqd">		
										<div class="panel panel-gray">								
											<h1 class="panel-head">CLEAR UNFINISHED REGS</h1>						
											<div class="panel-body">'.
												(isset($alertUser)? $alertUser : '').'
												<label class="prime">Older than one week and uncompleted</label>
												<button class="btn btn-danger" data-toggle="smartToggler" data-id-targets="dun">CLEAR</button>
												<div id="dun" class="red modal-drop hide has-close-btn">							
													<form method="post" action="/site-clean-up" >	
														<p>ARE YOU SURE</p>
														<input class="btn btn-danger" type="submit" name="process_delete" value="CLEAR" />								
														<button type="button" class="btn close-toggle">CLOSE</button>
													</form>							
												</div>
											</div>
										</div>
										<div class="panel panel-gray">						
											<h1 class="panel-head" id="f2">CLEAR FEEDBACKS AND REPORTS</h1>						
											<div class="panel-body">'.
												(isset($alertUserF2)? $alertUserF2 : '').'																
												<br/><br/><form method="post" class="inline-form" action="/site-clean-up#f2">
													<div class="field-ctrl">
														<select name="action" class="field">
															<option '.(($option == 1)? 'selected' : '').'>Clear feedbacks older than 6 months</option>
															<option  '.(($option == 2)? 'selected' : '').'>Clear reports older than 6 months</option>
															<option  '.(($option == 3)? 'selected' : '').'>Clear feedbacks older than 1 year</option>
															<option  '.(($option == 4)? 'selected' : '').'>Clear reports older than 1 year</option>
														</select>
													</div>
													<div class="field-ctrl btn-ctrl">
														<button type="button" class="btn btn-danger" data-toggle="smartToggler" data-id-targets="clr">CLEAR</button>
													</div>							
													<div id="clr" class="red modal-drop hide has-close-btn">
														<p>ARE YOU SURE</p>
														<input class="btn btn-danger" type="submit" name="fback_report_form" value="PROCESS" />
														<button type="button" class="btn close-toggle">CLOSE</button>
													</div>							
												</form>  
											</div>
										</div>
									</div>'
								: '').'
							</div>
						</div>
					</div>
				</div>'
		));
						
		break;

	}
	
	
	
	
	
	
	
	/**LOAD HOME**/
	case 'load-home':{
					
		$bannedUser=$bannedTopic=$fpSections=$general=$entertainment=$sciTech=
		$eliteTopics=$generalExtended=$alertUser=$entertainmentExtended=$sciTechExtended=$generalExtendedForMob=
		$entertainmentExtendedForMob=$sciTechExtendedForMob=$genDesc=$entDesc=$sciTechDesc="";

		
		//////CHECK IF A MODS BANNED USER WAS LOGGED OUT AND REDIRECTED HERE//////

		if(isset($_COOKIE[$K="mods_banned"])){

			$bannedUser = $ENGINE->sanitize_user_input($_COOKIE[$K]);	
			$ENGINE->set_cookie($K, '', 1);
			$alertUser .= '<div class="alert alert-danger has-close-btn">
								<strong> 
									<span class="cyan">'.$bannedUser.'</span>, You have been placed under a moderator\'s ban and automatically signed out of your account
								</strong>
							</div>';	
			
		}

		//////CHECK IF A SPAM BANNED USER WAS LOGGED OUT AND REDIRECTED HERE//////

		elseif(isset($_COOKIE[$K="spam_banned"])){

			$bannedUser = $ENGINE->sanitize_user_input($_COOKIE[$K]);
			$bannedUid = $ACCOUNT->memberIdToggle($bannedUser, true);	
			$ENGINE->set_cookie($K, '', 1);

			////PDO QUERY///////////

			$sql = "SELECT TOPIC_ID, BAN_DURATION, TIME_BANNED FROM spam_controls 
					WHERE USER_ID=? AND SPAM_COUNTER >=5 AND BAN_STATUS=1 ";
			$valArr = array($bannedUid);
			$stmt = $dbm->doSecuredQuery($sql, $valArr);

			while($row = $dbm->fetchRow($stmt)){
				
				$tid = $row["TOPIC_ID"];
				$timeBanned = $row["TIME_BANNED"];
				$banDur = $row["BAN_DURATION"];
				list($sbd, $bh) = $ENGINE->time_difference($banDur, $timeBanned, true);
				$bannedTopic .= '-> '.$ENGINE->sanitize_slug($SITE->getThreadSlug($tid), array('ret'=>'url', 'urlText'=>$SITE->topicIdToggle($tid), 'slugSanitized'=>true)).'<br/>';

			}

			if($bannedTopic)
				$alertUser .= '<div class="alert alert-danger has-close-btn">
									<strong> <span class="cyan">'.$bannedUser.'</span>, You have been automatically signed out of your account
									and	given a '.$bh.' hours ban for excessive spamming in the following topic(s):<br/>'.$bannedTopic.' </strong>
								</div>';	
			
		}


		if(isset($_SESSION[$K="was_banned"]) && $sessUsername){
			
			$alertUser .= '<div  class="alert alert-info has-close-btn box-close-target">
								<b><h2 class="">Welcome Back '.$sessUsername.' !!!<br/> You ban has been lifted</h2> We implore you to always comply with our community rules and 
								to refrain from any act that may cause disruption. Please note that frequent violation of our policies will attract several bans which may in turn cause 
								you to permanently lose access to your account</b><br/>
								<a role="button" class="btn btn-info btn-sm box-close" href="#" >ACKNOWLEDGED</a>						
							</div>';

			unset($_SESSION[$K]);

		}


		//////POPULATE THE HOME PAGE WITH CATEG_NAME AND SECTIONS AND RANDOM TOPICS FROM ALL SECTIONS ///////

		/////////PDO QUERY////////

		//$sql = "SELECT s.ID,SECTION_NAME,SECTION_SLUG,SECTION_DESC,CATEG_ID,CATEG_NAME,CATEG_SLUG,CATEG_DESC FROM sections s JOIN categories c ON s.CATEG_ID = c.ID WHERE PARENT_SECTION = '' AND FP_VISIBLE = 1 ORDER BY SECTION_NAME ";
		$sql = "SELECT s.ID, SECTION_NAME, SECTION_SLUG, SECTION_DESC, CATEG_ID, CATEG_NAME, CATEG_SLUG, CATEG_DESC ".$SITE->getPopularitySubQuery()." FROM sections s JOIN categories c ON s.CATEG_ID = c.ID WHERE (PARENT_SECTION = '' AND CATEG_ID != 0 AND FP_VISIBLE = 1) ORDER BY POPULARITY DESC";
		
		$stmt = $dbm->query($sql);

		$gen =$ent=$sci=1;

		while($row = $dbm->fetchRow($stmt)){
			
			$sid = $row["ID"];
			$cid = $row["CATEG_ID"];
			$categName = strtolower($row["CATEG_NAME"]);
			$categNameU = strtoupper($categName);
			$categDesc = $row["CATEG_DESC"];
			$categSlug = $row["CATEG_SLUG"];
			$sectionName = $row["SECTION_NAME"];
			$sectionSlug = $row["SECTION_SLUG"];	
			$sectionDesc = $row["SECTION_DESC"];
			$section = '<span class="pill-followerX"><a href="/'.$sectionSlug.'" title="'.$sectionDesc.' - '.$sid.'"  class="links">'.$sectionName.'</a></span> | ';
			
			if($categName == "general"){
			
				$genName = $categNameU;	
				$genDesc = $categDesc;	
				$genCategSlug = $categSlug;
				$genCid = $cid;			
				($gen > 10)? ($generalExtended .= $section) : ($general .= $section);
				$gen++;			
			
			}elseif($categName == "entertainment"){
			
				$entName = $categNameU;	
				$entDesc = $categDesc;	
				$entCategSlug = $categSlug;
				$entCid = $cid;				
				($ent > 10)? ($entertainmentExtended .= $section) : ($entertainment .= $section);
				$ent++;	
			
			}elseif($categName == "science & technology"){
			
				$sciTechName = $categNameU;
				$sciTechDesc = $categDesc;
				$sci_techCategSlug = $categSlug;
				$sciCid = $cid;							
				($sci > 10)? ($sciTechExtended .= $section) : ($sciTech .= $section);
				$sci++;
			
			}
			
		}
		
		$fTglDatas = ' data-target-prev="true"  data-target-inline="inline" data-toggle-attr="title|view less$text|&#8212;" ';
		$viewMore = '<span class="fp-plus" data-toggle="smartToggler" '.$fTglDatas.' title="view more">+</span>';
			
		if($generalExtended)
			$generalExtendedForMob = '<span class="'.($cl1='fp-more').'">'.$generalExtended.'</span>'.$viewMore;
			
		if($entertainmentExtended)
			$entertainmentExtendedForMob = '<span class="'.$cl1.'">'.$entertainment_more.'</span>'.$viewMore;
			
		if($sciTechExtended)
			$sciTechExtendedForMob = '<span class="'.$cl1.'">'.$sciTechExtended.'</span>'.$viewMore;


		$general = '<div class="col-lg-w-4 adamx"><div class="'.($cl2='fp-cat-base').'"><a title="'.$genDesc.' - '.$genCid.'" href="/'.$genCategSlug.'" class="'.($cl3='links bold').'">'.$genName.'</a> >> '.$general.'<span class="'.($cl4='fp-screen-dpn').'">'.$generalExtended.'</span>'.$generalExtendedForMob.'</div></div>';
		$entertainment = '<div class="col-lg-w-3 evex"><div class="'.$cl2.'"><a title="'.$entDesc.' - '.$entCid.'" href="/'.$entCategSlug.'" class="'.$cl3.'">'.$entName.' </a> >> '.$entertainment.'<span class="'.$cl4.'">'.$entertainmentExtended.'</span>'.$entertainmentExtendedForMob.'</div></div>';
		$sciTech = '<div class="col-lg-w-3 evex"><div class="'.$cl2.'"><a title="'.$sciTechDesc.' - '.$sciCid.'" href="/'.$sci_techCategSlug.'" class="'.$cl3.'">'.$sciTechName.'</a> >> '.$sciTech.'<span class="'.$cl4.'">'.$sciTechExtended.'</span>'.$sciTechExtendedForMob.'</div></div>';


		$fpSections = $sciTech.$general.$entertainment;
		
		$fpSectionListForMob = '<div class="mob-platform-dpn"><nav class="nav-base">
								<ul class="nav nav-tabs justified-center tab-basicx justified justified-bomx" data-open-active-mob="true">			
									<li><a class="" data-toggle="tab-tab">Sci & Tech</a></li>
									<li><a class="" data-default-tab="true" data-toggle="tab-tab">General</a></li>
									<li><a class="" data-toggle="tab-tab">Entertainment</a></li>
								</ul>
								<div class="tab-contents sides-padless" data-animate="">  				
									<div class="tab-content">'.$sciTech.'</div>	
									<div class="tab-content">'.$general.'</div>	
									<div class="tab-content">'.$entertainment.'</div>	
								</div>
							</nav></div>';
		
		////////GENERATE FRONTPAGE TOPICS///

		$eliteTopicsArr = $FORUM->getEliteTopics();

		if(isset($eliteTopicsArr[$K="topics"]))
			$eliteTopics = $eliteTopicsArr[$K];
			
		if(isset($eliteTopicsArr[$K="pagination"]))
			$pagination_topics = $eliteTopicsArr[$K];
			
		if(isset($eliteTopicsArr[$K="pageId"]))
			$pageId = $eliteTopicsArr[$K];
			
		if(isset($eliteTopicsArr[$K="totalPage"]))
			$totalPage = $eliteTopicsArr[$K];
		
		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(HOMEPAGE_SID);
		
		$contactMods = '<nav class="nav-base no-pad"><ul class="nav nav-list justified-center"><li><a href="/contact-moderators?sid='.HOMEPAGE_SID.'" class="links" >Contact Elite moderators</a></li></ul></nav>';
		
		$SITE->buildPageHtml(array("pageTitle"=>SITE_SLOGAN, "pageTitlePrependDomain"=>true,
				"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/" >Home</a></li>'),
				"pageBody"=>'												
					<div class="base-ctrl">
						<div class="" >'.									
							(isset($unactivatedUserPoster)? $unactivatedUserPoster : '')
							.(isset($alertUser)? $alertUser : '').'									
							<h1 class="fp-caption page-title pan prime-sc1">'.strtoupper($siteName).' <small class="dsk-platform-dpn sky-blue font-normalized"><b data-auto-rewrite="true" data-rewrite-once="false">'.SITE_SLOGAN.' |'.$siteName.'! Where the world converge!</b></small> </h1>
							<div class="equal-base fp-block dsk-platform-dpn">
								'.$fpSections.'
							</div>
							'.$fpSectionListForMob.'
						</div>
						<div class="single-base blend-top bg-img2">
							'.$pageTopAds.'										
							<div class="row row-pad base-t-pad">
								<div class="'.$leftWidgetClass.' box-shadowx col-lg-m">
									<div class="base-border">												
										<h2 class="page-title">'.$SITE->getBgImg(array('file'=>$mediaRootFav.'star_c.png')).'Elite Topics<br/>'.(isset($pagination_topicsx)? '(page <span class="cyan">'.$pageId.'</span> of '.$totalPage.')' : '').'</h2>'.
										$eliteTopics.((isset($pagination_topics) && $pagination_topics)? $pagination_topics.'<hr/>' : '').
										$contactMods.'																		
									</div>						
								</div>'.$rightWidget.'																																	
								<div class="clear">
									<div style="margin-top:40px;" class="col-lg-w-5-pull col-sm-w-4x-pull">
										'.$ACCOUNT->getBirthdays().$ACCOUNT->getBirthdays(false).'
									</div>
								</div>
							</div>
							<div class="">
								'.$pageBottomAds.'																							
							</div>
							<div class="">'.					
								$SITE->collectSiteTraffic().$SITE->displaySiteTraffic("homepage").
								$SITE->getSocialHandlePlugins().'																					
							</div>			
						</div>			
					</div>'
		));
			
		break;
			
	}
	
	
	
	
	
	
	
	
	
	/**SEARCH**/
	case 'search':{

		$cats=$pagination=$totalRecordsView=$messages=$categories=$searchQuery=$sections=$searchCnd=$pageId=$action=$totalPage=$tJoinSubQry=
		$imgPref=$protectedTopicsFilter=$topicNamesAndAuthorsFilter=$hiddenPostsFilter=$lockedPostsFilter=$imgFilterQry=$imgFilterCountQry=$imgFilterQryCnd="";

		////DEFAULT SEARCH QUERY (GET)////////
		$searchQuery = '';
		$defaultFilter = 'dna';

		/////HANDLE FOR SEARCH QUERIES/////////

		////////////COLLECT SEARCH DATAS PASSED VIA PAGINATION URL//////////////

		if(isset($_POST[$K="sq"]))
			$searchQuery = $_POST[$K];
		
		elseif(isset($_GET[$K]))
			$searchQuery = $_GET[$K];
		
		elseif(isset($_GET[$K="s"]))
			$searchQuery = $_GET[$K];
		
		$searchQuery = $ENGINE->sanitize_user_input($searchQuery);

		if(isset($_POST[$K="img_pref"]))
			$imgPref = $_POST[$K];
		
		elseif(isset($_GET[$K]))
			$imgPref = $_GET[$K];
		
		$imgPref = $ENGINE->sanitize_user_input($imgPref);

		if(isset($_POST[$K="topic_n_author"]))
			$topicNamesAndAuthorsFilter = $_POST[$K];
		
		elseif(isset($_GET[$K]))
			$topicNamesAndAuthorsFilter = $_GET[$K];
		
		$topicNamesAndAuthorsFilter = $ENGINE->sanitize_user_input($topicNamesAndAuthorsFilter);

		if(isset($_POST[$K="hidden_post"]))
			$hiddenPostsFilter = $_POST[$K];

		elseif(isset($_GET[$K]))
			$hiddenPostsFilter = $_GET[$K];

		$hiddenPostsFilter = $ENGINE->sanitize_user_input($hiddenPostsFilter);

		if(isset($_POST[$K="locked_post"]))
			$lockedPostsFilter = $_POST[$K];

		elseif(isset($_GET[$K]))
			$lockedPostsFilter = $_GET[$K];

		$lockedPostsFilter = $ENGINE->sanitize_user_input($lockedPostsFilter);

		if(isset($_POST[$K="protected_topic"]))
			$protectedTopicsFilter = $_POST[$K];

		elseif(isset($_GET[$K]))
			$protectedTopicsFilter = $_GET[$K];

		$protectedTopicsFilter = $ENGINE->sanitize_user_input($protectedTopicsFilter);

		if(isset($_POST[$K="ca"]))
			$categories = $_POST[$K];

		elseif(isset($_GET[$K]))
			$categories = $_GET[$K];

		$categories = $ENGINE->sanitize_user_input($categories);

		if(isset($_POST[$K="se"]))
			$sections = $_POST[$K];

		elseif(isset($_GET[$K]))
			$sections = $_GET[$K];

		$sections = $ENGINE->sanitize_user_input($sections);

		////DEFAULT SEARCH QUERY////////
		if(!$searchQuery) $searchQuery = 'euroadams economy education politics technology finance wealth money';

		//////////FALL BACKS IF LINK IS ALTERED INAPPROPRIATELY////////////////////

		if(!$categories)
			$categories = 'All Categories(1)';

		if(!$sections)
			$sections = 'All Sections(1)';

		if(!$imgPref)
			$imgPref = 'all';

		if(!$hiddenPostsFilter)
			$hiddenPostsFilter = $defaultFilter;

		if(!$lockedPostsFilter)
			$lockedPostsFilter = $defaultFilter;

		if(!$protectedTopicsFilter)
			$protectedTopicsFilter = $defaultFilter;

		if(!$topicNamesAndAuthorsFilter)
			$topicNamesAndAuthorsFilter = $defaultFilter;

		////////////////GENERATE CATEGORIES DROP DOWN MENU/////////

		///////////PDO QUERY/////
			
		$sql = "SELECT CATEG_NAME FROM categories ORDER BY CATEG_NAME ";
		$stmt = $dbm->query($sql, true);

		while($catName = $stmt->fetchColumn()){
					
			$cats .= '<option '.(($categories == $catName)? 'selected' : '').' >'.$catName.'</option>';	
				
		}

		$cats = '<option selected>All Categories('.$dbm->getRecordCount().')</option>'.$cats;
								
								
		////////GENERATE SECTIONS DROP DOWN MENU////////

		if(isset($_POST[$K="cat"])){
			
			$cat = $_POST[$K];
			
			$sec = isset($_POST[$K="sec"])? $_POST[$K] : '';			
			
			$catId = $SITE->categoryIdToggle($cat);
			
			$xceptions = getExceptionParams('sidsnosearch', 0);
			
			//////IF ALL CATEGORIES//////
				
			if(preg_match("#^(all categories\([0-9]+\))#i", $cat)){
						
				///////////PDO QUERY////
			
				$sql = "SELECT SECTION_NAME FROM sections WHERE ID NOT IN(".$xceptions.") ORDER BY SECTION_NAME";
				$stmt = $dbm->query($sql, true);
				
				while($sectionName = $stmt->fetchColumn()){
					
					$sections .= '<option '.(($sec == $sectionName)? 'selected' : '').' >'.$sectionName.'</option>';
					
				}
				
				$sections = '<option selected>All Sections('.$dbm->getRecordCount().')</option>'.$sections;
								

				echo $sections; /////echo it for ajax to return the results//////////
							
			}else{
						
				///////////PDO QUERY///////
				
				$sql =  "SELECT SECTION_NAME FROM sections WHERE CATEG_ID = ? AND ID NOT IN(".$xceptions.") ORDER BY SECTION_NAME";
				$valArr = array($catId);
				$stmt = $dbm->doSecuredQuery($sql, $valArr, true);
					
				while($sectionName = $stmt->fetchColumn()){
								
					$sections .= '<option '.(($sec == $sectionName)? 'selected' : '').' >'.$sectionName.'</option>';			
								
				}

				$sections = '<option selected >All Sections('.$dbm->getRecordCount().')</option>'.$sections;
							

				echo $sections; ///////////echo it for ajax to return the results//////////


			}

			exit(); /////TERMINATE THE SCRIPT AFTER GENERATING SECTION DROPDOWN OPTION FROM AJAX CALL////
								
		}
		
		$tJoinSubQry = ' JOIN topics t ON posts.TOPIC_ID = t.ID ';
			
		/************GET ALL THE KEYWORDS IN THE SEARCH QUERY THAT*************/	
		$valArr=$valArrMerged=array();
		$keywordsArr = explode(" ", $searchQuery);
	
		foreach($keywordsArr as $keyword){
			
			if(mb_strlen($keyword) <= 2 || $SITE->inSearchKeywordExceptions($keyword))
				continue;
			
			$keyword = $ENGINE->sanitize_user_input($keyword);	
			
			if(!(strtolower($topicNamesAndAuthorsFilter) == 'yes')){	
			
				$searchCnd .= '(MESSAGE LIKE ? OR TOPIC_ID IN(SELECT ID FROM topics WHERE TOPIC_NAME LIKE ?)
							 OR POST_AUTHOR_ID IN(SELECT ID FROM users WHERE USERNAME LIKE ?)) OR ';
				
				for($i=1; $i <= 3; $i++)
					$valArr[] = "%".$keyword."%";	
			
			}else{
			
				$searchCnd .= '(TOPIC_ID IN(SELECT ID FROM topics WHERE TOPIC_NAME LIKE ?)
							 OR POST_AUTHOR_ID IN(SELECT ID FROM users WHERE USERNAME LIKE ?)) OR ';
				
				for($i=1; $i <= 2; $i++)
					$valArr[] = "%".$keyword."%";
			}
			
		}

		if($searchCnd)
			$searchCnd = '('.trim($searchCnd, " OR ").')';
		
		$qstrKeyValArr = array('sq'=>$searchQuery,'ca'=>$categories,'se'=>$sections,'img_pref'=>$imgPref,'otp'=>$topicNamesAndAuthorsFilter);	
			
		if($GLOBAL_isStaff){	
			
			$qstrKeyValArr['locked_post'] = $lockedPostsFilter; 
			$qstrKeyValArr['hidden_post'] = $hiddenPostsFilter;	
			$qstrKeyValArr['protected_topic'] = $protectedTopicsFilter;	
			
		}	
			
		$urlhash = 'ptab';
		$pageUrl = 'search';
		
		//////////HANDLE FOR IMAGE SEARCH PREFERENCE //////////
		$esc = '\\\\';
		$imgRegex = '"('.$esc.'.JPG|'.$esc.'.JPEG|'.$esc.'.JPE|'.$esc.'.PNG|'.$esc.'.GIF|'.$esc.'.TIFF|'.$esc.'.TIF|'.$esc.'.SVG|'.$esc.'.SVGZ|'.$esc.'.ICO|'.$esc.'.BMP)"';	
			
		if($imgPref){	
			
			$imgFilterQry = $imgFilterCountQry = " (SELECT COUNT(*) FROM post_uploads up WHERE (posts.ID=up.POST_ID AND up.FILE RLIKE ".$imgRegex.") OR posts.MESSAGE RLIKE ".$imgRegex.") AS IMAGES "; 	
			
		}	
			
		switch($imgPref){		
																
			case 'yes': $imgFilterQryCnd = ' HAVING IMAGES >= 1 '; break;		
							
			case 'no': $imgFilterQryCnd = ' HAVING IMAGES = 0 '; break;		
			
			default: $imgFilterQry=$imgFilterCountQry='';	
			
		}	
			
		switch($hiddenPostsFilter){	
																	
			case 'yes': $searchCnd .= ' AND posts.HIDDEN != 0 '; break;	
								
			case 'no': $searchCnd .= ' AND posts.HIDDEN = 0 '; break;	
			
		}	
			
		switch($lockedPostsFilter){		
																
			case 'yes': $searchCnd .= ' AND posts.LOCKED != 0 '; break;		
							
			case 'no': $searchCnd .= ' AND posts.LOCKED = 0 '; break;	
			
		}
		
		$protectionTopicRegex = '"LV-"'; 
			
		switch($protectedTopicsFilter){		
																
			case 'yes': $searchCnd .= ' AND PROTECTION_LEVEL RLIKE '.$protectionTopicRegex; break;		
							
			case 'no': $searchCnd .= ' AND PROTECTION_LEVEL NOT RLIKE '.$protectionTopicRegex; break;
			
		}
		
		$searchClause = $searchCnd.$imgFilterQryCnd;
		$searchCnd = ' WHERE '.$searchClause;
		
		if($searchQuery && (mb_strlen($searchQuery) >= 3) && $categories && $sections){

			$sectionId = $SITE->sectionIdToggle($sections);
			$categoryId = $SITE->categoryIdToggle($categories);
			$hideImgs = ($imgPref == "all*");
			
			///////IF ALL CATEGORIES ARE SELECTED//////////////
			
			if(preg_match("#^(all categories\([0-9]+\))#i", $categories)){
				
				///////IF ALL SECTIONS ARE SELECTED////////
				
				if(preg_match("#^(all sections\([0-9]+\))#i", $sections)){
						
					//PDO QUERY/////////			
					$sql =  $imgFilterQry? "(SELECT ".$imgFilterCountQry." FROM posts ".$tJoinSubQry.$searchCnd.") tmp"
					: "posts ".$tJoinSubQry.$searchCnd;
					
					$sql = "SELECT COUNT(*) FROM ".$sql;
					$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();

					if($totalRecords ){

						$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'qstrKeyVal'=>$qstrKeyValArr,'useFlat'=>false,'qstrEncode'=>true,'hash'=>'ptab'));						
						$pagination = $paginationArr["pagination"];
						$totalPage = $paginationArr["totalPage"];
						$perPage = $paginationArr["perPage"];
						$startIndex = $paginationArr["startIndex"];
						$pageIdOut = $paginationArr["pageId"];

						//////////END OF PAGINATION//////
					

						///////GET THE FOUND RESULTS/////////
			
			
						///////////PDO QUERY////////
			
						$sql =  $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'uniqueColumns' => $imgFilterQry, 'filterCnd' => $searchClause, 'orderBy' => 'posts.TIME DESC'));
						
						///////////DISPLAY THE POSTS/////
						list($messages) = $FORUM->loadPosts($sql, $valArr, array('type'=>'search', 'highlighKeywords'=>$searchQuery, 'hideImgs'=>$hideImgs));
						
		   
					}
							
				}	
				////IF ALL CATEGORIES ARE SELECTED AND TOGETHER WITH SPECIFIC SECTION /////
				else{
					
					$valArrMerged = array_merge(array($sectionId),$valArr);			
					////PDO QUERY///////
					
					$sql =  $imgFilterQry? "(SELECT ".$imgFilterCountQry." FROM posts ".$tJoinSubQry." AND t.SECTION_ID=? ".$searchCnd.") tmp"
					: "posts ".$tJoinSubQry." AND t.SECTION_ID=? ".$searchCnd;
					
					$sql = "SELECT COUNT(*) FROM ".$sql;						
					$valArr = $valArrMerged;
					$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
					
					if($totalRecords){

						/**********CREATE THE PAGINATION**********/											
						$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'qstrKeyVal'=>$qstrKeyValArr,'useFlat'=>false,'qstrEncode'=>true,'hash'=>'ptab'));					
						$pagination = $paginationArr["pagination"];
						$totalPage = $paginationArr["totalPage"];
						$perPage = $paginationArr["perPage"];
						$startIndex = $paginationArr["startIndex"];
						$pageIdOut = $paginationArr["pageId"];

						////END OF PAGINATION////////
					
						///////GET THE FOUND RESULTS/////////
			
						///////////PDO QUERY///////
			
						$sql =  $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'topicsTableJoinCnd' => 'posts.TOPIC_ID = '.($t='topics').'.ID AND '.$t.'.SECTION_ID=?', 'uniqueColumns' => $imgFilterQry, 'filterCnd' => $searchClause, 'orderBy' => 'posts.TIME DESC'));
					
						///////////DISPLAY THE POSTS/////
						list($messages) = $FORUM->loadPosts($sql, $valArrMerged, array('type'=>'search', 'highlighKeywords'=>$searchQuery, 'hideImgs'=>$hideImgs));
						
					}		

				}
				
			}
			////////////IF A SPECIFIC CATEGORY IS SELECTED//////////
			else{
				
				//////IF ALL SECTIONS IN A SPECIFIC CATEGORY//////
				if(preg_match("#^(all sections\([0-9]+\))#i", $sections)){
							
					$valArrMerged = array_merge(array($categoryId),$valArr);			
					///////////PDO QUERY///////
			
					$sql = $imgFilterQry? "(SELECT ".$imgFilterCountQry." FROM posts ".$tJoinSubQry." JOIN sections s ON s.ID = t.SECTION_ID AND  s.CATEG_ID=? ".$searchCnd.") tmp"
					: "posts ".$tJoinSubQry." JOIN sections s ON s.ID = t.SECTION_ID AND  s.CATEG_ID=? ".$searchCnd;
					
					$sql = "SELECT COUNT(*) FROM ".$sql;
					$valArr = $valArrMerged;
					$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();

					if($totalRecords ){

						/**********CREATE THE PAGINATION***********/							
						$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'qstrKeyVal'=>$qstrKeyValArr,'useFlat'=>false,'qstrEncode'=>true,'hash'=>'ptab'));						
						$pagination = $paginationArr["pagination"];
						$totalPage = $paginationArr["totalPage"];
						$perPage = $paginationArr["perPage"];
						$startIndex = $paginationArr["startIndex"];
						$pageIdOut = $paginationArr["pageId"];

						///////END OF PAGINATION///////

						/////GET THE FOUND RESULTS///////
			
						///////////PDO QUERY///////
						
						$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'sectionsTableJoinCnd' => ($s='sections').'.ID = '.($t='topics').'.SECTION_ID AND '.$s.'.CATEG_ID=?', 'uniqueColumns' => $imgFilterQry, 'filterCnd' => $searchClause, 'orderBy' => 'posts.TIME DESC'));
					
						///////////DISPLAY THE POSTS/////
						list($messages) = $FORUM->loadPosts($sql, $valArrMerged, array('type'=>'search', 'highlighKeywords'=>$searchQuery, 'hideImgs'=>$hideImgs));
						
					}				
				}
				////IF A SPECIFIC CATEGORY AND A SPECIFIC SECTION IS SELECTED//////
				else{
					
					$valArrMerged = array_merge(array($categoryId,$sectionId),$valArr);
					///////////PDO QUERY/////////
			
					$sql = $imgFilterQry? "(SELECT ".$imgFilterCountQry." FROM posts ".$tJoinSubQry." JOIN sections s ON s.ID = t.SECTION_ID AND s.CATEG_ID=? AND t.SECTION_ID=? ".$searchCnd.") tmp"
					: "posts ".$tJoinSubQry." JOIN sections s ON s.ID = t.SECTION_ID AND s.CATEG_ID=? AND t.SECTION_ID=? ".$searchCnd;
					
					$sql = "SELECT COUNT(*) FROM ".$sql;
					$valArr = $valArrMerged;
					$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();

					if($totalRecords){
										
						/**********CREATE THE PAGINATION*************/
						$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'qstrKeyVal'=>$qstrKeyValArr,'useFlat'=>false,'qstrEncode'=>true,'hash'=>'ptab'));					
						$pagination = $paginationArr["pagination"];
						$totalPage = $paginationArr["totalPage"];
						$perPage = $paginationArr["perPage"];
						$startIndex = $paginationArr["startIndex"];
						$pageIdOut = $paginationArr["pageId"];

						////////END OF PAGINATION////////

						////////GET THE FOUND RESULTS//////
				
						///////////PDO QUERY/////
						
						$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'sectionsTableJoinCnd' => ($s='sections').'.ID = '.($t='topics').'.SECTION_ID AND '.$s.'.CATEG_ID=? AND '.$t.'.SECTION_ID=?', 'uniqueColumns' => $imgFilterQry, 'filterCnd' => $searchClause, 'orderBy' => 'posts.TIME DESC'));
						
						///////////DISPLAY THE POSTS/////
						list($messages) = $FORUM->loadPosts($sql, $valArrMerged, array('type'=>'search', 'highlighKeywords'=>$searchQuery, 'hideImgs'=>$hideImgs));
						
					}			
			
				}
			
			
			}	
			
			$hideImgs? ($messages = '<div class="search-pim-ctrl">'.$messages.'</div>') : '';
			
		}else{
			
			if(isset($_POST["sq"])){
				
				if(mb_strlen($searchQuery) < 3)
					$nullFields = '<span class="alert alert-danger">Please enter at least three(3) characters  search keyword!</span>';			
			
				elseif(!$searchQuery)
					$nullFields = '<span class="alert alert-danger">Please enter a search keyword!</span>';		
			
			}elseif($searchQuery && mb_strlen($searchQuery) < 3)
				$nullFields = '<span class="alert alert-danger">Please enter at least three(3) characters  search keyword!</span>';		
			
			
		}

		$linkSearchQryToGoogle = '<p class="black">Not Satisfied with your search result? Please try <a class="links" target="_blank" href="http://www.google.com/search?q='.urlencode($searchQuery).'"> Google Search </a></p>';

		$totalRecordsView = '<span class="alert alert-success">('.$totalRecords.') result'.(($totalRecords > 1)? 's' : '').' found for "<span class="search-identifierx">'.$searchQuery.'</span>"</span>'.$linkSearchQryToGoogle;


		if($messages != "")
			$messages .= $linkSearchQryToGoogle;

		list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $SITE->getWidget(ELITE_SID);

		$SITE->buildPageHtml(array("pageTitle"=>'Search',
			"pageBodyOnload"=>'initialize_sections(\''.$sections.'\');',
			"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="search">Search</a></li>'),
			"pageBody"=>'
				<div class="single-base blend-top">
					<div class="base-ctrl">
						<div class="base-rad" >
							<h1 class="page-title pan bg-limex">SEARCH</h1>
							<p>'.(isset($nullFields)? $nullFields : '').'</p>
							<div class="base-pad">
								<form class="inline-form" method="post" action="/search">
									<div class="field-ctrl col-sm-w-7">						
										<label>Search query:</label>
										<label class="field-clr-base">								
											<input aria-label="search '.$siteName.'" list="pre-sq-srch" type="text" class="field has-field-icon has-field-clr col-sm-w-10" id="q" name="sq" value="'.(isset($searchQuery)? $searchQuery : '').'"  placeholder="Enter your search keyword here" />
										</label>
										<datalist id="pre-sq-srch">
											<option>Economy</option>
											<option>Advertising</option>
											<option>Nigeria</option>
											<option>Africa</option>
										</datalist>
									</div>
									<div class="">
										<div class="">
											<div class="field-ctrl">
												<label> Category:</label>
												<select class="field" name="ca" id="categories">
												'.$cats.'
												</select>
											</div>
											<div class="field-ctrl">
												<label> Sections:</label>
												<select class="field" name="se" id="sections"></select>
											</div>
										</div>										
										<div class="hide" id="'.($advOptsId = 'adv-search-opts').'">
											<div class="field-ctrl">
												<label>Filter:</label>
												<div class="radio-group">
													'.$SITE->getHtmlComponent($componentType='iconic-radio', array('label'=>'posts with images', 'title'=>'search only for posts with images', 'fieldName'=>$K='img_pref', 'value'=>$V='yes', 'on'=>($imgPref == $V))).
													$SITE->getHtmlComponent($componentType, array('label'=>'posts with no images', 'title'=>'search only for posts with no images', 'fieldName'=>$K, 'value'=>$V='no', 'on'=>($imgPref == $V))).
													$SITE->getHtmlComponent($componentType, array('label'=>'all posts but hide images', 'title'=>'search for all posts but hide associated images', 'fieldName'=>$K, 'value'=>$V='all*', 'on'=>($imgPref == $V))).
													$SITE->getHtmlComponent($componentType, array('label'=>'none', 'title'=>'search for all posts including associated images', 'fieldName'=>$K, 'value'=>$V='all', 'on'=>($imgPref == $V || (!isset($_POST[$K]) && !isset($_GET[$K]))))).'
												</div>
											</div>
											<div class="">
												<div class="field-ctrl">
													<label>Topics/Authors:</label>
													<div class="radio-group">
														'.$SITE->getHtmlComponent($componentType, array('label'=>'yes', 'title'=>$T='search only for topic names and authors', 'fieldName'=>$K='topic_n_author', 'value'=>$V='yes', 'on'=>($topicNamesAndAuthorsFilter == $V))).
														$SITE->getHtmlComponent($componentType, array('label'=>'no', 'title'=>$T, 'fieldName'=>$K, 'value'=>$V='no', 'on'=>($topicNamesAndAuthorsFilter == $V))).				
														$SITE->getHtmlComponent($componentType, array('label'=>'dna', 'title'=>$T_dna='do not apply(dna) this filter', 'fieldName'=>$K, 'value'=>$V='dna', 'on'=>($topicNamesAndAuthorsFilter == $V))).'				
													</div>
												</div>'.($GLOBAL_isStaff? '
												<div class="field-ctrl">
													<label>Hidden Posts:</label>
													<div class="radio-group">
														'.$SITE->getHtmlComponent($componentType, array('label'=>'yes', 'title'=>$T='search only for hidden posts', 'fieldName'=>$K='hidden_post', 'value'=>$V='yes', 'on'=>($hiddenPostsFilter == $V))).
														$SITE->getHtmlComponent($componentType, array('label'=>'no', 'title'=>$T, 'fieldName'=>$K, 'value'=>$V='no', 'on'=>($hiddenPostsFilter == $V))).
														$SITE->getHtmlComponent($componentType, array('label'=>'dna', 'title'=>$T_dna, 'fieldName'=>$K, 'value'=>$V='dna', 'on'=>($hiddenPostsFilter == $V))).'				
													</div>
													<label>Locked Posts:</label>
													<div class="radio-group">
														'.$SITE->getHtmlComponent($componentType, array('label'=>'yes', 'title'=>$T='search only for locked posts', 'fieldName'=>$K='locked_post', 'value'=>$V='yes', 'on'=>($lockedPostsFilter == $V))).
														$SITE->getHtmlComponent($componentType, array('label'=>'no', 'title'=>$T, 'fieldName'=>$K, 'value'=>$V='no', 'on'=>($lockedPostsFilter == $V))).
														$SITE->getHtmlComponent($componentType, array('label'=>'dna', 'title'=>$T_dna, 'fieldName'=>$K, 'value'=>$V='dna', 'on'=>($lockedPostsFilter == $V))).'				
													</div>
													<label>Protected Threads:</label>
													<div class="radio-group">
														'.$SITE->getHtmlComponent($componentType, array('label'=>'yes', 'title'=>$T='search only for protected threads', 'fieldName'=>$K='protected_topic', 'value'=>$V='yes', 'on'=>($protectedTopicsFilter == $V))).
														$SITE->getHtmlComponent($componentType, array('label'=>'no', 'title'=>$T, 'fieldName'=>$K, 'value'=>$V='no', 'on'=>($protectedTopicsFilter == $V))).
														$SITE->getHtmlComponent($componentType, array('label'=>'dna', 'title'=>$T_dna, 'fieldName'=>$K, 'value'=>$V='dna', 'on'=>($protectedTopicsFilter == $V))).'				
													</div>
												</div>' : '').'
											</div>
										</div>										
									</div>								
									<div class="field-ctrl">
										<a class="btn btn-link" role="button" data-toggle="smartToggler" data-id-targets="'.$advOptsId.'" data-toggle-attr="text|Hide Advance Options">Show Advance Options</a>
										<input type="submit" name="search" class="submit-btn" value="SEARCH" />
									</div>
								</form>					
							</div>			
						</div>'.$pageTopAds.'
						<div class="row">
							<div class="'.$leftWidgetClass.' base-ctrl base-rad">'.
								(isset($totalRecordsView)? $totalRecordsView : '').
								($totalPage? '<h2>(Page <span class="cyan">'.$pageIdOut.'</span> of '.$totalPage.')</h2>' : '').
								((isset($pagination) && $pagination)? '<hr/>'.$pagination : '').
								(isset($data)? $data : '').(isset($messages)? $messages : '').
								((isset($pagination) && $pagination)? '<hr/>'.$pagination : '').'   												
							</div>
							'.$rightWidget.'											
						</div>
						<div class="">
							'.$pageBottomAds.'
						</div>			
					</div>
				</div>'
		));
						
		break;
			
	}
	
	
	
	
	
	
	
	
	
	/**SEARCH IP ADDRESS**/
	case 'search-ip-address':{
		
		///////AUTHORIZE TOP LEVEL ACCESS///////////
		$ACCOUNT->authorizeTopAccess(false);
		
		$notLogged=$output="";			

		if($sessUsername){
			
			
			///GET THE USER PRIVILEGE/////////
			
			if(!$GLOBAL_isAdmin)
				$noViewPrivilege = '<span class="alert alert-danger">'.$GLOBAL_sessionUrl_unOnly.' Sorry You do not have enough Privilege to view this Page</h3>';
			

			////IF A SEARCH IS SET////

			$search = 'search';
			$sq = 'sq';

			if(isset($_POST[$search]) || isset($_GET[$sq])){
						
				$key = isset($_POST[$search])? $ENGINE->sanitize_user_input($_POST[$sq]) : (isset($_GET[$sq])? $ENGINE->sanitize_user_input($_GET[$sq]) : '');				
					
				if($key){					
					
					//////////FETCH THE RESULT FROM DB/////
					
					$keyDelim = '%'.$key.'%';
					$cnd = "WHERE (USER_ID = ? OR IP LIKE ? OR TOPIC_ID = ?) GROUP BY IP, USER_ID, TOPIC_ID, TIME";
								
					$uid = $ACCOUNT->memberIdToggle($key, true);			
								
					$sql = "SELECT COUNT(*) FROM (SELECT ID FROM topic_views ".$cnd.") temp";
					$valArr = array($uid, $keyDelim, $key);			
					$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
								
					/**********CREATE THE PAGINATION*********/						
					$qstrKeyValArr = array('sq'=>$key);					
					$pageUrl = 'search-ip-address';
					$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'qstrKeyVal'=>$qstrKeyValArr,'useFlat'=>false,'hash'=>'sc-tab'));			
					$pagination = $paginationArr["pagination"];
					$totalPage = $paginationArr["totalPage"];
					$perPage = $paginationArr["perPage"];
					$startIndex = $paginationArr["startIndex"];
					$pageId = $paginationArr["pageId"];			
						
					if($totalRecords){
						
						$sql = "SELECT * FROM topic_views ".$cnd." ORDER BY TIME DESC LIMIT ".$startIndex.",".$perPage;			
						$stmt = $dbm->doSecuredQuery($sql, $valArr);
						
						while($row = $dbm->fetchRow($stmt)){
							
							$ip = $row["IP"];
							$uid = $row["USER_ID"];
						
							$uid_username = $uid? $ACCOUNT->memberIdToggle($uid) : 'Guest';					
							$tid = $row["TOPIC_ID"];					
							$date = $ENGINE->time_ago($row["TIME"]);					
							
							$output .= 	'<tr><td>'.$ACCOUNT->sanitizeUserSlug($uid_username, array('anchor'=>true)).'</td><td>'.$ip.'</td><td>'.$SITE->idCsvToNameString($tid, $t='tid').'</td><td>'.$date.'</td></tr>';
										
						}
						
						
						$output = 	'<div class="table-responsive">
										<table class="table-classic">					
												<tr><th>USERNAME</th><th>IP</th><th>TOPIC</th><th>DATE</th></tr>
												'.$output.'
										</table>
									</div>';
						
						$output = $output? '<div class="alert alert-success">('.$totalRecords.') '.(($totalRecords > 1)? 'results' : 'result').' found for "'.$key.'"</div>'.$output : $output;
								
								
					}else
						$alertUser = '<span class="alert alert-danger">Sorry no result with the keyword <span class="blue" >'.$key.'</span> was not found. Please verify the keyword you entered and try again.</span>';	
				
				}else
					$alertUser = '<span class="alert alert-danger">Please enter a username, ip Address  or topic id !</span>';									
			
			}

		}else
			$notLogged = $GLOBAL_notLogged;
		

		$SITE->buildPageHtml(array("pageTitle"=>'Search Ip Address',
				"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href=/'.$pageSelf.' title="">Search Ip Address</a></li>'),
				"pageBody"=>'
					<div class="single-base blend">
						<div class="base-ctrl">'.
							$notLogged.(isset($noViewPrivilege)? $noViewPrivilege : '').
							
							(($sessUsername && $GLOBAL_isAdmin)? '															
								<div class="panel panel-gray">
									<h1 class="panel-head page-title">SEARCH IP ADDRESS</h1>
									<div class="panel-body">'.
										(isset($alertUser)? $alertUser : '').'
										<form class="inline-form" method="post" action="/'.$pageSelf.'">
											<div class="field-ctrl">
												<label>KEYWORD:<small class="prime">(username, ip, topic id)</small></label>
												<label class="field-clr-base">	
													<input aria-label="search IP" class="field has-field-icon has-field-clr col-lg-w-10"  value="'.(isset($key)? $key : '').'" type="text" name="sq" placeholder="username, email, phone, last name, first name" />
												</label>		
											</div>							
											<div class="field-ctrl">
												<input type="submit" class="form-btn" name="search" value="SEARCH" />
											</div>								
										</form>'.
										
										((isset($pagination) && $pagination)? '<h2 id="sc-tab">(page <span class="cyan">'.$pageId.'</span> of '.$totalPage.' )</h2>' : '').
										(isset($pagination)? $pagination : '').
										(isset($output)? $output : '').
										(isset($pagination)? $pagination : '').'													
									</div>
								</div>'
							: '').'																			
						</div>
					</div>'
		));
						
		break;

	}
	
	
	
	
	
	
	
	
	
	/**SEARCH USERS**/
	case 'search-users':{
					
		$notLogged=$sort=$filter=$output=$outputAll=$pageId="";
		
		$dataUrl = ' data-url="/'.$pageSelf.'" '; $tableDecoy = 'user';							

		$sortList = array(//FORMAT => urlSlug:urlLabel:urlIcon:ignoreCond
			($defaultOrder = $latest = 'latest'), ($oldest = 'oldest'), ($alphabet = 'alphabet'), 
			($lastVisit = 'last-visit').':Last Visit', ($reputation = 'reputation'), 
			($adCreditsAvail = 'ad-credits-available').':ad credits available::'.!$GLOBAL_isTopStaff, 
			($adCreditsUsed = 'ad-credits-used').':ad credits used::'.!$GLOBAL_isTopStaff
		);

		$filterList = array(//FORMAT => urlSlug:urlLabel:urlIcon:ignoreCond
			($defaultFilter = $none = 'none').':::'.!$GLOBAL_isTopStaff, 
			($scheduledDeact = 'scheduled-deactivation').':scheduled deactivation::'.!$GLOBAL_isTopStaff			
		);

			
		
		function generateUserTable($user, $retLiveFields=false){

			$editParam=$allYouFollow=$allFollowers=$sectionsModerated="";
			global $ENGINE, $FORUM, $ACCOUNT, $SITE, $GLOBAL_isTrusted, $GLOBAL_mediaRootAvt, $GLOBAL_page_self,
			$tableDecoy;

			$mediaRootAvt = $GLOBAL_mediaRootAvt;
			
			$F = array('fn' => ' data-name="fn" ', 'ln' => ' data-name="ln" ', 'sex' => ' data-name="sex" ', 'phn' => ' data-name="phone" ',
					'dob' => ' data-name="dob" ', 'site' => ' data-name="site" ', 'fb' => ' data-name="fb" ', 'tw' => ' data-name="tw" ',
					'instagram' => ' data-name="instagram" ', 'linkedin' => ' data-name="linkedin" ', 'wa' => ' data-name="wa" ', 'em' => ' data-name="em" ',
					'ban' => ' data-name="bans" ', 'rep' => ' data-name="repu" ', 'crda' => ' data-name="crda" ', 'crdu' => ' data-name="crdu" ',
					'prmPurse' => ' data-name="prm-purse" ', 'un' => ' data-name="uname" ', 'pwd' => ' data-name="pwd" ', 'mst' => ' data-name="mst" '
				);
			
			if($retLiveFields)
				return $F;
			
			$uid = $user["ID"];
			$username = $user["USERNAME"];
			
			if($uid){

				///GET THE SCAT AND CAT THE USER MODERATES/////////
				$sectionsModerated = $SITE->moderatedSectionCategoryHandler(array('uid'=>$uid,'action'=>'scm-names'));												
				
				///GET THE PPLE FOLLOWING AND FOLLOWERS OF THE CURRENT USER ON VIEW//////////////
				$followingFollowersArr = $FORUM->followedMembersHandler(array('uid'=>$uid, 'vcardMin'=>true, 'getFF'=>true));
				
				if(isset($followingFollowersArr[$K="followers"]))
					$allFollowers = $followingFollowersArr[$K];
	
				if(isset($followingFollowersArr[$K="following"]))
					$allYouFollow = $followingFollowersArr[$K];

			}

			$dataId = ' data-id="'.$uid.'" ';
			$dataUrl = ' data-url="/'.$GLOBAL_page_self.'" ';
			$editParam = 'class="live-edit"  data-table="'.$tableDecoy.'" '.$dataUrl.$dataId;

			if((isset($_SESSION[ADMIN_EDIT_USER]) && $_SESSION[ADMIN_EDIT_USER] == $uid) || (isset($_SESSION[$K=ADMIN_EDIT_ALL_USER]) && $_SESSION[$K]))
				$editParam .= ' contenteditable="true" ';
			
			
			//////CHECK IF THE USER HAS BEEN PLACED UNDER A BAN ///
			list($spamBan, $modsBan, $isBanned) = $ACCOUNT->getBanStatus($uid);	
			$onBan = $isBanned? '<hr/><b class="red">CURRENTLY ON BAN</b>' : '';
			
			
			$table = '<div class="" style="padding-bottom: 100px;">
						<caption><h1 class="cyan">'.strtoupper(trim($username)).'\'s DATAS</h1></caption>
						<div class="table-responsive"><table class="table-classicx">					
							<tr>
								<th>USER ID</th>												
								<td>'.$uid.'</td>											
							</tr>										
							<tr>
								<th>USERNAME</th>												
								<td>'.$ACCOUNT->sanitizeUserSlug($username, array('anchor'=>true)).'</td>											
							</tr>																										
							<tr>
								<th>FIRST NAME</th>												
								<td '.$F['fn'].$editParam.' >'.ucfirst($user["FIRST_NAME"]).'</td>											
							</tr>
							<tr>
								<th>LAST NAME</th>												
								<td '.$F['ln'].$editParam.' >'.ucfirst($user["LAST_NAME"]).'</td>											
							</tr>					
							<tr>
								<th>GENDER</th>												
								<td '.$F['sex'].$editParam.' >'.$user["SEX"].'</td>											
							</tr>
							<tr>
								<th>PHONE</th>												
								<td '.$F['phn'].$editParam.' >'.$user["PHONE"].'</td>											
							</tr>
							<tr>
								<th>EMAIL</th>												
								<td><a href="mailto:'.($K=$user["EMAIL"]).'">'.$K.'</a></td>											
							</tr>
							<tr>
								<th>MARITAL STATUS</th>								
								<td>'.$user["MARITAL_STATUS"].'</td>							
							</tr>
							<tr>
								<th>BIRTHDAY</th>								
								<td '.$F['dob'].$editParam.' >'.$user["DOB"].'</td>							
							</tr>
							<tr>
								<th>TIME REGISTERED</th>												
								<td>'.$ENGINE->time_ago($user["TIME"]).'</td>											
							</tr>											
							<tr>
								<th>TIME LAST VISITED</th>												
								<td>'.$ENGINE->time_ago($user["LAST_SEEN"]).'</td>											
							</tr>																						
							<tr>
								<th>SCHEDULED ACCOUNT TERMINATION</th>												
								<td><b class="'.(($K=(int)$user["SCHEDULED_TIME_FOR_DELETE"])? 'red' : 'green').'">'.($K? 'YES' : 'NO').'</b></td>											
							</tr>											
							<tr>
								<th>AVATAR</th>												
								<td>'.$ACCOUNT->getDp($uid, array('zoomctrl'=>true, 'url'=>$SITE->getDownloadURL($user["AVATAR"], "profile", false))).'</td>							
							</tr>																																									
							<tr>
								<th>WEBSITE</th>												
								<td '.$F['site'].$editParam.' >'.$SITE->idCsvToNameString($user["WEBSITE_URL"], 'xurl').'</td>											
							</tr>					
							<tr>
								<th>FACEBOOK</th>												
								<td '.$F['fb'].$editParam.' >'.$user["FACEBOOK_URL"].'</td>											
							</tr>											
							<tr>
								<th>TWITTER</th>												
								<td '.$F['tw'].$editParam.' >'.$user["TWITTER_URL"].'</td>											
							</tr>																
							<tr>
								<th>INSTAGRAM</th>												
								<td '.$F['instagram'].$editParam.' >'.$user["INSTAGRAM_URL"].'</td>											
							</tr>																
							<tr>
								<th>Linkedin</th>												
								<td '.$F['linkedin'].$editParam.' >'.$user["LINKEDIN_URL"].'</td>											
							</tr>					
							<tr>
								<th>WHATSAPP</th>												
								<td '.$F['wa'].$editParam.' >'.$user["WHATSAPP_URL"].'</td>											
							</tr>	
							<tr>
								<th>NUMBER OF BANS</th>												
								<td '.$F['ban'].$editParam.' >'.$user["BAN_COUNTER"].$onBan.'</td>											
							</tr>
							<tr>
								<th>FOLLOWING</th>												
								<td>'.$allYouFollow.'</td>											
							</tr>											
							<tr>
								<th>FOLLOWERS</th>												
								<td>'.$allFollowers.'</td>											
							</tr>											
							<tr>
								<th>LATEST TOPICS</th>												
								<td>'.$FORUM->loadUserTopics(array('uid'=>$uid, 'sep'=>'<hr>')).'</td>											
							</tr>											
							<tr>
								<th>TOPICS FOLLOWED</th>												
								<td>'.$FORUM->followedTopicsHandler(array('uid'=>$uid, 'sep'=>'<hr>')).'</td>											
							</tr>											
							<tr>
								<th>FOLLOWED SECTIONS</th>												
								<td>'.$FORUM->followedSectionsHandler(array('uid'=>$uid, 'sep'=>', ')).'</td>											
							</tr>											
							<tr>
								<th>RANK</th>												
								<td>'.$ACCOUNT->getUserPrivilege($uid, false, true).'</td>											
							</tr>
							<tr>
								<th>REPUTATION</th>												
								<td '.$F['rep'].$editParam.' >'.$user["REPUTATION"].'</td>											
							</tr>																					
							<tr>
								<th>MODERATES IN</th>												
								<td>'.$sectionsModerated.'</td>											
							</tr>
							'.(($ACCOUNT->SESS->isTopStaff())?
							'<tr>
								<th>AD CREDITS AVAIL</th>												
								<td '.$F['crda'].$editParam.' >'.$user["ADS_CREDITS_AVAIL"].'</td>											
							</tr>					
							<tr>
								<th>AD CREDITS USED</th>												
								<td '.$F['crdu'].$editParam.' >'.$user["ADS_CREDITS_USED"].'</td>											
							</tr>
							<tr>
								<th>AD PREMIUM PURSE</th>												
								<td '.$F['prmPurse'].$editParam.'>'.$user["ADS_PREMIUM_PURSE"].'</td>											
							</tr>					
							<tr>
								<th>BANNER CAMPAIGN STATUS</th>												
								<td>'.(($user["BANNER_CAMPAIGN_STATUS"])? '<b class="green">ACTIVE</b>' : '<b class="red">PAUSED</b>').'</td>											
							</tr>
							<tr>
								<th>TEXT CAMPAIGN STATUS</th>												
								<td>'.(($user["TEXT_CAMPAIGN_STATUS"])? '<b class="green">ACTIVE</b>' : '<b class="red">PAUSED</b>').'</td>											
							</tr>
							<tr>
								<th>CAMPAIGN NTFY STATUS</th>												
								<td>'.(($user["CAMPAIGN_NTF"])? '<b class="green">ENABLED</b>' : '<b class="red">DISABLED</b>').'</td>											
							</tr>' : '')
							.(($ACCOUNT->SESS->isAdmin())?
							'<tr>												
								<th>CTRL</th>
								'.$SITE->getLiveTableAdminControls($tableDecoy, $uid, $dataUrl, ADMIN_DEL_USER).'
							</tr>' : '').'							
						</table></div>
					</div>';
			
			return $table;
			
		}

		if($sessUsername){
							
			//////CALL LIVES/////////
			$SITE->doAdminLiveUnlocks();
			$SITE->doAdminLiveEdit();
			$SITE->doAdminLiveAdd();
			$SITE->doAdminLiveDelete();
			
			///GET THE USER PRIVILEGE/////////
			
			if(!$GLOBAL_isStaff)
				$noViewPrivilege = '<span class="alert alert-danger">'.$ACCOUNT->sanitizeUserSlug($sessUsername, array('anchor'=>true, 'youRef' => false)).', Sorry! You do not have enough Privilege to view this Page</span>';
			

			////IF A SEARCH IS SET////
			
			$search = 'search';
			$sq = 'sq';
			
			if(isset($_POST[$search]) || isset($_GET[$sq])){
						
				$key = isset($_POST[$search])? $ENGINE->sanitize_user_input($_POST[$sq]) : (isset($_GET[$sq])? $ENGINE->sanitize_user_input($_GET[$sq]) : '');		
				
				$searching = true;
					
				if($key){		

					$keyDelim = '%'.$key.'%';
					$cnd = "WHERE (USERNAME = ?  OR EMAIL LIKE ? OR PHONE LIKE ? OR FIRST_NAME LIKE ? OR LAST_NAME LIKE ?) AND USERNAME != ''";
					
					$sql = "SELECT COUNT(*) FROM users ".$cnd;
					$valArr = array($key, $keyDelim, $keyDelim, $keyDelim, $keyDelim);
					$totalRecords = $dbm->doSecuredQuery($sql, $valArr)->fetchColumn();
					
					/**********CREATE THE PAGINATION*********/						
					$qstrKeyValArr = array('sq'=>$key);					
					$pageUrl = 'search-users';
					$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'qstrKeyVal'=>$qstrKeyValArr,'useFlat'=>false,'perPage'=>10,
					'pageKey'=>'page_ids','jmpKey'=>'jump_page_s','hash'=>'sc-tab'));					
					$paginationSrch = $paginationArr["pagination"];
					$totalPageSrch = $paginationArr["totalPage"];
					$perPage = $paginationArr["perPage"];
					$startIndex = $paginationArr["startIndex"];
					$page_ids = $paginationArr["pageId"];

					///////END OF PAGINATION///		
					
					if($totalRecords){
						
						$sql = "SELECT * FROM users ".$cnd." ORDER BY USERNAME ASC LIMIT ".$startIndex.",".$perPage;
						$stmt = $dbm->doSecuredQuery($sql, $valArr);
						
						while($row = $dbm->fetchRow($stmt)){
							
							$uid = $row["ID"];
							$username = $row["USERNAME"];					
							$usernameSlug = $ACCOUNT->sanitizeUserSlug($username);		
							
							list($ranksHigher, $ranksEqual) = $ACCOUNT->sessionRanksHigher($username);
								
							if(!$ranksHigher && !$ranksEqual){
											
								$output .= '<span class="alert alert-danger">Sorry you do not have enough privilege to view <a href="/'.$usernameSlug.'" class="links" >'.$username.'</a>\'s Datas</span>';
								continue;
							}					
								
							$output .= 	generateUserTable($row);
										
						}
						
						$output = $output? '<div class="alert alert-success">('.$totalRecords.') '.(($totalRecords > 1)? 'results' : 'result').' found for "'.$key.'"</div>'.$output : $output;
								
								
					}else
						$alertUser = '<span class="alert alert-danger">Sorry no user with the keyword <span class="blue" >'.$key.' </span> was not found. Please verify the keyword you entered and try again.</span>';								

			
				}else
					$alertUser = '<span class="alert alert-danger">Please enter the keyword for the user you want to search !</span>';
									
			
			}



			/////////TABLE OF ALL MEMBERS////////

			if(isset($_GET[$K="srt"]))		
				$sort = $_GET[$K];
			
			elseif(isset($_POST[$K]))
				$sort = $_POST[$K];
				
			$sort = $ENGINE->sanitize_user_input($sort, array('lowercase' => true));
			
			if(isset($_GET[$K="filter"]))		
				$filter = $_GET[$K];
			
			elseif(isset($_POST[$K]))
				$filter = $_POST[$K];
				
			$filter = $ENGINE->sanitize_user_input($filter, array('lowercase' => true));						
																
			switch($sort){
	
				case $oldest: $orderBy = 'TIME ASC'; break;
	
				case $alphabet: $orderBy = 'USERNAME'; break;
	
				case $lastVisit: $orderBy = 'LAST_SEEN DESC'; break;
	
				case $adCreditsAvail: $orderBy = 'ADS_CREDITS_AVAIL DESC'; break;
	
				case $adCreditsUsed: $orderBy = 'ADS_CREDITS_USED DESC'; break;
	
				case $reputation: $orderBy = 'REPUTATION DESC'; break;
	
				default: $orderBy = 'TIME DESC';
	
			}
	
			$orderBy = ' ORDER BY '.$orderBy; 	
			
			switch($filter){
	
				case $scheduledDeact: $filterCnd = ' AND SCHEDULED_TIME_FOR_DELETE !=0'; break;
	
				default: $filterCnd = '';
	
			}
	
			$filterCnd = " WHERE USERNAME != '' ".$filterCnd;
			
			if($GLOBAL_isStaff){
							
				///////////PDO QUERY////////
				
				$sql = "SELECT COUNT(*) FROM users ".$filterCnd;
				$totalRecords = $dbm->query($sql)->fetchColumn();
				
				/**********CREATE THE PAGINATION*********/					
				$qstrKeyValArr = array('srt'=>$sort,'filter'=>$filter);					
				$pageUrl = 'search-users';
				$paginationArr = $SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'qstrKeyVal'=>$qstrKeyValArr,'useFlat'=>false,'perPage'=>10,'hash'=>'all-tab'));									
				$pagination = $paginationArr["pagination"];
				$totalPage = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$pageId = $paginationArr["pageId"];

				///////END OF PAGINATION///
				
				if($totalRecords){
						
					$sql = "SELECT * FROM users ".$filterCnd.$orderBy." LIMIT ".$startIndex.",".$perPage;
					$stmt = $dbm->query($sql);
						
					while($row = $dbm->fetchRow($stmt)){														
							
						$uid = $row["ID"];				
						$username = $row["USERNAME"];				
						$usernameSlug = $ACCOUNT->sanitizeUserSlug($username);						
											
						list($ranksHigher, $ranksEqual) = $ACCOUNT->sessionRanksHigher($uid);
							
						if(!$ranksHigher && !$ranksEqual){
										
							$outputAll .= '<span class="alert alert-danger">Sorry you do not have enough privilege to view <a href="/'.$usernameSlug.'" class="links" >'.$username.'</a>`s Datas</span>';
							continue;
	
						}				
					
						$outputAll .= generateUserTable($row);
	
					}
					
					$F = generateUserTable('', true);
					
					$dataAdd = ' contenteditable="true" data-adding="1"';
					$addTable = '
								<div class="table-responsive">
									<table>
										<tr>
											<th>USERNAME</th><th>PASSWORD</th><th>E-MAIL</th><th>FIRST NAME</th>
											<th>LAST NAME</th><th>GENDER</th><th>MARITAL STATUS</th><th>DOB(DD-MM-YYYY)</th><th>ADD</th>
										</tr>
										<tr>
											<td '.$dataAdd.$F['un'].'></td>
											<td '.$dataAdd.$F['pwd'].'></td>
											<td '.$dataAdd.$F['em'].'></td>
											<td '.$dataAdd.$F['fn'].'></td>
											<td '.$dataAdd.$F['ln'].'></td>
											<td '.$dataAdd.$F['sex'].'></td>
											<td '.$dataAdd.$F['mst'].'></td>
											<td  '.$dataAdd.$F['dob'].'></td>
											'.$SITE->getLiveTableAddControls($tableDecoy, $dataUrl).'									
										</tr>
									</table>
								</div>';
					
					
					$outputAll = ($GLOBAL_isAdmin? $addTable : '').$outputAll;
								

				}else
					$outputAll = '<span class="alert alert-danger">Sorry no result was found matching your request</span>';
					

			}
			
		}else
			$notLogged = $GLOBAL_notLogged;
				
			
		$SITE->buildPageHtml(array("pageTitle"=>'Search Users',
					"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/search-users" title="">Search a User</a></li>'),
					"pageBody"=>'
					<d iv class="single-base blend">
						<div class="base-ctrl">'.
							$notLogged.(isset($noViewPrivilege)? $noViewPrivilege : ''). 
							
							(($sessUsername && $GLOBAL_isStaff)? '														
								<div class="panel panel-orange">
									<h1 class="panel-head page-title">SEARCH A USER</h1>
									<div class="panel-body">'.
										(isset($alertUser)? $alertUser : '').'
										<form class="inline-form" method="post" action="/'.$pageSelf.'">
											<div class="field-ctrl">
												<label>KEYWORD:<small class="prime">(username, email, phone, last name, first name)</small></label>
												<label class="field-clr-base" >	
													<input aria-label="search user" class="field has-field-icon has-field-clr col-w-10"  value="'.(isset($key)? $key : '').'" type="text" name="sq" placeholder="username, email, phone, last name, first name" />
												</label>		
											</div>	
											<div class="field-ctrl">
												<input type="submit" class="form-btn" name="search" value="SEARCH" />
											</div>
											<div class="field-ctrl">
												<a role="button" class="btn btn-warning" href="/search-users">Clear Search</a>
											</div>																										
										</form>'.
										((isset($paginationSrch) && $paginationSrch)? '<h1 id="sc-tab">(page <span class="cyan">'.$page_ids.'</span> of '.$totalPageSrch.' )</h1>' : '').
										(isset($paginationSrch)? $paginationSrch : '').
										(isset($output)? $output : '').
										(isset($paginationSrch)? $paginationSrch : '').'													
									</div>
								</div>'.
								((($GLOBAL_isStaff) && !isset($searching))? '
									
									<div class="panel panel-bluex">
										<h1 class="panel-head page-title">TABLE OF MEMBERS</h1>
											<div class="panel-body">'.
												$SITE->buildSortLinks(array(
													'baseUrl' => '', 'pageId' => $pageId, 'sq' => '', 'urlHash' => 'tab', 
													'activeOrder' => $sort, 'orderList' => $sortList, 'orderKey' => 'srt',
													'activeFilter' => $filter, 'filterList' => $filterList, 'filterKey' => 'filter',
													'defaultOrder' => $defaultOrder, 'defaultFilter' => $defaultFilter, 
													'orderGlobLabel' => 'Sort by', 'hasRelBase' => false
													)).
												((isset($pagination) && $pagination)? '<h1 id="all-tab">(page <span class="cyan">'.$pageId.'</span> of '.$totalPage.' )</h1>' : '').
												(isset($pagination)? $pagination : '').
												(isset($outputAll)? $outputAll : '').
												(isset($pagination)? $pagination : '').
										'</div>
									</div>'
								: '')

							: '').'										
						</div>
					</div>'
		));
						
		break;

	}
	
	
	
	
	default:{
	
		/**IF ABOVE REQUEST URL NOT FOUND THEN FALL BACK TO 404 PAGE ERROR**/			
		include_once(DOC_ROOT."/page-error.php");
		exit();	
					
	}
	
}


?>