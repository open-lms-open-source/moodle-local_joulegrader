/**
 * joule Grader javascript
 *
 * @author Sam Chaffee
 * @package local/joulegrader
 */

window.M = window.M || {};
M.local_joulegrader = M.local_joulegrader || {};

/**
 * Lightweight mock class to replace Y.Panel functionality
 * strictly tailored to the needs of joulegrader.
 */
class JoulePanel {
    constructor(config) {
        this.config = config;
        this.srcNode = config.srcNode ? document.querySelector(config.srcNode) : null;
        this.width = config.width || null;

        // If no srcNode, dynamically generate it (used for generate_errorpanel).
        if (!this.srcNode && config.bodyContent !== undefined) {
            this.srcNode = document.createElement('div');
            this.srcNode.className = 'local-joulegrader-panel yui3-panel';
            this.srcNode.style.position = 'absolute';
            this.srcNode.style.zIndex = config.zIndex || 200;
            this.srcNode.style.display = 'none';
            this.srcNode.style.backgroundColor = '#fff';
            this.srcNode.style.border = '1px solid #ccc';
            this.srcNode.style.boxShadow = '0 2px 5px rgba(0,0,0,0.3)';
            this.srcNode.style.padding = '10px';
            this.srcNode.innerHTML = `
                <div class="yui3-widget-hd" style="cursor: move; padding-bottom: 5px; border-bottom: 1px solid #ddd;">${config.headerContent || ''}</div>
                <div class="yui3-widget-bd" style="padding: 10px 0;">${config.bodyContent || ''}</div>
                <div class="yui3-widget-ft" style="text-align: right;"></div>
            `;
            let renderTarget = config.render ? document.querySelector(config.render) : document.body;
            if (renderTarget) {
                renderTarget.appendChild(this.srcNode);
            }
        }

        if (this.srcNode) {
            if (this.width) this.srcNode.style.width = this.width + 'px';
            if (config.visible === false) this.srcNode.style.display = 'none';
        }

        this.onVisibleChange = null;

        if (config.buttons) {
            config.buttons.forEach(btn => this.addButton(btn));
        }

        this._initDrag();
    }

    _initDrag() {
        if (!this.srcNode) return;
        let hd = this.srcNode.querySelector('.yui3-widget-hd');
        if (!hd) return;
        hd.style.cursor = 'move';

        let isDragging = false;
        let startX, startY, initialLeft, initialTop;
        let rafId = null;

        hd.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            let rect = this.srcNode.getBoundingClientRect();
            initialLeft = rect.left;
            initialTop = rect.top;

            // Prevent text selection while dragging the panel.
            document.body.style.userSelect = 'none';
            e.preventDefault();
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            if (rafId) cancelAnimationFrame(rafId);

            rafId = requestAnimationFrame(() => {
                let dx = e.clientX - startX;
                let dy = e.clientY - startY;
                this.srcNode.style.left = (initialLeft + dx) + 'px';
                this.srcNode.style.top = (initialTop + dy) + 'px';
                this.srcNode.style.transform = 'none'; // Clear any centering transforms.
            });
        });

        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                document.body.style.userSelect = '';
                if (rafId) cancelAnimationFrame(rafId);
            }
        });
    }

    get(prop) {
        if (prop === 'srcNode') {
            return {
                one: (sel) => this.srcNode.querySelector(sel),
                all: (sel) => this.srcNode.querySelectorAll(sel),
                get: (p) => {
                    if (p === 'scrollWidth') return this.srcNode.scrollWidth;
                    if (p === 'offsetHeight') return this.srcNode.offsetHeight;
                    return this.srcNode[p];
                },
                scrollIntoView: () => this.srcNode.scrollIntoView()
            };
        }
        if (prop === 'y') return this.srcNode.getBoundingClientRect().top + window.scrollY;
        if (prop === 'width') return parseFloat(this.srcNode.style.width) || this.srcNode.offsetWidth;
        return this.config[prop];
    }

    set(prop, val) {
        if (prop === 'width') {
            this.width = val;
            this.srcNode.style.width = val + 'px';
        }
        if (prop === 'bodyContent') {
            let bd = this.srcNode.querySelector('.yui3-widget-bd') || this.srcNode;
            bd.innerHTML = val;
        }
    }

    show() {
        if (this.srcNode.style.display === 'none' && this.onVisibleChange) {
            this.onVisibleChange({newVal: true, prevVal: false});
        }
        this.srcNode.style.display = 'block';
    }

    hide() {
        if (this.srcNode.style.display !== 'none' && this.onVisibleChange) {
            this.onVisibleChange({newVal: false, prevVal: true});
        }
        this.srcNode.style.display = 'none';
    }

    align(targetStr) {
        let target = typeof targetStr === 'string' ? document.querySelector(targetStr) : targetStr;
        if (!target) return;
        let tRect = target.getBoundingClientRect();
        this.srcNode.style.position = 'absolute';
        this.srcNode.style.top = (tRect.top + window.scrollY) + 'px';
        this.srcNode.style.left = (tRect.left + (tRect.width / 2) - (this.srcNode.offsetWidth / 2)) + 'px';
    }

    centered() {
        this.srcNode.style.position = 'fixed';
        this.srcNode.style.top = '50%';
        this.srcNode.style.left = '50%';
        this.srcNode.style.transform = 'translate(-50%, -50%)';
    }

    addButton(btn) {
        let ft = this.srcNode.querySelector('.yui3-widget-ft');
        if (!ft) {
            ft = document.createElement('div');
            ft.className = 'yui3-widget-ft';
            this.srcNode.appendChild(ft);
        }
        let b = document.createElement('button');
        b.textContent = btn.value;
        b.addEventListener('click', (e) => btn.action(e));
        ft.appendChild(b);
    }

    render() {} // No-op for mock.
    plug() {} // No-op for mock.

    after(event, callback) {
        if (event === 'visibleChange') {
            this.onVisibleChange = callback;
        }
    }
}

