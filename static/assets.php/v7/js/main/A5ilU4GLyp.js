/////JQUERY DOCUMENT READY BEGIN/////



_doc = {
		
	preloaderDpnClass: 'preloader-dpn',
	replyBoxHandleClass: 'reply-box-handle',
	timedVisAttr: 'data-timed-visibility',
	codeResendInterval: 180, //Time Interval in seconds

	ajaxEngine: {

		runner: 'data-run-by-ajax',		
		reloadBox: 'data-ajax-rel-rcv'

	},

	control: {
		
		busyAttr: 'data-control-busy', //used to control another click event from firing while one is already running to prevent loading an appended content twice
		editableAttr: 'contenteditable'

	},


	events: {

		fieldEdit: 'input keyup keypress change paste propertychange'

	},

	buttons: {

		close: '<div title="close" class="red close">&times;</div>'

	},

	elId: {
		
		

	},

	elCls: {

		

	},

	elAttr: {

		remEl: 'data-remove-el',
		remTaggedCls: 'data-remove-tagged-cls',

	}
	
};


$(function(){

	/**BEGIN FRAMEWORK**/
	
	var COUNTER = 0, AUTO_REWRITE_FIRE_TIMER,

	_keyboard = {
		
		esc: {code: 27}
		
	},
	
	


	_docExt = $.extend(true, _doc, {
		
		sessionUser: $("html").attr("data-sess-user"),
		assetPre: $("html").attr("data-asset-pre"),
		baseRdr: $("html").attr("data-base-rdr")
		
	}),


	
	_zoomCtrl = {
		
		data: 'zoomCtrl',
		opener: {
			
			className: 'zoomed-in'

		}

	},

	

	_tab = {
				
		cls: {
			
			contents: '.tab-contents',
			close: '.tab-close-btn',
			collapsed: '.collapsed'

		}

	},
	



	_smartToggler = {
		
		dbName: 'smartToggler',		
		openerAttr: 'data-opener',
		opener: {
			
		}

	},




	_slideShow = {

		defaultSpeed: 4500,
		attr: {
			
			speed: 'data-speed',
			outsetTime: 'data-outset-time',
			autoTrigger: 'data-auto-triggered'

		}


	},

 


	_COMPOSER = $("html").attr("data-composer-id"),
	_ANIMATED = ' animated ', 
	_TIMED_FADE_OUT = ' data-_tfo="true"  ',
	_POST_SUBMIT_BTN_ID = '#postSubmitBtn', 
	_HAS_BBC_CLS = '.has-bbc',
	_HAS_UIBG_CLS = '.has-uibg', 

	uibgMainCls = '_uibg-ui',
	uibgHibernationBtnCls = '_uibg-hibernation-btn',
	
	_uibg = {

		noBgDefaultId: 0, // no background default id
		maxCharLen: 160,		
		fieldCountAttr: 'data-field-count',
		fieldCounterClassName: 'field-counter',
		charExceededAttr: 'data-uibg-char-exceeded',
		charExceededClassName: '_composer-uibg-valid-len-exceeded', //referenced in style sheet (edit accordingly if changed)
		mainClassName: uibgMainCls,
		wrapperClassName: uibgMainCls + ' _uibg-ui-composer-form',
		defaultBgClassName: '_uibg-default', //referenced in style sheet (edit accordingly if changed)
		initGrowthPadClassName: '_init-cgp', //=>initial center growth pad, referenced in style sheet (edit accordingly if changed)
		fillClassName: '_uibg-ui-fill', //referenced in style sheet (edit accordingly if changed)
		hibernationBtnHtml: '<div title="close" class="red close-btn ' + uibgHibernationBtnCls + '">&times;</div>', //referenced in style sheet (edit accordingly if changed)
		hibernationBtnClassName: uibgHibernationBtnCls, //referenced in style sheet (edit accordingly if changed)
		hibernationClassName: '_uibg-hibernated', //referenced in style sheet (edit accordingly if changed)		
		capturedStyleAttr: 'data-uibg-captured-style',
		loaderFieldName: 'uibg-loader',
		loaderAttr: 'data-uibg-loader', //referenced in style sheet (edit accordingly if changed)
		loadedComposerBgAttr: 'data-loaded-composer-uibg',
		highlightOnlyAttr: 'data-highlight-selected-only',
		pager: 'data-uibg-page',

	},



	
	_emoticons = {

		mainClassName: 'emoticons',
		pager: 'data-emoticon-page',
		emojisContainerClassName: '_emojis-ui-container',
		tabId: 'emoticon-tabs',
		dropPaneId: 'emoticonsDropPane',

		tabs: {

			prefetcher: 'data-prefetch-emoticon-tabs',

		}

	},



	
	_mods = {

		cms: {

			base: 'staff-cms',

			//CMS KEYS
			td: 'data-td-cms', //thread delete
			tl: 'data-tl-cms', //thread lock
			tm: 'data-tm-cms', //thread move
			trn: 'data-trn-cms', //thread rename
			tpn: 'data-tpn-cms', //thread pin
			tpr: 'data-tpr-cms', //thread protection
			tft: 'data-tft-cms', //thread feature
			tth: 'data-tth-cms', //thread tag hot
			to: 'data-to-cms', //thread open
			tc: 'data-tc-cms', //thread close
			ph: 'data-ph-cms', //post hide
			pl: 'data-pl-cms', //post lock
			ppn: 'data-ppn-cms', //post pin
			pd: 'data-pd-cms', //post delete

		}

	},




	backOverlayOpenerMainCls = 'has-back-overlay', 
	backOverlayerMainCls = 'back-overlay',

	_backOverlay = {
		
		opener: {
			
			mainClassName: backOverlayOpenerMainCls,
			className: backOverlayOpenerMainCls + ' back-overlay-item-centered content-zoom open-zoom'

		},

		overlayer: {
			
			mainClassName: backOverlayerMainCls,
			overlay: '<div class="' + backOverlayerMainCls + ' back-overlay-bg"></div>'

		}

	};


	
	
	//CALL ALL FUNCTIONS THAT REQUIRE ON LOAD INITIALIZATION
	function AJAX_RECALLS(recallArr){
		
		recallArr = recallArr || ['all'];

		all = inArray('all', recallArr);
		slideshow = inArray('slideshow', recallArr);
		
		//fire auto-rewrite 3s after page load
		if(all || inArray('autoRewrite', recallArr))
			AUTO_REWRITE_FIRE_TIMER = setTimeout(autoRewrite, 3000);
		
		//initialize and cycle slide shows
		if(all || slideshow || inArray('loadSlideshow', recallArr))
			loadSlides();
	
		if(all || slideshow || inArray('cycleSlideshow', recallArr))
			cycleSlides();	
	
		if(all || inArray('customScrollbar', recallArr))
			customScrollbar();	

		
	}AJAX_RECALLS();
	
	
	$('body').addClass('_js_enabled _js-' + (mobile_platform()? 'm' : 'o') + '-platform');
	
	
	$('[data-js-display-mode]').each(function(){ 
	
		var $t = $(this), displayMode = $t.attr('data-js-display-mode');
		
		$t.css('display', displayMode);
	
	});
	
	
	themeToggleBtn = $('[data-theme-mode-toggle]');
	
	
	themeToggleBtn.on('click', function(){toggleThemeMode(false);});
	
	
	function toggleThemeMode(captureSystemMode){ 
	
		var themeContextEle = $('body'), themeModeAttr = 'data-theme-mode', lightMode = 'light', darkMode = 'dark', 
		systemMode = 'system', newThemeMode, appCurrThemeMode = themeMode4Switch = themeContextEle.attr(themeModeAttr) || lightMode, 
		iconEle = themeToggleBtn.children().eq(0), iconCls = iconEle.attr("class"), sun = 'sun', moon = 'moon',
		useSystemDarkMode = false;
		
		//CONSIDER SYSTEM DARK MODE PREFERENCE WHEN USER IS'NT SPECIFIC
		if(captureSystemMode)
			themeMode4Switch = systemMode;
		
		switch(themeMode4Switch.toLowerCase()){
			
			case systemMode: useSystemDarkMode = (matchMedia("(prefers-color-scheme: "+ darkMode +")").matches || appCurrThemeMode == darkMode);
				newThemeMode = useSystemDarkMode? darkMode : lightMode; 
				iconCls = useSystemDarkMode? iconCls.replace(moon, sun) : iconCls.replace(sun, moon);
				break;
			
			case darkMode: newThemeMode = lightMode; iconCls = iconCls.replace(sun, moon); break;
			
			default: newThemeMode = darkMode; iconCls = iconCls.replace(moon, sun);
			
		}
		
		$('#theme-mode-scheme').attr("content", newThemeMode);
		themeToggleBtn.children().attr("class", iconCls);
		themeContextEle.attr(themeModeAttr, newThemeMode);
		themeToggleBtn.attr('title', themeToggleBtn.attr('title').replace(newThemeMode, appCurrThemeMode.toLowerCase()));
		
		$.ajax({
			
			url: '/ajaxion/update-user-preferred-theme-mode',
			method: 'post',
			data: {themeMode: newThemeMode},
			success: function(res){
				
			}
			
		});
		
	
	}
	
	//Load OS Preferred Mode
	//toggleThemeMode(true); //uncomment to allow App follow operating system color scheme
	

	//Sess Based Controller Event Listener
	$("[data-sess-ctrl]").on("click", function(){
		
		$.ajax({
			
			url: '/ajaxion/' + $(this).attr("data-url"),
			method: 'post',
			data: {state: true},
			success: function(res){
				
			},
			error:function(e){
				//alert(e.responseText)
			}
		});	

	})
	

	function mobile_platform(){
	
		return (screen.availWidth < 610);
	
	}
	
	function _check_for_sun($t, user, msg, msgCss){
	
		msg = msg || 'You can`t do that!';
		msgCss = msgCss || 'text-danger';
		console.log(_doc)
	
		if(_doc.sessionUser.toLowerCase() == user.toLowerCase()){
	
			$t.next().hasClass("_sun")? $t.next().show() : $t.after('<span class="_sun ' + msgCss + '" ' + _TIMED_FADE_OUT +'> ' + msg + ' </span>');
			return true;
	
		}
	
		return false;
	}
	
	$(document).on("click", "._js-ignanchor", function(){return false;});
	$("[data-npips]").on("contextmenu", function(){return false;});
	

	//navigate to link via js
	$(document).on("click", "[data-navigate]", function(){
		
		location.assign($(this).attr("data-navigate"));
	
	});
	
	//////SCROLL TO PAGE TOP AND BOTTOM WITH ANIMATION///////

	$(".topageup,.topagedown").click(function(){
	
		var dwn = $(this).hasClass("topagedown");
		$("html, body").animate({scrollTop:(dwn? $("#doc-end").eOffset(false).top : '0')}, (dwn? 1500 : 1000));		
		
		return false; //PREVENT DEFAULT	
			
	});
	
	/////FADE IN/OUT PAGE UP/DOWN BTN//////
	$(window).scroll(function(){
		
		$(".midpage-scroll-base")['fade' + ($(window).scrollTop() > 800? 'In' : 'Out')]("slow");	
	
	});
	
	
	/////Initialize overlayScrollbars plugin//////
	function customScrollbar(){

		$("[" + (customScrollbarAttr = 'data-has-custom-scrollbar') + "]").each(function(e){
			
			var $t = $(this);
			var options = JSON.parse($t.attr(customScrollbarAttr));
			//options = Object.assign({}, JSON.parse($t.attr(customScrollbarAttr)));console.log(options)
			$t.overlayScrollbars(options);
			
		});

	}customScrollbar();
	
	/////STICKY NAV//////			
	/*$(window).scroll(function(){	
			
		if($(window).scrollTop() > 400){
				
			$(".sticky-nav").fadeIn("slow");
	
		}else if($(window).scrollTop() < 400) {
				
			$(".sticky-nav").fadeOut("slow");	
			
		}
			
	});*/
	
	var lastScroll = 0, stickyNav = $('.sticky-nav'), stickyOffset = 280;
	
	$(window).scroll(function(){
	
		var currentScroll = $(this).scrollTop();
	
		if(currentScroll > lastScroll  || currentScroll <= stickyOffset){	
			
			stickyNav.fadeOut(1);
	
		}else if(currentScroll > stickyOffset){			
				
			stickyNav.fadeIn(1);			
				
		}		
		
		lastScroll = currentScroll;		
		
	});
	
	
	/////ELASTIC FIELDS//////	
	$(".field-elastic").on("focus", function(e){		
		
		var K = '.has-field-elastic';
		$(K).addClass("elongated");		
		
		$(document).on("click", function(e){		
		
			if(!$(e.target).closest(K).length)
				$(K).removeClass("elongated");		
		
		});		
		
	});
	
	
	/////CLASSIC FIELDS//////	
	var  classicFieldTgt = $(".form-ui-basic .field"), classicFieldCls = 'form-ui-basic-field-focused';		
		
	//ADD CLASSIC CLASS ONLY IF THE FIELD IS EMPTY AND NOT FOCUSED
	classicFieldTgt.filter(function(){return (!this.value && !$(this).filter(':focus').length);}).parent().addClass(classicFieldCls);		
		
	$('.' + classicFieldCls + ' label').on('click', function(e){		
		
		$(this).siblings('.field').focus();		
		
	});		
		
	classicFieldTgt.on("focus blur", function(e){		
		
		var $t = $(this), eType = e.type, isFocus = (eType.toLowerCase() == 'focus');		
		
		if(!isFocus && $t.val())
			return;		
		
		$t.closest('.field-ctrl')[(isFocus? 'remove' : 'add') + 'Class'](classicFieldCls);		
		
	});
	
	/*
	//DIGIT COUNTER REFRESH
	$("[data-digit-counter-refresh]").each(function(){		
		
		var $t=$(this),n;
		n = parseInt($t.text());
		
		
	});*/

	
	$("[data-tables-ctrl]").each(function(){

		$(this).DataTable();

	})
	
	
	
	//MAKE ALL TABLE DATAS DISPLAY THEIR CORRESPONDING TABLE HEAD TITLE WHEN HOVERED
	$("[data-td-titler] td").each(function(){		
		
		var $t=$(this);
		$t.attr('title', $t.closest('table').find('th').eq($t.index()).text());		
		
	});



	//Bind Printing Events
	var printData = ' data-print-content="true"';

	$("[data-content-printable]").after('<button class="btn btn-primary btn-sm" ' + printData + '>PRINT</button>');

	$("[" + printData + "]").on("click", function(){

		printDivContent($(this).prev());

	})


	
	//MAKE ALL TABLE DATAS DISPLAY THEIR CORRESPONDING TABLE HEAD TITLE WHEN HOVERED
	$(document).on('scroll', function(){		
		
		$('.table-head-sticky').each(function(){		
		
			var $t=$(this), parentTable = $t.closest("table"), scrollTop = $(window).scrollTop();		
		
			if((scrollTop > $t.eOffset(false).top) && (scrollTop < ((parentTable.outerHeight() + parentTable.eOffset(false).top) - (screen.availHeight / 2))))
				$t.css({'position': 'fixed', 'top': '0'});		
		
			else
				$t.css({'position': 'static'});		
		
		});		
		
	});
	
	/////AUTO WRITE///////
	$('[data-auto-rewrite]').hide();//hide auto-rewrite contents by default			
		
	function autoRewrite(){		
		
		var K, randMin = 100, randMax = 5000;
		
		$(K='[data-auto-rewrite]').each(function(){				
		
			var $t,txtArr,txtArrLen,txtArrEnd,txt,txtIndex=0,i,s,once,screen,autoRDatas,autoWDatas, 
			autoCursor = 'autoRewriteCursor', htmlCursor = '<small class="' + autoCursor + '">_</small>';
		
			$t = $(this);
			txtArr = $t.text().split("|");
			txtArrLen = txtArr.length;
			txt = txtArr[txtIndex];
			txtArrEnd = ((txtIndex + 1) >= txtArrLen);
			i = $t.data("index") || 0;
			s = $t.data("speed") || 100;		
			once = $t.data("rewrite-once") || false;		
			screen = (K=$t.attr("data-screen"))? $("#" + K) : $t;
			screen.empty().show();		 
			
			$t.data(K='autoRewriteMetas', {id: COUNTER++});		
				
			var autoWrite = function($t, txt, i, s){			
		
				autoRDatas = $t.data(K);
				clearTimeout(autoRDatas.id); //clear old timer
				screen.children('.' + autoCursor).remove();		
		
				if(i < txt.length){			
					
					screen.append(txt[i++] + htmlCursor);		
		
					autoRDatas.id = setTimeout(function(){		
				
						autoWrite($t, txt, i, s);			
			
					}, s + 30);		
		
				}else{		
		
					if(once && txtArrEnd) return true;		
		
					autoRDatas.id = setTimeout(function(){		
		
						i=0;
						autoWipe($t, screen.text(), i, s);		
		
					}, jsRand(s + randMin, randMax));
									
				}		
				
			}
			
			$t.data(K='autoWipeMetas', {id: COUNTER});			
		
			var autoWipe = function($t, txt, i, s){		
		
				autoWDatas = $t.data(K);
				screen.children('.' + autoCursor).remove();
				var tmpTxt = screen.text();
				var index = tmpTxt.length - 1;
				clearTimeout(autoWDatas.id); //clear old timer		
		
				if(index >= 0){		
		
					screen.html(tmpTxt.substr(0, index) + htmlCursor);	
					autoWDatas.id = setTimeout(function(){		
								
						autoWipe($t, txt, i, s);				
		
					}, s - 40);		
		
				}else{		
		
					txtIndex++;
					txtArrEnd = (txtIndex >= txtArrLen);
					txtIndex = txtArrEnd? 0 : txtIndex;
					txt = txtArr[txtIndex];		
		
					if(once && txtArrEnd) 		
						return true;		
		
					autoWDatas.id = setTimeout(function(){		
		
						i=0;
						autoWrite($t, txt, i, s);		
		
					}, jsRand(s + randMin, randMax));		
		
				}
			}		
		
			autoWrite($t, txt, i, s);
			clearTimeout(AUTO_REWRITE_FIRE_TIMER);		
		
		});		
	};	
	
	
	//IMG FILE PREVIEW
	$(document).on("change", '[data-imgfp]', function(e){		
		
		var $t = $(this), cls='imgfp', files = e.target.files, fileLen = files.length, url, screen, taggedCls, tagCls='imgfp-'+ COUNTER++;
		
		screen = $t.attr('data-screen') ||  '';
		taggedCls = $t.attr(_doc.elAttr.remTaggedCls) ||  '';		
		
		if(taggedCls)
			$('.'+ taggedCls).remove();
		
		if(!screen){		
		
			$t.after('<div class="'+ cls +'"></div>');
			screen = $t.next();		
		
		}else
			screen = $(screen).addClass(cls);
			
		for(var i = 0; i < (fileLen); i++){		
		
			url = URL.createObjectURL(files[i]);		
		
			imgSrcValid(url, function(o){		
		
				if(o.stat == 'ok'){		
		
					screen.append('<img src="'+ o.url +'" alt="image preview" class="'+ tagCls +'"/>');
					$t.attr(_doc.elAttr.remTaggedCls, tagCls);		
		
				}		
		
			});		
		
		}	
		
		$t.val([files.pop()])
		URL.revokeObjectURL;	
		
	})
	
	
	//HIDE CENTER ALERT BOX (CAB)///
	$("#cab").on("click", function(){	
		
			$(this).hide();		
		
	});
	
		
	/////UNLOCK DB-MANAGER EDIT/DEL BY ADMIN//////
	$(".live-unlock").on("click", function(){
		
		var $t = $(this);
		var action = $t.data("action");		
		var id = $t.data("id") || '';				
		var d_url = $t.data("url");				
		var $this = $t;					
		
		$.ajax({	
								
			url:d_url,
			method:"post",
			dataType:"json",
			data:{id:id, action:action, admin_unlocks:true},
			success:function(data){	
					
				//$this.parent().prepend(data.result);
				$('#cab').html(data.result).show();
		
				if(!data.error)
					location.reload();
		
			}
			
		});	
		
		return false; //PREVENT DEFAULT
		
	});
	
		
	/////LIVE EDIT//////
	$(".live-edit").on("blur", function(){
		
		var $t = $(this), col, val, id, table, d_url;
		col = $t.data("name");		
		//val = $t.text().trim();		
		val = $t.text().trim();		
		id = $t.data("id");				
		table = $t.data("table");				
		d_url = $t.data("url");										
		
		$.ajax({	
								
			url:d_url,
			method:"post",
			dataType:"json",
			data:{id:id, value:val, name:col, table:table, admin_edit:true},
			success:function(data){
		
				//$t.after(data.result);
				$('#cab').html(data.result).show();
				$t.closest('.modal-drop').hide();
				//location.reload();
		
			},error: function(e){
				//alert(e.responseText)
			}
			
		});							
		
	});
	
		
	/////LIVE ADD//////
	var liveAddFieldClr = 'live-add-fc';
		
	$(".live-add,." + liveAddFieldClr).on("click", function(){
		
		var $t = $(this), adding, table, d_url;
		var dataObj = {};
		adding = $t.closest(".live-add-ref").siblings("[data-adding]");
		
		if($t.hasClass(liveAddFieldClr)){
		
			adding.text('');
			$t.closest('.modal-drop').hide();
			
			return false;
		
		}

		
		adding.each(function(){	
				
			dataObj[($(this).data("name"))] = ($(this).text().trim());
					
		});
		
		table = $t.data("table");				
		d_url = $t.data("url");						
		dataObj["admin_add"] = true;
		dataObj["table"] = table;
				
		$.ajax({
									
			url:d_url,
			method:"post",
			dataType:"json",
			data:dataObj,
			success:function(data){	
				
				//$t.parent().prepend(data.result);
				$('#cab').html(data.result).show();
				$t.closest('.modal-drop').hide();
				$t.parent().next().next().children("." + liveAddFieldClr).trigger("click"); //reset add fields
				/*if(!data.error)
					location.reload();
				*/
			}
			
		});
		
		return false; //PREVENT DEFAULT
		
	});
	
	
	/////LIVE DELETE//////
	$(".live-delete").on("click", function(){
		
		var $t = $(this), id, table, d_url;
		id = $t.data("id");				
		table = $t.data("table");				
		d_url = $t.data("url");				
		
		$.ajax({	
								
			url:d_url,
			method:"post",
			dataType:"json",
			data:{id:id, table:table, admin_delete:true},
			success:function(data){	
				
				//$t.parent().prepend(data.result);
				$('#cab').html(data.result).show();
		
				if(!data.error)
					location.reload();
		
			}
			
		});	
								
		return false; //PREVENT DEFAULT
		
	});
	
	
	////RIGHT ARROW POINTER AUTO ADD/////
	$(".has-ra-carets li,.has-ra-caret").prepend('<span class="rarr"></span>');
		
	////CIRCULAR BULLETS///
	$(".has-circular-bullets > *,.has-circular-bullet").prepend('<span class="circular-bullet"></span>');

		
	//////SHOW ALL PASSWORD FIELDS///////////
	var pwdToggleTgt = 'data-toggle-password-plain-target';
		
	$('['+ pwdToggleTgt +']').on("click",function(){
		
		var tgt,$t,K;
		$t = $(this);
		tgt = '.' + $t.attr(pwdToggleTgt);
		$(tgt).attr("type", ($(tgt).attr("type").toLowerCase() == "password")? 'text' : 'password');
		$t.toggleClass("fa-eye fa-eye-slash");
		
	});

		
	////COPY TO CLIPBOARD////
	var clipBoardCopy = 'data-clipboard-copy',hasClipBoardCopy = 'data-has-clipboard-copy',
	clipBoardCopyBtnTxt = 'data-clipboard-copy-btn-text',defaultClipBoardCopyBtnTxt = 'Copy';
		
	$("["+ hasClipBoardCopy +"]").each(function(){
		
		var $t=$(this),K;
		$t.after('<a role="button" class="btn" '+ clipBoardCopy +'="true">'+((K=$t.attr(clipBoardCopyBtnTxt))? K : defaultClipBoardCopyBtnTxt)+'</a>');
		
	});
		
	$(document).on('click', '['+ clipBoardCopy +']', function(){
		
		var $t=$(this),K,tgt,tmpId='_clipboard_copy_field';
		tgt = (K=$t.attr("data-clipboard-copy-target"))? $('#' + K) : $t.prev();
		$("body").append('<textarea readonly="readonly" id="' + tmpId + '" style="position: absolute; left: -99999px;">' + tgt.text() + '</textarea>');
		tgt = $("#" + tmpId);
		
		// Check if there is any content selected previously and Store selection if found
		var selected = document.getSelection().rangeCount > 0 ? document.getSelection().getRangeAt(0) : false; 
		tgt.select();
		document.execCommand("copy");
		tgt.remove();
		
		// If a selection existed before copying, deselect everything on the HTML document and restore the original selection
		if(selected){  
		                               
			document.getSelection().removeAllRanges();  
			document.getSelection().addRange(selected);
		 
		}
		
		$t.text('Copied!');
		setTimeout(function(){
		
			$t.text(((K=$t.prev('['+ hasClipBoardCopy +']').attr(clipBoardCopyBtnTxt))? K : defaultClipBoardCopyBtnTxt))
		
		}, 3000);
		
		//$t.after('<span class="" data-click-to-hide="true" ' + _TIMED_FADE_OUT +'>Copied!</span>');
		
	});
	
	
	
	////DISPLAY VALUES OF FIELD AS TOOLTIPS////
	$("[data-field-tip],[data-form-field-tip] .field").on("mouseenter mouseleave keyup", function(e){
	
		var $t = $(this),tipBase='has-tooltip',tip='tooltip',tgtTip=$t.next('.'+tip),tipPos,v,eDisp;
		
		tipPos = 'tooltip-'+($t.attr("data-tip-pos") || $t.closest("[data-form-field-tip]").attr("data-tip-pos") || 'up');
		v =  $('#'+$t.attr("data-tip-loader")).html() || $t.val() || $t.html();
		eDisp =  $t.css('display').toLowerCase();
		
		if(!v ||  e.type === 'mouseleave'){
	
			tgtTip.removeClass('active');
			if(!v || $t.attr("data-tip-loader")) removeTip($t);
			return true;
	
		} 
		
		if($t.next().hasClass(tip))
			tgtTip.html(v);
	
		else{
	
			$t.wrap('<div class="'+tipBase+' '+tipPos+'"></div>');
			$t.after('<span class="'+tip+'">' + v + '</span>');
			$t.next('.'+tip).addClass('active');
			$t.parent('.'+tipBase).css({'display': ($t.hasClass('field')? '' : 'inline-')+'block'});
	
		}
	
		$t.focus();
		toolTipMisc($t);
	
	});
	
	function removeTip($t){
	
		if(!$t.parent('.has-tooltip').length) return true;
		$t.next(".tooltip").remove();
		$t.unwrap();
	
	}
	
	function toolTipMisc($t){
	
		var tgtTip=$t.next('.tooltip'), tipUp='tooltip-up', tipDown='tooltip-down', 
		tipR='tooltip-right', tipL='tooltip-left',w,offset,isTipUp,isTipDown,iniOffset,oh,ohstp; 
		
		w = parseInt($t.attr("data-tip-w")) || parseInt($t.parent('.has-tooltip').css('width')) / 1.7;
		offset = parseInt($t.attr("data-tip-xoffset"));
		tgtTip.css({'width': w+'px', 'marginLeft': -(w / 2)+'px'});
		
		ohstp = tgtTip.eOffset().top;
		oh = tgtTip.outerHeight();
	
		if((ohstp - (screen.height - screen.availHeight)) < oh)
			tgtTip.parent('.'+tipUp).removeClass(tipUp).addClass(tipDown);
	
		else
			tgtTip.parent('.'+tipDown).removeClass(tipDown).addClass(tipUp);
		
		isTipUp = $t.closest('.'+tipUp).length;
		isTipDown = $t.closest('.'+tipDown).length;
		iniOffset = parseInt(tgtTip.css(isTipUp? 'bottom' : 'top'));
		(offset && (isTipUp || isTipDown))? tgtTip.css((isTipUp? 'bottom' : 'top'), (isTipUp? iniOffset+offset : iniOffset+offset)) : '';
		//tgtTip.parent('.'+tipR).removeClass(tipR).addClass(tipL);
		//tgtTip.parent('.'+tipL).removeClass(tipL).addClass(tipR);
		
	}
		
		
	//FOCUS//
	$.fn.extend({
	
		scrollElemIntoView: function(context, duration){
	
			var $t = $(this);
			duration = duration || 2  // duration in seconds 
			context = $(context? context : window);
			
			scrollTo(context[0], getRelativePos($t[0]).top , duration);
			
			return true;
	
		}
	
	});	
	

	function getRelativePos(e){
	
		var pPos = e.parentNode.getBoundingClientRect(), // parent pos
		cPos = e.getBoundingClientRect(), // target pos
		pos = {};
  
		pos.top = cPos.top - pPos.top + e.parentNode.scrollTop,
		pos.right = cPos.right - pPos.right,
		pos.bottom = cPos.bottom - pPos.bottom,
		pos.left = cPos.left - pPos.left;
  
		return pos;
	  
	}

		
	function getRelativePosXXX(e, context){
		
		var contextEle = context? $(e).closest(((context.substr(0, 1) == "#")? '' : '.') + context) : context;
		e = toJsElement(e);
		contextEle = toJsElement(contextEle);
		contextEle = contextEle || document.body;
		var pPos = contextEle.getBoundingClientRect(), // context or parent pos
		//var pPos = e.parentNode.getBoundingClientRect(), // parent pos
		tPos = e.getBoundingClientRect(), // target pos
		pos = {};
		console.log("Main_ctx: " + context.classList + " ctx: " + contextEle.classList)
		//console.log('targetR: ' + tPos.right + ' CtxtR: ' + pPos.right)
		pos.top = tPos.top - pPos.top + e.parentNode.scrollTop,
		pos.right = pPos.right - tPos.right /*tPos.right - pPos.right,*/,
		pos.bottom = tPos.bottom - pPos.bottom,
		pos.left = tPos.left - pPos.left;

		return pos;
	
	}



	function findPos(e, isContext){

		//isContext? $((((e.substr(0, 1) == "#")? '' : '.') + e)) : e;
		
		var $e = e, pos = {};
		pos.left = pos.right = pos.top  = pos.bottom =  0;
		
		e = toJsElement(e);

		if(e.offsetParent){

			do{

				pos.left += e.offsetLeft;
				pos.top += e.offsetTop;

			}while(e = e.offetParent);

			pos.right = pos.left + $e.width();
			pos.bottom = pos.top + $e.height();
			
			//pos.right = $(window).width() - $e.width() - pos.left;
			//pos.bottom = $(window).height() - $e.height() - pos.top;

		}

		return pos;

	}


	function getMaxZindex(stackContext){

		stackContext = stackContext || 'body';
		//console.log(stackContext.attr('class'))
		return Math.max.apply(null, $.map($(stackContext).find('*'), function(e, n){

			if($(e).css('position') != 'static')
				return parseInt($(e).css('z-index')) || 1;

		}));

	}


	function incrementZindex(stackContext, step){

		step = step || 1;
		return (step + getMaxZindex(stackContext));

	}



		
		
	function scrollTo(element, to, duration, onDone){
	
		var start = element.scrollTop,
			change = to - start,
			startTime = performance.now(),
			val, now, elapsed, t;

		function animateScroll(){
		
			now = performance.now();
			elapsed = (now - startTime)/1000;
			t = (elapsed/duration);

			element.scrollTop = start + change * easeInOutQuad(t);

			if( t < 1 )
				window.requestAnimationFrame(animateScroll);
		
			else
				onDone && onDone();
		
		};

		animateScroll();
		
	}

		
	function easeInOutQuad(t){ return t<.5 ? 2*t*t : -1+(4-2*t)*t };

			
	//FIND EL OFFSET//
	$.fn.extend({
		
		eOffset: function(refViewPort, retInt, context){
		
			var $t = $(this), tmp,e,box,tp,btm,left,right,tmpCls='tmp-eOffset';
		
			if(refViewPort === undefined)
				refViewPort = true;
		
			if(retInt === undefined)
				retInt = true;
		
			$t.before('<div class="' + tmpCls + '"></div>')
			tmp = $t.prev('.' + tmpCls);
			tmp.css("display","block");
			e = document.documentElement;
			box = tmp.get(0).getBoundingClientRect();
			tmp.remove();
			
			if(refViewPort){
		
				tp = box.top;
				btm = box.bottom;
				left = box.left;
				right = box.right;
		
			}else{
		
				tp = box.top + window.pageYOffset - e.clientTop;
				btm = box.bottom + window.pageYOffset - e.clientBottom;
				left = box.left + window.pageXOffset - e.clientLeft;
				right = box.right + window.pageXOffset - e.clientRight;
		
			}
			
			if(context){
		
				var contextBox = context.get(0).getBoundingClientRect();
				tp = tp - contextBox.top;
				btm = btm - contextBox.bottom;
				left = left - contextBox.left;
				right = right - contextBox.right;
		
			}
			
			if(retInt){
		
				tp = parseInt(tp);
				btm = parseInt(btm);
				left = parseInt(left);
				right = parseInt(right);
		
			}
			
			return {top: tp, bottom: btm, left: left, right: right};
		
		}
		
	});
	
	
	
	//AUTO ADJUST EL POSITION//
	$.fn.extend({
		
		autoPosition: function(useScroll, context, tmg){
		
			return this.each(function(){
		
				var $t = $(this), oh,ow,tp,lf;
		
				if(useScroll === undefined)
					useScroll = false;
		
				if(tmg === undefined || !tmg)
					tmg = 20;//tolerance margin
		
				context = $(context? context : window);
				ow = parseInt($t.outerWidth());
				oh = parseInt($t.outerHeight());
				tp = $t.eOffset().top;
				lf = $t.eOffset().left;	
				tp = (tp >= oh  && (tp - tmg) > oh)? (tp - oh - tmg) : tp;
				lf = ((lf + ow) <= lf)? lf : (lf + ow - tmg);
		
				if(useScroll){
		
					var dvah,dvh,ohstp,doh,dvuh,wst,botMg,availScrn,newScroll;
					botMg = 80;//tolerance margin from bottom
					dvh = screen.height;//dvh => device height 
					dvah = screen.availHeight;//dvah => device available height
					dvuh = dvh - dvah;//dvuh => device unavail height
					ohstp = $t.eOffset().top;//ohstp => obj height from screen top
					doh = $t.eOffset(false).top;//doh => obj height from doc top 
					wst = context.scrollTop();
					availScrn = (dvah - dvuh - botMg);//obj position difference from screen bottom
					newScroll = (oh > availScrn)? (doh - botMg) : (wst + Math.abs((ohstp + oh) - availScrn));
					((ohstp + oh + dvuh + botMg) >= dvah)? context.scrollTop(newScroll) : '';
		
				}else
					$t.css({"top": tp + "px", "left": lf + "px"});
				
			})
		
		}
		
	});
	
	
	
	
	
	/*!
	 * hoverIntent v1.9.0 // 2017.09.01 // jQuery v1.7.0+
	 * http://briancherne.github.io/jquery-hoverIntent/
	 *
	 * You may use hoverIntent under the terms of the MIT license. Basically that
	 * means you are free to use hoverIntent as long as this header is left intact.
	 * Copyright 2007-2017 Brian Cherne
	 */
	!function(factory){"use strict";"function"==typeof define&&define.amd?define(["jquery"],factory):jQuery&&!jQuery.fn.hoverIntent&&factory(jQuery)}(function($){"use strict";var cX,cY,_cfg={interval:100,sensitivity:6,timeout:0},INSTANCE_COUNT=0,track=function(ev){cX=ev.pageX,cY=ev.pageY},compare=function(ev,$el,s,cfg){if(Math.sqrt((s.pX-cX)*(s.pX-cX)+(s.pY-cY)*(s.pY-cY))<cfg.sensitivity)return $el.off(s.event,track),delete s.timeoutId,s.isActive=!0,ev.pageX=cX,ev.pageY=cY,delete s.pX,delete s.pY,cfg.over.apply($el[0],[ev]);s.pX=cX,s.pY=cY,s.timeoutId=setTimeout(function(){compare(ev,$el,s,cfg)},cfg.interval)},delay=function(ev,$el,s,out){return delete $el.data("hoverIntent")[s.id],out.apply($el[0],[ev])};$.fn.hoverIntent=function(handlerIn,handlerOut,selector){var instanceId=INSTANCE_COUNT++,cfg=$.extend({},_cfg);$.isPlainObject(handlerIn)?(cfg=$.extend(cfg,handlerIn),$.isFunction(cfg.out)||(cfg.out=cfg.over)):cfg=$.isFunction(handlerOut)?$.extend(cfg,{over:handlerIn,out:handlerOut,selector:selector}):$.extend(cfg,{over:handlerIn,out:handlerIn,selector:handlerOut});var handleHover=function(e){var ev=$.extend({},e),$el=$(this),hoverIntentData=$el.data("hoverIntent");hoverIntentData||$el.data("hoverIntent",hoverIntentData={});var state=hoverIntentData[instanceId];state||(hoverIntentData[instanceId]=state={id:instanceId}),state.timeoutId&&(state.timeoutId=clearTimeout(state.timeoutId));var mousemove=state.event="mousemove.hoverIntent.hoverIntent"+instanceId;if("mouseenter"===e.type){if(state.isActive)return;state.pX=ev.pageX,state.pY=ev.pageY,$el.off(mousemove,track).on(mousemove,track),state.timeoutId=setTimeout(function(){compare(ev,$el,state,cfg)},cfg.interval)}else{if(!state.isActive)return;$el.off(mousemove,track),state.timeoutId=setTimeout(function(){delay(ev,$el,state,cfg.out)},cfg.timeout)}};return this.on({"mouseenter.hoverIntent":handleHover,"mouseleave.hoverIntent":handleHover},cfg.selector)}});
	
	
	////INSERT AT A CARET///////
	$.fn.extend({
		
		insertAtCaret: function(myValue){
		
		  return this.each(function(i){
		
			if(document.selection){
		
			  //For browsers like Internet Explorer
			  this.focus();
			  var sel = document.selection.createRange();
			  sel.text = myValue;
			  this.focus();
			  
			}else if('selectionStart' in this){
			  
			  //For browsers like Firefox and Webkit based
			  
			  var startPos = this.selectionStart;
			  var endPos = this.selectionEnd;
			  var scrollTop = this.scrollTop;
			  this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
			  this.focus();
			  this.selectionStart = startPos + myValue.length;
			  this.selectionEnd = startPos + myValue.length;
			  this.scrollTop = scrollTop;
			  
			}else{
			  
			  this.value += myValue;
			  this.focus();
			  
			}
			  
		  });
			  
		}
			  
	});
	
			  
	//SELECTING RANGE
	$.fn.selectRange = function(start, end){
			  
		if(end === undefined){
			  
			end = start;
			  
		}
			  
		return this.each(function(){
			  
			if('selectionStart' in this){
			  
				this.selectionStart = start;
				this.selectionEnd = end;
			  
			} else if(this.setSelectionRange){
			  
				this.setSelectionRange(start, end);
			  
			}else if(this.createTextRange){
			  
				var range = this.createTextRange();
				range.collapse(true);
				range.moveEnd('character', end);
				range.moveStart('character', start);
				range.select();
			  
			}
			  
		});
			  
	};
	
			  
	//GET SELECT START/END
	function getInputSelection(el){
			  
		var start = 0, end = 0, normalizedValue, range,
			textInputRange, len, endRange;

		if(typeof el.selectionStart == "number" && typeof el.selectionEnd == "number"){
			  
			start = el.selectionStart;
			end = el.selectionEnd;
			  
		}else{
			  
			range = document.selection.createRange();

			if(range && range.parentElement() == el){
			  
				len = el.value.length;
				normalizedValue = el.value.replace(/\r\n/g, "\n");

				// Create a working TextRange that lives only in the input
				textInputRange = el.createTextRange();
				textInputRange.moveToBookmark(range.getBookmark());

				// Check if the start and end of the selection are at the very end
				// of the input, since moveStart/moveEnd doesn't return what we want
				// in those cases
				endRange = el.createTextRange();
				endRange.collapse(false);

				if(textInputRange.compareEndPoints("StartToEnd", endRange) > -1){

					start = end = len;

				}else{

					start = -textInputRange.moveStart("character", -len);
					start += normalizedValue.slice(0, start).split("\n").length - 1;

					if(textInputRange.compareEndPoints("EndToEnd", endRange) > -1){

						end = len;

					}else{

						end = -textInputRange.moveEnd("character", -len);
						end += normalizedValue.slice(0, end).split("\n").length - 1;

					}
				}
			}
		}

		return {

			start: start,
			end: end

		};

	}
	
	
	////COUNT FIELD VALUES//////
	$(document).on(_doc.events.fieldEdit, "[data-field-count]", function(e){
	
		var $t = $(this), max, min, c, cval, $val, label, K, labelCountCls = _uibg.fieldCounterClassName,
		char2GoLabel = 'Characters to go: ', charLeftLabel = 'Characters left: ', charEnteredLabel = 'Characters entered: ';
			
		max = (K = $t.attr("maxLength"))? parseInt(K) : '';		
		min = (K = $t.attr("data-minLength"))? parseInt(K) : '';
		$val = $t[($t.attr(_doc.control.editableAttr)? 'html' : 'val')]();
		cval =  parseInt($val.length);
		
		if(!$t.attr(K="data-field-count-init")){
	
			if(min){ 
	
				c = (cval > min)? 0 : (min - cval); label = char2GoLabel;
	
			}else if(max){ 
			
				c = (cval > max)? 0 : (max - cval); label = charLeftLabel;
			
			}else if(!min && !max){
			
				c = cval; label = charEnteredLabel;
			
			}
			
			$t.attr(K, true).before('<span class="'+ labelCountCls +'"><b class="prime">'+ label +' </b><b class="">' + c + '</b></span>');
			
		}			
	
		if(min){ 
			 
			c = ((K=(min - cval)) > 0)? K : (((K=(max - cval)) > 0)? K : cval);
			label = ((min - cval) > 0)? char2GoLabel : (((max - cval) > 0)? charLeftLabel : charEnteredLabel);
			$t.prev('.'+ labelCountCls).show().children().eq(0).text(label);
			
		}else if(max){
			
			c = (cval > max)? 0 : (max - cval); 
			if(cval > max) $t[($t.attr(editableAttr)? 'html' : 'val')]($val.substring(0, max));
			
		}else 
			c = cval;
			
		$t.prev('.'+ labelCountCls).show().children().eq(1).text(c);
		
	});
	
	
	/////FUNCTION TO CLEAR INPUT FIELD//////
	var fieldClr = 'field-clr';
	$(".has-field-clr").after('<a href="#" class="'+ fieldClr +'" title="clear field">&times;</a>');
			
	$(document).on("click", '.'+ fieldClr, function(){
			
		$(this).prev().val("");
		return false; //PREVENT DEFAULT
			
	});
	
			
	//////TOGGLE VISIBILITY OF THE CLEAR ICON ON CERTAIN EVENTS///////
	$('.'+ fieldClr).prev().on("focus blur " + _doc.events.fieldEdit, function(e){
			
		var $t=$(this);
		$t.next().css("opacity", ((!$t.val() || e.type === 'blur')? "0" : "1"));
			
	}); 


	
	
	/////FUNCTIONS TO HANDLE FORM FIELD VALIDATION//////////
		
	$('[data-field-validation] [data-validation-name]').on("keyup", function(){
			
		var $t = $(this),succ = 'field-success',err = 'field-error',labelAuth = 'field-auth',
		label,checked,notChecked,field_name,field_type,found,twin;
				
		label = $t.parent().hasClass('has-tooltip')? $t.parent().prev('label') : $t.prev('label');
		checked = ' <i class="green ' + labelAuth + ' far fa-check-circle"></i>';
		notChecked = ' <i class="red ' + labelAuth + ' far fa-times-circle"></i>';
		field_name = $t.attr("name").toLowerCase();
		field_val = $t.val();
		field_type = $t.attr("data-validation-name").toLowerCase();		
		found = false;
		label.children('.' + labelAuth).remove();			
		pwdTest = /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^a-zA-Z0-9])\S{6,50}$/.test(field_val);				
		
		switch(field_type){
			
			case "email":
			
				found = /^[a-z0-9-_\.]+\@[a-z0-9-_]+\.[a-z]{1,}$/i.test(field_val);
			
				if(!found){
										
					$t.removeClass(succ).addClass(err);					
					label.append(notChecked);
			
				}else{
			
					$t.removeClass(err).addClass(succ);					
					label.append(checked);
			
				}
				
				break;
				
			case "username":		
			
				//found = /^[a-z0-9-_]{4,}$/i.test(field_val);
				found = /^(?=[A-Za-z0-9_-]*[A-Za-z])[A-Za-z0-9_-]{4,30}$/.test(field_val);		
			
				if(!found){
										
					$t.removeClass(succ).addClass(err);					
					label.append(notChecked);
						
				}else{
				
					$t.removeClass(err).addClass(succ);					
					label.append(checked);
				
				}
				
				break;
				
			case "password":
				
				found = pwdTest;														
							
				if(!found){	
																							
					$("input[data-name=password_2]").removeClass(succ).addClass(err);											
					$t.removeClass(succ).addClass(err);					
					label.append(notChecked);
				
				}else{
				
					$t.removeClass(err).addClass(succ);
					label.append(checked);	
								
				}
				
				break;
				
			case "password-twin":
				
				found = pwdTest;							
				
				var twin =  $t.attr("data-twin-id");
				twin =  twin? jqIdDom(twin) : $t;
		
				var twinVal = twin.val();
							
				if(!found){
																									
					$([twin[0], $t[0]]).removeClass(succ).addClass(err);					
					label.append(notChecked);
				
				}else{
				
					$t.removeClass(err).addClass(succ);
					label.append(checked);
				
					if(field_val == twinVal){	
															
						twin.removeClass(err).addClass(succ);
						twin.prev().children('.' + labelAuth).remove();
						twin.prev().append(checked);
				
					}else{	
																
						twin.removeClass(succ).addClass(err);						
						twin.prev().children('.' + labelAuth).remove();
						twin.prev().append(notChecked);
					
					}
						
				}
				
				break;
					
			default:
				
				if(field_val.length < 1){
					
					$t.removeClass(succ).addClass(err);
					label.append($notChecked);
				
				}else{
					
					$t.removeClass(err).addClass(succ);
					label.append($checked);
				
				}
			
		}			
		
	});
	
	
			
	//LETTER AVATAR////
	$(".lavatar").each(function(){
			
		var $t,$la,$rnd,$whl,$bg,$toff;
		$t = $(this);
		$whl = $t.attr("data-alphas-whl");
		$bg = $t.attr("data-alphas-bg");
		$la = $t.attr("data-alphas");
		$toff = $t.attr("data-alphas-toff");
		$rnd = $t.attr("data-alphas-rnd");
		$t.text($la).css({"width": $whl, "height": $whl, "lineHeight": $whl, "background": $bg, "borderRadius": $rnd, "top": $toff});
			
	});
	

			
	/////FUNCTION FOR IMAGE ZOOMING IN OR OUT////
	var zoomCtrl = 'zoom-ctrl';	
				
	$("img."+ zoomCtrl +",."+ zoomCtrl +" img,img#"+ zoomCtrl).click(function(e){	
			
		var $t = $(this), animate,K, zoomCls = 'zoomed-in';
		
		if(mobile_platform()) 
			return true;
				
		animate = _ANIMATED + (((K=$t.attr("data-animate")) !== undefined && K)? K : 'zoomIn');

		_zoomCtrl.opener.className += animate;
		
		if(animate){

			$t.toggleClass(animate);
			$t.data(_zoomCtrl.data, _zoomCtrl);

		}
			
		$t.attr('data-scale-xy')? (zoomCls += ' zoomed-by-scale ') : '';
		$t.toggleClass(zoomCls);
		
		if(!$t.parent().hasClass(_backOverlay.opener.className))			
			$t.castBackOverlay(e).addClass(zoomCls);
					
		else			
			$t.castBackOverlay(e, true).removeClass(zoomCls);
			
		return false; //PREVENT DEFAULT	
			
	});

	
	//CAST BACKOVERLAY//
	$.fn.extend({
			
		castBackOverlay: function(e, remove, scrollable, addUibgHibernationBtn){
			
			(remove === undefined)? (remove = false) : '';
			(scrollable === undefined)? (scrollable = false) : '';
			(addUibgHibernationBtn === undefined)? (addUibgHibernationBtn = false) : '';
			
			return this.each(function(){
			
				var $t = $(this), scrollAttr = 'data-scrollable', etype =  e.type || window.event.type;
			
				if(remove){
			
					if(etype == 'scroll' && $t.parent().attr(scrollAttr)){
			
						
					}else if($t.parent().hasClass(_backOverlay.opener.className)){
						
						$t.unwrap().removeClass(_zoomCtrl.opener.className);			
						$t.next().remove();			
						$t.children('.' + _uibg.hibernationBtnClassName).remove();	
			
					}
					
				}else{
				
					$t.wrap('<div class="' + _backOverlay.opener.className + '" ' + (scrollable? ' ' + scrollAttr + '="true" ' : '') + '></div>').css('position', 'relative').prepend(addUibgHibernationBtn? _uibg.hibernationBtnHtml : '');
					scrollable? $t.autoPosition(true) : '';
					$t.parent().after(_backOverlay.overlayer.overlay);
				
				}
				
			});
			
		}
	});
	
				
	$(document).on("click keydown scroll", function(e){
				
		var etype = e.type,tgt = $(e.target),
		overlayProps = tgt.hasClass(_backOverlay.overlayer.mainClassName) || tgt.hasClass(_backOverlay.opener.mainClassName) || tgt.hasClass(_uibg.hibernationBtnClassName);
		
		if((etype == 'keydown' && e.keyCode == _keyboard.esc.code) || etype == 'scroll' || (etype == 'click' && overlayProps))
			$('.' + _backOverlay.overlayer.mainClassName).prev().children().castBackOverlay(e, true);
			
		if(overlayProps)
			uibg_style_capture(false, e);
		
		if(etype == 'click' && tgt.hasClass(_backOverlay.overlayer.mainClassName))
			return false;
				
	});
	
		
	
	////PREVIEW UPLOADED FILES ON EDIT	
	$(".has-file-preview").hoverIntent(function(){
				
		var $t=$(this), prvCls,jqPrvCls,url,imgTag,file,oh,ow,tp,lf,tmg;
		url = $t.attr("href"); prvCls = 'img-preview'; jqPrvCls = '.' + prvCls;
		file = $t.attr("data-file");
				
		imgSrcValid(url, function(statObj){
			
			if($t.next(jqPrvCls).length)
				$t.next(jqPrvCls).remove();
				
			else{
				
				imgTag = (statObj.stat != 'ok')? '<div class="no-file-preview"><i class="fas fa-file active-done-state"></i> No preview available</div>' : 
							'<img class="img-responsive" src="' + statObj.url + '" alt="file ' + file + ' preview" /><div class="img-preview-dimension">' + statObj.width + ' x ' + statObj.height + ' </div>';
				$t.after('<div class="' + prvCls + '">' + imgTag + '</div>');
				$t.next(jqPrvCls).autoPosition();
				
			}
			
		});
				
	}, ".file-preview");	
		
	
				
	//REMOVE TARGET ELEMENT FROM DOM				
	$(document).on('click', jqAttrDom(_doc.elAttr.remEl, true), function(){
		
		var $t = $(this), elCtxt = $t.attr(_doc.elAttr.remEl), tgt;

		(tgt = $t[elCtxt]()).remove();				
		jqClassDom((elCtxt.toLowerCase() == 'prev')? tgt : tgt.find('input').attr(_doc.elAttr.remTaggedCls)).remove();
		$t.remove();								
				
	});	
			
	
				
	////CLICK TO HIDE////	
	$(document).find("[data-click-to-hide]").attr("title", "click on me to close").addClass("pointer");	
	var CLICK2HIDE_TI = 10000/**/, CLICK2HIDE_TMR =  CLICK2HIDE = '';
				
	CLICK2HIDE = function(){$("body").find("[" + _TIMED_FADE_OUT + "]:visible").each(function(){
				
			var $t = $(this); var tfData = 'data-timedFadeOut';
			$t.attr(tfData)? $t.removeAttr(tfData).fadeOut(2000) : $t.attr(tfData, true);
	
		})
				
		clearTimeout(CLICK2HIDE_TMR);
		setTimeout(CLICK2HIDE, CLICK2HIDE_TI);
				
	}
				
	CLICK2HIDE_TMR = setTimeout(CLICK2HIDE, CLICK2HIDE_TI);
	
				
	////CLOSE BTNS/////
	function boxDismissCleaner($t){
		
		if(K = $t.data(_smartToggler.dbName).opener.target.css.resetCss)
			$t.css(K);
		
	}

	$(".has-close-btn").prepend(_doc.buttons.close);
				
	$(document).on("click", ".close, .box-close, .close-toggle, [data-click-to-hide]", function(){
					
		var $t = $(this), target, K;
				
		if($t.attr("data-click-to-hide")) target = $t;
				
		else if($t.hasClass("close")) target = $t.parent();
				
		else if($t.hasClass("box-close")) target = $t.closest('.box-close-target');
				
		else if((K = $t.attr("data-close-target"))) target = $('#' + K);
				
		else target = $t.closest('['+_smartToggler.openerAttr+']');
			
		if(K = target.data(_smartToggler.dbName)){
			
			K = K.opener;
			K.toggler.el.removeClass(K.toggler.toggledClass).children('.caret-down').removeClass('up');
				
			if(target.attr("data-hide-toggler")) K.toggler.el.show();
				
			K.target.el.removeClass(K.target.toggledClass).removeAttr(K.target.toggledAttr);
			boxDismissCleaner(target);
			
		}
				
		target.hide();
		return false; //PREVENT DEFAULT
				
	});
	
		
	////FREE TOGGLES////
	$(".modal-drop.has-caret,.alert.has-caret").prepend('<div class="caret-up caret-sm modal-caret"></div>');
				
	$(document).on("click", "[data-toggle=smartToggler]", function(e){
				
		processSmartToggles($(this), e);
		
		function processSmartToggles($t, e, isClosingCtxt){
				
			var collapsedCls='collapsed', openedBy, targets, action='', K, tgtAttrStr, togAttrStr, togChildAttrStr, 
			$tx, callBack, align2Context, togAttr, togChildAttrCls, togChildAttr, tgtAttr, tgtInline, attrOnly, tgtPrev, tgtChild, 
			closeOthersInContext, activeCls, overlay, autoPos, dataBase = {opener: {}};
			$tx = $t;
			
			parseBool($t.attr("data-keep-default"))? '' : e.preventDefault(); //KEEP DEFAULT IF ALLOWED//
			collapsedCls += ((K=$t.attr(K="data-animate")) != undefined && K)? _ANIMATED + ' animation-fast ' + K : '';
			openedBy = $t.attr("data-toggle");
			activeCls = openedBy + '-active';
			tgtAttr = $t.attr(tgtAttrStr="data-target-attr") || '';
			togAttr = $t.attr(togAttrStr="data-toggle-attr") || ''; 
			togChildAttr = $t.attr(togChildAttrStr="data-toggle-child-attr") || '';
			togChildAttrCls = $t.attr("data-toggle-child-attr-class") || '';
			closeOthersInContext = $t.attr("data-close-others-in-context") || '';			
			tgtInline = ($t.attr(K="data-target-inline"))? $t.attr(K) : false; 
			tgtInline = (tgtInline && tgtInline.toLowerCase() == 'inline')? tgtInline : (tgtInline? 'inline-block' : false); 
			attrOnly = (K=$t.attr("data-attr-only"))? parseBool(K) : false; 				
			tgtPrev = (K=$t.attr("data-target-prev"))? parseBool(K) : false;
			overlay = (K=$t.attr("data-overlay"))? parseBool(K) : false; 				
			tgtChild = ($t.attr(K="data-target-child"))? $t.attr(K) : ''; 				 				
			callBack = ($t.attr(K="data-cb"))? $t.attr(K) : ''; 
			align2Context = ($t.attr(K="data-align-to-context"))? $t.attr(K) : '';
			autoPos = ($t.attr(K="data-autopos"))? parseBool($t.attr(K)) : true; 
								;
			if($t.attr(K="data-action")) action = $t.attr(K);
				
			action = action.toLowerCase();
			idOrClassCombo = jqDataTgtDom($t);
			
			targets = (idOrClassCombo.idClassIsSet)? idOrClassCombo.domCombo : (tgtPrev? $t.prev() : (tgtChild? $t.children(tgtChild) : $t.next()));			
			
			
			$(targets).each(function(e){
				
				var $t =  $(this), tgtWasVis = $t.is(":visible"), forceAttrReversal = isClosingCtxt, ePos;
				
				if(action == "hide"){
				
					$t.hide();
				
				}else if(action == "show"){		
				
					tgtInline? $t.css('display', tgtInline) : $t.show();		
				
					
				}else if(tgtWasVis || forceAttrReversal){
					/**
					 We assume all within closing context is still visible even if they are already hidden 
					 so as to process toggled attributes reversal on them all just in case
					**/
													
					if(!attrOnly){		
				
						$t.hide();
				
					}		
				
					$t.removeClass(collapsedCls).removeAttr(_smartToggler.openerAttr);
					
				}else{		
					
					if(!attrOnly){		
				
						tgtInline? $t.css('display', tgtInline) : $t.show();								
				
					}		
				
					$t.addClass(collapsedCls).attr(_smartToggler.openerAttr, openedBy);
					//$t.data("offset")? $t.scrollElemIntoView('', $t.data("offset")) : $t.attr("tabindex", "-1").focus().css("outline","none");						
				
					if(!attrOnly && autoPos)
						$t.autoPosition(true);		
				
				}		
				
				if(tgtWasVis && !attrOnly && autoPos)
					$tx.autoPosition(true);
				
				$tx[((tgtWasVis || forceAttrReversal)? 'remove' : 'add') + 'Class'](activeCls);

				if(!tgtWasVis && !forceAttrReversal){
					
					dataBase.opener.toggler = {
						
						el: $tx, 
						toggledClass: activeCls

					};

					dataBase.opener.target = {

							el: $t, 
							css: {
								top: $t.css("top"), bottom: $t.css("bottom"), 
								left: $t.css("left"), right: $t.css("right"), 
								position: $t.css("position"), display: $t.css("display"),
								'z-index': $t.css("z-index")
							}, 
							toggledClass: collapsedCls, 
							toggledAttr: _smartToggler.openerAttr

					};

					
				}
				
				(K = $t.attr("data-hide-toggler"))? $('#' + K).hide() : ''
				
			
				if(overlay)
					$t.castBackOverlay(e, (tgtWasVis || forceAttrReversal), true);
			
				if(tgtAttr)
					$tx.attr(tgtAttrStr, processSmartToggleAttr(tgtAttr, $t));
				
				if(callBack) 
					callFunctionName(callBack);	
					
				if(align2Context){

					if(!tgtWasVis || !forceAttrReversal){
						
						ePos = findPos($tx);
						$t.css({'position': 'absolute', 'top': ePos.top + $tx.height() + 'px', 'z-index': incrementZindex($t.parent())});
						
						var lPos = ePos.left, rPos = ePos.right, tgtWidth = $t.width(), togglerWidth = $tx.width(),
						resetCss = {position: dataBase.opener.target.css.position, top: dataBase.opener.target.css.top, 'z-index': dataBase.opener.target.css['z-index']};
		
						//$t.css({'left': (lPos - tgtWidth + togglerWidth) + 'px'});
						//console.log("lPos: " + lPos + " tgtW: " + tgtWidth + " tglW: " + togglerWidth + " toglH: " + $tx.height())
				
						if(lPos > rPos || tgtWidth < lPos){

							$t.css({'left': (lPos - tgtWidth + togglerWidth) + 'px'});
							//$t.css({'left': Math.abs(lPos - tgtWidth + togglerWidth) + 'px'});
							resetCss.left = dataBase.opener.target.css.left;
							
						}else{
							
							/*if(rPos > tgtWidth)
								$t.css({'right': (rPos - tgtWidth + togglerWidth) + 'px'});
							else
								$t.css({'right': (tgtWidth - rPos + togglerWidth) + 'px'});	
							//$t.css({'right': Math.abs(rPos - tgtWidth + togglerWidth) + 'px'});
							resetCss.right = dataBase.opener.target.css.right;*/
						
						}

						dataBase.opener.target.css.resetCss = resetCss;

					}else
						boxDismissCleaner($t);

					$t.toggleClass("box-elevation");

				}	
				
				//Store the data in the database
				$t.data(_smartToggler.dbName, dataBase);				
				
							
			});
			
			if(togAttr)
				$t.attr(togAttrStr, processSmartToggleAttr(togAttr, $t));		
				
			if(togChildAttr)
				$t.attr(togChildAttrStr, processSmartToggleAttr(togChildAttr, (togChildAttrCls? $t.find('.' + togChildAttrCls) : $t.children())));
			
			//close all within context except the current one
			if(closeOthersInContext && !isClosingCtxt){				
				
				//Fetch and process the toggles of all within the context
				$('.' + closeOthersInContext).find('.' + activeCls).not($t).each(function(){
					
					processSmartToggles($(this), e, true);
					
				});		
				
			}

		}
				
				
		function processSmartToggleAttr(tgtAttr, $t){		
				
			var feedBackAttr = '',kv,k,v,vkv,vk,vv,nSep = '$',sep,vkvSep,oldV,kvSep = '|';			
			
			tgtAttr = tgtAttr.split(nSep);		
				
			for(var i=0; i < tgtAttr.length; i++){		
				
				kv = tgtAttr[i].split(kvSep);
				k = kv[0]; v = kv[1];
				sep = (i + 1 == tgtAttr.length)? '' : nSep;
				vkvSep = ':';		
				
				if(k){		
				
					k = k.toLowerCase().trim();		
				
					switch(k){		
				
						case 'class': oldV = v; $t.toggleClass(v); break;		
				
						case 'css': vkv = v.split(vkvSep); vk = vkv[0]; vv = vkv[1];
							oldV = $t[k](vk); $t[k](vk, vv); break;		
				
						case 'text': 
						case 'val': oldV = $t[k](); $t[k](v); break;		
				
						case 'value': oldV = $t.val(); $t.val(v); break;
				
						case 'checked': oldV = $t.attr(k); $t.prop(k, !$t.prop(k)); break;		
				
						default : oldV = $t.attr(k)? $t.attr(k) : ''; $t.attr(k, v);		
				
					}		
				
					feedBackAttr += ((k == 'css')? (k + kvSep + vk + vkvSep + oldV) : (k + kvSep + oldV)) + sep;		
				
				}		
				
			}		
				
			return feedBackAttr;		
				
		}
				
	});
	
	
	//TOGGLE SIDE NAV/////
	$("[data-toggle=side-nav]").click(function(){		
				
		var $t,nav,wrpCls,closeParams,ss,ssCls,ov,fw,isPushee,navCloseCls,K,closeData = 'clsp';
		$t = $(this);
		nav = (K=$t.attr("data-target"))? $("#"+K) : $t.next().children(":eq(0)");
		
		ov = (K=nav.attr("data-overlay"))? parseBool(K) : false;
		fw = (K=nav.attr("data-cover"))? parseBool(K) : false;
		ss = (K=nav.attr("data-slide-style"))? K.toLowerCase() : '';
		ssCls = 'slide-'+ss+(fw? ' fw' : '');	
		navCloseCls = '_snc-by-ov';
		isPushee = (ss != 'v' && ss != 'v70');
		
		wrpCls = (isPushee? ' side-nav-pushee' : '');
		$("body").addClass(wrpCls);
		closeParams = {ssCls:ssCls, nav:nav, wrpCls:wrpCls};
		$("body").data(closeData, closeParams)
		nav.addClass(_backOverlay.opener.mainClassName).after('<div class="'+ _backOverlay.overlayer.mainClassName +' '+(ov? '' : 'overlay-hidden ')+ navCloseCls +'"></div>');
		nav.next(jqClassDom(_backOverlay.overlayer.mainClassName)).data(closeData, closeParams);
		
		if(nav.hasClass("open"))
			_cls_snav(closeParams);		
				
		else{				
					
			nav.addClass(ssCls);
			nav.children(".snc-btn").remove();
			nav.prepend('<div class="nav-toggle-bar snc-btn"><span class="nav-toggle close-icon '+ navCloseCls +'" ><i class="menu-iconbar"></i><i class="menu-iconbar"></i><i class="menu-iconbar"></i></span></div>');
			nav.find(".nav-toggle").data(closeData, closeParams)
			nav.attr("aria-expanded", "true").addClass("open");			
						
		}
		
		function _cls_snav(p){		
				
			var nav = p.nav,ssCls = p.ssCls;
			nav.removeClass(ssCls);
			nav.attr("aria-expanded", "false").removeClass("open "+ _backOverlay.opener.mainClassName);			
			nav.next(jqClassDom(_backOverlay.overlayer.mainClassName)).remove();
			if(p.wrpCls) $("body").removeClass(p.wrpCls);		
				
		}
		
		$(document).on("click", "."+navCloseCls, function(){		
				
			_cls_snav($(this).data(closeData));		
				
		});		
				
		return false; //PREVENT DEFAULT
		
	});
	
	

	////DROPDOWN TOGGLE//////
	var caretD = 'caret-down caret-xs dropdown-caret', caretU = 'up', drpDwnToggle = ".has-dropdown [data-toggle=nav-dropdown]"; 
	dataActiveHover = 'data-nav-active-hover-drop'; 		
				
	$(".has-dropdown .has-caret").append('<span class="' + caretD + '"></span>');
	$(drpDwnToggle).attr("href", 'javascript:void(0)');		
				
	$(drpDwnToggle).click(function(e){		
				
		handleNavDropDown($(this), e);	
		return false; //PREVENT DEFAULT		
				
	});		
				
	$("[data-hover-triggered-dropdowns] .has-dropdown").hover(function(e){		
				
		handleNavDropDown($(this).children("[data-toggle=nav-dropdown]").eq(0), e);	
		return false; //PREVENT DEFAULT		
				
	});
			
				
	$(document).on("click keydown", function(e){		
				
		var $t = $(e.target);
		var tgt = $t.attr("data-toggle");			
		var drpdwn = $t.closest(".dropdown-menu").length;		
				
		//DISABLE NAV DROPDOWN ESC FUNCTION ON ACTIVE HOVER DROPS
		if($("body").attr(dataActiveHover)) 		
			return;
		
		if((tgt != "nav-dropdown" && !drpdwn && e.type != "keydown") || e.keyCode == _keyboard.esc.code){		
									
			$("[data-toggle=nav-dropdown]").next(".dropdown-menu").css("display","none");
			$("[data-toggle=nav-dropdown]").children(":last-child").removeClass(caretU);
			$("ul.dropdown-menu").removeClass("open").attr("aria-expanded", "false");
			$("[data-toggle=nav-dropdown],.has-dropdown").removeClass("active");		
				
		}		
						
	});
			
				
	function handleNavDropDown($this, e){		
					
		var tgtDropMenu = $this.next("ul.dropdown-menu"), toggleParent = $this.parent(), 
		multiDrop = toggleParent.parent().hasClass("dropdown-menu"),
		hover2Drop = $this.closest("[data-hover-triggered-dropdowns]").length,
		mobileCollapsedNav = $this.closest(".collapse-nav").length,
		eventType = e.type || window.event.type, isHoverTriggered = (eventType != "click")? true : false;		
				
		//RESOLVE HOVER2DROP AND CLICK2DROP CONFLICT ON DESKTOP N MOBILE PLATFORMS
		if((hover2Drop && !isHoverTriggered && !mobileCollapsedNav) 
			|| (hover2Drop && isHoverTriggered && mobileCollapsedNav))
			return;		
					
		if(tgtDropMenu.is(":visible")){		
					
			tgtDropMenu.css("display","none");//hide("");		
			$this.children(":last-child").removeClass(caretU);
			toggleParent.removeClass("active");			
			tgtDropMenu.removeClass("open").attr("aria-expanded", "false");		
					
			if(isHoverTriggered) $("body").removeAttr(dataActiveHover);		
					
		}else{
		
			tgtDropMenu.css("display","block");//show("");		
			$this.children(":last-child").addClass(caretU);			
			toggleParent.addClass("active");			
			tgtDropMenu.addClass("open").attr("aria-expanded", "true");			
			$this.parent().prev().prev().not(".has-dropdown").attr("tabindex", "-1").focus().removeAttr("tabindex").blur();					
		
			if(isHoverTriggered) $("body").attr(dataActiveHover, "true");
		
		}
		
		if(isHoverTriggered) $this.toggleClass("active-hover-drop");
		
		if(!multiDrop){
		
			$(".has-dropdown").not(toggleParent).removeClass("active");
			$(".dropdown-menu").not(tgtDropMenu).removeClass("open").attr("aria-expanded", "false").hide();					
			$(drpDwnToggle).not($this).children(":last-child").removeClass(caretU);						
		
		}
		
	}

	
		
	////TOGGLE ACCORDIONS////	
	$(".accordions").each(function(){
		
		var idx=1,$to = $(this);
		
		$to.children(".accordion").each(function(){
		
			var $t = $(this),K;
		
			if(K=$to.attr("data-no-badge")){
		
				K.toLowerCase() == 'true'? '' : $t.prepend('<span class="accordion-badge">'+ idx +'.</span>');
				idx++;
		
			}
		
			if(K=$to.attr("data-collapsed")){
		
				if(K.toLowerCase() == 'true'){
		
					$to.children(".accordion-panel").addClass("collapsed");
					$t.addClass("active");
		
				}
		
			}
		
		});	
			
	});
		
		
	$(document).on("click", ".accordion", function(){
		
		var activeCls='active', collapsedCls='collapsed',$t,panelId,panel,glob,accordion,panels,globAccordion,animate;
		$t = $(this);		
		panelId = $t.attr("data-target");		
		panel = panelId? $("#" + panelId) : $t.next();
		globAccordion = $t.closest(".accordions");
		animate = globAccordion.attr("data-animate");
		animate? (panel.addClass(_ANIMATED + animate)) : '';
		accordion = $(".accordion"); panels = $(".accordion-panel");
		glob = globAccordion.attr("data-global") || 'true';
		
		if(glob.toLowerCase() == 'false'){
		
			accordion = globAccordion.children(".accordion"); 
			panels = globAccordion.children(".accordion-panel");
		
		} 	
		
		if(panel.is(":visible")){
		
			panels.not(panel).css({"display":"none"}).removeClass(collapsedCls).removeAttr("tabindex");	
			panel.css({"display":"none"}).removeClass(collapsedCls);
			$t.removeClass(activeCls);
		
		}else{
		
			panel.toggleClass(collapsedCls);	
			panel.css({"display":"block"});
			$t.addClass(activeCls);
		
		}		
		
		accordion.not($t).removeClass(activeCls);
		
		panel.autoPosition(true);
		return false; //PREVENT DEFAULT
		
	});

	
	////TOGGLE TABS/////
	$(_tab.cls.contents + ".has-tab-close").append('<div title="close" class="close '+ jqStripSelDelim(_tab.cls.close) +'">&times;</div>');
		
	$("[data-toggle=tab],[data-toggle=tab-tab],[data-toggle=tab-pill],[data-toggle=tab-list]").on("click", function(){	
		
		var $t = $(this),targetTabId,targetTab,defaultTarget,toggleType,xtendClass,isXtend,openActiveMobCls,
		toggleActiveCls = 'active', tabContentsCls = _tab.cls.contents, collapseCls = jqStripSelDelim(_tab.cls.collapsed),animate,K;
		
		toggleType = $t.attr("data-toggle");
		toggleType = toggleType? toggleType.toLowerCase() : '';
		isXtend = (toggleType != 'tab')? true : false;		
		toggleActiveCls += openActiveMobCls = (isXtend && $t.closest('.nav').attr("data-open-active-mob"))? ' open-active-mob ' : '';
		defaultTarget = $t[isXtend? 'closest' : 'parent'](isXtend? '.nav' : '').next(tabContentsCls).children().eq(isXtend? $t.parent('li').index() : $t.index());
		targetTabId = $t.attr("data-target");
		targetTab = targetTabId? $("#" + targetTabId) : defaultTarget;
		animate = _ANIMATED + (((K=targetTab.closest(tabContentsCls).attr("data-animate")) != undefined && K)? K : 'fadeIn');
		animate? (targetTab.addClass(_ANIMATED + animate)) : '';
		
		if(isXtend){
		
			$t.parent().toggleClass(toggleActiveCls);	
			$t.parent().siblings().not($t.parent()).removeClass(toggleActiveCls);	
		
		}else{
		
			$t.toggleClass(toggleActiveCls);	
			$t.siblings("[data-toggle=tab]").not($t).removeClass(toggleActiveCls);	
		
		}
		
		xtendClass = (isXtend && toggleType != 'tab-tab')? 'extended-tab-contents' : ((isXtend && toggleType == 'tab-tab')? 'extended-tab-tab-contents' + openActiveMobCls : '');
		targetTab.siblings().not(targetTab).not(_tab.cls.close).removeClass(collapseCls).css({"display":"none"});						
		isXtend? targetTab.parent(tabContentsCls).addClass(xtendClass) : '';
		
		if(targetTab.is(":visible")){
			
			targetTab.css({"display":"none"}).removeClass(collapseCls).parent().hide();			
		
		}else{
			
			targetTab.css({"display":"block"}).addClass(collapseCls).parent().show();
		
		}
				 
		return false; //PREVENT DEFAULT
		
	});

		
	$(_tab.cls.close).click(function(){
		
		$(this).closest(_tab.cls.contents).css({"display":"none"}).find(_tab.cls.collapsed).removeClass(jqStripSelDelim(_tab.cls.collapsed));
		$(this).closest(_tab.cls.contents).prev().children().removeClass("active open-active-mob");
		return false; //PREVENT DEFAULT
		
	});
		
	$("[data-default-tab]").click();

	
	/////FUNCTION FOR LIGHTBOX//////
	$(".lightbox").each(function(){
			
		var $t = $(this);
		$t.children().wrap('<div class="lightbox-content"></div>');
		$t.wrapInner('<div class="lightbox-row"><div class="lightbox-contents"></div></div>');
		
	});
		
	$(".lightbox").on("click", ".lightbox-content, .lightbox-navigator [data-slideIndex]", function(){
		
		var $t = $(this),slideNumber,slide,slideIndex,footSlides,totalSlides,next,prev,lightbox,captionRaw,caption,K,
		lBoxContentCls='.lightbox-content',lBoxFoot='lightbox-foot',lBoxModal='lightbox-modal';
		lightbox = $t.closest(".lightbox");		
		slides = lightbox.find(".lightbox-contents " + lBoxContentCls).children();	
		slideNumber = parseInt($t.attr("data-slideIndex")? $t.attr("data-slideIndex") : $t.index() + 1);		
		totalSlides = slides.length;		
		slideIndex = (slideNumber - 1);
		prev = ((slideNumber - 1) < 1)? totalSlides:(slideNumber - 1);
		next = ((slideNumber + 1) > totalSlides)? 1:(slideNumber + 1);		
		caption = captionRaw = slides.eq(slideIndex).attr("alt") || slides.eq(slideIndex).attr("data-alt"); 
		caption = caption? '<div class="lightbox-caption animated rollIn">'+ caption +'</div>' : '';
		slide = '<div class="modal ' + lBoxModal + '"><span class="lightbox-close-btn">&times;</span>' +
					'<div class="modal-content">' +
						'<div class="slide-badge">'+ slideNumber + ' / ' + totalSlides +'</div>' +
						'<div class="active now"><img src="'+ slides.eq(slideIndex).attr("src") + '" title="' + captionRaw + '" alt="' + captionRaw + '" /></div>' +
						'<div class="lightbox-navigator"><a class="prev-slide" data-slideIndex="'+ prev +'">&#10094;</a>' +
						'<a class="next-slide" data-slideIndex="'+ next +'">&#10095;</a></div>' + caption +					
						'<div class="' + lBoxFoot + '"></div>' +
					'</div>' +
				'</div>';	
		
		lightbox.find('.' + lBoxModal).remove();
		lightbox.append(slide);	
		lightbox.find('.' + lBoxModal).css({"display":"block"});
		lightbox.find(lBoxContentCls).clone(true).appendTo('.' + lBoxFoot);
		footSlides = lightbox.find('.' + lBoxFoot + ' ' + lBoxContentCls).children();
		footSlides.eq(slideIndex).toggleClass("active");	
		return false; //PREVENT DEFAULT
		
	});
		
		
	$(".lightbox").on("click", ".lightbox-close-btn", function(){
		
		$(this).closest(".lightbox").find(".lightbox-modal").css({"display":"none"});
		return false; //PREVENT DEFAULT
		
	});
	
	
	
	////FUNCTION TO HANDLE SLIDES/////
	$(".slide-show._auto-playing[data-hover-pause]").hoverIntent(function(e){
	
		var $t = $(this), tg = $(e.target), pausedCls='paused',pauseIconCls='pause-icon',hoverPause=false,K;
	 
		if(K=$t.attr("data-hover-pause")) K.toLowerCase() == 'true'? (hoverPause = true) : '';
	
		if(((tg.hasClass('prev-slide') || tg.hasClass('next-slide')) && e.type === 'mouseenter') || !hoverPause || $t.hasClass('_singleton')) return; 
	
		(e.type === 'mouseenter')? $t.addClass(pausedCls).find('.' + pauseIconCls).show() : $t.removeClass(pausedCls).find('.' + pauseIconCls).hide();
	
	});	
	
		
	$(".has-slide-show,.slide-show,[data-slide-show-external-pager-target]").on("click", "[data-slideIndex]", loadSlides);
			
	function loadSlides(slideNumber){
	
		var $tx = $(this),idx,slides,totalSlides,slideSeeds,slideIndex,next,prev,targetSlideShow,K,
		showPagerNum,showPagerArrow,showCtrls,animate,initialImgStyles='',slideShowCls='.slide-show',externalPagerTgtAttr='data-slide-show-external-pager-target';
		slideNumber = slideNumber || 1;		
		
		onloadHideAttr = 'hidden';
		onloadHideRemoveAttr = 'data-remove-onload-hide-and-show';
	
		if($tx.attr(K="data-slideIndex")){
	
			slideNumber = parseInt($tx.attr(K));
			targetSlideShow = $tx.closest(K='['+ externalPagerTgtAttr +']').length? $('#' + $tx.closest(K).attr(externalPagerTgtAttr)) : $tx.closest(slideShowCls);
	
		}else{
	
			targetSlideShow = $(slideShowCls); /*slideNumber = 1;*/
	
		}
		
		slideIndex = (slideNumber - 1);
			
		targetSlideShow.each(function(){
	
			var activeCls='active',pagerCrumbs='',showPagerCrumbs,pagerCrumbCls,pagerCrumbsStyle,pagerCrumbsTop,
			$t,caption,hasExternalPager,extPagers,speed,outsetTime,autoTriggered,slideShowDynamicClasses='';
			$t = $(this);
			hasExternalPager = $t.attr(K="data-has-external-pager")? $t.attr(K).toLowerCase() : 'false';
	
			if(K=$t.attr("data-scale-full")) K.toLowerCase() == 'true'? (slideShowDynamicClasses += '_slide-full ') : '';
	
			if(K=$t.attr("data-adapt-white")) K.toLowerCase() == 'true'? (slideShowDynamicClasses += '_adapt-bg-white ') : '';
	
			if(K=$t.attr("data-auto-play")) K.toLowerCase() == 'true'? (slideShowDynamicClasses += '_auto-playing ') : '';
	
			animate = $t.attr(K="data-animate")? $t.attr(K) : '';
			initialImgStyles = $t.attr(K="data-initial-img-styles")? $t.attr(K).toLowerCase() : 'true';
			showCtrls = $t.attr(K="data-controls")? $t.attr(K).toLowerCase() : 'true';
			pagerCrumbsStyle = $t.attr(K="data-pager-crumbs-style")? $t.attr(K).toLowerCase() : 'dot';
			pagerCrumbsTop = $t.attr(K="data-pager-crumbs-top")? $t.attr(K).toLowerCase() : 'false';
			pagerCrumbsTop = (pagerCrumbsTop == 'true');
			showPagerCrumbs = $t.attr(K="data-pager-crumbs")? $t.attr(K).toLowerCase() : 'true';
			showPagerNum = $t.attr(K="data-pager-numbers")? $t.attr(K).toLowerCase() : 'true';
			showPagerArrow = $t.attr(K="data-pager-arrow")? $t.attr(K).toLowerCase() : 'true';
			$t.find(".slide").length? '' : $t.children().wrap('<div class="slide"></div>');
			slides = $t.children(".slide");

			/*Implement Cycling and Time Collision Control For SlideShow With Manual and Auto Cycling*/
			speed = parseInt($t.attr(_slideShow.attr.speed)) || _slideShow.defaultSpeed;
			outsetTime = parseInt($t.attr(_slideShow.attr.outsetTime)) || false;
			autoTriggered = $tx.attr(_slideShow.attr.autoTrigger);			
			autoTriggered? $tx.removeAttr(_slideShow.attr.autoTrigger) : '';

			if((autoTriggered && getTime('ms') > outsetTime) || !autoTriggered){
	
				if(initialImgStyles == 'true') slides.addClass('_initial-img-styles');
		
				totalSlides = parseInt(slides.length);
		
				if(animate){
		
					$t.addClass('_slide-custom-anim'); slides.addClass(_ANIMATED + animate);
		
				}
		
				if(totalSlides <= 1) slideShowDynamicClasses += "_singleton";
		
				$t.addClass(slideShowDynamicClasses);
				caption = slides.eq(slideIndex).children().attr("alt") || slides.eq(slideIndex).children().attr("data-alt")
				caption = caption? '<div class="slide-caption">'+ caption +'</div>' : '';
		
				if(showCtrls == 'false' || hasExternalPager == 'true' || totalSlides <= 1) showPagerCrumbs=showPagerArrow=showCtrls=false;
				
				prev = ((slideNumber - 1) < 1)? totalSlides:(slideNumber - 1);
				next = ((slideNumber + 1) > totalSlides)? 1:(slideNumber + 1);
				extPagers = $('[data-slide-show-external-pager-target='+ $t.attr('id') +']').children();
				
				pagerCrumbsTop? $t.addClass('has-pager-crumbs-top') : '';

				switch(pagerCrumbsStyle){

					case 'tile': pagerCrumbCls = 'tile'; 
							$isPagerTileStyle = true; 
							$t.addClass('has-pager-style-tile'); 
							break;

					default: pagerCrumbCls = 'dot'; 
							$isPagerTileStyle = false;
					
				}
				
				for(idx=1; idx <= totalSlides; idx++){
		
					if(hasExternalPager == 'true'){
		
						extPagers.eq(idx - 1).attr('data-slideIndex', idx);
						
						if(idx == slideNumber){
							
							extPagers.removeClass(activeCls);
							extPagers.eq(idx - 1).addClass(activeCls);
							
						}
						
						continue;
		
					}
		
					pagerCrumbs += '<span class="pager-crumb '+ pagerCrumbCls +' ' + ((idx == slideNumber)? activeCls : "" ) + '" data-slideIndex="'+ idx +'" '+ ($isPagerTileStyle? 'style="width:'+ (100 / totalSlides) +'%"' : '') +' ></span>';
		
				}
						
				slideSeeds = '<div class="slide-seeds">' +
								((showPagerNum == 'true')? '<div class="slide-badge">'+ slideNumber + ' / ' + totalSlides +'</div>' : '') +					
								'<div class="'+ ((showPagerArrow == 'true')? '' : 'hide') +'"><a class="prev-slide" data-slideIndex="'+ prev +'">&#10094;</a>' +
								'<a class="next-slide" data-slideIndex="'+ next +'">&#10095;</a></div>' + caption +	
								'<div class="pause-icon" title="paused">||</div>'+
							'</div>';
				
				slides.css("display","none");			
				$t.children(K=".pager-crumbs-base,.slide-seeds").remove();
		
				if(showPagerCrumbs == 'true'){

					$t[pagerCrumbsTop? 'prepend' : 'append']('<div class="pager-crumbs-base">'+ pagerCrumbs +'</div>');

				}
								
				$t.append(slideSeeds);
				$t.children(K).css("display", "block");
				slides.eq(slideIndex).css("display","block");
				
				//register outset time for the current slide
				$t.attr(_slideShow.attr.outsetTime, (getTime('ms') + speed));
				
				if($t.attr(onloadHideRemoveAttr)){
					
					$t.removeAttr(onloadHideAttr).removeAttr(onloadHideRemoveAttr);
					$t.css("display", "block");
					
				}
				
				else if($t.attr(onloadHideAttr))
					$t.attr(onloadHideRemoveAttr, true);	
			}							

		});
	
		return false; //PREVENT DEFAULT
	
	}
	
	
	function cycleSlides(){
	
		$(".slide-show._auto-playing:not(.paused)").each(function(){
	
			var $t = $(this);
			var speed = parseInt($t.attr(_slideShow.attr.speed)) || _slideShow.defaultSpeed;
			setInterval(function(){
				$t.not(".paused").find(".next-slide").attr(_slideShow.attr.autoTrigger, true).trigger("click");
			}, speed);
	
		});
	
	}
	
	
	

	/**END OF FRAMEWORK**/
	
	
		
	/////FUNCTION TO ADD ACTIVE CLASS TO LINKS////////

	var path = window.location.pathname;
	var page = (path.split("/"))[1];//.replace(/^(.*)\/([^/]*)/, "$2");	
		
	$(".top-nav li a,.breadcrumbs li a").toArray().forEach(function(e){
	
		var pageIn = ($(e).attr("href")).split("/");
		pageIn = (pageIn.length > 1)? pageIn[1] : pageIn[0];
	
		if(pageIn.toLowerCase() == page.toLocaleLowerCase()){
	
			if($(e).closest(".breadcrumbs").length)
				$(e).closest("li").addClass("active-sn");
	
			else
				$(e).closest("li").addClass("has-ncount");
				
		}
	
	});
	
	/////FUNCTION TO DISABLE LAST NAV BREADCRUMBS////////
	$(".nav.breadcrumbs li:last-child a").attr({"disabled" : "disabled", "title" : "This link is disabled",}).click(function(){return false;});
	
	//FUNCTION TO FILL METERED-TITLE
	//$('.qlinks').hoverIntent(function(){$(this).closest('li').siblings('.metered-title').toggleClass('meter-full');}, 'a');

	
	////FUNCTIONS TO RESEND CONFIRMATION CODES/////////

	jqClassDom(codeResendCls = 'resend-code').each(function(){

		$(this).attr(_doc.timedVisAttr, _doc.codeResendInterval);

	});
		
	jqClassDom(codeResendCls).click(function(e){
									
		var $t = $(this), ajadata='', output, outputCls = 'code-resend-ajax-res';

		e.preventDefault(); //PREVENT DEFAULT

		if($t.data("user")){
	
			var user = $t.data("user");											
			ajadata = 'user=' + user;	
							
		}else if($t.data("email")){
		
			var email = $t.data("email");											
			ajadata = 'email=' + email;
					
		}else if($t.data("ulu")){
		
			var ulu = $t.data("ulu");											
			ajadata = {_ulu:ulu, unlock:true};
		
		}
		
		$t.next().hasClass(outputCls)? '' : $t.after('<div class="' + outputCls + '"></div>');
		output = $t.next();
		output.html('<b class="red">Resending your code. please wait </b>');
		toggle_preloader({context:output});
		
		$.ajax({
			
			url:'/resend_confirmation_code',
			method:'post',
			data:ajadata,
			success:function(res){
				
				output.html(res);				
				
			}
						
		});	
					
		//return false; //PREVENT DEFAULT
		
	});
			
		

	////FUNCTION TO  EXECUTE CHECK FOR DELETE OF INBOX AND OLD INBOX MESSAGES///////

	////SELECT CHECKBOX TO DELETE/////////////	

	$(K_pmc='.pm-check-all').click(function(){
		
		var $t = $(this), base = $t.closest(".pm-root"), pmIds='',txt;
		
		switch($t.text().toLowerCase()){
		
			case 'uncheck all': txt = 'check all'; 
				//base.find(".checkbox_inbox").filter(":checked").trigger("click"); break;
				base.find(".checkbox_inbox").filter(":checked").each(function(){
					
					pmIds += $(this).attr("data-pm") + ',';
					$(this).prop("checked", false);
					
				}); break;
		
			//default: txt = 'uncheck all'; base.find(".checkbox_inbox").not(":checked").trigger("click");
			default: txt = 'uncheck all'; 
				base.find(".checkbox_inbox").not(":checked").each(function(){
					
					pmIds += $(this).attr("data-pm") + ',';
					$(this).prop("checked", true);
					
				});
		
		}
		
		if(pmIds){
			
			$t.after('<input type="hidden" data-pm="' + pmIds + '" class="checkbox_inbox" />');
			$t.next().trigger("click");
			
			$(K_pmc).text(txt);
			
		}
		
		return false;
		
	});
		
		
	$('.pm-root').on('click', '.checkbox_inbox', (function(){
		
		var $t = $(this), data, pm;
		pm = $t.attr("data-pm");				
		data = "pm=" + pm;
		
		$.ajax({
					
			url:'/do-pm-delete-check',
			method:'post',
			data:data,
			success:function(res){
				
				$(".total-pm-checked").html(res);
			
			}
						
		});		
				
	}));



	////ONCLICK FOR DELETE OF INBOX AND OLD INBOX////////
		
	$(".empty-inbox").click(function(){
		
		var urlTarget = $(this).attr("data-landpage");							
		var grant = $(this).val();							
		grant = grant.trim();					
				
		if(grant == "OK"){
		
			window.location.assign(urlTarget);
		
		}
		
		return false; //PREVENT DEFAULT	
				
	});
		



	////FUNCTION TO HANDLE BBC AND EMOTICONS ONCLICK EVENTS//INPOST COLOR WELL ipcWell////
	$(_HAS_BBC_CLS).on("change", "[data-color-well]", function(){
		
		var $t = $(this), val,eti,fpos=6,K='data-response-id';
		val = $t.val() || '#000';
		$t.val(val);
		eti = $('#' + $t.closest('[' + K + ']').attr(K));
		eti.insertAtCaret('[col=' + val + '][/col]').selectRange(getInputSelection(eti[0]).start - fpos);
				
	});
	
	
	$(_HAS_BBC_CLS).on("click", "[data-bbc]:not([data-color-well])", function(){
		
		var $t = $(this); var code,eti,fbp,recentlyUsed='recently-used',K='data-response-id',emoticons=jqClassDom(_emoticons.mainClassName);
		////fbp focus back position
		code = $t.attr("data-bbc");
		fbp = $t.attr("data-fbp") || 0;
		eti = $('#' + $t.closest('[' + K + ']').attr(K));
		eti.insertAtCaret(code);
		
		if(fbp)
			eti.selectRange(getInputSelection(eti[0]).start - fbp);
		
		if($t.hasClass('emoticon') && (K = $t.attr('data-remoticon'))){
		
			var data = "remoticon="  + K;
		
			$.ajax({
				
				url:'/' + $('html').attr('data-remoticons-loader'),
				method:'post',
				data:data,
				dataType:'JSON',
				success:function(res){
					
					if(res.loadedRecentlyUsed)
						((K=emoticons.find('#' + recentlyUsed)).length)? K.html(res.loadedRecentlyUsed) : emoticons.prepend(res.loadedRecentlyUsed);
					
					if(res.reloadedEmoticonTabs){
						
						$("#" + _emoticons.dropPaneId).find("#" + _emoticons.tabId).remove();
						emoticons.closest("." + _emoticons.emojisContainerClassName).before(res.reloadedEmoticonTabs).closest("#" + _emoticons.dropPaneId).addClass('has-emotabs');
						emoticons.html(res.loadedEmoticons);
						AJAX_RECALLS('loadSlideshow');
						
					}
					
				}
		
			});
		
		}
		
		return false; //PREVENT DEFAULT	
		
	});
	
	
	$(_HAS_BBC_CLS).on("click", "[" + _emoticons.pager + "]:not([" + _doc.control.busyAttr + "]),.emoticon-tab,[" + _emoticons.tabs.prefetcher + "]:not([" + _doc.control.busyAttr + "])", function(e){
		
		var $t = $(this), data,K;
		
		/*Prefetch emoticons tabs
			It was opted not to embed emoticon tabs directly on the page but 
			instead load tabs via ajax to speed up page loading time on front end
		*/
		if($t.attr(_emoticons.tabs.prefetcher)){
			
			$t.attr(_doc.control.busyAttr, true);
			data = "load_emoticon_tabs=true";
		
			$.ajax({
				
				url:'/' + $('html').attr('data-emoticon-tabs-loader'),
				method:'post',
				data:data,
				dataType:'JSON',
				success:function(res){
					
					jqClassDom(_emoticons.mainClassName).closest("." + _emoticons.emojisContainerClassName).before(res.loadedEmoticonTabs);
					$t.removeAttr(_emoticons.tabs.prefetcher);//Remove Prefetcher; Save Resource By Not Prefetching Redundantly
					jqIdDom(_emoticons.tabId).hide(); //We don't need the tab visible for now
		
				}
		
			});

		//Load all or more emoticons using pager
		}else if($t.attr(_emoticons.pager)){
		
			var pageId = parseInt($t.attr(_emoticons.pager)), busyControl = _doc.control.busyAttr,
			data = "emoticons_load_page_id=" + pageId;
			$t.attr(busyControl, true);
		
			$.ajax({
				
				url:'/' + $('html').attr('data-memoticons-loader'),
				method:'post',
				data:data,
				dataType:'JSON',
				beforeSend:function(){
		
					toggle_preloader({context:$t, spinClass:'emojis-ui-spinner'});
		
				},success:function(res){
					
					$t.attr(_emoticons.pager, (pageId + 1)).removeAttr(busyControl).parent().html(res.loadedEmoticons);
					toggle_preloader({context:$t, remove:true});
		
					if(res.lastRecord){
		
						$t.hide();
		
					}else{
					
						$t.trigger("click");
					
					}
					
				},complete:function(){
					
					//After loading more emoticons, make the prefetched emoticon tabs visible
					jqIdDom(_emoticons.tabId).show().closest("#" + _emoticons.dropPaneId).addClass('has-emotabs');					
					AJAX_RECALLS('loadSlideshow');
				
				}
				
			})

			
				
		}else{
				
			highlightEmotabs($t);
			$('#' + $t.attr("data-tgt")).scrollElemIntoView($t.closest("#" + _emoticons.tabId).next());				
			e.preventDefault();
				
		}
		
	});
				
				
				
				
	function highlightEmotabs($t){
		
		$('.emoticon-tab').each(function(){
				
			var $ti = $(this),K,foc='_f',curr_e;
			curr_e = $t.is($ti);
			$ti.attr({"aria-selected": (curr_e? true : false), "tabindex": (curr_e? 0 : -1)});
			K = $ti.find('img');
			K.attr("src", K.attr("src").replace(foc, ''));
			curr_e? K.attr("src", K.attr("src").replace('.png', foc + '.png')) : '';
				
		});
				
	}
	
	
	$(_HAS_UIBG_CLS).on("click", "[" + _uibg.loaderAttr + "]", function(e){
				
		var $t = $(this), uibgWrapper, oldTgtStyles, tgtComposer, uibgId, wrapClsPri = _uibg.mainClassName, fillCls = _uibg.fillClassName, oldVal, tmp,
		wrapCls, composerForm, loaderFieldName =_uibg.loaderFieldName, cleanUpCls = _uibg.defaultBgClassName, emojisUiContainer = _emoticons.emojisContainerClassName, 
		containerOldScroll,
		dataFieldCount = _uibg.fieldCountAttr;
		tgtComposer = $('#' + $t.closest('._uibg').attr('data-response-id'));
		composerForm = tgtComposer.closest('form');
		tgtComposer.removeClass(cleanUpCls);
		uibgId = $t.attr(_uibg.loaderAttr);
		wrapCls = _uibg.wrapperClassName; 
		oldTgtStyles = /*tgtComposer.attr('style') ||*/ '';
				
		if(composerForm.hasClass(_uibg.charExceededClassName))
			return false;
				
		uibgWrapper = tgtComposer.closest('.' + wrapClsPri);
		uibgWrapper.removeClass(_uibg.hibernationClassName);
		containerOldScroll = $t.closest('.' + emojisUiContainer).scrollTop();
		
		highlight_selected_uibg($t);
		
		if(tgtComposer.attr(_uibg.highlightOnlyAttr))
			return false;
			
		if(uibgWrapper.length){
				
			uibgWrapper.children('[name=' + loaderFieldName + ']').remove();
			uibgWrapper.removeClass(wrapCls);
			composerForm.castBackOverlay(e, true, true, true);
				
		}
		
		if(uibgId != _uibg.noBgDefaultId){
				
			composerForm.addClass(wrapCls).removeAttr(_uibg.charExceededAttr).prepend('<input type="hidden" name="' + loaderFieldName + '" value="' + uibgId + '"/>').castBackOverlay(e, false, true, true);
			//tgtComposer.attr("style", oldTgtStyles + $t.attr("data-uibg-styles")).attr(_uibg.charExceededAttr, uibgId);
			var composerAttr = {};
			composerAttr["style"] = oldTgtStyles + $t.attr("data-uibg-styles");
			composerAttr[_uibg.charExceededAttr] = uibgId;
			composerAttr[dataFieldCount] = true;
			composerAttr["data-minLength"] = _uibg.maxCharLen;
			tgtComposer.attr(composerAttr).addClass(_uibg.initGrowthPadClassName);
			tgtComposer.parent().addClass(fillCls);
				
			if(!validate_uibg_len(tgtComposer))
				uibg_style_capture(false, e, true);
				
		}else{
				
			tgtComposer.addClass(cleanUpCls).removeAttr(dataFieldCount).removeAttr("style");
			tgtComposer.parent().removeClass(fillCls);
			tgtComposer.prev('.' + _uibg.fieldCounterClassName).hide();
				
		}
				
		if(tgtComposer.attr(_uibg.loadedComposerBgAttr)){
				
			tgtComposer.focus_field_end();
				
		}
				
		tgtComposer.removeAttr(_uibg.loadedComposerBgAttr).focus();
		$t.closest('.' + emojisUiContainer).scrollTop(containerOldScroll);
		
		return false; //PREVENT DEFAULT	
		
	});
	
	
	$(_HAS_UIBG_CLS).on("click", "[" + _uibg.pager + "]:not([" + _doc.control.busyAttr + "])", function(){
				
		var $t = $(this), pageId = parseInt($t.attr(_uibg.pager)), busyControl = _doc.control.busyAttr,
		composer = $t.closest(_HAS_UIBG_CLS).find('#' + _COMPOSER), data = "uibg_load_page_id="  + pageId;
		
		$t.attr(busyControl, true);
		
		$.ajax({
			
			url:'/' + $('html').attr('data-muibg-loader'),
			method:'post',
			data:data,
			dataType:'JSON',
			beforeSend:function(){
				
				toggle_preloader({context:$t, spinClass:'emojis-ui-spinner'});
				
			},success:function(res){
				
				$t.attr(_uibg.pager, (pageId + 1)).removeAttr(busyControl).parent().html(res.loaded);
				toggle_preloader({context:$t, remove:true});
				highlight_selected_uibg(composer.closest(_HAS_UIBG_CLS).find("[" + _uibg.loaderAttr + "=" + composer.attr(_uibg.loadedComposerBgAttr) + "]"));
			
				if(res.lastRecord)
					$t.hide();
			
				else
					$t.trigger("click");
			
			}
			
		})
		
		
	});
			
	
	//CONSTRAIN UIBG TO INPUT LEN
	$(document).on(_doc.events.fieldEdit + ' click', '#' + _COMPOSER, function(e){
			
		var $t = $(this);
			
		if($t.attr(_uibg.highlightOnlyAttr)){
			
			highlight_selected_uibg($t.closest(_HAS_UIBG_CLS).find("[" + _uibg.loaderAttr + "=" + $t.attr(_uibg.loadedComposerBgAttr) + "]"));
			$('.' + _uibg.mainClassName).removeClass(_uibg.hibernationClassName);
			$t.removeAttr(_uibg.highlightOnlyAttr);
			validate_uibg_len($t, true, e);
			
		}else{
			
			validate_uibg_len($t, false, e);
			return true;
			
		}
			
	});
			
	
	function validate_uibg_len(tgtComposer, checkOnly, e){
			
		(checkOnly === undefined)? (checkOnly = true) : '';
		var validLen = true, len = _uibg.maxCharLen, uibgId, composerForm = tgtComposer.closest('form'), 
		composerLen = tgtComposer[(tgtComposer.attr(_doc.control.editableAttr)? 'html' : 'val')]().length;
		validLen = !(composerLen > len);
			
		if(!checkOnly)
			uibg_style_capture(validLen, e, true);
		
		tgtComposer[(composerLen >= 70 ? 'remove' : 'add') + 'Class'](_uibg.initGrowthPadClassName).removeClass(_uibg.defaultBgClassName);
			
		if(composerForm.hasClass(_uibg.wrapperClassName))
			composerForm[(validLen? 'remove' : 'add') + 'Class'](_uibg.charExceededClassName);
			
		return validLen;
			
	}
	
	
	function uibg_style_capture(revertClosedStyle, e, invalidateFieldName, loadStyles){
			
		var composer = $('.' + _uibg.mainClassName + ' #' + _COMPOSER), composerForm = composer.closest('form'),
		loaderFieldNameAttr = '[name*=' + _uibg.loaderFieldName + ']', loaderFieldName = composerForm.find(loaderFieldNameAttr).attr("name"), invalid = (invalidateFieldName? '_INVALID' : '');
		
		if(revertClosedStyle){
			
			if($('.' + _uibg.mainClassName).hasClass(_uibg.hibernationClassName) || loadStyles){
			
				composer.attr('style', (loadStyles? loadStyles : composerForm.attr(_uibg.capturedStyleAttr)));
				composerForm.removeClass(_uibg.hibernationClassName).castBackOverlay(e, false, true, true).find(loaderFieldNameAttr).attr('name', loaderFieldName.replace(invalid, ''));
				composer.focus_field_end();
			
			}else{
			
				composer.attr(_uibg.fieldCountAttr, true).attr('data-minLength', _uibg.maxCharLen);
			
			}
			
		}else{
			
			var closedStyle = composer.attr('style');
			
			if(closedStyle){
			
				composer.attr('style', '').prev('.' + _uibg.fieldCounterClassName).hide();
				$('.' + _uibg.mainClassName).addClass(_uibg.hibernationClassName).attr(_uibg.capturedStyleAttr, closedStyle);
				composerForm.castBackOverlay(e, true).find(loaderFieldNameAttr).attr('name', loaderFieldName + invalid);
			
			}
			
		}
		
	}
	
			
	/*
	function focus_field_end(tgt){
			
		var editableAttr = _doc.control.editableAttr, oldVal = tgt[(tgt.attr(editableAttr)? 'html' : 'val')]();
		tgt[(tgt.attr(editableAttr)? 'html' : 'val')]('')[(tgt.attr(editableAttr)? 'html' : 'val')](oldVal).focus();
			
	}*/
	
			
	$.fn.extend({
			
		focus_field_end: function(){
			
			return this.each(function(){
			
				var $t = $(this), editableAttr = _doc.control.editableAttr, oldVal = $t[($t.attr(editableAttr)? 'html' : 'val')]();
				$t[($t.attr(editableAttr)? 'html' : 'val')]('')[($t.attr(editableAttr)? 'html' : 'val')](oldVal).focus();
			
			});
			
		}
		
	});
	
			
	function highlight_selected_uibg($t){
			
		var ariaLabel = 'aria-label', ariaLabelInfo = ', selected', highlightCls = '_uibg-selected'; 
			
		$("[" + _uibg.loaderAttr + "]").each(function(){
			
			var $ti = $(this), label, tgt = $ti.closest("[" + ariaLabel + "]");
			label = tgt.attr(ariaLabel);
			
			if(label)
				label.lastIndexOf(ariaLabelInfo)? tgt.attr(ariaLabel, label.replace(ariaLabelInfo, '')) : '';

			$ti.removeClass(highlightCls);
			$ti.is($t)? $ti.addClass(highlightCls).closest("[" + ariaLabel + "]").attr(ariaLabel, label + ariaLabelInfo) : '';
			
		});
			
	}
	
			
	//LIVE EDITOR
	$(".has-composer").on("click", ".live-editor-placeholder", function(){
			
		$(this).closest(".live-editor").find(".live-editor-composer").focus();
			
	})
			
	$(".has-composer").on("keyup focusin focusout", ".live-editor-composer", function(e){
			
		var $t = $(this), focusCls = 'focused', etype = e.type;
			
		if(etype == 'focusin' || etype == 'focusout')
			$t.closest(".live-editor-base")[((etype == 'focusout')? 'remove' : 'add') + 'Class'](focusCls);
			
		else{
			
			$t.closest(".live-editor").find(".live-editor-placeholder").css("visibility",($t.text()? "hidden" : "visible"));
			$t.closest(".live-editor")[(($t.text().length >= 90)? 'add' : 'remove') + 'Class']("grown");
			
		}
			
	});
			
			
	
	////CHECK FOR NULL POST FIELD////
	$('.has-composer').on("click", '[data-check-fnull]', function(){
			
		var composer = $("#" + _COMPOSER);
			
		if(!composer[(composer.attr(_doc.control.editableAttr)? 'html' : 'val')]()){
			
			$("#reply-box .error-box").html('<span class="alert alert-danger align-c" data-click-to-hide="true">Ooops! empty message</span>').attr("tabindex", "-1").focus();
			return false;
			
		}
			
	});
	
	
	/////FUNCTION FOR POST REPLIES//////
	jqClassDom(_doc.replyBoxHandleClass).on("click", function(e){
			
		e.preventDefault();
		var $t = $(this), href = $t.attr("href"), postBox = '#reply-box', composerId = '#'+_COMPOSER, composer = $(composerId), composerForm = composer.closest("form"),
		composerGroup = composer.closest(".composer-group"), appending = $t.attr("data-append"), loadedUiBg = false, url,
		preloaderDpn = jqClassDom(_doc.preloaderDpnClass);
		
		$(postBox + "," + composerId).show().autoPosition(true);				
		
		if(!appending){
			
			composer[(composer.attr(_doc.control.editableAttr)? 'html' : 'val')]("");
			composerForm.attr("action", href).show();
			
		}
		
		if(composerForm.attr("action") == '/')
			composerForm.attr("action", href);
			
		$("#modify-file-box," + postBox + " .error-box," + postBox + " .unlogged-error-box").html("");
		$("#vds-check").prop("checked", false);	
		var lrdr = $t.attr("data-lrdr");
			
		if(lrdr){
			
			$(postBox + " .unlogged-error-box").html('<span class="alert alert-danger">Please <a class="links" href="' + lrdr + '">Login</a> first</span>').autoPosition(true);
			return false;
			
		}

		$t.hide();
		toggle_preloader({context:preloaderDpn, insertTarget:$t, insertMethod:'after'});
		
		//var $url = ($t.attr("href").split("#"))[0];
		url = href.replace(/\#.+/gi, '');
		
		$.ajax({
			
			url:url,
			method:"post",
			dataType:"json",
			beforeSend:function(){
			
				toggle_preloader({context:$(postBox), insertMethod:'prepend', spinClass:'post-box-spinner'});
			
			},success:function(data){
				
				if($t.attr("data-load-content")){
					
					if(data.hideForm)
						composerGroup.hide();
			
					else{		
						
						composer.insertAtCaret(data.loadedDatas);
								
						if(data.loadedUiBg != 0){
			
							var composerAttr = {}; 
							composerAttr['style'] = data.loadedUiBgStyleMetas;
							composerAttr[_uibg.loadedComposerBgAttr] = data.loadedUiBg;
							composerAttr[_uibg.highlightOnlyAttr] = loadedUiBg = true;
							//composer.attr(_uibg.loadedComposerBgAttr, data.loadedUiBg).trigger("click");
							composer.attr(composerAttr).trigger("click");
							composerForm.addClass(_uibg.wrapperClassName).wrap('<div class="' + _backOverlay.opener.className + '" data-scrollable="true"></div>');
							composerForm.prepend(data.loadedUiBgCloseBtn + data.loadedUiBgField).parent().after(_backOverlay.overlayer.overlay);
							composer.parent().addClass(data.loadedUiBgFillCls);
			
						}
						
						$("#modify-file-box").html(data.files);
						$("#vds-check").prop("checked", data.vds);
						$("#syndicate-url").val(data.syndicateUrl);
						
						if(data.loadedTags){
							
							$("#threadTagsToggleWrapper").html(data.tagsToggleBtn);
							$("#threadTags").val(data.loadedTags);
							
						}
			
						composerGroup.show();
			
					}
			
				}
						
				if(!appending && !loadedUiBg)
					composer.closest(_HAS_UIBG_CLS).find("[" + _uibg.loaderAttr + "=" + _uibg.noBgDefaultId + "]").trigger("click");
				
				if(!appending)
					$(_POST_SUBMIT_BTN_ID).attr("value", data.postBtnTxt);
				$(postBox + " .error-box").html(data.errors);
				$(postBox + " .unlogged-error-box").html(data.unloggedUserErrors);
				toggle_preloader({context:$(postBox), remove:true});
				location.assign(postBox); //Bring post box into full view
				composer.focus();
				
				if(!appending && !data.hideForm){
			
					composerForm.attr("action", href).show();
			
				}

				toggle_preloader({context:preloaderDpn, remove:true});
				$t.show();
				AJAX_RECALLS(['autoRewrite', 'customScrollbar']);
			
			}
			
		});
		
		return false; //PREVENT DEFAULT
			
	});
	


	
	/////FUNCTION TO PRELOAD COMPOSER FORM//////
	(function(){
			
		var $t = $(".load-reply-box"), preloaderDpn = jqClassDom(_doc.preloaderDpnClass), replyBoxHandle = jqClassDom(_doc.replyBoxHandleClass),url;
		//var url = ($t.attr("href").split("#"))[0];		
		url = $t.attr("href");

		if(url){

			url = url.replace(/\#.+/gi, '');

			$.ajax({

				url:url,
				method:"post",
				dataType:"json",
				data:{ajaxPreloadComposer:true},
				beforeSend:function(){

					toggle_preloader({context:preloaderDpn, insertTarget:replyBoxHandle, insertMethod:'after'});

				},success:function(data){
				
					$("#reply-box").html('<div class="close" title="close">&times;</div><div class="unlogged-error-box"></div>' + data.htmlForm);
					replyBoxHandle.css("display", "inline-block");
					toggle_preloader({context:preloaderDpn, remove:true});
				
				}
				
			});
				
		}
				
	})();
		
	
	
	
		
	////FUNCTION TO DO CHECKS FOR MULTI QUOTES////////
	$(".multiquote").click(function(){
			
		$.ajax({
						
			url:"/multi-quote",
			method:"post",
			data:{post:$(this).attr("data-pid")},
			success:function(res){
				
			}

		});	
	
	});



	//////FUNCTION TO CLEAR ALL MULTIQUOTES/////
	$(".clear-mqt").click(function(){		
				
		var $t = $(this), data = 'clear=1';
		$(".multiquote").prop("checked", false);
		
		$.ajax({
						
			url:"/clear-multiquotes",
			method:"post",
			data:data,
			beforeSend:function(){

				toggle_preloader({context:$t.parent(), insertTarget:$t, insertMethod:'after'});

			},success:function(res){
			
				toggle_preloader({context:$t.parent(), remove:true});
			
			},complete:function(){
			
				//location.reload();
			
			}
			
		});
			
		return false; //PREVENT DEFAULT
			
	});


	////FUNCTION TO EDIT OR REMOVE POSTED FILES DURING POST MODIFICATION///
	$(".has-composer").on("click", ".edit-posted-files", function(){
			
		var $t = $(this);			
		var data;		
		var file = $t.attr("data-file");			
		var mid = $t.attr("data-mid");
		var count = parseInt($("#post-uploads-count").text());
		$("#post-uploads-count").text(count - 1);
		data = "file=" + file   + "&post=" + mid;
		$t.parent().remove();

		$.ajax({
			
			url:"/modify-post-file",
			method:"post",
			data:data,
			success:function(res){			
								
			}
			
		});

		return false; //PREVENT DEFAULT	

	});


	/////FUNCTION TO REMOVE AVATAR///////
	$("[data-remove-file]").click(function(){
		
		var $t = $(this);
				
		$.ajax({
			
			url:"/remove-file",
			method:"post",
			data:{file:$t.attr("data-file"), tgt:$t.attr("data-remove-file")},
			success:function(res){
						
				$t.parent().remove();
										
			}
					
		});
		
		return false; //PREVENT DEFAULT	
				
	});



	/////FUNCTION TO DYNAMICALLY ADD MORE FILE UPLOAD INPUTS ///
		
	$(".has-composer").on("click", jqAttrDom('data-add-file-field', true), function(){
		
		var $t = $(this), srcMeta =  $t.attr("data-meta"), 
		cloned, delBtn = '<div class="_micon pointer" '+ _doc.elAttr.remEl +'="parent" style="position: absolute; top: 9px; right: 9px;background-image:url(\'' + srcMeta + '\')" title="Remove"></div>';
		cloned = $t.siblings('input').first().clone(true);		
		$t.before(cloned).prev().wrap('<div class="relative"></div>');
		$t.prev().append(delBtn).find('input').val('').removeAttr(_doc.elAttr.remTaggedCls); //clear values/data of the added clone		
		
		return false; //PREVENT DEFAULT
			
	});

		

	////LIKE FUNCTION/////////
	function do_vote($t, action, count, state, pid, disp){
		
		var css = res = like_link = state2 = count2 = scores = "" ;			
		action = action.trim();
		state = parseInt(state);
		count = parseInt(count);
		css = $t.attr("data-css");
		
		$t2 = $('[data-post-vote][data-pid='+ pid +']').not($t);
		$t2.children(".far").removeClass(css);
		state2 = parseInt($t2.attr("data-state"));
		state2? $t2.attr("data-count", parseInt($t2.attr("data-count")) - 1) : '';
		count2 = parseInt($t2.attr("data-count"));
		$t.children(".far")[(state? 'remove' : 'add') + 'Class'](css);
		count = state? count - 1 : count + 1;
		scores = (action == "downvote")? count2 - count : count - count2;
		$t.attr({"data-count" : count, "data-state" : (state? 0 : 1)});
		$t2.attr({"data-count" : count2, "data-state" : 0});
		$('#' + disp).text(scores);
		
	}

		
	$(".vote-answer").click(function(){
		
		var $t = $(this);	
		var postId =  $t.attr("data-pid");
		var action =  $t.attr("data-action");				
		var count =  $t.attr("data-count");				
		var state =  $t.attr("data-state");				
		var disp =  $t.attr("data-disp");				
		var data = "postId="  + postId + "&taskAction="  + action ;				
		
		do_vote($t, action, count, state, postId, disp);
		
		$.ajax({
			
			url:"/vote-answer",
			method:"post",
			data:data,			
			success:function(res){
																
			}
										
		});
		
		return false; //PREVENT DEFAULT
		
	});

		

	////////AVATAR LIKE////////
	function do_avatar_like($t, action, count, disp){
		
		count = count || 0;
		var res = '', raw_res = '', user = '' ;		
		doCount = ($t.attr("data-count") !== undefined);
		user = $t.attr("data-user");					
		action =  action.trim();
		count = parseInt(count);
		
		if(action == "like"){
		
			res = (count + 1);
			$t.html('Unlike <i class="far fa-thumbs-down active-done-state"></i>');			
			like_link = $t.attr("href").replace("/like", "/unlike");
			$t.attr("href", like_link);
			$t.attr("data-action", "unlike");
		
			if(doCount)
				$t.attr("data-count", res);
		
			res += (res > 1)? ' Likes ' : ' Like ';
		
			if(!doCount)
				res = ' You have also liked <b><a href="/' + user + '" class="links" >' + user + '</a>\'s</b> avatar ';
		
		}else if(action == "unlike"){
		
			res = raw_res = (count - 1);
			res = ( res >= 1)? res : '';
			$t.html((!doCount)? 'Like Back' : 'Like' + ' <i class="far fa-thumbs-up"></i>');			
			like_link = $t.attr("href").replace("/unlike", "/like");
			$t.attr("href", like_link);
			$t.attr("data-action", "like");
		
			if(doCount)
				$t.attr("data-count", raw_res);
		
			res += (res > 1)? ' Likes ' : ((res)? ' Like ' : '');
		
			if(!doCount)
				res = '';
		
		}
		
		$('#' + disp).html(res);												
		
		
	}

		
		
	$("[data-has-avatar-like]").on('click', '.dp_like', function(){
		
		var $t = $(this);
		var user = $t.data("user");
		var action = $t.attr("data-action");
		var count = $t.attr("data-count");
		var disp = $t.attr("data-disp");
		var data = {user:user, taskAction:action};
		
		if(!_check_for_sun($t, user)){
		
			do_avatar_like($t, action, count, disp);
			
			$.ajax({
				
				url:"/avatars",
				method:"post",			
				data:data,				
				success:function(res){
					
				}	
					
			});
		
		}
		
		return false; //PREVENT DEFAULT	
			
	});	
		
		
		
	////CLEAR DP LIKES///////
	$(".clear_dp_likes").click(function(){
				
		$.ajax({
							
			url:"/clear-avatar-likes",
			method:"post",
			success:function(res){
									
				location.reload(true);
				
			},complete:function(){
							
				location.reload();				
			
			}
			
		});				
			
		return false; //PREVENT DEFAULT					
				
	});
		
		
	////SHARE FUNCTION/////
	function do_share($t, action, count, disp){
		
		var css, res = '', raw_res, share_link;			
		css = $t.attr("data-css");				
		action = action.trim();		
		count = parseInt(count);		
		
		if(action == "share"){
	
			res = (count + 1);
			share_link = $t.attr("href").replace("/share", "/unshare");
			$t.attr({"data-count": res,"href": share_link,"data-action": "unshare", "title": "Unshare this post"});
			$t.addClass(css).find(".far").addClass(css);
			$t.children(":last-child").html('&nbsp;Unshare');			
			res +=  (res > 1)? ' Shares ' : ' Share ';
	
		}else if(action == "unshare"){				
		
			res = raw_res = (count - 1);
			res = (res >= 1 )?  res : '';
			share_link = $t.attr("href").replace("/unshare", "/share");
			$t.attr({"data-count": raw_res,"href": share_link,"data-action": "share", "title": $t.attr("title").replace("Unshare", "Share")});	
			$t.removeClass(css).find(".far").removeClass(css);			
			$t.children(":last-child").html('&nbsp;Share');
			res +=  (res > 1)? ' Shares ' : (res? ' Share ' : '');				
		
		}
		
		$('#' + disp).text(res);				
		
	}
				
						
		
	$(".share").click(function(){				
		
		var $t = $(this);	
		var postId =  $t.attr("data-pid");
		var action =  $t.attr("data-action");		
		var count =  $t.attr("data-count");		
		var disp =  $t.attr("data-disp");		
		var data = "postId="  + postId + "&taskAction=" + action ;					
		
		do_share($t, action, count, disp);
		
		$.ajax({
			
			url:"/post-shares",
			method:"post",
			data:data,			
			success:function(res){												
				
			}						
			
		});				
		
		return false; //PREVENT DEFAULT					
		
	});
	

	//ANIMATE SOCIAL SHARE ICONS
	$(".soc-icon").on("mouseleave mouseenter", function(e){

		$(this).closest(".soc-sharer")[(e.type === 'mouseleave'? 'add' : 'remove') + 'Class']("animated bounce");

	});


	
	
	////FUNCTION TO UPDATE SCATS IN SEARCH ON CAT CHANGE/////	
		
	$("#categories").change(function(){				
		
		var $t = $(this);
		var cat = $t.val();			
		cat = {cat:cat};		
				
		$.ajax({
					
			url:"/search",
			method:"post",
			data:cat,
			beforeSend:function(){				
		
				toggle_preloader({context:$t.parent()});				
		
			},success:function(res){
						
				if($("#sections").val() != "")				
					$("#sections").empty();
								
				$("#sections").append(res);
				toggle_preloader({context:$t.parent(), remove:true});						
				
			}			
			
		});		
		
	});


	///////FOLLOW A MEMBER/////
	function do_mfollow($t, action, disp){
		
		var res = $tmp = flwBase = flw1 = sess = '' ;
		flw1 = !$('.flws-base').find('.track-fm-append').length;
		flwBase = $('.flws-base' + (flw1? '' : ' .track-fm-append'));					
		action = action.trim();		
		sess = _doc.sessionUser.toLowerCase();
			
		if(action == "follow"){
			
			$(".mem-unf-base.fm-n1").show();			
			n_link = $t.attr("href").replace("/follow", "/unfollow");
			$t.attr("href", n_link);
			$t.attr("data-action", "unfollow");	
			$t.text("unfollow");
			$tmp = '<a href="/' + sess + '" class="links ' + sess + '-unfld">You</a>';
			res = $tmp + ' are following this member ';
			
			if($t.attr("data-count-holder") && sess){
							
				dch = $t.attr("data-count-holder");
				newC = parseInt($('.mem-unf-base .' + dch).text()) + 1;				
				$('.mem-unf-base .' + dch).text(newC);
				//($newC > 1)? $('.flws-base .track-fm-append').before('<b class="' + sess + '-unfld">, </b>' + $tmp) : $('.flws-base .track-fm-append').before($tmp);
				(newC > 1)? flwBase[(flw1? 'append' : 'before')]('<b class="' + sess + '-unfld">, </b>' + $tmp) : flwBase[(flw1? 'append' : 'before')]($tmp);
			
			}
			
		}else if(action == "unfollow"){	
				
			n_link = $t.attr("href").replace("/unfollow", "/follow");
			$t.attr("href", n_link);
			$t.attr("data-action", "follow");
			$t.text("Follow this member");
		
			if($t.attr("data-count-holder") && sess){	
					
				dch = $t.attr("data-count-holder");
				newC = parseInt($('.mem-unf-base .' + dch).text()) - 1;				
				$('.mem-unf-base .' + dch).text(newC);
				$(".mem-unf-base ." + sess + "-unfld").hide();
		
			}
		
		}
		
		$('#' + disp).html(res);												
		
		
	}

		
	$(".follow-member").click(function(){
			
		var $t = $(this);
		var user = $t.attr("data-user");
		var action = $t.attr("data-action");
		data = "user=" + user + "&taskAction=" + action;
				
		if(!_check_for_sun($t, user, 'You can`t follow yourself!')){
			
			do_mfollow($t, action, "mf-disp");
			
			$.ajax({
						
				url:"/members-follows",
				method:"post",
				data:data,			
				success:function(res){				
					
				}
		
			});
		
		}
		
		return false; //PREVENT DEFAULT	
		
	});


	////FOLLOW TOPICS///////
	function do_tfollow($t, action, disp){
		
		var res,tgt;		
		tgt = ($t.attr("data-twin"))? $(".follow_topic") : $t ;					
		action = action.trim();				
		
		if(action == "follow"){	
					
			n_link = $t.attr("href").replace("/follow", "/unfollow");
			tgt.attr("href", n_link);
			tgt.attr("data-action", "unfollow");	
			tgt.text("unfollow");	
			res = '<a href="/' + _doc.sessionUser + '" class="links">You</a> are following this topic ';
		
		}else if(action == "unfollow"){
					
			n_link = $t.attr("href").replace("/unfollow", "/follow");
			tgt.attr("href", n_link);
			tgt.attr("data-action", "follow");
			tgt.text("Follow this topic");
		
			if($t.attr("data-base"))
				$('#' + $t.attr("data-base")).hide();
									
			if($t.attr("data-count-dec")){
		
				dcd = $t.attr("data-count-dec");
				newC = parseInt($('#' + dcd).text()) - 1;				
				$('#' + dcd).text(newC);
		
				if(newC == 0) $(".unf-all-cup").hide();
		
			}
		
		}
		
		$('.' + disp).html(res);												
				
	}

		
	$(".follow_topic").click(function(){
		
		var $t = $(this);
		var tid = $t.attr("data-tid");
		var action = $t.attr("data-action");		
		data = "tid=" + tid + "&taskAction=" + action ;
		
		do_tfollow($t, action, "tf-disp");
		
		$.ajax({
					
			url:"/topic-follows",
			method:"post",
			data:data,			
			success:function(res){						
				
			}
		
		});
		
		return false; //PREVENT DEFAULT	
		
	});



	////FOLLOW SECTION///////
	function do_sfollow($t, action, sid){
		
		var K;
		twin = ($t.attr("data-twin"))? true : false;
		sname = ($t.attr(K="data-sname"))? $t.attr(K) : false;
		tgt = twin? $(".follow_scat") : $t;					
		action = action.trim();		
		
		if(action == "follow"){	
					
			n_link = $t.attr("href").replace("/follow", "/unfollow");
			tgt.attr("href", n_link);
			tgt.attr("data-action", "unfollow");	
			tgt.text("Unfollow this section");
			twin? (tgt.attr("title", 'you are following the ' + sname + ' section, click to unfollow')) : '';
		
		}else if(action == "unfollow"){
					
			n_link = $t.attr("href").replace("/unfollow", "/follow");
			tgt.attr("href", n_link);
			tgt.attr("data-action", "follow");
			tgt.text("Follow this section");
			twin? (tgt.attr("title", 'click to follow the ' + sname + ' section')) : '';
		
			if($t.attr("data-base")){
		
				$('#' + $t.attr("data-base")).hide();
				
				if(sid == 'all'){
		
					$('#fsc').text(0);
		
				}else{
		
					res = parseInt($('#fsc').text());
					$('#fsc').text(res - 1);
		
				}
		
			}
		
		}
		
		//$('#' + $disp).html(res);												
				
	}


	$(".follow_scat").click(function(){
		
		var $t = $(this);
		var sid = $t.attr("data-sid");
		var action = $t.attr("data-action");		
		
		data =  "sid=" + sid +"&taskAction=" + action;
		
		do_sfollow($t, action, sid);		
		
		$.ajax({
					
			url:"/section-follows",
			method:"post",
			data:data,			
			success:function(res){			
				
			}
		
		});
		
		return false; //PREVENT DEFAULT	
		
	});
	
	
	////DELETE A CAMPAIGN///
	$(".confirm-camp-del").click(function(){
		
		var $t = $(this);
		var grant = $t.val();					
		grant = grant.trim();		
		
		if(grant == "OK"){
			
			var campaign_id  = $t.attr("data-campaign-id");
			var campaign  = $t.attr("data-campaign");
	
			if(campaign_id != "all"){
								
				$t.closest(".campaign-base").hide();	
				var $old_tc = parseInt($("#tc-count").text());
				$("#tc-count").text($old_tc - 1);
			
			}else if(campaign_id == "all"){	
							
				$(".campaign-base").hide();	
				$("#del-all-hbase,.pagination").hide();
				$("#tc-count").text(0);	
					
			}				
			
			var data = "ad=" + campaign_id + "&campaign=" + campaign + (($t.attr("data-adm-ref"))? ('&adm-ref=' + $t.attr("data-adm-ref")) : '');
			
			$.ajax({
				
				url:"/delete-campaign",
				method:"post",
				data:data,
				success:function(res){															
										
				}
				
			});
		
		}
		
		return false; //PREVENT DEFAULT	
		
	});

	
		
	////REMOVE  CAMPAIGN FROM A SECTION/////////

	$(".remove-placement").click(function(){
		
		var $t = $(this);
		var ad  = $t.attr("data-ad-id");
		var section = $t.attr("data-sid");
		var campaign = $t.attr("data-campaign");
		var active_only = ($t.attr("data-active-only"))? 'true' : '';
		
		var data = {ad:ad, section:section, campaign:campaign, active_only:active_only};		
		var base = ($t.attr("data-hide-all"))? $t.closest(".aplc-base") : $t.closest(".per-plc-rmv-base");		

		base.hide();
		
		$.ajax({
				
			url:"/remove-ad-from-section",
			method:"post",
			data:data,
			beforeSend:function(){
						
				toggle_preloader({context:$t.parent()});
		
			},success:function(res){
			
				if(active_only) location.reload();
				
			}
						
		});	
				
		return false; //PREVENT DEFAULT	
			
	});
	
	

	////RESET CUBANK///////
	$(".reset-cubank").click(function(){
			
		$("#cubank").text('0.00');
		
		$.ajax({
						
			url:"/reset-cubank",
			method:"post"
			
		});
			
		//return false; //PREVENT DEFAULT
				
	})
	
	
	

	////RESUME OR PAUSE CAMPAIGN///////

	$(".campaign_stat").click(function(){
			
		var $t = $(this);
		var rpt = $t.attr("data-rpt");
				
		$.ajax({
						
			url:"/rp-campaign",
			method:"post",
			data:rpt,
			beforeSend:function(){	
						
				toggle_preloader({context:$t.parent()});
			
			},success:function(res){
										
			},complete:function(){
					
				location.reload();
			
			}
								
		});
								
		return false; //PREVENT DEFAULT	
								
	});

		
	
	
	//Handle For Appending Dynamic Fields Values To Moderation Backend Data
	function appendBendDynamicData(jsData){		
		
		if(jsData.fEnd.dynamicFields !== undefined){

			//Accummulate and append the comma separated dynamic field parameters
			var dynamicData = '', dynamicFieldsArr = jsData.fEnd.dynamicFields.split(',');

			for(var x in dynamicFieldsArr){

				dynamicData += '&' + jqIdDom(dynamicFieldsArr[x]).attr('name') + '=' + jqIdDom(dynamicFieldsArr[x]).val();

			}

			jsData.bEnd.data = jsData.bEnd.data + dynamicData;

		}

		return jsData;
		
	}


	
	//Moderation CMS Frontend Data Update Handle
	function updateFendModCmsData($t, cmsDataKey, updateKey, updateVal){
		
		if($t.attr(cmsDataKey)){

			var jsData = JSON.parse($t.attr(cmsDataKey));				
			jsData.fEnd[updateKey] = updateVal;			
			$t.attr(cmsDataKey, JSON.stringify(jsData));
						
		}
		
	}


	
	//Moderation Frontend Alteration Handle
	function doModStatefulFendAlter(tgt, jsData, cmsBtn, isStateReversal){
		
		var ctxTgt, stateVal, titleReplace1, titleReplace2, mutableContent, textContent, clsToggle, visToggle,
			dataClsGrp = 'data-cls-grp', tgtGrp = tgt.attr(dataClsGrp);
			
		tgtGrp? (tgt = jqAttrDom(dataClsGrp + '="' + tgtGrp + '"')) : '';

		if(isStateReversal){

			stateVal = 0;
			titleReplace1 = jsData.fEnd.actTxt2;
			titleReplace2 = jsData.fEnd.actTxt1;
			mutableContent = jsData.fEnd.modalMutable1;
			textContent = jsData.fEnd.actTxt1;
			clsToggle = 'remove';
			visToggle = 'hide';

		}else{

			stateVal = 1;
			titleReplace1 = jsData.fEnd.actTxt1;
			titleReplace2 = jsData.fEnd.actTxt2;
			mutableContent = jsData.fEnd.modalMutable2;
			textContent = jsData.fEnd.actTxt2;
			clsToggle = 'add';
			visToggle = 'show';

		}

		tgt.each(function(){

			var $t = $(this);

			updateFendModCmsData(jqIdDom($t.attr('data-id-targets')).find(jqAttrDom(jsData.fEnd.cms, true)), jsData.fEnd.cms, 'state', stateVal);			
			jqClassDom(jsData.fEnd.stickerBox)[visToggle]();

			//Update Text
			$t.text(textContent);
			
			//Update Title
			if($t.attr('title'))
				$t.attr('title', ($t.attr('title').replace(titleReplace1, titleReplace2)));
			
			//Update Mutables
			if(jsData.fEnd.modalMutableBox !== undefined)				
				$t.closest(jqClassDom(_mods.cms.base, true)).find(jqClassDom(jsData.fEnd.modalMutableBox, true)).html(mutableContent);												


		});
				

		/////GET RELATED DELETE BTN AND INSTALL STATE LOCK ON THEM 
		if(jsData.fEnd.lockTgtDelGrp !== undefined){

			jqClassDom(jsData.fEnd.lockTgtDelGrp.tgtGrp).each(function(){
				
				updateFendModCmsData($(this), jsData.fEnd.lockTgtDelGrp.tgtGrpCms, 'lock', stateVal);												

			})

		}	
		
		//Execute Context Action
		if(jsData.fEnd.doCtxTgt !== undefined){
				
			ctxTgt = cmsBtn.closest(jqClassDom(jsData.fEnd.doCtxTgt, true));
			
			(	(jsData.fEnd.doCtxByClsFind !== undefined)? 
				ctxTgt.find(jqClassDom(jsData.fEnd.doCtxByClsFind, true))
				:
				ctxTgt
			)[clsToggle + 'Class'](jsData.fEnd.doCtxCls);

		}
		
	}
		
	
	//Moderation prompt confirmation/
	function modCmsPromptOk(cmsBtn){
		
		var acknowledgement = false;
		
		cmsBtn.attr("value")? (acknowledgement = (cmsBtn.attr("value").trim().toLowerCase() == "ok")) : '';

		return acknowledgement;

	}

	
	//Moderation Rank Authorization
	function modsRankAuthorization(tgt, jsData, smartToggleData){

		var rankAuthorization = false;

		if(jsData.fEnd.rank == 0){	
			
			tgt.closest(jqClassDom(_mods.cms.base, true)).next(jqClassDom(jsData.fEnd.alertBox, true)).html(jsData.fEnd.rankMsg);
			smartToggleData.opener.target.el.hide();		
		
		}else if(jsData.fEnd.rank == 1){ 
			
			rankAuthorization = true; 

		}

		return rankAuthorization;

	}

	
	
	
	//Moderation CMS common Handle For Deciding Execution Target Buttons
	function decideModTargetBtn(cmsBtn){
		
		var tgtBtn = cmsBtn, 
		smartToggleData = cmsBtn.closest(jqAttrDom(_smartToggler.openerAttr, true)).data(_smartToggler.dbName);		
		smartToggleData? (tgtBtn = smartToggleData.opener.toggler.el) : '';

		return {tgt: tgtBtn, smartToggleData: smartToggleData};

	}

	
	
	//Moderation CMS common Handle For Stateful Executions (2-way state)
	function statefulModCmsHandle(cmsBtn, jsData){
		
		var tgtObj = decideModTargetBtn(cmsBtn), tgt = tgtObj.tgt, smartToggleData = tgtObj.smartToggleData;		
		
		if(modCmsPromptOk(cmsBtn) && modsRankAuthorization(tgt, jsData, smartToggleData)){									
			
			if(jsData.fEnd.state == 0){
				
				doModStatefulFendAlter(tgt, jsData, cmsBtn, false);
				
			
			}else if(jsData.fEnd.state == 1){
				
				doModStatefulFendAlter(tgt, jsData, cmsBtn, true);
				
			}
			
			jsData = appendBendDynamicData(jsData);
			toggle_preloader({context: cmsBtn});
			
			$.ajax({
									
				url: jsData.bEnd.url,
				method: "post",
				dataType: "json",
				data: jsData.bEnd.data,			
				success:function(data){
					
					tgt.closest(jqClassDom(_mods.cms.base, true)).next(jqClassDom(jsData.fEnd.alertBox, true)).html(data.res);
					toggle_preloader({context: cmsBtn, remove: true});
		
				},
				error:function(r){
					//alert(r.responseText)
				}
				
			});	

			smartToggleData.opener.target.el.hide();
			//cmsBtn.closest(jqClassDom(jsData.fEnd.modalBox, true)).hide();

		}
		
		return false; //PREVENT DEFAULT

	}




	

	//Moderation Handle For Stateless Executions (1-way state)
	function statelessModCmsHandle(cmsBtn, jsData){

		var tgtObj = decideModTargetBtn(cmsBtn), tgt = tgtObj.tgt, smartToggleData = tgtObj.smartToggleData;
		
		toggle_preloader({context: cmsBtn});
		
		if(modCmsPromptOk(cmsBtn) && modsRankAuthorization(tgt, jsData, smartToggleData)){
			
			if(jsData.fEnd.lock == 1){
				
				tgt.closest(jqClassDom(_mods.cms.base, true)).next(jqClassDom(jsData.fEnd.alertBox, true)).html(jsData.fEnd.lockMsg);
				cmsBtn.closest(jqClassDom(jsData.fEnd.modalBox, true)).hide();
				toggle_preloader({context: cmsBtn, remove: true, emptyOnly: true});				
				
			}else{																		

				jsData = appendBendDynamicData(jsData);
				
				$.ajax({
									
					url: jsData.bEnd.url,
					method: "post",
					dataType: "json",
					data: jsData.bEnd.data,
					success: function(data){	
												
						tgt.closest(jqClassDom(_mods.cms.base, true)).next(jqClassDom(jsData.fEnd.alertBox, true)).html(data.res);						
						toggle_preloader({context: cmsBtn, remove: true});
						
						if(!data.error){
							
							if(jsData.fEnd.doClsHide !== undefined){

								jqClassDom(jsData.fEnd.doClsHide).hide();
								
							}		
							
							jqClassDom(jsData.fEnd.stickerBox).show();

						}
						//location.reload();
											
					}
					
				});
				
				smartToggleData.opener.target.el.hide();
				//cmsBtn.closest(jqClassDom(jsData.fEnd.modalBox, true)).hide();
		
			}
		
		}
		
		return false; //PREVENT DEFAULT

	}



	
	/*** THREAD CMS EVENTS ***/

	//RENAME TOPIC ON THE FLY
	//Bind Event For Topic Rename CMS
	jqClassDom(_mods.cms.base).on('click', jqAttrDom(_mods.cms.trn, true), function(){
		
		statelessModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.trn)));
		
	});	

	//MOVE TOPIC ON THE FLY
	//Bind Event For Topic Move CMS
	jqClassDom(_mods.cms.base).on('click', jqAttrDom(_mods.cms.tm, true), function(){
		
		statelessModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.tm)));
		
	});

	//PROTECT TOPIC ON THE FLY
	//Bind Event For Topic Protect CMS
	jqClassDom(_mods.cms.base).on('click', jqAttrDom(_mods.cms.tpr, true), function(){
		
		return statelessModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.tpr)));
		
	});	

	//OPEN TOPIC ON THE FLY
	//Bind Event For Topic Open CMS
	jqClassDom(_mods.cms.base).on('click', jqAttrDom(_mods.cms.to, true), function(){
		
		return statelessModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.to)));
		
	});	

	//CLOSE TOPIC ON THE FLY
	//Bind Event For Topic Close CMS
	jqClassDom(_mods.cms.base).on('click', jqAttrDom(_mods.cms.tc, true), function(){
		
		return statelessModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.tc)));
		
	});	

	//DELETE TOPIC ON THE FLY
	//Bind Event For Topic Delete CMS
	jqClassDom(_mods.cms.base).on("click", jqAttrDom(_mods.cms.td, true), function(){		

		return statelessModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.td)));
		
	});
	
	//LOCK TOPIC ON THE FLY
	//Bind Event For Topic Lock CMS
	jqClassDom(_mods.cms.base).on('click', jqAttrDom(_mods.cms.tl, true), function(){
		
		return statefulModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.tl)));
		
	});	

	//FEATURE TOPIC ON THE FLY
	//Bind Event For Topic Feature CMS
	jqClassDom(_mods.cms.base).on('click', jqAttrDom(_mods.cms.tft, true), function(){
		
		return statefulModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.tft)));
		
	});	
	
	//PIN TOPIC ON THE FLY
	//Bind Event For Topic Pin CMS
	jqClassDom(_mods.cms.base).on('click', jqAttrDom(_mods.cms.tpn, true), function(){
		
		return statefulModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.tpn)));
		
	});	
	
	//TAG TOPIC HOT ON THE FLY
	//Bind Event For Topic Tag Hot CMS
	jqClassDom(_mods.cms.base).on('click', jqAttrDom(_mods.cms.tth, true), function(){
		
		return statefulModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.tth)));
		
	});	
	


	




	/*** POST CMS EVENTS ***/

	
	//DELETE POST ON THE FLY
	//Bind Event For Post Delete CMS	
	jqClassDom(_mods.cms.base).on("click", jqAttrDom(_mods.cms.pd, true), function(){		

		return statelessModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.pd)));
		
	});
	
	//LOCK POST ON THE FLY
	//Bind Event For Post Lock CMS
	jqClassDom(_mods.cms.base).on('click', jqAttrDom(_mods.cms.pl, true), function(){
		
		return statefulModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.pl)));		
		
	});
	
	//HIDE POST ON THE FLY
	//Bind Event For Post Hide CMS
	jqClassDom(_mods.cms.base).on('click', jqAttrDom(_mods.cms.ph, true), function(){
		
		return statefulModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.ph)));		
		
	});
	
	//PIN POST ON THE FLY
	//Bind Event For Post Pin CMS
	jqClassDom(_mods.cms.base).on('click', jqAttrDom(_mods.cms.ppn, true), function(){
		
		return statefulModCmsHandle($(this), JSON.parse($(this).attr(_mods.cms.ppn)));		
		
	});


	
	
	
	/////FUNCTION TO POPULATE MODS SECTION SELECT LIST//////
	(function(){

		var $t = $(".mods-section-select-list");

		toggle_preloader({context: $t.parent(), insertTarget: $t, insertMethod:'after'});

		$.ajax({

			url:'/',
			method:"post",
			dataType:"json",
			data:{ajaxPopulateModsSectionSelectList: true},
			success:function(data){

				$t.html(data.options);
				toggle_preloader({context: $t.parent(), insertTarget: $t, insertMethod:'after', remove: true});

			}

		});

	})();

		
	////POP MODS ACTION CONFIRMATION DROPS//////		
	$(".mod-actions").on("change click", function(e){modActionsDropPaneToggler(false, e)});
	
	function modActionsDropPaneToggler(elId, e){
		
		var $t = elId? $(elId) : $(e.target), clsDot = '.', dash = '-', opt = tgt = metas = topicValuesMeta = 
		postValuesMeta = keysMeta = topicTgtPrefix = postTgtPrefix = tgtSel = '';
		
		if(opt = $t.val()){
				
			opt = opt.toLowerCase();
			tgt = $t.parent().next(".mod-pops");		
			tgt.children().hide();
			metas = JSON.parse($t.attr("data-metas"));
			topicValuesMeta = metas.values.topic;
			postValuesMeta = metas.values.post;
			keysMeta = metas.keys;
			topicTgtPrefix = clsDot + keysMeta.topicTgtTitle + dash;
			postTgtPrefix = clsDot + keysMeta.postTgtTitle + dash;
					
			switch(opt){
			
				case topicValuesMeta.feature: tgtSel = topicTgtPrefix + keysMeta.feature; break;
				
				case topicValuesMeta.rename: tgtSel = topicTgtPrefix + keysMeta.rename; break;
			
				case topicValuesMeta.delete: tgtSel = topicTgtPrefix + keysMeta.delete; break;
			
				case topicValuesMeta.move: tgtSel = topicTgtPrefix + keysMeta.move; break;

				case topicValuesMeta.protect: tgtSel = topicTgtPrefix + keysMeta.protect; break;

				case topicValuesMeta.lock: tgtSel = topicTgtPrefix + keysMeta.lock; break;

				case topicValuesMeta.tagHot: tgtSel = topicTgtPrefix + keysMeta.tagHot; break;

				case topicValuesMeta.pin: tgtSel = topicTgtPrefix + keysMeta.pin; break;
				
				case postValuesMeta.pin: tgtSel = postTgtPrefix + keysMeta.pin; break;
				
				case postValuesMeta.lock: tgtSel = postTgtPrefix + keysMeta.lock; break;		
				
				case postValuesMeta.hide: tgtSel = postTgtPrefix + keysMeta.hide; break;
				
				case postValuesMeta.delete: tgtSel = postTgtPrefix + keysMeta.delete; break;
			
			}

			tgt.find(tgtSel).show();

		}
			
	}modActionsDropPaneToggler("#topic-mod-form-actions,#post-mod-form-actions"); //call on document load as well
			
	
	//Dynamically load mod form current values from the inputted topic id
	jqAttrDom('data-mod-currents').each(function(){ //call on document load as well

		populateModFormCurrVals($(this)); 
					
		$(this).on("blur", function(e){populateModFormCurrVals(false, e)});		

	});

	function populateModFormCurrVals($t, e){

		var $t = e? $(e.target) : $t, tid = $t.val(), clsSelPref = ',.t-' + tid, idSelPref = '#mod-page',
		threadUrlSuf = '-mod-form-thread-url', sectionUrlSuf = '-mod-form-curr-section',
		protectionSuf = '-mod-form-curr-protection';
		
		$.ajax({

			url:'/',
			method:"post",
			dataType:"json",
			data:{ajaxPopulateModFormCurrVals: true, tid: tid},
			success:function(data){
				
				$(idSelPref + threadUrlSuf + clsSelPref + threadUrlSuf).html(data.threadUrl);
				$(idSelPref + sectionUrlSuf + clsSelPref + sectionUrlSuf).html(data.currSectionUrl);
				$(idSelPref + protectionSuf + clsSelPref + protectionSuf).html(data.currProtection);								

			},
			error:function(r){
				//alert(r.responseText)
			}

		})

	}
	
		
	
	

	////FORGET SAVED PAYMENT CARD///////
	$(".forget-pay-card").click(function(){
			
		$.ajax({
						
			url:$(this).attr('data-url'),
			method:"post",
			data:{cardSignature:$(this).attr('data-card-token')},
			success:function(res){
				
				//alert(res)
									
			}
			
		});
				
	})
	
	

	
	/////FUNCTION TO HANDLE AJAX FORM SUBMIT///////

	var myAjaxEngine = function(e){
		
		var tgt = $(this), options = JSON.parse(tgt.attr(_doc.ajaxEngine.runner)),
		dataType = options.dataType || 'json', preloader = options.preloader || true,
		runBox = options.runBox, reloadBox = options.reloadBox, keepDft = options.keepDft,
		reloadUrl = options.reloadUrl, resetForm = options.resetForm, 
		reloadTime = options.reloadTime || 1000 /* 1ms */,		
		runUrl = tgt.attr("href"), method = 'POST',  formData;

		keepDft? '' : e.preventDefault(); //KEEP DEFAULT IF ALLOWED//

		//If it's already busy return
		if(tgt.attr(_doc.control.busyAttr))
			return false;
		else //Add Busy Control
			tgt.attr(_doc.control.busyAttr, true);
		
		if(tgt.is('form')){
			
			formData = new FormData(this);	
			runUrl = tgt.attr("action");
			method = tgt.attr("method");

		}
		  
		if(preloader)
			toggle_preloader({context: tgt});
			
		$.ajax({			
			url: runUrl,
			method: method,
			data: formData,
			dataType: dataType,
			contentType: false, //required
			processData: false, //required
			success: function(data){				
				
				(runBox? $('#' + runBox) : tgt).after(data.res);
				
				if(resetForm)
					tgt[0].reset();
										
				if(preloader)
					toggle_preloader({context: tgt, remove:true});		
				
				if(reloadUrl){
					
					$.ajax({			
						url: reloadUrl,
						method: method,
						dataType: dataType,
						contentType: false, //required
						processData: false, //required
						success:function(data){
							
							setTimeout(
								function(){
									
									(reloadBox? $('#' + reloadBox) : tgt.closest('[' + _doc.ajaxEngine.reloadBox + ']')).html(data.res);
									
									tgt.removeAttr(_doc.control.busyAttr); //Remove Busy Control

								},

								reloadTime
							);

						}

					});

				}else
					tgt.removeAttr(_doc.control.busyAttr); //Remove Busy Control

			}
		})		
	};
	
	$(document).on("submit", "form["+ _doc.ajaxEngine.runner +"]", myAjaxEngine);
	$(document).on("click", "a["+ _doc.ajaxEngine.runner +"]", myAjaxEngine);
		
		
	
	
	
	/////FUNCTION TO HANDLE AJAX FORM SUBMIT///////
	
	$('[data-ajax-submit]').on("click", function(e){
		
		var $form = $(this).closest('form'), retType, noSpin, submitName, resHolder, formData;
		
		retType = $form.attr('data-retType') || 'json';
		noSpin = $form.attr('data-nospin') || false,
		submitName = ($form.attr('data-submit-name'))? '&' + $form.attr('data-submit-name') : '&submit=true'; 
		 
		$form.attr("data-keep-default")? '' : e.preventDefault(); //KEEP DEFAULT IF ALLOWED//
		resHolder = ($form.attr('data-response-holder'))? $form.attr('data-response-holder') : '#ajax-res';
		formData = $form.serialize() + submitName;
				
		if($form.attr('data-alter-submit-name')){
		
			alterSep = '|';
			alterArr = $form.attr("data-alter-submit-name").split(alterSep);
			$form.attr('data-submit-name', alterArr[1]);
			$form.attr('data-alter-submit-name', alterArr[1] + alterSep + alterArr[0]);
		
		}

		
		$.ajax({
			url:$form.attr("action"),			
			method:$form.attr("method"),
			data:formData,
			dataType:retType,
			beforeSend:function(){
				
				if(!noSpin)
					toggle_preloader({context:$form});
					
			},
			success:function(data){	
				
				var res = data.res,
				rdr = data.rdr, reload = data.reload,
				resetForm = data.resetForm;
		
				if(rdr){
				
					location.assign(rdr);
				
				}else{
				
					if(!noSpin)
						toggle_preloader({context:$form, remove:true});
				
					$form.find(resHolder).html(res);
					
					if(resetForm)
						$form[0].reset();

					if(reload)						
						location.reload();
									
				}
				
			}
				
		});
		
		
	});
	
	
	

	////AJAX FORM PLUGGING HANDLE////
	var progressBar = '#progress', fpUploadCancelBtn = '#ajax-fp-cancel', fpResponse = '#ajax-fp-response';
	
	$(".ajax-fp-submit").ajaxForm({	
						
		target:fpResponse,	
		dataType:'json',
		beforeSend:function(jqxhr){	
									
			$(fpUploadCancelBtn).click(function(){
				
				jqxhr.abort();
				$(fpUploadCancelBtn).hide();
				$(progressBar).children(".ajax-process").remove();
				$(fpResponse).html('<span class="error">Upload aborted</span>');
				 
			});
				
		},beforeSubmit:function(formData, jqForm, options){	
			
			toggle_preloader({context:$(progressBar), insertMethod:'prepend'});
		
			//if(jqForm[0].files)
				$(progressBar + ',' + fpUploadCancelBtn).show();
			//alert(jqForm[0].files)
		
		},uploadProgress:function(e, position, total, percent){
			
			$("#progress-val").css("width", percent + '%').html('<span class="progress-percent">'+ percent + '%' +'</span>');			
		
		},success:function(rtxt, stxt, xhr, $form){	
				
			$(fpResponse).html(rtxt.res);
			$(fpUploadCancelBtn).hide();
		
			if(rtxt.noFile != "undefined" && rtxt.noFile)
				$(progressBar).hide();
		
			$(progressBar).children(".ajax-process").remove();
			
		
		},error:function(re){
		
			$(fpResponse).html('<span class="alert alert-danger">Upload failed; the file or one of the files is beyond the maximum file size limit the server allows</span>');
		
		},complete:function(){
		
			toggle_preloader({context:$(progressBar), remove:true});
			$(fpUploadCancelBtn).hide();
		
		},
		/*clearForm:true,*/
		resetForm:true	
		
	});




	////FUNCTION TO CANCEL USER  ACCOUNT//////

	$(".confirm_cancel").click(function(){							
		
		$('#ajax-res').html("You will be redirected shortly, please wait........");
	
		window.location.assign("/cancelaccount");						
			
	});
	
	
	/////TERMINATE USER ACCOUNT BY ADMIN//////
		
	$(".terminate_by_admin").click(function(){
		
		var  $t = $(this), uid = $t.attr("data-uid"), data = "uid_by_admin=" + uid;
		
		$.ajax({
							
			url:"/cancelaccount",
			method:"post",
			data:data,
			success:function(res){
				
				$("#ajax_res").html(res);				
				$t.closest(".modal-drop").hide();					
				
			}
			
		});
		
		return false; //PREVENT DEFAULT
		
	});
	
	
	
	
	/////SET SEEN POP//////
		
	$("#ajax-pop-out-base").on("click", ".pop-seen", function(){
		
		var $t = $(this), tknName = $t.attr("data-tkn-name"), tknRmd = $t.attr("data-tkn-rmd"),									
		tknKey = $t.attr("data-tkn-key");
		
		$.ajax({						
									
			url:"/set-si",
			method:"post",
			data:{tkn_name:tknName, tkn_rmd:tknRmd, tkn_key:tknKey},
			success:function(res){				
					
			}
			
		});							
		
	});
	
	
	CNTER=0;
	////DISPLAY POPS/////
	var POP_TI = 120000, /*2 MINS*/ POP_TMR =  POP_NOTIFICATION = '';
	
	POP_NOTIFICATION = function(){
	
							if($("#ajax-pop-out-base .center-pops").is(":visible"))
								return true;
	
							$.ajax({
								
								url:"/" + $("html").attr("data-pop-loader"),
								method:"post",
								data:{_rdr:_doc.baseRdr},
								success:function(res){
	
									if(res){
	
										$("#ajax-pop-out-base").empty().html(res);
										clearTimeout(POP_TMR);
										POP_TMR = setTimeout(POP_NOTIFICATION, POP_TI);
										AJAX_RECALLS('customScrollbar');
	
									}
	
								}
								
							});
	
						}
	
	POP_TMR = setTimeout(POP_NOTIFICATION, POP_TI);
	
	//CALL PRETIFY
	PR.prettyPrint();

	
	//REGISTER COUNT DOWN TIMING HANDLER 
	jqAttrDom(countDownDpnAttr = 'data-count-down').each(function() {
		
		var $t = $(this), options = $.extend({}, {$t: $t}, JSON.parse($t.attr(countDownDpnAttr)));
		
		startCountDown(options);
		
	});

	


	//REGISTER TIMED VISIBILITY HANDLER
	$(document).on('click', jqAttrDom(timeDpnVisAttr = _doc.timedVisAttr, true), function() {
		
		var $t = $(this), options, dispCls,
		time = ($t.attr(timeDpnVisAttr) || 0);

		dispCls = $t.attr("data-display-cls") || 'timed-visibility-scrn'
		
		$t.prev().hasClass(dispCls)? '' : $t.before('<span class="' + dispCls + '"></span>');		

		options = {

			$t: $t.prev(),
			timer: {
				time: parseInt(time) + getTime(),
				prefix: $t.attr("data-text") || 'Resend again in:',
				basic: true,
				alertExpired: false
			},
			after: {
				hide: {ignore: true},
				show: {tgt: $t}
			}
		};
		
		startCountDown(options);
		
	});
	
	
});

