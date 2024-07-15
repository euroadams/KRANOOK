<?php


trait Post{
	
	
	/************************************************************************************/
	/************************************************************************************
										METHODS
	/************************************************************************************
	/************************************************************************************/
		
	
	/*** Method for fetching a field from database post table ***/
	public function getPostDetail($param, $col, $tidRef=false, $order="ORDER BY TIME DESC"){
		
		/////PDO QUERY//////////	
		$sql = "SELECT ".$col." FROM posts WHERE ".($tidRef? "TOPIC_ID" : "ID")." = ? ".$order." LIMIT 1 ";
		$valArr = array($param);
		$return = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
		return $return;

	}

	
    		





	
		
	/*** Method for computing page id of a post in a thread using the post number ***/
	public function getPostPageNumber($postNumber){
		
		$perPage = $this->SITE->getPaginationCount();
		
		$page = ceil(($postNumber / $perPage));
		
		if(!$page)
			$page = 1;
			
		return $page;
		
	}
	 
	 



	
		


	
		
	/*** Method for computing post number of a post in a thread ***/
	public function getPostNumber($pid, $tid, $findLastPostNumber=false){
		 
		/////////PDO QUERY//////
		
		if($findLastPostNumber){
			
			$subQry = " ";
			$valArr = array($tid);
			
		}else{
			
			$subQry = ' AND ID <= ?';
			$valArr = array($tid, $pid );
			
		}
			
		$sql = "SELECT COUNT(*) FROM posts WHERE TOPIC_ID = ? ".$subQry;
		$postNum = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();		
		
		return $postNum;
		 
	}







		


		

		
	/*** Method for loading pinned posts in a threead ***/
	public function loadPinnedPosts($tid, $limit = 6){
		 
		/////////PDO QUERY//////
		
		$sql = $this->SITE->composeQuery(array('type' => 'for_post', 'start' => 0, 'stop' => $limit, 'uniqueColumns' => '', 'filterCnd' => 'TOPIC_ID = ? AND  posts.PIN_TIME != 0', 'orderBy' => 'PINNED_BY_MOD DESC, posts.PIN_TIME DESC'));
		$valArr = array($tid);

		///////////DISPLAY THE POSTS/////
		list($messages) = $this->loadPosts($sql, $valArr, array('type'=>'pins'));

		return $messages;
		
	}
	 
	 

	 








	/*** Method for loading posts queried from a topic ***/
	public function loadPosts($sql, $valArr, $xtraMetaArr=""){	
		
		global $badgesAndReputations, $GLOBAL_sessionUrl, $GLOBAL_isTrusted, $GLOBAL_mediaRootFav,
		$GLOBAL_mediaRootPost, $GLOBAL_mediaRootPostXCL, $GLOBAL_siteDomain, $GLOBAL_siteName, $GLOBAL_rdr;
		
		$messages=$postId=$topicId=$sid=$cid=$sectionName=$categName='';

		$composerFieldId = '#'.COMPOSER_ID;
		$pinCount = 1;	
		
		$sessUsername = $this->SESS->getUsername();
		$sessPrivilege = $this->SESS->getUserPrivilege($sessUsername);
		$sessUrl = $GLOBAL_sessionUrl;
		$tops = $this->SESS->isTopStaff();
		$mediaRootFav = $GLOBAL_mediaRootFav;
		$mediaRootPost = $GLOBAL_mediaRootPost;
		$mediaRootPostXCL = $GLOBAL_mediaRootPostXCL;
		$siteDomain = $GLOBAL_siteDomain;
		$siteName = $GLOBAL_siteName;
		$rdr = $GLOBAL_rdr;
		$sessUid = $this->SESS->getUserId();
		$messages=$postId=$userBadges=$topicId="";
		///GET AUTHORIZATIONS///
		$modsAuth = $this->authorizeModeration();	
		$flagPost = $modsAuth["flagPost"];
		$voteDownAllowed = $modsAuth["voteDown"];
		$voteUpAllowed = $modsAuth["voteUp"];
		
		$highlighKeywords = $this->ENGINE->get_assoc_arr($xtraMetaArr, 'highlighKeywords');
		$hideImgs = (bool)$this->ENGINE->get_assoc_arr($xtraMetaArr, 'hideImgs');
		$type = strtolower($this->ENGINE->get_assoc_arr($xtraMetaArr, 'type'));
		$replyBoxAjaxClass = $this->ENGINE->get_assoc_arr($xtraMetaArr, 'replyBoxAjaxClass');
		$preloaderDpn = 'preloader-dpn';
		$pageIdPassed = $this->ENGINE->get_assoc_arr($xtraMetaArr, 'pageIdPassed');
		
		$forOutsideMainThread = !in_array($type, array("main"));

		$isTypePins = ($type == 'pins');
		
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);	
		