/**
 * Initializes grade pane resizing via vanilla JS drag and drop.
 */
M.local_joulegrader.init_resize = function() {
    var gradepanegridpositions = ['yui3-u-1-2', 'yui3-u-11-24', 'yui3-u-5-12', 'yui3-u-3-8', 'yui3-u-1-3', 'yui3-u-1-4'];
    var viewpanegridpositions = ['yui3-u-1-2', 'yui3-u-13-24', 'yui3-u-7-12', 'yui3-u-5-8', 'yui3-u-2-3', 'yui3-u-3-4', 'yui3-u-4-5', 'yui3-u-5-6'];

    var gradepane = document.querySelector('#local-joulegrader-gradepane');
    var viewpane = document.querySelector('#local-joulegrader-viewpane');
    var draghandle = document.querySelector('#local-joulegrader-resize');
    var gradepanecontent = gradepane ? gradepane.querySelector('.content') : null;

    if (!gradepane || !viewpane || !draghandle || !gradepanecontent) return;

    document.querySelector('#local-joulegrader-resize').classList.remove('d-none');

    var updatehandlepos = function() {
        var handleheight = window.getComputedStyle(gradepanecontent).height;
        var handlex = gradepane.getBoundingClientRect().left + window.scrollX;
        draghandle.style.left = (handlex - 15) + 'px';
        draghandle.style.height = handleheight;
    };

    var calculatepixels = function() {
        var pixels = [];
        for (var i = 0; i < gradepanegridpositions.length; i++) {
            let dummy = document.querySelector('.' + gradepanegridpositions[i] + '.local-joulegrader-dummy');
            if (dummy) {
                pixels.push(dummy.getBoundingClientRect().left + window.scrollX - 10);
            }
        }
        return pixels;
    };

    updatehandlepos();
    var pixels = calculatepixels();

    // Vanilla Drag implementation with Smooth Animations and Bound Limits.
    let isDragging = false;
    let rafId = null;

    draghandle.addEventListener('mousedown', function(e) {
        isDragging = true;
        document.body.style.cursor = 'ew-resize';
        // Prevent accidental text selection while dragging (prevents major flickering).
        document.body.style.userSelect = 'none';
        e.preventDefault();

        // Recalculate snap points just in case the window shifted.
        pixels = calculatepixels();
    });

    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;

        // Use requestAnimationFrame to sync visual updates with the browser's paint cycle.
        if (rafId) cancelAnimationFrame(rafId);

        rafId = requestAnimationFrame(() => {
            let actx = e.clientX + window.scrollX;

            // --- CONSTRAIN DRAG AREA ---
            // Calculate a boundary so the user cannot drag too far.
            let panesContainer = document.querySelector('#local-joulegrader-panes');
            let containerLeft = panesContainer ? panesContainer.getBoundingClientRect().left + window.scrollX : 0;
            let containerWidth = panesContainer ? panesContainer.offsetWidth : window.innerWidth;

            // Define max/min dragging percentages (e.g. between 0% and 100% of container width).
            // Adjust these values if you want to allow more or less dragging space.
            let absoluteMinX = containerLeft + (containerWidth * 0);
            let absoluteMaxX = containerLeft + (containerWidth * 1);

            // Combine the absolute boundary with the available pixel grid array.
            let minX = Math.max(Math.min(...pixels), absoluteMinX);
            let maxX = Math.min(Math.max(...pixels), absoluteMaxX);

            // Confine the handle exactly within the limits.
            let boundedX = Math.max(minX, Math.min(actx, maxX));
            // ---------------------------

            // Smoothly move the handle exactly with the bounded constraints.
            draghandle.style.left = boundedX + 'px';

            // Find closest snap point to calculate the correct grid layout class.
            let closestPix = pixels.reduce((prev, curr) => Math.abs(curr - boundedX) < Math.abs(prev - boundedX) ? curr : prev);
            let pixidx = pixels.indexOf(closestPix);

            if (pixidx === -1 || !gradepanegridpositions[pixidx]) return;

            var newgpclass = gradepanegridpositions[pixidx];
            var newvpclass = viewpanegridpositions[pixidx];

            // Only touch the DOM classes if the threshold is crossed.
            if (!gradepane.classList.contains(newgpclass)) {
                gradepanegridpositions.forEach(cls => gradepane.classList.remove(cls));
                viewpanegridpositions.forEach(cls => viewpane.classList.remove(cls));

                gradepane.classList.add(newgpclass);
                viewpane.classList.add(newvpclass);
            }
        });
    });

    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            document.body.style.cursor = '';
            document.body.style.userSelect = ''; // Restore text selection.

            if (rafId) cancelAnimationFrame(rafId);

            // Snap the handle to the perfect grid alignment ONLY when the user lets go of the mouse.
            updatehandlepos();
        }
    });

    window.addEventListener('resize', function() {
        pixels = calculatepixels();
        updatehandlepos();
    });

    const joulegraderpane = document.querySelector('#local-joulegrader-panes');
    if (joulegraderpane && window.ResizeObserver) {
        const joulegraderobserver = new ResizeObserver(() => {
            pixels = calculatepixels();
            updatehandlepos();
        });
        joulegraderobserver.observe(joulegraderpane);
    }
};

