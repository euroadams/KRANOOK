function adBlockerDetect(){
	var el = dom("di-ad-blocker");
	el.textContent = 'Disabled';
	el.style.color = el.getAttribute("data-off-color");
}