		while($row = $this->DBM->fetchRow($stmt)){
		
			///////GET DETAILS///////
			
			$topicId = $row["TOPIC_ID"];
			
			list($postAuthorized, $viewAuthorized, $protectionAlert) = $this->authorizeThreadAccess($topicId);
			
			$newPostLink = "post";
			$topicName = $row["TOPIC_NAME"];	
			
			$topicStatArr = $this->getThreadStatus($topicId);												
			$topicClosed = $topicStatArr["tClosed"];

			$sid = $row["SECTION_ID"];
			$sectionName = $row["SECTION_NAME"];
			$sectionSlug = $this->ENGINE->sanitize_slug($sectionName);
			
			$cid = $row["CATEG_ID"];
			$categName = $row["CATEG_NAME"];	
			$categSlug = $this->ENGINE->sanitize_slug($categName);		

			$postId = $row["ID"];
			$postNumber = $this->getPostNumber($postId, $topicId, "");
			$messageBody = $row["MESSAGE"];
			$postAuthorId = $row["POST_AUTHOR_ID"];
			$U = $this->ACCOUNT->loadUser($postAuthorId); 
			$postAuthorUsername = $U->getUsername();
			$postTime = $row["TIME"];
			$postSocialCounts = $row["SOCIAL_COUNTS"];
			$postSocialCounts = $postSocialCounts? ' <b class="cyan">'.$this->ENGINE->format_number($postSocialCounts).'</b>' : ''; 		
			$vds = $row["VDS_WARNING"];
			$syndicateUrl = $row["SYNDICATE_URL"];
			$uiBgId = $row["UIBG_ID"];
			$uiBgRow = $this->SITE->uiBgHandler('', array('id' => $uiBgId, 'action' => 'getRow'));
			$uiBgStyles = is_array($uiBgRow)? $uiBgRow["BG_STYLES"] : '';
			$uiBgContentStyles = is_array($uiBgRow)? $uiBgRow["CONTENT_STYLES"] : '';
			$postUploads = $row["UPLOADS"];
			$uploadsOriginalNames = $row["UPLOADS_ORIGINAL_NAMES"];		
			list($postHidden, $PHU) = $this->decodeModerationStatus($row["HIDDEN"]);
			list($postLocked, $PLU) = $this->decodeModerationStatus($row["LOCKED"]);
			list($postPinned, $PPU) = $this->decodeModerationStatus($row["PINNED"]);
			
			list($floatedProtSticker, $unfloatedProtSticker) = $this->getThreadProtectionSticker($row["PROTECTION_LEVEL"]);
			
			$threadPrtPin = ($unfloatedProtSticker && $forOutsideMainThread)? '<div class="in-post-thread-prt-pin">'.$unfloatedProtSticker.'</div>' : '';
			
			$pinTime = $row["PIN_TIME"];
			$flags = $row["FLAG_COUNTS"];
			$pageId = $this->getPostPageNumber($postNumber);
			$appendPageIdPassed = $pageIdPassed? '/'.$pageIdPassed : '';
			$categTitle = ' title="'.$categName.' ('.$cid.')" ';
			$sectionTitle = ' title="'.$sectionName.' ('.$sid.')" ';
			$threadTitle = ' title="'.$topicName.' ('.$topicId.', '.$postId.')" ';
			$topicSlugNoHash = $this->SITE->getThreadSlug($topicId).$pageId;
			$topicSlugHash = '#'.$postNumber;
			$topicSlug = $topicSlugNoHash.$topicSlugHash;
			$postHashId = $topicId.'_'.$postNumber;
			$modsCtrlMetaArr = array(
					"postHashId"=>$postHashId, "pid"=>$postId, "tid"=>$topicId, "tn"=>$topicName, "section"=>$sectionName,
					 "rdr"=>$rdr					
				);
			$uploadedFiles = $this->getPostUploads($postUploads, array('fileOriginalNames'=>$uploadsOriginalNames, 'vds'=>$vds, 'tid'=>$topicId, 'pid'=>$postId, 'slug'=>$topicSlugNoHash, 'slugHash'=>$topicSlugHash, 'hideImgs'=>$hideImgs));
			////GET SIGNATURES AND MOD STARS//////////
			$signsStars = $this->ACCOUNT->getUserSignature($postAuthorUsername);			
			$loc = $U->getLocation();		
			$userBadges = ($this->SESS->getBadgeViews() || !$sessUsername)? $badgesAndReputations->loadUserBadges(array('uid'=>$postAuthorId, 'ipb'=>true, 'mvp'=>$postId)) : "";
			$userBadges = $userBadges.'<div class="post-body-ctrl no-tp clear" >'.($loc? ' <span class="prime small no-tp" ><b class="fas fa-map-marker"></b> '.$loc.'</span>' : '')
			.'<div class="pull-r">'.$this->ENGINE->build_lavatar($postAuthorUsername, '-5px', true, false, true ).$threadPrtPin.'</div></div> ';
			
			////FORMAT THE WAY POST DATES ARE SHOWN/////////
			
			$postTimeView =  '<span class="">'.$this->ENGINE->time_ago($postTime).'</span>';		
			$dp = ($this->SESS->getShowPostAvatars() || !$sessUsername)? $this->ACCOUNT->getDp($postAuthorUsername, array('type'=>'vcard','ipaneTop'=>false)) : "";	

			$postAuthorMetaArr = array('anchor'=>true, 'gender'=>true, 'wrap'=>'span.bg-white', 'cls'=>'post-author', 'urlAttr'=>'title="'.($loc? 'Location: '.$loc : '').'"');
			$postAuthor = ' by '.$this->ACCOUNT->sanitizeUserSlug($postAuthorUsername, $postAuthorMetaArr).' ';
			
			$postUnavailableAlert = '<div class="alert alert-warning"><span class="red">THIS POST IS UNAVAILABLE AT THIS TIME</span><br/>The content cannot be displayed right now. It may be temporarily unavailable or you may not have permission to view it.</div>';
			$activeStateClass = ' active-done-state ';	
			 
			$postEditLink=$likes=$unlike=$shares=$unshare=$shareStat=$postSubNav=
			$postReportLink=$postShareLink=$postSocialSharingToggler=$postLikeLink=$postQuoteLink=
			$postMultiQuoteLink=$multiQuoteStat="";$socMetasArr=array();
			
			
			///KEEP MODIFY LINK HIDDEN IF THE USER IS NOT AUTHORIZED///////////////			
						
			
			if($postAuthorId == $sessUid  || $this->ACCOUNT->sessionAccess(array('id'=>$topicId))){
				
				$postEditLink = '<span class="'.$preloaderDpn.'">(<a href="/'.$newPostLink.'/'.$topicId.'/edit/'.$postId.$appendPageIdPassed.$composerFieldId.'" data-load-content="true" class="'.$replyBoxAjaxClass.' links" title="Edit this post"><i class="fas fa-edit"></i><span class="dsk-platform-dpn-ix">&nbsp;Edit</span></a>)</span>';
				
			}	
			
			
			///GET THE VOTES///////		
			$totUppers = $this->votesHandler(array('type'=>'u','pid'=>$postId));		
			$totDowners = $this->votesHandler(array('type'=>'d','pid'=>$postId));
			$likesT = ($totUppers - $totDowners);
			$likes = $this->ENGINE->format_number($likesT);
					
			$hasUpped = $this->votesHandler(array('uid'=>$sessUid,'pid'=>$postId,'type'=>'u','action'=>'check','voter'=>true));
			$hasDowned = $this->votesHandler(array('uid'=>$sessUid,'pid'=>$postId,'type'=>'d','action'=>'check','voter'=>true));							
			
			///GET THE SHARES ////////////		
			$totShares = $this->sharesHandler(array('pid'=>$postId));	
			
			if($this->sharesHandler(array('uid'=>$sessUid,'pid'=>$postId,'action'=>'check','sharer'=>true)))	
				$shareStat = 'Unshare';				
			else
				$shareStat = 'Share';		
			
			if($totShares == 1)	
				$shares = $totShares.' Share ';			
			elseif($totShares > 1)	
				$shares = $this->ENGINE->format_number($totShares).' Shares ';				
			
			if($sessUsername){
							
				if($this->highlightedForMultiQuote($sessUid, $postId))
					$multiQuoteStat = 'checked';
			
			
				$postQuoteLink = '<span class="'.$preloaderDpn.'">(<a href="/'.$newPostLink.'/'.$topicId.'/quote/'.$postId.$appendPageIdPassed.$composerFieldId.'"  data-load-content="true" data-append="true" class="'.$replyBoxAjaxClass.' links"  title="Quote this post">Quote</a>)</span>';

				$postMultiQuoteLink = '<span class="'.$preloaderDpn.'">(<a href="/'.$newPostLink.'/'.$topicId.'/multi-quote'.$appendPageIdPassed.$composerFieldId.'" data-load-content="true" data-append="true" class="'.$replyBoxAjaxClass.' links"  title="Quote all multi-highlighted posts">Multi-Quote</a>
										'.$this->SITE->getHtmlComponent('iconic-checkbox', array('fieldData'=>'data-pid="'.$postId.'"', 'title'=>'Highlight this post for multi-quoting', 'fieldClass'=>'multiquote', 'on'=>$multiQuoteStat)).'
										<a href="/clear-multiquotes?_rdr='.$rdr.'#'.$postNumber.'" class="links clear-mqt" title="Clear all post you have highlighted for multi-quoting" >clear all</a>)</span>';
			
			}
			