M.local_joulegrader.init_gradepane_panel = function(Y, options) {
    var panelnode = Y.one('#' + options.id);
    var btn = Y.one('#local-joulegrader-preview-button');
    if (!panelnode || !btn) {
        return;
    }

    // Joule grader div.
    var joulegrader = Y.one('#local-joulegrader');

    // Create the panel.
    var panel = new Y.Panel({
        srcNode: '#' + options.id,
        headerContent: M.str.local_joulegrader[options.grademethod],
        footerContent: '',
        centered: joulegrader,
        zIndex: 2,
        visible: false,
        plugins: [Y.Plugin.Drag]
    });

    panel.plug(M.local_mr.accessiblepanel);

    // Restore the "normal" height on the joule grader div after hiding the panel.
    panel.after('visibleChange', function(e) {
        if (!e.newVal && e.prevVal) {
            joulegrader.setStyle('height', null);
        }
    });

    // Only allow dragging from the header and footer bars.
    panel.dd.addHandle('.yui3-widget-hd');
    panel.dd.addHandle('.yui3-widget-ft');

    var adjustjoulegraderheight = function() {
        if (!panelnode || !joulegrader) {
            return;
        }

        // Get the panel content's height.
        var panelheight = panelnode.get('offsetHeight');

        // Joule grader height.
        var jgheight = joulegrader.get('offsetHeight');

        if (jgheight < panelheight) {
            joulegrader.setStyle('height', panelheight + 100 + 'px');
        }
    };

    // Adjust the height of joule grader div if textareas cause resizing of of the modal (on mouseup).
    joulegrader.delegate('mouseup', adjustjoulegraderheight, '#local-joulegrader-gradepane-panel textarea');

    // Wire up the button.
    btn.on('click', function(e) {
        e.preventDefault();

        // Adjust the height of the joulegrader div if necessary.
        adjustjoulegraderheight();

        // Re-align on the local-joulegrader div (top-center of panel with top-center of local-joulegrader div.
        panel.align(joulegrader, [Y.WidgetPositionAlign.TC, Y.WidgetPositionAlign.TC]);

        // Remove the hidden class from the rubric.
        panelnode.removeClass('dontshow');

        // Open the panel.
        panel.show();
    });

    // --- Bridge to Vanilla JS ---
    // Create a proxy that perfectly mimics the expected JoulePanel interface for the vanilla sub-methods,
    // while routing actions to the YUI panel. Crucially, it forces the panel to render in-place
    // so it doesn't break out of the Moodle <form> element (which causes empty grade submissions).
    var vanillaPanelProxy = {
        srcNode: panel.get('srcNode').getDOMNode(),
        render: function() {
            var parent = panelnode.get('parentNode');
            if (parent) {
                // Renders strictly inside the <form> structure so inputs submit successfully.
                panel.render(parent);
            } else {
                panel.render();
            }
        },
        hide: function() { panel.hide(); },
        show: function() { panel.show(); },
        set: function(prop, val) { panel.set(prop, val); },
        get: function(prop) {
            if (prop === 'width') {
                // YUI returns strings (e.g., '500px'). Vanilla expects raw numbers to avoid NaN errors.
                return parseFloat(panel.get('width')) || 0;
            }
            return panel.get(prop);
        },
        addButton: function(btn) { panel.addButton(btn); }
    };

    if (M.local_joulegrader.hasOwnProperty('init_' + options.grademethod)) {
        M.local_joulegrader['init_' + options.grademethod](options, vanillaPanelProxy);
    }
}

