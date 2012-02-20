/**
 * joule Grader javascript
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */

M.local_joulegrader = M.local_joulegrader || {};

/** Useful for full embedding of various stuff */
/** Copied and modified from Moodle core utility js */
M.local_joulegrader.init_maximised_embed = function(Y, id) {
    var obj = Y.one('#'+id);
    if (!obj) {
        return;
    }

    var get_htmlelement_size = function(el, prop) {
        if (Y.Lang.isString(el)) {
            el = Y.one('#' + el);
        }
        var val = el.getStyle(prop);
        if (val == 'auto') {
            val = el.getComputedStyle(prop);
        }
        return parseInt(val);
    };

    var resize_object = function() {
        obj.setStyle('width', '0px');
        obj.setStyle('height', '0px');
        var newwidth = get_htmlelement_size('local-joulegrader-viewpane', 'width') - 35;

        if (newwidth > 500) {
            obj.setStyle('width', newwidth  + 'px');
        } else {
            obj.setStyle('width', '500px');
        }

        var headerheight = get_htmlelement_size('page-header', 'height');

        var headermain = Y.one('#header-main');
        var headermainheight = 0;
        if (headermain) {
            headermainheight = get_htmlelement_size(headermain, 'height');
        }

        var custommenuwrap = Y.one('#custommenu-wrap');
        var custommenuwrapheight = 0;
        if (custommenuwrap) {
            custommenuwrapheight = get_htmlelement_size(custommenuwrap, 'height');
        }

        var navbar = Y.one('.navbar');
        var navbarheight = 0;
        if (navbar) {
            navbarheight = get_htmlelement_size(navbar, 'height');
        }

        var viewportheight = Y.one('body').get('winHeight');
        var newheight = parseInt(viewportheight) - headerheight - headermainheight - custommenuwrapheight - navbarheight;
        if (newheight < 400) {
            newheight = 400;
        }
        obj.setStyle('height', newheight+'px');
    };

    resize_object();
    // fix layout if window resized too
    window.onresize = function() {
        resize_object();
    };
};
