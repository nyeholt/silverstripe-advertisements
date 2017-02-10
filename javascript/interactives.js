
(function ($) {
    var config = {
        remember: false,         // remember the user through requests
        trackviews: false,
        trackclicks: true,      // should clicks be 
        trackforward: true,     // should target links be hooked and have a uid appended?
        items: [],
        tracker: 'Local',
        endpoint: '',
        cookieprefix: 'int_'
    };
    
    var uuid = null;
    
    var allowed_add_actions = ['prepend', 'append', 'before', 'after', 'html'];
    
    var current_id = get_url_param('int_id');
    
    var tracker;

    var Trackers = {};
    
    $().ready(function () {
        

        if (!window.SSInteractives) {
            return;
        }
        
        for (var property in window.SSInteractives.config) {
            config[property] = window.SSInteractives.config[property];
        }

        if (!config.endpoint) {
            var base = $('base').attr('href');
            config.endpoint = base + 'interactive-action/trk';
        }

        tracker = Trackers[config.tracker] ? Trackers[config.tracker] : Trackers.Local;
        
        if (!tracker) {
            return;
        }
        
        // bind globally available API endpoints now
        window.SSInteractives.addInteractiveItem = add_interactive_item;
        window.SSInteractives.track = tracker.track;

        var recorded = {};
        
        var uid = url_uuid();
        
        // record that a page was loaded because of an interaction with a previous interactive
        if (uid && config.trackforward) {
            tracker.track(current_id, 'int');
        }

        // see if we have any items to display
        if (config.campaigns.length) {
            for (var j = 0; j < config.campaigns.length; j++) {
                add_campaign(config.campaigns[j]);
            }
        }
        
        var recordClick = function (b) {
            var adId = $(this).attr('data-intid');
            if (b.which < 3) {
                tracker.track(adId, 'clk');
            }
            
            if ($(this).hasClass('hide-on-interact')) {
                var blocked = get_cookie('interacted');
                blocked += '|' + adId;
                set_cookie('interacted', blocked);
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
            }
            setTimeout(processViews, 3000);
        }
        
        processViews();
    });
    
    /**
     * Add a whole campaign to the page. 
     * @param object campaign
     * @returns 
     */
    function add_campaign(campaign) {
        if (campaign.interactives.length) {
            var cookie_name = 'cmp_' + campaign.id;
            // see what type; if it's all, or just a specific ID to show
            var showId = 0;
            var showIndex = 0;
            var item = null;
            
            if (campaign.display == 'stickyrandom') {
                // if we already have a specific ID in a cookie, we need to use that
                var savedId = get_cookie(cookie_name);
                if (savedId) {
                    showId = savedId;
                } else {
                    // okay, get a new random
                    
                }
            }
            
            // now check for random / stickyrandom if needbe
            if (!showId && campaign.display !== 'all') {
                showIndex = Math.floor(Math.random() * (campaign.interactives.length));
                
                item = campaign.interactives[showIndex];
                // if it's sticky, we need to save a cookie
                if (campaign.display == 'stickyrandom') {
                    set_cookie(cookie_name, item.ID);
                }

                return add_interactive_item(item);
            }
            
            
            for (var i = 0; i < campaign.interactives.length; i++) {
                item = campaign.interactives[i];
                // if we're looking for a particular ID
                if (showId) {
                    if (item.ID == showId) {
                        return add_interactive_item(item);
                    }
                } else {
                    add_interactive_item(item);
                }
            }
        }
    }
    
    /**
     * Adds a new interactive item into the page
     * 
     * Takes into account the location to be added, and any 
     * handlers that need binding on contained 'a' elements. 
     * 
     * @param {type} item
     * @returns {undefined}
     */
    function add_interactive_item(item) {
        var target;
        var addFunction = 'prepend';
        
        var effect = 'show';
        
        if (current_id && current_id == item.ID) {
            // bind a handler for the 'completion' element, but we don't display anything
            if (item.CompletionElement) {
                $(document).on('mouseup', item.CompletionElement, function () {
                    tracker.track(item.ID, 'cpl', current_uuid());
                });
            }
            return;
        }
        
        var hidden = get_cookie('interacted');
        if (hidden && hidden.length) {
            hidden = hidden.split('|');
            if (hidden.indexOf("" + item.ID) >= 0 && item.HideAfterInteraction) {
                return;
            }
        }
        
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
            var canUse = allowed_add_actions.indexOf(item.Location);
            addFunction = canUse >= 0 ? item.Location : 'prepend';
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

            // if there's a completion element identified, we pass on the information about
            // this item in the link
            if (item.CompletionElement) {
                var append = 'int_src=' + current_uuid() + '&int_id=' + item.ID;
                var newLink = $(this).attr('href');
                if (newLink.indexOf('?') >= 0) {
                    append = "&" + append;
                } else {
                    append = "?" + append;
                }
                $(this).attr('href', newLink + append);
            }
            
            if (item.HideAfterInteraction) {
                $(this).addClass('hide-on-interact');
            }
        });
        
        
        
        var timeout = item.Delay ? item.Delay : 0;
        
        setTimeout(function () {
            // Add the item using the appropriate location
            target[addFunction](holder);
            // and effect for showing
            holder[effect]();
        }, timeout);
    };

    function current_uuid() {
        // check the URL string for a continual UUID
        if (uuid) {
            return uuid;
        }
        
        var uid = null;
        
        if (config.remember) {
            // check in a cookie
            uid = get_cookie('uuid');
        }

        // check the URL string
        if (!uid) {
            uid = url_uuid();
        }

        if (!uid) {
            uid = UUID().generate();
            set_cookie('uuid', uid);
        }
        
        uuid = uid;
        return uid;
    }
    
    function url_uuid() {
        return get_url_param('int_src');
    }
    
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


    //<editor-fold defaultstate="collapsed" desc="Cookie management">
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
        name = config.cookieprefix + name;
        document.cookie = name + "=" + value + expires + "; path=/";
    }
    
    function get_cookie(name) {
        name = config.cookieprefix + name;
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
    //</editor-fold>

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