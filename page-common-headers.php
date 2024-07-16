<?php

////CONSTANTS/////////
###DOC
define("IS_DEV", (isset($_SERVER[$K="HTTP_HOST"]) && preg_match('#\.test$#', $_SERVER[$K])));
define("DOC_ROOT", __DIR__);
define("ADMIN", 'ADMIN');
define("MODERATOR", 'MODERATOR');
define("MEMBER", 'MEMBER');
define("GUEST", 'GUEST');
define("ASSUMED_INTERVAL_LATEST", ' 2 MONTH ');///ASSUMED LATEST//////
define("ASSET_PREFIX", 'static/assets.php/v7/');
define("L_WIDGET", 'left-widget col-lg-w-7-pull col-md-w-6-pull');
define("R_WIDGET", 'right-widget col-lg-w-3-pull col-md-w-4-pull');
define("L_WIDGET_POST_CTRL", 'left-widget col-lg-w-6-pull col-sm-w-7-pull');
define("R_WIDGET_POST_CTRL", 'right-widget col-lg-w-4-pull col-sm-w-3-pull');
define("BD_WISH_PM_SUBJECT", ' )n- -)vBirthday Wishes )n- -)v');

/****** TURN ON/OFF ERRORS DEPENDING ON SITE MODE(PRODUCTION OR DEVELOPMENT) ******/
error_reporting(E_ALL);
ini_set('log_errors', true);
ini_set('display_errors', IS_DEV);

///AUTOLOAD NECESSARY CLASSES

$baseDir = DOC_ROOT.'/'.ASSET_PREFIX;

require_once($baseDir.'classes/Engine.class.php');
require_once('page-common-header-functions.php');

$autoLoadlookUpBaseDirArr = array(

	$baseDir.'classes/',
	$baseDir.'plugins/qr-code-4.5.2/src/',
	$baseDir.'plugins/PHPMailer/src/',

);

Engine::autoload_classes($autoLoadlookUpBaseDirArr);


/************************************************************************************************************/
/********************************** INSTATIATE NECESSARY CLASSES ********************************************/
/************************************************************************************************************/

/*************************
INSTANTIATE/START ERROR/EXCEPTION HANDLER
*************************/

$exceptionHandler = new ExceptionHandler(IS_DEV);


/*************************
INSTANTIATE/START ENGINE
*************************/

$ENGINE = new Engine();

//SET TIME ZONE///
$ENGINE->set_time_zone();

			
			

/*************************
INSTANTIATE/START DATABASE MANAGER
*************************/
define("DB_NAME", "eurotech2");
define("DB_HOST", "eurotech.test");
define("DB_USERNAME", "root");
define("DB_PWD", "");

