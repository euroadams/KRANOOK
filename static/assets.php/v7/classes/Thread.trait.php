<?php


trait Thread{
	
	
	/************************************************************************************/
	/************************************************************************************
										METHODS
	/************************************************************************************
	/************************************************************************************/
		
		




		
	/*** Method for fetching thread/topic slug ***/
	public function getThreadSlug($tid, $preSlash=true, $sufSlash=true){
		
		$sql =  "SELECT ID, TOPIC_NAME, TIME, YEAR(TIME) YEAR_CREATED, MONTH(TIME) MONTH_CREATED, DAY(TIME) DAY_CREATED FROM topics WHERE ID=? LIMIT 1";
		$valArr = array($tid);
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		$row = $this->DBM->fetchRow($stmt);
		
		if(!empty($row)){
		
			$tid = $row["ID"];
			$yearCreated = $row["YEAR_CREATED"];
			$monthCreated = $row["MONTH_CREATED"];
			$dayCreated = $row["DAY_CREATED"];
			$tname = $row["TOPIC_NAME"];
			$tnameSan = $this->ENGINE->sanitize_slug($tname);
			
			switch(strtolower(THREAD_SLUG_STYLE)){

				case 'timestamped': $style = $yearCreated.'/'.$monthCreated.'/'.$dayCreated.'/'; break;

				default: $style = '';

			}
			
			return (($preSlash? '/' : '').THREAD_SLUG_CONSTANT.'/'.$style.$tid.'/'.$tnameSan.($sufSlash? '/' : ''));
			
		}
		
		return '';

	}






	
	/*** Method for fetching a field from database topic table ***/
	public function getTopicDetail($param, $col="SECTION_ID"){
		
		/////PDO QUERY//////////
		
		$sql = 'SELECT '.$col.' FROM topics WHERE (ID = ? OR TOPIC_NAME = ?) LIMIT 1 ';
		$valArr = array($param, $param);
		$return = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
		return $return;

	}


	

	
	

	
		
	/*** Method for toggling topic id to topic name and vice versa ***/
	public function topicIdToggle($param){
		
		$return = '';
		
		/////PDO QUERY//////////
		
		$sql = 'SELECT ID, TOPIC_NAME FROM topics WHERE (ID = ? OR TOPIC_NAME = ?) LIMIT 1 ';
		$valArr = array($param, $param);
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		$row = $this->DBM->fetchRow($stmt);
		
		if(!empty($row)){
			
			$return = is_numeric($param)? $row["TOPIC_NAME"] : $row["ID"];
			
		}
		
		return $return;


	}



	
		
	/*** Method for checking if topic has been tagged hot ***/
	public function isHotTopic($topicId){

		///PDO QUERY//////
	
		$sql = "SELECT ID FROM topics WHERE HOT !=0 AND ID = ? LIMIT 1";
		$valArr = array($topicId);
		return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();

	}

	


	
		
	/*** Method for checking if topic has been pinned ***/
	public function isPinnedTopic($topicId){

		///PDO QUERY///////
		
		$sql = "SELECT ID FROM topics WHERE ID = ? AND PIN_TIME !=0 LIMIT 1";
		$valArr = array($topicId);
			
		return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();

	}
	
		


	
		
	/*** Method for checking if topic has been featured on front page ***/
	public function isFeaturedTopic($topicId){
		
		///PDO QUERY///////
		
		$sql = "SELECT ID FROM topics WHERE ID = ? AND FEATURE_TIME !=0 LIMIT 1";
		$valArr = array($topicId);
			
		return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();

	}


	
		
	/*** Method for collecting topic views (cron job) ***/
	public function collectThreadViews(){

		$interval = 1;
		$maxLim = $this->DBM->getMaxRowPerSelect();
		
		for($i=0; ; $i += $maxLim){
			
			$sql = "SELECT * FROM site_traffics WHERE (TOPIC_ID !=0 AND (ARRIVAL_TIME + INTERVAL ? MINUTE) < NOW()) LIMIT ".$i.",".$maxLim;
			$valArr = array($interval);
			$stmtx = $this->DBM->doSecuredQuery($sql, $valArr, true);
			
			/////IMPORTANT INFINITE LOOP CONTROL ////
			if(!$this->DBM->getSelectCount())
				break;
							
			while($row = $this->DBM->fetchRow($stmtx)){
				
				$userId = $row["USER_ID"];
				$topicId = $row["TOPIC_ID"];
				$pageOnView = $row["PAGE_ON_VIEW"];
				$ip = $row["IP"];
				$time = $row["ARRIVAL_TIME"];
				
				$sql = "SELECT IP FROM topic_views WHERE ".($userId? 'USER_ID=?' : 'IP=?')." AND TOPIC_ID=? LIMIT 1";

				$valArr = array(($userId? $userId : $ip), $topicId);
				
				if(!$this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn()){
									
					///PDO QUERY////
				
					$sql = "INSERT INTO topic_views (IP, TOPIC_ID, USER_ID, TIME) VALUES(?,?,?,?)";
					$valArr = array($ip, $topicId, $userId, $time);							
					$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
						
				}
			}			
			
		}
		 
	}
	 
	 
	 
	 
	 
	
		


	
		
