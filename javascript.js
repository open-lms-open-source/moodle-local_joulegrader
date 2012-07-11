/**
 * joule Grader javascript
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */

M.local_joulegrader = M.local_joulegrader || {};

M.local_joulegrader.init_gradepane_panel = function(Y, options) {
    var panelnode = Y.one('#' + options.id);
    var btn = Y.one('#local-joulegrader-preview-button');
    if (!panelnode || !btn) {
        return;
    }


    //joule grader div
    var joulegrader = Y.one('#local-joulegrader');

    //create the panel
    var panel = new Y.Panel({
        srcNode: '#' + options.id,
        headerContent: M.str.local_joulegrader[options.grademethod],
        footerContent: '',
        centered: joulegrader,
//        width: 500,
        zIndex: 10,
//        modal: true,
        visible: false,
//        render: true,
        plugins: [Y.Plugin.Drag]
    });

    // only allow dragging from the header and footer bars
    panel.dd.addHandle('.yui3-widget-hd');
    panel.dd.addHandle('.yui3-widget-ft');

    //wire up the button
    btn.on('click', function(e) {
        e.preventDefault();

        //get the panel content's height
        var panelheight = panelnode.get('offsetHeight');

        //joule grader height
        var jgheight = joulegrader.get('offsetHeight');

        if (jgheight < panelheight) {
            joulegrader.setStyle('height', panelheight + 100 + 'px');
        }

        //re-align on the local-joulegrader div (top-center of panel with top-center of local-joulegrader div
        panel.align(joulegrader, [Y.WidgetPositionAlign.TC, Y.WidgetPositionAlign.TC]);

        //remove the hidden class from the rubric
        panelnode.removeClass('dontshow');

        //open the panel
        panel.show();
    });

    if (M.local_joulegrader.hasOwnProperty('init_' + options.grademethod)) {
        M.local_joulegrader['init_' + options.grademethod](Y, options, panel);
    }
}

M.local_joulegrader.init_checklist = function(Y, options, panel) {
    var panelnode = panel.get('srcNode');

    //get the submit and submit next buttons if they exist
    var submitbuttons = Y.all('#' + options.id + ' input[type=submit]');
    if (submitbuttons.isEmpty()) {
        //this is for the student
        //add a close button
        var closebutton = {
            value: M.str.local_joulegrader.close,
            action: function(e) {
                panel.hide();
            },
            section: 'footer'
        };

        panel.addButton(closebutton);
    }

    // render the panel
    panel.render();

    // resize if necessary
    panel.set('width', 5000);
    var widthnode = panel.get('srcNode').one('.gradingform_checklist .groups');
    var panelwidth = widthnode.get('scrollWidth');
    panel.set('width', panelwidth);

}

M.local_joulegrader.init_rubric = function(Y, options, panel) {

    //get the submit and submit next buttons if they exist
    var submitbuttons = Y.all('#' + options.id + ' input[type=submit]');
    if (submitbuttons && !submitbuttons.isEmpty()) {
        //render the panel first so the that the error panel renders correctly
        panel.render();

        //a little panel for display an error message
        var errorpanel = new Y.Panel({
            srcNode: '#local-joulegrader-gradepane-rubricerror',
            centered: '#' + options.id,
            zindex: 200,
            width: 200,
            visible: false,
            render: '#' + options.id,
            buttons: [
                {
                    value: M.str.local_joulegrader.close,
                    action: function(e) {
                        errorpanel.hide();
                    },
                    section: 'footer'
                }
            ]
        });

        //attach the event handlers
        submitbuttons.on('click', function(e) {
            //flag for valid rubric
            var valid = true;

            //get all the criteria
            var criteria = Y.all('#' + options.id + ' .gradingform_rubric .criterion');

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
                errorpanel.get('srcNode').scrollIntoView();
                errorpanel.centered();
            }

        });
    } else {
        //this is for the student
        //add a close button
        var closebutton = {
            value: M.str.local_joulegrader.close,
            action: function(e) {
                panel.hide();
            },
            section: 'footer'
        };

        panel.addButton(closebutton);

        //now we can render the panel
        panel.render();
    }

    // resize if necessary
    panel.set('width', 5000);
    var tablenode = panel.get('srcNode').one('.gradingform_rubric table');
    var panelwidth = tablenode.get('offsetWidth');
    panel.set('width', panelwidth + 30);

}

