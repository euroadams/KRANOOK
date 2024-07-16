<?php

require_once ('page-common-headers.php');

//require_once("geoplugin.class/geoplugin.class.php");

$siteDomain = $GLOBAL_siteDomain;
$siteName = $GLOBAL_siteName;
$currentLocation="";


///UNSET SESSION ALERTS///
$ENGINE->unset_global_var('ss', 'SESS_ALERT');

/*****CHECK IF USER HAS COOKIE ENABLED
	CHECK IF THE TEST COOKIE SET FROM page common headers WAS SENT
**/
if(!empty($_SESSION) && !$GLOBAL_username && !$ENGINE->get_global_var('ck', 'isCookieEnabled'))
	/*echo '<div class="container"><div class="align-c alert alert-danger">ATTENTION!!!<br/> It seems that your browser is either not cookie enabled or has its ability to accept cookies disabled!
			<br/> Please note that without a cookie enabled browser, You may not be able to use some of the features on this website.
			<br/>We advice You use a cookie enabled device to get a better user experience while browsing this website. 
			<br/>Please see our <a href="/policies" class="links">policies</a> for more.
		</div></div>';	*/;

$followUs = $SITE->getSocialLinks(array('type'=>'followus','alt'=>true,'size'=>'lg'));
$newsLetterSubLink = $GLOBAL_username? '<a class="links" target="_blank" href="/dnd-mail-list/subscribe/'.$ACCOUNT->SESS->getEmail().'">Newsletters DND</a>' : '';