M.local_joulegrader.generate_errorpanel = function(options, errormsg) {
    var errorpanel = new JoulePanel({
        bodyContent: errormsg,
        render: '#' + options.id,
        zIndex: 200,
        width: 200,
        visible: false,
        buttons: [
            {
                value: M.str.local_joulegrader.close,
                action: function(e) {
                    errorpanel.hide();
                }
            }
        ]
    });

    errorpanel.plug(M.local_mr && M.local_mr.accessiblepanel ? M.local_mr.accessiblepanel : null);
    return errorpanel;
};

M.local_joulegrader.init_checklist = function(options, panel) {
    var submitbuttons = document.querySelectorAll('#' + options.id + ' input[type=submit]');
    if (submitbuttons.length === 0) {
        var closebutton = {
            value: M.str.local_joulegrader.close,
            action: function(e) { panel.hide(); }
        };
        panel.addButton(closebutton);
    }

    panel.render();
    panel.set('width', 5000);
    var widthnode = panel.srcNode.querySelector('.gradingform_checklist .groups');
    if (widthnode) {
        var panelwidth = widthnode.scrollWidth;
        panel.set('width', panelwidth);
    }
};

M.local_joulegrader.init_guide = function(options, panel) {
    var submitbuttons = document.querySelectorAll('#' + options.id + ' input[type=submit]');

    if (submitbuttons.length > 0) {
        panel.render();
        var errorpanel = M.local_joulegrader.generate_errorpanel(options, '');

        submitbuttons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                var valid = true;
                errorpanel.set('bodyContent', '');
                var errorpanelcontent = '<div class="gradingform_guide-error">' + M.str.local_joulegrader.guideerror + '</div>';

                var criteriascore = document.querySelectorAll('#' + options.id + ' .gradingform_guide .criterion .score input[type="text"]');
                criteriascore.forEach(function(score) {
                    var maxscore = score.nextElementSibling;
                    while (maxscore && !maxscore.classList.contains('criteriondescriptionscore')) {
                        maxscore = maxscore.nextElementSibling;
                    }
                    var maxscorevalue = maxscore ? parseInt(maxscore.textContent, 10) : null;
                    var scorevalue = score.value;

                    if (scorevalue === '' || isNaN(scorevalue) || (maxscorevalue && (parseInt(scorevalue, 10) > maxscorevalue || parseInt(scorevalue, 10) < 0))) {
                        valid = false;
                        var criterionRow = score.closest('.criterion');
                        var criterionshortnameel = criterionRow ? criterionRow.querySelector('.criterionshortname') : null;
                        var criterionshortname = criterionshortnameel ? criterionshortnameel.textContent : '';

                        if (maxscorevalue && criterionshortname !== '') {
                            var errstr = M.util.get_string('err_scoreinvalid', 'gradingform_guide', {criterianame: criterionshortname, maxscore: maxscorevalue});
                            errorpanelcontent += '<div class="gradingform_guide-error">' + errstr + '</div>';
                        }
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    errorpanel.set('width', 500);
                    errorpanel.set('bodyContent', errorpanelcontent);
                    errorpanel.show();

                    var errorEl = errorpanel.srcNode;
                    if (errorEl && errorEl.scrollIntoView) {
                        errorEl.scrollIntoView();
                    }
                    errorpanel.centered();
                }
            });
        });
    } else {
        var closebutton = {
            value: M.str.local_joulegrader.close,
            action: function(e) { panel.hide(); }
        };
        panel.addButton(closebutton);
        panel.render();
    }

    var panelwidth = panel.srcNode.scrollWidth;
    var maxwidth = parseInt(document.querySelector('#local-joulegrader').offsetWidth, 10);
    if (panelwidth > maxwidth) {
        panelwidth = maxwidth;
    }
    panel.set('width', panelwidth);
};

