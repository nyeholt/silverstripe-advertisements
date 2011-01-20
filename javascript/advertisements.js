
(function ($) {
	$().ready(function () {
		
		var base = $('base').attr('href');
		var impressions = [];
		
		/** 
		 * each time an ad link comes up, add it to the impressions
		 */
		$('.adlink').livequery(function () {
			var adId = $(this).attr('adid');
			impressions.push(adId);
			
			$(this).mouseup(function (b) {
				if (b.which < 3) {
					$.post(base + 'adclick/clk', {id: adId});
				}
				return true;
			})
		});
		
		var processImpressions = function () {
			if (impressions.length) {
				var ids = impressions.join(',');
				$.post(base + 'adclick/imp', {ids: ids});
				impressions = [];
				
				setTimeout(processImpressions, 2000);
			}
		}
		
		processImpressions();
		
	});
})(jQuery);