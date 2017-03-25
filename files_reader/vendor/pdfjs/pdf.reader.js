PDFJS.reader = {};
PDFJS.reader.plugins = {};

PDFJS.TextLayerBuilder = {};

(function(root, $) {

    var previousReader = root.pdfReader || {};

    var pdfReader = root.pdfReader = function(path, options) {
        return new PDFJS.Reader(path, options);
    };

})(window, jQuery);

PDFJS.Reader = function(bookPath, _options) {

    var reader = this,
        book,
        $viewer = $("#viewer"),
        search = window.location.search;

    var TEXT_RENDER_DELAY = 200,  // ms
        PAGE_RENDER_DELAY = 200,  // ms
        MAX_CANVAS_PIXELS = 5242880,
        CSS_UNITS = 96.0 / 72.0,
        MIN_SCALE = 0.25,
        MAX_SCALE = 10.0,
        DEFAULT_SCALE = 1;

    this.settings = this.defaults(_options || {}, {
        bookPath: bookPath,
        textRenderDelay: TEXT_RENDER_DELAY,
        pageRenderDelay: PAGE_RENDER_DELAY,
        canvasLimit: 0,
        cssZoomOnly: false, // true || false, only zoom using CSS, render document at 100% size
        textSelect: false,   // true || false, add selectable text layer
        doubleBuffer: true, // true || false, draw to off-screen canvas
        numPages: 0,
        currentPage: 1,
        scale: DEFAULT_SCALE,
        oddPageRight: true, // when true, odd pages render on the right side
        zoomLevel: window.outerWidth > window.outerHeight ? "spread" : "fit_page",  // spread, fit_page, fit_width, percentage
        rotation: 0,    // 0 || 90 || 180 || 270 
        history: true,
        keyboard: {
            32: 'next',         // space
            34: 'next',         // page-down
            39: 'next',         // cursor-right
            33: 'previous',     // page-up
            37: 'previous',     // cursor-left
            36: 'first',        // home
            35: 'last',         // end
            65: 'annotate',     // a
            66: 'bookmark',     // b
            76: 'rotateLeft',   // l
            82: 'rotateRight',  // r
            90: 'toggleZoom',   // z
            83: 'toggleSidebar',// s
            84: 'toggleTitlebar',   // t
            68: 'toggleDay',    // d
            78: 'toggleNight',  // n
            70: 'toggleFullscreen', // f
            27: 'closeSidebar'  // esc
        },
        nightMode: false,
        dayMode: false,
        maxWidth: 72,
        pageArrows: false,
        annotations: {},
        customStyles: {},
        activeStyles: {},
        session: {}
    });

    // used for annotations and bookmarks
    this.Annotation = function (type, anchor, body, id) {
        this.id = id || EPUBJS.core.uuid();
        this.type = type;
        this.date = Date.now();
        this.edited = this.date;
        this.anchor = anchor;
        this.body = body;
    };

    // used for UI and book styles
    this.Style = function (name, selector, rules, extra) {
        this.name = name;
        this.selector = selector;
        this.rules = rules;
        this.extra = extra || null;
    };

    this.resourcelst = [
        {
            canvas: document.getElementById("left"),
            ctx: document.getElementById("left").getContext('2d'),
            textdiv: document.getElementById("text_left"),
            textLayer: null,
            renderTask: null,
            oscanvas: null,
            osctx: null,
            pageNum: null
        },
        {
            canvas: document.getElementById("right"),
            ctx: document.getElementById("right").getContext('2d'),
            textdiv: document.getElementById("text_right"),
            textLayer: null,
            renderTask: null,
            oscanvas: null,
            osctx: null,
            pageNum: null
        }
    ];

    this.cancelPage = {};

    this.renderQueue = false;

    // Overide options with search parameters
    if(search) {
        parameters = search.slice(1).split("&");
        parameters.forEach(function(p){
            var split = p.split("=");
            var name = split[0];
            var value = split[1] || '';
            reader.settings[name] = decodeURIComponent(value);
        });
    }


    //this.restoreDefaults(this.settings.session.defaults);
    //this.restorePreferences(this.settings.session.preferences);
    //this.restoreAnnotations(this.settings.session.annotations);
    this.sideBarOpen = false;
    this.viewerResized = false;
	this.pageNumPending = null;
    this.output_scaled = false;
    this.restricted_scaling = false;
    this.CSS_UNITS = CSS_UNITS;
    this.MIN_SCALE = MIN_SCALE;
    this.MAX_SCALE = MAX_SCALE;

 	PDFJS.getDocument(reader.settings.bookPath).then(function(_book) {
		reader.book = book = _book;
        console.log(book);
        reader.settings.numPages = reader.book.numPages;
        document.getElementById('total_pages').textContent = reader.settings.numPages;
        if(reader.settings.session.cursor !== {}) {
            reader.settings.currentPage = parseInt(reader.settings.session.cursor.anchor);
        }

		reader.ReaderController = PDFJS.reader.ReaderController.call(reader, book);
		reader.SettingsController = PDFJS.reader.SettingsController.call(reader, book);
		reader.ControlsController = PDFJS.reader.ControlsController.call(reader, book);
		reader.SidebarController = PDFJS.reader.SidebarController.call(reader, book);
		// BookmarksController depends on NotesController so load NotesController first
		//reader.NotesController = PDFJS.reader.NotesController.call(reader, book);
		//reader.BookmarksController = PDFJS.reader.BookmarksController.call(reader, book);
		//reader.SearchController = PDFJS.reader.SearchController.call(reader, book);
		//reader.MetaController = EPUBJS.reader.MetaController.call(reader, meta);
		//reader.TocController = EPUBJS.reader.TocController.call(reader, toc);
	    //reader.TextLayerController = PDFJS.reader.TextLayerController();	

        //reader.queuePage(reader.settings.currentPage);
        console.log(reader.settings);
        var startPage = (reader.settings.zoomLevel === "spread" && reader.settings.oddPageRight) ? 0 : 1;
        reader.queuePage(startPage);
		reader.ReaderController.hideLoader();
    });

	return this;
};