	/*** Method for counting topic views ***/
	public function countThreadViews($topicId, $counted=0, $retCountsOnly=false){

		///GET THE TOPIC VIEW COUNT//////////
			
		////PDO QUERY//////
		
		if(!$counted){
		
			$sql = "SELECT COUNT(*) FROM topic_views WHERE TOPIC_ID = ?";
			$valArr = array($topicId);
			$counted = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
			
		}
		
		$counted = $this->ENGINE->format_number($counted);			
		$customCounts = $counted.' view'.(($counted == 1)? '' : 's');	
		
		if($retCountsOnly)		
			return $counted;
		
		else	
			return $customCounts;
		
	}
	 


	 
	 
	

	
		
	/*** Method for counting posts in a thread ***/
	public function countThreadPosts($topicId, $counted=0, $retCountsOnly=false){
	
		//PDO QUERY///////
		if(!$counted){
			
			$sql = "SELECT COUNT(*) FROM posts WHERE TOPIC_ID =?";
			$valArr = array($topicId);
			$counted = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
			
		}	
		
		$counted = $this->ENGINE->format_number($counted);
		
		$customCounts =  $counted.' post'.(($counted == 1)? '' : 's');

		if($retCountsOnly)	
			return $counted;

		else
			return $customCounts;
		
		
	}


	 




	

	/*** Method for loading threads ***/
	public function loadThreads($sql, $valArr, $type=""){
		
		//////VARIABLE INITIALIZATION/////
		$desc=$topic=$categName=$sectionName=$newPosts=$topics="";
			
		//////////GET DATABASE CONNECTION/////
		global $GLOBAL_page_self, $GLOBAL_rdr;
		
		$type = strtolower(trim($type));
		
		/*******GET REFERRING PAGE******************/
		$rdr = $GLOBAL_rdr;
		$pageSelf = $GLOBAL_page_self;
		$mediaRootFav = $this->SITE->getMediaLinkRoot("favicon");
		
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);

