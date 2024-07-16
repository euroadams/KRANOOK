<?php


//QR CODE NAMESPACE
use Endroid\QrCode\Color\color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;


class Site{

	use Category, Section, Thread;
	
	/*** Generic member variables ***/
	private $DBM;
	private $ACCOUNT;
	private $SESS;
	private $ENGINE;
	private $INT_HASHER;
	
	
	
	/*** Constructor ***/
	public function __construct(){
		
		global $dbm, $ACCOUNT, $ENGINE, $INT_HASHER;
		
		$this->DBM = $dbm;
		$this->ACCOUNT = $ACCOUNT;
		$this->SESS = $ACCOUNT->SESS;
		$this->ENGINE = $ENGINE;
		$this->INT_HASHER = $INT_HASHER;
		
		
	}
	
	
	
	/*** Destructor ***/
	public function __destruct(){
		
		
	}

	
	
	/************************************************************************************/
	/************************************************************************************
									SITE METHODS
	/************************************************************************************
	/************************************************************************************/
		
	



	 


	


		
	/*** Method for fetching redirect url query string if passed ***/
	public function getRdr($retArr = true){
				
		$rdr = urlDecode(isset($_GET[$K = '_rdr'])? $_GET[$K] : (isset($_POST[$K])? $_POST[$K] : '')); 
		$rdrAlt = $rdr? $rdr : '/';

		return ($retArr? array($rdr, $rdrAlt) : $rdrAlt);

	}


		
	/*** Method for embedding custom scrollbar into html elements ***/
	public function embedCustomScrollbar(){
	 
		return 'data-has-custom-scrollbar="'.$this->ENGINE->jsonify('{"scrollbars": { "autoHide": "leave"}}').'"';

	}
		
