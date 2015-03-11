(function($) 
{
$.popunder = function(sUrl) {
	var bSimple = $.browser.msie,
		run = function() {
			$.popunderHelper.open(sUrl, bSimple);
		};
	(bSimple) ? run() : window.setTimeout(run, 1);
	return $;
};

$.popunderHelper = {
	rand: function(name, rand) {
		var p = (name) ? name : 'pu_';
		return p + (rand === false ? '' : Math.floor(89999999*Math.random()+10000000));
	},

	open: function(sUrl, bSimple) {
		var _parent = self,
			sToolbar = (!$.browser.webkit && (!$.browser.mozilla || parseInt($.browser.version, 10) < 12)) ? 'yes' : 'no',
			sOptions,
			popunder;

		if (top != self) {
			try {
				if (top.document.location.toString()) {
					_parent = top;
				}
			}
			catch(err) { }
		}
		
		var w = 70;
		var h = 70;
		var livew = screen.availWidth;
		var liveh = screen.availHeight;
		var showw = (w/100) * livew;
		var showh = (h/100) * liveh;

		sOptions = 'toolbar=' + sToolbar + ',scrollbars=yes,location=yes,statusbar=yes,menubar=no,resizable=1,width=' + showw.toString();
		sOptions += ',height=' + showh.toString() + ',screenX=0,screenY=0,left=0,top=0';

		// if(jQuery.browser.mobile){
			
		// 	popunder=_parent.window.open(_parent.window.opener.location.href);
		// 	_parent.window.opener.location.href=sUrl;
			
		// }else{
			popunder = _parent.window.open(sUrl, $.popunderHelper.rand(), sOptions);			
		//}		


		if (popunder) {

			popunder.blur();
			if (bSimple) {
				window.focus();
				try { opener.window.focus(); }
				catch (err) { }
			}
			else {
				popunder.init = function(e) {
					with (e) {
						(function() {
							// if (typeof window.mozPaintCount != 'undefined' || typeof navigator.webkitGetUserMedia === "function") {
							// 	var x = window.open('about:blank');
							// 	x.close();
							// }

							try { opener.window.focus(); }
							catch (err) { }
						})();
					}
				};
				popunder.params = {
					url: sUrl
				};
				popunder.init(popunder);
			}
		}

		return true;
	}
};
})(jQuery);
function iframepopupwidow(sUrl)
{
	jQuery('#openpopunder').ready(function() {
		jQuery.popunder(sUrl);
	});
}