////END OF DOC.READY////////


///////JS+JQ CALLS//////

function isJqObj(obj){

	return (obj instanceof jQuery);
	
}


function printDivContent(divId){
	
	var divContents = isJqObj(divId)? divId.html() : document.getElementById(divId).innerHTML;
	var printWin = window.open('', '', 'left=0, top=0, toolbar=0, sta­tus=0');	
	printWin.document.write('<html><body>' + divContents + '</body></html>');	
	printWin.document.close();
	printWin.focus();
	printWin.print();	

}


//FUNCTION TO TOGGLE PRELOADERS
function toggle_preloader(metaObj){
	
	var hasSpinCls = '.spinner';
	
	spinCls = metaObj.spinClass || ''; 
	remove = metaObj.remove || false; 
	emptyOnly = metaObj.emptyOnly || false; 
	context = metaObj.context || false; 
	insertTgt = metaObj.insertTarget || context; 
	insertMethod = metaObj.insertMethod || 'append'; 
	var spinner = '<span class="spinner ' + spinCls + ' custom-spinner spinner-style-dot spinner-xs"><i></i><i></i><i></i></span>';	

	if(!context)
		return;

	if(remove)
		context.children(hasSpinCls)[(emptyOnly? 'html' : 'remove')]('');
		
	else
		context.find(hasSpinCls)[0]? '' : insertTgt[insertMethod](spinner);	
		
}





