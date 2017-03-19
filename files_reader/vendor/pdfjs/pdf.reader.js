PDFJS.reader = {};
PDFJS.reader.plugins = {};

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

    this.settings = this.defaults(_options || {}, {
        bookPath: bookPath,
        numPages: 0,
        currentPage: 1,
        scale: 1,
        sideBySide: true,   // when true, render 2 pages side-by-side
        oddPageRight: true, // when true, odd pages render on the right side
        history: true,
        keyboard: {
            32: 'next', // space
            34: 'next', // page-down
            39: 'next', // cursor-right
            33: 'previous', // page-up
            37: 'previous', // cursor-left
            36: 'first', // home
            35: 'last', // end
            65: 'annotate', // a
            66: 'bookmark', // b
            82: 'reflow', // r
            83: 'toggleSidebar', // s
            84: 'toolbar', // t
            68: 'toggleDay', // d
            78: 'toggleNight', // n
            70: 'toggleFullscreen', // f
            27: 'closeSidebar' // esc
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

    this.canvaslst = [
        document.getElementById("left"),
        document.getElementById("right")
    ];

    this.contextlst = [
        document.getElementById("left").getContext('2d'),
        document.getElementById("right").getContext('2d')
    ];

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
	//this.sideBySide = window.outerWidth > window.outerHeight ? true : false;
    this.sideBySide = false;
	this.pageNumPending = null;

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

        //reader.queuePage(reader.settings.currentPage);
        var startPage = reader.settings.oddPageRight ? 0 : 1;
        reader.queuePage(startPage);
		reader.ReaderController.hideLoader();
    });

	return this;
};

PDFJS.Reader.prototype.renderPage = function(pageNum) {

	var reader = this,
		pageShift = this.settings.sideBySide ? 2 : 1,
        oddPageShift = this.settings.oddPageRight ? 0 : 1,
    	i = (pageNum - oddPageShift) % pageShift,
    	canvas = this.canvaslst[i],
    	ctx = this.contextlst[i],
        pixelratio = window.devicePixelRatio,
        max_view_height = parseInt(window.outerHeight * 0.95),
        max_view_width = reader.settings.sideBySide
            ? parseInt((window.outerWidth / 2) * 1)
            : parseInt(window.outerWidth * 1),
        scale,
        transform,
        $page_num = document.getElementById('page_num');

    if (this.settings.sideBySide) {
        this.canvaslst[1].style.display = "block";
    } else {
        this.canvaslst[1].style.display = "none";
    }

    if (pageNum <= this.settings.numPages && pageNum >= 1) {

        this.pageRendering = true;

        this.book.getPage(pageNum).then(function(page) {
            console.log(page);
            var page_width = page.pageInfo.view[2];
            var page_height = page.pageInfo.view[3];

            var scale_height = parseFloat(max_view_height / page_height);
            var scale_width = parseFloat(max_view_width / page_width);
            var document_aspect = parseFloat(page_width / page_height);
            var view_aspect = parseFloat(max_view_width / max_view_height);

            console.log(max_view_width
                + " " + max_view_height
                + " " + page_width
                + " " + page_height
                + " " + document_aspect
                + " " + view_aspect
                + " " + scale_width
                + " " + scale_height
                + " " + pixelratio);

            scale = Math.min(scale_width, scale_height) / pixelratio;

            if (scale_width < scale_height) {
                canvas.width = max_view_width;
                canvas.height = parseInt(page_height * scale_width);
                canvas.style.width = parseInt(max_view_width / pixelratio);
                scale = scale_width;
            } else {
                canvas.height = max_view_height;
                canvas.width = parseInt(page_width * scale_height);
                canvas.style.width = parseInt(max_view_width / pixelratio);
                scale = scale_height;
            }

            if (document_aspect < view_aspect) {
                console.log("document aspect < view aspect, aspect ratio " + document_aspect);
                //canvas.height = parseInt(max_view_height / pixelratio);
                //canvas.width = parseInt(canvas.height * document_aspect);
                //scale = parseFloat(scale_height / pixelratio);
                transform = [ 1, 0, 0, 1, parseInt((canvas.width - (page_width*scale)) / 2), 0 ];
            } else {
                console.log("document aspect > view_aspect, aspect ratio " + document_aspect);

                //canvas.height = parseInt(max_view_height / pixelratio);
                //canvas.width = parseInt(canvas.height * document_aspect);
                //canvas.width = parseInt(max_view_width / pixelratio);
                //canvas.height = parseInt(canvas.width * document_aspect);
                //scale = parseFloat(scale_width / pixelratio);
                canvas.style.top = parseInt((max_view_height - canvas.height) / 2);
                transform = [ 1, 0, 0, 1, 0, parseInt((canvas.height - (page_height*scale)) / 2) ];
            }
            console.log(canvas.width + " " + canvas.height);

            var viewport = page.getViewport(scale);
            console.log(viewport);

            var renderContext = {
                canvasContext: ctx,
                viewport: viewport
                //transform: transform
            };

            var renderTask = page.render(renderContext);
            renderTask.promise.then(function() {
                reader.pageRendering = false;
                if (reader.pageNumPending !== null) {
                    reader.renderPage(reader.pageNumPending);
                    reader.pageNumPending = null;
                }
            });
        });

    } else {

        canvas.width = max_view_width;
        canvas.height = max_view_height;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

    }

	if (i === 0) {

        if (pageNum > 0) {
            $page_num.textContent = pageNum.toString();
        }
		reader.settings.currentPage = pageNum;

	} else {

        if (pageNum === 1) {

            $page_num.textContent = pageNum.toString();

        } else {

            var text = $page_num.textContent;
            text += "-" + pageNum.toString();
            $page_num.textContent = text;

        }
    }
};

PDFJS.Reader.prototype.queuePage = function(pageNum) {
    
	var pageShift = this.settings.sideBySide ? 2 : 1;

    if (this.pageRendering) {
        this.pageNumPending = pageNum;
    } else {
        for (var i = 0; i < pageShift; i++) {
            this.renderPage(pageNum + i);
        }
    }
};

PDFJS.Reader.prototype.prevPage = function() {

    var pageShift = this.settings.sideBySide ? 2 : 1;
    var oddPageShift = this.settings.oddPageRight ? 0 : 1;

    if (this.settings.currentPage - pageShift < oddPageShift) {
        return;
    } else {
        this.queuePage(this.settings.currentPage - pageShift);
    }
};

PDFJS.Reader.prototype.nextPage = function() {

    var pageShift = this.settings.sideBySide ? 2 : 1;

    if (this.settings.currentPage + pageShift > this.settings.numPages) {
        return;
    } else {
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
