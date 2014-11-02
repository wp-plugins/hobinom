// from clickTab.js
var clickTabCurrent = 1;

function clickTabSwitch(switchTo) {
	document.getElementById('clickTab' + clickTabCurrent).style.display = "none";
	clickTabCurrent = switchTo;
	document.getElementById('clickTab' + clickTabCurrent).style.display = "block";
}

// from switch.js
(function($) {
	jQuery(document).ready(function() {
		jQuery(".btn-group").on("click", function(){
        jQuery(this).find(".btn").toggleClass("select").toggleClass("unselect");  
		});
	});
})(jQuery);