M.local_joulegrader.init_rubric = function(options, panel) {
    var submitbuttons = document.querySelectorAll('#' + options.id + ' input[type=submit]');
    if (submitbuttons.length > 0) {
        panel.render();
        var errorpanel = M.local_joulegrader.generate_errorpanel(options, M.str.local_joulegrader.rubricerror);

        submitbuttons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                var valid = true;
                var criteria = document.querySelectorAll('#' + options.id + ' .gradingform_rubric .criterion');

                if (criteria.length > 0) {
                    criteria.forEach(function(criterion) {
                        var radiobuttons = criterion.querySelectorAll('input[type=radio]');
                        if (radiobuttons.length > 0) {
                            var validcriterion = false;
                            radiobuttons.forEach(function(radio) {
                                if (radio.checked) validcriterion = true;
                            });
                            valid = valid && validcriterion;
                        }
                    });
                }

                if (!valid) {
                    e.preventDefault();
                    errorpanel.show();
                    if (errorpanel.srcNode && errorpanel.srcNode.scrollIntoView) {
                        errorpanel.srcNode.scrollIntoView();
                    }
                    errorpanel.centered();
                }
            });
        });
    } else {
        var closebutton = {
            value: M.str.local_joulegrader.close,
            action: function(e) { panel.hide(); }
        };
        panel.addButton(closebutton);
        panel.render();
    }

    if (window.navigator.userAgent.indexOf("MSIE ") > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
        var commenttextarea = document.querySelector('#local-joulegrader-gradepane-panel .criterion .remark');
        if (commenttextarea) {
            commenttextarea.insertAdjacentHTML('beforeend', '<div style="visibility: hidden; width: 100px;"></div>');
        }
    }

    panel.set('width', 5000);
    var tablenode = panel.srcNode.querySelector('.gradingform_rubric table');
    if (tablenode) {
        var panelwidth = parseInt(tablenode.offsetWidth, 10) + 30;
        var jgEl = document.querySelector('#local-joulegrader');
        var maxwidth = jgEl ? parseInt(jgEl.offsetWidth, 10) : 5000;
        if (panelwidth > maxwidth) {
            panelwidth = maxwidth;
        }
        panel.set('width', panelwidth);
        var rubricForm = document.querySelector('#local-joulegrader-gradepane-panel .gradingform_rubric');
        if(rubricForm) {
            rubricForm.style.width = (panel.get('width') - 30) + 'px';
        }
    }
};

