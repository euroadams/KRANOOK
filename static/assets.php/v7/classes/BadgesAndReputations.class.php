<?php

class BadgesAndReputations{
	
	/*** Generic member variables ***/
	private $DBM;
	private $ACCOUNT;
	private $SESS;
	private $ENGINE;
	private $SITE;
	private static $nAwardedTip = 'Number of times awarded';
	private static $addedRepTip = 'Additional reputation points earned';
	
	/*** Constructor ***/
	public function __construct(){
		
		global $dbm, $ACCOUNT, $ENGINE, $SITE;
		
		$this->DBM = $dbm;
		$this->ACCOUNT = $ACCOUNT;
		$this->SESS = $ACCOUNT->SESS;
		$this->ENGINE = $ENGINE;
		$this->SITE = $SITE;
		
	}
	
	/*** Destructor ***/
	public function __destruct(){
		
		
	}



	
		
	/*** Method for fetching the css styles of badge classes ***/
	public function getBadgeClassCssStyles($badgeClass='', $retType='inline'){
		
		$bronzeInlineCss = BRONZE_INLINE_STYLE;
		$silverInlineCss = SILVER_INLINE_STYLE;
		$goldInlineCss = GOLD_INLINE_STYLE;
		
		switch($badgeClass){
		
			case BRONZE_BADGE_CLASS: $classInlineCss = $bronzeInlineCss; $classColor = BRONZE_COLOR; break;
		
			case SILVER_BADGE_CLASS: $classInlineCss = $silverInlineCss; $classColor = SILVER_COLOR; break;
		
			case GOLD_BADGE_CLASS: $classInlineCss = $goldInlineCss; $classColor = GOLD_COLOR; break;
		
			default: $classInlineCss = ''; $classColor = '';
		
		}
		
		switch(strtolower($retType)){
		
			case 'array': $ret = array($classInlineCss, $bronzeInlineCss, $silverInlineCss, $goldInlineCss); break;
		
			case 'color': $ret = $classColor; break;
		
			default: $ret = $classInlineCss;
		
		}
		
		return $ret;
		
	}
		

	




	
		
	/*** Method for fetching a field from database badge table ***/
	public function getBadgeDetail($needle, $col="REPUTATION_REWARD", $ucwords=true){
		
		$ret="";
		
		$sql = "SELECT ".$col." FROM badges WHERE (".$this->DBM->useStrLeadDigitIdField()." OR BADGE_NAME=?) LIMIT 1";
		$valArr = array($needle, $needle);
		$ret = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		(strtolower($col) == 'badge_name' && $ucwords)? ($ret = ucwords(strtolower($ret))) : '';
		
		return $ret;
		
	}




	

	
	
	
		
