
(function ($) {
    var config = {
        remember: false,
        trackviews: false,
        trackclicks: true,      // should clicks be 
        trackforward: true,     // should target links be hooked and have a uid appended?
        items: [],
        tracker: 'Local',
        endpoint: ''
    };
    
    var uuid = null;

    $().ready(function () {

        if (!window.SSInteractives) {
            return;
        }

        config = window.SSInteractives;
        if (!config.endpoint) {
            var base = $('base').attr('href');
            config.endpoint = base + 'interactive-action/trk';
        }

        var tracker = Trackers[config.tracker] ? Trackers[config.tracker] : Trackers.Local;
        
        if (!tracker) {
            return;
        } 

        var recorded = {};
        
        var uid = url_uuid();
        console.log(config);
        if (uid && config.trackforward) {
            tracker.track(get_url_param('int_id'), 'int');
        }

        // see if we have any items to display
        if (config.items.length) {
            for (var i = 0; i < config.items.length; i++) {
                var item = config.items[i];
                add_interactive_item(item);
            }
        }

        var recordClick = function (b) {
            var adId = $(this).attr('data-intid');
            if (b.which < 3) {
                tracker.track(adId, 'clk');
            }
        };

        if (config.trackclicks) {
            $(document).on('mouseup', 'a.int-link', recordClick);
        }
        
        var processViews = function () {
            var ads = $('.int-track-view');
            var ids = [];
            for (var i = 0, c = ads.length; i < c; i++) {
                var adId = $(ads[i]).attr('data-intid');
                if (recorded[adId]) {
                    continue;
                }
                recorded[adId] = true;
                ids.push(adId);
            }

            if (ids.length) {
                tracker.track(ids.join(','), 'imp');
                setTimeout(processViews, 10000);
            }
        }
        
        processViews();
    });
    
    function add_interactive_item(item) {
        var target;
        var addFunction = 'prepend';
        var effect = 'show';
        
        if (item.Frequency > 0) {
            var rand = Math.floor(Math.random() * (item.Frequency)) + 1;
            if (rand != 1) {
                // k good to go
                return;
            }
        }
        
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
        
        holder.find('a').each(function () {
            $(this).attr('data-intid', item.ID);
            $(this).addClass('int-link'); 
            if (item.TrackViews) {
                $(this).addClass('int-track-view');
            }

            if (config.trackforward) {
                var append = 'int_src=' + current_uuid() + '&int_id=' + item.ID;
                var newLink = $(this).attr('href');
                if (newLink.indexOf('?') >= 0) {
                    append = "&" + append;
                } else {
                    append = "?" + append;
                }
                $(this).attr('href', newLink + append);
            }
        })
        
        target[addFunction](holder);
        holder[effect]();
    };

    function current_uuid() {
        // check the URL string for a continual UUID
        if (uuid) {
            return uuid;
        }
        
        var uid = null;
        
        if (config.remember) {
            // check in a cookie
            uid = '';
        }
        
        // check the URL string
        if (!uid) {
            uid = url_uuid();
        }

        if (!uid) {
            uid = UUID().generate();
        }
        
        uuid = uid;
        return uid;
    }
    
    function url_uuid() {
        return get_url_param('int_src');
    }
    
    var Trackers = {};
    
    
    Trackers.Google = {
        track: function (ids, event, uid) {
            var category = 'Interactives';
            
            var uid = current_uuid();
            var action = event;
            
            var allIds = ids.split(',');
            
            for (var i = 0; i < allIds.length; i++) {
                var label = 'id:' + allIds[i] + '|uid:' + current_uuid();
                if (window._gaq) {
                    window._gaq.push(['_trackEvent', category, action, label]);
                } else if (window.ga) {
                    ga('send', {
                        hitType: 'event',
                        eventCategory: category,
                        eventAction: action,
                        eventLabel: label
                      });
                }
            }
        }
    };
    
    Trackers.Local = {
        track: function (ids, event, uid) {
            var uid = current_uuid();
            $.post(config.endpoint, {ids: ids, evt: event, sig: uid});
        }
    };


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