/////FUNCTION TO STRIP JQ SELECTOR DELIMITERS /////
function jqStripSelDelim(str){

	return str.replace(/[.#\[\]]/g, '');

}




/////FUNCTION TO RETURN REFERENCE TO $("." + ElementClass)/////
function jqClassDom(cls, retJqIdentifierOnly){
	
	var jqIdentifier, delim = '.';

	jqIdentifier = (cls.trim()[0] == delim? cls : delim + cls);

	if(isJqObj(cls) && !retJqIdentifierOnly)
		return cls;

	return (retJqIdentifierOnly? jqIdentifier : $(jqIdentifier));

}

/////FUNCTION TO RETURN REFERENCE TO $("#" + ElementId)/////
function jqIdDom(id, retJqIdentifierOnly){
	
	var jqIdentifier, delim = '#';

	jqIdentifier = (id.trim()[0] == delim? id : delim + id);	

	if(isJqObj(id) && !retJqIdentifierOnly)
		return id;

	return (retJqIdentifierOnly? jqIdentifier : $(jqIdentifier));

}

/////FUNCTION TO RETURN REFERENCE TO $("#" + ElementId, "." + ElementClass)/////
function jqIdClassDom(idOrCls, retJqIdentifierOnly){

	var jqIdentifier = jqIdDom(idOrCls, true)+ ',' +jqClassDom(idOrCls, true);
	
	if(isJqObj(idOrCls) && !retJqIdentifierOnly)
		return idOrCls;

	return (retJqIdentifierOnly? jqIdentifier : $(jqIdentifier));

}


/////FUNCTION TO RETURN REFERENCE TO $("[" + ElementAttr + "]")/////
function jqAttrDom(attr, retJqIdentifierOnly){
	
	var jqIdentifier = "[" + attr + "]";
	
	if(isJqObj(attr) && !retJqIdentifierOnly)
		return attr;

	return (retJqIdentifierOnly? jqIdentifier : $(jqIdentifier));

}


//JQ Data target DOM for id or class element
function jqDataTgtDom($t){	
	
	var idTgts, classTgts, sep, domCombo, idClassIsSet;

	classTgts = ($t.attr(K="data-class-targets"))? $t.attr(K) : '';
	idTgts = ($t.attr(K="data-id-targets"))? $t.attr(K) : '';
				
	if(classTgts) classTgts = '.' + classTgts.replace(",", ", .");
		
	if(idTgts) idTgts = '#' + idTgts.replace(",", ", #");
		
	sep = (classTgts && idTgts)? ',' : '';

	domCombo = classTgts + sep + idTgts;
	idClassIsSet = (classTgts || idTgts)? true : false;
	
	return {classTgts: classTgts, idTgts: idTgts, domCombo: domCombo, idClassIsSet: idClassIsSet};
	
}

	
/////FUNCTION TO RETURN REFERENCE TO document.getElementById/////
function dom(el, t){
	
	var e='';
	t = t || '';
	
	switch(t.toLowerCase()){
	
		case 'c': e = document.getElementsByClassName(el); break;
	
		case 't': e = document.getElementsByTagName(el); break;
	
		default : e = document.getElementById(el);
	
	}
	
	return e;
		
}

	
function findAncestor(el, cls){	
	
	while((el = el.parentNode) && !el.className.indexOf(cls) < 0);
	return el;
	
}


function toJsElement(e){

	return (e != null && e.jquery? e[0] : e);
	
}

function parseBool(v){
	
	if(typeof(v) === "string")
		v = v.trim().toLowerCase();
	
	switch(v){
	
		case true:
		case "true":
		case 1:
		case "1":
		case "on":
		case "yes":
			return true;
	
		default: return false;
	
	}
	
}

	
function jsRand(min, max){
	
	return (Math.round((Math.random() * max)) + min);
	
}


function callFunctionName(functionName, context /*, args */) {
	
  context = context || window;
  var args = Array.prototype.slice.call(arguments, 2);
  var namespaces = functionName.split(".");
  var func = namespaces.pop();
	
  for(var i = 0; i < namespaces.length; i++) {
	
    context = context[namespaces[i]];
	
  }
	
  return context[func].apply(context, args);
	
}


/////FUNTION TO COUNT WORDS IN A STRING//////

function wordCount(str){
	
	if(str)
		return (str.split(" ").length);
	else
		return str;
	
}


/////FUNTION TO COUNT WORDS IN A STRING//////

function inArray(val, arr){
	
	return (arr.indexOf(val) != -1);
	
}

/////FUNTION TO BLINK ELEMENTS//////

function blink(){
	
	$(".blink").fadeOut(10000);
	$(".blink").fadeIn(10000);
	
}

setInterval(blink, 1000);////BLINK EVERY SECONDS/////////


function sleep(ms){

	return new Promise(resolve => setTimeout(resolve, ms));

}

function getTime(unit){

	var  t = 0, msT = new Date().getTime(), sT = Math.floor(msT / 1000),
	mT =  Math.floor(sT / 60), hT = Math.floor(mT / 60), dT = Math.floor(hT / 24),
	unitMs = 'ms', unitS = 's', unitM = 'm', unitH = 'h', unitD = 'd';
	unit = unit || unitS;

	switch(unit.toLowerCase()){

		case unitMs: t = msT; break;
		case unitM: t = mT; break;		
		case unitH: t = hT; break;		
		case unitD: t = dT; break;		
		case unitS: 
		default: t = sT;
	}

	return t;

}



function startCountDown(options){
	
	var eventTime = options.timer.time, $t = options.$t, afterHideTgt = options.after.hide.tgt,
	afterHideIgnore = options.after.hide.ignore, afterShowTgt = options.after.show.tgt, 
	timerStyle = options.timer.style, basicTimer = options.timer.basic, alertExpired = options.timer.alertExpired,
	displayEle = options.timer.display, basicPreTxt = options.timer.prefix || '';
	
	afterHideTgt = afterHideTgt? jqIdClassDom(afterHideTgt) : (afterHideIgnore? '' : $t.parent());
	afterShowTgt = afterShowTgt? jqIdClassDom(afterShowTgt) : '';
	displayEle = displayEle? jqIdClassDom(displayEle) : $t;
	basicPreTxt? (basicPreTxt = basicPreTxt + ' ') : '';
	timerStyle = timerStyle || '';

	if(afterShowTgt) afterShowTgt.hide(); //Hide target and show only when the timer elapses
	
	function updateTimer(){
		
		/////CONVERT JAVASCRIPT TIME NOW TO PHP TIME NOW BOTH IN SECONDS//////////////////////
		var timeNow = getTime(),
		rem = Math.abs(eventTime - timeNow),					
		s = Math.floor(rem), 
		m = Math.floor(s/60),
		h = Math.floor(m/60),
		d = Math.floor(h/24);
		
		h %= 24;
		m %= 60;
		s %= 60;
		
		var dUnit = 'Day'+ (d > 1? 's' : ''),
		hUnit = 'Hour'+ (h > 1? 's' : ''),
		mUnit = 'Minute'+ (m > 1? 's' : ''),
		sUnit = 'Second'+ (s > 1? 's' : ''), 
		basicFormat = '';

		if(d)
			basicFormat += d + ' ' + dUnit;
		
		if(h)
			basicFormat += (d? ' ' : '') + h + ' ' + hUnit;		
		
		if(m)
			basicFormat += (h? ' ' : '') + m + ' ' + mUnit;		

		if(s)
			basicFormat += (m? ' ' : '') + s + ' ' + sUnit;
				

		
		var timer =	basicTimer? '<b class="blue">'+ basicPreTxt + basicFormat +'</b' :
					'<div class="countdown-timer '+ timerStyle +'">\
					  <div>\
						<span class="days">'+ d +'</span>\
						<div class="cdt-unit">'+ dUnit +'</div>\
					  </div><div>\
						<span class="hours">'+ h +'</span>\
						<div class="cdt-unit">'+ hUnit +'</div>\
					  </div><div>\
						<span class="minutes">'+ m +'</span>\
						<div class="cdt-unit">'+ mUnit +'</div>\
					  </div><div>\
						<span class="seconds">'+ s +'</span>\
						<div class="cdt-unit">'+ sUnit +'</div>\
					  </div>\
					</div>';
			
		if(rem <= 0 || (eventTime < timeNow)){
	
			clearInterval(activeInterval);			
			timer = alertExpired? '<span class="red">TIMER EXPIRED!</span>' : '';
	
			if(afterHideTgt) afterHideTgt.hide(); //Hide target when the timer elapses
			if(afterShowTgt) afterShowTgt.show(); //Show target when the timer elapses
			
		}
			
		displayEle.html(timer);
		
		
	}
	
	updateTimer();//Run the first call b4 interval kicks in
	var activeInterval = setInterval(updateTimer,1000);
	
}

	
//FORMAT NUMBER TO THOUSAND K,M,G/B,T TYPE//
function formatNumber(num, $units_arr, dcp, sep){

	var numFmtd='',thsd_acc='',thsd_arr=[];
	$units_arr = ($units_arr)? $units_arr : ["K", "M", "G", "T"];
	dcp = (dcp || dcp === 0)? dcp : 6;
	sep = (sep)? sep : ',';
	//numFmtd = (dcp > 0)? num.toFixed(dcp) : num;			
	numFmtd = num.toFixed(dcp);			
	num = Math.round(num);
	num = num.toString();

	if(num.length > 3){

		var i=0;
		var loop = Math.ceil(num.length / 3);

		while(loop){
				
			var num_n = num.substr(-3, 3);
			num = num.substr(-0, num.length - 3);
			thsd_arr[i] = num_n;
			i++; loop--;

		}
		
		for(y=thsd_arr.length - 1; y >= 0; y--){

			thsd_acc += thsd_arr[y] + ((y >= 1)? sep : '');

		}
		
		var $thsd_arr =  thsd_acc.split(",");
		$thsd_len = $thsd_arr.length;
		numFmtd = $thsd_arr[0] + ((($thsd_len >= 2) && $thsd_arr[1][0] != 0)? '.' + $thsd_arr[1][0] : "");
		numFmtd += ($thsd_len >= 2)? ($units_arr[($thsd_len - 2)]) : "";

	}	

	return numFmtd;

}

	
//COLOR TO HEX//
function color2Hex(c){

	c = c? c.toLowerCase() : c;

	switch(c){

		case 'black': c = '#000'; break;

		case 'brown': c = '#7d4302'; break;

		case 'red': c = '#e60514'; break;

		case 'orange': c = '#f26b31'; break;

		case 'yellow': c = '#f2ea7c'; break;

		case 'green': c = '#3cb057'; break;

		case 'blue': c = '#0483de'; break;

		case 'violet': c = '#9d2ca3'; break;

		case 'gray': c = '#808080'; break;

		case 'white': c = '#fff'; break;

		case 'gold': c = '#d4af37'; break;

		case 'silver': c = '#c0c0c0'; break;

	}
	
	return c;

}




///////INITIALIZATION OF SEARCH CAT AND SCAT/////	
function initialize_sections(scat){
	
	var cat = $("#categories").val();	
	
	var data, sections = '#sections';
	
	if(scat)	
		 data = {cat:cat, sec:scat};

	else if(cat)
		 data = {cat:cat};
			
	$.ajax({
				
		url:"/search",
		method:"post",
		data:data,		
		success:function(res){
				
			if($(sections).val() != "")	
			   $(sections).empty();
						
			$(sections).append(res);
			
		}
				
	});

}
	



function imgSrcValid(url, callback, timeoutT){
	
	var timeout = timeoutT || 5000;
	var img = new Image(), timer,preTxt='loading..',timedOut=false;	
	img.src = url;
	var statObj = {url:url, width:preTxt, height:preTxt, size:preTxt};

	img.onerror = img.onabort = function(){

		if(!timedOut){

			clearTimeout(timer);
			statObj['stat'] = 'error';	
			callback(statObj);

		}

	};

	img.onload = function(){

		if(!timedOut){

			clearTimeout(timer);
			statObj['stat'] = 'ok';
			statObj['width'] = img.width;	
			statObj['height'] = img.height;	
			statObj['size'] = img.size;	
			callback(statObj);

		}

	};
		
	timer = setTimeout(function(){

		timedOut=true;
		img.src = '///!!!/###/invalidsrc.jpg'; //INVALIDATE SRC TO STOP TESTING AFTER TIMEOUT
		statObj['stat'] = 'timeout';
		callback(statObj);

	}, timeout);

}	
		
	
//notifications.requestPermission(function(e){console.log(e);})
//REGISTER SITE SERVICE WORKERS
if('serviceWorker' in navigator){	
	
	var SW_path = '/'+_doc.assetPre+'/js/main/B4esWRwNhHQ-SW.js';	
	
	navigator.serviceWorker.register(SW_path).then(function(){	
	
		console.log('service worker registered successfully!');	
	
	}).catch(function(){	
	
		console.log('service worker registration failed!');	
	
	});	
	
}else
	console.log('oops! it appears your browser does not support service worker!');	
		

	