	/*** Method for counting badges queried from database ***/
	public function getBadgeCount($metaArr=''){
		
		$ret=$subQry=""; $valArr=array();
	
		$uid = $this->ENGINE->get_assoc_arr($metaArr, 'uid');
		$xuid = $this->ENGINE->get_assoc_arr($metaArr, 'xuid');
		$bid = $this->ENGINE->get_assoc_arr($metaArr, 'bid');
		$cid = $this->ENGINE->get_assoc_arr($metaArr, 'cid');
		$clid = $this->ENGINE->get_assoc_arr($metaArr, 'clid');
		$clid = $this->ENGINE->get_assoc_arr($metaArr, 'clid');
		$uwsb = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'uwsb');//USERS WITH SAME BADGE
		$distinct = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'distinct');
		$subQry = $uid? ' USER_ID=? ' : '';	
		$subQry .= $bid? ($subQry? ' AND ' : '').' BADGE_ID=? ' : '';
		$subQry .= $clid? ($subQry? ' AND ' : '').' CLASS=? ' : '';
		$subQry .= $cid? ($subQry? ' AND ' : '').' CATEGORY=? ' : '';	
		$subQry .= $xuid? ($subQry? ' AND ' : '').' USER_ID !=? ' : '';	
		$subQry = $subQry? ' WHERE ('.$subQry.')' : '';
		
		if($uid || $uwsb){
			
			$uid = $this->ACCOUNT->loadUser($uid)->getUserId();
			
			$sql = "SELECT COUNT(".(($bid && !$distinct)? "*" : "DISTINCT ".
			($uwsb? "USER_ID" : "BADGE_ID")).") AS TOTAL_RECS FROM awarded_badges ".
			(($cid || $clid)? " JOIN badges ON badges.ID=awarded_badges.BADGE_ID" : "").$subQry;

			$uid? ($valArr[] = $uid) : '';
			$bid? ($valArr[] = $bid) : '';		
			$clid? ($valArr[] = $clid) : '';		
			$cid? ($valArr[] = $cid) : '';		
			$xuid? ($valArr[] = $xuid) : '';	
		
		}elseif($bid){
	
			$sql = "SELECT COUNT(*) FROM awarded_badges WHERE BADGE_ID = ?";
			$valArr[] = $bid;		
		
		}else{
	
			$subQry = $cid? ' CATEGORY=? ' : '';	
			$subQry .= $clid? ($subQry? ' AND ' : '').' CLASS=? ' : '';		
			$subQry = $subQry? ' WHERE ('.$subQry.')' : '';
			
			$sql = "SELECT COUNT(*)FROM badges".$subQry;
			$cid? ($valArr[] = $cid) :'';
			$clid? ($valArr[] = $clid) : '';
	
		}

		$ret = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
		return $ret;
		
	}
		

	
	

	

	
	
	
		
	/*** Method for replacing badge tags link/url database placeholders ***/
	public function decodeDbTagDelimeters($crit, $tagName){
	
		$tagName_LC = strtolower($tagName);
		$tagName_UCF = ucwords($tagName_LC);
		$title = ' title="Tag: '.$tagName_UCF.'" ';
		return str_ireplace(BT_TAG_ANCHOR_DELIMETER, '<a href="/'.THREAD_SLUG_CONSTANT.'/tagged/'.urlencode($tagName_LC).'" '.$title.' class="links no-hover-bg"><span id="'.$tagName_LC.'" class="thread-tag">'.$tagName_UCF.'</span></a> tag', $crit);
	
	}


	

	

	
	
	
		
	/*** Method for loading queried badges from database ***/
	public function loadBadges($metaArr=array('i'=>0, 'n'=>50)){
		
		$ret=$count=$subQry="";
			
		$staff = $this->SESS->isStaff();			
		$wrapper = $this->ENGINE->get_assoc_arr($metaArr, 'wrapper');	
		$appendCount = $this->ENGINE->is_assoc_key_set($metaArr, $K='appendCount')? $this->ENGINE->get_assoc_arr($metaArr, $K) : true;
		$selOpt = $this->ENGINE->get_assoc_arr($metaArr, 'selOpt');	
		$cid = $this->ENGINE->get_assoc_arr($metaArr, 'cid');
		$bid = $this->ENGINE->get_assoc_arr($metaArr, 'bid');	
		$clid = $this->ENGINE->get_assoc_arr($metaArr, 'clid');	
		$decodeTags = $this->ENGINE->get_assoc_arr($metaArr, 'decodeTags');
		$url = $this->ENGINE->get_assoc_arr($metaArr, 'url');	
		$i = $this->ENGINE->get_assoc_arr($metaArr, 'i');
		$i = $i? $i : 0;
		$n = $this->ENGINE->get_assoc_arr($metaArr, 'n');
		$n = $n? $n : 50;
		$valArr = array();
		$cid? ($valArr[] = $cid) : '';
		$bid? ($valArr[] = $bid) : '';
		$clid? ($valArr[] = $clid) : '';
		$subQry = $cid? ' CATEGORY=? ' : '';
		$subQry .= $bid? ($subQry? ' AND ' : '').' ID=? ' : '';
		$subQry .= $clid? ($subQry? ' AND ' : '').' CLASS=? ' : '';
		$subQry .= $decodeTags? ($subQry? ' AND ' : '').' ID IN('.$decodeTags.') ' : '';
		$subQry = $subQry? ' WHERE ('.$subQry.')' : '';
		
		$sql = "SELECT * FROM badges ".$subQry." ".($decodeTags? "" :" ORDER BY BADGE_NAME ASC")." LIMIT ".$i.",".$n;
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);	
		
		while($row = $this->DBM->fetchRow($stmt)){
			
			$bid = $row["ID"];
			$name = $row["BADGE_NAME"];
			$badgeClass = $row["CLASS"];
			$catId = $row["CATEGORY"];
			$crit = $row["CRITERIA"];
			$accRep = $row["REPUTATION_REWARD"];
			$name_LC = strtolower($name);
			$name_UCF = ucwords($name_LC);		
			$classInlineCss = $this->getBadgeClassCssStyles($badgeClass);
			
			$classDot = '<i '.$classInlineCss.' class="circular-bullet"></i>';
			$prefix = !$wrapper? '<li>' : $wrapper;
			$suffix = str_replace('<', '</', $prefix);
			
			if($appendCount){	
							
				$count = ' <small class="green" title="'.self::$nAwardedTip.' within the community"> x'.$this->getBadgeCount(array('bid'=>$bid)).'</small>';							
				$count .= ($staff? '&nbsp;&nbsp;&nbsp;<small class="lgreen" title="'.self::$addedRepTip.'">+'.$accRep.'</small>' : '');
			
			}
			
			$title = ' title="'.(($catId == TAG_BADGE_CATEGORY)? 'Tag' : 'Badge').': '.$name_UCF.' - '.$bid.'" ';
			$href = ($url? $url : '/badges/awardees').'/'.$bid;
			
			if($selOpt)
				$ret .= $prefix.$name_UCF.$suffix;
			
			elseif($decodeTags)
				$ret .= '<a href="/'.THREAD_SLUG_CONSTANT.'/tagged/'.urlencode($name_LC).'" '.$title.' class="links no-hover-bg"><span id="'.$name_LC.'" class="thread-tag">'.$name_UCF.'</span></a>';
			
			else
				$ret .= $prefix.'<a href="'.$href.'" '.$title.' class="links no-hover-bg"><span id="'.$name_LC.'" class="badge '.(($catId == TAG_BADGE_CATEGORY)? 'badge-tag' :'').'">
				'.$classDot.$name_UCF.'</span></a>'.$count.'<br/><span class="prime small">'.$this->decodeDbTagDelimeters($crit, $name).'</span>'.$suffix;
			
		}
			
		if(!$selOpt && !$decodeTags && !$stmt->rowCount())
			$ret = '<span class="alert alert-danger">Sorry no badge was found</span>';
			
		return $ret;
			
	}



	
	
	

	

	
	
	
		
	/*** Method for loading user badges from database ***/
	public function loadUserBadges($metaArr=array('uid'=>'', 'ipb'=>false, 'mvp'=>'', 'appendCount'=>true, 'i'=>0, 'n'=>20)){	
	
		global $GLOBAL_mediaRootBadge;
	
		$mediaRootBadge = $GLOBAL_mediaRootBadge;
		$allBadges=$count=$more=$medals=$goldBadges=$silverBadges=$bronzeBadges=$subQry=$alert=''; 
	
		$tmp_arr=$tmpMore_arr=$acc_arr=array();
		$golds=$silvers=$bronzes=0;
		$userMedalCls = 'user-medal';
		$nbsp = '&nbsp;&nbsp;&nbsp;';
		$cBullet = 'circular-bullet';
		
		$userBadgesTable = 'awarded_badges';
		$uid = $this->ENGINE->get_assoc_arr($metaArr, 'uid');	
		$ipb = $this->ENGINE->get_assoc_arr($metaArr, 'ipb');//IN POST BADGE	
		$maxVis_Pid = $this->ENGINE->get_assoc_arr($metaArr, 'mvp');	
		$appendCount = $this->ENGINE->get_assoc_arr($metaArr, 'appendCount');
		$appendCount = $appendCount? $appendCount : true; 
		$i = $this->ENGINE->get_assoc_arr($metaArr, 'i'); 
		$i = $i? $i : 0;
		$n = $this->ENGINE->get_assoc_arr($metaArr, 'n');
		$n = $n? $n : 20;
		$cid = $this->ENGINE->get_assoc_arr($metaArr, 'cid');		
		$clid = $this->ENGINE->get_assoc_arr($metaArr, 'clid');		
		$medalsOnly = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'medalsOnly');		
		$medalBg = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'medalBg');		
		$panelBlock = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'panelBlock');		
		$reportErr = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'reportErr');		
		$retArr = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'retArr');		
		$bid = $this->ENGINE->get_assoc_arr($metaArr, 'bid');			
		$userBadgeCms = ($this->ENGINE->is_assoc_key_set($metaArr, $K='userBadgeCms'))? $this->ENGINE->get_assoc_arr($metaArr, $K) : false;
		
		$U = $this->ACCOUNT->loadUser($uid);
		$userId = $U->getUserId();
		$username = $U->getUsername();
		$isSess = (strtolower($username) == strtolower($this->SESS->getUsername()));
		$refTitle = $isSess? 'my' : $username.'\'s';
		$shieldIcon = '<i class="fas fa-shield-alt"></i>';
		
		$valArr = array($userId);
		$clid? ($valArr[] = $clid) : '';
		$cid? ($valArr[] = $cid) : '';
		$bid? ($valArr[] = $bid) : '';
		$subQry = ' USER_ID=? ';
		$subQry .= $clid? ($subQry? ' AND ' : '').' CLASS=? ' : '';
		$subQry .= $cid? ($subQry? ' AND ' : '').' CATEGORY=? ' : '';
		$subQry .= $bid? ($subQry? ' AND ' : '').' BADGE_ID=? ' : '';
		$subQry = $subQry? ' WHERE ('.$subQry.')' : '';
		
		$sql = 'SELECT *,badges.ID AS BID, '.($bid? $userBadgesTable.'.TIME' : '(SELECT MAX(TIME) FROM '.$userBadgesTable.' WHERE '.$userBadgesTable.'.BADGE_ID=BID)').' AS AWTIME'.(!$bid? ', COUNT(*) AS NTIMES' : '').' 
				FROM '.$userBadgesTable.' JOIN badges ON badges.ID = '.$userBadgesTable.'.BADGE_ID '
				.$subQry.((!$bid)? ' GROUP BY BADGE_ID ' : '').' ORDER BY '.$userBadgesTable.'.TIME DESC,BADGE_NAME ASC LIMIT '.$i.','.$n;
		
		if($medalsOnly || $stmt = $this->DBM->doSecuredQuery($sql, $valArr)){
	
			$max = 3;								
			
			if($ipb && !$U->getShowBadge()){
				
				////CHECK IF THE USER ALLOW DISPLAY OF BADGES IN POST///				
				return '';	
												
			}							
									
			list($classInlineCss, $bronzeInlineCss, $silverInlineCss, $goldInlineCss) = $this->getBadgeClassCssStyles($badgeClass='', 'array');							
			
			if(!$medalsOnly){
				
				while($bdg = $this->DBM->fetchRow($stmt)){
					
					$bdgId  = $rowFound = $bdg["BID"];
					$name  = $bdg["BADGE_NAME"];
					$catId  = $bdg["CATEGORY"];
					$freq = strtolower($bdg["AWARD_FREQ"]);
					$criteria = strtolower($bdg["CRITERIA"]);
					$nTimes = $this->ENGINE->get_assoc_arr($bdg, 'NTIMES');				
					$badgeClass = $bdg["CLASS"];
					$awTime = $this->ENGINE->get_date_safe($bdg["AWTIME"], 'jS M Y', array('xActiveYearFmt'=>'M jS'));				
					$name_LC = strtolower($name);
					$name_UCF = ucwords($name_LC);
					$bdgTitle = $name_UCF.' Badge';
					list($classInlineCss, $bronzeInlineCss, $silverInlineCss, $goldInlineCss) = $this->getBadgeClassCssStyles($badgeClass, 'array');
					
					if($appendCount && $nTimes){
					
						$count = ($freq == "m")? ' <small class="green" title="'.self::$nAwardedTip.' to '.($isSess? 'you' : $username).'"> x'.($nTimes).'</small>' : '';							
	
					}						
									
					$perBadge = '<div class="'.($ipb? 'inline-block base-r-mg' : '').'">
									<a title="'.$bdgTitle.'" class="links no-hover-bg" href="/'.strtolower($username).'/badges/award-history/'.$bdgId.'"><span class="badge '.($ipb? ' ipb ' : '').(($catId == TAG_BADGE_CATEGORY)? 'badge-tag' : '').'"><i '.$classInlineCss.' class="'.$cBullet.'"></i>'.$name_UCF.'</span></a>'
									.(!$ipb? $count.' <small class="prime-1" title="Date awarded"> - '.$awTime.'</small>' : '').'
								</div>';					
									
					if($ipb){	
					
						$tmp_arr[] = $acc_arr[] = $perBadge;
	
					}else{
					
						if($badgeClass == BRONZE_BADGE_CLASS)
							$bronzeBadges .= $perBadge;
					
						if($badgeClass == SILVER_BADGE_CLASS)
							$silverBadges .= $perBadge;
					
						if($badgeClass == GOLD_BADGE_CLASS)
							$goldBadges .= $perBadge;
											
					}
													
				}
					
				!isset($rowFound)? $alert = '<span class="alert alert-danger">Sorry no badge was found</span>' : '';
					
			}
					
			shuffle($tmp_arr);
			$tmpMore_arr = array_slice($tmp_arr, $max);
			$tmp_arr = array_slice($tmp_arr, 0, $max);
			$allBadges = implode('', $tmp_arr);
			$more = implode('', $tmpMore_arr);
			$goldCounts = $silverCounts = $bronzeCounts = 0;
					
			if($userId){
					
				$goldCounts = $this->ENGINE->format_number($this->getBadgeCount(array('uid'=>$userId, 'clid'=>GOLD_BADGE_CLASS)));
				$silverCounts = $this->ENGINE->format_number($this->getBadgeCount(array('uid'=>$userId, 'clid'=>SILVER_BADGE_CLASS)));
				$bronzeCounts = $this->ENGINE->format_number($this->getBadgeCount(array('uid'=>$userId, 'clid'=>BRONZE_BADGE_CLASS)));
					
			}
					
			$medalIncs = ($ipb || $medalsOnly);
			$golds = $goldCounts? '<b class="'.$userMedalCls.'"><i '.$goldInlineCss.' class="'.$cBullet.'"></i>'.($medalIncs? '<span class="dsk-platform-dpn-i">Golds:</span>' : 'Golds:').' '.($userBadgeCms? '' : $goldCounts).'</b>' : '';
			$silvers = $silverCounts? '<b class="'.$userMedalCls.'"><i '.$silverInlineCss.' class="'.$cBullet.'"></i>'.($medalIncs? '<span class="dsk-platform-dpn-i">Silvers:</span>' : 'Silvers:').' '.($userBadgeCms? '' : $silverCounts).'</b>' : '';
			$bronzes = $bronzeCounts? '<b class="'.$userMedalCls.'"><i	'.$bronzeInlineCss.' class="'.$cBullet.'"></i>'.($medalIncs? '<span class="dsk-platform-dpn-i">Bronzes:</span>' : 'Bronzes:').' '.($userBadgeCms? '' : $bronzeCounts).'</b>' : '';
			$reputation = '<b class="'.$userMedalCls.'">'.($medalIncs? $shieldIcon.' <span class="dsk-platform-dpn-i">Reputation:</span>' : '<span class="prime-2">'.$shieldIcon.' Reputation:</span>').' <span class="prime">'.$this->ENGINE->format_number($U->getReputation()).'</span></b>';
			
			$medals = '<span>'.$reputation.'</span>'.($goldCounts? '<span>'.$golds.($userBadgeCms? '<b>'.$goldCounts.'</b>' : '').'</span>' : '').
			($silverCounts? '<span>'.$silvers.($userBadgeCms? '<b>'.$silverCounts.'</b>' : '').'</span>' : '').($bronzeCounts? '<span>'.$bronzes.($userBadgeCms? '<b>'.$bronzeCounts.'</b>' : '').'</span>' : '');
			
			$medals = '<div class="pill-followers'.($medalIncs? ' smaller' : '').'">'.$medals.'</div>';
			
			$allBadges = $medals.$allBadges;
					
			if(!$medalsOnly){
					
				if($maxVis_Pid && $more){
					
					/*
					$tmp='';
					foreach($acc_arr as $swiperItem){
					
						$tmp .= '<div class="swiper-slide">'.$swiperItem.'</div>';
					
					}
					
					$allBadges = '<div class="swiper-container swiper-basic" data-slides-per-view="10">
									<div class="swiper-wrapper">'.$tmp.'</div>
									<div class="swiper-button-next"></div>
									<div class="swiper-button-prev"></div>
								</div>';
					*/
					
					$allBadges = $allBadges.'<span class="hide">'.$more.' <a class="links" href="/'.$username.'/badges">see all >>></a></span> <button data-toggle-attr="text|less" class="btn btn-xs" data-toggle="smartToggler" data-target-prev="true">more</button>';
					
				
				}
					
				$customBadgeListDisplay='';
				$badgeArr = array(array($golds, $goldBadges), array($silvers, $silverBadges), array($bronzes, $bronzeBadges));
				$badgeArrLen = count($badgeArr);	
					
				for($i = 0; $i < $badgeArrLen; $i++){
					
					$bg = 'bg-'.(($i == 0)? 'goldx' : (($i == 1)? 'silver' : (($i == 2)? 'bronze' : '')));
					$customBadgeListDisplay .= $badgeArr[$i][1]? '<div class="panel panel-cols panel-sqd '.($panelBlock? '' : 'col-lg-w-3-pull col-md-w-3-pull').'">
					<h3 class="panel-head panel-head-normalized align-c '.$bg.'" >'.$badgeArr[$i][0].'</h3><div class="panel-body align-l hr-dividers no-bg">'.$badgeArr[$i][1].'</div></div>' : '';
					
				}
					
			}
			
			$userBadgeCounts = !$medalIncs? $this->getBadgeCount(array('uid'=>$userId)) : '';
			
			if($medalIncs && $allBadges)
				$allBadges = '<div class="user-badges '.($ipb? 'post-body-ctrl' : '').'">'.$allBadges.'</div>';
					
			elseif(!$medalIncs)
				$allBadges = '<div class="row">'.
								($userBadgeCms? $medals : '').(($bid && !$alert)? '<div class="base-mg"><b>'.$name.' BADGE: </b><span class="prime">'.$this->decodeDbTagDelimeters($criteria, $name).'</span></div>' : '').
								(!$userBadgeCms?'
								<h3 class="cyan">BADGES AND REPUTATIONS:</h3>
								<div class="">'.$reputation.'</div>
								<h4 class="prime">Badges(<span class="">'.$userBadgeCounts.'</span>):</h4>' : '')
								.$customBadgeListDisplay.($reportErr? $alert : '').
							'</div>';
					
			
		}

		return ($retArr? array($allBadges, $userBadgeCounts) : $allBadges);
					
	}


	
	

	

	
	
	
		
	/*** Method for loading user same badges from database ***/
	public function getBadgeAwardees($metaArr=array()){
	
		$accUsers='';
	
		$bid = $this->ENGINE->get_assoc_arr($metaArr, 'bid'); 
		$xuid = $this->ENGINE->get_assoc_arr($metaArr, 'xuid');//XCLUDE USER 
		$i = $this->ENGINE->get_assoc_arr($metaArr, 'i'); 
		$i = $i? $i : 0;
		$n = $this->ENGINE->get_assoc_arr($metaArr, 'n');
		$n = $n? $n : 20;	
		$valArr = array($bid);
		$xuid? ($valArr[] = $xuid) : '';
		
		$sql = 'SELECT USER_ID, MAX(TIME) TIME FROM awarded_badges WHERE BADGE_ID = ? '.($xuid? ' AND USER_ID != ? ' : '').' GROUP BY USER_ID ORDER BY TIME DESC LIMIT '.$i.','.$n;
		
		if($stmt = $this->DBM->doSecuredQuery($sql, $valArr)){
	
			while($urow = $this->DBM->fetchRow($stmt)){

				$user = $urow["USER_ID"];				
				$accUsers .= $this->ACCOUNT->getUserVCard($user, array('time'=>$urow["TIME"]));

			}
			
			$accUsers = $accUsers? '<div class="hr-dividers align-l">'.$accUsers.'</div>' : '<span class="alert alert-danger">Sorry no '.($xuid? 'other ' : '').'user has been awarded this badge yet!</span>';

		}
	
		$accUsers = '<ol class="no-list-type">'.$this->loadBadges(array('bid'=>$bid)).'</ol><h2 class="cpop align-l">Recently Awarded To:</h2>'.$accUsers;
		
		return $accUsers;
	 
	}


	

	

	
	
	
		
	/*** Method for fetching badge sort navigator ***/
	public function getBadgeSortNav($metaArr){
		
		$sort = $this->ENGINE->get_assoc_arr($metaArr, 's1');
		$sort2 = $this->ENGINE->get_assoc_arr($metaArr, 's2');
		$getPage = $this->ENGINE->get_assoc_arr($metaArr, 'pid');
		$page_self_srt = $this->ENGINE->get_assoc_arr($metaArr, 'baseUrl');
		
		$sort = !in_array($sort, array('general', 'tags'))? '' : $sort;
		$sort2 = !in_array($sort2, array('gold', 'silver', 'bronze'))? 'all' : $sort2;
		
		switch($sort){
		
			case 'tags': $cid = TAG_BADGE_CATEGORY; break;
		
			default: $sort = 'general'; $cid = GENERIC_BADGE_CATEGORY;
		
		}
		
		switch($sort2){
		
			case 'gold': $clid = GOLD_BADGE_CLASS; break; 
		
			case 'silver': $clid = SILVER_BADGE_CLASS; break;
		
			case 'bronze': $clid = BRONZE_BADGE_CLASS; break;
		
			default: $clid = '';
		
		}

		$orderList = array(//FORMAT => urlSlug:urlLabel:urlIcon:ignoreCond
			($defaultOrder = 'general'), 'tags'
		);
		
		$filterList = array(//FORMAT => urlSlug:urlLabel:urlIcon:ignoreCond
			($defaultFilter = 'all'), 'gold', 'silver', 'bronze'
		);


		$sortNav = $this->SITE->buildSortLinks(array(
						'baseUrl' => $page_self_srt, 'pageId' => $getPage,
						'activeOrder' => $sort, 'orderList' => $orderList,
						'activeFilter' => $sort2, 'filterList' => $filterList,
						'defaultOrder' => $defaultOrder, 'defaultFilter' => $defaultFilter, 
						'orderGlobLabel' => 'Badge Type', 'filterGlobLabel' => 'Showing'
					));
					
		return array($sortNav, $cid, $clid, $sort, $sort2);
					
	}



	

	

	
	
	
		
	/*** Method for processing thread tags (involves toggling tag ids to tag names and vice versa) ***/
	public function processTags($metaArr){	
	
		$accTags=$accTagsDecoded=$accRegx="";	
		
		if(!empty($metaArr)){
			
			$encode = $this->ENGINE->get_assoc_arr($metaArr, 'encode');
			$decode = $this->ENGINE->get_assoc_arr($metaArr, 'decode');
			$updateId = $this->ENGINE->get_assoc_arr($metaArr, 'update');
			$rollBack = $this->ENGINE->get_assoc_arr($metaArr, 'rollBack');
			$tags = $this->ENGINE->get_assoc_arr($metaArr, 'tags');
			$tags = trim(str_ireplace(DB_GPREF, '', $tags), DB_GSUF);
			$tagsArr = explode(',', $tags);
		
			foreach($tagsArr as $tag){
		
				if(is_numeric($tag)){
		
					$tagsIdForInArr[] = $tag;
					continue;
		
				}
		
				$tagLC = strtolower($tag);
				$tagUCF = ucwords($tag);
				$title = ' title="Tag: '.$tagUCF.'" ';
				$accTagsDecoded .= '<a href="/'.THREAD_SLUG_CONSTANT.'/tagged/'.urlencode($tagLC).'" '.$title.' class="links no-hover-bg"><span id="'.$tagLC.'" class="thread-tag user-defined">'.$tagUCF.'</span></a>';
				
			}
		
			$tagsIdForIn = isset($tagsIdForInArr)? implode(',', $tagsIdForInArr) : 0;
			
			if($tags || $updateId){
		
				///REPLACE TAGS WITH THEIR CORRESPONDING ID FROM DB IF FOUND
				if($encode || $rollBack || $updateId){
					
					////PREPARE RLIKE REGEX
					if($tags && !$rollBack)
						foreach($tagsArr as $tag){
		
							if(!$tag) continue;
		
							$accRegx .= '^('.preg_replace_callback("#([^a-z0-9]+)#iU", 
									function($m){
										return '\\'.$m[1];
									}, $tag).')$|';
		
						}	
										
					$tagsCnd = $rollBack? $tagsIdForIn : rtrim($accRegx, '|');
									
					if($tagsCnd || $rollBack){
						
						/////PDO QUERY//////////
						
						$cnd = $rollBack? ' ID IN('.$tagsCnd.') AND CATEGORY=? ' : ' BADGE_NAME RLIKE ? AND CATEGORY=? LIMIT '.MAX_THREAD_TAG;
						$sql = 'SELECT GROUP_CONCAT(ID) AS BADGE_TAGS_ID, GROUP_CONCAT(BADGE_NAME) AS BADGE_TAGS_NAME FROM badges WHERE '.$cnd;
						$valArr = $rollBack? array(TAG_BADGE_CATEGORY) : array($tagsCnd, TAG_BADGE_CATEGORY);
						$stmt = $this->DBM->doSecuredQuery($sql, $valArr);			
						$row = $this->DBM->fetchRow($stmt);
						$badgeTagsIdArr = ($K=$row["BADGE_TAGS_ID"])? explode(',', $K) : '';
						$badgeTagsNameArr = explode(',', ($row[$K="BADGE_TAGS_NAME"]? $row[$K] : ''));
						$totBadgeTagsId = is_array($badgeTagsIdArr)? count($badgeTagsIdArr) : '';
		
						foreach($tagsArr as $perOrigTag){
		
							if(is_array($badgeTagsIdArr))
								for($i=0; $i < $totBadgeTagsId; $i++){
		
									$badgeTagId = $badgeTagsIdArr[$i];
									$badgeTagName = $badgeTagsNameArr[$i];
		
									if(!$badgeTagId) 
										continue;
		
									if(strtolower($perOrigTag) == strtolower($rollBack? $badgeTagId : $badgeTagName)){
		
										$tagUI = $rollBack? ucwords(strtolower($badgeTagName)) : $badgeTagId;
										break;
		
									}elseif(($i + 1) == $totBadgeTagsId)
										$tagUI = $perOrigTag;
		
								}
		
							else
								$tagUI = $perOrigTag;
		
							$accTags4Db[] = $rollBack? $tagUI : DB_GPREF.$tagUI.DB_GSUF;
		
						}
						
						$accTags = implode($rollBack? ',' : '', $rollBack? $accTags4Db : array_slice($accTags4Db, 0, MAX_THREAD_TAG, true));
		
					}
		
					if($updateId){
		
						$sql = 'UPDATE topics SET TAGS = ? WHERE ID = ? LIMIT 1';
						$valArr = array($accTags, $updateId);
						$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
							
					}
							
				}elseif($decode){
		
					if($tagsIdForIn)
						$accTags = $this->loadBadges(array('decodeTags'=>$tagsIdForIn, 'n'=>MAX_THREAD_TAG));
		
					$accTags .= $accTagsDecoded;
		
				}
			}			
		}
		
		return $accTags;
		
	}







	

	
	
	
		
	/*** Method for handling awarding of badge ***/
	public function awardBadge($candidateNeedle, $badgeName, $confiscate=false, $n=1){
		
		$ret=false;
		
		$user = $this->ACCOUNT->loadUser($candidateNeedle);
		$candidateId = $user->getUserId();
		$candidateOldRep = $user->getReputation();
			
		//MAKE SURE ONLY ACTIVATED/CONFIRMED USERS CAN BE AWARDED BADGES		
		if($candidateId && $user->getActivationStatus()){
		
			$badgeId = $this->getBadgeDetail($badgeName, 'ID');
		
			###ENSURE ONLY BADGES WITH MULTIPLE FREQ ARE AWARDED MORE THAN ONCE AND 
			###ALSO THAT STUDENT BADGES CAN'T BE CONFISCATED
			if(($this->badgeAwardCount($candidateId, $badgeName) && !$confiscate && strtolower($this->getBadgeDetail($badgeName, 'AWARD_FREQ')) != 'm')
				|| ($confiscate && strtolower($badgeName) == 'student'))
				return $ret;
		
			###MAKE SURE WHEN CONFISCATING BADGE THAT THE USER HAS THE BADGE##
			
			$sql = $confiscate? 'DELETE FROM awarded_badges WHERE USER_ID=? AND BADGE_ID=? LIMIT 1'			
					: 'INSERT INTO awarded_badges (USER_ID, BADGE_ID) VALUES(?,?)';
			
			$valArr = array($candidateId, $badgeId);
		
			$ret = ($this->DBM->doSecuredQuery($sql, $valArr, 'chain')->getRowCount() && $this->sendBadgeAwardPM($candidateId, $badgeName, $confiscate));
			
		
		}	
			
		return $ret;
		
	}



	

	

	
	
	
		
	/*** Method for handling awarding of reputation ***/
	public function awardReputation($candidateNeedle, $repPoints, $confiscate=false, $n=1){
		
		$ret=true;
		
		$user = $this->ACCOUNT->loadUser($candidateNeedle);
		$candidateId = $user->getUserId();
		$candidateOldRep = $user->getReputation();
		
		##WE DON'T WANT REPUTATION TO FALL BELOW 1
		$repPoints = (($confiscate && ($candidateOldRep - $repPoints) < 1) || $repPoints < 1 )? 1 : $repPoints;
		
		//MAKE SURE ONLY ACTIVATED/CONFIRMED USERS CAN EARN REPUTATIONS
		if($user->getActivationStatus()){
			
			for($i = 0; $i < $n; $i++){
		
				if($candidateId){
		
					###MAKE SURE WHEN CONFISCATING REPUTATION THAT YOU DON'T GO BELOW 1 POINT##
					if($confiscate){
		
						$col = ' REPUTATION = '.(($repPoints == 1)? '?' : '(REPUTATION - ?) ');
		
					}else{
		
						$col = ' REPUTATION = (REPUTATION + ?) ';
		
					}
					
					$ret = $this->ACCOUNT->updateUser($candidateId, $col, array($repPoints));
					
				}
				
			}
			
		}
			
		return $ret;
		
	}


	

	

	
	
	
		
	/*** Method for counting the a number of times a badge has been awarded to a user ***/
	public function badgeAwardCount($uid, $badgeName){
		
		$badgeId = $this->getBadgeDetail($badgeName, 'ID');
		
		/////PDO QUERY////////
		$sql = "SELECT COUNT(*)FROM awarded_badges WHERE USER_ID=? AND BADGE_ID = ?";
		$valArr = array($uid, $badgeId);
		return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
	}


	
	
	
	
		
	/*** Method for sending users pm when they get awarded new badges ***/
	public function sendBadgeAwardPM($uid, $badgeName, $confiscate=false){
		
		$stat=false;
		
		//MAKE SURE ONLY ACTIVATED/CONFIRMED USERS RECEIVE BADGE AWARD PMs
		if($this->ACCOUNT->loadUser($uid)->getActivationStatus()){
			
			$classCssColor = $this->getBadgeClassCssStyles($this->getBadgeDetail($badgeName, "CLASS"), 'color');
			$username = $this->ACCOUNT->memberIdToggle($uid);
			//$sender => "Webmaster";
			$senderId = 0;
			$unSan = $this->ACCOUNT->sanitizeUserSlug($username);
			$badgeName = '[a '.$unSan.'/badges/award-history/'.$this->getBadgeDetail($badgeName, "ID").'][spn class="bg-black base-lr-pad" title="'.$badgeName.'"][col='.$classCssColor.']'.strtoupper($badgeName).'[/col][/spn][/a]';
			$subject = $confiscate? '[spn class="red"]BADGE CONFISCATION NOTIFICATION[/spn]' : 'BADGE AWARD NOTIFICATION';
			
			$message = $confiscate? '[spn class="red"]Oops! [a '.$unSan.']'.$username.'[/a] you\'ve lost one of your '.$badgeName.' badge.[/spn]'
						: 'Congratulations [a '.$unSan.']'.$username.'[/a] you\'ve earned the '.$badgeName.' badge.';
			
			$stat = $this->SITE->sendPm($senderId, $uid, $subject, $message);
		
		}
		
		return $stat;
			
	}

	
	
	
	
	
		
	/*** Method for awarding certain badges to users on the fly immediately they fulfill the criteria ***/
	public function badgeAwardFly($uid, $badgeName){
		
		///////ALL FLY AWARDED ONLY ONCE //////
		switch(strtolower($badgeName)){

			case "analyst":{
		
				if(!$this->badgeAwardCount($uid, $badgeName)){	
					
					$this->awardBadge($uid, $badgeName);
		
				}
		
				break;
		
			}
			case "autobiographer":{
		
				/////PDO QUERY////////
				$sql = "SELECT ID FROM users WHERE (ID=? AND LENGTH(ABOUT_YOU) > 70 AND INSTR(ABOUT_YOU, ' ')) LIMIT 1";
				$valArr = array($uid);
				if($this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn() && !$this->badgeAwardCount($uid, $badgeName)){	
					
					$this->awardBadge($uid, $badgeName);
		
				}
		
				break;
		
			}
			case "excavator":{	
				
				if(!$this->badgeAwardCount($uid, $badgeName)){	
					
					$this->awardBadge($uid, $badgeName);
		
				}
		
				break;
		
			}
			case "licit":{	
				
				if(!$this->badgeAwardCount($uid, $badgeName)){
						
					$this->awardBadge($uid, $badgeName);
		
				}
		
				break;
		
			}
			case "editor":{	
				
				if(!$this->badgeAwardCount($uid, $badgeName)){	
					
					$this->awardBadge($uid, $badgeName);
		
				}
		
				break;
		
			}	
			case "student":{
					
				if(!$this->badgeAwardCount($uid, $badgeName)){	
					
					$this->awardBadge($uid, $badgeName);
		
				}
		
				break;
		
			}			
		}
	}






	
	
	
	
	
		
	/*** Method for awarding users year-end criterion badges (cron job) ***/
	public function awardYearEndBadges(){

		$cronArr = array('disciplined');

		if(getAutoPilotState($col="CRON_BADGE_AWARD")){

			$limit = $this->DBM->getMaxRowPerSelect();

			foreach($cronArr as $badgeName){
				
				switch(strtolower($badgeName)){
					
					case "disciplined":{
						/*********DISCIPLINED**********
						MAKE 1000 POSTS IN 1 YEAR WITHOUT A FLAG
						****AWARDED MULTIPLE TIMES***/
						$cond = 1000 ;
						$cond2 = ' = 0' ;

						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;					
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							While($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
								
								/////PDO QUERY////////				
								$sql = "SELECT COUNT(*) FROM (SELECT YEAR(p.TIME) AS YR, COUNT(*) AS TOTAL_POSTS,
										(SELECT COUNT(*) FROM reported_posts r JOIN posts p ON p.ID=r.POST_ID WHERE POST_AUTHOR_ID=? AND YEAR(r.TIME) = YR) AS YR_FLAGS FROM posts p 
										WHERE POST_AUTHOR_ID=? GROUP BY YR HAVING TOTAL_POSTS >= ".$cond." AND YR_FLAGS ".$cond2.") tmp";
								$valArr = array($uid, $uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);						
								
								if($uid && ($oldNTimes != $nTimes)){

									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);

								}
								
							}
							
						}
	
						break;

					}
					
				}
			}
		}
	}





	
	
	
	
	
		
	/*** Method for awarding users daily criterion badges (cron job) ***/
	public function awardDailyBadges(){

		$cronArr = array('fanatic', 'enthusiast');

		if(getAutoPilotState($col="CRON_BADGE_AWARD")){
			
			$limit = $this->DBM->getMaxRowPerSelect();
			
			foreach($cronArr as $badgeName){
				
				switch(strtolower($badgeName)){
					
					case "enthusiast":{
						/*********ENTHUSIAST**********
						VISIT THE SITE FOR 30 DAYS
						****AWARDED MULTIPLE TIMES***/
						$cond = 30;

						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID, DVC FROM users WHERE DVC >= ".$cond." LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
								$nTimes = (int)(floor($row["DVC"] / $cond));					
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);						
												
								if($uid && ($oldNTimes != $nTimes)){

									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);

								}
							}
							
						}
	
						break;

					}
					case "fanatic":{
						/*********FANATIC**********
						VISIT THE SITE FOR 100 DAYS
						****AWARDED MULTIPLE TIMES***/
						$cond = 100;

						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID, DVC FROM users WHERE DVC >= ".$cond." LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
								$nTimes = (int)(floor($row["DVC"] / $cond));					
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);						
												
								if($uid && ($oldNTimes != $nTimes)){

									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);

								}
								
							}
							
						}
	
						break;

					}
				}
			}
		}
	}
	
	
	
	
	
	
	
	
			
	
	
		
	/*** Method for awarding users periodic criterion badges (cron job) ***/
	public function awardPeriodicBadges(){
		
		$esc = '\\\\';	
		$codeRegex = $esc.'[cd(i|b)?(=[a-z0-9]+)?'.$esc.'].+'.$esc.'[/cd(i|b)?'.$esc.']';
		$quoteRegex = $esc.'[quote author=.+'.$esc.'].+'.$esc.'[/quote'.$esc.']';	
		$limit = $this->DBM->getMaxRowPerSelect();
		$xcludes = '"disciplined","enthusiast","fanatic"';
		
		##NOTE THAT LOOP BREAKS ARE EXTREMELY VITAL HERE TO AVOID INFINITE LOOPS
		
		/*****GET ALL BADGES AND EVALUTE CRITERIAS******/
		
		for($startx=0; ; $startx += $limit){
		
			/////PDO QUERY////////
			$sql = "SELECT * FROM badges WHERE BADGE_NAME NOT IN(".$xcludes.") LIMIT ".$startx.",".$limit;
			$valArr = array();
			$stmtx = $this->DBM->doSecuredQuery($sql, $valArr, true);
			
			/////IMPORTANT INFINITE LOOP CONTROL ////
			if(!$this->DBM->getSelectCount())
				break;
							
			while($rowx = $this->DBM->fetchRow($stmtx)){
				
				$badgeId = $rowx["ID"];
				$badgeName = $rowx["BADGE_NAME"];
				$badgeCateg = $rowx["CATEGORY"];
				$badgeClass = $rowx["CLASS"];
				$accompaniedPoints = $rowx["REPUTATION_REWARD"];				
				
				switch(strtolower($badgeName)){
					
					case "alpha":{
						/*********ALPHA**********
						FIRST 1000 POSTS BY A USER
						****AWARDED ONLY ONCE ***/
						$cond = 1000;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT COUNT(*) AS USER_POSTS, POST_AUTHOR_ID FROM posts 							
									GROUP BY POST_AUTHOR_ID HAVING USER_POSTS >= ".$cond." LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["POST_AUTHOR_ID"];
		
								if(!$this->badgeAwardCount($uid, $badgeName)){
		
									$this->awardBadge($uid, $badgeName);
		
								}
								
							}
							
						}
						
						break;
		
					}
					case "beta":{
						/*********BETA**********
						FIRST 30 THREAD BY A USER IN ANY SECTION
						****AWARDED MULTIPLE TIMES***/
						$cond = 30;
						
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;				
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["ID"];										
							
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT COUNT(*) AS TOTAL_THREADS FROM topics WHERE TOPIC_AUTHOR_ID=? GROUP BY SECTION_ID HAVING TOTAL_THREADS >= ".$cond.") tmp";
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
													
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}	
								
							}
							
						}
						
						break;
		
					}
					case "benefactor":{
						/*********BENEFACTOR**********
						SHARE A THREAD (YOU ARE'NT THE AUTHOR) THAT GETS 300 UNIQUE VISITS
						****AWARDED MULTIPLE TIMES***/
						$cond = 300;
		
						for($start=0; ; $start += $limit){
								
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;		
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["ID"];								
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT 
										(SELECT COUNT(*) FROM topic_views v WHERE v.TIME > s.TIME AND v.TOPIC_ID = s.TOPIC_ID)
										AS IP_HITS FROM topic_social_shares s JOIN topics t ON s.TOPIC_ID=t.ID WHERE 
										USER_ID=? AND TOPIC_AUTHOR_ID !=? GROUP BY TOPIC_ID HAVING IP_HITS >= ".$cond.") tmp";
								$valArr = array($uid, $uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}	
								
							}
							
							
						}
							
						break;
		
					}						
					case "booster":{
						/*********BOOSTER**********
						SHARE A THREAD (YOU ARE THE AUTHOR) THAT GETS 300 UNIQUE VISITS
						****AWARDED MULTIPLE TIMES***/
						$cond = 300;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;		
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["ID"];								
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT 
										(SELECT COUNT(*) FROM topic_views v WHERE v.TIME > s.TIME AND v.TOPIC_ID = s.TOPIC_ID)
										AS IP_HITS FROM topic_social_shares s JOIN topics t ON s.TOPIC_ID=t.ID WHERE 
										USER_ID=? AND TOPIC_AUTHOR_ID =? GROUP BY TOPIC_ID HAVING IP_HITS >= ".$cond.") tmp";
								$valArr = array($uid, $uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
							
						break;
		
					}						
					case "announcer":{
						/*********ANNOUNCER**********
						SHARE ANY THREAD THAT GETS 30 UNIQUE VISITS
						****AWARDED MULTIPLE TIMES***/
						$cond = 30;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;		
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["ID"];								
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT 
										(SELECT COUNT(*) FROM topic_views v WHERE v.TIME > s.TIME AND v.TOPIC_ID = s.TOPIC_ID)
										AS IP_HITS FROM topic_social_shares s WHERE USER_ID=?
										GROUP BY TOPIC_ID HAVING IP_HITS >= ".$cond.") tmp";
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}	
								
							}
							
						}
		
						break;
		
					}
					case "publicist":{
						/*********PUBLICIST**********
						SHARE ANY THREAD THAT GETS 1000 UNIQUE VISITS
						****AWARDED MULTIPLE TIMES***/
						$cond = 1000;
		
						for($start=0; ; $start += $limit){
											
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;		
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["ID"];								
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT  s.USER_ID, 
										(SELECT COUNT(*) FROM topic_views v WHERE v.TIME > s.TIME AND v.TOPIC_ID = s.TOPIC_ID)
										AS IP_HITS FROM topic_social_shares s WHERE USER_ID=?
										GROUP BY TOPIC_ID HAVING IP_HITS >= ".$cond.") tmp";
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
		
						break;
		
					}
					case "socialite":{
						/*********SOCIALITE**********
						SHARE 30 THREADS
						****AWARDED MULTIPLE TIMES***/
						$cond = 30;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT s.USER_ID, COUNT(DISTINCT s.TOPIC_ID) AS SHARED_THREADS 
									FROM topic_social_shares s GROUP BY s.USER_ID  HAVING SHARED_THREADS >= ".$cond." LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["USER_ID"];								
								$nTimes = (int)(floor($row["SHARED_THREADS"] / $cond));					
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}			
					case "civic duty":{
						/*********CIVIC DUTY**********
						VOTE 300 TIMES IN ANY THREAD
						****AWARDED MULTIPLE TIMES***/
						$cond = 300;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["ID"];								
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT COUNT(*) AS NVOTES FROM upvotes u JOIN posts p ON p.ID=u.POST_ID 
										WHERE (u.UPPER_ID = ? AND u.STATE=1) GROUP BY p.TOPIC_ID HAVING NVOTES >= ".$cond.") tmp";
								$valArr = array($uid);
								$nTimes1 = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT COUNT(*) AS NVOTES FROM downvotes d JOIN posts p ON p.ID=d.POST_ID 
										WHERE (d.DOWNER_ID = ? AND d.STATE=1) GROUP BY p.TOPIC_ID HAVING NVOTES >= ".$cond.") tmp";
								$valArr = array($uid);
								$nTimes2 = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								///EVALUATE RESULTANT NTIMES
								$nTimes = ($nTimes1 + $nTimes2);
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "electorate":{
						/*********ELECTORATE**********
						VOTE 50 TIMES IN ANY THREAD
						****AWARDED MULTIPLE TIMES***/
						$cond = 50;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["ID"];								
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT COUNT(*) AS NVOTES, p.TOPIC_ID FROM upvotes u JOIN posts p ON p.ID=u.POST_ID 
										WHERE (UPPER_ID = ? AND u.STATE=1) GROUP BY p.TOPIC_ID HAVING NVOTES >= ".$cond.") tmp";
								$valArr = array($uid);
								$nTimes1 = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT COUNT(*) AS NVOTES, p.TOPIC_ID FROM downvotes d JOIN posts p ON p.ID=d.POST_ID 
										WHERE (DOWNER_ID = ? AND d.STATE=1) GROUP BY p.TOPIC_ID HAVING NVOTES >= ".$cond.") tmp";
								$valArr = array($uid);
								$nTimes2 = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								///EVALUATE RESULTANT NTIMES
								$nTimes = ($nTimes1 + $nTimes2);
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}	
								
							}
							
						}
			
						break;
		
					}
					case "companion":{
						/*********COMPANION**********
						2ND UNIQUE USER TO MAKE A POST IN ANY THREAD
						****AWARDED MULTIPLE TIMES***/
						$cond = null;
						
						for($start=0; ; $start += $limit){
						
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;				
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["ID"];								
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT p.POST_AUTHOR_ID FROM posts p 
										JOIN topics t ON p.TOPIC_ID=t.ID WHERE p.POST_AUTHOR_ID != t.TOPIC_AUTHOR_ID AND 
										p.POST_AUTHOR_ID=? GROUP BY p.TOPIC_ID) tmp";
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
									
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
									
								}
								
							}
							
						}	
		
						break;
		
					}
					case "illustrator":{
						/*********ILLUSTRATOR**********
						FIRST CODE SENT BY A USER IN ANY THREAD WITH 5 UPVOTES
						****AWARDED MULTIPLE TIMES***/
						$cond = 5;
						
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;		
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["ID"];								
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT (SELECT COUNT(*) FROM upvotes WHERE (POST_ID=p.ID AND STATE=1)) AS UPVOTES, 
										(SELECT COUNT(*) FROM downvotes WHERE (POST_ID=p.ID AND STATE=1)) AS DOWNVOTES FROM posts p JOIN upvotes u ON u.POST_ID=p.ID AND u.STATE=1
										WHERE MESSAGE RLIKE '".$codeRegex."' AND POST_AUTHOR_ID=? GROUP BY TOPIC_ID HAVING (UPVOTES - DOWNVOTES) >= ".$cond.") tmp";
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}	
								
							}
							
						}
			
						break;
		
					}			
					case "creditor":{
						/*********CREDITOR**********
						FIRST POST TO ANY THREAD WHERE YOU CITE/QUOTE ANOTHER HAVING 1 OR MORE VOTES 
						****AWARDED MULTIPLE TIMES***/
						$cond = 1;
						
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;		
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);									
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["ID"];	
		
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) AS AWARD_NTIMES FROM (SELECT (SELECT COUNT(*) FROM upvotes WHERE (POST_ID=p.ID AND STATE=1)) AS UPVOTES,
										(SELECT COUNT(*) FROM downvotes WHERE (POST_ID=p.ID AND STATE=1)) AS DOWNVOTES FROM posts p JOIN upvotes u ON u.POST_ID=p.ID AND u.STATE=1
										WHERE MESSAGE RLIKE '".$quoteRegex."' AND POST_AUTHOR_ID=? GROUP BY TOPIC_ID HAVING (UPVOTES - DOWNVOTES) >= ".$cond.") tmp";
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "critic":{
						/*********CRITIC**********
						FIRST DOWN VOTE
						****AWARDED ONLY ONCE***/
						$cond = null;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["ID"];								
								
								/////PDO QUERY////////
								$sql = "SELECT ID FROM downvotes WHERE DOWNER_ID = ? LIMIT 1";
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								if($nTimes && !$this->badgeAwardCount($uid, $badgeName)){
		
									$this->awardBadge($uid, $badgeName);
		
								}
								
							}
							
						}
		
						break;
		
					}
					case "supporter":{
						/*********SUPPORTER**********
						FIRST UP VOTE
						****AWARDED ONLY ONCE***/
						$cond = null;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["ID"];								
								
								/////PDO QUERY////////
								$sql = "SELECT ID FROM upvotes WHERE (UPPER_ID = ? AND STATE=1) LIMIT 1";
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								if($nTimes && !$this->badgeAwardCount($uid, $badgeName)){
		
									$this->awardBadge($uid, $badgeName);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "patrol":{
						/*********PATROL**********
						FIRST USEFUL FLAG RAISED
						****AWARDED ONLY ONCE***/
						$cond = null;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT REPORTER_ID FROM reported_posts GROUP BY REPORTER_ID LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["REPORTER_ID"];													
								
								if($uid && !$this->badgeAwardCount($uid, $badgeName)){
		
									$this->awardBadge($uid, $badgeName);
		
								}
								
							}
							
						}	
		
						break;
		
					}
					case "detective":{
						/*********DETECTIVE**********
						RAISE 80 HELPFUL FLAGS
						****AWARDED MULTIPLE TIMES***/
						$cond = 80;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT REPORTER_ID, COUNT(*) AS TOTAL_RAISED FROM reported_posts 
									GROUP BY REPORTER_ID HAVING TOTAL_RAISED >= ".$cond." LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["REPORTER_ID"];								
								$nTimes = (int)(floor($row["TOTAL_RAISED"] / $cond));								
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "marshall":{
						/*********MARSHALL**********
						RAISE 300 HELPFUL FLAGS
						****AWARDED MULTIPLE TIMES***/
						$cond = 300;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT REPORTER_ID, COUNT(*) AS TOTAL_RAISED FROM reported_posts 
									GROUP BY REPORTER_ID HAVING TOTAL_RAISED >= ".$cond." LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["REPORTER_ID"];								
								$nTimes = (int)(floor($row["TOTAL_RAISED"] / $cond));								
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}			
					case "major":{
						/*********MAJOR**********
						RAISE 500 HELPFUL FLAGS
						****AWARDED MULTIPLE TIMES***/
						$cond = 500;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT REPORTER_ID, COUNT(*) AS TOTAL_RAISED FROM reported_posts 
									GROUP BY REPORTER_ID HAVING TOTAL_RAISED >= ".$cond." LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["REPORTER_ID"];								
								$nTimes = (int)(floor($row["TOTAL_RAISED"] / $cond));								
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}			
					case "enlightened":{
						/*********ENLIGHTENED**********
						CREATE 200 THREADS
						****AWARDED MULTIPLE TIMES***/
						$cond = 200;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT TOPIC_AUTHOR_ID, COUNT(*) AS TOTAL_THREADS FROM topics 
									GROUP BY TOPIC_AUTHOR_ID  HAVING TOTAL_THREADS >= ".$cond." LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["TOPIC_AUTHOR_ID"];
								$nTimes = (int)(floor($row["TOTAL_THREADS"] / $cond));
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}	
								
							}
							
						}	
		
						break;
		
					}
					case "rookie":{
						/*********ROOKIE**********
						CREATE YOUR FIRST THREAD IN ANY SECTION
						****AWARDED MULTIPLE TIMES***/
						$cond = 1;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT TOPIC_AUTHOR_ID FROM topics GROUP BY TOPIC_AUTHOR_ID LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["TOPIC_AUTHOR_ID"];
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT TOPIC_AUTHOR_ID FROM topics WHERE TOPIC_AUTHOR_ID=? 
										GROUP BY SECTION_ID) tmp";
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}	
								
							}
							
						}
			
						break;
		
					}
					case "epic":{
						/*********SCORE 500 VOTES EACH FROM 5 POSTS IN ONE THREAD**********				
						****AWARDED MULTIPLE TIMES***/
						$cond = 500; ///500 VOTES///////
						$cond2 = 5; ///5 POSTS/////
						
						for($start=0; ; $start += $limit){
							 
							/////PDO QUERY////////				
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;		
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["ID"];								
												
								/////PDO QUERY////////							
								$sql = "SELECT COUNT(*) FROM (SELECT COUNT(*) AS POSTS_PER_TOPIC FROM (SELECT TOPIC_ID, COUNT(*) AS UPVOTES, 
										(SELECT COUNT(*) FROM downvotes WHERE (POST_ID=u.POST_ID AND STATE=1)) AS DOWNVOTES FROM upvotes u
										JOIN posts p ON p.ID=u.POST_ID AND u.STATE=1 WHERE (POST_AUTHOR_ID=?) GROUP BY POST_ID HAVING (UPVOTES - DOWNVOTES) >= ".$cond.") tmp 
										GROUP BY TOPIC_ID HAVING POSTS_PER_TOPIC >= ".$cond2.") tmp2"; 
										
								$valArr = array($uid);							
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "guru":{
						/*********GURU**********
						SCORE 1000 UPVOTES IN ONE POST
						****AWARDED MULTIPLE TIMES***/
						$cond = 1000;
						
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT POST_AUTHOR_ID FROM upvotes u JOIN posts p ON u.POST_ID=p.ID AND u.STATE=1
								GROUP BY POST_AUTHOR_ID LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["POST_AUTHOR_ID"];							
														
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT COUNT(*) AS UPVOTES, 
									(SELECT COUNT(*) FROM downvotes WHERE (POST_ID=u.POST_ID AND STATE=1)) AS DOWNVOTES FROM upvotes u JOIN posts p ON u.POST_ID=p.ID AND u.STATE=1
									WHERE (POST_AUTHOR_ID=?) GROUP BY POST_ID HAVING (UPVOTES - DOWNVOTES) >= ".$cond.") tmp";
								$valArr = array($uid);							
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);					
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}			
					case "outspoken":{
						/*********OUTSPOKEN**********
						SCORE 100 NON-SOCIAL SHARES IN ANY POST
						****AWARDED MULTIPLE TIMES***/
						$cond = 100;
						
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT POST_AUTHOR_ID FROM shares s JOIN posts p ON s.POST_ID=p.ID WHERE s.STATE=1 
								GROUP BY POST_AUTHOR_ID LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["POST_AUTHOR_ID"];
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT COUNT(*) AS SHARES FROM 
									shares s JOIN posts p ON s.POST_ID=p.ID WHERE POST_AUTHOR_ID=?
									GROUP BY POST_ID HAVING SHARES >= ".$cond.") tmp";
								$valArr = array($uid);							
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);					
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}			
					case "knowledgeable":{
						/*********KNOWLEDGEABLE**********
						CREATE 20 THREADS THAT HITS 300 PARTICIPANTS EACH
						****AWARDED MULTIPLE TIMES***/
						$cond = 300; //PARTICIPANTS//
						$cond2 = 20; //THREADS//
						
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT TOPIC_AUTHOR_ID FROM topics GROUP BY TOPIC_AUTHOR_ID LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["TOPIC_AUTHOR_ID"];
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) AS THREADS FROM (SELECT (SELECT COUNT(DISTINCT POST_AUTHOR_ID)
										FROM posts WHERE TOPIC_ID = t.ID) AS PARTICIPANTS FROM topics t WHERE TOPIC_AUTHOR_ID=?
										GROUP BY t.ID HAVING PARTICIPANTS >= ".$cond.") tmp HAVING THREADS >= ".$cond2;							
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$nTimes = (int)(floor($nTimes / $cond2));
													
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}	
								
							}
							
						}
			
						break;
		
					}
					case "legend":{
						/*********LEGEND**********
						GAIN 100 FOLLOWERS
						****AWARDED ONLY ONCE***/
						$cond = 100;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT USER_ID, COUNT(*) AS FOLLOWERS FROM members_follows WHERE STATE=1 GROUP BY USER_ID HAVING FOLLOWERS >= ".$cond."  LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["USER_ID"];													
								
								if($uid && !$this->badgeAwardCount($uid, $badgeName)){
		
									$this->awardBadge($uid, $badgeName);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "famous":{
						/*********FAMOUS**********
						GAIN 50 FOLLOWERS
						****AWARDED ONLY ONCE***/
						$cond = 50;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT USER_ID, COUNT(*) AS FOLLOWERS FROM members_follows WHERE STATE=1 GROUP BY USER_ID HAVING FOLLOWERS >= ".$cond." LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
		
								$uid = $row["USER_ID"];													
								
								if($uid && !$this->badgeAwardCount($uid, $badgeName)){
		
									$this->awardBadge($uid, $badgeName);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "pioneer":{
						/*********PIONEER**********
						FIRST THREAD OF THE YEAR IN ANY SECTION
						****AWARDED MULTIPLE TIMES***/
						$cond = null;

						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT TOPIC_AUTHOR_ID FROM topics GROUP BY TOPIC_AUTHOR_ID LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["TOPIC_AUTHOR_ID"];
							
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT TOPIC_AUTHOR_ID,YEAR(TIME) AS YR 
										FROM topics GROUP BY SECTION_ID, YR HAVING TOPIC_AUTHOR_ID=?) tmp";
								$valArr = array($uid);						
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "yearling":{
						/*********YEARLING**********
						ACTIVE FOR 1 YR WITH 300+ POSTS
						****AWARDED MULTIPLE TIMES***/
						$cond = 300;
						
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;		
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
		
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT YEAR(TIME) AS YR, 
										COUNT(*) AS YR_POSTS FROM posts WHERE POST_AUTHOR_ID=? 
										GROUP BY YR HAVING YR_POSTS >= ".$cond.") tmp";
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
													
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}	
		
						break;
		
					}
					case "year-end":{
						/*********YEAR-END**********
						MAKE AT LEAST 50 POSTS ON DECEMBER 31
						****AWARDED MULTIPLE TIMES***/
						$cond = 12; ///DECEMBER MONTH///
						$cond2 = 31; ////DECEMBER 31//////
						$cond3 = 50; //POSTS////
						
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;		
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
		
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT YEAR(TIME) AS YR, MONTH(TIME) AS MONTH, DAYOFMONTH(TIME) AS DAY, COUNT(*) AS TOTAL_POSTS 
										FROM posts WHERE POST_AUTHOR_ID=? GROUP BY YR, MONTH, DAY HAVING MONTH = ".$cond." AND DAY = ".$cond2." 
										AND TOTAL_POSTS >= ".$cond3.") tmp";
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
													
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}	
		
						break;
		
					}
					case "constable":{
						/*********CONSTABLE**********
						SERVE AS AN ELECTED/PRO TEM MOD FOR 1 YR
						****AWARDED MULTIPLE TIMES***/
						$cond = 365;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT u.ID, DATEDIFF(NOW(), m.TIME) AS DAYS FROM users u JOIN moderators m ON u.ID=m.USER_ID
									WHERE USER_PRIVILEGE !='' GROUP BY m.USER_ID HAVING DAYS >= ".$cond." LIMIT ".$start.",".$limit;						
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
								$nTimes = (int)(floor($row["DAYS"] / $cond));					
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);						
												
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "sheriff":{
						/*********SHERIFF**********
						SERVE AS AN ELECTED/PRO TEM SUPER MOD FOR 1 YR
						****AWARDED MULTIPLE TIMES***/
						$cond = 365;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT u.ID, DATEDIFF(NOW(), m.TIME) AS DAYS FROM users u JOIN moderators m ON u.ID=m.USER_ID AND LEVEL=2
									WHERE USER_PRIVILEGE !='' GROUP BY m.USER_ID HAVING DAYS >= ".$cond." LIMIT ".$start.",".$limit;						
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
								$nTimes = (int)(floor($row["DAYS"] / $cond));					
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);						
												
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}			
					case "steward":{
						/*********STEWARD**********
						SERVE AS AN ELECTED/PROTEM MOD AND REVIEW/TREAT 200 REPORT CASES
						****AWARDED MULTIPLE TIMES***/
						$cond = 200;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT u.ID, (SELECT COUNT(*) FROM reported_posts r WHERE r.TREATED_BY=u.ID) AS TREATED_CASES FROM users u 
									JOIN moderators m ON u.ID=m.USER_ID WHERE u.USER_PRIVILEGE !=''
									GROUP BY m.USER_ID HAVING TREATED_CASES >= ".$cond." LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
								$nTimes = (int)(floor($row["TREATED_CASES"] / $cond));					
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);						
												
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "custodian":{
						/*********CUSTODIAN**********
						SERVE AS AN ELECTED/PROTEM MOD AND REVIEW/TREAT 800 REPORT CASES
						****AWARDED MULTIPLE TIMES***/
						$cond = 800;
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT u.ID, (SELECT COUNT(*) FROM reported_posts r WHERE r.TREATED_BY=u.ID) AS TREATED_CASES FROM users u 
									JOIN moderators m ON u.ID=m.USER_ID WHERE u.USER_PRIVILEGE !=''
									GROUP BY m.USER_ID HAVING TREATED_CASES >= ".$cond." LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
								$nTimes = (int)(floor($row["TREATED_CASES"] / $cond));					
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);						
												
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}			
					case "inquisitive":{
						/*********INQUISITIVE**********
						PARTICIPATE IN 20 THREADS MAKING AT LEAST 300 POSTS EACH
						****AWARDED MULTIPLE TIMES***/
						$cond = 300; ///POSTS////
						$cond2 = 20; ///THREADS///
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) AS TOTAL_THREADS FROM (SELECT COUNT(*) AS POSTS 
										FROM posts WHERE POST_AUTHOR_ID=? GROUP BY TOPIC_ID HAVING POSTS >= ".$cond.")
										tmp HAVING TOTAL_THREADS >= ".$cond2;
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								$nTimes = (int)(floor($nTimes / $cond2));
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);						
												
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "informed":{
						/*********INFORMED**********
						PARTICIPATE IN 50 THREADS MAKING AT LEAST 300 POSTS EACH
						****AWARDED MULTIPLE TIMES***/
						$cond = 300; ///POSTS////
						$cond2 = 50; ///THREADS////
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) AS TOTAL_THREADS FROM (SELECT COUNT(*) AS POSTS 
										FROM posts WHERE POST_AUTHOR_ID=? GROUP BY TOPIC_ID HAVING POSTS >= ".$cond.")
										tmp HAVING TOTAL_THREADS >= ".$cond2;
								$valArr = array($uid);
								
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								$nTimes = (int)(floor($nTimes / $cond2));
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);						
												
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "scholar":{
						/*********SCHOLAR**********
						PARTICIPATE IN 200 THREADS MAKING AT LEAST 1000 POSTS EACH
						****AWARDED MULTIPLE TIMES***/
						$cond = 1000; ///POSTS////
						$cond2 = 200; ///THREADS////
		
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) AS TOTAL_THREADS FROM (SELECT COUNT(*) AS POSTS 
										FROM posts WHERE POST_AUTHOR_ID=? GROUP BY TOPIC_ID HAVING POSTS >= ".$cond.")
										tmp HAVING TOTAL_THREADS >= ".$cond2;
								$valArr = array($uid);
								
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								$nTimes = (int)(floor($nTimes / $cond2));
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);							
												
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "photogenic":{
						/*********PHOTOGENIC**********
						UPLOAD YOUR AVATAR AND GAIN 10 LIKES ON IT
						****AWARDED ONLY ONCE***/
						$cond = 10; 
						
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT USER_ID, COUNT(*) AS LIKES FROM avatar_likes WHERE STATE=1 GROUP BY USER_ID HAVING LIKES >= ".$cond."  LIMIT ".$start.",".$limit;
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["USER_ID"];
								
								if(!$this->badgeAwardCount($uid, $badgeName)){
		
									$this->awardBadge($uid, $badgeName);
		
								}
								
							}
							
						}	
		
						break;
		
					}
					case "curious":{
						/*********CURIOUS**********
						PARTICIPATE IN A THREAD FOR 1 YEAR MAKING AT LEAST 2000 POSTS
						****AWARDED MULTIPLE TIMES***/
						$cond = 2000;
						
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////					
							$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;		
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
								
								/////PDO QUERY////////
								$sql = "SELECT COUNT(*) FROM (SELECT YEAR(TIME) AS YR, COUNT(*) AS TOTAL_POSTS FROM posts p 
										WHERE POST_AUTHOR_ID=? GROUP BY YR, TOPIC_ID  HAVING TOTAL_POSTS >= ".$cond.") tmp";
								$valArr = array($uid);
								$nTimes = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "salient":{
						/*********SALIENT**********
						CREATE A THREAD THAT MAKES IT TO FRONTPAGE
						****AWARDED MULTIPLE TIMES***/
						$cond =  null;
						
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT TOPIC_AUTHOR_ID, COUNT(*) AS AWARD_NTIMES FROM topics WHERE 
									FEATURED != 0 AND FEATURE_TIME != '' GROUP BY TOPIC_AUTHOR_ID LIMIT ".$start.",".$limit;					
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["TOPIC_AUTHOR_ID"];
								$nTimes = $row["AWARD_NTIMES"];
								
								$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
								
								if($uid && ($oldNTimes != $nTimes)){
		
									$confiscate = ($oldNTimes > $nTimes);
									$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
									$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
								}
								
							}
							
						}	
		
						break;
		
					}
					case "citizen":{
						/*********CITIZEN**********
						PARTICIPATE ON THE COMMUNITY FOR 5 YEARS CREATING AT LEAST 50 THREADS AND
						MAKING AT LEAST 5000 POSTS
						****AWARDED ONLY ONCE***/
						$cond = 5; ///5 YRS///
						$cond2 = 50; ///THREADS///
						$cond3 = 5000; ///POSTS/////
						
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT u.ID, (YEAR(NOW()) - YEAR(TIME)) AS YR_REG, 
									(SELECT COUNT(*) FROM topics t WHERE t.TOPIC_AUTHOR_ID=u.ID) AS TOPICS,
									(SELECT COUNT(*) FROM posts p WHERE p.POST_AUTHOR_ID=u.ID) AS POSTS
									FROM users u HAVING YR_REG >= ".$cond." AND TOPICS >= ".$cond2." AND POSTS >= ".$cond3." 
									LIMIT ".$start.",".$limit;					
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
								
								if(!$this->badgeAwardCount($uid, $badgeName)){
		
									$this->awardBadge($uid, $badgeName);
		
								}
								
							}
							
						}
			
						break;
		
					}
					case "antique":{
						/*********ANTIQUE**********
						PARTICIPATE ON THE COMMUNITY FOR 2 YEARS CREATING AT LEAST 10 THREADS AND
						MAKING AT LEAST 2000 POSTS
						****AWARDED ONLY ONCE***/
						$cond = 2; ///2 YRS///
						$cond2 = 10; ///THREADS///
						$cond3 = 2000; ///POSTS/////
						
						for($start=0; ; $start += $limit){
							
							/////PDO QUERY////////
							$sql = "SELECT u.ID, (YEAR(NOW()) - YEAR(TIME)) AS YR_REG, 
									(SELECT COUNT(*) FROM topics t WHERE t.TOPIC_AUTHOR_ID=u.ID) AS TOPICS,
									(SELECT COUNT(*) FROM posts p WHERE p.POST_AUTHOR_ID=u.ID) AS POSTS
									FROM users u HAVING YR_REG >= ".$cond." AND TOPICS >= ".$cond2." AND POSTS >= ".$cond3." 
									LIMIT ".$start.",".$limit;					
							$valArr = array();
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
							
							/////IMPORTANT INFINITE LOOP CONTROL ////
							if(!$this->DBM->getSelectCount())
								break;
							
							while($row = $this->DBM->fetchRow($stmt)){
								
								$uid = $row["ID"];
								
								if(!$this->badgeAwardCount($uid, $badgeName)){
		
									$this->awardBadge($uid, $badgeName);
		
								}
								
							}
							
						}	
		
						break;
		
					}
					default:{
						#####AWARD TAGS
						if($badgeCateg == 2){
		
							/*********SCORE THE FOLLOWING**********	
							VOTES(cond): 
								BRONZE => 100
								SILVER => 400
								GOLD => 1000
							POSTS(cond2):
								BRONZE => 20
								SILVER => 100
								GOLD => 200
							****AWARDED MULTIPLE TIMES***/
							switch($badgeClass){
		
								case GOLD_BADGE_CLASS: $cond = 1000; $cond2 = 200; break;
		
								case SILVER_BADGE_CLASS: $cond = 400; $cond2 = 100; break;
		
								default: $cond = 100; $cond2 = 20;	
								
							}
							
							$tagRegex = $esc.DB_GPREF.$badgeId.DB_GSUF;
							
							for($start=0; ; $start += $limit){
								 
								/////PDO QUERY////////				
								$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;		
								$valArr = array();
								$stmt1 = $this->DBM->doSecuredQuery($sql, $valArr, true);
								
								/////IMPORTANT INFINITE LOOP CONTROL ////
								if(!$this->DBM->getSelectCount())
									break;
							
								while($row = $this->DBM->fetchRow($stmt1)){
		
									$uid = $row["ID"];								
													
									/////PDO QUERY////////							
									$sql = "SELECT COUNT(*) POSTS, SUM(UPVOTES) UPVOTES, SUM(DOWNVOTES) DOWNVOTES FROM 
											(	SELECT COUNT(*) AS UPVOTES, 
												(SELECT COUNT(*) FROM downvotes WHERE (POST_ID=u.POST_ID AND STATE=1)) AS DOWNVOTES FROM posts p
												JOIN upvotes u ON p.ID=u.POST_ID AND u.STATE=1 JOIN topics t ON p.TOPIC_ID=t.ID WHERE t.TAGS RLIKE '".$tagRegex."'
												AND POST_AUTHOR_ID=? GROUP BY POST_ID
											) tmp 
											HAVING (UPVOTES - DOWNVOTES) >= ".$cond." AND POSTS >= ".$cond2; 
							
									$valArr = array($uid);							
									$stmt = $this->DBM->doSecuredQuery($sql, $valArr);						
									$nTimes = $this->DBM->fetchRow($stmt)["POSTS"];
									$nTimes = (int)(floor($nTimes / $cond2));
									$oldNTimes = $this->badgeAwardCount($uid, $badgeName);
									
									if($uid && ($oldNTimes != $nTimes)){
		
										$confiscate = ($oldNTimes > $nTimes);
										$nTimes = $confiscate? ($oldNTimes - $nTimes) : ($nTimes - $oldNTimes);
										$this->awardBadge($uid, $badgeName, $confiscate, $n=$nTimes);
		
									}
									
								}
								
							}
			
							break;
		
						}
					}				
							
				}
			}
			
		}
	}



	
	
	
		
		

	
	
		
	/*** Method for awarding users reputation points earned (cron job) ***/
	public function reputationAwardCron(){
	
		$limit = $this->DBM->getMaxRowPerSelect();
	
		for($start=0; ; $start += $limit){
			
			/////PDO QUERY////////
			$sql = "SELECT ID FROM users LIMIT ".$start.",".$limit;
			$valArr = array();
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
			
			/////IMPORTANT INFINITE LOOP CONTROL ////
			if(!$this->DBM->getSelectCount())
				break;
							
			while($row = $this->DBM->fetchRow($stmt)){
	
				##VERY VITAL LOOP VARIABLE
				$reputationsEarned=0;
				
				$uid = $row["ID"];
				
				##CALCULTE TOTAL EARNED FROM UPVOTES 
				$sql = "SELECT COUNT(*) FROM upvotes u JOIN posts p ON u.POST_ID = p.ID AND u.STATE=1 WHERE (u.UPPER_ID !=? AND p.POST_AUTHOR_ID=?)";
				$valArr = array($uid, $uid);
				$reputationsEarned += ($this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn() * POST_UPVOTED_REP);
				
				##CALCULTE TOTAL EARNED FROM SHARES
				$sql = "SELECT COUNT(*) FROM shares s JOIN posts p ON s.POST_ID = p.ID WHERE (s.SHARER_ID !=? AND s.STATE=1 AND p.POST_AUTHOR_ID=?)";
				$valArr = array($uid, $uid);
				$reputationsEarned += ($this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn() * POST_SHARED_REP);
				
				##CALCULTE TOTAL EARNED FROM POST SOCIAL SHARES
				$sql = "SELECT COUNT(*) FROM post_social_shares s JOIN posts p ON s.POST_ID = p.ID WHERE (s.USER_ID !=? AND p.POST_AUTHOR_ID=?)";
				$valArr = array($uid, $uid);
				$reputationsEarned += ($this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn() * POST_SOCIAL_SHARED_REP);
				
				##CALCULTE TOTAL EARNED FROM TOPIC SOCIAL SHARES
				$sql = "SELECT COUNT(*) FROM topic_social_shares s JOIN topics t ON s.TOPIC_ID = t.ID WHERE (s.USER_ID !=? AND t.TOPIC_AUTHOR_ID=?)";
				$valArr = array($uid, $uid);
				$reputationsEarned += ($this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn() * THREAD_SOCIAL_SHARED_REP);
				
				##CALCULTE TOTAL EARNED FROM TOPIC FOLLOWS
				$sql = "SELECT COUNT(*) FROM topic_follows tf JOIN topics t ON tf.TOPIC_ID = t.ID WHERE (tf.USER_ID !=? AND t.TOPIC_AUTHOR_ID=? AND tf.STATE=1)";
				$valArr = array($uid, $uid);
				$reputationsEarned += ($this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn() * THREAD_GAIN_FOLLOWS_REP);
				
				##CALCULTE TOTAL EARNED FROM MEMBER FOLLOWS
				$sql = "SELECT COUNT(*) FROM members_follows WHERE (STATE=1 AND USER_ID = ?)";
				$valArr = array($uid);
				$reputationsEarned += ($this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn() * MEMBER_GAIN_FOLLOWS_REP);
		
				##CONFISCATE FROM USERS WITH FOUR POST FLAGS 
				$sql = "SELECT COUNT(*) FROM (SELECT COUNT(*) AS NFLAGS FROM reported_posts r JOIN posts p ON r.POST_ID = p.ID WHERE p.POST_AUTHOR_ID=? GROUP BY r.POST_ID HAVING NFLAGS >= ?) tmp";
				$valArr = array($uid, N_POST_FLAG);
				$reputationsEarned -= ($this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn() * FOUR_POST_FLAGS_REP);
				
				##THERE's 1 REPUTATION POINT ADDED TO ALL
				$reputationsEarned += 1;
				
				##UPDATE THE USER
				$this->awardReputation($uid, $reputationsEarned);
				
			}
			
		}
		
				
	}


	
	
	
	
	
	
	
	
	
	
	

}
	

?>