M.local_joulegrader.init_commentloop = function(id) {
    var commentloopcon = document.getElementById(id);
    if (!commentloopcon) return;

    var comments = commentloopcon.querySelector('.local_joulegrader_commentloop_comments');
    if (!comments) return;

    comments.scrollTop = comments.scrollHeight;

    var commentform = commentloopcon.querySelector('form');
    if (!commentform) return;

    // Delegate delete clicks.
    commentloopcon.addEventListener('click', function(e) {
        var link = e.target.closest('.local_joulegrader_comment_delete a');
        if (!link) return;

        e.preventDefault();

        if (!link.classList.contains('ajax-in-progress')) {
            link.classList.add('ajax-in-progress');
            var lnkhref = link.getAttribute('href');
            var params = lnkhref.split('?')[1];
            if (!params) return;

            fetch(M.cfg.wwwroot + '/local/joulegrader/view.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params + '&ajax=1'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.html) {
                        comments.innerHTML = data.html; // Using innerHTML as YUI's replace handles child replacement.
                    } else if (data.error) {
                        alert(data.error);
                    }
                })
                .catch(err => alert(err))
                .finally(() => link.classList.remove('ajax-in-progress'));
        }
    });

    commentform.addEventListener('submit', function(e) {
        var textarea = commentform.querySelector('textarea');
        if (!textarea) return;

        var editor;
        if (typeof tinyMCE !== "undefined") {
            editor = tinyMCE.get(textarea.id);
        } else {
            editor = commentform.querySelector('.editor_atto_content');
        }

        var comment = textarea.value;
        if (comment === '') return;

        e.preventDefault();

        var formData = new URLSearchParams(new FormData(commentform));
        formData.append('ajax', '1');

        fetch(M.cfg.wwwroot + '/local/joulegrader/view.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.html) {
                    comments.innerHTML = data.html;
                    textarea.value = '';
                    if (editor && typeof editor.setContent === 'function') {
                        editor.setContent('');
                    }
                    comments.scrollTop = comments.scrollHeight;
                } else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(err => alert(err));
    });
};

/**
 * Allow teachers to view submissions inline.
 *
 * @param unusedYUI {object} Needed but not used. This is because sadly js_init_call
 *                           always and forever calls function_call_with_y which injects the YUI.
 * @param courseid {number} The courseid where the inlinefile is being viewed.
 */