PDFJS.Reader.prototype.setZoom = function(zoom) {
    
    var reader = this,
        page = reader.settings.currentPage;

    reader.settings.zoomLevel = zoom;
    reader.queuePage(page);
};

PDFJS.Reader.prototype.setRotation = function (rotation) {

    var reader = this,
        page = reader.settings.currentPage;

    reader.settings.rotation = rotation;
    reader.queuePage(page);
};

PDFJS.Reader.prototype.cancelRender = function (index) {

    var reader = this,
        resourcelst = reader.resourcelst[index];

    if (resourcelst.renderTask) {
        console.log("cancel render on canvas " + index + ", pageNum " + resourcelst.pageNum);
        resourcelst.renderTask.cancel();
        resourcelst.renderTask = resourcelst.pageNum = null;
        resourcelst.oscanvas = resourcelst.osctx = null;
    }

    if (resourcelst.textLayer) {
        resourcelst.textLayer.cancel();
        resourcelst.textLayer = null;
    }
};

PDFJS.Reader.prototype.renderPage = function(pageNum) {

    var reader = this,
        $viewer = $("#viewer"),
        $page_num = document.getElementById('page_num');

    var index,
    	canvas,     // actual canvas
    	ctx,        // actual context
        oscanvas,   // off-screen canvas
        osctx,      // off-screen context
        textdiv,
        textLayer,
		outputscale,
        max_view_width,
        max_view_height,
        page_width,
        page_height,
        scale_width, 
        scale_height,
        view_aspect,
        document_aspect,
        scale,
        page_rotation,
        rotation,
        initial_viewport,
        viewport,
        zoom,
        fraction,
        offset,
        renderContext,
        renderTask,
        resourcelst,
        swap_orientation;

    max_view_width = window.innerWidth;
    max_view_height = window.innerHeight;

    if (this.settings.zoomLevel === "spread") {

        // show second canvas
        reader.resourcelst[1].canvas.style.display = "block";
        max_view_width /= 2;
        // select canvas and ctx based on pageNum, pageShift and oddPageRight
        pageShift = 2;
        oddPageShift = reader.settings.oddPageRight ? 0 : 1;
        index = (pageNum - oddPageShift) % pageShift;

    } else {

        index = 0;
        // hide second canvas
        reader.resourcelst[1].canvas.style.display = "none";
        // clear text layer
        reader.resourcelst[1].textdiv.innerHTML = "";

        // don't try to render non-existing page 0 (which is used
        // to indicate the empty left page when oddPageRight === true)
        if (pageNum === 0)
            pageNum++;

    }

    resourcelst = reader.resourcelst[index];

    canvas = resourcelst.canvas;
    ctx = resourcelst.ctx;
    textdiv = resourcelst.textdiv;
    outputscale = reader.getOutputScale(resourcelst.ctx);
    fraction = reader.approximateFraction(outputscale);

    if (pageNum <= this.settings.numPages && pageNum >= 1) {

        if (resourcelst.renderTask) {
            resourcelst.renderTask.cancel();
            resourcelst.renderTask = null;
        }

        if (resourcelst.textLayer) {
            resourcelst.textLayer.cancel();
            resourcelst.textLayer = null;
        }

        resourcelst.pageNum = pageNum;

        if (reader.cancelPage[pageNum])
            delete reader.cancelPage[pageNum];

        this.book.getPage(pageNum).then(function(page) {
            //console.log(page);
            page_rotation = page.rotate;
            rotation = (page_rotation + reader.settings.rotation) % 360;
            //initial_viewport = page.getViewport({scale: 1, rotation: rotation});
            initial_viewport = page.getViewport(1, rotation);
            page_width = initial_viewport.width;
            page_height = initial_viewport.height;

            document_aspect = parseFloat(page_width / page_height);
            view_aspect = parseFloat(max_view_width / max_view_height);
            
            scale_height = parseFloat(max_view_height / page_height);
            scale_width = parseFloat(max_view_width / page_width);

            /*
            console.log("m_v_w: " + max_view_width
                + " m_v_h: " + max_view_height
                + " p_w: " + page_width
                + " p_h: " + page_height
                + " d_a: " + document_aspect
                + " v_a: " + view_aspect
                + " s_w: " + scale_width
                + " s_h: " + scale_height
                + " p_r: " + page_rotation
                + " r: " + rotation
                + " o: " + outputscale);
            console.log("fraction: ", fraction);
            */

            switch (reader.settings.zoomLevel) {

                case "spread":

                    // INTENTIONAL FALL-THROUGH

                case "fit_page":

                    $viewer.addClass("flex");

                    if (scale_width > scale_height) {
                        scale = scale_height;
                        canvas.height = reader.roundToDivide(max_view_height * outputscale, fraction[0]);
                        canvas.width = reader.roundToDivide(parseInt(canvas.height * document_aspect), fraction[0]);
                    } else {
                        scale = scale_width;
                        canvas.width = reader.roundToDivide(max_view_width * outputscale, fraction[0])
                        canvas.height = reader.roundToDivide(parseInt(canvas.width / document_aspect), fraction[0]);
                    }

                    break;

                case "fit_width":

                    $viewer.removeClass("flex");

                    if (scale_width < scale_height) {
                        scale = scale_height;
                        canvas.height = reader.roundToDivide(max_view_height * outputscale, fraction[0]);
                        canvas.width = reader.roundToDivide(parseInt(canvas.height * document_aspect), fraction[0]);
                    } else {
                        scale = scale_width;
                        canvas.width = reader.roundToDivide(max_view_width * outputscale, fraction[0])
                        canvas.height = reader.roundToDivide(parseInt(canvas.width / document_aspect), fraction[0]);
                    }

                    break;

                default:

                    $viewer.removeClass("flex");
                    scale = parseFloat(reader.settings.zoomLevel * reader.CSS_UNITS);
                    canvas.width = reader.roundToDivide(parseInt(page_width * scale * outputscale), fraction[0]); ;
                    canvas.height = reader.roundToDivide(parseInt(page_height * scale * outputscale), fraction[0]);
                    break;
            }

            viewport = initial_viewport.clone({scale: scale, rotation: rotation});

            if (reader.settings.cssZoomOnly) {
                console.log("css zoom only");
                var actualSizeViewport = viewport.clone({scale: 1});
                canvas.width = actualSizeViewport.width;
                canvas.height = actualSizeViewport.height;
                outputscale = actualSizeViewport.width / viewport.width;
                reader.output_scaled = true;
            } 

            if (reader.settings.canvasLimit > 0) {
                console.log("canvas is limited");
                var pixelsInViewport = viewport.width * viewport.height;
                var maxscale =
                    Math.sqrt(reader.settings.canvasLimit / pixelsInViewport);
                if (outputscale > maxscale) {
                    console.log("outputscale: " + outputscale, "maxscale: " + maxscale);
                    outputscale = maxscale;
                    reader.output_scaled = true;
                    reader.restricted_scaling = true;
                } else {
                    reader.restricted_scaling = false;
                }
            }

            //console.log("outputscale: " + outputscale);
            //console.log("canvas w x h: " + canvas.width + " x " + canvas.height);

            transform = (outputscale === 1)
                ? null
                : [outputscale, 0, 0, outputscale, 0, 0];


            if (outputscale !== 1) {
                canvas.style.width = reader.roundToDivide(viewport.width, fraction[1]) + 'px';
                canvas.style.height = reader.roundToDivide(viewport.height, fraction[1]) + 'px';
            } else {
                canvas.style.width = "";
                canvas.style.height = "";
            }

            /* textlayer */
            if (reader.settings.textSelect) {
                textdiv.style.width = reader.roundToDivide(viewport.width, fraction[1]) + 'px';
                textdiv.style.height = reader.roundToDivide(viewport.height, fraction[1]) + 'px';
                offset = $(canvas).offset();
                $(textdiv).offset({
                    top: offset.top,
                    left: offset.left
                });
                page.getTextContent({ normalizeWhitespace: true }).then(function (textContent) {
                    resourcelst.textLayer = textLayer = new PDFJS.Reader.TextLayerController({
						textLayerDiv: textdiv,
						pageIdx: pageNum - 1,
						viewport: viewport,
						enhanceTextSelection: true
					});
                    textLayer.setTextContent(textContent);
                });
            } else {
                resourcelst.textLayer = textLayer = null;
            }

            /* /textLayer */

            if (reader.settings.doubleBuffer) {
                resourcelst.oscanvas = oscanvas = document.createElement("canvas");
                resourcelst.osctx = context = osctx = oscanvas.getContext('2d');
                oscanvas.width = canvas.width;
                oscanvas.height = canvas.height;
            } else {
                context = ctx;
            }

            renderContext = {
                canvasContext: context,
                viewport: viewport,
                transform: transform,
                textLayer: textLayer
            };

            resourcelst.renderTask = renderTask = page.render(renderContext);

            renderTask.promise.then(
                function pdfPageRenderCallback (something) {
                    if (reader.cancelPage[pageNum] === undefined) { 
                        console.log("finished rendering page " + pageNum);
                        if (reader.settings.doubleBuffer)
                            ctx.drawImage(oscanvas, 0, 0);
                        if (textLayer)
                            textLayer.render(reader.settings.textRenderDelay);
                    } else {
                        console.log("rendering page " + pageNum + " finished but cancelled");
                    }
                },
                function pdfPageRenderError(error) {
                    console.log("pdfPageRenderError: " + error);
                }
            );
        });

    } else {
        // clear canvas (by resizing it), use maximum size
        canvas.width = 0;
        canvas.height = 0;
        canvas.width = reader.roundToDivide(max_view_width * outputscale, fraction[0]);
        canvas.height = reader.roundToDivide(max_view_height * outputscale, fraction[0]);
        if (outputscale !== 1) {
            canvas.style.width = reader.roundToDivide(max_view_width, fraction[1]) + 'px';
            canvas.style.height = reader.roundToDivide(max_view_height, fraction[1]) + 'px';
        }
        // resizing clears canvas so this is not needed...
        //ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
};

PDFJS.Reader.prototype.queuePage = function(page) {
    
	//var pageShift = (this.settings.zoomLevel === "spread") ? 2 : 1;
    //
    var reader = this,
        zoom = reader.settings.zoomLevel,
        oddPageRight = reader.settings.oddPageRight,
        pageShift,
        $page_num = document.getElementById('page_num'),
        text;

    if (zoom === "spread") {
        pageShift = 2;
        if (oddPageRight === true) {
            page -= page % 2;
        } else {
            page -= (page + 1) % 2;
        }

        console.log("page: " + page);

        if (page >= 0 && page <= reader.settings.numPages) {
            if (page === reader.settings.numPages) {
                text = page.toString();
            } else if (page === 0) {
                text = "1";
            } else {
                text = page.toString() + "-" + parseInt(page+1).toString();
            }

            $page_num.textContent = text;
        }

    } else {
        pageShift = 1;
        if (page >= 1 && page <= reader.settings.numPages)
            $page_num.textContent = page.toString();
    }

    reader.settings.currentPage = page;

    if (typeof reader.renderQueue === 'number') {
        window.clearTimeout(reader.renderQueue);
        reader.renderQueue = false;
    }

    reader.renderQueue = window.setTimeout(function queuePages() {
        for (var i = 0; i < pageShift; i++) {
            reader.renderPage(page + i);
        }
    }, reader.settings.pageRenderDelay);
};

PDFJS.Reader.prototype.prevPage = function() {

    var reader = this;

	var pageShift = (this.settings.zoomLevel === "spread") ? 2 : 1;

    var oddPageShift = this.settings.oddPageRight ? 0 : 1;

    if (this.settings.currentPage - pageShift < oddPageShift) {
        return;
    } else {
        for (var i = 0; i < pageShift; i++) {
            reader.cancelPage[this.settings.currentPage - i] = true;
        }
        this.queuePage(this.settings.currentPage - pageShift);
    }
};

PDFJS.Reader.prototype.nextPage = function() {

    var reader = this;

	var pageShift = (this.settings.zoomLevel === "spread") ? 2 : 1;

    if (this.settings.currentPage + pageShift > this.settings.numPages) {
        return;
    } else {
        for (var i = 0; i < pageShift; i++) {
            reader.cancelPage[this.settings.currentPage + i] = true;
        }
        this.queuePage(this.settings.currentPage + pageShift);
    }
};

PDFJS.Reader.prototype.defaults = function (obj) {
  for (var i = 1, length = arguments.length; i < length; i++) {
    var source = arguments[i];
    for (var prop in source) {
      if (obj[prop] === void 0) obj[prop] = source[prop];
    }
  }
  return obj;
};

PDFJS.Reader.prototype.setScale = function (scale) {

};

PDFJS.Reader.prototype.getOutputScale = function (ctx) {
	var devicePixelRatio = window.devicePixelRatio || 1,
		backingStoreRatio = ctx.webkitBackingStorePixelRatio ||
			ctx.mozBackingStorePixelRatio ||
			ctx.msBackingStorePixelRatio ||
			ctx.oBackingStorePixelRatio ||
			ctx.backingStorePixelRatio || 1,
  	pixelRatio = devicePixelRatio / backingStoreRatio;

	return pixelRatio;
};

PDFJS.Reader.prototype.roundToDivide = function (x, div) {

    var r = x % div;

    return r === 0
        ? x
        : Math.round(x - r + div);
};


/**
 *  Approximates float number as a fraction using Farey sequence (max order
 *  of 8).
 *  @param {number} x - Positive float number.
 *  @returns {Array} Estimated fraction: the first array item is a numerator,
 *                   the second one is a denominator.
 */
PDFJS.Reader.prototype.approximateFraction = function (x) {

    // Fast paths for int numbers or their inversions.
    if (Math.floor(x) === x) {
        return [x, 1];
    }
    var xinv = 1 / x;
    var limit = 8;
    if (xinv > limit) {
        return [1, limit];
    } else if (Math.floor(xinv) === xinv) {
        return [1, xinv];
    }

    var x_ = x > 1 ? xinv : x;
    // a/b and c/d are neighbours in Farey sequence.
    var a = 0, b = 1, c = 1, d = 1;
    // Limiting search to order 8.
    while (true) {
        // Generating next term in sequence (order of q).
        var p = a + c, q = b + d;
        if (q > limit) {
            break;
        }
        if (x_ <= p / q) {
            c = p; d = q;
        } else {
            a = p; b = q;
        }
    }
    var result;
    // Select closest of the neighbours to x.
    if (x_ - a / b < c / d - x_) {
        result = x_ === x ? [a, b] : [b, a];
    } else {
        result = x_ === x ? [c, d] : [d, c];
    }
    return result;
};

PDFJS.Reader.prototype.isMobile = function () {

    var reader = this;

    var isMobile = (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);

    if (isMobile) {
        reader.isMobile = true;
        reader.canvasLimit = reader.settings.canvasLimit;
    }

    return isMobile;
};