		while($row = $this->DBM->fetchRow($stmt)){
			
			////////LOOP THROUGH THE ENTIRE TOPICS IN THE SECTION////////
			$topicId = $row["ID"];
			$topic = $row["TOPIC_NAME"];												
			$topicStatArr = $this->getThreadStatus($topicId);
			$topicClosed = $topicStatArr["tClosed"];
			$isLockedTopic = $topicStatArr["isLocked"];
			$topicLocker = $topicStatArr["locker"];
			$isPinnedTopic = $topicStatArr["isPinned"];
			$topicPinner = $topicStatArr["pinner"];
			$sid = $row["SECTION_ID"];
			$protection = $row["PROTECTION_LEVEL"];
			$sectionName = $row["SECTION_NAME"];
			$sectionSlug = $this->ENGINE->sanitize_slug($sectionName);
			$cid = $row["CATEG_ID"];;
			$category = $row["CATEG_NAME"];
			$categorySlug = $this->ENGINE->sanitize_slug($category);	
			$topicSlug = $this->SITE->getThreadSlug($topicId);
			$categTitle = ' title="'.$category.' ('.$cid.')" ';
			$sectionTitle = ' title="'.$sectionName.' ('.$sid.')" ';
			$threadTitle = ' title="'.$topic.' ('.$topicId.')" ';
			//list($pinned, $pinner) = $this->decodeModerationStatus($row["PINNED"]);
			$pinTime = $row["PIN_TIME"];
			$pinIcon = $this->SITE->getPinIcon($topicPinner, $pinTime);

			///GET AUTHORIZATION///
			list($postAuthorized, $viewAuthorized, $protectionAlert) = $this->authorizeThreadAccess($topicId);							
					
			//////////GET THE TOTAL MESSAGES IN EVERY TOPIC//////////
			$topicPostCounts = $this->countThreadPosts($topicId, $row["TOTAL_POSTS"]);
			
			
			////////////////////////GET THE TOPIC VIEW COUNT/////////
			$topicViewCounts = $this->countThreadViews($topicId, $row["TOPIC_VIEWS"]);
			

			///PDO QUERY/////////////
			
			$sql = "SELECT COUNT(*) FROM posts WHERE TOPIC_ID=?";
			$valArr = array($topicId);
			$totalRecords = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
			
			/**********CREATE THE PAGINATION********/
			
			$pageUrl = $this->SITE->getThreadSlug($topicId);
			$paginationArr = $this->SITE->paginationHandler(array('totalRec' => $totalRecords, 'url' => $pageUrl, 'extendLast' => true,
			'jmpKey' => 'jmp_to_topic_'.$topicId, 'navigators' => false, 'activePage' => false, 'cssClass' => 'page-inline disc', 'nested'=>true, 'showRecordCrumbs' => false));	
			$pagination = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageId = $paginationArr["pageId"];


			////IF THIS FUNCTION WAS CALLED FROM FOLLOWED TOPICS/////////////

			if($type == "followed topics"){
				
				$lastSeenPostId = $this->followedTopicsHandler(array('uid'=>$this->SESS->getUserId(), 'tid'=>$topicId, 'action'=>'getlspn'));
				
				if(!$lastSeenPostId)
					$lastSeenPostId = 1;
				
				/////CHECK IF THERE IS A NEW POST SINCE THE USER'S LAST VISIT/////
				
				$sql =  "SELECT ID FROM posts WHERE TOPIC_ID=? ORDER BY TIME DESC LIMIT 1";
				$valArr = array($topicId);
				$latestPostId = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
						
				 
				if($latestPostId > $lastSeenPostId){
					
					$nextPostAfterLastSeen = ($this->getPostNumber($lastSeenPostId, $topicId, "") + 1);
					////COMPUTE THE NEW POST INDEX FROM LAST SEEN POST INDEX//////
					$metaDatas = $this->getPostPageNumber($nextPostAfterLastSeen).'#'.$topicId."_".$nextPostAfterLastSeen;
					
					//$newPosts = $this->SITE->getBgImg(array('file'=>$mediaRootFav.'new-post.png','title'=>'New Posts since your last visit','url'=>$this->SITE->getThreadSlug($topicId).$metaDatas));
					$newPosts = '<a href="'.$this->SITE->getThreadSlug($topicId).$metaDatas.'" role="button" class="btn btn-xs btn-success" title="Continue from where you left off on this topic">New <sup>+<sup></a>';
					
				}
				
				
			} 

			////////////////////////////////
			
			$postAuthor = $this->ACCOUNT->memberIdToggle($row["TOPIC_AUTHOR_ID"]);
			$lastPostAuthor = $this->ACCOUNT->memberIdToggle($row["LAST_POST_AUTHOR_ID"]);	
			
			$topic_author = ' by '.$this->ACCOUNT->sanitizeUserSlug($postAuthor, array('anchor'=>true, 'gender'=>true, 'cls'=>' sv-txt-case'));
			$postAuthor = 	'last post by '.$this->ACCOUNT->sanitizeUserSlug($lastPostAuthor, array('anchor'=>true, 'gender'=>true, 'cls'=>' sv-txt-case'));
			

			/////GET MODS CTRL/////
			
			$modsCtrlMetaArr = array("tid"=>$topicId, "tn"=>$topic, "section"=>$sectionName,"type"=>"topic", "rdr"=>$rdr, "cmsStickerCombo" => false);

			list($postCms, $postCmsStickers, $topicCms, $topicCmsStickers) = $this->getModerationControls($modsCtrlMetaArr);								
			
			////FORMAT THE WAY POST DATES ARE SHOWN//////
			
			$postTimeView =  '<span style="text-transform:none;">'. $this->ENGINE->time_ago($row["LAST_POST_TIME"]).'</span>';
			
			$unfollowLink = $this->SITE->getBgImg(array('file'=>$mediaRootFav.'delete.png', 'title'=>'Un-follow this topic','ocls'=>'follow_topic','url'=>'/topic-follows/unfollow/'.$topicId.'/?_rdr='.$rdr, 'oAttr'=>'data-tid="'.$topicId.'" data-action="unfollow" data-base="t-'.$topicId.'-base" data-count-dec="ft-count-disp"'));

			/////GET TOPIC STICKERS/////

			$stickers="";
			
			$hotStat = $row["HOT"];
			$featStat = $row["FEATURED"];			
			$daysOld = $row["DAYS_OLD"];
			$assumedNew = ($this->ENGINE->sanitize_number(ASSUMED_INTERVAL_LATEST) * 30);		
			
			if($topicClosed)		
				$stickers  = $this->SITE->getBgImg(array('file'=>$mediaRootFav.'closed.png','anchor'=>false,'title'=>'This topic is closed'));
			else
				$stickers = $this->SITE->getBgImg(array('file'=>$mediaRootFav.'open.png','anchor'=>false,'title'=>'This topic is open'));				
				
			if($hotStat == 1)
				$stickers .=  ' <i class="fas fa-fire red" title="This topic has been tagged hot"></i> ';
				
			if($featStat == 1)
				$stickers .= $this->SITE->getBgImg(array('file'=>$mediaRootFav.'featured.png','anchor'=>false,'title'=>'This topic has appeared frontpage'));

			if($daysOld <= $assumedNew)
				$stickers .=  '<i class="text-sticker text-sticker-info text-sticker-xs" title="This is a new topic">new</i>';
				
			$stickers = '<div class="inline" >'.$stickers.'</div>';	
			$topicIndenter = $this->SITE->getFA('fa-inbox yellow');	
					
			$prefixBreadCrumbs = in_array($type, array("common","profile","followed topics"));
			
			///////FINALLY, GENERATE A LIST OF ALL THE TOPICS WITH THEIR AUTHORS AND TOTAL POSTS////

			$topics .= '<li class="'.($K='t-'.$topicId.'-base').'" id="'.$K.'">'.
							$topicIndenter. 
							($prefixBreadCrumbs? '
								<a href="/'.$categorySlug.'" class="links sc1" '.$categTitle.'>'.$category.'</a> <span class="lblack">></span> 
								<a href="/'.$sectionSlug.'" class="links sc1" '.$sectionTitle.'>'.$sectionName.'</a> <span class="lblack">></span> ' : ''
							).'
							<a class="links sc1" href="'.$topicSlug.'" '.$threadTitle.'>'.$topic.'</a>'.
							(($type == "followed topics")? $unfollowLink.$newPosts : '').
							$stickers.$pagination.
							($viewAuthorized? '
								<small class="sc-footer">'.
									$topic_author.' ('.$topicPostCounts.' & '.$topicViewCounts.') 
									<span class="gingerx"> ('.$postAuthor.' '.$postTimeView.')</span>'.$topicCms.'
								</small>' : ''
							).$pinIcon.$topicCmsStickers.'
						</li>';
		
		}
		
