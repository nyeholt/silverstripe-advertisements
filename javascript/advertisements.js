
(function ($) {
    var config = {
        remember: false,
        items: [],
        endpoint: ''
    };

    $().ready(function () {

        if (!window.SSInteractives) {
            return;
        }

        config = window.SSInteractives;

        var base = $('base').attr('href');
        var recorded = {};
        
        var uuid = current_uuid();
        
        // see if we have any items to display
        if (config.items.length) {
            for (var i = 0; i < config.items.length; i++) {
                var item = config.items[i];
                add_interactive_item(item);
            }
        }

        /**
         * each time an ad link comes up, add it to the impressions
         */
        $(document).on('mouseup', '.adlink', function (b) {
            var adId = $(this).attr('adid');
            if (b.which < 3) {
                $.post(base + 'interactive-action/clk', {id: adId});
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
    
    function add_interactive_item(item) {
        var target;
        var addFunction = 'prepend';
        var effect = 'show';
        
        if (item.Element) {
            target = $(item.Element);
            if (!target.length) {
                return;
            }
        }
        
        if (item.Location != 'prepend') {
            addFunction = item.Location === 'append' ? 'append' : 'prepend';
        }
        
        if (item.Transition && item.Transition != 'show') {
            effect = item.Transition;
        }
        
        var holder = $('<div class="ss-interactive-item">').hide().append(item.Content);
        
        target[addFunction](holder);
        holder[effect]();
    };

    function current_uuid() {
        // check the URL string for a continual UUID
        var uid = null;
        if (config.remember) {
            // check in a cookie
            uid = '';
        }
        
        // check the URL string
        if (!uid) {
            uid = get_url_param('int_src');
        }

        if (!uid) {
            uid = UUID();
        }
        
        return uid;
    }


    /**
     * Fast UUID generator, RFC4122 version 4 compliant.
     * @author Jeff Ward (jcward.com).
     * @license MIT license
     * @link http://jcward.com/UUID.js
     **/
    function UUID() {
        var self = {};
        var lut = [];
        for (var i = 0; i < 256; i++) {
            lut[i] = (i < 16 ? '0' : '') + (i).toString(16);
        }
        self.generate = function () {
            var d0 = Math.random() * 0xffffffff | 0;
            var d1 = Math.random() * 0xffffffff | 0;
            var d2 = Math.random() * 0xffffffff | 0;
            var d3 = Math.random() * 0xffffffff | 0;
            return lut[d0 & 0xff] + lut[d0 >> 8 & 0xff] + lut[d0 >> 16 & 0xff] + lut[d0 >> 24 & 0xff] + '-' +
                    lut[d1 & 0xff] + lut[d1 >> 8 & 0xff] + '-' + lut[d1 >> 16 & 0x0f | 0x40] + lut[d1 >> 24 & 0xff] + '-' +
                    lut[d2 & 0x3f | 0x80] + lut[d2 >> 8 & 0xff] + '-' + lut[d2 >> 16 & 0xff] + lut[d2 >> 24 & 0xff] +
                    lut[d3 & 0xff] + lut[d3 >> 8 & 0xff] + lut[d3 >> 16 & 0xff] + lut[d3 >> 24 & 0xff];
        }
        return self;
    };


    function set_cookie(name, value, days) {
        var expires = "";
        if (!days) {
            days = 30;
        }
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + value + expires + "; path=/";
    }

    function get_cookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ')
                c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0)
                return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    function clear_cookie(name) {
        set_cookie(name, "", -1);
    }

    function get_url_param(sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };
})(jQuery);