$linkArr = array(
	'login' => '<a class="links" href="/login">Login</a>',
	'signup' => '<a class="links" href="/signup">Signup</a>',
	'privacy' => '<a class="links" href="/policies/privacy">Privacy Policy</a>',
	'terms' => '<a class="links" href="/policies/terms-of-service">Terms of Service</a>',
	'dmca' => '<a class="links" href="/policies/dmca">DMCA Disclaimer</a>',
	'how_to_ad' => '<a href="/ads-campaign#hta" class="links">How To Advertise</a>',
	'estimated_ad_rates' => '<a class="links" href="/estimated-ad-rates">Estimated Ad Rates</a>',
	'ad_policy' => '<a class="links" href="/policies/ads">Ad Policy</a>',
	'support' => '<a class="links" href="/feedback">Support/Feedback</a>',
	'contact' => '<a class="links" href="/contact-us">Contact Us</a>',
	'about' => '<a class="links" href="/about-us">About Us</a>',
	'faq' => '<a class="links" href="/faq">Faq</a>',
	'rules' => '<a class="links" href="/policies/general-community-rules">General Community Rules</a>',
	'badges' => '<a class="links" href="/policies/badges-reputations">Badges & Reputations</a>',
	'tools' => '<a class="links" href="/tools">Website Tools</a>'
);

 
?>

					
	<div class="nav-base no-pad"><ul class="nav nav-pills justified-center"><li><a class="links skyb topageup" href="#go_up">Go Up &uArr;</a></li></ul></div>
	<span id="go_down"></span>

	<div class="footer">
		<div class="nav qlinks align-l dsk-platform-dpn">
			<div class="container">
				<div class="row cols-padx">					
					<div class="footer-ribbon custom-ffam"><span><i class="fas fa-link"></i> Quick Links</span></div>
					<div class="footer-ribbon alt"><?= $SITE->getBrandLogo(); ?></div>
					<div class="col-sm-w-2-pull">
						<h3 class="metered-title">Legals</h3>
						<ul class="">
							<li><?php echo $linkArr["privacy"]; ?></li>
							<li><?php echo $linkArr["terms"]; ?></li>
							<li><?php echo $linkArr["dmca"]; ?></li>
						</ul>
					</div>
					<div class="col-sm-w-2-pull">
						<h3 class="metered-title">Advertisers</h3>
						<ul class="">
							<li><?php echo $linkArr["how_to_ad"]; ?></li>
							<li><?php echo $linkArr["estimated_ad_rates"]; ?></li>
							<li><?php echo $linkArr["ad_policy"]; ?></li>
						</ul>
					</div>
					<div class="col-sm-w-2-pull">
						<h3 class="metered-title">Get in Touch</h3>
						<ul class="">
							<li><?php echo $linkArr["contact"]; ?></li>
							<li><?php echo $linkArr["support"]; ?></li>
							<li><?php echo $linkArr["faq"]; ?></li>
						</ul>
					</div>
					<div class="col-sm-w-2-pull">
						<h3 class="metered-title"><?php echo $siteName; ?></h3>
						<ul class="">
							<?php if(!$GLOBAL_username){ ?>
							<li><?php echo $linkArr["signup"]; ?></li>
							<li><?php echo $linkArr["login"]; ?></li>		
							<?php } ?>
							<li><?php echo $linkArr["about"]; ?></li>
							<li><?php echo $linkArr["rules"]; ?></li>
							<li><?php echo $linkArr["badges"]; ?></li>
							<li><?php echo $linkArr["tools"]; ?></li>
							<li><?php echo $newsLetterSubLink; ?></li>
						</ul>
					</div>
					<div class="col-sm-w-2-pull follow-us">
						<h3 class="metered-title">Follow Us</h3>
						<ul class="">
							<li><?php echo $followUs; ?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="nav qlinks ql-mob align-c mob-platform-dpn">
			<h3>QUICK LINKS</h3>
			<ul class="container">										
				<?php if(!$GLOBAL_username){ ?>
				<li><?php echo $linkArr["signup"]; ?></li>
				<li><?php echo $linkArr["login"]; ?></li>	
				<?php } ?>
				<li><?php echo $linkArr["about"]; ?></li>					
				<li><?php echo $linkArr["contact"]; ?></li>					
				<li><?php echo $linkArr["support"]; ?></li>					
				<li><?php echo $linkArr["how_to_ad"]; ?></li>
				<li><?php echo $linkArr["estimated_ad_rates"]; ?></li>				
				<li><?php echo $linkArr["faq"]; ?></li>
				<li><?php echo $linkArr["privacy"]; ?></li>
				<li><?php echo $linkArr["ad_policy"]; ?></li>
				<li><?php echo $linkArr["terms"]; ?></li>
				<li><?php echo $linkArr["dmca"]; ?></li>
				<li><?php echo $linkArr["rules"]; ?></li> 
				<li><?php echo $linkArr["badges"]; ?></li>
				<li class="esc-pipe"><?php echo $newsLetterSubLink; ?></li>
				<li class="esc-pipe social-follows"><?php echo $followUs; ?></li>															
			</ul>
		</div>
		<div class="container">
			<?php echo $SITE->getNavSearch('ft', true).$SITE->platformSwitchBtn(); ?>
			
			<footer id="show_footer">
				<?php echo $SITE->getCopyRight(); ?>
				<br/><strong>Disclaimer</strong>: Every <a href="<?=$siteDomain;?>" class="links"><?=$siteName;?></a> member is <strong>solely responsible</strong> for his/her <strong>posts</strong> or <strong>uploads</strong> on <a href="<?=$siteDomain;?>"  class="links"><?=$siteName;?></a>. 
				<div class="dsk-platform-dpn">
					<div id="geoposname"><?= $currentLocation ?></div>
					<div id="uagent"></div>
				</div>
				<div class="clear">
				<?php 
					if($GLOBAL_isStaff){
						echo '<b class="pull-r">Ad Slots Filled: <span class="prime">'.$adsCampaign->getAdSlots('', '', true).'</span> of '.($dbm->getTableCount('sections') * (int)(MAX_AD_MATRIX)).'</b>';
					}
				?>
				</div>
			</footer>
		</div>
	</div>
	<div id="doc-end" aria-hidden="true"></div>		
	<script  type="text/javascript" src="<?php echo $siteDomain.'/'.ASSET_PREFIX; ?>js/main/footer.min.js<?php echo $SITE->getCacheVer("ft-js"); ?>"></script>	

<?php



?>
<!--PAGE AUTO DIVS CLOSE-->
</div>
<!--END PAGE AUTO DIVS CLOSE-->
