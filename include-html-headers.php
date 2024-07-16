<?php

global $SITE, $ENGINE, $dbm, $GLOBAL_siteDomain, $GLOBAL_siteName;

/**SINCE THIS IS LOADED FIRST IN ALL PAGES, IT'S BEST TO IMPLEMENT IT HERE INSTEAD OF FROM EUROFOOTER****/
/***********GET USER VIEW PREFERENCE (MOBILE OR CLASSIC/DESKTOP)*********/

$viewport = $SITE->platformSwitchBtn(false, true);

$siteName = $GLOBAL_siteName;
$siteRoot = $GLOBAL_siteDomain.'/';
$selfHostedRsrcRoot = $SITE->getResourceRoot();
$devHostRsrcRoot = $SITE->getResourceRoot('dev');

$rqstBaseURL = strtolower($ENGINE->get_page_path('page_url', 1, true));
$isDev = IS_DEV;
$cacheVer = $SITE->getCacheVer('');
$siteCSS = 'k5xiGEMCQr.min.css'.$SITE->getCacheVer('css');
$siteJS = 'A5ilU4GLyp.min.js'.$SITE->getCacheVer('js');
$jsPreloadDatas = ' rel="preload" as="script" type="text/javascript"';
$stylePreloadDatas = ' rel="preload" as="style" onload="this.rel=\'stylesheet\'" type="text/css"';


##NOTES
/************
use attribute async or defer to load scripts asynchronously
async=>loaded asynchronously and runs immediately after
defer=>loaded asynchronously and runs after document load in the order it occurs

<link rel="preload" as="font" crossorigin="anonymous" href="" type="font/woff2"> 
<meta property="og:locale" content="en-UK" />
<meta property="og:type" content="website" />
<meta property="og:title" content="" />
<meta property="og:site_name" content="" />
<meta property="og:description" content="" />
<meta property="og:url" content="" />
<meta property="og:image" content="" />
<meta property="article:section" content="" />
<meta property="article:published_time" content="" />
<meta property="article:modified_time" content="" />

<meta property="twitter:card" content="" />
<meta property="twitter:title" content="" />
<meta property="twitter:description" content="" />
<meta property="twitter:image" content="" />
<meta property="twitter:url" content="" />


<link rel="preload" as="font" crossorigin="anonymous" href="<?php echo $selfHostedRsrcRoot; ?>styles/main/fonts/fa/fa-solid-900.woff2<?php echo $cacheVer; ?>" type="font/woff2"/>
<link rel="preload" as="font" crossorigin="anonymous" href="<?php echo $selfHostedRsrcRoot; ?>styles/main/fonts/fa/fa-regular-400.woff2<?php echo $cacheVer; ?>" type="font/woff2"/>
<link rel="preload" as="font" crossorigin="anonymous" href="<?php echo $selfHostedRsrcRoot; ?>styles/main/fonts/fa/fa-brand-400.woff2<?php echo $cacheVer; ?>" type="font/woff2"/>


***********/

?>

