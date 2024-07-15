<?php


//VERY IMPORTANT: remove the execution time limit
@set_time_limit(0);

class Daemon{

	/*********************
	NOTES:
	
	
	*********************/
	
	/*** Generic member variables ***/
	private $DBM;
	private $ACCOUNT;
	private $SESS;
	private $ENGINE;
	private $SITE;
	
	
	private $daemonTable = 'daemons';
	private $stopAllCmd = 'stop_all';
	private $stopCmd = 'stop';
	private $pauseCmd = 'pause';
	private $runCmd = 'run';
	
	
	
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

	
	
	/************************************************************************************/
	/************************************************************************************
									SITE METHODS
	/************************************************************************************
	/************************************************************************************/
		
	

	

		
	/*** Method for fetching daemon details from database ***/
	public function getDetails($daemonName, $field = 'ID'){
		
		//PDO QUERY/////////
		
		$sql = "SELECT * FROM ".$this->daemonTable." WHERE NAME = ? LIMIT 1";
		$valArr = array($daemonName);
		$row = $this->DBM->doSecuredQuery($sql, $valArr, 'chain')->fetchRow();
		
		return ($field? $row[$field] : $row);


	}
	

		
	/*** Method for updating daemon details in database ***/
	public function updateDetails($daemonName, $field, $fieldVal){
		
		//PDO QUERY/////////
		
		$sql = "UPDATE ".$this->daemonTable." SET ".$this->DBM->escapeField($field)." = ? WHERE NAME = ? LIMIT 1";
		$valArr = array($fieldVal, $daemonName);
		return $this->DBM->doSecuredQuery($sql, $valArr);


	}
	
		
	/*** Method for fetching minimum cycling interval (in Minutes) in daemon table ***/
	public function getMinCycleInterval(){
		
		//PDO QUERY/////////
		
		$sql = "SELECT MIN(CYCLE_INTERVAL) FROM ".$this->daemonTable;
		$valArr = array();
		return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();


	}
	

		
	/*** Method for cycling all daemons continuously ***/
	public function cycleAll(){
		
		//global $FORUM, $badgesAndReputations, $adsCampaign;
		
		$FORUM = new Forum();
		
		$badgesAndReputations = new BadgesAndReputations();

		$adsCampaign = new AdsCampaign();
		
		$maxPerFetch = $this->DBM->getMaxRowPerSelect();
			
		$globStop = false;
		
		//$sleep = ($this->getMinCycleInterval() * 60); //sleep at the minimum cycling time (in seconds) 
		
		while(!$globStop){
			
			//sleep($sleep); //We control cycling interval using cycling time specified in the database table
			
			for($i = 0; ; $i += $maxPerFetch){
				
				/******
				
					We want to query for daemons whose cycling interval is due and its command allows for a run 
				
				******/
				
				$sql = "SELECT * FROM ".$this->daemonTable." WHERE (((LAST_CYCLE_TIME + INTERVAL CYCLE_INTERVAL MINUTE) <= NOW()) AND COMMAND = ?) LIMIT ".$i.",".$maxPerFetch;
				$valArr = array($this->runCmd);
				$stmtx = $this->DBM->doSecuredQuery($sql, $valArr, true);
				
				/////IMPORTANT INFINITE LOOP CONTROL ////
				if(!$this->DBM->getSelectCount() || ($globCmd = strtolower(getAutoPilotState('GLOBAL_DAEMON_STOP_CMD')) ) == $this->stopAllCmd){
					
					switch($globCmd){
						
						case $this->stopAllCmd: 
							$globStop = true; //SET FLAG TO STOP ALL DAEMONS
							break;
						
					}
					
					break; //STOP ITERATION
					
				}
					
				while($rowx = $this->DBM->fetchRow($stmtx)){
					
					$daemonName = $rowx["NAME"];
					$cmd = strtolower($rowx["COMMAND"]);
					
					switch($cmd){
						
						case $this->stopCmd:
						case $this->pauseCmd: 
							$run = false;
							break;
							
						default:
							$run = true;
						
					}
					
					
					if($run){
						
						switch($daemonName){
							
							case 'DAILY_SITE_CORE_CRON': 
								
								//////TERMINATE ALL USERS THAT HAS SCHEDULED TERMINATION REQUEST///
								if(getAutoPilotState($col="SCHEDULED_ACCOUNTS_TERMINATION")){
									
									$this->ACCOUNT->terminateScheduledAccounts();

								}

								//EXPIRE ALL AUTHENTICATION CODES OLDER THAN THEIR LIFE SPAN/////
								$this->SITE->expireAuthentication();
									
								//INVALIDATE EXPIRED USER ACCOUNT TEMP PASSWORD/////
								$this->ACCOUNT->expireUserTempPassword();
									
								//RUN SESSION GARBAGE COLLECTION TO CLEANUP EXPIRED SESSION METAS/////
								$this->SESS->collectGarbage();
								
								break;
								
								
							case 'SITE_TRAFFIC_CRON': 
								
								$FORUM->collectThreadViews();
								$this->SITE->decollectSiteTraffic();
								
								break;
								
								
							case 'NEWS_LETTER_DISPATCH_CRON': 
								
								if(getAutoPilotState($col="NEWS_LETTER_DISPATCH")){
									
									$this->SITE->newsLetterDispatcher();
									
								}
								
								break;
								
								
							case 'DAILY_BADGE_AWARD_CRON': 
								
								$badgesAndReputations->awardDailyBadges();
								
								break;
								
								
							case 'PERIODIC_BADGE_AWARD_CRON': 
								
								if(getAutoPilotState($col="CRON_BADGE_AWARD")){
									
									$badgesAndReputations->awardPeriodicBadges();
									$badgesAndReputations->reputationAwardCron();
									
								}
								
								break;
								
								
							case 'YEAR_END_BADGE_AWARD_CRON': 
								
								$badgesAndReputations->awardYearEndBadges();
								
								break;
								
							
							case 'DAILY_ADS_CRON': 
								
								
								////BILL BEFORE PLACING NEW ADS///////
								if(getAutoPilotState($col="AD_BILLING")){
									
									$adsCampaign->adsBillingCron();
									
								}

								
								/////PLACE NEW ADS INTO VACCANT SLOTS//////////
								if(getAutoPilotState($col="AD_PLACEMENT")){

									$adsCampaign->adsPlacementCron();

								}


								/////NOTIFY USERS WITH ACTIVE CAMPAIGNS AND NOTIFICATION STATUS WHEN THEY ARE RUNNING LOW ON CREDIT////////
								$adsCampaign->lowCreditNotificationDispatchCron();

								/////PLACE SECTION AD RATE ON PREMIUM 
								$adsCampaign->placeSectionsOnAdPremiumRate();


								/////DO AD TABLE CLEAN UP//////////
								if(getAutoPilotState($col="AD_CLEAN_UP")){
									
									//$adsCampaign->adTableCleanUp();

								}
								
								break;
							
							
								
								
							case 'FLUTTERWAVE_HOOK_RESEND': 
								
								$flutterwavePaymentGateway = new FlutterwavePaymentGateway();
								$flutterwavePaymentGateway->TrxHookResendDaemon();
								
								break;
								
						}

						//Update last cycle time accordingly
						$sql = "UPDATE ".$this->daemonTable." SET LAST_CYCLE_TIME = NOW() WHERE NAME = ? LIMIT 1";
						$valArr = array('DAILY_ADS_CRON');
						$this->DBM->doSecuredQuery($sql, $valArr);
		
					}
					
				}
				
			}
			
		}


	}

	
	
	
	
}





?>