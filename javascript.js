/**
 * joule Grader javascript
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */

M.local_joulegrader = M.local_joulegrader || {};

M.local_joulegrader.init_gradepane_panel = function(Y, id) {
    var panelnode = Y.one('#' + id);
    if (!panelnode) {
        return;
    }

    //create the panel
    var panel = new Y.Panel({
        srcNode: '#' + id,
        headerContent: M.str.local_joulegrader.rubric,
        centered: true,
//        width: 500,
        zIndex: 10,
//        modal: true,
        visible: false,
        render: true,
        plugins: [Y.Plugin.Drag]
    });

    //wire up the button
    var btn = Y.one('#local-joulegrader-viewrubric-button');
    btn.on('click', function(e) {
        e.preventDefault();

        //remove the hidden class from the rubric
        panelnode.removeClass('dontshow');

        //open the panel
        panel.show();
    });

    //get the submit and submit next buttons if they exist
    var submitbuttons = Y.all('#' + id + ' input[type=submit]');
    if (submitbuttons) {
        //a little panel for display an error message
        errorpanel = new Y.Panel({
            srcNode: '#local-joulegrader-gradepane-rubricerror',
            centered: true,
            zindex: 200,
            width: 200,
            visible: false,
            render: '#' + id
        });

        errorpanel.render();

        //attach the event handlers
        submitbuttons.on('click', function(e) {
            //flag for valid rubric
            var valid = true;

            //get all the criteria
            var criteria = Y.all('#' + id + ' .gradingform_rubric .criterion');

            //make sure we have some criteria
            if (criteria) {
                //iterate over each criterion
                criteria.each(function(criterion) {
                    //get the levels (radio buttons) for this criterion
                    var radiobuttons = criterion.all('input[type=radio]');
                    if (radiobuttons) {
                        var validcriterion = false;
                        //iterate over each level (radio button)
                        radiobuttons.each(function(radio) {
                            if (radio.get('checked')) {
                                //if the criterion is not valid already and
                                validcriterion = true;
                            }
                        });

                        //combine overall validity with this criterion's validity
                        valid = valid && validcriterion;
                    }
                });
            }

            if (!valid) {
                e.preventDefault();
                Y.one('#local-joulegrader-gradepane-rubricerror').removeClass('dontshow');

                errorpanel.show();
                Y.later(2000, errorpanel, errorpanel.hide);
            }

        });
    }

}

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