<meta name="theme-color" content="#fff" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="encoding" content="utf-8" />
<meta charset="utf-8" />
<meta name="keywords" content="<?php if(isset($siteName)) echo $siteName?>, Science and Technology, Entertainment" />
<meta name="description" content="<?php if(isset($siteName)) echo $siteName?> -- Learn, Share and Build Your Career" />
<meta name="author" content="<?php if(isset($siteName)) echo $siteName?> Nigeria" />				
<meta name="viewport" content="<?php if(isset($viewport)) echo $viewport; ?>" />
<?php echo $SITE->getThemeModeProps('meta-tag'); ?>
<link rel="manifest"  href="<?php echo $siteRoot; ?>manifest.json" />
<link rel="shortcut icon" type="image/png" href="<?php echo $SITE->getSiteFavicon(); ?>" />
<?php if($rqstBaseURL == 'tools'){ /******SITE TOOLS******/?>
<link rel="stylesheet" type="text/css" href="<?php echo $selfHostedRsrcRoot; ?>styles/main/css/tools.min.css<?php echo $SITE->getCacheVer('tools-css'); ?>" />
<?php } ?>
<?php if($isDev){ /******JQ******/ ?>
<script type="text/javascript" src="<?php echo $devHostRsrcRoot; ?>jq/jquery-v3.7.1.min.js<?php echo $cacheVer; ?>" ></script>
<?php }else{ ?>
<script type="text/javascript" rel="dns-prefetch" src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js" ></script>
<?php } /******SITE JS******/ ?>
<script type="text/javascript" src="<?php echo $selfHostedRsrcRoot; ?>js/main/<?php echo $siteJS; ?>" ></script>
<?php if($isDev){/******PLUGINS ON DEV******/ ?>
<link rel="stylesheet" type="text/css" href="<?php echo $devHostRsrcRoot; ?>prtfy/css/prtfy.css<?php echo $cacheVer; ?>" />								
<script type="text/javascript" src="<?php echo $devHostRsrcRoot; ?>prtfy/js/prtfy.js<?php echo $cacheVer; ?>" ></script> 
<link rel="stylesheet" type="text/css" href="<?php echo $devHostRsrcRoot; ?>DataTables/datatables.css<?php echo $cacheVer; ?>" />								
<script type="text/javascript" src="<?php echo $devHostRsrcRoot; ?>DataTables/datatables.js<?php echo $cacheVer; ?>" ></script>
<link rel="stylesheet" type="text/css" href="<?php echo $devHostRsrcRoot; ?>OverlayScrollbars/1.13.3/css/OverlayScrollbars.css<?php echo $cacheVer; ?>" />								
<script type="text/javascript" src="<?php echo $devHostRsrcRoot; ?>OverlayScrollbars/1.13.3/js/jquery.overlayScrollbars.js<?php echo $cacheVer; ?>" ></script> 
<script type="text/javascript" src="<?php echo $devHostRsrcRoot; ?>jq/jquery.form-v3.51.0.min.js<?php echo $cacheVer; ?>" ></script>
<?php if(isset($_GET[RSRC_PLUGS['qr']])){ /******Qr Code******/ ?>
<script type="text/javascript" src="<?php echo $devHostRsrcRoot; ?>webrtc-adapter/adapter.min.js<?php echo $cacheVer; ?>"></script>
<script type="text/javascript" src="<?php echo $devHostRsrcRoot; ?>instascan/instascan.min.js<?php echo $cacheVer; ?>"></script>
<?php } ?>
<?php }else{ /******PLUGINS ON PRODUCTION******/?> 
<script type="text/javascript" rel="dns-prefetch" src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js" async="1"></script>
<link rel="stylesheet" type="text/css" href="https://.datatables.net/1.11.4/css/jquery.datatables.css" />
<script type="text/javascript" rel="dns-prefetch" src="https://cdn.datatables.net/1.11.4/js/jquery.datatables.js" async="1"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.13.3/css/OverlayScrollbars.min.css" />
<script type="text/javascript" rel="dns-prefetch" src="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.13.3/js/jquery.overlayScrollbars.min.js" async="1"></script>
<script type="text/javascript" rel="dns-prefetch" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" integrity="sha384-FzT3vTVGXqf7wRfy8k4BiyzvbNfeYjK+frTVqZeNDFl8woCbF0CYG6g2fMEFFo/i" crossorigin="anonymous" ></script>
<?php if(isset($_GET[RSRC_PLUGS['qr']])){ /******Qr Code******/ ?>
<script type="text/javascript" rel="dns-prefetch" src="https://cdnjs.cloudflare.com/ajax/libs/webrtc-adapter/6.1.4/adapter.min.js" ></script>
<script type="text/javascript" rel="dns-prefetch" src="https://cdn.jsdelivr.net/gh/schmich/instascan-builds@master/instascan.min.js" ></script>
<?php } ?>
<?php } ?>
<?php if(isset($_GET["sh-plugin.inc"])){ /******SYNTAX HIGHLIGHTER******/ ?>
<link rel="stylesheet" type="text/css" href="<?php echo $selfHostedRsrcRoot; ?>plugins/sh/css/sh_core/shCore.css" />		
<link rel="stylesheet" type="text/css" href="<?php echo $selfHostedRsrcRoot; ?>plugins/sh/css/sh_core/shCoreDefault.css" />		
<link rel="stylesheet" type="text/css" href="<?php echo $selfHostedRsrcRoot; ?>plugins/sh/css/sh_core/shThemeDefault.css" />				
<script type="text/javascript" src="<?php echo $selfHostedRsrcRoot; ?>plugins/sh/js/sh_m.js" ></script>
<?php } ?>
<?php /******LOAD CSS******/?>
<link rel="stylesheet" href="<?php echo $selfHostedRsrcRoot; ?>styles/main/css/<?php echo $siteCSS; ?>" />

<?php  if(isset($_GET["cdn-misc.inc"])){ ?>
<!-- 
<script   type="text/javascript" src="<?php echo $siteRoot; ?>/jquery.geo.rc1.1.js" ></script> 
<script src="https://cdn.jsdelivr.net/gh/jquery-form/form@4.2.2/dist/jquery.form.min.js" integrity="sha384-FzT3vTVGXqf7wRfy8k4BiyzvbNfeYjK+frTVqZeNDFl8woCbF0CYG6g2fMEFFo/i" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" integrity="sha384-FzT3vTVGXqf7wRfy8k4BiyzvbNfeYjK+frTVqZeNDFl8woCbF0CYG6g2fMEFFo/i" crossorigin="anonymous"></script>
//LOAD PRETTIFY CDN
<script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>

<script>

  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-83881732-1', 'auto');
  ga('send', 'pageview');
  ga('set', 'userId', {{USER_ID}}); // Set the user ID using signed-in user_id.

</script>
-->
<?php } ?>