/**
 *
 * @param Y
 * @param id - id of the comment loop container
 */
M.local_joulegrader.init_commentloop = function(Y, id) {
    //get the comment loop container
    var commentloopcon = Y.one('#' + id);
    if (!commentloopcon) {
        return;
    }

    //get the
    var comments = commentloopcon.one('.local_joulegrader_commentloop_comments');
    if (!comments) {
        return;
    }

    //scroll the comments to the most recent
    comments.set('scrollTop', comments.get('scrollHeight'));

    //get the comment form element
    var commentform = commentloopcon.one('form');
    if (!commentform) {
        return;
    }

    //event handler for deleting comments
    var deleteaction = function(e) {
        e.preventDefault();

        var lnkhref = e.currentTarget.get('href');
        //get the the params
        var params = lnkhref.split('?')[1];
        if (!params) {
            return;
        }

        //get the comment div
        var comment = e.currentTarget.ancestor('.local_joulegrader_comment');
        if (!comment) {
            return;
        }

        //Y.io cfg
        var cfg = {
            method: 'POST',
            data: params + '&ajax=1',
            on: {
                success: function(id, o, args) {
                    try {
                        //get the response
                        var response = Y.JSON.parse(o.responseText);

                        //if html is there replace the old one
                        if (response.html) {
                            var newcomment = Y.Node.create(response.html);

                            //insert the new comment after the old one
                            comment.insert(newcomment, 'after');

                            //make sure the new comment has the deleted class
                            if (!newcomment.hasClass('deleted')) {
                                newcomment.addClass('deleted');
                            }

                            //delete the old comment
                            comment.remove(true);
                        } else if (response.error) {
                            alert(response.error);
                        }
                    } catch (err) {
                        alert(err);
                    }
                }
            }
        };

        //send the ajax request
        Y.io(M.cfg.wwwroot + '/local/joulegrader/view.php', cfg);
    }

    //attach onclick event listener for delete comment
    var commentdeletelinks = commentloopcon.all('.local_joulegrader_comment_delete a');
    if (commentdeletelinks) {
        commentdeletelinks.on('click', deleteaction);
    }

    //attach onsubmit event listener for adding new comments
    commentform.on('submit', function(e) {
        //try to get the comment textarea element
        var textarea = commentform.one('textarea');
        if (!textarea) {
            return;
        }

        //try to get the iframe for the tinymce editor
        var editor = tinyMCE.getInstanceById(textarea.get('id'));

        //try to get the comment text
        var comment = textarea.get('value');
        if (comment == '') {
            //if there is no comment then just return and let the form client-side validation handle it
            return;
        }

        e.preventDefault();
        //looks like this is a good comment, let's submit it all ajax-like
        var cfg = {
            method: 'POST',
            form: {
                id: commentform
            },
            data: 'ajax=1',
            on: {
                success: function(id, o, args) {
                    try {
                        var response = Y.JSON.parse(o.responseText);

                        if (response.html) {
                            //append the comment
                            var newcomment = Y.Node.create(response.html);
                            comments.append(newcomment);
                            
                            //attach the delete event listener
                            var deletelnk = newcomment.one('.local_joulegrader_comment_delete a');
                            if (deletelnk) {
                                deletelnk.on('click', deleteaction);
                            }

                            //delete the textarea
                            textarea.set('value', '');

                            //set the tinyMCE content to an empty string also
                            if (editor) {
                                editor.setContent('');
                            }

                            //scroll down
                            comments.set('scrollTop', comments.get('scrollHeight'));

                        } else if (response.error) {
                            alert(response.error);
                        }
                    } catch (excp) {
                        alert(excp);
                    }
                }
            }
        };

        //fire the ajax request
        Y.io(M.cfg.wwwroot + '/local/joulegrader/view.php', cfg);

    });
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