	/*** Method for embedding timed visibility into html elements 
	 * 	@param $t => time in seconds
	 * ***/
	public function embedTimedVisibility($t = 120){
	 
		return 'data-timed-visibility="'.$t.'"';

	}


		
	/*** Method for fetching important site meta datas ***/
	public function getMeta($type, $metaArr = array()){
		
		global $GLOBAL_rdr;
		
		switch(strtolower($type)){
			
			case 'not-logged-in-alert': $meta = '<span class="alert alert-danger">It appears you are not logged in or your session has expired, please <a href="/login?_rdr='.$GLOBAL_rdr.'#lun" class="links">login</a></span>';
				break;
			
			case 'ad-mods-alert': $meta = '<span class="alert alert-danger" data-click-to-hide="true">Please note that you are not the owner of this Ad, however you have been granted administrator\'s Ad moderation privilege</span></b>'; 
				break; 
				
			case 'file-preview-tip': $meta = '<p class="prime" data-auto-rewrite="true" data-rewrite-once="true">Tips: hover over file names to view the available preview</p>'; 
				break;
			
			case 'valid-password-tip': $meta = '<span class="alert alert-danger"><b>NOTICE:</b> Password must be at least '.MIN_PWD.' characters in length. It must contain an uppercase and lowercase alphabet, a number, a symbol and no spaces!</span>';
				break;
			
			case 'unactivated-user-poster': $meta = (!($sessUsername = $this->SESS->getUsername()) || ($sessUsername && $this->SESS->getActivationStatus()))? '' : 
				'<span class="alert alert-danger">
					<b>ATTENTION '.$sessUsername.':</b> your account has not been activated/confirmed. Please note that all 
					accounts that have not been activated/confirmed will not be able to earn any badge or reputation and 
					consequently won`t be able to unlock any of the community privileges.<br/>
					Please <a href="/activate-account?username='.$sessUsername.'&code='.$this->getAuthentication($sessUsername, AUTH_CODE_KEY_ACTIVATE_USER).'" class="btn btn-success" role="button" target="_blank">ACTIVATE/CONFIRM</a> your account now to remove
					these restrictions. <a href="/resend_confirmation_code?user='.$sessUsername.'&_rdr=signup" class="resend-code links" data-user="'.$sessUsername.'" >Resend Activation Email</a>
				</span>';
				break;
			
			case 'thread-access-decline-msg':
				$meta = $this->ENGINE->get_assoc_arr($metaArr, 'rs')? '<div class="alert alert-danger">Sorry this thread has been recycled and you are not authorized to view it</div>' :
				'<div class="alert alert-warning">Sorry this thread is under a '.$this->ENGINE->get_assoc_arr($metaArr, 'pa').' protection<br/>you do not meet the required reputation to participate</div>';				
				break;

			default: $meta = '';
			
		}
		
		return $meta;
		
	}



	


		
	/*** Method for fetching dynamic content tips ***/
	public function getDynamicContentTips(){
		
		return '
		To refer to client by their specific names in your content body or title or to include a widget in your content body simply use the corresponding placeholders below:
		<ol class="ol" type="i">
			<li><span '.($tmp='data-has-clipboard-copy="true"').'>'.DB_PH_UN.'</span> "for client username"</li>
			<li><span '.$tmp.'>'.DB_PH_FN.'</span> "for client first name"</li>
			<li><span '.$tmp.'>'.DB_PH_LN.'</span> "for client last name"</li>
			<li>For client full name simply combine '.DB_PH_FN.' and '.DB_PH_LN.' accordingly</li>
			<li><span '.$tmp.'>'.DB_PH_SITE_NAME.'</span> "for website name"</li>
			<li><span '.$tmp.'>'.DB_PH_SITE_SLOGAN.'</span> "for website slogan"</li>
			<li><span '.$tmp.'>'.DB_PH_SITE_SUPPORT_EMAIL.'</span> "for website support email address"</li>
			<li><span '.$tmp.'>'.DB_PH_SITE_WHATSAPP_URL.'</span> "for website whatsapp url"</li>
			<li><span '.$tmp.'>'.DB_PH_SITE_TELEGRAM_URL.'</span> "for website telegram url"</li>
			<li><span '.$tmp.'>'.DB_PH_SITE_HOT_LINES.'</span> "for website phone hot lines"</li>
			<li><span '.$tmp.'>'.DB_PH_SITE_SOCIAL_FOLLOW_WIDGET.'</span> "for website social follow widget"</li>
			<li><span '.$tmp.'>'.DB_PH_SITE_SOCIAL_PLUGINS.'</span> "for website social plugin widget"</li>
			<li><span '.$tmp.'>'.DB_PH_FORUM_RULES.'</span> "for community rules widget"</li>
		</ol>';
		
	}

	


		
	/*** Method for replacing placeholders in database with their true dynamic values ***/
	public function replaceDbPh($content){
		
		return str_ireplace(DB_PH_ARR, DB_PH_REPLACE_ARR, $content);
		
	}




	 
	/*** Method for fetching preferred site name ***/
	public function getSiteName(){
		
		return getAutoPilotState("VISIBLE_SITE_NAME");
		
	}

	 


		
	/*** Method for fetching preferred site favicon ***/
	public function getSiteFavicon($returnPath2Fav = true){
		
		$faviconName = 'krn-mid-logo-300.png';

		return ($returnPath2Fav? $this->getMediaLinkRoot("favicon").$faviconName : $faviconName);		
		
	}


	


		
	/*** Method for fetching site logo ***/
	public function getBrandLogo($eTag='', $pCls='', $linkCls=' prime-sc1', $append=''){
		
		global $GLOBAL_siteName, $GLOBAL_mediaRootFav;
		
		$eTag = $eTag? $eTag : 'div';
		$whiteSpace = ($append? '<i class="mr-10"></i><i class="mr-10"></i>' : '');	
		
		$siteLogoSrc = 'src="'.$GLOBAL_mediaRootFav.'kranook-mid-logo-300.png" class="img-responsive brand-logo" alt="Kranook Logo" title="Kranook"';
		
		return	'<'.$eTag.' class="nav-brand '.$pCls.'">
					<a class="links'.$linkCls.' no-hover-bg" href="/"><img '.(SEASON_HEADER? SEASON_HEADER : $siteLogoSrc).' /></a>'.$whiteSpace.$append.'
				</'.$eTag.'>';
	}



		
	/*** Method for fetching site copy right statement ***/
	public function getCopyRight($cls=''){
		
		global $GLOBAL_siteDomain, $GLOBAL_siteName;
		
		$linkCls = 'links '.$cls;
		return '<a href="'.$GLOBAL_siteDomain.'" class="'.$linkCls.'">'.$GLOBAL_siteName.'</a>-Copyright &#169; '. Date('Y') .' <a target="_blank" href="'.getAutoPilotState("ADMIN_SOCIAL_LINK").'" class="'.$linkCls.'" rel="nofollow noopener">'.getAutoPilotState("ADMIN_SOCIAL_LINK", "sn").'</a> All Rights Reserved.';	
		
	}



	


		
	/*** Method for fetching site statistics ***/
	public function siteStatistics(){
		
		$stats="";
			
		//PDO QUERY/////////
		
		$sql = "SELECT COUNT(*) FROM topics ";
		$valArr = array();
		$topicCount = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
		$topicCount = $this->ENGINE->format_number($topicCount).' topic'.(($topicCount == 1)? '' : 's');


		//PDO QUERY//////////
		
		$sql = "SELECT COUNT(*) FROM topic_views ";
		$valArr = array();
		$pageViewCount = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();

		$pageViewCount = ' & '.$this->ENGINE->format_number($pageViewCount).' view'.(($pageViewCount == 1)? '' : 's');

		$stats = ' '. $topicCount.$pageViewCount;

		return $stats;

		
	}



	
	
	

	 
		
		
	/*** Method for highlighting target text keywords in a string ***/
	public function highlightText($mainTxt, $targetTxt){	
		
		/********************************************
		Using negative lookbehind assertion ?<!
		NOTES: ensure you don`t highlight 
			attributes (=, ", ')
			tags (<, [)
			forward url slash (/)
		********************************************/
		
		return preg_replace("#(?<!\=|\=\'|\=\"|/|\<|\[)(".$targetTxt.")#i", '<span class="search-identifier">$1</span>', $mainTxt);
		
		
	}






  
 
 
	 
		
		
	/*** Method for ignoring certain keywords in community search query ***/
	public function inSearchKeywordExceptions($keyword){	 
				
		$non_vital_keywords_arr = array(
			'this', 'has', 'how', 'his', 'her', 'him', 'she', 'was', 'were', 'want', 'where',
			'who', 'whom', 'whose', 'went', 'with'
		);
		
		return in_array($keyword, $non_vital_keywords_arr);
		
	 }
	 
 
 
 
		


		
		
	/*** Method for popping adult contents view warning ***/
	public function adultContentsViewPrompt($rdr='', $tokenId=''){
		
		if(!isset($_SESSION["ADULT_VIEW_PROMPT_ACCEPTED"]) || ($tokenId && !isset($_SESSION["ADULT_VIEW_PROMPT_ACCEPTED_TOKEN_".$tokenId]))){
			
			header("Location:/plus18?".($tokenId? '_tkid='.$tokenId : '').'&'.($rdr? '_rdr='.$rdr : ''), true, 302);
			exit();
			
		}
	}


	

	
	 


		
	/*** Method for fetching javascript bound back button ***/
	public function getBackBtn(){
	
		return '<a class="links" onclick="javaScript:history.back()" href="/'.$this->ENGINE->get_page_path('page_url').'">Go Back</a>';
	 
	}





		
	/*** Method for fetching site navigation bar search field ***/
	public function getNavSearch($tk='tn', $footer=false){
		
		$listId = 'pre-sq-'.$tk;
		$fid = 'srch-'.$tk;
		
		return '<div class="submit-btn-impounded align-c '.($footer? 'base-pad' : 'top base-t-mg').'" >
					<form  method="post" class="nav-form '.($footer? '' : 'align-lg-r').'" action="/search">
						<div class="field-ctrl col-lg-w-'.($footer? '5' : '6').' col-w-7">
							<div class="">
								<label class="field-clr-base">
									<input aria-label="search '.$this->getSiteName().'" list="'.$listId.'" id="'.$fid.'" class="field has-field-icon has-field-clr '.($footer? '' : 'field-elastic').'" type="text" name="sq" placeholder="search '.$this->getSiteName().'..." />
								</label>
								<datalist id="'.$listId.'">
									<option>Economy</option>
									<option>Advertising</option>
									<option>Nigeria</option>
									<option>Africa</option>
								</datalist>
							</div>
						</div>
						<div class="field-ctrl">
							<input type="submit" class="submit-btn" name="search" value="SEARCH" />
						</div>
					</form>
				</div>';
				
	}



	


		
	/*** Method for building and fetching nav url ***/
	private function buildNavUrlMetas($listItem){
		
		$itemsArr = explode(':', $listItem); //listItem => slug:urlLabel:urlIcon:ignoreCond

		$slugItems = $this->ENGINE->get_assoc_arr($itemsArr, 0);
		$slugItemsArr = explode('.', $slugItems);  //slugItems => slug.defaultOrderFlow
		$slug = $this->ENGINE->get_assoc_arr($slugItemsArr, 0);
		$defaultOrderFlow = $this->ENGINE->get_assoc_arr($slugItemsArr, 0);
		$urlLabelArr = explode(';', $this->ENGINE->get_assoc_arr($itemsArr, 1)); //urlLabel => urlLabel;urlTitle
		$urlLabel = $this->ENGINE->get_assoc_arr($urlLabelArr, 0);
		!$urlLabel? ($urlLabel = $slug) : '';
		$urlTitle = $this->ENGINE->get_assoc_arr($urlLabelArr, 1);
		!$urlTitle? ($urlTitle = $urlLabel) : '';			
		$urlIcon = $this->ENGINE->get_assoc_arr($itemsArr, 2);
		$urlIgnore = $this->ENGINE->get_assoc_arr($itemsArr, 3);
		
		return array($slug, ucwords($urlLabel), $urlTitle, $urlIcon, $defaultOrderFlow, $urlIgnore);

	}





		
	/*** Method for building nav url icon style ***/
	private function buildNavUrlIcon($urlIcon, $iconBlock, $urlListIconRight){

		if($urlIcon)				
			$urlIcon = '<div class="p'.($urlListIconRight? 'l' : 'r' ).'-5 '.($iconBlock? '' : 'inline-block').'">'.$urlIcon.'</div>';

		return $urlIcon;

	}


		
	/*** Method for building and fetching sort and filter link list ***/
	public function buildSortLinks($metaArr){
		
		$orderAcc = $orderFlowAcc = $filterAcc = '';
		$orderList=$orderFlowList=$filterList=array();

		$activeCls = ' class="active" ';
		$colon = ':';
				
		$baseUrl = $this->ENGINE->get_assoc_arr($metaArr, 'baseUrl');
		$pageId = $this->ENGINE->get_assoc_arr($metaArr, 'pageId');
		$navExtCls = $this->ENGINE->get_assoc_arr($metaArr, 'navExtCls');
		$sq = $this->ENGINE->get_assoc_arr($metaArr, 'sq');
		$urlHash = md5('_ENGINE_srt_tab_'.$baseUrl);//$this->ENGINE->get_assoc_arr($metaArr, 'urlHash');
		$hash = ($urlHash? '#'.$urlHash : '');
		$hasRelBase = $this->ENGINE->is_assoc_key_set($metaArr, $K='hasRelBase')? $this->ENGINE->get_assoc_arr($metaArr, $K) : true; // has relative base url
		$hasRelBase? ($baseUrl = '/'.$baseUrl) : '';
		
		$activeOrder = $this->ENGINE->get_assoc_arr($metaArr, 'activeOrder');
		$defaultOrder = $this->ENGINE->get_assoc_arr($metaArr, 'defaultOrder');
		$orderGlobLabel = $this->ENGINE->is_assoc_key_set($metaArr, $K='orderGlobLabel')? $this->ENGINE->get_assoc_arr($metaArr, $K) : 'Order by';
		$orderGlobLabelSuff = $this->ENGINE->is_assoc_key_set($metaArr, $K='orderGlobLabelSuff')? $this->ENGINE->get_assoc_arr($metaArr, $K) : $colon;
		$this->ENGINE->is_assoc_key_set($metaArr, $K='orderList')? ($orderList = $this->ENGINE->get_assoc_arr($metaArr, $K)) : '';
		$urlListIconRight = $this->ENGINE->is_assoc_key_set($metaArr, $K='urlListIconRight')? $this->ENGINE->get_assoc_arr($metaArr, $K) : false;
		$iconBlock = $this->ENGINE->is_assoc_key_set($metaArr, $K='iconBlock')? $this->ENGINE->get_assoc_arr($metaArr, $K) : false;
		$orderKey = $this->ENGINE->get_assoc_arr($metaArr, 'orderKey');
		
		$activeOrderFlow = $this->ENGINE->get_assoc_arr($metaArr, 'activeOrderFlow');
		$defaultOrderFlow = $this->ENGINE->get_assoc_arr($metaArr, 'defaultOrderFlow');
		$orderFlowGlobLabel = $this->ENGINE->is_assoc_key_set($metaArr, $K='orderFlowGlobLabel')? $this->ENGINE->get_assoc_arr($metaArr, $K) : 'Order flow';
		$orderFlowGlobLabelSuff = $this->ENGINE->is_assoc_key_set($metaArr, $K='orderFlowGlobLabelSuff')? $this->ENGINE->get_assoc_arr($metaArr, $K) : $colon;
		$this->ENGINE->is_assoc_key_set($metaArr, $K='orderFlowList')? ($orderFlowList = $this->ENGINE->get_assoc_arr($metaArr, $K)) : '';
		$orderFlowKey = $this->ENGINE->get_assoc_arr($metaArr, 'orderFlowKey');
		
		$activeFilter = $this->ENGINE->get_assoc_arr($metaArr, 'activeFilter');
		$defaultFilter = $this->ENGINE->get_assoc_arr($metaArr, 'defaultFilter');
		$filterGlobLabel = $this->ENGINE->is_assoc_key_set($metaArr, $K='filterGlobLabel')? $this->ENGINE->get_assoc_arr($metaArr, $K) : 'Filter by';
		$filterGlobLabelSuff = $this->ENGINE->is_assoc_key_set($metaArr, $K='filterGlobLabelSuff')? $this->ENGINE->get_assoc_arr($metaArr, $K) : $colon;
		$this->ENGINE->is_assoc_key_set($metaArr, $K='filterList')? ($filterList = $this->ENGINE->get_assoc_arr($metaArr, $K)) : '';
		$filterKey = $this->ENGINE->get_assoc_arr($metaArr, 'filterKey');

		$isKeyValQstr = ($orderKey || $orderFlowKey || $filterKey);	
		$pageKey = $this->ENGINE->is_assoc_key_set($metaArr, $K='pageKey')? $this->ENGINE->get_assoc_arr($metaArr, $K) : 'pageId';	
		
		$appendPage = (($pageId > 1)? '/'.$pageId : '').($sq? '?sq='.$sq : '').$hash;		
		$PreFilterQstr = '/'.$activeOrder.'/';
		$PostFilterQstr = ($activeOrderFlow? '/'.$activeOrderFlow : '').$appendPage;
		$PreOrderFlowQstr = '/'.$activeOrder.'/'.($activeFilter? $activeFilter.'/' : '');	
		$pipeSep = ' |'	;
		
		//ORDER OR SORT
		foreach($orderList as $listItem){ //orderList => listItem, listItem,.....									

			list($slug, $urlLabel, $urlTitle, $urlIcon, $defaultOrderFlow, $urlIgnore) = $this->buildNavUrlMetas($listItem);
			
			if($urlIgnore)
				continue;

			$srtQstr = ($activeFilter? '/'.$activeFilter : '').($activeOrderFlow? '/'.($defaultOrderFlow?  $defaultOrderFlow : $activeOrderFlow) : '').$appendPage;			

			$orderActiveCls = (
					(	strtolower($activeOrder) == strtolower($slug) || 
						(	$defaultOrder && strtolower($defaultOrder) == strtolower($slug) &&
							(!$activeOrder || !$this->ENGINE->find_in_arr_val($activeOrder, $orderList)) // No activeOrder or activeOrder not in orderList							
						)
					)?	 $activeCls : "");

			$uri = $baseUrl.'/'.$slug.$srtQstr;			

			if($isKeyValQstr){

				$appendPage = ($activeFilter? '&'.$filterKey.'='.urlencode($activeFilter) : '').
							($activeOrderFlow? '&'.$orderFlowKey.'='.urlencode($activeOrderFlow) : '').
							($pageId? '&'.$pageKey.'='.$pageId : '').$hash;
							
				$uri = $baseUrl.'?'.$orderKey.'='.urlencode($slug).$appendPage;	
				
			}

			$urlIcon = $this->buildNavUrlIcon($urlIcon, $iconBlock, $urlListIconRight);
		
			$orderAcc .= '<li '.$orderActiveCls.'><a class="links" href="'.$uri.'" title="'.$urlTitle.'" >'.($urlListIconRight? '' : $urlIcon).$urlLabel.($urlListIconRight? $urlIcon : '').'</a></li>';
			
		}
		
		//ORDER FLOW (ASC|DESC)
		foreach($orderFlowList as $listItem){			

			list($slug, $urlLabel, $urlTitle, $urlIcon, $defaultOrderFlow, $urlIgnore) = $this->buildNavUrlMetas($listItem);

			if($urlIgnore)
				continue;

			$orderFlowActiveCls = (
				(	strtolower($activeOrderFlow) == strtolower($slug) || 
					(	$defaultOrderFlow && strtolower($defaultOrderFlow) == strtolower($slug) &&
						(!$activeOrderFlow || !$this->ENGINE->find_in_arr_val($activeOrderFlow, $orderFlowList)) // No activeOrderFlow or activeOrderFlow not in orderFlowList							
					)
				)?	 $activeCls : "");

			$uri = $baseUrl.$PreOrderFlowQstr.$slug.$appendPage;
						
			if($isKeyValQstr){

				$appendPage = ($activeOrder? '&'.$orderKey.'='.urlencode($activeOrder) : '').
							($activeFilter? '&'.$filterKey.'='.urlencode($activeFilter) : '').
							($pageId? '&'.$pageKey.'='.$pageId : '').$hash;

				$uri = $baseUrl.'?'.$orderFlowKey.'='.urlencode($slug).$appendPage;	
				
			}

			$urlIcon = $this->buildNavUrlIcon($urlIcon, $iconBlock, $urlListIconRight);

			$orderFlowAcc .= '<li '.$orderFlowActiveCls.'><a class="links" href="'.$uri.'" title="'.$urlTitle.'" >'.($urlListIconRight? '' : $urlIcon).$urlLabel.($urlListIconRight? $urlIcon : '').'</a></li>'.$pipeSep;
			
		}
		
		//FILTER BY
		foreach($filterList as $listItem){			

			list($slug, $urlLabel, $urlTitle, $urlIcon, $defaultOrderFlow, $urlIgnore) = $this->buildNavUrlMetas($listItem);

			if($urlIgnore)
				continue;

			$filterActiveCls = (
				(	strtolower($activeFilter) == strtolower($slug) || 
					(	$defaultFilter && strtolower($defaultFilter) == strtolower($slug) &&
						(!$activeFilter || !$this->ENGINE->find_in_arr_val($activeFilter, $filterList)) // No activeFilter or activeFilter not in filterList							
					)
				)?	 $activeCls : "");

			$uri = $baseUrl.$PreFilterQstr.$slug.$PostFilterQstr;
						
			if($isKeyValQstr){

				$appendPage = ($activeOrder? '&'.$orderKey.'='.urlencode($activeOrder) : '').
							($activeOrderFlow? '&'.$orderFlowKey.'='.urlencode($activeOrderFlow) : '').
							($pageId? '&'.$pageKey.'='.$pageId : '').$hash;

				$uri = $baseUrl.'?'.$filterKey.'='.urlencode($slug).$appendPage;	
				
			}
			
			$urlIcon = $this->buildNavUrlIcon($urlIcon, $iconBlock, $urlListIconRight);

			$filterAcc .= '<li '.$filterActiveCls.'><a class="links" href="'.$uri.'" title="'.$urlTitle.'" >'.($urlListIconRight? '' : $urlIcon).$urlLabel.($urlListIconRight? $urlIcon : '').'</a></li>';
			
		}

		$gloLabelEl = 'h4';
		
		$sortNav = '<nav class="nav-base '.$navExtCls.'"><ul class="nav nav-pills justified-center active-classic" id="'.$urlHash.'">
						'.($orderAcc? '<li><'.$gloLabelEl.'>'.$orderGlobLabel.$orderGlobLabelSuff.'</'.$gloLabelEl.'></li>'.$orderAcc : '').'
						'.($orderFlowAcc? '<li><'.$gloLabelEl.'>'.$orderFlowGlobLabel.$orderFlowGlobLabelSuff.'</'.$gloLabelEl.'></li>'.rtrim($orderFlowAcc, $pipeSep) : '').'
						'.($filterAcc? '<li><'.$gloLabelEl.'>'.$filterGlobLabel.$filterGlobLabelSuff.'</'.$gloLabelEl.'></li>'.$filterAcc : '').'
					</ul></nav>';
		
		return $sortNav;
	
	}
	
	


	
	 


		
	/*** Method for building and fetching linear Navigation Links ***/
	public function buildLinearNav($metaArr){

		$urlAcc='';
		
		$activeCls = ' class="active" ';
		$urlItemsSep = ':';
		$baseUrl = $this->ENGINE->get_assoc_arr($metaArr, 'baseUrl');
		$hasRelBase = $this->ENGINE->is_assoc_key_set($metaArr, $K='hasRelBase')? $this->ENGINE->get_assoc_arr($metaArr, $K) : true; // has relative base url
		$hasRelBase? ($baseUrl = '/'.$baseUrl) : '';

		$active = $this->ENGINE->get_assoc_arr($metaArr, 'active');
		$default = $this->ENGINE->get_assoc_arr($metaArr, 'default');			
		$urlListCls = $this->ENGINE->is_assoc_key_set($metaArr, $K='urlListCls')? $this->ENGINE->get_assoc_arr($metaArr, $K) : 'nav-tabs justified justified-bom';
		$urlListIconRight = $this->ENGINE->is_assoc_key_set($metaArr, $K='urlListIconRight')? $this->ENGINE->get_assoc_arr($metaArr, $K) : false;
		$iconBlock = $this->ENGINE->is_assoc_key_set($metaArr, $K='iconBlock')? $this->ENGINE->get_assoc_arr($metaArr, $K) : false;
		$urlList = $this->ENGINE->get_assoc_arr($metaArr, 'urlList');	
		is_array($urlList)? '' : ($urlList = (array)$urlList);

		foreach($urlList as $listItem){
			
			$urlItemsArr = explode($urlItemsSep, $listItem); //urlItems => urlSlug:urlLabel:urlIcon:ignoreCond

			$urlSlug = $this->ENGINE->get_assoc_arr($urlItemsArr, 0);			
			$urlLabelArr = explode(';', $this->ENGINE->get_assoc_arr($urlItemsArr, 1)); //urlLabel => urlLabel;urlTitle
			$urlLabel = $this->ENGINE->get_assoc_arr($urlLabelArr, 0);
			$urlTitle = $this->ENGINE->get_assoc_arr($urlLabelArr, 1);
			!$urlTitle? ($urlTitle = $urlLabel) : '';			
			$urlIcon = $this->ENGINE->get_assoc_arr($urlItemsArr, 2);
			$urlIgnore = $this->ENGINE->get_assoc_arr($urlItemsArr, 3);

			list($slug, $urlLabel, $urlTitle, $urlIcon, $defaultOrderFlow, $urlIgnore) = $this->buildNavUrlMetas($listItem);			

			if($urlIgnore)
				continue;

			$activeUrlCls = (
				(	strtolower($active) == strtolower($slug) || 
					(	$default && strtolower($default) == strtolower($slug) &&
						(!$active || !$this->ENGINE->find_in_arr_val($active, $urlList)) // No active or active not in urlList							
					)
				)?	 $activeCls : "");

			$uri = $baseUrl.'/'.$slug;
			
			$urlIcon = $this->buildNavUrlIcon($urlIcon, $iconBlock, $urlListIconRight);

			$urlAcc .= '<li '.$activeUrlCls.'>
							<a class="links" href="'.$uri.'" title="'.$urlTitle.'" >'.($urlListIconRight? '' : $urlIcon).$urlLabel.($urlListIconRight? $urlIcon : '').'</a>
						</li>';
			
		}
						

		$urlAcc = $urlAcc? '<nav class="nav-base ">
								<ul class="nav '.$urlListCls.'">					
									'.$urlAcc.'
								</ul>
							</nav>'
				: '';

		return $urlAcc;

	}
	
	 


		
	/*** Method for building and fetching a search form ***/
	public function getSearchForm($metaArr){
		
		$url = $this->ENGINE->get_assoc_arr($metaArr, 'url');
		$pageResetUrl = $this->ENGINE->get_assoc_arr($metaArr, 'pageResetUrl');
		$formAttr = $this->ENGINE->get_assoc_arr($metaArr, 'formAttr');
		$formMethod = $this->ENGINE->get_assoc_arr($metaArr, 'formMethod');
		!$formMethod? ($formMethod = 'post') : '';
		$formClass = $this->ENGINE->get_assoc_arr($metaArr, 'formClass');
		!$formClass? ($formClass = 'inline-form') : '';
		$fName = $this->ENGINE->get_assoc_arr($metaArr, 'fieldName');
		$fLabel = $this->ENGINE->get_assoc_arr($metaArr, 'fieldLabel');
		$labelWrapper = $fLabel? 'label' : 'span';
		$hasFieldClr = $this->ENGINE->is_assoc_key_set($metaArr, $K='hasFieldClr')? $this->ENGINE->get_assoc_arr($metaArr, $K) : false;
		$fLabelClass = $this->ENGINE->get_assoc_arr($metaArr, 'fieldLabelClass');
		$fClass = $this->ENGINE->get_assoc_arr($metaArr, 'fieldClass');
		$fPH = $this->ENGINE->get_assoc_arr($metaArr, 'fieldPH');
		$fType = $this->ENGINE->get_assoc_arr($metaArr, 'fieldType');
		$moreFields = $this->ENGINE->get_assoc_arr($metaArr, 'moreFields');
		is_array($moreFields)? ($moreFields = '<div class="field-ctrl">'.implode('</div><div class="field-ctrl">', $moreFields).'</div>') : '';
		$hiddenFields = $this->ENGINE->get_assoc_arr($metaArr, 'hiddenFields');
		$newWindow = $this->ENGINE->get_assoc_arr($metaArr, 'newWindow');
		$newWindow = is_bool($newWindow)? '_blank' : $newWindow;
		$btnName = $this->ENGINE->get_assoc_arr($metaArr, 'btnName');
		!$btnName? ($btnName = 'search') : '';
		$btnClass = 'form-btn '.$this->ENGINE->get_assoc_arr($metaArr, 'btnClass');		
		$btnLabel = $this->ENGINE->get_assoc_arr($metaArr, 'btnLabel');
		!$btnLabel? ($btnLabel = 'search') : '';
		!$fType? ($fType = 'text') : '';
		
		if($hasFieldClr){
			
			$fLabelClass .= 'field-clr-base';
			$fClass .= ' has-field-icon has-field-clr ';
			
		}


		
		
		$f = '<form method="'.$formMethod.'" class="'.$formClass.' base-t-mg" action="'.$url.'" target="'.$newWindow.'" '.$formAttr.'>						
				<div class="field-ctrl">'.
					($hasFieldClr? '<label>'.$fLabel.'</label>' : '').'
					<'.$labelWrapper.' class="'.$fLabelClass.'">'.($hasFieldClr? '' : $fLabel).'																	
						<input class="field '.$fClass.'" value="'.(isset($_POST[$fName])? $_POST[$fName] : '').'" type="'.$fType.'" name="'.$fName.'" placeholder="'.$fPH.'" required="required" />																
					</'.$labelWrapper.'>
				</div>
				'.$moreFields.'
				<div class="field-ctrl">
					<div class="hide">'.$hiddenFields.'</div>
					<input type="submit" class="'.$btnClass.'" name="'.$btnName.'" value="'.$btnLabel.'" />
				</div>
				'.($pageResetUrl? '
					<div class="field-ctrl">
						<a class="form-btn btn-default" href="/'.$pageResetUrl.'" role="button">Reset Field</a>
					</div>'
				 : '').'
			</form>';
			
		return $f;
		
	}




	


		
	/*** Method for grabbing  params for presenting a page content under a different userId ***/
	public function getBackDoorViewOwnersParams(){
		
		$alertData = 'class="alert alert-danger" data-click-to-hide="true"';
		$sessUid = $this->SESS->getUserId();
		$specialPriv = (isset($_GET[$K="_owner"]) && $this->SESS->isAdmin());
		$owner = $specialPriv? $this->ENGINE->sanitize_user_input($_GET[$K]) : $this->SESS->getUsername();
		$ownerId = $owner? $this->ACCOUNT->memberIdToggle($owner, true) : $sessUid;
		$isOwner = ($sessUid == $ownerId);
		$intruder = (isset($_GET[$K]) && !$this->SESS->isAdmin());
		$intruderAlert = $intruder? '<span '.$alertData.'>WARNING: You are intruding, please refrain from this act henceforth</span>' : '';
		$alertAdmins = $isOwner? '' : $this->getMeta('ad-mods-alert');
		$refTitle = $alertAdmins? $owner."'s" : 'your';
		$specQstr = $specialPriv? '?'.$K.'='.$owner : '';		
		
		return array($owner, $ownerId, $alertAdmins, $specQstr, $refTitle, $isOwner, $intruderAlert);

	}




		
	/*** Method for fetching list of all countries of the world ***/
	public function getCountryList($forCountryCode=false){

		$n = $this->DBM->getMaxRowPerSelect();
		$acc = $options = '';
		
		/////DELETE UPLOADS RELATING TO THE TOPIC/////
		for($i = 0; ; $i += $n){
			
			///////PDO QUERY//////		
			$sql = "SELECT NAME, COUNTRY_CODE FROM countries WHERE NAME_ISO_ALPHA_3 !='' ORDER BY NAME ASC LIMIT ".$i.",".$n;
			$valArr = array();
			$stmt = $this->DBM->query($sql, true);
			
			/////IMPORTANT INFINITE LOOP CONTROL ////
			if(!$this->DBM->getSelectCount())
				break;
	
			while($row = $this->DBM->fetchRow($stmt)){

				$countryCode = $row["COUNTRY_CODE"];
				$options .= '<option '.($forCountryCode? 'value="+'.$countryCode.'"' : '').'>'.($forCountryCode? '(+'.$countryCode.') ' : '').$row["NAME"].'</option>';						
																
			}
																				
												
		}
		
		$options? ($acc = '<select class="field" name="'.($forCountryCode? 'country_code' : 'country').'">'.$options.'</select>') : '';

		return $acc;

	}
		




		
	/*** Method for collecting site traffics after a specific idle time (cron job) ***/
	public function collectSiteTraffic($pageExtractionLen=2, $tid=0){
		
		global $FORUM;
		
		$username = $this->SESS->getUsername();
		$userId = $this->SESS->getUserId();
		
		if(!$username)
			$username = "Guest";
		
		$currentPage = $this->ENGINE->get_page_path('page_url', $pageExtractionLen, true);
		
		$ip = $this->ENGINE->get_ip();
		$sid = $tid? $FORUM->getTopicDetail($tid) : 0;
		
		////PDO QUERY/////
				
		if(strtolower($username) == "guest"){
			
			$userId = 0;
			
			$sql = "SELECT PAGE_ON_VIEW FROM site_traffics WHERE (IP = ? AND USER_ID=0) LIMIT 1";
			$valArr = array($ip);
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			$row = $this->DBM->fetchRow($stmt);
			
			if(empty($row)){
				
				////PDO QUERY//////

				$sql = "INSERT INTO site_traffics (USER_ID, TOPIC_ID, SECTION_ID, PAGE_ON_VIEW, IP, ARRIVAL_TIME, REFRESH_TIME) VALUES(?,?,?,?,?,NOW(),NOW())";
				$valArr = array($userId, $tid, $sid, $currentPage, $ip);
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			
			}else{	
			
				$stillOnPage = (strtolower($row["PAGE_ON_VIEW"]) == strtolower($currentPage));	
				////PDO QUERY////
				$sql = "UPDATE site_traffics SET TOPIC_ID = ?, SECTION_ID = ?, PAGE_ON_VIEW = ?, REFRESH_TIME = NOW()".($stillOnPage? '' : ', ARRIVAL_TIME = NOW()')." WHERE (IP = ? AND USER_ID=0) LIMIT 1";
				$valArr = array($tid, $sid, $currentPage, $ip);
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
					
			}

			
		}else{
			
			$sql = "SELECT PAGE_ON_VIEW FROM site_traffics WHERE USER_ID = ? LIMIT 1";
			$valArr = array($userId);
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			$row = $this->DBM->fetchRow($stmt);	
			
			if(empty($row)){
					
				////PDO QUERY//////

				$sql = "INSERT INTO site_traffics (USER_ID, TOPIC_ID, SECTION_ID, PAGE_ON_VIEW, IP, ARRIVAL_TIME, REFRESH_TIME) VALUES(?,?,?,?,?,NOW(),NOW())";
				$valArr = array($userId, $tid, $sid, $currentPage, $ip);
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			
			}else{	
			
				$stillOnPage = (strtolower($row["PAGE_ON_VIEW"]) == strtolower($currentPage));	
				////PDO QUERY////

				$sql = "UPDATE site_traffics SET TOPIC_ID = ?, SECTION_ID = ?, PAGE_ON_VIEW = ?, REFRESH_TIME = NOW()".($stillOnPage? '' : ', ARRIVAL_TIME = NOW()')."  WHERE (USER_ID = ?) LIMIT 1";
				$valArr = array($tid, $sid, $currentPage, $userId);
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
					
			}
		}
		
	}





	
	


				


		
	/*** Method for deleting collected site traffics after a specific idle time (cron job) ***/
	public function decollectSiteTraffic(){
					
		//////REFRESH EVERY 3MINUTES
		$interval = 3;
			
		///////////PDO QUERY////

		$sql = "DELETE FROM site_traffics WHERE (REFRESH_TIME + INTERVAL ? MINUTE) < NOW()";
		$valArr = array($interval);
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);	
		
		
	}





	
	
	


				


		
	/*** Method for displaying site traffics collected per page slug ***/
	public function displaySiteTraffic($name){
			
		$viewingPage=$guests=$members="";
		
		$currentPage = $this->ENGINE->get_page_path('page_url', 2, true);
			
		$guestCounter = 0;


		if(strtolower($name == "homepage")){
				
			///////PDO QUERY/////////
			
			$sql = "SELECT COUNT(*) FROM site_traffics WHERE USER_ID !=0";
			$valArr = array();
			$members = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
			
			if($members)
				$members .= ' member'.(($members > 1)? 's' : '');
			
			else
				$members = '';
				

			//////PDO QUERY//////
			
			$sql = "SELECT COUNT(*) FROM site_traffics WHERE USER_ID = 0";
			$valArr = array();
			$guests = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
			
			if($guests)
				$guests = '<b class="prime-2">'.$guests.' Guest'.(($guests > 1)? 's' : '').'</b>';
			
			else
				$guests = '';
			
			$guests = ($members && $guests)? ' and '.$guests : $guests;
			
			$viewingPage = "<div class='online-members'><h2 class='metered-title'>Members Online:</h2>(<b class='prime-1'>".$members."</b>".$guests." in the <b>last 5 minutes</b>)</div>";
				
		}else{
				
			///////////PDO QUERY/////////////
			
			/////CHECK IF THE REQUEST WAS MADE FROM USER-PROFILE PAGE AND RENDER DIFFERENT SQL/////////////////
			
			$sql = "SELECT * FROM site_traffics WHERE PAGE_ON_VIEW LIKE ? ORDER BY REFRESH_TIME DESC LIMIT 50";
			$valArr = array($currentPage);
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			
			while($row = $this->DBM->fetchRow($stmt)){
					
				$username = $recordCount = $this->ACCOUNT->memberIdToggle($row["USER_ID"]);
				
				if(!$username)
					$guestCounter++;
				
				else
					//$viewingPage .= $this->ACCOUNT->sanitizeUserSlug($username, array('anchor'=>true, 'gender'=>true)).', ';
					$viewingPage .= $this->ACCOUNT->getUserVCard($username, array('minVer'=>true));
						
				
			}	
			
				
			$viewingPage = trim($viewingPage, ", ");
			
			if($guestCounter)
				$guests = $guestCounter.' Guest'.(($guestCounter && $guestCounter > 1)? 's' : '');						
						
			$guests = ($viewingPage && $guests)? ' and <b class="prime-2">'.$guests.'</b>' : '<b class="prime-2">'.$guests.'</b>';
			
			if(isset($recordCount))
				$viewingPage = '<div class="online-members"><h2 class="metered-title">Viewing this '.$name.'</h2>['.$viewingPage.'<span class="">'.$guests.'</span>]</div>';
		
				
		}
			
			
		return $viewingPage;
			
		
	}




	 
	
	


				


		
	/*** Method for fetching floating to page top/bottom button ***/
	public function getFloatingPageSkipBtn(){
		
		$sessUsername = $this->SESS->getUsername();
		
		$ret =	'<div class="midpage-scroll-base">
					<div class="midpage-scroll no-hover-bg">
						<a class="topagedown" title="scroll to bottom of page" href="#"><img src="'.($K=$this->getMediaLinkRoot("favicon")).'scrolldown.png" alt="scroll to page bottom" /></a>
						<a class="topageup" title="scroll to top of page" href="#"><img src="'.$K.'scrollup.png" alt="scroll to page top" /></a>
					</div>
				</div>';
				
		if(!$this->SESS->getFloatingPageSkip() && $sessUsername)
			$ret = '';
		
		return $ret;
		
	}
 



 


				


		
	/*** Method for fetching pop out notifications ***/
	public function getPops($username="", $fixed="", $uniqueToken="", $retPopTarget='', $rdr=''){
		
		//////////GET DATABASE CONNECTION//////
		global $GLOBAL_rdr, $GLOBAL_mediaRootFav, $GLOBAL_isSeasonGreetings, $GLOBAL_isValentineGreetings;
		
		$sessUsername = $this->SESS->getUsername();
		$sessUid = $this->SESS->getUserId();
		$isStaff = $this->SESS->isStaff();
		$isSeasonGreetings = $GLOBAL_isSeasonGreetings;
		$isValentineGreetings = $GLOBAL_isValentineGreetings;
		$rdr = $this->ENGINE->sanitize_user_input(($rdr? $rdr : $GLOBAL_rdr), true);	
		!$username? ($username = $sessUsername) : '';	
		$uniqueId = ($uniqueToken? $uniqueToken.'-' : '').($sessUid? $sessUid : str_ireplace('.', '-', $this->ENGINE->get_ip()));	
		
		$pops=$fullName="";
		$isBirthday = $this->ACCOUNT->isUserBirthday($username);
		
		if($isBirthday)
			$fullName = $this->ACCOUNT->loadUser($username)->getFullName();
		
		////EXPECTED POP TARGETS///////	
		$modsPop = POP_FOR_MODERATORS;
		$birthdayPop = POP_FOR_BIRTHDAYS;
		$seasonPop = POP_FOR_SEASONS;
		$valentinePop = POP_FOR_VALENTINES;
		
		if($retPopTarget == $birthdayPop)
			$sql =  "SELECT * FROM pop_outs WHERE TARGET = '".$birthdayPop."'  AND STATE=1 LIMIT 1";
		
		else
			$sql =  "SELECT * FROM pop_outs WHERE STATE=1 ORDER BY RELEVANCE DESC, TIME DESC LIMIT 20";

		$valArr = array();
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			
		while($row = $this->DBM->fetchRow($stmt)){
	
			$id = $row["ID"];
			$popTarget = strtolower($retPopTarget? $retPopTarget : $row["TARGET"]);
			$tokenKey = hash('sha256', $uniqueId.'_seen-pop_'.$id.'_'.$this->ENGINE->sanitize_number($row["TIME"]).'_'.$this->ENGINE->get_date_safe('', 'Y'), false);
			$seenPop = isset($_COOKIE[$tokenKey]);
			$isBirthdayPop = ($fullName && ($popTarget == $birthdayPop));
			
			if($seenPop || ($popTarget == $modsPop && !$isStaff)
				|| ($popTarget == $seasonPop && !$isSeasonGreetings) || ($popTarget == $valentinePop && !$isValentineGreetings) 
				||	($popTarget == $birthdayPop && !$isBirthday)){
				
				if($retPopTarget)
					break;
				continue;
			}
	
			$gracefullyAcknowledge = ($isBirthdayPop | $isSeasonGreetings | $isValentineGreetings);
			$tokenKey = urlencode($tokenKey);
			$tokenName = 'pops';
			$incReminder = !$isBirthdayPop;
			$tokenReminder = 1;
			$tknUrlMetas = ' href="/set-si?tkn_name='.$tokenName.'&tkn_key='.$tokenKey.'&tkn_rmd='.$tokenReminder.'&_rdr='.$rdr.'" class="pop-seen btn btn-success box-close btn-classic" role="button" data-tkn-name="'.$tokenName.'" data-tkn-key="'.$tokenKey.'" data-tkn-rmd='.$tokenReminder;
			$popIcon = $isBirthdayPop? '<img '.BIRTHDAY_ICON.' />' : (($isSeasonGreetings || $isValentineGreetings)? ((/*$popTarget == $seasonPop &&*/ $isSeasonGreetings)? '<img '.SEASON_CARD.' />' : ((/*$popTarget == $valentinePop &&*/ $isValentineGreetings)? '<img '.VALENTINE_CARD.' />' : '' )) : '');
			$pops .= '<div class="pops-content" '.$this->embedCustomScrollbar().'>
						'.($isBirthdayPop? '<b>Dear <span class="bg-white prime">'.$fullName.'</spa></b>,' : '').
						'<div>'.$this->replaceDbPh($this->bbcHandler('', array('action' => 'decode', 'content' => $row["CONTENT"]))).'</div>
						<br/><a '.str_ireplace('rmd='.$tokenReminder, 'rmd=0', $tknUrlMetas).'>'.($gracefullyAcknowledge? 'Ok Thanks' : 'Got it <i class="fas fa-check"></i>').'</a>						
						'.($incReminder? '<a '.$tknUrlMetas.'>Remind me <i class="fas fa-clock"></i></a>' : '').'
					</div>';
	
			
	
			break;////GET THE ONES USER HAS'NT SEEN ONE AT A TIME//////
	
		}	
	
		$animCls = 'animated slideInUp ';
		$pops = $pops? '<div class="row"><div class="col-w-1-pull"><i class="fas fa-bell fa-2x"></i></div><div class="col-w-9-pull"><div class="side-thumbnail-base centered-inline">'.$popIcon.'</div>'.$pops.'</div></div>' : '';
		
		if($pops)
			$pops = '<div class="'.$animCls.(($fixed || !$isBirthdayPop)? 'center-pops' : 'relative').' box-shadow box-close-target"><div title="close" class="close">&times;</div>'.$pops.'</div>';
		
					
		return $pops;
			
	}


	


	

		
	/*** Method for checking if a url slug is requesting a dynamic page ***/
	public function isPageSlug($slug){
		
		$sql = "SELECT ID FROM pages WHERE (TITLE=? OR TITLE_SLUG=?) LIMIT 1";
		$valArr = array($slug, $slug);
		
		return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
	}


	

	

		
	/*** Method for checking if a url slug is requesting a community section ***/
	public function isSectionSlug($slug){
		
		$sql = "SELECT ID FROM sections WHERE (SECTION_NAME = ? OR SECTION_SLUG = ?) LIMIT 1";
		$valArr = array($slug, $slug);
		
		return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
			
	}


		

	

		
	/*** Method for checking if a url slug is requesting a community category ***/
	public function isCategorySlug($slug){
		
		$sql =  "SELECT ID FROM categories WHERE (CATEG_NAME = ? OR CATEG_SLUG = ?) LIMIT 1";
		$valArr = array($slug, $slug);
		
		return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
			
	}


	

	

		
	/*** Method for checking if a url slug is requesting a user profile ***/
	public function isProfileSlug($slug){
		
		if($slug){
			
			$sql =  "SELECT ID FROM users WHERE USERNAME = ? LIMIT 1";
			$valArr = array($slug);
			
			return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
			
		}
		
		return false;
			
	}


	
	
	

	 

					
	/*** Method for fetching community rules ***/
	public function getCommunityRules($skip='', $accordion=true){
		 
		 $siteName = $this->getSiteName();
		 
		 $communityRules =  ($accordion? '<div class="accordions" data-no-badge="true" data-collapsed="true" data-animate="fadeIn">
							<h3 class="accordion" ><b>**<span>'.strtoupper($siteName).' FORUM RULES</span>**</b>'.($skip? '(<a title="skip" href="#'.$skip.'" class="no-hover-bg" onclick="event.stopImmediatePropagation();"><img class="fav-img" src="'.$this->getMediaLinkRoot("favicon").'skip.png" alt="icon" /></a>)' : '').'</h3>
							<div class="accordion-panel" >' : '').'
								<ol class="ol align-l">
									<li>
										We are highly against <b class="red">SPAMMING</b> in this community, therefore all members are advised to 
										refrain from it under all circumstances.
									</li>   
									<li>
										Members are advised to create new threads/topics only within the sections that they best fit into.
									</li>   
									<li>
										Posts are to be made only in the right threads/topics and in a way that is consistent with "<b class="red">normal writing</b>".
										That is users should not post excessive numbers of emoticons, UI background images, large, small or coloured text, etc. 
									</li>   
									<li>
										This community is aimed at the general audience. Posting <b class="red">pornographic</b> or generally offensive
										text, images, links, etc in inappropriate sections will not be tolerated.
									</li>    
									<li>
										Members should not violate the <b class="red">privacy or copyright</b> of anybody by posting contents 
										relating to them without their consent.
									</li>   
									<li>
										Members should not use this community as an avenue for <b class="red">illegal acts such as; computer fraud, cyber bullying 
										or warfare, plagiarism, hacking, spreading computer virus</b>, etc.
									</li>   
									<li>
										Members are advised to <b class="red">post in English</b>, as this is an English speaking community.
									</li>   	
									<li>
										Members should use appropriate or descriptive subject when creating new threads/topics. 
									</li>   
									<li>
										The moderating, support and other teams of this community reserve the right to <b class="red">edit</b>,
										<b class="red">remove</b> or put on moderation queue any post or thread/topic at any time.
									</li>   
									<li>
										Members should not Abuse the private messaging system(PM) under any circumstances. It is <b class="red">not 
										a chat platform</b>. 
									</li>   
									<li>
										Members should treat all Moderators with optimum respect. Complaints to or against moderators must be sent
										privately. Please don\'t disobey, disrespect, or defame them. 
									</li>   
									<li>
										Members should not Flame or abuse, bully, deliberately threaten, insult, provoke, fight, or wish harm to 
										other members or their <b class="red"> RELIGION</b>, <b class="red">RACE or TRIBES</b>.
									</li>   
									<li>
										Members should desist from acting as “<b class="red">back seat moderators</b>”. If members note any issue 
										which contravenes this community rules, they are welcome to bring it to the attention of any member of 
										the Moderator Team. Do not respond to such issues yourself as members who constantly “<b class="red">act</b>”
										as moderators may be banned permanently. 
									</li>   
									<li>
										Members should use the “Flag” button  to <b class="red">report</b> posts that violates this community 
										rules.	 
									</li>   
									<li>
										Members\' Signatures may contain  up to two lines of text. Content in signatures should be brief, consistent with 
										normal writing and abide by the general community etiquette.
									</li>   
									<li>
										Members that are under a temporary ban should not try to <b class="red">circumvent</b> by either 
										<b class="red">re-registering under a new username or email</b> or by any other means.
									</li>   
									<li>
										Members are highly advised not to post <b class="red">false informations or contents</b> on this community 
										as those who constantly engage in this act may have their account terminated and ip permanently blocked 
										from accessing this community.
									</li>   
									<li>
										Members who feel they have been unfairly warned or treated badly by a member of the moderator team are
										welcome to contact the relevant team leader.
									</li>   
									<li>
										Members should not use this community as a platform to promote illegal deals or investments.
									</li>   
									<li>
										Members should <b class="red">NEVER use indecent or unpleasant images</b> as their avatar.
									</li>   	 
								</ol>'
						.($accordion?	'</div>
						</div>' : '');
				
				
				return $communityRules;
		 
		 
	}
	 

	 
	 
	 



	



	

		
	/*** Method for fetching number of records to display per page ***/
	public function getPaginationCount(){	
		
		if($K=$this->SESS->getMaxPaging())
			$ret = $K;
		
		else
			$ret = DEFAULT_PAGINATION_COUNT;
		
		return $ret;	
		
	}
	 

	 
	
	

		
	/*** Method for fetching count down clock html box ***/
	public function getCountDownClockBox($time, $metaArr=''){
		
		$expires='';

		$hideTgt = $this->ENGINE->get_assoc_arr($metaArr, "hideTgt");
		$showTgt = $this->ENGINE->get_assoc_arr($metaArr, "showTgt");
		$timerStyle = $this->ENGINE->get_assoc_arr($metaArr, "timerStyle");
		$hideIgnore = (bool)$this->ENGINE->get_assoc_arr($metaArr, "hideIgnore");
		$timerBasic = (bool)$this->ENGINE->get_assoc_arr($metaArr, "timerBasic");		
		
		if($time){
		
			list($bd, $bh, $bm, $bs) = $this->ENGINE->time_difference('', $time);	
		
			$expires = '<div class="red bold">'.
							(
							$bd.' day'.(($bd > 1)? 's' : '').
							$bh.' hour'.(($bh > 1)? 's' : '').
							$bm.' minute'.(($bm > 1)? 's' : '').
							$bs.' second'.(($bs > 1)? 's' : '')
							)
						.'</div>';

			$options = '{	
							"timer" : {"time" : "'.strtotime($time).'", "basic" : "'.$timerBasic.'", "style" : "'.$timerStyle.'"}, 
							"after" : {
								"hide" : {"tgt" : "'.$hideTgt.'", "ignore" : "'.$hideIgnore.'"}, 
								"show" : {"tgt" : "'.$showTgt.'"}
							}
						}';

			$options = $this->ENGINE->jsonify($options);
		
			$expires = '<div data-count-down="'.$options.'"></div><noscript>'.$expires.'</noscript>';
		}
		
		return $expires;
		
	}

	
	
		
	/*** Method for embedding run by ajax metas ***/
	public function runByAjax($metaArr=''){
				
		$retType = $this->ENGINE->get_assoc_arr($metaArr, "retType");
		$reloadUrl = $this->ENGINE->get_assoc_arr($metaArr, "reloadUrl");
		$runBox = $this->ENGINE->get_assoc_arr($metaArr, "runBox");
		$reloadBox = $this->ENGINE->get_assoc_arr($metaArr, "reloadBox");
		$keepDft = (bool)$this->ENGINE->get_assoc_arr($metaArr, "keepDft");
		$preloader = (bool)$this->ENGINE->get_assoc_arr($metaArr, "preloader");		
		$resetForm = (bool)$this->ENGINE->get_assoc_arr($metaArr, "resetForm");				
		
		$options = '{	
			"reloadUrl" : "'.$reloadUrl.'", "reloadBox" : "'.$reloadBox.'",
			"runBox" : "'.$runBox.'", "retType" : "'.$retType.'",
			"keepDft" : "'.$keepDft.'", "preloader" : "'.$preloader.'",
			"resetForm" : "'.$resetForm.'"
		}';

		/*
			,
			"runBox" : "'.$runBox.'", "retType" : "'.$retType.'",
			"keepDft" : "'.$keepDft.'", "preloader" : "'.$preloader.'",
			"resetForm" : "'.$resetForm.'"

		*/

		$options = $this->ENGINE->jsonify($options);
		
		return 'data-run-by-ajax="'.$options.'"';
		
	}
	


	
	

		
	/*** Method for fetching widgets ***/
	public function getWidget($sid, $cid="", $tid="", $postWidget=true){	
		
		global $FORUM, $GLOBAL_mediaRootFav, $GLOBAL_isXmas, $GLOBAL_isNewYear, $showSeasonCards;
		
		$festiveCards = (($GLOBAL_isXmas || $GLOBAL_isNewYear) && $showSeasonCards)? '<div class="side-widget widget gcard zoom-ctrl"><img '.XMAS_CARD.' /><img '.NEW_YEAR_CARD.' /></div>' : '';
		
		$adsCampaign = new AdsCampaign();
		$section_ads_arr = $adsCampaign->getSectionAds($sid);

		$pageTopAds = isset($section_ads_arr[$K="topAds"])? $section_ads_arr[$K] : '';
		
		$pageBottomAds = isset($section_ads_arr[$K="bottomAds"])? $section_ads_arr[$K] : '';
		
		$adsT2 = isset($section_ads_arr[$K="adsT2"])? $section_ads_arr[$K] : '';
		
		$adsT3 = isset($section_ads_arr[$K="adsT3"])? $section_ads_arr[$K] : '';
		
		$adsT4 = isset($section_ads_arr[$K="adsT4"])? $section_ads_arr[$K] : '';
		
		
		list($trending, $trendingMob) = $FORUM->randomTopics($cid, $tid, $limit=10, $trend=true);
		list($random, $randomMob) = $FORUM->randomTopics($cid, $tid, $limit=10);
		list($related, $relatedMob) = $FORUM->relatedTopics($sid, $tid, $forceMob=false, $forceInl=false, $onlyDsk=false, $limit=10);
		$mobDpnTabs = ($trendingMob || $randomMob || $relatedMob)? 
						'<div class="mob-platform-dpn"><nav class="nav-base">
							<ul class="nav nav-tabs justified-center tab-basicx justified justified-bomx" data-open-active-mob="true">			
								'.($relatedMob? '<li><a class="" '.(($trendingMob || $randomMob)? '' : ' data-default-tab="true" ').' data-toggle="tab-tab">Related</a></li>' : '')
								.($trendingMob? '<li><a class="" data-default-tab="true" data-toggle="tab-tab">Trending</a></li>' : '')	
								.($randomMob? '<li><a class="" '.((!$trendingMob && $randomMob)? ' data-default-tab="true" ' : '').' data-toggle="tab-tab">Interesting</a></li>' : '').'
							</ul>
							<div class="tab-contents sides-padless" data-animate="">  				
								'.($relatedMob? '<div class="tab-content">'.$relatedMob.'</div>' : '')	
								.($trendingMob? '<div class="tab-content">'.$trendingMob.'</div>' : '')	
								.($randomMob? '<div class="tab-content">'.$randomMob.'</div>' : '').'	
							</div>
						</nav></div>' : '';
		$contactBtn = '<div class="side-widget"><a title="contact us" role="button" href="/contact-us" class="no-hover-bg"><img class="img-responsive" src="'.$GLOBAL_mediaRootFav.'contact-us-btn.png" alt="contact us" /></a></div>';
		$tmp_arr = array($adsT2, $adsT3);
		shuffle($tmp_arr);
		
		$mobDpnAds = (getAutoPilotState("MOBILE_SIDE_ADS-SHUFFLE"))? '<div class="mob-platform-dpn banner-screen-dpn">'.implode("", $tmp_arr).'</div>' : '';
		
		$w = '<aside class="'.($postWidget? R_WIDGET_POST_CTRL : R_WIDGET).'">
					<div class="dsk-platform-dpn">'.$related.$festiveCards.$adsT2.$contactBtn.$adsT3.$trending.'</div>'.
						$mobDpnTabs.$mobDpnAds.$this->loadExternalWidget('', true).
						$this->getSocialLinks(array('type'=>'followus','widget'=>true)).$adsT4.' 												
					<div class="dsk-platform-dpn">'.$random.'</div>
				</aside>';
			
		return array($w, $pageTopAds, $pageBottomAds, ($postWidget? L_WIDGET_POST_CTRL : L_WIDGET));
		
	}


	
	
	
	

		
	/*** Method for loading external plugin widgets ***/
	public function loadExternalWidget($widgetAutopilotName='', $forSideWidget = false){
				
		global $siteName;
		!$widgetAutopilotName? ($widgetAutopilotName = 'LIVE_FOREX_RATES') : '';		
		$widget = getAutoPilotState($widgetAutopilotName);
		$widgetSiteName = $this->ENGINE->extract_domain_name($widget);		

		$widget = '<div class="dsk-platform-dpn '.($forSideWidget? 'side-widget widget' : '').'">'
						.str_ireplace('<iframe ', '<iframe title="'.$siteName.' '.$widgetSiteName.' widget" data-external-widget="true" ', $widget).
					'</div>';

		return $widget;
		
	}
	
	

		
	/*** Method for fetching social handle plugins ***/
	public function getSocialHandlePlugins($retTypeArr=array('all')){
		
		global $siteName;
		$socialHandlesArr = explode(',', getAutoPilotState('LIVE_SOCIAL_HANDLES'));
		$acc='';
		foreach($socialHandlesArr as $handle){
			
			$socialSiteName = $this->ENGINE->extract_domain_name($handle);
			
			if(strtolower($retTypeArr[0]) == 'all' || in_array($socialSiteName, $retTypeArr))
				$acc .= str_ireplace('<iframe ', '<iframe class="dsk-platform-dpn-i" title="'.$siteName.' '.$socialSiteName.' Page" ', $handle);
			
		}
		
		return $acc;
		
	}




	


		
	/*** Method for fetching social site url ***/
	public function getSocialLinks($meta){
		
		$forPost=$forTopic=$forFollowUs=$widget=false;
		
		switch(strtolower($meta['type'])){
		
			case 'post': $forPost = true; break;
		
			case 'followus': $forFollowUs = true; $widget = isset($meta[$K="widget"])? $meta[$K] : false; break;
		
			default: $forTopic = true;
		
		}
		
		$term = $forPost? 'post' : 'topic';
		$scmn = ' target="_blank"  rel="nofollow noopener" ';
		$scicmn = 'soc-icon';
		$c1 = 'soc-sharer'; $c2 = 'soc-count';
		$alternate = isset($meta[$K="alt"])? $meta[$K] : false;
		$size = isset($meta[$K="size"])? $meta[$K] : '';
		$size = $widget? 'lg' : $size;
		
		$FA_ST = ' fa-inverse fa-stack-1x ';
		$FA_SB = ($alternate? ' fa-circle ' : ' fa-square ').' fa-stack-2x ';
		
		$socFA_arr = array(array($FA_SB, "fab fa-google-plus-g".$FA_ST), array($FA_SB, "fab fa-facebook-f".$FA_ST), 
							array($FA_SB, "fab fa-twitter".$FA_ST), array($FA_SB, "fab fa-whatsapp mobile".$FA_ST), 
							array($FA_SB, "fab fa-instagram".$FA_ST), array($FA_SB, "fab fa-linkedin-in".$FA_ST),
							array($FA_SB, "fab fa-telegram".$FA_ST), array($FA_SB, "fab fa-pinterest".$FA_ST), 
							array($FA_SB, "fa-envelope".$FA_ST));
		
		list($FA_gp, $FA_fb, $FA_twt, $FA_wa, $FA_in, $FA_ldn, $FA_tel, $FA_pin, $FA_envlope) =  $this->getFA($socFA_arr, array("size"=>$size));
		
		$gp = ' googleplus';
		$fb = ' facebook';
		$tw = ' twitter';
		$in = ' instagram';
		$pin = ' pinterest';
		$ldn = ' linkedin';
		$wa = ' whatsapp';
		$tel = ' telegram';
		$em = ' mail';
		
		if($forFollowUs){
		
			$fbUrl = SITE_FACEBOOK_URL_STR;
			$twUrl = SITE_TWITTER_URL_STR;
			$inUrl = SITE_INSTAGRAM_URL_STR;
			$links = '<a class="'.$scicmn.$fb.'" href="'.$fbUrl.'" title="'.($K='follow us on facebook').'" >'.$FA_fb.'</a>
					<a class="'.$scicmn.$tw.'" href="'.$twUrl.'" title="'.($K='follow us on twitter').'"  >'.$FA_twt.'</a>
					<a class="'.$scicmn.$in.'" href="'.$inUrl.'" title="'.($K='follow us on instagram').'" >'.$FA_in.'</a>';	
				
			$links = '<div class="socials">'.
							($widget? 
								'<div class="follow-us dsk-platform-dpn side-widget widget no-hover-bg">
									<h2 class="page-title head-bg-classic-r pan bg-orange">Follow Us On </h2>
									<div class=""><h2>'.$links.'</h2></div>
								</div>' 
								
								: 								
								
								'<p>'.$links.'</p>'
							).
					'</div>';
		
			return $links;
			
		}
		
		$preQstr = '/ss?ref_t='.$meta["tid"].(isset($meta["pid"])? '&ref_p='.$meta["pid"] : '');
		$siteDomain = $this->ENGINE->get_domain();
		$siteName = $this->getSiteName();
		$subject = $topic_name = ucwords($meta["tname"]);
		$topic_slug = $meta["tslug"];
		$url = $siteDomain.'/'.ltrim($topic_slug, '/');
		$summary = 'I came across this '.$term.' on '.$siteName.' and thought you would like it. Check it out on '.$url;
		$rdr = $meta["rdr"];
		
		return '<h3 class="'.($forPost? 'page-title' : 'metered-title').' align-c">SHARE THIS '.strtoupper($term).' ON</h3>
				<div class="socials '.($forPost? '' : 'thread-soc').' no-hover-bg">
					<div class="'.$c1.'"><span class="'.$c2.$gp.'">('.(isset($meta[$K="GP_COUNTS"])? $this->ENGINE->format_number($meta[$K]) : 0).')</span><a '.$scmn.' class="'.$scicmn.$gp.'" href="'.$preQstr.'&ref_ssn='.urlencode(GP_SOCIAL).'&loc='.urlencode('https://plus.google.com/share?url='.$url).'&_rdr='.$rdr.'" title="Share this '.$term.' on google plus" >'.$FA_gp.'</a></div>
					<div class="'.$c1.'"><span class="'.$c2.$fb.'">('.(isset($meta[$K="FB_COUNTS"])? $this->ENGINE->format_number($meta[$K]) : 0).')</span><a '.$scmn.' class="'.$scicmn.$fb.'" href="'.$preQstr.'&ref_ssn='.urlencode(FB_SOCIAL).'&loc='.urlencode('https://www.facebook.com/sharer.php?u='.$url).'&_rdr='.$rdr.'" title="Share this '.$term.' on facebook" >'.$FA_fb.'</a></div>
					<div class="'.$c1.'"><span class="'.$c2.$tw.'">('.(isset($meta[$K="TW_COUNTS"])? $this->ENGINE->format_number($meta[$K]) : 0).')</span><a '.$scmn.' class="'.$scicmn.$tw.'" href="'.$preQstr.'&ref_ssn='.urlencode(TW_SOCIAL).'&loc='.urlencode('https://twitter.com/intent/tweet?url='.$url.'&text='.$summary.'&via='.$siteName).'&_rdr='.$rdr.'" title="Tweet this '.$term.' on twitter" >'.$FA_twt.'</a></div>
					<div class="'.$c1.'"><span class="'.$c2.$pin.'">('.(isset($meta[$K="PIN_COUNTS"])? $this->ENGINE->format_number($meta[$K]) : 0).')</span><a '.$scmn.' class="'.$scicmn.$pin.'" href="'.$preQstr.'&ref_ssn='.urlencode(PIN_SOCIAL).'&loc='.urlencode('https://pinterest.com/pin/create/button/?url='.$url.'&media=&description='.$summary).'&_rdr='.$rdr.'" title="Share this '.$term.' on pinterest" >'.$FA_pin.'</a></div>
					<div class="'.$c1.'"><span class="'.$c2.$ldn.'">('.(isset($meta[$K="LDN_COUNTS"])? $this->ENGINE->format_number($meta[$K]) : 0).')</span><a '.$scmn.' class="'.$scicmn.$ldn.'" href="'.$preQstr.'&ref_ssn='.urlencode(LDN_SOCIAL).'&loc='.urlencode('https://www.linkedin.com/shareArticle?mini=true&url='.$url.'&title='.$subject.'&summary='.$summary.'&source='.$siteName).'&_rdr='.$rdr.'" title="Share this '.$term.' on linkedin" >'.$FA_ldn.'</a></div>
					<div class="'.$c1.'"><span class="'.$c2.$wa.'">('.(isset($meta[$K="WA_COUNTS"])? $this->ENGINE->format_number($meta[$K]) : 0).')</span><a '.$scmn.' class="'.$scicmn.$wa.'" href="'.$preQstr.'&ref_ssn='.urlencode(WA_SOCIAL).'&loc='.urlencode('whatsapp://send?text='.$siteName.': '.$subject.'-'.$url).'&_rdr='.$rdr.'" title="Share this '.$term.' on whatsapp" >'.$FA_wa.'</a></div>																	
					<div class="'.$c1.'"><span class="'.$c2.$tel.'">('.(isset($meta[$K="TEL_COUNTS"])? $this->ENGINE->format_number($meta[$K]) : 0).')</span><a '.$scmn.' class="'.$scicmn.$tel.'" href="'.$preQstr.'&ref_ssn='.urlencode(TEL_SOCIAL).'&loc='.urlencode('https://telegram.me/share/url?url='.$url.'&text='.$siteName.': '.$subject).'&_rdr='.$rdr.'" title="Share this '.$term.' on telegram" >'.$FA_tel.'</a></div>																	
					<div class="'.$c1.'"><span class="'.$c2.$em.'">('.(isset($meta[$K="EM_COUNTS"])? $this->ENGINE->format_number($meta[$K]) : 0).')</span><a '.$scmn.' class="'.$scicmn.$em.'" href="'.$preQstr.'&ref_ssn='.urlencode(EM_SOCIAL).'&loc='.urlencode('mailto:?&subject='.$subject.'&body='.$summary).'&_rdr='.$rdr.'" title="Mail this '.$term.' to a friend" >'.$FA_envlope.'</a></div>
				</div>';
				
		
	}

	

	

		
	/*** Method for fetching social site counts sub query ***/
	public function getSocialQuery($type=1){
		
		$forTopic = ($type == 1);
		$table1 = ($forTopic? 'topic' : 'post').'_social_shares';
		$table2 = $forTopic? 'topics' : 'posts';
		$preQry = "(SELECT COUNT(*) FROM ".$table1." WHERE ".$table2.".ID = ".$table1.".".($forTopic? "TOPIC" : "POST")."_ID AND SOCIAL_SITE = ";
		
		return ($forTopic? "" : "(SELECT COUNT(*) FROM ".$table1." WHERE ".$table2.".ID = ".$table1.".POST_ID) AS SOCIAL_COUNTS,").
				$preQry."'".GP_SOCIAL."') AS GP_COUNTS,".
				$preQry."'".FB_SOCIAL."') AS FB_COUNTS,".
				$preQry."'".TW_SOCIAL."') AS TW_COUNTS,".
				$preQry."'".PIN_SOCIAL."') AS PIN_COUNTS,".
				$preQry."'".LDN_SOCIAL."') AS LDN_COUNTS,".
				$preQry."'".WA_SOCIAL."') AS WA_COUNTS,".
				$preQry."'".TEL_SOCIAL."') AS TEL_COUNTS,".
				$preQry."'".EM_SOCIAL."') AS EM_COUNTS";
				
	}
	

	

	

		
	/*** Method for building database query for a specific task type ***/
	public function composeQuery($metaArr){
		
		$isStaff = $this->SESS->isStaff();
		
		$type = $metaArr["type"];
		$subType = isset($metaArr[$K="subType"])? strtolower($metaArr[$K]) : '';
		$startIndex = isset($metaArr[$K="start"])? $metaArr[$K] : '';;
		$stopLimit = isset($metaArr[$K="stop"])? $metaArr[$K] : '';;
		$uniqueColumns = isset($metaArr[$K="uniqueColumns"])? $metaArr[$K] : '';
		$filterCnd = isset($metaArr[$K="filterCnd"])? $metaArr[$K] : '';
		$primaryTable = isset($metaArr[$K="primaryTable"])? $metaArr[$K] : '';
		$primaryJoinCnd = isset($metaArr[$K="primaryJoinCnd"])? $metaArr[$K] : '';
		$topicsTableJoinCnd = isset($metaArr[$K="topicsTableJoinCnd"])? $metaArr[$K] : '';
		$sectionsTableJoinCnd = isset($metaArr[$K="sectionsTableJoinCnd"])? $metaArr[$K] : '';
		$categoriesTableJoinCnd = isset($metaArr[$K="categoriesTableJoinCnd"])? $metaArr[$K] : '';
		$orderBy = isset($metaArr[$K="orderBy"])? $metaArr[$K] : '';
		$exceptions = isset($metaArr[$K="exceptions"])? $metaArr[$K] : false;
		$ignoreCmnCols = isset($metaArr[$K="ignoreCmnCols"])? $metaArr[$K] : false;
		$postColsOnly = isset($metaArr[$K="postColsOnly"])? $metaArr[$K] : false;
		$loadAll = isset($metaArr[$K="loadAll"])? $metaArr[$K] : false;
		$countColumn = isset($metaArr[$K="countColumn"])? $metaArr[$K] : '';
		$recordCountAlias = isset($metaArr[$K="recordCountAlias"])? $metaArr[$K] : '';
		

		switch(strtolower($type)){
			
			case 'for_topic':
				
				$trendingCount = 'record_count_trending';
				$isTrendingCount = ($subType == $trendingCount);
				
				$lastPostTimeCol = "(SELECT TIME FROM posts WHERE topics.ID = posts.TOPIC_ID ORDER BY TIME DESC LIMIT 1)";
				
				$topicsCmnCols = "topics.*, SECTION_NAME, DATEDIFF(NOW(), topics.TIME) AS DAYS_OLD, ".$lastPostTimeCol." AS LAST_POST_TIME, 
				(SELECT POST_AUTHOR_ID FROM posts WHERE topics.ID = posts.TOPIC_ID ORDER BY TIME DESC LIMIT 1) AS LAST_POST_AUTHOR_ID,
				(SELECT COUNT(*) FROM posts WHERE topics.ID = posts.TOPIC_ID) AS TOTAL_POSTS,
				(SELECT COUNT(*) FROM topic_views WHERE topics.ID = topic_views.TOPIC_ID) AS TOPIC_VIEWS,
				SECTION_ID, CATEG_ID, SECTION_NAME, CATEG_NAME".(
				$loadAll? ", ".$this->getSocialQuery().", 
					(SELECT REASON FROM activity_logs WHERE (TYPE='t' AND TYPE_ID=topics.ID AND ACTIVITY RLIKE '^closed') ORDER BY TIME DESC LIMIT 1) AS MOD_REASON"
					: ""
				);
				
				$topicXcept = ($exceptions && !$isStaff)?  ' AND SECTION_ID NOT IN('.SIDS_STAFF_ONLY.') ' : '';
				
				$qryFilterCnd = " WHERE ".($filterCnd? $filterCnd : "topics.ID = ?").$topicXcept;
				$trendingCnd = "WHERE (".$lastPostTimeCol." >= (NOW() - INTERVAL 1 MONTH) ".$topicXcept.")";
				$isTrendingCount? ($qryFilterCnd = $trendingCnd) : '';
				
				$qryJoinCnd = " FROM ".($primaryTable? $primaryTable." JOIN " : "")." topics ".($primaryJoinCnd? " ON ".$primaryJoinCnd : "")."
				JOIN sections ON ".($sectionsTableJoinCnd? $sectionsTableJoinCnd : "topics.SECTION_ID=sections.ID")."
				JOIN categories ON ".($categoriesTableJoinCnd? $categoriesTableJoinCnd : "sections.CATEG_ID=categories.ID ")." ";
					
				switch($subType){
					
					case 'record_count':
					case $trendingCount:
						$sql = "SELECT COUNT(".($countColumn?  $countColumn : "*").") AS ".($recordCountAlias?  $recordCountAlias : "TOTAL_RECS")." FROM topics ".$qryFilterCnd;
						break;
				
					case 'trending':
						$threadUpvotesSubQry = ",(SELECT COUNT(*) FROM upvotes JOIN posts ON upvotes.POST_ID = posts.ID WHERE posts.TOPIC_ID = topics.ID) AS THREAD_UPVOTES";	
						
						//(ONE UPVOTE TO TIME RATIO) IN SECS  
						$upvoteToTimeRatio = 45000;
						
						//log10(1 + 1) logarithm of one vote | NOTE: to check logarithm of 0 we added 1
						$trendMultiplier = round($upvoteToTimeRatio / log10(2));
						
						$sql = "SELECT ".$topicsCmnCols.$threadUpvotesSubQry.$qryJoinCnd.$trendingCnd."
						ORDER BY (LOG10(THREAD_UPVOTES + 1) * ".$trendMultiplier." + UNIX_TIMESTAMP(topics.TIME)) DESC
						LIMIT ".($startIndex? $startIndex.", " : "").($stopLimit? $stopLimit : 20);
						break;
				
					default:
						$sql = "SELECT ".($ignoreCmnCols? "" : $topicsCmnCols).($uniqueColumns? ($ignoreCmnCols? "" : ", ").$uniqueColumns : "").
						$qryJoinCnd.$qryFilterCnd." ORDER BY ".($orderBy? $orderBy : "TIME DESC")." 
						LIMIT ".($startIndex? $startIndex.", " : "").($stopLimit? $stopLimit : 20);
					
				}
				
				break;			
			
			case 'for_post':
			
			default: 
			
				$postUploadsColSubQry = "(SELECT GROUP_CONCAT(FILE SEPARATOR '".GRPSEP."') FROM post_uploads pu WHERE posts.ID = pu.POST_ID) AS UPLOADS,
										(SELECT GROUP_CONCAT(ORIGINAL_FILE SEPARATOR '".GRPSEP."') FROM post_uploads pu WHERE posts.ID = pu.POST_ID) AS UPLOADS_ORIGINAL_NAMES";
			
				$postCmnCols = "posts.*, ".$postUploadsColSubQry.
				($postColsOnly? "" : ", TOPIC_ID, SECTION_ID, CATEG_ID, TOPIC_NAME, SECTION_NAME, CATEG_NAME, PROTECTION_LEVEL, ".
					$this->getSocialQuery(2).", (SELECT COUNT(*) FROM reported_posts WHERE posts.ID = reported_posts.POST_ID ) AS FLAG_COUNTS "
				);
			
				$postXcept = ($exceptions && !$isStaff)? ' AND posts.TOPIC_ID NOT IN((SELECT ID FROM topics WHERE SECTION_ID IN('.SIDS_STAFF_ONLY.'))) ' : '';
				
				$qryFilterCnd = " WHERE ".($filterCnd? $filterCnd : "TOPIC_ID = ?").$postXcept;
				
				$qryJoinCnd = " FROM ".($primaryTable? $primaryTable." JOIN " : "")." posts ".($primaryJoinCnd? " ON ".$primaryJoinCnd : "")."
				JOIN topics ON ".($topicsTableJoinCnd? $topicsTableJoinCnd : "posts.TOPIC_ID=topics.ID")." 
				JOIN sections ON ".($sectionsTableJoinCnd? $sectionsTableJoinCnd : "topics.SECTION_ID=sections.ID")."
				JOIN categories ON ".($categoriesTableJoinCnd? $categoriesTableJoinCnd : "sections.CATEG_ID=categories.ID ")." ";
				
				switch($subType){
					
					case 'record_count':
						$sql = "SELECT COUNT(".($countColumn?  $countColumn : "*").") AS ".($recordCountAlias?  $recordCountAlias : "TOTAL_RECS")." FROM posts ".$qryFilterCnd;
						break;
				
					default:
						$sql = "SELECT ".($ignoreCmnCols? "" : $postCmnCols).($uniqueColumns? ($ignoreCmnCols? "" : ", ").$uniqueColumns : "").
						$qryJoinCnd.$qryFilterCnd." ORDER BY ".($orderBy? $orderBy : "TIME, ID ASC")." 
						LIMIT ".($startIndex? $startIndex.", " : "").($stopLimit? $stopLimit : 20);
					
				}
				
				
		}
		
		return $sql;
		
	}
	

		
	

		
	

		
	/*** Method for checking if email address exist in a specific database table ***/
	public function emailExist($em, $table=''){
		
		!$table? ($table = 'users') : '';
		$sql = "SELECT ID FROM ".$table." WHERE EMAIL=? LIMIT 1";
		$valArr = array($em);
		return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
	}



	
	

	

		
	/*** Method for checking if emoticon ui code or unicode source exist ***/
	public function emoticonValExist($param, $checkUiCode=false){
		
		$table = 'emoticons';
		$sql = "SELECT ID FROM ".$table." WHERE ".($checkUiCode? "UI_CODE" : "UNICODE_SRC")."=? LIMIT 1";
		$valArr = array($param);
		return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
	}

	
	

	

		
	/*** Method for checking if country name exists in our table of countries ***/
	public function countryValExist($param){
		
		$table = 'countries';
		$sql = "SELECT ID FROM ".$table." WHERE NAME=? LIMIT 1";
		$valArr = array($param);
		return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
	}



	
	

	

		
	/*** 
	 	Method for checking for page slug conflict; 	 
		To avoid conflict btwn user slugs and virtual slugs, 
		we must keep track of all virtual slugs
		and check usernames against it during signup 
	 ***/
	public function pageSlugConflicts($slug){

		return $this->urlIsPingable($slug);
		
	}





	

	

		
	/*** Method for refreshing page after form submit to clear the POST/GET variable thus avoiding duplicate actions ***/
	public function formGateRefresh($info="", $rdr="", $hash="", $noQstr=true){
		
		$form="";
		global $GLOBAL_page_self;
		
		$info_arr = (!is_array($info))? ((array)$info) : $info;
		
		if(count($info_arr) >= 2)
			list($info, $form) = $info_arr;
		
		else
			list($info) =  $info_arr;
		
		if(!$rdr)
			$rdr = $GLOBAL_page_self;
		
		if($noQstr)
			$rdr = preg_replace("#\?.*#", "", $rdr).($hash? '#'.trim($hash, '#') : '');
		
		$_SESSION["ENGINE_SESS_RMB_BYPASS"] = true;
		$info?  ($_SESSION["form_gate_response"] = $info) : "";
		$form?  ($_SESSION["form_gate_response_form"] = $form) : "";
		
		////// REDIRECT TO AVOID PAGE REFRESH DUPLICATE ACTION//////////
		
		header("Location:/form-gate?_rdr=".urlencode($rdr));
		//header("Refresh:0;url=/".$rdr);
		//header("Location:/form-gate?_rdr=".urlencode($rdr));
		//header("Refresh:0;url=/form-gate?_rdr=".urlencode($rdr));
		exit();

		
	}


	

	

		
	/*** Method for fetching form gate refresh response ***/
	public function formGateRefreshResponse($ret_arr=false){
		
		$info=$form="";
		//////////GET FORM-GATE RESPONSE//////////
		if(isset($_SESSION[$K="form_gate_response"])){
			
			$info = $_SESSION[$K];				
			unset($_SESSION[$K]);	
			
		}
		
		if(isset($_SESSION[$K="form_gate_response_form"])){
			
			$form = $_SESSION[$K];
			unset($_SESSION[$K]);
			
		}	
		
		unset($_SESSION["ENGINE_SESS_RMB_BYPASS"]);
		
		return (($ret_arr)? array($info, $form) : $info);
		
	}


	

	

		
	/*** Method for logging site authentication code to database ***/
	public function logAuthentication($uid, $type, $code, $validMinutes=1440){
		
		$U = $this->ACCOUNT->loadUser($uid);
		$uid = $U->getUserId();
		
		/////////PDO QUERY////
		
		$sql =  "INSERT INTO authentications (USER_ID, TYPE, VALID_MINUTES, CODE) VALUES(?,?,?,?)";	
		$valArr = array($uid, $type, $validMinutes, $code);
		return $this->DBM->doSecuredQuery($sql, $valArr);	

	}
	
		
	

	
	
		
	/*** Method for relogging site authentication code to database ***/
	public function relogAuthentication($uid, $type, $code){
		
		$U = $this->ACCOUNT->loadUser($uid);
		$uid = $U->getUserId();
		
		/////////PDO QUERY////
		
		$sql =  "SELECT ID from authentications WHERE (USER_ID=? AND TYPE=?) LIMIT 1";	
		$valArr = array($uid, $type);
		
		if($this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn()){
			
			$sql =  "UPDATE authentications SET CODE=? WHERE (USER_ID=? AND TYPE=?) LIMIT 1";	
			$valArr = array($code, $uid, $type);
			return $this->DBM->doSecuredQuery($sql, $valArr);
			
		}else
			return $this->logAuthentication($uid, $type, $code);
		
		
	}




		

	
	
		
	/*** Method for fetching site authentication code from database ***/
	public function getAuthentication($uid, $type){
		
		$U = $this->ACCOUNT->loadUser($uid);
		$uid = $U->getUserId();
		
		/////////PDO QUERY////
		
		$sql =  "SELECT CODE FROM authentications WHERE (USER_ID=? AND TYPE=?) ORDER BY TIME DESC LIMIT 1";	
		$valArr = array($uid, $type);
		return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
	}

	
		
	
		

	
	
		
	/*** Method for expiring site authentication code in database ***/
	public function expireAuthentication($uid='', $type=''){
		
		if($uid && $type){
			
			$U = $this->ACCOUNT->loadUser($uid);
			$uid = $U->getUserId();
			
			$sql =  "DELETE FROM authentications WHERE (USER_ID=? AND TYPE=?) LIMIT 1";
			$valArr = array($uid, $type);
			
		}else{
			
			$sql =  "DELETE FROM authentications WHERE (TIME + INTERVAL VALID_MINUTES MINUTE) <= NOW()";	
			$valArr = array();
			
		}
		
		return ($this->DBM->doSecuredQuery($sql, $valArr));

	}


	
		

	
	
		
	/*** Method for logging site moderators activities into database ***/
	public function logActivity($type, $id, $a, $r='', $sid=0){
		
		global $FORUM;
		
		/********
		@param $type
		c  for category activities
		s  for section activities
		t  for topic activities
		
		********/
		
		switch(strtolower($type)){
			
			case 't': $sid = $sid? $sid : $FORUM->getTopicDetail($id); break;
			
			default: $sid = 0;
			
		}
		/////////PDO QUERY////
		$sql =  "INSERT INTO activity_logs (USER_ID, SID, TYPE, TYPE_ID, ACTIVITY, REASON) VALUES(?,?,?,?,?,?)";
		$valArr = array($this->SESS->getUserId(), $sid, $type, $id, $a, $r);
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);

	}

	
		

	
	
		
	/*** Method for clearing site moderators activities from database ***/
	public function cleanActivityLog($t, $id='', $sixOld=false){
		
		$sql=$subQry='';
		$subQry = ($sixOld)? 'AND (TIME + INTERVAL 6 MONTH) <= NOW()' : '';
		$valArr = array($t, $id);
		
		/////////PDO QUERY////
		if($t && $id)
			$sql =  "DELETE FROM activity_logs WHERE (TYPE=? AND TYPE_ID=? ".$subQry.")";
		
		elseif($t){
			
			$sql =  "DELETE FROM activity_logs WHERE TYPE=? ".$subQry;
			$valArr = array($t);
			
		}
		
		if($sql){
			
			return $this->DBM->doSecuredQuery($sql, $valArr);
			
		}else
			return false;

	}
	
		
	
	
		

	
	
		
	/*** Method for fetching site resource base root ***/
	public function getResourceRoot($repo = ''){

		is_null($repo)? ($repo = 'self') : '';
		
		return ((strtolower($repo) == 'dev')? 'http://dev-resources.test/.libs/' : $this->ENGINE->get_domain().'/'.ASSET_PREFIX);


	}
		
	
	
		

	
	
		
	/*** Method for assigning/updating site resource cache version ***/
	public function getCacheVer($rsrc='css', $qstrKey='?_ui_v=', $hashAlgo='sha256', $hash=true){
		
		switch(strtolower($rsrc)){
			
			case 'css': $ver = '1.2.b8'; break;
			
			case 'js': $ver = '1.4.c8'; break;
			
			case 'tools-css': $ver = '1.0.3'; break;
			
			case 'tools-js': $ver = '1.0.2'; break;
			
			case 'ft-js': $ver = '1.0.a5'; break;
			
			default: $ver = '1.0.7';
			
		}
		
		return $qstrKey.($hash? hash($hashAlgo, $ver, false) : $ver);
		
	}

	
		

	
	
		
	/*** Method for fetching site platform switch button ***/
	public function platformSwitchBtn($footer=true, $getViewport=false){
		
		global $GLOBAL_page_self_rel,$FA_mobile,$FA_desktop;
		
		$cmnBtnCls = 'btn btn-sm';
		$mobile = 'mobile';
		$desktop = 'desktop';
		$platformPref = 'view_pref';
		$platformUaPref = 'ua_view_pref';
		
		if($getViewport){
				
			$uaDesktopRequest = $this->ENGINE->ua_desktop_request();

			if(isset($_POST[$mobile]) || isset($_POST[$desktop]) || $uaDesktopRequest){
				
				if(isset($_POST[$mobile]))
					$_SESSION[$platformPref] = $mobile;	
					
				elseif(isset($_POST[$desktop]) || $uaDesktopRequest)
					$_SESSION[$platformPref] = $desktop;
					
				if($uaDesktopRequest)
					$_SESSION[$platformUaPref] = $desktop;	
				
			}elseif(isset($_SESSION[$platformUaPref])){
				
				unset($_SESSION[$platformPref]);
				unset($_SESSION[$platformUaPref]);
				
			}


			if(isset($_SESSION[$platformPref]) && ($_SESSION[$platformPref] == $desktop))
				$viewport = 'width=1200';

			else
				$viewport = 'width=device-width, initial-scale=1';
			
			return $viewport;
		
		}
		
		
		/***********GET USER VIEW PREFERENCE (MOBILE OR CLASSIC/DESKTOP)*********/
		if(isset($_SESSION[$platformPref]) && ($_SESSION[$platformPref] == $desktop)){
			
			$desktopActive = 'class="'.$cmnBtnCls.'"';
			$mobileActive = 'class="'.$cmnBtnCls.' btn-block btn-sc"';
			$forcedDesktop = true;
			
		}else{	
		
			$mobileActive = 'class="'.$cmnBtnCls.'"';
			$desktopActive = 'class="'.$cmnBtnCls.' btn-sc"';
			$forcedDesktop = false;
			
		}	
		

		$platformSwitchBtn = '<div class="btn-group '.($forcedDesktop?  'dsk' : 'mob').'-platform-dpn "> 
								<span id="res_a"></span><span id="res"></span>
								<form method="POST" action="'.$GLOBAL_page_self_rel.'">			
									<button type="submit" '.(isset($mobileActive)? $mobileActive : '').' name="mobile">'.($forcedDesktop?  'Switch to mobile version ' : 'Mobile').$FA_mobile.'</button>
									'.($forcedDesktop? '' : '<button type="submit" '.(isset($desktopActive)? $desktopActive : '').' name="desktop">Desktop'.$FA_desktop.'</button>').'
								</form>
							</div>';
							
		return (((!$footer && !$forcedDesktop) || isset($_SESSION[$platformUaPref]))? '' : $platformSwitchBtn);
		
	}


	
	
	
		
	/*** Method for pin icons for threads and posts ***/
	public function getPinIcon($pinner, $pinTime, $pinCount = 0, $pinIconOnly = false){

		$pinSticker='';
		$isPinned = ($pinner && $this->ENGINE->datetime_true($pinTime));
		$forPost = $pinCount;
		$pinIcon = $this->getFA('fa-map-pin '.($forPost? '' : 'prime font-sm'));

		if($pinIconOnly) 
			return $pinIcon;

		$pinnerTitle = $isPinned? 'title="This '.($forPost? 'post' : 'thread').' was pinned by '.$this->ACCOUNT->memberIdToggle($pinner).' '.$this->ENGINE->time_ago($pinTime).'"' : '';

		if($isPinned){

			if($forPost)
				$pinSticker = '<div class="pin-count" '.$pinnerTitle.'>'.str_repeat($pinIcon, $pinCount).'</div>';

			else
				$pinSticker = '<span class="pull-l" '.$pinnerTitle.'>'.$pinIcon.'</span>';

		}
				
		return $forPost? array($pinSticker, $pinIcon, $pinnerTitle) : $pinSticker;

	}




	
		
	/*** Method for fetching font awesome FA icons ***/
	public function getFA($clsArr, $metaArrArr=array(), $stackSize=''){
		
		$acc=array();
		$prefixArr = array("fa","fas","far","fab","fal");
		$clsArr = (array)$clsArr;
		$metaArrArr = (array)$metaArrArr;
		$len = count($clsArr);
		
		foreach($clsArr as $ck => $cls){
			
			foreach($metaArrArr as $k => $v){
				
				$metaArr = is_array($v)? (isset($metaArrArr[$ck])? $metaArrArr[$ck] : array()) : $metaArrArr;
				break;
				
			}
			
			$title = ((isset($metaArr[$k="title"]) && $metaArr[$k])? 'title="'.$metaArr[$k].'"' : '');
			$otherAttr = isset($metaArr[$k="attr"])? ' '.$metaArr[$k].' ' : '';
			$size = (isset($metaArr[$k="size"]) && $metaArr[$k])? ' fa-'.$metaArr[$k].' ' : '';
			$attr = $title.$otherAttr.' aria-hidden="true" ';
			
			if(is_array($cls)){
				
				$tmp = array();
				
				foreach($cls as $sck => $scls){
					
					$scls = !in_array(strtolower(trim(current(explode(" ", $scls)))), $prefixArr)? 'fas '.$scls : $scls;
					$tmp[] = ' <i class="'.$scls.'" '.$attr.'></i> ';
					
				}
				
				$acc[] = ' <span class="fa-stack'.$size.'">'.implode('', $tmp).'</span> ';
				
			}else{
				
				$cls = !in_array(strtolower(trim(current(explode(" ", $cls)))), $prefixArr)? 'fas '.$cls : $cls;
				$acc[] = ' <i class="'.$cls.'" '.$attr.'></i> ';
				
			}
		}
		
		return (($len > 1)? $acc : implode('', $acc));
		
	}







	
		
	/*** Method for handling QR Code request ***/
	public function qrCodeRequestHandler(){		

		$responseData = '';

		$qrData = isset($_GET[$K='data'])? $_GET[$K] : (isset($_POST[$K])? $_POST[$K] : '');
		$label = isset($_GET[$K='label'])? $_GET[$K] : (isset($_POST[$K])? $_POST[$K] : $this->getSiteName());
		$size = isset($_GET[$K='size'])? $_GET[$K] : (isset($_POST[$K])? $_POST[$K] : 400);
		$size = $this->ENGINE->sanitize_number($size);
		$margin = isset($_GET[$K='margin'])? $_GET[$K] : (isset($_POST[$K])? $_POST[$K] : 10);
		$margin = $this->ENGINE->sanitize_number($margin);
		$logoResize2Width = isset($_GET[$K='logoR2w'])? $_GET[$K] : (isset($_POST[$K])? $_POST[$K] : 50);
		$logoResize2Width = $this->ENGINE->sanitize_number($logoResize2Width);
		$logoResize2Height = isset($_GET[$K='logoR2H'])? $_GET[$K] : (isset($_POST[$K])? $_POST[$K] : 50);
		$logoResize2Height = $this->ENGINE->sanitize_number($logoResize2Height);
		$charSet = isset($_GET[$K='charSet'])? $_GET[$K] : (isset($_POST[$K])? $_POST[$K] : 'UTF-8');
		$foreColor = isset($_GET[$K='foreColor'])? trim($_GET[$K], '()') : (isset($_POST[$K])? trim($_POST[$K], '()') : '0, 0, 0');
		$backColor = isset($_GET[$K='backColor'])? trim($_GET[$K], '()') : (isset($_POST[$K])? trim($_POST[$K], '()') : '255, 255, 255');
		$labelColor = isset($_GET[$K='labelColor'])? trim($_GET[$K], '()') : (isset($_POST[$K])? trim($_POST[$K], '()') : '255, 0, 0');
		$forceOutput = isset($_GET[$K='forceOutput'])? $_GET[$K] : (isset($_POST[$K])? $_POST[$K] : true);
		$save2File = isset($_GET[$K='save2File'])? $_GET[$K] : (isset($_POST[$K])? $_POST[$K] : false);
		$punchoutBg = isset($_GET[$K='punchoutBg'])? $_GET[$K] : (isset($_POST[$K])? $_POST[$K] : false);
		$fileSaveName = isset($_GET[$K='saveName'])? $_GET[$K] : (isset($_POST[$K])? $_POST[$K] : 'qrcode-'.md5(time().mt_rand()));		

		$metaArr = array(

			'data' => $qrData,
			'label' => $label,
			'size' => $size,
			'margin' => $margin,
			'logoR2w' => $logoResize2Width,
			'logoR2H' => $logoResize2Height,
			'charSet' => $charSet,
			'foreColor' => $foreColor,
			'backColor' => $backColor,
			'labelColor' => $labelColor,
			'forceOutput' => $forceOutput,
			'save2File' => $save2File,
			'punchoutBg' => $punchoutBg,
			'saveName' => $fileSaveName

		);
			
		if($qrData)
			$responseData = $this->qrCodeGenerator($metaArr);

		if($responseData)
			$metaArr['responseData'] = $responseData;

		return $metaArr;
	

	}

	
		
	/*** Method for handling QR Code request ***/
	public function qrCodeGenerator($metaArr=array()){

		$data = $this->ENGINE->get_assoc_arr($metaArr, 'data');	
		$label = $this->ENGINE->get_assoc_arr($metaArr, 'label');	
		$size = (int)$this->ENGINE->get_assoc_arr($metaArr, 'size');	
		$margin = (int)$this->ENGINE->get_assoc_arr($metaArr, 'margin');	
		$logoResize2Width = (int)$this->ENGINE->get_assoc_arr($metaArr, 'logoR2w');	
		$logoResize2Height = (int)$this->ENGINE->get_assoc_arr($metaArr, 'logoR2H');	
		$charSet = $this->ENGINE->get_assoc_arr($metaArr, 'charSet');
		$defaultAlpha = 0;	
		list($foreColorRed, $foreColorGreen, $foreColorBlue, $foreColorAlpha) = array_pad(explode(',', $this->ENGINE->get_assoc_arr($metaArr, 'foreColor')), 4, $defaultAlpha);	
		list($backColorRed, $backColorGreen, $backColorBlue, $backColorAlpha) = array_pad(explode(',', $this->ENGINE->get_assoc_arr($metaArr, 'backColor')), 4, $defaultAlpha);	
		list($labelColorRed, $labelColorGreen, $labelColorBlue, $labelColorAlpha) = array_pad(explode(',', $this->ENGINE->get_assoc_arr($metaArr, 'labelColor')), 4, $defaultAlpha);	
		$fileSaveName = $this->ENGINE->get_assoc_arr($metaArr, 'saveName');	
		$forceOutput = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'forceOutput');	
		$save2File = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'save2File');
		$punchoutBg = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'punchoutBg');
		$getDataUri = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'getDataUri');
		
		$path2Save = $this->getMediaLinkRoot('qr', 'root');
		$path2Logo = $this->getSiteFavicon();			
	
		//Create QR Code
		$qrCode = QrCode::create($data)
		->setEncoding(new Encoding($charSet))
		->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
		->setSize($size)
		->setMargin($margin)
		->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
		->setForegroundColor(new Color($foreColorRed, $foreColorGreen, $foreColorBlue))
		->setBackgroundColor(new Color($backColorRed, $backColorGreen, $backColorBlue));

		//Create Generic Logo
		$logo = Logo::Create($path2Logo)
		->setResizeToWidth($logoResize2Width)
		->setResizeToHeight($logoResize2Height)
		->setPunchoutBackground($punchoutBg);

		//Create Generic Label
		$label = Label::create($label)
		->setTextColor(new Color($labelColorRed, $labelColorGreen, $labelColorBlue));

		$res = (new PngWriter())->write($qrCode, $logo, $label);
						
		//Save the QR code
		if($save2File)
			$res->saveToFile($path2Save.$fileSaveName.$this->ENGINE->get_ext_from_mime_type($res->getMimeType(), true));			
		

		//Directly Output the QR code
		if($forceOutput){

			header('Content-Type: '.$res->getMimeType());
			echo $res->getString();

		}			
		

		//Generate a data URI to include image data inline (i.e. inside an <img> tag)
		$dataUri = $res->getDataUri();

		if($getDataUri)
			return $dataUri;		


	}


	
	
	
		

	/*** Method for fetching media link paths ***/
	public function getMediaLinkRoot($type="", $domainPrependOrRoot=true, $ver="v1", $size="28"){
		
		$root="";
		$B = ((is_string($domainPrependOrRoot) && strtolower($domainPrependOrRoot) == 'root')? DOC_ROOT : $this->ENGINE->get_domain()).'/'; //domain base
		$SB = is_dir(DOC_ROOT.'/'.$this->ENGINE->get_page_path('page_url', 1))? '../' : ''; //sub domain base
		$assetPre = ASSET_PREFIX;
		$icoBase = $assetPre.'s-assets/icons/';	
		$cAssetPath = $assetPre.'c-assets/';	
		/*********************************/
		$rootBBC = $icoBase.'bbc/';
		$rootEmoticonsMain = $icoBase.'emoticons/';
		$rootEmoticons = $rootEmoticonsMain.$ver.'/'.$size.'/';
		$rootFav = $icoBase.'favicons/';
		$rootBg = $icoBase.'backgrounds/';
		$rootUiBg = $icoBase.'backgrounds/ui-bg/'.$ver.'/';
		$rootBanner = $cAssetPath.'ad-banners/';
		$rootCampCSV = $cAssetPath.'ad-campaigns/';
		$rootPost = $cAssetPath.'post-uploads/';
		$rootAvatar = $cAssetPath.'avatars/';		
		$rootQrCode = $cAssetPath.'qr-codes/';
		$rootBadge = $icoBase.'badges/';
		$rootSpin = $icoBase.'spinners/';
		$rootCloud = $assetPre.'clouds/';
		$rootZip = $assetPre.'archives/';
		$rootTmp = $assetPre.'tmp/';
		
		switch(strtolower($type)){
		
			case "bbc-icon":
			case "bbc-iconx": $root = $rootBBC; break;
			
			case "emoticon-tabs": $root = $rootEmoticonsMain; break;
			
			case "emoticons":
			case "emoticonsx": $root = $rootEmoticons; break;
			
			case "bg":
			case "bgx": $root = $rootBg; break;
			
			case "ui-bg":
			case "ui-bgx": $root = $rootUiBg; break;
			
			case "banner":
			case "bannerx": $root = $rootBanner; break;
			
			case "campaign-csv":
			case "campaign-csvx": $root = $rootCampCSV; break;
			
			case "post":
			case "postx": $root = $rootPost; break;
			
			case "avatar":
			case "avatarx": $root = $rootAvatar; break;
			
			case "qr":
			case "qrx": $root = $rootQrCode; break;
			
			case "badge":
			case "badgex": $root = $rootBadge; break;
			
			case "spin":
			case "spinx": $root = $rootSpin; break;
			
			case "zip":
			case "zipx": $root = $rootZip; break;
			
			case "cloud":
			case "cloudx": $root = $rootCloud; break;
			
			case "tmp":
			case "tmpx": $root = $rootTmp; break;
			
			case "ini-tops":{

				return array(

						'favicon' => $B.$rootFav, 'faviconX' => $SB.$rootFav, 'banner' => $B.$rootBanner, 'bannerX' => $SB.$rootBanner, 
						'post' => $B.$rootPost, 'postX' => $SB.$rootPost, 'avatar' => $B.$rootAvatar, 'avatarX' => $SB.$rootAvatar, 
						'badge' => $B.$rootBadge, 'badgeX' => $SB.$rootBadge, 'cloud' => $B.$rootCloud, 'cloudX' => $SB.$rootCloud,
						'spin' => $B.$rootSpin, 'spinX' => $SB.$rootSpin, 'zip' => $B.$rootZip, 'zipX' => $SB.$rootZip,
						'uiBg' => $B.$rootUiBg, 'uiBgX' => $SB.$rootUiBg, 'tmp' => $B.$rootTmp, 'tmpX' => $SB.$rootTmp,
						'qr' => $B.$rootQrCode, 'qrX' => $SB.$rootQrCode
						
					);
			}	
			
			default : $root = $rootFav;
		}	
		
		if($domainPrependOrRoot)
			$root = ((substr($type, -1, 1) == 'X')? $SB : $B).$root;
		
		return $root;
		
	}


	
	
	
	
	
		
	/*** Method for decoding media download directory from a referral slug ***/
	public function getDownloadDir($rfr){
		
		$folder='';
		
		switch(strtolower($rfr)){
			
			case 'post': $folder = 'post'; break;	
			
			case 'ads': $folder = 'banner'; break;	
			
			case 'profile': $folder = 'avatar'; break;
			
			case 'clouds': $folder = 'cloud'; break;	
			
			case 'campaign-csv': $folder = 'campaign-csv'; break;
			
			case 'favicon': $folder = 'favicon'; break;
			
			case 'archives': $folder = 'zip'; break;
			
			case 'qr': $folder = 'qr'; break;
			
		}
		
		$dir = $this->getMediaLinkRoot($folder, false);
		
		return $dir;
		
	}

	 
	
	
	
	
		
	/*** Method for fetching media download url ***/
	public function getDownloadURL($f, $type, $rel = true, $prependDomain = false){
		
		$url="";
		$preUrl = ($rel? '/' : '').'downloads/';
		
		switch(strtolower($type)){
			
			case 'profile': $url = $preUrl.'profile/'.$f; break;
			
			case 'post': $url = $preUrl.'post/'.$f; break;
			
			case 'ads': $url = $preUrl.'ads/'.$f; break;
			
			case 'qr': $url = $preUrl.'qr-code/'.$f; break;
			
			default: $url = $preUrl.'clouds/'.$f;
			
		}
		
		return ($prependDomain? $this->ENGINE->get_domain().$url : $url);
		
	}
 








	
	
		
	/*** Method for fetching composer form ***/
	public function getComposer($metaArr, $type='post'){
		
		global $GLOBAL_mediaRootFav;
		
		$form=$uiBgLoadedStyles=$uiBgLoader=$uiBgField=$threadTagUpdateAccess=$uiBgFillCls=$fieldFocus=$messageFieldErr=''; 
		$ignoreForm = false;
		$goBack = $this->getBackBtn();
		
		switch(strtolower($type)){
			
			case 'create': $forCreate = true; break;
			
			default: $forCreate = false;
			
		}
		
		if($forCreate){
			
			$kArr = array('crtSubj', 'alertUser', 'keepForm', 'required', 'asterix', 'topicExistsErr', 'sid', 'cat', 
			'xceptionsArr', 'topicFieldErr', 'topicHolder', 'messageFieldErr', 'fieldFocus', 'autofocus', 
			'pageSelf', 'textContent', 'tags', 'vdsChk', 'fileTooLarge', 'uploadErr', 'syndicateUrl');
			
			list($crtSubj, $alertUser, $keepForm, $required, $asterix, $topicExistsErr, $sid, $cat, $xceptionsArr, 
			$topicFieldErr, $topicHolder, $messageFieldErr, $fieldFocus, $autofocus, $pageSelf, $textContent, 
			$tags, $vdsChk, $fileTooLarge, $uploadErr, $syndicateUrl) = $this->ENGINE->get_assoc_arr($metaArr, $kArr);	
		
		}else{
			
			$kArr = array('uiBgPreWrap', 'uiBgComposerFormClass', 'uiBgCloseBtn', 'uiBgField', 'alertMods', 'uiBgFillCls', 
			'uiBgLoadedStyles', 'uiBgLoader', 'uiBgPostWrap', 'threadTagUpdateAccess', 'ftopic', 'uploadFilesView', 'postModifyIdField', 
			'postBtnTxt', 'isPostEdit', 'autofocus', 'pageSelf', 'textContent', 'tags', 'vdsChk', 'fileTooLarge', 'uploadErr', 'syndicateUrl');
			
			list($uiBgPreWrap, $uiBgComposerFormClass, $uiBgCloseBtn, $uiBgField, $alertMods, $uiBgFillCls, $uiBgLoadedStyles,
			$uiBgLoader, $uiBgPostWrap, $threadTagUpdateAccess, $ftopic, $uploadFilesView, $postModifyIdField, $postBtnTxt, $isPostEdit, $autofocus, 
			$pageSelf, $textContent, $tags, $vdsChk, $fileTooLarge, $uploadErr, $syndicateUrl) = $this->ENGINE->get_assoc_arr($metaArr, $kArr);

		}
		
		$uiBgComposerClass = (mb_strlen($textContent) < 70 && $uiBgField)? '_init-cgp' : '';
		
		$preWrap = '<div class="'.COMPOSER_WRAPPER_CLASS.($forCreate? 'base-ctrl base-rad base-container' : '').'">'
					.($forCreate? '<div class="row"><div class="'.L_WIDGET.'">'
									.(!$alertUser? $this->getCommunityRules($crtSubj).'<br/>' : '').'<span id="npff"></span>'.
									$required.($alertUser? $alertUser.$goBack : '').
									$topicExistsErr

					: $uiBgPreWrap);
		
		$postWrap = ($forCreate? '</div></div>' : $uiBgPostWrap).'</div>';
		
		if($forCreate){
			
			$alertDangerCls = 'alert alert-danger';
			
			if($alertUser && !$keepForm)
				$ignoreForm = $form = $alertUser;
			
			elseif(in_array($sid, $xceptionsArr))
				$ignoreForm = $form = '<span class="'.$alertDangerCls.'">Sorry! you cannot create topics under this section</span>';
			
			elseif($sid == MODS_SID && !$this->SESS->isStaff())
				$ignoreForm = $form = '<span class="'.$alertDangerCls.'">Sorry! you do not have enough privilege to create topics under this section</span><hr/>';
					
		}
			
		if(!$ignoreForm){
			
			$vdsField = $this->getHtmlComponent('iconic-checkbox', array('label'=>'Viewer\'s Discretion Warning :', 'title'=>'check this box if you are uploading a graphic or disturbing image', 'wrapClass'=>'red', 'fieldId'=>'vds-check', 'fieldName'=>'vds', 'on'=>$vdsChk));
			$ph = 'What do you wish to share'.(($fn=$this->SESS->getFirstName())? ', '.ucwords($fn).'?' : '');
			$composerId = COMPOSER_ID;
			$uibgExceedMsg = '<div class="hide _uibg-exceed-msg" ><span class="alert alert-danger">UIBG maximum permissible character length exceeded</span></div>';
			
			$form = $preWrap.'
					<form  name="'.($forCreate? 'createtopic' : 'postmessage').'" id="'.($K='composer-form').'" class="'.$K.' '.(isset($uiBgComposerFormClass)? $uiBgComposerFormClass : '').'" method="post" enctype="multipart/form-data" action="/'.$pageSelf.$fieldFocus.'">
						'.($forCreate? 
							'<div class="field-ctrl">
								<label>Subject/Topic'.$asterix.'</label>
								<input class="field '.$topicFieldErr.'" maxlength="'.MAX_TOPIC_NAME_LEN.'" type="text" id="'.$crtSubj.'"  value="'.$topicHolder.'"  name="topicsubject" placeholder="Subject" />
							</div>' : 
							$uiBgCloseBtn.$uiBgField.'<div class="error-box">'.$alertMods.'</div><br/>'
						).'
						<div class="composer-group">'.$uibgExceedMsg.'
							<div class="field-ctrl '.$uiBgFillCls.'">
								'.($forCreate? '<label>Message'.$asterix.'</label>' : '').'
								<textarea spellcheck="true" class="field '.$composerId.' '.$uiBgComposerClass.' bbc-response '.$messageFieldErr.'" '.$uiBgLoadedStyles.' id="'.$composerId.'" name="'.($forCreate? 'topicmessage' : 'postmessage').'" placeholder="'.$ph.'" title="'.$ph.'" '.$uiBgLoader.' >'.$textContent.'</textarea>
							</div>'.$this->bbcHandler().'
							<div class="field-ctrl">'.
								($forCreate?  '' : '<button class="btn btn-infox btn-xs" data-toggle="smartToggler" data-id-targets="syndicate-url" title="if you have a link(url) to the source of your content, click here to enter it">Content Source '.($forCreate? '<span class="black">(optional)</span>' : '').'</button><span id="contentSrcUpdaterWrap"></span>').
								($threadTagUpdateAccess? '<button class="btn btn-xs btn-sc" data-toggle="smartToggler" data-id-targets="threadTags">Modify Thread Tags</button>' : '<span id="threadTagsToggleWrapper"></span>').
								($forCreate? '' : '<button class="btn btn-xs btn-purple" data-toggle="smartToggler" data-id-targets="uploadGrp">Upload File</button>').
								($forCreate? '<label>Content Source <small class="prime">(optional)</small></label>' : '').'
								<input id="syndicate-url" class="field '.($forCreate? '' : 'hide').'" type="text" value="'.$syndicateUrl.'" name="src_url" placeholder="http://www.mysource.com" />'
								.($forCreate? '<label>Tags <small class="prime">(optional)</small></label>' : '').'
								<input id="threadTags" class="field '.($forCreate? '' : 'hide').'" type="text" value="'.$tags.'" name="tags" placeholder="tags" />														
							</div>	
							<div class="field-ctrl" >
								<div class="red">'.$fileTooLarge.$uploadErr.'</div>
								<div class="'.($forCreate? '' : 'hide').'" id="uploadGrp">
									<label id="attach_label">Attachments:(maximum allowed file size is '.MAX_POST_UPLOAD_SIZE_STR.' per file)</label><br/>
									<input '.$autofocus.' class="field upload-field" type="file" name="fileups[]" multiple="multiple" data-imgfp="true" data-screen="#imgfp-screen"  />					 
									<span title="Add more files" class="plus-icon" data-add-file-field="true" data-meta="'.$GLOBAL_mediaRootFav.'delete.png">+</span>
									<div id="imgfp-screen"></div>																
									'.($forCreate? $vdsField : '').'
								</div>
							</div>
							'.($forCreate? '' : '
							<div class="field-ctrl">
								<div class="base-b-mg">'.$ftopic.'</div>
								<div id="modify-file-box"></div>
								'.$uploadFilesView.$postModifyIdField.$vdsField.'
								<div class="error-box">'.$alertMods.'</div>
							</div>
							').'
							<div class="field-ctrl btn-ctrl" id="postSubmitBtn-par" >
								'.($forCreate? '
								<input type="hidden" name="section" value="'. $sid .'" />
								<input type="hidden" name="cat" value="'.$cat.'" />' : 
								'').'
								<input type="submit" id="postSubmitBtn" class="form-btn '.($forCreate? '' : 'btn-success').'" name="'.($forCreate? 'create' : 'post').'" value="'.($forCreate? 'CREATE' : $postBtnTxt).'" '.($this->ENGINE->is_ajax()? 'data-check-fnull="true"' : '').' />
							</div>
						</div>
					</form>'.$postWrap;
			
		}
			
		return $form;
		
	}

	
	



	
	
		
	/*** Method for handling composer user interface background images ***/
	public function uiBgHandler($responseFieldId='', $uiBgMetaArr=array()){
		
		//////////GET DATABASE CONNECTION//////
		global $GLOBAL_mediaRootUiBg, $GLOBAL_mediaRootUiBgXCL;
		$counter = 1; $startIndex = 0; $perPage = 19; 
		$bgAcc = $bgAccMore = '';
		$loadMore=$getRow=$decodeStyle=$getPerBg=false;
		$xtn = '.jpg';
		
		$isFireFox = $this->ENGINE->validate_browser();
		$uiBgMetaArr = (array)$uiBgMetaArr;
		$uiBgId = isset($uiBgMetaArr[$K='id'])? $uiBgMetaArr[$K] : 0;
		$stylesToDecode = isset($uiBgMetaArr[$K='styles'])? $uiBgMetaArr[$K] : '';
		$action = isset($uiBgMetaArr[$K='action'])? $uiBgMetaArr[$K] : '';
		$retRaw = isset($uiBgMetaArr[$K='retRaw'])? $uiBgMetaArr[$K] : false;
		
		switch(strtolower($action)){
			
			case 'getrow': $getRow = true; break;
			
			case 'decodestyle': $decodeStyle = true; break;
			
			case 'getperbg': $getPerBg = true; break;
			
		}
		
		if($decodeStyle){
			
			return (str_ireplace(array('url(', $xtn), array('url('.$GLOBAL_mediaRootUiBg, '_'.$uiBgId.$xtn), $stylesToDecode));
			
		}
		
		if(isset($_POST[$K='uibg_load_page_id']) && ($pageId = $_POST[$K])){
			
			$loadMore = true;
			//$startIndex = $this->getPageRecordIndex($pageId, $perPage);
			
		}
		
		$valArr = $uiBgId? array($uiBgId) : array();
		
		for($pageId=1; ; $pageId++){
			
			if($loadMore)
				$startIndex = $this->getPageRecordIndex($pageId, $perPage);
			
			$sql = "SELECT * FROM ui_bgs ".($uiBgId? " WHERE ID=? LIMIT 1 " : (" WHERE (RENDER_VERSION=0 OR RENDER_VERSION=".($isFireFox? 2 : 1).") ".($loadMore? " ORDER BY LABEL " : " AND CATEGORY = 1 ORDER BY IS_SOLID_STYLE DESC "))." LIMIT ".$startIndex.",".$perPage);
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
			
			/////IMPORTANT INFINITE LOOP CONTROL ////
			if(!$this->DBM->getSelectCount())
				break;
							
			while($row = $this->DBM->fetchRow($stmt)){
		
				$id = $foundDatas = $row["ID"];
				$isSolidStyle = $row["IS_SOLID_STYLE"];
				$bgStyles = $row["BG_STYLES"];
				$contentStyles = $row["CONTENT_STYLES"];
				$renderStyles = $bgStyles.' '.$contentStyles;
				$label = $row["LABEL"];
		
				if($getRow){
					
					return ($uiBgId? $row : '');
		
				}
					
				if(!$isSolidStyle && (!file_exists($GLOBAL_mediaRootUiBgXCL.$id.$xtn) || !file_exists($GLOBAL_mediaRootUiBgXCL.$id.'_'.$id.$xtn))){
		
					if($getPerBg)
						return '<b class="red">?<br/> file not found</b>';
		
					continue;
		
				}
				
				$label = $label.(($label && !$isSolidStyle)? ',' : '').' background'.($isSolidStyle? '' : ' image');
				!$isSolidStyle? ($renderStyles = $this->uiBgHandler('', array('id' => $id, 'styles' => $renderStyles, 'action' => 'decodeStyle'))) : '';
				$bgArr = array("dir"=>"ui-bg", "file"=>$id.$xtn, "title"=>$label, "label"=>$label, "ocls"=>'_28p', "icls"=>'_28', "iAttr"=>' data-uibg-loader="'.$id.'" data-uibg-styles="'.$renderStyles.'"', "type"=>"bg", "hoverReact"=>false, "hoverReactBaseCtrlCls"=>"_hv-react-scale-sm _28p");
		
				if($isSolidStyle){
		
					$bgArr["useSolidColor"] = $bgStyles;
		
				}/*else{
		
					$bgArr["dir"] = 'ui-bg';
					$bgArr["file"] = $id.$xtn;
		
				}*/
				
				if($retRaw)
					return ($isSolidStyle? $bgStyles : $renderStyles);
				
				$tmp = $this->getBgImg($bgArr);
				
				if($getPerBg)
					return $tmp;
				
				($counter > $perPage)? ($bgAccMore .= $tmp) : ($bgAcc .= $tmp);
				
				$counter++;			
							
			}
			
			if(!$loadMore){
		
				//BREAK FROM INFINITE LOOP
				break;
		
			}
			
		}
			
		if(isset($foundDatas)){	
			
			$loadMoreBtn = $loadMore? '' : '<a role="button" class="btn btn-sc btn-xs" data-uibg-page="1">Load all</a>';
			//$bgAcc = '<div class="_uibg" data-response-id="'.$responseFieldId.'"><div class="_uibg-0" data-uibg-loader="0" title="default"></div>'.$bgAcc.'</div>';
			$bgAcc = '<div class="">'.$this->getBgImg(array("useSolidColor"=>"background-color: #f1f1f1",  "title"=>$label='No background', "label"=>$label.', selected', "iAttr"=>' data-uibg-loader="0" ', "oCls"=>'_28p', "iCls"=>'_uibg-selected _28', "type"=>"bg", "hoverReact"=>false, "hoverReactBaseCtrlCls"=>"_hv-react-scale-sm _28p")).$bgAcc.$bgAccMore.'</div>'.$loadMoreBtn;
			
			if($loadMore){
			
				$JSONArr['loaded'] = $bgAcc;	
				$JSONArr['lastRecord'] = true;//($found < $perPage)? true : false ;	
				echo json_encode($JSONArr);
				exit();
			
			}

			$bgAcc = '<div class="_uibg" data-response-id="'.$responseFieldId.'">'.$bgAcc.'</div>';
			
		}
		
		return $bgAcc;
			
	}



	

	
	

	


	
		
	/*** Method for generating emoticons user interface placeholder codes and fetching the corresponding reqex ***/
	public function emoticonsUiCodeGenerator($update=false){
		
		/***
			IMPORTANT NOTES: 
				AS OUTLINED BELOW, EMOTICONS PLACE HOLDERS MUST BEGIN WITH:
				THE FIXED PREFIX OUTLINED
				(FOLLOWED BY TWO OF THE SPECIAL EMOTICONS CHARS AND ONE LOWER CASE ALPHABET) IN ANY RANDOM ORDER
				MUST BE UNIQUE FOR EVERY EMOTICON
		***/
		
		/**
			SPECIAL CHARS ARE CAREFULLY SELECTED TO GIVE A COMPACTED UI CODE
			(#$<>&%) DOESN'T CREATE A COMPACT LOOK
			COMPACT HERE IMPLIES OCCUPYING LESS VISUAL SPACE
		**/
		
		
		$emoticonSpecialCharsLen = 3;
		$fixedPrefix = ' ';
		$openChar1 = '(';
		$closeChar1 = ')';
		$mainChars = ":-~';`";
		$specEmoticonChars = $mainChars.$openChar1.$closeChar1;

		/****
			For generation choose the combination manually following the sequence outlined below;
			use the sequences below and move to the next when exhausted
			feel free to use the chars in $mainChars to extend the sequences as seen fit
			
			$uiGenMainChars = ":" //concatenate $openChar1
			$uiGenMainChars = ":" //concatenate $closeChar1
			$uiGenMainChars = ":-" //concatenate $openChar1
			$uiGenMainChars = ":-" //concatenate $closeChar1
			$uiGenMainChars = ":~" //concatenate $openChar1
			$uiGenMainChars = ":~" //concatenate $closeChar1
			$uiGenMainChars = ":~'" //concatenate $openChar1 (LAST GENERATION END POINT)
			$uiGenMainChars = ":~'" //concatenate $closeChar1
			$uiGenMainChars = "~;" //concatenate $openChar1
			$uiGenMainChars = ":~;" //concatenate $closeChar1
			$uiGenMainChars = "~-" //concatenate $openChar1
			$uiGenMainChars = ":~-" //concatenate $closeChar1
			$uiGenMainChars = "~`" //concatenate $openChar1
			$uiGenMainChars = ":~`" //concatenate $closeChar1
		
		***/
		$uiGenFixedPrefix = $fixedPrefix;
		$uiGenMainChars = ":~'"; //Adjust during uiCode generation using the outlined sequences above
		$uiGenSpecEmoticonChars = $uiGenMainChars.$openChar1; //Concatenate other sequence chars
		
		
		if($update){
			
			$table = 'emoticons';
			
			/*
			//REARRANGE OR INSERT VALUES AT SOME POINT IN EMOTICONS TABLE
			for($pageId=1; ; $pageId++){
				
				$startIndex = $this->getPageRecordIndex($pageId, $perPage=200);
				
				$sql = "SELECT * FROM ".$table." WHERE LENGTH(UI_CODE) = 5 ORDER BY ID ASC LIMIT ".$startIndex.",".$perPage;
				$valArr = array();
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
				
				while($row = $this->DBM->fetchRow($stmt)){
					
					$id = $row["ID"];
					$unicodeSrc = $row["UNICODE_SRC"];
					$uiCode = $row["UI_CODE"];
					$categ = $row["CATEGORY"];
					$label = $row["LABEL"];
					
					if(mb_strlen($uiCode) < 5)
						continue;
					
					$sql = "UPDATE ".$table." SET UI_CODE=? WHERE ID=? LIMIT 1";
					$valArr = array(mb_substr($uiCode, 1), $id);
					$this->DBM->doSecuredQuery($sql, $valArr);
					
					$iterateInsert = false; 
					
					do{
						$sql = "INSERT INTO ".$table." (UNICODE_SRC, UI_CODE, CATEGORY, LABEL) VALUES(?,?,?,?)";
						$valArr = array($unicodeSrc, $uiCode, $categ, $label);
						$this->DBM->doSecuredQuery($sql, $valArr);
						
						if($id == 477 && !isset($stopInsertIteration)){
							
							$unicodeSrc = '1f490';
							$categ = 'Animals & Nature';
							$uiCode = $label = '';
							$iterateInsert = $stopInsertIteration = true;
							
						}elseif($id == 534 && !isset($stopInsertIteration)){
							
							$unicodeSrc = '1f350';
							$categ = 'Food & Drink';
							$uiCode = $label = '';
							$iterateInsert = $stopInsertIteration = true;
							
						}elseif(isset($stopInsertIteration)){
							
							$iterateInsert = false;
							unset($stopInsertIteration);
							
						}
						
					}while($iterateInsert);
					
					
					
				}
				
				//BREAK FROM INFINITE LOOP
				if(!$stmt->rowCount())
					break;
				
			}
			
			return;
			
			*/
			
			$alphas = $this->ENGINE->get_alphabets('lc'); //lc (lower case alphabets)
			
			if(!isset($_SESSION[$K="EMOTICON_UICODES_REVALIDATION_RESET_DONE"])){
				
				$sql = "UPDATE ".$table." SET UI_CODE=''";
				$valArr = array();
				
				if($this->DBM->doSecuredQuery($sql, $valArr))
					$_SESSION[$K] = true;
				
			}
			
			for($pageId=1; ; $pageId++){
				
				$startIndex = $this->getPageRecordIndex($pageId, $perPage=200);
				
				$sql = "SELECT ID FROM ".$table." WHERE UI_CODE='' LIMIT ".$startIndex.",".$perPage;
				$valArr = array();
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
				$uiCodeExist = false; $loopTrials = 300;
				
				/////IMPORTANT INFINITE LOOP CONTROL ////
				if(!$this->DBM->getSelectCount())
					break;
							
				while($row = $this->DBM->fetchRow($stmt)){
					
					$killNextExceededTrial = false; $loopCounts = 1;
					$id = $row["ID"];
					
					do{
						$uiCode = $uiGenFixedPrefix.str_shuffle(mb_substr(str_shuffle($uiGenSpecEmoticonChars), 0, 2).mb_substr(str_shuffle($alphas), 0, 1));
						$sql = "SELECT ID FROM ".$table." WHERE UI_CODE=? LIMIT 1";
						$valArr = array($uiCode);
						$uiCodeExist = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
						
						if($loopCounts > $loopTrials){
							
							$loopCounts = 0;
							
							if($killNextExceededTrial){
								
								die('EXHAUSTED UICODE COMBINATION AFTER SET TRIALS WERE EXCEEDED');
								
							}
							
							$killNextExceededTrial = true;
							
						}
						
						$loopCounts++;
						
					}while($uiCodeExist);
					
					$sql = "UPDATE ".$table." SET UI_CODE=? WHERE ID=? LIMIT 1";
					$valArr = array($uiCode, $id);
					$this->DBM->doSecuredQuery($sql, $valArr);
					
				}
				
			}
			
		}else{
			
			return $regexIn = '#(\s[a-z'.$this->ENGINE->escape_with_backslash($specEmoticonChars).']{'.$emoticonSpecialCharsLen.'})#s';
			
		}
	}



	



	
		
	/*** Method for fetching recently used emoticons ***/
	public function getRecentlyUsedEmoticons($remoticonKey, $wrap=true){
		
		$recentlyUsedEmoticons='';
		
		if(isset($_SESSION[$remoticonKey]) && ($remoticonsArr = $_SESSION[$remoticonKey]) && !empty($remoticonsArr)){
			
			//DECODE ID HASH
			foreach($remoticonsArr as $k => $v)
				$remoticonsArr[$k] = $this->INT_HASHER->decode($v);
			
			$recentlyUsedEmoticonsArr = $this->bbcHandler('', array('action' => 'getRecentlyUsed', 'emoticonIdArr' => $remoticonsArr, 'retArr' => true));
			
			//REARRANGE TO FOLLOW MOST RECENT
			foreach($remoticonsArr as $k => $v){
				
				if(!isset($recentlyUsedEmoticonsArr[$v]))
					continue;
				
				$recentlyUsedEmoticons .= $recentlyUsedEmoticonsArr[$v];
				
			}
			
			$recentlyUsedEmoticons = $recentlyUsedEmoticonsArr["CT"].$recentlyUsedEmoticons;
			
			return  ($wrap? '<div class="emoticon-group" id="recently-used">'.$recentlyUsedEmoticons.'</div>' : $recentlyUsedEmoticons);
			
		}
		
		return '';
		
	}



	
	


	


	
		
	/*** Method for loading emoticon nav tabs ***/
	public function loadEmoticonTabs($emoticonsTable, $remoticonKey, $ver, $size, $xtn){
		
		$emoticonTabs=''; $counter=1; $prepend=false;
		
		$sql = "SELECT DISTINCT CATEGORY FROM ".$emoticonsTable;
		$valArr = array();
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		
		while(($row = $this->DBM->fetchRow($stmt)) || $prepend){
		
			if($prepend){
		
				$tab = 'Recently Used';
				$dataRecentTab = ' data-recent-tab="true" ';
		
			}else{
		
				$tab = $row["CATEGORY"];
				$dataRecentTab = '';
		
			}
		
			$tabSan = $this->ENGINE->sanitize_slug($tab, array('appendXtn' => false));
			
			$tmp = '<li role="presentation" title="'.$tab.'" '.$dataRecentTab.'><a role="tab" href="#" class="emoticon-tab" aria-selected="false" tabindex="-1" data-tgt="'.$tabSan.'"><span aria-label="'.$tab.'" class="_emotab '.$tabSan.'" ><img class="img-responsive" src="'.$this->getMediaLinkRoot("emoticon-tabs", true, $ver, $size).'tab-icons/'.$tabSan.$xtn.'" /></span></a></li>';
			($prepend && !isset($_SESSION[$remoticonKey]))? ($tmp = '') : '';
			$prepend? ($emoticonTabs = $tmp.$emoticonTabs) : ($emoticonTabs .= $tmp);
			
			if($counter == 8){
		
				$prepend = true;
		
			}else
				$prepend = false;
		
			$counter++;
			
		}
		
		return '<nav class="nav-base base-xs" id="emoticon-tabs"><ul class="nav nav-pills" role="tablist" data-slide-show-external-pager-target="emoticons-slide-show">
					'.$emoticonTabs.'
				</ul></nav>';


	}


	
		
	/*** Method for handling (encoding/decoding) bbc and emoticon ***/
	public function bbcHandler($responseFieldId='', $metaArr=array(), $content=''){
		
		$responseFieldId = $responseFieldId? $responseFieldId : COMPOSER_ID;
		$accEmoticons=''; 
		$remoticonKey = 'REMOTICONS'; 
		$maxRecentlyUsedShown = 42;
		$emoticonsTable = 'emoticons'; 
		$xtn = '.png'; 
		$startIndex = 0; 
		$perPage = 100; 
		$loadEmoticons=$getPerEmoticon=$decode=$remoticon=$loadEmoticonTabs=$getRecentlyUsed=false;
		//unset($_SESSION[$remoticonKey]);
		$metaArr = (array)$metaArr;
		$isFireFox = $this->ENGINE->validate_browser();
		$action = isset($metaArr[$K='action'])? $metaArr[$K] : '';
		$content = isset($metaArr[$K='content'])? $metaArr[$K] : '';
		$emoticonId = isset($metaArr[$K='emoticonId'])? $metaArr[$K] : '';
		$emoticonIdArr = isset($metaArr[$K='emoticonIdArr'])? $metaArr[$K] : '';
		$retArr = isset($metaArr[$K='retArr'])? $metaArr[$K] : '';
		$ver = isset($metaArr[$K='ver'])? $metaArr[$K] : 'v'.($isFireFox? '2' : '1');
		$size = isset($metaArr[$K='size'])? $metaArr[$K] : '28';
		$isEmoticonIdArr = is_array($emoticonIdArr);
		$mediaRootBBC = $this->getMediaLinkRoot("bbc-icon");
		$mediaRootEmoticons = $this->getMediaLinkRoot("emoticons", true, $ver, $size);
		
		switch(strtolower($action)){
			
			case 'decode': $decode = true; break;
			
			case 'getperemoticon': $getPerEmoticon = true; break;
			
			case 'getrecentlyused': $getRecentlyUsed = true; break;
			
			case 'remoticon': $remoticon = true; break;
			
			case 'loademoticontabs': $loadEmoticonTabs = true; break;
			
			case 'loadmoreemoticons': $loadEmoticons = true; break;
			
		}
		
		// Capture recently used emoticons 
		if(isset($_POST[$K='remoticon']) && ($remoticonId = $_POST[$K]) && $remoticon){
			
			$counter = 1; $wrap = false; $newRemArr = array();
			
			if(isset($_SESSION[$remoticonKey])){
			
				$remoticonsArr = $_SESSION[$remoticonKey];
				(($K=array_search($remoticonId, $remoticonsArr)) !== false)? ($remoticonsArr[$K] = '') : '';
				array_unshift($remoticonsArr, $remoticonId);
			
				foreach($remoticonsArr as $v){
			
					if(!$v) continue;
					$newRemArr[] = $v;
			
					if($counter == $maxRecentlyUsedShown)
						break;
			
					$counter++;
			
				}
				
				$_SESSION[$remoticonKey] = $newRemArr;
				
			}else{
				
				$_SESSION[$remoticonKey] = array($remoticonId);
				
				// Ensure to reload emoticon nav tabs to show the recently used emoticon tab 
				$JSONArr['reloadedEmoticonTabs'] = $this->loadEmoticonTabs($emoticonsTable, $remoticonKey, $ver, $size, $xtn);
				
				// Ensure also to reload emoticons so as to include the recently used emoticon slide 
				$loadEmoticons = true;
				
			}
			
			if(!$loadEmoticons){
				
				$JSONArr['loadedRecentlyUsed'] = $this->getRecentlyUsedEmoticons($remoticonKey, $wrap);
				echo json_encode($JSONArr);
				exit(); //TERMINATE AJAX CALL
				
			}
			
		}
		// load emoticon tabs
		elseif(isset($_POST['load_emoticon_tabs']) && $loadEmoticonTabs){
			
			$JSONArr['loadedEmoticonTabs'] = $this->loadEmoticonTabs($emoticonsTable, $remoticonKey, $ver, $size, $xtn);
			
			echo json_encode($JSONArr);
			exit(); //TERMINATE AJAX CALL
			
		}
		// load more emoticon by paging
		elseif(isset($_POST[$K='emoticons_load_page_id']) && ($pageId = $_POST[$K]) && $loadEmoticons){
			
			//$startIndex = $this->getPageRecordIndex($pageId=1, $perPage);
			
		}
			
			
		/****NUMERIC KEYS EQUIVALENTS
		bbc: array('uiInput'=>'','regexIn'=>'','regexReplace'=>'','regexModifier'=>'','src'=>'','title'=>'','miscDatas'=>'','elementType'=>'')
		emoticon: array('uiInput'=>'', 'src'=>'','title'=>'')
		*******/
		
		$delim = '#';	
		//$parseSrc = '((?!logout)(?!logout-all).)+';//VERY IMPORTANT: Don`t parse href pointing to site logout page 
		$parseSrc = '((?!'.$this->ENGINE->get_http_host().').)+';//VERY IMPORTANT: Don`t parse href pointing to self; imagine if a resource url points to the site logout page(users will be unconsciously logged out) 
		$extLinkAttr = " target='_blank' rel='nofollow noopener' ";
		$mdf = 'isU'; $mdf2 = 'is'; $mdf3 = 's';
			
		$bbcArr = array(
			
				array("[col][/col]", "\\[col=(\"|')?([a-z0-9\\#]+)(\"|')?\\](.*)\\[/col\\]", "<span style='color:$2'>$4</span>", $mdf, "", 'Text Color', 'type="color" value="#0000ff" class="pointer" data-fbp="6" data-color-well="true"', "input"),		
			
				array("[br]", "\\[br(.*)/?\\]", "<br $1/>", $mdf, "line-break.png", 'Line Break', '', ""),

				array("[b][/b]", "\\[b(.*)\\](.*)\\[/b\\]", "<b $1>$2</b>", $mdf, "bold.gif", 'Bold Text', 'data-fbp="4" class="fas fa-bold"', "i"),		
			
				array("[i][/i]", "\\[i\\](.*)\\[/i\\]", "<i>$1</i>", $mdf, "italicize.gif", 'Italicize Text', 'data-fbp="4" class="fas fa-italic"', "i"),		
			
				array("[f f=verdana s=14px][/f]", "\\[f f=(\"|')?([a-z -]+)(\"|')? s=(\"|')?([a-z0-9]+)(\"|')?\\](.*)\\[/f\\]", "<span style='font-family:$2;font-size:$5;'>$7</span>", $mdf, "face.gif", 'Font Face or Family', 'data-fbp="4"', ""),		
			
				array("[sz=16px][/sz]", "\\[sz=(\"|')?([a-z0-9]+)(\"|')?\\](.*)\\[/sz\\]", "<span style='font-size:$2;'>$4</span>", $mdf, "size.gif", 'Font Size', 'data-fbp="5"', ""),		
			
				array("[hr]", "\\[hr(.*)/?\\]", "<hr $1/>", $mdf, "hr.gif", 'Horizontal Rule', '', ""),		
			
				array("[u][/u]", "\\[u\\](.*)\\[/u\\]", "<span class='text-underline'>$1</span>", $mdf, "", 'Underline', 'data-fbp="4" class="fas fa-underline"', "i"),		
			
				array("[s][/s]", "\\[s\\](.*)\\[/s\\]", "<del class='strike default'>$1</del>", $mdf, "strike.gif", 'Strike Through', 'data-fbp="4" class="fas fa-strikethrough"', "i"),		
			
				array("[sb][/sb]", "\\[sb\\](.*)\\[/sb\\]", "<sub>$1</sub>", $mdf, "sub.gif", 'Subscript', 'data-fbp="5" class="fas fa-subscript"', "i"),		
			
				array("[sp][/sp]", "\\[sp\\](.*)\\[/sp\\]", "<sup>$1</sup>", $mdf, "sup.gif", 'Superscript', 'data-fbp="5" class="fas fa-superscript"', "i"),		
			
				array("[l][/l]", "\\[l\\](.*)\\[/l\\]", "<span class='text-left'>$1</span>", $mdf, "left.gif", 'Align Text Left', 'data-fbp="4" class="fas fa-align-left"', "i"),		
			
				array("[r][/r]", "\\[r\\](.*)\\[/r\\]", "<span class='text-right'>$1</span>", $mdf, "right.gif", 'Align Text Right', 'data-fbp="4" class="fas fa-align-right"', "i"),		
			
				array("[c][/c]", "\\[c\\](.*)\\[/c\\]", "<span class='text-center'>$1</span>", $mdf, "center.gif", 'Center Text', 'data-fbp="4" class="fas fa-align-center"', "i"),		
			
				array("[j][/j]", "\\[j\\](.*)\\[/j\\]", "<span class='text-justify'>$1</span>", $mdf, "", 'Justify Text', 'data-fbp="4" class="fas fa-align-justify"', "i"),		
			
				array("[ind=10][/ind]", "\\[ind=([0-9]{2,2})\\](.*)\\[/ind\\]", "<span class='text-indent' style='text-indent:$1px'>$2</span>", $mdf, "", 'Indent Text', 'data-fbp="6" class="fas fa-indent"', "i"),		
				
				array("[ul][li][/li][li][/li][li][/li][/ul]", "\\[ul(.*)\\](.*)\\[/ul\\]", "<ul $1 class='ul default'>$2</ul>", $mdf, "", 'Unordered List', 'data-fbp="28" class="fas fa-list-ul"', "i"),		
			
				array("[ol][li][/li][li][/li][li][/li][/ol]", "\\[ol(.*)\\](.*)\\[/ol\\]", "<ol $1>$2</ol>", $mdf, "", 'Ordered List', 'data-fbp="28" class="fas fa-list-ol"', "i"),				
				
				array("[quote][/quote]", "(.*)\\[quote( author=(\"|')?([a-z0-9_-]+))?(\"|')?( post=(\"|')?([0-9]+))?(\"|')?\\](.*)\\[/quote\\](.*)", "$1<blockquote class='quotes'><header class='post-head-min'><a href='/$4' class='post-author text-capitalize'>$4</a></header><div class='org-qp' data-ref-post='$8'>$10</div><div class='clear'><a href='/find-post/$8' title='Click this icon to locate the original post' class='links pull-r'>&#8662;".$this->getFA('fa-eye')."</a></div></blockquote>$11", $mdf, "quote.gif", 'Quote', 'data-fbp="8"', ""),		
			
				array("[em][/em]", "\\[em\\](.*)\\[/em\\]", "<a href='mailto:$1' class='links'>$1</a>", $mdf, "email.gif", 'Email', 'data-fbp="5" class="fas fa-envelope"', "i"),		
			
				array("[em][/em]", "\\[em=([^(\"|')].+)\\](.*)\\[/em\\]", "<a href='mailto:$1' class='links'>$2</a>", $mdf, "email.gif", 'Email', 'data-fbp="5"', "parse-only"),		
			
				array("[a][/a]", $href1="\\[a=([^(\"|')]+)\\](.+)\\[/a\\]", "<a class='links' ".$extLinkAttr." href='$1'>$2</a>", $mdf, "url.gif", 'url', 'data-fbp="4" class="fas fa-link"', "i"),		
			
				array("[a][/a]", $href2="\\[a\\](.+)\\[/a\\]", "<a class='links' ".$extLinkAttr." href='$1'>$1</a>", $mdf, "url.gif", 'Url', 'data-fbp="4"', "parse-only"),		
			
				array("[a][/a]", "\\[a (.+)\\](.*)\\[/a\\]", "<a class='links' href='/$1'>$2</a>", $mdf, "url.gif", 'Url', 'data-fbp="4"', "parse-only"),		
				
				array("[img_sb=".($srcTitlePH='src1::title1;src2::title2;src3::title3')."]", $slideBox="\\[img_sb=(".$parseSrc.")\\]", "<div class='base-tb-mg slide-show _slide-full' data-auto-play='true' data-hover-pause='true' data-speed='7500'>$1</div>", $mdf, "", 'Embed a slide show', 'data-fbp="39" class="fas fa-sliders-h"', "i"),		
			
				array("[img_lb=".$srcTitlePH."]", $lightBox="\\[img_lb=(".$parseSrc.")\\]", "<div class='base-tb-mg lightbox photo-storyXX hover-shadow'>$1</div>", $mdf, "", 'Embed image carousel or lightbox', 'data-fbp="39" class="fas fa-images"', "i"),		
			
				array("[img=]", "\\[img=(((http|https)\\://)".$parseSrc.")\\]", "<div class='pim-limit _hv-react-base'><img class='zoom-ctrl _hv-react media-responsive pim' src='$1' alt='Image From: $1' title='Image From: $1' /></div>", $mdf, "img.gif", 'Embed Image', 'data-fbp="1" class="fas fa-image"', "i"),		
			
				array("[aud src= t=audio/mpeg]", "\\[aud src=(((http|https)\\://)".$parseSrc.")( t=([A-Z-_0-9/]+))\\]", "<div class='base-tb-mg media-responsive'><audio title='Audio From: $1' class='' controls autoplay muted><source src='$1' type='$6'>Please upgrade your browser to play the audio here</audio></div>", $mdf, "", 'Embed Audio', 'data-fbp="14" class="fas fa-music"', "i"),		
			
				array("[vid src= t=video/mp4 w=560 h=315]", "\\[vid src=(((http|https)\\://)".$parseSrc.")( t=([A-Z-_0-9/]+))( w=([0-9]+) h=([0-9]+))?\\]", "<div class='base-tb-mg media-responsive'><video title='Video From: $1' width='$8' height='$9' class='' controls autoplay muted><source src='$1' type='$6'>Please upgrade your browser to play the video embedded here</video></div>", $mdf, "", 'Embed Video', 'data-fbp="25" class="fas fa-video"', "i"),		
				
				array("[embed src= w=560 h=315]", "\\[embed src=(((http|https)\\://)".$parseSrc.")( w=([0-9]+) h=([0-9]+))?\\]", "<div class='iframe-base'><iframe title='Embedded Content From: $1' src='$1' width='$6' height='$7' scrolling='no' noresize allow='autoplay; fullscreen; encrypted-media'></iframe></div>", $mdf, "", 'Embed Medias and Contents Using Iframe', 'data-fbp="13" class="fab fa-youtube"', "i"),		
			
				array("[cdi][/cdi]", "\\[cdi\\](.*)\\[/cdi\\]", "<div class='code i'><pre><code class='i'>$1</code></pre></div>", $mdf, "code.gif", 'Inline Code', 'data-fbp="6" class="fas fa-hashtag"', "i"),		
			
				array("[cd=js][/cd]", $bcui="\\[cd=([a-z0-9]+)\\](.*)\\[/cd\\]", "<div class='code'><pre class='prettyprint lang-$1'><code>$2</code></pre></div>", $mdf, "block-code.gif", 'Block Code', 'data-fbp="5" class="fas fa-code"', "i"),		
			
				array("[p][/p]", "\\[p(.*)\\](.*)\\[/p\\]", "<p $1>$2</p>", $mdf, "paragraph.png", 'Paragraph', 'data-fbp="4" class="fas fa-paragraph"', "i"),		
			
				array("[h4][/h4]", "\\[h([1-6])\\](.*)\\[/h[1-6]\\]", "<h$1>$2</h$1>", $mdf, "head.png", 'Headings (H1-H6)', 'data-fbp="5" class="fas fa-heading"', "i"),				
			
				array("[e][/e]", "\\[e( (.*))?\\](.*)\\[/e\\]", "<em $2>$3</em>", $mdf, "", 'Emphasize', 'data-fbp="4"', "parse-only"),		
			
				array("hashTag", $hashTag="\\s\\#([^</> \\[]+)", "<a class='links no-hover-bg' href='/".THREAD_SLUG_CONSTANT."/tagged/hash-tagged/$1'><span class='thread-tag hash-tag'>$2</span></a>", $mdf2, "", '', '', "parse-only"),		
			
				array("@tag", $atTag="\\@(".trim(USERNAME_PATTERN, "/^/$").")", "<a class='links' href='/$1'><span class='at-tag'>$2</span></a>", $mdf2, "", '', '', "parse-only"),		
			
				array("[spn][/spn]", "\\[spn(.*)\\](.*)\\[/spn\\]", "<span $1>$2</span>", $mdf, "", 'span', 'data-fbp="6"', "parse-only"),		
			
				array("[dv][/dv]", "\\[dv(.*)\\](.*)\\[/dv\\]", "<div $1>$2</div>", $mdf, "", 'block division', 'data-fbp="5"', "parse-only"),		
			
				array("[li][/li]", "\\[li(.*)\\](.*)\\[/li\\]", "<li $1>$2</li>", $mdf, "", 'list(li) element', 'data-fbp="5"', "parse-only")
				
			);
		
		
		/****EMOTICONS CRITERION
			max length: 4 
			char set: _:()a-z
			To avoid conflicts; characters can be shuffled but shouldn't be repeated
		****/
		if(!$decode){
			
			$K='EMOTICON_PARENT_ARR';
			$valArr = $emoticonId? array($emoticonId) : ($isEmoticonIdArr? $emoticonIdArr : array());
			$ph = $isEmoticonIdArr? rtrim(str_repeat('?,', $emoticonIdArrLen = count($emoticonIdArr)), ',') : '';
			
			for($pageId=1; ; $pageId++){
			
				if($loadEmoticons)
					$startIndex = $this->getPageRecordIndex($pageId, $perPage);
			
				$sql = "SELECT * FROM ".$emoticonsTable.($emoticonId? " WHERE ID=? LIMIT 1" : ($isEmoticonIdArr? " WHERE ID IN(".$ph.") LIMIT ".$emoticonIdArrLen : " LIMIT ".$startIndex.",".$perPage));
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
				$updatedCategTitle='';
			
				
				/////IMPORTANT INFINITE LOOP CONTROL ////
				if(!$foundDatas = $this->DBM->getSelectCount())
					break;
		
				while($row = $this->DBM->fetchRow($stmt)){
		
					$uiInput = $row["UI_CODE"];
					$unicode = $row["UNICODE_SRC"];
					$categ = $row["CATEGORY"];
					$label = $row["LABEL"];
					$id = $row["ID"];
					$categSlug = $categSlugDecoy = $this->ENGINE->sanitize_slug($categ, array('appendXtn' => false));
		
					if($getRecentlyUsed){
		
						$categ = 'Recently Used';
						$categSlugDecoy = 'recently-used';
		
					}
					
					$collectionTitle = '<div class="lblack"><h5>'.$categ.'</h5></div>';
					$perEmoticon = $accEmoticonsArr[$id] = '<a class="no-hover-bg _js-ignanchor" href="#" aria-label="'.$label.($label? ', emoticon' : '').'"><div class="_28p '.($getPerEmoticon? '_28v' : '').'"><img src="'.$mediaRootEmoticons.$categSlug.'/'.$unicode.$xtn.'" alt="'.($hexPref='&#x').str_replace('_', $hexPref, $unicode).';" class="emoticon _28" data-bbc="'.$uiInput.'" '.($getPerEmoticon? '' : 'data-remoticon="'.$this->INT_HASHER->encode($id).'"').' /></div></a>';
		
					///GROUP EMOTICONS INTO ARRAY BASE ON THEIR CATEGORY
					if(isset($_SESSION[$K])){
		
						if(isset($_SESSION[$K][0][$categSlugDecoy])){
		
							$_SESSION[$K][0][$categSlugDecoy][0] = $_SESSION[$K][0][$categSlugDecoy][0].$perEmoticon;
							$_SESSION[$K][0][$categSlugDecoy][1] = $collectionTitle;
		
						}else{
		
							$_SESSION[$K][0][$categSlugDecoy][0] = $perEmoticon;
							$_SESSION[$K][0][$categSlugDecoy][1] = $collectionTitle;
		
						}
		
					}else{
		
						$_SESSION[$K] = array(array($categSlugDecoy => array($perEmoticon, $collectionTitle)));
		
					}
					
				}
				
				if(!$loadEmoticons){
		
					//BREAK FROM INFINITE LOOP
					break;
		
				}
			
			}
			
			if(isset($foundDatas)){
				
				foreach($_SESSION[$K][0] as $k => $v){
			
					$accEmoticons .= ($getPerEmoticon? '' : '<div class="emoticon-group" id="'.$k.'" >'.$v[1]).$v[0].($getPerEmoticon? '' : '</div>');
			
				}
				
				unset($_SESSION[$K]); //CLEAN UP ARRAY TO AVOID ACCUMULATION AND FREE MEMORY
				
				if($getPerEmoticon || $getRecentlyUsed){
			
					$getRecentlyUsed? ($accEmoticonsArr["CT"] = $collectionTitle) : '';
					return ($retArr? $accEmoticonsArr : $accEmoticons);
			
				}

				if($loadEmoticons){
			
					$JSONArr['loadedEmoticons'] = '<div id="emoticons-slide-show" class="slide-show" data-has-external-pager="true" data-scale-full="true" data-pager-numbers="false" data-animate="slideInRight">'.$this->getRecentlyUsedEmoticons($remoticonKey).$accEmoticons.'</div>';
					$JSONArr['lastRecord'] = true;
					echo json_encode($JSONArr);
					exit();
			
				}
			
			}
			
		}
		
		//EMOTICONS DECODE
		elseif($decode){
			
			$regexIn = $this->emoticonsUiCodeGenerator();
			$regexModifier = $mdf3;
			
			$content = preg_replace_callback($regexIn, 
			
				function($m){
			
					$sql = "SELECT ID FROM emoticons WHERE UI_CODE=? LIMIT 1";
					$valArr = array($m[1]);
					
					if($emoticonId = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn()){
			
						return $this->bbcHandler('', array('action' => 'getPerEmoticon', 'emoticonId' => $emoticonId));
						
					}
					
					return $m[1];
					
				}, $content
			);
			
		}
		
		$accBBCArr=array();
			
		foreach($bbcArr as $bbcMetaArr){
			
			$uiInput = $bbcMetaArr[0];
			$regexIn = $bbcMetaArr[1];
			$regexReplace = $bbcMetaArr[2];
			$regexModifier = $bbcMetaArr[3];
			$src = $bbcMetaArr[4];
			$title = $bbcMetaArr[5];
			$miscDatas = $bbcMetaArr[6];
			$elementType = $bbcMetaArr[7];
			
			if($decode){
			
				$content = ($regexIn == $bcui)?  preg_replace_callback($delim.$regexIn.$delim.$regexModifier, function ($m) use ($regexReplace){return (str_replace(array("$1", "$2"), array($m[1], htmlspecialchars($m[2], ENT_QUOTES)), $regexReplace));}, $content)
				
						: ( in_array($regexIn, array($href1, $href2))?
						
							preg_replace_callback($delim.$regexIn.$delim.$regexModifier, function ($m) use ($regexReplace, $href1, $regexIn){return (str_replace(array("$1", "$2"), array($this->ENGINE->add_http_protocol($m[1], false), (($regexIn == $href1)? $m[2] : $m[1])), $regexReplace));}, $content)
							
							: ( in_array($regexIn, array($hashTag, $atTag))?
							
								preg_replace_callback($delim.$regexIn.$delim.$regexModifier, function ($m) use ($regexReplace, $hashTag, $atTag, $regexIn){return ((($regexIn == $atTag) && !$this->isProfileSlug($m[1]))? $m[0] : str_replace(array("$1", "$2"), array($m[1], (($regexIn == $hashTag)? '#'.$m[1] : '@'.$m[1])), $regexReplace));}, $content)
									
								: ( in_array($regexIn, array($lightBox, $slideBox))?  preg_replace_callback($delim.$regexIn.$delim.$regexModifier, 
										
										function ($m) use ($regexReplace){
											
											$boxSrcs='';
											$srcArr = explode(';', $m[1]);
											
											$srcCount = count($srcArr);
											$maxBoxContent = 5; // Used to limit the number of images/contents allowed in a slide show or light box
											$srcArr = array_slice($srcArr, 0, $maxBoxContent);
											/*
											if($srcCount  > $maxBoxContent)
												return '';
											*/
											foreach($srcArr as $srcTitle){
												
												$srcTitleArr = explode('::', $srcTitle) ;
												$boxSrcs .= '<img src="'.$srcTitleArr[0].'" alt="'.($altTxt = (isset($srcTitleArr[1])? $srcTitleArr[1] : 'image')).'" title="'.$altTxt.'" >';
												
											}
											
											return (str_replace('$1', $boxSrcs, $regexReplace));
											
										}, $content)
									
									: $this->ENGINE->match_nested_regex($delim.$regexIn.$delim.$regexModifier, $regexReplace, $content)
								
								)	
								
							)
							
						);
			
			}else{
			
				$accUI = '';
				$el = strtolower(($elementType? $elementType : 'img'));
			
				if($el == 'parse-only')
					continue;
			
				switch($el){
			
					case 'i': $accUI = '<i data-bbc="'.$uiInput.'" '.$miscDatas.' title="'.$title.'" ></i>'; break;
			
					case 'input': $accUI = '<input data-bbc="'.$uiInput.'" title="'.$title.'" '.$miscDatas.' />'; break;
			
					//default: $accUI = '<a class="no-hover-bg" href="#" aria-label="'.$title.', '.$type.'"><div style="background-image:url(\''.$mediaRootBBC.$src.'\')" data-bbc="'.$uiInput.'" title="'.$title.'" '.$miscDatas.' ></div></a>';
					default: $accUI = $this->getBgImg(array("dir"=>"bbc-icon", "file"=>$src, "title"=>$title, "label"=>$title.', BBC', "type"=>'emoticon', "iAttr"=>' data-bbc="'.$uiInput.'" title="'.$title.'" '.$miscDatas));
			
				}
				
				$accBBCArr[] = $accUI;
			
			}
			
		}
		
		$containerContext = '_mojisbbc_container';
		$containerCls = ' base-b-pad _emojis-ui-container ';
		$containerClrCls = ' _ui-container-clr ';
		$btnMetas = ' class="btn btn-xs btn-info has-caret" data-toggle="smartToggler" data-toggle-child-attr="class|up" data-close-others-in-context="'.$containerContext.'" ';
		$dropCaret = ' <span class="caret-down caret-xs caret-static pos-mid"></span>';
		$uibgDropPaneId = 'uibgDropPane';
		$bbcDropPaneId = 'bbcDropPane';
		$emoticonsDropPaneId = 'emoticonsDropPane';
		
		if($decode)
			return $content;
			
		else
			return '<div data-npipsX="true" class="_custom-scrollbar '.$containerContext.'" >'.
						(($forComposer = ($responseFieldId == COMPOSER_ID))? 
							'<button '.$btnMetas.' data-id-targets="'.$uibgDropPaneId.'" title="toggle user interface(UI) background(BG) images">UI BG'.$dropCaret.'</button>'
						: '').'
						<button '.$btnMetas.' data-id-targets="'.$bbcDropPaneId.'" title="toggle bbc">BBC'.$dropCaret.'</button>
						<button '.$btnMetas.' data-id-targets="'.$emoticonsDropPaneId.'" data-prefetch-emoticon-tabs="true" title="toggle emoticons">EMOTICONS'.$dropCaret.'</button>
						'.
						($forComposer? 
							'<div class="hide '.$containerCls.'" id="'.$uibgDropPaneId.'" '.$this->embedCustomScrollbar().'><div class="'.$containerClrCls.'">'.$this->uiBgHandler($responseFieldId).'</div></div>'
						: '').'
						<div class="bbcs hide '.$containerCls.'" id="'.$bbcDropPaneId.'" '.($resDatas='data-response-id="'.$responseFieldId.'"').'>'.implode('', $accBBCArr).'</div>
						<div class="hide" id="'.$emoticonsDropPaneId.'">
							<div class="'.$containerCls.'">
								<div class="emoticons '.$containerClrCls.'" '.$resDatas.'>
									'.$this->getRecentlyUsedEmoticons($remoticonKey).$accEmoticons.'
									<a role="button" class="btn btn-sc btn-xs" data-emoticon-page="2">Load all</a>
								</div>
							</div>
						</div>
					</div>';
			
			
	}



	
	


	


	
		
	/*** Method for fetching icons as background images ***/
	public function getBgImg($optArr){
		
		$dir = $this->ENGINE->get_assoc_arr($optArr, 'dir'); 
		$file = $this->ENGINE->get_assoc_arr($optArr, 'file'); 
		$title = $this->ENGINE->get_assoc_arr($optArr, 'title'); 
		$url = $this->ENGINE->get_assoc_arr($optArr, 'url'); 
		$useSolidColorStyles = $this->ENGINE->get_assoc_arr($optArr, 'useSolidColor'); 
		$urlText = $this->ENGINE->get_assoc_arr($optArr, 'urlText'); 
		$iAttr = $this->ENGINE->get_assoc_arr($optArr, 'iAttr'); 
		$oAttr = $this->ENGINE->get_assoc_arr($optArr, 'oAttr'); 
		$label = $this->ENGINE->get_assoc_arr($optArr, 'label'); 
		$hoverReact = $this->ENGINE->get_assoc_arr($optArr, 'hoverReact'); 
		$ignoreAtag = $this->ENGINE->get_assoc_arr($optArr, 'ignoreAtag'); 
		$hoverReactBasCtrlCls = $this->ENGINE->get_assoc_arr($optArr, 'hoverReactBaseCtrlCls'); 
		$ocls = ($K=$this->ENGINE->get_assoc_arr($optArr, 'ocls'))? $K.' ' : $K; 
		$icls = ($K=$this->ENGINE->get_assoc_arr($optArr, 'icls'))? ' '.$K : $K;
		$type = strtolower($this->ENGINE->get_assoc_arr($optArr, 'type'));
		$anchor = $this->ENGINE->is_assoc_key_set($optArr, 'anchor')? $this->ENGINE->get_assoc_arr($optArr, 'anchor') : true;
		
		switch($type){
		
			case 'bg': $sizeCls = '_bg-img'; break;
		
			case 'emoticon': $sizeCls = 'emoticon'; break;
		
			default: $sizeCls = '_micon';
		
		}
		
		$icls =  $sizeCls.$icls.($hoverReact? ' _hv-react' : '');
		$_2wayMetas = $title? ' title="'.$title.'" ' : '';
		$micon = '<div class="'.$icls.'" style="'.($useSolidColorStyles? $useSolidColorStyles : 'background-image:url(\''.($dir? $this->getMediaLinkRoot($dir) : '').$file.'\');').'" '.($anchor? '' : $_2wayMetas).$iAttr.'></div>';
		$micon = $hoverReact? '<div class="_hv-react-base '.$hoverReactBasCtrlCls.' '.($anchor? '' : $ocls).'" '.($anchor? '' : $oAttr).'>'.$micon.'</div>' : $micon;
		
		return ($anchor? '<a href="'.($url? $url : '#').'" class="'.(($url && !$ignoreAtag)? '' : '_js-ignanchor ').$ocls.'no-hover-bg" aria-label="'.($label? $label : $title).'" '.$_2wayMetas.$oAttr.'>'.$urlText.$micon.'</a>' : $micon);
				
	}






	

	

	
	
		
	/*** Method for fetching page record index ***/
	public function getPageRecordIndex($pageId, $perPage){
		
		return ((($pageId? $pageId : 1) * $perPage) - $perPage);
		
	}




	
	
		
	/*** Method for creating pagination ***/
	public function paginationHandler($metaArr){
		
		$pageId=$pageIdPassed=$startIndex=$pagination=$leftSidePaging=$rightSidePaging=$nextPage=$prevPage=$firstPage=
		$totalPage=$lastPage=$urlQstr=$urlQstrAcc=$rightSidePagingExtend="";
			
		$totalRec = $this->ENGINE->get_assoc_arr($metaArr, 'totalRec');
		$pageUrl = $this->ENGINE->get_assoc_arr($metaArr, 'url');
		$perPage = $this->ENGINE->get_assoc_arr($metaArr, 'perPage');
		$pageIdKey = $this->ENGINE->get_assoc_arr($metaArr, 'pageKey');
		!$pageIdKey? ($pageIdKey = 'pageId') : '';
		$qstrKeyValArr = $this->ENGINE->get_assoc_arr($metaArr, 'qstrKeyVal', true);
		$qstrValArr = $this->ENGINE->get_assoc_arr($metaArr, 'qstrVal', true);
		$jmpPageKey = $this->ENGINE->get_assoc_arr($metaArr, 'jmpKey');
		!$jmpPageKey? ($jmpPageKey = 'jump_page') : '';
		$paginationType = $this->ENGINE->get_assoc_arr($metaArr, 'type');
		!$paginationType? ($paginationType = ' type-1 ') : '';
		$cssClass = $this->ENGINE->get_assoc_arr($metaArr, 'cssClass');
		$cssClass = $paginationType.$cssClass;
		$urlHash = $this->ENGINE->get_assoc_arr($metaArr, 'hash');
		$showRecCrumbs = $this->ENGINE->is_assoc_key_set($metaArr, $K='ShowRecordCrumbs')?	(bool)$this->ENGINE->get_assoc_arr($metaArr, $K) : true;	
		$useFlat = $this->ENGINE->is_assoc_key_set($metaArr, $K='useFlat')? (bool)$this->ENGINE->get_assoc_arr($metaArr, $K) : true;	
		$navigators = $this->ENGINE->is_assoc_key_set($metaArr, $K='navigators')?	(bool)$this->ENGINE->get_assoc_arr($metaArr, $K) : true;	
		$nested = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'nested');	
		$activePage = $this->ENGINE->is_assoc_key_set($metaArr, $K='activePage')?	(bool)$this->ENGINE->get_assoc_arr($metaArr, $K) : true;	
		$activePageClass = $activePage? 'active-page' : '';
		$maxPageOnSides = $this->ENGINE->get_assoc_arr($metaArr, 'maxPageOnSides');
		$maxPageOnSides = $maxPageOnSides? $maxPageOnSides : 4;
		$extendLast = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'extendLast');
		$forceRDR = $this->ENGINE->get_assoc_arr($metaArr, 'forceRdr');	
		$qstrEncode = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'qstrEncode');
		$currBaseUrl = strtolower($this->ENGINE->get_page_path('page_url', 1));
		
		$qstrCombiner = '&';
		
		###MAKE SURE YOU PAGE ONLY IF THERE IS A RECORD TO PAGE
		if($totalRec){
			
			###SET DEFAULT PAGE SEPERATOR TO /
			$pageSep = '/';
			
			###GET ALL QSTR PASSED FROM THE QSTR NAME AND VALUE ARRAY
			
			if(!empty($qstrKeyValArr))
				foreach($qstrKeyValArr as $k => $v){
					
					if($v)
						$urlQstrAcc .= ($k.'='.($qstrEncode? urlencode($v) : $v).$qstrCombiner);
				
				}
			
			if(!$useFlat){
				
				$urlQstr = '?'.$urlQstrAcc; //prepend ? for non flat type pagination qstr
				$pageSep = $pageIdKey.'=';//override default page seperator
				
				
			}else
				for($idx=0; $idx < count($qstrValArr); $idx++){
					
					if(isset($qstrValArr[$idx]) && $qstrValArr[$idx])
						$urlQstr .= '/'.($qstrEncode? urlencode($qstrValArr[$idx]) : $qstrValArr[$idx]);
					
				}					
			
			###ENSURE PER PAGE IS ALWAYS PASSED TO AVOID ZERO DIVISION ERROR ON TOTAL PAGE CALCULATED BELOW		
			if(!$perPage)
				$perPage = $this->getPaginationCount();
			
			###GET THE TOTAL PAGES THAT THE ENTIRE RECORD WILL SPAN
			$totalPage = ceil($totalRec/$perPage);
			
			if(!$totalPage)
				$totalPage = 1;	
					
			
			####DECIDE THE PAGE ID TO USE(pageId sent via $_GET Vs pageId sent via $_POST(jump to page))
			###REMEMBER TO FILTER INVALID XTERS

			///use pageId sent via $_POST(jump to page) if it's set		
			if(isset($_POST[$jmpPageKey])){	
			
				if(isset($_POST[$K="page_input"]) && $_POST[$K])
					$pageId = preg_replace("#[^0-9]#", "", $_POST[$K]);	
				
				else
					$pageId = $totalPage; //default to total page if no value was entered on jump
					
				//always rdr on jump to update the url with the pageId
				$useForceRDR = true;
				
			}
			//else use the pageId sent via $_GET if it's set
			elseif(isset($_GET[$pageIdKey]) && ($_GET[$pageIdKey])){
				
				$pageId = $pageIdPassed = preg_replace("#[^0-9]#", "", $_GET[$pageIdKey]);	
				
			}
			
			###if no pageId after filtering, DEFAULT TO PAGE 1
			if(!$pageId)
				$pageId = 1;
			
			###trim off trailing zeros from pageId
			if(mb_strlen($pageId) > 1 && substr($pageId, 0, 1) == 0){
				
				$pageId = ltrim($pageId, 0);
				$useForceRDR = true;
				
			}
			
			###MAKE SURE THE pageId PASSED DOES NOT EXCEED THE TOTAL PAGES THE ENTIRE RECORDS CAN SPAN
			if($pageId > $totalPage){
				
				$pageId = $totalPage;
				
				if(!$nested)
					$useForceRDR = true;
				
				
			}elseif($pageIdPassed == 1 && !$nested) /**HIGHLY VITAL**/
				$useForceRDR = true;
			
			
			###GET URL HASH IF ANY
			if($urlHash)
				$urlHash = '#'.trim($urlHash, '#');
			
			###CAPTURE AND APPEND LITERAL QSTRS TO FLAT URLs
			if($useFlat){
				
				$urlQstrAcc = trim($urlQstrAcc.$_SERVER["QUERY_STRING"], $qstrCombiner);
				$urlQstrAcc = implode($qstrCombiner, array_unique(explode($qstrCombiner, $urlQstrAcc)));
				$urlHash = ($urlQstrAcc? ($flatAppendQstr='?'.$urlQstrAcc) : '').$urlHash;
				
			}
				
												
				
			###SETUP JUMP PAGE URL AS THE CURRENT PAGE URL WITH QUERY STRINGS APPENDED
			$fullPageUrl = trim($pageUrl.$urlQstr, '/').($pageId? $pageSep : '');
			
			###ENSURE WHEN JUMPING TO A NEW URL YOU FIRST POST TO THE CURRENT PAGE 
			###SO AS TO EXTRACT THE pageId B4 REDIRECTING
			$jmpUrl = ($currBaseUrl != mb_substr(strtolower($pageUrl), 0, mb_strlen($currBaseUrl)))?
				$this->ENGINE->get_page_path('page_url') : $pageUrl.$urlQstr.$urlHash;
			
			###SET DEFAULT FALLBACK(FORCERDR) 
			if(!$forceRDR)
				$forceRDR = $fullPageUrl;				
			
			/******FORCE REDIRECT CONTROLLER***********/
			if(isset($useForceRDR)){	
			
				###ENSURE THERE IS A SLASH B4 pageId WHEN USING FORCERDR
				if($useFlat && mb_substr($forceRDR, -1) != '/')
					$forceRDR .= '/';							
				
				//force page 1 to same as the root page								
				header("Location:/".($forceRDR.(($pageId == 1)? (isset($flatAppendQstr)? $flatAppendQstr : '') : $pageId.$urlHash)), true, 302);
				exit();		
				
			}					
			
			//CALCULATE THE STARTING FROM THE PAGE ID PASSED////
			
			$startIndex = $this->getPageRecordIndex($pageId, $perPage);
						 
			###GENERATE THE PAGINATION LINKS		 
			###SHOW THE PAGINATION ONLY IF THE TOTAL RECORDS IN DB EXCEEDS ONE PAGE		  
			if($totalPage > 1){
				
				////only show the FIRST and PREV PAGE NAVIGATOR when pageId is not the first///				 
				if($pageId > 1){
					
					////DEFINE FIRST PAGE NAVIGATOR///
					$firstPage = 1;					 
					$firstPage = '<a class="page" href="/'.$fullPageUrl.$firstPage.$urlHash.'" >First</a>';
				
					////DEFINE PREV_PAGE NAVIGATOR////			 
					$prevPage = $pageId - 1;							
					$prevPage = '<a class="page" href="/'.$fullPageUrl.$prevPage.$urlHash.'" > &laquo; Prev</a>';
																		
				}
				
				/******create LEFT SIDE PAGING, Limit to  $maxPageOnSides from current PageId*********/	
				$beginL = ($pageId - $maxPageOnSides);
				$nL = $pageId;		
				
				for($i=$beginL; $i <= $nL; $i++){
					
					if($i < 1)//we don't want to see zero and negative pages
						continue;						
					$leftSidePaging .= '<a class="page '.(($i == $pageId)? $activePageClass : '').'" href="/'.$fullPageUrl.$i.$urlHash.'" >'.$i.'</a>';										
					
				}
				 
				////only show the RIGHT SIDE PAGING,NEXT and LAST PAGE NAVIGATOR when pageId is not the last///				 				 
				if($pageId != $totalPage){	
				
					////DEFINE LAST PAGE NAVIGATOR///	
					$lastPage = $totalPage;							
					$lastPage = '<a class="page" href="/'.$fullPageUrl.$lastPage.$urlHash.'" >Last</a>';
					
					////DEFINE NEXT PAGE NAVIGATOR///			 
					$nextPage = $pageId + 1;			 
					$nextPage = '<a class="page" href="/'.$fullPageUrl.$nextPage.$urlHash.'" >Next &raquo;</a>';
						
					/******create RIGHT SIDE PAGING, Limit to $maxPageOnSides from current PageId*********/					
					$dots = ' ...... ';
					$nR = $sideSpan = ($pageId + $maxPageOnSides);
					$beginR = ($pageId + 1);
					
					for($i=$beginR; $i <= $nR; $i++){
						
						$rightSidePaging .= '<a class="page '.(($i == $pageId)? $activePageClass : '').'" href="/'.$fullPageUrl.$i.$urlHash.'" >'.$i.'</a>';
						
						if($i == $nR && $i < $totalPage && $extendLast){	
						
							for($y=($totalPage - $maxPageOnSides); $y <= $totalPage; $y++){
								
								//Control the behaviour of extendLast here
								##$y <= $sideSpan => ensure to start from numbers above span 
								##($sideSpan + 1) == $y => ensures there's at least a page gap eg 86...88
								##($totalPage - $y) == $maxPageOnSides => ensures extension does'nt exceed maxPageOnSides
								if($y <= $sideSpan || ($sideSpan + 1) == $y || ($totalPage - $y) == $maxPageOnSides)
									continue;
								
								$rightSidePagingExtend .= '<a class="page '.(($y == $pageId)? $activePageClass : '').(!isset($extensionBegan)? ' extBeg ' : '').'" href="/'.$fullPageUrl.$y.$urlHash.'" >'.$y.'</a>';
								$extensionBegan = true;
								
							}
							
							$rightSidePaging .= $rightSidePagingExtend? $dots.$rightSidePagingExtend : ''; 
							
						}
						
						/**ensure TOTAL PAGE IS NOT EXCEEDED******/					
						if($i >= $totalPage)
							break;	
						
					}					
				}
				
				if(!$navigators)//If navigators not needed
					$firstPage=$prevPage=$nextPage=$lastPage='';
				
				$mediaRootFav = $this->getMediaLinkRoot("favicon");
				
				###GENERATE THE FINAL PAGINATION UI
				$fTglDatas = ' data-target-prev="true" data-target-inline="true" data-toggle-attr="title|close" data-no-outline="true" ';
				$fTgl2Datas = ' data-attr-only="true" data-toggle-attr="src|'.$mediaRootFav.'closemenu.png" ';
				$pagination = '<div  id="ptab"  class="pagination '.$cssClass.'">'
									.$firstPage.$prevPage.$leftSidePaging.$rightSidePaging.$nextPage.$lastPage.' 
									<form class="jumppage hide"  method="post" action="/'.$jmpUrl.'" >
										<input class="jumppage-input" type="number" name="page_input" min="1" placeholder="Enter Page Number" />
										<input class="jumppage-btn" type="submit" name="'.$jmpPageKey.'" value="GO" />
									</form>
									<a title="jump to page" href="javascript:void(0)" class="skip-page no-hover-bg links" data-toggle="smartToggler" '.$fTglDatas.'>
										<img class="fav-img" src="'.$mediaRootFav.'skippage.png" alt="icon" data-toggle="smartToggler" '.$fTgl2Datas.' />
									</a>
									'.($showRecCrumbs? '<div><small>showing record ('.(($pageId == 1)? $pageId : ((($pageId > 1)? 1 : 0) + (($pageId - 1) * $perPage))).' - '.(($pageId == $totalPage)? $totalRec : ($perPage * $pageId)).' of '.$totalRec.') </small></div>' : '').'
								</div>';

								
			}
					
		}
		
		//VERY IMPORTANT///
		if(!$startIndex)
			$startIndex = 0;
		if(!$perPage)
			$perPage = 1;
		if(!$pageId)
			$pageId = 0;
		if(!$totalPage)
			$totalPage = 0;
		
		return array('pagination' => $pagination, 'pageId' => $pageId, 'totalPage' => $totalPage,
			'perPage' => $perPage, 'startIndex' => $startIndex, 'prev' => $prevPage, 'next' => $nextPage);
		
		
	}



	
	




	
	
	
	
	
		
	/*** Method for handling pm message display ***/
	public function pmHandler($metaArr){

		$data=$mssgnum=$pageHead=$isInbox=$isOldInbox="";
		
		global $GLOBAL_siteName, $GLOBAL_page_self, $GLOBAL_rdr,
		$GLOBAL_notLogged;		
		
		$siteName =  $GLOBAL_siteName;
		$pageSelf = $GLOBAL_page_self;
		
		$isSentPm =  false;
		$type =  strtolower($this->ENGINE->get_assoc_arr($metaArr, 'type'));	
		$backDoor =  strtolower($this->ENGINE->get_assoc_arr($metaArr, 'backDoor'));
		$uid = strtolower($this->ENGINE->get_assoc_arr($metaArr, 'uid'));
		$uid = $uid? $uid : $this->ACCOUNT->SESS->getUserId();		
		$countSessNew = strtolower($this->ENGINE->get_assoc_arr($metaArr, 'countSessNew'));	
		$U = $this->ACCOUNT->loadUser($uid);
		$username = $U->getUsername();
		$userId = $U->getUserId();

		$pmCheckBoxAlert = '<div class="total-pm-checked"></div>';
		
		/***************************BEGIN URL CONTROLLER****************************/
		if(!$countSessNew){

			$full_path =  $this->ENGINE->get_page_path('page_url', '', true);
			$path_arr = explode("/", $full_path);
			
			$pathKeysArr = $backDoor? array('pageUrl', 'user', 'event', "pageId") : array('pageUrl', "pageId");
			$maxPath = $backDoor? 4 : 2;	
			
			$this->ENGINE->url_controller(array('pathKeys'=>$pathKeysArr, 'maxPath'=>$maxPath));

		}
		/*******************************END URL CONTROLLER***************************/	
		
		
		
		if($username){
			
			switch($type){

				case 'old-inbox': $typeCol = 'OLD_INBOX'; $isOldInbox = true; $typeUrl = $backDoor? 'user-events/'.$username.'/old-inbox' : 'old-inbox'; 
						$emptyUrl = '/delete-pm/old-inbox'; $emptyUrlTxt = 'delete old messages'; 
						$delSelUrl = '/delete-pm/selected-old-pm';  $pageHead = 'Old Messages';
						break;

				case 'sent-pm': $typeCol = ''; $isSentPm = true; $typeUrl = $backDoor? 'user-events/'.$username.'/sent-pm' : 'sent-pm'; 
						$emptyUrl = ''; $emptyUrlTxt = ''; 
						$delSelUrl = '';  $pageHead = 'Sent PM';
						break;

				default: $typeCol = 'INBOX'; $isInbox = true; $typeUrl = $backDoor? 'user-events/'.$username.'/inbox' : 'inbox';
						$emptyUrl = '/delete-pm/clear-inbox'; $emptyUrlTxt = 'clear inbox'; 
						$delSelUrl = '/delete-pm/selected-pm'; $pageHead = 'Inbox';

					
			}

			$pageHead = ($backDoor? $username.' => ' : 'My ').$pageHead;
			
			$countSubQry = $isSentPm? '' : ($countSessNew? 'READ_STATUS !=1' : $typeCol.' !=""');
			$cnd = $isSentPm? 'SENDER_ID=?' : 'USER_ID=? AND '.$countSubQry;

			///////////PDO QUERY///////////
			$sql = "SELECT COUNT(*) AS TOTAL_RECS FROM private_messages WHERE (".$cnd.")";
			$valArr = array($userId);
			$totalPm = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
			if($countSessNew)
				return $totalPm;
			
			//////DECIDE OLD MESSAGES/////////

			///////////PDO QUERY///////////
			$sql = "UPDATE private_messages SET OLD_INBOX = INBOX, INBOX = '' WHERE (USER_ID=? AND INBOX != '' AND READ_STATUS = 1 AND TIME >= (TIME + INTERVAL 1 WEEK))";
			$valArr = array($userId);
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);	

			/////////////////RESET ALL CHECKS NOT EXECUTED//////////////////
			///////////PDO QUERY///////////
			$sql = "UPDATE private_messages SET SELECTION_STATUS=0 WHERE USER_ID=? AND SELECTION_STATUS !=0";
			$valArr = array($userId);
			$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			
			if(isset($isInbox) && !$backDoor){

				/////////////UPDATE READ STATUS IN DB//////
				///////////PDO QUERY///////////
				$sql = "UPDATE private_messages SET READ_STATUS=1 WHERE USER_ID=? AND READ_STATUS !=1";
				$valArr = array($userId);
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);

			}
			if($totalPm){
				
				$totalRecords = $totalPm;

				/**********CREATE THE PAGINATION*******/			
				$pageUrl  = $typeUrl;
				$paginationArr = $this->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'extendLast'=>true));
				$pagination = $paginationArr["pagination"];
				$totalPage = $paginationArr["totalPage"];
				$perPage = $paginationArr["perPage"];
				$startIndex = $paginationArr["startIndex"];
				$pageId = $paginationArr["pageId"];

				///////////////END OF PAGINATION/////////////

				///////////PDO QUERY///////////
				$sql = "SELECT * FROM private_messages WHERE ".$cnd." ORDER BY TIME DESC LIMIT ".$startIndex.",".$perPage;
				$valArr = array($userId);
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);	
					
				while($row = $this->DBM->fetchRow($stmt)){
					
					$pmId = $row["ID"];
					$pmSender = $this->ACCOUNT->memberIdToggle($row["SENDER_ID"]);
					$pmReceiver = $this->ACCOUNT->memberIdToggle($row["USER_ID"]);

					if(!$pmSender)
						$pmSender = '';

					$pmSubject = $this->bbcHandler('', array('action' => 'decode', 'content' => $row["MESSAGE_SUBJECT"]));								
					$pmContent = $this->bbcHandler('', array('action' => 'decode', 'content' => ($isSentPm? ($row[$K="INBOX"]? $row[$K] : $row["OLD_INBOX"]) : $row[$typeCol])));				
					$timeSent = $row["TIME"];

					///FORMAT THE WAY DATES ARE SHOWN///////
				 
					$pmDate = $this->ENGINE->time_ago($timeSent);
					$pmReplyLink = '<a role="button" href="/pm/reply/'.$pmId.'" class="pull-r btn btn-success">Reply</a>';
					$profileSanMetaArr = array('anchor'=>true, 'cls'=>'prime bg-white');
					
					$data .= '<div class="pm-base">
								<div class="base-rad base-b-pad clear">
									<header class="pm-sender clear">sent '.($isSentPm? 'to' : 'by').': <b>'.($isSentPm? $this->ACCOUNT->sanitizeUserSlug($pmReceiver, $profileSanMetaArr) : ($pmSender? $this->ACCOUNT->sanitizeUserSlug($pmSender, $profileSanMetaArr) : "webmaster")).'</b> ('.$pmDate.')  '.(($pmSender && !$backDoor && !$isSentPm)? $pmReplyLink : '').'</header>
									<div class="pm-subject" title="'.$pmId.'"><span class="green">Subject:</span> <span class="prime">'.$pmSubject.'</span></div>					
									<div class="pm-contents">'.($backDoor || $isSentPm? '' : '<label><input title="check this message for delete" type="checkbox" data-pm="'.$pmId.'" data-name="delchk" class="checkbox_inbox checkbox" /></label>').'<div class="pm-content-ctrl align-l">'.nl2br($pmContent).'</div></div>'.(($pmSender && !$backDoor && !$isSentPm)? '<div class="base-pad">'.$pmReplyLink.'</div>' : '').'
								</div>
							</div>';	
									
								
				}
				
				if(!$isSentPm){
					
					$checkAll =  '<li><a class="links" href="/pm-blacklist">pm blacklist</a></li><li><a href="/'.$typeUrl.'" class="links pm-check-all" >check all</a></li>';
					
					function getToggle($type, $url, $boxUnqId, $metaArr){
				
						!$type? ($type = 'del-all') : '';

						list($pmCheckBoxAlert, $emptyUrlTxt, $username) = $metaArr;
						
						switch(strtolower($type)){
							
							case 'select': 
							
								$link = '<li><a href="'.$url.'" class="links" data-toggle="smartToggler" data-id-targets="'.$boxUnqId.'" >delete selected</a></li>';
								
								$cfm = '<div id="'.$boxUnqId.'" class="red modal-drop hide has-close-btn">
											'.$pmCheckBoxAlert.'
											<p>You are about to delete the highlighted messages</p>
											<p>Please confirm</p>
											<a href="'.$url.'" class="btn btn-danger" role="button" >delete selected</a>
											<button class="btn close-toggle">close</button>
										</div>';
										
								break;
								
							default:
							
								$link = '<li><a href="'.$url.'"  class="links" data-toggle="smartToggler" data-id-targets="'.$boxUnqId.'" >'.$emptyUrlTxt.'</a></li>';
								
								$cfm = '<div id="'.$boxUnqId.'" class="hide" ><div class="alert alert-danger">
											<b/> 
												WARNING!!!<hr/>'. strtoupper($username).'<br/><br/> 
												you are about to delete all your '.((stripos($emptyUrlTxt, 'old') !== false)? 'old' : '').' inbox messages 
												<br/><br/>please confirm<br/><br/>NOTE: you will no longer be able to access them once deleted<br/>
												<input type="button"  class="btn btn-danger empty-inbox" value="OK" data-landpage="'.$url.'" /> 
												<input class="btn close-toggle" type="button" value="CLOSE" />
											</b>
										</div></div>';
								
							
							
						}
								
								
						return array($link, $cfm);

					}
					
					$metaArr = array($pmCheckBoxAlert, $emptyUrlTxt, $username);
					
					list($deleteSelectedLink, $delSelCfm) = getToggle($K='select', $delSelUrl, 'del-select-top-cfm', $metaArr);			
					list($deleteSelectedLinkBtm, $delSelCfmBtm) = getToggle($K, $delSelUrl, 'del-select-btm-cfm', $metaArr);
					list($emptyLink, $emptyLinkCfm) = getToggle($K='', $emptyUrl, 'del-all-top-cfm', $metaArr);			
					list($emptyLinkBtm, $emptyLinkCfmBtm) = getToggle($K, $emptyUrl, 'del-all-btm-cfm', $metaArr);	
				
				}
				
			}else
				$data = '<span class="alert alert-danger">sorry '.(!$backDoor? 'you have' : $username.' has').' no '.($isSentPm? 'sent pm yet' : ($isInbox? 'new messages in '.(!$backDoor? 'your' : '').' inbox' : 'old messages')).'</span>';
							
			$linkTo =  ($isSentPm? '' : '<li><a class="links" href="/sent-pm">view sent pm</a></li>').'<li><a class="links" href="/'.($isInbox? 'old-inbox' : 'inbox').'">'.($isInbox? 'view older messages' : 'view inbox').'</a></li>';
			

		}else
			$notLogged = $GLOBAL_notLogged;
		

		$this->buildPageHtml(array("pageTitle"=>$pageHead,	
				"preBodyMetas"=>$this->getNavBreadcrumbs('<li><a href="/'.$pageSelf.'" title="">'.$pageHead.'</a></li>'),
				"pageBody"=>'
				<div class="single-base blend">
					<div class="base-ctrl">'.
						(isset($notLogged)? $notLogged : '').
						($username? '
						<div class="panel panel-mine-1">									
							<h1 class="panel-head page-title">'.strtoupper($pageHead).' '.(isset($totalPm)? '<span class="small">('.$totalPm.')</span>' : '').'</h1>					
							<div class="panel-body pm-root">
								<h2>'.(isset($pagination)? '(Page <span class="cyan">'.$pageId.'</span> of '.$totalPage.')' : '').'</h2>'.
								(isset($pagination)?  $pagination : '').'
								<div id="deletedselection"></div>'.
								(!$backDoor? '
									<nav class="nav-base no-pad">
										<ul class="nav nav-pills justified-center">'.
											(isset($checkAll)? $checkAll : '').
											(isset($deleteSelectedLink)? $deleteSelectedLink : '').
											(isset($linkTo)? $linkTo : '').
											(isset($emptyLink)? $emptyLink : '').'
										</ul>
									</nav>'.(isset($delSelCfm)? $delSelCfm : '').(isset($emptyLinkCfm)? $emptyLinkCfm : '') : ''
								).$pmCheckBoxAlert.$this->ENGINE->get_global_var('ss', "SESS_ALERT").(isset($data)? $data : '').
								(isset($pagination)? $pagination : '').
								(!$backDoor? '
									<nav class="nav-base no-pad">
										<ul class="nav nav-pills justified-center">'.
											(isset($checkAll)? $checkAll : '').
											(isset($deleteSelectedLinkBtm)? $deleteSelectedLinkBtm : '').
											(isset($linkTo)? $linkTo : '').
											(isset($emptyLinkBtm)? $emptyLinkBtm : '').'
										</ul>
									</nav>'.(isset($delSelCfmBtm)? $delSelCfmBtm : '').(isset($emptyLinkCfmBtm)? $emptyLinkCfmBtm : '') : ''
								).'
								'.$pmCheckBoxAlert.'
							</div>
						</div>' : '').'
					</div>
				</div>'
		));
		
	}


	
	
	
	
	
	
		
	/*** Method for handling private message blacklist ***/
	public function pmBlacklistHandler($meta){
		
		global $GLOBAL_isAdmin, $GLOBAL_notLogged, $GLOBAL_sessionUrl, $GLOBAL_sessionUrl_unOnly,
		$GLOBAL_page_self_rel, $rdr;
		
		$table = 'pm_blacklists';
		$userId = $this->ENGINE->is_assoc_key_set($meta, $K='uid')? $this->ENGINE->get_assoc_arr($meta, $K) : $this->ACCOUNT->SESS->getUserId();
		$blacklistUserId = $this->ENGINE->get_assoc_arr($meta, 'buid');
		$U = $this->ACCOUNT->loadUser($blacklistUserId);
		$blacklistUsername = $U->getUsername();
		$blacklistIsStaff = $U->isStaff();
		$action = strtolower($this->ENGINE->get_assoc_arr($meta, 'action'));
		$pageUrl  = $this->ENGINE->get_page_path('page_url', 1);
		$isAjax = $this->ENGINE->is_ajax();
		$backBtn = $isAjax? '' : $this->getBackBtn();
		//$rdr? '' : ($rdr = '/pm-blacklist');
		$acc=$alert=$totalRecords='';

		$add = 'add'; 
		$remove = 'remove'; 
		$clear = 'clear';
		$check = 'check';
		
		switch($action){
		
			case $add:
			case $remove:
			case $clear:
			case $check:
				$add = ($action == $add);
				$remove = ($action == $remove);
				$clear = ($action == $clear);
				$check = ($action == $check);
		
				if(!$clear && !$blacklistUsername){

					$alert = '<span class="alert alert-warning">Sorry no valid data was specified '.$backBtn.'<span>';
					
					///AJAX RELOAD EXIT////	
					if($isAjax){		
									
						$res['res'] = $alert;
						echo json_encode($res);
						exit();

					}
				
					return $alert;

				}
		
				$sql = "SELECT STATE FROM ".$table." WHERE (USER_ID=? AND BLACKLISTED_USER_ID=?) LIMIT 1";
				$valArr = array($userId, $blacklistUserId);
				$state = $this->DBM->doSecuredQuery($sql, $valArr, true)->fetchColumn();
				$found = $this->DBM->getRecordCount();
		
				if($check)
					return $state;
		
				if($blacklistUserId != $userId){

					if(!$blacklistIsStaff || !$add || $GLOBAL_isAdmin){
			
						if($found || $clear){
			
							$valArr = array($userId);
							$clear? '' : ($valArr[] = $blacklistUserId) ;
							$sql = "UPDATE ".$table." SET STATE = ".($add? 1  : 0)." WHERE USER_ID=? ".($clear? '' : 'AND BLACKLISTED_USER_ID=? LIMIT 1');
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			
						}elseif($add){
			
							$sql = "INSERT INTO ".$table." (USER_ID, BLACKLISTED_USER_ID, STATE, TIME) VALUES(?,?, 1, NOW())";
							$valArr = array($userId, $blacklistUserId);
							$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			
						}
			
						$blacklistedUserUrl = $this->ACCOUNT->sanitizeUserSlug($blacklistUsername, array('anchor'=>true));
			
						if($remove && !$state){
			
							$alert = 'The user: '.$blacklistedUserUrl.' was not found on your blacklist. '.$backBtn;
			
						}else{
			
							$alert = $clear? 'Your pm blacklist '.($found? 'has been' : 'is already').' emptied' : 
							$GLOBAL_sessionUrl.($add? ' added ' : ' removed ').$blacklistedUserUrl.($add? ($state? ' already' : '').' to ' : ' from ').'your <a class="links" href="/pm-blacklist">pm blacklist</a>';
			
						}

						$alertCls = ($state? 'danger' : 'success');
			
					}else{

						$alert = 'Sorry '.$GLOBAL_sessionUrl.' you cannot blacklist a staff. '.$backBtn;
						$alertCls = 'danger';

					}
				
				}else{

					$alert = 'Sorry '.$GLOBAL_sessionUrl.' cannot blacklist yourself. '.$backBtn;
					$alertCls = 'danger';

				}
				
				$alert = '<span class="alert alert-'.$alertCls.'">'.$alert.'<span>'; 
						
				///AJAX RELOAD EXIT////	
				if($isAjax){		
								
					$res['res'] = $alert;
					echo json_encode($res);
					exit();

				}
				
				$this->ENGINE->set_global_var('ss', 'SESS_ALERT', $alert);
				
				if($rdr){

					header("Location:".$rdr."#prof-pmb");
					exit();

				}
				
				return $alert; break;
				
			default:
				if($this->ACCOUNT->SESS->getUsername()){
					
					///////////PDO QUERY///////////
					$sql = "SELECT COUNT(*) FROM ".$table." WHERE USER_ID=? AND STATE=1 ";
					$valArr = array($userId);
					$totalRecords = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
			
					/**********CREATE THE PAGINATION*******/			
					$paginationArr = $this->paginationHandler(array('totalRec'=>$totalRecords, 'url'=>$pageUrl));
					$pagination = $paginationArr["pagination"];
					$totalPage = $paginationArr["totalPage"];
					$perPage = $paginationArr["perPage"];
					$startIndex = $paginationArr["startIndex"];
					$pageId = $paginationArr["pageId"];
					
					$sql = "SELECT * FROM ".$table." WHERE USER_ID=? AND STATE=1 ORDER BY TIME DESC LIMIT ".$startIndex.",".$perPage;
					$valArr = array($userId);
					$stmt = $this->DBM->doSecuredQuery($sql, $valArr);		
						
					while($row = $this->DBM->fetchRow($stmt)){
		
						$acc .= $this->ACCOUNT->getUserVcard($row["BLACKLISTED_USER_ID"], array('time'=>$row["TIME"],
						'append'=>'<a role="button" '.$this->runByAjax(array('reloadUrl' => $GLOBAL_page_self_rel)).' class="btn btn-primary btn-xs" href="/pm-blacklist/remove/'.$this->ACCOUNT->memberIdToggle($row["BLACKLISTED_USER_ID"]).'" title="Remove from blacklist">remove</a>'));
		
					}
					
					$acc = $acc? '<div class="hr-dividers">'.$acc.'</div>' :
						'<span class="alert alert-danger">'.$GLOBAL_sessionUrl.' have not added anyone to your pm blacklist yet</span>';
		
				}else{
		
					$notLogged = $GLOBAL_notLogged;
		
				}
				
				$getToggle = '(<a href="/inbox" class="links" >view inbox</a>'.($totalRecords? ' | <a title="Clear your pm blacklist" href="'.($K='/pm-blacklist/clear').'" class="links" data-toggle="smartToggler" >remove all blacklisted users</a>' : '').')
							<div class="hide alert alert-warning has-close-btn">
								<p>You are about to clear your pm blacklist<br/>Please confirm</p>
								<a role="button" title="Clear your pm blacklist" href="'.$K.'" class="links clear_pmb btn btn-danger" >Remove All</a>
								<button class="btn close-toggle">Close</button>
							</div>';
					
				$pageTitle	= 'PM Blacklist';
								
				$ajaxReloadableContent = 
				(isset($notLogged)? $notLogged : 
					'<h1 class="panel-head page-title">'.$pageTitle.'</h1>'.
					((isset($pageId) && $pageId)? '<div class="cpop">(<span class="cyan">'.$pageId.'</span> of '.$totalPage.')</div>' : '').'									
					<div class="panel-body sides-padless" >									
						<div class="" >
							<span class="black"> You have blacklisted (<span class="cyan">'.$totalRecords.'</span>) person'.($totalRecords > 1? 's' : '').' from sending you private messages<hr/>
						</div>'.$getToggle.$pagination.'
						<div class="">
							<div class="inline-form-group hr-divider bd-inverse">'
								.
								$this->getSearchForm(array('url' => ''.$GLOBAL_page_self_rel, 'fieldName' => 'blacklist_uid', 
								/*'pageResetUrl' => $pageUrl,*/ 'fieldLabel' => 'Add To Blacklist',  'formAttr' => $this->runByAjax(array('reloadUrl' => $GLOBAL_page_self_rel)),
								'fieldPH' => 'username', 'btnName' => 'blacklist', 'btnLabel' => 'Add'))
								.
							'</div>'
							.$acc.
						'</div>'.
						$pagination.$getToggle.'	
					</div>'
				);
						
				///AJAX RELOAD EXIT////	
				if($this->ENGINE->is_ajax()){		
								
					$res['res'] = $ajaxReloadableContent;
					echo json_encode($res);
					exit();

				}
				
				$this->buildPageHtml(array("pageTitle"=>$pageTitle,
							"preBodyMetas"=>$this->getNavBreadcrumbs('<li><a href="'.$GLOBAL_page_self_rel.'" title="">'.$pageTitle.'</a></li>'),
							"pageBody"=>'										
							<div class="single-base blend">
								<div class="base-ctrl">
									<div class="panel panel-limex" data-ajax-rel-rcv="">
										'.$ajaxReloadableContent.'										
									</div>
								</div>
							</div>'
				));
		
		}
		
	}


	
	


	
	
		
	/*** Method for logging private message into database ***/
	public function sendPm($senderId, $recId, $msgSubj, $msg){
		
		$stat = true;
		!$senderId? ($senderId = 0) : '';
		
		if($recId && $msgSubj && $msg){	
			
			$sql = "INSERT INTO private_messages (MESSAGE_SUBJECT, INBOX, SENDER_ID, USER_ID, TIME) VALUES(?,?,?,?,NOW())";
			$valArr = array($msgSubj, $msg, $senderId, $recId);
			$stat = $this->DBM->doSecuredQuery($sql, $valArr);
		
		}
		
		return $stat;
		
	}





	
	
		
	/*** Method for sending mail using templates ***/
	public function mailByTemplate($options=array()){
		
		///GET DOMAIN OR HOMEPAGE/////
		$siteDomain = $this->ENGINE->get_domain();
		$siteName = $this->getSiteName();
		
		$template = $this->ENGINE->get_assoc_arr($options, 'template');
		$to = $this->ENGINE->get_assoc_arr($options, 'to');
		$code = $this->ENGINE->get_assoc_arr($options, 'code');
		$decoyCode = $this->ENGINE->get_assoc_arr($options, 'decoyCode');
		$username = $this->ENGINE->get_assoc_arr($options, 'username');
		$attachments = $this->ENGINE->get_assoc_arr($options, 'attachments');
		$domainUrl = '<a href="'.$siteDomain.'">'.$siteDomain.'</a>';
		$pureTo = preg_replace("#(\:\:.+)#", "", $to);
		
		switch(strtolower($template)){
			
			case 'confirm-reg-email':

			$subject = 'Confirm your email for registration';
			 
			$message = 'Hello \n Thank you for choosing to register an account with us\nYour confirmation link is shown below and it\'s valid for 24hours\nPlease click on the following button to confirm your email and continue with your registration\n <a '.EMS_PH_PRE.'BLUE_BTN_1 href="'.$siteDomain.'/signup?email='.$to.'&code='.$code.'">CONFIRM YOUR EMAIL</a> \nThank you\n\n\n\n';
						
			$footer = 'NOTE: This email was sent to you because you are about to register an account with '.$domainUrl.'. If you did not initiate such registration request, please kindly ignore this message.\n\n\n Please do not reply to this email.';
			break;
			
			case 'confirm-account':
			
			$subject = 'Activate Your Account';
			
			$message = 'Hello '.$username.'\n Thank you for registering an account with us\nPlease click on the following button to activate your account and remove all restrictions\n<a '.EMS_PH_PRE.'SUCCESS_BTN href="'.$siteDomain.'/activate-account?username='.$username.'&code='.$code.'">ACTIVATE YOUR ACCOUNT</a> \nThank you\n\n\n\n';
					
			$footer = 'NOTE: This email was sent to you because you registered an account at '.$domainUrl.'. If you
					  did not initiate such registration, please kindly ignore this message.\n\n\n Please do not reply to this email.';
			break;	
			
			case 'confirm-login-unlock':
			
			$unlockLink = '<a '.EMS_PH_PRE.'SUCCESS_BTN href="'.$siteDomain.'/login?_ulp='.$decoyCode.'&_ulc='.$code.'&_ule='.$pureTo.'&_ulu='.$username.'">UNLOCK YOUR ACCOUNT</a>';
			
			$subject = "Security Alert";
											
			$message = 'Hello '.$username.'\n We detected some suspicious login attempts with your account at '.$domainUrl.'.\n\n <h2>DETAILS:</h2> <div '.EMS_PH_PRE.'INFO_BOX>IP: '.$this->ENGINE->get_ip().'\n\nUser Agent: '.$this->ENGINE->get_user_agent().'\n\nTime: '.date(' \o\n l, dS M, Y  \a\t h:iA').'</div>\n\nAlthough '.$siteName.' has stopped these attempts, we recommend you review your recently used devices. In the meantime, your account has been temporarily locked for protection.\n\n Please click the button below to unlock your account'.$unlockLink.' \n\n Best regards,\n'.$siteName.' Account Security Team.\n\n\n\n';																
			
			$footer = 'NOTE: This email was sent to you because you registered an account at '.$domainUrl.' using this email address. Please kindly ignore this message if otherwise.\n\n\n Please do not reply to this email.';										 														
													
			break;	
		}
		
					 
		return $this->sendMail(array('to'=>$to, 'subject'=>$subject, 'body'=>$message, 'footer'=>$footer));
		
										
	}






	
	
		
	/*** Method for sending mail ***/
	
		
	private function mailStyler($styles){
		
		return 'style="'.trim($styles).'"';
		
	}

	public function sendMail($options=array()){
		
		$to = $this->ENGINE->get_assoc_arr($options, 'to');
		$recipientName = $this->ENGINE->get_assoc_arr($options, 'recipientName');
		$from = $this->ENGINE->get_assoc_arr($options, 'from');
		$senderName = $this->ENGINE->get_assoc_arr($options, 'senderName');
		$headers = $this->ENGINE->get_assoc_arr($options, 'headers');
		$subject = $this->ENGINE->get_assoc_arr($options, 'subject');
		$attachments = $this->ENGINE->get_assoc_arr($options, 'attachments');
		$body = $this->ENGINE->get_assoc_arr($options, 'body');
		$footer = $this->ENGINE->get_assoc_arr($options, 'footer');
		$newsLetter = (bool)$this->ENGINE->get_assoc_arr($options, 'newsLetter');
		$dispatchStatus = false;
		
		///GET DOMAIN OR HOMEPAGE/////
		$siteDomain = $this->ENGINE->get_domain();
		$siteName = $this->getSiteName();
		$inMailDomain = preg_replace("#(https?\://)#isU", "", $siteDomain);
		
		
		//DECODE MAIL CSS STYLE PLACEHOLDERS
		$btnCmn = ' display:block; margin:10px; text-transform:uppercase; padding:10px; border-radius:4px; text-align:center; ';
		$boxCmn = ' display:block; margin:10px 0; padding:10px; font-size:15px; border-radius:3px; ';
		$phPrefix = EMS_PH_PRE; 
		$whiteColor = ' color:#fff; ';
		$success = '#5cb85c;';
		$warn = '#ec971f;';
		$danger = '#d9534f;';
		$info = '#5bc0de;';
		$successBtn = $successBox = 'background-color:'.$success.$whiteColor;
		$successBtn .= $btnCmn;
		$successBox .= $boxCmn;
		$warnBtn = $warnBox = 'background-color:'.$warn.$whiteColor;
		$warnBtn .= $btnCmn;
		$warnBox .= $boxCmn;
		$dangerBtn = $dangerBox = 'background-color:'.$danger.$whiteColor;
		$dangerBtn .= $btnCmn;
		$dangerBox .= $boxCmn;
		$infoBtn = $infoBox = 'background-color:'.$info.$whiteColor;
		$infoBtn .= $btnCmn;
		$infoBox .= $boxCmn;
		
		$matchArr = array('\n', $phPrefix.'BLUE_BTN_1', $phPrefix.'SUCCESS_BTN', $phPrefix.'DANGER_BTN', 
			$phPrefix.'WARN_BTN', $phPrefix.'RED_BOX_1', $phPrefix.'BLUE_BOX_1', $phPrefix.'PLAIN_BOX', $phPrefix.'INFO_BOX',
			$phPrefix.'WARN_BOX', $phPrefix.'DANGER_BOX',  $phPrefix.'SUCCESS_BOX', 
			$phPrefix.'RED', $phPrefix.'GREEN', $phPrefix.'BLUE'
		);
		
		$replaceArr = array('<br/>', $this->mailStyler($btnCmn.'background-color:#337ab7;'.$whiteColor), $this->mailStyler($successBtn), 
			$this->mailStyler($dangerBtn), $this->mailStyler($warnBtn), $this->mailStyler($boxCmn.'background-color:#8b0000;'.$whiteColor), 
			$this->mailStyler($boxCmn.'background-color:#0090ac;'.$whiteColor), $this->mailStyler($boxCmn.'border:1px solid #333;'), 
			$this->mailStyler($infoBox), $this->mailStyler($warnBox), $this->mailStyler($dangerBox), 
			$this->mailStyler($successBox), $this->mailStyler('color:#ff0000;'), $this->mailStyler('color:#00ff00;'), 
			$this->mailStyler('color:#0000ff;')
		);
		
		$antiPhishingCodePh = $phPrefix.'DISPATCH_SIGNATURE';
		$footer = str_replace($matchArr, $replaceArr, $footer);	
		$subject = str_replace($matchArr, $replaceArr, $subject).' - '.$siteName;	
		$body = str_replace($matchArr, $replaceArr, $body);
		

		$body = '<div style="line-height:1.5">
					<header style="text-align:center; background:#00b3ac; color:#fff; padding:6px; margin:0; border-top-left-radius:4px; border-top-right-radius:4px;">
						<h1>'.strtoupper($siteName).'</h1>
					</header>
					<div style="padding:15px 10px 5px 10px; background-color:#fff; font-size:16px; border-left: 1px solid #f1f1f1; border-right: 1px solid #f1f1f1;">
						'.$antiPhishingCodePh.$body.'
					</div>
					<footer style="display:block; text-align:center;background:#e8ece0;padding:10px 10px;font-size:0.8em;margin:0 auto;margin-bottom:10px; border-top:1px solid #c0c0c0;border-bottom-left-radius:4px;border-bottom-right-radius:4px;">
						'.$this->getCopyRight().'
						<br/>'.$footer.'
						'.($newsLetter? '<div>If you do not wish to receive newsletters and promotional emails from us anymore, kindly <a href="'.$siteDomain.'/dnd-mail-list/subsribe/'.$to.'" >Unsubscribe</a>. </div>' : '').'
					</footer>									
				</div>';
		
		$senderName? '' : ($senderName = $siteName);
		$from? '' : ($from = 'admin@'.$inMailDomain);
		
		//MAILING
		// Import PHPMailer classes into the global namespace
		// These must be at the top of your script, not inside a function
		/*
		use PHPMailer\PHPMailer\PHPMailer;
		use PHPMailer\PHPMailer\SMTP;
		use PHPMailer\PHPMailer\Exception;
		*/
		
		// Instantiation and passing `true` enables exceptions
		$mail = new PHPMailer\PHPMailer\PHPMailer(true);
		
		$debugOutputLevel = 0; //SMTP::DEBUG_SERVER;///ZERO TURNS OFF DEBUG OUPUT
		
		//Server settings
		$mail->SMTPDebug = $debugOutputLevel;  		// Enable verbose debug output
		$mail->isSMTP();                        	// Send using SMTP
		//$mail->Debugoutput = 'html';          	// Send using SMTP
		$mail->Host       = 'smtp.gmail.com';   	// Set the SMTP server to send through
		$mail->SMTPAuth   = true;               	// Enable SMTP authentication
		$mail->Username   = 'xpresors@gmail.com';  // SMTP username
		$mail->Password   = 'Adimabua02';          // SMTP password
		$mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;   // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS`(ssl) encouraged
		$mail->Port       = 465;                   // TCP port to connect to, use 587 for `PHPMailer::ENCRYPTION_STARTTLS`(tls), 465 for `PHPMailer::ENCRYPTION_SMTPS`(ssl)
		
		
		
		//Sender
		$mail->setFrom($from, $senderName);
		$mail->addReplyTo($from, 'DoNotReply');
		
		
		$recipientsArr = explode(',', $to);
		$totalRecipients = count($recipientsArr);
	 
		foreach($recipientsArr as $rec){
			
			$recAddressNamePairArr = explode('::', $rec);
			$to = $recAddressNamePairArr[0];
			$recName = isset($recAddressNamePairArr[1])? $recAddressNamePairArr[1] : '';
			$recipientName = ($totalRecipients == 1 && $recipientName)? $recipientName : $recName;
				
			//Recipient
			$mail->addAddress($to, $recipientName);
			/*
			$mail->addReplyTo('info@example.com', 'Information');
			$mail->addCC('cc@example.com');
			$mail->addBCC('bcc@example.com');
			*/
			
			// Attachments
			if($attachments){
				
				$tmpArr = explode(',', $attachments);
				$counter = 1;
				
				foreach($tmpArr as $attachment){
					
					$optionalName = 'Attachment-'.$counter++;
					$mail->addAttachment($tmpArr[0], $optionalName);
					
				}
				
			}
			
			/*
			//Adding string blob attachments
			$mysql_data = $mysql_row['blob_data'];
			$mail->addStringAttachment($mysql_data, 'db_data.db');
			$mail->addStringAttachment(file_get_contents($url), 'myfile.pdf');
			
			//embedding image in body
			$mail->addEmbeddedImage('path/to/image_file.jpg', 'image_cid');
			$mail->Body = '<img src="cid:image_cid"> Mail body in HTML';
			*/
			
			$recipientUser = $this->ACCOUNT->loadUser($to);
			$recipientAntiPhishingCode = $recipientUser->getAntiPhishingCode();
			
			$antiPhishingCodePhReplace = !$recipientAntiPhishingCode? '' : 
			'<p style="color: #8a6d3b; background-color: #fcf8e3; border-color: #faebcc; padding: 15px; margin: 10px 5px; border: 1px solid transparent; border-radius: 4px; border-left-width: 5px">
				<em>Dispatch Signature: '.$recipientAntiPhishingCode.'</em>
				<br/> Please verify that the above dispatch signature matches with what you have setup on your '.$siteName.' account before clicking any link in this email.
			</p>';
			
			$body = str_replace($antiPhishingCodePh, $antiPhishingCodePhReplace, $body);
			
			
			
			// Content
			$mail->isHTML(true);                                  
			$mail->Subject = $subject;
			$mail->Body = $body;
			$mail->AltBody = strip_tags($body);
		
			
			try {
				
				$dispatchStatus = $mail->send();
				if($debugOutputLevel && $dispatchStatus)
					echo 'Message has been sent';
				
			} catch (Exception $e) {
				
				echo "<div>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</div>";
					
				//ATTEMPT USING PHP mail()
				$headers = "From: ".($from)."\r\n";
				$headers .= "Reply-To: ".($from)."\r\n";
				$headers .= "Return-Path: ".($from)."\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/html;charset=UTF-8\r\n";
				$headers .= "X-Priority: 1\r\n";
				$headers .= "X-MSMail-Priority: High\r\n";
				$headers .= "Importance: High\r\n";
				$headers .= "X-Mailer: PHP".phpversion()."\r\n";
				$dispatchStatus = mail($to, $subject, $body, $headers);
				echo '<div>Message has been sent by PHP default mail() status: '.($dispatchStatus? 'success' : 'failed').'</div>';
			
			}    
			
			//Clear all addresses and attachments for the next iteration
			$mail->clearAddresses();
			$mail->clearAttachments();
		}
		
		return $dispatchStatus;
		
	}









	
	
		
	/*** Method for loading dynamic pages from database ***/
	public function loadDynamicContents($metaArr = array('type' => 'page', 'token' => '', 'parseFullHtml' => false)){
		
		//////////GET DATABASE CONNECTION//////
		global $GLOBAL_page_self_rel, $GLOBAL_siteName;	
		
		$title=$content=$theme=$alertUser=''; 
		$pageSelf = $GLOBAL_page_self_rel;
		$flagErr = false;
		$type = strtolower($this->ENGINE->get_assoc_arr($metaArr, 'type'));
		$token = $this->ENGINE->get_assoc_arr($metaArr, 'token');
		$parseFullHtml = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'parseFullHtml');
		
		$flagErr = (!$type || !$token);

		if($flagErr)
			$alertUser = '<span class="alert alert-danger">An unexpected error has occurred<br/> we are sorry about this!</span>';
		
		if($type == 'page'){

			if(!$flagErr){
				
				$page_slug = $token;
				
				$sql = "SELECT * FROM pages WHERE (TITLE=? OR TITLE_SLUG=?) LIMIT 1";
				$valArr = array($page_slug, $page_slug);
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
				$row = $this->DBM->fetchRow($stmt);
				$theme = str_ireplace(array('blue','dark cyan','red','lime'), array('bluex','dcyan','redx','limex'), $row["THEME"]);		
				$title = $row["TITLE"];		
				$content = $this->replaceDbPh($row["CONTENT"]);

			}

			!$theme? ($theme = 'dcyan') : '';

			list($rightWidget, $pageTopAds, $pageBottomAds, $leftWidgetClass) = $this->getWidget(ELITE_SID);
			
			$html = '<div class="single-base">			
						<div class="base-ctrl base-container">
							'.$pageTopAds.'														
							<div class="row">
								<div class="'.$leftWidgetClass.' base-borderx box-shadowx">																							
									<div class="panel panel-'.$theme.' img-responsive links ol ul">
										<h1 class="panel-head page-title">'.ucwords($title).'</h1>
										<div class="panel-body">'.													
											$alertUser.
											$content.'													
										</div>
									</div>
								</div>'.															
								$rightWidget.'										
							</div>'.										
							$pageBottomAds.'									
						</div>			
					</div>';

			if($parseFullHtml)
				$this->buildPageHtml(array("pageTitle"=>$title,
							"preBodyMetas"=>$this->getNavBreadcrumbs('<li><a href="'.$pageSelf.'">'.$title.'</a></li>'),
							"pageBody"=>$html	
				));
				
			return array('title' => $title, 'content' => $content, 'html' => $html);

		}
	}






	
	
		
	/*** Method for handling do not disturb(DND) email subscription requests ***/
	public function dndEmailList($action, $email){
		
		$alert = '';

		!$action? ($action = 'sub') : '';
		
		$redInlineStyle = 'style="color:#ff0000;"';
		$greenInlineStyle = 'style="color:#00ff00;"';
		$boxStyle = 'style="text-align:center;padding:10px;font-size:20px;"';
		$header = '<h2 style="text-align:center;">DO NOT DISTURB MAIL LIST</h2>';
		$infos = '<div style="text-align:center;padding:15px;">
					By default, you are automatically subscribed to receive our newsletters when you register an account with us. 
					<br/>If however, you no longer wish to receive newsletters from us, kindly subscribe to our DND(Do Not Disturb) list and 
					we will stop sending you newsletters and promotional emails.
				</div>';
		$unsubLink = '<a href="'.($K = '/dnd-mail-list/').'unsubscribe/'.$email.'">Unsubscribe</a>';
		$subLink = '<a href="'.$K.'subscribe/'.$email.'">Subscribe</a>';
		$action = strtolower($action? $action : 'sub');
		$subscribe = ($action == 'subscribe');
		$unSubscribe = ($action == 'unsubscribe');
		
		if($subscribe){
		
			if($this->emailExist($email)){
		
				if($subscribe && !$this->emailExist($email, 'dnd_mail_lists')){
		
					$sql = "INSERT INTO dnd_mail_lists (EMAIL, IP) VALUES(?,?)";
					$valArr = array($email, $this->ENGINE->get_ip());
					$alert = $this->DBM->doSecuredQuery($sql, $valArr)? 'You have successfully <b '.$greenInlineStyle.'>subscribed</b> to our DND list '.$unsubLink : 'Ooops! something went wrong; Please try again';
		
				}else
					$alert = '<span '.$redInlineStyle.'>You are already <b '.$greenInlineStyle.'>subscribed</b> to our DND list '.$unsubLink.'</span>';	
		
			}else
				$alert = '<span '.$redInlineStyle.'>We are sorry! for now, we only accept registered member\'s emails in our DND list</span>';	
		
		}elseif($unSubscribe){
		
			$sql = "DELETE FROM dnd_mail_lists WHERE EMAIL=? LIMIT 1";
			$valArr = array($email);
		
			if($stmt = $this->DBM->doSecuredQuery($sql, $valArr))
				$alert = $this->DBM->getRowCount()? 'You have successfully <b '.$redInlineStyle.'>unsubscribed</b> from our DND list '.$subLink : '<span '.$redInlineStyle.'>Sorry you are <b '.$redInlineStyle.'>not subscribed</b> to our DND list '.$subLink.'</span>';
		
			else
				$alert = 'Ooops! something went wrong; Please try again';
		
		}
			
		$alert = $alert? '<div '.$boxStyle.'>'.$header.$infos.$alert.'</div>' : $alert;
		
		return $alert;
		
	}






	


	
	
		
	/*** Method for dispatching newsletters from database ***/
	public function newsLetterDispatcher($dispatchId='', $receiverId=''){
		
		//////////GET DATABASE CONNECTION//////
		global $GLOBAL_siteName;	
		$limit = $this->DBM->getMaxRowPerSelect();
		$newsTable = 'news_letters';
		
		for($index = 0; ; $index += $limit){
			
			$cond1 = 'DISPATCH_STATUS = 1';
			$cond2 = '';
			$dispatchTest = false;
			$valArr1=$valArr2=array();
			
			if($dispatchId && $receiverId){
				
				$cond1 = 'ID = ?';
				$valArr1[] = $dispatchId;
				$cond2 = 'WHERE ID = ?';
				$valArr2[] = $receiverId;
				$dispatchTest = true;
				
			}
			
			
			$sql = "SELECT ID, TITLE, CONTENT from ".$newsTable." WHERE ".$cond1." LIMIT ".$index.",".$limit;
			$valArr = $valArr1;
			$stmtx = $this->DBM->doSecuredQuery($sql, $valArr, true);
			
			/////IMPORTANT INFINITE LOOP CONTROL ////
			if(!$this->DBM->getSelectCount())
				break;
						
			while($rowx = $this->DBM->fetchRow($stmtx)){
				
				$newsId = $rowx["ID"];
				$newsTitle = $rowx["TITLE"];
				$newsContent = $rowx["CONTENT"];
			
				for($index2 = 0; ; $index2 += $limit){
					
					$sql = "SELECT ID, USERNAME, EMAIL, FIRST_NAME, LAST_NAME, (SELECT COUNT(*) FROM dnd_mail_lists ml WHERE users.EMAIL = ml.EMAIL) DND from users ".$cond2." LIMIT ".$index2.",".$limit;
					$valArr = $valArr2;
					$stmt = $this->DBM->doSecuredQuery($sql, $valArr, true);
						
					/////IMPORTANT INFINITE LOOP CONTROL ////
					if(!$this->DBM->getSelectCount())
						break;
						
					while($row = $this->DBM->fetchRow($stmt)){
						
						$userId = $row["ID"];
						$username = $row["USERNAME"];
						$to = $row["EMAIL"];
						$fn = $row["FIRST_NAME"];
						$ln = $row["LAST_NAME"];
						$dnd = $row["DND"];
						
						if($dnd && !$dispatchTest)
							continue;
						
						$metaArr1 = array(DB_PH_FN, DB_PH_LN, DB_PH_UN, DB_PH_SITE_NAME, DB_PH_SITE_SLOGAN,);
						$metaArr2 = array($fn, $ln, $username, $GLOBAL_siteName, SITE_SLOGAN);
						$newsTitle2Mail = str_ireplace($metaArr1, $metaArr2, $newsTitle);
						$newsContent2Mail = str_ireplace($metaArr1, $metaArr2, $newsContent);
						
						$this->sendMail(array('to'=>$to.'::'.$fn, 'subject'=>$newsTitle2Mail, 'body'=>$newsContent2Mail, 'newsLetter'=>true));
						
						if($dispatchTest)
							return;
						
					}
					
				}
				
				//MARK AS DISPATCHED 
				$sql = "UPDATE ".$newsTable." SET DISPATCH_STATUS = 2 WHERE ID = ? LIMIT 1";
				$valArr = array($newsId);
				$stmtx = $this->DBM->doSecuredQuery($sql, $valArr);
				
			}
			
		}
		
	}





	


	

	
		
	/*** Method for generating date select list ***/
	public function generateDateSelectField($refDate='', $startYearDiff=0){
		
		$yearToday = $this->ENGINE->get_date_safe("", 'Y') - $startYearDiff;
		$sday = $this->ENGINE->get_date_safe($refDate, 'j');
		$smonth = $this->ENGINE->get_date_safe($refDate, 'F');
		$syear = $this->ENGINE->get_date_safe($refDate, 'Y');		
		
		$days=$months=$years=$months_arr=''; 
		
		$months_arr = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
		
		
		for($idx=1; $idx <= 31; $idx++){
			$days .= '<option '.(($sday == $idx )? 'selected' : '').'>'.$idx.'</option>';
		}
		
		$days = '<select id="dob" class="field" name="date_day" ><option  value="">--select day--</option>'.$days.'</select>';
		
		
		for($idx=0; $idx < count($months_arr); $idx++){
			
			$months .= '<option '.(($smonth == $months_arr[$idx])? 'selected' : '').'>'.$months_arr[$idx].'</option>';
			
		}
		
		$months = '<select class="field" name="date_month" ><option value="">--select month--</option>'.$months.'</select>';
		
		
		for($idx=$yearToday; $idx >= ($yearToday - 120) ; $idx--){
			
			$years .= '<option '.(($syear == $idx)? 'selected' : '').'>'.$idx.'</option>';
			
		}
		
		$years = '<select class="field" name="date_year" ><option value="">--select year--</option>'.$years.'</select>';	
		
		return '<div class="">'.$days.$months.$years.'</div>';
		
		
	}




	

	


	

	
		
	/*** Method for converting date string to database date format ***/
	public function ConvertToDatabaseDate($dDay, $dMonth, $dYear){
		
		$minAge = SITE_ACCESS_MIN_AGE;
		$todY = $this->ENGINE->get_date_safe('', 'Y');
		
		if(!$dDay)
			$dDay = '01';

		elseif(mb_strlen($dDay) == 1)
			$dDay = '0'.$dDay;

		if(!$dMonth)
			$dMonth = 'January';

		if(in_array($dYear, array($todY, '')) || ($todY - $dYear < $minAge))
			$dYear = $todY - $minAge;

		$dbDate = $this->ENGINE->get_date_safe($dDay.' '.$dMonth.' '.$dYear, 'Y-m-d');
		
		return $dbDate;
		
	}

	

	

	

	
		
	/*** Method for customizing date display style or format ***/
	public function customDateDisplay($date, $username, $viewAs = false){
		
		$date = (strtolower($this->SESS->getUsername()) == strtolower($username) && !$viewAs)? $this->ENGINE->get_date_safe($date, 'jS  \o\f F Y') : $this->ENGINE->get_date_safe($date, 'jS  \o\f F');	
		
		return $date;
		
	}



	 
	 

	 

	 

	
		
	/*** Method for fetching navigation breadcrumbs ***/
	public function getNavBreadcrumbs($subNav='', $subNavMob=''){
		
		$siteName = $this->getSiteName();
		$cls1 = 'nav-base no-pad no-mg';
		$cls2 = 'nav breadcrumbs bordered caret-pad';
		$siteSlogan = SITE_SLOGAN;
		
		return 	'<div class="container">'.
					($subNavMob? 
					'<header class="mob-platform-dpn">
						<nav class="'.$cls1.'">
							<ul class="'.$cls2.'">
								<li><a href="/" title="'.$siteSlogan.'">'.$siteName.'</a></li>								
								'.$subNavMob.'
							</ul>
						</nav>
					</header>' : '').
					'<header class="'.($subNavMob? '' : 'mob-platform-dpn').' dsk-platform-dpn">
						<nav class="'.$cls1.'">
							<ul class="'.$cls2.'">
								<li><a href="/" title="'.$siteSlogan.'">'.$siteName.'</a></li>								
								'.$subNav.'
							</ul>
						</nav>
					</header>
				</div>
				<span id="go_up"></span>
				'.$this->getFloatingPageSkipBtn().'
				<div class="nav-base no-pad"><ul class="nav nav-pills justified-center"><li><a class="links topagedown" href="#go_down">Go Down &dArr;</a></li></ul></div>';
		
	}
	 
	 
	 
	






	
		
	/*** Method for fetching banned users ***/
	public function loadBannedUsers($pageUrl){
		
		global $FORUM;
		
		$spamBansLoaded=$modBansLoaded='';	
			
		
		////PDO QUERY//////
		$sql = "SELECT COUNT(*) FROM spam_controls WHERE BAN_STATUS=1 ";
		$valArr = array();
		$totSpamBans = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
		////PDO QUERY//////
		$sql = "SELECT COUNT(*) FROM moderation_bans WHERE BAN_STATUS=1 ";
		$valArr = array();
		$totModBans = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
		
		////FOR SPAM BANS
		$totalRecords = $totSpamBans;

		/**********CREATE THE PAGINATION*******/
		$paginationArr = $this->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>'page_ids','jmpKey'=>'jump_page_s','hash'=>'sbt'));
		$pagination_sb = $paginationArr["pagination"];
		$total_page_sb = $paginationArr["totalPage"];
		$perPage = $paginationArr["perPage"];
		$startIndex = $paginationArr["startIndex"];
		$page_idsb = $paginationArr["pageId"];
		$countDownMetaArr = array("hideIgnore" => true, "timerBasic" => true);

		///////////////END OF PAGINATION/////////////
		////PDO QUERY//////
		$sql = "SELECT * FROM spam_controls WHERE BAN_STATUS=1 LIMIT ".$startIndex.",".$perPage;
		$valArr = array();
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		
		while($row = $this->DBM->fetchRow($stmt)){

			$bLen = $row["BAN_DURATION"];
			$expires = $this->getCountDownClockBox($bLen, $countDownMetaArr);
			$tid = $row["TOPIC_ID"];
			$pid = $row["POST_ID"];
			$postNum = $FORUM->getPostNumber($pid, $tid);
			$postPageNumber = $FORUM->getPostPageNumber($postNum);
			$thread = $this->ENGINE->sanitize_slug($this->getThreadSlug($tid), array('ret'=>'url', 'urlText'=>$this->topicIdToggle($tid), 'slugSanitized'=>true, 'postUrl'=>$postPageNumber, 'urlHash'=>$postNum));

			$spamBansLoaded .= '<tr>
							<td>'.$this->ACCOUNT->sanitizeUserSlug($this->ACCOUNT->memberIdToggle($row["USER_ID"]), array('anchor'=>true, 'gender'=>true, 'cls'=>' sv-txt-case')).'</td>
							<td>'.$expires.'</td><td>'.$thread.'</td><td>'.$row["MESSAGE"].'</td>
						</tr>';
						
		}
				
		$spamBansLoaded = !$spamBansLoaded? '<span class="alert alert-danger">No Spam Bans</span>' : '
						<div class="table-responsive"><table class="table-classic">
							<caption class="">SPAM BANS('.$totSpamBans.')<caption>
							<tr><th>BANNED USER</th><th>EXPIRES IN</th><th>THREAD</th><th>SPAM CONTENT</th></tr>
							'.$spamBansLoaded.'
						</table></div>'.$pagination_sb;	
						
		////FOR MOD BANS
		$totalRecords = $totModBans;

		/**********CREATE THE PAGINATION*******/
		$paginationArr = $this->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>'page_ids','jmpKey'=>'jump_page_s','hash'=>'sbt'));
		$pagination_mb = $paginationArr["pagination"];
		$total_page_mb = $paginationArr["totalPage"];
		$perPage = $paginationArr["perPage"];
		$startIndex = $paginationArr["startIndex"];
		$page_idmb = $paginationArr["pageId"];
		
		///////////////END OF PAGINATION/////////////
		////PDO QUERY//////
		$sql = "SELECT * FROM moderation_bans WHERE BAN_STATUS=1 LIMIT ".$startIndex.",".$perPage;
		$valArr = array();
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
		
		while($row = $this->DBM->fetchRow($stmt)){

			$bLen = $row["BAN_DURATION"];		
			$expires = $this->getCountDownClockBox($bLen, $countDownMetaArr);

			$modBansLoaded .= '<tr>
							<td>'.$this->ACCOUNT->sanitizeUserSlug($this->ACCOUNT->memberIdToggle($row["USER_ID"]), ($metaArr=array('anchor'=>true, 'gender'=>true))).'</td>
							<td>'.$this->ACCOUNT->sanitizeUserSlug($this->ACCOUNT->memberIdToggle($row["TREATED_BY_USER_ID"]), $metaArr).'</td>
							<td>'.$expires.'</td><td>'.$row["REASONS"].'</td><td>'.$this->ENGINE->time_ago($row["TIME"]).'</td>
						</tr>';
						
		}
				
		$modBansLoaded = !$modBansLoaded? '<span class="alert alert-danger">No Mod Bans</span>' : '
						<div class="table-responsive"><table class="table-classic">
							<caption class="">MOD BANS('.$totModBans.')<caption>
							<tr><th>BANNED USER</th><th>MOD</th><th>EXPIRES IN</th><th>MOD REASON</th><th>TIME</th></tr>
							'.$modBansLoaded.'
						</table></div>'.$pagination_mb;

		
		return array('<div class="hr-dividers no-bg">'.$modBansLoaded.$spamBansLoaded.'</div>');
		
	}


 
 


	/////FUNCTION TO CONVERT COMMA SEPERATED IDs A TO NAME
	
	
	
	
		
	/*** Method for converting comma separated id values to equivalent name string ***/
	public function idCsvToNameString($ids, $t='sid', $nSep='', $opt_arr=['']){
		
		$acc=$nPrev='';
		
		!$ids? ($ids = '') : '';

		$t = strtolower($t);
		$sid = 'sid';
		$cid = 'cid';
		$tid = 'tid';
		$uid = 'uid';
		$xurl = 'xurl';
		$nSep = $nSep? $nSep : ', ';
		$id_arr = explode(",", $ids);
		$tot = count($id_arr);
		$i = 1;
		
		foreach($id_arr as $id){
			
			if(!$id)
				continue;
			
			if($t == $sid){
				
				$n = $this->sectionIdToggle($id);
				$acc .= (($i > 1)? $nSep : '').$this->ENGINE->sanitize_slug($n, array('ret'=>'url', 'ignoreHref'=>(HOMEPAGE_SID == $id)));
			
			}elseif($t == $cid){
			
				$n = $this->categoryIdToggle($id);
				$acc .= (($i > 1)? $nSep : '').$this->ENGINE->sanitize_slug($n, array('ret'=>'url'));
			
			}elseif($t == $tid){
			
				$n = $this->topicIdToggle($id);
				$acc .= (($i > 1)? $nSep : '').$this->ENGINE->sanitize_slug($this->getThreadSlug($id), array('ret'=>'url', 'urlText'=>$n, 'slugSanitized'=>true));
			
			}elseif($t == $uid){	
					
				$n = $this->ACCOUNT->memberIdToggle($id);
				$nLC = strtolower($n);
				$nSep = '<b class="'.$nPrev.'-unfld">, </b>';
				$acc .= (($i > 1)? $nSep : '').$this->ACCOUNT->sanitizeUserSlug($n, array('anchor'=>true, 'gender'=>(isset($opt_arr["gnd"])? ($cls = $nLC.'-unfld') : ''), 'cls'=>$cls));
			
				$nPrev = $nLC;
			
			}elseif($t == $xurl){
			
				$n = $id;
				$acc .= (($i > 1)? $nSep : '').$this->ENGINE->add_http_protocol($n);
			
			}
			
			$i++;
			
		}
			
		//$acc = implode($nSep='', $acc);	
		return $acc;
			
	}


	
	
	
	
	
	
		
	/*** Method for fetching extended content view link ***/
	public function getExtendedViewLink($url, $urlText=''){
		
		return '&nbsp;&nbsp;<a role="button" href="/'.$url.'" class="btn btn-xs btn-info">'.($urlText? $urlText : 'view all').'</a>';
		
	} 



	
	
	
	
		
	/*** Method for fetching site administrators ***/
	public function getAdmins($returnType="username", $metaArr=array()){
		
		$allAdmins=$localAdminIdsList=$table=$tableSubQry=$paginationAdmin="";
		$adminsEmailsArr=$adminsUsernamesArr=$adminsIdsArr=array();
		
		$returnType = trim(strtolower($returnType));
		$retArr = isset($metaArr[$K="retArr"])? $metaArr[$K] : false;
		$pageUrl = isset($metaArr[$K="url"])? $metaArr[$K] : '';
		
		/////GET THE ADMINS//////
		$adminsSubQry = "IS_ADMIN=1";
		
		if($returnType == "subqry")
			return $adminsSubQry;
		
		
		if($getTable = ($returnType == "table")){
			
			///PDO QUERY/////

			$sql =  "SELECT COUNT(*) FROM users WHERE (".$adminsSubQry.") ";
			$valArr = array();
			$totalRecords = $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();

			/**********CREATE THE PAGINATION*************/
			$paginationArr = $this->paginationHandler(array('totalRec'=>$totalRecords,'url'=>$pageUrl,'pageKey'=>'page_id_a','jmpKey'=>'jump_page_admin','perPage'=>30,'hash'=>'s-admins'));
			$paginationAdmin = $paginationArr["pagination"];
			$totalPage = $paginationArr["totalPage"];
			$perPage = $paginationArr["perPage"];
			$startIndex = $paginationArr["startIndex"];
			$pageId = $paginationArr["pageId"];

			$tableSubQry = " ORDER BY USERNAME LIMIT ".$startIndex.",".$perPage;

		}
		/////////PDO QUERY//////
		
		$sql =  "SELECT ID, USERNAME, EMAIL, CONCAT_WS(' ', FIRST_NAME, LAST_NAME) AS FULL_NAME FROM users WHERE (".$adminsSubQry.")".$tableSubQry;
		$valArr = array();
		$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
			
		while($row = $this->DBM->fetchRow($stmt)){
		
			$adminUsername = $row["USERNAME"];
			$adminId = $row["ID"];
			$adminEmail = $row["EMAIL"];
			$adminFullName = $row["FULL_NAME"];
			$adminsUsernamesArr[] = $adminUsername;
			$adminsEmailsArr[] = $adminEmail;
			$adminsIdsArr[] = $adminId;
						
			///GET SLUG N GENDER///
			$adminSlugGnd = $this->ACCOUNT->sanitizeUserSlug($adminUsername, array('anchor'=>true, 'gender'=>true));						
			$allAdmins.= $adminSlugGnd.', ';
			
			$table .= '<tr>
							<td>'.$adminId.'</td>
							<td>'.$adminSlugGnd.$this->ACCOUNT->getUserSeal($adminUsername).'<br/><a title="send private message" href="/pm/'.$adminUsername.'" role="button" class="btn btn-success">PM</a></td>
							<td>'.$adminFullName.'</td>
							<td>'.$adminEmail.'<br/><a title="send Email" href="mailto:'.$adminEmail.'" role="button" class="btn btn-primary">E-mail</a></td>
						</tr>';
			
			
		}
		
		
		if($table)
			$table = '<div class="table-responsive">
						<table class="table-classic">
							<caption class="bg-red">SITE ADMINISTRATORS</caption>
							<th>ID</th><th>USERNAME</th><th>NAME</th><th>EMAIL</th>
							'.$table.'
						</table>
					</div>';
		
		$allAdmins = "[ Admins: ".mb_substr($allAdmins, 0, -2)." ]";
			
		if(!isset($adminUsername)){
			
			$table = '<span class="alert alert-warning">No Administrators Yet</span>';
			
		}
		
		$adminsIds = $retArr? $adminsIdsArr : implode(',', $adminsIdsArr);
		$adminsUsernames = $retArr? $adminsUsernamesArr : implode(',', $adminsUsernamesArr);
		$adminsEmails = $retArr? $adminsEmailsArr : implode(',', $adminsEmailsArr);

		if($returnType == "array-all")
			return array($adminsEmails, $adminsIds, $adminsUsernames);
		
		if($returnType == "email")
			return $adminsEmails;
		
		elseif($returnType == "username")
			return $adminsUsernames;
		
		elseif($returnType == "id")
			return $adminsIds;
		
		elseif($getTable)
			return array($table, $paginationAdmin);
		
		else
			return $allAdmins;
			
	}




	
	
	
	
	
		
	/*** Method for fetching sections/categories moderated by a user ***/
	public function moderatedSectionCategoryHandler($metaArr){
		
		global $FORUM;
		
		$moderatesIn=$moderators=$cnd="";
		$modsUsernamesArr=$modsEmailsArr=$modsIdsArr=array();
		
		$user = $this->ENGINE->get_assoc_arr($metaArr, 'uid');
		$level = $this->ENGINE->get_assoc_arr($metaArr, 'level');
		$scId = $this->ENGINE->get_assoc_arr($metaArr, 'scId');
		$cid = ($level == 1)? $FORUM->getSectionField($scId) : '';//FIND SUPER MODERATORS OF LEVEL 1 (SECTION) MODS			
		$action = strtolower($this->ENGINE->get_assoc_arr($metaArr, 'action'));			
		$updatePrivilege = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'updatePrivilege');			
		$retType = strtolower($this->ENGINE->get_assoc_arr($metaArr, 'retType'));				
		$i = $this->ENGINE->get_assoc_arr($metaArr, 'i');
		$i = $i? $i : 0;
		$n = $this->ENGINE->get_assoc_arr($metaArr, 'n');
		$n = $n? $n : 10;
		$sep = $this->ENGINE->get_assoc_arr($metaArr, 'sep');
		$sep = $sep? $sep : ' , ';
		//ADD ADMINS TO LIST OF MODS TO RECEIVE NOTIFICATION	
		$incAdmins = $this->ENGINE->is_assoc_key_set($metaArr, $K='incAdmins')? (bool)$this->ENGINE->get_assoc_arr($metaArr, $K) : true;
		##EXTRA "GET ACTION" SETUP PARAMS
		$vcard = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'vcard');
		$vcardMin = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'vcardMin');
		$modsTable = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'modsTable');
		$U = $this->ACCOUNT->loadUser($user);
		$uid = $U->getUserId();
		
		//PDO QUERY//	
		$tableNCond =  ' FROM moderators md WHERE USER_ID = ?';
		
		if($action == 'scm-qry'){
		
				$scmQry = 'SELECT SC_ID FROM moderators WHERE USER_ID=? AND LEVEL=1 UNION			
				SELECT sc.ID FROM moderators md JOIN sections sc ON md.SC_ID=sc.CATEG_ID WHERE USER_ID=? AND LEVEL=2';
				return $scmQry;	
					
		}elseif($action == 'cm-qry'){
		
			$cmQry = 'SELECT SC_ID FROM moderators WHERE USER_ID=? AND LEVEL=2';			
			return $cmQry;	
					
		}elseif($uid){	
			
			$valArr = array($uid);
		
			if($scId && $level){
		
				$valArr[] = $level;
				$valArr[] = $scId;
		
				if($action == 'add'){
		
					$sql = "INSERT INTO moderators (USER_ID, LEVEL, SC_ID) VALUES(?,?,?)";
					
					return ($this->DBM->doSecuredQuery($sql, $valArr) && $this->ACCOUNT->updatePrivilege($uid));
					
				}elseif($action == 'del'){
		
					$sql = "DELETE FROM moderators WHERE (USER_ID=? AND LEVEL=? AND SC_ID=?) LIMIT 1";
					$return = $this->DBM->doSecuredQuery($sql, $valArr);

					/****VERIFY TO MAKE SURE THAT THE USER IS NOT MODERATING IN OTHER CATEGORIES
						OR SECTIONS B4 U RESET HIS PRIVILEGE TO MEMBER***/
					$this->moderatedSectionCategoryHandler(array('uid'=>$uid,'action'=>'isMod'))? '' : $this->ACCOUNT->updatePrivilege($uid, '');
					return $return;
					
				}elseif($action == 'check' || $action == 'checkboth'){	
					
					$chkBoth = ($cid && $action == 'checkboth')? true : false;
					$chkBoth? ($valArr[] = $cid) : '';
					$sql = "SELECT ID FROM moderators WHERE (USER_ID=? AND ((LEVEL=? AND SC_ID=?) ".($chkBoth? "OR (LEVEL=2 AND SC_ID=?)" : "").")) LIMIT 1";
					
					return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
					
				}
				
			}elseif($action == 'ismod'){
		
				$valArr = array($uid);
				$level? ($valArr[] = $level) : '';
				$sql = "SELECT COUNT(*) AS T FROM moderators WHERE USER_ID=? ".($level? "AND LEVEL=?" : "");
				
				return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
					
			}elseif($action == 'scm-names'){
						
				$scQry = 'SELECT GROUP_CONCAT(SC_ID) FROM moderators tmp WHERE md.USER_ID=tmp.USER_ID AND LEVEL=';
				$sidsQry = '('.$scQry.'1)';
				$cidsQry = '('.$scQry.'2)';
				$sql = "SELECT ".$sidsQry." AS SIDS_MODERATED, ".$cidsQry." AS CIDS_MODERATED ".$tableNCond;
				$valArr = array($uid);
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
				$row = $this->DBM->fetchRow($stmt);	
				
				if(!empty($row)){
					
					$sids = $row["SIDS_MODERATED"];
					$cids = $row["CIDS_MODERATED"];							
					$moderatesIn .= $this->idCsvToNameString($cids, $t='cid', $sep);
					$moderatesIn .= ($moderatesIn? $sep : '').$this->idCsvToNameString($sids, $t='sid', $sep);
					
				}
				
				return $moderatesIn;
		
			}
			
		}else{
		
			##GET/COUNT MODERATORS
			/////////PDO QUERY//////
			$valArr=array();
			$cnd = $scId? "SC_ID=?" : "";
			$cnd .= $level? ($cnd? " AND " : "")."LEVEL=?" : "";
			$superModsCnd = ($scId && $cid && $cnd)? " OR (SC_ID=? AND LEVEL=2)" : "";
			$cnd = $cnd? " WHERE ((".$cnd.")".$superModsCnd.")" : "";
			$scId? ($valArr[] = $scId) : '';
			$level? ($valArr[] = $level) : '';
			$superModsCnd? ($valArr[] = $cid) : '';
			
			if($action == 'count'){	
									
				$sql =  "SELECT COUNT(DISTINCT USER_ID) FROM moderators ".$cnd;
					
				return $this->DBM->doSecuredQuery($sql, $valArr)->fetchColumn();
				
			}elseif($action == 'get'){
							
				$sql =  "SELECT u.ID,USERNAME, EMAIL, CONCAT_WS(' ', FIRST_NAME, LAST_NAME) AS NAME,m.TIME FROM moderators m 
				JOIN users u ON m.USER_ID=u.ID ".$cnd." GROUP BY USER_ID ORDER BY IS_ADMIN DESC, LEVEL DESC, m.TIME LIMIT ".$i.",".$n;
				$stmt = $this->DBM->doSecuredQuery($sql, $valArr);
				
				while($row = $this->DBM->fetchRow($stmt)){
					
					$name = $row["NAME"];
					$modId = $row["ID"];
					$mod = $this->ENGINE->title_case($row["USERNAME"]);
					$modEmail = $row["EMAIL"];
					$time = $row["TIME"];
					$modsIdsArr[] = $modId;
					$modsUsernamesArr[] = $mod;				
					$modsEmailsArr[] = $modEmail;

					$modSlugGnd = $this->ACCOUNT->sanitizeUserSlug($mod, array('anchor'=>true, 'gender'=>true));
													
					if($vcard || $vcardMin)
						$moderators .= $this->ACCOUNT->getUserVCard($mod, array(($vcardMin? 'minVer' : 'time') => ($vcardMin? true : $time)));
		
					elseif($modsTable){
		
						$seal = $this->ACCOUNT->getUserSeal($mod);
						$scmNames = $this->moderatedSectionCategoryHandler(array('uid'=>$mod,'action'=>'scm-names','sep'=>$sep));
						$moderators .= '<tr>
										<td>'.$modSlugGnd.$seal.'<br/><a title="send private message" href="/pm/'.$mod.'" role="button" class="btn btn-success">PM</a>
										</td>
										<td>'.$name.'</td>
										<td>'.$modEmail.'<br/><a title="send Email" href="mailto:'.$modEmail.'" role="button" class="btn btn-primary">E-mail</a>
										</td>
										<td>'.$scmNames.'</td>
									</tr>';
		
					}else{	
						
						$moderators .= $modSlugGnd.', ';
		
					}
					
				}
				
				$slug = (($level == 1)? '' : 'super-').'moderators/'.$scId;
				$term = (($level == 1)? '' : 'super ').'moderators';
				
				if($vcard);
		
				elseif($modsTable){
		
					$moderators = $moderators? '
									<div class="table-responsive" >									
										<table class="table-basic">
											<caption class="bg-gray">SITE MODERATORS</caption>
											<tr><th>USERNAME</th><th>NAME</th><th>EMAIL</th><th>MOD SECTIONS</th>
											</tr>'.$moderators.'
										</table>
									</div>'
									: '<span class="alert alert-danger">sorry there are no '.$term.' yet</span>';
		
				}else
					$moderators = '[ '.ucwords($term).': <span class="font-default hash-focus" id="smods">'.trim($moderators, ', ').'</span>  '.(($scId && $moderators)? '<a role="button" class="btn btn-xs btn-info" href="/activities/'.$slug.'">see all</a>' : '').']';
				
				##EMAILS AND IDs ARE USED FOR NOTIFICATION INCLUDE ADMINS TO THE LIST IF REQUESTED
				if($incAdmins){
		
					list($adminsEmailsArr, $adminsIdsArr) = $this->getAdmins("array-all", array('retArr' => true));
					$modsEmailsArr = array_unique(array_merge($modsEmailsArr, $adminsEmailsArr));
					$modsIdsArr = array_unique(array_merge($modsIdsArr, $adminsIdsArr));
		
				}
				
				##CONVERT TO COMMA SEPERATED STRINGS
				$modsIds = implode(',', $modsIdsArr);
				$modsUsernames = implode(',', $modsUsernamesArr);
				$modsEmails = implode(',', $modsEmailsArr);
				
				##RETURN MODS PARAMS FOR NOTIFICATION
				if($retType == "array-all")
					return array($modsEmails, $modsIds, $modsUsernames);
		
				elseif($retType == "email")
					return $modsEmails;
		
				elseif($retType == "username")
					return $modsUsernames;
		
				elseif($retType == "id")
					return $modsIds;
		
				else
					return $moderators;
		
			}
		}
	}






	
	
	
		
	/*** Method for fetching popularity sub query ***/
	public function getPopularitySubQuery($sectionTableAlias='s'){
		
		$s = $sectionTableAlias;
		$homepage2EliteRatio = 4; //elite section popularity is assumed to be that of homepage divided by this ratio
		
		return (', 
			CEIL(
					(SELECT COUNT(*) FROM sections sc JOIN topics tp ON sc.ID=tp.SECTION_ID JOIN topic_views tvs ON tp.ID=tvs.TOPIC_ID WHERE 
						IF('.$s.'.ID='.GEN_SID.', sc.CATEG_ID='.GEN_CID.', 
							IF('.$s.'.ID='.ENT_SID.', sc.CATEG_ID='.ENT_CID.', 
								IF('.$s.'.ID='.SCI_TECH_SID.', sc.CATEG_ID='.SCI_TECH_CID.',
									IF('.$s.'.ID='.HOMEPAGE_SID.', 1=1, 
										IF('.$s.'.ID='.ELITE_SID.', 1=1, sc.ID='.$s.'.ID)
									)
								)
							)
						)
					) / IF('.$s.'.ID='.ELITE_SID.', '.$homepage2EliteRatio.', 1)
					
			) AS POPULARITY '
		);
		
	}


	

	

	
	
	
		
	/*** Method for fetching database tables for admin operations using a decoy key index ***/
	public function getDatabaseTableFromDecoy($keyIndex){
		
		return (array(
			'cat' => 'categories', 'scat' => 'sections', 'user' => 'users', 'pop' => 'pop_outs', 'cron' => 'daemons',
			'badge' => 'badges', 'auto-pilot' => 'auto_pilots', 'uibg' => 'ui_bgs', 'emot' => 'emoticons', 'ctry' => 'countries'
		)[$keyIndex]);

	}







	

	
		
	/*** Method for fetching live edit and delete admin control buttons ***/
	public function getLiveTableAdminControls($tableDecoy, $id, $dataUrl, $delunLockKey){
		
		global $FA_lock, $FA_unlock;
		
		$dataId = ' data-id="'.$id.'" ';
		$deleteEnabled = (isset($_SESSION[$delunLockKey]) && $_SESSION[$delunLockKey] == $id)? ' data-toggle="smartToggler" ' : '';
		
		return '<td>
					<div class="align-c">
						<div>'.($deleteEnabled? $FA_unlock : $FA_lock).'</div>
						<div class="modal-drop text-warning hide">
							<h3>ARE YOU SURE?</h3>
							<button class="btn btn-danger live-delete" data-table="'.$tableDecoy.'"  '.$dataUrl.$dataId.' >delete</button>
							<br/><br/><button class="btn close-toggle" >CLOSE</button>
						</div>
						<button class="btn btn-danger" '.$deleteEnabled.' data-target-prev="true">delete</button>						
						<br/><br/>
						<div class="modal-drop text-warning hide">
							<h3>UNLOCK</h3>
							<button class="btn btn-warning live-unlock" data-action="'.$tableDecoy.'-del"  '.$dataUrl.$dataId.' >Del</button><br/><br/>
							<button class="btn btn-warning live-unlock" data-action="'.$tableDecoy.'-edit"  '.$dataUrl.$dataId.' >Edit</button>
							<button class="btn btn-warning live-unlock" data-action="'.$tableDecoy.'-edit-all"  '.$dataUrl.' >Edit All</button>
							<br/><br/><button class="btn close-toggle" >CLOSE</button>								
						</div>
						<button class="btn btn-success" data-toggle="smartToggler" data-target-prev="true">unlocks</button>							
					</div>
				</td>';
				
				
	}



	
	
	


	
		
	/*** Method for fetching live add admin control buttons ***/
	public function getLiveTableAddControls($tableDecoy, $dataUrl){
		
		return '<td  class="live-add-ref">
					<div class="modal-drop text-warning hide">
						<h3>ARE YOU SURE?</h3>
						<button class="live-add btn btn-warning " data-table="'.$tableDecoy.'" '.$dataUrl.'>YES</button>
						<br/><br/><button class="btn close-toggle" >CLOSE</button>
					</div>
					<button class="btn btn-success" data-toggle="smartToggler" data-target-prev="true">Add</button>
					<div class="modal-drop text-warning hide">
						<h3>WANT TO CLEAR?</h3>
						<button class="live-add-fc btn btn-warning " >YES</button>
						<br/><br/><button class="btn close-toggle" >CLOSE</button>
					</div>
					<button class="btn btn-warning" data-toggle="smartToggler" data-target-prev="true">Clear</button>
				</td>';
				
	}



		




	
		
	/*** Method for unlocking HTML table for admin add, edit and delete operations ***/
	public function doAdminLiveUnlocks(){
		
		/////DO AJAX UNLOCK/////////
		if(isset($_POST["admin_unlocks"]) && isset($_POST["id"]) && isset($_POST["action"])){
			
			$id = $_POST["id"];		
			$action = $this->ENGINE->sanitize_user_input($_POST["action"]);
			
			switch(strtolower($action)){
				
				case "cat-edit": $_SESSION[$K=ADMIN_EDIT_CAT]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id)?  "" : $id; break;
				
				case "cat-edit-all": $_SESSION[$K=ADMIN_EDIT_ALL_CAT]  = (isset($_SESSION[$K]) && $_SESSION[$K])?  "" : $action; break;
				
				case "cat-del": $_SESSION[$K=ADMIN_DEL_CAT]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id)?  "" : $id; break;
				
				case "scat-edit": $_SESSION[$K=ADMIN_EDIT_SCAT]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id)?  "" : $id; break;
				
				case "scat-edit-all": $_SESSION[$K=ADMIN_EDIT_ALL_SCAT]  = (isset($_SESSION[$K]) && $_SESSION[$K])?  "" : $action; break;
				
				case "scat-del":  $_SESSION[$K=ADMIN_DEL_SCAT]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id) ?  "" : $id; break;
				
				case "user-edit": $_SESSION[$K=ADMIN_EDIT_USER]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id)?  "" : $id; break;
				
				case "user-edit-all": $_SESSION[$K=ADMIN_EDIT_ALL_USER]  = (isset($_SESSION[$K]) && $_SESSION[$K])?  "" : $action; break;
				
				case "user-del":  $_SESSION[$K=ADMIN_DEL_USER]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id) ?  "" : $id; break;
				
				case "pop-edit": $_SESSION[$K=ADMIN_EDIT_POP]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id)?  "" : $id; break;
				
				case "pop-edit-all": $_SESSION[$K=ADMIN_EDIT_ALL_POP]  = (isset($_SESSION[$K]) && $_SESSION[$K])?  "" : $action; break;
				
				case "pop-del":  $_SESSION[$K=ADMIN_DEL_POP]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id) ?  "" : $id; break;
				
				case "badge-edit": $_SESSION[$K=ADMIN_EDIT_BADGE]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id)?  "" : $id; break;
				
				case "badge-edit-all": $_SESSION[$K=ADMIN_EDIT_ALL_BADGE]  = (isset($_SESSION[$K]) && $_SESSION[$K])?  "" : $action; break;
				
				case "badge-del":  $_SESSION[$K=ADMIN_DEL_BADGE]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id) ?  "" : $id; break;
				
				case "auto-pilot-edit": $_SESSION[$K=ADMIN_EDIT_AUTO_PILOT]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id)?  "" : $id; break;
				
				case "auto-pilot-edit-all": $_SESSION[$K=ADMIN_EDIT_ALL_AUTO_PILOT]  = (isset($_SESSION[$K]) && $_SESSION[$K])?  "" : $action; break;
				
				case "auto-pilot-del":  $_SESSION[$K=ADMIN_DEL_AUTO_PILOT]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id) ?  "" : $id; break;
				
				case "uibg-edit": $_SESSION[$K=ADMIN_EDIT_UI_BG]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id)?  "" : $id; break;
				
				case "uibg-edit-all": $_SESSION[$K=ADMIN_EDIT_ALL_UI_BG]  = (isset($_SESSION[$K]) && $_SESSION[$K])?  "" : $action; break;
				
				case "uibg-del":  $_SESSION[$K=ADMIN_DEL_UI_BG]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id) ?  "" : $id; break;
				
				case "emot-edit": $_SESSION[$K=ADMIN_EDIT_EMOTICONS]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id)?  "" : $id; break;
				
				case "emot-edit-all": $_SESSION[$K=ADMIN_EDIT_ALL_EMOTICONS]  = (isset($_SESSION[$K]) && $_SESSION[$K])?  "" : $action; break;
				
				case "emot-del":  $_SESSION[$K=ADMIN_DEL_EMOTICONS]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id) ?  "" : $id; break;
				
				case "cron-edit": $_SESSION[$K=ADMIN_EDIT_DAEMONS]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id)?  "" : $id; break;
				
				case "cron-edit-all": $_SESSION[$K=ADMIN_EDIT_ALL_DAEMONS]  = (isset($_SESSION[$K]) && $_SESSION[$K])?  "" : $action; break;
				
				case "cron-del":  $_SESSION[$K=ADMIN_DEL_DAEMONS]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id) ?  "" : $id; break;
	
				case "ctry-edit": $_SESSION[$K=ADMIN_EDIT_COUNTRIES]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id)?  "" : $id; break;
				
				case "ctry-edit-all": $_SESSION[$K=ADMIN_EDIT_ALL_COUNTRIES]  = (isset($_SESSION[$K]) && $_SESSION[$K])?  "" : $action; break;
				
				case "ctry-del":  $_SESSION[$K=ADMIN_DEL_COUNTRIES]  = (isset($_SESSION[$K]) && $_SESSION[$K] == $id) ?  "" : $id; break;
				
			}
			
			$JSON["result"] = '<div class="green">DONE. Please refresh the page</div>';
			$JSON["error"] = false;
			echo json_encode($JSON);
			exit();
			
		}

	}


	
		




	
		
	/*** Method for effecting admin live HTML table add, edit and delete operations in database ***/
	public function liveQueryHandler($metaArr){
		
		$nullErr = '<div class="red">Error: Null Values </div>';
		$type = strtolower($this->ENGINE->get_assoc_arr($metaArr, 'type'));
		$table = $this->ENGINE->get_assoc_arr($metaArr, 'table');
		$dbCols = trim($this->ENGINE->get_assoc_arr($metaArr, 'dbCols'), ",");
		$valArr = $this->ENGINE->get_assoc_arr($metaArr, 'valArr');
		$ExeErr = $this->ENGINE->get_assoc_arr($metaArr, 'ExeErr');
		$dbExeErr = $this->ENGINE->get_assoc_arr($metaArr, 'dbExeErr');
		
		switch($type){
			
			//UPDATE(EDIT)
			case 'update': $id = $this->ENGINE->get_assoc_arr($metaArr, 'id'); 
					
							if($table && $id && $dbCols && !$ExeErr){
								
								$sql = "UPDATE ".$table." SET ".$dbCols." WHERE ID=? LIMIT 1";
								
								if(!$this->DBM->doSecuredQuery($sql, $valArr))
									$ExeErr = '<div class="red">ERROR: '.$dbExeErr.'</div>';
								
							}
							
							break;
			//INSERT(ADD)
			default: $val_PH = trim($this->ENGINE->get_assoc_arr($metaArr, 'val_PH'), ",");
					$values = $this->ENGINE->get_assoc_arr($metaArr, 'values');
					
					if($table && $val_PH && $dbCols && !empty($valArr) && $values && !$ExeErr){
						
						$sql = "INSERT INTO ".$table." (".$dbCols.") VALUES(".$val_PH.")";
						
						if(!$this->DBM->doSecuredQuery($sql, $valArr))
							$ExeErr = '<div class="red">ERROR: '.$dbExeErr.'</div>';				
						
					}elseif(!$values)				
						$ExeErr  = $nullErr;
						
		}
		
		return	$ExeErr;
		
	}



	
		




	
		
	/*** Method for handling admin live HTML table edit request ***/
	public function doAdminLiveEdit(){
		
		global $badgesAndReputations, $DAEMON;
		
		$ExeErr='';
		$tagStripTables_arr = array("");
		
		//$this->ENGINE->url_decode_global_var($_POST); //Decode encoded POST contents
		
		if(isset($_POST["admin_edit"]) && isset($_POST["id"]) && isset($_POST["value"]) && isset($_POST["name"]) 
			&& isset($_POST["table"])){
			
			$dbCols=$slug=$valArr="";
			$id = $this->ENGINE->sanitize_number($_POST["id"]);		
			$col = $this->ENGINE->sanitize_user_input($_POST["name"], array('lowercase' => true));				
			$table = $this->ENGINE->sanitize_user_input($_POST["table"], array('lowercase' => true));
			$val = (in_array($table, $tagStripTables_arr))? $this->ENGINE->sanitize_user_input($_POST["value"]) : $this->ENGINE->sanitize_user_input($_POST["value"], array('preserveTags' => true));
			
			if($table == "cat"){
				
				$table = $this->getDatabaseTableFromDecoy($table);
				
				switch($col){
					
					case "cat-name": $dbCols = "CATEG_NAME=?, CATEG_SLUG=?" ; $slug = $this->ENGINE->sanitize_slug($val, array('appendXtn'=>false));
						$valArr = array($val, $slug, $id); 
					
						if($this->pageSlugConflicts($val)){
					
							$JSON["result"] = '<div class="red">Error: Front-end conflict</div>';						
							$JSON["error"] = true;
							echo json_encode($JSON);
							exit();
					
						}
					
						break;
					
					case "cat-desc": $dbCols = "CATEG_DESC=?" ; $valArr = array($val, $id); break;
					
				}
					
				$ExeErr = $this->liveQueryHandler(array('type' => 'update', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to update category('.$id.') with value('.$val.')', 'id' => $id));
				
			}elseif($table == "scat"){
					
				$table = $this->getDatabaseTableFromDecoy($table);
					
				switch($col){
					
					case "cat-id": $dbCols = "CATEG_ID=?" ; $valArr = array($val, $id); break;
					
					case "scat-name": $dbCols = "SECTION_NAME=?, SECTION_SLUG=?" ; $slug = $this->ENGINE->sanitize_slug($val, array('appendXtn'=>false));
						$valArr = array(ucwords($val), $slug, $id);
					 
						if($this->pageSlugConflicts($val))
							$ExeErr = '<div class="red">Error: Front-end conflict</div>';	
										
						break;
					
					case "scat-desc": $dbCols = "SECTION_DESC=?"; $valArr = array($val, $id); break;
					
					case "pscat": $dbCols = "PARENT_SECTION=?"; $valArr = array($val, $id); break;
					
					case "fp-visible": $dbCols = "FP_VISIBLE=?"; $valArr = array($this->ENGINE->sanitize_number($val), $id); break;
					
					case "min-rate": $dbCols = "MIN_AD_RATE=?"; $valArr = array($val, $id); break;
					
					case "min-prem-rate": $dbCols = "MIN_PREMIUM_RATE=?"; $valArr = array($val, $id); break;
					
					case "on-prem-rate": $dbCols = "ON_PREMIUM_RATE=?"; $valArr = array(boolVal($val), $id); break;
					
					case "min-disc": $dbCols = "MIN_DISCOUNT_RATE=?"; ((double)$val >= 100)? ($val = FALLBACK_DISCOUNT_RATE) : '';
					$valArr = array($val, $id); break;
					
					case "disc-is-prem": $dbCols = "DISCOUNT_IS_PREMIUM=?"; $valArr = array(boolVal($val), $id); break;
					
					case "access-level": $dbCols = "ACCESS_LEVEL=?"; $valArr = array($val, $id); break;
					
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'update', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to update section('.$id.') with value('.$val.')', 'id' => $id));
				
			}elseif($table == "user"){
					
				$table = $this->getDatabaseTableFromDecoy($table);
			
				switch($col){
			
					case "phone": $dbCols = "PHONE=?"; $valArr = array($val, $id); break;
			
					case "fn": $dbCols = "FIRST_NAME=?"; $valArr = array($val, $id); break;
			
					case "ln": $dbCols = "LAST_NAME=?"; $valArr = array($val, $id); break;
			
					case "sex": $dbCols = "SEX=?"; $valArr = array($val, $id); break;
			
					case "bans": $dbCols = "BAN_COUNTER=?"; $valArr = array($val, $id); break;
			
					case "site": $dbCols = "WEBSITE_URL=?"; $valArr = array($val, $id); break;
			
					case "tw": $dbCols = "TWITTER_URL=?"; $valArr = array($val, $id); break;
			
					case "fb": $dbCols = "FACEBOOK_URL=?"; $valArr = array($val, $id); break;
			
					case "instagram": $dbCols = "INSTAGRAM_URL=?"; $valArr = array($val, $id); break;
			
					case "linkedin": $dbCols = "LINKEDIN_URL=?"; $valArr = array($val, $id); break;
			
					case "wa": $dbCols = "WHATSAPP_URL=?"; $valArr = array($val, $id); break;
			
					case "crda": $dbCols = "ADS_CREDITS_AVAIL=?"; $valArr = array($val, $id); break;
			
					case "crdu": $dbCols = "ADS_CREDITS_USED=?"; $valArr = array($val, $id); break;
			
					case "prm-purse": $dbCols = "PREMIUM_CREDITS_AVAIL=?"; $valArr = array($val, $id); break;
			
					case "repu": $dbCols = "REPUTATION=?"; $valArr = array($this->ENGINE->sanitize_number($val), $id); break;
			
					case "dob": $dbCols = "DOB=?"; $valArr = array($val, $id); break;
			
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'update', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to update user('.$id.') with value('.$val.')', 'id' => $id));
				
			}elseif($table == "pop"){
			
				$table = $this->getDatabaseTableFromDecoy($table);
			
				switch($col){
			
					case "pop-content": $dbCols = "CONTENT=?"; $valArr = array($val, $id); break;
			
					case "pop-target": $dbCols = "TARGET=?"; $valArr = array($val, $id); break;
							
					case "pop-relevance": $dbCols = "RELEVANCE=?"; $valArr = array($val, $id); break;	
						
					case "pop-state": $dbCols = "STATE=?"; $valArr = array($val, $id); break;
							
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'update', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to update pop('.$id.') with value('.$val.')', 'id' => $id));
				
			}elseif($table == "badge"){
			
				$table = $this->getDatabaseTableFromDecoy($table);
			
				switch($col){
			
					case "badge-cat": $dbCols = "CATEGORY=?"; $valArr = array($val, $id); break;
			
					case "badge-name": $dbCols = "BADGE_NAME=?"; $valArr = array(strtoupper($val), $id); $badgeName = $val;
			
						if($badgesAndReputations->getBadgeDetail($val, "BADGE_NAME"))
							$ExeErr = '<div class="red">Error: Badge Name Exists</div>';
												
						break;
			
					case "criteria": $dbCols = "CRITERIA=?"; $valArr = array($val, $id); break;
							
					case "rep-reward": $dbCols = "REPUTATION_REWARD=?"; $valArr = array($val, $id); break;
							
					case "class": $dbCols = "CLASS=?"; $valArr = array($val, $id); break;	
						
					case "freq": $dbCols = "AWARD_FREQ=?"; $valArr = array(strtoupper($val), $id); break;	
						
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'update', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to update badge('.$id.') with value('.$val.')', 'id' => $id));
				
			}elseif($table == "auto-pilot"){
			
				$table = $this->getDatabaseTableFromDecoy($table);
			
				switch($col){
			
					case "pilot-name": $dbCols = "PILOT_NAME=?"; $valArr = array($val, $id);
			
						if(getAutoPilotState($val,$verify=true))
							$ExeErr = '<div class="red">Error: Pilot Name Exists</div>';
									
						break;
			
					case "status": $dbCols = "STATE=?"; $valArr = array($val, $id); break;
											
					case "state-name": $dbCols = "STATE_NAME=?"; $valArr = array($val, $id); break;
											
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'update', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to update pilot('.$id.') with value('.$val.')', 'id' => $id));
				
			}elseif($table == "cron"){
			
				$table = $this->getDatabaseTableFromDecoy($table);
			
				switch($col){
			
					case "name": $dbCols = "NAME=?"; $valArr = array($val, $id);
			
						if($DAEMON->getDetails($val))
							$ExeErr = '<div class="red">Error: Daemon Name Exists</div>';
									
						break;
			
					case "cmd": $dbCols = "COMMAND=?"; $valArr = array($val, $id); break;
											
					case "cycle-interval": $dbCols = "CYCLE_INTERVAL=?"; $valArr = array($val, $id); break;
											
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'update', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to update daemons('.$id.') with value('.$val.')', 'id' => $id));
				
			}elseif($table == "uibg"){
			
				$table = $this->getDatabaseTableFromDecoy($table);
			
				switch($col){
			
					case "uibg-style": $dbCols = "BG_STYLES=?"; $valArr = array($val, $id);	break;
			
					case "uibg-content-style": $dbCols = "CONTENT_STYLES=?"; $valArr = array($val, $id);	break;
			
					case "uibg-solid-style": $dbCols = "IS_SOLID_STYLE=?"; $valArr = array($val, $id);	break;
			
					case "uibg-cat": $dbCols = "CATEGORY=?"; $valArr = array($val, $id);	break;
			
					case "uibg-label": $dbCols = "LABEL=?"; $valArr = array($val, $id); break;
			
					case "uibg-version": $dbCols = "RENDER_VERSION=?"; $valArr = array($val, $id); break;
											
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'update', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to update uibg('.$id.') with value('.$val.')', 'id' => $id));
				
			}elseif($table == "emot"){
			
				$table = $this->getDatabaseTableFromDecoy($table);
			
				switch($col){
			
					case "emot-unicode": $dbCols = "UNICODE_SRC=?" ; $valArr = array($val, $id);
			
						if($this->emoticonValExist($val))
							$ExeErr = '<div class="red">Error: Unicode Exists</div>'; break;
			
					case "emot-ui": $dbCols = "UI_CODE=?" ; $valArr = array($val, $id);
			
						if($this->emoticonValExist($val, true))
							$ExeErr = '<div class="red">Error: UI code Exists</div>'; break;
			
					case "emot-categ": $dbCols = "CATEGORY=?" ; $valArr = array($val, $id); break;
			
					case "emot-label": $dbCols = "LABEL=?" ; $valArr = array($val, $id); break;
											
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'update', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to update emot('.$id.') with value('.$val.')', 'id' => $id));
				
			}elseif($table == "ctry"){
			
				$table = $this->getDatabaseTableFromDecoy($table);
			
				switch($col){
			
					case "name": $dbCols = "NAME=?" ; $valArr = array($this->ENGINE->title_case($val), $id);
			
						if($this->countryValExist($val))
							$ExeErr = '<div class="red">Error: Country Name Exists</div>'; break;
			
					case "name-iso-alpha-2": $dbCols = "NAME_ISO_ALPHA_2=?" ; $valArr = array(strtoupper($val), $id); break;
			
					case "name-iso-alpha-3": $dbCols = "NAME_ISO_ALPHA_3=?" ; $valArr = array(strtoupper($val), $id); break;
			
					case "name-iso-un-code": $dbCols = "NAME_ISO_UN_CODE=?" ; $valArr = array($val, $id); break;
			
					case "currency-name": $dbCols = "CURRENCY_NAME=?" ; $valArr = array(/*$this->ENGINE->title_case($val)*/$val, $id); break;
			
					case "currency-iso-alpha": $dbCols = "CURRENCY_ISO_ALPHA=?" ; $valArr = array(strtoupper($val), $id); break;
			
					case "currency-iso-num": $dbCols = "CURRENCY_ISO_NUMERIC=?" ; $valArr = array($val, $id); break;
			
					case "country-code": $dbCols = "COUNTRY_CODE=?" ; $valArr = array($val, $id); break;
			
					case "intl-dial-prefix": $dbCols = "INTL_DIAL_PREFIX=?" ; $valArr = array($val, $id); break;
			
					case "natl-dial-prefix": $dbCols = "NATL_DIAL_PREFIX=?" ; $valArr = array($val, $id); break;
			
					case "utc-dst": $dbCols = "UTC_DST=?" ; $valArr = array($val, $id); break;
											
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'update', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to update ctry('.$id.') with value('.$val.')', 'id' => $id));
				
			}
			
			
			$succ = isset($succ)? $succ : 'DONE. Please refresh the page';
			$JSON["result"] = $ExeErr? $ExeErr : '<div class="green">'.$succ.'</div>';
			$JSON["error"] = $ExeErr;
			echo json_encode($JSON);
			exit();
			
		}

	}
			


			



	

	
	
		
	/*** Method for handling admin live HTML table add request ***/
	public function doAdminLiveAdd(){
		
		global $badgesAndReputations, $DAEMON;
		
		$ExeErr=''; $nullErr = '<div class="red">Error: Null Values </div>';
		
		//$this->ENGINE->url_decode_global_var($_POST); //Decode encoded POST contents
		
		if(isset($_POST["admin_add"]) && isset($_POST["table"])){
			
			$dbCols=$slug=$val_PH=$values="";
			$valArr=array();	
			$table = $this->ENGINE->sanitize_user_input($_POST["table"], array('lowercase' => true));
					
			if($table == "cat"){
				
				$catName='';
				$table = $this->getDatabaseTableFromDecoy($table);
				
				if(isset($_POST[$K="cat-name"])){
				
					$dbCols = "CATEG_NAME,CATEG_SLUG"; 
					$val_PH = '?,?';
					$val = $catName = $this->ENGINE->sanitize_user_input($_POST[$K]);							
					$slug = $this->ENGINE->sanitize_slug($val, array('appendXtn'=>false));
					$valArr[] = $val;
					$valArr[] = $slug;
					$values .= $val;
				
					if($this->pageSlugConflicts($val))
						$ExeErr = '<div class="red">Error: Front-end conflict; category exists</div>';	
				
					if(!$val)
						$ExeErr = '<div class="red">Error: category name is required</div>';
								
				}
				
				if(isset($_POST[$K="cat-desc"])){
				
					$dbCols .= ",CATEG_DESC"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
				
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'insert', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to insert new category('.$catName.')', 'val_PH' => $val_PH, 'values' => $values));
					
			}elseif($table == "scat"){
			
				$sectionName='';
				$table = $this->getDatabaseTableFromDecoy($table);
				
				if(isset($_POST[$K="cat-id"])){
			
					$dbCols = "CATEG_ID";
					$val_PH = '?';
					$val = $this->ENGINE->sanitize_number($_POST[$K]);				
					$valArr[] = $val;	
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="scat-name"])){
			
					$dbCols .= ",SECTION_NAME,SECTION_SLUG";
					$val_PH .= ',?,?';
					$val = $sectionName = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = ucwords($val);
					$valArr[] = $this->ENGINE->sanitize_slug($val, array('appendXtn'=>false));
					$values .= $val;
			
					if($this->pageSlugConflicts($val))
						$ExeErr = '<div class="red">Error: Front-end conflict; section exists</div>';
			
					if(!$val)
						$ExeErr = '<div class="red">Error: section name is required</div>';	
								
				}
			
				if(isset($_POST[$K="scat-desc"])){
			
					$dbCols .= ",SECTION_DESC"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="pscat"])){
			
					$dbCols .= ",PARENT_SECTION";
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST["fp-visible"])){
			
					$dbCols .= ",FP_VISIBLE";
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_number($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="min-rate"])){
			
					$dbCols .= ",MIN_AD_RATE"; 
					$val_PH .= ',?';
					$val = $ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="min-prem-rate"])){
			
					$dbCols .= ",MIN_PREMIUM_RATE"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="on-prem-rate"])){
			
					$dbCols .= ",ON_PREMIUM_RATE"; 
					$val_PH .= ',?';
					$val = boolval($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="min-disc"])){
			
					$dbCols .= ",MIN_DISCOUNT_RATE";
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);
					((double)$val >= 100)? ($val = FALLBACK_DISCOUNT_RATE) : '';				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="disc-is-prem"])){
			
					$dbCols .= ",DISCOUNT_IS_PREMIUM";
					$val_PH .= ',?';
					$val = boolVal($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="access-level"])){
			
					$dbCols .= ",ACCESS_LEVEL";
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'insert', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to insert new section('.$sectionName.')', 'val_PH' => $val_PH, 'values' => $values));
									
			}elseif($table == "user"){
				
				$table = $this->getDatabaseTableFromDecoy($table);
				
				$uname = (isset($_POST[$K="uname"]))? $this->ENGINE->sanitize_user_input($_POST[$K]) : "";
				$pwd = (isset($_POST[$K="pwd"]))? $this->ENGINE->sanitize_user_input($_POST[$K]) : "";
				$em = (isset($_POST[$K="em"]))? $this->ENGINE->sanitize_user_input($_POST[$K]) : "";
				$fn = (isset($_POST[$K="fn"]))? $this->ENGINE->sanitize_user_input($_POST[$K]) : "";
				$ln = (isset($_POST[$K="ln"]))? $this->ENGINE->sanitize_user_input($_POST[$K]) : "";
				$sex = (isset($_POST[$K="sex"]))? $this->ENGINE->sanitize_user_input($_POST[$K]) : "";
				$mst = (isset($_POST[$K="mst"]))? $this->ENGINE->sanitize_user_input($_POST[$K]) : "";
				$dob = (isset($_POST[$K="dob"]))? $this->ENGINE->sanitize_user_input($_POST[$K]) : "";
			
				if($uname && $this->pageSlugConflicts($uname))
					$ExeErr = '<div class="red">Error: Front-end conflict; username exists</div>';
			
				if(!$ExeErr){
			
					list($done, $err) = $this->doAdminLiveAddUser($uname, $pwd, $em, $fn, $ln, $sex, $mst, $dob);
			
					if(!$done)					
						$ExeErr = $err;
								
				}
			
			}elseif($table == "pop"){
				
				$table = 'pop_outs';
				
				if(isset($_POST[$K="pop-content"])){
			
					$dbCols = "CONTENT"; 
					$val_PH = '?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K], array('preserveTags' => true));											
					$valArr[] = $val;				
					$values .= $val;
							
				}
			
				if(isset($_POST[$K="pop-target"])){
			
					$dbCols .= ",TARGET"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);
					!$val? ($val = 'all') : '';			
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="pop-state"])){
			
					$dbCols .= ",STATE"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="pop-relevance"])){
			
					$dbCols .= ",RELEVANCE"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}			
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'insert', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to insert new pop', 'val_PH' => $val_PH, 'values' => $values));
					
			}elseif($table == "badge"){
			
				$badgeName='';
				$table = $this->getDatabaseTableFromDecoy($table);
				
				if(isset($_POST[$K="badge-cat"])){
			
					$dbCols = "CATEGORY"; 
					$val_PH = '?';
					$val  =  $this->ENGINE->sanitize_user_input($_POST[$K]);											
					$valArr[] = $val;				
					$values .= $val;
											
				}
			
				if(isset($_POST[$K="badge-name"])){
			
					$dbCols .= ",BADGE_NAME"; 
					$val_PH .= ',?';
					$val  = $badgeName  = $this->ENGINE->sanitize_user_input($_POST[$K]);											
					$valArr[] = strtoupper($val);				
					$values .= $val;
			
					if($badgesAndReputations->getBadgeDetail($val, "BADGE_NAME"))
						$ExeErr = '<div class="red">Error: Badge Name Exists</div>';					
			
				}
			
				if(isset($_POST[$K="criteria"])){
			
					$dbCols .= ",CRITERIA"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K], array('preserveTags' => true));				
					$valArr[] = $val;
					$values .= $val;
			
				}
				
				if(isset($_POST[$K="rep-reward"])){
			
					$dbCols .= ",REPUTATION_REWARD" ; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="class"])){
			
					$dbCols .= ",CLASS"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="freq"])){
			
					$dbCols .= ",AWARD_FREQ"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = strtoupper($val);
					$values .= $val;
			
				}			
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'insert', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to insert new badge('.$badgeName.')', 'val_PH' => $val_PH, 'values' => $values));
				
			}elseif($table == "auto-pilot"){
			
				$pilotName='';
				$table = $this->getDatabaseTableFromDecoy($table);
			
				
				if(isset($_POST[$K="pilot-name"])){
			
					$dbCols = "PILOT_NAME"; 
					$val_PH = '?';
					$val = $pilotName = $this->ENGINE->sanitize_user_input($_POST[$K]);											
					$valArr[] = $val;				
					$values .= $val;
			
					if(getAutoPilotState($val, $verify=true))
						$ExeErr = '<div class="red">Error: Pilot Name Exists</div>';	
							
				}
			
				if(isset($_POST[$K="status"])){
			
					$dbCols .= ",STATE"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K], array('preserveTags' => true));				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="state-name"])){
			
					$dbCols .= ",STATE_NAME"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K], array('preserveTags' => true));				
					$valArr[] = $val;
					$values .= $val;
			
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'insert', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to insert new pilot ('.$pilotName.')', 'val_PH' => $val_PH, 'values' => $values));
						
			}elseif($table == "cron"){
			
				$pilotName='';
				$table = $this->getDatabaseTableFromDecoy($table);
			
				
				if(isset($_POST[$K="name"])){
			
					$dbCols = "NAME"; 
					$val_PH = '?';
					$val = $pilotName = $this->ENGINE->sanitize_user_input($_POST[$K]);											
					$valArr[] = $val;				
					$values .= $val;
			
					if($DAEMON->getDetails($val))
						$ExeErr = '<div class="red">Error: Daemon Name Exists</div>';	
							
				}
			
				if(isset($_POST[$K="cmd"])){
			
					$dbCols .= ",COMMAND"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="cycle-interval"])){
			
					$dbCols .= ",CYCLE_INTERVAL"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'insert', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to insert new daemon ('.$pilotName.')', 'val_PH' => $val_PH, 'values' => $values));
						
			}elseif($table == "uibg"){
			
				$table = $this->getDatabaseTableFromDecoy($table);
				
				if(isset($_POST[$K="uibg-style"])){
			
					$dbCols = "BG_STYLES";
					$val_PH = '?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);											
					$valArr[] = $val;				
					$values .= $val;	
							
				}
			
				if(isset($_POST[$K="uibg-content-style"])){
			
					$dbCols .= ",CONTENT_STYLES"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);											
					$valArr[] = $val;				
					$values .= $val;	
							
				}
			
				if(isset($_POST[$K="uibg-solid-style"])){
			
					$dbCols .= ",IS_SOLID_STYLE"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);											
					$valArr[] = $val;				
					$values .= $val;
								
				}
			
				if(isset($_POST[$K="uibg-cat"])){
			
					$dbCols .= ",CATEGORY"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);											
					$valArr[] = $val;				
					$values .= $val;	
							
				}
			
				if(isset($_POST[$K="uibg-label"])){
			
					$dbCols .= ",LABEL"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="uibg-version"])){
			
					$dbCols .= ",RENDER_VERSION"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'insert', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to insert new uibg', 'val_PH' => $val_PH, 'values' => $values));
							
			}elseif($table == "emot"){
			
				$table = $this->getDatabaseTableFromDecoy($table);
				
				if(isset($_POST[$K="emot-unicode"])){
			
					$dbCols = "UNICODE_SRC";
					$val_PH = '?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);											
					$valArr[] = $val;				
					$values .= $val;
			
					if($this->emoticonValExist($val))
						$ExeErr = '<div class="red">Error: Unicode Exists</div>';
								
				}
			
				if(isset($_POST[$K="emot-ui"])){
			
					$dbCols .= ",UI_CODE"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K], array('preserveWhitespace' => 2));											
					$valArr[] = $val;				
					$values .= $val;
			
					if($this->emoticonValExist($val, true))
						$ExeErr = '<div class="red">Error: UI code Exists</div>';
								
				}
			
				if(isset($_POST[$K="emot-categ"])){
			
					$dbCols .= ",CATEGORY"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);											
					$valArr[] = $val;				
					$values .= $val;
								
				}
			
				if(isset($_POST[$K="emot-label"])){
			
					$dbCols .= ",LABEL"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'insert', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to insert new emot', 'val_PH' => $val_PH, 'values' => $values));
						
			}elseif($table == "ctry"){
			
				$table = $this->getDatabaseTableFromDecoy($table);
				
				if(isset($_POST[$K="name"])){
			
					$dbCols = "NAME";
					$val_PH = '?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);											
					$valArr[] = $this->ENGINE->title_case($val);				
					$values .= $val;
			
					if($this->countryValExist($val))
						$ExeErr = '<div class="red">Error: Country Name Exists</div>';
								
				}
			
				if(isset($_POST[$K="name-iso-alpha-2"])){
			
					$dbCols .= ",NAME_ISO_ALPHA_2"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);											
					$valArr[] = strtoupper($val);				
					$values .= $val;
								
				}
			
				if(isset($_POST[$K="name-iso-alpha-3"])){
			
					$dbCols .= ",NAME_ISO_ALPHA_3"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);											
					$valArr[] = strtoupper($val);				
					$values .= $val;
								
				}
			
				if(isset($_POST[$K="name-iso-un-code"])){
			
					$dbCols .= ",NAME_ISO_UN_CODE"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="currency-name"])){
			
					$dbCols .= ",CURRENCY_NAME"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = /*$this->ENGINE->title_case($val)*/$val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="currency-iso-alpha"])){
			
					$dbCols .= ",CURRENCY_ISO_ALPHA"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = strtoupper($val);
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="currency-iso-num"])){
			
					$dbCols .= ",CURRENCY_ISO_NUMERIC"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="country-code"])){
			
					$dbCols .= ",COUNTRY_CODE"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="intl-dial-prefix"])){
			
					$dbCols .= ",INTL_DIAL_PREFIX"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="natl-dial-prefix"])){
			
					$dbCols .= ",NATL_DIAL_PREFIX"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
			
				if(isset($_POST[$K="utc-dst"])){
			
					$dbCols .= ",UTC_DST"; 
					$val_PH .= ',?';
					$val = $this->ENGINE->sanitize_user_input($_POST[$K]);				
					$valArr[] = $val;
					$values .= $val;
			
				}
				
				$ExeErr = $this->liveQueryHandler(array('type' => 'insert', 'table' => $table, 'dbCols' => $dbCols, 'valArr' => $valArr,
				'ExeErr' => $ExeErr, 'dbExeErr' => 'Failed to insert new ctry', 'val_PH' => $val_PH, 'values' => $values));
						
			}
					
			$succ = isset($succ)? $succ : 'DONE. Please refresh the page';
			$JSON["result"] = $ExeErr? $ExeErr : '<div class="green">'.$succ.'</div>';
			$JSON["error"] = $ExeErr;
			echo json_encode($JSON);
			exit();
			
		}

	}







	

	
	
		
	/*** Method for handling admin live HTML table delete request ***/
	public function doAdminLiveDelete(){
		
		$ExeErr='';
		
		if(isset($_POST["admin_delete"]) && isset($_POST["id"]) && isset($_POST["table"])){
					
			$id = $this->ENGINE->sanitize_number($_POST["id"]);						
			$table = $tableDecoy = $this->ENGINE->sanitize_user_input($_POST["table"], array('lowercase' => true));
			
			$delErr = '<div class="red">Error: Failed to delete record with id('.$id.') from '.$table.'</div>';
			
			$table = $this->getDatabaseTableFromDecoy($table);
			
			if($table && $id){
				
				if($tableDecoy == "user"){
					
					if($currentuser = $this->ACCOUNT->memberIdToggle($id)){
						
						list($done, $alertUser)	= $this->ACCOUNT->scheduleTermination($currentuser);
						
						if($alertUser)
							$ExeErr = $alertUser;
						elseif($done)
							$succ = '<span class="alert alert-warning">'.$currentuser.'`s'.' account has been scheduled for termination, It will be processed after 48 hours.</span>';
						else
							$ExeErr = $delErr;
									
					}
					
				}else{
					
					$sql = "DELETE FROM ".$table." WHERE ID=? LIMIT 1";
					$valArr = array($id);
					
					if(!$this->DBM->doSecuredQuery($sql, $valArr))
						$ExeErr = $delErr;
					
				}			
				
			}
			
			$succ = isset($succ)? $succ : 'DONE. Please refresh the page';
			$JSON["result"] = $ExeErr? $ExeErr : '<div class="green">'.$succ.'</div>';
			$JSON["error"] = $ExeErr;
			echo json_encode($JSON);
			exit();
			
		}

	}
		





	

	

	
	
		
	/*** Method for handling admin live HTML table add new user request ***/
	public function doAdminLiveAddUser($username, $password, $email, $fname, $lname, $sex, $mrt_stat, $dob){
		
		$stat = $err = '';
		//re-arrange dob to db format (from DD-MM-YYYY to YYYY-MM-DD)
		$dob = substr($dob, -4, 4).substr($dob, -8, 4).substr($dob, 0, 2);
		
		if($username && $password && $email && $fname && $lname && $sex && $mrt_stat && $dob){
					
			if($this->ENGINE->has_white_space(array($username)))
				$err .= '<div class="red">Spaces are not allowed in username.</div>';
			
			if(mb_strlen($username) < MIN_USERNAME || mb_strlen($username) > MAX_USERNAME)																						
				$err .= '<div class="red">username: min of '.MIN_USERNAME.'  and max of '.MAX_USERNAME.'</div>';
			
			if(!preg_match(USERNAME_PATTERN, $username))
				$err .= '<div class="red">The username field must contain at least one alphabet and optionally Numbers, Dashes(-) and Underscores(_)</div>';							
			
			if($this->ENGINE->has_white_space(array($password)))
				$err .= '<div class="red">Spaces are not allowed in password.</div>';
									
			if(mb_strlen($password) < MIN_PWD || mb_strlen($password) > MAX_PWD)
				$err .= '<div class="red">Password: min of '.MIN_PWD.'  and max of '.MAX_PWD.'</div>';
				
			if(!preg_match(PWD_PATTERN, $password))
				$err .= '<div class="red">Password must contain an uppercase and lowercase alphabet, a number, a symbol and no spaces!</div>';							
			
			if(!(preg_match("#[0-9]{4}\-[0-9]{2}\-[0-9]{2}#i", $dob)))
				$err .= '<div class="red">DOB Format DD-MM-YYYY e.g 09-04-1984</div>';
			
			if($this->emailExist($email))
				$err .= '<div class="red">Email exists</div>';
			
			if(!in_array(strtoupper($sex), $gndArr=array('M', 'F')))
				$err .= '<div class="red">Gender is either '.implode(' or ', $gndArr).'</div>';
			
			if(!in_array(ucfirst(strtolower($mrt_stat)), MARITAL_ARR))
				$err .= '<div class="red">Marital is either: '.implode(', ', MARITAL_ARR).'</div>';
			
			if(!$err){
				
				$password = $this->ACCOUNT->passwordEncrypt($password);
				
				$confirmstatus = 1;
				$sex = strtoupper($sex);
				$mrt_stat = ucfirst($mrt_stat);
				///PDO QUERY//////
																
				$sql = "INSERT INTO users (USERNAME, PASSWORD, EMAIL, FIRST_NAME, LAST_NAME, TIME,CONFIRMED
							,SEX ,MARITAL_STATUS, DOB, LAST_SEEN, REPUTATION) VALUES(?,?,?,?,?,NOW(),?,?,?,?,NOW(),?)";
				$valArr = array($username, $password, $email, ucfirst($fname), ucfirst($lname), $confirmstatus, $sex, $mrt_stat, $dob, 1);
					
				if($this->DBM->doSecuredQuery($sql, $valArr)){
					
					if(!$stat = badgeAwardHandle($this->DBM->getLastInsertId(), 'STUDENT'))
						$err = '<div class="red">Error: Ooops! seems something went wrong. Please try again.</div>';
					
				}
				
			}else
				$err = '<div class="red">Errors: '.$err.'</div>';
				
		}else
			$err = '<div class="red">Error: All fields are required.</div>';
		
		return array($stat, $err);
		
	}






	

	
		
	/*** Method for fetching reusable HTML components ***/
	public function getHtmlComponent($type='', $metaArr=array()){
		
		$component = ''; $switchSlider = 'switch-slider'; $iconicRadio = 'iconic-radio';
		!$type? ($type = $defaultType) : '';
		$type = strtolower($type);
		
		switch($type){
			
			case $switchSlider:
			case $iconicRadio:
			case 'iconic-checkbox':
			$wrapClass = $this->ENGINE->get_assoc_arr($metaArr, 'wrapClass');
			$wrapData = $this->ENGINE->get_assoc_arr($metaArr, 'wrapData');
			$innerWrapData = $this->ENGINE->get_assoc_arr($metaArr, 'iWrapData');
			$labelName = $this->ENGINE->get_assoc_arr($metaArr, 'label');
			$label2R = (bool)$this->ENGINE->get_assoc_arr($metaArr, 'label2R');
			$fieldName = $this->ENGINE->get_assoc_arr($metaArr, 'fieldName'); 
			$fieldData = $this->ENGINE->get_assoc_arr($metaArr, 'fieldData'); 
			$fieldClass = $this->ENGINE->get_assoc_arr($metaArr, 'fieldClass'); 
			$fieldId = $this->ENGINE->get_assoc_arr($metaArr, 'fieldId'); 
			$title = $this->ENGINE->get_assoc_arr($metaArr, 'title'); 
			$title = $title? ' title="'.$title.'" ' : ''; 
			$labelFor = $fieldId? ' for="'.$fieldId.'" ' : ''; 
			$on = (bool)($this->ENGINE->is_assoc_key_set($metaArr, $K='on')? $this->ENGINE->get_assoc_arr($metaArr, $K) : true);
			$value = $this->ENGINE->get_assoc_arr($metaArr, 'value'); 
			$valueStrict = $this->ENGINE->get_assoc_arr($metaArr, 'valueStrict'); 
			$hiddenFieldSubmitTracker = '<input type="hidden" name="'.$fieldName.'_submitted"/>'; 
			
			if($type == $switchSlider){
				
				$switchRounded = (bool)($this->ENGINE->is_assoc_key_set($metaArr, $K='round')? $this->ENGINE->get_assoc_arr($metaArr, $K) : true);
				$component = '<label '.$labelFor.' class="'.$wrapClass.'" '.$title.$wrapData.' >'.($label2R? '' : $labelName).'<span class="switch-toggle'.($switchRounded? ' round' : '').'" '.$innerWrapData.'><input '.($on? 'checked="checked"' : '').' type="checkbox" class="checkbox '.$fieldClass.'" name="'.$fieldName.'" id="'.$fieldId.'" '.$fieldData.'/><i class="switch-slider" aria-hidden="true"></i></span>'.($label2R? $labelName : '').$hiddenFieldSubmitTracker.'</label>';
				
			}else{
				
				$isRadio = ($type == $iconicRadio);
				$component = '<label '.$labelFor.' class="'.($isRadio? 'radio' : 'checkbox').'-iconic '.$wrapClass.'" '.$title.$wrapData.'>'.($label2R? '' : $labelName).'<input type="'.($isRadio? 'radio' : 'checkbox').'" name="'.$fieldName.'" '.($valueStrict? $valueStrict : 'value="'.$value.'"').($on? 'checked="checked"' : '').' class="'.($isRadio? 'radio' : 'checkbox').' '.$fieldClass.'" id="'.$fieldId.'" '.$fieldData.' /><i aria-hidden="true"></i>'.($label2R? $labelName : '').$hiddenFieldSubmitTracker.'</label>';
				
			}
			
			break;
			
		}
		
		return $component;
		
	}




	
	

	
		
	/*** Method for fetching Site Ctrl Endpoints ***/
	public function getEndpointUrl($endpointKey){

		switch(strtolower($endpointKey)){

			case 'admin-opens': $endpointUrl = 'activate-admin-opens'; break;

			default: $endpointUrl = '';

		}

		return $endpointUrl;

	}
	

	
	

	
		
	/*** Method for session based Ctrl properties ***/
	public function sessCtrlProps($propName, $propVal = true, $setState = false){
		
		$propInitBtn=$propState=$btnTitle=$btnLabel='';

		if(!$isTopStaff = $this->SESS->getUltimateLevel())
			return '';
		
		$propKey = 'sess_ctrl_'.str_ireplace('-', '_', $propName);

		switch(strtolower($propName)){

			case 'admin-opens': 
				$btnLabel = 'Admin Opens';
				$btnTitle = 'This button when activated ensures no staff can reverse opening actions(e.g thread/post opening, unlocking etc)';
				$endpointUrl = $this->getEndpointUrl('admin-opens');
				break;

		}

		//SET/UNSET STATE
		if($setState){
			
			$this->ENGINE->is_global_var_set('ss', $propKey)?

			$this->ENGINE->unset_global_var('ss', $propKey) : $this->ENGINE->set_global_var('ss', $propKey, $propVal);			
			
		}else{//GET STATE

			$propState = $this->ENGINE->get_global_var('ss', $propKey);
							
			$propStateOn = $propState? 'checked="checked"' : '';

		
			$fieldData = 'data-sess-ctrl="true" data-url="'.$endpointUrl.'"';
			$propInitBtn = '<div class="field-ctrl">'.$this->getHtmlComponent('switch-slider', array('label' => $btnLabel, 'wrapClass' => 'text-warning', 'fieldName' => $propKey, 'on' => $propStateOn, "title" => $btnTitle, "fieldData" => $fieldData)).'</div>';			

		}
		
		return array($propState, $propInitBtn);

	}


		
	/*** Method for fetching theme mode properties ***/
	public function getThemeModeProps($prop, $floatToggle=true){
		
		$prefThemeModeKey = 'USER_PREF_THEME_MODE'; 
		$darkMode = 'dark'; 
		$lightMode = 'light';
		$systemsMode = 'system';
		
		if($sessPrefThemeMode = $this->ENGINE->get_global_var('ss', $prefThemeModeKey));
			
		elseif($sessPrefThemeMode = $this->ENGINE->get_global_var('ck', $prefThemeModeKey));
			
		else
			$sessPrefThemeMode = $this->SESS->getPreferredThemeMode();
			
		!$sessPrefThemeMode? ($sessPrefThemeMode = $lightMode) : '';
		
		if($sessPrefThemeMode == $systemsMode){
			
			$toggleIconCls = 'fa-moon';
			$toggleThemeMode = $darkMode;
			
		}elseif($sessPrefThemeMode == $lightMode){
			
			$toggleIconCls = 'fa-moon';
			$toggleThemeMode = $darkMode;
			
		}else{
			
			$toggleIconCls = 'fa-sun';
			$toggleThemeMode = $lightMode;
			
		}
		
		switch(strtolower($prop)){
			
			case 'context-attr': $retProp = 'data-theme-mode="'.$sessPrefThemeMode.'"'; break;
			
			case 'meta-tag': $retProp = '<meta id="theme-mode-scheme" name="color-scheme" content="'.$sessPrefThemeMode.'" />'; break;
			
			case 'storage-key': $retProp = $prefThemeModeKey; break;
			
			default: $retProp = '<span class="'.($floatToggle? 'clear' : '').'"><span class="'.($floatToggle? 'pull-r' : '').' theme-mode-toggle" data-theme-mode-toggle="true" title="switch to '.$toggleThemeMode.' mode">'.$this->getFA($toggleIconCls.' fa-lg').'</span></span>';
			
			
		}
		
		return $retProp;
	
	}



	
	
	/* Method for building context for file_get_contents post request */
	public function buildRequestContext($body, $headers = ['Content-Type: application/x-www-form-urlencoded'], $method = 'post', $wrapper = 'http'){
		
		$opts = array(
			$wrapper => array(
				'method' => $method,
				'header' => implode("\r\n", $headers),
				'content' => http_build_query($body),
				'timeout' => 30,
				//'ignore_errors' => true
			)
		);
		
		return stream_context_create($opts);
	
		
	}




				
	/*** Method for custom file_get_contents ***/
	public function file_get_contents($url, $metaArr=array()){

		$sslOptArr = $httpOptArr = array();

		$getHttpCode = (bool)($this->ENGINE->is_assoc_key_set($metaArr, $K='getHttpCode')? $this->ENGINE->get_assoc_arr($metaArr, $K) : false);

		if($getHttpCode){

			$httpOptArr = array(

				'method' => "HEAD",
				'ignore_errors' => 1,
				//'max_redirects' => 0
				
			);
			
		}

		$sslOptArr = array(

			'verify_peer' => false,
			'verify_peer_name' => false

		);


		$optArr = array(

			//'http' => $httpOptArr,
			'ssl' => $sslOptArr

		);
		
		$contentBody = @file_get_contents($url, false, stream_context_create($optArr));

		if($getHttpCode){
		
			$responseCode = 0;

			sscanf($http_response_header[0], 'HTTP/%*d.%*d %d', $responseCode);

			return $responseCode;

		}

		return $contentBody;		

	}



	

	
	/* Method for running curl requests */
	public function file_get_contents_curl($metaArr){
		
		$curlUrl = $this->ENGINE->get_assoc_arr($metaArr, 'url');
		$curlRequestMethod = $this->ENGINE->get_assoc_arr($metaArr, 'method');
		!$curlRequestMethod? ($curlRequestMethod = 'POST') : '';
		$httpHeader = $this->ENGINE->get_assoc_arr($metaArr, 'header');
		!$httpHeader? ($httpHeader = []) : '';
		$jsonData = (bool)($this->ENGINE->is_assoc_key_set($metaArr, $K='jsonData')? $this->ENGINE->get_assoc_arr($metaArr, $K) : false);
		$sameOrigin = (bool)($this->ENGINE->is_assoc_key_set($metaArr, $K='sameOrigin')? $this->ENGINE->get_assoc_arr($metaArr, $K) : true);
		$getHttpCode = (bool)($this->ENGINE->is_assoc_key_set($metaArr, $K='getHttpCode')? $this->ENGINE->get_assoc_arr($metaArr, $K) : false);
		$curlPostFields = $this->ENGINE->get_assoc_arr($metaArr, 'postFields');
		$this->ENGINE->is_nested_arr($httpHeader)? (list($httpHeader) = $httpHeader) : $httpHeader;
		$this->ENGINE->is_nested_arr($curlPostFields)? (list($curlPostFields) = $curlPostFields) : $curlPostFields;
		
		// Important: Disable SSL verification when you domain is not secured https 
		$sslVerifyPeer = $this->ENGINE->is_secured_protocol();

		$curl = curl_init();

		//set options Arr
		$optArr = array(
		
			CURLOPT_URL => $curlUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => $curlRequestMethod,
			CURLOPT_HTTPHEADER => $httpHeader,
			CURLOPT_SSL_VERIFYPEER => $sslVerifyPeer
			
		);
	
		if($getHttpCode){
			
			$optArr[CURLOPT_NOBODY]  = true;
			$optArr[CURLOPT_FOLLOWLOCATION]  = true;
			$optArr[CURLOPT_TIMEOUT]  = 10;			

		}
		
		if(is_array($curlPostFields)){
			
			$curlPostFields['uid'] = $this->SESS->getUserId();
			$curlPostFields = $jsonData? json_encode($curlPostFields) : http_build_query($curlPostFields);
			$optArr[CURLOPT_POST] = true;
			$optArr[CURLOPT_POSTFIELDS] = $curlPostFields;
			//curl_setopt($curl, CURLOPT_POST, true);
			//curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPostFields);
			
		}
		
		if($sameOrigin){
			
			/*$cookieFile = '/'.$this->SITE->getMediaLinkRoot('tmp', false).'cookies.txt';
			$cookieFile = $this->SESS->setSessionSavePath();
			$optArr[CURLOPT_COOKIESESSION] = true;
			$optArr[CURLOPT_COOKIEFILE] = $cookieFile;
			$optArr[CURLOPT_COOKIEJAR] = $cookieFile;
			$optArr[CURLOPT_COOKIESESSION] = true;
			$optArr[CURLOPT_COOKIE] = $this->SESS->getSessionName().'='.$this->SESS->getSessionId();
			*/
			
		}

		//set the accumulated options
		curl_setopt_array($curl, $optArr);		
		
		$rawResponse = curl_exec($curl);
		$err = curl_error($curl);

		if($getHttpCode){

		$httpCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE /*CURLINFO_HTTP_CODE legacy alias*/);

		}

		curl_close($curl);

		if($getHttpCode)
			return $httpCode;

		$jsonDecodedResponse = json_decode($rawResponse);

		//null object response api call protection
		is_object($jsonDecodedResponse)? '' : ($jsonDecodedResponse = new ObjectProtection());
		
		return array($rawResponse, $jsonDecodedResponse, $err);
		
	}
	
	
	

	
	/*** Method for fetching HTTP response code ***/
	public function get_http_code($url, $useCurl = true){

		$metaArr = array('url' => $url, 'getHttpCode' => true);
		
		return (

			$useCurl? $this->file_get_contents_curl($metaArr) : $this->file_get_contents($url, $metaArr)
	
		);		
		
	}


	

		
	/*** Method for checking if a url is pingable ***/
	public function urlIsPingable($url){
		
		!$this->ENGINE->has_protocol_prefix($url)? ($url = $this->ENGINE->get_domain().'/'.$url) : '';
			
		return ($this->get_http_code($url) !== 404);		
		
	}
		

	
		
	/*** Method for parsing/displaying HTML pages ***/
	public function buildPageHtml($optArr){
		
		global $GLOBAL_rdr, $GLOBAL_timeoutMessage, $GLOBAL_username;
		
		$siteName = $this->getSiteName();
		
		$optArr = is_array($optArr)? $optArr : (array)$optArr;
		/*!isset($optArr[$K="pageTitle"])? ($optArr[$K] = '') : ''; 
		!isset($optArr[$K="pageBody"])? ($optArr[$K] = '') : ''; 
		!isset($optArr[$K="pageBodyMetas"])? ($optArr[$K] = '') : ''; 
		!isset($optArr[$K="preBodyMetas"])? ($optArr[$K] = '') : ''; 
		!isset($optArr[$K="pageBodyOnload"])? ($optArr[$K] = '') : '';
		!isset($optArr[$K="pageHeaderMetas"])? ($optArr[$K] = '') : '';*/
		!isset($optArr[$K="pageTitlePrependDomain"])? ($optArr[$K] = false) : ''; 
		!isset($optArr[$K="pageHeader"])? ($optArr[$K] = true) : '';
		!isset($optArr[$K="ogMetas"])? ($optArr[$K] = array()) : '';
		!isset($optArr[$K="pageFooter"])? ($optArr[$K] = true) : '';
		$kArr = array('ogMetas', 'pageHeaderMetas', 'pageTitle', 'pageTitlePrependDomain', 'pageBodyMetas',  'pageBody', 'preBodyMetas', 'pageBodyOnload', 'pageHeader', 'pageFooter');
		list($ogMetas_arr, $pageHeaderMetas, $pageTitle, $pageTitlePrependDomain, $pageBodyMetas, $pageBody, $preBodyMetas, $pageBodyOnload, $pageHeader, $pageFooter) = $this->ENGINE->get_assoc_arr($optArr, $kArr);
		
		$pageTitle = $this->ENGINE->title_case($pageTitle);
		$nl = "\n";
		
	echo'<!DOCTYPE HTML>
	<html '. DOC_ATTR.'>
	<head>
	<meta property="og:locale" content="en-UK" />
	<meta property="og:type" content="website" />
	<meta property="og:site_name" content="'.$siteName.'" />
	<meta property="og:title" content="'.$pageTitle.'" />'.
	(($ogDesc = $this->ENGINE->get_assoc_arr($ogMetas_arr,'desc'))? $nl.'<meta property="og:description" content="'.$ogDesc.'" />' : '').
	(($ogUrl = $this->ENGINE->get_assoc_arr($ogMetas_arr,'url'))? $nl.'<meta property="og:url" content="'.$ogUrl.'" />' : '').
	(($ogImg= $this->ENGINE->get_assoc_arr($ogMetas_arr,'img'))? $nl.'<meta property="og:image" content="'.$ogImg.'" />' : '').'
	<meta property="twitter:card" content="summary" />
	<meta property="twitter:title" content="'.$pageTitle.'" />'.
	($ogDesc? $nl.'<meta property="twitter:description" content="'.$ogDesc.'" />' : '').
	($ogUrl? $nl.'<meta property="twitter:url" content="'.$ogUrl.'" />' : '').
	($ogImg? $nl.'<meta property="twitter:image" content="'.$ogImg.'" />' : '').'
	<title>'.($pageTitlePrependDomain? ucwords($siteName).' - ' : '').$pageTitle.(!$pageTitlePrependDomain? ' | '.ucwords($siteName) : '').'</title>'.
	($ogUrl? $nl.'<link rel="canonical" href="'.$ogUrl.'" />' : '').
	$pageHeaderMetas;						

	require_once(DOC_ROOT.'/include-html-headers.php');

	echo'</head>
	<body '.$this->getThemeModeProps('context-attr').' id="site" class="_cs-config" '.($pageBodyOnload? 'onload="'.$pageBodyOnload.'"' : '').' >';
	if($pageHeader)
	require_once(DOC_ROOT.'/page-top-nav.php');
	echo ''.$preBodyMetas.
		'<!--CONTENT AUTO DIVS OPEN-->
			<div class="container">		
			<div class="page-content-base">		
		<!--END CONTENT AUTO DIVS OPEN-->'.
		//GET SESSION TIMEOUT MESSAGE IF DESIRED///
		(($GLOBAL_timeoutMessage && !$GLOBAL_username)? '<div class="alert alert-danger align-c blink">'.$GLOBAL_timeoutMessage.'</div>' : '')
		.$pageBody.'
		<!--CONTENT AUTO DIVS CLOSE-->
			</div>
			</div>
		<!--END CONTENT AUTO DIVS CLOSE-->';
	if($pageFooter)
		require_once(DOC_ROOT.'/page-footer.php');
	$pageBodyMetasArr = is_array($pageBodyMetas)? $pageBodyMetas : (array)$pageBodyMetas;
	foreach($pageBodyMetasArr as $meta){
		$meta = trim($meta);
		if(mb_substr($meta, 0, 1) == '<') echo $meta;
		elseif($meta) require_once($meta);
	}
	echo'</body>
	</html>';
	exit();

	}


}






?>