$dbm = new DataBaseManager("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USERNAME, DB_PWD);

//$dbm = new DataBaseManager("mysql:host=localhost;dbname=id204967_techcity", "id204967_techcity", "techcity+adimabua02");
//$dbm = new DataBaseManager("mysql:host=localhost;dbname=provid33_eurotech", "provid33_pwealth", "PFwealth+P&A_20PFin90");
//$dbm = new DataBaseManager("mysql:host=sql309.unaux.com;dbname=unaux_23099545_eurodesigns", "unaux_23099545", "EDAdimabua02");
//$dbm = new DataBaseManager("mysql:host=sql213.unaux.com;dbname=unaux_23282028_eurotechs", "unaux_23282028", "ZoroS2019");
//$dbm = new DataBaseManager("mysql:host=sql100.unaux.com;dbname=unaux_23807958_wkc", "unaux_23807958", "EartH2019");
//$dbm = new DataBaseManager("mysql:host=sql106.unaux.com;dbname=unaux_24204462_gkc", "unaux_24204462", "ZoroS2019");
//$dbm = new DataBaseManager("mysql:host=sql208.unaux.com;dbname=unaux_26779900_halls", "unaux_26779900", "ZoroS2020");


//////////DEFINE CONSTANTS THAT REQUIRES DATABASE QUERY AFTER STARTING A DATASE CONNECTION//////

/////GET AUTO PILOTED CONSTANTS PARAMS
$pilotArr = array(
		
		"BANK_DETAILS", "MIN_AD_DEPOSIT", "BANNER_TYPE2_CHARGE", "BANNER_TYPE3_CHARGE", "AD_PREMIUM_PURSE_TOPUP_RATE",
		
		"AD_PREMIUM_ELIGIBILITY_AMOUNT", "INTL_MEANS_OF_PAY_URL", "TEXT_AD_DISCOUNT_CHARGE", "TEXT_AD_CHARGE",
		
		"LOCKOUT_MODERATORS", "SHOW_SEASON_CARDS", "DOC_EXTENSION", "USE_USER_URL_EXTENSION",
		
		"TAKE_DOWN_SITE", "THREAD_SLUG_CONSTANT", "THREAD_SLUG_STYLE", "UIBG_ADD_HOLE_COUNT", "SITE_SLOGAN",
		
		"SITE_MAIL_ADDRESSES", "SITE_SOCIAL_URLS", "SITE_HOT_LINES", "API_TEST_MODE", 
		"PSTK_API_KEYS", "FLWV_API_KEYS",  "MNFY_API_KEYS" 
		
	);
	
list($bankDetails, $minAdDeposit, $bannerT2Charge, $bannerT3Charge, $adPrmPurseTopupRate, $adPrmEligAmt, $intlMeansOfPayUrl, $textAdDiscountCharge,
$textAdCharge, $modsLockOut, $showSeasonCards, $docExtension, $userUrlExtension, $takeDownSite, $threadSlugConstant, $threadSlugStyle,
$uiBgAddHoleCount, $siteSlogan, $siteMailAddresses, $siteSocialUrls, $siteHotLines, $apiTestMode,
$paystackApiKeys, $flutterwaveApiKeys, $monnifyApiKeys) = getAutoPilotState($pilotArr);


define('RSRC_PLUGS', array(
	
	'qr' => 'link.qr.plugs',
	
));

$paystackApiKeyArr = $ENGINE->str_to_assoc($paystackApiKeys);
$flutterwaveApiKeyArr = $ENGINE->str_to_assoc($flutterwaveApiKeys);
$monnifyApiKeyArr = $ENGINE->str_to_assoc($monnifyApiKeys);

define('SITE_ACCESS_MIN_AGE', 13);
define('SITE_SLOGAN', $siteSlogan);
define('TAKE_DOWN_SITE', $takeDownSite);
define('DOC_EXTENSION', $docExtension);
define('API_TEST_MODE', $apiTestMode);
define('PSTK_API_KEYS', $paystackApiKeyArr);
define('FLWV_API_KEYS', $flutterwaveApiKeyArr);
define('MNFY_API_KEYS', $monnifyApiKeyArr);
define('ACCOUNT_TERMINATION_WAIT_DAYS', 2);
define('ACCOUNT_TERMINATION_WAIT_HOURS', (ACCOUNT_TERMINATION_WAIT_DAYS * 24).' hours');
define('USE_USER_URL_EXTENSION', $userUrlExtension);
define('UIBG_COUNTS', $uiBgAddHoleCount);
define("TMP_PWD_LIFE_MINS", 20);

///GET MODERATORS LOCKOUT STATUS///
define("MODS_LOCKOUT", $modsLockOut);///USED IN getUserPrivilege AND authorizeMods FNCT/////

#############################################################
			
			
			

/*************************
INSTANTIATE IntHasher
*************************/

$INT_HASHER = new IntHasher('EAPP_FORUM', 11, '', true);

			
			

/***************************************
INSTANTIATE/START SESSION BINDED ACCOUNT
***************************************/

$ACCOUNT = new Account();

//7 GET SESSION USERNAME AND USERID///
$GLOBAL_username = $ACCOUNT->SESS->getUsername();
$GLOBAL_userId = $ACCOUNT->SESS->getUserId();

//8 GET SESSION MESSAGES IF DESIRED///
$GLOBAL_timeoutMessage = $ACCOUNT->SESS->getMessage();
	
	
/*************************
INSTANTIATE/START SITE
*************************/

$SITE = new Site();

		
			
/*************************
INSTANTIATE/START FORUM
*************************/

$FORUM = new Forum();

			

			
			
/*************************
INSTANTIATE/START BADGES AND REPUTATIONS
*************************/

$badgesAndReputations = new BadgesAndReputations();

			
			
/*************************
INSTANTIATE/START DAEMONS
*************************/

$DAEMON = new Daemon();


/************************************************************************************************************/
/************************************************************************************************************/
/************************************************************************************************************/

list($adminOpensCtrlState, $adminOpensCtrlBtn) = $SITE->sessCtrlProps('admin-opens');
define("ADMIN_OPENS", $adminOpensCtrlState);///USED IN OVERRIDING MODERATION OPENING ACTIONS BY ADMIN/////

list($GLOBAL_siteDomain, $GLOBAL_npDomain) = $ENGINE->get_domain(true);
$GLOBAL_siteName = $SITE->getSiteName();

list($siteHotLineStrArr, $siteHotLineUrlArr) = $ENGINE->str_to_assoc($siteHotLines, true, true);
list($siteMailAddressStrArr, $siteMailAddressUrlArr) = $ENGINE->str_to_assoc($siteMailAddresses, true);
list($siteSocialUrlStrArr, $siteSocialUrlArr) = $ENGINE->str_to_assoc($siteSocialUrls, true);

define("SITE_HOT_LINES", implode(', ', $siteHotLineUrlArr));
define("CONTACT_US_EMAIL_ADDR", $siteMailAddressUrlArr["support"]);
define("CONTACT_MODS_EMAIL_ADDR", $siteMailAddressUrlArr["moderators"]);
define("CAMPAIGN_EVID_EMAIL_ADDR", $siteMailAddressUrlArr["campaign_evidence"]);
define("DMCA_EMAIL_ADDR", $siteMailAddressUrlArr["dmca"]);
define("SITE_FACEBOOK_URL_STR", $siteSocialUrlStrArr["facebook"]);
define("SITE_TWITTER_URL_STR", $siteSocialUrlStrArr["twitter"]);
define("SITE_INSTAGRAM_URL_STR", $siteSocialUrlStrArr["instagram"]);
define("SITE_WHATSAPP_URL_STR", $siteSocialUrlStrArr["whatsapp"]);
define("SITE_TELEGRAM_URL_STR", $siteSocialUrlStrArr["telegram"]);
define("SITE_FACEBOOK_URL", $siteSocialUrlArr["facebook"]);
define("SITE_TWITTER_URL", $siteSocialUrlArr["twitter"]);
define("SITE_INSTAGRAM_URL", $siteSocialUrlArr["instagram"]);
define("SITE_WHATSAPP_URL", $siteSocialUrlArr["whatsapp"]);
define("SITE_TELEGRAM_URL", $siteSocialUrlArr["telegram"]);

define("THREAD_SLUG_CONSTANT", $threadSlugConstant);
define("THREAD_SLUG_STYLE", trim($threadSlugStyle));
		
$intlMeansOfPayUrl = $SITE->idCsvToNameString($intlMeansOfPayUrl, 'xurl', ' / ');

$FA_arr = array("fa-user", "fa-sign-in-alt", "fa-sign-out-alt", "fa-envelope", "fa-home", "fa-retweet", "fa-tasks",
					"fa-info-circle", "fa-wrench", "fa-inbox", "fa-cog", "fa-search", "far fa-hdd", "fa-book", "fa-trash",
					"fa-times", "fa-tags", "fa-reply", "fa-phone", "fa-mobile-alt", "fa-desktop", "far fa-thumbs-up", "far fa-thumbs-down","fa-lock",
					"fa-unlock", "fa-file", "fa-shield-alt", "fa-level-up-alt", "fa-address-book", "fa-address-card");
list($FA_user, $FA_signIn, $FA_signOut, $FA_envelope, $FA_home, $FA_retweet, $FA_tasks, $FA_infoCircle, $FA_wrench, $FA_inbox,
$FA_cog, $FA_search, $FA_hdd, $FA_book, $FA_trash, $FA_times, $FA_tags, $FA_reply, $FA_phone, $FA_mobile, $FA_desktop,
$FA_thumbsUp, $FA_thumbsDown, $FA_lock, $FA_unlock, $FA_file, $FA_shield, $FA_export, $FA_contact, $FA_about) = $SITE->getFA($FA_arr, array(""));


////CONSTANTS/////////
###INLINE CSS
define('BRONZE_COLOR', '#cd7f32');
define('SILVER_COLOR', '#c0c0c0');
define('GOLD_COLOR', '#d4af37');
define('BRONZE_INLINE_STYLE', 'style="background-color:'.BRONZE_COLOR.';"');
define('SILVER_INLINE_STYLE', 'style="background-color:'.SILVER_COLOR.';"');
define('GOLD_INLINE_STYLE', 'style="background-color:'.GOLD_COLOR.';"');

###BADGE CLASSES IN DB
define('GOLD_BADGE_CLASS', 3);
define('SILVER_BADGE_CLASS', 2);
define('BRONZE_BADGE_CLASS', 1);
define('GENERIC_BADGE_CATEGORY', 1);
define('TAG_BADGE_CATEGORY', 2);
define('MAX_THREAD_TAG', 10);

###SITE NOTIFICATION(POP OUT) TARGETS
define('POP_FOR_ALL', 'all');
define('POP_FOR_MODERATORS', 'moderators');
define('POP_FOR_BIRTHDAYS', 'birthdays');
define('POP_FOR_SEASONS', 'seasons');
define('POP_FOR_VALENTINES', 'valentines');

###POST REPORT FLAGS
define('REPORT_FLAGS_ARR', array('Spam', 'Fake Content', 'Child Abuse', 'Violence', 'Abusive', 'Offensive', 'Pornography', 'Needs Attention'));

###THREAD PROTECTION LEVELS
define('THREAD_PROTECTION_LEVEL_0', 'None');
define('THREAD_PROTECTION_LEVEL_1', 'LV-1 Standard');
define('THREAD_PROTECTION_LEVEL_2', 'LV-2 Classic');
define('THREAD_PROTECTION_LEVEL_3', 'LV-3 Premium');
define('THREAD_PROTECTION_LEVEL_4', 'LV-4 Elite');
define('THREAD_PROTECTION_LEVEL_5', 'LV-5 Lord');
define('THREAD_PROTECTION_LEVEL_6', 'LV-6 Master');
define('THREAD_PROTECTION_LEVEL_7', 'LV-7 Royal');
define('THREAD_PROTECTION_LEVEL_8', 'LV-8 Ultimate');


/**************BEGIN LIVE TABLE CONSTANTS*************/
###DB-MANAGER
define("GRPSEP", "|");///DATA GROUP PREFERRED SEPERATOR///////
define("DB_GPREF", "|");///DB DATA GROUP PREFIX///////
define("DB_GSUF", ",");///DB DATA GROUP SUFFIX///////
define("DB_TPREF", "!");///DB TIME PREFIX///////
define("BT_TAG_ANCHOR_DELIMETER", "__BT_TAG_ANCHOR__");
define($K="ADMIN_EDIT_SCAT", $K);
define($K="ADMIN_EDIT_ALL_SCAT", $K);
define($K="ADMIN_DEL_SCAT", $K);
define($K="ADMIN_EDIT_CAT", $K);
define($K="ADMIN_EDIT_ALL_CAT", $K);
define($K="ADMIN_DEL_CAT", $K);
define($K="ADMIN_EDIT_POP", $K);
define($K="ADMIN_EDIT_ALL_POP", $K);
define($K="ADMIN_DEL_POP", $K);
define($K="ADMIN_EDIT_BADGE", $K);
define($K="ADMIN_EDIT_ALL_BADGE", $K);
define($K="ADMIN_DEL_BADGE", $K);
define($K="ADMIN_EDIT_AUTO_PILOT", $K);
define($K="ADMIN_EDIT_ALL_AUTO_PILOT", $K);
define($K="ADMIN_DEL_AUTO_PILOT", $K);
define($K="ADMIN_EDIT_UI_BG", $K);
define($K="ADMIN_EDIT_ALL_UI_BG", $K);
define($K="ADMIN_DEL_UI_BG", $K);
define($K="ADMIN_EDIT_EMOTICONS", $K);
define($K="ADMIN_EDIT_ALL_EMOTICONS", $K);
define($K="ADMIN_DEL_EMOTICONS", $K);
define($K="ADMIN_EDIT_DAEMONS", $K);
define($K="ADMIN_EDIT_ALL_DAEMONS", $K);
define($K="ADMIN_DEL_DAEMONS", $K);
define($K="ADMIN_EDIT_COUNTRIES", $K);
define($K="ADMIN_EDIT_ALL_COUNTRIES", $K);
define($K="ADMIN_DEL_COUNTRIES", $K);
		
###SEARCH USER
define($K="ADMIN_EDIT_USER", $K);
define($K="ADMIN_EDIT_ALL_USER", $K);
define($K="ADMIN_DEL_USER", $K);

/**************END LIVE TABLE CONSTANTS*************/

##MOD ACTION
define("MOD_METAS",
	
	array( 
									
		'keys' => array(
				'action' => 'act', 'categoryId' => 'cid', 'sectionId' => 'sid', 'topicId' => 'tid', 
				'postId' => 'pid', 'protection' => 'prt', 'featName' => 'fn', 'newTopicName' => 'ntn', 
				'newSectionName' => 'nsn', 'reason' => 'rsn', 'target' => 'tgt',
				'topicTgtTitle' => ($topicTgtTitle = 'topic'), 'postTgtTitle' => ($postTgtTitle = 'post'),
				'post' => 'p', 'topic' => 't', ($moveK =  "move") => $moveK,  ($renameK =  "rename") => $renameK,
				($protectK =  "protect") => $protectK, ($tagHotK =  "tagHot") => $tagHotK, 
				($featureK =  "feature") => $featureK, ($openK =  "open") => $openK, ($closeK =  "close") => $closeK, 
				($pinK =  "pin") => $pinK, ($deleteK =  "delete") => $deleteK, ($lockK =  "lock") => $lockK,
				($hideK =  "hide") => $hideK, 'topicManager' => 'manage_topics', 'postManager' => 'manage_posts'
			),
		
		'values' =>
			array(
				$topicTgtTitle => array(
					$moveK => 'mov', $renameK => 'ren', $protectK => 'pro', $tagHotK => 'hot', 
					$featureK => 'fea', $openK => 'open', $closeK => 'close', $pinK => 't-pin', $deleteK => 't-del', 
					$lockK => 't-loc'
				),

				$postTgtTitle => array(
					$lockK => 'p-loc', $hideK => 'hide',  $pinK => 'p-pin', $deleteK => 'p-del', 
				)
			),

		'msg' => array(
				'rank' => 'Sorry!, the current actor outranks you... Please try again when you have earned more reputation!',
				'threadLock' => 'Sorry this thread is locked!',
				'postLock' => 'Sorry this post is locked!'				
			)
		
	)	
					
);
###MOD LEVEL
define("FLAG_POST", 1);
define("TAG_HOT", 2);
define("POST_IN_PROTECTED", 3);
define("RENAME_THREAD", 4);
define("MOVE_THREAD", 5);
define("LOCK_POST", 6);
define("PIN_POST", LOCK_POST.'.1');
define("HIDE_POST", 7);
define("CLOSE_THREAD", 8);
define("PROTECT_THREAD", 9);
define("MOD", 10);
define("SUPER_MOD", 11);
define("FEAT_THREAD", SUPER_MOD);
define("ULTIMATE_MOD", 12);
define("DELETE_POST", ULTIMATE_MOD.'.1');
define("DELETE_THREAD", ULTIMATE_MOD.'.1');
define("VOTE_UP", 13);
define("VOTE_DOWN", 14);
define("LOCK_THREAD", 15);
define("PIN_THREAD", LOCK_THREAD.'.1');
		
##REPUTATION LEVEL
define("FLAG_POST_REP", 15);
define("VOTE_UP_REP", 15);
define("VOTE_DOWN_REP", 130);
define("TAG_HOT_REP", 500);
define("POST_IN_PROTECTED_REP", 1000);
define("RENAME_THREAD_REP", 3000);
define("MOVE_THREAD_REP", 5000);
define("LOCK_POST_REP", 7000);
define("PIN_POST_REP", LOCK_POST_REP);
define("HIDE_POST_REP", 8000);
define("CLOSE_THREAD_REP", 10000);
define("PROTECT_THREAD_REP", 15000);
define("LOCK_THREAD_REP", 18000);
define("PIN_THREAD_REP", LOCK_THREAD_REP);
define("MOD_REP", 20000);
define("SUPER_MOD_REP", 30000);
define("FEAT_THREAD_REP", SUPER_MOD_REP);
define("ULTIMATE_MOD_REP", 50000);
define("DELETE_POST_REP", ULTIMATE_MOD_REP);
define("DELETE_THREAD_REP", ULTIMATE_MOD_REP);
		
##REPUTATION REWARD
define("POST_UPVOTED_REP", 5);
define("POST_DOWNVOTED_REP", 5);
define("POST_SHARED_REP", 5);
define("POST_UNSHARED_REP", 5);
define("POST_SOCIAL_SHARED_REP", 2);
define("THREAD_SOCIAL_SHARED_REP", 2);
define("THREAD_GAIN_FOLLOWS_REP", 6);
define("THREAD_LOSE_FOLLOWS_REP", 6);
define("MEMBER_GAIN_FOLLOWS_REP", 6);
define("MEMBER_LOSE_FOLLOWS_REP", 6);
define("FOUR_POST_FLAGS_REP", 100);
define("MAX_POST_FLAGS", 5);
define("N_POST_FLAG", 4);
		
##THREAD PROTECTION ACCESS LEVEL
define("STD_LV_ACCESS_REP", 100);
define("CLS_LV_ACCESS_REP", 200);
define("PRM_LV_ACCESS_REP", 300);
define("ELT_LV_ACCESS_REP", 500);
define("LRD_LV_ACCESS_REP", 1000);
define("MST_LV_ACCESS_REP", 2000);
define("ROY_LV_ACCESS_REP", 5000);
define("ULT_LV_ACCESS_REP", 10000);
		
###PROFILE
define("MAX_USERNAME", 30);
define("MIN_USERNAME", 4);
define("MAX_PWD", 50);
define("MIN_PWD", 6);
define("MAX_PHONE", 20);
define("MIN_PHONE", 11);
define("MAX_FN", 80);
define("MAX_LN", 80);
define("MAX_ABOUT_YOU", 200);
define("MAX_SIGNATURE", 50);
define("MAX_EXT_URL", 80);
define("MIN_MOD_REASON", 10);
define("MAX_MOD_REASON", 30);
define("USERNAME_PATTERN", '/^(?=[A-Za-z0-9_-]*[A-Za-z])[A-Za-z0-9_-]{'.MIN_USERNAME.','.MAX_USERNAME.'}$/');
define("PWD_PATTERN", '/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9])\S{'.MIN_PWD.','.MAX_PWD.'}$/');
define("MARITAL_ARR", array("Single","Married","Seperated","Divorced","Widowed"));
		
###CROSS SITE
defineSidConstants();
define("DEFAULT_PAGINATION_COUNT", 20);
define("MAX_SECTIONS_MODERATABLE", 3);
define("COMPOSER_ID", 'composer');
define("EMOJIS_WIDGET_CMN_CLASS", ' has-bbc has-slide-show ');
define("COMPOSER_WRAPPER_CLASS", ' has-composer has-uibg has-file-preview '.EMOJIS_WIDGET_CMN_CLASS);
		
###AD
define("ONE_KB_BYTE", 1024);
define("MAX_AD_MATRIX", "16");
define("AD_MATRIX", MAX_AD_MATRIX." x 1");
define("BANNER_1_W", 360);
define("BANNER_2_W", 300);
define("BANNER_3_W", 300);
define("BANNER_1_H", 114);
define("BANNER_2_H", 250);
define("BANNER_3_H", 600);
define("BANNER_1_DIMENSION", BANNER_1_W.' x '.BANNER_1_H);
define("BANNER_2_DIMENSION", BANNER_2_W.' x '.BANNER_2_H);
define("BANNER_3_DIMENSION", BANNER_3_W.' x '.BANNER_3_H);
define("BANNER_1_S", 20480);/**20kb**/
define("BANNER_2_S", 40960);/**40kb**/
define("BANNER_3_S", 51200);/**50kb**/
define("AD_TYPE1_SLOTS", 6);
define("AD_TYPE2_SLOTS", 2);
define("AD_TYPE3_SLOTS", 2);
define("AD_TYPE4_SLOTS", 6);
define("BANNER_EXT_ARR", array("jpg","jpeg","png", "gif"));
define("BANNER_EXT_FMT", ' Format/Extension: <b>'.implode(', ', BANNER_EXT_ARR).'</b>');
define("MAX_TXT_AD_CONTENT", 200);
define("MAX_LANDPAGE", 200);
		
#############EDITED TOGETHER##########
define("AD_BILL_BASE_FREQ", 7);
define("AD_RATE_BASE_FREQ", '/week');
		
######################################
define("ONE_DAY_SEC", 86400);
define("FALLBACK_DISCOUNT", 50);//IN %
define("TEXT_AD_DISCOUNT_CHARGE", $textAdDiscountCharge);//IN %
define("TEXT_AD_CHARGE", $textAdCharge);//IN %
define("BANNER_TYPE2_CHARGE", $bannerT2Charge);//IN %
define("BANNER_TYPE3_CHARGE", $bannerT3Charge);//IN %
define("MIN_AD_DEPOSIT", $minAdDeposit);
define("MIN_AD_PLACEMENT_CREDIT", (0.05 * $minAdDeposit));//5% OF minAdDeposit
define("AD_PREMIUM_ELIGIBILITY_AMOUNT", $adPrmEligAmt);
define("AD_PREMIUM_PURSE_TOPUP_RATE", $adPrmPurseTopupRate);
define("INTL_MEANS_OF_PAY_URL", $intlMeansOfPayUrl);
define("BANK_DETAILS", $bankDetails);

	

###EMAIL STYLE PLACEHOLDER PREFIX
define('EMS_PH_PRE', '__PH_');	

###DISPATCHER
define($K='DB_PH_UN', '_'.$K.'_');
define($K='DB_PH_FN', '_'.$K.'_');
define($K='DB_PH_LN', '_'.$K.'_');
define($K='DB_PH_FORUM_RULES', '_'.$K.'_');
define($K='DB_PH_SITE_NAME', '_'.$K.'_');
define($K='DB_PH_SITE_SLOGAN', '_'.$K.'_');
define($K='DB_PH_SITE_SOCIAL_FOLLOW_WIDGET', '_'.$K.'_');
define($K='DB_PH_SITE_SOCIAL_PLUGINS', '_'.$K.'_');
define($K='DB_PH_SITE_SUPPORT_EMAIL', '_'.$K.'_');
define($K='DB_PH_SITE_WHATSAPP_URL', '_'.$K.'_');
define($K='DB_PH_SITE_TELEGRAM_URL', '_'.$K.'_');
define($K='DB_PH_SITE_HOT_LINES', '_'.$K.'_');
define('DB_PH_ARR', array(DB_PH_FN, DB_PH_LN, DB_PH_UN, DB_PH_FORUM_RULES, DB_PH_SITE_NAME, DB_PH_SITE_SLOGAN, DB_PH_SITE_SOCIAL_FOLLOW_WIDGET, DB_PH_SITE_SOCIAL_PLUGINS,
 DB_PH_SITE_SUPPORT_EMAIL, DB_PH_SITE_WHATSAPP_URL, DB_PH_SITE_TELEGRAM_URL, DB_PH_SITE_HOT_LINES));
define('DB_PH_REPLACE_ARR', array($ACCOUNT->getFirstName(), $ACCOUNT->getLastName(), $ACCOUNT->getUsername(), $SITE->getCommunityRules(), $GLOBAL_siteName, SITE_SLOGAN, $SITE->getSocialLinks(array('type'=>'followus','widget'=>true)), 
$SITE->getSocialHandlePlugins(), CONTACT_US_EMAIL_ADDR, SITE_WHATSAPP_URL, SITE_TELEGRAM_URL, SITE_HOT_LINES));

$GLOBAL_TimeNow = $ENGINE->get_date_safe();
///DECIDE VALENTINE, XMAS AND NEW YEAR (seasonGreetings Jan 1-15 and Dec 10-31)///
$md = $ENGINE->get_date_safe('', 'm-j');
$GLOBAL_isSeasonGreetings = preg_match("#(^01-([1-9]|10|11|12|13|14|15)|12-[0-9]{2})$#", $md);
$GLOBAL_isValentineGreetings = ($md == '02-14');
$GLOBAL_isXmas = ($md == '12-25');
$GLOBAL_isNewYear = ($md == '01-1');

////INITIALIZE CROSS-PAGE VARIABLES AND FUNCTIONS///////

$GLOBAL_usernameSanitized = $ACCOUNT->SESS->getUsernameSlug();
$GLOBAL_sessionUrl = $ACCOUNT->sanitizeUserSlug($GLOBAL_username, array('anchor'=>true));
$GLOBAL_sessionUrl_unOnly = $ACCOUNT->sanitizeUserSlug($GLOBAL_username, array('anchor'=>true, 'youRef'=>false));
$GLOBAL_privilege = $ACCOUNT->getUserPrivilege($GLOBAL_username);
list($GLOBAL_page_self, $GLOBAL_page_self_rel, $GLOBAL_rdr) = $ENGINE->get_page_path("all", "", false, false);
$GLOBAL_notLogged = $SITE->getMeta('not-logged-in-alert');
$GLOBAL_spinner = '<div class="'.($K='custom-spinner spinner-style-dot').'"><i></i><i></i><i></i></div>';
$GLOBAL_spinnerXs = '<div class="'.$K.' spinner-xs"><i></i><i></i><i></i></div>';
$GLOBAL_gSpinner = '<div class="'.$K.' global"><i></i><i></i><i></i></div>';
$mdRt_arr = $SITE->getMediaLinkRoot("ini-tops");

$mdRtKey_arr = array(
					
					"banner", "bannerX", "favicon", "faviconX", "spin", "avatar", "avatarX", 
					"post", "postX", "cloud", "cloudX", "badge", "badgeX", "uiBg", "uiBgX"

				);
list(

	$GLOBAL_mediaRootBanner, $GLOBAL_mediaRootBannerXCL, $GLOBAL_mediaRootFav, $GLOBAL_mediaRootFavXCL,
	$GLOBAL_mediaRootSpin, $GLOBAL_mediaRootAvt, $GLOBAL_mediaRootAvtXCL, $GLOBAL_mediaRootPost,
	$GLOBAL_mediaRootPostXCL, $GLOBAL_mediaRootCloud, $GLOBAL_mediaRootCloudXCL, $GLOBAL_mediaRootBadge,
	$GLOBAL_mediaRootBadgeXCL, $GLOBAL_mediaRootUiBg, $GLOBAL_mediaRootUiBgXCL
	
	) =  $ENGINE->get_assoc_arr($mdRt_arr, $mdRtKey_arr);

$GLOBAL_isAdmin = $ACCOUNT->SESS->isAdmin();
$GLOBAL_isStaff = $ACCOUNT->SESS->isStaff();
$GLOBAL_isTopStaff = $ACCOUNT->SESS->getUltimateLevel();
$GLOBAL_isAjax = $ENGINE->is_ajax();
$GLOBAL_delBtn = $SITE->getBgImg(array('file'=>$GLOBAL_mediaRootFav.'delete.png', 'anchor'=>false));//'<img class="delete" alt="delete" src="'.$GLOBAL_mediaRootFav.'delete.png"/>';
		
##DOC
define("POP_LOADER", '2GPOP_7AzaeoIrgdfubPCk0S3QYjc5Z_yTENM1vnFqH');
define("SERVERT_LOADER", '4GSTS_Ce1d_DKL-SWjnqFGMwJoOH7yBuYrVpa6l8Ifgmv3U4N0kT');
define("MORE_UIBG_LOADER", ($cmnLoaderTkn='tAPp-sUz0Ij4xD57-cySKL-').'uibg');
define("MORE_EMOTICONS_LOADER", $cmnLoaderTkn.'emoticons');
define("REMOTICONS_LOADER", $cmnLoaderTkn.'remoticons');
define("EMOTICON_TABS_LOADER", $cmnLoaderTkn.'emoticon-tabs');
define("DOC_ATTR", 'lang="en-UK" dir="LTR" data-app="public" data-sess-user="'.$GLOBAL_username.'" data-asset-pre="'.ASSET_PREFIX.'"
data-base-rdr="'.$GLOBAL_rdr.'" data-pop-loader="'.POP_LOADER.'" data-servert-loader="'.SERVERT_LOADER.'" data-composer-id="'.COMPOSER_ID.'"
data-muibg-loader="'.MORE_UIBG_LOADER.'" data-memoticons-loader="'.MORE_EMOTICONS_LOADER.'" data-remoticons-loader="'.REMOTICONS_LOADER.'"
data-emoticon-tabs-loader="'.EMOTICON_TABS_LOADER.'"');
		
###MISC
//$GLOBAL_isSeasonGreetings = 1;
list($GLOBAL_isTrusted) =  $FORUM->authorizeModeration(ULTIMATE_MOD);
$max_topic_name_len = ($GLOBAL_isAdmin)? 200 : 130;
$seasonCardRand = 1; //mt_rand(1,4);
$seasonCard = ($GLOBAL_isSeasonGreetings && $showSeasonCards)? 'src="'.$GLOBAL_mediaRootFav.'season-card-'.$seasonCardRand.'.jpg" class="img-responsive" alt="season greetings card" title="Season\'s Greetings"' : '';
$seasonHeaderRand = 1; //mt_rand(1,1);
//$seasonHeader = ($GLOBAL_isSeasonGreetings)? 'src="'.$GLOBAL_mediaRootFav.'season-header-'.$seasonHeaderRand.'.png" class="img-responsive brand-logo" alt="season header" title="Season\'s Greeting"' : '';
$seasonHeader = ($GLOBAL_isSeasonGreetings)? 'src="'.$GLOBAL_mediaRootFav.'kranook-mid-logo-season-300.png" class="img-responsive brand-logo" alt="Season Logo" title="Season\'s greetings from us all at '.$GLOBAL_siteName.'"' : '';
$xmasCardRand = 1; //mt_rand(1,3);
$xmasCard = ($GLOBAL_isXmas && $showSeasonCards)? 'src="'.$GLOBAL_mediaRootFav.'xmas-card-'.$xmasCardRand.'.gif" class="img-responsive" alt="Merry Christmas" title="Merry Christmas"' : '';
$newyearCardRand = 1; //mt_rand(1,3);
$newyearCard = ($GLOBAL_isNewYear && $showSeasonCards)? 'src="'.$GLOBAL_mediaRootFav.'new-year-card-'.$newyearCardRand.'.gif" class="img-responsive" alt="Happy New Year" title="Happy New Year"' : '';
$valCardRand = 1; //mt_rand(1,1);
$valCard = ($GLOBAL_isValentineGreetings && $showSeasonCards)? 'src="'.$GLOBAL_mediaRootFav.'valentine-card-'.$valCardRand.'.jpg" class="img-responsive" alt="valentine greetings card"  title="Happy Valentine"' : '';
		
define('SESS_ALL', 'sess_all');
define('UIBG_ID_FIELD', 'uibg-loader');
define('DAILY_PM_LIMIT', 10);
define('MAX_POST_UPLOAD_SIZE_STR', '5MB');
define('MAX_TOPIC_NAME_LEN', $max_topic_name_len);
define("BIRTHDAY_ICON", 'src="'.$GLOBAL_mediaRootFav.'bgift.png" class="fav-img" alt="birthday favicon"');
define("SEASON_CARD", $seasonCard);
define("SEASON_HEADER", $seasonHeader);
define("XMAS_CARD", $xmasCard);
define("NEW_YEAR_CARD", $newyearCard);
define("VALENTINE_CARD", $valCard);
define("STAFF_SEAL", 'src="'.$GLOBAL_mediaRootFav.'star-medal.png" class="fav-img" alt="favicon"');
define("TRUSTED_SEAL", 'src="'.$GLOBAL_mediaRootFav.'trusted.png" class="fav-img" alt="favicon"');
define("SITE_COUNTRY", 'Nigeria');
define("CURRENCY_SUFFIX", ' NGN ');
define("CURRENCY_SYMBOL", '&#8358;');
define("AD_CREDIT_SUFFIX", '<span title="Advertising Credit"> AC </span>');
define("CAMPAIGN_LOW_CREDIT_LIMIT", 1);##MAX AD UPLOADABLE ON LOW CREDIT
define("MAX_SECTIONS_PLACEABLE", ($GLOBAL_isAdmin? 20 : 10));
define("MAX_SECTIONS_PLACEABLE_ON_LOW_CRD", ($GLOBAL_isAdmin? MAX_SECTIONS_PLACEABLE : 1));
define("GP_SOCIAL", 'Google+');
define("FB_SOCIAL", 'Facebook');
define("TW_SOCIAL", 'Twitter');
define("PIN_SOCIAL", 'Pinterest');
define("LDN_SOCIAL", 'LinkedIn');
define("WA_SOCIAL", 'WhatsApp');
define("TEL_SOCIAL", 'Telegram');
define("EM_SOCIAL", 'Email');
define("_TIMED_FADE_OUT", 'data-_tfo="true"');
		
##AUTHENTICATION CODE KEYS
define("AUTH_CODE_KEY_CONFIRM_REG_EMAIL", "confirm_reg_email");
define("AUTH_CODE_KEY_ACTIVATE_USER", "activate_user_account");
define("AUTH_CODE_KEY_CANCEL_USER", "cancel_user_account");
define("AUTH_CODE_KEY_UNLOCK_LOGIN", "unlock_login");
define("AUTH_CODE_KEY_CHANGE_EMAIL", "change_user_email");

###POST	
define("ALLOW_XMQ", $ACCOUNT->SESS->getCrossPageMultiQuote());



/**************************************************
	HANDLE SESSION SPOOFING
**************************************************/
if($GLOBAL_username){
	/*****CHECK IF THE USER HAS BEEN PLACED UNDER A BAN AND LOG THEM OUT ON THE FLY*****
							OR
	*****IF SESSION IS NOT DATABASE VALIDATED THEN INVALIDATE IT ON THE FLY******/
	list($spamBan, $modsBan, $isBanned) = $ACCOUNT->getBanStatus($GLOBAL_userId);				
	
	if(($modsBan && !$GLOBAL_isAjax) || !$GLOBAL_userId){
		
		if($modsBan)
			$ENGINE->set_cookie('mods_banned', $GLOBAL_username);
		
		$ACCOUNT->SESS->destroy(array("forceInvalidate" => true));
		
	}

}
	

/*****CHECK IF USER HAS COOKIE ENABLED
	SET A TEST COOKIE AND CHECK FROM page footer IF IT WAS SENT
**/
$cookieRefreshKey = 'cookiesLoadSilentRefresh';

if(!$ENGINE->get_global_var('ck', $K='isCookieEnabled')){
	
	$ENGINE->set_cookie($K, true);
	
	/*****
		SINCE COOKIES SET ON A CURRENT PAGE ARE NOT RECEIVED IMMEDIATELY UNTIL THE PAGE RELOADS; 
		PERFORM A SILENT PAGE REFRESH FOR FIRST LANDING ON ANY PAGE TO ENSURE RELEVANT COOKIES ARE SENT TO
		THE CURRENT PAGE
	**/	
	if(!$ENGINE->get_global_var('ss', $cookieRefreshKey)){
		
		$ENGINE->set_global_var('ss', $cookieRefreshKey, true);
		//header("Location:".$GLOBAL_page_self_rel);
		//exit();
		
	}
	
}else{
	
	//ONCE THE COOKIE IS SET, UNSET THE COOKIES REFRESH CONTROL VARIABLE FOR SUBSEQUENT DAYS VISIT
	$ENGINE->unset_global_var('ss', $cookieRefreshKey);
	
}


	
?>