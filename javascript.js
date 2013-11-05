/**
 * joule Grader javascript
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */

M.local_joulegrader = M.local_joulegrader || {};

/**
 * Initializes grade pane resizing via YUI 3 drag and drop.
 *
 * @param Y
 */
M.local_joulegrader.init_resize = function(Y) {

    var gradepanegridpositions = ['yui3-u-1-2', 'yui3-u-11-24', 'yui3-u-5-12', 'yui3-u-3-8', 'yui3-u-1-3', 'yui3-u-1-4', 'yui3-u-1-5', 'yui3-u-1-6'];
    var viewpanegridpositions = ['yui3-u-1-2', 'yui3-u-13-24', 'yui3-u-7-12', 'yui3-u-5-8', 'yui3-u-2-3', 'yui3-u-3-4', 'yui3-u-4-5', 'yui3-u-5-6'];

    var gradepane = Y.one('#local-joulegrader-gradepane');
    var viewpane = Y.one('#local-joulegrader-viewpane');
    var draghandle = Y.one('#local-joulegrader-resize');
    var gradepanecontent = gradepane.one('.content');

    /**
     * Updates the position and height of the drag handle.
     */
    var updatehandlepos = function() {
        var handleheight = gradepanecontent.getComputedStyle('height');
        var handlex = gradepane.getX();
        draghandle.setX(handlex - 10);
        draghandle.setStyle('height', handleheight);
    };

    /**
     * Calculates the pixel positions for each of the grade pane grid classes based on dummy grid elements.
     * These positions are used to constrain the YUI 3 drag and drop.
     *
     * @returns {Array}
     */
    var calculatepixels = function() {
        var pixels = [];

        for (var i = 0; i < gradepanegridpositions.length; i++) {
            pixels.push(Y.one('.' + gradepanegridpositions[i] + '.local-joulegrader-dummy').getX() - 10);
        }
        return pixels;
    };

    var resize_tinymce = function() {
        if (!tinymce) {
            return;
        }

        var width = parseInt(gradepanecontent.getComputedStyle('width'));
        width = width - 50;

        var eds = tinymce.editors;
        if (eds) {
            for (var i in eds) {
                var con = Y.one(eds[i].getContentAreaContainer());
                if (con.ancestor('#local-joulegrader-gradepane-panel')) {
                    continue;
                }

                var tmceiframe = con.one('iframe');
                if (tmceiframe) {
                    tmceiframe.setStyle('width', width + 'px');
                }
            }
        }
    }

    // Initialize the handle position and the grid pixel positions.
    updatehandlepos();
    var pixels = calculatepixels();
    resize_tinymce();

    // Create the drag instance using the draghandle as the drag node.
    var drag = new Y.DD.Drag({
        node: draghandle
    });

    Y.DD.DDM.set('dragCursor', 'ew-resize');

    // Constrain the drag node to only drag along the X-Axis and to snap to the grid pixel positions.
    drag.plug(Y.Plugin.DDConstrained, {
        stickX: true,
        tickXArray: pixels,
        constrain2node: '#local-joulegrader-panes'
    });

    // Update the YUI 3 grid classes when the drag is aligned with a grid pixel position.
    drag.con.on('drag:tickAlignX', function(e) {
        var actx = drag.actXY[0];
        var lastx = drag.lastXY[0];

        if (parseInt(actx) == parseInt(lastx)) {
            return;
        }

        var pixidx = pixels.indexOf(actx);

        if (pixidx === -1 || !gradepanegridpositions[pixidx]) {
            return;
        }

        var newgpclass = gradepanegridpositions[pixidx];
        var newvpclass = viewpanegridpositions[pixidx];

        var currentgpclass = gradepane.getAttribute('class');
        var currentvpclass = viewpane.getAttribute('class');

        gradepane.removeClass(currentgpclass);
        viewpane.removeClass(currentvpclass);

        gradepane.addClass(newgpclass);
        viewpane.addClass(newvpclass);

        resize_tinymce();
    });

    // Recalculate the grid pixel positions, update the handle position, and reset constrained drag "snap" points.
    Y.on('windowresize', function(e) {
        pixels = calculatepixels();
        drag.con.set('tickXArray', pixels);
        updatehandlepos();
        resize_tinymce();
    });
};

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

    panel.plug(M.local_mr.accessiblepanel);

    // Restore the "normal" height on the joule grader div after hiding the panel
    panel.after('visibleChange', function(e) {
        if (!e.newVal && e.prevVal) {
            joulegrader.setStyle('height', null);
        }
    });

    // only allow dragging from the header and footer bars
    panel.dd.addHandle('.yui3-widget-hd');
    panel.dd.addHandle('.yui3-widget-ft');

    var adjustjoulegraderheight = function() {
        if (!panelnode || !joulegrader) {
            return;
        }

        //get the panel content's height
        var panelheight = panelnode.get('offsetHeight');

        //joule grader height
        var jgheight = joulegrader.get('offsetHeight');

        if (jgheight < panelheight) {
            joulegrader.setStyle('height', panelheight + 100 + 'px');
        }
    };

    //adjust the height of joule grader div if textareas cause resizing of of the modal (on mouseup)
    joulegrader.delegate('mouseup', adjustjoulegraderheight, '#local-joulegrader-gradepane-panel textarea');

    //wire up the button
    btn.on('click', function(e) {
        e.preventDefault();

        //adjust the height of the joulegrader div if necessary
        adjustjoulegraderheight();

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

M.local_joulegrader.generate_errorpanel = function (Y, options, errormsg) {
    var errorpanel = new Y.Panel({
        bodyContent: errormsg,
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

    errorpanel.plug(M.local_mr.accessiblepanel, {ariaRole: "dialog-alert"});

    return errorpanel;
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

M.local_joulegrader.init_guide = function(Y, options, panel) {
    var panelnode = panel.get('srcNode');

    //get the submit and submit next buttons if they exist
    var submitbuttons = Y.all('#' + options.id + ' input[type=submit]');

    if (submitbuttons && !submitbuttons.isEmpty()) {
        //render the panel first so the that the error panel renders correctly
        panel.render();

        //a little panel for display an error message
        var errorpanel = M.local_joulegrader.generate_errorpanel(Y, options, '');

        //attach the event handlers
        submitbuttons.on('click', function(e) {
            //flag for valid guide
            var valid = true;

            errorpanel.set('bodyContent', '');
            var errorpanelcontent = '<div class="gradingform_guide-error">' + M.str.local_joulegrader.guideerror + '</div>';

            //get all the criteria
            var criteriascore = Y.all('#' + options.id + ' .gradingform_guide .criterion .score input[type="text"]');
            criteriascore.each(function(score) {
                var maxscore = score.next('.criteriondescriptionscore');
                var maxscorevalue;
                if (maxscore) {
                    maxscorevalue = parseInt(maxscore.get('textContent'));
                }
                var scorevalue = score.get('value');
                if (scorevalue === '' || isNaN(scorevalue) || (maxscorevalue && (parseInt(scorevalue) > maxscorevalue || parseInt(scorevalue) < 0))) {
                    valid = false;
                    var criterionshortnameel = score.ancestor('.criterion').one('.criterionshortname');
                    var criterionshortname = criterionshortnameel ? criterionshortnameel.get('textContent') : '';

                    if (maxscorevalue && criterionshortname !== '') {
                        var a = {
                            criterianame: criterionshortname,
                            maxscore: maxscorevalue
                        };
                        var errstr = M.util.get_string('err_scoreinvalid', 'gradingform_guide', a);
                        errorpanelcontent += '<div class="gradingform_guide-error">' + errstr + '</div>';
                    }
                }
            });


            if (!valid) {
                e.preventDefault();

                errorpanel.set('width', 500);
                errorpanel.set('bodyContent', errorpanelcontent);

                // Show the panel.
                errorpanel.show();

                // Scroll it into view and center it.
                if (Y.UA.ie > 0 && window.scrollTo) {
                    var epy = errorpanel.get('y');
                    window.scrollTo(epy, 0);
                } else {
                    errorpanel.get('srcNode').scrollIntoView();
                }
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

        // render the panel
        panel.render();
    }

    // resize if necessary
    var panelwidth = panelnode.get('scrollWidth');
    var maxwidth = parseInt(Y.one('#local-joulegrader').get('offsetWidth'));
    if (panelwidth > maxwidth) {
        panelwidth = maxwidth;
    }
    panel.set('width', panelwidth);
}


M.local_joulegrader.init_rubric = function(Y, options, panel) {

    //get the submit and submit next buttons if they exist
    var submitbuttons = Y.all('#' + options.id + ' input[type=submit]');
    if (submitbuttons && !submitbuttons.isEmpty()) {
        //render the panel first so the that the error panel renders correctly
        panel.render();

        //a little panel for display an error message
        var errorpanel = M.local_joulegrader.generate_errorpanel(Y, options, M.str.local_joulegrader.rubricerror);

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

                // Show the panel.
                errorpanel.show();

                // Scroll it into view and center it.
                if (Y.UA.ie > 0 && window.scrollTo) {
                    var epy = errorpanel.get('y');
                    window.scrollTo(epy, 0);
                } else {
                    errorpanel.get('srcNode').scrollIntoView();
                }
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

    //IE is special; add a invisible div to the 1st comment remark td so that IE will not squish that column
    if (Y.UA.ie) {
        var commenttextarea = Y.one('#local-joulegrader-gradepane-panel .criterion .remark');
        if (commenttextarea) {
            commenttextarea.append('<div style="visibility: hidden; width: 100px;"></div>');
        }
    }

    // resize if necessary
    panel.set('width', 5000);
    var tablenode = panel.get('srcNode').one('.gradingform_rubric table');
    var panelwidth = parseInt(tablenode.get('offsetWidth')) + 30;
    var maxwidth = parseInt(Y.one('#local-joulegrader').get('offsetWidth'));
    if (panelwidth > maxwidth) {
        panelwidth = maxwidth;
    }
    panel.set('width', panelwidth);
    Y.one('#local-joulegrader-gradepane-panel .gradingform_rubric').setStyle('width', panel.get('width') - 30);
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

                        //if html is there replace comments
                        if (response.html) {
                            //insert the new comment after the old one
                            comments.insert(response.html, 'replace');

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
    commentloopcon.delegate('click', deleteaction, '.local_joulegrader_comment_delete a');

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
                            // replace the comments
                            comments.insert(response.html, 'replace');

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

M.local_joulegrader.init_viewinlinefile = function(Y, courseid) {
    var loadedfiles = {};
    var filenamesbyids = {};
    var fileids = [];
    var filelinksbyids = {};
    var currentfile, currentfilehash;

    var filetreecon = Y.one('#local-joulegrader-assign23-treecon');
    if (!filetreecon) {
        return;
    }
    var fileinline = Y.one('#local-joulegrader-assign23-files-inline');
    if (!fileinline) {
        return;
    }

    // Online submission content.
    var onlinesubmission = Y.one('#local-joulegrader-assign23-assign_submission_onlinetext');

    // View inline links
    var inlinefilelinks = Y.all('.local_joulegrader_assign23_inlinefile');
    if (!inlinefilelinks || inlinefilelinks.isEmpty()) {
        return;
    }

    var nextinlinefilelink = Y.one('#local-joulegrader-assign23-ctrl-next');
    var previnlinefilelink = Y.one('#local-joulegrader-assign23-ctrl-previous');

    var downloadlinkctrl = Y.one('#local-joulegrader-assign23-ctrl-download');
    var filenamectrl = Y.one('#local-joulegrader-assign23-ctrl-filename');

    // Close inline file button.
    var closeinline = fileinline.one('#local-joulegrader-assign23-ctrl-close');

    var handleresize = function() {
        if (currentfile !== undefined && Y.Lang.isFunction(currentfile.hasClass) && !currentfile.hasClass('local_joulegrader_hidden')) {
            // Element that contains the object or iframe
            var resourcecon = currentfile.one('.resourcecontent');
            if (!resourcecon) {
                return;
            }

            // First see if there is an object tag.
            var embedel = resourcecon.one('object');
            var isiframe = false;
            if (!embedel) {
                // If no object element check for an iframe.
                embedel = resourcecon.one('iframe');
                isiframe = true;
            }

            if (!embedel) {
                return;
            }

            // Width of the file inline container
            var fileinlinewidth = fileinline.getComputedStyle('width');

            embedel.set('width', fileinlinewidth);

            if (isiframe) {
                var joulegraderpanesheight = Y.one('#local-joulegrader-panes').getComputedStyle('height');
                embedel.setStyle('height', joulegraderpanesheight);
            }
        }
    }

    Y.on('windowresize', handleresize);

    var show_node = function(node) {
        if (node.hasClass('local_joulegrader_hidden')) {
            node.removeClass('local_joulegrader_hidden');
        }
    };

    var hide_node = function(node) {
        if (!node.hasClass('local_joulegrader_hidden')) {
            node.addClass('local_joulegrader_hidden');
        }
    };

    var show_inlinefile = function(filenode) {
        if (onlinesubmission) {
            hide_node(onlinesubmission);
        }
        hide_node(filetreecon);
        show_node(filenode);
        show_node(fileinline);

        // Scroll it into view and center it.
        if (Y.UA.ie > 0 && window.scrollTo) {
            var fileinliney = fileinline.get('y');
            window.scrollTo(fileinliney, 0);
        } else {
            fileinline.scrollIntoView();
        }

        currentfile = filenode;

        // Resize
        handleresize();
    }

    var hide_inlinefile = function() {
        if (currentfile) {
            hide_node(currentfile);
        }
        hide_node(fileinline);
        if (onlinesubmission) {
            show_node(onlinesubmission);
        }
        show_node(filetreecon);
    }

    // Number of inline file links.
    var inlinefilecount = inlinefilelinks.size();

    var inlinefileselect = Y.one('#local-joulegrader-assign23-ctrl-select select');

    // Add view inline file links to the select menu.
    inlinefilelinks.each(function(filelink) {
        var selectkey = filelink.get('id');
        fileids.push(selectkey);
        var filename = filelink.previous('img').get('alt');

        // Add the option.
        inlinefileselect.append('<option value="' + selectkey + '">' + filename + '</option>')

        // Save the id and name for later.
        filenamesbyids[selectkey] = filename;

        var href = filelink.get('href');

        filelinksbyids[selectkey] = href;
    });

    var iocfg = {
        method: 'GET',
        timeout: 4000,
        on: {
            success: function(id, o, args) {
                try {
                    var response = Y.JSON.parse(o.responseText);

                    if (response.html) {
                        var inlineid = 'local-joulegrader-inlinefile-' + args.hashid;

                        // Append the html to fileinline div
                        fileinline.append('<div id="' + inlineid + '">' + response.html + '</div>');

                        // Store the node for later
                        loadedfiles[args.hashid] = fileinline.one('#' + inlineid);

                        // Show the inline file
                        show_inlinefile(loadedfiles[args.hashid]);

                    } else if (response.error) {
                        alert(response.error);
                    }

                } catch (excp) {
                    alert(excp);
                }
            },
            failure: function(id, o, args) {

            }
        }
    }

    closeinline.on('click', function(e) {
        e.preventDefault();
        hide_inlinefile();
    });

    var loadorshowfile = function(filehash) {
        // Check to see if it has already been loaded
        if (!loadedfiles.hasOwnProperty(filehash)) {
            // Fire the request.
            iocfg.data = 'action=inlinefile&f=' + filehash + '&courseid=' + courseid;
            iocfg.arguments = {hashid: filehash};
            Y.io(M.cfg.wwwroot + '/local/joulegrader/view.php', iocfg);
        } else {
            show_inlinefile(loadedfiles[filehash]);
        }

        filenamectrl.setContent(filenamesbyids[filehash]);
        downloadlinkctrl.setContent('(<a href="' + filelinksbyids[filehash] + '">' + M.str.local_joulegrader.download + '</a>)');
        inlinefileselect.set('value', filehash);
    }

    if (inlinefilecount < 2) {
        nextinlinefilelink.remove(true);
        previnlinefilelink.remove(true);
    } else {
        nextinlinefilelink.on('click', function(e) {
            e.preventDefault();

            if (currentfilehash) {
                var currentfilepos = fileids.indexOf(currentfilehash);
                if (currentfilepos !== -1) {
                    var nextpos;
                    if (currentfilepos === (fileids.length - 1)) {
                        // Current file is last in the list, the next is the first.
                        nextpos = 0;
                    } else {
                        nextpos = currentfilepos + 1;
                    }
                    var nextfilehash = fileids[nextpos];

                    hide_node(currentfile);
                    loadorshowfile(nextfilehash);
                    currentfilehash = nextfilehash;
                }
            }
        });

        previnlinefilelink.on('click', function(e) {
            e.preventDefault();

            if (currentfilehash) {
                var currentfilepos = fileids.indexOf(currentfilehash);
                if (currentfilepos !== -1) {
                    var prevpos;
                    if (currentfilepos === 0) {
                        // Current file is first in the list, the previous is the last.
                        prevpos = fileids.length - 1;
                    } else {
                        prevpos = currentfilepos - 1;
                    }
                    var nextfilehash = fileids[prevpos];

                    hide_node(currentfile);
                    loadorshowfile(nextfilehash);
                    currentfilehash = nextfilehash;
                }
            }
        });
    }

    inlinefileselect.on('change', function(e) {
        var selectedvalue = this.get('options').item(this.get('selectedIndex')).get('value');
        if (selectedvalue == 0) {
            hide_inlinefile();
        } else {
            hide_node(currentfile);
            loadorshowfile(selectedvalue);
            currentfilehash = selectedvalue;
        }
    });

    // Delegate click on all '.local_joulegrader_assign23_inlinefile' links under the filetree container
    filetreecon.delegate('click', function(e) {
        // Prevent the default action
        e.preventDefault();

        var link = e.currentTarget;
        var filehash = link.get('id');

        loadorshowfile(filehash);
        currentfilehash = filehash;

    }, '.local_joulegrader_assign23_inlinefile')
};