M.local_joulegrader.init_viewinlinefile = function(unusedYUI, courseid) {
    var loadedfiles = {};
    var filenamesbyids = {};
    var fileids = [];
    var filelinksbyids = {};
    var currentfile, currentfilehash;

    var filetreecon = document.querySelector('#local-joulegrader-assign23-treecon');
    var fileinline = document.querySelector('#local-joulegrader-assign23-files-inline');
    if (!filetreecon || !fileinline) return;

    var onlinesubmission = document.querySelector('#local-joulegrader-assign23-assign_submission_onlinetext');
    var inlinefilelinks = document.querySelectorAll('.local_joulegrader_assign23_inlinefile');
    if (inlinefilelinks.length === 0) return;

    var nextinlinefilelink = document.querySelector('#local-joulegrader-assign23-ctrl-next');
    var previnlinefilelink = document.querySelector('#local-joulegrader-assign23-ctrl-previous');
    var downloadlinkctrl = document.querySelector('#local-joulegrader-assign23-ctrl-download');
    var filenamectrl = document.querySelector('#local-joulegrader-assign23-ctrl-filename');
    var closeinline = fileinline.querySelector('#local-joulegrader-assign23-ctrl-close');

    var handleresize = function() {
        if (currentfile && !currentfile.classList.contains('local_joulegrader_hidden')) {
            var resourcecon = currentfile.querySelector('.resourcecontent');
            if (!resourcecon) return;

            var embedel = resourcecon.querySelector('object') || resourcecon.querySelector('iframe');
            var isiframe = !!resourcecon.querySelector('iframe');
            if (!embedel) return;

            var fileinlinewidth = window.getComputedStyle(fileinline).width;
            embedel.style.width = fileinlinewidth;

            if (isiframe) {
                var joulegraderpanes = document.querySelector('#local-joulegrader-panes');
                if (joulegraderpanes) {
                    embedel.style.height = window.getComputedStyle(joulegraderpanes).height;
                }
            }
        }
    };

    window.addEventListener('resize', handleresize);

    var show_node = function(node) {
        node.classList.remove('local_joulegrader_hidden');
    };

    var hide_node = function(node) {
        node.classList.add('local_joulegrader_hidden');
    };

    var show_inlinefile = function(filenode) {
        if (onlinesubmission) hide_node(onlinesubmission);
        hide_node(filetreecon);
        show_node(filenode);
        show_node(fileinline);

        if (fileinline.scrollIntoView) {
            fileinline.scrollIntoView();
        }

        currentfile = filenode;
        handleresize();
    };

    var hide_inlinefile = function() {
        if (currentfile) hide_node(currentfile);
        hide_node(fileinline);
        if (onlinesubmission) show_node(onlinesubmission);
        show_node(filetreecon);
    };

    var inlinefileselect = document.querySelector('#local-joulegrader-assign23-ctrl-select select');

    inlinefilelinks.forEach(function(filelink) {
        var selectkey = filelink.id;
        fileids.push(selectkey);

        var prevImg = filelink.previousElementSibling;
        var filename = prevImg && prevImg.tagName === 'IMG' ? prevImg.getAttribute('alt') : 'File';

        var option = document.createElement('option');
        option.value = selectkey;
        option.textContent = filename;
        if(inlinefileselect) inlinefileselect.appendChild(option);

        filenamesbyids[selectkey] = filename;
        filelinksbyids[selectkey] = filelink.getAttribute('href');
    });

    if (closeinline) {
        closeinline.addEventListener('click', function(e) {
            e.preventDefault();
            hide_inlinefile();
        });
    }

    var loadorshowfile = function(filehash) {
        if (!loadedfiles.hasOwnProperty(filehash)) {
            fetch(M.cfg.wwwroot + '/local/joulegrader/view.php?action=inlinefile&f=' + filehash + '&courseid=' + courseid)
                .then(response => response.json())
                .then(data => {
                    if (data.html) {
                        var inlineid = 'local-joulegrader-inlinefile-' + filehash;
                        var div = document.createElement('div');
                        div.id = inlineid;
                        div.innerHTML = data.html;
                        fileinline.appendChild(div);

                        loadedfiles[filehash] = document.getElementById(inlineid);
                        show_inlinefile(loadedfiles[filehash]);
                    } else if (data.error) {
                        alert(data.error);
                    }
                })
                .catch(err => alert(err));
        } else {
            show_inlinefile(loadedfiles[filehash]);
        }

        if (filenamectrl) filenamectrl.innerHTML = filenamesbyids[filehash];
        if (downloadlinkctrl) downloadlinkctrl.innerHTML = '(<a href="' + filelinksbyids[filehash] + '">' + M.str.local_joulegrader.download + '</a>)';
        if (inlinefileselect) inlinefileselect.value = filehash;
    };

    if (inlinefilelinks.length < 2) {
        if (nextinlinefilelink) nextinlinefilelink.remove();
        if (previnlinefilelink) previnlinefilelink.remove();
    } else {
        if (nextinlinefilelink) {
            nextinlinefilelink.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentfilehash) {
                    var currentfilepos = fileids.indexOf(currentfilehash);
                    if (currentfilepos !== -1) {
                        var nextpos = (currentfilepos === fileids.length - 1) ? 0 : currentfilepos + 1;
                        var nextfilehash = fileids[nextpos];
                        hide_node(currentfile);
                        loadorshowfile(nextfilehash);
                        currentfilehash = nextfilehash;
                    }
                }
            });
        }

        if (previnlinefilelink) {
            previnlinefilelink.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentfilehash) {
                    var currentfilepos = fileids.indexOf(currentfilehash);
                    if (currentfilepos !== -1) {
                        var prevpos = (currentfilepos === 0) ? fileids.length - 1 : currentfilepos - 1;
                        var nextfilehash = fileids[prevpos];
                        hide_node(currentfile);
                        loadorshowfile(nextfilehash);
                        currentfilehash = nextfilehash;
                    }
                }
            });
        }
    }

    if (inlinefileselect) {
        inlinefileselect.addEventListener('change', function(e) {
            var selectedvalue = this.value;
            if (selectedvalue == "0") {
                hide_inlinefile();
            } else {
                hide_node(currentfile);
                loadorshowfile(selectedvalue);
                currentfilehash = selectedvalue;
            }
        });
    }

    filetreecon.addEventListener('click', function(e) {
        var link = e.target.closest('.local_joulegrader_assign23_inlinefile');
        if (!link) return;

        e.preventDefault();
        var filehash = link.id;
        loadorshowfile(filehash);
        currentfilehash = filehash;
    });
};