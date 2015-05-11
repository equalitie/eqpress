var settimmer = 0;
jQuery(document).ready(function ($) {
	window.setInterval(function() {
		var timeCounter = $("strong[id=show-time]").html();
		var updateTime = eval(timeCounter)- eval(1);
		$("strong[id=show-time]").html(updateTime);

		if(updateTime == 0){
			window.location = (swp_url);
		}
	}, 1000);
});

