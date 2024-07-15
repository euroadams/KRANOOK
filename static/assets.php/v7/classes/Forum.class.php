<?php


class Forum{

	use Category, Section, Thread, Post;
	
	/*** Generic member variables ***/
	private $DBM;
	private $ACCOUNT;
	private $SESS;
	private $ENGINE;
	private $SITE;
	private $INT_HASHER;
	
	/*** Constructor ***/
	public function __construct(){
		
		global $dbm, $ACCOUNT, $ENGINE, $SITE, $INT_HASHER;
		
		$this->DBM = $dbm;
		$this->ACCOUNT = $ACCOUNT;
		$this->SESS = $ACCOUNT->SESS;
		$this->ENGINE = $ENGINE;
		$this->SITE = $SITE;
		$this->INT_HASHER = $INT_HASHER;
		
	}
	
	/*** Destructor ***/
	public function __destruct(){
		
		
	}

	




	 


	
		
	/*** Method for handling votes ***/
	public function votesHandler($metaArr){
		
		$subQry='';$res=0;			
		$upvoteTable = 'upvotes';
		$downvoteTable = 'downvotes'; 
				
		$uid = $this->ENGINE->get_assoc_arr($metaArr, 'uid');
		$pid = $this->ENGINE->get_assoc_arr($metaArr, 'pid');
		$countSessNewUpvotes = $this->ENGINE->get_assoc_arr($metaArr, 'countSessNewUpvotes');
		$action = strtolower($this->ENGINE->get_assoc_arr($metaArr, 'action'));
		$voter = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'voter');
		$countDistinct = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'countDistinct');
		$type = strtolower($this->ENGINE->get_assoc_arr($metaArr, 'type'));
		$table = ($type == 'u')? $upvoteTable : (($type == 'd')? $downvoteTable : '');
		$isUp = ($type == 'u');
		/// is uid the voter or author
		$condCol = $voter? (($type == 'u')?  ' UPPER_ID ' : (($type == 'd')? ' DOWNER_ID ' : '')) : ' p.POST_AUTHOR_ID ';
		$countCol = $countDistinct? 'COUNT(DISTINCT POST_ID)' : 'COUNT(*)';
		$U = $this->ACCOUNT->loadUser($uid);
		$uid = $U->getUserId();	
		
		$valArr=array();
		
		if($pid){
			
			$subQry = ' POST_ID = ? ';
			$valArr[] = $pid;	
			
		}	
		
		if($uid){
		
			$subQry .= ' '.($subQry? ' AND ' : '').$condCol.' = ? ';
			$valArr[] = $uid;
				
		}
		
		if($countSessNewUpvotes){
				
			$subQry .= ' '.($subQry? ' AND ' : '').' v.TIME > ?';
			$valArr[] = $U->getDownvotedPostsLastViewTime();
		
		}
		
		////PDO QUERY/////
		if($action){
					
			$sql = "SELECT STATE FROM ".$table." WHERE ".$subQry." LIMIT 1";
			$checkStatus = $this->DBM->doSecuredQuery($sql, $valArr, true)->fetchColumn();
			$entryCheckStatus = $this->DBM->getRecordCount();
		
			switch($action){
		
				case 'cast':
					if(!$entryCheckStatus){
		
						///////////PDO QUERY//////
						$sql = "INSERT INTO ".$table." (POST_ID, ".($isUp? "UPPER_ID" : "DOWNER_ID" ).", STATE) VALUES(?,?,1)";
						$valArr = array($pid, $uid);
						$stmt = $this->DBM->doSecuredQuery($sql, $valArr);	
									
					}else{
		
						///////////PDO QUERY//////////////
						$sql = "UPDATE ".$table." SET STATE=1 WHERE (POST_ID=? AND ".($isUp? "UPPER_ID" : "DOWNER_ID" )."=?) LIMIT 1";
						$valArr = array($pid, $uid);
						$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		
					}
		
					break;
		
				case 'revoke':
					///////////PDO QUERY////////////
					$sql = "UPDATE ".$table." SET STATE=0 WHERE (POST_ID=? AND ".($isUp? "UPPER_ID" : "DOWNER_ID" )."=?) LIMIT 1";
					$valArr = array($pid, $uid);
					$stmt = $this->DBM->doSecuredQuery($sql, $valArr); 
					break;
		
				default:
					return (($action == 'entrycheck')? $entryCheckStatus : $checkStatus);
		
			}		
			
		}elseif($subQry){
		
			//COUNT	
			$sql = "SELECT ".$countCol." FROM ".$table." v JOIN posts p ON p.ID=v.POST_ID AND v.STATE=1 WHERE ".$subQry;
			$res = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
		}
		
		return $res;
		
	}




	

	
		
	/*** Method for handling shares ***/
	public function sharesHandler($metaArr){
		
		$subQry='';$res=0;	 
				
		$uid = $this->ENGINE->get_assoc_arr($metaArr, 'uid');
		$pid = $this->ENGINE->get_assoc_arr($metaArr, 'pid');
		$countSessNewShares = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'countSessNewShares');
		$action = strtolower($this->ENGINE->get_assoc_arr($metaArr, 'action'));
		$postAuthorId = $this->ENGINE->get_assoc_arr($metaArr, 'postAuthorId');
		$sharer = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'sharer');
		$table = 'shares';
		$countDistinct = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'countDistinct');
		/// is uid the sharer or author	
		$condCol = $sharer? ' SHARER_ID ' : ' p.POST_AUTHOR_ID ';
		$countCol = $countDistinct? 'COUNT(DISTINCT POST_ID)' : 'COUNT(*)';
		$U = $this->ACCOUNT->loadUser($uid);
		$uid = $U->getUserId();	
		
		$valArr=array();
		
		if($pid){
		
			$subQry = ' POST_ID = ? ';
			$valArr[] = $pid;
				
		}	
		
		if($uid){
		
			$subQry .= ' '.($subQry? ' AND ' : '').$condCol.' = ? ';
			$valArr[] = $uid;
				
		}
		
		if($countSessNewShares){
				
			$subQry .= ' '.($subQry? ' AND ' : '').' s.TIME > ? AND SHARER_ID != ?';
			$valArr[] = $U->getSharedPostsLastViewTime();
			$valArr[] = $uid;
		
		}
		
		if($action){	
			
			///////////PDO QUERY//////			
			$sql = "SELECT STATE FROM ".$table." WHERE (".$subQry.") LIMIT 1";
			$checkStatus = $this->DBM->doSecuredQuery($sql, $valArr, true)->fetchColumn();
			$entryCheckStatus = $this->DBM->getRecordCount();
		
			switch($action){
		
				case 'share':
					if(!$entryCheckStatus){
		
						///////////PDO QUERY//////
						$sql = "INSERT INTO ".$table." (POST_ID, SHARER_ID, STATE) VALUES(?,?,1)";
						$valArr = array($pid, $uid);
						$stmt = $this->DBM->doSecuredQuery($sql, $valArr);	
									
					}else{
		
						///////////PDO QUERY//////////////
						$sql = "UPDATE ".$table." SET STATE=1 WHERE POST_ID=? AND SHARER_ID=? LIMIT 1";
						$valArr = array($pid, $uid);
						$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		
					}
					break;
		
				case 'unshare':
					///////////PDO QUERY////////////
					$sql = "UPDATE ".$table." SET STATE=0 WHERE POST_ID=? AND SHARER_ID=? LIMIT 1";
					$valArr = array($pid, $uid);
					$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
					break;
		
				default:
					return (($action == 'entrycheck')? $entryCheckStatus : $checkStatus);
		
			}
		
					
		}elseif($subQry){
		
			//COUNT	
			///////////PDO QUERY//////			
			$sql = "SELECT ".$countCol." FROM ".$table." s JOIN posts p ON p.ID=s.POST_ID AND s.STATE=1 WHERE (".$subQry.")";
			$res = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
		}
		
		return $res;
		
	}



	

	
		

	
		
	/*** Method for handling avatar likes ***/
	public function avatarLikesHandler($metaArr){	

		$acc='';
		
		$action = strtolower($this->ENGINE->get_assoc_arr($metaArr, 'action'));
		$uid = $this->ENGINE->get_assoc_arr($metaArr, 'uid');
		$liker = $this->ENGINE->get_assoc_arr($metaArr, 'liker');
		$count = $this->ENGINE->get_assoc_arr($metaArr, 'count');	
		$countNewSessLikes = $this->ENGINE->get_assoc_arr($metaArr, 'countNewSessLikes');		
		$sep = $this->ENGINE->get_assoc_arr($metaArr, 'sep');	
		$check = $this->ENGINE->get_assoc_arr($metaArr, 'check');
		$entryCheck = $this->ENGINE->get_assoc_arr($metaArr, 'entryCheck');
		$i = $this->ENGINE->get_assoc_arr($metaArr, 'i');
		$i = $i? $i : 0;
		$n = $this->ENGINE->get_assoc_arr($metaArr, 'n');
		$n = $n? $n : 20;
		$U = $this->ACCOUNT->loadUser($uid);
		$uid = $U->getUserId();
		$table = 'avatar_likes';

		if($uid){

			$valArr = array($uid);

			///////////PDO QUERY//////
			if($check || $entryCheck || $action){	

				$valArr[] = $liker;
				$sql = "SELECT STATE FROM ".$table." WHERE (USER_ID = ? AND LIKER_ID = ?) LIMIT 1";
				$checkStatus = $this->DBM->doSecuredQuery($sql, $valArr, true)->fetchColumn();
				$entryCheckStatus = $this->DBM->getRecordCount();
				
				switch($action){

					case 'like':
						if(!$entryCheckStatus){

							///////////PDO QUERY//////
							$sql = "INSERT INTO ".$table." (USER_ID, LIKER_ID, STATE) VALUES(?,?,1)";
							$valArr = array($uid, $liker);
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
								
						}else{

							///////////PDO QUERY//////////////
							$sql = "UPDATE ".$table." SET STATE=1 WHERE USER_ID=? AND LIKER_ID=? LIMIT 1";
							$valArr = array($uid, $liker);
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr);

						}
						break;

					case 'unlike':
						///////////PDO QUERY////////////
						$sql = "UPDATE ".$table." SET STATE=0 WHERE USER_ID=? AND LIKER_ID=? LIMIT 1";
						$valArr = array($uid, $liker);
						$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
						break;

					default:
						return ($entryCheck? $entryCheckStatus : $checkStatus);

				}

					
			}elseif($count || $countNewSessLikes){	
					
				$newSubQry='';

				if($countNewSessLikes){

					$valArr[] = $U->getAvatarLikesLastViewTime();						
					$newSubQry = ' AND TIME > ?';

				}
				
				$sql = "SELECT COUNT(*) FROM ".$table." WHERE (USER_ID = ? AND STATE=1".$newSubQry.")";
				return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
				
			}
		}	
	}





	
		

	
		
	/*** Method for handling followed topics ***/
	public function followedTopicsHandler($metaArr){
		
		$acc='';
				
		$uid = $this->ENGINE->get_assoc_arr($metaArr, 'uid');
		$tid = $this->ENGINE->get_assoc_arr($metaArr, 'tid');
		$count = $this->ENGINE->get_assoc_arr($metaArr, 'count');
		$countPost = $this->ENGINE->get_assoc_arr($metaArr, 'countPost');
		$countNewNonSessPost = $this->ENGINE->get_assoc_arr($metaArr, 'countNewNonSessPost');
		$getTopic = $this->ENGINE->get_assoc_arr($metaArr, 'getTopic');
		$lspn = $this->ENGINE->get_assoc_arr($metaArr, 'lspn');
		$action = strtolower($this->ENGINE->get_assoc_arr($metaArr, 'action'));
		$sep = $this->ENGINE->get_assoc_arr($metaArr, 'sep');
		$perUI = $this->ENGINE->get_assoc_arr($metaArr, 'perUI');
		$check = $this->ENGINE->get_assoc_arr($metaArr, 'check');
		$entryCheck = $this->ENGINE->get_assoc_arr($metaArr, 'entryCheck');
		$i = $this->ENGINE->get_assoc_arr($metaArr, 'i');
		$i = $i? $i : 0;
		$n = $this->ENGINE->get_assoc_arr($metaArr, 'n');
		$n = $n? $n : 20;
		$U = $this->ACCOUNT->loadUser($uid);
		$uid = $U->getUserId();	
		$username = $U->getUsername();
		$table = 'topic_follows';
		
		$ftSubQry = "SELECT TOPIC_ID FROM ".$table." WHERE (USER_ID=? AND STATE=1)";	
		
		if($uid){
			
			$valArr = array($uid);
			
					
			///////////PDO QUERY//////

			$mainSQL = $this->SITE->composeQuery(array('type' => 'for_topic', 'start' => $i, 'stop' => $n, 'uniqueColumns' => '', 'filterCnd' => 'topics.ID IN ('.$ftSubQry.')', 'orderBy' => 'LAST_POST_TIME DESC'));
			
			if($check || $entryCheck || in_array($action, array('follow', 'unfollow'))){
				
				$tid? ($valArr[] = $tid) : '';
				$subQry = $tid? " AND TOPIC_ID=? " : "";
				$queries_status = true;						
				$sql = "SELECT STATE FROM ".$table." WHERE (USER_ID = ? ".$subQry.") LIMIT 1";
				$checkStatus = $this->DBM->doSecuredQuery($sql, $valArr, true)->fetchColumn();
				$entryCheckStatus = $this->DBM->getRecordCount();
				
				switch($action){
					
					case 'follow':
						if(!$entryCheckStatus){
							
							///////////PDO QUERY//////
							$sql = "INSERT INTO ".$table." (USER_ID,TOPIC_ID, STATE) VALUES(?,?,1)";
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr);	
							
						}else{
							
							///////////PDO QUERY//////////////
							$sql = "UPDATE ".$table." SET STATE=1 WHERE USER_ID=? AND TOPIC_ID=? LIMIT 1";
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
							
						}
						
						break;
						
					case 'unfollow':			
						/**********CLEANUP ALL COOKIES THE USER HAS FOR THE FOLLOWED TOPIC*******/					
						if($checkStatus || !$tid){
							
							$sql =  "DELETE FROM users_metas WHERE (USER_ID=? ".($tid? "AND META_KEY=?" : "").")";
							$valArr = $tid? array($uid, $tid."_lspn") : $valArr;
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr);	
							
							$sql = "UPDATE ".$table." SET STATE=0 WHERE (USER_ID=? AND STATE=1".$subQry.")";
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
							
						}
						
						return $queries_status; break;
						
					default:
						return ($entryCheck? $entryCheckStatus : $checkStatus);
						
				
				}
				
				
			}elseif($getTopic){	
			
				list($lt) = $this->loadThreads($mainSQL, $valArr, $type="followed topics");
				return $lt;
				
			}elseif($count){
				
				$sql = "SELECT COUNT(*) FROM ".$table." WHERE (USER_ID = ? AND STATE=1)";
				$tot = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();;
				return $tot;
				
			}elseif($countPost || $countNewNonSessPost){
				
				$newSubQry='';
				
				if($countNewNonSessPost){
					
					$valArr[] = $uid;
					$valArr[] = $U->getFollowedTopicsLastViewTime();				
					$newSubQry = ' AND TIME > ?';
					
				}
				
				////COUNT POSTS FROM FOLLOWED TOPICS SINCE LAST ACCESS TIME/////						
				$sql =  "SELECT COUNT(*) FROM posts WHERE (".($countNewNonSessPost? "POST_AUTHOR_ID NOT LIKE ? AND " : "")." TOPIC_ID IN(".$ftSubQry.") ".$newSubQry.")";
				$tot = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
				return $tot;
				
				
			}elseif(in_array($action, array('loglspn', $getLSPN='getlspn'))){
				
				$metaKey = $tid.'_lspn';
				$sql =  "SELECT META_VALUE FROM users_metas WHERE (USER_ID=? AND META_KEY=?) LIMIT 1";
				$valArr = array($uid, $tid."_lspn");
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
				
				if($action == $getLSPN)
					return $stmt->fetchColumn();
				
				elseif($this->DBM->getRecordCount()){
					
					$sql =  "UPDATE users_metas SET META_VALUE=? WHERE (USER_ID=? AND META_KEY=?) LIMIT 1";
					$valArr = array($lspn, $uid, $metaKey);
					$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
					
				}else{
					
					$sql =  "INSERT INTO users_metas (USER_ID,META_KEY,META_VALUE) VALUES(?,?,?)";
					$valArr = array($uid, $metaKey, $lspn);
					$stmt = $this->DBM->doSecuredQuery($sql, $valArr);

				}
				
			}else{	
			
				if($perUI){
					
					$sql = $this->SITE->composeQuery(array('type' => 'for_topic', 'start' => $i, 'stop' => $n, 'uniqueColumns' => '', 'filterCnd' => 'topics.ID IN('.$ftSubQry.')', 'orderBy' => 'TIME DESC'));
					list($acc) = $this->loadThreads($sql, $valArr);
					
				}else{
					
					$counter = 0;
					$sql = "SELECT TOPIC_ID AS FT FROM ".$table." WHERE (USER_ID=? AND STATE=1) ORDER BY TIME DESC LIMIT ".$i.",".$n;
					$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
					
					while($row = $this->DBM->fetchRow($stmt)){
						
						$ft = $row["FT"];			
						$acc .= $ft.',';
						$counter++;
						
					}	
					
					$more = ($counter > 10)? $this->SITE->getExtendedViewLink('user-events/'.$username.'/followed-topics') : '';
					$acc = $this->SITE->idCsvToNameString($acc, $t='tid', $nSep=$sep).$more;
					
				}
			}
		}
		
		return $acc;
		
	}




	
	
	
		

	
		
	/*** Method for handling followed sections ***/
	public function followedSectionsHandler($metaArr){	
		
		$acc='';		
		
		$action = strtolower($this->ENGINE->get_assoc_arr($metaArr, 'action'));	
		$uid = $this->ENGINE->get_assoc_arr($metaArr, 'uid');	
		$count = $this->ENGINE->get_assoc_arr($metaArr, 'count');
		$countPost = $this->ENGINE->get_assoc_arr($metaArr, 'countPost');
		$countNewNonSessPost = $this->ENGINE->get_assoc_arr($metaArr, 'countNewNonSessPost');
		$countTopic = $this->ENGINE->get_assoc_arr($metaArr, 'countTopic');
		$getTopic = $this->ENGINE->get_assoc_arr($metaArr, 'getTopic');
		$getPost = $this->ENGINE->get_assoc_arr($metaArr, 'getPost');
		$sep = $this->ENGINE->get_assoc_arr($metaArr, 'sep');
		$perUI = $this->ENGINE->get_assoc_arr($metaArr, 'perUI');
		$sid = $this->ENGINE->sanitize_number($this->ENGINE->get_assoc_arr($metaArr, 'sid'));
		$check = $this->ENGINE->get_assoc_arr($metaArr, 'check');
		$entryCheck = $this->ENGINE->get_assoc_arr($metaArr, 'entryCheck');
		$i = $this->ENGINE->get_assoc_arr($metaArr, 'i');
		$i = $i? $i : 0;
		$n = $this->ENGINE->get_assoc_arr($metaArr, 'n');
		$n = $n? $n : 20;
		$U = $this->ACCOUNT->loadUser($uid);
		$uid = $U->getUserId();	
		$username = $U->getUsername();		
		$table = 'section_follows';	
			
		if($uid){
		
			$valArr = array($uid);
			
			///////////PDO QUERY//////
			$fsidSubQry = "SELECT SECTION_ID FROM ".$table." WHERE (USER_ID = ? AND STATE=1)";
			
			if($check || $entryCheck || $action){
				
				$sid? ($valArr[] = $sid) : '';
				$subQry = $sid? " AND SECTION_ID=? " : "";
				$sql = "SELECT STATE FROM ".$table." WHERE (USER_ID = ? ".$subQry.") LIMIT 1";
				$checkStatus = $this->DBM->doSecuredQuery($sql, $valArr, true)->fetchColumn();
				$entryCheckStatus = $this->DBM->getRecordCount();
				
				switch($action){
					
					case 'follow':
						$xceptArr = getExceptionParams('sidsnofollow');
						
						if(!$sid){
								
							////PDO QUERY/////////
							
							$sql = "SELECT ID FROM sections WHERE ID NOT IN(".implode(',', $xceptArr).") ORDER BY SECTION_NAME ";
							$stmt = $this->DBM->query($sql);
							
							while($row = $this->DBM->fetchRow($stmt)){				
								
								$sid = $row["ID"];
								
								if($this->followedSectionsHandler(array('uid'=>$uid, 'sid'=>$sid, 'check'=>true)) 
									|| !$this->ACCOUNT->sessionAccess(array('id'=>$sid, 'isSid'=>true, 'cond'=>'staffsOnly')) 
									)
									continue;
									
								if($this->followedSectionsHandler(array('uid'=>$uid, 'sid'=>$sid, 'entryCheck'=>true))){
									
									///////////PDO QUERY//////////////
									$sql = "UPDATE ".$table." SET STATE=1 WHERE (USER_ID=? AND SECTION_ID=?) LIMIT 1";
									$valArr = array($uid, $sid);
									$this->DBM->doSecuredQuery($sql, $valArr);
									
								}else{
									
									///////////PDO QUERY//////
									$sql = "INSERT INTO ".$table." (USER_ID,SECTION_ID,STATE) VALUES(?,?,1)";
									$valArr = array($uid, $sid);
									$this->DBM->doSecuredQuery($sql, $valArr);
									
								}										
								
							}									
							
						}else{
							
							////////VERIFY EXISTENCE////////
							$found = $this->SITE->sectionIdToggle($sid);	
							
							if($this->followedSectionsHandler(array('uid'=>$uid, 'sid'=>$sid, 'entryCheck'=>true))){
								
								///////////PDO QUERY//////////////
								$sql = "UPDATE ".$table." SET STATE=1 WHERE USER_ID=? AND SECTION_ID=? LIMIT 1";
								$this->DBM->doSecuredQuery($sql, $valArr);
								
							}elseif($found && $this->ACCOUNT->sessionAccess(array('id'=>$sid, 'isSid'=>true, 'cond'=>'staffsOnly')) 
								&& !in_array($sid, $xceptArr)){
									
								///////////PDO QUERY//////
								$sql = "INSERT INTO ".$table." (USER_ID,SECTION_ID,STATE) VALUES(?,?,1)";
								$this->DBM->doSecuredQuery($sql, $valArr);
								
							}
							
						}
						
						break;
						
					case 'unfollow':								
						if($checkStatus || !$sid){
							
							///UNFOLLOW ALL/SPECIFIC SECTION YOU ARE FOLLOWING ////
							///////////PDO QUERY///////////////	
							$sql = "UPDATE ".$table." SET STATE=0 WHERE (USER_ID=? ".$subQry.")";
							$this->DBM->doSecuredQuery($sql, $valArr);
							
						}
						
						break;
						
					default:
						return ($entryCheck? $entryCheckStatus : $checkStatus);
						
				
				}
			
			}elseif($getTopic){	
			
				$sql = $this->SITE->composeQuery(array('type' => 'for_topic', 'start' => $i, 'stop' => $n, 'uniqueColumns' => '', 'filterCnd' => 'SECTION_ID IN ('.$fsidSubQry.')', 'orderBy' => 'TIME DESC'));
				list($topics) = $this->loadThreads($sql, $valArr, $type="");	
				return $topics;
				
			}elseif($getPost){	
			
				$sql = $this->SITE->composeQuery(array('type' => 'for_post', 'start' => $i, 'stop' => $n, 'uniqueColumns' => '', 'filterCnd' => 'SECTION_ID IN ('.$fsidSubQry.')', 'orderBy' => 'TIME DESC'));
				list($messages) = $this->loadPosts($sql, $valArr);		
				return $messages;
				
			}elseif($count){	
				
				$sql = "SELECT COUNT(*) FROM ".$table." WHERE (USER_ID = ? AND STATE=1)";
				return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
				
			}elseif($countTopic){
				
				$sql = "SELECT COUNT(*) FROM topics WHERE SECTION_ID IN (".$fsidSubQry.") ";
				return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
				
			}elseif($countPost || $countNewNonSessPost){
				
				$newSubQry='';
				
				if($countNewNonSessPost){
					
					$valArr[] = $uid;
					$valArr[] = $U->getFollowedSectionsLastViewTime();				 
					$newSubQry = ' AND posts.TIME > ?';
					
				}
				
				$sql = "SELECT COUNT(*) FROM posts INNER JOIN topics ON  posts.TOPIC_ID = topics.ID  
						WHERE (".($countNewNonSessPost? "POST_AUTHOR_ID NOT LIKE ? AND " : "")." SECTION_ID IN (".$fsidSubQry.") ".$newSubQry.")";
				return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
				
			}else{	
				
				$counter = 0;
				$sql = "SELECT SECTION_ID AS FS FROM ".$table." WHERE (USER_ID=? AND STATE=1) ORDER BY TIME DESC LIMIT ".$i.",".$n;
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
				
				while($row = $this->DBM->fetchRow($stmt)){
					
					$fs = $row["FS"];			
					$acc .= $perUI? $this->loadSections($fs) : $fs.',';
					$counter++;
					
				}
				
				if(!$perUI){
					
					$more = ($counter > 10)? $this->SITE->getExtendedViewLink('user-events/'.$username.'/followed-sections') : '';
					$acc = $this->SITE->idCsvToNameString($acc, $t='sid', $nSep=$sep).$more;
					
				}
			}
		}
		
		return $acc;
		
	}



	
	
	
	
		

	
		
	/*** Method for handling followed members ***/
	public function followedMembersHandler($metaArr){
				
		$action = strtolower($this->ENGINE->get_assoc_arr($metaArr, 'action'));
		$uid = $this->ENGINE->get_assoc_arr($metaArr, 'uid');
		$follower = $this->ENGINE->get_assoc_arr($metaArr, 'follower');	
		$getUserOnly = $this->ENGINE->get_assoc_arr($metaArr, 'getUserOnly');	
		$getPost = $this->ENGINE->get_assoc_arr($metaArr, 'getPost');	
		$getTopic = $this->ENGINE->get_assoc_arr($metaArr, 'getTopic');	
		$count = $this->ENGINE->get_assoc_arr($metaArr, 'count');	
		$countPost = $this->ENGINE->get_assoc_arr($metaArr, 'countPost');	
		$countTopic = $this->ENGINE->get_assoc_arr($metaArr, 'countTopic');	
		$countNewNonSessPost = $this->ENGINE->get_assoc_arr($metaArr, 'countNewNonSessPost');	
		$getFF = $this->ENGINE->get_assoc_arr($metaArr, 'getFF');	
		$commaSep = $this->ENGINE->get_assoc_arr($metaArr, 'commaSep');	
		$vcard = $this->ENGINE->get_assoc_arr($metaArr, 'vcard');
		$vcardMin = $this->ENGINE->get_assoc_arr($metaArr, 'vcardMin');
		$check = $this->ENGINE->get_assoc_arr($metaArr, 'check');
		$entryCheck = $this->ENGINE->get_assoc_arr($metaArr, 'entryCheck');
		$i = $this->ENGINE->get_assoc_arr($metaArr, 'i');
		$i = $i? $i : 0;
		$n = $this->ENGINE->get_assoc_arr($metaArr, 'n');
		$n = $n? $n : 20;
		$U = $this->ACCOUNT->loadUser($uid);
		$uid = $U->getUserId();
		$username = $U->getUsername();			
		$table = 'members_follows';			
		
		$valArr = array($uid);		
		
		if($uid){		
		
			///////////PDO QUERY//////
			$fmidSubQry = "SELECT USER_ID FROM ".$table." WHERE (FOLLOWER_ID=? AND STATE=1)";		
		
			if($check || $entryCheck || $action){		
		
				$valArr[] = $follower;
				$sql = "SELECT STATE FROM ".$table." WHERE USER_ID = ? AND FOLLOWER_ID = ? LIMIT 1";
				$checkStatus = $this->DBM->doSecuredQuery($sql, $valArr, true)->fetchColumn();
				$entryCheckStatus = $this->DBM->getRecordCount();
				
				switch($action){		
		
					case 'follow':
						if(!$entryCheckStatus){		
		
							///////////PDO QUERY//////
							$sql = "INSERT INTO ".$table." (USER_ID,FOLLOWER_ID,STATE) VALUES(?,?,1)";
							$this->DBM->doSecuredQuery($sql, $valArr);			
										
						}else{		
		
							///////////PDO QUERY//////////////
							$sql = "UPDATE ".$table." SET STATE=1 WHERE USER_ID=? AND FOLLOWER_ID=? LIMIT 1";
							$this->DBM->doSecuredQuery($sql, $valArr);		
		
						}		
		
						break;		
		
					case 'unfollow':
						///////////PDO QUERY////////////
						$sql = "UPDATE ".$table." SET STATE=0 WHERE USER_ID=? AND FOLLOWER_ID=? LIMIT 1";
						$this->DBM->doSecuredQuery($sql, $valArr);
						break;		
		
					default:
						return ($entryCheck? $entryCheckStatus : $checkStatus);		
		
				}
				
			}elseif($getTopic){	

				$sql = $this->SITE->composeQuery(array('type' => 'for_topic', 'start' => $i, 'stop' => $n, 'uniqueColumns' => '', 'filterCnd' => 'TOPIC_AUTHOR_ID IN ('.$fmidSubQry.')', 'orderBy' => 'TIME DESC'));
				list($topics) = $this->loadThreads($sql, $valArr, $type="");	
				return $topics;
				
			}elseif($getPost){	
			
				$sql = $this->SITE->composeQuery(array('type' => 'for_post', 'start' => $i, 'stop' => $n, 'uniqueColumns' => '', 'filterCnd' => 'POST_AUTHOR_ID IN ('.$fmidSubQry.')', 'orderBy' => 'TIME DESC'));
				list($messages) = $this->loadPosts($sql, $valArr);		
				return $messages;
				
			}elseif($count){			
			
				$subQry = $follower? ' FOLLOWER_ID = ?' : '  USER_ID = ? ';			
				$sql = "SELECT COUNT(*) FROM ".$table." WHERE (STATE=1 AND ".$subQry.")";
				return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
				
			}elseif($countTopic){				
																
				$sql = "SELECT COUNT(*) FROM topics WHERE TOPIC_AUTHOR_ID IN(".$fmidSubQry.")";
				return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
				
			}elseif($countPost || $countNewNonSessPost){		
		
				$newSubQry='';		
		
				if($countNewNonSessPost){			
				
					$valArr[] = $U->getFollowedMembersLastViewTime();			 
					$newSubQry = ' AND TIME > ?';		
		
				}			
			
				$sql =  "SELECT COUNT(*) AS T FROM posts WHERE POST_AUTHOR_ID IN(".$fmidSubQry.")".$newSubQry;
				return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
				
			}elseif($getUserOnly){		
		
				$acc="";
				$col = $follower? ' USER_ID AS F ' : ' FOLLOWER_ID AS F ';
				$cnd = $follower? ' FOLLOWER_ID = ? ' : ' USER_ID = ? ';
				
				///////////PDO QUERY//////
				$sql = "SELECT ".$col.", TIME FROM ".$table." WHERE (STATE=1 AND ".$cnd.") ORDER BY TIME DESC LIMIT ".$i.",".$n;
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);		
		
				while($row = $this->DBM->fetchRow($stmt)){		
		
					$user = $row["F"];
					$time = $row["TIME"];
					
					if($vcardMin)
						$acc .= $this->ACCOUNT->getUserVCard($user, array('minVer'=>true));
						
					else
						$acc .= $vcard? $this->ACCOUNT->getUserVCard($user, array('time'=>$time)) : $user.',';							
		
				}		
		
				if($commaSep)
					$acc = $this->SITE->idCsvToNameString($acc, $t='uid', $nSep='<b class="">, </b>', array("gnd"=>true));		
		
				return $acc;		
		
				
			}elseif($getFF){		
		
				$nMore = 10;
				$total_ufollow = $this->followedMembersHandler(array('uid'=>$uid, 'follower'=>true, 'count'=>true));
				$more = ($total_ufollow > $nMore)? $this->SITE->getExtendedViewLink('user-events/'.$username.'/following') : '';
				$all_ufollow = $this->followedMembersHandler(array('uid'=>$uid, 'follower'=>true, 'getUserOnly'=>true, ($vcardMin? 'vcardMin' : 'commaSep')=>true));
				$total_ufollow? ($total_ufollow = '(<span class="cyan fm-count-disp">'.$total_ufollow.'</span>)') : '';
				$all_followers = $this->followedMembersHandler(array('uid'=>$uid, 'getUserOnly'=>true, ($vcardMin? 'vcardMin' : 'commaSep')=>true));
				$total_followers = $this->followedMembersHandler(array('uid'=>$uid, 'count'=>true));
				!$total_followers? ($total_followers = '') : '';
				$more2 = ($total_followers > $nMore)? '<span class="track-fm-append">'.$this->SITE->getExtendedViewLink('user-events/'.$username.'/followers').'</span>' : '';
				$total_followers? ($total_followers = '(<span class="cyan fm-count-disp">'.$total_followers.'</span>)') : '';
				
				return array("followers" => $all_followers.$more2, "following" => $all_ufollow.$more, "nFollowers" => $total_followers,
								"nFollowing" => $total_ufollow);		
		
								
			}
		}
	}

	
	
	
	
	
		

	
		
	/*** Method for authorizing moderation access level ***/
	public function authorizeModeration($level='', $tid="", $pid=""){
		
		/************************
		MOD_LEVEL	REPUTATION RQD.			DESCRIPTION
		1 				15						Flag posts
		2				500						Tag your threads hot
		3				1,000					Post in protected threads	
		4				3,000					Rename your threads
		5				5,000					Move your threads	
		6				7,000					Lock/Unlock & Pin/Unpin your post	
		7				8,000					Hide/Show your post
		8				10,000					Close/Open your threads
		9				15,000					Protect your threads
		10				20,000					Moderator(Two Section privileges and Access to mod tools )
		11				30,000					Super Moderator(One Category privileges)
		12				50,000					Ultimate User(Trusted global user with delete privileges)
		13				15						Vote Up
		14				130						Vote Down
		15				18,000					Lock/Unlock & Pin/Unpin your threads
		
		***********************/
		
		$isTopicAuthor=$isPostAuthor=false;
		
		$sessUid = $this->SESS->getUserId();
		
		if($tid){
			
			$sql = "SELECT TOPIC_AUTHOR_ID FROM topics WHERE ID=? LIMIT 1";
			$valArr = array($tid);
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			$row = $this->DBM->fetchRow();
			$isTopicAuthor = ($row["TOPIC_AUTHOR_ID"] == $sessUid);				
		
		}
		
		if($pid){
		
			$sql = "SELECT POST_AUTHOR_ID FROM posts WHERE ID=? LIMIT 1";
			$valArr = array($pid);
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			$row = $this->DBM->fetchRow();
			$isPostAuthor = ($row["POST_AUTHOR_ID"] == $sessUid);
		
		}												
									
		
		$reputation = $this->SESS->getReputation();	
		$ultimateLevel = $this->SESS->getUltimateLevel();
		$owner = ($isTopicAuthor || $isPostAuthor);
		$modRight = $this->ACCOUNT->sessionAccess(array('id'=>$tid));
		$admin = $this->SESS->isAdmin();
		$mod = $this->SESS->isModerator();
		$topStaff = ($admin || $ultimateLevel);
		$staff = ($topStaff || $modRight);
		$fpMod = $this->ACCOUNT->sessionAccess(array('id'=>HOMEPAGE_SID, 'isSid'=>true));		
		
		$authorizationArr = array(
			
			//Post in protected threads	
			'postInProtected' => ($canPostInProtected = ($reputation >= POST_IN_PROTECTED_REP || $staff)),
			
			//Rename threads	
			'renameThread' => ($canRenameThread = (($reputation >= RENAME_THREAD_REP && $owner) || $staff)),
			
			//Move threads	
			'moveThread' => ($canMoveThread = (($reputation >= MOVE_THREAD_REP && $owner) || $staff)),
			
			//Close/Open a thread	
			'closeThread' => ($canCloseThread = (($reputation >= CLOSE_THREAD_REP && $owner) || $staff)),
			
			//Protect a thread/topic	
			'protectThread' => ($canProtectThread = (($reputation >= PROTECT_THREAD_REP && $owner) || $staff)),
			
			//Lock Thread	
			'lockThread' => ($canLockThread = (($reputation >= LOCK_THREAD_REP && $owner) || $staff)),
			
			//Pin Thread on front page	
			'pinThread' => ($canPinThread = (($reputation >= PIN_THREAD_REP) || $ultimateLevel)),
			
			//Tag Hot
			'tagHot' => ($canTagHot = (($reputation >= TAG_HOT_REP && $owner) || $staff)),
			
			//Feature thread on front page	
			'featThread' => ($canFeatThread = ($reputation >= FEAT_THREAD_REP || $fpMod || $ultimateLevel)),
			
			//Delete Thread
			'deleteThread' => ($canDeleteThread = (($reputation >= DELETE_THREAD_REP) || $ultimateLevel)),
			
			 //Flag posts
			'flagPost' => ($canFlagPost = ($reputation >= FLAG_POST_REP || $staff)),			
			
			//Lock/Unlock a post	
			'lockPost' => ($canLockPost = (($reputation >= LOCK_POST_REP && $owner) || $staff)),
			
			//Pin Post in thread	
			'pinPost' => ($canPinPost = ($reputation >= PIN_POST_REP || $staff)),
			
			//Hide/Show a post	
			'hidePost' => ($canHidePost = (($reputation >= HIDE_POST_REP && $owner) || $staff)),
			
			//Delete post	
			'deletePost' => ($canDeletePost = (($reputation >= DELETE_POST_REP && $owner) || $ultimateLevel)),
						
			//Moderator	
			'mod' => ($canBecomeMod = ($reputation >= MOD_REP || $staff)),
			
			//Super Moderator	
			'superMod' => ($canBecomeSuperMod = ($reputation >= SUPER_MOD_REP || $staff)),
			
			////Trusted/Ultimate Moderator	
			'ultimateMod' => ($canBecomeUltimateMod = $ultimateLevel),
			
			////Vote up	
			'voteUp' => ($canUpvote = ($reputation >= VOTE_UP_REP || $staff)),
			
			////Vote down	
			'voteDown' => ($canDownvote = ($reputation >= VOTE_DOWN_REP || $staff)),
			
			
		);
		
		switch($level){
			
			case POST_IN_PROTECTED: $authorized = $canPostInProtected; break;
		 
			case RENAME_THREAD: $authorized = $canRenameThread; break; 
		
			case MOVE_THREAD: $authorized = $canMoveThread; break; 
		
			case CLOSE_THREAD: $authorized = $canCloseThread; break; 
		
			case PROTECT_THREAD: $authorized = $canProtectThread; break;
		 
			case LOCK_THREAD: $authorized = $canLockThread; break;
			
			case PIN_THREAD: $authorized = $canPinThread; break;
			
			case DELETE_THREAD: $authorized = $canDeleteThread; break;
		
			case FLAG_POST: $authorized = $canFlagPost; break;
		
			case TAG_HOT: $authorized = $canTagHot; break; 
		
			case LOCK_POST: $authorized = $canLockPost; break; 
			
			case PIN_POST: $authorized = $canPinPost; break; 
		
			case HIDE_POST: $authorized = $canHidePost; break; 
			
			case DELETE_POST: $authorized = $canDeletePost; break; 
		 
			case MOD: $authorized = $canBecomeMod; break; 
		
			case SUPER_MOD: $authorized = $canBecomeSuperMod; break; 
			
			case FEAT_THREAD: $authorized = $canFeatThread; break; 
		
			case ULTIMATE_MOD: $authorized = $ultimateLevel; break;
		 
			case VOTE_UP: $authorized = $canUpvote; break; 
		
			case VOTE_DOWN: $authorized = $canDownvote; break; 
		
			default: $authorized = $authorizationArr; 
			
		}
				
		////CONTROL MODS LOCKOUT/////
		if(MODS_LOCKOUT && !$admin)
			$authorized = $this->ENGINE->assoc_arr_reset($authorized, false);	
			
		return $authorized;
		
	}









		
	/*** Method for fetching section select list for moderators ***/
	public function getModPageSectionSelectList($selectedSection='', $retAllGrp=false){
		
		$moderatedSectionListOptions=$sectionListOptions=$categoryOptionGroup=$allSectionGrouped='';
		$xcept_arr = getExceptionParams('allvirtuals');
		
		///PDO QUERY///////
					
		$sql = "SELECT ID, SECTION_NAME FROM sections ORDER BY SECTION_NAME";
		$valArr = array();
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		
		$homepageOptionGroup=$categoryOptionGroup=$homepageOptionGroup="";
		
		while($row = $this->DBM->fetchRow($stmt)){
			
			$scatName = $row["SECTION_NAME"];
			$scatId = $row["ID"];
			$optVal = ''; //' value="'.$scatId.'" ';
			$isSelected = $optVal.((strtolower($scatName) == strtolower($selectedSection))? 'selected' : '');		
			
			if(!($this->ACCOUNT->sessionAccess(array('id'=>$scatId, 'isSid'=>true, 'cond'=>'staffsOnly'))))			
				continue;
					
			if($scatId == HOMEPAGE_SID)
				$homepageOptionGroup = '<option '.$isSelected.'>'.$scatName.'</option>';
			
			elseif(in_array($scatId, getExceptionParams('virtualcsid')))
				$categoryOptionGroup .= '<option '.$isSelected.'>'.$scatName.'</option>';
				
			else
				$sectionListOptions .= '<option '.$isSelected.'>'.$scatName.'</option>';
							
			/////EXCLUDE SECTIONS IN THE XCEPTIONS ARRAY////
			if(!(in_array($scatId, $xcept_arr)))
				$moderatedSectionListOptions .= '<option '.$isSelected.'>'.$scatName.'</option>';		
		
		}

		$allSectionGrouped = '<optgroup label="HOMEPAGE">'.$homepageOptionGroup.'</optgroup>
							<optgroup label="CATEGORIES">'.$categoryOptionGroup.'</optgroup>
							<optgroup label="SECTIONS">'.$sectionListOptions.'</optgroup>';
		
		if($retAllGrp)
			return $allSectionGrouped;
		
		return array($moderatedSectionListOptions, $sectionListOptions, $categoryOptionGroup, $homepageOptionGroup);
		
	}







	
	
		
	/*** Method for building moderation cms ***/
	private function getModerationCms($metaArr){

		$kArr = array(

					"actor", "tgtState", "has1WayState", "tgtId", "tgtAction", "tgtSticker", "dynamicFields", "dynamicFid",
					"actTxt1", "actTxt2", "cmsKey", "bEndUrl", "tgtData", "doCtxTgt", "doCtxByClsFind",
					"doCtxCls", "doClsHide", "isPostTgt", "isModPage", "smartTogglerData", "rdrQstr",
					"modPageModalTxt", "threadUrlTag", "tgtDelGrpCms"

				);

		list(
			
				$actor, $tgtState, $has1WayState, $tgtId, $tgtAction, $tgtSticker, $dynamicFields, $dynamicFid, 
				$actTxt1, $actTxt2, $cmsKey, $backEndUrl, $tgtData, $doCtxTgt, $doCtxByClsFind,
				$doCtxCls, $doClsHide, $isPostTgt, $isModPage, $smartTogglerData, $rdrQstr,
				$modPageModalTxt, $threadUrlTag, $tgtDelGrpCms

			) = $this->ENGINE->get_assoc_arr($metaArr, $kArr);

		$jsData = array( 
			'fEnd' => array(//Front End Data
				'lock' => '',
				'rankMsg' => '<span '.($K = 'class="alert alert-danger" data-click-to-hide="true" '._TIMED_FADE_OUT).'>'.MOD_METAS['msg']['rank'].'</span>',
				'lockMsg' => '<span '.$K.'>'.MOD_METAS['msg']['threadLock'].'</span>',
				'modalBox' => 'modal-drop', //class name
				'alertBox' => 'alert-box' //class naame			
			),

			'bEnd' => array(//Back End Data
				'url' => str_ireplace(array('pdem', 'tdem'), 'dem', $backEndUrl), 
				'data' => ''
			)
		);

		//STATE BOOL VALUES		
		$stateTrue = 1; 
		$stateFalse = 0;

		$modKeysMeta = MOD_METAS["keys"];
		$tgtTitle = ($isPostTgt? $modKeysMeta['postTgtTitle'] : $modKeysMeta['topicTgtTitle']);		
		$tgtActionMetas = MOD_METAS["values"][$tgtTitle];
		$isDelAct = ($tgtAction == $modKeysMeta["delete"]);
		$isLockAct = ($tgtAction == $modKeysMeta["lock"]);
		$isOpenAct = ($tgtAction == $modKeysMeta["open"]);
		$isCloseAct = ($tgtAction == $modKeysMeta["close"]);
		$has2WayState = (!$has1WayState && $tgtState);
		$hideCls = ' hide ';
		$clsGreen = 'green';
		$clsRed = 'red';
		($isDelAct || $isCloseAct)? ($clsGreen = $clsRed) : '';
		$stickerTag = $isPostTgt? 'div' : 'span';
		
		$tgtPrefix = ($isPostTgt? 'p' : 't').'-'.$tgtId.'-';
		list($tgtActorRankAuthorized) = $this->ACCOUNT->sessionRanksHigher($actor);		
		$tgtStickerBox = $tgtPrefix.$tgtAction.'-sticker';		
		$clsGrp = $tgtPrefix.$tgtAction;
		$modalId = $this->ENGINE->generate_sess_unq_token($clsGrp.'-modal-');
		$modalMutableBox = $clsGrp.'-modal-mutables';	
		$tgtDelGrpCls = $tgtPrefix.'del-lock';		
		$actTxt = $has2WayState? $actTxt2 : $actTxt1;			
		$modalMutable1 = '<b class="'.$clsGreen.'">'.$actTxt1.'</b>';			
		$modalMutable2 = '<b class="'.$clsRed.'">'.$actTxt2.'</b>';			
		$modalMutable = $has2WayState? $modalMutable2 : $modalMutable1;			
		$cmsSticker = '<'.$stickerTag.' class="'.$tgtStickerBox.' '.($tgtState? '' : $hideCls).'">'.$tgtSticker.'</'.$stickerTag.'>';					
		$jsData[($K = 'fEnd')]['cms'] = $cmsKey;
		$jsData[$K]['stickerBox'] = $tgtStickerBox;		
		$jsData[$K]['rank'] = $tgtActorRankAuthorized? $stateTrue : $stateFalse;
		$jsData[$K]['state'] = ($tgtState? $stateTrue : $stateFalse);
		$jsData[$K]['actTxt1'] = $actTxt1;
		$jsData[$K]['actTxt2'] = $actTxt2;
		$jsData[$K]['modalMutableBox'] = $modalMutableBox;
		$jsData[$K]['modalMutable1'] = $modalMutable1;
		$jsData[$K]['modalMutable2'] = $modalMutable2;

		$dynamicFid? ($jsData[$K]['dynamicFields'] = $dynamicFid) : ''; //dynamicFields can accept comma separated values E.g "id1, id2,..."		
		$isLockAct? ($jsData[$K]['lockTgtDelGrp'] = array('tgtGrp' => $tgtDelGrpCls, 'tgtGrpCms' => $tgtDelGrpCms)) : '';

		if($isOpenAct)
			$doClsHide = str_replace($modKeysMeta['open'], $modKeysMeta['close'], $tgtStickerBox);
		
		elseif($isCloseAct)
			$doClsHide = str_replace($modKeysMeta['close'], $modKeysMeta['open'], $tgtStickerBox);
		
		$doClsHide? ($jsData[$K]['doClsHide'] = $doClsHide) : '';

		 /*
		 	threadLock or postLock State accordingly will be the value passed 
			and used to install Locks on all associated delete cms buttons

		 */
		$isDelAct? ($jsData[$K]['lock'] = $tgtState) : '';

		if($doCtxTgt && $doCtxCls){

			$jsData[$K]['doCtxTgt'] = $doCtxTgt;
			$doCtxByClsFind? ($jsData[$K]['doCtxByClsFind'] = $doCtxByClsFind) : '';
			$jsData[$K]['doCtxCls'] = $doCtxCls;

		}

		$jsData['bEnd']['data'] = $tgtData.$modKeysMeta['action'].'='.$tgtActionMetas[$tgtAction];
		$modalDropCls = $tgtTitle.'-'.$tgtAction; //Important Structure referenced on the frontend (only alter with caution)
		$currentsName = '-mod-form-thread-url';
		$modalContentPrefix = 'You are about to <span class="'.$modalMutableBox.'">'.$modalMutable.'</span> '.
		($isPostTgt? 'this post' : 'the topic: <span '.($isModPage? 'id="mod-page'.$currentsName.'"' : 'class="t-'.$tgtId.$currentsName.'"').'>'.$threadUrlTag.'</span>');		
		$dropPaneCmcCls = $hideCls.$modalDropCls;		
		

		$modFormCmsDropPane = '<div class="'.$dropPaneCmcCls.'">
									<div class="alert alert-warning align-l">'.
										$modalContentPrefix.($modPageModalTxt? '<div class="italicize">'.$modPageModalTxt.'</div>' : '<br>').'						
										click the <span class="text-sticker text-sticker-success">PROCEED</span> button below to confirm 							
									</div>'
									.$dynamicFields.'
								</div>'; 
					

		$cmsDropPane = '<div class="'.$dropPaneCmcCls.' modal-drop has-close-btn box-close-target" id="'.$modalId.'">							
							'.$modalContentPrefix.$dynamicFields.'							
							<div>
								<button type="button" class="btn btn-'.($isDelAct? 'danger '.$tgtDelGrpCls : 'success').'" '.$cmsKey.'="'.$this->ENGINE->jsonify($jsData).'" value="OK" >OK</button>
								<input type="button" class="btn box-close" value="CLOSE" />														
							</div>						
						</div>';	
									

		$cmsBtn = '<a role="button" class="mod-ctrl" href="'.$backEndUrl.'?'.$jsData['bEnd']['data'].$rdrQstr.'" '.$smartTogglerData.' data-id-targets="'.$modalId.'" data-cls-grp="'.$clsGrp.'" title="'.$actTxt.' this '.$tgtTitle.'" >'.$actTxt.'</a>';

		return array($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker);


	}

	


	
		
	/*** Method for fetching moderation control buttons ***/
	public function getModerationControls($metaArr){
		
		global $FORUM;

		$topicCms=$topicManager=$postCms=$postManager=
		$modFormTopicDropPanes=$modFormPostDropPanes='';

		$topicCmsStickersArr=$postCmsStickersArr=array();
		
		$asterix = '<span class="asterix">*</span>';
		$optionSelected = ' selected="selected" ';
		
		$isModPage = isset($metaArr[$K="isModPage"])? $metaArr[$K] : false;
		$rdr = isset($metaArr[$K="rdr"])? $metaArr[$K] : '';
		$rdr .= isset($metaArr[$K="postHashId"])? '#'.$metaArr[$K] : '';
		$rdrQstr = '&_rdr='.$rdr;	
		$postId = isset($metaArr[$K="pid"])? $metaArr[$K] : '';
		$topicName = isset($metaArr[$K="tn"])? $metaArr[$K] : '';
		$section = isset($metaArr[$K="section"])? $metaArr[$K] : '';
		$topicId = isset($metaArr[$K="tid"])? $metaArr[$K] : '';
		$forFrontPage = isset($metaArr[$K="fp"])? $metaArr[$K] : false;
		$cmsStickerCombo = isset($metaArr[$K="cmsStickerCombo"])? $metaArr[$K] : true;
		$type = strtolower((isset($metaArr[$K="type"])? $metaArr[$K] : ''));							
		
		
		$modValuesMeta = MOD_METAS["values"];
		$modTopicValuesMeta = $modValuesMeta["topic"];
		$modPostValuesMeta = $modValuesMeta["post"];		
		$modKeysMeta = MOD_METAS["keys"];
		$actionKey = $modKeysMeta["action"];
		$tidKey = $modKeysMeta["topicId"];
		$pidKey = $modKeysMeta["postId"];
		$sidKey = $modKeysMeta["sectionId"];
		$rsnKey = $modKeysMeta["reason"];	
		$reasonLabel = 'REASON WHY:';
		$formSubmitBtnTxt = 'PROCEED';  
		$genModPageTxt = '<br> click the <span class="text-sticker text-sticker-success">'.$formSubmitBtnTxt.'</span> button below to confirm ';
		$threadSlug = $this->SITE->getThreadSlug($topicId);	
		$threadUrlTag = '<a href="'.$threadSlug.'" class="links" >'.$topicName.'</a>'					;		
		$hideCls = ' hide ';											
		$modPageAlertBoxCls = 'class="alert alert-warning align-l"';
		$cmsCmnCls = 'mod-ctrl';
		$cmsCmnAttr = ' role="button"  class="'.$cmsCmnCls.'" ';
		$stickerCmsCls = ' clear base-lr-pad ';
		$tDelLockTgtCls = 't-'.$topicId.'-del-lock';
		$pDelLockTgtCls = 'p-'.$postId.'-del-lock';
		$backEndBaseUrl = '/mod-tools/';
		$decentralizedModUrl = $backEndBaseUrl.'dem'.($rdr? "?_rdr=".$rdr : ""); //JS Support url
		$postDecentralizedModUrl = $backEndBaseUrl.'pdem'; //No JS support fallback url
		$topicDecentralizedModUrl = $backEndBaseUrl.'tdem'; //No JS support fallback url
		$cmsCtrlCtx = 'staff-cms';
		$cmsBaseCls = $cmsCtrlCtx.' mods-btn-ctrl';		
		$topicKeyValQstr = $modKeysMeta['topicId'].'='.$topicId.'&';
		$topicTgtData = $modKeysMeta[($K = 'target')].'='.$modKeysMeta['topic'].'&'.$topicKeyValQstr;
		$postTgtData = $modKeysMeta[$K].'='.$modKeysMeta['post'].'&'.$modKeysMeta['postId'].'='.$postId.'&'.$topicKeyValQstr;		

		//CMS KEYS
		$tDelCmsKey = 'data-td-cms'; //thread delete
		$tLockCmsKey = 'data-tl-cms'; //thread lock
		$tMovCmsKey = 'data-tm-cms'; //thread move
		$tRenCmsKey = 'data-trn-cms'; //thread rename
		$tPinCmsKey = 'data-tpn-cms'; //thread pin
		$tProCmsKey = 'data-tpr-cms'; //thread protection
		$tFeatCmsKey = 'data-tft-cms'; //thread feature
		$tTagCmsKey = 'data-tth-cms'; //thread tag hot
		$tCloseCmsKey = 'data-tc-cms'; //thread close
		$tOpenCmsKey = 'data-to-cms'; //thread open
		$pHideCmsKey = 'data-ph-cms'; //post hide
		$pLockCmsKey = 'data-pl-cms'; //post lock
		$pPinCmsKey = 'data-ppn-cms'; //post pin
		$pDelCmsKey = 'data-pd-cms'; //post delete

		//STATE BOOL VALUES		
		$stateTrue = 1; 
		$stateFalse = 0;

		//THREAD STATES AND ACTORS		
		$threadStatesArr = $this->getThreadStatus($topicId);
		$threadLocked = $threadStatesArr['isLocked'];
		$threadLocker = $threadStatesArr['locker'];
		$threadPinned = $threadStatesArr['isPinned'];
		$threadPinner = $threadStatesArr['pinner'];
		$threadRenamed = $threadStatesArr['isRenamed'];
		$threadRenamer = $threadStatesArr['renamer'];
		$threadRecycled = $threadStatesArr['isRecycled'];
		$threadRecycler = $threadStatesArr['recycler'];
		$threadMoved = $threadStatesArr['isMoved'];
		$threadMover = $threadStatesArr['mover'];
		$threadProtected = $threadStatesArr['isProtected'];
		$threadProtecter = $threadStatesArr['protecter'];
		$threadFeatured = $threadStatesArr['isFeatured'];
		$threadFeaturer = $threadStatesArr['featurer'];		
		$threadHot = $threadStatesArr['isHot'];		
		$threadHoter = $threadStatesArr['hoter'];		
		$threadClosed = $threadStatesArr['ocs'];		
		$threadOpenerOrCloser = $threadStatesArr['ocsUid'];	

		//POST STATES AND ACTORS
		$postStatesArr = $this->getPostStatus($postId);
		$postLocked = $postStatesArr['isLocked'];
		$postLocker = $postStatesArr['locker'];
		$postHidden = $postStatesArr['isHidden'];
		$postHider = $postStatesArr['hider'];
		$postPinned = $postStatesArr['isPinned'];
		$postPinner = $postStatesArr['pinner'];
		

		//STICKERS
		$stkClsExt1 = 'class="clear base-lr-pad"';		
		$stkFloatCls = 'pull-r';
		$stkClsExt = 'class="red pl-5 '.$stkFloatCls.'"';
		$stkClsInt = 'class="visible-lg-inlineXX hide"';
		$ariaAttr = 'aria-hidden="true"';
		$genHideSticker = '<span '.$stkClsExt.'><i class="fas fa-eye-slash" '.$ariaAttr.' title="hidden"></i> <span '.$stkClsInt.'>HIDDEN</span></span>';
		$genLockSticker = '<span '.$stkClsExt.'><i class="fas fa-lock" '.$ariaAttr.' title="locked"></i> <span '.$stkClsInt.'>LOCKED</span></span>';
		$genPinSticker = '<span '.$stkClsExt.'><i class="fa fa-map-pin" '.$ariaAttr.' title="pinned"></i> <span '.$stkClsInt.'>PINNED</span></span>';
		
		$floatedHiddenSticker = '<div '.$stkClsExt1.'>'.$genHideSticker.'</div>';
		$floatedLockedSticker = '<div '.$stkClsExt1.'>'.$genLockSticker.'</div>';
		$floatedPinnedSticker = '<div '.$stkClsExt1.'>'.$genPinSticker.'</div>';

		$unfloatedLockedSticker = str_replace($stkFloatCls, '', $genLockSticker);
		$unfloatedPinnedSticker = str_replace($stkFloatCls, '', $genPinSticker);
		
		$featuredSticker = '<span class="'.($txtStickerCmnCls = 'text-sticker text-sticker-').'primary" title="Featured => This thread has made it to the frontpage">'.$this->SITE->getFA('fa-star').'</span>';
		$hotSticker = '<span class="'.$txtStickerCmnCls.'danger" title="hot">HOT</span>';
		$openedSticker = '<span class="'.$txtStickerCmnCls.'success" title="open">OPEN</span>';
		$closedSticker = '<span class="'.$txtStickerCmnCls.'danger" title="closed">CLOSED</span>';
		
		//GET THE THREAD PROTECTION STICKER
		list($floatedProtSticker, $unfloatedProtSticker) = $this->getThreadProtectionSticker($threadProtected);
		

		//Accumulate stickers for public users without access (overriden below for authorized users) 
		$postHidden? ($postCmsStickersArr[$pHideCmsKey] = $floatedHiddenSticker) : '';
		$postLocked? ($postCmsStickersArr[$pLockCmsKey] = $floatedLockedSticker) : '';
		$postPinned? ($postCmsStickersArr[$pPinCmsKey] = $floatedPinnedSticker) : '';

		$threadClosed? ($topicCmsStickersArr[$tCloseCmsKey] = $closedSticker) : '';
		!$threadClosed? ($topicCmsStickersArr[$tOpenCmsKey] = $openedSticker) : '';
		$threadFeatured? ($topicCmsStickersArr[$tFeatCmsKey] = $featuredSticker) : '';
		$threadPinned? ($topicCmsStickersArr[$tPinCmsKey] = $unfloatedPinnedSticker) : '';
		$threadHot? ($topicCmsStickersArr[$tTagCmsKey] = $hotSticker) : '';
		$threadLocked? ($topicCmsStickersArr[$tLockCmsKey] = $unfloatedLockedSticker) : '';

		
		///GET AUTHORIZATIONS///
		$modsAuth = $FORUM->authorizeModeration('', $topicId, $postId);		

		$canOpenThread = $canCloseThread = $modsAuth["closeThread"];
		$canMoveThread = $modsAuth["moveThread"];
		$canRenameThread = $modsAuth["renameThread"];
		$canProtectThread = $modsAuth["protectThread"];
		$canDeleteThread = $modsAuth["deleteThread"];
		$canTagThreadHot = $modsAuth["tagHot"];
		$canLockThread = $modsAuth["lockThread"];
		$canFeatureThread = $modsAuth["featThread"];
		$canPinThread = $modsAuth["pinThread"];
		$canLockPost = $modsAuth["lockPost"];
		$canPinPost = $modsAuth["pinPost"];
		$canHidePost = $modsAuth["hidePost"];
		$canDeletePost = $modsAuth["deletePost"];
		//$mod = $modsAuth["mod"];
		//$superMod = $modsAuth["superMod"];
		//$trustedUser = $modsAuth["ultimateMod"];
		//$admin = $this->SESS->isAdmin();
		//$topStaff = $this->SESS->isTopStaff();

		$smartTogglerData = ' data-toggle="smartToggler" data-align-to-context="true" data-close-others-in-context="'.$cmsCtrlCtx.'" ';

			
		$authorizedUser = ($canCloseThread || $canMoveThread || $canRenameThread || $canProtectThread || $canTagThreadHot
							|| $canPinThread || $canDeleteThread || $canFeatureThread || $canLockThread || $canLockPost 
							|| $canPinPost || $canHidePost || $canDeletePost
						);
			
		if($authorizedUser){																								
			
			/*** BUILD POST CMS JS DATA ***/

			if($postId || $isModPage){						
			
				//POST HIDE
				if($canHidePost){

					$cmsMetaArr = array(

						"actor" => $postHider, "tgtState" => $postHidden, 
						"tgtId" => $postId, "tgtAction" => $modKeysMeta["hide"], "tgtSticker" => $floatedHiddenSticker, 						
						"actTxt1" => "Hide", "actTxt2" => "Show", "cmsKey" => $pHideCmsKey, 
						"bEndUrl" => $postDecentralizedModUrl, "tgtData" => $postTgtData, "isModPage" => $isModPage,
						"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, "modPageModalTxt" => "",
						"isPostTgt" => true, "doCtxTgt" => "postli", 
						"doCtxCls" => "dimmed", "doClsHide" => "", 
						 "dynamicFields" => "", "dynamicFid" => ""
				
					);

					list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
					$postHideDropPane = $cmsDropPane;
					$modFormPostDropPanes .= $modFormCmsDropPane;
					$postCms .= $cmsBtn.$postHideDropPane;
					$postCmsStickersArr[$pHideCmsKey] = $cmsSticker;
					
					
				}

				//POST LOCK
				if($canLockPost){

					$cmsMetaArr = array(

						"actor" => $postLocker, "tgtState" => $postLocked, 
						"tgtId" => $postId, "tgtAction" => $modKeysMeta["lock"], "tgtSticker" => $floatedLockedSticker, 						
						"actTxt1" => "Lock", "actTxt2" => "Unlock", "cmsKey" => $pLockCmsKey, "tgtDelGrpCms" => $pDelCmsKey, 
						"bEndUrl" => $postDecentralizedModUrl, "tgtData" => $postTgtData, "isModPage" => $isModPage,
						"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, "modPageModalTxt" => "",
						"isPostTgt" => true, "doCtxTgt" => "", 
						"doCtxCls" => "", "doClsHide" => "", 
						 "dynamicFields" => "", "dynamicFid" => ""
				
					);

					list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
					$postLockDropPane = $cmsDropPane;
					$modFormPostDropPanes .= $modFormCmsDropPane;
					$postCms .= $cmsBtn.$postLockDropPane;
					$postCmsStickersArr[$pLockCmsKey] = $cmsSticker;

				}
							
				//POST PIN
				if($canPinPost){

					$cmsMetaArr = array(

						"actor" => $postPinner, "tgtState" => $postPinned, 
						"tgtId" => $postId, "tgtAction" => $modKeysMeta["pin"], "tgtSticker" => $floatedPinnedSticker, 						
						"actTxt1" => "Pin", "actTxt2" => "Unpin", "cmsKey" => $pPinCmsKey, 
						"bEndUrl" => $postDecentralizedModUrl, "tgtData" => $postTgtData, "isModPage" => $isModPage,
						"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, "modPageModalTxt" => "",
						"isPostTgt" => true, "doCtxTgt" => "", 
						"doCtxCls" => "", "doClsHide" => "", 
						 "dynamicFields" => "", "dynamicFid" => ""
				
					);

					list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
					$postPinDropPane = $cmsDropPane;
					$modFormPostDropPanes .= $modFormCmsDropPane;
					$postCms .= $cmsBtn.$postPinDropPane;
					$postCmsStickersArr[$pPinCmsKey] = $cmsSticker;

				}

				//POST DELETE
				if($canDeletePost){

					$cmsMetaArr = array(

						"actor" => "", "tgtState" => $postLocked, 
						"tgtId" => $postId, "tgtAction" => $modKeysMeta["delete"], "tgtSticker" => "", 						
						"actTxt1" => "Delete", "actTxt2" => "Delete", "cmsKey" => $pDelCmsKey,
						"bEndUrl" => $postDecentralizedModUrl, "tgtData" => $postTgtData, "isModPage" => $isModPage,
						"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, "modPageModalTxt" => "",
						"isPostTgt" => true, "doCtxTgt" => "",
						"doCtxCls" => "", "doClsHide" => "p-".$postId."-base", 
						 "dynamicFields" => "", "dynamicFid" => ""
				
					);

					list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
					$postDelDropPane = $cmsDropPane;
					$modFormPostDropPanes .= $modFormCmsDropPane;
					$postCms .= $cmsBtn.$postDelDropPane;
					$postCmsStickersArr[$pDelCmsKey] = $cmsSticker;					

				}
				
			}
				

			/*** BUILD TOPIC CMS JS DATA ***/

			//TOPIC DELETE
			if($canDeleteThread){
				
				$cmsMetaArr = array(

					"actor" => "", "tgtState" => $threadLocked, "has1WayState" => true,
					"tgtId" => $topicId, "tgtAction" => $modKeysMeta["delete"], "tgtSticker" => "", 						
					"actTxt1" => "Delete", "actTxt2" => "Delete", "cmsKey" => $tDelCmsKey,
					"bEndUrl" => $topicDecentralizedModUrl, "tgtData" => $topicTgtData, "isModPage" => $isModPage,
					"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, "modPageModalTxt" => "",
					"threadUrlTag" => $threadUrlTag, "isPostTgt" => false, "doCtxTgt" => "", 
					"doCtxCls" => "", "doClsHide" => "t-".$topicId."-base", 
					 "dynamicFields" => "", "dynamicFid" => ""
			
				);

				list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
				$topicDelDropPane = $cmsDropPane;
				$modFormTopicDropPanes .= $modFormCmsDropPane;
				$topicCms .= $cmsBtn.$topicDelDropPane;
				$topicCmsStickersArr[$tDelCmsKey] = $cmsSticker;	

			}
			
			//TOPIC CLOSE
			if($canCloseThread){
				
				$modReason = isset($_POST[$rsnKey])? $_POST[$rsnKey] : '';

				$cmsMetaArr = array(

					"actor" => $threadOpenerOrCloser, "tgtState" => $threadClosed, "has1WayState" => true,
					"tgtId" => $topicId, "tgtAction" => $modKeysMeta["close"], "tgtSticker" => $closedSticker, 						
					"actTxt1" => "Close", "actTxt2" => "Close", "cmsKey" => $tCloseCmsKey,
					"bEndUrl" => $topicDecentralizedModUrl, "tgtData" => $topicTgtData, "isModPage" => $isModPage,
					"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, "modPageModalTxt" => "",
					"threadUrlTag" => $threadUrlTag, "isPostTgt" => false, "doCtxTgt" => "", 
					"doCtxCls" => "", "doClsHide" => "", 
					"dynamicFid" => ($dynamicFid = $this->ENGINE->generate_sess_unq_token("t-".$topicId."-close-dynamic-f-")),
					"dynamicFields" => '
						<div class="field-ctrl">
							<label>'.$reasonLabel.'</label><br/>
							<select name="'.$rsnKey.'" id="'.$dynamicFid.'" class="field col-w-10"  >
								<option value="">--select your reason--</option>												
								<option '.(($modReason == ($K = 'Grown Too Large'))? $optionSelected : '').' >'.$K.'</option>																				
								<option '.(($modReason == ($K = 'Duplicate/Redundant'))? $optionSelected : '').' >'.$K.'</option>																				
								<option '.(($modReason == ($K = 'Promotes Violence'))? $optionSelected : '').' >'.$K.'</option>																				
								<option '.(($modReason == ($K = 'Fake Content'))? $optionSelected : '').' >'.$K.'</option>																				
								<option '.(($modReason == ($K = 'Child Abuse'))? $optionSelected : '').' >'.$K.'</option>																				
								<option '.(($modReason == ($K = 'Abusive/Offensive'))? $optionSelected : '').' >'.$K.'</option>																				
								<option '.(($modReason == ($K = 'Prohibited Discussion'))? $optionSelected : '').' >'.$K.'</option>																				
								<option '.(($modReason == ($K = 'Political Incitation'))? $optionSelected : '').' >'.$K.'</option>																				
								<option '.(($modReason == ($K = 'Religious Incitation'))? $optionSelected : '').' >'.$K.'</option>																				
							</select>
						</div>'
						
				);

				list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
				$topicCloseDropPane = $cmsDropPane;
				$modFormTopicDropPanes .= $modFormCmsDropPane;
				$topicCms .= $cmsBtn.$topicCloseDropPane;
				$topicCmsStickersArr[$tCloseCmsKey] = $cmsSticker;	

			}
			
			//TOPIC OPEN
			if($canOpenThread){
				
				$cmsMetaArr = array(

					"actor" => $threadOpenerOrCloser, "tgtState" => !$threadClosed, "has1WayState" => true, 
					"tgtId" => $topicId, "tgtAction" => $modKeysMeta["open"], "tgtSticker" => $openedSticker, 						
					"actTxt1" => "Open", "actTxt2" => "Open", "cmsKey" => $tOpenCmsKey,
					"bEndUrl" => $topicDecentralizedModUrl, "tgtData" => $topicTgtData, "isModPage" => $isModPage,
					"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, "modPageModalTxt" => "",
					"threadUrlTag" => $threadUrlTag, "isPostTgt" => false, "doCtxTgt" => "", 
					"doCtxCls" => "", "doClsHide" => "", 
					"dynamicFields" => "", "dynamicFid" => ""
			
				);

				list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
				$topicOpenDropPane = $cmsDropPane;
				$modFormTopicDropPanes .= $modFormCmsDropPane;
				$topicCms .= $cmsBtn.$topicOpenDropPane;
				$topicCmsStickersArr[$tOpenCmsKey] = $cmsSticker;	

			}
			
			//TOPIC FEATURE
			if($canFeatureThread){
					
				$cmsMetaArr = array(

					"actor" => $threadFeaturer, "tgtState" => $threadFeatured, 
					"tgtId" => $topicId, "tgtAction" => $modKeysMeta["feature"], "tgtSticker" => $featuredSticker, 						
					"actTxt1" => "Feature", "actTxt2" => "Unfeature", "cmsKey" => $tFeatCmsKey, 
					"bEndUrl" => $topicDecentralizedModUrl, "tgtData" => $topicTgtData, "isModPage" => $isModPage,
					"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, 					
					"threadUrlTag" => $threadUrlTag, "isPostTgt" => false, "doCtxTgt" => "modal-drop", 
					"doCtxByClsFind" => ($ctxFinderCls = "t-".$topicId."-feat-doCtx-finder"), 
					"doCtxCls" => $hideCls, "doClsHide" => "", 
					"dynamicFid" => ($dynamicFid = $this->ENGINE->generate_sess_unq_token("t-".$topicId."-feat-dynamic-f-")),
					"dynamicFields" => '
						<div class="field-ctrl '.$ctxFinderCls.($threadFeatured? $hideCls : '').'">
							<label>(Enter a name to feature this topic with or leave it blank to use the topic name)</label>
							<input class="field col-w-10" type="text" id="'.$dynamicFid.'" name="'.$modKeysMeta["featName"].'" placeholder="Enter a feature name" />
						</div>', 
					"modPageModalTxt" => "* Enter a name to feature the topic with or leave blank to use the topic name (ignore field when unfeaturing)"
			
				);

				list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
				$topicFeatDropPane = $cmsDropPane;
				$modFormTopicDropPanes .= $modFormCmsDropPane;
				$topicCms .= $cmsBtn.$topicFeatDropPane;
				$topicCmsStickersArr[$tFeatCmsKey] = $cmsSticker;	


			}

			
			//TOPIC PIN
			if($canPinThread){
				
				$cmsMetaArr = array(

					"actor" => $threadPinner, "tgtState" => $threadPinned, 
					"tgtId" => $topicId, "tgtAction" => $modKeysMeta["pin"], "tgtSticker" => $unfloatedPinnedSticker, 						
					"actTxt1" => "Pin", "actTxt2" => "Unpin", "cmsKey" => $tPinCmsKey, 
					"bEndUrl" => $topicDecentralizedModUrl, "tgtData" => $topicTgtData, "isModPage" => $isModPage,
					"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, 					
					"threadUrlTag" => $threadUrlTag, "isPostTgt" => false, "doCtxTgt" => "", 
					"doCtxByClsFind" => "", "doCtxCls" => "", "doClsHide" => "", 
					"dynamicFid" => "", "dynamicFields" => "", "modPageModalTxt" => ""
			
				);

				list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
				$topicPinDropPane = $cmsDropPane;
				$modFormTopicDropPanes .= $modFormCmsDropPane;				

				//if($forFrontPage){					
						
				$topicCms .= $cmsBtn.$topicPinDropPane;
				$topicCmsStickersArr[$tPinCmsKey] = $cmsSticker;

				//}


			}

			
			//TOPIC TAG HOT
			if($canTagThreadHot){								
				
				$cmsMetaArr = array(

					"actor" => $threadHoter, "tgtState" => $threadHot, 
					"tgtId" => $topicId, "tgtAction" => $modKeysMeta["tagHot"], "tgtSticker" => $hotSticker, 						
					"actTxt1" => "Tag hot", "actTxt2" => "Untag hot", "cmsKey" => $tTagCmsKey, 
					"bEndUrl" => $topicDecentralizedModUrl, "tgtData" => $topicTgtData, "isModPage" => $isModPage,
					"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, 					
					"threadUrlTag" => $threadUrlTag, "isPostTgt" => false, "doCtxTgt" => "", 
					"doCtxByClsFind" => "", "doCtxCls" => "", "doClsHide" => "", 
					"dynamicFid" => "", "dynamicFields" => "", "modPageModalTxt" => ""
			
				);

				list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
				$topicTagHotDropPane = $cmsDropPane;
				$modFormTopicDropPanes .= $modFormCmsDropPane;
				$topicCms .= $cmsBtn.$topicTagHotDropPane;
				$topicCmsStickersArr[$tTagCmsKey] = $cmsSticker;

			}

			//TOPIC LOCK
			if($canLockThread){	
				
				$cmsMetaArr = array(

					"actor" => $threadLocker, "tgtState" => $threadLocked, 
					"tgtId" => $topicId, "tgtAction" => $modKeysMeta["lock"], "tgtSticker" => $unfloatedLockedSticker, 						
					"actTxt1" => "Lock", "actTxt2" => "Unlock", "cmsKey" => $tLockCmsKey, "tgtDelGrpCms" => $tDelCmsKey, 
					"bEndUrl" => $topicDecentralizedModUrl, "tgtData" => $topicTgtData, "isModPage" => $isModPage,
					"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, 					
					"threadUrlTag" => $threadUrlTag, "isPostTgt" => false, "doCtxTgt" => "", 
					"doCtxByClsFind" => "", "doCtxCls" => "", "doClsHide" => "", 
					"dynamicFid" => "", "dynamicFields" => "", "modPageModalTxt" => ""
			
				);

				list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
				$topicLockDropPane = $cmsDropPane;
				$modFormTopicDropPanes .= $modFormCmsDropPane;
				$topicCms .= $cmsBtn.$topicLockDropPane;
				$topicCmsStickersArr[$tLockCmsKey] = $cmsSticker;
								
			}									
			
			//TOPIC RENAME
			if($canRenameThread){
					
				$cmsMetaArr = array(

					"actor" => $threadRenamer, "tgtState" => $threadRenamed, "has1WayState" => true, 
					"tgtId" => $topicId, "tgtAction" => $modKeysMeta["rename"], "tgtSticker" => "", 						
					"actTxt1" => "Rename", "actTxt2" => "Rename", "cmsKey" => $tRenCmsKey, 
					"bEndUrl" => $topicDecentralizedModUrl, "tgtData" => $topicTgtData, "isModPage" => $isModPage,
					"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, 					
					"threadUrlTag" => $threadUrlTag, "isPostTgt" => false, "doCtxTgt" => "", 
					"doCtxByClsFind" => "", "doCtxCls" => "", "doClsHide" => "", 
					"dynamicFid" => ($dynamicFid = $this->ENGINE->generate_sess_unq_token("t-".$topicId."-ren-dynamic-f-")),
					"dynamicFields" => '
						<div class="field-ctrl">
							<label>TOPIC NEW NAME</label><br/>
							<input class="field col-w-10" type="text" id="'.$dynamicFid.'" name="'.$modKeysMeta["newTopicName"].'" maxlength="'.MAX_TOPIC_NAME_LEN.'" placeholder="Enter the new topic name here" />
						</div>', 
					"modPageModalTxt" => "* Enter the new name"
			
				);

				list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
				$topicRenDropPane = $cmsDropPane;
				$modFormTopicDropPanes .= $modFormCmsDropPane;
				$topicCms .= $cmsBtn.$topicRenDropPane;
				$topicCmsStickersArr[$tRenCmsKey] = $cmsSticker;


			}
				
			//TOPIC MOVE
			if($canMoveThread){
				
				$currentsName = '-mod-form-curr-section';

				$cmsMetaArr = array(

					"actor" => $threadMover, "tgtState" => $threadMoved, "has1WayState" => true, 
					"tgtId" => $topicId, "tgtAction" => $modKeysMeta["move"], "tgtSticker" => "", 						
					"actTxt1" => "Move", "actTxt2" => "Move", "cmsKey" => $tMovCmsKey, 
					"bEndUrl" => $topicDecentralizedModUrl, "tgtData" => $topicTgtData, "isModPage" => $isModPage,
					"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, 					
					"threadUrlTag" => $threadUrlTag, "isPostTgt" => false, "doCtxTgt" => "", 
					"doCtxByClsFind" => "", "doCtxCls" => "", "doClsHide" => "", 
					"dynamicFid" => ($dynamicFid = $this->ENGINE->generate_sess_unq_token("t-".$topicId."-mov-dynamic-f-")),
					"dynamicFields" => '
						<div class="field-ctrl">
							<label>NEW TARGET SECTION</label><br/>
							<select class="field col-w-10 mods-section-select-list" id="'.$dynamicFid.'" name="'.$modKeysMeta["newSectionName"].'"></select>
						</div>', 
					"modPageModalTxt" => '* Current Section: <strong '.($isModPage? 'id="mod-page'.$currentsName.'"' : 'class="t-'.$topicId.$currentsName.'"').'></strong>
										<br/> * Select the new target section'
			
				);

				list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
				$topicMovDropPane = $cmsDropPane;
				$modFormTopicDropPanes .= $modFormCmsDropPane;
				$topicCms .= $cmsBtn.$topicMovDropPane;
				$topicCmsStickersArr[$tMovCmsKey] = $cmsSticker;


			}


			//PROTECT THREAD
			if($canProtectThread){
				
				$protLevel = isset($_POST[$modKeysMeta[($K = "protection")]])? $_POST[$modKeysMeta[$K]] : '';
				$currentsName = '-mod-form-curr-protection';

				$cmsMetaArr = array(

					"actor" => $threadProtecter, "tgtState" => $threadProtected, "has1WayState" => true,
					"tgtId" => $topicId, "tgtAction" => $modKeysMeta["protect"], "tgtSticker" => "", 						
					"actTxt1" => "Protect", "actTxt2" => "Protect", "cmsKey" => $tProCmsKey, 
					"bEndUrl" => $topicDecentralizedModUrl, "tgtData" => $topicTgtData, "isModPage" => $isModPage,
					"smartTogglerData" => $smartTogglerData, "rdrQstr" => $rdrQstr, 					
					"threadUrlTag" => $threadUrlTag, "isPostTgt" => false, "doCtxTgt" => "", 
					"doCtxByClsFind" => "", "doCtxCls" => "", "doClsHide" => "", 
					"dynamicFid" => ($dynamicFid = $this->ENGINE->generate_sess_unq_token("t-".$topicId."-pro-dynamic-f-")),
					"dynamicFields" => '
						<div class="field-ctrl">
							<label>PROTECTION LEVEL<span class="red"></span>: </label>				
							<select name="'.$modKeysMeta['protection'].'" id="'.$dynamicFid.'" class="field col-w-10"  >
								<option value="">--select protection--</option>												
								<option '.(($protLevel == ($K = THREAD_PROTECTION_LEVEL_0))? $optionSelected : '').' >'.$K.'</option>												
								<option '.(($protLevel == ($K = THREAD_PROTECTION_LEVEL_1))? $optionSelected : '').' >'.$K.'</option>
								<option '.(($protLevel == ($K = THREAD_PROTECTION_LEVEL_2))? $optionSelected : '').' >'.$K.'</option>
								<option '.(($protLevel == ($K = THREAD_PROTECTION_LEVEL_3))? $optionSelected : '').' >'.$K.'</option>
								<option '.(($protLevel == ($K = THREAD_PROTECTION_LEVEL_4))? $optionSelected : '').' >'.$K.'</option>
								<option '.(($protLevel == ($K = THREAD_PROTECTION_LEVEL_5))? $optionSelected : '').' >'.$K.'</option>
								<option '.(($protLevel == ($K = THREAD_PROTECTION_LEVEL_6))? $optionSelected : '').' >'.$K.'</option>
								<option '.(($protLevel == ($K = THREAD_PROTECTION_LEVEL_7))? $optionSelected : '').' >'.$K.'</option>
								<option '.(($protLevel == ($K = THREAD_PROTECTION_LEVEL_8))? $optionSelected : '').' >'.$K.'</option>										
							</select>
						</div>', 
					"modPageModalTxt" => '* Current Protection: <strong '.($isModPage? 'id="mod-page'.$currentsName.'"' : 'class="t-'.$topicId.$currentsName.'"').'></strong>
										<br/> * Select the new protection level'
			
				);

				list($cmsBtn, $cmsDropPane, $modFormCmsDropPane, $cmsSticker) = $this->getModerationCms($cmsMetaArr);
				$topicProtDropPane = $cmsDropPane;
				$modFormTopicDropPanes .= $modFormCmsDropPane;
				$topicCms .= $cmsBtn.$topicProtDropPane;
				$topicCmsStickersArr[$tProCmsKey] = $cmsSticker;


			}
									

			$disabledReadonly = 'readonly="readonly" title="This Field is not editable"';
			
			$formAction = $isModPage? $this->ENGINE->get_page_path('rel_page_url').'#mto' : $decentralizedModUrl;													
			$action = isset($_POST[$modKeysMeta[($K = "action")]])? $_POST[$modKeysMeta[$K]] : '';
			$topicMngrFormSubmitName = $modKeysMeta['topicManager'];
			$postMngrFormSubmitName = $modKeysMeta['postManager'];
			$formAttrCmn = ' class="mod-tools" action="'.$formAction.'" method="post" data-response-holder="#'.($ajaxResId = $this->ENGINE->generate_sess_unq_token('ajax-res')).'"';
			$topicMngrFormAttr = $formAttrCmn.' data-submit-name="'.$topicMngrFormSubmitName.'" ';
			$postMngrFormAttr = $formAttrCmn.' data-submit-name="'.$postMngrFormSubmitName.'" ';
			$formFieldCmn = '<div class="field-ctrl reason">
								<label>'.$reasonLabel.' <small class="prime">(case-wise optional)</small></label>
								<textarea data-minLength="'.MIN_MOD_REASON.'" maxLength="'.MAX_MOD_REASON.'" data-field-count="true" class="field" name="'.$rsnKey.'" placeholder="Briefly describe the reason for the action(case-wise optional)" >'.(isset($_POST[$rsnKey])? $_POST[$rsnKey] : '').'</textarea>																																	
								<div id="'.$ajaxResId.'"></div>											
							</div>';	
							
			$modMetas = MOD_METAS;
			$modCurrTrigger = 'data-mod-currents="'.$topicId.'"';

			if($canLockPost || $canPinPost || $canHidePost || $canDeletePost){								

				$postManager = $modPagePostMngr = '
				<form '.$postMngrFormAttr.'>
					'.($isModPage? 
						
					'<div class="field-ctrl">
						<label>POST ID'.((isset($_POST[$pidKey]) && !$_POST[$pidKey])? $asterix : '').':</label>
						<input class="field" '.$modCurrTrigger.' type="number" min="1" placeholder="Type here the id of the post you want to manage " name="'.$pidKey.'" value="'.(isset($_POST[$pidKey])? $_POST[$pidKey] : '').'" />											
					</div>'
					:
					'<div class="font-default">
						<span class="bold"><b class="flab">TOPIC ID:</b> '.$topicId.'</span>&nbsp;&nbsp;
						<span class="bold"><b class="flab">POST ID:</b> '.$postId.'</span>
						<span class="bold"><b class="flab">SECTION:</b> '.$section.'</span>
					</div>								
					<input type="hidden" '.$modCurrTrigger.' name="'.$tidKey.'" value="'.$topicId.'" '.$disabledReadonly.' />
					<input type="hidden" name="'.$pidKey.'" value="'.$postId.'" '.$disabledReadonly.' />
					<input type="hidden" name="'.$sidKey.'" value="'.$section.'" '.$disabledReadonly.' />'
					
					).'					
																				
					<div class="field-ctrl">
						<label>ACTION<span class="red">*</span>: </label>				
						<select name="'.$actionKey.'" class="field mod-actions" id="post-mod-form-actions" data-metas="'.$this->ENGINE->jsonify($modMetas).'" >
							<option value="">--select--</option>'.							
							($canLockPost? '
							<option value="'.($K = $modPostValuesMeta["lock"]).'" '.(($action == $K)? $optionSelected : '').' >Lock/Unlock Post</option>' 
							: ""
							).							
							($canPinPost? '
							<option value="'.($K = $modPostValuesMeta["pin"]).'" '.(($action == $K)? $optionSelected : '').' >Pin/Unpin Post</option>' 
							: ""
							).							
							($canHidePost? '
							<option value="'.($K = $modPostValuesMeta["hide"]).'" '.(($action == $K)? $optionSelected : '').' >Hide/Unhide Post</option>' 
							: ""
							).							
							($canDeletePost? '
							<option value="'.($K = $modPostValuesMeta["delete"]).'" '.(($action == $K)? $optionSelected : '').' >Delete Post</option>' 
							: ""
							).'							
						</select>
					</div>
					<div class="mod-pops">
						'.$modFormPostDropPanes.'
					</div>																					
					'.$formFieldCmn.'								
					<div class="field-ctrl align-c">
						<input type="hidden" name='.$modKeysMeta["target"].' value="'.$modKeysMeta["post"].'" />									
						<input '.($isModPage? 'class="form-btn btn-success"' : 'data-ajax-submit="true" class="btn btn-success"').' type="submit" value="'.$formSubmitBtnTxt.'" name="'.$postMngrFormSubmitName.'" />									
						'.($isModPage? '' : '<input class="btn close-toggle" type="button" value="CLOSE" />').'
					</div>
				</form>';

				$postManager = '<a href="'.$postDecentralizedModUrl.'" '.$cmsCmnAttr.$smartTogglerData.' title="Manage this post" >Manage</a>
								<div class="modal-drop hide has-close-btn">'.$postManager.'</div>';

			}


			if($canCloseThread || $canRenameThread || $canMoveThread || $canProtectThread || $canDeleteThread){																

				$topicManager = $modPageTopicMngr = '
				<form '.$topicMngrFormAttr.'>
					'.($isModPage? 
					
					'<div class="field-ctrl">
						<label>TOPIC ID'.((isset($_POST[$tidKey]) && !$_POST[$tidKey])? $asterix : '').':</label>
						<input class="field" '.$modCurrTrigger.' type="number" min="1" placeholder="Type here the topic id of the topic you want to manage " name="'.$tidKey.'" value="'.(isset($_POST[$tidKey])? $_POST[$tidKey] : '').'" />											
					</div>'
					:
					'<div class="font-default">
						<span class="bold"><b class="flab">TOPIC ID:</b> '.$topicId.'</span>&nbsp;&nbsp;
						<span class="bold"><b class="flab">SECTION:</b> '.$section.'</span>
					</div>								
					<input type="hidden" '.$modCurrTrigger.' name="'.$tidKey.'" value="'.$topicId.'" '.$disabledReadonly.' />
					<input type="hidden" name="'.$sidKey.'" value="'.$section.'" '.$disabledReadonly.' />'
					
					).'						
					
					<div class="field-ctrl">
						<label>ACTION<span class="red">*</span>: </label>				
						<select name="'.$actionKey.'" class="field mod-actions" id="topic-mod-form-actions" data-metas="'.$this->ENGINE->jsonify($modMetas).'" >
							<option value="">--select--</option>'.
							($canCloseThread? '
							<option value="'.($K = $modTopicValuesMeta["open"]).'" '.(($action == $K)? $optionSelected : '').' >Open Thread</option>							
							<option value="'.($K = $modTopicValuesMeta["close"]).'" '.(($action == $K)? $optionSelected : '').' >Close Thread</option>' 
							: ""
							).
							($canLockThread? '
							<option value="'.($K = $modTopicValuesMeta["lock"]).'" '.(($action == $K)? $optionSelected : '').' >Lock/Unlock Thread</option>'
							: ""
							).
							($canTagThreadHot? '
							<option value="'.($K = $modTopicValuesMeta["tagHot"]).'" '.(($action == $K)? $optionSelected : '').' >Tag/Untag Thread Hot</option>'
							: ""
							).
							($canFeatureThread?
							'<option value="'.($K = $modTopicValuesMeta["feature"]).'" '.(($action == $K)? $optionSelected : '').' >Feature/Unfeature Thread</option>' 
							: ""
							).
							($canPinThread?
							'<option value="'.($K = $modTopicValuesMeta["pin"]).'" '.(($action == $K)? $optionSelected : '').' >Pin/Unpin Thread</option>' 
							: ""
							).
							($canRenameThread? '
							<option value="'.($K = $modTopicValuesMeta["rename"]).'" '.(($action == $K)? $optionSelected : '').' >Rename Thread</option>' 
							: ""
							).							
							($canMoveThread?
							'<option value="'.($K = $modTopicValuesMeta["move"]).'" '.(($action == $K)? $optionSelected : '').' >Move Thread to New Section</option>' 
							: ""
							).
							($canProtectThread?
							'<option value="'.($K = $modTopicValuesMeta["protect"]).'" '.(($action == $K)? $optionSelected : '').' >Protect Thread</option>' 
							: ""
							).
							($canDeleteThread?
							'<option value="'.($K = $modTopicValuesMeta["delete"]).'" '.(($action == $K)? $optionSelected : '').' >Delete Thread</option>' 
							: ""
							).'
						</select>
					</div>
					<div class="mod-pops">
						'.$modFormTopicDropPanes.'
					</div>																					
					'.$formFieldCmn.'								
					<div class="field-ctrl align-c">
						<input type="hidden" name='.$modKeysMeta["target"].' value="'.$modKeysMeta["topic"].'" />														
						<input '.($isModPage? 'class="form-btn btn-success"' : 'data-ajax-submit="true" class="btn btn-success"').' type="submit" value="'.$formSubmitBtnTxt.'" name="'.$topicMngrFormSubmitName.'" />									
						'.($isModPage? '' : '<input class="btn close-toggle" type="button" value="CLOSE" />').'
					</div>
				</form>';

				if($isModPage)
					return array($modPageTopicMngr, $modPagePostMngr);

				$topicManager = '<a href="'.$topicDecentralizedModUrl.'" '.$cmsCmnAttr.$smartTogglerData.' title="Manage this topic" >Manage</a>
								<div class="modal-drop hide has-close-btn">'.$topicManager.'</div>';

			}
											
			//$topicCms .= $topicManager;
			//$postCms .= $postManager;		
			
			$postCms = '<div class="'.$cmsBaseCls.'">'.$postCms.'</div>'.($alertBox = '<div class="alert-box"></div>');		
			$topicCms = '<div class="'.$cmsBaseCls.'">'.$topicCms.'</div>'.$alertBox;				
						
						
		}

		//Convert array of stickers to string
		$postCmsStickers = implode('', $postCmsStickersArr);
		$topicCmsStickers = implode('', $topicCmsStickersArr);			
		
		$topicCmsStickers = '<div class="'.($cmsStickerCombo? 'topic-status-board base-ctrl base-rad' : '').' base-tb-pad align-c">								
								<div class="bold">'.$unfloatedProtSticker.$topicCmsStickers.(($cmsStickerCombo && $this->SESS->isStaff())? '<hr/>'.$topicCms : '').'</div>								
							</div>';
		
		return array($postCms, $postCmsStickers, $topicCms, $topicCmsStickers, $topicManager);
		
	}













	
	
	
		
	/*** Method for fetching last moderation activity username and time ***/
	public function getLastActivityModerator($t, $id){
		
		$a='';
		/////////PDO QUERY////	
		$sql =  "SELECT USER_ID,TIME FROM activity_logs WHERE (TYPE=? AND TYPE_ID=?) ORDER BY TIME DESC LIMIT 1";
		$valArr = array($t, $id);
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		$row = $this->DBM->fetchRow($stmt);
		
		if(!empty($row)){
			
			$uid = $row["USER_ID"];
			$time = $row["TIME"];
			$un = $this->ACCOUNT->memberIdToggle($uid);
			$a = $this->ACCOUNT->sanitizeUserSlug($un, array('anchor'=>true, 'gender'=>true)).' '. $this->ENGINE->time_ago($time);
			
		}
		
		return $a;
		
	}








	

		
		
	/*** Method for decoding encoded moderation activities ***/
	public function DecodeModerationActivity($activities){	
		
		// Extract user id if any and toggle to corresponding user name
		$activities = preg_replace_callback(
						"#(\%U(.*?)\%U)#s", 
		
						function($m){
							
							$id = $m[2]; 
							$n = $this->ACCOUNT->memberIdToggle($id);
							return $this->ACCOUNT->sanitizeUserSlug($n, array('anchor'=>true, 'urlText'=>($n? $n : ' user-'.$id)));
							
						}, $activities
						
					);
		
		// Extract topic id if any and toggle to corresponding topic name			
		$activities = preg_replace_callback(
						"#(\%T(.*?)\%T)#s", 
						
						function($m){ 
						
							$id = $m[2]; 
							$n = $this->SITE->topicIdToggle($id);
							return '<a href="'.$this->SITE->getThreadSlug($id).'" class="links">'.($n? $n : ' topic-'.$id).'</a>';					
							
						}, $activities
					
					);
		
		// Extract post id if any and toggle as desired			
		$activities = preg_replace_callback(
						"#(\%P(.*?)\%P)#s", 
						
						function($m){ 
						
							$id = $m[2]; 					
							return '<a href="/find-post/'.$id.'" class="links">'.$id.'</a>';
							
						}, $activities
					
					);
		
		// Extract section id if any and toggle to corresponding section name			
		$activities = preg_replace_callback(
						"#(\%S(.*?)\%S)#s", 
						
						function($m){ 
						
							$id = $m[2]; 
							$n = $this->SITE->sectionIdToggle($id);
							return $this->ENGINE->sanitize_slug($n, array('ret'=>'url'));
							
						}, $activities
					
					);
		
		return $activities;
		
	}




	 


	

	
		
	/*** Method for separating user id and state from a moderation action ***/
	public function decodeModerationStatus($encodedState){
		
		$statUid = trim(stristr($encodedState, ($sep="|")), $sep);
		$status = stristr($encodedState, $sep, true);
		$status = ($status || $statUid)? $status : $encodedState;	
		return array($status, $statUid);
		
	}







	
	


	
		
	/*** Method for fetching homepage topics ***/
	public function getEliteTopics(){	
		
		////////GENERATE FRONTPAGE TOPICS///
			
		//////PDO QUERY//////////////

		$sql = "SELECT COUNT(*) FROM topics WHERE FEATURE_TIME !=0 ";
		$valArr = array();
		$totalRecords = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
		/**********CREATE THE PAGINATION********/	
		$pageUrl = 'elite-tops/topics';
		$paginationArr = $this->SITE->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'perPage'=>30));
		$pagination = $paginationArr["pagination"];
		$totalPage = $paginationArr["totalPage"];
		$perPage = $paginationArr["perPage"];
		$startIndex = $paginationArr["startIndex"];
		$pageId = $paginationArr["pageId"];

		//////END OF PAGINATION///////
		
		if($totalRecords){

			///////////PDO QUERY///////
			
			$sql = $this->SITE->composeQuery(array('type' => 'for_topic', 'start' => $startIndex, 'stop' => $perPage, 'uniqueColumns' => '', 'filterCnd' => 'FEATURE_TIME !=0', 'orderBy' => 'PINNED_BY_MOD DESC, PIN_TIME DESC, FEATURE_TIME DESC'));
			$valArr = array();
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			list($randTopics) = $this->getTopicCustomized($stmt, true);


		}else
			$randTopics = '<span class="alert alert-danger">Sorry there are no Elite Topics Yet.</span>';
			
			
		return array("topics" => $randTopics, "pagination" => $pagination, "pageId" => $pageId, "totalPage" => $totalPage);

	}
	 
	 



	
	
	


	
		
	/*** Method for fetching custom topics ***/
	public function getTopicCustomized($stmtx, $featured=false, $sideBar=false, $showPreview=true, $header="", $forceInline=false){
		
		global $GLOBAL_page_self, $GLOBAL_mediaRootFav, $GLOBAL_mediaRootPost,$FA_user;	
				
		$mediaRootFav = $GLOBAL_mediaRootFav;
		$mediaRootPost = $GLOBAL_mediaRootPost;
		$staff = $this->SESS->isStaff();
		$inlineSep = ' | ';
		$rdr = $GLOBAL_page_self;	
		$fpFeat=$accTopicsBasic="";
			
		while($rowx = $this->DBM->fetchRow($stmtx)){
			
			$topicId = $rowx["ID"];
			$topicName = $rowx["TOPIC_NAME"];
			$sectionName = $rowx["SECTION_NAME"];
			$topicAuthorId = $rowx["TOPIC_AUTHOR_ID"];
			$topicAuthor = $this->ACCOUNT->memberIdToggle($topicAuthorId);
			$time = $rowx["TIME"];	
			list($pinned, $pinner) = $this->decodeModerationStatus($rowx["PINNED"]);
			$pinTime = $rowx["PIN_TIME"];			
			$topicSlug = $this->SITE->getThreadSlug($topicId);			

			$topicStatArr = $this->getThreadStatus($topicId);			
			$topicLocked = $topicStatArr["isLocked"];
			$topicLocker = $topicStatArr["locker"];

			list($floatedProtSticker, $unfloatedProtSticker) = $this->getThreadProtectionSticker($rowx["PROTECTION_LEVEL"]);			
			$pinIcon = $this->SITE->getPinIcon($pinner, $pinTime);
			$topicAuthor = $pinIcon.'<span class="sc-footer pull-r sv-txt-case dsk-platform-dpn-i">'.$this->ENGINE->build_lavatar($topicAuthor).$this->ACCOUNT->sanitizeUserSlug($topicAuthor, array('anchor'=>true, 'gender'=>true)).' '.$this->ENGINE->time_ago($time).'</span>';
			if($rowx[$K="FEATURE_NAME"] && $featured)
				$featName = strtolower($rowx[$K]);
			else
				$featName = $topicName;
			
			$accTopicsBasic .=  '<div class="'.($forceInline? 'inline' : 'fp-tops-h').'"><a href="'.$topicSlug.'"  class="links sc2" >'.$topicName.'</a></div>'.($forceInline? $inlineSep : '');
			
							
			//PDO QUERY///////////				

			$sql = $this->SITE->composeQuery(array('type' => 'for_post', 'start' => '', 'stop' => 1, 'postColsOnly' => true, 'uniqueColumns' => 'SUBSTR(MESSAGE, 1, 100) PREVIEW, SUBSTR(MESSAGE, 100, 1) MORE', 'filterCnd' => 'TOPIC_ID=?', 'orderBy' => 'TIME ASC'));
			$valArr = array($topicId);
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			$row = $this->DBM->fetchRow($stmt);
			list($postHidden, $PHU) = $this->decodeModerationStatus($row["HIDDEN"]);
			$vds = $row["VDS_WARNING"];
			$uploads = $row["UPLOADS"];
			$uploadsOriginalNames = $row["UPLOADS_ORIGINAL_NAMES"];
			$preview = $row["PREVIEW"];
			$message = $row["MESSAGE"];
			$moreDots = $row["MORE"]? '<b>....</b>' : '';
					
			$previewMssg = $showPreview? $preview.$moreDots : '';		
					
			$postUploads = $this->getPostUploads($uploads, array('fileOriginalNames'=>$uploadsOriginalNames, 'vds'=>$vds, 'type'=>'thumbnail', 'slug'=>$topicSlug, 'useBgImg'=>true, 'blankNone'=>true, 'hiddenPost'=>$postHidden));
			$previewFile = '<div class="pull-l">'.$postUploads.'</div>';
			$previewMssg = '<div class="align-l c-preview">'.$previewMssg.'</div>';
			$previewMssg = ''; ///COMMENT OUT TO ENABLE PREVIEW MESSAGE
			
			/////GET MODS CTRL/////
			$modsCtrlMetaArr = array(
					"tid" => $topicId, "tn" => $featName, "section" => $sectionName,
					"type" => "topic", "rdr" => $rdr, "fp" => $featured, "cmsStickerCombo" => false
			);

			list($postCms, $postCmsStickers, $topicCms, $topicCmsStickers) = $this->getModerationControls($modsCtrlMetaArr);
			
			$fpFeat .= '<div class="fp-tops-base t-'.$topicId.'-base clear"><a class="links fp-tops sc1" href="'.$topicSlug.'" >'.$previewFile.'<span class="fp-tops-h">'.$featName.'</span>'.$previewMssg.'</a><div class="base-lr-pad">'.$topicAuthor.'<div class="thread-prt-pin">'.$floatedProtSticker.'</div>'.($staff? '<div class="align-c clear">'.$topicCms.$topicCmsStickers.'</div>' : '').'</div></div>';
			
		}
		
		if($accTopicsBasic)
			$accTopicsBasic = $forceInline? '<div class="base-pad">'.rtrim($accTopicsBasic, $inlineSep).'</div>' : '<div class="side-widget widget"><div class="align-c panel panel-limex"><h2 class="panel-head page-title">'.$header.'</h2>'./*substr(*/'<div class="panel-body hr-dividers no-bg">'.$accTopicsBasic.'</div>'/*, 0, -19)*/.'</div></div>';	
		
		$topicsGenerated = $fpFeat? '<div class="evesx">'.$fpFeat.'</div>' : '';
		
		if($sideBar && $topicsGenerated){
			
			$topicsGenerated = '<div class="side-widget widget">
									<div class="base-rad">
										<h2 class="page-title bg-mine-1">'.$header.'</h2>
										'.$topicsGenerated.
									'</div>
								</div>';
								
		}
		
		
		return array($topicsGenerated, $accTopicsBasic);
		
	}



	

	


	
	
	


	
		
	/*** Method for fetching related topics ***/
	public function relatedTopics($sid, $topicId, $forceMobile=false, $forceInline=false, $onlyDsk=false, $limit=10){
		
		$accTopics=$keys="";

		$stop = $limit;

		if($topicId && $sid){

			if(($topicName = $this->SITE->topicIdToggle($topicId))){

				$topicName_arr = explode(" ", $topicName);
				$valArr=array();
				
				foreach($topicName_arr as $key){
					
					if(mb_strlen($key) > 3){

						$keys .= " TOPIC_NAME LIKE ? OR";
						$valArr[] = '%'.$key.'%';

					}
					
				}
				
				$keys = trim($keys, 'OR');
				$valArr[] = $sid;
				$valArr[] = $topicId;

				///////COUNT TOTAL SIMILAR TOPIC AND USE FOR RANDOMIZATION//////
				//PDO QUERY//////
			
				$sql =  $this->SITE->composeQuery(array('type' => 'for_topic', 'subType' => 'record_count', 'filterCnd' => '('.$keys.') AND SECTION_ID = ? AND ID !=?', 'exceptions' => true));
				$counted = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();

				$begin = mt_rand(0, $counted);

				///////RANDOMIZE USING TOTAL COUNTED RESULT//////			
				///PDO QUERY///////
			
				$sql =  $this->SITE->composeQuery(array('type' => 'for_topic', 'start' => $begin, 'stop' => $stop, 'uniqueColumns' => '', 'filterCnd' => '('.$keys.') AND SECTION_ID = ? AND topics.ID !=?', 'orderBy' => '', 'exceptions' => true));
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
						
				list($accTopics, $accTopicsForMob) = $this->getTopicCustomized($stmt, $ftd=false, $sbar=true, $showPrev=false, 'Related Topics', $forceInline);
				return array($accTopics, $accTopicsForMob);

				/*
				if($forceMobile)
					$accTopics = $accTopicsForMob;
				else
					$accTopics = '<div class="dsk-platform-dpn">
										'.$accTopics.'
									</div>
									'.((!$onlyDsk)? '<div class="mob-platform-dpn">'.$accTopicsForMob.'</div>' : '');
				*/
		
			}
		}

		
		return $accTopics;

	}



		
	

	


	
	
	


	
		
	/*** Method for fetching random topics ***/
	public function randomTopics($cid, $topicId, $limit=10, $trend=false, $ret_arr=false){		
		
		$accTopics="";
		$valArr = array();	
			
		if($trend){
				
			//PDO QUERY//////
		
			$sql = $this->SITE->composeQuery(array('type' => 'for_topic', 'subType' => 'trending', 'stop' => $limit, 'filterCnd' => '', 'exceptions' => true));					
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			
			list($accTopics, $accTopicsForMob) = $this->getTopicCustomized($stmt, $ftd=false, $sbar=true, $showPrev=false, 'Trending');
			
			return array($accTopics, $accTopicsForMob);
			
			/*$accTopics = '<div class="dsk-platform-dpn">
								'.$accTopics.'
							</div>
							<div class="mob-platform-dpn">'.$accTopicsForMob.'</div>';
			*/			
			
		}else{
			
			$cidSpecific = ($topicId && $cid);
			$header = $cidSpecific? 'You might also like' : 'Featured Hot';
			$sidsSubQry = "SELECT ID FROM sections WHERE CATEG_ID = ?";	

			if($cidSpecific){

				$typeCnd = 'SECTION_ID IN('.$sidsSubQry.') AND topics.ID != ?';
				$valArr = array($cid, $topicId);

			}else{

				$typeCnd = ' HOT != 0';

			}			
			
			
			$sql = $this->SITE->composeQuery(array('type' => 'for_topic', 'subType' => 'record_count', 'filterCnd' => $typeCnd, 'exceptions' => true));			
			$counted = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
			$begin = mt_rand(0, $counted);
			$stop = $limit;

			if($counted){
				
				//PDO QUERY//////
			
				$sql = $this->SITE->composeQuery(array('type' => 'for_topic', 'start' => $begin, 'stop' => $stop, 'uniqueColumns' => '', 'filterCnd' => $typeCnd, 'orderBy' => '', 'exceptions' => true));				
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);	
				
				list($accTopics, $accTopicsForMob) = $this->getTopicCustomized($stmt, $ftd=false, $sbar=true, $showPrev=false, $header);
					
				return array($accTopics, $accTopicsForMob);
				
				/*list($trendDsk, $trendMob) = $this->randomTopics("", "", 5, true, true);
				
				$accTopics = '<div class="dsk-platform-dpn">
									'.$trendDsk.$accTopics.'
								</div>
								<div class="mob-platform-dpn">'.$trendMob.$accTopicsForMob.'</div>';
				*/
			
			}
		
		}


		return $accTopics;

	}








	
		
	/*** Method for fetching open graph description text for a thread ***/
	public function getOgDescription($param, $type='thread'){
		
		if($param){
		
			$type = strtolower($type);
		
			if($type == 'thread'){
		
				$sql = "SELECT SUBSTR(MESSAGE, 1, 200) OG_DESC, SUBSTR(MESSAGE, 200, 1) MORE FROM posts WHERE TOPIC_ID=? ORDER BY TIME ASC LIMIT 1";
				$valArr = array($param);
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
				$row = $this->DBM->fetchRow($stmt);
				return ($this->ENGINE->filter_line_chars($row["OG_DESC"], ' ').($row["MORE"]? '...' : ''));
		
			}
		}
		
		return '';
	}

	 






	/*** Method for fetching moderation status update string ***/
	public function getModerationStateStr($doState, $undoState){
		
		$sessUid = $this->SESS->getUserId();
		$isAdmin = $this->SESS->isAdmin();

		!$doState? ($doState = 1) : '';
		!$undoState? ($undoState = 0) : '';

		$stateApply = $doState.'|'.$sessUid;
		//$stateReverse = $undoState.(ADMIN_OPENS? '|'.$sessUid : '');
		$stateReverse = ($isAdmin && !ADMIN_OPENS)? $undoState : $undoState.'|'.$sessUid;

		return array($stateApply, $stateReverse);


	}







	/*** Method for executing forum moderation actions ***/
	public function doForumModeration($metaArr = array()){

		$alertUser='';
		
		$modValuesMeta = MOD_METAS["values"];
		$modTopicValuesMeta = $modValuesMeta["topic"]	;
		$modPostValuesMeta = $modValuesMeta["post"]	;
		$modKeysMeta = MOD_METAS["keys"];

		$kArr = array("tid", "pid", "act", "tgt", "fn", "prt", "ntn", "nsn", "rsn", "fRet");

		list($tid, $pid, $action, $target, $featureName, $protection, $newTopicName, 
		 $newSectionName, $modReason, $fRet) = $this->ENGINE->get_assoc_arr($metaArr, $kArr);

		
		if($this->SESS->getUserId()){
			
			//GRAB QSTR PARAMS; tid, pid, action, target etc
			if(!$tid)
				$tid = (isset($_POST[$K = $modKeysMeta['topicId']])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : ''));

			if(!$pid) 
				$pid = (isset($_POST[$K = $modKeysMeta['postId']])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : ''));
			
			if(!$action) 
				$action = (isset($_POST[$K = $modKeysMeta['action']])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : '')); 

			if(!$target)
				$target = (isset($_POST[$K = $modKeysMeta['target']])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : ''));
			
			if(!$newTopicName)
				$newTopicName = (isset($_POST[$K = $modKeysMeta['newTopicName']])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : ''));			
			
			if(!$newSectionName)
				$newSectionName = (isset($_POST[$K = $modKeysMeta['newSectionName']])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : ''));			

			if(!$featureName)
				$featureName = (isset($_POST[$K = $modKeysMeta['featName']])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : ''));												 									
			
			if(!$protection)
				$protection = (isset($_POST[$K = $modKeysMeta['protection']])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : ''));									
			
			if(!$modReason)
				$modReason = (isset($_POST[$K = $modKeysMeta['reason']])? $_POST[$K] : (isset($_GET[$K])? $_GET[$K] : '')); 
				
			//FALLBACKS
			if(!$tid && $pid)
				$tid = $this->getPostDetail($pid, 'TOPIC_ID');

			//SANITIZE
			$tid = $this->ENGINE->sanitize_number($tid); //topic id
			$pid = $this->ENGINE->sanitize_number($pid); //post id
			$newTopicName = $this->ENGINE->sanitize_user_input($newTopicName);
			$newSectionName = $this->ENGINE->sanitize_user_input($newSectionName);
			$featureName = $this->ENGINE->sanitize_user_input($featureName);
			$protection = $this->ENGINE->sanitize_user_input($protection);
						
			$action = strtolower($action);
			$target = strtolower($target);

			//Don't bother to do anything unless there's a reference topic 
			if($topicName = $this->SITE->topicIdToggle($tid)){

				$rankDenialAlert = '<span class="alert alert-danger">'.MOD_METAS['msg']['rank'].'</span>';

				$topicLink = $this->SITE->getThreadSlug($tid);				
				$topStaff = $this->SESS->isTopStaff();
				$modReasonLen = mb_strlen($modReason);

				if(!$action)
					$alertUser = $encounteredError =  '<span class="alert alert-danger">Please select an action!</span>';
				
				elseif($modReason && ($modReasonLen < MIN_MOD_REASON || $modReasonLen > MAX_MOD_REASON))					
					$alertUser = $encounteredError = '<span class="alert alert-danger">Limit your reasons to a minimum of '.MIN_MOD_REASON.' and maximum of '.MAX_MOD_REASON.' characters!</span>';
				
				if(!isset($encounteredError)){

					/**************************************************
						HANDLE THREAD LEVEL MODERATION
					****************************************************/
					if($target == $modKeysMeta['topic']){										

						$nullNewParamAlert = '<span class="alert alert-danger">Oops! the new name was not entered; please try again</span>';

						///GET THREAD MODERATION AUTHORIZATIONS///
						$modsAuth = $this->authorizeModeration('', $tid, "");
						$canOpenThread = $canCloseThread = $modsAuth["closeThread"];
						$canMoveThread = $modsAuth["moveThread"];
						$canRenameThread = $modsAuth["renameThread"];
						$canProtectThread = $modsAuth["protectThread"];
						$canTagThreadHot = $modsAuth["tagHot"];
						$canFeatureThread = $modsAuth["featThread"];
						$canPinThread = $modsAuth["pinThread"];
						$canLockThread = $modsAuth["lockThread"];
						$canDeleteThread = $modsAuth["deleteThread"];

						$preAuthorized = ($canCloseThread || $canMoveThread || $canRenameThread || $canProtectThread
										|| $canTagThreadHot || $canFeatureThread || $canPinThread || $canLockThread 
										|| $canDeleteThread
									);

						if($preAuthorized){

							//////GET THE TOPIC DETAILS//////					
										
							$topicStatArr = $this->getThreadStatus($tid);							
							$openOrClose = $topicStatArr["ocs"];
							$openerOrCloser = $topicStatArr["ocsUid"];						
							$locked = $topicStatArr["isLocked"];
							$locker = $topicStatArr["locker"];
							$protected = $topicStatArr["isProtected"];
							$protecter = $topicStatArr["protecter"];
							$moved = $topicStatArr["isMoved"];
							$mover = $topicStatArr["mover"];
							$recycled = $topicStatArr["isRecycled"];
							$recycler = $topicStatArr["recycler"];
							$renamed = $topicStatArr["isRenamed"];
							$renamer = $topicStatArr["renamer"];
							$hot = $topicStatArr["isHot"];
							$hoter = $topicStatArr["hoter"];
							$pinned = $topicStatArr["isPinned"];
							$pinner = $topicStatArr["pinner"];
							$featured = $topicStatArr["isFeatured"];
							$featurer = $topicStatArr["featurer"];

							$tname = $topicStatArr["tn"];
							$newSid = $sid = $topicStatArr["sid"];
										
							$logActivity = false;

							switch($action){

								//******** FEATURE THREAD ********//
								case ($isFeatureAction = $modTopicValuesMeta['feature']): {
									
									if($canFeatureThread){									
										
										$stateApplyTxt = 'FEATURED'; $stateReverseTxt = 'UNFEATURED';
										$statusDbCol = 'FEATURED'; 
										$stateApplySubQry = "FEATURE_NAME=?, FEATURE_TIME=NOW()";
										$stateReverseSubQry = "FEATURE_NAME='', FEATURE_TIME=0";
										$subValArr = $featured? array() : array($featureName); // ignore feature name when unfeaturing												
										
										$metaArr = array(
											
											"tid" => $tid, "doTxt" => $stateApplyTxt, 
											"undoTxt" => $stateReverseTxt, "dbCol" => $statusDbCol, 
											"doSubQry" => $stateApplySubQry, "undoSubQry" => $stateReverseSubQry, 
											"currState" => $featured, "currStateUid" => $featurer, "valArr" => $subValArr

										);					

										list($alertUser, $logActivity, $doneActionTxt) = $this->doThreadActionByRank($metaArr);


									}else
										$alertUser = $encounteredError = $rankDenialAlert;

									break;

								}




								//******** PIN THREAD ********//
								case ($isPinAction = $modTopicValuesMeta['pin']): {
									
									if($canPinThread){
										
										$stateApplyTxt = 'PINNED'; $stateReverseTxt = 'UNPINNED';
										$statusDbCol = 'PINNED';					
										$stateApplySubQry = "PIN_TIME = NOW()".($topStaff? ", PINNED_BY_MOD = 1" : "");
										$stateReverseSubQry = "PIN_TIME = 0, PINNED_BY_MOD = 0";
										$subValArr = array();
										
										$metaArr = array(
											
											"tid" => $tid, "doTxt" => $stateApplyTxt, 
											"undoTxt" => $stateReverseTxt, "dbCol" => $statusDbCol, 
											"doSubQry" => $stateApplySubQry, "undoSubQry" => $stateReverseSubQry, 
											"currState" => $pinned, "currStateUid" => $pinner, "valArr" => $subValArr

										);					

										list($alertUser, $logActivity, $doneActionTxt) = $this->doThreadActionByRank($metaArr);


									}else
										$alertUser = $encounteredError = $rankDenialAlert;

									break;
									
								}



								//******** TAG THREAD HOT ********//
								case ($isTagHotAction = $modTopicValuesMeta['tagHot']): {
									
									if($canTagThreadHot){
										
										$stateApplyTxt = 'TAGGED HOT'; $stateReverseTxt = 'UNTAGGED HOT';
										$statusDbCol = 'HOT';
										$subValArr = array();
										
										$metaArr = array(
											
											"tid" => $tid, "doTxt" => $stateApplyTxt, 
											"undoTxt" => $stateReverseTxt, "dbCol" => $statusDbCol, 
											"currState" => $hot, "currStateUid" => $hoter, "valArr" => $subValArr

										);					

										list($alertUser, $logActivity, $doneActionTxt) = $this->doThreadActionByRank($metaArr);


									}else
										$alertUser = $encounteredError = $rankDenialAlert;

									break;
									
								}
								


								//******** LOCK THREAD ********//
								case ($isLockAction = $modTopicValuesMeta['lock']): {								
									
									if($canLockThread){
										
										$stateApplyTxt = 'LOCKED'; $stateReverseTxt = 'UNLOCKED';
										$statusDbCol = 'LOCKED';
										$subValArr = array();
										
										$metaArr = array(
											
											"tid" => $tid, "doTxt" => $stateApplyTxt, 
											"undoTxt" => $stateReverseTxt, "dbCol" => $statusDbCol, 
											"currState" => $locked, "currStateUid" => $locker, "valArr" => $subValArr

										);					

										list($alertUser, $logActivity, $doneActionTxt) = $this->doThreadActionByRank($metaArr);


									}else
										$alertUser = $encounteredError = $rankDenialAlert;

									break;

								}



								//******** PROTECT THREAD ********//
								case ($isProtectAction = $modTopicValuesMeta['protect']): {
									
									
									if($canProtectThread){

										if($protection){
											$stateApplyTxt = $stateReverseTxt = strtoupper($protection).' PROTECTED';
											$statusDbCol = 'PROTECTION_LEVEL';
											$subValArr = array();
											
											$metaArr = array(
												
												"tid" => $tid, "doTxt" => $stateApplyTxt, "hasDoStateOnly" => true,
												"undoTxt" => $stateReverseTxt, "dbCol" => $statusDbCol,
												"doState" => $protection, "undoState" => $protection,
												"currState" => $protected, "currStateUid" => $protecter, "valArr" => $subValArr

											);					

											list($alertUser, $logActivity, $doneActionTxt) = $this->doThreadActionByRank($metaArr);	

											$alertUser = '<span class="alert alert-success">You have successfully placed <a href="'.$topicLink.'" class="links">'.$topicName.'</a> under '.strtoupper($protection).' protection</span>';

										}else
											$alertUser = $encounteredError = '<span class="alert alert-danger">Please select a protection level </span>';

									}else
										$alertUser = $encounteredError = $rankDenialAlert;
									
									
									break;

								}




								//******** OPEN THREAD ********//
								case ($isOpenAction = $modTopicValuesMeta['open']): {
																	
									if($canOpenThread){
																									
										$stateApplyTxt = $stateReverseTxt = 'OPENED';
										$statusDbCol = 'CLOSED';
										$subValArr = array();
										
										$metaArr = array(
											
											"tid" => $tid, "doTxt" => $stateApplyTxt, "forceUndoState" => true,
											"undoTxt" => $stateReverseTxt, "dbCol" => $statusDbCol,
											"currState" => $openOrClose, "currStateUid" => $openerOrCloser, "valArr" => $subValArr

										);					

										list($alertUser, $logActivity, $doneActionTxt) = $this->doThreadActionByRank($metaArr);									



									}else
										$alertUser = $encounteredError = $rankDenialAlert;
									
								
									break;

								}


								//******** CLOSE THREAD ********//
								case ($isCloseAction = $modTopicValuesMeta['close']): {
																	
									if($canCloseThread){
												
										if($modReason){

											$stateApplyTxt = $stateReverseTxt = 'CLOSED';
											$statusDbCol = 'CLOSED';
											$subValArr = array();
											
											$metaArr = array(
												
												"tid" => $tid, "doTxt" => $stateApplyTxt, "hasDoStateOnly" => true,
												"undoTxt" => $stateReverseTxt, "dbCol" => $statusDbCol, 
												"currState" => $openOrClose, "currStateUid" => $openerOrCloser, "valArr" => $subValArr

											);					

											list($alertUser, $logActivity, $doneActionTxt) = $this->doThreadActionByRank($metaArr);									

										}else
											$alertUser = $encounteredError = '<span class="alert alert-danger">'.
											(
												$fRet? 'Unfortunately, you have to specify a reason when closing a thread!, make it short and precise as it would be displayed on the thread page itself. Example: Grown too large'
												: 												
												'please select a reason why you are closing the thread'
											).'</span>';

									}else
										$alertUser = $encounteredError = $rankDenialAlert;
									
								
									break;

								}





								//******** RENAME THREAD ********//
								case ($isRenameAction = $modTopicValuesMeta['rename']): {																

									if($canRenameThread){																		

										if($newTopicName){
											
											$stateApplyTxt = $stateReverseTxt = 'RENAMED';
											$statusDbCol = 'RENAMED';											
											$stateApplySubQry = $stateReverseSubQry = "TOPIC_NAME = ?";
											$subValArr = array($this->ENGINE->title_case($newTopicName));
											
											$metaArr = array(
												
												"tid" => $tid, "doTxt" => $stateApplyTxt, "hasDoStateOnly" => true, 
												"undoTxt" => $stateReverseTxt, "dbCol" => $statusDbCol, 
												"doSubQry" => $stateApplySubQry, "undoSubQry" => $stateReverseSubQry, 
												"currState" => $renamed, "currStateUid" => $renamer, "valArr" => $subValArr

											);					

											list($alertUser, $logActivity, $doneActionTxt) = $this->doThreadActionByRank($metaArr);

											$alertUser = '<span class="alert alert-success">You have successfully <span class="blue">'.$doneActionTxt.'</span> the topic: <span class="strike text-capitalize"><b>'.$tname.'</b></span> to <b class="yellow text-capitalize">'.$newTopicName.'</b> </span>';

										}else
											$alertUser = $encounteredError = $nullNewParamAlert;
										
									}else
										$alertUser = $encounteredError = $rankDenialAlert;
											
									break;

								}




								//******** MOVE THREAD ********//
								case ($isMoveAction = $modTopicValuesMeta['move']): {
									
									if($canMoveThread){									
																						
										if($newSectionName){
										
											$isThreadMoveAction = true;
											$newSid = $this->getSectionField($newSectionName, "ID");
											//$newSid = $this->$ENGINE->sanitize_number($newSectionName);
											//$newTargetSection = $this->sectionIdToggle($newSid);

											if($newSid){
													
												$stateApplyTxt = $stateReverseTxt = 'MOVED';
												$statusDbCol = 'MOVED';											
												$stateApplySubQry = $stateReverseSubQry = "SECTION_ID = ?, RECYCLED = ?";																						
												$subValArr = array($newSid, ($newSid == RECYCLEBIN_SID)? 1 : 0);
												
												$metaArr = array(
													
													"tid" => $tid, "doTxt" => $stateApplyTxt, "hasDoStateOnly" => true,
													"undoTxt" => $stateReverseTxt, "dbCol" => $statusDbCol, 
													"doSubQry" => $stateApplySubQry, "undoSubQry" => $stateReverseSubQry, 
													"currState" => $moved, "currStateUid" => $mover, "valArr" => $subValArr

												);					

												list($alertUser, $logActivity, $doneActionTxt) = $this->doThreadActionByRank($metaArr);

												$alertUser = '<span class="alert alert-success">You have successfully <span class="blue">'.$doneActionTxt.' </span> the topic: <span class="blue text-capitalize">'.$tname.'</span> to <b class="blue">'.$newSectionName.'</b> section</span>';
							
											}else
												$alertUser = $encounteredError = '<span class="alert alert-danger">Sorry the specified new target section ('.$newSectionName.') was not found</span>';


										}else
											$alertUser = $encounteredError = $nullNewParamAlert;	
																			
									}else
										$alertUser = $encounteredError = $rankDenialAlert;

																	
									break;

								}



								//******** DELETE THREAD ********//
								case ($isDeleteAction = $modTopicValuesMeta['delete']): {							
									
									$doneActionTxt = 'DELETED';
									
									if($canDeleteThread){

										if(!$locked){
											
											$limit = $this->DBM->getMaxRowPerSelect();
											
											/////DELETE UPLOADS RELATING TO THE TOPIC/////
											for($start = 0; ; $start += $limit){
												
												///////PDO QUERY//////									
												$sql = $SITE->composeQuery(array('type' => 'for_post', 'start' => $start, 'stop' => $limit, 'postColsOnly' => true, 'uniqueColumns' => '', 'filterCnd' => 'TOPIC_ID=?', 'orderBy' => ''));
												$valArr = array($tid);
												$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
												
												/////IMPORTANT INFINITE LOOP CONTROL ////
												if(!$this->DBM->getSelectCount())
													break;
										
												while($row = $this->DBM->fetchRow($stmt)){
									
													$pid = $row["ID"];						
													$uploadedFiles = $row["UPLOADS"];						
													////DELETE THE RELATED FILES FROM SERVER///////																																										
													$FORUM->postedFilesHandler(array('pid'=>$pid, 'files'=>$uploadedFiles, 'del'=>true));
																									
												}
																													
																					
											}	
											
											///DELETE THE POST FROM VOTES AND SHARES///////
											////////PDO QUERY///////																	
											
											$subQry = "SELECT ID FROM posts WHERE TOPIC_ID=?";
											$tmpArr = array('upvotes','downvotes','shares');

											foreach($tmpArr as $table){

												$sql =  "DELETE FROM ".$table." WHERE POST_ID IN (".$subQry.")";
												$valArr = array($tid);
												$stmt = $this->DBM->doSecuredQuery($sql, $valArr);

											}
											
											///DELETE ALL ASSOCIATED POSTS
											///////PDO QUERY//////							
											$sql =  "DELETE FROM posts WHERE TOPIC_ID = ?";
											$valArr = array($tid);
											$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
											
											///////DELETE FROM TOPIC FOLLOWS
											///////PDO QUERY//////
											$sql =   "DELETE FROM topic_follows WHERE TOPIC_ID = ?";
											$valArr = array($tid);
											$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
											
											///////DELETE FROM TOPIC SOCIAL SHARES
											///////PDO QUERY//////
											$sql =   "DELETE FROM topic_social_shares WHERE TOPIC_ID = ?";
											$valArr = array($tid);
											$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
											
											///////DELETE FROM TOPIC FOLLOWS
											///////PDO QUERY//////
											$sql =   "DELETE FROM topic_views WHERE TOPIC_ID = ?";
											$valArr = array($tid);
											$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
											
											///DELETE THE TOPIC
											///////PDO QUERY//////
											$sql =   "DELETE FROM topics WHERE ID = ? LIMIT 1";
											$valArr = array($tid);
											$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
											$logActivity = true;
											$alertUser = '<span class="alert alert-success">You have successfully <span class="blue">'.$doneActionTxt.'</span> the topic:  <span class="blue strike text-capitalize">'.$tname.' </span> </span>';
											
										}else
											$alertUser = $encounteredError = '<span class="alert alert-danger">'.MOD_METAS['msg']['threadLock'].'</span>';									
									}else
										$alertUser = $encounteredError = $rankDenialAlert;
										
									break;

								}

							}

													
												
							//LOG MODERATION ACTIVITY FOR TOPICS IF PERMITTED//
							if($logActivity){														
																			
								$ACT = $doneActionTxt.' %T'.$tid.'%T';
								
								/***
									When moving thread from one section to another, 
									we want to log the activity both ways (to & fro)
									first: we log (from) in the new section
									second: we log (to) in the old section
								***/
								if(isset($isThreadMoveAction)){
									
									$ACT2 = $ACT.' from %S'.$sid.'%S';
									$this->SITE->logActivity('t', $tid, $ACT2, '', $newSid);
									$ACT .= ' to %S'.$newSid.'%S';
									
								}
								
								$this->SITE->logActivity('t', $tid, $ACT, $modReason, $sid);

							}
								
							
							
						}else
							$alertUser = $encounteredError = $rankDenialAlert;


					}



					/**************************************************
						HANDLE POSTS LEVEL MODERATION
					****************************************************/

					elseif($target == $modKeysMeta['post']){
						
						///GET POST MODERATION AUTHORIZATIONS///
						$modsAuth = $this->authorizeModeration('', $tid, $pid);				
						$canHidePost = $modsAuth["hidePost"];
						$canPinPost = $modsAuth["pinPost"];
						$canLockPost = $modsAuth["lockPost"];
						$canDeletePost = $modsAuth["deletePost"];

						$preAuthorized = ($canHidePost || $canPinPost || $canLockPost || $canDeletePost);

						if($preAuthorized){

							$logActivity = false;					

							$statArr = $this->getPostStatus($pid);
							$isLockedPost = $statArr["isLocked"];
							$postLocker = $statArr["locker"];
							$isHiddenPost = $statArr["isHidden"];
							$postHider = $statArr["hider"];
							$isPinnedPost = $statArr["isPinned"];
							$postPinner = $statArr["pinner"];		
							
							

							switch($action){

								//******** PIN POST ********//
								case ($isPinAction = $modPostValuesMeta['pin']): {
									
									if($canPinPost){
										
										$stateApplyTxt = 'PINNED'; $stateReverseTxt = 'UNPINNED';
										$statusDbCol = 'PINNED'; 
										$stateApplySubQry = "PIN_TIME = NOW()".($topStaff? ", PINNED_BY_MOD = 1" : "");
										$stateReverseSubQry = "PIN_TIME = 0, PINNED_BY_MOD = 0";	
										$subValArr = array();

										$metaArr = array(
											
											"pid" => $pid, "tid" => $tid, "doTxt" => $stateApplyTxt, 
											"undoTxt" => $stateReverseTxt, "dbCol" => $statusDbCol, 
											"doSubQry" => $stateApplySubQry, "undoSubQry" => $stateReverseSubQry, 
											"currState" => $isPinnedPost, "currStateUid" => $postPinner, "valArr" => $subValArr

										);					

										list($alertUser, $logActivity, $doneActionTxt) = $this->doPostActionByRank($metaArr);

									}else
										$alertUser = $encounteredError = $rankDenialAlert;		
									
									break;

								}



								//******** LOCK POST ********//
								case ($isLockAction = $modPostValuesMeta['lock']): {
									
									
									if($canLockPost){

										$stateApplyTxt = 'LOCKED'; $stateReverseTxt = 'UNLOCKED';
										$statusDbCol = 'LOCKED';
										$subValArr = array();
										
										$metaArr = array(
											
											"pid" => $pid, "tid" => $tid, "doTxt" => $stateApplyTxt, 
											"undoTxt" => $stateReverseTxt, "dbCol" => $statusDbCol, 
											"currState" => $isLockedPost, "currStateUid" => $postLocker, "valArr" => $subValArr
											
										);

										list($alertUser, $logActivity, $doneActionTxt) = $this->doPostActionByRank($metaArr);

									}else
										$alertUser = $encounteredError = $rankDenialAlert;


									break;

								}


								
								//******** HIDE POST ********//
								case ($isHideAction = $modPostValuesMeta['hide']): {

									
									if($canHidePost){
										
										$stateApplyTxt = 'HIDDEN'; $stateReverseTxt = 'UNHIDDEN';
										$statusDbCol = 'HIDDEN';
										$subValArr = array();
										
										$metaArr = array(
											
											"pid" => $pid, "tid" => $tid, "doTxt" => $stateApplyTxt, 
											"undoTxt" => $stateReverseTxt, "dbCol" => $statusDbCol, 
											"currState" => $isHiddenPost, "currStateUid" => $postHider, "valArr" => $subValArr
											
										);

										list($alertUser, $logActivity, $doneActionTxt) = $this->doPostActionByRank($metaArr);

									}else
										$alertUser = $encounteredError = $rankDenialAlert;

									break;


								}



								
								//******** DELETE POST ********//
								case ($isDeleteAction = $modPostValuesMeta['delete']): {							
										
									if($canDeletePost){
										
										$doneActionTxt = 'DELETED';
									
										if(!$isLockedPost){
									
											/////PDO QUERY//////////
										
											$sql = $this->SITE->composeQuery(array('type' => 'for_post', 'start' => '', 'stop' => 1, 'postColsOnly' => true, 'uniqueColumns' => '', 'filterCnd' => 'posts.ID=? AND TOPIC_ID=?', 'orderBy' => ''));
											$valArr = array($pid, $tid);
											$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
											$row = $this->DBM->fetchRow($stmt);
													
											if(!empty($row)){
										
												$uploadedFiles = $row["UPLOADS"];
												
												////DELETE THE RELATED FILES FROM SERVER///////																																							
												$this->postedFilesHandler(array('pid'=>$pid, 'files'=>$uploadedFiles, 'del'=>true));
													
												///DELETE THE POST FROM VOTES AND SHARES///////								
												$tmpArr = array('upvotes', 'downvotes', 'shares');
												
												foreach($tmpArr as $table){
									
													///PDO QUERY///////
										
													$sql = "DELETE FROM ".$table." WHERE POST_ID=?";
													$valArr = array($pid);
													$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
												
												}
															
												///DELETE THE POST/////
												//////PDO QUERY///						
												$sql = "DELETE FROM posts WHERE ID=? AND TOPIC_ID=? LIMIT 1";
												$valArr = array($pid, $tid);
												$stmt = $this->DBM->doSecuredQuery($sql, $valArr);						  								
												$logActivity = true;
												$alertUser = '<span class="alert alert-success">You have successfully <span class="red strike">'.$doneActionTxt.'</span> the post</span>';
												
											}
									
										}else			
											$alertUser = $encounteredError = '<span class="alert alert-danger">'.MOD_METAS['msg']['postLock'].'</span>';												
									
									}else
										$alertUser = $encounteredError = $rankDenialAlert;		
									
									
									break;

								}


							}

							
							//LOG MODERATION ACTIVITY FOR POSTS IF PERMITTED//
							if($logActivity){
								
								$ACT = $doneActionTxt.' POST %P'.$pid.'%P';
								$this->SITE->logActivity('t', $tid, $ACT, $modReason);

							}

							


						}else
							$alertUser = $encounteredError = $rankDenialAlert;

					}



				}
					

			}else{

				$tgtTerm = (($target == $modKeysMeta['post'])? 'post' : 'topic'). ' id';

				$alertUser = $encounteredError = '<span class="alert alert-danger">The '.$tgtTerm.' you entered is not tied to a valid thread.. please verify that you have entered the correct '.$tgtTerm.' and try again </span>';

			}


		}

		if($fRet)
			return array($alertUser);

		elseif($this->ENGINE->is_ajax()){
				
			//$jsonArr['alerts'] = $alertUser? '<div class="" '._TIMED_FADE_OUT.' data-click-to-hide="true">'.$alertUser.'</div>' : '';	
			$jsonArr['res'] = $alertUser? '<div data-click-to-hide="true" '._TIMED_FADE_OUT.'>'.$alertUser.'</div>' : '';	
			$jsonArr['error'] = isset($encounteredError);
			$jsonArr['reload'] = !isset($encounteredError);
			echo json_encode($jsonArr);	
			exit;
				
		}else{
			
			header("Location:".$this->SITE->getRdr(false));
			exit();
			
		}				

	}







			
			


}






?>