			$voteCounts = $likes? '<span>(<b class="cyan">'.(($likesT == 1 || $likesT == -1)? $likes.' Vote' : $likes.' Votes').'</b>)</span>' : '';
			
			if($sessUsername){
			
				if($postAuthorId != $sessUid && $postAuthorized){
					$voteDatas = ' data-pid="'.$postId.'" data-disp="lkc-'.$postId.'" data-css="'.$activeStateClass.'" data-post-vote="true"  class="links vote-answer"  ';
					$postLikeLink =  '<span>(<a '.($voteUpAllowed? '' : 'hidden="hidden"').' href="/vote-answer/upvote/'.$postId.'" data-action="upvote" data-state="'.($hasUpped? 1 : 0).'"  data-count="'.$totUppers.'" '.$voteDatas.' title="Up vote this post"><i class="far fa-thumbs-up '.($hasUpped? $activeStateClass : '').'"></i></a>'.($voteUpAllowed? '&nbsp;' : '').'
										<b id="lkc-'.$postId.'" class="cyan">'.$likes.((!$voteDownAllowed && !$voteUpAllowed)? (($likesT == 1 || $likesT == -1)?  ' Vote' : ' Votes') : '').'</b>
										'.($voteDownAllowed? '&nbsp;' : '').'<a '.($voteDownAllowed? '' : 'hidden="hidden"').'href="/vote-answer/downvote/'.$postId.'" data-action="downvote" data-state="'.($hasDowned? 1 : 0).'"  data-count="'.$totDowners.'" '.$voteDatas.' title="Down vote this post"><i class="far fa-thumbs-down '.($hasDowned? $activeStateClass : '').'"></i></a>)</span>';

				}else{
					
					$postLikeLink = $voteCounts;
				}
			
			}elseif($likes){
				
				$postLikeLink = $voteCounts;
				
			}
			
			$shareCounts = $shares? '<span>(<b class="cyan">'.trim($shares).'</b>)</span>' : '';
			$hasShared = (trim(strtolower($shareStat)) == "share")? false : true;
		
			if($sessUsername && $postAuthorized){
		
				$shareDatas = '  data-pid="'.$postId.'"  data-count="'.$totShares.'"  data-disp="shc-'.$postId.'" data-css="'.$activeStateClass.'" class="links share'.($hasShared? $activeStateClass : '').'" ';
				if(!$hasShared)
					$postShareLink = '<span>(<b id="shc-'.$postId.'" class="cyan">'.$shares.'</b><a href="/post-shares/share/'.$postId.'" data-action="share" '.$shareDatas.' title="Share this post with all your followers"><i class="far fa-share-square '.$activeStateClass.'"></i><span class="dsk-platform-dpn-ix">&nbsp;'.$shareStat.'</span></a>)</span>';
				else
					$postShareLink = '<span>(<b id="shc-'.$postId.'" class="cyan">'.$shares.'</b><a href="/post-shares/unshare/'.$postId.'" data-action="unshare" '.$shareDatas.' title="Unshare this post"><i class="far fa-share-square '.$activeStateClass.'"></i><span class="dsk-platform-dpn-ix">&nbsp;'.$shareStat.'</span></a>)</span>';

			}elseif($shares){
				
				$postShareLink = $shareCounts;
		
			}	
			
			$postSocialSharingToggler = '<span>(<a href="javascript:void(0)" class="links has-caret" data-align-to-context="true" data-toggle="smartToggler" data-id-targets="soc-mod-'.$postId.'" data-toggle-child-attr-class="caret-down" data-toggle-child-attr="class|up" title="Share this post on social websites" >Socials<i class="caret-down caret-static caret-xs pos-mid"></i></a>'.$postSocialCounts.')</span>';
			
			if($sessUsername){
		
				if($flagPost && $flags != MAX_POST_FLAGS)
					$postReportLink = '<span>(<a href="/report/post/'.$postId.'" class="links"  title="Flag or report this post" ><i class="fas fa-flag"></i><span class="dsk-platform-dpn-ix">&nbsp;Flag</span></a>'.(($flags)? ' <b class="cyan">'.$this->ENGINE->format_number($flags).'</b>' : '').')</span>';

			}	
		
			$socMetasArr = $row;
			$socMetasArr["type"] = 'post';
			$socMetasArr["alt"] = true;
			$socMetasArr["pid"] = $postId;
			$socMetasArr["tid"] = $topicId;
			$socMetasArr["tname"] = $topicName;
			$socMetasArr["tslug"] = $topicSlug;
			$socMetasArr["rdr"] = $rdr;
			$postSocialShareLinks = '<div class="modal-drop hide has-close-btn no-close-btn-tp" id="soc-mod-'.$postId.'">'.$this->SITE->getSocialLinks($socMetasArr, true).'</div>';
					
			
			/////HIDE ALL INTERRACTIVE LINKS IF TOPIC IS CLOSED AND USER IS NOT A STAFF//////
			if($topicClosed && !$this->ACCOUNT->sessionAccess(array('id'=>$topicId)))
				$postEditLink=$postQuoteLink=$postMultiQuoteLink=$postReportLink = '';
			
			
			/////HANDLE RETURN TYPES/////////
			$typeHeader=$liked_shared_by=$liked_shared_more="";
			
			if(in_array($type, array("upvotes", "downvotes", "shares"))){
						
				$ls_by_ret_arr = $this->decodeLS($row, $type);		
				$lsType = ($type == "upvotes")? 'upvoted ' : (($type == "downvotes")? 'downvoted ' : 'shared ');
		
				if(isset($ls_by_ret_arr["lsMore"]))			
					$liked_shared_more = trim($ls_by_ret_arr["lsMore"], " | ");
		
				if(isset($ls_by_ret_arr["ls"]))	
					$liked_shared_by = trim($ls_by_ret_arr["ls"], " | ");
							
				$typeHeader = 'Last '.$lsType.' by: '.$liked_shared_by.
				($this->SESS->isStaff()? '<a role="button" href="/post-event-history/'.$type.'/'.$postId.'" class="btn btn-xs btn-info">view all</a>' : '') ;								
					
				
			}elseif($type == "postsyouliked" || $type == "postsyoudv" ){
		
				$txtLDV = ($type == "postsyouliked")? "upvoted" : "downvoted";
				$date = ($type == "postsyouliked")? $this->decodeLS($row, "upvotes", true) : $this->decodeLS($row, "downvotes", true);
						
				$typeHeader = $sessUrl.' '.$txtLDV.' this post '.$date;

			}elseif($type == "postsyoushared"){

				$date  = $this->decodeLS($row, "shares", true);	
				$typeHeader = $sessUrl.' shared this post '.$date;
				
			}
			
			///THEN GENERATE THE FINAL OUTPUT TO THE WEB PAGE//////
					
			/////GET MODS CTRL///////
			list($postCms, $postCmsStickers, $topicCms, $topicCmsStickers) = $this->getModerationControls($modsCtrlMetaArr);				
			
			/////IF A POST IS HIDDEN SHOW RESTRICTION TEXT INSTEAD//
			if(($postHidden && !$this->ACCOUNT->sessionAccess(array('id'=>$topicId))) || !$this->ACCOUNT->sessionAccess(array('id'=>$topicId, 'cond'=>'staffsOnly'))){			
				
				if(in_array($sessPrivilege, array('', GUEST, MEMBER))){

					$messageBody =  $postUnavailable = $postUnavailableAlert;
					$uploadedFiles=$userBadges=$signsStars=$postCmsStickers=$postAuthor=$dp=$postTimeView="";

				}

				$postEditLink=$postQuoteLink=$postMultiQuoteLink=$postReportLink=$postSocialSharingToggler= 
				$postLikeLink=$postShareLink='';

			}
			
			/////IF USER IS NOT AUTHORIZED TO VIEW THREAD AT ALL///////
			if(!$viewAuthorized){
		
				$messageBody =  $postUnavailable =  $postUnavailableAlert;
				$uploadedFiles=$userBadges=$signsStars=$postCmsStickers=$postAuthor=$dp=$postTimeView=
				$postEditLink=$postQuoteLink=$postMultiQuoteLink=$postReportLink = '';
		
			}

			////IF A POST IS LOCKED////		
			if($postLocked && !$this->ACCOUNT->sessionAccess(array('id'=>$topicId)))
				$postEditLink=$postQuoteLink=$postMultiQuoteLink=$postReportLink=$postShareLink=$postLikeLink = '';		
			
			////IF A USER HAS ONLY VIEW PRIVILEGES DISABLE SUBNAV///////
			if(!$postAuthorized)
				$postEditLink=$postQuoteLink=$postMultiQuoteLink=$postReportLink=$postSocialSharingToggler='';
							
			//$messageBody = $this->ENGINE->htmlProtectedRendering($messageBody);
			
			/*******
				IT'S VITAL TO DECODE BBC BEFORE HIGHLIGHTING ANY KEYWORDS
				SO AS TO EXTEND DECODED BBC IN HIGHLIGHTING REGEX
			*********/
			
			/*********DECODE BBC*********/	
			$messageBody = $this->SITE->bbcHandler('', array('action' => 'decode', 'content' => $messageBody));
		
			/*********REPLACE NEW LINES WITH LINE BREAK********/		
			$messageBody = nl2br($messageBody);		
		
							
			/************HIGHLIGHT KEYWORDS IF ANY KEYWORD*************/	
			if($highlighKeywords){
							
				$postAuthorHighlighted = $postAuthorUsername;

				$keywordsArr = explode(" ", $highlighKeywords);
				
				foreach($keywordsArr as $keyword){
					
					if(mb_strlen($keyword) <= 2 || $this->SITE->inSearchKeywordExceptions($keyword))
						continue;
					
					if(!isset($postUnavailable)){
		
						$messageBody = $this->SITE->highlightText($messageBody, $keyword);	
						$postAuthorHighlighted = $this->SITE->highlightText($postAuthorHighlighted, $keyword);
		
					}				
			
					$topicName = $this->SITE->highlightText($topicName, $keyword);		
					
					
				}
				
				$postAuthorMetaArr['urlText'] = $postAuthorHighlighted;					
				$postAuthor =  (!isset($postUnavailable))? ' by '.$this->ACCOUNT->sanitizeUserSlug($postAuthorUsername, $postAuthorMetaArr).' ' : '';
					
			}				
					
					
							
			$postSubNav =  $sessUsername? $postEditLink.$postQuoteLink.$postMultiQuoteLink.$postLikeLink.$postShareLink.$postSocialSharingToggler.$postReportLink.$postSocialShareLinks
				: $postLikeLink.$postShareLink.$postSocialSharingToggler.$postSocialShareLinks;			
				
			$postSubNav = '<div class="post-sub-nav no-hover-bg" >'.$postSubNav.'</div>';
			
			$typeHeader = $typeHeader? '<div class="lgreen post-time post-body-ctrl">'.$typeHeader.'</div>' : '';
			
			$prefixBreadCrumbs = $forOutsideMainThread;
			
			
			$messageBody = $uiBgStyles? '<div class="_uibg-render _uibg-render-area" style="'.$this->SITE->uiBgHandler('', array('id' => $uiBgId, 'styles' => $uiBgStyles, 'action' => 'decodeStyle')).'"><div class="_uibg-content-area" style="'.$uiBgContentStyles.'">'.$messageBody.'</div></div>' : $messageBody;
			$syndicateUrl? ($messageBody .= '<div class="alert alert-warning">This content originated from '.$this->ENGINE->add_http_protocol($syndicateUrl, $anchor=true, $external=true).'</div>') : '';
			($postNumber > 1)? ($topicName = 'RE: '.$topicName) : '';
			
			//$topicName = $this->ENGINE->cloak($topicName, '50:200', -10, '.');

			//class="pslv-start" is used to highlight a focused post via css///
			
			if($isTypePins){

				list($pinSticker, $pinIcon, $pinTitle) = $this->SITE->getPinIcon($PPU, $pinTime, $pinCount);
				$messages .= '<div data-pin-number="'.$pinCount.'" data-navigate="'.$topicSlug.'" class="pinned-post" '.$pinTitle.'>'.$messageBody.'</div>';

			}else
			$messages .= '<span class="pslv-start" id="'.$postHashId.'"></span>
						<article class="postli clear p-'.$postId.'-base'.($postHidden? ' dimmed' : '').'" id="'.$postNumber.'">'.
							$typeHeader.'
							<header class="posthead post-body-ctrl clear">'.
								$dp.
								'<span class="posthead-breadcrumbs">'.
									($prefixBreadCrumbs? '
										<span class="dsk-platform-dpn-i">
											<a href="/'.$categSlug.'" '.$categTitle.' class="links">'.$categName.'</a> <span class="lblack">></span> 
											<a href="/'.$sectionSlug.'"  '.$sectionTitle.'  class="links">'.$sectionName.'</a> <span class="lblack">></span> 
										</span>' : ''
									).'
									<span data-pid="'.$postId.'"  class="base-r-mg" >'.$postNumber.'.</span>
									<a href="'.$topicSlug.'"  '.$threadTitle.' class="links">'.$topicName.'</a>
								</span>
								<span class="post-head-min">
									<span class="sv-txt-case">'.$postAuthor.$postTimeView.'</span>
								</span>
							</header><hr/>
							<div class="post-body post-body-ctrl">'.$messageBody.$postSubNav.'</div>
							<div class="posted-files" >'.$uploadedFiles.$postCms.'</div>'.
							$postCmsStickers.$userBadges.$signsStars.'
						</article>';

			$pinCount++; // increment pin number count
								
		}

		if($isTypePins){
			
			$pinnedPostsWidgetId = 'topic-'.$topicId.'-pinned-posts-widget';
			
			$messages = ($messages? 

					'<div class="pinned-posts">
						<h3 class="prime" title="pinned posts">'.$pinIcon.'PINS'.$pinIcon.'</h3>
						<div id="'.$pinnedPostsWidgetId.'" class="slide-show" data-auto-play="true" data-speed="8000" data-animate="slideInRight" data-pager-numbers="false" data-pager-arrow="false" data-pager-crumbs="true" data-pager-crumbs-style="tile" data-pager-crumbs-top="true">'
						.$messages.
					'	</div>
					</div><hr/>' : '');

		}else		
			$messages = '<div class="row-pad">'.($messages? $messages : '<div class="alert alert-danger">Sorry no post has been made to this thread yet</div>').'</div>';
		
		return array($messages, $postId, $topicId, $sid, $cid, $sectionName, $categName);
			
		
	}
	 
	 




    	
	/*** Method for decoding upvoters, downvoters and sharers usernames and time ***/
	public function decodeLS($row, $type, $retDate=false){
		
		$liked_shared_by=$liked_shared_more="";
		
		$lsCol = ($type == "upvotes")? "UPPER" : (($type == "downvotes")? "DOWNER" : "SHARER");
		$lsCol2 = ($type == "upvotes")? "UTIME" : (($type == "downvotes")? "DTIME" : "STIME");
		$lsType = ($type == "upvotes")? "Upvoted" : (($type == "downvotes")? "Downvoted" : "Shared");
		
		$voters_sharers = $row[$lsCol];
		$voters_sharersT = $row[$lsCol2];
		$voters_sharers_arr = explode(',', $voters_sharers);
		$voters_sharersT_arr = explode(',', $voters_sharersT);
		
		for($idx=0; $idx < count($voters_sharers_arr); $idx++ ){
		
			$liked_shared_uid = $voters_sharers_arr[$idx];											
			$likedShared_by  = $this->ACCOUNT->memberIdToggle($liked_shared_uid);			
			$like_share_time = $voters_sharersT_arr[$idx];
			$date =  $this->ENGINE->time_ago($like_share_time);
			$infos = $this->ACCOUNT->sanitizeUserSlug($likedShared_by, array('anchor'=>true, 'gender'=>true)).' '.$date.' | ';
		
			if($idx >= 5)
				$liked_shared_more .= $infos;
		
			else
				$liked_shared_by .= $infos;
			
			if($retDate && $liked_shared_uid == $this->SESS->getUserId())
				break;
		
		}
		
		if($retDate)
			return $date;
		
		else
			return array("ls" => $liked_shared_by, "lsMore" => $liked_shared_more );
		
	}




	
	/*** Method for clearing multi-checked post after multi-quoting ***/
	public function clearMultiQuoteHighlights($userId){
											
		////CLEAR MULTI-QUOTE CHECKS (IF ANY) AFTER EVERY POST////

		///////////PDO QUERY///////////////			
	
		$sql = "DELETE FROM mq_trackers WHERE USER_ID=?";
		$valArr = array($userId);
		return $this->DBM->doSecuredQuery($sql, $valArr);
		

	}
	




	
		
	/*** Method for fetching post quoted, upvoted, downvoted or shared as well as a tab link to them ***/
	public function getPostQUDS($userId, $type='', $retType='', $cssClass='cyan'){
		
		$return="";
		
		!$type? ($type = 'tab') : '';
		$retType = strtolower($retType);
		$type = strtolower($type);
		
		if(in_array($type, array($upvotes='u_upvoted', $downvotes='u_downvoted', $shares='u_shared', $quotesMentions='u_quoted_mentioned', $sharedWithU='shared_with_u'))){
			
			$forUpvotes = ($type == $upvotes);
			
			switch($type){
				
				case $upvotes:
				
				case $downvotes:
					$totalCount = $this->votesHandler(array('type'=>($forUpvotes? 'u' : 'd'), 'uid'=>$userId, 'voter'=>true));
					break;
				
				case $shares:
					$totalCount = $this->sharesHandler(array('uid'=>$userId,'sharer'=>true));
					break;
				
				case $quotesMentions:
					///////////PDO QUERY/////////
					$sql = "SELECT COUNT(*) FROM posts WHERE POST_AUTHOR_ID = ? AND MESSAGE LIKE ? ";
					$valArr = array($userId, "%Author=%");
					$totalCount = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
					break;
				
				case $sharedWithU:
					///////////PDO QUERY/////////
					$fmIdsSubQry = "SELECT USER_ID FROM members_follows WHERE FOLLOWER_ID=? AND STATE=1";				
					$sql = "SELECT COUNT(*) FROM posts WHERE ID IN(SELECT POST_ID FROM shares WHERE SHARER_ID IN (".$fmIdsSubQry."))";
					$valArr = array($this->SESS->getUserId());
					$totalCount = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
					break;
				
			}
			
			$customTotalCount = '(<span class="'.$cssClass.'"><b>'.$totalCount.'</b> post'.(($totalCount > 1)? 's' : '').'</span>)';
			
			if($retType == "count")
				return $totalCount;
			
			elseif($retType == "versions")
				return array($totalCount, $customTotalCount);
			
			else
				return $customTotalCount;
			
			
		}elseif($type == "tab"){
		
			$lC=$dvC=$sC=$swuC=$qC="";
			$activeCls = ' active ';
			$ind = 0;
		
			switch($retType){
		
				case $upvotes: $lC = $activeCls;  break;
		
				case $downvotes: $dvC = $activeCls; break;
		
				case $shares: $sC = $activeCls;  $ind = 1; break;
		
				case $sharedWithU: $swuC = $activeCls;  $ind = -3;  break;
		
				case $quotesMentions: $qC = $activeCls; $ind = -3; break;
		
			}
			
			$return_arr[] = '<li class="'.$lC.'"><a  href="/posts-you-upvoted" >Posts You\'ve Upvoted '.$this->getPostQUDS($userId, $upvotes, '', '').'</a></li>';				
			$return_arr[] = '<li class="'.$dvC.'"><a  href="/posts-you-downvoted" >Posts You\'ve Downvoted '.$this->getPostQUDS($userId, $downvotes, '', '').'</a></li>';		
			$return_arr[] = '<li class="'.$sC.'"><a   href="/posts-you-shared" >Posts You\'ve Shared '.$this->getPostQUDS($userId, $shares, '', '').'</a></li>';	
			$return_arr[] = '<li class="'.$swuC.'"><a  href="/posts-shared-with-you"  >Posts Shared With You'.$this->getPostQUDS($userId, $sharedWithU, '', '').'</a></li>';
			$return_arr[] = '<li class="'.$qC.'"><a  href="/posts-you-quoted"  >Posts You\'ve Quoted '.$this->getPostQUDS($userId, $quotesMentions, '', '').'</a></li>';		
			
			$retTmtp_arr = array_slice($return_arr, $ind, 3);
			$return = implode("", $retTmtp_arr);
			
			$return = '<nav class="nav-base"><ul class="nav nav-pills justified-center">'.$return.'</ul></nav>';
		
		}

		
		return $return;
		
		
	}







    
	/*** Method for handling file upload in a post ***/
	public function uploadPostFiles($oldUploadsCount=0){
		
		global $GLOBAL_mediaRootPostXCL;
		
		$userUploads=$originalUserUploads=$error='';
		$uok=1;///VERY IMPORTANT JUST INCASE NO FILE IS BEING POSTED/////											
																																															
		###UPLOAD FILES IF ANY###
		##PRECONFIG UPLOADER
		$htmlName = 'fileups';
		$uploadpath = $GLOBAL_mediaRootPostXCL;
		$allowedExtArr = array();##allow all file types
		$sizeLimit =  ($this->ENGINE->sanitize_number(MAX_POST_UPLOAD_SIZE_STR) * ONE_KB_BYTE**2);
		$imgWidthLimit = "";##
		$imgHeightLimit = "";##
		$uploadInfo = '';
		$uploadType = 'multiple'; //UPLOADER INTELLIGENTLY DECODES THE TYPES
		$maxUploads = 10;  
		$maxUploadsCmt = $oldUploadsCount? 'files more for this post' : 'files per post';   
		
		$FU = new FileUploader($htmlName, $uploadpath, $allowedExtArr, $sizeLimit);
		
		//EXPLICITLY SET OVERWRITE TO FALSE(THOUGH DEFAULT == false)
		$FU->setOverwrite(false);	
		
		//RENAME FILES
		$FU->setRename(true);
																										
		//LIMIT EACH POST UPLOADS TO $maxUploads
		$FU->setMaxUploads($maxUploads, $oldUploadsCount, $maxUploadsCmt);																								
		
		if($FU->fileIsSelected()){
			
			$FU->upload();
			$uok = $FU->getUploadStatus();	
			$error = $FU->getErrors();												
			$userUploads = $FU->getUploadedFiles(true);//RETURN UPLOADED FILES AS AN ARRAY
			$originalUserUploads = $FU->getOriginalFileNames(true);//RETURN UPLOADED ORIGINAL FILE NAMES AS AN ARRAY
			
		}
		
		return array($userUploads, $originalUserUploads, $uok, $error);
		
	}



	
	 
	
	/*** Method for pushing, deleting and counting uploaded post files from database ***/
	public function postedFilesHandler($metaArr){
		
		global $GLOBAL_mediaRootPostXCL;
		
		$i = 0;
		$stat = true;
		$pid = $this->ENGINE->get_assoc_arr($metaArr, 'pid');
		$filesArr = $this->ENGINE->get_assoc_arr($metaArr, 'files');	
		$originalFilesArr = $this->ENGINE->get_assoc_arr($metaArr, 'fileOriginalNames');	
		$count = $this->ENGINE->get_assoc_arr($metaArr, 'count');
		$unlinkOnly = $this->ENGINE->get_assoc_arr($metaArr, 'unlinkOnly');
		$del = $this->ENGINE->get_assoc_arr($metaArr, 'del');
		
		if(is_array($filesArr)) 
			list($filesArr) = $filesArr;//ENGINE RETURNS ARRAY AS AN ARRAY(1 == 2 dimensional)
		
		else 
			$filesArr = explode(GRPSEP, $filesArr);
		
		if(is_array($originalFilesArr)) 
			list($originalFilesArr) = $originalFilesArr;//ENGINE RETURNS ARRAY AS AN ARRAY(1 == 2 dimensional)
		
		else 
			$originalFilesArr = explode(GRPSEP, $originalFilesArr);
		
		if($count){
			
			$sql = "SELECT COUNT(*) FROM post_uploads ".($pid? "WHERE POST_ID=?" : "");
			$valArr = array($pid);
			return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
			
		}else
			foreach($filesArr as $file){
				
				if(!$file){
					
					$i++;
					continue;
					
				}
				
				$originalFile = isset($originalFilesArr[$i])? $originalFilesArr[$i] : '';
				
				if(!$unlinkOnly){
					
					$valArr = array($pid, $file);	
					$del? '' : ($valArr[] = $originalFile);	
					$sql = $del? "DELETE FROM post_uploads WHERE POST_ID=? AND FILE=? LIMIT 1" :
							"INSERT INTO post_uploads (POST_ID, FILE, ORIGINAL_FILE) VALUES(?,?,?)";
					$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
					
				}
				
				if($del || $unlinkOnly){
					
					////DELETE THE FILE FROM SERVER///////																								
					$path2del = $GLOBAL_mediaRootPostXCL.$file;
					
					if(realpath($path2del) && $file)
						unlink($path2del);	
					
				}

				$i++;
						
			}
			
		return $stat;
		
	}








	
		
	/*** Method for fetching uploaded post files from database ***/
	public function getPostUploads($postUploads, $metaArr){
		
		global $GLOBAL_mediaRootFav, $GLOBAL_mediaRootPost, $GLOBAL_mediaRootPostXCL;
		
		$fileOriginalNames = $this->ENGINE->get_assoc_arr($metaArr, 'fileOriginalNames');
		$vds = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'vds');
		$useBgImg = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'useBgImg');
		$blankNone = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'blankNone');
		$hiddenPost = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'hiddenPost');
		$hideImgs = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'hideImgs');
		$type = $this->ENGINE->get_assoc_arr($metaArr, 'type');
		$tid = $this->ENGINE->get_assoc_arr($metaArr, 'tid');
		$pid = $this->ENGINE->get_assoc_arr($metaArr, 'pid');
		$topicSlug = $this->ENGINE->get_assoc_arr($metaArr, 'slug');
		$topicSlugHash = $this->ENGINE->get_assoc_arr($metaArr, 'slugHash');
		
		$return=$imgFiles=$otherFiles=$mimetype=$blankImg="";		
		$fpThumbsnailArr=array();
		$imgsHidden=false;
		$blankNone = ($postUploads && !$hiddenPost)? false : $blankNone;
		$GLOBAL_username = $this->SESS->getUsername();
		$showPostImagesToUser = $this->SESS->getShowPostImages();
		$mediaRootPost = $GLOBAL_mediaRootPost;
		$mediaRootPostXCL = $GLOBAL_mediaRootPostXCL;
		$hoverRxBaseCls = ' _hv-react-base ';
		$hoverRxCls = ' _hv-react ';
		$icls = 'bg-thumbnail';
		//$ocls = 'bg-thumbnail'.$hoverRxBaseCls;
		$blankImgSrc = $GLOBAL_mediaRootFav.'blank-thumbnail.png';
		$blankImgTitle = 'no image';
		
		$blankImg = $useBgImg? $this->SITE->getBgImg(array('file'=>$blankImgSrc, 'title'=>$blankImgTitle, 'type'=>'bg', 'anchor'=>false, 'hoverReact'=>true, 'icls'=>$icls))
				: '<div class="'.$hoverRxBaseCls.'"><img class="img-responsive'.$hoverRxCls.'"  alt="'.$blankImgTitle.'"   src="'.$blankImgSrc.'" /></div>';
			
		if(!$blankNone && $postUploads){
			
			$path = $mediaRootPostXCL;
			$files = explode(GRPSEP, $postUploads);		
			$fileOriginalNames = explode(GRPSEP, $fileOriginalNames);		
			$fileLen = count ($files);
			
			$ii = $imgCount = 0; $c = 1;
			
			while($ii < $fileLen){
				
				$file = $files[$ii];
				$fileOriginalName = $fileOriginalNames[$ii];
				
				/****ENSURE THERE ARE FILES IN ARRAY********/				
				if(!$file){
					
					$ii++;
					continue;
					
				}				
									
				if(realpath($path.$file)){
					
					$mimetype = $this->ENGINE->mime_content_type($path.$file);
					
					$mimetypeArr = explode("/", $mimetype);
					
					$mimetypeArr = array_shift($mimetypeArr);
							
					if($mimetypeArr == "image"){
						
						$imgCount++;
						
						if((!$showPostImagesToUser && $GLOBAL_username) || $hideImgs){
							
							$ii++;
							$imgsHidden = true;
							continue;
							
						}
						
						$fileTitle = 'file '.$c++.': '.($fileOriginalName? $fileOriginalName : $file);
						$fileTitleAttr = ' title="'.$fileTitle.'"';
						$imgFiles .= '<div class="pim-limit'.$hoverRxBaseCls.'"><a class="links" href="'.$this->SITE->getDownloadURL($file, "post").'" '.$fileTitleAttr.' ><img  class="img-responsive pim zoom-ctrl'.$hoverRxCls.'"  alt="shared file"  src="'.$mediaRootPost.$file.'" /></a></div>';					
						$fpThumbsnailArr[]  = $useBgImg? $this->SITE->getBgImg(array('file'=>$mediaRootPost.$file, 'title'=>$fileTitle, 'type'=>'bg', 'anchor'=>false, 'hoverReact'=>true, 'icls'=>$icls))
											: '<div class="'.$hoverRxBaseCls.'"><img '.$fileTitleAttr.' class="img-responsive'.$hoverRxCls.' pim zoom-ctrl"  alt="shared file"  src="'.$mediaRootPost.$file.'" /></div>';
					}else
						$otherFiles .= '<div class=""><a class="links" href="'.$this->SITE->getDownloadURL($file, "post").'">'.$file.'</a></div>';
				}
				
				$ii++;
				
			
			}
			
			($imgCount > 1)? ($imgFiles = '<div class="pim-carousel">'.$imgFiles.'</div>') : '';
			
			if($vds && $imgFiles && ($showPostImagesToUser || !$GLOBAL_username)){
				
				$fpThumbsnailArr = array();
				$randVDS = mt_rand(1, 7);
				$vdsFile = $GLOBAL_mediaRootFav.'default-vds-'.$randVDS;
				$vdsFileTitle = 'Viewers Discretion Warning';
				$vdsRmbToken = 'VDS_'.$tid.'_'.$pid;
				
				if(isset($_GET[$vdsRmbToken])){
					
					$hideCls = $vdsOkBtn = '';
					
				} 
				
				else{
					
					$hideCls = 'hide';
					$vdsOkBtn = '<div class="alert alert-danger">
									ATTENTION! Images in this post may contain disturbing contents or graphics. Viewer Discretion is Advised.
									<a class="btn btn-danger" href="'.$topicSlug.'?'.$vdsRmbToken.'=ok'.$topicSlugHash.'" data-toggle="smartToggler" data-id-targets="'.$vdsRmbToken.'" data-toggle-attr="text|hide images" >OK show images</a>
								</div>';
								
				}
				
				$imgFiles = '<div class="vds-warning'.$hoverRxBaseCls.'"><img class="img-responsive pim no-pointer-event'.$hoverRxCls.'" alt="'.$vdsFileTitle.'" src="'.$vdsFile.'" /></div>'.$vdsOkBtn.'<div class="'.$hideCls.'" id="'.$vdsRmbToken.'">'.$imgFiles.'</div>';			
				$fpThumbsnailArr[] = $useBgImg? $this->SITE->getBgImg(array('file'=>$vdsFile, 'title'=>$vdsFileTitle, 'type'=>'bg', 'anchor'=>false, 'hoverReact'=>true, 'icls'=>$icls))
									: '<div class="'.$hoverRxBaseCls.'"><img class="img-responsive'.$hoverRxCls.'" alt="'.$vdsFileTitle.'" src="'.$vdsFile.'" /></div>';
									
			}
			
			if($type == "thumbnail"){
				
				if(!empty($fpThumbsnailArr)){
					
					shuffle($fpThumbsnailArr);
					$return = $fpThumbsnailArr[0];
					
				}
				
				!$return? ($return = $blankImg) : '';
				
			}else
				$return = ($imgsHidden? '<div class="alert alert-danger">
								ATTENTION!!! Due to your preference settings, Images or graphics in this post may have been hidden.
							</div>' : '').$imgFiles.$otherFiles;	
							
		}
		
		return ($blankNone? $blankImg : $return);
		
	}
	 
	 
	 



	

	/*** Method for checking if a user has selected a post for multi quoting ***/
	public function highlightedForMultiQuote($uid, $pid){
			
		$U = $this->ACCOUNT->loadUser($uid);
		$uid = $U->getUserId();
		
		///////////PDO QUERY//////
			
		$sql = "SELECT ID FROM mq_trackers WHERE USER_ID = ? AND POST_ID = ? LIMIT 1";
		$valArr = array($uid, $pid);
		
		return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
	}





	/*** Method for decoding and fetching post status ***/
	public function getPostStatus($pid){
		
		$isLocked=$locker=$isHidden=$hider=$isPinned=$pinner=$statAll=$statAllUid='';

		$actionCols = implode(',', array("LOCKED", "HIDDEN", "PINNED"));
		
		/////////PDO QUERY//////
		
		$sql = "SELECT ".$actionCols." FROM posts WHERE ID = ? LIMIT 1";
		$valArr = array($pid);
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		$row = $this->DBM->fetchRow($stmt);		

		if(!empty($row)){

			list($isLocked, $locker) = $this->decodeModerationStatus($row["LOCKED"]);
			list($isHidden, $hider) = $this->decodeModerationStatus($row["HIDDEN"]);
			list($isPinned, $pinner) = $this->decodeModerationStatus($row["PINNED"]);
			
			$sep = ', ';
			
			$statAll = $isLocked;		
			$statAll .= ($statAll? $sep : '').$isHidden;
			$statAll .= ($statAll? $sep : '').$isPinned;
			
			$statAllUid = $locker;		
			$statAllUid .= ($statAllUid? $sep : '').$hider;
			$statAllUid .= ($statAllUid? $sep : '').$pinner;
			
		}

		return array(
			 
			'isLocked' => $isLocked, 'locker' => $locker, 'isHidden' => $isHidden, "hider" => $hider, 
			'isPinned' => $isPinned, "pinner" => $pinner, 'all' => $statAll, 'allUid' => $statAllUid
		);

		 
	}



	
		
	/*** Method for updating/executing final post moderation actions using user ranking system ***/
	public function doPostActionByRank($metaArr){
		
		$kArr = array("pid", "tid", "doTxt", "undoTxt", "dbCol", "doSubQry", "undoSubQry", 
		"currState", "currStateUid", "valArr", "doState", "undoState", "hasDoStateOnly", "forceUndoState");

		list($pid, $tid, $stateApplyTxt, $stateReverseTxt, $statusDbCol, $stateApplySubQry, $stateReverseSubQry, 
		$stat, $statUid, $valArr, $doState, $undoState, $hasDoStateOnly, $forceUndoState) = $this->ENGINE->get_assoc_arr($metaArr, $kArr);

		$alertUser = '';	
		$logActivity = false;			
			
		list($stateApply, $stateReverse) = $this->getModerationStateStr($doState, $undoState);	
		
		//State Reverse/Undo
		if(($stat || $forceUndoState) && !$hasDoStateOnly){
			
			$newStat = $stateReverse;
			$typeSubQry = $stateReverseSubQry;
			$doneActionTxt = $stateReverseTxt;	
			$cls = 'red';	

		}else{//State Apply/Do

			$newStat = $stateApply;						
			$typeSubQry = $stateApplySubQry;
			$doneActionTxt = $stateApplyTxt;
			$cls = 'green';

		}
		
		list($ranksHigher, $ranksEqual) = $this->ACCOUNT->sessionRanksHigher($statUid);									
		
		if($ranksHigher){
				
			$valArr[] = $pid;
			$valArr[] = $tid;

			///PDO QUERY//////
		
			$sql = "UPDATE posts SET ".$statusDbCol." = ? ".($typeSubQry? ','.$typeSubQry : '')." WHERE ID = ? AND TOPIC_ID = ? LIMIT 1";
			$valArr = array_merge(array($newStat), $valArr);
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);

			$logActivity = true;				
			$alertUser = '<span class="alert alert-success">Post was successfully <span class="'.$cls.' bg-white">'.$doneActionTxt.'</span></span>';
						
		}else
			$alertUser = '<span class="alert alert-danger">Privilege Overridden!..... your rank could not override the current state of the post.</span>';
				
		
		return array($alertUser, $logActivity, $doneActionTxt);
		
	}

	



	

}


?>