		$topics = '<ul class="topic-base">'.$topics.'</ul>';
		
		return array($topics);
		
		
	}

	 

	 

	 



		
	/*** Method for loading a topic from database ***/
	public function loadThreadPosts($topicId, $forceRDR='', $limitPage="", $replyBoxAjaxClass=""){

		///////VARIABLE INITIALIZATION///////
		$urlhash="";						
			
		global $GLOBAL_mediaRootFav, $GLOBAL_rdr, $GLOBAL_page_self;
		
		$rdr = $GLOBAL_rdr;
		$sessUsername = $this->SESS->getUsername();	
		$sessUid = $this->SESS->getUserId();
		$pageSelf = $GLOBAL_page_self;

		////MAKE SURE THE TOPIC ID PASSED IS ACTUALLY TIED TO A TOPIC/////
											
		$topicName = $this->SITE->topicIdToggle($topicId);
		
		///////DISPLAY THE POSTS//////////////

		//GET TOTAL RECORDS FROM THE DB FOR PAGINATION/////

		/////////PDO QUERY////////////////////////////////////	
			
		$sql = "SELECT COUNT(*) FROM posts WHERE TOPIC_ID=?";
		$valArr = array($topicId);
		$totalRecords = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
		/**********CREATE THE PAGINATION*************/
		$urlhash = COMPOSER_ID;
		$path =  $GLOBAL_page_self;	
		$path_arr = explode("/", $path);
		
		if(isset($path_arr[2]) && strtolower($path_arr[2]) == "quote")
			$pageUrl = $this->ENGINE->get_page_path('page_url', 4);	
		
		elseif(isset($path_arr[2]) && strtolower($path_arr[2]) == "multi-quote")
			$pageUrl = $this->ENGINE->get_page_path('page_url', 3);		
			
		elseif(isset($path_arr[2]) && strtolower($path_arr[2]) == "edit")
			$pageUrl = $this->ENGINE->get_page_path('page_url', 4);	
				
		elseif(isset($path_arr[0]) && strtolower($path_arr[0]) == "post")
			$pageUrl = $this->ENGINE->get_page_path('page_url', 2);	
			
		else{
		
			$pageUrl = $this->SITE->getThreadSlug($topicId);
			$urlhash = 'ptab';
		
		}
		
		$paginationArr = $this->SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'perPage'=>$limitPage,'hash'=>$urlhash));						
		$pagination = $paginationArr["pagination"];
		$totalPage = $paginationArr["totalPage"];
		$perPage = $paginationArr["perPage"];
		$startIndex = $paginationArr["startIndex"];
		$pageId = $paginationArr["pageId"];
		
		 
		////END OF PAGINATION//////////////
		 
		 
		 
		///DISPLAY EACH POSTS OF THE TOPIC/////////


		/////////PDO QUERY/////////
			
		$sql = $this->SITE->composeQuery(array('type' => 'for_post', 'start' => $startIndex, 'stop' => $perPage, 'uniqueColumns' => '', 'filterCnd' => '', 'orderBy' => ''));		
		  
		$posthead = "";

		list($messages, $lastPostId, $topicId, $sid, $cid, $sectionName) = $this->loadPosts($sql, array($topicId), array('type'=>'main', 'replyBoxAjaxClass'=>$replyBoxAjaxClass, 'pageIdPassed'=>$pageId));					
				
		$topicStatArr = $this->getThreadStatus($topicId);		
		$isLockedTopic = $topicStatArr["isLocked"];
		$topicLocker = $topicStatArr["locker"];

		$modsCtrlMetaArr = array("tid"=>$topicId, "tn"=>$topicName, "section"=>$sectionName, "rdr"=>$rdr);
		
		list($postCms, $postCmsStickers, $topicCms, $topicCmsAndStickers) = $this->getModerationControls($modsCtrlMetaArr);	
		
		/*******IF THE USER IS FOLLOWING THIS TOPIC THEN**************/ 
		///////////PDO QUERY//////
				
		if($this->followedTopicsHandler(array('uid'=>$sessUid, 'tid'=>$topicId, 'check'=>true))){		
		  
			/////REMEMBER THE USER'S LAST SEEN POST////
		  
			///////////SET LAST POST INDEX COOKIE //////////
		  
			if($pageId == $totalPage)
				$this->followedTopicsHandler(array('uid'=>$sessUid, 'tid'=>$topicId, 'action'=>'loglspn', 'lspn'=>$lastPostId));	
			  
		}  

		$spamAlert='';

		
		if(getAutoPilotState("SPAM_BOT") && !$this->SESS->isAdmin()){
			////ALERT A USER THAT HE IS SPAMMING IN THIS TOPIC////////

			//////PDO QUERY////////
			
			$sql = "SELECT SPAM_COUNTER FROM spam_controls WHERE TOPIC_ID = ? AND USER_ID = ? LIMIT 1";
			$valArr = array($topicId, $sessUid);

			$spamCounter = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();


			if($spamCounter == 3){			
				
					$spamAlert = '<div class="spam-alert">									
										<strong>
											<span class="red">WARNING: </span><span class="cyan"><b>'.$sessUsername.'</b></span>, 
											our spam control system has flagged you for spamming in this topic(<span class="blue"><b>'.$topicName.'</b></span>) and your grace meter is 
											shown below. Please note that one more spam from you in this topic will fill up your grace meter
											and when that happens, you will be automatically signed out of your account and given <b class="red">at least 24 hours</b> ban.<br/>
											<br/>GRACE-METER:	
											<div class="spam-meter-base"><div class="spam-meter-h"></div></div>
										</strong>
									</div>';
				
				
			}elseif($spamCounter == 4){
				
				$spamAlert = '<div class="spam-alert">								
									<strong>
										<span class="red">WARNING: </span><span class="cyan"><b>'.$sessUsername.'</b></span>, 
										our spam control system has flagged you for spamming in this topic(<span class="blue"><b>'.$topicName.'</b></span>) and your grace meter is 
										shown below. Please note that your grace meter is completely filled and 
										any more spam from you in this topic will automatically sign you out of your account followed by an immediate ban of <b class="red">at least 24 hours</b>.<br/>
										<br/>GRACE-METER:	
										<div class="spam-meter-base"><div class="spam-meter-f"></div></div>
									</strong>
								</div>';
				
				
			}elseif($spamCounter >= 5){	
										
				header("Location:/logout?tid=".$topicId."&spam_user=".$sessUsername);			
				exit();	
						
			}
		}				 

		if(!$messages)
			$messages = '<span class="alert alert-danger">There are no posts in this topic yet</span>';

		return array($messages, $pagination, $spamAlert, $pageId, $totalPage, $topicCmsAndStickers);
			
		
	}








	
		
	/*** Method for loading user topics ***/
	public function loadUserTopics($metaArr){
		
		$acc='';
		
		$uid = $this->ENGINE->get_assoc_arr($metaArr, 'uid');	
		$sep = $this->ENGINE->get_assoc_arr($metaArr, 'sep');
		$i = $this->ENGINE->get_assoc_arr($metaArr, 'i');
		$i = $i? $i : 0;
		$n = $this->ENGINE->get_assoc_arr($metaArr, 'n');
		$n = $n? $n : 20;
		$U = $this->ACCOUNT->loadUser($uid);
		$uid = $U->getUserId();	
		$username = $U->getUsername();		
			
		if($uid){
			
			$valArr = array($uid); $counter = 0;
			///////////PDO QUERY//////
			$sql = "SELECT ID FROM topics WHERE TOPIC_AUTHOR_ID=? ORDER BY TIME DESC LIMIT ".$i.",".$n;
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			
			while($row = $this->DBM->fetchRow($stmt)){
				
				$tid = $row["ID"];			
				$acc .= $tid.',';
				$counter++;
				
			}
						
			$more = ($counter > 10)? $this->SITE->getExtendedViewLink($username.'/all-topics') : '';
			$acc = $this->SITE->idCsvToNameString($acc, $t='tid', $nSep=$sep).$more;	
		}
		
		return $acc;
	}



	
	
	



		
	/*** Method for spam control in a thread/topic ***/
	public function spamControl($topicId, $message, $pid){
		
		if(getAutoPilotState("SPAM_BOT") && !$this->SESS->isAdmin()){
			 
			$sessUsername = $this->SESS->getUsername();
			$sessUid = $this->SESS->getUserId();
			 
			////CHECK IF USERS ARE SPAMMING WITH THEIR POST//////

			///PDO QUERY/////////////
			
			$sql = "SELECT COUNT(*) FROM posts WHERE MESSAGE LIKE ?  AND  POST_AUTHOR_ID = ? AND TOPIC_ID= ?";
			$valArr = array($message, $sessUid, $topicId);
						
			$spamCounter = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();

			if($spamCounter == 3){
					
				/////////PDO QUERY///////////
				
				$sql = "INSERT INTO spam_controls (USER_ID, MESSAGE, TIME, SPAM_COUNTER, TOPIC_ID, POST_ID) VALUES(?,?,NOW(),?,?,?)";
				$valArr = array($sessUid, $message, $spamCounter, $topicId, $pid);
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);

			}elseif($spamCounter > 3){
			
				/////PDO QUERY/////
			
				$sql = "UPDATE spam_controls SET SPAM_COUNTER = SPAM_COUNTER + 1 WHERE USER_ID=? AND TOPIC_ID=? LIMIT 1";
				$valArr = array($sessUid, $topicId);
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			
				if($spamCounter == 5){	
				
					//LET THE SYSTEM DECIDE HOURS OF BAN(1 - 4 days)/////
					
					$hr = (mt_rand(1, 4) * 24);
					
					$banDuration = $this->ENGINE->get_date_safe('', '', array('mdfMeta'=>$hr.' hour'));			  

				
					////PDO QUERY///
				
					$sql = "UPDATE spam_controls SET BAN_STATUS = 1 , TIME_BANNED=NOW(), BAN_DURATION=?  WHERE USER_ID=?  AND TOPIC_ID=? LIMIT 1";
					$valArr = array($banDuration, $sessUid, $topicId);
					$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
				
					$this->ACCOUNT->updateUser($sessUid, 'BAN_COUNTER = BAN_COUNTER + 1');
				
				}					
						
			}

			////IF AFTER A WHILE AND THE USER DID NOT SPAM FURTHER WITH HIS SUBSEQUENT POST, THEN RESET HIS GRACE METER///

			/////PDO QUERY///////
			
			$sql = "DELETE FROM spam_controls WHERE (TIME + INTERVAL 24 HOUR) <= NOW() AND  USER_ID=? AND TOPIC_ID=? ";
			$valArr = array($sessUid, $topicId);
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		
		}
					
		
	 }
	 
	 
	 
	 
	

     
	/*** Method for fetching thread/topic closed icon ***/
	public function getThreadClosedIcon(){
		
		global $GLOBAL_mediaRootFav;
		
		//$icon = "<div class=thread_closed>THREAD CLOSED</div>";
		$icon = '<div class="thread-closed"><img alt="thread closed" src="'.$GLOBAL_mediaRootFav.'closed.gif"  /></div>';
		return $icon;
		
	}





	
		
	/*** Method for initiating thread/topic follow request ***/
	public function requestTopicFollow($tid, $taskAction='follow'){
			
		$sessUid = $this->SESS->getUserId();
		
		$tid = strtolower($tid);
		$taskAction = strtolower($taskAction);
		$found='';
		
		////////VERIFY EXISTENCE////////
		if($tid != "all"){
			
			$found = $this->SITE->topicIdToggle($tid);
			
		}
			
		if($tid && $taskAction && $sessUid){

			///UNFOLLOW THE TOPIC IF YOU ARE ALREADY FOLLOWING IT/////////														
			
			if($taskAction == "unfollow" && $found){
									
				$this->followedTopicsHandler(array('uid'=>$sessUid, 'tid'=>$tid, 'action'=>$taskAction));
				
								
			}elseif($taskAction == "follow" && $found){
							
				list($postAuthorized, $viewAuthorized, $protectionAlert) = $this->authorizeThreadAccess($tid);
					
				if($viewAuthorized){
					
					 $this->followedTopicsHandler(array('uid'=>$sessUid, 'tid'=>$tid, 'action'=>$taskAction));
					 
				}
							

			}elseif($tid == "all" && $taskAction == "unfollow"){
					
				///UNFOLLOW ALL TOPICS///////
				$this->followedTopicsHandler(array('uid'=>$sessUid, 'action'=>$taskAction));
				
			}												
		}	
	}






	
		
	/*** Method for deciding and fetching thread/topic follow/unfollow link ***/
	public function requestTopicFollowLink($topicId, $twin=true){

		//////////GET DATABASE CONNECTION//////
		global $GLOBAL_rdr;	
		
		$sessUid = $this->SESS->getUserId();
		$sessUsername = $this->SESS->getUsername();
		
		

		///CHECK IF U ARE FOLLOWING THIS TOPIC TO GET OPTION TO UNFOLLOW///////////	
						
		$txt=$isFollowing=$ftopicInPost=$fnu_links="";
		$data_twin = $twin? ' data-twin="true" ' : '';
		$cmDatas = ' data-tid="'.$topicId.'" '.$data_twin.' id="follow_topic" class="links follow_topic" ';
					
		if($this->followedTopicsHandler(array('uid'=>$sessUid, 'tid'=>$topicId, 'check'=>true))){
			
			$txt = 'Un-follow';
			
			$isFollowing = '<a href="/'.$sessUsername.'" class="links">You</a> <span class="black">are following this topic</span>';
			
			$fnu_links = '<span class="tf-disp" >'.$isFollowing.'</span><li><a  href="/topic-follows/unfollow/'.$topicId.'/?_rdr='.$GLOBAL_rdr.'"  data-action="unfollow" '.$cmDatas.'>'.$txt.'</a></li>';
			
		}else{
			
			$txt = 'Follow this topic';
			
			$fnu_links = '<span class="tf-disp" >'.$isFollowing.'</span><li><a href="/topic-follows/follow/'.$topicId.'/?_rdr='.$GLOBAL_rdr.'"    data-action="follow" '.$cmDatas.'>'.$txt.'</a></li>';
		
			
			/////IF THE USER IS NOT FOLLOWING THE TOPIC AND PRESENT AN OPTION TO DURING NEW POST FORM////////
			///CHECK FOR NO PERSISTENT TOPIC FOLLOW nptf cookie///
			if(!isset($_COOKIE["nptf-".$topicId]))
				$ftopicInPost = '<br/><label for="ftopic" >Follow this Topic <input id="ftopic" checked type="checkbox" class="checkbox" name="ftopic" /></label>
										&nbsp;<label for="nptf" >Don\'t prompt again <input id="nptf" type="checkbox" class="checkbox" name="nptf" /></label><br/>';
		
		}
				
		
		return array("fnuLink" => $fnu_links, "ftopicCheckBox" => $ftopicInPost);
		
		
	}





	
		

	
		
	/*** Method for fetching thread statuses ***/
	public function getThreadStatus($tid){
		
		$ocStat=$ocStatUid=$isRecycled=$recycler=$isLocked=$locker=$isProtected=$protecter=$isMoved=$mover=
		$isHot=$hoter=$isPinned=$pinner=$isFeatured=$featurer=$isRenamed=$renamer=$statAll=$statAllUid=$topicClosed=
		$modStat=$topicHeadStatus=$tn=$sid='';
		
		$actionCols = ','.implode(',', array("CLOSED", "MOVED", "HOT", "PINNED", "FEATURED", 
		"PROTECTION_LEVEL", "RECYCLED", "RENAMED", "LOCKED"));

		//////PDO QUERY/////////

		$sql =  "SELECT SECTION_ID, TOPIC_NAME".$actionCols." FROM topics WHERE ID=? LIMIT 1";
		$valArr = array($tid);
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		$row = $this->DBM->fetchRow($stmt);
		
		if(!empty($row)){

			$sid = $row["SECTION_ID"];
			$tn = $row["TOPIC_NAME"];
			list($ocStat, $ocStatUid) = $this->decodeModerationStatus($row["CLOSED"]);
			list($isRecycled, $recycler) = $this->decodeModerationStatus($row["RECYCLED"]);
			list($isLocked, $locker) = $this->decodeModerationStatus($row["LOCKED"]);
			list($isProtected, $protecter) = $this->decodeModerationStatus($row["PROTECTION_LEVEL"]);
			list($isMoved, $mover) = $this->decodeModerationStatus($row["MOVED"]);
			list($isRenamed, $renamer) = $this->decodeModerationStatus($row["RENAMED"]);
			list($isHot, $hoter) = $this->decodeModerationStatus($row["HOT"]);
			list($isPinned, $pinner) = $this->decodeModerationStatus($row["PINNED"]);
			list($isFeatured, $featurer) = $this->decodeModerationStatus($row["FEATURED"]);
			$rSticker = $isRecycled || ($sid == RECYCLEBIN_SID)? 'Recycled' : '';
			$lSticker = $isLocked? 'Locked' : '';
			$pSticker = $isProtected? $isProtected : '';
			$mSticker = $isMoved? 'Moved' : '';
			$rnSticker = $isRenamed? 'Renamed' : '';
			$hSticker = $isHot? 'Hot' : '';
			$pnSticker = $isPinned? 'Pinned' : '';
			$fSticker = $isFeatured? 'Featured' : '';	
			$topicClosed = $ocStat; 	
			$ocSticker = $topicClosed? 'Closed' : 'Open';	
			$sep = ', ';
			
			$statAll = $ocStat.($isRecycled? $sep.$isRecycled : '').($isLocked? $sep.$isLocked : '').($isProtected? $sep.$isProtected : '').
			($isMoved? $sep.$isMoved : '').($isHot? $sep.$isHot : '').($isPinned? $sep.$isPinned : '').($isFeatured? $sep.$isFeatured : '').
			($isRenamed? $sep.$isRenamed : '');	

			$modStat = $ocSticker.($isRecycled? $sep.$rSticker : '');
			$topicHeadStatus = trim(($topicClosed? $sep.$ocSticker : '').($isRecycled? $sep.$rSticker : '').($isLocked? $sep.$lSticker : ''), $sep);
			
			$statAllUid = $ocStatUid.($recycler? $sep.$recycler : '').($locker? $sep.$locker : '').($protecter? $sep.$protecter : '').
			($mover? $sep.$mover : '').($hoter? $sep.$hoter : '').($pinner? $sep.$pinner : '').($featurer? $sep.$featurer : '').
			($renamer? $sep.$renamer : '');	

		}

		return array(

			'ocs' => $ocStat, 'ocsUid' => $ocStatUid, 'isRecycled' => $isRecycled, 'recycler' => $recycler, 
			'isLocked' => $isLocked, 'locker' => $locker, 'isProtected' => $isProtected, 'protecter' => $protecter,
			'isMoved' => $isMoved, "mover" => $mover, 'isHot' => $isHot, "hoter" => $hoter, 
			'isPinned' => $isPinned, "pinner" => $pinner, 'isFeatured' => $isFeatured, "featurer" => $featurer,
			'isRenamed' => $isRenamed, 'renamer' => $renamer,
			'all' => $statAll, 'allUid' => $statAllUid, 'tClosed' => $topicClosed,
			'mds' => $modStat, 'ths' => $topicHeadStatus, 'tn' => $tn, 'sid' => $sid
				
		);
		

	}



	
	
	
		

	
		
	/*** Method for authorizing user access to threads ***/
	public function authorizeThreadAccess($topicId){		
		
		$topicStatArr = $this->getThreadStatus($topicId);		
		$protection = $topicStatArr["isProtected"];	
		
		$reputation = $this->SESS->getReputation();
		$staff = $this->SESS->isStaff();
		
		$viewAuthorized = $this->ACCOUNT->sessionAccess(array('id'=>$topicId, 'cond'=>'staffsOnly'));
		$postAuthorized = true;	

		switch($protection){	
			
			case THREAD_PROTECTION_LEVEL_1:
					$postAuthorized = ($reputation >= STD_LV_ACCESS_REP || $staff); break;	
						
			case THREAD_PROTECTION_LEVEL_2:
					$postAuthorized = ($reputation >= CLS_LV_ACCESS_REP || $staff); break; 
			
			case THREAD_PROTECTION_LEVEL_3:
					$postAuthorized = ($reputation >= PRM_LV_ACCESS_REP || $staff); break; 
			
			case THREAD_PROTECTION_LEVEL_4:
					$postAuthorized = ($reputation >= ELT_LV_ACCESS_REP || $staff); break; 
			
			case THREAD_PROTECTION_LEVEL_5:
					$postAuthorized = ($reputation >= LRD_LV_ACCESS_REP || $staff); break; 
			
			case THREAD_PROTECTION_LEVEL_6:
					$postAuthorized = ($reputation >= MST_LV_ACCESS_REP || $staff); break;
			 
			case THREAD_PROTECTION_LEVEL_7:
					$postAuthorized = ($reputation >= ROY_LV_ACCESS_REP || $staff); break; 
			
			case THREAD_PROTECTION_LEVEL_8:
					$postAuthorized = ($reputation >= ULT_LV_ACCESS_REP || $staff); 
					$viewAuthorized = ($postAuthorized && $viewAuthorized); 
					break; 
			
			case THREAD_PROTECTION_LEVEL_0: 
			
			default : $protection = ''; 
			
		}	
		
		$protectionAlert = $protection? '<b class="red">'.$protection.'</b>' : '';
		return array($postAuthorized, $viewAuthorized, $protectionAlert);
			
	}



	
	

	
	
		

	
		
	/*** Method for fetching thread protection level sticker ***/
	public function getThreadProtectionSticker($protection){
		
		global $FA_shield;
		
		$cls='';
		$shieldIcon = $FA_shield;
		list($protection) = $this->decodeModerationStatus($protection);
		
		switch($protection){	
		
			case THREAD_PROTECTION_LEVEL_1: $cls = 'green'; break;
			
			case THREAD_PROTECTION_LEVEL_2: $cls = 'brown'; break;
			
			case THREAD_PROTECTION_LEVEL_3: $cls = 'violet'; break;
			
			case THREAD_PROTECTION_LEVEL_4: $cls = 'olive'; break;
			
			case THREAD_PROTECTION_LEVEL_5: $cls = 'pink'; break;
			
			case THREAD_PROTECTION_LEVEL_6: $cls = 'bluex'; break;
			
			case THREAD_PROTECTION_LEVEL_7: $cls = 'gold'; break;
			
			case THREAD_PROTECTION_LEVEL_8: $cls = 'red'; break;
			
			case THREAD_PROTECTION_LEVEL_0: 
			
			default : $protection = '';
			
		}
			
		$title = ' title="Thread Protection: '.$protection.'" ';
		$cls = $cls? 'text-sticker bg-'.$cls : '';
		$protectionMain = $protection? '<b class="'.$cls.'" '.$title.'>'.$shieldIcon.$protection.'</b>' : '';
		$protectionFloated = $protection? '<div class="clear"><b class="'.$cls.' pull-r" '.$title.'>'.$shieldIcon.$protection.'</b></div>' : '';
		
		return array($protectionFloated, $protectionMain);
			
	}




		


	/*** Method for updating/executing final thread moderation actions using user ranking system ***/
	public function doThreadActionByRank($metaArr){
		
		$kArr = array("tid", "doTxt", "undoTxt", "dbCol", "doSubQry", "undoSubQry", 
		"currState", "currStateUid", "valArr", "doState", "undoState", "hasDoStateOnly", "forceUndoState");

		list($tid, $stateApplyTxt, $stateReverseTxt, $statusDbCol, $stateApplySubQry, $stateReverseSubQry, 
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
				
			$valArr[] = $tid;

			///PDO QUERY//////
		
			$sql = "UPDATE topics SET ".$statusDbCol." = ? ".($typeSubQry? ','.$typeSubQry : '')." WHERE ID=? LIMIT 1";
			$valArr = array_merge(array($newStat), $valArr);
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);

			$logActivity = true;				
			$alertUser = '<span class="alert alert-success">Thread was successfully <span class="'.$cls.' bg-white">'.$doneActionTxt.'</span></span>';
						
		}else
			$alertUser = '<span class="alert alert-danger">Privilege Overridden!..... your rank could not override the current state of the thread.</span>';
				
		
		return array($alertUser, $logActivity, $doneActionTxt);
		
	}


	


}


?>