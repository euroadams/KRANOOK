<?php

require_once ('page-common-headers.php');

//Throw 404 status code (page not found)
http_response_code(404);					


global $SITE, $GLOBAL_mediaRootFav;

$SITE->buildPageHtml(array("pageTitle"=>'Error 404: Page Not Found', 
			"preBodyMetas"=>$SITE->getNavBreadcrumbs('<li><a href="/page-error">Page Error</a></li>'),
			"pageBody"=>'
			<div class="single-base blend-top">	
				<div class="base-ctrl">				
					<h1 class="page-title pan bg-limex">Page Not Found !<br/><span class="red">(ERROR 404)</span></h1>
					<div class="align-l centered-inline">
						<div class="base-pad">
							<div class="align-c"><img class="img-responsive" src="'.$GLOBAL_mediaRootFav.'hi-robot.png" /></div>
							<p>Hi there! We could\'nt find the page you were looking for.</p>
							<p>It might have been removed, had its name changed or is temporarily unavailable.</p>
							
							<h3>Suggestions:</h3>
							<ul class="ul has-circular-bullets">
								<li>Check that the page url is spelled correctly.</li>
								<li>Go to our <a class="links" href="/">Homepage</a> and use the menus or links to navigate to the desired page.</li>
								<li>Use the <a class="links" href="/search">search</a> engine on our  <a class="links" href="/">community</a> to locate the content you are looking for.</li>										
							</ul>
						</div>
					</div>
				</div>
			</div>'
	));

?>