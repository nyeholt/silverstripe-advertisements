
(function ($) {
	$().ready(function () {

		var base = $('base').attr('href');
		var recorded = {};
        
        
			impressions.push(adId);



		/**
		 * each time an ad link comes up, add it to the impressions
		 */
		$(document).on('mouseup', '.adlink', function (b) {
            var adId = $(this).attr('adid');
            if (b.which < 3) {
                $.post(base + 'adclick/clk', {id: adId});
            }
            return true;
		});

		var processImpressions = function () {
            var ads = $('.adlink');
            var ids = [];
            for (var i = 0, c = ads.length; i < c; i++) {
                var adId = $(ads[i]).attr('adid');
                if (recorded[adId]) {
                    continue;
                }
                recorded[adId] = true;
                ids.push(adId);
            }

			if (ids.length) {
				$.post(base + 'adclick/imp', {ids: ids.join(',')});
				setTimeout(processImpressions, 10000);
			}
		}
	});
})(jQuery);